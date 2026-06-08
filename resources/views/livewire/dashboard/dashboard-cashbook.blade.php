<div class="dashboard-cashbook-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $rec = $dadosReceita ?? [];
        $des = $dadosDespesa ?? [];
        $n = max(count($rec), count($des));
        $mesesLabels = [];
        for ($i = $n - 1; $i >= 0; $i--) { $mesesLabels[] = now()->subMonths($i)->translatedFormat('M/y'); }
        $catLabels = $categorias ?? [];
        $catValues = $valoresCategorias ?? [];
    @endphp

    {{-- HEADER --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-emerald-50/70 to-teal-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-emerald-400/20 to-teal-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 shrink-0"><i class="bi bi-wallet2 text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">Financeiro</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Caixa, receitas, despesas e reservas · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Saldo total" tone="emerald" icon="bi-cash-stack" :value="$fmt($saldoTotal ?? 0)" />
        <x-dash.kpi label="Receitas" tone="teal" icon="bi-arrow-down-circle" :value="$fmt($totalReceitas ?? 0)" />
        <x-dash.kpi label="Despesas" tone="rose" icon="bi-arrow-up-circle" :value="$fmt($totalDespesas ?? 0)" />
        <x-dash.kpi label="Receita mês" tone="indigo" icon="bi-calendar-check" :value="$fmt($receitaMesAtual ?? 0)" />
        <x-dash.kpi label="Despesa mês" tone="amber" icon="bi-calendar-x" :value="$fmt($despesaMesAtual ?? 0)" />
        <x-dash.kpi label="Reservas" tone="purple" icon="bi-piggy-bank" :value="$fmt($totalCofrinhos ?? 0)" />
        <x-dash.kpi label="Previsão 30d" tone="sky" icon="bi-graph-up" :value="$fmt($previsao30dias ?? 0)" />
        <x-dash.kpi label="Orçamento usado" tone="blue" icon="bi-pie-chart" :value="number_format($orcamentoUsoPercentual ?? 0, 0) . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Fluxo de caixa (receita vs despesa) --}}
        <x-dash.card title="Fluxo de caixa" sub="Receitas vs Despesas" icon="bi-bar-chart-line" tone="emerald" span="dash-col-8">
            @if(array_sum($rec) > 0 || array_sum($des) > 0)
                <x-dash.chart id="dashCashflowChart" type="area"
                    :series="[['name'=>'Receitas','data'=>$rec], ['name'=>'Despesas','data'=>$des]]"
                    :labels="$mesesLabels"
                    :colors="['#10b981','#f43f5e']" />
            @else
                <x-dash.empty icon="bi-bar-chart" message="Sem movimentações no período" />
            @endif
        </x-dash.card>

        {{-- Despesas por categoria (donut) --}}
        <x-dash.card title="Despesas por categoria" sub="Distribuição" icon="bi-pie-chart" tone="rose" span="dash-col-4">
            @if(!empty($catValues) && array_sum($catValues) > 0)
                <x-dash.chart id="dashCatChart" type="donut" :series="$catValues" :labels="$catLabels"
                    :colors="['#6366f1','#f43f5e','#f59e0b','#10b981','#0ea5e9','#8b5cf6']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem categorias de despesa" />
            @endif
        </x-dash.card>

        {{-- Últimos lançamentos --}}
        <x-dash.card title="Últimos lançamentos" sub="Movimentações recentes" icon="bi-clock-history" tone="indigo" span="dash-col-6">
            @if(!empty($recentTransactions))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice($recentTransactions, 0, 8) as $t)
                        @php
                            $isEntrada = in_array(strtolower((string)($t['type'] ?? $t['tipo'] ?? '')), ['entrada','receita','credito','credit','income']);
                            $valor = $t['amount'] ?? $t['valor'] ?? 0;
                            $desc = $t['description'] ?? $t['descricao'] ?? $t['name'] ?? 'Lançamento';
                            $when = $t['date'] ?? $t['data'] ?? '';
                        @endphp
                        <x-dash.list-item :title="$desc" :sub="(string)$when"
                            :icon="$isEntrada ? 'bi-arrow-down-circle-fill' : 'bi-arrow-up-circle-fill'"
                            :tone="$isEntrada ? 'emerald' : 'rose'"
                            :value="$fmt($valor)" :trend="$isEntrada ? 'up' : 'down'" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-clock-history" message="Nenhum lançamento recente" />
            @endif
        </x-dash.card>

        {{-- Reservas / Cofrinhos --}}
        <x-dash.card title="Reservas & Metas" sub="Cofrinhos em destaque" icon="bi-piggy-bank" tone="purple" span="dash-col-6">
            @if(!empty($cofrinhosTopMeta))
                <div class="dash-list">
                    @foreach(array_slice($cofrinhosTopMeta, 0, 5) as $c)
                        @php
                            $nome = $c['nome'] ?? $c['name'] ?? 'Cofrinho';
                            $atual = $c['atual'] ?? $c['saldo'] ?? $c['valor'] ?? 0;
                            $meta = $c['meta'] ?? $c['objetivo'] ?? 0;
                            $pct = $meta > 0 ? min(100, round(($atual / $meta) * 100)) : 0;
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="$pct . '% da meta'" icon="bi-bullseye" tone="purple" :value="$fmt($atual)" />
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-3 py-2.5">
                        <p class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-300">Economizado mês</p>
                        <p class="text-sm font-black text-emerald-700 dark:text-emerald-200">{{ $fmt($economiadoMesAtual ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-purple-500/10 border border-purple-400/30 px-3 py-2.5">
                        <p class="text-[10px] font-bold uppercase text-purple-600 dark:text-purple-300">Total reservas</p>
                        <p class="text-sm font-black text-purple-700 dark:text-purple-200">{{ $fmt($totalCofrinhos ?? 0) }}</p>
                    </div>
                </div>
            @endif
        </x-dash.card>
    </div>
</div>
