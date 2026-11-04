{{-- Estilos dos modelos da página inicial (Vitrine › Página inicial › Modelo). Só entram quando um modelo está ligado,
     e só na página inicial: o resto do site continua com as cores e o estilo da loja. --}}
@if($hModel === 'editorial')
<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
@else
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;1,9..144,300;1,9..144,500&display=swap" rel="stylesheet">
@endif
<style>
    /* Paleta e letra de cada modelo */
    body {
        --hm-bg: #f1ece3; --hm-ink: #24302a; --hm-soft: #e6dfd2; --hm-card: #fbf8f2; --hm-acc: #c6ef5b; --hm-mute: #6b6f66;
        --hm-font: 'Anton', 'Oswald', Impact, sans-serif;
        --ml-bg: var(--hm-bg); --ml-card: var(--hm-card); --ml-text: var(--hm-ink); --ml-muted: var(--hm-mute); --ml-line: #ddd5c6;
        --ml-header: var(--hm-ink);
    }
    @if($hModel === 'botanico')
    body {
        --hm-bg: #f6efe9; --hm-ink: #3b2f33; --hm-soft: #ecdfd5; --hm-card: #fffaf6; --hm-acc: #b9825f; --hm-mute: #7a6b6c;
        --hm-font: 'Fraunces', 'Playfair Display', Georgia, serif;
        --ml-line: #e7d9cf;
    }
    @endif
    body { background: var(--hm-bg); }

    .hm-sec { margin: 0 0 28px; }
    .hm-block { padding: 0 2px; }
    .hm-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
    .hm-h { margin: 0; font-family: var(--hm-font); color: var(--hm-ink); font-weight: 400; line-height: .95; }
    .hm-more { color: var(--hm-ink); font-weight: 700; font-size: 13px; text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; }
    .hm-more:hover { text-decoration: underline; }
    .hm-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; text-decoration: none; font: inherit; font-weight: 700; font-size: 14px;
        padding: 13px 22px; border-radius: 999px; background: var(--hm-acc); color: var(--hm-ink); transition: transform .2s ease, box-shadow .2s ease; }
    .hm-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 22px -10px rgba(0,0,0,.4); }
    .hm-btn.light { background: #fff; }
    .hm-stars { color: #f5a524; letter-spacing: 1px; }
    .hm-all-head { margin-top: 10px; }

    /* Cards dos modelos */
    .hm-row { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(158px, 46%); gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory;
        padding: 2px 2px 10px; scrollbar-width: none; }
    .hm-row::-webkit-scrollbar { display: none; }
    .hm-card { position: relative; scroll-snap-align: start; display: flex; flex-direction: column; min-width: 0; }
    .hm-card-img { position: relative; display: block; aspect-ratio: 4 / 5; overflow: hidden; background: var(--hm-soft); }
    .hm-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
    .hm-card:hover .hm-card-img img { transform: scale(1.05); }
    .hm-flag { position: absolute; left: 8px; top: 8px; background: var(--hm-ink); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 999px; }
    .hm-flag.new { background: var(--hm-acc); color: var(--hm-ink); }
    .hm-fav { position: absolute; right: 8px; top: 8px; width: 34px; height: 34px; border-radius: 999px; border: 0; background: rgba(255,255,255,.88); color: var(--hm-ink); cursor: pointer; }
    .hm-fav.on { color: #e11d48; }
    .hm-card-body { padding: 10px 2px 0; display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .hm-card-cat { font-size: 11px; color: var(--hm-mute); text-transform: uppercase; letter-spacing: .06em; }
    .hm-card-name { color: var(--hm-ink); text-decoration: none; font-weight: 700; font-size: 13px; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hm-card-foot { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: 6px; padding-top: 4px; }
    .hm-card-price { font-weight: 800; color: var(--hm-ink); font-size: 14px; display: flex; flex-direction: column; line-height: 1.1; white-space: nowrap; min-width: 0; }
    .hm-card-price s { font-weight: 500; color: var(--hm-mute); font-size: 11px; }
    .hm-add { flex-shrink: 0; border: 0; cursor: pointer; width: 36px; height: 36px; border-radius: 999px; background: var(--hm-ink); color: #fff; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font: inherit; font-size: 12px; font-weight: 700; transition: background .2s ease; }
    .hm-add span { display: none; }
    .hm-add.in { background: #16a34a; }

    /* Categorias */
    .hm-cats { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(118px, 32%); gap: 12px; overflow-x: auto; scrollbar-width: none; padding-bottom: 6px; }
    .hm-cats::-webkit-scrollbar { display: none; }
    .hm-cat { text-decoration: none; color: var(--hm-ink); display: flex; flex-direction: column; gap: 4px; }
    .hm-cat-img { display: grid; place-items: center; overflow: hidden; background: var(--hm-soft); color: var(--hm-mute); font-size: 26px; }
    .hm-cat-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
    .hm-cat:hover .hm-cat-img img { transform: scale(1.06); }
    .hm-cat b { font-size: 13px; }
    .hm-cat small { color: var(--hm-mute); font-size: 11px; }

    .hm-quotes { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(250px, 84%); gap: 12px; overflow-x: auto; scrollbar-width: none; padding-bottom: 6px; }
    .hm-quotes::-webkit-scrollbar { display: none; }
    .hm-quote { margin: 0; background: var(--hm-card); border-radius: 18px; padding: 18px; display: flex; flex-direction: column; gap: 10px; }
    .hm-quote blockquote { margin: 0; color: var(--hm-ink); font-size: 14px; line-height: 1.5; }
    .hm-quote figcaption { color: var(--hm-mute); font-size: 12px; font-weight: 700; margin-top: auto; }
    .hm-rate { font-size: 13px; color: var(--hm-mute); white-space: nowrap; }
    .hm-rate b { color: var(--hm-ink); font-size: 18px; }

    /* ───────────── Editorial ───────────── */
    @if($hModel === 'editorial')
    .hm-h { text-transform: uppercase; font-size: clamp(30px, 8vw, 64px); letter-spacing: .005em; }
    .hm-h em { font-style: normal; display: block; color: var(--hm-ink); -webkit-text-stroke: 0; }
    .hm-hero { display: grid; grid-template-columns: 1fr; border-radius: 22px; overflow: hidden; background: var(--hm-ink); color: #f4efe6; margin-top: 12px; }
    .hm-hero-text { padding: 26px 20px 22px; display: flex; flex-direction: column; gap: 14px; }
    .hm-kicker { margin: 0; text-transform: uppercase; letter-spacing: .14em; font-size: 11px; font-weight: 700; color: var(--hm-acc); }
    .hm-hero-title { margin: 0; font-family: var(--hm-font); font-weight: 400; text-transform: uppercase; font-size: clamp(44px, 13vw, 132px); line-height: .9; letter-spacing: .005em; overflow-wrap: anywhere; }
    .hm-hero-cta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .hm-hero-note { font-size: 12px; line-height: 1.3; color: rgba(244,239,230,.75); }
    .hm-hero-note b { color: #fff; font-size: 16px; }
    .hm-hero-art { position: relative; background: #e9e2d4; min-height: 320px; }
    @media (min-width: 640px) and (max-width: 1023px) { .hm-hero-art { min-height: 420px; } }
    .hm-hero-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hm-spin { position: absolute; inset: 0; }
    .hm-spin-item { position: absolute; inset: 0; display: grid; place-items: center; padding: 24px 24px 54px; opacity: 0;
        animation: hm-spin calc(var(--n) * 3.6s) infinite; animation-delay: calc(var(--i) * 3.6s); text-decoration: none; }
    .hm-spin[style*="--n: 1"] .hm-spin-item { opacity: 1; animation: none; }
    .hm-spin-item img { position: absolute; left: 24px; top: 24px; width: calc(100% - 48px); height: calc(100% - 78px); object-fit: contain; mix-blend-mode: multiply; filter: drop-shadow(0 18px 18px rgba(0,0,0,.18)); }
    .hm-spin-tag { position: absolute; left: 14px; bottom: 14px; right: 14px; display: flex; justify-content: space-between; gap: 10px; color: var(--hm-ink); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
    .hm-spin-tag b { font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hm-strip { background: var(--hm-acc); color: var(--hm-ink); overflow: hidden; margin: 0 -12px 28px; padding: 12px 0; transform: rotate(-1.2deg); }
    .hm-strip-run { display: flex; gap: 22px; width: max-content; animation: hm-run 26s linear infinite; font-family: var(--hm-font); text-transform: uppercase; font-size: 20px; white-space: nowrap; align-items: center; }
    .hm-strip-run i { font-style: normal; }
    @keyframes hm-run { to { transform: translateX(-50%); } }
    .hm-card-name { text-transform: uppercase; letter-spacing: .02em; }
    .hm-card-img { border-radius: 14px; background: #e7e0d1; }
    .hm-cat-img { aspect-ratio: 1; border-radius: 999px; }
    .hm-cat { align-items: center; text-align: center; }
    .hm-cat b { text-transform: uppercase; }
    .hm-mosaic { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .hm-tile { position: relative; overflow: hidden; border-radius: 16px; background: #e7e0d1; aspect-ratio: 1; text-decoration: none; }
    .hm-tile.big { grid-column: span 2; aspect-ratio: 16 / 10; }
    .hm-mosaic > :nth-child(n+6) { display: none; }
    .hm-tile img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .hm-tile:hover img { transform: scale(1.06); }
    .hm-tile-info { position: absolute; left: 0; right: 0; bottom: 0; padding: 26px 12px 10px; background: linear-gradient(transparent, rgba(20,26,22,.78)); color: #fff; display: flex; flex-direction: column; }
    .hm-tile-info b { font-size: 12px; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hm-tile-info small { font-weight: 700; }
    .hm-feature { position: relative; border-radius: 22px; overflow: hidden; min-height: 340px; display: flex; align-items: flex-end; }
    .hm-feature img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hm-feature::after { content: ''; position: absolute; inset: 0; background: linear-gradient(transparent 20%, rgba(20,26,22,.82)); }
    .hm-feature-text { position: relative; z-index: 1; padding: 22px; display: flex; flex-direction: column; gap: 14px; align-items: flex-start; }
    .hm-feature h2 { margin: 0; color: #fff; font-family: var(--hm-font); font-weight: 400; text-transform: uppercase; font-size: clamp(26px, 6vw, 56px); line-height: .98; max-width: 20ch; }
    .hm-quote { border: 1.5px solid var(--hm-ink); background: transparent; border-radius: 4px; }
    @endif

    /* ───────────── Elegante ───────────── */
    @if($hModel === 'botanico')
    .hm-h { font-weight: 300; font-size: clamp(28px, 7vw, 54px); letter-spacing: -.01em; }
    .hm-h b { font-family: system-ui; font-weight: 400; font-size: .45em; vertical-align: middle; color: var(--hm-acc); margin: 0 .15em; }
    .hm-h em { font-style: italic; }
    .hm-hero { position: relative; margin: 12px 0 30px; border-radius: 24px; overflow: hidden; min-height: 460px; display: flex; align-items: flex-end; background: var(--hm-ink); }
    .hm-hero-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; animation: hm-kb 18s ease-in-out infinite alternate; }
    @keyframes hm-kb { to { transform: scale(1.08); } }
    .hm-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(30,22,25,.05) 30%, rgba(30,22,25,.72)); }
    .hm-hero-text { position: relative; z-index: 1; padding: 26px 20px; display: flex; flex-direction: column; gap: 14px; align-items: flex-start; color: #fff; }
    .hm-hero-title { margin: 0; font-family: var(--hm-font); font-weight: 300; font-size: clamp(40px, 11vw, 110px); line-height: .98; letter-spacing: -.02em; max-width: 14ch; }
    .hm-hero-title em { font-style: italic; font-weight: 300; }
    .hm-kicker { margin: 0; font-size: 15px; color: rgba(255,255,255,.85); max-width: 46ch; }
    .hm-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.16); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,.3); }
    .hm-btn { background: #fff; color: var(--hm-ink); }
    .hm-sec:not(.hm-hero) .hm-btn, .hm-spot .hm-btn { background: var(--hm-ink); color: #fff; }
    .hm-card { background: var(--hm-card); border-radius: 20px; padding: 8px 8px 12px; box-shadow: 0 1px 0 var(--ml-line); }
    .hm-card-img { border-radius: 14px; aspect-ratio: 3 / 4; }
    .hm-card-body { padding: 10px 4px 0; }
    .hm-card-name { font-family: var(--hm-font); font-weight: 500; font-size: 15px; }
    .hm-add { width: auto; height: 32px; padding: 0 12px; }
    .hm-add span { display: inline; }
    @media (max-width: 479px) { .hm-add { width: 32px; padding: 0; } .hm-add span { display: none; } .hm-card-price { font-size: 13px; } }
    .hm-about { text-align: center; padding: 26px 8px; max-width: 760px; margin-left: auto; margin-right: auto; }
    .hm-about-kicker { margin: 0 0 10px; text-transform: uppercase; letter-spacing: .16em; font-size: 11px; font-weight: 700; color: var(--hm-acc); }
    .hm-about-text { margin: 0 0 16px; font-family: var(--hm-font); font-weight: 300; font-size: clamp(22px, 5.4vw, 38px); line-height: 1.25; color: var(--hm-ink); }
    .hm-cats { grid-auto-columns: minmax(150px, 44%); }
    .hm-cat { position: relative; }
    .hm-cat-img { aspect-ratio: 3 / 4; border-radius: 18px; }
    .hm-cat-txt { position: absolute; left: 8px; right: 8px; bottom: 8px; background: rgba(255,250,246,.92); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); border-radius: 12px; padding: 8px 10px; display: flex; flex-direction: column; }
    .hm-cat b { font-family: var(--hm-font); font-weight: 500; font-size: 15px; }
    .hm-spot { display: grid; grid-template-columns: 1fr; gap: 16px; background: var(--hm-soft); border-radius: 24px; padding: 12px; }
    .hm-spot-img { display: block; border-radius: 18px; overflow: hidden; aspect-ratio: 4 / 3; background: var(--hm-card); }
    .hm-spot-img img { width: 100%; height: 100%; object-fit: cover; }
    .hm-spot-text { padding: 4px 10px 12px; display: flex; flex-direction: column; justify-content: center; }
    .hm-spot h2 { margin: 0 0 8px; font-family: var(--hm-font); font-weight: 300; font-size: clamp(26px, 6vw, 46px); line-height: 1.05; color: var(--hm-ink); }
    .hm-spot-price { margin: 0 0 16px; font-size: 20px; font-weight: 700; color: var(--hm-ink); }
    .hm-spot-price s { font-size: 14px; color: var(--hm-mute); font-weight: 500; }
    .hm-spot-btns { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .hm-rating { display: grid; gap: 16px; }
    .hm-rating-big { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 10px; color: var(--hm-ink); }
    .hm-rating-big b { font-family: var(--hm-font); font-weight: 300; font-size: 64px; line-height: 1; }
    .hm-rating-big > span:first-of-type { font-size: 20px; color: var(--hm-mute); }
    .hm-rating-big small { width: 100%; color: var(--hm-mute); }
    .hm-quote blockquote { font-family: var(--hm-font); font-weight: 300; font-size: 16px; }
    @endif

    /* A grade "Todos os produtos" ganha o mesmo tom */
    .ml-card { border-radius: {{ $hModel === 'botanico' ? '20px' : '14px' }}; box-shadow: none; }
    .ml-toolbar { background: transparent; }

    /* iPad e telas maiores */
    @media (min-width: 640px) {
        .hm-row { grid-auto-columns: minmax(180px, 30%); }
        .hm-cats { grid-auto-columns: minmax(130px, 22%); }
        .hm-quotes { grid-auto-columns: minmax(280px, 46%); }
        @if($hModel === 'editorial') .hm-mosaic { grid-template-columns: repeat(3, 1fr); } .hm-mosaic > :nth-child(n+6) { display: block; } .hm-tile.big { grid-column: span 2; grid-row: span 2; aspect-ratio: auto; } @endif
        @if($hModel === 'botanico') .hm-cats { grid-auto-columns: minmax(170px, 30%); } .hm-spot { grid-template-columns: 1.1fr 1fr; padding: 16px; } @endif
    }
    @media (min-width: 1024px) {
        .hm-sec { margin-bottom: 44px; }
        .hm-row { grid-auto-flow: row; grid-template-columns: repeat(4, 1fr); overflow: visible; }
        .hm-row > :nth-child(n+9) { display: none; }
        .hm-cats { grid-auto-flow: row; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); overflow: visible; }
        .hm-quotes { grid-auto-flow: row; grid-template-columns: repeat(3, 1fr); overflow: visible; }
        .hm-quotes > :nth-child(n+4) { display: none; }
        @if($hModel === 'editorial')
            .hm-hero { grid-template-columns: 1.15fr 1fr; min-height: 560px; }
            .hm-hero-text { padding: 48px 44px; justify-content: flex-end; }
            .hm-hero-art { min-height: 0; }
            .hm-mosaic { grid-template-columns: repeat(4, 1fr); }
            .hm-mosaic > :nth-child(n+6) { display: none; }
            .hm-feature { min-height: 520px; }
            .hm-feature-text { padding: 44px; }
        @endif
        @if($hModel === 'botanico')
            .hm-hero { min-height: 620px; }
            .hm-hero-text { padding: 56px; }
            .hm-row { grid-template-columns: repeat(3, 1fr); }
            .hm-row > :nth-child(n+7) { display: none; }
            .hm-cats { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
            .hm-rating { grid-template-columns: 260px 1fr; align-items: center; }
            .hm-quotes { grid-template-columns: repeat(2, 1fr); }
            .hm-quotes > :nth-child(n+3) { display: none; }
            .hm-about { padding: 50px 8px; }
        @endif
    }
    @if($hModel === 'botanico')
    @media (min-width: 1280px) {
        .hm-row { grid-template-columns: repeat(4, 1fr); }
        .hm-row > :nth-child(n+5) { display: none; }
    }
    @endif
    @media (min-width: 1700px) {
        .hm-row { grid-template-columns: repeat(6, 1fr); }
        .hm-row > :nth-child(-n+6) { display: flex; }
        .hm-row > :nth-child(n+7) { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .hm-spin-item { animation: none; }
        .hm-spin-item:first-child { opacity: 1; }
        .hm-strip-run, .hm-hero-photo { animation: none; }
    }
</style>
