<div class="w-full">
    <!-- Incluir CSS dos produtos -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
        }

        .product-card-modern {
            cursor: pointer;
            user-select: none;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }
    </style>

    <!-- Header Modernizado -->
    <x-sales-add-products-header
        :sale="$sale"
        :back-route="route('sales.show', $sale->id)"
        :total-selected="count($newProducts)" />

    <!-- Layout Split 3/4 e 1/4 -->
    <div class="w-full h-[75vh] flex">
        <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
        <div class="w-3/4 bg-white dark:bg-zinc-800 flex flex-col">
            <!-- Header com Controles -->
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-box text-emerald-600 dark:text-emerald-400 mr-3"></i>
                    Selecionar Produtos
                </h2>

                <!-- Controles de pesquisa e filtro -->
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Campo de pesquisa -->
                    <div class="flex-1">
                        <div class="relative">
                            <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text"
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Buscar produtos por nome, código ou categoria..."
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Filtro de categoria -->
                    <div class="flex items-center">
                        <select wire:model.live="selectedCategory"
                                class="px-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                            <option value="">
                                <i class="bi bi-funnel mr-2"></i>
                                Todas as categorias
                            </option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid de Produtos com Scroll -->
            <div class="flex-1 p-6 overflow-y-auto">
                @if($filteredProducts->isEmpty())
                    <!-- Estado vazio -->
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                            <i class="bi bi-box text-8xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            @if($searchTerm)
                                Nenhum produto encontrado para "{{ $searchTerm }}"
                            @else
                                Nenhum produto disponível
                            @endif
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-center">
                            @if($searchTerm)
                                Tente buscar por outro termo ou limpe o campo de pesquisa.
                            @else
                                Adicione produtos ao sistema para poder incluí-los nas vendas.
                            @endif
                        </p>
                    </div>
                @else
                    <!-- Grid de Cards de Produtos usando o mesmo estilo da página de produtos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($filteredProducts as $product)
                            @php
                                $isSelected = $this->isProductSelected($product->id);
                            @endphp

                            <!-- Produto com CSS customizado mantido -->
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})"
                                 wire:key="product-{{ $product->id }}">

                                <!-- Toggle de seleção estilizado -->
                                <div class="btn-action-group flex gap-2">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                {{ $isSelected
                                                    ? 'bg-emerald-600 border-emerald-600 text-white'
                                                    : 'bg-white border-gray-300 text-transparent hover:border-emerald-400' }}">
                                        @if($isSelected)
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
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Lado Direito: Produtos Selecionados (1/4 da tela) -->
        <div class="w-1/4 flex flex-col">
            <x-sales-add-products-panel
                :new-products="$newProducts"
                :filtered-products="$filteredProducts"
                :has-selected-products="$this->hasSelectedProducts()"
                :total-price="$this->getTotalPrice()"
                :sale="$sale" />
        </div>
    </div>

    <!-- Toast Notifications Component -->
    <x-toast-notifications />
</div>
