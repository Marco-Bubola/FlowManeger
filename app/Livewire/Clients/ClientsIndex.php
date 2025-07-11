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
    public int $perPage = 18;
    public int $month;
    public int $year;

    // Modal de exclusão
    public bool $showDeleteModal = false;
    public $deletingClient = null;

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
        'perPage' => ['except' => 18],
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
            $this->showDeleteModal = true;
        }
    }

    public function deleteClient(): void
    {
        if ($this->deletingClient) {
            // Verificar se o cliente pertence ao usuário autenticado
            if ($this->deletingClient->user_id === Auth::id()) {
                $this->deletingClient->delete();
                
                session()->flash('success', 'Cliente deletado com sucesso!');
            } else {
                session()->flash('error', 'Você não tem permissão para deletar este cliente.');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingClient = null;
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingClient = null;
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

        // Filtro de pesquisa por nome
        if (!empty($this->search)) {
            $searchTerm = str_replace('.', '', $this->search);
            $query->where('name', 'like', '%' . $searchTerm . '%');
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
