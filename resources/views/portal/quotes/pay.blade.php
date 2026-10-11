{{-- Pagar o pedido com Pix (Mercado Pago), no visual do catálogo. --}}
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
    </style>
@endpush

@php $amount = (float) ($payment->amount ?? 0); @endphp

<div class="po-page" x-data="pixPay(@js(route('portal.quotes.pay.status', $quote)), @js($payment?->expires_at?->toIso8601String()))">
    <a href="{{ route('portal.quotes.show', $quote) }}" class="po-back"><i class="fas fa-chevron-left"></i> Pedido #{{ $quote->id }}</a>
    <div class="po-head">
        <div>
            <h1>Pagar com Pix</h1>
            <p>Pedido #{{ $quote->id }} · {{ $store?->name ?? 'Loja' }}</p>
        </div>
    </div>

    @include('portal.partials.flash')

    @if($error)
        <div class="po-card po-empty">
            <i class="fas fa-circle-exclamation" style="color:var(--ml-orange)"></i>
            <h2>Não deu para gerar o Pix</h2>
            <p>{{ $error }}</p>
            <div class="po-actions" style="max-width:340px;margin:0 auto">
                <a href="{{ route('portal.quotes.pay', $quote) }}" class="po-btn po-btn-primary">Tentar de novo</a>
                @if(! $settings || $settings->allow_manual || ! $settings->pixReady())
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
                    <div class="po-total-lbl">Valor do Pix</div>
                    <div class="px-amount">{{ $orders->money($amount) }}</div>

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
                            @if($settings?->testMode())<p class="px-test">Modo de teste do Mercado Pago: nenhum dinheiro real é cobrado.</p>@endif
                        </div>
                    </template>

                    <template x-if="state === 'approved'">
                        <div style="margin-top:14px">
                            <i class="fas fa-circle-check" style="font-size:46px;color:var(--ml-green)"></i>
                            <p class="po-status tone-green" style="margin-top:8px">Pagamento aprovado!</p>
                            <p class="po-hint">Abrindo o seu pedido…</p>
                        </div>
                    </template>

                    <template x-if="state === 'expired' || state === 'rejected' || state === 'cancelled'">
                        <div style="margin-top:14px">
                            <i class="fas fa-clock-rotate-left" style="font-size:40px;color:var(--ml-orange)"></i>
                            <p class="po-status tone-amber" style="margin-top:8px" x-text="state === 'rejected' ? 'Pagamento recusado' : 'Este Pix venceu'"></p>
                            <p class="po-hint">Nada foi cobrado. Gere um novo Pix para pagar.</p>
                            <a href="{{ route('portal.quotes.pay', $quote) }}" class="po-btn po-btn-primary" style="margin-top:12px">Gerar novo Pix</a>
                        </div>
                    </template>
                </div>
            </section>
        </div>
        <div class="po-side">
            <section class="po-card">
                <div class="pa-sec-head"><h2>Como pagar</h2></div>
                <div class="po-card-body">
                    <ol class="px-steps">
                        <li>Abra o app do seu banco e escolha Pix.</li>
                        <li>Leia o QR code ou use Pix Copia e Cola.</li>
                        <li>Confira o valor e confirme.</li>
                    </ol>
                    <p class="po-hint" style="margin-top:10px">Assim que o banco confirmar, esta tela muda sozinha e a loja recebe o seu pedido pago.</p>
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
<script>
function pixPay(statusUrl, expiresAt) {
    return {
        state: 'pending',
        left: '',
        copied: false,
        timer: null,
        init() {
            if (!expiresAt) return;
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
            this.poll();
        },
        tick() {
            const ms = new Date(expiresAt) - new Date();
            if (ms <= 0) { this.left = '0:00'; if (this.state === 'pending') this.state = 'expired'; return; }
            const m = Math.floor(ms / 60000), s = Math.floor(ms / 1000) % 60;
            this.left = m + ':' + String(s).padStart(2, '0');
        },
        async poll() {
            if (this.state !== 'pending' && this.state !== 'expired') return;
            try {
                const r = await fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const d = await r.json();
                if (d.status === 'approved') {
                    this.state = 'approved';
                    setTimeout(() => location.href = d.redirect, 1200);
                    return;
                }
                if (['rejected', 'cancelled'].includes(d.status)) { this.state = d.status; return; }
                if (d.status === 'expired') { this.state = 'expired'; }
            } catch (e) {}
            // Mesmo vencido, confere mais um pouco: o pagamento pode ter sido feito no último minuto.
            if (this.state === 'pending' || (this.state === 'expired' && new Date() - new Date(expiresAt) < 120000)) {
                setTimeout(() => this.poll(), document.hidden ? 10000 : 5000);
            }
        },
        async copy() {
            const v = this.$refs.code.value;
            try { await navigator.clipboard.writeText(v); } catch (e) { this.$refs.code.select(); document.execCommand('copy'); }
            this.copied = true; setTimeout(() => this.copied = false, 2000);
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
