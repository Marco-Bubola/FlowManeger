<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardIndex extends Component
{
    // Dados do dashboard principal
    public float $totalCashbook = 0;
    public int $totalProdutos = 0;
    public int $totalProdutosEstoque = 0;
    public int $totalClientes = 0;
    public int $clientesComSalesPendentes = 0;
    public float $totalFaturamento = 0;
    public float $totalFaltante = 0;
    public $ultimaMovimentacaoCashbook = null;
    public $produtoMaiorEstoque = null;
    public $ultimaVenda = null;
    public $produtoMaisVendido = null;

    // Novas variáveis para o mês atual
    public int $salesMonth = 0;
    public float $ticketMedio = 0;
    public int $clientesNovosMes = 0;
    public int $produtosEstoqueBaixo = 0;

    // Variáveis adicionais para dashboard completo
    public int $clientesInadimplentes = 0;
    public int $aniversariantesMes = 0;
    public int $produtosVendidosMes = 0;
    public int $produtosCadastrados = 0;
    public float $saldoCaixa = 0;
    public float $contasPagar = 0;
    public float $contasReceber = 0;
    public float $fornecedoresPagar = 0;
    public float $despesasFixas = 0;
    public float $clientesReceber = 0;
    public float $outrosReceber = 0;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Saldo total do cashbook
        $totalReceitas = Cashbook::where('user_id', $userId)->where('type_id', 1)->sum('value');
        $totalDespesas = Cashbook::where('user_id', $userId)->where('type_id', 2)->sum('value');
        $this->totalCashbook = $totalReceitas - $totalDespesas;
        $this->saldoCaixa = $this->totalCashbook;

        // Produtos
        $this->totalProdutos = Product::where('user_id', $userId)->count();
        $this->totalProdutosEstoque = Product::where('user_id', $userId)->sum('stock_quantity');
        $this->produtosCadastrados = $this->totalProdutos;

        // Produtos vendidos no mês
        $this->produtosVendidosMes = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->sum('sale_items.quantity');

        // Clientes
        $this->totalClientes = Client::where('user_id', $userId)->count();
        $this->clientesComSalesPendentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            })->count();

        // Clientes inadimplentes (com vendas pendentes)
        $this->clientesInadimplentes = $this->clientesComSalesPendentes;

        // Aniversariantes do mês (coluna não existe, comentar para evitar erro)
        // $this->aniversariantesMes = Client::where('user_id', $userId)
        //     ->whereMonth('data_nascimento', now()->month)
        //     ->count();
        $this->aniversariantesMes = 0;

        // Faturamento
        $this->totalFaturamento = Sale::where('user_id', $userId)->sum('total_price');

        // Valor faltante (vendas pendentes - pagamentos)
        $salesPendentes = Sale::where('user_id', $userId)->where('status', 'pendente')->get(['id', 'total_price']);
        $idsPendentes = $salesPendentes->pluck('id');
        $totalPendentes = $salesPendentes->sum('total_price');
        $totalPagamentos = DB::table('sale_payments')
            ->whereIn('sale_id', $idsPendentes)
            ->sum('amount_paid');
        $this->totalFaltante = $totalPendentes - $totalPagamentos;

        // Card Cashbook: última movimentação
        $this->ultimaMovimentacaoCashbook = Cashbook::where('user_id', $userId)
            ->orderByDesc('date')
            ->first();

        // Card Produtos: produto com maior estoque
        $this->produtoMaiorEstoque = Product::where('user_id', $userId)
            ->orderByDesc('stock_quantity')
            ->first();

        // Produto mais vendido (nome)
        $this->produtoMaisVendido = SaleItem::select('products.name', DB::raw('SUM(quantity) as total_vendido'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('products.user_id', $userId)
            ->groupBy('products.name')
            ->orderByDesc('total_vendido')
            ->first();

        // Card Vendas: última venda
        $this->ultimaVenda = Sale::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        // Vendas no mês atual
        $salesMonth = Sale::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Ticket médio do mês
        $totalVendasMes = Sale::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');
        $ticketMedio = $salesMonth > 0 ? $totalVendasMes / $salesMonth : 0;

        // Novos clientes no mês
        $clientesNovosMes = Client::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Produtos com estoque baixo (exemplo: <= 5)
        $produtosEstoqueBaixo = Product::where('user_id', $userId)
            ->where('stock_quantity', '<=', 5)
            ->count();

        // Contas a pagar (exemplo: cashbook type_id = 2)
        $this->contasPagar = Cashbook::where('user_id', $userId)->where('type_id', 2)->sum('value');
        // Contas a receber (cashbook type_id = 1)
        $this->contasReceber = Cashbook::where('user_id', $userId)->where('type_id', 1)->sum('value');

        // Fornecedores a pagar (exemplo: cashbook category_id = 2)
        $this->fornecedoresPagar = Cashbook::where('user_id', $userId)->where('type_id', 2)->where('category_id', 2)->sum('value');
        // Despesas fixas (exemplo: cashbook category_id = 1)
        $this->despesasFixas = Cashbook::where('user_id', $userId)->where('type_id', 2)->where('category_id', 1)->sum('value');

        // Clientes a receber (exemplo: cashbook type_id = 1, category_id = 3)
        $this->clientesReceber = Cashbook::where('user_id', $userId)->where('type_id', 1)->where('category_id', 3)->sum('value');
        // Outros a receber (exemplo: cashbook type_id = 1, category_id != 3)
        $this->outrosReceber = Cashbook::where('user_id', $userId)->where('type_id', 1)->where('category_id', '!=', 3)->sum('value');

        // Guardar para uso na view
        $this->salesMonth = $salesMonth;
        $this->ticketMedio = $ticketMedio;
        $this->clientesNovosMes = $clientesNovosMes;
        $this->produtosEstoqueBaixo = $produtosEstoqueBaixo;
    }

    public function render()
    {
        $totalSales = Sale::where('user_id', Auth::id())->count();
        return view('livewire.dashboard.dashboard-index', [
            'totalSales' => $totalSales,
            'produtoMaisVendido' => $this->produtoMaisVendido,
            'totalProdutosEstoque' => $this->totalProdutosEstoque,
            'totalCashbook' => $this->totalCashbook,
            'totalClientes' => $this->totalClientes,
            'clientesComSalesPendentes' => $this->clientesComSalesPendentes,
            'totalFaturamento' => $this->totalFaturamento,
            'totalProdutos' => $this->totalProdutos,
            'salesMonth' => $this->salesMonth,
            'ticketMedio' => $this->ticketMedio,
            'clientesNovosMes' => $this->clientesNovosMes,
            'produtosEstoqueBaixo' => $this->produtosEstoqueBaixo,
            'clientesInadimplentes' => $this->clientesInadimplentes ?? 0,
            'aniversariantesMes' => $this->aniversariantesMes ?? 0,
            'produtosVendidosMes' => $this->produtosVendidosMes ?? 0,
            'produtosCadastrados' => $this->produtosCadastrados ?? 0,
            'saldoCaixa' => $this->saldoCaixa ?? 0,
            'contasPagar' => $this->contasPagar ?? 0,
            'contasReceber' => $this->contasReceber ?? 0,
            'fornecedoresPagar' => $this->fornecedoresPagar ?? 0,
            'despesasFixas' => $this->despesasFixas ?? 0,
            'clientesReceber' => $this->clientesReceber ?? 0,
            'outrosReceber' => $this->outrosReceber ?? 0,
        ]);
    }
}
