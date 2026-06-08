<div class="dashboard-sales-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $sc = $this->buildSalesCharts();
    @endphp

    {{-- HEADER --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-purple-50/70 to-pink-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-purple-400/20 to-pink-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-500 via-fuchsia-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30 shrink-0"><i class="bi bi-graph-up-arrow text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">Vendas</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Faturamento, conversão e ranking · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Faturamento" tone="emerald" icon="bi-cash-coin" :value="$fmt($totalFaturamento ?? 0)" :delta="round($crescimentoMensal ?? 0)" />
        <x-dash.kpi label="Vendas hoje" tone="indigo" icon="bi-calendar-day" :value="$vendasHoje ?? 0" countup />
        <x-dash.kpi label="Total vendas" tone="purple" icon="bi-bag-check" :value="$totalVendas ?? 0" countup />
        <x-dash.kpi label="Ticket médio" tone="sky" icon="bi-receipt" :value="$fmt($ticketMedio ?? 0)" />
        <x-dash.kpi label="A receber" tone="amber" icon="bi-hourglass-split" :value="$fmt($totalFaltante ?? 0)" />
        <x-dash.kpi label="Conversão" tone="teal" icon="bi-funnel" :value="number_format($taxaConversao ?? 0, 1) . '%'" />
        <x-dash.kpi label="Itens vendidos" tone="blue" icon="bi-boxes" :value="$totalProdutosVendidos ?? 0" countup />
        <x-dash.kpi label="Retenção" tone="rose" icon="bi-arrow-repeat" :value="number_format($taxaRetencao ?? 0, 1) . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Vendas por dia --}}
        <x-dash.card title="Vendas — últimos 14 dias" sub="Faturamento por dia" icon="bi-graph-up" tone="purple" span="dash-col-8">
            @if(array_sum($sc['daySeries']) > 0)
                <x-dash.chart id="dashSalesDayChart" type="area"
                    :series="[['name'=>'Vendas','data'=>$sc['daySeries']]]" :labels="$sc['dayLabels']"
                    :colors="['#a855f7']" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Sem vendas nos últimos 14 dias" />
            @endif
        </x-dash.card>

        {{-- Forma de pagamento (donut) --}}
        <x-dash.card title="Forma de pagamento" sub="À vista vs parcelado" icon="bi-credit-card" tone="indigo" span="dash-col-4">
            @if(array_sum($sc['paySeries']) > 0)
                <x-dash.chart id="dashPayChart" type="donut" :series="$sc['paySeries']" :labels="$sc['payLabels']"
                    :colors="['#10b981','#6366f1']" />
            @else
                <x-dash.empty icon="bi-credit-card" message="Nenhuma venda" />
            @endif
        </x-dash.card>

        {{-- Marketplaces --}}
        <x-dash.card title="Marketplaces" sub="Mercado Livre & Shopee" icon="bi-shop" tone="amber" span="dash-col-6">
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-yellow-500/10 border border-yellow-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase text-yellow-600 dark:text-yellow-300"><i class="bi bi-shop"></i> Mercado Livre</p>
                    <p class="text-sm font-black text-yellow-700 dark:text-yellow-200">{{ $fmt($mlRevenue ?? 0) }}</p>
                    <p class="text-[10px] text-slate-500">{{ $mlOrdersCount ?? 0 }} pedidos · {{ $mlPublicationsAtivas ?? 0 }} anúncios</p>
                </div>
                <div class="rounded-xl bg-orange-500/10 border border-orange-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase text-orange-600 dark:text-orange-300"><i class="bi bi-bag-heart"></i> Shopee</p>
                    <p class="text-sm font-black text-orange-700 dark:text-orange-200">{{ $fmt($shopeeRevenue ?? 0) }}</p>
                    <p class="text-[10px] text-slate-500">{{ $shopeeOrdersCount ?? 0 }} pedidos</p>
                </div>
            </div>
        </x-dash.card>

        {{-- Indicadores --}}
        <x-dash.card title="Indicadores" sub="Performance comercial" icon="bi-speedometer2" tone="teal" span="dash-col-6">
            <div class="dash-list">
                <x-dash.list-item title="CLV médio" sub="Valor por cliente" icon="bi-gem" tone="purple" :value="$fmt($clvMedio ?? 0)" />
                <x-dash.list-item title="Crescimento anual" sub="vs ano anterior" icon="bi-graph-up-arrow" tone="emerald" :value="number_format($crescimentoAnual ?? 0, 1) . '%'" :trend="($crescimentoAnual ?? 0) >= 0 ? 'up' : 'down'" />
                <x-dash.list-item title="Ticket recorrente" sub="Clientes fiéis" icon="bi-arrow-repeat" tone="indigo" :value="$fmt($ticketMedioRecorrente ?? 0)" />
                <x-dash.list-item title="Clientes pendentes" sub="Com saldo a pagar" icon="bi-person-exclamation" tone="amber" :value="$clientesComSalesPendentes ?? 0" />
            </div>
        </x-dash.card>
    </div>
</div>
