<div class="">

    <!-- Header Modernizado -->
    <x-sales-header
        title="Editar Produto"
        description="Altere as informações do produto #{{ $product->product_code }}"
        :back-route="route('products.index')"
        :current-step="$currentStep ?? 1"
        :steps="[
            [
                'title' => 'Informações',
                'description' => 'Nome, preços, estoque e categoria',
                'icon' => 'bi-info-circle',
                'gradient' => 'from-orange-500 to-red-500',
                'connector_gradient' => 'from-orange-500 to-red-500'
            ],
            [
                'title' => 'Imagem',
                'description' => 'Upload da foto',
                'icon' => 'bi-image',
                'gradient' => 'from-red-500 to-pink-500'
            ]
        ]" />

    <!-- Conteúdo Principal Moderno -->
    <div class="relative flex-1 overflow-y-auto">
        <div class="px-8 py-6 space-y-6 h-full flex flex-col">

            @if($currentStep == 1)
                <!-- ETAPA 1: Informações do Produto -->
                <form wire:submit="update" class="flex-1 space-y-6 animate-fadeIn">
                    <!-- Card Container Principal -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50">
                            <!-- Seção Nome e Código -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                                <!-- Nome do Produto Melhorado -->
                                <div class="group space-y-4">
                                    <label for="name" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-tag-fill text-white"></i>
                                        </div>
                                        Nome do Produto *
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="bi bi-box-seam text-slate-400 group-hover:text-blue-500 transition-colors duration-300"></i>
                                        </div>
                                        <input type="text"
                                            wire:model="name"
                                            id="name"
                                            class="w-full pl-14 pr-14 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                            placeholder="Ex: Notebook Dell Inspiron 15">
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

                                <!-- Código do Produto Melhorado -->
                                <div class="group space-y-4">
                                    <label for="product_code" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-upc-scan text-white"></i>
                                        </div>
                                        Código do Produto *
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="bi bi-hash text-slate-400 group-hover:text-purple-500 transition-colors duration-300"></i>
                                        </div>
                                        <input type="text"
                                            wire:model="product_code"
                                            id="product_code"
                                            class="w-full pl-14 pr-14 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('product_code') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                            placeholder="Ex: NB-DELL-001">
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

                                <!-- Categoria Melhorada -->
                                <div class="group space-y-4">


                                    <label for="category_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-tags-fill text-white"></i>
                                        </div>
                                        Selecione a Categoria *
                                    </label>

                                    <!-- Dropdown Customizado Melhorado -->
                                    <div class="relative"
                                         x-data="{
                                            open: false,
                                            selectedCategory: @entangle('category_id'),
                                            selectedCategoryName: @js($selectedCategoryName ?? 'Escolha uma categoria...'),
                                            selectedCategoryIcon: @js($selectedCategoryIcon ?? 'bi-grid-3x3-gap-fill'),
                                            categories: @js($categories->toArray()),
                                            updateCategoryInfo() {
                                                if (this.selectedCategory) {
                                                    const category = this.categories.find(cat => cat.id_category == this.selectedCategory);
                                                    if (category) {
                                                        this.selectedCategoryName = category.name;
                                                        // Mapear ícone conforme a lógica do backend
                                                        this.selectedCategoryIcon = this.getCategoryIcon(category.icone);
                                                    }
                                                } else {
                                                    this.selectedCategoryName = 'Escolha uma categoria...';
                                                    this.selectedCategoryIcon = 'bi-grid-3x3-gap-fill';
                                                }
                                            },
                                            getCategoryIcon(iconeName) {
                                                const iconMapping = {
                                                    'laptop': 'bi-laptop',
                                                    'phone': 'bi-phone',
                                                    'tablet': 'bi-tablet',
                                                    'headphones': 'bi-headphones',
                                                    'keyboard': 'bi-keyboard',
                                                    'mouse': 'bi-mouse',
                                                    'monitor': 'bi-display',
                                                    'default': 'bi-grid-3x3-gap-fill'
                                                };
                                                return iconMapping[iconeName] || iconMapping['default'];
                                            },
                                            selectCategory(category) {
                                                this.selectedCategory = category.id;
                                                this.selectedCategoryName = category.name;
                                                this.selectedCategoryIcon = category.icon;
                                                this.open = false;
                                                $wire.set('category_id', category.id);
                                            }
                                         }"
                                         x-init="updateCategoryInfo()"
                                         x-watch="selectedCategory"
                                         x-on:category-changed="updateCategoryInfo()">
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

                                        <!-- Dropdown Menu Melhorado -->
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
                            </div>

                            <!-- Descrição em linha separada ocupando largura total -->
                            <div class="grid grid-cols-1 gap-6 mb-6">
                                <!-- Descrição Melhorada -->
                                <div class="group space-y-4">
                                    <label for="description" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-card-text text-white"></i>
                                        </div>
                                        Descrição do Produto
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
                                            placeholder="Descreva as principais características do produto..."></textarea>
                                    </div>
                                    @error('description')
                                    <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                        <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Seção Preços e Estoque com Componentes Melhorados -->
                            <div class="bg-gradient-to-r from-slate-50/50 to-blue-50/50 dark:from-slate-800/50 dark:to-blue-900/20 rounded-2xl p-6 border border-slate-200/50 dark:border-slate-700/50">
                                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-6 flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-lg mr-3">
                                        <i class="bi bi-cash-stack text-white"></i>
                                    </div>
                                    Preços e Estoque
                                </h3>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Preço de Custo -->
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

                                    <!-- Preço de Venda -->
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

                                    <!-- Quantidade em Estoque -->
                                    <x-quantity-input
                                        name="stock_quantity"
                                        id="stock_quantity"
                                        wireModel="stock_quantity"
                                        :min="0"
                                        :max="99999"
                                        label="Quantidade em Estoque"
                                        icon="bi-boxes"
                                        icon-color="cyan"
                                        :required="true"
                                        width="w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            @if($currentStep == 2)
                <!-- ETAPA 2: Upload de Imagem Moderna -->
                <form wire:submit="update" class="flex-1 flex items-center justify-center animate-fadeIn">
                    <div class="w-full max-w-3xl">
                            <!-- Card Container para Upload -->
                            <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50">
                                <!-- Título da seção -->
                                <div class="text-center mb-6">
                                    <h2 class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-blue-600 to-indigo-600 dark:from-slate-200 dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
                                        📸 Imagem do Produto
                                    </h2>
                                    <p class="text-lg text-slate-600 dark:text-slate-400">
                                        Atualize a imagem que representa seu produto da melhor forma
                                    </p>
                                </div>

                                <!-- Componente de Upload Melhorado -->
                                <x-image-upload
                                    name="image"
                                    id="image"
                                    wire-model="image"
                                    title="Atualizar Imagem do Produto"
                                    description="Clique para selecionar ou arraste e solte sua imagem aqui"
                                    :existing-image="$this->existingImageUrl"
                                    :new-image="$image"
                                    height="h-96"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            <!-- Botões de Ação Modernizados -->
            <x-action-buttons-new
                :show-back="$currentStep > 1"
                :show-next="$currentStep < 2"
                :show-save="$currentStep == 2"
                :show-cancel="true"
                back-action="previousStep"
                next-action="nextStep"
                save-action="update"
                :cancel-route="route('products.index')"
                save-text="Atualizar Produto"
                loading-text="Atualizando produto..."
            />
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
    </div>
