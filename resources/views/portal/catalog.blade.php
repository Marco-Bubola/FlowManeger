@php
    $owner = $ownerId;
    $catalogUrl = fn (array $q = []) => route('portal.catalog', array_merge(
        ['userId' => $owner],
        array_filter(['search' => $search, 'category' => $category, 'ordem' => $sort, 'ofertas' => $onlyOffers ? 1 : null], fn ($v) => $v !== '' && $v !== null),
        $q
    ));
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);

    // Dados de cada produto para o card, a ficha e o carrinho (montados no controller).
    $info = fn ($p) => \App\Http\Controllers\ClientPortalController::cardInfo($p);

    $all = collect();
    foreach ($offers as $o) { $all[$o->id] = $info($o); }
    foreach ($products as $p) { $all[$p->id] = $info($p); }
    $showOffers = $offers->isNotEmpty() && ! $search && ! $category && ! $onlyOffers && $products->currentPage() === 1;
@endphp

<x-portal-catalog-layout title="Catálogo" :store="$store" :owner-id="$owner" :search="$search">

@push('styles')
@include('portal.partials.catalog-styles')
@endpush

<div class="ml-wrap" x-data="mlCatalog(@js($all), @js($cartUrl), @js($stockMap))" @keydown.escape.window="closeCart()" @open-cart.window="openCart()">

    @if($showOffers)
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

    @if($products->currentPage() === 1 && ! $search && ! $category && ! $onlyOffers)
        @include('portal.partials.recently-viewed')
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

        {{-- Rolagem infinita: carrega a próxima leva ao chegar perto do fim --}}
        <div class="ml-more" x-ref="more" data-next="{{ $products->nextPageUrl() }}">
            <template x-if="moreLoading"><span class="ml-more-load"><span class="ml-spin"></span> Carregando mais produtos…</span></template>
            <template x-if="moreError && !moreLoading"><button type="button" class="ml-btn ml-btn-sm" @click="loadMore()">Tentar de novo</button></template>
            <template x-if="!moreNext && !moreLoading"><span class="ml-more-end">Você viu todos os produtos</span></template>
        </div>
    @endif

    @include('portal.partials.catalog-cart')
</div>

@push('scripts')
@include('portal.partials.catalog-script')
@endpush

</x-portal-catalog-layout>
