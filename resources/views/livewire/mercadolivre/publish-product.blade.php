{{-- ╔══════════════════════════════════════════════════════════════════════╗
    ║  PUBLISH PRODUCT — SINGLE SCREEN BENTO GRID DASHBOARD              ║
    ║  Layout: h-screen, overflow-hidden, CSS Grid Areas                  ║
    ║  3 Colunas: Status | Produto+Catálogo | Config+Resumo              ║
    ╚══════════════════════════════════════════════════════════════════════╝ --}}

<div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950"
     x-data="{ autoSearched: false }"
     x-init="@if($product->barcode) if (!autoSearched) { autoSearched = true; $nextTick(() => $wire.searchCatalog()); } @endif">

    {{-- ═══ HEADER com componente sales-header ═══ --}}
    <x-sales-header
        title="Publicar no Mercado Livre"
        description="Painel de publicação · {{ $product->name }}"
        :backRoute="route('mercadolivre.products')">
        <x-slot:breadcrumb>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('mercadolivre.products') }}" class="hover:text-blue-600 transition-colors">Produtos ML</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Publicar</span>
            </div>
        </x-slot:breadcrumb>
        <x-slot:actions>
            <div class="flex items-center gap-3">
                {{-- Resumo Rápido --}}
                @php
                    $basePrice = (float)$publishPrice ?: ($product->price_sale ?? $product->price);
                    $mlFee = $listingType === 'gold_special' ? 0.16 : ($listingType === 'gold_pro' ? 0.17 : ($listingType === 'gold' ? 0.13 : 0.11));
                    $mlFeeAmount = $basePrice * $mlFee;
                    $shippingCost = $freeShipping ? 15.00 : 0;
                    $netAmount = $basePrice - $mlFeeAmount - $shippingCost;
                    $isKit = count($selectedProducts) > 1;
                    $condition = $product->condition ?? 'new';
                    $conditionLabel = $condition === 'new' ? 'Novo' : 'Usado';
                    $listingTypeLabel = $listingType === 'gold_special' ? 'Premium' : ($listingType === 'gold_pro' ? 'Clássico' : ($listingType === 'gold' ? 'Gold' : 'Grátis'));
                @endphp
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-700 border border-slate-200 dark:border-slate-600">
                    <div class="text-right">
                        <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase">Preço</p>
                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format($basePrice, 2, ',', '.') }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-300 dark:bg-slate-600"></div>
                    <div class="text-right">
                        <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase">Líquido</p>
                        <p class="text-sm font-black {{ $netAmount > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">R$ {{ number_format($netAmount, 2, ',', '.') }}</p>
                    </div>
                </div>
                
                {{-- Condição do Produto --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $condition === 'new' ? 'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400' : 'bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400' }} text-xs font-semibold">
                    <i class="bi {{ $condition === 'new' ? 'bi-star-fill' : 'bi-box-seam' }}"></i> {{ $conditionLabel }}
                </span>
                
                @if($isKit)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-400 text-xs font-semibold">
                        <i class="bi bi-box-seam-fill"></i> Kit ({{ count($selectedProducts) }} itens)
                    </span>
                @endif
                
                @if($freeShipping)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                        <i class="bi bi-truck-front-fill"></i> Frete Grátis
                    </span>
                @endif
                
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-400 text-xs font-semibold">
                    <i class="bi bi-award-fill"></i> {{ $listingTypeLabel }}
                </span>
                
                @if($catalogProductId)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-400 text-xs font-semibold">
                        <i class="bi bi-patch-check-fill"></i> Catálogo ML
                    </span>
                @endif
                
                <a href="{{ $catalogProductId ? 'https://www.mercadolivre.com.br/p/' . $catalogProductId : 'https://www.mercadolivre.com.br/' }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-400 text-xs font-semibold hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-all">
                    <i class="bi bi-box-arrow-up-right"></i> {{ $catalogProductId ? 'Ver no ML' : 'Catálogo ML' }}
                </a>
                
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Conectado</span>
                </div>
                
                {{-- Botões de Ação --}}
                <div class="flex items-center gap-2 ml-4 pl-4 border-l-2 border-slate-300 dark:border-slate-600">
                    <a href="{{ route('mercadolivre.products') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="button" onclick="document.getElementById('publish-form').requestSubmit()"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-gradient-to-r from-yellow-500 via-amber-500 to-orange-500 hover:from-yellow-600 hover:via-amber-600 hover:to-orange-600 text-white text-sm font-bold shadow-md hover:shadow-lg transition-all">
                        <i class="bi bi-rocket-takeoff-fill"></i> Publicar
                    </button>
                </div>
            </div>
        </x-slot:actions>
    </x-sales-header>

    {{-- ═══ BODY — BENTO GRID 2 COLUNAS com área horizontal de configs no topo ═══ --}}
    <form id="publish-form" wire:submit.prevent="publishProduct" class="flex-1 grid gap-4 p-4"
          style="grid-template-columns: 280px minmax(700px, 1fr); grid-auto-rows: minmax(0, 1fr);">

        {{-- ╔══════════════════════════════════════════════════════════════╗
             ║  COLUNA ESQUERDA — Produto + Status (250px)                 ║
             ╚══════════════════════════════════════════════════════════════╝ --}}
        <aside class="flex flex-col gap-3 overflow-y-auto scrollbar-thin pr-1">

            {{-- 🎁 SEÇÃO UNIFICADA: PRODUTO PRINCIPAL + PRODUTOS DO KIT --}}
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                {{-- Header --}}
                <div class="px-3.5 py-2.5 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-box-seam text-sm"></i> Produtos da Publicação
                            @if(count($selectedProducts) > 1)
                                <span class="px-1.5 py-0.5 rounded-full bg-purple-500 text-white text-[9px] font-bold">KIT</span>
                            @endif
                        </h4>
                        <span class="text-xs font-bold text-purple-600 dark:text-purple-400">{{ count($selectedProducts) }}</span>
                    </div>
                </div>

                {{-- Conteúdo --}}
                <div class="p-3 space-y-2">
                    {{-- Botão Adicionar Produto --}}
                    <button type="button" wire:click="toggleProductSelector"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-xs font-bold transition-all hover:shadow-md">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>{{ $showProductSelector ? 'Fechar Seletor' : 'Adicionar Produto ao Kit' }}</span>
                    </button>

                    {{-- Lista UNIFICADA de Produtos (Principal + Adicionados) --}}
                    <div class="space-y-2 max-h-96 overflow-y-auto scrollbar-thin">
                        @foreach($selectedProducts as $idx => $prod)
                            <div class="rounded-lg bg-white dark:bg-slate-900 border {{ $idx === 0 ? 'border-emerald-300 dark:border-emerald-700 shadow-sm' : 'border-slate-200 dark:border-slate-700' }} overflow-hidden">
                                {{-- Badge de produto principal --}}
                                @if($idx === 0)
                                    <div class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-200 dark:border-emerald-800">
                                        <span class="text-[8px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1">
                                            <i class="bi bi-star-fill"></i> Produto Principal
                                        </span>
                                    </div>
                                @endif

                                <div class="p-2 flex items-center gap-2">
                                    {{-- Imagem --}}
                                    @if(isset($prod['image_url']) && $prod['image_url'])
                                        <img src="{{ $prod['image_url'] }}" class="w-16 h-16 rounded-md object-cover flex-shrink-0">
                                    @else
                                        <div class="w-16 h-16 rounded-md bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-box text-slate-400 text-xl"></i>
                                        </div>
                                    @endif

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $prod['name'] }}</p>
                                        <div class="flex items-center gap-2 text-[9px] text-slate-500 mt-0.5">
                                            <span class="font-mono">#{{ $prod['product_code'] ?? $prod['id'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-[9px] text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-tag-fill text-emerald-500"></i>
                                                R$ {{ number_format($prod['price_sale'] ?? $prod['unit_cost'] ?? 0, 2, ',', '.') }}
                                            </span>
                                            <span class="text-[9px] text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-box-seam-fill text-blue-500"></i>
                                                Estoque: <strong>{{ $prod['stock_quantity'] }}</strong>
                                            </span>
                                            <span class="text-[9px] text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-calculator text-purple-500"></i>
                                                Qtd: <strong>{{ $prod['quantity'] }}x</strong>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Botão remover (só se tiver mais de 1 produto) --}}
                                    @if(count($selectedProducts) > 1)
                                        <button type="button" wire:click="removeProduct({{ $idx }})"
                                                class="flex-shrink-0 w-7 h-7 rounded-md bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 transition-all flex items-center justify-center"
                                                title="Remover produto">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Custo Total dos Produtos --}}
                    @if(count($selectedProducts) > 1)
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-2.5 mt-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-calculator-fill text-blue-600 dark:text-blue-400 text-xs"></i>
                                    <span class="text-[9px] font-bold text-blue-700 dark:text-blue-400 uppercase">Custo Total do Kit</span>
                                </div>
                                <span class="text-lg font-black text-blue-700 dark:text-blue-400">R$ {{ number_format($this->getTotalProductsPrice(), 2, ',', '.') }}</span>
                            </div>
                            <p class="text-[8px] text-blue-600 dark:text-blue-500 mt-1">
                                Soma: {{ count($selectedProducts) }} produto(s) × quantidade
                            </p>
                        </div>
                    @endif

                    {{-- Quantidade Disponível (Para Kits) --}}
                    @if(count($selectedProducts) > 1)
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-2.5 mt-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-box-seam-fill text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                    <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">Kits Disponíveis</span>
                                </div>
                                <span class="text-lg font-black text-emerald-700 dark:text-emerald-400">{{ $this->getAvailableQuantity() }}</span>
                            </div>
                            <p class="text-[8px] text-emerald-600 dark:text-emerald-500 mt-1">
                                Calculado pelo estoque mínimo disponível entre todos os produtos
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 📋 Meta Dados --}}
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 space-y-2.5">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Dados do Produto</h4>
                @if($product->barcode)
                <div class="flex items-center gap-2 p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/40">
                    <i class="bi bi-upc text-blue-500 text-sm"></i>
                    <span class="text-xs font-mono font-bold text-blue-700 dark:text-blue-400">{{ $product->barcode }}</span>
                    <div wire:loading wire:target="searchCatalog" class="ml-auto">
                        <i class="bi bi-arrow-repeat animate-spin text-blue-500 text-xs"></i>
                    </div>
                </div>
                @endif
                @if($product->category)
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <i class="bi bi-tag-fill text-amber-500 text-xs"></i>
                    <span class="truncate">{{ $product->category->name }}</span>
                </div>
                @endif
                @if($product->brand)
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <i class="bi bi-award-fill text-purple-500 text-xs"></i>
                    <span>{{ $product->brand }}</span>
                </div>
                @endif
                @if($product->sku)
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <i class="bi bi-hash text-slate-400 text-xs"></i>
                    <span class="font-mono">{{ $product->sku }}</span>
                </div>
                @endif
            </div>

            {{-- ✅ Checklist --}}
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 space-y-2">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Checklist</h4>
                @php
                    $checks = [
                        ['label' => 'Categoria ML', 'ok' => !empty($mlCategoryId)],
                        ['label' => 'Catálogo ML', 'ok' => !empty($catalogProductId)],
                        ['label' => 'Imagens', 'ok' => !empty($selectedPictures)],
                        ['label' => 'Preço > 0', 'ok' => (float)$publishPrice > 0],
                        ['label' => 'Quantidade', 'ok' => $publishQuantity > 0],
                    ];
                    $completedCount = collect($checks)->where('ok', true)->count();
                @endphp
                {{-- Barra de progresso --}}
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex-1 h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-green-400 transition-all duration-500"
                             style="width: {{ ($completedCount / count($checks)) * 100 }}%"></div>
                    </div>
                    <span class="text-xs font-bold {{ $completedCount === count($checks) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">{{ $completedCount }}/{{ count($checks) }}</span>
                </div>
                @foreach($checks as $c)
                <div class="flex items-center gap-2 py-1">
                    @if($c['ok'])
                        <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ $c['label'] }}</span>
                    @else
                        <i class="bi bi-circle text-slate-400 dark:text-slate-600 text-sm"></i>
                        <span class="text-xs text-slate-500 dark:text-slate-500">{{ $c['label'] }}</span>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- 🔍 Resultados Catálogo (se houver) --}}
            @if(!empty($catalogResults) && !$catalogProductId)
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-800 p-3 space-y-2">
                <h4 class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                    <i class="bi bi-search"></i> {{ count($catalogResults) }} resultado(s)
                </h4>
                @foreach($catalogResults as $catalogProduct)
                    @php
                        $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                        $domainId = $catalogProduct['domain_id'] ?? '';
                        $isSelected = $catalogProductId === $cProductId;
                    @endphp
                    <div wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $domainId }}')"
                         class="flex items-center gap-2 p-2 rounded-lg cursor-pointer transition-all text-xs
                                {{ $isSelected
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-500'
                                    : 'bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400' }}">
                        @if(isset($catalogProduct['thumbnail']) || isset($catalogProduct['picture']))
                            <img src="{{ $catalogProduct['thumbnail'] ?? $catalogProduct['picture'] }}" class="w-10 h-10 rounded-md object-cover flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-md bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-box text-slate-400"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold text-slate-900 dark:text-white truncate">{{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}</p>
                            <p class="text-[9px] font-mono text-slate-500">{{ $cProductId }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Loading Skeleton --}}
            <div wire:loading wire:target="searchCatalog" class="rounded-xl bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-800 p-3 space-y-2">
                <div class="flex items-center gap-2 text-[10px] font-bold text-blue-600 dark:text-blue-400">
                    <i class="bi bi-arrow-repeat animate-spin"></i> Buscando no catálogo...
                </div>
                <div class="space-y-2">
                    <div class="h-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-full animate-pulse w-full"></div>
                    <div class="h-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-full animate-pulse w-3/4"></div>
                    <div class="h-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-full animate-pulse w-1/2"></div>
                </div>
            </div>
        </aside>

        {{-- ╔══════════════════════════════════════════════════════════════╗
             ║  ÁREA PRINCIPAL — Configurações horizontal + Conteúdo        ║
             ║  Topo: Config side-by-side | Baixo: Catálogo/Categoria      ║
             ╚══════════════════════════════════════════════════════════════╝ --}}
        <div class="overflow-y-auto scrollbar-thin pr-1">
            {{-- ═══ ÁREA HORIZONTAL DE CONFIGURAÇÕES (Full width no topo) ═══ --}}
            <div class="mb-4 grid grid-cols-5 gap-3">
                
                {{-- 💰 CALCULADORA DE PREÇO ML --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-3 py-2 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-emerald-200 dark:border-emerald-800">
                        <h4 class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-calculator text-sm"></i> Calculadora ML
                        </h4>
                    </div>
                    <div class="p-3 space-y-2.5">
                        {{-- Taxas e Custos --}}
                        @php
                            // Preço base: soma de todos os produtos ou preço do anúscio
                            $totalProductsPrice = $this->getTotalProductsPrice();
                            $basePrice = (float)$publishPrice ?: $totalProductsPrice;
                            
                            $mlFee = $listingType === 'gold_special' ? 0.16 : ($listingType === 'gold_pro' ? 0.17 : ($listingType === 'gold' ? 0.13 : 0.11));
                            $mlFeeAmount = $basePrice * $mlFee;
                            $shippingCost = $freeShipping ? 15.00 : 0;
                            $netAmount = $basePrice - $mlFeeAmount - $shippingCost;
                            
                            // Preço sugerido baseado no custo total dos produtos
                            $suggestedPrice = $totalProductsPrice / (1 - $mlFee - 0.05);
                            
                            // Preço do catálogo (se selecionado)
                            $catalogPrice = isset($catalogProductData['price']) && $catalogProductData['price'] > 0 ? $catalogProductData['price'] : null;
                        @endphp

                        {{-- Grid 2 colunas: Preço e Sugerido --}}
                        <div class="grid grid-cols-2 gap-2">
                            {{-- Preço do Anúncio --}}
                            <div>
                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1 block">Preço Anúncio</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-emerald-600 dark:text-emerald-400 text-xs font-bold">R$</span>
                                    <input type="number" wire:model.live="publishPrice" step="0.01" min="0.01"
                                           class="w-full pl-8 pr-2 py-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/40 text-slate-900 dark:text-white text-base font-black focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 transition-all"
                                           placeholder="0,00">
                                </div>
                                <p class="text-[8px] text-slate-500 mt-0.5">
                                    @if(count($selectedProducts) > 1)
                                        Soma: R$ {{ number_format($totalProductsPrice, 2, ',', '.') }}
                                    @else
                                        Base: R$ {{ number_format($totalProductsPrice, 2, ',', '.') }}
                                    @endif
                                </p>
                            </div>

                            {{-- Preço Sugerido --}}
                            <div>
                                <label class="text-[9px] font-bold text-blue-600 dark:text-blue-400 uppercase mb-1 block flex items-center gap-1">
                                    <i class="bi bi-lightbulb-fill text-[10px]"></i> Sugerido
                                </label>
                                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-2 text-center">
                                    <p class="text-lg font-black text-blue-700 dark:text-blue-400">R$ {{ number_format($suggestedPrice, 2, ',', '.') }}</p>
                                </div>
                                <p class="text-[8px] text-blue-600 dark:text-blue-500 mt-0.5">
                                    @if(count($selectedProducts) > 1)
                                        Kit: Custos + 5% margem
                                    @else
                                        Custos + 5% margem
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($catalogPrice)
                        <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 p-2.5">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-purple-700 dark:text-purple-400 uppercase flex items-center gap-1">
                                    <i class="bi bi-patch-check-fill"></i> Preço no Catálogo ML
                                </span>
                                <span class="text-base font-black text-purple-700 dark:text-purple-400">R$ {{ number_format($catalogPrice, 2, ',', '.') }}</span>
                            </div>
                            <p class="text-[8px] text-purple-600 dark:text-purple-500">
                                Preço que o catálogo do ML está usando
                            </p>
                        </div>
                        @endif

                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 p-2 space-y-1">
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Custos</span>
                            
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="text-slate-600 dark:text-slate-400">Taxa ({{ $mlFee * 100 }}%)</span>
                                <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($mlFeeAmount, 2, ',', '.') }}</span>
                            </div>
                            
                            @if($freeShipping)
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="text-slate-600 dark:text-slate-400">Frete</span>
                                <span class="font-bold text-red-600 dark:text-red-400">- R$ {{ number_format($shippingCost, 2, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            <div class="pt-1 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Líquido</span>
                                <span class="text-sm font-black {{ $netAmount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">R$ {{ number_format($netAmount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- � TIPO DE ANÚNCIO + QUANTIDADE --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 space-y-2">
                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-bookmark-star text-purple-500 text-sm"></i> Tipo de anúncio
                    </h4>
                    <div class="grid grid-cols-2 gap-1.5">
                        @php
                            $listingTypes = [
                                ['key' => 'gold_special', 'label' => 'Clássico', 'icon' => 'trophy-fill', 'color' => 'yellow', 'fee' => '16%'],
                                ['key' => 'gold_pro', 'label' => 'Premium', 'icon' => 'star-fill', 'color' => 'purple', 'fee' => '17%'],
                                ['key' => 'gold', 'label' => 'Gold', 'icon' => 'star', 'color' => 'blue', 'fee' => '13%'],
                                ['key' => 'free', 'label' => 'Grátis', 'icon' => 'bag', 'color' => 'slate', 'fee' => '11%'],
                            ];
                        @endphp
                        @foreach($listingTypes as $lt)
                        <label class="cursor-pointer" wire:click="$set('listingType', '{{ $lt['key'] }}')">
                            <div class="p-2 rounded-lg border text-center transition-all
                                        {{ $listingType === $lt['key']
                                            ? 'border-' . $lt['color'] . '-500 bg-' . $lt['color'] . '-50 dark:bg-' . $lt['color'] . '-900/20 shadow-md'
                                            : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-slate-300' }}">
                                <i class="bi bi-{{ $lt['icon'] }} text-base block {{ $listingType === $lt['key'] ? 'text-' . $lt['color'] . '-500' : 'text-slate-400' }}"></i>
                                <p class="text-[10px] font-bold mt-0.5 {{ $listingType === $lt['key'] ? 'text-' . $lt['color'] . '-700 dark:text-' . $lt['color'] . '-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $lt['label'] }}</p>
                                <p class="text-[8px] {{ $listingType === $lt['key'] ? 'text-' . $lt['color'] . '-600 dark:text-' . $lt['color'] . '-500' : 'text-slate-500' }}">{{ $lt['fee'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Quantidade (abaixo do tipo) --}}
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                        <label class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 block flex items-center gap-1">
                            <i class="bi bi-box-seam"></i> Quantidade
                        </label>
                        <div class="relative">
                            <i class="bi bi-hash absolute left-2 top-1/2 -translate-y-1/2 text-blue-500 text-xs"></i>
                            <input type="number" wire:model="publishQuantity" min="1" step="1"
                                   class="w-full pl-7 pr-2 py-2 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/40 text-slate-900 dark:text-white text-base font-black focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 transition-all"
                                   placeholder="1">
                        </div>
                        <p class="text-[8px] text-slate-500 mt-0.5">
                            Estoque: <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->stock_quantity ?? 0 }} un.</span>
                        </p>
                    </div>
                </div>

                {{-- 🏷 CATEGORIA ML --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-tag-fill text-amber-500 text-sm"></i> Categoria ML
                        </h4>
                        @if($mlCategoryId)
                        <span class="text-[8px] font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                        @endif
                    </div>

                    {{-- Busca --}}
                    <div class="relative">
                        <i class="bi bi-search absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input type="text" wire:model.live.debounce.500ms="categorySearch"
                               placeholder="Buscar..."
                               class="w-full pl-7 pr-7 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[10px] placeholder-slate-400 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 transition-all">
                        <div wire:loading wire:target="updatedCategorySearch" class="absolute right-2 top-1/2 -translate-y-1/2">
                            <i class="bi bi-arrow-repeat animate-spin text-amber-500 text-[10px]"></i>
                        </div>
                    </div>

                    {{-- Select --}}
                    <div class="relative">
                        <select wire:model.live="mlCategoryId"
                                class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[10px] focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 transition-all appearance-none pr-6">
                            <option value="">{{ empty($mlCategories) ? 'Use IA ou busque' : 'Selecione (' . count($mlCategories) . ')' }}</option>
                            @foreach($mlCategories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        <i class="bi bi-chevron-down absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>

                    @if(empty($mlCategoryId))
                    <p class="text-[8px] text-amber-600 dark:text-amber-400 flex items-center gap-1">
                        <i class="bi bi-exclamation-triangle-fill"></i> Obrigatório
                    </p>
                    @endif
                </div>

                {{-- 📝 TIPO DE PUBLICAÇÃO --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 space-y-2">
                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-journals text-indigo-500 text-sm"></i> Tipo Publicação
                    </h4>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                      {{ !$catalogProductId ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300' }}">
                            <input type="radio" name="publicationType" checked disabled
                                   class="w-3 h-3 text-indigo-500 focus:ring-indigo-500/30">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-plus-circle-fill text-indigo-500 text-xs"></i>
                                    <p class="text-[10px] font-bold text-slate-900 dark:text-white">Nova Publicação</p>
                                </div>
                                <p class="text-[8px] text-slate-500 mt-0.5">Produto independente</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                      {{ $catalogProductId ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 opacity-60' }}">
                            <input type="radio" name="publicationType" {{ $catalogProductId ? 'checked' : '' }} disabled
                                   class="w-3 h-3 text-emerald-500 focus:ring-emerald-500/30">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-link-45deg text-emerald-500 text-xs"></i>
                                    <p class="text-[10px] font-bold text-slate-900 dark:text-white">Vinculado Catálogo</p>
                                </div>
                                <p class="text-[8px] text-slate-500 mt-0.5">
                                    {{ $catalogProductId ? 'ID: ' . $catalogProductId : 'Use busca automática' }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 🚚 ENVIO E LOGÍSTICA --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-3 py-2 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                        <h4 class="text-[10px] font-bold text-teal-700 dark:text-teal-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-truck text-sm"></i> Envio & Logística
                        </h4>
                    </div>
                    <div class="p-3 space-y-2">
                        <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                      {{ $freeShipping ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-300' }}">
                            <input type="checkbox" wire:model.live="freeShipping"
                                   class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-emerald-500 focus:ring-emerald-500/30">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-truck-front-fill text-emerald-500 text-sm"></i>
                                    <p class="text-[11px] font-bold text-slate-900 dark:text-white">Frete Grátis</p>
                                    <span class="ml-auto px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[8px] font-bold">DESTAQUE</span>
                                </div>
                                <p class="text-[8px] text-slate-500 mt-0.5">Custo ~R$ 15,00</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                      {{ $localPickup ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
                            <input type="checkbox" wire:model.live="localPickup"
                                   class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-500 focus:ring-blue-500/30">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi bi-shop-window text-blue-500 text-sm"></i>
                                    <p class="text-[11px] font-bold text-slate-900 dark:text-white">Retirada Local</p>
                                    <span class="ml-auto px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-[8px] font-bold">SEM CUSTO</span>
                                </div>
                                <p class="text-[8px] text-slate-500 mt-0.5">Cliente retira</p>
                            </div>
                        </label>
                        
                        {{-- Info adicional de envio --}}
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 p-2 space-y-1">
                            <div class="flex items-center justify-between text-[9px]">
                                <span class="text-slate-600 dark:text-slate-400">Modalidade:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Mercado Envios</span>
                            </div>
                            <div class="flex items-center justify-between text-[9px]">
                                <span class="text-slate-600 dark:text-slate-400">Prazo médio:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">2-5 dias úteis</span>
                            </div>
                            <div class="flex items-center justify-between text-[9px]">
                                <span class="text-slate-600 dark:text-slate-400">Rastreamento:</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400"><i class="bi bi-check-circle-fill"></i> Incluído</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🛡️ CONDIÇÃO & GARANTIA --}}
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-3 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-blue-200 dark:border-blue-800">
                        <h4 class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-shield-check text-sm"></i> Condição & Garantia
                        </h4>
                    </div>
                    <div class="p-3 space-y-2">
                        {{-- Condição --}}
                        <div>
                            <label class="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1 block">
                                Condição do Produto
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                              {{ $productCondition === 'new' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
                                    <input type="radio" wire:model.live="productCondition" value="new"
                                           class="w-3 h-3 text-blue-500 focus:ring-blue-500/30">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1">
                                            <i class="bi bi-star-fill text-blue-500 text-xs"></i>
                                            <p class="text-[10px] font-bold text-slate-900 dark:text-white">Novo</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-center gap-2 p-2 rounded-lg border-2 transition-all cursor-pointer
                                              {{ $productCondition === 'used' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300' }}">
                                    <input type="radio" wire:model.live="productCondition" value="used"
                                           class="w-3 h-3 text-amber-500 focus:ring-amber-500/30">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1">
                                            <i class="bi bi-box-seam text-amber-500 text-xs"></i>
                                            <p class="text-[10px] font-bold text-slate-900 dark:text-white">Usado</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Garantia --}}
                        <div>
                            <label class="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1 block">
                                Garantia
                            </label>
                            <input type="text" wire:model.live="warranty"
                                   placeholder="Ex: 90 dias de garantia do fabricante"
                                   class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[10px] placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 transition-all">
                            <p class="text-[8px] text-slate-500 mt-1">
                                <i class="bi bi-info-circle"></i> Opcional - Informe o período de garantia
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ═══ CONTEÚDO PRINCIPAL (Catálogo, Descrição, Categoria, Fotos, Atributos) ═══ --}}
            <div class="flex flex-col gap-3">

                {{-- Header do catálogo removido - informações agora estão na calculadora --}}

                {{-- ═══════════════════════════════════════════════════════════
                     ZONA 2 — DESCRIÇÃO DO CATÁLOGO
                     ═══════════════════════════════════════════════════════════ --}}
                @if($catalogProductId && !empty($catalogProductData))
                <div class="flex-shrink-0 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                    
                    {{-- TOGGLE: Vincular ao catálogo ou copiar dados --}}
                    <div class="mb-4 p-3 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/30 dark:to-indigo-950/30 border border-purple-200 dark:border-purple-900">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-link-45deg text-purple-600 dark:text-purple-400 text-lg"></i>
                                <span class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">Modo de Publicação</span>
                            </div>
                            <button type="button" wire:click="$toggle('linkToCatalog')"
                                    class="flex items-center gap-2 text-xs font-semibold transition-colors hover:scale-105">
                                <div class="relative w-11 h-6 rounded-full {{ $linkToCatalog ? 'bg-gradient-to-r from-purple-500 to-indigo-600' : 'bg-slate-300 dark:bg-slate-600' }} transition-all duration-300 shadow-lg">
                                    <div class="absolute top-0.5 {{ $linkToCatalog ? 'left-5.5' : 'left-0.5' }} w-5 h-5 rounded-full bg-white shadow-lg transition-all duration-300"></div>
                                </div>
                            </button>
                        </div>
                        <div class="space-y-1">
                            @if($linkToCatalog)
                                <p class="text-[11px] font-semibold text-purple-700 dark:text-purple-300 flex items-center gap-1.5">
                                    <i class="bi bi-link text-sm"></i> Vinculado ao Catálogo ML
                                </p>
                                <p class="text-[9px] text-purple-600/80 dark:text-purple-400/80 leading-tight">
                                    Publicação conectada ao catálogo. Dados sincronizados automaticamente com o Mercado Livre.
                                </p>
                            @else
                                <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="bi bi-clipboard-check text-sm"></i> Dados Copiados (Independente)
                                </p>
                                <p class="text-[9px] text-slate-600 dark:text-slate-400 leading-tight">
                                    Criando publicação independente com dados do catálogo. Você tem controle total sobre atributos e descrição.
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Descrição --}}
                    @if($catalogDescription)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bi bi-file-text text-blue-500 text-sm"></i> Descrição do Catálogo
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-[8px] font-bold text-emerald-700 dark:text-emerald-300">
                                <i class="bi bi-check-circle"></i> Será usada
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">
                            {{ is_string($catalogDescription) ? $catalogDescription : '' }}
                        </p>
                    </div>
                    @endif

                    {{-- Toggle de fotos --}}
                    @if(!empty($catalogPictures))
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            <i class="bi bi-images text-purple-500"></i> {{ count($catalogPictures) }} fotos do catálogo
                        </span>
                        <button type="button" wire:click="toggleCatalogPictures"
                                class="flex items-center gap-2 text-xs font-semibold transition-colors">
                            <div class="relative w-9 h-5 rounded-full {{ $useCatalogPictures ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }} transition-colors">
                                <div class="absolute top-0.5 {{ $useCatalogPictures ? 'left-4.5' : 'left-0.5' }} w-4 h-4 rounded-full bg-white shadow transition-all"></div>
                            </div>
                            <span class="{{ $useCatalogPictures ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">
                                {{ $useCatalogPictures ? 'Catálogo' : 'Local' }}
                            </span>
                        </button>
                    </div>
                    @endif

                    {{-- Atributos automáticos (badge inline) --}}
                    <div class="flex items-center gap-2 pt-3 mt-3 border-t border-slate-200 dark:border-slate-800">
                        <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Atributos preenchidos automaticamente</span>
                    </div>
                </div>
                @endif

                {{-- ═══════════════════════════════════════════════════════════
                     ZONA 3 — GALERIA DE FOTOS (full width, flex horizontal)
                     ═══════════════════════════════════════════════════════════ --}}
                @if($catalogProductId && !empty($catalogPictures))
                <div class="flex-shrink-0 rounded-2xl bg-gradient-to-br from-white to-blue-50/30 dark:from-slate-900 dark:to-blue-900/10 border border-slate-200 dark:border-slate-800 p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow">
                                <i class="bi bi-images text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Galeria de Imagens</h3>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ count($catalogPictures) }} fotos selecionadas do catálogo</p>
                            </div>
                        </div>
                        <button type="button" wire:click="toggleCatalogPictures"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg {{ $useCatalogPictures ? 'bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-300 dark:border-emerald-700' : 'bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700' }} transition-all">
                            <div class="relative w-8 h-4 rounded-full {{ $useCatalogPictures ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }} transition-colors">
                                <div class="absolute top-0.5 {{ $useCatalogPictures ? 'left-4' : 'left-0.5' }} w-3 h-3 rounded-full bg-white shadow transition-all"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $useCatalogPictures ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}">
                                {{ $useCatalogPictures ? 'Catálogo' : 'Local' }}
                            </span>
                        </button>
                    </div>
                    <div class="flex gap-4 overflow-x-auto pb-3 scrollbar-thin">
                        @foreach($catalogPictures as $index => $pic)
                            @php
                                $picUrl = $pic['secure_url'] ?: $pic['url'];
                                $isSelected = in_array($picUrl, $selectedPictures);
                            @endphp
                            <div class="relative flex-shrink-0 group">
                                <div class="w-48 h-48 rounded-2xl overflow-hidden border-2 transition-all cursor-pointer shadow-md hover:shadow-xl
                                            {{ $isSelected ? 'border-emerald-500 shadow-emerald-500/30 ring-4 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-400' }}">
                                    <img src="{{ $picUrl }}" alt="Foto {{ $index + 1 }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy">
                                </div>
                                @if($isSelected)
                                <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center border-3 border-white dark:border-slate-900 shadow-lg">
                                    <i class="bi bi-check-lg text-white text-sm font-bold"></i>
                                </div>
                                @endif
                                @if($index === 0 && $isSelected)
                                <div class="absolute top-2 left-2 px-2.5 py-1 rounded-lg text-[9px] font-black bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md uppercase tracking-wider flex items-center gap-1">
                                    <i class="bi bi-star-fill"></i> Principal
                                </div>
                                @endif
                                <div class="absolute bottom-2 left-2 right-2 bg-black/50 backdrop-blur-sm rounded-lg px-2 py-1 text-[10px] text-white font-semibold text-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    Imagem {{ $index + 1 }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ═══════════════════════════════════════════════════════════
                     ZONA 4 — ATRIBUTOS (full width, se houver, flex-1 cresce)
                     ═══════════════════════════════════════════════════════════ --}}
                @if(!$catalogProductId && !empty($mlCategoryAttributes))
                <div class="flex-1 min-h-0 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
                    <div class="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <div class="w-5 h-5 rounded bg-green-500 flex items-center justify-center">
                            <i class="bi bi-card-checklist text-white text-[10px]"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Atributos ({{ count($mlCategoryAttributes) }})</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 scrollbar-thin">
                        <div class="grid grid-cols-3 gap-2.5">
                            @foreach($mlCategoryAttributes as $attr)
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    {{ $attr['name'] }}
                                    @if($attr['hint'] ?? null) <span class="normal-case text-slate-400 font-normal">({{ $attr['hint'] }})</span> @endif
                                </label>
                                @if($attr['value_type'] === 'list')
                                    <select wire:model="selectedAttributes.{{ $attr['id'] }}"
                                            class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[11px] focus:border-green-500 focus:ring-1 focus:ring-green-500/20 transition-all">
                                        <option value="">—</option>
                                        @foreach($attr['values'] as $value)
                                            <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($attr['value_type'] === 'number')
                                    <input type="number" wire:model="selectedAttributes.{{ $attr['id'] }}" placeholder="0"
                                           class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[11px] focus:border-green-500 focus:ring-1 focus:ring-green-500/20 transition-all">
                                @else
                                    <input type="text" wire:model="selectedAttributes.{{ $attr['id'] }}" placeholder="..."
                                           class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-[11px] placeholder-slate-400 focus:border-green-500 focus:ring-1 focus:ring-green-500/20 transition-all">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @elseif($catalogProductId && !empty($catalogAttributes))
                {{-- ═══ Atributos do Catálogo (grid inline, ocupa o espaço restante) ═══ --}}
                <div class="flex-1 min-h-0 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
                    <div class="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/30 dark:to-indigo-950/30 border-b border-purple-200 dark:border-purple-900">
                        <div class="w-5 h-5 rounded bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <i class="bi bi-check2-circle text-white text-[10px]"></i>
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">Atributos do catálogo ({{ count($catalogAttributes) }}) ✓</span>
                        <span class="ml-auto px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-[8px] font-bold text-emerald-700 dark:text-emerald-300">Incluídos automaticamente</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 scrollbar-thin">
                        <div class="grid grid-cols-3 xl:grid-cols-4 gap-1.5">
                            @foreach($catalogAttributes as $attr)
                                @if(!empty($attr['value_name']))
                                <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 px-2.5 py-2">
                                    <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase block leading-none mb-0.5">{{ $attr['name'] }}</span>
                                    <span class="text-[11px] text-slate-800 dark:text-slate-200 font-semibold truncate block" title="{{ $attr['value_name'] }}">{{ $attr['value_name'] }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                {{-- ═══ Estado vazio: preenche espaço restante ═══ --}}
                <div class="flex-1 rounded-xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center">
                    <div class="text-center py-6">
                        <i class="bi bi-layout-wtf text-3xl text-slate-300 dark:text-slate-700"></i>
                        <p class="text-xs text-slate-400 dark:text-slate-600 mt-2 font-medium">Vincule um catálogo ou selecione uma categoria</p>
                        <p class="text-[10px] text-slate-300 dark:text-slate-700 mt-0.5">para preencher os atributos</p>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════
         MODAL PRODUCT SELECTOR
         ═══════════════════════════════════════════════════════════ --}}
    @if($showProductSelector)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click="toggleProductSelector">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-7xl w-full h-[85vh] flex flex-col" wire:click.stop>
                {{-- Header --}}
                <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-box-seam-fill text-purple-500"></i>
                            Adicionar Produtos ao Kit
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Selecione produtos para criar uma publicação em kit no Mercado Livre
                        </p>
                    </div>
                    <button type="button" wire:click="toggleProductSelector"
                            class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center">
                        <i class="bi bi-x-lg text-slate-600 dark:text-slate-400"></i>
                    </button>
                </div>

                {{-- Product Selector Component --}}
                <div class="flex-1 overflow-hidden px-6 py-4">
                    <livewire:mercado-livre.components.product-selector 
                        :initialProducts="$selectedProducts" />
                </div>

                {{-- Footer Info --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-slate-600 dark:text-slate-400">
                                <i class="bi bi-info-circle-fill text-blue-500"></i>
                                Produtos selecionados: <strong class="text-slate-900 dark:text-white">{{ count($selectedProducts) }}</strong>
                            </span>
                            @if(count($selectedProducts) > 1)
                                <span class="text-slate-600 dark:text-slate-400">
                                    <i class="bi bi-box-seam-fill text-purple-500"></i>
                                    Tipo: <strong class="text-purple-600 dark:text-purple-400">KIT</strong>
                                </span>
                            @endif
                        </div>
                        <button type="button" wire:click="toggleProductSelector"
                                class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold hover:shadow-lg transition-all flex items-center gap-2">
                            <i class="bi bi-check-lg"></i> Concluir Seleção
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
