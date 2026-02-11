{{-- PUBLISH PRODUCT — FLUXO POR STEPS (1. Produtos | 2. Catálogo | 3. Config) --}}
<div class="min-h-screen flex flex-col" x-data="{ autoSearched: false }">
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
        }

        .product-card-modern {
            cursor: pointer;
            user-select: none;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }
    </style>

    {{-- HEADER COM BOTÕES DE NAVEGAÇÃO --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-amber-50/90 to-yellow-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-yellow-900/20 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="relative px-6 py-5">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-5">
                    <a href="{{ route('mercadolivre.products') }}" class="w-12 h-12 rounded-xl bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-amber-50 dark:hover:bg-slate-700 transition-all">
                        <i class="bi bi-arrow-left text-xl text-amber-600 dark:text-amber-400"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-300 dark:to-orange-300 bg-clip-text text-transparent">Publicar no Mercado Livre</h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                            @if($currentStep === 1) Passo 1: Selecione os produtos
                            @elseif($currentStep === 2) Passo 2: Catálogo ML (opcional)
                            @else Passo 3: Valores e configuração
                            @endif
                        </p>
                    </div>
                </div>
                {{-- Step indicator --}}
                <div class="flex items-center gap-2">
                    @foreach([1 => 'Produtos', 2 => 'Catálogo', 3 => 'Config'] as $step => $label)
                    <div class="flex items-center">
                        <button type="button" wire:click="goToStep({{ $step }})"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-sm transition-all
                                           {{ $currentStep === $step ? 'bg-amber-500 text-white shadow-lg' : ($currentStep > $step ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500') }}">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center {{ $currentStep >= $step ? 'bg-white/20' : '' }}">
                                @if($currentStep > $step)<i class="bi bi-check-lg text-xs"></i>@else{{ $step }}@endif
                            </span>
                            {{ $label }}
                        </button>
                        @if($step < 3)<i class="bi bi-chevron-right text-slate-400 text-xs"></i>@endif
                    </div>
                    @endforeach
                </div>
                {{-- Botões de navegação no header --}}
                <div class="flex items-center gap-2 ml-auto">
                    @if($currentStep === 2)
                    <button type="button" wire:click="searchCatalog" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold hover:shadow-lg transition-all disabled:opacity-50">
                        <i class="bi bi-search" wire:loading.remove wire:target="searchCatalog"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="searchCatalog"></i>
                        Buscar Catálogo
                    </button>
                    @endif
                    @if($currentStep > 1)
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </button>
                    @endif
                    @if($currentStep < 3)
                        @if($currentStep===1 && $this->hasSelectedProducts())
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold shadow-lg transition-all">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                        @elseif($currentStep === 2)
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold shadow-lg transition-all">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                        @endif
                        @else
                        <button type="submit" form="publish-form"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold shadow-xl transition-all">
                            <i class="bi bi-rocket-takeoff-fill"></i> Publicar no ML
                        </button>
                        @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 1: SELEÇÃO DE PRODUTOS --}}
    @if($currentStep === 1)
    <div class="flex-1 flex">
        <div class="w-3/4 bg-white dark:bg-zinc-800 rounded-2xl border border-slate-200 dark:border-zinc-700 flex flex-col overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-box text-amber-500 mr-3"></i>Selecionar Produtos
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Apenas produtos prontos para publicação (com EAN, imagem, preço)</p>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Buscar por nome, código ou EAN..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <select wire:model.live="selectedCategory"
                        class="px-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                        <option value="">Todas as categorias</option>
                        @foreach($this->categories as $category)
                        <option value="{{ $category->id_category ?? $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                @if($this->filteredProducts->isEmpty())
                <div class="flex flex-col items-center justify-center h-full">
                    <i class="bi bi-box-seam text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">Nenhum produto pronto</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-center text-sm">Cadastre produtos com: nome, preço, estoque, imagem e código de barras (EAN)</p>
                </div>
                @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($this->filteredProducts as $product)
                    @php $isSelected = $this->isProductSelected($product->id); @endphp
                    <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                        wire:click="toggleProduct({{ $product->id }})" wire:key="p-{{ $product->id }}">
                        <div class="btn-action-group flex gap-2">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all {{ $isSelected ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-slate-700 border-gray-300 text-transparent' }}">
                                @if($isSelected)<i class="bi bi-check text-sm"></i>@endif
                            </div>
                        </div>
                        <div class="product-img-area">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                            <span class="badge-product-code"><i class="bi bi-upc-scan"></i> {{ $product->product_code }}</span>
                            <span class="badge-quantity"><i class="bi bi-stack"></i> {{ $product->stock_quantity }}</span>
                            @if($product->category)
                            <div class="category-icon-wrapper"><i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i></div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="product-title">{{ ucwords($product->name) }}</div>
                            <div class="price-area mt-2 flex flex-col gap-1">
                                <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}</span>
                                <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        <div class="w-1/4 flex flex-col bg-white dark:bg-zinc-800 rounded-2xl border border-slate-200 dark:border-zinc-700 ml-4 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white"><i class="bi bi-cart-check text-amber-500 mr-2"></i>Selecionados ({{ count($selectedProducts) }})</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                @if(empty($selectedProducts))
                <div class="text-center py-8">
                    <i class="bi bi-cart-x text-4xl text-slate-300 mb-3"></i>
                    <p class="text-sm text-slate-500">Clique nos produtos à esquerda</p>
                </div>
                @else
                @foreach($selectedProducts as $idx => $p)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 mb-2">
                    <img src="{{ $p['image_url'] ?? '' }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $p['name'] }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <label class="text-[10px] text-slate-500 font-medium">R$</label>
                            <input type="number" wire:model.live="selectedProducts.{{ $idx }}.price_sale" step="0.01" min="0"
                                class="w-20 py-1 px-1.5 text-xs rounded border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2: CATÁLOGO ML --}}
    @if($currentStep === 2)
    <div class="flex-1 flex gap-6">
        {{-- Coluna: Resultados (4 por linha, imagens menores) --}}
        @if(!empty($catalogResults))
        <div class="flex-shrink-0 w-96 lg:w-[420px]">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sticky top-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">{{ count($catalogResults) }} resultado(s)</h3>
                <div class="grid grid-cols-2 gap-3 max-h-[60vh] overflow-y-auto">
                    @foreach($catalogResults as $catalogProduct)
                    @php
                    $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                    $domainId = $catalogProduct['domain_id'] ?? '';
                    $isSelected = $catalogProductId === $cProductId;
                    $imgUrl = $this->getCatalogResultImage($catalogProduct);
                    $price = $this->getCatalogResultPrice($catalogProduct);
                    @endphp
                    <div wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $domainId }}')"
                        class="flex flex-col rounded-lg cursor-pointer transition-all border-2 overflow-hidden {{ $isSelected ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300' }}">
                        <div class="h-20 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden">
                            @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="" class="max-w-full max-h-full object-contain p-1">
                            @else
                            <i class="bi bi-box text-2xl text-slate-400"></i>
                            @endif
                        </div>
                        <div class="p-2 flex-1 min-h-0">
                            <p class="font-semibold text-xs text-slate-900 dark:text-white line-clamp-2">{{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}</p>
                            @if($price)
                            <p class="text-sm font-bold text-teal-600 dark:text-teal-400 mt-1">R$ {{ number_format($price, 2, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Coluna: Informações COMPLETAS do catálogo selecionado --}}
        @if($catalogProductId && !empty($catalogProductData))
        <div class="flex-1 min-w-0 grid grid-cols-1 xl:grid-cols-2 gap-4">
            {{-- Título, Preço e link --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border-2 border-teal-200 dark:border-teal-800 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <h3 class="text-xl font-bold text-teal-800 dark:text-teal-300 flex items-center gap-2">
                            <i class="bi bi-patch-check-fill text-2xl"></i> Catálogo Selecionado
                        </h3>
                        <div class="flex items-center gap-3">
                            @if($catalogPrice)
                            <span class="text-2xl font-black text-teal-600 dark:text-teal-400">R$ {{ number_format($catalogPrice, 2, ',', '.') }}</span>
                            @endif
                            <a href="https://www.mercadolivre.com.br/p/{{ $catalogProductId }}" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-teal-500 text-white text-sm font-bold hover:bg-teal-600">
                                <i class="bi bi-box-arrow-up-right"></i> Ver no ML
                            </a>
                            <button type="button" wire:click="clearCatalogProduct"
                                class="text-sm text-red-600 hover:text-red-700 font-medium">
                                <i class="bi bi-x-circle mr-1"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $catalogProductName ?: '—' }}</p>
                </div>
            </div>

            {{-- Galeria --}}
            @if(!empty($catalogPictures))
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-b">
                    <h3 class="text-sm font-bold text-blue-700 dark:text-blue-400"><i class="bi bi-images mr-1"></i> Galeria ({{ count($catalogPictures) }})</h3>
                </div>
                <div class="p-3 flex gap-2 overflow-x-auto">
                    @foreach($catalogPictures as $idx => $pic)
                    @php $picUrl = $pic['secure_url'] ?? $pic['url'] ?? ''; @endphp
                    @if($picUrl)
                    <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                        <img src="{{ $picUrl }}" alt="Foto {{ $idx + 1 }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Descrição --}}
            @if($catalogDescription)
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border-b">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300"><i class="bi bi-file-text mr-1"></i> Descrição</h3>
                </div>
                <div class="p-4 max-h-48 overflow-y-auto">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $catalogDescription }}</p>
                </div>
            </div>
            @endif

            {{-- Atributos --}}
            @if(!empty($catalogAttributes))
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden xl:col-span-2">
                <div class="px-4 py-2 bg-purple-50 dark:bg-purple-900/20 border-b">
                    <h3 class="text-sm font-bold text-purple-700 dark:text-purple-400"><i class="bi bi-sliders mr-1"></i> Atributos ({{ count($catalogAttributes) }})</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        @foreach($catalogAttributes as $attr)
                        @if(!empty($attr['value_id']) || !empty($attr['value_name']))
                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">{{ $attr['name'] }}</p>
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $attr['value_name'] ?: '—' }}</p>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="flex-1 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 min-h-[200px]">
            <div class="text-center p-6">
                @if(empty($catalogResults))
                <i class="bi bi-search text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Clique em "Buscar Catálogo" no header para buscar pelo EAN</p>
                @else
                <i class="bi bi-cursor text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Selecione um resultado à esquerda para ver os detalhes</p>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- STEP 3: CONFIGURAÇÃO COMPLETA (moderno, ícones, taxas, preço sugerido) --}}
    @if($currentStep === 3)
    <form id="publish-form" wire:submit.prevent="publishProduct" class="flex-1">
        @php
        $basePrice = (float)($publishPrice ?: 0) ?: $this->getTotalProductsPrice();
        $mlFee = match($listingType) { 'gold_special' => 0.16, 'gold_pro' => 0.17, 'gold' => 0.13, default => 0.11 };
        $mlFeeAmount = $basePrice * $mlFee;
        $shippingCost = $freeShipping ? 15.00 : 0;
        $netAmount = $basePrice - $mlFeeAmount - $shippingCost;
        $suggestedPrice = $this->getSuggestedPrice();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna 1: Produtos + Calculadora --}}
            <aside class="space-y-6">
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                        <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                            <i class="bi bi-box-seam text-lg"></i> Produtos ({{ count($selectedProducts) }})
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($selectedProducts as $p)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <img src="{{ $p['image_url'] ?? '' }}" class="w-14 h-14 rounded-lg object-cover" onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-slate-900 dark:text-white truncate">{{ $p['name'] }}</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">R$ {{ number_format($p['price_sale'] ?? $p['unit_cost'] ?? 0, 2, ',', '.') }} · Est: {{ $p['stock_quantity'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Calculadora de taxas --}}
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-emerald-200 dark:border-emerald-800">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                            <i class="bi bi-calculator-fill text-lg"></i> Resumo de Taxas
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Taxa ML ({{ $mlFee * 100 }}%)</span>
                            <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($mlFeeAmount, 2, ',', '.') }}</span>
                        </div>
                        @if($freeShipping)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Frete grátis</span>
                            <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($shippingCost, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Valor Líquido</span>
                            <span class="text-lg font-black {{ $netAmount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">R$ {{ number_format($netAmount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Coluna 2: Título, Preço, Tipo de Anúncio --}}
            <div class="space-y-6">
                {{-- Título da Publicação --}}
                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 overflow-hidden shadow-lg">
                    <div class="p-5">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                <i class="bi bi-textarea-t text-xl text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">Título que será publicado</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white leading-snug break-words">{{ $this->getFinalTitle() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($catalogProductName)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-xs font-bold text-teal-700 dark:text-teal-300">
                                <i class="bi bi-patch-check-fill"></i> Do Catálogo ML
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400">
                                <i class="bi bi-box"></i> Produto Original
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <i class="bi bi-currency-dollar text-lg"></i> Preço e Quantidade
                        </h4>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Preço do Anúncio</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 font-bold">R$</span>
                                    <input type="number" wire:model.live="publishPrice" step="0.01" min="0.01"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10 text-slate-900 dark:text-white font-bold text-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-[10px] text-slate-500 uppercase">Soma produtos</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($this->getTotalProductsPrice(), 2, ',', '.') }}</p>
                                </div>
                            </div>
                            @if($catalogPrice)
                            <p class="text-xs text-teal-600 dark:text-teal-400 mt-1"><i class="bi bi-patch-check"></i> Catálogo: R$ {{ number_format($catalogPrice, 2, ',', '.') }}</p>
                            @endif
                        </div>
                        @if($suggestedPrice > 0)
                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Preço Sugerido</p>
                                    <p class="text-xl font-black text-blue-700 dark:text-blue-300">R$ {{ number_format($suggestedPrice, 2, ',', '.') }}</p>
                                </div>
                                <button type="button" wire:click="applySuggestedPrice"
                                    class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold transition-all">
                                    <i class="bi bi-check2-square mr-1"></i> Aplicar
                                </button>
                            </div>
                        </div>
                        @endif
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Quantidade</label>
                            <input type="number" wire:model="publishQuantity" min="1"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-bold">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                        <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                            <i class="bi bi-bookmark-star-fill text-lg"></i> Tipo de Anúncio
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">Valores fixos do Mercado Livre (não vêm da API) · Definem taxa e exposição</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(['gold_special' => ['Clássico', '16%', 'bi-trophy-fill', 'Destaque na busca · Boa visibilidade · Conversões médias'], 'gold_pro' => ['Premium', '17%', 'bi-star-fill', 'Máxima visibilidade · Topo das buscas · Mais vendas'], 'gold' => ['Gold', '13%', 'bi-star', 'Visibilidade intermediária · Bom custo-benefício'], 'free' => ['Grátis', '11%', 'bi-bag', 'Taxa mais baixa · Menos destaque · Básico']] as $key => $data)
                            <label class="cursor-pointer">
                                <div class="p-3 rounded-xl border-2 transition-all {{ $listingType === $key ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 shadow-md' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300' }}">
                                    <input type="radio" wire:model.live="listingType" value="{{ $key }}" class="sr-only">
                                    <i class="bi {{ $data[2] }} block text-2xl mb-1 {{ $listingType === $key ? 'text-amber-600' : 'text-slate-400' }}"></i>
                                    <p class="font-bold text-sm {{ $listingType === $key ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $data[0] }}</p>
                                    <p class="text-xs font-semibold {{ $listingType === $key ? 'text-amber-600' : 'text-slate-500' }}">Taxa: {{ $data[1] }}</p>
                                    <p class="text-[9px] text-slate-400 mt-1 leading-tight">{{ $data[3] ?? '' }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna 3: Envio, Categoria --}}
            <div class="space-y-6">
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                        <h4 class="font-bold text-teal-700 dark:text-teal-400 flex items-center gap-2">
                            <i class="bi bi-truck text-lg"></i> Envio e Logística
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">📦 Mercado Envios · ⏱️ Prazo 2–5 dias úteis · 📍 Rastreamento incluído</p>
                    </div>
                    <div class="p-4 space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $freeShipping ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300' }}">
                            <input type="checkbox" wire:model.live="freeShipping" class="w-5 h-5 rounded text-amber-500">
                            <div class="flex-1">
                                <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-truck-front-fill text-teal-500 text-lg"></i> Frete Grátis</p>
                                <p class="text-xs text-slate-500 leading-relaxed">💰 Você paga ~R$ 15,00 por venda<br>⭐ Destaque "FRETE GRÁTIS" na busca<br>📈 Aumenta conversão em até 30%</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $localPickup ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
                            <input type="checkbox" wire:model.live="localPickup" class="w-5 h-5 rounded text-amber-500">
                            <div class="flex-1">
                                <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-shop-window text-blue-500 text-lg"></i> Retirada Local</p>
                                <p class="text-xs text-slate-500 leading-relaxed">💵 Sem custo adicional<br>🏪 Cliente retira no endereço cadastrado<br>⚡ Atendimento mais rápido</p>
                            </div>
                        </label>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2"><i class="bi bi-info-circle mr-1"></i>Informações de Envio</p>
                            <ul class="space-y-1 text-[11px] text-slate-500">
                                <li>✓ Modalidade: <strong>Mercado Envios Full</strong></li>
                                <li>✓ Proteção: <strong>Garantia de entrega</strong></li>
                                <li>✓ Embalagem: <strong>Responsabilidade do vendedor</strong></li>
                                <li>✓ Coleta: <strong>Agendada automaticamente</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Condição e Garantia --}}
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <i class="bi bi-box-seam text-lg"></i> Condição e Garantia
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Condição do produto</label>
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $productCondition === 'new' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                    <input type="radio" wire:model.live="productCondition" value="new" class="sr-only">
                                    <i class="bi bi-star-fill text-blue-500"></i>
                                    <span class="font-semibold text-sm">Novo</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $productCondition === 'used' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                    <input type="radio" wire:model.live="productCondition" value="used" class="sr-only">
                                    <i class="bi bi-box text-amber-500"></i>
                                    <span class="font-semibold text-sm">Usado</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Garantia (opcional)</label>
                            <input type="text" wire:model="warranty" placeholder="Ex: 90 dias, 1 ano"
                                class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <i class="bi bi-tag-fill text-lg"></i> Categoria ML
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <input type="text" wire:model.live.debounce.500ms="categorySearch" placeholder="Buscar categoria..."
                            class="w-full px-4 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                        <select wire:model.live="mlCategoryId"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium">
                            <option value="">Selecione</option>
                            @foreach($mlCategories as $cat)
                            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>