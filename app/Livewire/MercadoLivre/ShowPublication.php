<?php

namespace App\Livewire\MercadoLivre;

use App\Models\MlPublication;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowPublication extends Component
{
    use HasNotifications;

    public MlPublication $publication;
    public array $stockHistory = [];
    public array $stats = [];

    public function mount(MlPublication $publication)
    {
        // Verificar se a publicação pertence ao usuário
        if ($publication->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para visualizar esta publicação.');
        }

        $this->publication = $publication->load([
            'products.category', 
            'stockLogs.product', 
            'orders',
            'user'
        ]);

        $this->loadStats();
        $this->loadStockHistory();
    }

    protected function loadStats()
    {
        $this->stats = [
            'total_products' => $this->publication->products->count(),
            'total_stock_available' => $this->publication->calculateAvailableQuantity(),
            'total_sales' => $this->publication->orders()->count(),
            'total_revenue' => $this->publication->orders()
                ->where('order_status', 'completed')
                ->sum('total_amount'),
            'stock_logs_count' => $this->publication->stockLogs()->count(),
        ];
    }

    protected function loadStockHistory()
    {
        $this->stockHistory = $this->publication->stockLogs()
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function syncToMercadoLivre()
    {
        try {
            $this->publication->syncQuantityToMl();
            $this->notifySuccess('Sincronização iniciada com sucesso!');
            $this->publication->refresh();
        } catch (\Exception $e) {
            $this->notifyError('Erro ao sincronizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.show-publication');
    }
}
