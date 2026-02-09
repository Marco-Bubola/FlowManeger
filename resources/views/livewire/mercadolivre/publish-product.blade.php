{{-- BENTO GRID LAYOUT - Single Screen Dashboard --}}
<div class="h-screen flex flex-col overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/40 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">

    {{-- ==================== HEADER FIXO ==================== --}}
    <header class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 shadow-lg flex-shrink-0">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-yellow-400/20 to-orange-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        
        <div class="relative px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('mercadolivre.products') }}"
                       class="group relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 transition-all shadow-md border border-white/40 dark:border-slate-600/40">
                        <i class="bi bi-arrow-left text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform"></i>
                    </a>
                    
                    <div class="relative flex items-center justify-center w-11 h-11 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-lg shadow-lg">
                        <i class="bi bi-shop text-white text-xl"></i>
                    </div>
                    
                    <div>
                        <h1 class="text-lg font-bold bg-gradient-to-r from-slate-800 via-amber-600 to-orange-600 dark:from-slate-100 dark:via-amber-300 dark:to-orange-300 bg-clip-text text-transparent leading-tight">
                            Publicar no Mercado Livre
                        </h1>
                        <p class="text-xs text-slate-600 dark:text-slate-400">Dashboard de Publicação</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Conectado</span>
                </div>
            </div>
        </div>
    </header>

    {{-- ==================== BENTO GRID PRINCIPAL ==================== --}}
    <main class="flex-1 overflow-hidden">
        <div class="h-full grid grid-cols-[280px_1fr_360px] gap-3 p-3">
            
            {{-- ========== COLUNA ESQUERDA: STATUS & INFO ========== --}}
            <aside class="flex flex-col gap-3 overflow-y-auto custom-scrollbar">
                
                {{-- Product Card Compacto --}}
                <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg overflow-hidden">
                    <div class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                        @if($product->image && $product->image !== 'product-placeholder.png')
                            <img src="{{ asset('storage/products/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                <i class="bi bi-image text-3xl"></i>
                                <span class="text-xs mt-1">Sem imagem</span>
                            </div>
                        @endif
                        @if($catalogProductId)
                            <div class="absolute top-2 right-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500 text-white shadow-lg">
                                    <i class="bi bi-patch-check-fill"></i> CATÁLOGO
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="p-3 space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight line-clamp-2">{{ $product->name }}</h3>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 p-2 text-center">
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 block">Preço</span>
                                <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 p-2 text-center">
                                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 block">Estoque</span>
                                <p class="text-sm font-black text-blue-600 dark:text-blue-400">
                                    {{ $product->stock_quantity ?? 0 }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-1 pt-1 border-t border-slate-200 dark:border-slate-700">
                            @if($product->barcode)
                                <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <i class="bi bi-upc text-blue-500 text-[10px]"></i>
                                    <span class="font-mono text-[11px]">{{ $product->barcode }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Checklist Ultra Compacto --}}
                <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                            <i class="bi bi-clipboard-check text-white text-xs"></i>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">Checklist</h4>
                    </div>
                    @php
                        $checks = [
                            ['label' => 'Categoria ML', 'ok' => !empty($mlCategoryId)],
                            ['label' => 'Catálogo ML', 'ok' => !empty($catalogProductId)],
                            ['label' => 'Imagens', 'ok' => !empty($selectedPictures)],
                            ['label' => 'Preço', 'ok' => (float)$publishPrice > 0],
                            ['label' => 'Quantidade', 'ok' => $publishQuantity > 0],
                        ];
                    @endphp
                    <div class="space-y-1.5">
                        @foreach($checks as $c)
                            <div class="flex items-center gap-2 p-1.5 rounded-lg {{ $c['ok'] ? 'bg-emerald-50 dark:bg-emerald-900/10' : 'bg-slate-50 dark:bg-slate-800/30' }}">
                                <div class="w-5 h-5 rounded-md {{ $c['ok'] ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }} flex items-center justify-center">
                                    <i class="bi bi-{{ $c['ok'] ? 'check-lg' : 'dash' }} text-white text-xs"></i>
                                </div>
                                <span class="text-xs {{ $c['ok'] ? 'text-emerald-700 dark:text-emerald-400 font-medium' : 'text-slate-500 dark:text-slate-400' }}">{{ $c['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </aside>

            {{-- ========== COLUNA CENTRO: FORMULÁRIO PRINCIPAL ========== --}}
            <section class="flex flex-col gap-3 overflow-y-auto custom-scrollbar">
                <div class="space-y-3">
                    
                    {{-- Busca de Catálogo Compacta (Auto) --}}
                    @if($product->barcode)
                    <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg" 
                         x-data="{ autoSearched: false }" 
                         x-init="if (!autoSearched) { autoSearched = true; $wire.searchCatalog(); }">
                        <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                <span class="text-white font-black text-xs">1</span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xs font-bold text-slate-900 dark:text-white">Busca Automática no Catálogo</h2>
                            </div>
                            <div wire:loading wire:target="searchCatalog">
                                <i class="bi bi-arrow-repeat animate-spin text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        
                        <div class="p-3">
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/30">
                                <i class="bi bi-upc-scan text-blue-600 dark:text-blue-400 text-lg"></i>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $product->barcode }}</p>
                                    <p class="text-[10px] text-slate-600 dark:text-slate-400">🤖 Buscando automaticamente...</p>
                                </div>
                            </div>

                            {{-- Resultados Compactos --}}
                            @if(!empty($catalogResults))
                            <div class="mt-2 space-y-1">
                                @foreach($catalogResults as $catalogProduct)
                                    @php
                                        $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                                        $isSelected = $catalogProductId === $cProductId;
                                    @endphp
                                    <div wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $catalogProduct['domain_id'] ?? '' }}')"
                                         class="flex items-center gap-2 p-2 rounded-lg cursor-pointer transition-all
                                                {{ $isSelected ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-500' : 'bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
                                        @if(isset($catalogProduct['thumbnail']) || isset($catalogProduct['picture']))
                                            <img src="{{ $catalogProduct['thumbnail'] ?? $catalogProduct['picture'] }}" 
                                                 class="w-10 h-10 rounded-md object-cover border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                                <i class="bi bi-box text-slate-400"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold {{ $isSelected ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }} truncate">
                                                {{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}
                                            </p>
                                        </div>
                                        @if($isSelected)
                                            <i class="bi bi-check-circle-fill text-emerald-500 text-sm"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Categoria Compacta --}}
                    <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg">
                        <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                <span class="text-white font-black text-xs">{{ $product->barcode ? '2' : '1' }}</span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xs font-bold text-slate-900 dark:text-white">Categoria ML <span class="text-amber-600">*</span></h2>
                            </div>
                            @if($mlCategoryId)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold">
                                    <i class="bi bi-check-circle-fill"></i> {{ $mlCategoryId }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="p-3 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="predictCategory" wire:loading.attr="disabled"
                                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white text-xs font-bold shadow-md transition-all">
                                    <i class="bi bi-stars text-xs"></i>
                                    <span wire:loading.remove wire:target="predictCategory">IA</span>
                                    <span wire:loading wire:target="predictCategory">...</span>
                                </button>
                                <button type="button" wire:click="loadMainCategories" wire:loading.attr="disabled"
                                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                                    <i class="bi bi-list-ul text-xs"></i>
                                    <span wire:loading.remove wire:target="loadMainCategories">Principais</span>
                                    <span wire:loading wire:target="loadMainCategories">...</span>
                                </button>
                            </div>

                            <div class="relative">
                                <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" wire:model.live.debounce.500ms="categorySearch"
                                       placeholder="Buscar categoria..."
                                       autofocus
                                       class="w-full pl-8 pr-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs placeholder-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">
                            </div>

                            <select wire:model.live="mlCategoryId"
                                    class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">
                                <option value="">{{ empty($mlCategories) ? 'Use busca ou IA' : 'Selecione ('.count($mlCategories).')' }}</option>
                                @foreach($mlCategories as $category)
                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Info do Catálogo (se selecionado) --}}
                    @if($catalogProductId && !empty($catalogProductData))
                    <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/10 dark:to-green-900/10 border border-emerald-200 dark:border-emerald-800/30 shadow-lg">
                        <div class="flex items-center justify-between gap-2 px-3 py-2 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-emerald-200 dark:border-emerald-800/30">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500 flex items-center justify-center">
                                    <i class="bi bi-patch-check-fill text-white text-xs"></i>
                                </div>
                                <h2 class="text-xs font-bold text-slate-900 dark:text-white">Catálogo ML</h2>
                            </div>
                            <button type="button" wire:click="clearCatalogProduct" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-500 hover:text-red-500 transition-all">
                                <i class="bi bi-x-lg text-xs"></i>
                            </button>
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 line-clamp-2">{{ $catalogProductName }}</p>
                            <p class="text-[10px] font-mono text-emerald-600 dark:text-emerald-500 mt-1">{{ $catalogProductId }}</p>
                            @if(!empty($catalogPictures))
                                <div class="flex items-center gap-1 mt-2 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/30">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="useCatalogPictures" class="w-3.5 h-3.5 rounded text-blue-500">
                                        <span class="text-[10px] font-semibold {{ $useCatalogPictures ? 'text-blue-700 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400' }}">
                                            Usar {{ count($catalogPictures) }} fotos do catálogo
                                        </span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Atributos Obrigatórios (se não usar catálogo e tiver atributos) --}}
                    @if(!$catalogProductId && !empty($mlCategoryAttributes))
                    <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg">
                        <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                                <i class="bi bi-card-checklist text-white text-xs"></i>
                            </div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white">Atributos ({{ count($mlCategoryAttributes) }})</h2>
                        </div>
                        <div class="p-3 space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
                            @foreach($mlCategoryAttributes as $attr)
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 block mb-1">
                                        {{ $attr['name'] }}
                                        @if($attr['hint'] ?? null)
                                            <span class="text-slate-500 font-normal">({{ $attr['hint'] }})</span>
                                        @endif
                                    </label>
                                    @if($attr['value_type'] === 'list')
                                        <select wire:model="selectedAttributes.{{ $attr['id'] }}"
                                                class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                            <option value="">Selecione...</option>
                                            @foreach($attr['values'] as $value)
                                                <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($attr['value_type'] === 'number')
                                        <input type="number" wire:model="selectedAttributes.{{ $attr['id'] }}" placeholder="0"
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                    @else
                                        <input type="text" wire:model="selectedAttributes.{{ $attr['id'] }}" placeholder="Preencha..."
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Configurações Compactas (Grid 2x2) --}}
                    <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg">
                        <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                                <i class="bi bi-gear text-white text-xs"></i>
                            </div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white">Configurações</h2>
                        </div>
                        
                        <div class="p-3 space-y-3">
                            {{-- Preço e Quantidade --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 block mb-1">Preço (R$)</label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-emerald-600 dark:text-emerald-400 text-xs font-bold">R$</span>
                                        <input type="number" wire:model="publishPrice" step="0.01" min="0.01"
                                               class="w-full pl-7 pr-2 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/30 text-slate-900 dark:text-white text-sm font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 block mb-1">Quantidade</label>
                                    <input type="number" wire:model="publishQuantity" min="1"
                                           class="w-full px-2 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/30 text-slate-900 dark:text-white text-sm font-bold focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                                </div>
                            </div>

                            {{-- Tipo de Anúncio --}}
                            <div>
                                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400 block mb-1.5">Tipo de Anúncio</label>
                                <div class="grid grid-cols-4 gap-1.5">
                                    @php
                                        $types = [
                                            ['value' => 'gold_special', 'icon' => 'trophy-fill', 'label' => 'Clássico', 'color' => 'yellow'],
                                            ['value' => 'gold_pro', 'icon' => 'star-fill', 'label' => 'Premium', 'color' => 'purple'],
                                            ['value' => 'gold', 'icon' => 'star', 'label' => 'Gold', 'color' => 'blue'],
                                            ['value' => 'free', 'icon' => 'bag', 'label' => 'Grátis', 'color' => 'slate'],
                                        ];
                                    @endphp
                                    @foreach($types as $type)
                                        <label class="cursor-pointer" wire:click="$set('listingType', '{{ $type['value'] }}')">
                                            <div class="p-2 rounded-lg border text-center transition-all
                                                        {{ $listingType === $type['value'] ? 'border-'.$type['color'].'-500 bg-'.$type['color'].'-50 dark:bg-'.$type['color'].'-900/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-'.$type['color'].'-300' }}">
                                                <i class="bi bi-{{ $type['icon'] }} text-sm {{ $listingType === $type['value'] ? 'text-'.$type['color'].'-500' : 'text-slate-400' }}"></i>
                                                <p class="text-[10px] font-bold {{ $listingType === $type['value'] ? 'text-'.$type['color'].'-700 dark:text-'.$type['color'].'-400' : 'text-slate-600 dark:text-slate-400' }} mt-0.5">{{ $type['label'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Envio --}}
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all
                                              {{ $freeShipping ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40' }}">
                                    <input type="checkbox" wire:model="freeShipping" class="w-4 h-4 rounded text-emerald-500">
                                    <span class="text-xs font-semibold {{ $freeShipping ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">Frete Grátis</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all
                                              {{ $localPickup ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40' }}">
                                    <input type="checkbox" wire:model="localPickup" class="w-4 h-4 rounded text-blue-500">
                                    <span class="text-xs font-semibold {{ $localPickup ? 'text-blue-700 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300' }}">Retirada Local</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- ========== COLUNA DIREITA: PREVIEW & AÇÕES ========== --}}
            <aside class="flex flex-col gap-3 overflow-y-auto custom-scrollbar">
                
                {{-- Preview Visual --}}
                <div class="rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700/50 shadow-lg">
                    <div class="px-3 py-2 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-slate-200 dark:border-slate-700/50">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                                <i class="bi bi-eye text-white text-xs"></i>
                            </div>
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white">Preview do Anúncio</h3>
                        </div>
                    </div>
                    <div class="p-3">
                        {{-- Imagem Principal --}}
                        @if(!empty($selectedPictures))
                            <div class="aspect-square rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 mb-2">
                                <img src="{{ $selectedPictures[0] }}" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="aspect-square rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-2">
                                <div class="text-center text-slate-400">
                                    <i class="bi bi-image text-3xl block mb-1"></i>
                                    <span class="text-xs">Sem imagem</span>
                                </div>
                            </div>
                        @endif

                        {{-- Título --}}
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 mb-2">
                            {{ $catalogProductName ?: $product->name }}
                        </h4>

                        {{-- Preço Destaque --}}
                        <div class="p-3 rounded-lg bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800/30 text-center mb-2">
                            <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">Preço do Anúncio</p>
                            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                R$ {{ number_format((float)$publishPrice, 2, ',', '.') }}
                            </p>
                        </div>

                        {{-- Badges de Recursos --}}
                        <div class="flex flex-wrap gap-1.5">
                            @if($freeShipping)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500 text-white text-[10px] font-bold">
                                    <i class="bi bi-truck"></i> Frete Grátis
                                </span>
                            @endif
                            @if($localPickup)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-500 text-white text-[10px] font-bold">
                                    <i class="bi bi-shop"></i> Retirada
                                </span>
                            @endif
                            @if($catalogProductId)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-500 text-white text-[10px] font-bold">
                                    <i class="bi bi-patch-check-fill"></i> Catálogo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Resumo Estatístico --}}
                <div class="rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/10 dark:to-yellow-900/10 border border-amber-200 dark:border-amber-800/30 shadow-lg p-3">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                            <i class="bi bi-clipboard-data text-white text-xs"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white">Resumo</h3>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-white dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-2 text-center">
                            <div class="w-6 h-6 mx-auto mb-1 rounded-md bg-emerald-500/20 flex items-center justify-center">
                                <i class="bi bi-cash-coin text-emerald-600 dark:text-emerald-400 text-xs"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Preço</span>
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format((float)$publishPrice, 2, ',', '.') }}</span>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-2 text-center">
                            <div class="w-6 h-6 mx-auto mb-1 rounded-md bg-blue-500/20 flex items-center justify-center">
                                <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-xs"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Qtd</span>
                            <span class="text-sm font-black text-blue-600 dark:text-blue-400">{{ $publishQuantity }}</span>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-2 text-center">
                            <div class="w-6 h-6 mx-auto mb-1 rounded-md bg-purple-500/20 flex items-center justify-center">
                                <i class="bi bi-images text-purple-600 dark:text-purple-400 text-xs"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Fotos</span>
                            <span class="text-sm font-black text-purple-600 dark:text-purple-400">{{ count($selectedPictures) }}</span>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-2 text-center">
                            <div class="w-6 h-6 mx-auto mb-1 rounded-md bg-amber-500/20 flex items-center justify-center">
                                <i class="bi bi-tag text-amber-600 dark:text-amber-400 text-xs"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block text-[8px]">Categoria</span>
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 truncate block">{{ $mlCategoryId ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="space-y-2">
                    <button type="button" wire:click="publishProduct" wire:loading.attr="disabled"
                            class="w-full group relative flex items-center justify-center gap-2 px-4 py-3 rounded-xl overflow-hidden
                                   bg-gradient-to-r from-yellow-500 via-amber-500 to-orange-500
                                   hover:from-yellow-600 hover:via-amber-600 hover:to-orange-600
                                   text-white text-xs font-black uppercase tracking-wide
                                   shadow-xl hover:shadow-2xl hover:scale-[1.02]
                                   disabled:opacity-60 disabled:cursor-not-allowed
                                   transition-all duration-200">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 group-hover:translate-x-full transition-transform duration-700"></div>
                        <span wire:loading.remove wire:target="publishProduct" class="relative flex items-center gap-2">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                            Publicar no ML
                        </span>
                        <span wire:loading wire:target="publishProduct" class="relative flex items-center gap-2">
                            <i class="bi bi-arrow-repeat animate-spin"></i>
                            Publicando...
                        </span>
                    </button>

                    <a href="{{ route('mercadolivre.products') }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>
                </div>

            </aside>

        </div>
    </main>
</div>

{{-- Custom Scrollbar Styles --}}
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgb(148 163 184 / 0.3);
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgb(148 163 184 / 0.5);
}
</style>
