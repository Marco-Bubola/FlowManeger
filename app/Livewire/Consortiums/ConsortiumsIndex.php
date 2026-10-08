<?php

namespace App\Livewire\Consortiums;

use App\Models\Consortium;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ConsortiumsIndex extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $statusFilter = '';
    public string $quickFilter = '';
    public string $dateStart = '';
    public string $dateEnd = '';
    public int $perPage = 12;
    public bool $fullHdLayout = false;
    public bool $ultraWindClient = false;

    // Ordenação
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    // Modal de exclusão
    public ?Consortium $deletingConsortium = null;
    public bool $showDeleteModal = false;

    // Modal de dicas
    public bool $showTipsModal = false;

    // Filtros avançados
    public bool $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'quickFilter' => ['except' => ''],
        'dateStart' => ['except' => ''],
        'dateEnd' => ['except' => ''],
        'perPage' => ['except' => 12],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->ultraWindClient = $this->isUltraWind();
    }

    private function isUltraWind(): bool
    {
        $clientName = env('CLIENT_NAME', config('app.client_name', ''));
        return strtolower(trim($clientName)) === 'ultra wind';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage($value)
    {
        $this->resetPage();
    }

    public function sortByField($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->quickFilter = '';
        $this->dateStart = '';
        $this->dateEnd = '';
        $this->perPage = $this->ultraWindClient ? 16 : 12;
        $this->resetPage();
        session()->flash('success', 'Filtros limpos com sucesso!');
    }

    public function setQuickFilter($filter)
    {
        $this->quickFilter = $filter;

        switch ($filter) {
            case 'today':
                $this->dateStart = now()->format('Y-m-d');
                $this->dateEnd = now()->format('Y-m-d');
                break;
            case 'week':
                $this->dateStart = now()->startOfWeek()->format('Y-m-d');
                $this->dateEnd = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->dateStart = now()->startOfMonth()->format('Y-m-d');
                $this->dateEnd = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'active':
                $this->statusFilter = 'active';
                $this->dateStart = '';
                $this->dateEnd = '';
                break;
            case 'completed':
                $this->statusFilter = 'completed';
                $this->dateStart = '';
                $this->dateEnd = '';
                break;
            case 'cancelled':
                $this->statusFilter = 'cancelled';
                $this->dateStart = '';
                $this->dateEnd = '';
                break;
        }

        $this->resetPage();
    }

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function confirmDelete($consortiumId)
    {
        $this->deletingConsortium = Consortium::where('user_id', Auth::id())->find($consortiumId);
        $this->showDeleteModal = (bool) $this->deletingConsortium;
    }

    public function cancelDelete()
    {
        $this->deletingConsortium = null;
        $this->showDeleteModal = false;
    }

    public function deleteConsortium()
    {
        if (! $this->deletingConsortium || $this->deletingConsortium->user_id !== Auth::id()) {
            $this->cancelDelete();
            return;
        }

        // Mesma regra da tela do consórcio: com gente ou sorteio, só desativar.
        if ($this->deletingConsortium->participants()->exists() || $this->deletingConsortium->draws()->exists()) {
            session()->flash('error', 'Este consórcio tem participantes ou sorteios. Desative em vez de excluir, para não perder o histórico.');
            $this->cancelDelete();
            return;
        }

        $this->deletingConsortium->delete();
        session()->flash('success', 'Consórcio excluído com sucesso!');
        $this->cancelDelete();
    }

    public function getPerPageOptions()
    {
        return $this->ultraWindClient ? [16, 32, 48, 64] : [12, 24, 36, 48];
    }

    public function getConsortiumsProperty()
    {
        // Números dos cards numa consulta só (antes eram ~8 por card).
        $query = Consortium::where('user_id', Auth::id())
            ->withCount([
                'participants as members_count' => fn ($q) => $q->where('status', '!=', 'quit'),
                'participants as contemplated_total' => fn ($q) => $q->where('is_contemplated', true),
                'participants as eligible_total' => fn ($q) => Consortium::filterEligible($q),
                'draws',
                'payments as overdue_total' => fn ($q) => $q->where('consortium_payments.status', 'pending')->whereDate('consortium_payments.due_date', '<', today()),
            ])
            ->withSum('participants as collected_total', 'total_paid')
            ->withMax('draws as last_draw_at', 'draw_date');

        // Filtro de busca
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Filtro de status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filtro de data
        if ($this->dateStart) {
            $query->whereDate('created_at', '>=', $this->dateStart);
        }

        if ($this->dateEnd) {
            $query->whereDate('created_at', '<=', $this->dateEnd);
        }

        // Ordenação
        $sort = in_array($this->sortBy, ['created_at', 'name', 'monthly_value', 'start_date', 'status'], true) ? $this->sortBy : 'created_at';
        $query->orderBy($sort, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.consortiums.consortiums-index', [
            'consortiums' => $this->consortiums,
            'perPageOptions' => $this->getPerPageOptions(),
            'totalActive' => Consortium::where('user_id', Auth::id())->where('status', 'active')->count(),
            'totalCompleted' => Consortium::where('user_id', Auth::id())->where('status', 'completed')->count(),
            'totalParticipants' => Auth::user()->consortiums()->withCount('participants')->get()->sum('participants_count'),
        ]);
    }
}
