@php
    $firstBankCycle = collect($bankCycleOverview)->first();
    $savingsProgress = $totalMetasCofrinhos > 0 ? ($totalCofrinhos / $totalMetasCofrinhos) * 100 : 0;
    $economiaDelta = $economiadoMesAnterior > 0 ? (($economiadoMesAtual - $economiadoMesAnterior) / $economiadoMesAnterior) * 100 : ($economiadoMesAtual > 0 ? 100 : 0);
    $cashbookExportParams = ['year' => $ano, 'month' => $mes];
    if ($cofrinhoFiltro) {
        $cashbookExportParams['cofrinho'] = $cofrinhoFiltro;
    }
    $cashbookQuickMetrics = [
        [
            'label' => 'Dias ativos',
            'value' => number_format($activeDaysCount ?? 0, 0, ',', '.'),
            'icon' => 'fas fa-calendar-week',
            'surface' => 'border-sky-200/80 bg-sky-50/80 dark:border-sky-500/20 dark:bg-sky-500/10',
            'text' => 'text-sky-700 dark:text-sky-200',
        ],
        [
            'label' => 'Receita por dia',
            'value' => 'R$ ' . number_format($mediaReceitaDia ?? 0, 2, ',', '.'),
            'icon' => 'fas fa-arrow-trend-up',
            'surface' => 'border-emerald-200/80 bg-emerald-50/80 dark:border-emerald-500/20 dark:bg-emerald-500/10',
            'text' => 'text-emerald-700 dark:text-emerald-200',
        ],
        [
            'label' => 'Invoice por dia',
            'value' => 'R$ ' . number_format($mediaInvoiceDia ?? 0, 2, ',', '.'),
            'icon' => 'fas fa-file-invoice-dollar',
            'surface' => 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10',
            'text' => 'text-amber-700 dark:text-amber-200',
        ],
        [
            'label' => 'Taxa de poupança',
            'value' => number_format($savingsRatePercent ?? 0, 1, ',', '.') . '%',
            'icon' => 'fas fa-piggy-bank',
            'surface' => 'border-fuchsia-200/80 bg-fuchsia-50/80 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10',
            'text' => 'text-fuchsia-700 dark:text-fuchsia-200',
        ],
    ];
@endphp

