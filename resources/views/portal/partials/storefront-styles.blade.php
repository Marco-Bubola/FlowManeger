<style>
    /* Vitrine: aviso, banners (topo, faixa, lateral) e coleções */
    @media (max-width: 520px) { .sf-notice { font-size: 12px; padding: 7px 10px; } }
    .sf-notice { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 10px 0 0; padding: 8px 12px; border-radius: 8px; background: #fff7e6; color: #8a5a00; font-size: 13px; font-weight: 600; text-align: center; border: 1px solid #f6dfae; }
    .sf-notice i { color: #d48806; }
    a.sf-notice:hover { background: #fff1d6; }

    .sf-ban { position: relative; display: block; overflow: hidden; border-radius: var(--ml-radius); background: var(--sf-bg); color: #fff; text-decoration: none; isolation: isolate; }
    .sf-ban picture, .sf-ban img { display: block; width: 100%; height: 100%; }
    .sf-ban img { object-fit: cover; }
    .sf-ban.has-img .sf-txt { position: absolute; inset: 0; z-index: 1; background: linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.18) 55%, transparent 80%); }
    .sf-txt { display: flex; flex-direction: column; justify-content: center; align-items: flex-start; gap: 6px; padding: 18px 20px; }
    .sf-txt b { font-size: 21px; line-height: 1.15; font-weight: 900; max-width: 560px; text-wrap: balance; }
    .sf-txt > span { font-size: 13px; opacity: .92; max-width: 480px; line-height: 1.4; }
    .sf-cta { display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; background: #fff; color: #1f2937; font-style: normal; font-size: 13px; font-weight: 800; padding: 8px 14px; border-radius: 999px; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .sf-ban.no-img::after { content: ''; position: absolute; z-index: -1; right: -50px; bottom: -70px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.13); }
    .sf-ban.no-img::before { content: ''; position: absolute; z-index: -1; right: 90px; top: -60px; width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,.08); }
    a.sf-ban:focus-visible { outline: 3px solid var(--ml-blue); outline-offset: 2px; }

    /* Topo: carrossel */
    .sf-hero-wrap { position: relative; margin-top: 12px; }
    .sf-hero-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; border-radius: var(--ml-radius); }
    .sf-hero-track::-webkit-scrollbar { display: none; }
    .sf-hero-track > .sf-ban { flex: 0 0 100%; scroll-snap-align: center; scroll-snap-stop: always; border-radius: 0; }
    .sf-hero.has-img { aspect-ratio: 3 / 1; }
    .sf-hero.no-img { min-height: 168px; display: flex; }
    .sf-hero.no-img .sf-txt { padding: 22px 22px 26px; }
    @media (max-width: 767px) { .sf-hero.has-img.has-m { aspect-ratio: 16 / 9; } .sf-hero.has-img .sf-txt b { font-size: 17px; } .sf-hero.has-img .sf-txt > span { display: none; } }
    .sf-dots { position: absolute; left: 0; right: 0; bottom: 8px; display: flex; justify-content: center; gap: 6px; z-index: 2; pointer-events: none; }
    .sf-dots button { pointer-events: auto; width: 8px; height: 8px; padding: 0; border: 0; border-radius: 99px; background: rgba(255,255,255,.55); box-shadow: 0 0 0 1px rgba(0,0,0,.08); cursor: pointer; transition: width .2s, background .2s; }
    .sf-dots button.on { width: 22px; background: #fff; }
    .sf-arrow { display: none; position: absolute; top: 50%; z-index: 2; width: 40px; height: 40px; margin-top: -20px; border: 0; border-radius: 50%; background: rgba(255,255,255,.92); color: #333; box-shadow: 0 2px 10px rgba(0,0,0,.2); cursor: pointer; font-size: 15px; }
    .sf-arrow.l { left: 10px; } .sf-arrow.r { right: 10px; }
    @media (min-width: 768px) {
        .sf-hero-wrap:hover .sf-arrow { display: flex; align-items: center; justify-content: center; }
        .sf-hero.no-img { min-height: 220px; }
        .sf-hero.no-img .sf-txt { padding: 28px 40px; }
        .sf-txt b { font-size: 28px; }
        .sf-txt > span { font-size: 15px; }
    }
    @media (min-width: 1180px) { .sf-hero.has-img .sf-txt, .sf-hero.no-img .sf-txt { padding-left: 64px; } }

    /* Faixa entre as seções */
    .sf-strip { margin-top: 12px; }
    .sf-strip.has-img { aspect-ratio: 6 / 1; }
    @media (max-width: 767px) { .sf-strip.has-img.has-m { aspect-ratio: 3 / 1; } .sf-strip.has-img:not(.has-m) .sf-txt { display: none; } }
    .sf-strip.no-img .sf-txt { flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: flex-start; padding: 14px 18px; gap: 6px 14px; }
    .sf-strip.no-img .sf-txt b { font-size: 17px; }
    .sf-strip.no-img .sf-cta { margin: 0 0 0 auto; }
    @media (max-width: 520px) { .sf-strip.no-img .sf-cta { margin-left: 0; } }

    /* Laterais: só quando sobra espaço dos dois lados do catálogo */
    .sf-side { display: none; }
    .sf-side { max-height: calc(100vh - 110px); overflow: hidden; }
    @media (min-width: 1580px) {
        .sf-side { display: block; position: fixed; z-index: 30; top: calc(84px + env(safe-area-inset-top)); width: 160px; }
        .sf-side.left { left: calc(50% - 600px - 180px); }
        .sf-side.right { right: calc(50% - 600px - 180px); }
        .sf-side .sf-ban { margin-bottom: 12px; box-shadow: var(--ml-shadow); }
        .sf-side .sf-ban.has-img { aspect-ratio: 4 / 15; }
        .sf-side .sf-ban.no-img { min-height: 420px; display: flex; }
        .sf-side .sf-ban.no-img .sf-txt { justify-content: flex-end; padding: 18px 14px; }
        .sf-side .sf-txt b { font-size: 20px; }
        .sf-side .sf-txt > span { font-size: 13px; }
        .sf-side .sf-ban.has-img .sf-txt { background: linear-gradient(0deg, rgba(0,0,0,.6) 0%, transparent 60%); justify-content: flex-end; padding: 14px; }
    }
    @media (min-width: 2200px) {
        .sf-side { width: 220px; }
        .sf-side.left { left: calc(50% - 600px - 250px); }
        .sf-side.right { right: calc(50% - 600px - 250px); }
    }

    /* Coleções */
    .sf-col { margin-top: 12px; overflow: hidden; }
    .sf-col-ban { display: block; border-radius: 0; text-decoration: none; }
    .sf-col-page .sf-col-ban { border-radius: var(--ml-radius, 14px); overflow: hidden; }
    .sf-col-head { display: flex; position: relative; min-height: 96px; background: var(--sf-bg); color: #fff; text-decoration: none; isolation: isolate; }
    .sf-col-head.has-img { aspect-ratio: 4 / 1; min-height: 0; }
    @media (max-width: 767px) { .sf-col-head.has-img { aspect-ratio: 5 / 2; } }
    .sf-col-head img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; }
    .sf-col-head.has-img::before { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(90deg, rgba(0,0,0,.55), rgba(0,0,0,.05) 70%); }
    .sf-col-head.no-img::after { content: ''; position: absolute; z-index: -1; right: -30px; top: -50px; width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,.13); }
    .sf-col-txt { display: flex; flex-direction: column; justify-content: center; gap: 4px; padding: 16px 18px; flex: 1; min-width: 0; }
    .sf-col-txt small { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .85; }
    .sf-col-txt b { font-size: 20px; font-weight: 900; line-height: 1.15; }
    .sf-col-txt span { font-size: 13px; opacity: .92; max-width: 520px; }
    .sf-col-go { align-self: center; margin-right: 16px; flex-shrink: 0; background: rgba(255,255,255,.95); color: #1f2937; font-size: 13px; font-weight: 800; padding: 8px 14px; border-radius: 999px; white-space: nowrap; }
    @media (min-width: 768px) { .sf-col-txt { padding: 20px 28px; } .sf-col-txt b { font-size: 26px; } }
    .sf-col .ml-rail { padding-top: 12px; }
    .sf-col-page { margin-top: 12px; }
    .sf-col-page .sf-col-head { border-radius: var(--ml-radius); }
    .ml-sec-head h2 i.sf-star { color: #f5b301; }

    /* Modelos prontos (banner desenhado em HTML) */
    .sf-ban.has-design { display: block; }
    .sf-d { position: relative; isolation: isolate; display: flex; align-items: center; gap: 14px; width: 100%; height: 100%; overflow: hidden; padding: 18px 18px; color: #fff; background: linear-gradient(120deg, var(--c1), var(--c2)); text-align: left; }
    .sf-d.ink-dark { color: #1f2937; }
    .sf-d-txt { position: relative; z-index: 1; flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }
    .sf-d-badge { font-style: normal; font-size: 11px; font-weight: 900; letter-spacing: .08em; padding: 4px 10px; border-radius: 999px; background: var(--acc); color: var(--c2); white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
    .sf-d.ink-dark .sf-d-badge, .sf-d.ink-dark .sf-d-cta { color: #fff; }
    .sf-d-title { font-size: 22px; line-height: 1.08; font-weight: 900; letter-spacing: -.01em; text-wrap: balance; max-width: 620px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .sf-d-sub { font-size: 13px; opacity: .9; line-height: 1.35; max-width: 520px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .sf-d-cta { display: inline-flex; align-items: center; gap: 6px; margin-top: 4px; background: var(--acc); color: var(--c2); font-weight: 800; font-size: 13px; padding: 8px 14px; border-radius: 999px; box-shadow: 0 4px 14px rgba(0,0,0,.16); white-space: nowrap; }
    .sf-d-cta svg { width: 14px; height: 14px; fill: currentColor; }
    .sf-d-pics { position: relative; z-index: 1; flex-shrink: 0; display: flex; align-items: center; gap: 10px; }
    .sf-d-pic { display: block; width: 118px; aspect-ratio: 1; border-radius: 18px; background: #fff; padding: 8px; box-shadow: 0 10px 28px rgba(0,0,0,.2); overflow: hidden; }
    .sf-d-pic img { width: 100%; height: 100%; object-fit: contain; }
    .sf-d-deco { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
    .sf-d-deco i { position: absolute; display: block; width: 34px; height: 34px; opacity: .16; }
    .sf-d-deco svg { width: 100%; height: 100%; fill: currentColor; }
    .sf-d-deco i:nth-child(1) { right: 4%; top: -8%; width: 120px; height: 120px; opacity: .12; transform: rotate(14deg); }
    .sf-d-deco i:nth-child(2) { left: 46%; top: 10%; width: 26px; height: 26px; }
    .sf-d-deco i:nth-child(3) { left: 58%; bottom: 8%; width: 40px; height: 40px; transform: rotate(-12deg); }
    .sf-d-deco i:nth-child(4) { right: 28%; bottom: -12%; width: 70px; height: 70px; opacity: .1; }
    .sf-d-deco i:nth-child(5) { left: -10px; bottom: -14px; width: 60px; height: 60px; opacity: .1; }
    /* cada modelo */
    .sf-d-round .sf-d-pic { border-radius: 50%; box-shadow: 0 0 0 10px rgba(124,58,237,.12), 0 12px 30px rgba(124,58,237,.25); }
    .sf-d-round .sf-d-deco i { color: var(--acc); }
    .sf-d-tilt .sf-d-pic { transform: rotate(-4deg); border-radius: 24px; }
    .sf-d-stripe { background: radial-gradient(circle at 85% 30%, #3f3f46 0, transparent 45%), linear-gradient(120deg, var(--c1), var(--c2)); }
    .sf-d-stripe::after { content: ''; position: absolute; z-index: 0; left: -10%; right: -10%; bottom: 18%; height: 18px; background: var(--acc); transform: rotate(-4deg); opacity: .9; }
    .sf-d-stripe .sf-d-deco i { color: var(--acc); opacity: .35; }
    .sf-d-stripe .sf-d-badge, .sf-d-stripe .sf-d-cta { color: #111; }
    .sf-d-stripe .sf-d-pic { box-shadow: 0 0 0 3px var(--acc), 0 12px 30px rgba(0,0,0,.5); }
    .sf-d-bigicon .sf-d-deco i:nth-child(1) { width: 170px; height: 170px; right: 2%; top: 50%; margin-top: -85px; transform: none; opacity: .14; }
    .sf-d-ticket .sf-d-badge { order: 3; font-size: 18px; letter-spacing: .14em; padding: 8px 16px; border-radius: 10px; border: 2px dashed var(--c2); box-shadow: 0 0 0 4px var(--acc); margin: 4px 0 2px 4px; }
    .sf-d-ticket .sf-d-cta { order: 4; }
    .sf-d-clean { border: 1px solid #e5e7eb; }
    .sf-d-clean .sf-d-pic { box-shadow: 0 6px 18px rgba(15,23,42,.1); border: 1px solid #eef2f7; }
    .sf-d.sf-d-clean .sf-d-badge { background: transparent; color: var(--acc); padding: 0; letter-spacing: .12em; }
    .sf-d-serif .sf-d-deco i { color: var(--acc); opacity: .3; }
    .sf-d-serif .sf-d-badge { font-family: Georgia, serif; font-style: italic; letter-spacing: .04em; font-size: 13px; }

    .sf-d-round .sf-d-pic { border-radius: 50%; }
    .sf-d-tilt .sf-d-deco i { opacity: .2; }
    .sf-d-stripe .sf-d-badge, .sf-d-stripe .sf-d-cta { color: #111; }
    /* layouts: foto à esquerda e só texto no centro */
    .sf-d.lay-dir { flex-direction: row-reverse; }
    .sf-d.lay-centro { justify-content: center; }
    .sf-d.lay-centro .sf-d-txt { flex: 0 1 auto; align-items: center; text-align: center; }
    .sf-d.lay-centro .sf-d-title, .sf-d.lay-centro .sf-d-sub { margin-left: auto; margin-right: auto; }

    /* tamanhos por lugar: medidos pela largura do próprio banner (vale igual no catálogo e na prévia do painel) */
    .sf-ban.has-design { container-type: inline-size; }
    .sf-hero.has-design { display: flex; }
    .sf-hero .sf-d { height: auto; min-height: 196px; }
    .sf-hero .sf-d.pics-3 .sf-d-pic { width: 74px; padding: 5px; border-radius: 12px; }
    .sf-hero .sf-d.pics-3 .sf-d-pic:nth-child(3) { display: none; }
    @container (max-width: 400px) { .sf-hero .sf-d.pics-1 .sf-d-pic { width: 104px; } .sf-hero .sf-d-title { font-size: 20px; } .sf-hero .sf-d-sub { -webkit-line-clamp: 1; } }
    @container (min-width: 680px) {
        .sf-hero .sf-d { min-height: 260px; padding: 26px 40px; gap: 24px; }
        .sf-hero .sf-d-title { font-size: 34px; }
        .sf-hero .sf-d-sub { font-size: 16px; }
        .sf-hero .sf-d-badge { font-size: 12px; padding: 5px 12px; }
        .sf-hero .sf-d-cta { font-size: 15px; padding: 10px 18px; margin-top: 8px; }
        .sf-hero .sf-d-pic { width: 190px; border-radius: 22px; padding: 12px; }
        .sf-hero .sf-d.pics-3 .sf-d-pic { width: 140px; padding: 8px; border-radius: 18px; }
        .sf-hero .sf-d.pics-3 .sf-d-pic:nth-child(3) { display: block; }
        .sf-hero .sf-d.pics-3 .sf-d-pic:nth-child(2) { transform: translateY(-14px); }
        .sf-hero .sf-d.pics-3 .sf-d-pic { width: 118px; }
        .sf-hero .sf-d.pics-3 .sf-d-title { font-size: 28px; }
    }
    @container (min-width: 1000px) {
        .sf-hero .sf-d { min-height: 300px; padding: 30px 64px; }
        .sf-hero .sf-d-title { font-size: 44px; }
        .sf-hero .sf-d-pic { width: 228px; }
        .sf-hero .sf-d.pics-3 .sf-d-pic { width: 170px; }
        .sf-hero .sf-d.pics-3 .sf-d-title { font-size: 38px; }
    }
    .sf-strip .sf-d { min-height: 96px; padding: 10px 16px; gap: 12px; }
    .sf-strip .sf-d-txt { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 4px 12px; }
    .sf-strip .sf-d-title { font-size: 16px; -webkit-line-clamp: 2; }
    .sf-strip .sf-d-sub { display: none; }
    .sf-strip .sf-d-cta { margin: 0; padding: 7px 12px; font-size: 12px; }
    .sf-strip .sf-d-pic { width: 70px; padding: 5px; border-radius: 12px; }
    .sf-strip .sf-d.pics-3 .sf-d-pic:nth-child(n+2) { display: none; }
    .sf-strip .sf-d-deco i:nth-child(1) { width: 80px; height: 80px; }
    .sf-strip .sf-d-ticket .sf-d-badge { font-size: 14px; padding: 4px 10px; }
    @container (max-width: 500px) { .sf-strip .sf-d-badge { display: none; } .sf-strip .sf-d-ticket .sf-d-badge { display: inline-block; } }
    @container (min-width: 680px) {
        .sf-strip .sf-d { min-height: 112px; padding: 12px 32px; gap: 20px; }
        .sf-strip .sf-d-title { font-size: 22px; -webkit-line-clamp: 1; }
        .sf-strip .sf-d-sub { display: block; font-size: 14px; -webkit-line-clamp: 1; }
        .sf-strip .sf-d-cta { margin-left: auto; font-size: 14px; padding: 9px 16px; }
        .sf-strip .sf-d-pic { width: 84px; }
        .sf-strip .sf-d.pics-3 .sf-d-pic:nth-child(n+2) { display: block; }
    }
    .sf-sb .sf-d { height: 600px; max-height: calc(100vh - 110px); flex-direction: column-reverse; justify-content: space-between; align-items: stretch; padding: 18px 14px; }
    .sf-sb .sf-d.lay-dir { flex-direction: column; }
    .sf-sb .sf-d-title { font-size: 22px; -webkit-line-clamp: 4; }
    .sf-sb .sf-d-sub { font-size: 13px; -webkit-line-clamp: 3; }
    .sf-sb .sf-d-pics { justify-content: center; padding-top: 10px; }
    .sf-sb .sf-d-pic { width: 120px; }
    .sf-sb .sf-d.pics-3 .sf-d-pic:nth-child(n+2) { display: none; }
    .sf-sb .sf-d-cta { white-space: normal; }
    @container (min-width: 200px) { .sf-sb .sf-d-pic { width: 170px; } .sf-sb .sf-d-title { font-size: 28px; } }
    /* Formatos: mudam a estrutura do banner, não só o fundo */
    .sf-d-ghost { display: none; }
    .sf-d-count { display: inline-flex; gap: 6px; margin-top: 2px; }
    .sf-d-count > span { display: flex; flex-direction: column; align-items: center; min-width: 46px; padding: 6px 6px 4px; border-radius: 10px; background: rgba(0,0,0,.28); border: 1px solid rgba(255,255,255,.25); line-height: 1; }
    .sf-d.ink-dark .sf-d-count > span { background: rgba(255,255,255,.7); border-color: rgba(0,0,0,.08); }
    .sf-d-count b { font-size: 20px; font-weight: 900; font-variant-numeric: tabular-nums; }
    .sf-d-count small { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; opacity: .8; margin-top: 3px; }
    .sf-d.fmt-preco .sf-d-badge { font-size: 26px; letter-spacing: -.01em; padding: 6px 14px; border-radius: 14px; transform: rotate(-3deg); box-shadow: 0 8px 22px rgba(0,0,0,.25); overflow: visible; margin: 4px 0 8px; }
    .sf-d.fmt-preco .sf-d-badge::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--c2); margin-right: 8px; vertical-align: middle; box-shadow: inset 0 0 0 2px rgba(255,255,255,.6); }
    .sf-strip .sf-d.fmt-preco .sf-d-badge { font-size: 18px; padding: 3px 10px; display: inline-block; }
    .sf-hero .sf-d.fmt-meio, .sf-hero .sf-d.fmt-diagonal { padding: 0; gap: 0; align-items: stretch; }
    .sf-hero .sf-d.fmt-meio .sf-d-txt, .sf-hero .sf-d.fmt-diagonal .sf-d-txt { justify-content: center; padding: 18px 16px 18px 18px; }
    .sf-hero .sf-d.fmt-meio .sf-d-pics, .sf-hero .sf-d.fmt-diagonal .sf-d-pics { width: 44%; justify-content: center; background: #fff; padding: 14px 10px 14px 26px; }
    .sf-hero .sf-d.fmt-meio .sf-d-pics { border-radius: 50% 0 0 50% / 100% 0 0 100%; box-shadow: -10px 0 0 rgba(255,255,255,.25); }
    .sf-hero .sf-d.fmt-diagonal .sf-d-pics { clip-path: polygon(22% 0, 100% 0, 100% 100%, 0 100%); padding-left: 34px; }
    .sf-hero .sf-d.fmt-diagonal::after { content: ''; position: absolute; z-index: 0; top: 0; bottom: 0; right: 44%; width: 26px; background: var(--acc); transform: skewX(-12deg) translateX(30px); opacity: .9; }
    .sf-hero .sf-d.fmt-meio.lay-dir .sf-d-pics { border-radius: 0 50% 50% 0 / 0 100% 100% 0; padding: 14px 26px 14px 10px; box-shadow: 10px 0 0 rgba(255,255,255,.25); }
    .sf-hero .sf-d.fmt-diagonal.lay-dir .sf-d-pics { clip-path: polygon(0 0, 100% 0, 78% 100%, 0 100%); padding: 14px 34px 14px 10px; }
    .sf-hero .sf-d.fmt-diagonal.lay-dir::after { right: auto; left: 44%; transform: skewX(-12deg) translateX(-30px); }
    .sf-hero .sf-d.fmt-meio .sf-d-pic, .sf-hero .sf-d.fmt-diagonal .sf-d-pic { width: 100% !important; max-width: 190px; background: transparent; box-shadow: none; padding: 0; border-radius: 0; }
    .sf-hero .sf-d.fmt-holofote .sf-d-pics::before { content: ''; position: absolute; z-index: -1; left: 50%; top: 50%; width: 170%; aspect-ratio: 1; translate: -50% -50%; border-radius: 50%; background: radial-gradient(circle, var(--acc) 0, color-mix(in srgb, var(--acc) 40%, transparent) 35%, transparent 68%); opacity: .55; }
    .sf-hero .sf-d.fmt-holofote .sf-d-pic { border-radius: 50%; padding: 14px; background: radial-gradient(circle at 40% 35%, #fff 0, #f3f4f6 70%); box-shadow: 0 0 0 6px rgba(255,255,255,.35), 0 18px 40px rgba(0,0,0,.3); width: 132px; }
    .sf-hero .sf-d.fmt-holofote .sf-d-title { text-transform: uppercase; letter-spacing: .02em; }
    .sf-hero .sf-d.fmt-revista .sf-d-ghost { display: block; position: absolute; z-index: 0; left: -4px; bottom: -24px; font-size: 120px; line-height: 1; font-weight: 900; letter-spacing: -.04em; white-space: nowrap; opacity: .13; pointer-events: none; }
    .sf-hero .sf-d.fmt-revista.lay-dir .sf-d-ghost { left: auto; right: -4px; }
    .sf-hero .sf-d.fmt-revista .sf-d-title { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-weight: 700; }
    .sf-hero .sf-d.fmt-revista .sf-d-badge { background: transparent; color: inherit; padding: 0 0 4px; border-radius: 0; border-bottom: 2px solid var(--acc); letter-spacing: .2em; }
    .sf-hero .sf-d.fmt-revista .sf-d-pic { background: transparent; box-shadow: none; padding: 0; filter: drop-shadow(0 14px 18px rgba(0,0,0,.35)); width: 150px; }
    .sf-hero .sf-d.fmt-contagem .sf-d-txt { gap: 8px; }
    .sf-hero .sf-d.fmt-mosaico .sf-d-pics { display: grid; grid-template-columns: repeat(2, 84px); grid-auto-rows: 84px; gap: 6px; }
    .sf-hero .sf-d.fmt-mosaico .sf-d-pic { width: 100% !important; height: 100%; padding: 6px; border-radius: 12px; transform: none !important; display: block !important; }
    .sf-hero .sf-d.fmt-mosaico.pics-3 .sf-d-pic:first-child { grid-row: span 2; }
    .sf-hero .sf-d.fmt-mosaico.pics-1 .sf-d-pics { grid-template-columns: 150px; grid-auto-rows: 150px; }
    .sf-hero .sf-d.fmt-leque .sf-d-pics, .sf-hero .sf-d.fmt-polaroid .sf-d-pics { gap: 0; padding: 0 6px 0 16px; }
    .sf-hero .sf-d.fmt-leque .sf-d-pic, .sf-hero .sf-d.fmt-polaroid .sf-d-pic { display: block !important; width: 92px !important; flex-shrink: 0; }
    .sf-hero .sf-d.fmt-leque .sf-d-pic { margin-left: -34px; border-radius: 16px; transform-origin: 50% 120%; }
    .sf-hero .sf-d.fmt-leque .sf-d-pic:first-child { margin-left: 0; }
    .sf-hero .sf-d.fmt-leque.pics-3 .sf-d-pic:nth-child(1) { transform: rotate(-12deg) !important; }
    .sf-hero .sf-d.fmt-leque.pics-3 .sf-d-pic:nth-child(2) { transform: translateY(-8px) !important; z-index: 2; position: relative; }
    .sf-hero .sf-d.fmt-leque.pics-3 .sf-d-pic:nth-child(3) { transform: rotate(12deg) !important; }
    .sf-hero .sf-d.fmt-leque.pics-2 .sf-d-pic:nth-child(1) { transform: rotate(-8deg) !important; }
    .sf-hero .sf-d.fmt-leque.pics-2 .sf-d-pic:nth-child(2) { transform: rotate(8deg) !important; }
    .sf-hero .sf-d.fmt-polaroid .sf-d-pic { aspect-ratio: auto; height: auto; margin-left: -18px; border-radius: 3px; padding: 6px 6px 22px; background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,.28); }
    .sf-hero .sf-d.fmt-polaroid .sf-d-pic .sf-d-in { aspect-ratio: 1; height: auto; background: #f5f5f4; }
    .sf-hero .sf-d.fmt-polaroid .sf-d-pic:first-child { margin-left: 0; }
    .sf-hero .sf-d.fmt-polaroid .sf-d-pic:nth-child(odd) { transform: rotate(-6deg) !important; }
    .sf-hero .sf-d.fmt-polaroid .sf-d-pic:nth-child(even) { transform: rotate(5deg) translateY(-10px) !important; z-index: 2; position: relative; }
    @container (max-width: 400px) {
        .sf-hero .sf-d.fmt-mosaico .sf-d-pics { grid-template-columns: repeat(2, 62px); grid-auto-rows: 62px; gap: 4px; }
        .sf-hero .sf-d.fmt-leque .sf-d-pic, .sf-hero .sf-d.fmt-polaroid .sf-d-pic { width: 66px !important; }
        .sf-hero .sf-d.fmt-leque .sf-d-pic { margin-left: -30px; }
        .sf-hero .sf-d.fmt-polaroid .sf-d-pic { padding: 4px 4px 14px; margin-left: -22px; }
        .sf-hero .sf-d.fmt-revista .sf-d-ghost { font-size: 84px; }
        .sf-hero .sf-d.fmt-revista .sf-d-pic, .sf-hero .sf-d.fmt-holofote .sf-d-pic { width: 104px; }
        .sf-d.fmt-preco .sf-d-badge { font-size: 20px; }
        .sf-d-count > span { min-width: 40px; } .sf-d-count b { font-size: 17px; }
    }
    @container (min-width: 680px) {
        .sf-hero .sf-d.fmt-meio .sf-d-txt, .sf-hero .sf-d.fmt-diagonal .sf-d-txt { padding: 26px 24px 26px 40px; }
        .sf-hero .sf-d.fmt-meio.lay-dir .sf-d-txt, .sf-hero .sf-d.fmt-diagonal.lay-dir .sf-d-txt { padding: 26px 40px 26px 24px; }
        .sf-hero .sf-d.fmt-meio .sf-d-pic, .sf-hero .sf-d.fmt-diagonal .sf-d-pic { max-width: 320px; }
        .sf-hero .sf-d.fmt-holofote .sf-d-pic { width: 220px; padding: 20px; }
        .sf-hero .sf-d.fmt-revista .sf-d-ghost { font-size: 190px; bottom: -40px; }
        .sf-hero .sf-d.fmt-revista .sf-d-pic { width: 240px; }
        .sf-hero .sf-d.fmt-revista .sf-d-title { font-size: 42px; }
        .sf-d.fmt-preco .sf-d-badge { font-size: 46px; padding: 8px 20px; border-radius: 18px; }
        .sf-hero .sf-d-count > span { min-width: 64px; padding: 10px 8px 7px; border-radius: 14px; }
        .sf-hero .sf-d-count b { font-size: 32px; }
        .sf-hero .sf-d-count small { font-size: 10px; }
        .sf-hero .sf-d.fmt-mosaico .sf-d-pics { grid-template-columns: repeat(2, 120px); grid-auto-rows: 120px; gap: 8px; }
        .sf-hero .sf-d.fmt-mosaico.pics-1 .sf-d-pics { grid-template-columns: 220px; grid-auto-rows: 220px; }
        .sf-hero .sf-d.fmt-leque .sf-d-pic, .sf-hero .sf-d.fmt-polaroid .sf-d-pic { width: 150px !important; }
        .sf-hero .sf-d.fmt-leque .sf-d-pic { margin-left: -50px; }
        .sf-hero .sf-d.fmt-polaroid .sf-d-pic { padding: 8px 8px 30px; margin-left: -24px; }
    }
    @container (min-width: 1000px) {
        .sf-hero .sf-d.fmt-mosaico .sf-d-pics { grid-template-columns: repeat(2, 140px); grid-auto-rows: 140px; }
        .sf-hero .sf-d.fmt-leque .sf-d-pic, .sf-hero .sf-d.fmt-polaroid .sf-d-pic { width: 180px !important; }
        .sf-hero .sf-d.fmt-revista .sf-d-ghost { font-size: 240px; }
        .sf-hero .sf-d.fmt-meio .sf-d-pics, .sf-hero .sf-d.fmt-diagonal .sf-d-pics { width: 42%; }
    }
    .sf-strip .sf-d-count b { font-size: 16px; } .sf-strip .sf-d-count > span { min-width: 38px; padding: 4px; }
    .sf-strip .sf-d.fmt-mosaico .sf-d-pic:nth-child(n+2), .sf-sb .sf-d.fmt-mosaico .sf-d-pic:nth-child(n+2) { display: none; }
    .sf-strip .sf-d.fmt-polaroid .sf-d-pic, .sf-strip .sf-d.fmt-leque .sf-d-pic { border-radius: 4px; }
    .sf-sb .sf-d-count { flex-wrap: wrap; }
    /* Fotos de cada espaço empilhadas; com animação passam em ciclo (3,2 s cada). */
    .sf-d-in { position: relative; display: block; width: 100%; height: 100%; }
    .sf-d-in img { position: absolute; inset: 0; }
    .sf-d-in img:not(:first-child) { opacity: 0; }
    .sf-d-in.cyc-2 img { opacity: 1; animation: sf-c2 6.4s ease-in-out infinite; animation-delay: calc(var(--sf-j) * -3.2s + var(--sf-o, 0s)); }
    .sf-d-in.cyc-3 img { opacity: 1; animation: sf-c3 9.6s ease-in-out infinite; animation-delay: calc(var(--sf-j) * -3.2s + var(--sf-o, 0s)); }
    .sf-d.anim-deslizar .sf-d-in.cyc-2 img { animation-name: sf-s2; }
    .sf-d.anim-deslizar .sf-d-in.cyc-3 img { animation-name: sf-s3; }
    .sf-d.anim-zoom .sf-d-in, .sf-d.anim-vivo .sf-d-in { animation: sf-kb 7s ease-in-out infinite alternate; }
    .sf-d.anim-entrada .sf-d-txt > *, .sf-d.anim-vivo .sf-d-txt > * { animation: sf-up .7s cubic-bezier(.2,.8,.2,1) both; }
    .sf-d.anim-entrada .sf-d-txt > :nth-child(2), .sf-d.anim-vivo .sf-d-txt > :nth-child(2) { animation-delay: .12s; }
    .sf-d.anim-entrada .sf-d-txt > :nth-child(3), .sf-d.anim-vivo .sf-d-txt > :nth-child(3) { animation-delay: .24s; }
    .sf-d.anim-entrada .sf-d-txt > :nth-child(4), .sf-d.anim-vivo .sf-d-txt > :nth-child(4) { animation-delay: .36s; }
    .sf-d.anim-entrada .sf-d-pics, .sf-d.anim-vivo .sf-d-pics { animation: sf-in .8s .2s cubic-bezier(.2,.8,.2,1) both; }
    .sf-d.anim-vivo .sf-d-badge { animation: sf-up .7s both, sf-pulse 2.4s 1s ease-in-out infinite; }
    .sf-d.anim-vivo .sf-d-cta { position: relative; overflow: hidden; }
    .sf-d.anim-vivo .sf-d-cta::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 30%, rgba(255,255,255,.55) 50%, transparent 70%); translate: -120% 0; animation: sf-shine 3.2s 1.2s ease-in-out infinite; }
    .sf-d.anim-vivo .sf-d-deco i, .sf-d.anim-entrada .sf-d-deco i { animation: sf-float 5s ease-in-out infinite; }
    .sf-d.anim-vivo .sf-d-deco i:nth-child(2n), .sf-d.anim-entrada .sf-d-deco i:nth-child(2n) { animation-duration: 6.5s; animation-delay: -2s; }
    @keyframes sf-c2 { 0%, 42% { opacity: 1; } 50%, 92% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes sf-c3 { 0%, 28% { opacity: 1; } 33.4%, 95% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes sf-s2 { 0%, 42% { opacity: 1; translate: 0 0; } 50% { opacity: 0; translate: -40% 0; } 51%, 91% { opacity: 0; translate: 40% 0; } 100% { opacity: 1; translate: 0 0; } }
    @keyframes sf-s3 { 0%, 28% { opacity: 1; translate: 0 0; } 33.4% { opacity: 0; translate: -40% 0; } 34%, 94% { opacity: 0; translate: 40% 0; } 100% { opacity: 1; translate: 0 0; } }
    @keyframes sf-kb { from { scale: 1; } to { scale: 1.09; } }
    @keyframes sf-up { from { opacity: 0; translate: 0 14px; } to { opacity: 1; translate: 0 0; } }
    @keyframes sf-in { from { opacity: 0; translate: 24px 0; scale: .92; } to { opacity: 1; translate: 0 0; scale: 1; } }
    @keyframes sf-pulse { 0%, 100% { scale: 1; } 50% { scale: 1.07; } }
    @keyframes sf-shine { 0% { translate: -120% 0; } 45%, 100% { translate: 120% 0; } }
    @keyframes sf-float { 0%, 100% { translate: 0 0; } 50% { translate: 0 -9px; } }
    @media (prefers-reduced-motion: reduce) {
        .sf-d *, .sf-d *::after { animation: none !important; }
        .sf-d-in img:not(:first-child) { opacity: 0 !important; }
    }
</style>
