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

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
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

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function confirmDelete($consortiumId)
    {
        $this->deletingConsortium = Consortium::find($consortiumId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->deletingConsortium = null;
        $this->showDeleteModal = false;
    }

    public function deleteConsortium()
    {
        if ($this->deletingConsortium) {
            $this->deletingConsortium->delete();
            session()->flash('message', 'Consórcio excluído com sucesso!');
            $this->cancelDelete();
        }
    }

    public function getPerPageOptions()
    {
        return $this->ultraWindClient ? [16, 32, 48, 64] : [12, 24, 36, 48];
    }

    public function getConsortiumsProperty()
    {
        $query = Consortium::where('user_id', Auth::id())
            ->with(['participants', 'draws']);

        // Filtro de busca
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Filtro de status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Ordenação
        $query->orderBy($this->sortBy, $this->sortDirection);

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
