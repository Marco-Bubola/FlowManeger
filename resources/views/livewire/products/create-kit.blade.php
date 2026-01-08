<div class="">
    <!-- Header Compacto no Estilo das Outras Páginas -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

        <div class="relative px-8 py-4">
            <div class="flex justify-between items-center">
                <!-- Título e Progress -->
                <div class="flex items-center gap-6">
                    <!-- Ícone principal -->
                    <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                        <i class="bi bi-boxes text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">
                            Criar Novo Kit
                        </h1>

                        <!-- Progress Steps Horizontal -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $currentStep >= 1 ? 'bg-gradient-to-br from-purple-500 to-indigo-500' : 'bg-slate-300 dark:bg-slate-600' }} transition-all duration-300">
                                    <i class="bi bi-collection text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium {{ $currentStep >= 1 ? 'text-purple-600 dark:text-purple-400' : 'text-slate-500 dark:text-slate-400' }}">Produtos</span>
                            </div>

                            <div class="w-12 h-0.5 rounded {{ $currentStep >= 2 ? 'bg-gradient-to-r from-purple-500 to-indigo-500' : 'bg-slate-300 dark:bg-slate-600' }} transition-all duration-300"></div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $currentStep >= 2 ? 'bg-gradient-to-br from-indigo-500 to-blue-500' : 'bg-slate-300 dark:bg-slate-600' }} transition-all duration-300">
                                    <i class="bi bi-gear text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium {{ $currentStep >= 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400' }}">Configuração</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-white/60 dark:bg-slate-700/60 hover:bg-white dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                    </a>

                    @if($currentStep > 1)
                    <button type="button" wire:click="previousStep" class="px-5 py-2.5 bg-white/60 dark:bg-slate-700/60 hover:bg-white dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-arrow-left mr-2"></i>Anterior
                    </button>
                    @endif

                    @if($currentStep < 2)
                    <button type="button" wire:click="nextStep" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                        Próximo<i class="bi bi-arrow-right ml-2"></i>
                    </button>
                    @endif

                    @if($currentStep == 2)
                    <button type="button" wire:click="store" wire:loading.attr="disabled" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50">
                        <span wire:loading.remove wire:target="store">
                            <i class="bi bi-check-circle mr-2"></i>Criar Kit
                        </span>
                        <span wire:loading wire:target="store">
                            <i class="bi bi-arrow-repeat animate-spin mr-2"></i>Criando...
                        </span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal Moderno -->
    <div class="relative flex-1 overflow-y-auto">
        <div class="px-8 py-6 space-y-6">

            @if($currentStep == 1)
                <!-- ETAPA 1: Seleção de Produtos -->
                <div class="flex-1 space-y-6 animate-fadeIn">
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
                </div>
            @endif

            @if($currentStep == 2)
                <!-- ETAPA 2: Configuração Completa do Kit -->
                <div class="flex-1 animate-fadeIn">
                    <!-- Card Único com Grid Fluido -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 dark:border-slate-700/50">

                        <!-- Grid Adaptativo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                            <!-- Nome do Kit -->
                            <div class="space-y-2">
                                <label for="name" class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-boxes text-purple-500 mr-2"></i>
                                    Nome do Kit *
                                </label>
                                <input type="text"
                                    wire:model="name"
                                    id="name"
                                    class="w-full px-3 py-2.5 border rounded-xl bg-white/60 dark:bg-slate-700/60 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm
                                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-500' : 'border-slate-300 dark:border-slate-600 focus:border-purple-500' }}
                                    focus:ring-2 focus:ring-purple-500/20 focus:outline-none transition-all"
                                    placeholder="Ex: Kit Notebook + Mouse">
                                @error('name')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1"><i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categoria do Kit -->
                            <div class="space-y-2">
                                <label for="category_id" class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-tags-fill text-purple-500 mr-2"></i>
                                    Categoria do Kit *
                                </label>

                                <!-- Dropdown Customizado -->
                                <div class="relative" x-data="{
                                    open: false,
                                    selectedCategory: @entangle('category_id'),
                                    selectedCategoryName: '{{ $selectedCategoryName ?? 'Escolha...' }}',
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
                                            class="w-full flex items-center justify-between px-3 py-2.5 border rounded-xl bg-white/60 dark:bg-slate-700/60 text-slate-900 dark:text-slate-100 text-sm
                                            {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500' : 'border-slate-300 dark:border-slate-600 focus:border-purple-500' }}
                                            focus:ring-2 focus:ring-purple-500/20 focus:outline-none transition-all">
                                        <div class="flex items-center gap-2">
                                            <i :class="selectedCategoryIcon" class="text-slate-500 text-xs"></i>
                                            <span x-text="selectedCategoryName" class="text-sm"></span>
                                        </div>
                                        <i class="bi bi-chevron-down text-slate-400 text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open"
                                         x-transition
                                         @click.away="open = false"
                                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                                        @foreach($categories as $category)
                                        <button type="button"
                                                @click="selectCategory({ id: {{ $category->id_category }}, name: '{{ $category->name }}', icon: '{{ $this->getCategoryIcon($category->icone) }}' })"
                                                class="w-full flex items-center px-3 py-2.5 text-left hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-all text-sm border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                            <i class="{{ $this->getCategoryIcon($category->icone) }} text-purple-500 mr-2 text-xs"></i>
                                            <span class="text-slate-700 dark:text-slate-300">{{ $category->name }}</span>
                                        </button>
                                        @endforeach
                                    </div>
                                </div>

                                @error('category_id')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1"><i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Código do Kit -->
                            <div class="space-y-2">
                                <label for="product_code" class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-upc-scan text-indigo-500 mr-2"></i>
                                    Código *
                                </label>
                                <input type="text"
                                    wire:model="product_code"
                                    id="product_code"
                                    class="w-full px-3 py-2.5 border rounded-xl bg-white/60 dark:bg-slate-700/60 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm
                                    {{ $errors->has('product_code') ? 'border-red-400 focus:border-red-500' : 'border-slate-300 dark:border-slate-600 focus:border-indigo-500' }}
                                    focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all"
                                    placeholder="Ex: KIT-001">
                                @error('product_code')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1"><i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição (span completo) -->
                            <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                <label for="description" class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-card-text text-indigo-500 mr-2"></i>
                                    Descrição do Kit
                                </label>
                                <textarea wire:model="description"
                                    id="description"
                                    rows="2"
                                    class="w-full px-3 py-2.5 border rounded-xl bg-white/60 dark:bg-slate-700/60 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm resize-none
                                    {{ $errors->has('description') ? 'border-red-400 focus:border-red-500' : 'border-slate-300 dark:border-slate-600 focus:border-purple-500' }}
                                    focus:ring-2 focus:ring-purple-500/20 focus:outline-none transition-all"
                                    placeholder="Descreva o kit..."></textarea>
                                @error('description')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1"><i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Imagem do Kit -->
                            <div class="space-y-2 md:col-span-2">
                                <label class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    <i class="bi bi-image text-blue-500 mr-2"></i>
                                    Imagem do Kit
                                </label>
                                <x-image-upload
                                    name="image"
                                    id="image"
                                    wire-model="image"
                                    title="Adicionar Imagem"
                                    description="Clique ou arraste"
                                    :new-image="$image"
                                    height="h-40"
                                />
                            </div>

                            <!-- Custos Adicionais -->
                            <div class="space-y-3 bg-amber-50/50 dark:bg-amber-900/10 p-4 rounded-xl border border-amber-200 dark:border-amber-800">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center">
                                    <i class="bi bi-plus-circle text-amber-600 mr-2"></i>
                                    Custos Adicionais
                                </h4>
                                <div class="space-y-2">
                                    <label for="additional_costs" class="block text-xs font-medium text-slate-600 dark:text-slate-400">Valor</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-500">R$</span>
                                        <input type="text"
                                            id="additional_costs"
                                            wire:model.live="additional_costs"
                                            class="w-full pl-9 pr-3 py-2 border rounded-lg bg-white dark:bg-slate-800 text-sm border-slate-300 dark:border-slate-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                                            placeholder="0,00" />
                                    </div>
                                    <input type="text"
                                        id="additional_costs_description"
                                        wire:model="additional_costs_description"
                                        placeholder="Descrição (opcional)"
                                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-800 text-sm border-slate-300 dark:border-slate-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                                </div>
                            </div>

                            <!-- Resumo de Custos -->
                            <div class="space-y-2 bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-200 dark:border-blue-800">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center mb-2">
                                    <i class="bi bi-calculator text-blue-600 mr-2"></i>
                                    Resumo de Custos
                                </h4>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">Custo produtos:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">R$ {{ number_format(collect($selectedProducts)->sum(function($product) { return ($product['price'] ?? 0) * ($product['quantity'] ?? 1); }), 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">Custos adicionais:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">R$ {{ number_format((float)str_replace(',', '.', $additional_costs ?: '0'), 2, ',', '.') }}</span>
                                    </div>
                                    <hr class="border-slate-300 dark:border-slate-600 my-1">
                                    <div class="flex justify-between text-sm pt-1">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Total:</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($calculated_cost_price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preço Sugerido -->
                            <div class="space-y-2 bg-green-50/50 dark:bg-green-900/10 p-4 rounded-xl border border-green-200 dark:border-green-800">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center mb-2">
                                    <i class="bi bi-currency-dollar text-green-600 mr-2"></i>
                                    Preço Sugerido
                                </h4>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">Preço venda:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">R$ {{ number_format(collect($selectedProducts)->sum(function($product) { return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1); }), 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">Margem 5%:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">R$ {{ number_format($calculated_sale_price * 0.05, 2, ',', '.') }}</span>
                                    </div>
                                    <hr class="border-slate-300 dark:border-slate-600 my-1">
                                    <div class="flex justify-between text-sm pt-1">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Sugerido:</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preço Real -->
                            <div class="space-y-2 bg-purple-50/50 dark:bg-purple-900/10 p-4 rounded-xl border border-purple-200 dark:border-purple-800">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center mb-2">
                                    <i class="bi bi-tag text-purple-600 mr-2"></i>
                                    Preço Real
                                </h4>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-500">R$</span>
                                        <input type="text"
                                            wire:model.live="real_sale_price"
                                            placeholder="{{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}"
                                            class="w-full pl-9 pr-3 py-2 border rounded-lg bg-white dark:bg-slate-800 text-sm border-slate-300 dark:border-slate-600 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 focus:outline-none" />
                                    </div>
                                    <button type="button"
                                        wire:click="usesSuggestedPrice"
                                        class="px-3 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-xs font-semibold rounded-lg shadow hover:shadow-lg transition-all whitespace-nowrap">
                                        Usar
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

            <!-- Botões de Ação já estão integrados no header acima -->
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

            // Forçar aplicação dos estilos corretos dos cards após Livewire carregar
            setTimeout(() => {
                const productCards = document.querySelectorAll('.product-card-modern');
                productCards.forEach(card => {
                    // Força a reaplicação das classes específicas do CSS inline
                    card.classList.add('product-card-modern');

                    // Força a re-renderização dos badges
                    const badges = card.querySelectorAll('.badge-product-code, .badge-quantity, .badge-price, .badge-price-sale');
                    badges.forEach(badge => {
                        const style = badge.getAttribute('style') || '';
                        badge.setAttribute('style', style + '; display: block !important;');
                    });
                });
            }, 200);
        });

        // Também aplica quando há navegação/mudança de step
        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => {
                const productCards = document.querySelectorAll('.product-card-modern');
                productCards.forEach(card => {
                    card.classList.add('product-card-modern');

                    const badges = card.querySelectorAll('.badge-product-code, .badge-quantity, .badge-price, .badge-price-sale');
                    badges.forEach(badge => {
                        const style = badge.getAttribute('style') || '';
                        badge.setAttribute('style', style + '; display: block !important;');
                    });
                });
            }, 200);
        });
    </script>
</div>

