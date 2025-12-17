@props([
    'products' => [],
    'categories' => [],
    'showBackButton' => true
])

@php
    // Mapeamento de ícones icons8 para Bootstrap Icons
    $iconMap = [
        'icons8-perfume' => 'bi-emoji-heart-eyes',
        'icons8-nubank' => 'bi-credit-card-2-front',
        'icons8-pagamento' => 'bi-currency-dollar',
        'icons8-pix' => 'bi-lightning-charge',
        'icons8-xp' => 'bi-graph-up-arrow',
        'icons8-inter' => 'bi-bank',
        'icons8-rendimento' => 'bi-graph-up',
        'icons8-restaurante' => 'bi-cup-straw',
        'icons8-beleza' => 'bi-heart',
        'icons8-supermercado' => 'bi-cart',
        'icons8-transporte' => 'bi-bus-front',
        'icons8-casa' => 'bi-house',
        'icons8-saude' => 'bi-heart-pulse',
        'icons8-educacao' => 'bi-book',
        'icons8-entretenimento' => 'bi-controller',
        'icons8-vestuario' => 'bi-bag',
        'icons8-tecnologia' => 'bi-laptop',
        'icons8-combustivel' => 'bi-fuel-pump',
        'icons8-farmacia' => 'bi-capsule',
        'icons8-pet' => 'bi-heart',
    ];
@endphp

