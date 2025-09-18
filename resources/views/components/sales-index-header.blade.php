@props([
    'totalSales' => 0,
    'pendingSales' => 0,
    'todaySales' => 0,
    'monthSales' => 0,
    'totalRevenue' => 0,
    'pendingRevenue' => 0,
    'status' => '',
    'paymentType' => '',
    'search' => ''
])

<div class="bg-gradient-to-br from-white via-blue-50 to-indigo-100 dark:from-gray-800 dark:via-gray-900 dark:to-indigo-900 rounded-3xl p-8 mb-8 border border-gray-200 dark:border-gray-700 shadow-xl">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

        <!-- Header Principal -->
        <div class="flex-1">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-cart text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 via-indigo-800 to-purple-700 dark:from-white dark:via-indigo-200 dark:to-purple-300 bg-clip-text text-transparent">
                        Vendas
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">Gerencie e monitore todas as suas vendas</p>
                </div>
            </div>

            <!-- Métricas Essenciais -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total de Vendas -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-cart-check text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalSales }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total de Vendas</p>
                        </div>
                    </div>
                </div>

                <!-- Vendas Pendentes -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-clock text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingSales }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pendentes</p>
                        </div>
                    </div>
                </div>

                <!-- Vendas Hoje -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-calendar-check text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $todaySales }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Hoje</p>
                        </div>
                    </div>
                </div>

                <!-- Receita Total -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-currency-dollar text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Receita Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Ações e Filtros -->
        <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-white/50 dark:border-gray-700/50 lg:min-w-[400px]">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <i class="bi bi-funnel text-indigo-600 mr-2"></i>
                Filtros e Ações
            </h3>

            <div class="space-y-4">
                <!-- Barra de Pesquisa -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por cliente, ID da venda..."
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">
                </div>

                <!-- Filtros Rápidos -->
                <div class="flex flex-wrap gap-2">
                    <button wire:click="$set('status', 'pendente')"
                            class="px-3 py-1.5 text-xs font-medium rounded-full {{ $status === 'pendente' ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }} transition-colors">
                        <i class="bi bi-clock mr-1"></i>Pendentes
                    </button>
                    <button wire:click="$set('status', 'finalizada')"
                            class="px-3 py-1.5 text-xs font-medium rounded-full {{ $status === 'finalizada' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200' }} transition-colors">
                        <i class="bi bi-check-circle mr-1"></i>Finalizadas
                    </button>
                    <button wire:click="$set('payment_type', 'parcelado')"
                            class="px-3 py-1.5 text-xs font-medium rounded-full {{ $paymentType === 'parcelado' ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }} transition-colors">
                        <i class="bi bi-credit-card mr-1"></i>Parceladas
                    </button>
                    <button wire:click="clearFilters"
                            class="px-3 py-1.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                        <i class="bi bi-x mr-1"></i>Limpar
                    </button>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-3">
                    <!-- Nova Venda -->
                    <a href="{{ route('sales.create') }}"
                       class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="bi bi-plus-circle mr-2"></i>Nova Venda
                    </a>

                    <!-- Exportar -->
                    <button class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-all duration-200">
                        <i class="bi bi-download mr-2 text-blue-600"></i>
                        Exportar
                    </button>

                    <!-- Atualizar -->
                    <button wire:click="$refresh" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-all duration-200">
                        <i class="bi bi-arrow-clockwise mr-2 text-green-600"></i>
                        Atualizar
                    </button>
                </div>

                <!-- Filtros Avançados Toggle -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-xl shadow-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                        <i class="bi bi-sliders mr-2"></i>Filtros Avançados
                        <i class="bi bi-chevron-down ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.away="open = false"
                         class="absolute right-0 top-full mt-2 w-full bg-white dark:bg-zinc-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-zinc-700"
                         style="display: none;">

                        <div class="p-4 space-y-4">
                            <!-- Período -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Inicial</label>
                                    <input type="date" wire:model.live="date_start"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Final</label>
                                    <input type="date" wire:model.live="date_end"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>

                            <!-- Valor -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Valor Mínimo</label>
                                    <input type="number" wire:model.live="min_value" placeholder="R$ 0,00"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Valor Máximo</label>
                                    <input type="number" wire:model.live="max_value" placeholder="R$ 9999,99"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>

                            <!-- Itens por página -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Itens por página</label>
                                <select wire:model.live="perPage"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                    <option value="12">12 por página</option>
                                    <option value="24">24 por página</option>
                                    <option value="48">48 por página</option>
                                    <option value="96">96 por página</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
