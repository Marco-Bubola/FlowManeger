<x-layouts.app title="{{ $isAdminView ? 'Usuarios e permissoes' : 'Meu acesso' }}">
    <style>
        .access-center-page {
            --ac-accent: #9333ea;
            --ac-accent-rgb: 147, 51, 234;
            --ac-grad: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%);
            --ac-card-bg: white;
            --ac-card-border: #f1f5f9;
            --ac-text: #0f172a;
            --ac-muted: #64748b;
            --ac-border: rgba(148,163,184,.18);
            max-width: 1320px;
            margin: 0 auto;
            width: 100%;
            padding: 1.25rem var(--app-fluid-padding, clamp(0.65rem, 1.2vw, 1rem)) max(7.5rem, env(safe-area-inset-bottom));
            color: var(--ac-text);
        }
        .dark .access-center-page {
            --ac-card-bg: #1e293b;
            --ac-card-border: #334155;
            --ac-text: #f1f5f9;
            --ac-muted: #94a3b8;
            --ac-border: rgba(71,85,105,.48);
            color: var(--ac-text);
        }

        /* Alerts */
        .ac-alert { padding: .75rem 1rem; border-radius: 12px; margin-bottom: 1rem; border: 1.5px solid transparent; font-size: .84rem; font-weight: 600; }
        .ac-alert-success { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.2); color: #059669; }
        .dark .ac-alert-success { background: rgba(16,185,129,.1); color: #6ee7b7; }
        .ac-alert-error { background: rgba(239,68,68,.07); border-color: rgba(239,68,68,.2); color: #dc2626; }
        .dark .ac-alert-error { background: rgba(239,68,68,.09); color: #fca5a5; }

        /* Hero */
        .ac-hero { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .ac-hero-kicker {
            display: inline-flex; align-items: center; gap: .4rem; padding: .25rem .65rem;
            border: 1.5px solid rgba(var(--ac-accent-rgb),.2); background: rgba(var(--ac-accent-rgb),.06);
            border-radius: 999px; color: var(--ac-accent); font-size: .68rem; font-weight: 800;
            letter-spacing: .08em; text-transform: uppercase;
        }
        .dark .ac-hero-kicker { color: #c084fc; border-color: rgba(168,85,247,.3); background: rgba(168,85,247,.12); }
        .ac-hero-title { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 900; letter-spacing: -.04em; margin-top: .5rem; color: var(--ac-text); }
        .ac-hero-sub { max-width: 720px; color: var(--ac-muted); margin-top: .3rem; line-height: 1.6; font-size: .86rem; }
        .ac-hero-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: flex-start; }

        /* Buttons */
        .ac-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            border-radius: 12px; padding: .65rem .95rem; font-weight: 700; border: 1.5px solid transparent;
            cursor: pointer; transition: all .18s ease; font-size: .8rem; text-decoration: none; white-space: nowrap;
        }
        .ac-btn-primary { background: var(--ac-grad); color: #fff; box-shadow: 0 8px 24px rgba(var(--ac-accent-rgb),.2); }
        .ac-btn-primary:hover { transform: translateY(-1px); opacity: .94; }
        .ac-btn-ghost {
            background: white; border-color: #e2e8f0; color: #475569;
        }
        .dark .ac-btn-ghost { background: rgba(30,41,59,.8); border-color: #475569; color: #cbd5e1; }
        .ac-btn-ghost:hover { border-color: rgba(var(--ac-accent-rgb),.3); color: var(--ac-accent); }
        .dark .ac-btn-ghost:hover { color: #c084fc; }
        .ac-btn-danger { background: rgba(239,68,68,.06); border-color: rgba(239,68,68,.2); color: #dc2626; }
        .dark .ac-btn-danger { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.25); color: #fca5a5; }
        .ac-btn-danger:hover { background: rgba(239,68,68,.12); }
        .ac-btn-sm { padding: .45rem .7rem; border-radius: 10px; font-size: .72rem; }

        /* Stats */
        .ac-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
        .ac-stat {
            position: relative; overflow: hidden;
            background: var(--ac-card-bg); border: 1.5px solid var(--ac-card-border);
            border-radius: 1.1rem; padding: 1rem 1.1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03);
            transition: transform .2s, border-color .2s;
        }
        .ac-stat:hover { transform: translateY(-2px); border-color: rgba(var(--ac-accent-rgb),.16); }
        .ac-stat-label { color: var(--ac-muted); font-size: .68rem; text-transform: uppercase; letter-spacing: .1em; font-weight: 800; }
        .ac-stat-value { font-size: 1.75rem; font-weight: 900; letter-spacing: -.05em; margin-top: .3rem; }
        .ac-stat-sub { color: var(--ac-muted); font-size: .72rem; margin-top: .2rem; line-height: 1.45; }

        /* Cards / Sections */
        .ac-card {
            background: var(--ac-card-bg); border: 1.5px solid var(--ac-card-border);
            border-radius: 1.25rem; padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 24px rgba(0,0,0,.04);
            margin-bottom: 1rem;
            transition: border-color .2s;
        }
        .ac-card:hover { border-color: rgba(var(--ac-accent-rgb),.16); }
        .ac-card-head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: .85rem; }
        .ac-card-title { font-size: .98rem; font-weight: 800; letter-spacing: -.02em; color: var(--ac-text); }
        .ac-card-sub { color: var(--ac-muted); font-size: .8rem; margin-top: .2rem; line-height: 1.5; }

        /* Spotlight info */
        .ac-spotlight {
            border: 1.5px solid rgba(6,182,212,.18); border-radius: 1rem; padding: .85rem 1rem; margin-bottom: 1rem;
            background: linear-gradient(135deg, rgba(6,182,212,.06), rgba(var(--ac-accent-rgb),.05));
        }
        .dark .ac-spotlight { border-color: rgba(6,182,212,.22); background: linear-gradient(135deg, rgba(6,182,212,.08), rgba(168,85,247,.06)); }
        .ac-spotlight-title { font-size: .9rem; font-weight: 800; color: var(--ac-text); }
        .ac-spotlight-sub { color: var(--ac-muted); font-size: .8rem; margin-top: .15rem; line-height: 1.5; }

        /* Filter bar */
        .ac-filter { display: grid; grid-template-columns: 1.5fr .9fr auto; gap: .65rem; }
        .ac-field, .ac-select, textarea.ac-field {
            width: 100%; background: var(--ac-card-bg); border: 1.5px solid var(--ac-card-border);
            color: var(--ac-text); border-radius: 12px; padding: .7rem .85rem; font-size: .82rem; outline: none; resize: vertical;
        }
        .ac-field:focus, .ac-select:focus { border-color: rgba(var(--ac-accent-rgb),.4); box-shadow: 0 0 0 3px rgba(var(--ac-accent-rgb),.08); }
        .dark .ac-select option { background: #1e293b; }

        /* Plan grid */
        .ac-plan-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: .75rem; }
        .ac-plan-card {
            background: var(--ac-card-bg); border: 1.5px solid var(--ac-card-border);
            border-radius: 1rem; padding: .95rem 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.03); transition: transform .2s, border-color .2s;
        }
        .ac-plan-card:hover { transform: translateY(-2px); border-color: rgba(var(--ac-accent-rgb),.2); }
        .ac-plan-top { display: flex; justify-content: space-between; gap: .75rem; align-items: flex-start; margin-bottom: .65rem; }
        .ac-plan-name { font-size: .95rem; font-weight: 800; color: var(--ac-text); }
        .ac-plan-price { color: var(--ac-accent); font-weight: 800; font-size: .92rem; }
        .dark .ac-plan-price { color: #c084fc; }
        .ac-mini { font-size: .72rem; color: var(--ac-muted); line-height: 1.45; }

        /* Chips & Pills */
        .ac-chip {
            display: inline-flex; align-items: center;
            border: 1.5px solid var(--ac-card-border); border-radius: 999px;
            padding: .18rem .5rem; font-size: .65rem; font-weight: 600; color: var(--ac-muted);
            background: rgba(var(--ac-accent-rgb),.03);
        }
        .ac-pill-wrap { display: flex; flex-wrap: wrap; gap: .35rem; }
        .ac-pill {
            display: inline-flex; align-items: center; gap: .3rem;
            border-radius: 999px; padding: .22rem .55rem; font-size: .67rem; font-weight: 700;
            border: 1.5px solid transparent;
        }
        .ac-pill-on { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.18); color: #059669; }
        .dark .ac-pill-on { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.22); color: #6ee7b7; }
        .ac-pill-off { background: rgba(100,116,139,.06); border-color: var(--ac-card-border); color: rgba(100,116,139,.5); }
        .dark .ac-pill-off { background: rgba(255,255,255,.04); color: rgba(255,255,255,.3); }
        .ac-pill-admin { background: rgba(var(--ac-accent-rgb),.08); border-color: rgba(var(--ac-accent-rgb),.2); color: var(--ac-accent); }
        .dark .ac-pill-admin { background: rgba(236,72,153,.12); border-color: rgba(236,72,153,.25); color: #f9a8d4; }

        /* Table */
        .ac-table-shell { overflow-x: auto; border-radius: .75rem; }
        .ac-table { width: 100%; border-collapse: collapse; min-width: 1080px; }
        .ac-table thead th {
            text-align: left; padding: .65rem .75rem; font-size: .68rem; color: var(--ac-muted);
            text-transform: uppercase; letter-spacing: .08em; font-weight: 700;
            border-bottom: 1.5px solid var(--ac-card-border); background: rgba(248,250,252,.6);
        }
        .dark .ac-table thead th { background: rgba(15,23,42,.4); border-color: #334155; }
        .ac-table tbody td {
            padding: .85rem .75rem; border-bottom: 1px solid rgba(241,245,249,.8);
            vertical-align: top; font-size: .82rem;
        }
        .dark .ac-table tbody td { border-color: rgba(51,65,85,.5); }
        .ac-table tbody tr:hover td { background: rgba(var(--ac-accent-rgb),.02); }
        .dark .ac-table tbody tr:hover td { background: rgba(255,255,255,.02); }

        /* User */
        .ac-user-card { display: flex; align-items: center; gap: .65rem; }
        .ac-avatar {
            width: 40px; height: 40px; border-radius: 12px;
            display: grid; place-items: center; font-weight: 900; font-size: .82rem;
            color: white; background: var(--ac-grad);
            box-shadow: 0 6px 18px rgba(var(--ac-accent-rgb),.2); flex-shrink: 0;
        }
        .ac-user-name { font-weight: 800; font-size: .85rem; color: var(--ac-text); }
        .ac-user-meta { color: var(--ac-muted); font-size: .72rem; margin-top: .1rem; }
        .ac-stack { display: flex; flex-direction: column; gap: .3rem; }

        /* Badges */
        .ac-badge {
            display: inline-flex; align-items: center; gap: .3rem;
            border-radius: 999px; padding: .2rem .55rem; font-size: .66rem; font-weight: 800;
            border: 1.5px solid transparent;
        }
        .ac-badge-green { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.18); color: #059669; }
        .dark .ac-badge-green { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.22); color: #6ee7b7; }
        .ac-badge-gray { background: rgba(100,116,139,.08); border-color: rgba(100,116,139,.15); color: var(--ac-muted); }
        .dark .ac-badge-gray { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); }
        .ac-badge-blue { background: rgba(59,130,246,.08); border-color: rgba(59,130,246,.18); color: #2563eb; }
        .dark .ac-badge-blue { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.22); color: #93c5fd; }
        .ac-badge-pink { background: rgba(var(--ac-accent-rgb),.08); border-color: rgba(var(--ac-accent-rgb),.2); color: var(--ac-accent); }
        .dark .ac-badge-pink { background: rgba(236,72,153,.12); border-color: rgba(236,72,153,.25); color: #f9a8d4; }
        .ac-badge-amber { background: rgba(245,158,11,.08); border-color: rgba(245,158,11,.18); color: #d97706; }
        .dark .ac-badge-amber { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.25); color: #fcd34d; }

        /* Forms */
        .ac-grant-form, .ac-action-stack { display: grid; gap: .45rem; }
        .ac-card-note {
            border-radius: 12px; border: 1.5px solid var(--ac-card-border);
            background: rgba(248,250,252,.6); padding: .65rem .75rem;
        }
        .dark .ac-card-note { background: rgba(30,41,59,.5); }
        .ac-empty { text-align: center; color: var(--ac-muted); padding: 2rem 1rem; font-size: .86rem; }
        .ac-pagination { margin-top: .75rem; }
        .ac-pagination nav { display: flex; justify-content: center; }
        .ac-pagination svg { width: 1rem; height: 1rem; }

        /* Responsive */
        @media (max-width: 1200px) {
            .ac-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 900px) {
            .access-center-page { padding: .85rem .65rem max(7.25rem, env(safe-area-inset-bottom)); }
            .ac-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ac-plan-grid, .ac-filter { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .ac-stats { grid-template-columns: 1fr 1fr; }
            .ac-hero { flex-direction: column; }
            .ac-hero-actions { width: 100%; }
            .ac-hero-actions .ac-btn { flex: 1; }
        }
    </style>
@php
    $heroTitle = $isAdminView ? 'Usuarios, planos e permissoes' : 'Meu acesso, plano e permissoes';
    $heroSub = $isAdminView
        ? 'Os administradores conseguem ajustar nivel de acesso, trocar o plano de qualquer conta e revisar as permissoes efetivas de toda a base.'
        : 'Aqui voce acompanha somente a sua conta: plano atual, recursos liberados e o que esta disponivel para upgrade, sem acesso aos dados de outros usuarios.';

    $statCards = $isAdminView
        ? [
            ['label' => 'Usuarios', 'value' => $stats['total_users'], 'sub' => 'Base total cadastrada', 'tone' => '#ffffff'],
            ['label' => 'Admins', 'value' => $stats['admin_users'], 'sub' => 'Contas com acesso elevado', 'tone' => '#f9a8d4'],
            ['label' => 'Pagos', 'value' => $stats['paid_users'], 'sub' => 'Assinaturas fora do plano Free', 'tone' => '#6ee7b7'],
            ['label' => 'Free', 'value' => $stats['free_users'], 'sub' => 'Plano padrao ou sem assinatura valida', 'tone' => '#93c5fd'],
            ['label' => 'Equipe', 'value' => $stats['team_users'], 'sub' => 'Planos com multiusuario ativo', 'tone' => '#fcd34d'],
        ]
        : [
            ['label' => 'Escopo', 'value' => 'Pessoal', 'sub' => 'Apenas sua conta aparece nesta area', 'tone' => '#ffffff'],
            ['label' => 'Nivel', 'value' => $stats['admin_users'] ? 'Admin' : 'Padrao', 'sub' => 'Seu nivel de acesso atual', 'tone' => '#f9a8d4'],
            ['label' => 'Plano', 'value' => $stats['paid_users'] ? 'Pago' : 'Free', 'sub' => 'Resumo do plano em uso', 'tone' => '#6ee7b7'],
            ['label' => 'Equipe', 'value' => $stats['team_users'] ? 'Ativa' : 'Solo', 'sub' => 'Capacidade de multiusuario do seu plano', 'tone' => '#93c5fd'],
            ['label' => 'Permissoes', 'value' => $stats['enabled_permissions'], 'sub' => 'Permissoes efetivas habilitadas', 'tone' => '#fcd34d'],
        ];
@endphp

<div class="access-center-page w-full app-viewport-fit mobile-393-base">
    @include('partials.plan-center-nav', [
        'scope' => $isAdminView ? 'admin' : 'account',
        'title' => $isAdminView ? 'Usuarios, planos e permissoes' : 'Meu acesso e permissoes',
        'subtitle' => $isAdminView
            ? 'Tudo agora fica dentro do layout principal, com a mesma sidebar, tab bar e navegação da area de planos.'
            : 'Aqui voce ve apenas os dados da sua propria conta e os recursos liberados no seu plano atual.',
    ])

        @if(session('success'))
            <div class="ac-alert ac-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="ac-alert ac-alert-error">{{ session('error') }}</div>
        @endif

        <div class="ac-hero">
            <div>
                <span class="ac-hero-kicker">{{ $isAdminView ? 'Painel de acesso' : 'Central da conta' }}</span>
                <div class="ac-hero-title">{{ $heroTitle }}</div>
                <div class="ac-hero-sub">{{ $heroSub }}</div>
            </div>

            <div class="ac-hero-actions">
                @if($isAdminView)
                    <a href="{{ route('admin.plans.index') }}" class="ac-btn ac-btn-ghost">Editar planos</a>
                    <a href="{{ route('admin.subscriptions.index') }}" class="ac-btn ac-btn-primary">Ver assinaturas</a>
                @else
                    <a href="{{ route('settings.plan') }}" class="ac-btn ac-btn-ghost">Abrir configuracoes</a>
                    <a href="{{ route('subscription.plans') }}" class="ac-btn ac-btn-primary">Trocar plano</a>
                @endif
            </div>
        </div>

        <section class="ac-stats">
            @foreach($statCards as $card)
                <article class="ac-stat">
                    <div class="ac-stat-label">{{ $card['label'] }}</div>
                    <div class="ac-stat-value" style="color:{{ $card['tone'] }}">{{ $card['value'] }}</div>
                    <div class="ac-stat-sub">{{ $card['sub'] }}</div>
                </article>
            @endforeach
        </section>

        @if(!$isAdminView)
            <section class="ac-spotlight">
                <div class="ac-spotlight-title">Seu escopo esta isolado</div>
                <div class="ac-spotlight-sub">Voce pode consultar o plano ativo, recursos liberados e fazer upgrade da propria conta. Alteracoes de nivel admin e mudancas de plano de terceiros continuam restritas aos administradores.</div>
            </section>
        @endif

        @if($isAdminView)
            <div class="ac-card">
                <div class="ac-card-head">
                    <div>
                        <div class="ac-card-title">Filtro rapido</div>
                        <div class="ac-card-sub">Busque por nome, email ou veja apenas usuarios de um plano especifico.</div>
                    </div>
                </div>

                <form method="GET" class="ac-filter">
                    <input type="text" name="search" class="ac-field" placeholder="Buscar por nome ou email" value="{{ request('search') }}">
                    <select name="plan" class="ac-select">
                        <option value="">Todos os planos</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <button type="submit" class="ac-btn ac-btn-primary">Filtrar</button>
                        @if(request()->filled('search') || request()->filled('plan'))
                            <a href="{{ route('admin.plans.users') }}" class="ac-btn ac-btn-ghost">Limpar</a>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        <div class="ac-card">
            <div class="ac-card-head">
                <div>
                    <div class="ac-card-title">Permissoes liberadas por plano</div>
                    <div class="ac-card-sub">Cada card resume o que o plano libera no sistema. A mudanca de plano reflete automaticamente nas permissoes efetivas do usuario.</div>
                </div>
            </div>

            <div class="ac-plan-grid">
                @foreach($plans as $plan)
                    <article class="ac-plan-card">
                        <div class="ac-plan-top">
                            <div>
                                <div class="ac-plan-name">{{ $plan->name }}</div>
                                <div class="ac-mini">/{{ $plan->slug }}</div>
                            </div>
                            <div style="text-align:right">
                                <div class="ac-plan-price">{{ $plan->formattedPriceMonthly() }}</div>
                                <div class="ac-mini">por mes</div>
                            </div>
                        </div>

                        <div class="ac-pill-wrap" style="margin-bottom:.6rem">
                            @if($plan->is_default)
                                <span class="ac-chip">Padrao</span>
                            @endif
                            @if($plan->badge_label)
                                <span class="ac-chip">{{ $plan->badge_label }}</span>
                            @endif
                            <span class="ac-chip">{{ $plan->max_users }} usuario(s)</span>
                            <span class="ac-chip">{{ $plan->max_products === -1 ? 'Produtos ilimitados' : $plan->max_products . ' produtos' }}</span>
                        </div>

                        <div class="ac-pill-wrap">
                            @foreach($plan->permissionMatrix() as $permission)
                                <span class="ac-pill {{ $permission['enabled'] ? 'ac-pill-on' : 'ac-pill-off' }}">
                                    <span>{{ $permission['enabled'] ? '●' : '○' }}</span>
                                    {{ $permission['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="ac-card">
            <div class="ac-card-head">
                <div>
                    <div class="ac-card-title">{{ $isAdminView ? 'Mapa de acesso por usuario' : 'Resumo detalhado da sua conta' }}</div>
                    <div class="ac-card-sub">
                        {{ $isAdminView
                            ? 'Os admins podem trocar o plano de qualquer usuario, promover ou rebaixar acesso administrativo e revogar assinaturas quando necessario.'
                            : 'Voce pode acompanhar o status da sua assinatura, conferir as permissoes ativas e seguir para a troca do proprio plano.' }}
                    </div>
                </div>
            </div>

            <div class="ac-table-shell">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Plano atual</th>
                            <th>Status</th>
                            <th>Permissoes efetivas</th>
                            <th>{{ $isAdminView ? 'Gerenciar plano' : 'Meu plano' }}</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $currentPlan = $user->activeSubscription?->plan ?? $defaultPlan;
                                $activeSubscription = $user->activeSubscription;
                                $permissions = $user->effectivePermissions();
                                $statusBadge = match ($activeSubscription?->statusColor()) {
                                    'green' => 'ac-badge-green',
                                    'blue' => 'ac-badge-blue',
                                    'orange' => 'ac-badge-amber',
                                    default => 'ac-badge-gray',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="ac-user-card">
                                        <div class="ac-avatar">{{ $user->initials() }}</div>
                                        <div>
                                            <div class="ac-user-name">{{ $user->name }}</div>
                                            <div class="ac-user-meta">{{ $user->email }} · ID {{ $user->id }}</div>
                                            <div class="ac-pill-wrap" style="margin-top:.3rem">
                                                @if($user->is_admin)
                                                    <span class="ac-badge ac-badge-pink">Admin</span>
                                                @endif
                                                @if($user->id === auth()->id())
                                                    <span class="ac-badge ac-badge-blue">Conta atual</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="ac-stack">
                                        <div style="font-weight:800">{{ $currentPlan?->name ?? 'Sem plano' }}</div>
                                        <div class="ac-mini">/{{ $currentPlan?->slug ?? 'nao-definido' }}</div>
                                        <div class="ac-pill-wrap">
                                            @if($currentPlan?->is_default)
                                                <span class="ac-chip">Padrao</span>
                                            @endif
                                            <span class="ac-chip">{{ $currentPlan?->max_users ?? 1 }} usuario(s)</span>
                                            <span class="ac-chip">{{ $activeSubscription?->billingCycleLabel() ?? 'Sem ciclo' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="ac-stack">
                                        @if($activeSubscription)
                                            <span class="ac-badge {{ $statusBadge }}">{{ $activeSubscription->statusLabel() }}</span>
                                            <div class="ac-mini">{{ $activeSubscription->gatewayLabel() }} · {{ $activeSubscription->billingCycleLabel() }}</div>
                                            <div class="ac-mini">
                                                {{ $activeSubscription->isTrialing()
                                                    ? 'Trial ate ' . $activeSubscription->trial_ends_at?->format('d/m/Y')
                                                    : 'Vigencia ate ' . ($activeSubscription->current_period_end?->format('d/m/Y') ?? 'sem prazo') }}
                                            </div>
                                        @else
                                            <span class="ac-badge ac-badge-gray">Sem assinatura ativa</span>
                                            <div class="ac-mini">Usando o plano padrao do sistema.</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="ac-pill-wrap">
                                        @foreach($permissions as $permission)
                                            <span class="ac-pill {{ $permission['source'] === 'admin' ? 'ac-pill-admin' : ($permission['enabled'] ? 'ac-pill-on' : 'ac-pill-off') }}">
                                                <span>{{ $permission['enabled'] ? '●' : '○' }}</span>
                                                {{ $permission['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($isAdminView)
                                        <form method="POST" action="{{ route('admin.plans.grant') }}" class="ac-grant-form">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <select name="plan_id" class="ac-select" required>
                                                @foreach($plans as $plan)
                                                    <option value="{{ $plan->id }}" {{ $currentPlan && $currentPlan->id === $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="billing_cycle" class="ac-select" required>
                                                <option value="monthly" {{ ($activeSubscription?->billing_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Mensal</option>
                                                <option value="annual" {{ ($activeSubscription?->billing_cycle ?? 'monthly') === 'annual' ? 'selected' : '' }}>Anual</option>
                                            </select>
                                            <textarea name="notes" rows="2" class="ac-field" placeholder="Observacao interna sobre a mudanca">{{ $activeSubscription?->notes }}</textarea>
                                            <button type="submit" class="ac-btn ac-btn-primary ac-btn-sm">Salvar plano</button>
                                        </form>
                                    @else
                                        <div class="ac-action-stack">
                                            <div class="ac-card-note">
                                                <div style="font-weight:800;font-size:.82rem">Voce altera apenas o proprio plano</div>
                                                <div class="ac-mini">Use o fluxo de assinatura para upgrade, downgrade ou cancelamento.</div>
                                            </div>
                                            <a href="{{ route('subscription.plans') }}" class="ac-btn ac-btn-primary ac-btn-sm">Escolher outro plano</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="ac-action-stack">
                                        @if($isAdminView)
                                            <form method="POST" action="{{ route('admin.users.toggle-admin') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="ac-btn {{ $user->is_admin ? 'ac-btn-ghost' : 'ac-btn-primary' }} ac-btn-sm" style="width:100%">
                                                    {{ $user->is_admin ? 'Remover admin' : 'Tornar admin' }}
                                                </button>
                                            </form>

                                            @if($activeSubscription)
                                                <form method="POST" action="{{ route('admin.subscriptions.revoke', $activeSubscription) }}">
                                                    @csrf
                                                    <button type="submit" class="ac-btn ac-btn-danger ac-btn-sm" style="width:100%">Revogar acesso</button>
                                                </form>
                                            @endif

                                            @if($user->id === auth()->id() && $user->is_admin)
                                                <div class="ac-mini">Seu proprio acesso admin continua protegido contra revogacao acidental.</div>
                                            @endif
                                        @else
                                            <a href="{{ route('settings.plan') }}" class="ac-btn ac-btn-ghost ac-btn-sm">Ver configuracoes</a>
                                            <div class="ac-mini">Nao ha acesso a dados de terceiros nesta area.</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ac-empty">Nenhum usuario encontrado para os filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="ac-pagination">{{ $users->links() }}</div>
            @endif
        </div>
</div>
</x-layouts.app>