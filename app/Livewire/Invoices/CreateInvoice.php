<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateInvoice extends Component
{
    public $bankId;
    public $type = '';
    public $description = '';
    public $value = '';
    public $installments = '';
    public $category_id = '';
    public $client_id = '';
    public $date = '';
    public $due_date = '';

    public $banks = [];
    public $categories = [];
    public $clients = [];

    protected $rules = [
        'bankId' => 'required|exists:banks,id_bank',
        'type' => 'required|in:receita,despesa',
        'description' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'installments' => 'nullable|string|max:255',
        'category_id' => 'required|exists:category,id_category',
        'client_id' => 'nullable|exists:clients,id',
        'date' => 'required|date',
        'due_date' => 'nullable|date',
    ];

    protected $messages = [
        'bankId.required' => 'O banco é obrigatório.',
        'bankId.exists' => 'O banco selecionado não existe.',
        'type.required' => 'O tipo é obrigatório.',
        'type.in' => 'O tipo deve ser receita ou despesa.',
        'description.required' => 'A descrição é obrigatória.',
        'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
        'value.required' => 'O valor é obrigatório.',
        'category_id.required' => 'A categoria é obrigatória.',
        'category_id.exists' => 'A categoria selecionada não existe.',
        'client_id.exists' => 'O cliente selecionado não existe.',
        'date.required' => 'A data é obrigatória.',
        'date.date' => 'A data deve ser uma data válida.',
        'due_date.date' => 'A data de vencimento deve ser uma data válida.',
    ];

    public function mount($bankId = null)
    {
        $this->bankId = $bankId;
        $this->date = now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        $this->banks = Bank::all();
        $this->categories = Category::all();
        $this->clients = Client::all();
    }

    public function save()
    {
        $this->validate();

        try {
            // Converter vírgula para ponto no valor
            $value = str_replace(',', '.', $this->value);

            Invoice::create([
                'id_bank' => $this->bankId,
                'type' => $this->type,
                'description' => $this->description,
                'value' => $value,
                'installments' => $this->installments,
                'category_id' => $this->category_id,
                'client_id' => $this->client_id ?: null,
                'invoice_date' => $this->date,
                'due_date' => $this->due_date ?: null,
                'user_id' => Auth::id(),
            ]);

            session()->flash('success', 'Transação criada com sucesso!');
            $this->dispatch('invoice-created');

            return redirect()->route('invoices.index', ['bankId' => $this->bankId]);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao criar a transação: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoices.create-invoice');
    }
}
