@php
    $banksExportParams = ['year' => $ano, 'month' => $mes];
    $bankQuickMetrics = [
        [
            'label' => 'Bancos com movimento',
            'value' => number_format($activeBanksMonth ?? 0, 0, ',', '.'),
            'icon' => 'fas fa-building-columns',
            'surface' => 'border-indigo-200/80 bg-indigo-50/80 dark:border-indigo-500/20 dark:bg-indigo-500/10',
            'text' => 'text-indigo-700 dark:text-indigo-200',
        ],
        [
            'label' => 'Ciclo médio',
            'value' => 'R$ ' . number_format($avgCycleAmount ?? 0, 2, ',', '.'),
            'icon' => 'fas fa-arrows-rotate',
            'surface' => 'border-cyan-200/80 bg-cyan-50/80 dark:border-cyan-500/20 dark:bg-cyan-500/10',
            'text' => 'text-cyan-700 dark:text-cyan-200',
        ],
        [
            'label' => 'Fechamento médio',
            'value' => number_format($avgDaysToClose ?? 0, 1, ',', '.') . ' dias',
            'icon' => 'fas fa-hourglass-half',
            'surface' => 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10',
            'text' => 'text-amber-700 dark:text-amber-200',
        ],
        [
            'label' => 'Saída anual',
            'value' => 'R$ ' . number_format($totalSaidasBancos ?? 0, 2, ',', '.'),
            'icon' => 'fas fa-chart-line',
            'surface' => 'border-fuchsia-200/80 bg-fuchsia-50/80 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10',
            'text' => 'text-fuchsia-700 dark:text-fuchsia-200',
        ],
    ];
@endphp

