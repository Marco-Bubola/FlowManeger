<x-portal-layout title="Editar Orçamento #{{ $quote->id }}">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-quotes-create.css') }}">
@endpush

<div class="portal-quote-create-page" x-data="{
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
    submitForm() {
        const form = document.getElementById('quoteEditForm');
        const hid  = document.getElementById('hiddenInputsEdit');
        hid.innerHTML = '';
        let idx = 0;
        for (const [id, item] of Object.entries(this.selected)) {
            const qEl = form.querySelector('[data-qty-id=\''+id+'\']');
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][product_id]\' value=\''+id+'\'>';
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][quantity]\' value=\''+(qEl?qEl.value:item.qty||1)+'\'>';
            hid.innerHTML += '<input type=\'hidden\' name=\'items['+idx+'][notes]\' value=\''+(item.notes||'')+'\'>';
            idx++;
        }
        form.submit();
    },
    init() {
        const existing = {{ Js::from($quote->items ?? []) }};
        existing.forEach(item => {
            const p = this.products.find(x => x.id === item.product_id);
            if (p) {
                this.selected[p.id] = { ...p, qty: item.quantity, notes: item.notes || '' };
            } else {
                this.selected[item.product_id] = {
                    id: item.product_id,
                    name: item.name,
                    nameLow: (item.name || '').toLowerCase(),
                    qty: item.quantity,
                    notes: item.notes || '',
                    price: item.price_ref || 0,
                    priceF: item.price_ref ? 'R$ ' + parseFloat(item.price_ref).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}) : '',
                    img: null, cat: '', catIcon: 'fas fa-box', stock: 999,
                };
            }
        });
        this.selected = { ...this.selected };
    }
}" x-cloak>

    {{-- Hero banner --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-600 to-amber-700 rounded-2xl p-5 mb-5 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-tr from-black/10 to-transparent"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-8 -bottom-8 w-28 h-28 bg-white/5 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.quotes.show', $quote) }}"
                   class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 border border-white/25 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-[.2em] mb-1">
                        <i class="fas fa-pen-to-square text-[8px]"></i> Editar Orçamento
                    </div>
                    <h1 class="text-lg font-black leading-tight">Orçamento #{{ $quote->id }}</h1>
                    <p class="text-amber-100/80 text-xs mt-0.5">Adicione ou remova produtos. Alterações só são permitidas antes da confirmação.</p>
                </div>
            </div>
            <div x-show="selectedCount > 0"
                 class="flex items-center gap-2 bg-white/15 border border-white/25 backdrop-blur-sm rounded-xl px-4 py-2.5">
                <i class="fas fa-basket-shopping text-xs text-amber-200"></i>
                <div>
                    <p class="text-[9px] text-amber-200">Selecionados</p>
                    <p class="font-black text-sm leading-none" x-text="selectedCount + ' produto' + (selectedCount>1?'s':'')"></p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.quotes.update', $quote) }}" id="quoteEditForm">
        @csrf @method('PUT')
        <div id="hiddenInputsEdit"></div>

        <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-5 items-start">

            {{-- ── Coluna principal ── --}}
            <div class="lg:col-span-2 xl:col-span-3 space-y-5">

                {{-- Card Selecionar Produtos --}}
                <div class="portal-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-amber-50/80 to-orange-50/80 dark:from-amber-900/20 dark:to-orange-900/20">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-boxes-stacked text-white text-xs"></i>
                                </div>
                                <div>
                                    <h2 class="font-black text-sm text-gray-900 dark:text-slate-200">Selecione os Produtos</h2>
                                    <p class="text-[10px] text-gray-400 dark:text-slate-500">Clique nos cards para adicionar ou remover do orçamento</p>
                                </div>
                            </div>
                            <div x-show="selectedCount > 0"
                                 class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 dark:bg-amber-900/40 rounded-full">
                                <i class="fas fa-circle-check text-amber-600 dark:text-amber-400 text-xs"></i>
                                <span class="text-xs font-black text-amber-700 dark:text-amber-300"
                                      x-text="selectedCount + ' selecionado' + (selectedCount>1?'s':'')"></span>
                            </div>
                        </div>

                        {{-- Barra de busca e filtro --}}
                        <div class="mt-5 space-y-2.5">
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-amber-400 dark:text-amber-500 text-sm pointer-events-none"></i>
                                <input x-model="search" @input="page=1" type="text"
                                       placeholder="Buscar produto por nome, categoria..."
                                       class="w-full pl-11 pr-4 py-3.5 text-sm font-semibold bg-white dark:bg-slate-800 border-2 border-amber-100 dark:border-amber-800/50 rounded-2xl focus:border-amber-400 dark:focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:focus:ring-amber-900/30 text-gray-900 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-500 transition-all shadow-sm">
                                <span x-show="search" @click="search=''; page=1"
                                      class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                    <i class="fas fa-xmark"></i>
                                </span>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <select x-model="catFilter" @change="page=1"
                                        class="portal-input py-2 text-xs flex-1 min-w-[9rem]">
                                    <option value="all">Todas as categorias</option>
                                    @foreach($products->pluck('category')->filter()->unique('id') as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/40 rounded-xl text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                    <i class="fas fa-boxes-stacked text-[10px]"></i>
                                    <span x-text="filtered.length + ' produto' + (filtered.length !== 1 ? 's' : '')"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Nenhum resultado --}}
                        <div x-show="filtered.length === 0" class="py-16 text-center">
                            <i class="fas fa-box-open text-4xl text-gray-200 dark:text-slate-600 mb-3"></i>
                            <p class="text-sm font-bold text-gray-400 dark:text-slate-500">Nenhum produto encontrado</p>
                            <button type="button" @click="search=''; catFilter='all'; page=1"
                                    class="mt-3 text-xs text-amber-500 hover:underline">Limpar filtros</button>
                        </div>

                        {{-- Grid de produtos --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5"
                             x-show="filtered.length > 0">
                            <template x-for="p in paged" :key="p.id">
                                <div class="portal-product-card cursor-pointer select-none"
                                     :class="{ 'selected': isSelected(p.id) }"
                                     @click="toggleProduct(p)">

                                    {{-- Checkmark de seleção --}}
                                    <div class="sel-badge absolute top-2.5 right-2.5 z-20 w-6 h-6 bg-amber-500 rounded-full items-center justify-center shadow-lg">
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
                                        <div class="pcard-cat-circle">
                                            <i :class="p.catIcon"></i>
                                        </div>
                                    </div>

                                    {{-- Corpo --}}
                                    <div class="pcard-body">
                                        <p class="text-[9px] font-black uppercase tracking-[.15em] text-amber-600 dark:text-amber-400 truncate w-full"
                                           x-text="p.cat" x-show="p.cat"></p>
                                        <p class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 w-full"
                                           x-text="p.name"></p>
                                        <p class="text-sm font-black text-amber-600 dark:text-amber-400"
                                           x-text="p.priceF" x-show="p.priceF"></p>

                                        {{-- Qtd --}}
                                        <div class="product-details flex-col gap-1 w-full mt-1" @click.stop>
                                            <label class="text-[9px] font-bold text-gray-500 dark:text-slate-400">Quantidade</label>
                                            <input type="number"
                                                   :data-qty-id="p.id"
                                                   :min="1" :max="p.stock"
                                                   :value="selected[p.id] ? selected[p.id].qty : 1"
                                                   @input="if(selected[p.id]) selected[p.id].qty = $event.target.value"
                                                   class="portal-input py-1.5 px-2 text-xs mt-0.5 text-center font-bold w-full">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Paginação --}}
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

                {{-- Itens extras + observações --}}
                <div class="portal-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700">
                        <h2 class="font-black text-sm text-gray-900 dark:text-slate-200 flex items-center gap-2">
                            <i class="fas fa-list-check text-violet-500 text-xs"></i> Itens Adicionais e Observações
                        </h2>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">Inclua itens fora do catálogo ou deixe uma observação para o vendedor</p>
                    </div>
                    <div class="p-5 space-y-4" x-data="{
                        extras: {{ Js::from(collect($quote->extra_items ?? [])->map(fn($e) => ['desc' => $e['description'] ?? '', 'qty' => $e['quantity'] ?? 1])->values()) }},
                        addExtra() { this.extras.push({desc:'',qty:1}); },
                        removeExtra(i) { this.extras.splice(i,1); }
                    }">
                        <template x-for="(ei, i) in extras" :key="i">
                            <div class="flex items-center gap-2">
                                <input :name="'extra_items['+i+'][desc]'" x-model="ei.desc" type="text"
                                       placeholder="Descrição do item..."
                                       class="portal-input py-2.5 text-xs flex-1">
                                <input :name="'extra_items['+i+'][qty]'" x-model="ei.qty" type="number" min="1"
                                       class="portal-input py-2.5 text-xs w-20 text-center">
                                <button type="button" @click="removeExtra(i)"
                                        class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors flex-shrink-0">
                                    <i class="fas fa-xmark text-xs"></i>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addExtra()"
                                class="inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-violet-200 dark:border-violet-700/50 hover:border-violet-400 dark:hover:border-violet-500 text-violet-500 dark:text-violet-400 text-xs font-bold rounded-xl transition-all hover:bg-violet-50 dark:hover:bg-violet-900/20">
                            <i class="fas fa-plus text-[10px]"></i> Adicionar item extra
                        </button>
                        <div class="pt-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 mb-1.5">Observações gerais</label>
                            <textarea name="client_notes" rows="3" maxlength="1000" placeholder="Deixe uma observação para o vendedor..."
                                      class="portal-input py-2.5 text-xs resize-none">{{ old('client_notes', $quote->client_notes) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>{{-- /coluna principal --}}

            {{-- ── Sidebar: Resumo ── --}}
            <div class="lg:col-span-1">
                <div id="summaryPanel" class="portal-card overflow-hidden sticky top-4"
                     :class="{'has-items': selectedCount > 0}">
                    <div class="px-4 py-3.5 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-sm">
                                <i class="fas fa-receipt text-white text-xs"></i>
                            </div>
                            <div>
                                <h2 class="font-black text-sm text-gray-900 dark:text-slate-200">Resumo</h2>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500"
                                   x-text="selectedCount + ' produto' + (selectedCount!==1?'s':'') + ' selecionado' + (selectedCount!==1?'s':'')"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Vazio --}}
                    <div x-show="selectedCount === 0" class="px-4 py-10 text-center">
                        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-basket-shopping text-amber-300 dark:text-amber-700 text-xl"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-400 dark:text-slate-500">Nenhum produto selecionado</p>
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
                                    <div class="w-9 h-9 bg-amber-50 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0 border border-amber-100 dark:border-amber-800/20">
                                        <i class="fas fa-box text-amber-300 text-xs"></i>
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-gray-900 dark:text-slate-200 truncate" x-text="item.name"></p>
                                    <p class="text-[9px] text-amber-600 dark:text-amber-400 font-bold" x-text="item.priceF" x-show="item.priceF"></p>
                                </div>
                                <span class="text-[9px] font-black text-white bg-amber-500 rounded-lg px-1.5 py-0.5 flex-shrink-0"
                                      x-text="(item.qty||1) + '×'"></span>
                                <button type="button" @click="toggleProduct(item)"
                                        class="w-5 h-5 flex items-center justify-center text-red-400 hover:text-red-600 transition-colors flex-shrink-0 rounded ml-0.5">
                                    <i class="fas fa-times text-[9px]"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Total referência --}}
                    <div x-show="selectedCount > 0 && totalRef > 0"
                         class="mx-3 mb-3 mt-1 p-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-100 dark:border-amber-800/30">
                        <p class="text-[9px] text-gray-500 dark:text-slate-400 font-semibold">Valor de referência</p>
                        <p class="text-base font-black text-amber-700 dark:text-amber-300"
                           x-text="'R$ ' + totalRef.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})"></p>
                        <p class="text-[9px] text-gray-400 dark:text-slate-500 mt-0.5">*Sujeito a negociação</p>
                    </div>

                    {{-- Botões --}}
                    <div class="px-3 pb-4 pt-1 space-y-2">
                        <button type="button" @click="submitForm()"
                                :disabled="selectedCount === 0"
                                class="w-full flex items-center justify-center gap-2 py-3.5 font-black text-sm rounded-xl transition-all"
                                :class="selectedCount > 0
                                    ? 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white hover:scale-[1.02] shadow-lg shadow-amber-500/20'
                                    : 'opacity-40 cursor-not-allowed bg-gray-200 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
                            <i class="fas fa-floppy-disk text-xs"></i>
                            <span x-text="selectedCount > 0 ? 'Salvar alterações (' + selectedCount + ')' : 'Salvar alterações'"></span>
                        </button>
                        <a href="{{ route('portal.quotes.show', $quote) }}"
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
                class="inline-flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black text-sm rounded-2xl shadow-2xl shadow-amber-500/40 hover:scale-105 transition-all">
            <i class="fas fa-floppy-disk text-xs"></i>
            <span x-text="'Salvar (' + selectedCount + ')'"></span>
        </button>
    </div>

