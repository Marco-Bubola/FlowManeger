<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public ?string $flash = null;

    /** As sessões só ficam guardadas no banco quando o driver de sessão é "database". */
    public function sessionsAvailable(): bool
    {
        return config('session.driver') === 'database' && rescue(fn () => Schema::hasTable(config('session.table', 'sessions')), false, false);
    }

    public function endSession(string $id): void
    {
        if (! $this->sessionsAvailable() || $id === session()->getId()) return;
        DB::table(config('session.table', 'sessions'))->where('user_id', Auth::id())->where('id', $id)->delete();
        $this->flash = 'Sessão encerrada. Aquele aparelho vai pedir login de novo.';
    }

    public function endOtherSessions(): void
    {
        if (! $this->sessionsAvailable()) return;
        DB::table(config('session.table', 'sessions'))->where('user_id', Auth::id())->where('id', '!=', session()->getId())->delete();
        // Quem marcou "lembrar de mim" em outro aparelho também precisa entrar de novo
        $user = Auth::user();
        $user->setRememberToken(Str::random(60));
        $user->save();
        $this->flash = 'Pronto: só este aparelho continua conectado.';
    }
}; ?>

@php
    $describe = function (?string $ua) {
        $ua = (string) $ua;
        $l = strtolower($ua);
        $type = (str_contains($l, 'ipad') || str_contains($l, 'tablet')) ? 'tablet'
            : ((str_contains($l, 'mobile') || str_contains($l, 'iphone') || str_contains($l, 'android')) ? 'phone' : 'desktop');
        $browser = 'Navegador';
        if (str_contains($ua, 'Edg')) $browser = 'Edge';
        elseif (str_contains($ua, 'OPR') || str_contains($ua, 'Opera')) $browser = 'Opera';
        elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($ua, 'Chrome') || str_contains($ua, 'CriOS')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
        $os = 'Sistema desconhecido';
        if (str_contains($ua, 'iPhone')) $os = 'iPhone';
        elseif (str_contains($ua, 'iPad')) $os = 'iPad';
        elseif (str_contains($ua, 'Android')) $os = 'Android';
        elseif (str_contains($ua, 'Windows')) $os = 'Windows';
        elseif (str_contains($ua, 'Macintosh')) $os = 'Mac';
        elseif (str_contains($ua, 'Linux')) $os = 'Linux';
        $icon = ['phone' => 'bi-phone', 'tablet' => 'bi-tablet', 'desktop' => 'bi-laptop'][$type];
        $label = ['phone' => 'Celular', 'tablet' => 'Tablet', 'desktop' => 'Computador'][$type];
        return compact('type', 'browser', 'os', 'icon', 'label');
    };

    $current = $describe(request()->userAgent());
    $currentId = session()->getId();
    $available = $this->sessionsAvailable();
    $sessions = collect();
    if ($available) {
        $sessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(function ($s) use ($describe, $currentId) {
                $d = $describe($s->user_agent);
                $d['id'] = $s->id;
                $d['ip'] = $s->ip_address;
                $d['last'] = \Illuminate\Support\Carbon::createFromTimestamp($s->last_activity);
                $d['current'] = $s->id === $currentId;
                $d['online'] = $d['last']->gt(now()->subMinutes(5));
                return $d;
            });
    }
    $others = $sessions->where('current', false)->count();
@endphp

