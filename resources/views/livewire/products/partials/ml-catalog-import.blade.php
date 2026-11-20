{{-- Importar do catálogo do Mercado Livre: buscar → escolher o produto → marcar o que trazer.
     Usado no Novo/Editar produto e no card do envio de PDF (mlUploadMode). --}}
@if($showMlImport)
@php
    $mlDesc = !empty($mlData) ? trim((string) ($mlData['description'] ?? '')) : '';
    $mlFeatures = !empty($mlData) ? ($mlData['features'] ?? []) : [];
    $mlNow = !empty($mlData) ? $this->mlCurrentValues() : [];
    $mlCat = $mlData['category'] ?? null;
    $mlDims = array_diff_key($mlData['dims'] ?? [], ['from' => 1]);
    $mlCats = !empty($mlData) && $mlCat ? $this->mlAppCategories() : collect();
    $mlNowCat = !empty($mlNow['category_id']) ? $mlCats->firstWhere('id_category', (int) $mlNow['category_id']) : null;
    $mlNum = fn ($v, $d = 1) => $d > 0 ? rtrim(rtrim(number_format((float) $v, $d, ',', '.'), '0'), ',') : number_format((float) $v, 0, ',', '.');
    $mlUrl = !empty($mlData['permalink']) ? $mlData['permalink'] : (!empty($mlData['id']) ? 'https://www.mercadolivre.com.br/p/' . $mlData['id'] : null);
    $mlSpecsShown = 8;
    $mlShowDims = !empty($mlData) && $this->mlAvailable('dims');
