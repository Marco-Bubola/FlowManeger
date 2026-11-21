{{-- Estilos e contagem dos blocos da página especial (no site e na prévia do painel). --}}
<style>
    .ps { container: ps / inline-size; --ps-acc: #7c3aed; --ps-r: 14px; --ps-t: 1; display: flex; flex-direction: column; gap: 12px; margin-top: 12px; color: var(--ml-text, #1f2937); }
    .ps.inteiro { gap: 0; }
    .ps.top { margin-top: 0; margin-bottom: 12px; }
    .ps-sec { background: var(--ps-bg, var(--ml-card, #fff)); border-radius: var(--ps-r); padding: 20px 16px; overflow: hidden; }
    .ps.cards .ps-sec { box-shadow: 0 1px 2px rgba(0,0,0,.08); }
    .ps.inteiro .ps-sec { border-radius: 0; }
    .ps.inteiro .ps-sec + .ps-sec { border-top: 1px solid var(--ml-line, #eee); }
    .ps h2.ps-h { margin: 0 0 12px; font-size: calc(20px * var(--ps-t)); font-weight: 800; line-height: 1.25; color: var(--ml-text, #111); }
    .ps h2.ps-h.c { text-align: center; }
    .ps-p { margin: 0; font-size: 15px; line-height: 1.65; color: var(--ml-muted, #555); }
    .ps-p.c { text-align: center; max-width: 680px; margin: 0 auto; }
    .ps-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 22px; border-radius: 999px; border: 0; font-size: 15px; font-weight: 800; color: #fff; background: var(--ps-acc); text-decoration: none; cursor: pointer; box-shadow: 0 6px 18px -6px rgba(0,0,0,.35); }
    .ps-btn:hover { filter: brightness(1.06); }
    .ps-zoom { cursor: zoom-in; }
    /* Banner */
    .ps-ban { position: relative; display: grid; gap: 14px; padding: 22px 18px; border-radius: var(--ps-r); color: #fff; background: linear-gradient(120deg, var(--c1), var(--c2)); overflow: hidden; }
    .ps-ban.dark { color: #1f2937; }
    .ps.inteiro .ps-ban { border-radius: 0; }
    .ps-ban-badge { align-self: start; justify-self: start; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; padding: 5px 11px; border-radius: 999px; background: rgba(255,255,255,.22); }
    .ps-ban.dark .ps-ban-badge { background: rgba(0,0,0,.08); }
    .ps-ban h2 { margin: 0; font-size: calc(26px * var(--ps-t)); font-weight: 900; line-height: 1.12; }
    .ps-ban p { margin: 0; font-size: 15px; line-height: 1.5; opacity: .92; }
    .ps-ban .ps-btn { justify-self: start; background: #fff; color: var(--c1); }
    .ps-ban.dark .ps-btn { background: #111827; color: #fff; }
    .ps-ban-txt { display: grid; gap: 10px; align-content: center; position: relative; z-index: 1; }
    .ps-ban-img { display: flex; align-items: center; justify-content: center; }
    .ps-ban-img img { max-width: 100%; max-height: 260px; object-fit: contain; border-radius: 12px; filter: drop-shadow(0 12px 24px rgba(0,0,0,.25)); }
    .ps-ban.fundo { min-height: 240px; }
    .ps-ban.fundo .ps-ban-img { position: absolute; inset: 0; }
    .ps-ban.fundo .ps-ban-img img { width: 100%; height: 100%; max-height: none; object-fit: cover; border-radius: 0; filter: none; }
    .ps-ban.fundo::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,.6), rgba(0,0,0,.1)); }
    .ps-ban.fundo.dark::after { background: linear-gradient(90deg, rgba(255,255,255,.85), rgba(255,255,255,.2)); }
    /* Faixa */
    .ps-faixa { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 16px; border-radius: var(--ps-r); background: var(--c1); color: #fff; font-size: 15px; font-weight: 800; text-align: center; }
    .ps.inteiro .ps-faixa { border-radius: 0; }
    .ps-faixa i { font-size: 18px; }
    /* Foto com texto */
    .ps-ft { display: grid; gap: 16px; align-items: center; }
    .ps-ft img { width: 100%; max-height: 420px; object-fit: contain; border-radius: calc(var(--ps-r) - 4px); background: #f6f6f6; }
    /* Destaques */
    .ps-dest { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ps-dest > div { display: flex; flex-direction: column; gap: 6px; padding: 14px 12px; border-radius: calc(var(--ps-r) - 4px); background: color-mix(in srgb, var(--ps-acc) 8%, transparent); }
    .ps-dest i { font-size: 24px; color: var(--ps-acc); }
    .ps-dest b { font-size: 15px; font-weight: 800; color: var(--ml-text, #111); }
    .ps-dest span { font-size: 13px; line-height: 1.45; color: var(--ml-muted, #555); }
    /* Galeria */
    .ps-gal { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ps-gal button { padding: 0; border: 0; border-radius: calc(var(--ps-r) - 6px); overflow: hidden; background: #f6f6f6; aspect-ratio: 1; cursor: zoom-in; }
    .ps-gal img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
    .ps-gal button:hover img { transform: scale(1.04); }
    /* Vídeo */
    .ps-vid { position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: calc(var(--ps-r) - 4px); overflow: hidden; background: #000; }
    .ps-vid.tall { aspect-ratio: 9 / 16; max-width: 380px; margin: 0 auto; }
    .ps-vid iframe, .ps-vid video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
    .ps-vid-empty { display: flex; align-items: center; justify-content: center; gap: 8px; color: #fff; opacity: .7; font-size: 14px; height: 100%; }
    /* Medidas */
    .ps-med { display: grid; gap: 16px; align-items: start; }
    .ps-med img { width: 100%; max-height: 360px; object-fit: contain; border-radius: calc(var(--ps-r) - 4px); background: #f6f6f6; }
    .ps-med dl { margin: 0; display: grid; border: 1px solid var(--ml-line, #eee); border-radius: calc(var(--ps-r) - 4px); overflow: hidden; }
    .ps-med dl div { display: grid; grid-template-columns: minmax(110px, 40%) 1fr; font-size: 14px; }
    .ps-med dl div:nth-child(odd) { background: color-mix(in srgb, var(--ps-acc) 6%, transparent); }
    .ps-med dt { padding: 10px 12px; font-weight: 700; color: var(--ml-text, #111); }
    .ps-med dd { margin: 0; padding: 10px 12px; color: var(--ml-muted, #555); }
    /* Perguntas */
    .ps-faq details { border-bottom: 1px solid var(--ml-line, #eee); }
    .ps-faq summary { list-style: none; display: flex; align-items: center; gap: 10px; padding: 14px 2px; font-size: 15px; font-weight: 700; cursor: pointer; }
    .ps-faq summary::-webkit-details-marker { display: none; }
    .ps-faq summary::after { content: '+'; margin-left: auto; font-size: 22px; font-weight: 400; color: var(--ps-acc); transition: transform .2s; }
    .ps-faq details[open] summary::after { transform: rotate(45deg); }
    .ps-faq p { margin: 0 0 14px; font-size: 14px; line-height: 1.6; color: var(--ml-muted, #555); }
    /* Depoimentos */
    .ps-dep { display: flex; gap: 10px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 2px; }
    .ps-dep::-webkit-scrollbar { display: none; }
    .ps-dep figure { flex: 0 0 84%; scroll-snap-align: start; margin: 0; padding: 16px; border-radius: calc(var(--ps-r) - 4px); border: 1px solid var(--ml-line, #eee); background: var(--ml-card, #fff); display: flex; flex-direction: column; gap: 10px; }
    .ps-dep blockquote { margin: 0; font-size: 15px; line-height: 1.55; }
    .ps-dep blockquote::before { content: '“'; display: block; font-size: 40px; line-height: .6; color: var(--ps-acc); font-family: Georgia, serif; }
    .ps-dep figcaption { margin-top: auto; font-size: 13px; font-weight: 700; color: var(--ml-muted, #555); }
    .ps-dep figcaption span { color: #f5b301; letter-spacing: 1px; margin-right: 6px; }
    /* Contagem */
    .ps-cd { text-align: center; color: #fff; background: linear-gradient(135deg, var(--c1), color-mix(in srgb, var(--c1) 60%, #000)); }
    .ps-cd h2.ps-h { color: #fff; text-align: center; }
    .ps-cd-n { display: flex; justify-content: center; gap: 8px; margin: 4px 0 12px; }
    .ps-cd-n div { min-width: 66px; padding: 10px 6px; border-radius: 12px; background: rgba(255,255,255,.16); }
    .ps-cd-n b { display: block; font-size: 30px; font-weight: 900; line-height: 1; font-variant-numeric: tabular-nums; }
    .ps-cd-n small { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }
    .ps-cd p { margin: 0; font-size: 14px; opacity: .9; }
    /* Chamada */
    .ps-cta { text-align: center; display: grid; gap: 10px; justify-items: center; padding: 28px 16px; background: color-mix(in srgb, var(--c1) 9%, var(--ps-bg, var(--ml-card, #fff))); }
    .ps-cta .ps-btn { background: var(--c1); }
    .ps-cta h2.ps-h { margin: 0; text-align: center; }
    /* Espaço */
    .ps-gap { background: transparent !important; box-shadow: none !important; padding: 0; }
    .ps-gap.s { height: 8px; } .ps-gap.m { height: 24px; } .ps-gap.l { height: 48px; }
    .ps-gap.line { display: flex; align-items: center; padding: 0 16px; }
    .ps-gap.line::before { content: ''; flex: 1; height: 1px; background: var(--ml-line, #ddd); }

    /* No topo e no celular o banner fica baixo, para o nome e a foto do produto aparecerem logo. */
    .ps.top .ps-ban { padding: 16px; gap: 8px; }
    .ps.top .ps-ban h2 { font-size: calc(22px * var(--ps-t)); }
    .ps.top .ps-ban-img img { max-height: 140px; }
    @container ps (min-width: 640px) {
        .ps-sec { padding: 28px 28px; }
        .ps-ban { grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); padding: 34px 36px; min-height: 280px; }
        .ps-ban.esq .ps-ban-img { order: -1; }
        .ps-ban.fundo { grid-template-columns: minmax(0, 1fr); min-height: 320px; }
        .ps-ban.fundo .ps-ban-txt { max-width: 560px; }
        .ps-ban h2 { font-size: calc(36px * var(--ps-t)); }
        .ps-ban-img img { max-height: 320px; }
        .ps-ft { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; }
        .ps-ft.dir .ps-ft-img { order: 2; }
        .ps-dest { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .ps-gal { grid-template-columns: repeat(var(--cols, 3), minmax(0, 1fr)); }
        .ps-med.img { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 28px; }
        .ps-dep figure { flex-basis: calc((100% - 20px) / 3); }
        .ps h2.ps-h { font-size: calc(24px * var(--ps-t)); }
        .ps-cd-n div { min-width: 86px; }
        .ps-cd-n b { font-size: 40px; }
    }
</style>
<script>
    // Contagem regressiva dos blocos da página especial.
    function psCount(end) {
        return {
            d: '0', h: '00', m: '00', s: '00', over: false,
            init() { this.tick(); this.t = setInterval(() => this.tick(), 1000); },
            destroy() { clearInterval(this.t); },
            tick() {
                const ms = new Date(end).getTime() - Date.now();
                if (! end || isNaN(ms) || ms <= 0) { this.over = !! end && ! isNaN(ms); this.d = '0'; this.h = this.m = this.s = '00'; return; }
                const p = (n) => String(n).padStart(2, '0');
                this.d = String(Math.floor(ms / 864e5)); this.h = p(Math.floor(ms / 36e5) % 24); this.m = p(Math.floor(ms / 6e4) % 60); this.s = p(Math.floor(ms / 1e3) % 60);
            },
        };
    }
</script>
