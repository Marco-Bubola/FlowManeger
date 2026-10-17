<?php

namespace App\Services\FinanceCapture;

use App\Models\FinanceCapture;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Lançar por print: lê o print/texto, monta o lançamento, grava na fatura do
 * cartão certo ou no livro caixa, e permite desfazer.
 */
class CaptureService
{
    public function __construct(private EntryWriter $writer, private PrintReader $reader)
    {
    }

    /**
     * @return array{captures: FinanceCapture[], error: ?string}
     */
    public function fromImage(int $userId, string $bytes, string $mime, string $source, bool $autoSave, array $extra = []): array
    {
        $path = 'finance-prints/' . $userId . '/' . now()->format('Ym') . '/' . bin2hex(random_bytes(8)) . '.' . (str_contains($mime, 'png') ? 'png' : 'jpg');
        Storage::disk('local')->put($path, $bytes);

        $known = $this->writer->banks($userId)->pluck('name')
            ->merge($this->writer->accounts($userId)->pluck('name'))->unique()->values()->all();
        $read = $this->reader->readImage($bytes, $mime, $known);

        $items = $read['items'];
        // A IA leu o texto mas não montou itens: tenta o leitor de texto
        if (! $items && $read['raw_text']) {
            $parsed = NotificationParser::parse($read['raw_text']);
            $items = $parsed ? [$parsed] : [];
        }

        if (! $items) {
            $failed = FinanceCapture::create($extra + [
                'user_id' => $userId, 'source' => $source, 'status' => 'failed',
                'image_path' => $path, 'raw_text' => $read['raw_text'], 'error' => $read['error'] ?? 'Não achei nenhum valor no print.',
            ]);

            return ['captures' => [$failed], 'error' => $failed->error];
        }

        $captures = [];
        foreach ($items as $i => $item) {
            // O id da mensagem do WhatsApp é único: só o primeiro item fica com ele
            $captures[] = $this->build($userId, $item, $source, $autoSave, ($i === 0 ? $extra : array_diff_key($extra, ['wa_message_id' => 1])) + [
                'image_path' => $path, 'raw_text' => $read['raw_text'],
            ]);
        }

        return ['captures' => $captures, 'error' => null];
    }

    /**
     * Texto colado (uma ou várias notificações separadas por linha em branco).
     * @return array{captures: FinanceCapture[], error: ?string}
     */
    public function fromText(int $userId, string $text, string $source, bool $autoSave, array $extra = []): array
    {
        $blocks = preg_split('/\n\s*\n/', trim($text)) ?: [];
        $captures = [];
        foreach ($blocks as $i => $block) {
            $item = NotificationParser::parse($block);
            if ($item) {
                $captures[] = $this->build($userId, $item, $source, $autoSave, ($i === 0 ? $extra : array_diff_key($extra, ['wa_message_id' => 1])) + ['raw_text' => $block]);
            }
        }

        return ['captures' => $captures, 'error' => $captures ? null : 'Não achei valor (R$) no texto.'];
    }

    /** Monta o rascunho, escolhe cartão/conta/categoria e, se der, já grava. */
    public function build(int $userId, array $item, string $source, bool $autoSave, array $extra = []): FinanceCapture
    {
        $kind = $item['kind'] ?? null;
        $total = max(1, (int) ($item['installments_total'] ?? 1));
        $bank = $kind === FinanceCapture::KIND_CARD ? $this->writer->findBank($userId, $item['institution'] ?? null, $item['card_last4'] ?? null) : null;
        $account = in_array($kind, [FinanceCapture::KIND_INCOME, FinanceCapture::KIND_EXPENSE], true) ? $this->writer->findAccount($userId, $item['institution'] ?? null) : null;

        $capture = FinanceCapture::create($extra + [
            'user_id' => $userId,
            'source' => $source,
            'status' => 'review',
            'kind' => $kind,
            'amount' => $item['amount'],
            'description' => $item['description'] ?? null,
            'date' => $item['date'] ?? now()->toDateString(),
            'installments' => $total > 1 ? "1 de {$total}" : null,
            'institution' => $item['institution'] ?? null,
            'bank_id' => $bank?->id_bank,
            'account_id' => $account?->id,
            'category_id' => $this->writer->guessCategory($userId, (string) ($item['description'] ?? ''), $kind === FinanceCapture::KIND_INCOME),
            'parsed' => $item,
        ]);

        $lowConfidence = isset($item['confidence']) && $item['confidence'] !== null && $item['confidence'] < 0.6;
        // Mesmo print mandado de novo: não grava sozinho (se for outra compra igual, a pessoa grava no app)
        if ($twin = $this->twin($capture)) {
            $capture->update(['error' => 'Parece o mesmo lançamento de ' . $twin->created_at->format('d/m H:i') . '. Se for outro igual, grave aqui.']);

            return $capture->fresh();
        }
        if ($autoSave && ! $lowConfidence && $this->missing($capture) === null) {
            $this->save($capture);
        }

        return $capture->fresh();
    }

