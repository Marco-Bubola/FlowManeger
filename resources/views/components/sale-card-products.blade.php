@props([
    'items' => collect(),
    'max' => 3,
])

@php
    $displayItems = $items->take($max);
@endphp

@if($displayItems->isNotEmpty())
    <div class="sale-card-products">
        @foreach($displayItems as $item)
            @php
                $product = $item->product;
            @endphp
            <div class="product-card-modern sale-card-product">
                <div class="product-img-area">
                    <img src="{{ $product && $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                         alt="{{ $product->name ?? 'Produto' }}"
                         class="product-img">

                    <span class="badge-product-code">
                        <i class="bi bi-upc-scan"></i> {{ $product->product_code ?? 'N/A' }}
                    </span>

                    <span class="badge-quantity">
                        <i class="bi bi-stack"></i> {{ $item->quantity }}x
                    </span>
                </div>

                <div class="card-body">
                    <div class="product-title" title="{{ $product->name ?? 'Produto não encontrado' }}">
                        {{ ucwords($product->name ?? 'Produto não encontrado') }}
                    </div>

                    <div class="price-area">
                        <span class="badge-price" title="Preço Unitário">
                            <i class="bi bi-tag"></i>
                            {{ number_format($item->price, 2, ',', '.') }}
                        </span>

                        <span class="badge-price-sale" title="Subtotal">
                            <i class="bi bi-currency-dollar"></i>
                            {{ number_format($item->price_sale * $item->quantity, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($items->count() > $max)
        <div class="sale-card-more">
            +{{ $items->count() - $max }} produtos
        </div>
    @endif
@endif
