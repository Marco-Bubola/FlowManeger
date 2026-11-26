{{-- Orçamento do cliente (link público) e pedido de orçamento do portal --}}
<style>
    .cq-page { max-width: 880px; margin: 0 auto; padding: 1rem 1rem 4rem; display: flex; flex-direction: column; gap: 1rem; color: var(--ml-text, #333); }
    .cq-card { background: var(--ml-card, #fff); border: 1px solid var(--ml-line, #e6e6e6); border-radius: 18px; overflow: hidden; }
    .cq-pad { padding: 1.1rem 1.2rem; }
    .cq-hero { position: relative; padding: 1.3rem 1.2rem; color: #fff; background: var(--ml-hero, linear-gradient(135deg, #db2777, #8b5cf6 55%, #2563eb)); }
    .cq-hero h1 { margin: .2rem 0 .3rem; font-size: 1.45rem; font-weight: 900; line-height: 1.2; }
    .cq-hero p { margin: 0; opacity: .92; font-size: .9rem; }
    .cq-code { font-size: .75rem; font-weight: 800; letter-spacing: .06em; opacity: .85; text-transform: uppercase; }
    .cq-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 800; background: rgb(255 255 255 / .22); color: #fff; }
    .cq-badges { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .7rem; }
    .cq-h2 { margin: 0 0 .7rem; font-size: 1.05rem; font-weight: 800; color: var(--ml-text, #222); display: flex; align-items: center; gap: .45rem; }
    .cq-item { display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: .9rem; padding: 1rem 1.2rem; }
    .cq-item + .cq-item { border-top: 1px solid var(--ml-line, #eee); }
    @media (max-width: 560px) { .cq-item { grid-template-columns: 76px minmax(0, 1fr); gap: .75rem; padding: .9rem 1rem; } }
    .cq-ph { position: relative; width: 100%; aspect-ratio: 1; border-radius: 14px; overflow: hidden; background: var(--ml-chip, #f3f3f6); display: flex; align-items: center; justify-content: center; color: var(--ml-muted, #999); font-size: 1.6rem; }
    .cq-ph img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .cq-name { font-weight: 800; font-size: 1rem; margin: 0; color: var(--ml-text, #222); }
    .cq-attrs { display: flex; flex-wrap: wrap; gap: .3rem; margin: .35rem 0; }
    .cq-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .55rem; border-radius: 999px; font-size: .74rem; font-weight: 700; background: var(--ml-soft, #ede9fe); color: var(--ml-blue-dark, #6d28d9); }
    .cq-desc { margin: .2rem 0 0; font-size: .88rem; color: var(--ml-page-text, #555); white-space: pre-line; }
    .cq-price { margin-top: .5rem; display: flex; align-items: baseline; justify-content: space-between; gap: .5rem; }
    .cq-price b { font-size: 1.1rem; font-weight: 900; color: var(--ml-text, #222); }
    .cq-price span { font-size: .78rem; color: var(--ml-muted, #888); }
    .cq-thumbs { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .55rem; }
    .cq-thumbs a { width: 52px; height: 52px; border-radius: 10px; overflow: hidden; border: 1px solid var(--ml-line, #eee); }
    .cq-thumbs img { width: 100%; height: 100%; object-fit: cover; }
    .cq-row { display: flex; justify-content: space-between; gap: .75rem; padding: .35rem 0; font-size: .92rem; color: var(--ml-page-text, #555); }
    .cq-row b { color: var(--ml-text, #222); }
    .cq-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: .7rem; margin-top: .4rem; border-top: 1px solid var(--ml-line, #eee); }
    .cq-total span { font-weight: 800; color: var(--ml-muted, #777); }
    .cq-total b { font-size: 1.7rem; font-weight: 900; color: var(--ml-text, #111); }
    .cq-info { display: grid; gap: .6rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .cq-info div { padding: .75rem .85rem; border-radius: 14px; background: var(--ml-chip, #f6f6f8); border: 1px solid var(--ml-line, #eee); }
    .cq-info small { display: block; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--ml-muted, #888); }
    .cq-info strong { font-size: .95rem; color: var(--ml-text, #222); }
    .cq-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 50px; padding: 0 1.2rem; border-radius: 14px; font-weight: 800; font-size: 1rem; border: 0; cursor: pointer; text-decoration: none; }
    .cq-btn-primary { color: #fff; background: var(--ml-blue, #8b5cf6); box-shadow: 0 10px 24px -12px var(--ml-blue, #8b5cf6); width: 100%; }
    .cq-btn-primary:hover { background: var(--ml-blue-dark, #7c3aed); }
    .cq-btn-soft { color: var(--ml-text, #333); background: var(--ml-chip, #f3f3f6); border: 1px solid var(--ml-line, #e5e5e5); }
    .cq-btn-wa { color: #fff; background: #16a34a; }
    .cq-actions { display: grid; gap: .6rem; }
    @media (min-width: 640px) { .cq-actions.two { grid-template-columns: 1fr 1fr; } }
    .cq-flash { display: flex; gap: .6rem; align-items: flex-start; padding: .9rem 1rem; border-radius: 14px; font-weight: 700; font-size: .92rem; }
    .cq-flash.ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .cq-flash.bad { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
    .cq-flash.info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .cq-input { width: 100%; box-sizing: border-box; border-radius: 12px; border: 1px solid var(--ml-line, #ddd); background: #fff; padding: .75rem .85rem; font: inherit; font-size: 1rem; color: #222; }
    .cq-input:focus { outline: none; border-color: var(--ml-blue, #8b5cf6); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ml-blue, #8b5cf6) 20%, transparent); }
    textarea.cq-input { min-height: 110px; resize: vertical; }
    .cq-label { display: flex; flex-direction: column; gap: .35rem; font-size: .85rem; font-weight: 700; color: var(--ml-text, #333); }
    .cq-label small { font-weight: 500; color: var(--ml-muted, #888); }
    .cq-err { color: #be123c; font-size: .8rem; font-weight: 700; }
    .cq-grid2 { display: grid; gap: .8rem; grid-template-columns: 1fr; }
    @media (min-width: 640px) { .cq-grid2 { grid-template-columns: 1fr 1fr; } }
    .cq-kind { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
    .cq-kind label { position: relative; display: flex; flex-direction: column; gap: .25rem; padding: .9rem; border-radius: 14px; border: 2px solid var(--ml-line, #e5e5e5); cursor: pointer; background: var(--ml-card, #fff); }
    .cq-kind input { position: absolute; opacity: 0; pointer-events: none; }
    .cq-kind label:has(input:checked) { border-color: var(--ml-blue, #8b5cf6); background: var(--ml-soft, #f5f3ff); }
    .cq-kind i { font-size: 1.4rem; color: var(--ml-blue, #8b5cf6); }
    .cq-kind b { font-size: .95rem; color: var(--ml-text, #222); }
    .cq-kind span { font-size: .76rem; color: var(--ml-muted, #888); }
    .cq-swatches { display: flex; flex-wrap: wrap; gap: .45rem; }
    .cq-sw { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .65rem .35rem .4rem; border-radius: 999px; border: 2px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); font-size: .8rem; font-weight: 700; cursor: pointer; color: var(--ml-text, #333); }
    .cq-sw i { width: 20px; height: 20px; border-radius: 999px; border: 1px solid rgb(0 0 0 / .12); flex-shrink: 0; }
    .cq-sw.on { border-color: var(--ml-blue, #8b5cf6); background: var(--ml-soft, #f5f3ff); }
    .cq-group { border: 1px solid var(--ml-line, #eee); border-radius: 14px; overflow: hidden; }
    .cq-group summary { list-style: none; display: flex; align-items: center; gap: .5rem; padding: .8rem .9rem; font-weight: 800; cursor: pointer; }
    .cq-group summary::-webkit-details-marker { display: none; }
    .cq-group[open] summary { border-bottom: 1px solid var(--ml-line, #eee); }
    .cq-group-body { padding: .8rem .9rem; }
    .cq-picked { margin-left: auto; font-size: .78rem; font-weight: 700; color: var(--ml-blue-dark, #6d28d9); }
    .cq-drop { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .3rem; padding: 1.2rem; border-radius: 14px; border: 2px dashed color-mix(in srgb, var(--ml-blue, #8b5cf6) 55%, transparent); background: var(--ml-soft, #f5f3ff); color: var(--ml-blue-dark, #6d28d9); font-weight: 800; cursor: pointer; text-align: center; }
    .cq-drop small { font-weight: 600; color: var(--ml-muted, #888); }
    .cq-previews { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-top: .6rem; }
    @media (min-width: 640px) { .cq-previews { grid-template-columns: repeat(6, 1fr); } }
    .cq-previews div { position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden; background: #f3f3f3; }
    .cq-previews img { width: 100%; height: 100%; object-fit: cover; }
    .cq-previews button { position: absolute; top: 4px; right: 4px; width: 24px; height: 24px; border-radius: 999px; border: 0; background: rgb(0 0 0 / .65); color: #fff; font-size: .75rem; cursor: pointer; }
    .cq-product { display: flex; gap: .8rem; align-items: center; padding: .8rem; border-radius: 14px; background: var(--ml-chip, #f6f6f8); border: 1px solid var(--ml-line, #eee); }
    .cq-product img { width: 64px; height: 64px; border-radius: 12px; object-fit: cover; flex-shrink: 0; background: #eee; }
    .cq-muted { font-size: .82rem; color: var(--ml-muted, #888); }
    .cq-steps { display: grid; gap: .6rem; grid-template-columns: repeat(3, 1fr); }
    .cq-steps div { text-align: center; font-size: .78rem; color: var(--ml-page-text, #555); }
    .cq-steps i { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; margin: 0 auto .3rem; border-radius: 12px; background: var(--ml-soft, #f5f3ff); color: var(--ml-blue, #8b5cf6); font-size: 1.15rem; }
</style>
