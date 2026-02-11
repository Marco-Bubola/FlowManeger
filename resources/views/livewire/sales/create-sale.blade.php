<div x-data="{ currentStep: 1, completedSteps: [], init() { window.addEventListener('gotoStep', e => { this.currentStep = e.detail; }); } }" x-init="init()" class="">
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
                'title' => 'Produtos',
                'description' => 'Selecione itens e quantidades',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Finalizar',
                'description' => 'Revisar e concluir venda',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-green-500 to-emerald-500'
            ]
        ]">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('sales.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-shopping-cart mr-1"></i>Vendas
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Nova Venda</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <button wire:click="toggleTips" type="button"
                class="p-2 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                <i class="bi bi-lightbulb"></i>
            </button>

            @php
                $canProceed = count($selectedProducts) > 0 && $client_id;
                $tooltip = 'Ir para o resumo da venda';
                if (!$canProceed) {
                    if (empty($client_id) && count($selectedProducts) === 0) {
                        $tooltip = 'Selecione um cliente e adicione produtos para poder continuar.';
                    } elseif (empty($client_id)) {
                        $tooltip = 'Selecione um cliente para poder continuar.';
                    } elseif (count($selectedProducts) === 0) {
                        $tooltip = 'Adicione ao menos um produto para poder continuar.';
                    }
                }
            @endphp

            <button
                type="button"
                @if($canProceed)
                    @click="window.dispatchEvent(new CustomEvent('gotoStep', { detail: 2 }))"
                @endif
                title="{{ $tooltip }}"
                @if(!$canProceed) disabled @endif
                class="
                    group relative inline-flex items-center justify-center px-6 py-2.5 rounded-lg font-semibold tracking-wide text-white transition-all duration-300
                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-900
                    {{ $canProceed
                        ? 'bg-black/20 dark:bg-white/10 backdrop-blur-md border border-white/20 shadow-lg shadow-indigo-500/20 hover:bg-gradient-to-r from-indigo-500 to-purple-600'
                        : 'bg-slate-400/50 dark:bg-slate-700/50 cursor-not-allowed opacity-60'
                    }}
                "
            >
                <span class="flex items-center gap-2">
                    <span class="hidden sm:inline">Ir para Resumo</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </button>
        </x-slot>
    </x-sales-header>

    <!-- Conteúdo Principal -->
    <div class="">
        <form wire:submit.prevent="save" class="">
            <div class="">

                <!-- NOTE: Step 1 (Cliente) foi removido. A seleção de cliente, data e parcelas foi integrada ao painel lateral do Step Produtos (abaixo). -->

                <!-- Step 1: Produtos - Layout Split 3/4 e 1/4 (antiga Step 2) -->
                <div x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full h-[80vh] flex">

                    <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
                    <div class="w-3/4  flex flex-col h-full">
                        <!-- Header com Controles -->
                        <div class="p-2  ">


                            <!-- Controles de pesquisa e filtro -->
                            <div class=" flex flex-col md:flex-row gap-4">
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
                                    <label class="toggle-filter">
                                        <input type="checkbox"
                                               wire:model.live="showOnlySelected"
                                               class="toggle-filter-input">
                                        <span class="toggle-filter-track">
                                            <span class="toggle-filter-thumb"></span>
                                        </span>
                                        <span class="toggle-filter-text text-gray-700 dark:text-gray-300 font-medium">
                                            Selecionados
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de Produtos com Scroll -->
                        <div class="flex-1 p-6 overflow-y-auto min-h-0">
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
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 products-step-grid">
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
                                                        : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-purple-400 dark:hover:border-purple-500' }}">
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

                <!-- Lado Direito: Painel de Resumo & Produtos Selecionados (1/4 da tela) -->
                <div class="w-1/4 flex flex-col h-[80vh]">
                    <!-- Painel de Resumo da Venda Modernizado -->
                    <div class="p-4 ">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <i class="bi bi-receipt text-indigo-500"></i>
                                <span>Resumo da Venda</span>
                            </h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full dark:bg-blue-900 dark:text-blue-300 transition-all duration-300">Orçamento</span>
                        </div>

                        <!-- Grid de Informações 2x2 -->
                        <div class="grid grid-cols-2 gap-3">

                            <!-- Bloco Cliente -->
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                <div class="relative">
                                    <button type="button" class="w-full text-left" @click="open = !open; $nextTick(() => { if (open) $refs.clientSearchSidebar.focus() })">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-person-fill text-blue-500 text-lg"></i>
                                            <div>
                                                <label class="text-[10px] font-medium text-blue-800 dark:text-blue-200">Cliente</label>
                                                <div class="text-sm font-bold text-slate-800 dark:text-slate-100 -mt-1 truncate">
                                                    {{ $this->selectedClient->name ?? 'Selecionar...' }}
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                    <!-- Dropdown -->
                                    <div x-show="open" x-transition @click.away="open = false; $wire.set('clientSearch', '')" class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        <div class="p-2 border-b border-slate-100 dark:border-zinc-700">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400"><i class="bi bi-search"></i></span>
                                                <input x-ref="clientSearchSidebar" type="text" wire:model.live.debounce.250ms="clientSearch" placeholder="Buscar cliente..." class="w-full pl-10 pr-4 py-2 rounded-md border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 text-sm focus:ring-2 focus:ring-indigo-400">
                                            </div>
                                        </div>
                                        <div class="py-1">
                                            @php $filteredClients = $this->filteredClients; @endphp
                                            @if($filteredClients->isEmpty())
                                                <div class="px-4 py-2 text-sm text-slate-500">Nenhum cliente encontrado</div>
                                            @else
                                                @foreach($filteredClients as $client)
                                                    <button type="button" @click="open=false; $wire.set('client_id', {{ $client->id }}).then(() => $wire.set('clientSearch',''))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-zinc-700 flex items-center gap-3 text-sm transition-colors">
                                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm">{{ strtoupper(substr($client->name,0,1)) }}</div>
                                                        <div class="text-slate-700 dark:text-slate-300">{{ $client->name }}</div>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @error('client_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Bloco Data -->
                            <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl shadow-sm">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-calendar-fill text-purple-500 text-lg"></i>
                                    <div>
                                        <label class="text-[10px] font-medium text-purple-800 dark:text-purple-200">Data</label>
                                        <input type="date" wire:model="sale_date" class="p-0 text-sm font-bold text-slate-700 dark:text-slate-200 bg-transparent border-0 focus:ring-0">
                                    </div>
                                </div>
                                @error('sale_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Bloco Pagamento -->
                            <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                <div class="relative h-full">
                                    <button type="button" @click="open = !open" class="w-full h-full text-left">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-credit-card-fill text-green-500 text-lg"></i>
                                            <div>
                                                <label class="text-[10px] font-medium text-green-800 dark:text-green-200">Pagamento</label>
                                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                    <span>{{ $tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         @click.away="open = false"
                                         class="absolute z-10 w-full mt-2 bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1">
                                        <button @click="$wire.set('tipo_pagamento', 'a_vista'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 {{ $tipo_pagamento === 'a_vista' ? 'bg-green-100 dark:bg-green-900/50' : '' }}">
                                            @if($tipo_pagamento === 'a_vista') <i class="bi bi-check-circle-fill text-green-500"></i> @endif
                                            <span>À Vista</span>
                                        </button>
                                        <button @click="$wire.set('tipo_pagamento', 'parcelado'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 {{ $tipo_pagamento === 'parcelado' ? 'bg-green-100 dark:bg-green-900/50' : '' }}">
                                            @if($tipo_pagamento === 'parcelado') <i class="bi bi-check-circle-fill text-green-500"></i> @endif
                                            <span>Parcelado</span>
                                        </button>
                                    </div>
                                </div>
                                @error('tipo_pagamento') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Bloco Parcelas -->
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl shadow-sm">
                                @if($tipo_pagamento == 'parcelado')
                                <div x-data="{ open: false }" x-transition class="h-full">
                                    <div class="relative h-full">
                                        <button type="button" @click="open = !open" class="w-full h-full text-left">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-hash text-amber-500 text-lg"></i>
                                                <div>
                                                    <label class="text-[10px] font-medium text-amber-800 dark:text-amber-200">Parcelas</label>
                                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                        <span>{{ $parcelas }}x</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.away="open = false"
                                             class="absolute z-10 w-full mt-2 bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1 max-h-40 overflow-y-auto">
                                            @for($i = 1; $i <= 12; $i++)
                                                <button @click="$wire.set('parcelas', {{ $i }}); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-amber-100/50 dark:hover:bg-amber-900/20 {{ $parcelas == $i ? 'bg-amber-100 dark:bg-amber-900/50' : '' }}">
                                                    @if($parcelas == $i) <i class="bi bi-check-circle-fill text-amber-500"></i> @endif
                                                    <span>{{ $i }}x</span>
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                    @error('parcelas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                @else
                                <div class="flex items-center gap-2 text-slate-400 h-full">
                                    <i class="bi bi-calendar-range-fill"></i>
                                    <span class="text-xs">Sem parcelas</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Total Geral -->
                        @if(!empty($selectedProducts))
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700/50">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="bi bi-currency-dollar"></i>Valor Total</span>
                                <span class="text-2xl font-bold text-green-500">
                                    R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endif
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
                        <div class="p-4 ">
                            @foreach($products as $index => $productItem)
                                @php
                                    $selectedProduct = $availableProducts->find($productItem['product_id']);
                                @endphp

                                @if($selectedProduct)
                                {{-- Card de produto selecionado com UI/UX moderno --}}
                                <div class="bg-white dark:bg-slate-800 rounded-xl p-3.5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-600 transition-all duration-300 group">
                                    <div class="flex items-center gap-4">
                                        {{-- Imagem --}}
                                        <div class="flex-shrink-0">
                                            <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                                 alt="{{ $selectedProduct->name }}"
                                                 class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                        </div>

                                        {{-- Informações do Produto --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-bold text-slate-800 dark:text-white truncate" title="{{ $selectedProduct->name }}">
                                                    {{ $selectedProduct->name }}
                                                </h4>
                                                <button type="button"
                                                        wire:click="toggleProduct({{ $selectedProduct->id }})"
                                                        class="text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors -mt-1 -mr-1">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>

                                            {{-- Preço de Custo --}}
                                            <div class="flex items-center text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                <i class="bi bi-tag mr-1.5"></i>
                                                <span>Custo: R$ {{ number_format($selectedProduct->price, 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Controles e Preços --}}
                                    <div class="mt-4 flex items-end justify-between">
                                        {{-- Controle de Quantidade --}}
                                        <div class="flex items-center gap-2">
                                             <label for="quantity-{{ $selectedProduct->id }}" class="text-xs text-slate-500 dark:text-gray-400 font-medium">Qtd:</label>
                                             <input type="number" id="quantity-{{ $selectedProduct->id }}"
                                                 wire:change="updateProductQuantity({{ $selectedProduct->id }}, $event.target.value)"
                                                 value="{{ $productItem['quantity'] }}"
                                                 min="1"
                                                 @if(isset($selectedProduct->tipo) && $selectedProduct->tipo === 'simples')
                                                    max="{{ $selectedProduct->stock_quantity }}"
                                                 @endif
                                                 class="w-20 h-8 text-center text-sm border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                                        </div>

                                        {{-- Preço de venda (editável) --}}
                                        <div>
                                            <label for="price-{{ $selectedProduct->id }}" class="text-xs text-slate-500 dark:text-gray-400 font-medium">Preço Venda</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-sm text-slate-400">R$</span>
                                                <input type="number" id="price-{{ $selectedProduct->id }}"
                                                   wire:change="updateProductPrice({{ $selectedProduct->id }}, $event.target.value)"
                                                   value="{{ number_format($productItem['unit_price'], 2, '.', '') }}"
                                                   step="0.01" min="0"
                                                   class="w-28 h-8 font-semibold text-green-600 dark:text-green-400 border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/50 pl-7 pr-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                                            </div>
                                        </div>

                                        {{-- Subtotal --}}
                                        <div class="text-right">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Subtotal</span>
                                            <p class="font-bold text-slate-800 dark:text-white">
                                                R$ {{ number_format($productItem['quantity'] * $productItem['unit_price'], 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>


                    </div>
                </div>
            </div>

            <!-- Step 3: Resumo e Finalização - Layout em duas colunas -->
            <div x-show="currentStep === 2"
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
                            @click="currentStep = 1"
                            class="group relative w-full inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                            <i class="bi bi-arrow-left mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                            Voltar: Produtos
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

<!-- Modal de Dicas (Wizard) - Compacto -->
@if($showTipsModal)
    <div x-data="{
        currentStep: 1,
        totalSteps: 3,
        nextStep() { if (this.currentStep < this.totalSteps) this.currentStep++; },
        prevStep() { if (this.currentStep > 1) this.currentStep--; }
    }" x-show="$wire.showTipsModal" x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

        <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
            class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95">

            <!-- Header -->
            <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-700 px-6 py-5 text-white">
                <button @click="$wire.toggleTips()" class="absolute top-3 right-3 p-2 hover:bg-white/20 rounded-lg">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-white/20 rounded-xl"><i class="bi bi-lightbulb-fill text-xl"></i></div>
                    <div>
                        <h2 class="text-2xl font-bold">Dicas: Criar Venda</h2>
                        <p class="text-indigo-100 text-sm">Processo completo de registro de vendas</p>
                    </div>
                </div>
                <div class="flex gap-1.5">
                    <template x-for="step in totalSteps" :key="step">
                        <div class="flex-1 h-1.5 rounded-full overflow-hidden bg-white/20">
                            <div class="h-full bg-white rounded-full transition-all duration-500" :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Content -->
            <div class="overflow-y-auto max-h-[calc(85vh-200px)] p-6">
                <!-- Step 1: Selecionar Cliente -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl shadow-xl mb-4">
                            <i class="bi bi-person-plus text-4xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Passo 1: Cliente</h3>
                        <p class="text-slate-600 dark:text-slate-300">Selecione ou busque o cliente para esta venda</p>
                    </div>
                    <div class="space-y-4">
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50">
                            <h4 class="font-bold text-slate-800 dark:text-white mb-3 flex items-center gap-2"><i class="bi bi-search text-blue-500"></i>Busca de Cliente</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="bi bi-check-circle-fill text-green-500"></i>Digite o nome do cliente na barra de busca</li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="bi bi-check-circle-fill text-green-500"></i>Use o dropdown para selecionar da lista</li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="bi bi-check-circle-fill text-green-500"></i>Cliente é obrigatório para prosseguir</li>
                            </ul>
                        </div>
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-700">
                            <p class="text-sm text-slate-700 dark:text-slate-300"><i class="bi bi-lightbulb-fill text-amber-500 mr-2"></i><strong>Dica:</strong> Você pode cadastrar novos clientes no menu "Clientes" antes de criar a venda</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Adicionar Produtos -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-4">
                            <i class="bi bi-box-seam text-4xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Passo 2: Produtos</h3>
                        <p class="text-slate-600 dark:text-slate-300">Adicione produtos à venda e defina quantidades</p>
                    </div>
                    <div class="space-y-4">
                        <div class="p-5 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50">
                            <h4 class="font-bold text-slate-800 dark:text-white mb-3"><i class="bi bi-plus-square text-purple-500 mr-2"></i>Adicionando Produtos</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg"><div class="font-semibold text-purple-600 mb-1">1. Buscar</div><p class="text-slate-600 dark:text-slate-400">Use a busca para filtrar produtos</p></div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg"><div class="font-semibold text-blue-600 mb-1">2. Selecionar</div><p class="text-slate-600 dark:text-slate-400">Marque checkbox dos produtos</p></div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg"><div class="font-semibold text-green-600 mb-1">3. Quantidade</div><p class="text-slate-600 dark:text-slate-400">Defina a quantidade desejada</p></div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg"><div class="font-semibold text-orange-600 mb-1">4. Preço</div><p class="text-slate-600 dark:text-slate-400">Ajuste o preço se necessário</p></div>
                            </div>
                        </div>
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-700">
                            <p class="text-sm text-slate-700 dark:text-slate-300"><i class="bi bi-info-circle-fill text-green-500 mr-2"></i>O sistema verifica automaticamente o estoque disponível antes de adicionar</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Resumo e Finalização -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-4">
                            <i class="bi bi-check-circle text-4xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Passo 3: Finalizar</h3>
                        <p class="text-slate-600 dark:text-slate-300">Revise tudo e complete a venda</p>
                    </div>
                    <div class="space-y-4">
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50">
                            <h4 class="font-bold text-slate-800 dark:text-white mb-3"><i class="bi bi-cash text-blue-500 mr-2"></i>Formas de Pagamento</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="bi bi-credit-card text-blue-500"></i><strong>À Vista:</strong> Pagamento imediato</li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="bi bi-calendar-range text-purple-500"></i><strong>Parcelado:</strong> Defina número de parcelas</li>
                            </ul>
                        </div>
                        <div class="p-5 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50">
                            <h4 class="font-bold text-slate-800 dark:text-white mb-3"><i class="bi bi-list-check text-green-500 mr-2"></i>Checklist Final</h4>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg text-sm"><input type="checkbox" class="w-4 h-4 text-green-600 rounded"><span class="text-slate-700 dark:text-slate-300">Cliente selecionado corretamente</span></label>
                                <label class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg text-sm"><input type="checkbox" class="w-4 h-4 text-green-600 rounded"><span class="text-slate-700 dark:text-slate-300">Produtos e quantidades conferidos</span></label>
                                <label class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg text-sm"><input type="checkbox" class="w-4 h-4 text-green-600 rounded"><span class="text-slate-700 dark:text-slate-300">Forma de pagamento definida</span></label>
                                <label class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg text-sm"><input type="checkbox" class="w-4 h-4 text-green-600 rounded"><span class="text-slate-700 dark:text-slate-300">Valores estão corretos</span></label>
                            </div>
                        </div>
                        <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-xl border-2 border-amber-300">
                            <div class="flex items-center gap-3"><i class="bi bi-emoji-smile-fill text-2xl text-amber-500"></i><div><h4 class="text-base font-bold text-slate-800 dark:text-white">Tudo Pronto!</h4><p class="text-sm text-slate-600 dark:text-slate-300">Clique em "Finalizar Venda" para concluir</p></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <button @click="prevStep()" x-show="currentStep > 1" class="flex items-center gap-2 px-5 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all hover:scale-105"><i class="bi bi-arrow-left"></i>Anterior</button>
                    <div x-show="currentStep === 1"></div>
                    <div class="flex items-center gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step" class="transition-all duration-300 rounded-full" :class="currentStep === step ? 'w-8 h-3 bg-gradient-to-r from-indigo-600 to-purple-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400'"></button>
                        </template>
                    </div>
                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()" class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg transition-all hover:scale-105"><span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'"></span><i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i></button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
