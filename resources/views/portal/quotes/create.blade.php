<x-portal-layout title="Meu Carrinho">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-quotes-create.css') }}">
@endpush

@php
    $allProducts = $products->map(fn($p) => [
        'id'       => $p->id,
        'name'     => $p->name,
        'nameLow'  => strtolower($p->name),
        'cat'      => $p->category?->name ?? '',
        'catLow'   => strtolower($p->category?->name ?? ''),
        'catIcon'  => $p->category?->icone ?? 'fas fa-box',
        'price'    => $p->price_sale,
        'priceF'   => $p->price_sale ? 'R$ '.number_format($p->price_sale,2,',','.') : 'Sob consulta',
        'stock'    => max(0, (int)($p->stock_quantity ?? 0)),
        'img'      => $p->image_url ?? ($p->image ? asset('storage/products/'.$p->image) : null),
        'frequent' => in_array($p->id, $frequentIds ?? []) ? 1 : 0,
    ]);
@endphp

<div class="ec-page" x-data="{
    /* ── Dados ─── */
    allProducts: {{ Js::from($allProducts) }},
    frequentIds: {{ Js::from($frequentIds ?? []) }},
    recentIds:   [],

    /* ── Carrinho ─── */
    selected: {},

    /* ── Filtros catálogo ─── */
    search:    '',
    catFilter: 'all',
    sortMode:  'smart',   /* smart | price_asc | price_desc | name */
    stockOnly: true,
    page:  1,
    perPage: 12,

    /* ── Finalização ─── */
    paymentPreference: '',
    notesTxt: '',
    checkoutOpen: false,

    /* ── Toast ─── */
    toastMsg: '',
    toastTimer: null,

    /* ═══ Produtos ordenados (smart) ═══ */
    get products() {
        let arr = [...this.allProducts];
        if (this.sortMode === 'smart') {
            arr.sort((a, b) => {
                /* 1° carrinho */
                const aCart = this.isSelected(a.id) ? 0 : 1;
                const bCart = this.isSelected(b.id) ? 0 : 1;
                if (aCart !== bCart) return aCart - bCart;
                /* 2° recentes */
                const aRec = this.recentIds.indexOf(a.id);
                const bRec = this.recentIds.indexOf(b.id);
                const aRI  = aRec === -1 ? 999 : aRec;
                const bRI  = bRec === -1 ? 999 : bRec;
                if (aRI !== bRI) return aRI - bRI;
                /* 3° frequentes */
                if (a.frequent !== b.frequent) return b.frequent - a.frequent;
                /* 4° nome */
                return a.name.localeCompare(b.name, 'pt-BR');
            });
        } else if (this.sortMode === 'price_asc') {
            arr.sort((a,b) => (parseFloat(a.price)||0) - (parseFloat(b.price)||0));
        } else if (this.sortMode === 'price_desc') {
            arr.sort((a,b) => (parseFloat(b.price)||0) - (parseFloat(a.price)||0));
        } else {
            arr.sort((a,b) => a.name.localeCompare(b.name, 'pt-BR'));
        }
        return arr;
    },

    /* ═══ Catálogo filtrado ═══ */
    get filtered() {
        return this.products.filter(p => {
            if (this.stockOnly && p.stock <= 0) return false;
            const q = this.search.trim().toLowerCase();
            if (q && !p.nameLow.includes(q) && !p.catLow.includes(q)) return false;
            if (this.catFilter !== 'all' && p.cat !== this.catFilter) return false;
            return true;
        });
    },
    get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
    get paged()      { const s=(this.page-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
    get pageNumbers() {
        const out=[], cur=this.page, tot=this.totalPages;
        const lo=Math.max(1,cur-2), hi=Math.min(tot,cur+2);
        if(lo>1){ out.push(1); if(lo>2) out.push('…'); }
        for(let i=lo;i<=hi;i++) out.push(i);
        if(hi<tot){ if(hi<tot-1) out.push('…'); out.push(tot); }
        return out;
    },
    get uniqueCats() {
        const seen=new Set();
        return this.allProducts.filter(p=>p.cat).map(p=>({name:p.cat,icon:p.catIcon})).filter(c=>{
            if(seen.has(c.name)) return false; seen.add(c.name); return true;
        });
    },
    /* grouped by category (para fileiras horizontais) */
    get isFiltered() {
        return this.search.trim() !== '' || this.catFilter !== 'all';
    },
    get filteredGrouped() {
        const bycat = {};
        this.filtered.forEach(p => {
            const k = p.cat || 'Outros';
            if (!bycat[k]) bycat[k] = { name: k, icon: p.catIcon, items: [] };
            bycat[k].items.push(p);
        });
        return Object.values(bycat);
    },

    /* ═══ Carrinho ═══ */
    get selectedList()  { return Object.values(this.selected); },
    get selectedCount() { return this.selectedList.length; },
    get totalItems()    { return this.selectedList.reduce((a,i)=>a+(parseInt(i.qty)||1),0); },
    get totalRef()      { return this.selectedList.reduce((a,i)=>a+(parseFloat(i.price||0)*(parseInt(i.qty)||1)),0); },

    /* ═══ Métodos ═══ */
    resetPage() { this.page = 1; },

    addToCart(p) {
        if (p.stock <= 0) return;
        if (!this.selected[p.id]) {
            this.selected[p.id] = { ...p, qty: 1 };
            this.selected = { ...this.selected };
            this.saveCart();
            this.toast('Adicionado: ' + p.name);
        }
        /* scroll para o carrinho */
        document.getElementById('cart-section')?.scrollIntoView({ behavior:'smooth', block:'nearest' });
    },

    removeItem(id) {
        delete this.selected[id];
        this.selected = { ...this.selected };
        this.saveCart();
    },
    clearCart() { this.selected = {}; this.saveCart(); },
    isSelected(id) { return !!this.selected[id]; },

    setQty(id, val) {
        const item = this.selected[id];
        if (!item) return;
        const max = item.stock > 0 ? item.stock : 999;
        const q = Math.min(max, Math.max(1, parseInt(val) || 1));
        item.qty = q;
        this.selected = { ...this.selected };
        this.saveCart();
    },

    /* ═══ Persistência localStorage ═══ */
    saveCart() {
        try {
            const arr = Object.values(this.selected).map(i=>({ id:i.id, qty:i.qty||1 }));
            localStorage.setItem('portal_cart', JSON.stringify(arr));
        } catch(e) {}
    },
    loadCart() {
        try {
            const raw = localStorage.getItem('portal_cart');
            if (!raw) return;
            const arr = JSON.parse(raw);
            arr.forEach(item => {
                const p = this.allProducts.find(x => x.id === item.id);
                if (p && p.stock > 0) {
                    this.selected[p.id] = { ...p, qty: Math.min(Math.max(1,item.qty||1), p.stock) };
                }
            });
            this.selected = { ...this.selected };
        } catch(e) {}
    },

    /* ═══ Recentes localStorage ═══ */
    loadRecent() {
        try {
            const raw = localStorage.getItem('portal_recent');
            if (raw) this.recentIds = JSON.parse(raw).slice(0, 20);
        } catch(e) {}
    },
    markViewed(id) {
        try {
            let arr = JSON.parse(localStorage.getItem('portal_recent') || '[]');
            arr = [id, ...arr.filter(x=>x!==id)].slice(0,20);
            localStorage.setItem('portal_recent', JSON.stringify(arr));
            this.recentIds = arr;
        } catch(e) {}
    },

    /* ═══ Toast ═══ */
    toast(msg) {
        this.toastMsg = msg;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => this.toastMsg = '', 2200);
    },

    /* ═══ Submit ═══ */
    submitForm() {
        if (!this.selectedCount) return;
        const form = document.getElementById('quoteForm');
        const hid  = document.getElementById('hiddenInputs');
        hid.innerHTML = '';
        let idx = 0;
        for (const [id, item] of Object.entries(this.selected)) {
            hid.innerHTML += `<input type='hidden' name='items[${idx}][product_id]' value='${id}'>`;
            hid.innerHTML += `<input type='hidden' name='items[${idx}][quantity]' value='${item.qty||1}'>`;
            idx++;
        }
        localStorage.removeItem('portal_cart');
        form.submit();
    }

}" x-init="
    loadCart();
    loadRecent();
    $watch('search', () => resetPage());
    $watch('catFilter', () => resetPage());
    $watch('stockOnly', () => resetPage());
    $watch('sortMode', () => resetPage());
