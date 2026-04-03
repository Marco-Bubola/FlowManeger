<?php

namespace App\Services\Dashboard;

use App\Models\MercadoLivreSyncLog;
use App\Models\MercadoLivreOrder;
use App\Models\MlPublication;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use App\Models\ShopeeSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardProductsMetricsService
{
    public function getMetrics(int $userId, int $selectedMonth, int $selectedYear, string $periodPreset = 'month', string $selectedChannel = 'all'): array
    {
        $referenceDate = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth();
        [$periodStart, $periodEnd] = $this->getPeriodBounds($referenceDate, $periodPreset);
        [$comparisonPreviousStart, $comparisonPreviousEnd, $comparisonLastYearStart, $comparisonLastYearEnd] = $this->getComparisonRanges($periodStart, $periodEnd);
        $rollingSixStart = $referenceDate->copy()->subMonths(5)->startOfMonth()->startOfDay();
        $idleLimitDate = $periodEnd->copy()->subDays(60);
        $periodDays = max(1, $periodStart->diffInDays($periodEnd) + 1);

        $products = Product::where('user_id', $userId)
            ->get([
                'id',
                'name',
                'price',
                'price_sale',
                'stock_quantity',
                'status',
                'product_code',
                'created_at',
                'tipo',
                'category_id',
            ]);

        $periodSalesBase = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd]);

        $pedidosPeriodo = (int) Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $faturamentoPeriodo = (float) (clone $periodSalesBase)
            ->sum(DB::raw('sale_items.price_sale * sale_items.quantity'));

        $unidadesVendidasPeriodo = (int) (clone $periodSalesBase)
            ->sum('sale_items.quantity');

        $ticketMedioPeriodo = $pedidosPeriodo > 0 ? $faturamentoPeriodo / $pedidosPeriodo : 0.0;

        $directDemand = (clone $periodSalesBase)
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as direct_quantity, SUM(sale_items.price_sale * sale_items.quantity) as direct_revenue')
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        $kitComponentDemand = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products as sold_products', 'sale_items.product_id', '=', 'sold_products.id')
            ->join('produto_componentes as pc', 'sold_products.id', '=', 'pc.kit_produto_id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->where('sold_products.tipo', 'kit')
            ->selectRaw('pc.componente_produto_id as product_id, SUM(sale_items.quantity * pc.quantidade) as kit_quantity')
            ->groupBy('pc.componente_produto_id')
            ->get()
            ->keyBy('product_id');

        $productsById = $products->keyBy('id');
        $effectiveDemand = $products->map(function ($product) use ($directDemand, $kitComponentDemand, $periodDays) {
            $directQty = (int) data_get($directDemand->get($product->id), 'direct_quantity', 0);
            $directRevenue = (float) data_get($directDemand->get($product->id), 'direct_revenue', 0);
            $kitQty = (int) data_get($kitComponentDemand->get($product->id), 'kit_quantity', 0);
            $effectiveQty = $directQty + $kitQty;
            $dailyDemand = $effectiveQty > 0 ? $effectiveQty / $periodDays : 0;
            $coverageDays = $dailyDemand > 0 ? $product->stock_quantity / $dailyDemand : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'tipo' => $product->tipo,
                'category_id' => $product->category_id,
                'stock_quantity' => (int) $product->stock_quantity,
                'direct_quantity' => $directQty,
                'kit_quantity' => $kitQty,
                'effective_quantity' => $effectiveQty,
                'direct_revenue' => $directRevenue,
                'daily_demand' => $dailyDemand,
                'coverage_days' => $coverageDays,
            ];
        })->keyBy('id');

        $produtosMaisVendidos = (clone $periodSalesBase)
            ->select(
                'products.id',
                'products.product_code',
                'products.name',
                'products.price',
                'products.price_sale',
                'products.stock_quantity',
                'products.tipo',
                DB::raw('SUM(sale_items.quantity) as total_vendido'),
                DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as receita_total')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.product_code', 'products.name', 'products.price', 'products.price_sale', 'products.stock_quantity', 'products.tipo')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($effectiveDemand) {
                return [
                    'id' => $item->id,
                    'product_code' => $item->product_code,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'price_sale' => (float) $item->price_sale,
                    'stock_quantity' => (int) $item->stock_quantity,
                    'tipo' => $item->tipo,
                    'total_vendido' => (int) $item->total_vendido,
                    'receita_total' => (float) $item->receita_total,
                    'demanda_total' => (int) data_get($effectiveDemand->get($item->id), 'effective_quantity', (int) $item->total_vendido),
                    'demanda_kits' => (int) data_get($effectiveDemand->get($item->id), 'kit_quantity', 0),
                    'margem_percentual' => (float) ($item->price_sale > 0 ? (($item->price_sale - $item->price) / $item->price_sale) * 100 : 0),
                ];
            })
            ->values()
            ->all();

        $produtosMaiorReceita = (clone $periodSalesBase)
            ->select(
                'products.id',
                'products.product_code',
                'products.name',
                'products.price',
                'products.price_sale',
                'products.stock_quantity',
                'products.tipo',
                DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as receita_total'),
                DB::raw('SUM(sale_items.quantity) as total_vendido')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.product_code', 'products.name', 'products.price', 'products.price_sale', 'products.stock_quantity', 'products.tipo')
            ->orderByDesc('receita_total')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'product_code' => $item->product_code,
                'name' => $item->name,
                'price' => (float) $item->price,
                'price_sale' => (float) $item->price_sale,
                'stock_quantity' => (int) $item->stock_quantity,
                'tipo' => $item->tipo,
                'receita_total' => (float) $item->receita_total,
                'total_vendido' => (int) $item->total_vendido,
                'margem_percentual' => (float) ($item->price_sale > 0 ? (($item->price_sale - $item->price) / $item->price_sale) * 100 : 0),
            ])
            ->values()
            ->all();

        $produtoMaiorLucro = (clone $periodSalesBase)
            ->select(
                'products.id',
                'products.product_code',
                'products.name',
                'products.price',
                'products.price_sale',
                'products.stock_quantity',
                'products.tipo',
                DB::raw('SUM((sale_items.price_sale - products.price) * sale_items.quantity) as lucro_total'),
                DB::raw('SUM(sale_items.quantity) as total_vendido')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.product_code', 'products.name', 'products.price', 'products.price_sale', 'products.stock_quantity', 'products.tipo')
            ->orderByDesc('lucro_total')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'product_code' => $item->product_code,
                'name' => $item->name,
                'price' => (float) $item->price,
                'price_sale' => (float) $item->price_sale,
                'stock_quantity' => (int) $item->stock_quantity,
                'tipo' => $item->tipo,
                'lucro_total' => (float) $item->lucro_total,
                'total_vendido' => (int) $item->total_vendido,
                'margem_percentual' => (float) ($item->price_sale > 0 ? (($item->price_sale - $item->price) / $item->price_sale) * 100 : 0),
            ])
            ->values()
            ->all();

        $dadosGraficoPizza = (clone $periodSalesBase)
            ->select(
                DB::raw('category.name as category_name'),
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as receita_total')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('category', 'products.category_id', '=', 'category.id_category')
            ->groupBy('category.name')
            ->orderByDesc('receita_total')
            ->get()
            ->map(fn ($item) => [
                'category_name' => $item->category_name,
                'total_sold' => (int) $item->total_sold,
                'receita_total' => (float) $item->receita_total,
            ])
            ->values()
            ->all();

        $ultimosProdutos = Product::with('category:id_category,name')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderByDesc('created_at')
            ->take(6)
            ->get()
            ->toArray();

        if (empty($ultimosProdutos)) {
            $ultimosProdutos = Product::with('category:id_category,name')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->take(6)
                ->get()
                ->toArray();
        }

        $inventoryProducts = $products->where('stock_quantity', '>', 0);
        $totalDespesasProdutos = (float) $inventoryProducts->sum(fn ($product) => (float) $product->price * (int) $product->stock_quantity);
        $totalReceitasProdutos = (float) $inventoryProducts->sum(fn ($product) => (float) $product->price_sale * (int) $product->stock_quantity);
        $totalSaldoProdutos = $totalReceitasProdutos - $totalDespesasProdutos;
        $margemMediaEstoque = $totalReceitasProdutos > 0 ? (($totalReceitasProdutos - $totalDespesasProdutos) / $totalReceitasProdutos) * 100 : 0;

        $soldProductIdsLast60 = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('sales.created_at', '>=', $idleLimitDate)
            ->pluck('sale_items.product_id')
            ->unique();

        $kitComponentIdsLast60 = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products as sold_products', 'sale_items.product_id', '=', 'sold_products.id')
            ->join('produto_componentes as pc', 'sold_products.id', '=', 'pc.kit_produto_id')
            ->where('sales.user_id', $userId)
            ->where('sales.created_at', '>=', $idleLimitDate)
            ->where('sold_products.tipo', 'kit')
            ->pluck('pc.componente_produto_id')
            ->unique();

        $activeInLast60 = $soldProductIdsLast60->merge($kitComponentIdsLast60)->unique()->values();

        $produtosParadosQuery = Product::where('user_id', $userId)
            ->whereNotIn('id', $activeInLast60)
            ->orderByDesc('updated_at');

        $produtosSemGiro = (clone $produtosParadosQuery)->count();
        $produtosParados = $produtosParadosQuery->take(6)->get()->toArray();

        $produtosEstoqueBaixoAltaDemanda = $effectiveDemand
            ->filter(fn ($item) => $item['stock_quantity'] > 0 && $item['stock_quantity'] < 10 && $item['effective_quantity'] > 0)
            ->sortByDesc('effective_quantity')
            ->take(8)
            ->map(fn ($item) => [
                'product_code' => $item['product_code'],
                'name' => $item['name'],
                'stock_quantity' => $item['stock_quantity'],
                'total_vendido' => $item['effective_quantity'],
                'receita_total' => $item['direct_revenue'],
                'demanda_kits' => $item['kit_quantity'],
            ])
            ->values()
            ->all();

        $totalProdutos = $products->count();
        $totalProdutosEstoque = (int) $products->where('stock_quantity', '>', 0)->sum('stock_quantity');
        $produtosEstoqueCritico = $products->filter(fn ($product) => $product->stock_quantity > 0 && $product->stock_quantity < 10)->count();
        $produtosSemEstoque = $products->where('stock_quantity', 0)->count();
        $produtosAtivos = $products->where('status', 'ativo')->count();
        $produtosInativos = $products->where('status', 'inativo')->count();
        $produtosDescontinuados = $products->where('status', 'descontinuado')->count();
        $produtoMaiorEstoque = $products->sortByDesc('stock_quantity')->first();
        $produtoMaisVendido = $produtosMaisVendidos[0] ?? null;
        $giroMedioProdutos = $totalProdutos > 0 ? $unidadesVendidasPeriodo / $totalProdutos : 0.0;
        $participacaoTopProdutos = $faturamentoPeriodo > 0 ? (collect($produtosMaiorReceita)->sum('receita_total') / $faturamentoPeriodo) * 100 : 0.0;

        $statusProdutos = [
            ['label' => 'Ativos', 'value' => $produtosAtivos],
            ['label' => 'Inativos', 'value' => $produtosInativos],
            ['label' => 'Descontinuados', 'value' => $produtosDescontinuados],
            ['label' => 'Sem estoque', 'value' => $produtosSemEstoque],
        ];

        $vendasMensaisProdutos = $this->buildMonthlyTrend($userId, $referenceDate, $rollingSixStart, $periodEnd);

        $produtosComMlAtivo = Product::where('user_id', $userId)
            ->whereHas('mlPublications', fn ($query) => $query->where('status', 'active'))
            ->count();

        $produtosComShopeeAtivo = Product::where('user_id', $userId)
            ->whereHas('shopeePublications', fn ($query) => $query->where('status', 'published'))
            ->count();

        $kitsVendidosPeriodo = (int) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->where('products.tipo', 'kit')
            ->sum('sale_items.quantity');

        $receitaKitsPeriodo = (float) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->where('products.tipo', 'kit')
            ->sum(DB::raw('sale_items.price_sale * sale_items.quantity'));

        $componentesConsumidosViaKits = (int) $kitComponentDemand->sum('kit_quantity');
        $produtosLigadosKits = (int) DB::table('produto_componentes')
            ->join('products as kits', 'produto_componentes.kit_produto_id', '=', 'kits.id')
            ->where('kits.user_id', $userId)
            ->distinct('produto_componentes.componente_produto_id')
            ->count('produto_componentes.componente_produto_id');

        $topKits = (clone $periodSalesBase)
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('products.tipo', 'kit')
            ->select(
                'products.id',
                'products.name',
                'products.product_code',
                DB::raw('SUM(sale_items.quantity) as total_vendido'),
                DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as receita_total')
            )
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderByDesc('total_vendido')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'product_code' => $item->product_code,
                'total_vendido' => (int) $item->total_vendido,
                'receita_total' => (float) $item->receita_total,
            ])
            ->values()
            ->all();

        $categoryNames = DB::table('category')
            ->whereIn('id_category', $products->pluck('category_id')->filter()->unique()->values())
            ->pluck('name', 'id_category');

        $coberturaProdutos = $effectiveDemand
            ->filter(fn ($item) => $item['effective_quantity'] > 0)
            ->sortBy(fn ($item) => $item['coverage_days'] ?? PHP_INT_MAX)
            ->take(8)
            ->map(function ($item) {
                return [
                    'name' => $item['name'],
                    'product_code' => $item['product_code'],
                    'stock_quantity' => $item['stock_quantity'],
                    'daily_demand' => round($item['daily_demand'], 2),
                    'coverage_days' => $item['coverage_days'] !== null ? round($item['coverage_days'], 1) : null,
                    'kit_quantity' => $item['kit_quantity'],
                    'effective_quantity' => $item['effective_quantity'],
                    'tipo' => $item['tipo'],
                ];
            })
            ->values()
            ->all();

        $coberturaCategorias = $effectiveDemand
            ->groupBy('category_id')
            ->map(function (Collection $items, $categoryId) use ($categoryNames, $periodDays) {
                $effectiveQty = (int) $items->sum('effective_quantity');
                $stock = (int) $items->sum('stock_quantity');
                $dailyDemand = $effectiveQty > 0 ? $effectiveQty / $periodDays : 0;
                $coverageDays = $dailyDemand > 0 ? $stock / $dailyDemand : null;

                return [
                    'category_name' => $categoryNames[$categoryId] ?? 'Sem categoria',
                    'stock_quantity' => $stock,
                    'effective_quantity' => $effectiveQty,
                    'daily_demand' => round($dailyDemand, 2),
                    'coverage_days' => $coverageDays !== null ? round($coverageDays, 1) : null,
                ];
            })
            ->filter(fn ($item) => $item['effective_quantity'] > 0)
            ->sortBy(fn ($item) => $item['coverage_days'] ?? PHP_INT_MAX)
            ->take(6)
            ->values()
            ->all();

        $currentRevenue = $faturamentoPeriodo;
        $previousRevenue = $this->sumRevenueBetween($userId, $comparisonPreviousStart, $comparisonPreviousEnd);
        $lastYearRevenue = $this->sumRevenueBetween($userId, $comparisonLastYearStart, $comparisonLastYearEnd);
        $currentUnits = $unidadesVendidasPeriodo;
        $previousUnits = $this->sumUnitsBetween($userId, $comparisonPreviousStart, $comparisonPreviousEnd);
        $lastYearUnits = $this->sumUnitsBetween($userId, $comparisonLastYearStart, $comparisonLastYearEnd);
        $currentProfit = $this->sumProfitBetween($userId, $periodStart, $periodEnd);
        $previousProfit = $this->sumProfitBetween($userId, $comparisonPreviousStart, $comparisonPreviousEnd);
        $lastYearProfit = $this->sumProfitBetween($userId, $comparisonLastYearStart, $comparisonLastYearEnd);

        $periodComparison = [
            'labels' => ['Atual', 'Anterior', 'Ano passado'],
            'revenue' => [$currentRevenue, $previousRevenue, $lastYearRevenue],
            'units' => [$currentUnits, $previousUnits, $lastYearUnits],
            'profit' => [$currentProfit, $previousProfit, $lastYearProfit],
        ];

        $periodSummary = [
            ['label' => 'Receita do periodo', 'value' => $currentRevenue, 'delta' => $this->calculateDelta($currentRevenue, $previousRevenue)],
            ['label' => 'Unidades vendidas', 'value' => $currentUnits, 'delta' => $this->calculateDelta($currentUnits, $previousUnits)],
            ['label' => 'Lucro estimado', 'value' => $currentProfit, 'delta' => $this->calculateDelta($currentProfit, $previousProfit)],
        ];

        $marketplacePeriodMetrics = $this->buildMarketplaceMetrics($userId, $periodStart, $periodEnd, $rollingSixStart, $referenceDate);
        $channelMetrics = $this->buildChannelMetrics($userId, $periodStart, $periodEnd);

        return [
            'periodLabel' => $this->getPeriodLabel($referenceDate, $selectedMonth, $selectedYear, $periodPreset),
            'selectedChannel' => $selectedChannel,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'periodPreset' => $periodPreset,
            'ticketMedio' => $ticketMedioPeriodo,
            'ticketMedioPeriodo' => $ticketMedioPeriodo,
            'totalProdutos' => $totalProdutos,
            'totalProdutosEstoque' => $totalProdutosEstoque,
            'produtosSemEstoque' => $produtosSemEstoque,
            'produtosEstoqueCritico' => $produtosEstoqueCritico,
            'produtosSemGiro' => $produtosSemGiro,
            'produtosAtivos' => $produtosAtivos,
            'produtosInativos' => $produtosInativos,
            'produtosDescontinuados' => $produtosDescontinuados,
            'produtosComMlAtivo' => $produtosComMlAtivo,
            'produtosComShopeeAtivo' => $produtosComShopeeAtivo,
            'unidadesVendidasPeriodo' => $unidadesVendidasPeriodo,
            'pedidosPeriodo' => $pedidosPeriodo,
            'categoriasAtivasPeriodo' => count($dadosGraficoPizza),
            'totalDespesasProdutos' => $totalDespesasProdutos,
            'totalReceitasProdutos' => $totalReceitasProdutos,
            'totalSaldoProdutos' => $totalSaldoProdutos,
            'margemMediaEstoque' => $margemMediaEstoque,
            'giroMedioProdutos' => $giroMedioProdutos,
            'faturamentoPeriodo' => $faturamentoPeriodo,
            'lucroEstimadoPeriodo' => $currentProfit,
            'participacaoTopProdutos' => $participacaoTopProdutos,
            'produtoMaiorEstoque' => $produtoMaiorEstoque,
            'produtoMaisVendido' => $produtoMaisVendido,
            'ultimosProdutos' => $ultimosProdutos,
            'produtosParados' => $produtosParados,
            'produtosMaisVendidos' => $produtosMaisVendidos,
            'produtosMaiorReceita' => $produtosMaiorReceita,
            'produtoMaiorLucro' => $produtoMaiorLucro,
            'produtosEstoqueBaixoAltaDemanda' => $produtosEstoqueBaixoAltaDemanda,
            'dadosGraficoPizza' => $dadosGraficoPizza,
            'statusProdutos' => $statusProdutos,
            'vendasMensaisProdutos' => $vendasMensaisProdutos,
            'periodComparison' => $periodComparison,
            'periodSummary' => $periodSummary,
            'kitsVendidosPeriodo' => $kitsVendidosPeriodo,
            'receitaKitsPeriodo' => $receitaKitsPeriodo,
            'componentesConsumidosViaKits' => $componentesConsumidosViaKits,
            'produtosLigadosKits' => $produtosLigadosKits,
            'topKits' => $topKits,
            'coberturaProdutos' => $coberturaProdutos,
            'coberturaCategorias' => $coberturaCategorias,
            'marketplacePeriodMetrics' => $marketplacePeriodMetrics,
            'channelMetrics' => $channelMetrics,
        ];
    }

    protected function buildChannelMetrics(int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $internal = $this->buildInternalChannelMetrics($userId, $periodStart, $periodEnd);
        $kits = $this->buildKitChannelMetrics($userId, $periodStart, $periodEnd);
        $ml = $this->buildMarketplaceOrderChannelMetrics('ml', $userId, $periodStart, $periodEnd);
        $shopee = $this->buildMarketplaceOrderChannelMetrics('shopee', $userId, $periodStart, $periodEnd);

        return [
            'all' => [
                'label' => 'Visão geral',
                'cards' => [
                    ['label' => 'Loja interna', 'value' => $internal['revenue'], 'type' => 'currency'],
                    ['label' => 'Kits', 'value' => $kits['revenue'], 'type' => 'currency'],
                    ['label' => 'Mercado Livre', 'value' => $ml['revenue'], 'type' => 'currency'],
                    ['label' => 'Shopee', 'value' => $shopee['revenue'], 'type' => 'currency'],
                ],
                'topProducts' => array_slice(array_merge($internal['topProducts'], $kits['topProducts']), 0, 6),
            ],
            'internal' => $internal,
            'kits' => $kits,
            'ml' => $ml,
            'shopee' => $shopee,
        ];
    }

    protected function buildInternalChannelMetrics(int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $base = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->where('products.tipo', '!=', 'kit');

        $topProducts = (clone $base)
            ->select('products.name', 'products.product_code', DB::raw('SUM(sale_items.quantity) as total_units'), DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as total_revenue'))
            ->groupBy('products.name', 'products.product_code')
            ->orderByDesc('total_units')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'product_code' => $item->product_code,
                'units' => (int) $item->total_units,
                'revenue' => (float) $item->total_revenue,
            ])
            ->values()
            ->all();

        return [
            'label' => 'Loja interna',
            'cards' => [
                ['label' => 'Receita', 'value' => (float) (clone $base)->sum(DB::raw('sale_items.price_sale * sale_items.quantity')), 'type' => 'currency'],
                ['label' => 'Unidades', 'value' => (int) (clone $base)->sum('sale_items.quantity'), 'type' => 'number'],
                ['label' => 'Pedidos', 'value' => (int) (clone $base)->distinct('sales.id')->count('sales.id'), 'type' => 'number'],
            ],
            'revenue' => (float) (clone $base)->sum(DB::raw('sale_items.price_sale * sale_items.quantity')),
            'topProducts' => $topProducts,
        ];
    }

    protected function buildKitChannelMetrics(int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $base = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->where('products.tipo', 'kit');

        $topProducts = (clone $base)
            ->select('products.name', 'products.product_code', DB::raw('SUM(sale_items.quantity) as total_units'), DB::raw('SUM(sale_items.price_sale * sale_items.quantity) as total_revenue'))
            ->groupBy('products.name', 'products.product_code')
            ->orderByDesc('total_units')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'product_code' => $item->product_code,
                'units' => (int) $item->total_units,
                'revenue' => (float) $item->total_revenue,
            ])
            ->values()
            ->all();

        return [
            'label' => 'Kits',
            'cards' => [
                ['label' => 'Receita', 'value' => (float) (clone $base)->sum(DB::raw('sale_items.price_sale * sale_items.quantity')), 'type' => 'currency'],
                ['label' => 'Kits vendidos', 'value' => (int) (clone $base)->sum('sale_items.quantity'), 'type' => 'number'],
                ['label' => 'Pedidos', 'value' => (int) (clone $base)->distinct('sales.id')->count('sales.id'), 'type' => 'number'],
            ],
            'revenue' => (float) (clone $base)->sum(DB::raw('sale_items.price_sale * sale_items.quantity')),
            'topProducts' => $topProducts,
        ];
    }

    protected function buildMarketplaceOrderChannelMetrics(string $platform, int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $isMl = $platform === 'ml';
        $orders = $isMl
            ? MercadoLivreOrder::whereBetween('date_created', [$periodStart, $periodEnd])->get()
            : ShopeeOrder::where('user_id', $userId)->whereBetween('shopee_created_at', [$periodStart, $periodEnd])->get();

        if ($orders->isEmpty()) {
            return [
                'label' => $isMl ? 'Mercado Livre' : 'Shopee',
                'cards' => [
                    ['label' => 'Receita', 'value' => 0, 'type' => 'currency'],
                    ['label' => 'Pedidos', 'value' => 0, 'type' => 'number'],
                    ['label' => 'Unidades mapeadas', 'value' => 0, 'type' => 'number'],
                ],
                'revenue' => 0.0,
                'topProducts' => [],
            ];
        }

        $publications = $isMl
            ? MlPublication::with('products')->where('user_id', $userId)->whereIn('ml_item_id', $orders->pluck('ml_item_id')->filter()->unique()->values())->get()->keyBy('ml_item_id')
            : ShopeePublication::with('products')->where('user_id', $userId)->whereIn('shopee_item_id', $orders->pluck('shopee_item_id')->filter()->unique()->values())->get()->keyBy('shopee_item_id');

        $productStats = [];
        $mappedUnits = 0;
        $revenue = 0.0;

        foreach ($orders as $order) {
            $publicationKey = $isMl ? $order->ml_item_id : $order->shopee_item_id;
            $publication = $publications->get($publicationKey);
            if (! $publication) {
                continue;
            }

            $orderQuantity = $isMl ? (int) ($order->quantity ?? 1) : $this->extractShopeeOrderQuantity($order->order_items ?? []);
            $orderRevenue = (float) ($order->total_amount ?? 0);
            $revenue += $orderRevenue;

            $baseValue = max(0.01, (float) $publication->products->sum(fn ($product) => ((float) $product->price_sale ?: 1) * ((int) ($product->pivot->quantity ?? 1))));

            foreach ($publication->products as $product) {
                $pivotQuantity = (int) ($product->pivot->quantity ?? 1);
                $allocatedUnits = $orderQuantity * $pivotQuantity;
                $shareBase = (((float) $product->price_sale ?: 1) * $pivotQuantity) / $baseValue;
                $allocatedRevenue = $orderRevenue * $shareBase;
                $mappedUnits += $allocatedUnits;

                if (! isset($productStats[$product->id])) {
                    $productStats[$product->id] = [
                        'name' => $product->name,
                        'product_code' => $product->product_code,
                        'units' => 0,
                        'revenue' => 0.0,
                    ];
                }

                $productStats[$product->id]['units'] += $allocatedUnits;
                $productStats[$product->id]['revenue'] += $allocatedRevenue;
            }
        }

        $topProducts = collect($productStats)
            ->sortByDesc('units')
            ->take(6)
            ->values()
            ->map(fn ($item) => [
                'name' => $item['name'],
                'product_code' => $item['product_code'],
                'units' => (int) $item['units'],
                'revenue' => (float) $item['revenue'],
            ])
            ->all();

        return [
            'label' => $isMl ? 'Mercado Livre' : 'Shopee',
            'cards' => [
                ['label' => 'Receita', 'value' => $revenue, 'type' => 'currency'],
                ['label' => 'Pedidos', 'value' => (int) $orders->count(), 'type' => 'number'],
                ['label' => 'Unidades mapeadas', 'value' => (int) $mappedUnits, 'type' => 'number'],
            ],
            'revenue' => $revenue,
            'topProducts' => $topProducts,
        ];
    }

    protected function extractShopeeOrderQuantity(array $orderItems): int
    {
        if (empty($orderItems)) {
            return 1;
        }

        $quantity = 0;

        foreach ($orderItems as $item) {
            $quantity += (int) ($item['model_quantity_purchased'] ?? $item['quantity'] ?? $item['item_quantity'] ?? 1);
        }

        return max(1, $quantity);
    }

    protected function buildMarketplaceMetrics(int $userId, Carbon $periodStart, Carbon $periodEnd, Carbon $rollingSixStart, Carbon $referenceDate): array
    {
        $mlCreated = MlPublication::where('user_id', $userId)->whereBetween('created_at', [$periodStart, $periodEnd]);
        $shopeeCreated = ShopeePublication::where('user_id', $userId)->whereBetween('created_at', [$periodStart, $periodEnd]);
        $mlSyncs = MercadoLivreSyncLog::where('user_id', $userId)->where('entity_type', 'publication')->whereBetween('created_at', [$periodStart, $periodEnd]);
        $shopeeSyncs = ShopeeSyncLog::where('user_id', $userId)->where('entity_type', 'publication')->whereBetween('created_at', [$periodStart, $periodEnd]);

        $mlMonthly = MlPublication::where('user_id', $userId)
            ->where('created_at', '>=', $rollingSixStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $shopeeMonthly = ShopeePublication::where('user_id', $userId)
            ->where('created_at', '>=', $rollingSixStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $trend = collect(range(5, 0))
            ->map(function ($monthsAgo) use ($referenceDate, $mlMonthly, $shopeeMonthly) {
                $date = $referenceDate->copy()->subMonths($monthsAgo);
                $key = $date->format('Y-m');

                return [
                    'label' => ucfirst($date->translatedFormat('M/y')),
                    'ml' => (int) ($mlMonthly[$key] ?? 0),
                    'shopee' => (int) ($shopeeMonthly[$key] ?? 0),
                ];
            })
            ->push([
                'label' => ucfirst($referenceDate->translatedFormat('M/y')),
                'ml' => (int) ($mlMonthly[$referenceDate->format('Y-m')] ?? 0),
                'shopee' => (int) ($shopeeMonthly[$referenceDate->format('Y-m')] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'cards' => [
                ['label' => 'ML publicadas no período', 'value' => (int) $mlCreated->count(), 'tone' => 'ml'],
                ['label' => 'Shopee publicadas no período', 'value' => (int) $shopeeCreated->count(), 'tone' => 'shopee'],
                ['label' => 'Syncs ML no período', 'value' => (int) $mlSyncs->count(), 'tone' => 'sync'],
                ['label' => 'Syncs Shopee no período', 'value' => (int) $shopeeSyncs->count(), 'tone' => 'sync'],
                ['label' => 'Erros ML no período', 'value' => (int) (clone $mlSyncs)->where('status', 'error')->count(), 'tone' => 'error'],
                ['label' => 'Erros Shopee no período', 'value' => (int) (clone $shopeeSyncs)->where('status', 'error')->count(), 'tone' => 'error'],
            ],
            'trend' => $trend,
        ];
    }

    protected function buildMonthlyTrend(int $userId, Carbon $referenceDate, Carbon $rollingSixStart, Carbon $periodEnd): array
    {
        $monthlySales = SaleItem::selectRaw("DATE_FORMAT(sales.created_at, '%Y-%m') as sale_month")
            ->selectRaw('SUM(sale_items.quantity) as total_units')
            ->selectRaw('SUM(sale_items.price_sale * sale_items.quantity) as total_revenue')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('sales.created_at', '>=', $rollingSixStart)
            ->where('sales.created_at', '<=', $periodEnd)
            ->groupBy('sale_month')
            ->orderBy('sale_month')
            ->get()
            ->keyBy('sale_month');

        return collect(range(5, 0))
            ->map(function ($monthsAgo) use ($referenceDate, $monthlySales) {
                $date = $referenceDate->copy()->subMonths($monthsAgo);
                $key = $date->format('Y-m');
                $monthData = $monthlySales->get($key);

                return [
                    'label' => ucfirst($date->translatedFormat('M/y')),
                    'total_units' => (int) data_get($monthData, 'total_units', 0),
                    'total_revenue' => (float) data_get($monthData, 'total_revenue', 0),
                ];
            })
            ->push([
                'label' => ucfirst($referenceDate->translatedFormat('M/y')),
                'total_units' => (int) data_get($monthlySales->get($referenceDate->format('Y-m')), 'total_units', 0),
                'total_revenue' => (float) data_get($monthlySales->get($referenceDate->format('Y-m')), 'total_revenue', 0),
            ])
            ->values()
            ->all();
    }

    protected function sumRevenueBetween(int $userId, Carbon $start, Carbon $end): float
    {
        return (float) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum(DB::raw('sale_items.price_sale * sale_items.quantity'));
    }

    protected function sumUnitsBetween(int $userId, Carbon $start, Carbon $end): int
    {
        return (int) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum('sale_items.quantity');
    }

    protected function sumProfitBetween(int $userId, Carbon $start, Carbon $end): float
    {
        return (float) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum(DB::raw('(sale_items.price_sale - products.price) * sale_items.quantity'));
    }

    protected function calculateDelta(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? 100.0 : 0.0;
        }

        return (((float) $current - (float) $previous) / (float) $previous) * 100;
    }

    protected function getPeriodBounds(Carbon $referenceDate, string $periodPreset): array
    {
        return match ($periodPreset) {
            'today' => [$referenceDate->copy()->startOfDay(), $referenceDate->copy()->endOfDay()],
            'quarter' => [$referenceDate->copy()->startOfQuarter()->startOfDay(), $referenceDate->copy()->endOfQuarter()->endOfDay()],
            'year' => [$referenceDate->copy()->startOfYear()->startOfDay(), $referenceDate->copy()->endOfYear()->endOfDay()],
            default => [$referenceDate->copy()->startOfMonth()->startOfDay(), $referenceDate->copy()->endOfMonth()->endOfDay()],
        };
    }

    protected function getComparisonRanges(Carbon $periodStart, Carbon $periodEnd): array
    {
        $durationDays = $periodStart->diffInDays($periodEnd);
        $comparisonPreviousEnd = $periodStart->copy()->subDay()->endOfDay();
        $comparisonPreviousStart = $comparisonPreviousEnd->copy()->subDays($durationDays)->startOfDay();
        $comparisonLastYearStart = $periodStart->copy()->subYear();
        $comparisonLastYearEnd = $periodEnd->copy()->subYear();

        return [$comparisonPreviousStart, $comparisonPreviousEnd, $comparisonLastYearStart, $comparisonLastYearEnd];
    }

    protected function getPeriodLabel(Carbon $referenceDate, int $selectedMonth, int $selectedYear, string $periodPreset): string
    {
        $monthOptions = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return match ($periodPreset) {
            'today' => 'Hoje',
            'quarter' => 'Trimestre de ' . ucfirst($referenceDate->translatedFormat('M/Y')),
            'year' => 'Ano de ' . $selectedYear,
            default => ucfirst($monthOptions[$selectedMonth] ?? $referenceDate->translatedFormat('F')) . ' de ' . $selectedYear,
        };
    }
}
