<?php

namespace App\Livewire\Gestao;

use App\Models\MlStockLog;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Histórico de entradas e saídas de estoque.
 * Os registros já eram gravados pelo ProductObserver (ml_stock_logs) a cada
 * mudança de estoque; aqui eles ficam visíveis.
 */
class StockMovements extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $direction = ''; // entrada | saida

    #[Url]
    public string $period = '30';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'direction', 'period'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        // Product tem escopo de time/usuário: só os produtos visíveis entram
        $productIds = Product::query()
            ->when(auth()->user()?->isAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('product_code', 'like', '%'.$this->search.'%')))
            ->pluck('id');

        $base = MlStockLog::query()
            ->whereNull('ml_publication_id')
            ->whereIn('product_id', $productIds)
            ->when($this->period !== 'all', fn ($q) => $q->where('created_at', '>=', now()->subDays((int) $this->period)));

        // Resumo do período (sem o filtro de entrada/saída, para comparar os dois lados)
        $totals = [
            'in' => (int) (clone $base)->where('quantity_change', '>', 0)->sum('quantity_change'),
            'out' => (int) abs((clone $base)->where('quantity_change', '<', 0)->sum('quantity_change')),
            'count' => (clone $base)->count(),
        ];

        $logs = (clone $base)
            ->when($this->direction === 'entrada', fn ($q) => $q->where('quantity_change', '>', 0))
            ->when($this->direction === 'saida', fn ($q) => $q->where('quantity_change', '<', 0))
            ->latest('created_at')
            ->paginate(30);

        $products = Product::whereIn('id', $logs->pluck('product_id')->unique())
            ->get(['id', 'name', 'product_code', 'stock_quantity'])
            ->keyBy('id');

        return view('livewire.gestao.stock-movements', compact('logs', 'products', 'totals'));
    }
}
