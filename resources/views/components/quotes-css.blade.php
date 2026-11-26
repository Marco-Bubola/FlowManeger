{{-- Estilo da área de Orçamentos (usa também x-client-rows-css e x-crm-css) --}}
<x-client-rows-css />
<x-crm-css />
<style>
    .qt-page { padding-bottom: 6rem; }
    .qt-tone { --t: #6366f1; color: var(--t); background: color-mix(in srgb, var(--t) 13%, transparent); }
    .qt-pill { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 800; padding: .2rem .55rem; border-radius: 999px; white-space: nowrap; }
    .qt-tabs { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .25rem; scrollbar-width: none; }
    .qt-tabs::-webkit-scrollbar { display: none; }
    .qt-tab { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .8rem; border-radius: 999px; font-size: .8rem; font-weight: 700; color: rgb(71 85 105); background: #fff; border: 1px solid rgb(226 232 240); white-space: nowrap; }
    .qt-tab b { font-size: .7rem; padding: .05rem .4rem; border-radius: 999px; background: rgb(241 245 249); color: rgb(71 85 105); }
    .qt-tab.is-on { color: #fff; border-color: transparent; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); }
    .qt-tab.is-on b { background: rgb(255 255 255 / .25); color: #fff; }
    .dark .qt-tab { background: rgb(15 23 42 / .8); border-color: rgb(51 65 85); color: rgb(203 213 225); }
    .dark .qt-tab b { background: rgb(30 41 59); color: rgb(203 213 225); }
    .qt-grid { display: grid; gap: .85rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 700px) { .qt-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1280px) { .qt-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (min-width: 2200px) { .qt-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
    .qt-card { position: relative; display: flex; flex-direction: column; border-radius: 1.1rem; border: 1px solid rgb(226 232 240 / .85); background: #fff; box-shadow: 0 1px 2px rgb(15 23 42 / .05); overflow: hidden; transition: border-color .15s, box-shadow .15s, transform .15s; }
    .qt-card:hover { border-color: rgb(165 180 252); box-shadow: 0 12px 30px -18px rgb(79 70 229 / .45); }
    .dark .qt-card { border-color: rgb(51 65 85 / .7); background: rgb(15 23 42 / .85); }
    .qt-card-top { display: flex; gap: .75rem; padding: .85rem .9rem .6rem; }
    .qt-thumb { position: relative; width: 4.25rem; height: 4.25rem; flex-shrink: 0; border-radius: .9rem; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .qt-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .qt-code { font-size: .68rem; font-weight: 800; letter-spacing: .04em; color: rgb(100 116 139); }
    .qt-title { font-size: .95rem; font-weight: 800; color: rgb(15 23 42); line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .dark .qt-title { color: #fff; }
    .qt-who { font-size: .78rem; color: rgb(71 85 105); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dark .qt-who { color: rgb(148 163 184); }
    .qt-card-foot { margin-top: auto; display: flex; align-items: center; gap: .5rem; padding: .6rem .9rem; border-top: 1px solid rgb(241 245 249); }
    .dark .qt-card-foot { border-top-color: rgb(30 41 59); }
    .qt-total { font-size: 1.05rem; font-weight: 900; color: rgb(15 23 42); }
    .dark .qt-total { color: #fff; }
    .qt-muted { font-size: .72rem; color: rgb(100 116 139); }
    .qt-alert { display: flex; align-items: flex-start; gap: .5rem; padding: .55rem .75rem; border-radius: .8rem; font-size: .78rem; font-weight: 600; }
    .qt-alert.is-warn { background: rgb(255 251 235); color: rgb(146 64 14); border: 1px solid rgb(253 230 138); }
    .qt-alert.is-info { background: rgb(238 242 255); color: rgb(55 48 163); border: 1px solid rgb(199 210 254); }
    .qt-alert.is-ok { background: rgb(236 253 245); color: rgb(6 95 70); border: 1px solid rgb(167 243 208); }
    .qt-alert.is-bad { background: rgb(255 241 242); color: rgb(159 18 57); border: 1px solid rgb(254 205 211); }
    .dark .qt-alert.is-warn { background: rgb(69 26 3 / .4); color: rgb(253 230 138); border-color: rgb(146 64 14 / .6); }
    .dark .qt-alert.is-info { background: rgb(49 46 129 / .35); color: rgb(199 210 254); border-color: rgb(67 56 202 / .5); }
    .dark .qt-alert.is-ok { background: rgb(6 78 59 / .35); color: rgb(167 243 208); border-color: rgb(6 95 70 / .6); }
    .dark .qt-alert.is-bad { background: rgb(76 5 25 / .4); color: rgb(254 205 211); border-color: rgb(159 18 57 / .6); }
    /* Tabela de materiais */
    .qt-mat-row { display: grid; grid-template-columns: minmax(0, 1fr) 5.5rem 7rem 2rem; gap: .5rem; align-items: center; padding: .45rem 1rem; }
    @media (max-width: 640px) {
        .qt-mat-row { grid-template-columns: 5.5rem minmax(0, 1fr) 2rem; row-gap: .3rem; }
        .qt-mat-row > :first-child { grid-column: 1 / -1; }
        .qt-mat-row.qt-head-row > :first-child { display: none; }
    }
    @media (min-width: 1024px) { .qt-mat-row { grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.6fr) 6rem 7.5rem 2rem; } }
    .qt-mat-note { display: none; }
    @media (min-width: 1024px) { .qt-mat-note { display: block; } }
    .qt-cell { width: 100%; border-radius: .6rem; border: 1px solid transparent; background: transparent; padding: .35rem .5rem; font-size: .85rem; color: rgb(15 23 42); }
    .qt-cell:hover { border-color: rgb(226 232 240); }
    .qt-cell:focus { outline: none; border-color: rgb(129 140 248); background: #fff; box-shadow: 0 0 0 3px rgb(99 102 241 / .15); }
    .dark .qt-cell { color: rgb(241 245 249); color-scheme: dark; }
    .dark .qt-cell:hover { border-color: rgb(51 65 85); }
    .dark .qt-cell:focus { background: rgb(15 23 42); }
    .qt-cell.is-money { text-align: right; font-weight: 800; }
    .qt-cell.is-est { color: rgb(180 83 9); }
    .dark .qt-cell.is-est { color: rgb(252 211 77); }
    .qt-head-row { font-size: .64rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: rgb(148 163 184); }
    /* Editor */
    .qt-editor { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
    @media (min-width: 1280px) { .qt-editor { grid-template-columns: minmax(0, 1fr) 360px; } .qt-side { position: sticky; top: 1rem; } }
    @media (min-width: 2200px) { .qt-editor { grid-template-columns: minmax(0, 1fr) 420px; } }
    .qt-sec { padding: .9rem 1rem; display: flex; flex-direction: column; gap: .75rem; }
    .qt-2 { display: grid; gap: .6rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 640px) { .qt-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .qt-3 { display: grid; gap: .6rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 900px) { .qt-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .qt-seg { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem; }
    .qt-seg button { display: flex; align-items: center; gap: .55rem; padding: .65rem .75rem; border-radius: .9rem; border: 1px solid rgb(226 232 240); background: #fff; text-align: left; }
    .qt-seg button.is-on { border-color: rgb(99 102 241); box-shadow: 0 0 0 3px rgb(99 102 241 / .15); }
    .dark .qt-seg button { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .qt-seg-t { font-size: .85rem; font-weight: 800; color: rgb(15 23 42); }
    .dark .qt-seg-t { color: #fff; }
    .qt-item { border-radius: 1rem; border: 1px solid rgb(226 232 240); background: rgb(248 250 252 / .6); overflow: hidden; }
    .dark .qt-item { border-color: rgb(51 65 85); background: rgb(30 41 59 / .35); }
    .qt-item-head { display: flex; align-items: center; gap: .5rem; padding: .6rem .75rem; background: #fff; border-bottom: 1px solid rgb(241 245 249); }
    .dark .qt-item-head { background: rgb(15 23 42 / .7); border-bottom-color: rgb(30 41 59); }
    .qt-num { width: 1.6rem; height: 1.6rem; border-radius: .55rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 900; color: #fff; background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); }
    .qt-cost { border-radius: .85rem; border: 1px dashed rgb(203 213 225); background: #fff; }
    .dark .qt-cost { border-color: rgb(71 85 105); background: rgb(15 23 42 / .6); }
    .qt-cost-row { display: grid; grid-template-columns: minmax(0, 1fr) 4.2rem 5.6rem 5.4rem 1.8rem; gap: .35rem; align-items: center; padding: .3rem .5rem; }
    @media (max-width: 639px) { .qt-hide-sm { display: none !important; } }
    /* Celular: o nome ocupa a linha toda (não corta) e qtd./valor ficam embaixo. */
    @media (max-width: 640px) {
        .qt-cost-row { grid-template-columns: 4.6rem minmax(0, 1fr) 1.8rem; row-gap: .25rem; }
        .qt-cost-row > :first-child { grid-column: 1 / -1; }
        .qt-cost-row .qt-line-total { display: none; }
        .qt-cost-row.qt-head-row > :first-child { display: none; }
    }
    .qt-cost-row + .qt-cost-row { border-top: 1px solid rgb(241 245 249); }
    .dark .qt-cost-row + .qt-cost-row { border-top-color: rgb(30 41 59); }
    .qt-price-box { display: grid; gap: .5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: .75rem; border-radius: .9rem; background: linear-gradient(135deg, rgb(238 242 255), rgb(245 243 255)); }
    @media (min-width: 900px) { .qt-price-box { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .dark .qt-price-box { background: linear-gradient(135deg, rgb(49 46 129 / .35), rgb(59 7 100 / .3)); }
    .qt-kv { display: flex; flex-direction: column; gap: .1rem; min-width: 0; }
    .qt-kv span { font-size: .64rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); }
    .qt-kv b { font-size: 1rem; font-weight: 900; color: rgb(15 23 42); white-space: nowrap; }
    .dark .qt-kv b { color: #fff; }
    .qt-photos { display: flex; flex-wrap: wrap; gap: .5rem; }
    .qt-photo { position: relative; width: 4.5rem; height: 4.5rem; border-radius: .8rem; overflow: hidden; border: 1px solid rgb(226 232 240); background: rgb(241 245 249); }
    .qt-photo img { width: 100%; height: 100%; object-fit: cover; }
    .qt-photo button { position: absolute; top: .2rem; right: .2rem; width: 1.4rem; height: 1.4rem; border-radius: 999px; background: rgb(15 23 42 / .7); color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center; }
    .qt-photo-add { width: 4.5rem; height: 4.5rem; border-radius: .8rem; border: 2px dashed rgb(165 180 252); color: rgb(99 102 241); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .1rem; font-size: .65rem; font-weight: 800; cursor: pointer; background: rgb(238 242 255 / .5); }
    .dark .qt-photo-add { background: rgb(49 46 129 / .25); border-color: rgb(99 102 241 / .6); color: rgb(165 180 252); }
    .qt-sum-row { display: flex; align-items: center; justify-content: space-between; gap: .5rem; font-size: .85rem; color: rgb(71 85 105); }
    .dark .qt-sum-row { color: rgb(148 163 184); }
    .qt-sum-row b { color: rgb(15 23 42); }
    .dark .qt-sum-row b { color: #fff; }
    .qt-sum-total { font-size: 1.5rem; font-weight: 900; color: rgb(15 23 42); }
    .dark .qt-sum-total { color: #fff; }
    .qt-private { display: inline-flex; align-items: center; gap: .25rem; font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: rgb(190 18 60); background: rgb(255 228 230); padding: .1rem .4rem; border-radius: 999px; }
    .dark .qt-private { background: rgb(76 5 25 / .5); color: rgb(253 164 175); }
    .qt-pick { position: absolute; z-index: 30; left: 0; right: 0; margin-top: .25rem; max-height: 18rem; overflow-y: auto; border-radius: .9rem; border: 1px solid rgb(226 232 240); background: #fff; box-shadow: 0 20px 40px -20px rgb(15 23 42 / .35); }
    .dark .qt-pick { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .qt-pick button { width: 100%; display: flex; align-items: center; gap: .5rem; padding: .5rem .75rem; text-align: left; font-size: .82rem; }
    .qt-pick button:hover { background: rgb(238 242 255); }
    .dark .qt-pick button:hover { background: rgb(30 41 59); }
    .qt-pick-cat { position: sticky; top: 0; padding: .35rem .75rem; font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: rgb(100 116 139); background: rgb(248 250 252); }
    .dark .qt-pick-cat { background: rgb(30 41 59); }
    .qt-mobile-bar { position: fixed; z-index: 40; left: 0; right: 0; bottom: 0; display: flex; align-items: center; gap: .6rem; padding: .65rem 1rem calc(.65rem + env(safe-area-inset-bottom)); background: rgb(255 255 255 / .96); backdrop-filter: blur(12px); border-top: 1px solid rgb(226 232 240); }
    .dark .qt-mobile-bar { background: rgb(15 23 42 / .96); border-top-color: rgb(30 41 59); }
    @media (min-width: 1280px) { .qt-mobile-bar { display: none; } }
    .qt-tl { display: flex; flex-direction: column; gap: .1rem; }
    .qt-tl-item { display: flex; gap: .6rem; padding: .35rem 0; font-size: .8rem; color: rgb(51 65 85); }
    .dark .qt-tl-item { color: rgb(203 213 225); }
    .qt-tl-dot { width: 1.75rem; height: 1.75rem; border-radius: 999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .78rem; }

    .qt-card-visible { overflow: visible !important; position: relative; z-index: 5; }
    /* ===== Editor: tipo + cliente lado a lado no computador ===== */
    .qt-tc { display: grid; gap: .75rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 1024px) { .qt-tc { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
    .qt-tc-kind, .qt-tc-client { display: flex; flex-direction: column; gap: .35rem; min-width: 0; }
    .qt-tc .qt-seg > button { min-height: 3.6rem; }
    .qt-tc-row { display: grid; gap: .6rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 640px) { .qt-tc-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } .qt-tc-row .qt-tc-title { grid-column: 1 / -1; } .qt-tc-row.has-client { grid-template-columns: minmax(0, 1fr); } }
    @media (min-width: 1024px) { .qt-tc-row { grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr) minmax(0, 1.4fr); } .qt-tc-row .qt-tc-title { grid-column: auto; } }

    .qt-dd { width: 100%; min-height: 3.6rem; display: flex; align-items: center; gap: .65rem; padding: .5rem .75rem; border-radius: .9rem; border: 1px solid rgb(226 232 240); background: #fff; font-size: .86rem; color: rgb(30 41 59); transition: border-color .15s, box-shadow .15s; }
    .qt-dd:hover, .qt-dd:focus-visible { border-color: rgb(165 180 252); box-shadow: 0 0 0 3px rgb(99 102 241 / .12); outline: none; }
    .qt-dd.is-set { border-color: rgb(199 210 254); background: rgb(238 242 255 / .7); }
    .dark .qt-dd { background: rgb(15 23 42 / .7); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .dark .qt-dd.is-set { background: rgb(99 102 241 / .12); border-color: rgb(99 102 241 / .45); }
    .qt-dd-av { flex-shrink: 0; width: 2.1rem; height: 2.1rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .85rem; color: #fff; background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); }
    .qt-dd-menu { position: absolute; z-index: 40; left: 0; right: 0; margin-top: .35rem; border-radius: .9rem; border: 1px solid rgb(226 232 240); background: #fff; box-shadow: 0 24px 48px -20px rgb(15 23 42 / .4); display: flex; flex-direction: column; max-height: 26rem; overflow: hidden; }
    .dark .qt-dd-menu { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .qt-dd-list { flex: 1; min-height: 0; overflow-y: auto; padding: .25rem 0; }
    .qt-dd-list button { width: 100%; display: flex; align-items: center; gap: .6rem; padding: .45rem .8rem; text-align: left; font-size: .85rem; color: rgb(51 65 85); }
    .qt-dd-list button:hover { background: rgb(248 250 252); }
    .dark .qt-dd-list button { color: rgb(226 232 240); }
    .dark .qt-dd-list button:hover { background: rgb(30 41 59); }
    .qt-dd-list .qt-dd-av { width: 1.9rem; height: 1.9rem; font-size: .78rem; }
    .qt-dd-foot { flex-shrink: 0; border-top: 1px solid rgb(241 245 249); padding: .5rem; background: rgb(248 250 252 / .8); }
    .dark .qt-dd-foot { border-top-color: rgb(30 41 59); background: rgb(2 6 23 / .4); }
    .qt-dd-new { width: 100%; display: flex; align-items: center; gap: .5rem; padding: .45rem .5rem; border-radius: .7rem; font-size: .85rem; font-weight: 700; color: rgb(4 120 87); }
    .qt-dd-new:hover { background: rgb(16 185 129 / .1); }
    .qt-dd-new > span { width: 1.75rem; height: 1.75rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: rgb(16 185 129 / .15); font-size: .75rem; }
    .dark .qt-dd-new { color: rgb(110 231 183); }

    /* Reforma: serviços e "a peça" na mesma linha no computador */
    @media (min-width: 1024px) { .qt-ref { flex-direction: row !important; align-items: center; justify-content: space-between; } .qt-ref > .crm-chips:last-child { flex-shrink: 0; } }

    /* Item: quantidade estreita; descrição e fotos lado a lado */
    @media (min-width: 1024px) {
        .qt-item-f { grid-template-columns: 7rem minmax(0, 1fr) minmax(0, 1fr) !important; }
        .qt-item-dp { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .75rem; align-items: end; }
        .qt-item-dp .qt-photos { max-width: 22rem; justify-content: flex-end; }
    }
    .qt-item-dp { display: flex; flex-direction: column; gap: .75rem; }
    @media (min-width: 1024px) { .qt-item-dp { display: grid; } }

    /* Prazo e pagamento: tudo numa linha no computador; observações lado a lado */
    @media (min-width: 1280px) { .qt-terms { grid-template-columns: 8rem 10rem minmax(0, 1fr) 8rem 9rem !important; } .qt-terms > .col-span-2 { grid-column: auto; } }
    @media (min-width: 1024px) { .qt-notes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    /* Criar venda a partir do orçamento */
    .qt-sale-box { margin-top: .5rem; border-radius: .9rem; border: 1px solid rgb(167 243 208); background: rgb(236 253 245 / .8); padding: .7rem; display: flex; flex-direction: column; gap: .55rem; }
    .qt-sale-box p { display: flex; gap: .5rem; font-size: .78rem; color: rgb(6 95 70); line-height: 1.35; }
    .qt-sale-box p i { color: rgb(16 185 129); font-size: 1rem; }
    .dark .qt-sale-box { background: rgb(6 78 59 / .25); border-color: rgb(6 95 70 / .6); }
    .dark .qt-sale-box p { color: rgb(167 243 208); }
    .qt-btn-sale { color: #fff !important; border: 0; background: linear-gradient(90deg, rgb(16 185 129), rgb(5 150 105)) !important; box-shadow: 0 8px 18px -10px rgb(5 150 105 / .8); }
</style>
