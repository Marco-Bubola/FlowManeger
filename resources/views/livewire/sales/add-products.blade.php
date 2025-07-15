<div class="py-8">
    <!-- Incluir CSS dos produtos -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    
    <style>
        .product-card-modern.selected {
            border-color: #9575cd !important;
            background: linear-gradient(135deg, #e6e6fa 0%, #d1c4e9 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(149, 117, 205, 0.3) !important;
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
    
    <!-- Header -->
    <div class="mb-8 px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="bi bi-plus-circle text-indigo-600 dark:text-indigo-400 mr-3"></i>
                    Adicionar Produtos à Venda #{{ $sale->id }}
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Cliente: {{ $sale->client->name }} | Total atual: R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('sales.show', $sale->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Voltar para Venda
                </a>
            </div>
        </div>
    </div>

    <!-- Layout Split 3/4 e 1/4 -->
    <div class="w-full h-[75vh] flex">
        <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
        <div class="w-3/4 bg-white dark:bg-zinc-800 flex flex-col">
            <!-- Header com Controles -->
            <div class="p-2 border-b border-gray-200 dark:border-zinc-700">
                <!-- Controles de pesquisa e filtro -->
                <div class="mt-6 flex flex-col md:flex-row gap-4">
                    <!-- Campo de pesquisa -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-search text-gray-400"></i>
                            </div>
                            <input type="text"
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Pesquisar produtos por nome ou código..."
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                        </div>
                    </div>

                    <!-- Filtro de categoria -->
                    <div class="flex items-center">
                        <select wire:model.live="selectedCategory"
                                class="px-3 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Todas as categorias</option>
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
                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8v2m0 4h.01M21 21l-5-5m5 5v-4a1 1 0 00-1-1h-4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            @if($searchTerm)
                                Nenhum produto encontrado
                            @else
                                Nenhum produto disponível
                            @endif
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-center">
                            @if($searchTerm)
                                Tente pesquisar com outros termos
                            @else
                                Cadastre produtos para começar a vender
                            @endif
                        </p>
                    </div>
                @else
                    <!-- Grid de Cards de Produtos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                        @foreach($filteredProducts as $product)
                            @php
                                $isSelected = $this->isProductSelected($product->id);
                            @endphp

                            <div class="product-card-modern card-hover {{ $isSelected ? 'selected' : '' }}"
                                 wire:click="toggleProduct({{ $product->id }})"
                                 wire:key="product-{{ $product->id }}">

                                <!-- Toggle de seleção -->
                                <div class="absolute top-3 right-3 z-10">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 
                                                {{ $isSelected 
                                                    ? 'bg-purple-600 border-purple-600 text-white' 
                                                    : 'bg-white border-gray-300 text-transparent hover:border-purple-400' }}">
                                        @if($isSelected)
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
                                        <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
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
                                    Custo: R$ {{ number_format($product->price, 2, ',', '.') }}
                                </span>

                                <span class="badge-price-sale">
                                    <i class="bi bi-currency-dollar"></i>
                                    Venda: R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Lado Direito: Produtos Selecionados (1/4 da tela) -->
        <div class="w-1/4 flex flex-col">
            <!-- Header do painel direito -->
            <div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="bi bi-cart text-green-600 dark:text-green-400 mr-2 text-sm"></i>
                    Produtos ({{ count($newProducts) }})
                </h3>
            </div>

            <!-- Lista de produtos selecionados com scroll -->
            <div class="flex-1 overflow-y-auto">
                @if(empty($newProducts) || !$this->hasSelectedProducts())
                    <div class="p-3 text-center">
                        <div class="text-gray-400 mb-2">
                            <i class="bi bi-cart-x text-2xl"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-500 text-xs">
                            Clique nos produtos à esquerda para adicioná-los
                        </p>
                    </div>
                @else
                    <div class="p-2 space-y-2">
                        @foreach($newProducts as $index => $productItem)
                            @if($productItem['product_id'])
                                @php
                                    $selectedProduct = $filteredProducts->find($productItem['product_id']);
                                @endphp

                                @if($selectedProduct)
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-zinc-700">
                                        <!-- Header do produto -->
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center space-x-2">
                                                <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                                     alt="{{ $selectedProduct->name }}"
                                                     class="w-8 h-8 rounded object-cover">

                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-medium text-gray-900 dark:text-white text-xs truncate">{{ $selectedProduct->name }}</h4>
                                                    <div class="flex items-center justify-between">
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedProduct->product_code }}</p>
                                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Est: {{ $selectedProduct->stock_quantity }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Controles -->
                                        <div class="space-y-2">
                                            <!-- Quantidade e Preço na mesma linha -->
                                            <div class="grid grid-cols-2 gap-2">
                                                <!-- Quantidade -->
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Qtd:</label>
                                                    <input type="number"
                                                           wire:model="newProducts.{{ $index }}.quantity"
                                                           min="1"
                                                           max="{{ $selectedProduct->stock_quantity }}"
                                                           class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                                                </div>

                                                <!-- Preço -->
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Preço:</label>
                                                    <input type="number"
                                                           wire:model="newProducts.{{ $index }}.price_sale"
                                                           step="0.01"
                                                           min="0"
                                                           class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                                                </div>
                                            </div>

                                            <!-- Total do item -->
                                            <div class="pt-1 border-t border-gray-200 dark:border-zinc-600">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Total:</span>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs font-bold text-green-600 dark:text-green-400">
                                                            R$ {{ number_format($productItem['quantity'] * $productItem['price_sale'], 2, ',', '.') }}
                                                        </span>
                                                        <!-- Botão de excluir -->
                                                        <button type="button"
                                                                wire:click="removeProductRow({{ $index }})"
                                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1 hover:bg-red-50 dark:hover:bg-red-900 rounded transition-colors duration-200">
                                                            <i class="bi bi-trash text-xs"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Footer: Total Geral e Navegação -->
            <div class="p-3 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
                <!-- Total Geral -->
                @if($this->hasSelectedProducts())
                    <div class="mb-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-700">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-green-800 dark:text-green-200">Total:</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Navegação -->
                <div class="flex gap-2">
                    <a href="{{ route('sales.show', $sale->id) }}"
                       class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                        <i class="bi bi-arrow-left mr-1"></i>
                        Voltar
                    </a>

                    <button type="button"
                            wire:click="addProducts"
                            @if(!$this->hasSelectedProducts()) disabled @endif
                            class="flex-1 inline-flex items-center justify-center px-3 py-2 text-white text-xs font-bold rounded-lg shadow-lg transition-all duration-200 
                                   {{ $this->hasSelectedProducts() ? 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700' : 'bg-gray-400 cursor-not-allowed' }}">
                        <i class="bi bi-check mr-1"></i>
                        Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notificações -->
@if (session()->has('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-check-circle-fill mr-2"></i>
        {{ session('success') }}
        <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
@endif

@if (session()->has('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
        {{ session('error') }}
        <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
@endif
