<div class="dashboard-index-page mobile-393-base app-viewport-fit min-h-screen w-full relative overflow-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-index-ultrawide.css') }}">

    <x-loading-overlay message="Carregando dashboard..." />

    @php
        $budgetUsagePercent = $orcamentoMesTotal > 0 ? min(($orcamentoMesUsado / $orcamentoMesTotal) * 100, 100) : 0;
        $receivableCoveragePercent = $contasPagarPendentes > 0
            ? min(($contasReceberPendentes / $contasPagarPendentes) * 100, 100)
            : ($contasReceberPendentes > 0 ? 100 : 0);
        $inventoryHealthPercent = $totalProdutos > 0 ? min(($produtosAtivos / $totalProdutos) * 100, 100) : 0;
        $alertsCount = count($alertas ?? []);
        $activitiesCount = count($atividades ?? []);
        $historyBreakdown = collect($atividades ?? [])->groupBy('module')->map->count()->sortDesc()->take(4);
        $progressWidthClass = function (float $value): string {
            return match (true) {
                $value >= 100 => 'w-full',
                $value >= 90 => 'w-[90%]',
                $value >= 80 => 'w-4/5',
                $value >= 70 => 'w-[70%]',
                $value >= 60 => 'w-3/5',
                $value >= 50 => 'w-1/2',
                $value >= 40 => 'w-2/5',
                $value >= 30 => 'w-[30%]',
                $value >= 20 => 'w-1/4',
                $value > 0 => 'w-[8%]',
                default => 'w-0',
            };
        };
        $budgetUsageWidthClass = $progressWidthClass($budgetUsagePercent);
        $receivableCoverageWidthClass = $progressWidthClass($receivableCoveragePercent);
        $inventoryHealthWidthClass = $progressWidthClass($inventoryHealthPercent);
        $chartReferenceDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1);
        $dashboardChartPayload = [
            'monthLabels' => [
                $chartReferenceDate->copy()->subMonths(5)->format('M'),
                $chartReferenceDate->copy()->subMonths(4)->format('M'),
                $chartReferenceDate->copy()->subMonths(3)->format('M'),
                $chartReferenceDate->copy()->subMonths(2)->format('M'),
                $chartReferenceDate->copy()->subMonths(1)->format('M'),
                $chartReferenceDate->copy()->format('M'),
            ],
            'cashflowMonthly' => $cashflowMonthly,
            'expensesByCategory' => $expensesByCategory,
            'gastosInvoicePorBanco' => $gastosInvoicePorBanco,
            'budgetUsagePercent' => round($budgetUsagePercent, 2),
            'receivableCoveragePercent' => round($receivableCoveragePercent, 2),
            'inventoryHealthPercent' => round($inventoryHealthPercent, 2),
            'modulePresence' => [
                (int) $salesMonth,
                (int) $totalClientes,
                (int) $produtosAtivos,
                (int) $totalInvoices,
                (int) $totalCofrinhos,
                (int) $totalConsorciosAtivos,
            ],
            'salesVsCosts' => [
                (float) $valorVendas,
                (float) $custoEstoque,
                (float) $custoProdutosVendidos,
            ],
                'periodComparison' => $periodComparison,
        ];

        $executiveTiles = [
            [
                'label' => 'Lucro líquido',
                'value' => 'R$ ' . number_format($lucroLiquido, 2, ',', '.'),
                'note' => $lucroLiquido >= 0 ? 'Resultado positivo no mês' : 'Resultado pressionado no mês',
                'eyebrowClass' => 'text-emerald-600 dark:text-emerald-300',
                'iconClass' => 'bg-gradient-to-br from-emerald-500 to-emerald-600',
                'icon' => 'fas fa-sparkles',
            ],
            [
                'label' => 'Margem operacional',
                'value' => number_format($margemLucro, 1, ',', '.') . '%',
                'note' => 'Comparação entre vendas e custos vendidos',
                'eyebrowClass' => 'text-indigo-600 dark:text-indigo-300',
                'iconClass' => 'bg-gradient-to-br from-indigo-500 to-indigo-600',
                'icon' => 'fas fa-chart-line',
            ],
            [
                'label' => 'Orçamento consumido',
                'value' => number_format($budgetUsagePercent, 0, ',', '.') . '%',
                'note' => 'Uso do orçamento do mês corrente',
                'eyebrowClass' => 'text-amber-600 dark:text-amber-300',
                'iconClass' => 'bg-gradient-to-br from-amber-500 to-orange-600',
                'icon' => 'fas fa-bullseye',
            ],
            [
                'label' => 'Saúde do estoque',
                'value' => number_format($inventoryHealthPercent, 0, ',', '.') . '%',
                'note' => $produtosEstoqueBaixo . ' item(ns) em atenção',
                'eyebrowClass' => 'text-sky-600 dark:text-sky-300',
                'iconClass' => 'bg-gradient-to-br from-sky-500 to-indigo-600',
                'icon' => 'fas fa-box-open',
            ],
        ];

        $moduleCards = [
            [
                'title' => 'Vendas',
                'value' => $salesMonth,
                'subtitle' => 'pedidos no mês',
                'meta' => 'Ticket médio de R$ ' . number_format($ticketMedio, 2, ',', '.'),
                'icon' => 'fas fa-shopping-bag',
                'gradient' => 'from-fuchsia-500 to-purple-600',
                'route' => route('sales.index'),
            ],
            [
                'title' => 'Clientes',
                'value' => $totalClientes,
                'subtitle' => 'cadastros ativos',
                'meta' => $clientesNovosMes . ' novo(s) no mês',
                'icon' => 'fas fa-users',
                'gradient' => 'from-rose-500 to-pink-600',
                'route' => route('clients.index'),
            ],
            [
                'title' => 'Produtos',
                'value' => $produtosAtivos,
                'subtitle' => 'itens com estoque',
                'meta' => $produtosCadastrados . ' cadastrado(s) no total',
                'icon' => 'fas fa-box',
                'gradient' => 'from-sky-500 to-indigo-600',
                'route' => route('products.index'),
            ],
            [
                'title' => 'Invoices',
                'value' => $totalInvoices,
                'subtitle' => 'registros lançados',
                'meta' => 'R$ ' . number_format($invoicesProxVenc30Total, 2, ',', '.') . ' nos próximos 30 dias',
                'icon' => 'fas fa-file-invoice-dollar',
                'gradient' => 'from-blue-500 to-cyan-600',
                'route' => route('invoices.index'),
            ],
            [
                'title' => 'Bancos',
                'value' => $totalBancos,
                'subtitle' => 'origens financeiras',
                'meta' => 'Fluxo por banco monitorado',
                'icon' => 'fas fa-building-columns',
                'gradient' => 'from-slate-500 to-slate-700',
                'route' => route('banks.index'),
            ],
            [
                'title' => 'Cofrinhos',
                'value' => $totalCofrinhos,
                'subtitle' => 'objetivos ativos',
                'meta' => 'R$ ' . number_format($totalEconomizado, 2, ',', '.') . ' acumulados',
                'icon' => 'fas fa-piggy-bank',
                'gradient' => 'from-pink-500 to-rose-500',
                'route' => route('cofrinhos.index'),
            ],
            [
                'title' => 'Consórcios',
                'value' => $totalConsorciosAtivos,
                'subtitle' => 'grupos ativos',
                'meta' => $proximosSorteios . ' sorteio(s) em 30 dias',
                'icon' => 'fas fa-layer-group',
                'gradient' => 'from-teal-500 to-cyan-600',
                'route' => route('consortiums.index'),
            ],
            [
                'title' => 'Planejamento',
                'value' => $recorrentesAtivas,
                'subtitle' => 'recorrências ativas',
                'meta' => 'R$ ' . number_format($recorrentesProx30Total, 2, ',', '.') . ' nos próximos 30 dias',
                'icon' => 'fas fa-calendar-check',
                'gradient' => 'from-amber-500 to-orange-600',
                'route' => route('cashbook.index'),
            ],
        ];

        $priorityCards = [
            [
                'title' => 'Receber x pagar',
                'value' => number_format($receivableCoveragePercent, 0, ',', '.') . '%',
                'note' => 'Cobertura das entradas sobre as saídas próximas',
                'cardClass' => 'border-emerald-200/70 bg-emerald-50/70 dark:border-emerald-500/20 dark:bg-emerald-500/10',
                'titleClass' => 'text-emerald-800 dark:text-emerald-200',
                'icon' => 'fas fa-scale-balanced',
            ],
            [
                'title' => 'Parcelas vencidas',
                'value' => $parcelasVencidasCount,
                'note' => 'R$ ' . number_format($parcelasVencidasValor, 2, ',', '.') . ' em atraso',
                'cardClass' => 'border-rose-200/70 bg-rose-50/70 dark:border-rose-500/20 dark:bg-rose-500/10',
                'titleClass' => 'text-rose-800 dark:text-rose-200',
                'icon' => 'fas fa-triangle-exclamation',
            ],
            [
                'title' => 'Economia em cofrinhos',
                'value' => 'R$ ' . number_format($totalEconomizado, 2, ',', '.'),
                'note' => $totalCofrinhos . ' objetivo(s) ativo(s)',
                'cardClass' => 'border-pink-200/70 bg-pink-50/70 dark:border-pink-500/20 dark:bg-pink-500/10',
                'titleClass' => 'text-pink-800 dark:text-pink-200',
                'icon' => 'fas fa-piggy-bank',
            ],
            [
                'title' => 'Pagamentos consórcios',
                'value' => 'R$ ' . number_format($consorcioPagamentosPendentesTotal, 2, ',', '.'),
                'note' => $consorcioParticipantesAtivos . ' participante(s) ativos',
                'cardClass' => 'border-cyan-200/70 bg-cyan-50/70 dark:border-cyan-500/20 dark:bg-cyan-500/10',
                'titleClass' => 'text-cyan-800 dark:text-cyan-200',
                'icon' => 'fas fa-layer-group',
            ],
        ];
    @endphp

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(168,85,247,0.14),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.04),_transparent)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.28),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.18),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.55),_transparent)]"></div>
        <div class="absolute left-1/2 top-32 h-72 w-72 -translate-x-1/2 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute right-0 top-12 h-60 w-60 rounded-full bg-fuchsia-400/10 blur-3xl"></div>
    </div>

    <div class="dashboard-shell px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <section class="dashboard-section dashboard-hero-section space-y-4">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-6">
                <div class="xl:col-span-8 overflow-hidden rounded-[28px] border border-white/60 bg-white/85 shadow-[0_20px_80px_rgba(15,23,42,0.10)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="relative overflow-hidden px-5 py-6 sm:px-7 sm:py-7">
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(59,130,246,0.10),rgba(168,85,247,0.08),rgba(16,185,129,0.08))] dark:bg-[linear-gradient(135deg,rgba(59,130,246,0.18),rgba(168,85,247,0.14),rgba(16,185,129,0.12))]"></div>
                        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-3xl space-y-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/80 bg-indigo-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-200">
                                        Parte 1
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/75 px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                                        <i class="fas fa-bolt text-amber-500"></i>
                                        Centro de comando do app
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                                        <i class="fas fa-calendar-range"></i>
                                        {{ $periodLabel }}
                                    </span>
                                </div>

                                <div>
                                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                                        Dashboard executivo do FlowManager
                                    </h1>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">
                                        Uma visão consolidada de finanças, vendas, estoque, clientes, bancos, cofrinhos, consórcios e operação diária, com leitura clara em qualquer dispositivo.
                                    </p>
                                </div>

                                <div class="flex flex-col gap-3 rounded-[24px] border border-slate-200/80 bg-white/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60 sm:flex-row sm:items-end">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Filtro de período</p>
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Os indicadores mensais e os gráficos abaixo respondem ao mês e ao ano selecionados.</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 sm:w-auto sm:min-w-[280px]">
                                        <label class="block">
                                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Mês</span>
                                            <select wire:model.live="selectedMonth"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                                                @foreach ($monthOptions as $monthNumber => $monthName)
                                                    <option value="{{ $monthNumber }}">{{ $monthName }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Ano</span>
                                            <select wire:model.live="selectedYear"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-indigo-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                                                @foreach ($availableYears as $year)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="applyPeriodPreset('today')"
                                        class="rounded-full px-3 py-2 text-xs font-bold transition {{ $periodPreset === 'today' ? 'bg-slate-900 text-white shadow-lg dark:bg-white dark:text-slate-900' : 'bg-white/80 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/70 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                                        Hoje
                                    </button>
                                    <button type="button" wire:click="applyPeriodPreset('month')"
                                        class="rounded-full px-3 py-2 text-xs font-bold transition {{ $periodPreset === 'month' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white/80 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/70 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                                        Mes atual
                                    </button>
                                    <button type="button" wire:click="applyPeriodPreset('quarter')"
                                        class="rounded-full px-3 py-2 text-xs font-bold transition {{ $periodPreset === 'quarter' ? 'bg-fuchsia-600 text-white shadow-lg' : 'bg-white/80 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/70 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                                        Ultimos 3 meses
                                    </button>
                                    <button type="button" wire:click="applyPeriodPreset('year')"
                                        class="rounded-full px-3 py-2 text-xs font-bold transition {{ $periodPreset === 'year' ? 'bg-emerald-600 text-white shadow-lg' : 'bg-white/80 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/70 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                                        Ano atual
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                                    <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Receitas</p>
                                        <p class="mt-2 text-2xl font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($receitasPeriodo, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-sky-200/70 bg-sky-50/80 p-4 dark:border-sky-500/20 dark:bg-sky-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Saldo atual</p>
                                        <p class="mt-2 text-2xl font-black text-sky-700 dark:text-sky-200">R$ {{ number_format($saldoCaixa, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-indigo-200/70 bg-indigo-50/80 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-700 dark:text-indigo-300">Alertas</p>
                                        <p class="mt-2 text-2xl font-black text-indigo-700 dark:text-indigo-200">{{ $alertsCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-cyan-200/70 bg-cyan-50/80 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Cobertura</p>
                                        <p class="mt-2 text-2xl font-black text-cyan-700 dark:text-cyan-200">{{ number_format($receivableCoveragePercent, 0, ',', '.') }}%</p>
                                    </div>
                                    <div class="rounded-2xl border border-violet-200/70 bg-violet-50/80 p-4 dark:border-violet-500/20 dark:bg-violet-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-violet-700 dark:text-violet-300">Histórico</p>
                                        <p class="mt-2 text-2xl font-black text-violet-700 dark:text-violet-200">{{ $activitiesCount }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[320px]">
                                <a href="{{ route('cashbook.index') }}"
                                    class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300">
                                    <span class="flex items-center gap-3"><i class="fas fa-wallet text-indigo-500"></i>Caixa</span>
                                    <i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i>
                                </a>
                                <a href="{{ route('sales.index') }}"
                                    class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-fuchsia-300 hover:text-fuchsia-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-fuchsia-500/40 dark:hover:text-fuchsia-300">
                                    <span class="flex items-center gap-3"><i class="fas fa-cart-shopping text-fuchsia-500"></i>Vendas</span>
                                    <i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i>
                                </a>
                                <a href="{{ route('products.index') }}"
                                    class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-300 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-sky-500/40 dark:hover:text-sky-300">
                                    <span class="flex items-center gap-3"><i class="fas fa-boxes-stacked text-sky-500"></i>Produtos</span>
                                    <i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i>
                                </a>
                                <a href="{{ route('clients.index') }}"
                                    class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:text-emerald-300">
                                    <span class="flex items-center gap-3"><i class="fas fa-users text-emerald-500"></i>Clientes</span>
                                    <i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4">
                    @foreach ($executiveTiles as $tile)
                        <div class="rounded-[24px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_12px_40px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.24em] {{ $tile['eyebrowClass'] }}">{{ $tile['label'] }}</p>
                                    <p class="mt-3 text-2xl font-black text-slate-900 dark:text-white">{{ $tile['value'] }}</p>
                                    <p class="mt-2 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ $tile['note'] }}</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-lg {{ $tile['iconClass'] }}">
                                    <i class="{{ $tile['icon'] }} text-lg"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @include('livewire.dashboard.partials.kpis-grid')
        </section>

        <section class="dashboard-section dashboard-finance-section space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-sky-200/80 bg-sky-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200">
                        Parte 2
                    </span>
                    <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Inteligência financeira</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fluxo de caixa, distribuição de despesas, custos, cobertura de recebíveis e uso do orçamento para {{ $periodLabel }}.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">Saldo atual: R$ {{ number_format($saldoCaixa, 2, ',', '.') }}</span>
                    <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">A receber: R$ {{ number_format($contasReceberPendentes, 2, ',', '.') }}</span>
                    <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">A pagar: R$ {{ number_format($contasPagarPendentes, 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-7 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex flex-col gap-4 border-b border-slate-200/80 px-5 py-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-600 text-white shadow-lg">
                                <i class="fas fa-wave-square text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Fluxo de caixa anual</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Evolução das entradas e saídas até {{ $periodLabel }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:min-w-[250px]">
                            <div class="rounded-2xl bg-emerald-50/80 px-4 py-3 dark:bg-emerald-500/10">
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Receitas</p>
                                <p class="mt-2 text-lg font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($receitasPeriodo, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-rose-50/80 px-4 py-3 dark:bg-rose-500/10">
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Despesas</p>
                                <p class="mt-2 text-lg font-black text-rose-700 dark:text-rose-200">R$ {{ number_format($despesasPeriodo, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="cashflowMonthlyChart" class="dashboard-chart dashboard-chart-lg"></div>
                    </div>
                </div>

                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-white shadow-lg">
                                <i class="fas fa-chart-pie text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pulso financeiro</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Orçamento, cobertura de caixa e equilíbrio operacional</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-5 lg:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div id="budgetHealthChart" class="dashboard-chart dashboard-chart-md"></div>
                        <div class="space-y-3">
                            <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    <span>Uso do orçamento</span>
                                    <span>{{ number_format($budgetUsagePercent, 0, ',', '.') }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-orange-500 {{ $budgetUsageWidthClass }}"></div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    <span>Cobertura de recebíveis</span>
                                    <span>{{ number_format($receivableCoveragePercent, 0, ',', '.') }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 {{ $receivableCoverageWidthClass }}"></div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    <span>Saúde do estoque</span>
                                    <span>{{ number_format($inventoryHealthPercent, 0, ',', '.') }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 {{ $inventoryHealthWidthClass }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-12 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-white shadow-lg">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Comparativo de período</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Leitura direta entre período atual, período anterior e mesmo recorte do ano passado.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">Atual: {{ $periodLabel }}</span>
                                <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">Receita atual: R$ {{ number_format($receitasPeriodo, 0, ',', '.') }}</span>
                                <span class="rounded-full bg-white/80 px-3 py-1.5 text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:ring-slate-700">Despesa atual: R$ {{ number_format($despesasPeriodo, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="periodComparisonChart" class="dashboard-chart dashboard-chart-md"></div>
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg">
                                <i class="fas fa-chart-pie text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Despesas por categoria</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Top 10 categorias de {{ $periodLabel }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="expensesByCategoryChart" class="dashboard-chart dashboard-chart-md"></div>
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                                <i class="fas fa-credit-card text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Invoices por banco</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Comportamento mensal por origem financeira até {{ $periodLabel }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="invoicesByBankChart" class="dashboard-chart dashboard-chart-md"></div>
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-lg">
                                <i class="fas fa-chart-column text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Vendas versus custos</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Relação entre faturamento, estoque e custo vendido em {{ $periodLabel }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="salesVsCostsChart" class="dashboard-chart dashboard-chart-md"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section dashboard-commercial-section space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-fuchsia-200/80 bg-fuchsia-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-fuchsia-700 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10 dark:text-fuchsia-200">
                    Parte 3
                </span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Operação comercial e planejamento</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Blocos com leitura rápida para vendas, clientes, estoque, orçamento, recorrências e saúde operacional.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-purple-600 text-white shadow-lg">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Comercial</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Conversão, faturamento e cobrança</p>
                            </div>
                        </div>
                        <a href="{{ route('sales.index') }}" class="text-xs font-bold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Vendas no mês</p>
                                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $salesMonth }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Produtos vendidos</p>
                                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $produtosVendidosMes }}</p>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/70 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Ticket médio</span>
                                <span class="text-lg font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-rose-200/70 bg-rose-50/70 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-rose-800 dark:text-rose-200">Parcelas vencidas</span>
                                <span class="text-right text-sm font-black text-rose-700 dark:text-rose-200">{{ $parcelasVencidasCount }}<br>R$ {{ number_format($parcelasVencidasValor, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-indigo-50/80 p-4 dark:bg-indigo-500/10">
                                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Faturamento total</p>
                                <p class="mt-2 text-lg font-black text-indigo-700 dark:text-indigo-200">R$ {{ number_format($totalFaturamento, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-amber-50/80 p-4 dark:bg-amber-500/10">
                                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Crescimento</p>
                                <p class="mt-2 text-lg font-black {{ $taxaCrescimento >= 0 ? 'text-amber-700 dark:text-amber-200' : 'text-rose-700 dark:text-rose-200' }}">{{ number_format($taxaCrescimento, 1, ',', '.') }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-lg">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes e estoque</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Base ativa, risco de inadimplência e abastecimento</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            <a href="{{ route('clients.index') }}">Clientes</a>
                            <span class="text-slate-300 dark:text-slate-700">|</span>
                            <a href="{{ route('products.index') }}">Produtos</a>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Clientes ativos</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $totalClientes }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Produtos ativos</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $produtosAtivos }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-200/70 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Estoque baixo</p>
                            <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-200">{{ $produtosEstoqueBaixo }}</p>
                        </div>
                        <div class="rounded-2xl border border-rose-200/70 bg-rose-50/70 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                            <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">Clientes com pendência</p>
                            <p class="mt-2 text-2xl font-black text-rose-700 dark:text-rose-200">{{ $clientesInadimplentes }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl bg-sky-50/80 p-4 dark:bg-sky-500/10">
                            <div class="mb-2 flex items-center justify-between text-sm font-semibold text-sky-800 dark:text-sky-200">
                                <span>Saúde do estoque</span>
                                <span>{{ number_format($inventoryHealthPercent, 0, ',', '.') }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-sky-100 dark:bg-slate-800">
                                <div class="h-2 rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 {{ $inventoryHealthWidthClass }}"></div>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-emerald-50/80 p-4 dark:bg-emerald-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">A receber pendente</span>
                                <span class="text-lg font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($contasReceberPendentes, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-lg">
                                <i class="fas fa-compass-drafting"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Planejamento</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Orçamento, recorrências e integridade dos uploads</p>
                            </div>
                        </div>
                        <a href="{{ route('cashbook.index') }}" class="text-xs font-bold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-teal-50/80 p-4 dark:bg-teal-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-teal-800 dark:text-teal-200">Orçado no mês</span>
                                <span class="text-lg font-black text-teal-700 dark:text-teal-200">R$ {{ number_format($orcamentoMesTotal, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-rose-50/80 p-4 dark:bg-rose-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-rose-800 dark:text-rose-200">Gasto no mês</span>
                                <span class="text-lg font-black text-rose-700 dark:text-rose-200">R$ {{ number_format($orcamentoMesUsado, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-900/70">
                            <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <span>Consumo do orçamento</span>
                                <span>{{ number_format($budgetUsagePercent, 0, ',', '.') }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-2 rounded-full bg-gradient-to-r from-teal-400 to-cyan-500 {{ $budgetUsageWidthClass }}"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-amber-50/80 p-4 dark:bg-amber-500/10">
                                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Recorrências ativas</p>
                                <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-200">{{ $recorrentesAtivas }}</p>
                            </div>
                            <div class="rounded-2xl bg-indigo-50/80 p-4 dark:bg-indigo-500/10">
                                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Próximos 30 dias</p>
                                <p class="mt-2 text-lg font-black text-indigo-700 dark:text-indigo-200">R$ {{ number_format($recorrentesProx30Total, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                            <div class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                                <i class="fas fa-heart-pulse text-emerald-500"></i>
                                Saúde dos uploads
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-xl bg-white/90 p-3 dark:bg-slate-950/60">
                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Cashbook</div>
                                    <div class="mt-2 text-xs font-bold text-slate-700 dark:text-slate-200">{{ $lastUploads['cashbook']->status ?? '—' }}</div>
                                </div>
                                <div class="rounded-xl bg-white/90 p-3 dark:bg-slate-950/60">
                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Produtos</div>
                                    <div class="mt-2 text-xs font-bold text-slate-700 dark:text-slate-200">{{ $lastUploads['products']->status ?? '—' }}</div>
                                </div>
                                <div class="rounded-xl bg-white/90 p-3 dark:bg-slate-950/60">
                                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Invoices</div>
                                    <div class="mt-2 text-xs font-bold text-slate-700 dark:text-slate-200">{{ $lastUploads['invoices']->status ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section dashboard-coverage-section space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200/80 bg-cyan-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-200">
                    Parte 4
                </span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Cobertura do app inteiro</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Uma leitura transversal dos módulos já implementados no FlowManager, com foco em volume, ocupação e equilíbrio entre áreas.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-7 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Módulos estratégicos</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Cada cartão aponta o estado resumido de uma área chave do sistema.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ count($moduleCards) }} módulos</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($moduleCards as $module)
                            <a href="{{ $module['route'] }}"
                                class="group rounded-[24px] border border-slate-200/80 bg-white/80 p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/70">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $module['gradient'] }} text-white shadow-lg">
                                                <i class="{{ $module['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $module['title'] }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $module['subtitle'] }}</p>
                                            </div>
                                        </div>
                                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $module['value'] }}</p>
                                        <p class="mt-2 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ $module['meta'] }}</p>
                                    </div>
                                    <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400 transition group-hover:text-indigo-500"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="xl:col-span-5 grid grid-cols-1 gap-4">
                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <div class="border-b border-slate-200/80 px-5 py-5 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-lg">
                                    <i class="fas fa-chart-radar"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Mapa de presença</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Distribuição de volume entre os principais módulos</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div id="operationsRadarChart" class="dashboard-chart dashboard-chart-md"></div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                                <i class="fas fa-shield-heart"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Saúde sistêmica</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Pontos de estabilidade operacional e continuidade</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-2xl bg-emerald-50/80 p-4 dark:bg-emerald-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Lucro líquido do período</span>
                                    <span class="text-lg font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($lucroLiquido, 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-indigo-50/80 p-4 dark:bg-indigo-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">Contemplações registradas</span>
                                    <span class="text-lg font-black text-indigo-700 dark:text-indigo-200">{{ $consorcioContemplacoesTotal }}</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-cyan-50/80 p-4 dark:bg-cyan-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-cyan-800 dark:text-cyan-200">Bancos integrados</span>
                                    <span class="text-lg font-black text-cyan-700 dark:text-cyan-200">{{ $totalBancos }}</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-amber-50/80 p-4 dark:bg-amber-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-amber-800 dark:text-amber-200">Uploads rastreados</span>
                                    <span class="text-lg font-black text-amber-700 dark:text-amber-200">3</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section dashboard-history-section space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200/80 bg-amber-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                    Parte 5
                </span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Alertas, prioridades e histórico ampliado</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Acompanhamento contínuo das ações recentes do sistema, com prioridade operacional e contexto por módulo.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 grid grid-cols-1 gap-4">
                    @if ($alertsCount > 0)
                        @include('livewire.dashboard.partials.alertas')
                    @else
                        <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-6 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                                    <i class="fas fa-badge-check"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Sem alertas críticos</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">O painel não identificou urgências neste momento.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Radar do dia</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Fatores que merecem monitoramento contínuo</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($priorityCards as $priority)
                                <div class="rounded-2xl border p-4 {{ $priority['cardClass'] }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2 text-sm font-bold {{ $priority['titleClass'] }}">
                                                <i class="{{ $priority['icon'] }}"></i>
                                                {{ $priority['title'] }}
                                            </div>
                                            <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ $priority['value'] }}</p>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $priority['note'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($historyBreakdown->isNotEmpty())
                            <div class="mt-5 border-t border-slate-200/80 pt-4 dark:border-slate-800">
                                <p class="mb-3 text-xs font-black uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Módulos mais presentes no histórico</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($historyBreakdown as $module => $count)
                                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 dark:bg-slate-900 dark:text-slate-300">{{ $module }} · {{ $count }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="xl:col-span-8">
                    @include('livewire.dashboard.partials.atividades')
                </div>
            </div>
        </section>

        @include('livewire.dashboard.partials.fab-menu')
    </div>

    <style>
        .dashboard-shell {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            margin-inline: auto;
        }

        .dashboard-index-page,
        .dashboard-index-page * {
            box-sizing: border-box;
        }

        .dashboard-index-page {
            overflow-x: clip;
        }

        .dashboard-index-page section,
        .dashboard-index-page [class*="grid"] > * {
            min-width: 0;
        }

        .dashboard-index-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.82)) !important;
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .dark .dashboard-index-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.78)) !important;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.42), inset 0 1px 0 rgba(148, 163, 184, 0.08);
        }

        .dashboard-index-page [class*="bg-slate-50/80"],
        .dashboard-index-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(239, 246, 255, 0.88), rgba(238, 242, 255, 0.78)) !important;
            border: 1px solid rgba(125, 211, 252, 0.18);
        }

        .dark .dashboard-index-page [class*="bg-slate-50/80"],
        .dark .dashboard-index-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.8)) !important;
            border-color: rgba(71, 85, 105, 0.38);
        }

        .dashboard-index-page .dashboard-kpi-grid > div {
            padding: 0.9rem !important;
        }

        .dashboard-index-page .dashboard-kpi-grid .text-3xl,
        .dashboard-index-page .text-3xl.font-black {
            font-size: clamp(1.45rem, 1vw, 1.95rem) !important;
        }

        .dashboard-index-page .text-2xl.font-black {
            font-size: clamp(1.1rem, 0.78vw, 1.35rem) !important;
        }

        .dashboard-index-page h2.text-2xl.font-black {
            font-size: clamp(1.35rem, 0.92vw, 1.7rem) !important;
        }

        .dashboard-index-page h1.text-3xl.font-black {
            font-size: clamp(1.8rem, 1.5vw, 2.55rem) !important;
        }

        .dashboard-index-page .rounded-\[28px\],
        .dashboard-index-page .rounded-\[24px\] {
            border-radius: 22px !important;
        }

        .dashboard-index-page .p-5,
        .dashboard-index-page .p-6 {
            padding: 1rem !important;
        }

        .dashboard-index-page .px-5,
        .dashboard-index-page .py-5 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .dashboard-index-page .space-y-6 > section {
            margin-bottom: 0.9rem !important;
        }

        .dashboard-chart {
            width: 100%;
            border-radius: 24px;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 38%),
                radial-gradient(circle at bottom right, rgba(236, 72, 153, 0.1), transparent 34%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.75), rgba(248, 250, 252, 0.38));
            border: 1px solid rgba(148, 163, 184, 0.14);
            padding: 0.35rem;
        }

        .dark .dashboard-chart {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 38%),
                radial-gradient(circle at bottom right, rgba(168, 85, 247, 0.18), transparent 34%),
                linear-gradient(180deg, rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.38));
            border-color: rgba(71, 85, 105, 0.32);
        }

        .dashboard-index-page select {
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #64748b 50%), linear-gradient(135deg, #64748b 50%, transparent 50%);
            background-position: calc(100% - 18px) calc(50% - 3px), calc(100% - 12px) calc(50% - 3px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 2.75rem;
        }

        .dark .dashboard-index-page select {
            background-image: linear-gradient(45deg, transparent 50%, #cbd5e1 50%), linear-gradient(135deg, #cbd5e1 50%, transparent 50%);
        }

        .dashboard-chart-lg {
            min-height: 270px;
        }

        .dashboard-chart-md {
            min-height: 220px;
        }

        @media (max-width: 640px) {
            .dashboard-chart-lg {
                min-height: 260px;
            }

            .dashboard-chart-md {
                min-height: 240px;
            }
        }

        .apexcharts-legend-text {
            color: #0f172a !important;
        }

        .dark .apexcharts-legend-text,
        .apexcharts-theme-dark .apexcharts-legend-text {
            color: #e5e7eb !important;
        }
    </style>

    <script type="application/json" id="dashboard-chart-payload">@json($dashboardChartPayload)</script>

    <script>
        (() => {
            const payloadElement = document.getElementById('dashboard-chart-payload');
            const chartData = payloadElement ? JSON.parse(payloadElement.textContent) : {};

            const chartRegistry = window.dashboardIndexChartRegistry || {};
            window.dashboardIndexChartRegistry = chartRegistry;

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
                        toolbar: {
                            show: false,
                        },
                        animations: {
                            enabled: true,
                            speed: 650,
                        },
                        fontFamily: 'inherit',
                        foreColor: labelColor,
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                    },
                    theme: {
                        mode: darkMode ? 'dark' : 'light',
                    },
                };

                mountChart('#cashflowMonthlyChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Receitas',
                            data: (chartData.cashflowMonthly || []).map((row) => Number(row.receitas || 0)),
                        },
                        {
                            name: 'Despesas',
                            data: (chartData.cashflowMonthly || []).map((row) => Number(row.despesas || 0)),
                        },
                    ],
                    chart: {
                        ...baseChart.chart,
                        type: 'area',
                        height: largeHeight,
                    },
                    colors: ['#10b981', '#f43f5e'],
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.08,
                            stops: [0, 90, 100],
                        },
                    },
                    xaxis: {
                        categories: (chartData.cashflowMonthly || []).map((row) => row.label),
                        labels: {
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactMoney(value),
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => money(value),
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                    },
                });

                mountChart('#budgetHealthChart', {
                    ...baseChart,
                    series: [
                        chartData.budgetUsagePercent,
                        chartData.receivableCoveragePercent,
                        chartData.inventoryHealthPercent,
                    ],
                    chart: {
                        ...baseChart.chart,
                        type: 'radialBar',
                        height: mediumHeight,
                    },
                    colors: ['#f59e0b', '#10b981', '#3b82f6'],
                    labels: ['Orçamento', 'Recebíveis', 'Estoque'],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '32%',
                            },
                            track: {
                                background: darkMode ? '#1e293b' : '#e2e8f0',
                            },
                            dataLabels: {
                                name: {
                                    fontSize: '13px',
                                },
                                value: {
                                    formatter: (value) => Number(value).toFixed(0) + '%',
                                    fontSize: '22px',
                                    fontWeight: 700,
                                },
                                total: {
                                    show: true,
                                    label: 'Média',
                                    formatter: () => {
                                        const average = (chartData.budgetUsagePercent + chartData.receivableCoveragePercent + chartData.inventoryHealthPercent) / 3;
                                        return Number(average).toFixed(0) + '%';
                                    },
                                },
                            },
                        },
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                    },
                });

                mountChart('#expensesByCategoryChart', {
                    ...baseChart,
                    series: (chartData.expensesByCategory || []).map((row) => Number(row.total || 0)),
                    labels: (chartData.expensesByCategory || []).map((row) => row.label),
                    chart: {
                        ...baseChart.chart,
                        type: 'donut',
                        height: mediumHeight,
                    },
                    colors: ['#f97316', '#ef4444', '#f59e0b', '#ec4899', '#8b5cf6', '#6366f1', '#0ea5e9', '#14b8a6', '#22c55e', '#84cc16'],
                    legend: {
                        position: 'bottom',
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%',
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => money(value),
                        },
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: (value) => Number(value).toFixed(0) + '%',
                    },
                });

                mountChart('#invoicesByBankChart', {
                    ...baseChart,
                    series: chartData.gastosInvoicePorBanco || [],
                    chart: {
                        ...baseChart.chart,
                        type: 'line',
                        height: mediumHeight,
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                    },
                    markers: {
                        size: 4,
                    },
                    xaxis: {
                        categories: chartData.monthLabels || [],
                        labels: {
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactMoney(value),
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => money(value),
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                    },
                });

                mountChart('#salesVsCostsChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Vendas',
                            data: [chartData.salesVsCosts[0]],
                        },
                        {
                            name: 'Custo estoque',
                            data: [chartData.salesVsCosts[1]],
                        },
                        {
                            name: 'Custo vendidos',
                            data: [chartData.salesVsCosts[2]],
                        },
                    ],
                    chart: {
                        ...baseChart.chart,
                        type: 'bar',
                        height: mediumHeight,
                        stacked: true,
                    },
                    colors: ['#8b5cf6', '#3b82f6', '#f59e0b'],
                    plotOptions: {
                        bar: {
                            borderRadius: 12,
                            columnWidth: '48%',
                        },
                    },
                    xaxis: {
                        categories: ['Comparativo'],
                        labels: {
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactMoney(value),
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => money(value),
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                    },
                });

                mountChart('#periodComparisonChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Vendas',
                            data: ((chartData.periodComparison || {}).sales || []).map((value) => Number(value || 0)),
                        },
                        {
                            name: 'Receitas',
                            data: ((chartData.periodComparison || {}).income || []).map((value) => Number(value || 0)),
                        },
                        {
                            name: 'Despesas',
                            data: ((chartData.periodComparison || {}).expenses || []).map((value) => Number(value || 0)),
                        },
                    ],
                    chart: {
                        ...baseChart.chart,
                        type: 'bar',
                        height: mediumHeight,
                    },
                    colors: ['#06b6d4', '#10b981', '#f97316'],
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            columnWidth: '42%',
                        },
                    },
                    xaxis: {
                        categories: ((chartData.periodComparison || {}).labels || []),
                        labels: {
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactMoney(value),
                            style: {
                                colors: labelColor,
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => money(value),
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                    },
                });

                mountChart('#operationsRadarChart', {
                    ...baseChart,
                    series: [
                        {
                            name: 'Volume',
                            data: chartData.modulePresence,
                        },
                    ],
                    chart: {
                        ...baseChart.chart,
                        type: 'radar',
                        height: mediumHeight,
                    },
                    labels: ['Vendas', 'Clientes', 'Produtos', 'Invoices', 'Cofrinhos', 'Consórcios'],
                    colors: ['#06b6d4'],
                    stroke: {
                        width: 3,
                    },
                    fill: {
                        opacity: 0.24,
                    },
                    markers: {
                        size: 4,
                    },
                    yaxis: {
                        show: false,
                    },
                    xaxis: {
                        labels: {
                            style: {
                                colors: Array(6).fill(labelColor),
                            },
                        },
                    },
                    tooltip: {
                        theme: tooltipTheme,
                        y: {
                            formatter: (value) => Number(value).toLocaleString('pt-BR'),
                        },
                    },
                });
            };

            let resizeTimer;
            let themeObserver;

            if (!window.dashboardIndexChartsBound) {
                document.addEventListener('livewire:navigated', renderCharts);
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(renderCharts, 180);
                });

                themeObserver = new MutationObserver(() => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(renderCharts, 120);
                });

                themeObserver.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });

                window.dashboardIndexChartsBound = true;
            }

            document.addEventListener('DOMContentLoaded', renderCharts);
            setTimeout(renderCharts, 0);
        })();
    </script>
</div>