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
            $planSummary = [
                ['label' => 'Plano', 'value' => $activePlan->name, 'tone' => '#a855f7'],
                ['label' => 'Status', 'value' => $activeSub?->statusLabel() ?? 'Sem assinatura ativa', 'tone' => $activeSub?->statusColor() ?? '#64748b'],
                ['label' => 'Ciclo', 'value' => $activeSub?->billingCycleLabel() ?? 'Sem ciclo', 'tone' => '#10b981'],
                ['label' => 'Renovacao', 'value' => $activeSub?->current_period_end?->format('d/m/Y') ?? 'Sem renovacao', 'tone' => '#06b6d4'],
            ];
        @endphp

        {{-- Flash messages --}}
        @if(session('flash.planMessage'))
            <div style="padding:.8rem 1.2rem;border-radius:10px;margin-bottom:1.25rem;font-size:.84rem;
                        background:{{ session('flash.planType') === 'warning' ? 'rgba(245,158,11,.1)' : 'rgba(16,185,129,.1)' }};
                        border:1px solid {{ session('flash.planType') === 'warning' ? 'rgba(245,158,11,.3)' : 'rgba(16,185,129,.3)' }};
                        color:{{ session('flash.planType') === 'warning' ? '#fbbf24' : '#34d399' }}">
                {{ session('flash.planMessage') }}
            </div>
        @endif

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- CARD: Plano atual --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(168,85,247,.12);color:#a855f7">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Plano atual</p>
                        <p class="settings-card-desc">Você está no plano <strong>{{ $activePlan->name }}</strong></p>
                    </div>
                </div>
                @if($activeSub)
                    <span class="s-badge" style="background:{{ $activeSub->statusColor() }}1a;color:{{ $activeSub->statusColor() }};border:1px solid {{ $activeSub->statusColor() }}50">
                        {{ $activeSub->statusLabel() }}
                    </span>
                @endif
            </div>

            <div style="padding:0 1.25rem 1.5rem">
                {{-- Subscription details --}}
                @if($activeSub)
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;margin-bottom:1.25rem">
                    <div class="settings-info-tile" style="background:rgba(168,85,247,.06);border-color:rgba(168,85,247,.15)">
                        <p class="settings-info-tile-label" style="color:#a855f7">Plano</p>
                        <p style="font-size:.95rem;font-weight:800;margin:.1rem 0 0">{{ $activePlan->name }}</p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(16,185,129,.06);border-color:rgba(16,185,129,.15)">
                        <p class="settings-info-tile-label" style="color:#10b981">Ciclo</p>
                        <p style="font-size:.95rem;font-weight:800;margin:.1rem 0 0">{{ $activeSub->billingCycleLabel() }}</p>
                    </div>
                    @if(!$activePlan->isFree())
                    <div class="settings-info-tile" style="background:rgba(6,182,212,.06);border-color:rgba(6,182,212,.15)">
                        <p class="settings-info-tile-label" style="color:#06b6d4">Válido até</p>
                        <p style="font-size:.95rem;font-weight:800;margin:.1rem 0 0">{{ $activeSub->current_period_end?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(245,158,11,.06);border-color:rgba(245,158,11,.15)">
                        <p class="settings-info-tile-label" style="color:#f59e0b">Valor</p>
                        <p style="font-size:.95rem;font-weight:800;margin:.1rem 0 0">{{ $activePlan->formattedPriceMonthly() }}/mês</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Features list --}}
                @php $features = $activePlan->featuresList(); @endphp
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.4rem">
                    @foreach($features as $feat)
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;{{ !$feat['active'] ? 'color:rgba(0,0,0,.3)' : '' }}">
                        <span style="color:{{ $feat['active'] ? '#10b981' : '#cbd5e1' }}">{{ $feat['active'] ? '✓' : '✕' }}</span>
                        {{ $feat['label'] }}
                    </div>
                    @endforeach
                </div>

                {{-- Upgrade / Cancel CTAs --}}
                <div style="display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap">
                    <a href="{{ route('subscription.plans') }}"
                       style="padding:.6rem 1.4rem;border-radius:.6rem;font-size:.82rem;font-weight:700;background:linear-gradient(135deg,#ec4899,#a855f7);color:#fff;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem">
                        {{ $activePlan->isFree() ? 'Ver planos pagos' : 'Mudar plano' }}
                    </a>
                    @if($activeSub && !$activePlan->isFree())
                    <button wire:click="cancelSubscription"
                            wire:confirm="Tem certeza que deseja cancelar sua assinatura?"
                            style="padding:.6rem 1.2rem;border-radius:.6rem;font-size:.82rem;font-weight:700;background:rgba(239,68,68,.08);color:#ef4444;border:1px solid rgba(239,68,68,.2);cursor:pointer">
                        Cancelar assinatura
                    </button>
                    @endif
                </div>
            </div>
        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Resumo da minha assinatura</p>
                        <p class="settings-card-desc">Aqui aparecem apenas os dados do seu plano atual, sem listar planos de outras areas.</p>
                    </div>
                </div>
                <a href="{{ route('subscription.plans') }}" style="font-size:.78rem;font-weight:700;color:#a855f7;text-decoration:none">Abrir catálogo →</a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;padding:0 1.25rem 1.25rem">
                @foreach($planSummary as $item)
                <div class="settings-info-tile" style="background:{{ $item['tone'] }}10;border-color:{{ $item['tone'] }}22">
                    <p class="settings-info-tile-label" style="color:{{ $item['tone'] }}">{{ $item['label'] }}</p>
                    <p class="settings-info-tile-val">{{ $item['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CARD: Histórico de assinaturas --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Histórico de assinaturas</p>
                        <p class="settings-card-desc">Todas as assinaturas da sua conta</p>
                    </div>
                </div>
            </div>
            <div style="padding:0 1.25rem 1.5rem">
                @php $allSubs = $planUser->subscriptions()->with('plan')->latest()->take(10)->get(); @endphp
                @if($allSubs->isEmpty())
                    <div style="background:#f8fafc;border-radius:.75rem;padding:2rem 1rem;text-align:center;color:#94a3b8;font-size:.84rem">
                        Nenhuma assinatura encontrada.
                    </div>
                @else
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        @foreach($allSubs as $sub)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.7rem 1rem;background:rgba(0,0,0,.02);border:1px solid rgba(0,0,0,.06);border-radius:.7rem;font-size:.82rem;flex-wrap:wrap;gap:.5rem">
                            <div>
                                <span style="font-weight:700">{{ $sub->plan->name ?? '—' }}</span>
                                <span style="color:#94a3b8;margin-left:.5rem">· {{ $sub->gatewayLabel() }} · {{ $sub->billingCycleLabel() }}</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <span style="color:#94a3b8">{{ $sub->created_at->format('d/m/Y') }}</span>
                                <span style="padding:.15rem .6rem;border-radius:99px;font-size:.7rem;font-weight:700;background:{{ $sub->statusColor() }}1a;color:{{ $sub->statusColor() }};border:1px solid {{ $sub->statusColor() }}40">
                                    {{ $sub->statusLabel() }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @php
            $usageCards = $activePlan->isFree()
                ? [
                    ['Produtos sincronizados', 68, '34 de 50', '#6366f1'],
                    ['Usuários da equipe', 50, '1 de 2', '#10b981'],
                    ['Exportações mensais', 72, '18 de 25', '#f59e0b'],
                ]
                : [
                    ['Produtos sincronizados', 41, '410 de 1000', '#6366f1'],
                    ['Usuários da equipe', 56, '7 de 12', '#10b981'],
                    ['Exportações mensais', 29, '58 de 200', '#f59e0b'],
                ];
        @endphp

        </div>{{-- /s-col-side --}}

        <div class="s-col-full">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-top:1rem">
            <div class="settings-card" style="padding:1.25rem;background:linear-gradient(135deg,rgba(168,85,247,.08),transparent)">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(168,85,247,.12);color:#a855f7">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Uso estimado do plano</p>
                            <p class="settings-card-desc">Indicadores para acompanhar sua capacidade atual</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.8rem">
                    @foreach($usageCards as [$label,$percent,$value,$color])
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.32rem">
                            <p style="font-size:.78rem;font-weight:700;color:#334155;margin:0">{{ $label }}</p>
                            <span style="font-size:.74rem;color:{{ $color }};font-weight:700">{{ $value }}</span>
                        </div>
                        <div style="height:8px;border-radius:999px;background:#eef2ff;overflow:hidden">
                            <div style="height:100%;width:{{ $percent }}%;border-radius:999px;background:linear-gradient(90deg,{{ $color }},{{ $color }}CC)"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="settings-card" style="padding:1.25rem">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Cobrança e próximos passos</p>
                            <p class="settings-card-desc">O que acontece com sua assinatura no próximo ciclo</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.7rem">
                    <div class="settings-info-tile" style="background:rgba(16,185,129,.06)">
                        <p class="settings-info-tile-label">Próxima referência</p>
                        <p class="settings-info-tile-val">{{ $activeSub?->current_period_end?->format('d/m/Y') ?? 'Sem renovação' }}</p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(59,130,246,.06)">
                        <p class="settings-info-tile-label">Método</p>
                        <p class="settings-info-tile-val">{{ $activeSub?->gatewayLabel() ?? 'Manual' }}</p>
                    </div>
                    <div class="settings-info-tile" style="background:rgba(245,158,11,.06)">
                        <p class="settings-info-tile-label">Melhor ação</p>
                        <p class="settings-info-tile-val">{{ $activePlan->isFree() ? 'Considerar upgrade' : 'Acompanhar renovação' }}</p>
                    </div>
                </div>
                <p style="font-size:.74rem;color:#64748b;line-height:1.55;margin:.85rem 0 0">{{ $activePlan->isFree() ? 'Seu uso está perto do limite em alguns recursos. Um plano pago libera mais capacidade e automações.' : 'Seu plano atual já oferece uma boa margem. Revise o consumo antes da próxima renovação para evitar upgrade desnecessário.' }}</p>
            </div>
        </div>

        </div>{{-- /s-col-full --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
