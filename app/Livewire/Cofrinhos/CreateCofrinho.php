<?php

namespace App\Livewire\Cofrinhos;

use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateCofrinho extends Component
{
    public $nome = '';
    public $meta_valor = '';
    public $description = '';

    protected $rules = [
        'nome' => 'required|string|max:255',
        'meta_valor' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'nome.required' => 'O nome do cofrinho é obrigatório.',
        'nome.max' => 'O nome do cofrinho não pode ter mais de 255 caracteres.',
        'meta_valor.required' => 'A meta de valor é obrigatória.',
        'meta_valor.numeric' => 'A meta de valor deve ser um número.',
        'meta_valor.min' => 'A meta de valor deve ser maior que zero.',
        'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
    ];

    public function save()
    {
        $this->validate();

        Cofrinho::create([
            'user_id' => Auth::id(),
            'nome' => $this->nome,
            'meta_valor' => $this->meta_valor,
            'description' => $this->description,
            'status' => 'ativo',
        ]);

        session()->flash('success', 'Cofrinho criado com sucesso!');
        
        $this->dispatch('cofrinhoUpdated');
        
        return $this->redirect(route('cofrinhos.index'), navigate: true);
    }

    public function cancel()
    {
        return $this->redirect(route('cofrinhos.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.cofrinhos.create');
    }
}
