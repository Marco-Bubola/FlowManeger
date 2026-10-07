<?php

namespace App\Livewire\Gestao;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Lucro por venda no mês: faturamento menos o custo dos itens.
 * O custo vem do preço de custo gravado no item no momento da venda.
 */
class SalesProfit extends Component
{
    #[Url]
    public string $month = '';

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function shiftMonth(int $delta): void
    {
        $this->month = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->addMonths($delta)->format('Y-m');
    }

    public function render()
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->startOfMonth();

        $sales = Sale::with(['client', 'saleItems'])
            ->when(Auth::user()->isAdmin(), fn ($q) => $q->where('user_id', Auth::id()))
            ->whereNotIn('status', ['cancelada', 'orcamento'])
            ->whereBetween('created_at', [$start, $start->copy()->endOfMonth()])
            ->latest()
            ->get();

        $rows = $sales->map(function (Sale $sale) {
            $revenue = (float) $sale->total_price;
            $cost = $sale->saleItems->sum(fn ($i) => (float) $i->price * (int) $i->quantity);
            $missingCost = $sale->saleItems->contains(fn ($i) => (float) $i->price <= 0);

            return [
                'sale' => $sale,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
                'margin' => $revenue > 0 ? ($revenue - $cost) / $revenue * 100 : 0,
                'missingCost' => $missingCost,
            ];
        });

        $revenue = $rows->sum('revenue');
        $profit = $rows->sum('profit');

        return view('livewire.gestao.sales-profit', [
            'rows' => $rows,
            'start' => $start,
            'summary' => [
                'revenue' => $revenue,
                'cost' => $rows->sum('cost'),
                'profit' => $profit,
                'margin' => $revenue > 0 ? $profit / $revenue * 100 : 0,
                'count' => $rows->count(),
                'ticket' => $rows->count() ? $revenue / $rows->count() : 0,
            ],
        ]);
    }
}
