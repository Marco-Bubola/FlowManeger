{{-- Estilos de "Meus pedidos" (lista e detalhe), no visual do catálogo. --}}
<style>
    .po-page { max-width: 880px; margin: 0 auto; padding: 14px 12px 0; }
    .po-back { display: inline-flex; align-items: center; gap: 6px; color: var(--ml-blue-dark); font-weight: 700; font-size: 13px; margin-bottom: 10px; }
    .po-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .po-head h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.01em; color: #222; }
    .po-head p { margin: 2px 0 0; color: var(--ml-muted); font-size: 13px; }
    .po-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; font-weight: 700; font-size: 14px; cursor: pointer; white-space: nowrap; text-decoration: none; }
    .po-btn-primary { background: var(--ml-blue); color: #fff; }
    .po-btn-primary:hover { background: var(--ml-blue-dark); }
    .po-btn-soft { background: var(--ml-blue-soft); color: var(--ml-blue-dark); }
    .po-btn-soft:hover { filter: brightness(.97); }
    .po-btn-ghost { background: #fff; color: #444; border: 1px solid var(--ml-line); }
    .po-btn-green { background: var(--ml-green); color: #fff; }
    .po-btn-red { background: #fff; color: var(--ml-red); border: 1px solid #f6c3c8; }
    .po-btn-wa { background: #25d366; color: #fff; }
    .po-btn-block { width: 100%; }

    .po-chips { display: flex; gap: 8px; overflow-x: auto; padding: 2px 0 12px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .po-chips::-webkit-scrollbar { display: none; }
    .po-chip { flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid var(--ml-line); color: #444; font-size: 13px; font-weight: 600; }
    .po-chip b { font-weight: 800; color: var(--ml-muted); font-size: 12px; }
    .po-chip.on { background: var(--ml-blue); border-color: var(--ml-blue); color: #fff; }
    .po-chip.on b { color: rgba(255,255,255,.85); }

    .po-card { background: var(--ml-card); border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); margin-bottom: 12px; overflow: hidden; }
    .po-card-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 14px; border-bottom: 1px solid var(--ml-line); font-size: 13px; color: #555; }
    .po-card-head strong { color: #222; font-weight: 700; }
    .po-card-body { padding: 14px; }
    .po-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--ml-line); }
    .po-link-card { display: block; color: inherit; transition: box-shadow .15s; }
    .po-link-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
    .po-new { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .po-new::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; }

    .po-status { font-size: 16px; font-weight: 800; margin: 0; }
    .po-hint { margin: 3px 0 0; font-size: 13px; color: var(--ml-muted); }
    .tone-green { color: var(--ml-green); }
    .tone-red { color: var(--ml-red); }
    .tone-amber { color: #c2610c; }
    .tone-violet { color: var(--ml-blue-dark); }
    .tone-gray { color: #737373; }

    .po-prod { display: flex; gap: 12px; align-items: center; margin-top: 12px; }
    .po-thumb { width: 64px; height: 64px; flex-shrink: 0; border-radius: 8px; border: 1px solid var(--ml-line); background: #fafafa; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #c4c4c4; font-size: 22px; position: relative; }
    .po-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .po-thumb-more { position: absolute; right: 3px; bottom: 3px; padding: 1px 6px; border-radius: 999px; background: rgba(0,0,0,.65); color: #fff; font-size: 10px; font-weight: 800; }
    .po-prod-name { font-size: 14px; font-weight: 600; color: #333; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .po-prod-sub { font-size: 12px; color: var(--ml-muted); margin-top: 3px; }

    /* Progresso compacto (lista) */
    .po-steps { display: flex; align-items: flex-start; margin-top: 14px; }
    .po-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; min-width: 0; }
    .po-step::before { content: ''; position: absolute; top: 6px; right: 50%; width: 100%; height: 3px; background: #e5e5e5; z-index: 0; }
    .po-step:first-child::before { display: none; }
    .po-step.done::before, .po-step.current::before, .po-step.failed::before { background: var(--ml-green); }
    .po-step.failed::before { background: var(--ml-red); }
    .po-dot { width: 15px; height: 15px; border-radius: 50%; background: #e5e5e5; position: relative; z-index: 1; border: 3px solid #fff; box-shadow: 0 0 0 1px #e5e5e5; }
    .po-step.done .po-dot { background: var(--ml-green); box-shadow: 0 0 0 1px var(--ml-green); }
    .po-step.current .po-dot { background: #fff; box-shadow: 0 0 0 2px var(--ml-blue); }
    .po-step.failed .po-dot { background: var(--ml-red); box-shadow: 0 0 0 1px var(--ml-red); }
    .po-step span { margin-top: 5px; font-size: 10.5px; font-weight: 600; color: #999; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; padding: 0 2px; }
    .po-step.done span, .po-step.current span { color: #444; }
    .po-step.failed span { color: var(--ml-red); }

    .po-total-lbl { font-size: 12px; color: var(--ml-muted); }
    .po-total { font-size: 18px; font-weight: 800; color: #222; }

    /* Linha do tempo (detalhe) */
    .po-tl { list-style: none; margin: 0; padding: 0; }
    .po-tl li { display: flex; gap: 12px; position: relative; padding-bottom: 18px; }
    .po-tl li:last-child { padding-bottom: 0; }
    .po-tl li::before { content: ''; position: absolute; left: 11px; top: 26px; bottom: 2px; width: 2px; background: #e5e5e5; }
    .po-tl li:last-child::before { display: none; }
    .po-tl li.done::before { background: var(--ml-green); }
    .po-tl-ic { width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; background: #eee; color: #aaa; position: relative; z-index: 1; }
    .po-tl li.done .po-tl-ic { background: var(--ml-green); color: #fff; }
    .po-tl li.current .po-tl-ic { background: #fff; color: var(--ml-blue); box-shadow: 0 0 0 2px var(--ml-blue); }
    .po-tl li.failed .po-tl-ic { background: var(--ml-red); color: #fff; }
    .po-tl-t { font-size: 14px; font-weight: 700; color: #333; }
    .po-tl li.todo .po-tl-t { color: #aaa; font-weight: 600; }
    .po-tl li.failed .po-tl-t { color: var(--ml-red); }
    .po-tl-d { font-size: 12px; color: var(--ml-muted); margin-top: 2px; }

    .po-items { list-style: none; margin: 0; padding: 0; }
    .po-items li { display: flex; gap: 12px; align-items: center; padding: 12px 0; border-top: 1px solid var(--ml-line); }
    .po-items li:first-child { border-top: 0; padding-top: 0; }
    .po-items .po-info { flex: 1; min-width: 0; }
    .po-items .po-price { text-align: right; flex-shrink: 0; }
    .po-items .po-price strong { display: block; font-size: 15px; color: #222; }
    .po-items .po-price s { font-size: 11px; color: #999; }
    .po-note { margin-top: 4px; font-size: 12px; color: #6b6b6b; font-style: italic; }

    .po-sum { width: 100%; border-collapse: collapse; font-size: 14px; }
    .po-sum td { padding: 6px 0; color: #555; }
    .po-sum td:last-child { text-align: right; font-weight: 600; color: #333; }
    .po-sum tr.po-sum-total td { border-top: 1px solid var(--ml-line); padding-top: 10px; font-size: 16px; font-weight: 800; color: #222; }
    .po-sec-title { margin: 0 0 12px; font-size: 15px; font-weight: 800; color: #222; }
    .po-msg { border-left: 3px solid var(--ml-blue); background: var(--ml-blue-soft); border-radius: 0 8px 8px 0; padding: 10px 12px; font-size: 14px; color: #333; white-space: pre-line; }
    .po-flash { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: var(--ml-radius); margin-bottom: 12px; font-size: 14px; font-weight: 600; background: #e6f7ee; color: #00733a; border: 1px solid #b7e6cc; }
    .po-flash.err { background: #fdecee; color: #b4232f; border-color: #f6c3c8; }
    .po-empty { text-align: center; padding: 40px 20px; }
    .po-empty i { font-size: 42px; color: var(--ml-blue); opacity: .5; }
    .po-empty h2 { margin: 12px 0 4px; font-size: 18px; }
    .po-empty p { margin: 0 0 16px; color: var(--ml-muted); }
    .po-actions { display: grid; gap: 8px; }
    .po-pager { display: flex; justify-content: space-between; gap: 8px; margin: 4px 0 16px; }
    .po-hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .po-hero-id { font-size: 13px; color: var(--ml-muted); }
    .po-hero-id strong { color: #222; font-size: 15px; }

    @media (min-width: 900px) {
        .po-page { padding-top: 22px; }
        .po-head h1 { font-size: 26px; }
        .po-grid { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 16px; align-items: start; }
        .po-grid > div > .po-card:last-child { margin-bottom: 0; }
        .po-side { position: sticky; top: 76px; }
        .po-step span { font-size: 12px; }
    }
</style>
