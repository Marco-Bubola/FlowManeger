<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class ShowProduct extends Component
{
    public string $productCode;
    public $products;
    public $mainProduct;
    public $category;
    public $salesData;
    public $analytics;

    // Filtros para gráficos
    public string $period = '6months'; // 30days, 3months, 6months, 1year, all
    public string $chartType = 'sales'; // sales, profit, quantity

    public function mount($productCode)
    {
        $this->productCode = $productCode;
        $this->loadProductData();
        $this->loadAnalytics();
    }

    public function loadProductData()
    {
        // Buscar todos os produtos com este código
        $this->products = Product::where('product_code', $this->productCode)
            ->where('user_id', Auth::id())
            ->get();

        if ($this->products->isEmpty()) {
            abort(404, 'Produto não encontrado');
        }

        // Produto principal (primeiro encontrado ou ativo)
        $this->mainProduct = $this->products->where('status', 'ativo')->first() ?? $this->products->first();
        
        // Categoria do produto
        $this->category = Category::find($this->mainProduct->category_id);
    }

    public function loadAnalytics()
    {
        $productIds = $this->products->pluck('id');
        
        // Carregar vendas completas com relacionamentos
        $salesQuery = Sale::whereHas('saleItems', function($query) use ($productIds) {
                $query->whereIn('product_id', $productIds);
            })
            ->with(['client', 'saleItems' => function($query) use ($productIds) {
                $query->whereIn('product_id', $productIds);
            }])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filtro de período
        if ($this->period !== 'all') {
            $salesQuery = $this->applyPeriodFilter($salesQuery);
        }

        $this->salesData = $salesQuery->get();

        // Debug: Se não há vendas, criar dados de exemplo para teste (apenas em desenvolvimento)
        if (config('app.debug') && $this->salesData->isEmpty()) {
            // Comentário para criar algumas vendas de exemplo
            // Você pode descomentar isso temporariamente para testar a interface
            /*
            $this->salesData = collect([
                (object)[
                    'id' => 1,
                    'client_id' => null,
                    'client' => null,
                    'total_price' => 150.00,
                    'status' => 'concluida',
                    'payment_method' => 'dinheiro',
                    'tipo_pagamento' => 'a_vista',
                    'parcelas' => 1,
                    'created_at' => now()->subDays(2),
                    'saleItems' => collect([
                        (object)[
                            'product_id' => $this->mainProduct->id,
                            'quantity' => 2,
                            'price' => 100.00,
                            'price_sale' => 150.00
                        ]
                    ])
                ]
            ]);
            */
        }

        // Calcular analytics
        $this->analytics = $this->calculateAnalytics($productIds);
    }

    private function applyPeriodFilter($query)
    {
        $date = match($this->period) {
            '30days' => Carbon::now()->subDays(30),
            '3months' => Carbon::now()->subMonths(3),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonths(6)
        };

        return $query->where('created_at', '>=', $date);
    }

    private function calculateAnalytics($productIds)
    {
        // Total em estoque
        $totalStock = $this->products->sum('stock_quantity');
        
        // Valor total em estoque
        $stockValue = $this->products->sum(function($product) {
            return $product->stock_quantity * $product->price_sale;
        });

        // Estatísticas de vendas
        $salesStats = SaleItem::whereIn('product_id', $productIds)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', Auth::id())
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(sale_items.quantity) as total_quantity_sold,
                SUM(sale_items.quantity * sale_items.price_sale) as total_revenue,
                SUM(sale_items.quantity * products.price) as total_cost,
                AVG(sale_items.price_sale) as avg_sale_price,
                MAX(sales.created_at) as last_sale_date,
                MIN(sales.created_at) as first_sale_date
            ')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->first();

        // Calcular lucro
        $totalProfit = ($salesStats->total_revenue ?? 0) - ($salesStats->total_cost ?? 0);
        $profitMargin = $salesStats->total_revenue > 0 
            ? ($totalProfit / $salesStats->total_revenue) * 100 
            : 0;

        // Vendas por mês (últimos 12 meses)
        $monthlySales = SaleItem::whereIn('product_id', $productIds)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', Auth::id())
            ->where('sales.created_at', '>=', Carbon::now()->subYear())
            ->selectRaw('
                YEAR(sales.created_at) as year,
                MONTH(sales.created_at) as month,
                SUM(sale_items.quantity) as quantity,
                SUM(sale_items.quantity * sale_items.price_sale) as revenue
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top clientes
        $topClients = SaleItem::whereIn('product_id', $productIds)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('clients', 'sales.client_id', '=', 'clients.id')
            ->where('sales.user_id', Auth::id())
            ->selectRaw('
                clients.id,
                clients.name,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.quantity * sale_items.price_sale) as total_spent,
                COUNT(DISTINCT sales.id) as total_orders
            ')
            ->groupBy('clients.id', 'clients.name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        // Performance por variação do produto
        $variationPerformance = [];
        foreach ($this->products as $product) {
            $sales = SaleItem::where('product_id', $product->id)
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.user_id', Auth::id())
                ->selectRaw('
                    SUM(sale_items.quantity) as quantity_sold,
                    SUM(sale_items.quantity * sale_items.price_sale) as revenue
                ')
                ->first();

            $variationPerformance[] = [
                'product' => $product,
                'quantity_sold' => $sales->quantity_sold ?? 0,
                'revenue' => $sales->revenue ?? 0,
            ];
        }

        return [
            'total_stock' => $totalStock,
            'stock_value' => $stockValue,
            'total_sales' => $salesStats->total_sales ?? 0,
            'total_quantity_sold' => $salesStats->total_quantity_sold ?? 0,
            'total_revenue' => $salesStats->total_revenue ?? 0,
            'total_cost' => $salesStats->total_cost ?? 0,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
            'avg_sale_price' => $salesStats->avg_sale_price ?? 0,
            'last_sale_date' => $salesStats->last_sale_date,
            'first_sale_date' => $salesStats->first_sale_date,
            'monthly_sales' => $monthlySales,
            'top_clients' => $topClients,
            'variation_performance' => $variationPerformance,
        ];
    }

    public function updatedPeriod()
    {
        $this->loadAnalytics();
    }

    public function updatedChartType()
    {
        $this->loadAnalytics();
    }

    // Métodos para ações de vendas
    public function duplicateSale($saleId)
    {
        try {
            $originalSale = Sale::findOrFail($saleId);
            
            // Criar nova venda baseada na original
            $newSale = Sale::create([
                'client_id' => $originalSale->client_id,
                'user_id' => Auth::id(),
                'total_price' => $originalSale->total_price,
                'amount_paid' => 0, // Nova venda inicia sem pagamento
                'status' => 'pendente',
                'payment_method' => $originalSale->payment_method,
                'tipo_pagamento' => $originalSale->tipo_pagamento,
                'parcelas' => $originalSale->parcelas,
            ]);

            // Duplicar itens da venda
            foreach ($originalSale->saleItems as $item) {
                $newSale->saleItems()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'price_sale' => $item->price_sale,
                ]);
            }

            session()->flash('success', 'Venda duplicada com sucesso!');
            $this->loadAnalytics(); // Recarregar dados
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao duplicar venda: ' . $e->getMessage());
        }
    }

    public function createSale()
    {
        // Redirecionar para criação de venda com produto pré-selecionado
        return redirect()->route('sales.create', ['product_id' => $this->mainProduct->id]);
    }

    // Método para verificar dados no banco
    public function checkDatabaseData()
    {
        $productIds = $this->products->pluck('id');
        
        $debug = [
            'product_ids' => $productIds->toArray(),
            'total_sales' => Sale::where('user_id', Auth::id())->count(),
            'sales_with_items' => Sale::whereHas('saleItems')->where('user_id', Auth::id())->count(),
            'sale_items_count' => SaleItem::whereIn('product_id', $productIds)->count(),
            'sales_for_products' => Sale::whereHas('saleItems', function($query) use ($productIds) {
                $query->whereIn('product_id', $productIds);
            })->where('user_id', Auth::id())->count()
        ];
        
        session()->flash('debug_info', $debug);
        
        return $debug;
    }

    public function getCategoryIcon($icon)
    {
        return $icon ?: 'bi bi-tag';
    }

    // Dados para os gráficos
    public function getChartDataProperty()
    {
        $monthlySales = $this->analytics['monthly_sales'];
        
        $labels = [];
        $salesData = [];
        $revenueData = [];
        
        // Gerar últimos 12 meses
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->year . '-' . $date->month;
            
            $labels[] = $date->format('M Y');
            
            $monthData = $monthlySales->where('year', $date->year)
                                   ->where('month', $date->month)
                                   ->first();
            
            $salesData[] = $monthData ? (int)$monthData->quantity : 0;
            $revenueData[] = $monthData ? (float)$monthData->revenue : 0;
        }

        return [
            'labels' => $labels,
            'sales' => $salesData,
            'revenue' => $revenueData,
        ];
    }

    public function render()
    {
        return view('livewire.products.show-product', [
            'chartData' => $this->chartData,
        ]);
    }
}
