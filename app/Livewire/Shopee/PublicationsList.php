<?php

namespace App\Livewire\Shopee;

use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use App\Models\ShopeeSyncLog;
use App\Services\Shopee\OrderService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Componente de listagem de publicações da Shopee com status de sync e logs de erro.
 */
class PublicationsList extends Component
{
    use HasNotifications, WithPagination;

    public string $searchTerm  = '';
    public string $statusFilter = '';
    public string $syncFilter  = '';
    public bool   $isImporting = false;

    protected $queryString = ['searchTerm', 'statusFilter', 'syncFilter'];

    public function updatedSearchTerm(): void  { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedSyncFilter(): void  { $this->resetPage(); }

    /**
     * Importa pedidos recentes da Shopee manualmente.
     */
    public function importOrders(): void
    {
        $this->isImporting = true;
        try {
            $service = app(OrderService::class);
            $result  = $service->importRecentOrders(Auth::id());
            $msg = "Importação concluída: {$result['imported']} pedido(s) importado(s)" .
                ($result['errors'] > 0 ? ", {$result['errors']} erro(s)." : '.');
            $result['errors'] > 0 ? $this->notifyWarning($msg) : $this->notifySuccess($msg);
        } catch (\Exception $e) {
            $this->notifyError('Erro ao importar pedidos: ' . $e->getMessage());
        } finally {
            $this->isImporting = false;
        }
    }

    public function render()
    {
        $publications = ShopeePublication::where('user_id', Auth::id())
            ->when($this->searchTerm, fn($q) => $q->where('title', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('shopee_item_id', 'like', '%' . $this->searchTerm . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->syncFilter, fn($q) => $q->where('sync_status', $this->syncFilter))
            ->with('products')
            ->latest()
            ->paginate(15);

        // Logs recentes de erro
        $errorLogs = ShopeeSyncLog::where('user_id', Auth::id())
            ->where('status', 'error')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $ordersCount = ShopeeOrder::where('user_id', Auth::id())
            ->whereIn('order_status', ['READY_TO_SHIP', 'PROCESSED'])
            ->count();

        return view('livewire.shopee.publications-list', [
            'publications' => $publications,
            'errorLogs'    => $errorLogs,
            'ordersCount'  => $ordersCount,
        ]);
    }
}
