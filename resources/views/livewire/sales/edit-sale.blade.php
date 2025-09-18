<div x-data="{ currentStep: 1, completedSteps: [1, 2, 3] }" class="">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <!-- Header Modernizado para Edição -->
    <x-sales-header
        title="Editar Venda #{{ $sale->id }}"
        description="Atualize as informações da venda seguindo os passos"
        :back-route="route('sales.show', $sale->id)"
        :current-step="$currentStep ?? 1"
        :is-edit="true"
        :sale="$sale"
        :steps="[
            [
                'title' => 'Cliente',
                'description' => 'Atualizar cliente',
                'icon' => 'bi-person',
                'gradient' => 'from-indigo-500 to-purple-500',
                'connector_gradient' => 'from-indigo-500 to-purple-500'
            ],
            [
                'title' => 'Produtos',
                'description' => 'Editar produtos',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Resumo',
                'description' => 'Conferir e salvar',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-orange-500 to-red-500'
            ]
        ]" />

    <!-- Conteúdo Principal -->
    <div class="">
        <form wire:submit.prevent="update" class="">
            <div class="">

                <!-- Step 1: Informações do Cliente e Pagamento - Full Width -->
                <div x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="">

                    <div class="px-8 py-8">
                        <!-- Informações do Cliente - Full Width, sem card -->
                        <div class=" p-8">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                                <i class="bi bi-pencil-square text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                Editar Informações da Venda
                            </h2>

                            <!-- Informações da Venda Original -->
                            <div class="mb-8 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border-l-4 border-blue-500">
                                <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200 mb-4">
                                    <i class="bi bi-info-circle mr-2"></i>Informações Originais
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">Criada em:</span>
                                        <p class="text-blue-800 dark:text-blue-200">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">Status:</span>
                                        <p class="text-blue-800 dark:text-blue-200">{{ ucfirst($sale->status) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">Valor Original:</span>
                                        <p class="text-blue-800 dark:text-blue-200">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">Última Atualização:</span>
                                        <p class="text-blue-800 dark:text-blue-200">{{ $sale->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Cliente -->
                                <div class="md:col-span-2">
                                    <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="bi bi-person-circle text-indigo-500 mr-2"></i>Cliente *
                                    </label>
                                    <!-- Dropdown customizado -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button type="button"
                                                @click="open = !open"
                                                class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg text-left @error('client_id') border-red-300 @enderror">
                                            <span class="flex items-center justify-between">
                                                <span>
                                                    @if($client_id && $selectedClient = $clients->find($client_id))
                                                        {{ $selectedClient->name }}
                                                    @else
                                                        Selecione um cliente...
                                                    @endif
                                                </span>
                                                <i class="bi bi-chevron-down transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                            </span>
                                        </button>

                                        <div x-show="open"
                                             @click.away="open = false"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                            @foreach($clients as $client)
                                                <button type="button"
                                                        wire:click="$set('client_id', {{ $client->id }})"
                                                        @click="open = false"
                                                        class="w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-zinc-600 transition-colors duration-200 {{ $client_id == $client->id ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-900 dark:text-white' }}">
                                                    <div class="flex items-center">
                                                        <i class="bi bi-person mr-2"></i>
                                                        <span>{{ $client->name }}</span>
                                                        @if($client_id == $client->id)
                                                            <i class="bi bi-check ml-auto text-indigo-500"></i>
                                                        @endif
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    @error('client_id')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="bi bi-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>

                                <!-- Tipo de Pagamento -->
                                <div>
                                    <label for="tipo_pagamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="bi bi-credit-card text-indigo-500 mr-2"></i>Tipo de Pagamento *
                                    </label>
                                    <select wire:model="tipo_pagamento"
                                            wire:change="$refresh"
                                            id="tipo_pagamento"
                                            class="w-full px-4 py-4 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-lg @error('tipo_pagamento') border-red-300 @enderror">
                                        <option value="a_vista">💳 À Vista</option>
                                        <option value="parcelado">📅 Parcelado</option>
                                    </select>
                                    @error('tipo_pagamento')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="bi bi-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>

                                <!-- Número de Parcelas (condicional) -->
                                @if($tipo_pagamento == 'parcelado')
                                <div>
                                    <label for="parcelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="bi bi-calendar-range text-indigo-500 mr-2"></i>Número de Parcelas *
                                    </label>
                                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                                        @for($i = 1; $i <= 12; $i++)
                                        <button type="button"
                                                wire:click="$set('parcelas', {{ $i }})"
                                                class="p-3 text-center border rounded-lg transition-all duration-200 {{ $parcelas == $i ? 'bg-indigo-500 text-white border-indigo-500' : 'bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-zinc-600 hover:border-indigo-300' }}">
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
                                </div>
                                @endif
                            </div>

                            <!-- Botão Próximo Moderno -->
                            <div class="w-full flex justify-end mt-8 pt-6 border-t border-gray-200 dark:border-zinc-600">
                                <button type="button"
                                    @click="currentStep = 2"
                                    class="group relative inline-flex items-center justify-center px-12 py-4 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-600 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-indigo-300 backdrop-blur-sm">
                                    <span class="flex items-center">
                                        Próximo: Produtos
                                        <i class="bi bi-arrow-right ml-2 group-hover:scale-110 transition-transform duration-200"></i>
                                    </span>
                                    <!-- Efeito hover ring -->
                                    <div class="absolute inset-0 rounded-2xl bg-indigo-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
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
                        <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                                <i class="bi bi-box text-purple-600 dark:text-purple-400 mr-3"></i>
                                Editar Produtos da Venda
                            </h2>

                            <!-- Controles de pesquisa e filtro -->
                            <div class="mt-6 flex flex-col md:flex-row gap-4">
                                <!-- Campo de pesquisa -->
                                <div class="flex-1">
                                    <div class="relative">
                                        <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                        <input type="text"
                                               wire:model.live="searchTerm"
                                               placeholder="Buscar produtos por nome, código ou categoria..."
                                               class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>

                                <!-- Toggle para mostrar apenas selecionados -->
                                <div class="flex items-center">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               wire:model.live="showOnlySelected"
                                               class="sr-only">
                                        <div class="relative">
                                            <div class="w-12 h-6 bg-gray-200 dark:bg-zinc-600 rounded-full shadow-inner {{ $showOnlySelected ? 'bg-indigo-600' : '' }}"></div>
                                            <div class="absolute inset-y-0 left-0 w-6 h-6 bg-white rounded-full shadow transform transition-transform duration-200 {{ $showOnlySelected ? 'translate-x-6' : '' }}"></div>
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Apenas Selecionados</span>
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
                                    <i class="bi bi-box text-8xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    @if($searchTerm)
                                        Nenhum produto encontrado para "{{ $searchTerm }}"
                                    @elseif($showOnlySelected)
                                        Nenhum produto selecionado ainda
                                    @else
                                        Nenhum produto disponível
                                    @endif
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">
                                    @if($searchTerm)
                                        Tente buscar por outro termo ou limpe o campo de pesquisa.
                                    @elseif($showOnlySelected)
                                        Selecione produtos da lista para editá-los aqui.
                                    @else
                                        Adicione produtos ao sistema para poder incluí-los nas vendas.
                                    @endif
                                </p>
                            </div>
                            @else
                            <!-- Grid de Cards de Produtos usando o mesmo estilo da página de produtos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                @foreach($this->getFilteredProducts() as $product)
                                @php
                                    $isSelected = collect($selectedProducts)->contains(function($selected) use ($product) {
                                        return $selected['product_id'] == $product->id;
                                    });
                                @endphp

                                <!-- Produto com CSS customizado mantido -->
                                <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                     wire:click="toggleProduct({{ $product->id }})"
                                     style="cursor: pointer;">

                                    <!-- Área da imagem com badges -->
                                    <div class="product-img-area">
                                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                             alt="{{ $product->name }}"
                                             class="product-img">

                                        <!-- Badge da categoria -->
                                        @if($product->category)
                                        <span class="badge-category">
                                            {{ $product->category->name }}
                                        </span>
                                        @endif

                                        <!-- Badge do código do produto -->
                                        <span class="badge-product-code">
                                            #{{ $product->code }}
                                        </span>

                                        <!-- Badge de selecionado -->
                                        @if($isSelected)
                                        <span class="badge-selected">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </span>
                                        @endif

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
                                            {{ $product->name }}
                                        </div>

                                        <!-- Área dos preços -->
                                        <div class="price-area">
                                            <div class="price-current">
                                                R$ {{ number_format($product->price, 2, ',', '.') }}
                                            </div>
                                            @if($product->original_price && $product->original_price > $product->price)
                                            <div class="price-original">
                                                R$ {{ number_format($product->original_price, 2, ',', '.') }}
                                            </div>
                                            @endif
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
                    <!-- Header do painel direito -->
                    <div class="p-3 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="bi bi-cart text-orange-600 dark:text-orange-400 mr-2 text-sm"></i>
                            Produtos Editados ({{ count($selectedProducts) }})
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
                                A venda atual não possui produtos ou clique nos produtos à esquerda para editá-los
                            </p>
                        </div>
                        @else
                        <div class="p-2 space-y-2">
                            @foreach($selectedProducts as $index => $productItem)
                            @php
                            $selectedProduct = $this->getFilteredProducts()->find($productItem['product_id']);
                            @endphp

                            @if($selectedProduct)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-zinc-700">
                                <!-- Header do produto -->
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate" title="{{ $selectedProduct->name }}">
                                            {{ $selectedProduct->name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $selectedProduct->code }}
                                        </p>
                                    </div>
                                    <button type="button"
                                            wire:click="removeProduct({{ $index }})"
                                            class="text-red-500 hover:text-red-700 ml-2">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </div>

                                <!-- Controles -->
                                <div class="space-y-2">
                                    <!-- Quantidade -->
                                    <div>
                                        <label class="text-xs text-gray-600 dark:text-gray-400">Quantidade</label>
                                        <div class="flex items-center space-x-1 mt-1">
                                            <button type="button"
                                                    wire:click="decrementQuantity({{ $index }})"
                                                    class="w-6 h-6 bg-gray-200 dark:bg-zinc-600 text-gray-600 dark:text-gray-300 rounded text-xs font-bold hover:bg-gray-300 dark:hover:bg-zinc-500 transition-colors">
                                                -
                                            </button>
                                            <input type="number"
                                                   wire:model.blur="selectedProducts.{{ $index }}.quantity"
                                                   min="1"
                                                   class="w-12 h-6 text-center text-xs border border-gray-200 dark:border-zinc-600 rounded bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                                            <button type="button"
                                                    wire:click="incrementQuantity({{ $index }})"
                                                    class="w-6 h-6 bg-gray-200 dark:bg-zinc-600 text-gray-600 dark:text-gray-300 rounded text-xs font-bold hover:bg-gray-300 dark:hover:bg-zinc-500 transition-colors">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Preço Unitário -->
                                    <div>
                                        <label class="text-xs text-gray-600 dark:text-gray-400">Preço Unit.</label>
                                        <input type="text"
                                               wire:model.blur="selectedProducts.{{ $index }}.unit_price"
                                               class="w-full text-xs border border-gray-200 dark:border-zinc-600 rounded px-2 py-1 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white mt-1">
                                    </div>

                                    <!-- Total do item -->
                                    <div class="bg-gray-50 dark:bg-zinc-700 rounded p-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">Total:</span>
                                            <span class="text-xs font-bold text-green-600 dark:text-green-400">
                                                R$ {{ number_format(($productItem['quantity'] ?? 0) * (floatval(str_replace(',', '.', str_replace('.', '', $productItem['unit_price'] ?? '0'))) ?: 0), 2, ',', '.') }}
                                            </span>
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
                        <div class="mb-3 p-3 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg border border-orange-200 dark:border-orange-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-orange-800 dark:text-orange-200">Total:</span>
                                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">
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
                                class="group relative inline-flex items-center justify-center flex-1 px-3 py-2 rounded-xl text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm {{ count($selectedProducts) > 0 ? 'bg-gradient-to-br from-orange-500 via-red-500 to-red-600 hover:from-orange-600 hover:via-red-600 hover:to-red-700 border border-orange-300' : 'bg-gray-400 cursor-not-allowed border border-gray-300' }}">
                                <span class="flex items-center">
                                    Resumo
                                    <i class="bi bi-arrow-right ml-1 group-hover:scale-110 transition-transform duration-200"></i>
                                </span>
                                @if(count($selectedProducts) > 0)
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-orange-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Produtos -->
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <i class="bi bi-box text-indigo-600 dark:text-indigo-400 mr-3"></i>
                                Produtos da Venda
                            </h2>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ count($selectedProducts) }} {{ count($selectedProducts) === 1 ? 'produto' : 'produtos' }}
                                </span>
                                <button type="button"
                                        wire:click="addProduct"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-plus-lg mr-2"></i>
                                    Adicionar Produto
                                </button>
                            </div>
                        </div>

                        @error('selectedProducts')
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 rounded-lg">
                                <div class="flex items-center">
                                    <i class="bi bi-exclamation-triangle text-red-500 mr-2"></i>
                                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                            </div>
                        @enderror

                        <div class="space-y-6">
                            @foreach($selectedProducts as $index => $product)
                                <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-zinc-700 dark:to-zinc-600 rounded-2xl p-6 border-2 border-gray-200 dark:border-zinc-600 hover:shadow-lg transition-all duration-300">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <i class="bi bi-tag text-indigo-500 mr-2"></i>
                                            Produto #{{ $index + 1 }}
                                        </h3>
                                        <button type="button"
                                                wire:click="removeProduct({{ $index }})"
                                                class="inline-flex items-center justify-center w-10 h-10 border-2 border-red-300 dark:border-red-600 text-sm font-medium rounded-xl text-red-700 dark:text-red-400 bg-white dark:bg-zinc-600 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-4 focus:ring-red-500/30 transition-all duration-300 hover:scale-110">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Produto -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-box-seam text-blue-500 mr-2"></i>Produto *
                                            </label>
                                            <select wire:model="selectedProducts.{{ $index }}.product_id"
                                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.product_id') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <option value="">Selecione um produto...</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}">{{ $prod->name }} (Estoque: {{ $prod->stock_quantity }})</option>
                                                @endforeach
                                            </select>
                                            @error('selectedProducts.'.$index.'.product_id')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Quantidade -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-plus-slash-minus text-green-500 mr-2"></i>Quantidade *
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model="selectedProducts.{{ $index }}.quantity"
                                                       min="1"
                                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.quantity') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <i class="bi bi-hash text-gray-400"></i>
                                                </div>
                                            </div>
                                            @error('selectedProducts.'.$index.'.quantity')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Preço de Venda -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <i class="bi bi-currency-dollar text-yellow-500 mr-2"></i>Preço de Venda *
                                            </label>
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model="selectedProducts.{{ $index }}.price_sale"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full px-4 py-3 pl-12 border-2 border-gray-200 dark:border-zinc-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-zinc-600 text-gray-900 dark:text-white shadow-md hover:shadow-lg @error('selectedProducts.'.$index.'.price_sale') !border-red-400 !ring-4 !ring-red-500/20 @enderror">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <div class="absolute inset-y-0 left-8 flex items-center">
                                                    <div class="h-6 border-l border-gray-300 dark:border-zinc-500"></div>
                                                </div>
                                            </div>
                                            @error('selectedProducts.'.$index.'.price_sale')
                                                <div class="flex items-center mt-2 text-xs text-red-600">
                                                    <i class="bi bi-exclamation-triangle mr-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Informações do Produto Selecionado -->
                                    @if($product['product_id'])
                                        @php
                                            $selectedProduct = collect($products)->firstWhere('id', $product['product_id']);
                                        @endphp
                                        @if($selectedProduct)
                                            <div class="mt-6 pt-6 border-t-2 border-gray-200 dark:border-zinc-600">
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Preço Original</p>
                                                                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($selectedProduct->price_sale, 2, ',', '.') }}</p>
                                                            </div>
                                                            <i class="bi bi-tag text-2xl text-blue-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Estoque</p>
                                                                <p class="text-lg font-bold {{ $selectedProduct->stock_quantity <= 10 ? 'text-red-600' : 'text-green-600' }}">
                                                                    {{ $selectedProduct->stock_quantity }}
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-boxes text-2xl {{ $selectedProduct->stock_quantity <= 10 ? 'text-red-500' : 'text-green-500' }}"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Subtotal</p>
                                                                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                                                    R$ {{ number_format(($product['quantity'] ?? 0) * ($product['price_sale'] ?? 0), 2, ',', '.') }}
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-calculator text-2xl text-indigo-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 shadow-md">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">Margem</p>
                                                                @php
                                                                    $margin = $product['price_sale'] ? (($product['price_sale'] - $selectedProduct->price) / $product['price_sale']) * 100 : 0;
                                                                @endphp
                                                                <p class="text-lg font-bold {{ $margin > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                    {{ number_format($margin, 1) }}%
                                                                </p>
                                                            </div>
                                                            <i class="bi bi-graph-up text-2xl {{ $margin > 0 ? 'text-green-500' : 'text-red-500' }}"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach

                            @if(empty($selectedProducts))
                                <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                                    <div class="bg-gray-100 dark:bg-zinc-700 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                                        <i class="bi bi-box text-5xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold mb-2">Nenhum produto adicionado</h3>
                                    <p class="text-sm mb-6">Clique em "Adicionar Produto" para começar a editar os itens da venda.</p>
                                    <button type="button"
                                            wire:click="addProduct"
                                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <i class="bi bi-plus-lg mr-2"></i>
                                        Adicionar Primeiro Produto
                                    </button>
                                </div>
                            @endif
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
                        <i class="bi bi-pencil-square text-orange-600 dark:text-orange-400 mr-2"></i>
                        Resumo da Edição
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
                                        R$ {{ number_format($this->getTotalPrice() / $parcelas, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-2">
                                    <i class="bi bi-info-circle mr-1"></i>
                                    Parcelas mensais de {{ \Carbon\Carbon::parse($sale_date)->format('d/m/Y') }}
                                    até {{ \Carbon\Carbon::parse($sale_date)->addMonths($parcelas - 1)->format('d/m/Y') }}
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
                                {{ $parcelas }}x de R$ {{ number_format($this->getTotalPrice() / $parcelas, 2, ',', '.') }}
                            </p>
                            @endif
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
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
                            class="group relative w-full inline-flex items-center justify-center px-12 py-4 rounded-2xl bg-gradient-to-br from-orange-500 via-red-500 to-red-600 hover:from-orange-600 hover:via-red-600 hover:to-red-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-orange-300 backdrop-blur-sm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove class="flex items-center">
                                <i class="bi bi-pencil-square mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                                Salvar Alterações
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Salvando Alterações...
                            </span>
                            <!-- Efeito hover ring -->
                            <div class="absolute inset-0 rounded-2xl bg-orange-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </div>

                <!-- Coluna Direita: Lista de Produtos (3/5 da tela) -->
                <div class="w-4/5 bg-orange-50 dark:bg-orange-900/20 border-l border-gray-200 dark:border-zinc-700 p-8">
                    <h3 class="text-2xl font-bold text-orange-800 dark:text-orange-200 mb-6">
                        <i class="bi bi-cart mr-2"></i>Produtos Selecionados ({{ count($selectedProducts) }})
                    </h3>

                    <!-- Grid de Cards de Produtos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-118">
                        @foreach($selectedProducts as $index => $product)
                        @if($product['product_id'])
                        @php
                        $productData = collect($products)->firstWhere('id', $product['product_id']);
                        $total = $product['quantity'] * $product['price_sale'];
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
                                        {{ number_format($product['price_sale'], 2, ',', '.') }}
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
                            <span class="text-xl font-bold text-orange-800 dark:text-orange-200">
                                <i class="bi bi-calculator mr-2"></i>
                                Total Geral:
                            </span>
                            <span class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                                R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
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
            </div>
        </form>
    </div>
</div>
