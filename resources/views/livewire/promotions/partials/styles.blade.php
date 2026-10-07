{{-- Estilos comuns da área de Promoções (inline: o deploy não reenvia public/assets) --}}
<style>
    .promo-chip {
        display: inline-flex; align-items: center; gap: .35rem; padding: .4rem .75rem;
        border-radius: .75rem; font-size: .75rem; font-weight: 600; color: rgb(71 85 105);
        background: rgba(255,255,255,.8); border: 1px solid rgba(148,163,184,.4);
        transition: all .15s ease; white-space: nowrap;
    }
    .promo-chip:hover { border-color: rgba(244,63,94,.55); color: rgb(190 18 60); }
    .promo-chip-active, .promo-chip-max {
        background: linear-gradient(135deg, #f43f5e, #ec4899, #f97316) !important;
        color: #fff !important; border-color: transparent !important;
    }
    .dark .promo-chip { background: rgba(30,41,59,.7); color: rgb(203 213 225); border-color: rgba(71,85,105,.6); }

    .promo-tag {
        display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .55rem;
        border-radius: 999px; font-size: .66rem; font-weight: 800; color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.18); white-space: nowrap;
    }

    .promo-row {
        border-radius: 1rem; padding: .7rem; background: rgba(248,250,252,.9);
        border: 1px solid rgba(226,232,240,.9);
    }
    .dark .promo-row { background: rgba(30,41,59,.6); border-color: rgba(51,65,85,.8); }
    .promo-row-error { border-color: rgba(239,68,68,.7) !important; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }

    .promo-field { display: flex; flex-direction: column; gap: .15rem; min-width: 0; }
    .promo-field > span { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); }
    .promo-field input {
        width: 100%; min-width: 0; padding: .4rem .55rem; border-radius: .6rem; font-size: .82rem;
        background: #fff; border: 1px solid rgba(203,213,225,.9); color: rgb(30 41 59);
    }
    .promo-field input:focus { outline: none; border-color: #f43f5e; box-shadow: 0 0 0 3px rgba(244,63,94,.15); }
    .promo-field-main input { border-color: rgba(244,63,94,.45); }
    .dark .promo-field input { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }

    .promo-step {
        width: 2rem; height: 2rem; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
        border-radius: .7rem; font-size: .95rem; color: rgb(225 29 72); background: #fff;
        border: 1px solid rgba(244,63,94,.35); box-shadow: 0 1px 3px rgba(0,0,0,.06); transition: all .12s ease;
    }
    .promo-step:hover:not(:disabled) { background: linear-gradient(135deg, #f43f5e, #f97316); color: #fff; border-color: transparent; }
    .promo-step:active:not(:disabled) { transform: scale(.92); }
    .promo-step:disabled { opacity: .4; cursor: not-allowed; }
    .dark .promo-step { background: rgb(15 23 42); }

    .promo-off {
        min-width: 3.1rem; text-align: center; padding: .3rem .45rem; border-radius: .7rem; flex-shrink: 0;
        font-size: .82rem; font-weight: 900; color: #fff; background: linear-gradient(135deg, #f43f5e, #f97316);
    }

    .promo-range { -webkit-appearance: none; appearance: none; height: 6px; border-radius: 999px; cursor: pointer;
        background: linear-gradient(90deg, #f43f5e 0%, #f97316 var(--fill, 0%), rgba(203,213,225,.9) var(--fill, 0%)); }
    .dark .promo-range { background: linear-gradient(90deg, #f43f5e 0%, #f97316 var(--fill, 0%), rgb(51 65 85) var(--fill, 0%)); }
    .promo-range::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%;
        background: #fff; border: 3px solid #f43f5e; box-shadow: 0 1px 4px rgba(0,0,0,.25); }
    .promo-range::-moz-range-thumb { width: 14px; height: 14px; border-radius: 50%; background: #fff; border: 3px solid #f43f5e; }

    .promo-page-btn {
        width: 2.25rem; height: 2.25rem; display: inline-flex; align-items: center; justify-content: center;
        border-radius: .75rem; background: #fff; border: 1px solid rgba(203,213,225,.9); color: rgb(71 85 105);
    }
    .promo-page-btn:disabled { opacity: .4; }
    .dark .promo-page-btn { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(203 213 225); }

    .promo-quick::-webkit-scrollbar { display: none; }

    /* Toque confortável no celular */
    @media (max-width: 767.98px) {
        .promo-step { width: 2.4rem; height: 2.4rem; }
        .promo-field input { font-size: 16px; } /* evita zoom do iOS ao focar */
    }
</style>
