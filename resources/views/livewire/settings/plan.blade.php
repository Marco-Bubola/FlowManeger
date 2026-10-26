<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Subscription;

new class extends Component {
    public function cancelSubscription(): void
    {
        $user = Auth::user();
        $sub  = $user->activeSubscription;
        if ($sub && !$sub->plan->isFree()) {
            $sub->update([
                'status'      => Subscription::STATUS_CANCELED,
                'canceled_at' => now(),
            ]);
            // Assign free plan
            $free = Plan::getDefault();
            if ($free) {
                $user->subscriptions()->create([
                    'plan_id'              => $free->id,
                    'status'               => Subscription::STATUS_ACTIVE,
                    'billing_cycle'        => 'monthly',
                    'price_paid'           => 0,
                    'gateway'              => Subscription::GATEWAY_MANUAL,
                    'current_period_start' => now(),
                    'current_period_end'   => now()->addYears(10),
                ]);
            }
            session()->flash('flash.planMessage', 'Sua assinatura foi cancelada.');
            session()->flash('flash.planType', 'warning');
        }
    }
}; ?>

<section class="settings-plan-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    @php
        $planUser = Auth::user()->fresh();
        $activeSub = $planUser->activeSubscription;
        $activePlan = $planUser->plan();
        $isFree = $activePlan->isFree() || (float) $activePlan->price_monthly == 0;
        $isAnnual = $activeSub && $activeSub->billing_cycle === 'annual';
        $periodEnd = $activeSub?->current_period_end;
        $daysLeft = $periodEnd ? max(0, (int) now()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay(), false)) : null;
        $periodStart = $activeSub?->current_period_start;
        $periodDays = ($periodStart && $periodEnd) ? max(1, (int) $periodStart->diffInDays($periodEnd)) : null;
        $periodPct = ($periodDays && $daysLeft !== null) ? (int) max(0, min(100, round(($periodDays - $daysLeft) / $periodDays * 100))) : 0;

        // Uso real da conta comparado aos limites do plano
        try {
            $usedProducts = \App\Models\Product::count();
            $usedClients = \App\Models\Client::count();
            $usedSales = \App\Models\Sale::where('created_at', '>=', now()->startOfMonth())->count();
        } catch (\Throwable $e) {
            $usedProducts = $usedClients = $usedSales = 0;
        }
        $meter = function (string $label, string $icon, int $used, $limit) {
            $limit = (int) $limit;
            if ($limit === -1) {
                return ['label' => $label, 'icon' => $icon, 'used' => $used, 'limitTxt' => 'Ilimitado', 'pct' => 0, 'tone' => 'free'];
            }
            $pct = $limit > 0 ? (int) min(100, round($used / $limit * 100)) : 0;
            $tone = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warn' : 'ok');
            return ['label' => $label, 'icon' => $icon, 'used' => $used, 'limitTxt' => 'de ' . number_format($limit, 0, ',', '.'), 'pct' => $pct, 'tone' => $tone];
        };
        $meters = [
            $meter('Produtos', 'bi-box-seam', $usedProducts, $activePlan->max_products),
            $meter('Clientes', 'bi-people', $usedClients, $activePlan->max_clients),
            $meter('Vendas no mês', 'bi-bag-check', $usedSales, $activePlan->max_orders_per_month),
        ];
        $nearLimit = collect($meters)->contains(fn ($m) => $m['pct'] >= 80);

        // Recursos: o que o plano tem e em qual plano está o que falta
        $allPlans = \App\Models\Plan::active()->ordered()->get();
        $featureMap = [
            ['has_financial_control', 'Controle financeiro', 'bi-wallet2'],
            ['has_ml_integration', 'Mercado Livre', 'bi-shop'],
            ['has_shopee_integration', 'Shopee', 'bi-bag'],
            ['has_ai_features', 'IA e leitura automática', 'bi-stars'],
            ['has_advanced_reports', 'Relatórios avançados', 'bi-graph-up-arrow'],
            ['has_export_pdf_excel', 'Exportar PDF e Excel', 'bi-file-earmark-arrow-down'],
            ['has_api_access', 'API de integração', 'bi-code-slash'],
            ['has_priority_support', 'Suporte prioritário', 'bi-headset'],
        ];
        $features = collect($featureMap)->map(function ($f) use ($activePlan, $allPlans) {
            $on = (bool) ($activePlan->{$f[0]} ?? false);
            $where = $on ? null : $allPlans->first(fn ($pl) => (bool) ($pl->{$f[0]} ?? false));
            return ['label' => $f[1], 'icon' => $f[2], 'on' => $on, 'where' => $where?->name];
        });
        $teamOn = (int) $activePlan->max_users > 1;
        $features->push(['label' => $teamOn ? 'Equipe com até ' . $activePlan->max_users . ' pessoas' : 'Equipe com mais pessoas', 'icon' => 'bi-people', 'on' => $teamOn, 'where' => $teamOn ? null : $allPlans->first(fn ($pl) => (int) $pl->max_users > 1)?->name]);
        $onCount = $features->where('on', true)->count();

        // Próximo plano acima do atual
        $nextPlan = $allPlans->first(fn ($pl) => (float) $pl->price_monthly > (float) $activePlan->price_monthly);
        $nextGains = [];
        if ($nextPlan) {
            foreach ($featureMap as $f) {
                if (($nextPlan->{$f[0]} ?? false) && ! ($activePlan->{$f[0]} ?? false)) $nextGains[] = [$f[1], $f[2]];
            }
            foreach ([['max_products', 'produtos'], ['max_clients', 'clientes'], ['max_orders_per_month', 'vendas por mês']] as [$fld, $word]) {
                $a = (int) $activePlan->{$fld}; $b = (int) $nextPlan->{$fld};
                if ($b === -1 && $a !== -1) $nextGains[] = [ucfirst($word) . ' sem limite', 'bi-infinity'];
                elseif ($b > $a && $a !== -1) $nextGains[] = ['Até ' . number_format($b, 0, ',', '.') . ' ' . $word, 'bi-arrow-up-circle'];
            }
            $nextGains = array_slice($nextGains, 0, 6);
        }
        $allSubs = $planUser->subscriptions()->with('plan')->latest()->take(10)->get();
    @endphp

    <div class="pl-page">
        @if(session('flash.planMessage'))
            <div class="pl-flash {{ session('flash.planType') === 'warning' ? 'is-warn' : '' }}"><i class="bi bi-info-circle"></i> {{ session('flash.planMessage') }}</div>
        @endif

        {{-- Plano atual --}}
        <div class="pl-hero bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <span class="pl-hero-glow"></span>
            <div class="pl-hero-main">
                <span class="pl-kicker"><i class="bi bi-patch-check-fill"></i> Seu plano</span>
                <h2>{{ $activePlan->name }}</h2>
                <p>{{ $activePlan->description ?: ($isFree ? 'Para começar a organizar suas vendas sem gastar nada.' : 'Tudo o que sua loja precisa para crescer.') }}</p>
                <div class="pl-hero-tags">
                    @if($activeSub)
                        <span><i class="bi bi-circle-fill" style="color: {{ $activeSub->statusColor() }}"></i> {{ $activeSub->statusLabel() }}</span>
                        <span><i class="bi bi-arrow-repeat"></i> {{ $activeSub->billingCycleLabel() }}</span>
                        <span><i class="bi bi-credit-card-2-front"></i> {{ $activeSub->gatewayLabel() }}</span>
                    @else
                        <span><i class="bi bi-dash-circle"></i> Sem assinatura ativa</span>
                    @endif
                </div>
            </div>
            <div class="pl-hero-side">
                <div class="pl-price">
                    @if($isFree)
                        <b>R$ 0</b><small>para sempre, sem cartão</small>
                    @else
                        <b>{{ $isAnnual ? $activePlan->formattedPriceAnnual() : $activePlan->formattedPriceMonthly() }}</b><small>{{ $isAnnual ? 'por ano' : 'por mês' }}</small>
                    @endif
                </div>
                @if(! $isFree && $daysLeft !== null)
                    <div class="pl-renew">
                        <div class="pl-renew-bar"><span style="width: {{ $periodPct }}%"></span></div>
                        <small>{{ $daysLeft === 0 ? 'Renova hoje' : 'Renova em ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'dia' : 'dias') }} · {{ $periodEnd->format('d/m/Y') }}</small>
                    </div>
                @endif
                <div class="pl-hero-actions">
                    <a href="{{ route('subscription.plans') }}" class="pl-btn-white"><i class="bi bi-stars"></i> {{ $isFree ? 'Ver planos pagos' : 'Mudar de plano' }}</a>
                    @if($activeSub && ! $isFree)
                        <button type="button" class="pl-btn-glass" wire:click="cancelSubscription" wire:confirm="Tem certeza que quer cancelar a assinatura? Sua conta volta para o plano Grátis.">Cancelar</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Uso --}}
        <div class="pl-meters">
            @foreach($meters as $m)
                <div class="pl-meter is-{{ $m['tone'] }}">
                    <div class="pl-meter-ring" style="--pct: {{ $m['tone'] === 'free' ? 100 : max(2, $m['pct']) }}">
                        <i class="bi {{ $m['icon'] }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p>{{ $m['label'] }}</p>
                        <b>{{ number_format($m['used'], 0, ',', '.') }} <small>{{ $m['limitTxt'] }}</small></b>
                        <span>{{ $m['tone'] === 'free' ? 'Sem limite no seu plano' : ($m['pct'] >= 100 ? 'Limite atingido' : $m['pct'] . '% usado') }}</span>
                    </div>
                </div>
            @endforeach
            <div class="pl-meter">
                <div class="pl-meter-ring" style="--pct: {{ (int) $activePlan->max_users > 1 ? 100 : 100 }}"><i class="bi bi-person-badge"></i></div>
                <div class="min-w-0">
                    <p>Equipe</p>
                    <b>{{ (int) $activePlan->max_users === -1 ? 'Sem limite' : (int) $activePlan->max_users }} <small>{{ (int) $activePlan->max_users === 1 ? 'pessoa' : 'pessoas' }}</small></b>
                    <span><a href="{{ route('settings.team') }}" wire:navigate>Ver equipe <i class="bi bi-arrow-right"></i></a></span>
                </div>
            </div>
        </div>

        <div class="pl-grid">
            <div class="pl-main">
                {{-- Recursos --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-ui-checks-grid"></i></span>
                        <div class="min-w-0">
                            <h2>O que seu plano inclui</h2>
                            <p>{{ $onCount }} de {{ $features->count() }} recursos liberados. Os bloqueados mostram em qual plano estão.</p>
                        </div>
                    </div>
                    <div class="pl-feats">
                        @foreach($features as $f)
                            <div class="pl-feat {{ $f['on'] ? 'is-on' : '' }}">
                                <span class="pl-feat-ico"><i class="bi {{ $f['icon'] }}"></i></span>
                                <span class="min-w-0">
                                    <b>{{ $f['label'] }}</b>
                                    <small>{{ $f['on'] ? 'Liberado' : ($f['where'] ? 'No plano ' . $f['where'] : 'Em breve') }}</small>
                                </span>
                                <i class="bi {{ $f['on'] ? 'bi-check-circle-fill' : 'bi-lock-fill' }} pl-feat-state"></i>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Histórico --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-slate-600 to-slate-900"><i class="bi bi-clock-history"></i></span>
                        <div class="min-w-0">
                            <h2>Histórico de assinaturas</h2>
                            <p>Os planos que sua conta já teve.</p>
                        </div>
                    </div>
                    @if($allSubs->isEmpty())
                        <div class="pl-empty"><i class="bi bi-inbox"></i> Nenhuma assinatura ainda.</div>
                    @else
                        <ol class="pl-timeline">
                            @foreach($allSubs as $sub)
                                <li>
                                    <span class="pl-dot" style="background: {{ $sub->statusColor() }}"></span>
                                    <div class="min-w-0">
                                        <b>{{ $sub->plan->name ?? '—' }}</b>
                                        <small>{{ $sub->billingCycleLabel() }} · {{ $sub->gatewayLabel() }}{{ (float) $sub->price_paid > 0 ? ' · R$ ' . number_format($sub->price_paid, 2, ',', '.') : '' }}</small>
                                    </div>
                                    <span class="pl-when">{{ $sub->created_at->format('d/m/Y') }}</span>
                                    <span class="pl-status" style="color: {{ $sub->statusColor() }}; background: {{ $sub->statusColor() }}1a">{{ $sub->statusLabel() }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>

            <aside class="pl-side">
                {{-- Próximo plano --}}
                @if($nextPlan)
                    <div class="pl-next">
                        <span class="pl-kicker"><i class="bi bi-rocket-takeoff"></i> Próximo passo</span>
                        <h3>Plano {{ $nextPlan->name }}</h3>
                        <p class="pl-next-price"><b>{{ $nextPlan->formattedPriceMonthly() }}</b>{{ (float) $nextPlan->price_monthly > 0 ? '/mês' : '' }}
                            @if($nextPlan->annualSavingsPercent() > 0)<span>até {{ $nextPlan->annualSavingsPercent() }}% off no anual</span>@endif
                        </p>
                        @if($nearLimit)
                            <p class="pl-next-warn"><i class="bi bi-exclamation-triangle-fill"></i> Você está perto do limite do plano atual.</p>
                        @endif
                        <ul>
                            @forelse($nextGains as [$txt, $ic])
                                <li><i class="bi {{ $ic }}"></i> {{ $txt }}</li>
                            @empty
                                <li><i class="bi bi-plus-circle"></i> Mais capacidade para a sua loja</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('subscription.plans') }}" class="pl-next-btn bg-gradient-to-r from-indigo-600 to-purple-600">
                            {{ $nextPlan->trial_days ? 'Testar grátis por ' . $nextPlan->trial_days . ' dias' : 'Conhecer o plano' }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="ap-card pl-top">
                        <span class="ap-ico bg-gradient-to-br from-amber-400 to-orange-500"><i class="bi bi-trophy"></i></span>
                        <h3>Você está no plano mais completo</h3>
                        <p>Todos os recursos do FlowManager já estão liberados para a sua loja.</p>
                    </div>
                @endif

                {{-- Cobrança --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-receipt"></i></span>
                        <div class="min-w-0">
                            <h2>Cobrança</h2>
                            <p>Como e quando sua assinatura é paga.</p>
                        </div>
                    </div>
                    <div class="acc-facts pl-facts">
                        <div><span>Valor</span><b>{{ $isFree ? 'R$ 0,00' : ($isAnnual ? $activePlan->formattedPriceAnnual() . '/ano' : $activePlan->formattedPriceMonthly() . '/mês') }}</b></div>
                        <div><span>Forma de pagamento</span><b>{{ $activeSub?->gatewayLabel() ?? '—' }}</b></div>
                        <div><span>Início do período</span><b>{{ $periodStart?->format('d/m/Y') ?? '—' }}</b></div>
                        <div><span>{{ $isFree ? 'Válido até' : 'Próxima cobrança' }}</span><b>{{ $isFree ? 'Sem prazo' : ($periodEnd?->format('d/m/Y') ?? '—') }}</b></div>
                    </div>
                    <a href="{{ route('subscription.plans') }}" class="pl-link"><i class="bi bi-grid-3x3-gap"></i> Comparar todos os planos <i class="bi bi-arrow-right ml-auto"></i></a>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .pl-page { display: flex; flex-direction: column; gap: 1rem; }
        .pl-flash { display: flex; align-items: center; gap: .5rem; padding: .8rem 1rem; border-radius: 1rem; font-size: .84rem; font-weight: 600; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .pl-flash.is-warn { background: #fffbeb; color: #92400e; border-color: #fde68a; }
        .pl-hero { position: relative; overflow: hidden; display: flex; gap: 1.5rem; align-items: stretch; justify-content: space-between; padding: 1.6rem 1.7rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .pl-hero-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 15% 0%, rgba(255, 255, 255, .28), transparent 40%), radial-gradient(circle at 90% 100%, rgba(255, 255, 255, .18), transparent 40%); }
        .pl-hero-main, .pl-hero-side { position: relative; min-width: 0; }
        .pl-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
        .pl-hero h2 { font-size: 2.2rem; font-weight: 900; line-height: 1.05; margin-top: .35rem; }
        .pl-hero-main > p { font-size: .88rem; opacity: .9; margin-top: .35rem; max-width: 34rem; }
        .pl-hero-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .9rem; }
        .pl-hero-tags span { display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 700; padding: .3rem .7rem; border-radius: 999px; background: rgba(255, 255, 255, .18); backdrop-filter: blur(6px); }
        .pl-hero-tags .bi-circle-fill { font-size: .5rem; filter: drop-shadow(0 0 2px #fff); }
        .pl-hero-side { flex: none; width: 18rem; display: flex; flex-direction: column; gap: .8rem; padding: 1rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .14); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, .25); }
        .pl-price b { display: block; font-size: 1.9rem; font-weight: 900; line-height: 1; }
        .pl-price small { font-size: .78rem; opacity: .85; }
        .pl-renew-bar { height: .45rem; border-radius: 999px; background: rgba(255, 255, 255, .25); overflow: hidden; }
        .pl-renew-bar span { display: block; height: 100%; border-radius: 999px; background: #fff; }
        .pl-renew small { display: block; margin-top: .35rem; font-size: .72rem; opacity: .9; }
        .pl-hero-actions { display: flex; gap: .5rem; margin-top: auto; }
        .pl-btn-white { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; font-size: .82rem; font-weight: 800; padding: .65rem .9rem; border-radius: .9rem; background: #fff; color: var(--color-indigo-700); box-shadow: 0 8px 18px -8px rgba(15, 23, 42, .5); }
        .pl-btn-glass { font-size: .8rem; font-weight: 700; padding: .65rem .9rem; border-radius: .9rem; background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .35); color: #fff; }

        .pl-meters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .8rem; }
        .pl-meter { --c: #10b981; display: flex; align-items: center; gap: .8rem; padding: .9rem 1rem; border-radius: 1.2rem; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -20px rgba(15, 23, 42, .25); }
        .dark .pl-meter { background: #0f172a; border-color: #1e293b; box-shadow: none; }
        .pl-meter.is-warn { --c: #f59e0b; }
        .pl-meter.is-danger { --c: #ef4444; }
        .pl-meter.is-free, .pl-meter:not([class*="is-"]) { --c: var(--color-indigo-500); }
        .pl-meter-ring { position: relative; flex: none; width: 3.3rem; height: 3.3rem; border-radius: 999px; display: grid; place-items: center; background: conic-gradient(var(--c) calc(var(--pct) * 1%), #e2e8f0 0); }
        .dark .pl-meter-ring { background: conic-gradient(var(--c) calc(var(--pct) * 1%), #1f2937 0); }
        .pl-meter-ring::before { content: ''; position: absolute; inset: .3rem; border-radius: 999px; background: #fff; }
        .dark .pl-meter-ring::before { background: #0f172a; }
        .pl-meter-ring i { position: relative; color: var(--c); font-size: 1.05rem; }
        .pl-meter p { font-size: .74rem; font-weight: 700; color: #64748b; }
        .pl-meter b { display: block; font-size: 1.2rem; font-weight: 900; color: #0f172a; line-height: 1.2; }
        .pl-meter b small { font-size: .74rem; font-weight: 600; color: #94a3b8; }
        .pl-meter span { display: block; font-size: .7rem; font-weight: 700; color: var(--c); }
        .pl-meter span a { color: var(--color-indigo-600); }
        .dark .pl-meter p { color: #94a3b8; }
        .dark .pl-meter b { color: #f1f5f9; }
        .dark .pl-meter span a { color: var(--color-indigo-300); }

        .pl-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .pl-main, .pl-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .pl-feats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .55rem; }
        .pl-feat { display: flex; align-items: center; gap: .6rem; padding: .65rem .7rem; border-radius: 1rem; background: #f8fafc; border: 1px dashed #e2e8f0; }
        .pl-feat.is-on { background: #ecfdf5; border: 1px solid #bbf7d0; }
        .dark .pl-feat { background: #111827; border-color: #1f2937; }
        .dark .pl-feat.is-on { background: rgba(16, 185, 129, .08); border-color: rgba(16, 185, 129, .25); }
        .pl-feat-ico { flex: none; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: .7rem; background: #e2e8f0; color: #94a3b8; }
        .pl-feat.is-on .pl-feat-ico { background: #10b981; color: #fff; }
        .dark .pl-feat-ico { background: #1f2937; color: #64748b; }
        .pl-feat b { display: block; font-size: .8rem; color: #0f172a; line-height: 1.2; }
        .pl-feat small { display: block; font-size: .68rem; color: #94a3b8; }
        .pl-feat.is-on small { color: #059669; font-weight: 700; }
        .dark .pl-feat b { color: #e2e8f0; }
        .pl-feat:not(.is-on) b { color: #64748b; }
        .dark .pl-feat:not(.is-on) b { color: #94a3b8; }
        .pl-feat-state { margin-left: auto; font-size: .9rem; color: #cbd5e1; }
        .pl-feat.is-on .pl-feat-state { color: #10b981; }
        .dark .pl-feat-state { color: #475569; }

        .pl-timeline { display: flex; flex-direction: column; }
        .pl-timeline li { position: relative; display: flex; align-items: center; gap: .75rem; padding: .65rem 0 .65rem 1.4rem; border-top: 1px dashed #e2e8f0; }
        .pl-timeline li:first-child { border-top: 0; }
        .dark .pl-timeline li { border-color: #1e293b; }
        .pl-dot { position: absolute; left: .2rem; top: 50%; width: .6rem; height: .6rem; border-radius: 999px; transform: translateY(-50%); box-shadow: 0 0 0 4px rgba(148, 163, 184, .15); }
        .pl-timeline b { display: block; font-size: .85rem; color: #0f172a; }
        .pl-timeline small { display: block; font-size: .72rem; color: #64748b; }
        .dark .pl-timeline b { color: #f1f5f9; }
        .pl-timeline > li > div { flex: 1; }
        .pl-when { font-size: .74rem; color: #94a3b8; white-space: nowrap; }
        .pl-status { font-size: .68rem; font-weight: 800; padding: .2rem .55rem; border-radius: 999px; white-space: nowrap; }
        .pl-empty { padding: 1.5rem; text-align: center; color: #94a3b8; font-size: .84rem; }

        .pl-next { position: relative; padding: 1.2rem; border-radius: 1.3rem; background: linear-gradient(160deg, color-mix(in oklab, var(--color-indigo-500) 12%, #fff), #fff 60%); border: 1.5px solid color-mix(in oklab, var(--color-indigo-500) 35%, transparent); box-shadow: 0 16px 40px -26px color-mix(in oklab, var(--color-indigo-600) 80%, transparent); }
        .dark .pl-next { background: linear-gradient(160deg, color-mix(in oklab, var(--color-indigo-500) 20%, #0f172a), #0f172a 60%); }
        .pl-next .pl-kicker { color: var(--color-indigo-600); }
        .dark .pl-next .pl-kicker { color: var(--color-indigo-300); }
        .pl-next h3 { font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-top: .3rem; }
        .dark .pl-next h3 { color: #f8fafc; }
        .pl-next-price { margin-top: .2rem; font-size: .8rem; color: #64748b; }
        .pl-next-price b { font-size: 1.35rem; font-weight: 900; color: #0f172a; }
        .dark .pl-next-price b { color: #f8fafc; }
        .pl-next-price span { display: block; font-size: .72rem; font-weight: 700; color: #059669; }
        .pl-next-warn { margin-top: .6rem; font-size: .74rem; font-weight: 700; color: #b45309; background: #fffbeb; padding: .45rem .6rem; border-radius: .7rem; }
        .pl-next ul { display: flex; flex-direction: column; gap: .45rem; margin: .9rem 0 1rem; }
        .pl-next li { display: flex; align-items: center; gap: .5rem; font-size: .82rem; font-weight: 600; color: #334155; }
        .pl-next li i { color: var(--color-indigo-600); }
        .dark .pl-next li { color: #cbd5e1; }
        .dark .pl-next li i { color: var(--color-indigo-300); }
        .pl-next-btn { display: flex; align-items: center; justify-content: center; gap: .45rem; color: #fff; font-size: .85rem; font-weight: 800; padding: .75rem; border-radius: .95rem; }
        .pl-top { text-align: center; display: flex; flex-direction: column; align-items: center; gap: .5rem; }
        .pl-top h3 { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .pl-top p { font-size: .8rem; color: #64748b; }
        .dark .pl-top h3 { color: #f1f5f9; }
        .pl-link { display: flex; align-items: center; gap: .5rem; margin-top: .8rem; padding: .65rem .8rem; border-radius: .9rem; background: #f8fafc; font-size: .8rem; font-weight: 700; color: var(--color-indigo-700); }
        .dark .pl-link { background: #111827; color: var(--color-indigo-300); }
        .acc-facts { display: flex; flex-direction: column; }
        .acc-facts div { display: flex; justify-content: space-between; gap: .75rem; padding: .55rem 0; border-top: 1px dashed #e2e8f0; font-size: .8rem; }
        .acc-facts div:first-child { border-top: 0; }
        .dark .acc-facts div { border-color: #1e293b; }
        .acc-facts span { color: #64748b; }
        .acc-facts b { color: #0f172a; text-align: right; }
        .dark .acc-facts b { color: #f1f5f9; }

        @media (max-width: 1279px) { .pl-grid { grid-template-columns: minmax(0, 1fr) 320px; } .pl-feats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 1023px) {
            .pl-grid { grid-template-columns: minmax(0, 1fr); }
            .pl-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); order: -1; }
            .pl-meters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .pl-hero { flex-direction: column; padding: 1.2rem; border-radius: 1.3rem; }
            .pl-hero h2 { font-size: 1.8rem; }
            .pl-hero-side { width: auto; }
            .pl-meters { gap: .55rem; }
            .pl-meter { flex-direction: column; align-items: flex-start; gap: .5rem; padding: .8rem; }
            .pl-feats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem; }
            .pl-feat { padding: .5rem; gap: .45rem; }
            .pl-feat-ico { width: 1.7rem; height: 1.7rem; font-size: .8rem; }
            .pl-feat-state { display: none; }
            .pl-side { display: flex; }
            .pl-timeline li { flex-wrap: wrap; }
            .pl-timeline > li > div { flex-basis: calc(100% - 1rem); }
        }
    </style>
    </x-settings.layout>
</section>
