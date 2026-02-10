<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/40 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">

    {{-- =============================== HEADER MODERNIZADO =============================== --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 shadow-lg mb-6">
        <!-- Efeito de brilho sutil -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
        
        <!-- Background decorativo -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-yellow-400/20 via-amber-400/20 to-orange-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-28 h-28 bg-gradient-to-tr from-blue-400/10 via-indigo-400/10 to-purple-400/10 rounded-full transform -translate-x-12 translate-y-12"></div>
        
        <div class="relative w-full px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Botão Voltar -->
                    <a href="{{ route('mercadolivre.products') }}"
                       class="group relative inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-200 shadow-md border border-white/40 dark:border-slate-600/40 backdrop-blur-sm">
                        <i class="bi bi-arrow-left text-lg text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-150"></i>
                        <div class="absolute inset-0 rounded-xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    </a>
                    
                    <!-- Ícone Principal -->
                    <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-xl shadow-lg shadow-amber-500/20">
                        <i class="bi bi-shop text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/20 to-transparent opacity-40"></div>
                    </div>
                    
                    <!-- Título -->
                    <div class="space-y-0.5">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-amber-600 to-orange-600 dark:from-slate-100 dark:via-amber-300 dark:to-orange-300 bg-clip-text text-transparent">
                            Publicar no Mercado Livre
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                            🚀 Configure e publique seu produto no marketplace
                        </p>
                    </div>
                </div>
                
                <!-- Badge Conectado -->
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Conectado</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =============================== CONTENT MODERNIZADO =============================== --}}
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

            {{-- ==================== SIDEBAR MODERNIZADA ==================== --}}
            <aside class="xl:col-span-2">
                <div class="sticky top-6 space-y-5">

                    {{-- Product Card Premium Modernizado --}}
                    <div class="group rounded-2xl overflow-hidden bg-white dark:bg-slate-900/70 border-2 border-slate-200 dark:border-slate-700/50 shadow-2xl backdrop-blur-md hover:shadow-3xl hover:scale-[1.01] transition-all duration-300">
                        <div class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 overflow-hidden">
                            @if($product->image && $product->image !== 'product-placeholder.png')
                                <img src="{{ asset('storage/products/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-600">
                                    <i class="bi bi-image text-5xl mb-2"></i>
                                    <span class="text-xs font-medium">Sem imagem</span>
                                </div>
                            @endif
                            @if($catalogProductId)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 text-white shadow-xl shadow-emerald-500/50 backdrop-blur-sm border border-white/20">
                                        <i class="bi bi-patch-check-fill"></i>
                                        <span>CATÁLOGO</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 space-y-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug line-clamp-2">{{ $product->name }}</h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-2 border-emerald-200 dark:border-emerald-800/30 p-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 mb-1">
                                        <i class="bi bi-cash-coin text-emerald-600 dark:text-emerald-400 text-sm"></i>
                                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase">Preço</span>
                                    </div>
                                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                                        R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-2 border-blue-200 dark:border-blue-800/30 p-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 mb-1">
                                        <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-sm"></i>
                                        <span class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase">Estoque</span>
                                    </div>
                                    <p class="text-xl font-black text-blue-600 dark:text-blue-400 leading-tight">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2.5 pt-2 border-t-2 border-slate-100 dark:border-slate-800">
                                @if($product->category)
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                            <i class="bi bi-tag-fill text-amber-600 dark:text-amber-400 text-xs"></i>
                                        </div>
                                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $product->category->name }}</span>
                                    </div>
                                @endif
                                @if($product->barcode)
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <div class="w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                            <i class="bi bi-upc text-blue-600 dark:text-blue-400 text-xs"></i>
                                        </div>
                                        <span class="font-mono text-slate-700 dark:text-slate-300 font-semibold">{{ $product->barcode }}</span>
                                    </div>
                                @endif
                                @if($product->brand)
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <div class="w-7 h-7 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                            <i class="bi bi-award-fill text-purple-600 dark:text-purple-400 text-xs"></i>
                                        </div>
                                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $product->brand }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Checklist Premium --}}
                    <div class="rounded-2xl bg-white dark:bg-slate-900/70 border-2 border-slate-200 dark:border-slate-700/50 shadow-xl p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                <i class="bi bi-clipboard-check text-white text-sm"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Checklist de Publicação</h4>
                        </div>
                        @php
                            $checks = [
                                ['label' => 'Categoria ML selecionada', 'ok' => !empty($mlCategoryId), 'icon' => 'tag'],
                                ['label' => 'Produto do catálogo', 'ok' => !empty($catalogProductId), 'icon' => 'patch-check'],
                                ['label' => 'Imagens selecionadas', 'ok' => !empty($selectedPictures), 'icon' => 'images'],
                                ['label' => 'Preço definido', 'ok' => (float)$publishPrice > 0, 'icon' => 'cash-coin'],
                                ['label' => 'Quantidade disponível', 'ok' => $publishQuantity > 0, 'icon' => 'box-seam'],
                            ];
                        @endphp
                        <div class="space-y-2.5">
                            @foreach($checks as $c)
                                <div class="flex items-center gap-3 p-2.5 rounded-xl transition-all
                                            {{ $c['ok'] ? 'bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/30' : 'bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700/30' }}">
                                    @if($c['ok'])
                                        <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-emerald-500 flex items-center justify-center shadow-md">
                                            <i class="bi bi-check-lg text-white text-sm font-bold"></i>
                                        </div>
                                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">{{ $c['label'] }}</span>
                                    @else
                                        <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-300 dark:bg-slate-700 flex items-center justify-center">
                                            <i class="bi bi-{{ $c['icon'] }} text-slate-500 dark:text-slate-400 text-xs"></i>
                                        </div>
                                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ $c['label'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>

            {{-- ==================== MAIN FORM MODERNIZADO ==================== --}}
            <main class="xl:col-span-3">
                <form wire:submit.prevent="publishProduct" class="space-y-5">

                    {{-- ═══════════ STEP 1 — Catálogo ML com BUSCA AUTOMÁTICA ═══════════ --}}
                    @if($product->barcode)
                    <section class="rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 shadow-xl overflow-hidden" x-data="{ autoSearched: false }" x-init="if (!autoSearched) { autoSearched = true; $wire.searchCatalog(); }">
                        <div class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                                <span class="text-white font-black text-sm">1</span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-search"></i>
                                    Buscar no Catálogo ML
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">🤖 Busca automática ativada — Identificando produto...</p>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            {{-- Barcode Info com Loading State --}}
                            <div class="relative p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border-2 border-blue-200 dark:border-blue-800/30">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-500/20 dark:bg-blue-500/30 flex items-center justify-center">
                                        <i class="bi bi-upc-scan text-2xl text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">
                                            Código de Barras Detectado
                                        </p>
                                        <p class="font-mono text-lg font-bold text-blue-600 dark:text-blue-400 tracking-wide">
                                            {{ $product->barcode }}
                                        </p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                            💡 Buscando automaticamente no catálogo do Mercado Livre...
                                        </p>
                                    </div>
                                    <div wire:loading wire:target="searchCatalog" class="flex-shrink-0">
                                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-500/20 border border-blue-500/30">
                                            <i class="bi bi-arrow-repeat animate-spin text-blue-600 dark:text-blue-400"></i>
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Buscando...</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Skeleton Loading Elegante --}}
                                <div wire:loading wire:target="searchCatalog" class="mt-4 space-y-3">
                                    <div class="h-3 bg-gradient-to-r from-blue-200 to-indigo-200 dark:from-blue-800/30 dark:to-indigo-800/30 rounded-full animate-pulse w-3/4"></div>
                                    <div class="h-3 bg-gradient-to-r from-blue-200 to-indigo-200 dark:from-blue-800/30 dark:to-indigo-800/30 rounded-full animate-pulse w-1/2"></div>
                                </div>
                            </div>

                            {{-- Catalog Results Modernizados --}}
                            @if(!empty($catalogResults))
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                        <i class="bi bi-search text-blue-500"></i>
                                        {{ count($catalogResults) }} resultado(s) encontrado(s)
                                    </p>
                                </div>
                                <div class="space-y-2.5">
                                @foreach($catalogResults as $catalogProduct)
                                    @php
                                        $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                                        $domainId = $catalogProduct['domain_id'] ?? '';
                                        $isSelected = $catalogProductId === $cProductId;
                                    @endphp
                                    <div wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $domainId }}')"
                                         class="flex items-center gap-4 p-4 rounded-2xl cursor-pointer transition-all group
                                                {{ $isSelected
                                                    ? 'bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-2 border-emerald-500 shadow-xl shadow-emerald-500/20'
                                                    : 'bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-lg' }}">
                                        @if(isset($catalogProduct['thumbnail']) || isset($catalogProduct['picture']))
                                            <img src="{{ $catalogProduct['thumbnail'] ?? $catalogProduct['picture'] }}" 
                                                 class="w-16 h-16 rounded-xl object-cover border-2 {{ $isSelected ? 'border-emerald-500' : 'border-slate-200 dark:border-slate-700' }} flex-shrink-0 shadow-md">
                                        @else
                                            <div class="w-16 h-16 rounded-xl {{ $isSelected ? 'bg-emerald-500/20' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-box {{ $isSelected ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} text-xl"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold {{ $isSelected ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }} line-clamp-2 leading-snug">
                                                {{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}
                                            </p>
                                            <p class="text-xs font-mono {{ $isSelected ? 'text-emerald-600 dark:text-emerald-500' : 'text-slate-500 dark:text-slate-400' }} mt-1.5">
                                                {{ $cProductId }}
                                            </p>
                                        </div>
                                        @if($isSelected)
                                            <span class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold shadow-lg">
                                                <i class="bi bi-check-circle-fill"></i>
                                                <span>Selecionado</span>
                                            </span>
                                        @else
                                            <div class="flex-shrink-0 text-xs font-semibold text-blue-600 dark:text-blue-400 group-hover:underline flex items-center gap-1">
                                                <span>Selecionar</span>
                                                <i class="bi bi-arrow-right text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </section>
                    @endif

                    {{-- ═══════════ CATALOG INFO — Dados do Produto do Catálogo ═══════════ --}}
                    @if($catalogProductId && !empty($catalogProductData))
                    <section class="rounded-2xl overflow-hidden bg-white dark:bg-slate-900/60 border-2 border-emerald-200 dark:border-emerald-800/40 shadow-xl">
                        <div class="flex items-center justify-between gap-3 px-5 py-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b-2 border-emerald-200 dark:border-emerald-800/40">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                    <i class="bi bi-patch-check-fill text-white text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Produto do Catálogo ML</h2>
                                    <p class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">
                                        ✨ Dados oficiais <span class="font-mono text-emerald-600 dark:text-emerald-500 ml-1">{{ $catalogProductId }}</span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" 
                                    wire:click="clearCatalogProduct" 
                                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all" 
                                    title="Remover produto do catálogo">
                                <i class="bi bi-x-lg text-sm"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-6">

                            {{-- Catalog Product Name & Description Modernizados --}}
                            <div class="space-y-4">
                                @if($catalogProductName)
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug bg-gradient-to-r from-slate-900 to-emerald-700 dark:from-white dark:to-emerald-400 bg-clip-text text-transparent">
                                        {{ $catalogProductName }}
                                    </h3>
                                </div>
                                @endif
                                @if($catalogDescription)
                                <div class="relative">
                                    <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border-2 border-slate-200 dark:border-slate-700 max-h-40 overflow-y-auto custom-scrollbar">
                                        <div class="absolute top-3 right-3 w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                            <i class="bi bi-text-left text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                        </div>
                                        {!! nl2br(e(is_string($catalogDescription) ? $catalogDescription : '')) !!}
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Catalog Pictures Gallery Premium --}}
                            @if(!empty($catalogPictures))
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border-2 border-blue-200 dark:border-blue-800/30">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                            <i class="bi bi-images text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                                Fotos do Catálogo
                                            </h4>
                                            <p class="text-xs text-blue-700 dark:text-blue-400 font-medium">{{ count($catalogPictures) }} imagens disponíveis</p>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center gap-3 cursor-pointer group">
                                        <span class="text-xs font-semibold {{ $useCatalogPictures ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }} transition-colors">
                                            {{ $useCatalogPictures ? '✔ Usando fotos do catálogo' : 'Usar fotos do catálogo?' }}
                                        </span>
                                        <button type="button" 
                                                wire:click="toggleCatalogPictures"
                                                class="relative w-14 h-7 rounded-full transition-all {{ $useCatalogPictures ? 'bg-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-slate-300 dark:bg-slate-700' }}">
                                            <div class="absolute top-1 {{ $useCatalogPictures ? 'left-7' : 'left-1' }} w-5 h-5 rounded-full bg-white shadow-md transition-all"></div>
                                        </button>
                                    </label>
                                </div>

                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    @foreach($catalogPictures as $index => $pic)
                                        @php
                                            $picUrl = $pic['secure_url'] ?: $pic['url'];
                                            $isSelected = in_array($picUrl, $selectedPictures);
                                        @endphp
                                        <div class="relative group">
                                            <div class="aspect-square rounded-2xl overflow-hidden border-2 transition-all cursor-pointer
                                                        {{ $isSelected ? 'border-emerald-500 shadow-xl shadow-emerald-500/30 scale-95' : 'border-slate-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-600 hover:shadow-lg' }}">
                                                <img src="{{ $picUrl }}" 
                                                     alt="Foto {{ $index + 1 }}" 
                                                     class="w-full h-full object-cover transition-all {{ $isSelected ? 'scale-100' : 'group-hover:scale-110' }}"
                                                     loading="lazy">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            </div>
                                            @if($isSelected)
                                            <div class="absolute top-2 right-2 w-7 h-7 rounded-xl bg-emerald-500 flex items-center justify-center shadow-xl shadow-emerald-500/50 border-2 border-white">
                                                <i class="bi bi-check-lg text-white text-sm font-bold"></i>
                                            </div>
                                            @endif
                                            @if($index === 0 && $isSelected)
                                            <div class="absolute bottom-2 left-2 px-2.5 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold uppercase shadow-lg">
                                                ⭐ Principal
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if($useCatalogPictures)
                                <div class="flex items-start gap-2.5 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/10 border-2 border-blue-200 dark:border-blue-800/30">
                                    <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                        <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                                        As fotos do catálogo ML são hospedadas nos servidores do Mercado Livre — carregamento instantâneo e sem custo de armazenamento.
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif

                            {{-- Catalog Attributes Premium --}}
                            @if(!empty($catalogAttributes))
                            <div class="space-y-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                        <i class="bi bi-list-check text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                        Atributos do Catálogo <span class="text-purple-600 dark:text-purple-400">({{ count($catalogAttributes) }})</span>
                                    </h4>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach($catalogAttributes as $attr)
                                        @if(!empty($attr['value_name']))
                                        <div class="group rounded-2xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 p-3.5 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md transition-all">
                                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1.5">{{ $attr['name'] }}</span>
                                            <span class="text-sm text-slate-900 dark:text-white font-semibold block truncate group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors" title="{{ $attr['value_name'] }}">{{ $attr['value_name'] }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        </div>
                    </section>
                    @endif

                    {{-- ═══════════ STEP 2 — Categoria MODERNIZADA ═══════════ --}}
                    <section class="rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 shadow-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                                <span class="text-white font-black text-sm">{{ $product->barcode ? '2' : '1' }}</span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-tag"></i>
                                    Categoria do Mercado Livre <span class="text-amber-600 dark:text-amber-400">*</span>
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Obrigatória — selecione a categoria que melhor se encaixa</p>
                            </div>
                            @if($mlCategoryId)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-500/30">
                                    <i class="bi bi-check-circle-fill"></i> {{ $mlCategoryId }}
                                </span>
                            @endif
                        </div>
                        <div class="p-6 space-y-5">
                            {{-- Quick actions com design modernizado --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button type="button"
                                        wire:click="predictCategory"
                                        wire:loading.attr="disabled"
                                        class="group relative flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white text-sm font-bold shadow-lg shadow-purple-500/20 transition-all hover:shadow-xl hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 group-hover:translate-x-full transition-transform duration-700"></div>
                                    <span wire:loading.remove wire:target="predictCategory" class="relative flex items-center gap-2">
                                        <i class="bi bi-stars"></i> Sugestão Automática (IA)
                                    </span>
                                    <span wire:loading wire:target="predictCategory" class="relative flex items-center gap-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Analisando...
                                    </span>
                                </button>
                                <button type="button"
                                        wire:click="loadMainCategories"
                                        wire:loading.attr="disabled"
                                        class="flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-100 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold transition-all disabled:opacity-50">
                                    <span wire:loading.remove wire:target="loadMainCategories">
                                        <i class="bi bi-list-ul"></i> Categorias Principais
                                    </span>
                                    <span wire:loading wire:target="loadMainCategories">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Carregando...
                                    </span>
                                </button>
                            </div>

                            {{-- Search com ícone e loading inline --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <i class="bi bi-search text-slate-400 dark:text-slate-500"></i>
                                </div>
                                <input type="text"
                                       wire:model.live.debounce.500ms="categorySearch"
                                       placeholder="Buscar categoria (ex: shampoo, eletrônicos, roupas...)"
                                       class="w-full pl-11 pr-12 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 dark:placeholder-slate-500 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all">
                                <div wire:loading wire:target="updatedCategorySearch" class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <i class="bi bi-arrow-repeat animate-spin text-amber-500"></i>
                                </div>
                            </div>

                            {{-- Category selector modernizado --}}
                            <div class="relative">
                                <select wire:model.live="mlCategoryId"
                                        class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all appearance-none cursor-pointer
                                               {{ empty($mlCategoryId) ? 'text-slate-400' : '' }}">
                                    <option value="">
                                        @if(empty($mlCategories))
                                            Use os botões acima ou busque para carregar categorias
                                        @else
                                            Selecione ({{ count($mlCategories) }} disponíveis)
                                        @endif
                                    </option>
                                    @foreach($mlCategories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <i class="bi bi-chevron-down text-slate-400"></i>
                                </div>
                            </div>

                            @if(empty($mlCategoryId))
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border-2 border-amber-200 dark:border-amber-800/30">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                    <i class="bi bi-exclamation-triangle-fill text-amber-600 dark:text-amber-400"></i>
                                </div>
                                <p class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">
                                    A categoria é <strong class="font-bold">obrigatória</strong> para publicar, mesmo usando produto do catálogo.
                                </p>
                            </div>
                            @endif
                        </div>
                    </section>

                    {{-- ═══════════ STEP 3 — Atributos (pré-preenchidos) ═══════════ --}}
                    @if($catalogProductId)
                    <section class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/10 dark:to-teal-900/10 border-2 border-emerald-200 dark:border-emerald-800/30 shadow-lg overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30">
                                <span class="text-white font-black text-sm">{{ $product->barcode ? '3' : '2' }}</span>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-list-check"></i>
                                    Atributos
                                </h2>
                                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5 font-medium">
                                    ✨ Serão preenchidos automaticamente pelo catálogo do ML
                                </p>
                            </div>
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                                <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400 text-2xl"></i>
                            </div>
                        </div>
                    </section>
                    @elseif(!empty($mlCategoryAttributes))
                    <section class="rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 shadow-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg shadow-green-500/30">
                                <span class="text-white font-black text-sm">{{ $product->barcode ? '3' : '2' }}</span>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-card-checklist"></i>
                                    Atributos Obrigatórios
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ count($mlCategoryAttributes) }} campo(s) exigido(s) pela categoria</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($mlCategoryAttributes as $attr)
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                                        {{ $attr['name'] }}
                                        @if($attr['hint'] ?? null)
                                            <span class="text-slate-500 dark:text-slate-400 font-normal text-xs">({{ $attr['hint'] }})</span>
                                        @endif
                                    </label>
                                    @if($attr['value_type'] === 'list')
                                        <select wire:model="selectedAttributes.{{ $attr['id'] }}"
                                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all">
                                            <option value="">Selecione...</option>
                                            @foreach($attr['values'] as $value)
                                                <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($attr['value_type'] === 'number')
                                        <input type="number" wire:model="selectedAttributes.{{ $attr['id'] }}" 
                                               placeholder="0"
                                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all">
                                    @else
                                        <input type="text" wire:model="selectedAttributes.{{ $attr['id'] }}" 
                                               placeholder="Preencha..."
                                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all">
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @endif

                    {{-- ═══════════ STEP 4 — Preço, Quantidade e Configurações ═══════════ --}}
                    <section class="rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 shadow-xl overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 border-b border-slate-200 dark:border-slate-700/50">
                            @php $stepNum = $product->barcode ? '4' : '3'; @endphp
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/30">
                                <span class="text-white font-black text-sm">{{ $stepNum }}</span>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-gear"></i>
                                    Configurações do Anúncio
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Preço, quantidade, tipo de listagem e envio</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-8">

                            {{-- Price & Quantity Modernizados --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                                    <i class="bi bi-cash-coin text-emerald-500"></i>
                                    Preço e Quantidade
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                                    {{-- Price Input Modernizado --}}
                                    <div class="space-y-2">
                                        <label for="publishPrice" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                            Preço do Anúncio
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                                <span class="text-emerald-600 dark:text-emerald-400 text-base font-bold">R$</span>
                                            </div>
                                            <input type="number" id="publishPrice" wire:model="publishPrice" step="0.01" min="0.01"
                                                   class="w-full pl-14 pr-4 py-3.5 rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/10 dark:to-green-900/10 border-2 border-emerald-200 dark:border-emerald-800/30 text-slate-900 dark:text-white text-xl font-bold focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all"
                                                   placeholder="0,00">
                                        </div>
                                        @if($product->price_sale && $product->price && $product->price_sale != $product->price)
                                        <p class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                                            Original: <span class="line-through">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                            <i class="bi bi-arrow-right text-emerald-500"></i>
                                            Promoção: <span class="text-emerald-600 dark:text-emerald-400 font-semibold">R$ {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                                        </p>
                                        @endif
                                    </div>

                                    {{-- Quantity Input Modernizado --}}
                                    <div class="space-y-2">
                                        <label for="publishQuantity" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                            Quantidade Disponível
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                                <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-lg"></i>
                                            </div>
                                            <input type="number" id="publishQuantity" wire:model="publishQuantity" min="1" step="1"
                                                   class="w-full pl-14 pr-4 py-3.5 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border-2 border-blue-200 dark:border-blue-800/30 text-slate-900 dark:text-white text-xl font-bold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all"
                                                   placeholder="1">
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">
                                            Estoque atual no sistema: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $product->stock_quantity ?? 0 }} un.</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Listing Type Modernizado --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                                    <i class="bi bi-bookmark-star text-purple-500"></i>
                                    Tipo de Anúncio
                                </h3>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    {{-- Gold Special --}}
                                    <label class="relative cursor-pointer group" wire:click="$set('listingType', 'gold_special')">
                                        <div class="p-4 rounded-2xl border-2 text-center transition-all
                                                    {{ $listingType === 'gold_special' ? 'border-yellow-500 bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 shadow-lg shadow-yellow-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-yellow-300 dark:hover:border-yellow-700 hover:shadow-md' }}">
                                            <i class="bi bi-trophy-fill text-2xl block mb-2 {{ $listingType === 'gold_special' ? 'text-yellow-500' : 'text-slate-400 group-hover:text-yellow-400' }}"></i>
                                            <p class="text-sm font-bold {{ $listingType === 'gold_special' ? 'text-yellow-700 dark:text-yellow-400' : 'text-slate-700 dark:text-slate-300' }}">Clássico</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Destaque máximo</p>
                                        </div>
                                    </label>
                                    {{-- Gold Pro --}}
                                    <label class="relative cursor-pointer group" wire:click="$set('listingType', 'gold_pro')">
                                        <div class="p-4 rounded-2xl border-2 text-center transition-all
                                                    {{ $listingType === 'gold_pro' ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 shadow-lg shadow-purple-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md' }}">
                                            <i class="bi bi-star-fill text-2xl block mb-2 {{ $listingType === 'gold_pro' ? 'text-purple-500' : 'text-slate-400 group-hover:text-purple-400' }}"></i>
                                            <p class="text-sm font-bold {{ $listingType === 'gold_pro' ? 'text-purple-700 dark:text-purple-400' : 'text-slate-700 dark:text-slate-300' }}">Premium</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Full + vídeo</p>
                                        </div>
                                    </label>
                                    {{-- Gold --}}
                                    <label class="relative cursor-pointer group" wire:click="$set('listingType', 'gold')">
                                        <div class="p-4 rounded-2xl border-2 text-center transition-all
                                                    {{ $listingType === 'gold' ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 shadow-lg shadow-blue-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-md' }}">
                                            <i class="bi bi-star text-2xl block mb-2 {{ $listingType === 'gold' ? 'text-blue-500' : 'text-slate-400 group-hover:text-blue-400' }}"></i>
                                            <p class="text-sm font-bold {{ $listingType === 'gold' ? 'text-blue-700 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300' }}">Gold</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Boa visibilidade</p>
                                        </div>
                                    </label>
                                    {{-- Grátis --}}
                                    <label class="relative cursor-pointer group" wire:click="$set('listingType', 'free')">
                                        <div class="p-4 rounded-2xl border-2 text-center transition-all
                                                    {{ $listingType === 'free' ? 'border-slate-400 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-800/60 dark:to-gray-800/60 shadow-lg shadow-slate-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-md' }}">
                                            <i class="bi bi-bag text-2xl block mb-2 {{ $listingType === 'free' ? 'text-slate-600 dark:text-slate-400' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                            <p class="text-sm font-bold {{ $listingType === 'free' ? 'text-slate-700 dark:text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Grátis</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sem custo</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Shipping Modernizado --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                                    <i class="bi bi-truck text-emerald-500"></i>
                                    Envio
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all
                                                  {{ $freeShipping ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 shadow-lg' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md' }}">
                                        <input type="checkbox" wire:model="freeShipping"
                                               class="mt-0.5 w-5 h-5 rounded bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 text-emerald-500 focus:ring-4 focus:ring-emerald-500/20">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <i class="bi bi-truck text-emerald-500"></i>
                                                Frete Grátis
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Maior destaque no ML</p>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all
                                                  {{ $localPickup ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 shadow-lg' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-md' }}">
                                        <input type="checkbox" wire:model="localPickup"
                                               class="mt-0.5 w-5 h-5 rounded bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 text-blue-500 focus:ring-4 focus:ring-blue-500/20">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <i class="bi bi-shop text-blue-500"></i>
                                                Retirada Local
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Permite retirada</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ═══════════ RESUMO & AÇÕES PREMIUM ═══════════ --}}
                    <section class="rounded-2xl overflow-hidden shadow-2xl border-2 border-amber-200 dark:border-amber-800/40 bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 dark:from-amber-900/10 dark:via-yellow-900/10 dark:to-orange-900/10">
                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                                    <i class="bi bi-clipboard-check text-white text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-extrabold text-slate-900 dark:text-white">Resumo do Anúncio</h4>
                            </div>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="rounded-2xl bg-white dark:bg-slate-800/60 p-5 text-center shadow-md border border-slate-200 dark:border-slate-700">
                                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                                        <i class="bi bi-cash-coin text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1">Preço</span>
                                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format((float)$publishPrice, 2, ',', '.') }}</span>
                                </div>
                                <div class="rounded-2xl bg-white dark:bg-slate-800/60 p-5 text-center shadow-md border border-slate-200 dark:border-slate-700">
                                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                        <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1">Quantidade</span>
                                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $publishQuantity }}</span>
                                </div>
                                <div class="rounded-2xl bg-white dark:bg-slate-800/60 p-5 text-center shadow-md border border-slate-200 dark:border-slate-700">
                                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-purple-500/20 flex items-center justify-center">
                                        <i class="bi bi-images text-purple-600 dark:text-purple-400 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1">Imagens</span>
                                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ count($selectedPictures) }}</span>
                                </div>
                                <div class="rounded-2xl bg-white dark:bg-slate-800/60 p-5 text-center shadow-md border border-slate-200 dark:border-slate-700">
                                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-amber-500/20 flex items-center justify-center">
                                        <i class="bi bi-tag text-amber-600 dark:text-amber-400 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1">Categoria</span>
                                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400 truncate block">{{ $mlCategoryId ?: '—' }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                                <a href="{{ route('mercadolivre.products') }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-700/60 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold transition-all hover:shadow-md">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Cancelar</span>
                                </a>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="flex-1 group relative inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl overflow-hidden
                                               bg-gradient-to-r from-yellow-500 via-amber-500 to-orange-500
                                               hover:from-yellow-600 hover:via-amber-600 hover:to-orange-600
                                               text-white text-base font-black tracking-wide uppercase
                                               shadow-2xl shadow-amber-500/40
                                               hover:shadow-3xl hover:scale-[1.02]
                                               disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100
                                               transition-all duration-200">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 group-hover:translate-x-full transition-transform duration-700"></div>
                                    <span wire:loading.remove wire:target="publishProduct" class="relative flex items-center gap-3">
                                        <i class="bi bi-rocket-takeoff-fill text-xl"></i>
                                        <span>Publicar no Mercado Livre</span>
                                    </span>
                                    <span wire:loading wire:target="publishProduct" class="relative flex items-center gap-3">
                                        <i class="bi bi-arrow-repeat animate-spin text-xl"></i>
                                        <span>Publicando...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </section>

                </form>
            </main>
        </div>
    </div>
</div>