">

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        {{-- Row 1: ícone · título · ações --}}
        <div class="pph-row1">
            <div class="pph-icon" style="background:linear-gradient(135deg,#0ea5e9,#6366f1);">
                <i class="fas fa-basket-shopping"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney"></i> Início</a>
                    <i class="fas fa-chevron-right" style="font-size:.55rem"></i>
                    <span>Carrinho</span>
                </div>
                <h1 class="pph-title">Meu Carrinho</h1>
            </div>

            {{-- Badges de status — só sm+ --}}
            <div class="hidden sm:flex flex-wrap items-center gap-2">
                <span class="pph-badge info" x-show="selectedCount === 0">
                    <i class="fas fa-circle-info text-[8px]"></i> Adicione produtos abaixo
                </span>
                <span class="pph-badge success" x-show="selectedCount > 0">
                    <i class="fas fa-basket-shopping text-[8px]"></i>
                    <span x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'') + ' · ' + totalItems + ' un.'"></span>
                </span>
                <span class="pph-badge warning" x-show="totalRef > 0">
                    <i class="fas fa-tag text-[8px]"></i>
                    <span x-text="'R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2})"></span>
                </span>
            </div>

            {{-- Botão Finalizar — ícone+contador no mobile, texto no sm+ --}}
            <button type="button" @click="checkoutOpen=true"
                    x-show="selectedCount > 0"
                    class="pph-btn relative">
                <i class="fas fa-paper-plane" style="font-size:.75rem"></i>
                <span class="hidden sm:inline">Finalizar</span>
                <span class="inline sm:ml-1 bg-white/25 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full" x-text="selectedCount"></span>
            </button>

            {{-- Botão Pedidos — só ícone no mobile --}}
            <a href="{{ route('portal.quotes') }}"
               class="pph-btn flex-shrink-0"
               style="background:rgba(255,255,255,0.72);color:#0f172a;border:1px solid rgba(0,0,0,0.1);box-shadow:none;"
               title="Meus Pedidos">
                <i class="fas fa-list-ul" style="font-size:.75rem"></i>
                <span class="hidden sm:inline">Meus Pedidos</span>
            </a>
        </div>

        {{-- Row 2: pills de categoria (scroll horizontal no mobile) --}}
        <div class="pph-row2">
            <button type="button" @click="catFilter='all'; resetPage()" class="pph-pill" :class="{'active': catFilter==='all'}">
                <i class="fas fa-grid-2" style="font-size:.6rem"></i> Todos
            </button>
            <template x-for="c in uniqueCats" :key="c.name">
                <button type="button" @click="catFilter = catFilter===c.name ? 'all' : c.name; resetPage()"
                        class="pph-pill" :class="{'active': catFilter===c.name}">
                    <i :class="c.icon" style="font-size:.6rem"></i>
                    <span x-text="c.name"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         SEÇÃO 1 — CARRINHO
    ════════════════════════════════════════ --}}
    <div id="cart-section" x-show="selectedCount > 0" class="ec-cart-section">

        <div class="ec-section-head">
            <div class="ec-section-icon" style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                <i class="fas fa-basket-shopping text-white text-sm"></i>
            </div>
            <div>
                <h2 class="ec-section-title">Seu Carrinho</h2>
                <p class="ec-section-sub"
                   x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'') + ' · ' + totalItems + ' unidade' + (totalItems!==1?'s':'')"></p>
            </div>
            <button type="button" @click="clearCart()"
                    class="ml-auto text-xs text-red-400 hover:text-red-600 font-bold flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <i class="fas fa-trash text-[10px]"></i> Limpar carrinho
            </button>
        </div>

        {{-- Cards do carrinho (MESMO formato do catálogo) --}}
        <div class="ec-cart-grid">
            <template x-for="item in selectedList" :key="item.id">
                <div class="portal-product-card ec-cart-card">
                    {{-- Área imagem --}}
                    <div class="portal-product-img-area ec-cart-img">
                        <template x-if="item.img">
                            <img :src="item.img" :alt="item.name" loading="lazy">
                        </template>
                        <template x-if="!item.img">
                            <div class="pimg-placeholder">
                                <i class="fas fa-box text-3xl" style="color:#bae6fd"></i>
                            </div>
                        </template>
                        {{-- Badge estoque --}}
                        <span class="badge-stock"
                              :class="item.stock > 5 ? 'bg-emerald-500/90 text-white shadow-sm' : 'bg-amber-400/90 text-white shadow-sm'">
                            <i class="fas fa-layer-group text-[9px]"></i>
                            <span x-text="item.stock"></span>
                        </span>
                        {{-- Botão remover --}}
                        <button type="button" @click="removeItem(item.id)"
                                class="ec-remove-btn" title="Remover">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                        {{-- Círculo categoria --}}
                        <div class="pcard-cat-circle ec-cat-circle">
                            <i :class="item.catIcon"></i>
                        </div>
                    </div>
                    {{-- Corpo --}}
                    <div class="pcard-body ec-cart-body">
                        <p class="text-[9px] font-black uppercase tracking-[.2em] text-sky-600 dark:text-sky-400 truncate w-full" x-text="item.cat" x-show="item.cat"></p>
                        <h3 class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 min-h-[2rem] w-full" x-text="item.name"></h3>
                        <p class="text-base font-black text-sky-700 dark:text-sky-400 leading-none mt-0.5" x-text="item.priceF" x-show="item.price"></p>
                        {{-- Qty controls --}}
                        <div class="ec-qty-wrap" @click.stop>
                            <button type="button" @click="setQty(item.id, (item.qty||1)-1)" class="ec-qty-btn">−</button>
                            <input type="number"
                                   :value="item.qty||1"
                                   :max="item.stock>0?item.stock:1"
                                   min="1"
                                   @change="setQty(item.id, $event.target.value)"
                                   class="ec-qty-input">
                            <button type="button"
                                    @click="setQty(item.id, (item.qty||1)+1)"
                                    :disabled="item.stock>0 && (item.qty||1)>=item.stock"
                                    class="ec-qty-btn">+</button>
                        </div>
                        {{-- Subtotal --}}
                        <p class="text-[10px] font-black text-gray-500 dark:text-slate-400 mt-0.5"
                           x-show="item.price"
                           x-text="'= R$ ' + ((parseFloat(item.price)||0)*(parseInt(item.qty)||1)).toLocaleString('pt-BR',{minimumFractionDigits:2})"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Barra de total + pagamento + submit --}}
        <div class="ec-checkout-bar">
            <div class="ec-checkout-total">
                <div class="flex flex-col">
                    <span class="text-[9px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Valor de referência*</span>
                    <span class="text-2xl font-black text-sky-700 dark:text-sky-300"
                          x-text="'R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                    <span class="text-[9px] text-gray-400 dark:text-slate-500">*sujeito a negociação</span>
                </div>
            </div>

            <div class="ec-checkout-payment">
                <span class="text-[9px] text-gray-500 dark:text-slate-400 font-black uppercase tracking-wider block mb-1.5">
                    <i class="fas fa-credit-card text-sky-400 mr-1"></i> Pagamento preferido (opcional)
                </span>
                {{-- 3 colunas no mobile, lista no sm+ --}}
                <div class="grid grid-cols-3 sm:flex sm:flex-wrap gap-1.5">
                    <template x-for="opt in [
                        {val:'pix',      icon:'fas fa-qrcode',         label:'PIX'},
                        {val:'dinheiro', icon:'fas fa-money-bill-wave', label:'Dinheiro'},
                        {val:'credito',  icon:'fas fa-credit-card',     label:'Crédito'},
                        {val:'debito',   icon:'fas fa-credit-card',     label:'Débito'},
                        {val:'boleto',   icon:'fas fa-barcode',         label:'Boleto'},
                        {val:'outro',    icon:'fas fa-ellipsis-h',      label:'Outro'},
                    ]" :key="opt.val">
                        <button type="button"
                                @click="paymentPreference = paymentPreference === opt.val ? '' : opt.val"
                                :class="paymentPreference === opt.val
                                    ? 'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-500/20'
                                    : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 dark:border-slate-600 hover:border-sky-400 hover:text-sky-600'"
                                class="flex items-center justify-center gap-1 sm:gap-1.5 px-1.5 sm:px-2.5 py-1.5 border rounded-xl text-[10px] font-bold transition-all w-full sm:w-auto">
                            <i :class="opt.icon" class="text-xs flex-shrink-0"></i>
                            <span x-text="opt.label" class="truncate text-[9px] sm:text-[10px]"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="ec-checkout-notes">
                <label class="text-[9px] text-gray-500 dark:text-slate-400 font-black uppercase tracking-wider block mb-1.5">
                    <i class="fas fa-comment-dots text-sky-400 mr-1"></i> Observações
                </label>
                <textarea x-model="notesTxt" rows="2"
                          placeholder="Ex: Entrega urgente, cor preferida..."
                          class="portal-input text-xs resize-none"></textarea>
            </div>

            <div class="ec-checkout-submit">
                <button type="button" @click="submitForm()" class="ec-submit-btn">
                    <i class="fas fa-paper-plane text-sm"></i>
                    <span>Enviar Orçamento</span>
                    <span class="ec-submit-count" x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'')"></span>
                </button>
            </div>
        </div>
    </div>{{-- /ec-cart-section --}}

    {{-- ════════════════════════════════════════
         SEÇÃO 2 — CATÁLOGO
    ════════════════════════════════════════ --}}
    <div class="ec-catalog-section">

        <div class="ec-section-head">
            <div class="ec-section-icon" style="background:linear-gradient(135deg,#6366f1,#a855f7)">
                <i class="fas fa-store text-white text-sm"></i>
            </div>
            <div>
                <h2 class="ec-section-title" x-text="selectedCount > 0 ? 'Adicionar mais produtos' : 'Catálogo de Produtos'"></h2>
                <p class="ec-section-sub" x-text="filtered.length + ' produto' + (filtered.length!==1?'s':'') + ' disponíveis'"></p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="ec-filters">
            <div class="relative flex-1 min-w-36">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-sky-400 text-sm pointer-events-none"></i>
                <input x-model.debounce.200ms="search" type="text"
                       placeholder="Buscar produto..."
                       class="portal-input pl-10 pr-9 py-2.5 text-sm">
                <button type="button" x-show="search" @click="search=''; resetPage()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            <select x-model="sortMode" @change="resetPage()"
                    class="portal-input py-2.5 text-sm flex-shrink-0 w-auto min-w-[10rem]">
                <option value="smart">✦ Relevância</option>
                <option value="name">A → Z</option>
                <option value="price_asc">Menor preço</option>
                <option value="price_desc">Maior preço</option>
            </select>

            <button type="button" @click="stockOnly=!stockOnly; resetPage()"
                    :class="stockOnly ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700' : 'bg-white dark:bg-slate-800 text-gray-500 dark:text-slate-400 border-gray-200 dark:border-slate-600'"
                    class="flex items-center gap-2 px-3 py-2.5 border rounded-xl text-xs font-bold transition-all flex-shrink-0">
                <i class="fas fa-box-open text-[10px]"></i>
                <span x-text="stockOnly ? 'Em estoque' : 'Todos'"></span>
            </button>
        </div>

        {{-- Sem resultados --}}
        <div x-show="filtered.length === 0" class="portal-card py-16 text-center">
            <i class="fas fa-box-open text-4xl text-gray-200 dark:text-slate-600 mb-3 block"></i>
            <p class="text-sm font-bold text-gray-400 dark:text-slate-500">Nenhum produto encontrado</p>
            <button type="button" @click="search=''; catFilter='all'; stockOnly=true; resetPage()"
                    class="mt-3 text-xs text-sky-500 hover:underline">Limpar filtros</button>
        </div>

        {{-- MODO AGRUPADO: fileiras por categoria (sem filtro ativo) --}}
        <div x-show="filtered.length > 0 && !isFiltered">
            <template x-for="group in filteredGrouped" :key="group.name">
                <div class="mb-8">
                    {{-- cabeçalho da categoria --}}
                    <div class="ec-cat-row-head">
                        <div class="ec-cat-row-icon">
                            <i :class="group.icon" class="text-xs"></i>
                        </div>
                        <div>
                            <h3 class="ec-cat-row-title" x-text="group.name"></h3>
                            <p class="ec-cat-row-sub" x-text="group.items.length + ' produto' + (group.items.length!==1?'s':'')"></p>
                        </div>
                        <button type="button"
                                @click="catFilter = group.name; resetPage()"
                                class="ml-auto text-[10px] font-bold text-sky-500 hover:text-sky-700 flex items-center gap-1 px-2.5 py-1.5 rounded-full hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors whitespace-nowrap">
                            Ver todos <i class="fas fa-arrow-right text-[9px]"></i>
                        </button>
                    </div>
                    {{-- fileira scroll horizontal --}}
                    <div class="pcat-scroll-row">
                        <template x-for="p in group.items" :key="p.id">
                            <div class="portal-product-card ec-catalog-card"
                                 :class="{ 'ec-in-cart': isSelected(p.id), 'ec-out-stock': p.stock<=0 }"
                                 @mouseenter="markViewed(p.id)">

                                <div x-show="isSelected(p.id)" class="ec-in-cart-badge">
                                    <i class="fas fa-check text-[8px]"></i> No carrinho
                                </div>

                                <div class="portal-product-img-area">
                                    <template x-if="p.img">
                                        <img :src="p.img" :alt="p.name" loading="lazy">
                                    </template>
                                    <template x-if="!p.img">
                                        <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                                            <i class="fas fa-box text-3xl text-sky-200 dark:text-slate-600"></i>
                                        </div>
                                    </template>
                                    <span class="badge-stock"
                                          :class="p.stock>5 ? 'bg-emerald-500/90 text-white' : p.stock>0 ? 'bg-amber-400/90 text-white' : 'bg-red-400/90 text-white'">
                                        <i class="fas fa-layer-group text-[9px]"></i>
                                        <span x-text="p.stock > 0 ? p.stock : '!'"></span>
                                    </span>
                                    <div x-show="!isSelected(p.id) && recentIds.slice(0,5).includes(p.id)" class="ec-smart-tag recent">
                                        <i class="fas fa-eye text-[8px]"></i> Recente
                                    </div>
                                    <div x-show="!isSelected(p.id) && p.frequent && !recentIds.slice(0,5).includes(p.id)" class="ec-smart-tag popular">
                                        <i class="fas fa-fire text-[8px]"></i> Popular
                                    </div>
                                </div>

                                <div class="pcard-body">
                                    <h3 class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 min-h-[2rem] w-full" x-text="p.name"></h3>
                                    <p class="text-base font-black text-sky-700 dark:text-sky-400 leading-none mt-1" x-text="p.priceF" x-show="p.price"></p>
                                    <p class="text-[10px] italic text-gray-400 dark:text-slate-500 mt-1" x-show="!p.price">Sob consulta</p>

                                    <div class="mt-2 ec-add-wrap" @click.stop>
                                        <button type="button" x-show="!isSelected(p.id) && p.stock > 0"
                                                @click="addToCart(p)"
                                                class="ec-add-btn">
                                            <i class="fas fa-plus text-[9px]"></i> Adicionar
                                        </button>
                                        <div x-show="isSelected(p.id)" class="ec-added-controls">
                                            <button type="button" @click="setQty(p.id, (selected[p.id]?.qty||1)-1)" class="ec-qty-sm">−</button>
                                            <input type="number" :value="selected[p.id]?.qty||1" :max="p.stock" min="1"
                                                   @change="setQty(p.id, $event.target.value)" class="ec-qty-input-sm">
                                            <button type="button" @click="setQty(p.id, (selected[p.id]?.qty||1)+1)"
                                                    :disabled="p.stock>0 && (selected[p.id]?.qty||1)>=p.stock" class="ec-qty-sm">+</button>
                                            <button type="button" @click="removeItem(p.id)" class="ec-remove-sm">
                                                <i class="fas fa-trash text-[8px]"></i>
                                            </button>
                                        </div>
                                        <div x-show="p.stock <= 0" class="ec-no-stock">
                                            <i class="fas fa-ban text-[9px]"></i> Sem estoque
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- MODO LISTA: com filtro ativo (busca ou categoria) --}}
        <div x-show="filtered.length > 0 && isFiltered">
            {{-- filtro ativo badge --}}
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xs text-slate-500 dark:text-slate-400">Filtrando:</span>
                <template x-if="search.trim()">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 rounded-full text-xs font-bold">
                        <i class="fas fa-search text-[9px]"></i> <span x-text="search.trim()"></span>
                        <button @click="search=''; resetPage()" class="ml-1 hover:text-sky-900 dark:hover:text-sky-100"><i class="fas fa-xmark text-[9px]"></i></button>
                    </span>
                </template>
                <template x-if="catFilter !== 'all'">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold">
                        <i class="fas fa-tag text-[9px]"></i> <span x-text="catFilter"></span>
                        <button @click="catFilter='all'; resetPage()" class="ml-1 hover:text-indigo-900 dark:hover:text-indigo-100"><i class="fas fa-xmark text-[9px]"></i></button>
                    </span>
                </template>
            </div>

            <div class="ec-catalog-grid">
                <template x-for="p in paged" :key="p.id">
                    <div class="portal-product-card ec-catalog-card"
                         :class="{ 'ec-in-cart': isSelected(p.id), 'ec-out-stock': p.stock<=0 }"
                         @mouseenter="markViewed(p.id)">

                        <div x-show="isSelected(p.id)" class="ec-in-cart-badge">
                            <i class="fas fa-check text-[8px]"></i> No carrinho
                        </div>

                        <div class="portal-product-img-area">
                            <template x-if="p.img">
                                <img :src="p.img" :alt="p.name" loading="lazy">
                            </template>
                            <template x-if="!p.img">
                                <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                                    <i class="fas fa-box text-3xl text-sky-200 dark:text-slate-600"></i>
                                </div>
                            </template>
                            <span class="badge-stock"
                                  :class="p.stock>5 ? 'bg-emerald-500/90 text-white' : p.stock>0 ? 'bg-amber-400/90 text-white' : 'bg-red-400/90 text-white'">
                                <i class="fas fa-layer-group text-[9px]"></i>
                                <span x-text="p.stock > 0 ? p.stock : '!'"></span>
                            </span>
                            <div x-show="!isSelected(p.id) && recentIds.slice(0,5).includes(p.id)" class="ec-smart-tag recent">
                                <i class="fas fa-eye text-[8px]"></i> Recente
                            </div>
                            <div x-show="!isSelected(p.id) && p.frequent && !recentIds.slice(0,5).includes(p.id)" class="ec-smart-tag popular">
                                <i class="fas fa-fire text-[8px]"></i> Popular
                            </div>
                        </div>

                        <div class="pcard-body">
                            <p class="text-[9px] font-black uppercase tracking-[.2em] text-sky-600 dark:text-sky-400 truncate w-full" x-text="p.cat" x-show="p.cat"></p>
                            <h3 class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 min-h-[2rem] w-full" x-text="p.name"></h3>
                            <p class="text-base font-black text-sky-700 dark:text-sky-400 leading-none mt-1" x-text="p.priceF" x-show="p.price"></p>
                            <p class="text-[10px] italic text-gray-400 dark:text-slate-500 mt-1" x-show="!p.price">Sob consulta</p>

                            <div class="mt-2 ec-add-wrap" @click.stop>
                                <button type="button" x-show="!isSelected(p.id) && p.stock > 0"
                                        @click="addToCart(p)"
                                        class="ec-add-btn">
                                    <i class="fas fa-plus text-[9px]"></i> Adicionar
                                </button>
                                <div x-show="isSelected(p.id)" class="ec-added-controls">
                                    <button type="button" @click="setQty(p.id, (selected[p.id]?.qty||1)-1)" class="ec-qty-sm">−</button>
                                    <input type="number" :value="selected[p.id]?.qty||1" :max="p.stock" min="1"
                                           @change="setQty(p.id, $event.target.value)" class="ec-qty-input-sm">
                                    <button type="button" @click="setQty(p.id, (selected[p.id]?.qty||1)+1)"
                                            :disabled="p.stock>0 && (selected[p.id]?.qty||1)>=p.stock" class="ec-qty-sm">+</button>
                                    <button type="button" @click="removeItem(p.id)" class="ec-remove-sm">
                                        <i class="fas fa-trash text-[8px]"></i>
                                    </button>
                                </div>
                                <div x-show="p.stock <= 0" class="ec-no-stock">
                                    <i class="fas fa-ban text-[9px]"></i> Sem estoque
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>{{-- /MODO LISTA --}}

        {{-- Paginação (só no modo lista com filtro) --}}
        <div class="ec-pagination" x-show="isFiltered && totalPages > 1">
            <div class="ec-pag-info">
                <strong x-text="Math.min((page-1)*perPage+1, filtered.length)"></strong>
                –<strong x-text="Math.min(page*perPage, filtered.length)"></strong>
                de <strong x-text="filtered.length"></strong>
            </div>
            <div class="ec-pag-btns">
                <button type="button" @click="page=Math.max(1,page-1)" :disabled="page===1" class="ec-pag-btn">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </button>
                <template x-for="(n,ni) in pageNumbers" :key="ni">
                    <button type="button" @click="typeof n==='number'?page=n:null"
                            class="ec-pag-btn" :class="{'active':n===page,'dots':n==='…'}"
                            x-text="n" :disabled="n==='…'"></button>
                </template>
                <button type="button" @click="page=Math.min(totalPages,page+1)" :disabled="page===totalPages" class="ec-pag-btn">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </button>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] text-gray-400">Por pág.</span>
                <template x-for="pp in [12,24,48]" :key="pp">
                    <button type="button" @click="perPage=pp; resetPage()"
                            class="ec-pag-btn text-[10px]" :class="{'active':perPage===pp}" x-text="pp"></button>
                </template>
            </div>
        </div>

    </div>{{-- /ec-catalog-section --}}

    {{-- Formulário oculto --}}
    <form method="POST" action="{{ route('portal.quotes.store') }}" id="quoteForm" class="hidden">
        @csrf
        <div id="hiddenInputs"></div>
        <input type="hidden" name="payment_preference" :value="paymentPreference">
        <input type="hidden" name="client_notes" :value="notesTxt">
    </form>

    {{-- ── Mini-bar sticky (mobile/tablet) — aparece ao rolar para catálogo ── --}}
    <div id="ec-mini-bar" class="ec-mini-bar" x-show="selectedCount > 0" x-cloak>
        <div class="ec-mini-bar-inner">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                <i class="fas fa-basket-shopping text-white text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-black text-gray-900 dark:text-slate-200 truncate"
                   x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'') + ' • ' + totalItems + ' un.'"></p>
                <p class="text-[10px] font-bold text-sky-600 dark:text-sky-400"
                   x-text="'Ref: R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2})" x-show="totalRef > 0"></p>
            </div>
            <button type="button"
                    @click="document.getElementById('cart-section')?.scrollIntoView({behavior:'smooth', block:'start'})"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 text-[10px] font-black rounded-xl border border-sky-200 dark:border-sky-700/50 flex-shrink-0">
                <i class="fas fa-arrow-up text-[9px]"></i> Ver carrinho
            </button>
            <button type="button" @click="checkoutOpen=true"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-white text-[10px] font-black rounded-xl flex-shrink-0"
                    style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                <i class="fas fa-paper-plane text-[9px]"></i> Finalizar
            </button>
        </div>
    </div>

    {{-- ── Barra flutuante mobile (acima da tab bar) ── --}}
    <div class="ec-float-bar" x-show="selectedCount > 0 && !checkoutOpen" x-cloak>
        <button type="button" @click="checkoutOpen=true" class="ec-float-btn relative">
            <div class="relative flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-basket-shopping text-lg"></i>
                </div>
                <span class="ec-float-count" x-text="selectedCount"></span>
            </div>
            <div class="flex flex-col items-start min-w-0">
                <span class="font-black text-sm leading-tight">Finalizar Orçamento</span>
                <span class="text-[10px] opacity-80 leading-none mt-0.5"
                      x-text="totalItems + ' un. · R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2})"
                      x-show="totalRef > 0"></span>
            </div>
            <i class="fas fa-chevron-right ml-auto opacity-70 text-sm"></i>
        </button>
    </div>

    {{-- Modal de checkout (mobile) --}}
    <div class="ec-checkout-modal" :class="{'open': checkoutOpen}" x-show="checkoutOpen" x-cloak>
        <div class="ec-checkout-modal-backdrop" @click="checkoutOpen=false"></div>
        <div class="ec-checkout-modal-panel">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-slate-700">
                <h3 class="font-black text-gray-900 dark:text-slate-200">Finalizar Orçamento</h3>
                <button type="button" @click="checkoutOpen=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-4 overflow-y-auto flex-1">
                {{-- Resumo dos itens --}}
                <div class="space-y-2 mb-4">
                    <template x-for="item in selectedList" :key="item.id">
                        <div class="flex items-center gap-3 p-2 bg-sky-50 dark:bg-sky-900/20 rounded-xl">
                            <template x-if="item.img">
                                <img :src="item.img" :alt="item.name" class="w-10 h-10 rounded-lg object-contain bg-white">
                            </template>
                            <template x-if="!item.img">
                                <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-sky-300 text-sm"></i>
                                </div>
                            </template>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black truncate" x-text="item.name"></p>
                                <p class="text-[10px] text-sky-600 dark:text-sky-400 font-bold" x-text="item.priceF" x-show="item.price"></p>
                            </div>
                            <div class="flex items-center gap-1" @click.stop>
                                <button type="button" @click="setQty(item.id,(item.qty||1)-1)" class="ec-qty-sm">−</button>
                                <span class="text-xs font-black w-6 text-center" x-text="item.qty||1"></span>
                                <button type="button" @click="setQty(item.id,(item.qty||1)+1)"
                                        :disabled="item.stock>0&&(item.qty||1)>=item.stock" class="ec-qty-sm">+</button>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- Total --}}
                <div class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20 rounded-xl p-3 mb-4 border border-sky-100 dark:border-sky-800/30">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-700 dark:text-slate-300">Valor de referência</span>
                        <span class="text-xl font-black text-sky-700 dark:text-sky-300"
                              x-text="'R$ '+totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                    </div>
                </div>
                {{-- Pagamento --}}
                <div class="mb-4">
                    <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-2">Pagamento preferido</p>
                    <div class="grid grid-cols-3 gap-1.5">
                        <template x-for="opt in [
                            {val:'pix',icon:'fas fa-qrcode',label:'PIX'},
                            {val:'dinheiro',icon:'fas fa-money-bill-wave',label:'Dinheiro'},
                            {val:'credito',icon:'fas fa-credit-card',label:'Crédito'},
                            {val:'debito',icon:'fas fa-credit-card',label:'Débito'},
                            {val:'boleto',icon:'fas fa-barcode',label:'Boleto'},
                            {val:'outro',icon:'fas fa-ellipsis-h',label:'Outro'},
                        ]" :key="opt.val">
                            <button type="button"
                                    @click="paymentPreference = paymentPreference===opt.val ? '' : opt.val"
                                    :class="paymentPreference===opt.val
                                        ? 'bg-sky-500 text-white border-sky-500 shadow-md'
                                        : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 dark:border-slate-600 hover:border-sky-400'"
                                    class="flex flex-col items-center gap-0.5 py-2 border rounded-xl text-[9px] font-bold transition-all">
                                <i :class="opt.icon" class="text-sm"></i>
                                <span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
                {{-- Observações --}}
                <div class="mb-4">
                    <label class="text-[9px] text-gray-500 font-black uppercase tracking-wider block mb-1.5">Observações</label>
                    <textarea x-model="notesTxt" rows="2"
                              placeholder="Ex: Entrega urgente..."
                              class="portal-input text-xs resize-none"></textarea>
                </div>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-slate-700">
                <button type="button" @click="submitForm()" class="ec-submit-btn w-full">
                    <i class="fas fa-paper-plane text-sm"></i>
                    Enviar Orçamento
                    <span class="ec-submit-count" x-text="selectedCount + ' item' + (selectedCount!==1?'ns':'')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="ec-toast" :class="{'show': toastMsg}" x-show="toastMsg" x-cloak>
        <i class="fas fa-check-circle text-emerald-400 flex-shrink-0"></i>
        <span class="text-sm font-bold truncate" x-text="toastMsg"></span>
    </div>

