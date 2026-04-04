<x-layouts.app title="{{ $isAdminView ? 'Usuarios e permissoes' : 'Meu acesso' }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        .access-center-page {
            --pink: #ec4899;
            --purple: #a855f7;
            --cyan: #06b6d4;
            --green: #10b981;
            --amber: #f59e0b;
            --bg: #050507;
            --bg2: #0c0c12;
            --card: rgba(255,255,255,.04);
            --border: rgba(255,255,255,.09);
            --text: #f8fafc;
            --muted: rgba(255,255,255,.58);
            --grad: linear-gradient(135deg, #ec4899 0%, #a855f7 55%, #06b6d4 100%);
            --grad-soft: linear-gradient(135deg, rgba(236,72,153,.18) 0%, rgba(168,85,247,.18) 55%, rgba(6,182,212,.15) 100%);
        }
        .access-center-page {
            max-width: 1320px;
            margin: 0 auto;
            width: 100%;
            padding: 1.5rem 1rem max(7.5rem, env(safe-area-inset-bottom));
            color: #0f172a;
        }
        .dark .access-center-page { color: #f8fafc; }
        a { color: inherit; text-decoration: none; }
        .alert { padding: .95rem 1.15rem; border-radius: 14px; margin-bottom: 1.2rem; border: 1px solid transparent; }
        .alert-success { background: rgba(16,185,129,.10); border-color: rgba(16,185,129,.2); color: #6ee7b7; }
        .alert-error { background: rgba(239,68,68,.09); border-color: rgba(239,68,68,.2); color: #fca5a5; }
        .hero {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .7rem;
            border: 1px solid rgba(236,72,153,.25);
            background: rgba(236,72,153,.08);
            border-radius: 999px;
            color: #f9a8d4;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .hero-title { font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 900; letter-spacing: -.05em; margin-top: .75rem; }
        .hero-sub { max-width: 820px; color: var(--muted); margin-top: .45rem; line-height: 1.65; font-size: .94rem; }
        .hero-actions { display: flex; gap: .65rem; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            border-radius: 12px;
            padding: .82rem 1.05rem;
            font-weight: 800;
            border: 1px solid transparent;
            cursor: pointer;
            transition: .18s ease;
            font-size: .82rem;
            text-decoration: none;
        }
        .btn-primary { background: var(--grad); color: #fff; box-shadow: 0 16px 40px rgba(168,85,247,.22); }
        .btn-primary:hover { transform: translateY(-1px); opacity: .96; }
        .btn-ghost { background: rgba(255,255,255,.04); border-color: var(--border); color: #fff; }
        .btn-ghost:hover { background: rgba(255,255,255,.08); }
        .btn-danger { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.2); color: #fca5a5; }
        .btn-danger:hover { background: rgba(239,68,68,.14); }
        .btn-sm { padding: .56rem .8rem; border-radius: 10px; font-size: .73rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.025));
            border-radius: 22px;
            padding: 1.15rem;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            inset: auto -12% -35% auto;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .stat-label { color: var(--muted); font-size: .72rem; text-transform: uppercase; letter-spacing: .1em; font-weight: 800; }
        .stat-value { font-size: 1.95rem; font-weight: 900; letter-spacing: -.05em; margin-top: .4rem; }
        .stat-sub { color: var(--muted); font-size: .76rem; margin-top: .28rem; line-height: 1.5; }
        .section { border: 1px solid var(--border); background: var(--card); border-radius: 24px; padding: 1.2rem; margin-bottom: 1.4rem; }
        .section-head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; flex-wrap: wrap; margin-bottom: 1rem; }
        .section-title { font-size: 1.05rem; font-weight: 800; letter-spacing: -.02em; }
        .section-sub { color: var(--muted); font-size: .84rem; margin-top: .25rem; line-height: 1.55; }
        .spotlight {
            border: 1px solid rgba(6,182,212,.16);
            background: linear-gradient(135deg, rgba(6,182,212,.10), rgba(168,85,247,.08));
            border-radius: 20px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.2rem;
        }
        .spotlight strong { color: #fff; }
        .filter-bar { display: grid; grid-template-columns: 1.5fr .9fr auto; gap: .8rem; }
        .field, .select, textarea.field {
            width: 100%;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 14px;
            padding: .85rem .95rem;
            font-size: .85rem;
            outline: none;
            resize: vertical;
        }
        .field:focus, .select:focus, textarea.field:focus { border-color: rgba(236,72,153,.4); box-shadow: 0 0 0 4px rgba(236,72,153,.08); }
        .select option { background: #111120; }
        .plan-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .plan-card {
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
            border-radius: 22px;
            padding: 1rem;
        }
        .plan-top { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: .85rem; }
        .plan-name { font-size: 1.05rem; font-weight: 800; }
        .plan-price { color: #fff; font-weight: 800; font-size: 1rem; }
        .mini { font-size: .74rem; color: var(--muted); line-height: 1.5; }
        .chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .67rem;
            color: var(--muted);
        }
        .pill-wrap { display: flex; flex-wrap: wrap; gap: .45rem; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            border-radius: 999px;
            padding: .3rem .65rem;
            font-size: .69rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .pill-on { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.22); color: #6ee7b7; }
        .pill-off { background: rgba(255,255,255,.04); border-color: var(--border); color: rgba(255,255,255,.38); }
        .pill-admin { background: rgba(236,72,153,.12); border-color: rgba(236,72,153,.26); color: #f9a8d4; }
        .table-shell { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1180px; }
        thead th { text-align: left; padding: .8rem .85rem; font-size: .7rem; color: var(--muted); text-transform: uppercase; letter-spacing: .09em; border-bottom: 1px solid var(--border); }
        tbody td { padding: 1rem .85rem; border-bottom: 1px solid rgba(255,255,255,.05); vertical-align: top; font-size: .84rem; }
        tbody tr:hover td { background: rgba(255,255,255,.02); }
        .user-card { display: flex; align-items: center; gap: .75rem; }
        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            font-weight: 900;
            color: white;
            background: var(--grad);
            box-shadow: 0 10px 28px rgba(168,85,247,.2);
        }
        .user-name { font-weight: 800; }
        .user-meta { color: var(--muted); font-size: .76rem; margin-top: .15rem; }
        .stack { display: flex; flex-direction: column; gap: .35rem; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            padding: .25rem .65rem;
            font-size: .68rem;
            font-weight: 800;
            border: 1px solid transparent;
        }
        .badge-green { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.2); color: #6ee7b7; }
        .badge-gray { background: rgba(255,255,255,.05); border-color: var(--border); color: var(--muted); }
        .badge-blue { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.2); color: #93c5fd; }
        .badge-pink { background: rgba(236,72,153,.12); border-color: rgba(236,72,153,.25); color: #f9a8d4; }
        .badge-amber { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.25); color: #fcd34d; }
        .grant-form, .action-stack { display: grid; gap: .55rem; }
        .card-note {
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.03);
            padding: .75rem .85rem;
        }
        .empty { text-align: center; color: var(--muted); padding: 2.5rem 1rem; }
        .pagination { margin-top: 1rem; }
        .pagination nav { display: flex; justify-content: center; }
        .pagination svg { width: 1rem; height: 1rem; }
        @media (max-width: 1200px) {
            .stats-grid, .plan-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 900px) {
            .access-center-page { padding: 1rem .75rem max(7.25rem, env(safe-area-inset-bottom)); }
            .stats-grid, .plan-grid, .filter-bar { grid-template-columns: 1fr; }
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

<div class="access-center-page">
    @include('partials.plan-center-nav', [
        'scope' => $isAdminView ? 'admin' : 'account',
        'title' => $isAdminView ? 'Usuarios, planos e permissoes' : 'Meu acesso e permissoes',
        'subtitle' => $isAdminView
            ? 'Tudo agora fica dentro do layout principal, com a mesma sidebar, tab bar e navegação da area de planos.'
            : 'Aqui voce ve apenas os dados da sua propria conta e os recursos liberados no seu plano atual.',
    ])

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="hero">
            <div>
                <span class="hero-kicker">{{ $isAdminView ? 'Painel de acesso' : 'Central da conta' }}</span>
                <div class="hero-title">{{ $heroTitle }}</div>
                <div class="hero-sub">{{ $heroSub }}</div>
            </div>

            <div class="hero-actions">
                @if($isAdminView)
                    <a href="{{ route('admin.plans.index') }}" class="btn btn-ghost">Editar planos</a>
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-primary">Ver assinaturas</a>
                @else
                    <a href="{{ route('settings.plan') }}" class="btn btn-ghost">Abrir configuracoes</a>
                    <a href="{{ route('subscription.plans') }}" class="btn btn-primary">Trocar plano</a>
                @endif
            </div>
        </div>

        <section class="stats-grid">
            @foreach($statCards as $card)
                <article class="stat-card">
                    <div class="stat-label">{{ $card['label'] }}</div>
                    <div class="stat-value" style="color:{{ $card['tone'] }}">{{ $card['value'] }}</div>
                    <div class="stat-sub">{{ $card['sub'] }}</div>
                </article>
            @endforeach
        </section>

        @if(!$isAdminView)
            <section class="spotlight">
                <div class="section-title">Seu escopo esta isolado</div>
                <div class="section-sub">Voce pode consultar o plano ativo, recursos liberados e fazer upgrade da propria conta. Alteracoes de nivel admin e mudancas de plano de terceiros continuam restritas aos administradores.</div>
            </section>
        @endif

        @if($isAdminView)
            <section class="section">
                <div class="section-head">
                    <div>
                        <div class="section-title">Filtro rapido</div>
                        <div class="section-sub">Busque por nome, email ou veja apenas usuarios de um plano especifico.</div>
                    </div>
                </div>

                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="field" placeholder="Buscar por nome ou email" value="{{ request('search') }}">
                    <select name="plan" class="select">
                        <option value="">Todos os planos</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <div style="display:flex;gap:.65rem;flex-wrap:wrap">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        @if(request()->filled('search') || request()->filled('plan'))
                            <a href="{{ route('admin.plans.users') }}" class="btn btn-ghost">Limpar</a>
                        @endif
                    </div>
                </form>
            </section>
        @endif

        <section class="section">
            <div class="section-head">
                <div>
                    <div class="section-title">Permissoes liberadas por plano</div>
                    <div class="section-sub">Cada card resume o que o plano libera no sistema. A mudanca de plano reflete automaticamente nas permissoes efetivas do usuario.</div>
                </div>
            </div>

            <div class="plan-grid">
                @foreach($plans as $plan)
                    <article class="plan-card">
                        <div class="plan-top">
                            <div>
                                <div class="plan-name">{{ $plan->name }}</div>
                                <div class="mini">/{{ $plan->slug }}</div>
                            </div>
                            <div style="text-align:right">
                                <div class="plan-price">{{ $plan->formattedPriceMonthly() }}</div>
                                <div class="mini">por mes</div>
                            </div>
                        </div>

                        <div class="pill-wrap" style="margin-bottom:.8rem">
                            @if($plan->is_default)
                                <span class="chip">Padrao</span>
                            @endif
                            @if($plan->badge_label)
                                <span class="chip">{{ $plan->badge_label }}</span>
                            @endif
                            <span class="chip">{{ $plan->max_users }} usuario(s)</span>
                            <span class="chip">{{ $plan->max_products === -1 ? 'Produtos ilimitados' : $plan->max_products . ' produtos' }}</span>
                        </div>

                        <div class="pill-wrap">
                            @foreach($plan->permissionMatrix() as $permission)
                                <span class="pill {{ $permission['enabled'] ? 'pill-on' : 'pill-off' }}">
                                    <span>{{ $permission['enabled'] ? '●' : '○' }}</span>
                                    {{ $permission['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section">
            <div class="section-head">
                <div>
                    <div class="section-title">{{ $isAdminView ? 'Mapa de acesso por usuario' : 'Resumo detalhado da sua conta' }}</div>
                    <div class="section-sub">
                        {{ $isAdminView
                            ? 'Os admins podem trocar o plano de qualquer usuario, promover ou rebaixar acesso administrativo e revogar assinaturas quando necessario.'
                            : 'Voce pode acompanhar o status da sua assinatura, conferir as permissoes ativas e seguir para a troca do proprio plano.' }}
                    </div>
                </div>
            </div>

            <div class="table-shell">
                <table>
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
                                    'green' => 'badge-green',
                                    'blue' => 'badge-blue',
                                    'orange' => 'badge-amber',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="user-card">
                                        <div class="avatar">{{ $user->initials() }}</div>
                                        <div>
                                            <div class="user-name">{{ $user->name }}</div>
                                            <div class="user-meta">{{ $user->email }} · ID {{ $user->id }}</div>
                                            <div class="pill-wrap" style="margin-top:.4rem">
                                                @if($user->is_admin)
                                                    <span class="badge badge-pink">Admin</span>
                                                @endif
                                                @if($user->id === auth()->id())
                                                    <span class="badge badge-blue">Conta atual</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="stack">
                                        <div style="font-weight:800">{{ $currentPlan?->name ?? 'Sem plano' }}</div>
                                        <div class="mini">/{{ $currentPlan?->slug ?? 'nao-definido' }}</div>
                                        <div class="pill-wrap">
                                            @if($currentPlan?->is_default)
                                                <span class="chip">Padrao</span>
                                            @endif
                                            <span class="chip">{{ $currentPlan?->max_users ?? 1 }} usuario(s)</span>
                                            <span class="chip">{{ $activeSubscription?->billingCycleLabel() ?? 'Sem ciclo' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="stack">
                                        @if($activeSubscription)
                                            <span class="badge {{ $statusBadge }}">{{ $activeSubscription->statusLabel() }}</span>
                                            <div class="mini">{{ $activeSubscription->gatewayLabel() }} · {{ $activeSubscription->billingCycleLabel() }}</div>
                                            <div class="mini">
                                                {{ $activeSubscription->isTrialing()
                                                    ? 'Trial ate ' . $activeSubscription->trial_ends_at?->format('d/m/Y')
                                                    : 'Vigencia ate ' . ($activeSubscription->current_period_end?->format('d/m/Y') ?? 'sem prazo') }}
                                            </div>
                                        @else
                                            <span class="badge badge-gray">Sem assinatura ativa</span>
                                            <div class="mini">Usando o plano padrao do sistema.</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="pill-wrap">
                                        @foreach($permissions as $permission)
                                            <span class="pill {{ $permission['source'] === 'admin' ? 'pill-admin' : ($permission['enabled'] ? 'pill-on' : 'pill-off') }}">
                                                <span>{{ $permission['enabled'] ? '●' : '○' }}</span>
                                                {{ $permission['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($isAdminView)
                                        <form method="POST" action="{{ route('admin.plans.grant') }}" class="grant-form">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <select name="plan_id" class="select" required>
                                                @foreach($plans as $plan)
                                                    <option value="{{ $plan->id }}" {{ $currentPlan && $currentPlan->id === $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="billing_cycle" class="select" required>
                                                <option value="monthly" {{ ($activeSubscription?->billing_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Mensal</option>
                                                <option value="annual" {{ ($activeSubscription?->billing_cycle ?? 'monthly') === 'annual' ? 'selected' : '' }}>Anual</option>
                                            </select>
                                            <textarea name="notes" rows="2" class="field" placeholder="Observacao interna sobre a mudanca">{{ $activeSubscription?->notes }}</textarea>
                                            <button type="submit" class="btn btn-primary btn-sm">Salvar plano</button>
                                        </form>
                                    @else
                                        <div class="action-stack">
                                            <div class="card-note">
                                                <div style="font-weight:800">Voce altera apenas o proprio plano</div>
                                                <div class="mini">Use o fluxo de assinatura para upgrade, downgrade ou cancelamento.</div>
                                            </div>
                                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-sm">Escolher outro plano</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-stack">
                                        @if($isAdminView)
                                            <form method="POST" action="{{ route('admin.users.toggle-admin') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn {{ $user->is_admin ? 'btn-ghost' : 'btn-primary' }} btn-sm" style="width:100%">
                                                    {{ $user->is_admin ? 'Remover admin' : 'Tornar admin' }}
                                                </button>
                                            </form>

                                            @if($activeSubscription)
                                                <form method="POST" action="{{ route('admin.subscriptions.revoke', $activeSubscription) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Revogar acesso</button>
                                                </form>
                                            @endif

                                            @if($user->id === auth()->id() && $user->is_admin)
                                                <div class="mini">Seu proprio acesso admin continua protegido contra revogacao acidental.</div>
                                            @endif
                                        @else
                                            <a href="{{ route('settings.plan') }}" class="btn btn-ghost btn-sm">Ver configuracoes</a>
                                            <div class="mini">Nao ha acesso a dados de terceiros nesta area.</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">Nenhum usuario encontrado para os filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="pagination">{{ $users->links() }}</div>
            @endif
        </section>
</div>
</x-layouts.app>