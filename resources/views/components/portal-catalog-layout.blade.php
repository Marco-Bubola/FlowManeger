@props(['title' => 'Catálogo de Produtos', 'store' => null, 'ownerId' => null, 'search' => ''])
@php
    $storeName = $store?->name ?: config('app.name');
    // Cores da logo do app (rosa, roxo e azul), escolhidas pelo dono.
    $theme = ['header' => 'linear-gradient(90deg, #ec4899 0%, #a855f7 50%, #3b82f6 100%)', 'primary' => '#8b5cf6', 'dark' => '#7c3aed', 'soft' => '#ede9fe', 'hero' => 'linear-gradient(135deg, #db2777 0%, #8b5cf6 55%, #2563eb 100%)', 'meta' => '#a855f7'];
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);
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
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" href="/favicon.ico" sizes="any">
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
                <img src="/logo-64.png" alt="">
            </a>
            <form method="GET" action="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : route('portal.catalog') }}" class="ml-search" role="search">
                <input type="search" name="search" value="{{ $search }}" placeholder="Buscar" aria-label="Buscar produtos" enterkeyhint="search">
                <button type="submit" aria-label="Buscar"><i class="fas fa-magnifying-glass"></i></button>
            </form>
            <div class="ml-head-actions">
                @if(Auth::guard('portal')->check())
                    <a href="{{ route('portal.dashboard') }}" class="ml-login"><i class="fas fa-circle-user"></i><span>Minha conta</span></a>
                @else
                    <a href="{{ route('portal.login') }}" class="ml-login"><i class="fas fa-circle-user"></i><span>Entrar</span></a>
                @endif
                <button type="button" class="ml-icon-btn" x-data @click="$dispatch('open-cart')" aria-label="Abrir carrinho">
                    <i class="fas fa-cart-shopping"></i>
                    <span class="ml-count" x-show="$store.cart.count > 0" x-text="$store.cart.count" x-cloak></span>
                </button>
            </div>
        </div>
    </div>
</header>

<main>
    {{ $slot }}
</main>

<footer class="ml-foot">
    {{ $storeName }} · Catálogo online · {{ date('Y') }}
</footer>

<script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
