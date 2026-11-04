{{-- Card dos modelos Editorial e Elegante. Recebe $d (ClientPortalController::cardInfo). --}}
<div class="hm-card" data-hover-card>
    <a class="hm-card-img" href="{{ $d['url'] }}" @if(count($d['imgs']) > 1) data-hover-photos='@json(array_values(array_slice($d['imgs'], 0, 6)))' @endif>
        <img src="{{ $d['img'] }}" alt="{{ $d['name'] }}" data-hp-img loading="lazy">
        @if($d['pct'])<span class="hm-flag">-{{ $d['pct'] }}%</span>@elseif(! empty($d['new']))<span class="hm-flag new">Novo</span>@endif
    </a>
    <button type="button" class="hm-fav" :class="{ on: $store.favs.has({{ $d['id'] }}) }" @click.stop="fav({{ $d['id'] }})" aria-label="Favoritar">
        <i class="fa-heart" :class="$store.favs.has({{ $d['id'] }}) ? 'fas' : 'far'"></i>
    </button>
    <div class="hm-card-body">
        @if(! empty($d['cat']))<span class="hm-card-cat">{{ $d['cat'] }}</span>@endif
        <a class="hm-card-name" href="{{ $d['url'] }}">{{ $d['name'] }}</a>
        <div class="hm-card-foot">
            <span class="hm-card-price">
                @if($d['price'] > 0){{ $hmMoney($d['price']) }}@if($d['old'])<s>{{ $hmMoney($d['old']) }}</s>@endif @else Sob consulta @endif
            </span>
            <button type="button" class="hm-add" :class="{ in: $store.cart.has({{ $d['id'] }}) }" @click.stop="add(products[{{ $d['id'] }}])" aria-label="Adicionar ao carrinho">
                <i class="fas" :class="$store.cart.has({{ $d['id'] }}) ? 'fa-check' : 'fa-plus'"></i><span>Carrinho</span>
            </button>
        </div>
    </div>
</div>
