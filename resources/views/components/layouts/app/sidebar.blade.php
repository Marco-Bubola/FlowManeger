<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <!-- Modern Sidebar with Toggle -->
        <div id="modernSidebar" class="modern-sidebar fixed left-0 top-0 h-screen z-50 transition-all duration-300 ease-in-out" style="width: 280px;">
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
                        <button class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" onclick="document.getElementById('modernSidebar').classList.add('-translate-x-full')">
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
                                                        $__isFull = \Illuminate\Support\Str::startsWith($__path, ['http://', 'https://', '/']);
                                                        $__iconSrc = $__isFull ? $__path : asset('storage/' . ltrim($__path, '/'));
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
                    </div>
                    <!-- Footer Section -->
                    <div class="border-t border-slate-200/50 dark:border-slate-700/50 p-3">
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

        <!-- Mobile Toggle Button -->
        <button class="lg:hidden fixed bottom-4 right-4 z-50 w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-2xl flex items-center justify-center hover:scale-110 transition-transform duration-200" onclick="document.getElementById('modernSidebar').classList.remove('-translate-x-full')">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

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

            /* Mobile Responsive */
            @media (max-width: 1024px) {
                #modernSidebar {
                    transform: translateX(-100%);
                }
                #mainContent {
                    margin-left: 0 !important;
                }
            }

            /* Animations */
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
            function initSidebar() {
                const toggle = document.getElementById('sidebarToggle');
                const sidebar = document.getElementById('modernSidebar');
                const mainContent = document.getElementById('mainContent');

                // Load saved state
                const isCompact = localStorage.getItem('sidebarCompact') === 'true';
                if (isCompact) {
                    document.body.classList.add('sidebar-compact');
                } else {
                    document.body.classList.remove('sidebar-compact');
                }

                // Toggle functionality
                if (toggle && !toggle.hasAttribute('data-sidebar-initialized')) {
                    toggle.setAttribute('data-sidebar-initialized', 'true');
                    toggle.addEventListener('click', function() {
                        document.body.classList.toggle('sidebar-compact');
                        const compact = document.body.classList.contains('sidebar-compact');
                        localStorage.setItem('sidebarCompact', compact);
                    });
                }

                // Close mobile menu on navigation
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.addEventListener('click', function() {
                        if (window.innerWidth < 1024) {
                            sidebar.classList.add('-translate-x-full');
                        }
                    });
                });

                // Close user menu on outside click
                document.addEventListener('click', function(e) {
                    const userMenu = document.getElementById('userMenu');
                    const userButton = userMenu?.previousElementSibling;
                    if (userMenu && !userMenu.contains(e.target) && !userButton?.contains(e.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            // Inicializar no carregamento
            document.addEventListener('DOMContentLoaded', initSidebar);

            // Reinicializar após navegação Livewire
            document.addEventListener('livewire:navigated', initSidebar);
        </script>

        @fluxScripts
    </body>
</html>
