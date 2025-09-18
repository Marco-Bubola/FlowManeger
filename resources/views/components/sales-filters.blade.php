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
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-flag mr-1 text-blue-500"></i>
                        Status da Venda
                    </label>
                    <select wire:model.live="statusFilter"
                            class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                        <option value="">Todos os status</option>
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                        <option value="partially_paid">Parcialmente Pago</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>

                <!-- Cliente -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-person mr-1 text-green-500"></i>
                        Cliente
                    </label>
                    <select wire:model.live="clientFilter"
                            class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                        <option value="">Todos os clientes</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Período (Data de) -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-calendar mr-1 text-indigo-500"></i>
                        Data Inicial
                    </label>
                    <input type="date"
                           wire:model.live="startDate"
                           class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                </div>

                <!-- Período (Data até) -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-calendar-check mr-1 text-indigo-500"></i>
                        Data Final
                    </label>
                    <input type="date"
                           wire:model.live="endDate"
                           class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                </div>

                <!-- Valor Mínimo -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-currency-dollar mr-1 text-emerald-500"></i>
                        Valor Mínimo
                    </label>
                    <input type="number"
                           step="0.01"
                           wire:model.live="minValue"
                           placeholder="R$ 0,00"
                           class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                </div>

                <!-- Valor Máximo -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-currency-dollar mr-1 text-emerald-500"></i>
                        Valor Máximo
                    </label>
                    <input type="number"
                           step="0.01"
                           wire:model.live="maxValue"
                           placeholder="R$ 999.999,99"
                           class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                </div>

                <!-- Forma de Pagamento -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-credit-card mr-1 text-purple-500"></i>
                        Forma de Pagamento
                    </label>
                    <select wire:model.live="paymentMethodFilter"
                            class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                        <option value="">Todas as formas</option>
                        <option value="cash">Dinheiro</option>
                        <option value="card">Cartão</option>
                        <option value="pix">PIX</option>
                        <option value="bank_transfer">Transferência</option>
                        <option value="installment">Parcelado</option>
                    </select>
                </div>

                <!-- Vendedor -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-person-badge mr-1 text-orange-500"></i>
                        Vendedor
                    </label>
                    <select wire:model.live="sellerFilter"
                            class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md">
                        <option value="">Todos os vendedores</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Filtros Rápidos -->
            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-600">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-lightning mr-1 text-yellow-500"></i>
                        Filtros Rápidos - Período
                    </h4>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
                    <button wire:click="setQuickFilter('today')"
                            class="group px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 {{ $quickFilter === 'today' ? 'ring-4 ring-blue-300' : '' }}">
                        <i class="bi bi-calendar-day mr-1"></i>
                        Hoje
                    </button>

                    <button wire:click="setQuickDateFilter('yesterday')"
                            class="group px-4 py-3 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-calendar-minus mr-1"></i>
                        Ontem
                    </button>

                    <button wire:click="setQuickFilter('week')"
                            class="group px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 {{ $quickFilter === 'week' ? 'ring-4 ring-green-300' : '' }}">
                        <i class="bi bi-calendar-week mr-1"></i>
                        Esta Semana
                    </button>

                    <button wire:click="setQuickDateFilter('last_week')"
                            class="group px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-calendar-week-fill mr-1"></i>
                        Semana Passada
                    </button>

                    <button wire:click="setQuickFilter('month')"
                            class="group px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 {{ $quickFilter === 'month' ? 'ring-4 ring-purple-300' : '' }}">
                        <i class="bi bi-calendar-month mr-1"></i>
                        Este Mês
                    </button>

                    <button wire:click="setQuickDateFilter('last_month')"
                            class="group px-4 py-3 bg-gradient-to-r from-violet-500 to-violet-600 hover:from-violet-600 hover:to-violet-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-calendar-month-fill mr-1"></i>
                        Mês Passado
                    </button>

                    <button wire:click="setQuickDateFilter('last_quarter')"
                            class="group px-4 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-calendar3 mr-1"></i>
                        Trimestre Passado
                    </button>

                    <button wire:click="setQuickDateFilter('year')"
                            class="group px-4 py-3 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="bi bi-calendar-range mr-1"></i>
                        Este Ano
                    </button>
                </div>

                <!-- Filtros de Status -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <i class="bi bi-flag mr-1 text-blue-500"></i>
                        Status Rápido
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button wire:click="setQuickFilter('pending')"
                                class="group px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 {{ $quickFilter === 'pending' ? 'ring-4 ring-yellow-300' : '' }}">
                            <i class="bi bi-clock mr-1"></i>
                            Pendentes
                        </button>

                        <button wire:click="setQuickFilter('paid')"
                                class="group px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 {{ $quickFilter === 'paid' ? 'ring-4 ring-emerald-300' : '' }}">
                            <i class="bi bi-check-circle mr-1"></i>
                            Pagas
                        </button>

                        <button wire:click="$set('statusFilter', 'partially_paid')"
                                class="group px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-pie-chart mr-1"></i>
                            Parcialmente Pagas
                        </button>

                        <button wire:click="$set('statusFilter', 'cancelled')"
                                class="group px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-x-circle mr-1"></i>
                            Canceladas
                        </button>
                    </div>
                </div>

                <!-- Filtros de Valor -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <i class="bi bi-currency-dollar mr-1 text-green-500"></i>
                        Faixas de Valor
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button wire:click="setValueRange('low')"
                                class="group px-4 py-3 bg-gradient-to-r from-slate-400 to-slate-500 hover:from-slate-500 hover:to-slate-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-coin mr-1"></i>
                            Até R$ 100
                        </button>

                        <button wire:click="setValueRange('medium')"
                                class="group px-4 py-3 bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-cash-coin mr-1"></i>
                            R$ 100 - 500
                        </button>

                        <button wire:click="setValueRange('high')"
                                class="group px-4 py-3 bg-gradient-to-r from-green-400 to-green-500 hover:from-green-500 hover:to-green-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-cash-stack mr-1"></i>
                            R$ 500 - 2.000
                        </button>

                        <button wire:click="setValueRange('premium')"
                                class="group px-4 py-3 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-gem mr-1"></i>
                            Acima R$ 2.000
                        </button>
                    </div>
                </div>

                <!-- Pesquisas Rápidas -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <i class="bi bi-search mr-1 text-purple-500"></i>
                        Pesquisas Avançadas
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button wire:click="setQuickSearch('parcelado')"
                                class="group px-4 py-2 bg-gradient-to-r from-orange-400 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-calendar-plus mr-1"></i>
                            Parcelado
                        </button>

                        <button wire:click="setQuickSearch('transferência')"
                                class="group px-4 py-2 bg-gradient-to-r from-teal-400 to-teal-500 hover:from-teal-500 hover:to-teal-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-bank mr-1"></i>
                            Transferência
                        </button>

                        <button wire:click="setQuickSearch('débito')"
                                class="group px-4 py-2 bg-gradient-to-r from-slate-400 to-slate-500 hover:from-slate-500 hover:to-slate-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-credit-card-2-front mr-1"></i>
                            Débito
                        </button>

                        <button wire:click="setQuickSearch('crédito')"
                                class="group px-4 py-2 bg-gradient-to-r from-purple-400 to-purple-500 hover:from-purple-500 hover:to-purple-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-credit-card-2-back mr-1"></i>
                            Crédito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
