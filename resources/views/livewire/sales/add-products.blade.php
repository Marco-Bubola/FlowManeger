<div class="add-products-page w-full mobile-393-base">
    <!-- Incluir CSS dos produtos -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-products-ultrawide.css') }}">

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
            position: relative;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }

        /* Badge de quantidade selecionada */
        .selected-qty-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 22px;
            height: 22px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 6px rgba(16,185,129,0.5);
            z-index: 10;
            border: 2px solid white;
        }

        /* Sort button ativo */
        .sort-btn-active {
            color: #059669;
            font-weight: 600;
        }

        /* Input com erro de estoque */
        .stock-exceeded {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
        }

        .dark .stock-exceeded {
            background: rgba(239,68,68,0.15) !important;
        }
    </style>

    <!-- Header Compacto Sticky (mesmo visual do Create/Edit Sale) -->
    <div class="sticky top-0 z-50 bg-white/88 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/55 dark:border-slate-700/40 -mx-[0.85rem] px-4 py-2.5 mb-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.show', $sale->id) }}"
               class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition-all">
                <i class="bi bi-arrow-left text-sm"></i>
            </a>
            <span class="shrink-0 w-9 h-9 inline-flex items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow">
                <i class="bi bi-plus-circle text-base"></i>
            </span>
            <div class="flex-1 min-w-0">
                <h1 class="text-sm font-bold text-slate-800 dark:text-white leading-tight">Adicionar Produtos</h1>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                    Venda #{{ $sale->id }} · {{ $sale->client->name ?? 'Cliente' }}
                </p>
            </div>
            @if(count($newProducts) > 0 && $this->hasSelectedProducts())
            <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                <i class="bi bi-check-circle-fill"></i>{{ count(array_filter($newProducts, fn($p) => !empty($p['product_id']))) }} novos
            </span>
            @endif
        </div>
    </div>

    <!-- Layout Split 3/4 e 1/4 -->
    <div class="w-full flex add-products-split-shell">
        <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
        <div class="w-3/4 bg-white dark:bg-zinc-800 flex flex-col add-products-pane">
            <!-- Header com Controles -->
            <div class="p-3 border-b border-gray-200 dark:border-zinc-700">
                <div class="flex flex-row items-center gap-2 mb-2">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                        <i class="bi bi-box text-emerald-600 dark:text-emerald-400"></i>
                        Selecionar Produtos
                    </h2>
                    <div class="flex items-center gap-1.5 ml-auto">
                        @if($filteredProducts->isNotEmpty())
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-zinc-700 px-2 py-0.5 rounded-lg">
                            {{ $filteredProducts->count() }} {{ $filteredProducts->count() === 1 ? 'produto' : 'produtos' }}
                        </span>
                        @endif
                        @if(count($newProducts) > 0 && $this->hasSelectedProducts())
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-2 py-0.5 rounded-lg">
                            <i class="bi bi-check-circle-fill mr-1"></i>{{ count(array_filter($newProducts, fn($p) => !empty($p['product_id']))) }} selecionados
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Controles de pesquisa e filtro -->
                <div class="flex flex-col md:flex-row gap-2">
                    <!-- Campo de pesquisa com botão de limpar -->
                    <div class="flex-1">
                        <div class="relative">
                            <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text"
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Buscar por nome, código ou categoria..."
                                class="w-full pl-12 pr-10 py-2 border border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                            @if($searchTerm)
                            <button type="button" wire:click="$set('searchTerm', '')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                <i class="bi bi-x-circle-fill text-base"></i>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Filtro de categoria -->
                    <div class="flex items-center">
                        <select wire:model.live="selectedCategory"
                                class="px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200 text-sm">
                            <option value="">Todas as categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ordenação -->
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-zinc-700 rounded-xl px-2 py-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 mr-1 whitespace-nowrap">
                            <i class="bi bi-sort-alpha-down mr-1"></i>Ordenar:
                        </span>
                        <button type="button"
                                wire:click="setSortBy('name')"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $sortBy === 'name' ? 'bg-white dark:bg-zinc-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                            Nome
                            @if($sortBy === 'name')
                                <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-0.5"></i>
                            @endif
                        </button>
                        <button type="button"
                                wire:click="setSortBy('price_sale')"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $sortBy === 'price_sale' ? 'bg-white dark:bg-zinc-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                            Preço
                            @if($sortBy === 'price_sale')
                                <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-0.5"></i>
                            @endif
                        </button>
                        <button type="button"
                                wire:click="setSortBy('stock_quantity')"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $sortBy === 'stock_quantity' ? 'bg-white dark:bg-zinc-600 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                            Estoque
                            @if($sortBy === 'stock_quantity')
                                <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-0.5"></i>
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid de Produtos com Scroll -->
            <div class="flex-1 p-3 overflow-y-auto">
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
                                $selectedQty = $isSelected ? $this->getSelectedQuantity($product->id) : 0;
                            @endphp

                            <!-- Produto com CSS customizado mantido -->
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})"
                                 wire:key="product-{{ $product->id }}">

                                <!-- Badge de quantidade selecionada -->
                                @if($isSelected)
                                <div class="selected-qty-badge">{{ $selectedQty }}x</div>
                                @endif

                                <!-- Toggle de seleção estilizado -->
                                <div class="btn-action-group flex gap-2">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                {{ $isSelected
                                                    ? 'bg-emerald-600 border-emerald-600 text-white'
                                                    : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400 dark:hover:border-emerald-500' }}">
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
