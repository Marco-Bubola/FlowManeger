{{-- Estilo do CRM: Histórico do cliente e Tarefas --}}
<style>
    .cmx.cmx-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 768px) { .cmx.cmx-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .cmx-value.crm-small { font-size: 1rem; }
    .crm-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-items: start; }
    @media (min-width: 1024px) { .crm-grid { grid-template-columns: minmax(320px, 5fr) minmax(0, 7fr); } .crm-side { position: sticky; top: 1rem; } }
    @media (min-width: 2200px) { .crm-grid { grid-template-columns: minmax(380px, 4fr) minmax(0, 8fr); } }
    .crm-btn { display: inline-flex; align-items: center; gap: .4rem; height: 2.5rem; padding: 0 .9rem; border-radius: .8rem; font-size: .85rem; font-weight: 700; color: rgb(51 65 85); background: rgb(255 255 255 / .9); border: 1px solid rgb(226 232 240); transition: background .15s, border-color .15s; white-space: nowrap; }
    .crm-btn:hover { border-color: rgb(165 180 252); background: #fff; }
    .dark .crm-btn { color: rgb(226 232 240); background: rgb(15 23 42 / .8); border-color: rgb(51 65 85); }
    .crm-btn-primary { color: #fff !important; border: 0; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)) !important; box-shadow: 0 8px 18px -10px rgb(79 70 229 / .8); }
    .crm-btn-primary:disabled { opacity: .6; }
    .crm-btn-wa { color: #fff !important; border: 0; background: #16a34a !important; }
    .crm-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
    .crm-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .35rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 700; color: rgb(71 85 105); background: rgb(241 245 249); border: 1px solid transparent; white-space: nowrap; transition: background .15s; }
    .crm-chip:hover { background: rgb(226 232 240); }
    .crm-chip.is-on { color: rgb(67 56 202); background: rgb(224 231 255); border-color: rgb(165 180 252); }
    .dark .crm-chip { color: rgb(203 213 225); background: rgb(30 41 59); }
    .dark .crm-chip.is-on { color: rgb(199 210 254); background: rgb(49 46 129 / .55); border-color: rgb(99 102 241 / .6); }
    .crm-input { width: 100%; border-radius: .75rem; border: 1px solid rgb(226 232 240); background: #fff; padding: .55rem .75rem; font-size: .9rem; color: rgb(15 23 42); }
    .crm-input:focus { outline: none; border-color: rgb(129 140 248); box-shadow: 0 0 0 3px rgb(99 102 241 / .15); }
    .dark .crm-input { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(241 245 249); color-scheme: dark; }
    textarea.crm-input { resize: vertical; min-height: 5.5rem; }
    .crm-label { display: flex; flex-direction: column; gap: .25rem; font-size: .72rem; font-weight: 700; color: rgb(100 116 139); }
    .crm-err { margin-top: .25rem; font-size: .75rem; font-weight: 600; color: rgb(225 29 72); }
    .crm-task-box { border-radius: .9rem; border: 1px dashed rgb(203 213 225); padding: .7rem .8rem; }
    .crm-task-box.is-open { border-style: solid; border-color: rgb(253 230 138); background: rgb(255 251 235 / .6); }
    .dark .crm-task-box { border-color: rgb(51 65 85); }
    .dark .crm-task-box.is-open { border-color: rgb(146 64 14 / .6); background: rgb(69 26 3 / .25); }
    .crm-check { width: 2rem; height: 2rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 999px; font-size: 1.25rem; color: rgb(148 163 184); transition: color .15s; }
    .crm-check:hover, .crm-check.is-done { color: rgb(16 185 129); }
    .crm-icon-btn { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .6rem; color: rgb(148 163 184); }
    .crm-icon-btn:hover { background: rgb(241 245 249); color: rgb(79 70 229); }
    .dark .crm-icon-btn:hover { background: rgb(30 41 59); }
    .crm-tl-day { position: sticky; top: 0; z-index: 1; margin: .5rem 0 .35rem; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: rgb(100 116 139); }
    .crm-tl-day:first-child { margin-top: 0; }
    .crm-tl-item { position: relative; display: flex; gap: .75rem; padding: .45rem 0 .45rem 0; }
    .crm-tl-item::before { content: ''; position: absolute; left: 1.05rem; top: 2.6rem; bottom: -.45rem; width: 2px; background: rgb(226 232 240); }
    .dark .crm-tl-item::before { background: rgb(30 41 59); }
    .crm-tl-item:has(+ .crm-tl-day)::before, .crm-tl-item:last-of-type::before { display: none; }
    .crm-tl-dot { width: 2.15rem; height: 2.15rem; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
    .crm-tl-body { min-width: 0; flex: 1; padding-top: .1rem; }
    .crm-tl-title { font-size: .875rem; font-weight: 700; color: rgb(15 23 42); }
    .dark .crm-tl-title { color: rgb(241 245 249); }
    .crm-tl-time { margin-left: .35rem; font-size: .7rem; font-weight: 600; color: rgb(148 163 184); }
    .crm-tl-detail { font-size: .8rem; color: rgb(71 85 105); overflow-wrap: anywhere; }
    .dark .crm-tl-detail { color: rgb(148 163 184); }
    .crm-tl-detail.is-note { margin-top: .25rem; white-space: pre-line; padding: .5rem .65rem; border-radius: .7rem; background: rgb(248 250 252); color: rgb(30 41 59); }
    .dark .crm-tl-detail.is-note { background: rgb(30 41 59 / .7); color: rgb(226 232 240); }
    .crm-task-row .crs-row-title, .crm-side .crs-row-title { white-space: normal; overflow-wrap: anywhere; }
    .crm-task-row.is-done .crs-row-title { text-decoration: line-through; color: rgb(148 163 184); }
    @media (min-width: 1024px) { .crm-grid.crm-grid-single { grid-template-columns: minmax(0, 1fr); } }
    .crm-tasks-grid { grid-template-columns: minmax(0, 1fr) !important; }
    .crm-tasks-grid > .crs-empty { grid-column: 1 / -1; }
    @media (min-width: 1280px) { .crm-tasks-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; } }
    @media (min-width: 2200px) { .crm-tasks-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; } .crm-tasks-grid > *:nth-child(odd) { border-right: 0; } .crm-tasks-grid > *:not(:nth-child(3n)) { border-right: 1px solid rgb(241 245 249); } }
    .crm-wa-ready { background: rgb(220 252 231); }
    .dark .crm-wa-ready { background: rgb(20 83 45 / .5); }
    .crm-menu-item { width: 100%; display: flex; align-items: center; gap: .5rem; padding: .5rem .8rem; text-align: left; color: rgb(51 65 85); }
    .crm-menu-item:hover { background: rgb(248 250 252); }
    .dark .crm-menu-item { color: rgb(226 232 240); }
    .dark .crm-menu-item:hover { background: rgb(30 41 59); }
    @media (max-width: 640px) { .crm-btn { height: 2.4rem; padding: 0 .75rem; } }
</style>
