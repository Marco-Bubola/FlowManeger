<div x-data="{ activeTab: 'overview' }" class="min-h-screen bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-gray-900 dark:via-slate-900 dark:to-gray-800">
    <!-- Ultra Modern Header with Glassmorphism -->
    <div class="relative bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-white/20 dark:border-gray-700/50 shadow-xl">
        <!-- Background Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 via-green-500/10 to-teal-500/10"></div>

        <div class="relative w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-8">
                <div class="flex items-center space-x-6">
                    <!-- Animated Icon -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-lg opacity-50 animate-pulse"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 rounded-2xl shadow-2xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-4xl font-black bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 bg-clip-text text-transparent">
                            Products Analytics
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 font-medium">Análise completa e inteligente de produtos</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></div>
                                Dados Atualizados
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ now()->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons with Glassmorphism -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard.index') }}"
                       class="group inline-flex items-center px-6 py-3 bg-white/20 dark:bg-gray-800/20 backdrop-blur-lg border border-white/30 dark:border-gray-600/30 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Principal
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerenciar Produtos
                    </a>
                </div>
            </div>

            <!-- Modern Tab Navigation -->
            <div class="flex space-x-1 bg-white/30 dark:bg-gray-800/30 backdrop-blur-lg rounded-2xl p-1 border border-white/20 dark:border-gray-600/20">
                <button @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Visão Geral
                </button>
                <button @click="activeTab = 'analytics'"
                        :class="activeTab === 'analytics' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Analytics
                </button>
                <button @click="activeTab = 'inventory'"
                        :class="activeTab === 'inventory' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Estoque
                </button>
                <button @click="activeTab = 'performance'"
                        :class="activeTab === 'performance' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Performance
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content with Tabs -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- KPI Cards with Advanced Design -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Products -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-blue-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TOTAL DE PRODUTOS</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ number_format($totalProdutos) }}</div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-100/20 text-red-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    {{ $produtosSemEstoque }} sem estoque
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-300 to-indigo-300"></div>
                </div>

                <!-- Stock Total -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">ESTOQUE TOTAL</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ number_format($totalProdutosEstoque) }}</div>
                            <div class="text-xs opacity-80">unidades disponíveis</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-green-300"></div>
                </div>

                <!-- Average Ticket -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-purple-400 via-purple-500 to-violet-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TICKET MÉDIO</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                            <div class="text-xs opacity-80">por venda</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-300 to-violet-300"></div>
                </div>

                <!-- Balance -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-orange-400 via-orange-500 to-red-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">SALDO PRODUTOS</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">
                                R$ {{ number_format(abs($totalSaldoProdutos), 2, ',', '.') }}
                            </div>
                            <div class="text-xs opacity-80">lucro estimado</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-orange-300 to-red-300"></div>
                </div>
            </div>

            <!-- Product Highlights Section -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        Destaques dos Produtos
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Atualizado em tempo real</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Highest Stock -->
                    @if($produtoMaiorEstoque)
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-2xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-blue-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Maior Estoque</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $produtoMaiorEstoque->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $produtoMaiorEstoque->stock_quantity }} unidades</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Best Seller -->
                    @if($produtoMaisVendido)
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/30 rounded-2xl p-6 border border-emerald-200/50 dark:border-emerald-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-emerald-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Mais Vendido</p>
                                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $produtoMaisVendido->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $produtoMaisVendido->product_code }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Out of Stock Alert -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-red-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-2xl p-6 border border-red-200/50 dark:border-red-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-red-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sem Estoque</p>
                                    <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $produtosSemEstoque }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">produtos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <!-- Most Sold Products -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Produtos Mais Vendidos
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Top 10</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($produtosMaisVendidos as $index => $produto)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl hover:shadow-lg transition-all duration-300 border border-blue-100 dark:border-blue-800/50">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    @if($index < 3)
                                        <div class="w-10 h-10 bg-gradient-to-r {{ $index === 0 ? 'from-yellow-400 to-yellow-500' : ($index === 1 ? 'from-gray-400 to-gray-500' : 'from-orange-400 to-orange-500') }} rounded-full flex items-center justify-center shadow-lg">
                                            <span class="text-sm font-black text-white">{{ $index + 1 }}</span>
                                        </div>
                                        @if($index === 0)
                                            <div class="absolute -top-1 -right-1">
                                                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-bold text-white">{{ $index + 1 }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $produto['name'] ?? $produto['product_code'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($produto['total_vendido']) }}</span>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto vendido</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Highest Revenue Products -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                            Maior Receita
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Top 10</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($produtosMaiorReceita as $index => $produto)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-2xl hover:shadow-lg transition-all duration-300 border border-emerald-100 dark:border-emerald-800/50">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-white">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ $produto['name'] ?? $produto['product_code'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($produto['receita_total'], 2, ',', '.') }}</span>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto vendido</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Highest Profit Products -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-violet-600 bg-clip-text text-transparent">
                            Maior Lucro
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Top 10</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($produtoMaiorLucro as $index => $produto)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-2xl hover:shadow-lg transition-all duration-300 border border-purple-100 dark:border-purple-800/50">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-white">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                        {{ $produto['name'] ?? $produto['product_code'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($produto['lucro_total'], 2, ',', '.') }}</span>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto vendido</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent and Stuck Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Products -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">
                            Produtos Recentes
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-cyan-500 rounded-full"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Últimos cadastrados</span>
                        </div>
                    </div>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @forelse($ultimosProdutos as $produto)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-2xl border border-cyan-200 dark:border-cyan-800/50">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $produto['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }} • {{ $produto['stock_quantity'] }} unidades</p>
                            </div>
                            <span class="text-sm font-bold text-cyan-600 dark:text-cyan-400">R$ {{ number_format($produto['price_sale'], 2, ',', '.') }}</span>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-cyan-100 dark:bg-cyan-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto cadastrado</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Stuck Products -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">
                            Produtos Parados
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">60 dias sem venda</span>
                        </div>
                    </div>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @forelse($produtosParados as $produto)
                        <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-2xl border border-red-200 dark:border-red-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $produto['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }} • {{ $produto['stock_quantity'] }} unidades</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-red-600 dark:text-red-400">R$ {{ number_format($produto['price_sale'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto parado! 🚀</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <!-- Sales by Category Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Vendas por Categoria
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Últimos 12 meses</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="categoryChart" class="w-full h-full"></div>
                    </div>
                </div>

                <!-- Stock Evolution Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                            Evolução do Estoque
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Últimos 6 meses</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="stockEvolutionChart" class="w-full h-full"></div>
                    </div>
                </div>
            </div>

            <!-- Profitability Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <!-- Profitability by Category -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-violet-600 bg-clip-text text-transparent">
                            Lucratividade por Categoria
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Margem %</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="profitabilityChart" class="w-full h-full"></div>
                    </div>
                </div>

                <!-- ABC Analysis -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                            Análise ABC de Produtos
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Por receita</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="abcAnalysisChart" class="w-full h-full"></div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Turnover Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-400 via-cyan-500 to-blue-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-cyan-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">GIRO DE ESTOQUE</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">3.2x</div>
                            <div class="text-xs opacity-80">por ano</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-300 to-blue-300"></div>
                </div>

                <!-- Days in Stock -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-teal-400 via-teal-500 to-emerald-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-teal-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">DIAS EM ESTOQUE</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">45</div>
                            <div class="text-xs opacity-80">dias médios</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-teal-300 to-emerald-300"></div>
                </div>

                <!-- Stock Coverage -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-400 via-indigo-500 to-purple-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-indigo-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">COBERTURA</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">92%</div>
                            <div class="text-xs opacity-80">disponibilidade</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-300 to-purple-300"></div>
                </div>

                <!-- ROI -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-pink-400 via-pink-500 to-rose-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-pink-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">ROI PRODUTOS</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">28%</div>
                            <div class="text-xs opacity-80">retorno médio</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-pink-300 to-rose-300"></div>
                </div>
            </div>
        </div>

        <!-- Inventory Tab -->
        <div x-show="activeTab === 'inventory'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Stock Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- In Stock -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">EM ESTOQUE</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ $totalProdutos - $produtosSemEstoque }}</div>
                            <div class="text-xs opacity-80">produtos disponíveis</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-green-300"></div>
                </div>

                <!-- Out of Stock -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-red-400 via-red-500 to-rose-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-red-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">SEM ESTOQUE</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ $produtosSemEstoque }}</div>
                            <div class="text-xs opacity-80">produtos zerados</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-red-300 to-rose-300"></div>
                </div>

                <!-- Low Stock -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-amber-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">ESTOQUE BAIXO</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">15</div>
                            <div class="text-xs opacity-80">produtos críticos</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-amber-300 to-orange-300"></div>
                </div>

                <!-- High Stock -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-blue-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">ESTOQUE ALTO</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">8</div>
                            <div class="text-xs opacity-80">produtos excesso</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-300 to-indigo-300"></div>
                </div>
            </div>

            <!-- Critical Stock Alerts -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">
                        Alertas Críticos de Estoque
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Ação necessária</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Urgent Restock -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-red-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-2xl p-6 border border-red-200/50 dark:border-red-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-red-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Reposição Urgente</p>
                                    <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $produtosSemEstoque }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">produtos zerados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Warning -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/30 rounded-2xl p-6 border border-amber-200/50 dark:border-amber-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-amber-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Estoque Baixo</p>
                                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">15</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">produtos críticos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Excess Stock -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-2xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-blue-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Estoque Alto</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">8</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">produtos em excesso</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Tab -->
        <div x-show="activeTab === 'performance'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Performance Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                <!-- Top Performer Card -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-yellow-400 via-yellow-500 to-orange-600 rounded-3xl p-8 text-white shadow-2xl hover:shadow-yellow-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium opacity-80">PRODUTO ESTRELA</div>
                            </div>
                        </div>
                        @if($produtoMaisVendido)
                        <div class="space-y-3">
                            <div class="text-2xl font-black">{{ $produtoMaisVendido->name }}</div>
                            <div class="text-sm opacity-80">{{ $produtoMaisVendido->product_code }}</div>
                            <div class="flex items-center space-x-4 mt-4">
                                <div class="text-center">
                                    <div class="text-lg font-bold">89%</div>
                                    <div class="text-xs opacity-70">conversão</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold">4.8★</div>
                                    <div class="text-xs opacity-70">avaliação</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-300 to-orange-300"></div>
                </div>

                <!-- Margin Leader -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 rounded-3xl p-8 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium opacity-80">MAIOR MARGEM</div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="text-4xl font-black">67%</div>
                            <div class="text-sm opacity-80">margem de lucro</div>
                            @if($produtoMaiorLucro && isset($produtoMaiorLucro[0]))
                            <div class="mt-4">
                                <div class="text-lg font-bold">{{ $produtoMaiorLucro[0]['name'] ?? $produtoMaiorLucro[0]['product_code'] }}</div>
                                <div class="text-xs opacity-70">{{ $produtoMaiorLucro[0]['product_code'] }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-teal-300"></div>
                </div>

                <!-- Growth Indicator -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-purple-400 via-purple-500 to-indigo-600 rounded-3xl p-8 text-white shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium opacity-80">CRESCIMENTO</div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="text-4xl font-black">+24%</div>
                            <div class="text-sm opacity-80">vs. mês anterior</div>
                            <div class="flex items-center space-x-2 mt-4">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                <div class="text-xs opacity-70">Tendência positiva</div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-300 to-indigo-300"></div>
                </div>
            </div>

            <!-- Product Performance Leaderboard -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-violet-600 bg-clip-text text-transparent">
                        Ranking de Performance dos Produtos
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Score completo</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Performance Metrics -->
                    <div class="space-y-6">
                        @foreach(['Vendas', 'Margem', 'Giro', 'Satisfação'] as $index => $metric)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-2xl border border-purple-200 dark:border-purple-800/50">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-bold text-white">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $metric }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Métrica principal</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ [92, 87, 79, 95][$index] }}%
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">score</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Top Performers -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Top 5 Produtos</h4>
                        @forelse(array_slice($produtosMaisVendidos, 0, 5) as $index => $produto)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl hover:shadow-lg transition-all duration-300 border border-indigo-200 dark:border-indigo-800/50">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    @if($index < 3)
                                        <div class="w-12 h-12 bg-gradient-to-r {{ $index === 0 ? 'from-yellow-400 to-yellow-500' : ($index === 1 ? 'from-gray-400 to-gray-500' : 'from-orange-400 to-orange-500') }} rounded-full flex items-center justify-center shadow-lg">
                                            <span class="text-lg font-black text-white">{{ $index + 1 }}</span>
                                        </div>
                                        @if($index === 0)
                                            <div class="absolute -top-1 -right-1">
                                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center">
                                            <span class="text-lg font-bold text-white">{{ $index + 1 }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ $produto['name'] ?? $produto['product_code'] }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ [95, 89, 84, 78, 72][$index] }}%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">performance</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto para análise</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryData = @json($dadosGraficoPizza);

    // Category Chart with ApexCharts
    const categoryOptions = {
        series: categoryData.map(item => item.total_sold),
        chart: {
            type: 'donut',
            height: 320,
            background: 'transparent',
            foreColor: '#64748B',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        labels: categoryData.map(item => item.category_name),
        colors: [
            '#34D399',
            '#60A5FA',
            '#F87171',
            '#FBBF24',
            '#A78BFA',
            '#F472B6',
            '#38BDF8',
            '#FB923C'
        ],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '16px',
                            fontWeight: 600,
                            color: '#374151',
                            offsetY: -10
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontWeight: 700,
                            color: '#111827',
                            offsetY: 16,
                            formatter: function (val) {
                                return parseInt(val).toLocaleString()
                            }
                        },
                        total: {
                            show: true,
                            showAlways: false,
                            label: 'Total',
                            fontSize: '16px',
                            fontWeight: 600,
                            color: '#6B7280',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => {
                                    return a + b
                                }, 0).toLocaleString()
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            floating: false,
            fontSize: '14px',
            fontWeight: 500,
            markers: {
                width: 12,
                height: 12,
                strokeWidth: 0,
                radius: 12
            },
            itemMargin: {
                horizontal: 8,
                vertical: 4
            }
        },
        tooltip: {
            enabled: true,
            style: {
                fontSize: '14px'
            },
            y: {
                formatter: function(val) {
                    return val + " unidades"
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 280
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    const categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryOptions);
    categoryChart.render();

    // Stock Evolution Chart - Analytics Tab
    const stockEvolutionOptions = {
        series: [{
            name: 'Entrada',
            data: [65, 59, 80, 81, 56, 55],
            color: '#10B981'
        }, {
            name: 'Saída',
            data: [45, 39, 60, 61, 46, 35],
            color: '#EF4444'
        }],
        chart: {
            type: 'area',
            height: 320,
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        xaxis: {
            categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.4,
                opacityTo: 0.1
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        grid: {
            show: true,
            borderColor: '#E5E7EB'
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };

    const stockEvolutionChart = new ApexCharts(document.querySelector("#stockEvolutionChart"), stockEvolutionOptions);
    stockEvolutionChart.render();

    // Profitability Chart - Analytics Tab
    const profitabilityOptions = {
        series: [{
            data: [44, 55, 41, 67, 22, 43, 21]
        }],
        chart: {
            type: 'bar',
            height: 320,
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                horizontal: true,
                distributed: true,
                barHeight: '70%'
            }
        },
        colors: ['#8B5CF6'],
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val + "%"
            }
        },
        xaxis: {
            categories: ['Eletrônicos', 'Roupas', 'Casa', 'Esporte', 'Livros', 'Beleza', 'Outros']
        },
        grid: {
            show: false
        },
        legend: {
            show: false
        }
    };

    const profitabilityChart = new ApexCharts(document.querySelector("#profitabilityChart"), profitabilityOptions);
    profitabilityChart.render();

    // ABC Analysis Chart - Analytics Tab
    const abcAnalysisOptions = {
        series: [70, 20, 10],
        chart: {
            type: 'pie',
            height: 320,
            background: 'transparent',
            foreColor: '#64748B'
        },
        labels: ['Classe A (Top 20%)', 'Classe B (Médio 30%)', 'Classe C (Baixo 50%)'],
        colors: ['#EF4444', '#F59E0B', '#10B981'],
        legend: {
            position: 'bottom'
        }
    };

    const abcAnalysisChart = new ApexCharts(document.querySelector("#abcAnalysisChart"), abcAnalysisOptions);
    abcAnalysisChart.render();

    // Stock Movement Chart - Inventory Tab
    const stockMovementOptions = {
        series: [{
            name: 'Entrada',
            data: [31, 40, 28, 51, 42, 109, 100]
        }, {
            name: 'Saída',
            data: [11, 32, 45, 32, 34, 52, 41]
        }],
        chart: {
            height: 320,
            type: 'area',
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        xaxis: {
            categories: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom']
        },
        colors: ['#3B82F6', '#EF4444'],
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        }
    };

    const stockMovementChart = new ApexCharts(document.querySelector("#stockMovementChart"), stockMovementOptions);
    stockMovementChart.render();

    // Stock Value Chart - Inventory Tab
    const stockValueOptions = {
        series: [{
            data: [400, 430, 448, 470, 540, 580, 690]
        }],
        chart: {
            type: 'bar',
            height: 320,
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '70%'
            }
        },
        xaxis: {
            categories: ['Eletrônicos', 'Roupas', 'Casa', 'Esporte', 'Livros', 'Beleza', 'Outros']
        },
        colors: ['#10B981'],
        grid: {
            show: true,
            borderColor: '#E5E7EB'
        }
    };

    const stockValueChart = new ApexCharts(document.querySelector("#stockValueChart"), stockValueOptions);
    stockValueChart.render();

    // Sales vs Stock Chart - Performance Tab
    const salesVsStockOptions = {
        series: [{
            name: 'Vendas',
            type: 'column',
            data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160]
        }, {
            name: 'Estoque',
            type: 'line',
            data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16]
        }],
        chart: {
            height: 320,
            type: 'line',
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        stroke: {
            width: [0, 4]
        },
        xaxis: {
            categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
        },
        colors: ['#3B82F6', '#10B981']
    };

    const salesVsStockChart = new ApexCharts(document.querySelector("#salesVsStockChart"), salesVsStockOptions);
    salesVsStockChart.render();

    // Profit Margin Trend Chart - Performance Tab
    const profitMarginTrendOptions = {
        series: [{
            name: 'Margem de Lucro',
            data: [28, 29, 33, 36, 32, 32, 33]
        }],
        chart: {
            height: 320,
            type: 'line',
            background: 'transparent',
            foreColor: '#64748B',
            toolbar: {
                show: false
            }
        },
        stroke: {
            width: 4,
            curve: 'smooth'
        },
        xaxis: {
            categories: ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023', 'Q1 2024', 'Q2 2024', 'Q3 2024']
        },
        colors: ['#10B981'],
        markers: {
            size: 6
        },
        grid: {
            show: true,
            borderColor: '#E5E7EB'
        }
    };

    const profitMarginTrendChart = new ApexCharts(document.querySelector("#profitMarginTrendChart"), profitMarginTrendOptions);
    profitMarginTrendChart.render();

    // Dark mode observer for chart updates
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#9CA3AF' : '#64748B';
                const gridColor = isDark ? '#374151' : '#E5E7EB';

                // Update all charts
                [categoryChart, stockEvolutionChart, profitabilityChart, abcAnalysisChart,
                 stockMovementChart, stockValueChart, salesVsStockChart, profitMarginTrendChart].forEach(chart => {
                    if (chart) {
                        chart.updateOptions({
                            chart: {
                                foreColor: textColor
                            },
                            grid: {
                                borderColor: gridColor
                            }
                        });
                    }
                });
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});
</script>
@endpush
