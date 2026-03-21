{{-- ============================================================
     SHOPEE — PUBLICAR PRODUTO (Steps 1–4)
     Design: "Planta Baixa" — iPhone 15 (393px) → Desktop
     ============================================================ --}}
<div class="shopee-publish-page min-h-screen bg-gray-50 dark:bg-gray-900"
     x-data="{ step: @entangle('currentStep') }">

    {{-- ─── Barra de progresso de steps ─── --}}
    <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                    Publicar na Shopee — Step {{ $currentStep }} de 4
                </span>
                <div class="flex items-center gap-1">
                    {{-- Logo Shopee --}}
                    <div class="w-6 h-6 rounded-md flex items-center justify-center"
                         style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.686 2 6 4.686 6 8c0 1.657.672 3.157 1.757 4.243A6.956 6.956 0 005 18v2h14v-2a6.956 6.956 0 00-2.757-5.757A5.978 5.978 0 0018 8c0-3.314-2.686-6-6-6z"/>
                        </svg>
                    </div>
                </div>
            </div>
            {{-- Barra de progresso --}}
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full transition-all duration-500"
                     style="background: linear-gradient(90deg,#EE4D2D,#FF6633);
                            width: {{ ($currentStep / 4) * 100 }}%">
                </div>
            </div>
            {{-- Labels dos steps --}}
            <div class="flex justify-between mt-1">
                @foreach(['Produtos', 'Categoria', 'Detalhes', 'Publicar'] as $i => $label)
                <span class="text-xs transition-colors
                    {{ $currentStep > $i ? 'text-orange-600 dark:text-orange-400 font-semibold' :
                       ($currentStep === $i + 1 ? 'text-orange-500 font-medium' : 'text-gray-400') }}">
                    {{ $label }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-5 sm:px-6 lg:px-8">

        {{-- ───────────────────────────────────────────────────────
             STEP 1 — Selecionar Produto(s)
             ─────────────────────────────────────────────────────── --}}
        @if($currentStep === 1)
        <div class="space-y-4" wire:key="step-1">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Selecionar Produto</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Escolha o produto que será publicado na Shopee.</p>
            </div>

            {{-- Busca --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.400ms="searchTerm"
                       placeholder="Buscar produto..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                              rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
            </div>

            {{-- Grid de produtos --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @forelse($productsList as $product)
                <div wire:click="selectProduct({{ $product->id }})"
                     class="shopee-product-card bg-white dark:bg-gray-800 rounded-xl border-2 cursor-pointer
                            transition-all duration-200 overflow-hidden
                            {{ isset($selectedProducts[$product->id])
                               ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 scale-[1.02] shadow-md'
                               : 'border-gray-200 dark:border-gray-700 hover:border-orange-300 hover:-translate-y-0.5' }}">

                    {{-- Imagem --}}
                    <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover" loading="lazy"
                             onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">

                        @if(isset($selectedProducts[$product->id]))
                        <div class="absolute inset-0 bg-orange-500/20 flex items-center justify-center">
                            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="p-2.5">
                        <p class="text-xs font-medium text-gray-900 dark:text-white line-clamp-2 leading-tight mb-1">
                            {{ $product->name }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Estoque: {{ $product->stock_quantity }}</span>
                            <span class="text-xs font-bold text-orange-600">
                                R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}
                            </span>
                        </div>

                        {{-- Indicador de dados logísticos --}}
                        @if($product->weight_grams)
                        <div class="mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-xs text-green-600">{{ $product->weight_grams }}g</span>
                        </div>
                        @else
                        <div class="mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs text-amber-600">Sem peso</span>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full py-8 text-center text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-sm">Nenhum produto encontrado</p>
                </div>
                @endforelse
            </div>

            {{ $productsList->links() }}
        </div>
        @endif

        {{-- ───────────────────────────────────────────────────────
             STEP 2 — Categoria Shopee
             ─────────────────────────────────────────────────────── --}}
        @if($currentStep === 2)
        <div class="space-y-4" wire:key="step-2">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Categoria Shopee</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Selecione a categoria correta para seu produto na Shopee.</p>
            </div>

            @if(empty($shopeeCategories))
            <div class="text-center py-8">
                <div wire:loading wire:target="loadCategories">
                    <svg class="w-8 h-8 text-orange-500 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">Carregando categorias...</p>
                </div>
                <div wire:loading.remove wire:target="loadCategories">
                    <button wire:click="loadCategories"
                            class="px-4 py-2 text-sm rounded-xl bg-orange-500 text-white hover:bg-orange-600">
                        Carregar categorias
                    </button>
                </div>
            </div>
            @else
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Categoria <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="shopeeCategoryId"
                        class="w-full px-3 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                               rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                    <option value="">Selecione uma categoria...</option>
                    @foreach($shopeeCategories as $cat)
                    <option value="{{ $cat['category_id'] }}">
                        {{ $cat['display_category_name'] ?? $cat['original_category_name'] ?? $cat['category_id'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Atributos da categoria --}}
            @if(!empty($categoryAttributes))
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Atributos da Categoria
                </h3>
                @foreach($categoryAttributes as $attr)
                @if(!empty($attr['is_mandatory']))
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $attr['display_attribute_name'] ?? $attr['original_attribute_name'] ?? '' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model="selectedAttributes.{{ $attr['attribute_id'] }}"
                           placeholder="{{ $attr['display_attribute_name'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                                  rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                </div>
                @endif
                @endforeach
            </div>
            @endif
            @endif
        </div>
        @endif

        {{-- ───────────────────────────────────────────────────────
             STEP 3 — Detalhes, Preço e Logística
             ─────────────────────────────────────────────────────── --}}
        @if($currentStep === 3)
        <div class="space-y-4" wire:key="step-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Detalhes e Logística</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Configure preço, estoque e informações de entrega.</p>
            </div>

            {{-- Título e Descrição --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Informações do Anúncio</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="title" maxlength="120"
                           placeholder="Nome do produto na Shopee"
                           class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                  rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                    <p class="mt-1 text-xs text-right text-gray-400">{{ strlen($title) }}/120</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Descrição</label>
                    <textarea wire:model="description" rows="3" maxlength="3000"
                              placeholder="Descreva seu produto..."
                              class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                     rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white resize-none">
                    </textarea>
                </div>
            </div>

            {{-- Preço e Estoque --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Preço e Estoque</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Preço (R$) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="publishPrice" step="0.01" min="0"
                               placeholder="0,00"
                               class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                      rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Quantidade</label>
                        <input type="number" wire:model="publishQuantity" min="1"
                               class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                      rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- ─── LOGÍSTICA (obrigatório Shopee) ─── --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-orange-200 dark:border-orange-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Logística do Pacote
                            <span class="text-xs text-orange-600 dark:text-orange-400 font-normal ml-1">(obrigatório Shopee)</span>
                        </h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    {{-- Peso --}}
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Peso do Pacote (g) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="weightGrams" min="1" placeholder="Ex: 300"
                                   class="w-full pl-3 pr-8 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                          rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">g</span>
                        </div>
                    </div>

                    {{-- DTS --}}
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Dias para Envio (DTS) <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="daysToShip"
                                class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                       rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                            @foreach([1, 2, 3, 5, 7] as $d)
                            <option value="{{ $d }}">{{ $d }} dia{{ $d > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dimensões --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Dimensões do pacote (cm) — recomendado para frete mais preciso</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach([['lengthCm', 'Comp.'], ['widthCm', 'Larg.'], ['heightCm', 'Alt.']] as [$field, $label])
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</label>
                        <div class="relative">
                            <input type="number" wire:model="{{ $field }}" min="0" step="0.1" placeholder="0"
                                   class="w-full pl-2 pr-6 py-2 text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600
                                          rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">cm</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Condição do produto --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Condição</h3>
                <div class="flex gap-3">
                    @foreach(['NEW' => 'Novo', 'USED' => 'Usado'] as $value => $label)
                    <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $productCondition === $value
                           ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                           : 'border-gray-200 dark:border-gray-700 hover:border-orange-300' }}">
                        <input type="radio" wire:model="productCondition" value="{{ $value }}" class="sr-only">
                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center
                            {{ $productCondition === $value ? 'border-orange-500' : 'border-gray-400' }}">
                            @if($productCondition === $value)
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            @endif
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ───────────────────────────────────────────────────────
             STEP 4 — Revisão e Publicação
             ─────────────────────────────────────────────────────── --}}
        @if($currentStep === 4)
        <div class="space-y-4" wire:key="step-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Revisar e Publicar</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Confira os dados antes de publicar na Shopee.</p>
            </div>

            {{-- Resumo --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">

                {{-- Produtos selecionados --}}
                <div class="p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Produto(s) selecionado(s)</p>
                    @foreach($selectedProducts as $pid => $data)
                    <div class="flex items-center gap-3">
                        <img src="{{ $data['image'] }}" class="w-10 h-10 rounded-lg object-cover bg-gray-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $data['name'] }}</p>
                            <p class="text-xs text-gray-500">Qtd: {{ $data['quantity'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Anúncio --}}
                <div class="p-4 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Título</p>
                        <p class="font-medium text-gray-900 dark:text-white text-xs">{{ $title ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Categoria ID</p>
                        <p class="font-medium text-gray-900 dark:text-white text-xs">{{ $shopeeCategoryId ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Preço</p>
                        <p class="font-bold text-orange-600">R$ {{ number_format((float)$publishPrice, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Quantidade</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $publishQuantity }}</p>
                    </div>
                </div>

                {{-- Logística --}}
                <div class="p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Logística
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <span class="px-2.5 py-1 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded-lg font-medium">
                            {{ $weightGrams }}g
                        </span>
                        @if($lengthCm || $widthCm || $heightCm)
                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                            {{ $lengthCm }}×{{ $widthCm }}×{{ $heightCm }} cm
                        </span>
                        @endif
                        <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg font-medium">
                            DTS: {{ $daysToShip }}d
                        </span>
                    </div>
                </div>
            </div>

            {{-- Aviso de status --}}
            @if(!$isConnected)
            <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">
                    Sua loja Shopee não está conectada. O anúncio será salvo como rascunho.
                </p>
            </div>
            @endif

            {{-- Botão de publicar --}}
            <button wire:click="publish"
                    wire:loading.attr="disabled"
                    class="w-full py-4 rounded-2xl text-base font-bold text-white transition-all duration-200
                           hover:opacity-90 active:scale-95 disabled:opacity-60"
                    style="background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);">
                <span wire:loading.remove wire:target="publish">
                    <svg class="inline-block w-5 h-5 mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Publicar na Shopee
                </span>
                <span wire:loading wire:target="publish">
                    <svg class="inline-block w-5 h-5 mr-2 -mt-0.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Publicando...
                </span>
            </button>
        </div>
        @endif

        {{-- ─── Navegação Inferior ─── --}}
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            @if($currentStep > 1)
            <button wire:click="previousStep"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400
                           bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </button>
            @else
            <div></div>
            @endif

            @if($currentStep < 4)
            <button wire:click="nextStep"
                    class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl
                           transition-all duration-200 hover:opacity-90 active:scale-95"
                    style="background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);">
                Próximo
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            @endif
        </div>

    </div>
</div>
