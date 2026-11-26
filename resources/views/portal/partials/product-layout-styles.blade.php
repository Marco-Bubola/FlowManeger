@once
{{-- Montagem da página do produto (Promoções › Páginas de produto › Montar a página). Classes ppl-* no .pp-main. --}}
<style>
    /* Nome */
    .pp-main.ppl-ts-p .pp-head h1 { font-size: 16px; }
    .pp-main.ppl-ts-g .pp-head h1 { font-size: 24px; font-weight: 700; letter-spacing: -.01em; }
    .pp-main.ppl-ta-centro .pp-head { text-align: center; }
    .pp-main.ppl-ta-centro .pp-head .pp-rate { justify-content: center; }

    /* Fotos */
    .pp-main.ppl-pr-retrato .ml-gal-track > div { aspect-ratio: 4 / 5; max-height: 68vh; }
    .pp-main.ppl-pr-paisagem .ml-gal-track > div { aspect-ratio: 4 / 3; }
    .pp-main.ppl-pf-preencher .ml-gal-track img { object-fit: cover; padding: 0; }
    .pp-main.ppl-pf-preencher .ml-gal-track > div { overflow: hidden; }
    .pp-main.ppl-no-count .ml-gal-n { display: none; }
    .pp-main.ppl-th-nada .ml-thumbs, .pp-main.ppl-th-nada .ml-dots { display: none !important; }

    /* Preço */
    .pp-main.ppl-pc-g .pp-info .ml-price { font-size: 44px; font-weight: 600; }
    .pp-main.ppl-pc-g .pp-info .ml-price sup { font-size: 20px; }
    .pp-main.ppl-pc-g .pp-info .ml-pct { font-size: 20px; }

    @media (max-width: 767px) {
        /* Nome abaixo da foto (no celular) */
        .pp-main.ppl-t-abaixo { display: flex; flex-direction: column; }
        .pp-main.ppl-t-abaixo .ml-gal-wrap { order: 1; }
        .pp-main.ppl-t-abaixo .pp-head { order: 2; padding-top: 12px; }
        .pp-main.ppl-t-abaixo .pp-info { order: 3; padding-top: 2px; }
    }

    @media (min-width: 768px) {
        .pp-main { --ppl-g: 1.25fr; grid-template-columns: minmax(0, var(--ppl-g)) minmax(0, 1fr); }
        .pp-main.ppl-pz-pequena { --ppl-g: 1fr; }
        .pp-main.ppl-pz-grande { --ppl-g: 1.7fr; }
        .pp-main.ppl-ps-direita { grid-template-areas: "head gal" "info gal"; grid-template-columns: minmax(0, 1fr) minmax(0, var(--ppl-g)); }
        .pp-main.ppl-ps-direita .pp-head, .pp-main.ppl-ps-direita .pp-info { padding-left: 24px; padding-right: 8px; }
        .pp-main.ppl-ts-p .pp-head h1 { font-size: 19px; }
        .pp-main.ppl-ts-m .pp-head h1 { font-size: 24px; }
        .pp-main.ppl-ts-g .pp-head h1 { font-size: 32px; line-height: 1.15; }
        .pp-main.ppl-pr-retrato .ml-gal-track > div { max-height: 640px; }
        .pp-main.ppl-pc-g .pp-info .ml-price { font-size: 48px; }

        /* Miniaturas do lado da foto */
        .pp-main.ppl-th-lado .ml-gal-wrap { display: grid; grid-template-columns: 74px minmax(0, 1fr); align-items: start; }
        .pp-main.ppl-th-lado .ml-gal { grid-column: 2; grid-row: 1; }
        .pp-main.ppl-th-lado .ml-thumbs { grid-column: 1; grid-row: 1; flex-direction: column; flex-wrap: nowrap; padding: 6px 0 0 12px; max-height: 520px; overflow-y: auto; scrollbar-width: none; }
        .pp-main.ppl-th-lado .ml-thumbs::-webkit-scrollbar { display: none; }
        .pp-main.ppl-th-lado .ml-thumbs button { flex-shrink: 0; }
    }
</style>
@endonce
