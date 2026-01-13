<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardClientes extends Component
{
    public int $totalClientes = 0;
    public int $clientesNovosMes = 0;
    public int $clientesInadimplentes = 0;
    public int $clientesAniversariantes = 0;

    public function mount()
    {
        $userId = Auth::id();
        $this->totalClientes = Client::where('user_id', $userId)->count();
        $this->clientesNovosMes = Client::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $this->clientesInadimplentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) {
                $q->where('status', 'pendente');
            })->count();
        // A tabela clients não possui coluna de nascimento por padrão.
        // Mantemos 0 para evitar erro e deixar explícito que depende de adicionar a coluna.
        $this->clientesAniversariantes = 0;
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-clientes', [
            'totalClientes' => $this->totalClientes,
            'clientesNovosMes' => $this->clientesNovosMes,
            'clientesInadimplentes' => $this->clientesInadimplentes,
            'clientesAniversariantes' => $this->clientesAniversariantes,
        ]);
    }
}
