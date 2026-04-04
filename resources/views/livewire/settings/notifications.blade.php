<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
@php
    /** @var array|null $dbNotif */
    $dbNotif = auth()->user()?->preferences['notifications'] ?? null;
@endphp

<section class="settings-notifications-page w-full mobile-393-base"
    x-data="{
        email: {
            sales: true, clients: true, payments: true,
            reports: false, weekly: true, security: true,
            goals: false, habits: false
        },
        system: {
            browser: false, reminders: true, due_dates: true,
            achievements: true, low_stock: false
        },
        frequency: 'instant',
        quiet_from: '22:00',
        quiet_to: '08:00',
        quiet_enabled: false,
        saved: false,
        saveAll() {
            const payload = {
                email: this.email,
                system: this.system,
                frequency: this.frequency,
                quiet_from: this.quiet_from,
                quiet_to: this.quiet_to,
                quiet_enabled: this.quiet_enabled,
            };
            localStorage.setItem('flowmanager:notif', JSON.stringify(payload));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch('/settings/preferences/notifications', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body: JSON.stringify({data: payload})
            }).catch(()=>{});
            this.saved = true;
            setTimeout(() => this.saved = false, 2200);
        }
    }"
    x-init="
        // Carrega do banco (autoritativo entre dispositivos) ou cai no localStorage
        const dbData = @json($dbNotif ?? null);
        if (dbData) {
            if (dbData.email) Object.assign(email, dbData.email);
            if (dbData.system) Object.assign(system, dbData.system);
            if (dbData.frequency) frequency = dbData.frequency;
            if (dbData.quiet_from) quiet_from = dbData.quiet_from;
            if (dbData.quiet_to) quiet_to = dbData.quiet_to;
            if (dbData.quiet_enabled !== undefined) quiet_enabled = dbData.quiet_enabled;
        } else {
            try {
                const s = JSON.parse(localStorage.getItem('flowmanager:notif') || 'null');
                if (s) {
                    if (s.email) Object.assign(email, s.email);
                    if (s.system) Object.assign(system, s.system);
                    if (s.frequency) frequency = s.frequency;
                    if (s.quiet_from) quiet_from = s.quiet_from;
                    if (s.quiet_to) quiet_to = s.quiet_to;
                    if (s.quiet_enabled !== undefined) quiet_enabled = s.quiet_enabled;
                }
            } catch(e){}
        }
    ">

    <x-settings.layout :heading="''">

        {{-- ── BANNER de estado salvo ── --}}
        <div x-show="saved" x-transition.opacity
             style="display:none;position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:0.65rem 1.1rem;border-radius:0.75rem;background:var(--s-accent);color:#fff;font-size:0.82rem;font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,0.18);display:flex;align-items:center;gap:0.4rem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Preferências salvas!
        </div>

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- ── CARD: Email ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Notificações por e-mail</p>
                        <p class="settings-card-desc">Selecione quais eventos geram e-mails para você</p>
                    </div>
                </div>
                <span class="s-badge s-badge-accent">{{ auth()->user()->email }}</span>
            </div>

            <div class="settings-notif-list">

                <div class="settings-notif-group-label">Vendas e finanças</div>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,0.1);color:#10b981">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Nova venda realizada</p>
                            <p class="settings-notif-desc">Confirmação imediata a cada nova venda</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.sales ? 'active' : ''" @click="email.sales = !email.sales; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(239,68,68,0.1);color:#ef4444">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Pagamentos e vencimentos</p>
                            <p class="settings-notif-desc">Alertas de faturas próximas ao vencimento</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.payments ? 'active' : ''" @click="email.payments = !email.payments; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Relatórios automáticos</p>
                            <p class="settings-notif-desc">Envio de relatórios de desempenho gerados</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.reports ? 'active' : ''" @click="email.reports = !email.reports; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <div class="settings-notif-group-label" style="margin-top:1rem">Clientes e equipe</div>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(168,85,247,0.1);color:#a855f7">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Novos clientes cadastrados</p>
                            <p class="settings-notif-desc">Alerta quando um novo cliente é adicionado</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.clients ? 'active' : ''" @click="email.clients = !email.clients; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <div class="settings-notif-group-label" style="margin-top:1rem">Resumos e segurança</div>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Resumo semanal</p>
                            <p class="settings-notif-desc">E-mail toda segunda-feira com o resumo da semana</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.weekly ? 'active' : ''" @click="email.weekly = !email.weekly; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(239,68,68,0.1);color:#ef4444">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Alertas de segurança</p>
                            <p class="settings-notif-desc">Login em novo dispositivo, troca de senha, etc.</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.security ? 'active' : ''" @click="email.security = !email.security; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(99,102,241,0.1);color:#6366f1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Metas e conquistas</p>
                            <p class="settings-notif-desc">Alertas de metas atingidas e novos pontos de conquista</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="email.goals ? 'active' : ''" @click="email.goals = !email.goals; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

            </div>
        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        {{-- ── CARD: Frequência ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Frequência de notificações</p>
                        <p class="settings-card-desc">Define quando e com que frequência você recebe alertas</p>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;padding:1rem 1.25rem">
                <label class="settings-freq-card" :class="frequency === 'instant' ? 'sf-active' : ''" @click="frequency = 'instant'; saveAll()">
                    <div class="settings-freq-icon" style="background:rgba(16,185,129,0.1);color:#10b981">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.2rem;height:1.2rem"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                    </div>
                    <p class="settings-freq-label">Imediato</p>
                    <p class="settings-freq-sub">Assim que ocorre</p>
                </label>
                <label class="settings-freq-card" :class="frequency === 'daily' ? 'sf-active' : ''" @click="frequency = 'daily'; saveAll()">
                    <div class="settings-freq-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.2rem;height:1.2rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                    </div>
                    <p class="settings-freq-label">Diário</p>
                    <p class="settings-freq-sub">Resumo às 18h</p>
                </label>
                <label class="settings-freq-card" :class="frequency === 'weekly' ? 'sf-active' : ''" @click="frequency = 'weekly'; saveAll()">
                    <div class="settings-freq-icon" style="background:rgba(168,85,247,0.1);color:#a855f7">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.2rem;height:1.2rem"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    </div>
                    <p class="settings-freq-label">Semanal</p>
                    <p class="settings-freq-sub">Toda segunda-feira</p>
                </label>
            </div>
        </div>

        {{-- ── CARD: Horário de silêncio ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Horário de silêncio</p>
                        <p class="settings-card-desc">Suspende todas as notificações no período definido</p>
                    </div>
                </div>
                <div class="settings-toggle-switch" :class="quiet_enabled ? 'active' : ''" @click="quiet_enabled = !quiet_enabled; saveAll()">
                    <div class="settings-toggle-track"></div>
                    <div class="settings-toggle-thumb"></div>
                </div>
            </div>

            <div x-show="quiet_enabled" x-transition style="padding:0 1.25rem 1.25rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
                <div>
                    <label class="settings-field-label">Das</label>
                    <input type="time" x-model="quiet_from" @change="saveAll()"
                           style="padding:0.45rem 0.65rem;border-radius:0.5rem;border:1.5px solid rgba(var(--s-accent-rgb),0.25);background:var(--s-accent-faint);color:inherit;font-size:0.85rem;outline:none;cursor:pointer">
                </div>
                <div>
                    <label class="settings-field-label">Até</label>
                    <input type="time" x-model="quiet_to" @change="saveAll()"
                           style="padding:0.45rem 0.65rem;border-radius:0.5rem;border:1.5px solid rgba(var(--s-accent-rgb),0.25);background:var(--s-accent-faint);color:inherit;font-size:0.85rem;outline:none;cursor:pointer">
                </div>
                <div style="padding:0.55rem 0.9rem;border-radius:0.5rem;background:rgba(var(--s-accent-rgb),0.07);font-size:0.8rem;color:var(--s-accent);font-weight:600;margin-top:1rem">
                    🔕 Silêncio de <span x-text="quiet_from"></span> às <span x-text="quiet_to"></span>
                </div>
            </div>
        </div>

        {{-- ── CARD: Notificações do sistema ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Notificações do sistema</p>
                        <p class="settings-card-desc">Alertas e lembretes dentro do FlowManager</p>
                    </div>
                </div>
                <span class="s-badge s-badge-success">Ativo</span>
            </div>

            <div class="settings-notif-list">

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Lembretes de vencimento</p>
                            <p class="settings-notif-desc">3 dias e 1 dia antes de faturas vencerem</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="system.due_dates ? 'active' : ''" @click="system.due_dates = !system.due_dates; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(236,72,153,0.1);color:#ec4899">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Conquistas desbloqueadas</p>
                            <p class="settings-notif-desc">Notificação quando você alcança um novo marco</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="system.achievements ? 'active' : ''" @click="system.achievements = !system.achievements; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(234,88,12,0.1);color:#ea580c">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.95rem;height:0.95rem"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Estoque baixo</p>
                            <p class="settings-notif-desc">Produtos com estoque abaixo do mínimo configurado</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="system.low_stock ? 'active' : ''" @click="system.low_stock = !system.low_stock; saveAll()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

            </div>
        </div>

        </div>{{-- /s-col-side --}}

        <div class="s-col-full">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-bottom:1rem">
            <div class="settings-card" style="padding:1.25rem;background:linear-gradient(135deg,rgba(var(--s-accent-rgb),0.08),rgba(255,255,255,0.92))">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(var(--s-accent-rgb),0.14)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Resumo inteligente</p>
                            <p class="settings-card-desc">Visão rápida do que está ativo hoje</p>
                        </div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem">
                    <div class="settings-info-tile" style="background:rgba(16,185,129,.08)">
                        <p class="settings-info-tile-label">E-mails ativos</p>
                        <p class="settings-info-tile-val" x-text="Object.values(email).filter(Boolean).length + ' categorias'"></p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(99,102,241,.08)">
                        <p class="settings-info-tile-label">Sistema ativo</p>
                        <p class="settings-info-tile-val" x-text="Object.values(system).filter(Boolean).length + ' alertas'"></p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(245,158,11,.08)">
                        <p class="settings-info-tile-label">Entrega</p>
                        <p class="settings-info-tile-val" x-text="frequency === 'instant' ? 'Imediata' : (frequency === 'daily' ? 'Resumo diário' : 'Resumo semanal')"></p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(236,72,153,.08)">
                        <p class="settings-info-tile-label">Silêncio</p>
                        <p class="settings-info-tile-val" x-text="quiet_enabled ? quiet_from + ' - ' + quiet_to : 'Desligado'"></p>
                    </div>
                </div>
            </div>

            <div class="settings-card" style="padding:1.25rem">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.709 3.168l2.343.257a48.312 48.312 0 0 0 9.396 0l2.343-.257A3.184 3.184 0 0 0 21.75 12.76V11.25c0-1.286-.84-2.423-2.068-2.809l-1.507-.471a48.114 48.114 0 0 0-12.35 0l-1.507.471A2.953 2.953 0 0 0 2.25 11.25v1.51Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12v3.75m0 0a2.25 2.25 0 0 0 2.25 2.25m-2.25-2.25a2.25 2.25 0 0 1-2.25 2.25"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Prévia de entrega</p>
                            <p class="settings-card-desc">Como o FlowManager vai priorizar seus avisos</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.7rem">
                    <div style="display:flex;align-items:start;gap:.7rem;padding:.75rem;border-radius:.75rem;background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.14)">
                        <div style="width:.55rem;height:.55rem;border-radius:999px;background:#10b981;margin-top:.35rem;flex-shrink:0"></div>
                        <div>
                            <p style="font-size:.8rem;font-weight:700;color:#14532d;margin:0">Prioridade alta</p>
                            <p style="font-size:.74rem;color:#64748b;margin:.15rem 0 0">Segurança, pagamentos e vendas urgentes ignoram resumos longos e devem chegar primeiro.</p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:start;gap:.7rem;padding:.75rem;border-radius:.75rem;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.14)">
                        <div style="width:.55rem;height:.55rem;border-radius:999px;background:#6366f1;margin-top:.35rem;flex-shrink:0"></div>
                        <div>
                            <p style="font-size:.8rem;font-weight:700;color:#3730a3;margin:0">Resumos automáticos</p>
                            <p style="font-size:.74rem;color:#64748b;margin:.15rem 0 0" x-text="frequency === 'instant' ? 'Você prefere eventos individuais em tempo real.' : (frequency === 'daily' ? 'Os avisos menos críticos serão agrupados no fim do dia.' : 'Os avisos menos críticos serão agrupados no resumo semanal.')"></p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:start;gap:.7rem;padding:.75rem;border-radius:.75rem;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.14)">
                        <div style="width:.55rem;height:.55rem;border-radius:999px;background:#f59e0b;margin-top:.35rem;flex-shrink:0"></div>
                        <div>
                            <p style="font-size:.8rem;font-weight:700;color:#92400e;margin:0">Janela silenciosa</p>
                            <p style="font-size:.74rem;color:#64748b;margin:.15rem 0 0" x-text="quiet_enabled ? 'Silêncio programado de ' + quiet_from + ' até ' + quiet_to + '.' : 'Sem silêncio agendado. Todos os avisos podem chegar a qualquer momento.'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── INFO: dica final ── --}}
        <div style="display:flex;align-items:flex-start;gap:0.65rem;padding:0.85rem 1rem;border-radius:0.75rem;background:rgba(var(--s-accent-rgb),0.05);border:1px solid rgba(var(--s-accent-rgb),0.12);margin-bottom:1.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="var(--s-accent)" style="width:1.1rem;height:1.1rem;flex-shrink:0;margin-top:0.05rem"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
            <p style="font-size:0.8rem;color:#64748b;line-height:1.5">Essas preferências são salvas na sua conta e sincronizadas entre dispositivos. O navegador mantém uma cópia local apenas para deixar a interface mais rápida.</p>
        </div>

        </div>{{-- /s-col-full --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
