<x-layouts.app title="Admin - Planos">
    <style>
        :root {
            --muted: #64748b;
            --border: rgba(148,163,184,.18);
            --text: #0f172a;
        }

        .plan-admin-page {
            width: 100%;
            padding: 1.25rem var(--app-fluid-padding, clamp(0.65rem, 1.2vw, 1rem)) max(7.5rem, env(safe-area-inset-bottom));
            color: #0f172a;
        }

        .dark .plan-admin-page {
            color: #f8fafc;
        }

        .plan-admin-page a {
            color: inherit;
            text-decoration: none;
        }

        .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .page-title { font-size: 1.4rem; font-weight: 900; letter-spacing: -.03em; }
        .page-sub { font-size: .84rem; color: #64748b; margin-top: .2rem; }
        .dark .page-sub { color: #94a3b8; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: .45rem; padding: .55rem 1.2rem; border-radius: 9px; font-size: .82rem; font-weight: 700; cursor: pointer; border: none; transition: all .18s; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #7c3aed); color: #fff; box-shadow: 0 12px 24px -18px rgba(79,70,229,.85); }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .btn-sm { padding: .35rem .75rem; font-size: .75rem; border-radius: 7px; }
        .btn-ghost { background: rgba(255,255,255,.78); color: #475569; border: 1px solid rgba(148,163,184,.24); }
        .btn-ghost:hover { background: rgba(255,255,255,.96); color: #0f172a; }
        .dark .btn-ghost { background: rgba(15,23,42,.62); color: #cbd5e1; border-color: rgba(71,85,105,.5); }
        .dark .btn-ghost:hover { background: rgba(30,41,59,.92); color: #fff; }
        .btn-danger { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .btn-danger:hover { background: rgba(239,68,68,.2); }
        .btn-success { background: rgba(16,185,129,.12); color: #34d399; border: 1px solid rgba(16,185,129,.2); }

        .stats-row { display: grid; grid-template-columns: repeat(4,1fr); gap: .75rem; margin-bottom: 1.25rem; }
        .stat-card { background: white; border: 1.5px solid #f1f5f9; border-radius: 1rem; padding: .95rem 1.1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03); }
        .dark .stat-card { background: #1e293b; border-color: #334155; }
        .stat-card-label { font-size: .72rem; color: #64748b; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; margin-bottom: .4rem; }
        .dark .stat-card-label { color: #94a3b8; }
        .stat-card-value { font-size: 1.9rem; font-weight: 900; letter-spacing: -.04em; }
        .stat-card-value.grad { background: linear-gradient(135deg, #3b82f6, #7c3aed); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-card-sub { font-size: .72rem; color: #94a3b8; margin-top: .25rem; }

        .table-card { background: white; border: 1.5px solid #f1f5f9; border-radius: 1.1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 24px rgba(0,0,0,.04); }
        .dark .table-card { background: #1e293b; border-color: #334155; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { min-width: 860px; }
        .table-card-header { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(148,163,184,.18); gap: 1rem; }
        .table-card-title { font-size: .95rem; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: .75rem 1.5rem; text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #64748b; border-bottom: 1px solid rgba(148,163,184,.18); background: rgba(248,250,252,.8); }
        .dark thead th { color: #94a3b8; background: rgba(15,23,42,.78); border-bottom-color: rgba(71,85,105,.46); }
        tbody td { padding: .9rem 1.5rem; border-bottom: 1px solid rgba(148,163,184,.12); font-size: .84rem; vertical-align: middle; }
        .dark tbody td { border-bottom-color: rgba(71,85,105,.38); }
        tbody tr:last-child td { border: none; }
        tbody tr:hover td { background: rgba(248,250,252,.78); }
        .dark tbody tr:hover td { background: rgba(30,41,59,.55); }

        /* Badges */
        .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .65rem; border-radius: 99px; font-size: .68rem; font-weight: 700; white-space: nowrap; }
        .badge-green { background: rgba(16,185,129,.12); color: #34d399; border: 1px solid rgba(16,185,129,.2); }
        .badge-gray  { background: rgba(148,163,184,.12); color: #64748b; border: 1px solid rgba(148,163,184,.2); }
        .badge-purple{ background: rgba(168,85,247,.12); color: #c084fc; border: 1px solid rgba(168,85,247,.2); }
        .badge-cyan  { background: rgba(6,182,212,.12); color: #22d3ee; border: 1px solid rgba(6,182,212,.2); }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

        /* Feature pill */
        .feat-yes { color: #34d399; font-size: .9rem; }
        .feat-no  { color: rgba(100,116,139,.45); font-size: .9rem; }

        /* Alerts */
        .alert { padding: .85rem 1.2rem; border-radius: 10px; font-size: .84rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: .6rem; }
        .alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.25); color: #34d399; }
        .alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.25);  color: #f87171; }
        .alert-warning { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25); color: #fbbf24; }

        /* Actions column */
        .actions { display: flex; gap: .4rem; flex-wrap: wrap; }

        /* Plan color dot */
        .plan-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* Responsive */
        .grant-card {
            margin-top: 1rem;
            background: white;
            border: 1.5px solid #f1f5f9;
            border-radius: 1.1rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03);
        }
        .dark .grant-card {
            background: #1e293b;
            border-color: #334155;
        }

        @media (max-width: 900px) {
            .plan-admin-page { padding: .85rem .65rem max(7.25rem, env(safe-area-inset-bottom)); }
            .stats-row { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 600px) {
            .stats-row { grid-template-columns: 1fr; }
            .page-header { align-items: stretch; }
            .page-header .btn { width: 100%; justify-content: center; }
        }
    </style>

    <div class="plan-admin-page w-full app-viewport-fit mobile-393-base">
        @include('partials.plan-center-nav', [
            'scope' => 'admin',
            'title' => 'Gerenciar planos',
            'subtitle' => 'Catálogo, métricas e concessão manual usando a mesma sidebar e tab bar do restante do app.',
        ])

        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✕ {{ session('error') }}</div>
        @endif

        <div class="page-header">
            <div>
                <div class="page-title">Gerenciar Planos</div>
                <div class="page-sub">Crie, edite e ative/desative os planos disponíveis no FlowManager.</div>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
                ＋ Novo Plano
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-label">Assinantes ativos</div>
                <div class="stat-card-value grad">{{ $stats['total_subscribers'] }}</div>
                <div class="stat-card-sub">Total inclui em teste</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Receita este mês</div>
                <div class="stat-card-value" style="color:#34d399">R$ {{ number_format($stats['revenue_this_month'], 2, ',', '.') }}</div>
                <div class="stat-card-sub">Soma dos pagamentos</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Em trial</div>
                <div class="stat-card-value" style="color:#60a5fa">{{ $stats['trialing'] }}</div>
                <div class="stat-card-sub">Período de teste ativo</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Cancelamentos/mês</div>
                <div class="stat-card-value" style="color:#f87171">{{ $stats['canceled_this_month'] }}</div>
                <div class="stat-card-sub">Cancelados neste mês</div>
            </div>
        </div>

        <!-- Planos table -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">Planos</div>
                <div style="font-size:.78rem;color:var(--muted)">{{ $plans->count() }} plano(s) cadastrado(s)</div>
            </div>
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Plano</th>
                        <th>Preço</th>
                        <th>Recursos</th>
                        <th>Assinantes</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.6rem">
                                @if($plan->color)
                                    <span class="plan-dot" style="background-color: {{ $plan->color }};"></span>
                                @else
                                    <span class="plan-dot" style="background-color: #64748b;"></span>
                                @endif
                                <div>
                                    <div style="font-weight:700">{{ $plan->name }}</div>
                                    <div style="font-size:.72rem;color:var(--muted)">/{{ $plan->slug }}</div>
                                </div>
                                @if($plan->badge_label)
                                    <span class="badge badge-purple">{{ $plan->badge_label }}</span>
                                @endif
                                @if($plan->is_default)
                                    <span class="badge badge-gray">Padrão</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600">{{ $plan->formattedPriceMonthly() }}<span style="font-size:.72rem;color:var(--muted)">/mês</span></div>
                            @if($plan->price_annual > 0)
                                <div style="font-size:.72rem;color:var(--muted)">R$ {{ number_format($plan->price_annual, 2, ',', '.') }}/mês anual ({{ $plan->annualSavingsPercent() }}% off)</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                @if($plan->has_ml_integration)   <span class="badge badge-cyan">ML</span>    @endif
                                @if($plan->has_ai_features)       <span class="badge badge-purple">IA</span>   @endif
                                @if($plan->has_advanced_reports)  <span class="badge badge-green">Reports</span> @endif
                                @if($plan->has_api_access)        <span class="badge badge-gray">API</span>   @endif
                                @if($plan->has_priority_support)  <span class="badge badge-gray">24/7</span>  @endif
                                <span style="font-size:.72rem;color:var(--muted);align-self:center">
                                    {{ $plan->max_products === -1 ? '∞' : $plan->max_products }} prod.
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:1rem">{{ $plan->active_subscriptions_count ?? 0 }}</div>
                            <div style="font-size:.72rem;color:var(--muted)">assinantes ativos</div>
                        </td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge badge-green"><span class="badge-dot"></span>Ativo</span>
                            @else
                                <span class="badge badge-gray"><span class="badge-dot"></span>Inativo</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-ghost">Editar</a>
                                <form method="POST" action="{{ route('admin.plans.toggle', $plan) }}" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $plan->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $plan->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                @if(($plan->active_subscriptions_count ?? 0) == 0)
                                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" style="display:inline" onsubmit="return confirm('Excluir plano {{ $plan->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Excluir</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:2rem">
                            Nenhum plano cadastrado. <a href="{{ route('admin.plans.create') }}" style="color:#c084fc">Criar primeiro plano →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Grant Manual Access -->
        <div class="grant-card">
            <div style="font-size:.95rem;font-weight:700;margin-bottom:1rem">🔑 Conceder Acesso Manual</div>
            <form method="POST" action="{{ route('admin.plans.grant') }}" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
                @csrf
                <div style="flex:1;min-width:200px">
                    <label style="font-size:.72rem;color:var(--muted);font-weight:700;display:block;margin-bottom:.35rem">USUÁRIO</label>
                    <input type="text" name="user_id" placeholder="ID do usuário" required
                        style="width:100%;background:rgba(255,255,255,.72);border:1px solid rgba(148,163,184,.24);border-radius:8px;padding:.55rem .9rem;color:#0f172a;font-size:.84rem;outline:none">
                </div>
                <div style="flex:1;min-width:180px">
                    <label style="font-size:.72rem;color:var(--muted);font-weight:700;display:block;margin-bottom:.35rem">PLANO</label>
                    <select name="plan_id" required
                        style="width:100%;background:rgba(255,255,255,.72);border:1px solid rgba(148,163,184,.24);border-radius:8px;padding:.55rem .9rem;color:#0f172a;font-size:.84rem;outline:none">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" style="background:#111120">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:140px">
                    <label style="font-size:.72rem;color:var(--muted);font-weight:700;display:block;margin-bottom:.35rem">CICLO</label>
                    <select name="billing_cycle" style="width:100%;background:rgba(255,255,255,.72);border:1px solid rgba(148,163,184,.24);border-radius:8px;padding:.55rem .9rem;color:#0f172a;font-size:.84rem;outline:none">
                        <option value="monthly" style="background:#111120">Mensal</option>
                        <option value="annual" style="background:#111120">Anual</option>
                    </select>
                </div>
                <div style="flex:2;min-width:200px">
                    <label style="font-size:.72rem;color:var(--muted);font-weight:700;display:block;margin-bottom:.35rem">OBSERVAÇÕES (opcional)</label>
                    <input type="text" name="notes" placeholder="Ex: Parceria, cortesia..."
                        style="width:100%;background:rgba(255,255,255,.72);border:1px solid rgba(148,163,184,.24);border-radius:8px;padding:.55rem .9rem;color:#0f172a;font-size:.84rem;outline:none">
                </div>
                <button class="btn btn-primary" type="submit">Conceder Acesso</button>
            </form>
        </div>

    </div>
</x-layouts.app>
