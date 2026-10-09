{{-- Meus favoritos: lista de desejos do portal, com o "Avise-me" (promoção / volta ao estoque). --}}
@php
    $logged = (bool) $client;
    $catalogUrl = route('portal.catalog', ['userId' => $storeId]);
    $cartHref = $logged ? route('portal.quotes.create') : $catalogUrl;
    $count = $products->count();
@endphp
<x-portal-catalog-layout title="Meus favoritos" :store="$store" :owner-id="$storeId" :cart-href="$cartHref">

@push('styles')
    @include('portal.partials.orders-styles')
    <style>
        .wl-list { display: grid; grid-template-columns: minmax(0, 1fr); gap: 10px; }
        @media (min-width: 760px) { .wl-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } }
        .wl-card { position: relative; display: flex; gap: 12px; padding: 12px; background: var(--ml-card); border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); }
        .wl-card.is-new { box-shadow: 0 0 0 2px #f9a8d4, var(--ml-shadow); }
        .wl-img { position: relative; width: 104px; height: 104px; flex-shrink: 0; border-radius: 8px; border: 1px solid var(--ml-line); background: #fff; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #d0d0d0; font-size: 26px; }
        .wl-img img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
        .wl-img.dim img { opacity: .45; filter: grayscale(.6); }
        .wl-img .wl-off { position: absolute; left: 4px; top: 4px; background: var(--ml-green); color: #fff; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
        .wl-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .wl-name { margin: 0 34px 0 0; font-size: 14px; line-height: 1.3; color: #333; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .wl-heart { position: absolute; right: 6px; top: 6px; width: 36px; height: 36px; border: 0; border-radius: 50%; background: transparent; color: #ec4899; font-size: 18px; cursor: pointer; }
        .wl-heart:active { background: #fdf2f8; }
        .wl-old { font-size: 12px; color: #999; text-decoration: line-through; margin-top: 4px; }
        .wl-price-row { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
        .wl-price { font-size: 20px; color: #333; white-space: nowrap; }
        .wl-pct { color: var(--ml-green); font-size: 13px; font-weight: 600; }
        .wl-ask { font-size: 13px; color: var(--ml-blue); font-weight: 600; margin-top: 4px; }
        .wl-av { font-size: 12px; margin-top: 2px; display: inline-flex; align-items: center; gap: 5px; }
        .wl-av.in { color: var(--ml-green); font-weight: 600; }
        .wl-av.low { color: var(--ml-orange); font-weight: 700; }
        .wl-av.out { color: var(--ml-red); font-weight: 700; }
        .wl-av.off { color: var(--ml-muted); font-weight: 600; }
        .wl-new { align-self: flex-start; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 3px; padding: 3px 8px; border-radius: 999px; background: #fce7f3; color: #9d174d; font-size: 11px; font-weight: 800; }
        .wl-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .wl-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 12px; border-radius: 8px; border: 0; font-size: 13px; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .wl-btn-cart { background: var(--ml-blue); color: #fff; }
        .wl-btn-cart.in { background: var(--ml-blue-soft); color: var(--ml-blue-dark); }
        .wl-bell { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 10px; border-radius: 999px; border: 1px solid var(--ml-line); background: #fff; color: #666; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap; }
        .wl-bell.on { border-color: #f9a8d4; background: #fdf2f8; color: #be185d; }
        .wl-card { flex-wrap: wrap; }
        .wl-bells { flex-basis: 100%; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding-top: 10px; border-top: 1px solid var(--ml-line); }
        .wl-bells-l { font-size: 12px; font-weight: 700; color: var(--ml-muted); margin-right: 2px; }
        .wl-bells-l i { color: #ec4899; }
        .wl-note { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 12px; border-radius: 10px; background: #fff; box-shadow: var(--ml-shadow); font-size: 13px; color: #555; line-height: 1.4; }
        .wl-note > i { color: #ec4899; font-size: 18px; margin-top: 1px; }
        .wl-note-acts { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .wl-empty { text-align: center; padding: 44px 20px; background: #fff; border-radius: var(--ml-radius); box-shadow: var(--ml-shadow); }
        .wl-empty > i { font-size: 44px; color: #f9a8d4; }
        .wl-empty h2 { margin: 14px 0 6px; font-size: 17px; }
        .wl-empty p { margin: 0 0 16px; color: var(--ml-muted); font-size: 14px; }
        .wl-toast { position: fixed; z-index: 95; left: 50%; bottom: 24px; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; box-shadow: 0 6px 20px rgba(0,0,0,.25); white-space: nowrap; }
    </style>
@endpush

@if(! $logged && ! $guestHasIds)
    {{-- Sem login os favoritos estão no aparelho: manda os ids para o servidor montar a lista. --}}
    <script>
        (function () {
            try {
                var ids = (JSON.parse(localStorage.getItem('portal_favs') || '[]') || []).filter(Boolean);
                if (ids.length) {
                    var u = new URL(location.href);
                    u.searchParams.set('ids', ids.join(','));
                    location.replace(u.toString());
                }
            } catch (e) {}
        })();
    </script>
@endif

<div class="po-page" x-data="wlPage(@js(route('portal.favorites.notify', ['product' => '__ID__'])))">
    <div class="po-head">
        <div>
            <h1>Meus favoritos</h1>
            <p>
                @if($count)
                    {{ $count }} {{ $count === 1 ? 'produto salvo' : 'produtos salvos' }} em {{ $store?->name ?? 'a loja' }}
                @else
                    Salve aqui o que você gostou para comprar depois.
                @endif
            </p>
        </div>
        <a href="{{ $catalogUrl }}" class="po-btn po-btn-ghost"><i class="fas fa-store"></i> Catálogo</a>
    </div>

    @unless($logged)
        <div class="wl-note" data-testid="guest-note">
            <i class="fas fa-bell"></i>
            <div>
                Seus favoritos estão guardados <b>neste aparelho</b>. Entre ou crie sua conta para salvá-los e
                <b>a loja te avisar</b> quando entrarem em promoção ou voltarem ao estoque.
                <div class="wl-note-acts">
                    <a href="{{ route('portal.login') }}" class="po-btn po-btn-primary" style="height:36px">Entrar</a>
                    <a href="{{ route('portal.register', ['loja' => $storeId]) }}" class="po-btn po-btn-soft" style="height:36px">Criar conta</a>
                </div>
            </div>
        </div>
    @endunless

    @if($count === 0)
        <div class="wl-empty" data-testid="favorites-empty">
            <i class="fas fa-heart"></i>
            <h2>Você ainda não tem favoritos</h2>
            <p>Toque no <i class="far fa-heart"></i> dos produtos do catálogo para salvar aqui.</p>
            <a href="{{ $catalogUrl }}" class="po-btn po-btn-primary">Ver produtos</a>
        </div>
    @else
        <div class="wl-list">
            @foreach($products as $p)
                @php
                    $promo = $p->livePromotion();
                    $price = $promo ? (float) $promo->promo_price : (float) $p->price_sale;
                    $av = $wishlist->availability($p);
                    $fav = $favorites[$p->id] ?? null;
                    $new = $alerts[$p->id] ?? collect();
                    $newPromo = $new->contains('reason', 'promo');
                    $newStock = $new->contains('reason', 'stock');
                    $img = $p->all_images[0] ?? null;
                    $name = $wishlist->productName($p);
                    $buyable = in_array($av['key'], ['in', 'low'], true);
                    // Sem quantidade de estoque: o carrinho confere no checkout.
                    $cartItem = ['id' => $p->id, 'name' => $name, 'price' => $price, 'stock' => 0, 'img' => $img];
                @endphp
                <article class="wl-card {{ $new->isNotEmpty() ? 'is-new' : '' }}" data-testid="fav-card" data-product="{{ $p->id }}" data-availability="{{ $av['key'] }}"
                         x-show="$store.favs.has({{ $p->id }})" x-transition.opacity>
                    <a href="{{ $buyable ? route('portal.catalog', ['userId' => $storeId, 'produto' => $p->id]) : '#' }}" class="wl-img {{ $buyable ? '' : 'dim' }}" @if(! $buyable) onclick="return false" @endif>
                        @if($img)
                            <img src="{{ $img }}" alt="" loading="lazy" onerror="this.remove()">
                        @else
                            <i class="fas fa-image"></i>
                        @endif
                        @if($promo)<span class="wl-off">-{{ $promo->discount_percent }}%</span>@endif
                    </a>
                    <div class="wl-body">
                        @if($newPromo)
                            <span class="wl-new"><i class="fas fa-tag"></i> Entrou em promoção!</span>
                        @elseif($newStock)
                            <span class="wl-new"><i class="fas fa-box-open"></i> Voltou ao estoque!</span>
                        @endif
                        <p class="wl-name">{{ $name }}</p>
                        @if($promo)
                            <span class="wl-old">R$ {{ number_format((float) $promo->original_price, 2, ',', '.') }}</span>
                        @endif
                        @if($price > 0)
                            <div class="wl-price-row">
                                <span class="wl-price">R$ {{ number_format($price, 2, ',', '.') }}</span>
                                @if($promo)<span class="wl-pct">{{ $promo->discount_percent }}% OFF</span>@endif
                            </div>
                        @else
                            <span class="wl-ask">Preço sob consulta</span>
                        @endif

                        @if($av['key'] === 'out')
                            <span class="wl-av out" data-testid="fav-availability">
                                <i class="fas fa-circle-xmark"></i>
                                @if($logged && $fav)
                                    <span x-text="notify[{{ $p->id }}]?.stock ? 'Esgotado — avisaremos quando voltar' : 'Esgotado'">{{ $fav->notify_stock ? 'Esgotado — avisaremos quando voltar' : 'Esgotado' }}</span>
                                @else
                                    Esgotado
                                @endif
                            </span>
                        @elseif($av['key'] === 'low')
                            <span class="wl-av low" data-testid="fav-availability">Últimas unidades!</span>
                        @elseif($av['key'] === 'in')
                            <span class="wl-av in" data-testid="fav-availability"><i class="fas fa-box"></i> Em estoque</span>
                        @else
                            <span class="wl-av off" data-testid="fav-availability">{{ $av['label'] }}</span>
                        @endif

                        @if($buyable)
                            <div class="wl-actions">
                                <button type="button" class="wl-btn wl-btn-cart" :class="{ in: $store.cart.has({{ $p->id }}) }"
                                        @click="addToCart(@js($cartItem))">
                                    <i class="fas" :class="$store.cart.has({{ $p->id }}) ? 'fa-check' : 'fa-cart-plus'"></i>
                                    <span x-text="$store.cart.has({{ $p->id }}) ? 'No carrinho' : 'Adicionar ao carrinho'">Adicionar ao carrinho</span>
                                </button>
                            </div>
                        @endif

                    </div>
                    @if($logged && $fav)
                        <div class="wl-bells" x-init="notify[{{ $p->id }}] = { promo: @js($fav->notify_promo), stock: @js($fav->notify_stock) }">
                            <span class="wl-bells-l"><i class="fas fa-bell"></i> Avise-me:</span>
                            <button type="button" class="wl-bell" :class="{ on: notify[{{ $p->id }}]?.promo }" @click="flip({{ $p->id }}, 'promo')"
                                    :aria-pressed="!!notify[{{ $p->id }}]?.promo" data-testid="bell-promo">
                                <i class="fas" :class="notify[{{ $p->id }}]?.promo ? 'fa-bell' : 'fa-bell-slash'"></i> Promoção
                            </button>
                            <button type="button" class="wl-bell" :class="{ on: notify[{{ $p->id }}]?.stock }" @click="flip({{ $p->id }}, 'stock')"
                                    :aria-pressed="!!notify[{{ $p->id }}]?.stock" data-testid="bell-stock">
                                <i class="fas" :class="notify[{{ $p->id }}]?.stock ? 'fa-bell' : 'fa-bell-slash'"></i> {{ $av['key'] === 'out' ? 'Quando voltar' : 'Volta ao estoque' }}
                            </button>
                        </div>
                    @endif
                    <button type="button" class="wl-heart" @click="remove({{ $p->id }})" aria-label="Tirar dos favoritos" title="Tirar dos favoritos">
                        <i class="fas fa-heart"></i>
                    </button>
                </article>
            @endforeach
        </div>
        <div class="wl-empty" x-show="!$store.favs.count" x-cloak style="margin-top:4px">
            <i class="fas fa-heart"></i>
            <h2>Sua lista ficou vazia</h2>
            <p>Toque no <i class="far fa-heart"></i> dos produtos do catálogo para salvar aqui.</p>
            <a href="{{ $catalogUrl }}" class="po-btn po-btn-primary">Ver produtos</a>
        </div>
    @endif

    <div class="wl-toast" x-show="toast" x-cloak x-transition.opacity x-text="toast"></div>
</div>

@push('scripts')
<script>
function wlPage(notifyUrl) {
    return {
        notify: {},
        toast: '',
        timer: null,
        say(text) {
            this.toast = text;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.toast = '', 2200);
        },
        addToCart(item) {
            if (Alpine.store('cart').has(item.id)) { this.say('Já está no carrinho'); return; }
            Alpine.store('cart').add(item, 1);
            this.say('Adicionado ao carrinho');
        },
        remove(id) {
            Alpine.store('favs').toggle(id);
            this.say('Tirado dos favoritos');
        },
        flip(id, key) {
            const cur = this.notify[id] || {};
            const next = !cur[key];
            this.notify[id] = { ...cur, [key]: next };
            fetch(notifyUrl.replace('__ID__', id), {
                method: 'PATCH', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ ['notify_' + key]: next }),
            }).then(r => r.ok ? r.json() : Promise.reject(r))
              .then(d => { this.notify[id] = { promo: d.notify_promo, stock: d.notify_stock }; this.say(next ? 'Combinado! A loja vai te avisar.' : 'Aviso desligado'); })
              .catch(() => { this.notify[id] = cur; this.say('Não deu certo. Tente de novo.'); });
        },
    };
}
</script>
@endpush

</x-portal-catalog-layout>
