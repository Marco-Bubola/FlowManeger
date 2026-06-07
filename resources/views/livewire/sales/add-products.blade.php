<div class="add-products-page w-full mobile-393-base"
     x-data="addProductsPage()">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ultrawide.css') }}">

    @php
        $selectedCount = count(array_filter($newProducts, fn($p) => !empty($p['product_id'])));
        $selectedQty = array_sum(array_map(fn($p) => !empty($p['product_id']) ? (int)$p['quantity'] : 0, $newProducts));
        $activeFilters = $this->getActiveFiltersCount();
    @endphp

    {{-- ============ HEADER MODERNO (padrão das outras páginas) ============ --}}
    <div class="ap-header sticky top-0 z-40 mb-4 overflow-hidden rounded-2xl border border-white/30 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-emerald-50/80 to-teal-50/70 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/40 backdrop-blur-xl shadow-xl">
        <div class="relative px-4 sm:px-6 py-3.5">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.show', $sale->id) }}"
                   class="shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white/70 dark:bg-slate-800/70 text-slate-500 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 border border-slate-200/60 dark:border-slate-700/60 transition-all">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="shrink-0 w-11 h-11 flex items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-green-600 text-white shadow-lg shadow-emerald-500/30">
                    <i class="bi bi-cart-plus text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white leading-tight truncate">Adicionar Produtos</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight truncate">
                        Venda #{{ $sale->id }} · {{ $sale->client->name ?? 'Cliente' }}
                    </p>
                </div>
                @if($selectedCount > 0)
                <span class="shrink-0 hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-300/50 dark:border-emerald-700/50 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                    <i class="bi bi-check-circle-fill"></i>{{ $selectedCount }} {{ $selectedCount === 1 ? 'item' : 'itens' }}
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ LAYOUT ============ --}}
    <div class="w-full flex flex-col lg:flex-row gap-4 lg:gap-5 ap-shell">

        {{-- ===== COLUNA ESQUERDA: LISTA ===== --}}
        <div class="w-full lg:w-3/4 flex flex-col rounded-2xl bg-gradient-to-br from-white/70 via-emerald-50/40 to-teal-50/40 dark:from-slate-900/85 dark:via-slate-800/75 dark:to-slate-900/85 border border-white/40 dark:border-slate-700/60 backdrop-blur-xl shadow-2xl overflow-hidden ap-list-pane">

            {{-- TOOLBAR: busca + botão filtros ----------------------------------- --}}
            <div class="px-4 sm:px-5 py-4 border-b border-white/30 dark:border-slate-700/60 bg-gradient-to-r from-white/50 to-transparent dark:from-slate-800/50">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                    {{-- Busca --}}
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="bi bi-search text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                        </div>
                        <input type="text"
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Buscar por nome ou código..."
                            class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-white/80 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 placeholder-slate-400 border border-slate-200/70 dark:border-slate-700/60 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none shadow-sm transition-all" />
                        @if($searchTerm)
                            <button type="button" wire:click="$set('searchTerm', '')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Botão Filtros --}}
                    <button type="button" @click.prevent="openFilters()"
                        class="relative inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600 hover:from-emerald-600 hover:via-teal-600 hover:to-green-700 shadow-lg shadow-emerald-500/25 transition-all">
                        <i class="bi bi-sliders2 text-base"></i>
                        <span>Filtros</span>
                        @if($activeFilters > 0)
                            <span class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-bold bg-white text-emerald-700 shadow ring-2 ring-white/40">{{ $activeFilters }}</span>
                        @endif
                    </button>

                    {{-- Contador --}}
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        <i class="bi bi-box-seam text-emerald-500"></i>
                        {{ $filteredProducts->count() }} {{ $filteredProducts->count() === 1 ? 'produto' : 'produtos' }}
                    </div>
                </div>

                {{-- Chips de filtros ativos --}}
                @if($activeFilters > 0)
                <div class="mt-3 flex flex-wrap gap-2 items-center">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Filtros:</span>
                    @if($selectedCategory)
                        <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                            <i class="bi bi-tag-fill text-[10px]"></i>
                            {{ optional(collect($categories)->firstWhere('id', (int)$selectedCategory))->name ?? 'Categoria' }}
                            <button type="button" wire:click="$set('selectedCategory', '')" class="ml-0.5 w-4 h-4 inline-flex items-center justify-center rounded-full hover:bg-emerald-500/30"><i class="bi bi-x text-[11px]"></i></button>
                        </span>
                    @endif
                    @if($stockFilter !== 'all')
                        <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-teal-500/15 text-teal-700 dark:text-teal-300 border border-teal-500/30">
                            <i class="bi bi-box-fill text-[10px]"></i>
                            {{ ['low'=>'Estoque baixo','in_stock'=>'Em estoque','kits'=>'Somente kits'][$stockFilter] ?? '' }}
                            <button type="button" wire:click="$set('stockFilter', 'all')" class="ml-0.5 w-4 h-4 inline-flex items-center justify-center rounded-full hover:bg-teal-500/30"><i class="bi bi-x text-[11px]"></i></button>
                        </span>
                    @endif
                    @if($sortBy !== 'name' || $sortDirection !== 'asc')
                        <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">
                            <i class="bi bi-sort-down text-[10px]"></i>
                            {{ ['name'=>'Nome','price_sale'=>'Preço','stock_quantity'=>'Estoque'][$sortBy] ?? '' }} {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        </span>
                    @endif
                    <button type="button" wire:click="clearFilters" class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold text-rose-600 dark:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30">
                        <i class="bi bi-trash3 text-[10px]"></i> Limpar
                    </button>
                </div>
                @endif
            </div>

            {{-- GRID DE PRODUTOS ----------------------------------------------- --}}
            <div class="flex-1 p-3 md:p-5 overflow-y-auto ap-scroll">
                @if($filteredProducts->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="w-24 h-24 mb-5 rounded-3xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center">
                            <i class="bi bi-search text-4xl bg-gradient-to-br from-emerald-500 to-teal-500 bg-clip-text text-transparent"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1.5">
                            @if($searchTerm) Nada encontrado @else Nenhum produto disponível @endif
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs">
                            @if($searchTerm) Tente outro termo de busca @else Ajuste os filtros ou cadastre produtos @endif
                        </p>
                    </div>
                @else
                    <div class="ap-grid grid gap-3 md:gap-4">
                        @foreach($filteredProducts as $product)
                            @php
                                $isSelected = $this->isProductSelected($product->id);
                                $qtySel = $isSelected ? $this->getSelectedQuantity($product->id) : 0;
                                $isKit = ($product->tipo ?? 'simples') === 'kit';
                            @endphp
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})"
                                 wire:key="product-{{ $product->id }}">

                                @if($isSelected)
                                <div class="selected-qty-badge">{{ $qtySel }}x</div>
                                @endif

                                <div class="btn-action-group flex gap-2">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200
                                                {{ $isSelected ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400' }}">
                                        @if($isSelected)<i class="bi bi-check text-sm"></i>@endif
                                    </div>
                                </div>

                                <div class="product-img-area">
                                    <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $product->name }}" class="product-img">

                                    @if($isKit)
                                        <div class="absolute top-3 left-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white px-2 py-1 rounded-full text-[10px] font-bold shadow">
                                            <i class="bi bi-boxes mr-0.5"></i> KIT
                                        </div>
                                    @elseif($product->stock_quantity <= 5)
                                        <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-[10px] font-medium shadow">
                                            <i class="bi bi-exclamation-triangle mr-0.5"></i> Baixo
                                        </div>
                                    @endif

                                    <span class="badge-product-code"><i class="bi bi-upc-scan"></i> {{ $product->product_code }}</span>
                                    @unless($isKit)
                                        <span class="badge-quantity"><i class="bi bi-stack"></i> {{ $product->stock_quantity }}</span>
                                    @endunless
                                    @if($product->category)
                                    <div class="category-icon-wrapper">
                                        <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                    </div>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <div class="product-title" title="{{ $product->name }}">{{ ucwords($product->name) }}</div>
                                    <div class="price-area">
                                        <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> {{ number_format($product->price, 2, ',', '.') }}</span>
                                        <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== COLUNA DIREITA: CARRINHO (lg+) ===== --}}
        <div class="hidden lg:flex w-full lg:w-1/4 flex-col rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xl overflow-hidden ap-cart-pane">
            <x-sales-add-products-panel
                :new-products="$newProducts"
                :filtered-products="$filteredProducts"
                :has-selected-products="$this->hasSelectedProducts()"
                :total-price="$this->getTotalPrice()"
                :sale="$sale"
                :confirm-via-modal="true" />
        </div>
    </div>

    {{-- ============ FAB CARRINHO (mobile/tablet) ============ --}}
    <template x-teleport="body">
        <button type="button" @click.prevent="openCart()"
            class="ap-fab lg:hidden fixed bottom-24 right-4 z-[90] w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-green-600 text-white shadow-2xl shadow-emerald-500/40 flex items-center justify-center active:scale-95 transition-transform">
            <i class="bi bi-cart3 text-2xl"></i>
            @if($selectedCount > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[1.5rem] h-6 px-1.5 rounded-full bg-rose-500 text-white text-xs font-black flex items-center justify-center ring-4 ring-white dark:ring-slate-900 shadow-lg animate-pulse">{{ $selectedCount }}</span>
            @endif
        </button>
    </template>

    {{-- ============ MODAL CARRINHO (mobile/tablet) ============ --}}
    <template x-teleport="body">
        <div x-show="showCart" x-cloak class="fixed inset-0 z-[110] flex items-end sm:items-center justify-center p-0 sm:p-4 lg:hidden" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-md" @click="closeCart()"></div>
            <div class="relative w-full sm:max-w-md max-h-[88vh] overflow-hidden rounded-t-3xl sm:rounded-3xl shadow-2xl bg-white dark:bg-zinc-800 border border-white/40 dark:border-slate-700/60 flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                <x-sales-add-products-panel
                    :new-products="$newProducts"
                    :filtered-products="$filteredProducts"
                    :has-selected-products="$this->hasSelectedProducts()"
                    :total-price="$this->getTotalPrice()"
                    :sale="$sale"
                    :confirm-via-modal="true" />
            </div>
        </div>
    </template>

    {{-- ============ MODAL DE FILTROS ============ --}}
    <template x-teleport="body">
        <div x-show="showFilters" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-md" @click="closeFilters()"></div>
            <div class="relative w-full max-w-xl max-h-[90vh] overflow-hidden rounded-3xl shadow-2xl bg-gradient-to-br from-white via-emerald-50/40 to-teal-50/30 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-white/40 dark:border-slate-700/60 flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="px-6 py-5 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-green-500/10 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <i class="bi bi-sliders2 text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Filtros</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Refine a lista de produtos</p>
                    </div>
                    <button type="button" @click="closeFilters()" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="px-6 py-5 overflow-y-auto flex-1 ap-scroll">
                    {{-- Categoria --}}
                    <div class="mb-6">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-tags-fill text-emerald-500"></i> Categoria</h4>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('selectedCategory', '')" class="ap-chip {{ $selectedCategory === '' ? 'ap-chip-active' : '' }}"><i class="bi bi-grid-3x3-gap"></i> Todas</button>
                            @foreach($categories as $category)
                                <button type="button" wire:click="$set('selectedCategory', '{{ $category->id }}')" class="ap-chip {{ (string)$selectedCategory === (string)$category->id ? 'ap-chip-active' : '' }}"><i class="bi bi-tag-fill"></i> {{ $category->name }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Estoque --}}
                    <div class="mb-6">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-boxes text-teal-500"></i> Estoque</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" wire:click="$set('stockFilter', 'all')" class="ap-chip ap-chip-block {{ $stockFilter === 'all' ? 'ap-chip-active' : '' }}"><i class="bi bi-stack"></i> Todos</button>
                            <button type="button" wire:click="$set('stockFilter', 'in_stock')" class="ap-chip ap-chip-block {{ $stockFilter === 'in_stock' ? 'ap-chip-active' : '' }}"><i class="bi bi-check-circle"></i> Em estoque</button>
                            <button type="button" wire:click="$set('stockFilter', 'low')" class="ap-chip ap-chip-block {{ $stockFilter === 'low' ? 'ap-chip-active' : '' }}"><i class="bi bi-exclamation-triangle"></i> Baixo</button>
                            <button type="button" wire:click="$set('stockFilter', 'kits')" class="ap-chip ap-chip-block {{ $stockFilter === 'kits' ? 'ap-chip-active' : '' }}"><i class="bi bi-boxes"></i> Kits</button>
                        </div>
                    </div>

                    {{-- Ordenar --}}
                    <div class="mb-2">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-sort-down text-indigo-500"></i> Ordenar por</h4>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="setSortBy('name')" class="ap-chip ap-chip-block {{ $sortBy === 'name' ? 'ap-chip-active' : '' }}">
                                Nome @if($sortBy === 'name')<i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                            </button>
                            <button type="button" wire:click="setSortBy('price_sale')" class="ap-chip ap-chip-block {{ $sortBy === 'price_sale' ? 'ap-chip-active' : '' }}">
                                Preço @if($sortBy === 'price_sale')<i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                            </button>
                            <button type="button" wire:click="setSortBy('stock_quantity')" class="ap-chip ap-chip-block {{ $sortBy === 'stock_quantity' ? 'ap-chip-active' : '' }}">
                                Estoque @if($sortBy === 'stock_quantity')<i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200/60 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                    <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 dark:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30"><i class="bi bi-trash3"></i> Limpar</button>
                    <button type="button" @click="closeFilters()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600 shadow-lg shadow-emerald-500/30"><i class="bi bi-check2-circle"></i> Aplicar</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ============ MODAL DE CONFIRMAÇÃO ============ --}}
    <template x-teleport="body">
        <div x-show="showConfirm" x-cloak class="fixed inset-0 z-[130] flex items-center justify-center p-3 sm:p-6" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-md" @click="closeConfirm()"></div>
            <div class="relative w-full max-w-lg max-h-[90vh] overflow-hidden rounded-3xl shadow-2xl bg-white dark:bg-slate-900 border border-white/40 dark:border-slate-700/60 flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="px-6 py-5 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-green-500/10 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <i class="bi bi-cart-check-fill text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Confirmar adição</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $selectedCount }} {{ $selectedCount === 1 ? 'item' : 'itens' }} · {{ $selectedQty }} {{ $selectedQty === 1 ? 'unidade' : 'unidades' }}</p>
                    </div>
                    <button type="button" @click="closeConfirm()" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="px-5 py-4 overflow-y-auto flex-1 ap-scroll">
                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Os seguintes produtos serão adicionados à venda <strong>#{{ $sale->id }}</strong>:</p>
                    <div class="space-y-2">
                        @foreach($this->selectedItems as $item)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200/70 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/50 p-2.5">
                            <img src="{{ $item['image'] ? asset('storage/products/' . $item['image']) : asset('storage/products/product-placeholder.png') }}"
                                 alt="{{ $item['name'] }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $item['name'] }}</p>
                                    @if($item['is_kit'])<span class="shrink-0 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-purple-500/20 text-purple-700 dark:text-purple-300">KIT</span>@endif
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $item['quantity'] }}x · R$ {{ number_format($item['price_sale'], 2, ',', '.') }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200/60 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/40">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Total a adicionar:</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="closeConfirm()" class="flex-1 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancelar</button>
                        <button type="button" wire:click="addProducts" wire:loading.attr="disabled" wire:target="addProducts"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 shadow-lg shadow-emerald-500/30 transition disabled:opacity-60">
                            <span wire:loading.remove wire:target="addProducts"><i class="bi bi-check2-circle"></i> Confirmar</span>
                            <span wire:loading wire:target="addProducts"><i class="bi bi-hourglass-split animate-spin"></i> Adicionando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <x-toast-notifications />

    <script>
        if (typeof window.addProductsPage === 'undefined') {
            window.addProductsPage = function () {
                return {
                    showFilters: false,
                    showCart: false,
                    showConfirm: false,
                    openFilters() { this.showFilters = true; this._lock(); },
                    closeFilters() { this.showFilters = false; this._unlock(); },
                    openCart() { this.showCart = true; this._lock(); },
                    closeCart() { this.showCart = false; this._unlock(); },
                    openConfirm() { this.showCart = false; this.showConfirm = true; this._lock(); },
                    closeConfirm() { this.showConfirm = false; this._unlock(); },
                    _lock() { document.body.style.overflow = 'hidden'; },
                    _unlock() { document.body.style.overflow = ''; },
                    init() {
                        // Botão "Confirmar Adição" dentro do painel abre o modal de confirmação
                        window.addEventListener('open-confirm-add', () => this.openConfirm());
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') { this.closeFilters(); this.closeCart(); this.closeConfirm(); }
                        });
                    }
                };
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        /* Grid base (colunas finas ajustadas por device nos CSS responsivos) */
        .ap-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }

        /* ===== COMPACTAÇÃO BASE DOS CARDS (remove o vazio de min-height:420px) ===== */
        .add-products-page .product-card-modern {
            min-height: 0 !important;
            height: auto !important;
        }
        .add-products-page .product-card-modern .product-img-area {
            min-height: 150px !important;
            height: 150px !important;
        }
        .add-products-page .product-card-modern .card-body {
            min-height: 0 !important;
            padding: 1.65em 0.6em 1.5em 0.6em !important;
            gap: 0.2em !important;
        }
        .add-products-page .product-card-modern .product-title {
            font-size: 0.82em !important;
            line-height: 1.18 !important;
            min-height: 2.1em !important;
            margin: 0 !important;
        }
        .add-products-page .product-card-modern .price-area {
            min-height: 1.5em !important;
        }
        .add-products-page .product-card-modern .badge-price,
        .add-products-page .product-card-modern .badge-price-sale {
            bottom: 0.2em !important;
            font-size: 0.84em !important;
        }

        .product-card-modern.selected {
            border-color: #10b981 !important;
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16,185,129,.3) !important;
        }
        .product-card-modern { cursor: pointer; user-select: none; position: relative; }
        .product-card-modern:hover { transform: translateY(-2px) scale(1.01); }
        .product-card-modern.selected:hover { transform: translateY(-2px) scale(1.02); }

        .selected-qty-badge {
            position: absolute; top: -8px; right: -8px; min-width: 22px; height: 22px;
            background: linear-gradient(135deg,#059669,#10b981); color: #fff; font-size: .7rem;
            font-weight: 700; border-radius: 999px; display: flex; align-items: center;
            justify-content: center; padding: 0 5px; box-shadow: 0 2px 6px rgba(16,185,129,.5);
            z-index: 10; border: 2px solid #fff;
        }

        /* Chips do modal */
        .ap-chip {
            display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .9rem;
            border-radius: .85rem; font-size: .78rem; font-weight: 600; color: rgb(71 85 105);
            background: rgba(255,255,255,.75); border: 1px solid rgba(148,163,184,.4);
            transition: all .2s ease; white-space: nowrap;
        }
        .ap-chip:hover { background: #fff; border-color: rgba(16,185,129,.55); color: rgb(6 95 70); transform: translateY(-1px); }
        .ap-chip-block { justify-content: center; width: 100%; }
        .ap-chip-active {
            background: linear-gradient(135deg, rgb(16 185 129), rgb(20 184 166), rgb(5 150 105)) !important;
            color: #fff !important; border-color: transparent !important;
            box-shadow: 0 6px 18px -6px rgba(16,185,129,.6);
        }
        .dark .ap-chip { background: rgba(30,41,59,.7); color: rgb(203 213 225); border-color: rgba(71,85,105,.6); }
        .dark .ap-chip:hover { background: rgba(51,65,85,.9); color: rgb(110 231 183); }

        .ap-scroll::-webkit-scrollbar { width: 6px; }
        .ap-scroll::-webkit-scrollbar-thumb { background: linear-gradient(180deg, rgba(16,185,129,.4), rgba(20,184,166,.4)); border-radius: 8px; }
        .ap-scroll::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, rgba(16,185,129,.7), rgba(20,184,166,.7)); }

        .ap-fab::before {
            content: ''; position: absolute; inset: -4px; border-radius: 1.25rem; opacity: 0;
            background: radial-gradient(circle, rgba(16,185,129,.5), transparent 70%);
            animation: apFabPulse 2.2s ease-out infinite; pointer-events: none;
        }
        @keyframes apFabPulse { 0%{transform:scale(.9);opacity:.7;} 100%{transform:scale(1.25);opacity:0;} }

        @media (min-width: 1024px) { .ap-list-pane, .ap-cart-pane { height: calc(100vh - 150px); } }
    </style>
</div>
