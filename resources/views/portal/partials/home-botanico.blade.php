{{-- Página inicial no modelo Elegante: foto grande no topo, títulos com letra fina e itálico, cards altos com
     "+ Carrinho", texto da loja que acende ao rolar, categorias em fotos altas, nova chegada e nota das avaliações. --}}
@php extract(\App\Services\Portal\HomeModelData::build(get_defined_vars())); @endphp

@php
    $hmBg = $hmBannerImg ?: ($hmLoved->first()['img'] ?? $hmNew->first()['img'] ?? null);
    $hmWords = preg_split('/\s+/', $hmTitle) ?: [];
    $hmLast = count($hmWords) > 1 ? array_pop($hmWords) : null;
@endphp
<section class="hm-hero hm-sec {{ $hmBg ? 'has-photo' : '' }}" data-testid="hm-hero">
    @if($hmBg)<img class="hm-hero-photo" src="{{ $hmBg }}" alt="">@endif
    <div class="hm-hero-text">
        @if($hmRate)
            <span class="hm-chip"><span class="hm-stars">★★★★★</span> {{ number_format($hmRate['avg'], 1, ',', '') }} · {{ $hmRate['n'] }} {{ $hmRate['n'] === 1 ? 'avaliação' : 'avaliações' }}</span>
        @endif
        <h1 class="hm-hero-title hm-words">{{ implode(' ', $hmWords) }}@if($hmLast) <em>{{ $hmLast }}</em>@endif</h1>
        @if($hmSub !== '')<p class="hm-kicker">{{ $hmSub }}</p>@endif
        <a href="{{ $hmHref }}" class="hm-btn">{{ $hmBtn }} <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

@if($hmLoved->isNotEmpty())
    <section class="hm-sec hm-block" data-testid="hm-loved">
        <div class="hm-head">
            @include('portal.partials.home-title', ['t1' => 'Mais vendidos', 't2' => 'Produtos'])
            <a href="{{ $catalogUrl(['ordem' => 'vendidos']) }}#todos" class="hm-more">Ver todos <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="hm-row">
            @foreach($hmLoved as $d) @include('portal.partials.home-mini-card') @endforeach
        </div>
    </section>
@endif

@if($hmAboutShort !== '')
    <section class="hm-sec hm-about" data-testid="hm-about">
        <p class="hm-about-kicker">Sobre a {{ $hmId['name'] }}</p>
        <p class="hm-about-text" data-mo-read>{{ $hmAboutShort }}</p>
        <a href="{{ route('portal.store-page', ['userId' => $owner, 'page' => 'sobre']) }}" class="hm-more">Conheça nossa história <i class="fas fa-arrow-right"></i></a>
    </section>
@endif

@if($hmCats->count() > 1)
    <section class="hm-sec hm-block" data-testid="hm-cats">
        <div class="hm-head">@include('portal.partials.home-title', ['t1' => 'Explore', 't2' => 'Categorias'])</div>
        <div class="hm-cats">
            @foreach($hmCats as $c)
                <a href="{{ $c['url'] }}" class="hm-cat">
                    <span class="hm-cat-img">@if($c['img'])<img src="{{ $c['img'] }}" alt="" loading="lazy">@else<i class="fas fa-leaf"></i>@endif</span>
                    <span class="hm-cat-txt"><b>{{ $c['name'] }}</b><small>{{ $c['n'] }} {{ $c['n'] === 1 ? 'item' : 'itens' }}</small></span>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if($hmNewest)
    <section class="hm-sec hm-spot" data-testid="hm-spot">
        <a href="{{ $hmNewest['url'] }}" class="hm-spot-img"><img src="{{ $hmNewest['img'] }}" alt="{{ $hmNewest['name'] }}" loading="lazy"></a>
        <div class="hm-spot-text">
            <p class="hm-about-kicker">Nova chegada</p>
            <h2 class="hm-words">{{ $hmNewest['name'] }}</h2>
            @if($hmNewest['price'] > 0)<p class="hm-spot-price">{{ $hmMoney($hmNewest['price']) }}@if($hmNewest['old']) <s>{{ $hmMoney($hmNewest['old']) }}</s>@endif</p>@endif
            <div class="hm-spot-btns">
                <button type="button" class="hm-btn" @click="add(products[{{ $hmNewest['id'] }}])"><i class="fas fa-plus"></i> Carrinho</button>
                <a href="{{ $hmNewest['url'] }}" class="hm-more">Ver detalhes <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>
@endif

@if($hmRate && $hmReviews->isNotEmpty())
    <section class="hm-sec hm-block hm-rating" data-testid="hm-reviews">
        <div class="hm-rating-big">
            <b>{{ number_format($hmRate['avg'], 1, ',', '') }}</b><span>/5</span>
            <span class="hm-stars" style="--v: {{ $hmRate['avg'] }}">★★★★★</span>
            <small>{{ $hmRate['n'] }} {{ $hmRate['n'] === 1 ? 'avaliação' : 'avaliações' }} de clientes</small>
        </div>
        <div class="hm-quotes">
            @foreach($hmReviews as $r)
                <figure class="hm-quote">
                    <span class="hm-stars">{{ str_repeat('★', (int) $r->rating) }}</span>
                    <blockquote>“{{ \Illuminate\Support\Str::limit($r->comment, 200) }}”</blockquote>
                    <figcaption>{{ \App\Models\PortalReview::shortName($r->name) ?: 'Cliente' }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
