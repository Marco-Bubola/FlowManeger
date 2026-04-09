<x-portal-layout title="Catálogo de Produtos">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-products.css') }}">
@endpush

@php
// Monta mapa de produtos para o modal Alpine (todos os produtos visíveis nesta página)
$allProds = $grouped
    ? $grouped->flatten()
    : collect($products ?? []);
$productsForModal = $allProds->keyBy('id')->map(fn($p) => [
    'id'          => $p->id,
    'name'        => $p->name,
    'description' => $p->description,
    'price'       => (float)($p->price_sale ?? 0),
    'priceFmt'    => $p->price_sale ? 'R$ ' . number_format($p->price_sale, 2, ',', '.') : null,
    'stock'       => (int)$p->stock_quantity,
    'code'        => $p->product_code,
    'brand'       => $p->brand,
    'model'       => $p->model,
    'condition'   => $p->condition,
    'warranty'    => $p->warranty_months,
    'catName'     => $p->category?->name,
    'catIcon'     => $p->category?->icone,
    'mainImg'     => ($p->image_url && !str_ends_with($p->image_url, 'product-placeholder.png')) ? $p->image_url : null,
    'images'      => $p->images->count() > 0
                        ? $p->images->pluck('url')->toArray()
                        : (($p->image_url && !str_ends_with($p->image_url, 'product-placeholder.png')) ? [$p->image_url] : []),
])->toArray();
@endphp

