@php
    $owner = $ownerId;
    $catalogUrl = fn (array $q = []) => route('portal.catalog', array_merge(
        ['userId' => $owner],
        array_filter(['search' => $search, 'category' => $category, 'ordem' => $sort, 'ofertas' => $onlyOffers ? 1 : null, 'colecao' => $collection?->slug], fn ($v) => $v !== '' && $v !== null),
        $q
    ));
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);

    // Dados de cada produto para o card, a ficha e o carrinho (montados no controller).
    $info = fn ($p) => \App\Http\Controllers\ClientPortalController::cardInfo($p);

    $all = collect();
    foreach ($offers as $o) { $all[$o->id] = $info($o); }
    foreach ($products as $p) { $all[$p->id] = $info($p); }
    foreach ($featured as $p) { $all[$p->id] ??= $info($p); }
    foreach ($collections as $c) { foreach ($c->items as $p) { $all[$p->id] ??= $info($p); } }
    $showOffers = $offers->isNotEmpty() && $isHome;

    // Vitrine (Promoções › Vitrine do catálogo)
    $front = \App\Services\Portal\StorefrontService::class;
    $heroBanners = $isHome ? ($banners['hero'] ?? collect()) : collect();
    $strips = $isHome ? ($banners['strip'] ?? collect())->values() : collect();
    $sides = ($banners['side'] ?? collect())->values();
    $homeLayout = \App\Services\Portal\HomeLayout::get($settings ?? new \App\Models\PortalSetting(['user_id' => $ownerId]), true);
    $homeOrder = $homeLayout['order'];
    $homeOff = $isHome ? $homeLayout['off'] : [];
    $notice = $settings && $settings->notice_active && trim((string) $settings->notice_text) !== '' ? $settings : null;
@endphp

<x-portal-catalog-layout title="Catálogo" :store="$store" :owner-id="$owner" :search="$search">

@push('styles')
@include('portal.partials.catalog-styles')
@include('portal.partials.storefront-styles')
@endpush

