<div x-data="{
    activeTab: 'overview',
    chartInitialized: false
}"
    x-init="$watch('activeTab', () => setTimeout(() => { window.dispatchEvent(new Event('resize')); if (window.dashInitCharts) window.dashInitCharts(); }, 90))"
    class="show-product-page w-full mobile-393-base">

    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-mobile.css') }}">

    {{-- Compactação geral da página de produto (cards de gráfico mais densos) --}}
    <style>
        /* Gráficos dash mais compactos nesta página */
        .show-product-page .dash-chart { min-height: 16rem !important; }
        /* Reduz o respiro exagerado das seções de gráfico */
        .show-product-page .gap-10 { gap: 1.25rem !important; }
        .show-product-page .mb-10 { margin-bottom: 1.25rem !important; }
        .show-product-page .gap-8 { gap: 1rem !important; }
        /* Cards de gráfico/analytics: padding e títulos menores */
        .show-product-page .chart-container { margin-top: .25rem; }
        @media (min-width: 1024px) {
            .show-product-page .p-8 { padding: 1.25rem !important; }
        }
        .show-product-page .p-8 .text-2xl { font-size: 1.15rem !important; }
        .show-product-page .p-8 .mb-8 { margin-bottom: 1rem !important; }
        .show-product-page .p-8 .w-12.h-12 { width: 2.4rem !important; height: 2.4rem !important; }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-product-ultrawide.css') }}">

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-4 sm:px-5 py-3.5">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <!-- Título e Info do Produto -->
            <div class="flex items-center gap-3 min-w-0">
                <!-- Voltar -->
                <a href="{{ route('products.index') }}"
                    class="shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-700 text-slate-500 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60 transition-all shadow-sm group">
                    <i class="bi bi-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>

                <!-- Imagem do produto -->
                <div class="relative w-14 h-14 rounded-2xl overflow-hidden shadow-lg bg-gradient-to-br from-white to-slate-100 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center shrink-0 group">
                    @if($mainProduct->image)
                    <img src="{{ asset('storage/products/' . $mainProduct->image) }}" alt="{{ $mainProduct->name }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    @else
                    <i class="bi bi-box-seam text-2xl text-slate-400 dark:text-slate-500"></i>
                    @endif
                </div>

                <div class="min-w-0">
                    <h1 class="text-lg sm:text-2xl font-black bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent truncate leading-tight">
                        {{ $mainProduct->name }}
                    </h1>
                    <!-- Badges do produto (compactos) -->
                    <div class="flex items-center gap-1.5 flex-wrap mt-1">
                        @if(($mainProduct->tipo ?? 'simples') === 'kit')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white shadow">
                            <i class="bi bi-boxes mr-1"></i>KIT · {{ $kitMontaveis }} montáveis
                        </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow">
                            <i class="bi bi-upc-scan mr-1"></i>{{ $productCode }}
                        </span>
                        @if($category)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-white/80 dark:bg-slate-700/80 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-700/60">
                            <i class="{{ $this->getCategoryIcon($category->icone) }} mr-1"></i>{{ $category->name }}
                        </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold {{ $products->count() > 1 ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white' }}">
                            <i class="bi bi-layers mr-1"></i>{{ $products->count() }} {{ $products->count() === 1 ? 'var.' : 'vars.' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ações (compactas, ícone + label) -->
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('products.edit', $mainProduct) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white/80 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700/60 transition shadow-sm">
                    <i class="bi bi-pencil-fill text-amber-500"></i><span class="hidden sm:inline">Editar</span>
                </a>
                <button wire:click="$dispatch('openExportModal', { productId: {{ $mainProduct->id }} })"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 shadow transition">
                    <i class="bi bi-file-earmark-arrow-down"></i><span class="hidden sm:inline">Exportar</span>
                </button>
                <button wire:click="duplicateProduct({{ $mainProduct->id }})"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-purple-500 to-fuchsia-500 hover:from-purple-600 hover:to-fuchsia-600 shadow transition">
                    <i class="bi bi-files"></i><span class="hidden sm:inline">Duplicar</span>
                </button>
                <button wire:click="confirmDelete({{ $mainProduct->id }})"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 shadow transition">
                    <i class="bi bi-trash3"></i><span class="hidden sm:inline">Excluir</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== PÁGINA ÚNICA (sem abas) — layout denso com dash ===== -->
<div class="space-y-4">
    {{-- ============ COMPOSIÇÃO DO KIT (apenas para kits) ============ --}}
    @if(($mainProduct->tipo ?? 'simples') === 'kit' && $kitComponents->isNotEmpty())
    @php
        $kitCustoTotal = $kitComponents->sum('cost_total');
        $kitVendaTotal = $kitComponents->sum('sale_total');
        $kitMargem = $kitVendaTotal > 0 ? (($kitVendaTotal - $kitCustoTotal) / $kitVendaTotal) * 100 : 0;
    @endphp
    <div class="kit-components-card relative overflow-hidden rounded-3xl border border-purple-200/60 dark:border-purple-800/50 bg-gradient-to-br from-white via-purple-50/40 to-indigo-50/30 dark:from-slate-900 dark:via-purple-950/30 dark:to-slate-900 shadow-2xl mb-4">
        <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-gradient-to-br from-purple-400/20 to-pink-400/15 blur-3xl pointer-events-none"></div>
        <div class="relative px-5 sm:px-6 py-4 border-b border-purple-200/50 dark:border-purple-800/40 flex flex-wrap items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30 shrink-0"><i class="bi bi-boxes text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-black text-slate-800 dark:text-white leading-tight flex items-center gap-2">Composição do Kit
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-700 dark:text-purple-300">{{ $kitComponents->count() }} {{ $kitComponents->count() === 1 ? 'item' : 'itens' }}</span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Produtos que compõem este kit e seu estoque</p>
            </div>
            <div class="shrink-0 text-right rounded-2xl px-4 py-2 bg-gradient-to-br {{ $kitMontaveis > 0 ? 'from-emerald-500/15 to-teal-500/15 border border-emerald-400/40' : 'from-rose-500/15 to-red-500/15 border border-rose-400/40' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider {{ $kitMontaveis > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">Montáveis agora</p>
                <p class="text-2xl font-black {{ $kitMontaveis > 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-rose-700 dark:text-rose-200' }} leading-none">{{ $kitMontaveis }}</p>
            </div>
        </div>
        <div class="relative px-5 sm:px-6 py-3 grid grid-cols-3 gap-2 border-b border-purple-200/40 dark:border-purple-800/30">
            <div class="rounded-xl bg-slate-100/70 dark:bg-slate-800/50 px-3 py-2"><p class="text-[10px] font-bold uppercase text-slate-400">Custo dos itens</p><p class="text-sm font-black text-slate-700 dark:text-slate-200">R$ {{ number_format($kitCustoTotal, 2, ',', '.') }}</p></div>
            <div class="rounded-xl bg-purple-100/60 dark:bg-purple-900/30 px-3 py-2"><p class="text-[10px] font-bold uppercase text-purple-500 dark:text-purple-300">Venda dos itens</p><p class="text-sm font-black text-purple-700 dark:text-purple-200">R$ {{ number_format($kitVendaTotal, 2, ',', '.') }}</p></div>
            <div class="rounded-xl bg-emerald-100/60 dark:bg-emerald-900/30 px-3 py-2"><p class="text-[10px] font-bold uppercase text-emerald-500 dark:text-emerald-300">Margem</p><p class="text-sm font-black text-emerald-700 dark:text-emerald-200">{{ number_format($kitMargem, 1) }}%</p></div>
        </div>
        <div class="relative p-4 sm:p-5">
            <div class="kit-comp-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach($kitComponents as $kc)
                <div class="group relative rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-900/60 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-stretch gap-3 p-3">
                        <div class="relative w-16 h-16 rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-950 shrink-0 flex items-center justify-center">
                            <img src="{{ !empty($kc['image']) ? asset('storage/products/' . $kc['image']) : asset('storage/products/product-placeholder.png') }}" alt="{{ $kc['name'] }}" class="w-full h-full object-contain p-1 group-hover:scale-105 transition-transform" />
                            <span class="absolute top-1 left-1 w-5 h-5 rounded-md bg-purple-600 text-white text-[10px] font-black flex items-center justify-center shadow">{{ $kc['quantity'] }}x</span>
                        </div>
                        <div class="min-w-0 flex-1 flex flex-col justify-between">
                            <div><p class="text-xs font-bold text-slate-800 dark:text-white truncate" title="{{ $kc['name'] }}">{{ $kc['name'] }}</p><p class="text-[10px] text-slate-400 font-mono">#{{ $kc['code'] }}</p></div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                @php $stockLow = $kc['stock'] <= ($kc['quantity'] * 3); @endphp
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $stockLow ? 'bg-rose-500/15 text-rose-600 dark:text-rose-300' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' }}"><i class="bi bi-stack text-[9px]"></i>{{ $kc['stock'] }}</span>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-500/15 text-purple-600 dark:text-purple-300"><i class="bi bi-currency-dollar text-[9px]"></i>{{ number_format($kc['sale'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-3 pb-2.5">
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider mb-1"><span class="text-slate-400">Rende</span><span class="{{ $kc['kits_possiveis'] <= $kitMontaveis ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $kc['kits_possiveis'] }} kits</span></div>
                        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">@php $w = $kitMontaveis > 0 && $kc['kits_possiveis'] > 0 ? min(100, ($kitMontaveis / $kc['kits_possiveis']) * 100) : ($kc['kits_possiveis'] > 0 ? 100 : 0); @endphp<div class="h-full rounded-full bg-gradient-to-r {{ $kc['kits_possiveis'] <= $kitMontaveis ? 'from-amber-400 to-orange-500' : 'from-emerald-400 to-teal-500' }}" style="width: {{ $w }}%"></div></div>
                        @if($kc['kits_possiveis'] <= $kitMontaveis)<p class="text-[9px] text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-triangle-fill"></i> Item que limita a montagem</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ============ KPIs (compactos + sparkline + countup) ============ --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Total vendido" tone="sky" icon="bi-cart-check" :value="number_format($analytics['total_quantity_sold'])" countup :spark="$chartData['sales'] ?? []" spark-color="#0ea5e9" />
        <x-dash.kpi label="Receita total" tone="emerald" icon="bi-currency-dollar" :value="'R$ ' . number_format($analytics['total_revenue'], 0, ',', '.')" :spark="$chartData['revenue'] ?? []" spark-color="#10b981" />
        <x-dash.kpi label="Lucro total" tone="purple" icon="bi-graph-up-arrow" :value="'R$ ' . number_format($analytics['total_profit'], 0, ',', '.')" :delta="round($analytics['profit_margin'] ?? 0)" />
        <x-dash.kpi label="Margem" tone="indigo" icon="bi-percent" :value="number_format($analytics['profit_margin'], 1) . '%'" />
        <x-dash.kpi label="Em estoque" tone="amber" icon="bi-boxes" :value="number_format($analytics['total_stock'])" countup />
        <x-dash.kpi label="Valor estoque" tone="rose" icon="bi-cash-stack" :value="'R$ ' . number_format($analytics['stock_value'], 0, ',', '.')" />
    </div>

    {{-- ============ GRID PRINCIPAL (tudo em uma tela) ============ --}}
    <div class="dash-grid">

        {{-- Vendas por mês --}}
        <x-dash.card title="Vendas por mês" sub="Unidades nos últimos 12 meses" icon="bi-bar-chart-line" tone="sky" span="dash-col-6">
            @if(!empty(array_sum($chartData['sales'] ?? [])))
                <x-dash.chart id="spSalesChart" type="bar" :series="[['name' => 'Unidades', 'data' => $chartData['sales'] ?? []]]" :labels="$chartData['labels'] ?? []" :colors="['#3b82f6']" :height="240" />
            @else
                <x-dash.empty icon="bi-bar-chart" message="Sem vendas no período" />
            @endif
        </x-dash.card>

        {{-- Receita por mês --}}
        <x-dash.card title="Receita por mês" sub="Faturamento mensal" icon="bi-graph-up" tone="emerald" span="dash-col-6">
            @if(!empty(array_sum($chartData['revenue'] ?? [])))
                <x-dash.chart id="spRevenueChart" type="area" :series="[['name' => 'Receita', 'data' => $chartData['revenue'] ?? []]]" :labels="$chartData['labels'] ?? []" :colors="['#10b981']" :height="240" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem receita no período" />
            @endif
        </x-dash.card>

        {{-- Variações (donut) --}}
        <x-dash.card title="Por variação" sub="Receita de cada variação" icon="bi-pie-chart" tone="purple" span="dash-col-4">
            @php
                $varPerf = $analytics['variation_performance'] ?? [];
                $varSeries = []; $varLabels = [];
                foreach ($varPerf as $vp) {
                    $varSeries[] = round((float) ($vp['revenue'] ?? 0), 2);
                    $vpProd = $vp['product'] ?? null;
                    $varLabels[] = ($vpProd && ($vpProd->name ?? null)) ? \Illuminate\Support\Str::limit($vpProd->name, 16) : 'Variação';
                }
            @endphp
            @if(!empty(array_sum($varSeries)))
                <x-dash.chart id="spVariationChart" type="donut" :series="$varSeries" :labels="$varLabels" :colors="['#8b5cf6','#ec4899','#f59e0b','#ef4444','#06b6d4','#84cc16','#f97316']" :height="240" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem dados de variações" />
            @endif
        </x-dash.card>

        {{-- Top clientes --}}
        <x-dash.card title="Top clientes" sub="Quem mais comprou" icon="bi-trophy" tone="amber" span="dash-col-8">
            @if(!empty($analytics['top_clients']) && count($analytics['top_clients']) > 0)
                <div class="dash-list dash-scroll max-h-[260px] overflow-y-auto pr-1">
                    @foreach($analytics['top_clients'] as $i => $client)
                        <x-dash.list-item :title="$client->name ?? 'Cliente'" :sub="'#' . ($i + 1) . ' · ' . (int)($client->total_quantity ?? 0) . ' un · ' . (int)($client->total_orders ?? 0) . ' pedidos'" icon="bi-person-fill" tone="amber" :value="'R$ ' . number_format($client->total_spent ?? 0, 0, ',', '.')" trend="up" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-people" message="Nenhum cliente comprou ainda" />
            @endif
        </x-dash.card>

        {{-- Preços & informações --}}
        <x-dash.card title="Preços & informações" sub="Dados do produto" icon="bi-info-circle" tone="indigo" span="dash-col-4">
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-slate-100/70 dark:bg-slate-800/50 px-3 py-2.5"><p class="text-[10px] font-bold uppercase text-slate-400">Custo</p><p class="text-base font-black text-slate-700 dark:text-slate-200">R$ {{ number_format($mainProduct->price, 2, ',', '.') }}</p></div>
                <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-3 py-2.5"><p class="text-[10px] font-bold uppercase text-emerald-500">Venda</p><p class="text-base font-black text-emerald-700 dark:text-emerald-300">R$ {{ number_format($mainProduct->price_sale, 2, ',', '.') }}</p></div>
                <div class="rounded-xl bg-indigo-500/10 border border-indigo-400/30 px-3 py-2.5"><p class="text-[10px] font-bold uppercase text-indigo-500">Ticket médio</p><p class="text-sm font-black text-indigo-700 dark:text-indigo-300">R$ {{ number_format($analytics['avg_sale_price'] ?? 0, 2, ',', '.') }}</p></div>
                <div class="rounded-xl bg-amber-500/10 border border-amber-400/30 px-3 py-2.5"><p class="text-[10px] font-bold uppercase text-amber-500">Variações</p><p class="text-sm font-black text-amber-700 dark:text-amber-300">{{ $products->count() }}</p></div>
            </div>
            <div class="mt-2 space-y-1.5 text-xs">
                <div class="flex justify-between items-center rounded-lg bg-slate-50 dark:bg-slate-950/40 px-3 py-2"><span class="text-slate-500">1ª venda</span><span class="font-bold text-slate-700 dark:text-slate-200">{{ !empty($analytics['first_sale_date']) ? \Carbon\Carbon::parse($analytics['first_sale_date'])->format('d/m/Y') : '—' }}</span></div>
                <div class="flex justify-between items-center rounded-lg bg-slate-50 dark:bg-slate-950/40 px-3 py-2"><span class="text-slate-500">Última venda</span><span class="font-bold text-slate-700 dark:text-slate-200">{{ !empty($analytics['last_sale_date']) ? \Carbon\Carbon::parse($analytics['last_sale_date'])->format('d/m/Y') : '—' }}</span></div>
            </div>
        </x-dash.card>

        {{-- Vendas recentes --}}
        <x-dash.card title="Vendas recentes" sub="Onde este produto apareceu" icon="bi-receipt" tone="teal" span="dash-col-8">
            @if($salesData && $salesData->count() > 0)
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach($salesData->take(10) as $sale)
                        @php $qtd = $sale->saleItems->sum('quantity'); $st = strtolower((string)($sale->status ?? 'pendente')); @endphp
                        <a href="{{ route('sales.show', $sale->id) }}" class="block">
                            <x-dash.list-item :title="'Venda #' . $sale->id . ' · ' . ($sale->client->name ?? 'Sem cliente')" :sub="$sale->created_at->format('d/m/Y') . ' · ' . $qtd . ' un'" :icon="$st === 'pago' || $st === 'finalizada' ? 'bi-check-circle-fill' : 'bi-clock-fill'" :tone="$st === 'pago' || $st === 'finalizada' ? 'emerald' : 'amber'" :value="'R$ ' . number_format($sale->total_price, 0, ',', '.')" />
                        </a>
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-receipt" message="Este produto ainda não foi vendido" />
            @endif
        </x-dash.card>

        {{-- Variações detalhadas --}}
        @if($products->count() > 1)
        <x-dash.card title="Variações" sub="Estoque e preço de cada uma" icon="bi-layers" tone="rose" span="dash-col-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">
                @foreach($products as $variant)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-900/60 p-3 flex items-center gap-3 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 flex items-center justify-center">
                        <img src="{{ $variant->image ? asset('storage/products/' . $variant->image) : asset('storage/products/product-placeholder.png') }}" alt="" class="w-full h-full object-contain p-1" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $variant->name }}</p>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ ($variant->stock_quantity ?? 0) <= 5 ? 'bg-rose-500/15 text-rose-600 dark:text-rose-300' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' }}"><i class="bi bi-stack text-[9px]"></i>{{ $variant->stock_quantity ?? 0 }}</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/15 text-indigo-600 dark:text-indigo-300">R$ {{ number_format($variant->price_sale ?? 0, 2, ',', '.') }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ ($variant->status ?? '') === 'ativo' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' : 'bg-slate-400/20 text-slate-500' }}">{{ ucfirst($variant->status ?? '—') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </x-dash.card>
        @endif
    </div>{{-- /.dash-grid --}}
