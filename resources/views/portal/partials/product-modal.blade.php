{{--
    MODAL DE PRODUTO — Portal do Cliente
    Dispara via: $dispatch('open-product-modal', { id: productId })
    Requer: addToCart(id,name,price,stock,img) e inCart(id) no Alpine pai
--}}
<div x-data="{
    open: false,
    prod: null,
    currentImg: 0,
    cart: [],
    products: @js($products_for_modal ?? []),

    init() {
        this.cart = JSON.parse(localStorage.getItem('portal_cart') || '[]');
        window.addEventListener('storage', () => {
            this.cart = JSON.parse(localStorage.getItem('portal_cart') || '[]');
        });
    },
    inCart(id) { return this.cart.some(x => x.id === id); },
    addToCart(id, name, price, stock, img) {
        const arr = JSON.parse(localStorage.getItem('portal_cart') || '[]');
        const idx = arr.findIndex(x => x.id === id);
        if (idx >= 0) { arr[idx].qty = Math.min(arr[idx].qty + 1, stock || 999); }
        else { arr.push({ id, name, price: parseFloat(price)||0, stock: parseInt(stock)||0, img: img||null, qty: 1 }); }
        localStorage.setItem('portal_cart', JSON.stringify(arr));
        this.cart = arr;
        this.$dispatch('portal-cart-updated');
    },

    openModal(id) {
        this.prod = this.products[id] ?? null;
        this.currentImg = 0;
        if (this.prod) {
            this.cart = JSON.parse(localStorage.getItem('portal_cart') || '[]');
            this.open = true;
        }
    },
    closeModal() {
        this.open = false;
        setTimeout(() => { this.prod = null; this.currentImg = 0; }, 300);
    },
    allImages() {
        if (!this.prod) return [];
        const imgs = this.prod.images ?? [];
        if (imgs.length) return imgs;
        return this.prod.mainImg ? [this.prod.mainImg] : [];
    },
    prevImg() {
        const n = this.allImages().length;
        if (n < 2) return;
        this.currentImg = (this.currentImg - 1 + n) % n;
    },
    nextImg() {
        const n = this.allImages().length;
        if (n < 2) return;
        this.currentImg = (this.currentImg + 1) % n;
    }
}"
@open-product-modal.window="openModal($event.detail.id)"
@keydown.escape.window="if(open) closeModal()">

    {{-- OVERLAY --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="fixed inset-0 z-[300]"
         style="background:rgba(0,0,0,0.55);backdrop-filter:blur(6px)"
         x-cloak></div>

    {{-- PAINEL (bottom-sheet mobile / dialog desktop) --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
         class="pmod-panel"
         x-cloak>

        <template x-if="prod">
            <div class="flex flex-col h-full">

                {{-- HEADER --}}
                <div class="pmod-header">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <div class="pmod-cat-icon" x-show="prod.catIcon">
                            <i :class="prod.catIcon" class="text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="pmod-cat-label" x-text="prod.catName" x-show="prod.catName"></p>
                            <h2 class="pmod-title" x-text="prod.name"></h2>
                        </div>
                    </div>
                    <button @click="closeModal()" class="pmod-close" aria-label="Fechar">
                        <i class="fas fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- SCROLL --}}
                <div class="pmod-body">

                    {{-- GALERIA --}}
                    <div class="pmod-gallery">
                        {{-- imagem principal --}}
                        <div class="pmod-main-img-wrap">
                            <template x-for="(img, i) in allImages()" :key="i">
                                <img :src="img" :alt="prod.name + ' foto ' + (i+1)"
                                     x-show="currentImg === i"
                                     class="pmod-main-img"
                                     loading="lazy">
                            </template>
                            <div x-show="allImages().length === 0" class="pmod-img-placeholder">
                                <i class="fas fa-box text-4xl text-sky-200 dark:text-slate-600"></i>
                            </div>

                            {{-- setas --}}
                            <button x-show="allImages().length > 1"
                                    @click.stop="prevImg()"
                                    class="pmod-arrow pmod-arrow-left" aria-label="Anterior">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button x-show="allImages().length > 1"
                                    @click.stop="nextImg()"
                                    class="pmod-arrow pmod-arrow-right" aria-label="Próxima">
                                <i class="fas fa-chevron-right"></i>
                            </button>

                            {{-- contador --}}
                            <span x-show="allImages().length > 1"
                                  class="pmod-img-counter"
                                  x-text="(currentImg+1) + ' / ' + allImages().length"></span>
                        </div>

                        {{-- thumbnails --}}
                        <div class="pmod-thumbs" x-show="allImages().length > 1">
                            <template x-for="(img, i) in allImages()" :key="i">
                                <button @click.stop="currentImg = i"
                                        :class="currentImg === i ? 'pmod-thumb active' : 'pmod-thumb'">
                                    <img :src="img" :alt="'Foto ' + (i+1)" class="w-full h-full object-cover rounded-lg">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- PREÇO + BOTÃO --}}
                    <div class="pmod-price-row">
                        <div>
                            <p class="pmod-price-label">Valor</p>
                            <p class="pmod-price" x-text="prod.priceFmt || 'Consultar'"></p>
                        </div>
                        <button type="button"
                            @click="addToCart(prod.id, prod.name, prod.price, prod.stock, allImages()[0] ?? null)"
                            :class="inCart(prod.id) ? 'pmod-btn-added' : 'pmod-btn-add'">
                            <i :class="inCart(prod.id) ? 'fas fa-check' : 'fas fa-basket-shopping'"></i>
                            <span x-text="inCart(prod.id) ? 'No carrinho' : 'Adicionar'"></span>
                        </button>
                    </div>

                    {{-- CHIPS DE INFO --}}
                    <div class="pmod-details-grid">

                        <div class="pmod-info-chip">
                            <div class="pmod-info-icon" style="background:rgba(16,185,129,0.12)">
                                <i class="fas fa-layer-group text-emerald-600 dark:text-emerald-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="pmod-info-label">Estoque</p>
                                <p class="pmod-info-value" x-text="prod.stock + ' un.'"></p>
                            </div>
                        </div>

                        <template x-if="prod.code">
                            <div class="pmod-info-chip">
                                <div class="pmod-info-icon" style="background:rgba(99,102,241,0.10)">
                                    <i class="fas fa-barcode text-indigo-500 dark:text-indigo-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="pmod-info-label">Código</p>
                                    <p class="pmod-info-value" x-text="prod.code"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="prod.brand">
                            <div class="pmod-info-chip">
                                <div class="pmod-info-icon" style="background:rgba(14,165,233,0.10)">
                                    <i class="fas fa-tag text-sky-600 dark:text-sky-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="pmod-info-label">Marca</p>
                                    <p class="pmod-info-value" x-text="prod.brand"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="prod.model">
                            <div class="pmod-info-chip">
                                <div class="pmod-info-icon" style="background:rgba(245,158,11,0.10)">
                                    <i class="fas fa-cube text-amber-600 dark:text-amber-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="pmod-info-label">Modelo</p>
                                    <p class="pmod-info-value" x-text="prod.model"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="prod.condition">
                            <div class="pmod-info-chip">
                                <div class="pmod-info-icon" style="background:rgba(139,92,246,0.10)">
                                    <i class="fas fa-star text-violet-600 dark:text-violet-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="pmod-info-label">Condição</p>
                                    <p class="pmod-info-value" x-text="prod.condition === 'new' ? 'Novo' : 'Usado'"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="prod.warranty">
                            <div class="pmod-info-chip">
                                <div class="pmod-info-icon" style="background:rgba(16,185,129,0.10)">
                                    <i class="fas fa-shield-halved text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="pmod-info-label">Garantia</p>
                                    <p class="pmod-info-value" x-text="prod.warranty + ' meses'"></p>
                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- DESCRIÇÃO --}}
                    <template x-if="prod.description">
                        <div class="pmod-desc-block">
                            <p class="pmod-desc-label">
                                <i class="fas fa-align-left mr-1.5 text-xs"></i> Descrição
                            </p>
                            <p class="pmod-desc-text" x-text="prod.description"></p>
                        </div>
                    </template>

                </div>{{-- /pmod-body --}}

                {{-- FOOTER FIXO --}}
                <div class="pmod-footer">
                    <button @click="closeModal()" class="pmod-footer-close">Fechar</button>

                    <template x-if="!inCart(prod.id)">
                        <button type="button"
                            @click="addToCart(prod.id, prod.name, prod.price, prod.stock, allImages()[0] ?? null); closeModal()"
                            class="pmod-footer-add">
                            <i class="fas fa-basket-shopping text-xs"></i>
                            Adicionar ao carrinho
                        </button>
                    </template>

                    <template x-if="inCart(prod.id)">
                        <a href="{{ route('portal.quotes.create') }}" class="pmod-footer-cart">
                            <i class="fas fa-arrow-right text-xs"></i>
                            Ver carrinho
                        </a>
                    </template>
                </div>

            </div>
        </template>

    </div>

</div>