    /** Lançamento por print já gravado com o mesmo tipo, valor, descrição, data e destino. */
    public function twin(FinanceCapture $c): ?FinanceCapture
    {
        return FinanceCapture::where('user_id', $c->user_id)->where('id', '!=', $c->id)->where('status', 'saved')
            ->where('kind', $c->kind)->where('amount', $c->amount)->whereDate('date', $c->date)
            ->where('description', $c->description)
            ->where(fn ($q) => $c->bank_id ? $q->where('bank_id', $c->bank_id) : $q->whereNull('bank_id'))
            ->first();
    }

    /** O que falta para gravar (null = pronto). */
    public function missing(FinanceCapture $c): ?string
    {
        return match (true) {
            ! $c->kind => 'tipo',
            ! $c->amount || (float) $c->amount <= 0 => 'valor',
            $c->kind === FinanceCapture::KIND_CARD && ! $c->bank_id => 'cartão',
            ! $c->category_id => 'categoria',
            default => null,
        };
    }

    public function save(FinanceCapture $c): FinanceCapture
    {
        if ($c->status === 'saved' || $this->missing($c) !== null) {
            return $c;
        }

        return DB::transaction(function () use ($c) {
            $total = $c->installments ? (int) (explode(' de ', $c->installments)[1] ?? 1) : 1;
            $value = $c->kind === FinanceCapture::KIND_CARD ? round((float) $c->amount / max(1, $total), 2) : (float) $c->amount;

            $existing = $this->writer->findExisting($c->user_id, $c->kind, $value, $c->date->toDateString(), $c->bank_id, $c->account_id, $c->installments);
            if ($existing) {
                [$type, $id] = $existing;
                $c->fill(['status' => 'saved', 'target_type' => $type, 'target_id' => $id, 'linked_existing' => true])->save();

                return $c;
            }

            [$type, $id] = $this->writer->create($c->user_id, [
                'kind' => $c->kind,
                'amount' => $c->amount,
                'description' => $c->description ?: $c->kindLabel(),
                'date' => $c->date->toDateString(),
                'installments_total' => $total,
                'bank_id' => $c->bank_id,
                'account_id' => $c->account_id,
                'category_id' => $c->category_id,
                'note' => $c->source === 'whatsapp' ? 'Lançado pelo WhatsApp' : 'Lançado por print',
            ]);
            $c->fill(['status' => 'saved', 'target_type' => $type, 'target_id' => $id, 'linked_existing' => false, 'error' => null])->save();

            return $c;
        });
    }

    /** Apaga o lançamento criado pelo print (um que já existia antes só é desligado). */
    public function undo(FinanceCapture $c): FinanceCapture
    {
        if ($c->status !== 'saved') {
            return $c;
        }
        if (! $c->linked_existing && $c->target_type && $c->target_id) {
            $this->writer->delete($c->target_type, $c->target_id, $c->user_id);
        }
        $c->fill(['status' => 'undone', 'target_type' => null, 'target_id' => null, 'linked_existing' => false])->save();

        return $c;
    }

    /** Frase curta: "Compra no cartão · iFood · R$ 45,90 · 12/10 · fatura do Nubank" */
    public function summary(FinanceCapture $c): string
    {
        $parts = [
            $c->kindLabel(),
            $c->description,
            'R$ ' . number_format((float) $c->amount, 2, ',', '.') . ($c->installments ? ' em ' . explode(' de ', $c->installments)[1] . 'x' : ''),
            $c->date?->format('d/m'),
            $c->destinationLabel(),
        ];

        return implode(' · ', array_filter($parts));
    }
}
