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

    // Parâmetros para retornar à mesma visualização
    public $returnMonth = null;
    public $returnYear = null;

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

        // Capturar parâmetros de retorno da query string
        $this->returnMonth = request()->query('return_month');
        $this->returnYear = request()->query('return_year');

        $this->loadInvoice();
        $this->loadData();
    }

    public function loadInvoice()
    {
        $this->invoice = Invoice::findOrFail($this->invoiceId);
        $this->description = $this->invoice->description;
        $this->value = $this->invoice->value; // Removida a formatação str_replace
        $this->installments = $this->invoice->installments;
        $this->category_id = $this->invoice->category_id;
        $this->client_id = $this->invoice->client_id;
        $this->invoice_date = $this->invoice->invoice_date;
    }

    public function loadData()
    {
        $this->banks = Bank::all();

        // Carregar categorias ordenadas por uso recente (compatível com MySQL)
        $this->categories = Category::where('category.is_active', 1)
            ->where('category.user_id', Auth::id())
            ->where('category.type', 'transaction')
            ->leftJoin('invoice', 'category.id_category', '=', 'invoice.category_id')
            ->select('category.*')
            ->selectRaw('MAX(invoice.created_at) as last_used')
            ->groupBy('category.id_category', 'category.name', 'category.description', 'category.is_active', 'category.type', 'category.user_id', 'category.created_at', 'category.updated_at')
            ->orderByRaw('CASE WHEN last_used IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('last_used DESC')
            ->orderBy('category.name')
            ->get();

        // Carregar clientes ordenados por uso recente (compatível com MySQL)
        $this->clients = Client::where('clients.user_id', Auth::id())
            ->leftJoin('invoice', 'clients.id', '=', 'invoice.client_id')
            ->select('clients.*')
            ->selectRaw('MAX(invoice.created_at) as last_used')
            ->groupBy('clients.id', 'clients.name', 'clients.email', 'clients.phone', 'clients.address', 'clients.user_id', 'clients.caminho_foto', 'clients.created_at', 'clients.updated_at')
            ->orderByRaw('CASE WHEN last_used IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('last_used DESC')
            ->orderBy('clients.name')
            ->get();
    }

    public function save()
    {
        // Log a ser verificado em storage/logs/laravel.log
        \Illuminate\Support\Facades\Log::info('EditInvoice save method called.', [
            'description' => $this->description,
            'value' => $this->value,
            'installments' => $this->installments,
            'category_id' => $this->category_id,
            'client_id' => $this->client_id,
            'invoice_date' => $this->invoice_date,
        ]);

        // A propriedade 'value' já está no formato 'xxxx.xx' vinda do componente de moeda.
        $validatedData = $this->validate([
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|gt:0', // Valida a string numérica
            'installments' => 'nullable|string|max:255',
            'category_id' => 'required|exists:category,id_category',
            'client_id' => 'nullable|exists:clients,id',
            'invoice_date' => 'required|date',
        ]);

        try {
            // A atualização usará apenas os dados que passaram na validação
            $this->invoice->update($validatedData);

            session()->flash('success', 'Transação atualizada com sucesso!');
            $this->dispatch('invoice-updated');

            // Redirecionar com os parâmetros de month e year preservados
            $queryParams = ['bankId' => $this->invoice->id_bank];

            // Adicionar month e year aos parâmetros se estiverem disponíveis
            if ($this->returnMonth) {
                $queryParams['return_month'] = $this->returnMonth;
            }
            if ($this->returnYear) {
                $queryParams['return_year'] = $this->returnYear;
            }

            return redirect()->route('invoices.index', $queryParams);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao atualizar a transação: ' . $e->getMessage());
            // Log para facilitar a depuração no futuro
            \Illuminate\Support\Facades\Log::error('Erro ao atualizar fatura: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoices.edit-invoice', [
            'selectedCategoryName' => $this->getSelectedCategoryName(),
            'selectedClientName' => $this->getSelectedClientName(),
        ]);
    }

    private function getSelectedCategoryName()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            return $category ? $category->name : 'Selecione uma categoria';
        }
        return 'Selecione uma categoria';
    }

    private function getSelectedClientName()
    {
        if ($this->client_id) {
            $client = Client::find($this->client_id);
            return $client ? $client->name : 'Selecione um cliente';
        }
        return 'Selecione um cliente';
    }
}