@endphp
<style>
    .mi-bg { position: fixed; inset: 0; z-index: 80; background: rgba(2,6,23,.62); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); animation: miFade .18s ease; }
    .mi { --mi-bg: #fff; --mi-card: #f8fafc; --mi-line: rgba(148,163,184,.28); --mi-ink: #0f172a; --mi-mute: #64748b; --mi-soft: #475569; --mi-acc: #6366f1;
          position: fixed; z-index: 81; left: 0; right: 0; bottom: 0; max-height: 94vh; display: flex; flex-direction: column; background: var(--mi-bg); color: var(--mi-ink);
          border-radius: 26px 26px 0 0; box-shadow: 0 -20px 60px rgba(2,6,23,.35); overflow: hidden; animation: miUp .26s cubic-bezier(.2,.9,.3,1); }
    .dark .mi { --mi-bg: #0b1222; --mi-card: rgba(30,41,59,.55); --mi-line: rgba(71,85,105,.55); --mi-ink: #f1f5f9; --mi-mute: #94a3b8; --mi-soft: #cbd5e1; --mi-acc: #818cf8; }
    @media (min-width: 768px) {
        .mi { left: 50%; top: 50%; bottom: auto; right: auto; transform: translate(-50%, -50%); width: min(1040px, 94vw); max-height: 90vh; border-radius: 26px; animation: miPop .22s ease; }
    }
    @media (min-width: 1800px) { .mi { width: 1240px; } }
    @keyframes miFade { from { opacity: 0 } }
    @keyframes miUp { from { transform: translateY(40px); opacity: 0 } }
    @keyframes miPop { from { transform: translate(-50%, -47%) scale(.98); opacity: 0 } }
    .mi-grab { width: 2.6rem; height: .3rem; border-radius: 99px; background: rgba(148,163,184,.5); margin: .55rem auto 0; }
    @media (min-width: 768px) { .mi-grab { display: none; } }

    /* Cabeçalho com a faixa amarela do ML */
    .mi-h { position: relative; display: flex; align-items: center; gap: .8rem; padding: .85rem 1rem .85rem; border-bottom: 1px solid var(--mi-line);
            background: linear-gradient(120deg, rgba(255,230,0,.16), rgba(255,230,0,0) 45%), var(--mi-bg); }
    .mi-h::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: linear-gradient(90deg, #ffe600, #f59e0b, #a855f7); }
    @media (min-width: 768px) { .mi-h { padding: 1.05rem 1.35rem; } }
    .mi-logo { width: 2.6rem; height: 2.6rem; border-radius: .95rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: #ffe600; color: #2d3277; font-size: 1.15rem; box-shadow: 0 8px 20px rgba(234,179,8,.3); }
    .mi-h h3 { margin: 0; font-size: 1.02rem; font-weight: 800; letter-spacing: -.01em; }
    .mi-h p { margin: .1rem 0 0; font-size: .75rem; color: var(--mi-mute); }
    .mi-steps { display: none; align-items: center; gap: .35rem; margin-left: auto; font-size: .72rem; font-weight: 700; color: var(--mi-mute); }
    @media (min-width: 640px) { .mi-steps { display: flex; } }
    .mi-steps span { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .6rem; border-radius: 99px; background: rgba(148,163,184,.12); }
    .mi-steps span.on { background: rgba(99,102,241,.14); color: var(--mi-acc); }
    .mi-steps span.ok { color: #10b981; background: rgba(16,185,129,.12); }
    .mi-steps i.sep { font-size: .7rem; opacity: .6; }
    .mi-close { margin-left: auto; width: 2.3rem; height: 2.3rem; border-radius: .85rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--mi-mute); background: rgba(148,163,184,.14); transition: background .15s; }
    .mi-close:hover { background: rgba(148,163,184,.26); }
    @media (min-width: 640px) { .mi-steps + .mi-close { margin-left: .4rem; } }

    .mi-body { overflow-y: auto; padding: 1rem; overscroll-behavior: contain; flex: 1; }
    @media (min-width: 768px) { .mi-body { padding: 1.25rem 1.35rem; } }

    /* Passo 1: busca */
    .mi-search { position: relative; display: flex; gap: .5rem; }
    .mi-search .box { position: relative; flex: 1; }
    .mi-search .box i { position: absolute; left: .95rem; top: 50%; transform: translateY(-50%); color: var(--mi-mute); }
    .mi-search input { width: 100%; height: 3.1rem; padding: 0 1rem 0 2.6rem; border-radius: 1rem; border: 1.5px solid var(--mi-line); background: var(--mi-card); color: var(--mi-ink); font-size: .95rem; outline: none; transition: border-color .15s, box-shadow .15s; }
    .mi-search input:focus { border-color: var(--mi-acc); box-shadow: 0 0 0 4px rgba(99,102,241,.15); }
    .mi-search button { height: 3.1rem; padding: 0 1.2rem; border-radius: 1rem; }
    .mi-hint { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .7rem; font-size: .74rem; color: var(--mi-mute); }
    .mi-hint span { display: inline-flex; align-items: center; gap: .3rem; padding: .28rem .6rem; border-radius: 99px; background: rgba(148,163,184,.12); }
    .mi-cands { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 1.1rem; }
    @media (min-width: 640px) { .mi-cands { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); } }
    .mi-cand { display: flex; flex-direction: column; text-align: left; border: 1px solid var(--mi-line); border-radius: 1.1rem; overflow: hidden; background: var(--mi-card); transition: border-color .15s, transform .15s, box-shadow .15s; }
    .mi-cand:hover { border-color: var(--mi-acc); transform: translateY(-3px); box-shadow: 0 14px 30px rgba(15,23,42,.14); }
    .mi-cand .img { aspect-ratio: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: .6rem; }
    .mi-cand .img img { width: 100%; height: 100%; object-fit: contain; }
    .mi-cand .t { padding: .6rem .7rem 0; font-size: .8rem; font-weight: 700; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .mi-cand .b { padding: .35rem .7rem 0; display: flex; flex-wrap: wrap; gap: .25rem; }
    .mi-cand .go { margin-top: auto; padding: .6rem .7rem .7rem; font-size: .74rem; font-weight: 700; color: var(--mi-acc); display: flex; align-items: center; gap: .3rem; }
    .mi-chip { display: inline-flex; align-items: center; gap: .3rem; max-width: 100%; padding: .2rem .55rem; border-radius: 99px; font-size: .7rem; font-weight: 600; background: rgba(148,163,184,.14); color: var(--mi-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mi-chip.y { background: rgba(255,230,0,.22); color: #854d0e; }
    .dark .mi-chip.y { color: #fde047; background: rgba(255,230,0,.12); }
    .mi-chip.g { background: rgba(16,185,129,.14); color: #047857; }
    .dark .mi-chip.g { color: #6ee7b7; }
    .mi-skel { border-radius: 1.1rem; overflow: hidden; border: 1px solid var(--mi-line); }
    .mi-skel div { background: linear-gradient(90deg, rgba(148,163,184,.12), rgba(148,163,184,.26), rgba(148,163,184,.12)); background-size: 200% 100%; animation: miShim 1.2s infinite; }
    @keyframes miShim { to { background-position: -200% 0 } }
    .mi-empty { text-align: center; padding: 2.2rem 1rem 1rem; color: var(--mi-mute); font-size: .84rem; }
    .mi-empty .ico { width: 4rem; height: 4rem; margin: 0 auto .8rem; border-radius: 1.3rem; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; background: rgba(255,230,0,.2); color: #a16207; }
    .mi-err { margin-top: .9rem; display: flex; gap: .55rem; align-items: flex-start; padding: .75rem .9rem; border-radius: 1rem; font-size: .82rem; background: rgba(244,63,94,.1); color: #be123c; border: 1px solid rgba(244,63,94,.25); }
    .dark .mi-err { color: #fda4af; }

    /* Passo 2: produto escolhido */
    .mi-hero { display: grid; grid-template-columns: 5.5rem minmax(0, 1fr); gap: .9rem; align-items: center; padding: .9rem; border-radius: 1.3rem;
               background: linear-gradient(135deg, rgba(255,230,0,.16), rgba(168,85,247,.08)); border: 1px solid rgba(234,179,8,.3); }
    @media (min-width: 768px) { .mi-hero { grid-template-columns: 7.5rem minmax(0, 1fr) auto; padding: 1rem 1.1rem; } }
    .mi-hero .pic { aspect-ratio: 1; border-radius: 1rem; background: #fff; display: flex; align-items: center; justify-content: center; padding: .35rem; box-shadow: 0 8px 22px rgba(15,23,42,.12); }
    .mi-hero .pic img { width: 100%; height: 100%; object-fit: contain; }
    .mi-hero .n { font-size: .95rem; font-weight: 800; line-height: 1.3; letter-spacing: -.01em; }
    @media (min-width: 768px) { .mi-hero .n { font-size: 1.08rem; } }
    .mi-hero .chips { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
    .mi-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: .2rem; margin-top: .5rem; font-size: .74rem; color: var(--mi-mute); }
    .mi-crumb b { color: var(--mi-soft); font-weight: 700; }
    .mi-crumb i { font-size: .62rem; opacity: .7; }
    .mi-hero .acts { grid-column: 1 / -1; display: flex; gap: .45rem; flex-wrap: wrap; }
    @media (min-width: 768px) { .mi-hero .acts { grid-column: auto; flex-direction: column; align-items: stretch; } }
    .mi-mini { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .45rem .8rem; border-radius: .8rem; font-size: .76rem; font-weight: 700; border: 1px solid var(--mi-line); background: var(--mi-bg); color: var(--mi-soft); white-space: nowrap; transition: border-color .15s, color .15s; }
    .mi-mini:hover { border-color: var(--mi-acc); color: var(--mi-acc); }
    .mi-price { display: inline-flex; align-items: baseline; gap: .3rem; margin-top: .5rem; font-size: .72rem; color: var(--mi-mute); }
    .mi-price b { font-size: .95rem; color: #059669; font-weight: 800; }
    .dark .mi-price b { color: #34d399; }

    .mi-bar { position: sticky; top: -1rem; z-index: 2; display: flex; align-items: center; gap: .5rem; margin: 1rem -1rem 0; padding: .6rem 1rem; background: var(--mi-bg); border-bottom: 1px solid var(--mi-line); }
    @media (min-width: 768px) { .mi-bar { top: -1.25rem; margin: 1.1rem -1.35rem 0; padding: .65rem 1.35rem; } }
    .mi-bar b { font-size: .86rem; }
    .mi-bar small { font-size: .74rem; color: var(--mi-mute); }
    .mi-bar .r { margin-left: auto; display: flex; gap: .35rem; }

    .mi-grid { display: grid; gap: .9rem; margin-top: 1rem; }
    @media (min-width: 1024px) { .mi-grid { grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); align-items: start; } }
    .mi-col { display: grid; gap: .9rem; min-width: 0; }

    .mi-card { border: 1px solid var(--mi-line); border-radius: 1.15rem; background: var(--mi-card); transition: border-color .15s, background .15s, opacity .15s; min-width: 0; }
    .mi-card:has(> .mi-ch input[data-t]:checked) { border-color: rgba(99,102,241,.55); background: rgba(99,102,241,.06); }
    .mi-ch { display: flex; align-items: center; gap: .7rem; padding: .8rem .9rem; cursor: pointer; }
    .mi-ch .ico { width: 2.15rem; height: 2.15rem; border-radius: .75rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; }
    .mi-ch .tt { min-width: 0; flex: 1; }
    .mi-ch .tt b { display: block; font-size: .88rem; line-height: 1.25; }
    .mi-ch .tt small { display: block; font-size: .72rem; color: var(--mi-mute); margin-top: .1rem; }
    .mi-cb { padding: 0 .9rem .9rem; font-size: .82rem; color: var(--mi-soft); }

    /* Chave liga/desliga (o input continua sendo checkbox do Livewire) */
    .mi-sw { position: relative; width: 2.6rem; height: 1.5rem; flex-shrink: 0; }
    .mi-sw input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; z-index: 1; }
    .mi-sw span { position: absolute; inset: 0; border-radius: 99px; background: rgba(148,163,184,.4); transition: background .18s; }
    .mi-sw span::after { content: ''; position: absolute; top: .19rem; left: .19rem; width: 1.12rem; height: 1.12rem; border-radius: 99px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.2); transition: transform .18s; }
    .mi-sw input:checked + span { background: linear-gradient(90deg, #6366f1, #a855f7); }
    .mi-sw input:checked + span::after { transform: translateX(1.1rem); }
    .mi-sw input:focus-visible + span { box-shadow: 0 0 0 3px rgba(99,102,241,.35); }

    .mi-photos { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: .55rem; }
    @media (min-width: 768px) { .mi-photos { grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)); } }
    .mi-photo { position: relative; aspect-ratio: 1; border-radius: .95rem; overflow: hidden; border: 2px solid var(--mi-line); background: #fff; cursor: pointer; transition: border-color .15s, transform .15s; }
    .mi-photo:hover { transform: translateY(-2px); }
    .mi-photo img { width: 100%; height: 100%; object-fit: contain; padding: .25rem; }
    .mi-photo input { position: absolute; opacity: 0; pointer-events: none; }
    .mi-photo .ck { position: absolute; top: .35rem; right: .35rem; width: 1.4rem; height: 1.4rem; border-radius: 99px; background: rgba(255,255,255,.95); border: 1px solid rgba(148,163,184,.6); display: flex; align-items: center; justify-content: center; font-size: .72rem; color: transparent; }
    .mi-photo .no { position: absolute; left: .35rem; top: .35rem; min-width: 1.3rem; height: 1.3rem; padding: 0 .3rem; border-radius: 99px; background: rgba(15,23,42,.6); color: #fff; font-size: .66rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .mi-photo .cap { position: absolute; left: 0; right: 0; bottom: 0; padding: .2rem; text-align: center; font-size: .64rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); }
    .mi-photo:not(:has(input:checked)) img { opacity: .45; filter: grayscale(.5); }
    .mi-photo:has(input:checked) { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.18); }
    .mi-photo:has(input:checked) .ck { background: #6366f1; border-color: #6366f1; color: #fff; }
    .mi-row { display: flex; align-items: center; gap: .6rem; margin-top: .8rem; padding: .6rem .7rem; border-radius: .9rem; background: rgba(148,163,184,.1); cursor: pointer; font-size: .8rem; }
    .mi-row .mi-sw { margin-left: auto; }

    .mi-desc { white-space: pre-line; line-height: 1.55; position: relative; }
    .mi-desc.cut { max-height: 8.5rem; overflow: hidden; }
    .mi-desc.cut::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 2.6rem; background: linear-gradient(transparent, var(--mi-fade, #f8fafc)); }
    .dark .mi-desc.cut::after { --mi-fade: #121a2c; }
    .mi-card:has(> .mi-ch input:checked) .mi-desc.cut::after { --mi-fade: #f4f5fe; }
    .dark .mi-card:has(> .mi-ch input:checked) .mi-desc.cut::after { --mi-fade: #151a35; }
    .mi-feats { display: grid; gap: .35rem; margin-top: .75rem; }
    .mi-feats li { display: flex; gap: .45rem; align-items: flex-start; line-height: 1.4; }
    .mi-feats li i { color: #10b981; margin-top: .1rem; }
    .mi-more { margin-top: .5rem; font-size: .76rem; font-weight: 700; color: var(--mi-acc); display: inline-flex; align-items: center; gap: .25rem; }

    .mi-specs { display: grid; grid-template-columns: minmax(0, 1fr); border-radius: .9rem; overflow: hidden; border: 1px solid var(--mi-line); }
    @media (min-width: 640px) { .mi-specs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .mi-specs div { display: flex; flex-direction: column; gap: .05rem; padding: .45rem .7rem; min-width: 0; border-bottom: 1px solid var(--mi-line); background: var(--mi-bg); }
    .mi-specs dt { font-size: .68rem; font-weight: 700; color: var(--mi-mute); text-transform: uppercase; letter-spacing: .03em; }
    .mi-specs dd { margin: 0; font-size: .82rem; font-weight: 600; color: var(--mi-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .mi-val { font-size: .9rem; font-weight: 800; color: var(--mi-ink); word-break: break-word; }
    .mi-now { margin-top: .25rem; font-size: .72rem; color: var(--mi-mute); }
    .mi-now s { opacity: .8; }
    .mi-dims { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .4rem; }
    .mi-dims div { text-align: center; padding: .5rem .2rem; border-radius: .8rem; background: var(--mi-bg); border: 1px solid var(--mi-line); }
    .mi-dims b { display: block; font-size: .86rem; color: var(--mi-ink); }
    .mi-dims small { font-size: .66rem; color: var(--mi-mute); text-transform: uppercase; letter-spacing: .03em; }
    .mi-sel { width: 100%; height: 2.6rem; margin-top: .55rem; padding: 0 .8rem; border-radius: .8rem; border: 1.5px solid var(--mi-line); background: var(--mi-bg); color: var(--mi-ink); font-size: .84rem; font-weight: 600; }
    .mi-fam { border-color: rgba(139,92,246,.45); background: rgba(139,92,246,.08); }

    /* Rodapé */
    .mi-f { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .75rem 1rem calc(.8rem + env(safe-area-inset-bottom)); border-top: 1px solid var(--mi-line); background: var(--mi-bg); }
    @media (min-width: 768px) { .mi-f { flex-wrap: nowrap; padding: .85rem 1.35rem; } }
    .mi-sum { flex-basis: 100%; min-width: 0; font-size: .76rem; color: var(--mi-mute); display: flex; align-items: center; gap: .4rem; }
    .mi-sum b { color: var(--mi-ink); }
    .mi-sum span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    @media (min-width: 768px) { .mi-sum { flex-basis: auto; flex: 1; } }
    .mi-f .ep-btn { min-height: 2.9rem; }
    .mi-go { flex: 1; }
    @media (min-width: 768px) { .mi-go { flex: none; min-width: 15rem; } }
</style>

<div class="mi-bg" wire:click="closeMlImport"></div>
<div class="mi" wire:key="mi-{{ $mlData['id'] ?? 'busca' }}" role="dialog" aria-modal="true" aria-label="Importar do Mercado Livre"
     x-data="{
        n: 0, photos: 0, cover: -1, labels: [],
        sum() {
            const on = [...$root.querySelectorAll('input[data-t]:checked')];
            this.labels = on.map(i => i.dataset.t);
            const ph = [...$root.querySelectorAll('input[data-photo]')];
            this.photos = ph.filter(i => i.checked).length;
            const cv = $root.querySelector('input[data-cover]');
            this.cover = cv && cv.checked ? ph.findIndex(i => i.checked) : -1;
            this.n = this.labels.length + (this.photos ? 1 : 0);
        },
        setAll(on) {
            $root.querySelectorAll('input[data-t]:not([data-keep]), input[data-photo]').forEach(i => {
                if (i.checked !== on) { i.checked = on; i.dispatchEvent(new Event('change', { bubbles: true })); }
            });
            this.sum();
        },
        photosAll(on) {
            $root.querySelectorAll('input[data-photo]').forEach(i => {
                if (i.checked !== on) { i.checked = on; i.dispatchEvent(new Event('change', { bubbles: true })); }
            });
            this.sum();
        },
        turnOn(key) {
            const i = $root.querySelector(`input[data-key='${key}']`);
            if (i && !i.checked) { i.checked = true; i.dispatchEvent(new Event('change', { bubbles: true })); }
            this.sum();
        },
        text() {
            const parts = [];
            if (this.photos) parts.push(this.photos + (this.photos === 1 ? ' foto' : ' fotos'));
            return parts.concat(this.labels).join(' · ');
        }
     }"
     x-init="$nextTick(() => sum())"
     @change="sum()"
     @keydown.escape.window="$wire.closeMlImport()">
    <div class="mi-grab"></div>
    <div class="mi-h">
        <span class="mi-logo"><i class="bi bi-cloud-download"></i></span>
        <div class="min-w-0">
            <h3>Importar do Mercado Livre</h3>
            <p>{{ empty($mlData) ? 'Busque pelo código de barras ou pelo nome' : (!empty($mlUploadMode) ? 'Marque o que levar. Entra no produto quando você salvar a lista.' : 'Ligue o que quer trazer para este produto') }}</p>
        </div>
        <div class="mi-steps" aria-hidden="true">
            <span class="{{ empty($mlData) ? 'on' : 'ok' }}"><i class="bi {{ empty($mlData) ? 'bi-search' : 'bi-check-lg' }}"></i>Achar o produto</span>
            <i class="bi bi-chevron-right sep"></i>
            <span class="{{ empty($mlData) ? '' : 'on' }}"><i class="bi bi-toggles"></i>Escolher o que trazer</span>
        </div>
        <button type="button" class="mi-close" wire:click="closeMlImport" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="mi-body">
        @if(empty($mlData))
            <form class="mi-search" wire:submit.prevent="searchMlCatalog">
                <div class="box">
                    <i class="bi bi-upc-scan"></i>
                    <input type="search" wire:model="mlQuery" placeholder="Código de barras ou nome do produto" autofocus enterkeyhint="search">
                </div>
                <button type="submit" class="ep-btn ep-btn-primary"><i class="bi bi-search"></i><span class="hidden sm:inline">Buscar</span></button>
            </form>
            <div class="mi-hint">
                <span><i class="bi bi-upc"></i>Código de barras acha o produto exato</span>
                <span><i class="bi bi-fonts"></i>Pelo nome mostra várias opções</span>
            </div>

            <div class="mi-cands" wire:loading.grid wire:target="searchMlCatalog,pickMlProduct" style="display:none">
                @for($k = 0; $k < 6; $k++)
                    <div class="mi-skel"><div style="aspect-ratio:1"></div><div style="height:.7rem;margin:.7rem .7rem .4rem;border-radius:99px"></div><div style="height:.7rem;width:60%;margin:0 .7rem .8rem;border-radius:99px"></div></div>
                @endfor
            </div>

            @if($mlSearchError)<div class="mi-err" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct"><i class="bi bi-exclamation-circle"></i><span>{{ $mlSearchError }}</span></div>@endif

            @if(count($mlCandidates))
                <div class="flex items-center justify-between mt-4" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct">
                    <b class="text-sm">{{ count($mlCandidates) }} {{ count($mlCandidates) === 1 ? 'produto encontrado' : 'produtos encontrados' }}</b>
                    <small class="text-xs" style="color:var(--mi-mute)">Toque no certo</small>
                </div>
                <div class="mi-cands" style="margin-top:.6rem" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct">
                    @foreach($mlCandidates as $c)
                        <button type="button" class="mi-cand" wire:click="pickMlProduct(@js($c['id']))" wire:key="cand-{{ $c['id'] }}">
                            <div class="img">@if($c['picture'])<img src="{{ $c['picture'] }}" alt="" loading="lazy">@else<i class="bi bi-image text-3xl text-slate-300"></i>@endif</div>
                            <div class="t">{{ $c['name'] }}</div>
                            @if($c['brand'] || $c['model'])
                                <div class="b">
                                    @if($c['brand'])<span class="mi-chip y">{{ $c['brand'] }}</span>@endif
                                    @if($c['model'])<span class="mi-chip">{{ $c['model'] }}</span>@endif
                                </div>
                            @endif
                            <div class="go">Escolher <i class="bi bi-arrow-right"></i></div>
                        </button>
                    @endforeach
                </div>
            @elseif(!$mlSearchError)
                <div class="mi-empty" wire:loading.remove wire:target="searchMlCatalog,pickMlProduct">
                    <div class="ico"><i class="bi bi-bag-check"></i></div>
                    Busque o produto no catálogo do Mercado Livre para trazer fotos, descrição, ficha técnica, categoria, marca e medidas.
                </div>
            @endif
        @else
            {{-- Produto escolhido --}}
            <div class="mi-hero">
                <div class="pic">@if(!empty($mlData['pictures'][0]))<img src="{{ $mlData['pictures'][0] }}" alt="">@else<i class="bi bi-image text-3xl text-slate-300"></i>@endif</div>
                <div class="min-w-0">
                    <div class="n">{{ $mlData['name'] }}</div>
                    <div class="chips">
                        @if(!empty($mlData['brand']))<span class="mi-chip y"><i class="bi bi-award"></i>{{ $mlData['brand'] }}</span>@endif
                        @if(!empty($mlData['gtin']))<span class="mi-chip"><i class="bi bi-upc"></i>{{ $mlData['gtin'] }}</span>@endif
                        <span class="mi-chip"><i class="bi bi-hash"></i>{{ $mlData['id'] }}</span>
                    </div>
                    @if($mlCat)
                        <div class="mi-crumb"><i class="bi bi-diagram-3" style="font-size:.78rem;opacity:1"></i>
                            @foreach($mlCat['path'] as $k => $lvl)
                                @if($k)<i class="bi bi-chevron-right"></i>@endif
                                @if($loop->last)<b>{{ $lvl }}</b>@else<span>{{ $lvl }}</span>@endif
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($mlData['price']))
                        <div class="mi-price">Preço no ML hoje <b>R$ {{ number_format($mlData['price'], 2, ',', '.') }}</b></div>
                    @endif
                </div>
                <div class="acts">
                    @if($mlUrl)<a href="{{ $mlUrl }}" target="_blank" rel="noopener" class="mi-mini"><i class="bi bi-box-arrow-up-right"></i>Ver no ML</a>@endif
                    @if(count($mlCandidates) > 1)
                        <button type="button" class="mi-mini" wire:click="backToMlResults"><i class="bi bi-arrow-left-right"></i>Trocar produto</button>
                    @endif
                </div>
            </div>

            <div class="mi-bar">
                <div class="min-w-0">
                    <b>O que trazer</b>
                    <small class="block sm:inline sm:ml-1" x-text="n ? (n + (n === 1 ? ' item ligado' : ' itens ligados')) : 'nada ligado ainda'"></small>
                </div>
                <div class="r">
                    <button type="button" class="mi-mini" @click="setAll(true)"><i class="bi bi-check2-all"></i><span class="hidden sm:inline">Ligar tudo</span><span class="sm:hidden">Tudo</span></button>
                    <button type="button" class="mi-mini" @click="setAll(false)"><i class="bi bi-x-lg"></i><span class="hidden sm:inline">Desligar tudo</span><span class="sm:hidden">Nada</span></button>
                </div>
            </div>

            <div class="mi-grid">
                {{-- Coluna principal: fotos, descrição, ficha --}}
                <div class="mi-col">
                    @if(count($mlData['pictures']))
                    <div class="mi-card">
                        <div class="mi-ch" style="cursor:default">
                            <span class="ico" style="background:linear-gradient(135deg,#ec4899,#a855f7)"><i class="bi bi-images"></i></span>
                            <div class="tt"><b>Fotos</b><small><span x-text="photos"></span> de {{ count($mlData['pictures']) }} marcadas · vão para a galeria</small></div>
                            <button type="button" class="mi-mini" style="padding:.35rem .6rem" @click="photosAll(photos < {{ count($mlData['pictures']) }})" x-text="photos < {{ count($mlData['pictures']) }} ? 'Todas' : 'Nenhuma'"></button>
                        </div>
                        <div class="mi-cb">
                            <div class="mi-photos">
                                @foreach($mlData['pictures'] as $i => $url)
                                    <label class="mi-photo" wire:key="mp-{{ $i }}">
                                        <input type="checkbox" wire:model="mlPhotos.{{ $i }}" data-photo>
                                        <img src="{{ $url }}" alt="Foto {{ $i + 1 }}" loading="lazy">
                                        <span class="no">{{ $i + 1 }}</span>
                                        <span class="ck"><i class="bi bi-check-lg"></i></span>
                                        <span class="cap" x-show="cover === {{ $i }}" x-cloak>Capa</span>
                                    </label>
                                @endforeach
                            </div>
                            <label class="mi-row">
                                <i class="bi bi-star-fill text-amber-400"></i>
                                <span>Primeira foto marcada vira a <b>imagem principal</b></span>
                                <span class="mi-sw"><input type="checkbox" wire:model="mlTake.cover" data-cover data-keep><span></span></span>
                            </label>
                        </div>
                    </div>
                    @endif

                    @if($mlDesc !== '' || count($mlFeatures))
                    <div class="mi-card" x-data="{ all: false }">
                        <label class="mi-ch">
                            <span class="ico" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"><i class="bi bi-text-paragraph"></i></span>
                            <div class="tt"><b>Descrição</b><small>Substitui a descrição atual{{ count($mlFeatures) ? ' · com ' . count($mlFeatures) . ' destaques' : '' }}</small></div>
                            <span class="mi-sw"><input type="checkbox" wire:model="mlTake.desc" data-t="Descrição" data-key="desc"><span></span></span>
                        </label>
                        <div class="mi-cb">
                            @if($mlDesc !== '')
                                <div class="mi-desc" :class="all ? '' : 'cut'">{{ $mlDesc }}</div>
                            @endif
                            @if(count($mlFeatures))
                                <ul class="mi-feats" x-show="all || {{ $mlDesc === '' ? 'true' : 'false' }}" @if($mlDesc !== '') x-cloak @endif>
                                    @foreach($mlFeatures as $f)<li><i class="bi bi-check-circle-fill"></i><span>{{ $f }}</span></li>@endforeach
                                </ul>
                            @endif
                            @if(mb_strlen($mlDesc) > 320 || ($mlDesc !== '' && count($mlFeatures)))
                                <button type="button" class="mi-more" @click="all = !all"><span x-text="all ? 'Mostrar menos' : 'Ler tudo'"></span><i class="bi" :class="all ? 'bi-chevron-up' : 'bi-chevron-down'"></i></button>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(count($mlData['specs']))
                    <div class="mi-card" x-data="{ all: false }">
                        <label class="mi-ch">
                            <span class="ico" style="background:linear-gradient(135deg,#14b8a6,#0ea5e9)"><i class="bi bi-list-check"></i></span>
                            <div class="tt"><b>Ficha técnica</b><small>{{ count($mlData['specs']) }} itens · junta com a ficha atual</small></div>
                            <span class="mi-sw"><input type="checkbox" wire:model="mlTake.specs" data-t="Ficha técnica" data-key="specs"><span></span></span>
                        </label>
                        <div class="mi-cb">
                            <dl class="mi-specs">
                                @foreach($mlData['specs'] as $k => $s)
                                    <div @if($k >= $mlSpecsShown) x-show="all" x-cloak @endif><dt>{{ $s['n'] }}</dt><dd title="{{ $s['v'] }}">{{ $s['v'] }}</dd></div>
                                @endforeach
                            </dl>
                            @if(count($mlData['specs']) > $mlSpecsShown)
                                <button type="button" class="mi-more" @click="all = !all"><span x-text="all ? 'Mostrar menos' : 'Ver as {{ count($mlData['specs']) }}'"></span><i class="bi" :class="all ? 'bi-chevron-up' : 'bi-chevron-down'"></i></button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Coluna lateral: categoria e detalhes --}}
                <div class="mi-col">
                    @if($mlCat)
                    <div class="mi-card">
                        <label class="mi-ch">
                            <span class="ico" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="bi bi-tags"></i></span>
                            <div class="tt"><b>Categoria</b><small>Só se você quiser trocar</small></div>
                            <span class="mi-sw"><input type="checkbox" wire:model="mlTake.category" data-t="Categoria" data-key="category" data-keep><span></span></span>
                        </label>
                        <div class="mi-cb">
                            <div class="mi-val">{{ $mlCat['name'] }}</div>
                            @if(count($mlCat['path']) > 1)<div class="mi-now">{{ implode(' › ', array_slice($mlCat['path'], 0, -1)) }}</div>@endif
                            <select class="mi-sel" wire:model="mlCatChoice" @change="turnOn('category')" aria-label="Categoria do app">
                                <option value="new">+ Criar a categoria "{{ \Illuminate\Support\Str::limit($mlCat['name'], 40) }}"</option>
                                @foreach($mlCats as $cat)
                                    <option value="{{ $cat->id_category }}">Usar: {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="mi-now">
                                @if($mlNowCat)Hoje: <b>{{ $mlNowCat->name }}</b> · @endif Escolha onde ela entra no app
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($mlShowDims)
                    <div class="mi-card">
                        <label class="mi-ch">
                            <span class="ico" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="bi bi-rulers"></i></span>
                            <div class="tt"><b>Peso e medidas</b><small>Da {{ $mlData['dims']['from'] ?? 'embalagem' }} · usado no frete</small></div>
                            <span class="mi-sw"><input type="checkbox" wire:model="mlTake.dims" data-t="Peso e medidas" data-key="dims"><span></span></span>
                        </label>
                        <div class="mi-cb">
                            <div class="mi-dims">
                                <div><b>{{ isset($mlDims['weight_g']) ? ($mlDims['weight_g'] >= 1000 ? $mlNum($mlDims['weight_g'] / 1000, 2) . ' kg' : $mlNum($mlDims['weight_g'], 0) . ' g') : '–' }}</b><small>Peso</small></div>
                                <div><b>{{ isset($mlDims['length_cm']) ? $mlNum($mlDims['length_cm']) : '–' }}</b><small>Compr.</small></div>
                                <div><b>{{ isset($mlDims['width_cm']) ? $mlNum($mlDims['width_cm']) : '–' }}</b><small>Larg.</small></div>
                                <div><b>{{ isset($mlDims['height_cm']) ? $mlNum($mlDims['height_cm']) : '–' }}</b><small>Alt.</small></div>
                            </div>
                            <div class="mi-now">Medidas em cm.@if(!empty($mlNow['dims'])) Hoje o produto já tem medidas: serão trocadas.@endif</div>
                        </div>
                    </div>
                    @endif

                    @foreach([
                        ['k' => 'brand', 'l' => 'Marca', 'i' => 'bi-award', 'c' => '#eab308,#ca8a04', 'v' => $mlData['brand'] ?? null, 'now' => $mlNow['brand'] ?? ''],
                        ['k' => 'model', 'l' => 'Modelo', 'i' => 'bi-box', 'c' => '#64748b,#475569', 'v' => $mlData['model'] ?? null, 'now' => $mlNow['model'] ?? ''],
                        ['k' => 'gtin', 'l' => 'Código de barras', 'i' => 'bi-upc-scan', 'c' => '#0ea5e9,#2563eb', 'v' => $mlData['gtin'] ?? null, 'now' => $mlNow['barcode'] ?? ''],
                        ['k' => 'name', 'l' => 'Nome do catálogo', 'i' => 'bi-fonts', 'c' => '#f43f5e,#e11d48', 'v' => $mlData['name'] ?? null, 'now' => $mlNow['name'] ?? ''],
                    ] as $f)
                        @if(!empty($f['v']))
                        <div class="mi-card" wire:key="mi-f-{{ $f['k'] }}">
                            <label class="mi-ch">
                                <span class="ico" style="background:linear-gradient(135deg,{{ $f['c'] }})"><i class="bi {{ $f['i'] }}"></i></span>
                                <div class="tt">
                                    <b>{{ $f['l'] }}</b>
                                    <div class="mi-val" style="font-size:.82rem;margin-top:.15rem">{{ $f['v'] }}</div>
                                    @if(trim((string) $f['now']) !== '' && mb_strtolower(trim((string) $f['now'])) !== mb_strtolower(trim((string) $f['v'])))
                                        <div class="mi-now">Hoje: <s>{{ \Illuminate\Support\Str::limit($f['now'], 60) }}</s></div>
                                    @elseif(trim((string) $f['now']) !== '')
                                        <div class="mi-now"><i class="bi bi-check2"></i> Igual ao que já está</div>
                                    @endif
                                </div>
                                <span class="mi-sw"><input type="checkbox" wire:model="mlTake.{{ $f['k'] }}" data-t="{{ $f['l'] }}" data-key="{{ $f['k'] }}" @if($f['k'] === 'name') data-keep @endif><span></span></span>
                            </label>
                        </div>
                        @endif
                    @endforeach

                    @if(($famCount = $this->mlFamilyCount) > 0)
                    <div class="mi-card mi-fam">
                        <label class="mi-ch">
                            <span class="ico" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="bi bi-diagram-3"></i></span>
                            <div class="tt"><b>Levar também para as {{ $famCount }} outras {{ $famCount === 1 ? 'variação' : 'variações' }}</b>
                                <small>Fotos, descrição, ficha, marca e categoria. Nome, código e EAN de cada uma ficam como estão.</small></div>
                            <span class="mi-sw"><input type="checkbox" wire:model="mlToFamily"><span></span></span>
                        </label>
                    </div>
                    @endif
                </div>
            </div>

            @if($mlSearchError)<div class="mi-err"><i class="bi bi-exclamation-circle"></i><span>{{ $mlSearchError }}</span></div>@endif
        @endif
    </div>

    <div class="mi-f">
        @if(!empty($mlData))
            <div class="mi-sum"><i class="bi bi-bag-check" style="color:var(--mi-acc)"></i><span x-show="n" x-cloak>Vai trazer: <b x-text="text()"></b></span><span x-show="!n">Ligue pelo menos um item</span></div>
        @endif
        <button type="button" class="ep-btn ep-btn-ghost flex-1 sm:flex-none" wire:click="closeMlImport">Cancelar</button>
        @if(!empty($mlRowSet))
            <button type="button" class="ep-btn ep-btn-ghost flex-1 sm:flex-none" style="color:#e11d48" wire:click="clearMlRow" title="Tirar o que foi escolhido do ML para este produto"><i class="bi bi-x-circle"></i><span class="hidden sm:inline">Não usar o ML</span></button>
        @endif
        @if(!empty($mlData))
            <button type="button" class="ep-btn ep-btn-primary mi-go" wire:click="applyMlImport" wire:loading.attr="disabled" wire:target="applyMlImport" :disabled="!n">
                <span wire:loading.remove wire:target="applyMlImport"><i class="bi bi-download"></i> {{ !empty($mlUploadMode) ? 'Usar neste produto' : 'Trazer para o produto' }}</span>
                <span wire:loading wire:target="applyMlImport"><i class="bi bi-arrow-repeat animate-spin"></i> Trazendo...</span>
            </button>
        @endif
    </div>
</div>
@endif
