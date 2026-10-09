@props(['title' => 'Catálogo de Produtos', 'store' => null, 'ownerId' => null, 'search' => '', 'cartHref' => null])
@php
    $storeName = $store?->name ?: config('app.name');
    // Cores da logo do app (rosa, roxo e azul), escolhidas pelo dono.
    $theme = ['header' => 'linear-gradient(90deg, #ec4899 0%, #a855f7 50%, #3b82f6 100%)', 'primary' => '#8b5cf6', 'dark' => '#7c3aed', 'soft' => '#ede9fe', 'hero' => 'linear-gradient(135deg, #db2777 0%, #8b5cf6 55%, #2563eb 100%)', 'meta' => '#a855f7'];
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);
    // Novidades nos pedidos (confirmado/recusado/proposta) para o menu da conta.
    $orderAlerts = Auth::guard('portal')->check()
        ? \App\Models\ClientQuoteRequest::where('client_id', Auth::guard('portal')->id())
            ->where(fn ($q) => $q->where('status', 'quoted')->orWhere(fn ($q) => $q->unseenByClient()))
            ->count()
        : 0;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $theme['meta'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ $storeName }}</title>
    <meta property="og:title" content="{{ $storeName }} · Catálogo">
    <meta property="og:description" content="Veja os produtos e ofertas de {{ $storeName }}.">
    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ml-header: {!! $theme['header'] !!};
            --ml-hero: {!! $theme['hero'] !!};
            --ml-bg: #f0f0f3;
            --ml-card: #ffffff;
            --ml-text: #333333;
            --ml-muted: #737373;
            --ml-line: #e6e6e6;
            --ml-blue: {{ $theme['primary'] }};
            --ml-blue-dark: {{ $theme['dark'] }};
            --ml-blue-soft: {{ $theme['soft'] }};
            --ml-green: #00a650;
            --ml-orange: #ff7733;
            --ml-red: #f23d4f;
            --ml-radius: 8px;
            --ml-shadow: 0 1px 2px rgba(0,0,0,.12);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body { margin: 0; background: var(--ml-bg); color: var(--ml-text); font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 14px; line-height: 1.35; }
        a { color: inherit; text-decoration: none; }
        button, input, select { font: inherit; color: inherit; }
        img { display: block; max-width: 100%; }
        [x-cloak] { display: none !important; }
        .ml-wrap { max-width: 1200px; margin: 0 auto; padding: 0 12px; }

        /* ── Cabeçalho nas cores do app ── */
        .ml-header { position: sticky; top: 0; z-index: 60; background: var(--ml-header); box-shadow: 0 2px 10px rgba(0,0,0,.15); padding-top: env(safe-area-inset-top); }
        .ml-header-row { display: flex; align-items: center; gap: 10px; height: 60px; }
        .ml-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,.18); }
        .ml-logo img { width: 28px; height: 28px; }
        .ml-search { flex: 1; min-width: 0; position: relative; max-width: 680px; margin: 0 auto; }
        .ml-search input { width: 100%; height: 40px; border: 0; border-radius: 999px; padding: 0 44px 0 16px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.2); font-size: 14px; outline: none; }
        .ml-search input::placeholder { color: #999; }
        .ml-search button { position: absolute; right: 4px; top: 4px; width: 32px; height: 32px; border: 0; border-radius: 50%; background: transparent; color: #666; cursor: pointer; }
        .ml-head-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .ml-login { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px; border-radius: 999px; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); color: #fff; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .ml-login:active { background: rgba(255,255,255,.3); }
        .ml-icon-btn { position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 19px; flex-shrink: 0; border-radius: 50%; border: 0; background: transparent; cursor: pointer; }
        .ml-icon-btn:active { background: rgba(255,255,255,.18); }
        .ml-count { position: absolute; top: 1px; right: -1px; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px; background: #fff; color: var(--ml-blue-dark); font-size: 11px; font-weight: 900; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(0,0,0,.25); }
        @media (max-width: 380px) { .ml-login span { display: none; } .ml-login { width: 36px; padding: 0; justify-content: center; } }

        /* Menu da conta (cliente logado) */
        .ml-acc { position: relative; }
        .ml-acc .ml-login { cursor: pointer; position: relative; }
        .ml-acc-dot { position: absolute; top: -2px; right: -2px; width: 11px; height: 11px; border-radius: 50%; background: #facc15; border: 2px solid #fff; }
        .ml-acc-menu { position: absolute; right: 0; top: calc(100% + 8px); width: 220px; background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.18); padding: 6px; z-index: 80; color: var(--ml-text); }
        .ml-acc-menu::before { content: ''; position: absolute; top: -6px; right: 22px; width: 12px; height: 12px; background: #fff; transform: rotate(45deg); }
        .ml-acc-name { margin: 4px 10px 6px; font-size: 12px; color: var(--ml-muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ml-acc-menu a, .ml-acc-menu button { position: relative; display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px; border: 0; background: none; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: left; cursor: pointer; }
        .ml-acc-menu a:hover, .ml-acc-menu button:hover { background: var(--ml-blue-soft); color: var(--ml-blue-dark); }
        .ml-acc-menu i { width: 18px; text-align: center; color: var(--ml-blue); }
        .ml-acc-menu form { margin: 4px 0 0; padding-top: 4px; border-top: 1px solid var(--ml-line); }
        .ml-acc-badge { margin-left: auto; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: var(--ml-blue); color: #fff; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }

        main { padding-bottom: 96px; }
        .ml-foot { text-align: center; color: #999; font-size: 12px; padding: 24px 12px 110px; }
    </style>
    @stack('styles')
</head>
<body>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        load() {
            try { this.items = JSON.parse(localStorage.getItem('portal_cart') || '[]'); } catch (e) { this.items = []; }
        },
        sync(stock) {
            const before = this.items.length;
            this.items = this.items
                .filter(i => stock[i.id] > 0)
                .map(i => ({ ...i, stock: stock[i.id], qty: Math.min(i.qty || 1, stock[i.id]) }));
            this.save();
            return before - this.items.length;
        },
        save() {
            try { localStorage.setItem('portal_cart', JSON.stringify(this.items)); } catch (e) {}
        },
        get count() { return this.items.length; },
        get total() { return this.items.reduce((s, i) => s + (parseFloat(i.price) || 0) * (i.qty || 1), 0); },
        has(id) { return this.items.some(i => i.id === id); },
        qty(id) { const i = this.items.find(x => x.id === id); return i ? i.qty : 0; },
        add(p, qty = 1) {
            const max = p.stock > 0 ? p.stock : 999;
            const found = this.items.find(i => i.id === p.id);
            if (found) {
                found.qty = Math.min(max, (found.qty || 1) + qty);
            } else {
                this.items.push({ id: p.id, name: p.name, price: p.price, stock: p.stock, img: p.img || null, qty: Math.min(max, qty) });
            }
            this.save();
        },
        setQty(id, qty) {
            const item = this.items.find(i => i.id === id);
            if (!item) return;
            const max = item.stock > 0 ? item.stock : 999;
            item.qty = Math.min(max, Math.max(1, qty));
            this.save();
        },
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
            this.save();
        },
        clear() {
            this.items = [];
            this.save();
        },
        init() { this.load(); },
    });
});
</script>

<header class="ml-header">
    <div class="ml-wrap">
        <div class="ml-header-row">
            <a href="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : '#' }}" class="ml-logo" aria-label="Início do catálogo">
                <img src="{{ Vite::asset('resources/images/icons/logo-64.png') }}" alt="">
            </a>
            <form method="GET" action="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : route('portal.catalog') }}" class="ml-search" role="search">
                <input type="search" name="search" value="{{ $search }}" placeholder="Buscar" aria-label="Buscar produtos" enterkeyhint="search">
                <button type="submit" aria-label="Buscar"><i class="fas fa-magnifying-glass"></i></button>
            </form>
            <div class="ml-head-actions">
                @if(Auth::guard('portal')->check())
                    <div class="ml-acc" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" class="ml-login" @click="open = !open" :aria-expanded="open" aria-haspopup="menu">
                            <i class="fas fa-circle-user"></i><span>Minha conta</span>
                            @if($orderAlerts > 0)<span class="ml-acc-dot" aria-label="Novidades nos pedidos"></span>@endif
                        </button>
                        <div class="ml-acc-menu" x-show="open" x-cloak x-transition.opacity role="menu">
                            <p class="ml-acc-name">{{ Str::limit(Auth::guard('portal')->user()->name, 28) }}</p>
                            <a href="{{ route('portal.quotes') }}" role="menuitem"><i class="fas fa-box"></i> Meus pedidos
                                @if($orderAlerts > 0)<span class="ml-acc-badge">{{ $orderAlerts }}</span>@endif</a>
                            <a href="{{ route('portal.sales') }}" role="menuitem"><i class="fas fa-bag-shopping"></i> Minhas compras</a>
                            <a href="{{ route('portal.profile') }}" role="menuitem"><i class="fas fa-user-pen"></i> Meus dados</a>
                            <a href="{{ route('portal.dashboard') }}" role="menuitem"><i class="fas fa-house"></i> Painel da conta</a>
                            <form method="POST" action="{{ route('portal.logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"><i class="fas fa-right-from-bracket"></i> Sair</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('portal.login') }}" class="ml-login"><i class="fas fa-circle-user"></i><span>Entrar</span></a>
                @endif
                @if($cartHref)
                <a href="{{ $cartHref }}" class="ml-icon-btn" x-data aria-label="Abrir carrinho">
                    <i class="fas fa-cart-shopping"></i>
                    <span class="ml-count" x-show="$store.cart.count > 0" x-text="$store.cart.count" x-cloak></span>
                </a>
                @else
                <button type="button" class="ml-icon-btn" x-data @click="$dispatch('open-cart')" aria-label="Abrir carrinho">
                    <i class="fas fa-cart-shopping"></i>
                    <span class="ml-count" x-show="$store.cart.count > 0" x-text="$store.cart.count" x-cloak></span>
                </button>
                @endif
            </div>
        </div>
    </div>
</header>

<main>
    @include('portal.partials.order-updates', ['variant' => 'ml'])
    {{ $slot }}
</main>

<footer class="ml-foot">
    {{ $storeName }} · Catálogo online · {{ date('Y') }}
</footer>

<script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
