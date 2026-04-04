<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* ─── Background ─── */
        .auth-root {
            min-height: 100dvh;
            background: #050507;
            display: flex;
            align-items: stretch;
            overflow: hidden;
        }

        /* ─── Left branding panel (desktop) ─── */
        .auth-brand-panel {
            display: none;
            position: relative;
            width: 42%;
            flex-shrink: 0;
            overflow: hidden;
            background: linear-gradient(135deg, #0d0d15 0%, #0a0a14 100%);
        }
        @media (min-width: 960px) {
            .auth-brand-panel { display: flex; flex-direction: column; justify-content: space-between; padding: 2.5rem; }
        }

        /* Mesh gradient behind panel */
        .auth-brand-panel::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 50% at 20% 20%, rgba(236,72,153,0.22) 0%, transparent 60%),
                radial-gradient(ellipse 60% 60% at 80% 80%, rgba(99,102,241,0.20) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 50% 50%, rgba(6,182,212,0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Animated grid lines */
        .auth-grid-lines {
            position: absolute; inset: 0; pointer-events: none; overflow: hidden; opacity: 0.07;
            background-image:
                linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Floating orbs */
        .orb {
            position: absolute; border-radius: 50%; pointer-events: none; filter: blur(60px);
        }
        .orb-1 { width: 300px; height: 300px; top: -80px; left: -80px; background: radial-gradient(circle, rgba(236,72,153,0.35), transparent 70%); animation: orbFloat1 12s ease-in-out infinite; }
        .orb-2 { width: 250px; height: 250px; bottom: -60px; right: -60px; background: radial-gradient(circle, rgba(99,102,241,0.30), transparent 70%); animation: orbFloat2 15s ease-in-out infinite; }
        .orb-3 { width: 180px; height: 180px; top: 50%; left: 50%; transform: translate(-50%,-50%); background: radial-gradient(circle, rgba(6,182,212,0.18), transparent 70%); animation: orbFloat3 10s ease-in-out infinite; }

        @keyframes orbFloat1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,20px)} }
        @keyframes orbFloat2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-20px,-30px)} }
        @keyframes orbFloat3 { 0%,100%{transform:translate(-50%,-50%) scale(1)} 50%{transform:translate(-50%,-50%) scale(1.2)} }

        /* Brand panel content */
        .auth-brand-content { position: relative; z-index: 2; display: flex; flex-direction: column; gap: 2rem; flex: 1; justify-content: center; }
        .auth-brand-logo-wrap { display: flex; align-items: center; gap: 0.875rem; }
        .auth-brand-logo { width: 52px; height: 52px; filter: drop-shadow(0 0 20px rgba(236,72,153,0.5)); }
        .auth-brand-name { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; background: linear-gradient(135deg, #f9a8d4 0%, #c084fc 50%, #67e8f9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .auth-brand-tagline { font-size: 2.4rem; font-weight: 800; line-height: 1.15; letter-spacing: -0.03em; color: #fff; }
        .auth-brand-tagline span { background: linear-gradient(135deg, #f472b6 0%, #a78bfa 50%, #38bdf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .auth-brand-sub { font-size: 1rem; color: rgba(255,255,255,0.45); line-height: 1.6; max-width: 340px; }

        /* Feature pills */
        .auth-features { display: flex; flex-direction: column; gap: 0.75rem; }
        .auth-feature-pill {
            display: flex; align-items: center; gap: 0.75rem;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px; padding: 0.75rem 1rem;
            backdrop-filter: blur(8px);
        }
        .auth-feature-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .auth-feature-text { font-size: 0.825rem; color: rgba(255,255,255,0.7); font-weight: 500; }

        /* Brand footer */
        .auth-brand-footer { position: relative; z-index: 2; font-size: 0.75rem; color: rgba(255,255,255,0.25); }

        /* ─── Right form panel ─── */
        .auth-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            padding: 2rem 1.25rem;
            background: #050507;
            position: relative;
            overflow: hidden;
        }
        /* Mobile glow */
        .auth-form-panel::before {
            content: '';
            position: absolute; top: -200px; right: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(167,139,250,0.08) 0%, transparent 65%);
            pointer-events: none;
        }
        .auth-form-panel::after {
            content: '';
            position: absolute; bottom: -200px; left: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(236,72,153,0.06) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Mobile logo (visible only < 960px) */
        .auth-mobile-logo {
            display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
            margin-bottom: 1.75rem; position: relative; z-index: 1;
        }
        @media (min-width: 960px) { .auth-mobile-logo { display: none; } }
        .auth-mobile-logo img { width: 56px; height: 56px; filter: drop-shadow(0 0 18px rgba(236,72,153,0.45)); }
        .auth-mobile-logo-name { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; background: linear-gradient(135deg, #f9a8d4 0%, #c084fc 50%, #67e8f9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Card */
        .auth-card {
            width: 100%; max-width: 420px;
            background: rgba(255,255,255,0.032);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
            position: relative; z-index: 1;
        }
        @media (max-width: 400px) { .auth-card { padding: 1.75rem 1.25rem; border-radius: 16px; } }

        /* Copyright */
        .auth-copyright { font-size: 0.72rem; color: rgba(255,255,255,0.2); margin-top: 1.5rem; position: relative; z-index: 1; text-align: center; }

        /* ─── Form elements ─── */
        .auth-label { display: block; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 0.4rem; }
        .auth-input {
            width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09);
            border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; color: #fff;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            -webkit-appearance: none;
        }
        .auth-input::placeholder { color: rgba(255,255,255,0.22); }
        .auth-input:focus { border-color: rgba(167,139,250,0.6); background: rgba(167,139,250,0.06); box-shadow: 0 0 0 3px rgba(167,139,250,0.12); }
        .auth-input-pw { padding-right: 3rem; }
        .auth-pw-toggle { position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.3); padding: 0.25rem; line-height: 0; transition: color 0.15s; }
        .auth-pw-toggle:hover { color: rgba(255,255,255,0.7); }

        /* Primary button */
        .auth-btn-primary {
            width: 100%; padding: 0.85rem 1.5rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #38bdf8 100%);
            background-size: 200% 200%;
            border: none; border-radius: 12px;
            font-size: 0.9rem; font-weight: 700; color: #fff;
            cursor: pointer; outline: none;
            transition: transform 0.15s, box-shadow 0.2s, background-position 0.4s;
            box-shadow: 0 8px 30px rgba(236,72,153,0.25);
            position: relative; overflow: hidden;
            -webkit-appearance: none;
        }
        .auth-btn-primary::before { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0); transition: background 0.2s; }
        .auth-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 12px 40px rgba(236,72,153,0.35); background-position: 100% 100%; }
        .auth-btn-primary:hover::before { background: rgba(255,255,255,0.07); }
        .auth-btn-primary:active { transform: scale(0.98); }
        .auth-btn-primary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

        /* Social button */
        .auth-social-btn {
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            width: 100%; padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 12px;
            font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.8);
            cursor: pointer; outline: none; transition: all 0.2s;
            -webkit-appearance: none; text-decoration: none;
        }
        .auth-social-btn:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.16); color: #fff; transform: translateY(-1px); }
        .auth-social-btn:active { transform: scale(0.98); }
        .auth-social-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .auth-social-btn svg, .auth-social-btn img { flex-shrink: 0; }

        /* Social grid */
        .auth-social-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.6rem; }
        @media (max-width: 380px) { .auth-social-grid { grid-template-columns: 1fr; } }

        /* Divider */
        .auth-divider { display: flex; align-items: center; gap: 0.875rem; color: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }

        /* Checkbox */
        .auth-checkbox { accent-color: #a78bfa; width: 15px; height: 15px; cursor: pointer; border-radius: 4px; }

        /* Error text */
        .auth-error { font-size: 0.75rem; color: #f87171; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.3rem; }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .auth-spin { animation: spin 0.8s linear infinite; }

        /* Heading */
        .auth-heading { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.025em; color: #fff; line-height: 1.2; }
        .auth-subheading { font-size: 0.875rem; color: rgba(255,255,255,0.4); margin-top: 0.3rem; line-height: 1.5; }

        /* Link */
        .auth-link { color: #c084fc; font-weight: 600; text-decoration: none; transition: color 0.15s; }
        .auth-link:hover { color: #e879f9; }

        /* Badge "grátis" */
        .auth-free-badge { display: inline-flex; align-items: center; gap: 0.3rem; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); color: #34d399; border-radius: 999px; padding: 0.2rem 0.6rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }

        /* Password strength bar */
        .pw-strength-bar { height: 3px; border-radius: 2px; transition: width 0.4s, background-color 0.4s; }
    </style>
</head>
<body class="antialiased">
<div class="auth-root">

    {{-- ── LEFT BRAND PANEL (desktop) ── --}}
    <aside class="auth-brand-panel">
        <div class="auth-grid-lines"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        {{-- Top: logo --}}
        <div style="position:relative;z-index:2;">
            <div class="auth-brand-logo-wrap">
                <img src="/apple-touch-icon.png" alt="FlowManager" class="auth-brand-logo">
                <span class="auth-brand-name">{{ config('app.name', 'FlowManager') }}</span>
            </div>
        </div>

        {{-- Center: tagline + features --}}
        <div class="auth-brand-content">
            <div>
                <p class="auth-brand-tagline">Gerencie seu<br>negócio com<br><span>inteligência</span></p>
                <p class="auth-brand-sub" style="margin-top:1rem;">Controle vendas, estoque, clientes e finanças em um só lugar. Simples, rápido e poderoso.</p>
            </div>

            <div class="auth-features">
                <div class="auth-feature-pill">
                    <div class="auth-feature-icon" style="background:rgba(236,72,153,0.15);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#f472b6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="auth-feature-text">Dashboard com métricas em tempo real</span>
                </div>
                <div class="auth-feature-pill">
                    <div class="auth-feature-icon" style="background:rgba(139,92,246,0.15);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#a78bfa" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="auth-feature-text">Gestão completa de vendas e pedidos</span>
                </div>
                <div class="auth-feature-pill">
                    <div class="auth-feature-icon" style="background:rgba(6,182,212,0.15);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#38bdf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="auth-feature-text">CRM integrado para seus clientes</span>
                </div>
            </div>
        </div>

        {{-- Bottom: copyright --}}
        <div class="auth-brand-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} · Todos os direitos reservados
        </div>
    </aside>

    {{-- ── RIGHT FORM PANEL ── --}}
    <main class="auth-form-panel">

        {{-- Mobile Logo --}}
        <a href="{{ route('home') }}" class="auth-mobile-logo" wire:navigate>
            <img src="/apple-touch-icon.png" alt="{{ config('app.name') }}" class="auth-mobile-logo img">
            <span class="auth-mobile-logo-name">{{ config('app.name', 'FlowManager') }}</span>
        </a>

        {{-- Form Card --}}
        <div class="auth-card">
            {{ $slot }}
        </div>

        <p class="auth-copyright">&copy; {{ date('Y') }} {{ config('app.name') }} · Todos os direitos reservados</p>
    </main>

</div>

@fluxScripts
@stack('scripts')
</body>
</html>
