{{-- Carrinho do cliente logado: confere os itens e envia o pedido, no visual do catálogo. --}}
<x-portal-catalog-layout title="Carrinho" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
    <style>
        .pc-item { display: flex; gap: 12px; padding: 14px; border-top: 1px solid var(--ml-line); }
        .pc-item:first-of-type { border-top: 0; }
        .pc-item .po-thumb { width: 76px; height: 76px; }
        .pc-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
        .pc-name { font-size: 14px; color: #333; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .pc-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .pc-qty { display: inline-flex; align-items: center; border: 1px solid var(--ml-line); border-radius: 6px; height: 34px; overflow: hidden; }
        .pc-qty button { width: 34px; height: 100%; border: 0; background: #fff; color: var(--ml-blue); font-size: 18px; font-weight: 700; cursor: pointer; }
        .pc-qty button:disabled { color: #ccc; cursor: default; }
        .pc-qty span { min-width: 34px; text-align: center; font-weight: 700; font-size: 14px; }
        .pc-remove { border: 0; background: none; padding: 0; color: var(--ml-blue-dark); font-size: 13px; font-weight: 700; cursor: pointer; }
        .pc-price { text-align: right; }
        .pc-price s { display: block; font-size: 12px; color: #999; }
        .pc-price strong { font-size: 17px; color: #222; }
        .pc-unit { font-size: 12px; color: var(--ml-muted); }
        .pc-last { font-size: 12px; color: var(--ml-orange); font-weight: 600; }
        .pc-pay { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .pc-pay button { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 10px 4px; border: 1px solid var(--ml-line); border-radius: 8px; background: #fff; font-size: 12px; font-weight: 700; color: #555; cursor: pointer; }
        .pc-pay button i { font-size: 16px; color: var(--ml-blue); }
        .pc-pay button.on { border-color: var(--ml-blue); background: var(--ml-blue-soft); color: var(--ml-blue-dark); box-shadow: 0 0 0 1px var(--ml-blue); }
        .pc-addr { font-size: 13px; color: #444; line-height: 1.45; }
        .pc-addr strong { color: #222; }
        .pc-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 50; background: #fff; box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); display: flex; align-items: center; gap: 12px; }
        .pc-bar .po-btn { flex: 1; height: 46px; font-size: 15px; }
        @media (min-width: 900px) { .pc-bar { display: none; } }
        .pc-toast { position: fixed; left: 50%; bottom: 88px; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; z-index: 70; box-shadow: 0 4px 14px rgba(0,0,0,.25); }
    </style>
@endpush

@php
    $orders = app(\App\Services\Portal\PortalOrderService::class);
    $catalogUrl = route('portal.catalog', ['userId' => $client->user_id]);
    // Dados atuais de cada produto à venda (preço com promoção, foto, estoque com teto de 10).
    $fresh = $products->mapWithKeys(fn ($p) => [$p->id => \App\Http\Controllers\ClientPortalController::cardInfo($p)]);
    $stockMap = $fresh->map(fn ($d) => $d['stock']);
    $address = $client->formattedPortalAddress();
@endphp

<div class="po-page" x-data="portalCart(@js($fresh), @js($stockMap))">
    <div class="po-head">
        <div>
            <h1>Carrinho</h1>
            <p x-text="$store.cart.count ? ($store.cart.count + ($store.cart.count === 1 ? ' produto' : ' produtos') + ' · ' + units + ' un.') : 'Nenhum produto ainda'"></p>
        </div>
        <a href="{{ $catalogUrl }}" class="po-btn po-btn-soft" x-show="$store.cart.count" x-cloak><i class="fas fa-plus"></i> Adicionar</a>
    </div>

    @include('portal.partials.flash')

    <template x-if="removed > 0">
        <div class="po-flash err"><i class="fas fa-circle-info" style="margin-top:2px"></i><span x-text="removed === 1 ? 'Um produto saiu do carrinho porque acabou o estoque.' : removed + ' produtos saíram do carrinho porque acabou o estoque.'"></span></div>
    </template>

    <div class="po-card po-empty" x-show="!$store.cart.count" x-cloak>
        <i class="fas fa-cart-shopping"></i>
        <h2>Seu carrinho está vazio</h2>
        <p>Escolha os produtos no catálogo e eles aparecem aqui.</p>
        <a href="{{ $catalogUrl }}" class="po-btn po-btn-primary">Ver produtos</a>
    </div>

    <div class="po-grid" x-show="$store.cart.count" x-cloak>
        <div>
            <section class="po-card">
                <div class="pa-sec-head"><h2>Produtos</h2></div>
                <template x-for="item in $store.cart.items" :key="item.id">
                    <div class="pc-item" data-testid="cart-item">
                        <div class="po-thumb">
                            <template x-if="item.img"><img :src="item.img" :alt="item.name" loading="lazy"></template>
                            <template x-if="!item.img"><i class="fas fa-image"></i></template>
                        </div>
                        <div class="pc-main">
                            <div class="pc-name" x-text="item.name"></div>
                            <div class="pc-unit" x-text="money(item.price) + ' cada'"></div>
                            <div class="pc-last" x-show="item.stock && item.stock <= 3">Últimas unidades</div>
                            <div class="pc-row">
                                <div class="pc-qty">
                                    <button type="button" @click="$store.cart.setQty(item.id, item.qty - 1)" :disabled="item.qty <= 1" aria-label="Diminuir">−</button>
                                    <span x-text="item.qty"></span>
                                    <button type="button" @click="$store.cart.setQty(item.id, item.qty + 1)" :disabled="item.stock > 0 && item.qty >= item.stock" aria-label="Aumentar">+</button>
                                </div>
                                <div class="pc-price">
                                    <s x-show="fresh[item.id] && fresh[item.id].old" x-text="fresh[item.id] ? money((fresh[item.id].old || 0) * item.qty) : ''"></s>
                                    <strong x-text="money(item.price * item.qty)"></strong>
                                </div>
                            </div>
                            <div><button type="button" class="pc-remove" @click="$store.cart.remove(item.id)">Excluir</button></div>
                        </div>
                    </div>
                </template>
                <a href="{{ $catalogUrl }}" class="po-card-foot">
                    <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-store" style="color:var(--ml-blue)"></i> Continuar comprando</span>
                    <i class="fas fa-chevron-right" style="color:#bbb"></i>
                </a>
            </section>

            <section class="po-card">
                <div class="pa-sec-head">
                    <h2>Seus dados</h2>
                    <a href="{{ route('portal.profile') }}">Alterar</a>
                </div>
                <div class="po-card-body pc-addr">
                    <strong>{{ $client->name }}</strong><br>
                    @if($client->phone){{ $client->phone }}@else<span class="tone-amber">Sem telefone cadastrado</span>@endif
                    @if($client->cpf_cnpj) · CPF/CNPJ {{ $client->cpf_cnpj }}@endif<br>
                    @if($address)
                        <i class="fas fa-location-dot" style="color:var(--ml-blue)"></i> {{ $address }}
                    @else
                        <span class="tone-amber">Sem endereço cadastrado.</span> <a href="{{ route('portal.profile') }}" style="color:var(--ml-blue-dark);font-weight:700">Cadastrar endereço</a>
                    @endif
                </div>
            </section>
        </div>

        <div class="po-side">
            <section class="po-card">
                <div class="pa-sec-head"><h2>Resumo do pedido</h2></div>
                <div class="po-card-body">
                    <table class="po-sum">
                        <tr><td x-text="'Produtos (' + units + ')'"></td><td x-text="money(totalOld)"></td></tr>
                        <tr x-show="totalOld - $store.cart.total > 0.009"><td>Descontos</td><td style="color:var(--ml-green)" x-text="'− ' + money(totalOld - $store.cart.total)"></td></tr>
                        <tr class="po-sum-total"><td>Total</td><td x-text="money($store.cart.total)"></td></tr>
                    </table>
                    <p class="po-hint" style="margin-top:8px">A loja confirma os valores e combina a entrega com você.</p>
                </div>
                <div class="po-card-body" style="border-top:1px solid var(--ml-line)">
                    <p class="po-sec-title" style="font-size:14px;margin-bottom:10px">Como prefere pagar?</p>
                    <div class="pc-pay">
                        <template x-for="opt in payOptions" :key="opt.val">
                            <button type="button" :class="{ on: pay === opt.val }" @click="pay = pay === opt.val ? '' : opt.val" :aria-pressed="pay === opt.val">
                                <i :class="opt.icon"></i><span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>
                    <div class="pf-field" style="margin-top:14px">
                        <label class="pf-label" for="cartNotes">Observação <small>(opcional)</small></label>
                        <textarea id="cartNotes" x-model="notes" rows="2" maxlength="1000" placeholder="Ex.: entregar depois das 18h" class="pf-input"></textarea>
                    </div>
                    <button type="button" class="po-btn po-btn-primary po-btn-block" style="height:48px;font-size:15px;margin-top:14px" @click="send()" :disabled="sending" data-testid="cart-send">
                        <i class="fas fa-paper-plane"></i> <span x-text="sending ? 'Enviando…' : 'Enviar pedido'"></span>
                    </button>
                </div>
            </section>
        </div>
    </div>

    <div class="pc-bar" x-show="$store.cart.count" x-cloak>
        <div>
            <div class="po-total-lbl">Total</div>
            <div class="po-total" x-text="money($store.cart.total)"></div>
        </div>
        <button type="button" class="po-btn po-btn-primary" @click="send()" :disabled="sending"><i class="fas fa-paper-plane"></i> Enviar pedido</button>
    </div>

    <form method="POST" action="{{ route('portal.quotes.store') }}" x-ref="form" style="display:none">
        @csrf
        <template x-for="(item, i) in $store.cart.items" :key="item.id">
            <span>
                <input type="hidden" :name="'items[' + i + '][product_id]'" :value="item.id">
                <input type="hidden" :name="'items[' + i + '][quantity]'" :value="item.qty">
            </span>
        </template>
        <input type="hidden" name="payment_preference" :value="pay">
        <input type="hidden" name="client_notes" :value="notes">
    </form>

    <div class="pc-toast" x-show="toast" x-transition.opacity x-text="toast" x-cloak></div>
</div>

@push('scripts')
<script>
function portalCart(fresh, stockMap) {
    return {
        fresh,
        removed: 0,
        pay: '',
        notes: '',
        sending: false,
        toast: '',
        payOptions: [
            { val: 'pix', icon: 'fas fa-qrcode', label: 'Pix' },
            { val: 'dinheiro', icon: 'fas fa-money-bill-wave', label: 'Dinheiro' },
            { val: 'credito', icon: 'fas fa-credit-card', label: 'Crédito' },
            { val: 'debito', icon: 'far fa-credit-card', label: 'Débito' },
            { val: 'boleto', icon: 'fas fa-barcode', label: 'Boleto' },
            { val: 'outro', icon: 'fas fa-ellipsis', label: 'Outro' },
        ],
        init() {
            const cart = Alpine.store('cart');
            // Só o que ainda está à venda, com preço, nome e foto atuais.
            this.removed = cart.sync(stockMap);
            cart.items.forEach(i => {
                const f = fresh[i.id];
                if (f) { i.price = f.price; i.name = f.name; i.img = f.img; i.stock = f.stock; }
            });
            cart.save();
            // Link direto: ?product_id=ID coloca o produto no carrinho.
            const pre = Number(new URLSearchParams(location.search).get('product_id'));
            if (pre && fresh[pre] && !cart.has(pre)) {
                cart.add(fresh[pre]);
                this.flash('Adicionado: ' + fresh[pre].name);
            }
        },
        get units() { return Alpine.store('cart').items.reduce((s, i) => s + (i.qty || 1), 0); },
        get totalOld() { return Alpine.store('cart').items.reduce((s, i) => s + ((this.fresh[i.id]?.old || i.price) * (i.qty || 1)), 0); },
        money(v) { return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
        flash(msg) { this.toast = msg; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2200); },
        send() {
            if (!Alpine.store('cart').count || this.sending) return;
            this.sending = true;
            // O envio monta os campos na hora da chamada; depois o carrinho pode ser limpo.
            this.$refs.form.submit();
            Alpine.store('cart').clear();
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
