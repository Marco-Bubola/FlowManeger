<div x-data="{ currentStep: 1, completedSteps: [] }" class="">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <!-- Header Modernizado -->
    <x-sales-header
        title="Nova Venda"
        description="Registre uma nova venda no sistema seguindo os passos"
        :back-route="route('sales.index')"
        :current-step="$currentStep ?? 1"
        :steps="[
            [
                'title' => 'Cliente',
                'description' => 'Selecione o cliente',
                'icon' => 'bi-person',
                'gradient' => 'from-indigo-500 to-purple-500',
                'connector_gradient' => 'from-indigo-500 to-purple-500'
            ],
            [
                'title' => 'Produtos',
                'description' => 'Adicione produtos',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Resumo',
                'description' => 'Conferir e finalizar',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-green-500 to-emerald-500'
            ]
        ]" />

    <!-- Conteúdo Principal -->
    <div class="">
        <form wire:submit.prevent="save" class="">
            <div class="">

                <!-- Step 1: Seleção do Cliente - Full Width -->
                <div x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="">

                    <div class="px-8 py-8">
                        <!-- Informações do Cliente - Full Width, sem card -->
                        <div class=" p-8">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                                <i class="bi bi-person-circle text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                Informações do Cliente
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Cliente -->
                                <div class="md:col-span-2">

                                    <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="bi bi-person text-gray-400 mr-2"></i>Cliente *
                                    </label>
                                    <!-- Dropdown customizado sem Alpine, apenas Blade + Livewire -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button type="button" class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg flex items-center justify-between" @click="open = !open">
                                            <div class="flex items-center space-x-3">
                                                @php
                                                    $selectedClient = $clients->find($client_id);
                                                @endphp
                                                @if($selectedClient)
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                            <span>{{ strtoupper(substr($selectedClient->name, 0, 1) . (strpos($selectedClient->name, ' ') !== false ? substr($selectedClient->name, strpos($selectedClient->name, ' ') + 1, 1) : '')) }}</span>
                                                        </div>
                                                        <div class="flex flex-col items-start">
                                                            <span class="font-medium">{{ $selectedClient->name }}</span>
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedClient->phone }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-zinc-600 flex items-center justify-center">
                                                            <i class="bi bi-person text-gray-400"></i>
                                                        </div>
                                                        <span class="text-gray-500">Selecione um cliente...</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <i class="bi bi-chevron-down transition-transform duration-200" :class="open ? 'transform rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                            @foreach($clients as $client)
                                                <button type="button" wire:click="$set('client_id', {{ $client->id }})" @click="open = false" class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-zinc-600 flex items-center space-x-3 transition-colors duration-150">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                        <span>{{ strtoupper(substr($client->name, 0, 1) . (strpos($client->name, ' ') !== false ? substr($client->name, strpos($client->name, ' ') + 1, 1) : '')) }}</span>
                                                    </div>
                                                    <div class="flex flex-col items-start">
                                                        <span class="font-medium text-gray-900 dark:text-white">{{ $client->name }}</span>
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }}</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    @error('client_id')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>

                                <!-- Grid de 3 colunas: Data / Tipo de Pagamento / Parcelamento -->
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Data da Venda -->
                                    <div>
                                        <label for="sale_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            <i class="bi bi-calendar text-gray-400 mr-2"></i>Data da Venda *
                                        </label>
                                        <input type="date"
                                            wire:model="sale_date"
                                            id="sale_date"
                                            class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg @error('sale_date') border-red-300 @enderror">
                                        @error('sale_date')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                        @enderror
                                    </div>

                                    <!-- Tipo de Pagamento -->
                                    <div>
                                        <label for="tipo_pagamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            <i class="bi bi-credit-card text-gray-400 mr-2"></i>Tipo de Pagamento *
                                        </label>
                                        <select wire:model.live="tipo_pagamento"
                                            id="tipo_pagamento"
                                            class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg @error('tipo_pagamento') border-red-300 @enderror">
                                            <option value="a_vista">À Vista</option>
                                            <option value="parcelado">Parcelado</option>
                                        </select>
                                        @error('tipo_pagamento')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                        @enderror
                                    </div>

                                    <!-- Número de Parcelas (condicional) -->
                                    <div>
                                        @if($tipo_pagamento == 'parcelado')
                                        <label for="parcelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            <i class="bi bi-calendar-range text-indigo-500 mr-2"></i>Número de Parcelas *
                                        </label>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            @for($i = 1; $i <= 12; $i++)
                                            <button type="button"
                                                    wire:click="$set('parcelas', {{ $i }})"
                                                    class="p-2 text-center border rounded-lg transition-all duration-200 text-sm {{ $parcelas == $i ? 'bg-indigo-500 text-white border-indigo-500' : 'bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-zinc-600 hover:border-indigo-300' }}">
                                                {{ $i }}x
                                            </button>
                                            @endfor
                                        </div>
                                        @error('parcelas')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <i class="bi bi-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                        @enderror
                                        @else
                                        <div class="h-20 flex items-center justify-center text-gray-400 text-sm">
                                            <i class="bi bi-info-circle mr-2"></i>
                                            Selecione "Parcelado" para escolher parcelas
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Botão Próximo Moderno -->
                            <div class="w-full flex justify-end mt-8 pt-6 border-t border-gray-200 dark:border-zinc-600">
                                <button type="button"
                                    @click="currentStep = 2"
                                    @if(!$client_id) disabled @endif
                                    class="group relative inline-flex items-center justify-center px-8 py-4 rounded-2xl text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm {{ $client_id ? 'bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-600 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-700 border border-indigo-300' : 'bg-gray-400 cursor-not-allowed border border-gray-300' }}">
                                    <span class="flex items-center">
                                        Próximo: Produtos
                                        <i class="bi bi-arrow-right ml-2 group-hover:scale-110 transition-transform duration-200"></i>
                                    </span>
                                    @if($client_id)
                                    <!-- Efeito hover ring -->
                                    <div class="absolute inset-0 rounded-2xl bg-indigo-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Produtos - Layout Split 3/4 e 1/4 -->
                <div x-show="currentStep === 2"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full h-[75vh] flex">

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
                                            wire:model.live.debounce.300ms="searchProduct"
                                            placeholder="Pesquisar produtos por nome ou código..."
                                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                                    </div>
                                </div>

                                <!-- Toggle para mostrar apenas selecionados -->
                                <div class="flex items-center">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="checkbox"
                                            wire:model.live="showOnlySelected"
                                            class="w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                                            <i class="bi bi-funnel mr-1"></i>
                                            Apenas selecionados
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de Produtos com Scroll -->
                        <div class="flex-1 p-6 overflow-y-auto">
                            @if($this->getFilteredProducts()->isEmpty())
                            <!-- Estado vazio -->
                            <div class="flex flex-col items-center justify-center h-full">
                                <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8v2m0 4h.01M21 21l-5-5m5 5v-4a1 1 0 00-1-1h-4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    @if($showOnlySelected && empty($selectedProducts))
                                    Nenhum produto selecionado
                                    @elseif($searchProduct)
                                    Nenhum produto encontrado
                                    @else
                                    Nenhum produto disponível
                                    @endif
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">
                                    @if($showOnlySelected && empty($selectedProducts))
                                    Selecione alguns produtos para visualizá-los aqui
                                    @elseif($searchProduct)
                                    Tente pesquisar com outros termos
                                    @else
                                    Cadastre produtos para começar a vender
                                    @endif
                                </p>
                            </div>
                            @else
                            <!-- Grid de Cards de Produtos usando o mesmo estilo da página de produtos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                @foreach($this->getFilteredProducts() as $product)
                                @php
                                    $isSelected = in_array($product->id, $selectedProducts);
                                @endphp

                                <!-- Produto com CSS customizado mantido -->
                                <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                    wire:click="toggleProduct({{ $product->id }})"
                                    wire:key="product-{{ $product->id }}">

                                    <!-- Toggle de seleção estilizado -->
                                    <div class="btn-action-group flex gap-2">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                    {{ $isSelected
                                                        ? 'bg-purple-600 border-purple-600 text-white'
                                                        : 'bg-white border-gray-300 text-transparent hover:border-purple-400' }}">
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
                <div class="w-1/4  flex flex-col">
                    <!-- Header do painel direito -->
                    <div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="bi bi-cart text-green-600 dark:text-green-400 mr-2 text-sm"></i>
                            Produtos ({{ count($selectedProducts) }})
                        </h3>
                    </div>

                    <!-- Lista de produtos selecionados com scroll -->
                    <div class="flex-1 overflow-y-auto">
                        @if(empty($selectedProducts))
                        <div class="p-3 text-center">
                            <div class="text-gray-400 mb-2">
                                <i class="bi bi-cart-x text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-500 text-xs">
                                Clique nos produtos à esquerda para adicioná-los
                            </p>
                        </div>
                        @else
                        <div class="p-2 space-y-3">
                            @foreach($products as $index => $productItem)
                            @php
                            $selectedProduct = $availableProducts->find($productItem['product_id']);
                            @endphp

                            @if($selectedProduct)
                            <!-- Card de produto selecionado modernizado -->
                            <div class="bg-gradient-to-r from-white to-gray-50 dark:from-zinc-800 dark:to-zinc-900 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-zinc-700 hover:shadow-md transition-all duration-200">
                                <!-- Header do produto com imagem -->
                                <div class="flex items-center mb-3">
                                    <div class="flex-shrink-0 mr-3">
                                        <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                             alt="{{ $selectedProduct->name }}"
                                             class="w-10 h-10 rounded-lg object-cover ring-2 ring-purple-200 dark:ring-purple-700">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $selectedProduct->name }}">
                                            {{ $selectedProduct->name }}
                                        </h4>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">
                                                #{{ $selectedProduct->product_code }}
                                            </p>
                                            <button type="button"
                                                    wire:click="toggleProduct({{ $selectedProduct->id }})"
                                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded transition-colors">
                                                <i class="bi bi-trash text-xs"></i>
                                            </button>
                                        </div>
                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                                            Est: {{ $selectedProduct->stock_quantity }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Controles em grid -->
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Quantidade -->
                                    <div>
                                        <label class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1 block">Quantidade</label>
                                        <input type="number"
                                               wire:change="updateProductQuantity({{ $selectedProduct->id }}, $event.target.value)"
                                               value="{{ $productItem['quantity'] }}"
                                               min="1"
                                               max="{{ $selectedProduct->stock_quantity }}"
                                               class="w-full h-7 text-center text-xs border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    </div>

                                    <!-- Preço Unitário -->
                                    <div>
                                        <label class="text-xs text-gray-600 dark:text-gray-400 font-medium mb-1 block">Preço Unit.</label>
                                        <div class="relative">
                                            <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-xs text-gray-500">R$</span>
                                            <input type="number"
                                                   wire:change="updateProductPrice({{ $selectedProduct->id }}, $event.target.value)"
                                                   value="{{ $productItem['unit_price'] }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full h-7 text-xs border border-gray-300 dark:border-zinc-600 rounded-lg pl-7 pr-2 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Total do item destacado -->
                                <div class="mt-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-2 border border-green-200 dark:border-green-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-green-700 dark:text-green-300 font-medium flex items-center">
                                            <i class="bi bi-calculator mr-1"></i>
                                            Subtotal:
                                        </span>
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                            R$ {{ number_format($productItem['quantity'] * $productItem['unit_price'], 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Footer: Total Geral e Navegação -->
                    <div class="p-3 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
                        <!-- Total Geral -->
                        @if(!empty($selectedProducts))
                        <div class="mb-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-green-800 dark:text-green-200">Total:</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Navegação Modernizada -->
                        <div class="flex gap-2">
                            <button type="button"
                                @click="currentStep = 1"
                                class="group relative inline-flex items-center justify-center flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white text-xs font-medium transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                                <i class="bi bi-arrow-left mr-1 group-hover:scale-110 transition-transform duration-200"></i>
                                Cliente
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>

                            <button type="button"
                                @click="if({{ count($selectedProducts) }} > 0) currentStep = 3"
                                @if(count($selectedProducts) === 0) disabled @endif
                                class="group relative inline-flex items-center justify-center flex-1 px-3 py-2 rounded-xl text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm {{ count($selectedProducts) > 0 ? 'bg-gradient-to-br from-purple-500 via-pink-500 to-rose-600 hover:from-purple-600 hover:via-pink-600 hover:to-rose-700 border border-purple-300' : 'bg-gray-400 cursor-not-allowed border border-gray-300' }}">
                                <span class="flex items-center">
                                    Resumo
                                    <i class="bi bi-arrow-right ml-1 group-hover:scale-110 transition-transform duration-200"></i>
                                </span>
                                @if(count($selectedProducts) > 0)
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-purple-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Resumo e Finalização - Layout em duas colunas -->
            <div x-show="currentStep === 3"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                class="w-full max-h-screen flex overflow-hidden">

                <!-- Coluna Esquerda: Informações do Cliente e Total (2/5 da tela) -->
                <div class="w-2/5 bg-white dark:bg-zinc-800 p-4 flex flex-col">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        <i class="bi bi-check-circle text-green-600 dark:text-green-400 mr-2"></i>
                        Resumo da Venda
                    </h2>

                    <!-- Informações do Cliente -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 mb-4 rounded-lg border-l-4 border-blue-500">
                        <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200 mb-3">
                            <i class="bi bi-person-circle mr-2"></i>Cliente
                        </h3>
                        @if($client_id && $selectedClient = $clients->find($client_id))
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Nome:</p>
                                    <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ $selectedClient->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Telefone:</p>
                                    <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ $selectedClient->phone }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Data:</p>
                                    <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ \Carbon\Carbon::parse($sale_date)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Pagamento:</p>
                                    <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ ucfirst(str_replace('_', ' ', $tipo_pagamento)) }}</p>
                                </div>
                            </div>

                        </div>
                        @endif
                    </div>

                    <!-- Total Geral -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 rounded-lg border-l-4 border-indigo-500 mb-4">
                        <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-200 mb-2">
                            <i class="bi bi-calculator mr-2"></i>Total da Venda
                        </h3>

                        @if($tipo_pagamento === 'parcelado' && $parcelas > 1)
                        <!-- Informações de Parcelamento Destacadas -->
                        <div class="bg-white dark:bg-indigo-800/30 p-4 rounded-lg border border-indigo-200 dark:border-indigo-600 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-lg font-semibold text-indigo-700 dark:text-indigo-300">
                                    <i class="bi bi-credit-card-2-front mr-2"></i>Pagamento Parcelado
                                </span>
                                <span class="bg-indigo-100 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm font-bold">
                                    {{ $parcelas }}x
                                </span>
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-indigo-600 dark:text-indigo-400">Valor por parcela:</span>
                                    <span class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                                        R$ {{ number_format($this->getTotalAmount() / $parcelas, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-2">
                                    <i class="bi bi-info-circle mr-1"></i>
                                    Parcelas mensais de {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                    até {{ \Carbon\Carbon::now()->addMonths($parcelas - 1)->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-white dark:bg-green-800/30 p-4 rounded-lg border border-green-200 dark:border-green-600 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-green-700 dark:text-green-300">
                                    <i class="bi bi-cash mr-2"></i>Pagamento à Vista
                                </span>
                                <span class="bg-green-100 dark:bg-green-700 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-bold">
                                    À Vista
                                </span>
                            </div>
                        </div>
                        @endif

                        <div class="text-center">
                            @if($tipo_pagamento === 'parcelado' && $parcelas > 1)
                            <p class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">
                                {{ $parcelas }}x de R$ {{ number_format($this->getTotalAmount() / $parcelas, 2, ',', '.') }}
                            </p>
                            @endif
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                R$ {{ number_format($this->getTotalAmount(), 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Navegação Final Modernizada -->
                    <div class="mt-auto space-y-3">
                        <button type="button"
                            @click="currentStep = 2"
                            class="group relative w-full inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                            <i class="bi bi-arrow-left mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                            Voltar: Produtos
                            <!-- Efeito hover ring -->
                            <div class="absolute inset-0 rounded-2xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>

                        <button type="submit"
                            class="group relative w-full inline-flex items-center justify-center px-12 py-4 rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 hover:from-green-600 hover:via-emerald-600 hover:to-teal-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-green-300 backdrop-blur-sm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove class="flex items-center">
                                <i class="bi bi-check-circle mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                                Finalizar Venda
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Criando Venda...
                            </span>
                            <!-- Efeito hover ring -->
                            <div class="absolute inset-0 rounded-2xl bg-green-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </div>

                <!-- Coluna Direita: Lista de Produtos (3/5 da tela) -->
                <div class="w-4/5 bg-green-50 dark:bg-green-900/20 border-l border-gray-200 dark:border-zinc-700 p-8">
                    <h3 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-6">
                        <i class="bi bi-cart mr-2"></i>Produtos Selecionados ({{ count($products) }})
                    </h3>

                    <!-- Grid de Cards de Produtos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-118">
                        @foreach($products as $product)
                        @if($product['product_id'])
                        @php
                        $productData = $availableProducts->find($product['product_id']);
                        $total = $product['quantity'] * $product['unit_price'];
                        @endphp

                        <!-- Card do Produto -->
                        <div class="product-card-modern">
                            <!-- Área da imagem com badges -->
                            <div class="product-img-area">
                                <img src="{{ $productData->image ? asset('storage/products/' . $productData->image) : asset('storage/products/product-placeholder.png') }}"
                                     alt="{{ $productData->name ?? 'Produto' }}"
                                     class="product-img">

                                <!-- Badge da quantidade selecionada -->
                                <span class="badge-product-code">
                                    <i class="bi bi-upc-scan"></i> {{ $productData->product_code ?? 'N/A' }}
                                </span>

                                <!-- Badge da quantidade -->
                                <span class="badge-quantity">
                                    <i class="bi bi-cart-check"></i> {{ $product['quantity'] }}
                                </span>

                                <!-- Ícone da categoria -->
                                @if($productData && $productData->category)
                                <div class="category-icon-wrapper">
                                    <i class="{{ $productData->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                </div>
                                @endif
                            </div>

                            <!-- Conteúdo do card -->
                            <div class="card-body">
                                <div class="product-title" title="{{ $productData->name ?? 'Produto não encontrado' }}">
                                    {{ ucwords($productData->name ?? 'Produto não encontrado') }}
                                </div>

                                <!-- Área dos preços -->
                                <div class="price-area">
                                    <span class="badge-price" title="Preço Unitário">
                                        <i class="bi bi-tag"></i>
                                        {{ number_format($product['unit_price'], 2, ',', '.') }}
                                    </span>

                                    <span class="badge-price-sale" title="Total">
                                        <i class="bi bi-calculator"></i>
                                        {{ number_format($total, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <!-- Resumo Total abaixo dos cards -->
                    <div class="mt-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-green-800 dark:text-green-200">
                                <i class="bi bi-calculator mr-2"></i>
                                Total Geral:
                            </span>
                            <span class="text-3xl font-bold text-green-600 dark:text-green-400">
                                R$ {{ number_format($this->getTotalAmount(), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </form>
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
</div>
