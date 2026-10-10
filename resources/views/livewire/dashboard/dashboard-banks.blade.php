<div class="dashboard-banks-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');

        // Distribuição por banco (donut)
        $bankLabels = [];
        foreach (($topBanks ?? []) as $b) {
            if (is_string($b)) { $bankLabels[] = $b; }
            elseif (is_array($b)) { $bankLabels[] = $b['name'] ?? $b['nome'] ?? 'Banco'; }
            else { $bankLabels[] = $b->name ?? 'Banco'; }
        }
        $bankValues = array_map(fn($v) => (float)$v, $topBanksMonthValues ?? []);

        // Categorias de fatura (donut)
        $icsLabels = []; $icsValues = [];
        foreach (($invoiceCategoryShare ?? []) as $row) {
            $icsLabels[] = $row['categoria'] ?? $row['label'] ?? $row['name'] ?? 'Outros';
            $icsValues[] = (float)($row['value'] ?? $row['total'] ?? $row['valor'] ?? $row['amount'] ?? 0);
        }
    @endphp

    @php
        $cartoes = collect($bancosInfo ?? []);
        $abertoTotal = (float) $cartoes->sum('cycle_total');
        $proximo = $cartoes->sortBy('days_to_close')->first();
        $topCat = $invoiceCategoryShare[0] ?? null;
    @endphp

    {{-- HEADER --}}
    <x-dash.page-header title="Bancos e cartões" active="bancos" icon="bi-bank" gradient="from-blue-500 via-indigo-500 to-violet-500"
        subtitle="Gastos no cartão, faturas abertas e fechamento">
        <x-slot:period>
            <x-dash.month-nav :label="$periodLabel ?: ucfirst(now()->translatedFormat('F/Y'))"
                :can-next="\Carbon\Carbon::create((int) $ano, (int) $mes, 1)->lt(now()->startOfMonth())" />
        </x-slot:period>
    </x-dash.page-header>

    {{-- KPIs --}}
    <div class="dash-kpis dash-kpis-8">
        <x-dash.kpi label="Gasto no mês" tone="amber" icon="bi-calendar-month" :value="$fmt($monthTotal ?? 0)" />
        <x-dash.kpi label="Compras no mês" tone="indigo" icon="bi-bag" :value="$monthCount ?? 0" countup />
        <x-dash.kpi label="Faturas abertas" tone="rose" icon="bi-receipt" :value="$fmt($abertoTotal)" />
        <x-dash.kpi label="Média 6 meses" tone="sky" icon="bi-graph-up" :value="$fmt($avgMonth ?? 0)" />
        <x-dash.kpi label="Média por dia" tone="teal" icon="bi-calendar-day" :value="$fmt($monthDailyAverage ?? 0)" />
        <x-dash.kpi label="Próx. fechamento" tone="blue" icon="bi-clock" :value="$proximo ? ((int) $proximo['days_to_close'] === 0 ? 'Hoje' : (int) $proximo['days_to_close'] . ' dias') : '—'" />
        <x-dash.kpi label="Cartões usados" tone="purple" icon="bi-credit-card" :value="($activeBanksMonth ?? 0) . ' de ' . ($totalBancos ?? $cartoes->count())" />
        <x-dash.kpi label="Maior categoria" tone="emerald" icon="bi-tags" :value="$topCat['categoria'] ?? $topCat['label'] ?? '—'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Tendência de gastos --}}
        <x-dash.card title="Tendência de gastos" sub="Total dos cartões nos últimos 6 meses" icon="bi-graph-up" tone="blue" span="dash-col-8">
            @if(!empty($trendValues) && array_sum($trendValues) > 0)
                <x-dash.chart id="dashBankTrendChart" type="area"
                    :series="[['name'=>'Gastos','data'=>array_map(fn($v)=>(float)$v, $trendValues)]]"
                    :labels="$trendLabels ?? []" :colors="['#3b82f6']" :currency="true" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem histórico de gastos" />
            @endif
        </x-dash.card>

        {{-- Distribuição por banco (donut) --}}
        <x-dash.card title="Por banco" sub="Gasto no mês" icon="bi-pie-chart" tone="indigo" span="dash-col-4">
            @if(!empty($bankValues) && array_sum($bankValues) > 0)
                <x-dash.chart id="dashBankShareChart" type="donut" :currency="true" :series="$bankValues" :labels="$bankLabels"
                    :colors="['#3b82f6','#8b5cf6','#0ea5e9','#6366f1','#10b981','#f59e0b']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem dados por banco" />
            @endif
        </x-dash.card>

        {{-- Categorias de fatura (donut) --}}
        <x-dash.card title="Gastos por categoria" sub="Compras no mês" icon="bi-tags" tone="purple" span="dash-col-6">
            @if(!empty($icsValues) && array_sum($icsValues) > 0)
                <x-dash.chart id="dashBankCatChart" type="donut" :currency="true" :series="$icsValues" :labels="$icsLabels"
                    :colors="['#6366f1','#f43f5e','#f59e0b','#10b981','#0ea5e9','#8b5cf6']" />
            @else
                <x-dash.empty icon="bi-tags" message="Sem categorias de fatura" />
            @endif
        </x-dash.card>

        {{-- Cartões: fatura aberta e fechamento --}}
        <x-dash.card title="Cartões" sub="Fatura aberta e fechamento" icon="bi-credit-card-2-front" tone="sky" span="dash-col-6">
            @if($cartoes->isNotEmpty())
                <div class="dash-list dash-scroll max-h-[280px] overflow-y-auto pr-1">
                    @foreach($cartoes as $c)
                        @php $dias = (int) ($c['days_to_close'] ?? 0); @endphp
                        <a href="{{ $c['link'] ?? '#' }}" wire:navigate class="block">
                            <x-dash.list-item :title="$c['nome'] ?? 'Cartão'"
                                :sub="'Ciclo ' . ($c['cycle_start'] ?? '') . ' a ' . ($c['cycle_end'] ?? '') . ' · ' . ($dias === 0 ? 'fecha hoje' : 'fecha em ' . $dias . ' dias')"
                                icon="bi-credit-card-fill" tone="sky" :value="$fmt($c['cycle_total'] ?? 0)" />
                        </a>
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-credit-card" message="Nenhum cartão cadastrado" />
            @endif
        </x-dash.card>
    </div>
</div>
