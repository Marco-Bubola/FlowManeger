<div x-data="{
    activeTab: 'overview',
    chartInitialized: false
}" class="w-full">

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
@endpush

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Título e Info do Produto -->
            <div class="flex items-center gap-6">
                <!-- Voltar -->
                <a href="{{ route('products.index') }}"
                    class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 transition-all duration-300 shadow-lg hover:shadow-xl group">
                    <i class="bi bi-arrow-left text-white text-xl group-hover:scale-110 transition-transform duration-300"></i>
                </a>

                <!-- Imagem do produto -->
                <div class="relative w-20 h-20 rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-white to-slate-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center group">
                    @if($mainProduct->image)
                    <img src="{{ asset('storage/products/' . $mainProduct->image) }}"
                        alt="{{ $mainProduct->name }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    @else
                    <i class="bi bi-box-seam text-3xl text-slate-400 dark:text-slate-500"></i>
                    @endif
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">
                        {{ $mainProduct->name }}
                    </h1>

                    <!-- Badges do produto -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg">
                            <i class="bi bi-upc-scan mr-2"></i>
                            {{ $productCode }}
                        </span>
                        @if($category)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-semibold bg-white/80 dark:bg-slate-700/80 text-purple-700 dark:text-purple-300 shadow-lg border border-purple-200 dark:border-purple-700">
                            <i class="{{ $this->getCategoryIcon($category->icone) }} mr-1.5"></i>
                            {{ $category->name }}
                        </span>
                        @endif
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold shadow-lg border
                            {{ $products->count() > 1 ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white border-amber-300' : 'bg-gradient-to-r from-green-500 to-emerald-500 text-white border-green-300' }}">
                            <i class="bi bi-layers mr-2"></i>
                            {{ $products->count() }} {{ $products->count() === 1 ? 'variação' : 'variações' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg">
                        <i class="bi bi-cart-check text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Vendas</div>
                        <div class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ number_format($analytics['total_quantity_sold']) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg">
                        <i class="bi bi-currency-dollar text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Receita</div>
                        <div class="text-lg font-bold text-slate-800 dark:text-slate-200">R$ {{ number_format($analytics['total_revenue'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg">
                        <i class="bi bi-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Estoque</div>
                        <div class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ number_format($analytics['total_stock']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation Modernas - Full Width -->
<div class="relative mb-8">
    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-2">
        <nav class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <!-- Overview Tab -->
            <button @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ?
                    'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg scale-105' :
                    'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
                class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl font-bold text-sm transition-all duration-300 group">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg transition-transform duration-300 group-hover:scale-110"
                    :class="activeTab === 'overview' ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-700'">
                    <i class="bi bi-grid-3x3-gap text-xl"
                        :class="activeTab === 'overview' ? 'text-white' : 'text-blue-500'"></i>
                </div>
                <span class="text-center">Visão Geral</span>
                <div x-show="activeTab === 'overview'" class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/20 to-indigo-400/20 animate-pulse"></div>
            </button>

            <!-- Analytics Tab -->
            <button @click="activeTab = 'analytics'"
                :class="activeTab === 'analytics' ?
                    'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg scale-105' :
                    'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
                class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl font-bold text-sm transition-all duration-300 group">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg transition-transform duration-300 group-hover:scale-110"
                    :class="activeTab === 'analytics' ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-700'">
                    <i class="bi bi-graph-up text-xl"
                        :class="activeTab === 'analytics' ? 'text-white' : 'text-purple-500'"></i>
                </div>
                <span class="text-center">Analytics</span>
                <div x-show="activeTab === 'analytics'" class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-400/20 to-pink-400/20 animate-pulse"></div>
            </button>

            <!-- Sales Tab -->
            <button @click="activeTab = 'sales'"
                :class="activeTab === 'sales' ?
                    'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg scale-105' :
                    'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
                class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl font-bold text-sm transition-all duration-300 group">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg transition-transform duration-300 group-hover:scale-110"
                    :class="activeTab === 'sales' ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-700'">
                    <i class="bi bi-cart-check text-xl"
                        :class="activeTab === 'sales' ? 'text-white' : 'text-green-500'"></i>
                </div>
                <span class="text-center">Vendas</span>
                <div x-show="activeTab === 'sales'" class="absolute inset-0 rounded-xl bg-gradient-to-r from-green-400/20 to-emerald-400/20 animate-pulse"></div>
            </button>

            <!-- Variations Tab -->
            <button @click="activeTab = 'variations'"
                :class="activeTab === 'variations' ?
                    'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg scale-105' :
                    'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
                class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-xl font-bold text-sm transition-all duration-300 group">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg transition-transform duration-300 group-hover:scale-110"
                    :class="activeTab === 'variations' ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-700'">
                    <i class="bi bi-layers text-xl"
                        :class="activeTab === 'variations' ? 'text-white' : 'text-orange-500'"></i>
                </div>
                <span class="text-center">Variações</span>
                <span x-show="activeTab !== 'variations'" class="absolute -top-1 -right-1 px-2 py-0.5 bg-orange-500 text-white text-xs font-bold rounded-full">{{ $products->count() }}</span>
                <div x-show="activeTab === 'variations'" class="absolute inset-0 rounded-xl bg-gradient-to-r from-orange-400/20 to-red-400/20 animate-pulse"></div>
            </button>
        </nav>
    </div>
</div>

<!-- Content Container -->
<div class="space-y-8">

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
                <!-- Total Vendido -->
                <div class="group relative bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl hover:shadow-blue-500/30 transition-all duration-500 transform hover:scale-105 hover:-translate-y-2 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="bi bi-cart-check text-blue-200 text-sm"></i>
                                <p class="text-blue-100 text-sm font-semibold tracking-wide uppercase">Total Vendido</p>
                            </div>
                            <p class="text-4xl font-black mb-2 group-hover:scale-110 transition-transform duration-300">{{ number_format($analytics['total_quantity_sold']) }}</p>
                            <p class="text-blue-200 text-sm font-medium">unidades vendidas</p>
                        </div>
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                            <i class="bi bi-cart-check text-3xl text-white"></i>
                        </div>
                    </div>
                    <!-- Animated Border -->
                    <div class="absolute inset-0 rounded-3xl border-2 border-white/20 group-hover:border-white/40 transition-colors duration-500"></div>
                </div>

                <!-- Receita Total -->
                <div class="group relative bg-gradient-to-br from-green-500 via-emerald-600 to-teal-700 rounded-3xl p-8 text-white shadow-2xl hover:shadow-green-500/30 transition-all duration-500 transform hover:scale-105 hover:-translate-y-2 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="bi bi-currency-dollar text-green-200 text-sm"></i>
                                <p class="text-green-100 text-sm font-semibold tracking-wide uppercase">Receita Total</p>
                            </div>
                            <p class="text-4xl font-black mb-2 group-hover:scale-110 transition-transform duration-300">R$ {{ number_format($analytics['total_revenue'], 0, ',', '.') }}</p>
                            <p class="text-green-200 text-sm font-medium">faturamento bruto</p>
                        </div>
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                            <i class="bi bi-currency-dollar text-3xl text-white"></i>
                        </div>
                    </div>
                    <!-- Animated Border -->
                    <div class="absolute inset-0 rounded-3xl border-2 border-white/20 group-hover:border-white/40 transition-colors duration-500"></div>
                </div>

                <!-- Lucro -->
                <div class="group relative bg-gradient-to-br from-purple-500 via-violet-600 to-pink-700 rounded-3xl p-8 text-white shadow-2xl hover:shadow-purple-500/30 transition-all duration-500 transform hover:scale-105 hover:-translate-y-2 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="bi bi-graph-up-arrow text-purple-200 text-sm"></i>
                                <p class="text-purple-100 text-sm font-semibold tracking-wide uppercase">Lucro Total</p>
                            </div>
                            <p class="text-4xl font-black mb-2 group-hover:scale-110 transition-transform duration-300">R$ {{ number_format($analytics['total_profit'], 0, ',', '.') }}</p>
                            <div class="flex items-center space-x-2">
                                <p class="text-purple-200 text-sm font-medium">{{ number_format($analytics['profit_margin'], 1) }}% margem</p>
                                <div class="flex items-center px-2 py-1 bg-white/20 rounded-full">
                                    <i class="bi bi-trending-up text-xs text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                            <i class="bi bi-graph-up-arrow text-3xl text-white"></i>
                        </div>
                    </div>
                    <!-- Animated Border -->
                    <div class="absolute inset-0 rounded-3xl border-2 border-white/20 group-hover:border-white/40 transition-colors duration-500"></div>
                </div>

                <!-- Estoque -->
                <div class="group relative bg-gradient-to-br from-orange-500 via-red-600 to-pink-700 rounded-3xl p-8 text-white shadow-2xl hover:shadow-orange-500/30 transition-all duration-500 transform hover:scale-105 hover:-translate-y-2 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="bi bi-boxes text-orange-200 text-sm"></i>
                                <p class="text-orange-100 text-sm font-semibold tracking-wide uppercase">Em Estoque</p>
                            </div>
                            <p class="text-4xl font-black mb-2 group-hover:scale-110 transition-transform duration-300">{{ number_format($analytics['total_stock']) }}</p>
                            <p class="text-orange-200 text-sm font-medium">R$ {{ number_format($analytics['stock_value'], 0, ',', '.') }} em valor</p>
                        </div>
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-500 shadow-lg">
                            <i class="bi bi-boxes text-3xl text-white"></i>
                        </div>
                    </div>
                    <!-- Animated Border -->
                    <div class="absolute inset-0 rounded-3xl border-2 border-white/20 group-hover:border-white/40 transition-colors duration-500"></div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">

                <!-- Sales Chart -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent dark:from-blue-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-graph-up text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Vendas por Mês</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm">Performance de vendas ao longo do tempo</p>
                            </div>
                        </div>
                        <select wire:model.live="period" class="text-sm border-2 border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2 bg-white/80 dark:bg-slate-700/80 backdrop-blur-sm text-slate-700 dark:text-slate-300 font-medium focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300">
                            <option value="6months">Últimos 6 meses</option>
                            <option value="1year">Último ano</option>
                            <option value="all">Todo período</option>
                        </select>
                    </div>
                    <div class="relative chart-container">
                        <div id="salesChart" class="h-80 w-full"></div>
                        <!-- Loading state -->
                        <div id="salesChartLoading" class="chart-loading" style="display: flex;">
                            <div class="flex items-center space-x-3">
                                <div class="loading-spinner"></div>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">Carregando gráfico de vendas...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Clients -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-transparent dark:from-green-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative flex items-center space-x-4 mb-8">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-people-fill text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Top Clientes</h3>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Clientes que mais compraram este produto</p>
                        </div>
                    </div>
                    <div class="relative space-y-4 max-h-80 overflow-y-auto custom-scrollbar">
                        @forelse($analytics['top_clients'] as $index => $client)
                        <div class="group/item relative flex items-center justify-between p-5 bg-gradient-to-r from-slate-50/80 to-white/60 dark:from-slate-700/80 dark:to-slate-600/60 backdrop-blur-sm rounded-2xl border border-white/30 dark:border-slate-600/30 hover:shadow-lg transition-all duration-300 transform hover:scale-102">
                            <!-- Ranking badge -->
                            <div class="absolute -top-2 -left-2 w-8 h-8 bg-gradient-to-r {{ $index === 0 ? 'from-yellow-400 to-yellow-500' : ($index === 1 ? 'from-gray-400 to-gray-500' : 'from-orange-400 to-orange-500') }} rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                {{ $index + 1 }}
                            </div>

                            <div class="flex items-center space-x-4 flex-1">
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg group-hover/item:scale-110 transition-transform duration-300">
                                    {{ substr($client->name, 0, 1) }}
                                    @if($index < 3)
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-full flex items-center justify-center">
                                        <i class="bi bi-star-fill text-white text-xs"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-slate-800 dark:text-slate-100 group-hover/item:text-blue-600 dark:group-hover/item:text-blue-400 transition-colors duration-300">{{ $client->name }}</p>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-lg text-xs font-medium">
                                            <i class="bi bi-bag-check mr-1"></i>
                                            {{ $client->total_orders }} pedidos
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-lg text-xs font-medium">
                                            <i class="bi bi-box mr-1"></i>
                                            {{ $client->total_quantity }} un.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-xl text-green-600 dark:text-green-400">R$ {{ number_format($client->total_spent, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">total gasto</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gradient-to-r from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <i class="bi bi-person-x text-3xl text-slate-400 dark:text-slate-500"></i>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 font-medium">Nenhum cliente encontrado</p>
                            <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Este produto ainda não foi vendido</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20 dark:border-slate-700/50">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-info-circle text-white text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Informações do Produto</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200/50 dark:border-blue-700/50">
                        <label class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                            <i class="bi bi-tag mr-2"></i>
                            Preço de Custo
                        </label>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-2">R$ {{ number_format($mainProduct->price, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200/50 dark:border-green-700/50">
                        <label class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
                            <i class="bi bi-currency-dollar mr-2"></i>
                            Preço de Venda
                        </label>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-2">R$ {{ number_format($mainProduct->price_sale, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl border border-purple-200/50 dark:border-purple-700/50">
                        <label class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                            <i class="bi bi-calendar-check mr-2"></i>
                            Primeira Venda
                        </label>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-2">
                            {{ $analytics['first_sale_date'] ? \Carbon\Carbon::parse($analytics['first_sale_date'])->format('d/m/Y') : 'Nunca' }}
                        </p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl border border-orange-200/50 dark:border-orange-700/50">
                        <label class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                            <i class="bi bi-calendar-event mr-2"></i>
                            Última Venda
                        </label>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-2">
                            {{ $analytics['last_sale_date'] ? \Carbon\Carbon::parse($analytics['last_sale_date'])->format('d/m/Y') : 'Nunca' }}
                        </p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-2xl border border-cyan-200/50 dark:border-cyan-700/50">
                        <label class="text-sm font-medium text-cyan-600 dark:text-cyan-400 flex items-center">
                            <i class="bi bi-graph-up mr-2"></i>
                            Preço Médio de Venda
                        </label>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-2">R$ {{ number_format($analytics['avg_sale_price'], 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl border border-emerald-200/50 dark:border-emerald-700/50">
                        <label class="text-sm font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                            <i class="bi bi-toggle-on mr-2"></i>
                            Status
                        </label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2 {{ $mainProduct->status === 'ativo' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : ($mainProduct->status === 'inativo' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200') }}">
                            <i class="bi bi-{{ $mainProduct->status === 'ativo' ? 'check-circle' : ($mainProduct->status === 'inativo' ? 'pause-circle' : 'x-circle') }} mr-2"></i>
                            {{ ucfirst($mainProduct->status) }}
                        </span>
                    </div>
                </div>

                @if($mainProduct->description)
                <div class="mt-8 p-6 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-800/50 dark:to-gray-800/50 rounded-2xl border border-slate-200/50 dark:border-slate-700/50">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400 flex items-center mb-3">
                        <i class="bi bi-file-text mr-2"></i>
                        Descrição do Produto
                    </label>
                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $mainProduct->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">

                <!-- Revenue Chart -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-transparent dark:from-green-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative flex items-center space-x-4 mb-8">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-currency-dollar text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Receita por Mês</h3>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Evolução do faturamento mensal</p>
                        </div>
                    </div>
                    <div class="relative chart-container">
                        <div id="revenueChart" class="h-80 w-full"></div>
                        <!-- Loading state -->
                        <div id="revenueChartLoading" class="chart-loading" style="display: flex;">
                            <div class="flex items-center space-x-3">
                                <div class="loading-spinner"></div>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">Carregando receita...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance by Variation -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-transparent dark:from-purple-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative flex items-center space-x-4 mb-8">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-pie-chart text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Performance por Variação</h3>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Distribuição de receita entre variações</p>
                        </div>
                    </div>
                    <div class="relative chart-container">
                        <div id="variationChart" class="h-80 w-full"></div>
                        <!-- Loading state -->
                        <div id="variationChartLoading" class="chart-loading" style="display: flex;">
                            <div class="flex items-center space-x-3">
                                <div class="loading-spinner"></div>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">Carregando variações...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Performance Metrics -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent dark:from-blue-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-graph-up text-white text-xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100">Métricas de Performance</h4>
                        </div>

                        <div class="space-y-6">
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-receipt mr-2"></i>
                                    Ticket Médio
                                </span>
                                <span class="text-xl font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($analytics['avg_sale_price'], 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-percent mr-2"></i>
                                    Margem de Lucro
                                </span>
                                <span class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($analytics['profit_margin'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-arrow-return-right mr-2"></i>
                                    ROI
                                </span>
                                <span class="text-xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $analytics['total_revenue'] > 0 ? number_format(($analytics['total_profit'] / ($analytics['total_revenue'] - $analytics['total_profit'])) * 100, 1) : '0' }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Velocity -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50/50 to-transparent dark:from-orange-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-speedometer2 text-white text-xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100">Velocidade de Vendas</h4>
                        </div>

                        <div class="space-y-6">
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-play-circle mr-2"></i>
                                    Primeira Venda
                                </span>
                                <span class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $analytics['first_sale_date'] ? \Carbon\Carbon::parse($analytics['first_sale_date'])->format('d/m/Y') : 'Nunca' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-stop-circle mr-2"></i>
                                    Última Venda
                                </span>
                                <span class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $analytics['last_sale_date'] ? \Carbon\Carbon::parse($analytics['last_sale_date'])->format('d/m/Y') : 'Nunca' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-calendar-range mr-2"></i>
                                    Dias Ativos
                                </span>
                                <span class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                                    @if($analytics['first_sale_date'] && $analytics['last_sale_date'])
                                        {{ \Carbon\Carbon::parse($analytics['first_sale_date'])->diffInDays(\Carbon\Carbon::parse($analytics['last_sale_date'])) + 1 }}
                                    @else
                                        0
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Analysis -->
                <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-500 p-8 border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-transparent dark:from-purple-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-boxes text-white text-xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100">Análise de Estoque</h4>
                        </div>

                        <div class="space-y-6">
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-currency-dollar mr-2"></i>
                                    Valor em Estoque
                                </span>
                                <span class="text-xl font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($analytics['stock_value'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-arrow-repeat mr-2"></i>
                                    Giro de Estoque
                                </span>
                                <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                    {{ $analytics['total_stock'] > 0 ? number_format($analytics['total_quantity_sold'] / $analytics['total_stock'], 2) : '0' }}x
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl">
                                <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                    <i class="bi bi-calendar-date mr-2"></i>
                                    Cobertura (dias)
                                </span>
                                <span class="text-xl font-bold text-orange-600 dark:text-orange-400">
                                    @php
                                        $daysSinceFirst = $analytics['first_sale_date'] ? \Carbon\Carbon::parse($analytics['first_sale_date'])->diffInDays(now()) : 0;
                                        $avgDailySales = $daysSinceFirst > 0 ? $analytics['total_quantity_sold'] / $daysSinceFirst : 0;
                                        $coverageDays = $avgDailySales > 0 ? $analytics['total_stock'] / $avgDailySales : 0;
                                    @endphp
                                    {{ $coverageDays > 0 ? number_format($coverageDays, 0) : '∞' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Tab -->
        <div x-show="activeTab === 'sales'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/20 dark:border-slate-700/50">
                <div class="p-8 border-b border-slate-200/50 dark:border-slate-700/50 bg-gradient-to-r from-slate-50/50 to-white/50 dark:from-slate-800/50 dark:to-slate-700/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-receipt text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Histórico de Vendas</h3>
                                <p class="text-slate-600 dark:text-slate-400 text-sm">Todas as vendas realizadas deste produto</p>
                            </div>
                        </div>
                        <div class="hidden sm:flex items-center space-x-4">
                            <div class="px-4 py-2 bg-blue-100 dark:bg-blue-900 rounded-xl">
                                <span class="text-sm font-bold text-blue-800 dark:text-blue-200">{{ $salesData->count() ?? 0 }} vendas</span>
                            </div>
                            <div class="px-4 py-2 bg-green-100 dark:bg-green-900 rounded-xl">
                                <span class="text-sm font-bold text-green-800 dark:text-green-200">R$ {{ number_format(($salesData ? $salesData->sum('total_price') : 0), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($salesData) && $salesData->isNotEmpty())
                <div class="p-6">
                    <!-- Filtros e Controles -->
                    <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <!-- Filtro por período -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-calendar3 mr-1"></i>
                                    Período:
                                </label>
                                <select class="px-4 py-2.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-500" data-filter="period">
                                    <option value="30">Últimos 30 dias</option>
                                    <option value="90">Últimos 90 dias</option>
                                    <option value="180">Últimos 6 meses</option>
                                    <option value="365">Último ano</option>
                                    <option value="all">Todas as vendas</option>
                                </select>
                            </div>

                            <!-- Filtro por status -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-check-circle mr-1"></i>
                                    Status:
                                </label>
                                <select class="px-4 py-2.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-500" data-filter="status">
                                    <option value="all">Todos</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="orcamento">Orçamento</option>
                                    <option value="confirmada">Confirmada</option>
                                    <option value="concluida">Concluída</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                        </div>

                        <!-- Busca -->
                        <div class="relative">
                            <input type="text"
                                placeholder="Buscar por cliente ou pedido..."
                                class="pl-10 pr-4 py-2.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-500 w-64">
                            <i class="bi bi-search absolute left-3 top-3.5 text-slate-400"></i>
                        </div>
                    </div>

                    <!-- Tabela de Vendas -->
                    <div class="overflow-x-auto bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-lg relative">
                        <div class="loading-overlay" id="salesTableLoading">
                            <div class="loading-spinner"></div>
                        </div>

                        <table class="w-full sales-table">
                            <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <span>Pedido</span>
                                            <i class="bi bi-arrow-down-up sort-indicator text-slate-400 hover:text-slate-600 cursor-pointer" data-column="0"></i>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <span>Cliente</span>
                                            <i class="bi bi-arrow-down-up sort-indicator text-slate-400 hover:text-slate-600 cursor-pointer" data-column="1"></i>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <span>Data</span>
                                            <i class="bi bi-arrow-down-up sort-indicator text-slate-400 hover:text-slate-600 cursor-pointer" data-column="2"></i>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        Quantidade
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        Preço Unit.
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <span>Total</span>
                                            <i class="bi bi-arrow-down-up sort-indicator text-slate-400 hover:text-slate-600 cursor-pointer" data-column="5"></i>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse($salesData as $index => $sale)
                                @php
                                    $orderNumber = str_pad($sale->id, 3, '0', STR_PAD_LEFT);
                                    $gradients = [
                                        'from-blue-500 to-indigo-500',
                                        'from-green-500 to-emerald-500',
                                        'from-purple-500 to-pink-500',
                                        'from-orange-500 to-red-500',
                                        'from-teal-500 to-cyan-500',
                                        'from-violet-500 to-purple-500'
                                    ];
                                    $avatarGradients = [
                                        'from-purple-500 to-pink-500',
                                        'from-orange-500 to-red-500',
                                        'from-teal-500 to-cyan-500',
                                        'from-blue-500 to-indigo-500',
                                        'from-green-500 to-emerald-500',
                                        'from-violet-500 to-purple-500'
                                    ];
                                    $gradient = $gradients[$index % count($gradients)];
                                    $avatarGradient = $avatarGradients[$index % count($avatarGradients)];

                                    // Dados do cliente
                                    $clientName = $sale->client ? $sale->client->name : 'Cliente Avulso';
                                    $clientEmail = $sale->client ? $sale->client->email : '';
                                    $initials = '';
                                    if ($sale->client) {
                                        $names = explode(' ', $clientName);
                                        $initials = strtoupper(substr($names[0], 0, 1));
                                        if (count($names) > 1) {
                                            $initials .= strtoupper(substr($names[1], 0, 1));
                                        }
                                    } else {
                                        $initials = 'CA';
                                    }

                                    // Configuração de status baseado no modelo Sale
                                    $statusConfig = [
                                        'pendente' => [
                                            'bg' => 'bg-yellow-100 dark:bg-yellow-900',
                                            'text' => 'text-yellow-800 dark:text-yellow-200',
                                            'border' => 'border-yellow-200 dark:border-yellow-700',
                                            'icon' => 'bi-clock',
                                            'label' => 'Pendente'
                                        ],
                                        'orcamento' => [
                                            'bg' => 'bg-blue-100 dark:bg-blue-900',
                                            'text' => 'text-blue-800 dark:text-blue-200',
                                            'border' => 'border-blue-200 dark:border-blue-700',
                                            'icon' => 'bi-file-text',
                                            'label' => 'Orçamento'
                                        ],
                                        'confirmada' => [
                                            'bg' => 'bg-indigo-100 dark:bg-indigo-900',
                                            'text' => 'text-indigo-800 dark:text-indigo-200',
                                            'border' => 'border-indigo-200 dark:border-indigo-700',
                                            'icon' => 'bi-check2',
                                            'label' => 'Confirmada'
                                        ],
                                        'concluida' => [
                                            'bg' => 'bg-green-100 dark:bg-green-900',
                                            'text' => 'text-green-800 dark:text-green-200',
                                            'border' => 'border-green-200 dark:border-green-700',
                                            'icon' => 'bi-check-circle',
                                            'label' => 'Concluída'
                                        ],
                                        'cancelada' => [
                                            'bg' => 'bg-red-100 dark:bg-red-900',
                                            'text' => 'text-red-800 dark:text-red-200',
                                            'border' => 'border-red-200 dark:border-red-700',
                                            'icon' => 'bi-x-circle',
                                            'label' => 'Cancelada'
                                        ]
                                    ];

                                    $status = $statusConfig[$sale->status] ?? $statusConfig['pendente'];

                                    // Calcular dados dos itens da venda
                                    $saleItems = $sale->saleItems ?? collect();
                                    $totalQuantity = $saleItems->sum('quantity') ?: 1;

                                    // Buscar o item específico do produto atual se existir
                                    $currentProductItem = $saleItems->where('product_id', $mainProduct->id ?? 0)->first();
                                    $unitPrice = $currentProductItem ? $currentProductItem->price_sale : ($saleItems->first()->price_sale ?? ($sale->total_price / $totalQuantity));

                                    // Tipo de pagamento para exibição
                                    $saleType = match($sale->payment_method ?? '') {
                                        'dinheiro' => 'Dinheiro',
                                        'cartao_credito' => 'Cartão Crédito',
                                        'cartao_debito' => 'Cartão Débito',
                                        'pix' => 'PIX',
                                        'transferencia' => 'Transferência',
                                        default => 'Venda direta'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-r {{ $gradient }} rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                #{{ $orderNumber }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">#VENDA-{{ date('Y') }}-{{ $orderNumber }}</div>
                                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                                    {{ $saleType }}
                                                    @if($sale->tipo_pagamento === 'parcelado' && $sale->parcelas > 1)
                                                        • {{ $sale->parcelas }}x
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-gradient-to-r {{ $avatarGradient }} rounded-full flex items-center justify-center text-white text-xs font-bold">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $clientName }}</div>
                                                @if($clientEmail)
                                                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $clientEmail }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900 dark:text-slate-100">{{ $sale->created_at->format('d/m/Y') }}</div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $sale->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium quantity-badge">
                                                {{ $totalQuantity }} {{ $totalQuantity == 1 ? 'unidade' : 'unidades' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100 currency-field">
                                            R$ {{ number_format($unitPrice, 2, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400 currency-field">
                                            R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium status-badge {{ $status['bg'] }} {{ $status['text'] }} border {{ $status['border'] }}">
                                            <i class="bi {{ $status['icon'] }} mr-1"></i>
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <button class="action-button p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200 tooltip" title="Ver detalhes" data-tooltip="Ver detalhes" onclick="window.location.href='{{ route('sales.show', $sale->id) }}'">
                                                <i class="bi bi-eye text-sm"></i>
                                            </button>
                                            <button class="action-button p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200 tooltip" title="Imprimir" data-tooltip="Imprimir" onclick="window.print()">
                                                <i class="bi bi-printer text-sm"></i>
                                            </button>
                                            @if($sale->status !== 'cancelada')
                                            <button class="action-button p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors duration-200 tooltip" title="Duplicar" data-tooltip="Duplicar" wire:click="duplicateSale({{ $sale->id }})">
                                                <i class="bi bi-files text-sm"></i>
                                            </button>
                                            @endif
                                            @if($sale->status === 'pendente')
                                            <button class="action-button p-2 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors duration-200 tooltip" title="Editar" data-tooltip="Editar" onclick="window.location.href='{{ route('sales.edit', $sale->id) }}'">
                                                <i class="bi bi-pencil text-sm"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-gradient-to-r from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <i class="bi bi-cart-x text-2xl text-slate-400 dark:text-slate-500"></i>
                                        </div>
                                        <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Nenhuma venda encontrada</h4>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">Este produto ainda não possui vendas registradas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação e Totais -->
                    <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400">
                            @php
                                $totalSales = $salesData->count();
                                $totalValue = $salesData->sum('total_price');
                                $currentPage = request()->get('page', 1);
                                $perPage = 10; // ou o valor que você definir na paginação
                                $startItem = ($currentPage - 1) * $perPage + 1;
                                $endItem = min($currentPage * $perPage, $totalSales);
                            @endphp
                            <span>Mostrando <span class="font-medium text-slate-900 dark:text-slate-100">{{ $startItem }}-{{ $endItem }}</span> de <span class="font-medium text-slate-900 dark:text-slate-100">{{ $totalSales }}</span> vendas</span>
                            <div class="h-4 w-px bg-slate-300 dark:bg-slate-600"></div>
                            <span>Total vendido: <span class="font-bold text-green-600 dark:text-green-400">R$ {{ number_format($totalValue, 2, ',', '.') }}</span></span>
                        </div>

                        @if($salesData instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <nav class="flex items-center space-x-1">
                            @if($salesData->onFirstPage())
                                <button class="pagination-button px-3 py-2 text-sm font-medium text-slate-300 dark:text-slate-600 rounded-lg" disabled>
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            @else
                                <button class="pagination-button px-3 py-2 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200" wire:click="previousPage">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            @endif

                            @foreach($salesData->getUrlRange(max(1, $salesData->currentPage() - 2), min($salesData->lastPage(), $salesData->currentPage() + 2)) as $page => $url)
                                @if($page == $salesData->currentPage())
                                    <button class="pagination-button px-3 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg shadow-sm">{{ $page }}</button>
                                @else
                                    <button class="pagination-button px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200" wire:click="gotoPage({{ $page }})">{{ $page }}</button>
                                @endif
                            @endforeach

                            @if($salesData->hasMorePages())
                                <button class="pagination-button px-3 py-2 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200" wire:click="nextPage">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @else
                                <button class="pagination-button px-3 py-2 text-sm font-medium text-slate-300 dark:text-slate-600 rounded-lg" disabled>
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @endif
                        </nav>
                        @else
                        <nav class="flex items-center space-x-1">
                            <span class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">Página 1 de 1</span>
                        </nav>
                        @endif
                    </div>

                    <!-- Estatísticas Rápidas -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $totalSalesCount = $salesData->count();
                            $averageTicket = $totalSalesCount > 0 ? $salesData->avg('total_price') : 0;
                            $uniqueClients = $salesData->whereNotNull('client_id')->pluck('client_id')->unique()->count();
                            $totalValue = $salesData->sum('total_price');

                            // Calcular crescimento mensal
                            $currentMonth = now()->month;
                            $currentYear = now()->year;
                            $currentMonthSales = $salesData->where('created_at', '>=', now()->startOfMonth())->count();
                            $lastMonthSales = $salesData->whereBetween('created_at', [
                                now()->subMonth()->startOfMonth(),
                                now()->subMonth()->endOfMonth()
                            ])->count();
                            $salesGrowth = $lastMonthSales > 0 ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

                            // Calcular crescimento do ticket médio
                            $currentMonthAvg = $salesData->where('created_at', '>=', now()->startOfMonth())->avg('total_price') ?? 0;
                            $lastMonthAvg = $salesData->whereBetween('created_at', [
                                now()->subMonth()->startOfMonth(),
                                now()->subMonth()->endOfMonth()
                            ])->avg('total_price') ?? 0;
                            $ticketGrowth = $lastMonthAvg > 0 ? (($currentMonthAvg - $lastMonthAvg) / $lastMonthAvg) * 100 : 0;

                            // Novos clientes este mês
                            $newClientsThisMonth = $salesData->where('created_at', '>=', now()->startOfMonth())
                                                            ->whereNotNull('client_id')
                                                            ->pluck('client_id')
                                                            ->unique()
                                                            ->count();
                        @endphp

                        <div class="stats-card bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Vendas do Mês</p>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $currentMonthSales }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        @if($salesGrowth > 0)
                                            +{{ number_format($salesGrowth, 1) }}% vs mês anterior
                                        @elseif($salesGrowth < 0)
                                            {{ number_format($salesGrowth, 1) }}% vs mês anterior
                                        @else
                                            Sem variação
                                        @endif
                                    </p>
                                </div>
                                <div class="icon w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-graph-up text-white text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200/50 dark:border-green-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Ticket Médio</p>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 currency-field">R$ {{ number_format($averageTicket, 2, ',', '.') }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        @if($ticketGrowth > 0)
                                            +{{ number_format($ticketGrowth, 1) }}% vs mês anterior
                                        @elseif($ticketGrowth < 0)
                                            {{ number_format($ticketGrowth, 1) }}% vs mês anterior
                                        @else
                                            Sem variação
                                        @endif
                                    </p>
                                </div>
                                <div class="icon w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-currency-dollar text-white text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 border border-purple-200/50 dark:border-purple-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Clientes Únicos</p>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $uniqueClients }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        @if($newClientsThisMonth > 0)
                                            +{{ $newClientsThisMonth }} {{ $newClientsThisMonth == 1 ? 'novo cliente' : 'novos clientes' }}
                                        @else
                                            Nenhum cliente novo este mês
                                        @endif
                                    </p>
                                </div>
                                <div class="icon w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-people text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-20">
                    <div class="w-24 h-24 bg-gradient-to-r from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-cart-x text-4xl text-slate-400 dark:text-slate-500"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Nenhuma venda encontrada</h4>
                    <p class="text-slate-500 dark:text-slate-400">Este produto ainda não foi vendido</p>
                    <div class="mt-6">
                        @if(Route::has('sales.create'))
                        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar primeira venda
                        </a>
                        @else
                        <button wire:click="createSale" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar primeira venda
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Variations Tab -->
        <div x-show="activeTab === 'variations'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                @foreach($products as $product)
                <!-- Card no estilo produtos.css -->
                <div class="product-card-modern">
                    <!-- Botões flutuantes -->
                    <div class="btn-action-group">
                        <a href="{{ route('products.show', $product->product_code) }}" class="btn btn-secondary" title="Ver Detalhes">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary" title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button type="button"
                                wire:click="$dispatch('openExportModal', { productId: {{ $product->id }} })"
                                class="btn btn-success"
                                title="Exportar Card">
                            <i class="bi bi-file-earmark-image"></i>
                        </button>
                        <button type="button"
                                wire:click="confirmDelete({{ $product->id }})"
                                class="btn btn-danger"
                                title="Excluir">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    <!-- Área da imagem com badges -->
                    <div class="product-img-area">
                        <img src="{{ asset('storage/products/' . $product->image) }}" class="product-img" alt="{{ $product->name }}">

                        @if($product->stock_quantity == 0)
                        <div class="out-of-stock">
                            <i class="bi bi-x-circle"></i> Fora de Estoque
                        </div>
                        @endif

                        <!-- Código do produto -->
                        <span class="badge-product-code" title="Código do Produto">
                            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                        </span>

                        <!-- Quantidade -->
                        <span class="badge-quantity" title="Quantidade em Estoque">
                            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                        </span>

                        <!-- Ícone da categoria -->
                        <div class="category-icon-wrapper">
                            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="card-body">
                        <div class="product-title" title="{{ $product->name }}">
                            {{ ucwords($product->name) }}
                        </div>

                        <!-- Área de preços dentro do card-body -->
                        <div class="price-area mt-3">
                            <div class="flex flex-col gap-2">
                                <span class="badge-price" title="Preço de Custo">
                                    <i class="bi bi-tag"></i>
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </span>

                                <span class="badge-price-sale" title="Preço de Venda">
                                    <i class="bi bi-currency-dollar"></i>
                                    R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
</div>

    <!-- Export Modal Component -->
    @livewire('products.export-product-card')
</div>

@push('scripts')
@php
$defaultChartData = [
    'sales' => [12, 19, 15, 23, 18, 25, 30],
    'revenue' => [1200, 1900, 1500, 2300, 1800, 2500, 3000],
    'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul']
];

$defaultVariations = [
    ['product' => ['id' => 1], 'revenue' => 1500],
    ['product' => ['id' => 2], 'revenue' => 2300],
    ['product' => ['id' => 3], 'revenue' => 1800]
];
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, iniciando verificação do ApexCharts...');

    let salesChart, revenueChart, variationChart;

    // Check if ApexCharts is loaded with retry mechanism
    function checkApexCharts(callback, retries = 0) {
        if (typeof ApexCharts !== 'undefined') {
            console.log('ApexCharts encontrado! Versão:', ApexCharts.version);
            callback();
        } else if (retries < 20) {
            console.log('ApexCharts não carregado ainda, tentativa:', retries + 1);
            setTimeout(() => checkApexCharts(callback, retries + 1), 500);
        } else {
            console.error('ApexCharts não está disponível após várias tentativas');
            // Fallback: tentar carregar via CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts@latest';
            script.onload = () => {
                console.log('ApexCharts carregado via fallback');
                setTimeout(callback, 100);
            };
            document.head.appendChild(script);
        }
    }

    // Chart data from Livewire - com dados de exemplo se não houver dados
    @php
    $defaultChartData = [
        'sales' => [12, 19, 15, 23, 18, 25, 30],
        'revenue' => [1200, 1900, 1500, 2300, 1800, 2500, 3000],
        'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul']
    ];
    @endphp
    const chartData = {!! json_encode($chartData ?? $defaultChartData) !!};
    console.log('Chart data:', chartData);

    function showLoading(chartId) {
        const loadingEl = document.querySelector(`#${chartId}Loading`);
        if (loadingEl) {
            loadingEl.style.display = 'flex';
            loadingEl.style.position = 'absolute';
            loadingEl.style.top = '0';
            loadingEl.style.left = '0';
            loadingEl.style.right = '0';
            loadingEl.style.bottom = '0';
            loadingEl.style.zIndex = '10';
        }
    }

    function hideLoading(chartId) {
        const loadingEl = document.querySelector(`#${chartId}Loading`);
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }

    function initSalesChart() {
        const chartElement = document.querySelector("#salesChart");
        if (!chartElement) {
            console.warn('Elemento #salesChart não encontrado');
            return;
        }

        console.log('Inicializando gráfico de vendas...');
        showLoading('salesChart');

        if (salesChart) {
            salesChart.destroy();
        }

        const salesOptions = {
            series: [{
                name: 'Quantidade Vendida',
                data: chartData.sales || [12, 19, 15, 23, 18, 25, 30]
            }],
            chart: {
                height: 320,
                type: 'area',
                fontFamily: 'Inter, sans-serif',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                },
                background: 'transparent'
            },
            colors: ['#3B82F6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.8,
                    opacityTo: 0.1,
                    stops: [0, 90, 100],
                    colorStops: [
                        {
                            offset: 0,
                            color: '#3B82F6',
                            opacity: 0.8
                        },
                        {
                            offset: 100,
                            color: '#1D4ED8',
                            opacity: 0.1
                        }
                    ]
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 4,
                colors: ['#3B82F6']
            },
            xaxis: {
                categories: chartData.labels || ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                }
            },
            grid: {
                borderColor: '#E2E8F0',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif'
                },
                y: {
                    formatter: function(val) {
                        return val + ' unidades'
                    }
                }
            },
            markers: {
                size: 6,
                colors: ['#3B82F6'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: {
                    size: 8
                }
            }
        };

        try {
            salesChart = new ApexCharts(chartElement, salesOptions);
            salesChart.render().then(() => {
                console.log('Gráfico de vendas renderizado com sucesso');
                hideLoading('salesChart');
            }).catch(error => {
                console.error('Erro ao renderizar gráfico de vendas:', error);
                hideLoading('salesChart');
            });
        } catch (error) {
            console.error('Erro ao criar gráfico de vendas:', error);
            hideLoading('salesChart');
        }
    }

    function initRevenueChart() {
        const chartElement = document.querySelector("#revenueChart");
        if (!chartElement) {
            console.warn('Elemento #revenueChart não encontrado');
            return;
        }

        console.log('Inicializando gráfico de receita...');
        showLoading('revenueChart');

        if (revenueChart) {
            revenueChart.destroy();
        }

        const revenueOptions = {
            series: [{
                name: 'Receita',
                data: chartData.revenue || [1200, 1900, 1500, 2300, 1800, 2500, 3000]
            }],
            chart: {
                height: 320,
                type: 'bar',
                fontFamily: 'Inter, sans-serif',
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            colors: ['#10B981'],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '60%',
                    colors: {
                        ranges: [{
                            color: '#10B981'
                        }]
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: chartData.labels || ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '12px',
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '12px',
                        fontWeight: 500
                    },
                    formatter: function(val) {
                        return 'R$ ' + val.toLocaleString('pt-BR')
                    }
                }
            },
            grid: {
                borderColor: '#E2E8F0',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif'
                },
                y: {
                    formatter: function(val) {
                        return 'R$ ' + val.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2
                        })
                    }
                }
            }
        };

        try {
            revenueChart = new ApexCharts(chartElement, revenueOptions);
            revenueChart.render().then(() => {
                console.log('Gráfico de receita renderizado com sucesso');
                hideLoading('revenueChart');
            }).catch(error => {
                console.error('Erro ao renderizar gráfico de receita:', error);
                hideLoading('revenueChart');
            });
        } catch (error) {
            console.error('Erro ao criar gráfico de receita:', error);
            hideLoading('revenueChart');
        }
    }

    function initVariationChart() {
        const chartElement = document.querySelector("#variationChart");
        if (!chartElement) {
            console.warn('Elemento #variationChart não encontrado');
            return;
        }

        console.log('Inicializando gráfico de variações...');
        showLoading('variationChart');

        if (variationChart) {
            variationChart.destroy();
        }

        @php
        $defaultVariations = [
            ['product' => ['id' => 1], 'revenue' => 1500],
            ['product' => ['id' => 2], 'revenue' => 2300],
            ['product' => ['id' => 3], 'revenue' => 1800]
        ];
        @endphp
        const variations = {!! json_encode($analytics['variation_performance'] ?? $defaultVariations) !!};

        const variationData = variations.map(v => v.revenue);
        const variationLabels = variations.map(v => 'Variação ' + v.product.id);

        if (variationData.length === 0) {
            chartElement.innerHTML = '<div class="flex items-center justify-center h-80 text-slate-500"><div class="text-center"><i class="bi bi-pie-chart text-4xl mb-2"></i><p>Nenhum dado disponível</p></div></div>';
            return;
        }

        const variationOptions = {
            series: variationData,
            chart: {
                height: 320,
                type: 'donut',
                fontFamily: 'Inter, sans-serif',
                background: 'transparent'
            },
            colors: ['#8B5CF6', '#EC4899', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F97316'],
            labels: variationLabels,
            legend: {
                position: 'bottom',
                labels: {
                    colors: '#64748B',
                    useSeriesColors: false
                },
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif'
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif'
                },
                y: {
                    formatter: function(val) {
                        return 'R$ ' + val.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2
                        })
                    }
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        try {
            variationChart = new ApexCharts(chartElement, variationOptions);
            variationChart.render().then(() => {
                console.log('Gráfico de variações renderizado com sucesso');
                hideLoading('variationChart');
            }).catch(error => {
                console.error('Erro ao renderizar gráfico de variações:', error);
                hideLoading('variationChart');
            });
        } catch (error) {
            console.error('Erro ao criar gráfico de variações:', error);
            hideLoading('variationChart');
        }
    }

    // Initialize charts with retry mechanism
    checkApexCharts(() => {
        console.log('Iniciando renderização de todos os gráficos...');
        initSalesChart();
        setTimeout(initRevenueChart, 200);
        setTimeout(initVariationChart, 400);
        console.log('Todos os gráficos foram iniciados');
    });

    // Re-render charts when tabs change
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('@click') && e.target.getAttribute('@click').includes('analytics')) {
            setTimeout(() => {
                console.log('Tab Analytics ativada, re-renderizando gráficos...');
                initRevenueChart();
                initVariationChart();
            }, 300);
        } else if (e.target.getAttribute('@click') && e.target.getAttribute('@click').includes('overview')) {
            setTimeout(() => {
                console.log('Tab Overview ativada, re-renderizando gráfico de vendas...');
                initSalesChart();
            }, 300);
        }
    });

    // Listen for Livewire updates
    if (typeof Livewire !== 'undefined') {
        Livewire.on('chart-updated', () => {
            console.log('Dados atualizados pelo Livewire, re-renderizando gráficos...');
            setTimeout(() => {
                initSalesChart();
                initRevenueChart();
                initVariationChart();
            }, 100);
        });
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        setTimeout(() => {
            if (salesChart) {
                console.log('Redimensionando gráfico de vendas...');
                salesChart.updateOptions({});
            }
            if (revenueChart) {
                console.log('Redimensionando gráfico de receita...');
                revenueChart.updateOptions({});
            }
            if (variationChart) {
                console.log('Redimensionando gráfico de variações...');
                variationChart.updateOptions({});
            }
        }, 100);
    });
});
</script>

<!-- Script para funcionalidades da tabela de vendas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidades para a aba de vendas
    initSalesTable();
});

function initSalesTable() {
    // Filtros da tabela
    const periodFilter = document.querySelector('select[placeholder*="período"], select option[value="30"]')?.closest('select');
    const statusFilter = document.querySelector('select[placeholder*="status"], select option[value="all"]')?.closest('select');
    const searchInput = document.querySelector('input[placeholder*="Buscar"]');

    // Event listeners para filtros
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            filterSales();
            console.log('Filtro de período alterado:', this.value);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterSales();
            console.log('Filtro de status alterado:', this.value);
        });
    }

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterSales();
                console.log('Busca realizada:', this.value);
            }, 300);
        });
    }

    // Sorting da tabela
    const sortButtons = document.querySelectorAll('th .bi-arrow-down-up');
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const th = this.closest('th');
            const table = th.closest('table');
            const column = Array.from(th.parentNode.children).indexOf(th);

            // Remove sorting anterior
            sortButtons.forEach(btn => {
                btn.className = 'bi bi-arrow-down-up text-slate-400 hover:text-slate-600 cursor-pointer';
            });

            // Aplica novo sorting
            this.className = 'bi bi-sort-down text-blue-600 cursor-pointer';

            sortTable(table, column);
            console.log('Ordenação aplicada na coluna:', column);
        });
    });

    // Botões de ação
    const actionButtons = document.querySelectorAll('button[title]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('title');
            const row = this.closest('tr');
            const saleId = row.querySelector('.font-medium')?.textContent;

            handleSaleAction(action, saleId, row);
        });
    });

    // Paginação
    const paginationButtons = document.querySelectorAll('nav button');
    paginationButtons.forEach(button => {
        if (!button.disabled && !button.classList.contains('bg-blue-600')) {
            button.addEventListener('click', function() {
                const page = this.textContent.trim();
                if (page && !isNaN(page)) {
                    loadPage(parseInt(page));
                } else if (this.querySelector('.bi-chevron-left')) {
                    loadPage('prev');
                } else if (this.querySelector('.bi-chevron-right')) {
                    loadPage('next');
                }
            });
        }
    });
}

