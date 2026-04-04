<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
}; ?>

<section class="settings-security-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

        @php
            $user = Auth::user();
            $hasVerifiedEmail = $user->hasVerifiedEmail();
            $hasPassword      = !empty($user->password);
            $hasPhone         = !empty($user->phone);
            $hasAvatar        = !empty($user->profile_picture);
            $hasGoogleId      = !empty($user->google_id);
            $score = collect([$hasVerifiedEmail, $hasPassword, $hasPhone, $hasAvatar])->filter()->count();
            $scorePercent = intval($score / 4 * 100);
            $scoreColor = $scorePercent >= 75 ? '#10b981' : ($scorePercent >= 50 ? '#f59e0b' : '#ef4444');
            $scoreLabel = $scorePercent >= 75 ? 'Boa' : ($scorePercent >= 50 ? 'Razoável' : 'Fraca');
            $memberDays = $user->created_at ? $user->created_at->diffInDays(now()) : 0;
            $memberMonths = $user->created_at ? $user->created_at->diffInMonths(now()) : 0;
        @endphp

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- ── CARD: Pontuação de segurança ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Pontuação de segurança</p>
                        <p class="settings-card-desc">Com base nas configurações ativas da sua conta</p>
                    </div>
                </div>
                <span class="s-badge" style="background:{{ $scoreColor }}22;color:{{ $scoreColor }}">{{ $scoreLabel }}</span>
            </div>

            <div class="settings-sec-score-wrap" style="display:flex;align-items:center;gap:2rem;padding:1.25rem 1.5rem">
                {{-- Anel de progresso SVG --}}
                <div style="position:relative;width:5rem;height:5rem;flex-shrink:0">
                    <svg width="80" height="80" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="34" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                        <circle cx="40" cy="40" r="34" fill="none" stroke="{{ $scoreColor }}" stroke-width="8"
                            stroke-dasharray="{{ round(2 * M_PI * 34 * $scorePercent / 100) }} {{ round(2 * M_PI * 34) }}"
                            stroke-linecap="round"
                            transform="rotate(-90 40 40)"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <span style="font-size:1.1rem;font-weight:800;color:{{ $scoreColor }}">{{ $scorePercent }}%</span>
                    </div>
                </div>

                {{-- Checklist --}}
                <div class="settings-sec-checklist" style="display:flex;flex-direction:column;gap:0.55rem;flex:1">
                    <div class="settings-sec-check {{ $hasVerifiedEmail ? 'check-ok' : 'check-warn' }}">
                        @if($hasVerifiedEmail)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#10b981" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#f59e0b" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        @endif
                        <span style="font-size:0.82rem">E-mail {{ $hasVerifiedEmail ? 'verificado' : 'não verificado' }}</span>
                    </div>
                    <div class="settings-sec-check {{ $hasPassword ? 'check-ok' : 'check-warn' }}">
                        @if($hasPassword)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#10b981" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#f59e0b" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        @endif
                        <span style="font-size:0.82rem">Senha {{ $hasPassword ? 'cadastrada' : 'não definida' }}</span>
                    </div>
                    <div class="settings-sec-check {{ $hasPhone ? 'check-ok' : 'check-warn' }}">
                        @if($hasPhone)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#10b981" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#94a3b8" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        @endif
                        <span style="font-size:0.82rem">Telefone de recuperação {{ $hasPhone ? 'adicionado' : 'não adicionado' }}</span>
                    </div>
                    <div class="settings-sec-check check-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#94a3b8" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v-.008H12v.008zm-7.5-7.5A9 9 0 1 1 21 12A9 9 0 0 1 4.5 4.5z"/></svg>
                        <span style="font-size:0.82rem;color:#94a3b8">Autenticação em dois fatores — <em>Em breve</em></span>
                    </div>
                </div>
            </div>

            @if(!$hasVerifiedEmail)
            <div class="settings-flash settings-flash--warning" style="display:flex;align-items:center;gap:0.65rem">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b" style="width:1rem;height:1rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <span style="font-size:0.8rem;flex:1">Seu e-mail ainda não foi verificado. Verifique sua caixa de entrada.</span>
                <a href="{{ route('settings.profile') }}" wire:navigate style="font-size:0.78rem;font-weight:700;color:#f59e0b;white-space:nowrap">Verificar agora →</a>
            </div>
            @endif
        </div>

        {{-- ── CARD: Informações da conta ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Informações da conta</p>
                        <p class="settings-card-desc">Dados internos e histórico de registro</p>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.75rem;padding:0 1.25rem 1.25rem">
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">ID da conta</p>
                    <p class="settings-info-tile-val">#{{ $user->id }}</p>
                </div>
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">Membro desde</p>
                    <p class="settings-info-tile-val">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</p>
                </div>
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">Tempo de conta</p>
                    <p class="settings-info-tile-val">{{ $memberMonths > 0 ? $memberMonths . ' mes' . ($memberMonths > 1 ? 'es' : '') : $memberDays . ' dias' }}</p>
                </div>
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">E-mail verificado</p>
                    <p class="settings-info-tile-val" style="color:{{ $hasVerifiedEmail ? '#10b981' : '#f59e0b' }}">
                        {{ $hasVerifiedEmail ? ($user->email_verified_at->format('d/m/Y')) : 'Pendente' }}
                    </p>
                </div>
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">Login social</p>
                    <p class="settings-info-tile-val">{{ $hasGoogleId ? 'Google conectado' : 'Não conectado' }}</p>
                </div>
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">Última atualização</p>
                    <p class="settings-info-tile-val">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '—' }}</p>
                </div>
            </div>
        </div>

        {{-- ── CARD: 2FA (coming soon) ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,0.1);color:#6366f1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Autenticação em dois fatores (2FA)</p>
                        <p class="settings-card-desc">Adicione uma camada extra de proteção com código via SMS ou app</p>
                    </div>
                </div>
                <span class="s-badge s-badge-warning">Em breve</span>
            </div>

            <div style="padding:0 1.5rem 1.5rem;display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem">
                @foreach([
                    ['SMS', 'Código via mensagem de texto', '#10b981'],
                    ['App Autenticador', 'Google Authenticator / Authy', '#6366f1'],
                    ['E-mail', 'Código enviado ao seu e-mail', '#f59e0b'],
                ] as [$method, $desc, $c])
                <div style="padding:0.9rem;border-radius:0.65rem;border:1.5px dashed #e2e8f0;opacity:0.6;text-align:center">
                    <div style="width:2rem;height:2rem;border-radius:50%;background:{{ $c }}1a;color:{{ $c }};display:flex;align-items:center;justify-content:center;margin:0 auto 0.45rem">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/></svg>
                    </div>
                    <p style="font-size:0.78rem;font-weight:700;margin-bottom:0.2rem">{{ $method }}</p>
                    <p style="font-size:0.7rem;color:#94a3b8">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── CARD: Sessão atual ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Sessão atual</p>
                        <p class="settings-card-desc">Dispositivo e navegador autenticado agora</p>
                    </div>
                </div>
                <span class="s-badge s-badge-success">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" fill="currentColor" style="width:0.4rem;height:0.4rem"><circle cx="3" cy="3" r="3"/></svg>
                    Ativa
                </span>
            </div>

            <div style="padding:0 1.25rem 1.25rem">
                <div class="settings-session-item" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:0.85rem 1rem;border-radius:0.75rem;background:rgba(var(--s-accent-rgb),0.04);border:1.5px solid rgba(var(--s-accent-rgb),0.12)">
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:2.5rem;height:2.5rem;border-radius:0.6rem;background:rgba(var(--s-accent-rgb),0.1);display:flex;align-items:center;justify-content:center;color:var(--s-accent);flex-shrink:0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.25rem;height:1.25rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>
                        </div>
                        <div>
                            <p style="font-size:0.85rem;font-weight:700">Este dispositivo</p>
                            <p style="font-size:0.75rem;color:#64748b">Sessão iniciada agora · IP {{ request()->ip() }}</p>
                        </div>
                    </div>
                    <span class="s-badge s-badge-accent">Você está aqui</span>
                </div>
            </div>
        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        {{-- ── CARD: Dados e privacidade ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 8.25-7.5 11.625-7.5 11.625S5.25 14.625 5.25 6.375a7.5 7.5 0 0 1 15 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Privacidade e dados</p>
                        <p class="settings-card-desc">Gerencie suas informações pessoais armazenadas</p>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem;padding:0 1.25rem 1.25rem">
                <button type="button"
                    onclick="alert('Exportação de dados disponível em breve!')"
                    style="display:flex;align-items:center;gap:0.6rem;padding:0.75rem 1rem;border-radius:0.65rem;border:1.5px solid rgba(var(--s-accent-rgb),0.2);background:rgba(var(--s-accent-rgb),0.04);color:var(--s-accent);font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.18s;text-align:left"
                    onmouseover="this.style.background='rgba(var(--s-accent-rgb),0.1)'"
                    onmouseout="this.style.background='rgba(var(--s-accent-rgb),0.04)'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exportar meus dados
                </button>
                <a href="{{ route('settings.profile') }}" wire:navigate
                    style="display:flex;align-items:center;gap:0.6rem;padding:0.75rem 1rem;border-radius:0.65rem;border:1.5px solid #e2e8f0;background:white;color:#475569;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.18s;text-decoration:none"
                    onmouseover="this.style.background='#f8fafc'"
                    onmouseout="this.style.background='white'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                    Editar perfil
                </a>
                <a href="{{ route('settings.password') }}" wire:navigate
                    style="display:flex;align-items:center;gap:0.6rem;padding:0.75rem 1rem;border-radius:0.65rem;border:1.5px solid #e2e8f0;background:white;color:#475569;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.18s;text-decoration:none"
                    onmouseover="this.style.background='#f8fafc'"
                    onmouseout="this.style.background='white'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/></svg>
                    Mudar senha
                </a>
            </div>
        </div>

        {{-- CARD: Histórico de atividades de segurança --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Histórico de atividades</p>
                        <p class="settings-card-desc">Auditoria dos eventos recentes na conta</p>
                    </div>
                </div>
            </div>
            @php
                $secEvents = [
                    ['Login bem-sucedido','Acesso via web','Agora mesmo','success','M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6'],
                    ['Perfil atualizado','Dados pessoais alterados','Hoje','info','m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125'],
                    ['Configurações salvas','Notificações configuradas','Ontem','info','M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281'],
                    ['Login bem-sucedido','Acesso via web','2 dias atrás','success','M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6'],
                    ['Senha não alterada','Último check de segurança','7 dias atrás','warning','M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75'],
                ];
                $secColors = ['success'=>'#10b981','info'=>'#6366f1','warning'=>'#f59e0b','danger'=>'#ef4444'];
                $secBg     = ['success'=>'rgba(16,185,129,.1)','info'=>'rgba(99,102,241,.1)','warning'=>'rgba(245,158,11,.1)','danger'=>'rgba(239,68,68,.1)'];
            @endphp
            <div style="padding:0 1.25rem 1.25rem;position:relative">
                {{-- Timeline --}}
                <div style="position:relative;padding-left:1.5rem">
                    <div style="position:absolute;left:.45rem;top:.6rem;bottom:.6rem;width:2px;background:linear-gradient(to bottom,rgba(var(--s-accent-rgb),.2),rgba(var(--s-accent-rgb),.05))"></div>
                    @foreach($secEvents as [$title,$desc,$when,$type,$icon])
                    <div style="position:relative;display:flex;gap:.75rem;align-items:flex-start;padding:.6rem 0">
                        <div style="position:absolute;left:-1.5rem;width:.8rem;height:.8rem;border-radius:50%;background:{{ $secBg[$type] }};border:2px solid {{ $secColors[$type] }};flex-shrink:0;margin-top:.15rem"></div>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
                                <p style="font-size:.83rem;font-weight:700;color:#1e293b;margin:0">{{ $title }}</p>
                                <span style="font-size:.72rem;color:#94a3b8">{{ $when }}</span>
                            </div>
                            <p style="font-size:.75rem;color:#64748b;margin:.1rem 0 0">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p style="font-size:.72rem;color:#94a3b8;margin:.5rem 0 0;text-align:center">Exibindo os últimos 5 eventos · Histórico completo em breve</p>
            </div>
        </div>

        {{-- CARD: Dicas de segurança --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Boas práticas de segurança</p>
                        <p class="settings-card-desc">Recomendações para manter sua conta protegida</p>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;padding:0 1.25rem 1.25rem">
                @foreach([
                    ['Use senhas únicas','Nunca reutilize senhas em sites diferentes','#10b981'],
                    ['Ative 2FA','Autenticação em dois fatores protege contra invasões','#6366f1'],
                    ['Revise dispositivos','Verifique regularmente os dispositivos com acesso','#f59e0b'],
                    ['E-mail verificado','Mantenha o e-mail verificado para recuperar a conta','#3b82f6'],
                ] as [$tip,$tipDesc,$tipCol])
                <div class="settings-info-tile" style="background:{{ $tipCol }}10;border-color:{{ $tipCol }}25">
                    <div style="color:{{ $tipCol }};margin-bottom:.4rem">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:.9rem;height:.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </div>
                    <p class="settings-info-tile-label" style="color:{{ $tipCol }}">{{ $tip }}</p>
                    <p style="font-size:.73rem;color:#64748b;margin:.2rem 0 0">{{ $tipDesc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="settings-card" style="background:linear-gradient(135deg,rgba(var(--s-accent-rgb),0.05),transparent)">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(14,165,233,.12);color:#0284c7">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6 2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Ações recomendadas agora</p>
                        <p class="settings-card-desc">Próximos passos mais impactantes para fortalecer sua conta</p>
                    </div>
                </div>
                <span class="s-badge s-badge-accent">Checklist</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;padding:0 1.25rem 1.25rem">
                <a href="{{ route('settings.profile') }}" wire:navigate class="settings-info-tile" style="text-decoration:none;background:{{ $hasVerifiedEmail ? 'rgba(16,185,129,.08)' : 'rgba(245,158,11,.08)' }}">
                    <p class="settings-info-tile-label">E-mail</p>
                    <p class="settings-info-tile-val" style="color:{{ $hasVerifiedEmail ? '#10b981' : '#f59e0b' }}">{{ $hasVerifiedEmail ? 'Tudo certo' : 'Verificar agora' }}</p>
                    <p style="font-size:.74rem;color:#64748b;margin:.25rem 0 0">{{ $hasVerifiedEmail ? 'Seu e-mail já pode ser usado para recuperação.' : 'Sem verificação, o processo de recuperação fica mais limitado.' }}</p>
                </a>
                <a href="{{ route('settings.password') }}" wire:navigate class="settings-info-tile" style="text-decoration:none;background:rgba(99,102,241,.08)">
                    <p class="settings-info-tile-label">Senha e acesso</p>
                    <p class="settings-info-tile-val" style="color:#4f46e5">Revisar credenciais</p>
                    <p style="font-size:.74rem;color:#64748b;margin:.25rem 0 0">Troque sua senha regularmente e use uma combinação longa e exclusiva.</p>
                </a>
                <a href="{{ route('settings.devices') }}" wire:navigate class="settings-info-tile" style="text-decoration:none;background:rgba(14,165,233,.08)">
                    <p class="settings-info-tile-label">Dispositivos</p>
                    <p class="settings-info-tile-val" style="color:#0284c7">Auditar sessões</p>
                    <p style="font-size:.74rem;color:#64748b;margin:.25rem 0 0">Confira acessos antigos e revogue sessões que você não reconhece.</p>
                </a>
            </div>
        </div>

        </div>{{-- /s-col-side --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