<section class="settings-devices-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="dv-page">
        @if($flash)
            <div class="pl-flash"><i class="bi bi-check-circle"></i> {{ $flash }}</div>
        @endif

        <div class="dv-hero bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <span class="dv-hero-glow"></span>
            <div class="dv-hero-ico"><i class="bi {{ $current['icon'] }}"></i></div>
            <div class="dv-hero-txt">
                <span class="pl-kicker"><i class="bi bi-circle-fill dv-live"></i> Você está usando agora</span>
                <h2>{{ $current['label'] }} · {{ $current['os'] }}</h2>
                <p>{{ $current['browser'] }} · IP {{ request()->ip() }} · desde {{ now()->format('H:i') }}</p>
            </div>
            <div class="dv-hero-count">
                <b>{{ max(1, $sessions->count()) }}</b>
                <small>{{ max(1, $sessions->count()) === 1 ? 'aparelho conectado' : 'aparelhos conectados' }}</small>
            </div>
        </div>

        <div class="pl-grid">
            <div class="pl-main">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-pc-display-horizontal"></i></span>
                        <div class="min-w-0">
                            <h2>Onde sua conta está aberta</h2>
                            <p>Se aparecer um aparelho que você não conhece, encerre a sessão e troque a senha.</p>
                        </div>
                    </div>

                    @if(! $available || $sessions->isEmpty())
                        <div class="dv-row is-current">
                            <span class="dv-ico"><i class="bi {{ $current['icon'] }}"></i></span>
                            <div class="min-w-0">
                                <b>{{ $current['label'] }} · {{ $current['os'] }}</b>
                                <small>{{ $current['browser'] }} · {{ request()->ip() }}</small>
                            </div>
                            <span class="dv-badge">Este aparelho</span>
                        </div>
                        @unless($available)
                            <p class="dv-note"><i class="bi bi-info-circle"></i> A lista dos outros aparelhos aparece quando as sessões ficam guardadas no banco de dados.</p>
                        @endunless
                    @else
                        <div class="dv-list">
                            @foreach($sessions as $s)
                                <div class="dv-row {{ $s['current'] ? 'is-current' : '' }}" wire:key="sess-{{ $s['id'] }}">
                                    <span class="dv-ico"><i class="bi {{ $s['icon'] }}"></i>@if($s['online'])<em></em>@endif</span>
                                    <div class="min-w-0">
                                        <b>{{ $s['label'] }} · {{ $s['os'] }}</b>
                                        <small>{{ $s['browser'] }} · {{ $s['ip'] ?: 'IP desconhecido' }} · {{ $s['current'] ? 'agora' : ($s['online'] ? 'ativo agora' : 'visto ' . $s['last']->locale('pt_BR')->diffForHumans()) }}</small>
                                    </div>
                                    @if($s['current'])
                                        <span class="dv-badge">Este aparelho</span>
                                    @else
                                        <button type="button" class="dv-end" wire:click="endSession('{{ $s['id'] }}')" wire:confirm="Encerrar a sessão deste aparelho?">
                                            <i class="bi bi-box-arrow-right"></i> <span>Encerrar</span>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($others > 0)
                            <div class="dv-actions">
                                <button type="button" class="dv-end-all" wire:click="endOtherSessions" wire:confirm="Sair da conta em todos os outros aparelhos? Este continua conectado.">
                                    <i class="bi bi-shield-x"></i> Sair de todos os outros aparelhos ({{ $others }})
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <aside class="pl-side">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-shield-check"></i></span>
                        <div class="min-w-0">
                            <h2>Deixe a conta mais segura</h2>
                            <p>Três passos rápidos.</p>
                        </div>
                    </div>
                    <div class="acc-links">
                        <a href="{{ route('settings.password') }}" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-key"></i></span>
                            <span class="min-w-0"><b>Trocar a senha</b><small>Use uma senha que você não usa em outro lugar</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                        <a href="{{ route('settings.security') }}" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-shield-lock"></i></span>
                            <span class="min-w-0"><b>Segurança da conta</b><small>Verificação e alertas</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                        <a href="{{ route('settings.profile') }}#acc-contato" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-envelope-check"></i></span>
                            <span class="min-w-0"><b>Confirmar o e-mail</b><small>Para recuperar a conta se perder a senha</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                    </div>
                </div>
                <div class="ap-card dv-tip">
                    <i class="bi bi-lightbulb"></i>
                    <p>Usou o app no computador de outra pessoa? Encerre aquela sessão aqui mesmo, sem precisar voltar lá.</p>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .dv-page { display: flex; flex-direction: column; gap: 1rem; }
        .pl-flash { display: flex; align-items: center; gap: .5rem; padding: .8rem 1rem; border-radius: 1rem; font-size: .84rem; font-weight: 600; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .pl-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .pl-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .pl-main, .pl-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .dv-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.1rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .dv-hero-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .dv-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); font-size: 1.8rem; border: 1px solid rgba(255, 255, 255, .3); }
        .dv-hero-txt { position: relative; flex: 1; min-width: 0; }
        .dv-hero-txt h2 { font-size: 1.5rem; font-weight: 900; line-height: 1.15; margin-top: .2rem; }
        .dv-hero-txt p { font-size: .82rem; opacity: .9; }
        .dv-live { font-size: .5rem; color: #4ade80; filter: drop-shadow(0 0 4px #4ade80); }
        .dv-hero-count { position: relative; flex: none; text-align: center; padding: .7rem 1.1rem; border-radius: 1.1rem; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); }
        .dv-hero-count b { display: block; font-size: 1.8rem; font-weight: 900; line-height: 1; }
        .dv-hero-count small { font-size: .7rem; opacity: .9; }
        .dv-list { display: flex; flex-direction: column; gap: .45rem; }
        .dv-row { display: flex; align-items: center; gap: .75rem; padding: .7rem .8rem; border-radius: 1rem; background: #f8fafc; border: 1px solid #eef2f7; }
        .dv-row.is-current { background: #ecfdf5; border-color: #a7f3d0; }
        .dark .dv-row { background: #111827; border-color: #1f2937; }
        .dark .dv-row.is-current { background: rgba(16, 185, 129, .08); border-color: rgba(16, 185, 129, .3); }
        .dv-ico { position: relative; flex: none; display: grid; place-items: center; width: 2.6rem; height: 2.6rem; border-radius: .9rem; background: #e2e8f0; color: #475569; font-size: 1.15rem; }
        .dv-row.is-current .dv-ico { background: #10b981; color: #fff; }
        .dark .dv-ico { background: #1f2937; color: #cbd5e1; }
        .dv-ico em { position: absolute; right: -.1rem; top: -.1rem; width: .7rem; height: .7rem; border-radius: 999px; background: #22c55e; border: 2px solid #fff; }
        .dv-row b { display: block; font-size: .86rem; color: #0f172a; }
        .dv-row small { display: block; font-size: .72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .dv-row b { color: #f1f5f9; }
        .dark .dv-row small { color: #94a3b8; }
        .dv-row > div { flex: 1; }
        .dv-badge { flex: none; font-size: .68rem; font-weight: 800; color: #047857; background: #fff; border: 1px solid #a7f3d0; padding: .25rem .6rem; border-radius: 999px; }
        .dark .dv-badge { background: transparent; color: #6ee7b7; border-color: rgba(16, 185, 129, .35); }
        .dv-end { flex: none; display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 800; color: #dc2626; background: #fff; border: 1px solid #fecaca; padding: .4rem .7rem; border-radius: .75rem; }
        .dv-end:hover { background: #fef2f2; }
        .dark .dv-end { background: transparent; border-color: rgba(239, 68, 68, .35); color: #fca5a5; }
        .dv-actions { display: flex; justify-content: flex-end; margin-top: .9rem; padding-top: .9rem; border-top: 1px dashed #e2e8f0; }
        .dark .dv-actions { border-color: #1e293b; }
        .dv-end-all { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 800; color: #fff; background: #ef4444; padding: .6rem 1rem; border-radius: .9rem; box-shadow: 0 10px 20px -12px rgba(239, 68, 68, .8); }
        .dv-note { margin-top: .75rem; font-size: .76rem; color: #64748b; }
        .dv-tip { display: flex; gap: .7rem; align-items: flex-start; background: #fffbeb !important; border-color: #fde68a !important; }
        .dv-tip i { font-size: 1.2rem; color: #d97706; }
        .dv-tip p { font-size: .8rem; color: #92400e; }
        .dark .dv-tip { background: rgba(245, 158, 11, .08) !important; border-color: rgba(245, 158, 11, .3) !important; }
        .dark .dv-tip p { color: #fcd34d; }
        .acc-links { display: flex; flex-direction: column; gap: .3rem; }
        .acc-link { display: flex; align-items: center; gap: .7rem; padding: .55rem .6rem; border-radius: .9rem; transition: background .15s; }
        .acc-link:hover { background: #f8fafc; }
        .dark .acc-link:hover { background: #111827; }
        .acc-link-ico { flex: none; display: grid; place-items: center; width: 2.2rem; height: 2.2rem; border-radius: .75rem; color: #fff; }
        .acc-link b { display: block; font-size: .83rem; color: #0f172a; }
        .acc-link small { display: block; font-size: .7rem; color: #64748b; }
        .dark .acc-link b { color: #f1f5f9; }
        .dark .acc-link small { color: #94a3b8; }
        .acc-link-go { margin-left: auto; color: #cbd5e1; }
        @media (max-width: 1279px) { .pl-grid { grid-template-columns: minmax(0, 1fr) 320px; } }
        @media (max-width: 1023px) { .pl-grid { grid-template-columns: minmax(0, 1fr); } .pl-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .dv-hero { flex-wrap: wrap; padding: 1.1rem; border-radius: 1.3rem; }
            .dv-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.4rem; }
            .dv-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .dv-hero-txt h2 { font-size: 1.2rem; }
            .dv-hero-count { width: 100%; display: flex; align-items: baseline; justify-content: center; gap: .4rem; padding: .5rem; }
            .dv-hero-count b { font-size: 1.2rem; }
            .dv-end span { display: none; }
            .pl-side { display: flex; }
            .dv-end-all { width: 100%; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
