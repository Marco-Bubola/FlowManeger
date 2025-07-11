<?php

namespace App\Livewire\Cofrinhos;

use App\Models\Cofrinho;
use App\Models\Cashbook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class CofrinhoIndex extends Component
{
    public $cofrinhos = [];
    public $total = 0;
    public $ranking = [];
    public $showDeleteModal = false;
    public $deletingCofrinho = null;

    public function mount()
    {
        $this->loadCofrinhos();
        $this->calculateRanking();
    }

    public function loadCofrinhos()
    {
        $cofrinhos = Cofrinho::where('user_id', Auth::id())
            ->with(['cashbooks' => function($q) {
                $q->select('id', 'cofrinho_id', 'value', 'type_id', 'created_at');
            }])
            ->withCount('cashbooks')
            ->get();

        // Calcular valor acumulado considerando receitas e despesas
        foreach ($cofrinhos as $cofrinho) {
            $valor = 0;
            foreach ($cofrinho->cashbooks as $cb) {
                if ($cb->type_id == 1) { // Receita
                    $valor += $cb->value;
                } elseif ($cb->type_id == 2) { // Despesa
                    $valor -= $cb->value;
                }
            }
            $cofrinho->valor_acumulado = $valor;
            $cofrinho->porcentagem = $cofrinho->meta_valor > 0 ? ($valor / $cofrinho->meta_valor) * 100 : 0;
        }

        $this->cofrinhos = $cofrinhos->toArray();
        $this->total = $cofrinhos->sum('meta_valor');
    }

    public function calculateRanking()
    {
        $cofrinhos = collect($this->cofrinhos);
        
        $this->ranking = $cofrinhos->map(function($c) {
            $crescimento = collect($c['cashbooks'])
                ->where('type_id', 1)
                ->filter(function($cb) {
                    return \Carbon\Carbon::parse($cb['created_at'])->format('Y-m') === now()->format('Y-m');
                })
                ->sum('value');
            
            return [
                'id' => $c['id'],
                'nome' => $c['nome'],
                'crescimento' => $crescimento,
                'meta' => $c['meta_valor'],
                'valor_acumulado' => $c['valor_acumulado'],
            ];
        })->sortByDesc('crescimento')->take(3)->toArray();
    }

    public function confirmDelete($id)
    {
        $this->deletingCofrinho = Cofrinho::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingCofrinho = null;
    }

    public function deleteCofrinho()
    {
        if ($this->deletingCofrinho) {
            $this->deletingCofrinho->delete();
            $this->showDeleteModal = false;
            $this->deletingCofrinho = null;
            $this->loadCofrinhos();
            $this->calculateRanking();
            
            session()->flash('success', 'Cofrinho excluído com sucesso!');
        }
    }

    #[On('cofrinhoUpdated')]
    public function refreshCofrinhos()
    {
        $this->loadCofrinhos();
        $this->calculateRanking();
    }

    public function render()
    {
        return view('livewire.cofrinhos.index');
    }
}