<div class="portal-products-page" x-data="{
    /* ── cart ── */
    cart: [],
    cartCount: 0,
    showToast: false,
    toastProd: '',
    _toastTimer: null,
    initCart() {
        this.cart = JSON.parse(localStorage.getItem('portal_cart') || '[]');
        this.cartCount = this.cart.length;
        // re-sync quando modal de produto adiciona ao carrinho
        window.addEventListener('portal-cart-updated', () => {
            this.cart = JSON.parse(localStorage.getItem('portal_cart') || '[]');
            this.cartCount = this.cart.length;
        });
    },
    inCart(id) { return this.cart.some(x => x.id === id); },
    addToCart(id, name, price, stock, img) {
        const arr = JSON.parse(localStorage.getItem('portal_cart') || '[]');
        const idx = arr.findIndex(x => x.id === id);
        if (idx >= 0) { arr[idx].qty = Math.min(arr[idx].qty + 1, stock || 999); }
        else { arr.push({ id, name, price: parseFloat(price)||0, stock: parseInt(stock)||0, img: img||null, qty: 1 }); }
        localStorage.setItem('portal_cart', JSON.stringify(arr));
        this.cart = arr;
        this.cartCount = arr.length;
        this.toastProd = name;
        this.showToast = true;
        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => this.showToast = false, 2500);
    },

    /* ── filter modal ── */
    filterOpen: false,
    filterSearch: '{{ addslashes($search) }}',
    filterCat: '{{ addslashes($category) }}',
    applyFilter() {
        const url = new URL(window.location.href);
        url.searchParams.set('search', this.filterSearch);
        url.searchParams.set('category', this.filterCat);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    },
    clearFilter() {
        window.location.href = '{{ route('portal.products') }}';
    }
}" x-init="initCart()">

    {{-- ════════════════════════════
         HEADER
    ════════════════════════════ --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon" style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney"></i> Início</a>
                    <i class="fas fa-chevron-right" style="font-size:.55rem"></i>
                    <span>Catálogo</span>
                </div>
                <h1 class="pph-title">Catálogo</h1>
            </div>

            {{-- Badges sm+ --}}
            <div class="hidden sm:flex items-center gap-2">
                <span class="pph-badge info">
                    <i class="fas fa-box" style="font-size:.6rem"></i>
                    {{ $grouped ? $grouped->flatten()->count() : $products->total() }} produto{{ ($grouped ? $grouped->flatten()->count() : $products->total()) !== 1 ? 's' : '' }}
                </span>
                @if($categories->count())
                <span class="pph-badge">
                    <i class="fas fa-tags" style="font-size:.6rem"></i>
                    {{ $categories->count() }} categoria{{ $categories->count() !== 1 ? 's' : '' }}
                </span>
                @endif
            </div>

            {{-- Botão filtro --}}
            <button type="button" @click="filterOpen = true"
                    class="pph-btn relative"
                    :class="filterSearch || filterCat ? 'ring-2 ring-amber-400 ring-offset-1' : ''">
                <i class="fas fa-sliders" style="font-size:.75rem"></i>
                <span class="hidden sm:inline">Filtrar</span>
                @if($search || $category)
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full shadow"></span>
                @endif
            </button>
        </div>
    </div>

    {{-- ════════════════════════════
         FILTRO ATIVO (pill)
    ════════════════════════════ --}}
    @if($search || $category)
    @php $activeCat = $categories->firstWhere('id', $category); @endphp
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="text-xs text-gray-500 dark:text-slate-400 font-semibold">Filtrando:</span>
        @if($search)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 rounded-full text-[11px] font-bold">
            <i class="fas fa-search text-[9px]"></i> "{{ $search }}"
            <a href="{{ route('portal.products', array_filter(['category'=>$category])) }}" class="ml-0.5 hover:text-sky-900 dark:hover:text-sky-100"><i class="fas fa-xmark text-[9px]"></i></a>
        </span>
        @endif
        @if($activeCat)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-[11px] font-bold">
            @if($activeCat->icone)<i class="{{ $activeCat->icone }} text-[9px]"></i>@endif
            {{ $activeCat->name }}
            <a href="{{ route('portal.products', array_filter(['search'=>$search])) }}" class="ml-0.5 hover:text-indigo-900 dark:hover:text-indigo-100"><i class="fas fa-xmark text-[9px]"></i></a>
        </span>
        @endif
        <a href="{{ route('portal.products') }}" class="text-[11px] font-bold text-gray-400 hover:text-gray-600 dark:text-slate-500 dark:hover:text-slate-300 underline ml-1">Limpar tudo</a>
    </div>
    @endif

    {{-- ════════════════════════════
         CONTEÚDO: AGRUPADO POR CATEGORIA
    ════════════════════════════ --}}
    @if($grouped && $grouped->isNotEmpty())
        @foreach($grouped as $catId => $catProducts)
        @php
            $firstProduct = $catProducts->first();
            $catName  = $firstProduct->category?->name ?? 'Outros';
            $catIcon  = $firstProduct->category?->icone ?? 'fas fa-box';
            $catColor = 'linear-gradient(135deg,#0ea5e9,#6366f1)';
        @endphp

        {{-- Cabeçalho da seção de categoria --}}
        <div class="pcat-section-head">
            <div class="pcat-section-icon" style="background:{{ $catColor }}">
                <i class="{{ $catIcon }}"></i>
            </div>
            <div class="pcat-section-title-wrap">
                <h2 class="pcat-section-title">{{ $catName }}</h2>
                <p class="pcat-section-sub">{{ $catProducts->count() }} produto{{ $catProducts->count() !== 1 ? 's' : '' }}</p>
            </div>
            {{-- Ver todos desta categoria --}}
            <a href="{{ route('portal.products', ['category' => $catId ?: '']) }}"
               class="pcat-section-link">
                <span class="hidden sm:inline">Ver todos</span>
                <span class="sm:hidden">Ver</span>
                <i class="fas fa-arrow-right" style="font-size:.65rem"></i>
            </a>
        </div>

        {{-- Linha scroll horizontal por categoria --}}
        <div class="pcat-scroll-row mb-10">
            @foreach($catProducts as $product)
            @include('portal.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        @endforeach

    {{-- ════════════════════════════
         CONTEÚDO: LISTA PAGINADA (com filtro)
    ════════════════════════════ --}}
    @elseif($products && $products->isNotEmpty())
        <div class="pcat-section-grid">
            @foreach($products as $product)
            @include('portal.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-5">
            {{ $products->appends(request()->query())->links() }}
        </div>

    @else
        <div class="portal-card p-14 text-center">
            <div class="w-14 h-14 bg-gray-100 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-boxes-stacked text-gray-400 dark:text-slate-500 text-xl"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900 dark:text-slate-200 mb-1">Nenhum produto encontrado</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">Tente outra busca ou entre em contato com o vendedor.</p>
        </div>
    @endif

    {{-- ═════════════════════════════
         MODAL DE FILTROS
         Bottom-sheet mobile · Dialog desktop
    ═══════════════════════════════════ --}}
    <div x-show="filterOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center"
         @click.self="filterOpen = false"
         style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)"
         x-cloak>

        <div x-show="filterOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
             class="filter-modal-panel"
             @click.stop>

            {{-- Handle drag (mobile) --}}
            <div class="sm:hidden flex justify-center pt-3 pb-0.5">
                <div class="w-9 h-[3px] rounded-full" style="background:rgba(0,0,0,0.15)"></div>
            </div>

            {{-- Header do modal --}}
            <div class="flex items-center justify-between px-5 pt-3 pb-3 border-b"
                 style="border-color:rgba(0,0,0,0.06)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#6366f1,#0ea5e9)">
                        <i class="fas fa-tags text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="font-black text-sm" style="color:#0f172a">Filtrar por categoria</p>
                        <p class="text-[10px]" style="color:#64748b">Toque para selecionar</p>
                    </div>
                </div>
                <button type="button" @click="filterOpen = false"
                        class="filter-modal-close">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            {{-- Categorias: grid de chips visuais --}}
            @if($categories->count())
            <div class="px-5 py-4">
                <div class="filter-cat-grid">
                    {{-- Chip "Todos" --}}
                    <button type="button" @click="filterCat = ''; applyFilter()"
                            :class="filterCat === '' ? 'filter-chip active' : 'filter-chip'">
                        <span class="filter-chip-icon">
                            <i class="fas fa-grid-2"></i>
                        </span>
                        <span class="filter-chip-label">Todos</span>
                    </button>
                    @foreach($categories as $cat)
                    <button type="button" @click="filterCat = '{{ $cat->getKey() }}'; applyFilter()"
                            :class="filterCat === '{{ $cat->getKey() }}' ? 'filter-chip active' : 'filter-chip'">
                        <span class="filter-chip-icon">
                            @if($cat->icone)<i class="{{ $cat->icone }}"></i>@else<i class="fas fa-box"></i>@endif
                        </span>
                        <span class="filter-chip-label">{{ $cat->name }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Footer: só limpar --}}
            <div class="px-5 pb-5 pt-1 flex items-center justify-between"
                 style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px))">
                <p class="text-[10px]" style="color:#94a3b8">
                    <i class="fas fa-info-circle mr-1"></i> Toque em uma categoria para filtrar
                </p>
                <button type="button" @click="clearFilter()"
                        class="text-[11px] font-bold underline" style="color:#6366f1">
                    Limpar
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed z-[999] flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-xl"
         style="bottom:calc(4.25rem + 1rem + env(safe-area-inset-bottom,0px));left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;min-width:220px;max-width:calc(100vw - 2rem)">
        <div class="w-7 h-7 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-basket-shopping text-xs"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-black leading-tight truncate" x-text="toastProd"></p>
            <p class="text-[9px] opacity-80 mt-0.5">Adicionado ao carrinho!</p>
        </div>
        <a href="{{ route('portal.quotes.create') }}" class="text-[10px] font-black underline opacity-90 hover:opacity-100 flex-shrink-0 whitespace-nowrap">Ver →</a>
    </div>

</div>

{{-- MODAL DE PRODUTO --}}
@include('portal.partials.product-modal', ['products_for_modal' => $productsForModal ?? []])

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // noop — sincronização via localStorage já ocorre no x-data
});
</script>
@endpush

</x-portal-layout>
