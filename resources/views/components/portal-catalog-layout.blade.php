@props(['title' => 'Catálogo de Produtos'])
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/portal/portal-base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/portal/portal-products.css') }}">
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
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        /* Portal product card */
        .portal-product-card { background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); border:1.5px solid #bae6fd; border-radius:1.5rem; box-shadow:0 4px 18px rgba(14,165,233,0.11); overflow:visible; display:flex; flex-direction:column; position:relative; isolation:isolate; z-index:1; transition:box-shadow 0.2s,border-color 0.2s,transform 0.2s cubic-bezier(.4,0,.2,1); }
        .portal-product-card:hover { transform:translateY(-4px); box-shadow:0 14px 36px rgba(14,165,233,0.20); border-color:#38bdf8; }
        .dark .portal-product-card { background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); border-color:#334155; }
        .dark .portal-product-card:hover { border-color:#38bdf8; box-shadow:0 14px 36px rgba(14,165,233,0.28); }
        .portal-product-img-area { position:relative; height:15rem; border-radius:1.2rem 1.2rem 0 0; background:linear-gradient(120deg,#f0f9ff 60%,#e0f2fe 100%); overflow:visible; flex-shrink:0; }
        .dark .portal-product-img-area { background:linear-gradient(120deg,#0f172a 60%,#1e293b 100%); }
        .portal-product-img-area img { width:100%; height:100%; object-fit:cover; border-radius:1.2rem 1.2rem 0 0; transition:transform 0.35s; display:block; }
        .portal-product-card:hover .portal-product-img-area img { transform:scale(1.06); }
        .portal-product-img-area .pimg-placeholder { width:100%; height:100%; border-radius:1.2rem 1.2rem 0 0; display:flex; align-items:center; justify-content:center; }
        .badge-stock { position:absolute; top:0.5rem; right:0.5rem; display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.65rem; font-weight:700; z-index:3; }
        .pcard-cat-circle { position:absolute; left:50%; bottom:-32px; transform:translateX(-50%); width:62px; height:62px; background:#fff; border:2.5px solid #bae6fd; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px rgba(14,165,233,0.25); z-index:20; font-size:2.1rem; color:#0369a1; flex-shrink:0; }
        .dark .pcard-cat-circle { background:#1e293b; border-color:#334155; color:#38bdf8; }
        .pcard-body { padding:3em 0.9rem 2.2rem 0.9rem; display:flex; flex-direction:column; flex:1; align-items:center; text-align:center; gap:0.35em; }
        .portal-input { width:100%; padding:0.6rem 1rem; border:1px solid #e5e7eb; border-radius:0.75rem; font-size:0.8125rem; transition:all 0.15s; background:#fff; color:#111827; }
        .portal-input:focus { outline:none; border-color:#38bdf8; box-shadow:0 0 0 3px rgba(14,165,233,0.15); }
        .dark .portal-input { background:#1e293b; border-color:#334155; color:#e2e8f0; }
        .dark .portal-input:focus { border-color:#38bdf8; }
        .portal-card { background:#fff; border-radius:1.25rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .dark .portal-card { background:#1e293b; border-color:#334155; }
        main { animation:fadeIn .18s ease; }
        @keyframes fadeIn { from{opacity:.5;transform:translateY(4px)}to{opacity:1;transform:none} }
        .status-badge { display:inline-flex; align-items:center; gap:0.3rem; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.7rem; font-weight:700; }
        .portal-table thead th { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:0.6rem 1rem; }
        .portal-table tbody td { padding:0.65rem 1rem; font-size:0.8125rem; border-top:1px solid #f3f4f6; }
        .dark .portal-table tbody td { border-color:#334155; }
    </style>
    @stack('styles')
</head>
<body class="min-h-full bg-slate-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 transition-colors duration-200">

    {{-- Top navbar --}}
    <div x-data="{ dark: document.documentElement.classList.contains('dark') }">
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-100 dark:border-slate-800 shadow-sm">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-4">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5 flex-shrink-0">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#38bdf8,#6366f1)">
                    <i class="fas fa-store text-white text-sm"></i>
                </div>
                <span class="font-black text-gray-900 dark:text-white text-sm hidden sm:block">{{ config('app.name') }}</span>
            </div>

            {{-- Search bar (center) --}}
            <form method="GET" action="{{ route('portal.catalog') }}" class="flex-1 max-w-md mx-auto hidden sm:block">
                <input type="hidden" name="owner" value="{{ request('owner') ?? $ownerId ?? '' }}">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sky-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Buscar produtos..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 dark:focus:ring-sky-900/30 transition-all">
                </div>
            </form>

            <div class="ml-auto flex items-center gap-2">
                {{-- Dark mode --}}
                <button type="button"
                        @click="dark=!dark; document.documentElement.classList.toggle('dark',dark); localStorage.setItem('portal-dark',dark?'1':'0')"
                        class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors text-sm">
                    <i x-show="!dark" class="fas fa-moon"></i>
                    <i x-show="dark" class="fas fa-sun" x-cloak></i>
                </button>

                {{-- Cart button --}}
                <div x-data="{ cnt: 0, init() { this.cnt = JSON.parse(localStorage.getItem('portal_cart')||'[]').length; } }" x-init="init()">
                    <a href="{{ Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']) }}"
                       class="relative flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-xs font-black rounded-xl transition-all shadow-sm">
                        <i class="fas fa-basket-shopping text-xs"></i>
                        <span class="hidden sm:inline">Meu Carrinho</span>
                        <span x-show="cnt > 0"
                              class="bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full"
                              x-text="cnt"></span>
                    </a>
                </div>

                {{-- Login / Account --}}
                @if(Auth::guard('portal')->check())
                <a href="{{ route('portal.dashboard') }}"
                   class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 dark:border-slate-600 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-user text-xs"></i>
                    <span class="hidden sm:inline">Meu Painel</span>
                </a>
                @else
                <a href="{{ route('portal.login') }}"
                   class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 dark:border-slate-600 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-sign-in-alt text-xs"></i>
                    <span class="hidden sm:inline">Entrar</span>
                </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot }}
    </main>

    </div>{{-- /x-data dark --}}

    @stack('scripts')
</body>
</html>
