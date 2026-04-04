@php
    $scope = $scope ?? 'account';
    $title = $title ?? ($scope === 'admin' ? 'Planos e assinaturas' : 'Minha assinatura');
    $subtitle = $subtitle ?? ($scope === 'admin'
        ? 'Gerencie catálogo, assinaturas e acessos com a navegação principal do FlowManager.'
        : 'Acompanhe o plano ativo e navegue para upgrade sem sair do layout principal.');
    $kicker = $kicker ?? ($scope === 'admin' ? 'Central de planos' : 'Assinatura');

    $isPlansPage = request()->routeIs('admin.plans.index') || request()->routeIs('admin.plans.create') || request()->routeIs('admin.plans.edit');

    $links = $scope === 'admin'
        ? [
            ['label' => 'Planos', 'href' => route('admin.plans.index'), 'active' => $isPlansPage],
            ['label' => 'Assinaturas', 'href' => route('admin.subscriptions.index'), 'active' => request()->routeIs('admin.subscriptions.*')],
            ['label' => 'Usuários', 'href' => route('admin.plans.users'), 'active' => request()->routeIs('admin.plans.users')],
        ]
        : [
            ['label' => 'Meu plano', 'href' => route('settings.plan'), 'active' => request()->routeIs('settings.plan')],
            ['label' => 'Trocar plano', 'href' => route('subscription.plans'), 'active' => request()->routeIs('subscription.plans')],
        ];
@endphp

<style>
    .plan-center-nav {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: linear-gradient(135deg, rgba(255,255,255,0.88), rgba(248,250,252,0.92));
        backdrop-filter: blur(16px);
        border-radius: 1.5rem;
        padding: 1rem 1.1rem;
        box-shadow: 0 18px 42px -28px rgba(15, 23, 42, 0.35);
    }

    .dark .plan-center-nav {
        background: linear-gradient(135deg, rgba(15,23,42,0.92), rgba(30,41,59,0.9));
        border-color: rgba(71, 85, 105, 0.55);
        box-shadow: 0 24px 48px -30px rgba(2, 6, 23, 0.8);
    }

    .plan-center-nav__intro {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .plan-center-nav__logo {
        width: 2.85rem;
        height: 2.85rem;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        color: #fff;
        background: linear-gradient(135deg, #3b82f6, #7c3aed);
        box-shadow: 0 14px 30px -16px rgba(59, 130, 246, 0.75);
    }

    .plan-center-nav__copy {
        min-width: 0;
    }

    .plan-center-nav__kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.25rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #6366f1;
    }

    .dark .plan-center-nav__kicker {
        color: #a5b4fc;
    }

    .plan-center-nav__title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .dark .plan-center-nav__title {
        color: #f8fafc;
    }

    .plan-center-nav__subtitle {
        margin: 0.2rem 0 0;
        font-size: 0.82rem;
        line-height: 1.55;
        color: #64748b;
    }

    .dark .plan-center-nav__subtitle {
        color: #94a3b8;
    }

    .plan-center-nav__tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        align-items: center;
        justify-content: flex-end;
    }

    .plan-center-nav__tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.7rem;
        padding: 0.72rem 1rem;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255,255,255,0.7);
        color: #475569;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.18s ease;
    }

    .plan-center-nav__tab:hover {
        transform: translateY(-1px);
        border-color: rgba(99, 102, 241, 0.3);
        color: #312e81;
        background: rgba(238, 242, 255, 0.95);
    }

    .plan-center-nav__tab.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #3b82f6, #7c3aed);
        box-shadow: 0 14px 26px -18px rgba(79, 70, 229, 0.85);
    }

    .dark .plan-center-nav__tab {
        background: rgba(15,23,42,0.62);
        border-color: rgba(71, 85, 105, 0.45);
        color: #cbd5e1;
    }

    .dark .plan-center-nav__tab:hover {
        background: rgba(30,41,59,0.92);
        color: #f8fafc;
        border-color: rgba(129, 140, 248, 0.45);
    }

    @media (max-width: 768px) {
        .plan-center-nav {
            padding: 0.95rem;
            border-radius: 1.25rem;
        }

        .plan-center-nav__intro {
            align-items: flex-start;
        }

        .plan-center-nav__tabs {
            width: 100%;
            justify-content: stretch;
        }

        .plan-center-nav__tab {
            flex: 1 1 140px;
        }
    }
</style>

<div class="plan-center-nav">
    <div class="plan-center-nav__intro">
        <div class="plan-center-nav__logo" aria-hidden="true">
            <img src="/favicon.svg" alt="{{ config('app.name', 'FlowManager') }}" class="h-5 w-5 object-contain" />
        </div>
        <div class="plan-center-nav__copy">
            <span class="plan-center-nav__kicker">{{ $kicker }}</span>
            <h1 class="plan-center-nav__title">{{ $title }}</h1>
            <p class="plan-center-nav__subtitle">{{ $subtitle }}</p>
        </div>
    </div>

    <nav class="plan-center-nav__tabs" aria-label="Navegação da central de planos">
        @foreach($links as $link)
            <a href="{{ $link['href'] }}" class="plan-center-nav__tab {{ $link['active'] ? 'is-active' : '' }}" wire:navigate.hover>
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>
</div>