<?php

namespace App\Livewire\Cofrinhos;

use App\Models\Cofrinho;
use App\Models\Cashbook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowCofrinho extends Component
{
    public Cofrinho $cofrinho;
    public $valor_acumulado = 0;
    public $porcentagem = 0;
    public $transacoes = [];
    public $estatisticas = [];

    public function mount(Cofrinho $cofrinho)
    {
        // Verificar se o cofrinho pertence ao usuário logado
        if ($cofrinho->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $this->cofrinho = $cofrinho;
        $this->loadData();
    }

    public function loadData()
    {
        // Carregar transações do cofrinho
        $this->transacoes = Cashbook::where('cofrinho_id', $this->cofrinho->id)
            ->with(['type'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // Calcular valor acumulado
        // LÓGICA CORRIGIDA:
        // type_id=1 (receita) = dinheiro ENTRANDO no cofrinho (guardando) - ADICIONA
        // type_id=2 (despesa) = dinheiro SAINDO do cofrinho (retirando) - SUBTRAI
        $this->valor_acumulado = 0;
        foreach ($this->transacoes as $transacao) {
            if ($transacao['type_id'] == 1) { // Receita = guardando no cofrinho
                $this->valor_acumulado += $transacao['value'];
            } elseif ($transacao['type_id'] == 2) { // Despesa = retirando do cofrinho
                $this->valor_acumulado -= $transacao['value'];
            }
        }

        // Calcular porcentagem da meta
        $this->porcentagem = $this->cofrinho->meta_valor > 0
            ? ($this->valor_acumulado / $this->cofrinho->meta_valor) * 100
            : 0;

        // Estatísticas
        $this->calculateStatistics();
    }

    public function calculateStatistics()
    {
        $receitas = collect($this->transacoes)->where('type_id', 1);
        $despesas = collect($this->transacoes)->where('type_id', 2);

        $this->estatisticas = [
            'total_receitas' => $receitas->sum('value'),
            'total_despesas' => $despesas->sum('value'),
            'qtd_receitas' => $receitas->count(),
            'qtd_despesas' => $despesas->count(),
            'media_receitas' => $receitas->count() > 0 ? $receitas->avg('value') : 0,
            'media_despesas' => $despesas->count() > 0 ? $despesas->avg('value') : 0,
            'transacoes_mes' => collect($this->transacoes)->filter(function($t) {
                return \Carbon\Carbon::parse($t['created_at'])->format('Y-m') === now()->format('Y-m');
            })->count(),
            'valor_restante' => $this->cofrinho->meta_valor - $this->valor_acumulado,
        ];
    }

    public function render()
    {
        return view('livewire.cofrinhos.show');
    }
}
