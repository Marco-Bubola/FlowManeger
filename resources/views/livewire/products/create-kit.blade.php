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
        <div class="">

            @if($currentStep == 1)
                <!-- ETAPA 1: Seleção de Produtos -->
                <div class="flex-1  animate-fadeIn">
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
                <div class="flex-1 animate-fadeIn max-h-[81vh] overflow-hidden">
                    <div class="flex flex-col xl:flex-row gap-5 h-full">

                        <!-- ========== COLUNA ESQUERDA: Formulário ========== -->
                        <div class="flex-1 overflow-y-auto custom-scrollbar">
                            <div class="bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-2xl p-5 shadow-2xl border border-slate-700/50">

                                <!-- Informações Básicas -->
                                <div class="mb-4">
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                                            <i class="bi bi-boxes text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-white leading-tight">Informações Básicas</h3>
                                            <p class="text-xs text-slate-400 leading-tight">Dados essenciais do kit</p>
                                        </div>
                                    </div>

                                    <!-- Tudo em uma linha -->
                                    <div class="grid grid-cols-12 gap-3">
                                        <!-- Nome do Kit - 4 colunas (AUTO) -->
                                        <div class="col-span-4 space-y-1.5">
                                            <label for="name" class="flex items-center text-sm font-semibold text-slate-300">
                                                <i class="bi bi-boxes text-purple-400 mr-1.5 text-xs"></i>
                                                Nome do Kit
                                                <span class="ml-2 px-2 py-0.5 bg-purple-500/20 text-purple-300 text-xs rounded-full flex items-center gap-1">
                                                    <i class="bi bi-magic"></i> Auto
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    wire:model="name"
                                                    id="name"
                                                    readonly
                                                    class="w-full px-3 py-2.5 rounded-lg bg-slate-800/30 border text-slate-100 placeholder-slate-400 text-sm border-slate-700/50 cursor-not-allowed"
                                                    placeholder="Gerado automaticamente...">
                                                <i class="bi bi-lock-fill absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <i class="bi bi-info-circle"></i> Baseado nos produtos
                                            </p>
                                        </div>

                                        <!-- Código do Kit - 2 colunas (AUTO) -->
                                        <div class="col-span-2 space-y-1.5">
                                            <label for="product_code" class="flex items-center text-sm font-semibold text-slate-300">
                                                <i class="bi bi-upc-scan text-indigo-400 mr-1.5 text-xs"></i>
                                                Código
                                                <span class="ml-2 px-2 py-0.5 bg-indigo-500/20 text-indigo-300 text-xs rounded-full flex items-center gap-1">
                                                    <i class="bi bi-magic"></i> Auto
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    wire:model="product_code"
                                                    id="product_code"
                                                    readonly
                                                    class="w-full px-3 py-2.5 rounded-lg bg-slate-800/30 border text-slate-100 placeholder-slate-400 text-sm border-slate-700/50 cursor-not-allowed"
                                                    placeholder="Gerado...">
                                                <i class="bi bi-lock-fill absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <i class="bi bi-info-circle"></i> Códigos unidos
                                            </p>
                                        </div>

                                        <!-- Categoria do Kit - 3 colunas (AUTO: Kit) -->
                                        <div class="col-span-3 space-y-1.5">
                                            <label for="category_id" class="flex items-center text-sm font-semibold text-slate-300">
                                                <i class="bi bi-tags-fill text-purple-400 mr-1.5 text-xs"></i>
                                                Categoria
                                                <span class="ml-2 px-2 py-0.5 bg-emerald-500/20 text-emerald-300 text-xs rounded-full flex items-center gap-1">
                                                    <i class="bi bi-magic"></i> Kit
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    value="Kit"
                                                    readonly
                                                    class="w-full px-3 py-2.5 rounded-lg bg-slate-800/30 border text-slate-100 text-sm border-slate-700/50 cursor-not-allowed"
                                                    placeholder="Categoria: Kit">
                                                <i class="bi bi-lock-fill absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <i class="bi bi-info-circle"></i> Sempre "Kit"
                                            </p>
                                        </div>

                                        <!-- Descrição - 3 colunas (AUTO) -->
                                        <div class="col-span-3 space-y-1.5">
                                            <label for="description" class="flex items-center text-sm font-semibold text-slate-300">
                                                <i class="bi bi-card-text text-indigo-400 mr-1.5 text-xs"></i>
                                                Descrição
                                                <span class="ml-2 px-2 py-0.5 bg-blue-500/20 text-blue-300 text-xs rounded-full flex items-center gap-1">
                                                    <i class="bi bi-magic"></i> Auto
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    wire:model="description"
                                                    id="description"
                                                    readonly
                                                    class="w-full px-3 py-2.5 rounded-lg bg-slate-800/30 border text-slate-100 placeholder-slate-400 text-sm border-slate-700/50 cursor-not-allowed"
                                                    placeholder="Gerada automaticamente...">
                                                <i class="bi bi-lock-fill absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <i class="bi bi-info-circle"></i> Lista de produtos
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divisor -->
                                <div class="border-t border-slate-700/50 my-3.5"></div>

                                <!-- Preços e Custos -->
                                <div>
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center shrink-0">
                                            <i class="bi bi-cash-coin text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-white leading-tight">Preços e Custos</h3>
                                            <p class="text-xs text-slate-400 leading-tight">Defina os valores do kit</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <!-- Custos Adicionais -->
                                        <div class="space-y-2.5 bg-amber-900/20 p-3.5 rounded-lg border border-amber-700/30">
                                            <h4 class="text-base font-bold text-white flex items-center">
                                                <i class="bi bi-plus-circle text-amber-400 mr-1.5"></i>
                                                Custos Adicionais
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <div class="relative flex-1">
                                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-semibold text-slate-300">R$</span>
                                                    <input type="text"
                                                        id="additional_costs"
                                                        wire:model.live="additional_costs"
                                                        class="w-full pl-10 pr-3 py-2.5 border rounded-lg bg-slate-800/50 text-slate-100 text-base font-semibold border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none placeholder-slate-500"
                                                        placeholder="0,00" />
                                                </div>
                                                <input type="text"
                                                    id="additional_costs_description"
                                                    wire:model="additional_costs_description"
                                                    placeholder="Descrição"
                                                    class="flex-1 px-3 py-2.5 border rounded-lg bg-slate-800/50 text-slate-100 text-sm border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none placeholder-slate-500" />
                                            </div>
                                        </div>

                                        <!-- Resumo de Custos -->
                                        <div class="space-y-2.5 bg-blue-900/20 p-3.5 rounded-lg border border-blue-700/30">
                                            <h4 class="text-base font-bold text-white flex items-center">
                                                <i class="bi bi-calculator text-blue-400 mr-1.5"></i>
                                                Resumo de Custos
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 font-medium">Custo produtos:</span>
                                                    <span class="font-bold text-slate-100 text-base">R$ {{ number_format(collect($selectedProducts)->sum(function($product) { return ($product['price'] ?? 0) * ($product['quantity'] ?? 1); }), 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 font-medium">Custos adicionais:</span>
                                                    <span class="font-bold text-slate-100 text-base">R$ {{ number_format((float)str_replace(',', '.', $additional_costs ?: '0'), 2, ',', '.') }}</span>
                                                </div>
                                                <hr class="border-slate-700/50 my-1.5">
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="font-bold text-white text-base">Total:</span>
                                                    <span class="font-black text-blue-400 text-lg">R$ {{ number_format($calculated_cost_price, 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preço Sugerido -->
                                        <div class="space-y-2.5 bg-green-900/20 p-3.5 rounded-lg border border-green-700/30">
                                            <h4 class="text-base font-bold text-white flex items-center">
                                                <i class="bi bi-currency-dollar text-green-400 mr-1.5"></i>
                                                Preço Sugerido
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 font-medium">Preço venda:</span>
                                                    <span class="font-bold text-slate-100 text-base">R$ {{ number_format(collect($selectedProducts)->sum(function($product) { return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1); }), 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-300 font-medium">Margem 5%:</span>
                                                    <span class="font-bold text-slate-100 text-base">R$ {{ number_format($calculated_sale_price * 0.05, 2, ',', '.') }}</span>
                                                </div>
                                                <hr class="border-slate-700/50 my-1.5">
                                                <div class="flex justify-between items-center pt-1">
                                                    <span class="font-bold text-white text-base">Sugerido:</span>
                                                    <span class="font-black text-emerald-400 text-lg">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preço Real -->
                                        <div class="space-y-2.5 bg-purple-900/20 p-3.5 rounded-lg border border-purple-700/30">
                                            <h4 class="text-base font-bold text-white flex items-center">
                                                <i class="bi bi-tag text-purple-400 mr-1.5"></i>
                                                Preço Real *
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <div class="relative flex-1">
                                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-semibold text-slate-300">R$</span>
                                                    <input type="text"
                                                        wire:model.live="real_sale_price"
                                                        placeholder="{{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}"
                                                        class="w-full pl-10 pr-3 py-2.5 border rounded-lg bg-slate-800/50 text-slate-100 text-base font-bold border-slate-700/50 focus:border-purple-500 focus:ring-1 focus:ring-purple-500/20 focus:outline-none placeholder-slate-500" />
                                                </div>
                                                <button type="button"
                                                    wire:click="usesSuggestedPrice"
                                                    class="px-3 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-bold rounded-lg shadow hover:shadow-lg transition-all whitespace-nowrap">
                                                    Usar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== COLUNA DIREITA: Upload ========== -->
                        <div class="w-full xl:w-[380px]">
                            <div class="bg-gradient-to-br from-slate-900/95 via-purple-900/20 to-slate-900/95 backdrop-blur-xl rounded-2xl p-5 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                                <div class="flex items-center gap-2.5 mb-4">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="bi bi-image-fill text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-white leading-tight">Imagem do Kit</h3>
                                        <p class="text-xs text-slate-400 leading-tight">Foto de alta qualidade</p>
                                    </div>
                                </div>

                                <div class="flex-1 flex items-center justify-center min-h-0">
                                    <x-image-upload
                                        name="image"
                                        id="image"
                                        wire-model="image"
                                        title="Upload da Imagem"
                                        description="Clique ou arraste aqui"
                                        :new-image="$image"
                                        height="h-[calc(81vh-180px)]"
                                    />
                                </div>

                                <div class="mt-3 flex items-start gap-1.5 text-xs text-slate-400">
                                    <i class="bi bi-info-circle text-blue-400 mt-0.5 text-xs"></i>
                                    <p>JPG, PNG, JPEG • Máx 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estilo para scrollbar customizada -->
                <style>
                    .custom-scrollbar::-webkit-scrollbar {
                        width: 6px;
                    }
                    .custom-scrollbar::-webkit-scrollbar-track {
                        background: rgba(15, 23, 42, 0.3);
                        border-radius: 10px;
                    }
                    .custom-scrollbar::-webkit-scrollbar-thumb {
                        background: rgba(139, 92, 246, 0.5);
                        border-radius: 10px;
                    }
                    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                        background: rgba(139, 92, 246, 0.7);
                    }
                </style>
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

