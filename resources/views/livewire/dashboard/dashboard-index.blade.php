<div class="dashboard-index-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        use Illuminate\Support\Facades\Auth;
        $userName = Auth::user()->name ?? 'Usuário';
        $firstName = trim(explode(' ', $userName)[0] ?? $userName);
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $ov = $overviewCharts ?? ['revenueLabels'=>[], 'revenueSeries'=>[], 'statusLabels'=>[], 'statusSeries'=>[]];

        // Fluxo de caixa mensal (receitas x despesas)
        $cf = collect($cashflowMonthly ?? []);
        $cfLabels   = $cf->pluck('label')->values()->all();
        $cfReceitas = $cf->pluck('receitas')->map(fn($v)=>round((float)$v,2))->values()->all();
        $cfDespesas = $cf->pluck('despesas')->map(fn($v)=>round((float)$v,2))->values()->all();

        // Despesas por categoria
        $ec = collect($expensesByCategory ?? []);
        $ecLabels = $ec->pluck('label')->values()->all();
        $ecSeries = $ec->pluck('total')->map(fn($v)=>round((float)$v,2))->values()->all();

        // Comparativo de períodos
        $pc = $periodComparison ?? ['labels'=>[], 'income'=>[], 'expenses'=>[]];

        // Orçamento do mês
        $orcTotal = (float)($orcamentoMesTotal ?? 0);
        $orcUsado = (float)($orcamentoMesUsado ?? 0);
        $orcPct   = $orcTotal > 0 ? min(100, round($orcUsado / $orcTotal * 100)) : 0;
    @endphp

    {{-- ============ HEADER COMPACTO ============ --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-indigo-50/70 to-purple-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-indigo-400/20 to-purple-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex flex-wrap items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30 shrink-0">
                <i class="bi bi-grid-1x2-fill text-white text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">{{ $greeting }}, {{ $firstName }} 👋</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Visão geral do seu negócio · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" wire:click="getAiSummary" wire:loading.attr="disabled" wire:target="getAiSummary"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-lg shadow-violet-500/25 transition">
                    <i class="bi bi-stars" wire:loading.remove wire:target="getAiSummary"></i>
                    <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="getAiSummary"></i>
                    <span class="hidden sm:inline">IA</span>
                </button>
                <button type="button" wire:click="refreshData" wire:loading.attr="disabled" wire:target="refreshData"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 hover:bg-white dark:hover:bg-slate-700 transition">
                    <i class="bi bi-arrow-clockwise" wire:loading.remove wire:target="refreshData"></i>
                    <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshData"></i>
                    <span class="hidden sm:inline">Atualizar</span>
                </button>
                <x-header-bell />
            </div>
        </div>

        {{-- Painel IA (resumo) --}}
        @if($showAiPanel ?? false)
        <div class="relative px-4 sm:px-5 pb-3.5">
            <div class="rounded-xl border border-violet-300/40 dark:border-violet-700/40 bg-violet-50/70 dark:bg-violet-950/30 p-3 text-xs text-slate-700 dark:text-slate-200">
                <div class="flex items-start gap-2">
                    <i class="bi bi-robot text-violet-500 mt-0.5"></i>
                    <div class="flex-1">
                        @if($aiSummaryLoading ?? false)
                            <span class="text-violet-500">Gerando análise inteligente...</span>
                        @else
                            {!! nl2br(e($aiSummary ?? 'Sem resumo disponível.')) !!}
                        @endif
                    </div>
                    <button wire:click="closeAiPanel" class="text-slate-400 hover:text-rose-500"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
        </div>
        @endif
    </div>

    @php
        $atalhos = array_values(array_filter([
            ['Nova venda', 'bi-cart-plus', 'sales.create', 'from-emerald-500 to-teal-500'],
            ['Lançar no caixa', 'bi-journal-plus', 'cashbook.create', 'from-sky-500 to-blue-600'],
            ['Novo produto', 'bi-box-seam', 'products.create', 'from-indigo-500 to-purple-600'],
            ['Novo cliente', 'bi-person-plus', 'clients.create', 'from-pink-500 to-rose-500'],
            ['Importar extrato', 'bi-file-earmark-arrow-up', 'cashbook.upload2', 'from-amber-500 to-orange-500'],
            ['Hábitos', 'bi-check2-square', 'conquistas.hub', 'from-violet-500 to-fuchsia-500'],
        ], fn ($a) => \Illuminate\Support\Facades\Route::has($a[2])));
        $toneCls = [
            'emerald' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300',
            'rose' => 'bg-rose-500/15 text-rose-600 dark:text-rose-300',
            'amber' => 'bg-amber-500/15 text-amber-600 dark:text-amber-300',
            'purple' => 'bg-purple-500/15 text-purple-600 dark:text-purple-300',
            'sky' => 'bg-sky-500/15 text-sky-600 dark:text-sky-300',
        ];
    @endphp

    {{-- ============ PARA HOJE + ATALHOS ============ --}}
    <div class="ini-top grid grid-cols-1 lg:grid-cols-12 gap-3 mb-3">
        <section class="ini-hoje lg:col-span-8 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 bg-white/90 dark:bg-slate-800/80 shadow-sm p-3 sm:p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-sun text-amber-500"></i> Para hoje
                    <span class="text-xs font-medium text-slate-400">{{ now()->translatedFormat('l, d \\d\\e F') }}</span>
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                @foreach($hoje as $item)
                    <a href="{{ $item['link'] }}" wire:navigate wire:key="hoje-{{ $loop->index }}"
                       class="ini-hoje-item flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <span class="flex items-center justify-center w-9 h-9 shrink-0 rounded-xl {{ $toneCls[$item['tone']] ?? $toneCls['sky'] }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </span>
                        <span class="flex-1 min-w-0 text-sm font-medium text-slate-700 dark:text-slate-200 leading-snug">{{ $item['title'] }}</span>
                        @if($item['value'])
                            <span class="shrink-0 text-sm font-bold text-slate-800 dark:text-white">{{ $item['value'] }}</span>
                        @endif
                        <i class="bi bi-chevron-right text-xs text-slate-400 shrink-0"></i>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="ini-atalhos lg:col-span-4 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 bg-white/90 dark:bg-slate-800/80 shadow-sm p-3 sm:p-4">
            <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-2">
                <i class="bi bi-lightning-charge text-indigo-500"></i> Atalhos
            </h2>
            <div class="grid grid-cols-6 lg:grid-cols-3 gap-1 sm:gap-2">
                @foreach($atalhos as [$label, $icon, $route, $grad])
                    <a href="{{ route($route) }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-xl py-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition text-center">
                        <span class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-md">
                            <i class="bi {{ $icon }} text-lg"></i>
                        </span>
                        <span class="text-[10px] sm:text-[11px] font-semibold text-slate-600 dark:text-slate-300 leading-tight">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    {{-- ============ NÚMEROS (4 no celular, o resto ao tocar em "Ver todos") ============ --}}
    <div x-data="{ todos: false }" class="mb-1">
        <div class="dash-kpis ini-kpis" :class="todos && 'ini-kpis-todos'">
            <x-dash.kpi label="Faturamento do mês" tone="emerald" icon="bi-cash-coin" :value="$fmt($faturamentoMes ?? 0)" :delta="round($taxaCrescimento ?? 0)" />
            <x-dash.kpi label="A receber" tone="amber" icon="bi-hourglass-split" :value="$fmt($contasReceberPendentes ?? 0)" />
            <x-dash.kpi label="A pagar" tone="rose" icon="bi-credit-card" :value="$fmt($contasPagarPendentes ?? 0)" />
            <x-dash.kpi label="Saldo em caixa" tone="teal" icon="bi-wallet2" :value="$fmt($saldoCaixa ?? 0)" />
            <x-dash.kpi class="ini-kpi-extra" label="Vendas no mês" tone="indigo" icon="bi-bag-check" :value="$salesMonth ?? 0" countup />
            <x-dash.kpi class="ini-kpi-extra" label="Ticket médio" tone="purple" icon="bi-receipt" :value="$fmt($ticketMedio ?? 0)" />
            <x-dash.kpi class="ini-kpi-extra" label="Lucro líquido" tone="emerald" icon="bi-graph-up-arrow" :value="$fmt($lucroLiquido ?? 0)" />
            <x-dash.kpi class="ini-kpi-extra" label="Faturamento total" tone="sky" icon="bi-cash-stack" :value="$fmt($totalFaturamento ?? 0)" />
            <x-dash.kpi class="ini-kpi-extra" label="Produtos" tone="blue" icon="bi-box-seam" :value="$totalProdutos ?? 0" countup />
            <x-dash.kpi class="ini-kpi-extra" label="Clientes" tone="slate" icon="bi-people" :value="$totalClientes ?? 0" countup />
        </div>
        <button type="button" @click="todos = !todos" class="ini-kpis-btn w-full mt-2 py-2 rounded-xl text-xs font-semibold text-indigo-600 dark:text-indigo-300 bg-indigo-500/10">
            <span x-show="!todos">Ver todos os números</span><span x-show="todos" x-cloak>Mostrar menos</span>
        </button>
    </div>

    {{-- ============ GRID DE CONTEÚDO (denso) ============ --}}
    <div class="dash-grid" x-data="{ mais: window.matchMedia('(min-width: 768px)').matches }" x-init="$nextTick(() => window.dashInitCharts && window.dashInitCharts())">

        {{-- Receita 14 dias (área gradiente) --}}
        <x-dash.card title="Receita — últimos 14 dias" sub="Total por dia" icon="bi-graph-up" tone="indigo" span="dash-col-8">
            @if(array_sum($ov['revenueSeries']) > 0)
                <x-dash.chart id="dashRevenueChart" :currency="true" type="area"
                    :series="[['name' => 'Receita', 'data' => $ov['revenueSeries']]]"
                    :labels="$ov['revenueLabels']"
                    :colors="['#6366f1']" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem vendas nos últimos 14 dias" />
            @endif
        </x-dash.card>

        {{-- Fluxo de caixa mensal (receitas x despesas) --}}
        <x-dash.card title="Fluxo de caixa" sub="Receitas x Despesas por mês" icon="bi-bar-chart-line" tone="emerald" span="dash-col-4">
            @if(!empty($cfLabels))
                <x-dash.chart id="dashCashflowChart" :currency="true" type="bar"
                    :series="[['name' => 'Receitas', 'data' => $cfReceitas], ['name' => 'Despesas', 'data' => $cfDespesas]]"
                    :labels="$cfLabels"
                    :colors="['#10b981','#f43f5e']" />
            @else
                <x-dash.empty icon="bi-bar-chart" message="Sem dados de fluxo de caixa" />
            @endif
        </x-dash.card>

        <template x-if="mais">
        {{-- Vendas por status (donut) --}}
        <x-dash.card title="Vendas por status" sub="Distribuição" icon="bi-pie-chart" tone="purple" span="dash-col-4">
            @if(array_sum($ov['statusSeries']) > 0)
                <x-dash.chart id="dashStatusChart" type="donut"
                    :series="$ov['statusSeries']"
                    :labels="$ov['statusLabels']"
                    :colors="['#22c55e','#facc15','#10b981','#f87171']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Nenhuma venda registrada" />
            @endif
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Despesas por categoria (donut) --}}
        <x-dash.card title="Despesas por categoria" sub="Para onde vai o dinheiro" icon="bi-pie-chart-fill" tone="rose" span="dash-col-4">
            @if(array_sum($ecSeries) > 0)
                <x-dash.chart id="dashExpCatChart" :currency="true" type="donut"
                    :series="$ecSeries"
                    :labels="$ecLabels"
                    :colors="['#f43f5e','#f59e0b','#6366f1','#14b8a6','#8b5cf6','#0ea5e9','#64748b']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem despesas categorizadas" />
            @endif
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Comparativo de períodos (barras) --}}
        <x-dash.card title="Comparativo de períodos" sub="Atual x anterior x ano passado" icon="bi-clipboard-data" tone="blue" span="dash-col-4">
            @if(!empty($pc['labels']))
                <x-dash.chart id="dashComparisonChart" :currency="true" type="bar"
                    :series="[['name' => 'Receitas', 'data' => $pc['income'] ?? []], ['name' => 'Despesas', 'data' => $pc['expenses'] ?? []]]"
                    :labels="$pc['labels']"
                    :colors="['#10b981','#f43f5e']" />
            @else
                <x-dash.empty icon="bi-clipboard-data" message="Sem comparativo disponível" />
            @endif
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Orçamento do mês (progresso) --}}
        <x-dash.card title="Orçamento do mês" sub="Usado x planejado" icon="bi-speedometer2" tone="amber" span="dash-col-4">
            @if($orcTotal > 0)
                <div class="flex flex-col gap-2 py-1">
                    <div class="flex items-end justify-between">
                        <span class="text-lg font-black text-slate-800 dark:text-white">{{ $orcPct }}%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $fmt($orcUsado) }} / {{ $fmt($orcTotal) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-slate-200/70 dark:bg-slate-700/60 overflow-hidden">
                        <div class="h-full rounded-full {{ $orcPct >= 100 ? 'bg-rose-500' : ($orcPct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $orcPct }}%"></div>
                    </div>
                    @if(!empty($orcamentosTopEstouro))
                        <div class="dash-list mt-1">
                            @foreach(array_slice($orcamentosTopEstouro, 0, 3) as $oe)
                                <x-dash.list-item :title="$oe['category'] ?? 'Categoria'" sub="Acima do orçado" icon="bi-arrow-up-right" tone="rose" :value="$fmt($oe['estouro'] ?? 0)" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <x-dash.empty icon="bi-speedometer2" message="Nenhum orçamento definido" />
            @endif
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Destaques --}}
        <x-dash.card title="Destaques" sub="Indicadores do período" icon="bi-trophy" tone="rose" span="dash-col-4">
            <div class="dash-list">
                @if($produtoMaisVendido)
                    <x-dash.list-item
                        :title="$produtoMaisVendido->name ?? 'Produto'"
                        sub="Mais vendido"
                        icon="bi-star-fill" tone="amber"
                        :value="(int)($produtoMaisVendido->total_vendido ?? 0) . 'x'" />
                @endif
                <x-dash.list-item title="Novos clientes" sub="No mês atual" icon="bi-person-plus-fill" tone="emerald" :value="$clientesNovosMes ?? 0" trend="up" />
                <x-dash.list-item title="Produtos vendidos" sub="No mês" icon="bi-box-seam-fill" tone="indigo" :value="$produtosVendidosMes ?? 0" />
                <x-dash.list-item title="Estoque baixo" sub="Produtos a repor" icon="bi-exclamation-triangle-fill" tone="rose" :value="$produtosEstoqueBaixo ?? 0" :trend="($produtosEstoqueBaixo ?? 0) > 0 ? 'down' : null" />
            </div>
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Atividades recentes --}}
        <x-dash.card title="Atividades recentes" sub="Últimas movimentações" icon="bi-activity" tone="sky" span="dash-col-4">
            @if(!empty($atividades))
                <div class="dash-list dash-scroll max-h-[240px] overflow-y-auto pr-1">
                    @foreach(array_slice($atividades, 0, 8) as $a)
                        <a href="{{ $a['link'] ?? '#' }}" class="block">
                            <x-dash.list-item
                                :title="$a['title'] ?? 'Atividade'"
                                :sub="($a['module'] ?? '') . ' · ' . ($a['time'] ?? '')"
                                icon="bi-dot"
                                tone="indigo" />
                        </a>
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-activity" message="Nenhuma atividade recente" />
            @endif
        </x-dash.card>
        </template>

        <template x-if="mais">
        {{-- Resumo financeiro rápido --}}
        <x-dash.card title="Resumo financeiro" sub="Contas e reservas" icon="bi-bank" tone="teal" span="dash-col-12">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">A receber</p>
                    <p class="text-sm font-black text-emerald-700 dark:text-emerald-200">{{ $fmt($contasReceberPendentes ?? 0) }}</p>
                </div>
                <div class="rounded-xl bg-rose-500/10 border border-rose-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-300">A pagar</p>
                    <p class="text-sm font-black text-rose-700 dark:text-rose-200">{{ $fmt($contasPagarPendentes ?? 0) }}</p>
                </div>
                <div class="rounded-xl bg-indigo-500/10 border border-indigo-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Reservas</p>
                    <p class="text-sm font-black text-indigo-700 dark:text-indigo-200">{{ $fmt($totalEconomizado ?? 0) }}</p>
                </div>
                <div class="rounded-xl bg-amber-500/10 border border-amber-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-300">Parcelas vencidas</p>
                    <p class="text-sm font-black text-amber-700 dark:text-amber-200">{{ $parcelasVencidasCount ?? 0 }}</p>
                </div>
            </div>
        </x-dash.card>
        </template>

        <button type="button" x-show="!mais" @click="mais = true; $nextTick(() => window.dashInitCharts && window.dashInitCharts())" class="ini-mais-btn dash-col-12 w-full py-2.5 rounded-xl text-sm font-semibold text-indigo-600 dark:text-indigo-300 bg-white/90 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/60">
            <i class="bi bi-bar-chart-line mr-1"></i> Ver mais gráficos e listas
        </button>
    </div>
</div>