</div>{{-- /.space-y-4 (página única) --}}

    <!-- Export Modal Component -->
    @livewire('products.export-product-card')
</div>{{-- /.show-product-page --}}

@push('scripts')

{{-- Gráficos migrados para o sistema dash (x-dash.chart) — montagem e tema via assets/js/dash-charts.js --}}

<!-- Script para funcionalidades da tabela de vendas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidades para a aba de vendas
    initSalesTable();
});

function initSalesTable() {
    // Filtros da tabela
    const periodFilter = document.querySelector('select[placeholder*="período"], select option[value="30"]')?.closest('select');
    const statusFilter = document.querySelector('select[placeholder*="status"], select option[value="all"]')?.closest('select');
    const searchInput = document.querySelector('input[placeholder*="Buscar"]');

    // Event listeners para filtros
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            filterSales();
            console.log('Filtro de período alterado:', this.value);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterSales();
            console.log('Filtro de status alterado:', this.value);
        });
    }

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterSales();
                console.log('Busca realizada:', this.value);
            }, 300);
        });
    }

    // Sorting da tabela
    const sortButtons = document.querySelectorAll('th .bi-arrow-down-up');
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const th = this.closest('th');
            const table = th.closest('table');
            const column = Array.from(th.parentNode.children).indexOf(th);

            // Remove sorting anterior
            sortButtons.forEach(btn => {
                btn.className = 'bi bi-arrow-down-up text-slate-400 hover:text-slate-600 cursor-pointer';
            });

            // Aplica novo sorting
            this.className = 'bi bi-sort-down text-blue-600 cursor-pointer';

            sortTable(table, column);
            console.log('Ordenação aplicada na coluna:', column);
        });
    });

    // Botões de ação
    const actionButtons = document.querySelectorAll('button[title]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('title');
            const row = this.closest('tr');
            const saleId = row.querySelector('.font-medium')?.textContent;

            handleSaleAction(action, saleId, row);
        });
    });

    // Paginação
    const paginationButtons = document.querySelectorAll('nav button');
    paginationButtons.forEach(button => {
        if (!button.disabled && !button.classList.contains('bg-blue-600')) {
            button.addEventListener('click', function() {
                const page = this.textContent.trim();
                if (page && !isNaN(page)) {
                    loadPage(parseInt(page));
                } else if (this.querySelector('.bi-chevron-left')) {
                    loadPage('prev');
                } else if (this.querySelector('.bi-chevron-right')) {
                    loadPage('next');
                }
            });
        }
    });
}

