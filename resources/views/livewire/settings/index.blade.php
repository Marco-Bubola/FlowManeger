<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

@php
    // Início das Configurações: resumo da conta e todas as páginas por grupo, com a situação de cada uma.
    $u = auth()->user();
    $groups = \App\Support\SettingsMenu::groups($u);
    $plan = null;
    try { $plan = $u->plan(); } catch (\Throwable $e) { $plan = null; }
    $verified = $u->hasVerifiedEmail();
    $hasAvatar = ! empty($u->profile_picture);
    $safe = fn (callable $fn, $default = null) => (function () use ($fn, $default) { try { return $fn(); } catch (\Throwable $e) { return $default; } })();

    // Perfil completo
    $profileChecks = [$hasAvatar, ! empty($u->phone), ! empty($u->birth_date), ! empty($u->location), ! empty($u->about_me), $verified, ! empty($u->instagram) || ! empty($u->website)];
    $profilePct = (int) round(collect($profileChecks)->filter()->count() / count($profileChecks) * 100);

    // Nota de segurança (mesma regra do cabeçalho antigo)
    $secScore = collect([$verified, ! empty($u->password), ! empty($u->phone), $hasAvatar])->filter()->count();
    $secPct = (int) ($secScore / 4 * 100);

    $palette = $u->preferences['appearance']['palette'] ?? [];
    $themeNames = ['flow' => 'Flow', 'oceano' => 'Oceano', 'esmeralda' => 'Esmeralda', 'por-do-sol' => 'Pôr do sol', 'framboesa' => 'Framboesa', 'lavanda' => 'Lavanda', 'ceu' => 'Céu', 'floresta' => 'Floresta', 'vinho' => 'Vinho', 'ambar' => 'Âmbar', 'grafite' => 'Grafite', 'cereja' => 'Cereja'];

    $db = \Illuminate\Support\Facades\DB::class;
    $mlOn = $safe(fn () => $db::table('mercadolivre_tokens')->where('user_id', $u->id)->where('is_active', 1)->exists(), false);
    $shopeeOn = $safe(fn () => $db::table('shopee_tokens')->where('user_id', $u->id)->where('is_active', 1)->exists(), false);
    $waOn = $safe(fn () => (bool) \App\Models\WhatsAppSetting::where('user_id', $u->id)->value('enabled'), false);
    $banks = $safe(fn () => \App\Models\BankConnectionAccount::where('user_id', $u->id)->count(), 0);
    $payOn = $safe(fn () => (bool) \App\Models\PaymentSetting::where('user_id', $u->id)->where('enabled', true)->whereNotNull('access_token')->exists(), false);
    $devices = $safe(fn () => config('session.driver') === 'database' ? $db::table(config('session.table', 'sessions'))->where('user_id', $u->id)->count() : null);
    $team = $safe(fn () => $u->activeTeam());
    $seats = $team ? $safe(fn () => $team->currentMemberCount(), 1) : 1;

    // Situação mostrada em cada página: [texto, tom]
    $status = [
        'settings.profile' => [$profilePct . '% completo', $profilePct >= 80 ? 'ok' : 'warn'],
        'settings.password' => [$u->updated_at ? 'Atualizada ' . $u->updated_at->locale('pt_BR')->diffForHumans() : 'Definida', 'off'],
        'settings.security' => ['Nota ' . $secPct . '%', $secPct >= 75 ? 'ok' : 'warn'],
        'settings.devices' => [$devices ? $devices . ' ' . ($devices === 1 ? 'aparelho' : 'aparelhos') : 'Este aparelho', 'off'],
        'settings.plan' => [$plan?->name ?? 'Grátis', 'ok'],
        'settings.team' => [$team ? $seats . ' ' . ($seats === 1 ? 'pessoa' : 'pessoas') : 'Só você', 'off'],
        'settings.menu' => (function () { $n = \App\Support\AppMenu::hiddenCount(); return [$n ? $n . ' escondida' . ($n > 1 ? 's' : '') : 'Tudo à mostra', 'off']; })(),
        'settings.appearance' => [$themeNames[$palette['theme'] ?? ''] ?? (empty($palette) ? 'Flow' : 'Cores próprias'), 'off'],
        'settings.whatsapp' => [$waOn ? 'Ligado' : 'Desligado', $waOn ? 'ok' : 'off'],
        'settings.bank-connections' => [$banks ? $banks . ' ' . ($banks === 1 ? 'conta' : 'contas') : 'Nenhum banco', $banks ? 'ok' : 'off'],
        'settings.payments' => [$payOn ? 'Recebendo' : 'Não configurado', $payOn ? 'ok' : 'off'],
        'settings.connections' => [collect([$mlOn ? 'ML' : null, $shopeeOn ? 'Shopee' : null])->filter()->implode(' e ') ?: 'Nada conectado', ($mlOn || $shopeeOn) ? 'ok' : 'off'],
    ];

    // Pendências com atalho
    $todo = collect([
        ! $verified ? ['Confirme seu e-mail', 'Para recuperar a conta se perder a senha', 'settings.profile', 'bi-envelope-exclamation', 'acc-contato'] : null,
        ! $hasAvatar ? ['Coloque uma foto', 'Aparece no menu e para a equipe', 'settings.profile', 'bi-camera', 'acc-photo'] : null,
        empty($u->phone) ? ['Adicione seu telefone', 'Ajuda a proteger a conta', 'settings.profile', 'bi-telephone', 'acc-contato'] : null,
    ])->filter()->values();
