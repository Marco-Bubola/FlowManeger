{{-- Página inicial no modelo Editorial: títulos grandes em maiúsculas, produto girando no topo, faixa correndo,
     novidades soltas no fundo claro, mais amados em mosaico e depoimentos. Só dados reais da loja. --}}
@php extract(\App\Services\Portal\HomeModelData::build(get_defined_vars())); @endphp

<section class="hm-hero hm-sec" data-testid="hm-hero">
    <div class="hm-hero-text">
        @if($hmSub !== '')<p class="hm-kicker">{{ $hmSub }}</p>@endif
        <h1 class="hm-hero-title hm-words">{{ $hmTitle }}</h1>
        <div class="hm-hero-cta">
            <a href="{{ $hmHref }}" class="hm-btn">{{ $hmBtn }} <i class="fas fa-arrow-right"></i></a>
            <span class="hm-hero-note"><b>{{ $stockMap->count() }}</b> produtos<br>prontos para você</span>
        </div>
    </div>
    <div class="hm-hero-art" aria-hidden="{{ $hmSpin->isEmpty() ? 'true' : 'false' }}">
        @if($hmSpin->isNotEmpty())
            @php $hmN = $hmSpin->count(); $hmV = 100 / $hmN; @endphp
            {{-- Cada produto aparece por 1/N do ciclo e gira para dar lugar ao próximo --}}
            <style>@keyframes hm-spin { 0% { opacity: 0; transform: rotate(-8deg) scale(.9); } {{ round($hmV * .18, 2) }}% { opacity: 1; transform: none; } {{ round($hmV * .82, 2) }}% { opacity: 1; transform: none; } {{ round($hmV, 2) }}% { opacity: 0; transform: rotate(7deg) scale(.94); } 100% { opacity: 0; } }</style>
            <div class="hm-spin" style="--n: {{ $hmN }}">
                @foreach($hmSpin as $i => $d)
                    <a href="{{ $d['url'] }}" class="hm-spin-item" style="--i: {{ $i }}">
                        <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}">
                        <span class="hm-spin-tag"><b>{{ $d['name'] }}</b>@if($d['price'] > 0) {{ $hmMoney($d['price']) }}@endif</span>
                    </a>
                @endforeach
            </div>
        @elseif($hmBannerImg)
            <img class="hm-hero-photo" src="{{ $hmBannerImg }}" alt="">
        @endif
    </div>
</section>

@if($hmStrip->isNotEmpty())
    <div class="hm-strip" aria-label="Destaques da loja">
        <div class="hm-strip-run">
            @for($k = 0; $k < 2; $k++)
                @foreach($hmStrip as $s)<span @if($k) aria-hidden="true" @endif>{{ $s }}</span><i aria-hidden="true">✺</i>@endforeach
            @endfor
        </div>
    </div>
@endif

@if($hmNew->isNotEmpty())
    <section class="hm-sec hm-block" data-testid="hm-new">
        <div class="hm-head">
            @include('portal.partials.home-title', ['t1' => 'Conheça as', 't2' => 'novidades'])
            <a href="{{ $catalogUrl(['ordem' => 'novos']) }}#todos" class="hm-more">Ver todas <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="hm-row">
            @foreach($hmNew as $d) @include('portal.partials.home-mini-card') @endforeach
        </div>
    </section>
@endif

@if($hmCats->count() > 1)
    <section class="hm-sec hm-block" data-testid="hm-cats">
        <div class="hm-head">@include('portal.partials.home-title', ['t1' => 'Compre por', 't2' => 'categoria'])</div>
        <div class="hm-cats">
            @foreach($hmCats as $c)
                <a href="{{ $c['url'] }}" class="hm-cat">
                    <span class="hm-cat-img">@if($c['img'])<img src="{{ $c['img'] }}" alt="" loading="lazy">@else<i class="fas fa-tag"></i>@endif</span>
                    <b>{{ $c['name'] }}</b>
                    <small>{{ $c['n'] }} {{ $c['n'] === 1 ? 'item' : 'itens' }}</small>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if($hmLoved->count() >= 3)
    <section class="hm-sec hm-block hm-loved" data-testid="hm-loved">
        <div class="hm-head">@include('portal.partials.home-title', ['t1' => 'Os mais', 't2' => 'amados'])</div>
        <div class="hm-mosaic">
            @foreach($hmLoved as $i => $d)
                <a href="{{ $d['url'] }}" class="hm-tile {{ $i === 0 ? 'big' : '' }}">
                    <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}" loading="lazy">
                    <span class="hm-tile-info"><b>{{ $d['name'] }}</b>@if($d['price'] > 0)<small>{{ $hmMoney($d['price']) }}</small>@endif</span>
                </a>
            @endforeach
        </div>
    </section>
@endif

@php $hmBig = $hmBannerImg ?: ($hmLoved->first()['img'] ?? null); @endphp
@if($hmBig)
    <section class="hm-sec hm-feature" data-testid="hm-feature">
        <img src="{{ $hmBig }}" alt="" loading="lazy">
        <div class="hm-feature-text">
            <h2 class="hm-words">{{ $hmAboutShort !== '' ? \Illuminate\Support\Str::words($hmAboutShort, 9) : ($hmId['tagline'] ?: 'Feito para a sua casa') }}</h2>
            <a href="{{ $hmAbout !== '' ? route('portal.store-page', ['userId' => $owner, 'page' => 'sobre']) : '#todos' }}" class="hm-btn light">{{ $hmAbout !== '' ? 'Nossa história' : 'Ver produtos' }} <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>
@endif

@if($hmReviews->isNotEmpty())
    <section class="hm-sec hm-block" data-testid="hm-reviews">
        <div class="hm-head">
            @include('portal.partials.home-title', ['t1' => 'Quem comprou', 't2' => 'recomenda'])
            @if($hmRate)<span class="hm-rate"><b>{{ number_format($hmRate['avg'], 1, ',', '') }}</b>/5 · {{ $hmRate['n'] }} {{ $hmRate['n'] === 1 ? 'avaliação' : 'avaliações' }}</span>@endif
        </div>
        <div class="hm-quotes">
            @foreach($hmReviews as $r)
                <figure class="hm-quote">
                    <span class="hm-stars" aria-label="Nota {{ $r->rating }} de 5">{{ str_repeat('★', (int) $r->rating) }}</span>
                    <blockquote>“{{ \Illuminate\Support\Str::limit($r->comment, 220) }}”</blockquote>
                    <figcaption>{{ \App\Models\PortalReview::shortName($r->name) ?: 'Cliente' }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
