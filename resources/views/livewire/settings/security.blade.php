<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
}; ?>

@php
    // Segurança: checkup da conta só com o que existe de verdade (cada item leva para onde se resolve).
    $user = Auth::user();
    $hasVerifiedEmail = $user->hasVerifiedEmail();
    $hasPassword = ! empty($user->password);
    $hasPhone = ! empty($user->phone);
    $hasAvatar = ! empty($user->profile_picture);
    $hasGoogle = ! empty($user->google_id);
    $score = collect([$hasVerifiedEmail, $hasPassword, $hasPhone, $hasAvatar])->filter()->count();
    $pct = (int) ($score / 4 * 100);
    [$tone, $label, $msg] = $pct >= 75
        ? ['ok', 'Boa', 'Sua conta está bem protegida.']
        : ($pct >= 50 ? ['warn', 'Razoável', 'Faltam poucos passos para deixar a conta segura.'] : ['bad', 'Fraca', 'Resolva os itens abaixo para proteger a conta.']);

    $sessions = null;
    try {
        if (config('session.driver') === 'database') {
            $sessions = \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->count();
        }
    } catch (\Throwable $e) {
        $sessions = null;
    }

    $checks = [
        [$hasVerifiedEmail, 'E-mail confirmado', $hasVerifiedEmail ? 'Dá para recuperar a conta pelo e-mail.' : 'Sem confirmar, não dá para recuperar a conta se esquecer a senha.', route('settings.profile') . '#acc-contato', 'Confirmar', 'bi-envelope-check'],
        [$hasPassword, 'Senha cadastrada', $hasPassword ? 'Troque de tempos em tempos e não use em outros sites.' : 'Crie uma senha para entrar sem depender do Google.', route('settings.password'), $hasPassword ? 'Trocar' : 'Criar', 'bi-key'],
        [$hasPhone, 'Telefone de contato', $hasPhone ? $user->phone : 'Ajuda a confirmar que é você.', route('settings.profile') . '#acc-contato', 'Adicionar', 'bi-telephone'],
        [$hasAvatar, 'Foto do perfil', $hasAvatar ? 'Sua equipe reconhece você no app.' : 'Facilita reconhecer a conta certa.', route('settings.profile') . '#acc-photo', 'Colocar', 'bi-camera'],
    ];
@endphp

