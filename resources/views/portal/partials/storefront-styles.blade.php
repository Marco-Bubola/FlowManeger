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
</style>
