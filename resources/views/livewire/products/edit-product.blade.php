<div class="">
    <!-- Header Modernizado com botões de ação -->
    <x-sales-header
        title="Editar Produto"
        description="Altere as informações do produto #{{ $product->product_code }}"
        :back-route="route('products.index')"
        :current-step="1"
        :steps="[]">
        <x-slot name="actions">
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-200 font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                <i class="bi bi-x-lg text-lg"></i>
                Cancelar
            </a>
            <button type="submit" form="edit-product-form"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 hover:scale-105 transition-all duration-200">
                <span wire:loading.remove wire:target="update">
                    <i class="bi bi-save-fill text-lg"></i>
                    Salvar Alterações
                </span>
                <span wire:loading wire:target="update" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Salvando...
                </span>
            </button>
        </x-slot>
    </x-sales-header>

    <!-- Conteúdo Principal -->
    <form id="edit-product-form" wire:submit.prevent="update" class="px-6 py-6">
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
                            <p class="text-xs text-slate-400">Atualize a foto do produto</p>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <x-image-upload
                            name="image"
                            id="image"
                            wire-model="image"
                            title="Atualizar Imagem"
                            description="Clique ou arraste para alterar"
                            :existing-image="$this->existingImageUrl"
                            :new-image="$image"
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
</div>
