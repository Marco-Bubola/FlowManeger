@php
    $owner = $ownerId;
    $d = $all[$item->id];
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);
    $catalogHome = route('portal.catalog', ['userId' => $owner]);
    $categoryUrl = $item->category_id ? route('portal.catalog', ['userId' => $owner, 'category' => $item->category_id]) : null;

    $phone = preg_replace('/\D+/', '', (string) ($store?->phone ?? ''));
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

<x-portal-catalog-layout :title="$d['name']" :store="$store" :owner-id="$owner" :og="$og">

@push('styles')
@include('portal.partials.catalog-styles')
<style>
    .pp-crumbs { display: flex; align-items: center; gap: 8px; padding: 12px 2px 10px; font-size: 13px; color: var(--ml-muted); min-width: 0; }
    .pp-back { flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 999px; background: #fff; border: 1px solid #d9d9d9; color: #333; font-weight: 600; }
    .pp-trail { display: flex; align-items: center; gap: 6px; min-width: 0; white-space: nowrap; overflow: hidden; }
    .pp-trail a { color: var(--ml-blue); font-weight: 600; }
    .pp-trail span:last-child { overflow: hidden; text-overflow: ellipsis; }
    .pp-trail i { font-size: 9px; opacity: .6; }

    .pp-main { padding: 8px 0 18px; }
    .pp-main .ml-gal { padding-top: 6px; }
    .pp-main .ml-gal-track > div { max-height: 56vh; }
    .pp-fav { position: absolute; right: 12px; top: 8px; z-index: 2; width: 42px; height: 42px; border-radius: 50%; border: 0; background: rgba(255,255,255,.95); box-shadow: 0 1px 6px rgba(0,0,0,.18); color: #8c8c8c; font-size: 19px; cursor: pointer; }
    .pp-fav.on { color: #ec4899; }
    .pp-info { padding: 10px 16px 0; }
    .pp-info .ml-cat-line { font-size: 12px; color: var(--ml-muted); }
    .pp-info h1 { margin: 6px 0 10px; font-size: 20px; font-weight: 500; line-height: 1.3; color: #333; }
    .pp-info .ml-price { font-size: 34px; }
    .pp-info .ml-price sup { font-size: 16px; }
    .pp-info .ml-pct { font-size: 17px; }
    .pp-out { display: flex; gap: 10px; align-items: flex-start; margin-top: 12px; padding: 12px; border-radius: 8px; background: #fff4ec; color: #9a3d0b; font-size: 14px; }
    .pp-out i { margin-top: 2px; }
    .pp-out small { display: block; color: #a8673f; font-size: 12px; margin-top: 2px; }

    .pp-opts { margin-top: 16px; }
    .pp-opts-label { font-size: 14px; margin-bottom: 8px; }
    .pp-opts-label b { font-weight: 700; }
    .pp-opts-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .pp-opt { min-width: 48px; min-height: 42px; padding: 6px 14px; border: 1px solid #d0d0d0; border-radius: 8px; background: #fff; font-size: 14px; font-weight: 600; color: #333; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .pp-opt img { width: 28px; height: 28px; object-fit: contain; border-radius: 4px; }
    .pp-opt.on { border: 2px solid var(--ml-blue); color: var(--ml-blue); padding: 5px 13px; background: var(--ml-blue-soft); }
    .pp-opt.off { color: #a3a3a3; border-style: dashed; }
    .pp-opt.off span { text-decoration: line-through; }

    .pp-about { margin-top: 12px; padding: 18px 16px; }
    .pp-about h2 { margin: 0 0 10px; font-size: 18px; font-weight: 700; }
    .pp-about h2 + p { margin: 0; white-space: pre-line; color: #555; font-size: 14px; line-height: 1.6; }
    .pp-about .pp-sub { margin-top: 20px; }
    .pp-about .ml-specs { margin-top: 0; }

    .pp-related { margin-top: 12px; }
    .pp-related .ml-sec-head h2 i { color: var(--ml-blue); }

    /* Barra de compra fixa no celular (no lugar da barra do carrinho) */
    .pp-buybar { position: fixed; z-index: 70; left: 0; right: 0; bottom: 0; background: #fff; box-shadow: 0 -2px 12px rgba(0,0,0,.12); padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); display: flex; align-items: center; gap: 10px; }
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
        .pp-main .ml-gal-wrap { position: sticky; top: 80px; }
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
        .pp-related .ml-rail .ml-card { flex-basis: 184px; }
    }
    @media (min-width: 1180px) {
        .pp-related .ml-rail .ml-card { flex-basis: 196px; }
    }
</style>
@endpush

<div class="ml-wrap pp" x-data="mlCatalog(@js($all), @js($cartUrl), @js($stockMap), {
        p: @js($d),
        setup() { if (! this.p.buyable) this.qty = 0; },
        pick(id) {
            const next = this.products[id];
            if (! next || next.id === this.p.id) return;
            this.p = next;
            this.qty = next.buyable ? 1 : 0;
            this.slide = 0;
            if (this.$refs.track) this.$refs.track.scrollLeft = 0;
            try { history.replaceState(null, '', next.url); } catch (e) {}
            document.title = next.name + ' · ' + @js($store?->name ?: config('app.name'));
        },
    })" @keydown.escape.window="closeCart()" @open-cart.window="openCart()">

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

    <section class="ml-box pp-main">
        <div class="ml-gal-wrap">
            <div class="ml-gal">
                <div class="ml-gal-track" x-ref="track" @scroll.debounce.60ms="slide = Math.round($el.scrollLeft / $el.clientWidth)">
                    <template x-if="!p.imgs.length">
                        <div><div class="ml-ph"><i class="fas fa-image"></i></div></div>
                    </template>
                    <template x-for="(src, i) in p.imgs" :key="p.id + '-' + i">
                        <div><img :src="src" :alt="p.name" :loading="i ? 'lazy' : 'eager'" x-on:error="p.imgs = p.imgs.filter(x => x !== src)"></div>
                    </template>
                </div>
                <span class="ml-gal-n" x-show="p.imgs.length > 1" x-text="(slide + 1) + ' / ' + p.imgs.length"></span>
                <button type="button" class="pp-fav" :class="{ on: $store.favs.has(p.id) }" @click="fav(p.id)"
                        :aria-pressed="$store.favs.has(p.id)" :aria-label="$store.favs.has(p.id) ? 'Tirar dos favoritos' : 'Salvar nos favoritos'" data-testid="page-fav">
                    <i class="fa-heart" :class="$store.favs.has(p.id) ? 'fas' : 'far'"></i>
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
            <div class="ml-cat-line" x-text="[p.cat, p.brand].filter(Boolean).join(' · ') || 'Produto'">{{ collect([$d['cat'], $d['brand']])->filter()->join(' · ') ?: 'Produto' }}</div>
            <h1 x-text="p.name">{{ $d['name'] }}</h1>
            <span class="ml-deal" x-show="p.pct" @if(! $d['pct']) x-cloak @endif>Oferta do dia</span>
            <div class="ml-old" x-show="p.old" x-text="money(p.old)" @if(! $d['old']) x-cloak @endif>@if($d['old'])R$ {{ number_format($d['old'], 2, ',', '.') }}@endif</div>
            <div class="ml-price-row" x-show="p.price > 0" @if(! ($d['price'] > 0)) x-cloak @endif>
                <span class="ml-price"><span class="cur">R$</span><span x-text="intPart(p.price)">{{ $int }}</span><sup x-show="cents(p.price) !== '00'" x-text="cents(p.price)" @if($cents === '00') x-cloak @endif>{{ $cents }}</sup></span>
                <span class="ml-pct" x-show="p.pct" x-text="p.pct + '% OFF'" @if(! $d['pct']) x-cloak @endif>{{ $d['pct'] }}% OFF</span>
            </div>
            <div class="ml-ask" x-show="!(p.price > 0)" @if($d['price'] > 0) x-cloak @endif>Preço sob consulta</div>
            @if($family->count() > 1 && $family->pluck('price_sale')->unique()->count() > 1)
                <div class="ml-stock" style="margin-top:4px">A partir de R$ {{ number_format($minPrice, 2, ',', '.') }}, conforme {{ mb_strtolower($attribute) }}</div>
            @endif
            <div class="ml-ends" x-show="p.ends" style="margin-top:6px" x-cloak><i class="fas fa-clock"></i> <span x-text="p.ends"></span></div>

            <div class="pp-out" x-show="!p.buyable" x-cloak data-testid="sold-out">
                <i class="fas fa-box-open"></i>
                <span><b>Esgotado no momento</b><small>Toque no coração para salvar. Avisamos quando voltar ao estoque.</small></span>
            </div>

            @if($family->count() > 1)
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
            <div class="ml-perk">
                <i class="fas fa-truck-fast"></i>
                <span><b>Combine a entrega</b> com a loja<small>Retirada ou envio, você escolhe ao finalizar.</small></span>
            </div>
            <div class="ml-perk">
                <i class="fas fa-shield-halved"></i>
                <span>Compra direta com <b style="color:#333">{{ $store?->name ?: 'a loja' }}</b><small>Seu pedido chega para a loja confirmar.</small></span>
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
            </div>
        </div>
    </section>

    <section class="ml-box pp-about" x-show="p.desc || p.brand || p.model || p.code || (p.specs && p.specs.length)">
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

    @if($related->isNotEmpty())
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
@endpush

</x-portal-catalog-layout>
