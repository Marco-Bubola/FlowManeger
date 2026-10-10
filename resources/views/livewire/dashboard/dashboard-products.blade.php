<div class="dashboard-products-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        // Unidades somadas no estoque e quantidade de produtos por situação (o gráfico usa só produtos).
        $unidades = max(0, ($totalProdutosEstoque ?? 0));
        $semEstoque = $produtosSemEstoque ?? 0;
        $critico = $produtosEstoqueCritico ?? 0;
        $semGiro = $produtosSemGiro ?? 0;
        $estoqueOk = max(0, ($totalProdutos ?? 0) - $semEstoque - $critico);
        $stockSeries = [$estoqueOk, $critico, $semEstoque];
        $stockLabels = ['Estoque ok','Estoque baixo (menos de 10)','Sem estoque'];
        $periodos = ['today' => 'Hoje', 'month' => 'Mês', 'quarter' => 'Trimestre', 'year' => 'Ano'];
    @endphp

    {{-- HEADER --}}
    <x-dash.page-header title="Produtos" active="produtos" icon="bi-box-seam" gradient="from-amber-500 via-orange-500 to-red-500"
        subtitle="Estoque, giro e desempenho">
        <x-slot:period>
            <x-dash.period-chips :options="$periodos" :current="$periodPreset" method="applyPeriodPreset" />
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $periodLabel }}</span>
        </x-slot:period>
    </x-dash.page-header>

    {{-- KPIs --}}
    <div class="dash-kpis dash-kpis-8">
        <x-dash.kpi label="Produtos" tone="indigo" icon="bi-boxes" :value="$totalProdutos ?? 0" countup />
        <x-dash.kpi label="Unidades" tone="emerald" icon="bi-stack" :value="$unidades" countup />
        <x-dash.kpi label="Estoque baixo" tone="amber" icon="bi-exclamation-triangle" :value="$critico" countup />
        <x-dash.kpi label="Sem estoque" tone="rose" icon="bi-x-circle" :value="$semEstoque" countup />
        <x-dash.kpi label="Vendido" tone="teal" icon="bi-cash-coin" :value="$fmt($faturamentoPeriodo ?? 0)" />
        <x-dash.kpi label="Un. vendidas" tone="blue" icon="bi-bag-check" :value="$unidadesVendidasPeriodo ?? 0" countup />
        <x-dash.kpi label="Lucro estimado" tone="purple" icon="bi-graph-up-arrow" :value="$fmt($lucroEstimadoPeriodo ?? 0)" />
        <x-dash.kpi label="Margem média" tone="sky" icon="bi-percent" :value="number_format($margemMediaEstoque ?? 0, 1, ',', '.') . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Top produtos vendidos --}}
        <x-dash.card title="Mais vendidos" sub="Por unidades vendidas no período" icon="bi-trophy" tone="amber" span="dash-col-8">
            @if(!empty($produtosMaisVendidos))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice($produtosMaisVendidos, 0, 8) as $i => $p)
                        @php
                            $nome = $p['name'] ?? $p['nome'] ?? ($p->name ?? 'Produto');
                            $qtd = $p['total_vendido'] ?? $p['quantidade'] ?? $p['total'] ?? ($p->total_vendido ?? 0);
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'#' . ($i + 1) . ' · ' . $fmt($p['receita_total'] ?? 0)" icon="bi-star-fill" tone="amber" :value="(int)$qtd . ' un'" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-trophy" message="Nenhuma venda no período" />
            @endif
        </x-dash.card>

        {{-- Saúde do estoque (donut) --}}
        <x-dash.card title="Saúde do estoque" sub="Produtos por situação" icon="bi-clipboard-data" tone="emerald" span="dash-col-4">
            @if(array_sum($stockSeries) > 0)
                <x-dash.chart id="dashStockChart" type="donut" :series="$stockSeries" :labels="$stockLabels"
                    :colors="['#10b981','#f59e0b','#f43f5e']" />
            @else
                <x-dash.empty icon="bi-clipboard-data" message="Sem dados de estoque" />
            @endif
        </x-dash.card>

        {{-- Resumo de estoque --}}
        <x-dash.card title="Resumo de estoque" sub="Visão rápida" icon="bi-boxes" tone="sky" span="dash-col-4">
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Estoque ok</p>
                    <p class="text-sm font-black text-emerald-700 dark:text-emerald-200">{{ $estoqueOk }}</p>
                </div>
                <div class="rounded-xl bg-rose-500/10 border border-rose-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-300">Sem estoque</p>
                    <p class="text-sm font-black text-rose-700 dark:text-rose-200">{{ $semEstoque }}</p>
                </div>
                <div class="rounded-xl bg-amber-500/10 border border-amber-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-300">Estoque baixo</p>
                    <p class="text-sm font-black text-amber-700 dark:text-amber-200">{{ $critico }}</p>
                </div>
                <div class="rounded-xl bg-indigo-500/10 border border-indigo-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Margem média</p>
                    <p class="text-sm font-black text-indigo-700 dark:text-indigo-200">{{ number_format($margemMediaEstoque ?? 0, 1) }}%</p>
                </div>
            </div>
        </x-dash.card>

        {{-- Produtos parados --}}
        <x-dash.card title="Produtos parados" sub="Sem vendas nos últimos 60 dias" icon="bi-pause-circle" tone="slate" span="dash-col-4">
            @if(!empty($produtosParados))
                <div class="dash-list dash-scroll max-h-[280px] overflow-y-auto pr-1">
                    @foreach(array_slice($produtosParados, 0, 8) as $p)
                        @php
                            $nome = $p['name'] ?? $p['nome'] ?? ($p->name ?? 'Produto');
                            $estoque = $p['stock_quantity'] ?? $p['estoque'] ?? ($p->stock_quantity ?? 0);
                        @endphp
                        <x-dash.list-item :title="$nome" sub="Sem vendas" icon="bi-box" tone="slate" :value="(int)$estoque . ' un'" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-check-circle" message="Nenhum produto parado 🎉" />
            @endif
        </x-dash.card>

        {{-- Últimos cadastrados --}}
        <x-dash.card title="Últimos cadastrados" sub="Novos no catálogo" icon="bi-plus-square" tone="indigo" span="dash-col-4">
            @if(!empty($ultimosProdutos))
                <div class="dash-list dash-scroll max-h-[280px] overflow-y-auto pr-1">
                    @foreach(array_slice($ultimosProdutos, 0, 8) as $p)
                        @php
                            $nome = $p['name'] ?? $p['nome'] ?? ($p->name ?? 'Produto');
                            $preco = $p['price_sale'] ?? $p['preco'] ?? ($p->price_sale ?? 0);
                        @endphp
                        <x-dash.list-item :title="$nome" sub="Recém cadastrado" icon="bi-box-seam-fill" tone="indigo" :value="$fmt($preco)" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-plus-square" message="Nenhum produto recente" />
            @endif
        </x-dash.card>
    </div>
</div>