@endphp

<section class="settings-index-page w-full mobile-393-base">
    <x-settings.layout :hub="true">
    <div class="sh" x-data="{ q: '', has(k) { return k.includes(this.q.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim()); } }">

        {{-- Topo: conta, plano e busca --}}
        <div class="sh-hero bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <span class="sh-glow"></span>
            <a href="{{ route('settings.profile') }}" wire:navigate class="sh-av">
                @if($hasAvatar)
                    <img src="{{ asset('storage/' . $u->profile_picture) }}" alt="">
                @else
                    {{ $u->initials() }}
                @endif
            </a>
            <div class="sh-who">
                <span class="sh-kicker"><i class="bi bi-gear"></i> Configurações</span>
                <h1>{{ $u->name }} @if($verified)<i class="bi bi-patch-check-fill" title="E-mail confirmado"></i>@endif</h1>
                <p>{{ $u->email }}</p>
                <div class="sh-chips">
                    <span><i class="bi bi-patch-check"></i> Plano {{ $plan?->name ?? 'Grátis' }}</span>
                    <span><i class="bi bi-shield-check"></i> Segurança {{ $secPct }}%</span>
                    <span><i class="bi bi-calendar3"></i> Desde {{ $u->created_at ? ucfirst($u->created_at->locale('pt_BR')->translatedFormat('M/Y')) : '—' }}</span>
                </div>
            </div>
            <div class="sh-head-actions"><x-header-bell /></div>
            <label class="sh-search">
                <i class="bi bi-search"></i>
                <input type="search" x-model="q" placeholder="O que você quer ajustar? Ex.: senha, cores, WhatsApp" aria-label="Buscar configuração">
            </label>
        </div>

        @if($todo->isNotEmpty())
            <div class="sh-todo" x-show="!q">
                @foreach($todo as [$tTitle, $tDesc, $tRoute, $tIcon, $tAnchor])
                    <a href="{{ route($tRoute) }}#{{ $tAnchor }}" wire:navigate class="sh-todo-item">
                        <span><i class="bi {{ $tIcon }}"></i></span>
                        <span class="min-w-0"><b>{{ $tTitle }}</b><small>{{ $tDesc }}</small></span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Todas as páginas, por grupo --}}
        <div class="sh-groups">
            @foreach($groups as $gKey => [$gName, $gIcon, $gItems])
                <div class="sh-group" x-show="!q || [...$el.querySelectorAll('[data-k]')].some(a => has(a.dataset.k))">
                    <div class="sh-group-head"><i class="bi {{ $gIcon }}"></i> {{ $gName }}</div>
                    <div class="sh-list">
                        @foreach($gItems as [$iRoute, $iLabel, $iIcon, $iDesc, $iTone])
                            @php [$sTxt, $sTone] = $status[$iRoute] ?? [null, 'off']; @endphp
                            <a href="{{ route($iRoute) }}" @if(str_starts_with($iRoute, 'settings.')) wire:navigate @endif class="sh-item"
                               data-k="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($iLabel . ' ' . $iDesc . ' ' . $gName)) }}"
                               x-show="!q || has($el.dataset.k)">
                                <span class="sh-ico bg-gradient-to-br {{ $iTone }}"><i class="bi {{ $iIcon }}"></i></span>
                                <span class="sh-txt">
                                    <b>{{ $iLabel }}</b>
                                    <small>{{ $iDesc }}</small>
                                </span>
                                @if($sTxt)<span class="sh-badge is-{{ $sTone }}">{{ $sTxt }}</span>@endif
                                <i class="bi bi-chevron-right sh-go"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <p class="sh-empty" x-cloak x-show="q && ![...$root.querySelectorAll('.sh-item')].some(a => has(a.dataset.k))"><i class="bi bi-search"></i> Nada encontrado para “<span x-text="q"></span>”.</p>
    </div>

    <style>
        .sh { display: flex; flex-direction: column; gap: 1rem; }
        .sh-hero { position: relative; overflow: hidden; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: .4rem 1.2rem; align-items: center; padding: 1.5rem 1.6rem 1.3rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .sh-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 8% 0%, rgba(255, 255, 255, .32), transparent 42%), radial-gradient(circle at 100% 100%, rgba(255, 255, 255, .14), transparent 40%); }
        .sh-av { position: relative; display: grid; place-items: center; width: 4.6rem; height: 4.6rem; border-radius: 1.4rem; overflow: hidden; background: rgba(255, 255, 255, .2); border: 2px solid rgba(255, 255, 255, .45); font-size: 1.5rem; font-weight: 900; }
        .sh-av img { width: 100%; height: 100%; object-fit: cover; }
        .sh-who { position: relative; min-width: 0; }
        .sh-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .sh-who h1 { display: flex; align-items: center; gap: .4rem; font-size: 1.6rem; font-weight: 900; line-height: 1.15; margin-top: .15rem; }
        .sh-who h1 i { font-size: 1.1rem; color: #a7f3d0; }
        .sh-who p { font-size: .82rem; opacity: .9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sh-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .55rem; }
        .sh-chips span { display: inline-flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .25); }
        .sh-head-actions { position: relative; align-self: start; }
        .sh-search { position: relative; grid-column: 1 / -1; display: flex; align-items: center; gap: .6rem; margin-top: .8rem; height: 3rem; padding: 0 1rem; border-radius: 1rem; background: #fff; color: #94a3b8; box-shadow: 0 14px 30px -18px rgba(15, 23, 42, .7); }
        .sh-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; font-size: .9rem; color: #0f172a; padding: 0; }
        .sh-search input:focus { box-shadow: none; }
        .dark .sh-search { background: #0f172a; }
        .dark .sh-search input { color: #f1f5f9; }
        .sh-todo { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: .6rem; }
        .sh-todo-item { display: flex; align-items: center; gap: .7rem; padding: .7rem .85rem; border-radius: 1rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .sh-todo-item > span:first-child { flex: none; display: grid; place-items: center; width: 2.2rem; height: 2.2rem; border-radius: .75rem; background: #fef3c7; color: #d97706; }
        .sh-todo-item > span:nth-child(2) { flex: 1; }
        .sh-todo-item b { display: block; font-size: .82rem; }
        .sh-todo-item small { display: block; font-size: .7rem; opacity: .85; }
        .dark .sh-todo-item { background: rgba(245, 158, 11, .08); border-color: rgba(245, 158, 11, .3); color: #fcd34d; }
        .dark .sh-todo-item > span:first-child { background: rgba(245, 158, 11, .15); color: #fbbf24; }
        .sh-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start; }
        .sh-group { padding: .9rem; border-radius: 1.3rem; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -18px rgba(15, 23, 42, .25); }
        .dark .sh-group { background: #0f172a; border-color: #1e293b; box-shadow: none; }
        .sh-group-head { display: flex; align-items: center; gap: .45rem; padding: .1rem .35rem .6rem; font-size: .72rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: #64748b; }
        .sh-group-head i { color: var(--color-indigo-500); font-size: .9rem; }
        .dark .sh-group-head { color: #94a3b8; }
        .sh-list { display: flex; flex-direction: column; gap: .2rem; }
        .sh-item { display: flex; align-items: center; gap: .75rem; padding: .6rem .55rem; border-radius: 1rem; transition: background .15s, transform .15s; }
        .sh-item:hover { background: #f8fafc; }
        .dark .sh-item:hover { background: #111827; }
        .sh-ico { flex: none; display: grid; place-items: center; width: 2.6rem; height: 2.6rem; border-radius: .9rem; color: #fff; font-size: 1.1rem; box-shadow: 0 8px 18px -10px rgba(15, 23, 42, .55); }
        .sh-txt { flex: 1; min-width: 0; }
        .sh-txt b { display: block; font-size: .9rem; color: #0f172a; }
        .sh-txt small { display: block; font-size: .74rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .sh-txt b { color: #f1f5f9; }
        .dark .sh-txt small { color: #94a3b8; }
        .sh-badge { flex: none; font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 999px; background: #f1f5f9; color: #64748b; white-space: nowrap; }
        .sh-badge.is-ok { background: #ecfdf5; color: #047857; }
        .sh-badge.is-warn { background: #fffbeb; color: #b45309; }
        .dark .sh-badge { background: #1e293b; color: #94a3b8; }
        .dark .sh-badge.is-ok { background: rgba(16, 185, 129, .12); color: #6ee7b7; }
        .dark .sh-badge.is-warn { background: rgba(245, 158, 11, .12); color: #fcd34d; }
        .sh-go { flex: none; color: #cbd5e1; }
        .dark .sh-go { color: #475569; }
        .sh-empty { padding: 2rem; text-align: center; font-size: .86rem; color: #64748b; }
        @media (min-width: 1700px) { .sh-groups { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 1023px) { .sh-groups { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 767px) {
            .sh-hero { grid-template-columns: auto minmax(0, 1fr) auto; gap: .3rem .8rem; padding: 1.1rem 1rem 1rem; border-radius: 1.3rem; }
            .sh-av { width: 3.4rem; height: 3.4rem; border-radius: 1rem; font-size: 1.1rem; }
            .sh-who h1 { font-size: 1.2rem; }
            .sh-chips { display: none; }
            .sh-search { height: 2.75rem; margin-top: .6rem; }
            .sh-search input { font-size: .84rem; }
            .sh-group { padding: .55rem; border-radius: 1.1rem; }
            .sh-item { padding: .55rem .45rem; gap: .65rem; }
            .sh-ico { width: 2.35rem; height: 2.35rem; font-size: 1rem; border-radius: .8rem; }
            .sh-txt b { font-size: .86rem; }
            .sh-badge { max-width: 7.5rem; overflow: hidden; text-overflow: ellipsis; }
        }
    </style>
    </x-settings.layout>
</section>
