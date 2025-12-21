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
    ,'perPageOptions' => []
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
        <div class="relative  border-b border-slate-200/50 dark:border-slate-600/50">
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
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <!-- Coluna 1: Status da Venda -->
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

                <!-- Coluna 2: Forma de Pagamento -->
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

                <!-- Coluna 3: Itens por página -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-list-ol mr-1 text-amber-500"></i>
                        Itens por página
                    </h4>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($perPageOptions as $option)
                            <button wire:click="$set('perPage', {{ $option }})"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-amber-300 dark:hover:border-amber-500 transition-all duration-200 {{ (isset($perPage) && $perPage == $option) ? 'ring-2 ring-amber-500 bg-amber-50 dark:bg-amber-900/30' : '' }}">
                                <div class="text-center">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $option }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Coluna 4: Ordenação -->
                <div>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                            <i class="bi bi-arrow-up-down mr-1 text-indigo-500"></i>
                            Ordenação
                        </h4>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
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

            </div>
        </div>
    </div>
</div>
