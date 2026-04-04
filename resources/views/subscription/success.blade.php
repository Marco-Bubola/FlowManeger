<x-layouts.app title="Assinatura ativada">
    <style>
        .success-shell { max-width: 980px; margin: 0 auto; width: 100%; padding: 1.5rem 1rem max(7.5rem, env(safe-area-inset-bottom)); color:#0f172a; }
        .dark .success-shell { color:#f8fafc; }
        .success-card { text-align: center; max-width: 560px; margin: 0 auto; padding: 2rem; background: rgba(255,255,255,.84); border:1px solid rgba(148,163,184,.18); border-radius: 1.5rem; box-shadow: 0 18px 32px -26px rgba(15,23,42,.22); }
        .dark .success-card { background: rgba(15,23,42,.78); border-color: rgba(71,85,105,.48); box-shadow: 0 22px 36px -24px rgba(2,6,23,.76); }
        .success-icon { font-size: 4rem; margin-bottom: 1rem; animation: pop .5s cubic-bezier(.34,1.56,.64,1); }
        @keyframes pop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-title { font-size: 2rem; font-weight: 900; letter-spacing: -.04em; margin-bottom: .5rem; }
        .success-title span { background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .success-sub { font-size: 1rem; color: #64748b; margin-bottom: 2rem; }
        .dark .success-sub { color:#94a3b8; }
        .plan-badge { display: inline-block; padding: .45rem 1.4rem; border-radius: 99px; font-size: .95rem; font-weight: 800; margin-bottom: 1.5rem; }
        .plan-badge.grad { background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%); color: #fff; box-shadow: 0 4px 20px rgba(79,70,229,.3); }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: 2rem; }
        .info-box { background: rgba(248,250,252,.78); border: 1px solid rgba(148,163,184,.18); border-radius: 12px; padding: 1rem; }
        .dark .info-box { background: rgba(15,23,42,.62); border-color: rgba(71,85,105,.48); }
        .info-box-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #64748b; margin-bottom: .3rem; }
        .dark .info-box-label { color:#94a3b8; }
        .info-box-val { font-size: .95rem; font-weight: 700; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .75rem 2rem; border-radius: 12px; font-size: .9rem; font-weight: 700; cursor: pointer; border: none; transition: all .18s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%); color: #fff; box-shadow: 0 2px 20px rgba(79,70,229,.35); }
        .btn-primary:hover { opacity: .9; }
        .btn-ghost { background: rgba(255,255,255,.78); color: #475569; border: 1px solid rgba(148,163,184,.18); }
        .btn-ghost:hover { background: rgba(255,255,255,.96); }
        .dark .btn-ghost { background: rgba(15,23,42,.62); color:#cbd5e1; border-color: rgba(71,85,105,.48); }
        .btns { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
        @media (max-width: 768px) {
            .success-shell { padding: 1rem .75rem max(7.5rem, env(safe-area-inset-bottom)); }
            .success-card { padding: 1.5rem 1rem; border-radius: 1.25rem; }
            .row { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) {
            .btns { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>

<div class="success-shell">
    @include('partials.plan-center-nav', [
        'scope' => 'account',
        'title' => 'Plano ativado com sucesso',
        'subtitle' => 'O pós-checkout agora continua dentro do mesmo layout principal do FlowManager.',
    ])

<div class="success-card">
    <div class="success-icon">🎉</div>

    <h1 class="success-title">
        Bem-vindo ao <span>{{ $activePlan->name ?? 'FlowManager' }}</span>!
    </h1>

    @if($activatedPlan)
        <p class="success-sub">Seu plano <strong>{{ $activatedPlan }}</strong> foi ativado com sucesso.</p>
    @else
        <p class="success-sub">Sua assinatura está ativa e pronta para usar.</p>
    @endif

    @if($activeSub)
        <div class="plan-badge grad">{{ $activePlan->name ?? 'Plano' }}</div>

        <div class="row">
            <div class="info-box">
                <div class="info-box-label">Status</div>
                <div class="info-box-val" style="color:#34d399">{{ $activeSub->statusLabel() }}</div>
            </div>
            <div class="info-box">
                <div class="info-box-label">Ciclo</div>
                <div class="info-box-val">{{ $activeSub->billingCycleLabel() }}</div>
            </div>
            <div class="info-box">
                <div class="info-box-label">Válido até</div>
                <div class="info-box-val">{{ $activeSub->current_period_end?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="info-box">
                <div class="info-box-label">Valor pago</div>
                <div class="info-box-val">R$ {{ number_format($activeSub->price_paid, 2, ',', '.') }}</div>
            </div>
        </div>
    @endif

    <div class="btns">
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Ir para o Dashboard</a>
        <a href="{{ route('settings.plan') }}" class="btn btn-ghost">Ver meu plano</a>
    </div>
</div>
</div>
</x-layouts.app>
