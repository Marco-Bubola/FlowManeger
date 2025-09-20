@props([
    'vendas' => [],
    'filterYear' => null,
    'filterMonth' => null,
    'filterStatus' => null,
    'filterPaymentType' => null
])

<!-- Header com Filtros em Dropdown -->
<div class="w-full px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                    <i class="bi bi-speedometer2 text-white text-lg"></i>
                </div>
                Dashboard do Cliente
            </h2>
            <div class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-2xl border border-indigo-200 dark:border-indigo-700/50 shadow-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Atualizado em tempo real</span>
            </div>
        </div>

        <!-- Dropdown de Filtros Modernizado -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 hover:from-gray-50 hover:to-gray-100 dark:hover:from-gray-700 dark:hover:to-gray-600 border border-gray-200 dark:border-gray-600 rounded-2xl shadow-lg hover:shadow-xl text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-300 transform hover:scale-105">
                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-200">
                    <i class="bi bi-funnel-fill text-white text-sm"></i>
                </div>
                <span class="font-semibold">Filtros Avançados</span>
                <i class="bi bi-chevron-down ml-2 transform transition-transform duration-200 group-hover:scale-110" :class="{ 'rotate-180': open }"></i>

                <!-- Efeito hover ring -->
                <div class="absolute inset-0 rounded-2xl bg-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="transform opacity-0 scale-95 translate-y-4"
                 @click.away="open = false"
                 class="absolute right-0 mt-4 w-96 bg-white/95 dark:bg-gray-800/95 border border-gray-200 dark:border-gray-700 rounded-3xl shadow-2xl z-50 backdrop-blur-xl">

                <div class="p-8">
                    <!-- Header do Dropdown -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="bi bi-sliders text-white text-sm"></i>
                            </div>
                            Configurar Filtros
                        </h3>
                        <button wire:click="clearFilters"
                                class="group relative text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold flex items-center px-3 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-all duration-200">
                            <i class="bi bi-arrow-clockwise mr-2 group-hover:rotate-180 transition-transform duration-300"></i>
                            Limpar
                        </button>
                    </div>

                    <!-- Grid de Filtros Modernizado -->
                    <div class="space-y-6">

                        <!-- Seção de Anos -->
                        <div class="space-y-3">
                            <h4 class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="w-5 h-5 bg-gradient-to-br from-blue-500 to-indigo-500 rounded flex items-center justify-center mr-2">
                                    <i class="bi bi-calendar-year text-white text-xs"></i>
                                </div>
                                Filtrar por Ano
                            </h4>
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Todos os Anos -->
                                <button wire:click="$set('filterYear', '')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $filterYear === '' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-list-ul text-blue-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Todos</div>
                                    </div>
                                </button>

                                @for($year = now()->year; $year >= 2022; $year--)
                                    <button wire:click="$set('filterYear', '{{ $year }}')"
                                            class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $filterYear == $year ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                        <div class="text-center">
                                            <i class="bi bi-calendar text-blue-500 text-lg"></i>
                                            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $year }}</div>
                                        </div>
                                    </button>
                                @endfor
                            </div>
                        </div>

                        <!-- Seção de Meses -->
                        <div class="space-y-3">
                            <h4 class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="w-5 h-5 bg-gradient-to-br from-green-500 to-emerald-500 rounded flex items-center justify-center mr-2">
                                    <i class="bi bi-calendar-month text-white text-xs"></i>
                                </div>
                                Filtrar por Mês
                            </h4>
                            <div class="grid grid-cols-4 gap-2">
                                <!-- Todos os Meses -->
                                <button wire:click="$set('filterMonth', '')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $filterMonth === '' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-list-ul text-green-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Todos</div>
                                    </div>
                                </button>

                                @php
                                    $meses = [
                                        1 => ['nome' => 'Jan', 'icon' => 'bi-snow'],
                                        2 => ['nome' => 'Fev', 'icon' => 'bi-heart'],
                                        3 => ['nome' => 'Mar', 'icon' => 'bi-flower1'],
                                        4 => ['nome' => 'Abr', 'icon' => 'bi-sun'],
                                        5 => ['nome' => 'Mai', 'icon' => 'bi-flower2'],
                                        6 => ['nome' => 'Jun', 'icon' => 'bi-brightness-high'],
                                        7 => ['nome' => 'Jul', 'icon' => 'bi-fire'],
                                        8 => ['nome' => 'Ago', 'icon' => 'bi-thermometer-sun'],
                                        9 => ['nome' => 'Set', 'icon' => 'bi-leaf'],
                                        10 => ['nome' => 'Out', 'icon' => 'bi-tree'],
                                        11 => ['nome' => 'Nov', 'icon' => 'bi-cloud-rain'],
                                        12 => ['nome' => 'Dez', 'icon' => 'bi-gift']
                                    ];
                                @endphp

                                @foreach($meses as $num => $mes)
                                    <button wire:click="$set('filterMonth', '{{ $num }}')"
                                            class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $filterMonth == $num ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                                        <div class="text-center">
                                            <i class="{{ $mes['icon'] }} text-green-500 text-lg"></i>
                                            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $mes['nome'] }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Seção de Status -->
                        <div class="space-y-3">
                            <h4 class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="w-5 h-5 bg-gradient-to-br from-purple-500 to-pink-500 rounded flex items-center justify-center mr-2">
                                    <i class="bi bi-flag text-white text-xs"></i>
                                </div>
                                Status das Vendas
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Todos os Status -->
                                <button wire:click="$set('filterStatus', '')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 {{ $filterStatus === '' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-list-ul text-purple-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Todos</div>
                                    </div>
                                </button>

                                <!-- Pago -->
                                <button wire:click="$set('filterStatus', 'pago')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $filterStatus === 'pago' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-check-circle text-green-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Pago</div>
                                    </div>
                                </button>

                                <!-- Pendente -->
                                <button wire:click="$set('filterStatus', 'pendente')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all duration-200 {{ $filterStatus === 'pendente' ? 'ring-2 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-clock text-yellow-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Pendente</div>
                                    </div>
                                </button>

                                <!-- Cancelado -->
                                <button wire:click="$set('filterStatus', 'cancelado')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 {{ $filterStatus === 'cancelado' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-x-circle text-red-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Cancelado</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Seção de Tipos de Pagamento -->
                        <div class="space-y-3">
                            <h4 class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <div class="w-5 h-5 bg-gradient-to-br from-orange-500 to-red-500 rounded flex items-center justify-center mr-2">
                                    <i class="bi bi-credit-card text-white text-xs"></i>
                                </div>
                                Tipo de Pagamento
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Todos os Tipos -->
                                <button wire:click="$set('filterPaymentType', '')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 {{ $filterPaymentType === '' ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-list-ul text-orange-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Todos</div>
                                    </div>
                                </button>

                                <!-- Dinheiro -->
                                <button wire:click="$set('filterPaymentType', 'dinheiro')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 {{ $filterPaymentType === 'dinheiro' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-cash text-green-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Dinheiro</div>
                                    </div>
                                </button>

                                <!-- Cartão -->
                                <button wire:click="$set('filterPaymentType', 'cartao')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 {{ $filterPaymentType === 'cartao' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-credit-card-2-front text-blue-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">Cartão</div>
                                    </div>
                                </button>

                                <!-- PIX -->
                                <button wire:click="$set('filterPaymentType', 'pix')"
                                        class="group p-3 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 {{ $filterPaymentType === 'pix' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : '' }}">
                                    <div class="text-center">
                                        <i class="bi bi-qr-code text-teal-500 text-lg"></i>
                                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">PIX</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Footer com Estatísticas -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <i class="bi bi-search text-indigo-500 mr-2"></i>
                                    <span class="font-medium">Resultados:</span>
                                </div>
                                <div class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-full">
                                    <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ count($vendas) }} vendas</span>
                                </div>
                            </div>

                            <!-- Indicador de filtros ativos -->
                            @php
                                $filtrosAtivos = collect([$filterYear, $filterMonth, $filterStatus, $filterPaymentType])->filter()->count();
                            @endphp

                            @if($filtrosAtivos > 0)
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                                        {{ $filtrosAtivos }} filtro{{ $filtrosAtivos > 1 ? 's' : '' }} ativo{{ $filtrosAtivos > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
