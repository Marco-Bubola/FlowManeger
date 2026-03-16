<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        @php
            $mobilePendingSales = 0;
            $mobilePendingMl = 0;
            try {
                if (auth()->check()) {
                    $mobilePendingSales = \App\Models\Sale::where('user_id', auth()->id())
                        ->where('status', 'pendente')
                        ->count();

                    $mobilePendingMl = \App\Models\MlPublication::where('user_id', auth()->id())
                        ->where('sync_status', 'pending')
                        ->count();
                }
            } catch (\Throwable $e) {
                $mobilePendingSales = 0;
                $mobilePendingMl = 0;
            }
        @endphp
        <!-- Modern Sidebar with Toggle -->
        <div id="modernSidebar" class="modern-sidebar mobile-sidebar-closed fixed left-0 top-0 h-screen z-50 transition-all duration-300 ease-in-out" style="width: 280px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 1.5px 8px 0 rgba(80, 80, 200, 0.10); border-radius: 0 1.5rem 1.5rem 0;">
                    <style>
                    @keyframes fade-slide-in {
                        0% { opacity: 0; transform: translateX(-24px); }
                        100% { opacity: 1; transform: translateX(0); }
                    }
                    .animate-fade-slide-in {
                        animation: fade-slide-in 0.7s cubic-bezier(.4,1.7,.6,1) 0s 1 both;
                    }
                    </style>
            <!-- Sidebar Container with Glassmorphism -->
            <div class="h-full relative overflow-hidden">
                <!-- Background Blur Layer -->
                <div class="absolute inset-0 bg-white/80 dark:bg-slate-900/90 backdrop-blur-xl border-r border-slate-200/50 dark:border-slate-700/50"></div>

                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 via-transparent to-purple-500/5 pointer-events-none"></div>

                <!-- Sidebar Content -->
                <div class="relative h-full flex flex-col">
                    <!-- Header Section -->
                    <div class="flex items-center justify-between p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 transition-all duration-300 hover:scale-105" wire:navigate>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                <x-app-logo class="w-6 h-6" />
                            </div>
                            <span class="sidebar-text font-black text-xl bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">FlowManager</span>
                        </a>

                        <!-- Toggle Button -->
                        <button id="sidebarToggle" class="lg:flex hidden w-9 h-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200 group">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Mobile Close -->
                        <button class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" onclick="closeMobileSidebar()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation Content -->
                    <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 custom-scrollbar">
                        <!-- Dashboards Section -->
                        <div class="mb-4">
                            <nav class="space-y-1">
                                <!-- Dashboard Geral (Principal) -->
                                <a href="{{ route('dashboard.index') }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.index') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.index') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Dashboard Geral</span>
                                    <div class="{{ request()->routeIs('dashboard.index') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                </a>

                                <!-- Dashboards Secundários (Indentados) -->
                                <div class="ml-4 space-y-1 border-l-2 border-slate-200 dark:border-slate-700 pl-2">
                                    <a href="{{ route('dashboard.cashbook') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.cashbook') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.cashbook') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Dashboard Financeiro</span>
                                        <div class="{{ request()->routeIs('dashboard.cashbook') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('dashboard.products') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.products') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.products') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Dashboard Produtos</span>
                                        <div class="{{ request()->routeIs('dashboard.products') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('dashboard.sales') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.sales') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.sales') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Dashboard Vendas</span>
                                        <div class="{{ request()->routeIs('dashboard.sales') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                    </a>
                                </div>
                            </nav>
                        </div>


                        <!-- Financeiro Section -->
                        <div class="mb-4">
                            <nav class="space-y-1">
                                <!-- Bancos com botão + -->
                                <a href="{{ url('banks') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('banks') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('banks') ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Bancos</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('banks.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-emerald-400 transition-all duration-200 flex-shrink-0" title="Novo Banco">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('banks') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-l-full"></div>
                                </a>

                                @php
                                    $__sidebar_banks = collect();
                                    try {
                                        if (auth()->check()) {
                                            $__sidebar_banks = \App\Models\Bank::where('user_id', auth()->id())->orderBy('name')->get();
                                        }
                                    } catch (\Throwable $e) {
                                        $__sidebar_banks = collect();
                                    }
                                @endphp

                                @if($__sidebar_banks->isNotEmpty())
                                    <div class="ml-4 space-y-1 border-l-2 border-slate-200 dark:border-slate-700 pl-2">
                                        @foreach ($__sidebar_banks as $__bank)
                                            @php
                                                // Melhor detecção de banco ativo
                                                $__currentUrl = request()->fullUrl();
                                                $__isCurrentBank = Request::is('invoices*') &&
                                                    (str_contains($__currentUrl, 'bankId=' . $__bank->id_bank) ||
                                                     str_contains($__currentUrl, '/invoices/' . $__bank->id_bank));
                                                $__iconSrc = null;
                                                try {
                                                    if (!empty($__bank->caminho_icone)) {
                                                        $__path = $__bank->caminho_icone;
                                                        $__isFull = \Illuminate\Support\Str::startsWith($__path, ['http://', 'https://']);
                                                        $__iconSrc = $__isFull ? $__path : asset($__path);
                                                    }
                                                } catch (\Throwable $__e) {
                                                    $__iconSrc = null;
                                                }
                                                $__initials = '';
                                                try {
                                                    $__parts = preg_split('/\s+/', trim($__bank->name));
                                                    foreach ($__parts as $__p) {
                                                        $__initials .= mb_substr($__p, 0, 1);
                                                        if (mb_strlen($__initials) >= 2) break;
                                                    }
                                                    $__initials = strtoupper($__initials ?: mb_substr($__bank->name, 0, 1));
                                                } catch (\Throwable $__e) {
                                                    $__initials = '';
                                                }
                                            @endphp
                                            <a href="{{ route('invoices.index', ['bankId' => $__bank->id_bank]) }}" class="relative flex flex-nowrap items-center gap-1.5 px-2 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ $__isCurrentBank ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate>
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ $__isCurrentBank ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : '' }}">
                                                    <span class="w-5 h-5 rounded-lg overflow-hidden flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                                                        @if($__iconSrc)
                                                            <img src="{{ $__iconSrc }}" alt="{{ $__bank->name }}" class="w-full h-full object-contain" />
                                                        @else
                                                            {{ $__initials }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="sidebar-text flex-1 font-medium truncate text-sm">{{ $__bank->name }}</span>
                                                <!-- Botões + e Upload -->
                                                <div class="flex items-center gap-0.5 flex-shrink-0">
                                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('invoices.create', ['bankId' => $__bank->id_bank]) }}'" class="w-6 h-6 flex items-center justify-center rounded-md bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-emerald-400 transition-all duration-200" title="Nova Fatura">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('invoices.upload', ['bankId' => $__bank->id_bank]) }}'" class="w-6 h-6 flex items-center justify-center rounded-md bg-blue-500/10 hover:bg-blue-500 hover:text-white text-blue-600 dark:text-blue-400 transition-all duration-200" title="Upload Faturas">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="{{ $__isCurrentBank ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-l-full"></div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <a href="{{ url('cashbook') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('cashbook') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('cashbook') ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Livro Caixa</span>
                                    <!-- Botões + e Upload -->
                                    <div class="sidebar-text flex items-center gap-0.5 flex-shrink-0">
                                        <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('cashbook.create') }}'" class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-emerald-400 transition-all duration-200" title="Nova Transação">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                        <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('cashbook.upload2') }}'" class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-500/10 hover:bg-blue-500 hover:text-white text-blue-600 dark:text-blue-400 transition-all duration-200" title="Upload Transações">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="{{ Request::is('cashbook') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-l-full"></div>
                                </a>

                                <a href="{{ url('cofrinhos') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('cofrinhos*') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('cofrinhos*') ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Cofrinhos</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('cofrinhos.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-emerald-400 transition-all duration-200 flex-shrink-0" title="Novo Cofrinho">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('cofrinhos*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-l-full"></div>
                                </a>

                                <a href="{{ route('goals.dashboard') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('goals*') ? 'bg-gradient-to-r from-purple-500/10 to-indigo-500/10 dark:from-purple-500/20 dark:to-indigo-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('goals*') ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <i class="bi bi-bullseye text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Metas e Objetivos</span>
                                    <div class="{{ Request::is('goals*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-indigo-600 rounded-l-full"></div>
                                </a>


                                <a href="{{ route('daily-habits.dashboard') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('daily-habits*') ? 'bg-gradient-to-r from-indigo-500/10 to-pink-500/10 dark:from-indigo-500/20 dark:to-pink-500/20 text-indigo-600 dark:text-indigo-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('daily-habits*') ? 'bg-gradient-to-br from-indigo-500 to-pink-600 text-white shadow-lg shadow-indigo-500/30' : '' }}">
                                        <i class="bi bi-calendar-check text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Hábitos Diários</span>
                                    <div class="{{ Request::is('daily-habits*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-indigo-500 to-pink-600 rounded-l-full"></div>
                                </a>

                                <!-- Achievements/Conquistas -->
                                <a href="{{ route('achievements.index') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-yellow-900 dark:hover:text-yellow-200 hover:translate-x-1 group {{ Request::is('achievements*') ? 'bg-gradient-to-r from-yellow-400/20 to-orange-400/20 dark:from-yellow-600/30 dark:to-orange-600/30 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800 transition-all duration-200 flex-shrink-0 {{ Request::is('achievements*') ? 'bg-gradient-to-br from-yellow-400 to-orange-400 text-white shadow-lg shadow-yellow-400/30' : '' }}">
                                        <i class="bi bi-trophy-fill text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Conquistas</span>
                                    <div class="{{ Request::is('achievements*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-yellow-400 to-orange-400 rounded-l-full"></div>
                                </a>

                                <a href="{{ route('consortiums.index') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('consortiums*') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('consortiums*') ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : '' }}">
                                        <i class="bi bi-piggy-bank text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Consórcios</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('consortiums.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-emerald-400 transition-all duration-200 flex-shrink-0" title="Novo Consórcio">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('consortiums*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-l-full"></div>
                                </a>
                            </nav>
                        </div>

                        <!-- Vendas e Produtos Section -->
                        <div class="mb-4">

                            <nav class="space-y-1">
                                <a href="{{ url('products') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('products') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('products') ? 'bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Produtos</span>
                                    <!-- Botões + e Upload -->
                                    <div class="sidebar-text flex items-center gap-0.5 flex-shrink-0">
                                        <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('products.create') }}'" class="w-7 h-7 flex items-center justify-center rounded-lg bg-purple-500/10 hover:bg-purple-500 hover:text-white text-purple-600 dark:text-purple-400 transition-all duration-200" title="Novo Produto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                        <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('products.upload') }}'" class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-500/10 hover:bg-blue-500 hover:text-white text-blue-600 dark:text-blue-400 transition-all duration-200" title="Upload Produtos">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="{{ Request::is('products') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-600 rounded-l-full"></div>
                                </a>

                                                                <a href="{{ url('clients') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('clients') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('clients') ? 'bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Clientes</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('clients.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-purple-500/10 hover:bg-purple-500 hover:text-white text-purple-600 dark:text-purple-400 transition-all duration-200 flex-shrink-0" title="Novo Cliente">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('clients') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-600 rounded-l-full"></div>
                                </a>

                                <a href="{{ url('sales') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('sales') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('sales') ? 'bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Vendas</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('sales.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-purple-500/10 hover:bg-purple-500 hover:text-white text-purple-600 dark:text-purple-400 transition-all duration-200 flex-shrink-0" title="Nova Venda">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('sales') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-600 rounded-l-full"></div>
                                </a>

                                <a href="{{ url('categories') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('categories') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('categories') ? 'bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Categorias</span>
                                    <!-- Botão + -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('categories.create') }}'" class="sidebar-text w-7 h-7 flex items-center justify-center rounded-lg bg-purple-500/10 hover:bg-purple-500 hover:text-white text-purple-600 dark:text-purple-400 transition-all duration-200 flex-shrink-0" title="Nova Categoria">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <div class="{{ Request::is('categories') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-600 rounded-l-full"></div>
                                </a>
                            </nav>
                        </div>

                        <!-- Integrações Section -->
                        <div class="mb-4">
                            <div class="sidebar-text px-3 mb-2">
                                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Integrações</p>
                            </div>
                            <nav class="space-y-1">
                                <!-- Mercado Livre com submenu -->
                                <a href="{{ route('mercadolivre.products') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre*') ? 'bg-gradient-to-r from-yellow-400/20 to-amber-500/20 dark:from-yellow-500/30 dark:to-amber-600/30 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre*') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-lg shadow-yellow-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Mercado Livre</span>
                                    <!-- Badge de progresso -->
                                    <div class="sidebar-text flex items-center gap-1 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-green-400/20 text-green-700 dark:bg-green-500/20 dark:text-green-300">100%</span>
                                    </div>
                                    <div class="{{ Request::is('mercadolivre*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                </a>

                                <!-- Submenu Mercado Livre -->
                                @if(Request::is('mercadolivre*'))
                                    <div class="ml-4 space-y-1 border-l-2 border-yellow-200 dark:border-yellow-800 pl-2">
                                        <a href="{{ route('mercadolivre.products') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/products') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/products') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V8"></path>
                                                </svg>
                                            </div>
                                            <span class="sidebar-text flex-1 font-medium truncate text-sm">Produtos ML</span>
                                            <div class="{{ Request::is('mercadolivre/products') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                        </a>

                                        <a href="{{ route('mercadolivre.orders') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/orders') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/orders') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                                <i class="bi bi-receipt text-sm"></i>
                                            </div>
                                            <span class="sidebar-text flex-1 font-medium truncate text-sm">Pedidos</span>
                                            <div class="{{ Request::is('mercadolivre/orders') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                        </a>

                                        <a href="{{ route('mercadolivre.publications') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/publications') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/publications') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                                <i class="bi bi-list-check text-sm"></i>
                                            </div>
                                            <span class="sidebar-text flex-1 font-medium truncate text-sm">Publicações</span>
                                            <div class="{{ Request::is('mercadolivre/publications') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                        </a>

                                        <a href="{{ route('mercadolivre.settings') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/settings') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate>
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/settings') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <span class="sidebar-text flex-1 font-medium truncate text-sm">Configurações</span>
                                            <div class="{{ Request::is('mercadolivre/settings') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                        </a>
                                    </div>
                                @endif
                            </nav>
                        </div>
                    </div>
                    <!-- Footer Section -->
                    <div class="border-t border-slate-200/50 dark:border-slate-700/50 p-3">
                        <!-- Notificações -->
                        <div class="mb-3">
                            @livewire('components.consortium-notifications')
                        </div>

                        <!-- User Profile -->
                        <div class="mb-3">
                            <button onclick="document.getElementById('userMenu').classList.toggle('hidden')" class="w-full flex items-center gap-3 p-3 rounded-xl bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-700 hover:from-slate-200 hover:to-slate-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-200 group">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                    {{ auth()->user()->initials() }}
                                </div>
                                <div class="sidebar-text flex-1 text-left">
                                    <p class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <svg class="sidebar-text w-5 h-5 text-slate-400 transition-transform duration-200 group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="userMenu" class="hidden mt-2 p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl">
                                <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all" wire:navigate>
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Configurações</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all text-red-600 dark:text-red-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Sair</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Links externos (compact mode) -->
                        <div class="sidebar-text flex items-center gap-2">
                            <a href="https://github.com/laravel/livewire-starter-kit" target="_blank" class="flex-1 flex items-center justify-center gap-2 p-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                <span class="font-medium">GitHub</span>
                            </a>
                            <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank" class="flex-1 flex items-center justify-center gap-2 p-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="font-medium">Docs</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobileSidebarBackdrop" class="mobile-sidebar-backdrop lg:hidden" onclick="closeMobileSidebar()" aria-hidden="true"></div>

        <!-- Mobile Bottom Tab Bar (premium) -->
        <nav class="mobile-bottom-tabbar lg:hidden" role="navigation" aria-label="Navegação principal">

            <!-- Inicio -->
            <a href="{{ route('dashboard.index') }}" wire:navigate
               class="mobile-tab-item {{ request()->routeIs('dashboard.*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"></path>
                    </svg>
                </div>
                <span>Inicio</span>
            </a>

            <!-- Vendas -->
            <a href="{{ url('sales') }}" wire:navigate
               class="mobile-tab-item {{ Request::is('sales*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span>Vendas</span>
                @if($mobilePendingSales > 0)
                    <span class="mobile-tab-badge">{{ $mobilePendingSales > 99 ? '99+' : $mobilePendingSales }}</span>
                @endif
            </a>

            <!-- FAB Central: Ações Rápidas -->
            <button type="button" class="mobile-tab-item mobile-tab-fab" onclick="openFabSheet()" aria-label="Ações rápidas">
                <div class="fab-circle">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </button>

            <!-- Produtos (mobile) / ML (iPad extra - hidden on pure mobile) -->
            <!-- ML tab: visivel somente no iPad (768px+) -->
            <a href="{{ route('mercadolivre.products') }}" wire:navigate
               class="mobile-tab-item tab-ipad-only {{ Request::is('mercadolivre*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-8-5v5m-4 0h16M3 3h18"></path>
                    </svg>
                </div>
                <span>ML</span>
                @if($mobilePendingMl > 0)
                    <span class="mobile-tab-badge">{{ $mobilePendingMl > 99 ? '99+' : $mobilePendingMl }}</span>
                @endif
            </a>

            <!-- Produtos -->
            <a href="{{ url('products') }}" wire:navigate
               class="mobile-tab-item {{ Request::is('products*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span>Produtos</span>
            </a>

            <!-- Mais -->
            <button type="button" class="mobile-tab-item" onclick="openMoreSheet()" aria-label="Mais opções">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h6"></path>
                    </svg>
                </div>
                <span>Mais</span>
            </button>
        </nav>

        <!-- FAB Sheet: Ações Rápidas -->
        <div id="mobileFabSheet" class="mobile-action-sheet lg:hidden" aria-hidden="true">
            <div class="mobile-sheet-backdrop" onclick="closeFabSheet()"></div>
            <div class="mobile-sheet-panel">
                <div class="mobile-sheet-handle"></div>
                <h3 class="mobile-sheet-title">
                    <svg class="w-5 h-5 inline-block mr-1.5 -mt-0.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Ações Rápidas
                </h3>
                <div class="mobile-sheet-grid">
                    <a href="{{ route('sales.create') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span>Nova Venda</span>
                    </a>
                    <a href="{{ route('products.upload') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <span>Upload</span>
                    </a>
                    <a href="{{ route('clients.create') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#8b5cf6,#7c3aed)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <span>Novo Cliente</span>
                    </a>
                    <a href="{{ route('products.create') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#f59e0b,#d97706)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <span>Novo Produto</span>
                    </a>
                    <a href="{{ route('mercadolivre.products') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#ef4444,#b91c1c)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-8-5v5m-4 0h16M3 3h18"></path>
                            </svg>
                        </div>
                        <span>Pub. ML</span>
                    </a>
                    <a href="{{ route('clients.index') }}" class="mobile-sheet-action" wire:navigate onclick="closeFabSheet()">
                        <div class="action-icon" style="background: linear-gradient(135deg,#06b6d4,#0891b2)">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span>Clientes</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mais Sheet: Navegação Completa -->
        <div id="mobileMoreSheet" class="mobile-action-sheet lg:hidden" aria-hidden="true">
            <div class="mobile-sheet-backdrop" onclick="closeMoreSheet()"></div>
            <div class="mobile-sheet-panel">
                <div class="mobile-sheet-handle"></div>
                <h3 class="mobile-sheet-title">
                    <svg class="w-5 h-5 inline-block mr-1.5 -mt-0.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Menu Completo
                </h3>

                {{-- NAVEGAÇÃO PRINCIPAL --}}
                <p class="mobile-sheet-section-label">Navegação</p>
                <div class="mobile-sheet-list">
                    <a href="{{ url('products') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Produtos</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('clients.index') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Clientes</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('mercadolivre.products') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-8-5v5m-4 0h16M3 3h18"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Mercado Livre</span>
                        @if($mobilePendingMl > 0)
                            <span class="mobile-sheet-badge">{{ $mobilePendingMl > 99 ? '99+' : $mobilePendingMl }}</span>
                        @endif
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                {{-- DASHBOARDS --}}
                <p class="mobile-sheet-section-label" style="margin-top:0.9rem">Dashboards</p>
                <div class="mobile-sheet-list">
                    <a href="{{ route('dashboard.index') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Dashboard Geral</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('dashboard.cashbook') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Financeiro</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('dashboard.sales') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Dashboard Vendas</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('dashboard.clients') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Dashboard Clientes</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                {{-- CRIAR / UPLOAD --}}
                <p class="mobile-sheet-section-label" style="margin-top:0.9rem">Criar &amp; Importar</p>
                <div class="mobile-sheet-list">
                    <a href="{{ route('sales.create') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Nova Venda</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('products.create') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Novo Produto</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('clients.create') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Novo Cliente</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('products.upload') }}" class="mobile-sheet-nav-item" wire:navigate onclick="closeMoreSheet()">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        </div>
                        <span class="mobile-sheet-nav-label">Upload Produtos</span>
                        <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <div class="mobile-sheet-divider" style="margin-top:0.9rem"></div>
                <button class="mobile-sheet-nav-item w-full text-left" onclick="closeMoreSheet(); setTimeout(openMobileSidebar, 220)">
                    <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#475569,#334155)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </div>
                    <span class="mobile-sheet-nav-label">Menu Completo (Sidebar)</span>
                    <svg class="w-4 h-4 text-slate-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="mainContent" class="transition-all duration-300 ease-in-out" style="margin-left: 280px;">
            {{ $slot }}
        </div>

        <!-- Custom Styles -->
        <style>
            /* Custom Scrollbar - Invisível */
            .custom-scrollbar::-webkit-scrollbar {
                width: 0px;
                display: none;
            }
            .custom-scrollbar {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }

            /* Navigation Items */
            .nav-item {
                @apply relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300;
            }
            .nav-item:hover {
                @apply bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white transform translate-x-1;
            }
            .nav-item.active {
                @apply bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold;
            }
            .nav-item.nav-subitem {
                @apply py-2 text-sm;
            }
            .nav-icon-wrapper {
                @apply flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0;
            }
            .nav-item.active .nav-icon-wrapper {
                @apply bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30;
            }
            .nav-text {
                @apply flex-1 font-medium truncate;
            }
            .nav-indicator {
                @apply hidden absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full;
            }
            .nav-item.active .nav-indicator {
                @apply block;
            }

            /* Compact Mode */
            body.sidebar-compact #modernSidebar {
                width: 100px !important;
            }
            body.sidebar-compact #mainContent {
                margin-left: 100px !important;
            }
            body.sidebar-compact .sidebar-text {
                display: none !important;
            }
            body.sidebar-compact .nav-item {
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            body.sidebar-compact .nav-icon-wrapper {
                margin: 0 auto;
            }
            body.sidebar-compact #sidebarToggle svg {
                transform: rotate(180deg);
            }

            /* Tablet: compact sidebar on iPad landscape and larger tablets */
            @media (min-width: 1025px) and (max-width: 1366px) {
                #modernSidebar {
                    width: 240px !important;
                }

                #mainContent {
                    margin-left: 240px !important;
                }
            }

            /* Mobile Responsive */
            @media (max-width: 1024px) {
                #modernSidebar {
                    width: min(86vw, 280px) !important;
                    border-radius: 0 1.1rem 1.1rem 0 !important;
                }
                #mainContent {
                    margin-left: 0 !important;
                    padding-bottom: calc(76px + env(safe-area-inset-bottom));
                }

                #modernSidebar.mobile-sidebar-closed {
                    transform: translateX(-100%);
                }

                #modernSidebar:not(.mobile-sidebar-closed) {
                    transform: translateX(0);
                }

                #modernSidebar.animate-fade-slide-in {
                    animation: none;
                }

                .mobile-sidebar-backdrop {
                    position: fixed;
                    inset: 0;
                    z-index: 45;
                    background: rgba(2, 6, 23, 0.42);
                    backdrop-filter: blur(3px);
                    opacity: 0;
                    pointer-events: none;
                    transition: opacity 0.2s ease;
                }

                #modernSidebar:not(.mobile-sidebar-closed) ~ .mobile-sidebar-backdrop {
                    opacity: 1;
                    pointer-events: auto;
                }
            }

            /* ─── Mobile bottom tab bar (premium) ─── */
            .mobile-bottom-tabbar {
                position: fixed;
                left: 0; right: 0; bottom: 0;
                z-index: 60;
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                align-items: center;
                gap: 0;
                padding: 0.25rem 0.35rem calc(0.3rem + env(safe-area-inset-bottom));
                background: rgba(255,255,255,0.96);
                backdrop-filter: blur(20px) saturate(1.8);
                -webkit-backdrop-filter: blur(20px) saturate(1.8);
                border-top: 1px solid rgba(148,163,184,0.2);
                box-shadow: 0 -6px 28px rgba(15,23,42,0.10), 0 -1px 0 rgba(148,163,184,0.15);
            }

            .dark .mobile-bottom-tabbar {
                background: rgba(8,12,26,0.95);
                border-top-color: rgba(51,65,85,0.5);
                box-shadow: 0 -6px 28px rgba(0,0,0,0.35), 0 -1px 0 rgba(51,65,85,0.35);
            }

            /* Tab item base */
            .mobile-tab-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.14rem;
                padding: 0.28rem 0.15rem;
                min-height: 3rem;
                border-radius: 0.75rem;
                color: #64748b;
                font-size: 0.6rem;
                font-weight: 700;
                letter-spacing: 0.015em;
                line-height: 1;
                transition: color 0.2s ease, transform 0.18s cubic-bezier(0.34,1.56,0.64,1);
                background: transparent;
                border: none;
                cursor: pointer;
                position: relative;
                text-decoration: none;
                -webkit-tap-highlight-color: transparent;
                user-select: none;
            }

            .dark .mobile-tab-item { color: #94a3b8; }

            /* Tab icon wrapper */
            .mobile-tab-item .tab-icon {
                width: 1.7rem;
                height: 1.7rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
            }

            /* Active state */
            .mobile-tab-item.is-active { color: #3b82f6; }
            .dark .mobile-tab-item.is-active { color: #60a5fa; }

            .mobile-tab-item.is-active .tab-icon {
                background: linear-gradient(135deg,rgba(59,130,246,.14),rgba(99,102,241,.2));
                transform: translateY(-3px) scale(1.1);
                box-shadow: 0 5px 14px rgba(59,130,246,.22);
            }
            .dark .mobile-tab-item.is-active .tab-icon {
                background: linear-gradient(135deg,rgba(30,64,175,.4),rgba(79,70,229,.4));
                box-shadow: 0 5px 14px rgba(59,130,246,.35);
            }

            /* Active dot */
            .mobile-tab-item.is-active::after {
                content:'';
                position:absolute;
                bottom:0.12rem;
                left:50%;
                transform:translateX(-50%);
                width:0.3rem;
                height:0.3rem;
                border-radius:50%;
                background:linear-gradient(135deg,#3b82f6,#6366f1);
                box-shadow: 0 0 4px rgba(99,102,241,.5);
            }

            /* FAB center button */
            .mobile-tab-fab { gap: 0 !important; padding: 0 !important; }

            .mobile-tab-fab .fab-circle {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                background: linear-gradient(135deg,#3b82f6 0%,#6366f1 50%,#8b5cf6 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 6px 22px rgba(99,102,241,.45), 0 2px 8px rgba(59,130,246,.3), inset 0 1px 0 rgba(255,255,255,.2);
                transform: translateY(-5px);
                transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1);
                color: white;
            }
            .mobile-tab-fab:active .fab-circle,
            .mobile-tab-fab:focus-visible .fab-circle {
                transform: translateY(-3px) scale(0.93);
                box-shadow: 0 3px 12px rgba(99,102,241,.3);
            }

            /* Badge */
            .mobile-tab-badge {
                position: absolute;
                top: 0.08rem;
                right: calc(50% - 0.95rem);
                min-width: 1.05rem;
                height: 1.05rem;
                padding: 0 0.2rem;
                border-radius: 9999px;
                background: linear-gradient(135deg,#ef4444,#f97316);
                color: #fff;
                font-size: 0.55rem;
                font-weight: 800;
                line-height: 1.05rem;
                text-align: center;
                box-shadow: 0 0 0 2px white;
                animation: mobile-badge-pulse 1.8s infinite;
                pointer-events: none;
            }
            .dark .mobile-tab-badge { box-shadow: 0 0 0 2px rgba(8,12,26,.95); }

            @keyframes mobile-badge-pulse {
                0%,100% { transform: scale(1); }
                50% { transform: scale(1.12); }
            }

            /* ML-only tab: hidden on mobile, shown on iPad */
            .tab-ipad-only { display: none !important; }

            /* iPad portrait: 6-column layout with ML tab (override Tailwind lg:hidden at 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) {
                .mobile-bottom-tabbar {
                    display: grid !important;
                    grid-template-columns: repeat(6, minmax(0, 1fr));
                    padding: 0.45rem 0.75rem calc(0.5rem + env(safe-area-inset-bottom));
                }
                .tab-ipad-only {
                    display: flex !important;
                }
                .mobile-tab-item {
                    min-height: 3.6rem;
                    font-size: 0.65rem;
                    gap: 0.18rem;
                }
                .mobile-tab-item .tab-icon {
                    width: 1.85rem;
                    height: 1.85rem;
                }
                .mobile-tab-fab .fab-circle {
                    width: 3rem;
                    height: 3rem;
                    transform: translateY(-6px);
                }
            }

            /* ─── Action Sheets ─── */
            .mobile-action-sheet {
                position: fixed;
                inset: 0;
                z-index: 80;
                pointer-events: none;
            }
            .mobile-action-sheet.is-open { pointer-events: auto; }

            .mobile-sheet-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(2,6,23,.55);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                opacity: 0;
                transition: opacity 0.25s ease;
            }
            .mobile-action-sheet.is-open .mobile-sheet-backdrop { opacity: 1; }

            .mobile-sheet-panel {
                position: absolute;
                left: 0; right: 0; bottom: 0;
                background: #ffffff;
                border-radius: 1.5rem 1.5rem 0 0;
                padding: 0.4rem 1.1rem calc(1.4rem + env(safe-area-inset-bottom));
                transform: translateY(100%);
                transition: transform 0.32s cubic-bezier(0.32,0.72,0,1);
                box-shadow: 0 -10px 50px rgba(15,23,42,.18);
                max-height: 88dvh;
                overflow-y: auto;
                overscroll-behavior: contain;
            }
            .dark .mobile-sheet-panel {
                background: #0d1526;
                border-top: 1px solid rgba(51,65,85,.4);
            }
            .mobile-action-sheet.is-open .mobile-sheet-panel { transform: translateY(0); }

            .mobile-sheet-handle {
                width: 2.5rem; height: 0.25rem;
                border-radius: 9999px;
                background: #e2e8f0;
                margin: 0.3rem auto 0.85rem;
            }
            .dark .mobile-sheet-handle { background: #1e293b; }

            .mobile-sheet-title {
                font-size: 1rem;
                font-weight: 800;
                color: #0f172a;
                margin-bottom: 0.9rem;
                display: flex;
                align-items: center;
            }
            .dark .mobile-sheet-title { color: #f1f5f9; }

            /* FAB Sheet: grid 3 cols */
            .mobile-sheet-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.7rem;
                padding-bottom: 0.5rem;
            }
            .mobile-sheet-action {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                padding: 0.85rem 0.4rem;
                border-radius: 1.1rem;
                background: #f8fafc;
                border: 1.5px solid #e2e8f0;
                text-decoration: none;
                color: #0f172a;
                font-size: 0.7rem;
                font-weight: 700;
                transition: all 0.16s ease;
                -webkit-tap-highlight-color: transparent;
            }
            .dark .mobile-sheet-action {
                background: #1e293b;
                border-color: rgba(51,65,85,.55);
                color: #f1f5f9;
            }
            .mobile-sheet-action:active { transform: scale(0.94); }
            .action-icon {
                width: 2.9rem; height: 2.9rem;
                border-radius: 0.85rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.3rem;
                box-shadow: 0 4px 12px rgba(0,0,0,.15);
            }

            /* More Sheet: nav list */
            .mobile-sheet-list {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
            }
            .mobile-sheet-nav-item {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding: 0.8rem 0.6rem;
                border-radius: 0.85rem;
                text-decoration: none;
                color: #0f172a;
                font-size: 0.88rem;
                font-weight: 600;
                background: transparent;
                border: none;
                cursor: pointer;
                transition: background 0.14s ease;
                -webkit-tap-highlight-color: transparent;
            }
            .dark .mobile-sheet-nav-item { color: #e2e8f0; }
            .mobile-sheet-nav-item:active,
            .mobile-sheet-nav-item:hover { background: #f1f5f9; }
            .dark .mobile-sheet-nav-item:active,
            .dark .mobile-sheet-nav-item:hover { background: #1e293b; }

            .mobile-sheet-nav-icon {
                width: 2.3rem; height: 2.3rem;
                border-radius: 0.65rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1rem;
                flex-shrink: 0;
                box-shadow: 0 3px 8px rgba(0,0,0,.12);
            }
            .mobile-sheet-nav-label { flex: 1; }
            .mobile-sheet-badge {
                min-width: 1.35rem; height: 1.35rem;
                padding: 0 0.3rem;
                border-radius: 9999px;
                background: linear-gradient(135deg,#ef4444,#f97316);
                color: white;
                font-size: 0.62rem;
                font-weight: 800;
                line-height: 1.35rem;
                text-align: center;
            }
            .mobile-sheet-divider {
                height: 1px;
                background: #e2e8f0;
                margin: 0.4rem 0;
            }
            .dark .mobile-sheet-divider { background: #1e293b; }

            /* Section labels inside More sheet */
            .mobile-sheet-section-label {
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #94a3b8;
                padding: 0 0.3rem;
                margin: 0.2rem 0 0.3rem;
            }
            .dark .mobile-sheet-section-label { color: #475569; }

            /* ─── Animations ─── */
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(-10px); }
                to { opacity: 1; transform: translateX(0); }
            }
            .nav-item {
                animation: slideIn 0.3s ease-out;
            }
        </style>

        <!-- Toggle Script -->
        <script>
            function openMobileSidebar() {
                const sidebar = document.getElementById('modernSidebar');
                if (!sidebar) return;
                sidebar.classList.remove('mobile-sidebar-closed');
                document.body.classList.add('overflow-hidden');
            }

            function closeMobileSidebar() {
                const sidebar = document.getElementById('modernSidebar');
                if (!sidebar) return;
                sidebar.classList.add('mobile-sidebar-closed');
                document.body.classList.remove('overflow-hidden');
            }

            function openFabSheet() {
                const sheet = document.getElementById('mobileFabSheet');
                if (!sheet) return;
                sheet.classList.add('is-open');
                sheet.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function closeFabSheet() {
                const sheet = document.getElementById('mobileFabSheet');
                if (!sheet) return;
                sheet.classList.remove('is-open');
                sheet.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            }

            function openMoreSheet() {
                const sheet = document.getElementById('mobileMoreSheet');
                if (!sheet) return;
                sheet.classList.add('is-open');
                sheet.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function closeMoreSheet() {
                const sheet = document.getElementById('mobileMoreSheet');
                if (!sheet) return;
                sheet.classList.remove('is-open');
                sheet.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            }

            function initSidebar() {
                const toggle = document.getElementById('sidebarToggle');
                const sidebar = document.getElementById('modernSidebar');

                // Load saved compact state (desktop only — > 1024px)
                const isCompact = localStorage.getItem('sidebarCompact') === 'true';
                if (isCompact && window.innerWidth > 1024) {
                    document.body.classList.add('sidebar-compact');
                } else {
                    document.body.classList.remove('sidebar-compact');
                }

                // Enforce mobile-sidebar-closed on mobile/tablet (including 1024px iPad Pro)
                if (window.innerWidth <= 1024) {
                    sidebar?.classList.add('mobile-sidebar-closed');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    sidebar?.classList.remove('mobile-sidebar-closed');
                    document.body.classList.remove('overflow-hidden');
                }

                // Close any open sheets on navigation
                closeFabSheet();
                closeMoreSheet();

                // Desktop toggle functionality
                if (toggle && !toggle.hasAttribute('data-sidebar-initialized')) {
                    toggle.setAttribute('data-sidebar-initialized', 'true');
                    toggle.addEventListener('click', function() {
                        document.body.classList.toggle('sidebar-compact');
                        const compact = document.body.classList.contains('sidebar-compact');
                        localStorage.setItem('sidebarCompact', compact);
                    });
                }

                // Close mobile sidebar on nav link click
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.addEventListener('click', function() {
                        if (window.innerWidth <= 1024) {
                            closeMobileSidebar();
                        }
                    });
                });

                // Close user dropdown on outside click
                document.addEventListener('click', function(e) {
                    const userMenu = document.getElementById('userMenu');
                    const userButton = userMenu?.previousElementSibling;
                    if (userMenu && !userMenu.contains(e.target) && !userButton?.contains(e.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', initSidebar);
            document.addEventListener('livewire:navigated', initSidebar);
        </script>

        @fluxScripts
    </body>
</html>
