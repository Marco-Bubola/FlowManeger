<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FlowManager — gerencie pedidos, produtos, clientes e integrações como Mercado Livre em um só lugar.">
    <title>FlowManager — Gerencie seu negócio com inteligência</title>
    <link rel="icon" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --pink: #ec4899;
            --purple: #a855f7;
            --cyan: #06b6d4;
            --bg: #050507;
            --bg2: #0a0a0f;
            --card: rgba(255,255,255,0.03);
            --border: rgba(255,255,255,0.07);
            --text: #f1f5f9;
            --muted: rgba(255,255,255,0.45);
            --grad: linear-gradient(135deg, var(--pink), var(--purple), var(--cyan));
        }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        a { color: inherit; text-decoration: none; }

        /* ── BG Glow ─────────────────────────────── */
        .bg-glow {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background: radial-gradient(ellipse 80% 60% at 20% -10%, rgba(168,85,247,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 50% at 80% 10%, rgba(236,72,153,0.08) 0%, transparent 55%),
                        radial-gradient(ellipse 50% 40% at 50% 100%, rgba(6,182,212,0.07) 0%, transparent 60%);
        }
        .noise {
            position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* ── NAVBAR ──────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 5%;
            height: 68px;
            background: rgba(5,5,7,0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        .nav-logo {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em;
        }
        .nav-logo img { width: 32px; height: 32px; border-radius: 8px; }
        .nav-logo span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { font-size: 0.875rem; color: var(--muted); transition: color .2s; }
        .nav-links a:hover { color: #fff; }
        .nav-cta { display: flex; align-items: center; gap: 0.75rem; }
        .btn-ghost {
            padding: 0.5rem 1.2rem; border-radius: 8px; font-size: 0.875rem; font-weight: 500;
            color: rgba(255,255,255,0.65); transition: all .2s; border: 1px solid transparent;
            cursor: pointer; background: transparent;
        }
        .btn-ghost:hover { color: #fff; border-color: var(--border); background: rgba(255,255,255,0.05); }
        .btn-primary {
            padding: 0.55rem 1.4rem; border-radius: 10px; font-size: 0.875rem; font-weight: 600;
            color: #fff; cursor: pointer; border: none; position: relative; overflow: hidden;
            background: linear-gradient(135deg, var(--pink) 0%, var(--purple) 60%, var(--cyan) 100%);
            transition: opacity .2s, transform .15s;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .menu-toggle { display: none; background: none; border: none; cursor: pointer; padding: 0.5rem; }
        .menu-toggle span { display: block; width: 22px; height: 2px; background: #fff; margin: 4px 0; border-radius: 2px; transition: .3s; }

        /* ── HERO ──────────────────────────────── */
        .hero {
            position: relative; z-index: 1;
            min-height: 100svh;
            display: flex; align-items: center; justify-content: center;
            padding: 120px 5% 80px;
            text-align: center;
        }
        .hero-inner { max-width: 860px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.25);
            border-radius: 999px; padding: 0.35rem 0.9rem;
            font-size: 0.78rem; font-weight: 600; color: #c084fc;
            letter-spacing: 0.04em; margin-bottom: 1.8rem;
        }
        .hero-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: #c084fc; animation: pulseDot 2s infinite; }
        @keyframes pulseDot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(1.5); } }
        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900; line-height: 1.08; letter-spacing: -0.03em;
            margin-bottom: 1.4rem;
        }
        .hero h1 .grad { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p {
            font-size: clamp(1rem, 2vw, 1.2rem); line-height: 1.7;
            color: var(--muted); margin-bottom: 2.5rem; max-width: 640px; margin-left: auto; margin-right: auto;
        }
        .hero-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 2rem; border-radius: 12px; font-size: 1rem; font-weight: 700;
            color: #fff; cursor: pointer; border: none; position: relative;
            background: linear-gradient(135deg, var(--pink) 0%, var(--purple) 55%, var(--cyan) 100%);
            box-shadow: 0 0 40px rgba(168,85,247,0.3);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-hero:hover { transform: translateY(-2px); box-shadow: 0 0 60px rgba(168,85,247,0.45); }
        .btn-hero-outline {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 2rem; border-radius: 12px; font-size: 1rem; font-weight: 600;
            color: rgba(255,255,255,0.7); cursor: pointer; background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.12); transition: all .2s;
        }
        .btn-hero-outline:hover { color: #fff; background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.22); }
        .hero-social-proof {
            margin-top: 4rem; display: flex; align-items: center; justify-content: center;
            gap: 1rem; flex-wrap: wrap;
        }
        .avatar-stack { display: flex; }
        .avatar-stack img {
            width: 36px; height: 36px; border-radius: 50%;
            border: 2px solid var(--bg); margin-left: -8px;
            background: linear-gradient(135deg, var(--pink), var(--purple));
            object-fit: cover;
        }
        .avatar-stack .av-placeholder {
            width: 36px; height: 36px; border-radius: 50%;
            border: 2px solid var(--bg); margin-left: -8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700; color: #fff;
        }
        .hero-social-text { font-size: 0.85rem; color: var(--muted); }
        .hero-social-text strong { color: #fff; }
        .star-rating { color: #fbbf24; letter-spacing: -1px; }

        /* ── DASHBOARD PREVIEW ───────────────────── */
        .preview-wrap {
            position: relative; z-index: 1;
            padding: 0 5% 100px;
            display: flex; justify-content: center;
        }
        .preview-frame {
            width: 100%; max-width: 960px;
            border-radius: 20px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 40px 120px rgba(0,0,0,0.6), 0 0 0 1px rgba(168,85,247,0.1);
            background: #0d0d14;
        }
        .preview-topbar {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1rem; background: #111118;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green { background: #28c840; }
        .preview-url-bar {
            flex: 1; background: rgba(255,255,255,0.04); border-radius: 6px;
            padding: 0.3rem 0.8rem; font-size: 0.72rem; color: rgba(255,255,255,0.3);
            margin: 0 1rem; max-width: 300px;
        }
        .preview-body { padding: 1.5rem; display: grid; grid-template-columns: 180px 1fr; gap: 1rem; min-height: 320px; }
        .preview-sidebar { display: flex; flex-direction: column; gap: 0.4rem; }
        .preview-sidebar-item {
            padding: 0.5rem 0.8rem; border-radius: 8px; font-size: 0.75rem; font-weight: 500;
            color: rgba(255,255,255,0.35); display: flex; align-items: center; gap: 0.5rem;
        }
        .preview-sidebar-item.active { background: rgba(168,85,247,0.12); color: #c084fc; }
        .preview-sidebar-icon { width: 14px; height: 14px; border-radius: 3px; background: currentColor; opacity: 0.5; }
        .preview-content { display: flex; flex-direction: column; gap: 0.8rem; }
        .preview-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem; }
        .preview-card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 10px; padding: 0.9rem;
        }
        .preview-card-label { font-size: 0.65rem; color: rgba(255,255,255,0.3); margin-bottom: 0.3rem; }
        .preview-card-value { font-size: 1.1rem; font-weight: 700; }
        .preview-card-value.pink { color: var(--pink); }
        .preview-card-value.purple { color: var(--purple); }
        .preview-card-value.cyan { color: var(--cyan); }
        .preview-chart {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 10px; padding: 1rem; flex: 1;
            display: flex; align-items: flex-end; gap: 0.4rem;
        }
        .preview-bar {
            flex: 1; border-radius: 4px 4px 0 0;
            background: linear-gradient(to top, rgba(168,85,247,0.6), rgba(236,72,153,0.6));
        }

        /* ── SECTION SHARED ──────────────────────── */
        section { position: relative; z-index: 1; }
        .section-label {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(6,182,212,0.08); border: 1px solid rgba(6,182,212,0.2);
            border-radius: 999px; padding: 0.3rem 0.85rem;
            font-size: 0.72rem; font-weight: 700; color: #22d3ee;
            letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 1.2rem;
        }
        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 800;
            letter-spacing: -0.03em; line-height: 1.15; margin-bottom: 1rem;
        }
        .section-sub { font-size: 1.05rem; color: var(--muted); line-height: 1.7; max-width: 580px; }

        /* ── FEATURES ──────────────────────────── */
        .features { padding: 100px 5%; }
        .features-header { text-align: center; margin-bottom: 4rem; }
        .features-header .section-sub { margin: 0 auto; }
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; max-width: 1100px; margin: 0 auto; }
        .feature-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 20px; padding: 2rem;
            transition: transform .25s, border-color .25s, box-shadow .25s;
            position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(168,85,247,0.05) 0%, transparent 60%);
            opacity: 0; transition: opacity .3s;
        }
        .feature-card:hover { transform: translateY(-4px); border-color: rgba(168,85,247,0.25); box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 48px; height: 48px; border-radius: 12px; margin-bottom: 1.2rem;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        }
        .feature-icon.pink { background: rgba(236,72,153,0.12); }
        .feature-icon.purple { background: rgba(168,85,247,0.12); }
        .feature-icon.cyan { background: rgba(6,182,212,0.12); }
        .feature-icon.emerald { background: rgba(16,185,129,0.12); }
        .feature-icon.amber { background: rgba(245,158,11,0.12); }
        .feature-icon.blue { background: rgba(59,130,246,0.12); }
        .feature-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 0.6rem; }
        .feature-desc { font-size: 0.875rem; color: var(--muted); line-height: 1.7; }

        /* ── STATS ──────────────────────────────── */
        .stats {
            padding: 80px 5%;
            background: linear-gradient(180deg, transparent 0%, rgba(168,85,247,0.04) 50%, transparent 100%);
        }
        .stats-inner { max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center; }
        .stat-number {
            font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; letter-spacing: -0.04em;
            background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 0.4rem;
        }
        .stat-label { font-size: 0.875rem; color: var(--muted); }
        .stat-divider { width: 1px; background: var(--border); }

        /* ── INTEGRATIONS ──────────────────────── */
        .integrations { padding: 100px 5%; }
        .integrations-inner { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }
        .integration-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .int-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;
            transition: border-color .2s;
        }
        .int-card:hover { border-color: rgba(255,255,255,0.14); }
        .int-card-logo { font-size: 1.5rem; }
        .int-card-name { font-size: 0.875rem; font-weight: 600; }
        .int-card-status {
            display: inline-flex; align-items: center; gap: 0.35rem;
            font-size: 0.7rem; font-weight: 600; color: #34d399;
        }
        .int-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #34d399; }
        .int-card-status.soon { color: rgba(255,255,255,0.3); }
        .int-card-status.soon .int-status-dot { background: rgba(255,255,255,0.2); }

        /* ── HOW IT WORKS ─────────────────────── */
        .how { padding: 100px 5%; }
        .how-inner { max-width: 1000px; margin: 0 auto; }
        .how-header { text-align: center; margin-bottom: 4rem; }
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; position: relative; }
        .steps::before {
            content: '';
            position: absolute; top: 28px; left: calc(16% + 28px); right: calc(16% + 28px);
            height: 1px; background: linear-gradient(90deg, var(--purple), var(--cyan));
            opacity: 0.3;
        }
        .step { text-align: center; padding: 0 1rem; }
        .step-number {
            width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 1.2rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 900;
            background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(236,72,153,0.2));
            border: 1px solid rgba(168,85,247,0.25); color: #c084fc;
        }
        .step-title { font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; }
        .step-desc { font-size: 0.85rem; color: var(--muted); line-height: 1.65; }

        /* ── CTA BANNER ──────────────────────── */
        .cta-banner {
            margin: 0 5% 100px;
            border-radius: 24px;
            padding: 5rem 3rem;
            text-align: center;
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(168,85,247,0.12) 0%, rgba(236,72,153,0.08) 50%, rgba(6,182,212,0.08) 100%);
            border: 1px solid rgba(168,85,247,0.2);
        }
        .cta-banner::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 60% 60% at 50% 0%, rgba(168,85,247,0.15) 0%, transparent 70%);
        }
        .cta-banner-inner { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; }
        .cta-banner h2 { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 900; letter-spacing: -0.03em; margin-bottom: 1rem; }
        .cta-banner h2 span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cta-banner p { color: var(--muted); font-size: 1.05rem; margin-bottom: 2rem; line-height: 1.6; }

        /* ── FOOTER ──────────────────────────── */
        footer {
            border-top: 1px solid var(--border); padding: 3rem 5%;
            position: relative; z-index: 1;
        }
        .footer-inner { max-width: 1100px; margin: 0 auto; display: flex; align-items: flex-start; justify-content: space-between; gap: 3rem; flex-wrap: wrap; }
        .footer-brand { max-width: 260px; }
        .footer-logo { display: flex; align-items: center; gap: 0.6rem; font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; }
        .footer-logo img { width: 28px; height: 28px; border-radius: 7px; }
        .footer-logo span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .footer-tagline { font-size: 0.85rem; color: var(--muted); line-height: 1.6; }
        .footer-links { display: flex; gap: 4rem; flex-wrap: wrap; }
        .footer-col h4 { font-size: 0.8rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-bottom: 1rem; }
        .footer-col a { display: block; font-size: 0.875rem; color: var(--muted); margin-bottom: 0.6rem; transition: color .2s; }
        .footer-col a:hover { color: #fff; }
        .footer-bottom { max-width: 1100px; margin: 2rem auto 0; padding-top: 1.5rem; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .footer-bottom p { font-size: 0.8rem; color: rgba(255,255,255,0.25); }

        /* ── MOBILE ──────────────────────────── */
        @media (max-width: 768px) {
            nav { padding: 0 1.25rem; }
            .nav-links { display: none; }
            .menu-toggle { display: block; }
            .nav-mobile { display: none; position: fixed; top: 68px; left: 0; right: 0; z-index: 99; background: rgba(5,5,7,0.97); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border); padding: 1rem 1.25rem 1.5rem; flex-direction: column; gap: 0.5rem; }
            .nav-mobile.open { display: flex; }
            .nav-mobile a { padding: 0.7rem 0; font-size: 1rem; color: rgba(255,255,255,0.65); border-bottom: 1px solid var(--border); }
            .nav-mobile a:last-child { border: none; }
            .hero { padding: 100px 1.25rem 60px; }
            .hero-actions { flex-direction: column; align-items: center; }
            .btn-hero, .btn-hero-outline { width: 100%; max-width: 340px; justify-content: center; }
            .preview-body { grid-template-columns: 1fr; }
            .preview-sidebar { display: none; }
            .preview-cards { grid-template-columns: 1fr 1fr; }
            .features { padding: 70px 1.25rem; }
            .features-grid { grid-template-columns: 1fr; }
            .stats-inner { grid-template-columns: 1fr 1fr; }
            .stat-divider { display: none; }
            .integrations { padding: 70px 1.25rem; }
            .integrations-inner { grid-template-columns: 1fr; gap: 3rem; }
            .how { padding: 70px 1.25rem; }
            .steps { grid-template-columns: 1fr; }
            .steps::before { display: none; }
            .cta-banner { margin: 0 1.25rem 60px; padding: 3rem 1.5rem; }
            footer { padding: 2.5rem 1.25rem; }
            .footer-inner { flex-direction: column; }
            .footer-links { gap: 2rem; }
        }
        @media (max-width: 600px) {
            .preview-cards { grid-template-columns: 1fr; }
            .integration-cards { grid-template-columns: 1fr; }
        }

        @keyframes float {
            0%,100% { transform:translateY(0); }
            50% { transform:translateY(-10px); }
        }
        .float { animation: float 5s ease-in-out infinite; }
    </style>
</head>
<body>

<div class="bg-glow"></div>
<div class="noise"></div>

<!-- ── NAVBAR ───────────────────────────────── -->
<nav>
    <a href="/" class="nav-logo">
        <img src="/apple-touch-icon.png" alt="FlowManager">
        <span>FlowManager</span>
    </a>
    <div class="nav-links">
        <a href="#features">Funcionalidades</a>
        <a href="#integrations">Integrações</a>
        <a href="#how">Como funciona</a>
    </div>
    <div class="nav-cta">
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-primary">Ir para o Dashboard →</a>
        @else
            <a href="{{ route('login') }}" class="btn-ghost">Entrar</a>
            <a href="{{ route('register') }}" class="btn-primary">Começar grátis</a>
        @endauth
    </div>
    <button class="menu-toggle" onclick="document.querySelector('.nav-mobile').classList.toggle('open')" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- ── MOBILE NAV ───────────────────────────── -->
<div class="nav-mobile">
    <a href="#features" onclick="document.querySelector('.nav-mobile').classList.remove('open')">Funcionalidades</a>
    <a href="#integrations" onclick="document.querySelector('.nav-mobile').classList.remove('open')">Integrações</a>
    <a href="#how" onclick="document.querySelector('.nav-mobile').classList.remove('open')">Como funciona</a>
    @auth
        <a href="{{ url('/dashboard') }}" style="color:#c084fc;font-weight:600;">Dashboard →</a>
    @else
        <a href="{{ route('login') }}">Entrar</a>
        <a href="{{ route('register') }}" style="color:#c084fc;font-weight:600;">Criar conta grátis →</a>
    @endauth
</div>

<!-- ── HERO ──────────────────────────────────── -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            Novo · Integração Mercado Livre
        </div>

        <h1>
            Gerencie seu negócio<br>
            com <span class="grad">inteligência</span>
        </h1>

        <p>
            Dashboard completo, gestão de pedidos, produtos, clientes e
            integração com Mercado Livre — tudo em uma plataforma moderna,
            rápida e fácil de usar.
        </p>

        <div class="hero-actions">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-hero">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Acessar Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-hero">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Começar grátis agora
                </a>
                <a href="{{ route('login') }}" class="btn-hero-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Entrar na conta
                </a>
            @endauth
        </div>

        <div class="hero-social-proof">
            <div class="avatar-stack">
                <div class="av-placeholder" style="background:linear-gradient(135deg,#ec4899,#8b5cf6);">MR</div>
                <div class="av-placeholder" style="background:linear-gradient(135deg,#8b5cf6,#06b6d4);">LS</div>
                <div class="av-placeholder" style="background:linear-gradient(135deg,#06b6d4,#10b981);">KO</div>
                <div class="av-placeholder" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">PA</div>
            </div>
            <div class="hero-social-text">
                <span class="star-rating">★★★★★</span><br>
                Usado por <strong>+500 vendedores</strong> no Brasil
            </div>
        </div>
    </div>
</section>

<!-- ── DASHBOARD PREVIEW ──────────────────────── -->
<div class="preview-wrap">
    <div class="preview-frame float">
        <div class="preview-topbar">
            <span class="dot dot-red"></span>
            <span class="dot dot-yellow"></span>
            <span class="dot dot-green"></span>
            <div class="preview-url-bar">flowmanager.app/dashboard</div>
        </div>
        <div class="preview-body">
            <div class="preview-sidebar">
                <div class="preview-sidebar-item active">
                    <span class="preview-sidebar-icon" style="background:linear-gradient(135deg,#a855f7,#ec4899);opacity:1;width:14px;height:14px;border-radius:4px;"></span>
                    Dashboard
                </div>
                <div class="preview-sidebar-item"><span class="preview-sidebar-icon"></span> Pedidos</div>
                <div class="preview-sidebar-item"><span class="preview-sidebar-icon"></span> Produtos</div>
                <div class="preview-sidebar-item"><span class="preview-sidebar-icon"></span> Clientes</div>
                <div class="preview-sidebar-item"><span class="preview-sidebar-icon"></span> Relatórios</div>
                <div class="preview-sidebar-item"><span class="preview-sidebar-icon"></span> Mercado Livre</div>
            </div>
            <div class="preview-content">
                <div class="preview-cards">
                    <div class="preview-card">
                        <div class="preview-card-label">Faturamento</div>
                        <div class="preview-card-value pink">R$ 48.320</div>
                    </div>
                    <div class="preview-card">
                        <div class="preview-card-label">Pedidos hoje</div>
                        <div class="preview-card-value purple">127</div>
                    </div>
                    <div class="preview-card">
                        <div class="preview-card-label">Ticket médio</div>
                        <div class="preview-card-value cyan">R$ 380</div>
                    </div>
                </div>
                <div class="preview-chart">
                    <div class="preview-bar" style="height:45%;"></div>
                    <div class="preview-bar" style="height:60%;"></div>
                    <div class="preview-bar" style="height:38%;"></div>
                    <div class="preview-bar" style="height:80%;"></div>
                    <div class="preview-bar" style="height:55%;"></div>
                    <div class="preview-bar" style="height:70%;"></div>
                    <div class="preview-bar" style="height:90%;border: 1px solid rgba(236,72,153,0.4);"></div>
                    <div class="preview-bar" style="height:65%;"></div>
                    <div class="preview-bar" style="height:75%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── STATS ───────────────────────────────────── -->
<section class="stats" id="stats">
    <div class="stats-inner">
        <div>
            <div class="stat-number">500+</div>
            <div class="stat-label">Vendedores ativos</div>
        </div>
        <div>
            <div class="stat-number">R$ 2M+</div>
            <div class="stat-label">Em vendas gerenciadas</div>
        </div>
        <div>
            <div class="stat-number">99.9%</div>
            <div class="stat-label">Uptime garantido</div>
        </div>
        <div>
            <div class="stat-number">24/7</div>
            <div class="stat-label">Suporte disponível</div>
        </div>
    </div>
</section>

<!-- ── FEATURES ───────────────────────────────── -->
<section class="features" id="features">
    <div class="features-header">
        <div class="section-label">Funcionalidades</div>
        <h2 class="section-title">Tudo que você precisa em um só lugar</h2>
        <p class="section-sub">Do cadastro de produtos ao relatório de vendas — sem precisar de vários sistemas diferentes.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon pink">📊</div>
            <div class="feature-title">Dashboard em Tempo Real</div>
            <div class="feature-desc">Visualize faturamento, pedidos, ticket médio e tendências de venda com gráficos atualizados em tempo real.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon purple">📦</div>
            <div class="feature-title">Gestão de Pedidos</div>
            <div class="feature-desc">Acompanhe todos os pedidos em um pipeline visual. Atualize status, gere etiquetas e gerencie devoluções facilmente.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon cyan">🛍️</div>
            <div class="feature-title">Catálogo de Produtos</div>
            <div class="feature-desc">Cadastre produtos com variações, imagens, preços e controle de estoque. Sincronize com o Mercado Livre automaticamente.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon emerald">👥</div>
            <div class="feature-title">CRM de Clientes</div>
            <div class="feature-desc">Histórico completo de compras, categorização por valor e ferramentas para aumentar a recompra e fidelização.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon amber">📈</div>
            <div class="feature-title">Relatórios e Exportação</div>
            <div class="feature-desc">Gere relatórios de vendas, lucratividade e desempenho. Exporte para Excel ou PDF com um clique.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon blue">🔗</div>
            <div class="feature-title">Integrações Nativas</div>
            <div class="feature-desc">Conecte Mercado Livre, Google, GitHub e muito mais. API aberta para integrar com seu sistema atual.</div>
        </div>
    </div>
</section>

<!-- ── INTEGRATIONS ────────────────────────────── -->
<section class="integrations" id="integrations">
    <div class="integrations-inner">
        <div>
            <div class="section-label">Integrações</div>
            <h2 class="section-title">Conectado com<br>as ferramentas que você já usa</h2>
            <p class="section-sub">Sincronize seus anúncios, pedidos e estoque diretamente do FlowManager — sem trabalho manual.</p>
            <br>
            <a href="{{ route('register') }}" class="btn-primary" style="display:inline-block;padding:0.7rem 1.6rem;font-size:0.9rem;">
                Conectar agora →
            </a>
        </div>
        <div class="integration-cards">
            <div class="int-card">
                <div class="int-card-logo">🛒</div>
                <div class="int-card-name">Mercado Livre</div>
                <div class="int-card-status"><span class="int-status-dot"></span> Disponível</div>
            </div>
            <div class="int-card">
                <div class="int-card-logo">🔥</div>
                <div class="int-card-name">Firebase Auth</div>
                <div class="int-card-status"><span class="int-status-dot"></span> Disponível</div>
            </div>
            <div class="int-card">
                <div class="int-card-logo">📊</div>
                <div class="int-card-name">Google Sheets</div>
                <div class="int-card-status soon"><span class="int-status-dot"></span> Em breve</div>
            </div>
            <div class="int-card">
                <div class="int-card-logo">🛍️</div>
                <div class="int-card-name">Shopee</div>
                <div class="int-card-status soon"><span class="int-status-dot"></span> Em breve</div>
            </div>
            <div class="int-card">
                <div class="int-card-logo">📦</div>
                <div class="int-card-name">Bling ERP</div>
                <div class="int-card-status soon"><span class="int-status-dot"></span> Em breve</div>
            </div>
            <div class="int-card">
                <div class="int-card-logo">💳</div>
                <div class="int-card-name">Asaas / Pagar.me</div>
                <div class="int-card-status soon"><span class="int-status-dot"></span> Em breve</div>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────── -->
<section class="how" id="how">
    <div class="how-inner">
        <div class="how-header">
            <div class="section-label">Como funciona</div>
            <h2 class="section-title">Comece em 3 passos simples</h2>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-title">Crie sua conta</div>
                <div class="step-desc">Cadastre-se em segundos com e-mail ou Google. Nenhum cartão de crédito necessário para começar.</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-title">Conecte suas lojas</div>
                <div class="step-desc">Integre com Mercado Livre e outras plataformas. Seus pedidos e produtos sincronizam automaticamente.</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-title">Gerencie com clareza</div>
                <div class="step-desc">Acompanhe tudo no dashboard, tome decisões com dados reais e escale suas vendas com confiança.</div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA BANNER ─────────────────────────────── -->
<div class="cta-banner">
    <div class="cta-banner-inner">
        <h2>Pronto para escalar<br><span>suas vendas?</span></h2>
        <p>Junte-se a centenas de vendedores que já usam o FlowManager para crescer de forma organizada e inteligente.</p>
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-hero" style="display:inline-flex;">
                Acessar Dashboard →
            </a>
        @else
            <a href="{{ route('register') }}" class="btn-hero" style="display:inline-flex;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Criar conta grátis
            </a>
        @endauth
    </div>
</div>

<!-- ── FOOTER ─────────────────────────────────── -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="/apple-touch-icon.png" alt="FlowManager">
                <span>FlowManager</span>
            </div>
            <p class="footer-tagline">A plataforma completa para gestão de vendas online no Brasil.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Produto</h4>
                <a href="#features">Funcionalidades</a>
                <a href="#integrations">Integrações</a>
                <a href="#how">Como funciona</a>
            </div>
            <div class="footer-col">
                <h4>Conta</h4>
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}">Cadastrar</a>
                @auth <a href="{{ url('/dashboard') }}">Dashboard</a> @endauth
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="#">Termos de Uso</a>
                <a href="#">Privacidade</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© {{ date('Y') }} FlowManager. Todos os direitos reservados.</p>
        <p style="color:rgba(255,255,255,0.15);">Feito com ❤️ no Brasil</p>
    </div>
</footer>

</body>
</html>
