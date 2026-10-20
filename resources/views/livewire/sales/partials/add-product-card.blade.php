{{-- Card de produto para adicionar à venda (sozinho ou como slide do carrossel de variações). --}}
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
            @elseif(!$product->made_to_order && $product->stock_quantity <= 5)
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
            @if (!empty($inCarousel)) <x-variation-carousel-nav /> @endif
            <div class="price-area">
                <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> {{ number_format($product->price, 2, ',', '.') }}</span>
                @if($apPromo = $product->livePromotion())<span class="badge-price-sale" title="Em promoção" style="background: linear-gradient(135deg, #f43f5e 0%, #f97316 100%);"><i class="bi bi-fire"></i> <s class="opacity-75 text-[0.8em]">{{ number_format($apPromo->original_price, 2, ',', '.') }}</s> {{ number_format($apPromo->promo_price, 2, ',', '.') }}</span>@else <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> {{ number_format($product->price_sale, 2, ',', '.') }}</span>@endif
            </div>
        </div>
    </div>