function filterSales() {
    const periodValue = document.querySelector('select option[value="30"]')?.closest('select')?.value || 'all';
    const statusValue = document.querySelector('select option[value="all"]')?.closest('select')?.value || 'all';
    const searchValue = document.querySelector('input[placeholder*="Buscar"]')?.value || '';

    const tbody = document.querySelector('table tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        let showRow = true;

        // Filtro por status
        if (statusValue !== 'all') {
            const statusBadge = row.querySelector('.bg-green-100, .bg-yellow-100, .bg-red-100');
            const statusText = statusBadge?.textContent.toLowerCase().trim();

            if (statusValue === 'completed' && !statusText?.includes('concluída')) {
                showRow = false;
            } else if (statusValue === 'pending' && !statusText?.includes('pendente')) {
                showRow = false;
            } else if (statusValue === 'cancelled' && !statusText?.includes('cancelada')) {
                showRow = false;
            }
        }

        // Filtro por busca
        if (searchValue && showRow) {
            const rowText = row.textContent.toLowerCase();
            if (!rowText.includes(searchValue.toLowerCase())) {
                showRow = false;
            }
        }

        // Mostrar/ocultar linha
        row.style.display = showRow ? '' : 'none';
    });

    // Atualizar contador
    updateRowCounter();
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aVal = a.children[column]?.textContent.trim() || '';
        const bVal = b.children[column]?.textContent.trim() || '';

        // Tratamento especial para valores monetários
        if (aVal.includes('R$') && bVal.includes('R$')) {
            const aNum = parseFloat(aVal.replace(/[R$.,\s]/g, '').replace(',', '.'));
            const bNum = parseFloat(bVal.replace(/[R$.,\s]/g, '').replace(',', '.'));
            return bNum - aNum; // Ordem decrescente para valores
        }

        // Tratamento para datas
        if (aVal.includes('/') && bVal.includes('/')) {
            const aDate = new Date(aVal.split('/').reverse().join('-'));
            const bDate = new Date(bVal.split('/').reverse().join('-'));
            return bDate - aDate; // Ordem decrescente para datas
        }

        // Ordenação alfabética padrão
        return aVal.localeCompare(bVal);
    });

    // Reordenar linhas
    rows.forEach(row => tbody.appendChild(row));
}

