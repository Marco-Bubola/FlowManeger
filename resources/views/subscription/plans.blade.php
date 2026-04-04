<x-layouts.app title="Planos e assinatura">
    <style>
        :root {
            --catalog-muted: #64748b;
            --catalog-border: rgba(148,163,184,.18);
            --catalog-border-dark: rgba(71,85,105,.48);
            --catalog-grad: linear-gradient(135deg, #3b82f6 0%, #7c3aed 55%, #06b6d4 100%);
        }
        .page {
            --muted: #64748b;
            --border: rgba(148,163,184,.18);
            --card: rgba(255,255,255,.84);
        }

        .page { max-width: 1120px; margin: 0 auto; width: 100%; padding: 1.5rem 1rem max(7.5rem, env(safe-area-inset-bottom)); color:#0f172a; }
        .dark .page { color:#f8fafc; }
        .back-link { font-size: .8rem; color: var(--catalog-muted); display: inline-flex; align-items: center; gap: .35rem; margin-bottom: 1.25rem; transition: color .15s; }
        .back-link:hover { color: #312e81; }
        .dark .back-link { color:#94a3b8; }

        /* Header */
        .page-header { text-align: center; margin-bottom: 3rem; }
        .badge-current { display: inline-flex; align-items: center; gap: .4rem; padding: .25rem .8rem; background: rgba(168,85,247,.15); border: 1px solid rgba(168,85,247,.3); border-radius: 99px; font-size: .75rem; font-weight: 700; color: #c084fc; margin-bottom: 1rem; }
        .page-title { font-size: clamp(1.8rem,4vw,2.8rem); font-weight: 900; letter-spacing: -.04em; }
        .page-title span { background: var(--catalog-grad); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .page-sub { font-size: 1rem; color: var(--catalog-muted); margin-top: .6rem; }
        .dark .page-sub { color:#94a3b8; }

        /* Toggle */
        .billing-toggle { display: flex; align-items: center; justify-content: center; gap: .75rem; margin: 2rem 0; }
        .toggle-pill { display: flex; background: rgba(255,255,255,.75); border: 1px solid var(--catalog-border); border-radius: 99px; padding: 3px; gap: 2px; }
        .dark .toggle-pill { background: rgba(15,23,42,.62); border-color: var(--catalog-border-dark); }
        .toggle-btn { padding: .4rem 1.1rem; border-radius: 99px; font-size: .82rem; font-weight: 700; cursor: pointer; border: none; background: transparent; color: var(--muted); transition: all .2s; }
        .toggle-btn.active { background: #a855f7; color: #fff; box-shadow: 0 2px 12px rgba(168,85,247,.4); }
        .toggle-badge { font-size: .7rem; font-weight: 800; padding: .15rem .5rem; border-radius: 99px; background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(16,185,129,.25); }

        /* Cards grid */
        .plans-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 1.5rem; }

        /* Plan card */
        .plan-card {
            background: rgba(255,255,255,.84); border: 1px solid var(--catalog-border);
            border-radius: 22px; padding: 1.75rem 1.5rem 2rem;
            display: flex; flex-direction: column; position: relative; transition: transform .2s, border-color .2s;
        }
        .dark .plan-card { background: rgba(15,23,42,.78); border-color: var(--catalog-border-dark); }
        .plan-card:hover { transform: translateY(-4px); border-color: rgba(255,255,255,.15); }
        .plan-card.popular { border-color: rgba(168,85,247,.4); background: rgba(168,85,247,.06); }
        .plan-card.popular::before {
            content: attr(data-badge);
            position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
            background: var(--grad); color: #fff; font-size: .68rem; font-weight: 800;
            padding: .2rem .9rem; border-radius: 99px; white-space: nowrap;
            box-shadow: 0 2px 12px rgba(168,85,247,.4);
        }
        .plan-card.current-plan { border-color: rgba(6,182,212,.4); background: rgba(6,182,212,.04); }

        .plan-name { font-size: 1rem; font-weight: 700; margin-bottom: .2rem; }
        .plan-desc { font-size: .78rem; color: var(--catalog-muted); margin-bottom: 1.5rem; min-height: 2.5em; }
        .dark .plan-desc { color:#94a3b8; }
        .plan-price { margin-bottom: 1.5rem; }
        .plan-price-val { font-size: 2.4rem; font-weight: 900; letter-spacing: -.05em; }
        .plan-price-period { font-size: .78rem; color: var(--catalog-muted); }
        .plan-price-annual { font-size: .72rem; color: var(--catalog-muted); margin-top: .15rem; }
        .plan-price-free { font-size: 2rem; font-weight: 900; color: #34d399; }

        .plan-features { list-style: none; flex: 1; margin-bottom: 1.75rem; display: flex; flex-direction: column; gap: .5rem; }
        .plan-features li { display: flex; align-items: flex-start; gap: .55rem; font-size: .83rem; }
        .feat-icon.yes { color: #34d399; flex-shrink: 0; margin-top: 1px; }
        .feat-icon.no  { color: rgba(255,255,255,.2); flex-shrink: 0; margin-top: 1px; }

        /* Billing cycle form */
        .plan-cta form { width: 100%; }
        .billing-select { display: none; } /* hidden, toggled by JS */
        .plan-btn {
            width: 100%; padding: .75rem; border-radius: 12px; font-size: .88rem; font-weight: 700;
            cursor: pointer; border: none; transition: all .18s;
        }
        .plan-btn-primary { background: var(--catalog-grad); color: #fff; box-shadow: 0 2px 20px rgba(79,70,229,.35); }
        .plan-btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .plan-btn-outline { background: transparent; color: var(--catalog-muted); border: 1px solid var(--catalog-border); }
        .plan-btn-outline:hover { background: rgba(255,255,255,.05); color: #fff; }
        .plan-btn-current { background: rgba(6,182,212,.1); color: #22d3ee; border: 1px solid rgba(6,182,212,.25); cursor: default; }

        /* Gateway picker */
        .gateway-row { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; margin-bottom: .75rem; }
        .gw-opt { display: none; } /* hidden radio */
        .gw-label {
            display: flex; flex-direction: column; align-items: center; gap: .15rem;
            padding: .45rem .3rem; background: rgba(255,255,255,.72); border: 1px solid var(--catalog-border);
            border-radius: 9px; cursor: pointer; font-size: .68rem; font-weight: 600; color: var(--catalog-muted);
            transition: all .15s; text-align: center;
        }
        .dark .gw-label { background: rgba(15,23,42,.62); border-color: var(--catalog-border-dark); color:#cbd5e1; }
        .gw-label .icon { font-size: 1.1rem; }
        .gw-opt:checked + .gw-label { border-color: #a855f7; background: rgba(168,85,247,.1); color: #c084fc; }

        /* Alert */
        .alert { padding: .85rem 1.2rem; border-radius: 10px; font-size: .84rem; margin-bottom: 2rem; display: flex; align-items: center; gap: .6rem; }
        .alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.25); color: #34d399; }

        /* Comparison table */
        .compare-section { margin-top: 4rem; }
        .compare-title { font-size: 1.3rem; font-weight: 800; text-align: center; margin-bottom: 1.5rem; }
        .compare-table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 14px; }
        .compare-table { width: 100%; min-width: 720px; border-collapse: collapse; background: rgba(255,255,255,.84); border: 1px solid var(--catalog-border); border-radius: 14px; overflow: hidden; }
        .dark .compare-table { background: rgba(15,23,42,.78); border-color: var(--catalog-border-dark); }
        .compare-table th, .compare-table td { padding: .8rem 1.2rem; font-size: .82rem; border-bottom: 1px solid rgba(255,255,255,.04); text-align: center; }
        .compare-table th { font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--catalog-muted); background: rgba(248,250,252,.8); }
        .dark .compare-table th { color:#94a3b8; background: rgba(15,23,42,.78); }
        .compare-table th:first-child, .compare-table td:first-child { text-align: left; }
        .compare-table tr:last-child td { border: none; }
        .check { color: #34d399; }
        .cross { color: rgba(255,255,255,.2); }

        @media (max-width: 1024px) {
            .plans-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 860px) {
            .page { padding: 1rem .75rem max(7.5rem, env(safe-area-inset-bottom)); }
            .plans-grid { grid-template-columns: 1fr; max-width: 460px; margin: 0 auto; }
            .plan-card { padding: 1.35rem 1.1rem 1.5rem; }
            .compare-section { margin-top: 2.5rem; }
        }
        @media (max-width: 600px) {
            .page-header { margin-bottom: 2.1rem; }
            .billing-toggle { flex-direction: column; align-items: stretch; }
            .toggle-pill { width: 100%; justify-content: stretch; }
            .toggle-btn { flex: 1 1 0; }
            .gateway-row { grid-template-columns: 1fr 1fr; }
            .compare-title { text-align: left; }
        }
    </style>

<div class="page">
    @include('partials.plan-center-nav', [
        'scope' => 'account',
        'title' => 'Escolha o plano certo para você',
        'subtitle' => 'O catálogo agora usa a mesma sidebar principal e continua acessível junto da tab bar no mobile.',
    ])

    <a href="{{ url('/dashboard') }}" class="back-link">← Voltar ao Dashboard</a>

    @if(session('activated_plan'))
        <div class="alert alert-success">✓ Plano <strong>{{ session('activated_plan') }}</strong> ativado com sucesso!</div>
    @endif

    <div class="page-header">
        <div class="badge-current">
            Plano atual: <strong>{{ $currentPlan->name }}</strong>
        </div>
        <div class="page-title">Escolha o plano <span>certo para você</span></div>
        <div class="page-sub">Sem compromisso. Cancele quando quiser.</div>
    </div>

    <!-- Billing toggle -->
    <div class="billing-toggle">
        <div class="toggle-pill">
            <button class="toggle-btn active" id="btn-monthly" onclick="setBilling('monthly')">Mensal</button>
            <button class="toggle-btn" id="btn-annual" onclick="setBilling('annual')">Anual</button>
        </div>
        <span class="toggle-badge">até 20% off</span>
    </div>

    <!-- Plans grid -->
    <div class="plans-grid">
        @foreach($plans as $plan)
        @php
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
            $isPopular  = $plan->badge_label != null;
        @endphp
           <div class="plan-card {{ $isPopular ? 'popular' : '' }} {{ $isCurrent ? 'current-plan' : '' }}"
               data-badge="{{ $plan->badge_label }}"
               @if($plan->color) style="border-color: {{ $plan->color }}40;" @endif>

            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-desc">{{ $plan->description }}</div>

            <div class="plan-price">
                @if($plan->isFree())
                    <div class="plan-price-free">Grátis</div>
                    <div class="plan-price-period">sempre gratuito</div>
                @else
                    <div>
                        <span class="plan-price-val price-display" data-monthly="{{ $plan->price_monthly }}" data-annual="{{ $plan->price_annual }}">
                            R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}
                        </span>
                        <span class="plan-price-period">/mês</span>
                    </div>
                    <div class="plan-price-annual billing-note-monthly" style="display:block">cobrado mensalmente</div>
                    <div class="plan-price-annual billing-note-annual" style="display:none">
                        R$ {{ number_format($plan->price_annual * 12, 2, ',', '.') }}/ano · <span style="color:#34d399">{{ $plan->annualSavingsPercent() }}% economia</span>
                    </div>
                @endif
            </div>

            <ul class="plan-features">
                @php $features = $plan->featuresList(); @endphp
                @foreach($features as $feat)
                <li>
                    <span class="feat-icon {{ $feat['included'] ? 'yes' : 'no' }}">{{ $feat['included'] ? '✓' : '✕' }}</span>
                    <span {{ !$feat['included'] ? 'style=color:rgba(255,255,255,.35)' : '' }}>{{ $feat['label'] }}</span>
                </li>
                @endforeach
            </ul>

            <div class="plan-cta">
                @if($isCurrent)
                    <button class="plan-btn plan-btn-current" disabled>Plano atual</button>
                @elseif($plan->isFree())
                    <form method="POST" action="{{ route('subscription.checkout.process') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="billing_cycle" value="monthly">
                        <input type="hidden" name="gateway" value="manual">
                        <button class="plan-btn plan-btn-outline">Começar grátis</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('subscription.checkout.process') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="billing_cycle" class="billing-cycle-input" value="monthly">

                        <!-- Gateway picker -->
                        <div class="gateway-row">
                            <input type="radio" name="gateway" id="gw-stripe-{{ $plan->id }}"   class="gw-opt" value="stripe"      checked>
                            <label for="gw-stripe-{{ $plan->id }}" class="gw-label"><span class="icon">💳</span>Stripe</label>

                            <input type="radio" name="gateway" id="gw-mp-{{ $plan->id }}"       class="gw-opt" value="mercadopago">
                            <label for="gw-mp-{{ $plan->id }}" class="gw-label"><span class="icon">🛒</span>Mercado Pago</label>

                            <input type="radio" name="gateway" id="gw-ps-{{ $plan->id }}"       class="gw-opt" value="pagseguro">
                            <label for="gw-ps-{{ $plan->id }}" class="gw-label"><span class="icon">🏦</span>PagSeguro</label>

                            @if(auth()->user()->isAdmin())
                            <input type="radio" name="gateway" id="gw-manual-{{ $plan->id }}"   class="gw-opt" value="manual">
                            <label for="gw-manual-{{ $plan->id }}" class="gw-label"><span class="icon">🔑</span>Manual</label>
                            @endif
                        </div>

                        <button class="plan-btn plan-btn-primary">
                            {{ $plan->trial_days > 0 ? 'Começar trial grátis' : 'Assinar agora' }}
                        </button>
                    </form>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- Comparison table -->
    <div class="compare-section">
        <div class="compare-title">Comparação detalhada</div>
        <div class="compare-table-wrap">
        <table class="compare-table">
            <thead>
                <tr>
                    <th>Recurso</th>
                    @foreach($plans as $p)<th>{{ $p->name }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $rows = [
                    ['label' => 'Produtos',           'key' => 'max_products',            'format' => 'limit'],
                    ['label' => 'Pedidos/mês',        'key' => 'max_orders_per_month',     'format' => 'limit'],
                    ['label' => 'Clientes',            'key' => 'max_clients',              'format' => 'limit'],
                    ['label' => 'Usuários na equipe', 'key' => 'max_users',                'format' => 'limit'],
                    ['label' => 'Integ. Mercado Livre','key' => 'has_ml_integration',      'format' => 'bool'],
                    ['label' => 'Integ. Shopee',      'key' => 'has_shopee_integration',   'format' => 'bool'],
                    ['label' => 'Recursos de IA',     'key' => 'has_ai_features',           'format' => 'bool'],
                    ['label' => 'Relatórios avançados','key' => 'has_advanced_reports',    'format' => 'bool'],
                    ['label' => 'Contr. financeiro',  'key' => 'has_financial_control',    'format' => 'bool'],
                    ['label' => 'Acesso API',         'key' => 'has_api_access',            'format' => 'bool'],
                    ['label' => 'Suporte prioritário','key' => 'has_priority_support',     'format' => 'bool'],
                    ['label' => 'Export PDF/Excel',   'key' => 'has_export_pdf_excel',     'format' => 'bool'],
                ];
                @endphp
                @foreach($rows as $row)
                <tr>
                    <td style="font-weight:500">{{ $row['label'] }}</td>
                    @foreach($plans as $p)
                        @php $val = $p->{$row['key']}; @endphp
                        <td>
                            @if($row['format'] === 'bool')
                                @if($val)<span class="check">✓</span>@else<span class="cross">—</span>@endif
                            @else
                                {{ $val === -1 ? '∞' : $val }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

 </div>

<script>
let billing = 'monthly';
function setBilling(b) {
    billing = b;
    document.getElementById('btn-monthly').classList.toggle('active', b === 'monthly');
    document.getElementById('btn-annual').classList.toggle('active', b === 'annual');

    // Update prices
    document.querySelectorAll('.price-display').forEach(el => {
        const v = b === 'annual' ? el.dataset.annual : el.dataset.monthly;
        el.textContent = 'R$ ' + parseFloat(v).toLocaleString('pt-BR', {minimumFractionDigits:2});
    });

    // Update billing notes
    document.querySelectorAll('.billing-note-monthly').forEach(el => el.style.display = b === 'monthly' ? 'block' : 'none');
    document.querySelectorAll('.billing-note-annual').forEach(el => el.style.display = b === 'annual' ? 'block' : 'none');

    // Update hidden billing cycle inputs
    document.querySelectorAll('.billing-cycle-input').forEach(el => el.value = b);
}
</script>
</x-layouts.app>
