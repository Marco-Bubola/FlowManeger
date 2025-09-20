@props([
    'showFilters' => false,
    'clients' => collect(),
    'sellers' => collect(),
    'statusFilter' => '',
    'clientFilter' => '',
    'startDate' => '',
    'endDate' => '',
    'minValue' => '',
    'maxValue' => '',
    'paymentMethodFilter' => '',
    'sellerFilter' => '',
    'quickFilter' => '',
    'sortBy' => 'created_at',
    'sortDirection' => 'desc'
])

<!-- Filtros Avançados -->
<div x-show="showFilters"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-2"
     class="mb-6">

    <div class="relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900 rounded-3xl border border-slate-200/50 dark:border-slate-600/50 shadow-xl shadow-blue-500/5 dark:shadow-blue-500/10 backdrop-blur-xl">
        <!-- Fundo decorativo -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 via-transparent to-purple-50/50 dark:from-blue-900/20 dark:via-transparent dark:to-purple-900/20"></div>

        <!-- Header do painel de filtros -->
        <div class="relative px-6 py-4 border-b border-slate-200/50 dark:border-slate-600/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl shadow-lg">
                        <i class="bi bi-funnel text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Filtros Avançados</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Refine sua busca com filtros específicos</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="clearFilters"
                            class="group px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-x-circle mr-1 group-hover:rotate-90 transition-transform duration-200"></i>
                        Limpar
                    </button>
                    <button @click="showFilters = false"
                            class="group p-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500 text-slate-600 dark:text-slate-300 rounded-xl transition-all duration-200">
                        <i class="bi bi-x-lg group-hover:rotate-90 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Conteúdo dos filtros -->
        <div class="relative p-6">
            <!-- Seção de Ordenação -->
            <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl border border-indigo-200/50 dark:border-indigo-700/50">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                    <i class="bi bi-arrow-up-down mr-1 text-indigo-500"></i>
                    Ordenação
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <!-- Ordenar por Data -->
                    <button wire:click="sortByField('created_at')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 {{ $sortBy === 'created_at' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-calendar text-indigo-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Data</div>
                            </div>
                            @if($sortBy === 'created_at')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-indigo-500 text-sm"></i>
                            @endif
                        </div>
                    </button>

                    <!-- Ordenar por Valor -->
                    <button wire:click="sortByField('total_price')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $sortBy === 'total_price' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-currency-dollar text-green-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Valor</div>
                            </div>
                            @if($sortBy === 'total_price')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-green-500 text-sm"></i>
                            @endif
                        </div>
                    </button>

                    <!-- Ordenar por Cliente -->
                    <button wire:click="sortByField('client_name')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $sortBy === 'client_name' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-person text-blue-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Cliente</div>
                            </div>
                            @if($sortBy === 'client_name')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-blue-500 text-sm"></i>
                            @endif
                        </div>
                    </button>

                    <!-- Ordenar por Status -->
                    <button wire:click="sortByField('status')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $sortBy === 'status' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-flag text-purple-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Status</div>
                            </div>
                            @if($sortBy === 'status')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-purple-500 text-sm"></i>
                            @endif
                        </div>
                    </button>

                    <!-- Ordenar por ID -->
                    <button wire:click="sortByField('id')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $sortBy === 'id' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-hash text-orange-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">ID</div>
                            </div>
                            @if($sortBy === 'id')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-orange-500 text-sm"></i>
                            @endif
                        </div>
                    </button>

                    <!-- Ordenar por Atualização -->
                    <button wire:click="sortByField('updated_at')"
                            class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500 transition-all duration-200 {{ $sortBy === 'updated_at' ? 'ring-2 ring-pink-500 bg-pink-50 dark:bg-pink-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <i class="bi bi-arrow-clockwise text-pink-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Atualização</div>
                            </div>
                            @if($sortBy === 'updated_at')
                                <i class="bi bi-{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }} text-pink-500 text-sm"></i>
                            @endif
                        </div>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Status da Venda -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-flag mr-1 text-blue-500"></i>
                        Status da Venda
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Todos os Status -->
                        <button wire:click="$set('statusFilter', '')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $statusFilter === '' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-list-ul text-blue-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todos</div>
                            </div>
                        </button>

                        <!-- Pendente -->
                        <button wire:click="$set('statusFilter', 'pending')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all duration-200 {{ $statusFilter === 'pending' ? 'ring-2 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-clock text-yellow-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Pendente</div>
                            </div>
                        </button>

                        <!-- Pago -->
                        <button wire:click="$set('statusFilter', 'paid')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $statusFilter === 'paid' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-check-circle text-green-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Pago</div>
                            </div>
                        </button>

                        <!-- Parcialmente Pago -->
                        <button wire:click="$set('statusFilter', 'partially_paid')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $statusFilter === 'partially_paid' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-pie-chart text-orange-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Parcial</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Formas de Pagamento -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-credit-card mr-1 text-purple-500"></i>
                        Forma de Pagamento
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Todas -->
                        <button wire:click="$set('paymentMethodFilter', '')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $paymentMethodFilter === '' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-list-ul text-purple-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todas</div>
                            </div>
                        </button>

                        <!-- Dinheiro -->
                        <button wire:click="$set('paymentMethodFilter', 'cash')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $paymentMethodFilter === 'cash' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-cash text-green-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Dinheiro</div>
                            </div>
                        </button>

                        <!-- Cartão -->
                        <button wire:click="$set('paymentMethodFilter', 'card')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $paymentMethodFilter === 'card' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-credit-card-2-front text-blue-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Cartão</div>
                            </div>
                        </button>

                        <!-- PIX -->
                        <button wire:click="$set('paymentMethodFilter', 'pix')"
                                class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 {{ $paymentMethodFilter === 'pix' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : '' }}">
                            <div class="text-center">
                                <i class="bi bi-qr-code text-teal-500 text-lg"></i>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">PIX</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Período de Datas -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-calendar-range mr-1 text-indigo-500"></i>
                        Período
                    </h4>
                    <div class="space-y-3">
                        <!-- Data Inicial -->
                        <div class="relative">
                            <input type="date"
                                   wire:model.live="startDate"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $startDate ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                            <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                Data Inicial
                            </label>
                        </div>

                        <!-- Data Final -->
                        <div class="relative">
                            <input type="date"
                                   wire:model.live="endDate"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $endDate ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                            <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                Data Final
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Valores (Min/Max) -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-currency-dollar mr-1 text-emerald-500"></i>
                        Faixa de Valores
                    </h4>
                    <div class="space-y-3">
                        <!-- Valor Mínimo -->
                        <div class="relative">
                            <input type="number"
                                   step="0.01"
                                   wire:model.live="minValue"
                                   placeholder="0,00"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $minValue ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                            <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                Valor Mínimo
                            </label>
                        </div>

                        <!-- Valor Máximo -->
                        <div class="relative">
                            <input type="number"
                                   step="0.01"
                                   wire:model.live="maxValue"
                                   placeholder="999.999,99"
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $maxValue ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : '' }}">
                            <label class="absolute -top-2 left-3 px-1 bg-white dark:bg-slate-700 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                Valor Máximo
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Seção separada para Cliente e Vendedor (campos mais específicos) -->
            <div class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Cliente -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-person mr-1 text-blue-500"></i>
                            Cliente Específico
                        </h4>
                        <div class="relative">
                            <select wire:model.live="clientFilter"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $clientFilter ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                <option value="">Selecionar cliente...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="bi bi-chevron-down text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Vendedor -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-person-badge mr-1 text-orange-500"></i>
                            Vendedor Específico
                        </h4>
                        <div class="relative">
                            <select wire:model.live="sellerFilter"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md {{ $sellerFilter ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                                <option value="">Selecionar vendedor...</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="bi bi-chevron-down text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
