<?php

namespace App\Livewire\Banks;

use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class EditBank extends Component
{
    public Bank $bank;
    public string $name = '';
    public string $description = '';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public string $caminho_icone = '';

    // Propriedade que não deve ser serializada pelo Livewire
    protected $bankIcons;

    public function mount(Bank $bank): void
    {
        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->bank = $bank;
        $this->fill($bank);
        
        $this->bankIcons = [
            ['name' => 'Nubank', 'icon' => asset('assets/img/banks/nubank.svg')],
            ['name' => 'Inter', 'icon' => asset('assets/img/banks/inter.png')],
            ['name' => 'Santander', 'icon' => asset('assets/img/banks/santander.png')],
            ['name' => 'Itaú', 'icon' => asset('assets/img/banks/itau.png')],
            ['name' => 'Banco do Brasil', 'icon' => asset('assets/img/banks/bb.png')],
            ['name' => 'Caixa', 'icon' => asset('assets/img/banks/caixa.png')],
            ['name' => 'Bradesco', 'icon' => asset('assets/img/banks/bradesco.png')],
        ];
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'caminho_icone' => 'required|string',
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();
        $this->bank->update($validated);

        // Dispara um evento para fechar o modal e atualizar a lista de bancos
        $this->dispatch('bank-updated');
        $this->dispatch('close-edit-modal'); // Dispatch para fechar o modal
        
        Session::flash('success', 'Cartão atualizado com sucesso!');
    }

    public function render()
    {
        return view('livewire.banks.edit-bank', [
            'bankIcons' => $this->bankIcons
        ])->layout('layouts.app');
    }
}