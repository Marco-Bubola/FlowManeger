<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">

    <div class="container mx-auto px-4 py-8">

        <!-- Cabeçalho Principal -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                        <i class="fas fa-box-open text-blue-600"></i>
                        Dashboard de Produtos
                    </h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Análise completa de produtos, estoque e lucratividade
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Atualizado em</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- KPIs Principais (4 Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

            <!-- KPI 1: Lucro Estimado -->
            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-coins text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium opacity-90">Lucro Estimado</p>
                        <p class="text-3xl font-extrabold">
                            R$ {{ number_format($totalSaldoProdutos, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-white/20">
                    <span class="text-xs opacity-80">Receitas - Custos</span>
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>

            <!-- KPI 2: Preço Venda Total -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-arrow-trend-up text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium opacity-90">Preço Venda Total</p>
                        <p class="text-3xl font-extrabold">
                            R$ {{ number_format($totalReceitasProdutos, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-white/20">
                    <span class="text-xs opacity-80">Valor em estoque (venda)</span>
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>

            <!-- KPI 3: Custo Total -->
            <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-arrow-trend-down text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium opacity-90">Custo Total</p>
                        <p class="text-3xl font-extrabold">
                            R$ {{ number_format($totalDespesasProdutos, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-white/20">
                    <span class="text-xs opacity-80">Valor em estoque (custo)</span>
                    <i class="fas fa-receipt"></i>
                </div>
            </div>

            <!-- KPI 4: Ticket Médio -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-tags text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium opacity-90">Ticket Médio</p>
                        <p class="text-3xl font-extrabold">
                            R$ {{ number_format($ticketMedio, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-white/20">
                    <span class="text-xs opacity-80">Preço médio dos produtos</span>
                    <i class="fas fa-calculator"></i>
                </div>
            </div>

        </div>

        <!-- Cards de Estatísticas Secundárias (3 Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

            <!-- Estatística 1: Total de Produtos -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cubes text-blue-600 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Total de Produtos</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                            {{ $totalProdutos }}
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Produtos cadastrados</span>
                </div>
            </div>

            <!-- Estatística 2: Produtos em Estoque -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Produtos em Estoque</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                            {{ $totalProdutosEstoque }}
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Com quantidade disponível</span>
                </div>
            </div>

            <!-- Estatística 3: Produtos Sem Estoque -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Produtos Sem Estoque</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                            {{ $produtosSemEstoque }}
                        </p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Necessitam reposição</span>
                </div>
            </div>

        </div>

        <!-- Primeira Linha de Listas (2 Colunas) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Lista 1: Produtos Mais Vendidos -->
            @if(isset($produtosMaisVendidos) && count($produtosMaisVendidos) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                        <i class="fas fa-fire text-orange-500 mr-2"></i>
                        Top 5 Produtos Mais Vendidos
                    </h3>
                    <div class="space-y-3">
                        @foreach($produtosMaisVendidos as $produto)
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ data_get($produto, 'name') }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Vendidos: {{ data_get($produto, 'total_vendido') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">
                                        R$ {{ number_format(data_get($produto, 'price_sale'), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lista 2: Produtos com Maior Receita -->
            @if(isset($produtosMaiorReceita) && count($produtosMaiorReceita) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                        <i class="fas fa-dollar-sign text-green-500 mr-2"></i>
                        Top 5 Produtos por Receita
                    </h3>
                    <div class="space-y-3">
                        @foreach($produtosMaiorReceita as $produto)
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ data_get($produto, 'name') }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Estoque: {{ data_get($produto, 'stock') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">
                                        R$ {{ number_format(data_get($produto, 'receita_total'), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Segunda Linha de Listas (2 Colunas) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Lista 3: Produtos Parados -->
            @if(isset($produtosParados) && count($produtosParados) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                        <i class="fas fa-pause-circle text-orange-500 mr-2"></i>
                        Produtos Parados (Sem Vendas)
                    </h3>
                    <div class="space-y-3">
                        @foreach($produtosParados as $produto)
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ data_get($produto, 'name') }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Estoque: {{ data_get($produto, 'stock') }} | Custo: R$ {{ number_format(data_get($produto, 'price'), 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">
                                        R$ {{ number_format(data_get($produto, 'price_sale'), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lista 4: Produtos com Estoque Baixo e Alta Demanda -->
            @if(isset($produtosEstoqueBaixoAltaDemanda) && count($produtosEstoqueBaixoAltaDemanda) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        Alerta: Estoque Baixo / Alta Demanda
                    </h3>
                    <div class="space-y-3">
                        @foreach($produtosEstoqueBaixoAltaDemanda as $produto)
                            <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ data_get($produto, 'name') }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Estoque: {{ data_get($produto, 'stock') }} | Vendidos: {{ data_get($produto, 'total_vendido') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 text-xs font-bold">
                                        <i class="fas fa-bell mr-1"></i>
                                        Reabastecer
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Cards de Destaque (2 Colunas) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Destaque 1: Produto com Maior Estoque -->
            @if(isset($produtoMaiorEstoque) && $produtoMaiorEstoque)
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-warehouse mr-2"></i>
                        Produto com Maior Estoque
                    </h3>
                    <div class="space-y-3">
                        <p class="text-2xl font-extrabold">
                            {{ data_get($produtoMaiorEstoque, 'name') }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-90">Quantidade em Estoque:</span>
                            <span class="text-xl font-bold">
                                {{ data_get($produtoMaiorEstoque, 'stock') }} unidades
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-90">Preço de Venda:</span>
                            <span class="text-xl font-bold">
                                R$ {{ number_format(data_get($produtoMaiorEstoque, 'price_sale'), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Destaque 2: Produto Mais Vendido -->
            @if(isset($produtoMaisVendido) && $produtoMaisVendido)
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-trophy mr-2"></i>
                        Produto Mais Vendido
                    </h3>
                    <div class="space-y-3">
                        <p class="text-2xl font-extrabold">
                            {{ data_get($produtoMaisVendido, 'name') }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-90">Total Vendido:</span>
                            <span class="text-xl font-bold">
                                {{ data_get($produtoMaisVendido, 'total_vendido') }} unidades
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-90">Preço de Venda:</span>
                            <span class="text-xl font-bold">
                                R$ {{ number_format(data_get($produtoMaisVendido, 'price_sale'), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    <!-- Estilos Customizados -->
    <style>
        .apexcharts-legend-text {
            color: #0f172a !important;
        }
        .dark .apexcharts-legend-text,
        .apexcharts-theme-dark .apexcharts-legend-text {
            color: #E5E7EB !important;
        }
    </style>

</div>
