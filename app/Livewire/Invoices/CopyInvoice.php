<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CopyInvoice extends Component
{
    public $invoiceId;
    public $originalInvoice;
    public $bankId;
    public $description = '';
    public $value = '';
    public $installments = '';
    public $category_id = '';
    public $client_id = '';
    public $invoice_date = '';
    public $divisions = 1;
    
    public $banks = [];
    public $categories = [];
    public $clients = [];

    protected $rules = [
        'bankId' => 'required|exists:banks,id_bank',
        'description' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'installments' => 'required|string|max:255',
        'category_id' => 'required|exists:category,id_category',
        'client_id' => 'nullable|exists:clients,id',
        'invoice_date' => 'required|date',
        'divisions' => 'required|integer|min:1',
    ];

    protected $messages = [
        'bankId.required' => 'O banco é obrigatório.',
        'bankId.exists' => 'O banco selecionado não existe.',
        'description.required' => 'A descrição é obrigatória.',
        'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
        'value.required' => 'O valor é obrigatório.',
        'installments.required' => 'As parcelas são obrigatórias.',
        'category_id.required' => 'A categoria é obrigatória.',
        'category_id.exists' => 'A categoria selecionada não existe.',
        'client_id.exists' => 'O cliente selecionado não existe.',
        'invoice_date.required' => 'A data é obrigatória.',
        'invoice_date.date' => 'A data deve ser uma data válida.',
        'divisions.required' => 'O número de divisões é obrigatório.',
        'divisions.integer' => 'O número de divisões deve ser um número inteiro.',
        'divisions.min' => 'O número de divisões deve ser pelo menos 1.',
    ];

    public function mount($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        $this->loadOriginalInvoice();
        $this->loadData();
    }

    public function loadOriginalInvoice()
    {
        $this->originalInvoice = Invoice::findOrFail($this->invoiceId);
        $this->bankId = $this->originalInvoice->id_bank;
        $this->description = $this->originalInvoice->description;
        $this->value = str_replace('.', ',', $this->originalInvoice->value);
        $this->installments = $this->originalInvoice->installments;
        $this->category_id = $this->originalInvoice->category_id;
        $this->client_id = $this->originalInvoice->client_id;
        $this->invoice_date = now()->format('Y-m-d');
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
                'description' => $this->description,
                'value' => $value,
                'installments' => $this->installments,
                'category_id' => $this->category_id,
                'client_id' => $this->client_id ?: null,
                'invoice_date' => $this->invoice_date,
                'user_id' => $this->originalInvoice->user_id,
            ]);

            session()->flash('success', 'Transação copiada com sucesso!');
            $this->dispatch('invoice-created');
            
            return redirect()->route('invoices.index', ['bankId' => $this->bankId]);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao copiar a transação: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoices.copy-invoice');
    }
}
