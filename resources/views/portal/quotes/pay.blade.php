{{-- Pagar o pedido pelo site (Mercado Pago): Pix, cartão ou boleto, no visual do catálogo. --}}
<x-portal-catalog-layout title="Pagar pedido #{{ $quote->id }}" :store="$store" :owner-id="$quote->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
    <style>
        .px-amount { font-size: 30px; font-weight: 800; color: #222; letter-spacing: -.01em; }
        .px-qr { width: 230px; height: 230px; margin: 14px auto 6px; padding: 10px; border: 1px solid var(--ml-line); border-radius: 12px; background: #fff; }
        .px-qr img { width: 100%; height: 100%; image-rendering: pixelated; }
        .px-code { display: flex; gap: 8px; margin-top: 12px; }
        .px-code input { flex: 1; min-width: 0; height: 44px; border: 1px solid #d6d6d6; border-radius: 6px; padding: 0 10px; font: inherit; font-size: 13px; color: #555; background: #fafafa; }
        .px-steps { margin: 0; padding-left: 20px; color: #444; font-size: 14px; line-height: 1.6; }
        .px-timer { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; background: #fff8e6; color: #7a4b00; font-size: 13px; font-weight: 700; }
        .px-wait { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 14px; color: var(--ml-muted); font-size: 13px; }
        .px-spin { width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--ml-blue-soft); border-top-color: var(--ml-blue); animation: pxspin .8s linear infinite; }
        @keyframes pxspin { to { transform: rotate(360deg); } }
        .px-test { margin-top: 10px; font-size: 12px; color: #b4232f; font-weight: 700; }
        .px-center { text-align: center; }
        .px-tabs { display: grid; grid-auto-flow: column; grid-auto-columns: 1fr; gap: 6px; padding: 6px; margin-bottom: 12px; background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
        .px-tabs a { display: flex; align-items: center; justify-content: center; gap: 7px; height: 42px; border-radius: 7px; font-size: 14px; font-weight: 700; color: #555; text-decoration: none; }
        .px-tabs a.on { background: var(--ml-blue-soft); color: var(--ml-blue-dark); }
        .px-card-box { min-height: 320px; text-align: left; }
        .px-alert { margin-top: 12px; padding: 10px 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: left; }
        .px-alert.bad { background: #fdecee; color: #b4232f; }
        .px-alert.wait { background: #fff8e6; color: #7a4b00; }
        .px-bar { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .02em; }
        .px-lock { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 12px; font-size: 12px; color: var(--ml-muted); }
        @media (max-width: 380px) { .px-tabs a span { display: none; } }
    </style>
@endpush

@php
    $cfgPay = [
        'status'   => route('portal.quotes.pay.status', $quote),
        'card'     => route('portal.quotes.pay.card', $quote),
        'expires'  => $method === 'pix' ? $payment?->expires_at?->toIso8601String() : null,
        'method'   => $method,
        'poll'     => $payment && $payment->isPending() ? ($method === 'boleto' ? 30000 : 5000) : 0,
        'amount'   => $amount,
        'pk'       => $method === 'cartao' ? $settings?->public_key : null,
        'maxInst'  => (int) ($settings->max_installments ?? 1),
        'email'    => $client->email,
        'cpf'      => preg_replace('/\D+/', '', (string) $client->cpf_cnpj),
        'csrf'     => csrf_token(),
    ];
    $tabs = ['pix' => ['fa-qrcode', 'Pix'], 'cartao' => ['fa-credit-card', 'Cartão'], 'boleto' => ['fa-barcode', 'Boleto']];
    $titles = ['pix' => 'Pagar com Pix', 'cartao' => 'Pagar com cartão', 'boleto' => 'Pagar com boleto'];
@endphp

<div class="po-page" x-data="portalPay(@js($cfgPay))">
    <a href="{{ route('portal.quotes.show', $quote) }}" class="po-back"><i class="fas fa-chevron-left"></i> Pedido #{{ $quote->id }}</a>
    <div class="po-head">
        <div>
            <h1>{{ $titles[$method] ?? 'Pagar pedido' }}</h1>
            <p>Pedido #{{ $quote->id }} · {{ $store?->name ?? 'Loja' }}</p>
        </div>
    </div>

    @include('portal.partials.flash')

    @if(count($methods) > 1 && ! ($error && ! $method))
        <nav class="px-tabs" aria-label="Forma de pagamento" data-testid="pay-tabs">
            @foreach($methods as $m)
                <a href="{{ route('portal.quotes.pay', [$quote, 'metodo' => $m]) }}" class="{{ $m === $method ? 'on' : '' }}" @if($m === $method) aria-current="page" @endif>
                    <i class="fas {{ $tabs[$m][0] }}"></i><span>{{ $tabs[$m][1] }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if($error)
        <div class="po-card po-empty">
            <i class="fas fa-circle-exclamation" style="color:var(--ml-orange)"></i>
            <h2>Não deu para pagar agora</h2>
            <p>{{ $error }}</p>
            <div class="po-actions" style="max-width:340px;margin:0 auto">
                <a href="{{ route('portal.quotes.pay', [$quote, 'metodo' => $method]) }}" class="po-btn po-btn-primary">Tentar de novo</a>
                @if(! $settings || $settings->allow_manual || ! $settings->onlineReady())
                <form method="POST" action="{{ route('portal.quotes.manual', $quote) }}">@csrf
                    <button type="submit" class="po-btn po-btn-soft po-btn-block">Combinar o pagamento com a loja</button>
                </form>
                @endif
            </div>
        </div>
    @else
    <div class="po-grid">
        <div>
            <section class="po-card">
                <div class="po-card-body px-center">
                    <div class="po-total-lbl">Valor {{ $method === 'pix' ? 'do Pix' : ($method === 'boleto' ? 'do boleto' : 'a pagar') }}</div>
                    <div class="px-amount">{{ $orders->money($payment ? (float) $payment->amount : $amount) }}</div>
                    @if($pixOff > 0)
                        @if($method === 'pix')
                            <p class="po-hint" style="color:var(--ml-green);font-weight:600" data-testid="pay-pix-off">Com {{ $orders->money($pixOff) }} de desconto por pagar no Pix.</p>
                        @else
                            <p class="po-hint" data-testid="pay-pix-tip">No Pix sai {{ $orders->money($pixOff) }} mais barato.</p>
                        @endif
                    @endif

                    <template x-if="state === 'approved'">
                        <div style="margin-top:14px">
                            <i class="fas fa-circle-check" style="font-size:46px;color:var(--ml-green)"></i>
                            <p class="po-status tone-green" style="margin-top:8px">Pagamento aprovado!</p>
                            <p class="po-hint">Abrindo o seu pedido…</p>
                        </div>
                    </template>

                    @if($method === 'pix')
                        <template x-if="state === 'pending'">
                            <div>
                                <div style="margin-top:8px"><span class="px-timer"><i class="fas fa-clock"></i> <span x-text="'Vence em ' + left"></span></span></div>
                                @if($payment->qr_code_base64)
                                    <div class="px-qr"><img src="data:image/png;base64,{{ $payment->qr_code_base64 }}" alt="QR code do Pix"></div>
                                @endif
                                <p class="po-hint">Aponte a câmera do app do banco ou copie o código abaixo.</p>
                                <div class="px-code">
                                    <input type="text" readonly value="{{ $payment->qr_code }}" aria-label="Código Pix copia e cola" x-ref="code" @focus="$event.target.select()">
                                    <button type="button" class="po-btn po-btn-primary" @click="copy()"><i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i> <span x-text="copied ? 'Copiado' : 'Copiar'"></span></button>
                                </div>
                                <div class="px-wait"><span class="px-spin"></span> Esperando a confirmação do pagamento…</div>
                            </div>
                        </template>
                        <template x-if="state === 'expired' || state === 'rejected' || state === 'cancelled'">
                            <div style="margin-top:14px">
                                <i class="fas fa-clock-rotate-left" style="font-size:40px;color:var(--ml-orange)"></i>
                                <p class="po-status tone-amber" style="margin-top:8px" x-text="state === 'rejected' ? 'Pagamento recusado' : 'Este Pix venceu'"></p>
                                <p class="po-hint">Nada foi cobrado. Gere um novo Pix para pagar.</p>
                                <a href="{{ route('portal.quotes.pay', [$quote, 'metodo' => 'pix']) }}" class="po-btn po-btn-primary" style="margin-top:12px">Gerar novo Pix</a>
                            </div>
                        </template>
                    @elseif($method === 'cartao')
                        <div x-show="state !== 'approved'">
                            <div class="px-wait" x-show="!brickReady"><span class="px-spin"></span> Carregando o formulário seguro…</div>
                            <div id="cardBrick" class="px-card-box" data-testid="card-brick" style="margin-top:10px" x-ignore></div>
                            <div class="px-alert bad" x-show="cardMsg && state === 'rejected'" x-text="cardMsg" data-testid="card-msg"></div>
                            <div class="px-alert wait" x-show="state === 'pending'" data-testid="card-wait"><span x-text="cardMsg || 'Pagamento em análise…'"></span></div>
                            <p class="px-lock"><i class="fas fa-lock"></i> Os dados do cartão vão direto para o Mercado Pago. A loja não vê o número do cartão.</p>
                        </div>
                    @elseif($method === 'boleto')
                        @if($payment)
                            <div x-show="state !== 'approved'" data-testid="boleto-open">
                                <div style="margin-top:8px"><span class="px-timer"><i class="fas fa-calendar"></i> Vence em {{ $payment->expires_at?->format('d/m/Y') }}</span></div>
                                @if($payment->barcode)
                                    <p class="po-hint" style="margin-top:14px">Linha digitável / código de barras</p>
                                    <div class="px-code">
                                        <input type="text" readonly value="{{ $payment->barcode }}" class="px-bar" aria-label="Código de barras do boleto" x-ref="code" @focus="$event.target.select()">
                                        <button type="button" class="po-btn po-btn-primary" @click="copy()"><i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i> <span x-text="copied ? 'Copiado' : 'Copiar'"></span></button>
                                    </div>
                                @endif
                                @if($payment->ticket_url)
                                    <a href="{{ $payment->ticket_url }}" target="_blank" rel="noopener" class="po-btn po-btn-soft po-btn-block" style="margin-top:12px"><i class="fas fa-arrow-up-right-from-square"></i> Abrir o boleto para imprimir</a>
                                @endif
                                <p class="po-hint" style="margin-top:12px">O banco confirma o pagamento em até 2 dias úteis. Você vê aqui e em Meus pedidos quando a loja receber.</p>
                            </div>
                        @else
                            <div style="margin-top:12px">
                                <p class="po-hint">O boleto vence em {{ (int) ($settings->boleto_days ?? 3) }} {{ (int) ($settings->boleto_days ?? 3) === 1 ? 'dia' : 'dias' }}. Ele sai no seu nome e CPF, com o endereço de Meus dados.</p>
                                <form method="POST" action="{{ route('portal.quotes.pay.boleto', $quote) }}" style="margin-top:12px" x-data="{ busy: false }" @submit="busy = true">@csrf
                                    <button type="submit" class="po-btn po-btn-primary po-btn-block" :disabled="busy" data-testid="boleto-make"><i class="fas fa-barcode"></i> <span x-text="busy ? 'Gerando…' : 'Gerar boleto'"></span></button>
                                </form>
                            </div>
                        @endif
                    @endif

                    @if($settings?->testMode())<p class="px-test">Modo de teste do Mercado Pago: nenhum dinheiro real é cobrado.</p>@endif
                </div>
            </section>
        </div>
        <div class="po-side">
            <section class="po-card">
                <div class="pa-sec-head"><h2>{{ $method === 'cartao' ? 'Como funciona' : 'Como pagar' }}</h2></div>
                <div class="po-card-body">
                    <ol class="px-steps">
                        @if($method === 'pix')
                            <li>Abra o app do seu banco e escolha Pix.</li>
                            <li>Leia o QR code ou use Pix Copia e Cola.</li>
                            <li>Confira o valor e confirme.</li>
                        @elseif($method === 'cartao')
                            <li>Preencha os dados do cartão e escolha as parcelas.</li>
                            <li>Toque em Pagar.</li>
                            <li>Aprovado, o pedido vai pago direto para a loja.</li>
                        @else
                            <li>Gere o boleto e pague no app do banco, lotérica ou internet banking.</li>
                            <li>A confirmação chega em até 2 dias úteis.</li>
                        @endif
                    </ol>
                    @if($method !== 'boleto')
                    <p class="po-hint" style="margin-top:10px">Assim que for aprovado, esta tela muda sozinha e a loja recebe o seu pedido pago.</p>
                    @endif
                </div>
                @if($quote->delivery_method)
                <div class="po-card-foot" style="display:block">
                    <div style="font-weight:700;font-size:14px">{{ $quote->delivery_method === 'entrega' ? 'Entrega' : 'Retirada na loja' }}</div>
                    <div class="po-hint" style="margin:2px 0 0">
                        @if($quote->delivery_method === 'entrega'){{ $quote->shipping_address['text'] ?? '' }}@if((float) $quote->delivery_fee > 0) · taxa {{ $orders->money((float) $quote->delivery_fee) }}@endif
                        @else A loja combina o horário da retirada com você.@endif
                    </div>
                </div>
                @endif
                @if($settings?->allow_manual)
                <form method="POST" action="{{ route('portal.quotes.manual', $quote) }}" class="po-card-foot" style="margin:0">@csrf
                    <button type="submit" style="border:0;background:none;padding:0;cursor:pointer;font-weight:700;color:var(--ml-blue-dark)">Prefiro combinar o pagamento com a loja</button>
                </form>
                @endif
            </section>
        </div>
    </div>
    @endif
</div>

@push('scripts')
@if($method === 'cartao' && ! $error)
<script src="https://sdk.mercadopago.com/js/v2"></script>
@endif
<script>
function portalPay(cfg) {
    return {
        state: cfg.method === 'cartao' ? 'idle' : 'pending',
        left: '',
        copied: false,
        brickReady: false,
        cardMsg: '',
        init() {
            if (cfg.expires) { this.tick(); setInterval(() => this.tick(), 1000); }
            if (cfg.poll) setTimeout(() => this.poll(), cfg.poll);
            if (cfg.method === 'cartao' && cfg.pk) this.mountCard(0);
        },
        tick() {
            const ms = new Date(cfg.expires) - new Date();
            if (ms <= 0) { this.left = '0:00'; if (this.state === 'pending') this.state = 'expired'; return; }
            const m = Math.floor(ms / 60000), s = Math.floor(ms / 1000) % 60;
            this.left = m + ':' + String(s).padStart(2, '0');
        },
        async poll() {
            if (this.state !== 'pending' && this.state !== 'expired') return;
            try {
                const r = await fetch(cfg.status, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const d = await r.json();
                if (d.status === 'approved') { this.done(d.redirect); return; }
                if (cfg.method !== 'cartao' && ['rejected', 'cancelled'].includes(d.status)) { this.state = d.status; return; }
                if (cfg.method === 'cartao' && d.status === 'rejected') { this.state = 'rejected'; this.cardMsg = 'Pagamento recusado após análise. Tente outro cartão ou o Pix.'; return; }
                if (d.status === 'expired') { this.state = 'expired'; }
            } catch (e) {}
            // Mesmo vencido, confere mais um pouco: o Pix pode ter sido pago no último minuto.
            if (this.state === 'pending' || (this.state === 'expired' && cfg.expires && new Date() - new Date(cfg.expires) < 120000)) {
                setTimeout(() => this.poll(), document.hidden ? Math.max(10000, cfg.poll || 5000) : (cfg.poll || 5000));
            }
        },
        done(url) {
            this.state = 'approved';
            setTimeout(() => location.href = url, 1200);
        },
        async copy() {
            const v = this.$refs.code.value;
            try { await navigator.clipboard.writeText(v); } catch (e) { this.$refs.code.select(); document.execCommand('copy'); }
            this.copied = true; setTimeout(() => this.copied = false, 2000);
        },
        // Formulário seguro do Mercado Pago (Card Payment Brick): o cartão vira um token no navegador.
        async mountCard(tries) {
            if (typeof window.MercadoPago === 'undefined') {
                if (tries < 60) return setTimeout(() => this.mountCard(tries + 1), 150);
                this.cardMsg = 'Não foi possível carregar o pagamento com cartão. Recarregue a página ou use o Pix.';
                this.state = 'rejected';
                return;
            }
            const mp = new window.MercadoPago(cfg.pk, { locale: 'pt-BR' });
            await mp.bricks().create('cardPayment', 'cardBrick', {
                initialization: { amount: cfg.amount, payer: { email: cfg.email || undefined, identification: cfg.cpf ? { type: cfg.cpf.length > 11 ? 'CNPJ' : 'CPF', number: cfg.cpf } : undefined } },
                customization: {
                    paymentMethods: { maxInstallments: Math.max(1, cfg.maxInst), types: { included: ['credit_card', 'debit_card'] } },
                    visual: { style: { theme: 'default', customVariables: { baseColor: '#3483fa' } } },
                },
                callbacks: {
                    onReady: () => { this.brickReady = true; },
                    onError: (e) => { console.warn('cartão', e); },
                    onSubmit: (form) => this.payCard(form),
                },
            });
        },
        payCard(form) {
            this.cardMsg = 'Processando o pagamento…'; this.state = 'pending';
            return fetch(cfg.card, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                body: JSON.stringify(form),
            }).then(r => r.json()).then(d => {
                if (d.status === 'approved') { this.done(d.redirect); return; }
                this.cardMsg = d.message || 'Pagamento recusado.';
                if (['pending', 'in_process', 'authorized'].includes(d.status)) {
                    this.state = 'pending';
                    cfg.poll = 5000; setTimeout(() => this.poll(), 5000);
                    return;
                }
                this.state = 'rejected';
                throw new Error(this.cardMsg); // o formulário volta a ficar editável
            });
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
