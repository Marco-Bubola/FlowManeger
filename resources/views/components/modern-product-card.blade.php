@props([
    'product',
    'selected' => false,
    'clickAction' => null
])

<div class="product-card-modern {{ $selected ? 'selected' : '' }}"
     @if($clickAction) wire:click="{{ $clickAction }}" @endif
     wire:key="product-{{ $product->id }}">

    <!-- Toggle de seleção estilizado -->
    <div class="btn-action-group flex gap-2">
        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                    {{ $selected
                        ? 'bg-purple-600 border-purple-600 text-white'
                        : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-purple-400 dark:hover:border-purple-500' }}">
            @if($selected)
            <i class="bi bi-check text-sm"></i>
            @endif
        </div>
    </div>

    <!-- Área da imagem com badges -->
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

        <!-- Código do produto -->
        <span class="badge-product-code">
            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
        </span>

        <!-- Quantidade em estoque -->
        <span class="badge-quantity">
            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
        </span>

        <!-- Ícone da categoria -->
        @if($product->category)
        <div class="category-icon-wrapper">
            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
        </div>
        @endif
    </div>

    <!-- Conteúdo do card -->
    <div class="card-body">
        <div class="product-title" title="{{ $product->name }}">
            {{ ucwords($product->name) }}
        </div>

        <!-- Área dos preços -->
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
