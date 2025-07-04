<div x-data="{ currentStep: 1, completedSteps: [] }" class=" ">
    <!-- Header com Steppers - Full Width -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 sticky top-0 z-10 shadow-sm">
        <div class="w-full px-8 py-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('sales.index') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white dark:bg-zinc-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all duration-200 shadow-sm border border-indigo-200 dark:border-indigo-700">
                        <i class="bi bi-arrow-left text-xl text-indigo-600 dark:text-indigo-400"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl mr-4 shadow-lg">
                                <i class="bi bi-plus-circle text-white text-xl"></i>
                            </div>
                            Nova Venda
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">Registre uma nova venda no sistema seguindo os passos</p>
                    </div>
                </div>

                <!-- Steppers Melhorados -->
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-6">
                        <!-- Step 1: Cliente -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                                :class="currentStep === 1 ? 'bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-lg shadow-indigo-500/30' : (currentStep > 1 ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                <i class="bi bi-person text-xl" x-show="currentStep === 1"></i>
                                <i class="bi bi-check-lg text-xl" x-show="currentStep > 1"></i>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <p class="text-lg font-bold transition-colors duration-300"
                                        :class="currentStep === 1 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">Cliente</p>
                                    <i class="bi bi-check-circle-fill text-green-500 ml-2 text-lg" x-show="currentStep > 1"></i>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Selecione o cliente</p>
                            </div>
                        </div>

                        <!-- Connector 1 -->
                        <div class="w-16 h-1 rounded-full transition-all duration-300"
                            :class="currentStep >= 2 ? 'bg-gradient-to-r from-indigo-500 to-purple-500' : 'bg-gray-300 dark:bg-zinc-600'"></div>

                        <!-- Step 2: Produtos -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                                :class="currentStep === 2 ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/30' : (currentStep > 2 ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                <i class="bi bi-box text-xl" x-show="currentStep === 2"></i>
                                <i class="bi bi-check-lg text-xl" x-show="currentStep > 2"></i>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <p class="text-lg font-bold transition-colors duration-300"
                                        :class="currentStep === 2 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">Produtos</p>
                                    <i class="bi bi-check-circle-fill text-green-500 ml-2 text-lg" x-show="currentStep > 2"></i>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Adicione produtos</p>
                            </div>
                        </div>

                        <!-- Connector 2 -->
                        <div class="w-16 h-1 rounded-full transition-all duration-300"
                            :class="currentStep >= 3 ? 'bg-gradient-to-r from-purple-500 to-pink-500' : 'bg-gray-300 dark:bg-zinc-600'"></div>

                        <!-- Step 3: Resumo -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                                :class="currentStep === 3 ? 'bg-gradient-to-br from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/30' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400'">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <p class="text-lg font-bold transition-colors duration-300"
                                        :class="currentStep === 3 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">Resumo</p>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Conferir e finalizar</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Conteúdo Principal - Full Width -->
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
                                    <select wire:model="tipo_pagamento"
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
                                @if($tipo_pagamento == 'parcelado')
                                <div>
                                    <label for="parcelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="bi bi-list-ol text-gray-400 mr-2"></i>Número de Parcelas
                                    </label>
                                    <input type="number"
                                        wire:model="parcelas"
                                        id="parcelas"
                                        min="2"
                                        max="12"
                                        class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg @error('parcelas') border-red-300 @enderror">
                                    @error('parcelas')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>
                                @endif
                            </div>

                            <!-- Botão Próximo - Full Width -->
                            <div class="w-full flex justify-end mt-8 pt-6 border-t border-gray-200 dark:border-zinc-600">
                                <button type="button"
                                    @click="currentStep = 2"
                                    @if(!$client_id) disabled @endif
                                    class="inline-flex items-center px-8 py-4 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 {{ $client_id ? 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105' : 'bg-gray-400 cursor-not-allowed' }}">
                                    Próximo: Produtos
                                    <i class="bi bi-arrow-right ml-2"></i>
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
                            <!-- Grid de Cards de Produtos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                                @foreach($this->getFilteredProducts() as $product)
                                @php
                                $isSelected = in_array($product->id, $selectedProducts);
                                $quantity = $this->getProductQuantity($product->id);
                                $price = $this->getProductPrice($product->id);
                                @endphp

                                <div class="product-card {{ $isSelected ? 'selected' : '' }}"
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

                                        <!-- Badge de estoque -->
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
                        <div class="p-2 space-y-2">
                            @foreach($products as $index => $productItem)
                            @php
                            $selectedProduct = $availableProducts->find($productItem['product_id']);
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
                                                wire:change="updateProductQuantity({{ $selectedProduct->id }}, $event.target.value)"
                                                value="{{ $productItem['quantity'] }}"
                                                min="1"
                                                max="{{ $selectedProduct->stock_quantity }}"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-purple-500 focus:border-transparent">
                                        </div>

                                        <!-- Preço -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Preço:</label>
                                            <input type="number"
                                                wire:change="updateProductPrice({{ $selectedProduct->id }}, $event.target.value)"
                                                value="{{ $productItem['unit_price'] }}"
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
                                                    R$ {{ number_format($productItem['quantity'] * $productItem['unit_price'], 2, ',', '.') }}
                                                </span>
                                                <!-- Botão de excluir -->
                                                <button type="button"
                                                    wire:click="toggleProduct({{ $selectedProduct->id }})"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1 hover:bg-red-50 dark:hover:bg-red-900 rounded transition-colors duration-200">
                                                    <i class="bi bi-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
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

                        <!-- Navegação -->
                        <div class="flex gap-2">
                            <button type="button"
                                @click="currentStep = 1"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                                <i class="bi bi-arrow-left mr-1"></i>
                                Cliente
                            </button>

                            <button type="button"
                                @click="if({{ count($selectedProducts) }} > 0) currentStep = 3"
                                @if(count($selectedProducts) === 0) disabled @endif
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-white text-xs font-bold rounded-lg shadow-lg transition-all duration-200 {{ count($selectedProducts) > 0 ? 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700' : 'bg-gray-400 cursor-not-allowed' }}">
                                Resumo
                                <i class="bi bi-arrow-right ml-1"></i>
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
                class="w-full h-screen flex">

                <!-- Coluna Esquerda: Informações do Cliente e Total (2/5 da tela) -->
                <div class="w-2/5 bg-white dark:bg-zinc-800 p-8 flex flex-col">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                        <i class="bi bi-check-circle text-green-600 dark:text-green-400 mr-3"></i>
                        Resumo da Venda
                    </h2>

                    <!-- Informações do Cliente -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 mb-6 rounded-xl border-l-4 border-blue-500">
                        <h3 class="text-xl font-bold text-blue-800 dark:text-blue-200 mb-4">
                            <i class="bi bi-person-circle mr-2"></i>Cliente
                        </h3>
                        @if($client_id && $selectedClient = $clients->find($client_id))
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Nome:</p>
                                <p class="text-blue-800 dark:text-blue-200 font-semibold text-lg">{{ $selectedClient->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Telefone:</p>
                                <p class="text-blue-800 dark:text-blue-200 font-semibold text-lg">{{ $selectedClient->phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Data da Venda:</p>
                                <p class="text-blue-800 dark:text-blue-200 font-semibold text-lg">{{ \Carbon\Carbon::parse($sale_date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Tipo de Pagamento:</p>
                                <p class="text-blue-800 dark:text-blue-200 font-semibold text-lg">{{ ucfirst(str_replace('_', ' ', $tipo_pagamento)) }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Total Geral -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6 rounded-xl border-l-4 border-indigo-500 mb-6">
                        <h3 class="text-2xl font-bold text-indigo-800 dark:text-indigo-200 mb-2">Total Geral da Venda</h3>
                        @if($tipo_pagamento === 'parcelado' && $parcelas > 1)
                        <p class="text-indigo-700 dark:text-indigo-300 flex items-center mb-2">
                            <i class="bi bi-credit-card mr-2"></i>
                            {{ $parcelas }}x de R$ {{ number_format($this->getTotalAmount() / $parcelas, 2, ',', '.') }}
                        </p>
                        @endif
                        <p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                            R$ {{ number_format($this->getTotalAmount(), 2, ',', '.') }}
                        </p>
                    </div>

                    <!-- Navegação Final -->
                    <div class="mt-auto space-y-4">
                        <button type="button"
                            @click="currentStep = 2"
                            class="w-full inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-arrow-left mr-2"></i>
                            Voltar: Produtos
                        </button>

                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-12 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove class="flex items-center">
                                <i class="bi bi-check-circle mr-2 text-xl"></i>
                                Finalizar Venda
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Criando Venda...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Coluna Direita: Lista de Produtos (3/5 da tela) -->
                <div class="w-3/5 bg-green-50 dark:bg-green-900/20 border-l border-gray-200 dark:border-zinc-700 p-8">
                    <h3 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-6">
                        <i class="bi bi-cart mr-2"></i>Produtos ({{ count($products) }})
                    </h3>

                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-green-100 dark:bg-green-900/30">
                                    <tr>
                                        <th class="text-left py-4 px-6 text-green-700 dark:text-green-300 font-semibold">Produto</th>
                                        <th class="text-center py-4 px-4 text-green-700 dark:text-green-300 font-semibold">Quantidade</th>
                                        <th class="text-right py-4 px-4 text-green-700 dark:text-green-300 font-semibold">Preço Unit.</th>
                                        <th class="text-right py-4 px-6 text-green-700 dark:text-green-300 font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    @if($product['product_id'])
                                    @php
                                    $productData = $availableProducts->find($product['product_id']);
                                    $total = $product['quantity'] * $product['unit_price'];
                                    @endphp
                                    <tr class="border-b border-green-100 dark:border-green-900/50 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors duration-150">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-4">
                                                <img src="{{ $productData->image ? asset('storage/products/' . $productData->image) : asset('storage/products/product-placeholder.png') }}"
                                                    alt="{{ $productData->name ?? 'Produto' }}"
                                                    class="w-14 h-14 rounded-lg object-cover shadow-sm">
                                                <div>
                                                    <p class="font-semibold text-green-800 dark:text-green-200 text-lg">{{ $productData->name ?? 'Produto não encontrado' }}</p>
                                                    <p class="text-sm text-green-600 dark:text-green-400">{{ $productData->product_code ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                {{ $product['quantity'] }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <span class="text-green-800 dark:text-green-200 font-medium text-lg">
                                                R$ {{ number_format($product['unit_price'], 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                                R$ {{ number_format($total, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
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

<!-- CSS customizado para manter consistência visual -->
<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

<!-- CSS para cards de produtos estilosos -->
<style>
    /* Scrollbar customizada para área de produtos */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-track {
        background: #1e293b;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #475569;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    .product-card {
        position: relative;
        background: var(--card-bg, #e6e6fa);
        border: 2px solid var(--card-border, #b39ddb);
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        transform: scale(1);
        box-shadow: 0 4px 8px var(--shadow-card, rgba(149, 117, 205, 0.13));
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 24px var(--shadow-strong, rgba(81, 45, 168, 0.18));
    }

    .product-card.selected {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-color: var(--primary, #9575cd);
        box-shadow: 0 8px 16px var(--shadow-strong, rgba(81, 45, 168, 0.18)), 0 0 0 3px rgba(139, 92, 246, 0.3);
    }

    .product-img-area {
        position: relative;
        padding: 1rem;
        text-align: center;
    }

    .product-img {
        width: 6rem;
        height: 6rem;
        margin: 0 auto 0.75rem auto;
        border-radius: 0.5rem;
        object-fit: cover;
        background: var(--gray-100, #f3f3f7);
    }

    .badge-product-code {
        position: absolute;
        bottom: 0.5rem;
        left: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--blue-light, #ede7f6);
        color: var(--blue-strong, #9575cd);
        box-shadow: 0 2px 4px var(--badge-shadow-1, #9575cd33);
    }

    .badge-quantity {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--green-xlight, #f8bbd0);
        color: var(--green-strong, #ba68c8);
        box-shadow: 0 2px 4px var(--badge-shadow-2, #f8bbd011);
    }

    .card-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-title {
        font-weight: bold;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        color: var(--gray-title, #424242);
        min-height: 2.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge-price {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--gradient-badge-price, linear-gradient(90deg, #f8bbd0 0%, #9575cd 100%));
        color: white;
        box-shadow: 0 2px 4px var(--badge-shadow-3, #ba68c833);
    }

    .badge-price-sale {
        position: absolute;
        top: 0.5rem;
        right: 3rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--gradient-badge-sale, linear-gradient(90deg, #9575cd 0%, #ba68c8 100%));
        color: white;
        box-shadow: 0 2px 4px var(--badge-shadow-4, #f8bbd011);
    }

    /* Ajustes para dark mode */
    .dark .product-card {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .product-card.selected {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-color: #8b5cf6;
    }

    .dark .product-title {
        color: #f9fafb;
    }

    .dark .badge-product-code {
        background: #374151;
        color: #9ca3af;
    }

    .dark .badge-quantity {
        background: #374151;
        color: #9ca3af;
    }
</style>
</div>