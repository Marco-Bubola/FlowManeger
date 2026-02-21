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

    <div class="relative px-5 py-4 sales-index-header-inner">
        <!-- LINHA 1: Título + Badges + Busca + Nova Venda -->
        <div class="sales-index-header-row-1">
            <!-- Ícone + Título -->
            <div class="sales-index-header-left">
                @if($backRoute)
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg border border-white/50 dark:border-slate-600/50">
                    <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400"></i>
                </a>
                @endif

                <div class="sales-index-header-icon">
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-cart' }} text-white text-2xl"></i>
                </div>

                <div class="sales-index-header-title-wrap">
                    @isset($breadcrumb)
                    {{ $breadcrumb }}
                    @endisset
                    <h1 class="sales-index-header-title">{{ $title }}</h1>
                </div>
            </div>

            @if(!$showSteps)
            <!-- Badges de estatísticas -->
            <div class="sales-index-header-badges">
                <div class="sale-badge sale-badge-success">
                    <i class="bi bi-cart-check"></i>
                    <span>{{ $totalSales }} vendas</span>
                </div>
                @if($pendingSales > 0)
                <div class="sale-badge sale-badge-warning">
                    <i class="bi bi-clock"></i>
                    <span>{{ $pendingSales }} pendentes</span>
                </div>
                @endif
                @if($todaySales > 0)
                <div class="sale-badge sale-badge-info">
                    <i class="bi bi-calendar-check"></i>
                    <span>{{ $todaySales }} hoje</span>
                </div>
                @endif
            </div>
            @endif

            <!-- Busca (flex-grow para ocupar espaço) -->
            @if($sales && !$showSteps)
            <div class="sales-index-header-search relative group">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar vendas..."
                    class="w-full pl-11 pr-10 py-2.5 bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500/40 focus:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium backdrop-blur-sm">
                <div class="absolute left-3.5 top-1/2 transform -translate-y-1/2">
                    <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                </div>
                <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                    class="absolute right-2.5 top-1/2 transform -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                    <i class="bi bi-x text-sm"></i>
                </button>
            </div>

            <!-- Botão Nova Venda -->
            <a href="{{ route('sales.create') }}"
                class="sales-index-header-btn-create group">
                <i class="bi bi-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                <span>Nova Venda</span>
            </a>
            @endif
        </div>

        <!-- LINHA 2: Filtros + Paginação + Ações -->
        @if($sales && !$showSteps)
        <div class="sales-index-header-row-2">
            <!-- Lado Esquerdo: Filtros pill group -->
            <div class="sales-index-header-row-2-left">
                <div class="sale-filter-pills">
                    <button type="button" wire:click="$set('statusFilter', '')"
                        class="sale-filter-pill {{ $statusFilter === '' ? 'active' : '' }}"
                        title="Mostrar todos">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>Todos</span>
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'pendente')"
                        class="sale-filter-pill pill-warning {{ $statusFilter === 'pendente' ? 'active' : '' }}"
                        title="Somente Pendentes">
                        <i class="bi bi-clock-history"></i>
                        <span>Pendentes</span>
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'pago')"
                        class="sale-filter-pill pill-success {{ $statusFilter === 'pago' ? 'active' : '' }}"
                        title="Somente Pagos">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Pagos</span>
                    </button>
                </div>

                <!-- Seletor de itens por página estilo pills -->
                <div class="sale-filter-pills sale-perpage-pills">
                    @php $currentPerPage = $sales->perPage(); @endphp
                    @foreach([12, 24, 48, 64] as $pp)
                    <button type="button" wire:click="$set('perPage', {{ $pp }})"
                        class="sale-filter-pill pill-perpage {{ $currentPerPage == $pp ? 'active' : '' }}"
                        title="{{ $pp }} por página">
                        <span>{{ $pp }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Lado Direito: Paginação + Dicas + Filtros -->
            <div class="sales-index-header-row-2-right">
                @if ($sales->hasPages())
                <div class="sale-pagination-compact">
                    @if ($sales->currentPage() > 1)
                    <button type="button" wire:click.prevent="previousPage" class="sale-pagination-btn">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    @endif
                    <span class="sale-pagination-indicator">
                        {{ $sales->currentPage() }} / {{ $sales->lastPage() }}
                    </span>
                    @if ($sales->hasMorePages())
                    <button type="button" wire:click.prevent="nextPage" class="sale-pagination-btn">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    @endif
                </div>
                @endif

                <button type="button" wire:click="toggleTips"
                    class="sale-action-btn sale-action-tips" title="Dicas">
                    <i class="bi bi-lightbulb"></i>
                </button>

                <button type="button" @click="showFilters = !showFilters"
                    class="sale-action-btn sale-action-filter"
                    :class="{ 'active': showFilters }" title="Filtros Avançados">
                    <i class="bi bi-sliders"></i>
                </button>
            </div>
        </div>
        @endif
    </div>{{-- /sales-index-header-inner --}}
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
