<?php

namespace App\Livewire\Gestao;

use App\Models\Sale;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Contas a receber: tudo o que os clientes ainda devem, num lugar só.
 * Junta parcelas pendentes de vendas parceladas e o saldo em aberto de
 * vendas à vista.
 */
class Receivables extends Component
{
    public string $filter = 'todas'; // todas | vencidas | semana | mes

    public function render()
    {
        $today = now()->startOfDay();

        $salesQuery = fn () => Sale::query()
            ->when(Auth::user()->isAdmin(), fn ($q) => $q->where('user_id', Auth::id()))
            ->whereNotIn('status', ['cancelada', 'orcamento']);

        // Parcelas pendentes
        $installments = VendaParcela::with('sale.client')
            ->where('status', 'pendente')
            ->whereIn('sale_id', $salesQuery()->select('id'))
            ->get()
            ->map(fn ($p) => [
                'key' => 'p'.$p->id,
                'sale' => $p->sale,
                'label' => 'Parcela '.$p->numero_parcela,
                'value' => (float) $p->valor,
                'due' => $p->data_vencimento ? Carbon::parse($p->data_vencimento)->startOfDay() : null,
            ]);

        // Vendas não parceladas com saldo em aberto
        $open = $salesQuery()
            ->with('client')
            ->where(fn ($q) => $q->whereNull('tipo_pagamento')->orWhere('tipo_pagamento', '!=', 'parcelado'))
            ->whereColumn('amount_paid', '<', 'total_price')
            ->get()
            ->map(fn ($s) => [
                'key' => 's'.$s->id,
                'sale' => $s,
                'label' => 'Saldo da venda',
                'value' => round((float) $s->total_price - (float) $s->amount_paid, 2),
                'due' => $s->created_at?->copy()->startOfDay(),
            ])
            ->filter(fn ($r) => $r['value'] > 0.009);

        $all = $installments->concat($open)
            ->sortBy(fn ($r) => $r['due']?->timestamp ?? PHP_INT_MAX)
            ->values();

        $rows = $all->filter(fn ($r) => match ($this->filter) {
            'vencidas' => $r['due'] && $r['due']->lt($today),
            'semana' => $r['due'] && $r['due']->between($today, $today->copy()->addDays(7)),
            'mes' => $r['due'] && $r['due']->between($today, $today->copy()->endOfMonth()),
            default => true,
        })->values();

        return view('livewire.gestao.receivables', [
            'rows' => $rows,
            'today' => $today,
            'totals' => [
                'all' => $all->sum('value'),
                'overdue' => $all->filter(fn ($r) => $r['due'] && $r['due']->lt($today))->sum('value'),
                'week' => $all->filter(fn ($r) => $r['due'] && $r['due']->between($today, $today->copy()->addDays(7)))->sum('value'),
            ],
        ]);
    }
}
