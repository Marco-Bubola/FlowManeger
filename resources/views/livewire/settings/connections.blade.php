<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public array $stats = [];

    public function mount(): void
    {
        $uid = Auth::id();
        $safe = function (callable $fn, $default = null) {
            try { return $fn(); } catch (\Throwable $e) { return $default; }
        };

        $mlToken = $safe(fn () => DB::table('mercadolivre_tokens')->where('user_id', $uid)->where('is_active', 1)->latest('updated_at')->first());
        $shopeeToken = $safe(fn () => DB::table('shopee_tokens')->where('user_id', $uid)->where('is_active', 1)->latest('updated_at')->first());
        $pay = $safe(fn () => \App\Models\PaymentSetting::where('user_id', $uid)->first());
        $wa = $safe(fn () => \App\Models\WhatsAppSetting::where('user_id', $uid)->first());

        $this->stats = [
            'ml_connected' => ! is_null($mlToken),
            'ml_expires' => $mlToken?->expires_at,
            'shopee_connected' => ! is_null($shopeeToken),
            'shopee_shop_id' => $shopeeToken?->shop_id ?? null,
            'pay_on' => (bool) ($pay && $pay->enabled && filled($pay->access_token)),
            'pay_test' => (bool) ($pay && str_starts_with((string) $pay->access_token, 'TEST-')),
            'wa_on' => (bool) ($wa?->enabled),
            'banks' => (int) $safe(fn () => \App\Models\BankConnectionAccount::where('user_id', $uid)->count(), 0),
            'google' => ! empty(Auth::user()->google_id),
        ];
    }
}; ?>

@php
    $s = $stats;
    $r = fn ($name, $fallback = null) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;
    // Cada integração: [nome, descrição, ícone, cor, ligada?, situação, [botões [texto, url, principal?]]]
    $cards = [
        'Vendas em marketplaces' => [
            ['Mercado Livre', 'Anúncios, pedidos, perguntas e estoque sincronizado', 'bi-bag-check', 'from-yellow-400 to-amber-500', $s['ml_connected'],
                $s['ml_connected'] ? ($s['ml_expires'] ? 'Conectado · renova ' . \Carbon\Carbon::parse($s['ml_expires'])->locale('pt_BR')->diffForHumans() : 'Conectado') : 'Não conectado',
                $s['ml_connected'] ? [['Configurar', $r('mercadolivre.settings'), true], ['Anúncios', $r('mercadolivre.publications'), false]] : [['Conectar conta', $r('mercadolivre.auth.redirect'), true]]],
            ['Shopee', 'Pedidos, anúncios e estoque da loja', 'bi-shop', 'from-orange-500 to-red-500', $s['shopee_connected'],
                $s['shopee_connected'] ? 'Conectado' . ($s['shopee_shop_id'] ? ' · loja ' . $s['shopee_shop_id'] : '') : 'Não conectado',
                $s['shopee_connected'] ? [['Configurar', $r('shopee.settings'), true], ['Anúncios', $r('shopee.publications'), false]] : [['Conectar loja', $r('shopee.auth.connect'), true]]],
        ],
        'Dinheiro e pagamentos' => [
            ['Mercado Pago', 'Pix, cartão e boleto no catálogo online', 'bi-qr-code', 'from-sky-500 to-cyan-600', $s['pay_on'],
                $s['pay_on'] ? ($s['pay_test'] ? 'Ligado em modo teste' : 'Recebendo pagamentos') : 'Não configurado',
                [[$s['pay_on'] ? 'Configurar' : 'Configurar agora', $r('settings.payments'), true]]],
            ['Bancos conectados', 'Extratos dos bancos entram sozinhos no livro caixa', 'bi-bank', 'from-slate-500 to-slate-700', $s['banks'] > 0,
                $s['banks'] ? $s['banks'] . ' ' . ($s['banks'] === 1 ? 'conta ligada' : 'contas ligadas') : 'Nenhum banco',
                [[$s['banks'] ? 'Gerenciar' : 'Conectar banco', $r('settings.bank-connections'), true]]],
            ['WhatsApp', 'Mande o print da compra e ela entra na fatura ou no caixa', 'bi-whatsapp', 'from-green-500 to-emerald-600', $s['wa_on'],
                $s['wa_on'] ? 'Ligado' : 'Desligado',
                [[$s['wa_on'] ? 'Configurar' : 'Ligar', $r('settings.whatsapp'), true], ['Lançar por print', $r('finance.print-capture'), false]]],
        ],
    ];
    $all = collect($cards)->flatten(1);
    $onCount = $all->filter(fn ($c) => $c[4])->count();
@endphp

