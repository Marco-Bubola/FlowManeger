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

    {{-- ============ KPIs (8 no desktop) ============ --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Faturamento" tone="emerald" icon="bi-cash-coin" :value="$fmt($totalFaturamento ?? 0)" :delta="round($taxaCrescimento ?? 0)" countup />
        <x-dash.kpi label="Vendas no mês" tone="indigo" icon="bi-bag-check" :value="$salesMonth ?? 0" countup />
        <x-dash.kpi label="Ticket médio" tone="purple" icon="bi-receipt" :value="$fmt($ticketMedio ?? 0)" />
        <x-dash.kpi label="A receber" tone="amber" icon="bi-hourglass-split" :value="$fmt($contasReceberPendentes ?? 0)" />
        <x-dash.kpi label="Produtos" tone="sky" icon="bi-box-seam" :value="$totalProdutos ?? 0" countup />
        <x-dash.kpi label="Clientes" tone="blue" icon="bi-people" :value="$totalClientes ?? 0" countup />
        <x-dash.kpi label="Saldo em caixa" tone="teal" icon="bi-wallet2" :value="$fmt($saldoCaixa ?? 0)" />
        <x-dash.kpi label="Lucro líquido" tone="rose" icon="bi-graph-up-arrow" :value="$fmt($lucroLiquido ?? 0)" :delta="round($margemLucro ?? 0)" />
    </div>

    {{-- ============ GRID DE CONTEÚDO ============ --}}
    <div class="dash-grid">

        {{-- Receita 14 dias (área gradiente) --}}
        <x-dash.card title="Receita — últimos 14 dias" sub="Total por dia" icon="bi-graph-up" tone="indigo" span="dash-col-8">
            @if(array_sum($ov['revenueSeries']) > 0)
                <x-dash.chart id="dashRevenueChart" type="area"
                    :series="[['name' => 'Receita', 'data' => $ov['revenueSeries']]]"
                    :labels="$ov['revenueLabels']"
                    :colors="['#6366f1']" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem vendas nos últimos 14 dias" />
            @endif
        </x-dash.card>

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

        {{-- Atividades recentes --}}
        <x-dash.card title="Atividades recentes" sub="Últimas movimentações" icon="bi-activity" tone="sky" span="dash-col-6">
            @if(!empty($atividades))
                <div class="dash-list dash-scroll max-h-[280px] overflow-y-auto pr-1">
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

        {{-- Alertas --}}
        <x-dash.card title="Alertas" sub="Itens que precisam de atenção" icon="bi-exclamation-triangle" tone="amber" span="dash-col-6">
            @if(!empty($alertas))
                <div class="dash-list">
                    @foreach($alertas as $al)
                        @php
                            $tone = ($al['type'] ?? '') === 'danger' ? 'rose' : (($al['type'] ?? '') === 'warning' ? 'amber' : 'sky');
                        @endphp
                        <a href="{{ $al['link'] ?? '#' }}" class="block">
                            <x-dash.list-item :title="$al['message'] ?? 'Alerta'" icon="bi-bell-fill" :tone="$tone" />
                        </a>
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-check-circle" message="Tudo certo! Nenhum alerta." />
            @endif
        </x-dash.card>

        {{-- Resumo financeiro rápido --}}
        <x-dash.card title="Resumo financeiro" sub="Contas e reservas" icon="bi-bank" tone="teal" span="dash-col-6">
            <div class="grid grid-cols-2 gap-2">
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

        {{-- Destaques --}}
        <x-dash.card title="Destaques" sub="Indicadores do período" icon="bi-trophy" tone="rose" span="dash-col-6">
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
    </div>
</div>
