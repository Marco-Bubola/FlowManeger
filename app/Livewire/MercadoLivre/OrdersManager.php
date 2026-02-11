<?php

namespace App\Livewire\MercadoLivre;

use App\Services\MercadoLivre\OrderService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class OrdersManager extends Component
{
    use HasNotifications, WithPagination;

    // Filtros
    public string $searchTerm = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    
    // Detalhes do pedido selecionado
    public ?array $selectedOrder = null;
    public bool $showDetailsModal = false;
    
    // Paginação
    public int $perPage = 20;
    
    // Dados
    public array $orders = [];
    public array $paging = [];
    public bool $loading = false;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Definir datas padrão (últimos 30 dias)
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $this->loadOrders();
    }

    /**
     * Carrega pedidos do Mercado Livre
     */
    public function loadOrders()
    {
        $this->loading = true;

        try {
            $orderService = new OrderService();
            
            $filters = [
                'limit' => $this->perPage,
                'offset' => 0,
            ];
            
            if ($this->statusFilter) {
                $filters['status'] = $this->statusFilter;
            }
            
            if ($this->dateFrom) {
                $filters['date_from'] = $this->dateFrom;
            }
            
            if ($this->dateTo) {
                $filters['date_to'] = $this->dateTo;
            }
            
            $result = $orderService->getOrders($filters);
            
            if ($result['success']) {
                $this->orders = $result['orders'];
                $this->paging = $result['paging'];
                
                // Filtrar localmente por termo de busca se houver
                if ($this->searchTerm) {
                    $this->orders = array_filter($this->orders, function($order) {
                        $searchLower = strtolower($this->searchTerm);
                        $orderId = strtolower($order['id'] ?? '');
                        $buyerNickname = strtolower($order['buyer']['nickname'] ?? '');
                        
                        return str_contains($orderId, $searchLower) || 
                               str_contains($buyerNickname, $searchLower);
                    });
                }
                
                $this->notifySuccess('Pedidos carregados: ' . count($this->orders));
            } else {
                $this->notifyError($result['message'] ?? 'Erro ao carregar pedidos');
                $this->orders = [];
            }
        } catch (\Exception $e) {
            Log::error('Erro ao carregar pedidos ML', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);
            $this->notifyError('Erro ao carregar pedidos: ' . $e->getMessage());
            $this->orders = [];
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Recarregar pedidos ao mudar filtros
     */
    public function updatedStatusFilter()
    {
        $this->loadOrders();
    }

    public function updatedDateFrom()
    {
        $this->loadOrders();
    }

    public function updatedDateTo()
    {
        $this->loadOrders();
    }

    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) >= 3 || $this->searchTerm === '') {
            $this->loadOrders();
        }
    }

    /**
     * Ver detalhes do pedido
     */
    public function viewOrderDetails(string $mlOrderId)
    {
        try {
            $orderService = new OrderService();
            $orderDetails = $orderService->getOrderDetails($mlOrderId);
            
            if ($orderDetails) {
                $this->selectedOrder = $orderDetails;
                $this->showDetailsModal = true;
            } else {
                $this->notifyError('Pedido não encontrado');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do pedido', [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao buscar detalhes: ' . $e->getMessage());
        }
    }

    /**
     * Importar pedido para o sistema
     */
    public function importOrder(string $mlOrderId)
    {
        try {
            $orderService = new OrderService();
            $result = $orderService->importOrder($mlOrderId);
            
            if ($result['success']) {
                $this->notifySuccess('Pedido importado com sucesso!');
                $this->loadOrders(); // Recarregar lista
                $this->closeDetailsModal();
            } else {
                $this->notifyError($result['message'] ?? 'Erro ao importar pedido');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao importar pedido ML', [
                'ml_order_id' => $mlOrderId,
                'error' => $e->getMessage()
            ]);
            $this->notifyError('Erro ao importar: ' . $e->getMessage());
        }
    }

    /**
     * Fechar modal de detalhes
     */
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedOrder = null;
    }

    /**
     * Limpar filtros
     */
    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->statusFilter = '';
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $this->loadOrders();
    }

    /**
     * Helper: Retorna badge de status
     */
    public function getStatusBadge(string $status): array
    {
        $badges = [
            'confirmed' => ['text' => 'Confirmado', 'color' => 'green'],
            'payment_required' => ['text' => 'Aguardando Pagamento', 'color' => 'yellow'],
            'payment_in_process' => ['text' => 'Pagamento em Processo', 'color' => 'blue'],
            'paid' => ['text' => 'Pago', 'color' => 'emerald'],
            'fulfilled' => ['text' => 'Entregue', 'color' => 'green'],
            'cancelled' => ['text' => 'Cancelado', 'color' => 'red'],
            'invalid' => ['text' => 'Inválido', 'color' => 'gray'],
        ];
        
        return $badges[$status] ?? ['text' => ucfirst($status), 'color' => 'gray'];
    }

    /**
     * Helper: Formata valor monetário
     */
    public function formatPrice($value): string
    {
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }

    /**
     * Helper: Formata data
     */
    public function formatDate($date): string
    {
        return Carbon::parse($date)->format('d/m/Y H:i');
    }

    public function render()
    {
        return view('livewire.mercadolivre.orders-manager')
            ->layout('components.layouts.app');
    }
}
