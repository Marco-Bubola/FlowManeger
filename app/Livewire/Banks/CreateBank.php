<?php

namespace App\Livewire\Banks;

use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class CreateBank extends Component
{
    // Propriedades do estado para o formulário
    public string $name = '';
    public string $description = '';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public string $caminho_icone = '';

    // Propriedade que não deve ser serializada pelo Livewire
    protected $bankIcons;

    public function mount(): void
    {
        // Define os ícones dos bancos como array (não Collection)
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
    
    public function store(): void
    {
        // Valida os dados
        $validated = $this->validate();

        // Cria o novo banco
        $bank = new Bank();
        $bank->fill($validated); // Preenche os campos validados
        $bank->user_id = Auth::id(); // Atribui o ID do usuário logado
        $bank->save();

        // Redireciona com uma mensagem de sucesso
        Session::flash('success', 'Cartão adicionado com sucesso!');
        $this->redirect(route('banks.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.banks.create-bank', [
            'bankIcons' => $this->bankIcons
        ]);
    }
}