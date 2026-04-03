<div class="dashboard-sales-page mobile-393-base app-viewport-fit min-h-screen w-full relative overflow-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-sales-ultrawide.css') }}">

    @php
        $statusCollection = collect($vendasPorStatus ?? []);
        $topClients = collect($vendasPorCliente ?? [])->take(6);
        $pendingClients = collect($clientesPendentes ?? [])->take(6);
        $inactiveClients = collect($clientesInativos ?? [])->take(5);
        $recurringClients = collect($clientesRecorrentes ?? [])->take(5);
        $soldProducts = collect($produtosMaisVendidos ?? [])->take(5);
        $temporalMetrics = collect($metricasTemporais ?? []);
        $recentMl = collect($recentMlOrders ?? [])->take(5);
        $recentShopee = collect($recentShopeeOrders ?? [])->take(5);
        $marketplaceActivity = collect($marketplaceLogs ?? [])->take(8);
        $dominantChannelIndex = collect($marketplaceSplit['revenue'] ?? [0, 0, 0])->search(collect($marketplaceSplit['revenue'] ?? [0, 0, 0])->max());
        $dominantChannel = data_get($marketplaceSplit, 'labels.' . (is_int($dominantChannelIndex) ? $dominantChannelIndex : 0), 'Loja');
        $comparisonCards = [
            [
                'title' => 'Mês atual',
                'count' => $vendasMesAtualCount,
                'amount' => $vendasMesAtual,
                'icon' => 'fas fa-calendar-day',
                'tone' => 'from-sky-500 to-indigo-600',
                'surface' => 'border-sky-200/80 bg-sky-50/75 dark:border-sky-500/20 dark:bg-sky-500/10',
                'text' => 'text-sky-700 dark:text-sky-200',
            ],
            [
                'title' => 'Mês anterior',
                'count' => $vendasMesAnteriorCount,
                'amount' => $vendasMesAnterior,
                'icon' => 'fas fa-calendar-minus',
                'tone' => 'from-slate-500 to-slate-700',
                'surface' => 'border-slate-200/80 bg-slate-50/75 dark:border-slate-700 dark:bg-slate-900/55',
                'text' => 'text-slate-700 dark:text-slate-200',
            ],
            [
                'title' => 'Ano atual',
                'count' => $vendasAnoAtualCount,
                'amount' => $vendasAnoAtual,
                'icon' => 'fas fa-calendar-check',
                'tone' => 'from-emerald-500 to-teal-600',
                'surface' => 'border-emerald-200/80 bg-emerald-50/75 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-200',
            ],
            [
                'title' => 'Ano anterior',
                'count' => $vendasAnoAnteriorCount,
                'amount' => $vendasAnoAnterior,
                'icon' => 'fas fa-calendar-alt',
                'tone' => 'from-fuchsia-500 to-pink-600',
                'surface' => 'border-fuchsia-200/80 bg-fuchsia-50/75 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10',
                'text' => 'text-fuchsia-700 dark:text-fuchsia-200',
            ],
        ];
        $quickMetrics = [
            [
                'label' => 'Crescimento mensal',
                'value' => number_format($growthRate ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-chart-line',
                'surface' => 'border-cyan-200/80 bg-cyan-50/75 dark:border-cyan-500/20 dark:bg-cyan-500/10',
                'text' => 'text-cyan-700 dark:text-cyan-200',
            ],
            [
                'label' => 'Conversão',
                'value' => number_format($conversionRate ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-badge-percent',
                'surface' => 'border-emerald-200/80 bg-emerald-50/75 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-200',
            ],
            [
                'label' => 'Eficiência',
                'value' => number_format($eficienciaVendas ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-gauge-high',
                'surface' => 'border-violet-200/80 bg-violet-50/75 dark:border-violet-500/20 dark:bg-violet-500/10',
                'text' => 'text-violet-700 dark:text-violet-200',
            ],
            [
                'label' => 'Satisfação',
                'value' => number_format($customerSatisfaction ?? 0, 1, ',', '.') . '%',
                'icon' => 'fas fa-heart',
                'surface' => 'border-pink-200/80 bg-pink-50/75 dark:border-pink-500/20 dark:bg-pink-500/10',
                'text' => 'text-pink-700 dark:text-pink-200',
            ],
            [
                'label' => 'Dias ativos',
                'value' => number_format($totalDiasAtivos ?? 0, 0, ',', '.'),
                'icon' => 'fas fa-calendar-week',
                'surface' => 'border-amber-200/80 bg-amber-50/75 dark:border-amber-500/20 dark:bg-amber-500/10',
                'text' => 'text-amber-700 dark:text-amber-200',
            ],
            [
                'label' => 'Clientes pendentes',
                'value' => number_format($clientesComSalesPendentes ?? 0, 0, ',', '.'),
                'icon' => 'fas fa-user-clock',
                'surface' => 'border-rose-200/80 bg-rose-50/75 dark:border-rose-500/20 dark:bg-rose-500/10',
                'text' => 'text-rose-700 dark:text-rose-200',
            ],
            [
                'label' => 'Canal dominante',
                'value' => $dominantChannel,
                'icon' => 'fas fa-tower-broadcast',
                'surface' => 'border-sky-200/80 bg-sky-50/75 dark:border-sky-500/20 dark:bg-sky-500/10',
                'text' => 'text-sky-700 dark:text-sky-200',
            ],
            [
                'label' => 'Publicações ativas',
                'value' => number_format(($mlPublicationsAtivas ?? 0) + ($shopeePublicationsAtivas ?? 0), 0, ',', '.'),
                'icon' => 'fas fa-shop',
                'surface' => 'border-orange-200/80 bg-orange-50/75 dark:border-orange-500/20 dark:bg-orange-500/10',
                'text' => 'text-orange-700 dark:text-orange-200',
            ],
        ];
        $marketplaceCards = [
            [
                'title' => 'Mercado Livre',
                'orders' => $mlOrdersCount,
                'amount' => $mlRevenue,
                'meta' => $mlPublicationsAtivas . ' publicacao(oes) ativa(s)',
                'icon' => 'fas fa-store',
                'surface' => 'border-yellow-200/80 bg-yellow-50/75 dark:border-yellow-500/20 dark:bg-yellow-500/10',
                'text' => 'text-yellow-700 dark:text-yellow-200',
            ],
            [
                'title' => 'Shopee',
                'orders' => $shopeeOrdersCount,
                'amount' => $shopeeRevenue,
                'meta' => $shopeePublicationsAtivas . ' publicacao(oes) ativa(s)',
                'icon' => 'fas fa-bag-shopping',
                'surface' => 'border-orange-200/80 bg-orange-50/75 dark:border-orange-500/20 dark:bg-orange-500/10',
                'text' => 'text-orange-700 dark:text-orange-200',
            ],
            [
                'title' => 'Pedidos pagos ML',
                'orders' => $mlPaidOrders,
                'amount' => 0,
                'meta' => 'pedidos aprovados ou entregues',
                'icon' => 'fas fa-circle-check',
                'surface' => 'border-emerald-200/80 bg-emerald-50/75 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-200',
            ],
            [
                'title' => 'Pedidos concluidos Shopee',
                'orders' => $shopeeCompletedOrders,
                'amount' => 0,
                'meta' => 'pedidos enviados ou concluidos',
                'icon' => 'fas fa-truck-fast',
                'surface' => 'border-sky-200/80 bg-sky-50/75 dark:border-sky-500/20 dark:bg-sky-500/10',
                'text' => 'text-sky-700 dark:text-sky-200',
            ],
        ];
        $chartPayload = [
            'salesEvolution' => collect($vendasPorMesEvolucao ?? [])->values()->all(),
            'categoryChart' => collect($dadosGraficoPizza ?? [])->values()->all(),
            'topProducts' => collect($topProdutos ?? [])->values()->all(),
            'salesByHour' => array_values($vendasPorHora ?? []),
            'temporalMetrics' => $temporalMetrics->values()->all(),
            'marketplaceSplit' => $marketplaceSplit ?? [],
            'marketplaceMonthly' => $marketplaceMonthly ?? [],
            'marketplaceStatus' => $marketplaceStatus ?? [],
        ];
    @endphp

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(168,85,247,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.14),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.04),_transparent)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(168,85,247,0.26),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.18),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.6),_transparent)]"></div>
        <div class="absolute left-16 top-16 h-52 w-52 rounded-full bg-fuchsia-400/10 blur-3xl"></div>
        <div class="absolute right-10 top-10 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl"></div>
    </div>

    <div class="dashboard-sales-shell px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <section class="space-y-4">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <div class="xl:col-span-8 overflow-hidden rounded-[28px] border border-white/60 bg-white/85 shadow-[0_20px_80px_rgba(15,23,42,0.10)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="relative overflow-hidden px-5 py-6 sm:px-6 sm:py-6">
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(168,85,247,0.10),rgba(236,72,153,0.08),rgba(56,189,248,0.07))] dark:bg-[linear-gradient(135deg,rgba(168,85,247,0.18),rgba(236,72,153,0.14),rgba(56,189,248,0.12))]"></div>
                        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-3xl space-y-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-fuchsia-200/80 bg-fuchsia-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-fuchsia-700 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10 dark:text-fuchsia-200">Parte 1</span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/75 px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300"><i class="fas fa-signal text-emerald-500"></i>Performance comercial</span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"><i class="fas fa-circle text-[8px]"></i>Atualizado agora</span>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-300">
                                        <a href="{{ route('dashboard') }}" class="transition hover:text-fuchsia-600 dark:hover:text-fuchsia-300"><i class="fas fa-chart-line mr-1"></i>Dashboard</a>
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                        <span class="font-semibold text-slate-700 dark:text-slate-200"><i class="fas fa-cart-shopping mr-1"></i>Vendas</span>
                                    </div>
                                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Dashboard comercial completo</h1>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">Uma leitura mais compacta e proporcional de faturamento, conversão, ritmo de vendas, clientes e mix de produtos, pensada para caber melhor em qualquer tela.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                    <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Faturamento</p>
                                        <p class="mt-2 text-2xl font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($totalFaturamento ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/80 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-700 dark:text-indigo-300">Ticket médio</p>
                                        <p class="mt-2 text-2xl font-black text-indigo-700 dark:text-indigo-200">R$ {{ number_format($averageOrderValue ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Vendas hoje</p>
                                        <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-200">{{ number_format($vendasHoje ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/80 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-700 dark:text-rose-300">Em aberto</p>
                                        <p class="mt-2 text-2xl font-black text-rose-700 dark:text-rose-200">R$ {{ number_format($totalFaltante ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[320px]">
                                <a href="{{ route('dashboard.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-fuchsia-300 hover:text-fuchsia-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-fuchsia-500/40 dark:hover:text-fuchsia-300"><span class="flex items-center gap-3"><i class="fas fa-house text-fuchsia-500"></i>Dashboard geral</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                <a href="{{ route('sales.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"><span class="flex items-center gap-3"><i class="fas fa-sliders text-indigo-500"></i>Gerenciar vendas</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-300">
                                    <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"><i class="fas fa-user-group text-sky-500"></i>Base ativa</div>
                                    <div class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format($totalClientes ?? 0, 0, ',', '.') }}</div>
                                    <div class="mt-1 text-xs">clientes no relacionamento</div>
                                </div>
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-300">
                                    <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"><i class="fas fa-repeat text-emerald-500"></i>Retenção</div>
                                    <div class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format($taxaRetencao ?? 0, 1, ',', '.') }}%</div>
                                    <div class="mt-1 text-xs">clientes recorrentes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4">
                    @foreach ($quickMetrics as $metric)
                        <div class="rounded-[24px] border p-4 shadow-[0_12px_40px_rgba(15,23,42,0.08)] backdrop-blur {{ $metric['surface'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.22em] {{ $metric['text'] }}">{{ $metric['label'] }}</p>
                                    <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
                                </div>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/80 text-slate-700 shadow-sm dark:bg-slate-900/75 dark:text-slate-200">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-sky-200/80 bg-sky-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200">Parte 2</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Leitura gráfica das vendas</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Evolução, categorias, top produtos e ritmo por horário com uma apresentação mais proporcional.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-7 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Evolução de faturamento</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Últimos 12 meses com curva de receita</p>
                        </div>
                        <span class="rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-bold text-fuchsia-700 dark:bg-fuchsia-500/10 dark:text-fuchsia-200">12 meses</span>
                    </div>
                    <div class="p-4 sm:p-5"><div id="salesEvolutionChart" class="sales-chart sales-chart-lg"></div></div>
                </div>

                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Mix por categoria</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Categorias com mais volume vendido</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="categoryChart" class="sales-chart sales-chart-md"></div></div>
                </div>

                <div class="xl:col-span-6 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Produtos campeões</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Volume vendido por item</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="topProductsChart" class="sales-chart sales-chart-md"></div></div>
                </div>

                <div class="xl:col-span-6 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Ritmo por horário</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Janelas horárias com maior atividade</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="salesByHourChart" class="sales-chart sales-chart-md"></div></div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/80 bg-indigo-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-200">Parte 3</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Comparativos e produtividade</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Comparação de períodos e indicadores de retenção, valor por cliente e consistência de operação.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach ($comparisonCards as $card)
                        <div class="rounded-[24px] border p-4 shadow-[0_18px_40px_rgba(15,23,42,0.08)] {{ $card['surface'] }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['tone'] }} text-white shadow-lg"><i class="{{ $card['icon'] }}"></i></div>
                                <span class="text-[11px] font-black uppercase tracking-[0.18em] {{ $card['text'] }}">{{ $card['title'] }}</span>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Vendas</p>
                                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($card['count'], 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Faturamento</p>
                                    <p class="text-base font-black {{ $card['text'] }}">R$ {{ number_format($card['amount'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-white shadow-lg"><i class="fas fa-bullseye"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Radar de produtividade</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sinais rápidos do comercial</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70"><div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Ticket recorrente</span><span class="text-lg font-black text-slate-900 dark:text-white">R$ {{ number_format($ticketMedioRecorrente ?? 0, 2, ',', '.') }}</span></div></div>
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70"><div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">CLV médio</span><span class="text-lg font-black text-slate-900 dark:text-white">R$ {{ number_format($clvMedio ?? 0, 2, ',', '.') }}</span></div></div>
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70"><div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Crescimento anual</span><span class="text-lg font-black {{ ($crescimentoAnual ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-200' : 'text-rose-700 dark:text-rose-200' }}">{{ number_format($crescimentoAnual ?? 0, 1, ',', '.') }}%</span></div></div>
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70"><div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Velocidade diária</span><span class="text-lg font-black text-slate-900 dark:text-white">{{ number_format($velocidadeVendas ?? 0, 1, ',', '.') }}</span></div></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4">
                    @foreach ($marketplaceCards as $card)
                        <div class="rounded-[24px] border p-4 shadow-[0_18px_40px_rgba(15,23,42,0.08)] {{ $card['surface'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.2em] {{ $card['text'] }}">{{ $card['title'] }}</p>
                                    <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ number_format($card['orders'], 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $card['meta'] }}</p>
                                    @if ($card['amount'] > 0)
                                        <p class="mt-3 text-sm font-black {{ $card['text'] }}">R$ {{ number_format($card['amount'], 2, ',', '.') }}</p>
                                    @endif
                                </div>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/80 text-slate-700 shadow-sm dark:bg-slate-900/75 dark:text-slate-200"><i class="{{ $card['icon'] }}"></i></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Mix de canais</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Comparativo entre loja, Mercado Livre e Shopee</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="marketplaceSplitChart" class="sales-chart sales-chart-md"></div></div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Status dos pedidos de marketplace</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Concluídos, pendentes e cancelados por canal</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="marketplaceStatusChart" class="sales-chart sales-chart-md"></div></div>
                </div>

                <div class="xl:col-span-12 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Tendência mensal por canal</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Receita comparada entre loja interna, Mercado Livre e Shopee nos últimos 6 meses</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="marketplaceMonthlyChart" class="sales-chart sales-chart-lg"></div></div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">Parte 4</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Clientes, retenção e recorrência</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Quem mais compra, onde há pendências e quais relações merecem reativação.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Top clientes</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Melhores resultados por cliente</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">{{ $topClients->count() }} em destaque</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($topClients as $client)
                            <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-sm font-black text-white">{{ strtoupper(substr(data_get($client, 'client.name', 'C'), 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($client, 'client.name', 'Cliente') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format(data_get($client, 'qtd_vendas', 0), 0, ',', '.') }} venda(s)</p>
                                    </div>
                                </div>
                                <div class="text-right text-sm font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format((float) data_get($client, 'total_vendas', 0), 2, ',', '.') }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">Nenhum cliente com destaque ainda.</div>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Pendências comerciais</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Clientes com vendas em aberto</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($pendingClients as $client)
                            <div class="rounded-2xl border border-rose-200/70 bg-rose-50/70 p-3 dark:border-rose-500/20 dark:bg-rose-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-rose-800 dark:text-rose-200">{{ data_get($client, 'name', 'Cliente') }}</p>
                                        <p class="text-xs text-rose-600 dark:text-rose-300">{{ collect(data_get($client, 'sales', []))->count() }} venda(s) pendente(s)</p>
                                    </div>
                                    <i class="fas fa-triangle-exclamation text-rose-500"></i>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">Sem pendências relevantes.</div>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-3 grid grid-cols-1 gap-4">
                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes recorrentes</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Relações mais constantes</p>
                        <div class="mt-4 space-y-3">
                            @forelse ($recurringClients as $client)
                                <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($client, 'client.name', 'Cliente') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format(data_get($client, 'qtd_vendas', 0), 0, ',', '.') }} compras</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Ainda sem recorrência expressiva.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes inativos</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sem compras recentes</p>
                        <div class="mt-4 space-y-3">
                            @forelse ($inactiveClients as $client)
                                <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($client, 'name', 'Cliente') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">criado em {{ \Carbon\Carbon::parse(data_get($client, 'created_at'))->format('d/m/Y') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum cliente inativo relevante.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200/80 bg-amber-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">Parte 5</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Operação detalhada</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Status de vendas, ranking de produtos, comportamento por dia da semana e último movimento registrado.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Status das vendas</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($statusCollection as $status)
                            <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <div class="flex items-center gap-3">
                                    <span class="h-3 w-3 rounded-full {{ match(strtolower((string) data_get($status, 'status', ''))) { 'finalizado' => 'bg-emerald-500', 'pendente' => 'bg-amber-500', 'cancelado' => 'bg-rose-500', default => 'bg-sky-500' } }}"></span>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ data_get($status, 'status', 'Sem status') }}</span>
                                </div>
                                <span class="text-lg font-black text-slate-900 dark:text-white">{{ number_format((int) data_get($status, 'total', 0), 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum status encontrado.</p>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Produtos mais vendidos</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($soldProducts as $product)
                            <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ data_get($product, 'name', 'Produto') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format((int) data_get($product, 'total_vendido', 0), 0, ',', '.') }} unidade(s)</p>
                                    </div>
                                    <div class="text-right text-sm font-black text-amber-700 dark:text-amber-200">R$ {{ number_format((float) data_get($product, 'receita_total', 0), 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Ainda sem produtos ranqueados.</p>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-4 grid grid-cols-1 gap-4">
                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Comportamento semanal</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($temporalMetrics as $day)
                                <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ data_get($day, 'dia', 'Dia') }}</span>
                                        <span class="text-slate-500 dark:text-slate-400">{{ number_format((int) data_get($day, 'total_vendas', 0), 0, ',', '.') }} venda(s)</span>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Ticket médio: R$ {{ number_format((float) data_get($day, 'ticket_medio', 0), 2, ',', '.') }}</div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Sem dados temporais suficientes.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Última venda</h3>
                        @if ($ultimaVenda)
                            <div class="mt-4 rounded-2xl border border-fuchsia-200/70 bg-fuchsia-50/70 p-4 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10">
                                <p class="text-sm font-bold text-fuchsia-800 dark:text-fuchsia-200">Pedido #{{ $ultimaVenda->id }}</p>
                                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $ultimaVenda->total_price, 2, ',', '.') }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($ultimaVenda->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Nenhuma venda registrada ainda.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pulso semanal das vendas</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Volume e ticket médio por dia da semana</p>
                    </div>
                    <div class="p-4 sm:p-5"><div id="temporalMetricsChart" class="sales-chart sales-chart-md"></div></div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-500 text-white shadow-lg"><i class="fas fa-store"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Pedidos recentes de marketplace</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Últimos registros de ML e Shopee</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-700 dark:text-yellow-200">Mercado Livre</p>
                            <div class="space-y-3">
                                @forelse ($recentMl as $order)
                                    <div class="rounded-2xl border border-yellow-200/70 bg-yellow-50/70 p-3 dark:border-yellow-500/20 dark:bg-yellow-500/10">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">#{{ data_get($order, 'ml_order_id') }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ data_get($order, 'buyer_nickname', 'Comprador') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-black text-yellow-700 dark:text-yellow-200">R$ {{ number_format((float) data_get($order, 'total_amount', 0), 2, ',', '.') }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse(data_get($order, 'date_created'))->format('d/m H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Sem pedidos recentes no ML.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-orange-700 dark:text-orange-200">Shopee</p>
                            <div class="space-y-3">
                                @forelse ($recentShopee as $order)
                                    <div class="rounded-2xl border border-orange-200/70 bg-orange-50/70 p-3 dark:border-orange-500/20 dark:bg-orange-500/10">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">#{{ data_get($order, 'shopee_order_sn') }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ data_get($order, 'buyer_username', 'Comprador') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-black text-orange-700 dark:text-orange-200">R$ {{ number_format((float) data_get($order, 'total_amount', 0), 2, ',', '.') }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ data_get($order, 'shopee_created_at') ? \Carbon\Carbon::parse(data_get($order, 'shopee_created_at'))->format('d/m H:i') : '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Sem pedidos recentes na Shopee.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-3 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-lg"><i class="fas fa-arrows-rotate"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Saúde de sincronização</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Logs recentes dos canais</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200">ML sucesso</span><span class="font-black text-emerald-700 dark:text-emerald-200">{{ number_format((int) data_get($marketplaceSyncHealth, 'mercadoLivre.success', 0), 0, ',', '.') }}</span></div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200">ML erro</span><span class="font-black text-rose-700 dark:text-rose-200">{{ number_format((int) data_get($marketplaceSyncHealth, 'mercadoLivre.error', 0), 0, ',', '.') }}</span></div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200">Shopee sucesso</span><span class="font-black text-emerald-700 dark:text-emerald-200">{{ number_format((int) data_get($marketplaceSyncHealth, 'shopee.success', 0), 0, ',', '.') }}</span></div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200">Shopee erro</span><span class="font-black text-rose-700 dark:text-rose-200">{{ number_format((int) data_get($marketplaceSyncHealth, 'shopee.error', 0), 0, ',', '.') }}</span></div>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-slate-200/80 pt-4 dark:border-slate-800">
                        <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Atividade recente</p>
                        <div class="space-y-3">
                            @forelse ($marketplaceActivity as $log)
                                <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-black uppercase tracking-[0.16em] {{ data_get($log, 'channel') === 'Shopee' ? 'text-orange-700 dark:text-orange-200' : 'text-yellow-700 dark:text-yellow-200' }}">{{ data_get($log, 'channel') }}</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ data_get($log, 'created_at') }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ data_get($log, 'sync_type', 'sync') }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ data_get($log, 'message', 'Sem mensagem') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Sem logs recentes.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .dashboard-sales-shell {
            width: 100%;
            max-width: 100%;
            margin-inline: auto;
            box-sizing: border-box;
        }

        .dashboard-sales-page,
        .dashboard-sales-page * {
            box-sizing: border-box;
        }

        .dashboard-sales-page {
            overflow-x: clip;
        }

        .dashboard-sales-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.84)) !important;
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.62);
        }

        .dark .dashboard-sales-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.8)) !important;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.42), inset 0 1px 0 rgba(148, 163, 184, 0.08);
        }

        .dashboard-sales-page [class*="bg-slate-50/80"],
        .dashboard-sales-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(245, 247, 255, 0.88), rgba(237, 242, 255, 0.72)) !important;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .dark .dashboard-sales-page [class*="bg-slate-50/80"],
        .dark .dashboard-sales-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.8)) !important;
            border-color: rgba(71, 85, 105, 0.32);
        }

        .sales-chart {
            width: 100%;
            border-radius: 24px;
            background: radial-gradient(circle at top left, rgba(168, 85, 247, 0.12), transparent 38%), radial-gradient(circle at bottom right, rgba(56, 189, 248, 0.10), transparent 34%), linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(248, 250, 252, 0.40));
            border: 1px solid rgba(148, 163, 184, 0.14);
            padding: 0.35rem;
        }

        .dark .sales-chart {
            background: radial-gradient(circle at top left, rgba(168, 85, 247, 0.18), transparent 38%), radial-gradient(circle at bottom right, rgba(56, 189, 248, 0.18), transparent 34%), linear-gradient(180deg, rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.40));
            border-color: rgba(71, 85, 105, 0.32);
        }

        .sales-chart-lg {
            min-height: 320px;
        }

        .sales-chart-md {
            min-height: 260px;
        }

        .dashboard-sales-page h1.text-3xl.font-black {
            font-size: clamp(2rem, 1.8vw, 2.9rem) !important;
        }

        .dashboard-sales-page h2.text-2xl.font-black {
            font-size: clamp(1.4rem, 1vw, 1.9rem) !important;
        }

        .dashboard-sales-page .text-2xl.font-black {
            font-size: clamp(1.2rem, 0.88vw, 1.5rem) !important;
        }

        .dashboard-sales-page .rounded-\[28px\],
        .dashboard-sales-page .rounded-\[24px\] {
            border-radius: 22px !important;
        }

        .apexcharts-legend-text {
            color: #0f172a !important;
        }

        .dark .apexcharts-legend-text,
        .apexcharts-theme-dark .apexcharts-legend-text {
            color: #e5e7eb !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="application/json" id="dashboard-sales-chart-payload">@json($chartPayload)</script>

    <script>
        (() => {
            const payloadElement = document.getElementById('dashboard-sales-chart-payload');
            const chartData = payloadElement ? JSON.parse(payloadElement.textContent) : {};
            const chartRegistry = window.dashboardSalesChartRegistry || {};
            window.dashboardSalesChartRegistry = chartRegistry;

            const money = (value, decimals = 2) => 'R$ ' + Number(value || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });

            const compactMoney = (value) => 'R$ ' + Number(value || 0).toLocaleString('pt-BR', {
                maximumFractionDigits: 0,
            });

            const isDark = () => document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

            const destroyCharts = () => {
                Object.keys(chartRegistry).forEach((key) => {
                    if (chartRegistry[key] && typeof chartRegistry[key].destroy === 'function') {
                        chartRegistry[key].destroy();
                    }
                    delete chartRegistry[key];
                });
            };

            const mountChart = (selector, options) => {
                const element = document.querySelector(selector);
                if (!element || typeof ApexCharts === 'undefined') {
                    return;
                }

                const chart = new ApexCharts(element, options);
                chart.render();
                chartRegistry[selector] = chart;
            };

            const renderCharts = () => {
                if (typeof ApexCharts === 'undefined') {
                    return;
                }

                destroyCharts();

                const darkMode = isDark();
                const labelColor = darkMode ? '#cbd5e1' : '#64748b';
                const gridColor = darkMode ? '#334155' : '#e2e8f0';
                const tooltipTheme = darkMode ? 'dark' : 'light';
                const largeHeight = window.innerWidth < 640 ? 260 : window.innerWidth < 1280 ? 300 : 340;
                const mediumHeight = window.innerWidth < 640 ? 240 : 280;

                const baseChart = {
                    chart: {
                        toolbar: { show: false },
                        animations: { enabled: true, speed: 650 },
                        fontFamily: 'inherit',
                        foreColor: labelColor,
                    },
                    dataLabels: { enabled: false },
                    grid: { borderColor: gridColor, strokeDashArray: 4 },
                    theme: { mode: darkMode ? 'dark' : 'light' },
                };

                mountChart('#salesEvolutionChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Faturamento',
                            data: (chartData.salesEvolution || []).map((item) => Number(item.total || 0)),
                        },
                    ],
                    chart: { ...baseChart.chart, type: 'area', height: largeHeight },
                    colors: ['#a855f7'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.08, stops: [0, 90, 100] },
                    },
                    xaxis: {
                        categories: (chartData.salesEvolution || []).map((item) => item.periodo || 'Mes'),
                        labels: { style: { colors: labelColor } },
                    },
                    yaxis: {
                        labels: { formatter: (value) => compactMoney(value), style: { colors: labelColor } },
                    },
                    tooltip: { theme: tooltipTheme, y: { formatter: (value) => money(value) } },
                });

                mountChart('#categoryChart', {
                    ...baseChart,
                    series: (chartData.categoryChart || []).map((item) => Number(item.total_sold || 0)),
                    labels: (chartData.categoryChart || []).map((item) => item.category_name || 'Categoria'),
                    chart: { ...baseChart.chart, type: 'donut', height: mediumHeight },
                    colors: ['#a855f7', '#ec4899', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
                    legend: { position: 'bottom' },
                    plotOptions: { pie: { donut: { size: '64%' } } },
                    tooltip: { theme: tooltipTheme },
                    dataLabels: { enabled: true, formatter: (value) => Number(value).toFixed(0) + '%' },
                });

                mountChart('#topProductsChart', {
                    ...baseChart,
                    series: [{ name: 'Unidades', data: (chartData.topProducts || []).map((item) => Number(item.total_vendido || 0)) }],
                    chart: { ...baseChart.chart, type: 'bar', height: mediumHeight },
                    colors: ['#f59e0b'],
                    plotOptions: { bar: { borderRadius: 10, horizontal: true, barHeight: '58%' } },
                    xaxis: { labels: { style: { colors: labelColor } } },
                    yaxis: { categories: (chartData.topProducts || []).map((item) => item.name || 'Produto'), labels: { style: { colors: labelColor } } },
                    tooltip: { theme: tooltipTheme },
                });

                mountChart('#salesByHourChart', {
                    ...baseChart,
                    series: [{ name: 'Vendas', data: (chartData.salesByHour || []).map((item) => Number(item || 0)) }],
                    chart: { ...baseChart.chart, type: 'line', height: mediumHeight },
                    colors: ['#3b82f6'],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    xaxis: {
                        categories: ['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h'],
                        labels: { style: { colors: labelColor } },
                    },
                    yaxis: { labels: { style: { colors: labelColor } } },
                    tooltip: { theme: tooltipTheme, y: { formatter: (value) => value + ' vendas' } },
                });

                mountChart('#temporalMetricsChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Vendas',
                            type: 'column',
                            data: (chartData.temporalMetrics || []).map((item) => Number(item.total_vendas || 0)),
                        },
                        {
                            name: 'Ticket médio',
                            type: 'line',
                            data: (chartData.temporalMetrics || []).map((item) => Number(item.ticket_medio || 0)),
                        },
                    ],
                    chart: { ...baseChart.chart, type: 'line', height: mediumHeight, stacked: false },
                    colors: ['#8b5cf6', '#10b981'],
                    stroke: { width: [0, 3], curve: 'smooth' },
                    plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
                    xaxis: {
                        categories: (chartData.temporalMetrics || []).map((item) => item.dia || 'Dia'),
                        labels: { style: { colors: labelColor } },
                    },
                    yaxis: [
                        { labels: { style: { colors: labelColor } } },
                        {
                            opposite: true,
                            labels: { formatter: (value) => compactMoney(value), style: { colors: labelColor } },
                        },
                    ],
                    tooltip: { theme: tooltipTheme, y: [{ formatter: (value) => value + ' vendas' }, { formatter: (value) => money(value) }] },
                    legend: { position: 'top', horizontalAlign: 'right' },
                });

                mountChart('#marketplaceSplitChart', {
                    ...baseChart,
                    series: (chartData.marketplaceSplit?.revenue || []).map((value) => Number(value || 0)),
                    labels: chartData.marketplaceSplit?.labels || [],
                    chart: { ...baseChart.chart, type: 'donut', height: mediumHeight },
                    colors: ['#6366f1', '#f59e0b', '#fb923c'],
                    legend: { position: 'bottom' },
                    plotOptions: { pie: { donut: { size: '62%' } } },
                    tooltip: { theme: tooltipTheme, y: { formatter: (value) => money(value) } },
                    dataLabels: { enabled: true, formatter: (value) => Number(value).toFixed(0) + '%' },
                });

                mountChart('#marketplaceStatusChart', {
                    ...baseChart,
                    series: [
                        { name: 'Mercado Livre', data: (chartData.marketplaceStatus?.mercadoLivre || []).map((value) => Number(value || 0)) },
                        { name: 'Shopee', data: (chartData.marketplaceStatus?.shopee || []).map((value) => Number(value || 0)) },
                    ],
                    chart: { ...baseChart.chart, type: 'bar', height: mediumHeight, stacked: true },
                    colors: ['#f59e0b', '#fb923c'],
                    plotOptions: { bar: { borderRadius: 10, columnWidth: '48%' } },
                    xaxis: { categories: chartData.marketplaceStatus?.labels || [], labels: { style: { colors: labelColor } } },
                    yaxis: { labels: { style: { colors: labelColor } } },
                    tooltip: { theme: tooltipTheme },
                    legend: { position: 'top', horizontalAlign: 'right' },
                });

                mountChart('#marketplaceMonthlyChart', {
                    ...baseChart,
                    series: [
                        { name: 'Loja', data: (chartData.marketplaceMonthly?.internal || []).map((value) => Number(value || 0)) },
                        { name: 'Mercado Livre', data: (chartData.marketplaceMonthly?.mercadoLivre || []).map((value) => Number(value || 0)) },
                        { name: 'Shopee', data: (chartData.marketplaceMonthly?.shopee || []).map((value) => Number(value || 0)) },
                    ],
                    chart: { ...baseChart.chart, type: 'line', height: largeHeight },
                    colors: ['#6366f1', '#f59e0b', '#fb923c'],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    xaxis: { categories: chartData.marketplaceMonthly?.labels || [], labels: { style: { colors: labelColor } } },
                    yaxis: { labels: { formatter: (value) => compactMoney(value), style: { colors: labelColor } } },
                    tooltip: { theme: tooltipTheme, y: { formatter: (value) => money(value) } },
                    legend: { position: 'top', horizontalAlign: 'right' },
                });
            };

            const bootCharts = () => {
                renderCharts();

                const observer = new MutationObserver(() => {
                    renderCharts();
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });

                window.addEventListener('resize', renderCharts, { passive: true });
                document.addEventListener('livewire:navigated', renderCharts);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootCharts, { once: true });
            } else {
                bootCharts();
            }
        })();
    </script>
</div>
