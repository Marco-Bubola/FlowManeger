<?php

namespace App\Livewire\Clients;

use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Cashbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ClientResumo extends Component
{
    public Client $client;
    public $clienteId;
    
    // Dados financeiros
    public $totalFaturas = 0;
    public $totalRecebido = 0;
    public $totalEnviado = 0;
    public $saldoAtual = 0;
    public $categories = [];
    public $totals = [];
    
    // Paginação independente
    public $faturasPage = 1;
    public $enviadasPage = 1;
    public $recebidasPage = 1;
    public $perPage = 5;
    
    // Listas paginadas
    public $faturas = [];
    public $transferenciasEnviadas = [];
    public $transferenciasRecebidas = [];
    public $faturasTotal = 0;
    public $enviadasTotal = 0;
    public $recebidasTotal = 0;
    
    public function mount($cliente)
    {
        $this->clienteId = $cliente;
        $this->client = Client::where('user_id', Auth::id())->findOrFail($cliente);
        
        // Inicializar arrays vazios
        $this->faturas = [];
        $this->transferenciasEnviadas = [];
        $this->transferenciasRecebidas = [];
        $this->categories = [];
        $this->totals = [];
        
        $this->loadData();
    }
    
    public function loadData()
    {
        // Buscar categorias e somar valores considerando 'dividida'
        $this->categories = DB::table('category')
            ->join('invoice', 'category.id_category', '=', 'invoice.category_id')
            ->select(
                'category.name as label',
                DB::raw('SUM(CASE WHEN invoice.dividida = 1 THEN invoice.value / 2 ELSE invoice.value END) as value')
            )
            ->where('invoice.client_id', $this->clienteId)
            ->groupBy('category.name')
            ->havingRaw('SUM(CASE WHEN invoice.dividida = 1 THEN invoice.value / 2 ELSE invoice.value END) > 0')
            ->get()
            ->toArray();

        // Calcular total de faturas considerando 'dividida'
        $this->totalFaturas = Invoice::where('client_id', $this->clienteId)
            ->selectRaw('SUM(CASE WHEN dividida = 1 THEN value / 2 ELSE value END) as total')
            ->value('total') ?: 0;

        $this->totalRecebido = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 1)
            ->sum('value');
            
        $this->totalEnviado = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 2)
            ->sum('value');
            
        $this->saldoAtual = $this->totalRecebido - $this->totalEnviado - $this->totalFaturas;

        // Dados para o gráfico de pizza
        $this->totals = [
            'income' => $this->totalRecebido,
            'expense' => $this->totalEnviado + $this->totalFaturas,
            'balance' => $this->saldoAtual,
        ];

        // Faturas paginadas
        $faturasQuery = Invoice::where('client_id', $this->clienteId)
            ->select('id_invoice as id', 'invoice_date', 'description', 'value', 'category_id', 'dividida')
            ->with(['category:id_category,name,icone'])
            ->orderBy('invoice_date', 'desc');
            
        $this->faturasTotal = $faturasQuery->count();
        $this->faturas = $faturasQuery
            ->skip(($this->faturasPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->toArray() ?: [];

        // Transferências enviadas paginadas
        $enviadasQuery = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 2)
            ->select('id', 'type_id', 'value', 'date', 'description', 'category_id')
            ->with(['category:id_category,name,icone,hexcolor_category'])
            ->orderBy('date', 'desc');
            
        $this->enviadasTotal = $enviadasQuery->count();
        $this->transferenciasEnviadas = $enviadasQuery
            ->skip(($this->enviadasPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->toArray() ?: [];

        // Transferências recebidas paginadas
        $recebidasQuery = Cashbook::where('client_id', $this->clienteId)
            ->where('type_id', 1)
            ->select('id', 'type_id', 'value', 'date', 'description', 'category_id')
            ->with(['category:id_category,name,icone,hexcolor_category'])
            ->orderBy('date', 'desc');
            
        $this->recebidasTotal = $recebidasQuery->count();
        $this->transferenciasRecebidas = $recebidasQuery
            ->skip(($this->recebidasPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->toArray() ?: [];
    }
    
    public function toggleDividida($invoiceId)
    {
        $invoice = Invoice::where('client_id', $this->clienteId)->findOrFail($invoiceId);
        
        // Toggle do estado dividida
        $invoice->dividida = !$invoice->dividida;
        $invoice->save();
        
        // Recarregar dados para atualizar totais
        $this->loadData();
        
        $status = $invoice->dividida ? 'dividida' : 'não dividida';
        session()->flash('success', "Fatura marcada como {$status}!");
    }
    
    // Métodos de paginação
    public function nextFaturasPage()
    {
        $maxPage = ceil($this->faturasTotal / $this->perPage);
        if ($this->faturasPage < $maxPage) {
            $this->faturasPage++;
            $this->loadData();
        }
    }
    
    public function prevFaturasPage()
    {
        if ($this->faturasPage > 1) {
            $this->faturasPage--;
            $this->loadData();
        }
    }
    
    public function nextEnviadasPage()
    {
        $maxPage = ceil($this->enviadasTotal / $this->perPage);
        if ($this->enviadasPage < $maxPage) {
            $this->enviadasPage++;
            $this->loadData();
        }
    }
    
    public function prevEnviadasPage()
    {
        if ($this->enviadasPage > 1) {
            $this->enviadasPage--;
            $this->loadData();
        }
    }
    
    public function nextRecebidasPage()
    {
        $maxPage = ceil($this->recebidasTotal / $this->perPage);
        if ($this->recebidasPage < $maxPage) {
            $this->recebidasPage++;
            $this->loadData();
        }
    }
    
    public function prevRecebidasPage()
    {
        if ($this->recebidasPage > 1) {
            $this->recebidasPage--;
            $this->loadData();
        }
    }
    
    // Listener para mudanças diretas de página
    public function updatedFaturasPage()
    {
        $this->loadData();
    }
    
    public function updatedEnviadasPage()
    {
        $this->loadData();
    }
    
    public function updatedRecebidasPage()
    {
        $this->loadData();
    }
    
    public function render()
    {
        return view('livewire.clients.client-resumo');
    }
}
