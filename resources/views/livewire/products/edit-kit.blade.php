<div class="create-kit-page mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-kit-ultrawide.css') }}">
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
                            Editar Kit
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
                                <i class="bi bi-check-circle mr-2"></i>Salvar Alterações
                            </span>
                            <span wire:loading wire:target="store">
                                <i class="bi bi-arrow-repeat animate-spin mr-2"></i>Salvando...
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
                    :searchTerm="$searchTerm"
                    :selectedCategory="$selectedCategory"
                    :sortBy="$sortBy"
                    :stockFilter="$stockFilter"
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
            @php
                $productsCostTotal = collect($selectedProducts)->sum(function($product) {
                    return ($product['price'] ?? 0) * ($product['quantity'] ?? 1);
                });
                $productsSaleTotal = collect($selectedProducts)->sum(function($product) {
                    return ($product['salePrice'] ?? 0) * ($product['quantity'] ?? 1);
                });
                $additionalCostsRaw = preg_replace('/[^\d,]/', '', (string)($additional_costs ?: '0'));
                $additionalCostsTotal = (float) str_replace(',', '.', $additionalCostsRaw ?: '0');
                $kitSelectedCount = is_countable($selectedProducts) ? count($selectedProducts) : 0;
                $kitSelectedQty = 0;
                foreach ($selectedProducts as $sp) { $kitSelectedQty += (int)($sp['quantity'] ?? 1); }
            @endphp

            <!-- ETAPA 2: Configuração Completa do Kit (redesenhado) -->
            <div class="flex-1 animate-fadeIn">
                <div class="create-kit-step2 grid grid-cols-1 xl:grid-cols-12 gap-5">

                    <!-- ========== COLUNA ESQUERDA (8/12) ========== -->
                    <div class="create-kit-step2-left xl:col-span-8 space-y-5">

                        <!-- ===== CARD 1: HERO / NOME DO KIT ===== -->
                        <div class="step2-card relative overflow-hidden rounded-2xl border border-slate-700/60 shadow-2xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 via-purple-600/15 to-pink-600/15"></div>
                            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-gradient-to-br from-purple-500/30 to-pink-500/20 blur-3xl"></div>
                            <div class="absolute -bottom-12 -left-12 w-48 h-48 rounded-full bg-gradient-to-br from-indigo-500/25 to-blue-500/15 blur-3xl"></div>

                            <div class="relative p-5 backdrop-blur-xl bg-slate-900/55">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                        <i class="bi bi-boxes text-white text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-white">Identificação do Kit</h3>
                                        <p class="text-xs text-slate-400">Nome editável com geração automática</p>
                                    </div>
                                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-purple-200 text-[11px] font-bold border border-purple-400/30">
                                        <i class="bi bi-magic"></i> Smart
                                    </span>
                                </div>

                                <label for="name" class="text-[11px] font-bold uppercase tracking-wider text-purple-300/80 mb-1.5 block">Nome do Kit</label>
                                <div class="flex gap-2">
                                    <input type="text"
                                        wire:model="name"
                                        id="name"
                                        class="flex-1 px-4 py-3 rounded-xl bg-slate-950/60 border border-slate-700/60 text-slate-100 placeholder-slate-500 text-sm font-semibold focus:border-purple-500 focus:ring-2 focus:ring-purple-500/25 focus:outline-none transition-all"
                                        placeholder="Nome do kit...">
                                    <button type="button"
                                        wire:click="$set('name', '{{ addslashes($this->getAutoGeneratedName()) }}')"
                                        class="px-4 py-3 rounded-xl bg-gradient-to-br from-purple-500/30 to-indigo-500/30 hover:from-purple-500/50 hover:to-indigo-500/50 border border-purple-400/40 text-purple-200 transition-all group"
                                        title="Regenerar nome">
                                        <i class="bi bi-arrow-clockwise group-hover:rotate-180 transition-transform duration-500"></i>
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-200 text-[11px] font-semibold border border-indigo-400/30">
                                        <i class="bi bi-upc-scan"></i> Código auto
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-200 text-[11px] font-semibold border border-emerald-400/30">
                                        <i class="bi bi-tags-fill"></i> Categoria auto
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-200 text-[11px] font-semibold border border-blue-400/30">
                                        <i class="bi bi-card-text"></i> Descrição auto
                                    </span>
                                </div>

                                <input type="hidden" wire:model="product_code">
                                <input type="hidden" wire:model="category_id">
                                <input type="hidden" wire:model="description">
                            </div>
                        </div>

                        <!-- ===== CARD 2: STATS TRIO (KPIs) ===== -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="step2-stat rounded-2xl p-4 border border-slate-700/60 bg-gradient-to-br from-slate-900/85 to-slate-800/60 backdrop-blur-xl shadow-xl">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 rounded-xl bg-slate-700/60 flex items-center justify-center">
                                        <i class="bi bi-coin text-amber-300 text-sm"></i>
                                    </div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total custo</p>
                                </div>
                                <p class="text-xl font-black text-slate-100 leading-none">R$ {{ number_format($productsCostTotal, 2, ',', '.') }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">Soma dos preços de custo</p>
                            </div>
                            <div class="step2-stat rounded-2xl p-4 border border-purple-700/40 bg-gradient-to-br from-purple-900/40 to-slate-900/80 backdrop-blur-xl shadow-xl shadow-purple-900/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 rounded-xl bg-purple-500/25 flex items-center justify-center">
                                        <i class="bi bi-cash-stack text-purple-200 text-sm"></i>
                                    </div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-purple-300">Total venda</p>
                                </div>
                                <p class="text-xl font-black text-white leading-none">R$ {{ number_format($productsSaleTotal, 2, ',', '.') }}</p>
                                <p class="text-[10px] text-purple-300/70 mt-1">Soma dos preços de venda</p>
                            </div>
                            <div class="step2-stat relative rounded-2xl p-4 border border-blue-500/40 bg-gradient-to-br from-blue-600/35 via-indigo-600/25 to-blue-900/50 backdrop-blur-xl shadow-xl shadow-blue-900/30 overflow-hidden">
                                <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-blue-400/20 blur-2xl"></div>
                                <div class="relative">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-8 h-8 rounded-xl bg-blue-500/35 flex items-center justify-center">
                                            <i class="bi bi-bullseye text-blue-100 text-sm"></i>
                                        </div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Base final</p>
                                    </div>
                                    <p class="text-2xl font-black text-white leading-none">R$ {{ number_format($calculated_sale_price, 2, ',', '.') }}</p>
                                    <p class="text-[10px] text-blue-200/80 mt-1">price_sale + adicionais</p>
                                </div>
                            </div>
                        </div>

                        <!-- ===== CARD 3: CUSTOS ADICIONAIS ===== -->
                        <div class="step2-card rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900/85 via-amber-900/10 to-slate-900/85 backdrop-blur-xl shadow-2xl p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                                    <i class="bi bi-plus-circle-fill text-white text-base"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-white">Custos Adicionais</h3>
                                    <p class="text-xs text-slate-400">Frete, embalagem, taxas e afins</p>
                                </div>
                                <button type="button"
                                    wire:click="addAdditionalCostItem"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-500/25 hover:bg-amber-500/40 border border-amber-500/50 text-amber-100 text-xs font-bold transition shadow-md shadow-amber-500/10">
                                    <i class="bi bi-plus-lg"></i> Adicionar
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                @foreach($additionalCostItems as $costIndex => $costItem)
                                <div class="grid grid-cols-12 gap-2 items-center p-2 rounded-xl bg-slate-950/40 border border-slate-700/40">
                                    <div class="col-span-12 sm:col-span-7">
                                        <input type="text"
                                            wire:model.live="additionalCostItems.{{ $costIndex }}.description"
                                            placeholder="Descrição do custo"
                                            class="w-full px-3 py-2 border rounded-lg bg-slate-900/70 text-slate-100 text-sm border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none placeholder-slate-500" />
                                    </div>
                                    <div class="col-span-10 sm:col-span-4 relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-bold text-amber-300">R$</span>
                                        <input type="text"
                                            wire:model.live="additionalCostItems.{{ $costIndex }}.value"
                                            oninput="this.value = window.fmMoneyMask ? window.fmMoneyMask(this.value) : this.value"
                                            class="w-full pl-10 pr-2 py-2 border rounded-lg bg-slate-900/70 text-slate-100 text-sm font-semibold border-slate-700/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none placeholder-slate-500"
                                            placeholder="0,00" />
                                    </div>
                                    <div class="col-span-2 sm:col-span-1 text-right">
                                        @if(count($additionalCostItems) > 1)
                                        <button type="button"
                                            wire:click="removeAdditionalCostItem({{ $costIndex }})"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-300 hover:text-rose-200 hover:bg-rose-500/20 transition">
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

                        <!-- ===== CARD 4: COMPOSIÇÃO + PREÇO FINAL ===== -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <!-- Composição -->
                            <div class="step2-card rounded-2xl border border-emerald-700/40 bg-gradient-to-br from-slate-900/90 via-emerald-900/15 to-slate-900/90 backdrop-blur-xl shadow-2xl p-5">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                        <i class="bi bi-calculator-fill text-white text-base"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-white">Composição do Preço</h3>
                                        <p class="text-xs text-slate-400">Como o sugerido é calculado</p>
                                    </div>
                                </div>
                                <div class="space-y-1.5 text-sm">
                                    <div class="flex justify-between items-center rounded-lg bg-slate-950/45 border border-slate-700/40 px-3 py-2">
                                        <span class="text-slate-300 text-xs">Total custo</span>
                                        <span class="font-bold text-slate-100">R$ {{ number_format($productsCostTotal, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center rounded-lg bg-slate-950/45 border border-slate-700/40 px-3 py-2">
                                        <span class="text-slate-300 text-xs">Total venda</span>
                                        <span class="font-bold text-slate-100">R$ {{ number_format($productsSaleTotal, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center rounded-lg bg-slate-950/45 border border-slate-700/40 px-3 py-2">
                                        <span class="text-slate-300 text-xs">Custos adicionais</span>
                                        <span class="font-bold text-amber-300">R$ {{ number_format($additionalCostsTotal, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center rounded-lg bg-slate-950/45 border border-slate-700/40 px-3 py-2">
                                        <span class="text-slate-300 text-xs">Base (venda + adicionais)</span>
                                        <span class="font-bold text-blue-300">R$ {{ number_format($calculated_sale_price, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center rounded-lg bg-slate-950/45 border border-slate-700/40 px-3 py-2">
                                        <span class="text-slate-300 text-xs">Margem 5%</span>
                                        <span class="font-bold text-emerald-300">+ R$ {{ number_format(($calculated_sale_price ?? 0) * 0.05, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center rounded-xl bg-gradient-to-r from-emerald-500/25 to-teal-500/25 border border-emerald-400/50 px-3 py-3 mt-2 shadow-lg shadow-emerald-500/10">
                                        <span class="font-extrabold text-white text-sm">Preço sugerido</span>
                                        <span class="font-black text-emerald-200 text-lg">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preço Final -->
                            <div class="step2-card rounded-2xl border border-purple-700/40 bg-gradient-to-br from-slate-900/90 via-purple-900/20 to-slate-900/90 backdrop-blur-xl shadow-2xl p-5">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                        <i class="bi bi-tag-fill text-white text-base"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-white">Preço de Venda Final</h3>
                                        <p class="text-xs text-slate-400">Valor salvo no kit</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-base font-bold text-purple-300">R$</span>
                                            <input type="text"
                                                wire:model.live="real_sale_price"
                                                placeholder="{{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}"
                                                class="w-full pl-11 pr-3 py-3 border rounded-xl bg-slate-950/60 text-white text-lg font-black border-slate-700/50 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/25 focus:outline-none placeholder-slate-600" />
                                        </div>
                                        <button type="button"
                                            wire:click="usesSuggestedPrice"
                                            class="px-4 py-3 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition whitespace-nowrap">
                                            <i class="bi bi-magic mr-1"></i>Usar
                                        </button>
                                    </div>

                                    <div class="rounded-xl bg-gradient-to-r from-purple-500/15 to-pink-500/15 border border-purple-400/40 px-4 py-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-[10px] uppercase tracking-wider font-bold text-purple-300/80">Sugerido atual</p>
                                            <p class="text-lg font-black text-purple-200">R$ {{ number_format($this->suggestedSalePrice ?? 0, 2, ',', '.') }}</p>
                                        </div>
                                        <i class="bi bi-lightning-charge-fill text-purple-300 text-2xl"></i>
                                    </div>

                                    <p class="text-[11px] text-slate-400 flex items-start gap-1.5">
                                        <i class="bi bi-info-circle text-blue-400 mt-0.5"></i>
                                        Edite livremente ou clique em "Usar" para aplicar o sugerido automaticamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== COLUNA DIREITA (4/12) ========== -->
                    <div class="create-kit-step2-right xl:col-span-4 space-y-5">

                        <!-- Imagem do Kit (compacto) -->
                        <div class="step2-card relative overflow-hidden rounded-2xl border border-slate-700/60 shadow-2xl">
                            <div class="absolute inset-0 bg-gradient-to-br from-pink-600/20 via-purple-600/15 to-indigo-600/20"></div>
                            <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-pink-500/20 blur-3xl"></div>

                            <div class="relative p-4 backdrop-blur-xl bg-slate-900/55 flex flex-col">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-500 flex items-center justify-center shadow-lg shadow-pink-500/30 shrink-0">
                                        <i class="bi bi-image-fill text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-bold text-white leading-tight">Imagem do Kit</h3>
                                        <p class="text-[11px] text-slate-400 leading-tight">JPG, PNG • Máx 2MB</p>
                                    </div>
                                </div>

                                <div class="w-full overflow-hidden rounded-xl">
                                    <x-image-upload
                                        name="image"
                                        id="image"
                                        wire-model="image"
                                        title="Atualizar Imagem"
                                        description="Clique ou arraste"
                                        :new-image="$image"
                                        :existing-image="$kit->image ? asset('storage/products/' . $kit->image) : null"
                                        height="h-[170px] md:h-[200px]" />
                                </div>
                            </div>
                        </div>

                        <!-- Produtos Selecionados (resumo compacto) -->
                        <div class="step2-card rounded-2xl border border-amber-700/40 bg-gradient-to-br from-slate-900/90 via-amber-900/10 to-slate-900/90 backdrop-blur-xl shadow-2xl p-4">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30 shrink-0">
                                    <i class="bi bi-grid-3x3-gap-fill text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-white leading-tight">Itens do Kit</h3>
                                    <p class="text-[11px] text-slate-400 leading-tight">{{ $kitSelectedCount }} {{ $kitSelectedCount === 1 ? 'produto' : 'produtos' }} · {{ $kitSelectedQty }} un.</p>
                                </div>
                            </div>

                            @if(count($selectedProducts) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
                                @foreach($selectedProducts as $item)
                                <div class="rounded-lg border border-slate-700/60 bg-slate-950/50 hover:bg-slate-950/70 p-2 transition flex items-center gap-2">
                                    <img src="{{ !empty($item['image']) ? asset('storage/products/' . $item['image']) : asset('storage/products/product-placeholder.png') }}"
                                        alt="{{ $item['name'] ?? 'Produto' }}"
                                        class="w-9 h-9 rounded-md object-cover border border-slate-700 shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-bold text-white truncate leading-tight">{{ $item['name'] ?? 'Produto' }}</p>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[9px] font-bold bg-slate-700/60 text-slate-200">{{ $item['quantity'] ?? 1 }}x</span>
                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[9px] font-bold bg-purple-500/25 text-purple-200">R$ {{ number_format($item['salePrice'] ?? 0, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-xl border border-dashed border-slate-700/60 bg-slate-950/30 px-4 py-5 text-center">
                                <i class="bi bi-cart-x text-2xl text-slate-500"></i>
                                <p class="text-xs text-slate-400 mt-1.5">Nenhum produto selecionado</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estilo para scrollbar customizada -->
            <style>
                .step2-card {
                    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
                }
                .step2-card:hover {
                    transform: translateY(-2px);
                    border-color: rgba(139, 92, 246, 0.45);
                }
                .step2-stat {
                    transition: transform .25s ease, box-shadow .25s ease;
                }
                .step2-stat:hover {
                    transform: translateY(-3px) scale(1.01);
                }

                /* Ultrawide — usa toda a largura */
                @media (min-width: 1441px) {
                    .create-kit-step2 { gap: 1.5rem; }
                }

                /* iPad portrait — empilha colunas com gap menor */
                @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
                    .create-kit-step2 { gap: 1rem; }
                    .step2-card { padding: 1rem !important; }
                }

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