</div>{{-- /ec-page --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ─── Pré-selecionar produto via ?product_id=X ─── */
    const preId = new URLSearchParams(window.location.search).get('product_id');
    if (preId) {
        const tryPre = () => {
            const el = document.querySelector('.ec-page');
            if (el && el._x_dataStack?.[0]) {
                const c = el._x_dataStack[0];
                const p = c.allProducts.find(x => String(x.id) === String(preId));
                if (p && p.stock > 0 && !c.isSelected(p.id)) c.addToCart(p);
            } else { setTimeout(tryPre, 150); }
        };
        tryPre();
    }

    /* ─── Mini-bar sticky: aparece quando cart section sai da viewport ─── */
    const miniBar    = document.getElementById('ec-mini-bar');
    const cartSection = document.getElementById('cart-section');
    if (miniBar && cartSection && window.innerWidth < 1024) {
        const obs = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    miniBar.classList.remove('visible');
                } else {
                    /* Só mostra se tem itens no carrinho */
                    const page = document.querySelector('.ec-page');
                    const count = page?._x_dataStack?.[0]?.selectedCount ?? 0;
                    if (count > 0) miniBar.classList.add('visible');
                }
            },
            { rootMargin: '0px', threshold: 0.1 }
        );
        obs.observe(cartSection);

        /* Re-verificar ao fazer scroll manual — para quando cart está oculto (vazio) */
        document.addEventListener('alpine:initialized', () => {
            Alpine.effect(() => {
                const page = document.querySelector('.ec-page');
                const count = page?._x_dataStack?.[0]?.selectedCount ?? 0;
                if (count === 0) miniBar.classList.remove('visible');
            });
        });
    }
});
</script>
@endpush

</x-portal-layout>
