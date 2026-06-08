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
            $icsLabels[] = $row['label'] ?? $row['name'] ?? $row['categoria'] ?? 'Outros';
            $icsValues[] = (float)($row['value'] ?? $row['total'] ?? $row['valor'] ?? $row['amount'] ?? 0);
        }
    @endphp

    {{-- HEADER --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-blue-50/70 to-indigo-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-blue-400/20 to-indigo-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 via-indigo-500 to-violet-500 flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0"><i class="bi bi-bank text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">Bancos & Cartões</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Faturas, gastos e ciclos · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Saldo bancos" tone="emerald" icon="bi-cash-stack" :value="$fmt($saldoTotalBancos ?? 0)" />
        <x-dash.kpi label="Total faturas" tone="indigo" icon="bi-receipt" :value="$fmt($totalInvoicesBancos ?? 0)" />
        <x-dash.kpi label="Saídas" tone="rose" icon="bi-arrow-up-circle" :value="$fmt($totalSaidasBancos ?? 0)" />
        <x-dash.kpi label="Gasto no mês" tone="amber" icon="bi-calendar-month" :value="$fmt($monthTotal ?? 0)" />
        <x-dash.kpi label="Média mensal" tone="sky" icon="bi-graph-up" :value="$fmt($avgMonth ?? 0)" />
        <x-dash.kpi label="Ticket ciclo" tone="purple" icon="bi-arrow-repeat" :value="$fmt($avgCycleAmount ?? 0)" />
        <x-dash.kpi label="Dias p/ fechar" tone="blue" icon="bi-clock" :value="number_format($avgDaysToClose ?? 0, 0) . 'd'" />
        <x-dash.kpi label="Sucesso upload" tone="teal" icon="bi-cloud-check" :value="number_format($uploadSuccessAverage ?? 0, 0) . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Tendência de gastos --}}
        <x-dash.card title="Tendência de gastos" sub="Evolução do consolidado" icon="bi-graph-up" tone="blue" span="dash-col-8">
            @if(!empty($trendValues) && array_sum($trendValues) > 0)
                <x-dash.chart id="dashBankTrendChart" type="area"
                    :series="[['name'=>'Gastos','data'=>array_map(fn($v)=>(float)$v, $trendValues)]]"
                    :labels="$trendLabels ?? []" :colors="['#3b82f6']" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem histórico de gastos" />
            @endif
        </x-dash.card>

        {{-- Distribuição por banco (donut) --}}
        <x-dash.card title="Por banco" sub="Gasto no mês" icon="bi-pie-chart" tone="indigo" span="dash-col-4">
            @if(!empty($bankValues) && array_sum($bankValues) > 0)
                <x-dash.chart id="dashBankShareChart" type="donut" :series="$bankValues" :labels="$bankLabels"
                    :colors="['#3b82f6','#8b5cf6','#0ea5e9','#6366f1','#10b981','#f59e0b']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem dados por banco" />
            @endif
        </x-dash.card>

        {{-- Categorias de fatura (donut) --}}
        <x-dash.card title="Gastos por categoria" sub="Distribuição das faturas" icon="bi-tags" tone="purple" span="dash-col-6">
            @if(!empty($icsValues) && array_sum($icsValues) > 0)
                <x-dash.chart id="dashBankCatChart" type="donut" :series="$icsValues" :labels="$icsLabels"
                    :colors="['#6366f1','#f43f5e','#f59e0b','#10b981','#0ea5e9','#8b5cf6']" />
            @else
                <x-dash.empty icon="bi-tags" message="Sem categorias de fatura" />
            @endif
        </x-dash.card>

        {{-- Uploads recentes --}}
        <x-dash.card title="Uploads recentes" sub="Faturas importadas" icon="bi-cloud-arrow-up" tone="sky" span="dash-col-6">
            @if(!empty($recentUploads))
                <div class="dash-list dash-scroll max-h-[280px] overflow-y-auto pr-1">
                    @foreach(array_slice($recentUploads, 0, 8) as $u)
                        @php
                            $banco = $u['bank'] ?? $u['banco'] ?? $u['name'] ?? 'Banco';
                            $when = $u['date'] ?? $u['data'] ?? $u['created_at'] ?? '';
                            $total = $u['total'] ?? $u['valor'] ?? $u['amount'] ?? null;
                        @endphp
                        <x-dash.list-item :title="$banco" :sub="(string)$when" icon="bi-file-earmark-arrow-up-fill" tone="sky" :value="$total !== null ? $fmt($total) : null" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-cloud-arrow-up" message="Nenhum upload recente" />
            @endif
        </x-dash.card>
    </div>
</div>