function handleSaleAction(action, saleId, row) {
    console.log(`Ação ${action} executada para venda ${saleId}`);

    switch(action) {
        case 'Ver detalhes':
            showSaleDetails(saleId);
            break;
        case 'Imprimir':
            printSale(saleId);
            break;
        case 'Duplicar':
            duplicateSale(saleId);
            break;
        case 'Editar':
            editSale(saleId);
            break;
        default:
            console.log('Ação não reconhecida:', action);
    }
}

function showSaleDetails(saleId) {
    // Aqui você implementaria a modal ou navegação para detalhes
    console.log('Mostrando detalhes da venda:', saleId);

    // Exemplo de implementação básica
    alert(`Detalhes da venda ${saleId}\n\nEsta funcionalidade abrirá uma modal com todos os detalhes da venda.`);
}

function printSale(saleId) {
    console.log('Imprimindo venda:', saleId);

    // Exemplo de implementação
    if (confirm(`Deseja imprimir a venda ${saleId}?`)) {
        // Aqui você implementaria a impressão
        window.print();
    }
}

function duplicateSale(saleId) {
    console.log('Duplicando venda:', saleId);

    // Exemplo de implementação
    if (confirm(`Deseja criar uma nova venda baseada em ${saleId}?`)) {
        // Aqui você implementaria a duplicação
        alert('Venda duplicada com sucesso!');
    }
}

