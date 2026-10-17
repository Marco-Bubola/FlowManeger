<x-layouts.app title="Trocar plano">
<section class="settings-plans-page w-full mobile-393-base" x-data="{ annual: false }">
    <x-settings.layout :heading="''">
        <style>
            .tp-grid { display: grid; gap: .9rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 270px), 1fr)); padding: 0 1.25rem 1.25rem; }
            .tp-card { position: relative; display: flex; flex-direction: column; border-radius: 1.1rem; border: 1.5px solid #e2e8f0; background: #fff; padding: 1.1rem; transition: border-color .15s, box-shadow .15s; }
            .dark .tp-card { background: rgba(15,23,42,.45); border-color: #334155; }
            .tp-card:hover { border-color: rgba(147,51,234,.35); box-shadow: 0 6px 22px -12px rgba(147,51,234,.35); }
            .tp-card.is-popular { border-color: rgba(168,85,247,.45); background: rgba(168,85,247,.05); }
            .tp-card.is-current { border-color: rgba(79,70,229,.5); background: rgba(79,70,229,.05); }
            .tp-tag { display: inline-flex; align-items: center; gap: .25rem; width: max-content; border-radius: 999px; padding: .15rem .55rem; font-size: .68rem; font-weight: 700; }
            .tp-price { font-size: 1.85rem; font-weight: 900; letter-spacing: -.04em; line-height: 1.1; color: #0f172a; }
            .dark .tp-price { color: #f8fafc; }
            .tp-feats { list-style: none; margin: .9rem 0 1rem; padding: 0; display: flex; flex-direction: column; gap: .4rem; flex: 1; }
            .tp-feats li { display: flex; align-items: flex-start; gap: .45rem; font-size: .82rem; color: #334155; }
            .dark .tp-feats li { color: #e2e8f0; }
            .tp-feats li.off { color: #94a3b8; }
            .dark .tp-feats li.off { color: #64748b; }
            .tp-gw { display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: .4rem; margin-bottom: .7rem; }
            .tp-gw input { position: absolute; opacity: 0; pointer-events: none; }
            .tp-gw label { display: flex; align-items: center; justify-content: center; gap: .35rem; min-height: 2.5rem; padding: .4rem; border-radius: .7rem; border: 1px solid #e2e8f0; background: #f8fafc; font-size: .74rem; font-weight: 600; color: #475569; cursor: pointer; text-align: center; }
            .dark .tp-gw label { background: rgba(15,23,42,.6); border-color: #334155; color: #cbd5e1; }
            .tp-gw input:checked + label { border-color: #a855f7; background: rgba(168,85,247,.1); color: #7e22ce; }
            .dark .tp-gw input:checked + label { color: #d8b4fe; }
            .tp-gw input:focus-visible + label { outline: 2px solid #a855f7; outline-offset: 2px; }
            .tp-btn { display: inline-flex; width: 100%; align-items: center; justify-content: center; gap: .4rem; min-height: 2.75rem; border-radius: .8rem; font-size: .88rem; font-weight: 700; }
            .tp-toggle { display: inline-flex; border-radius: 999px; padding: 3px; border: 1px solid #e2e8f0; background: #f8fafc; }
            .dark .tp-toggle { background: rgba(15,23,42,.6); border-color: #334155; }
            .tp-toggle button { border-radius: 999px; padding: .35rem 1rem; font-size: .8rem; font-weight: 700; color: #64748b; }
            .tp-toggle button.on { background: #9333ea; color: #fff; box-shadow: 0 2px 10px rgba(147,51,234,.35); }
            .tp-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 0 1.25rem 1.25rem; }
            .tp-table { width: 100%; min-width: 560px; border-collapse: collapse; font-size: .84rem; }
            .tp-table th, .tp-table td { padding: .65rem .8rem; text-align: center; border-bottom: 1px solid #f1f5f9; }
            .dark .tp-table th, .dark .tp-table td { border-color: #1e293b; }
            .tp-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; color: #64748b; }
            .tp-table th:first-child, .tp-table td:first-child { text-align: left; }
            .tp-table td { color: #334155; }
            .dark .tp-table td { color: #e2e8f0; }
            .tp-table th.is-current, .tp-table td.is-current { background: rgba(79,70,229,.06); }
            @media (max-width: 639px) {
                .tp-grid, .tp-table-wrap { padding: 0 .6rem .9rem; }
                .tp-card { padding: .95rem; }
            }
        </style>

        @if(session('activated_plan'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-check-circle-fill mr-1"></i>Plano <strong>{{ session('activated_plan') }}</strong> ativado.</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"><i class="bi bi-exclamation-triangle-fill mr-1"></i>{{ session('error') }}</div>
        @endif

        {{-- Planos --}}
        <div class="settings-card">
            <div class="settings-card-header" style="flex-wrap:wrap;gap:.75rem">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(147,51,234,.12);color:#9333ea"><i class="bi bi-stars"></i></div>
                    <div>
                        <p class="settings-card-title">Escolha o seu plano</p>
                        <p class="settings-card-desc">Você está no plano <strong>{{ $currentPlan?->name ?? 'sem plano' }}</strong> · sem fidelidade, cancele quando quiser</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="tp-toggle" role="group" aria-label="Forma de cobrança">
                        <button type="button" :class="annual ? '' : 'on'" @click="annual = false">Mensal</button>
                        <button type="button" :class="annual ? 'on' : ''" @click="annual = true">Anual</button>
                    </div>
                    <span class="tp-tag bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">até 20% off</span>
                </div>
            </div>

            <div class="tp-grid">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                        $isPopular = filled($plan->badge_label);
                    @endphp
                    <article class="tp-card {{ $isCurrent ? 'is-current' : ($isPopular ? 'is-popular' : '') }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $plan->name }}</p>
                                @if($plan->description)
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $plan->description }}</p>
                                @endif
                            </div>
                            @if($isCurrent)
                                <span class="tp-tag bg-indigo-600 text-white shrink-0"><i class="bi bi-check2"></i>Seu plano</span>
                            @elseif($isPopular)
                                <span class="tp-tag bg-purple-600 text-white shrink-0"><i class="bi bi-star-fill"></i>{{ $plan->badge_label }}</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            @if($plan->isFree())
                                <p class="tp-price" style="color:#16a34a">Grátis</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">sempre gratuito</p>
                            @else
                                <p>
                                    <span class="tp-price" x-text="annual ? @js('R$ '.number_format($plan->price_annual, 2, ',', '.')) : @js('R$ '.number_format($plan->price_monthly, 2, ',', '.'))">R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">/mês</span>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400" x-show="!annual">cobrado todo mês</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400" x-show="annual" x-cloak>R$ {{ number_format($plan->price_annual * 12, 2, ',', '.') }} por ano · <span class="font-semibold text-emerald-600 dark:text-emerald-400">economia de {{ $plan->annualSavingsPercent() }}%</span></p>
                            @endif
                        </div>

                        <ul class="tp-feats">
                            @foreach($plan->featuresList() as $feat)
                                @php
                                    // featuresList() usa 'active'; recursos extras do banco podem vir como texto simples
                                    $featLabel = is_array($feat) ? ($feat['label'] ?? '') : (string) $feat;
                                    $featOn = is_array($feat) ? (bool) ($feat['included'] ?? $feat['active'] ?? true) : true;
                                @endphp
                                <li class="{{ $featOn ? '' : 'off' }}">
                                    <i class="bi {{ $featOn ? 'bi-check-lg text-emerald-500' : 'bi-x-lg' }}" style="margin-top:1px"></i>
                                    <span>{{ $featLabel }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if($isCurrent)
                            <a href="{{ route('settings.plan') }}" class="tp-btn border border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-patch-check"></i>Ver minha assinatura</a>
                        @elseif($plan->isFree())
                            <form method="POST" action="{{ route('subscription.checkout.process') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_cycle" value="monthly">
                                <input type="hidden" name="gateway" value="manual">
                                <button class="tp-btn border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Mudar para o grátis</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('subscription.checkout.process') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_cycle" :value="annual ? 'annual' : 'monthly'" value="monthly">

                                <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pagar com</p>
                                <div class="tp-gw">
                                    <input type="radio" name="gateway" id="gw-stripe-{{ $plan->id }}" value="stripe" checked>
                                    <label for="gw-stripe-{{ $plan->id }}"><i class="bi bi-credit-card"></i>Stripe</label>

                                    <input type="radio" name="gateway" id="gw-mp-{{ $plan->id }}" value="mercadopago">
                                    <label for="gw-mp-{{ $plan->id }}"><i class="bi bi-wallet2"></i>Mercado Pago</label>

                                    <input type="radio" name="gateway" id="gw-ps-{{ $plan->id }}" value="pagseguro">
                                    <label for="gw-ps-{{ $plan->id }}"><i class="bi bi-bank"></i>PagSeguro</label>

                                    @if(auth()->user()->isAdmin())
                                        <input type="radio" name="gateway" id="gw-manual-{{ $plan->id }}" value="manual">
                                        <label for="gw-manual-{{ $plan->id }}"><i class="bi bi-key"></i>Manual</label>
                                    @endif
                                </div>

                                <button class="tp-btn text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25">
                                    <i class="bi bi-arrow-up-circle"></i>{{ $plan->trial_days > 0 ? 'Testar grátis por '.$plan->trial_days.' dias' : 'Assinar '.$plan->name }}
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Comparação --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="bi bi-table"></i></div>
                    <div>
                        <p class="settings-card-title">Comparar lado a lado</p>
                        <p class="settings-card-desc">Limites e recursos de cada plano</p>
                    </div>
                </div>
            </div>
            @php
                $rows = [
                    ['Produtos', 'max_products', 'limit'],
                    ['Pedidos por mês', 'max_orders_per_month', 'limit'],
                    ['Clientes', 'max_clients', 'limit'],
                    ['Usuários na equipe', 'max_users', 'limit'],
                    ['Mercado Livre', 'has_ml_integration', 'bool'],
                    ['Shopee', 'has_shopee_integration', 'bool'],
                    ['Recursos de IA', 'has_ai_features', 'bool'],
                    ['Relatórios avançados', 'has_advanced_reports', 'bool'],
                    ['Controle financeiro', 'has_financial_control', 'bool'],
                    ['Acesso à API', 'has_api_access', 'bool'],
                    ['Suporte prioritário', 'has_priority_support', 'bool'],
                    ['Exportar PDF e Excel', 'has_export_pdf_excel', 'bool'],
                ];
            @endphp
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead>
                        <tr>
                            <th>Recurso</th>
                            @foreach($plans as $p)<th class="{{ $currentPlan && $currentPlan->id === $p->id ? 'is-current' : '' }}">{{ $p->name }}</th>@endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as [$label, $key, $format])
                            <tr>
                                <td class="font-medium">{{ $label }}</td>
                                @foreach($plans as $p)
                                    @php $val = $p->{$key}; @endphp
                                    <td class="{{ $currentPlan && $currentPlan->id === $p->id ? 'is-current' : '' }}">
                                        @if($format === 'bool')
                                            @if($val)<i class="bi bi-check-lg text-emerald-500"></i>@else<span class="text-slate-300 dark:text-slate-600">—</span>@endif
                                        @else
                                            {{ $val === -1 ? 'Ilimitado' : $val }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-settings.layout>
</section>
</x-layouts.app>
