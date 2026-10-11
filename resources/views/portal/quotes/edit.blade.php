{{-- Alterar pedido: o cliente muda quantidades, tira ou inclui produtos antes da loja responder. --}}
<x-portal-catalog-layout title="Alterar pedido #{{ $quote->id }}" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
    <style>
        .pc-item { display: flex; gap: 12px; padding: 14px; border-top: 1px solid var(--ml-line); }
        .pc-item:first-of-type { border-top: 0; }
        .pc-item .po-thumb { width: 64px; height: 64px; }
        .pc-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
        .pc-name { font-size: 14px; color: #333; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .pc-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .pc-qty { display: inline-flex; align-items: center; border: 1px solid var(--ml-line); border-radius: 6px; height: 34px; overflow: hidden; }
        .pc-qty button { width: 34px; height: 100%; border: 0; background: #fff; color: var(--ml-blue); font-size: 18px; font-weight: 700; cursor: pointer; }
        .pc-qty button:disabled { color: #ccc; cursor: default; }
        .pc-qty span { min-width: 34px; text-align: center; font-weight: 700; font-size: 14px; }
        .pc-remove { border: 0; background: none; padding: 0; color: var(--ml-blue-dark); font-size: 13px; font-weight: 700; cursor: pointer; }
        .pc-note { height: 36px; font-size: 13px; }
        .pe-search { position: relative; }
        .pe-search .pf-input { padding-left: 38px; }
        .pe-search i { position: absolute; left: 13px; top: 14px; color: #999; }
        .pe-hit { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 0; border: 0; border-top: 1px solid var(--ml-line); background: none; text-align: left; cursor: pointer; }
        .pe-hit:first-child { border-top: 0; }
        .pe-hit .po-thumb { width: 44px; height: 44px; font-size: 15px; }
        .pe-hit-n { flex: 1; min-width: 0; font-size: 13.5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pe-hit-p { font-size: 13px; font-weight: 700; color: #222; }
        .pe-add { width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: var(--ml-blue-soft); color: var(--ml-blue-dark); display: flex; align-items: center; justify-content: center; }
        .pe-extra { display: grid; grid-template-columns: minmax(0, 1fr) 76px 36px; gap: 8px; align-items: center; margin-bottom: 8px; }
        .pe-x { width: 36px; height: 36px; border: 0; border-radius: 8px; background: #fdecee; color: var(--ml-red); cursor: pointer; }
        .pc-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 50; background: #fff; box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); display: flex; align-items: center; gap: 12px; }
        .pc-bar .po-btn { flex: 1; height: 46px; font-size: 15px; }
        @media (min-width: 900px) { .pc-bar { display: none; } }
    </style>
@endpush

@php
    $fresh = $products->mapWithKeys(fn ($p) => [$p->id => \App\Http\Controllers\ClientPortalController::cardInfo($p)]);
    // Itens do pedido; os que não estão mais à venda continuam com o nome e o preço de referência.
    $start = collect($quote->items ?? [])->map(fn ($i) => array_merge(
        ['id' => (int) $i['product_id'], 'name' => $i['name'] ?? 'Produto', 'price' => (float) ($i['price_ref'] ?? 0), 'img' => null, 'stock' => 0],
        isset($fresh[$i['product_id']]) ? \Illuminate\Support\Arr::only($fresh[$i['product_id']], ['name', 'price', 'img', 'stock']) : [],
        ['qty' => (int) ($i['quantity'] ?? 1), 'notes' => (string) ($i['notes'] ?? '')]
    ))->values();
    $extras = collect($quote->extra_items ?? [])->map(fn ($e) => ['desc' => $e['description'] ?? '', 'qty' => (int) ($e['quantity'] ?? 1)])->values();
@endphp

<div class="po-page" x-data="quoteEdit(@js($fresh), @js($start), @js($extras), @js((string) $quote->client_notes))">
    <a href="{{ route('portal.quotes.show', $quote) }}" class="po-back"><i class="fas fa-chevron-left"></i> Pedido #{{ $quote->id }}</a>
    <div class="po-head">
        <div>
            <h1>Alterar pedido</h1>
            <p>Mude as quantidades, tire ou inclua produtos. A loja vê o pedido atualizado.</p>
        </div>
    </div>

    @include('portal.partials.flash')

    <form method="POST" action="{{ route('portal.quotes.update', $quote) }}" x-ref="form" @submit.prevent="save()">
        @csrf
        @method('PUT')

        <div class="po-grid">
            <div>
                <section class="po-card">
                    <div class="pa-sec-head"><h2>Produtos do pedido</h2><span class="po-hint" style="margin:0" x-text="items.length + (items.length === 1 ? ' produto' : ' produtos')"></span></div>
                    <template x-for="(item, i) in items" :key="item.id">
                        <div class="pc-item">
                            <div class="po-thumb">
                                <template x-if="item.img"><img :src="item.img" :alt="item.name" loading="lazy"></template>
                                <template x-if="!item.img"><i class="fas fa-image"></i></template>
                            </div>
                            <div class="pc-main">
                                <div class="pc-name" x-text="item.name"></div>
                                <div class="pc-row">
                                    <div class="pc-qty">
                                        <button type="button" @click="item.qty = Math.max(1, item.qty - 1)" :disabled="item.qty <= 1" aria-label="Diminuir">−</button>
                                        <span x-text="item.qty"></span>
                                        <button type="button" @click="item.qty = item.stock > 0 ? Math.min(item.stock, item.qty + 1) : item.qty + 1" :disabled="item.stock > 0 && item.qty >= item.stock" aria-label="Aumentar">+</button>
                                    </div>
                                    <strong style="font-size:16px;color:#222" x-text="money(item.price * item.qty)"></strong>
                                </div>
                                <input type="text" class="pf-input pc-note" x-model="item.notes" maxlength="255" placeholder="Observação deste produto (opcional)">
                                <input type="hidden" :name="'items[' + i + '][product_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + i + '][quantity]'" :value="item.qty">
                                <input type="hidden" :name="'items[' + i + '][notes]'" :value="item.notes">
                                <div><button type="button" class="pc-remove" @click="items.splice(i, 1)">Excluir</button></div>
                            </div>
                        </div>
                    </template>
                    <div class="po-card-body" x-show="!items.length" style="color:var(--ml-muted)">Nenhum produto. Inclua abaixo ou descreva um item.</div>
                </section>

                <section class="po-card">
                    <div class="pa-sec-head"><h2>Incluir produto</h2></div>
                    <div class="po-card-body">
                        <div class="pe-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" class="pf-input" x-model="q" placeholder="Buscar produto da loja" aria-label="Buscar produto">
                        </div>
                        <div style="margin-top:6px">
                            <template x-for="p in hits" :key="p.id">
                                <button type="button" class="pe-hit" @click="add(p)">
                                    <span class="po-thumb">
                                        <template x-if="p.img"><img :src="p.img" alt="" loading="lazy"></template>
                                        <template x-if="!p.img"><i class="fas fa-image"></i></template>
                                    </span>
                                    <span class="pe-hit-n" x-text="p.name"></span>
                                    <span class="pe-hit-p" x-text="money(p.price)"></span>
                                    <span class="pe-add"><i class="fas" :class="has(p.id) ? 'fa-check' : 'fa-plus'"></i></span>
                                </button>
                            </template>
                            <p class="po-hint" x-show="q.trim().length >= 2 && !hits.length">Nenhum produto encontrado.</p>
                        </div>
                    </div>
                </section>

                <section class="po-card">
                    <div class="pa-sec-head"><h2>Algo que não está no catálogo?</h2></div>
                    <div class="po-card-body">
                        <template x-for="(ei, i) in extras" :key="i">
                            <div class="pe-extra">
                                <input type="text" class="pf-input" :name="'extra_items[' + i + '][desc]'" x-model="ei.desc" maxlength="255" placeholder="Descreva o item" required>
                                <input type="number" class="pf-input" :name="'extra_items[' + i + '][qty]'" x-model.number="ei.qty" min="1" required aria-label="Quantidade">
                                <button type="button" class="pe-x" @click="extras.splice(i, 1)" aria-label="Tirar item"><i class="fas fa-xmark"></i></button>
                            </div>
                        </template>
                        <button type="button" class="po-btn po-btn-soft" @click="extras.push({ desc: '', qty: 1 })"><i class="fas fa-plus"></i> Descrever um item</button>
                    </div>
                </section>
            </div>

            <div class="po-side">
                <section class="po-card">
                    <div class="pa-sec-head"><h2>Resumo</h2></div>
                    <div class="po-card-body">
                        <table class="po-sum">
                            <tr><td x-text="'Produtos (' + units + ')'"></td><td x-text="money(total)"></td></tr>
                            <tr x-show="extras.length"><td>Itens descritos</td><td x-text="extras.length"></td></tr>
                            <tr class="po-sum-total"><td>Total estimado</td><td x-text="money(total)"></td></tr>
                        </table>
                        <div class="pf-field" style="margin-top:14px">
                            <label class="pf-label" for="editNotes">Observação para a loja <small>(opcional)</small></label>
                            <textarea id="editNotes" name="client_notes" x-model="notes" rows="3" maxlength="1000" class="pf-input" placeholder="Ex.: entregar depois das 18h"></textarea>
                        </div>
                        <button type="submit" class="po-btn po-btn-primary po-btn-block" style="height:48px;font-size:15px;margin-top:14px" :disabled="!items.length && !extras.length"><i class="fas fa-check"></i> Salvar pedido</button>
                        <a href="{{ route('portal.quotes.show', $quote) }}" class="po-btn po-btn-ghost po-btn-block" style="margin-top:8px">Cancelar</a>
                    </div>
                </section>
            </div>
        </div>

        <div class="pc-bar">
            <div>
                <div class="po-total-lbl">Total estimado</div>
                <div class="po-total" x-text="money(total)"></div>
            </div>
            <button type="submit" class="po-btn po-btn-primary" :disabled="!items.length && !extras.length"><i class="fas fa-check"></i> Salvar</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function quoteEdit(fresh, start, extras, notes) {
    const all = Object.values(fresh);
    return {
        items: start,
        extras,
        notes,
        q: '',
        get hits() {
            const t = this.q.trim().toLowerCase();
            if (t.length < 2) return [];
            return all.filter(p => p.name.toLowerCase().includes(t) || (p.code || '').toLowerCase().includes(t)).slice(0, 8);
        },
        get units() { return this.items.reduce((s, i) => s + (i.qty || 1), 0); },
        get total() { return this.items.reduce((s, i) => s + (Number(i.price) || 0) * (i.qty || 1), 0); },
        has(id) { return this.items.some(i => i.id === id); },
        add(p) {
            const found = this.items.find(i => i.id === p.id);
            if (found) { found.qty = p.stock > 0 ? Math.min(p.stock, found.qty + 1) : found.qty + 1; return; }
            this.items.push({ id: p.id, name: p.name, price: p.price, img: p.img, stock: p.stock, qty: 1, notes: '' });
        },
        money(v) { return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
        save() {
            if (!this.items.length && !this.extras.length) return;
            this.$refs.form.submit();
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
