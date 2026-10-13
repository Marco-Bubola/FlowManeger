{{-- Estilo das listas das telas de cliente (Resumo, Faturas, Transferências) --}}
<style>
    .crs-card { border-radius: 1rem; border: 1px solid rgb(226 232 240 / .8); background: #fff; box-shadow: 0 1px 2px rgb(15 23 42 / .05); overflow: hidden; min-width: 0; }
    .dark .crs-card { border-color: rgb(51 65 85 / .7); background: rgb(15 23 42 / .8); }
    .crs-head { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-bottom: 1px solid rgb(241 245 249); }
    .dark .crs-head { border-bottom-color: rgb(30 41 59); }
    .crs-head-icon { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; box-shadow: 0 6px 14px -8px rgb(15 23 42 / .5); }
    .crs-title { font-weight: 800; font-size: .95rem; color: rgb(15 23 42); display: flex; align-items: center; gap: .4rem; }
    .dark .crs-title { color: #fff; }
    .crs-count { font-size: .7rem; font-weight: 700; padding: .05rem .45rem; border-radius: 999px; background: rgb(241 245 249); color: rgb(71 85 105); }
    .dark .crs-count { background: rgb(30 41 59); color: rgb(148 163 184); }
    .crs-sub { font-size: .72rem; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .crs-link { flex-shrink: 0; display: inline-flex; align-items: center; gap: .2rem; font-size: .75rem; font-weight: 700; color: rgb(79 70 229); padding: .35rem .6rem; border-radius: .6rem; background: rgb(238 242 255); }
    .dark .crs-link { color: rgb(165 180 252); background: rgb(49 46 129 / .35); }
    .crs-list > * + * { border-top: 1px solid rgb(241 245 249); }
    .dark .crs-list > * + * { border-top-color: rgb(30 41 59); }
    .crs-row { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; }
    .crs-row-link:hover { background: rgb(248 250 252); }
    .dark .crs-row-link:hover { background: rgb(30 41 59 / .5); }
    .crs-row-icon { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .7rem; display: flex; align-items: center; justify-content: center; font-size: .95rem; }
    .crs-row-title { font-size: .875rem; font-weight: 600; color: rgb(15 23 42); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dark .crs-row-title { color: rgb(241 245 249); }
    .crs-row-meta { font-size: .72rem; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .crs-row-value { flex-shrink: 0; font-size: .875rem; font-weight: 800; white-space: nowrap; }
    .crs-row-value:not([class*="text-"]) { color: rgb(15 23 42); }
    .dark .crs-row-value:not([class*="text-"]) { color: #fff; }
    .crs-split { display: inline-flex; align-items: center; gap: .35rem; font-size: .68rem; font-weight: 700; padding: .2rem .5rem; border-radius: 999px; color: rgb(100 116 139); background: rgb(241 245 249); }
    .crs-split-dot { width: .5rem; height: .5rem; border-radius: 999px; background: rgb(148 163 184); }
    .crs-split.is-on { color: rgb(67 56 202); background: rgb(224 231 255); }
    .crs-split.is-on .crs-split-dot { background: rgb(79 70 229); }
    .dark .crs-split { background: rgb(30 41 59); color: rgb(148 163 184); }
    .dark .crs-split.is-on { background: rgb(49 46 129 / .5); color: rgb(199 210 254); }
    .crs-empty { display: flex; flex-direction: column; align-items: center; gap: .4rem; padding: 2rem 1rem; color: rgb(148 163 184); font-size: .85rem; text-align: center; }
    .crs-empty i { font-size: 1.75rem; opacity: .6; }
    .crs-pager { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .6rem 1rem; border-top: 1px solid rgb(241 245 249); font-size: .75rem; font-weight: 600; color: rgb(100 116 139); }
    .dark .crs-pager { border-top-color: rgb(30 41 59); }
    .crs-pager button { width: 2rem; height: 2rem; border-radius: .6rem; display: flex; align-items: center; justify-content: center; background: rgb(241 245 249); color: rgb(51 65 85); }
    .dark .crs-pager button { background: rgb(30 41 59); color: rgb(226 232 240); }
    .crs-pager button:disabled { opacity: .4; }
    @media (max-width: 640px) { .crs-sub { display: none; } }
    /* Lista em colunas (telas largas) e números do topo em 3 colunas, também no celular */
    .crs-list.is-grid { display: grid; grid-template-columns: minmax(0, 1fr); }
    .crs-list.is-grid > * + * { border-top: 0; }
    .crs-list.is-grid > * { border-bottom: 1px solid rgb(241 245 249); }
    .dark .crs-list.is-grid > * { border-bottom-color: rgb(30 41 59); }
    @media (min-width: 900px) { .crs-list.is-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .crs-list.is-grid > *:nth-child(odd) { border-right: 1px solid rgb(241 245 249); } .dark .crs-list.is-grid > *:nth-child(odd) { border-right-color: rgb(30 41 59); } }
    .crs-badge { font-size: .66rem; font-weight: 700; padding: .15rem .45rem; border-radius: 999px; white-space: nowrap; }
    .cmx { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; }
    .cmx-item { display: flex; flex-direction: column; gap: .15rem; min-width: 0; text-align: left; padding: .7rem .8rem; border-radius: 1rem; border: 1px solid rgb(226 232 240 / .8); background: #fff; box-shadow: 0 1px 2px rgb(15 23 42 / .05); transition: border-color .15s, box-shadow .15s; }
    .dark .cmx-item { border-color: rgb(51 65 85 / .7); background: rgb(15 23 42 / .8); }
    button.cmx-item:hover { border-color: rgb(165 180 252); }
    .cmx-item.is-active { border-color: rgb(99 102 241); box-shadow: 0 0 0 3px rgb(99 102 241 / .15); }
    .cmx-label { display: flex; align-items: center; gap: .35rem; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cmx-label i { font-size: .8rem; }
    .cmx-value { font-size: 1.25rem; font-weight: 900; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cmx-value:not([class*="text-"]) { color: rgb(15 23 42); }
    .dark .cmx-value:not([class*="text-"]) { color: #fff; }
    .cmx-hint { font-size: .68rem; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    @media (max-width: 640px) { .cmx { gap: .4rem; } .cmx-item { padding: .55rem .6rem; border-radius: .85rem; } .cmx-value { font-size: .9rem; } .cmx-label { font-size: .6rem; } .cmx-hint { display: none; } }
</style>
