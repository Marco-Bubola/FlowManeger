<div class="">
    <!-- Header Modernizado com botões de ação -->
    <x-sales-header
        title="Criar Novo Produto"
        description="Adicione um novo produto ao seu catálogo"
        :back-route="route('products.index')"
        :current-step="1"
        :steps="[]">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-box mr-1"></i>Produtos
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Novo Produto</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <button wire:click="toggleTips" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200">
                <i class="bi bi-lightbulb text-lg"></i>
                Dicas
            </button>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-200 font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                <i class="bi bi-x-lg text-lg"></i>
                Cancelar
            </a>
            <button type="submit" form="create-product-form"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 hover:scale-105 transition-all duration-200">
                <span wire:loading.remove wire:target="store">
                    <i class="bi bi-check-circle-fill text-lg"></i>
                    Criar Produto
                </span>
                <span wire:loading wire:target="store" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Criando...
                </span>
            </button>
        </x-slot>
    </x-sales-header>

    <!-- Conteúdo Principal -->
    <form id="create-product-form" wire:submit.prevent="store" class="px-6 py-6">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- ========== COLUNA ESQUERDA: Formulário ========== -->
            <div class="flex-1">
                <div class="bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50">

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-info-circle-fill text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Informações Básicas</h3>
                                <p class="text-xs text-slate-400">Dados essenciais do produto</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Nome -->
                            <div class="space-y-2">
                                <label for="name" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-tag-fill text-blue-400"></i>
                                    Nome do Produto <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live="name"
                                           id="name"
                                           class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                           {{ $errors->has('name') ? 'border-red-500 focus:border-red-400' : 'focus:border-blue-500 hover:border-slate-600' }}
                                           focus:ring-4 focus:ring-blue-500/20 focus:outline-none"
                                           placeholder="Ex: Notebook Dell">
                                    @if($name && !$errors->has('name'))
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <i class="bi bi-check-circle-fill text-emerald-400 text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                                @error('name')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Código -->
                            <div class="space-y-2">
                                <label for="product_code" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-upc-scan text-purple-400"></i>
                                    Código <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live="product_code"
                                           id="product_code"
                                           class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                           {{ $errors->has('product_code') ? 'border-red-500 focus:border-red-400' : 'focus:border-purple-500 hover:border-slate-600' }}
                                           focus:ring-4 focus:ring-purple-500/20 focus:outline-none"
                                           placeholder="Ex: NB-DELL-001">
                                    @if($product_code && !$errors->has('product_code'))
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <i class="bi bi-check-circle-fill text-emerald-400 text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                                @error('product_code')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Categoria -->
                            <div class="space-y-2">
                                <label for="category_id" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-tags-fill text-pink-400"></i>
                                    Categoria <span class="text-red-400">*</span>
                                </label>
                                <div class="relative" x-data="{
                                    open: false,
                                    selectedCategory: @entangle('category_id'),
                                    selectedCategoryName: '{{ $selectedCategoryName ?? 'Selecione...' }}',
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
                                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                            {{ $errors->has('category_id') ? 'border-red-500' : 'hover:border-pink-500 focus:border-pink-500' }}
                                            focus:ring-4 focus:ring-pink-500/20 focus:outline-none">
                                        <span class="flex items-center gap-2">
                                            <i :class="selectedCategoryIcon" class="text-pink-400"></i>
                                            <span x-text="selectedCategoryName"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open"
                                         x-transition
                                         @click.away="open = false"
                                         class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                        @foreach($categories as $category)
                                            <button type="button"
                                                    @click="selectCategory({ id: {{ $category->id_category }}, name: '{{ $category->name }}', icon: '{{ $this->getCategoryIcon($category->icone) }}' })"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                                <i class="{{ $this->getCategoryIcon($category->icone) }} text-pink-400"></i>
                                                <span class="text-white text-sm font-medium">{{ $category->name }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('category_id')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="border-t border-slate-700/50 my-6"></div>

                    <!-- Descrição -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-card-text text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Descrição</h3>
                                <p class="text-xs text-slate-400">Detalhes e características</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <textarea wire:model.live="description"
                                      id="description"
                                      rows="4"
                                      class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium resize-none transition-all duration-200
                                      {{ $errors->has('description') ? 'border-red-500 focus:border-red-400' : 'focus:border-indigo-500 hover:border-slate-600' }}
                                      focus:ring-4 focus:ring-indigo-500/20 focus:outline-none"
                                      placeholder="Descreva as principais características e benefícios do produto..."></textarea>
                            @error('description')
                                <p class="text-red-400 text-xs flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="border-t border-slate-700/50 my-6"></div>

                    <!-- Preços e Estoque -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-currency-dollar text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Preços e Estoque</h3>
                                <p class="text-xs text-slate-400">Valores e quantidade</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <x-currency-input
                                name="price"
                                id="price"
                                wireModel="price"
                                label="Preço de Custo"
                                icon="bi-tag-fill"
                                icon-color="orange"
                                :required="true"
                                width="w-full"
                            />

                            <x-currency-input
                                name="price_sale"
                                id="price_sale"
                                wireModel="price_sale"
                                label="Preço de Venda"
                                icon="bi-currency-dollar"
                                icon-color="green"
                                :required="true"
                                width="w-full"
                            />

                            <x-quantity-input
                                name="stock_quantity"
                                id="stock_quantity"
                                wireModel="stock_quantity"
                                :min="0"
                                :max="99999"
                                label="Qtd. Estoque"
                                icon="bi-boxes"
                                icon-color="cyan"
                                :required="true"
                                width="w-full"
                            />
                        </div>
                    </div>

                    <!-- Informações Mercado Livre -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-shop text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Mercado Livre (Opcional)</h3>
                                <p class="text-xs text-slate-400">Informações para integração com marketplace</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <!-- Código de Barras -->
                            <div class="space-y-2">
                                <label for="barcode" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-upc text-yellow-400"></i>
                                    Código de Barras (EAN)
                                </label>
                                <input type="text"
                                       wire:model.live="barcode"
                                       id="barcode"
                                       maxlength="15"
                                       class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                       focus:border-yellow-500 hover:border-slate-600 focus:ring-4 focus:ring-yellow-500/20 focus:outline-none"
                                       placeholder="Ex: 7891234567890">
                                @error('barcode')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Marca -->
                            <div class="space-y-2">
                                <label for="brand" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-award text-yellow-400"></i>
                                    Marca
                                </label>
                                <input type="text"
                                       wire:model.live="brand"
                                       id="brand"
                                       maxlength="100"
                                       class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                       focus:border-yellow-500 hover:border-slate-600 focus:ring-4 focus:ring-yellow-500/20 focus:outline-none"
                                       placeholder="Ex: Samsung, Apple, Dell">
                                @error('brand')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Modelo -->
                            <div class="space-y-2">
                                <label for="model" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-diagram-3 text-yellow-400"></i>
                                    Modelo
                                </label>
                                <input type="text"
                                       wire:model.live="model"
                                       id="model"
                                       maxlength="100"
                                       class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                       focus:border-yellow-500 hover:border-slate-600 focus:ring-4 focus:ring-yellow-500/20 focus:outline-none"
                                       placeholder="Ex: Galaxy S23, iPhone 15">
                                @error('model')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Garantia -->
                            <div class="space-y-2">
                                <label for="warranty_months" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-shield-check text-yellow-400"></i>
                                    Garantia (meses)
                                </label>
                                <input type="number"
                                       wire:model.live="warranty_months"
                                       id="warranty_months"
                                       min="0"
                                       max="120"
                                       class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                       focus:border-yellow-500 hover:border-slate-600 focus:ring-4 focus:ring-yellow-500/20 focus:outline-none"
                                       placeholder="3">
                                @error('warranty_months')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Condição -->
                            <div class="space-y-2">
                                <label for="condition" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-star text-yellow-400"></i>
                                    Condição
                                </label>
                                <select wire:model.live="condition"
                                        id="condition"
                                        class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                        focus:border-yellow-500 hover:border-slate-600 focus:ring-4 focus:ring-yellow-500/20 focus:outline-none">
                                    <option value="new">Novo</option>
                                    <option value="used">Usado</option>
                                    <option value="refurbished">Recondicionado</option>
                                </select>
                                @error('condition')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex items-start gap-2 text-xs text-slate-400 bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3">
                            <i class="bi bi-info-circle text-yellow-400 mt-0.5"></i>
                            <p>Estes campos são opcionais agora, mas serão necessários ao publicar no Mercado Livre.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== COLUNA DIREITA: Upload ========== -->
            <div class="w-full xl:w-[450px]">
                <div class="bg-gradient-to-br from-slate-900/95 via-purple-900/20 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-image-fill text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Imagem do Produto</h3>
                            <p class="text-xs text-slate-400">Adicione uma foto de alta qualidade</p>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <x-image-upload
                            name="image"
                            id="image"
                            wire-model="image"
                            title="Upload da Imagem"
                            description="Clique ou arraste sua imagem aqui"
                            :existing-image="$image"
                            height="h-[500px]"
                        />
                    </div>

                    <div class="mt-4 flex items-start gap-2 text-xs text-slate-400">
                        <i class="bi bi-info-circle text-blue-400 mt-0.5"></i>
                        <p>JPG, PNG, JPEG • Máx 2MB • Recomendado: 800x800px</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- Modal de Dicas (Wizard) -->
    @if($showTipsModal)
        <div x-data="{
            currentStep: 1,
            totalSteps: 5,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }" x-show="$wire.showTipsModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

            <!-- Modal Content -->
            <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
                class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Header with Progress Bar -->
                <div class="relative bg-gradient-to-br from-green-600 via-emerald-600 to-teal-700 px-8 py-6 text-white">
                    <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>

                    <div class="pr-12">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="bi bi-lightbulb-fill text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold">Dicas: Criar Produto</h2>
                                <p class="text-green-100 text-sm mt-1">Aprenda a cadastrar produtos corretamente</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex gap-2 mt-6">
                            <template x-for="step in totalSteps" :key="step">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                    <div class="h-full bg-white rounded-full transition-all duration-500"
                                         :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8">
                    <!-- Step 1: Campos Obrigatórios -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-asterisk text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Campos Obrigatórios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Informações essenciais para cadastrar um produto</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-tag text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Nome do Produto *</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Nome claro e descritivo do produto (obrigatório)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-upc-scan text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Código do Produto *</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">SKU, código de barras ou código interno único</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-cash text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Preço de Venda *</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Preço pelo qual o produto será vendido</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-orange-500 rounded-xl">
                                        <i class="bi bi-box-seam text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Quantidade em Estoque *</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Quantidade disponível para venda</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-pink-50 to-rose-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-pink-200/50 dark:border-slate-500/50 md:col-span-2">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-pink-500 rounded-xl">
                                        <i class="bi bi-folder text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Categoria *</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Selecione a categoria apropriada para organizar seu produto no catálogo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Campos Opcionais -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-plus-circle text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Campos Opcionais</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Informações adicionais para enriquecer seu produto</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-image text-2xl text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Imagem do Produto</h4>
                                        <p class="text-slate-700 dark:text-slate-300 mb-3">Adicione uma imagem para facilitar a identificação visual</p>
                                        <ul class="space-y-2">
                                            <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                                Formatos aceitos: JPG, PNG, JPEG, GIF, WEBP
                                            </li>
                                            <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                                Tamanho máximo: 2MB
                                            </li>
                                            <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                                Resolução recomendada: 800x800px
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-text-paragraph text-2xl text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Descrição</h4>
                                        <p class="text-slate-700 dark:text-slate-300 mb-3">Adicione detalhes, características ou informações relevantes</p>
                                        <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                            <p class="text-sm text-slate-600 dark:text-slate-400 italic">💡 Dica: Uma boa descrição ajuda na busca e na identificação do produto</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-cash-coin text-2xl text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Preço de Custo</h4>
                                        <p class="text-slate-700 dark:text-slate-300 mb-3">Quanto você pagou pelo produto (útil para calcular margem de lucro)</p>
                                        <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-calculator text-green-500 mr-1"></i>
                                                O sistema calculará automaticamente sua margem de lucro
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Preços e Valores -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-currency-dollar text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Preços e Valores</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Entenda como gerenciar os preços do produto</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-green-500 text-white rounded-lg font-bold">1</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Preço de Venda</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-4">Este é o preço principal que aparecerá nas vendas:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Formato</p>
                                        <p class="text-lg font-bold text-green-600">R$ 99,90</p>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Campo aceita</p>
                                        <p class="text-sm text-slate-700 dark:text-slate-300">Decimais com vírgula</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-lg font-bold">2</span>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Preço de Custo</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-4">Quanto você pagou pelo produto (para controle interno):</p>
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Custo</p>
                                            <p class="text-lg font-bold text-blue-600">R$ 50,00</p>
                                        </div>
                                        <div class="text-center px-3">
                                            <i class="bi bi-arrow-right text-slate-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Venda</p>
                                            <p class="text-lg font-bold text-green-600">R$ 99,90</p>
                                        </div>
                                        <div class="text-center px-3">
                                            <i class="bi bi-arrow-right text-slate-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Lucro</p>
                                            <p class="text-lg font-bold text-emerald-600">R$ 49,90</p>
                                        </div>
                                    </div>
                                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                        <p class="text-sm text-emerald-700 dark:text-emerald-300 text-center font-semibold">
                                            <i class="bi bi-graph-up-arrow mr-1"></i>
                                            Margem de Lucro: 99.8%
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="bi bi-lightbulb-fill text-2xl text-amber-500"></i>
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Dica de Precificação</h4>
                                </div>
                                <ul class="space-y-2">
                                    <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                        <i class="bi bi-check-circle-fill text-amber-500"></i>
                                        Considere custos adicionais (frete, impostos, comissões)
                                    </li>
                                    <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                        <i class="bi bi-check-circle-fill text-amber-500"></i>
                                        Pesquise preços da concorrência
                                    </li>
                                    <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                        <i class="bi bi-check-circle-fill text-amber-500"></i>
                                        Mantenha uma margem de lucro saudável (mínimo 30%)
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Categorias e Organização -->
                    <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-folder2-open text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Categorias e Organização</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Organize seus produtos de forma eficiente</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-purple-500 rounded-lg">
                                        <i class="bi bi-tags text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Importância das Categorias</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-4">Categorizar produtos traz diversos benefícios:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-search text-purple-500"></i>
                                        Facilita a busca
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-funnel text-purple-500"></i>
                                        Melhora os filtros
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-graph-up text-purple-500"></i>
                                        Relatórios por categoria
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                        <i class="bi bi-grid text-purple-500"></i>
                                        Organização visual
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-blue-500 rounded-lg">
                                        <i class="bi bi-lightbulb text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Dicas de Categorização</h4>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">1</span>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Seja específico</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Ex: "Camisetas Masculinas" ao invés de apenas "Roupas"</p>
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">2</span>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Use padrões</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Mantenha uma nomenclatura consistente</p>
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">3</span>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Não exagere</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Muitas categorias podem confundir. Mantenha entre 5-15 categorias</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="bi bi-plus-circle-fill text-2xl text-green-500"></i>
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Criar Nova Categoria</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-2">Não encontrou a categoria ideal?</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    <i class="bi bi-arrow-right text-green-500 mr-1"></i>
                                    Acesse o menu <strong>Categorias</strong> para criar novas categorias personalizadas com ícones e cores!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Finalização -->
                    <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-check-circle text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Pronto para Cadastrar!</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Checklist final antes de criar o produto</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="bi bi-list-check text-blue-500"></i>
                                    Checklist de Verificação
                                </h4>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Nome do produto está claro e descritivo</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Código/SKU está correto</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Preços estão corretos (venda e custo)</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Quantidade em estoque está atualizada</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Categoria apropriada selecionada</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                        <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                        <span class="text-slate-700 dark:text-slate-300">Imagem do produto adicionada (opcional mas recomendado)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-purple-500 rounded-lg">
                                        <i class="bi bi-rocket-takeoff text-xl text-white"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Após Criar o Produto</h4>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 mb-3">O que você poderá fazer:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <i class="bi bi-cart-plus text-lg text-purple-500 mb-1"></i>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">Adicionar em Vendas</p>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <i class="bi bi-pencil text-lg text-blue-500 mb-1"></i>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">Editar a qualquer momento</p>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <i class="bi bi-files text-lg text-green-500 mb-1"></i>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">Duplicar o produto</p>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <i class="bi bi-boxes text-lg text-orange-500 mb-1"></i>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">Criar kits</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-green-300 dark:border-green-600">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="bi bi-emoji-smile-fill text-3xl text-green-500"></i>
                                    <div>
                                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Tudo Pronto!</h4>
                                        <p class="text-slate-600 dark:text-slate-300 text-sm">Agora você está pronto para cadastrar produtos</p>
                                    </div>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">Preencha o formulário com as informações do produto e clique em <strong class="text-green-600 dark:text-green-400">"Criar Produto"</strong> para finalizar!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer with Navigation -->
                <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <!-- Previous Button -->
                        <button @click="prevStep()" x-show="currentStep > 1"
                            class="flex items-center gap-2 px-6 py-3 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all duration-200 hover:scale-105">
                            <i class="bi bi-arrow-left"></i>
                            Anterior
                        </button>
                        <div x-show="currentStep === 1"></div>

                        <!-- Step Indicators -->
                        <div class="flex items-center gap-2">
                            <template x-for="step in totalSteps" :key="step">
                                <button @click="currentStep = step"
                                    class="transition-all duration-300 rounded-full"
                                    :class="currentStep === step ? 'w-8 h-3 bg-gradient-to-r from-green-600 to-emerald-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'">
                                </button>
                            </template>
                        </div>

                        <!-- Next/Finish Button -->
                        <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                            class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                            <span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'"></span>
                            <i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
