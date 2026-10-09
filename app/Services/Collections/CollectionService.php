<?php

namespace App\Services\Collections;

use App\Models\ConsortiumPayment;
use App\Models\PaymentReminder;
use App\Models\PromotionSetting;
use App\Models\Sale;
use App\Models\User;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Cobranças: junta parcelas de vendas, saldos de vendas em aberto e parcelas
 * de consórcio ainda não pagas, com a data do último lembrete enviado e o
 * texto pronto para o WhatsApp.
 */
class CollectionService
{
    /**
     * Tudo o que está em aberto para o usuário, ordenado por vencimento.
     *
     * Cada linha: key, kind (parcela|venda|consorcio), channel (vendas|consorcios),
     * remindable_type, remindable_id, client, client_id, description, installment,
     * value, due (Carbon), days (dias até vencer; negativo = em atraso),
     * last_reminder (?Carbon), url.
     */
    public function rows(User $user): Collection
    {
        $today = now()->startOfDay();

        // Mesmo recorte da tela "A receber": admin vê só as próprias vendas;
        // no console (comando diário) não há escopo de equipe, então filtra pelo dono.
        $salesQuery = fn () => Sale::query()
            ->when($user->isAdmin() || app()->runningInConsole(), fn ($q) => $q->where('user_id', $user->id))
            ->whereNotIn('status', ['cancelada', 'orcamento']);

        $installments = VendaParcela::with('sale.client')
            ->where('status', 'pendente')
            ->whereNotNull('data_vencimento')
            ->whereIn('sale_id', $salesQuery()->select('id'))
            ->get();

        $installmentTotals = $installments->isEmpty() ? collect() : VendaParcela::query()
            ->whereIn('sale_id', $installments->pluck('sale_id')->unique())
            ->selectRaw('sale_id, COUNT(*) as total')
            ->groupBy('sale_id')
            ->pluck('total', 'sale_id');

        $rows = $installments->map(function (VendaParcela $p) use ($installmentTotals) {
            $total = max((int) $p->sale?->parcelas, (int) ($installmentTotals[$p->sale_id] ?? 0));
            $installment = 'Parcela ' . $p->numero_parcela . ($total ? '/' . $total : '');

            return [
                'key' => 'p' . $p->id,
                'kind' => 'parcela',
                'channel' => 'vendas',
                'remindable_type' => VendaParcela::class,
                'remindable_id' => $p->id,
                'client' => $p->sale?->client,
                'client_id' => $p->sale?->client_id,
                'description' => 'Venda #' . $p->sale_id . ' · ' . mb_strtolower($installment),
                'what' => 'Venda #' . $p->sale_id,
                'installment' => $installment,
                'value' => round((float) $p->valor, 2),
                'due' => Carbon::parse($p->data_vencimento)->startOfDay(),
                'url' => $this->safeRoute('sales.show', $p->sale_id),
            ];
        });

        // Vendas não parceladas com saldo em aberto (vencimento = data da venda)
        $open = $salesQuery()
            ->with('client')
            ->where(fn ($q) => $q->whereNull('tipo_pagamento')->orWhere('tipo_pagamento', '!=', 'parcelado'))
            ->whereColumn('amount_paid', '<', 'total_price')
            ->get()
            ->map(fn (Sale $s) => [
                'key' => 's' . $s->id,
                'kind' => 'venda',
                'channel' => 'vendas',
                'remindable_type' => Sale::class,
                'remindable_id' => $s->id,
                'client' => $s->client,
                'client_id' => $s->client_id,
                'description' => 'Venda #' . $s->id . ' · saldo em aberto',
                'what' => 'Venda #' . $s->id,
                'installment' => 'Saldo da venda',
                'value' => round((float) $s->total_price - (float) $s->amount_paid, 2),
                'due' => $s->created_at?->copy()->startOfDay() ?? now()->startOfDay(),
                'url' => $this->safeRoute('sales.show', $s->id),
            ])
            ->filter(fn ($r) => $r['value'] > 0.009);

        $rows = $rows->concat($open)->concat($this->consortiumRows($user));

        // Último lembrete de cada item
        $last = collect();
        if ($rows->isNotEmpty()) {
            $last = PaymentReminder::query()
                ->where(function ($q) use ($rows) {
                    foreach ($rows->groupBy('remindable_type') as $type => $group) {
                        $q->orWhere(fn ($w) => $w->where('remindable_type', $type)->whereIn('remindable_id', $group->pluck('remindable_id')));
                    }
                })
                ->selectRaw('remindable_type, remindable_id, MAX(sent_at) as last_sent')
                ->groupBy('remindable_type', 'remindable_id')
                ->get()
                ->mapWithKeys(fn ($r) => [$r->remindable_type . '#' . $r->remindable_id => Carbon::parse($r->last_sent)]);
        }

        return $rows
            ->map(function ($r) use ($today, $last) {
                $r['days'] = (int) $today->diffInDays($r['due'], false);
                $r['last_reminder'] = $last[$r['remindable_type'] . '#' . $r['remindable_id']] ?? null;

                return $r;
            })
            ->sortBy(fn ($r) => $r['due']->timestamp)
            ->values();
    }

