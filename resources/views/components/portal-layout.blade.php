@props(['title' => 'Portal do Cliente'])
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/portal/portal-base.css') }}">
    {{-- Dark mode antes do Tailwind para evitar FOUC --}}
    <script>(function(){var d=localStorage.getItem('portal-dark');if(d==='1')document.documentElement.classList.add('dark');})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                        dark:  { bg:'#0f172a', card:'#1e293b', border:'#334155', hover:'#293548', input:'#1e293b', muted:'#94a3b8', text:'#e2e8f0', title:'#f1f5f9' }
                    },
                    screens: { 'xs': '480px' }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        /* Scrollbar fina */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        /* Sidebar links */
        .sidebar-link { display:flex; align-items:center; gap:0.75rem; padding:0.55rem 0.875rem; border-radius:0.75rem; font-size:0.8125rem; font-weight:500; transition:all 0.15s; color:#bae6fd; }
        .sidebar-link:hover { background:rgba(255,255,255,0.12); color:#fff; }
        .sidebar-link.active { background:rgba(255,255,255,0.22); color:#fff; box-shadow:0 1px 6px rgba(0,0,0,0.18) inset; }

        /* ═══ Portal product card — pixel-perfect com product-card-modern do app ═══ */
        .portal-product-card {
            background: linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%);
            border: 1.5px solid #bae6fd;
            border-radius: 1.5rem;
            box-shadow: 0 4px 18px rgba(14,165,233,0.11);
            overflow: visible; /* crítico: ícone circular transborda */
            display: flex;
            flex-direction: column;
            position: relative;
            isolation: isolate;
            z-index: 1;
            transition: box-shadow 0.2s,border-color 0.2s,transform 0.2s cubic-bezier(.4,0,.2,1);
        }
        .portal-product-card:hover { transform:translateY(-4px); box-shadow:0 14px 36px rgba(14,165,233,0.20); border-color:#38bdf8; }
        .dark .portal-product-card { background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); border-color:#334155; }
        .dark .portal-product-card:hover { border-color:#38bdf8; box-shadow:0 14px 36px rgba(14,165,233,0.28); }

        /* Área de imagem — overflow:visible para que o ícone circle transborde */
        .portal-product-img-area {
            position: relative;
            height: 15rem; /* 240px igual ao product-card-modern */
            border-radius: 1.2rem 1.2rem 0 0;
            background: linear-gradient(120deg,#f0f9ff 60%,#e0f2fe 100%);
            overflow: visible;
            flex-shrink: 0;
        }
        .dark .portal-product-img-area { background:linear-gradient(120deg,#0f172a 60%,#1e293b 100%); }
        /* A imagem carrega o border-radius para não vazar nos cantos */
        .portal-product-img-area img { width:100%; height:100%; object-fit:cover; border-radius:1.2rem 1.2rem 0 0; transition:transform 0.35s; display:block; }
        .portal-product-card:hover .portal-product-img-area img { transform:scale(1.06); }
        /* Placeholder sem imagem */
        .portal-product-img-area .pimg-placeholder { width:100%; height:100%; border-radius:1.2rem 1.2rem 0 0; display:flex; align-items:center; justify-content:center; }

        /* Stock badge */
        .badge-stock { position:absolute; top:0.5rem; right:0.5rem; display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.65rem; font-weight:700; z-index:3; }

        /* Ícone circular central — 62px igual ao product-card-modern */
        .pcard-cat-circle {
            position: absolute;
            left: 50%;
            bottom: -32px; /* sai 32px abaixo, metade visível na img, metade no body */
            transform: translateX(-50%);
            width: 62px;
            height: 62px;
            background: #fff;
            border: 2.5px solid #bae6fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(14,165,233,0.25);
            z-index: 20;
            font-size: 2.1rem; /* igual ao category-icon do product-card-modern */
            color: #0369a1;
            flex-shrink: 0;
        }
        .dark .pcard-cat-circle { background:#1e293b; border-color:#334155; color:#38bdf8; }

        /* Corpo do card — 3em padding-top igual ao product-card-modern */
        .pcard-body {
            padding: 3em 0.9rem 2.2rem 0.9rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            align-items: center;
            text-align: center;
            gap: 0.35em;
        }

        /* Compact table */
        .portal-table thead th { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:0.6rem 1rem; }
        .portal-table tbody td { padding:0.65rem 1rem; font-size:0.8125rem; border-top:1px solid #f3f4f6; }
        .dark .portal-table tbody td { border-color:#334155; }

        /* Status badges */
        .status-badge { display:inline-flex; align-items:center; gap:0.3rem; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.7rem; font-weight:700; }

        /* Smooth page transitions */
        main { animation: fadeIn .18s ease; }
        @keyframes fadeIn { from { opacity:.5; transform:translateY(4px); } to { opacity:1; transform:none; } }
        [x-cloak] { display: none !important; }

        /* Bottom tab bar */
        .bottom-tab { display:flex; flex-direction:column; align-items:center; gap:0.2rem; flex:1; padding:0.55rem 0 0.45rem; font-size:0.6rem; font-weight:700; color:#94a3b8; transition:color 0.15s; cursor:pointer; text-decoration:none; }
        .bottom-tab i { font-size:1.1rem; }
        .bottom-tab.active, .bottom-tab:hover { color:#0ea5e9; }
        .dark .bottom-tabbar-wrap { background:rgba(15,23,42,0.92) !important; border-top-color:rgba(51,65,85,0.6) !important; }
        .dark .bottom-tab { color:#64748b; }
        .dark .bottom-tab.active, .dark .bottom-tab:hover { color:#38bdf8; }

        /* Safe area on mobile */
        @supports(padding-bottom: env(safe-area-inset-bottom)) {
            .bottom-tabbar { padding-bottom: env(safe-area-inset-bottom); }
        }

        @media (max-width: 1023px) {
            main { padding-bottom: 5.5rem; }
        }
        .portal-input { width:100%; padding:0.6rem 1rem; border:1px solid #e5e7eb; border-radius:0.75rem; font-size:0.8125rem; transition:all 0.15s; background:#fff; color:#111827; }
        .portal-input:focus { outline:none; border-color:#38bdf8; box-shadow:0 0 0 3px rgba(14,165,233,0.15); }
        .dark .portal-input { background:#1e293b; border-color:#334155; color:#e2e8f0; }
        .dark .portal-input:focus { border-color:#38bdf8; box-shadow:0 0 0 3px rgba(14,165,233,0.2); }

        /* Card standard */
        .portal-card { background:#fff; border-radius:1.25rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .dark .portal-card { background:#1e293b; border-color:#334155; }

        /* KPI tile */
        .kpi-tile { background:#fff; border-radius:1.25rem; border:1px solid #e5e7eb; padding:1rem 1.25rem; transition:box-shadow 0.2s; }
        .kpi-tile:hover { box-shadow:0 4px 16px rgba(14,165,233,0.1); }
        .dark .kpi-tile { background:#1e293b; border-color:#334155; }

        /* Alert dismiss */
        .portal-alert { display:flex; align-items:flex-start; gap:0.75rem; padding:0.85rem 1rem; border-radius:0.875rem; font-size:0.8125rem; border:1px solid; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 transition-colors duration-200">

{{-- Alpine dark mode state (body-level) --}}
<div x-data="{
    dark: document.documentElement.classList.contains('dark'),
    cartCount: 0,
    initCart() {
        try {
            const d = JSON.parse(localStorage.getItem('portal_cart') || '[]');
            this.cartCount = Array.isArray(d) ? d.length : 0;
        } catch(e) { this.cartCount = 0; }
        // Atualiza ao voltar para a aba (ex: retornou da página de carrinho)
        window.addEventListener('focus', () => {
            try {
                const d = JSON.parse(localStorage.getItem('portal_cart') || '[]');
                this.cartCount = Array.isArray(d) ? d.length : 0;
            } catch(e) {}
        });
        // Atualiza ao evento 'storage' (outras abas)
        window.addEventListener('storage', (e) => {
            if (e.key === 'portal_cart') {
                try { this.cartCount = Array.isArray(JSON.parse(e.newValue||'[]')) ? JSON.parse(e.newValue||'[]').length : 0; } catch(e) {}
            }
        });
        // Atualiza quando modal de produto adiciona ao carrinho (mesma aba)
        window.addEventListener('portal-cart-updated', () => {
            try {
                const d = JSON.parse(localStorage.getItem('portal_cart') || '[]');
                this.cartCount = Array.isArray(d) ? d.length : 0;
            } catch(e) {}
        });
    }
}" x-init="initCart()">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar (desktop) ── --}}
    <aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col shadow-2xl transform -translate-x-full lg:translate-x-0 fixed lg:static inset-y-0 left-0 z-50 transition-transform duration-300 overflow-hidden" style="background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 40%, #1e293b 100%);">

        {{-- Logo strip (top accent) --}}
        <div class="h-1 w-full" style="background: linear-gradient(90deg, #38bdf8, #6366f1, #a855f7)"></div>

        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-white/[0.07]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0" style="background: linear-gradient(135deg,#38bdf8,#6366f1)">
                    <i class="fas fa-store text-white text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-bold uppercase tracking-[.22em]" style="color:#38bdf8">Portal do Cliente</p>
                    <p class="text-white font-black text-sm leading-tight truncate">{{ config('app.name') }}</p>
                </div>
            </div>
        </div>

        {{-- Client info card --}}
        <div class="mx-3 mt-3 mb-2 rounded-xl px-3 py-2.5 border border-white/[0.08]" style="background:rgba(255,255,255,0.05)">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-black text-xs flex-shrink-0 shadow" style="background:linear-gradient(135deg,#0ea5e9,#818cf8)">
                    {{ strtoupper(substr(Auth::guard('portal')->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-white text-[11px] font-bold truncate leading-snug">{{ Auth::guard('portal')->user()->name }}</p>
                    <p class="text-[10px] truncate leading-none mt-0.5" style="color:#94a3b8">{{ Auth::guard('portal')->user()->email ?: Auth::guard('portal')->user()->portal_login }}</p>
                </div>
                <div class="w-2 h-2 rounded-full flex-shrink-0 shadow" style="background:#22c55e"></div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-2.5 pb-3 space-y-0.5 overflow-y-auto" style="-ms-overflow-style:none;scrollbar-width:none">
            @php
                $portalQuoteAlerts = Auth::guard('portal')->check()
                    ? \App\Models\ClientQuoteRequest::where('client_id', Auth::guard('portal')->id())->where('status', 'quoted')->count()
                    : 0;
            @endphp

            <div class="pt-2 pb-1">
                <p class="px-3 text-[9px] font-black uppercase tracking-[.22em]" style="color:#475569">Principal</p>
            </div>
            <a href="{{ route('portal.dashboard') }}" class="sidebar-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <i class="fas fa-house-chimney w-4 text-center text-sm flex-shrink-0"></i><span>Início</span>
            </a>
            <a href="{{ route('portal.sales') }}" class="sidebar-link {{ request()->routeIs('portal.sales') ? 'active' : '' }}">
                <i class="fas fa-bag-shopping w-4 text-center text-sm flex-shrink-0"></i><span>Minhas Compras</span>
            </a>
            <a href="{{ route('portal.products') }}" class="sidebar-link {{ request()->routeIs('portal.products') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-sm flex-shrink-0"></i><span>Catálogo de Produtos</span>
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-[9px] font-black uppercase tracking-[.22em]" style="color:#475569">Orçamentos</p>
            </div>
            <a href="{{ route('portal.quotes') }}" class="sidebar-link {{ request()->routeIs('portal.quotes') && !request()->routeIs('portal.quotes.create') ? 'active' : '' }}">
                <i class="fas fa-file-lines w-4 text-center text-sm flex-shrink-0"></i>
                <span class="flex-1">Meus Pedidos</span>
                @if($portalQuoteAlerts > 0)
                <span class="w-5 h-5 rounded-full text-[9px] font-black flex items-center justify-center flex-shrink-0 animate-pulse" style="background:#fbbf24;color:#1e293b">{{ $portalQuoteAlerts }}</span>
                @endif
            </a>
            <a href="{{ route('portal.quotes.create') }}" class="sidebar-link {{ request()->routeIs('portal.quotes.create') || request()->routeIs('portal.quotes.edit') ? 'active' : '' }}">
                <i class="fas fa-plus-circle w-4 text-center text-sm flex-shrink-0"></i><span>Novo Orçamento</span>
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-[9px] font-black uppercase tracking-[.22em]" style="color:#475569">Conta</p>
            </div>
            <a href="{{ route('portal.profile') }}" class="sidebar-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <i class="fas fa-circle-user w-4 text-center text-sm flex-shrink-0"></i><span>Meu Perfil</span>
            </a>
        </nav>

        {{-- Logout --}}
        <div class="px-2.5 py-3 border-t border-white/[0.07]">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-left hover:text-red-300">
                    <i class="fas fa-right-from-bracket w-4 text-center text-sm flex-shrink-0"></i><span>Sair da conta</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- ── Main area ── --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Top bar — apenas desktop (mobile navega via tab bar) --}}
        <header class="hidden lg:flex bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700 px-4 lg:px-6 py-2.5 items-center justify-between flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                {{-- Sem hamburger no mobile: navegação via tab bar abaixo --}}
                {{-- Breadcrumb (mobile: título da página; sm+: "Portal > título") --}}
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="hidden sm:inline text-gray-400 dark:text-slate-500">Portal</span>
                    <i class="hidden sm:inline fas fa-chevron-right text-gray-300 dark:text-slate-600 text-xs"></i>
                    <span class="font-semibold text-gray-800 dark:text-slate-200 text-sm">{{ $title }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Dark mode toggle --}}
                <button @click="dark = !dark; document.documentElement.classList.toggle('dark'); localStorage.setItem('portal-dark', dark ? '1' : '0')"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                    :title="dark ? 'Modo claro' : 'Modo escuro'">
                    <i class="fas" :class="dark ? 'fa-sun' : 'fa-moon'" style="font-size:0.875rem"></i>
                </button>
                {{-- New quote button --}}
                <a href="{{ route('portal.quotes.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow hover:shadow-sky-400/30 transition-all hover:scale-105">
                    <i class="fas fa-plus text-[10px]"></i> Novo Orçamento
                </a>
                {{-- Profile avatar --}}
                <a href="{{ route('portal.profile') }}" class="w-8 h-8 bg-gradient-to-br from-sky-400 to-violet-500 rounded-full flex items-center justify-center text-white font-black text-xs shadow hover:shadow-sky-400/40 transition-all hover:scale-110">
                    {{ strtoupper(substr(Auth::guard('portal')->user()->name, 0, 2)) }}
                </a>
            </div>
        </header>

        {{-- Alerts --}}
        @if(session('success') || session('error') || $errors->any())
        <div class="px-4 lg:px-6 pt-3 space-y-2">
            @if(session('success'))
                <div class="portal-alert bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700/50 text-green-800 dark:text-green-300">
                    <i class="fas fa-circle-check text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="portal-alert bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700/50 text-red-700 dark:text-red-300">
                    <i class="fas fa-circle-exclamation text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="portal-alert bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700/50 text-red-700 dark:text-red-300">
                    <i class="fas fa-triangle-exclamation text-red-400 flex-shrink-0 mt-0.5 text-sm"></i>
                    <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-4 lg:px-6 py-4 pb-6">
            {{ $slot }}
        </main>

        {{-- Footer (desktop) --}}
        <footer class="hidden lg:flex flex-shrink-0 px-6 py-2.5 border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-800 items-center justify-between">
            <p class="text-[11px] text-gray-400 dark:text-slate-500">Portal do Cliente · {{ config('app.name') }}</p>
            <p class="text-[11px] text-gray-400 dark:text-slate-500">{{ date('Y') }}</p>
        </footer>
    </div>
</div>

{{-- ── Bottom tab bar (mobile/tablet, oculto em lg+) ── --}}
<nav class="bottom-tabbar fixed bottom-0 inset-x-0 lg:hidden z-30"
     :style="dark
        ? 'background:rgba(15,23,42,0.96);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-top:1px solid rgba(51,65,85,0.55);box-shadow:0 -4px 24px rgba(0,0,0,0.3);'
        : 'background:rgba(255,255,255,0.93);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-top:1px solid rgba(0,0,0,0.06);box-shadow:0 -4px 24px rgba(0,0,0,0.07);'">
    <div class="flex items-end">

        {{-- Início --}}
        <a href="{{ route('portal.dashboard') }}"
           class="bottom-tab {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
            <i class="fas fa-house-chimney"></i>
            <span>Início</span>
        </a>

        {{-- Catálogo --}}
        <a href="{{ route('portal.products') }}"
           class="bottom-tab {{ request()->routeIs('portal.products') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked"></i>
            <span>Catálogo</span>
        </a>

        {{-- CARRINHO — botão central elevado --}}
        <a href="{{ route('portal.quotes.create') }}"
           class="relative flex flex-col items-center flex-1 -mt-4"
           style="text-decoration:none">
            {{-- círculo elevado --}}
            <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg
                        {{ request()->routeIs('portal.quotes.create') ? 'ring-2 ring-white ring-offset-2' : '' }}"
                 style="background:linear-gradient(135deg,#0ea5e9,#6366f1);box-shadow:0 6px 20px rgba(99,102,241,0.45)">
                <i class="fas fa-basket-shopping text-white" style="font-size:1.25rem"></i>
                {{-- Badge do carrinho via localStorage --}}
                <span x-show="cartCount > 0"
                      x-text="cartCount > 9 ? '9+' : cartCount"
                      class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] bg-rose-500 text-white rounded-full text-[9px] font-black flex items-center justify-center px-[3px] shadow"
                      x-cloak></span>
            </div>
            <span class="mt-1 text-[10px] font-bold"
                  style="color: {{ request()->routeIs('portal.quotes.create') ? '#0ea5e9' : '#64748b' }}">Carrinho</span>
        </a>

        {{-- Pedidos --}}
        <a href="{{ route('portal.quotes') }}"
           class="bottom-tab {{ request()->routeIs('portal.quotes') && !request()->routeIs('portal.quotes.create') ? 'active' : '' }}">
            <div class="relative">
                <i class="fas fa-file-lines"></i>
                @if($portalQuoteAlerts > 0)
                <span class="absolute -top-1.5 -right-2 w-4 h-4 bg-amber-400 text-slate-900 rounded-full text-[8px] font-black flex items-center justify-center">{{ $portalQuoteAlerts }}</span>
                @endif
            </div>
            <span>Pedidos</span>
        </a>

        {{-- Perfil --}}
        <a href="{{ route('portal.profile') }}"
           class="bottom-tab {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
            <i class="fas fa-circle-user"></i>
            <span>Perfil</span>
        </a>

    </div>
</nav>

</div>{{-- /alpine --}}

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.add('hidden');
}
</script>
@stack('scripts')
</body>
</html>