function editSale(saleId) {
    console.log('Editando venda:', saleId);

    // Exemplo de implementação - redirecionamento
    // window.location.href = `/sales/edit/${saleId}`;
    alert(`Redirecionando para edição da venda ${saleId}`);
}

function loadPage(page) {
    console.log('Carregando página:', page);

    // Aqui você implementaria a paginação AJAX
    // Por enquanto, apenas simula o carregamento
    const currentPageBtn = document.querySelector('nav .bg-blue-600');
    if (currentPageBtn) {
        currentPageBtn.classList.remove('bg-blue-600', 'text-white');
        currentPageBtn.classList.add('text-slate-700', 'hover:text-slate-900', 'dark:text-slate-300', 'dark:hover:text-slate-100', 'hover:bg-slate-100', 'dark:hover:bg-slate-700');
    }

    // Simula carregamento
    if (typeof page === 'number') {
        const newPageBtn = document.querySelector(`nav button:nth-child(${page + 1})`);
        if (newPageBtn) {
            newPageBtn.classList.add('bg-blue-600', 'text-white');
            newPageBtn.classList.remove('text-slate-700', 'hover:text-slate-900', 'dark:text-slate-300', 'dark:hover:text-slate-100', 'hover:bg-slate-100', 'dark:hover:bg-slate-700');
        }
    }
}

