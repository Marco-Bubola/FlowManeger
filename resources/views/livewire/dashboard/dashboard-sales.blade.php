<div class="dashboard-sales-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $pp = $periodo;
    @endphp

    {{-- HEADER --}}
    <x-dash.page-header title="Vendas" active="vendas" icon="bi-graph-up-arrow" gradient="from-purple-500 via-fuchsia-500 to-pink-500"
        subtitle="Faturamento, clientes e formas de pagamento">
        <x-slot:period>
            <x-dash.period-chips :options="\App\Livewire\Dashboard\DashboardSales::PERIODS" :current="$period" />
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $pp['label'] ?? '' }}</span>
        </x-slot:period>
    </x-dash.page-header>

    {{-- KPIs --}}
    <div class="dash-kpis dash-kpis-8">
        <x-dash.kpi label="Faturamento" tone="emerald" icon="bi-cash-coin" :value="$fmt($pp['faturamento'] ?? 0)" :delta="$pp['delta'] ?? null" />
        <x-dash.kpi label="Vendas" tone="purple" icon="bi-bag-check" :value="$pp['vendas'] ?? 0" countup />
        <x-dash.kpi label="Ticket médio" tone="sky" icon="bi-receipt" :value="$fmt($pp['ticket'] ?? 0)" />
        <x-dash.kpi label="Itens vendidos" tone="blue" icon="bi-boxes" :value="$pp['itens'] ?? 0" countup />
        <x-dash.kpi label="Clientes" tone="teal" icon="bi-people" :value="$pp['clientes'] ?? 0" countup />
        <x-dash.kpi label="Vendas hoje" tone="indigo" icon="bi-calendar-day" :value="$vendasHoje ?? 0" countup />
        <x-dash.kpi label="A receber" tone="amber" icon="bi-hourglass-split" :value="$fmt($totalFaltante ?? 0)" />
        <x-dash.kpi label="Recorrência" tone="rose" icon="bi-arrow-repeat" :value="number_format($taxaRetencao ?? 0, 1, ',', '.') . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Vendas por dia --}}
        <x-dash.card :title="$pp['chartTitle'] ?? 'Vendas'" sub="Faturamento no período" icon="bi-graph-up" tone="purple" span="dash-col-8">
            @if(array_sum($pp['serie'] ?? []) > 0)
                <x-dash.chart id="dashSalesDayChart" type="area"
                    :series="[['name'=>'Vendas','data'=>$pp['serie']]]" :labels="$pp['labels']"
                    :colors="['#a855f7']" :currency="true" />
            @else
                <x-dash.empty icon="bi-graph-up" message="Nenhuma venda neste período" />
            @endif
        </x-dash.card>

        {{-- Forma de pagamento (donut) --}}
        <x-dash.card title="Forma de pagamento" sub="Vendas no período" icon="bi-credit-card" tone="indigo" span="dash-col-4">
            @if(array_sum($pp['paySeries'] ?? []) > 0)
                <x-dash.chart id="dashPayChart" type="donut" :series="$pp['paySeries']" :labels="$pp['payLabels']"
                    :colors="['#10b981','#6366f1','#f59e0b','#0ea5e9']" />
            @else
                <x-dash.empty icon="bi-credit-card" message="Nenhuma venda neste período" />
            @endif
        </x-dash.card>

        {{-- Marketplaces --}}
        <x-dash.card title="Marketplaces" sub="Pedidos desde o início" icon="bi-shop" tone="amber" span="dash-col-6">
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
                <x-dash.list-item title="Crescimento anual" sub="vs ano anterior" icon="bi-graph-up-arrow" tone="emerald" :value="number_format($crescimentoAnual ?? 0, 1, ',', '.') . '%'" :trend="($crescimentoAnual ?? 0) >= 0 ? 'up' : 'down'" />
                <x-dash.list-item title="Ticket recorrente" sub="Clientes fiéis" icon="bi-arrow-repeat" tone="indigo" :value="$fmt($ticketMedioRecorrente ?? 0)" />
                <x-dash.list-item title="Clientes pendentes" sub="Com saldo a pagar" icon="bi-person-exclamation" tone="amber" :value="$clientesComSalesPendentes ?? 0" />
            </div>
        </x-dash.card>
    </div>
</div>
