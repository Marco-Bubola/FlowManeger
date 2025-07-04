<?php

namespace App\Livewire\Clients;

use App\Models\Client;
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

    // Modal de exclusão
    public ?Client $deletingClient = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'perPage' => ['except' => 18],
        'page' => ['except' => 1],
    ];

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

    public function confirmDelete(Client $client): void
    {
        $this->deletingClient = $client;
        $this->showDeleteModal = true;
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
