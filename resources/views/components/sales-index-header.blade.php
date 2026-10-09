@props([
    'tabsSection' => null,
    'tabsActive' => null,
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
'maxValue' => '',
'quickFilter' => ''
])

<style>
    /* Os CSS antigos de celular pintam o cabeçalho de escuro com texto branco;
       aqui o cabeçalho é claro, então os botões da segunda linha voltam a ter contraste. */
    @media (max-width: 1024px) {
        .sales-index-header-v2.sales-index-header-v2 .sale-filter-pill,
        .sales-index-header-v2.sales-index-header-v2 .sale-pagination-btn,
        .sales-index-header-v2.sales-index-header-v2 .sale-pagination-indicator,
        .sales-index-header-v2.sales-index-header-v2 .sale-action-btn {
            background: rgba(255, 255, 255, 0.85) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            color: #475569 !important;
        }
        .sales-index-header-v2.sales-index-header-v2 .sale-filter-pill.active,
        .sales-index-header-v2.sales-index-header-v2 .sale-action-btn.active {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: #fff !important;
        }
        .dark .sales-index-header-v2.sales-index-header-v2 .sale-filter-pill:not(.active),
        .dark .sales-index-header-v2.sales-index-header-v2 .sale-pagination-btn,
        .dark .sales-index-header-v2.sales-index-header-v2 .sale-pagination-indicator,
        .dark .sales-index-header-v2.sales-index-header-v2 .sale-action-btn:not(.active) {
            background: rgba(30, 41, 59, 0.85) !important;
            border-color: rgba(71, 85, 105, 0.7) !important;
            color: #cbd5e1 !important;
        }
    }
</style>

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="sales-index-header-v2 relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
    <!-- Efeito de brilho sutil -->
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.12),transparent_32%)]"></div>

    <!-- Background decorativo -->
    <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <!-- LINHA 1: Título + números + busca + nova venda -->
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                @if($backRoute)
                <a href="{{ $backRoute }}" title="Voltar"
                    class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300"></i>
                </a>
                @endif
                <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-cart' }} text-white text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-house mr-1"></i>Início</a>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="text-indigo-600 dark:text-indigo-300"><i class="bi bi-cart mr-1"></i>{{ $title }}</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">{{ $title }}</h1>
                    @if(!$showSteps)
                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1"><i class="bi bi-cart-check"></i>{{ $totalSales }} vendas</span>
                        @if($pendingSales > 0)
                        <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400"><i class="bi bi-clock"></i>{{ $pendingSales }} pendentes</span>
                        @endif
                        @if($todaySales > 0)
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><i class="bi bi-calendar-check"></i>{{ $todaySales }} hoje</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @if($sales && !$showSteps)
            <div class="flex flex-1 flex-col sm:flex-row sm:items-center gap-2 xl:justify-end">
                <div class="relative group sm:flex-1 xl:max-w-sm">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente ou número"
                        class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-slate-200/80 dark:border-slate-600/80 bg-white/90 dark:bg-slate-800/90 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 shadow-sm">
                    <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0" x-cloak title="Limpar busca"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-lg text-slate-500 hover:bg-rose-500 hover:text-white">
                        <i class="bi bi-x text-sm"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.create', ['scanner' => 1]) }}" title="Criar venda com scanner"
                        class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                        <i class="bi bi-upc-scan text-indigo-500"></i>Scanner
                    </a>
                    <a href="{{ route('sales.create') }}"
                        class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition">
                        <i class="bi bi-plus-lg"></i>Nova venda
                    </a>
                </div>
            </div>
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
                        class="sale-filter-pill pill-warning sales-mobile-hide hidden md:inline-flex {{ $statusFilter === 'pendente' ? 'active' : '' }}"
                        title="Somente Pendentes">
                        <i class="bi bi-clock-history"></i>
                        <span>Pendentes</span>
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'pago')"
                        class="sale-filter-pill pill-success sales-mobile-hide hidden md:inline-flex {{ $statusFilter === 'pago' ? 'active' : '' }}"
                        title="Somente Pagos">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Pagos</span>
                    </button>
                </div>

                <!-- Seletor de itens por página estilo pills -->
                <div class="sale-filter-pills sale-perpage-pills sales-mobile-hide hidden md:inline-flex">
                    @php $currentPerPage = $sales->perPage(); @endphp
                    @foreach([12, 24, 48, 64] as $pp)
                    <button type="button" wire:click="$set('perPage', {{ $pp }})"
                        class="sale-filter-pill pill-perpage {{ $currentPerPage == $pp ? 'active' : '' }}"
                        title="{{ $pp }} por página">
                        <span>{{ $pp }}</span>
                    </button>
                    @endforeach
                </div>

                <!-- Ordenação rápida: oculto no mobile -->
                <div class="sale-filter-pills sale-sort-pills sales-mobile-hide hidden md:flex">
                    <span class="sale-filter-pill-label">
                        <i class="bi bi-arrow-down-up"></i>
                       
                    </span>

                    <button type="button" wire:click="setSortOrder('created_at', 'desc')"
                        class="sale-filter-pill {{ $sortBy === 'created_at' && $sortDirection === 'desc' ? 'active' : '' }}"
                        title="Mais recentes">
                        <span>Recentes</span>
                    </button>

                    <button type="button" wire:click="setSortOrder('created_at', 'asc')"
                        class="sale-filter-pill {{ $sortBy === 'created_at' && $sortDirection === 'asc' ? 'active' : '' }}"
                        title="Mais antigas">
                        <span>Antigas</span>
                    </button>

                    <button type="button" wire:click="setSortOrder('total_price', 'desc')"
                        class="sale-filter-pill {{ $sortBy === 'total_price' && $sortDirection === 'desc' ? 'active' : '' }}"
                        title="Maior valor">
                        <span>Maior valor</span>
                    </button>
                </div>

                <!-- Filtro rápido de período: oculto no mobile -->
                <div class="sale-filter-pills sale-period-pills sales-mobile-hide hidden md:flex">
                    <span class="sale-filter-pill-label">
                        <i class="bi bi-calendar-week"></i>
                       
                    </span>

                    <button type="button" wire:click="setQuickFilter('today')"
                        class="sale-filter-pill {{ $quickFilter === 'today' ? 'active' : '' }}"
                        title="Vendas de hoje">
                        <span>Hoje</span>
                    </button>

                    <button type="button" wire:click="setQuickFilter('week')"
                        class="sale-filter-pill {{ $quickFilter === 'week' ? 'active' : '' }}"
                        title="Vendas da semana">
                        <span>Semana</span>
                    </button>

                    <button type="button" wire:click="setQuickFilter('month')"
                        class="sale-filter-pill {{ $quickFilter === 'month' ? 'active' : '' }}"
                        title="Vendas do mês">
                        <span>Mês</span>
                    </button>
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
                    <span>Dicas</span>
                </button>

                <button type="button" @click="showFilters = !showFilters"
                    class="sale-action-btn sale-action-filter"
                    :class="{ 'active': showFilters }" title="Filtros Avançados">
                    <i class="bi bi-sliders"></i>
                    <span>Filtros</span>
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
    @if($tabsSection)
        <div class="relative px-4 sm:px-6 pb-3 -mt-2">
            <x-section-tabs :section="$tabsSection" :active="$tabsActive" class="!mt-0" />
        </div>
    @endif
</div>
