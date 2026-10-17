<?php

namespace App\Services\WhatsApp;

use App\Models\FinanceCapture;
use App\Models\WhatsAppSetting;
use App\Services\FinanceCapture\CaptureService;
use App\Services\FinanceCapture\EntryWriter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Conversa do WhatsApp: recebe o print, lança e responde. Quando falta o
 * cartão ou o tipo, pergunta com uma lista numerada e espera o número.
 */
class WhatsAppBot
{
    private ?WhatsAppClient $client = null;

    public function __construct(private CaptureService $captures, private EntryWriter $writer)
    {
    }

    public function withClient(WhatsAppClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    private function client(WhatsAppSetting $s): WhatsAppClient
    {
        return $this->client ??= new WhatsAppClient($s);
    }

    /** Uma mensagem do webhook (entry.changes.value.messages[]). */
    public function handle(WhatsAppSetting $s, array $msg): void
    {
        $from = (string) ($msg['from'] ?? '');
        $id = (string) ($msg['id'] ?? '');
        if ($from === '' || $id === '') {
            return;
        }
        if (! $s->allows($from)) {
            Log::info('WhatsApp: mensagem de número não autorizado ignorada', ['user' => $s->user_id, 'from' => substr($from, 0, 4) . '…']);

            return;
        }
        // A Meta reenvia quando demora: cada mensagem só uma vez
        if (! Cache::add("wa-msg:{$id}", 1, now()->addDays(2)) || FinanceCapture::where('wa_message_id', $id)->exists()) {
            return;
        }
        $s->forceFill(['last_message_at' => now()])->save();

        $type = $msg['type'] ?? '';
        $extra = ['wa_from' => $from, 'wa_message_id' => $id];

        if ($type === 'image' || ($type === 'document' && str_starts_with((string) data_get($msg, 'document.mime_type'), 'image/'))) {
            $mediaId = data_get($msg, "{$type}.id");
            try {
                [$bytes, $mime] = $this->client($s)->downloadMedia((string) $mediaId);
            } catch (\Throwable $e) {
                $this->say($s, $from, '⚠️ ' . $e->getMessage());

                return;
            }
            $result = $this->captures->fromImage($s->user_id, $bytes, $mime, 'whatsapp', $s->auto_save, $extra);
            $this->replyCaptures($s, $from, $result['captures']);

            return;
        }

        if ($type === 'document') {
            $this->say($s, $from, 'Mande o *print* (imagem) da notificação. Para fatura em PDF, use Cartões › Importar fatura no app.');

            return;
        }

        $text = trim((string) (data_get($msg, 'text.body') ?? data_get($msg, 'interactive.button_reply.title') ?? data_get($msg, 'interactive.list_reply.title') ?? ''));
        if ($text === '') {
            $this->say($s, $from, $this->help());

            return;
        }
        $this->handleText($s, $from, $text, $extra);
    }

    private function handleText(WhatsAppSetting $s, string $from, string $text, array $extra): void
    {
        $low = mb_strtolower(trim($text, " .!?\t\n"));

        if (in_array($low, ['desfazer', 'desfaz', 'apagar', 'cancelar', 'excluir'], true)) {
            $last = FinanceCapture::where('user_id', $s->user_id)->where('source', 'whatsapp')->where('wa_from', $from)
                ->where('status', 'saved')->where('updated_at', '>=', now()->subDay())->latest('updated_at')->first();
            if (! $last) {
                $this->say($s, $from, 'Não achei nada lançado por aqui nas últimas 24 horas para desfazer.');

                return;
            }
            $this->captures->undo($last);
            $this->say($s, $from, '↩️ Desfeito: ' . $last->description . ' (R$ ' . number_format((float) $last->amount, 2, ',', '.') . ') saiu ' . ($last->kind === FinanceCapture::KIND_CARD ? 'da fatura.' : 'do livro caixa.'));

            return;
        }

        if (in_array($low, ['ajuda', 'oi', 'olá', 'ola', 'menu', 'help', 'inicio', 'início'], true)) {
            $this->say($s, $from, $this->help());

            return;
        }

        if (preg_match('/^\d{1,2}$/', $low) && ($pending = Cache::get($this->choiceKey($s, $from)))) {
            $this->applyChoice($s, $from, $pending, (int) $low);

            return;
        }

        $result = $this->captures->fromText($s->user_id, $text, 'whatsapp', $s->auto_save, $extra);
        if ($result['captures']) {
            $this->replyCaptures($s, $from, $result['captures']);

            return;
        }
        $this->say($s, $from, "Não achei um valor (R\$) nessa mensagem.\n\n" . $this->help());
    }

    /** @param FinanceCapture[] $captures */
    private function replyCaptures(WhatsAppSetting $s, string $from, array $captures): void
    {
        $lines = [];
        $ask = null;
        foreach ($captures as $c) {
            if ($c->status === 'failed') {
                $lines[] = '⚠️ ' . ($c->error ?: 'Não consegui ler esse print.') . ' Manda de novo, ou copie e cole o texto da notificação.';
                continue;
            }
            if ($c->status === 'saved') {
                $lines[] = ($c->linked_existing ? '👍 Já estava lançado: ' : '✅ Lancei: ') . $this->captures->summary($c);
                continue;
            }
            if ($c->error) {
                $lines[] = '🔁 Esse já tinha sido lançado: ' . $this->captures->summary($c) . '. Se for outra compra igual, grave no app.';
                continue;
            }
            // Falta algo: pergunta só da primeira; as outras ficam no app
            $missing = $this->captures->missing($c);
            if (! $ask && in_array($missing, ['tipo', 'cartão'], true)) {
                $ask = $c;
                continue;
            }
            $lines[] = '📝 Deixei para conferir no app: ' . $this->captures->summary($c);
        }

        $saved = collect($captures)->where('status', 'saved')->where('linked_existing', false)->count();
        if ($saved) {
            $lines[] = 'Responda *desfazer* para apagar o último.';
        }
        if ($lines) {
            $this->say($s, $from, implode("\n", $lines));
        }
        if ($ask) {
            $this->ask($s, $from, $ask);
        }
        if (collect($captures)->where('status', 'review')->count() > ($ask ? 1 : 0)) {
            $this->say($s, $from, 'Para conferir: ' . route('finance.print-capture'));
        }
    }

    private function choiceKey(WhatsAppSetting $s, string $from): string
    {
        return "wa-choice:{$s->user_id}:" . WhatsAppSetting::normalizeNumber($from);
    }

    private function ask(WhatsAppSetting $s, string $from, FinanceCapture $c): void
    {
        $value = 'R$ ' . number_format((float) $c->amount, 2, ',', '.');
        if (! $c->kind) {
            Cache::put($this->choiceKey($s, $from), ['capture' => $c->id, 'field' => 'kind', 'options' => [FinanceCapture::KIND_CARD, FinanceCapture::KIND_EXPENSE, FinanceCapture::KIND_INCOME]], now()->addHours(12));
            $this->say($s, $from, "Achei {$value} em {$c->description}. O que é?\n1. Compra no cartão de crédito\n2. Saída da conta (Pix, débito, boleto)\n3. Entrada (dinheiro que recebi)\n\nResponda com o número.");

            return;
        }

        $banks = $this->writer->banks($s->user_id);
        if ($banks->isEmpty()) {
            $this->say($s, $from, "Achei uma compra de {$value} em {$c->description}, mas você não tem cartão cadastrado. Cadastre em Cartões no app e confira aqui: " . route('finance.print-capture'));

            return;
        }
        $options = $banks->take(9)->values();
        Cache::put($this->choiceKey($s, $from), ['capture' => $c->id, 'field' => 'bank_id', 'options' => $options->pluck('id_bank')->all()], now()->addHours(12));
        $list = $options->map(fn ($b, $i) => ($i + 1) . '. ' . $b->name)->implode("\n");
        $this->say($s, $from, "Compra de {$value} em {$c->description}" . ($c->institution ? " ({$c->institution})" : '') . ". Em qual cartão?\n{$list}\n\nResponda com o número.");
    }

    private function applyChoice(WhatsAppSetting $s, string $from, array $pending, int $n): void
    {
        $c = FinanceCapture::where('user_id', $s->user_id)->find($pending['capture'] ?? 0);
        $value = $pending['options'][$n - 1] ?? null;
        if (! $c || $c->status !== 'review') {
            Cache::forget($this->choiceKey($s, $from));
            $this->say($s, $from, 'Esse lançamento já foi resolvido.');

            return;
        }
        if ($value === null) {
            $this->say($s, $from, 'Responda com um dos números da lista.');

            return;
        }
        Cache::forget($this->choiceKey($s, $from));

        if ($pending['field'] === 'kind') {
            $c->kind = $value;
            if ($value === FinanceCapture::KIND_CARD) {
                $c->bank_id = $this->writer->findBank($s->user_id, $c->institution)?->id_bank;
            } else {
                $c->account_id = $this->writer->findAccount($s->user_id, $c->institution)?->id;
                $c->installments = null;
            }
            $c->category_id = $c->category_id ?: $this->writer->guessCategory($s->user_id, (string) $c->description, $value === FinanceCapture::KIND_INCOME);
        } else {
            $c->bank_id = (int) $value;
        }
        $c->save();

        $this->captures->save($c);
        $c->refresh();
        if ($c->status === 'saved') {
            $this->say($s, $from, ($c->linked_existing ? '👍 Já estava lançado: ' : '✅ Lancei: ') . $this->captures->summary($c) . "\nResponda *desfazer* para apagar.");
        } elseif ($this->captures->missing($c) === 'cartão') {
            $this->ask($s, $from, $c);
        } else {
            $this->say($s, $from, '📝 Deixei para conferir no app: ' . route('finance.print-capture'));
        }
    }

    private function help(): string
    {
        return "Oi! Eu lanço suas compras e Pix no FlowManeger.\n\n"
            . "📸 Mande o *print* da notificação do banco (compra no cartão, Pix recebido, pagamento...).\n"
            . "✍️ Ou cole o texto da notificação.\n"
            . "↩️ *desfazer* apaga o último lançamento.\n\n"
            . 'Compra no crédito vai para a fatura do cartão; entrada e saída vão para o livro caixa.';
    }

    private function say(WhatsAppSetting $s, string $to, string $text): void
    {
        try {
            $this->client($s)->sendText($to, $text);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp: falha ao responder', ['error' => $e->getMessage()]);
        }
    }
}