function updateRowCounter() {
    const tbody = document.querySelector('table tbody');
    const visibleRows = tbody.querySelectorAll('tr:not([style*="display: none"])');
    const totalRows = tbody.querySelectorAll('tr').length;

    const counter = document.querySelector('.text-slate-600.dark\\:text-slate-400');
    if (counter) {
        counter.innerHTML = `Mostrando <span class="font-medium text-slate-900 dark:text-slate-100">${visibleRows.length}</span> de <span class="font-medium text-slate-900 dark:text-slate-100">${totalRows}</span> vendas`;
    }
}

// Função para exportar dados (pode ser chamada por um botão)
function exportSalesData() {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');

    let csvContent = "data:text/csv;charset=utf-8,";

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell =>
            cell.textContent.replace(/,/g, ';').trim()
        ).join(',');
        csvContent += rowData + "\r\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `vendas_produto_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log('Dados exportados com sucesso');
}
</script>

<style>
/* Prevenção de overflow horizontal */
html, body {
    overflow-x: hidden;
    max-width: 100%;
}

* {
    box-sizing: border-box;
}

/* Container principal */
.main-container {
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
}

/* Scrollbar customizado */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(59, 130, 246, 0.5) rgba(241, 245, 249, 0.1);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

/* Hide scrollbar for tabs */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Garantir que grids sejam responsivos */
.responsive-grid {
    display: grid;
    gap: 1.5rem;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .responsive-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .responsive-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .responsive-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Cards responsivos */
.responsive-card {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

/* Charts container */
.chart-container {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.chart-container #salesChart,
.chart-container #revenueChart,
.chart-container #variationChart {
    width: 100% !important;
    max-width: 100% !important;
}

/* Classes utilitárias */
.border-3 {
    border-width: 3px;
}

.hover\:scale-102:hover {
    transform: scale(1.02);
}

.shadow-3xl {
    box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

/* Animações de blob para o background */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Efeito de loading suave */
@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

.pulse-glow {
    animation: pulse-glow 2s infinite;
}

/* Efeito hover melhorado para cards */
.card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Gradiente animado */
@keyframes gradient-shift {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}

/* Efeito shimmer para loading */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

/* Responsividade melhorada */
@media (max-width: 768px) {
    .mobile-stack {
        flex-direction: column;
    }

    .mobile-full {
        width: 100%;
    }

    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

@media (max-width: 640px) {
    .px-6 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}

/* Efeitos de hover para botões */
.btn-hover {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.btn-hover:hover::before {
    left: 100%;
}

/* Melhoria na tipografia */
.text-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .dark-blur {
        backdrop-filter: blur(20px) saturate(180%);
        background-color: rgba(17, 25, 40, 0.75);
    }
}

/* Fixes para ApexCharts */
.apexcharts-svg {
    max-width: 100% !important;
    width: 100% !important;
}

.apexcharts-canvas {
    max-width: 100% !important;
    width: 100% !important;
}

/* Loading state melhorado */
.chart-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 320px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos específicos para a tabela de vendas */
.sales-table {
    border-collapse: separate;
    border-spacing: 0;
}

.sales-table th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, rgb(248 250 252) 0%, rgb(241 245 249) 100%);
}

.dark .sales-table th {
    background: linear-gradient(135deg, rgb(51 65 85) 0%, rgb(71 85 105) 100%);
}

.sales-table tbody tr {
    transition: all 0.2s ease;
}

.sales-table tbody tr:hover {
    background-color: rgb(248 250 252);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.dark .sales-table tbody tr:hover {
    background-color: rgb(51 65 85 / 0.5);
}

/* Animação para badges de status */
.status-badge {
    animation: fadeInUp 0.3s ease;
    transition: all 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para botões de ação */
.action-button {
    transition: all 0.2s ease;
    transform: scale(1);
}

.action-button:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-button:active {
    transform: scale(0.95);
}

/* Estilos para filtros */
.filter-input, .filter-select {
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.filter-input:focus, .filter-select:focus {
    border-color: rgb(59 130 246);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

/* Animação para carregamento de dados */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

.dark .loading-overlay {
    background: rgba(15, 23, 42, 0.9);
}

.loading-overlay.show {
    display: flex;
}

/* Estilos para paginação */
.pagination-button {
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.pagination-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.pagination-button:hover::before {
    left: 100%;
}

/* Highlight para linhas selecionadas */
.row-selected {
    background-color: rgb(219 234 254) !important;
    border-left: 4px solid rgb(59 130 246);
}

.dark .row-selected {
    background-color: rgb(30 58 138 / 0.3) !important;
}

/* Estilos para campos monetários */
.currency-field {
    font-family: 'Courier New', monospace;
    font-weight: 600;
}

/* Indicador de ordenação */
.sort-indicator {
    transition: all 0.2s ease;
}

.sort-indicator.active {
    color: rgb(59 130 246);
    transform: scale(1.2);
}

/* Estilos para badges de quantidade */
.quantity-badge {
    background: linear-gradient(135deg, rgb(219 234 254) 0%, rgb(191 219 254) 100%);
    border: 1px solid rgb(147 197 253);
}

.dark .quantity-badge {
    background: linear-gradient(135deg, rgb(30 58 138) 0%, rgb(29 78 216) 100%);
    border: 1px solid rgb(59 130 246);
}

/* Animação para estatísticas */
.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stats-card .icon {
    transition: all 0.3s ease;
}

.stats-card:hover .icon {
    transform: scale(1.1) rotate(5deg);
}

/* Responsividade melhorada para tabela */
@media (max-width: 1024px) {
    .sales-table {
        font-size: 0.875rem;
    }

    .sales-table th,
    .sales-table td {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 768px) {
    .sales-table {
        font-size: 0.8125rem;
    }

    .sales-table th,
    .sales-table td {
        padding: 0.5rem 0.375rem;
    }

    .action-button {
        padding: 0.375rem;
    }
}

/* Tooltip personalizado */
.tooltip {
    position: relative;
}

.tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgb(15 23 42);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 30;
}

.tooltip:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 5px);
}

/* Estilos para modo escuro */
@media (prefers-color-scheme: dark) {
    .tooltip::after {
        background: rgb(241 245 249);
        color: rgb(15 23 42);
    }
}
</style>

<!-- CSS do produtos.css -->
<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

@endpush
