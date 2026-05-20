<div class="bulk-edit-page"
     x-data="bulkEditPage()"
     x-init="init()">

    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-edit-products.css') }}">

    <!-- ───────────────── HEADER (idêntico ao products-index) ───────────────── -->
    <x-products-header
        title="Edição em Massa"
        description="Edite seus produtos em cards interativos"
        :total-products="count($productsData)"
        :total-categories="$categories->count()"
        :show-quick-actions="false">

        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('products.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="bi bi-box-seam mr-1"></i>Produtos
                </a>
                <i class="bi bi-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    <i class="bi bi-grid-3x3-gap-fill mr-1"></i>Edição em Massa
                </span>
            </div>
        </x-slot>

        <!-- Controls slot — igual ao layout do products-index -->
        <div class="w-full products-index-controls">

            <!-- ── LINHA 1: Busca + Status filter ── -->
            <div class="prod-header-row-1">
                <div class="prod-header-search relative group">
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nome, código, EAN/código de barras..."
                        class="w-full pl-11 pr-10 py-2.5 bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500/40 focus:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium backdrop-blur-sm">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2">
                        <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors"></i>
                    </div>
                    <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 rounded-lg transition-all duration-200">
                        <i class="bi bi-x text-sm"></i>
                    </button>
                    <div wire:loading.delay wire:target="search" class="absolute right-10 top-1/2 -translate-y-1/2">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-purple-500 border-t-transparent"></div>
                    </div>
                </div>

                <!-- Status filter -->
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button type="button" wire:click="$set('filterStatus', '')"
                        class="sale-filter-pill prod-pill-type {{ $filterStatus === '' ? 'active' : '' }}">
                        <i class="bi bi-grid-3x3-gap"></i><span>Todos</span>
                    </button>
                    <button type="button" wire:click="$set('filterStatus', 'ativo')"
                        class="sale-filter-pill {{ $filterStatus === 'ativo' ? 'active' : '' }}">
                        <i class="bi bi-check-circle"></i><span class="hidden sm:inline">Ativos</span>
                    </button>
                    <button type="button" wire:click="$set('filterStatus', 'inativo')"
                        class="sale-filter-pill {{ $filterStatus === 'inativo' ? 'active' : '' }}">
                        <i class="bi bi-pause-circle"></i><span class="hidden sm:inline">Inativos</span>
                    </button>
                </div>

                <!-- Voltar -->
                <a href="{{ route('products.index') }}" class="sale-action-btn flex-shrink-0">
                    <i class="bi bi-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>

            <!-- ── LINHA 2: Sort pills + Ações ── -->
            <div class="prod-header-row-2">
                <div class="prod-header-row-2-left">
                    <div class="sale-filter-pills sale-sort-pills hidden md:flex">
                        <span class="sale-filter-pill-label"><i class="bi bi-arrow-down-up"></i></span>
                        <button type="button" wire:click="$set('sortBy', 'name')"
                            class="sale-filter-pill {{ $sortBy === 'name' ? 'active' : '' }}">
                            <span>A-Z</span>
                        </button>
                        <button type="button" wire:click="$set('sortBy', 'updated_at')"
                            class="sale-filter-pill {{ $sortBy === 'updated_at' ? 'active' : '' }}">
                            <span>Recentes</span>
                        </button>
                        <button type="button" wire:click="$set('sortBy', 'price_sale')"
                            class="sale-filter-pill {{ $sortBy === 'price_sale' ? 'active' : '' }}">
                            <span>Preço</span>
                        </button>
                    </div>

                    <!-- Contagem -->
                    <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">
                        {{ count($productsData) }} produto{{ count($productsData) !== 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="prod-header-row-2-right">
                    <button type="button" wire:click="loadProducts"
                        class="sale-action-btn" title="Recarregar">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span class="hidden sm:inline">Recarregar</span>
                    </button>
                </div>
            </div>
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

                    <!-- ── Botões de ação (absolutos dentro do card, overlay na imagem) ── -->
                    <div class="btn-action-group">
                        <!-- Copiar nome -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ addslashes($product['name']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="bulk-act-btn bulk-act-green btn" title="Copiar nome">
                            <i x-show="!copied" class="bi bi-tag"></i>
                            <i x-show="copied" class="bi bi-check-lg"></i>
                        </button>

                        <!-- Copiar código -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ addslashes($product['product_code']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="bulk-act-btn bulk-act-blue btn" title="Copiar código">
                            <i x-show="!copied" class="bi bi-upc-scan"></i>
                            <i x-show="copied" class="bi bi-check-lg"></i>
                        </button>

                        <!-- Remover -->
                        <button type="button"
                                wire:click="removeProduct({{ $index }})"
                                wire:confirm="Remover este produto permanentemente?"
                                class="bulk-act-btn bulk-act-red btn" title="Remover produto">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    <!-- ── Área da imagem ── -->
                    <div class="product-img-area">
                        <div class="bulk-img-click" @click="$refs.fileInput{{ $index }}.click()">
                            <img :src="tempImage || '{{ $product['image_url'] }}'"
                                 class="product-img"
                                 alt="{{ $product['name'] }}">

                            <div class="bulk-img-overlay">
                                <div class="bulk-camera-pill"><i class="bi bi-camera"></i></div>
                            </div>

                            <div x-show="hasTempImage" x-transition
                                 class="absolute top-1.5 left-1.5 bg-amber-400 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-md z-10">
                                Nova foto
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput{{ $index }}" class="hidden" accept="image/*"
                               @change="const f=$event.target.files[0]; if(!f) return; const r=new FileReader(); r.onload=e=>{tempImage=e.target.result;}; r.readAsDataURL(f);">

                        <!-- Badge código -->
                        <div class="badge-product-code editable-badge" title="Código">
                            <input type="text"
                                   wire:model.lazy="productsData.{{ $index }}.product_code"
                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded text-[11px]"
                                   placeholder="Código" maxlength="15">
                        </div>

                        <!-- Badge estoque -->
                        <div class="badge-quantity editable-badge" title="Estoque">
                            <input type="number"
                                   wire:model.lazy="productsData.{{ $index }}.stock_quantity"
                                   min="0"
                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white/40 focus:bg-white/10 rounded text-[11px]"
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

                        <!-- Categoria -->
                        <div class="bulk-cat-wrap" x-data="{
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
                            <button type="button" x-ref="dt"
                                    @click.stop="open ? closeDropdown() : openDropdown()"
                                    class="bulk-cat-wrap-btn w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg focus:outline-none transition-all text-xs">
                                <span class="flex items-center gap-1.5 overflow-hidden min-w-0">
                                    <i :class="selectedCategory.icon" class="text-purple-500 flex-shrink-0 text-xs"></i>
                                    <span x-text="selectedCategory.name" class="truncate text-xs"></span>
                                </span>
                                <i class="bi bi-chevron-down text-slate-400 text-[10px] transition-transform flex-shrink-0" :class="{ 'rotate-180': open }"></i>
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

                    <!-- ── Footer: Preços + Código de Barras + Salvar ── -->
                    <div class="bulk-card-footer">
                        <!-- Linha de preços -->
                        <div class="bulk-prices-row">
                            <div class="bulk-price-field">
                                <div class="bulk-price-input-wrap">
                                    <span class="bulk-price-prefix">Custo R$</span>
                                    <input type="number" wire:model.lazy="productsData.{{ $index }}.price" step="0.01" min="0"
                                           class="bulk-price-input" placeholder="0,00">
                                </div>
                            </div>
                            <div class="bulk-price-field">
                                <div class="bulk-price-input-wrap bulk-price-sale">
                                    <span class="bulk-price-prefix">Venda R$</span>
                                    <input type="number" wire:model.lazy="productsData.{{ $index }}.price_sale" step="0.01" min="0"
                                           class="bulk-price-input" placeholder="0,00">
                                </div>
                            </div>
                        </div>

                        <!-- Código de barras (EAN) -->
                        <div class="bulk-barcode-row">
                            <i class="bi bi-upc bulk-barcode-icon"></i>
                            <input type="text"
                                   wire:model.lazy="productsData.{{ $index }}.barcode"
                                   class="bulk-barcode-input"
                                   placeholder="EAN / Cód. de Barras"
                                   maxlength="30">
                        </div>

                        <!-- Botão Salvar -->
                        <button type="button"
                                wire:loading.attr="disabled"
                                wire:target="saveProductWithImage({{ $index }})"
                                @click="
                                    const img = tempImage;
                                    $wire.call('saveProductWithImage', {{ $index }}, img || null).then(() => { tempImage = null; });
                                "
                                class="bulk-save-btn {{ $isSaved ? 'bulk-save-btn--saved' : '' }}">
                            <span wire:loading.remove wire:target="saveProductWithImage({{ $index }})" class="flex items-center gap-1.5">
                                @if($isSaved)
                                    <i class="bi bi-check-circle-fill"></i><span>Salvo!</span>
                                @else
                                    <i class="bi bi-floppy-fill"></i><span>Salvar</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="saveProductWithImage({{ $index }})" class="flex items-center gap-1.5">
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
