@props([
    'products' => [],
    'selectedProducts' => [],
    'categories' => [],
    'searchTerm' => '',
    'selectedCategory' => '',
    'sortBy' => 'name_asc',
    'stockFilter' => 'all',
    'title' => 'Selecionar Produtos',
    'emptyMessage' => 'Nenhum produto disponível',
    'showQuantityInput' => true,
    'showPriceInput' => true,
    'wireModel' => 'selectedProducts',
    'compactCards' => false
])

<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

@php
    $totalCategories = is_countable($categories) ? count($categories) : 0;
@endphp

<div
    x-data="{
        showFilters: false,
        get activeFiltersCount() {
            let n = 0;
            if (this.$wire.selectedCategory) n++;
            if (this.$wire.sortBy && this.$wire.sortBy !== 'name_asc') n++;
            if (this.$wire.stockFilter && this.$wire.stockFilter !== 'all') n++;
            return n;
        },
        get selectedCategoryName() {
            const id = this.$wire.selectedCategory;
            if (!id) return null;
            const el = this.$refs.catData?.querySelector(`[data-cat-id='${id}']`);
            return el ? el.dataset.catName : null;
        }
    }"
    class="modern-product-selector w-full h-auto lg:h-[81vh] flex flex-col lg:flex-row gap-4 lg:gap-5 {{ $compactCards ? 'compact-cards' : '' }}"
