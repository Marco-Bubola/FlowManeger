<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Cashbook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ClientTransferencias extends Component
{
    use WithPagination;
    
    public Client $client;
    public $clienteId;
    public $tipo = 'all'; // all, recebidas, enviadas
    
    public function mount($clienteId, $tipo = 'all')
    {
        $this->clienteId = $clienteId;
        $this->tipo = $tipo;
        $this->client = Client::where('user_id', Auth::id())->findOrFail($clienteId);
    }
    
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
        $this->resetPage();
    }
    
    public function render()
    {
        $query = Cashbook::where('client_id', $this->clienteId)
            ->select('id', 'type_id', 'value', 'date', 'description', 'category_id')
            ->with(['category:id_category,name,icone,hexcolor_category'])
            ->whereHas('category', function($query) {
                $query->where('user_id', Auth::id());
            });
            
        if ($this->tipo === 'recebidas') {
            $query->where('type_id', 1);
        } elseif ($this->tipo === 'enviadas') {
            $query->where('type_id', 2);
        }
        
        $transferencias = $query->orderBy('date', 'desc')->paginate(12);
        
        // Calcular totais
        $totalRecebido = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 1)
            ->whereHas('category', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->sum('value');
            
        $totalEnviado = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 2)
            ->whereHas('category', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->sum('value');
            
        return view('livewire.clients.client-transferencias', [
            'transferencias' => $transferencias,
            'totalRecebido' => $totalRecebido,
            'totalEnviado' => $totalEnviado
        ]);
    }
}
