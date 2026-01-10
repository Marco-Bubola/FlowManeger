<div class="w-full">

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-pink-50/80 dark:from-slate-800/90 dark:via-purple-900/30 dark:to-pink-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
        <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-pink-400/20 to-rose-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-purple-400/10 via-pink-400/10 to-rose-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-xl shadow-lg flex items-center justify-center ring-4 ring-white/50 dark:ring-slate-700/50">
                            <i class="fas fa-shopping-cart text-white text-2xl"></i>
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 shadow-sm">
                            <div class="w-full h-full bg-green-500 rounded-full animate-ping opacity-75"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <!-- Breadcrumb dentro do header -->
                        <div class="flex items-center gap-2 text-sm text-white/70 mb-1">
                            <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">
                                <i class="fas fa-chart-line mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-white font-medium">
                                <i class="fas fa-shopping-cart mr-1"></i>Vendas
                            </span>
                        </div>

                        <h1 class="text-2xl lg:text-3xl font-bold text-white">
                            Análise de Vendas
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-slate-200">
                            <span class="flex items-center">
                                <i class="fas fa-chart-pie mr-1"></i>
                                <span class="text-slate-200">Inteligência Completa de Vendas e Performance</span>
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

                    <a href="{{ route('sales.index') }}"
                        class="group inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-md hover:shadow-xl text-sm transform hover:scale-105">
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
            <!-- Faturamento Total -->
            <div class="group relative bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-dollar-sign text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-green-800 dark:text-green-300 font-medium">Faturamento Total</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">R$ {{ number_format($totalFaturamento ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total de Clientes -->
            <div class="group relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-blue-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-blue-800 dark:text-blue-300 font-medium">Total de Clientes</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $totalClientes ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Médio -->
            <div class="group relative bg-gradient-to-br from-purple-50 to-violet-100 dark:from-purple-900/20 dark:to-violet-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-receipt text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium">Ticket Médio</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">R$ {{ number_format($averageOrderValue ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendas Hoje -->
            <div class="group relative bg-gradient-to-br from-orange-50 to-red-100 dark:from-orange-900/20 dark:to-red-900/30 rounded-xl shadow-lg border border-orange-200 dark:border-orange-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-orange-400/10 rounded-full transform translate-x-8 -translate-y-8"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-shopping-bag text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-orange-800 dark:text-orange-300 font-medium">Vendas Hoje</p>
                            <p class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ $vendasHoje ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Secundários -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <!-- Taxa de Crescimento -->
            <div class="group relative bg-gradient-to-br from-cyan-50 to-blue-100 dark:from-cyan-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-cyan-200 dark:border-cyan-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-cyan-800 dark:text-cyan-300 font-medium">Crescimento</p>
                        <p class="text-lg font-bold text-cyan-700 dark:text-cyan-400">+{{ number_format($growthRate ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Taxa de Conversão -->
            <div class="group relative bg-gradient-to-br from-emerald-50 to-green-100 dark:from-emerald-900/20 dark:to-green-900/30 rounded-xl shadow-lg border border-emerald-200 dark:border-emerald-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-emerald-800 dark:text-emerald-300 font-medium">Conversão</p>
                        <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($conversionRate ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Produtos Vendidos -->
            <div class="group relative bg-gradient-to-br from-amber-50 to-orange-100 dark:from-amber-900/20 dark:to-orange-900/30 rounded-xl shadow-lg border border-amber-200 dark:border-amber-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-amber-800 dark:text-amber-300 font-medium">Produtos</p>
                        <p class="text-lg font-bold text-amber-700 dark:text-amber-400">{{ $totalProdutosVendidos ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Eficiência -->
            <div class="group relative bg-gradient-to-br from-violet-50 to-purple-100 dark:from-violet-900/20 dark:to-purple-900/30 rounded-xl shadow-lg border border-violet-200 dark:border-violet-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-violet-800 dark:text-violet-300 font-medium">Eficiência</p>
                        <p class="text-lg font-bold text-violet-700 dark:text-violet-400">{{ number_format($eficienciaVendas ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Satisfação -->
            <div class="group relative bg-gradient-to-br from-pink-50 to-rose-100 dark:from-pink-900/20 dark:to-rose-900/30 rounded-xl shadow-lg border border-pink-200 dark:border-pink-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heart text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-pink-800 dark:text-pink-300 font-medium">Satisfação</p>
                        <p class="text-lg font-bold text-pink-700 dark:text-pink-400">{{ number_format($customerSatisfaction ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Velocidade -->
            <div class="group relative bg-gradient-to-br from-indigo-50 to-blue-100 dark:from-indigo-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-indigo-200 dark:border-indigo-800 p-4 hover:shadow-xl transition-all duration-200 overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-indigo-800 dark:text-indigo-300 font-medium">Velocidade</p>
                        <p class="text-lg font-bold text-indigo-700 dark:text-indigo-400">{{ number_format(($velocidadeVendas ?? 0) * 30, 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Evolução de Vendas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-area text-purple-500 mr-2"></i>
                        Evolução de Vendas
                    </h3>
                </div>
                <div id="salesEvolutionChart" class="h-80"></div>
            </div>

            <!-- Distribuição por Categoria -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-chart-pie text-pink-500 mr-2"></i>
                        Vendas por Categoria
                    </h3>
                </div>
                <div id="categoryChart" class="h-80"></div>
            </div>
        </div>

        <!-- Segunda linha de gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Produtos -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-medal text-amber-500 mr-2"></i>
                        Top 10 Produtos
                    </h3>
                </div>
                <div id="topProductsChart" class="h-80"></div>
            </div>

            <!-- Vendas por Hora -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        Vendas por Horário
                    </h3>
                </div>
                <div id="salesByHourChart" class="h-80"></div>
            </div>
        </div>

        <!-- Cards de Comparação -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <!-- Mês Atual -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-calendar text-blue-500 mr-2"></i>
                    Mês Atual
                </h3>
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Vendas</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $vendasMesAtualCount ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Faturamento</p>
                        <p class="text-lg font-bold text-blue-600">R$ {{ number_format($vendasMesAtual ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Mês Anterior -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-600 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-calendar-minus text-slate-500 mr-2"></i>
                    Mês Anterior
                </h3>
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Vendas</p>
                        <p class="text-2xl font-bold text-slate-600">{{ $vendasMesAnteriorCount ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Faturamento</p>
                        <p class="text-lg font-bold text-slate-600">R$ {{ number_format($vendasMesAnterior ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Ano Atual -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                    Ano Atual
                </h3>
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Vendas</p>
                        <p class="text-2xl font-bold text-green-600">{{ $vendasAnoAtualCount ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Faturamento</p>
                        <p class="text-lg font-bold text-green-600">R$ {{ number_format($vendasAnoAtual ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Ano Anterior -->
            <div class="bg-gradient-to-br from-purple-50 to-violet-100 dark:from-purple-900/20 dark:to-violet-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-700 p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                    Ano Anterior
                </h3>
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Vendas</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $vendasAnoAnteriorCount ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Faturamento</p>
                        <p class="text-lg font-bold text-purple-600">R$ {{ number_format($vendasAnoAnterior ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabelas com Paginação Dinâmica -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Clientes -->
            @if(isset($vendasPorCliente) && count($vendasPorCliente) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-star text-amber-500 mr-2"></i>
                        Top 10 Clientes
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3" x-data="{ currentPage: 1, itemsPerPage: 5, get paginatedItems() { const start = (this.currentPage - 1) * this.itemsPerPage; return @js(array_slice($vendasPorCliente ?? [], 0, 10)).slice(start, start + this.itemsPerPage); }, get totalPages() { return Math.ceil(@js(count(array_slice($vendasPorCliente ?? [], 0, 10))) / this.itemsPerPage); } }">
                        <template x-for="cliente in paginatedItems" :key="cliente.client_id">
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold">
                                        <span x-text="(cliente.client && cliente.client.name) ? cliente.client.name.charAt(0) : 'C'"></span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-800 dark:text-slate-100" x-text="(cliente.client && cliente.client.name) ? cliente.client.name : 'Cliente'"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Vendas: <span x-text="cliente.total_vendas"></span></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">R$ <span x-text="parseFloat(cliente.total_vendas || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })"></span></p>
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

            <!-- Status das Vendas -->
            @if(isset($vendasPorStatus) && count($vendasPorStatus) > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fas fa-list-check text-blue-500 mr-2"></i>
                        Status das Vendas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3" x-data="{ currentPage: 1, itemsPerPage: 5, get paginatedItems() { const start = (this.currentPage - 1) * this.itemsPerPage; return @js(array_slice($vendasPorStatus ?? [], 0, 10)).slice(start, start + this.itemsPerPage); }, get totalPages() { return Math.ceil(@js(count(array_slice($vendasPorStatus ?? [], 0, 10))) / this.itemsPerPage); } }">
                        <template x-for="status in paginatedItems" :key="status.status">
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :class="{
                                        'bg-green-500': status.status === 'Finalizado',
                                        'bg-yellow-500': status.status === 'Pendente',
                                        'bg-red-500': status.status === 'Cancelado',
                                        'bg-blue-500': status.status === 'Em Andamento'
                                    }"></div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-800 dark:text-slate-100" x-text="status.status"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-slate-700 dark:text-slate-300" x-text="status.total"></p>
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
            const isDark = document.documentElement.classList.contains('dark');

            // Evolução de Vendas
            if (document.querySelector("#salesEvolutionChart")) {
                const evolutionData = @json($vendasPorMesEvolucao ?? []);
                const evolutionOptions = {
                    series: [{
                        name: 'Vendas',
                        data: evolutionData.map(item => parseFloat(item.total || 0))
                    }],
                    chart: { type: 'area', height: 320, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                    colors: ['#8B5CF6'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
                    xaxis: { categories: evolutionData.map(item => item.periodo || 'Mês'), labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { formatter: function(value) { return value.toFixed(0); }, style: { colors: '#64748b' } } },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [isDark ? '#ffffff' : '#0f172a'] } },
                    tooltip: { theme: 'dark', y: { formatter: function(value) { return value + ' vendas'; } } }
                };
                new ApexCharts(document.querySelector("#salesEvolutionChart"), evolutionOptions).render();
            }

            // Vendas por Categoria
            if (document.querySelector("#categoryChart")) {
                const categoryData = @json($dadosGraficoPizza ?? []);
                if (categoryData && categoryData.length > 0) {
                    const categoryOptions = {
                        series: categoryData.map(item => parseInt(item.total_sold || 0)),
                        labels: categoryData.map(item => item.category_name || 'Categoria'),
                        chart: { type: 'pie', height: 320 },
                        colors: ['#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
                        legend: { position: 'bottom', labels: { colors: [isDark ? '#ffffff' : '#0f172a'] } },
                        responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }],
                        tooltip: { theme: 'dark' }
                    };
                    new ApexCharts(document.querySelector("#categoryChart"), categoryOptions).render();
                }
            }

            // Top Produtos
            if (document.querySelector("#topProductsChart")) {
                const topProductsData = @json($topProdutos ?? []);
                if (topProductsData && topProductsData.length > 0) {
                    const topProductsOptions = {
                        series: [{ name: 'Vendidos', data: topProductsData.map(p => parseInt(p.total_vendido || 0)) }],
                        chart: { type: 'bar', height: 320, toolbar: { show: false } },
                        colors: ['#F59E0B'],
                        plotOptions: { bar: { borderRadius: 8, horizontal: true, dataLabels: { position: 'top' } } },
                        dataLabels: { enabled: false },
                        xaxis: { categories: topProductsData.map(p => p.name || 'Produto'), labels: { style: { colors: '#64748b' } } },
                        yaxis: { labels: { style: { colors: '#64748b' } } },
                        grid: { borderColor: '#e2e8f0' },
                        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [isDark ? '#ffffff' : '#0f172a'] } },
                        tooltip: { theme: 'dark' }
                    };
                    new ApexCharts(document.querySelector("#topProductsChart"), topProductsOptions).render();
                }
            }

            // Vendas por Hora
            if (document.querySelector("#salesByHourChart")) {
                const salesByHourData = {!! json_encode($vendasPorHora ?? [1,2,3,4,5,6,7,8,9,10,11]) !!};
                const salesByHourOptions = {
                    series: [{ name: 'Vendas', data: salesByHourData }],
                    chart: { type: 'line', height: 320, toolbar: { show: false } },
                    colors: ['#3B82F6'],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 5, strokeColors: '#fff', strokeWidth: 2, hover: { size: 7 } },
                    xaxis: { categories: ['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h'], labels: { style: { colors: '#64748b' } } },
                    yaxis: { labels: { style: { colors: '#64748b' } } },
                    grid: { borderColor: '#e2e8f0' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: [isDark ? '#ffffff' : '#0f172a'] } },
                    tooltip: { theme: 'dark', y: { formatter: function(value) { return value + ' vendas'; } } }
                };
                new ApexCharts(document.querySelector("#salesByHourChart"), salesByHourOptions).render();
            }
        });
    </script>
</div>
