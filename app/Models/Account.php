<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Onde o dinheiro está: conta corrente, poupança, carteira, investimento.
 * O saldo é o saldo inicial + receitas - despesas lançadas nela no livro-caixa.
 */
class Account extends Model
{
    public const TYPES = [
        'corrente' => ['Conta corrente', 'bi-bank'],
        'poupanca' => ['Poupança', 'bi-piggy-bank'],
        'carteira' => ['Carteira / dinheiro', 'bi-wallet2'],
        'investimento' => ['Investimento', 'bi-graph-up'],
    ];

    protected $fillable = ['user_id', 'name', 'type', 'initial_balance', 'color', 'archived'];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'archived' => 'boolean',
    ];

    public function entries()
    {
        return $this->hasMany(Cashbook::class, 'account_id');
    }

    public function scopeOwned($query, ?int $userId = null)
    {
        return $query->where('user_id', $userId ?? auth()->id());
    }

    public function balance(): float
    {
        $sums = $this->entries()
            ->where('is_pending', false)
            ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE 0 END) as income, SUM(CASE WHEN type_id = 2 THEN value ELSE 0 END) as expense')
            ->first();

        return round((float) $this->initial_balance + (float) ($sums->income ?? 0) - (float) ($sums->expense ?? 0), 2);
    }
}
