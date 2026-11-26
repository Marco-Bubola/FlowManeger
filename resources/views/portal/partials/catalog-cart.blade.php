{{-- Carrinho lateral, barra fixa e aviso. Precisa de $cartUrl e $owner, dentro do x-data do mlCatalog. --}}
    {{-- Carrinho (abre sem login; só finalizar pede conta) --}}
    <div class="ml-sheet-bg" x-show="cartOpen" x-cloak x-transition.opacity @click="closeCart()"></div>
    <aside class="ml-cart" x-show="cartOpen" x-cloak role="dialog" aria-modal="true" aria-label="Carrinho"
           x-transition:enter="ml-cart-in" x-transition:leave="ml-cart-out">
        <div class="ml-cart-head">
            <h2>Meu carrinho <small x-show="$store.cart.count" x-text="'(' + $store.cart.count + ')'"></small></h2>
            <button type="button" class="ml-x" @click="closeCart()" aria-label="Fechar"><i class="fas fa-xmark"></i></button>
        </div>

        <div class="ml-cart-empty" x-show="!$store.cart.count">
            <i class="fas fa-cart-shopping"></i>
            <h3>Seu carrinho está vazio</h3>
            <p>Toque em <i class="fas fa-cart-plus"></i> num produto para adicionar.</p>
            <button type="button" class="ml-btn ml-btn-primary ml-btn-sm" @click="closeCart()">Ver produtos</button>
        </div>

        <ul class="ml-cart-list" x-show="$store.cart.count">
            <template x-for="item in $store.cart.items" :key="item.key || item.id">
                <li class="ml-cart-item">
                    <div class="ml-cart-img">
                        <template x-if="item.img"><img :src="item.img" alt="" x-on:error="item.img = null"></template>
                        <template x-if="!item.img"><div class="ml-ph"><i class="fas fa-image"></i></div></template>
                    </div>
                    <div class="ml-cart-info">
                        <p x-text="item.name"></p>
                        <template x-if="item.opts && item.opts.length">
                            <ul class="ml-cart-opts" data-testid="cart-opts">
                                <template x-for="o in item.opts" :key="o.c"><li><span x-text="o.l + ':'"></span> <b x-text="o.v"></b><em x-show="o.x > 0" x-text="' +' + money(o.x)"></em></li></template>
                            </ul>
                        </template>
                        <small class="ml-cart-mix" x-show="$store.cart.mixed(item.id)" x-cloak data-testid="cart-mixed"><i class="fas fa-triangle-exclamation"></i> Cores diferentes do mesmo produto</small>
                        <strong x-text="money(item.price * item.qty)"></strong>
                        <small x-show="item.qty > 1" x-text="item.qty + ' × ' + money(item.price)"></small>
                        <div class="ml-cart-row">
                            <div class="ml-step">
                                <button type="button" @click="$store.cart.setQty(item.key, item.qty - 1)" :disabled="item.qty <= 1" aria-label="Menos"><i class="fas fa-minus"></i></button>
                                <span x-text="item.qty"></span>
                                <button type="button" @click="$store.cart.setQty(item.key, item.qty + 1)" :disabled="item.stock && item.qty >= item.stock" aria-label="Mais"><i class="fas fa-plus"></i></button>
                            </div>
                            <button type="button" class="ml-cart-del" @click="$store.cart.remove(item.key)">
                                <i class="fas fa-trash-can"></i> Remover
                            </button>
                        </div>
                    </div>
                </li>
            </template>
        </ul>

        <div class="ml-cart-foot" x-show="$store.cart.count">
            <div class="ml-cart-total">
                <span>Total</span>
                <strong x-text="money($store.cart.total)"></strong>
            </div>
            <a href="{{ $cartUrl }}" class="ml-btn ml-btn-primary">
                {{ Auth::guard('portal')->check() ? 'Finalizar pedido' : 'Entrar para finalizar' }}
            </a>
            @guest('portal')
                <p class="ml-cart-note">Ainda não tem conta? <a href="{{ route('portal.register', ['redirect' => 'cart', 'loja' => $owner]) }}">Criar conta</a>. Seu carrinho continua salvo.</p>
            @endguest
            <button type="button" class="ml-cart-clear" @click="if (confirm('Tirar todos os produtos do carrinho?')) $store.cart.clear()">Esvaziar carrinho</button>
        </div>
    </aside>

    {{-- Carrinho fixo --}}
    <div class="ml-cartbar" x-show="$store.cart.count > 0 && !cartOpen" x-cloak x-transition.opacity>
        <div class="ml-cartbar-in">
            <div class="ml-cartbar-sum">
                <small x-text="$store.cart.count + ($store.cart.count === 1 ? ' produto no carrinho' : ' produtos no carrinho')"></small>
                <strong x-text="money($store.cart.total)"></strong>
            </div>
            <button type="button" class="ml-btn ml-btn-primary" @click="openCart()">
                <i class="fas fa-cart-shopping"></i> Ver carrinho
            </button>
        </div>
    </div>

    <div class="ml-toast" x-show="toast" x-cloak x-transition.opacity>
        <i class="fas fa-circle-check"></i> <span x-text="toast"></span>
    </div>
