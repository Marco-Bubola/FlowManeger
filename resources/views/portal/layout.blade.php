<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal do Cliente' }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }
        .sidebar-link:not(.active) { color: #e0f2fe; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 999px;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-gray-50 text-gray-900">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ── --}}
    <aside id="sidebar" class="w-64 flex-shrink-0 bg-gradient-to-b from-sky-700 via-sky-800 to-indigo-900 flex flex-col shadow-2xl transform -translate-x-full lg:translate-x-0 fixed lg:static inset-y-0 left-0 z-50 transition-transform duration-300">
        {{-- Logo --}}
        <div class="px-6 py-6 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-sky-200 font-medium uppercase tracking-widest">Portal</p>
                    <p class="text-white font-bold text-base leading-tight">{{ config('app.name') }}</p>
                </div>
            </div>
        </div>

        {{-- Client Info --}}
        <div class="px-6 py-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold shadow-md text-sm">
                    {{ strtoupper(substr(Auth::guard('portal')->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ Auth::guard('portal')->user()->name }}</p>
                    <p class="text-sky-300 text-xs truncate">{{ Auth::guard('portal')->user()->email }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('portal.dashboard') }}" class="sidebar-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Início</span>
            </a>
            <a href="{{ route('portal.sales') }}" class="sidebar-link {{ request()->routeIs('portal.sales') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag w-5 text-center"></i>
                <span>Minhas Compras</span>
            </a>
            <a href="{{ route('portal.products') }}" class="sidebar-link {{ request()->routeIs('portal.products') ? 'active' : '' }}">
                <i class="fas fa-box-open w-5 text-center"></i>
                <span>Catálogo de Produtos</span>
            </a>
            <div class="pt-2 border-t border-white/10 mt-2">
                <p class="px-4 py-1 text-xs font-semibold text-sky-300 uppercase tracking-wider">Orçamentos</p>
            </div>
            <a href="{{ route('portal.quotes') }}" class="sidebar-link {{ request()->routeIs('portal.quotes') ? 'active' : '' }}">
                <i class="fas fa-file-invoice w-5 text-center"></i>
                <span>Meus Pedidos</span>
            </a>
            <a href="{{ route('portal.quotes.create') }}" class="sidebar-link {{ request()->routeIs('portal.quotes.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle w-5 text-center"></i>
                <span>Novo Orçamento</span>
            </a>
            <div class="pt-2 border-t border-white/10 mt-2">
                <p class="px-4 py-1 text-xs font-semibold text-sky-300 uppercase tracking-wider">Conta</p>
            </div>
            <a href="{{ route('portal.profile') }}" class="sidebar-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-5 text-center"></i>
                <span>Meu Perfil</span>
            </a>
        </nav>

        {{-- Logout --}}
        <div class="px-3 py-4 border-t border-white/10">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-sky-100 hover:bg-white/10 hover:text-white transition-all duration-200">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- ── Main ── --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-4 lg:px-8 py-3 flex items-center justify-between flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <nav class="text-sm text-gray-500 hidden sm:block">
                    <span class="font-medium text-gray-900">{{ $title ?? 'Início' }}</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.quotes.create') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                    <i class="fas fa-plus text-xs"></i>
                    Novo Orçamento
                </a>
                <a href="{{ route('portal.profile') }}" class="w-9 h-9 bg-gradient-to-br from-sky-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md hover:shadow-lg transition-all hover:scale-110">
                    {{ strtoupper(substr(Auth::guard('portal')->user()->name, 0, 2)) }}
                </a>
            </div>
        </header>

        {{-- Alerts --}}
        <div class="px-4 lg:px-8 pt-4">
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm mb-2">
                    <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm mb-2">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 flex-shrink-0 mt-0.5"></i>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto px-4 lg:px-8 py-4">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="flex-shrink-0 px-4 lg:px-8 py-3 border-t border-gray-100 bg-white">
            <p class="text-xs text-gray-400 text-center">
                Portal do Cliente · {{ config('app.name') }} · {{ date('Y') }}
            </p>
        </footer>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
}
</script>
@stack('scripts')
</body>
</html>
