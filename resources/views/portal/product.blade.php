@php
    $owner = $ownerId;
    $d = $all[$item->id];
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);
    $catalogHome = route('portal.catalog', ['userId' => $owner]);
    $categoryUrl = $item->category_id ? route('portal.catalog', ['userId' => $owner, 'category' => $item->category_id]) : null;

    // WhatsApp da Identidade da loja; sem ele, o telefone da conta.
    $phone = preg_replace('/\D+/', '', (string) (\App\Services\Portal\StoreIdentity::for((int) $owner)['whatsapp'] ?: ($store?->phone ?? '')));
    if ($phone && strlen($phone) <= 11) { $phone = '55' . $phone; }

    // Prévia do link no WhatsApp: foto, nome e preço do produto.
    $ogImg = $d['img'] ?? null;
    if ($ogImg && ! str_starts_with($ogImg, 'http')) { $ogImg = url($ogImg); }
    if ($item->image && $item->image !== 'product-placeholder.png') {
        $ogImg = route('portal.catalog.preview-image', ['userId' => $owner, 'product' => $item->id]);
    }
    $og = [
        'title' => $d['name'],
        'description' => ($d['old'] ? 'De R$ ' . number_format($d['old'], 2, ',', '.') . ' por ' : '') . 'R$ ' . number_format($d['price'], 2, ',', '.')
            . ($d['pct'] ? ' (' . $d['pct'] . '% OFF)' : '') . ' · ' . ($store?->name ?: config('app.name')),
        'image' => $ogImg && ! str_ends_with($ogImg, 'product-placeholder.png') ? $ogImg : null,
        'url' => $d['url'],
    ];
    $int = number_format(floor($d['price']), 0, ',', '.');
    $cents = str_pad((string) (round($d['price'] * 100) % 100), 2, '0', STR_PAD_LEFT);
@endphp
@push('head')
    <link rel="canonical" href="{{ $d['url'] }}">
    <script type="application/ld+json">{!! \App\Services\Portal\ProductFeed::jsonLd($item, $d, $rating, \App\Services\Portal\StoreIdentity::for((int) $owner)['name'] ?: ($store?->name ?? config('app.name'))) !!}</script>
@endpush

<x-portal-catalog-layout :title="$d['name']" :store="$store" :owner-id="$owner" :og="$og">

