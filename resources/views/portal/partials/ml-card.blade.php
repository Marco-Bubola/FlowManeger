{{-- Card do catálogo público. Recebe $d de ClientPortalController::cardInfo; abre a página do produto. --}}
@php
    $int = number_format(floor($d['price']), 0, ',', '.');
    $cents = str_pad((string) (round($d['price'] * 100) % 100), 2, '0', STR_PAD_LEFT);
@endphp
<div class="ml-card" data-hover-card>
    <div class="ml-card-img" @if(count($d['imgs']) > 1) data-hover-photos='@json(array_values(array_slice($d['imgs'], 0, 8)))' @endif>
        @if($d['img'])
            <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}" data-hp-img loading="lazy" onerror="this.outerHTML='<div class=&quot;ml-ph&quot;><i class=&quot;fas fa-image&quot;></i></div>'">
        @else
            <div class="ml-ph"><i class="fas fa-image"></i></div>
        @endif
        @if($d['pct'])
            <span class="ml-off-flag">-{{ $d['pct'] }}%</span>
        @endif
        @if(! empty($d['new']))
            <span class="ml-new-flag {{ $d['pct'] ? 'low' : '' }}" data-testid="new-flag">Novidade</span>
        @endif
        @if(count($d['imgs']) > 1)
            <span class="ml-imgs"><i class="fas fa-images"></i> {{ count($d['imgs']) }}</span>
            <span class="hp-dots" data-hp-dots aria-hidden="true">@for($k = 0; $k < min(count($d['imgs']), 5); $k++)<i class="{{ $k === 0 ? 'on' : '' }}"></i>@endfor</span>
        @endif
        <button type="button" class="ml-fav" :class="{ on: $store.favs.has({{ $d['id'] }}) }" @click.stop="fav({{ $d['id'] }})"
                :aria-pressed="$store.favs.has({{ $d['id'] }})" aria-label="Favoritar" data-fav="{{ $d['id'] }}">
            <i class="fa-heart" :class="$store.favs.has({{ $d['id'] }}) ? 'fas' : 'far'"></i>
        </button>
        <button type="button" class="ml-add" :class="{ in: $store.cart.has({{ $d['id'] }}) }"
                @click.stop="add(products[{{ $d['id'] }}])" aria-label="Adicionar ao carrinho">
            <i class="fas" :class="$store.cart.has({{ $d['id'] }}) ? 'fa-check' : 'fa-cart-plus'"></i>
        </button>
    </div>
    <div class="ml-card-body">
        @if($d['pct'])
            <span class="ml-deal">Oferta do dia</span>
        @endif
        {{-- O link cobre o card todo (::after); coração e carrinho ficam por cima. --}}
        <a class="ml-title ml-card-link" href="{{ $d['url'] }}">{{ $d['name'] }}</a>
        @if(! empty($d['rate']))
            <span class="ml-rate" aria-label="Nota {{ $d['rate'] }} de 5"><span class="rv-stars" style="--v: {{ $d['rate'] }}">★★★★★</span> <small>({{ $d['rates'] }})</small></span>
        @endif
        @if($d['old'])
            <span class="ml-old">R$ {{ number_format($d['old'], 2, ',', '.') }}</span>
        @endif
        @if($d['price'] > 0)
            <div class="ml-price-row">
                <span class="ml-price"><span class="cur">R$</span>{{ $int }}@if($cents !== '00')<sup>{{ $cents }}</sup>@endif</span>
                @if($d['pct'])<span class="ml-pct">{{ $d['pct'] }}% OFF</span>@endif
            </div>
            @if(! empty($d['pix']))
                <span class="ml-pix" data-testid="card-pix">ou R$ {{ number_format($d['pix'], 2, ',', '.') }} no Pix</span>
            @endif
        @else
            <span class="ml-ask">Preço sob consulta</span>
        @endif
        @if($d['ends'])
            <span class="ml-ends"><i class="fas fa-clock"></i> {{ $d['ends'] }}</span>
        @endif
        {{-- O cliente não vê quantas unidades há em estoque. --}}
        @if(!empty($d['order']))
            <span class="ml-stock"><i class="fas fa-clock"></i> {{ $d['order'] }}</span>
        @elseif($d['stock'] <= 3)
            <span class="ml-stock low">Últimas unidades!</span>
        @else
            <span class="ml-stock"><i class="fas fa-box"></i> Em estoque</span>
        @endif
    </div>
</div>
