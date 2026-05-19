<div class="bulk-edit-page"
     x-data="bulkEditPage()"
     x-init="init()">

    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-edit-products.css') }}">

    <!-- ───────────────── HEADER (igual ao products-index) ───────────────── -->
    <x-products-header
        title="Edição em Massa"
        description="Edite seus produtos em cards interativos"
        :total-products="count($productsData)"
        :total-categories="0"
        :show-quick-actions="false">

        <div class="bulk-header-controls">
            <!-- Busca -->
            <div class="bulk-search-wrap">
                <i class="bi bi-search bulk-search-icon"></i>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Buscar produto..."
                    class="bulk-search-input">
            </div>

            <!-- Voltar -->
            <a href="{{ route('products.index') }}" class="sale-action-btn">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </x-products-header>

    <!-- ───────────────── GRID DE CARDS ───────────────── -->
    <div class="bulk-grid-wrap mt-4">
        @if(count($productsData) === 0)
            <div class="bulk-empty-state">
                <div class="bulk-empty-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h4>Nenhum produto encontrado</h4>
                <p>{{ $search ? 'Tente outro termo de busca.' : 'Cadastre produtos para editá-los aqui.' }}</p>
            </div>
        @else
            <div class="bulk-products-grid">
                @foreach($productsData as $index => $product)
                @php
                    $iconMap = [
                        'icons8-perfume'      => 'bi-emoji-heart-eyes',
                        'icons8-beleza'       => 'bi-heart',
                        'icons8-tecnologia'   => 'bi-laptop',
                        'icons8-vestuario'    => 'bi-bag',
                        'icons8-saude'        => 'bi-heart-pulse',
                        'icons8-casa'         => 'bi-house',
                        'icons8-supermercado' => 'bi-cart',
                        'icons8-restaurante'  => 'bi-cup-straw',
                    ];
                    $category  = $categories->firstWhere('id_category', $product['category_id']);
                    $iconClass = $iconMap[$category->icone ?? ''] ?? 'bi-tag';
                    $isSaved   = isset($savedStatus[$index]) && $savedStatus[$index] === 'saved';
                @endphp

                <div class="bulk-card product-card-modern {{ $isSaved ? 'bulk-card--saved' : '' }}"
                     x-data="{
                         dropdownOpen: false,
                         tempImage: null,
                         get hasTempImage() { return this.tempImage !== null; }
                     }"
                     :class="{ 'dropdown-open': dropdownOpen }">

                    <!-- ── Botões de ação (topo) ── -->
                    <div class="btn-action-group bulk-actions-top">
                        <!-- Copiar nome -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ addslashes($product['name']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="group inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 hover:from-emerald-500 hover:to-emerald-700 text-white transition-all duration-300 shadow-lg border border-emerald-300"
                                title="Copiar nome">
                            <i x-show="!copied" class="bi bi-tag text-sm group-hover:scale-110 transition-transform"></i>
                            <i x-show="copied" class="bi bi-check-lg text-sm"></i>
                        </button>

                        <!-- Copiar código -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ addslashes($product['product_code']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="group inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white transition-all duration-300 shadow-lg border border-blue-300"
                                title="Copiar código">
                            <i x-show="!copied" class="bi bi-upc-scan text-sm group-hover:scale-110 transition-transform"></i>
                            <i x-show="copied" class="bi bi-check-lg text-sm"></i>
                        </button>

                        <!-- Remover -->
                        <button type="button"
                                wire:click="removeProduct({{ $index }})"
                                wire:confirm="Remover este produto permanentemente?"
                                class="group inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gradient-to-br from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white transition-all duration-300 shadow-lg border border-red-300"
                                title="Remover produto">
                            <i class="bi bi-trash3 text-sm group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>

                    <!-- ── Área da imagem ── -->
                    <div class="product-img-area">
                        <!-- Imagem clicável -->
                        <div class="bulk-img-click relative cursor-pointer"
                             @click="$refs.fileInput{{ $index }}.click()">

                            <img :src="tempImage || '{{ $product['image_url'] }}'"
                                 class="product-img"
                                 alt="{{ $product['name'] }}"
                                 id="bulk-preview-{{ $index }}">

                            <!-- Overlay câmera -->
                            <div class="bulk-img-overlay">
                                <div class="bulk-camera-pill">
                                    <i class="bi bi-camera"></i>
                                </div>
                            </div>

                            <!-- Badge nova foto -->
                            <div x-show="hasTempImage"
                                 x-transition
                                 class="absolute top-2 left-2 bg-amber-400 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md">
                                Nova foto
                            </div>
                        </div>

                        <!-- Input file -->
                        <input type="file"
                               x-ref="fileInput{{ $index }}"
                               class="hidden"
                               accept="image/*"
                               @change="
                                   const file = $event.target.files[0];
                                   if (!file) return;
                                   const reader = new FileReader();
                                   reader.onload = e => { tempImage = e.target.result; };
                                   reader.readAsDataURL(file);
                               ">

                        <!-- Badge código -->
                        <div class="badge-product-code editable-badge" title="Código">
                            <input type="text"
                                   wire:model.lazy="productsData.{{ $index }}.product_code"
                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded"
                                   placeholder="Código"
                                   maxlength="15">
                        </div>

                        <!-- Badge estoque -->
                        <div class="badge-quantity editable-badge" title="Estoque">
                            <input type="number"
                                   wire:model.lazy="productsData.{{ $index }}.stock_quantity"
                                   min="0"
                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded"
                                   placeholder="0">
                        </div>

                        <!-- Ícone categoria -->
                        <div class="category-icon-wrapper">
                            <i id="bulk-cat-icon-{{ $index }}" class="{{ $iconClass }} category-icon"></i>
                        </div>
                    </div>

                    <!-- ── Corpo do card ── -->
                    <div class="card-body bulk-card-body">
                        <!-- Nome editável -->
                        <div class="product-title-editable">
                            <input type="text"
                                   wire:model.lazy="productsData.{{ $index }}.name"
                                   class="bulk-name-input"
                                   placeholder="Nome do produto">
                        </div>

                        <!-- Status -->
                        <div class="bulk-status-wrap">
                            <span class="bulk-status-badge bulk-status-{{ $product['status'] }}">
                                <i class="bi bi-{{ $product['status'] === 'ativo' ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                                {{ ucfirst($product['status']) }}
                            </span>
                        </div>

                        <!-- Categoria -->
                        <div class="flex justify-center mt-2" x-data="{
                            open: false, search: '',
                            selectedId: {{ $product['category_id'] ?? 0 }},
                            dropdownTop: 0, dropdownLeft: 0, dropdownWidth: 0, dropdownBottom: 0, dropdownIsAbove: false,
                            categories: {{ Js::from($categories->map(function($cat) use ($iconMap) {
                                return ['id' => $cat->id_category, 'name' => $cat->name, 'icon' => $iconMap[$cat->icone] ?? 'bi-tag'];
                            })) }},
                            get selectedCategory() { return this.categories.find(c => c.id === this.selectedId) || this.categories[0] || { id: 0, name: 'Categoria', icon: 'bi-tag' }; },
                            get filteredCategories() { if (!this.search) return this.categories; return this.categories.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase())); },
                            openDropdown() {
                                const rect = this.$refs.dt.getBoundingClientRect();
                                const vw = window.innerWidth, vh = window.innerHeight;
                                const w = Math.max(180, Math.min(rect.width, vw - 16));
                                let left = rect.left;
                                if (left + w > vw - 8) left = vw - w - 8;
                                if (left < 8) left = 8;
                                this.dropdownWidth = w; this.dropdownLeft = left;
                                if (vh - rect.bottom < 256 && rect.top > 256) { this.dropdownIsAbove = true; this.dropdownBottom = vh - rect.top + 4; this.dropdownTop = 0; }
                                else { this.dropdownIsAbove = false; this.dropdownTop = rect.bottom + 4; this.dropdownBottom = 0; }
                                this.open = true; this.$parent.dropdownOpen = true;
                            },
                            closeDropdown() { this.open = false; this.$parent.dropdownOpen = false; },
                            selectCategory(cat) {
                                this.selectedId = cat.id; this.closeDropdown(); this.search = '';
                                $wire.set('productsData.{{ $index }}.category_id', cat.id);
                                const ic = document.getElementById('bulk-cat-icon-{{ $index }}');
                                if (ic) ic.className = cat.icon + ' category-icon';
                            }
                        }">
                            <div class="relative w-full max-w-xs">
                                <button type="button" x-ref="dt"
                                        @click.stop="open ? closeDropdown() : openDropdown()"
                                        class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg border border-purple-200 dark:border-purple-700 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:border-purple-400 focus:outline-none transition-all text-xs">
                                    <span class="flex items-center gap-2 overflow-hidden">
                                        <i :class="selectedCategory.icon" class="text-purple-500 flex-shrink-0"></i>
                                        <span x-text="selectedCategory.name" class="truncate max-w-[100px]"></span>
                                    </span>
                                    <i class="bi bi-chevron-down text-slate-400 transition-transform flex-shrink-0" :class="{ 'rotate-180': open }"></i>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         @click.away="closeDropdown()"
                                         :style="dropdownIsAbove ? `position:fixed;z-index:2147483647;width:${dropdownWidth}px;bottom:${dropdownBottom}px;left:${dropdownLeft}px` : `position:fixed;z-index:2147483647;width:${dropdownWidth}px;top:${dropdownTop}px;left:${dropdownLeft}px`"
                                         class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl max-h-60 overflow-hidden">
                                        <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                            <input type="text" x-model="search" @click.stop placeholder="Pesquisar..."
                                                   class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                        </div>
                                        <div class="overflow-y-auto max-h-44">
                                            <template x-for="cat in filteredCategories" :key="cat.id">
                                                <button type="button" @click="selectCategory(cat)"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                    <i :class="cat.icon" class="text-purple-500 text-xs"></i>
                                                    <span class="text-slate-700 dark:text-slate-200 text-xs" x-text="cat.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredCategories.length === 0" class="px-3 py-2 text-xs text-slate-500 text-center">Nenhuma encontrada</div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- ── Preços ── -->
                    <div class="badge-price editable-price-badge" title="Preço de Custo">
                        <i class="bi bi-tag"></i>
                        <span class="text-xs">R$</span>
                        <input type="number" wire:model.lazy="productsData.{{ $index }}.price" step="0.01"
                               class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded text-right"
                               placeholder="0,00">
                    </div>
                    <div class="badge-price-sale editable-price-badge" title="Preço de Venda">
                        <i class="bi bi-currency-dollar"></i>
                        <span class="text-xs">R$</span>
                        <input type="number" wire:model.lazy="productsData.{{ $index }}.price_sale" step="0.01"
                               class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded text-right"
                               placeholder="0,00">
                    </div>

                    <!-- ── Salvar card ── -->
                    <div class="bulk-save-row">
                        <button type="button"
                                wire:loading.attr="disabled"
                                wire:target="saveProduct({{ $index }})"
                                @click="
                                    const img = tempImage;
                                    $wire.call('saveProductWithImage', {{ $index }}, img || null).then(() => { tempImage = null; });
                                "
                                class="bulk-save-btn {{ $isSaved ? 'bulk-save-btn--saved' : '' }}">
                            <span wire:loading.remove wire:target="saveProduct({{ $index }})" class="flex items-center gap-1.5">
                                @if($isSaved)
                                    <i class="bi bi-check-circle-fill"></i><span>Salvo!</span>
                                @else
                                    <i class="bi bi-floppy-fill"></i><span>Salvar</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="saveProduct({{ $index }})" class="flex items-center gap-1.5">
                                <i class="bi bi-arrow-clockwise animate-spin"></i><span>Salvando...</span>
                            </span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function bulkEditPage() {
    return {
        init() {}
    };
}
</script>
