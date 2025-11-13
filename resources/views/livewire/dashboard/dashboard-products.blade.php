<div class="w-full">

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-orange-50/90 to-amber-50/80 dark:from-slate-800/90 dark:via-orange-900/30 dark:to-amber-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
        <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-orange-400/20 via-amber-400/20 to-yellow-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-orange-400/10 via-amber-400/10 to-yellow-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-xl shadow-lg flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                            <div class="w-full h-full bg-green-500 rounded-full animate-ping opacity-75"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-sm text-slate-300 dark:text-slate-200 mb-1">
                            <i class="fas fa-chart-pie mr-1"></i>
                            <span class="text-white font-medium">Dashboard Produtos</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">
                            Gestão de Estoque
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-200">
                            <span class="flex items-center">
                                <i class="fas fa-cubes mr-1"></i>
                                <span class="text-slate-200">Análise Completa de Produtos e Lucratividade</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center space-x-2 px-3 py-2 bg-green-600/20 dark:bg-green-700/80 rounded-lg border border-green-700/30 dark:border-green-800 shadow-sm">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-white">Dados em Tempo Real</span>
                    </div>

                    <a href="{{ route('dashboard.index') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-home mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('products.index') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
                        <i class="fas fa-cog mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                        <span>Gerenciar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 pb-8">
        <!-- KPIs Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Lucro Estimado -->
            <div class="group relative bg-gradient-to-br from-cyan-50 to-blue-100 dark:from-cyan-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-cyan-200 dark:border-cyan-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-cyan-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-coins text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-cyan-800 dark:text-cyan-300 font-medium">Lucro Estimado</p>
                            <p class="text-2xl font-bold text-cyan-700 dark:text-cyan-400">R$ {{ number_format($totalSaldoProdutos, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preço Venda Total -->
            <div class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-arrow-trend-up text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-green-800 dark:text-green-300 font-medium">Valor Total (Venda)</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">R$ {{ number_format($totalReceitasProdutos, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custo Total -->
            <div class="group relative bg-gradient-to-br from-red-50 to-rose-100 dark:from-red-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-red-200 dark:border-red-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-arrow-trend-down text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-red-800 dark:text-red-300 font-medium">Valor Total (Custo)</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-400">R$ {{ number_format($totalDespesasProdutos, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Médio -->
            <div class="group relative bg-gradient-to-br from-purple-50 to-indigo-100 dark:from-purple-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-calculator text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium">Ticket Médio</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico de Pizza - Categorias por Receita -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-pie text-orange-500 mr-2"></i>
                        Distribuição por Categoria
                    </h3>
                </div>
                <div id="categoriesChart" class="h-80"></div>
            </div>

            <!-- Gráfico Top 10 Produtos por Receita -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Top 10 Produtos por Receita
                    </h3>
                </div>
                <div id="topProductsChart" class="h-80"></div>
            </div>
        </div>

        <!-- Segunda linha de gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico Estoque x Vendas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-warehouse text-indigo-500 mr-2"></i>
                        Estoque vs Vendas (Top 10)
                    </h3>
                </div>
                <div id="stockSalesChart" class="h-80"></div>
            </div>

            <!-- Gráfico Margem de Lucro -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-percentage text-green-500 mr-2"></i>
                        Margem de Lucro por Produto
                    </h3>
                </div>
                <div id="profitMarginChart" class="h-80"></div>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Estatísticas Gerais -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                    Estatísticas Gerais
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-600 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-cubes text-blue-600"></i>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Total de Produtos</span>
                        </div>
                        <span class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalProdutos }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-600 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600"></i>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Em Estoque</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">{{ $totalProdutosEstoque }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-600 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Sem Estoque</span>
                        </div>
                        <span class="text-lg font-bold text-red-600">{{ $produtosSemEstoque }}</span>
                    </div>
                </div>
            </div>

            <!-- Produto Destaque -->
            @if(isset($produtoMaisVendido) && $produtoMaisVendido)
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-trophy text-amber-500 mr-2"></i>
                    Produto Mais Vendido
                </h3>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-crown text-white text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">{{ data_get($produtoMaisVendido, 'name') }}</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">{{ data_get($produtoMaisVendido, 'total_vendido') }} unidades vendidas</p>
                    <p class="text-lg font-bold text-green-600">R$ {{ number_format(data_get($produtoMaisVendido, 'price_sale', 0), 2, ',', '.') }}</p>
                </div>
            </div>
            @endif

            <!-- Produto Maior Estoque -->
            @if(isset($produtoMaiorEstoque) && $produtoMaiorEstoque)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-warehouse text-indigo-500 mr-2"></i>
                    Maior Estoque
                </h3>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-boxes text-white text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">{{ data_get($produtoMaiorEstoque, 'name') }}</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">{{ data_get($produtoMaiorEstoque, 'stock') }} unidades</p>
                    <p class="text-lg font-bold text-blue-600">R$ {{ number_format(data_get($produtoMaiorEstoque, 'price_sale', 0), 2, ',', '.') }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Tabelas com Paginação Dinâmica -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top 10 Produtos Mais Vendidos -->
            @if(isset($produtosMaisVendidos) && count($produtosMaisVendidos) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-fire text-orange-500 mr-2"></i>
                        Top 10 Produtos Mais Vendidos
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3" x-data="{ currentPage: 1, itemsPerPage: 5, get paginatedItems() { const start = (this.currentPage - 1) * this.itemsPerPage; return @js(array_slice($produtosMaisVendidos ?? [], 0, 10)).slice(start, start + this.itemsPerPage); }, get totalPages() { return Math.ceil(@js(count(array_slice($produtosMaisVendidos ?? [], 0, 10))) / this.itemsPerPage); } }">
                        <template x-for="produto in paginatedItems" :key="produto.name">
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100" x-text="produto.name"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Vendidos: <span x-text="produto.total_vendido"></span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">R$ <span x-text="parseFloat(produto.price_sale || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })"></span></p>
                                </div>
                            </div>
                        </template>

                        <!-- Paginação -->
                        <div class="flex justify-between items-center mt-4" x-show="totalPages > 1">
                            <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300 rounded disabled:opacity-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span>
                            </span>
                            <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300 rounded disabled:opacity-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Produtos com Estoque Baixo -->
            @if(isset($produtosEstoqueBaixoAltaDemanda) && count($produtosEstoqueBaixoAltaDemanda) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        Alerta: Estoque Baixo
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3" x-data="{ currentPage: 1, itemsPerPage: 5, get paginatedItems() { const start = (this.currentPage - 1) * this.itemsPerPage; return @js(array_slice($produtosEstoqueBaixoAltaDemanda ?? [], 0, 10)).slice(start, start + this.itemsPerPage); }, get totalPages() { return Math.ceil(@js(count(array_slice($produtosEstoqueBaixoAltaDemanda ?? [], 0, 10))) / this.itemsPerPage); } }">
                        <template x-for="produto in paginatedItems" :key="produto.name">
                            <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100" x-text="produto.name"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Estoque: <span x-text="produto.stock"></span> | Vendidos: <span x-text="produto.total_vendido"></span></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 text-xs font-bold">
                                        <i class="fas fa-bell mr-1"></i>
                                        Reabastecer
                                    </span>
                                </div>
                            </div>
                        </template>

                        <!-- Paginação -->
                        <div class="flex justify-between items-center mt-4" x-show="totalPages > 1">
                            <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300 rounded disabled:opacity-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span>
                            </span>
                            <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300 rounded disabled:opacity-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>

    <!-- ApexCharts CDN e Scripts -->
    <style>
        .apexcharts-legend-text { color: #0f172a !important; }
        .dark .apexcharts-legend-text, .apexcharts-theme-dark .apexcharts-legend-text { color: #E5E7EB !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dados dos gráficos vindos do backend
            const dadosGraficoPizza = @json($dadosGraficoPizza ?? []);
            const produtosMaisVendidos = @json(array_slice($produtosMaisVendidos ?? [], 0, 10));
            const produtosMaiorReceita = @json(array_slice($produtosMaiorReceita ?? [], 0, 10));

            // Gráfico de Pizza - Categorias
            if (dadosGraficoPizza && dadosGraficoPizza.length > 0) {
                const categoriesOptions = {
                    series: dadosGraficoPizza.map(item => parseFloat(item.receita_total || 0)),
                    labels: dadosGraficoPizza.map(item => item.name || 'Sem categoria'),
                    chart: { type: 'pie', height: 320 },
                    colors: ['#f97316', '#eab308', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'],
                    legend: { position: 'bottom', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }],
                    tooltip: { theme: 'dark', y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
                };
                new ApexCharts(document.querySelector("#categoriesChart"), categoriesOptions).render();
            }

            // Gráfico Top 10 Produtos por Receita
            if (produtosMaiorReceita && produtosMaiorReceita.length > 0) {
                const topProductsOptions = {
                    series: [{ name: 'Receita Total', data: produtosMaiorReceita.map(p => parseFloat(p.receita_total || 0)) }],
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#10b981'],
                    plotOptions: { bar: { borderRadius: 8, horizontal: true, dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: produtosMaiorReceita.map(p => p.name || 'Produto'), labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#64748b' } } },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    tooltip: { theme: 'dark', y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
                };
                new ApexCharts(document.querySelector("#topProductsChart"), topProductsOptions).render();
            }

            // Gráfico Estoque vs Vendas
            if (produtosMaisVendidos && produtosMaisVendidos.length > 0) {
                const stockSalesOptions = {
                    series: [
                        { name: 'Estoque', data: produtosMaisVendidos.map(p => parseInt(p.stock || 0)) },
                        { name: 'Vendidos', data: produtosMaisVendidos.map(p => parseInt(p.total_vendido || 0)) }
                    ],
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#3b82f6', '#f97316'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: produtosMaisVendidos.map(p => p.name || 'Produto').map(name => name.length > 15 ? name.substring(0, 15) + '...' : name), labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { style: { colors: '#64748b' } } },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    grid: { borderColor: '#e2e8f0' },
                    tooltip: { theme: 'dark' }
                };
                new ApexCharts(document.querySelector("#stockSalesChart"), stockSalesOptions).render();
            }

            // Gráfico Margem de Lucro
            if (produtosMaiorReceita && produtosMaiorReceita.length > 0) {
                const profitMarginOptions = {
                    series: [{
                        name: 'Margem (%)',
                        data: produtosMaiorReceita.map(p => {
                            const custo = parseFloat(p.price || 0);
                            const venda = parseFloat(p.price_sale || 0);
                            return custo > 0 ? ((venda - custo) / venda * 100) : 0;
                        })
                    }],
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#10b981'],
                    plotOptions: { bar: { borderRadius: 8, columnWidth: '70%' } },
                    dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(1) + '%'; } },
                    xaxis: { categories: produtosMaiorReceita.map(p => p.name || 'Produto').map(name => name.length > 15 ? name.substring(0, 15) + '...' : name), labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { formatter: function(value) { return value.toFixed(0) + '%'; }, style: { colors: '#64748b' } } },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? '#ffffff' : '#0f172a'] } },
                    tooltip: { theme: 'dark', y: { formatter: function(value) { return value.toFixed(2) + '%'; } } }
                };
                new ApexCharts(document.querySelector("#profitMarginChart"), profitMarginOptions).render();
            }
        });
    </script>
</div>
