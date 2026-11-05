{{-- Estilos da página inicial do FlowManager (Admin › Planos › Página inicial). "Atual" não carrega nada daqui. --}}
@if ($lpModel === 'editorial')
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
@elseif ($lpModel === 'elegante')
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,400;1,9..144,600&display=swap" rel="stylesheet">
@endif
<style>
    /* ───── Comum aos dois estilos claros ───── */
    body.lp-editorial, body.lp-elegante { background: var(--bg); color: var(--text); }
    body.lp-editorial .noise, body.lp-elegante .noise { opacity: .05; }
    body.lp-editorial nav#navbar, body.lp-elegante nav#navbar { background: color-mix(in srgb, var(--bg) 82%, transparent); border-bottom-color: var(--border); }
    body.lp-editorial nav#navbar.scrolled, body.lp-elegante nav#navbar.scrolled { background: color-mix(in srgb, var(--bg) 95%, transparent); }
    body.lp-editorial .nav-drawer, body.lp-elegante .nav-drawer { background: color-mix(in srgb, var(--bg) 97%, transparent); }
    body.lp-editorial .nav-drawer a, body.lp-elegante .nav-drawer a { border-bottom-color: var(--border); }
    body.lp-editorial .hamburger span, body.lp-elegante .hamburger span { background: var(--text); }
    body.lp-editorial :is(.nav-links a:hover, .nav-drawer a:hover, .btn-nav-ghost:hover, .trust-text strong, .toggle-label.active, .footer-col a:hover),
    body.lp-elegante :is(.nav-links a:hover, .nav-drawer a:hover, .btn-nav-ghost:hover, .trust-text strong, .toggle-label.active, .footer-col a:hover) { color: var(--text); }
    body.lp-editorial .btn-nav-ghost:hover, body.lp-elegante .btn-nav-ghost:hover { background: var(--card2); }
    body.lp-editorial .btn-cta-secondary, body.lp-elegante .btn-cta-secondary { color: var(--text); background: var(--card); }
    body.lp-editorial .btn-cta-secondary:hover, body.lp-elegante .btn-cta-secondary:hover { color: var(--text); background: var(--card2); border-color: var(--text); }
    body.lp-editorial .ticker-wrap, body.lp-elegante .ticker-wrap { background: var(--card); border-color: var(--border); }
    body.lp-editorial .ticker-item b, body.lp-elegante .ticker-item b { color: var(--text); }
    body.lp-editorial .ticker-sep, body.lp-elegante .ticker-sep { color: var(--faint); }
    body.lp-editorial .testi-text, body.lp-elegante .testi-text { color: var(--muted); }
    body.lp-editorial .plan-feat, body.lp-elegante .plan-feat { color: var(--text); }
    body.lp-editorial :is(.plan-feat .x, .plan-feat.dimmed), body.lp-elegante :is(.plan-feat .x, .plan-feat.dimmed) { color: var(--faint); }
    body.lp-editorial .btn-plan-outline, body.lp-elegante .btn-plan-outline { color: var(--text); border-color: var(--border2); background: transparent; }
    body.lp-editorial .btn-plan-outline:hover, body.lp-elegante .btn-plan-outline:hover { color: var(--text); background: var(--card2); }
    body.lp-editorial :is(.footer-col-title, .footer-copy, .footer-made), body.lp-elegante :is(.footer-col-title, .footer-copy, .footer-made) { color: var(--muted); }
    body.lp-editorial :is(.tag-cyan, .tag-purple, .tag-pink, .tag-green, .feat-tag), body.lp-elegante :is(.tag-cyan, .tag-purple, .tag-pink, .tag-green, .feat-tag) { color: var(--tag-ink); background: var(--tag-bg); border-color: transparent; }
    body.lp-editorial .faq-q:hover, body.lp-elegante .faq-q:hover { color: var(--acc-ink); }
    body.lp-editorial .faq-item.open .faq-icon, body.lp-elegante .faq-item.open .faq-icon { color: var(--acc-ink); }
    body.lp-editorial .faq-item.open, body.lp-elegante .faq-item.open { border-color: var(--text); }
    body.lp-editorial .hero-eyebrow, body.lp-elegante .hero-eyebrow { color: var(--text); background: var(--card2); border-color: var(--border2); }
    /* A telinha do sistema continua escura, como uma tela de verdade */
    body.lp-editorial .preview-browser, body.lp-elegante .preview-browser { box-shadow: 0 30px 80px rgba(36, 30, 20, .28); }

    /* ───── Editorial: creme, tinta verde-escura e verde-limão, títulos enormes ───── */
    body.lp-editorial {
        --bg: #f1ece3; --bg2: #e9e2d5; --bg3: #e3dbcc;
        --card: #faf7f1; --card2: #fffdf8;
        --border: rgba(36, 48, 42, .14); --border2: rgba(36, 48, 42, .28);
        --text: #24302a; --muted: rgba(36, 48, 42, .68); --faint: rgba(36, 48, 42, .4);
        --grad: linear-gradient(135deg, #24302a, #24302a); --grad-r: var(--grad);
        --cyan2: #24302a; --acc-ink: #3f5a12;
        --tag-bg: #c6ef5b; --tag-ink: #24302a;
        --shadow: 0 18px 50px rgba(36, 48, 42, .14);
    }
    body.lp-editorial .bg-glow { background: radial-gradient(ellipse 70% 50% at 85% 0%, rgba(198, 239, 91, .35), transparent 60%); }
    body.lp-editorial :is(.hero-title, .section-h2, .cta-final h2) {
        font-family: 'Anton', 'Oswald', 'Arial Narrow', Impact, sans-serif; font-stretch: condensed; font-weight: 400; text-transform: uppercase; letter-spacing: .005em; line-height: 1.06; overflow-wrap: anywhere;
    }
    body.lp-editorial .hero-title { font-size: clamp(2.6rem, 8vw, 6.6rem); }
    body.lp-editorial .section-h2 { font-size: clamp(2.1rem, 5.6vw, 4.4rem); }
    body.lp-editorial .cta-final h2 { font-size: clamp(2.2rem, 6vw, 4.6rem); }
    body.lp-editorial :is(.grad-text, .cta-final h2 span, .section-h2 span) {
        background: none; -webkit-text-fill-color: currentColor; color: var(--text);
        box-shadow: inset 0 -.24em 0 #c6ef5b; padding: 0 .08em;
    }
    body.lp-editorial :is(.nav-logo-text, .footer-logo-text span) { background: none; -webkit-text-fill-color: currentColor; color: var(--text); }
    body.lp-editorial :is(.btn-nav-primary, .btn-cta-primary, .btn-plan-primary) { background: #24302a; color: #c6ef5b; box-shadow: none; border-radius: 999px; }
    body.lp-editorial :is(.btn-nav-primary, .btn-cta-primary, .btn-plan-primary):hover { background: #c6ef5b; color: #24302a; box-shadow: 0 0 0 2px #24302a; }
    body.lp-editorial :is(.btn-cta-secondary, .btn-plan-outline, .btn-plan-outline-purple) { border-radius: 999px; }
    body.lp-editorial :is(.feat-card, .step-card, .testi-card, .plan-card, .faq-item, .stat-box) { box-shadow: none; }
    body.lp-editorial :is(.feat-card, .testi-card, .plan-card) { border-radius: 26px; }
    body.lp-editorial .plan-card.popular { background: #24302a; color: #f1ece3; border-color: #24302a; --muted: rgba(241, 236, 227, .7); --faint: rgba(241, 236, 227, .45); --cyan2: #c6ef5b; }
    body.lp-editorial .plan-card.popular :is(.plan-feat, .plan-amount) { color: #f1ece3; }
    body.lp-editorial .plan-card.popular .btn-plan-primary { background: #c6ef5b; color: #24302a; }
    body.lp-editorial .plan-popular-badge { background: #c6ef5b; color: #24302a; }
    body.lp-editorial .plan-amount { font-family: 'Anton', sans-serif; font-weight: 400; letter-spacing: 0; }
    body.lp-elegante .plan-popular-badge { background: #3b2f33; color: #f6efe9; }
    body.lp-elegante .plan-card.popular { border-color: rgba(185, 130, 95, .55); }
    body.lp-elegante .plan-feat .check { color: #b9825f; }
    body.lp-editorial .plan-feat .check { color: #3f5a12; }
    body.lp-editorial .plan-card.popular .plan-feat .check { color: #c6ef5b; }
    body.lp-editorial .stat-num { font-family: 'Anton', sans-serif; font-weight: 400; background: none; -webkit-text-fill-color: currentColor; color: var(--text); }
    body.lp-editorial .testi-stars { color: #3f5a12; }
    body.lp-editorial .cta-final { background: #c6ef5b; }
    body.lp-editorial .cta-final-glow { display: none; }
    body.lp-editorial .cta-final p { color: rgba(36, 48, 42, .78); }
    body.lp-editorial .cta-final .btn-cta-secondary { background: transparent; border-color: #24302a; }

    /* ───── Elegante: tons de pele, letra serifada itálica e cards de vidro ───── */
    body.lp-elegante {
        --bg: #f6efe9; --bg2: #efe4dc; --bg3: #eadbd0;
        --card: rgba(255, 255, 255, .55); --card2: rgba(255, 255, 255, .78);
        --border: rgba(59, 47, 51, .12); --border2: rgba(59, 47, 51, .24);
        --text: #3b2f33; --muted: rgba(59, 47, 51, .66); --faint: rgba(59, 47, 51, .4);
        --grad: linear-gradient(135deg, #b9825f 0%, #8e5c6b 100%); --grad-r: var(--grad);
        --cyan2: #8e5c6b; --acc-ink: #8e5c6b;
        --tag-bg: rgba(185, 130, 95, .16); --tag-ink: #8a5a3c;
        --shadow: 0 24px 60px rgba(59, 47, 51, .12);
    }
    body.lp-elegante .bg-glow {
        background:
            radial-gradient(ellipse 55% 45% at 12% 8%, rgba(232, 187, 160, .55), transparent 62%),
            radial-gradient(ellipse 50% 40% at 90% 18%, rgba(205, 168, 190, .45), transparent 60%),
            radial-gradient(ellipse 60% 45% at 50% 100%, rgba(238, 210, 180, .5), transparent 60%);
    }
    body.lp-elegante :is(.hero-title, .section-h2, .cta-final h2, .feat-title, .step-title, .plan-name) {
        font-family: 'Fraunces', Georgia, serif; font-weight: 400; letter-spacing: -.02em;
    }
    body.lp-elegante .hero-title { font-size: clamp(2.6rem, 7vw, 5.6rem); line-height: 1.02; }
    body.lp-elegante :is(.grad-text, .cta-final h2 span) { font-style: italic; }
    body.lp-elegante :is(.feat-card, .step-card, .testi-card, .plan-card, .faq-item, .stats-grid) {
        background: rgba(255, 255, 255, .5); backdrop-filter: blur(16px) saturate(1.3); -webkit-backdrop-filter: blur(16px) saturate(1.3);
        border-color: rgba(255, 255, 255, .8); box-shadow: 0 10px 40px rgba(59, 47, 51, .08), inset 0 1px 0 rgba(255, 255, 255, .9);
    }
    body.lp-elegante :is(.btn-nav-primary, .btn-cta-primary, .btn-plan-primary) { background: #3b2f33; color: #f6efe9; box-shadow: 0 10px 30px rgba(59, 47, 51, .22); border-radius: 999px; }
    body.lp-elegante :is(.btn-nav-primary, .btn-cta-primary, .btn-plan-primary):hover { background: #b9825f; color: #fff; }
    body.lp-elegante :is(.btn-cta-secondary, .btn-plan-outline, .btn-plan-outline-purple) { border-radius: 999px; }
    body.lp-elegante .testi-text { font-family: 'Fraunces', Georgia, serif; font-style: italic; font-size: 1rem; }
    body.lp-elegante .testi-stars { color: #b9825f; }
    body.lp-elegante .cta-final-glow { background: radial-gradient(ellipse 60% 60% at 50% 50%, rgba(185, 130, 95, .22), transparent 70%); }
</style>
