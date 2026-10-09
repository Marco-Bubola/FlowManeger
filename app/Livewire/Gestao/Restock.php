<?php

namespace App\Livewire\Gestao;

use App\Models\Product;
use App\Services\Stock\LowStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Produtos para repor: estoque abaixo do mínimo (global ou do próprio produto),
 * com quanto vendeu nos últimos 30 dias, quantos dias o estoque atual ainda dura
 * e quanto repor. Kits entram pelo que dá para montar com os componentes.
 */
class Restock extends Component
{
    public int $minimum = 3;

    public function mount(): void
    {
        $this->minimum = LowStockService::userMinimum(Auth::user());
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

    /** Mínimo próprio do produto (vazio = usa o mínimo global). */
    public function setMinStock(int $productId, $value): void
    {
        $product = $this->baseQuery()->whereKey($productId)->first();
        if (! $product) {
            return;
        }

        $value = ($value === '' || $value === null) ? null : max(0, min(9999, (int) $value));
        Product::withoutGlobalScopes()->whereKey($product->id)->update(['min_stock' => $value]);

        try {
            app(LowStockService::class)->checkProducts((int) ($product->user_id ?: Auth::id()), [$product->id]);
        } catch (\Throwable $e) {
            // alerta é secundário; a alteração do mínimo já foi salva
        }
    }

    protected function baseQuery()
    {
        return Product::query()
            ->when(Auth::user()->isAdmin(), fn ($q) => $q->where('user_id', Auth::id()))
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'ativo'))
            ->where(fn ($q) => $q->whereNull('is_variation_parent')->orWhere('is_variation_parent', false));
    }

    public function render()
    {
        $cols = ['id', 'user_id', 'name', 'product_code', 'stock_quantity', 'min_stock', 'price', 'image', 'tipo', 'variation_value'];

        $simple = $this->baseQuery()
            ->where('tipo', '!=', 'kit')
            ->whereRaw('stock_quantity <= COALESCE(min_stock, ?)', [$this->minimum])
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->limit(300)
            ->get($cols)
            ->map(fn ($p) => ['product' => $p, 'stock' => max(0, (int) $p->stock_quantity), 'isKit' => false]);

        $kits = $this->baseQuery()
            ->where('tipo', 'kit')
            ->limit(300)
            ->get($cols)
            ->map(fn ($p) => ['product' => $p, 'stock' => $p->availableStock(), 'isKit' => true])
            ->filter(fn ($r) => $r['stock'] <= ($r['product']->min_stock ?? $this->minimum));

        $all = $simple->concat($kits);
        $sold = LowStockService::soldUnits($all->map(fn ($r) => $r['product']->id)->all());

        $rows = $all->map(function ($r) use ($sold) {
            $p = $r['product'];
            $min = $p->min_stock !== null ? (int) $p->min_stock : $this->minimum;
            $sold30 = (int) ($sold[$p->id] ?? 0);
            $s = LowStockService::suggestion($r['stock'], $sold30, $min);

            return $r + [
                'sold30' => $sold30,
                'avgDaily' => $s['avg_daily'],
                'daysLeft' => $s['days_left'],
                'suggested' => $s['suggested'],
            ];
        })->sortBy([
            fn ($a, $b) => ($b['sold30'] <=> $a['sold30']),
        ])->values();

        return view('livewire.gestao.restock', [
            'rows' => $rows,
            'zeroCount' => $rows->where('stock', '<=', 0)->count(),
        ]);
    }
}
