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

        // Produtos
        $this->totalProdutos = Product::where('user_id', $userId)->count();
        $this->totalProdutosEstoque = Product::where('user_id', $userId)->sum('stock_quantity');

        // Clientes
        $this->totalClientes = Client::where('user_id', $userId)->count();
        $this->clientesComSalesPendentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            })->count();

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
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-index');
    }
}
