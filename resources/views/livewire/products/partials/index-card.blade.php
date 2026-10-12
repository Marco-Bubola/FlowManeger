{{-- Card de produto simples da lista de produtos (sozinho ou como slide do carrossel de variações). --}}
<div class="product-card-modern">
    <!-- Botões flutuantes -->
    <div class="btn-action-group">

        <a href="{{ route('products.show', $product->product_code) }}"
            class="btn btn-secondary" title="Ver Detalhes">
            <i class="bi bi-eye"></i>
        </a>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"
            title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
        <button type="button"
            @click="$dispatch('open-product-share', { productId: {{ $product->id }} })"
            class="btn btn-wa" title="Enviar no WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </button>
        <a href="{{ route('products.breakdown', $product) }}" class="btn btn-breakdown" title="Desmembrar kit">
            <i class="bi bi-scissors"></i>
        </a>
        <button type="button"
            wire:click="$dispatch('openExportModal', { productId: {{ $product->id }} })"
            class="btn btn-success" title="Exportar Card">
            <i class="bi bi-file-earmark-image"></i>
        </button>
        <button type="button" wire:click="confirmDelete({{ $product->id }})"
            class="btn btn-danger" title="Excluir">
            <i class="bi bi-trash3"></i>
        </button>
    </div>

    <!-- Área da imagem com badges -->
    <div class="product-img-area">
        <img src="{{ asset('storage/products/' . $product->image) }}" class="product-img"
            alt="{{ $product->name }}">

        {{-- Cada card mostra só o próprio estoque (o principal não soma as variações) --}}
        @if ($product->stock_quantity == 0 && $product->tipo !== 'kit')
            <div class="out-of-stock">
                <i class="bi bi-x-circle"></i> Fora de Estoque
            </div>
        @endif

        <!-- Badge de variações (produto-pai); no carrossel quem mostra é a barra -->
        @if ($product->is_variation_parent && empty($inCarousel))
            @php
                $variantsCount = $product->variants()->count();
                $variantsInStock = $product->variants()->where('stock_quantity', '>', 0)->count() + ($product->stock_quantity > 0 ? 1 : 0);
            @endphp
            <div class="absolute top-2 left-2 z-10">
                <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg border border-violet-400"
                      title="{{ $variantsInStock }} de {{ $variantsCount + 1 }} com estoque">
                    <i class="bi bi-diagram-3 mr-1"></i>{{ $variantsInStock ? ($variantsCount + 1) . " var. · {$variantsInStock} c/ estoque" : ($variantsCount + 1) . " variações" }}
                </span>
            </div>
        @endif

        @if ($promo = $product->livePromotion())
            <a href="{{ route('promotions.index') }}" class="product-promo-flag {{ $product->is_variation_parent || $product->tipo === 'kit' ? 'is-low' : '' }}" title="Em promoção: de R$ {{ number_format($promo->original_price, 2, ',', '.') }} por R$ {{ number_format($promo->promo_price, 2, ',', '.') }}">
                <i class="bi bi-fire"></i><span>-{{ $promo->discount_percent }}%</span>
            </a>
        @endif

        <!-- Código do produto -->
        <span class="badge-product-code" title="Código do Produto">
            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
        </span>

        <!-- Quantidade -->
        <span class="badge-quantity" title="Quantidade em Estoque">
            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
        </span>

        <!-- Ícone da categoria -->
        <div class="category-icon-wrapper">
            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="card-body">
        <div class="product-title" title="{{ $product->name }}">
            {{ ucwords($product->name) }}
        </div>
        @if (!empty($inCarousel)) <x-variation-carousel-nav /> @endif
        @if ($favCount = (int) ($favCounts[$product->id] ?? 0))
            <a href="{{ route('products.interested', ['produto' => $product->id]) }}" class="product-fav-count" title="Clientes que favoritaram no catálogo online" data-testid="fav-count">
                <i class="bi bi-heart-fill"></i> {{ $favCount }} {{ $favCount === 1 ? 'cliente' : 'clientes' }}
            </a>
        @endif

        <!-- Área de preços dentro do card-body -->
        <div class="price-area mt-3">
            <div class="flex flex-col gap-2">
                <span class="badge-price" title="Preço de Custo">
                    <i class="bi bi-tag"></i>
                    R$ {{ number_format($product->price, 2, ',', '.') }}
                </span>

                @if ($promo = $product->livePromotion())
                    <span class="badge-price-sale" title="Em promoção (revenda R$ {{ number_format($product->price_sale, 2, ',', '.') }})" style="background: linear-gradient(135deg, #f43f5e 0%, #f97316 100%);">
                        <i class="bi bi-fire"></i>
                        <s class="opacity-75 text-[0.8em] mr-1">R$ {{ number_format($promo->original_price, 2, ',', '.') }}</s>
                        R$ {{ number_format($promo->promo_price, 2, ',', '.') }}
                    </span>
                @else
                    <span class="badge-price-sale" title="Preço de Venda">
                        <i class="bi bi-currency-dollar"></i>
                        R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
