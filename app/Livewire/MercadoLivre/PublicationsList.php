<?php

namespace App\Livewire\MercadoLivre;

use App\Models\MlPublication;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PublicationsList extends Component
{
    use WithPagination, HasNotifications;
    
    public string $search = '';
    public string $statusFilter = 'all';
    public string $typeFilter = 'all';
    public string $syncFilter = 'all';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'syncFilter' => ['except' => 'all'],
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatingSyncFilter()
    {
        $this->resetPage();
    }
    
    public function getPublications()
    {
        $query = MlPublication::query()
            ->with(['products', 'user'])
            ->where('user_id', Auth::id());
        
        // Filtro de busca
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('ml_item_id', 'like', "%{$this->search}%");
            });
        }
        
        // Filtro de status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        // Filtro de tipo
        if ($this->typeFilter !== 'all') {
            $query->where('publication_type', $this->typeFilter);
        }
        
        // Filtro de sincronização
        if ($this->syncFilter !== 'all') {
            $query->where('sync_status', $this->syncFilter);
        }
        
        // Ordenação: Prontos para publicar primeiro, depois publicados, depois pendentes
        return $query
            ->orderByRaw("
                CASE 
                    WHEN sync_status = 'synced' AND status = 'active' THEN 1
                    WHEN ml_item_id IS NOT NULL AND ml_item_id != '' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
    
    public function render()
    {
        $publications = $this->getPublications();
        
        $stats = [
            'total' => MlPublication::where('user_id', Auth::id())->count(),
            'active' => MlPublication::where('user_id', Auth::id())->where('status', 'active')->count(),
            'kits' => MlPublication::where('user_id', Auth::id())->where('publication_type', 'kit')->count(),
            'errors' => MlPublication::where('user_id', Auth::id())->where('sync_status', 'error')->count(),
        ];
        
        return view('livewire.mercadolivre.publications-list', [
            'publications' => $publications,
            'stats' => $stats,
        ]);
    }
}
