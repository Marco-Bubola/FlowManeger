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
                        : 'bg-white border-gray-300 text-transparent hover:border-purple-400' }}">
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

<style>
/* Posicionamentos e cores EXATOS da página de produtos - MÁXIMA ESPECIFICIDADE */
.product-card-modern.product-card-modern .badge-product-code {
    position: absolute !important;
    top: 0.5em !important;
    left: 0.5em !important;
    background: var(--blue-strong, #9575cd) !important;
    color: var(--white, #fff) !important;
    font-size: 0.98em !important;
    font-weight: 600 !important;
    padding: 0.32em 0.95em !important;
    border-radius: 1.2em !important;
    z-index: 4 !important;
    box-shadow: 0 2px 8px var(--badge-shadow-1, #9575cd33) !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.3em !important;
    letter-spacing: 0.03em !important;
}

.product-card-modern.product-card-modern .badge-quantity {
    position: absolute !important;
    bottom: 0.5em !important;
    right: 0.5em !important;
    background: var(--green-xlight, #f8bbd0) !important;
    color: var(--green-strong, #ba68c8) !important;
    font-size: 1em !important;
    font-weight: 700 !important;
    padding: 0.32em 1.1em !important;
    border-radius: 1.2em !important;
    z-index: 4 !important;
    box-shadow: 0 2px 8px var(--shadow-success, rgba(139, 195, 74, 0.2)) !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.2em !important;
    min-width: 38px !important;
    max-width: 80px !important;
    justify-content: center !important;
}

.product-card-modern.product-card-modern .category-icon-wrapper {
    position: absolute !important;
    left: 50% !important;
    bottom: -32px !important;
    transform: translateX(-50%) !important;
    z-index: 3 !important;
    background: var(--white, #fff) !important;
    border-radius: 50% !important;
    box-shadow: 0 4px 16px var(--badge-shadow-5, #f8bbd099) !important;
    width: 62px !important;
    height: 62px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.product-card-modern.product-card-modern .category-icon {
    font-size: 2.5em !important;
    color: var(--gray-title, #424242) !important;
    display: block !important;
}

.product-card-modern.product-card-modern .product-title {
    font-size: 1.13em !important;
    font-weight: 700 !important;
    color: var(--gray-title, #424242) !important;
    margin-bottom: 0.3em !important;
    box-shadow: none !important;
    display: block !important;
    max-width: 100% !important;
    overflow: hidden !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
    text-align: center !important;
}

.product-card-modern.product-card-modern .price-area {
    position: relative !important;
    min-height: 2.2em !important;
    margin-bottom: 0.2em !important;
}

.product-card-modern.product-card-modern .badge-price {
    position: absolute !important;
    left: 0 !important;
    bottom: 0 !important;
    background: var(--gradient-badge-price, linear-gradient(90deg, #f8bbd0 0%, #9575cd 100%)) !important;
    color: var(--white, #fff) !important;
    font-weight: 700 !important;
    font-size: 0.98em !important;
    border-radius: 1.2em 0 0.8em 0 !important;
    padding: 0.13em 0.9em !important;
    box-shadow: 0 2px 8px var(--badge-shadow-1, #9575cd33), 0 1px 4px var(--badge-shadow-2, #f8bbd011) !important;
    border-bottom: 2px solid var(--blue-gradient-2, #9575cd) !important;
    border-right: 2px solid var(--blue-gradient-2, #9575cd) !important;
    z-index: 2 !important;
    display: block !important;
    min-width: 60px !important;
    text-align: left !important;
}

.product-card-modern.product-card-modern .badge-price-sale {
    position: absolute !important;
    right: 0 !important;
    bottom: 0 !important;
    background: var(--gradient-badge-sale, linear-gradient(90deg, #9575cd 0%, #ba68c8 100%)) !important;
    color: var(--white, #fff) !important;
    font-weight: 700 !important;
    font-size: 0.98em !important;
    border-radius: 0 1.2em 0 0.8em !important;
    padding: 0.13em 0.9em !important;
    box-shadow: 0 2px 8px var(--badge-shadow-3, #ba68c833), 0 1px 4px var(--badge-shadow-4, #f8bbd011) !important;
    border-bottom: 2px solid var(--blue-gradient-1, #f8bbd0) !important;
    border-left: 2px solid var(--blue-gradient-1, #f8bbd0) !important;
    z-index: 2 !important;
    display: block !important;
    min-width: 60px !important;
    text-align: right !important;
}

/* Garantir que o CSS seja aplicado após qualquer carregamento */
.product-card-modern.product-card-modern.product-card-modern .badge-product-code,
.product-card-modern.product-card-modern.product-card-modern .badge-quantity,
.product-card-modern.product-card-modern.product-card-modern .category-icon-wrapper,
.product-card-modern.product-card-modern.product-card-modern .category-icon,
.product-card-modern.product-card-modern.product-card-modern .product-title,
.product-card-modern.product-card-modern.product-card-modern .price-area,
.product-card-modern.product-card-modern.product-card-modern .badge-price,
.product-card-modern.product-card-modern.product-card-modern .badge-price-sale {
    animation: none !important;
    transition: all 0.3s ease !important;
}
</style>
