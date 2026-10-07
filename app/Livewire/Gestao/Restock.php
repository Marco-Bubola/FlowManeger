<?php

namespace App\Livewire\Gestao;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Produtos para repor: estoque abaixo do mínimo, com quanto vendeu nos
 * últimos 30 dias e quantos dias o estoque atual ainda dura.
 */
class Restock extends Component
{
    public int $minimum = 3;

    public function mount(): void
    {
        $this->minimum = (int) (Auth::user()->preferences['stock']['minimum'] ?? 3);
    }

    public function updatedMinimum($value): void
    {
        $this->minimum = max(0, min(9999, (int) $value));

        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['stock']['minimum'] = $this->minimum;
        $user->preferences = $prefs;
        $user->save();
    }

    public function render()
    {
        $products = Product::query()
            ->when(Auth::user()->isAdmin(), fn ($q) => $q->where('user_id', Auth::id()))
            ->where('tipo', '!=', 'kit')
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'ativo'))
            ->where('stock_quantity', '<=', $this->minimum)
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'product_code', 'stock_quantity', 'price', 'image']);

        // Vendas dos últimos 30 dias por produto (vendas não canceladas)
        $sold = SaleItem::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->whereHas('sale', fn ($q) => $q->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('status', ['cancelada', 'orcamento']))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $rows = $products->map(function ($p) use ($sold) {
            $perDay = ((int) ($sold[$p->id] ?? 0)) / 30;

            return [
                'product' => $p,
                'sold30' => (int) ($sold[$p->id] ?? 0),
                'daysLeft' => $perDay > 0 ? (int) floor($p->stock_quantity / $perDay) : null,
            ];
        })->sortBy([
            fn ($a, $b) => ($b['sold30'] <=> $a['sold30']),
        ])->values();

        return view('livewire.gestao.restock', [
            'rows' => $rows,
            'zeroCount' => $products->where('stock_quantity', '<=', 0)->count(),
        ]);
    }
}