<section class="settings-security-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="sc-page">
        {{-- Topo: nota de segurança --}}
        <div class="sc-hero is-{{ $tone }}">
            <span class="sc-glow"></span>
            <div class="sc-ring" style="--pct: {{ $pct }}"><b>{{ $pct }}%</b></div>
            <div class="sc-hero-txt">
                <span class="sc-kicker"><i class="bi bi-shield-check"></i> Nota de segurança · {{ $label }}</span>
                <h2>{{ $msg }}</h2>
                <p>{{ $score }} de 4 itens em dia. Cada item resolvido sobe a nota.</p>
            </div>
        </div>

        <div class="pl-grid">
            <div class="pl-main">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-clipboard2-check"></i></span>
                        <div class="min-w-0">
                            <h2>Checkup da conta</h2>
                            <p>Toque num item pendente para resolver.</p>
                        </div>
                    </div>
                    <div class="sc-checks">
                        @foreach($checks as [$ok, $title, $desc, $href, $cta, $icon])
                            <a href="{{ $href }}" wire:navigate class="sc-check {{ $ok ? 'is-ok' : '' }}">
                                <span class="sc-check-ico"><i class="bi {{ $ok ? 'bi-check-lg' : $icon }}"></i></span>
                                <span class="min-w-0">
                                    <b>{{ $title }}</b>
                                    <small>{{ $desc }}</small>
                                </span>
                                @if($ok)
                                    <span class="sc-tag">Em dia</span>
                                @else
                                    <span class="sc-cta">{{ $cta }} <i class="bi bi-arrow-right"></i></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-door-open"></i></span>
                        <div class="min-w-0">
                            <h2>Como você entra</h2>
                            <p>Formas de login e aparelhos conectados.</p>
                        </div>
                    </div>
                    <div class="sc-ways">
                        <div class="sc-way {{ $hasPassword ? 'is-on' : '' }}">
                            <span class="sc-way-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-key"></i></span>
                            <b>E-mail e senha</b>
                            <small>{{ $hasPassword ? 'Ativo' : 'Sem senha' }}</small>
                        </div>
                        <div class="sc-way {{ $hasGoogle ? 'is-on' : '' }}">
                            <span class="sc-way-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-google"></i></span>
                            <b>Google</b>
                            <small>{{ $hasGoogle ? 'Vinculado' : 'Não vinculado' }}</small>
                        </div>
                        <a href="{{ route('settings.devices') }}" wire:navigate class="sc-way is-on">
                            <span class="sc-way-ico bg-gradient-to-br from-violet-500 to-purple-600"><i class="bi bi-phone"></i></span>
                            <b>Aparelhos</b>
                            <small>{{ $sessions ? $sessions . ' ' . ($sessions === 1 ? 'conectado' : 'conectados') : 'Ver onde está aberta' }}</small>
                        </a>
                    </div>
                    <div class="sc-facts">
                        <div><small>Conta criada em</small><b>{{ $user->created_at?->format('d/m/Y') ?? '—' }}</b></div>
                        <div><small>E-mail</small><b>{{ $user->email }}</b></div>
                        <div><small>Este acesso</small><b>IP {{ request()->ip() }}</b></div>
                    </div>
                </div>
            </div>

            <aside class="pl-side">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-lightbulb"></i></span>
                        <div class="min-w-0">
                            <h2>Dicas rápidas</h2>
                            <p>O que mais protege uma conta.</p>
                        </div>
                    </div>
                    <ul class="sc-tips">
                        <li><i class="bi bi-check2-circle"></i> Use uma senha só para o FlowManager, com 12 letras ou mais.</li>
                        <li><i class="bi bi-check2-circle"></i> Usou o computador de outra pessoa? Encerre a sessão em Dispositivos.</li>
                        <li><i class="bi bi-check2-circle"></i> Dê para a equipe só as permissões que cada pessoa precisa.</li>
                        <li><i class="bi bi-check2-circle"></i> Nunca mande chaves e senhas por mensagem; cole só nas telas de Configurações.</li>
                    </ul>
                </div>

                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-slate-500 to-slate-700"><i class="bi bi-lightning"></i></span>
                        <div class="min-w-0">
                            <h2>Atalhos</h2>
                            <p>Outras telas da conta.</p>
                        </div>
                    </div>
                    <div class="acc-links">
                        <a href="{{ route('settings.password') }}" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-key"></i></span>
                            <span class="min-w-0"><b>Trocar a senha</b><small>Senha e login</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                        <a href="{{ route('settings.devices') }}" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-phone"></i></span>
                            <span class="min-w-0"><b>Dispositivos</b><small>Encerrar sessões abertas</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                        <a href="{{ route('settings.team') }}" wire:navigate class="acc-link">
                            <span class="acc-link-ico bg-gradient-to-br from-blue-500 to-indigo-600"><i class="bi bi-people"></i></span>
                            <span class="min-w-0"><b>Permissões da equipe</b><small>Quem vê o quê</small></span>
                            <i class="bi bi-chevron-right acc-link-go"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .sc-page { display: flex; flex-direction: column; gap: 1rem; }
        .pl-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .pl-main, .pl-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .sc-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .sc-hero.is-ok { background: linear-gradient(135deg, #059669, #0d9488 55%, #0891b2); }
        .sc-hero.is-warn { background: linear-gradient(135deg, #d97706, #ea580c 60%, #e11d48); }
        .sc-hero.is-bad { background: linear-gradient(135deg, #e11d48, #db2777 55%, #9333ea); }
        .sc-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .sc-ring { position: relative; flex: none; display: grid; place-items: center; width: 5.2rem; height: 5.2rem; border-radius: 999px; background: conic-gradient(#fff calc(var(--pct) * 1%), rgba(255, 255, 255, .25) 0); }
        .sc-ring::before { content: ''; position: absolute; inset: .45rem; border-radius: 999px; background: rgba(15, 23, 42, .28); }
        .sc-ring b { position: relative; font-size: 1.3rem; font-weight: 900; }
        .sc-hero-txt { position: relative; flex: 1; min-width: 0; }
        .sc-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .sc-hero-txt h2 { font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .sc-hero-txt p { font-size: .82rem; opacity: .92; }
        .sc-checks { display: flex; flex-direction: column; gap: .45rem; }
        .sc-check { display: flex; align-items: center; gap: .75rem; padding: .7rem .8rem; border-radius: 1rem; background: #fffbeb; border: 1px solid #fde68a; }
        .sc-check.is-ok { background: #f8fafc; border-color: #eef2f7; }
        .dark .sc-check { background: rgba(245, 158, 11, .07); border-color: rgba(245, 158, 11, .28); }
        .dark .sc-check.is-ok { background: #111827; border-color: #1f2937; }
        .sc-check-ico { flex: none; display: grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: .8rem; background: #fef3c7; color: #d97706; font-size: 1.05rem; }
        .sc-check.is-ok .sc-check-ico { background: #d1fae5; color: #059669; }
        .dark .sc-check-ico { background: rgba(245, 158, 11, .15); color: #fbbf24; }
        .dark .sc-check.is-ok .sc-check-ico { background: rgba(16, 185, 129, .15); color: #6ee7b7; }
        .sc-check > span:nth-child(2) { flex: 1; }
        .sc-check b { display: block; font-size: .86rem; color: #0f172a; }
        .sc-check small { display: block; font-size: .74rem; color: #64748b; }
        .dark .sc-check b { color: #f1f5f9; }
        .dark .sc-check small { color: #94a3b8; }
        .sc-tag { flex: none; font-size: .68rem; font-weight: 800; color: #047857; background: #ecfdf5; padding: .25rem .6rem; border-radius: 999px; }
        .dark .sc-tag { background: rgba(16, 185, 129, .12); color: #6ee7b7; }
        .sc-cta { flex: none; display: inline-flex; align-items: center; gap: .3rem; font-size: .76rem; font-weight: 800; color: #fff; background: #f59e0b; padding: .4rem .75rem; border-radius: .75rem; }
        .sc-ways { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .55rem; }
        .sc-way { display: flex; flex-direction: column; align-items: flex-start; gap: .2rem; padding: .85rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; opacity: .7; }
        .sc-way.is-on { opacity: 1; background: #fff; border-color: #e2e8f0; }
        .dark .sc-way { background: #111827; border-color: #1f2937; }
        .sc-way-ico { display: grid; place-items: center; width: 2.2rem; height: 2.2rem; border-radius: .75rem; color: #fff; margin-bottom: .3rem; }
        .sc-way b { font-size: .84rem; color: #0f172a; }
        .sc-way small { font-size: .72rem; color: #64748b; }
        .dark .sc-way b { color: #f1f5f9; }
        .dark .sc-way small { color: #94a3b8; }
        .sc-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .55rem; margin-top: .9rem; padding-top: .9rem; border-top: 1px dashed #e2e8f0; }
        .dark .sc-facts { border-color: #1e293b; }
        .sc-facts small { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
        .sc-facts b { display: block; font-size: .82rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .sc-facts b { color: #f1f5f9; }
        .sc-tips { display: flex; flex-direction: column; gap: .55rem; font-size: .8rem; color: #334155; }
        .sc-tips li { display: flex; gap: .5rem; }
        .sc-tips i { color: #10b981; margin-top: .1rem; }
        .dark .sc-tips { color: #cbd5e1; }
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
        @media (max-width: 1279px) { .pl-grid { grid-template-columns: minmax(0, 1fr) 300px; } }
        @media (max-width: 1023px) { .pl-grid { grid-template-columns: minmax(0, 1fr); } .pl-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .sc-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .sc-ring { width: 4.2rem; height: 4.2rem; }
            .sc-ring b { font-size: 1.05rem; }
            .sc-hero-txt h2 { font-size: 1.1rem; }
            .pl-side { display: flex; }
            .sc-ways, .sc-facts { grid-template-columns: minmax(0, 1fr); }
            .sc-way { flex-direction: row; align-items: center; gap: .65rem; }
            .sc-way-ico { margin: 0; }
            .sc-way small { margin-left: auto; }
            .sc-cta { padding: .4rem .55rem; }
        }
    </style>
    </x-settings.layout>
</section>