<div class="dashboard-banks-page app-viewport-fit w-full min-h-screen bg-[radial-gradient(circle_at_top,#132c56_0%,#07101d_36%,#030712_100%)] text-white mobile-393-base relative overflow-x-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-banks-ultrawide.css') }}">

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.20),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(34,211,238,0.16),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.06),_transparent)]"></div>
        <div class="absolute left-10 top-14 h-56 w-56 rounded-full bg-indigo-400/10 blur-3xl"></div>
        <div class="absolute right-10 top-10 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute left-1/2 top-24 h-72 w-72 -translate-x-1/2 rounded-full bg-fuchsia-400/8 blur-3xl"></div>
    </div>

    <div class="mx-auto w-full max-w-[1780px] px-4 py-4 sm:px-5 lg:px-6 xl:px-8">
        <section class="banks-hero rounded-[30px] border border-indigo-400/20 bg-slate-900/75 p-5 shadow-[0_30px_120px_rgba(2,8,23,0.55)] backdrop-blur-xl lg:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-400 via-sky-500 to-cyan-400 shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-building-columns text-2xl text-white"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-indigo-200">Parte 1</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-100"><i class="fas fa-credit-card"></i>Bancos e faturas</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-100"><i class="fas fa-circle text-[8px]"></i>{{ $periodLabel }}</span>
                        </div>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-white lg:text-4xl">Leitura de ciclo, volume e concentração por banco</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-300 lg:text-base">Veja quanto cada banco está puxando do caixa, como o ciclo de fatura está fechando e quais categorias estão concentrando a despesa do período.</p>
                    </div>
                </div>

                <div class="grid w-full gap-3 md:grid-cols-3 xl:w-[720px]">
                    <div>
                        <label for="ano-banks" class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Ano</label>
                        <select id="ano-banks" wire:model.live="ano" class="w-full rounded-2xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="mes-banks" class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Mês</label>
                        <select id="mes-banks" wire:model.live="mes" class="w-full rounded-2xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-400">
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}">{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="banks-mini-card rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Recorte</div>
                        <div class="mt-2 text-sm font-bold text-white">{{ $periodLabel }}</div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ route('dashboard.cashbook') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-cyan-300 hover:text-cyan-200"><i class="fas fa-arrow-left"></i>cashbook</a>
                            <a href="{{ route('reports.dashboard-banks.export', array_merge($banksExportParams, ['format' => 'xlsx'])) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-300 hover:text-emerald-200"><i class="fas fa-file-excel"></i>excel</a>
                            <a href="{{ route('reports.dashboard-banks.export', array_merge($banksExportParams, ['format' => 'pdf'])) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-rose-300 hover:text-rose-200"><i class="fas fa-file-pdf"></i>pdf</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($bankQuickMetrics as $metric)
                    <div class="banks-mini-card rounded-2xl border p-4 shadow-[0_12px_40px_rgba(2,8,23,0.22)] {{ $metric['surface'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] {{ $metric['text'] }}">{{ $metric['label'] }}</p>
                                <p class="mt-3 text-2xl font-black text-white">{{ $metric['value'] }}</p>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-lg {{ $metric['text'] }}">
                                <i class="{{ $metric['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="banks-kpi rounded-[26px] border border-indigo-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Bancos ativos</span><i class="fas fa-building-columns text-indigo-300"></i></div>
                <div class="mt-3 text-3xl font-black text-white">{{ $totalBancos }}</div>
                <div class="mt-2 text-sm text-slate-400">visão consolidada de cartões e contas</div>
            </article>
            <article class="banks-kpi rounded-[26px] border border-rose-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Faturas do mês</span><i class="fas fa-credit-card text-rose-300"></i></div>
                <div class="mt-3 text-3xl font-black text-rose-200">R$ {{ number_format($monthTotal, 2, ',', '.') }}</div>
                <div class="mt-2 text-sm text-slate-400">{{ $monthCount }} lançamentos no período</div>
            </article>
            <article class="banks-kpi rounded-[26px] border border-amber-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Ticket médio</span><i class="fas fa-receipt text-amber-300"></i></div>
                <div class="mt-3 text-3xl font-black text-amber-100">R$ {{ number_format($avgMonth, 2, ',', '.') }}</div>
                <div class="mt-2 text-sm text-slate-400">média por invoice do mês</div>
            </article>
            <article class="banks-kpi rounded-[26px] border border-cyan-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Carga anual</span><i class="fas fa-chart-line text-cyan-300"></i></div>
                <div class="mt-3 text-3xl font-black text-cyan-100">R$ {{ number_format($totalInvoicesBancos, 2, ',', '.') }}</div>
                <div class="mt-2 text-sm text-slate-400">saída consolidada de invoices do ano</div>
            </article>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="banks-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Banco líder</p>
                        <p class="mt-2 text-lg font-black text-white">{{ data_get($topBankSummary, 'nome', 'Sem destaque') }}</p>
                    </div>
                    <i class="fas fa-trophy text-xl text-amber-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">{{ $topBankSummary ? 'R$ ' . number_format(data_get($topBankSummary, 'month_total', 0), 2, ',', '.') . ' no mês' : 'sem volume relevante neste período' }}</p>
            </article>

            <article class="banks-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Ritmo diário</p>
                        <p class="mt-2 text-2xl font-black text-cyan-100">R$ {{ number_format($monthDailyAverage, 2, ',', '.') }}</p>
                    </div>
                    <i class="fas fa-wave-square text-xl text-cyan-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">média diária de invoices no mês</p>
            </article>

            <article class="banks-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Categoria dominante</p>
                        <p class="mt-2 text-lg font-black text-white">{{ data_get($topCategorySummary, 'label', 'Sem destaque') }}</p>
                    </div>
                    <i class="fas fa-layer-group text-xl text-fuchsia-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">{{ $topCategorySummary ? 'R$ ' . number_format(data_get($topCategorySummary, 'value', 0), 2, ',', '.') . ' no mês' : 'sem concentração relevante' }}</p>
            </article>

            <article class="banks-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Saúde operacional</p>
                        <p class="mt-2 text-2xl font-black text-emerald-300">{{ number_format($uploadSuccessAverage, 1, ',', '.') }}%</p>
                    </div>
                    <i class="fas fa-cloud-arrow-up text-xl text-emerald-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">sucesso médio dos uploads recentes</p>
            </article>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="banks-panel xl:col-span-7 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-300">Tendência</p>
                        <h2 class="text-xl font-black text-white">Últimos 7 meses de invoices</h2>
                    </div>
                    <span class="text-xs text-slate-400">rolling window</span>
                </div>
                <div id="banksTrendChart" class="banks-chart-lg h-[340px]"></div>
            </section>

            <section class="banks-panel xl:col-span-5 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-300">Ranking</p>
                        <h2 class="text-xl font-black text-white">Top bancos do mês</h2>
                    </div>
                    <span class="text-xs text-slate-400">por valor</span>
                </div>
                <div id="banksRankingChart" class="banks-chart-lg h-[340px]"></div>
            </section>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="banks-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-300">Categorias</p>
                        <h2 class="text-xl font-black text-white">Distribuição do mês</h2>
                    </div>
                </div>
                <div id="banksCategoryChart" class="banks-chart-md h-[320px]"></div>
            </section>

            <section class="banks-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-300">Ciclo</p>
                        <h2 class="text-xl font-black text-white">Carga por fechamento</h2>
                    </div>
                </div>
                <div id="banksCycleChart" class="banks-chart-md h-[320px]"></div>
            </section>

            <section class="banks-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-fuchsia-300">Uploads</p>
                        <h2 class="text-xl font-black text-white">Histórico recente</h2>
                    </div>
                    <span class="text-xs text-slate-400">últimos 6</span>
                </div>
                <div class="space-y-3">
                    @forelse($recentUploads as $upload)
                        <div class="banks-list-item rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-white">{{ $upload['bank'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $upload['filename'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold uppercase tracking-[0.12em] {{ $upload['status'] === 'failed' ? 'text-rose-300' : 'text-cyan-300' }}">{{ $upload['status'] }}</div>
                                    <div class="mt-1 text-sm text-slate-300">R$ {{ number_format($upload['total_value'], 2, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                <span>{{ $upload['created_at'] }}</span>
                                <span>sucesso {{ number_format($upload['success_rate'], 1, ',', '.') }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Sem uploads recentes de invoices.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="banks-panel mt-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-300">Bancos detalhados</p>
                    <h2 class="text-xl font-black text-white">Cartões e contas com métricas operacionais</h2>
                </div>
                <span class="text-xs text-slate-400">{{ count($bancosInfo) }} cartões/contas</span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach($bancosInfo as $banco)
                    <a href="{{ $banco['link'] }}" class="group banks-bank-card rounded-[26px] border border-slate-800 bg-slate-950/70 p-5 transition hover:border-indigo-400/30 hover:bg-slate-950">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-black text-white">{{ $banco['nome'] }}</h3>
                                <p class="mt-1 text-sm text-slate-400">{{ $banco['descricao'] ?: 'Sem descrição cadastrada.' }}</p>
                            </div>
                            <span class="rounded-full border border-slate-800 px-3 py-1 text-[11px] uppercase tracking-[0.14em] text-slate-400">{{ $banco['cycle_start'] }}-{{ $banco['cycle_end'] }}</span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="banks-mini-card rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.14em] text-slate-500">Mês</div>
                                <div class="mt-2 text-xl font-black text-rose-200">R$ {{ number_format($banco['month_total'], 2, ',', '.') }}</div>
                            </div>
                            <div class="banks-mini-card rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.14em] text-slate-500">Ciclo atual</div>
                                <div class="mt-2 text-xl font-black text-cyan-100">R$ {{ number_format($banco['cycle_total'], 2, ',', '.') }}</div>
                            </div>
                            <div class="banks-mini-card rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.14em] text-slate-500">Invoices</div>
                                <div class="mt-2 text-xl font-black text-white">{{ $banco['qtd_invoices'] }}</div>
                            </div>
                            <div class="banks-mini-card rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3">
                                <div class="text-xs uppercase tracking-[0.14em] text-slate-500">Ticket médio</div>
                                <div class="mt-2 text-xl font-black text-white">R$ {{ number_format($banco['media_invoices'], 2, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-slate-300">
                            <div class="flex items-center justify-between"><span>Maior invoice</span><span class="font-semibold">R$ {{ $banco['maior_invoice'] ? number_format($banco['maior_invoice']['value'], 2, ',', '.') : '0,00' }}</span></div>
                            <div class="flex items-center justify-between"><span>Menor invoice</span><span class="font-semibold">R$ {{ $banco['menor_invoice'] ? number_format($banco['menor_invoice']['value'], 2, ',', '.') : '0,00' }}</span></div>
                            <div class="flex items-center justify-between"><span>Categoria líder</span><span class="truncate pl-3 font-semibold">{{ $banco['top_category'] ?: 'Sem destaque' }}</span></div>
                            <div class="flex items-center justify-between"><span>Fechamento estimado</span><span class="font-semibold text-indigo-200">{{ $banco['days_to_close'] }} dia(s)</span></div>
                        </div>

                        <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-300 transition group-hover:text-indigo-200">
                            Ver invoices <i class="fas fa-arrow-right text-xs"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div id="banks-chart-data"
         class="hidden"
         data-trend-labels="{{ e(json_encode($trendLabels)) }}"
         data-trend-values="{{ e(json_encode($trendValues)) }}"
         data-top-banks="{{ e(json_encode($topBanks)) }}"
         data-top-banks-month-values="{{ e(json_encode($topBanksMonthValues)) }}"
         data-top-banks-cycle-values="{{ e(json_encode($topBanksCycleValues)) }}"
         data-invoice-category-share="{{ e(json_encode($invoiceCategoryShare)) }}"></div>

    <style>
        .dashboard-banks-page .apexcharts-legend-text,
        .dashboard-banks-page .apexcharts-xaxis-label,
        .dashboard-banks-page .apexcharts-yaxis-label { color: #94a3b8 !important; }
        .dashboard-banks-page .banks-panel {
            padding: 1rem;
        }
        .dashboard-banks-page .banks-hero {
            padding: 1.2rem;
        }
        .dashboard-banks-page .banks-kpi {
            padding: 1rem;
        }
        .dashboard-banks-page .banks-kpi .text-3xl.font-black {
            font-size: clamp(1.85rem, 1.7vw, 2.35rem);
        }
        .dashboard-banks-page .banks-mini-card {
            padding: 0.9rem;
        }
        .dashboard-banks-page .banks-list-item {
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
        }
        .dashboard-banks-page .banks-bank-card {
            padding: 1rem;
        }
        .dashboard-banks-page .banks-chart-lg {
            height: 300px !important;
        }
        .dashboard-banks-page .banks-chart-md {
            height: 280px !important;
        }
        @media (min-width: 1024px) {
            .dashboard-banks-page .banks-panel {
                padding: 1.1rem;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function renderBanksCharts() {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            const dataRoot = document.getElementById('banks-chart-data');
            if (!dataRoot) {
                return;
            }

            const parseData = (key, fallback = []) => {
                try {
                    return JSON.parse(dataRoot.dataset[key] || JSON.stringify(fallback));
                } catch (error) {
                    return fallback;
                }
            };

            const trendLabels = parseData('trendLabels');
            const trendValues = parseData('trendValues');
            const topBanks = parseData('topBanks');
            const topBanksMonthValues = parseData('topBanksMonthValues');
            const topBanksCycleValues = parseData('topBanksCycleValues');
            const invoiceCategoryShare = parseData('invoiceCategoryShare');
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
            const isNotebook = window.innerWidth >= 1024 && window.innerWidth < 1440;
            const chartHeights = {
                lg: isMobile ? 240 : (isTablet ? 270 : (isNotebook ? 290 : 300)),
                md: isMobile ? 220 : (isTablet ? 250 : 280),
            };

            window.banksCharts = window.banksCharts || {};
            Object.values(window.banksCharts).forEach((chart) => chart?.destroy?.());
            window.banksCharts = {};

            const common = {
                chart: { toolbar: { show: false }, background: 'transparent' },
                grid: { borderColor: '#1e293b' },
                dataLabels: { enabled: false },
                legend: { position: isMobile ? 'bottom' : 'top', labels: { colors: ['#e2e8f0'] } },
                tooltip: { theme: 'dark' },
            };

            const trendEl = document.querySelector('#banksTrendChart');
            if (trendEl) {
                window.banksCharts.trend = new ApexCharts(trendEl, {
                    ...common,
                    series: [{ name: 'Invoices', data: trendValues }],
                    chart: { ...common.chart, height: chartHeights.lg, type: 'area' },
                    colors: ['#60a5fa'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.08 } },
                    xaxis: { categories: trendLabels, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.banksCharts.trend.render();
            }

            const rankingEl = document.querySelector('#banksRankingChart');
            if (rankingEl) {
                window.banksCharts.ranking = new ApexCharts(rankingEl, {
                    ...common,
                    series: [{ name: 'Mês', data: topBanksMonthValues }],
                    chart: { ...common.chart, height: chartHeights.lg, type: 'bar' },
                    colors: ['#818cf8'],
                    plotOptions: { bar: { borderRadius: 8, horizontal: true } },
                    xaxis: { categories: topBanks, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#cbd5e1' } } },
                });
                window.banksCharts.ranking.render();
            }

            const categoryEl = document.querySelector('#banksCategoryChart');
            if (categoryEl) {
                window.banksCharts.categories = new ApexCharts(categoryEl, {
                    ...common,
                    series: invoiceCategoryShare.map((item) => item.value),
                    chart: { ...common.chart, height: chartHeights.md, type: 'donut' },
                    labels: invoiceCategoryShare.map((item) => item.label),
                    colors: ['#38bdf8', '#818cf8', '#f97316', '#f43f5e', '#14b8a6', '#facc15', '#a855f7', '#e879f9'],
                    legend: { position: 'bottom', labels: { colors: ['#e2e8f0'] } },
                });
                window.banksCharts.categories.render();
            }

            const cycleEl = document.querySelector('#banksCycleChart');
            if (cycleEl) {
                window.banksCharts.cycle = new ApexCharts(cycleEl, {
                    ...common,
                    series: [{ name: 'Ciclo atual', data: topBanksCycleValues }],
                    chart: { ...common.chart, height: chartHeights.md, type: 'bar' },
                    colors: ['#22d3ee'],
                    plotOptions: { bar: { borderRadius: 8, columnWidth: '58%' } },
                    xaxis: { categories: topBanks, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.banksCharts.cycle.render();
            }
        }

        document.addEventListener('DOMContentLoaded', renderBanksCharts);
        document.addEventListener('livewire:navigated', renderBanksCharts);
        window.addEventListener('banks-charts-updated', renderBanksCharts);
    </script>
</div>
