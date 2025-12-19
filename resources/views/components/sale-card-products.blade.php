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
            <div class="product-card-modern sale-card-product bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 overflow-hidden rounded-xl">
                <div class="product-img-area relative" style="padding: 0 !important; margin: 0 !important; height: 130px;">
                    <img src="{{ $product && $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                         alt="{{ $product->name ?? 'Produto' }}"
                         style="width: 100%; height: 100%; object-fit: cover; padding: 0; margin: 0; display: block;"
                         class="!p-0 !m-0">

                    <span class="badge-product-code bg-slate-900/80 dark:bg-slate-800/90 text-white">
                        <i class="bi bi-upc-scan"></i> {{ $product->product_code ?? 'N/A' }}
                    </span>

                    <span class="badge-quantity bg-blue-600/90 dark:bg-blue-500/90 text-white">
                        <i class="bi bi-stack"></i> {{ $item->quantity }}x
                    </span>
                </div>

                <div class="card-body bg-white dark:bg-slate-700">
                    <div class="product-title text-slate-900 dark:text-white" title="{{ $product->name ?? 'Produto não encontrado' }}">
                        {{ ucwords($product->name ?? 'Produto não encontrado') }}
                    </div>

                    <div class="price-area">
                        <span class="badge-price bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200" title="Preço Unitário">
                            <i class="bi bi-tag"></i>
                            {{ number_format($item->price, 2, ',', '.') }}
                        </span>

                        <span class="badge-price-sale bg-gradient-to-r from-emerald-500 to-green-500 text-white" title="Subtotal">
                            <i class="bi bi-currency-dollar"></i>
                            {{ number_format($item->price_sale * $item->quantity, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($items->count() > $max)
        <div class="sale-card-more text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
            +{{ $items->count() - $max }} produtos
        </div>
    @endif
@endif
