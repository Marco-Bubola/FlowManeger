<div class="add-products-page promotions-create-page w-full mobile-393-base" x-data="promoCreatePage()">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ultrawide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-compact.css') }}?v=20260806">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sales-compact.css') }}?v=20260806">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-card-standard.css') }}?v=20260806">
    @include('livewire.promotions.partials.styles')

    @php
        $selectedCount = count($rows);
        $activeFilters = ($category !== '' ? 1 : 0) + ($show !== 'todos' ? 1 : 0) + ($sort !== 'name' ? 1 : 0);
        $marginLabel = rtrim(rtrim(number_format((float) $settings->min_margin_percent, 2, ',', ''), '0'), ',');
    @endphp

    {{-- ============ HEADER ============ --}}
    <div class="ap-header sticky top-0 z-40 mb-4 overflow-hidden rounded-2xl border border-white/30 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-rose-50/80 to-orange-50/70 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/40 backdrop-blur-xl shadow-xl">
        <div class="relative px-4 sm:px-6 py-3.5">
            <div class="flex items-center gap-3">
                <a href="{{ route('promotions.index') }}"
                   class="shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white/70 dark:bg-slate-800/70 text-slate-500 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 border border-slate-200/60 dark:border-slate-700/60 transition-all" title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="shrink-0 w-11 h-11 flex items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 text-white shadow-lg shadow-rose-500/30">
                    <i class="bi bi-percent text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white leading-tight truncate">Nova promoção</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight truncate">
                        Toque nos produtos e ajuste o desconto. O preço nunca fica abaixo do custo + {{ $marginLabel }}%.
                    </p>
                </div>
                @if($selectedCount > 0)
                    <span class="shrink-0 hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/15 border border-rose-300/50 dark:border-rose-700/50 text-xs font-bold text-rose-700 dark:text-rose-300">
                        <i class="bi bi-check-circle-fill"></i>{{ $selectedCount }} {{ $selectedCount === 1 ? 'produto' : 'produtos' }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ LAYOUT ============ --}}
    <div class="w-full flex flex-col lg:flex-row gap-4 lg:gap-5 ap-shell">

        {{-- ===== LISTA DE PRODUTOS ===== --}}
        <div class="w-full lg:w-3/4 flex flex-col rounded-2xl bg-gradient-to-br from-white/70 via-rose-50/30 to-orange-50/30 dark:from-slate-900/85 dark:via-slate-800/75 dark:to-slate-900/85 border border-white/40 dark:border-slate-700/60 backdrop-blur-xl shadow-2xl overflow-hidden ap-list-pane">

            <div class="px-4 sm:px-5 py-4 border-b border-white/30 dark:border-slate-700/60 bg-gradient-to-r from-white/50 to-transparent dark:from-slate-800/50">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="bi bi-search text-slate-400 group-focus-within:text-rose-500 transition-colors"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou código..."
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-white/80 dark:bg-slate-800/70 text-slate-800 dark:text-slate-100 placeholder-slate-400 border border-slate-200/70 dark:border-slate-700/60 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none shadow-sm transition-all" />
                        @if($search)
                            <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Atalhos de filtro --}}
                    <div class="flex gap-1 p-1 rounded-xl bg-white/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 overflow-x-auto promo-quick">
                        @foreach(['todos' => ['Todos', 'bi-grid'], 'sem_promo' => ['Sem promoção', 'bi-tag'], 'com_tabela' => ['Com tabela', 'bi-receipt'], 'em_estoque' => ['Com estoque', 'bi-box-seam']] as $key => [$label, $icon])
                            <button type="button" wire:click="$set('show', '{{ $key }}')"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $show === $key ? 'bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
                                <i class="bi {{ $icon }}"></i> {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <button type="button" @click.prevent="openFilters()"
                            class="relative inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 hover:from-rose-600 hover:via-pink-600 hover:to-orange-600 shadow-lg shadow-rose-500/25 transition-all">
                        <i class="bi bi-sliders2 text-base"></i>
                        <span>Filtros</span>
                        @if($activeFilters > 0)
                            <span class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-bold bg-white text-rose-700 shadow">{{ $activeFilters }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="flex-1 p-3 md:p-5 overflow-y-auto ap-scroll">
                @if($products->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="w-24 h-24 mb-5 rounded-3xl bg-gradient-to-br from-rose-500/20 to-orange-500/20 flex items-center justify-center">
                            <i class="bi bi-search text-4xl text-rose-500"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1.5">Nada encontrado</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs">Tente outro termo ou limpe os filtros.</p>
                        @if($activeFilters || $search)
                            <button type="button" wire:click="clearFilters" class="mt-4 text-sm font-semibold text-rose-600 hover:underline">Limpar filtros</button>
                        @endif
                    </div>
                @else
                    <div class="ap-grid grid gap-3 md:gap-4">
                        @foreach($products as $product)
                            @php
                                $isSelected = isset($items[$product->id]);
                                $isKit = ($product->tipo ?? 'simples') === 'kit';
                                $promo = $product->activePromotion;
                                $stock = $product->is_variation_parent ? $product->family_stock : (int) $product->stock_quantity;
                            @endphp
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})" wire:key="pc-{{ $product->id }}">

                                <div class="btn-action-group flex gap-2">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 {{ $isSelected ? 'bg-rose-600 border-rose-600 text-white' : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent' }}">
                                        @if($isSelected)<i class="bi bi-check text-sm"></i>@endif
                                    </div>
                                </div>

                                <div class="product-img-area">
                                    <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $product->name }}" class="product-img">

                                    <div class="promo-card-tags absolute bottom-2 left-2 z-10 flex flex-wrap items-end gap-1">
                                        @if($isKit)
                                            <span class="promo-tag bg-gradient-to-r from-blue-500 to-blue-600"><i class="bi bi-boxes"></i> KIT</span>
                                        @elseif($product->is_variation_parent)
                                            <span class="promo-tag bg-gradient-to-r from-violet-500 to-purple-600"><i class="bi bi-diagram-3"></i> {{ $product->variants_count + 1 }} variações</span>
                                        @endif
                                        @if($promo)
                                            <span class="promo-tag bg-gradient-to-r from-rose-500 to-orange-500" title="Já está em promoção. Criar outra substitui a atual."><i class="bi bi-fire"></i> -{{ $promo->discount_percent }}%</span>
                                        @elseif((float) $product->price_original > (float) $product->price_sale && (float) $product->price_sale > 0)
                                            <span class="promo-tag bg-gradient-to-r from-emerald-500 to-teal-600" title="Tabela {{ number_format($product->price_original, 2, ',', '.') }}"><i class="bi bi-receipt"></i> tabela</span>
                                        @endif
                                    </div>

                                    <span class="badge-product-code"><i class="bi bi-upc-scan"></i> {{ $product->product_code }}</span>
                                    @unless($isKit)
                                        <span class="badge-quantity"><i class="bi bi-stack"></i> {{ $stock }}</span>
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
                                        <span class="badge-price" title="Custo (a pagar)"><i class="bi bi-tag"></i> {{ number_format($product->price, 2, ',', '.') }}</span>
                                        <span class="badge-price-sale" title="Revenda"><i class="bi bi-currency-dollar"></i> {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <div class="mt-5 flex items-center justify-center gap-2">
                            <button type="button" wire:click="previousPage" @disabled($products->onFirstPage()) class="promo-page-btn"><i class="bi bi-chevron-left"></i></button>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                            <button type="button" wire:click="nextPage" @disabled(!$products->hasMorePages()) class="promo-page-btn"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ===== PAINEL (lg+) ===== --}}
        <div class="hidden lg:flex w-full lg:w-1/4 flex-col rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xl overflow-hidden ap-cart-pane promo-panel">
            @include('livewire.promotions.partials.create-panel', ['mobile' => false])
        </div>
    </div>

    {{-- ============ BOTÃO FLUTUANTE (celular/tablet) ============ --}}
    <template x-teleport="body">
        <button type="button" @click.prevent="openCart()"
                class="ap-fab lg:hidden fixed bottom-24 right-4 z-[90] w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 text-white shadow-2xl shadow-rose-500/40 flex items-center justify-center active:scale-95 transition-transform">
            <i class="bi bi-fire text-2xl"></i>
            @if($selectedCount > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[1.5rem] h-6 px-1.5 rounded-full bg-slate-900 text-white text-xs font-black flex items-center justify-center ring-4 ring-white dark:ring-slate-900 shadow-lg">{{ $selectedCount }}</span>
            @endif
        </button>
    </template>

    {{-- ============ PAINEL (celular/tablet) ============ --}}
    <template x-teleport="body">
        <div x-show="showCart" x-cloak class="fixed inset-0 z-[110] flex items-end sm:items-center justify-center p-0 sm:p-4 lg:hidden" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-md" @click="closeCart()"></div>
            <div class="relative w-full sm:max-w-lg h-[88vh] overflow-hidden rounded-t-3xl sm:rounded-3xl shadow-2xl bg-white dark:bg-zinc-800 border border-white/40 dark:border-slate-700/60 flex flex-col promo-panel">
                @include('livewire.promotions.partials.create-panel', ['mobile' => true])
            </div>
        </div>
    </template>

    {{-- ============ FILTROS ============ --}}
    <template x-teleport="body">
        <div x-show="showFilters" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-md" @click="closeFilters()"></div>
            <div class="relative w-full max-w-xl max-h-[90vh] overflow-hidden rounded-3xl shadow-2xl bg-gradient-to-br from-white via-rose-50/40 to-orange-50/30 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-white/40 dark:border-slate-700/60 flex flex-col">
                <div class="px-6 py-5 border-b border-slate-200/60 dark:border-slate-700/60 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 flex items-center justify-center shadow-lg"><i class="bi bi-sliders2 text-white text-lg"></i></div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Filtros</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Refine a lista de produtos</p>
                    </div>
                    <button type="button" @click="closeFilters()" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="px-6 py-5 overflow-y-auto flex-1 ap-scroll space-y-6">
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-tags-fill text-rose-500"></i> Categoria</h4>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('category', '')" class="promo-chip {{ $category === '' ? 'promo-chip-active' : '' }}">Todas</button>
                            @foreach($this->categories as $cat)
                                <button type="button" wire:click="$set('category', '{{ $cat->id_category }}')" class="promo-chip {{ (string) $category === (string) $cat->id_category ? 'promo-chip-active' : '' }}">{{ $cat->name }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-sort-down text-indigo-500"></i> Ordenar por</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach(['name' => 'Nome', 'desconto' => 'Maior desconto', 'estoque' => 'Estoque', 'recentes' => 'Recentes'] as $key => $label)
                                <button type="button" wire:click="$set('sort', '{{ $key }}')" class="promo-chip justify-center {{ $sort === $key ? 'promo-chip-active' : '' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-3">
                    <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 bg-rose-500/10 hover:bg-rose-500/20"><i class="bi bi-trash3"></i> Limpar</button>
                    <button type="button" @click="closeFilters()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 shadow-lg"><i class="bi bi-check2-circle"></i> Aplicar</button>
                </div>
            </div>
        </div>
    </template>

    <x-toast-notifications />

    <script>
        if (typeof window.promoCreatePage === 'undefined') {
            window.promoCreatePage = function () {
                return {
                    showFilters: false,
                    showCart: false,
                    openFilters() { this.showFilters = true; document.body.style.overflow = 'hidden'; },
                    closeFilters() { this.showFilters = false; document.body.style.overflow = ''; },
                    openCart() { this.showCart = true; document.body.style.overflow = 'hidden'; },
                    closeCart() { this.showCart = false; document.body.style.overflow = ''; },
                    init() {
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') { this.closeFilters(); this.closeCart(); }
                        });
                    }
                };
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .ap-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .add-products-page .product-card-modern { min-height: 0 !important; height: auto !important; cursor: pointer; user-select: none; position: relative; }
        .add-products-page .product-card-modern .product-img-area { min-height: 150px !important; height: 150px !important; }
        .add-products-page .product-card-modern .card-body { min-height: 0 !important; padding: 1.65em 0.6em 1.5em 0.6em !important; gap: 0.2em !important; }
        .add-products-page .product-card-modern .product-title { font-size: 0.82em !important; line-height: 1.18 !important; min-height: 2.1em !important; margin: 0 !important; }
        .add-products-page .product-card-modern .price-area { min-height: 1.5em !important; }
        .add-products-page .product-card-modern .badge-price,
        .add-products-page .product-card-modern .badge-price-sale { bottom: 0.2em !important; font-size: 0.84em !important; }
        .promotions-create-page .product-card-modern.selected { border-color: #f43f5e !important; transform: scale(1.02); box-shadow: 0 8px 32px rgba(244,63,94,.3) !important; }
        .promotions-create-page .product-card-modern:hover { transform: translateY(-2px) scale(1.01); }
        .ap-scroll::-webkit-scrollbar { width: 6px; }
        .ap-scroll::-webkit-scrollbar-thumb { background: rgba(244,63,94,.35); border-radius: 8px; }
        @media (min-width: 1024px) { .ap-list-pane, .ap-cart-pane { height: calc(100vh - 150px); } }
        /* O painel de ajuste precisa de mais espaço que o carrinho da venda */
        @media (min-width: 1024px) and (max-width: 1439.98px) {
            .promotions-create-page .ap-list-pane { width: 62% !important; }
            .promotions-create-page .ap-cart-pane { width: 38% !important; }
        }
        @media (min-width: 1440px) and (max-width: 2497.98px) {
            .promotions-create-page .ap-list-pane { width: 70% !important; }
            .promotions-create-page .ap-cart-pane { width: 30% !important; }
        }
        .promotions-create-page .promo-card-tags { max-width: calc(100% - 3rem); }
        /* Celular (inclui iPhone 15): 2 cards por linha, legíveis */
        @media (max-width: 767.98px) {
            .promotions-create-page .ap-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: .6rem !important; }
            .promotions-create-page .product-card-modern .product-img-area { min-height: 130px !important; height: 130px !important; }
            .promotions-create-page .product-card-modern .product-title { font-size: .74em !important; }
            .promotions-create-page .promo-tag { font-size: .6rem; padding: .15rem .45rem; }
        }
        /* iPad em pé: 3 por linha */
        @media (min-width: 768px) and (max-width: 1023.98px) {
            .promotions-create-page .ap-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        }
        /* iPad deitado (toque): a barra de baixo cobre o fim da tela */
        @media (min-width: 1024px) and (max-width: 1366px) and (pointer: coarse) {
            .promotions-create-page .ap-list-pane, .promotions-create-page .ap-cart-pane { height: calc(100vh - 235px) !important; }
            .promotions-create-page .ap-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        }
        /* Telas grandes: cards no mesmo tamanho de Produtos */
        @media (min-width: 1440px) and (max-width: 1919.98px) {
            .promotions-create-page .ap-grid { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
        }
        @media (min-width: 1920px) and (max-width: 2497.98px) {
            .promotions-create-page .ap-grid { grid-template-columns: repeat(6, minmax(0, 1fr)) !important; }
            .promotions-create-page .product-card-modern .product-img-area { min-height: 150px !important; height: 150px !important; }
            .promotions-create-page .product-card-modern .product-title { font-size: .78em !important; }
        }
        @media (min-width: 2498px) {
            .promotions-create-page .ap-list-pane { width: 75% !important; }
            .promotions-create-page .ap-cart-pane { width: 25% !important; }
            .promotions-create-page .ap-grid { grid-template-columns: repeat(7, minmax(0, 1fr)) !important; }
            .promotions-create-page .product-card-modern .product-img-area { min-height: 160px !important; height: 160px !important; }
        }
    </style>
</div>
