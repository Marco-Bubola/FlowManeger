<?php

namespace App\Livewire\MercadoLivre;

use App\Models\MlPublication;
use App\Services\MercadoLivre\MlStockSyncService;
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
                ->whereIn('order_status', ['paid', 'confirmed'])
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
            if (!$this->publication->ml_item_id || str_starts_with($this->publication->ml_item_id, 'TEMP_')) {
                $this->notifyError('Esta publicação ainda não foi publicada no Mercado Livre');
                return;
            }

            $syncService = app(MlStockSyncService::class);

            // Traz do ML o que mudou lá (título, preço, status...)
            $fetch = $syncService->fetchPublicationFromMercadoLivre($this->publication);
            if (!$fetch['success']) {
                $this->notifyError('Erro ao buscar no ML: ' . $fetch['message']);
                return;
            }

            // Envia a quantidade calculada pelo estoque local (ignorado se não há produtos vinculados)
            $push = $syncService->syncQuantityToMercadoLivre($this->publication->refresh());
            if (!$push['success']) {
                $this->notifyWarning('Dados atualizados do ML, mas falhou ao enviar a quantidade: ' . $push['message']);
            } else {
                $this->notifySuccess('Publicação sincronizada com o Mercado Livre');
            }

            $this->publication->refresh()->load(['products.category', 'stockLogs.product', 'orders', 'user']);
            $this->loadStats();
            $this->loadStockHistory();
        } catch (\Exception $e) {
            $this->notifyError('Erro ao sincronizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.show-publication');
    }
}
