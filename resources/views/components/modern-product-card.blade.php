@props([
    'product',
    'selected' => false,
    'clickAction' => null
])

<div class="product-card-modern card-hover {{ $selected ? 'selected' : '' }}"
     @if($clickAction) wire:click="{{ $clickAction }}" @endif
     wire:key="product-{{ $product->id }}">

    <!-- Toggle de seleção -->
    <div class="absolute top-3 right-3 z-10">
        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200
                    {{ $selected
                        ? 'bg-purple-600 border-purple-600 text-white'
                        : 'bg-white border-gray-300 text-transparent hover:border-purple-400' }}">
            @if($selected)
                <i class="bi bi-check text-sm"></i>
            @endif
        </div>
    </div>

    <!-- Imagem do produto -->
    <div class="product-img-area">
        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
             alt="{{ $product->name }}"
             class="product-img">

        <!-- Código do produto -->
        <span class="badge-product-code">
            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
        </span>

        <!-- Quantidade em estoque -->
        <span class="badge-quantity">
            <i class="bi bi-stack"></i> {{ $product->stock_quantity ?? 0 }}
        </span>

        <!-- Ícone da categoria -->
        @if($product->category)
            <div class="category-icon-wrapper">
                <i class="bi bi-tag category-icon"></i>
            </div>
        @endif
    </div>

    <!-- Conteúdo do card -->
    <div class="card-body">
        <div class="product-title" title="{{ $product->name }}">
            {{ ucwords($product->name) }}
        </div>
    </div>

    <!-- Preços -->
    <span class="badge-price">
        <i class="bi bi-tag"></i>
        Custo: R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}
    </span>

    <span class="badge-price-sale">
        <i class="bi bi-currency-dollar"></i>
        Venda: R$ {{ number_format($product->price_sale ?? 0, 2, ',', '.') }}
    </span>
</div>

<style>
/* Estilos do card de produto */
.product-card-modern {
    @apply relative bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:shadow-xl;
    cursor: pointer;
    user-select: none;
}

.product-img-area {
    @apply relative h-40 overflow-hidden;
}

.product-img {
    @apply w-full h-full object-cover transition-transform duration-300;
}

.product-card-modern:hover .product-img {
    @apply scale-105;
}

.badge-product-code {
    @apply absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full font-medium;
}

.badge-quantity {
    @apply absolute top-2 right-10 bg-green-600 text-white text-xs px-2 py-1 rounded-full font-medium;
}

.category-icon-wrapper {
    @apply absolute bottom-2 left-2 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center;
}

.category-icon {
    @apply text-white text-sm;
}

.card-body {
    @apply p-4;
}

.product-title {
    @apply font-bold text-gray-900 dark:text-white text-sm truncate;
}

.badge-price {
    @apply absolute bottom-2 left-2 bg-orange-600 text-white text-xs px-2 py-1 rounded-full font-medium;
}

.badge-price-sale {
    @apply absolute bottom-2 right-2 bg-emerald-600 text-white text-xs px-2 py-1 rounded-full font-medium;
}
</style>
