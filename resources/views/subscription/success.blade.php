<x-layouts.app title="Assinatura ativada">
    <style>
        .success-shell { width: 100%; padding: 1.25rem var(--app-fluid-padding, clamp(0.65rem, 1.2vw, 1rem)) max(7.5rem, env(safe-area-inset-bottom)); color:#0f172a; }
        .dark .success-shell { color:#f8fafc; }
        .success-card { max-width: 720px; margin: 0 auto; padding: 1.5rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 6px 24px rgba(0,0,0,.04); }
        .dark .success-card { background: #1e293b; border-color: #334155; box-shadow: 0 1px 3px rgba(0,0,0,.1), 0 6px 24px rgba(0,0,0,.12); }
        .success-icon { font-size: 2.75rem; margin-bottom: .75rem; animation: pop .5s cubic-bezier(.34,1.56,.64,1); display: block; text-align: center; }
        @keyframes pop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-title { font-size: 1.6rem; font-weight: 900; letter-spacing: -.04em; margin-bottom: .35rem; text-align: center; }
        .success-title span { background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .success-sub { font-size: 1rem; color: #64748b; margin-bottom: 2rem; }
        .dark .success-sub { color:#94a3b8; }
        .plan-badge { display: inline-block; padding: .45rem 1.4rem; border-radius: 99px; font-size: .95rem; font-weight: 800; margin-bottom: 1.5rem; }
        .plan-badge.grad { background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%); color: #fff; box-shadow: 0 4px 20px rgba(79,70,229,.3); }
        .row { display: grid; grid-template-columns: repeat(4, 1fr); gap: .65rem; margin-bottom: 1.5rem; }
        .info-box { background: rgba(248,250,252,.8); border: 1.5px solid #f1f5f9; border-radius: .85rem; padding: .75rem .85rem; }
        .dark .info-box { background: rgba(30,41,59,.5); border-color: #334155; }
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
            .success-shell { padding: .85rem .65rem max(7.5rem, env(safe-area-inset-bottom)); }
            .success-card { padding: 1rem; border-radius: 1rem; }
            .row { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 520px) {
            .btns { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>

<div class="success-shell w-full app-viewport-fit mobile-393-base">
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
