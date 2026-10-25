{{-- Estilos do catálogo público (cards, ficha, carrinho). Usado no catálogo e na página do produto. --}}
<style>
    .ml-section { margin-top: 12px; }
    .ml-box { background: var(--ml-card); border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); }

    /* Faixa de boas-vindas */
    .ml-hero { margin-top: 12px; border-radius: var(--ml-radius); overflow: hidden; position: relative; padding: 18px 18px 20px; color: #fff;
        background: var(--ml-hero); }
    .ml-hero::after { content: ''; position: absolute; right: -40px; bottom: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.12); }
    .ml-hero small { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; background: #fff; color: var(--ml-blue-dark); padding: 4px 9px; border-radius: 999px; }
    .ml-hero h1 { margin: 10px 0 4px; font-size: 22px; line-height: 1.15; font-weight: 900; max-width: 520px; }
    .ml-hero p { margin: 0; font-size: 13px; opacity: .9; max-width: 460px; }
    .ml-hero-stats { display: flex; gap: 8px; margin-top: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
    .ml-hero-stats span { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.25); padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }

    /* Atalhos de categoria */
    .ml-cats { display: flex; gap: 10px; overflow-x: auto; padding: 14px 12px 12px; scrollbar-width: none; scroll-snap-type: x proximity; }
    .ml-cats::-webkit-scrollbar { display: none; }
    .ml-cat { flex: 0 0 auto; width: 72px; display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center; scroll-snap-align: start; }
    .ml-cat-ico { width: 56px; height: 56px; border-radius: 50%; background: var(--ml-chip); box-shadow: var(--ml-shadow); display: flex; align-items: center; justify-content: center; font-size: 22px; color: var(--ml-blue); border: 2px solid transparent; transition: transform .15s; }
    .ml-cat > span:last-child { font-size: 11px; line-height: 1.2; color: var(--ml-page-text); font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ml-cat.on .ml-cat-ico { border-color: var(--ml-blue); background: var(--ml-blue-soft); }
    .ml-cat.on > span:last-child { color: var(--ml-blue); }
    .ml-cat.hot .ml-cat-ico { color: #fff; background: linear-gradient(135deg, var(--ml-orange), var(--ml-red)); }
    .ml-cat:active .ml-cat-ico { transform: scale(.94); }

    /* Ofertas do dia */
    .ml-offers { padding: 14px 0 6px; }
    .ml-sec-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; padding: 0 14px 10px; }
    .ml-sec-head h2 { margin: 0; font-size: 18px; font-weight: 800; color: var(--ml-text); display: flex; align-items: center; gap: 8px; }
    .ml-sec-head h2 i { color: var(--ml-orange); }
    .ml-sec-head a { color: var(--ml-blue); font-size: 13px; font-weight: 600; white-space: nowrap; }
    .ml-rail { display: flex; gap: 10px; overflow-x: auto; padding: 0 14px 12px; scroll-padding: 0 14px; scroll-snap-type: x mandatory; scrollbar-width: none; }
    .ml-rail::-webkit-scrollbar { display: none; }
    .ml-seen { margin-top: 12px; }
    .ml-seen .ml-sec-head h2 i { color: var(--ml-blue); }
    .ml-seen-clear { border: 0; background: none; color: var(--ml-blue); font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px; }
    .ml-rail .ml-card { flex: 0 0 152px; scroll-snap-align: start; border: 1px solid var(--ml-line); box-shadow: none; }

    /* Barra de filtros */
    .ml-toolbar { position: sticky; top: calc(59px + env(safe-area-inset-top)); z-index: 40; background: var(--ml-bg); margin: 0 -12px; padding: 10px 12px 8px; display: flex; align-items: center; gap: 8px; overflow-x: auto; scrollbar-width: none; }
    .ml-toolbar::-webkit-scrollbar { display: none; }
    .ml-count-txt { font-size: 13px; color: var(--ml-page-text); white-space: nowrap; margin-right: auto; padding-left: 2px; }
    .ml-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 999px; background: var(--ml-chip); border: 1px solid var(--ml-line); font-size: 13px; font-weight: 600; color: var(--ml-text); white-space: nowrap; cursor: pointer; }
    .ml-chip.on { background: var(--ml-blue-soft); border-color: var(--ml-blue); color: var(--ml-blue); }
    .ml-chip.hot.on { background: #fff1ea; border-color: var(--ml-orange); color: #d9541a; }
    .ml-chip select { border: 0; background: transparent; color: inherit; font-weight: 600; font-size: 13px; outline: none; padding-right: 2px; cursor: pointer; appearance: none; -webkit-appearance: none; }
    .ml-search-note { font-size: 14px; padding: 4px 2px 0; color: var(--ml-page-text); }
    .ml-search-note b { font-weight: 800; }
    .ml-search-note a { color: var(--ml-blue); font-weight: 600; margin-left: 6px; font-size: 13px; }

    /* Grade de produtos */
    .ml-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    @media (min-width: 640px) { .ml-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; } }
    @media (min-width: 960px) { .ml-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; } }
    @media (min-width: 1180px) { .ml-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

    .ml-card { position: relative; background: var(--ml-card); border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); overflow: hidden; display: flex; flex-direction: column; cursor: pointer; transition: box-shadow .18s, transform .18s; -webkit-tap-highlight-color: transparent; }
    @media (hover: hover) { .ml-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.14); transform: translateY(-2px); } }
    .ml-card-img { position: relative; aspect-ratio: 1 / 1; background: #fff; border-bottom: 1px solid var(--ml-line); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .ml-card-img img { width: 100%; height: 100%; object-fit: contain; padding: 8px; transition: transform .3s; }
    @media (hover: hover) { .ml-card:hover .ml-card-img img { transform: scale(1.05); } }
    .ml-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 34px; color: #d6d6d6; background: linear-gradient(135deg, #fafafa, #f0f0f0); }
    .ml-off-flag { position: absolute; top: 8px; left: 8px; background: var(--ml-green); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 7px; border-radius: 4px; }
    .ml-add { position: absolute; right: 8px; bottom: 8px; width: 36px; height: 36px; border-radius: 50%; border: 0; background: #fff; color: var(--ml-blue); box-shadow: 0 2px 8px rgba(0,0,0,.18); display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; transition: transform .15s, background .15s; }
    .ml-add:active { transform: scale(.9); }
    .ml-add { z-index: 2; }
    .ml-card-link::after { content: ''; position: absolute; inset: 0; z-index: 1; }
    .ml-card-link:focus-visible { outline: none; }
    .ml-card:has(.ml-card-link:focus-visible) { outline: 3px solid var(--ml-blue); outline-offset: 2px; }
    .ml-fav { position: absolute; right: 8px; top: 8px; z-index: 2; width: 34px; height: 34px; border-radius: 50%; border: 0; background: rgba(255,255,255,.94); color: #8c8c8c; box-shadow: 0 1px 5px rgba(0,0,0,.16); display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: transform .15s, color .15s; }
    .ml-fav:active { transform: scale(.88); }
    .ml-fav.on { color: #ec4899; }
    .ml-fav.on i { animation: mlPop .3s ease; }
    @keyframes mlPop { 0% { transform: scale(.6); } 60% { transform: scale(1.25); } 100% { transform: scale(1); } }
    .ml-rail .ml-fav { width: 30px; height: 30px; font-size: 14px; right: 6px; top: 6px; }
    .ml-sheet-fav { width: 40px; height: 40px; border: 0; background: transparent; border-radius: 50%; font-size: 20px; color: #8c8c8c; cursor: pointer; }
    .ml-sheet-fav.on { color: #ec4899; }
    .ml-sheet-acts { display: flex; align-items: center; gap: 2px; }
    .ml-chip.fav i { color: #ec4899; }
    .ml-add.in { background: var(--ml-blue); color: #fff; }
    .ml-imgs { position: absolute; left: 8px; bottom: 8px; background: rgba(0,0,0,.55); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 7px; border-radius: 999px; display: inline-flex; gap: 4px; align-items: center; }
    .ml-card-body { padding: 10px 12px 14px; display: flex; flex-direction: column; gap: 3px; flex: 1; }
    .ml-deal { align-self: flex-start; background: var(--ml-blue); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .02em; padding: 3px 6px; border-radius: 3px; text-transform: uppercase; margin-bottom: 2px; }
    .ml-title { margin: 0; font-size: 13px; font-weight: 400; color: var(--ml-text); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
    .rv-stars { --v: 0; display: inline-block; letter-spacing: 1px; line-height: 1; background: linear-gradient(90deg, #f5b301 calc(var(--v) / 5 * 100%), #d4d4d8 0); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .ml-rate { display: flex; align-items: center; gap: 3px; font-size: 12px; color: var(--ml-muted); margin-top: 2px; }
    .ml-rate .rv-stars { font-size: 12px; }
    .ml-old { font-size: 12px; color: var(--ml-muted); text-decoration: line-through; margin-top: 4px; }
    .ml-price-row { display: flex; align-items: flex-start; gap: 6px; flex-wrap: wrap; }
    .ml-price { font-size: 22px; font-weight: 400; color: var(--ml-text); line-height: 1.1; letter-spacing: -.01em; white-space: nowrap; }
    .ml-price sup { font-size: 11px; position: relative; top: -.35em; margin-left: 1px; vertical-align: baseline; }
    .ml-price .cur { font-size: 15px; margin-right: 2px; }
    .ml-pct { color: var(--ml-green); font-size: 13px; font-weight: 600; margin-top: 4px; white-space: nowrap; }
    .ml-ask { font-size: 13px; color: var(--ml-blue); font-weight: 600; margin-top: 6px; }
    .ml-pix { font-size: 12px; color: var(--ml-green); font-weight: 600; }
    .ml-stock { font-size: 12px; margin-top: 3px; color: var(--ml-muted); }
    .ml-stock.low { color: var(--ml-orange); font-weight: 700; }
    .ml-ends { font-size: 11px; color: #d9541a; font-weight: 700; display: inline-flex; gap: 4px; align-items: center; }
    .ml-rail .ml-price { font-size: 19px; }
    .ml-rail .ml-title { font-size: 12px; }
    .ml-rail .ml-card-body { padding: 8px 10px 12px; }

    /* Paginação */
    .ml-more { display: flex; justify-content: center; align-items: center; min-height: 56px; margin: 18px 0 8px; color: var(--ml-muted, #777); font-size: 13px; }
    .ml-more-load { display: inline-flex; align-items: center; gap: 10px; }
    .ml-more-end { opacity: .8; }
    .ml-spin { width: 20px; height: 20px; border-radius: 50%; border: 3px solid rgba(52,131,250,.25); border-top-color: var(--ml-blue); animation: mlspin .8s linear infinite; }
    @keyframes mlspin { to { transform: rotate(360deg); } }

    /* Vazio */
    .ml-empty { padding: 48px 20px; text-align: center; }
    .ml-empty i { font-size: 44px; color: #ccc; }
    .ml-empty h3 { margin: 14px 0 6px; font-size: 17px; }
    .ml-empty p { margin: 0 0 16px; color: var(--ml-muted); font-size: 14px; }

    /* Botões */
    .ml-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 48px; border: 0; border-radius: 6px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .15s; }
    .ml-btn-primary { background: var(--ml-blue); color: #fff; }
    .ml-btn-primary:hover { background: var(--ml-blue-dark); }
    .ml-btn-soft { background: var(--ml-blue-soft); color: var(--ml-blue); }
    .ml-btn-soft:hover { background: #d0e1fa; }
    .ml-btn-wa { background: #fff; color: #128c4b; border: 1px solid #b5e3c9; }
    .ml-btn-sm { display: inline-flex; width: auto; height: 40px; padding: 0 20px; }

    /* Ficha do produto (bottom sheet) */
    .ml-sheet-bg { position: fixed; inset: 0; z-index: 90; background: rgba(0,0,0,.5); }
    .ml-sheet { position: fixed; z-index: 91; left: 0; right: 0; bottom: 0; max-height: 94vh; background: #fff; border-radius: 16px 16px 0 0; overflow-y: auto; overscroll-behavior: contain; padding-bottom: calc(16px + env(safe-area-inset-bottom)); }
    .ml-sheet-grip { position: sticky; top: 0; z-index: 3; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 8px 8px 4px 16px; }
    .ml-sheet-grip::before { content: ''; position: absolute; left: 50%; top: 6px; width: 40px; height: 4px; margin-left: -20px; border-radius: 4px; background: #d9d9d9; }
    .ml-sheet-grip small { font-size: 12px; color: var(--ml-muted); padding-top: 10px; }
    .ml-x { width: 40px; height: 40px; border: 0; background: transparent; border-radius: 50%; font-size: 18px; color: #666; cursor: pointer; }
    .ml-gal { position: relative; }
    .ml-gal-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
    .ml-gal-track::-webkit-scrollbar { display: none; }
    .ml-gal-track > div { flex: 0 0 100%; scroll-snap-align: center; aspect-ratio: 1 / 1; max-height: 52vh; display: flex; align-items: center; justify-content: center; }
    .ml-gal-track img { width: 100%; height: 100%; object-fit: contain; padding: 6px 16px; }
    .ml-gal-n { position: absolute; left: 14px; top: 8px; background: #f2f2f2; color: #555; font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
    .ml-dots { display: flex; justify-content: center; gap: 6px; padding: 8px 0 2px; }
    .ml-dots i { width: 6px; height: 6px; border-radius: 50%; background: #d0d0d0; transition: all .2s; }
    .ml-dots i.on { background: var(--ml-blue); width: 16px; border-radius: 6px; }
    .ml-thumbs { display: none; }
    .ml-info { padding: 6px 16px 4px; }
    .ml-info .ml-cat-line { font-size: 12px; color: var(--ml-muted); }
    .ml-info h2 { margin: 6px 0 10px; font-size: 18px; font-weight: 500; line-height: 1.3; color: #333; }
    .ml-info .ml-price { font-size: 32px; }
    .ml-info .ml-price sup { font-size: 15px; }
    .ml-info .ml-pct { font-size: 16px; }
    .ml-perk { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; margin-top: 12px; }
    .ml-perk i { width: 18px; text-align: center; margin-top: 2px; color: var(--ml-green); }
    .ml-perk b { color: var(--ml-green); font-weight: 600; }
    .ml-perk span small { display: block; color: var(--ml-muted); font-size: 12px; margin-top: 1px; }
    .ml-qty { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 16px 0 12px; padding: 10px 12px; border-radius: 6px; background: #f5f5f5; }
    .ml-qty-label { font-size: 14px; }
    .ml-qty-label small { color: var(--ml-muted); margin-left: 4px; }
    .ml-step { display: inline-flex; align-items: center; background: #fff; border-radius: 999px; box-shadow: var(--ml-shadow); }
    .ml-step button { width: 36px; height: 36px; border: 0; background: transparent; color: var(--ml-blue); font-size: 14px; cursor: pointer; }
    .ml-step button:disabled { color: #ccc; cursor: default; }
    .ml-step span { min-width: 26px; text-align: center; font-weight: 700; }
    .ml-actions { display: grid; gap: 8px; }
    .ml-desc { margin: 20px 16px 0; padding-top: 16px; border-top: 1px solid var(--ml-line); }
    .ml-desc h3 { margin: 0 0 8px; font-size: 17px; font-weight: 600; }
    .ml-desc p { margin: 0; white-space: pre-line; color: #555; font-size: 14px; line-height: 1.55; }
    .ml-specs { margin: 14px 0 0; border-radius: 6px; overflow: hidden; border: 1px solid var(--ml-line); }
    .ml-specs div { display: flex; font-size: 13px; }
    .ml-specs div:nth-child(odd) { background: #f5f5f5; }
    .ml-specs dt { width: 40%; padding: 9px 12px; font-weight: 600; }
    .ml-specs dd { margin: 0; padding: 9px 12px; flex: 1; color: #555; }
    @media (min-width: 900px) {
        .ml-sheet { left: 50%; top: 50%; bottom: auto; right: auto; transform: translate(-50%, -50%); width: min(980px, 94vw); max-height: 90vh; border-radius: 12px; padding-bottom: 20px; }
        .ml-sheet-grip::before { display: none; }
        .ml-sheet-body { display: grid; grid-template-columns: 1.15fr 1fr; gap: 8px; align-items: start; }
        .ml-gal-track > div { max-height: 480px; }
        .ml-thumbs { display: flex; gap: 6px; padding: 8px 16px 0; flex-wrap: wrap; }
        .ml-thumbs button { width: 52px; height: 52px; border: 1px solid var(--ml-line); border-radius: 6px; background: #fff; padding: 3px; cursor: pointer; }
        .ml-thumbs button.on { border: 2px solid var(--ml-blue); }
        .ml-thumbs img { width: 100%; height: 100%; object-fit: contain; }
        .ml-dots { display: none; }
        .ml-info { padding: 4px 22px 0 8px; }
        .ml-desc { grid-column: 1 / -1; }
    }

    /* Carrinho fixo embaixo */
    .ml-cartbar { position: fixed; z-index: 70; left: 0; right: 0; bottom: 0; background: #fff; box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); }
    .ml-cartbar-in { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
    .ml-cartbar-sum { flex: 1; min-width: 0; }
    .ml-cartbar-sum small { display: block; color: var(--ml-muted); font-size: 12px; }
    .ml-cartbar-sum strong { font-size: 18px; font-weight: 700; }
    .ml-cartbar .ml-btn { width: auto; padding: 0 22px; height: 46px; }

    .ml-cart { position: fixed; z-index: 92; top: 0; right: 0; bottom: 0; width: min(420px, 100vw); background: #fff; display: flex; flex-direction: column; box-shadow: -8px 0 30px rgba(0,0,0,.2); padding-top: env(safe-area-inset-top); }
    .ml-cart-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 8px 10px 16px; border-bottom: 1px solid var(--ml-line); }
    .ml-cart-head h2 { margin: 0; font-size: 18px; font-weight: 800; }
    .ml-cart-head small { color: var(--ml-muted); font-weight: 600; font-size: 14px; }
    .ml-cart-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 30px; color: var(--ml-muted); }
    .ml-cart-empty > i { font-size: 46px; color: #d4d4d8; }
    .ml-cart-empty h3 { margin: 14px 0 6px; color: #333; font-size: 17px; }
    .ml-cart-empty p { margin: 0 0 18px; font-size: 14px; }
    .ml-cart-list { list-style: none; margin: 0; padding: 4px 16px; flex: 1; overflow-y: auto; overscroll-behavior: contain; }
    .ml-cart-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--ml-line); }
    .ml-cart-img { width: 72px; height: 72px; flex-shrink: 0; border-radius: 8px; overflow: hidden; border: 1px solid var(--ml-line); background: #fff; }
    .ml-cart-img img { width: 100%; height: 100%; object-fit: contain; }
    .ml-cart-img .ml-ph { font-size: 22px; }
    .ml-cart-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .ml-cart-info p { margin: 0; font-size: 14px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ml-cart-info strong { font-size: 17px; font-weight: 700; margin-top: 2px; }
    .ml-cart-info small { font-size: 12px; color: var(--ml-muted); }
    .ml-cart-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 8px; }
    .ml-cart-row .ml-step { box-shadow: none; border: 1px solid var(--ml-line); }
    .ml-cart-row .ml-step button { width: 32px; height: 32px; }
    .ml-cart-del { border: 0; background: transparent; color: var(--ml-red); font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 8px 4px; cursor: pointer; }
    .ml-cart-foot { border-top: 1px solid var(--ml-line); padding: 12px 16px calc(14px + env(safe-area-inset-bottom)); display: grid; gap: 8px; box-shadow: 0 -4px 12px rgba(0,0,0,.05); }
    .ml-cart-total { display: flex; align-items: baseline; justify-content: space-between; }
    .ml-cart-total span { font-size: 15px; }
    .ml-cart-total strong { font-size: 22px; font-weight: 800; }
    .ml-cart-note { margin: 0; font-size: 12px; color: var(--ml-muted); text-align: center; }
    .ml-cart-note a { color: var(--ml-blue); font-weight: 700; }
    .ml-cart-clear { border: 0; background: transparent; color: var(--ml-muted); font-size: 13px; font-weight: 600; padding: 4px; cursor: pointer; text-decoration: underline; }
    .ml-cart-in { animation: mlSide .25s cubic-bezier(.2,.8,.2,1); }
    .ml-cart-out { animation: mlSide .18s reverse ease-in; }
    @keyframes mlSide { from { transform: translateX(100%); } to { transform: none; } }

    .ml-toast { position: fixed; z-index: 95; left: 50%; bottom: 92px; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 6px 20px rgba(0,0,0,.25); max-width: calc(100vw - 32px); white-space: nowrap; }
    .ml-toast i { color: #3fe08a; }
    .ml-toast span { overflow: hidden; text-overflow: ellipsis; }

    .ml-guest { display: flex; align-items: center; gap: 10px; margin-top: 12px; padding: 10px 12px; font-size: 13px; color: var(--ml-muted); }
    .ml-guest i { color: var(--ml-blue); font-size: 18px; }
    .ml-guest a { color: var(--ml-blue); font-weight: 700; white-space: nowrap; margin-left: auto; }
</style>
<style>
    .ml-anim-in { animation: mlUp .26s cubic-bezier(.2,.8,.2,1); }
    .ml-anim-out { animation: mlUp .18s reverse ease-in; }
    @keyframes mlUp { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
    @media (min-width: 900px) {
        @keyframes mlUp { from { transform: translate(-50%, -46%); opacity: 0; } to { transform: translate(-50%, -50%); opacity: 1; } }
    }
</style>
