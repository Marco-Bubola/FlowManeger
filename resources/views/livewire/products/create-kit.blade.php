<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-blue-900/20 dark:to-indigo-900/20">
    <!-- Header com Steppers Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50">
        <!-- Efeito de brilho sutil -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

        <div class="relative px-6 py-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-6">
                    <!-- Botão voltar melhorado -->
                    <a href="{{ route('products.index') }}"
                        class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                        <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200"></i>
                        <!-- Efeito hover ring -->
                        <div class="absolute inset-0 rounded-2xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>

                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-purple-700 to-indigo-700 dark:from-slate-100 dark:via-purple-300 dark:to-indigo-300 bg-clip-text text-transparent flex items-center">
                            <!-- Ícone animado -->
                            <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-400 via-purple-500 to-indigo-500 rounded-2xl mr-4 shadow-xl shadow-purple-500/25">
                                <i class="bi bi-boxes text-white text-xl animate-pulse"></i>
                                <!-- Efeito de brilho -->
                                <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                            </div>
                            Criar Novo Kit
                        </h1>
                        <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">
                            📦 Combine produtos para criar kits personalizados e otimizar suas vendas
                        </p>
                    </div>
                </div>

                <!-- Steppers usando componente -->
                <x-stepper
                    :steps="[
                        [
                            'title' => 'Informações do Kit',
                            'description' => 'Nome, preços e categoria',
                            'icon' => 'bi-info-circle'
                        ],
                        [
                            'title' => 'Produtos do Kit',
                            'description' => 'Selecione os produtos',
                            'icon' => 'bi-collection'
                        ],
                        [
                            'title' => 'Imagem do Kit',
                            'description' => 'Upload da foto do kit',
                            'icon' => 'bi-image'
                        ]
                    ]"
                    :current-step="$currentStep"
                    :show-step-numbers="false"
                />
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal Moderno -->
    <div class="relative flex-1 overflow-y-auto">
        <div class="px-8 py-6 space-y-6 h-full flex flex-col">

            @if($currentStep == 1)
                <!-- ETAPA 1: Informações do Kit -->
                <form wire:submit.prevent="nextStep" class="flex-1 space-y-6 animate-fadeIn">
                    <!-- Card Container Principal -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50">
                        <!-- Seção Nome e Preços -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Nome do Kit -->
                            <div class="group space-y-4">
                                <label for="name" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-boxes text-white"></i>
                                    </div>
                                    Nome do Kit *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-collection text-slate-400 group-hover:text-purple-500 transition-colors duration-300"></i>
                                    </div>
                                    <input type="text"
                                        wire:model="name"
                                        id="name"
                                        class="w-full pl-14 pr-14 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                        focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                        placeholder="Ex: Kit Notebook + Mouse + Teclado">
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                        @if($name && !$errors->has('name'))
                                            <div class="flex items-center justify-center w-6 h-6 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse">
                                                <i class="bi bi-check text-white text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @error('name')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Categoria -->
                            <div class="group space-y-4">
                                <label for="category_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-tags-fill text-white"></i>
                                    </div>
                                    Categoria do Kit *
                                </label>

                                <!-- Dropdown Customizado -->
                                <div class="relative" x-data="{
                                    open: false,
                                    selectedCategory: @entangle('category_id'),
                                    selectedCategoryName: '{{ $selectedCategoryName ?? 'Escolha uma categoria...' }}',
                                    selectedCategoryIcon: '{{ $selectedCategoryIcon ?? 'bi-grid-3x3-gap-fill' }}',
                                    selectCategory(category) {
                                        this.selectedCategory = category.id;
                                        this.selectedCategoryName = category.name;
                                        this.selectedCategoryIcon = category.icon;
                                        this.open = false;
                                        $wire.set('category_id', category.id);
                                    }
                                }">
                                    <button type="button"
                                            @click="open = !open"
                                            class="w-full flex items-center justify-between pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100
                                            {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl group-hover:scale-[1.02]">
                                        <div class="flex items-center">
                                            <div class="absolute left-4">
                                                <i :class="selectedCategoryIcon" class="text-slate-400 group-hover:text-purple-500 transition-colors duration-300"></i>
                                            </div>
                                            <span x-text="selectedCategoryName" class="text-left font-medium"></span>
                                        </div>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                         @click.away="open = false"
                                         class="absolute z-50 w-full mt-2 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-slate-200 dark:border-slate-600 rounded-2xl shadow-2xl max-h-60 overflow-y-auto">

                                        @foreach($categories as $category)
                                        <button type="button"
                                                @click="selectCategory({ id: {{ $category->id_category }}, name: '{{ $category->name }}', icon: '{{ $this->getCategoryIcon($category->icone) }}' })"
                                                class="w-full flex items-center px-6 py-4 text-left hover:bg-purple-50/80 dark:hover:bg-purple-900/30 transition-all duration-200 border-b border-slate-100 dark:border-slate-700 last:border-b-0 hover:scale-[1.02] first:rounded-t-2xl last:rounded-b-2xl">
                                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg mr-3">
                                                <i class="{{ $this->getCategoryIcon($category->icone) }} text-white text-sm"></i>
                                            </div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $category->name }}</span>
                                        </button>
                                        @endforeach
                                    </div>
                                </div>

                                @error('category_id')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Código do Kit -->
                            <div class="group space-y-4">
                                <label for="product_code" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-upc-scan text-white"></i>
                                    </div>
                                    Código do Kit *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-hash text-slate-400 group-hover:text-indigo-500 transition-colors duration-300"></i>
                                    </div>
                                    <input type="text"
                                        wire:model="product_code"
                                        id="product_code"
                                        class="w-full pl-14 pr-14 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('product_code') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                        focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                        placeholder="Ex: KIT-001">
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                        @if($product_code && !$errors->has('product_code'))
                                            <div class="flex items-center justify-center w-6 h-6 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse">
                                                <i class="bi bi-check text-white text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @error('product_code')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Descrição em linha separada -->
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <div class="group space-y-4">
                                <label for="description" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-card-text text-white"></i>
                                    </div>
                                    Descrição do Kit
                                </label>
                                <div class="relative">
                                    <div class="absolute top-4 left-4 pointer-events-none">
                                        <i class="bi bi-text-paragraph text-slate-400 group-hover:text-indigo-500 transition-colors duration-300"></i>
                                    </div>
                                    <textarea wire:model="description"
                                        id="description"
                                        rows="4"
                                        class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 resize-none
                                        {{ $errors->has('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                        focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                        placeholder="Descreva o kit e seus benefícios..."></textarea>
                                </div>
                                @error('description')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            @if($currentStep == 2)
                <!-- ETAPA 2: Seleção de Produtos e Custos -->
                <form wire:submit.prevent="nextStep" class="flex-1 space-y-6 animate-fadeIn">
                    <!-- Incluir CSS dos produtos -->
                    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

                    <!-- Componente Modernizado de Seleção de Produtos -->
                    <x-modern-product-selector
                        :products="$filteredProducts"
                        :categories="$categories"
                        :selectedProducts="$selectedProducts"
                        searchTerm="searchTerm"
                        selectedCategory="selectedCategory"
                        title="Produtos do Kit"
                        emptyMessage="Nenhum produto disponível para o kit"
                        :showQuantityInput="true"
                        :showPriceInput="true"
                        wireModel="selectedProducts"
                        wire:key="product-selector-step-{{ $currentStep }}"
                    />

                    <!-- Custos Adicionais -->
                    <div class="mt-8 p-6 bg-gradient-to-r from-slate-50/50 to-blue-50/50 dark:from-slate-800/50 dark:to-blue-900/20 rounded-2xl border border-slate-200/50 dark:border-slate-700/50">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-6 flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg mr-3">
                                <i class="bi bi-plus-circle text-white"></i>
                            </div>
                            Custos Adicionais
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Adicione custos extras como embalagem, prendedores, laços, etc.
                        </p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label for="additional_costs" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-6 h-6 bg-gradient-to-br from-amber-400 to-orange-500 rounded mr-2">
                                            <i class="bi bi-plus-circle text-white text-xs"></i>
                                        </div>
                                        Valor dos Custos Adicionais
                                    </div>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-500 dark:text-slate-400">R$</span>
                                    </div>
                                    <input
                                        type="text"
                                        name="additional_costs"
                                        id="additional_costs"
                                        wire:model.live="additional_costs"
                                        class="w-full pl-10 pr-4 py-3 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('additional_costs') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-amber-500 focus:ring-amber-500/20 hover:border-amber-300' }}
                                        focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                        placeholder="0,00"
                                    />
                                </div>
                                @error('additional_costs')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="additional_costs_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Descrição dos Custos (opcional)
                                </label>
                                <input
                                    type="text"
                                    id="additional_costs_description"
                                    wire:model="additional_costs_description"
                                    placeholder="ex: embalagem, laços, prendedores..."
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200"
                                />
                            </div>
                        </div>

                        <!-- Resumo de Custos e Preço Sugerido -->
                        <div class="mt-6 space-y-6">
                            <!-- Resumos lado a lado -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Resumo de Custos do Kit -->
                                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                    <h4 class="text-md font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center">
                                        <i class="bi bi-calculator text-blue-600 mr-2"></i>
                                        Resumo de Custos do Kit
                                    </h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Custo dos produtos:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                R$ {{ number_format(collect($selectedProducts)->sum(function($product) {
                                                    return ($product['price'] ?? 0) * ($product['quantity'] ?? 1);
                                                }), 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Custos adicionais:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                R$ {{ number_format((float)str_replace(',', '.', $additional_costs ?: '0'), 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <hr class="border-slate-300 dark:border-slate-600">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span class="text-slate-800 dark:text-slate-200">Custo Total:</span>
                                            <span class="text-blue-600 dark:text-blue-400">
                                                R$ {{ number_format($calculated_cost_price, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preço de Venda Sugerido -->
                                <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
                                    <h4 class="text-md font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center">
                                        <i class="bi bi-currency-dollar text-green-600 mr-2"></i>
                                        Preço de Venda Sugerido (Margem 5%)
                                    </h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Preço de venda dos produtos:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                R$ {{ number_format(collect($selectedProducts)->sum(function($product) {
                                                    return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1);
                                                }), 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Custos adicionais:</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                R$ {{ number_format((float)str_replace(',', '.', $additional_costs ?: '0'), 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Margem de lucro (5%):</span>
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                R$ {{ number_format($calculated_sale_price * 0.05, 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <hr class="border-slate-300 dark:border-slate-600">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span class="text-slate-800 dark:text-slate-200">Preço Sugerido:</span>
                                            <span class="text-emerald-600 dark:text-emerald-400">
                                                R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo de Preço de Venda Real -->
                            <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                                <h4 class="text-md font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center">
                                    <i class="bi bi-tag text-purple-600 mr-2"></i>
                                    Preço de Venda Real do Kit
                                </h4>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-slate-500 dark:text-slate-400">R$</span>
                                            </div>
                                            <input
                                                type="text"
                                                wire:model.live="real_sale_price"
                                                placeholder="{{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}"
                                                class="w-full pl-10 pr-4 py-3 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                                border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300
                                                focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                            />
                                        </div>
                                    </div>
                                    <button type="button"
                                            wire:click="usesSuggestedPrice"
                                            class="px-4 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all duration-200 font-medium shadow-lg hover:shadow-xl">
                                        <i class="bi bi-arrow-down mr-1"></i>
                                        Usar Sugerido
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                    Este será o preço final de venda do kit. Você pode usar o preço sugerido ou definir um valor personalizado.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            @endif            @if($currentStep == 3)
                <!-- ETAPA 3: Upload de Imagem do Kit -->
                <form wire:submit.prevent="store" class="flex-1 flex items-center justify-center animate-fadeIn">
                    <div class="w-full max-w-3xl">
                        <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50">
                            <!-- Título da seção -->
                            <div class="text-center mb-6">
                                <h2 class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-purple-600 to-indigo-600 dark:from-slate-200 dark:via-purple-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
                                    📸 Imagem do Kit
                                </h2>
                                <p class="text-lg text-slate-600 dark:text-slate-400">
                                    Adicione uma imagem que represente seu kit da melhor forma
                                </p>
                            </div>

                            <!-- Componente de Upload -->
                            <x-image-upload
                                name="image"
                                id="image"
                                wire-model="image"
                                title="Adicionar Imagem do Kit"
                                description="Clique para selecionar ou arraste e solte sua imagem aqui"
                                :new-image="$image"
                                height="h-96"
                            />
                        </div>
                    </div>
                </form>
            @endif

            <!-- Botões de Ação Modernizados -->
            <x-action-buttons-new
                :show-back="$currentStep > 1"
                :show-next="$currentStep < 3"
                :show-save="$currentStep == 3"
                :show-cancel="true"
                back-action="previousStep"
                next-action="nextStep"
                save-action="store"
                :cancel-route="route('products.index')"
                save-text="Criar Kit"
                loading-text="Criando kit..."
            />
        </div>
    </div>

    <!-- Estilos Customizados para Animações -->
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out forwards;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.6s ease-out forwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        /* Efeitos de hover personalizados */
        .group:hover .group-hover\:animate-glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }

        /* Transições suaves entre etapas */
        .step-transition {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Efeito de glassmorphism melhorado */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Gradientes animados */
        .gradient-animate {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>

    <!-- Script para sincronizar totais com custos adicionais -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Listener para mudanças no campo de custos adicionais
            const additionalCostsInput = document.getElementById('additional_costs');
            if (additionalCostsInput) {
                additionalCostsInput.addEventListener('input', () => {
                    setTimeout(() => {
                        // Trigger recalculation no Alpine.js component
                        const alpineComponent = document.querySelector('[x-data*="selectedProducts"]');
                        if (alpineComponent && Alpine.$data(alpineComponent) && Alpine.$data(alpineComponent).calculateKitTotal) {
                            Alpine.$data(alpineComponent).calculateKitTotal();
                        }
                    }, 100);
                });
            }
        });
    </script>
</div>

