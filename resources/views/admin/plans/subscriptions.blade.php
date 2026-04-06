<x-layouts.app title="Admin - Assinaturas">
    <style>
        :root {
            --plan-muted: #64748b;
            --plan-border: rgba(148,163,184,.18);
            --plan-border-dark: rgba(71,85,105,.48);
            --muted: #64748b;
            --border: rgba(148,163,184,.18);
            --text: #0f172a;
        }

        .plan-subscriptions-page {
            width: 100%;
            padding: 1.25rem var(--app-fluid-padding, clamp(0.65rem, 1.2vw, 1rem)) max(7.5rem, env(safe-area-inset-bottom));
            color: #0f172a;
        }

        .dark .plan-subscriptions-page { color: #f8fafc; }
        .plan-subscriptions-page a { color: inherit; text-decoration: none; }
        .page-title { font-size:1.5rem;font-weight:900;margin-bottom:.2rem;letter-spacing:-.03em; }
        .page-sub { font-size:.84rem;color:var(--plan-muted); }
        .dark .page-sub { color:#94a3b8; }
        .btn { display:inline-flex;align-items:center;gap:.45rem;padding:.5rem 1rem;border-radius:9px;font-size:.8rem;font-weight:700;cursor:pointer;border:none;transition:all .18s; }
        .btn-primary { background:linear-gradient(135deg, #3b82f6, #7c3aed);color:#fff; }
        .btn-sm { padding:.3rem .7rem;font-size:.73rem;border-radius:7px; }
        .btn-ghost { background:rgba(255,255,255,.78);color:#475569;border:1px solid var(--plan-border); }
        .btn-ghost:hover { background:rgba(255,255,255,.96); }
        .dark .btn-ghost { background:rgba(15,23,42,.62);color:#cbd5e1;border-color:var(--plan-border-dark); }
        .btn-danger { background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2); }
        .btn-danger:hover { background:rgba(239,68,68,.2); }
        .filter-bar { display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1.5rem; }
        .filter-input { background:rgba(255,255,255,.8);border:1px solid var(--plan-border);border-radius:8px;padding:.5rem .9rem;color:#0f172a;font-size:.82rem;outline:none;min-width:200px; }
        .dark .filter-input { background:rgba(15,23,42,.62);border-color:var(--plan-border-dark);color:#f8fafc; }
        .filter-input:focus { border-color:#a855f7; }
        select.filter-input option { background:#111120; }
        .table-card { background: white; border: 1.5px solid #f1f5f9; border-radius: 1.1rem; overflow:hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 24px rgba(0,0,0,.04); }
        .dark .table-card { background: #1e293b; border-color: #334155; }
        .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        table { width:100%; min-width:920px; border-collapse:collapse; }
        thead th { padding:.75rem 1.25rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--plan-muted);border-bottom:1px solid var(--plan-border);background:rgba(248,250,252,.8); }
        .dark thead th { color:#94a3b8;border-bottom-color:var(--plan-border-dark);background:rgba(15,23,42,.78); }
        tbody td { padding:.8rem 1.25rem;border-bottom:1px solid rgba(148,163,184,.12);font-size:.83rem;vertical-align:middle; }
        .dark tbody td { border-bottom-color:rgba(71,85,105,.38); }
        tbody tr:last-child td { border:none; }
        tbody tr:hover td { background:rgba(248,250,252,.78); }
        .dark tbody tr:hover td { background:rgba(30,41,59,.55); }
        .badge { display:inline-flex;align-items:center;gap:.3rem;padding:.18rem .6rem;border-radius:99px;font-size:.67rem;font-weight:700;white-space:nowrap; }
        .badge-green { background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2); }
        .badge-yellow { background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.2); }
        .badge-red { background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2); }
        .badge-gray { background:rgba(148,163,184,.12);color:var(--plan-muted);border:1px solid rgba(148,163,184,.2); }
        .badge-blue { background:rgba(96,165,250,.12);color:#93c5fd;border:1px solid rgba(96,165,250,.2); }
        .badge-dot { width:5px;height:5px;border-radius:50%;background:currentColor; }
        .mini-input {
            width:52px;
            background:rgba(255,255,255,.8);
            border:1px solid var(--plan-border);
            border-radius:6px;
            padding:.28rem .45rem;
            color:#0f172a;
            font-size:.73rem;
            outline:none;
            text-align:center;
        }
        .dark .mini-input {
            background:rgba(15,23,42,.62);
            border-color:var(--plan-border-dark);
            color:#f8fafc;
        }
        .pagination-wrap {
            margin-top: 1.25rem;
        }
        .alert { padding:.85rem 1.2rem;border-radius:10px;font-size:.84rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.6rem; }
        .alert-success { background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#34d399; }
        .alert-error { background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171; }

        @media (max-width: 900px) {
            .plan-subscriptions-page { padding: .85rem .65rem max(7.25rem, env(safe-area-inset-bottom)); }
            .filter-input { min-width: 0; flex: 1 1 100%; }
        }
        @media (max-width: 600px) {
            .filter-bar > * { width: 100%; }
            .btn { justify-content: center; }
        }
    </style>

    <div class="plan-subscriptions-page w-full app-viewport-fit mobile-393-base">
        @include('partials.plan-center-nav', [
            'scope' => 'admin',
            'title' => 'Assinaturas ativas e históricas',
            'subtitle' => 'Filtro, acompanhamento e ações rápidas usando a sidebar principal e a tab bar do app.',
        ])

        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✕ {{ session('error') }}</div>
        @endif

        <div style="margin-bottom:1.75rem">
            <div class="page-title">Assinaturas</div>
            <div class="page-sub">{{ $subscriptions->total() }} assinatura(s) encontrada(s)</div>
        </div>

        <form method="GET" class="filter-bar">
            <input type="text" name="search" class="filter-input" placeholder="Buscar usuário..." value="{{ request('search') }}">
            <select name="status" class="filter-input">
                <option value="">Todos os status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Em trial</option>
                <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Cancelado</option>
                <option value="past_due" {{ request('status') === 'past_due' ? 'selected' : '' }}>Pagamento atrasado</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirado</option>
            </select>
            <select name="plan" class="filter-input">
                <option value="">Todos os planos</option>
                @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ request('plan') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-ghost" type="submit">Filtrar</button>
            @if(request()->anyFilled(['search','status','plan']))
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>

        <div class="table-card">
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Plano</th>
                        <th>Status</th>
                        <th>Gateway</th>
                        <th>Valor</th>
                        <th>Validade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $sub->user->name }}</div>
                            <div style="font-size:.72rem;color:var(--muted)">{{ $sub->user->email }}</div>
                        </td>
                        <td>{{ $sub->plan->name ?? '—' }}</td>
                        <td>
                            @php
                                $colors = [
                                    'active' => 'badge-green',
                                    'trialing' => 'badge-yellow',
                                    'canceled' => 'badge-red',
                                    'past_due' => 'badge-red',
                                    'expired' => 'badge-gray',
                                    'paused' => 'badge-blue',
                                ];
                                $cls = $colors[$sub->status] ?? 'badge-gray';
                            @endphp
                            <span class="badge {{ $cls }}">
                                <span class="badge-dot"></span>
                                {{ $sub->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size:.78rem;color:var(--muted)">{{ $sub->gatewayLabel() }}</span>
                        </td>
                        <td>R$ {{ number_format($sub->price_paid, 2, ',', '.') }}</td>
                        <td>
                            @if($sub->current_period_end)
                                <div style="font-size:.8rem">{{ $sub->current_period_end->format('d/m/Y') }}</div>
                                @if($sub->daysLeft() <= 7 && $sub->daysLeft() > 0)
                                    <div style="font-size:.7rem;color:#fbbf24">{{ $sub->daysLeft() }} dias restantes</div>
                                @elseif($sub->daysLeft() <= 0)
                                    <div style="font-size:.7rem;color:#f87171">Expirado</div>
                                @else
                                    <div style="font-size:.7rem;color:var(--muted)">{{ $sub->daysLeft() }} dias</div>
                                @endif
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.35rem;flex-wrap:wrap">
                                @if(in_array($sub->status, ['active', 'trialing']))
                                    <form method="POST" action="{{ route('admin.subscriptions.revoke', $sub) }}" onsubmit="return confirm('Revogar acesso de {{ $sub->user->name }}?')">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">Revogar</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.subscriptions.extend', $sub) }}" style="display:flex;gap:.35rem">
                                    @csrf
                                    <input type="number" name="days" value="30" min="1" max="365" class="mini-input">
                                    <button class="btn btn-sm btn-ghost" title="Estender por X dias">+dias</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:2.5rem">Nenhuma assinatura encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        @if($subscriptions->hasPages())
            <div class="pagination-wrap">
                {{ $subscriptions->onEachSide(1)->links() }}
            </div>
        @endif

    </div>
</x-layouts.app>