    protected function consortiumRows(User $user): Collection
    {
        $payments = ConsortiumPayment::query()
            ->with(['participant.client', 'participant.consortium'])
            ->whereIn('status', ['pending', 'overdue'])
            ->whereNotNull('due_date')
            ->whereHas('participant', fn ($q) => $q->where('status', '!=', 'quit')
                ->whereHas('consortium', fn ($c) => $c->where('user_id', $user->id)))
            ->get();

        if ($payments->isEmpty()) {
            return collect();
        }

        // Número da parcela = posição pelo vencimento entre as parcelas do participante
        $order = ConsortiumPayment::query()
            ->whereIn('consortium_participant_id', $payments->pluck('consortium_participant_id')->unique())
            ->orderBy('due_date')->orderBy('id')
            ->get(['id', 'consortium_participant_id'])
            ->groupBy('consortium_participant_id')
            ->map(fn ($g) => $g->pluck('id')->flip()->map(fn ($i) => $i + 1));

        return $payments->map(function (ConsortiumPayment $pay) use ($order) {
            $participant = $pay->participant;
            $consortium = $participant?->consortium;
            $n = $order[$pay->consortium_participant_id][$pay->id] ?? null;
            $total = (int) ($consortium?->duration_months ?: ($order[$pay->consortium_participant_id] ?? collect())->count());
            $installment = 'Parcela ' . ($n ?? '?') . ($total ? '/' . $total : '');
            $name = $consortium?->name ?? 'Consórcio';

            return [
                'key' => 'c' . $pay->id,
                'kind' => 'consorcio',
                'channel' => 'consorcios',
                'remindable_type' => ConsortiumPayment::class,
                'remindable_id' => $pay->id,
                'client' => $participant?->client,
                'client_id' => $participant?->client_id,
                'description' => $name . ' · ' . mb_strtolower($installment),
                'what' => $name,
                'installment' => $installment,
                'value' => round((float) $pay->amount, 2),
                'due' => Carbon::parse($pay->due_date)->startOfDay(),
                'url' => $consortium ? $this->safeRoute('consortiums.show', $consortium->id) : null,
            ];
        });
    }

    /** 'overdue' para parcela vencida, 'due' para vencendo hoje ou depois. */
    public function templateKind(array $row): string
    {
        return $row['days'] < 0 ? 'overdue' : 'due';
    }

    public function message(array $row, PromotionSetting $settings, ?string $storeName = null): string
    {
        $client = $row['client'] ?? null;
        $first = $client?->name ? Str::of($client->name)->trim()->explode(' ')->first() : '';

        $text = strtr($settings->collectionTemplate($this->templateKind($row)), [
            '{cliente}' => (string) $first,
            '{valor}' => 'R$ ' . number_format((float) $row['value'], 2, ',', '.'),
            '{vencimento}' => $row['due']->format('d/m/Y'),
            '{descricao}' => $row['description'],
            '{dias}' => (string) abs((int) $row['days']),
            '{loja}' => (string) $storeName,
        ]);

        // Sem nome do cliente: "Oi, !" vira "Oi!"
        $text = preg_replace('/,\s*([!?.])/u', '$1', $text);

        // Linhas que ficaram vazias (ex.: {loja} sem nome) não vão na mensagem
        return trim(preg_replace("/\n{3,}/", "\n\n", preg_replace("/[ \t]+\n/", "\n", $text)));
    }

    /** Telefone no formato do wa.me (55 + DDD + número) ou null se inválido. */
    public function whatsappPhone(?string $phone): ?string
    {
        $digits = ltrim(preg_replace('/\D/', '', (string) $phone), '0');

        if (strlen($digits) >= 12 && str_starts_with($digits, '55')) {
            return strlen($digits) <= 13 ? $digits : null;
        }

        return in_array(strlen($digits), [10, 11], true) ? '55' . $digits : null;
    }

    public function whatsappUrl(string $phone, string $text): string
    {
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }

    public function recordReminder(User $user, array $row, string $channel = 'whatsapp'): PaymentReminder
    {
        return PaymentReminder::create([
            'user_id' => $user->id,
            'remindable_type' => $row['remindable_type'],
            'remindable_id' => $row['remindable_id'],
            'client_id' => $row['client_id'],
            'channel' => $channel,
            'template' => $this->templateKind($row),
            'sent_at' => now(),
        ]);
    }

    protected function safeRoute(string $name, $param): ?string
    {
        try {
            return route($name, $param);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
