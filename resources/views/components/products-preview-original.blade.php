@props([
    'products' => [],
    'categories' => [],
    'showBackButton' => true
])

<div class="space-y-6">
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-neutral-600 dark:text-neutral-300">Revise os dados antes de salvar no sistema</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ !empty($products) ? count($products) : 0 }} produtos encontrados
                </span>
            </div>
        </div>

        @if(!empty($products) && count($products) > 0)
            <!-- Grid de Cards de Produtos - até 8 por linha em telas ultrawide -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-8 ultrawind:grid-cols-8 gap-6">
                @foreach($products as $index => $product)
                    <div class="product-card-modern" style="min-height: 420px;">
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
                                <i id="category-icon-{{ $index }}"
                                   class="{{ $categories->firstWhere('id_category', $product['category_id'] ?? 1)->icone ?? 'bi bi-box-seam' }} category-icon"></i>
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

                            <!-- Select de Categoria -->
                            <div class="flex justify-center mt-2">
                                <select wire:model.live="productsUpload.{{ $index }}.category_id"
                                        class="category-select text-xs px-3 py-1.5 rounded-lg border border-purple-200 bg-white dark:bg-slate-700 dark:border-purple-700 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-all duration-200"
                                        onchange="updateCategoryIcon({{ $index }}, this)">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}"
                                                data-icon="{{ $category->icone ?? 'bi bi-box-seam' }}"
                                                {{ ($product['category_id'] ?? 1) == $category->id_category ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
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
