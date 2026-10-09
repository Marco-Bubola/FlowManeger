<?php

namespace App\Livewire\Cofrinhos;

use App\Models\Cofrinho;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateCofrinho extends Component
{
    public $nome = '';
    public $meta_valor = '';
    public $icone = 'fa-piggy-bank';

    /** Ícones que o usuário pode escolher (Font Awesome, coluna cofrinhos.icone). */
    public const ICONES = [
        'fa-piggy-bank' => 'Cofrinho', 'fa-shield-alt' => 'Reserva', 'fa-plane' => 'Viagem', 'fa-car' => 'Carro',
        'fa-home' => 'Casa', 'fa-laptop' => 'Eletrônico', 'fa-graduation-cap' => 'Estudos', 'fa-gift' => 'Presente',
        'fa-heart' => 'Saúde', 'fa-ring' => 'Casamento', 'fa-baby' => 'Filhos', 'fa-store' => 'Negócio',
    ];

    protected $rules = [
        'nome' => 'required|string|max:255',
        'meta_valor' => 'required|numeric|min:0',
        'icone' => 'required|string|max:50',
    ];

    protected $messages = [
        'nome.required' => 'O nome do cofrinho é obrigatório.',
        'nome.max' => 'O nome do cofrinho não pode ter mais de 255 caracteres.',
        'meta_valor.required' => 'A meta de valor é obrigatória.',
        'meta_valor.numeric' => 'A meta de valor deve ser um número.',
        'meta_valor.min' => 'A meta de valor deve ser maior que zero.',
    ];

    public function save()
    {
        $this->validate();

        Cofrinho::create([
            'user_id' => Auth::id(),
            'nome' => $this->nome,
            'meta_valor' => $this->meta_valor,
            'icone' => array_key_exists($this->icone, self::ICONES) ? $this->icone : 'fa-piggy-bank',
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
