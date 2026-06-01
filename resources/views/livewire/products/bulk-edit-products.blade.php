<div class="bulk-edit-page"
     x-data="bulkEditPage()"
     x-init="init()"
     @keydown.escape.window="showSaveAllModal = false">

    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-edit-products.css') }}">

    <!-- ───────────────── HEADER (idêntico ao products-index) ───────────────── -->
    <x-products-header
        title="Edição em Massa"
        description="Edite seus produtos em cards interativos"
        :total-products="$totalProducts"
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
                    <span class="bulk-count-label">
                        <i class="bi bi-boxes"></i>
                        <strong>{{ number_format($totalProducts, 0, ',', '.') }}</strong> produto{{ $totalProducts !== 1 ? 's' : '' }}
                        <span class="bulk-count-page">· Página {{ $currentPage }}/{{ $totalPages }}</span>
                    </span>
                </div>

                <div class="prod-header-row-2-right">
                    <!-- Per page selector -->
                    <div class="bulk-perpage-wrap">
                        <label for="perPage" class="bulk-perpage-label"><i class="bi bi-grid"></i> Por página:</label>
                        <select wire:model.live="perPage" id="perPage" class="bulk-perpage-select">
                            <option value="60">60</option>
                            <option value="120">120</option>
                            <option value="180">180</option>
                            <option value="240">240</option>
                        </select>
                    </div>

                    <!-- Salvar editados (abre modal moderno) — só aparece quando há editados -->
                    <button type="button"
                            x-show="$store.bulkCart.count > 0"
                            x-transition
                            @click="showSaveAllModal = true"
                            :disabled="savingAll"
                            class="bulk-save-all-btn">
                        <span x-show="!savingAll" class="flex items-center gap-1.5">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span class="hidden sm:inline">Salvar Editados</span>
                            <span class="bulk-save-all-count" x-text="$store.bulkCart.count"></span>
                        </span>
                        <span x-show="savingAll" class="flex items-center gap-1.5">
                            <span class="bulk-btn-spinner"></span>
                            <span class="hidden sm:inline">Salvando...</span>
                        </span>
                    </button>

                    <button type="button" wire:click="loadProducts"
                        class="sale-action-btn" title="Recarregar">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span class="hidden sm:inline">Recarregar</span>
                    </button>
                </div>
            </div>
        </div>
    </x-products-header>

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
        $catsForJs = $categories->map(fn($cat) => [
            'id'   => $cat->id_category,
            'name' => $cat->name,
            'icon' => $iconMap[$cat->icone] ?? 'bi-tag',
        ])->values();
    @endphp

    <!-- Categorias definidas uma única vez (evita repetir JSON em todos os cards) -->
    <script>window.__bulkCats = @js($catsForJs);</script>

    <!-- ───────────────── GRID DE CARDS ───────────────── -->
    <div class="bulk-grid-wrap mt-4" id="bulk-grid-top">

        <!-- Overlay de loading durante mudança de página -->
        <div wire:loading.delay wire:target="goToPage,nextPage,previousPage,perPage,search,filterStatus,sortBy,loadProducts"
             class="bulk-page-loading-overlay">
            <div class="bulk-page-loading-content">
                <div class="bulk-page-loading-spinner"></div>
                <span>Carregando produtos...</span>
            </div>
        </div>

        @if(count($productsData) === 0)
            <div class="bulk-empty-state">
                <div class="bulk-empty-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h4>Nenhum produto encontrado</h4>
                <p>{{ $search ? 'Tente outro termo de busca.' : 'Cadastre produtos para editá-los aqui.' }}</p>
            </div>
        @else
            <!-- ── Paginação do topo (compacta) ── -->
            @if($totalPages > 1)
            <nav class="bulk-pagination bulk-pagination--top" aria-label="Paginação (topo)">
                <div class="bulk-pagination-info">
                    <i class="bi bi-collection"></i>
                    <strong>{{ ($currentPage - 1) * $perPage + 1 }}</strong>–<strong>{{ min($currentPage * $perPage, $totalProducts) }}</strong>
                    de <strong>{{ number_format($totalProducts, 0, ',', '.') }}</strong>
                </div>
                <div class="bulk-pagination-controls">
                    <button type="button" wire:click="previousPage" @disabled($currentPage === 1)
                            class="bulk-page-btn bulk-page-btn-arrow" title="Anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="bulk-page-numbers">
                        @foreach($pagesArray as $p)
                            @if($p === '...')
                                <span class="bulk-page-dots">...</span>
                            @else
                                <button type="button" wire:click="goToPage({{ $p }})"
                                        class="bulk-page-btn bulk-page-btn-num {{ $p == $currentPage ? 'bulk-page-btn--active' : '' }}">{{ $p }}</button>
                            @endif
                        @endforeach
                    </div>
                    <button type="button" wire:click="nextPage" @disabled($currentPage === $totalPages)
                            class="bulk-page-btn bulk-page-btn-arrow" title="Próxima">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </nav>
            @endif

            <div class="bulk-products-grid">
                @foreach($productsData as $index => $product)
                @php
                    $category  = $categories->firstWhere('id_category', $product['category_id']);
                    $iconClass = $iconMap[$category->icone ?? ''] ?? 'bi-tag';
                    $isSaved   = isset($savedStatus[$index]) && $savedStatus[$index] === 'saved';
                @endphp

                <div class="bulk-card product-card-modern {{ $isSaved ? 'bulk-card--saved' : '' }}"
                     wire:key="bulk-card-{{ $product['id'] }}-{{ $currentPage }}"
                     x-data="{
                         idx: {{ $index }},
                         pid: {{ $product['id'] }},
                         dropdownOpen: false,
                         tempImage: null,
                         saving: false,
                         nameCopied: false,
                         get hasTempImage() { return this.tempImage !== null; },
                         get isDirty() { return $store.bulkCart.has(this.idx); },
                         markDirty(image) { $store.bulkCart.mark(this.idx, this.pid, image); },
                         copyNameAndPick(name, ref) {
                             window.fmCopyText(name);
                             this.nameCopied = true;
                             setTimeout(() => this.nameCopied = false, 1800);
                             ref.click();
                         }
                     }"
                     @change="markDirty()"
                     :class="{ 'dropdown-open': dropdownOpen, 'bulk-card--saving': saving, 'bulk-card--dirty': isDirty }">

                    <!-- ── Overlay de loading individual ── -->
                    <div class="bulk-card-loading-overlay" x-show="saving" x-transition.opacity>
                        <div class="bulk-card-loading-content">
                            <div class="bulk-card-loading-spinner">
                                <div class="bulk-spinner-ring"></div>
                                <div class="bulk-spinner-ring"></div>
                                <div class="bulk-spinner-ring"></div>
                            </div>
                            <span class="bulk-card-loading-text">Salvando...</span>
                        </div>
                    </div>

                    <!-- ── Badge "editado / pendente" (carrinho) ── -->
                    <div class="bulk-dirty-badge" x-show="isDirty" x-transition title="Editado — pendente de salvar">
                        <i class="bi bi-pencil-fill"></i>
                    </div>

                    <!-- ── Botões de ação (absolutos dentro do card, overlay na imagem) ── -->
                    <div class="btn-action-group">
                        <!-- Copiar nome -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="window.fmCopyText('{{ addslashes($product['name']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="bulk-act-btn bulk-act-green btn" title="Copiar nome">
                            <i x-show="!copied" class="bi bi-tag"></i>
                            <i x-show="copied" class="bi bi-check-lg"></i>
                        </button>

                        <!-- Copiar código -->
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="window.fmCopyText('{{ addslashes($product['product_code']) }}'); copied = true; setTimeout(() => copied = false, 2000)"
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
                        <div class="bulk-img-click" @click="copyNameAndPick('{{ addslashes($product['name']) }}', $refs.fileInput{{ $index }})">
                            <img :src="tempImage || '{{ $product['image_url'] }}'"
                                 class="product-img"
                                 alt="{{ $product['name'] }}">

                            <div class="bulk-img-overlay">
                                <div class="bulk-camera-pill"><i class="bi bi-camera"></i></div>
                                <span class="bulk-img-hint">Copiar nome + trocar foto</span>
                            </div>

                            <div x-show="hasTempImage" x-transition
                                 class="bulk-new-photo-badge">
                                <i class="bi bi-camera-fill"></i> Nova foto
                            </div>

                            <div x-show="nameCopied" x-transition
                                 class="bulk-name-copied-badge">
                                <i class="bi bi-clipboard-check-fill"></i> Nome copiado!
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput{{ $index }}" class="hidden" accept="image/*"
                               @change.stop="const f=$event.target.files[0]; if(!f) return; const r=new FileReader(); r.onload=e=>{ tempImage=e.target.result; markDirty(tempImage); }; r.readAsDataURL(f);">

                        <!-- Badge código -->
                        <div class="badge-product-code editable-badge" title="Código">
                            <input type="text"
                                   wire:model.lazy="productsData.{{ $index }}.product_code"
                                   class="bulk-badge-input"
                                   placeholder="Código" maxlength="15">
                        </div>

                        <!-- Badge estoque -->
                        <div class="badge-quantity editable-badge" title="Estoque">
                            <input type="number"
                                   wire:model.lazy="productsData.{{ $index }}.stock_quantity"
                                   min="0"
                                   class="bulk-badge-input"
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
                            categories: (window.__bulkCats || []),
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
                                $store.bulkCart.mark({{ $index }}, {{ $product['id'] }});
                            }
                        }">
                            <button type="button" x-ref="dt"
                                    @click.stop="open ? closeDropdown() : openDropdown()"
                                    class="bulk-cat-wrap-btn">
                                <span class="bulk-cat-selected">
                                    <i :class="selectedCategory.icon" class="bulk-cat-selected-icon"></i>
                                    <span x-text="selectedCategory.name" class="bulk-cat-selected-name"></span>
                                </span>
                                <i class="bi bi-chevron-down bulk-cat-chevron" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <template x-teleport="body">
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     @click.away="closeDropdown()"
                                     :style="dropdownIsAbove ? `position:fixed;z-index:2147483647;width:${dropdownWidth}px;bottom:${dropdownBottom}px;left:${dropdownLeft}px` : `position:fixed;z-index:2147483647;width:${dropdownWidth}px;top:${dropdownTop}px;left:${dropdownLeft}px`"
                                     class="bulk-cat-dropdown-panel">
                                    <div class="bulk-cat-search-wrap">
                                        <input type="text" x-model="search" @click.stop placeholder="Pesquisar..."
                                               class="bulk-cat-search-input">
                                    </div>
                                    <div class="bulk-cat-options">
                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                            <button type="button" @click="selectCategory(cat)"
                                                    class="bulk-cat-option">
                                                <i :class="cat.icon" class="bulk-cat-option-icon"></i>
                                                <span x-text="cat.name" class="bulk-cat-option-name"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredCategories.length === 0" class="bulk-cat-empty">Nenhuma encontrada</div>
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

                        <!-- Status de edição (sem botão — salva tudo via "Salvar Editados") -->
                        <div class="bulk-card-status" :class="{ 'is-dirty': isDirty }">
                            <template x-if="isDirty">
                                <span class="bulk-card-status-dirty">
                                    <i class="bi bi-pencil-fill"></i> Editado — pendente
                                </span>
                            </template>
                            <template x-if="!isDirty">
                                <span class="bulk-card-status-idle">
                                    @if($isSaved)
                                        <i class="bi bi-check-circle-fill"></i> Salvo
                                    @else
                                        <i class="bi bi-pencil"></i> Toque para editar
                                    @endif
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- ───────────────── PAGINAÇÃO ───────────────── -->
            @if($totalPages > 1)
            <nav class="bulk-pagination" aria-label="Paginação">
                <div class="bulk-pagination-info">
                    <i class="bi bi-collection"></i>
                    Mostrando
                    <strong>{{ ($currentPage - 1) * $perPage + 1 }}</strong>
                    -
                    <strong>{{ min($currentPage * $perPage, $totalProducts) }}</strong>
                    de
                    <strong>{{ number_format($totalProducts, 0, ',', '.') }}</strong>
                </div>

                <div class="bulk-pagination-controls">
                    <!-- Primeira página -->
                    <button type="button"
                            wire:click="goToPage(1)"
                            @disabled($currentPage === 1)
                            class="bulk-page-btn bulk-page-btn-arrow"
                            title="Primeira página">
                        <i class="bi bi-chevron-double-left"></i>
                    </button>

                    <!-- Anterior -->
                    <button type="button"
                            wire:click="previousPage"
                            @disabled($currentPage === 1)
                            class="bulk-page-btn bulk-page-btn-arrow"
                            title="Anterior">
                        <i class="bi bi-chevron-left"></i>
                        <span class="hidden sm:inline">Anterior</span>
                    </button>

                    <!-- Números de página -->
                    <div class="bulk-page-numbers">
                        @foreach($pagesArray as $p)
                            @if($p === '...')
                                <span class="bulk-page-dots">...</span>
                            @else
                                <button type="button"
                                        wire:click="goToPage({{ $p }})"
                                        class="bulk-page-btn bulk-page-btn-num {{ $p == $currentPage ? 'bulk-page-btn--active' : '' }}">
                                    {{ $p }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <!-- Próxima -->
                    <button type="button"
                            wire:click="nextPage"
                            @disabled($currentPage === $totalPages)
                            class="bulk-page-btn bulk-page-btn-arrow"
                            title="Próxima">
                        <span class="hidden sm:inline">Próxima</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <!-- Última página -->
                    <button type="button"
                            wire:click="goToPage({{ $totalPages }})"
                            @disabled($currentPage === $totalPages)
                            class="bulk-page-btn bulk-page-btn-arrow"
                            title="Última página">
                        <i class="bi bi-chevron-double-right"></i>
                    </button>
                </div>
            </nav>
            @endif
        @endif
    </div>

    <!-- ───────────────── BOTÃO FLUTUANTE: SALVAR EDITADOS (carrinho) ───────────────── -->
    <button type="button"
            x-show="$store.bulkCart.count > 0"
            x-transition
            @click="showSaveAllModal = true"
            :disabled="savingAll"
            class="bulk-fab-save-all"
            title="Salvar produtos editados"
            style="display:none;">
        <span x-show="!savingAll" class="bulk-fab-content">
            <i class="bi bi-cart-check-fill"></i>
            <span class="bulk-fab-label">Salvar Editados</span>
            <span class="bulk-fab-count" x-text="$store.bulkCart.count"></span>
        </span>
        <span x-show="savingAll" class="bulk-fab-content">
            <span class="bulk-btn-spinner"></span>
            <span class="bulk-fab-label">Salvando...</span>
        </span>
    </button>

    <!-- ───────────────── MODAL MODERNO: CONFIRMAR SALVAR EDITADOS ───────────────── -->
    <template x-teleport="body">
        <div x-show="showSaveAllModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bulk-modal-overlay"
             @click.self="showSaveAllModal = false"
             style="display: none;">
            <div class="bulk-modal-card"
                 x-show="showSaveAllModal"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="bulk-modal-icon">
                    <i class="bi bi-cart-check-fill"></i>
                </div>

                <h3 class="bulk-modal-title">Salvar produtos editados?</h3>
                <p class="bulk-modal-text">
                    <strong x-text="$store.bulkCart.count"></strong>
                    produto(s) que você editou (nome, preço, categoria ou <strong>foto</strong>)
                    serão salvos agora.
                    <span class="bulk-modal-note">
                        <i class="bi bi-info-circle"></i>
                        Os produtos que você não mexeu permanecem inalterados.
                    </span>
                </p>

                <div class="bulk-modal-actions">
                    <button type="button" @click="showSaveAllModal = false" class="bulk-modal-btn-cancel">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="button"
                            @click="showSaveAllModal = false; saveEdited()"
                            class="bulk-modal-btn-confirm">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
/* Store "carrinho" de editados — registrado uma vez */
document.addEventListener('alpine:init', () => {
    if (window.Alpine && Alpine.store('bulkCart')) return;
    Alpine.store('bulkCart', {
        items: {},
        mark(index, id, image) {
            if (!this.items[index]) this.items[index] = { id: id, image: null };
            if (image !== undefined) this.items[index].image = image;
            this.items[index].id = id;
        },
        unmark(index) { delete this.items[index]; },
        clear() { this.items = {}; },
        has(index) { return Object.prototype.hasOwnProperty.call(this.items, index); },
        get count() { return Object.keys(this.items).length; }
    });
});

