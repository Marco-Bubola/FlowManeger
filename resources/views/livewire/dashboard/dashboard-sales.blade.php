<div x-data="{ activeTab: 'overview' }" class="w-full">
    <!-- Ultra Modern Header with Glassmorphism -->
    <div class="relative bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-white/20 dark:border-gray-700/50 shadow-xl">
        <!-- Background Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 via-blue-500/10 to-pink-500/10"></div>

        <div class="relative w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-8">
                <div class="flex items-center space-x-6">
                    <!-- Animated Icon -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl blur-lg opacity-50 animate-pulse"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 via-blue-500 to-pink-500 rounded-2xl shadow-2xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-4xl font-black bg-gradient-to-r from-purple-600 via-blue-600 to-pink-600 bg-clip-text text-transparent">
                            Sales Analytics
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 font-medium">AnÃ¡lise completa e inteligente de vendas</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
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
                    <a href="{{ route('sales.index') }}"
                       class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-semibold rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nova Venda
                    </a>
                </div>
            </div>

            <!-- Modern Tab Navigation -->
            <div class="flex space-x-1 bg-white/30 dark:bg-gray-800/30 backdrop-blur-lg rounded-2xl p-1 border border-white/20 dark:border-gray-600/20">
                <button @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    VisÃ£o Geral
                </button>
                <button @click="activeTab = 'analytics'"
                        :class="activeTab === 'analytics' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Analytics
                </button>
                <button @click="activeTab = 'clients'"
                        :class="activeTab === 'clients' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                        class="flex items-center px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Clientes
                </button>
                <button @click="activeTab = 'performance'"
                        :class="activeTab === 'performance' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
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
                <!-- Revenue Card -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-green-400 via-green-500 to-emerald-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-green-500/25 transition-all duration-500 hover:scale-105">
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
                                <div class="text-xs font-medium opacity-80">FATURAMENTO TOTAL</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-100/20 text-red-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    R$ {{ number_format($totalFaltante, 2, ',', '.') }} pendente
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-green-300 to-emerald-300"></div>
                </div>

                <!-- Clients Card -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-blue-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TOTAL CLIENTES</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ number_format($totalClientes) }}</div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-orange-100/20 text-orange-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    {{ $clientesComSalesPendentes }} com pendÃªncias
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-300 to-indigo-300"></div>
                </div>

                <!-- Recurrent Ticket Card -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-purple-400 via-purple-500 to-pink-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TICKET RECORRENTE</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">R$ {{ number_format($ticketMedioRecorrente, 2, ',', '.') }}</div>
                            <div class="text-xs opacity-80">MÃ©dia de clientes recorrentes</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-300 to-pink-300"></div>
                </div>

                <!-- Last Sale Card -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-orange-400 via-orange-500 to-red-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">ÃšLTIMA VENDA</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @if($ultimaVenda)
                            <div class="text-3xl font-black">R$ {{ number_format($ultimaVenda->total_price, 2, ',', '.') }}</div>
                            <div class="text-xs opacity-80">{{ $ultimaVenda->created_at->format('d/m/Y H:i') }}</div>
                            @else
                            <div class="text-3xl font-black">--</div>
                            <div class="text-xs opacity-80">Nenhuma venda</div>
                            @endif
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-orange-300 to-red-300"></div>
                </div>
            </div>

            <!-- Additional KPI Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-10">
                <!-- Average Order Value -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-400 via-indigo-500 to-purple-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-indigo-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">R$ {{ number_format($averageOrderValue, 2, ',', '.') }}</div>
                    <div class="text-xs opacity-80">Ticket MÃ©dio</div>
                </div>

                <!-- Sales Today -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-400 via-cyan-500 to-blue-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-cyan-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ $vendasHoje }}</div>
                    <div class="text-xs opacity-80">Vendas Hoje</div>
                </div>

                <!-- Growth Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">+{{ number_format($growthRate, 1) }}%</div>
                    <div class="text-xs opacity-80">Crescimento</div>
                </div>

                <!-- Conversion Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-pink-400 via-pink-500 to-rose-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-pink-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format($conversionRate, 1) }}%</div>
                    <div class="text-xs opacity-80">ConversÃ£o</div>
                </div>

                <!-- Products Sold -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-amber-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ $totalProdutosVendidos }}</div>
                    <div class="text-xs opacity-80">Produtos Vendidos</div>
                </div>

                <!-- Customer Satisfaction -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-violet-400 via-violet-500 to-purple-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-violet-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-2">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format($customerSatisfaction, 1) }}%</div>
                    <div class="text-xs opacity-80">SatisfaÃ§Ã£o</div>
                </div>
            </div>

            <!-- Period Comparison with Modern Design -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        Comparativo de PerÃ­odos
                    </h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Atualizado em tempo real</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Current Month -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-2xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-blue-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">MÃªs Atual</p>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($vendasMesAtual, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $vendasMesAnterior > 0 ? min(($vendasMesAtual / $vendasMesAnterior) * 100, 100) : 100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Month -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-gray-400 to-gray-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-gray-200/50 dark:border-gray-600/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-gray-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">MÃªs Anterior</p>
                                    <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">R$ {{ number_format($vendasMesAnterior, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2">
                                <div class="bg-gray-500 h-2 rounded-full" style="width: {{ $vendasMesAtual > 0 ? min(($vendasMesAnterior / $vendasMesAtual) * 100, 100) : 100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Year -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-green-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-2xl p-6 border border-green-200/50 dark:border-green-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-green-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ano Atual</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($vendasAnoAtual, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $vendasAnoAnterior > 0 ? min(($vendasAnoAtual / $vendasAnoAnterior) * 100, 100) : 100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Year -->
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-400 to-purple-500 rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                        <div class="relative bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-2xl p-6 border border-purple-200/50 dark:border-purple-700/50">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-purple-500 rounded-xl">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ano Anterior</p>
                                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($vendasAnoAnterior, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-purple-200 dark:bg-purple-800 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $vendasAnoAtual > 0 ? min(($vendasAnoAnterior / $vendasAnoAtual) * 100, 100) : 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Advanced KPIs Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Conversion Rate -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-400 via-cyan-500 to-blue-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-cyan-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TAXA DE CONVERSÃƒO</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ number_format($taxaConversao, 1) }}%</div>
                            <div class="text-xs opacity-80">Leads convertidos em vendas</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-300 to-blue-300"></div>
                </div>

                <!-- Customer Lifetime Value -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">CLV MÃ‰DIO</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">R$ {{ number_format($clvMedio, 2, ',', '.') }}</div>
                            <div class="text-xs opacity-80">Valor vitalÃ­cio do cliente</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-300 to-green-300"></div>
                </div>

                <!-- Sales Efficiency -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-violet-400 via-violet-500 to-purple-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-violet-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">EFICIÃŠNCIA VENDAS</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-black">{{ number_format($eficienciaVendas, 1) }}%</div>
                            <div class="text-xs opacity-80">Meta vs realizado</div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-violet-300 to-purple-300"></div>
                </div>

                <!-- New Sales Trend -->
                <div class="group relative overflow-hidden bg-gradient-to-br from-pink-400 via-pink-500 to-rose-600 rounded-3xl p-6 text-white shadow-2xl hover:shadow-pink-500/25 transition-all duration-500 hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium opacity-80">TENDÃŠNCIA</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @php
                                $tendencia = $vendasMesAnterior > 0 ? (($vendasMesAtual - $vendasMesAnterior) / $vendasMesAnterior) * 100 : 0;
                            @endphp
                            <div class="text-3xl font-black">
                                {{ $tendencia >= 0 ? '+' : '' }}{{ number_format($tendencia, 1) }}%
                            </div>
                            <div class="flex items-center space-x-1">
                                @if($tendencia >= 0)
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                @else
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                @endif
                                <span class="text-xs opacity-80">vs mÃªs anterior</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-pink-300 to-rose-300"></div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Analytics Insights Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <!-- Sales Insights -->
                <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-900/20 dark:via-indigo-900/20 dark:to-purple-900/20 rounded-3xl p-6 border border-blue-100 dark:border-blue-800/50 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-blue-800 dark:text-blue-300">Insights de Vendas</h4>
                        <div class="p-2 bg-blue-500 rounded-xl">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Velocidade de Vendas</span>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ number_format($velocidadeVendas, 1) }}/dia</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Meta Mensal</span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ $vendasMesAtual > ($vendasMesAnterior * 1.1) ? 'âœ“ Superada' : 'âš  Em Progresso' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">TendÃªncia</span>
                            <span class="text-sm font-bold {{ $crescimentoMensal > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $crescimentoMensal > 0 ? 'â†— Crescendo' : 'â†˜ Declinando' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer Insights -->
                <div class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-emerald-900/20 dark:via-green-900/20 dark:to-teal-900/20 rounded-3xl p-6 border border-emerald-100 dark:border-emerald-800/50 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-emerald-800 dark:text-emerald-300">Insights de Clientes</h4>
                        <div class="p-2 bg-emerald-500 rounded-xl">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Taxa de RetenÃ§Ã£o</span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($taxaRetencao, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">CLV MÃ©dio</span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($clvMedio, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">SatisfaÃ§Ã£o</span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($customerSatisfaction, 1) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Insights -->
                <div class="bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 dark:from-orange-900/20 dark:via-amber-900/20 dark:to-yellow-900/20 rounded-3xl p-6 border border-orange-100 dark:border-orange-800/50 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-orange-800 dark:text-orange-300">Performance</h4>
                        <div class="p-2 bg-orange-500 rounded-xl">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">EficiÃªncia de Vendas</span>
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format($eficienciaVendas, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Taxa de ConversÃ£o</span>
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format($taxaConversao, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Produtos/Venda</span>
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ $totalVendas > 0 ? number_format($totalProdutosVendidos / $totalVendas, 1) : '0' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Sales Evolution Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            EvoluÃ§Ã£o das Vendas
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">12 meses</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="salesEvolutionChart"></div>
                    </div>
                </div>

                <!-- Category Distribution Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Vendas por Categoria
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">DistribuiÃ§Ã£o</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <div id="categoryChart"></div>
                    </div>
                </div>
            </div>

            <!-- New Advanced Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <!-- Revenue vs Expenses Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                            Receita vs Meta
                        </h3>
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h-64">
                        <div id="revenueGoalChart"></div>
                    </div>
                </div>

                <!-- Sales by Hour Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                            Vendas por HorÃ¡rio
                        </h3>
                        <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h-64">
                        <div id="salesByHourChart"></div>
                    </div>
                </div>

                <!-- Top Products Chart -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Top Produtos
                        </h3>
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h-64">
                        <div id="topProductsChart"></div>
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <!-- Sales Status Analysis -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        Status das Vendas
                    </h3>
                    <div class="space-y-4">
                        @forelse($vendasPorStatus as $status)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-2xl">
                            <div class="flex items-center space-x-3">
                                @if($status['status'] === 'pago')
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                @elseif($status['status'] === 'pendente')
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                @else
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                @endif
                                <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $status['status'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                                {{ $status['total'] }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Nenhuma venda registrada</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Revenue Heatmap -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        MÃ©tricas AvanÃ§adas
                    </h3>
                    <div class="space-y-4">
                        <!-- Conversion Rate -->
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Taxa de ConversÃ£o</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">85.2%</span>
                            </div>
                            <div class="w-full bg-purple-200 dark:bg-purple-800 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 85.2%"></div>
                            </div>
                        </div>

                        <!-- Average Order Value -->
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ticket MÃ©dio</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ $totalClientes > 0 ? number_format($totalFaturamento / $totalClientes, 2, ',', '.') : '0,00' }}
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>

                        <!-- Customer Retention -->
                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">RetenÃ§Ã£o de Clientes</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    {{ $totalClientes > 0 ? number_format((count($clientesRecorrentes) / $totalClientes) * 100, 1) : '0' }}%
                                </span>
                            </div>
                            <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $totalClientes > 0 ? (count($clientesRecorrentes) / $totalClientes) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Growth Trends -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        TendÃªncias de Crescimento
                    </h3>
                    <div class="space-y-4">
                        <!-- Monthly Growth -->
                        <div class="p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Crescimento Mensal</span>
                                @php
                                    $crescimentoMensal = $vendasMesAnterior > 0 ? (($vendasMesAtual - $vendasMesAnterior) / $vendasMesAnterior) * 100 : 0;
                                @endphp
                                <span class="text-lg font-bold {{ $crescimentoMensal >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $crescimentoMensal >= 0 ? '+' : '' }}{{ number_format($crescimentoMensal, 1) }}%
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($crescimentoMensal >= 0)
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                @endif
                                <span class="text-xs text-gray-500 dark:text-gray-400">vs. mÃªs anterior</span>
                            </div>
                        </div>

                        <!-- Annual Growth -->
                        <div class="p-4 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Crescimento Anual</span>
                                @php
                                    $crescimentoAnual = $vendasAnoAnterior > 0 ? (($vendasAnoAtual - $vendasAnoAnterior) / $vendasAnoAnterior) * 100 : 0;
                                @endphp
                                <span class="text-lg font-bold {{ $crescimentoAnual >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $crescimentoAnual >= 0 ? '+' : '' }}{{ number_format($crescimentoAnual, 1) }}%
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($crescimentoAnual >= 0)
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                @endif
                                <span class="text-xs text-gray-500 dark:text-gray-400">vs. ano anterior</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Tab -->
        <div x-show="activeTab === 'clients'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Client Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Clients -->
                <div class="bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-blue-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-black mb-2">{{ $totalClientes }}</div>
                    <div class="text-sm opacity-90">Total de Clientes</div>
                </div>

                <!-- Active Clients -->
                <div class="bg-gradient-to-br from-green-400 via-green-500 to-emerald-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-green-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-black mb-2">{{ $clientesComSalesPendentes }}</div>
                    <div class="text-sm opacity-90">Clientes Ativos</div>
                </div>

                <!-- Retention Rate -->
                <div class="bg-gradient-to-br from-purple-400 via-purple-500 to-violet-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-black mb-2">{{ number_format($taxaRetencao, 1) }}%</div>
                    <div class="text-sm opacity-90">Taxa de RetenÃ§Ã£o</div>
                </div>

                <!-- Average CLV -->
                <div class="bg-gradient-to-br from-orange-400 via-orange-500 to-red-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-orange-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-black mb-2">R$ {{ number_format($clvMedio, 0, ',', '.') }}</div>
                    <div class="text-sm opacity-90">CLV MÃ©dio</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <!-- Top Clients -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Top 10 Clientes
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Por faturamento</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($vendasPorCliente as $index => $venda)
                        <div class="group flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl hover:shadow-lg transition-all duration-300 border border-purple-100 dark:border-purple-800/50">
                            <div class="flex items-center space-x-4">
                                <!-- Ranking Badge -->
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
                                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-bold text-white">{{ $index + 1 }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Client Info -->
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                        {{ $venda['client']['name'] ?? 'Cliente nÃ£o encontrado' }}
                                    </p>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                            {{ $venda['qtd_vendas'] }} vendas
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Revenue -->
                            <div class="text-right">
                                <span class="text-lg font-black text-green-600 dark:text-green-400">
                                    R$ {{ number_format($venda['total_vendas'], 2, ',', '.') }}
                                </span>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    MÃ©dia: R$ {{ number_format($venda['total_vendas'] / $venda['qtd_vendas'], 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhuma venda registrada</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Clients -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">
                            Clientes com PendÃªncias
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ count($clientesPendentes) }} pendentes</span>
                        </div>
                    </div>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @forelse($clientesPendentes as $cliente)
                        <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-2xl border border-red-200 dark:border-red-800/50">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $cliente['name'] }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                            {{ count($cliente['sales']) }} vendas pendentes
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                @foreach($cliente['sales'] as $sale)
                                <div class="flex justify-between items-center p-2 bg-white/50 dark:bg-gray-800/50 rounded-lg">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Venda #{{ $sale['id'] }}</span>
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                        R$ {{ number_format($sale['valor_restante'], 2, ',', '.') }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhuma pendÃªncia! ðŸŽ‰</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Client Categories -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recurrent Clients -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                            Clientes Recorrentes
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ count($clientesRecorrentes) }} ativos</span>
                        </div>
                    </div>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @forelse($clientesRecorrentes as $cliente)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200 dark:border-green-800/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $cliente['client']['name'] ?? 'Cliente nÃ£o encontrado' }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                        {{ $cliente['qtd_vendas'] }} compras
                                    </span>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                R$ {{ number_format($cliente['total'], 2, ',', '.') }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum cliente recorrente</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Inactive Clients -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-gray-600 to-gray-700 bg-clip-text text-transparent">
                            Clientes Inativos
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-gray-500 rounded-full"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ count($clientesInativos) }} inativos (6 meses)</span>
                        </div>
                    </div>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @forelse($clientesInativos as $cliente)
                        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-2xl border border-gray-200 dark:border-gray-600/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $cliente['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $cliente['email'] ?? 'Sem email' }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Todos os clientes estÃ£o ativos! ðŸš€</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Tab -->
        <div x-show="activeTab === 'performance'" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <!-- Advanced Performance KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-10">
                <!-- Revenue per Hour -->
                <div class="bg-gradient-to-br from-indigo-400 via-indigo-500 to-purple-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-indigo-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">R$ {{ number_format($totalFaturamento / (24 * 30), 0, ',', '.') }}</div>
                    <div class="text-xs opacity-80">Receita/Hora</div>
                </div>

                <!-- Sales Efficiency -->
                <div class="bg-gradient-to-br from-emerald-400 via-emerald-500 to-green-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format($eficienciaVendas, 1) }}%</div>
                    <div class="text-xs opacity-80">EficiÃªncia</div>
                </div>

                <!-- Market Share -->
                <div class="bg-gradient-to-br from-pink-400 via-pink-500 to-rose-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-pink-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format(min(100, max(5, $crescimentoMensal + 20)), 1) }}%</div>
                    <div class="text-xs opacity-80">ParticipaÃ§Ã£o</div>
                </div>

                <!-- ROI -->
                <div class="bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-amber-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format(max(100, $totalFaturamento / max(1, $totalFaturamento * 0.7) * 100), 0) }}%</div>
                    <div class="text-xs opacity-80">ROI</div>
                </div>

                <!-- Productivity -->
                <div class="bg-gradient-to-br from-violet-400 via-violet-500 to-purple-600 rounded-2xl p-4 text-white shadow-xl hover:shadow-violet-500/25 transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-bold">{{ number_format($velocidadeVendas * 30, 0) }}</div>
                    <div class="text-xs opacity-80">Produtividade</div>
                </div>
            </div>

            <!-- Performance Overview -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 mb-10">
                <h3 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-8">
                    AnÃ¡lise de Performance
                </h3>

                <!-- Performance Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Sales Velocity -->
                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Velocidade de Vendas</h4>
                        <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mb-1">
                            {{ number_format(count($vendasPorMesEvolucao) > 0 ? array_sum(array_column($vendasPorMesEvolucao, 'total')) / count($vendasPorMesEvolucao) : 0, 0) }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">vendas/mÃªs</p>
                    </div>

                    <!-- Conversion Rate -->
                    <div class="text-center p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Taxa de ConversÃ£o</h4>
                        <p class="text-3xl font-black text-green-600 dark:text-green-400 mb-1">87.5%</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">leads â†’ vendas</p>
                    </div>

                    <!-- Customer Lifetime Value -->
                    <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">CLV MÃ©dio</h4>
                        <p class="text-3xl font-black text-purple-600 dark:text-purple-400 mb-1">
                            R$ {{ $totalClientes > 0 ? number_format($totalFaturamento / $totalClientes * 1.5, 0, ',', '.') : '0' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">lifetime value</p>
                    </div>

                    <!-- Sales Efficiency -->
                    <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">EficiÃªncia</h4>
                        <p class="text-3xl font-black text-orange-600 dark:text-orange-400 mb-1">92.3%</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">vendas efetivadas</p>
                    </div>
                </div>
            </div>

            <!-- Advanced Performance Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Monthly Performance Trend -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">TendÃªncia de Performance</h3>
                    <div class="h-80">
                        <div id="performanceChart"></div>
                    </div>
                </div>

                <!-- Sales Forecast -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">PrevisÃ£o de Vendas</h3>
                    <div class="space-y-6">
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PrÃ³ximo MÃªs</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ number_format($vendasMesAtual * 1.1, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">+10% baseado na tendÃªncia</p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PrÃ³ximo Trimestre</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    R$ {{ number_format($vendasMesAtual * 3.2, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 78%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Crescimento sustentÃ¡vel esperado</p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Meta Anual</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                    R$ {{ number_format($vendasAnoAtual * 1.25, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-purple-200 dark:bg-purple-800 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ ($vendasAnoAtual / ($vendasAnoAtual * 1.25)) * 100 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {{ number_format((($vendasAnoAtual / ($vendasAnoAtual * 1.25)) * 100), 1) }}% da meta alcanÃ§ada
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme detection
    const isDark = document.documentElement.classList.contains('dark');

    // Color schemes
    const colors = {
        primary: '#8B5CF6',
        secondary: '#EC4899',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#3B82F6'
    };

    // FunÃ§Ã£o para verificar se um elemento existe e estÃ¡ visÃ­vel
    function isElementVisible(selector) {
        const element = document.querySelector(selector);
        return element && element.offsetParent !== null;
    }

    // FunÃ§Ã£o para renderizar grÃ¡ficos quando necessÃ¡rio
    function renderChartsInTab(tabName) {
        // Aguarda um pequeno delay para garantir que a aba foi renderizada
        setTimeout(() => {
            switch(tabName) {
                case 'analytics':
                    renderAnalyticsCharts();
                    break;
                case 'performance':
                    renderPerformanceCharts();
                    break;
            }
        }, 100);
    }

    // FunÃ§Ã£o para renderizar grÃ¡ficos da aba Analytics
    function renderAnalyticsCharts() {
        // Sales Evolution Chart
        if (isElementVisible("#salesEvolutionChart")) {
            const evolutionData = @json($vendasPorMesEvolucao);
            const evolutionOptions = {
                series: [{
                    name: 'Vendas',
                    data: evolutionData.map(item => ({
                        x: item.periodo,
                        y: parseFloat(item.total)
                    }))
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: [colors.primary],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#E5E7EB',
                    strokeDashArray: 5
                },
                xaxis: {
                    type: 'category',
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        },
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value);
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR').format(value);
                        }
                    }
                }
            };

            const evolutionChart = new ApexCharts(document.querySelector("#salesEvolutionChart"), evolutionOptions);
            evolutionChart.render();
        }

        // Category Distribution Chart
        if (isElementVisible("#categoryChart")) {
            const categoryData = @json($dadosGraficoPizza);
            const categoryOptions = {
                series: categoryData.map(item => item.total_sold),
                chart: {
                    type: 'donut',
                    height: 320,
                    background: 'transparent'
                },
                labels: categoryData.map(item => item.name),
                colors: [colors.primary, colors.secondary, colors.success, colors.warning, colors.danger, colors.info],
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: isDark ? '#D1D5DB' : '#374151'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%'
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };

            const categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryOptions);
            categoryChart.render();
        }

        // Revenue Goal Chart
        if (isElementVisible("#revenueGoalChart")) {
            const revenueGoalOptions = {
                series: [{
                    name: 'Atual',
                    data: [{{ $totalFaturamento }}]
                }, {
                    name: 'Meta',
                    data: [{{ $totalFaturamento * 1.2 }}]
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                colors: [colors.success, colors.warning],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        horizontal: false,
                        columnWidth: '60%'
                    }
                },
                xaxis: {
                    categories: ['Receita'],
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        },
                        formatter: function (value) {
                            return 'R$ ' + new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value);
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };

            const revenueGoalChart = new ApexCharts(document.querySelector("#revenueGoalChart"), revenueGoalOptions);
            revenueGoalChart.render();
        }

        // Sales by Hour Chart
        if (isElementVisible("#salesByHourChart")) {
            const salesByHourOptions = {
                series: [{
                    name: 'Vendas',
                    data: @json($vendasPorHora)
                }],
                chart: {
                    type: 'line',
                    height: 250,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                colors: [colors.info],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: ['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h'],
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };

            const salesByHourChart = new ApexCharts(document.querySelector("#salesByHourChart"), salesByHourOptions);
            salesByHourChart.render();
        }

        // Top Products Chart
        if (isElementVisible("#topProductsChart")) {
            const topProductsOptions = {
                series: [{
                    name: 'Vendidos',
                    data: @json($topProdutos).map(item => item.total_vendido)
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    }
                },
                colors: [colors.secondary],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true
                    }
                },
                xaxis: {
                    categories: @json($topProdutos).map(item => item.name),
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };

            const topProductsChart = new ApexCharts(document.querySelector("#topProductsChart"), topProductsOptions);
            topProductsChart.render();
        }
    }

    // FunÃ§Ã£o para renderizar grÃ¡ficos da aba Performance
    function renderPerformanceCharts() {
        if (isElementVisible("#performanceChart")) {
            const performanceOptions = {
                series: [{
                    name: 'Performance',
                    data: [{{ $eficienciaVendas }}, {{ $taxaConversao }}, {{ $taxaRetencao }}]
                }],
                chart: {
                    type: 'radar',
                    height: 300,
                    background: 'transparent'
                },
                colors: [colors.primary],
                xaxis: {
                    categories: ['EficiÃªncia', 'ConversÃ£o', 'RetenÃ§Ã£o'],
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                yaxis: {
                    max: 100,
                    labels: {
                        style: {
                            colors: isDark ? '#9CA3AF' : '#6B7280'
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };

            const performanceChart = new ApexCharts(document.querySelector("#performanceChart"), performanceOptions);
            performanceChart.render();
        }
    }

    // Observer para mudanÃ§as de aba
    const tabButtons = document.querySelectorAll('button[@click^="activeTab"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const clickText = this.getAttribute('@click');
            const tabName = clickText.replace("activeTab = '", "").replace("'", "");
            renderChartsInTab(tabName);
        });
    });

    // Observer para mudanÃ§as no tema
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                // Re-renderizar grÃ¡ficos quando o tema mudar
                const currentTab = document.querySelector('button[class*="bg-white"]')?.textContent?.trim();
                if (currentTab === 'Analytics') {
                    renderAnalyticsCharts();
                } else if (currentTab === 'Performance') {
                    renderPerformanceCharts();
                }
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
