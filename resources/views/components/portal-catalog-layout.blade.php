@props(['title' => 'Catálogo de Produtos', 'store' => null, 'ownerId' => null, 'search' => '', 'cartHref' => null, 'og' => null])
@php
    // Vitrine › Identidade da loja: logo, nome, fonte dos títulos, contatos e páginas.
    $identity = \App\Services\Portal\StoreIdentity::for($ownerId ? (int) $ownerId : ($store?->id ? (int) $store->id : null));
    $idOwner = $ownerId ?: $store?->id;
    $storeName = $identity['name'] ?: ($store?->name ?: config('app.name'));
    // Cores da logo do app (rosa, roxo e azul), escolhidas pelo dono.
    $theme = ['header' => 'linear-gradient(90deg, #ec4899 0%, #a855f7 50%, #3b82f6 100%)', 'primary' => '#8b5cf6', 'dark' => '#7c3aed', 'soft' => '#ede9fe', 'hero' => 'linear-gradient(135deg, #db2777 0%, #8b5cf6 55%, #2563eb 100%)', 'meta' => '#a855f7'];
    // Vitrine › Página inicial: cores do tema (evento ou escolhidas) e os cantos.
    $theme = array_merge($theme, \App\Services\Portal\HomeLayout::cssTheme($ownerId ? (int) $ownerId : null) ?? []);
    $cartUrl = Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']);
    // Novidades nos pedidos (confirmado/recusado/proposta) para o menu da conta.
    $orderAlerts = Auth::guard('portal')->check()
        ? \App\Models\ClientQuoteRequest::where('client_id', Auth::guard('portal')->id())
            ->where(fn ($q) => $q->where('status', 'quoted')->orWhere(fn ($q) => $q->unseenByClient()))
            ->count()
        : 0;
    // Favoritos: na conta quando logado; sem login, no aparelho (localStorage).
    $portalClient = Auth::guard('portal')->user();
    $wishReady = \App\Services\Portal\WishlistService::ready();
    $favIds = $portalClient && $wishReady ? app(\App\Services\Portal\WishlistService::class)->favoriteIds($portalClient) : [];
    $favAlerts = $portalClient && $wishReady ? app(\App\Services\Portal\WishlistService::class)->unseenAlertsForRequest($portalClient)->count() : 0;
    $favoritesUrl = route('portal.favorites', $portalClient ? [] : array_filter(['loja' => $ownerId]));
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $theme['meta'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ $storeName }}</title>
    {{-- Prévia do link no WhatsApp: com ?produto= mostra a foto, o nome e o preço do produto. --}}
    <meta property="og:type" content="{{ $og ? 'product' : 'website' }}">
    <meta property="og:site_name" content="{{ $storeName }}">
    <meta property="og:title" content="{{ $og['title'] ?? $storeName . ' · Catálogo' }}">
    <meta property="og:description" content="{{ $og['description'] ?? 'Veja os produtos e ofertas de ' . $storeName . '.' }}">
    @if (!empty($og['image']))
        <meta property="og:image" content="{{ $og['image'] }}">
        <meta property="og:image:alt" content="{{ $og['title'] }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    @if (!empty($og['url']))
        <meta property="og:url" content="{{ $og['url'] }}">
    @endif
    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @if($identity['font_url'])<link href="{{ $identity['font_url'] }}" rel="stylesheet">@endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ml-header: {!! $theme['header'] !!};
            --ml-hero: {!! $theme['hero'] !!};
            /* Cores das áreas: vêm do tema (Vitrine › Página inicial) */
            --ml-bg: {{ $theme['bg'] ?? '#f0f0f3' }};
            --ml-card: {{ $theme['card'] ?? '#ffffff' }};
            --ml-text: {{ $theme['text'] ?? '#333333' }};
            --ml-muted: {{ $theme['muted'] ?? '#737373' }};
            --ml-line: {{ $theme['line'] ?? '#e6e6e6' }};
            --ml-page-text: {{ $theme['page_text'] ?? '#555555' }};
            --ml-chip: {{ $theme['chip'] ?? '#ffffff' }};
            --ml-blue: {{ $theme['primary'] }};
            --ml-blue-dark: {{ $theme['dark'] }};
            --ml-blue-soft: {{ $theme['soft'] }};
            --ml-green: #00a650;
            --ml-orange: #ff7733;
            --ml-red: #f23d4f;
            --ml-radius: {{ $theme['radius'] ?? '8px' }};
            --sf-font: {!! $identity['font_css'] !!};
            --ml-shadow: 0 1px 2px rgba(0,0,0,.12);
            --ml-a: {{ $theme['a'] ?? '#ec4899' }};
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
        .ml-logo.own { width: auto; min-width: 40px; max-width: 120px; padding: 3px 6px; }
        .ml-logo.own img { width: auto; height: 34px; max-width: 108px; object-fit: contain; }
        @media (max-width: 767px) { .ml-logo.own { max-width: 72px; padding: 3px 4px; } .ml-logo.own img { max-width: 64px; height: 32px; } }
        .ml-brand { display: none; color: #fff; text-decoration: none; min-width: 0; max-width: 220px; line-height: 1.1; }
        .ml-brand b { display: block; font-size: 17px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--sf-font); }
        .ml-brand small { display: block; font-size: 11px; opacity: .85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        @media (min-width: 768px) { .ml-brand { display: block; } }
        /* Fonte dos títulos escolhida na Identidade da loja */
        h1, h2, h3, .sf-font { font-family: var(--sf-font); }
        .ml-foot2 { background: var(--ml-card); border-top: 1px solid var(--ml-line); margin-top: 24px; padding: 28px 0 110px; color: var(--ml-muted); font-size: 13px; }
        .ml-foot2-grid { display: grid; gap: 22px; grid-template-columns: 1fr; }
        @media (min-width: 640px) { .ml-foot2-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .ml-foot2-grid { grid-template-columns: 1.3fr 1fr 1fr 1fr; } }
        .ml-foot2 h4 { margin: 0 0 10px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--ml-text); font-family: var(--sf-font); }
        .ml-foot2 a { color: var(--ml-text); text-decoration: none; }
        .ml-foot2 a:hover { color: var(--ml-primary); }
        .ml-foot2 ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .ml-foot2 li { display: flex; gap: 8px; align-items: flex-start; overflow-wrap: anywhere; }
        .ml-foot2 li i { width: 16px; margin-top: 2px; color: var(--ml-primary); text-align: center; flex-shrink: 0; }
        .ml-foot2-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .ml-foot2-brand img { height: 40px; max-width: 120px; object-fit: contain; }
        .ml-foot2-brand b { font-size: 18px; color: var(--ml-text); font-family: var(--sf-font); }
        .ml-foot2-soc { display: flex; gap: 8px; margin-top: 12px; }
        .ml-foot2-soc a { width: 38px; height: 38px; border-radius: 50%; background: var(--ml-soft); color: var(--ml-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
        .ml-foot2-pay { display: flex; flex-wrap: wrap; gap: 6px; }
        .ml-foot2-pay span { border: 1px solid var(--ml-line); border-radius: 6px; padding: 4px 9px; font-size: 12px; font-weight: 600; color: var(--ml-text); background: var(--ml-chip); }
        .ml-foot2-copy { margin-top: 22px; padding-top: 14px; border-top: 1px solid var(--ml-line); text-align: center; color: var(--ml-muted); font-size: 12px; }
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
        .ml-acc-badge.pink { background: #ec4899; }
        .ml-fav-head, .ml-kit-head { display: none; }
        @media (min-width: 640px) { .ml-fav-head, .ml-kit-head { display: flex; } }
        .ml-acc-badge { margin-left: auto; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: var(--ml-blue); color: #fff; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }

        /* ── Estilo do site (Vitrine › Página inicial › Estilo do site): classes lk-{parte}-{escolha} no body ── */
        .lk-header-solido { --ml-header: var(--ml-a); }
        .lk-header-escuro { --ml-header: linear-gradient(90deg, #0b0b0f, #1c1c22); }
        .lk-header-vidro .ml-header { background: color-mix(in srgb, var(--ml-a) 72%, transparent); -webkit-backdrop-filter: blur(14px) saturate(1.5); backdrop-filter: blur(14px) saturate(1.5); }
        .lk-header-claro .ml-header { background: var(--ml-card); box-shadow: 0 1px 0 var(--ml-line), 0 6px 18px rgba(0,0,0,.06); }
        .lk-header-claro .ml-brand, .lk-header-claro .ml-icon-btn { color: var(--ml-text); }
        .lk-header-claro .ml-login { background: var(--ml-blue-soft); border-color: transparent; color: var(--ml-blue); }
        .lk-header-claro .ml-search input { background: var(--ml-bg); box-shadow: inset 0 0 0 1px var(--ml-line); color: var(--ml-text); }
        .lk-header-claro .ml-logo { box-shadow: 0 0 0 1px var(--ml-line); }
        .lk-cards-elevado { --ml-shadow: 0 12px 28px -14px rgba(0,0,0,.35), 0 2px 6px rgba(0,0,0,.06); }
        .lk-cards-borda { --ml-shadow: 0 0 0 1px var(--ml-line); }
        .lk-cards-contorno { --ml-shadow: 0 0 0 2px color-mix(in srgb, var(--ml-blue) 45%, transparent); }
        .lk-cards-plano { --ml-shadow: none; }
        @media (hover: hover) { .lk-cards-elevado .ml-card:hover { transform: translateY(-3px); box-shadow: 0 18px 34px -14px rgba(0,0,0,.4); } }
        .lk-buttons-pilula .ml-btn, .lk-buttons-pilula .kb-btn, .lk-buttons-pilula .ml-qty { border-radius: 999px; }
        .lk-buttons-reto .ml-btn, .lk-buttons-reto .kb-btn, .lk-buttons-reto .ml-search input, .lk-buttons-reto .ml-login, .lk-buttons-reto .ml-chip { border-radius: 3px; }
        .lk-density-compacto .ml-grid { gap: 6px; }
        @media (min-width: 960px) { .lk-density-compacto .ml-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; } }
        @media (min-width: 1180px) { .lk-density-compacto .ml-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
        .lk-density-espacoso .ml-grid { gap: 14px; }
        @media (min-width: 960px) { .lk-density-espacoso .ml-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; } }
        @media (min-width: 1180px) { .lk-density-espacoso .ml-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .lk-photo-retrato .ml-card-img { aspect-ratio: 4 / 5; }
        .lk-photo-cheia .ml-card-img img, .lk-photo-retrato .ml-card-img img { object-fit: cover; padding: 0; }
        .lk-text-titulos { font-family: var(--sf-font); }
        .lk-anim-nao *, .lk-anim-nao *::before, .lk-anim-nao *::after { animation: none !important; transition: none !important; }
        /* Desenho no fundo: uma camada fixa atrás de tudo, na cor dos botões, bem clarinha */
        body[class*="lk-pattern-"]:not(.lk-pattern-nenhum)::before { content: ''; position: fixed; inset: 0; z-index: -1; pointer-events: none; background: var(--ml-blue); opacity: .09; -webkit-mask-repeat: repeat; mask-repeat: repeat; }
        body.ml-dark[class*="lk-pattern-"]::before { opacity: .14; }
        .lk-pattern-pontos::before { -webkit-mask-image: radial-gradient(circle, #000 2px, transparent 2.5px); mask-image: radial-gradient(circle, #000 2px, transparent 2.5px); -webkit-mask-size: 22px 22px; mask-size: 22px 22px; }
        .lk-pattern-listras::before { -webkit-mask-image: repeating-linear-gradient(135deg, #000 0 10px, transparent 10px 28px); mask-image: repeating-linear-gradient(135deg, #000 0 10px, transparent 10px 28px); opacity: .06 !important; }
        .lk-pattern-coracoes::before { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='90' height='90'%3E%3Cpath d='M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z'/%3E%3Cpath transform='translate(48 46) scale(.6)' d='M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z'/%3E%3C/svg%3E"); mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='90' height='90'%3E%3Cpath d='M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z'/%3E%3Cpath transform='translate(48 46) scale(.6)' d='M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z'/%3E%3C/svg%3E"); -webkit-mask-size: 90px 90px; mask-size: 90px 90px; }
        .lk-pattern-estrelas::before { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='110' height='110'%3E%3Cpath d='M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z'/%3E%3Cpath transform='translate(58 56) scale(.55)' d='M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z'/%3E%3Ccircle cx='85' cy='20' r='2.5'/%3E%3Ccircle cx='18' cy='88' r='2'/%3E%3C/svg%3E"); mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='110' height='110'%3E%3Cpath d='M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z'/%3E%3Cpath transform='translate(58 56) scale(.55)' d='M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z'/%3E%3Ccircle cx='85' cy='20' r='2.5'/%3E%3Ccircle cx='18' cy='88' r='2'/%3E%3C/svg%3E"); -webkit-mask-size: 110px 110px; mask-size: 110px 110px; }
        .lk-pattern-confete::before { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Crect x='10' y='14' width='12' height='5' rx='2' transform='rotate(30 16 16)'/%3E%3Crect x='70' y='8' width='12' height='5' rx='2' transform='rotate(-25 76 10)'/%3E%3Ccircle cx='45' cy='50' r='4'/%3E%3Crect x='92' y='60' width='12' height='5' rx='2' transform='rotate(60 98 62)'/%3E%3Ccircle cx='20' cy='92' r='3'/%3E%3Crect x='55' y='96' width='12' height='5' rx='2' transform='rotate(-50 61 98)'/%3E%3Ccircle cx='104' cy='106' r='3'/%3E%3C/svg%3E"); mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Crect x='10' y='14' width='12' height='5' rx='2' transform='rotate(30 16 16)'/%3E%3Crect x='70' y='8' width='12' height='5' rx='2' transform='rotate(-25 76 10)'/%3E%3Ccircle cx='45' cy='50' r='4'/%3E%3Crect x='92' y='60' width='12' height='5' rx='2' transform='rotate(60 98 62)'/%3E%3Ccircle cx='20' cy='92' r='3'/%3E%3Crect x='55' y='96' width='12' height='5' rx='2' transform='rotate(-50 61 98)'/%3E%3Ccircle cx='104' cy='106' r='3'/%3E%3C/svg%3E"); -webkit-mask-size: 120px 120px; mask-size: 120px 120px; opacity: .14 !important; }
        .lk-pattern-neve::before { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' stroke='%23000' stroke-width='2' stroke-linecap='round'%3E%3Cg transform='translate(28 30)'%3E%3Cpath d='M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6'/%3E%3C/g%3E%3Cg transform='translate(86 84) scale(.65)'%3E%3Cpath d='M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6'/%3E%3C/g%3E%3Ccircle cx='90' cy='22' r='2.5' stroke='none'/%3E%3Ccircle cx='30' cy='96' r='3' stroke='none'/%3E%3Ccircle cx='60' cy='60' r='1.8' stroke='none'/%3E%3C/svg%3E"); mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' stroke='%23000' stroke-width='2' stroke-linecap='round'%3E%3Cg transform='translate(28 30)'%3E%3Cpath d='M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6'/%3E%3C/g%3E%3Cg transform='translate(86 84) scale(.65)'%3E%3Cpath d='M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6'/%3E%3C/g%3E%3Ccircle cx='90' cy='22' r='2.5' stroke='none'/%3E%3Ccircle cx='30' cy='96' r='3' stroke='none'/%3E%3Ccircle cx='60' cy='60' r='1.8' stroke='none'/%3E%3C/svg%3E"); -webkit-mask-size: 120px 120px; mask-size: 120px 120px; opacity: .16 !important; }
        body.ml-dark.lk-pattern-neve::before { background: #fff; }
        .lk-pattern-ondas::before { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='32' fill='none' stroke='%23000' stroke-width='2.5'%3E%3Cpath d='M0 16c10 0 10-10 20-10s10 10 20 10 10-10 20-10 10 10 20 10'/%3E%3C/svg%3E"); mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='32' fill='none' stroke='%23000' stroke-width='2.5'%3E%3Cpath d='M0 16c10 0 10-10 20-10s10 10 20 10 10-10 20-10 10 10 20 10'/%3E%3C/svg%3E"); -webkit-mask-size: 80px 32px; mask-size: 80px 32px; }
        /* Com animação: neve e confete caem devagar, corações sobem */
        @keyframes lk-fall { to { -webkit-mask-position: 0 240px; mask-position: 0 240px; } }
        @keyframes lk-rise { to { -webkit-mask-position: 0 -180px; mask-position: 0 -180px; } }
        @media (prefers-reduced-motion: no-preference) {
            .lk-anim-sim.lk-pattern-neve::before, .lk-anim-sim.lk-pattern-confete::before { animation: lk-fall 30s linear infinite; }
            .lk-anim-sim.lk-pattern-coracoes::before { animation: lk-rise 36s linear infinite; }
        }

        main { padding-bottom: 96px; }
        .ml-foot { text-align: center; color: #999; font-size: 12px; padding: 24px 12px 110px; }
    </style>
    @stack('styles')
</head>
<body class="{{ ! empty($theme['dark_mode']) ? 'ml-dark' : '' }} {{ \App\Services\Portal\HomeLayout::lookClasses($theme['look'] ?? null) }}">
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

    // Favoritos (coração). Sem login: só no aparelho; com login: na conta, e o
    // que estava no aparelho entra na conta na primeira página depois do login.
    Alpine.store('favs', {
        ids: @js($favIds),
        logged: @js((bool) $portalClient),
        toggleUrl: @js(route('portal.favorites.toggle')),
        syncUrl: @js(route('portal.favorites.sync')),
        local() {
            try { return (JSON.parse(localStorage.getItem('portal_favs') || '[]') || []).map(Number).filter(Boolean); } catch (e) { return []; }
        },
        saveLocal(ids) {
            try { localStorage.setItem('portal_favs', JSON.stringify(ids)); } catch (e) {}
        },
        post(url, body) {
            return fetch(url, { method: 'POST', credentials: 'same-origin', headers: {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            }, body: JSON.stringify(body) }).then(r => r.ok ? r.json() : Promise.reject(r));
        },
        init() {
            if (!this.logged) { this.ids = this.local(); return; }
            const pending = this.local().filter(id => !this.ids.includes(id));
            if (pending.length) {
                this.ids = [...this.ids, ...pending];
                this.post(this.syncUrl, { ids: pending })
                    .then(d => { this.ids = d.ids; this.saveLocal([]); })
                    .catch(() => {});
            } else if (this.local().length) {
                this.saveLocal([]);
            }
        },
        get count() { return this.ids.length; },
        has(id) { return this.ids.includes(Number(id)); },
        toggle(id) {
            id = Number(id);
            const on = !this.has(id);
            this.ids = on ? [...this.ids, id] : this.ids.filter(x => x !== id);
            if (!this.logged) { this.saveLocal(this.ids); return on; }
            this.post(this.toggleUrl, { product_id: id, on })
                .then(d => { this.ids = d.ids; })
                .catch(() => { this.ids = on ? this.ids.filter(x => x !== id) : [...this.ids, id]; });
            return on;
        },
    });
});
</script>

<header class="ml-header">
    <div class="ml-wrap">
        <div class="ml-header-row">
            <a href="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : '#' }}" class="ml-logo {{ $identity['logo_url'] ? 'own' : '' }}" aria-label="Início do catálogo" data-testid="store-logo">
                <img src="{{ $identity['logo_url'] ?: Vite::asset('resources/images/icons/logo-64.png') }}" alt="{{ $storeName }}">
            </a>
            @if($identity['show_name'])
                <a href="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : '#' }}" class="ml-brand" data-testid="store-name">
                    <b>{{ $storeName }}</b>@if($identity['tagline'])<small>{{ $identity['tagline'] }}</small>@endif
                </a>
            @endif
            <form method="GET" action="{{ $ownerId ? route('portal.catalog', ['userId' => $ownerId]) : route('portal.catalog') }}" class="ml-search" role="search">
                <input type="search" name="search" value="{{ $search }}" placeholder="Buscar" aria-label="Buscar produtos" enterkeyhint="search">
                <button type="submit" aria-label="Buscar"><i class="fas fa-magnifying-glass"></i></button>
            </form>
            <div class="ml-head-actions">
                @if(Auth::guard('portal')->check())
                    <div class="ml-acc" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" class="ml-login" @click="open = !open" :aria-expanded="open" aria-haspopup="menu">
                            <i class="fas fa-circle-user"></i><span>Minha conta</span>
                            @if($orderAlerts + $favAlerts > 0)<span class="ml-acc-dot" aria-label="Novidades na sua conta"></span>@endif
                        </button>
                        <div class="ml-acc-menu" x-show="open" x-cloak x-transition.opacity role="menu">
                            <p class="ml-acc-name">{{ Str::limit(Auth::guard('portal')->user()->name, 28) }}</p>
                            <a href="{{ route('portal.quotes') }}" role="menuitem"><i class="fas fa-box"></i> Meus pedidos
                                @if($orderAlerts > 0)<span class="ml-acc-badge">{{ $orderAlerts }}</span>@endif</a>
                            <a href="{{ route('portal.favorites') }}" role="menuitem" data-testid="menu-favorites"><i class="fas fa-heart"></i> Meus favoritos
                                @if($favAlerts > 0)<span class="ml-acc-badge pink">{{ $favAlerts }}</span>@endif</a>
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
                @if($idOwner && \App\Services\Portal\KitService::enabled((int) $idOwner))
                    <a href="{{ route('portal.kit', ['userId' => $idOwner]) }}" class="ml-icon-btn ml-kit-head" aria-label="Monte seu kit" title="Monte seu kit" data-testid="head-kit"><i class="fas fa-gift"></i></a>
                @endif
                <a href="{{ $favoritesUrl }}" class="ml-icon-btn ml-fav-head" x-data aria-label="Meus favoritos" title="Meus favoritos">
                    <i class="fas fa-heart"></i>
                    <span class="ml-count" x-show="$store.favs.count > 0" x-text="$store.favs.count" x-cloak></span>
                </a>
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
    @include('portal.partials.wishlist-updates', ['variant' => 'ml'])
    {{ $slot }}
</main>

@php
    $fContacts = array_filter([
        $identity['wa_link'] ? ['fab fa-whatsapp', 'WhatsApp ' . $identity['whatsapp'], $identity['wa_link']] : null,
        $identity['phone'] ? ['fas fa-phone', $identity['phone'], 'tel:' . preg_replace('/[^\d+]/', '', $identity['phone'])] : null,
        $identity['email'] ? ['fas fa-envelope', $identity['email'], 'mailto:' . $identity['email']] : null,
        $identity['address'] ? ['fas fa-location-dot', $identity['address'], 'https://www.google.com/maps/search/?api=1&query=' . urlencode($identity['address'])] : null,
        $identity['hours'] ? ['fas fa-clock', $identity['hours'], null] : null,
    ]);
    $fSocial = array_filter(['fab fa-instagram' => $identity['ig_link'], 'fab fa-facebook-f' => $identity['fb_link'], 'fab fa-tiktok' => $identity['tt_link'], 'fab fa-whatsapp' => $identity['wa_link']]);
    $fPages = $idOwner ? collect(\App\Services\Portal\StoreIdentity::PAGES)->filter(fn ($v, $k) => isset($identity['pages'][$k])) : collect();
    $fPay = array_intersect_key(\App\Services\Portal\StoreIdentity::PAYMENTS, array_flip((array) $identity['payments']));
@endphp
<footer class="ml-foot2" data-testid="store-footer">
    <div class="ml-wrap">
        <div class="ml-foot2-grid">
            <div>
                <div class="ml-foot2-brand">
                    @if($identity['logo_url'])<img src="{{ $identity['logo_url'] }}" alt="">@endif
                    <b>{{ $storeName }}</b>
                </div>
                @if($identity['tagline'])<p style="margin:0">{{ $identity['tagline'] }}</p>@endif
                @if($fSocial)
                    <div class="ml-foot2-soc">
                        @foreach($fSocial as $ic => $link)<a href="{{ $link }}" target="_blank" rel="noopener" aria-label="Rede social"><i class="{{ $ic }}"></i></a>@endforeach
                    </div>
                @endif
            </div>
            @if($fContacts)
                <div>
                    <h4>Atendimento</h4>
                    <ul>
                        @foreach($fContacts as [$ic, $txt, $link])
                            <li><i class="{{ $ic }}"></i>@if($link)<a href="{{ $link }}" @if(! str_starts_with($link, 'tel:') && ! str_starts_with($link, 'mailto:')) target="_blank" rel="noopener" @endif>{{ $txt }}</a>@else<span style="white-space:pre-line">{{ $txt }}</span>@endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($fPages->isNotEmpty())
                <div>
                    <h4>A loja</h4>
                    <ul>
                        @foreach($fPages as $k => [$t, $ic])
                            <li><i class="fas {{ $ic }}"></i><a href="{{ route('portal.store-page', ['userId' => $idOwner, 'page' => $k]) }}">{{ $t }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($fPay)
                <div>
                    <h4>Formas de pagamento</h4>
                    <div class="ml-foot2-pay">@foreach($fPay as $pname)<span>{{ $pname }}</span>@endforeach</div>
                </div>
            @endif
        </div>
        <p class="ml-foot2-copy">{{ $storeName }} · Catálogo online · {{ date('Y') }}</p>
    </div>
</footer>

@vite('resources/js/hover-photos.js')
<script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
