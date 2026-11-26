<?php

namespace App\Services\Quotes;

use App\Models\ClientTask;
use App\Models\ConsortiumNotification;
use App\Models\Quote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Automações dos orçamentos (rodam de hora em hora, quotes:automacoes):
 *  - pedido do portal sem resposta passou do prazo prometido → lembrete no sino;
 *  - enviado e não visto em 3 dias → lembrete para reenviar pelo WhatsApp;
 *  - faltam 2 dias para vencer → aviso no sino e (se ligado) tarefa com a mensagem pronta para o cliente;
 *  - passou da validade → muda para "Vencido".
 * Cada lembrete sai uma vez só (quotes.reminders guarda o que já foi feito).
 */
class QuoteAutomations
{
    public function run(?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : Carbon::now(QuoteService::TZ);
        $today = $now->copy()->startOfDay();
        $out = ['pedido' => 0, 'nao_visto' => 0, 'vence' => 0, 'vencido' => 0];
        $settings = [];
        $cfg = function (int $uid) use (&$settings) {
            return $settings[$uid] ??= QuoteService::settings($uid);
        };

        // 1) Pedido novo parado
        Quote::where('status', 'pedido')->where('created_at', '<=', $now->copy()->subDay())->chunkById(200, function ($qs) use ($now, $cfg, &$out) {
            foreach ($qs as $q) {
                $days = $cfg($q->user_id)['reply_days'];
                if ($this->done($q, 'pedido') || $this->businessDaysSince($q->created_at->timezone(QuoteService::TZ), $now) < $days) {
                    continue;
                }
                ConsortiumNotification::createGeneric('quotes', 'quote_reminder', $q->user_id,
                    'Pedido sem resposta: ' . $q->customerName(),
                    'O portal prometeu responder em ' . $days . ($days == 1 ? ' dia útil. ' : ' dias úteis. ') . Str::limit((string) $q->request_text, 100),
                    ['action_url' => route('orcamentos.edit', $q->id), 'priority' => 'high', 'entity_type' => 'quote', 'entity_id' => $q->id]);
                $this->mark($q, 'pedido', 'Lembrete: pedido sem resposta');
                $out['pedido']++;
            }
        });

        // 2) Enviado e não visto em 3 dias
        Quote::where('status', 'enviado')->whereNull('viewed_at')->where('sent_at', '<=', $now->copy()->subDays(3))->chunkById(200, function ($qs) use (&$out) {
            foreach ($qs as $q) {
                if ($this->done($q, 'nao_visto')) {
                    continue;
                }
                ConsortiumNotification::createGeneric('quotes', 'quote_reminder', $q->user_id,
                    $q->code() . ' ainda não foi visto',
                    $q->customerName() . ' não abriu o orçamento há 3 dias. Que tal reenviar pelo WhatsApp?',
                    ['action_url' => route('orcamentos.show', $q->id), 'entity_type' => 'quote', 'entity_id' => $q->id]);
                $this->mark($q, 'nao_visto', 'Lembrete: não visto em 3 dias');
                $out['nao_visto']++;
            }
        });

        // 3) Faltam 2 dias para vencer
        Quote::where('status', 'enviado')->whereNotNull('valid_until')
            ->whereBetween('valid_until', [$today->toDateString(), $today->copy()->addDays(2)->toDateString()])
            ->chunkById(200, function ($qs) use ($cfg, &$out) {
                foreach ($qs as $q) {
                    $key = 'vence_' . $q->valid_until->toDateString();
                    if ($this->done($q, $key)) {
                        continue;
                    }
                    ConsortiumNotification::createGeneric('quotes', 'quote_reminder', $q->user_id,
                        $q->code() . ' vence ' . $q->valid_until->format('d/m'),
                        $q->customerName() . ' ainda não respondeu ' . $q->displayTitle() . ' (' . QuoteService::money((float) $q->total) . ').',
                        ['action_url' => route('orcamentos.show', $q->id), 'entity_type' => 'quote', 'entity_id' => $q->id]);
                    if ($cfg($q->user_id)['remind_client'] && ($q->client_id || $q->customerPhone())) {
                        $first = trim(explode(' ', $q->customerName())[0] ?? '');
                        ClientTask::create([
                            'user_id' => $q->user_id,
                            'client_id' => $q->client_id,
                            'title' => mb_substr('Lembrar ' . $q->customerName() . ': ' . $q->code() . ' vence ' . $q->valid_until->format('d/m'), 0, 160),
                            'due_date' => Carbon::today(QuoteService::TZ)->toDateString(),
                            'auto' => 'orcamento',
                            'message' => ($first && $first !== 'Sem' ? "Oi, {$first}! " : 'Oi! ') . 'Passando para lembrar que o seu orçamento ' . $q->code() . ' vale até ' . $q->valid_until->format('d/m') . '. Qualquer dúvida é só chamar. ' . $q->publicUrl(),
                        ]);
                    }
                    $this->mark($q, $key, 'Lembrete: vence em breve');
                    $out['vence']++;
                }
            });

        // 4) Venceu
        Quote::whereIn('status', ['enviado', 'rascunho'])->whereNotNull('valid_until')->where('valid_until', '<', $today->toDateString())
            ->chunkById(200, function ($qs) use (&$out) {
                foreach ($qs as $q) {
                    $q->status = 'vencido';
                    $q->addEvent('vencido', 'Passou da validade');
                    $q->save();
                    $out['vencido']++;
                }
            });

        return $out;
    }

    protected function done(Quote $q, string $key): bool
    {
        return !empty(($q->reminders ?? [])[$key]);
    }

    protected function mark(Quote $q, string $key, string $event): void
    {
        $r = $q->reminders ?? [];
        $r[$key] = now()->toIso8601String();
        $q->reminders = $r;
        $q->addEvent('lembrete', $event);
        $q->saveQuietly();
    }

    /** Dias úteis (seg a sex) entre duas datas. */
    public function businessDaysSince(Carbon $from, Carbon $to): int
    {
        $d = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        $n = 0;
        while ($d->lt($end)) {
            $d->addDay();
            if (!$d->isWeekend()) {
                $n++;
            }
        }

        return $n;
    }
}