function filterSales() {
    const periodValue = document.querySelector('select option[value="30"]')?.closest('select')?.value || 'all';
    const statusValue = document.querySelector('select option[value="all"]')?.closest('select')?.value || 'all';
    const searchValue = document.querySelector('input[placeholder*="Buscar"]')?.value || '';

    const tbody = document.querySelector('table tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        let showRow = true;

        // Filtro por status
        if (statusValue !== 'all') {
            const statusBadge = row.querySelector('.bg-green-100, .bg-yellow-100, .bg-red-100');
            const statusText = statusBadge?.textContent.toLowerCase().trim();

            if (statusValue === 'completed' && !statusText?.includes('concluída')) {
                showRow = false;
            } else if (statusValue === 'pending' && !statusText?.includes('pendente')) {
                showRow = false;
            } else if (statusValue === 'cancelled' && !statusText?.includes('cancelada')) {
                showRow = false;
            }
        }

        // Filtro por busca
        if (searchValue && showRow) {
            const rowText = row.textContent.toLowerCase();
            if (!rowText.includes(searchValue.toLowerCase())) {
                showRow = false;
            }
        }

        // Mostrar/ocultar linha
        row.style.display = showRow ? '' : 'none';
    });

    // Atualizar contador
    updateRowCounter();
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aVal = a.children[column]?.textContent.trim() || '';
        const bVal = b.children[column]?.textContent.trim() || '';

        // Tratamento especial para valores monetários
        if (aVal.includes('R$') && bVal.includes('R$')) {
            const aNum = parseFloat(aVal.replace(/[R$.,\s]/g, '').replace(',', '.'));
            const bNum = parseFloat(bVal.replace(/[R$.,\s]/g, '').replace(',', '.'));
            return bNum - aNum; // Ordem decrescente para valores
        }

        // Tratamento para datas
        if (aVal.includes('/') && bVal.includes('/')) {
            const aDate = new Date(aVal.split('/').reverse().join('-'));
            const bDate = new Date(bVal.split('/').reverse().join('-'));
            return bDate - aDate; // Ordem decrescente para datas
        }

        // Ordenação alfabética padrão
        return aVal.localeCompare(bVal);
    });

    // Reordenar linhas
    rows.forEach(row => tbody.appendChild(row));
}

