<div class="dashboard-cashbook-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $rec = $dadosReceita ?? [];
        $des = $dadosDespesa ?? [];
        // Séries vêm de janeiro a dezembro do ano escolhido; mostra até o mês escolhido.
        $ate = max(1, (int) $mes);
        $rec = array_slice(array_map('floatval', $rec), 0, $ate);
        $des = array_slice(array_map('floatval', $des), 0, $ate);
        $mesesLabels = [];
        for ($i = 1; $i <= $ate; $i++) { $mesesLabels[] = ucfirst(\Carbon\Carbon::create((int) $ano, $i, 1)->locale('pt_BR')->translatedFormat('M')); }
        $pctDelta = fn($atual, $anterior) => $anterior > 0 ? (int) round((($atual - $anterior) / $anterior) * 100) : null;
        $deltaRec = $pctDelta($receitaMesAtual ?? 0, $receitaMesAnterior ?? 0);
        $deltaDes = $pctDelta($despesaMesAtual ?? 0, $despesaMesAnterior ?? 0);
        $catLabels = $categorias ?? [];
        $catValues = $valoresCategorias ?? [];
    @endphp

    {{-- HEADER --}}
    <x-dash.page-header title="Financeiro" active="financeiro" icon="bi-wallet2" gradient="from-emerald-500 via-teal-500 to-green-600"
        subtitle="Caixa, receitas, despesas e reservas">
        <x-slot:period>
            <x-dash.month-nav :label="$periodLabel ?: ucfirst(now()->translatedFormat('F/Y'))"
                :can-next="\Carbon\Carbon::create((int) $ano, (int) $mes, 1)->lt(now()->startOfMonth())" />
        </x-slot:period>
    </x-dash.page-header>

    {{-- KPIs --}}
    <div class="dash-kpis dash-kpis-8">
        <x-dash.kpi label="Saldo do caixa" tone="emerald" icon="bi-cash-stack" :value="$fmt($saldoTotal ?? 0)" />
        <x-dash.kpi label="Receitas no mês" tone="teal" icon="bi-arrow-down-circle" :value="$fmt($receitaMesAtual ?? 0)" :delta="$deltaRec" />
        <x-dash.kpi label="Despesas no mês" tone="rose" icon="bi-arrow-up-circle" :value="$fmt($despesaMesAtual ?? 0)" :delta="$deltaDes" />
        <x-dash.kpi label="Resultado do mês" :tone="($saldoMesAtual ?? 0) >= 0 ? 'indigo' : 'amber'" icon="bi-calculator" :value="$fmt($saldoMesAtual ?? 0)" />
        <x-dash.kpi label="Receitas no ano" tone="sky" icon="bi-calendar-check" :value="$fmt($totalReceitas ?? 0)" />
        <x-dash.kpi label="Despesas no ano" tone="amber" icon="bi-calendar-x" :value="$fmt($totalDespesas ?? 0)" />
        <x-dash.kpi label="Cofrinhos" tone="purple" icon="bi-piggy-bank" :value="$fmt($totalCofrinhos ?? 0)" />
        <x-dash.kpi label="Previsão 30 dias" tone="blue" icon="bi-graph-up" :value="$fmt($previsao30dias ?? 0)" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Fluxo de caixa (receita vs despesa) --}}
        <x-dash.card title="Fluxo de caixa" :sub="'Receitas e despesas por mês em ' . $ano" icon="bi-bar-chart-line" tone="emerald" span="dash-col-8">
            @if(array_sum($rec) > 0 || array_sum($des) > 0)
                <x-dash.chart id="dashCashflowChart" type="area"
                    :series="[['name'=>'Receitas','data'=>$rec], ['name'=>'Despesas','data'=>$des]]"
                    :labels="$mesesLabels"
                    :colors="['#10b981','#f43f5e']" :currency="true" />
            @else
                <x-dash.empty icon="bi-bar-chart" message="Sem lançamentos neste ano" />
            @endif
        </x-dash.card>

        {{-- Despesas por categoria (donut) --}}
        <x-dash.card title="Compras no cartão" :sub="'Por categoria em ' . $ano" icon="bi-pie-chart" tone="rose" span="dash-col-4">
            @if(!empty($catValues) && array_sum($catValues) > 0)
                <x-dash.chart id="dashCatChart" type="donut" :currency="true" :series="$catValues" :labels="$catLabels"
                    :colors="['#6366f1','#f43f5e','#f59e0b','#10b981','#0ea5e9','#8b5cf6']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem compras no cartão neste ano" />
            @endif
        </x-dash.card>

        {{-- Últimos lançamentos --}}
        <x-dash.card title="Últimos lançamentos" sub="Caixa e cartões" icon="bi-clock-history" tone="indigo" span="dash-col-6">
            @if(!empty($recentTransactions))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice($recentTransactions, 0, 8) as $t)
                        @php
                            $isEntrada = (int) ($t['type_id'] ?? 0) === 1;
                            $valor = $t['value'] ?? 0;
                            $desc = $t['description'] ?: 'Lançamento';
                            $data = !empty($t['date']) ? \Carbon\Carbon::parse($t['date'])->format('d/m/Y') : '';
                            $origem = ($t['origin'] ?? '') === 'invoice' ? 'Cartão' : 'Caixa';
                            $when = trim($data . ' · ' . $origem . (!empty($t['meta']) ? ' · ' . $t['meta'] : ''), ' ·');
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
        <x-dash.card title="Cofrinhos" sub="Mais perto da meta" icon="bi-piggy-bank" tone="purple" span="dash-col-6">
            @if(!empty($cofrinhosTopMeta))
                <div class="dash-list">
                    @foreach(array_slice($cofrinhosTopMeta, 0, 5) as $c)
                        @php
                            $nome = $c['nome'] ?? $c['name'] ?? 'Cofrinho';
                            $atual = (float) ($c['valor_guardado'] ?? 0);
                            $meta = (float) ($c['meta_valor'] ?? 0);
                            $pct = $meta > 0 ? min(100, round(($atual / $meta) * 100)) : 0;
                        @endphp
                        <a href="{{ $c['link'] ?? '#' }}" wire:navigate class="block"><x-dash.list-item :title="$nome" :sub="$meta > 0 ? $pct . '% de ' . $fmt($meta) : 'Sem meta definida'" icon="bi-bullseye" tone="purple" :value="$fmt($atual)" /></a>
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
