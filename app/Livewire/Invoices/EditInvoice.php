<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditInvoice extends Component
{
    public $invoiceId;
    public $invoice;
    public $description = '';
    public $value = '';
    public $installments = '';
    public $category_id = '';
    public $client_id = '';
    public $invoice_date = '';
    
    public $banks = [];
    public $categories = [];
    public $clients = [];

    protected $rules = [
        'description' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'installments' => 'nullable|string|max:255',
        'category_id' => 'required|exists:category,id_category',
        'client_id' => 'nullable|exists:clients,id',
        'invoice_date' => 'required|date',
    ];

    protected $messages = [
        'description.required' => 'A descrição é obrigatória.',
        'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
        'value.required' => 'O valor é obrigatório.',
        'category_id.required' => 'A categoria é obrigatória.',
        'category_id.exists' => 'A categoria selecionada não existe.',
        'client_id.exists' => 'O cliente selecionado não existe.',
        'invoice_date.required' => 'A data é obrigatória.',
        'invoice_date.date' => 'A data deve ser uma data válida.',
    ];

    public function mount($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        $this->loadInvoice();
        $this->loadData();
    }

    public function loadInvoice()
    {
        $this->invoice = Invoice::findOrFail($this->invoiceId);
        $this->description = $this->invoice->description;
        $this->value = str_replace('.', ',', $this->invoice->value);
        $this->installments = $this->invoice->installments;
        $this->category_id = $this->invoice->category_id;
        $this->client_id = $this->invoice->client_id;
        $this->invoice_date = $this->invoice->invoice_date;
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

            $this->invoice->update([
                'description' => $this->description,
                'value' => $value,
                'installments' => $this->installments,
                'category_id' => $this->category_id,
                'client_id' => $this->client_id ?: null,
                'invoice_date' => $this->invoice_date,
            ]);

            session()->flash('success', 'Transação atualizada com sucesso!');
            $this->dispatch('invoice-updated');
            
            return redirect()->route('invoices.index', ['bankId' => $this->invoice->id_bank]);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao atualizar a transação: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoices.edit-invoice');
    }
}
