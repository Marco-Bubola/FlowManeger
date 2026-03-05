<div class="mobile-393-base">
    <!-- Header Compacto no Estilo das Outras Páginas -->
    <div class="create-kit-header sticky top-0 z-40 relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

        <div class="create-kit-header-inner relative px-8 py-4">
            <div class="create-kit-header-row flex justify-between items-center">
                <!-- Título e Progress -->
                <div class="create-kit-header-left flex items-center gap-6">
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
                <div class="create-kit-header-actions flex items-center gap-3">
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
    <div class="create-kit-main relative flex-1 overflow-y-auto">
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
                    :compactCards="true"
                    wireModel="selectedProducts"
                    wire:key="product-selector-step-{{ $currentStep }}" />
            </div>
            @endif

            @if($currentStep == 2)
            <!-- ETAPA 2: Configuração Completa do Kit -->
            <div class="flex-1 animate-fadeIn">
                <div class="create-kit-step2 flex flex-col xl:flex-row gap-5 h-full">

                    <!-- ========== COLUNA ESQUERDA: Formulário ========== -->
                    <div class="create-kit-step2-left w-full xl:w-[56%] overflow-y-auto custom-scrollbar">
                        <div class="bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-2xl p-5 shadow-2xl border border-slate-700/50">

                            <!-- Informações Básicas -->
                            <div class="mb-4">
                               

                                <div class="grid grid-cols-1 gap-3">
                                    <div class="space-y-1.5">
                                        <label for="name" class="flex items-center text-sm font-semibold text-slate-300">
                                            <i class="bi bi-boxes text-purple-400 mr-1.5 text-xs"></i>
                                            Nome do Kit
                                            <span class="ml-2 px-2 py-0.5 bg-purple-500/20 text-purple-300 text-xs rounded-full flex items-center gap-1">
                                                <i class="bi bi-magic"></i> Auto
                                            </span>
                                        </label>
                                        <div class="relative flex gap-2">
                                            <input type="text"
                                                wire:model="name"
                                                id="name"
                                                class="flex-1 px-3 py-2.5 rounded-lg bg-slate-800/50 border text-slate-100 placeholder-slate-400 text-sm border-slate-700/50 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                                                placeholder="Nome do kit...">
                                            <button type="button"
                                                wire:click="$set('name', '{{ addslashes($this->getAutoGeneratedName()) }}')"
                                                class="px-3 py-2.5 bg-purple-500/20 hover:bg-purple-500/30 border border-purple-500/50 rounded-lg text-purple-300 transition-all group"
                                                title="Regenerar nome automaticamente">
                                                <i class="bi bi-arrow-clockwise group-hover:rotate-180 transition-transform duration-300"></i>
                                            </button>
                                        </div>
                                        <p class="text-xs text-slate-400 flex items-center gap-1">
                                            <i class="bi bi-info-circle"></i> Baseado nos produtos • Editável
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300">
                                            <i class="bi bi-upc-scan"></i>Código automático ativo
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300">
                                            <i class="bi bi-tags-fill"></i>Categoria automática ativa
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-300">
                                            <i class="bi bi-card-text"></i>Descrição automática ativa
                                        </span>
                                    </div>

                                    <input type="hidden" wire:model="product_code">
                                    <input type="hidden" wire:model="category_id">
                                    <input type="hidden" wire:model="description">
                                </div>
                            </div>

                            <!-- Divisor -->
                            <div class="border-t border-slate-700/50 my-3.5"></div>

                            <!-- Preços e Custos -->
                            <div>
                            

                                @php
                                $productsCostTotal = collect($selectedProducts)->sum(function($product) {
                                return ($product['price'] ?? 0) * ($product['quantity'] ?? 1);
                                });
                                $productsSaleTotal = collect($selectedProducts)->sum(function($product) {
                                return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1);
                                });
                                $additionalCostsRaw = preg_replace('/[^\d,]/', '', (string)($additional_costs ?: '0'));
                                $additionalCostsTotal = (float) str_replace(',', '.', $additionalCostsRaw ?: '0');
                                @endphp

                                <div class="space-y-3 bg-slate-900/35 border border-slate-700/50 rounded-xl p-3.5">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div class="rounded-lg bg-slate-800/45 border border-slate-700/60 px-3 py-2">
                                            <p class="text-[11px] text-slate-400">Total custo (price)</p>
                                            <p class="text-sm font-bold text-slate-100">R$ {{ number_format($productsCostTotal, 2, ',', '.') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-slate-800/45 border border-slate-700/60 px-3 py-2">
                                            <p class="text-[11px] text-slate-400">Total venda (price_sale)</p>
                                            <p class="text-sm font-bold text-slate-100">R$ {{ number_format($productsSaleTotal, 2, ',', '.') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-blue-500/15 border border-blue-700/40 px-3 py-2">
                                            <p class="text-[11px] text-blue-200">Base final do kit (price_sale + adicionais)</p>
                                            <p class="text-base font-black text-blue-300">R$ {{ number_format($calculated_sale_price, 2, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-700/50"></div>

                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-bold text-white flex items-center">
                                                <i class="bi bi-plus-circle text-amber-400 mr-1.5"></i>
                                                Custos Adicionais
                                            </h4>
                                            <button type="button"
                                                wire:click="addAdditionalCostItem"
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-500/20 border border-amber-500/40 text-amber-200 hover:bg-amber-500/30 text-xs font-semibold">
                                                <i class="bi bi-plus-lg mr-1"></i>Adicionar
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($additionalCostItems as $costIndex => $costItem)
                                            <div class="grid grid-cols-12 gap-2 items-center">
                                                <div class="col-span-7">
                                                    <input type="text"
                                                        wire:model.live="additionalCostItems.{{ $costIndex }}.description"
                                                        placeholder="Descrição do custo"
                                                        class="w-full px-3 py-2 border rounded-lg bg-slate-800/50 text-slate-100 text-sm border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none placeholder-slate-500" />
                                                </div>
                                                <div class="col-span-4 relative">
                                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-semibold text-slate-300">R$</span>
                                                    <input type="text"
                                                        wire:model.live="additionalCostItems.{{ $costIndex }}.value"
                                                            oninput="this.value = window.fmMoneyMask ? window.fmMoneyMask(this.value) : this.value"
                                                        class="w-full pl-10 pr-2 py-2 border rounded-lg bg-slate-800/50 text-slate-100 text-sm font-semibold border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none placeholder-slate-500"
                                                        placeholder="0,00" />
                                                </div>
                                                <div class="col-span-1 text-right">
                                                    @if(count($additionalCostItems) > 1)
                                                    <button type="button"
                                                        wire:click="removeAdditionalCostItem({{ $costIndex }})"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-red-300 hover:text-red-200 hover:bg-red-500/20">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        <div class="hidden">
                                            <input type="text" id="additional_costs" wire:model.live="additional_costs" />
                                            <input type="text" id="additional_costs_description" wire:model="additional_costs_description" />
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-700/50"></div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                        <div class="space-y-2.5">
                                            <h4 class="text-sm font-bold text-white flex items-center">
                                                <i class="bi bi-calculator text-emerald-400 mr-1.5"></i>
                                                Composição do Preço Sugerido
                                            </h4>
                                            <div class="space-y-1.5 text-sm">
                                                <div class="flex justify-between items-center rounded-lg bg-slate-800/35 px-2.5 py-2">
                                                    <span class="text-slate-300">Total custo (price)</span>
                                                    <span class="font-semibold text-slate-100">R$ {{ number_format($productsCostTotal, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center rounded-lg bg-slate-800/35 px-2.5 py-2">
                                                    <span class="text-slate-300">Total venda (price_sale)</span>
                                                    <span class="font-semibold text-slate-100">R$ {{ number_format($productsSaleTotal, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center rounded-lg bg-slate-800/35 px-2.5 py-2">
                                                    <span class="text-slate-300">Custos adicionais</span>
                                                    <span class="font-semibold text-slate-100">R$ {{ number_format($additionalCostsTotal, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center rounded-lg bg-slate-800/35 px-2.5 py-2">
                                                    <span class="text-slate-300">Base (price_sale + adicionais)</span>
                                                    <span class="font-semibold text-slate-100">R$ {{ number_format($calculated_sale_price, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center rounded-lg bg-slate-800/35 px-2.5 py-2">
                                                    <span class="text-slate-300">Margem 5%</span>
                                                    <span class="font-semibold text-slate-100">R$ {{ number_format(($calculated_sale_price ?? 0) * 0.05, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center rounded-lg bg-emerald-500/15 border border-emerald-700/40 px-2.5 py-2.5 mt-1">
                                                    <span class="font-bold text-white">Preço sugerido</span>
                                                    <span class="font-black text-emerald-300 text-base">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2.5">
                                            <h4 class="text-sm font-bold text-white flex items-center">
                                                <i class="bi bi-tag text-purple-400 mr-1.5"></i>
                                                Preço de Venda Final
                                            </h4>
                                            <p class="text-xs text-slate-300 bg-slate-800/35 border border-slate-700/50 rounded-lg px-2.5 py-2">
                                                Valor que será salvo no kit. Se quiser, use o sugerido automaticamente.
                                            </p>
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
                                                    <i class="bi bi-magic mr-1"></i>Usar
                                                </button>
                                            </div>
                                            <div class="rounded-lg bg-purple-500/15 border border-purple-700/40 px-2.5 py-2">
                                                <p class="text-[11px] text-purple-200">Sugerido atual</p>
                                                <p class="text-base font-black text-purple-300">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== COLUNA DIREITA: Upload ========== -->
                    <div class="create-kit-step2-right w-full xl:w-[40%] flex flex-col gap-4">
                        <div class="create-kit-upload-card bg-gradient-to-br from-slate-900/95 via-purple-900/20 to-slate-900/95 backdrop-blur-xl rounded-2xl p-5 shadow-2xl border border-slate-700/50 flex flex-col overflow-hidden">
                            <div class="flex items-center gap-2.5 mb-4">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="bi bi-image-fill text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white leading-tight">Imagem do Kit</h3>
                                    <p class="text-xs text-slate-400 leading-tight">Foto de alta qualidade</p>
                                </div>
                            </div>

                            <div class="flex-1 flex items-center justify-center min-h-0 w-full overflow-hidden">
                                <x-image-upload
                                    name="image"
                                    id="image"
                                    wire-model="image"
                                    title="Upload da Imagem"
                                    description="Clique ou arraste aqui"
                                    :new-image="$image"
                                    height="h-[300px] md:h-[420px]" />
                            </div>

                            <div class="mt-3 flex items-start gap-1.5 text-xs text-slate-400">
                                <i class="bi bi-info-circle text-blue-400 mt-0.5 text-xs"></i>
                                <p>JPG, PNG, JPEG • Máx 2MB</p>
                            </div>
                        </div>

                        <div class="create-kit-products-card bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-2xl p-4 shadow-2xl border border-slate-700/50">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="bi bi-grid-fill text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white leading-tight">Produtos Selecionados</h3>
                                    <p class="text-xs text-slate-400 leading-tight">Itens do kit (abaixo da imagem)</p>
                                </div>
                            </div>

                            @if(count($selectedProducts) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[340px] overflow-y-auto custom-scrollbar pr-1">
                                @foreach($selectedProducts as $item)
                                <div class="rounded-xl border border-slate-700/60 bg-slate-900/50 p-2.5">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ !empty($item['image']) ? asset('storage/products/' . $item['image']) : asset('storage/products/product-placeholder.png') }}"
                                            alt="{{ $item['name'] ?? 'Produto' }}"
                                            class="w-10 h-10 md:w-12 md:h-12 rounded-lg object-cover border border-slate-700" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-white truncate">{{ $item['name'] ?? 'Produto' }}</p>
                                            <p class="text-[11px] text-slate-400">Qtd: {{ $item['quantity'] ?? 1 }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/25 text-indigo-200">
                                            <i class="bi bi-tag mr-1"></i>R$ {{ number_format($item['price'] ?? 0, 2, ',', '.') }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/25 text-purple-200">
                                            <i class="bi bi-currency-dollar mr-1"></i>R$ {{ number_format($item['salePrice'] ?? 0, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-lg border border-slate-700/50 bg-slate-800/40 px-3 py-3 text-sm text-slate-300">
                                Nenhum produto selecionado até o momento.
                            </div>
                            @endif
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

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-10px) rotate(1deg);
            }

            66% {
                transform: translateY(-5px) rotate(-1deg);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
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
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
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
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @media (max-width: 450px) {
            .mobile-393-base {
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }

            .mobile-393-base .create-kit-header {
                border-radius: 1rem;
                margin-bottom: 0.75rem;
                top: 0.35rem;
            }

            .mobile-393-base .create-kit-header-inner {
                padding: 0.7rem !important;
            }

            .mobile-393-base .create-kit-header-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.7rem;
            }

            .mobile-393-base .create-kit-header-left {
                gap: 0.55rem;
                align-items: flex-start;
            }

            .mobile-393-base .create-kit-header-left h1 {
                font-size: 1.1rem !important;
                line-height: 1.2 !important;
            }

            .mobile-393-base .create-kit-header-left .w-14.h-14 {
                width: 2.35rem !important;
                height: 2.35rem !important;
            }

            .mobile-393-base .create-kit-header-left .w-14.h-14 i {
                font-size: 1rem !important;
            }

            .mobile-393-base .create-kit-header-left .gap-3 {
                gap: 0.35rem !important;
                flex-wrap: wrap;
            }

            .mobile-393-base .create-kit-header-left .w-12 {
                width: 1.4rem !important;
            }

            .mobile-393-base .create-kit-header-left .text-sm {
                font-size: 0.69rem !important;
            }

            .mobile-393-base .create-kit-header-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.45rem;
            }

            .mobile-393-base .create-kit-header-actions > * {
                width: 100%;
                justify-content: center;
                padding: 0.55rem 0.45rem !important;
                font-size: 0.72rem !important;
                border-radius: 0.7rem !important;
            }

            .mobile-393-base .create-kit-main {
                overflow-x: hidden;
            }

            .mobile-393-base .create-kit-step2 {
                gap: 0.65rem !important;
            }

            .mobile-393-base .create-kit-step2-left > div,
            .mobile-393-base .create-kit-upload-card,
            .mobile-393-base .create-kit-products-card {
                padding: 0.8rem !important;
                border-radius: 0.95rem !important;
            }

            .mobile-393-base .create-kit-step2-left .grid.grid-cols-12 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 0.4rem;
            }

            .mobile-393-base .create-kit-step2-left .col-span-7,
            .mobile-393-base .create-kit-step2-left .col-span-4,
            .mobile-393-base .create-kit-step2-left .col-span-1 {
                grid-column: span 1 / span 1;
            }

            .mobile-393-base .create-kit-step2-left .col-span-1 {
                text-align: left;
            }

            .mobile-393-base .create-kit-step2-left .grid.grid-cols-1.sm\:grid-cols-3,
            .mobile-393-base .create-kit-step2-left .grid.grid-cols-1.lg\:grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .mobile-393-base .create-kit-upload-card .mt-3 {
                margin-top: 0.5rem !important;
            }

            .mobile-393-base .create-kit-products-card .max-h-\[340px\] {
                max-height: 240px !important;
            }
        }
    </style>

    <!-- Script para sincronizar totais com custos adicionais -->
    <script>
        window.fmMoneyMask = function (value) {
            const digitsOnly = String(value || '').replace(/\D/g, '');
            if (!digitsOnly.length) return '';

            const integerValue = parseInt(digitsOnly, 10);
            if (Number.isNaN(integerValue)) return '';

            const cents = (integerValue / 100).toFixed(2);
            const [whole, decimal] = cents.split('.');
            const withThousands = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return `${withThousands},${decimal}`;
        };

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