<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FlowManager — dashboard de vendas, CRM, gestão de produtos e integração com Mercado Livre. Comece grátis hoje.">
    <title>FlowManager — Gerencie seu negócio com inteligência</title>
    <link rel="icon" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --pink:   #ec4899;
            --pink2:  #f472b6;
            --purple: #a855f7;
            --purple2:#8b5cf6;
            --cyan:   #06b6d4;
            --cyan2:  #22d3ee;
            --bg:     #050507;
            --bg2:    #0c0c12;
            --bg3:    #0f0f18;
            --card:   rgba(255,255,255,0.033);
            --card2:  rgba(255,255,255,0.055);
            --border: rgba(255,255,255,0.075);
            --border2:rgba(255,255,255,0.12);
            --text:   #f1f5f9;
            --muted:  rgba(255,255,255,0.48);
            --faint:  rgba(255,255,255,0.22);
            --grad:   linear-gradient(135deg, #ec4899 0%, #a855f7 55%, #06b6d4 100%);
            --grad-r: linear-gradient(135deg, #06b6d4 0%, #a855f7 45%, #ec4899 100%);
            --shadow: 0 24px 80px rgba(0,0,0,0.55);
        }
        html { scroll-behavior: smooth; font-size: 16px; }
        body {
            background: var(--bg); color: var(--text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden; line-height: 1.6;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; }
        button { font-family: inherit; }

        /* ━━ AMBIENT GLOWS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .bg-glow {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 90% 70% at 15% -5%,  rgba(168,85,247,.13) 0%, transparent 60%),
                radial-gradient(ellipse 70% 55% at 85%  5%,  rgba(236,72,153,.09) 0%, transparent 58%),
                radial-gradient(ellipse 60% 45% at 50% 105%, rgba(6,182,212,.08)  0%, transparent 55%);
        }
        .noise {
            position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: .028;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* ━━ NAVBAR ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            height: 64px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 clamp(1rem, 5%, 3.5rem);
            background: rgba(5,5,7,.75); backdrop-filter: blur(24px) saturate(1.5);
            border-bottom: 1px solid rgba(255,255,255,.06);
            transition: background .3s;
        }
        nav.scrolled { background: rgba(5,5,7,.92); }
        .nav-logo {
            display: flex; align-items: center; gap: .55rem;
            font-size: 1.1rem; font-weight: 800; letter-spacing: -.025em; flex-shrink: 0;
        }
        .nav-logo img { width: 30px; height: 30px; border-radius: 7px; }
        .nav-logo-text { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-links {
            display: flex; gap: 1.75rem; align-items: center;
            position: absolute; left: 50%; transform: translateX(-50%);
        }
        .nav-links a {
            font-size: .82rem; font-weight: 500; color: var(--muted);
            transition: color .2s; white-space: nowrap;
        }
        .nav-links a:hover { color: #fff; }
        .nav-cta { display: flex; gap: .6rem; align-items: center; flex-shrink: 0; }
        .btn-nav-ghost {
            padding: .42rem 1.1rem; border-radius: 8px; font-size: .82rem; font-weight: 500;
            color: var(--muted); cursor: pointer; background: transparent;
            border: 1px solid transparent; transition: all .2s;
        }
        .btn-nav-ghost:hover { color: #fff; border-color: var(--border); background: rgba(255,255,255,.05); }
        .btn-nav-primary {
            padding: .44rem 1.2rem; border-radius: 9px; font-size: .82rem; font-weight: 700;
            color: #fff; cursor: pointer; border: none;
            background: var(--grad);
            box-shadow: 0 2px 18px rgba(168,85,247,.28);
            transition: transform .18s, box-shadow .2s;
        }
        .btn-nav-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 28px rgba(168,85,247,.42); }
        .hamburger {
            display: none; flex-direction: column; gap: 5px; background: none;
            border: none; cursor: pointer; padding: .45rem;
        }
        .hamburger span {
            display: block; width: 22px; height: 2px;
            background: rgba(255,255,255,.7); border-radius: 2px;
            transition: all .28s;
        }
        .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ── mobile drawer ── */
        .nav-drawer {
            display: none; position: fixed; top: 64px; left: 0; right: 0; z-index: 190;
            background: rgba(6,6,10,.97); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 1rem clamp(1rem,5%,3.5rem) 1.5rem;
            flex-direction: column; gap: .25rem;
        }
        .nav-drawer.open { display: flex; }
        .nav-drawer a {
            padding: .65rem 0; font-size: .95rem; color: var(--muted);
            border-bottom: 1px solid rgba(255,255,255,.05);
            transition: color .2s;
        }
        .nav-drawer a:last-child { border: none; }
        .nav-drawer a:hover { color: #fff; }
        .nav-drawer a.highlight { color: #c084fc; font-weight: 700; }

        /* ━━ HERO ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .hero {
            position: relative; z-index: 1;
            padding: clamp(110px, 14vw, 160px) clamp(1rem,5%,3.5rem) clamp(60px,6vw,90px);
            text-align: center; min-height: 100svh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(168,85,247,.1); border: 1px solid rgba(168,85,247,.28);
            border-radius: 99px; padding: .3rem .9rem;
            font-size: .72rem; font-weight: 700; letter-spacing: .07em;
            color: #c084fc; text-transform: uppercase; margin-bottom: 1.6rem;
            animation: fadeUp .6s ease both;
        }
        .pulse-dot {
            width: 6px; height: 6px; border-radius: 50%; background: #c084fc;
            animation: pulseDot 2s ease-in-out infinite;
        }
        @keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.8)} }

        .hero-title {
            font-size: clamp(2.4rem, 6.5vw, 5rem); font-weight: 900;
            line-height: 1.06; letter-spacing: -.04em;
            max-width: 820px; margin: 0 auto 1.4rem;
            animation: fadeUp .65s .08s ease both;
        }
        .hero-title .grad-text {
            background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-sub {
            font-size: clamp(.95rem, 2vw, 1.18rem); color: var(--muted);
            max-width: 600px; margin: 0 auto 2.4rem; line-height: 1.75;
            animation: fadeUp .7s .15s ease both;
        }
        .hero-cta {
            display: flex; gap: .9rem; justify-content: center; flex-wrap: wrap;
            animation: fadeUp .7s .22s ease both; margin-bottom: 3.5rem;
        }
        .btn-cta-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .85rem 2rem; border-radius: 14px; font-size: 1rem; font-weight: 800;
            color: #fff; cursor: pointer; border: none;
            background: var(--grad);
            box-shadow: 0 4px 40px rgba(168,85,247,.35), 0 0 0 1px rgba(168,85,247,.2);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-cta-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 55px rgba(168,85,247,.5), 0 0 0 1px rgba(168,85,247,.3); }
        .btn-cta-secondary {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .85rem 2rem; border-radius: 14px; font-size: 1rem; font-weight: 600;
            color: rgba(255,255,255,.72); cursor: pointer;
            background: rgba(255,255,255,.05); border: 1px solid var(--border2);
            transition: all .2s;
        }
        .btn-cta-secondary:hover { color: #fff; background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.2); }
        .hero-trust {
            display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;
            animation: fadeUp .75s .3s ease both;
        }
        .avatars { display: flex; }
        .avatar-item {
            width: 34px; height: 34px; border-radius: 50%;
            border: 2px solid var(--bg); margin-left: -8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .6rem; font-weight: 800; color: #fff;
        }
        .trust-text { font-size: .82rem; color: var(--muted); }
        .trust-text strong { color: #fff; }
        .stars { color: #fbbf24; font-size: .85rem; letter-spacing: -1px; }

        @keyframes fadeUp { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:none; } }

        /* ━━ MARQUEE / TICKER ━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .ticker-wrap {
            position: relative; z-index: 1;
            overflow: hidden; padding: 1.2rem 0;
            border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.018);
            margin-bottom: 0;
        }
        .ticker-label {
            position: absolute; left: 0; top: 0; bottom: 0; z-index: 2;
            width: clamp(80px,12%,160px);
            display: flex; align-items: center; padding: 0 1rem;
            background: linear-gradient(90deg, var(--bg) 70%, transparent);
            font-size: .68rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: var(--faint);
        }
        .ticker-label-end {
            position: absolute; right: 0; top: 0; bottom: 0; z-index: 2;
            width: 80px;
            background: linear-gradient(270deg, var(--bg) 60%, transparent);
        }
        .ticker-track { display: flex; gap: 0; animation: ticker 30s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }
        .ticker-group { display: flex; gap: 2rem; padding: 0 1rem; white-space: nowrap; flex-shrink: 0; }
        .ticker-item {
            display: inline-flex; align-items: center; gap: .5rem;
            font-size: .8rem; color: var(--muted); font-weight: 500;
        }
        .ticker-item b { color: rgba(255,255,255,.65); }
        .ticker-sep { color: rgba(255,255,255,.15); }
        @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* ━━ PREVIEW MOCKUP ━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .preview-section {
            position: relative; z-index: 1;
            padding: clamp(50px,6vw,80px) clamp(1rem,4%,3rem);
            display: flex; justify-content: center;
        }
        .preview-glow {
            position: absolute; top: 10%; left: 50%; transform: translateX(-50%);
            width: 80%; max-width: 900px; height: 400px; z-index: 0;
            background: radial-gradient(ellipse 60% 60% at 50% 50%, rgba(168,85,247,.12) 0%, transparent 70%);
            filter: blur(40px); pointer-events: none;
        }
        .preview-outer {
            position: relative; z-index: 1;
            width: 100%; max-width: 1000px;
            animation: floatY 6s ease-in-out infinite;
        }
        @keyframes floatY { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        .preview-browser {
            border-radius: 18px; overflow: hidden;
            border: 1px solid rgba(255,255,255,.09);
            box-shadow: 0 50px 140px rgba(0,0,0,.65), 0 0 0 1px rgba(168,85,247,.08);
            background: #0d0d16;
        }
        .pb-bar {
            display: flex; align-items: center; gap: .5rem;
            padding: .65rem 1rem; background: #111120;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        .pb-dot { width: 11px; height: 11px; border-radius: 50%; }
        .pb-dot-r { background: #ff5f57; }
        .pb-dot-y { background: #febc2e; }
        .pb-dot-g { background: #28c840; }
        .pb-url {
            margin: 0 .75rem; flex: 1; max-width: 280px;
            background: rgba(255,255,255,.04); border-radius: 6px;
            padding: .28rem .75rem; font-size: .68rem; color: rgba(255,255,255,.28);
        }
        .pb-body { display: grid; grid-template-columns: 175px 1fr; min-height: 300px; }
        .pb-sidebar {
            background: rgba(0,0,0,.25); border-right: 1px solid rgba(255,255,255,.05);
            padding: .9rem .6rem; display: flex; flex-direction: column; gap: .25rem;
        }
        .pb-sidebar-logo {
            display: flex; align-items: center; gap: .4rem;
            padding: .4rem .6rem; margin-bottom: .5rem;
            font-size: .72rem; font-weight: 800; color: #c084fc;
        }
        .pb-sidebar-logo-icon {
            width: 18px; height: 18px; border-radius: 5px;
            background: var(--grad); display: flex; align-items: center;
            justify-content: center; font-size: .6rem; font-weight: 900; color: #fff;
        }
        .pb-nav-item {
            display: flex; align-items: center; gap: .45rem;
            padding: .42rem .65rem; border-radius: 7px; font-size: .7rem; font-weight: 500;
            color: rgba(255,255,255,.32); transition: all .15s; cursor: default;
        }
        .pb-nav-item.act { background: rgba(168,85,247,.12); color: #c084fc; }
        .pb-nav-icon { width: 12px; height: 12px; border-radius: 3px; background: currentColor; opacity: .7; }
        .pb-content { padding: 1rem; display: flex; flex-direction: column; gap: .75rem; }
        .pb-header { display: flex; align-items: center; justify-content: space-between; }
        .pb-header-title { font-size: .8rem; font-weight: 700; }
        .pb-header-badge {
            font-size: .6rem; padding: .2rem .5rem; border-radius: 5px;
            background: rgba(16,185,129,.12); color: #34d399; font-weight: 700;
        }
        .pb-kpis { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; }
        .pb-kpi {
            background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.055);
            border-radius: 9px; padding: .65rem .7rem;
        }
        .pb-kpi-label { font-size: .58rem; color: rgba(255,255,255,.3); margin-bottom: .2rem; }
        .pb-kpi-val { font-size: 1rem; font-weight: 800; }
        .pb-kpi-val.p { color: var(--pink); }
        .pb-kpi-val.v { color: var(--purple); }
        .pb-kpi-val.c { color: var(--cyan); }
        .pb-kpi-chg { font-size: .58rem; color: #34d399; margin-top: .1rem; }
        .pb-chart {
            background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.05);
            border-radius: 9px; padding: .75rem; display: flex; flex-direction: column; gap: .5rem; flex: 1;
        }
        .pb-chart-label { font-size: .62rem; color: rgba(255,255,255,.25); font-weight: 600; }
        .pb-bars { display: flex; align-items: flex-end; gap: .3rem; height: 80px; }
        .pb-bar-item {
            flex: 1; border-radius: 3px 3px 0 0; min-width: 0;
            background: linear-gradient(to top, rgba(168,85,247,.5), rgba(236,72,153,.45));
            transition: filter .2s;
        }
        .pb-bar-item.active {
            background: linear-gradient(to top, rgba(168,85,247,.9), rgba(236,72,153,.8));
            box-shadow: 0 0 8px rgba(168,85,247,.4);
        }

        /* ━━ SECTION SKELETON ━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .section-wrap { position: relative; z-index: 1; }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 clamp(1rem,5%,3.5rem); }
        .section-pad { padding: clamp(70px,9vw,110px) 0; }
        .section-tag {
            display: inline-flex; align-items: center; gap: .45rem;
            border-radius: 99px; padding: .28rem .85rem;
            font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            margin-bottom: 1.1rem;
        }
        .tag-cyan { background: rgba(6,182,212,.09); border: 1px solid rgba(6,182,212,.22); color: var(--cyan2); }
        .tag-purple { background: rgba(168,85,247,.09); border: 1px solid rgba(168,85,247,.22); color: #c084fc; }
        .tag-pink { background: rgba(236,72,153,.09); border: 1px solid rgba(236,72,153,.22); color: var(--pink2); }
        .tag-green { background: rgba(16,185,129,.09); border: 1px solid rgba(16,185,129,.22); color: #34d399; }
        .section-h2 {
            font-size: clamp(1.75rem, 4vw, 2.9rem); font-weight: 900;
            letter-spacing: -.03em; line-height: 1.12; margin-bottom: .9rem;
        }
        .section-sub {
            font-size: clamp(.9rem,1.8vw,1.05rem); color: var(--muted);
            line-height: 1.75; max-width: 560px;
        }
        .text-center { text-align: center; }
        .text-center .section-sub { margin: 0 auto; }

        /* ━━ FEATURES ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .features-grid {
            display: grid; gap: 1.25rem; margin-top: 3.5rem;
            grid-template-columns: repeat(3, 1fr);
        }
        .feat-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 20px; padding: clamp(1.4rem,3vw,2rem);
            position: relative; overflow: hidden;
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }
        .feat-card::after {
            content: ''; position: absolute; inset: 0; border-radius: 20px;
            background: linear-gradient(135deg, rgba(168,85,247,.06) 0%, transparent 65%);
            opacity: 0; transition: opacity .3s;
        }
        .feat-card:hover { transform: translateY(-5px); border-color: rgba(168,85,247,.25); box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .feat-card:hover::after { opacity: 1; }
        .feat-icon {
            width: 46px; height: 46px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; margin-bottom: 1.1rem; position: relative; z-index: 1;
        }
        .i-pink   { background: rgba(236,72,153,.12); }
        .i-purple { background: rgba(168,85,247,.12); }
        .i-cyan   { background: rgba(6,182,212,.12); }
        .i-emerald{ background: rgba(16,185,129,.12); }
        .i-amber  { background: rgba(245,158,11,.12); }
        .i-blue   { background: rgba(59,130,246,.12); }
        .i-rose   { background: rgba(244,63,94,.12); }
        .i-teal   { background: rgba(20,184,166,.12); }
        .feat-title { font-size: .98rem; font-weight: 700; margin-bottom: .5rem; position: relative; z-index: 1; }
        .feat-desc { font-size: .84rem; color: var(--muted); line-height: 1.7; position: relative; z-index: 1; }
        .feat-card.feat-wide { grid-column: span 2; }
        .feat-card.feat-wide .feat-inner { display: flex; align-items: flex-start; gap: 2rem; flex-wrap: wrap; }
        .feat-card.feat-wide .feat-text {}
        .feat-tag {
            display: inline-block; margin-top: .7rem;
            font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
            padding: .25rem .65rem; border-radius: 6px;
            background: rgba(168,85,247,.12); color: #c084fc;
        }
        .feat-tag.new { background: rgba(16,185,129,.12); color: #34d399; }

        /* ━━ STATS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .stats-strip {
            background: linear-gradient(180deg, rgba(168,85,247,.05) 0%, rgba(6,182,212,.04) 100%);
            border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
        }
        .stats-grid {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 0; padding: clamp(40px,5vw,60px) 0;
        }
        .stat-box {
            text-align: center; padding: 1rem 1.5rem;
            border-right: 1px solid var(--border);
        }
        .stat-box:last-child { border: none; }
        .stat-num {
            font-size: clamp(2rem,4.5vw,3.2rem); font-weight: 900; letter-spacing: -.04em;
            background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            line-height: 1; margin-bottom: .35rem;
        }
        .stat-lbl { font-size: .82rem; color: var(--muted); line-height: 1.4; }

        /* ━━ HOW IT WORKS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .steps-grid {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 2rem; margin-top: 3.5rem;
            position: relative;
        }
        .steps-line {
            position: absolute; top: 34px; left: calc(50%/3 + 34px);
            right: calc(50%/3 + 34px); height: 1px;
            background: linear-gradient(90deg, rgba(168,85,247,.4), rgba(6,182,212,.4));
        }
        .step-card { text-align: center; padding: 0 .5rem; position: relative; z-index: 1; }
        .step-num {
            width: 60px; height: 60px; border-radius: 18px; margin: 0 auto 1.3rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; font-weight: 900; color: #c084fc;
            background: linear-gradient(135deg, rgba(168,85,247,.18), rgba(236,72,153,.12));
            border: 1px solid rgba(168,85,247,.28);
            box-shadow: 0 0 30px rgba(168,85,247,.12);
        }
        .step-title { font-size: 1rem; font-weight: 700; margin-bottom: .5rem; }
        .step-desc { font-size: .84rem; color: var(--muted); line-height: 1.7; }

        /* ━━ TESTIMONIALS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .testi-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.25rem; margin-top: 3rem; }
        .testi-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px;
            padding: 1.6rem; display: flex; flex-direction: column; gap: 1rem;
        }
        .testi-stars { color: #fbbf24; font-size: .9rem; letter-spacing: -1px; }
        .testi-text { font-size: .88rem; color: rgba(255,255,255,.62); line-height: 1.7; flex: 1; }
        .testi-author { display: flex; align-items: center; gap: .7rem; }
        .testi-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .testi-name { font-size: .84rem; font-weight: 700; }
        .testi-role { font-size: .72rem; color: var(--muted); }

        /* ━━ PRICING ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .pricing-toggle {
            display: flex; align-items: center; justify-content: center; gap: .8rem;
            margin: 2rem 0 3rem;
        }
        .toggle-label { font-size: .85rem; color: var(--muted); font-weight: 500; }
        .toggle-label.active { color: #fff; font-weight: 700; }
        .toggle-pill {
            width: 46px; height: 26px; border-radius: 13px; cursor: pointer;
            background: rgba(168,85,247,.25); border: 1px solid rgba(168,85,247,.35);
            position: relative; transition: background .25s;
        }
        .toggle-pill .knob {
            position: absolute; top: 3px; left: 3px; width: 18px; height: 18px;
            border-radius: 50%; background: #c084fc; transition: transform .25s;
        }
        .toggle-pill.annual { background: rgba(168,85,247,.45); }
        .toggle-pill.annual .knob { transform: translateX(20px); }
        .annual-badge {
            font-size: .65rem; font-weight: 800; padding: .2rem .5rem; border-radius: 6px;
            background: rgba(16,185,129,.14); color: #34d399; letter-spacing: .04em;
        }
        .pricing-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.25rem; align-items: start; }
        .plan-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 22px;
            padding: clamp(1.6rem,3vw,2.2rem); position: relative; overflow: hidden;
            transition: transform .25s, box-shadow .25s;
        }
        .plan-card:hover { transform: translateY(-4px); }
        .plan-card.popular {
            border-color: rgba(168,85,247,.4);
            box-shadow: 0 0 0 1px rgba(168,85,247,.15), 0 20px 80px rgba(168,85,247,.12);
            background: linear-gradient(180deg, rgba(168,85,247,.08) 0%, var(--card) 100%);
        }
        .plan-popular-badge {
            position: absolute; top: -1px; left: 50%; transform: translateX(-50%);
            background: var(--grad); color: #fff;
            font-size: .65rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            padding: .3rem 1rem; border-radius: 0 0 10px 10px;
        }
        .plan-name { font-size: .8rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: .5rem; }
        .plan-price { display: flex; align-items: flex-end; gap: .3rem; margin-bottom: .3rem; }
        .plan-currency { font-size: 1.2rem; font-weight: 700; color: var(--muted); padding-bottom: .5rem; }
        .plan-amount { font-size: clamp(2.4rem,5vw,3.4rem); font-weight: 900; letter-spacing: -.04em; line-height: 1; }
        .plan-period { font-size: .8rem; color: var(--muted); padding-bottom: .45rem; }
        .plan-desc { font-size: .82rem; color: var(--muted); margin-bottom: 1.5rem; line-height: 1.5; }
        .plan-divider { height: 1px; background: var(--border); margin: 1.25rem 0; }
        .plan-features { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.75rem; }
        .plan-feat {
            display: flex; align-items: flex-start; gap: .55rem;
            font-size: .83rem; color: rgba(255,255,255,.72); line-height: 1.45;
        }
        .plan-feat .check { color: var(--cyan2); font-size: .88rem; line-height: 1.45; flex-shrink: 0; }
        .plan-feat .x { color: rgba(255,255,255,.2); font-size: .88rem; line-height: 1.45; flex-shrink: 0; }
        .plan-feat.dimmed { color: rgba(255,255,255,.28); }
        .btn-plan {
            display: block; width: 100%; padding: .8rem; border-radius: 12px;
            font-size: .9rem; font-weight: 700; text-align: center; cursor: pointer;
            border: none; transition: all .2s;
        }
        .btn-plan-outline {
            background: rgba(255,255,255,.06); color: rgba(255,255,255,.7);
            border: 1px solid var(--border2);
        }
        .btn-plan-outline:hover { background: rgba(255,255,255,.1); color: #fff; }
        .btn-plan-primary {
            background: var(--grad); color: #fff;
            box-shadow: 0 4px 30px rgba(168,85,247,.3);
        }
        .btn-plan-primary:hover { box-shadow: 0 6px 45px rgba(168,85,247,.5); transform: translateY(-1px); }
        .btn-plan-outline-purple {
            background: rgba(168,85,247,.08); color: #c084fc;
            border: 1px solid rgba(168,85,247,.25);
        }
        .btn-plan-outline-purple:hover { background: rgba(168,85,247,.15); }
        .plan-note { font-size: .7rem; color: var(--faint); text-align: center; margin-top: .75rem; }
        .pricing-guarantee {
            text-align: center; margin-top: 2.5rem;
            font-size: .85rem; color: var(--muted);
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .pricing-guarantee .shield { color: #34d399; }

        /* ━━ FAQ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .faq-list { max-width: 720px; margin: 3rem auto 0; display: flex; flex-direction: column; gap: .75rem; }
        .faq-item {
            background: var(--card); border: 1px solid var(--border); border-radius: 14px;
            overflow: hidden;
        }
        .faq-q {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.1rem 1.4rem; cursor: pointer; font-size: .9rem; font-weight: 600;
            gap: 1rem; transition: color .2s;
        }
        .faq-q:hover { color: var(--cyan2); }
        .faq-icon { flex-shrink: 0; font-size: 1.1rem; color: var(--muted); transition: transform .25s; }
        .faq-item.open .faq-icon { transform: rotate(45deg); color: var(--cyan2); }
        .faq-a { display: none; padding: 0 1.4rem 1.2rem; font-size: .85rem; color: var(--muted); line-height: 1.75; }
        .faq-item.open .faq-a { display: block; }
        .faq-item.open { border-color: rgba(6,182,212,.2); }

        /* ━━ CTA FINAL ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        .cta-final {
            margin: 0 clamp(.75rem,3%,2rem) clamp(60px,8vw,100px);
            border-radius: 28px; padding: clamp(50px,7vw,90px) clamp(1.5rem,5%,4rem);
            text-align: center; position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(168,85,247,.13) 0%, rgba(236,72,153,.09) 50%, rgba(6,182,212,.08) 100%);
            border: 1px solid rgba(168,85,247,.22);
        }
        .cta-final-glow {
            position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(ellipse 70% 70% at 50% 0%, rgba(168,85,247,.18) 0%, transparent 70%);
        }
        .cta-final-inner { position: relative; z-index: 1; max-width: 660px; margin: 0 auto; }
        .cta-final h2 { font-size: clamp(1.8rem,4.5vw,3rem); font-weight: 900; letter-spacing: -.035em; margin-bottom: .9rem; }
        .cta-final h2 span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cta-final p { font-size: clamp(.9rem,1.8vw,1.05rem); color: var(--muted); margin-bottom: 2rem; line-height: 1.7; }
        .cta-final-actions { display: flex; gap: .9rem; justify-content: center; flex-wrap: wrap; }
        .cta-final-note { margin-top: 1.2rem; font-size: .78rem; color: var(--faint); }

        /* ━━ FOOTER ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        footer {
            position: relative; z-index: 1;
            border-top: 1px solid var(--border);
            padding: clamp(40px,5vw,60px) clamp(1rem,5%,3.5rem) clamp(20px,3vw,32px);
        }
        .footer-top { display: flex; justify-content: space-between; gap: 3rem; flex-wrap: wrap; margin-bottom: 3rem; }
        .footer-brand { max-width: 260px; }
        .footer-logo { display: flex; align-items: center; gap: .55rem; margin-bottom: .8rem; }
        .footer-logo img { width: 28px; height: 28px; border-radius: 7px; }
        .footer-logo-text { font-size: 1rem; font-weight: 800; }
        .footer-logo-text span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .footer-tagline { font-size: .82rem; color: var(--muted); line-height: 1.65; }
        .footer-cols { display: flex; gap: clamp(2rem,5vw,4rem); flex-wrap: wrap; }
        .footer-col-title { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.38); margin-bottom: .9rem; }
        .footer-col a { display: block; font-size: .84rem; color: var(--muted); margin-bottom: .5rem; transition: color .2s; }
        .footer-col a:hover { color: #fff; }
        .footer-bottom {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            padding-top: 1.5rem; border-top: 1px solid var(--border);
        }
        .footer-copy { font-size: .77rem; color: rgba(255,255,255,.22); }
        .footer-made { font-size: .77rem; color: rgba(255,255,255,.16); }
        .footer-badges { display: flex; gap: .5rem; }
        .footer-badge {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .67rem; font-weight: 700; padding: .25rem .65rem; border-radius: 6px;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        /* ━━ RESPONSIVE ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2,1fr); }
            .feat-card.feat-wide { grid-column: span 2; }
            .pricing-grid { grid-template-columns: repeat(2,1fr); }
            .pricing-grid > :nth-child(3) { grid-column: span 2; max-width: 520px; margin: 0 auto; width: 100%; }
            .testi-grid { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 860px) {
            .nav-links { display: none; }
            .hamburger { display: flex; }
            .pb-body { grid-template-columns: 1fr; }
            .pb-sidebar { display: none; }
            .pb-kpis { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 720px) {
            .features-grid { grid-template-columns: 1fr; }
            .feat-card.feat-wide { grid-column: span 1; }
            .feat-card.feat-wide .feat-inner { flex-direction: column; }
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .stat-box { border-right: none; border-bottom: 1px solid var(--border); }
            .stat-box:nth-child(3) { border-bottom: none; }
            .stat-box:last-child { border: none; }
            .steps-grid { grid-template-columns: 1fr; }
            .steps-line { display: none; }
            .pricing-grid { grid-template-columns: 1fr; }
            .pricing-grid > :nth-child(3) { grid-column: span 1; max-width: 100%; }
            .testi-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .hero-cta { flex-direction: column; align-items: stretch; }
            .btn-cta-primary, .btn-cta-secondary { width: 100%; max-width: 100%; justify-content: center; }
            .cta-final-actions { flex-direction: column; align-items: stretch; }
            .cta-final-actions a { width: 100%; justify-content: center; }
            .pb-kpis { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="bg-glow"></div>
<div class="noise"></div>

<!-- ━━ NAVBAR ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<nav id="navbar">
    <a href="/" class="nav-logo">
        <img src="/apple-touch-icon.png" alt="FlowManager">
        <span class="nav-logo-text">FlowManager</span>
    </a>

    <div class="nav-links">
        <a href="#features">Funcionalidades</a>
        <a href="#integrations">Integrações</a>
        <a href="#how">Como funciona</a>
        <a href="#pricing">Preços</a>
        <a href="#faq">FAQ</a>
    </div>

    <div class="nav-cta">
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-nav-primary">Acessar Dashboard →</a>
        @else
            <a href="{{ route('login') }}" class="btn-nav-ghost">Entrar</a>
            <a href="{{ route('register') }}" class="btn-nav-primary">Começar grátis</a>
        @endauth
    </div>

    <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<nav class="nav-drawer" id="drawer">
    <a href="#features" onclick="closeDrawer()">Funcionalidades</a>
    <a href="#integrations" onclick="closeDrawer()">Integrações</a>
    <a href="#how" onclick="closeDrawer()">Como funciona</a>
    <a href="#pricing" onclick="closeDrawer()">Preços</a>
    <a href="#faq" onclick="closeDrawer()">FAQ</a>
    @auth
        <a href="{{ url('/dashboard') }}" class="highlight" onclick="closeDrawer()">Acessar Dashboard →</a>
    @else
        <a href="{{ route('login') }}" onclick="closeDrawer()">Entrar na conta</a>
        <a href="{{ route('register') }}" class="highlight" onclick="closeDrawer()">Criar conta grátis →</a>
    @endauth
</nav>

<!-- ━━ HERO ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="hero">
    <div class="hero-eyebrow">
        <span class="pulse-dot"></span>
        Novo · Integração Mercado Livre ativa
    </div>

    <h1 class="hero-title">
        Gerencie seu negócio<br>
        com <span class="grad-text">inteligência real</span>
    </h1>

    <p class="hero-sub">
        Dashboard completo, gestão de pedidos, CRM de clientes e sincronização
        automática com Mercado Livre — tudo em uma plataforma moderna, rápida e
        feita para vendedores brasileiros.
    </p>

    <div class="hero-cta">
        @auth
            <a href="{{ url('/dashboard') }}" class="btn-cta-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Ir para o Dashboard
            </a>
        @else
            <a href="{{ route('register') }}" class="btn-cta-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Começar grátis agora
            </a>
            <a href="#pricing" class="btn-cta-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Ver planos e preços
            </a>
        @endauth
    </div>

    <div class="hero-trust">
        <div class="avatars">
            <div class="avatar-item" style="background:linear-gradient(135deg,#ec4899,#8b5cf6)">MR</div>
            <div class="avatar-item" style="background:linear-gradient(135deg,#8b5cf6,#06b6d4)">LS</div>
            <div class="avatar-item" style="background:linear-gradient(135deg,#06b6d4,#10b981)">AO</div>
            <div class="avatar-item" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">PF</div>
            <div class="avatar-item" style="background:linear-gradient(135deg,#8b5cf6,#ec4899)">CB</div>
        </div>
        <div class="trust-text">
            <span class="stars">★★★★★</span>&nbsp;
            Avaliado por <strong>+500 vendedores</strong> &nbsp;·&nbsp; Sem cartão de crédito
        </div>
    </div>
</section>

<!-- ━━ TICKER ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<div class="ticker-wrap">
    <div class="ticker-label">Confiado por</div>
    <div class="ticker-label-end"></div>
    <div class="ticker-track">
        <div class="ticker-group">
            <span class="ticker-item">🛒 <b>+500 vendedores</b> ativos</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">📦 <b>+48.000 pedidos</b> processados</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">💰 <b>R$ 2M+</b> em vendas gerenciadas</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">⭐ 4.9/5 de satisfação</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🔗 Mercado Livre integrado</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🚀 99.9% uptime garantido</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🤖 IA + Gemini nativo</span>
            <span class="ticker-sep">·</span>
        </div>
        <div class="ticker-group" aria-hidden="true">
            <span class="ticker-item">🛒 <b>+500 vendedores</b> ativos</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">📦 <b>+48.000 pedidos</b> processados</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">💰 <b>R$ 2M+</b> em vendas gerenciadas</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">⭐ 4.9/5 de satisfação</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🔗 Mercado Livre integrado</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🚀 99.9% uptime garantido</span>
            <span class="ticker-sep">·</span>
            <span class="ticker-item">🤖 IA + Gemini nativo</span>
            <span class="ticker-sep">·</span>
        </div>
    </div>
</div>

<!-- ━━ DASHBOARD PREVIEW ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<div class="preview-section">
    <div class="preview-glow"></div>
    <div class="preview-outer">
        <div class="preview-browser">
            <div class="pb-bar">
                <span class="pb-dot pb-dot-r"></span>
                <span class="pb-dot pb-dot-y"></span>
                <span class="pb-dot pb-dot-g"></span>
                <div class="pb-url">app.flowmanager.com.br/dashboard</div>
            </div>
            <div class="pb-body">
                <div class="pb-sidebar">
                    <div class="pb-sidebar-logo">
                        <div class="pb-sidebar-logo-icon">F</div>
                        FlowManager
                    </div>
                    <div class="pb-nav-item act"><span class="pb-nav-icon" style="background:var(--purple);opacity:1;border-radius:4px;"></span> Dashboard</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Pedidos</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Produtos</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Clientes</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Relatórios</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Mercado Livre</div>
                    <div class="pb-nav-item"><span class="pb-nav-icon"></span> Financeiro</div>
                </div>
                <div class="pb-content">
                    <div class="pb-header">
                        <div class="pb-header-title">Visão Geral — Hoje</div>
                        <div class="pb-header-badge">↑ +12.4% vs ontem</div>
                    </div>
                    <div class="pb-kpis">
                        <div class="pb-kpi">
                            <div class="pb-kpi-label">Faturamento</div>
                            <div class="pb-kpi-val p">R$ 48.320</div>
                            <div class="pb-kpi-chg">↑ 18% este mês</div>
                        </div>
                        <div class="pb-kpi">
                            <div class="pb-kpi-label">Pedidos hoje</div>
                            <div class="pb-kpi-val v">127</div>
                            <div class="pb-kpi-chg">↑ 12 novos</div>
                        </div>
                        <div class="pb-kpi">
                            <div class="pb-kpi-label">Ticket médio</div>
                            <div class="pb-kpi-val c">R$ 380</div>
                            <div class="pb-kpi-chg">↑ R$ 24 vs mês ant.</div>
                        </div>
                    </div>
                    <div class="pb-chart">
                        <div class="pb-chart-label">FATURAMENTO — ÚLTIMOS 9 DIAS</div>
                        <div class="pb-bars">
                            <div class="pb-bar-item" style="height:42%"></div>
                            <div class="pb-bar-item" style="height:58%"></div>
                            <div class="pb-bar-item" style="height:35%"></div>
                            <div class="pb-bar-item" style="height:72%"></div>
                            <div class="pb-bar-item" style="height:51%"></div>
                            <div class="pb-bar-item" style="height:66%"></div>
                            <div class="pb-bar-item" style="height:80%"></div>
                            <div class="pb-bar-item" style="height:60%"></div>
                            <div class="pb-bar-item active" style="height:93%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ━━ STATS STRIP ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<div class="section-wrap stats-strip">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-num">500+</div>
                <div class="stat-lbl">Vendedores ativos</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">R$ 2M+</div>
                <div class="stat-lbl">Em vendas gerenciadas</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">48k+</div>
                <div class="stat-lbl">Pedidos processados</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">99.9%</div>
                <div class="stat-lbl">Uptime garantido</div>
            </div>
        </div>
    </div>
</div>

<!-- ━━ FEATURES ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="section-wrap section-pad" id="features">
    <div class="container">
        <div class="text-center">
            <div class="section-tag tag-cyan">Funcionalidades</div>
            <h2 class="section-h2">Tudo que você precisa, em um só lugar</h2>
            <p class="section-sub">Do cadastro de produtos ao relatório de lucratividade — sem precisar de 5 ferramentas diferentes.</p>
        </div>

        <div class="features-grid" id="integrations">
            <div class="feat-card feat-wide">
                <div class="feat-inner">
                    <div>
                        <div class="feat-icon i-purple">📊</div>
                        <div class="feat-title">Dashboard em Tempo Real</div>
                        <div class="feat-desc">Acompanhe faturamento, pedidos, ticket médio, clientes novos e tendências com gráficos atualizados a cada minuto. Tome decisões baseadas em dados reais.</div>
                        <span class="feat-tag">Gráficos ao vivo</span>
                    </div>
                    <div>
                        <div class="feat-icon i-pink">🛒</div>
                        <div class="feat-title">Integração Mercado Livre</div>
                        <div class="feat-desc">Sincronize anúncios, pedidos, estoque e perguntas do ML automaticamente. Gerencie suas vendas sem sair do FlowManager.</div>
                        <span class="feat-tag new">Ativo agora</span>
                    </div>
                </div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-cyan">📦</div>
                <div class="feat-title">Gestão de Pedidos</div>
                <div class="feat-desc">Pipeline visual com todos os pedidos. Atualize status, gere etiquetas de envio e gerencie devoluções com poucos cliques.</div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-emerald">👥</div>
                <div class="feat-title">CRM de Clientes</div>
                <div class="feat-desc">Histórico completo de compras, segmentação por valor, etiquetas personalizadas e ferramentas para aumentar a recompra.</div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-amber">🏷️</div>
                <div class="feat-title">Catálogo de Produtos</div>
                <div class="feat-desc">Cadastre produtos com variações, múltiplas imagens, precificação automática e controle de estoque em tempo real.</div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-blue">📈</div>
                <div class="feat-title">Relatórios Avançados</div>
                <div class="feat-desc">Gere relatórios de vendas, lucratividade por produto, desempenho por canal e exporte tudo para Excel ou PDF.</div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-rose">💳</div>
                <div class="feat-title">Controle Financeiro</div>
                <div class="feat-desc">Receitas, despesas, margem de lucro e fluxo de caixa. Entenda exatamente quanto seu negócio está rendendo.</div>
            </div>

            <div class="feat-card">
                <div class="feat-icon i-teal">🤖</div>
                <div class="feat-title">IA + Gemini</div>
                <div class="feat-desc">Sugestões automáticas de precificação, previsão de demanda e insights gerados por inteligência artificial para impulsionar suas vendas.</div>
                <span class="feat-tag new">Beta</span>
            </div>
        </div>
    </div>
</section>

<!-- ━━ HOW IT WORKS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="section-wrap section-pad" id="how" style="background:linear-gradient(180deg,rgba(168,85,247,.04) 0%,transparent 100%)">
    <div class="container">
        <div class="text-center">
            <div class="section-tag tag-purple">Como funciona</div>
            <h2 class="section-h2">Comece a vender melhor em 3 passos</h2>
            <p class="section-sub">Configuração rápida, sem burocracia. Do cadastro ao primeiro dashboard em menos de 5 minutos.</p>
        </div>

        <div class="steps-grid">
            <div class="steps-line"></div>
            <div class="step-card">
                <div class="step-num">1</div>
                <div class="step-title">Crie sua conta grátis</div>
                <div class="step-desc">Cadastre-se com e-mail ou Google em segundos. Nenhum cartão de crédito necessário para começar.</div>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <div class="step-title">Conecte suas lojas</div>
                <div class="step-desc">Integre com o Mercado Livre e outras plataformas. Seus pedidos e produtos sincronizam automaticamente.</div>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <div class="step-title">Gerencie com clareza</div>
                <div class="step-desc">Visualize tudo no dashboard, tome decisões com dados reais e escale suas vendas com confiança total.</div>
            </div>
        </div>
    </div>
</section>

<!-- ━━ TESTIMONIALS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="section-wrap section-pad">
    <div class="container">
        <div class="text-center">
            <div class="section-tag tag-green">Depoimentos</div>
            <h2 class="section-h2">O que dizem nossos clientes</h2>
            <p class="section-sub">Vendedores reais que transformaram a gestão do seu negócio com o FlowManager.</p>
        </div>

        <div class="testi-grid">
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"Antes eu usava 3 planilhas e ficava perdido. Hoje tudo está centralizado no FlowManager — faturamento, pedidos, clientes. Economizo pelo menos 2 horas por dia."</div>
                <div class="testi-author">
                    <div class="testi-avatar" style="background:linear-gradient(135deg,#ec4899,#8b5cf6)">MR</div>
                    <div>
                        <div class="testi-name">Marcos R.</div>
                        <div class="testi-role">Vendedor Mercado Livre · SP</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"A integração com o ML é simplesmente incrível. Atualizo estoque aqui e sincroniza automaticamente. Não perco mais venda por falta de produto."</div>
                <div class="testi-author">
                    <div class="testi-avatar" style="background:linear-gradient(135deg,#8b5cf6,#06b6d4)">LA</div>
                    <div>
                        <div class="testi-name">Letícia A.</div>
                        <div class="testi-role">Loja de eletrônicos · MG</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <div class="testi-text">"Relatórios de lucratividade por produto mudaram como eu precfico. Descobri quais produtos realmente lucram e foquei neles. Margem subiu 30%."</div>
                <div class="testi-author">
                    <div class="testi-avatar" style="background:linear-gradient(135deg,#06b6d4,#10b981)">RF</div>
                    <div>
                        <div class="testi-name">Rafael F.</div>
                        <div class="testi-role">Multimarcas e-commerce · RJ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ━━ PRICING ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="section-wrap section-pad" id="pricing" style="background:linear-gradient(180deg,rgba(6,182,212,.04) 0%,transparent 100%)">
    <div class="container">
        <div class="text-center">
            <div class="section-tag tag-pink">Preços</div>
            <h2 class="section-h2">Planos simples e transparentes</h2>
            <p class="section-sub">Comece grátis, evolua quando precisar. Sem taxas escondidas, cancel a qualquer momento.</p>
        </div>

        <div class="pricing-toggle">
            <span class="toggle-label active" id="label-monthly">Mensal</span>
            <div class="toggle-pill" id="billing-toggle" onclick="toggleBilling()">
                <span class="knob"></span>
            </div>
            <span class="toggle-label" id="label-annual">Anual</span>
            <span class="annual-badge" id="annual-badge" style="opacity:.4">Economize 20%</span>
        </div>

        <div class="pricing-grid">
            <!-- FREE -->
            <div class="plan-card">
                <div class="plan-name">Grátis</div>
                <div class="plan-price">
                    <span class="plan-currency">R$</span>
                    <span class="plan-amount">0</span>
                    <span class="plan-period">/mês</span>
                </div>
                <div class="plan-desc">Para começar a organizar suas vendas sem gastar nada.</div>
                <a href="{{ route('register') }}" class="btn-plan btn-plan-outline">Criar conta grátis</a>
                <div class="plan-note">Não precisa de cartão de crédito</div>
                <div class="plan-divider"></div>
                <div class="plan-features">
                    <div class="plan-feat"><span class="check">✓</span> Até 50 produtos cadastrados</div>
                    <div class="plan-feat"><span class="check">✓</span> Dashboard básico</div>
                    <div class="plan-feat"><span class="check">✓</span> Gestão de pedidos (até 100/mês)</div>
                    <div class="plan-feat"><span class="check">✓</span> CRM básico (até 200 clientes)</div>
                    <div class="plan-feat"><span class="check">✓</span> Exportação CSV</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> Integração Mercado Livre</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> Relatórios avançados</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> IA + Gemini</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> Suporte prioritário</div>
                </div>
            </div>

            <!-- PRO (popular) -->
            <div class="plan-card popular">
                <div class="plan-popular-badge">Mais popular</div>
                <div class="plan-name" style="margin-top:.5rem">Pro</div>
                <div class="plan-price">
                    <span class="plan-currency">R$</span>
                    <span class="plan-amount" id="pro-price">49</span>
                    <span class="plan-period">/mês</span>
                </div>
                <div class="plan-desc">Para vendedores sérios que querem crescer com dados.</div>
                <a href="{{ route('register') }}" class="btn-plan btn-plan-primary">Começar 7 dias grátis</a>
                <div class="plan-note">Depois R$ <span id="pro-note">49</span>/mês · cancele quando quiser</div>
                <div class="plan-divider"></div>
                <div class="plan-features">
                    <div class="plan-feat"><span class="check">✓</span> Produtos ilimitados</div>
                    <div class="plan-feat"><span class="check">✓</span> Dashboard completo em tempo real</div>
                    <div class="plan-feat"><span class="check">✓</span> Pedidos ilimitados</div>
                    <div class="plan-feat"><span class="check">✓</span> CRM completo (clientes ilimitados)</div>
                    <div class="plan-feat"><span class="check">✓</span> <b>Integração Mercado Livre</b></div>
                    <div class="plan-feat"><span class="check">✓</span> Relatórios avançados + PDF/Excel</div>
                    <div class="plan-feat"><span class="check">✓</span> Controle financeiro completo</div>
                    <div class="plan-feat"><span class="check">✓</span> IA + Gemini (insights automáticos)</div>
                    <div class="plan-feat"><span class="check">✓</span> Suporte via chat (horário comercial)</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> Múltiplos usuários</div>
                    <div class="plan-feat dimmed"><span class="x">✕</span> API de integração própria</div>
                </div>
            </div>

            <!-- BUSINESS -->
            <div class="plan-card">
                <div class="plan-name">Business</div>
                <div class="plan-price">
                    <span class="plan-currency">R$</span>
                    <span class="plan-amount" id="biz-price">149</span>
                    <span class="plan-period">/mês</span>
                </div>
                <div class="plan-desc">Para operações maiores com múltiplos canais e equipes.</div>
                <a href="{{ route('register') }}" class="btn-plan btn-plan-outline-purple">Falar com vendas</a>
                <div class="plan-note">Ideal para equipes de 2 a 10 pessoas</div>
                <div class="plan-divider"></div>
                <div class="plan-features">
                    <div class="plan-feat"><span class="check">✓</span> Tudo do plano Pro</div>
                    <div class="plan-feat"><span class="check">✓</span> Múltiplos usuários (até 10)</div>
                    <div class="plan-feat"><span class="check">✓</span> Permissões por usuário</div>
                    <div class="plan-feat"><span class="check">✓</span> Múltiplas lojas ML na mesma conta</div>
                    <div class="plan-feat"><span class="check">✓</span> Integração Shopee (em breve)</div>
                    <div class="plan-feat"><span class="check">✓</span> API pública para integrações</div>
                    <div class="plan-feat"><span class="check">✓</span> Relatórios personalizados</div>
                    <div class="plan-feat"><span class="check">✓</span> Suporte prioritário 24/7</div>
                    <div class="plan-feat"><span class="check">✓</span> Onboarding dedicado</div>
                    <div class="plan-feat"><span class="check">✓</span> SLA garantido (99.95%)</div>
                </div>
            </div>
        </div>

        <div class="pricing-guarantee">
            <span class="shield">🛡️</span>
            Garantia de 30 dias. Se não gostar, devolvemos seu dinheiro sem perguntas.
        </div>
    </div>
</section>

<!-- ━━ FAQ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<section class="section-wrap section-pad" id="faq">
    <div class="container">
        <div class="text-center">
            <div class="section-tag tag-cyan">Dúvidas</div>
            <h2 class="section-h2">Perguntas frequentes</h2>
            <p class="section-sub">As respostas para as principais dúvidas sobre o FlowManager.</p>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Preciso de cartão de crédito para criar minha conta grátis?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Não. Você cria sua conta e usa o plano Grátis sem precisar cadastrar nenhum cartão. Só cobramos se você escolher evoluir para um plano pago.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Como funciona a integração com o Mercado Livre?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Você conecta sua conta do ML com apenas alguns cliques (autenticação OAuth oficial). Depois disso, seus anúncios, pedidos e estoque sincronizam automaticamente a cada poucos minutos. Nenhuma configuração técnica necessária.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Posso cancelar minha assinatura a qualquer momento?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Sim, sem burocracia. Você cancela com um clique nas configurações da sua conta. Não há multa ou período mínimo de permanência. Você continua com acesso até o fim do período já pago.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Meus dados ficam seguros?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Sim. Todos os dados são criptografados em trânsito (HTTPS/TLS 1.3) e em repouso. Fazemos backups automáticos diários. Seguimos a LGPD e não compartilhamos seus dados com terceiros.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    O FlowManager funciona no celular?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Sim, o FlowManager é totalmente responsivo e funciona muito bem no celular e tablet. Você consegue acompanhar pedidos, consultar clientes e ver o dashboard de qualquer dispositivo.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Quantos produtos posso cadastrar no plano Pro?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">No plano Pro e Business, o cadastro de produtos é ilimitado. No plano Grátis, o limite é de 50 produtos ativos.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    Posso migrar meus dados de outra plataforma?
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-a">Sim. O FlowManager aceita importação de produtos via planilha Excel/CSV. Para dados históricos de vendas, nossa equipe pode auxiliar na migração nos planos Pro e Business.</div>
            </div>
        </div>
    </div>
</section>

<!-- ━━ CTA FINAL ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<div class="cta-final">
    <div class="cta-final-glow"></div>
    <div class="cta-final-inner">
        <div class="section-tag tag-purple" style="margin: 0 auto 1.2rem">Pronto para começar?</div>
        <h2>Transforme a gestão<br><span>do seu negócio hoje</span></h2>
        <p>Junte-se a centenas de vendedores que já organizam pedidos, clientes e produtos com o FlowManager. Comece grátis em menos de 1 minuto.</p>
        <div class="cta-final-actions">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-cta-primary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    Acessar Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-cta-primary">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Criar conta grátis agora
                </a>
                <a href="#pricing" class="btn-cta-secondary">Ver planos e preços</a>
            @endauth
        </div>
        <div class="cta-final-note">✓ Sem cartão &nbsp;·&nbsp; ✓ Cancele quando quiser &nbsp;·&nbsp; ✓ Dados seguros (LGPD)</div>
    </div>
</div>

<!-- ━━ FOOTER ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<footer>
    <div class="footer-top">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="/apple-touch-icon.png" alt="FlowManager">
                <div class="footer-logo-text"><span>FlowManager</span></div>
            </div>
            <p class="footer-tagline">A plataforma completa para gestão de vendas online. Dashboard, pedidos, CRM, integrações e muito mais.</p>
            <div style="display:flex;gap:.5rem;margin-top:1rem;flex-wrap:wrap;">
                <span class="footer-badge">🔒 LGPD Compliant</span>
                <span class="footer-badge">🛡️ SSL/TLS</span>
                <span class="footer-badge">🇧🇷 Made in Brazil</span>
            </div>
        </div>
        <div class="footer-cols">
            <div class="footer-col">
                <div class="footer-col-title">Produto</div>
                <a href="#features">Funcionalidades</a>
                <a href="#integrations">Integrações</a>
                <a href="#how">Como funciona</a>
                <a href="#pricing">Preços</a>
            </div>
            <div class="footer-col">
                <div class="footer-col-title">Suporte</div>
                <a href="#faq">FAQ</a>
                <a href="#">Documentação</a>
                <a href="#">Status do sistema</a>
                <a href="#">Contato</a>
            </div>
            <div class="footer-col">
                <div class="footer-col-title">Conta</div>
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}">Criar conta</a>
                @auth <a href="{{ url('/dashboard') }}">Dashboard</a> @endauth
            </div>
            <div class="footer-col">
                <div class="footer-col-title">Legal</div>
                <a href="#">Termos de Uso</a>
                <a href="#">Política de Privacidade</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p class="footer-copy">© {{ date('Y') }} FlowManager. Todos os direitos reservados.</p>
        <p class="footer-made">Feito com ❤️ no Brasil</p>
    </div>
</footer>

<!-- ━━ SCRIPTS ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
<script>
    // Navbar scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    // Hamburger / mobile drawer
    const hb = document.getElementById('hamburger');
    const drawer = document.getElementById('drawer');
    hb.addEventListener('click', () => {
        hb.classList.toggle('open');
        drawer.classList.toggle('open');
    });
    function closeDrawer() {
        hb.classList.remove('open');
        drawer.classList.remove('open');
    }

    // FAQ accordion
    function toggleFaq(el) {
        const item = el.parentElement;
        const wasOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
        if (!wasOpen) item.classList.add('open');
    }

    // Billing toggle
    let isAnnual = false;
    const prices = { pro: { monthly: 49, annual: 39 }, biz: { monthly: 149, annual: 119 } };
    function toggleBilling() {
        isAnnual = !isAnnual;
        const toggle = document.getElementById('billing-toggle');
        toggle.classList.toggle('annual', isAnnual);
        document.getElementById('label-monthly').classList.toggle('active', !isAnnual);
        document.getElementById('label-annual').classList.toggle('active', isAnnual);
        document.getElementById('annual-badge').style.opacity = isAnnual ? '1' : '.4';
        document.getElementById('pro-price').textContent = isAnnual ? prices.pro.annual : prices.pro.monthly;
        document.getElementById('pro-note').textContent = isAnnual ? prices.pro.annual : prices.pro.monthly;
        document.getElementById('biz-price').textContent = isAnnual ? prices.biz.annual : prices.biz.monthly;
    }

    // Smooth close drawer on nav link click (mobile)
    document.querySelectorAll('.nav-drawer a').forEach(a => {
        a.addEventListener('click', closeDrawer);
    });
</script>
</body>
</html>
