@php
    $owner = $ownerId;
    $catalogUrl = fn (array $q = []) => route('portal.catalog', array_merge(
        ['userId' => $owner],
        array_filter(['search' => $search, 'category' => $category, 'ordem' => $sort, 'ofertas' => $onlyOffers ? 1 : null], fn ($v) => $v !== '' && $v !== null),
        $q
    ));
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);

    // Dados de cada produto para o card, a ficha e o carrinho.
    $info = function ($p) {
        $promo = $p->livePromotion();
        $imgs = $p->all_images;
        $price = $promo ? (float) $promo->promo_price : (float) $p->price_sale;
        $endsIn = null;
        if ($promo && $promo->ends_at) {
            $hours = now()->diffInHours($promo->ends_at, false);
            $endsIn = $hours < 24 ? 'Termina hoje' : 'Termina em ' . (int) ceil($hours / 24) . ' dias';
        }
        $name = $p->variation_value && ! str_contains(mb_strtolower($p->name), mb_strtolower($p->variation_value))
            ? $p->name . ' · ' . $p->variation_value
            : $p->name;

        return [
            'id'      => $p->id,
            'name'    => $name,
            'price'   => $price,
            'old'     => $promo ? (float) $promo->original_price : null,
            'pct'     => $promo ? (int) $promo->discount_percent : null,
            'ends'    => $endsIn,
            'stock'   => (int) $p->stock_quantity,
            'img'     => $imgs[0] ?? null,
            'imgs'    => $imgs,
            'cat'     => $p->category?->name,
            'desc'    => trim((string) $p->description),
            'brand'   => $p->brand,
            'model'   => $p->model,
            'code'    => $p->product_code,
        ];
    };

    $all = collect();
    foreach ($offers as $o) { $all[$o->id] = $info($o); }
    foreach ($products as $p) { $all[$p->id] = $info($p); }

    $phone = preg_replace('/\D+/', '', (string) ($store?->phone ?? ''));
    if ($phone && strlen($phone) <= 11) { $phone = '55' . $phone; }
    $showOffers = $offers->isNotEmpty() && ! $search && ! $category && ! $onlyOffers && $products->currentPage() === 1;
@endphp

<x-portal-catalog-layout title="Catálogo" :store="$store" :owner-id="$owner" :search="$search">

