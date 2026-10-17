<?php

namespace App\Services\FinanceCapture;

use App\Models\Account;
use App\Models\Bank;
use App\Models\BankSyncTransaction;
use App\Models\Cashbook;
use App\Models\Category;
use App\Models\FinanceCapture;
use App\Models\Invoice;
use App\Models\InvoiceCategoryLearning;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Acha o cartão/conta, a categoria e grava a compra na fatura ou o lançamento
 * no livro caixa, sem duplicar o que já foi lançado por outro caminho.
 */
class EntryWriter
{
    public const TYPE_INCOME = 1;  // type.id_type de receita
    public const TYPE_EXPENSE = 2; // type.id_type de despesa

    public function banks(int $userId): Collection
    {
        return Bank::where('user_id', $userId)->orderBy('name')->get();
    }

    public function accounts(int $userId): Collection
    {
        return Account::where('user_id', $userId)->where('archived', false)->orderBy('name')->get();
    }

    public function findBank(int $userId, ?string $institution, ?string $last4 = null): ?Bank
    {
        $banks = $this->banks($userId);
        if ($banks->count() === 1 && ! $institution) {
            return $banks->first();
        }

        $matches = $banks->filter(fn (Bank $b) => Institutions::matches($b->name, $institution)
            || Institutions::matches($b->description, $institution));
        if ($last4 && $matches->count() !== 1) {
            $byDigits = $banks->filter(fn (Bank $b) => str_contains((string) $b->name . ' ' . $b->description, $last4));
            if ($byDigits->count() === 1) {
                return $byDigits->first();
            }
        }

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public function findAccount(int $userId, ?string $institution): ?Account
    {
        if (! $institution) {
            return null;
        }
        $matches = $this->accounts($userId)->filter(fn (Account $a) => Institutions::matches($a->name, $institution));
        // Conta corrente vence poupança/investimento do mesmo banco
        $corrente = $matches->where('type', 'corrente');
        $pool = $corrente->count() === 1 ? $corrente : $matches;

        return $pool->count() === 1 ? $pool->first() : null;
    }

    /** Categoria aprendida pela descrição; senão "Outros"; senão a primeira que serve. */
    public function guessCategory(int $userId, string $description, bool $income): ?int
    {
        $learned = InvoiceCategoryLearning::findCategoryForDescription($description, $userId);
        if ($learned && Category::where('id_category', $learned)->where('user_id', $userId)->exists()) {
            return (int) $learned;
        }

        $base = Category::where('user_id', $userId)->where('is_active', 1)->where('type', 'transaction');
        $tipo = $income ? 'receita' : 'gasto';
        $pick = (clone $base)->where('name', 'Outros')->whereIn('tipo', [$tipo, 'ambos'])->first()
            ?? (clone $base)->where('name', 'Outros')->first()
            ?? (clone $base)->whereIn('tipo', [$tipo, 'ambos'])->orderBy('id_category')->first()
            ?? (clone $base)->orderBy('id_category')->first();

        return $pick?->id_category;
    }

    /** Lançamento igual já existente (mesmo valor, cartão/conta e data com até 1 dia de diferença). */
    public function findExisting(int $userId, string $kind, float $amount, string $date, ?int $bankId, ?int $accountId, ?string $installments = null, bool $fromBank = false): ?array
    {
        $from = Carbon::parse($date)->subDay()->toDateString();
        $to = Carbon::parse($date)->addDay()->toDateString();

        if ($kind === FinanceCapture::KIND_CARD) {
            if (! $bankId) {
                return null;
            }
            $taken = $this->takenIds($userId, 'invoice', $fromBank);
            $row = Invoice::where('user_id', $userId)->where('id_bank', $bankId)
                ->where('value', round($amount, 2))
                ->whereBetween('invoice_date', [$from, $to])
                ->when($installments, fn ($q) => $q->where(fn ($w) => $w->where('installments', $installments)->orWhereNull('installments')->orWhere('installments', 'Compra à vista')))
                ->whereNotIn('id_invoice', $taken)
                ->orderBy('id_invoice')->first();

            return $row ? ['invoice', $row->id_invoice] : null;
        }

        $taken = $this->takenIds($userId, 'cashbook', $fromBank);
        $row = Cashbook::where('user_id', $userId)
            ->where('type_id', $kind === FinanceCapture::KIND_INCOME ? self::TYPE_INCOME : self::TYPE_EXPENSE)
            ->where('value', round($amount, 2))
            ->whereBetween('date', [$from, $to])
            ->when($accountId, fn ($q) => $q->where(fn ($w) => $w->where('account_id', $accountId)->orWhereNull('account_id')))
            ->whereNotIn('id', $taken)
            ->orderBy('id')->first();

        return $row ? ['cashbook', $row->id] : null;
    }

    /**
     * Lançamentos que já têm dono e não podem ser ligados de novo. O banco
     * conectado pode ligar num lançamento feito por print (é o mesmo gasto
     * chegando depois); só não liga em outro que já veio do banco.
     */
    private function takenIds(int $userId, string $type, bool $fromBank): array
    {
        $bank = BankSyncTransaction::where('user_id', $userId)->where('target_type', $type)->pluck('target_id')->all();
        if ($fromBank) {
            return $bank;
        }

        return array_merge($bank, FinanceCapture::where('user_id', $userId)->where('status', 'saved')->where('target_type', $type)->pluck('target_id')->all());
    }

    /**
     * Grava e devolve [tipo, id]. $data: kind, amount, description, date,
     * installments_total, installment_number, bank_id, account_id, category_id, note.
     */
    public function create(int $userId, array $data): array
    {
        $kind = $data['kind'];
        $amount = round((float) $data['amount'], 2);

        if ($kind === FinanceCapture::KIND_CARD) {
            $total = max(1, (int) ($data['installments_total'] ?? 1));
            $number = max(1, (int) ($data['installment_number'] ?? 1));
            // A fatura guarda o valor da parcela; as próximas aparecem na previsão
            $value = ($data['amount_is_installment'] ?? false) ? $amount : round($amount / $total, 2);
            $invoice = Invoice::create([
                'id_bank' => $data['bank_id'],
                'description' => mb_substr($data['description'], 0, 255),
                'installments' => $total > 1 ? "{$number} de {$total}" : 'Compra à vista',
                'value' => $value,
                'user_id' => $userId,
                'category_id' => $data['category_id'],
                'invoice_date' => $data['date'],
            ]);
            if (! empty($data['category_id'])) {
                InvoiceCategoryLearning::learn($data['description'], $data['category_id'], $userId);
            }

            return ['invoice', $invoice->id_invoice];
        }

        $entry = Cashbook::create([
            'user_id' => $userId,
            'value' => $amount,
            'description' => mb_substr($data['description'], 0, 255),
            'date' => $data['date'],
            'is_pending' => false,
            'category_id' => $data['category_id'],
            'type_id' => $kind === FinanceCapture::KIND_INCOME ? self::TYPE_INCOME : self::TYPE_EXPENSE,
            'account_id' => $data['account_id'] ?? null,
            'note' => $data['note'] ?? null,
            'inc_datetime' => now(),
        ]);

        return ['cashbook', $entry->id];
    }

    public function delete(string $type, int $id, int $userId): void
    {
        match ($type) {
            'invoice' => Invoice::where('id_invoice', $id)->where('user_id', $userId)->delete(),
            'cashbook' => Cashbook::where('id', $id)->where('user_id', $userId)->delete(),
            default => null,
        };
    }
}