function handleSaleAction(action, saleId, row) {
    console.log(`Ação ${action} executada para venda ${saleId}`);

    switch(action) {
        case 'Ver detalhes':
            showSaleDetails(saleId);
            break;
        case 'Imprimir':
            printSale(saleId);
            break;
        case 'Duplicar':
            duplicateSale(saleId);
            break;
        case 'Editar':
            editSale(saleId);
            break;
        default:
            console.log('Ação não reconhecida:', action);
    }
}

function showSaleDetails(saleId) {
    // Aqui você implementaria a modal ou navegação para detalhes
    console.log('Mostrando detalhes da venda:', saleId);

    // Exemplo de implementação básica
    alert(`Detalhes da venda ${saleId}\n\nEsta funcionalidade abrirá uma modal com todos os detalhes da venda.`);
}

function printSale(saleId) {
    console.log('Imprimindo venda:', saleId);
    window.fmConfirm({ title: 'Imprimir venda', message: `Deseja imprimir a venda <strong>${saleId}</strong>?`, variant: 'info', confirmText: 'Imprimir' })
        .then(function(ok) { if (ok) window.print(); });
}

function duplicateSale(saleId) {
    console.log('Duplicando venda:', saleId);
    window.fmConfirm({ title: 'Duplicar venda', message: `Deseja criar uma nova venda baseada em <strong>${saleId}</strong>?`, variant: 'info', confirmText: 'Duplicar' })
        .then(function(ok) { if (ok) window.notify && window.notify('success', 'Venda duplicada com sucesso!'); });
}

