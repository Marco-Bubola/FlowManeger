<?php

namespace App\Livewire\Cofrinhos;

use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditCofrinho extends Component
{
    public Cofrinho $cofrinho;
    public $nome = '';
    public $meta_valor = '';
    public $description = '';
    public $status = 'ativo';

    protected $rules = [
        'nome' => 'required|string|max:255',
        'meta_valor' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:500',
        'status' => 'required|in:ativo,inativo',
    ];

    protected $messages = [
        'nome.required' => 'O nome do cofrinho é obrigatório.',
        'nome.max' => 'O nome do cofrinho não pode ter mais de 255 caracteres.',
        'meta_valor.required' => 'A meta de valor é obrigatória.',
        'meta_valor.numeric' => 'A meta de valor deve ser um número.',
        'meta_valor.min' => 'A meta de valor deve ser maior que zero.',
        'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        'status.required' => 'O status é obrigatório.',
        'status.in' => 'O status deve ser ativo ou inativo.',
    ];

    public function mount(Cofrinho $cofrinho)
    {
        // Verificar se o cofrinho pertence ao usuário logado
        if ($cofrinho->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $this->cofrinho = $cofrinho;
        $this->nome = $cofrinho->nome;
        $this->meta_valor = $cofrinho->meta_valor;
        $this->description = $cofrinho->description ?? '';
        $this->status = $cofrinho->status;
    }

    public function save()
    {
        $this->validate();

        $this->cofrinho->update([
            'nome' => $this->nome,
            'meta_valor' => $this->meta_valor,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Cofrinho atualizado com sucesso!');
        
        $this->dispatch('cofrinhoUpdated');
        
        return $this->redirect(route('cofrinhos.index'), navigate: true);
    }

    public function cancel()
    {
        return $this->redirect(route('cofrinhos.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.cofrinhos.edit');
    }
}
