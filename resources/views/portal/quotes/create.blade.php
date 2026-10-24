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
        .pc-opts { display: grid; gap: 8px; }
        .pc-opt { display: flex; gap: 10px; align-items: flex-start; padding: 12px; border: 1px solid var(--ml-line); border-radius: 8px; cursor: pointer; background: #fff; }
        .pc-opt input { margin: 3px 0 0; accent-color: var(--ml-blue); width: 16px; height: 16px; flex-shrink: 0; }
        .pc-opt strong { display: block; font-size: 14px; color: #222; }
        .pc-opt small { display: block; font-size: 12px; color: var(--ml-muted); margin-top: 2px; }
        .pc-opt.on { border-color: var(--ml-blue); box-shadow: 0 0 0 1px var(--ml-blue); background: #faf8ff; }
        .pc-coupon-row { display: flex; gap: 8px; }
        .pc-coupon-row .pf-input { text-transform: uppercase; }
        .pc-coupon-row .po-btn { flex-shrink: 0; height: 44px; padding: 0 16px; }
        .pc-coupon-on { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px dashed var(--ml-green); border-radius: 8px; background: #effaf3; }
        .pc-coupon-on > i { color: var(--ml-green); font-size: 18px; }
        .pc-coupon-on span { flex: 1; min-width: 0; }
        .pc-coupon-on b { display: block; font-size: 14px; color: #14532d; letter-spacing: .03em; }
        .pc-coupon-on small { display: block; font-size: 12px; color: #3f6b4f; }
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
    // Campo de cupom só quando a loja tem algum cupom ligado.
    $hasCoupons = \App\Models\PortalCoupon::where('user_id', $client->user_id)->where('active', true)->exists();
    // Pagamento online (Mercado Pago) e entrega, quando a loja ligou em Configurações.
    $online = $paySettings && $paySettings->enabled;
    $payMethods = $paySettings?->methods() ?? [];
    $pixReady = $payMethods !== [];
    $allowManual = ! $pixReady || $paySettings->allow_manual;
    $canPickup = ! $online || $paySettings->pickup_enabled;
    $canDeliver = $online && $paySettings->delivery_enabled;
    $checkoutCfg = [
        'pix'        => $pixReady,
        'methods'    => $payMethods,
        'manual'     => $allowManual,
        'pickup'     => $online && $canPickup,
        'deliver'    => $canDeliver,
        'fee'        => $canDeliver ? (float) $paySettings->delivery_fee : 0,
        'hasCpf'     => strlen(preg_replace('/\D+/', '', (string) $client->cpf_cnpj)) >= 11,
        'hasAddress' => $client->hasRequiredPortalProfileData(),
        'couponUrl'  => route('portal.coupon.check'),
        'oldCoupon'  => old('coupon_code', ''),
        'hasErrors'  => $errors->any(),
    ];
@endphp

<div class="po-page" x-data="portalCart(@js($fresh), @js($stockMap), @js($checkoutCfg))">
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
                    <a href="{{ route('portal.profile', ['volta' => 'carrinho']) }}">Alterar</a>
                </div>
                <div class="po-card-body pc-addr">
                    <strong>{{ $client->name }}</strong><br>
                    @if($client->phone){{ $client->phone }}@else<span class="tone-amber">Sem telefone cadastrado</span>@endif
                    @if($client->cpf_cnpj) · CPF/CNPJ {{ $client->cpf_cnpj }}@endif<br>
                    @if($address)
                        <i class="fas fa-location-dot" style="color:var(--ml-blue)"></i> {{ $address }}
                    @else
                        <span class="tone-amber">Sem endereço cadastrado.</span> <a href="{{ route('portal.profile', ['volta' => 'carrinho']) }}" style="color:var(--ml-blue-dark);font-weight:700">Cadastrar endereço</a>
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
                        <tr x-show="discount > 0" x-cloak><td x-text="'Cupom ' + coupon?.code"></td><td style="color:var(--ml-green)" x-text="'− ' + money(discount)" data-testid="coupon-discount"></td></tr>
                        <tr x-show="delivery === 'entrega'"><td>Entrega</td><td x-text="cfg.fee > 0 ? money(cfg.fee) : 'Grátis'"></td></tr>
                        <tr class="po-sum-total"><td>Total</td><td x-text="money(grand)"></td></tr>
                    </table>
                    <p class="po-hint" style="margin-top:8px" x-show="!online">A loja confirma os valores e combina a entrega com você.</p>
                </div>
                @if($hasCoupons)
                <div class="po-card-body pc-coupon" style="border-top:1px solid var(--ml-line)" data-testid="coupon-box">
                    <template x-if="!coupon">
                        <form class="pc-coupon-row" @submit.prevent="applyCoupon()">
                            <input type="text" x-model="couponInput" class="pf-input" placeholder="Cupom de desconto" maxlength="40" autocomplete="off" autocapitalize="characters" aria-label="Cupom de desconto" data-testid="coupon-input">
                            <button type="submit" class="po-btn po-btn-soft" :disabled="couponBusy || !couponInput.trim()" data-testid="coupon-apply" x-text="couponBusy ? '...' : 'Aplicar'"></button>
                        </form>
                    </template>
                    <template x-if="coupon">
                        <div class="pc-coupon-on">
                            <i class="fas fa-ticket"></i>
                            <span><b x-text="coupon.code"></b><small x-text="discount > 0 ? couponLabel : couponMinMsg"></small></span>
                            <button type="button" class="pc-remove" @click="removeCoupon()">Remover</button>
                        </div>
                    </template>
                    <p class="pf-err" x-show="couponErr" x-text="couponErr" style="margin-top:6px" data-testid="coupon-error"></p>
                </div>
                @endif

                @if($online && ($canPickup || $canDeliver))
                <div class="po-card-body" style="border-top:1px solid var(--ml-line)">
                    <p class="po-sec-title" style="font-size:14px;margin-bottom:10px">Entrega</p>
                    <div class="pc-opts">
                        @if($canPickup)
                        <label class="pc-opt" :class="{ on: delivery === 'retirada' }">
                            <input type="radio" value="retirada" x-model="delivery">
                            <span><strong>Retirar com a loja</strong><small>Grátis. A loja combina o horário.</small></span>
                        </label>
                        @endif
                        @if($canDeliver)
                        <label class="pc-opt" :class="{ on: delivery === 'entrega' }">
                            <input type="radio" value="entrega" x-model="delivery">
                            <span><strong>Receber em casa</strong><small>{{ (float) $paySettings->delivery_fee > 0 ? 'Taxa de R$ ' . number_format((float) $paySettings->delivery_fee, 2, ',', '.') : 'Entrega grátis' }}@if($paySettings->delivery_note) · {{ $paySettings->delivery_note }}@endif</small></span>
                        </label>
                        @endif
                    </div>
                    <p class="pf-err" x-show="delivery === 'entrega' && !cfg.hasAddress" style="margin-top:8px">Complete seu endereço em <a href="{{ route('portal.profile', ['volta' => 'carrinho']) }}" style="text-decoration:underline">Meus dados</a> para receber em casa.</p>
                </div>
                @endif

                <div class="po-card-body" style="border-top:1px solid var(--ml-line)">
                    @if($pixReady)
                    <p class="po-sec-title" style="font-size:14px;margin-bottom:10px">Pagamento</p>
                    <div class="pc-opts">
                        @if(in_array('pix', $payMethods, true))
                        <label class="pc-opt" :class="{ on: checkout === 'pix' }">
                            <input type="radio" value="pix" x-model="checkout">
                            <span><strong><i class="fas fa-qrcode" style="color:#32bcad"></i> Pix</strong><small>Aprovação na hora.</small></span>
                        </label>
                        @endif
                        @if(in_array('cartao', $payMethods, true))
                        <label class="pc-opt" :class="{ on: checkout === 'cartao' }">
                            <input type="radio" value="cartao" x-model="checkout">
                            <span><strong><i class="fas fa-credit-card" style="color:#3483fa"></i> Cartão de crédito ou débito</strong><small>{{ (int) $paySettings->max_installments > 1 ? 'Em até ' . (int) $paySettings->max_installments . 'x no crédito.' : 'À vista.' }} Aprovação na hora.</small></span>
                        </label>
                        @endif
                        @if(in_array('boleto', $payMethods, true))
                        <label class="pc-opt" :class="{ on: checkout === 'boleto' }">
                            <input type="radio" value="boleto" x-model="checkout">
                            <span><strong><i class="fas fa-barcode" style="color:#555"></i> Boleto</strong><small>Vence em {{ (int) $paySettings->boleto_days }} {{ (int) $paySettings->boleto_days === 1 ? 'dia' : 'dias' }}. Confirmação em até 2 dias úteis após pagar.</small></span>
                        </label>
                        @endif
                        @if($allowManual)
                        <label class="pc-opt" :class="{ on: checkout === 'manual' }">
                            <input type="radio" value="manual" x-model="checkout">
                            <span><strong>Combinar com a loja</strong><small>Você paga quando a loja confirmar o pedido.</small></span>
                        </label>
                        @endif
                    </div>
                    <p class="pf-err" x-show="online && !cfg.hasCpf" style="margin-top:8px">Para pagar pelo site, cadastre seu CPF em <a href="{{ route('portal.profile', ['volta' => 'carrinho']) }}" style="text-decoration:underline">Meus dados</a>.</p>
                    <p class="pf-err" x-show="checkout === 'boleto' && cfg.hasCpf && !cfg.hasAddress" style="margin-top:8px">Para o boleto, complete seu endereço em <a href="{{ route('portal.profile', ['volta' => 'carrinho']) }}" style="text-decoration:underline">Meus dados</a>.</p>
                    @endif
                    <div x-show="!online" @if($pixReady) style="margin-top:14px" @endif>
                    <p class="po-sec-title" style="font-size:14px;margin-bottom:10px">Como prefere pagar?</p>
                    <div class="pc-pay">
                        <template x-for="opt in payOptions" :key="opt.val">
                            <button type="button" :class="{ on: pay === opt.val }" @click="pay = pay === opt.val ? '' : opt.val" :aria-pressed="pay === opt.val">
                                <i :class="opt.icon"></i><span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>
                    </div>
                    <div class="pf-field" style="margin-top:14px">
                        <label class="pf-label" for="cartNotes">Observação <small>(opcional)</small></label>
                        <textarea id="cartNotes" x-model="notes" rows="2" maxlength="1000" placeholder="Ex.: entregar depois das 18h" class="pf-input"></textarea>
                    </div>
                    <button type="button" class="po-btn po-btn-primary po-btn-block" style="height:48px;font-size:15px;margin-top:14px" @click="send()" :disabled="sending || blocked" data-testid="cart-send">
                        <i class="fas" :class="online ? 'fa-lock' : 'fa-paper-plane'"></i> <span x-text="sending ? 'Enviando…' : sendLabel"></span>
                    </button>
                </div>
            </section>
        </div>
    </div>

    <div class="pc-bar" x-show="$store.cart.count" x-cloak>
        <div>
            <div class="po-total-lbl">Total</div>
            <div class="po-total" x-text="money(grand)"></div>
        </div>
        <button type="button" class="po-btn po-btn-primary" @click="send()" :disabled="sending || blocked"><i class="fas" :class="online ? 'fa-lock' : 'fa-paper-plane'"></i> <span x-text="sendLabel"></span></button>
    </div>

    <form method="POST" action="{{ route('portal.quotes.store') }}" x-ref="form" style="display:none">
        @csrf
        <template x-for="(item, i) in $store.cart.items" :key="item.id">
            <span>
                <input type="hidden" :name="'items[' + i + '][product_id]'" :value="item.id">
                <input type="hidden" :name="'items[' + i + '][quantity]'" :value="item.qty">
            </span>
        </template>
        <input type="hidden" name="payment_preference" :value="online ? '' : pay">
        <input type="hidden" name="checkout" :value="checkout">
        <input type="hidden" name="delivery_method" :value="delivery">
        <input type="hidden" name="client_notes" :value="notes">
        <input type="hidden" name="coupon_code" :value="discount > 0 ? coupon.code : ''">
    </form>

    <div class="pc-toast" x-show="toast" x-transition.opacity x-text="toast" x-cloak></div>
</div>

@push('scripts')
<script>
function portalCart(fresh, stockMap, cfg) {
    return {
        fresh,
        cfg,
        checkout: cfg.methods.length ? cfg.methods[0] : 'manual',
        delivery: cfg.pickup ? 'retirada' : (cfg.deliver ? 'entrega' : ''),
        removed: 0,
        pay: '',
        notes: '',
        sending: false,
        toast: '',
        coupon: null,
        couponInput: cfg.oldCoupon || '',
        couponBusy: false,
        couponErr: '',
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
            // Voltou com erro do envio: o carrinho tinha sido limpo, então volta o que estava nele.
            if (cfg.hasErrors && !cart.count) {
                try {
                    const backup = JSON.parse(sessionStorage.getItem('portal_cart_backup') || '[]');
                    if (backup.length) { cart.items = backup; cart.sync(stockMap); }
                } catch (e) {}
            }
            if (this.couponInput) this.$nextTick(() => this.applyCoupon());
            // Link direto: ?product_id=ID coloca o produto no carrinho.
            const pre = Number(new URLSearchParams(location.search).get('product_id'));
            if (pre && fresh[pre] && !cart.has(pre)) {
                cart.add(fresh[pre]);
                this.flash('Adicionado: ' + fresh[pre].name);
            }
        },
        get discount() {
            const c = this.coupon, sub = Alpine.store('cart').total;
            if (!c || (c.min && sub + 0.009 < c.min)) return 0;
            const d = c.kind === 'fixed' ? c.value : sub * c.value / 100;
            return Math.round(Math.max(0, Math.min(d, sub)) * 100) / 100;
        },
        get couponLabel() {
            if (!this.coupon) return '';
            return (this.coupon.kind === 'fixed' ? this.money(this.coupon.value) : String(this.coupon.value).replace('.', ',') + '%') + ' de desconto · você economiza ' + this.money(this.discount);
        },
        get couponMinMsg() { return this.coupon?.min ? 'Vale a partir de ' + this.money(this.coupon.min) + ' em produtos' : ''; },
        get grand() { return Alpine.store('cart').total - this.discount + (this.delivery === 'entrega' ? (Number(this.cfg.fee) || 0) : 0); },
        async applyCoupon() {
            const code = this.couponInput.trim();
            if (!code || this.couponBusy) return;
            this.couponBusy = true;
            this.couponErr = '';
            try {
                const r = await fetch(this.cfg.couponUrl, { method: 'POST', credentials: 'same-origin', headers: {
                    'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                }, body: JSON.stringify({ code, subtotal: Alpine.store('cart').total }) });
                const d = await r.json();
                if (r.status === 429) { this.couponErr = 'Muitas tentativas. Espere um minuto e tente de novo.'; return; }
                if (!d.ok) { this.couponErr = d.message || 'Cupom inválido.'; return; }
                this.coupon = { code: d.code, kind: d.kind, value: Number(d.value), min: d.min ? Number(d.min) : null };
                this.flash('Cupom aplicado');
            } catch (e) {
                this.couponErr = 'Não deu para conferir o cupom agora. Tente de novo.';
            } finally {
                this.couponBusy = false;
            }
        },
        removeCoupon() { this.coupon = null; this.couponInput = ''; this.couponErr = ''; },
        get online() { return this.checkout !== 'manual'; },
        get blocked() { return (this.online && !this.cfg.hasCpf) || ((this.checkout === 'boleto' || this.delivery === 'entrega') && !this.cfg.hasAddress); },
        get sendLabel() { return this.online ? 'Ir para o pagamento' : 'Enviar pedido'; },
        get units() { return Alpine.store('cart').items.reduce((s, i) => s + (i.qty || 1), 0); },
        get totalOld() { return Alpine.store('cart').items.reduce((s, i) => s + ((this.fresh[i.id]?.old || i.price) * (i.qty || 1)), 0); },
        money(v) { return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
        flash(msg) { this.toast = msg; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2200); },
        send() {
            if (!Alpine.store('cart').count || this.sending || this.blocked) return;
            this.sending = true;
            // O envio monta os campos na hora da chamada; depois o carrinho pode ser limpo.
            this.$refs.form.submit();
            try { sessionStorage.setItem('portal_cart_backup', JSON.stringify(Alpine.store('cart').items)); } catch (e) {}
            Alpine.store('cart').clear();
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