function editSale(saleId) {
    console.log('Editando venda:', saleId);

    // Exemplo de implementação - redirecionamento
    // window.location.href = `/sales/edit/${saleId}`;
    alert(`Redirecionando para edição da venda ${saleId}`);
}

function loadPage(page) {
    console.log('Carregando página:', page);

    // Aqui você implementaria a paginação AJAX
    // Por enquanto, apenas simula o carregamento
    const currentPageBtn = document.querySelector('nav .bg-blue-600');
    if (currentPageBtn) {
        currentPageBtn.classList.remove('bg-blue-600', 'text-white');
        currentPageBtn.classList.add('text-slate-700', 'hover:text-slate-900', 'dark:text-slate-300', 'dark:hover:text-slate-100', 'hover:bg-slate-100', 'dark:hover:bg-slate-700');
    }

    // Simula carregamento
    if (typeof page === 'number') {
        const newPageBtn = document.querySelector(`nav button:nth-child(${page + 1})`);
        if (newPageBtn) {
            newPageBtn.classList.add('bg-blue-600', 'text-white');
            newPageBtn.classList.remove('text-slate-700', 'hover:text-slate-900', 'dark:text-slate-300', 'dark:hover:text-slate-100', 'hover:bg-slate-100', 'dark:hover:bg-slate-700');
        }
    }
}