<section class="settings-connections-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="cn-page">
        <div class="cn-hero bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500">
            <span class="cn-glow"></span>
            <div class="cn-dots">
                @foreach($all as $c)
                    <span class="bg-gradient-to-br {{ $c[3] }} {{ $c[4] ? 'is-on' : '' }}" title="{{ $c[0] }}"><i class="bi {{ $c[2] }}"></i></span>
                @endforeach
            </div>
            <div class="cn-hero-txt">
                <span class="cn-kicker"><i class="bi bi-plug"></i> Integrações</span>
                <h2>{{ $onCount }} de {{ $all->count() }} ligadas</h2>
                <p>Ligue o app aos lugares onde você vende e recebe. Cada uma se configura na própria tela.</p>
            </div>
        </div>

        @foreach($cards as $group => $items)
            <div class="cn-group">
                <p class="cn-group-title">{{ $group }}</p>
                <div class="cn-grid">
                    @foreach($items as [$name, $desc, $icon, $tone, $on, $status, $buttons])
                        <div class="ap-card cn-card {{ $on ? 'is-on' : '' }}">
                            <div class="cn-top">
                                <span class="cn-ico bg-gradient-to-br {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                                <span class="cn-state"><i class="bi bi-circle-fill"></i> {{ $on ? 'Ligado' : 'Desligado' }}</span>
                            </div>
                            <h3>{{ $name }}</h3>
                            <p>{{ $desc }}</p>
                            <small class="cn-status">{{ $status }}</small>
                            <div class="cn-btns">
                                @foreach($buttons as [$label, $url, $primary])
                                    @if($url)
                                        <a href="{{ $url }}" class="{{ $primary ? 'cn-btn' : 'cn-btn-ghost' }}">{{ $label }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="ap-card cn-note">
            <i class="bi bi-shield-lock"></i>
            <p>Chaves e senhas das integrações ficam guardadas com criptografia e só são coladas nas telas de Configurações. Nunca mande essas chaves por mensagem.</p>
            @if($s['google'])
                <span class="cn-google"><i class="bi bi-google"></i> Login com Google vinculado</span>
            @endif
        </div>
    </div>

    <style>
        .cn-page { display: flex; flex-direction: column; gap: 1rem; }
        .cn-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.3rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .cn-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .cn-dots { position: relative; flex: none; display: grid; grid-template-columns: repeat(3, 1.9rem); gap: .35rem; padding: .55rem; border-radius: 1.1rem; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .28); }
        .cn-dots span { display: grid; place-items: center; width: 1.9rem; height: 1.9rem; border-radius: .6rem; font-size: .85rem; filter: grayscale(1); opacity: .45; }
        .cn-dots span.is-on { filter: none; opacity: 1; box-shadow: 0 0 0 2px #fff; }
        .cn-hero-txt { position: relative; flex: 1; min-width: 0; }
        .cn-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .cn-hero-txt h2 { font-size: 1.45rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .cn-hero-txt p { font-size: .82rem; opacity: .92; max-width: 36rem; }
        .cn-group-title { padding: 0 .3rem .5rem; font-size: .72rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: #64748b; }
        .dark .cn-group-title { color: #94a3b8; }
        .cn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr)); gap: 1rem; }
        .cn-card { display: flex; flex-direction: column; }
        .cn-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .8rem; }
        .cn-ico { display: grid; place-items: center; width: 3rem; height: 3rem; border-radius: 1rem; color: #fff; font-size: 1.35rem; box-shadow: 0 10px 20px -10px rgba(15, 23, 42, .55); }
        .cn-card:not(.is-on) .cn-ico { filter: saturate(.55); }
        .cn-state { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 999px; background: #f1f5f9; color: #64748b; }
        .cn-state i { font-size: .4rem; }
        .cn-card.is-on .cn-state { background: #ecfdf5; color: #047857; }
        .dark .cn-state { background: #1e293b; color: #94a3b8; }
        .dark .cn-card.is-on .cn-state { background: rgba(16, 185, 129, .12); color: #6ee7b7; }
        .cn-card h3 { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .cn-card > p { font-size: .78rem; color: #64748b; margin-top: .15rem; flex: 1; }
        .dark .cn-card h3 { color: #f1f5f9; }
        .dark .cn-card > p { color: #94a3b8; }
        .cn-status { display: block; margin-top: .7rem; font-size: .74rem; font-weight: 700; color: #475569; }
        .dark .cn-status { color: #cbd5e1; }
        .cn-btns { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .85rem; }
        .cn-btn, .cn-btn-ghost { display: inline-flex; align-items: center; justify-content: center; flex: 1; gap: .35rem; font-size: .8rem; font-weight: 800; padding: .55rem .9rem; border-radius: .85rem; white-space: nowrap; }
        .cn-btn { color: #fff; background: linear-gradient(135deg, var(--color-indigo-600), var(--color-purple-600)); box-shadow: 0 10px 20px -12px var(--color-indigo-600); }
        .cn-btn-ghost { color: #475569; border: 1px solid #e2e8f0; background: #fff; }
        .dark .cn-btn-ghost { color: #cbd5e1; border-color: #334155; background: transparent; }
        .cn-note { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; }
        .cn-note > i { font-size: 1.3rem; color: var(--color-indigo-500); }
        .cn-note p { flex: 1; min-width: 14rem; font-size: .8rem; color: #475569; }
        .dark .cn-note p { color: #cbd5e1; }
        .cn-google { display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 800; color: #1d4ed8; background: #eff6ff; padding: .35rem .7rem; border-radius: 999px; }
        .dark .cn-google { background: rgba(59, 130, 246, .12); color: #93c5fd; }
        @media (max-width: 767px) {
            .cn-hero { flex-direction: column; align-items: flex-start; padding: 1.1rem; border-radius: 1.3rem; gap: .8rem; }
            .cn-dots { grid-template-columns: repeat(5, 1.9rem); }
            .cn-hero-txt h2 { font-size: 1.15rem; }
        }
    </style>
    </x-settings.layout>
</section>