<div class="dashboard-cashbook-page app-viewport-fit w-full min-h-screen bg-[radial-gradient(circle_at_top,#10304a_0%,#07111d_35%,#030712_100%)] text-white mobile-393-base relative overflow-x-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-cashbook-ultrawide.css') }}">
    <x-loading-overlay message="Carregando..." />

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.20),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(217,70,239,0.16),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.06),_transparent)]"></div>
        <div class="absolute left-10 top-14 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute right-10 top-10 h-64 w-64 rounded-full bg-fuchsia-400/10 blur-3xl"></div>
        <div class="absolute left-1/2 top-28 h-72 w-72 -translate-x-1/2 rounded-full bg-amber-400/8 blur-3xl"></div>
    </div>

    <div class="mx-auto flex w-full max-w-[1880px] flex-col px-4 py-4 sm:px-5 lg:px-6 xl:px-8">
        <section class="finance-hero rounded-[30px] border border-cyan-400/20 bg-slate-900/70 p-5 shadow-[0_30px_120px_rgba(2,8,23,0.55)] backdrop-blur-xl lg:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 via-sky-500 to-indigo-500 shadow-lg shadow-cyan-500/20">
                        <i class="fas fa-wallet text-2xl text-white"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-200">Parte 1</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-fuchsia-400/30 bg-fuchsia-400/10 px-3 py-1 text-xs font-semibold text-fuchsia-100"><i class="fas fa-wave-square"></i>Financeiro central</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-100"><i class="fas fa-circle text-[8px]"></i>{{ $periodLabel }}</span>
                        </div>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-white lg:text-4xl">Cashbook, bancos e invoices no mesmo painel</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-300 lg:text-base">Leitura operacional do caixa, pressão das faturas, orçamento do mês, avanço de cofrinhos e ciclos dos bancos sem alternar entre telas para entender o fluxo.</p>
                    </div>
                </div>

                <div class="grid w-full gap-3 md:grid-cols-2 xl:w-[760px] xl:grid-cols-4">
                    <div>
                        <label for="ano" class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Ano</label>
                        <select id="ano" wire:model.live="ano" class="w-full rounded-2xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-sm font-semibold text-white outline-none transition focus:border-cyan-400">
                            @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="mes" class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Mês</label>
                        <select id="mes" wire:model.live="mes" class="w-full rounded-2xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-sm font-semibold text-white outline-none transition focus:border-cyan-400">
                            @foreach (range(1, 12) as $mesNumero)
                                <option value="{{ $mesNumero }}">{{ str_pad($mesNumero, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cofrinhoFiltro" class="mb-1 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Cofrinho</label>
                        <div class="flex gap-2">
                            <select id="cofrinhoFiltro" wire:model.live="cofrinhoFiltro" class="w-full rounded-2xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-sm font-semibold text-white outline-none transition focus:border-cyan-400">
                                <option value="">Todos</option>
                                @foreach($cofrinhos as $cofrinho)
                                    <option value="{{ $cofrinho['id'] }}">{{ $cofrinho['nome'] }}</option>
                                @endforeach
                            </select>
                            @if($cofrinhoFiltro)
                                <button wire:click="clearCofrinhoFilter" class="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-3 text-rose-200 transition hover:bg-rose-500/20">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Recorte ativo</div>
                        <div class="mt-2 text-sm font-bold text-white">{{ $periodLabel }}</div>
                        <div class="mt-1 text-xs text-slate-400">{{ $totalBancos }} bancos · {{ $bancosAtivosComGasto }} com gasto no mês</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($cashbookQuickMetrics as $metric)
                    <div class="finance-mini-card rounded-2xl border p-4 shadow-[0_12px_40px_rgba(2,8,23,0.22)] {{ $metric['surface'] }}">
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

            <div class="mt-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setFilter(null)" class="rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $filterType === null ? 'border-cyan-400 bg-cyan-400 text-slate-950' : 'border-slate-700 bg-slate-900/70 text-slate-300 hover:border-cyan-400/50 hover:text-white' }}">Visão geral</button>
                    <button wire:click="setFilter('receitas')" class="rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $filterType === 'receitas' ? 'border-emerald-400 bg-emerald-400 text-slate-950' : 'border-slate-700 bg-slate-900/70 text-slate-300 hover:border-emerald-400/50 hover:text-white' }}">Receitas</button>
                    <button wire:click="setFilter('despesas')" class="rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $filterType === 'despesas' ? 'border-rose-400 bg-rose-400 text-slate-950' : 'border-slate-700 bg-slate-900/70 text-slate-300 hover:border-rose-400/50 hover:text-white' }}">Despesas</button>
                    <button wire:click="setFilter('invoices')" class="rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.16em] transition {{ $filterType === 'invoices' ? 'border-amber-400 bg-amber-400 text-slate-950' : 'border-slate-700 bg-slate-900/70 text-slate-300 hover:border-amber-400/50 hover:text-white' }}">Invoices</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashboard.banks') }}" class="inline-flex items-center gap-2 rounded-2xl border border-indigo-400/40 bg-indigo-500/10 px-4 py-3 text-sm font-semibold text-indigo-100 transition hover:bg-indigo-500/20"><i class="fas fa-building-columns"></i>Bancos</a>
                    @if($firstBankCycle)
                        <a href="{{ route('invoices.index', ['bankId' => $firstBankCycle['id_bank']]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-100 transition hover:bg-amber-500/20"><i class="fas fa-file-invoice-dollar"></i>Invoices</a>
                    @endif
                    <a href="{{ route('reports.dashboard-cashbook.export', array_merge($cashbookExportParams, ['format' => 'xlsx'])) }}" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-100 transition hover:bg-emerald-500/20"><i class="fas fa-file-excel"></i>Excel</a>
                    <a href="{{ route('reports.dashboard-cashbook.export', array_merge($cashbookExportParams, ['format' => 'pdf'])) }}" class="inline-flex items-center gap-2 rounded-2xl border border-rose-400/40 bg-rose-500/10 px-4 py-3 text-sm font-semibold text-rose-100 transition hover:bg-rose-500/20"><i class="fas fa-file-pdf"></i>PDF</a>
                </div>
            </div>
        </section>

        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="finance-kpi rounded-[26px] border border-cyan-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Saldo total</span><i class="fas fa-scale-balanced text-cyan-300"></i></div>
                <div class="mt-3 text-3xl font-black {{ $saldoTotal >= 0 ? 'text-cyan-100' : 'text-rose-200' }}">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</div>
                <div class="mt-2 text-sm text-slate-400">Mês atual: <span class="font-bold {{ $saldoMesAtual >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($saldoMesAtual, 2, ',', '.') }}</span></div>
            </article>
            <article class="finance-kpi rounded-[26px] border border-emerald-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Entradas x saídas</span><i class="fas fa-arrows-up-down text-emerald-300"></i></div>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-xs text-slate-500">Receitas</div>
                        <div class="text-2xl font-black text-emerald-300">R$ {{ number_format($receitaMesAtual, 2, ',', '.') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Despesas</div>
                        <div class="text-2xl font-black text-rose-300">R$ {{ number_format($despesaMesAtual, 2, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-2 text-sm text-slate-400">Ano {{ $ano }}: R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}</div>
            </article>
            <article class="finance-kpi rounded-[26px] border border-amber-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Pressão das invoices</span><i class="fas fa-file-invoice-dollar text-amber-300"></i></div>
                <div class="mt-3 text-3xl font-black text-amber-100">R$ {{ number_format($invoiceMesAtual, 2, ',', '.') }}</div>
                <div class="mt-2 flex items-center justify-between text-sm text-slate-400">
                    <span>{{ $invoiceQuantidadeMesAtual }} lançamentos</span>
                    <span>Ticket R$ {{ number_format($ticketMedioInvoice, 2, ',', '.') }}</span>
                </div>
            </article>
            <article class="finance-kpi rounded-[26px] border border-fuchsia-400/20 bg-slate-900/75 p-5 shadow-[0_20px_60px_rgba(2,8,23,0.35)]">
                <div class="flex items-center justify-between text-slate-400"><span class="text-xs font-black uppercase tracking-[0.16em]">Reserva e metas</span><i class="fas fa-piggy-bank text-fuchsia-300"></i></div>
                <div class="mt-3 text-3xl font-black text-fuchsia-100">R$ {{ number_format($totalCofrinhos, 2, ',', '.') }}</div>
                <div class="mt-2 text-sm text-slate-400">{{ number_format($savingsProgress, 1, ',', '.') }}% das metas cobertas</div>
            </article>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="finance-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Pressão das invoices</p>
                        <p class="mt-2 text-2xl font-black {{ $invoicePressurePercent <= 35 ? 'text-emerald-300' : ($invoicePressurePercent <= 70 ? 'text-amber-300' : 'text-rose-300') }}">{{ number_format($invoicePressurePercent, 1, ',', '.') }}%</p>
                    </div>
                    <i class="fas fa-gauge-high text-xl text-amber-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">peso das faturas sobre a receita do mês</p>
            </article>

            <article class="finance-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Ritmo diário</p>
                        <p class="mt-2 text-2xl font-black text-cyan-100">R$ {{ number_format($mediaDespesaDia, 2, ',', '.') }}</p>
                    </div>
                    <i class="fas fa-calendar-day text-xl text-cyan-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">média diária de despesas no período</p>
            </article>

            <article class="finance-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Banco dominante</p>
                        <p class="mt-2 text-lg font-black text-white">{{ data_get($topBank, 'name', 'Sem destaque') }}</p>
                    </div>
                    <i class="fas fa-building-columns text-xl text-indigo-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">{{ $topBank ? 'R$ ' . number_format(data_get($topBank, 'monthly_total', 0), 2, ',', '.') . ' no mês' : 'sem volume relevante neste período' }}</p>
            </article>

            <article class="finance-mini-card rounded-[24px] border border-slate-800 bg-slate-900/65 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Categoria líder</p>
                        <p class="mt-2 text-lg font-black text-white">{{ data_get($topExpenseCategory, 'categoria', 'Sem destaque') }}</p>
                    </div>
                    <i class="fas fa-layer-group text-xl text-fuchsia-300"></i>
                </div>
                <p class="mt-2 text-sm text-slate-400">{{ $topExpenseCategory ? 'R$ ' . number_format(data_get($topExpenseCategory, 'total', 0), 2, ',', '.') . ' concentrados' : 'sem concentração relevante' }}</p>
            </article>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="finance-panel xl:col-span-8 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-300">Panorama anual</p>
                        <h2 class="text-xl font-black text-white">Fluxo de caixa, faturas e saldo por mês</h2>
                    </div>
                    <div class="text-sm text-slate-400">Receitas, despesas, invoices e saldo líquido do ano {{ $ano }}</div>
                </div>
                <div id="financeFlowChart" class="finance-chart-lg h-[340px]"></div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-300">Comparativo</p>
                        <h2 class="text-xl font-black text-white">Mês atual vs anterior</h2>
                    </div>
                    <span class="rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-400">{{ $periodLabel }}</span>
                </div>
                <div id="periodComparisonChart" class="finance-chart-lg h-[340px]"></div>
            </section>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-300">Bancos</p>
                        <h2 class="text-xl font-black text-white">Participação das faturas</h2>
                    </div>
                    <span class="text-xs text-slate-400">{{ count($bankBreakdown) }} bancos</span>
                </div>
                <div id="bankShareChart" class="finance-chart-md h-[320px]"></div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange-300">Invoices</p>
                        <h2 class="text-xl font-black text-white">Gasto diário do mês</h2>
                    </div>
                    <span class="text-xs text-slate-400">{{ $invoiceQuantidadeMesAtual }} registros</span>
                </div>
                <div id="invoiceDailyChart" class="finance-chart-md h-[320px]"></div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-fuchsia-300">Categorias</p>
                        <h2 class="text-xl font-black text-white">Concentração de gastos</h2>
                    </div>
                    <span class="text-xs text-slate-400">cashbook + invoices</span>
                </div>
                <div id="categorySpendChart" class="finance-chart-md h-[320px]"></div>
            </section>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-fuchsia-300">Cofrinhos</p>
                        <h2 class="text-xl font-black text-white">Evolução e foco do mês</h2>
                    </div>
                    <span class="text-xs text-slate-400">{{ count($cofrinhos) }} metas</span>
                </div>
                <div id="cofrinhosEvolutionChart" class="finance-chart-sm h-[240px]"></div>
                <div class="finance-mini-card mt-3 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                    <div class="flex items-center justify-between text-sm text-slate-400">
                        <span>Economia do mês</span>
                        <span class="font-bold {{ $economiaDelta >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">{{ number_format(abs($economiaDelta), 1, ',', '.') }}%</span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-white">R$ {{ number_format($economiadoMesAtual, 2, ',', '.') }}</div>
                    @if($selectedCofrinhoSummary)
                        <div class="mt-3 border-t border-slate-800 pt-3 text-sm text-slate-300">
                            <div class="font-bold text-white">{{ $selectedCofrinhoSummary['nome'] }}</div>
                            <div class="mt-1">Saldo no mês: <span class="font-semibold">R$ {{ number_format($selectedCofrinhoSummary['saldo_mes'], 2, ',', '.') }}</span></div>
                            <div class="mt-1">Progresso: <span class="font-semibold">{{ number_format($selectedCofrinhoSummary['progresso'], 1, ',', '.') }}%</span></div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-300">Orçamento</p>
                        <h2 class="text-xl font-black text-white">Saúde do mês</h2>
                    </div>
                    <span class="text-xs text-slate-400">{{ number_format($orcamentoUsoPercentual, 1, ',', '.') }}% usado</span>
                </div>
                <div class="finance-mini-card rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                    <div class="flex items-center justify-between text-sm text-slate-400"><span>Orçado</span><span>R$ {{ number_format($orcamentoMesTotal, 2, ',', '.') }}</span></div>
                    <div class="mt-2 flex items-center justify-between text-sm text-slate-400"><span>Executado</span><span class="text-rose-300">R$ {{ number_format($orcamentoMesUsado, 2, ',', '.') }}</span></div>
                    <div class="mt-2 flex items-center justify-between text-sm text-slate-400"><span>Restante</span><span class="{{ $orcamentoRestante >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($orcamentoRestante, 2, ',', '.') }}</span></div>
                    <progress class="cashbook-progress mt-4 h-3 w-full overflow-hidden rounded-full" max="100" value="{{ min(100, max(0, $orcamentoUsoPercentual)) }}"></progress>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse($orcamentosTopEstouro as $estouro)
                        <div class="finance-list-item flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm">
                            <span class="truncate pr-3 text-slate-300">{{ $estouro['category'] }}</span>
                            <span class="font-bold text-rose-300">+ R$ {{ number_format($estouro['estouro'], 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Sem estouro relevante no orçamento deste mês.</div>
                    @endforelse
                </div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-300">Projeção</p>
                        <h2 class="text-xl font-black text-white">Fôlego de caixa</h2>
                    </div>
                    <span class="text-xs text-slate-400">média últimos 90 dias</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="finance-mini-card rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-slate-500">30 dias</div>
                        <div class="mt-2 text-2xl font-black {{ $previsao30dias >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($previsao30dias, 2, ',', '.') }}</div>
                    </div>
                    <div class="finance-mini-card rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-slate-500">60 dias</div>
                        <div class="mt-2 text-2xl font-black {{ $previsao60dias >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($previsao60dias, 2, ',', '.') }}</div>
                    </div>
                    <div class="finance-mini-card rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-4">
                        <div class="text-xs uppercase tracking-[0.16em] text-slate-500">90 dias</div>
                        <div class="mt-2 text-2xl font-black {{ $previsao90dias >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($previsao90dias, 2, ',', '.') }}</div>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-300">Ciclos</p>
                        <h2 class="text-xl font-black text-white">Fechamento por banco</h2>
                    </div>
                    <a href="{{ route('dashboard.banks') }}" class="text-xs font-semibold text-cyan-300 hover:text-cyan-200">abrir banks</a>
                </div>
                <div class="space-y-3">
                    @forelse($bankCycleOverview as $bank)
                        <a href="{{ $bank['link'] }}" class="finance-list-item block rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-4 transition hover:border-cyan-400/30 hover:bg-slate-950">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-white">{{ $bank['name'] }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $bank['cycle_start'] }} até {{ $bank['cycle_end'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-cyan-200">R$ {{ number_format($bank['cycle_total'], 2, ',', '.') }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $bank['days_to_close'] }} dia(s)</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Nenhum banco cadastrado para analisar ciclo.</div>
                    @endforelse
                </div>
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                @include('livewire.dashboard.components.calendar', ['cashbookDays' => $cashbookDays, 'invoiceDays' => $invoiceDays])

                @if ($selectedDate)
                    <div class="finance-mini-card mt-4 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                        <h3 class="text-lg font-black text-white">Detalhes do dia {{ $selectedDate }}</h3>
                        <div wire:loading wire:target="showDayDetails" class="mt-3 text-sm text-slate-400">Carregando detalhes...</div>
                        <div wire:loading.remove wire:target="showDayDetails" class="mt-4 space-y-4 text-sm">
                            <div>
                                <div class="mb-2 text-xs font-black uppercase tracking-[0.16em] text-emerald-300">Receitas</div>
                                @forelse ($dayDetails['receitas'] as $receita)
                                    <div class="flex items-center justify-between gap-3 py-1 text-slate-300"><span class="truncate">{{ $receita['description'] }}</span><span class="font-semibold text-emerald-300">R$ {{ number_format($receita['value'], 2, ',', '.') }}</span></div>
                                @empty
                                    <p class="text-slate-500">Nenhuma receita neste dia.</p>
                                @endforelse
                            </div>
                            <div>
                                <div class="mb-2 text-xs font-black uppercase tracking-[0.16em] text-rose-300">Despesas</div>
                                @forelse ($dayDetails['despesas'] as $despesa)
                                    <div class="flex items-center justify-between gap-3 py-1 text-slate-300"><span class="truncate">{{ $despesa['description'] }}</span><span class="font-semibold text-rose-300">R$ {{ number_format($despesa['value'], 2, ',', '.') }}</span></div>
                                @empty
                                    <p class="text-slate-500">Nenhuma despesa neste dia.</p>
                                @endforelse
                            </div>
                            <div>
                                <div class="mb-2 text-xs font-black uppercase tracking-[0.16em] text-amber-300">Invoices</div>
                                @forelse ($dayDetails['invoices'] as $invoice)
                                    <div class="flex items-center justify-between gap-3 py-1 text-slate-300"><span class="truncate">{{ $invoice['description'] }}</span><span class="font-semibold text-amber-300">R$ {{ number_format($invoice['value'], 2, ',', '.') }}</span></div>
                                @empty
                                    <p class="text-slate-500">Nenhuma invoice neste dia.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            <section class="finance-panel xl:col-span-4 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-300">Operação</p>
                        <h2 class="text-xl font-black text-white">Histórico financeiro</h2>
                    </div>
                    <span class="text-xs text-slate-400">cashbook + invoices + uploads</span>
                </div>
                <div class="space-y-3">
                    @forelse($activityFeed as $activity)
                        <div class="finance-list-item rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-2xl {{ $activity['tone'] === 'emerald' ? 'bg-emerald-500/15 text-emerald-300' : ($activity['tone'] === 'rose' ? 'bg-rose-500/15 text-rose-300' : ($activity['tone'] === 'amber' ? 'bg-amber-500/15 text-amber-300' : 'bg-cyan-500/15 text-cyan-300')) }}">
                                    <i class="fas {{ $activity['icon'] }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="truncate font-bold text-white">{{ $activity['title'] }}</div>
                                            <div class="mt-1 text-xs text-slate-400">{{ $activity['meta'] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-slate-200">R$ {{ number_format($activity['value'], 2, ',', '.') }}</div>
                                            <div class="mt-1 text-[11px] text-slate-500">{{ $activity['date'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Sem histórico operacional recente.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <section class="finance-panel xl:col-span-6 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-300">Últimas movimentações</p>
                        <h2 class="text-xl font-black text-white">Lista consolidada</h2>
                    </div>
                    <span class="text-xs text-slate-400">{{ count($recentTransactions) }} itens</span>
                </div>
                <div class="space-y-2">
                    @forelse ($recentTransactions as $transaction)
                        <div class="finance-list-item flex items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-white">{{ $transaction['description'] ?? 'Sem descrição' }}</div>
                                <div class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-500">{{ $transaction['origin'] ?? 'cashbook' }}</div>
                                @if(!empty($transaction['meta']))
                                    <div class="mt-1 truncate text-[11px] text-slate-400">{{ $transaction['meta'] }}</div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="font-bold {{ ($transaction['type_id'] ?? 2) == 1 ? 'text-emerald-300' : 'text-rose-300' }}">R$ {{ number_format($transaction['value'], 2, ',', '.') }}</div>
                                <div class="mt-1 text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Nenhuma transação recente.</div>
                    @endforelse
                </div>
            </section>

            <section class="finance-panel xl:col-span-3 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-fuchsia-300">Cofrinhos</p>
                        <h2 class="text-xl font-black text-white">Mais próximos da meta</h2>
                    </div>
                    <span class="text-xs text-slate-400">top 3</span>
                </div>
                <div class="space-y-3">
                    @forelse($cofrinhosTopMeta as $cofrinho)
                        <a href="{{ $cofrinho['link'] }}" class="finance-list-item block rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-4 transition hover:border-fuchsia-400/30 hover:bg-slate-950">
                            <div class="flex items-center justify-between gap-3">
                                <span class="truncate font-bold text-white">{{ $cofrinho['nome'] }}</span>
                                <span class="text-sm font-bold text-fuchsia-200">{{ number_format($cofrinho['progresso'], 1, ',', '.') }}%</span>
                            </div>
                            <progress class="cashbook-progress cashbook-progress-fuchsia mt-3 h-2 w-full overflow-hidden rounded-full" max="100" value="{{ min(100, $cofrinho['progresso']) }}"></progress>
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                                <span>R$ {{ number_format($cofrinho['valor_guardado'], 2, ',', '.') }}</span>
                                <span>Meta R$ {{ number_format($cofrinho['meta_valor'], 2, ',', '.') }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Todas as metas atuais já foram alcançadas.</div>
                    @endforelse
                </div>
            </section>

            <section class="finance-panel xl:col-span-3 rounded-[28px] border border-slate-800 bg-slate-900/75 p-5 shadow-[0_20px_70px_rgba(2,8,23,0.4)]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-300">Uploads</p>
                        <h2 class="text-xl font-black text-white">Integração financeira</h2>
                    </div>
                    <span class="text-xs text-slate-400">últimos 6</span>
                </div>
                <div class="space-y-3">
                    @forelse($uploadSummary as $upload)
                        <div class="finance-list-item rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-white">{{ $upload['name'] }}</div>
                                    <div class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-500">{{ $upload['type'] }} · {{ $upload['created_at'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold uppercase tracking-[0.12em] {{ $upload['status'] === 'failed' ? 'text-rose-300' : 'text-cyan-300' }}">{{ $upload['status'] }}</div>
                                    <div class="mt-1 text-sm text-slate-300">{{ $upload['total'] }} itens</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-6 text-sm text-slate-400">Nenhum upload recente encontrado.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div id="cashbook-chart-data"
         class="hidden"
         data-dados-receita="{{ e(json_encode($dadosReceita)) }}"
         data-dados-despesa="{{ e(json_encode($dadosDespesa)) }}"
         data-saldos-mes="{{ e(json_encode($saldosMes)) }}"
         data-invoice-totals-by-month="{{ e(json_encode($invoiceTotalsByMonth)) }}"
         data-period-comparison="{{ e(json_encode($periodComparison)) }}"
         data-dias-invoices="{{ e(json_encode($diasInvoices)) }}"
         data-valores-invoices="{{ e(json_encode($valoresInvoices)) }}"
         data-bank-breakdown="{{ e(json_encode($bankBreakdown)) }}"
         data-expense-categories="{{ e(json_encode($expenseCategories)) }}"
         data-evolucao-cofrinhos="{{ e(json_encode($evolucaoCofrinhos)) }}"></div>

    <style>
        .dashboard-cashbook-page .apexcharts-legend-text,
        .dashboard-cashbook-page .apexcharts-xaxis-label,
        .dashboard-cashbook-page .apexcharts-yaxis-label { color: #94a3b8 !important; }
        .dashboard-cashbook-page .finance-panel {
            padding: 1rem;
        }
        .dashboard-cashbook-page .finance-hero {
            padding: 1.2rem;
        }
        .dashboard-cashbook-page .finance-kpi {
            padding: 1rem;
        }
        .dashboard-cashbook-page .finance-kpi .text-3xl.font-black {
            font-size: clamp(1.85rem, 1.7vw, 2.35rem);
        }
        .dashboard-cashbook-page .finance-mini-card {
            padding: 0.9rem;
        }
        .dashboard-cashbook-page .finance-list-item {
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
        }
        .dashboard-cashbook-page .finance-chart-lg {
            height: 300px !important;
        }
        .dashboard-cashbook-page .finance-chart-md {
            height: 280px !important;
        }
        .dashboard-cashbook-page .finance-chart-sm {
            height: 220px !important;
        }
        @media (min-width: 1024px) {
            .dashboard-cashbook-page .finance-panel {
                padding: 1.1rem;
            }
        }
        .dashboard-cashbook-page .cashbook-progress {
            appearance: none;
            -webkit-appearance: none;
            background: #0f172a;
        }
        .dashboard-cashbook-page .cashbook-progress::-webkit-progress-bar {
            background: #0f172a;
            border-radius: 9999px;
        }
        .dashboard-cashbook-page .cashbook-progress::-webkit-progress-value {
            background: linear-gradient(90deg, #22d3ee 0%, #f59e0b 55%, #fb7185 100%);
            border-radius: 9999px;
        }
        .dashboard-cashbook-page .cashbook-progress::-moz-progress-bar {
            background: linear-gradient(90deg, #22d3ee 0%, #f59e0b 55%, #fb7185 100%);
            border-radius: 9999px;
        }
        .dashboard-cashbook-page .cashbook-progress-fuchsia::-webkit-progress-value {
            background: linear-gradient(90deg, #d946ef 0%, #22d3ee 100%);
        }
        .dashboard-cashbook-page .cashbook-progress-fuchsia::-moz-progress-bar {
            background: linear-gradient(90deg, #d946ef 0%, #22d3ee 100%);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function renderCashbookCharts() {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            const dataRoot = document.getElementById('cashbook-chart-data');
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

            const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            const dadosReceita = parseData('dadosReceita');
            const dadosDespesa = parseData('dadosDespesa');
            const saldosMes = parseData('saldosMes');
            const invoiceTotalsByMonth = parseData('invoiceTotalsByMonth');
            const periodComparison = parseData('periodComparison', {});
            const diasInvoices = parseData('diasInvoices');
            const valoresInvoices = parseData('valoresInvoices');
            const bankBreakdown = parseData('bankBreakdown');
            const expenseCategories = parseData('expenseCategories');
            const evolucaoCofrinhos = parseData('evolucaoCofrinhos');
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
            const isNotebook = window.innerWidth >= 1024 && window.innerWidth < 1440;
            const chartHeights = {
                lg: isMobile ? 240 : (isTablet ? 270 : (isNotebook ? 290 : 300)),
                md: isMobile ? 220 : (isTablet ? 250 : 280),
                sm: isMobile ? 190 : (isTablet ? 205 : 220),
            };

            window.cashbookCharts = window.cashbookCharts || {};
            Object.values(window.cashbookCharts).forEach((chart) => chart?.destroy?.());
            window.cashbookCharts = {};

            const common = {
                chart: { toolbar: { show: false }, background: 'transparent' },
                grid: { borderColor: '#1e293b' },
                dataLabels: { enabled: false },
                legend: { position: isMobile ? 'bottom' : 'top', labels: { colors: ['#e2e8f0'] } },
                tooltip: { theme: 'dark' },
            };

            const flowEl = document.querySelector('#financeFlowChart');
            if (flowEl) {
                window.cashbookCharts.flow = new ApexCharts(flowEl, {
                    ...common,
                    series: [
                        { name: 'Receitas', type: 'column', data: dadosReceita },
                        { name: 'Despesas', type: 'column', data: dadosDespesa },
                        { name: 'Invoices', type: 'line', data: invoiceTotalsByMonth },
                        { name: 'Saldo', type: 'area', data: saldosMes },
                    ],
                    chart: { ...common.chart, height: chartHeights.lg, stacked: false },
                    colors: ['#34d399', '#fb7185', '#f59e0b', '#38bdf8'],
                    stroke: { width: [0, 0, 3, 3], curve: 'smooth' },
                    fill: { opacity: [0.9, 0.9, 1, 0.16] },
                    xaxis: { categories: months, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.cashbookCharts.flow.render();
            }

            const comparisonEl = document.querySelector('#periodComparisonChart');
            if (comparisonEl) {
                window.cashbookCharts.comparison = new ApexCharts(comparisonEl, {
                    ...common,
                    series: [
                        { name: 'Atual', data: periodComparison.current || [] },
                        { name: 'Anterior', data: periodComparison.previous || [] },
                    ],
                    chart: { ...common.chart, height: chartHeights.lg, type: 'bar' },
                    colors: ['#22d3ee', '#64748b'],
                    plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
                    xaxis: { categories: periodComparison.labels || [], labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.cashbookCharts.comparison.render();
            }

            const bankEl = document.querySelector('#bankShareChart');
            if (bankEl) {
                window.cashbookCharts.banks = new ApexCharts(bankEl, {
                    ...common,
                    series: [{ name: 'Mês', data: bankBreakdown.map((item) => item.monthly_total || 0) }],
                    chart: { ...common.chart, height: chartHeights.md, type: 'bar' },
                    colors: ['#38bdf8'],
                    plotOptions: { bar: { horizontal: true, borderRadius: 8 } },
                    xaxis: { categories: bankBreakdown.map((item) => item.name), labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#cbd5e1' } } },
                });
                window.cashbookCharts.banks.render();
            }

            const dailyEl = document.querySelector('#invoiceDailyChart');
            if (dailyEl) {
                window.cashbookCharts.daily = new ApexCharts(dailyEl, {
                    ...common,
                    series: [{ name: 'Invoices', data: valoresInvoices }],
                    chart: { ...common.chart, height: chartHeights.md, type: 'bar' },
                    colors: ['#f97316'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '72%' } },
                    xaxis: { categories: diasInvoices, labels: { style: { colors: '#94a3b8' }, rotate: isMobile ? -70 : -45, hideOverlappingLabels: true } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.cashbookCharts.daily.render();
            }

            const categoryEl = document.querySelector('#categorySpendChart');
            if (categoryEl) {
                window.cashbookCharts.categories = new ApexCharts(categoryEl, {
                    ...common,
                    series: expenseCategories.map((item) => item.total || 0),
                    chart: { ...common.chart, height: chartHeights.md, type: 'donut' },
                    labels: expenseCategories.map((item) => item.categoria),
                    colors: ['#22d3ee', '#a855f7', '#f97316', '#f43f5e', '#0ea5e9', '#14b8a6', '#facc15', '#818cf8'],
                    legend: { position: 'bottom', labels: { colors: ['#e2e8f0'] } },
                });
                window.cashbookCharts.categories.render();
            }

            const cofrinhoEl = document.querySelector('#cofrinhosEvolutionChart');
            if (cofrinhoEl) {
                window.cashbookCharts.cofrinhos = new ApexCharts(cofrinhoEl, {
                    ...common,
                    series: [{ name: 'Acumulado', data: evolucaoCofrinhos }],
                    chart: { ...common.chart, height: chartHeights.sm, type: 'area' },
                    colors: ['#d946ef'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.52, opacityTo: 0.08 } },
                    xaxis: { categories: months, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) } },
                });
                window.cashbookCharts.cofrinhos.render();
            }
        }

        document.addEventListener('DOMContentLoaded', renderCashbookCharts);
        document.addEventListener('livewire:navigated', renderCashbookCharts);
        window.addEventListener('cashbook-charts-updated', renderCashbookCharts);
    </script>
</div>