function updateRowCounter() {
    const tbody = document.querySelector('table tbody');
    const visibleRows = tbody.querySelectorAll('tr:not([style*="display: none"])');
    const totalRows = tbody.querySelectorAll('tr').length;

    const counter = document.querySelector('.text-slate-600.dark\\:text-slate-400');
    if (counter) {
        counter.innerHTML = `Mostrando <span class="font-medium text-slate-900 dark:text-slate-100">${visibleRows.length}</span> de <span class="font-medium text-slate-900 dark:text-slate-100">${totalRows}</span> vendas`;
    }
}

// Função para exportar dados (pode ser chamada por um botão)
function exportSalesData() {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');

    let csvContent = "data:text/csv;charset=utf-8,";

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell =>
            cell.textContent.replace(/,/g, ';').trim()
        ).join(',');
        csvContent += rowData + "\r\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `vendas_produto_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log('Dados exportados com sucesso');
}
</script>

<style>
/* Prevenção de overflow horizontal */
html, body {
    overflow-x: hidden;
    max-width: 100%;
}

* {
    box-sizing: border-box;
}

/* Container principal */
.main-container {
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
}

/* Scrollbar customizado */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(59, 130, 246, 0.5) rgba(241, 245, 249, 0.1);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

/* Hide scrollbar for tabs */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Garantir que grids sejam responsivos */
.responsive-grid {
    display: grid;
    gap: 1.5rem;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .responsive-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .responsive-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .responsive-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Cards responsivos */
.responsive-card {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

/* Charts container */
.chart-container {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.chart-container #salesChart,
.chart-container #revenueChart,
.chart-container #variationChart {
    width: 100% !important;
    max-width: 100% !important;
}

/* Classes utilitárias */
.border-3 {
    border-width: 3px;
}

.hover\:scale-102:hover {
    transform: scale(1.02);
}

.shadow-3xl {
    box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

/* Animações de blob para o background */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Efeito de loading suave */
@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

.pulse-glow {
    animation: pulse-glow 2s infinite;
}

/* Efeito hover melhorado para cards */
.card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Gradiente animado */
@keyframes gradient-shift {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}

/* Efeito shimmer para loading */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

/* Responsividade melhorada */
@media (max-width: 768px) {
    .mobile-stack {
        flex-direction: column;
    }

    .mobile-full {
        width: 100%;
    }

    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

@media (max-width: 640px) {
    .px-6 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}

/* Efeitos de hover para botões */
.btn-hover {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.btn-hover:hover::before {
    left: 100%;
}

/* Melhoria na tipografia */
.text-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .dark-blur {
        backdrop-filter: blur(20px) saturate(180%);
        background-color: rgba(17, 25, 40, 0.75);
    }
}

/* Fixes para ApexCharts */
.apexcharts-svg {
    max-width: 100% !important;
    width: 100% !important;
}

.apexcharts-canvas {
    max-width: 100% !important;
    width: 100% !important;
}

/* Loading state melhorado */
.chart-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 320px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos específicos para a tabela de vendas */
.sales-table {
    border-collapse: separate;
    border-spacing: 0;
}

.sales-table th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, rgb(248 250 252) 0%, rgb(241 245 249) 100%);
}

.dark .sales-table th {
    background: linear-gradient(135deg, rgb(51 65 85) 0%, rgb(71 85 105) 100%);
}

.sales-table tbody tr {
    transition: all 0.2s ease;
}

.sales-table tbody tr:hover {
    background-color: rgb(248 250 252);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.dark .sales-table tbody tr:hover {
    background-color: rgb(51 65 85 / 0.5);
}

/* Animação para badges de status */
.status-badge {
    animation: fadeInUp 0.3s ease;
    transition: all 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para botões de ação */
.action-button {
    transition: all 0.2s ease;
    transform: scale(1);
}

.action-button:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-button:active {
    transform: scale(0.95);
}

/* Estilos para filtros */
.filter-input, .filter-select {
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.filter-input:focus, .filter-select:focus {
    border-color: rgb(59 130 246);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

/* Animação para carregamento de dados */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

.dark .loading-overlay {
    background: rgba(15, 23, 42, 0.9);
}

.loading-overlay.show {
    display: flex;
}

/* Estilos para paginação */
.pagination-button {
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.pagination-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.pagination-button:hover::before {
    left: 100%;
}

/* Highlight para linhas selecionadas */
.row-selected {
    background-color: rgb(219 234 254) !important;
    border-left: 4px solid rgb(59 130 246);
}

.dark .row-selected {
    background-color: rgb(30 58 138 / 0.3) !important;
}

/* Estilos para campos monetários */
.currency-field {
    font-family: 'Courier New', monospace;
    font-weight: 600;
}

/* Indicador de ordenação */
.sort-indicator {
    transition: all 0.2s ease;
}

.sort-indicator.active {
    color: rgb(59 130 246);
    transform: scale(1.2);
}

/* Estilos para badges de quantidade */
.quantity-badge {
    background: linear-gradient(135deg, rgb(219 234 254) 0%, rgb(191 219 254) 100%);
    border: 1px solid rgb(147 197 253);
}

.dark .quantity-badge {
    background: linear-gradient(135deg, rgb(30 58 138) 0%, rgb(29 78 216) 100%);
    border: 1px solid rgb(59 130 246);
}

/* Animação para estatísticas */
.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stats-card .icon {
    transition: all 0.3s ease;
}

.stats-card:hover .icon {
    transform: scale(1.1) rotate(5deg);
}

/* Responsividade melhorada para tabela */
@media (max-width: 1024px) {
    .sales-table {
        font-size: 0.875rem;
    }

    .sales-table th,
    .sales-table td {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 768px) {
    .sales-table {
        font-size: 0.8125rem;
    }

    .sales-table th,
    .sales-table td {
        padding: 0.5rem 0.375rem;
    }

    .action-button {
        padding: 0.375rem;
    }
}

/* Tooltip personalizado */
.tooltip {
    position: relative;
}

.tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgb(15 23 42);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 30;
}

.tooltip:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 5px);
}

/* Estilos para modo escuro */
@media (prefers-color-scheme: dark) {
    .tooltip::after {
        background: rgb(241 245 249);
        color: rgb(15 23 42);
    }
}
</style>

<!-- CSS do produtos.css -->
<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

@endpush
