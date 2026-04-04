<x-portal-layout title="Novo Orçamento">

@push('styles')
<style>
/* ── Seleção de card ── */
.portal-product-card .sel-badge { display:none; }
.portal-product-card.selected .sel-badge { display:flex; }
.portal-product-card.selected {
    border-color:#0ea5e9 !important;
    box-shadow:0 0 0 3px rgba(14,165,233,0.25), 0 10px 30px rgba(14,165,233,0.22) !important;
    transform:translateY(-4px) !important;
}
.portal-product-card .product-details { display:none; }
.portal-product-card.selected .product-details { display:flex; }

/* ── Resumo sticky ── */
#summaryPanel.has-items { border-color:#38bdf8; }

/* ── Paginação ── */
.pag-btn { display:inline-flex; align-items:center; justify-content:center; min-width:2.25rem; height:2.25rem; border-radius:0.6rem; font-size:0.75rem; font-weight:700; padding:0 0.5rem; transition:all 0.15s; border:1.5px solid transparent; cursor:pointer; }
.pag-btn:not(.active):not([disabled]) { color:#64748b; }
.pag-btn:not(.active):not([disabled]):hover { background:#f0f9ff; border-color:#bae6fd; color:#0369a1; }
.pag-btn.active { background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff; border-color:transparent; box-shadow:0 2px 8px rgba(14,165,233,0.3); }
.pag-btn[disabled] { opacity:0.35; cursor:not-allowed; }
.dark .pag-btn:not(.active):not([disabled]) { color:#94a3b8; }
.dark .pag-btn:not(.active):not([disabled]):hover { background:#1e293b; border-color:#334155; color:#38bdf8; }

/* ── Item no painel ── */
.sel-item-row { display:flex; align-items:center; gap:0.6rem; padding:0.5rem 0.65rem; border-radius:0.75rem; background:#fff; border:1px solid #e0f2fe; }
.dark .sel-item-row { background:#1e293b; border-color:#334155; }

/* ── Float mobile ── */
@media(max-width:1023px){
    #floatSubmit { position:fixed; bottom:5.5rem; right:1rem; z-index:50; }
}
</style>
@endpush

<div x-data="{
    search: '',
    catFilter: 'all',
    page: 1,
    perPage: 20,
    products: {{ Js::from($products->map(fn($p) => [
        'id'      => $p->id,
        'name'    => $p->name,
        'nameLow' => strtolower($p->name),
        'cat'     => $p->category?->name ?? '',
        'catIcon' => $p->category?->icone ?? 'fas fa-box',
        'price'   => $p->price_sale,
        'priceF'  => $p->price_sale ? 'R$ '.number_format($p->price_sale,2,',','.') : '',
        'stock'   => $p->stock_quantity ?? 0,
        'img'     => $p->image_url ?? ($p->image ? asset('storage/products/'.$p->image) : null),
    ])) }},
    extraItems: [],
    selected: {},
    get filtered() {
        return this.products.filter(p => {
            const m = !this.search || p.nameLow.includes(this.search.toLowerCase());
            const c = this.catFilter === 'all' || p.cat === this.catFilter;
            return m && c;
        });
    },
    get pages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
    get paged()  { const s=(this.page-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
    get selectedList()  { return Object.values(this.selected); },
    get selectedCount() { return this.selectedList.length; },
    get totalRef()      { return this.selectedList.reduce((a,i)=>a+(parseFloat(i.price||0)*(parseInt(i.qty)||1)),0); },
    toggleProduct(p) {
        if (this.selected[p.id]) { delete this.selected[p.id]; }
        else { this.selected[p.id] = { ...p, qty: 1, notes: '' }; }
        this.selected = { ...this.selected };
    },
    isSelected(id) { return !!this.selected[id]; },
    setPage(n)     { this.page = Math.max(1, Math.min(n, this.pages)); },
    addExtra()     { this.extraItems.push({ desc:'', qty:1 }); },
    removeExtra(i) { this.extraItems.splice(i,1); },
    submitForm() {
        const form = document.getElementById('quoteForm');
        const hid  = document.getElementById('hiddenInputs');
        hid.innerHTML = '';
        let idx = 0;
        for (const [id, item] of Object.entries(this.selected)) {
            const qEl = form.querySelector('[data-qty-id=\''+id+'\']');
            const nEl = form.querySelector('[data-notes-id=\''+id+'\']');
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][product_id]\' value=\''+id+'\'>';
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][quantity]\' value=\''+(qEl?qEl.value:item.qty||1)+'\'>';
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][notes]\' value=\''+(nEl?nEl.value:item.notes||'')+'\'>';
            idx++;
        }
        this.extraItems.forEach((e,i)=>{ if(e.desc){ hid.innerHTML+='<input type=\'hidden\' name=\'extra_items['+i+'][desc]\' value=\''+e.desc+'\'><input type=\'hidden\' name=\'extra_items['+i+'][qty]\' value=\''+e.qty+'\'>'; }});
        form.submit();
    }
}" x-cloak>

    {{-- Hero banner --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-indigo-700 to-violet-800 rounded-2xl p-5 mb-5 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-tr from-black/10 to-transparent"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-8 -bottom-8 w-28 h-28 bg-white/5 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.quotes') }}"
                   class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 border border-white/25 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-[.2em] mb-1">
                        <i class="fas fa-file-circle-plus text-[8px]"></i> Novo Orçamento
                    </div>
                    <h1 class="text-lg font-black leading-tight">Solicitar Orçamento</h1>
                    <p class="text-sky-200/80 text-xs mt-0.5">Selecione produtos e envie sua solicitação</p>
                </div>
            </div>
            <div x-show="selectedCount > 0"
                 class="flex items-center gap-2 bg-white/15 border border-white/25 backdrop-blur-sm rounded-xl px-4 py-2.5">
                <i class="fas fa-basket-shopping text-xs text-sky-200"></i>
                <div>
                    <p class="text-[9px] text-sky-200">Selecionados</p>
                    <p class="font-black text-sm leading-none" x-text="selectedCount + ' produto' + (selectedCount>1?'s':'')"></p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.quotes.store') }}" id="quoteForm">
        @csrf
        <div id="hiddenInputs"></div>

        <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-5 items-start">

            {{-- ── Coluna principal ── --}}
            <div class="lg:col-span-2 xl:col-span-3 space-y-5">

                {{-- Card Selecionar Produtos --}}
                <div class="portal-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-sky-50/80 to-indigo-50/80 dark:from-sky-900/20 dark:to-indigo-900/20">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-boxes-stacked text-white text-xs"></i>
                                </div>
                                <div>
                                    <h2 class="font-black text-sm text-gray-900 dark:text-slate-200">Selecione os Produtos</h2>
                                    <p class="text-[10px] text-gray-400 dark:text-slate-500">Clique nos cards para adicionar ao orçamento</p>
                                </div>
                            </div>
                            <div x-show="selectedCount > 0"
                                 class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 dark:bg-sky-900/40 rounded-full">
                                <i class="fas fa-circle-check text-sky-600 dark:text-sky-400 text-xs"></i>
                                <span class="text-xs font-black text-sky-700 dark:text-sky-300"
                                      x-text="selectedCount + ' selecionado' + (selectedCount>1?'s':'')"></span>
                            </div>
                        </div>

                        {{-- Search + filtro categoria --}}
                        <div class="mt-4 flex flex-col sm:flex-row gap-2.5">
                            <div class="relative flex-1">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <input x-model="search" @input="page=1" type="text"
                                       placeholder="Buscar produto por nome..."
                                       class="portal-input pl-9 pr-3 py-2.5 text-xs w-full">
                            </div>
                            <select x-model="catFilter" @change="page=1"
                                    class="portal-input py-2.5 text-xs sm:min-w-[10rem]">
                                <option value="all">Todas as categorias</option>
                                @foreach($products->pluck('category')->filter()->unique('id') as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Nenhum resultado --}}
                        <div x-show="filtered.length === 0" class="py-16 text-center">
                            <i class="fas fa-box-open text-4xl text-gray-200 dark:text-slate-600 mb-3"></i>
                            <p class="text-sm font-bold text-gray-400 dark:text-slate-500">Nenhum produto encontrado</p>
                            <button type="button" @click="search=''; catFilter='all'; page=1"
                                    class="mt-3 text-xs text-sky-500 hover:underline">Limpar filtros</button>
                        </div>

                        {{-- Grid de produtos --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5"
                             x-show="filtered.length > 0">
                            <template x-for="p in paged" :key="p.id">
                                <div class="portal-product-card cursor-pointer select-none"
                                     :class="{ 'selected': isSelected(p.id) }"
                                     @click="toggleProduct(p)">

                                    {{-- Checkmark de seleção --}}
                                    <div class="sel-badge absolute top-2.5 right-2.5 z-20 w-6 h-6 bg-sky-500 rounded-full items-center justify-center shadow-lg">
                                        <i class="fas fa-check text-white text-[9px]"></i>
                                    </div>

                                    {{-- Imagem --}}
                                    <div class="portal-product-img-area">
                                        <template x-if="p.img">
                                            <img :src="p.img" :alt="p.name" loading="lazy">
                                        </template>
                                        <template x-if="!p.img">
                                            <div class="pimg-placeholder">
                                                <i class="fas fa-box text-4xl text-sky-200 dark:text-slate-600"></i>
                                            </div>
                                        </template>
                                        <span class="badge-stock shadow-sm"
                                              :class="p.stock > 5 ? 'bg-emerald-500/90 text-white' : 'bg-amber-400/90 text-white'">
                                            <i class="fas fa-layer-group" style="font-size:0.55rem"></i>
                                            <span x-text="p.stock"></span>
                                        </span>
                                    </div>

                                    {{-- Ícone circular de categoria --}}
                                    <div class="pcard-cat-circle">
                                        <i :class="p.catIcon"></i>
                                    </div>

                                    {{-- Corpo --}}
                                    <div class="pcard-body">
                                        <p class="text-[9px] font-black uppercase tracking-[.15em] text-sky-600 dark:text-sky-400 truncate w-full"
                                           x-text="p.cat" x-show="p.cat"></p>
                                        <p class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 w-full"
                                           x-text="p.name"></p>
                                        <p class="text-sm font-black text-sky-600 dark:text-sky-400"
                                           x-text="p.priceF" x-show="p.priceF"></p>

                                        {{-- Qtd + obs (só quando selecionado) --}}
                                        <div class="product-details flex-col gap-1.5 w-full mt-1" @click.stop>
                                            <div>
                                                <label class="text-[9px] font-bold text-gray-500 dark:text-slate-400">Quantidade</label>
                                                <input type="number"
                                                       :data-qty-id="p.id"
                                                       :min="1" :max="p.stock" value="1"
                                                       @input="if(selected[p.id]) selected[p.id].qty = $event.target.value"
                                                       class="portal-input py-1.5 px-2 text-xs mt-0.5 text-center font-bold w-full">
                                            </div>
                                            <div>
                                                <label class="text-[9px] font-bold text-gray-500 dark:text-slate-400">Observação</label>
                                                <input type="text"
                                                       :data-notes-id="p.id"
                                                       placeholder="cor, tamanho..."
                                                       @input="if(selected[p.id]) selected[p.id].notes = $event.target.value"
                                                       class="portal-input py-1.5 px-2 text-xs mt-0.5 w-full">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Paginação moderna --}}
                        <div x-show="pages > 1"
                             class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100 dark:border-slate-700">
                            <p class="text-[10px] text-gray-400 dark:text-slate-500">
                                <span class="font-bold text-gray-700 dark:text-slate-300"
                                      x-text="Math.min((page-1)*perPage+1, filtered.length)"></span>–<span
                                      class="font-bold text-gray-700 dark:text-slate-300"
                                      x-text="Math.min(page*perPage, filtered.length)"></span>
                                de <span class="font-bold" x-text="filtered.length"></span> produtos
                            </p>
                            <div class="flex items-center gap-1">
                                <button type="button" class="pag-btn" :disabled="page===1" @click="setPage(page-1)">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </button>
                                <template x-for="n in Array.from({length:pages},(v,k)=>k+1)" :key="n">
                                    <button type="button" class="pag-btn"
                                            :class="{'active': n===page}"
                                            x-show="pages<=7 || Math.abs(n-page)<=2 || n===1 || n===pages"
                                            @click="setPage(n)" x-text="n"></button>
                                </template>
                                <button type="button" class="pag-btn" :disabled="page===pages" @click="setPage(page+1)">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Itens Adicionais ── --}}
                <div class="portal-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-violet-50/80 to-purple-50/80 dark:from-violet-900/20 dark:to-purple-900/20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-list-plus text-white text-xs"></i>
                                </div>
                                <div>
                                    <h2 class="font-black text-sm text-gray-900 dark:text-slate-200">
                                        Itens Adicionais
                                        <span class="text-[10px] font-normal text-gray-400 dark:text-slate-500 ml-0.5">(opcional)</span>
                                    </h2>
                                    <p class="text-[10px] text-gray-400 dark:text-slate-500">Frete, instalação ou itens fora do catálogo</p>
                                </div>
                            </div>
                            <button type="button" @click="addExtra()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                <i class="fas fa-plus text-[9px]"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div x-show="extraItems.length === 0" class="text-center py-8">
                            <i class="fas fa-layer-group text-3xl text-gray-200 dark:text-slate-700 mb-2"></i>
                            <p class="text-xs text-gray-400 dark:text-slate-500">Adicione serviços, frete ou outros itens personalizados</p>
                        </div>
                        <div class="space-y-2.5">
                            <template x-for="(item, idx) in extraItems" :key="idx">
                                <div class="flex gap-2 items-center bg-violet-50/60 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30 rounded-xl p-3">
                                    <div class="w-7 h-7 bg-violet-100 dark:bg-violet-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tag text-violet-500 text-[10px]"></i>
                                    </div>
                                    <input type="text" x-model="item.desc"
                                           placeholder="Descrição (ex: frete expresso, instalação...)"
                                           class="portal-input flex-1 py-2 text-xs">
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <label class="text-[9px] font-bold text-gray-400 dark:text-slate-500 whitespace-nowrap">Qtd</label>
                                        <input type="number" x-model="item.qty" min="1"
                                               class="portal-input w-14 py-2 text-xs text-center font-bold">
                                    </div>
                                    <button type="button" @click="removeExtra(idx)"
                                            class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-400 hover:text-red-600 rounded-lg transition-colors border border-red-100 dark:border-red-800/30">
                                        <i class="fas fa-trash-alt text-[9px]"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>{{-- /coluna principal --}}

            {{-- ── Sidebar: Resumo ── --}}
            <div class="lg:col-span-1">
                <div id="summaryPanel" class="portal-card overflow-hidden sticky top-4"
                     :class="{'has-items': selectedCount > 0}">
                    <div class="px-4 py-3.5 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                                <i class="fas fa-receipt text-white text-xs"></i>
                            </div>
                            <div>
                                <h2 class="font-black text-sm text-gray-900 dark:text-slate-200">Resumo do Pedido</h2>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500"
                                   x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'') + ' selecionado' + (selectedCount!==1?'s':'')"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Vazio --}}
                    <div x-show="selectedCount === 0" class="px-4 py-10 text-center">
                        <div class="w-12 h-12 bg-sky-50 dark:bg-sky-900/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-basket-shopping text-sky-300 dark:text-sky-700 text-xl"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-400 dark:text-slate-500">Nenhum produto ainda</p>
                        <p class="text-[10px] text-gray-300 dark:text-slate-600 mt-1">Clique nos cards ao lado</p>
                    </div>

                    {{-- Lista de selecionados --}}
                    <div x-show="selectedCount > 0" class="px-3 py-3 space-y-2 max-h-72 overflow-y-auto">
                        <template x-for="item in selectedList" :key="item.id">
                            <div class="sel-item-row">
                                <template x-if="item.img">
                                    <img :src="item.img" :alt="item.name" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                                </template>
                                <template x-if="!item.img">
                                    <div class="w-9 h-9 bg-sky-50 dark:bg-sky-900/30 rounded-lg flex items-center justify-center flex-shrink-0 border border-sky-100 dark:border-sky-800/20">
                                        <i class="fas fa-box text-sky-300 text-xs"></i>
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-gray-900 dark:text-slate-200 truncate" x-text="item.name"></p>
                                    <p class="text-[9px] text-sky-600 dark:text-sky-400 font-bold" x-text="item.priceF" x-show="item.priceF"></p>
                                </div>
                                <span class="text-[9px] font-black text-white bg-sky-500 rounded-lg px-1.5 py-0.5 flex-shrink-0"
                                      x-text="(item.qty||1) + '×'"></span>
                                <button type="button" @click="toggleProduct(item)"
                                        class="w-5 h-5 flex items-center justify-center text-red-400 hover:text-red-600 transition-colors flex-shrink-0 rounded ml-0.5">
                                    <i class="fas fa-times text-[9px]"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Extras no resumo --}}
                    <template x-if="extraItems.filter(e=>e.desc).length > 0">
                        <div class="px-3 pb-2 border-t border-gray-100 dark:border-slate-700 pt-2 space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-wider text-violet-500 dark:text-violet-400 mb-1">Itens adicionais</p>
                            <template x-for="(e,i) in extraItems.filter(x=>x.desc)" :key="i">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-tag text-violet-400 text-[9px] flex-shrink-0"></i>
                                    <p class="text-[10px] text-gray-600 dark:text-slate-400 flex-1 truncate" x-text="e.desc"></p>
                                    <span class="text-[9px] text-gray-400 flex-shrink-0" x-text="e.qty + '×'"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Total referência --}}
                    <div x-show="selectedCount > 0 && totalRef > 0"
                         class="mx-3 mb-3 mt-1 p-3 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20 rounded-xl border border-sky-100 dark:border-sky-800/30">
                        <p class="text-[9px] text-gray-500 dark:text-slate-400 font-semibold">Valor de referência</p>
                        <p class="text-base font-black text-sky-700 dark:text-sky-300"
                           x-text="'R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})"></p>
                        <p class="text-[9px] text-gray-400 dark:text-slate-500 mt-0.5">*Sujeito a negociação</p>
                    </div>

                    {{-- Botão Enviar --}}
                    <div class="px-3 pb-4 pt-1 space-y-2">
                        <button type="button" @click="submitForm()"
                                :disabled="selectedCount === 0 && extraItems.filter(e=>e.desc).length === 0"
                                class="w-full flex items-center justify-center gap-2 py-3.5 font-black text-sm rounded-xl transition-all"
                                :class="selectedCount > 0 || extraItems.filter(e=>e.desc).length > 0
                                    ? 'bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white hover:scale-[1.02] shadow-lg shadow-sky-500/20'
                                    : 'opacity-40 cursor-not-allowed bg-gray-200 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
                            <i class="fas fa-paper-plane text-xs"></i>
                            <span x-text="selectedCount > 0 ? 'Enviar Orçamento (' + selectedCount + ')' : 'Enviar Orçamento'"></span>
                        </button>
                        <a href="{{ route('portal.quotes') }}"
                           class="w-full flex items-center justify-center gap-2 py-2.5 text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700/50 rounded-xl transition-all">
                            <i class="fas fa-arrow-left text-[9px]"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>{{-- /sidebar --}}

        </div>{{-- /grid --}}
    </form>

    {{-- Botão flutuante (mobile) --}}
    <div id="floatSubmit" x-show="selectedCount > 0" x-cloak>
        <button type="button" @click="submitForm()"
                class="inline-flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-black text-sm rounded-2xl shadow-2xl shadow-sky-500/40 hover:scale-105 transition-all">
            <i class="fas fa-paper-plane text-xs"></i>
            <span x-text="'Enviar (' + selectedCount + ')'"></span>
        </button>
    </div>

</div>

@push('scripts')
<script>
// Pré-selecionar produto via query param ?product_id=X
document.addEventListener('DOMContentLoaded', () => {
    const preId = new URLSearchParams(window.location.search).get('product_id');
    if (!preId) return;
    const waitForAlpine = () => {
        const el = document.querySelector('[x-data]');
        if (el && el._x_dataStack && el._x_dataStack[0]) {
            const comp = el._x_dataStack[0];
            const p = comp.products.find(x => String(x.id) === String(preId));
            if (p) comp.toggleProduct(p);
        } else {
            setTimeout(waitForAlpine, 150);
        }
    };
    waitForAlpine();
});
</script>
@endpush

</x-portal-layout>
