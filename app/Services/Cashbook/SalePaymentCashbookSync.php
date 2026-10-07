<?php

namespace App\Services\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Quando o usuário ativa a opção, cada pagamento de venda vira uma receita
 * no livro-caixa (categoria "Vendas"). Editar ou excluir o pagamento
 * atualiza ou remove a receita ligada a ele.
 */
class SalePaymentCashbookSync
{
    public const PREFERENCE = 'sales_to_cashbook';
    private const TYPE_RECEITA = 1;

    public static function enabledFor(?User $user): bool
    {
        return (bool) ($user?->preferences['finance'][self::PREFERENCE] ?? false);
    }

    public static function setEnabled(User $user, bool $enabled): void
    {
        $prefs = $user->preferences ?? [];
        $prefs['finance'][self::PREFERENCE] = $enabled;
        $user->preferences = $prefs;
        $user->save();
    }

    public function created(SalePayment $payment): void
    {
        $this->safely(function () use ($payment) {
            $sale = $payment->sale;
            $user = $sale ? User::find($sale->user_id) : null;
            // "desconto" é abatimento, não dinheiro recebido
            if (! $user || ! self::enabledFor($user) || (float) $payment->amount_paid <= 0
                || $payment->payment_method === 'desconto') {
                return;
            }

            Cashbook::create([
                'user_id' => $user->id,
                'value' => $payment->amount_paid,
                'description' => $this->description($payment),
                'date' => $payment->payment_date ?: now()->toDateString(),
                'is_pending' => false,
                'category_id' => $this->salesCategoryId($user->id),
                'type_id' => self::TYPE_RECEITA,
                'client_id' => $sale->client_id,
                'note' => 'Pagamento de venda',
                'sale_payment_id' => $payment->id,
                'inc_datetime' => now(),
            ]);
        });
    }

    public function updated(SalePayment $payment): void
    {
        $this->safely(function () use ($payment) {
            Cashbook::where('sale_payment_id', $payment->id)->update([
                'value' => $payment->amount_paid,
                'date' => $payment->payment_date ?: now()->toDateString(),
                'edit_datetime' => now(),
            ]);
        });
    }

    public function deleted(SalePayment $payment): void
    {
        $this->safely(fn () => Cashbook::where('sale_payment_id', $payment->id)->delete());
    }

    private function description(SalePayment $payment): string
    {
        $client = $payment->sale?->client?->name;

        return 'Venda #'.$payment->sale_id.($client ? ' - '.$client : '');
    }

    private function salesCategoryId(int $userId): int
    {
        $category = Category::where('user_id', $userId)
            ->where('type', 'transaction')
            ->whereRaw('LOWER(name) = ?', ['vendas'])
            ->first();

        if (! $category) {
            $category = new Category();
            $category->forceFill([
                'name' => 'Vendas',
                'user_id' => $userId,
                'type' => 'transaction',
                'tipo' => 'receita',
                'hexcolor_category' => '#10b981',
                'icone' => 'bi bi-bag-check',
                'is_active' => 1,
            ])->save();
        }

        return $category->id_category;
    }

    /** O pagamento da venda nunca pode falhar por causa do livro-caixa. */
    private function safely(callable $callback): void
    {
        static $ready = null;
        $ready ??= Schema::hasColumn('cashbook', 'sale_payment_id');
        if (! $ready) {
            return;
        }

        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('Falha ao sincronizar pagamento de venda com o livro-caixa', ['error' => $e->getMessage()]);
        }
    }
}