function bulkEditPage() {
    return {
        showSaveAllModal: false,
        savingAll: false,

        init() {
            this.$wire.on('scroll-to-top', () => {
                const el = document.getElementById('bulk-grid-top');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            // Nova listagem (paginação/busca/filtro) → limpa o carrinho
            this.$wire.on('bulk-reloaded', () => {
                if (window.Alpine && Alpine.store('bulkCart')) Alpine.store('bulkCart').clear();
            });
        },

        async saveEdited() {
            const cart = Alpine.store('bulkCart');
            const entries = Object.entries(cart.items);
            if (!entries.length) {
                window.notifyInfo('Nenhum produto editado para salvar.');
                return;
            }
            this.savingAll = true;
            let ok = 0;
            for (const [index, data] of entries) {
                try {
                    await this.$wire.call('saveProductWithImage', parseInt(index), data.image || null, true);
                    ok++;
                } catch (e) { /* segue para o próximo */ }
            }
            cart.clear();
            this.savingAll = false;
            window.notifySuccess(ok + ' produto(s) editado(s) salvos com sucesso!');
        }
    };
}

// Helper de cópia compatível com HTTP (sem navigator.clipboard) e HTTPS
window.fmCopyText = function(text) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
            return true;
        }
    } catch (e) {}
    // Fallback universal (funciona em http://)
    try {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        return true;
    } catch (e) {
        return false;
    }
};
</script>
