{{-- Tela no lugar de uma página que o plano da pessoa não inclui (App\Http\Middleware\PlanFeature). --}}
<x-layouts.app :title="$info[0] . ' - FlowManager'">
    <div class="pl-wrap">
        <div class="pl-card">
            <div class="pl-art">
                <span class="pl-ico bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"><i class="bi {{ $info[1] }}"></i></span>
                <span class="pl-lock"><i class="bi bi-lock-fill"></i></span>
            </div>
            <p class="pl-kicker">Recurso de outro plano</p>
            <h1>{{ $info[0] }} não faz parte do seu plano</h1>
            <p class="pl-sub">
                Você está no plano <b>{{ $current->name }}</b>.
                @if($plan)
                    {{ $info[0] }} vem a partir do plano <b>{{ $plan->name }}</b> ({{ $plan->formattedPriceMonthly() }}{{ $plan->price_monthly > 0 ? ' por mês' : '' }}).
                @else
                    Fale com o suporte para liberar.
                @endif
            </p>
            <div class="pl-actions">
                <a href="{{ route('subscription.plans') }}" wire:navigate class="pl-btn bg-gradient-to-r from-indigo-600 to-purple-600"><i class="bi bi-stars"></i> Ver os planos</a>
                <a href="{{ route('access.center') }}" wire:navigate class="pl-btn-ghost"><i class="bi bi-shield-check"></i> O que meu plano inclui</a>
            </div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="pl-back"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
    </div>
    <style>
        .pl-wrap { min-height: 70vh; display: grid; place-items: center; padding: 1.5rem 1rem 7rem; }
        .pl-card { width: min(560px, 100%); text-align: center; padding: 2rem 1.5rem; border-radius: 1.6rem; background: rgba(255, 255, 255, .92); border: 1px solid #e2e8f0; box-shadow: 0 30px 60px -36px rgba(15, 23, 42, .45); }
        .dark .pl-card { background: rgba(15, 23, 42, .9); border-color: #1e293b; }
        .pl-art { position: relative; width: 5rem; height: 5rem; margin: 0 auto 1rem; }
        .pl-ico { display: grid; place-items: center; width: 5rem; height: 5rem; border-radius: 1.4rem; color: #fff; font-size: 2.1rem; box-shadow: 0 18px 34px -16px color-mix(in oklab, var(--color-indigo-600) 80%, transparent); }
        .pl-lock { position: absolute; right: -.45rem; bottom: -.45rem; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: 999px; background: #0f172a; color: #fff; font-size: .85rem; border: 3px solid #fff; }
        .dark .pl-lock { border-color: #0f172a; background: #f1f5f9; color: #0f172a; }
        .pl-kicker { font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--color-indigo-600); }
        .pl-card h1 { margin-top: .35rem; font-size: 1.35rem; font-weight: 900; color: #0f172a; line-height: 1.25; }
        .dark .pl-card h1 { color: #f8fafc; }
        .pl-sub { margin-top: .6rem; font-size: .9rem; color: #475569; line-height: 1.5; }
        .dark .pl-sub { color: #cbd5e1; }
        .pl-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; margin-top: 1.4rem; }
        .pl-btn, .pl-btn-ghost { display: inline-flex; align-items: center; gap: .45rem; padding: .7rem 1.2rem; border-radius: .9rem; font-size: .88rem; font-weight: 800; }
        .pl-btn { color: #fff; }
        .pl-btn-ghost { background: #f1f5f9; color: #334155; }
        .dark .pl-btn-ghost { background: #1e293b; color: #e2e8f0; }
        .pl-back { display: inline-flex; align-items: center; gap: .35rem; margin-top: 1.1rem; font-size: .8rem; font-weight: 700; color: #64748b; }
    </style>
</x-layouts.app>
