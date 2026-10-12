{{-- Estilos dos formulários de produto (ep-*): novo, editar e desmembrar kit --}}
    <style>
        .ep-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1200px) { .ep-grid { grid-template-columns: minmax(0, 1fr) 380px; align-items: start; } .ep-side { position: sticky; top: 1rem; } }
        .ep-col { display: grid; gap: 1rem; min-width: 0; }
        .ep-card { background: rgba(255,255,255,.92); border: 1px solid rgba(148,163,184,.28); border-radius: 22px; box-shadow: 0 8px 28px rgba(15,23,42,.06); }
        .dark .ep-card { background: rgba(15,23,42,.82); border-color: rgba(71,85,105,.5); }
        .ep-card-h { display: flex; align-items: center; gap: .65rem; padding: .85rem 1rem; border-bottom: 1px solid rgba(148,163,184,.2); }
        .dark .ep-card-h { border-color: rgba(71,85,105,.45); }
        .ep-card-h h3 { margin: 0; font-size: .95rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .dark .ep-card-h h3 { color: #f1f5f9; }
        .ep-card-h p { margin: .1rem 0 0; font-size: .72rem; color: #64748b; line-height: 1.3; }
        .ep-card-b { padding: 1rem; }
        .ep-ico { width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; background: linear-gradient(135deg, #6366f1, #a855f7); box-shadow: 0 6px 16px rgba(99,102,241,.25); }
        .ep-ico.green { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,.25); }
        .ep-ico.teal { background: linear-gradient(135deg, #14b8a6, #0ea5e9); box-shadow: 0 6px 16px rgba(14,165,233,.22); }
        .ep-ico.pink { background: linear-gradient(135deg, #ec4899, #a855f7); }
        .ep-ico.ml { background: #ffe600; color: #2d3277; box-shadow: 0 6px 16px rgba(234,179,8,.25); }
        .ep-label { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .35rem; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
        .ep-label .req { color: #f43f5e; }
        .ep-input { width: 100%; border-radius: .9rem; border: 1px solid rgba(148,163,184,.45); background: #f8fafc; padding: .65rem .85rem; font-size: .92rem; color: #0f172a; transition: border-color .15s, box-shadow .15s; }
        .ep-input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.15); background: #fff; }
        .dark .ep-input { background: rgba(30,41,59,.8); border-color: rgba(71,85,105,.7); color: #f1f5f9; }
        .dark .ep-input:focus { background: rgba(30,41,59,1); }
        .ep-input.is-err { border-color: #f43f5e; }
        .ep-err { margin: .3rem 0 0; font-size: .72rem; color: #f43f5e; }
        .ep-row { display: grid; gap: .85rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .ep-row.c2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ep-row.c3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 900px) { .ep-row.c4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } .ep-row.c5 { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (min-width: 640px) and (max-width: 899px) { .ep-row.c4, .ep-row.c5 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ep-row.fit { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
        .ep-btn { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; padding: .6rem 1rem; border-radius: .9rem; font-weight: 800; font-size: .86rem; transition: transform .15s, filter .15s; white-space: nowrap; }
        .ep-btn:active { transform: scale(.97); }
        .ep-btn-ml { color: #2d3277; background: #ffe600; box-shadow: 0 6px 16px rgba(234,179,8,.25); }
        .ep-btn-ml:hover { filter: brightness(.97); }
        .ep-btn-soft { color: #4f46e5; background: rgba(99,102,241,.1); }
        .dark .ep-btn-soft { color: #c7d2fe; background: rgba(99,102,241,.18); }
        .ep-btn-primary { color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); box-shadow: 0 8px 20px rgba(99,102,241,.3); }
        .ep-btn-ghost { color: #475569; background: transparent; border: 1px solid rgba(148,163,184,.45); }
        .dark .ep-btn-ghost { color: #cbd5e1; border-color: rgba(71,85,105,.7); }
        .ep-profit { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .9rem; }
        .ep-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .7rem; border-radius: 999px; font-size: .78rem; font-weight: 700; background: rgba(148,163,184,.14); color: #475569; }
        .dark .ep-pill { color: #cbd5e1; }
        .ep-pill.good { background: rgba(16,185,129,.12); color: #047857; }
        .dark .ep-pill.good { color: #6ee7b7; }
        .ep-pill.bad { background: rgba(244,63,94,.12); color: #be123c; }
        .dark .ep-pill.bad { color: #fda4af; }
        .ep-spec { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 3fr) auto; gap: .4rem; align-items: center; }
        .ep-spec + .ep-spec { margin-top: .4rem; }
        .ep-spec .ep-input { padding: .5rem .7rem; font-size: .86rem; }
        .ep-x { width: 2.1rem; height: 2.1rem; border-radius: .7rem; display: flex; align-items: center; justify-content: center; color: #e11d48; background: rgba(244,63,94,.1); }
        .ep-gal { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .5rem; }
        .ep-gal-item { position: relative; aspect-ratio: 1; border-radius: .9rem; overflow: hidden; border: 1px solid rgba(148,163,184,.3); background: #fff; }
        .ep-gal-item img { width: 100%; height: 100%; object-fit: cover; }
        .ep-gal-item .del { position: absolute; top: .25rem; right: .25rem; width: 1.6rem; height: 1.6rem; border-radius: 999px; background: rgba(15,23,42,.65); color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center; }
        .ep-gal-item .tag { position: absolute; left: .25rem; bottom: .25rem; padding: .05rem .4rem; border-radius: 999px; background: #ffe600; color: #2d3277; font-size: .6rem; font-weight: 900; }
        .ep-hint { font-size: .74rem; color: #64748b; display: flex; gap: .4rem; align-items: flex-start; }
        .ep-ml-box { border-radius: 1rem; padding: .85rem; background: linear-gradient(135deg, rgba(255,230,0,.16), rgba(255,230,0,.05)); border: 1px solid rgba(234,179,8,.35); }
        .ep-ml-box b { color: #2d3277; }
        .dark .ep-ml-box b { color: #fde68a; }
    </style>
