<div class="dashboard-products-page mobile-393-base app-viewport-fit min-h-screen w-full relative overflow-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-products-ultrawide.css') }}">

    @php
        $topSold = collect($produtosMaisVendidos ?? [])->take(6);
        $topRevenue = collect($produtosMaiorReceita ?? [])->take(6);
        $topProfit = collect($produtoMaiorLucro ?? [])->take(6);
        $lowStock = collect($produtosEstoqueBaixoAltaDemanda ?? [])->take(6);
        $idleProducts = collect($produtosParados ?? [])->take(6);
        $recentProducts = collect($ultimosProdutos ?? [])->take(6);
        $categoryMix = collect($dadosGraficoPizza ?? [])->take(8);
        $statusMix = collect($statusProdutos ?? [])->filter(fn ($item) => (int) data_get($item, 'value', 0) > 0)->values();
        $monthlyTrend = collect($vendasMensaisProdutos ?? [])->values();
        $comparisonSummary = collect($periodSummary ?? []);
        $topKitsCollection = collect($topKits ?? [])->take(6);
        $coverageProducts = collect($coberturaProdutos ?? [])->take(6);
        $coverageCategories = collect($coberturaCategorias ?? [])->take(6);
        $marketplaceCards = collect(data_get($marketplacePeriodMetrics ?? [], 'cards', []))->take(6);
        $marketplaceTrend = collect(data_get($marketplacePeriodMetrics ?? [], 'trend', []))->values();
        $selectedChannelMetrics = data_get($channelMetrics ?? [], $selectedChannel, data_get($channelMetrics ?? [], 'all', []));
        $selectedChannelCards = collect(data_get($selectedChannelMetrics ?? [], 'cards', []));
        $selectedChannelTopProducts = collect(data_get($selectedChannelMetrics ?? [], 'topProducts', []))->take(6);
        $selectedChannelLabel = data_get($selectedChannelMetrics ?? [], 'label', $channelOptions[$selectedChannel] ?? 'Todos');
        $heroStats = [
            [
                'label' => 'Receita do período',
                'value' => 'R$ ' . number_format($faturamentoPeriodo ?? 0, 2, ',', '.'),
                'icon' => 'fas fa-sack-dollar',
                'surface' => 'border-emerald-200/80 bg-emerald-50/80 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-200',
            ],
            [
                'label' => 'Unidades vendidas',
                'value' => number_format($unidadesVendidasPeriodo ?? 0, 0, ',', '.'),
                'icon' => 'fas fa-box-open',
                'surface' => 'border-sky-200/80 bg-sky-50/80 dark:border-sky-500/20 dark:bg-sky-500/10',
                'text' => 'text-sky-700 dark:text-sky-200',
            ],
            [
                'label' => 'Pedidos no período',
                'value' => number_format($pedidosPeriodo ?? 0, 0, ',', '.'),
                'icon' => 'fas fa-basket-shopping',
                'surface' => 'border-orange-200/80 bg-orange-50/80 dark:border-orange-500/20 dark:bg-orange-500/10',
                'text' => 'text-orange-700 dark:text-orange-200',
            ],
            [
                'label' => 'Ticket médio',
                'value' => 'R$ ' . number_format($ticketMedioPeriodo ?? 0, 2, ',', '.'),
                'icon' => 'fas fa-receipt',
                'surface' => 'border-violet-200/80 bg-violet-50/80 dark:border-violet-500/20 dark:bg-violet-500/10',
                'text' => 'text-violet-700 dark:text-violet-200',
            ],
        ];
        $sideMetrics = [
            [
                'label' => 'Categorias ativas',
                'value' => number_format($categoriasAtivasPeriodo ?? 0, 0, ',', '.'),
                'icon' => 'fas fa-layer-group',
                'surface' => 'border-cyan-200/80 bg-cyan-50/80 dark:border-cyan-500/20 dark:bg-cyan-500/10',
                'text' => 'text-cyan-700 dark:text-cyan-200',
            ],
            [
                'label' => 'Lucro estimado',
                'value' => 'R$ ' . number_format($lucroEstimadoPeriodo ?? 0, 2, ',', '.'),
                'icon' => 'fas fa-chart-line',
                'surface' => 'border-emerald-200/80 bg-emerald-50/80 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-200',
            ],
            [
                'label' => 'Margem do estoque',
                'value' => number_format($margemMediaEstoque ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-percent',
                'surface' => 'border-rose-200/80 bg-rose-50/80 dark:border-rose-500/20 dark:bg-rose-500/10',
                'text' => 'text-rose-700 dark:text-rose-200',
            ],
            [
                'label' => 'Top produtos no faturamento',
                'value' => number_format($participacaoTopProdutos ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-ranking-star',
                'surface' => 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10',
                'text' => 'text-amber-700 dark:text-amber-200',
            ],
        ];
        $insightCards = [
            [
                'label' => 'Estoque crítico',
                'value' => number_format($produtosEstoqueCritico ?? 0, 0, ',', '.'),
                'description' => 'produtos com menos de 10 unidades',
                'icon' => 'fas fa-triangle-exclamation',
                'surface' => 'border-rose-200/80 bg-rose-50/80 dark:border-rose-500/20 dark:bg-rose-500/10',
                'text' => 'text-rose-700 dark:text-rose-200',
            ],
            [
                'label' => 'Sem giro em 60 dias',
                'value' => number_format($produtosSemGiro ?? 0, 0, ',', '.'),
                'description' => 'itens sem venda recente',
                'icon' => 'fas fa-bed',
                'surface' => 'border-slate-200/80 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-900/60',
                'text' => 'text-slate-700 dark:text-slate-200',
            ],
            [
                'label' => 'Produtos ativos no ML',
                'value' => number_format($produtosComMlAtivo ?? 0, 0, ',', '.'),
                'description' => 'expostos em publicações ativas',
                'icon' => 'fas fa-store',
                'surface' => 'border-yellow-200/80 bg-yellow-50/80 dark:border-yellow-500/20 dark:bg-yellow-500/10',
                'text' => 'text-yellow-700 dark:text-yellow-200',
            ],
            [
                'label' => 'Produtos ativos na Shopee',
                'value' => number_format($produtosComShopeeAtivo ?? 0, 0, ',', '.'),
                'description' => 'presença ativa no canal',
                'icon' => 'fas fa-bag-shopping',
                'surface' => 'border-orange-200/80 bg-orange-50/80 dark:border-orange-500/20 dark:bg-orange-500/10',
                'text' => 'text-orange-700 dark:text-orange-200',
            ],
        ];
        $kitStats = [
            [
                'label' => 'Kits vendidos',
                'value' => number_format($kitsVendidosPeriodo ?? 0, 0, ',', '.'),
                'description' => 'volume vendido no recorte',
                'icon' => 'fas fa-boxes-stacked',
                'surface' => 'border-indigo-200/80 bg-indigo-50/80 dark:border-indigo-500/20 dark:bg-indigo-500/10',
                'text' => 'text-indigo-700 dark:text-indigo-200',
            ],
            [
                'label' => 'Receita de kits',
                'value' => 'R$ ' . number_format($receitaKitsPeriodo ?? 0, 2, ',', '.'),
                'description' => 'faturamento vindo de kits',
                'icon' => 'fas fa-badge-dollar',
                'surface' => 'border-fuchsia-200/80 bg-fuchsia-50/80 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10',
                'text' => 'text-fuchsia-700 dark:text-fuchsia-200',
            ],
            [
                'label' => 'Componentes consumidos',
                'value' => number_format($componentesConsumidosViaKits ?? 0, 0, ',', '.'),
                'description' => 'baixa operacional via kits',
                'icon' => 'fas fa-gears',
                'surface' => 'border-cyan-200/80 bg-cyan-50/80 dark:border-cyan-500/20 dark:bg-cyan-500/10',
                'text' => 'text-cyan-700 dark:text-cyan-200',
            ],
            [
                'label' => 'Produtos ligados a kits',
                'value' => number_format($produtosLigadosKits ?? 0, 0, ',', '.'),
                'description' => 'componentes vinculados hoje',
                'icon' => 'fas fa-link',
                'surface' => 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10',
                'text' => 'text-amber-700 dark:text-amber-200',
            ],
        ];
        $chartPayload = [
            'categoryMix' => $categoryMix->values()->all(),
            'topRevenue' => $topRevenue->values()->all(),
            'monthlyTrend' => $monthlyTrend->values()->all(),
            'stockSales' => $topSold->values()->all(),
            'statusMix' => $statusMix->values()->all(),
            'profitMargin' => $topProfit->values()->all(),
            'periodComparison' => $periodComparison ?? [],
            'marketplaceTrend' => $marketplaceTrend->values()->all(),
        ];
    @endphp

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.18),_transparent_38%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.12),_transparent_34%),linear-gradient(180deg,_rgba(15,23,42,0.04),_transparent)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.24),_transparent_38%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.18),_transparent_34%),linear-gradient(180deg,_rgba(15,23,42,0.62),_transparent)]"></div>
        <div class="absolute left-10 top-16 h-52 w-52 rounded-full bg-amber-400/10 blur-3xl"></div>
        <div class="absolute right-12 top-10 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
    </div>

    <div class="dashboard-products-shell px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <section class="space-y-4">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <div class="xl:col-span-8 overflow-hidden rounded-[28px] border border-white/60 bg-white/85 shadow-[0_20px_80px_rgba(15,23,42,0.10)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="relative overflow-hidden px-5 py-6 sm:px-6 sm:py-6">
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(245,158,11,0.12),rgba(234,88,12,0.08),rgba(14,165,233,0.08))] dark:bg-[linear-gradient(135deg,rgba(245,158,11,0.18),rgba(234,88,12,0.14),rgba(14,165,233,0.12))]"></div>
                        <div class="relative flex flex-col gap-5">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                <div class="max-w-3xl space-y-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-orange-200/80 bg-orange-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-200">Parte 1</span>
                                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/75 px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300"><i class="fas fa-layer-group text-cyan-500"></i>Operação, estoque e margem</span>
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"><i class="fas fa-circle text-[8px]"></i>{{ $periodLabel }}</span>
                                    </div>

                                    <div>
                                        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-300">
                                            <a href="{{ route('dashboard') }}" class="transition hover:text-orange-600 dark:hover:text-orange-300"><i class="fas fa-chart-line mr-1"></i>Dashboard</a>
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200"><i class="fas fa-box-open mr-1"></i>Produtos</span>
                                        </div>
                                        <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Dashboard de produtos com análise por período</h1>
                                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">Agora o painel acompanha o recorte selecionado com comparativo de desempenho, leitura de portfólio, sinais de ruptura e evolução mensal sem perder a visão operacional do estoque atual.</p>
                                    </div>
                                </div>

                                <div class="grid w-full gap-3 lg:w-auto lg:min-w-[360px]">
                                    <div class="grid gap-3 sm:grid-cols-4">
                                        <div>
                                            <label class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Mês</label>
                                            <select wire:model.live="selectedMonth" class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-orange-400 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                                                @foreach ($monthOptions as $monthNumber => $monthName)
                                                    <option value="{{ $monthNumber }}">{{ $monthName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Ano</label>
                                            <select wire:model.live="selectedYear" class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-orange-400 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                                                @foreach ($availableYears as $year)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Canal</label>
                                            <select wire:model.live="selectedChannel" class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-orange-400 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                                                @foreach ($channelOptions as $channelValue => $channelLabel)
                                                    <option value="{{ $channelValue }}">{{ $channelLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-300">
                                            <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"><i class="fas fa-calendar-range text-orange-500"></i>Recorte</div>
                                            <div class="mt-2 text-sm font-bold text-slate-900 dark:text-white">{{ $periodLabel }}</div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach (['today' => 'Hoje', 'month' => 'Mês', 'quarter' => 'Trimestre', 'year' => 'Ano'] as $preset => $label)
                                            <button type="button" wire:click="applyPeriodPreset('{{ $preset }}')" class="rounded-full border px-3 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $periodPreset === $preset ? 'border-orange-500 bg-orange-500 text-white shadow-sm' : 'border-slate-200/80 bg-white/85 text-slate-600 hover:border-orange-300 hover:text-orange-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-orange-500/40 dark:hover:text-orange-300' }}">{{ $label }}</button>
                                        @endforeach
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <a href="{{ route('dashboard.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:text-orange-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-orange-500/40 dark:hover:text-orange-300"><span class="flex items-center gap-3"><i class="fas fa-house text-orange-500"></i>Dashboard geral</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                        <a href="{{ route('products.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:text-cyan-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-cyan-500/40 dark:hover:text-cyan-300"><span class="flex items-center gap-3"><i class="fas fa-sliders text-cyan-500"></i>Gerenciar produtos</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                        <a href="{{ route('reports.dashboard-products.export', ['format' => 'pdf', 'month' => $selectedMonth, 'year' => $selectedYear, 'preset' => $periodPreset, 'channel' => $selectedChannel]) }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-rose-500/40 dark:hover:text-rose-300"><span class="flex items-center gap-3"><i class="fas fa-file-pdf text-rose-500"></i>Exportar PDF</span><i class="fas fa-download text-xs opacity-60 transition group-hover:translate-y-0.5"></i></a>
                                        <a href="{{ route('reports.dashboard-products.export', ['format' => 'xlsx', 'month' => $selectedMonth, 'year' => $selectedYear, 'preset' => $periodPreset, 'channel' => $selectedChannel]) }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:text-emerald-300"><span class="flex items-center gap-3"><i class="fas fa-file-excel text-emerald-500"></i>Exportar Excel</span><i class="fas fa-download text-xs opacity-60 transition group-hover:translate-y-0.5"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                @foreach ($heroStats as $stat)
                                    <div class="rounded-2xl border p-4 {{ $stat['surface'] }}">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/75 text-lg shadow-sm dark:bg-slate-900/50 {{ $stat['text'] }}">
                                                <i class="{{ $stat['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <p class="text-[11px] font-black uppercase tracking-[0.16em] {{ $stat['text'] }}">{{ $stat['label'] }}</p>
                                                <p class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ $stat['value'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4">
                    @foreach ($sideMetrics as $metric)
                        <div class="rounded-[24px] border p-4 shadow-[0_12px_40px_rgba(15,23,42,0.08)] backdrop-blur {{ $metric['surface'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] {{ $metric['text'] }}">{{ $metric['label'] }}</p>
                                    <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/75 text-lg shadow-sm dark:bg-slate-900/55 {{ $metric['text'] }}">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orange-500">Parte 2</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Comparativo e leitura visual</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Comparação do recorte atual contra período anterior e ano anterior.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-12 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange-500">Canal ativo</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $selectedChannelLabel }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Resumo analítico do canal selecionado sem perder a visão consolidada do dashboard.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($channelOptions as $channelValue => $channelLabel)
                                <button type="button" wire:click="$set('selectedChannel', '{{ $channelValue }}')" class="rounded-full border px-3 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $selectedChannel === $channelValue ? 'border-cyan-500 bg-cyan-500 text-white shadow-sm' : 'border-slate-200/80 bg-white/85 text-slate-600 hover:border-cyan-300 hover:text-cyan-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-cyan-500/40 dark:hover:text-cyan-300' }}">{{ $channelLabel }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                        <div class="xl:col-span-5 grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach ($selectedChannelCards as $card)
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/85 px-4 py-4 dark:border-slate-800 dark:bg-slate-900/55">
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">{{ data_get($card, 'label') }}</p>
                                    <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">
                                        {{ data_get($card, 'type') === 'currency' ? 'R$ ' . number_format((float) data_get($card, 'value', 0), 2, ',', '.') : number_format((float) data_get($card, 'value', 0), 0, ',', '.') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="xl:col-span-7 rounded-2xl border border-slate-200/80 bg-slate-50/85 p-4 dark:border-slate-800 dark:bg-slate-900/55">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h4 class="text-sm font-black uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">Produtos em evidência no canal</h4>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $selectedChannelTopProducts->count() }} itens</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                                @forelse ($selectedChannelTopProducts as $product)
                                    <div class="rounded-2xl border border-white/80 bg-white/90 px-4 py-3 dark:border-slate-700 dark:bg-slate-950/70">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ data_get($product, 'product_code') }}</p>
                                        <div class="mt-3 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ number_format((float) data_get($product, 'units', 0), 0, ',', '.') }} un.</span>
                                            <span>R$ {{ number_format((float) data_get($product, 'revenue', 0), 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-400">Sem dados suficientes para este canal no período.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange-500">Resumo do período</p>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Indicadores de evolução</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach ($comparisonSummary as $item)
                            @php $delta = (float) data_get($item, 'delta', 0); @endphp
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ data_get($item, 'label') }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $delta >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200' }}">{{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 1, ',', '.') }}%</span>
                                </div>
                                <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">
                                    {{ str_contains(strtolower(data_get($item, 'label', '')), 'unidades') ? number_format((float) data_get($item, 'value', 0), 0, ',', '.') : 'R$ ' . number_format((float) data_get($item, 'value', 0), 2, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="xl:col-span-8 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-500">Comparação</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Receita, unidades e lucro</h3>
                        </div>
                        <span class="rounded-full border border-cyan-200/80 bg-cyan-50/80 px-3 py-1 text-xs font-semibold text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-200">Atual x anterior x ano passado</span>
                    </div>
                    <div id="productsComparisonChart" class="products-chart-lg" wire:ignore></div>
                </article>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange-500">Categorias</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Distribuição vendida</h3>
                        </div>
                        <span class="rounded-full border border-orange-200/80 bg-orange-50/80 px-3 py-1 text-xs font-semibold text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-200">{{ $categoryMix->count() }} categorias</span>
                    </div>
                    <div id="productsCategoryChart" class="products-chart-md" wire:ignore></div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-500">Receita</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Produtos que mais faturam</h3>
                        </div>
                        <span class="rounded-full border border-cyan-200/80 bg-cyan-50/80 px-3 py-1 text-xs font-semibold text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-200">Top {{ $topRevenue->count() }}</span>
                    </div>
                    <div id="productsRevenueChart" class="products-chart-md" wire:ignore></div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-violet-500">Tendência</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Ritmo dos últimos 6 meses</h3>
                        </div>
                        <span class="rounded-full border border-violet-200/80 bg-violet-50/80 px-3 py-1 text-xs font-semibold text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-200">{{ $monthlyTrend->count() }} períodos</span>
                    </div>
                    <div id="productsMonthlyChart" class="products-chart-md" wire:ignore></div>
                </article>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orange-500">Parte 3</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Saúde operacional</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Alertas rápidos, status do catálogo e cobertura de estoque.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-7 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-500">Cobertura</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Estoque x demanda dos campeões</h3>
                        </div>
                        <span class="rounded-full border border-amber-200/80 bg-amber-50/80 px-3 py-1 text-xs font-semibold text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">{{ $topSold->count() }} itens</span>
                    </div>
                    <div id="productsStockSalesChart" class="products-chart-lg" wire:ignore></div>
                </article>

                <article class="xl:col-span-5 grid grid-cols-1 gap-4">
                    <div class="rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-500">Situação</p>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Status do catálogo</h3>
                            </div>
                            <span class="rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">{{ $statusMix->count() }} faixas</span>
                        </div>
                        <div id="productsStatusChart" class="products-chart-sm" wire:ignore></div>
                    </div>

                    <div class="rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-500">Margem</p>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Itens com melhor retorno</h3>
                            </div>
                            <span class="rounded-full border border-rose-200/80 bg-rose-50/80 px-3 py-1 text-xs font-semibold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">Top {{ $topProfit->count() }}</span>
                        </div>
                        <div id="productsMarginChart" class="products-chart-sm" wire:ignore></div>
                    </div>
                </article>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 xl:gap-5">
                @foreach ($insightCards as $card)
                    <article class="rounded-[24px] border p-4 shadow-[0_12px_40px_rgba(15,23,42,0.08)] backdrop-blur {{ $card['surface'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] {{ $card['text'] }}">{{ $card['label'] }}</p>
                                <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ $card['value'] }}</p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $card['description'] }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/75 text-lg shadow-sm dark:bg-slate-900/55 {{ $card['text'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 xl:gap-5">
                @foreach ($kitStats as $card)
                    <article class="rounded-[24px] border p-4 shadow-[0_12px_40px_rgba(15,23,42,0.08)] backdrop-blur {{ $card['surface'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] {{ $card['text'] }}">{{ $card['label'] }}</p>
                                <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ $card['value'] }}</p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $card['description'] }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/75 text-lg shadow-sm dark:bg-slate-900/55 {{ $card['text'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-7 rounded-[24px] border border-white/60 bg-white/85 p-4 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-fuchsia-500">Publicações</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">ML e Shopee por período</h3>
                        </div>
                        <span class="rounded-full border border-fuchsia-200/80 bg-fuchsia-50/80 px-3 py-1 text-xs font-semibold text-fuchsia-700 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10 dark:text-fuchsia-200">Últimos 6 meses</span>
                    </div>
                    <div id="productsMarketplaceTrendChart" class="products-chart-md" wire:ignore></div>
                </article>

                <article class="xl:col-span-5 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Indicadores</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Atividade de publicações</h3>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach ($marketplaceCards as $item)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ data_get($item, 'label') }}</p>
                                <span class="text-sm font-black text-slate-700 dark:text-slate-200">{{ number_format(data_get($item, 'value', 0), 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orange-500">Parte 4</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Destaques do período</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Produtos com melhor saída, maior margem e maior estoque atual.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-6 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-500">Destaque de venda</p>
                            <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ data_get($produtoMaisVendido, 'name', 'Sem vendas suficientes') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Maior volume vendido dentro do recorte selecionado.</p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald-100 text-xl text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Unidades</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format(data_get($produtoMaisVendido, 'total_vendido', 0), 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-cyan-200/80 bg-cyan-50/80 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300">Receita</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format(data_get($produtoMaisVendido, 'receita_total', 0), 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-violet-200/80 bg-violet-50/80 p-4 dark:border-violet-500/20 dark:bg-violet-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-violet-700 dark:text-violet-300">Preço atual</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format(data_get($produtoMaisVendido, 'price_sale', 0), 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">Estoque</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format(data_get($produtoMaisVendido, 'stock_quantity', 0), 0, ',', '.') }}</p>
                        </div>
                    </div>
                </article>

                <article class="xl:col-span-6 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Maior cobertura</p>
                            <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ data_get($produtoMaiorEstoque, 'name', 'Sem estoque registrado') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Item com maior volume em mãos hoje, útil para detectar capital concentrado.</p>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-sky-100 text-xl text-sky-700 dark:bg-sky-500/10 dark:text-sky-200">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-2xl border border-sky-200/80 bg-sky-50/80 p-4 dark:border-sky-500/20 dark:bg-sky-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-sky-700 dark:text-sky-300">Unidades</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format(data_get($produtoMaiorEstoque, 'stock_quantity', 0), 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-rose-200/80 bg-rose-50/80 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Custo unitário</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format(data_get($produtoMaiorEstoque, 'price', 0), 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Venda unitária</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format(data_get($produtoMaiorEstoque, 'price_sale', 0), 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-violet-200/80 bg-violet-50/80 p-4 dark:border-violet-500/20 dark:bg-violet-500/10">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-violet-700 dark:text-violet-300">Status</p>
                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ ucfirst(data_get($produtoMaiorEstoque, 'status', 'n/d')) }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orange-500">Parte 5</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Rankings e ações recomendadas</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">As listas mais úteis para priorizar reposição e revisar catálogo.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange-500">Top vendidos</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Maior volume</h3>
                        </div>
                        <span class="rounded-full border border-orange-200/80 bg-orange-50/80 px-3 py-1 text-xs font-semibold text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-200">{{ $topSold->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($topSold as $product)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format(data_get($product, 'stock_quantity', 0), 0, ',', '.') }} em estoque</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-orange-600 dark:text-orange-300">{{ number_format(data_get($product, 'total_vendido', 0), 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">unidades</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem dados de venda suficientes.</p>
                        @endforelse
                    </div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-500">Top margem</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Melhor retorno</h3>
                        </div>
                        <span class="rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">{{ $topProfit->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($topProfit as $product)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                    <p class="text-sm font-black text-emerald-600 dark:text-emerald-300">{{ number_format(data_get($product, 'margem_percentual', 0), 1, ',', '.') }}%</p>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>Lucro acumulado</span>
                                    <span>R$ {{ number_format(data_get($product, 'lucro_total', 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem dados de margem para exibir.</p>
                        @endforelse
                    </div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-500">Recentes</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Cadastros do período</h3>
                        </div>
                        <span class="rounded-full border border-cyan-200/80 bg-cyan-50/80 px-3 py-1 text-xs font-semibold text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-200">{{ $recentProducts->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($recentProducts as $product)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ data_get($product, 'category.name', 'Sem categoria') }}</p>
                                    </div>
                                    <span class="rounded-full border border-slate-200/80 bg-white/80 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-300">{{ number_format(data_get($product, 'stock_quantity', 0), 0, ',', '.') }}</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ data_get($product, 'created_at') ? \Illuminate\Support\Carbon::parse(data_get($product, 'created_at'))->format('d/m/Y') : 'Sem data' }}</span>
                                    <span>R$ {{ number_format(data_get($product, 'price_sale', 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum cadastro recente encontrado.</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-7 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-500">Reposição</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Estoque baixo com alta demanda</h3>
                        </div>
                        <span class="rounded-full border border-rose-200/80 bg-rose-50/80 px-3 py-1 text-xs font-semibold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">{{ $lowStock->count() }} alertas</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($lowStock as $product)
                            <div class="grid grid-cols-1 gap-3 rounded-2xl border border-rose-200/80 bg-rose-50/75 px-4 py-3 dark:border-rose-500/20 dark:bg-rose-500/10 md:grid-cols-[minmax(0,1fr)_auto_auto_auto] md:items-center">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Código {{ data_get($product, 'product_code', 'n/d') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Estoque</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ number_format(data_get($product, 'stock_quantity', 0), 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Vendidos</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ number_format(data_get($product, 'total_vendido', 0), 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Receita</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">R$ {{ number_format(data_get($product, 'receita_total', 0), 2, ',', '.') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-6 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">Nenhum produto com alta demanda em risco imediato.</div>
                        @endforelse
                    </div>
                </article>

                <article class="xl:col-span-5 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Sem giro</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Produtos parados</h3>
                        </div>
                        <span class="rounded-full border border-slate-200/80 bg-slate-50/80 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200">{{ number_format($produtosSemGiro ?? 0, 0, ',', '.') }} itens</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($idleProducts as $product)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name') }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format(data_get($product, 'stock_quantity', 0), 0, ',', '.') }} unidades sem giro</p>
                                    </div>
                                    <span class="rounded-full border border-slate-200/80 bg-white/80 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-300">{{ ucfirst(data_get($product, 'status', 'n/d')) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-6 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">Todos os produtos tiveram giro recente.</div>
                        @endforelse
                    </div>
                </article>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Top kits</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Kits com maior saída</h3>
                        </div>
                        <span class="rounded-full border border-indigo-200/80 bg-indigo-50/80 px-3 py-1 text-xs font-semibold text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-200">{{ $topKitsCollection->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($topKitsCollection as $kit)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($kit, 'name') }}</p>
                                    <p class="text-sm font-black text-indigo-600 dark:text-indigo-300">{{ number_format(data_get($kit, 'total_vendido', 0), 0, ',', '.') }}</p>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ data_get($kit, 'product_code') }}</span>
                                    <span>R$ {{ number_format(data_get($kit, 'receita_total', 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum kit vendido no período.</p>
                        @endforelse
                    </div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-500">Cobertura por produto</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Dias projetados</h3>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($coverageProducts as $item)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($item, 'name') }}</p>
                                    <p class="text-sm font-black text-rose-600 dark:text-rose-300">{{ data_get($item, 'coverage_days') !== null ? number_format((float) data_get($item, 'coverage_days'), 1, ',', '.') . 'd' : 'Sem demanda' }}</p>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>Estoque {{ number_format(data_get($item, 'stock_quantity', 0), 0, ',', '.') }}</span>
                                    <span>Demanda/dia {{ number_format((float) data_get($item, 'daily_demand', 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem consumo suficiente para projetar cobertura.</p>
                        @endforelse
                    </div>
                </article>

                <article class="xl:col-span-4 rounded-[24px] border border-white/60 bg-white/85 p-5 shadow-[0_16px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-500">Cobertura por categoria</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Pressão por grupo</h3>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($coverageCategories as $item)
                            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/55">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($item, 'category_name') }}</p>
                                    <p class="text-sm font-black text-cyan-600 dark:text-cyan-300">{{ data_get($item, 'coverage_days') !== null ? number_format((float) data_get($item, 'coverage_days'), 1, ',', '.') . 'd' : 'Sem demanda' }}</p>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                    <span>Estoque {{ number_format(data_get($item, 'stock_quantity', 0), 0, ',', '.') }}</span>
                                    <span>Demanda/dia {{ number_format((float) data_get($item, 'daily_demand', 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem demanda suficiente para agrupar categorias.</p>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>
    </div>

    <style>
        .dashboard-products-page,
        .dashboard-products-page * {
            box-sizing: border-box;
        }

        .dashboard-products-shell {
            width: min(100%, 1720px);
            margin: 0 auto;
        }

        .products-chart-lg {
            min-height: 320px;
        }

        .products-chart-md {
            min-height: 300px;
        }

        .products-chart-sm {
            min-height: 220px;
        }

        .apexcharts-legend-text {
            color: #0f172a !important;
        }

        .dark .apexcharts-legend-text,
        .apexcharts-theme-dark .apexcharts-legend-text {
            color: #e5e7eb !important;
        }
    </style>

    <script id="dashboard-products-chart-payload" type="application/json">@json($chartPayload)</script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const payloadElement = document.getElementById('dashboard-products-chart-payload');
            if (!payloadElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const payload = JSON.parse(payloadElement.textContent || '{}');
            const chartRegistry = window.dashboardProductsCharts || {};
            window.dashboardProductsCharts = chartRegistry;

            const isDark = () => document.documentElement.classList.contains('dark');
            const palette = () => ({
                text: isDark() ? '#e2e8f0' : '#0f172a',
                muted: isDark() ? '#94a3b8' : '#64748b',
                border: isDark() ? 'rgba(148,163,184,0.18)' : '#e2e8f0',
                tooltip: isDark() ? 'dark' : 'light',
            });

            const currency = (value) => 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const destroyChart = (key) => {
                if (chartRegistry[key]) {
                    chartRegistry[key].destroy();
                    delete chartRegistry[key];
                }
            };

            const mountChart = (key, selector, optionsBuilder) => {
                const element = document.querySelector(selector);
                destroyChart(key);

                if (!element) {
                    return;
                }

                const chart = new ApexCharts(element, optionsBuilder());
                chart.render();
                chartRegistry[key] = chart;
            };

            const mountAllCharts = () => {
                const colors = palette();

                mountChart('periodComparison', '#productsComparisonChart', () => ({
                    series: [
                        { name: 'Receita', type: 'column', data: payload.periodComparison?.revenue || [] },
                        { name: 'Unidades', type: 'line', data: payload.periodComparison?.units || [] },
                        { name: 'Lucro', type: 'line', data: payload.periodComparison?.profit || [] },
                    ],
                    chart: { height: 320, type: 'line', toolbar: { show: false } },
                    colors: ['#f97316', '#0ea5e9', '#10b981'],
                    stroke: { width: [0, 3, 3], curve: 'smooth' },
                    dataLabels: { enabled: false },
                    fill: { opacity: [0.85, 1, 1] },
                    xaxis: { categories: payload.periodComparison?.labels || [], labels: { style: { colors: colors.muted } } },
                    yaxis: [
                        { labels: { formatter: (value) => 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { maximumFractionDigits: 0 }), style: { colors: colors.muted } } },
                        { opposite: true, labels: { style: { colors: colors.muted } } },
                    ],
                    legend: { position: 'top', horizontalAlign: 'left', labels: { colors: colors.text } },
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    tooltip: { theme: colors.tooltip },
                }));

                mountChart('marketplaceTrend', '#productsMarketplaceTrendChart', () => ({
                    series: [
                        { name: 'Mercado Livre', data: (payload.marketplaceTrend || []).map((item) => Number(item.ml || 0)) },
                        { name: 'Shopee', data: (payload.marketplaceTrend || []).map((item) => Number(item.shopee || 0)) },
                    ],
                    chart: { height: 300, type: 'line', toolbar: { show: false } },
                    colors: ['#f59e0b', '#f97316'],
                    stroke: { width: 3, curve: 'smooth' },
                    dataLabels: { enabled: false },
                    xaxis: { categories: (payload.marketplaceTrend || []).map((item) => item.label || ''), labels: { style: { colors: colors.muted } } },
                    yaxis: { labels: { style: { colors: colors.muted } } },
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    legend: { position: 'top', horizontalAlign: 'left', labels: { colors: colors.text } },
                    tooltip: { theme: colors.tooltip },
                }));

                mountChart('categoryMix', '#productsCategoryChart', () => ({
                    series: (payload.categoryMix || []).map((item) => Number(item.total_sold || 0)),
                    labels: (payload.categoryMix || []).map((item) => item.category_name || 'Sem categoria'),
                    chart: { type: 'donut', height: 300, toolbar: { show: false } },
                    colors: ['#f97316', '#fb7185', '#f59e0b', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#14b8a6'],
                    stroke: { width: 0 },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', labels: { colors: colors.text } },
                    plotOptions: { pie: { donut: { size: '68%' } } },
                    tooltip: { theme: colors.tooltip, y: { formatter: (value) => Number(value || 0).toLocaleString('pt-BR') + ' unidades' } },
                }));

                mountChart('topRevenue', '#productsRevenueChart', () => ({
                    series: [{ name: 'Receita', data: (payload.topRevenue || []).map((item) => Number(item.receita_total || 0)) }],
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    colors: ['#06b6d4'],
                    plotOptions: { bar: { horizontal: true, borderRadius: 10, barHeight: '55%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: (payload.topRevenue || []).map((item) => item.name || 'Produto'), labels: { style: { colors: colors.muted } } },
                    yaxis: { labels: { style: { colors: colors.muted } } },
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    tooltip: { theme: colors.tooltip, y: { formatter: currency } },
                }));

                mountChart('monthlyTrend', '#productsMonthlyChart', () => ({
                    series: [
                        { name: 'Unidades', type: 'column', data: (payload.monthlyTrend || []).map((item) => Number(item.total_units || 0)) },
                        { name: 'Receita', type: 'line', data: (payload.monthlyTrend || []).map((item) => Number(item.total_revenue || 0)) },
                    ],
                    chart: { height: 300, type: 'line', toolbar: { show: false } },
                    colors: ['#f59e0b', '#8b5cf6'],
                    stroke: { width: [0, 3], curve: 'smooth' },
                    fill: { type: ['solid', 'gradient'], gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: (payload.monthlyTrend || []).map((item) => item.label || ''), labels: { style: { colors: colors.muted } } },
                    yaxis: [
                        { labels: { style: { colors: colors.muted } } },
                        { opposite: true, labels: { formatter: (value) => 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { maximumFractionDigits: 0 }), style: { colors: colors.muted } } },
                    ],
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    tooltip: { theme: colors.tooltip },
                    legend: { position: 'top', horizontalAlign: 'left', labels: { colors: colors.text } },
                }));

                mountChart('stockSales', '#productsStockSalesChart', () => ({
                    series: [
                        { name: 'Estoque', data: (payload.stockSales || []).map((item) => Number(item.stock_quantity || 0)) },
                        { name: 'Vendidos', data: (payload.stockSales || []).map((item) => Number(item.total_vendido || 0)) },
                    ],
                    chart: { type: 'bar', height: 320, stacked: false, toolbar: { show: false } },
                    colors: ['#0ea5e9', '#f97316'],
                    plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: (payload.stockSales || []).map((item) => {
                            const name = item.name || 'Produto';
                            return name.length > 16 ? name.slice(0, 16) + '...' : name;
                        }),
                        labels: { style: { colors: colors.muted } },
                    },
                    yaxis: { labels: { style: { colors: colors.muted } } },
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    legend: { position: 'top', horizontalAlign: 'left', labels: { colors: colors.text } },
                    tooltip: { theme: colors.tooltip },
                }));

                mountChart('statusMix', '#productsStatusChart', () => ({
                    series: (payload.statusMix || []).map((item) => Number(item.value || 0)),
                    labels: (payload.statusMix || []).map((item) => item.label || 'Status'),
                    chart: { type: 'radialBar', height: 220, toolbar: { show: false } },
                    colors: ['#10b981', '#64748b', '#8b5cf6', '#f97316'],
                    plotOptions: {
                        radialBar: {
                            hollow: { size: '28%' },
                            dataLabels: {
                                name: { color: colors.muted, fontSize: '12px' },
                                value: { color: colors.text, fontSize: '18px', fontWeight: 700 },
                                total: {
                                    show: true,
                                    label: 'Catálogo',
                                    color: colors.muted,
                                    formatter: () => String((payload.statusMix || []).reduce((sum, item) => sum + Number(item.value || 0), 0)),
                                },
                            },
                        },
                    },
                    legend: { show: true, position: 'bottom', labels: { colors: colors.text } },
                }));

                mountChart('profitMargin', '#productsMarginChart', () => ({
                    series: [{ name: 'Margem', data: (payload.profitMargin || []).map((item) => Number(item.margem_percentual || 0)) }],
                    chart: { type: 'bar', height: 220, toolbar: { show: false } },
                    colors: ['#10b981'],
                    plotOptions: { bar: { borderRadius: 10, horizontal: true, barHeight: '58%' } },
                    dataLabels: { enabled: true, formatter: (value) => Number(value || 0).toFixed(1) + '%' },
                    xaxis: { categories: (payload.profitMargin || []).map((item) => item.name || 'Produto'), labels: { style: { colors: colors.muted } } },
                    yaxis: { labels: { style: { colors: colors.muted } } },
                    grid: { borderColor: colors.border, strokeDashArray: 4 },
                    tooltip: { theme: colors.tooltip, y: { formatter: (value) => Number(value || 0).toFixed(2) + '%' } },
                }));
            };

            mountAllCharts();

            const observer = new MutationObserver(() => mountAllCharts());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            document.addEventListener('livewire:navigated', mountAllCharts);
        });
    </script>
</div>