<div class="ml-wrap" x-data="mlCatalog(@js($all), @js($cartUrl), @js($stockMap))" @keydown.escape.window="closeCart()" @open-cart.window="openCart()">

    @if($notice)
        @php $nHref = preg_match('~^https?://~i', (string) $notice->notice_link) ? $notice->notice_link : null; @endphp
        <{{ $nHref ? 'a' : 'p' }} @if($nHref) href="{{ $nHref }}" @endif class="sf-notice" data-testid="store-notice">
            <i class="fas fa-bullhorn"></i><span>{{ $notice->notice_text }}</span>
        </{{ $nHref ? 'a' : 'p' }}>
    @endif

    {{-- Blocos da página inicial na ordem da Vitrine › Página inicial (os escondidos ficam de fora só na inicial) --}}
    @foreach($homeOrder as $sec)
        @continue(in_array($sec, $homeOff, true))
        @if($sec === 'hero')
            @if($heroBanners->isNotEmpty())
                <section class="sf-hero-wrap" x-data="sfHero({{ $heroBanners->count() }})" aria-roledescription="carrossel" aria-label="Destaques da loja" data-testid="hero-carousel">
                    <div class="sf-hero-track" x-ref="track">
                        @foreach($heroBanners as $b)
                            @include('portal.partials.storefront-banner', ['b' => $b, 'kind' => 'hero', 'eager' => $loop->first])
                        @endforeach
                    </div>
                    @if($heroBanners->count() > 1)
                        <button type="button" class="sf-arrow l" @click="go(i - 1)" aria-label="Banner anterior"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="sf-arrow r" @click="go(i + 1)" aria-label="Próximo banner"><i class="fas fa-chevron-right"></i></button>
                        <div class="sf-dots">
                            @foreach($heroBanners as $b)
                                <button type="button" :class="i === {{ $loop->index }} && 'on'" @click="go({{ $loop->index }})" aria-label="Banner {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    @endif
                </section>
            @elseif($showOffers)
                <section class="ml-hero">
                    <small><i class="fas fa-bolt"></i> Ofertas da semana</small>
                    <h1>Até {{ $offers->max(fn ($o) => $o->livePromotion()->discount_percent) }}% OFF em produtos selecionados</h1>
                    <p>Escolha, adicione ao carrinho e finalize seu pedido em poucos toques.</p>
                    <div class="ml-hero-stats">
                        <span><i class="fas fa-tag"></i> {{ $offers->count() }} {{ $offers->count() === 1 ? 'oferta' : 'ofertas' }}</span>
                        <span><i class="fas fa-box"></i> {{ $stockMap->count() }} produtos disponíveis</span>
                    </div>
                </section>
            @endif
        @elseif($sec === 'cats')
            @if($categories->count())
                <nav class="ml-cats" aria-label="Categorias">
                    <a href="{{ route('portal.catalog', ['userId' => $owner]) }}" class="ml-cat {{ ! $category && ! $onlyOffers ? 'on' : '' }}">
                        <span class="ml-cat-ico"><i class="fas fa-store"></i></span>
                        <span>Tudo ({{ $stockMap->count() }})</span>
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
                            <span>{{ $cat->name }} ({{ $countByCategory[$cat->getKey()] ?? 0 }})</span>
                        </a>
                    @endforeach
                </nav>
            @endif
        @elseif($sec === 'featured')
            @if($featured->isNotEmpty())
                <section class="ml-box ml-offers" data-testid="featured">
                    <div class="ml-sec-head">
                        <h2><i class="fas fa-star sf-star"></i> {{ $settings?->featured_title ?: 'Destaques da loja' }}</h2>
                    </div>
                    <div class="ml-rail">
                        @foreach($featured as $p)
                            @include('portal.partials.ml-card', ['d' => $all[$p->id]])
                        @endforeach
                    </div>
                </section>
            @endif
        @elseif($sec === 'offers')
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
        @elseif($sec === 'collections')
            {{-- Faixas e coleções: uma faixa depois das ofertas e outra depois de cada coleção --}}
            @if($strips->isNotEmpty())
                @include('portal.partials.storefront-banner', ['b' => $strips[0], 'kind' => 'strip'])
            @endif
            @foreach($collections as $c)
                @php $cHref = route('portal.catalog', ['userId' => $owner, 'colecao' => $c->slug]); @endphp
                <section class="ml-box sf-col" data-testid="collection-{{ $c->slug }}">
                    @include('portal.partials.collection-head', ['c' => $c, 'href' => $cHref])
                    <div class="ml-rail">
                        @foreach($c->items as $p)
                            @include('portal.partials.ml-card', ['d' => $all[$p->id]])
                        @endforeach
                    </div>
                </section>
                @if(isset($strips[$loop->iteration]))
                    @include('portal.partials.storefront-banner', ['b' => $strips[$loop->iteration], 'kind' => 'strip'])
                @endif
            @endforeach
            @foreach($strips->slice($collections->count() + 1) as $b)
                @include('portal.partials.storefront-banner', ['b' => $b, 'kind' => 'strip'])
            @endforeach
        @elseif($sec === 'kits')
            @if($isHome && \App\Services\Portal\KitService::enabled($owner))
                @include('portal.partials.kit-home')
            @endif
        @elseif($sec === 'recent')
            @if($isHome)
                @include('portal.partials.recently-viewed')
            @endif
        @endif
    @endforeach

    @if($collection)
        <section class="sf-col-page" data-testid="collection-head">
            @include('portal.partials.collection-head', ['c' => $collection, 'href' => null])
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
        <span class="ml-count-txt">{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }} disponíveis</span>
        @if($offers->isNotEmpty())
            <a href="{{ $catalogUrl(['ofertas' => $onlyOffers ? null : 1, 'page' => null]) }}" class="ml-chip hot {{ $onlyOffers ? 'on' : '' }}">
                <i class="fas fa-fire"></i> Ofertas
                @if($onlyOffers)<i class="fas fa-xmark"></i>@endif
            </a>
        @endif
        <a href="{{ Auth::guard('portal')->check() ? route('portal.favorites') : route('portal.favorites', ['loja' => $owner]) }}" class="ml-chip fav" x-data data-testid="chip-favorites">
            <i class="fas fa-heart"></i> Favoritos
            <span x-show="$store.favs.count > 0" x-cloak x-text="'(' + $store.favs.count + ')'"></span>
        </a>
        <form method="GET" action="{{ route('portal.catalog', ['userId' => $owner]) }}" class="ml-chip {{ $sort ? 'on' : '' }}">
            @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            @if($onlyOffers)<input type="hidden" name="ofertas" value="1">@endif
            @if($collection)<input type="hidden" name="colecao" value="{{ $collection->slug }}">@endif
            <i class="fas fa-arrow-down-wide-short"></i>
            <select name="ordem" onchange="this.form.submit()" aria-label="Ordenar">
                <option value="" @selected(! $sort)>Mais relevantes</option>
                <option value="menor" @selected($sort === 'menor')>Menor preço</option>
                <option value="maior" @selected($sort === 'maior')>Maior preço</option>
                <option value="novos" @selected($sort === 'novos')>Novidades</option>
                <option value="vendidos" @selected($sort === 'vendidos')>Mais vendidos</option>
                <option value="nome" @selected($sort === 'nome')>Nome A-Z</option>
            </select>
            <i class="fas fa-chevron-down" style="font-size:10px"></i>
        </form>
        @if($collection)
            <a href="{{ $catalogUrl(['colecao' => null, 'page' => null]) }}" class="ml-chip on" data-testid="chip-collection">
                {{ $collection->name }} <i class="fas fa-xmark"></i>
            </a>
        @endif
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

        {{-- Rolagem infinita: carrega a próxima leva ao chegar perto do fim --}}
        <div class="ml-more" x-ref="more" data-next="{{ $products->nextPageUrl() }}">
            <template x-if="moreLoading"><span class="ml-more-load"><span class="ml-spin"></span> Carregando mais produtos…</span></template>
            <template x-if="moreError && !moreLoading"><button type="button" class="ml-btn ml-btn-sm" @click="loadMore()">Tentar de novo</button></template>
            <template x-if="!moreNext && !moreLoading"><span class="ml-more-end">Você viu todos os produtos</span></template>
        </div>
    @endif

    @include('portal.partials.catalog-cart')

    {{-- Banners laterais: só aparecem quando a tela é larga o bastante para sobrar espaço --}}
    @if($sides->isNotEmpty())
        @foreach(['right' => $sides->filter(fn ($b, $k) => $k % 2 === 0), 'left' => $sides->filter(fn ($b, $k) => $k % 2 === 1)] as $sideName => $list)
            @if($list->isNotEmpty())
                <aside class="sf-side {{ $sideName }}" aria-label="Banners">
                    @foreach($list as $b)
                        @include('portal.partials.storefront-banner', ['b' => $b, 'kind' => 'side'])
                    @endforeach
                </aside>
            @endif
        @endforeach
    @endif
</div>

@push('scripts')
@include('portal.partials.catalog-script')
@include('portal.partials.storefront-script')
@endpush

</x-portal-catalog-layout>
