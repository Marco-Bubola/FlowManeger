@php
    $quickMetrics = [
        [
            'label' => 'Clientes com compra no mês',
            'value' => number_format($clientesComCompraMes ?? 0, 0, ',', '.'),
            'icon' => 'fas fa-bag-shopping',
            'surface' => 'border-sky-200/80 bg-sky-50/80 dark:border-sky-500/20 dark:bg-sky-500/10',
            'text' => 'text-sky-700 dark:text-sky-200',
        ],
        [
            'label' => 'Receita do mês',
            'value' => 'R$ ' . number_format($receitaClientesMes ?? 0, 0, ',', '.'),
            'icon' => 'fas fa-coins',
            'surface' => 'border-emerald-200/80 bg-emerald-50/80 dark:border-emerald-500/20 dark:bg-emerald-500/10',
            'text' => 'text-emerald-700 dark:text-emerald-200',
        ],
        [
            'label' => 'Ticket médio',
            'value' => 'R$ ' . number_format($ticketMedioClientes ?? 0, 2, ',', '.'),
            'icon' => 'fas fa-receipt',
            'surface' => 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10',
            'text' => 'text-amber-700 dark:text-amber-200',
        ],
        [
            'label' => 'Taxa de retenção',
            'value' => number_format($taxaRetencao ?? 0, 1, ',', '.') . '%',
            'icon' => 'fas fa-rotate',
            'surface' => 'border-fuchsia-200/80 bg-fuchsia-50/80 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10',
            'text' => 'text-fuchsia-700 dark:text-fuchsia-200',
        ],
    ];

    $summaryCards = [
        [
            'label' => 'Base de clientes',
            'value' => number_format($totalClientes ?? 0, 0, ',', '.'),
            'note' => 'cadastros ativos no CRM',
            'icon' => 'fas fa-users',
            'tone' => 'from-rose-500 to-pink-600',
            'border' => 'border-rose-400/20',
            'accent' => 'text-rose-200',
        ],
        [
            'label' => 'Novos no mês',
            'value' => number_format($clientesNovosMes ?? 0, 0, ',', '.'),
            'note' => 'aquisição corrente',
            'icon' => 'fas fa-user-plus',
            'tone' => 'from-emerald-500 to-green-600',
            'border' => 'border-emerald-400/20',
            'accent' => 'text-emerald-200',
        ],
        [
            'label' => 'Com pendências',
            'value' => number_format($clientesInadimplentes ?? 0, 0, ',', '.'),
            'note' => 'relacionamentos com cobrança aberta',
            'icon' => 'fas fa-user-clock',
            'tone' => 'from-amber-500 to-orange-600',
            'border' => 'border-amber-400/20',
            'accent' => 'text-amber-100',
        ],
        [
            'label' => 'Recorrentes',
            'value' => number_format($clientesRecorrentesCount ?? 0, 0, ',', '.'),
            'note' => 'mais de 2 compras registradas',
            'icon' => 'fas fa-heart-circle-check',
            'tone' => 'from-indigo-500 to-cyan-600',
            'border' => 'border-cyan-400/20',
            'accent' => 'text-cyan-100',
        ],
    ];
@endphp

