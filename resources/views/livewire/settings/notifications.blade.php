<?php

use App\Models\ConsortiumNotification;
use Livewire\Volt\Component;

new class extends Component {
    /** Tipos de aviso do sino ligados (preferences.notifications.inapp.<tipo>; padrão ligado). */
    public array $inapp = [];

    public function mount(): void
    {
        $saved = auth()->user()->preferences['notifications']['inapp'] ?? [];
        foreach (array_keys(ConsortiumNotification::CATEGORIES) as $key) {
            if ($key !== 'sistema') {
                $this->inapp[$key] = ($saved[$key] ?? true) !== false;
            }
        }
    }

    public function toggle(string $key): void
    {
        if (! array_key_exists($key, $this->inapp)) {
            return;
        }
        $this->inapp[$key] = ! $this->inapp[$key];
        $this->persist();
    }

    public function setAll(bool $on): void
    {
        foreach ($this->inapp as $key => $v) {
            $this->inapp[$key] = $on;
        }
        $this->persist();
    }

    protected function persist(): void
    {
        $user = auth()->user();
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $notif = is_array($prefs['notifications'] ?? null) ? $prefs['notifications'] : [];
        $notif['inapp'] = $this->inapp;
        $prefs['notifications'] = $notif;
        $user->update(['preferences' => $prefs]);
        $this->dispatch('notif-saved');
    }
}; ?>

@php
    $cats = ConsortiumNotification::CATEGORIES;
    $catInfo = [
        'vendas' => ['Pedidos e orçamentos do portal, pagamentos recebidos e promoções', 'Na hora'],
        'estoque' => ['Produtos acabando ou esgotados', 'Todo dia às 08:30'],
        'mercadolivre' => ['Pedidos, perguntas, mensagens e reclamações', 'Na hora'],
        'shopee' => ['Pedidos e avisos da loja', 'Na hora'],
        'consorcio' => ['Sorteios disponíveis e resgates pendentes', 'Todo dia às 08:00'],
        'financeiro' => ['Contas e cobranças a vencer e em atraso', 'Todo dia às 09:00'],
    ];
    $tones = [
        'vendas' => 'from-emerald-500 to-teal-500', 'estoque' => 'from-amber-500 to-orange-500', 'mercadolivre' => 'from-yellow-400 to-amber-500',
        'shopee' => 'from-orange-500 to-red-500', 'consorcio' => 'from-violet-500 to-purple-600', 'financeiro' => 'from-sky-500 to-blue-600',
    ];
    $on = collect($inapp)->filter()->count();
    $total = count($inapp);
    $unread = 0;
    try { $unread = ConsortiumNotification::unreadCountForUser(auth()->id()); } catch (\Throwable $e) { $unread = 0; }
@endphp

