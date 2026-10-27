<x-layouts.app title="Usuários e permissões">
<section class="settings-admin-users-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        @php
            // Administração › Usuários: plano, situação e permissões de cada conta, com as ações de admin.
            $statCards = [
                ['Usuários', $stats['total_users'], 'bi-people', 'from-indigo-500 to-purple-600'],
                ['Admins', $stats['admin_users'], 'bi-shield-lock', 'from-pink-500 to-rose-500'],
                ['Em plano pago', $stats['paid_users'], 'bi-patch-check', 'from-emerald-500 to-teal-500'],
                ['No Grátis', $stats['free_users'], 'bi-gift', 'from-sky-500 to-blue-600'],
                ['Com equipe', $stats['team_users'], 'bi-people-fill', 'from-amber-500 to-orange-500'],
            ];
            $filtered = request()->filled('search') || request()->filled('plan');
        @endphp

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">{{ session('error') }}</div>
        @endif

        <div class="au-page">
            <div class="au-stats">
                @foreach($statCards as [$label, $value, $icon, $grad])
                    <div class="au-stat">
                        <span class="au-stat-ico bg-gradient-to-br {{ $grad }}"><i class="bi {{ $icon }}"></i></span>
                        <div class="min-w-0">
                            <b>{{ $value }}</b>
                            <small>{{ $label }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Busca e filtro por plano --}}
            <form method="GET" class="au-filter">
                <label class="au-search">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" placeholder="Buscar por nome ou e-mail" value="{{ request('search') }}">
                </label>
                <select name="plan" class="au-select" onchange="this.form.submit()">
                    <option value="">Todos os planos</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->slug }}" @selected(request('plan') === $plan->slug)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="au-btn au-btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                @if($filtered)
                    <a href="{{ route('admin.plans.users') }}" class="au-btn au-btn-ghost">Limpar</a>
                @endif
            </form>

            {{-- Uma conta por card --}}
            <div class="au-list">
                @forelse($users as $user)
                    @php
                        $currentPlan = $user->activeSubscription?->plan ?? $defaultPlan;
                        $sub = $user->activeSubscription;
                        $perms = collect($user->effectivePermissions())->reject(fn ($p) => $p['source'] === 'admin-only');
                        $permsOn = $perms->where('enabled', true)->count();
                        $tone = match ($sub?->statusColor()) {
                            'green' => 'is-green', 'blue' => 'is-blue', 'orange' => 'is-amber', default => 'is-gray',
                        };
                        $isMe = $user->id === auth()->id();
                    @endphp
                    <article class="au-user" x-data="{ open: false }" wire:key="u-{{ $user->id }}">
                        <div class="au-user-top">
                            <span class="au-avatar">{{ $user->initials() }}</span>
                            <div class="au-user-id">
                                <b>{{ $user->name }}</b>
                                <small>{{ $user->email }}</small>
                                <span class="au-tags">
                                    @if($user->is_admin)<span class="au-tag is-pink"><i class="bi bi-shield-lock"></i> Admin</span>@endif
                                    @if($isMe)<span class="au-tag is-blue">Você</span>@endif
                                    <span class="au-tag {{ $tone }}">{{ $sub ? $sub->statusLabel() : 'Sem assinatura' }}</span>
                                </span>
                            </div>
                            <div class="au-plan">
                                <b>{{ $currentPlan?->name ?? 'Sem plano' }}</b>
                                <small>
                                    @if($sub)
                                        {{ $sub->billingCycleLabel() }} ·
                                        {{ $sub->isTrialing() ? 'teste até ' . $sub->trial_ends_at?->format('d/m/Y') : 'até ' . ($sub->current_period_end?->format('d/m/Y') ?? 'sem prazo') }}
                                    @else
                                        Usa o plano padrão
                                    @endif
                                </small>
                            </div>
                        </div>

                        <div class="au-perms">
                            @foreach($perms as $perm)
                                <span class="au-perm {{ $perm['enabled'] ? 'is-on' : '' }}"><i class="bi {{ $perm['enabled'] ? 'bi-check-lg' : 'bi-lock' }}"></i>{{ $perm['label'] }}</span>
                            @endforeach
                        </div>

                        <div class="au-actions">
                            <button type="button" class="au-btn au-btn-primary" @click="open = !open"><i class="bi bi-arrow-left-right"></i> <span x-text="open ? 'Fechar' : 'Mudar plano'"></span></button>
                            @unless($isMe)
                                <form method="POST" action="{{ route('admin.users.toggle-admin') }}" onsubmit="return fmConfirmSubmit(event, '{{ $user->is_admin ? 'Tirar o acesso de administrador de ' . e($user->name) . '?' : 'Tornar ' . e($user->name) . ' administrador? Essa pessoa poderá mudar planos e acessos de todos.' }}')">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <button type="submit" class="au-btn au-btn-ghost"><i class="bi {{ $user->is_admin ? 'bi-shield-x' : 'bi-shield-plus' }}"></i> {{ $user->is_admin ? 'Tirar admin' : 'Tornar admin' }}</button>
                                </form>
                                @if($sub)
                                    <form method="POST" action="{{ route('admin.subscriptions.revoke', $sub) }}" onsubmit="return fmConfirmSubmit(event, 'Cancelar a assinatura de {{ e($user->name) }}? A conta volta para o plano padrão.')">
                                        @csrf
                                        <button type="submit" class="au-btn au-btn-danger"><i class="bi bi-x-circle"></i> Cancelar assinatura</button>
                                    </form>
                                @endif
                            @else
                                <span class="au-note"><i class="bi bi-info-circle"></i> Seu próprio acesso de admin não pode ser tirado por aqui.</span>
                            @endunless
                        </div>

                        <form method="POST" action="{{ route('admin.plans.grant') }}" class="au-grant" x-show="open" x-collapse x-cloak>
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <label>
                                <span>Plano</span>
                                <select name="plan_id" class="au-select" required>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" @selected($currentPlan && $currentPlan->id === $plan->id)>{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span>Cobrança</span>
                                <select name="billing_cycle" class="au-select" required>
                                    <option value="monthly" @selected(($sub?->billing_cycle ?? 'monthly') === 'monthly')>Mensal</option>
                                    <option value="annual" @selected(($sub?->billing_cycle ?? 'monthly') === 'annual')>Anual</option>
                                </select>
                            </label>
                            <label class="au-grant-note">
                                <span>Anotação (só admins veem)</span>
                                <input type="text" name="notes" class="au-select" placeholder="Ex.: cortesia, parceria" value="{{ $sub?->notes }}">
                            </label>
                            <button type="submit" class="au-btn au-btn-primary"><i class="bi bi-check2"></i> Salvar plano</button>
                        </form>
                    </article>
                @empty
                    <div class="au-empty">
                        <i class="bi bi-person-x"></i>
                        <b>Nenhuma conta encontrada</b>
                        <small>Tente outro nome, e-mail ou plano.</small>
                    </div>
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="au-pages">{{ $users->links() }}</div>
            @endif

            <p class="au-foot"><i class="bi bi-info-circle"></i> O que cada plano inclui é editado em <a href="{{ route('admin.plans.index') }}">Planos</a>. Pagamentos e históricos ficam em <a href="{{ route('admin.subscriptions.index') }}">Assinaturas</a>.</p>
        </div>

        <style>
            .au-page { display: flex; flex-direction: column; gap: 1rem; }
            .au-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .7rem; }
            .au-stat { display: flex; align-items: center; gap: .7rem; padding: .85rem; border-radius: 1.1rem; background: #fff; border: 1px solid #e2e8f0; }
            .dark .au-stat { background: #0f172a; border-color: #1e293b; }
            .au-stat-ico { flex: none; display: grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: .8rem; color: #fff; font-size: 1.05rem; }
            .au-stat b { display: block; font-size: 1.35rem; font-weight: 900; color: #0f172a; line-height: 1.1; }
            .au-stat small { display: block; font-size: .72rem; font-weight: 600; color: #64748b; }
            .dark .au-stat b { color: #f1f5f9; }
            .au-filter { display: flex; flex-wrap: wrap; gap: .5rem; padding: .7rem; border-radius: 1.1rem; background: #fff; border: 1px solid #e2e8f0; }
            .dark .au-filter { background: #0f172a; border-color: #1e293b; }
            .au-search { flex: 1 1 260px; display: flex; align-items: center; gap: .5rem; padding: 0 .8rem; border-radius: .8rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; }
            .au-search input { flex: 1; min-width: 0; border: 0; background: transparent; padding: .6rem 0; font-size: .85rem; color: #0f172a; outline: none; box-shadow: none; }
            .dark .au-search { background: #111827; border-color: #1f2937; }
            .dark .au-search input { color: #f1f5f9; }
            .au-select { min-width: 0; padding: .55rem .75rem; border-radius: .8rem; border: 1px solid #e2e8f0; background: #f8fafc; font-size: .85rem; color: #0f172a; }
            .dark .au-select { background: #111827; border-color: #1f2937; color: #f1f5f9; }
            .au-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .55rem .9rem; border-radius: .8rem; font-size: .8rem; font-weight: 800; white-space: nowrap; border: 1px solid transparent; transition: filter .15s; }
            .au-btn:hover { filter: brightness(1.05); }
            .au-btn-primary { background: linear-gradient(135deg, var(--color-indigo-600), var(--color-purple-600)); color: #fff; }
            .au-btn-ghost { background: #fff; border-color: #e2e8f0; color: #334155; }
            .dark .au-btn-ghost { background: #111827; border-color: #334155; color: #e2e8f0; }
            .au-btn-danger { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
            .dark .au-btn-danger { background: rgba(244, 63, 94, .1); border-color: rgba(244, 63, 94, .3); color: #fda4af; }
            .au-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; align-items: start; }
            .au-user { display: flex; flex-direction: column; gap: .8rem; padding: 1rem; border-radius: 1.25rem; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -22px rgba(15, 23, 42, .3); }
            .dark .au-user { background: #0f172a; border-color: #1e293b; box-shadow: none; }
            .au-user-top { display: flex; align-items: center; gap: .75rem; }
            .au-avatar { flex: none; display: grid; place-items: center; width: 2.9rem; height: 2.9rem; border-radius: 1rem; color: #fff; font-weight: 900; background: linear-gradient(135deg, var(--color-indigo-500), var(--color-purple-600)); }
            .au-user-id { flex: 1; min-width: 0; }
            .au-user-id b { display: block; font-size: .95rem; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .au-user-id small { display: block; font-size: .74rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .dark .au-user-id b { color: #f1f5f9; }
            .au-tags { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .3rem; }
            .au-tag { display: inline-flex; align-items: center; gap: .25rem; padding: .12rem .5rem; border-radius: 999px; font-size: .66rem; font-weight: 800; background: #f1f5f9; color: #475569; }
            .au-tag.is-pink { background: #fdf2f8; color: #be185d; }
            .au-tag.is-blue { background: #eff6ff; color: #1d4ed8; }
            .au-tag.is-green { background: #ecfdf5; color: #047857; }
            .au-tag.is-amber { background: #fffbeb; color: #b45309; }
            .dark .au-tag { background: #1e293b; color: #cbd5e1; }
            .au-plan { flex: none; text-align: right; max-width: 45%; }
            .au-plan b { display: block; font-size: .95rem; font-weight: 900; color: var(--color-indigo-600); }
            .au-plan small { display: block; font-size: .7rem; color: #64748b; }
            .dark .au-plan b { color: #a5b4fc; }
            .au-perms { display: flex; flex-wrap: wrap; gap: .3rem; }
            .au-perm { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .5rem; border-radius: .6rem; font-size: .68rem; font-weight: 700; background: #f8fafc; color: #94a3b8; border: 1px solid #eef2f7; }
            .au-perm.is-on { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
            .dark .au-perm { background: #111827; border-color: #1f2937; color: #64748b; }
            .dark .au-perm.is-on { background: rgba(16, 185, 129, .12); border-color: rgba(16, 185, 129, .3); color: #6ee7b7; }
            .au-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; padding-top: .75rem; border-top: 1px dashed #e2e8f0; }
            .dark .au-actions { border-color: #1e293b; }
            .au-note { font-size: .72rem; color: #64748b; display: inline-flex; gap: .3rem; align-items: center; }
            .au-grant { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; padding: .8rem; border-radius: 1rem; background: #f8fafc; border: 1px solid #eef2f7; }
            .dark .au-grant { background: #0b1220; border-color: #1e293b; }
            .au-grant label span { display: block; font-size: .7rem; font-weight: 800; color: #64748b; margin-bottom: .25rem; }
            .au-grant .au-select { width: 100%; background: #fff; }
            .dark .au-grant .au-select { background: #111827; }
            .au-grant-note { grid-column: 1 / -1; }
            .au-grant button { grid-column: 1 / -1; }
            .au-empty { grid-column: 1 / -1; display: flex; flex-direction: column; align-items: center; gap: .2rem; padding: 2.5rem 1rem; border-radius: 1.25rem; border: 1px dashed #cbd5e1; color: #64748b; text-align: center; }
            .au-empty i { font-size: 1.8rem; }
            .au-empty b { color: #0f172a; }
            .dark .au-empty b { color: #f1f5f9; }
            .au-foot { font-size: .75rem; color: #64748b; }
            .au-foot a { color: var(--color-indigo-600); font-weight: 700; }
            @media (max-width: 1279px) { .au-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } .au-list { grid-template-columns: minmax(0, 1fr); } }
            @media (max-width: 767px) {
                .au-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .au-stats .au-stat:first-child { grid-column: 1 / -1; }
                .au-user-top { flex-wrap: wrap; }
                .au-plan { text-align: left; max-width: none; flex-basis: 100%; padding-left: 3.65rem; }
                .au-filter .au-select, .au-filter .au-btn { flex: 1 1 auto; }
                .au-actions .au-btn, .au-actions form { flex: 1 1 auto; }
                .au-actions form .au-btn { width: 100%; }
                .au-grant { grid-template-columns: minmax(0, 1fr); }
            }
        </style>
    </x-settings.layout>
</section>
</x-layouts.app>
