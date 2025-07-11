<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardProducts extends Component
{
    // Métricas principais
    public float $ticketMedio = 0;
    public int $totalProdutos = 0;
    public int $totalProdutosEstoque = 0;
    public int $produtosSemEstoque = 0;
    public float $totalDespesasProdutos = 0;
    public float $totalReceitasProdutos = 0;
    public float $totalSaldoProdutos = 0;

    // Produtos específicos
    public $produtoMaiorEstoque = null;
    public $produtoMaisVendido = null;
    public $ultimosProdutos = [];
    public $produtosParados = [];

    // Listas e rankings
    public $produtosMaisVendidos = [];
    public $produtosMaiorReceita = [];
    public $produtoMaiorLucro = [];
    public $produtosEstoqueBaixoAltaDemanda = [];
    public $dadosGraficoPizza = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Ticket médio
        $totalVendas = SaleItem::whereHas('sale', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum(DB::raw('price_sale * quantity'));
        
        $totalVendasCount = Sale::where('user_id', $userId)->count();
        $this->ticketMedio = $totalVendasCount > 0 ? $totalVendas / $totalVendasCount : 0;

        // Produtos mais vendidos (top 10)
        $this->produtosMaisVendidos = SaleItem::select('products.product_code', 'products.name', DB::raw('SUM(quantity) as total_vendido'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('products.product_code', 'products.name')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get()
            ->toArray();

        // Produto com maior receita (top 5)
        $this->produtosMaiorReceita = SaleItem::select('products.product_code', 'products.name', DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as receita_total'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('products.product_code', 'products.name')
            ->orderByDesc('receita_total')
            ->limit(5)
            ->get()
            ->toArray();

        // Produto com maior lucro (top 5)
        $this->produtoMaiorLucro = SaleItem::select(
                'products.product_code',
                'products.name',
                DB::raw('SUM((sale_items.price_sale - products.price) * sale_items.quantity) as lucro_total')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('products.product_code', 'products.name')
            ->orderByDesc('lucro_total')
            ->limit(5)
            ->get()
            ->toArray();

        // Gráfico de vendas por categoria
        $this->dadosGraficoPizza = SaleItem::select(
            DB::raw('category.name as category_name'),
            DB::raw('SUM(sale_items.quantity) as total_sold')
        )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('category', 'products.category_id', '=', 'category.id_category')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('category.name')
            ->get()
            ->toArray();

        // Últimos produtos
        $this->ultimosProdutos = Product::where('user_id', $userId)
            ->where('stock_quantity', '>', 0)
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->toArray();

        // Produtos financeiros
        $produtosTodos = Product::where('user_id', $userId)
            ->where('stock_quantity', '>', 0)
            ->get(['price', 'price_sale']);

        $this->totalDespesasProdutos = $produtosTodos->sum('price');
        $this->totalReceitasProdutos = $produtosTodos->sum('price_sale');
        $this->totalSaldoProdutos = $this->totalReceitasProdutos - $this->totalDespesasProdutos;

        // Produtos parados (sem venda nos últimos 60 dias)
        $produtosVendidosIds = SaleItem::whereHas('sale', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('created_at', '>=', now()->subDays(60));
            })
            ->pluck('product_id')
            ->unique();
        
        $this->produtosParados = Product::where('user_id', $userId)
            ->whereNotIn('id', $produtosVendidosIds)
            ->get()
            ->toArray();

        // Produtos com estoque baixo e alta demanda (top 10)
        $this->produtosEstoqueBaixoAltaDemanda = SaleItem::select('products.product_code', 'products.name', DB::raw('SUM(sale_items.quantity) as total_vendido'), 'products.stock_quantity')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('products.product_code', 'products.name', 'products.stock_quantity')
            ->having('products.stock_quantity', '<', 10)
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get()
            ->toArray();

        // Total de produtos do usuário
        $this->totalProdutos = Product::where('user_id', $userId)->count();

        // Total de produtos em estoque (>0)
        $this->totalProdutosEstoque = Product::where('user_id', $userId)
            ->where('stock_quantity', '>', 0)
            ->sum('stock_quantity');

        // Produto com maior estoque
        $this->produtoMaiorEstoque = Product::where('user_id', $userId)
            ->orderByDesc('stock_quantity')
            ->first();

        // Produto mais vendido (nome)
        if (count($this->produtosMaisVendidos) > 0) {
            $this->produtoMaisVendido = Product::where('product_code', $this->produtosMaisVendidos[0]['product_code'])->first();
        }

        // Produtos sem estoque
        $this->produtosSemEstoque = Product::where('user_id', $userId)
            ->where('stock_quantity', 0)
            ->count();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-products');
    }
}