<div class="dashboard-clientes-page mobile-393-base app-viewport-fit min-h-screen w-full relative overflow-hidden">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/dashboard-clientes-ultrawide.css') }}">

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,_rgba(236,72,153,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.14),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.04),_transparent)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(236,72,153,0.28),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.20),_transparent_36%),linear-gradient(180deg,_rgba(15,23,42,0.55),_transparent)]"></div>
        <div class="absolute left-16 top-16 h-52 w-52 rounded-full bg-rose-400/10 blur-3xl"></div>
        <div class="absolute right-10 top-10 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute left-1/2 top-24 h-72 w-72 -translate-x-1/2 rounded-full bg-fuchsia-400/8 blur-3xl"></div>
    </div>

    <div class="dashboard-clientes-shell px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <section class="space-y-4">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-5">
                <div class="xl:col-span-8 overflow-hidden rounded-[28px] border border-white/60 bg-white/85 shadow-[0_20px_80px_rgba(15,23,42,0.10)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="relative overflow-hidden px-5 py-6 sm:px-6 sm:py-6">
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(244,114,182,0.10),rgba(45,212,191,0.08),rgba(56,189,248,0.07))] dark:bg-[linear-gradient(135deg,rgba(244,114,182,0.18),rgba(45,212,191,0.14),rgba(56,189,248,0.12))]"></div>
                        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-3xl space-y-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-pink-200/80 bg-pink-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-pink-700 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-200">Parte 1</span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/75 px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300"><i class="fas fa-address-book text-emerald-500"></i>Relacionamento com clientes</span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"><i class="fas fa-circle text-[8px]"></i>{{ $periodLabel }}</span>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-300">
                                        <a href="{{ route('dashboard') }}" class="transition hover:text-pink-600 dark:hover:text-pink-300"><i class="fas fa-chart-line mr-1"></i>Dashboard</a>
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                        <span class="font-semibold text-slate-700 dark:text-slate-200"><i class="fas fa-users mr-1"></i>Clientes</span>
                                    </div>
                                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Dashboard de clientes completo</h1>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">Uma leitura moderna do CRM com aquisição, recorrência, pendências, clientes inativos e movimentação recente, mantendo o mesmo estilo executivo dos outros dashboards.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/80 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-700 dark:text-rose-300">Base ativa</p>
                                        <p class="mt-2 text-2xl font-black text-rose-700 dark:text-rose-200">{{ number_format($totalClientes ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Receita do mês</p>
                                        <p class="mt-2 text-2xl font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($receitaClientesMes ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Pendências</p>
                                        <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-200">{{ number_format($clientesInadimplentes ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-cyan-200/80 bg-cyan-50/80 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-700 dark:text-cyan-300">Retenção</p>
                                        <p class="mt-2 text-2xl font-black text-cyan-700 dark:text-cyan-200">{{ number_format($taxaRetencao ?? 0, 1, ',', '.') }}%</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[320px]">
                                <a href="{{ route('dashboard.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-pink-300 hover:text-pink-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-pink-500/40 dark:hover:text-pink-300"><span class="flex items-center gap-3"><i class="fas fa-house text-pink-500"></i>Dashboard geral</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                <a href="{{ route('clients.index') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"><span class="flex items-center gap-3"><i class="fas fa-list text-indigo-500"></i>Gerenciar clientes</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                <a href="{{ route('clients.create') }}" class="group inline-flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:text-emerald-300"><span class="flex items-center gap-3"><i class="fas fa-user-plus text-emerald-500"></i>Novo cliente</span><i class="fas fa-arrow-right text-xs opacity-60 transition group-hover:translate-x-1"></i></a>
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-300">
                                    <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"><i class="fas fa-cake-candles text-amber-500"></i>Aniversários</div>
                                    <div class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ number_format($clientesAniversariantes ?? 0, 0, ',', '.') }}</div>
                                    <div class="mt-1 text-xs">no mês atual</div>
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
                <span class="inline-flex items-center gap-2 rounded-full border border-rose-200/80 bg-rose-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">Parte 2</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Panorama executivo dos clientes</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Indicadores principais de aquisição, recorrência e risco comercial.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach ($summaryCards as $card)
                    <article class="rounded-[26px] border {{ $card['border'] }} bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:bg-slate-950/75 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-3 text-slate-400">
                            <span class="text-xs font-black uppercase tracking-[0.16em]">{{ $card['label'] }}</span>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['tone'] }} text-white shadow-lg">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $card['value'] }}</div>
                        <div class="mt-2 text-sm {{ $card['accent'] }}">{{ $card['note'] }}</div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200/80 bg-cyan-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-200">Parte 3</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Clientes que puxam o relacionamento</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Quem mais compra, quem voltou a comprar e onde o relacionamento está mais saudável.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Top clientes</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Maior receita acumulada</p>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 dark:bg-rose-500/10 dark:text-rose-200">{{ count($topClientes ?? []) }} em destaque</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($topClientes as $client)
                            <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-pink-500 text-sm font-black text-white">{{ strtoupper(substr($client['name'] ?? 'C', 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $client['name'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($client['qtd_vendas'] ?? 0, 0, ',', '.') }} venda(s)</p>
                                    </div>
                                </div>
                                <div class="text-right text-sm font-black text-emerald-700 dark:text-emerald-200">R$ {{ number_format($client['total_vendas'] ?? 0, 2, ',', '.') }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">Nenhum cliente com histórico suficiente ainda.</div>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-white shadow-lg"><i class="fas fa-repeat"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes recorrentes</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Quem mais retorna para comprar</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($clientesRecorrentes as $client)
                            <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $client['name'] }}</p>
                                <div class="mt-1 flex items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ number_format($client['qtd_vendas'] ?? 0, 0, ',', '.') }} compras</span>
                                    <span>R$ {{ number_format($client['total_vendas'] ?? 0, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Ainda sem recorrência expressiva.</p>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-3 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Novos clientes</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Cadastros mais recentes</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($clientesRecentes as $client)
                            <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $client['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $client['created_at'] }}{{ !empty($client['email']) ? ' · ' . $client['email'] : '' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum cliente recente encontrado.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200/80 bg-amber-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">Parte 4</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Risco, inatividade e movimentação</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Onde agir rápido para recuperar receita e manter o CRM saudável.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg"><i class="fas fa-triangle-exclamation"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes com pendências</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Relacionamentos que exigem cobrança ou renegociação</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($clientesPendentes as $client)
                            <div class="rounded-2xl border border-amber-200/70 bg-amber-50/70 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-amber-800 dark:text-amber-200">{{ $client['name'] }}</p>
                                        <p class="text-xs text-amber-700/80 dark:text-amber-100/80">{{ number_format($client['sales_count'] ?? 0, 0, ',', '.') }} venda(s) pendente(s)</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-amber-800 dark:text-amber-100">R$ {{ number_format($client['pending_value'] ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">Nenhuma pendência relevante no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-3 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-500 to-slate-700 text-white shadow-lg"><i class="fas fa-user-slash"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Clientes inativos</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem compras há 6 meses</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($clientesInativos as $client)
                            <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $client['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">desde {{ $client['created_at'] }}{{ !empty($client['email']) ? ' · ' . $client['email'] : '' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem clientes inativos relevantes.</p>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-4 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-white shadow-lg"><i class="fas fa-wave-square"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Atividade recente</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Últimas vendas ligadas a clientes</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($atividadeRecente as $activity)
                            <div class="rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/70">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $activity['client'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $activity['date'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-indigo-700 dark:text-indigo-200">R$ {{ number_format($activity['value'] ?? 0, 2, ',', '.') }}</p>
                                        <p class="text-[11px] uppercase tracking-[0.14em] {{ ($activity['status'] ?? '') === 'pendente' ? 'text-amber-700 dark:text-amber-200' : 'text-emerald-700 dark:text-emerald-200' }}">{{ $activity['status'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem atividade recente registrada.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-fuchsia-200/80 bg-fuchsia-50/80 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-fuchsia-700 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10 dark:text-fuchsia-200">Parte 5</span>
                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Ações rápidas e calendário de relacionamento</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atalhos operacionais e visão rápida de aniversários quando houver campo disponível.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-7 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ações rápidas</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Atalhos para operações comuns do CRM</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-list"></i>
                                Lista de clientes
                            </a>
                            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                                <i class="fas fa-plus"></i>
                                Novo cliente
                            </a>
                            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 transition">
                                <i class="fas fa-cart-shopping"></i>
                                Vendas
                            </a>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-5 rounded-[28px] border border-slate-200/80 bg-white/85 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/75">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-600 text-white shadow-lg"><i class="fas fa-cake-candles"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Aniversariantes do mês</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Mostra dados apenas se houver coluna de nascimento</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($aniversariantesLista as $birthday)
                            <div class="rounded-2xl border border-fuchsia-200/70 bg-fuchsia-50/70 p-3 dark:border-fuchsia-500/20 dark:bg-fuchsia-500/10">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-fuchsia-800 dark:text-fuchsia-200">{{ $birthday['name'] }}</p>
                                    <span class="text-sm font-black text-fuchsia-700 dark:text-fuchsia-100">{{ $birthday['date'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">Sem campo de nascimento disponível ou nenhum aniversariante encontrado neste mês.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .dashboard-clientes-shell {
            width: 100%;
            max-width: 100%;
            margin-inline: auto;
            box-sizing: border-box;
        }

        .dashboard-clientes-page,
        .dashboard-clientes-page * {
            box-sizing: border-box;
        }

        .dashboard-clientes-page {
            overflow-x: clip;
        }

        .dashboard-clientes-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.84)) !important;
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.62);
        }

        .dark .dashboard-clientes-page [class*="bg-white/85"] {
            background: linear-gradient(155deg, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.8)) !important;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.42), inset 0 1px 0 rgba(148, 163, 184, 0.08);
        }

        .dashboard-clientes-page [class*="bg-slate-50/80"],
        .dashboard-clientes-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(245, 247, 255, 0.88), rgba(237, 242, 255, 0.72)) !important;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .dark .dashboard-clientes-page [class*="bg-slate-50/80"],
        .dark .dashboard-clientes-page [class*="bg-slate-50/70"] {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.8)) !important;
            border-color: rgba(71, 85, 105, 0.32);
        }

        .dashboard-clientes-page h1.text-3xl.font-black {
            font-size: clamp(2rem, 1.8vw, 2.9rem) !important;
        }

        .dashboard-clientes-page h2.text-2xl.font-black {
            font-size: clamp(1.4rem, 1vw, 1.9rem) !important;
        }

        .dashboard-clientes-page .text-3xl.font-black {
            font-size: clamp(1.55rem, 1.2vw, 2.1rem) !important;
        }

        .dashboard-clientes-page .rounded-\[28px\],
        .dashboard-clientes-page .rounded-\[24px\],
        .dashboard-clientes-page .rounded-\[26px\] {
            border-radius: 22px !important;
        }
    </style>
</div>
