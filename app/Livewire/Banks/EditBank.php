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
    // Abertura e fechamento são só o dia do mês (1 a 31); no banco seguem como data.
    public $start_day = null;
    public $end_day = null;
    public string $caminho_icone = '';
    public $credit_limit = null;
    public $due_day = null;

    // Propriedade que não deve ser serializada pelo Livewire
    protected $bankIcons;

    public function mount(Bank $bank): void
    {
        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->bank = $bank;
        // Cartões antigos podem ter descrição ou ícone vazios no banco (null quebrava a tela)
        $this->fill(array_merge($bank->toArray(), [
            'description' => (string) ($bank->description ?? ''),
            'caminho_icone' => (string) ($bank->caminho_icone ?? ''),
        ]));
        $this->start_day = $bank->start_date ? \Carbon\Carbon::parse($bank->start_date)->day : null;
        $this->end_day = $bank->end_date ? \Carbon\Carbon::parse($bank->end_date)->day : null;
        
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
            'start_day' => 'required|integer|between:1,31',
            'end_day' => 'required|integer|between:1,31',
            'caminho_icone' => 'required|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'due_day' => 'nullable|integer|between:1,31',
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();
        $validated['credit_limit'] = (float) ($validated['credit_limit'] ?? 0) > 0 ? $validated['credit_limit'] : null;
        $validated['due_day'] = $validated['due_day'] ?: null;
        $validated['start_date'] = self::dayToDate($validated['start_day']);
        $validated['end_date'] = self::dayToDate($validated['end_day']);
        unset($validated['start_day'], $validated['end_day']);
        $this->bank->update($validated);

              
        Session::flash('success', 'Cartão atualizado com sucesso!');
        $this->redirectRoute('banks.index');
    }

    protected $validationAttributes = [
        'start_day' => 'dia de abertura',
        'end_day' => 'dia de fechamento',
        'due_day' => 'dia de vencimento',
    ];

    /** Guarda o dia num mês de 31 dias (só o dia é usado nas faturas). */
    private static function dayToDate($day): string
    {
        return sprintf('%d-01-%02d', now()->year, (int) $day);
    }

    public function render()
    {
        return view('livewire.banks.edit-bank', [
            'bankIcons' => $this->bankIcons
        ]);
    }
}