@push('styles')
@include('portal.partials.catalog-styles')
<style>
    .pp-crumbs { display: flex; align-items: center; gap: 8px; padding: 12px 2px 10px; font-size: 13px; color: var(--ml-muted); min-width: 0; }
    .pp-back { flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 999px; background: var(--ml-chip); border: 1px solid var(--ml-line); color: var(--ml-text); font-weight: 600; }
    .pp-trail { display: flex; align-items: center; gap: 6px; min-width: 0; white-space: nowrap; overflow: hidden; }
    .pp-trail a { color: var(--ml-blue); font-weight: 600; }
    .pp-trail span:last-child { overflow: hidden; text-overflow: ellipsis; }
    .pp-trail i { font-size: 9px; opacity: .6; }

    .pp-main { padding: 8px 0 18px; }
    .pp-main .ml-gal { padding-top: 6px; }
    .pp-main .ml-gal-track > div { max-height: 56vh; }
    .pp-fav { position: absolute; right: 12px; top: 8px; z-index: 2; width: 42px; height: 42px; border-radius: 50%; border: 0; background: rgba(255,255,255,.95); box-shadow: 0 1px 6px rgba(0,0,0,.18); color: #8c8c8c; font-size: 19px; cursor: pointer; }
    .pp-fav.on { color: #ec4899; }
    .pp-share { top: 60px; color: var(--ml-muted); font-size: 17px; }
    .pp-share-row { display: flex; gap: 8px; margin-top: 8px; }
    .pp-share-row a, .pp-share-row button { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 40px; border-radius: 6px; border: 1px solid var(--ml-line); background: var(--ml-chip); color: var(--ml-text); font-size: 13px; font-weight: 600; cursor: pointer; }
    .pp-share-row .wa i { color: #128c4b; font-size: 16px; }
    .pp-kit { display: flex; align-items: center; gap: 10px; margin-top: 10px; padding: 11px 14px; border-radius: 12px; text-decoration: none; color: #fff; background: linear-gradient(120deg, #e11d48, #c026d3 60%, #7c3aed); }
    .pp-kit > span:first-child { font-size: 24px; }
    .pp-kit b { display: block; font-size: 14px; }
    .pp-kit small { display: block; font-size: 12px; opacity: .9; }
    .pp-kit i { margin-left: auto; }
    .pp-info { padding: 10px 16px 0; }
    .pp-info .ml-cat-line { font-size: 12px; color: var(--ml-muted); }
    .pp-info h1 { margin: 6px 0 10px; font-size: 20px; font-weight: 500; line-height: 1.3; color: var(--ml-text); }
    .pp-info .ml-price { font-size: 34px; }
    .pp-info .ml-price sup { font-size: 16px; }
    .pp-info .ml-pct { font-size: 17px; }
    .pp-pix { display: block; margin-top: 6px; font-size: 14px; }
    .pp-pts { margin-top: 6px; font-size: 13px; font-weight: 600; color: var(--ml-text); }
    .pp-pts i { color: #f5b301; }
    .pp-out { display: flex; gap: 10px; align-items: flex-start; margin-top: 12px; padding: 12px; border-radius: 8px; background: #fff4ec; color: #9a3d0b; font-size: 14px; }
    .pp-out i { margin-top: 2px; }
    .pp-out small { display: block; color: #a8673f; font-size: 12px; margin-top: 2px; }

    .pp-opts { margin-top: 16px; }
    .pp-opts-label { font-size: 14px; margin-bottom: 8px; }
    .pp-opts-label b { font-weight: 700; }
    .pp-opts-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .pp-opt { min-width: 48px; min-height: 42px; padding: 6px 14px; border: 1px solid var(--ml-line); border-radius: 8px; background: var(--ml-chip); font-size: 14px; font-weight: 600; color: var(--ml-text); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .pp-opt img { width: 28px; height: 28px; object-fit: contain; border-radius: 4px; }
    .pp-opt.on { border: 2px solid var(--ml-blue); color: var(--ml-blue); padding: 5px 13px; background: var(--ml-blue-soft); }
    .pp-opt.off { color: #a3a3a3; border-style: dashed; }
    .pp-opt.off span { text-decoration: line-through; }

    .pp-about { margin-top: 12px; padding: 18px 16px; }
    .pp-about h2 { margin: 0 0 10px; font-size: 18px; font-weight: 700; }
    .pp-about h2 + p { margin: 0; white-space: pre-line; color: var(--ml-muted); font-size: 14px; line-height: 1.6; }
    .pp-about .pp-sub { margin-top: 20px; }
    .pp-about [style*="display: none"] + .pp-sub { margin-top: 0; }
    .pp-about .ml-specs { margin-top: 0; }

    .pp-related { margin-top: 12px; }
    .pp-related .ml-sec-head h2 i { color: var(--ml-blue); }

    /* Barra de compra fixa no celular (no lugar da barra do carrinho) */
    .pp-buybar { position: fixed; z-index: 70; left: 0; right: 0; bottom: 0; background: var(--ml-card); box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); display: flex; align-items: center; gap: 10px; }
    .pp-buybar-sum { flex: 1; min-width: 0; }
    .pp-buybar-sum small { display: block; color: var(--ml-muted); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pp-buybar-sum strong { font-size: 19px; font-weight: 700; }
    .pp-buybar .ml-btn { width: auto; height: 46px; padding: 0 18px; font-size: 14px; }
    .pp-buybar .pp-cart-btn { width: 46px; padding: 0; position: relative; }
    .pp-buybar .pp-cart-btn .ml-count { top: -6px; right: -6px; background: var(--ml-blue); color: #fff; }
    @media (max-width: 767px) { .pp .ml-cartbar { display: none !important; } }

    @media (min-width: 768px) {
        .pp-buybar { display: none; }
        .pp-main { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 12px; align-items: start; padding: 20px 0 24px; }
        .pp-main { grid-template-areas: "gal head" "gal info"; grid-template-rows: auto 1fr; row-gap: 0; }
        .pp-main .ml-gal-wrap { grid-area: gal; position: sticky; top: 80px; }
        .pp-head { grid-area: head; padding: 4px 24px 0 8px; }
        .pp-head h1 { font-size: 24px; font-weight: 500; margin-bottom: 6px; }
        .pp-info { grid-area: info; }
        .pp-main .ml-gal-track > div { max-height: 520px; }
        .pp-main .ml-thumbs { display: flex; gap: 8px; padding: 10px 16px 0; flex-wrap: wrap; }
        .pp-main .ml-thumbs button { width: 60px; height: 60px; border: 1px solid var(--ml-line); border-radius: 6px; background: #fff; padding: 3px; cursor: pointer; }
        .pp-main .ml-thumbs button.on { border: 2px solid var(--ml-blue); }
        .pp-main .ml-thumbs img { width: 100%; height: 100%; object-fit: contain; }
        .pp-main .ml-dots { display: none; }
        .pp-info { padding: 4px 24px 0 8px; }
        .pp-info h1 { font-size: 24px; }
        .pp-about { padding: 24px; }
        .pp-about-in { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 32px; }
        .pp-about .pp-sub { margin-top: 0; }
        .pp-about-in:has(> div[style*="display: none"]) { grid-template-columns: minmax(0, 1fr); }
        .pp-about-in:has(> div[style*="display: none"]) .ml-specs { max-width: 720px; }
        .pp-related .ml-rail .ml-card { flex-basis: 184px; }
    }
    @media (min-width: 1180px) {
        .pp-related .ml-rail .ml-card { flex-basis: 196px; }
    }
    /* Calcular frete */
    .pp-ship { margin-top: 14px; padding: 12px; border: 1px solid var(--ml-line); border-radius: 8px; }
    .pp-ship-head { display: flex; align-items: center; gap: 8px; font-size: 14px; }
    .pp-ship-head i { color: var(--ml-green); }
    .pp-ship-head a { margin-left: auto; font-size: 12px; color: var(--ml-blue); }
    .pp-ship-row { display: flex; gap: 8px; margin-top: 10px; }
    .pp-ship-row input { flex: 1; min-width: 0; height: 42px; border: 1px solid #ccc; border-radius: 6px; padding: 0 12px; font-size: 16px; letter-spacing: .04em; }
    .pp-ship-row input:focus { outline: 2px solid var(--ml-blue); outline-offset: -1px; border-color: transparent; }
    .pp-ship-row .ml-btn { width: auto; height: 42px; padding: 0 18px; }
    .pp-ship-res { margin-top: 10px; display: grid; gap: 3px; font-size: 14px; }
    .pp-ship-res b { font-weight: 700; }
    .pp-ship-res .free { color: var(--ml-green); }
    .pp-ship-res small { font-size: 12px; color: var(--ml-muted); }
    .pp-ship-res small.free { color: var(--ml-green); font-weight: 600; }
    .pp-ship-err { margin: 10px 0 0; font-size: 13px; color: #c2410c; }
    /* Estrelas, compre junto e avaliações */
    .rv-stars { --v: 0; display: inline-block; letter-spacing: 1px; line-height: 1; background: linear-gradient(90deg, #f5b301 calc(var(--v) / 5 * 100%), #d4d4d8 0); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .pp-rate { display: inline-flex; align-items: center; gap: 6px; margin: 2px 0 6px; font-size: 13px; color: var(--ml-muted); text-decoration: none; }
    .pp-rate .rv-stars { font-size: 15px; }
    .pp-rate b { color: var(--ml-text); }
    .pp-buy2, .pp-reviews { padding: 16px; }
    @media (min-width: 768px) { .pp-buy2, .pp-reviews { padding: 20px 24px; } }
    .pp-buy2 h2, .pp-reviews h2 { font-size: 18px; font-weight: 800; margin: 0 0 12px; }
    .pp-buy2-row { display: flex; align-items: stretch; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
    .pp-buy2-item { position: relative; flex: 0 0 150px; display: flex; flex-direction: column; gap: 6px; padding: 10px; border: 1px solid var(--ml-line); border-radius: var(--ml-radius); background: var(--ml-card); cursor: pointer; transition: opacity .2s, border-color .2s; }
    .pp-buy2-item.off { opacity: .45; }
    .pp-buy2-item.on { border-color: var(--ml-blue); }
    .pp-buy2-item img, .pp-buy2-item .ph { width: 100%; aspect-ratio: 1; object-fit: contain; border-radius: 6px; background: #f5f5f5; }
    .pp-buy2-item b { font-size: 12px; font-weight: 500; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pp-buy2-item span { font-size: 15px; font-weight: 700; }
    .pp-buy2-item input { position: absolute; top: 8px; left: 8px; width: 18px; height: 18px; accent-color: var(--ml-blue); }
    .pp-buy2-plus { align-self: center; flex-shrink: 0; font-size: 20px; color: var(--ml-muted); }
    .pp-buy2-sum { flex: 0 0 200px; align-self: center; display: flex; flex-direction: column; gap: 8px; padding-left: 8px; }
    .pp-buy2-sum small { font-size: 13px; color: var(--ml-muted); }
    .pp-buy2-sum strong { font-size: 24px; font-weight: 800; }
    @media (max-width: 640px) {
        .pp-buy2-row { flex-wrap: wrap; overflow: visible; gap: 4px; }
        .pp-buy2-item { flex: 1 1 0; min-width: 0; padding: 6px; }
        .pp-buy2-item a { display: none; }
        .pp-buy2-plus { font-size: 14px; }
        .pp-buy2-sum { flex: 1 0 100%; padding: 10px 0 0; flex-direction: row; flex-wrap: wrap; align-items: center; column-gap: 10px; }
        .pp-buy2-sum .ml-btn { flex: 1 0 100%; }
    }
    .pp-reviews-top { display: grid; gap: 18px; grid-template-columns: 1fr; margin-bottom: 16px; }
    @media (min-width: 720px) { .pp-reviews-top { grid-template-columns: 220px 1fr; align-items: start; } }
    .pp-avg { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
    .pp-hist { align-self: stretch; }
    .pp-avg strong { font-size: 44px; font-weight: 800; line-height: 1; }
    .pp-avg .rv-stars { font-size: 22px; }
    .pp-avg small { color: var(--ml-muted); font-size: 13px; }
    .pp-hist { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; }
    .pp-hist div { display: grid; grid-template-columns: 30px 1fr 26px; align-items: center; gap: 6px; font-size: 12px; color: var(--ml-muted); }
    .pp-hist i { display: block; height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; }
    .pp-hist i b { display: block; height: 100%; background: #f5b301; }
    .pp-rv-form { border: 1px solid var(--ml-line); border-radius: var(--ml-radius); padding: 14px; display: flex; flex-direction: column; gap: 10px; }
    .pp-rv-form p { margin: 0; font-size: 14px; font-weight: 700; }
    .pp-pick { display: inline-flex; flex-direction: row-reverse; justify-content: flex-end; gap: 2px; }
    .pp-pick input { position: absolute; opacity: 0; pointer-events: none; }
    .pp-pick label { font-size: 32px; line-height: 1; color: #d4d4d8; cursor: pointer; transition: transform .15s, color .15s; }
    .pp-pick label:hover, .pp-pick label:hover ~ label, .pp-pick input:checked ~ label { color: #f5b301; }
    .pp-pick label:active { transform: scale(.9); }
    .pp-pick input:focus-visible + label { outline: 2px solid var(--ml-blue); border-radius: 4px; }
    .pp-rv-form textarea { width: 100%; min-height: 80px; border: 1px solid var(--ml-line); border-radius: 8px; padding: 10px; font: inherit; font-size: 14px; resize: vertical; }
    .pp-rv-form .ml-btn { align-self: flex-start; }
    .pp-rv-msg { padding: 10px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #ecfdf5; color: #047857; }
    .pp-rv-msg.err { background: #fef2f2; color: #b91c1c; }
    .pp-rv-list { display: flex; flex-direction: column; }
    .pp-rv { padding: 14px 0; border-top: 1px solid var(--ml-line); }
    .pp-rv-head { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 13px; }
    .pp-rv-head b { font-weight: 700; }
    .pp-rv-head small { color: var(--ml-muted); }
    .pp-rv-ok { color: var(--ml-green); font-weight: 600; font-size: 12px; }
    .pp-rv p { margin: 6px 0 0; font-size: 14px; line-height: 1.5; white-space: pre-line; }
    .pp-rv-reply { margin-top: 8px; padding: 8px 12px; border-left: 3px solid var(--ml-blue); background: var(--ml-blue-soft); border-radius: 0 8px 8px 0; font-size: 13px; }
    .pp-rv-reply b { display: block; font-size: 12px; margin-bottom: 2px; }
    .pp-rv-empty { color: var(--ml-muted); font-size: 14px; margin: 0; }

    /* Nome antes da foto */
    .pp-head { padding: 4px 16px 8px; }
    .pp-head .ml-cat-line { font-size: 12px; color: var(--ml-muted); }
    .pp-head h1 { margin: 4px 0 4px; font-size: 19px; font-weight: 600; line-height: 1.3; color: var(--ml-text); }
    .pp-head .pp-rate { margin: 2px 0 0; }

    /* Foto ampliada */
    .pp-zoomable { cursor: zoom-in; -webkit-tap-highlight-color: transparent; }
    .pp-zoom-btn { position: absolute; right: 12px; bottom: 10px; z-index: 2; width: 42px; height: 42px; border-radius: 50%; border: 0; background: rgba(255,255,255,.95); box-shadow: 0 1px 6px rgba(0,0,0,.18); color: #555; font-size: 17px; cursor: pointer; }
    html.pp-lock, html.pp-lock body { overflow: hidden; overscroll-behavior: none; }
    .pp-lb { position: fixed; inset: 0; z-index: 200; background: #0b0b0d; color: #fff; display: flex; flex-direction: column; user-select: none; -webkit-user-select: none; }
    .pp-lb-top { display: flex; align-items: center; gap: 12px; padding: calc(8px + env(safe-area-inset-top)) 10px 8px 16px; }
    .pp-lb-n { flex-shrink: 0; font-size: 13px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.12); }
    .pp-lb-name { flex: 1; min-width: 0; font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; opacity: .9; }
    .pp-lb-x { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255,255,255,.12); color: #fff; font-size: 20px; cursor: pointer; }
    .pp-lb-x:hover, .pp-lb-arrow:hover { background: rgba(255,255,255,.22); }
    .pp-lb-stage { position: relative; flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; touch-action: none; cursor: zoom-in; }
    .pp-lb-stage.zoomed { cursor: grab; }
    .pp-lb-stage.zoomed:active { cursor: grabbing; }
    .pp-lb-stage img { max-width: 100%; max-height: 100%; object-fit: contain; will-change: transform; pointer-events: none; transform-origin: center center; background: #fff; border-radius: 4px; }
    .pp-lb-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border-radius: 50%; border: 0; background: rgba(255,255,255,.12); color: #fff; font-size: 18px; cursor: pointer; display: none; }
    .pp-lb-arrow.prev { left: 16px; }
    .pp-lb-arrow.next { right: 16px; }
    .pp-lb-bottom { padding: 8px 12px calc(10px + env(safe-area-inset-bottom)); display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .pp-lb-thumbs { display: flex; gap: 6px; max-width: 100%; overflow-x: auto; scrollbar-width: none; padding: 2px; }
    .pp-lb-thumbs::-webkit-scrollbar { display: none; }
    .pp-lb-thumbs button { flex: 0 0 52px; height: 52px; padding: 2px; border-radius: 6px; border: 2px solid transparent; background: #fff; opacity: .55; cursor: pointer; }
    .pp-lb-thumbs button.on { border-color: #3483fa; opacity: 1; }
    .pp-lb-thumbs img { width: 100%; height: 100%; object-fit: contain; }
    .pp-lb-hint { font-size: 12px; opacity: .65; text-align: center; }
    @media (min-width: 768px) {
        .pp-lb-stage { margin: 0 80px; }
        .pp-lb-arrow { display: block; }
        .pp-lb-thumbs button { flex-basis: 64px; height: 64px; }
    }
    @media (max-width: 767px) { .pp-lb-stage img { border-radius: 0; } }

</style>
@endpush

<div class="ml-wrap pp" x-data="mlCatalog(@js($all), @js($cartUrl), @js($stockMap), {
        p: @js($d),
        setup() { if (! this.p.buyable) this.qty = 0; mlSeenPush(@js($owner), this.p.id); },
        zoom: false, zoomAt: 0, zoomList: [],
        zoomOpen(i, list = null) {
            this.zoomList = list && list.length ? list : this.p.imgs;
            if (! this.zoomList.length) return;
            this.zoomAt = Math.min(Math.max(i, 0), this.zoomList.length - 1);
            this.zoom = true;
            document.documentElement.classList.add('pp-lock');
        },
        zoomClose() {
            this.zoom = false;
            document.documentElement.classList.remove('pp-lock');
            if (this.zoomList === this.p.imgs && this.zoomAt !== this.slide) this.goTo(this.zoomAt);
        },
        async share() {
            const data = { title: this.p.name, text: this.p.name + ' por ' + this.money(this.p.price), url: this.p.url };
            if (navigator.share) {
                try { await navigator.share(data); } catch (e) {}
                return;
            }
            try { await navigator.clipboard.writeText(this.p.url); this.say('Link copiado'); }
            catch (e) { prompt('Copie o link do produto:', this.p.url); }
        },
        pick(id) {
            const next = this.products[id];
            if (! next || next.id === this.p.id) return;
            this.p = next;
            this.qty = next.buyable ? 1 : 0;
            this.slide = 0;
            if (this.$refs.track) this.$refs.track.scrollLeft = 0;
            try { history.replaceState(null, '', next.url); } catch (e) {}
            mlSeenPush(@js($owner), next.id);
            document.title = next.name + ' · ' + @js($store?->name ?: config('app.name'));
        },
    })" @keydown.escape.window="zoom ? zoomClose() : closeCart()" @open-cart.window="openCart()" @pp-buy.window="document.getElementById('comprar').scrollIntoView({ behavior: 'smooth', block: 'start' })">

    <nav class="pp-crumbs" aria-label="Você está em">
        <a href="{{ $catalogHome }}" class="pp-back" onclick="if (document.referrer.indexOf(location.host + '/portal/catalogo') !== -1 && history.length > 1) { history.back(); return false; }">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <div class="pp-trail">
            <a href="{{ $catalogHome }}">Catálogo</a>
            @if($categoryUrl)
                <i class="fas fa-chevron-right"></i>
                <a href="{{ $categoryUrl }}">{{ $item->category?->name }}</a>
            @endif
        </div>
    </nav>

    @if($special)
        @include('portal.partials.product-special-styles')
        @include('portal.partials.product-special', ['where' => 'topo', 'name' => $d['name'], 'photos' => $d['imgs'] ?? [], 'phone' => $phone])
    @endif

    <section class="ml-box pp-main" id="comprar">
        {{-- Nome antes da foto (no celular fica em cima; nas telas largas, no topo da coluna da direita). --}}
        <div class="pp-head" data-testid="page-head">
            <div class="ml-cat-line" x-text="[p.cat, p.brand].filter(Boolean).join(' · ') || 'Produto'">{{ collect([$d['cat'], $d['brand']])->filter()->join(' · ') ?: 'Produto' }}</div>
            <h1 x-text="p.name">{{ $d['name'] }}</h1>
            @if($rating)
                <a href="#avaliacoes" class="pp-rate" data-testid="page-rating"><span class="rv-stars" style="--v: {{ $rating['avg'] }}">★★★★★</span><b>{{ number_format($rating['avg'], 1, ',', '') }}</b> ({{ $rating['n'] }} {{ $rating['n'] === 1 ? 'avaliação' : 'avaliações' }})</a>
            @endif
        </div>

        <div class="ml-gal-wrap">
            <div class="ml-gal">
                <div class="ml-gal-track" x-ref="track" @scroll.debounce.60ms="slide = Math.round($el.scrollLeft / $el.clientWidth)">
                    <template x-if="!p.imgs.length">
                        <div><div class="ml-ph"><i class="fas fa-image"></i></div></div>
                    </template>
                    <template x-for="(src, i) in p.imgs" :key="p.id + '-' + i">
                        <div><img :src="src" :alt="p.name" :loading="i ? 'lazy' : 'eager'" x-on:error="p.imgs = p.imgs.filter(x => x !== src)"
                                  class="pp-zoomable" @click="zoomOpen(i)" title="Ampliar foto" data-testid="page-photo"></div>
                    </template>
                </div>
                <button type="button" class="pp-zoom-btn" x-show="p.imgs.length" @click="zoomOpen(slide)" aria-label="Ampliar foto" data-testid="page-zoom"><i class="fas fa-magnifying-glass-plus"></i></button>
                <span class="ml-gal-n" x-show="p.imgs.length > 1" x-text="(slide + 1) + ' / ' + p.imgs.length"></span>
                <button type="button" class="pp-fav" :class="{ on: $store.favs.has(p.id) }" @click="fav(p.id)"
                        :aria-pressed="$store.favs.has(p.id)" :aria-label="$store.favs.has(p.id) ? 'Tirar dos favoritos' : 'Salvar nos favoritos'" data-testid="page-fav">
                    <i class="fa-heart" :class="$store.favs.has(p.id) ? 'fas' : 'far'"></i>
                </button>
                <button type="button" class="pp-fav pp-share" @click="share()" aria-label="Compartilhar produto" title="Compartilhar" data-testid="page-share">
                    <i class="fas fa-share-nodes"></i>
                </button>
            </div>
            <div class="ml-dots" x-show="p.imgs.length > 1">
                <template x-for="(src, i) in p.imgs" :key="p.id + '-' + i"><i :class="{ on: i === slide }"></i></template>
            </div>
            <div class="ml-thumbs" x-show="p.imgs.length > 1">
                <template x-for="(src, i) in p.imgs" :key="p.id + '-' + i">
                    <button type="button" :class="{ on: i === slide }" @click="goTo(i)" :aria-label="'Foto ' + (i + 1)"><img :src="src" alt=""></button>
                </template>
            </div>
        </div>

        <div class="pp-info">
            <span class="ml-deal" x-show="p.pct" @if(! $d['pct']) x-cloak @endif>Oferta do dia</span>
            <div class="ml-old" x-show="p.old" x-text="money(p.old)" @if(! $d['old']) x-cloak @endif>@if($d['old'])R$ {{ number_format($d['old'], 2, ',', '.') }}@endif</div>
            <div class="ml-price-row" x-show="p.price > 0" @if(! ($d['price'] > 0)) x-cloak @endif>
                <span class="ml-price"><span class="cur">R$</span><span x-text="intPart(p.price)">{{ $int }}</span><sup x-show="cents(p.price) !== '00'" x-text="cents(p.price)" @if($cents === '00') x-cloak @endif>{{ $cents }}</sup></span>
                <span class="ml-pct" x-show="p.pct" x-text="p.pct + '% OFF'" @if(! $d['pct']) x-cloak @endif>{{ $d['pct'] }}% OFF</span>
            </div>
            <div class="ml-pix pp-pix" x-show="p.pix" @if(empty($d['pix'])) x-cloak @endif data-testid="page-pix"><i class="fas fa-qrcode"></i> <span x-text="p.pix ? 'ou ' + money(p.pix) + ' no Pix (' + @js(rtrim(rtrim(number_format(\App\Services\Portal\Rewards::for((int) $owner)['pix'], 2, ',', ''), '0'), ',')) + '% de desconto)' : ''"></span></div>
            <div class="pp-pts" x-show="p.pts > 0" @if(empty($d['pts'])) x-cloak @endif data-testid="page-points"><i class="fas fa-star"></i> <span x-text="'Ganhe ' + Number(p.pts).toLocaleString('pt-BR') + ' pontos comprando este produto'"></span></div>
            <div class="ml-ask" x-show="!(p.price > 0)" @if($d['price'] > 0) x-cloak @endif>Preço sob consulta</div>
            @if($family->count() > 1 && $family->pluck('price_sale')->unique()->count() > 1)
                <div class="ml-stock" style="margin-top:4px">A partir de R$ {{ number_format($minPrice, 2, ',', '.') }}, conforme {{ mb_strtolower($attribute) }}</div>
            @endif
            <div class="ml-ends" x-show="p.ends" style="margin-top:6px" x-cloak><i class="fas fa-clock"></i> <span x-text="p.ends"></span></div>

            <div class="pp-out" x-show="!p.buyable" x-cloak data-testid="sold-out">
                <i class="fas fa-box-open"></i>
                <span><b>Esgotado no momento</b><small>Toque no coração para salvar. Avisamos quando voltar ao estoque.</small></span>
            </div>

            @if($family->count() > 1 && $variants['mode'] === 'lista')
                {{-- Variações em lista suspensa colorida (Vitrine › Páginas de produto) --}}
                @include('portal.partials.variants-dropdown-styles')
                @php $vThumbs = $family->map(fn ($v) => $all[$v->id]['img'])->filter()->unique()->count() > 1; @endphp
                <div class="pp-vd" x-data="{ vOpen: false }" @click.outside="vOpen = false" @keydown.escape.stop="vOpen = false"
                     :class="{ open: vOpen }" style="--c1: {{ $variants['c1'] }}; --c2: {{ $variants['c2'] }}" data-testid="variations-dropdown">
                    <button type="button" class="pp-vd-btn" @click="vOpen = ! vOpen" :aria-expanded="vOpen" aria-haspopup="listbox" data-testid="variations-toggle">
                        <span class="pp-vd-av">
                            @if($vThumbs)<img :src="p.img" alt="" x-show="p.img">@endif
                            <i class="fas fa-layer-group" @if($vThumbs) x-show="!p.img" @endif></i>
                        </span>
                        <span class="pp-vd-txt"><small>{{ $attribute }}</small><b x-text="p.opt">{{ $d['opt'] ?? '' }}</b></span>
                        <span class="pp-vd-n">{{ $family->count() }} opções</span>
                        <span class="pp-vd-chev"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div class="pp-vd-panel" role="listbox" aria-label="{{ $attribute }}" x-show="vOpen" x-cloak
                         x-transition:enter="pp-vd-in" x-transition:enter-start="pp-vd-from" x-transition:leave="pp-vd-in" x-transition:leave-end="pp-vd-from">
                        @foreach($family as $v)
                            @php $o = $all[$v->id]; @endphp
                            <button type="button" class="pp-vd-item" role="option" :aria-selected="p.id === {{ $v->id }}"
                                    :class="{ on: p.id === {{ $v->id }}, off: !products[{{ $v->id }}].buyable }"
                                    @click="pick({{ $v->id }}); vOpen = false" data-testid="variation-item">
                                <span class="pp-vd-ic">
                                    @if($vThumbs && $o['img'])<img src="{{ $o['img'] }}" alt="" loading="lazy" onerror="this.remove()">@else<i class="fas fa-circle"></i>@endif
                                </span>
                                <span class="pp-vd-name">{{ $o['opt'] }}</span>
                                <span class="pp-vd-price">{{ $o['buyable'] ? ($o['price'] > 0 ? 'R$ ' . number_format($o['price'], 2, ',', '.') : '') : 'Esgotado' }}</span>
                                <i class="fas fa-check pp-vd-ok"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif($family->count() > 1)
                <div class="pp-opts" data-testid="variations">
                    <div class="pp-opts-label">{{ $attribute }}: <b x-text="p.opt">{{ $d['opt'] ?? '' }}</b></div>
                    <div class="pp-opts-list" role="radiogroup" aria-label="{{ $attribute }}">
                        @php $showThumbs = $family->map(fn ($v) => $all[$v->id]['img'])->filter()->unique()->count() > 1; @endphp
                        @foreach($family as $v)
                            @php $o = $all[$v->id]; @endphp
                            <button type="button" class="pp-opt" role="radio" :aria-checked="p.id === {{ $v->id }}"
                                    :class="{ on: p.id === {{ $v->id }}, off: !products[{{ $v->id }}].buyable }"
                                    @click="pick({{ $v->id }})" title="{{ $o['buyable'] ? $o['opt'] : $o['opt'] . ' (esgotado)' }}">
                                @if($showThumbs && $o['img'])<img src="{{ $o['img'] }}" alt="" loading="lazy" onerror="this.remove()">@endif
                                <span>{{ $o['opt'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="ml-perk" x-show="p.order" x-cloak>
                <i class="fas fa-clock"></i>
                <span><b x-text="p.order"></b><small>Feito depois do pedido. A loja confirma o prazo com você.</small></span>
            </div>
            <div class="ml-perk" x-show="p.old && p.buyable" x-cloak>
                <i class="fas fa-piggy-bank"></i>
                <span>Você economiza <b x-text="money((p.old - p.price) * Math.max(qty, 1))"></b></span>
            </div>
            @if($shipOn)
                @php $myCep = \App\Models\PortalShippingZone::formatCep(Auth::guard('portal')->user()?->cep); @endphp
                <div class="pp-ship" x-data="ppShip(@js(route('portal.catalog.shipping', ['userId' => $ownerId])), @js($myCep), @js($ownerId))" data-testid="ship-box">
                    <div class="pp-ship-head"><i class="fas fa-truck-fast"></i><b>Calcular frete</b><a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener">Não sei meu CEP</a></div>
                    <form class="pp-ship-row" @submit.prevent="calc(p.price * Math.max(qty, 1))">
                        <input type="text" inputmode="numeric" autocomplete="postal-code" maxlength="9" placeholder="00000-000" x-model="cep" @input="cep = mask(cep)" aria-label="CEP" data-testid="ship-cep">
                        <button type="submit" class="ml-btn ml-btn-soft" :disabled="busy || cep.replace(/\D/g, '').length !== 8" data-testid="ship-calc" x-text="busy ? '...' : 'Calcular'"></button>
                    </form>
                    <template x-if="res && res.ok">
                        <div class="pp-ship-res" data-testid="ship-result">
                            <span><b x-text="res.fee > 0 ? money(res.fee) : 'Frete grátis'" :class="res.fee > 0 ? '' : 'free'"></b> para <span x-text="res.place"></span></span>
                            <small x-show="res.eta" x-text="res.eta"></small>
                            <small class="free" x-show="res.fee > 0 && res.freeAbove" x-text="'Frete grátis a partir de ' + money(res.freeAbove) + ' em produtos'"></small>
                            <small>Ou retire com a loja, sem custo.</small>
                        </div>
                    </template>
                    <p class="pp-ship-err" x-show="res && !res.ok" x-text="res?.message" data-testid="ship-error"></p>
                </div>
            @else
                <div class="ml-perk">
                    <i class="fas fa-truck-fast"></i>
                    <span><b>Combine a entrega</b> com a loja<small>Retirada ou envio, você escolhe ao finalizar.</small></span>
                </div>
            @endif
            <div class="ml-perk">
                <i class="fas fa-shield-halved"></i>
                <span>Compra direta com <b style="color:var(--ml-text)">{{ $store?->name ?: 'a loja' }}</b><small>Seu pedido chega para a loja confirmar.</small></span>
            </div>

            <div class="ml-qty" x-show="p.buyable">
                <span class="ml-qty-label">Quantidade<small x-text="!p.order && p.stock <= 3 ? '(últimas unidades)' : ''"></small></span>
                <div class="ml-step">
                    <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1" aria-label="Menos"><i class="fas fa-minus"></i></button>
                    <span x-text="qty">1</span>
                    <button type="button" @click="qty = Math.min(p.stock || 999, qty + 1)" :disabled="p.stock && qty >= p.stock" aria-label="Mais"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <div class="ml-actions" style="margin-top:14px">
                <template x-if="p.buyable">
                    <div class="ml-actions">
                        <button type="button" class="ml-btn ml-btn-primary" @click="buyNow()" data-testid="buy-now">Comprar agora</button>
                        <button type="button" class="ml-btn ml-btn-soft" @click="add(p, qty)" data-testid="add-cart">
                            <i class="fas fa-cart-plus"></i> Adicionar ao carrinho
                        </button>
                    </div>
                </template>
                <template x-if="!p.buyable">
                    <button type="button" class="ml-btn ml-btn-primary" @click="fav(p.id)" x-text="$store.favs.has(p.id) ? 'Salvo: avisamos quando chegar' : 'Avise-me quando chegar'"></button>
                </template>
                @if($phone)
                    <a class="ml-btn ml-btn-wa" target="_blank" rel="noopener"
                       :href="'https://wa.me/{{ $phone }}?text=' + encodeURIComponent('Olá! Tenho interesse em: ' + p.name + '\n' + p.url)">
                        <i class="fab fa-whatsapp"></i> Perguntar pelo WhatsApp
                    </a>
                @endif
                @php $cqOn = \Illuminate\Support\Facades\Schema::hasTable('quotes') && \App\Services\Quotes\QuoteService::settings((int) $item->user_id)['portal_requests']; @endphp
                @if($cqOn)
                    <a class="ml-btn ml-btn-soft" href="{{ route('portal.custom-quote.create', ['userId' => $item->user_id, 'produto' => $item->id]) }}" data-testid="other-color">
                        <i class="fas fa-palette"></i> {{ $item->hasVariations() ? 'Quero em outra cor' : 'Quero em outra cor ou medida' }}
                    </a>
                @endif
            </div>
            <div class="pp-share-row">
                <a class="wa" target="_blank" rel="noopener" :href="'https://wa.me/?text=' + encodeURIComponent(p.name + ' ' + p.url)" data-testid="share-wa">
                    <i class="fab fa-whatsapp"></i> Enviar para alguém
                </a>
                <button type="button" @click="navigator.clipboard ? navigator.clipboard.writeText(p.url).then(() => say('Link copiado'), () => share()) : share()" data-testid="share-copy">
                    <i class="fas fa-link"></i> Copiar link
                </button>
            </div>
            @if(\App\Services\Portal\KitService::enabled((int) $owner))
                <a class="pp-kit" x-show="p.buyable" :href="@js(route('portal.kit', ['userId' => $owner])) + '?produto=' + p.id" data-testid="product-kit">
                    <span aria-hidden="true">🎁</span><span><b>Montar um kit com este produto</b><small>Cesta, balão, doces e cartão</small></span><i class="fas fa-chevron-right"></i>
                </a>
            @endif
        </div>
    </section>

    @if($special)
        @include('portal.partials.product-special', ['where' => 'meio', 'name' => $d['name'], 'photos' => $d['imgs'] ?? [], 'phone' => $phone])
    @endif

    {{-- Foto ampliada: toque/clique abre; pinça, toque duplo, roda do mouse e clique ampliam; arrasta para mover; desliza para trocar ou para baixo para fechar. --}}
    <template x-teleport="body">
        <div class="pp-lb" x-show="zoom" x-cloak x-transition.opacity.duration.150ms role="dialog" aria-modal="true" :aria-label="'Fotos de ' + p.name"
             x-data="ppZoom()" x-effect="if (zoom) reset(true)" @keydown.window="zoom && key($event)" data-testid="zoom">
            <div class="pp-lb-top">
                <span class="pp-lb-n" x-text="(zoomAt + 1) + ' / ' + zoomList.length"></span>
                <b class="pp-lb-name" x-text="p.name"></b>
                <button type="button" class="pp-lb-x" @click="zoomClose()" aria-label="Fechar" data-testid="zoom-close"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="pp-lb-stage" x-ref="stage" :class="{ zoomed: s > 1.01 }"
                 @pointerdown="down($event)" @pointermove="move($event)" @pointerup="up($event)" @pointercancel="up($event)"
                 @wheel.prevent="wheel($event)" @dblclick.prevent>
                <img x-ref="img" :src="zoomList[zoomAt]" :alt="p.name" draggable="false" @load="reset(true)"
                     :style="'transform: translate3d(' + (x + dx) + 'px,' + (y + dy) + 'px,0) scale(' + s + ');' + (anim ? 'transition: transform .22s ease' : '') + ';opacity:' + fade">
            </div>
            <button type="button" class="pp-lb-arrow prev" x-show="zoomList.length > 1" @click="step(-1)" aria-label="Foto anterior"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="pp-lb-arrow next" x-show="zoomList.length > 1" @click="step(1)" aria-label="Próxima foto"><i class="fas fa-chevron-right"></i></button>
            <div class="pp-lb-bottom">
                <div class="pp-lb-thumbs" x-show="zoomList.length > 1">
                    <template x-for="(src, i) in zoomList" :key="'z' + p.id + '-' + i">
                        <button type="button" :class="{ on: i === zoomAt }" @click="show(i)" :aria-label="'Foto ' + (i + 1)"><img :src="src" alt="" loading="lazy"></button>
                    </template>
                </div>
                <small class="pp-lb-hint" x-text="s > 1.01 ? 'Arraste para ver os detalhes' : (touch ? 'Toque duas vezes ou use dois dedos para ampliar' : 'Clique na foto ou use a roda do mouse para ampliar')"></small>
            </div>
        </div>
    </template>

    @if($together->isNotEmpty() && ! ($special['settings']['hide_together'] ?? false))
        @php $tIds = $together->pluck('id')->all(); @endphp
        <section class="ml-box pp-buy2" data-testid="buy-together"
                 x-data="{ sel: @js(collect($tIds)->mapWithKeys(fn ($i) => [$i => true])), get items() { return [p].concat(@js($tIds).filter(i => this.sel[i]).map(i => products[i])).filter(x => x && x.buyable !== false && x.stock > 0); }, get total() { return this.items.reduce((s, x) => s + x.price, 0); } }">
            <h2><i class="fas fa-cart-plus" style="color: var(--ml-blue)"></i> Compre junto</h2>
            <div class="pp-buy2-row">
                <div class="pp-buy2-item on">
                    <template x-if="p.img"><img :src="p.img" alt=""></template><template x-if="!p.img"><div class="ph"></div></template>
                    <b x-text="p.name"></b><span x-text="money(p.price)"></span>
                </div>
                @foreach($together as $t)
                    @php $td = $all[$t->id]; @endphp
                    <span class="pp-buy2-plus">+</span>
                    <label class="pp-buy2-item" :class="sel[{{ $t->id }}] ? 'on' : 'off'">
                        <input type="checkbox" x-model="sel[{{ $t->id }}]" aria-label="Levar {{ $td['name'] }} junto">
                        @if($td['img'])<img src="{{ $td['img'] }}" alt="" loading="lazy">@else<div class="ph"></div>@endif
                        <b>{{ $td['name'] }}</b><span>R$ {{ number_format($td['price'], 2, ',', '.') }}</span>
                        <a href="{{ $td['url'] }}" style="font-size:12px;color:var(--ml-blue)" @click.stop>Ver produto</a>
                    </label>
                @endforeach
                <div class="pp-buy2-sum">
                    <small x-text="items.length === 1 ? 'Só este produto' : 'Os ' + items.length + ' juntos por'"></small>
                    <strong x-text="money(total)"></strong>
                    <button type="button" class="ml-btn ml-btn-primary" :disabled="!items.length" @click="items.forEach(x => add(x, 1))" data-testid="buy-together-add">
                        <i class="fas fa-cart-plus"></i> <span x-text="items.length > 1 ? 'Adicionar os ' + items.length : 'Adicionar'"></span>
                    </button>
                </div>
            </div>
        </section>
    @endif

    <section class="ml-box pp-about" @if($special['settings']['hide_about'] ?? false) hidden @endif x-show="!{{ ($special['settings']['hide_about'] ?? false) ? 'true' : 'false' }} && (p.desc || p.brand || p.model || p.code || (p.specs && p.specs.length))">
        <div class="pp-about-in">
            <div x-show="p.desc">
                <h2>Sobre o produto</h2>
                <p x-text="p.desc">{{ $d['desc'] }}</p>
            </div>
            <div class="pp-sub" x-show="p.brand || p.model || p.code || (p.specs && p.specs.length)">
                <h2>Características</h2>
                <dl class="ml-specs">
                    <div x-show="p.brand"><dt>Marca</dt><dd x-text="p.brand"></dd></div>
                    <div x-show="p.model"><dt>Modelo</dt><dd x-text="p.model"></dd></div>
                    <div x-show="p.opt"><dt>{{ $attribute ?? 'Opção' }}</dt><dd x-text="p.opt"></dd></div>
                    <template x-for="s in (p.specs || [])" :key="s.n"><div><dt x-text="s.n"></dt><dd x-text="s.v"></dd></div></template>
                    <div x-show="p.code"><dt>Código</dt><dd x-text="p.code"></dd></div>
                </dl>
            </div>
        </div>
    </section>

    @if(\App\Services\Portal\ReviewService::ready() && ! ($special['settings']['hide_reviews'] ?? false))
        <section class="ml-box pp-reviews" id="avaliacoes" data-testid="reviews">
            <h2>Avaliações</h2>
            @if(session('success'))<p class="pp-rv-msg" role="status">{{ session('success') }}</p>@endif
            @if(session('error') || $errors->any())<p class="pp-rv-msg err" role="alert">{{ session('error') ?: $errors->first() }}</p>@endif
            <div class="pp-reviews-top">
                <div class="pp-avg">
                    @if($rating)
                        <strong>{{ number_format($rating['avg'], 1, ',', '') }}</strong>
                        <span class="rv-stars" style="--v: {{ $rating['avg'] }}">★★★★★</span>
                        <small>{{ $rating['n'] }} {{ $rating['n'] === 1 ? 'avaliação' : 'avaliações' }}</small>
                        <div class="pp-hist">
                            @foreach($hist as $star => $cnt)
                                <div><span>{{ $star }} ★</span><i><b style="width: {{ $rating['n'] ? round($cnt / $rating['n'] * 100) : 0 }}%"></b></i><span>{{ $cnt }}</span></div>
                            @endforeach
                        </div>
                    @else
                        <p class="pp-rv-empty">Ainda não tem avaliações. Seja o primeiro a contar o que achou!</p>
                    @endif
                </div>
                @if($canReview)
                    <form method="POST" action="{{ route('portal.product.review', ['userId' => $owner, 'product' => $item->id]) }}" class="pp-rv-form" data-testid="review-form">
                        @csrf
                        <p>{{ $myReview ? 'Sua avaliação (dá para mudar)' : 'O que você achou?' }}</p>
                        <div class="pp-pick" role="radiogroup" aria-label="Nota">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" id="rv-{{ $i }}" value="{{ $i }}" @checked((int) old('rating', $myReview?->rating) === $i) required>
                                <label for="rv-{{ $i }}" title="{{ $i }} {{ $i === 1 ? 'estrela' : 'estrelas' }}" data-testid="rv-star-{{ $i }}">★</label>
                            @endfor
                        </div>
                        <textarea name="comment" maxlength="600" placeholder="Conte como foi: qualidade, entrega, se valeu a pena (opcional)" data-testid="rv-comment">{{ old('comment', $myReview?->comment) }}</textarea>
                        <button type="submit" class="ml-btn ml-btn-primary" data-testid="rv-send"><i class="fas fa-paper-plane"></i> {{ $myReview ? 'Atualizar avaliação' : 'Enviar avaliação' }}</button>
                    </form>
                @elseif(! Auth::guard('portal')->check())
                    <div class="pp-rv-form">
                        <p>Comprou este produto?</p>
                        <span style="font-size:13px;color:var(--ml-muted)">Entre na sua conta para deixar sua avaliação com estrelas.</span>
                        <a class="ml-btn ml-btn-soft" href="{{ route('portal.login', ['redirect' => parse_url(\App\Http\Controllers\ClientPortalController::productUrl($item), PHP_URL_PATH) . '#avaliacoes']) }}" data-testid="rv-login"><i class="fas fa-right-to-bracket"></i> Entrar para avaliar</a>
                    </div>
                @endif
            </div>
            @if($reviews->isNotEmpty())
                <div class="pp-rv-list">
                    @foreach($reviews as $r)
                        <article class="pp-rv">
                            <div class="pp-rv-head">
                                <span class="rv-stars" style="--v: {{ $r->rating }}">★★★★★</span>
                                <b>{{ $r->name }}</b>
                                @if($r->verified)<span class="pp-rv-ok"><i class="fas fa-circle-check"></i> Comprou</span>@endif
                                <small>{{ $r->created_at?->format('d/m/Y') }}</small>
                            </div>
                            @if($r->comment)<p>{{ $r->comment }}</p>@endif
                            @if($r->reply)<div class="pp-rv-reply"><b>Resposta da loja</b>{{ $r->reply }}</div>@endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if($related->isNotEmpty() && ! ($special['settings']['hide_related'] ?? false))
        <section class="ml-box ml-offers pp-related" data-testid="related">
            <div class="ml-sec-head">
                <h2><i class="fas fa-layer-group"></i> Produtos relacionados</h2>
                <a href="{{ $categoryUrl ?? $catalogHome }}">Ver mais</a>
            </div>
            <div class="ml-rail">
                @foreach($related as $r)
                    @include('portal.partials.ml-card', ['d' => $all[$r->id]])
                @endforeach
            </div>
        </section>
    @endif

    @include('portal.partials.recently-viewed', ['exclude' => $family->isNotEmpty() ? $family->pluck('id')->all() : [$item->id]])

    {{-- Barra fixa de compra (celular) --}}
    <div class="pp-buybar">
        <div class="pp-buybar-sum">
            <small x-text="p.buyable ? (p.opt ? p.opt : 'Total') : 'Esgotado no momento'">Total</small>
            <strong x-text="p.price > 0 ? money(p.price * Math.max(qty, 1)) : 'Sob consulta'"></strong>
        </div>
        <button type="button" class="ml-btn ml-btn-soft pp-cart-btn" @click="openCart()" aria-label="Abrir carrinho">
            <i class="fas fa-cart-shopping"></i>
            <span class="ml-count" x-show="$store.cart.count > 0" x-text="$store.cart.count" x-cloak></span>
        </button>
        <button type="button" class="ml-btn ml-btn-primary" x-show="p.buyable" @click="add(p, qty)">
            <i class="fas fa-cart-plus"></i> Adicionar
        </button>
        <button type="button" class="ml-btn ml-btn-primary" x-show="!p.buyable" x-cloak @click="fav(p.id)">
            <i class="fa-heart" :class="$store.favs.has(p.id) ? 'fas' : 'far'"></i> Avise-me
        </button>
    </div>

    @include('portal.partials.catalog-cart')
</div>

@push('scripts')
@include('portal.partials.catalog-script')
<script>
// Calcular frete na página do produto: o CEP fica guardado no aparelho para as próximas.
function ppShip(url, myCep, owner) {
    const key = 'portal_cep';
    let saved = '';
    try { saved = localStorage.getItem(key) || ''; } catch (e) {}
    return {
        cep: myCep || saved, res: null, busy: false,
        init() { if (this.cep.replace(/\D/g, '').length === 8) this.$nextTick(() => this.calc(this.p.price * Math.max(this.qty, 1))); },
        mask(v) { const d = String(v).replace(/\D/g, '').slice(0, 8); return d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d; },
        async calc(valor) {
            const d = this.cep.replace(/\D/g, '');
            if (d.length !== 8) return;
            this.busy = true;
            try {
                const r = await fetch(url + '?cep=' + d + '&valor=' + (Number(valor) || 0).toFixed(2), { headers: { Accept: 'application/json' } });
                this.res = r.ok ? await r.json() : { ok: false, message: r.status === 429 ? 'Muitas consultas. Tente de novo em um minuto.' : 'Não deu para calcular agora. Tente de novo.' };
                if (this.res.ok) { try { localStorage.setItem(key, this.mask(d)); } catch (e) {} }
            } catch (e) { this.res = { ok: false, message: 'Sem conexão. Tente de novo.' }; }
            this.busy = false;
        },
    };
}
</script>
<script>
// Foto ampliada na página do produto (sem biblioteca): pinça, toque duplo, roda do mouse, arrastar e deslizar.
function ppZoom() {
    const MAX = 4, STEP = 2.5;
    return {
        s: 1, x: 0, y: 0, dx: 0, dy: 0, anim: false, fade: 1, touch: matchMedia('(pointer: coarse)').matches,
        pts: new Map(), start: null, lastTap: 0, moved: false,
        reset(instant) { this.anim = ! instant; this.s = 1; this.x = this.y = this.dx = this.dy = 0; this.fade = 1; this.pts.clear(); this.start = null; },
        key(e) {
            if (e.key === 'ArrowRight') { e.preventDefault(); this.step(1); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); this.step(-1); }
            if (e.key === '+' || e.key === '=') this.zoomTo(this.s * 1.5);
            if (e.key === '-') this.zoomTo(this.s / 1.5);
        },
        show(i) { if (i === this.zoomAt) return; this.zoomAt = i; this.reset(true); },
        step(d) {
            const n = this.zoomList.length;
            if (n < 2) { this.settle(); return; }
            this.show((this.zoomAt + d + n) % n);
        },
        // Medidas da foto já encaixada na tela (sem a ampliação).
        box() {
            const st = this.$refs.stage.getBoundingClientRect(), im = this.$refs.img;
            return { W: st.width, H: st.height, cx: st.left + st.width / 2, cy: st.top + st.height / 2, w: im.offsetWidth, h: im.offsetHeight };
        },
        clamp(s, x, y) {
            const b = this.box(), mx = Math.max(0, (b.w * s - b.W) / 2), my = Math.max(0, (b.h * s - b.H) / 2);
            return [Math.min(mx, Math.max(-mx, x)), Math.min(my, Math.max(-my, y))];
        },
        // Amplia mantendo o ponto (px, py) da tela no lugar.
        zoomTo(ns, px, py, animate = true) {
            const b = this.box();
            ns = Math.min(MAX, Math.max(1, ns));
            px = (px ?? b.cx) - b.cx; py = (py ?? b.cy) - b.cy;
            const k = ns / this.s;
            const [x, y] = this.clamp(ns, px - (px - this.x) * k, py - (py - this.y) * k);
            this.anim = animate; this.s = ns; this.x = ns === 1 ? 0 : x; this.y = ns === 1 ? 0 : y;
        },
        settle() { this.anim = true; this.dx = this.dy = 0; this.fade = 1; [this.x, this.y] = this.clamp(this.s, this.x, this.y); },
        wheel(e) { this.zoomTo(this.s * (e.deltaY < 0 ? 1.25 : 0.8), e.clientX, e.clientY, false); },
        down(e) {
            this.$refs.stage.setPointerCapture?.(e.pointerId);
            this.pts.set(e.pointerId, { x: e.clientX, y: e.clientY });
            this.anim = false; this.moved = false;
            this.start = this.snap();
        },
        snap() {
            const a = [...this.pts.values()];
            if (a.length >= 2) {
                return { two: true, d: Math.hypot(a[0].x - a[1].x, a[0].y - a[1].y) || 1, mx: (a[0].x + a[1].x) / 2, my: (a[0].y + a[1].y) / 2, s: this.s, x: this.x, y: this.y };
            }
            return { two: false, px: a[0].x, py: a[0].y, x: this.x, y: this.y, t: Date.now() };
        },
        move(e) {
            if (! this.pts.has(e.pointerId) || ! this.start) return;
            this.pts.set(e.pointerId, { x: e.clientX, y: e.clientY });
            const a = [...this.pts.values()], st = this.start;
            if (st.two && a.length >= 2) {
                // Pinça: amplia em volta do meio dos dois dedos e acompanha o arrasto.
                const d = Math.hypot(a[0].x - a[1].x, a[0].y - a[1].y), mx = (a[0].x + a[1].x) / 2, my = (a[0].y + a[1].y) / 2;
                const b = this.box(), ns = Math.min(MAX * 1.2, Math.max(0.8, st.s * d / st.d)), k = ns / st.s;
                const ox = st.mx - b.cx, oy = st.my - b.cy;
                this.s = ns; this.x = ox - (ox - st.x) * k + (mx - st.mx); this.y = oy - (oy - st.y) * k + (my - st.my);
                this.moved = true;
                return;
            }
            if (st.two) return;
            const mx = e.clientX - st.px, my = e.clientY - st.py;
            if (Math.abs(mx) + Math.abs(my) > 6) this.moved = true;
            if (this.s > 1.01) { [this.x, this.y] = this.clamp(this.s, st.x + mx, st.y + my); return; }
            // Sem ampliação: deslizar para os lados troca a foto; para baixo fecha.
            if (Math.abs(my) > Math.abs(mx) && my > 0) { this.dy = my; this.dx = 0; this.fade = Math.max(0.3, 1 - my / 400); }
            else { this.dx = mx; this.dy = 0; }
        },
        up(e) {
            if (! this.pts.has(e.pointerId)) return;
            this.pts.delete(e.pointerId);
            const st = this.start;
            if (this.pts.size) { this.start = this.snap(); return; }
            this.start = null;
            if (st && st.two) {
                if (this.s < 1) this.zoomTo(1); else if (this.s > MAX) this.zoomTo(MAX); else this.settle();
                return;
            }
            if (this.s <= 1.01) {
                if (this.dy > 110) { this.zoomClose(); this.reset(true); return; }
                if (Math.abs(this.dx) > 70) { this.step(this.dx < 0 ? 1 : -1); return; }
            }
            this.settle();
            if (this.moved || e.type === 'pointercancel') return;
            // Toque/clique sem arrastar: no mouse cada clique amplia/volta; no toque é o toque duplo.
            const now = Date.now(), isMouse = e.pointerType === 'mouse';
            if (isMouse || now - this.lastTap < 300) {
                this.lastTap = 0;
                this.s > 1.01 ? this.zoomTo(1) : this.zoomTo(STEP, e.clientX, e.clientY);
            } else {
                this.lastTap = now;
            }
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