<section class="settings-notifications-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="nt-page" x-data="{ saved: false }" @notif-saved.window="saved = true; clearTimeout($el._t); $el._t = setTimeout(() => saved = false, 1800)">
        {{-- Topo: quantos tipos estão ligados e atalho para o sino --}}
        <div class="nt-hero bg-gradient-to-br from-orange-500 via-rose-500 to-fuchsia-600">
            <span class="nt-glow"></span>
            <div class="nt-bell"><i class="bi bi-bell-fill"></i>@if($unread)<em>{{ $unread > 99 ? '99+' : $unread }}</em>@endif</div>
            <div class="nt-hero-txt">
                <span class="nt-kicker"><i class="bi bi-sliders"></i> Avisos do app</span>
                <h2>{{ $on }} de {{ $total }} tipos de aviso ligados</h2>
                <p>Escolha o que aparece no sino. O que estiver desligado não chega mais.</p>
            </div>
            <a href="{{ route('notifications.index') }}" wire:navigate class="nt-open">
                <i class="bi bi-inbox"></i> {{ $unread ? $unread . ' não ' . ($unread === 1 ? 'lida' : 'lidas') : 'Abrir avisos' }}
            </a>
        </div>

        <div class="pl-grid">
            <div class="pl-main">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-orange-500 to-red-500"><i class="bi bi-bell"></i></span>
                        <div class="min-w-0">
                            <h2>O que aparece no sino</h2>
                            <p>Toque para ligar ou desligar. Salva sozinho.</p>
                        </div>
                        <span class="ap-saved" x-cloak x-show="saved" x-transition.opacity><i class="bi bi-check2-circle"></i> Salvo</span>
                    </div>

                    <div class="nt-list">
                        @foreach($inapp as $key => $isOn)
                            @php $cat = $cats[$key] ?? ['label' => ucfirst($key), 'icon' => 'bi-bell']; @endphp
                            <button type="button" class="nt-row {{ $isOn ? 'is-on' : '' }}" wire:click="toggle('{{ $key }}')" wire:key="nt-{{ $key }}" aria-pressed="{{ $isOn ? 'true' : 'false' }}">
                                <span class="nt-ico bg-gradient-to-br {{ $tones[$key] ?? 'from-indigo-500 to-purple-600' }}"><i class="bi {{ $cat['icon'] }}"></i></span>
                                <span class="nt-txt">
                                    <b>{{ $cat['label'] }}</b>
                                    <small>{{ $catInfo[$key][0] ?? '' }}</small>
                                    <em><i class="bi bi-clock"></i> {{ $catInfo[$key][1] ?? 'Na hora' }}</em>
                                </span>
                                <span class="nt-switch"><span></span></span>
                            </button>
                        @endforeach
                    </div>

                    <div class="nt-actions">
                        <button type="button" wire:click="setAll(false)" class="ap-btn-ghost"><i class="bi bi-bell-slash"></i> Desligar todos</button>
                        <button type="button" wire:click="setAll(true)" class="ap-btn-ghost"><i class="bi bi-bell"></i> Ligar todos</button>
                    </div>
                </div>
            </div>

            <aside class="pl-side">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-info-lg"></i></span>
                        <div class="min-w-0">
                            <h2>Como os avisos chegam</h2>
                            <p>Tudo fica no sino e na tela Avisos.</p>
                        </div>
                    </div>
                    <ul class="nt-how">
                        <li><i class="bi bi-lightning-charge"></i><span><b>Na hora</b> quando acontece: pedido novo, pagamento, pergunta do Mercado Livre.</span></li>
                        <li><i class="bi bi-sunrise"></i><span><b>De manhã</b> os resumos do dia: consórcio às 08:00, estoque às 08:30 e cobranças às 09:00.</span></li>
                        <li><i class="bi bi-phone"></i><span>No celular, o número de avisos aparece no sino do topo de cada tela.</span></li>
                    </ul>
                </div>
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-box-seam"></i></span>
                        <div class="min-w-0">
                            <h2>Estoque baixo</h2>
                            <p>O mínimo de cada produto define quando avisa.</p>
                        </div>
                    </div>
                    <a href="{{ route('settings.products') }}" wire:navigate class="nt-link"><i class="bi bi-sliders"></i> Configurações de produtos <i class="bi bi-chevron-right"></i></a>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .nt-page { display: flex; flex-direction: column; gap: 1rem; }
        .pl-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .pl-main, .pl-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .nt-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .nt-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .nt-bell { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.7rem; }
        .nt-bell em { position: absolute; top: -.4rem; right: -.4rem; min-width: 1.5rem; height: 1.5rem; padding: 0 .3rem; border-radius: 999px; background: #fff; color: #e11d48; font-style: normal; font-size: .7rem; font-weight: 900; display: grid; place-items: center; }
        .nt-hero-txt { position: relative; flex: 1; min-width: 0; }
        .nt-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .nt-hero-txt h2 { font-size: 1.45rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .nt-hero-txt p { font-size: .82rem; opacity: .92; }
        .nt-open { position: relative; flex: none; display: inline-flex; align-items: center; gap: .4rem; padding: .65rem 1rem; border-radius: .9rem; background: #fff; color: #be123c; font-size: .82rem; font-weight: 800; box-shadow: 0 10px 20px -12px rgba(0, 0, 0, .5); }
        .nt-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; }
        .nt-row { width: 100%; justify-content: flex-start; display: flex; align-items: center; gap: .75rem; padding: .8rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; transition: all .15s; }
        .nt-row.is-on { background: #fff; border-color: var(--color-indigo-200); box-shadow: 0 8px 20px -16px var(--color-indigo-600); }
        .dark .nt-row { background: #111827; border-color: #1f2937; }
        .dark .nt-row.is-on { background: rgba(99, 102, 241, .08); border-color: rgba(99, 102, 241, .35); box-shadow: none; }
        .nt-ico { flex: none; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: .85rem; color: #fff; font-size: 1.05rem; transition: filter .15s, opacity .15s; }
        .nt-row:not(.is-on) .nt-ico { filter: grayscale(1); opacity: .5; }
        .nt-txt { display: block; flex: 1 1 auto; min-width: 0; width: 100%; text-align: left; }
        .nt-txt b { display: block; font-size: .88rem; color: #0f172a; }
        .nt-txt small { display: block; font-size: .73rem; color: #64748b; line-height: 1.3; }
        .nt-txt em { display: inline-flex; align-items: center; gap: .25rem; margin-top: .3rem; font-style: normal; font-size: .66rem; font-weight: 700; color: #94a3b8; }
        .dark .nt-txt b { color: #f1f5f9; }
        .dark .nt-txt small { color: #94a3b8; }
        .nt-row:not(.is-on) .nt-txt b { color: #94a3b8; }
        .nt-switch { flex: none; position: relative; width: 2.4rem; height: 1.4rem; border-radius: 999px; background: #cbd5e1; transition: background .2s; }
        .nt-switch span { position: absolute; top: .2rem; left: .2rem; width: 1rem; height: 1rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .2s; }
        .nt-row.is-on .nt-switch { background: var(--color-indigo-600); }
        .nt-row.is-on .nt-switch span { transform: translateX(1rem); }
        .dark .nt-switch { background: #334155; }
        .nt-actions { display: flex; justify-content: flex-end; gap: .5rem; margin-top: 1rem; }
        .nt-how { display: flex; flex-direction: column; gap: .7rem; font-size: .8rem; color: #334155; }
        .nt-how li { display: flex; gap: .6rem; }
        .nt-how i { flex: none; color: var(--color-indigo-500); font-size: 1rem; }
        .nt-how b { color: #0f172a; }
        .dark .nt-how { color: #cbd5e1; }
        .dark .nt-how b { color: #f1f5f9; }
        .nt-link { display: flex; align-items: center; gap: .5rem; padding: .65rem .8rem; border-radius: .9rem; background: #f8fafc; border: 1px solid #eef2f7; font-size: .82rem; font-weight: 700; color: #334155; }
        .nt-link .bi-chevron-right { margin-left: auto; color: #cbd5e1; }
        .dark .nt-link { background: #111827; border-color: #1f2937; color: #cbd5e1; }
        @media (max-width: 1535px) { .nt-list { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1279px) { .pl-grid { grid-template-columns: minmax(0, 1fr) 300px; } }
        @media (max-width: 1023px) { .pl-grid { grid-template-columns: minmax(0, 1fr); } .pl-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } .nt-list { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .nt-hero { flex-wrap: wrap; padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .nt-bell { width: 3.2rem; height: 3.2rem; font-size: 1.35rem; }
            .nt-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .nt-hero-txt h2 { font-size: 1.12rem; }
            .nt-open { width: 100%; justify-content: center; }
            .nt-list { grid-template-columns: minmax(0, 1fr); }
            .pl-side { display: flex; }
            .nt-actions button { flex: 1; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
