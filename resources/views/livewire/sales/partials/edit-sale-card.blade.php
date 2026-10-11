{{-- Card de produto na edição da venda (sozinho ou como slide do carrossel de variações). --}}
@php
    $isSelected = collect($selectedProducts)->contains(function($selected) use ($product) {
        return $selected['product_id'] == $product->id;
    });
@endphp
<div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
    wire:click="toggleProduct({{ $product->id }})"
    wire:key="product-{{ $product->id }}">

    <div class="btn-action-group flex gap-2">
        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                    {{ $isSelected
                        ? 'bg-purple-600 border-purple-600 text-white'
                        : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-purple-400 dark:hover:border-purple-500' }}">
            @if($isSelected)
            <i class="bi bi-check text-sm"></i>
            @endif
        </div>
    </div>

    <div class="product-img-area">
        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
            alt="{{ $product->name }}"
            class="product-img">
        @if($product->stock_quantity <= 5)
        <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
            <i class="bi bi-exclamation-triangle mr-1"></i>
            Baixo estoque
        </div>
        @endif
        <span class="badge-product-code">
            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
        </span>
        <span class="badge-quantity">
            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
        </span>
        @if($product->category)
        <div class="category-icon-wrapper">
            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
        </div>
        @endif
    </div>

    <div class="card-body">
        <div class="product-title" title="{{ $product->name }}">
            {{ ucwords($product->name) }}
        </div>
        @if (!empty($inCarousel)) <x-variation-carousel-nav /> @endif
        <div class="price-area">
            <span class="badge-price" title="Preço de Custo">
                <i class="bi bi-tag"></i>
                {{ number_format($product->price, 2, ',', '.') }}
            </span>
            <span class="badge-price-sale" title="Preço de Venda">
                <i class="bi bi-currency-dollar"></i>
                {{ number_format($product->price_sale, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>
