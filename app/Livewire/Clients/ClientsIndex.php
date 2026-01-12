<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Sale;
use App\Models\ConsortiumParticipant;
use App\Exports\VendaDetalhadaExport;
use App\Exports\ConsortiumParticipantExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientsIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $filter = '';
    public int $perPage = 12;
    public int $month;
    public int $year;

    // Novos filtros avançados
    public string $statusFilter = '';
    public string $periodFilter = '';
    public string $dateFilter = '';
    // Ordenação padrão: mais recentes adicionados primeiro
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $minValue = '';
    public string $maxValue = '';
    public string $minSales = '';
    public string $maxSales = '';

    // Propriedades para ações em massa
    public $selectedClients = [];
    public $selectAll = false;
    public bool $showAdvancedFilters = false;

    // Modal de exclusão
    public bool $showDeleteModal = false;
    public $deletingClient = null;
    public $clientToDelete = null;

    // Modal de export
    public bool $showExportModal = false;
    public $selectedClientForExport = null;

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
        'perPage' => ['except' => 12],
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

                session()->flash('success', 'Cliente deletado com sucesso!');
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

    // Métodos para controle do modal de export
    public function openExportModal($clientId): void
    {
        $this->selectedClientForExport = Client::with(['sales', 'consortiumParticipants.consortium'])->find($clientId);
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
        $this->selectedClientForExport = null;
    }

    public function exportClients(): void
    {
        // Aqui você pode implementar a lógica de exportação
        session()->flash('info', 'Exportação iniciada! O arquivo será enviado por email.');
    }

    // Métodos para seleção em massa - primeira definição
    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            // Buscar IDs dos clientes da página atual
            $query = Client::where('user_id', Auth::id());

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('city', 'like', '%' . $this->search . '%');
                });
            }

            $this->selectedClients = $query->pluck('id')->toArray();
        } else {
            $this->selectedClients = [];
        }
    }

    public function updatedSelectedClients()
    {
        // Atualizar selectAll baseado na seleção atual
        $this->selectAll = !empty($this->selectedClients);
    }

    // Ações em massa
    public function bulkDelete()
    {
        if (!empty($this->selectedClients)) {
            $deletedCount = Client::whereIn('id', $this->selectedClients)
                  ->where('user_id', Auth::id())
                  ->delete();

            $this->selectedClients = [];
            $this->selectAll = false;

            session()->flash('success', $deletedCount . ' clientes deletados com sucesso!');
            $this->resetPage();
        }
    }

    public function bulkExport()
    {
        if (!empty($this->selectedClients)) {
            // Lógica para exportar clientes selecionados
            session()->flash('success', count($this->selectedClients) . ' clientes exportados com sucesso!');
        }
    }

    // Estatísticas da página
    public function getClientStatsProperty()
    {
        $stats = collect();

        // Total de clientes
        $stats->put('total', Client::where('user_id', Auth::id())->count());

        // Clientes novos este mês
        $stats->put('new_this_month', Client::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->startOfMonth())
            ->count());

        // Clientes VIP (10+ compras)
        $stats->put('vip', Client::where('user_id', Auth::id())
            ->withCount('sales')
            ->having('sales_count', '>=', 10)
            ->count());

        // Total de vendas de todos os clientes
        $stats->put('total_sales', Sale::whereIn('client_id',
            Client::where('user_id', Auth::id())->pluck('id')
        )->sum('total'));

        // Média de vendas por cliente
        $clientsWithSales = Client::where('user_id', Auth::id())
            ->whereHas('sales')
            ->count();
        $stats->put('avg_sales_per_client', $clientsWithSales > 0 ?
            $stats->get('total_sales') / $clientsWithSales : 0);

        // Última venda
        $lastSale = Sale::whereIn('client_id',
            Client::where('user_id', Auth::id())->pluck('id')
        )->latest()->first();
        $stats->put('last_sale', $lastSale ? $lastSale->created_at->diffForHumans() : 'Nenhuma venda');

        return $stats;
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
            if ($this->statusFilter === 'inativo') {
                // Considera inativo quem não compra há mais de 30 dias, por exemplo
                $query->whereDoesntHave('sales', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                });
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // Filtro por período de cadastro
        if (!empty($this->dateFilter)) {
            switch ($this->dateFilter) {
                case 'hoje':
                    $query->whereDate('created_at', now()->toDateString());
                    break;
                case 'semana':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'mes':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'trimestre':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case 'ano':
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

        // Filtros de ordenação usando sortBy e sortDirection
        switch ($this->sortBy) {
            case 'created_at':
                $query->orderBy('created_at', $this->sortDirection);
                break;
            case 'updated_at':
                $query->orderBy('updated_at', $this->sortDirection);
                break;
            case 'name':
                $query->orderBy('name', $this->sortDirection);
                break;
            case 'email':
                $query->orderBy('email', $this->sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $this->sortDirection);
                break;
            case 'phone':
                $query->orderBy('phone', $this->sortDirection);
                break;
            case 'most_sales':
                $query->withCount('sales')->orderBy('sales_count', $this->sortDirection);
                break;
            case 'best_customers':
                $query->withSum('sales', 'total_price')->orderBy('sales_sum_total_price', $this->sortDirection);
                break;
            case 'recent_activity':
                $query->with(['sales' => function($q) {
                    $q->latest()->limit(1);
                }])->get()->sortByDesc(function($client) {
                    return $client->sales->first()?->created_at;
                });
                break;
            default:
                $query->orderBy('created_at', $this->sortDirection);
                break;
        }

        $clients = $query->with(['sales', 'consortiumParticipants'])->paginate($this->perPage);

        return view('livewire.clients.clients-index', [
            'clients' => $clients
        ]);
    }

    /**
     * Define pesquisa rápida
     */
    public function setQuickSearch($type)
    {
        // Limpa filtros existentes para evitar conflitos
        $this->clearFilters();

        switch ($type) {
            case 'ativo':
                $this->statusFilter = 'ativo';
                break;
            case 'premium':
                $this->statusFilter = 'premium';
                break;
            case 'inativos':
                $this->statusFilter = 'inativo';
                break;
            case 'recente':
                $this->dateFilter = 'mes';
                break;
            case 'mais_compras':
                $this->sortBy = 'most_sales';
                $this->sortDirection = 'desc';
                break;
        }

        $this->resetPage();
    }

    /**
     * Limpa todos os filtros
     */
    public function clearFilters()
    {
        $this->reset([
            'search',
            'filter',
            'statusFilter',
            'periodFilter',
            'dateFilter',
            'sortBy',
            'sortDirection',
            'minValue',
            'maxValue',
            'minSales',
            'maxSales',
            'perPage'
        ]);
        $this->resetPage();
    }

    /**
     * Alterna a ordenação: se já estiver na mesma coluna, inverte direção;
     * caso contrário, seleciona a coluna e define direção asc.
     */
    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Propriedades computadas para estatísticas
     */
    public function getActiveClientsProperty()
    {
        return Client::where('user_id', Auth::id())
                    ->where('status', 'ativo')
                    ->count();
    }

    public function getPremiumClientsProperty()
    {
        return Client::where('user_id', Auth::id())
                    ->where('type', 'premium')
                    ->count();
    }

    public function getNewClientsThisMonthProperty()
    {
        return Client::where('user_id', Auth::id())
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
    }

    /**
     * Export venda específica em PDF
     */
    public function exportSalePDF($saleId)
    {
        try {
            $sale = Sale::with([
                'client',
                'saleItems.product',
                'payments',
                'parcelasVenda'
            ])->findOrFail($saleId);

            // Verificar se a venda pertence a um cliente do usuário autenticado
            if ($sale->client->user_id !== Auth::id()) {
                session()->flash('error', 'Você não tem permissão para exportar esta venda.');
                return;
            }

            $pdf = Pdf::loadView('pdfs.sale', compact('sale'));

            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sale->client->name);
            $filename = $clientName . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar venda PDF: ' . $e->getMessage(), [
                'sale_id' => $saleId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erro ao gerar PDF da venda. Tente novamente.');
        }
    }

    /**
     * Export venda específica em Excel
     */
    public function exportSaleExcel($saleId)
    {
        try {
            $sale = Sale::with('client')->findOrFail($saleId);

            // Verificar permissão
            if ($sale->client->user_id !== Auth::id()) {
                session()->flash('error', 'Você não tem permissão para exportar esta venda.');
                return;
            }

            return Excel::download(
                new VendaDetalhadaExport($saleId),
                "venda-{$saleId}-" . now()->format('Y-m-d') . ".xlsx"
            );

        } catch (\Exception $e) {
            Log::error('Erro ao exportar venda Excel: ' . $e->getMessage(), [
                'sale_id' => $saleId,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Erro ao gerar Excel da venda. Tente novamente.');
        }
    }

    /**
     * Export consórcio do participante em PDF
     */
    public function exportConsortiumPDF($participantId)
    {
        try {
            $participant = ConsortiumParticipant::with([
                'consortium',
                'client',
                'payments'
            ])->findOrFail($participantId);

            // Verificar permissão
            if ($participant->client->user_id !== Auth::id()) {
                session()->flash('error', 'Você não tem permissão para exportar este consórcio.');
                return;
            }

            $pdf = Pdf::loadView('exports.consortium-participant-pdf', [
                'participant' => $participant
            ])->setPaper('a4', 'portrait');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, "consorcio-participante-{$participantId}-" . now()->format('Y-m-d') . ".pdf");

        } catch (\Exception $e) {
            Log::error('Erro ao exportar consórcio PDF: ' . $e->getMessage(), [
                'participant_id' => $participantId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erro ao gerar PDF do consórcio. Tente novamente.');
        }
    }

    /**
     * Export consórcio do participante em Excel
     */
    public function exportConsortiumExcel($participantId)
    {
        try {
            $participant = ConsortiumParticipant::with('client')->findOrFail($participantId);

            // Verificar permissão
            if ($participant->client->user_id !== Auth::id()) {
                session()->flash('error', 'Você não tem permissão para exportar este consórcio.');
                return;
            }

            return Excel::download(
                new ConsortiumParticipantExport($participantId),
                "consorcio-participante-{$participantId}-" . now()->format('Y-m-d') . ".xlsx"
            );

        } catch (\Exception $e) {
            Log::error('Erro ao exportar consórcio Excel: ' . $e->getMessage(), [
                'participant_id' => $participantId,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Erro ao gerar Excel do consórcio. Tente novamente.');
        }
    }
}