</div>

@push('styles')
<style>
/* Reuse portal product card selected state */
.portal-product-card.selected {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.2), 0 14px 36px rgba(245,158,11,0.20);
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}
.dark .portal-product-card.selected {
    border-color: #d97706;
    box-shadow: 0 0 0 3px rgba(217,119,6,0.25), 0 14px 36px rgba(217,119,6,0.18);
}
.portal-product-card.selected .sel-badge { display: flex; }
.portal-product-card:not(.selected) .sel-badge { display: none; }
.portal-product-card.selected .product-details { display: flex; }
.portal-product-card:not(.selected) .product-details { display: none; }
.has-items { box-shadow: 0 0 0 2px rgba(245,158,11,0.3), 0 20px 60px rgba(245,158,11,0.15); }
.sel-item-row { display:flex; align-items:center; gap:0.5rem; padding:0.4rem 0.3rem; border-radius:0.75rem; background:transparent; transition:background 0.1s; }
.sel-item-row:hover { background: rgb(255,251,235,0.7); }
.dark .sel-item-row:hover { background: rgba(120,88,10,0.2); }
.pag-btn { width:1.75rem; height:1.75rem; display:flex; align-items:center; justify-content:center; border-radius:0.5rem; font-size:0.7rem; font-weight:700; color:#6b7280; background:transparent; border:1px solid #e5e7eb; transition:all 0.15s; }
.pag-btn:hover:not(:disabled) { background:#fef3c7; color:#d97706; border-color:#fcd34d; }
.pag-btn.active { background:#f59e0b; color:#fff; border-color:#f59e0b; }
.pag-btn:disabled { opacity:0.4; cursor:not-allowed; }
.dark .pag-btn { color:#94a3b8; border-color:#334155; }
.dark .pag-btn:hover:not(:disabled) { background:rgba(120,88,10,0.3); color:#fbbf24; border-color:#78350f; }
#floatSubmit { position:fixed; bottom:5rem; left:50%; transform:translateX(-50%); z-index:40; }
@media (min-width:1024px) { #floatSubmit { display:none!important; } }
</style>
@endpush

</x-portal-layout>
