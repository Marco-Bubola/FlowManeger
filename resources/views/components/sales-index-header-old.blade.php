@props([
'title' => 'Vendas',
'description' => '',
'backRoute' => null,
'currentStep' => null,
'steps' => [],
'totalSales' => 0,
'pendingSales' => 0,
'todaySales' => 0,
'totalRevenue' => 0,
'showQuickActions' => true,
'showSteps' => false,
'sales' => null,
'search' => '',
'sortBy' => 'created_at',
'sortDirection' => 'desc',
'statusFilter' => '',
'clientFilter' => '',
'startDate' => '',
'endDate' => '',
'minValue' => '',
'maxValue' => ''
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="sales-index-header relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-4 py-3 sales-index-header-inner">
        <!-- Primeira Linha: Título, Estatísticas e Barra de Controle -->
        <div class="sales-index-header-main flex items-center justify-between gap-6 mb-6">
            <!-- Lado Esquerdo: Título e Estatísticas -->
            <div class="sales-index-header-left flex items-center gap-6">
                @if($backRoute)
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <div class="absolute inset-0 rounded-2xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <div class="sales-index-header-icon relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-cart' }} text-white text-3xl"></i>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="sales-index-header-title-wrap space-y-2">
                    @isset($breadcrumb)
                    {{ $breadcrumb }}
                    @endisset
                    <h1 class="sales-index-header-title text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>

                    @if(!$showSteps)
                    <div class="sales-index-header-stats flex items-center gap-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                            <i class="bi bi-cart-check text-emerald-600 dark:text-emerald-400"></i>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $totalSales }} vendas</span>
                        </div>
                        @if($pendingSales > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-xl border border-yellow-200 dark:border-yellow-700">
                            <i class="bi bi-clock text-yellow-600 dark:text-yellow-400"></i>
                            <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">{{ $pendingSales }} pendentes</span>
                        </div>
                        @endif
                        @if($todaySales > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                            <i class="bi bi-calendar-check text-blue-600 dark:text-blue-400"></i>
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $todaySales }} hoje</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Campo de Busca (Crescer para preencher espaço) -->
            @if($sales && !$showSteps)
            <div class="sales-index-header-search-wrapper relative group flex-1 max-w-md mx-6">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar vendas..."
                    class="w-full pl-11 pr-10 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 transition-all duration-200 shadow-lg text-sm font-medium">
                <div class="absolute left-3.5 top-1/2 transform -translate-y-1/2">
                    <i class="bi bi-search text-lg text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                </div>
                <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                    class="absolute right-2.5 top-1/2 transform -translate-y-1/2 p-1.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                    <i class="bi bi-x text-sm"></i>
                </button>
            </div>

            <!-- Botão Nova Venda (Destaque) -->
            <a href="{{ route('sales.create') }}"
                class="sales-index-header-btn-create group flex items-center gap-2.5 px-5 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-2xl transform hover:scale-105">
                <i class="bi bi-plus-circle text-lg group-hover:rotate-90 transition-transform duration-300"></i>
                <span>Nova Venda</span>
            </a>
            @endif
        </div>

        <!-- Segunda Linha: Filtros e Controles -->
        @if($sales && !$showSteps)
        <div class="sales-index-header-secondary flex items-center justify-between gap-4 pt-4 pb-2 border-t border-slate-200/50 dark:border-slate-700/50">
            <!-- Lado Esquerdo: Filtros Rápidos -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-1 py-1 bg-white dark:bg-slate-800/50 rounded-xl shadow-sm border border-slate-200 dark:border-slate-600">
                    <button wire:click="$set('statusFilter', '')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
                        :class="{{ json_encode($statusFilter === '') }} ? 'bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-600 dark:to-slate-800 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        title="Mostrar todos">
                        <i class="bi bi-grid-3x3-gap-fill mr-1.5"></i>Todos
                    </button>
                    <button wire:click="$set('statusFilter', 'pendente')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
                        :class="{{ json_encode($statusFilter === 'pendente') }} ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        title="Somente Pendentes">
                        <i class="bi bi-clock-history mr-1.5"></i>Pendentes
                    </button>
                    <button wire:click="$set('statusFilter', 'pago')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200"
                        :class="{{ json_encode($statusFilter === 'pago') }} ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        title="Somente Pagos">
                        <i class="bi bi-check-circle-fill mr-1.5"></i>Pagos
                    </button>
                </div>

                <div class="h-8 w-px bg-slate-300 dark:bg-slate-600"></div>

                <button wire:click="$set('perPage', 64)"
                    class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 shadow-sm"
                    title="Exibir 64 por página">
                    <i class="bi bi-grid text-indigo-600 dark:text-indigo-400"></i>
                    <span>64/página</span>
                </button>
            </div>

            <!-- Lado Direito: Paginação e Ações -->
            <div class="flex items-center gap-3">
                @if ($sales->hasPages())
                <div class="flex items-center gap-1.5 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5 shadow-sm">
                    @if ($sales->currentPage() > 1)
                    <button wire:click.prevent="previousPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                        <i class="bi bi-chevron-left text-sm text-slate-600 dark:text-slate-300"></i>
                    </button>
                    @endif
                    <span class="px-3 py-1 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/50 rounded-lg">
                        {{ $sales->currentPage() }} / {{ $sales->lastPage() }}
                    </span>
                    @if ($sales->hasMorePages())
                    <button wire:click.prevent="nextPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                        <i class="bi bi-chevron-right text-sm text-slate-600 dark:text-slate-300"></i>
                    </button>
                    @endif
                </div>
                @endif

                <button wire:click="toggleTips"
                    class="p-2.5 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105"
                    title="Dicas">
                    <i class="bi bi-lightbulb text-base"></i>
                </button>

                <button @click="showFilters = !showFilters"
                    class="p-2.5 bg-white hover:bg-purple-100 dark:bg-slate-800/50 dark:hover:bg-purple-900/50 border border-slate-200 dark:border-slate-600 rounded-xl transition-all duration-200 shadow-sm"
                    :class="{ 'bg-purple-100 dark:bg-purple-900 border-purple-300 dark:border-purple-600': showFilters }"
                    title="Filtros Avançados">
                    <i class="bi bi-funnel text-base text-purple-600 dark:text-purple-400"></i>
                </button>
            </div>
        </div>
        @endif
    </div>



    <!-- Steppers Modernos -->
    @if($showSteps && count($steps) > 0)
    <div class="flex items-center justify-center">
        <div class="flex items-center space-x-6">
            @foreach($steps as $index => $step)
            @php $stepNumber = $index + 1; @endphp

            <!-- Step -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                    :class="currentStep === {{ $stepNumber }} ? 'bg-gradient-to-br {{ $step['gradient'] ?? 'from-indigo-500 to-purple-500' }} text-white shadow-lg shadow-indigo-500/30' : (currentStep > {{ $stepNumber }} ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                    <i class="bi {{ $step['icon'] ?? 'bi-circle' }} text-xl" x-show="currentStep === {{ $stepNumber }}"></i>
                    <i class="bi bi-check-lg text-xl" x-show="currentStep > {{ $stepNumber }}"></i>
                </div>
                <div class="ml-4">
                    <div class="flex items-center">
                        <p class="text-lg font-bold transition-colors duration-300"
                            :class="currentStep === {{ $stepNumber }} ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">{{ $step['title'] }}</p>
                        <i class="bi bi-check-circle-fill text-green-500 ml-2 text-lg" x-show="currentStep > {{ $stepNumber }}"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                </div>
            </div>

            <!-- Connector -->
            @if(!$loop->last)
            <div class="w-16 h-1 rounded-full transition-all duration-300"
                :class="currentStep >= {{ $stepNumber + 1 }} ? 'bg-gradient-to-r {{ $step['connector_gradient'] ?? 'from-indigo-500 to-purple-500' }}' : 'bg-gray-300 dark:bg-zinc-600'"></div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
</div>
