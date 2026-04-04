<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        @php
            $mobilePendingSales = 0;
            $mobilePendingMl = 0;
            $mobileBanks = collect();
            try {
                if (auth()->check()) {
                    $mobilePendingSales = \App\Models\Sale::where('user_id', auth()->id())
                        ->where('status', 'pendente')
                        ->count();

                    $mobilePendingMl = \App\Models\MlPublication::where('user_id', auth()->id())
                        ->where('sync_status', 'pending')
                        ->count();

                    $mobileBanks = \App\Models\Bank::where('user_id', auth()->id())
                        ->select('id_bank', 'name')
                        ->orderBy('name')
                        ->limit(8)
                        ->get();
                }
            } catch (\Throwable $e) {
                $mobilePendingSales = 0;
                $mobilePendingMl = 0;
                $mobileBanks = collect();
            }
        @endphp
        <!-- Modern Sidebar with Toggle -->
        <div id="modernSidebar" class="modern-sidebar mobile-sidebar-closed fixed left-0 top-0 h-screen z-50 transition-all duration-300 ease-in-out" style="width: 280px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 1.5px 8px 0 rgba(80, 80, 200, 0.10); border-radius: 0 1.5rem 1.5rem 0;">
            {{-- CSS do sidebar movido para public/assets/css/responsive/sidebar.css --}}
            <!-- Sidebar Container with Glassmorphism -->
            <div class="h-full relative overflow-hidden">
                <!-- Background Blur Layer -->
                <div class="absolute inset-0 bg-white/80 dark:bg-slate-900/90 backdrop-blur-xl border-r border-slate-200/50 dark:border-slate-700/50"></div>

                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 via-transparent to-purple-500/5 pointer-events-none"></div>

                <!-- Sidebar Content -->
                <div class="relative h-full flex flex-col">
                    <!-- Header Section -->
                    <div class="flex items-center justify-between gap-3 p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 transition-all duration-300 hover:scale-105" wire:navigate.hover>
                            <img src="/favicon.svg" alt="{{ config('app.name', 'FlowManager') }}" class="h-10 w-10 shrink-0 object-contain" />
                            <span class="sidebar-text font-black text-xl bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ config('app.name', 'FlowManager') }}</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <div x-data="{ dark: document.documentElement.classList.contains('dark') }">
                                <button
                                    type="button"
                                    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('flowmanager:theme', dark ? 'dark' : 'light'); window.dispatchEvent(new CustomEvent('flowmanager:theme-changed', { detail: { theme: dark ? 'dark' : 'light' } }));"
                                    :aria-label="dark ? 'Ativar modo claro' : 'Ativar modo escuro'"
                                    :title="dark ? 'Ativar modo claro' : 'Ativar modo escuro'"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200/80 bg-white/80 text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                >
                                    <span class="sr-only" x-text="dark ? 'Ativar modo claro' : 'Ativar modo escuro'"></span>
                                    <svg x-cloak x-show="!dark" class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                                    </svg>
                                    <svg x-cloak x-show="dark" class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.326.265-2.59.744-3.74A9.753 9.753 0 103 12.75 9.75 9.75 0 0021.752 15.002z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Toggle Button -->
                            <button id="sidebarToggle" type="button" class="lg:flex hidden w-9 h-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200 group">
                                    <button
                                        type="button"
                                        @click="toggle()"
                                        :aria-expanded="open.toString()"
                                        class="flex w-full items-center justify-between rounded-2xl border border-fuchsia-200/70 bg-fuchsia-50/80 px-3 py-3 text-left transition-all duration-200 hover:border-fuchsia-300 hover:bg-fuchsia-100/80 dark:border-fuchsia-900/70 dark:bg-fuchsia-950/30 dark:hover:border-fuchsia-800 dark:hover:bg-fuchsia-900/30"
                                    >
                            <button type="button" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" onclick="closeMobileSidebar()">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Content -->
                    <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 custom-scrollbar">
                        <!-- Dashboards Section -->
                        <div class="mb-4 sidebar-section-dashboard">
                            <div class="sidebar-text px-3 mb-2">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-500 dark:text-indigo-400">Painel</p>
                            </div>
                            <nav class="space-y-1">
                                <!-- Dashboard Geral (Principal) -->
                                <a href="{{ route('dashboard.index') }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.index') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate.hover>
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
                                    <a href="{{ route('dashboard.cashbook') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.cashbook') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.cashbook') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Dashboard Financeiro</span>
                                        <div class="{{ request()->routeIs('dashboard.cashbook') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('dashboard.products') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.products') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('dashboard.products') ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Dashboard Produtos</span>
                                        <div class="{{ request()->routeIs('dashboard.products') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-purple-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('dashboard.sales') }}" class="relative flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('dashboard.sales') ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-600 dark:text-blue-400 font-semibold' : '' }}" wire:navigate.hover>
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
                        <div class="mb-4 sidebar-section-financeiro" x-data="flowSidebarSection('financeiro', true)" x-init="init()">
                            <button
                                type="button"
                                @click="toggle()"
                                :aria-expanded="open.toString()"
                                class="flex w-full items-center justify-between rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-3 py-3 text-left transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-100/80 dark:border-emerald-900/70 dark:bg-emerald-950/30 dark:hover:border-emerald-800 dark:hover:bg-emerald-900/30"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20">
                                        <i class="bi bi-wallet2 text-base"></i>
                                    </div>
                                    <div class="sidebar-text min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">Financeiro</p>
                                        <p class="truncate text-xs text-emerald-700 dark:text-emerald-300">Caixa, bancos e reservas</p>
                                    </div>
                                </div>
                                <div class="sidebar-text flex items-center gap-2 pl-3">
                                    <span class="rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm dark:bg-slate-900/70 dark:text-emerald-300">Gestão</span>
                                    <i class="bi bi-chevron-down text-xs text-emerald-700 transition-transform duration-200 dark:text-emerald-300" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                class="mt-2"
                            >
                            <nav class="space-y-1">
                                <!-- Bancos com botão + -->
                                <a href="{{ url('banks') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('banks') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate.hover>
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
                                            <a href="{{ route('invoices.index', ['bankId' => $__bank->id_bank]) }}" class="relative flex flex-nowrap items-center gap-1.5 px-2 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ $__isCurrentBank ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                <a href="{{ url('cashbook') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('cashbook') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                <a href="{{ url('cofrinhos') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('cofrinhos*') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                <a href="{{ route('goals.dashboard') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('goals*') ? 'bg-gradient-to-r from-purple-500/10 to-indigo-500/10 dark:from-purple-500/20 dark:to-indigo-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('goals*') ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/30' : '' }}">
                                        <i class="bi bi-bullseye text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Metas e Objetivos</span>
                                    <div class="{{ Request::is('goals*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-purple-500 to-indigo-600 rounded-l-full"></div>
                                </a>


                                <a href="{{ route('daily-habits.dashboard') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('daily-habits*') ? 'bg-gradient-to-r from-indigo-500/10 to-pink-500/10 dark:from-indigo-500/20 dark:to-pink-500/20 text-indigo-600 dark:text-indigo-400 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('daily-habits*') ? 'bg-gradient-to-br from-indigo-500 to-pink-600 text-white shadow-lg shadow-indigo-500/30' : '' }}">
                                        <i class="bi bi-calendar-check text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Hábitos Diários</span>
                                    <div class="{{ Request::is('daily-habits*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-indigo-500 to-pink-600 rounded-l-full"></div>
                                </a>

                                <!-- Achievements/Conquistas -->
                                <a href="{{ route('achievements.index') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-yellow-900 dark:hover:text-yellow-200 hover:translate-x-1 group {{ Request::is('achievements*') ? 'bg-gradient-to-r from-yellow-400/20 to-orange-400/20 dark:from-yellow-600/30 dark:to-orange-600/30 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800 transition-all duration-200 flex-shrink-0 {{ Request::is('achievements*') ? 'bg-gradient-to-br from-yellow-400 to-orange-400 text-white shadow-lg shadow-yellow-400/30' : '' }}">
                                        <i class="bi bi-trophy-fill text-lg"></i>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Conquistas</span>
                                    <div class="{{ Request::is('achievements*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-yellow-400 to-orange-400 rounded-l-full"></div>
                                </a>

                                <a href="{{ route('consortiums.index') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('consortiums*') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/20 dark:to-teal-500/20 text-emerald-600 dark:text-emerald-400 font-semibold' : '' }}" wire:navigate.hover>
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
                        </div>

                        <!-- Vendas e Produtos Section -->
                        <div class="mb-4 sidebar-section-vendas" x-data="flowSidebarSection('vendas', true)" x-init="init()">
                            <button
                                type="button"
                                @click="toggle()"
                                :aria-expanded="open.toString()"
                                class="flex w-full items-center justify-between rounded-2xl border border-violet-200/70 bg-violet-50/80 px-3 py-3 text-left transition-all duration-200 hover:border-violet-300 hover:bg-violet-100/80 dark:border-violet-900/70 dark:bg-violet-950/30 dark:hover:border-violet-800 dark:hover:bg-violet-900/30"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-lg shadow-violet-500/20">
                                        <i class="bi bi-bag-check text-base"></i>
                                    </div>
                                    <div class="sidebar-text min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">Vendas</p>
                                        <p class="truncate text-xs text-violet-700 dark:text-violet-300">CRM, catálogo e operação comercial</p>
                                    </div>
                                </div>
                                <div class="sidebar-text flex items-center gap-2 pl-3">
                                    <span class="rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-violet-700 shadow-sm dark:bg-slate-900/70 dark:text-violet-300">Operação</span>
                                    <i class="bi bi-chevron-down text-xs text-violet-700 transition-transform duration-200 dark:text-violet-300" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                class="mt-2"
                            >
                            <nav class="space-y-1">
                                <a href="{{ url('products') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('products') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                                                <a href="{{ route('products.barcode-scanner') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('products/barcode-scanner') ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 text-indigo-600 dark:text-indigo-400 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ Request::is('products/barcode-scanner') ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h2M4 10h2M4 14h2M4 18h2M8 6h2M8 18h2M12 6h2M12 18h2M16 6h8M16 10h8M16 14h8M16 18h8"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Scanner de Barras</span>
                                    <div class="{{ Request::is('products/barcode-scanner') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-indigo-500 to-purple-600 rounded-l-full"></div>
                                </a>

                                <a href="{{ url('clients') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('clients') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                <a href="{{ url('sales') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('sales') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate.hover>
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

                                <a href="{{ url('categories') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('categories') ? 'bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20 text-purple-600 dark:text-purple-400 font-semibold' : '' }}" wire:navigate.hover>
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

                        <!-- Integrações Section -->
                        <div class="mb-4">
                            <div class="sidebar-text px-3 mb-2">
                                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Integrações</p>
                            </div>
                            <nav class="space-y-1">
                                <!-- Mercado Livre com submenu -->
                                <div x-data="{ mlOpen: {{ Request::is('mercadolivre*') ? 'true' : 'false' }} }">
                                <a href="{{ route('mercadolivre.products') }}"
                                   @click.prevent="mlOpen = !mlOpen; if(!mlOpen) window.location='{{ route('mercadolivre.products') }}'"
                                   class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre*') ? 'bg-gradient-to-r from-yellow-400/20 to-amber-500/20 dark:from-yellow-500/30 dark:to-amber-600/30 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre*') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-lg shadow-yellow-500/30' : '' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Mercado Livre</span>
                                    <!-- Badge de integração -->
                                    <div class="sidebar-text flex items-center gap-1 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-yellow-400/15 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300">Marketplace</span>
                                    </div>
                                    <!-- Chevron accordion -->
                                    <i class="sidebar-text bi text-xs transition-transform duration-200 text-slate-400"
                                       :class="mlOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    <div class="{{ Request::is('mercadolivre*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                </a>

                                <!-- Submenu Mercado Livre (padronizado) -->
                                <div x-show="mlOpen"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="ml-4 space-y-1 border-l-2 border-yellow-200 dark:border-yellow-800 pl-2 mt-1">
                                    <a href="{{ route('mercadolivre.publications') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/publications*') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/products') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                            <i class="bi bi-list-check text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Publicações</span>
                                        <div class="{{ Request::is('mercadolivre/publications*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('mercadolivre.products.publish.create') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/products/publish/create') || Request::is('mercadolivre/products/*/publish') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/products/publish/create') || Request::is('mercadolivre/products/*/publish') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                            <i class="bi bi-plus-square text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Nova publicação</span>
                                        <div class="{{ Request::is('mercadolivre/products/publish/create') || Request::is('mercadolivre/products/*/publish') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('mercadolivre.settings') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/settings') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/settings') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                            <i class="bi bi-gear text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Configurações</span>
                                        <div class="{{ Request::is('mercadolivre/settings') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('mercadolivre.auth.redirect') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('mercadolivre/auth/*') ? 'bg-yellow-400/10 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('mercadolivre/auth/*') ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white shadow-md shadow-yellow-500/30' : '' }}">
                                            <i class="bi bi-plug text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Conexão</span>
                                        <div class="{{ Request::is('mercadolivre/auth/*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-yellow-400 to-amber-600 rounded-l-full"></div>
                                    </a>
                                </div>
                                </div>

                                <!-- Shopee com submenu -->
                                <div x-data="{ shopeeOpen: {{ Request::is('shopee*') ? 'true' : 'false' }} }">
                                <a href="{{ route('shopee.publications') }}"
                                   @click.prevent="shopeeOpen = !shopeeOpen; if(!shopeeOpen) window.location='{{ route('shopee.publications') }}'"
                                   class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('shopee*') ? 'bg-gradient-to-r from-orange-400/20 to-red-500/20 dark:from-orange-500/30 dark:to-red-600/30 text-orange-700 dark:text-orange-300 font-semibold' : '' }}" wire:navigate.hover>
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-orange-100 dark:bg-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('shopee*') ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-lg shadow-orange-500/30' : '' }}">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <span class="sidebar-text flex-1 font-medium truncate">Shopee</span>
                                    <div class="sidebar-text flex items-center gap-1 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-orange-400/15 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300">Marketplace</span>
                                    </div>
                                    <i class="sidebar-text bi text-xs transition-transform duration-200 text-slate-400"
                                       :class="shopeeOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    <div class="{{ Request::is('shopee*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-orange-500 to-red-600 rounded-l-full"></div>
                                </a>

                                  <!-- Submenu Shopee (padronizado) -->
                                <div x-show="shopeeOpen"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="ml-4 space-y-1 border-l-2 border-orange-200 dark:border-orange-800 pl-2 mt-1">

                                    <a href="{{ route('shopee.publications') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('shopee/publications') ? 'bg-orange-400/10 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('shopee/publications') ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Publicações</span>
                                        <div class="{{ Request::is('shopee/publications') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-orange-500 to-red-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('shopee.products.publish.create') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('shopee/products/publish/create') || Request::is('shopee/products/*/publish') ? 'bg-orange-400/10 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('shopee/products/publish/create') || Request::is('shopee/products/*/publish') ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/30' : '' }}">
                                            <i class="bi bi-plus-square text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Nova publicação</span>
                                        <div class="{{ Request::is('shopee/products/publish/create') || Request::is('shopee/products/*/publish') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-orange-500 to-red-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('shopee.settings') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('shopee/settings') ? 'bg-orange-400/10 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('shopee/settings') ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/30' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Configurações</span>
                                        <div class="{{ Request::is('shopee/settings') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-orange-500 to-red-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('shopee.auth.connect') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ Request::is('shopee/auth/*') ? 'bg-orange-400/10 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/50 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-all duration-200 flex-shrink-0 {{ Request::is('shopee/auth/*') ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/30' : '' }}">
                                            <i class="bi bi-plug text-sm"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate text-sm">Conexão</span>
                                        <div class="{{ Request::is('shopee/auth/*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-orange-500 to-red-600 rounded-l-full"></div>
                                    </a>
                                </div>
                                </div>

                            </nav>
                        </div>

                        <!-- Conta e Plano Section -->
                        <div class="mb-4 sidebar-section-account" x-data="flowSidebarSection('account', true)" x-init="init()">
                            <button
                                type="button"
                                @click="toggle()"
                                :aria-expanded="open.toString()"
                                class="flex w-full items-center justify-between rounded-2xl border border-fuchsia-200/70 bg-fuchsia-50/80 px-3 py-3 text-left transition-all duration-200 hover:border-fuchsia-300 hover:bg-fuchsia-100/80 dark:border-fuchsia-900/70 dark:bg-fuchsia-950/30 dark:hover:border-fuchsia-800 dark:hover:bg-fuchsia-900/30"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/20">
                                        <i class="bi bi-person-badge text-base"></i>
                                    </div>
                                    <div class="sidebar-text min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">Conta e Plano</p>
                                        <p class="truncate text-xs text-fuchsia-700 dark:text-fuchsia-300">Equipe, assinatura e acesso pessoal</p>
                                    </div>
                                </div>
                                <div class="sidebar-text flex items-center gap-2 pl-3">
                                    <span class="rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-fuchsia-700 shadow-sm dark:bg-slate-900/70 dark:text-fuchsia-300">Conta</span>
                                    <i class="bi bi-chevron-down text-xs text-fuchsia-700 transition-transform duration-200 dark:text-fuchsia-300" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                class="mt-2"
                            >
                                <nav class="space-y-1">
                                    <a href="{{ route('access.center') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('access.center') ? 'bg-gradient-to-r from-fuchsia-500/10 to-violet-500/10 dark:from-fuchsia-500/20 dark:to-violet-500/20 text-fuchsia-600 dark:text-fuchsia-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('access.center') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/30' : '' }}">
                                            <i class="bi bi-person-lock text-base"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate">Acesso e permissoes</span>
                                        <div class="{{ request()->routeIs('access.center') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('settings.team') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('settings.team') ? 'bg-gradient-to-r from-fuchsia-500/10 to-violet-500/10 dark:from-fuchsia-500/20 dark:to-violet-500/20 text-fuchsia-600 dark:text-fuchsia-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('settings.team') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/30' : '' }}">
                                            <i class="bi bi-people text-base"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate">Equipe</span>
                                        <div class="{{ request()->routeIs('settings.team') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('settings.plan') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('settings.plan') ? 'bg-gradient-to-r from-fuchsia-500/10 to-violet-500/10 dark:from-fuchsia-500/20 dark:to-violet-500/20 text-fuchsia-600 dark:text-fuchsia-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('settings.plan') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/30' : '' }}">
                                            <i class="bi bi-credit-card text-base"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate">Meu plano</span>
                                        <div class="{{ request()->routeIs('settings.plan') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                    </a>

                                    <a href="{{ route('subscription.plans') }}" class="relative flex flex-nowrap items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('subscription.plans') ? 'bg-gradient-to-r from-fuchsia-500/10 to-violet-500/10 dark:from-fuchsia-500/20 dark:to-violet-500/20 text-fuchsia-600 dark:text-fuchsia-400 font-semibold' : '' }}" wire:navigate.hover>
                                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 group-hover:bg-white dark:group-hover:bg-slate-700 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('subscription.plans') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/30' : '' }}">
                                            <i class="bi bi-stars text-base"></i>
                                        </div>
                                        <span class="sidebar-text flex-1 font-medium truncate">Trocar plano</span>
                                        <div class="{{ request()->routeIs('subscription.plans') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                    </a>

                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <div class="ml-4 space-y-1 border-l-2 border-fuchsia-200 dark:border-fuchsia-800 pl-2">
                                            <a href="{{ route('admin.plans.index') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('admin.plans.index') || request()->routeIs('admin.plans.create') || request()->routeIs('admin.plans.edit') ? 'bg-fuchsia-400/10 dark:bg-fuchsia-500/20 text-fuchsia-700 dark:text-fuchsia-300 font-semibold' : '' }}" wire:navigate.hover>
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-fuchsia-100 dark:bg-fuchsia-900/50 group-hover:bg-fuchsia-200 dark:group-hover:bg-fuchsia-800/50 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('admin.plans.index') || request()->routeIs('admin.plans.create') || request()->routeIs('admin.plans.edit') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-md shadow-fuchsia-500/30' : '' }}">
                                                    <i class="bi bi-gem text-sm"></i>
                                                </div>
                                                <span class="sidebar-text flex-1 font-medium truncate text-sm">Planos</span>
                                                <div class="{{ request()->routeIs('admin.plans.index') || request()->routeIs('admin.plans.create') || request()->routeIs('admin.plans.edit') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                            </a>

                                            <a href="{{ route('admin.subscriptions.index') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('admin.subscriptions.*') ? 'bg-fuchsia-400/10 dark:bg-fuchsia-500/20 text-fuchsia-700 dark:text-fuchsia-300 font-semibold' : '' }}" wire:navigate.hover>
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-fuchsia-100 dark:bg-fuchsia-900/50 group-hover:bg-fuchsia-200 dark:group-hover:bg-fuchsia-800/50 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('admin.subscriptions.*') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-md shadow-fuchsia-500/30' : '' }}">
                                                    <i class="bi bi-people text-sm"></i>
                                                </div>
                                                <span class="sidebar-text flex-1 font-medium truncate text-sm">Assinaturas</span>
                                                <div class="{{ request()->routeIs('admin.subscriptions.*') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                            </a>

                                            <a href="{{ route('admin.plans.users') }}" class="relative flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-slate-600 dark:text-slate-300 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/20 hover:text-slate-900 dark:hover:text-white hover:translate-x-1 group {{ request()->routeIs('admin.plans.users') ? 'bg-fuchsia-400/10 dark:bg-fuchsia-500/20 text-fuchsia-700 dark:text-fuchsia-300 font-semibold' : '' }}" wire:navigate.hover>
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-fuchsia-100 dark:bg-fuchsia-900/50 group-hover:bg-fuchsia-200 dark:group-hover:bg-fuchsia-800/50 transition-all duration-200 flex-shrink-0 {{ request()->routeIs('admin.plans.users') ? 'bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-md shadow-fuchsia-500/30' : '' }}">
                                                    <i class="bi bi-person-vcard text-sm"></i>
                                                </div>
                                                <span class="sidebar-text flex-1 font-medium truncate text-sm">Usuarios</span>
                                                <div class="{{ request()->routeIs('admin.plans.users') ? 'block' : 'hidden' }} absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-fuchsia-500 to-violet-600 rounded-l-full"></div>
                                            </a>
                                        </div>
                                    @endif
                                </nav>
                            </div>
                        </div>
                    </div>

                    @php
                        $__sidebarUser = auth()->user();
                        $__sidebarProfileImage = $__sidebarUser?->profile_picture ?: $__sidebarUser?->avatar;
                        $__sidebarProfileImage = $__sidebarProfileImage
                            ? (\Illuminate\Support\Str::startsWith($__sidebarProfileImage, ['http://', 'https://']) ? $__sidebarProfileImage : asset($__sidebarProfileImage))
                            : null;
                    @endphp

                    <!-- Footer Section -->
                    <div class="border-t border-slate-200/50 px-3 pb-3 pt-3 dark:border-slate-700/50" x-data="{ profileOpen: false }">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="sidebar-text">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Perfil</p>
                            </div>
                            @livewire('components.consortium-notifications')
                        </div>

                        <div class="relative">
                            <button @click="profileOpen = !profileOpen" type="button" class="w-full overflow-hidden rounded-[1.55rem] border border-slate-200/70 bg-[radial-gradient(circle_at_top_left,_rgba(96,165,250,0.16),_transparent_38%),linear-gradient(135deg,rgba(255,255,255,0.98),rgba(241,245,249,0.94))] p-3 shadow-[0_18px_38px_-24px_rgba(15,23,42,0.85)] transition-all duration-200 hover:border-slate-300 hover:shadow-[0_20px_40px_-22px_rgba(59,130,246,0.30)] dark:border-slate-700/70 dark:bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.16),_transparent_38%),linear-gradient(135deg,rgba(30,41,59,0.98),rgba(15,23,42,0.97))] dark:hover:border-slate-600 group">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-sm font-bold text-white shadow-lg shadow-blue-500/20 dark:border-slate-700/70">
                                        @if($__sidebarProfileImage)
                                            <img src="{{ $__sidebarProfileImage }}" alt="{{ $__sidebarUser->name }}" class="h-full w-full object-cover" />
                                        @else
                                            {{ $__sidebarUser->initials() }}
                                        @endif
                                    </div>
                                    <div class="sidebar-text min-w-0 flex-1 text-left">
                                        <p class="truncate text-sm font-extrabold text-slate-900 dark:text-white">{{ $__sidebarUser->name }}</p>
                                        <p class="truncate pt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $__sidebarUser->email }}</p>
                                        <div class="flex items-center gap-1.5 pt-1.5">
                                            <span class="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 shadow-sm dark:bg-slate-900/60 dark:text-slate-400">Conta</span>
                                            @if($__sidebarUser->isAdmin())
                                                <span class="inline-flex items-center rounded-full bg-pink-500/12 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.14em] text-pink-600 dark:text-pink-400">Admin</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="sidebar-text flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/70 bg-white/85 text-slate-400 transition-all duration-200 group-hover:border-slate-300 group-hover:text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:group-hover:text-slate-300">
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <div x-cloak x-show="profileOpen" @click.away="profileOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute bottom-[calc(100%+0.75rem)] left-0 right-0 z-40 rounded-[1.55rem] border border-slate-200 bg-white p-3 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
                                <div class="mb-3 flex items-center gap-3 rounded-2xl bg-gradient-to-r from-slate-50 to-blue-50 p-3 dark:from-slate-900 dark:to-slate-800/80">
                                    <div class="relative flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 text-sm font-bold text-white shadow-lg shadow-blue-500/20 dark:border-slate-700/70">
                                        @if($__sidebarProfileImage)
                                            <img src="{{ $__sidebarProfileImage }}" alt="{{ $__sidebarUser->name }}" class="h-full w-full object-cover" />
                                        @else
                                            {{ $__sidebarUser->initials() }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-extrabold text-slate-900 dark:text-white">{{ $__sidebarUser->name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $__sidebarUser->email }}</p>
                                        <p class="pt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Central da conta</p>
                                    </div>
                                </div>

                                <div class="mb-3 border-t border-slate-200/70 pt-3 dark:border-slate-700/70">
                                    <p class="mb-2 px-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Acessos rápidos</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('settings.profile') }}" class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-700/70" wire:navigate.hover>
                                            <i class="bi bi-person-circle text-sm text-slate-500 dark:text-slate-400"></i>
                                            <span>Perfil</span>
                                        </a>
                                        <a href="{{ route('settings.team') }}" class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-700/70" wire:navigate.hover>
                                            <i class="bi bi-people text-sm text-emerald-500"></i>
                                            <span>Equipe</span>
                                        </a>
                                        <a href="{{ route('settings.devices') }}" class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-700/70" wire:navigate.hover>
                                            <i class="bi bi-laptop text-sm text-indigo-500"></i>
                                            <span>Dispositivos</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="mb-3 border-t border-slate-200/70 pt-3 dark:border-slate-700/70">
                                    <p class="mb-2 px-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Recursos</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('settings.connections') }}" class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-700/70" wire:navigate.hover>
                                            <i class="bi bi-link-45deg text-sm text-slate-500 dark:text-slate-400"></i>
                                            <span>Conexoes</span>
                                        </a>
                                        <a href="{{ route('settings.notifications') }}" class="flex items-center gap-2 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-slate-700/70" wire:navigate.hover>
                                            <i class="bi bi-bell text-sm text-slate-500 dark:text-slate-400"></i>
                                            <span>Alertas</span>
                                        </a>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-600 transition-all hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/30">
                                        <i class="bi bi-box-arrow-right text-base"></i>
                                        <span>Sair da conta</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobileSidebarBackdrop" class="mobile-sidebar-backdrop lg:hidden" onclick="closeMobileSidebar()" aria-hidden="true"></div>

        <!-- Mobile Bottom Tab Bar (premium) -->
        <nav class="mobile-bottom-tabbar" role="navigation" aria-label="Navegação principal">

            <!-- Inicio -->
            <a href="{{ route('dashboard.index') }}" wire:navigate.hover
               class="mobile-tab-item {{ request()->routeIs('dashboard.*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"></path>
                    </svg>
                </div>
                <span>Inicio</span>
            </a>

            <!-- Vendas -->
            <a href="{{ url('sales') }}" wire:navigate.hover
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

            <!-- Clientes: visível somente no iPad (768px+) via tab-ipad-only -->
            <a href="{{ url('clients') }}" wire:navigate.hover
               class="mobile-tab-item tab-ipad-only {{ Request::is('clients*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span>Clientes</span>
            </a>

            <!-- FAB Central: Ações Rápidas -->
            <button type="button" class="mobile-tab-item mobile-tab-fab" id="tabFabBtn" onclick="openFabSheet()" aria-label="Ações rápidas">
                <div class="fab-circle">
                    <img src="{{ asset('assets/img/Criar.svg') }}" alt="Criar" style="display:block; width:5.8rem; height:5.8rem; object-fit:contain; border-radius:50%;">
                </div>
                <span class="fab-label">Criar</span>
            </button>

            <!-- Produtos -->
            <a href="{{ url('products') }}" wire:navigate.hover
               class="mobile-tab-item {{ Request::is('products*') ? 'is-active' : '' }}">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span>Produtos</span>
            </a>

            <!-- ML: visível somente no iPad (768px+) -->
            <a href="{{ route('mercadolivre.products') }}" wire:navigate.hover
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

            <!-- Mais -->
            <button type="button" class="mobile-tab-item mobile-tab-more" id="tabMoreBtn" onclick="openMoreSheet()" aria-label="Mais opções">
                <div class="tab-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h6"></path>
                    </svg>
                </div>
                <span>Mais</span>
            </button>
        </nav>

        <!-- FAB Sheet: Criar & Importar — Cards por Área -->
        <div id="mobileFabSheet" class="mobile-action-sheet lg:hidden" aria-hidden="true">
            <div class="mobile-sheet-backdrop" onclick="closeFabSheet()"></div>
            <div class="mobile-sheet-panel fab-sheet">
                <div class="mobile-sheet-handle"></div>

                {{-- ─── Header ─── --}}
                <div class="fab-sheet-header">
                    <div class="fab-sheet-header-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="fab-sheet-title">Criar &amp; Importar</p>
                        <p class="fab-sheet-subtitle">Organize todas as áreas do sistema</p>
                    </div>
                    <button type="button" class="fab-sheet-close-btn" onclick="closeFabSheet()" aria-label="Fechar">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- ─── AREA CARDS GRID (2 colunas, moderno) ─── --}}
                <div class="fab-areas-grid">

                    {{-- Card 1: Vendas & Clientes --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-emerald">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#10b981,#059669)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span>Vendas &amp; Clientes</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-2">
                            <a href="{{ route('sales.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <span>Nova Venda</span>
                            </a>
                            <a href="{{ route('clients.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </div>
                                <span>Novo Cliente</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 2: Produtos --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-amber">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <span>Produtos</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-3">
                            <a href="{{ route('products.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span>Novo</span>
                            </a>
                            <a href="{{ route('products.kit.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#fb923c,#f97316)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <span>Kit</span>
                            </a>
                            <a href="{{ route('products.upload') }}" class="fab-area-action fab-area-action-upload" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <span>Upload CSV</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 3: Livro Caixa --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-cyan">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <span>Livro Caixa</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-2">
                            <a href="{{ route('cashbook.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span>Nova Entrada</span>
                            </a>
                            <a href="{{ route('cashbook.upload.minimal') }}" class="fab-area-action fab-area-action-upload" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#22d3ee,#06b6d4)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <span>Upload CSV</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 4: Organização --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-pink">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#ec4899,#db2777)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <span>Organização</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-2">
                            <a href="{{ route('categories.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                                <span>Categoria</span>
                            </a>
                            <a href="{{ route('banks.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                </div>
                                <span>Banco</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 5: Reservas --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-indigo">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span>Reservas</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-2">
                            <a href="{{ route('cofrinhos.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <span>Cofrinho</span>
                            </a>
                            <a href="{{ route('consortiums.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#14b8a6,#0d9488)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <span>Consórcio</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 6: Pessoal & ML --}}
                    <div class="fab-area-card">
                        <div class="fab-area-header fab-acolor-lime">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#84cc16,#65a30d)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <span>Pessoal &amp; ML</span>
                        </div>
                        <div class="fab-area-body fab-area-body-cols-2">
                            <a href="{{ route('daily-habits.create') }}" class="fab-area-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#84cc16,#65a30d)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <span>Hábito</span>
                            </a>
                            <a href="{{ route('mercadolivre.products.publish.create') }}" class="fab-area-action fab-area-ml-action" wire:navigate.hover onclick="closeFabSheet()">
                                <div class="fab-area-action-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                </div>
                                <span>Publicar ML</span>
                            </a>
                        </div>
                    </div>

                </div>

                {{-- ─── FATURAS POR BANCO (full-width, dinâmico) ─── --}}
                @if($mobileBanks->count() > 0)
                    @php
                        $bankGrads = [
                            ['#3b82f6','#1d4ed8'],
                            ['#8b5cf6','#7c3aed'],
                            ['#0ea5e9','#0284c7'],
                            ['#6366f1','#4f46e5'],
                            ['#10b981','#059669'],
                            ['#f59e0b','#d97706'],
                            ['#14b8a6','#0d9488'],
                            ['#ec4899','#db2777'],
                        ];
                    @endphp
                    <div class="fab-area-card fab-area-full" style="margin-top:0.6rem">
                        <div class="fab-area-header fab-acolor-blue">
                            <div class="fab-area-icon-wrap" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <span>Faturas por Banco</span>
                        </div>
                        <div class="fab-area-banks-grid">
                            @foreach($mobileBanks as $bank)
                                @php $grad = $bankGrads[$loop->index % count($bankGrads)]; @endphp
                                <div class="fab-area-bank-card">
                                    <span class="fab-area-bank-name">{{ Str::limit($bank->name, 13) }}</span>
                                    <div class="fab-area-bank-actions">
                                        <a href="{{ route('invoices.create', $bank->id_bank) }}" class="fab-bank-btn fab-bank-btn-create" wire:navigate.hover onclick="closeFabSheet()" title="Criar Fatura"
                                           style="background:linear-gradient(135deg,{{ $grad[0] }},{{ $grad[1] }})">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </a>
                                        <a href="{{ route('invoices.upload', $bank->id_bank) }}" class="fab-bank-btn fab-bank-btn-upload" wire:navigate.hover onclick="closeFabSheet()" title="Upload Fatura">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- Mais Sheet: Navegação Completa (espelho da sidebar) -->
        <div id="mobileMoreSheet" class="mobile-action-sheet lg:hidden" aria-hidden="true">
            <div class="mobile-sheet-backdrop" onclick="closeMoreSheet()"></div>
            <div class="mobile-sheet-panel">
                <div class="mobile-sheet-handle"></div>
                <button type="button" class="fab-sheet-close-btn fab-sheet-close-absolute" onclick="closeMoreSheet()" aria-label="Fechar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="more-sheet-header">
                    <div class="more-sheet-header-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 7h16M4 12h10M4 17h7"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="more-sheet-title">Explorar menu</p>
                        <p class="more-sheet-subtitle">Acesse módulos, atalhos e painéis do app</p>
                    </div>
                </div>

                {{-- ─── PERFIL DO USUÁRIO ─── --}}
                @php
                    $__mobileProfileImage = auth()->user()?->profile_picture ?: auth()->user()?->avatar;
                    $__mobileProfileImage = $__mobileProfileImage
                        ? (\Illuminate\Support\Str::startsWith($__mobileProfileImage, ['http://', 'https://']) ? $__mobileProfileImage : asset($__mobileProfileImage))
                        : null;
                @endphp
                <div class="more-sheet-profile">
                    <div class="more-sheet-avatar {{ $__mobileProfileImage ? 'has-photo' : '' }}">
                        @if($__mobileProfileImage)
                            <img src="{{ $__mobileProfileImage }}" alt="{{ auth()->user()->name }}" class="more-sheet-avatar-image" />
                        @else
                            {{ auth()->user()->initials() }}
                        @endif
                    </div>
                    <div class="more-sheet-user-info">
                        <p class="more-sheet-user-name">{{ auth()->user()->name }}</p>
                        <p class="more-sheet-user-email">{{ auth()->user()->email }}</p>
                        <div class="more-sheet-user-meta">
                            <span class="more-sheet-user-chip">Conta</span>
                            @if(auth()->user()->isAdmin())
                                <span class="more-sheet-user-chip is-admin">Admin</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('settings.profile') }}" class="more-sheet-settings-btn" wire:navigate.hover onclick="closeMoreSheet()" title="Configurações de perfil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>
                </div>

                <div class="more-sheet-account-panel">
                    <div class="more-sheet-account-heading">
                        <span>Central da conta</span>
                        <span class="more-sheet-account-caption">Acessos essenciais</span>
                    </div>
                    <div class="more-sheet-account-grid">
                        <a href="{{ route('settings.profile') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                            <span class="more-sheet-account-icon is-slate"><i class="bi bi-person-circle"></i></span>
                            <span class="more-sheet-account-copy">
                                <strong>Perfil</strong>
                                <small>Ajustes pessoais</small>
                            </span>
                        </a>
                        <a href="{{ route('access.center') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                            <span class="more-sheet-account-icon is-sky"><i class="bi bi-person-badge"></i></span>
                            <span class="more-sheet-account-copy">
                                <strong>Meu acesso</strong>
                                <small>Credenciais e uso</small>
                            </span>
                        </a>
                        <a href="{{ route('settings.team') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                            <span class="more-sheet-account-icon is-emerald"><i class="bi bi-people"></i></span>
                            <span class="more-sheet-account-copy">
                                <strong>Equipe</strong>
                                <small>Permissoes e membros</small>
                            </span>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.plans.index') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-pink"><i class="bi bi-shield-check"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Planos</strong>
                                    <small>Camadas e oferta</small>
                                </span>
                            </a>
                            <a href="{{ route('admin.subscriptions.index') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-violet"><i class="bi bi-people"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Assinaturas</strong>
                                    <small>Gestão ativa</small>
                                </span>
                            </a>
                            <a href="{{ route('admin.plans.users') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-indigo"><i class="bi bi-person-vcard"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Usuarios</strong>
                                    <small>Acesso e permissoes</small>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('settings.plan') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-violet"><i class="bi bi-credit-card"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Meu plano</strong>
                                    <small>Resumo e cobrança</small>
                                </span>
                            </a>
                            <a href="{{ route('subscription.plans') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-pink"><i class="bi bi-stars"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Trocar plano</strong>
                                    <small>Upgrade e catalogo</small>
                                </span>
                            </a>
                            <a href="{{ route('settings.devices') }}" class="more-sheet-account-link" wire:navigate.hover onclick="closeMoreSheet()">
                                <span class="more-sheet-account-icon is-indigo"><i class="bi bi-laptop"></i></span>
                                <span class="more-sheet-account-copy">
                                    <strong>Dispositivos</strong>
                                    <small>Sessões conectadas</small>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- ─── PRINCIPAL ─── --}}
                <p class="mobile-sheet-section-label">Principal</p>
                <div class="more-sheet-app-grid">
                    <a href="{{ route('dashboard.index') }}" class="more-app-card {{ request()->routeIs('dashboard.index') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"></path></svg>
                        </div>
                        <span class="more-app-label">Dashboard</span>
                    </a>
                    <a href="{{ url('sales') }}" class="more-app-card {{ Request::is('sales*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Vendas</span>
                        @if($mobilePendingSales > 0)
                            <span class="more-app-badge">{{ $mobilePendingSales > 99 ? '99+' : $mobilePendingSales }}</span>
                        @endif
                    </a>
                    <a href="{{ route('clients.index') }}" class="more-app-card {{ Request::is('clients*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Clientes</span>
                    </a>
                    <a href="{{ url('products') }}" class="more-app-card {{ Request::is('products*') && !Request::is('products/barcode-scanner') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <span class="more-app-label">Produtos</span>
                    </a>
                    <a href="{{ route('products.barcode-scanner') }}" class="more-app-card {{ Request::is('products/barcode-scanner') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h2M4 10h2M4 14h2M4 18h2M8 6h2M8 18h2M12 6h2M12 18h2M16 6h8M16 10h8M16 14h8M16 18h8"></path></svg>
                        </div>
                        <span class="more-app-label">Scanner</span>
                    </a>
                    <a href="{{ url('categories') }}" class="more-app-card {{ Request::is('categories*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <span class="more-app-label">Categorias</span>
                    </a>
                </div>

                {{-- ─── FINANCEIRO ─── --}}
                <p class="mobile-sheet-section-label">Financeiro</p>
                <div class="more-sheet-app-grid">
                    <a href="{{ url('cashbook') }}" class="more-app-card {{ Request::is('cashbook*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <span class="more-app-label">Livro Caixa</span>
                    </a>
                    <a href="{{ url('banks') }}" class="more-app-card {{ Request::is('banks*') || Request::is('invoices*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                        </div>
                        <span class="more-app-label">Bancos & Faturas</span>
                    </a>
                    <a href="{{ url('cofrinhos') }}" class="more-app-card {{ Request::is('cofrinhos*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Cofrinhos</span>
                    </a>
                    <a href="{{ route('consortiums.index') }}" class="more-app-card {{ Request::is('consortiums*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#14b8a6,#0d9488)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Consórcios</span>
                    </a>
                    <a href="{{ route('goals.dashboard') }}" class="more-app-card {{ Request::is('goals*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#a855f7,#9333ea)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM12 2v1m0 18v1M4.22 4.22l.707.707M18.364 18.364l.707.707M2 12h1m18 0h1M4.22 19.778l.707-.707M18.364 5.636l.707-.707"></path></svg>
                        </div>
                        <span class="more-app-label">Metas</span>
                    </a>
                    <a href="{{ route('daily-habits.dashboard') }}" class="more-app-card {{ Request::is('daily-habits*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="more-app-label">Hábitos Diários</span>
                    </a>
                    <a href="{{ route('achievements.index') }}" class="more-app-card {{ Request::is('achievements*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#eab308,#ca8a04)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        </div>
                        <span class="more-app-label">Conquistas</span>
                    </a>
                </div>

                {{-- ─── MERCADO LIVRE ─── --}}
                <div class="flex items-center justify-between gap-3">
                    <p class="more-sheet-ml-label">Mercado Livre</p>
                    <span class="rounded-full bg-yellow-400/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300">Marketplace</span>
                </div>
                <div class="more-sheet-app-grid more-ml-grid">
                    <a href="{{ route('mercadolivre.publications') }}" class="more-app-card {{ Request::is('mercadolivre/publications*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <span class="more-app-label">Publicações</span>
                    </a>
                    <a href="{{ route('mercadolivre.products.publish.create') }}" class="more-app-card {{ Request::is('mercadolivre/products/publish/create') || Request::is('mercadolivre/products/*/publish') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#fb923c,#f59e0b)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="more-app-label">Nova publicação</span>
                    </a>
                    <a href="{{ route('mercadolivre.settings') }}" class="more-app-card {{ Request::is('mercadolivre/settings*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Configurações</span>
                    </a>
                    <a href="{{ route('mercadolivre.auth.redirect') }}" class="more-app-card {{ Request::is('mercadolivre/auth/*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#14b8a6,#0f766e)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Conexão</span>
                    </a>
                </div>

                {{-- ─── SHOPEE ─── --}}
                <div class="flex items-center justify-between gap-3">
                    <p class="more-sheet-ml-label">Shopee</p>
                    <span class="rounded-full bg-orange-400/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-orange-700 dark:bg-orange-500/20 dark:text-orange-300">Marketplace</span>
                </div>
                <div class="more-sheet-app-grid more-ml-grid">
                    <a href="{{ route('shopee.publications') }}" class="more-app-card {{ Request::is('shopee/publications*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#EE4D2D,#FF6633)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <span class="more-app-label">Publicações</span>
                    </a>
                    <a href="{{ route('shopee.products.publish.create') }}" class="more-app-card {{ Request::is('shopee/products/publish/create') || Request::is('shopee/products/*/publish') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#fb923c,#f97316)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="more-app-label">Nova publicação</span>
                    </a>
                    <a href="{{ route('shopee.settings') }}" class="more-app-card {{ Request::is('shopee/settings*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#c2410c,#EE4D2D)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Configurações</span>
                    </a>
                    <a href="{{ route('shopee.auth.connect') }}" class="more-app-card {{ Request::is('shopee/auth/*') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Conexão</span>
                    </a>
                </div>

                {{-- ─── DASHBOARDS ─── --}}
                <p class="mobile-sheet-section-label">Dashboards</p>
                <div class="more-sheet-app-grid">
                    <a href="{{ route('dashboard.cashbook') }}" class="more-app-card {{ request()->routeIs('dashboard.cashbook') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <span class="more-app-label">Financeiro</span>
                    </a>
                    <a href="{{ route('dashboard.products') }}" class="more-app-card {{ request()->routeIs('dashboard.products') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <span class="more-app-label">Produtos</span>
                    </a>
                    <a href="{{ route('dashboard.sales') }}" class="more-app-card {{ request()->routeIs('dashboard.sales') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Vendas</span>
                    </a>
                    <a href="{{ route('dashboard.clients') }}" class="more-app-card {{ request()->routeIs('dashboard.clients') ? 'is-active' : '' }}" wire:navigate.hover onclick="closeMoreSheet()">
                        <div class="more-app-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="more-app-label">Clientes</span>
                    </a>
                </div>

                {{-- ─── LOGOUT ─── --}}
                <div class="mobile-sheet-divider" style="margin: 0.9rem 0 0.4rem"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="more-sheet-logout-btn">
                        <div class="mobile-sheet-nav-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </div>
                        Sair da conta
                    </button>
                </form>

            </div>
        </div>

        <!-- Main Content Area -->
        <div id="mainContent" class="transition-all duration-300 ease-in-out">
            {{ $slot }}
        </div>

        {{-- CSS do tab bar e action sheets movido para public/assets/css/responsive/tabbar.css --}}

        <!-- Toggle Script -->
        <script>
            window.TABLET_NAV_PREF_KEY = window.TABLET_NAV_PREF_KEY || 'flowmanager:tablet-nav-mode';
            var TABLET_NAV_PREF_KEY = window.TABLET_NAV_PREF_KEY;

            function getTabletNavMode() {
                const mode = localStorage.getItem(TABLET_NAV_PREF_KEY);
                return mode === 'tabbar' ? 'tabbar' : 'sidebar';
            }

            function isTabletPortrait() {
                return window.innerWidth >= 768 && window.innerWidth <= 1024;
            }

            function isTabletLandscape() {
                // pointer: coarse distingue iPads (touch) de notebooks (mouse) no mesmo range de largura
                return window.matchMedia('(min-width: 1024px) and (max-width: 1366px) and (orientation: landscape) and (pointer: coarse)').matches;
            }

            function isDesktopOrLarger() {
                return window.innerWidth >= 1367;
            }

            function applyTabletNavMode() {
                // Não interferir enquanto um sheet de ações está aberto
                if (_isAnySheetOpen()) return;

                const sidebar = document.getElementById('modernSidebar');
                const mode = getTabletNavMode();

                if (isTabletPortrait() || isTabletLandscape()) {
                    // iPad portrait + landscape: TabBar sempre ativa, sidebar somente como drawer
                    // Remove classes tablet-nav-* se houver (CSS media query cuida diretamente)
                    if (document.body.classList.contains('tablet-nav-sidebar') || document.body.classList.contains('tablet-nav-tabbar')) {
                        document.body.classList.remove('tablet-nav-sidebar', 'tablet-nav-tabbar');
                    }
                    sidebar?.classList.add('mobile-sidebar-closed');

                } else if (isDesktopOrLarger()) {
                    // Desktop: aplica preferência do usuário
                    // Evita remover+readicionar a mesma classe (causa flash de layout)
                    const targetClass = mode === 'tabbar' ? 'tablet-nav-tabbar' : 'tablet-nav-sidebar';
                    if (!document.body.classList.contains(targetClass)) {
                        document.body.classList.remove('tablet-nav-sidebar', 'tablet-nav-tabbar');
                        document.body.classList.add(targetClass);
                    }

                    if (mode === 'tabbar') {
                        sidebar?.classList.add('mobile-sidebar-closed');
                    } else {
                        sidebar?.classList.remove('mobile-sidebar-closed');
                    }
                } else {
                    // Mobile puro (<768px): sidebar como drawer, tabbar visível
                    if (document.body.classList.contains('tablet-nav-sidebar') || document.body.classList.contains('tablet-nav-tabbar')) {
                        document.body.classList.remove('tablet-nav-sidebar', 'tablet-nav-tabbar');
                    }
                    sidebar?.classList.add('mobile-sidebar-closed');
                }
            }

            function openMobileSidebar() {
                const sidebar = document.getElementById('modernSidebar');
                if (!sidebar) return;

                // Desktop com modo sidebar: sidebar já é fixa, não vira overlay
                if (isDesktopOrLarger() && getTabletNavMode() === 'sidebar') {
                    sidebar.classList.remove('mobile-sidebar-closed');
                    return;
                }

                sidebar.classList.remove('mobile-sidebar-closed');
                document.body.classList.add('overflow-hidden');
            }

            function closeMobileSidebar() {
                const sidebar = document.getElementById('modernSidebar');
                if (!sidebar) return;

                // Desktop com modo sidebar: não fecha (sidebar é fixa)
                if (isDesktopOrLarger() && getTabletNavMode() === 'sidebar') {
                    return;
                }

                sidebar.classList.add('mobile-sidebar-closed');
                document.body.classList.remove('overflow-hidden'); // ← corrige iOS fixed bug
            }

            function _tabbar() {
                return document.querySelector('.mobile-bottom-tabbar');
            }

            function getSidebarSectionStates() {
                try {
                    return JSON.parse(localStorage.getItem('flowmanager:sidebar-sections') || '{}');
                } catch (error) {
                    return {};
                }
            }

            function setSidebarSectionState(key, value) {
                const states = getSidebarSectionStates();
                states[key] = value;
                localStorage.setItem('flowmanager:sidebar-sections', JSON.stringify(states));
            }

            window.flowSidebarSection = function(key, defaultOpen = true) {
                return {
                    open: defaultOpen,
                    init() {
                        const states = getSidebarSectionStates();
                        if (typeof states[key] === 'boolean') {
                            this.open = states[key];
                        }
                    },
                    toggle() {
                        this.open = !this.open;
                        setSidebarSectionState(key, this.open);
                    },
                };
            };

            function _isAnySheetOpen() {
                return document.getElementById('mobileFabSheet')?.classList.contains('is-open')
                    || document.getElementById('mobileMoreSheet')?.classList.contains('is-open');
            }

            function openFabSheet() {
                const sheet = document.getElementById('mobileFabSheet');
                if (!sheet) return;
                closeMobileSidebar(); // fecha sidebar antes — evita z-index conflict
                sheet.classList.add('is-open');
                sheet.setAttribute('aria-hidden', 'false');
                document.getElementById('tabFabBtn')?.classList.add('fab-sheet-open');
            }

            function closeFabSheet() {
                const sheet = document.getElementById('mobileFabSheet');
                if (!sheet) return;
                sheet.classList.remove('is-open');
                sheet.setAttribute('aria-hidden', 'true');
                document.getElementById('tabFabBtn')?.classList.remove('fab-sheet-open');
            }

            function openMoreSheet() {
                const sheet = document.getElementById('mobileMoreSheet');
                if (!sheet) return;
                closeMobileSidebar(); // fecha sidebar antes
                sheet.classList.add('is-open');
                sheet.setAttribute('aria-hidden', 'false');
                document.getElementById('tabMoreBtn')?.classList.add('more-sheet-open');
            }

            function closeMoreSheet() {
                const sheet = document.getElementById('mobileMoreSheet');
                if (!sheet) return;
                sheet.classList.remove('is-open');
                sheet.setAttribute('aria-hidden', 'true');
                document.getElementById('tabMoreBtn')?.classList.remove('more-sheet-open');
            }

            function initSidebar() {
                if (window.__flowmanagerSidebarInitialized) {
                    applyTabletNavMode();
                    return;
                }

                window.__flowmanagerSidebarInitialized = true;

                const toggle = document.getElementById('sidebarToggle');
                const sidebar = document.getElementById('modernSidebar');

                applyTabletNavMode();

                // Load saved compact state (desktop only — > 1366px)
                const isCompact = localStorage.getItem('sidebarCompact') === 'true';
                if (isCompact && isDesktopOrLarger()) {
                    if (getTabletNavMode() === 'sidebar') {
                        document.body.classList.add('sidebar-compact');
                    }
                } else {
                    document.body.classList.remove('sidebar-compact');
                }

                // Close any open sheets on navigation
                closeFabSheet();
                closeMoreSheet();
                document.getElementById('tabFabBtn')?.classList.remove('fab-sheet-open');
                document.getElementById('tabMoreBtn')?.classList.remove('more-sheet-open');

                // Desktop toggle functionality (apenas quando sidebar está ativa)
                if (toggle && !toggle.hasAttribute('data-sidebar-initialized')) {
                    toggle.setAttribute('data-sidebar-initialized', 'true');
                    toggle.addEventListener('click', function() {
                        document.body.classList.toggle('sidebar-compact');
                        const compact = document.body.classList.contains('sidebar-compact');
                        localStorage.setItem('sidebarCompact', compact);
                    });
                }

                if (!document.body.hasAttribute('data-sidebar-nav-bound')) {
                    document.body.setAttribute('data-sidebar-nav-bound', 'true');
                    document.addEventListener('click', function(e) {
                        const sidebarLink = e.target.closest('#modernSidebar a[href]');

                        if (!sidebarLink || sidebarLink.closest('#userMenu')) {
                            return;
                        }

                        if (window.innerWidth < 1024 || isTabletPortrait() || isTabletLandscape()) {
                            closeMobileSidebar();
                        }
                    });
                }

                // Close user dropdown on outside click
                document.addEventListener('click', function(e) {
                    const userMenu = document.getElementById('userMenu');
                    const userButton = userMenu?.previousElementSibling;
                    if (userMenu && !userMenu.contains(e.target) && !userButton?.contains(e.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            window.addEventListener('resize', applyTabletNavMode);
            window.addEventListener('orientationchange', function() {
                setTimeout(applyTabletNavMode, 100); // aguarda orientação estabilizar
            });
            window.addEventListener('flowmanager:tablet-nav-mode-changed', function() {
                applyTabletNavMode();
            });

            document.addEventListener('DOMContentLoaded', initSidebar);
            document.addEventListener('livewire:navigated', initSidebar);
            // Fechar sheets ANTES da navegação (garante que não persistam na próxima página)
            document.addEventListener('livewire:navigate.hover', function() {
                closeFabSheet();
                closeMoreSheet();
            });
        </script>

        @fluxScripts
    </body>
</html>
