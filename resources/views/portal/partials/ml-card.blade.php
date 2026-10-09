{{-- Card do catálogo público. Recebe $d com os dados montados em portal/catalog. --}}
@php
    $int = number_format(floor($d['price']), 0, ',', '.');
    $cents = str_pad((string) (round($d['price'] * 100) % 100), 2, '0', STR_PAD_LEFT);
@endphp
<div class="ml-card" role="button" tabindex="0" @click="open({{ $d['id'] }})" @keydown.enter="open({{ $d['id'] }})">
    <div class="ml-card-img">
        @if($d['img'])
            <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}" loading="lazy" onerror="this.outerHTML='<div class=&quot;ml-ph&quot;><i class=&quot;fas fa-image&quot;></i></div>'">
        @else
            <div class="ml-ph"><i class="fas fa-image"></i></div>
        @endif
        @if($d['pct'])
            <span class="ml-off-flag">-{{ $d['pct'] }}%</span>
        @endif
        @if(count($d['imgs']) > 1)
            <span class="ml-imgs"><i class="fas fa-images"></i> {{ count($d['imgs']) }}</span>
        @endif
        <button type="button" class="ml-add" :class="{ in: $store.cart.has({{ $d['id'] }}) }"
                @click.stop="add(products[{{ $d['id'] }}])" aria-label="Adicionar ao carrinho">
            <i class="fas" :class="$store.cart.has({{ $d['id'] }}) ? 'fa-check' : 'fa-cart-plus'"></i>
        </button>
    </div>
    <div class="ml-card-body">
        @if($d['pct'])
            <span class="ml-deal">Oferta do dia</span>
        @endif
        <p class="ml-title">{{ $d['name'] }}</p>
        @if($d['old'])
            <span class="ml-old">R$ {{ number_format($d['old'], 2, ',', '.') }}</span>
        @endif
        @if($d['price'] > 0)
            <div class="ml-price-row">
                <span class="ml-price"><span class="cur">R$</span>{{ $int }}@if($cents !== '00')<sup>{{ $cents }}</sup>@endif</span>
                @if($d['pct'])<span class="ml-pct">{{ $d['pct'] }}% OFF</span>@endif
            </div>
        @else
            <span class="ml-ask">Preço sob consulta</span>
        @endif
        @if($d['ends'])
            <span class="ml-ends"><i class="fas fa-clock"></i> {{ $d['ends'] }}</span>
        @endif
        {{-- O cliente não vê quantas unidades há em estoque. --}}
        @if($d['stock'] <= 3)
            <span class="ml-stock low">Últimas unidades!</span>
        @else
            <span class="ml-stock"><i class="fas fa-box"></i> Em estoque</span>
        @endif
    </div>
</div>
