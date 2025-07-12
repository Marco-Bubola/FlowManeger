<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ClientsIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $filter = '';
    public int $perPage = 16;
    public int $month;
    public int $year;

    // Novos filtros avançados
    public string $statusFilter = '';
    public string $periodFilter = '';
    public string $minValue = '';
    public string $maxValue = '';
    public string $minSales = '';
    public string $maxSales = '';
    public bool $showAdvancedFilters = false;

    // Modal de exclusão
    public bool $showDeleteModal = false;
    public $deletingClient = null;
    public $clientToDelete = null;

    // Estatísticas financeiras
    public $totalClients = 0;
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $averageTicket = 0;
    public $topClient = null;
    public $recentClients = [];
    public $monthlySales = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'periodFilter' => ['except' => ''],
        'perPage' => ['except' => 16],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->loadFinancialData();
    }

    public function loadFinancialData(): void
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfDay();

        // Total de clientes
        $this->totalClients = Client::where('user_id', Auth::id())->count();

        // Vendas do mês
        $salesQuery = Sale::whereHas('client', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        $this->totalSales = $salesQuery->count();
        $this->totalRevenue = $salesQuery->sum('total_price');
        $this->averageTicket = $this->totalSales > 0 ? $this->totalRevenue / $this->totalSales : 0;

        // Cliente com mais compras
        $this->topClient = Client::where('user_id', Auth::id())
            ->withCount(['sales as sales_count' => function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }])
            ->withSum(['sales as sales_total' => function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }], 'total_price')
            ->having('sales_count', '>', 0)
            ->orderBy('sales_total', 'desc')
            ->first();

        // Clientes recentes (últimos 5)
        $this->recentClients = Client::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // Vendas por dia do mês (para gráfico)
        $this->monthlySales = Sale::whereHas('client', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMinValue(): void
    {
        $this->resetPage();
    }

    public function updatingMaxValue(): void
    {
        $this->resetPage();
    }

    public function updatingMinSales(): void
    {
        $this->resetPage();
    }

    public function updatingMaxSales(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->loadFinancialData();
    }

    public function updatedYear(): void
    {
        $this->loadFinancialData();
    }

    public function previousMonth(): void
    {
        if ($this->month == 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
        $this->loadFinancialData();
    }

    public function nextMonth(): void
    {
        if ($this->month == 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
        $this->loadFinancialData();
    }

    public function confirmDelete($clientId): void
    {
        $client = Client::where('id', $clientId)
                       ->where('user_id', Auth::id())
                       ->first();
        
        if ($client) {
            $this->deletingClient = $client;
            $this->clientToDelete = $clientId;
            $this->showDeleteModal = true;
        }
    }

    public function deleteClient(): void
    {
        if ($this->deletingClient) {
            // Verificar se o cliente pertence ao usuário autenticado
            if ($this->deletingClient->user_id === Auth::id()) {
                $this->deletingClient->delete();
                
                session()->flash('message', 'Cliente deletado com sucesso!');
            } else {
                session()->flash('error', 'Você não tem permissão para deletar este cliente.');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingClient = null;
        $this->clientToDelete = null;
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingClient = null;
        $this->clientToDelete = null;
    }

    // Novos métodos para filtros avançados
    public function clearAllFilters(): void
    {
        $this->reset(['search', 'filter', 'statusFilter', 'periodFilter', 'minValue', 'maxValue', 'minSales', 'maxSales']);
        $this->resetPage();
    }

    public function exportClients(): void
    {
        // Aqui você pode implementar a lógica de exportação
        session()->flash('message', 'Exportação iniciada! O arquivo será enviado por email.');
    }

    #[On('client-created')]
    public function refreshClients(): void
    {
        $this->resetPage();
    }

    #[On('client-updated')]
    public function refreshClientsAfterUpdate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Client::where('user_id', Auth::id());

        // Filtro de pesquisa por nome, email, telefone ou cidade
        if (!empty($this->search)) {
            $searchTerm = str_replace('.', '', $this->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('address', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtro por status
        if (!empty($this->statusFilter)) {
            switch ($this->statusFilter) {
                case 'vip':
                    $query->withCount('sales')->having('sales_count', '>=', 10);
                    break;
                case 'premium':
                    $query->withCount('sales')->having('sales_count', '>=', 5)->having('sales_count', '<', 10);
                    break;
                case 'new':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'inactive':
                    $query->whereDoesntHave('sales', function($q) {
                        $q->where('created_at', '>=', now()->subMonths(6));
                    });
                    break;
            }
        }

        // Filtro por período de cadastro
        if (!empty($this->periodFilter)) {
            switch ($this->periodFilter) {
                case 'today':
                    $query->whereDate('created_at', now()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case 'year':
                    $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
            }
        }

        // Filtro por valor total de compras
        if (!empty($this->minValue) || !empty($this->maxValue)) {
            $query->withSum('sales', 'total_price');
            if (!empty($this->minValue)) {
                $query->having('sales_sum_total_price', '>=', (float)$this->minValue);
            }
            if (!empty($this->maxValue)) {
                $query->having('sales_sum_total_price', '<=', (float)$this->maxValue);
            }
        }

        // Filtro por número de compras
        if (!empty($this->minSales) || !empty($this->maxSales)) {
            $query->withCount('sales');
            if (!empty($this->minSales)) {
                $query->having('sales_count', '>=', (int)$this->minSales);
            }
            if (!empty($this->maxSales)) {
                $query->having('sales_count', '<=', (int)$this->maxSales);
            }
        }

        // Filtros de ordenação
        switch ($this->filter) {
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'updated_at':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'most_sales':
                $query->withCount('sales')->orderBy('sales_count', 'desc');
                break;
            case 'best_customers':
                $query->withSum('sales', 'total_price')->orderBy('sales_sum_total_price', 'desc');
                break;
            case 'recent_activity':
                $query->with(['sales' => function($q) {
                    $q->latest()->limit(1);
                }])->get()->sortByDesc(function($client) {
                    return $client->sales->first()?->created_at;
                });
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $clients = $query->with('sales')->paginate($this->perPage);

        return view('livewire.clients.clients-index', [
            'clients' => $clients
        ]);
    }
}