@push('styles')
<style>
    .ml-section { margin-top: 12px; }
    .ml-box { background: var(--ml-card); border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); }

    /* Faixa de boas-vindas */
    .ml-hero { margin-top: 12px; border-radius: var(--ml-radius); overflow: hidden; position: relative; padding: 18px 18px 20px; color: #fff;
        background: radial-gradient(120% 140% at 100% 0%, #5b6bff 0%, rgba(91,107,255,0) 55%), linear-gradient(135deg, #2d3277 0%, #3483fa 100%); }
    .ml-hero::after { content: ''; position: absolute; right: -40px; bottom: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,230,0,.18); }
    .ml-hero small { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; background: var(--ml-yellow); color: #2d3277; padding: 4px 9px; border-radius: 999px; }
    .ml-hero h1 { margin: 10px 0 4px; font-size: 22px; line-height: 1.15; font-weight: 900; max-width: 520px; }
    .ml-hero p { margin: 0; font-size: 13px; opacity: .9; max-width: 460px; }
    .ml-hero-stats { display: flex; gap: 8px; margin-top: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
    .ml-hero-stats span { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.25); padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }

    /* Atalhos de categoria */
    .ml-cats { display: flex; gap: 10px; overflow-x: auto; padding: 14px 12px 12px; scrollbar-width: none; scroll-snap-type: x proximity; }
    .ml-cats::-webkit-scrollbar { display: none; }
    .ml-cat { flex: 0 0 auto; width: 72px; display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center; scroll-snap-align: start; }
    .ml-cat-ico { width: 56px; height: 56px; border-radius: 50%; background: #fff; box-shadow: var(--ml-shadow); display: flex; align-items: center; justify-content: center; font-size: 22px; color: var(--ml-blue); border: 2px solid transparent; transition: transform .15s; }
    .ml-cat > span:last-child { font-size: 11px; line-height: 1.2; color: #555; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ml-cat.on .ml-cat-ico { border-color: var(--ml-blue); background: var(--ml-blue-soft); }
    .ml-cat.on > span:last-child { color: var(--ml-blue); }
    .ml-cat.hot .ml-cat-ico { color: #fff; background: linear-gradient(135deg, var(--ml-orange), var(--ml-red)); }
    .ml-cat:active .ml-cat-ico { transform: scale(.94); }

    /* Ofertas do dia */
    .ml-offers { padding: 14px 0 6px; }
    .ml-sec-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; padding: 0 14px 10px; }
    .ml-sec-head h2 { margin: 0; font-size: 18px; font-weight: 800; color: #333; display: flex; align-items: center; gap: 8px; }
    .ml-sec-head h2 i { color: var(--ml-orange); }
    .ml-sec-head a { color: var(--ml-blue); font-size: 13px; font-weight: 600; white-space: nowrap; }
    .ml-rail { display: flex; gap: 10px; overflow-x: auto; padding: 0 14px 12px; scroll-snap-type: x mandatory; scrollbar-width: none; }
    .ml-rail::-webkit-scrollbar { display: none; }
    .ml-rail .ml-card { flex: 0 0 152px; scroll-snap-align: start; border: 1px solid var(--ml-line); box-shadow: none; }

    /* Barra de filtros */
    .ml-toolbar { position: sticky; top: calc(85px + env(safe-area-inset-top)); z-index: 40; background: var(--ml-bg); box-shadow: 0 -14px 0 var(--ml-bg); margin: 0 -12px; padding: 10px 12px 8px; display: flex; align-items: center; gap: 8px; overflow-x: auto; scrollbar-width: none; }
    .ml-toolbar::-webkit-scrollbar { display: none; }
    .ml-count-txt { font-size: 13px; color: var(--ml-muted); white-space: nowrap; margin-right: auto; padding-left: 2px; }
    .ml-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 999px; background: #fff; border: 1px solid #d9d9d9; font-size: 13px; font-weight: 600; color: #333; white-space: nowrap; cursor: pointer; }
    .ml-chip.on { background: var(--ml-blue-soft); border-color: var(--ml-blue); color: var(--ml-blue); }
    .ml-chip.hot.on { background: #fff1ea; border-color: var(--ml-orange); color: #d9541a; }
    .ml-chip select { border: 0; background: transparent; font-weight: 600; font-size: 13px; outline: none; padding-right: 2px; cursor: pointer; appearance: none; -webkit-appearance: none; }
    .ml-search-note { font-size: 14px; padding: 4px 2px 0; color: #333; }
    .ml-search-note b { font-weight: 800; }
    .ml-search-note a { color: var(--ml-blue); font-weight: 600; margin-left: 6px; font-size: 13px; }

    /* Grade de produtos */
    .ml-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    @media (min-width: 640px) { .ml-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; } }
    @media (min-width: 960px) { .ml-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; } }
    @media (min-width: 1180px) { .ml-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

    .ml-card { position: relative; background: #fff; border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); overflow: hidden; display: flex; flex-direction: column; cursor: pointer; transition: box-shadow .18s, transform .18s; -webkit-tap-highlight-color: transparent; }
    @media (hover: hover) { .ml-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.14); transform: translateY(-2px); } }
    .ml-card-img { position: relative; aspect-ratio: 1 / 1; background: #fff; border-bottom: 1px solid #f2f2f2; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .ml-card-img img { width: 100%; height: 100%; object-fit: contain; padding: 8px; transition: transform .3s; }
    @media (hover: hover) { .ml-card:hover .ml-card-img img { transform: scale(1.05); } }
    .ml-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 34px; color: #d6d6d6; background: linear-gradient(135deg, #fafafa, #f0f0f0); }
    .ml-off-flag { position: absolute; top: 8px; left: 8px; background: var(--ml-green); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 7px; border-radius: 4px; }
    .ml-add { position: absolute; right: 8px; bottom: 8px; width: 36px; height: 36px; border-radius: 50%; border: 0; background: #fff; color: var(--ml-blue); box-shadow: 0 2px 8px rgba(0,0,0,.18); display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; transition: transform .15s, background .15s; }
    .ml-add:active { transform: scale(.9); }
    .ml-add.in { background: var(--ml-blue); color: #fff; }
    .ml-imgs { position: absolute; left: 8px; bottom: 8px; background: rgba(0,0,0,.55); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 7px; border-radius: 999px; display: inline-flex; gap: 4px; align-items: center; }
    .ml-card-body { padding: 10px 12px 14px; display: flex; flex-direction: column; gap: 3px; flex: 1; }
    .ml-deal { align-self: flex-start; background: var(--ml-blue); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .02em; padding: 3px 6px; border-radius: 3px; text-transform: uppercase; margin-bottom: 2px; }
    .ml-title { margin: 0; font-size: 13px; font-weight: 400; color: #333; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
    .ml-old { font-size: 12px; color: #999; text-decoration: line-through; margin-top: 4px; }
    .ml-price-row { display: flex; align-items: flex-start; gap: 6px; flex-wrap: wrap; }
    .ml-price { font-size: 22px; font-weight: 400; color: #333; line-height: 1.1; letter-spacing: -.01em; white-space: nowrap; }
    .ml-price sup { font-size: 11px; position: relative; top: -.35em; margin-left: 1px; vertical-align: baseline; }
    .ml-price .cur { font-size: 15px; margin-right: 2px; }
    .ml-pct { color: var(--ml-green); font-size: 13px; font-weight: 600; margin-top: 4px; white-space: nowrap; }
    .ml-ask { font-size: 13px; color: var(--ml-blue); font-weight: 600; margin-top: 6px; }
    .ml-pix { font-size: 12px; color: var(--ml-green); font-weight: 600; }
    .ml-stock { font-size: 12px; margin-top: 3px; color: var(--ml-muted); }
    .ml-stock.low { color: var(--ml-orange); font-weight: 700; }
    .ml-ends { font-size: 11px; color: #d9541a; font-weight: 700; display: inline-flex; gap: 4px; align-items: center; }
    .ml-rail .ml-price { font-size: 19px; }
    .ml-rail .ml-title { font-size: 12px; }
    .ml-rail .ml-card-body { padding: 8px 10px 12px; }

    /* Paginação */
    .ml-pages { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 22px 0 8px; flex-wrap: wrap; }
    .ml-pages a, .ml-pages span { min-width: 40px; height: 40px; padding: 0 14px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; background: #fff; box-shadow: var(--ml-shadow); color: var(--ml-blue); }
    .ml-pages span.cur { background: var(--ml-blue); color: #fff; }
    .ml-pages span.off { color: #bbb; box-shadow: none; background: transparent; }

    /* Vazio */
    .ml-empty { padding: 48px 20px; text-align: center; }
    .ml-empty i { font-size: 44px; color: #ccc; }
    .ml-empty h3 { margin: 14px 0 6px; font-size: 17px; }
    .ml-empty p { margin: 0 0 16px; color: var(--ml-muted); font-size: 14px; }

    /* Botões */
    .ml-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 48px; border: 0; border-radius: 6px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .15s; }
    .ml-btn-primary { background: var(--ml-blue); color: #fff; }
    .ml-btn-primary:hover { background: var(--ml-blue-dark); }
    .ml-btn-soft { background: var(--ml-blue-soft); color: var(--ml-blue); }
    .ml-btn-soft:hover { background: #d0e1fa; }
    .ml-btn-wa { background: #fff; color: #128c4b; border: 1px solid #b5e3c9; }
    .ml-btn-sm { display: inline-flex; width: auto; height: 40px; padding: 0 20px; }

    /* Ficha do produto (bottom sheet) */
    .ml-sheet-bg { position: fixed; inset: 0; z-index: 90; background: rgba(0,0,0,.5); }
    .ml-sheet { position: fixed; z-index: 91; left: 0; right: 0; bottom: 0; max-height: 94vh; background: #fff; border-radius: 16px 16px 0 0; overflow-y: auto; overscroll-behavior: contain; padding-bottom: calc(16px + env(safe-area-inset-bottom)); }
    .ml-sheet-grip { position: sticky; top: 0; z-index: 3; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 8px 8px 4px 16px; }
    .ml-sheet-grip::before { content: ''; position: absolute; left: 50%; top: 6px; width: 40px; height: 4px; margin-left: -20px; border-radius: 4px; background: #d9d9d9; }
    .ml-sheet-grip small { font-size: 12px; color: var(--ml-muted); padding-top: 10px; }
    .ml-x { width: 40px; height: 40px; border: 0; background: transparent; border-radius: 50%; font-size: 18px; color: #666; cursor: pointer; }
    .ml-gal { position: relative; }
    .ml-gal-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
    .ml-gal-track::-webkit-scrollbar { display: none; }
    .ml-gal-track > div { flex: 0 0 100%; scroll-snap-align: center; aspect-ratio: 1 / 1; max-height: 52vh; display: flex; align-items: center; justify-content: center; }
    .ml-gal-track img { width: 100%; height: 100%; object-fit: contain; padding: 6px 16px; }
    .ml-gal-n { position: absolute; left: 14px; top: 8px; background: #f2f2f2; color: #555; font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
    .ml-dots { display: flex; justify-content: center; gap: 6px; padding: 8px 0 2px; }
    .ml-dots i { width: 6px; height: 6px; border-radius: 50%; background: #d0d0d0; transition: all .2s; }
    .ml-dots i.on { background: var(--ml-blue); width: 16px; border-radius: 6px; }
    .ml-thumbs { display: none; }
    .ml-info { padding: 6px 16px 4px; }
    .ml-info .ml-cat-line { font-size: 12px; color: var(--ml-muted); }
    .ml-info h2 { margin: 6px 0 10px; font-size: 18px; font-weight: 500; line-height: 1.3; color: #333; }
    .ml-info .ml-price { font-size: 32px; }
    .ml-info .ml-price sup { font-size: 15px; }
    .ml-info .ml-pct { font-size: 16px; }
    .ml-perk { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; margin-top: 12px; }
    .ml-perk i { width: 18px; text-align: center; margin-top: 2px; color: var(--ml-green); }
    .ml-perk b { color: var(--ml-green); font-weight: 600; }
    .ml-perk span small { display: block; color: var(--ml-muted); font-size: 12px; margin-top: 1px; }
    .ml-qty { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 16px 0 12px; padding: 10px 12px; border-radius: 6px; background: #f5f5f5; }
    .ml-qty-label { font-size: 14px; }
    .ml-qty-label small { color: var(--ml-muted); margin-left: 4px; }
    .ml-step { display: inline-flex; align-items: center; background: #fff; border-radius: 999px; box-shadow: var(--ml-shadow); }
    .ml-step button { width: 36px; height: 36px; border: 0; background: transparent; color: var(--ml-blue); font-size: 14px; cursor: pointer; }
    .ml-step button:disabled { color: #ccc; cursor: default; }
    .ml-step span { min-width: 26px; text-align: center; font-weight: 700; }
    .ml-actions { display: grid; gap: 8px; }
    .ml-desc { margin: 20px 16px 0; padding-top: 16px; border-top: 1px solid var(--ml-line); }
    .ml-desc h3 { margin: 0 0 8px; font-size: 17px; font-weight: 600; }
    .ml-desc p { margin: 0; white-space: pre-line; color: #555; font-size: 14px; line-height: 1.55; }
    .ml-specs { margin: 14px 0 0; border-radius: 6px; overflow: hidden; border: 1px solid var(--ml-line); }
    .ml-specs div { display: flex; font-size: 13px; }
    .ml-specs div:nth-child(odd) { background: #f5f5f5; }
    .ml-specs dt { width: 40%; padding: 9px 12px; font-weight: 600; }
    .ml-specs dd { margin: 0; padding: 9px 12px; flex: 1; color: #555; }
    @media (min-width: 900px) {
        .ml-sheet { left: 50%; top: 50%; bottom: auto; right: auto; transform: translate(-50%, -50%); width: min(980px, 94vw); max-height: 90vh; border-radius: 12px; padding-bottom: 20px; }
        .ml-sheet-grip::before { display: none; }
        .ml-sheet-body { display: grid; grid-template-columns: 1.15fr 1fr; gap: 8px; align-items: start; }
        .ml-gal-track > div { max-height: 480px; }
        .ml-thumbs { display: flex; gap: 6px; padding: 8px 16px 0; flex-wrap: wrap; }
        .ml-thumbs button { width: 52px; height: 52px; border: 1px solid var(--ml-line); border-radius: 6px; background: #fff; padding: 3px; cursor: pointer; }
        .ml-thumbs button.on { border: 2px solid var(--ml-blue); }
        .ml-thumbs img { width: 100%; height: 100%; object-fit: contain; }
        .ml-dots { display: none; }
        .ml-info { padding: 4px 22px 0 8px; }
        .ml-desc { grid-column: 1 / -1; }
    }

    /* Carrinho fixo embaixo */
    .ml-cartbar { position: fixed; z-index: 70; left: 0; right: 0; bottom: 0; background: #fff; box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); }
    .ml-cartbar-in { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
    .ml-cartbar-sum { flex: 1; min-width: 0; }
    .ml-cartbar-sum small { display: block; color: var(--ml-muted); font-size: 12px; }
    .ml-cartbar-sum strong { font-size: 18px; font-weight: 700; }
    .ml-cartbar .ml-btn { width: auto; padding: 0 22px; height: 46px; }

    .ml-toast { position: fixed; z-index: 95; left: 50%; bottom: 92px; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 6px 20px rgba(0,0,0,.25); max-width: calc(100vw - 32px); white-space: nowrap; }
    .ml-toast i { color: #3fe08a; }
    .ml-toast span { overflow: hidden; text-overflow: ellipsis; }

    .ml-guest { display: flex; align-items: center; gap: 10px; margin-top: 12px; padding: 10px 12px; font-size: 13px; color: #555; }
    .ml-guest i { color: var(--ml-blue); font-size: 18px; }
    .ml-guest a { color: var(--ml-blue); font-weight: 700; white-space: nowrap; margin-left: auto; }
</style>
@endpush

<div class="ml-wrap" x-data="mlCatalog(@js($all), @js($cartUrl))" @keydown.escape.window="close()">

    @if($showOffers)
        <section class="ml-hero">
            <small><i class="fas fa-bolt"></i> Ofertas da semana</small>
            <h1>Até {{ $offers->max(fn ($o) => $o->livePromotion()->discount_percent) }}% OFF em produtos selecionados</h1>
            <p>Escolha, adicione ao carrinho e finalize seu pedido com {{ $store?->name ?? 'a loja' }}.</p>
            <div class="ml-hero-stats">
                <span><i class="fas fa-tag"></i> {{ $offers->count() }} {{ $offers->count() === 1 ? 'oferta' : 'ofertas' }}</span>
                <span><i class="fas fa-box"></i> {{ $products->total() }} produtos</span>
            </div>
        </section>
    @endif

    @if($categories->count())
        <nav class="ml-cats" aria-label="Categorias">
            <a href="{{ route('portal.catalog', ['userId' => $owner]) }}" class="ml-cat {{ ! $category && ! $onlyOffers ? 'on' : '' }}">
                <span class="ml-cat-ico"><i class="fas fa-store"></i></span>
                <span>Tudo</span>
            </a>
            @if($offers->isNotEmpty())
                <a href="{{ route('portal.catalog', ['userId' => $owner, 'ofertas' => 1]) }}" class="ml-cat hot {{ $onlyOffers ? 'on' : '' }}">
                    <span class="ml-cat-ico"><i class="fas fa-fire"></i></span>
                    <span>Ofertas</span>
                </a>
            @endif
            @foreach($categories as $cat)
                <a href="{{ route('portal.catalog', ['userId' => $owner, 'category' => $cat->getKey()]) }}"
                   class="ml-cat {{ (string) $category === (string) $cat->getKey() ? 'on' : '' }}">
                    <span class="ml-cat-ico"><i class="{{ $cat->icone ?: 'fas fa-tag' }}"></i></span>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if($showOffers)
        <section class="ml-box ml-offers">
            <div class="ml-sec-head">
                <h2><i class="fas fa-bolt"></i> Ofertas do dia</h2>
                <a href="{{ route('portal.catalog', ['userId' => $owner, 'ofertas' => 1]) }}">Ver todas</a>
            </div>
            <div class="ml-rail">
                @foreach($offers->take(15) as $o)
                    @include('portal.partials.ml-card', ['d' => $all[$o->id]])
                @endforeach
            </div>
        </section>
    @endif

    @guest('portal')
        <div class="ml-box ml-guest">
            <i class="fas fa-circle-info"></i>
            <span>Monte seu carrinho à vontade. Para finalizar, entre ou crie sua conta.</span>
            <a href="{{ route('portal.register', ['redirect' => 'cart', 'loja' => $owner]) }}">Criar conta</a>
        </div>
    @endguest

    @if($search)
        <p class="ml-search-note">Resultados para <b>“{{ $search }}”</b>
            <a href="{{ route('portal.catalog', ['userId' => $owner]) }}">Limpar busca</a>
        </p>
    @endif

    <div class="ml-toolbar">
        <span class="ml-count-txt">{{ $products->total() }} {{ $products->total() === 1 ? 'resultado' : 'resultados' }}</span>
        @if($offers->isNotEmpty())
            <a href="{{ $catalogUrl(['ofertas' => $onlyOffers ? null : 1, 'page' => null]) }}" class="ml-chip hot {{ $onlyOffers ? 'on' : '' }}">
                <i class="fas fa-fire"></i> Ofertas
                @if($onlyOffers)<i class="fas fa-xmark"></i>@endif
            </a>
        @endif
        <form method="GET" action="{{ route('portal.catalog', ['userId' => $owner]) }}" class="ml-chip {{ $sort ? 'on' : '' }}">
            @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            @if($onlyOffers)<input type="hidden" name="ofertas" value="1">@endif
            <i class="fas fa-arrow-down-wide-short"></i>
            <select name="ordem" onchange="this.form.submit()" aria-label="Ordenar">
                <option value="" @selected(! $sort)>Mais relevantes</option>
                <option value="menor" @selected($sort === 'menor')>Menor preço</option>
                <option value="maior" @selected($sort === 'maior')>Maior preço</option>
                <option value="novos" @selected($sort === 'novos')>Novidades</option>
                <option value="nome" @selected($sort === 'nome')>Nome A-Z</option>
            </select>
            <i class="fas fa-chevron-down" style="font-size:10px"></i>
        </form>
        @if($category)
            <a href="{{ $catalogUrl(['category' => null, 'page' => null]) }}" class="ml-chip on">
                {{ $categories->firstWhere('id_category', $category)?->name ?? 'Categoria' }} <i class="fas fa-xmark"></i>
            </a>
        @endif
    </div>

    @if($products->isEmpty())
        <div class="ml-box ml-empty">
            <i class="fas fa-magnifying-glass"></i>
            <h3>Não encontramos produtos</h3>
            <p>Confira a escrita ou tente uma palavra mais simples.</p>
            <a href="{{ route('portal.catalog', ['userId' => $owner]) }}" class="ml-btn ml-btn-primary ml-btn-sm">Ver todos os produtos</a>
        </div>
    @else
        <div class="ml-grid">
            @foreach($products as $p)
                @include('portal.partials.ml-card', ['d' => $all[$p->id]])
            @endforeach
        </div>

        @if($products->lastPage() > 1)
            <nav class="ml-pages" aria-label="Páginas">
                @if($products->onFirstPage())
                    <span class="off"><i class="fas fa-chevron-left"></i>&nbsp; Anterior</span>
                @else
                    <a href="{{ $products->previousPageUrl() }}"><i class="fas fa-chevron-left"></i>&nbsp; Anterior</a>
                @endif
                <span class="cur">{{ $products->currentPage() }} de {{ $products->lastPage() }}</span>
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}">Seguinte &nbsp;<i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="off">Seguinte &nbsp;<i class="fas fa-chevron-right"></i></span>
                @endif
            </nav>
        @endif
    @endif

    {{-- Ficha do produto --}}
    <template x-if="p">
        <div>
            <div class="ml-sheet-bg" @click="close()" x-transition.opacity></div>
            <div class="ml-sheet" role="dialog" aria-modal="true" :aria-label="p.name"
                 x-transition:enter="ml-anim-in" x-transition:leave="ml-anim-out">
                <div class="ml-sheet-grip">
                    <small x-text="p.stock <= 3 ? 'Últimas unidades' : 'Em estoque'"></small>
                    <button type="button" class="ml-x" @click="close()" aria-label="Fechar"><i class="fas fa-xmark"></i></button>
                </div>
                <div class="ml-sheet-body">
                    <div>
                        <div class="ml-gal">
                            <div class="ml-gal-track" x-ref="track" @scroll.debounce.60ms="slide = Math.round($el.scrollLeft / $el.clientWidth)">
                                <template x-if="!p.imgs.length">
                                    <div><div class="ml-ph"><i class="fas fa-image"></i></div></div>
                                </template>
                                <template x-for="(src, i) in p.imgs" :key="i">
                                    <div><img :src="src" :alt="p.name" :loading="i ? 'lazy' : 'eager'" x-on:error="p.imgs = p.imgs.filter(x => x !== src)"></div>
                                </template>
                            </div>
                            <span class="ml-gal-n" x-show="p.imgs.length > 1" x-text="(slide + 1) + ' / ' + p.imgs.length"></span>
                        </div>
                        <div class="ml-dots" x-show="p.imgs.length > 1">
                            <template x-for="(src, i) in p.imgs" :key="i"><i :class="{ on: i === slide }"></i></template>
                        </div>
                        <div class="ml-thumbs" x-show="p.imgs.length > 1">
                            <template x-for="(src, i) in p.imgs" :key="i">
                                <button type="button" :class="{ on: i === slide }" @click="goTo(i)"><img :src="src" alt=""></button>
                            </template>
                        </div>
                    </div>

                    <div class="ml-info">
                        <div class="ml-cat-line" x-text="[p.cat, p.brand].filter(Boolean).join(' · ') || 'Produto'"></div>
                        <h2 x-text="p.name"></h2>
                        <span class="ml-deal" x-show="p.pct">Oferta do dia</span>
                        <div class="ml-old" x-show="p.old" x-text="money(p.old)"></div>
                        <template x-if="p.price > 0">
                            <div class="ml-price-row">
                                <span class="ml-price"><span class="cur">R$</span><span x-text="intPart(p.price)"></span><sup x-show="cents(p.price) !== '00'" x-text="cents(p.price)"></sup></span>
                                <span class="ml-pct" x-show="p.pct" x-text="p.pct + '% OFF'"></span>
                            </div>
                        </template>
                        <div class="ml-ask" x-show="!(p.price > 0)">Preço sob consulta</div>
                        <div class="ml-ends" x-show="p.ends" style="margin-top:6px"><i class="fas fa-clock"></i> <span x-text="p.ends"></span></div>

                        <div class="ml-perk" x-show="p.old">
                            <i class="fas fa-piggy-bank"></i>
                            <span>Você economiza <b x-text="money((p.old - p.price) * qty)"></b></span>
                        </div>
                        <div class="ml-perk">
                            <i class="fas fa-truck-fast"></i>
                            <span><b>Combine a entrega</b> com a loja<small>Retirada ou envio, você escolhe ao finalizar.</small></span>
                        </div>
                        <div class="ml-perk">
                            <i class="fas fa-shield-halved"></i>
                            <span>Compra direta com <b style="color:#333">{{ $store?->name ?? 'a loja' }}</b><small>Seu pedido chega para a loja confirmar.</small></span>
                        </div>

                        <div class="ml-qty">
                            <span class="ml-qty-label">Quantidade<small x-text="'(' + p.stock + (p.stock === 1 ? ' disponível)' : ' disponíveis)')"></small></span>
                            <div class="ml-step">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1" aria-label="Menos"><i class="fas fa-minus"></i></button>
                                <span x-text="qty"></span>
                                <button type="button" @click="qty = Math.min(p.stock || 999, qty + 1)" :disabled="p.stock && qty >= p.stock" aria-label="Mais"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="ml-actions">
                            <button type="button" class="ml-btn ml-btn-primary" @click="buyNow()">Comprar agora</button>
                            <button type="button" class="ml-btn ml-btn-soft" @click="add(p, qty); close()">
                                <i class="fas fa-cart-plus"></i> Adicionar ao carrinho
                            </button>
                            @if($phone)
                                <a class="ml-btn ml-btn-wa" target="_blank" rel="noopener"
                                   :href="'https://wa.me/{{ $phone }}?text=' + encodeURIComponent('Olá! Tenho interesse em: ' + p.name)">
                                    <i class="fab fa-whatsapp"></i> Perguntar pelo WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="ml-desc" x-show="p.desc || p.brand || p.model || p.code">
                        <h3>Sobre o produto</h3>
                        <p x-show="p.desc" x-text="p.desc"></p>
                        <dl class="ml-specs" x-show="p.brand || p.model || p.code">
                            <div x-show="p.brand"><dt>Marca</dt><dd x-text="p.brand"></dd></div>
                            <div x-show="p.model"><dt>Modelo</dt><dd x-text="p.model"></dd></div>
                            <div x-show="p.code"><dt>Código</dt><dd x-text="p.code"></dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Carrinho fixo --}}
    <div class="ml-cartbar" x-show="$store.cart.count > 0" x-cloak x-transition.opacity>
        <div class="ml-cartbar-in">
            <div class="ml-cartbar-sum">
                <small x-text="$store.cart.count + ($store.cart.count === 1 ? ' produto no carrinho' : ' produtos no carrinho')"></small>
                <strong x-text="money($store.cart.total)"></strong>
            </div>
            <a href="{{ $cartUrl }}" class="ml-btn ml-btn-primary">
                {{ Auth::guard('portal')->check() ? 'Ver carrinho' : 'Finalizar pedido' }}
            </a>
        </div>
    </div>

    <div class="ml-toast" x-show="toast" x-cloak x-transition.opacity>
        <i class="fas fa-circle-check"></i> <span x-text="toast"></span>
    </div>
</div>

@push('styles')
<style>
    .ml-anim-in { animation: mlUp .26s cubic-bezier(.2,.8,.2,1); }
    .ml-anim-out { animation: mlUp .18s reverse ease-in; }
    @keyframes mlUp { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
    @media (min-width: 900px) {
        @keyframes mlUp { from { transform: translate(-50%, -46%); opacity: 0; } to { transform: translate(-50%, -50%); opacity: 1; } }
    }
</style>
@endpush

@push('scripts')
<script>
function mlCatalog(products, cartUrl) {
    return {
        products,
        p: null,
        qty: 1,
        slide: 0,
        toast: '',
        timer: null,
        money(v) {
            return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },
        intPart(v) { return Math.floor(Number(v) || 0).toLocaleString('pt-BR'); },
        cents(v) { return String(Math.round((Number(v) || 0) * 100) % 100).padStart(2, '0'); },
        open(id) {
            this.p = this.products[id] || null;
            this.qty = 1;
            this.slide = 0;
            document.documentElement.style.overflow = this.p ? 'hidden' : '';
        },
        close() {
            this.p = null;
            document.documentElement.style.overflow = '';
        },
        goTo(i) {
            const t = this.$refs.track;
            if (t) t.scrollTo({ left: i * t.clientWidth, behavior: 'smooth' });
            this.slide = i;
        },
        add(p, qty = 1) {
            Alpine.store('cart').add(p, qty);
            this.toast = 'Adicionado ao carrinho';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.toast = '', 2200);
        },
        buyNow() {
            Alpine.store('cart').add(this.p, this.qty);
            window.location.href = cartUrl;
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