>
    <template x-ref="catData">
        @foreach($categories as $category)
            <span data-cat-id="{{ $category->id_category ?? $category->id }}" data-cat-name="{{ $category->name }}"></span>
        @endforeach
    </template>

    {{-- =================== COLUNA ESQUERDA: LISTA =================== --}}
    <div class="mps-list-pane w-full lg:w-3/4 flex flex-col rounded-2xl bg-gradient-to-br from-white/70 via-purple-50/60 to-indigo-50/60 dark:from-slate-900/85 dark:via-slate-800/75 dark:to-slate-900/85 border border-white/40 dark:border-slate-700/60 backdrop-blur-xl shadow-2xl overflow-hidden">

        {{-- ===== TOOLBAR MODERNA ===== --}}
        <div class="mps-toolbar px-4 sm:px-5 py-4 border-b border-white/30 dark:border-slate-700/60 bg-gradient-to-r from-white/50 to-transparent dark:from-slate-800/50">
            <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                {{-- Busca --}}
                <div class="relative flex-1 group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                    </div>
                    <input type="text"
                        wire:model.live.debounce.300ms="searchTerm"
                        placeholder="Pesquisar por nome ou código..."
                        class="mps-search-input w-full pl-10 pr-10 py-2.5 rounded-xl bg-white/80 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 placeholder-slate-400 border border-slate-200/70 dark:border-slate-700/60 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 focus:outline-none shadow-sm transition-all" />
                    @if($searchTerm)
                        <button type="button" wire:click="$set('searchTerm', '')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors" title="Limpar busca">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    @endif
                </div>

                {{-- Botão de Filtro --}}
                <button type="button"
                    @click="showFilters = true"
                    class="mps-filter-btn relative inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all">
                    <i class="bi bi-sliders2 text-base"></i>
                    <span>Filtros</span>
                    <span x-show="activeFiltersCount > 0" x-cloak
                        class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-bold bg-white text-purple-700 shadow ring-2 ring-white/40"
                        x-text="activeFiltersCount"></span>
                </button>

                {{-- Contagem --}}
                <div class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    <i class="bi bi-box-seam text-purple-500"></i>
                    {{ count($products) }} {{ count($products) === 1 ? 'produto' : 'produtos' }}
                </div>
            </div>

            {{-- Chips de filtros ativos --}}
            <div x-show="activeFiltersCount > 0" x-cloak x-transition class="mt-3 flex flex-wrap gap-2 items-center">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Filtros ativos:</span>

                <template x-if="$wire.selectedCategory">
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-500/30">
                        <i class="bi bi-tag-fill text-[10px]"></i>
                        <span x-text="selectedCategoryName || 'Categoria'"></span>
                        <button type="button" @click="$wire.set('selectedCategory', '')" class="ml-0.5 w-4 h-4 inline-flex items-center justify-center rounded-full hover:bg-purple-500/30 transition">
                            <i class="bi bi-x text-[11px]"></i>
                        </button>
                    </span>
                </template>

                <template x-if="$wire.sortBy && $wire.sortBy !== 'name_asc'">
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">
                        <i class="bi bi-sort-down text-[10px]"></i>
                        <span x-text="{
                            'name_desc': 'Nome Z→A',
                            'price_asc': 'Menor preço',
                            'price_desc': 'Maior preço',
                            'stock_desc': 'Mais estoque',
                            'stock_asc': 'Menos estoque',
                        }[$wire.sortBy] || 'Ordenação'"></span>
                        <button type="button" @click="$wire.set('sortBy', 'name_asc')" class="ml-0.5 w-4 h-4 inline-flex items-center justify-center rounded-full hover:bg-indigo-500/30 transition">
                            <i class="bi bi-x text-[11px]"></i>
                        </button>
                    </span>
                </template>

                <template x-if="$wire.stockFilter && $wire.stockFilter !== 'all'">
                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                        <i class="bi bi-box-fill text-[10px]"></i>
                        <span x-text="$wire.stockFilter === 'low' ? 'Estoque baixo' : 'Estoque alto'"></span>
                        <button type="button" @click="$wire.set('stockFilter', 'all')" class="ml-0.5 w-4 h-4 inline-flex items-center justify-center rounded-full hover:bg-emerald-500/30 transition">
                            <i class="bi bi-x text-[11px]"></i>
                        </button>
                    </span>
                </template>

                <button type="button" wire:click="clearFilters"
                    class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold text-rose-600 dark:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition">
                    <i class="bi bi-trash3 text-[10px]"></i> Limpar tudo
                </button>
            </div>
        </div>

        {{-- ===== GRID DE PRODUTOS ===== --}}
        <div class="flex-1 p-3 md:p-5 overflow-y-auto mps-scroll">
            @if(count($products) === 0)
                <div class="flex flex-col items-center justify-center h-full text-center py-10">
                    <div class="w-24 h-24 mb-5 rounded-3xl bg-gradient-to-br from-purple-500/20 to-indigo-500/20 flex items-center justify-center">
                        <i class="bi bi-search text-4xl bg-gradient-to-br from-purple-500 to-indigo-500 bg-clip-text text-transparent"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1.5">{{ $emptyMessage }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs">
                        @if($searchTerm) Tente outros termos de pesquisa @else Ajuste os filtros ou cadastre produtos @endif
                    </p>
                </div>
            @else
                <div class="mps-grid grid gap-3 md:gap-4">
                    @foreach($products as $product)
                        <x-modern-product-card
                            :product="$product"
                            :selected="collect($selectedProducts)->pluck('id')->contains($product->id)"
                            clickAction="toggleProduct({{ $product->id }})"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- =================== COLUNA DIREITA: SELECIONADOS =================== --}}
    <div class="mps-side-pane w-full lg:w-1/4 flex flex-col max-h-[46vh] lg:max-h-none">
        <x-selected-products-panel
            :selectedProducts="$selectedProducts"
            :showQuantityInput="$showQuantityInput"
            :showPriceInput="$showPriceInput"
            :wireModel="$wireModel"
            :modernStyle="true"
            wire:key="selected-products-{{ count($selectedProducts) }}"
        />
    </div>

    {{-- =================== MODAL DE FILTROS =================== --}}
    <div x-show="showFilters" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6"
        x-transition.opacity>
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-md" @click="showFilters = false"></div>

        {{-- Modal --}}
        <div class="mps-modal relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-3xl shadow-2xl bg-gradient-to-br from-white via-purple-50/50 to-indigo-50/40 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-white/40 dark:border-slate-700/60"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Header do modal --}}
            <div class="relative px-6 py-5 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                        <i class="bi bi-sliders2 text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Filtros Avançados</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Refine sua seleção de produtos</p>
                    </div>
                    <button type="button" @click="showFilters = false"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-500/10 transition">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Conteúdo do modal --}}
            <div class="px-6 py-5 overflow-y-auto max-h-[calc(90vh-180px)] mps-scroll">

                {{-- Categoria --}}
                @if($totalCategories > 0)
                <div class="mb-6">
                    <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
                        <i class="bi bi-tags-fill text-purple-500"></i> Categoria
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('selectedCategory', '')"
                            class="mps-chip {{ $selectedCategory === '' ? 'mps-chip-active' : '' }}">
                            <i class="bi bi-grid-3x3-gap"></i> Todas
                        </button>
                        @foreach($categories as $category)
                            @php $catId = $category->id_category ?? $category->id; @endphp
                            <button type="button" wire:click="$set('selectedCategory', '{{ $catId }}')"
                                class="mps-chip {{ (string)$selectedCategory === (string)$catId ? 'mps-chip-active' : '' }}">
                                <i class="bi bi-tag-fill"></i> {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Ordenar por --}}
                <div class="mb-6">
                    <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
                        <i class="bi bi-sort-down text-indigo-500"></i> Ordenar por
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach([
                            'name_asc'   => ['Nome A→Z', 'bi-sort-alpha-down'],
                            'name_desc'  => ['Nome Z→A', 'bi-sort-alpha-up'],
                            'price_asc'  => ['Menor preço', 'bi-cash-coin'],
                            'price_desc' => ['Maior preço', 'bi-cash-stack'],
                            'stock_desc' => ['Mais estoque', 'bi-box-seam-fill'],
                            'stock_asc'  => ['Menos estoque', 'bi-box'],
                        ] as $value => [$label, $icon])
                            <button type="button" wire:click="$set('sortBy', '{{ $value }}')"
                                class="mps-chip mps-chip-block {{ ($sortBy ?? 'name_asc') === $value ? 'mps-chip-active' : '' }}">
                                <i class="bi {{ $icon }}"></i> {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Estoque --}}
                <div class="mb-2">
                    <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
                        <i class="bi bi-boxes text-emerald-500"></i> Estoque
                    </h4>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="$set('stockFilter', 'all')"
                            class="mps-chip mps-chip-block {{ ($stockFilter ?? 'all') === 'all' ? 'mps-chip-active' : '' }}">
                            <i class="bi bi-stack"></i> Todos
                        </button>
                        <button type="button" wire:click="$set('stockFilter', 'low')"
                            class="mps-chip mps-chip-block {{ ($stockFilter ?? 'all') === 'low' ? 'mps-chip-active' : '' }}">
                            <i class="bi bi-exclamation-triangle"></i> Baixo (≤5)
                        </button>
                        <button type="button" wire:click="$set('stockFilter', 'high')"
                            class="mps-chip mps-chip-block {{ ($stockFilter ?? 'all') === 'high' ? 'mps-chip-active' : '' }}">
                            <i class="bi bi-arrow-up-circle"></i> Alto (≥20)
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer do modal --}}
            <div class="px-6 py-4 border-t border-slate-200/60 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                <button type="button" wire:click="clearFilters"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 dark:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition">
                    <i class="bi bi-trash3"></i> Limpar filtros
                </button>
                <button type="button" @click="showFilters = false"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 shadow-lg shadow-purple-500/30 transition">
                    <i class="bi bi-check2-circle"></i> Aplicar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    /* ============ GRID PROPORCIONAL ============ */
    /* Mobile: 2 col | sm: 3 | md (iPad portrait baixo): 4 | iPad portrait completo: 5 | desktop: 6 */
    .mps-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    @media (min-width: 480px) {
        .mps-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (min-width: 768px) {
        .mps-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    /* iPad portrait — 5 colunas por linha conforme solicitado */
    @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
        .mps-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.65rem; }
    }
    @media (min-width: 1280px) {
        .mps-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    }

    /* ============ CHIPS ============ */
    .mps-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        border-radius: 0.85rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgb(71 85 105);
        background: rgba(255,255,255,0.7);
        border: 1px solid rgba(148,163,184,0.35);
        transition: all .18s ease;
        white-space: nowrap;
    }
    .mps-chip:hover {
        background: rgba(255,255,255,0.95);
        border-color: rgba(139,92,246,0.5);
        color: rgb(91 33 182);
        transform: translateY(-1px);
    }
    .mps-chip-block {
        justify-content: center;
        width: 100%;
    }
    .mps-chip-active {
        background: linear-gradient(135deg, rgb(99 102 241), rgb(139 92 246), rgb(236 72 153)) !important;
        color: white !important;
        border-color: transparent !important;
        box-shadow: 0 6px 18px -6px rgba(139,92,246,0.6);
    }
    .dark .mps-chip {
        background: rgba(30,41,59,0.7);
        color: rgb(203 213 225);
        border-color: rgba(71,85,105,0.6);
    }
    .dark .mps-chip:hover {
        background: rgba(51,65,85,0.85);
        color: rgb(216 180 254);
    }

    /* ============ SCROLLBAR ============ */
    .mps-scroll::-webkit-scrollbar { width: 6px; }
    .mps-scroll::-webkit-scrollbar-track { background: transparent; }
    .mps-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(139,92,246,0.4), rgba(99,102,241,0.4));
        border-radius: 8px;
    }
    .mps-scroll::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, rgba(139,92,246,0.65), rgba(99,102,241,0.65));
    }

    /* ============ CARDS — SELECTION FEEDBACK ============ */
    .product-card-modern.selected {
        border-color: #9575cd !important;
        transform: scale(1.02);
        box-shadow: 0 8px 32px rgba(149, 117, 205, 0.3) !important;
    }
    .product-card-modern { cursor: pointer; user-select: none; }
    .product-card-modern:hover { transform: translateY(-2px) scale(1.01); }
    .product-card-modern.selected:hover { transform: translateY(-2px) scale(1.02); }

    /* ============ COMPACT (kits) — TAMANHOS PROPORCIONAIS POR DEVICE ============ */
    .compact-cards .product-card-modern {
        min-height: 282px !important;
        height: auto !important;
    }
    .compact-cards .product-card-modern .product-img-area {
        min-height: 178px !important;
        height: 178px !important;
    }
    .compact-cards .product-card-modern .card-body {
        min-height: 0 !important;
        padding: 2em 0.55em 0 0.55em !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        gap: 0.15em !important;
        overflow: hidden !important;
    }
    .compact-cards .product-card-modern .product-title {
        font-size: 0.9em !important;
        line-height: 1.2 !important;
        margin: 0.05em 0 0.1em 0 !important;
        min-height: 2.2em !important;
    }
    .compact-cards .product-card-modern .price-area {
        min-height: 1.65em !important;
        margin-top: auto !important;
        position: relative !important;
    }
    .compact-cards .product-card-modern .badge-price,
    .compact-cards .product-card-modern .badge-price-sale { bottom: 0 !important; }

    /* iPad portrait — cards menores p/ caber 5 por linha proporcionais */
    @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
        .compact-cards .product-card-modern { min-height: 230px !important; }
        .compact-cards .product-card-modern .product-img-area {
            min-height: 128px !important;
            height: 128px !important;
        }
        .compact-cards .product-card-modern .card-body {
            padding: 1.55em 0.45em 0 0.45em !important;
        }
        .compact-cards .product-card-modern .product-title {
            font-size: 0.78em !important;
            line-height: 1.2 !important;
            min-height: 2.1em !important;
        }
        .compact-cards .product-card-modern .badge-price,
        .compact-cards .product-card-modern .badge-price-sale,
        .compact-cards .product-card-modern .badge-product-code,
        .compact-cards .product-card-modern .badge-quantity {
            font-size: 0.62em !important;
            padding: 0.16em 0.42em !important;
        }
        .mps-side-pane { max-height: 38vh !important; }
    }

    /* Mobile compacto */
    @media (max-width: 450px) {
        .compact-cards .product-card-modern { min-height: 238px !important; }
        .compact-cards .product-card-modern .product-img-area {
            min-height: 140px !important;
            height: 140px !important;
        }
        .compact-cards .product-card-modern .card-body {
            padding: 1.55em 0.45em 0 0.45em !important;
        }
        .compact-cards .product-card-modern .product-title {
            font-size: 0.78em !important;
            line-height: 1.15 !important;
            min-height: 2.05em !important;
        }
        .compact-cards .product-card-modern .badge-price,
        .compact-cards .product-card-modern .badge-price-sale,
        .compact-cards .product-card-modern .badge-product-code,
        .compact-cards .product-card-modern .badge-quantity {
            font-size: 0.66em !important;
            padding: 0.14em 0.38em !important;
        }
    }
</style>