<div class="space-y-6">
    <div class=" p-8">
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center space-x-4">
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    @php
                        $totalQuantity = !empty($products) ? array_sum(array_column($products, 'stock_quantity')) : 0;
                    @endphp
                    {{ $totalQuantity }} produtos encontrados
                </span>
            </div>
        </div>

        @if(!empty($products) && count($products) > 0)
            <!-- Grid de Cards de Produtos - até 8 por linha em telas ultrawide -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-8 ultrawind:grid-cols-8 gap-6">
                @foreach($products as $index => $product)
                    <div class="product-card-modern"
                         x-data="{ dropdownOpen: false }"
                         :class="{ 'dropdown-open': dropdownOpen }"
                         style="min-height: 420px;">
                        <!-- Botões de ação modernos -->
                        <div class="btn-action-group flex gap-2 mb-3">
                            <button type="button"
                                    onclick="copyProductName({{ $index }}, '{{ $product['name'] ?? '' }}')"
                                    class="group relative inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 hover:from-emerald-500 hover:to-emerald-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-emerald-300 backdrop-blur-sm"
                                    title="Copiar nome">
                                <i class="bi bi-tag text-sm group-hover:scale-110 transition-transform duration-200"></i>
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-emerald-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                            <button type="button"
                                    onclick="copyProductCode({{ $index }}, '{{ $product['product_code'] ?? '' }}')"
                                    class="group relative inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-300 backdrop-blur-sm"
                                    title="Copiar código">
                                <i class="bi bi-upc-scan text-sm group-hover:scale-110 transition-transform duration-200"></i>
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-blue-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                            <button type="button"
                                    wire:click="removeProduct({{ $index }})"
                                    class="group relative inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-red-300 backdrop-blur-sm"
                                    title="Remover">
                                <i class="bi bi-trash3 text-sm group-hover:scale-110 transition-transform duration-200"></i>
                                <!-- Efeito hover ring -->
                                <div class="absolute inset-0 rounded-xl bg-red-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                        </div>

                        <!-- Área da imagem com badges -->
                        <div class="product-img-area">
                            <!-- Upload de imagem ao clicar -->
                            <div class="relative cursor-pointer"
                                 onclick="document.getElementById('image-input-{{ $index }}').click();">
                                @if(isset($product['temp_image']))
                                    <img src="{{ $product['temp_image'] }}"
                                         class="product-img"
                                         alt="{{ $product['name'] ?? 'Produto' }}"
                                         id="product-image-{{ $index }}">
                                @else
                                    <img src="{{ asset('storage/products/product-placeholder.png') }}"
                                         class="product-img"
                                         alt="{{ $product['name'] ?? 'Produto' }}"
                                         id="product-image-{{ $index }}">
                                @endif

                                <!-- Overlay para indicar que é clicável -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-200 rounded-t-xl flex items-center justify-center opacity-0 hover:opacity-100">
                                    <div class="bg-white bg-opacity-90 rounded-full p-2">
                                        <i class="bi bi-camera text-gray-700 text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Input de arquivo oculto -->
                            <input type="file"
                                   id="image-input-{{ $index }}"
                                   class="hidden"
                                   accept="image/*">

                            <!-- Código do produto editável -->
                            <div class="badge-product-code editable-badge" title="Código do Produto">
                                <input type="text"
                                       wire:model.lazy="productsUpload.{{ $index }}.product_code"
                                       class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded"
                                       placeholder="Código"
                                       maxlength="15">
                            </div>

                            <!-- Quantidade editável -->
                            <div class="badge-quantity editable-badge" title="Quantidade em Estoque">
                                <input type="number"
                                       wire:model.lazy="productsUpload.{{ $index }}.stock_quantity"
                                       min="0"
                                       class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded"
                                       placeholder="0">
                            </div>

                            <!-- Ícone da categoria -->
                            <div class="category-icon-wrapper">
                                @php
                                    $category = $categories->firstWhere('id_category', $product['category_id'] ?? 1);
                                    $iconClass = $iconMap[$category->icone ?? ''] ?? 'bi-tag';
                                @endphp
                                <i id="category-icon-{{ $index }}"
                                   class="{{ $iconClass }} category-icon"></i>
                            </div>
                        </div>

                        <!-- Conteúdo editável -->
                        <div class="card-body">
                            <!-- Nome do produto como título editável -->
                            <div class="product-title-editable">
                                <input type="text"
                                       wire:model.lazy="productsUpload.{{ $index }}.name"
                                       class="w-full text-center font-bold bg-transparent border-none focus:outline-none focus:ring-2 focus:ring-purple-300 focus:bg-white focus:bg-opacity-10 rounded px-2 py-1"
                                       placeholder="Nome do produto"
                                       style="font-size: 1.1em; color: #1a1a1a; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            </div>

                            <!-- Status sempre ativo (badge fixo) -->
                            <div class="flex justify-center mt-1 mb-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="bi bi-check-circle-fill mr-1"></i> Ativo
                                </span>
                            </div>

                            <!-- Select de Categoria Estilizado -->
                            <div class="flex justify-center mt-2" x-data="{
                                open: false,
                                search: '',
                                selectedId: {{ $product['category_id'] ?? 1 }},
                                categories: {{ Js::from($categories->map(function($cat) use ($iconMap) {
                                    return [
                                        'id' => $cat->id_category,
                                        'name' => $cat->name,
                                        'icon' => $iconMap[$cat->icone] ?? 'bi-tag'
                                    ];
                                })) }},
                                get selectedCategory() {
                                    return this.categories.find(c => c.id === this.selectedId) || this.categories[0];
                                },
                                get filteredCategories() {
                                    if (!this.search) return this.categories;
                                    return this.categories.filter(c =>
                                        c.name.toLowerCase().includes(this.search.toLowerCase())
                                    );
                                },
                                selectCategory(cat) {
                                    this.selectedId = cat.id;
                                    this.open = false;
                                    this.search = '';
                                    $wire.set('productsUpload.{{ $index }}.category_id', cat.id);
                                    document.getElementById('category-icon-{{ $index }}').className = cat.icon + ' category-icon';
                                    this.$parent.dropdownOpen = false;
                                }
                            }">
                                <div class="relative w-full max-w-xs">
                                    <button type="button"
                                            @click="open = !open; $parent.dropdownOpen = open"
                                            class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg border border-purple-200 bg-white dark:bg-slate-700 dark:border-purple-700 text-slate-700 dark:text-slate-200 hover:border-purple-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-400/20 focus:outline-none transition-all duration-200 text-xs">
                                        <span class="flex items-center gap-2 overflow-hidden">
                                            <i :class="selectedCategory.icon" class="text-purple-500 flex-shrink-0"></i>
                                            <span x-text="selectedCategory.name" class="truncate max-w-[120px]"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open"
                                         x-transition
                                         @click.away="open = false; $parent.dropdownOpen = false"
                                         class="absolute z-[9999] w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl max-h-60 overflow-hidden">
                                        <!-- Search -->
                                        <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                            <input type="text"
                                                   x-model="search"
                                                   @click.stop
                                                   placeholder="Pesquisar..."
                                                   class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                        </div>
                                        <!-- Options -->
                                        <div class="overflow-y-auto max-h-44">
                                            <template x-for="cat in filteredCategories" :key="cat.id">
                                                <button type="button"
                                                        @click="selectCategory(cat)"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                    <i :class="cat.icon" class="text-purple-500 text-xs"></i>
                                                    <span class="text-slate-700 dark:text-slate-200 text-xs" x-text="cat.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredCategories.length === 0" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 text-center">
                                                Nenhuma categoria encontrada
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Badges de preço editáveis no rodapé -->
                        <div class="badge-price editable-price-badge" title="Preço de Custo">
                            <i class="bi bi-tag"></i>
                            <span class="text-xs">R$</span>
                            <input type="number"
                                   wire:model.lazy="productsUpload.{{ $index }}.price"
                                   step="0.01"
                                   class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded text-right"
                                   placeholder="0,00">
                        </div>

                        <div class="badge-price-sale editable-price-badge" title="Preço de Venda">
                            <i class="bi bi-currency-dollar"></i>
                            <span class="text-xs">R$</span>
                            <input type="number"
                                   wire:model.lazy="productsUpload.{{ $index }}.price_sale"
                                   step="0.01"
                                   class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded text-right"
                                   placeholder="0,00">
                        </div>
                    </div>
                @endforeach
            </div>


        @endif
    </div>
</div>
