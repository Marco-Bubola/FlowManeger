<div class="dashboard-products-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $emEstoque = max(0, ($totalProdutosEstoque ?? 0));
        $semEstoque = $produtosSemEstoque ?? 0;
        $critico = $produtosEstoqueCritico ?? 0;
        $semGiro = $produtosSemGiro ?? 0;
        $stockSeries = [$emEstoque, $critico, $semEstoque, $semGiro];
        $stockLabels = ['Em estoque','Crítico','Sem estoque','Sem giro'];
    @endphp

    {{-- HEADER --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-amber-50/70 to-orange-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-amber-400/20 to-orange-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-red-500 flex items-center justify-center shadow-lg shadow-amber-500/30 shrink-0"><i class="bi bi-box-seam text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">Produtos</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Estoque, giro e desempenho · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Total produtos" tone="indigo" icon="bi-boxes" :value="$totalProdutos ?? 0" countup />
        <x-dash.kpi label="Em estoque" tone="emerald" icon="bi-check-circle" :value="$emEstoque" countup />
        <x-dash.kpi label="Estoque crítico" tone="amber" icon="bi-exclamation-triangle" :value="$critico" countup />
        <x-dash.kpi label="Sem estoque" tone="rose" icon="bi-x-circle" :value="$semEstoque" countup />
        <x-dash.kpi label="Sem giro" tone="slate" icon="bi-pause-circle" :value="$semGiro" countup />
        <x-dash.kpi label="Faturamento" tone="teal" icon="bi-cash-coin" :value="$fmt($faturamentoPeriodo ?? 0)" />
        <x-dash.kpi label="Ticket médio" tone="purple" icon="bi-receipt" :value="$fmt($ticketMedioPeriodo ?? 0)" />
        <x-dash.kpi label="Margem média" tone="sky" icon="bi-percent" :value="number_format($margemMediaEstoque ?? 0, 1) . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Top produtos vendidos --}}
        <x-dash.card title="Mais vendidos" sub="Top giro no período" icon="bi-trophy" tone="amber" span="dash-col-8">
            @if(!empty($produtosMaisVendidos))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice($produtosMaisVendidos, 0, 8) as $i => $p)
                        @php
                            $nome = $p['name'] ?? $p['nome'] ?? ($p->name ?? 'Produto');
                            $qtd = $p['total_vendido'] ?? $p['quantidade'] ?? $p['total'] ?? ($p->total_vendido ?? 0);
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'#' . ($i + 1) . ' mais vendido'" icon="bi-star-fill" tone="amber" :value="(int)$qtd . 'x'" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-trophy" message="Nenhuma venda no período" />
            @endif
        </x-dash.card>

        {{-- Saúde do estoque (donut) --}}
        <x-dash.card title="Saúde do estoque" sub="Distribuição" icon="bi-clipboard-data" tone="emerald" span="dash-col-4">
            @if(array_sum($stockSeries) > 0)
                <x-dash.chart id="dashStockChart" type="donut" :series="$stockSeries" :labels="$stockLabels"
                    :colors="['#10b981','#f59e0b','#f43f5e','#64748b']" />
            @else
                <x-dash.empty icon="bi-clipboard-data" message="Sem dados de estoque" />
            @endif
        </x-dash.card>

        {{-- Resumo de estoque --}}
        <x-dash.card title="Resumo de estoque" sub="Visão rápida" icon="bi-boxes" tone="sky" span="dash-col-4">
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Em estoque</p>
                    <p class="text-sm font-black text-emerald-700 dark:text-emerald-200">{{ $emEstoque }}</p>
                </div>
                <div class="rounded-xl bg-rose-500/10 border border-rose-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-300">Sem estoque</p>
                    <p class="text-sm font-black text-rose-700 dark:text-rose-200">{{ $semEstoque }}</p>
                </div>
                <div class="rounded-xl bg-amber-500/10 border border-amber-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-300">Crítico</p>
                    <p class="text-sm font-black text-amber-700 dark:text-amber-200">{{ $critico }}</p>
                </div>
                <div class="rounded-xl bg-indigo-500/10 border border-indigo-400/30 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Margem média</p>
                    <p class="text-sm font-black text-indigo-700 dark:text-indigo-200">{{ number_format($margemMediaEstoque ?? 0, 1) }}%</p>
                </div>
            </div>
        </x-dash.card>

        {{-- Produtos parados --}}
        <x-dash.card title="Produtos parados" sub="Sem giro recente" icon="bi-pause-circle" tone="slate" span="dash-col-6">
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
        <x-dash.card title="Últimos cadastrados" sub="Novos no catálogo" icon="bi-plus-square" tone="indigo" span="dash-col-6">
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
