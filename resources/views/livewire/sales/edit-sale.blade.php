<div x-data="{ currentStep: $wire.currentStep, init() { window.addEventListener('gotoStep', e => { this.currentStep = e.detail; $wire.set('currentStep', e.detail); }); $watch('currentStep', v => $wire.set('currentStep', v)); } }" x-init="init()" class="edit-sale-page mobile-393-base w-full" style="overflow-x:clip">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/edit-sale.css') }}">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-sale-ultrawide.css') }}">

    <!-- Header Sticky (mesmo estilo do Create) -->
    <div class="create-sale-sticky-header">
    <x-sales-header
        title="Editar Venda #{{ $sale->id }}"
        description="Atualize as informações da venda seguindo os passos"
        :back-route="route('sales.show', $sale->id)"
        :current-step="$currentStep"
        :steps="[
            [
                'title' => 'Produtos',
                'description' => 'Selecione itens e quantidades',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Finalizar',
                'description' => 'Revisar e salvar alterações',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-green-500 to-emerald-500'
            ]
        ]">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('sales.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-shopping-cart mr-1"></i>Vendas
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Editar Venda</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            @php
                $canProceed = count($selectedProducts) > 0 && $client_id;
                $tooltip = 'Ir para o resumo da venda';
                if (!$canProceed) {
                    if (empty($client_id) && count($selectedProducts) === 0) {
                        $tooltip = 'Selecione um cliente e adicione produtos para poder continuar.';
                    } elseif (empty($client_id)) {
                        $tooltip = 'Selecione um cliente para poder continuar.';
                    } elseif (count($selectedProducts) === 0) {
                        $tooltip = 'Adicione ao menos um produto para poder continuar.';
                    }
                }
            @endphp

            <button
                type="button"
                @if($canProceed)
                    @click="currentStep = 2; $wire.set('currentStep', 2)"
                @endif
                title="{{ $tooltip }}"
                @if(!$canProceed) disabled @endif
                class="create-header-next-btn group relative inline-flex items-center justify-center rounded-lg font-semibold tracking-wide text-white transition-all duration-300
                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-900
                    {{ $canProceed
                        ? 'bg-black/20 dark:bg-white/10 backdrop-blur-md border border-white/20 shadow-lg shadow-indigo-500/20 hover:bg-gradient-to-r from-indigo-500 to-purple-600'
                        : 'bg-slate-400/50 dark:bg-slate-700/50 cursor-not-allowed opacity-60'
                    }}"
            >
                <span class="create-header-next-content">
                    <span class="create-header-next-copy">
                        <span class="create-header-next-label">Ir para Resumo</span>
                        <span class="create-header-next-meta">{{ $canProceed ? 'Revisar e salvar alterações' : 'Complete cliente e produtos para continuar' }}</span>
                    </span>
                    <span class="create-header-next-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </span>
                </span>
            </button>
        </x-slot>
    </x-sales-header>
    </div>{{-- /.create-sale-sticky-header --}}

    <!-- Conteúdo Principal -->
    <div class="">
        <form wire:submit.prevent="update" class="">
            <div class="">

                <!-- Step 1: Produtos - Layout Split 3/4 e 1/4 (igual ao Create) -->
                <div x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full flex flex-col lg:flex-row gap-4 edit-sale-step1-shell">

                    <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
                    <div class="w-full lg:flex-1 lg:min-w-0 flex flex-col edit-sale-products-pane">
                        <!-- Header com Controles -->
                        <div class="p-2">
                            <div class="flex flex-row items-center gap-2 md:gap-4 edit-sale-products-controls">
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-search text-gray-400"></i>
                                        </div>
                                        <input type="text"
                                            wire:model.live.debounce.300ms="searchTerm"
                                            placeholder="Pesquisar produtos por nome ou código..."
                                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                                    </div>
                                </div>
                                <div class="flex items-center shrink-0">
                                    <label class="toggle-filter">
                                        <input type="checkbox"
                                               wire:model.live="showOnlySelected"
                                               class="toggle-filter-input">
                                        <span class="toggle-filter-track">
                                            <span class="toggle-filter-thumb"></span>
                                        </span>
                                        <span class="toggle-filter-text text-gray-700 dark:text-gray-300 font-medium">
                                            Selecionados
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de Produtos com Scroll -->
                        <div class="flex-1 p-2 sm:p-3 overflow-y-auto min-h-0">
                            @if($this->getFilteredProducts()->isEmpty())
                            <div class="flex flex-col items-center justify-center h-full">
                                <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8v2m0 4h.01M21 21l-5-5m5 5v-4a1 1 0 00-1-1h-4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    @if($showOnlySelected && empty($selectedProducts))
                                    Nenhum produto selecionado
                                    @elseif($searchTerm)
                                    Nenhum produto encontrado
                                    @else
                                    Nenhum produto disponível
                                    @endif
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">
                                    @if($showOnlySelected && empty($selectedProducts))
                                    Selecione alguns produtos para visualizá-los aqui
                                    @elseif($searchTerm)
                                    Tente pesquisar com outros termos
                                    @else
                                    Cadastre produtos para começar a vender
                                    @endif
                                </p>
                            </div>
                            @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 products-step-grid products-mobile-compact-grid">
                                @foreach($this->getFilteredProducts() as $product)
                                @php
                                    $isSelected = collect($selectedProducts)->contains(function($selected) use ($product) {
                                        return $selected['product_id'] == $product->id;
                                    });
                                @endphp
                                <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                                    wire:click="toggleProduct({{ $product->id }})"
                                    wire:key="product-{{ $product->id }}">

                                    <div class="btn-action-group flex gap-2">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                    {{ $isSelected
                                                        ? 'bg-purple-600 border-purple-600 text-white'
                                                        : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-purple-400 dark:hover:border-purple-500' }}">
                                            @if($isSelected)
                                            <i class="bi bi-check text-sm"></i>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="product-img-area">
                                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                            alt="{{ $product->name }}"
                                            class="product-img">
                                        @if($product->stock_quantity <= 5)
                                        <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Baixo estoque
                                        </div>
                                        @endif
                                        <span class="badge-product-code">
                                            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                        </span>
                                        <span class="badge-quantity">
                                            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                        </span>
                                        @if($product->category)
                                        <div class="category-icon-wrapper">
                                            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="card-body">
                                        <div class="product-title" title="{{ $product->name }}">
                                            {{ ucwords($product->name) }}
                                        </div>
                                        <div class="price-area">
                                            <span class="badge-price" title="Preço de Custo">
                                                <i class="bi bi-tag"></i>
                                                {{ number_format($product->price, 2, ',', '.') }}
                                            </span>
                                            <span class="badge-price-sale" title="Preço de Venda">
                                                <i class="bi bi-currency-dollar"></i>
                                                {{ number_format($product->price_sale, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Lado Direito: Painel de Resumo Modernizado -->
                    <div class="w-full lg:w-1/4 shrink-0 flex flex-col create-sale-summary-pane">
                        <div class="p-4 create-sale-summary-card">
                            @php
                                $selectedItemsCount = count($selectedProducts);
                                $selectedUnitsCount = collect($selectedProducts)->sum('quantity');
                                $averageItemTicket = $selectedUnitsCount > 0 ? $this->getTotalPrice() / $selectedUnitsCount : 0;
                                $summaryReady = $client_id && $selectedItemsCount > 0;
                            @endphp
                            <div class="create-sale-summary-topbar">
                                <div class="create-sale-summary-title-wrap">
                                    <span class="create-sale-summary-icon">
                                        <i class="bi bi-pencil-square text-2xl"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800 dark:text-white">
                                            Editando #{{ $sale->id }}
                                        </h3>
                                    </div>
                                </div>
                                <span class="create-sale-summary-status">
                                    <i class="bi {{ $summaryReady ? 'bi-check2-circle' : 'bi-pencil' }}"></i>
                                    {{ $summaryReady ? 'Pronto' : 'Em edição' }}
                                </span>
                            </div>
                            <div class="create-sale-summary-stats">
                                <div class="create-sale-summary-stat">
                                    <span class="create-sale-summary-stat-label">Produtos</span>
                                    <span class="create-sale-summary-stat-value">{{ $selectedItemsCount }}</span>
                                </div>
                                <div class="create-sale-summary-stat">
                                    <span class="create-sale-summary-stat-label">Unidades</span>
                                    <span class="create-sale-summary-stat-value">{{ $selectedUnitsCount }}</span>
                                </div>
                                <div class="create-sale-summary-stat">
                                    <span class="create-sale-summary-stat-label">Modo</span>
                                    <span class="create-sale-summary-stat-value">{{ $tipo_pagamento === 'a_vista' ? 'Vista' : 'Parc.' }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 create-sale-summary-info-grid">
                                <!-- Bloco Cliente -->
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                    <div class="relative">
                                        <button type="button" class="w-full text-left" @click="open = !open; $nextTick(() => { if (open) $refs.clientSearchSidebar.focus() })">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-person-fill text-blue-500 text-lg"></i>
                                                <div>
                                                    <label class="text-[10px] font-medium text-blue-800 dark:text-blue-200">Cliente</label>
                                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100 -mt-1 truncate">
                                                        {{ $this->selectedClient->name ?? 'Selecionar...' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition @click.away="open = false; $wire.set('clientSearch', '')" class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                                            <div class="p-2 border-b border-slate-100 dark:border-zinc-700">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400"><i class="bi bi-search"></i></span>
                                                    <input x-ref="clientSearchSidebar" type="text" wire:model.live.debounce.250ms="clientSearch" placeholder="Buscar cliente..." class="w-full pl-10 pr-4 py-2 rounded-md border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 text-sm focus:ring-2 focus:ring-indigo-400">
                                                </div>
                                            </div>
                                            <div class="py-1">
                                                @php $filteredClients = $this->filteredClients; @endphp
                                                @if($filteredClients->isEmpty())
                                                <div class="px-4 py-2 text-sm text-slate-500">Nenhum cliente encontrado</div>
                                                @else
                                                @foreach($filteredClients as $client)
                                                <button type="button" @click="open=false; $wire.set('client_id', {{ $client->id }}).then(() => $wire.set('clientSearch',''))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-zinc-700 flex items-center gap-3 text-sm transition-colors">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm">{{ strtoupper(substr($client->name,0,1)) }}</div>
                                                    <div class="text-slate-700 dark:text-slate-300">{{ $client->name }}</div>
                                                </button>
                                                @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @error('client_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Bloco Data — Custom DatePicker -->
                                <div class="relative p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl shadow-sm"
                                     x-data="{
                                         open: false,
                                         sel: '{{ $sale_date ?? date('Y-m-d') }}',
                                         vy: 0, vm: 0,
                                         wdays: ['D','S','T','Q','Q','S','S'],
                                         init() {
                                             let d = this.sel ? new Date(this.sel + 'T12:00:00') : new Date();
                                             this.vy = d.getFullYear();
                                             this.vm = d.getMonth();
                                         },
                                         get mname() {
                                             return new Date(this.vy, this.vm, 1)
                                                 .toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
                                         },
                                         get dim() { return new Date(this.vy, this.vm + 1, 0).getDate(); },
                                         get fdow() { return new Date(this.vy, this.vm, 1).getDay(); },
                                         prev() { this.vm === 0 ? (this.vm = 11, this.vy--) : this.vm--; },
                                         next() { this.vm === 11 ? (this.vm = 0, this.vy++) : this.vm++; },
                                         pick(d) {
                                             let m = String(this.vm + 1).padStart(2, '0');
                                             let dd = String(d).padStart(2, '0');
                                             this.sel = this.vy + '-' + m + '-' + dd;
                                             $wire.set('sale_date', this.sel);
                                             this.open = false;
                                         },
                                         goToday() {
                                             let t = new Date();
                                             this.vy = t.getFullYear();
                                             this.vm = t.getMonth();
                                             this.pick(t.getDate());
                                         },
                                         isSel(d) {
                                             if (!this.sel) return false;
                                             let p = new Date(this.sel + 'T12:00:00');
                                             return p.getFullYear() === this.vy && p.getMonth() === this.vm && p.getDate() === d;
                                         },
                                         isToday(d) {
                                             let t = new Date();
                                             return t.getFullYear() === this.vy && t.getMonth() === this.vm && t.getDate() === d;
                                         },
                                         fmtSel() {
                                             if (!this.sel) return 'Selecionar data';
                                             return new Date(this.sel + 'T12:00:00')
                                                 .toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
                                         }
                                     }"
                                     @click.outside="open = false">

                                    <button type="button" @click="open = !open"
                                            class="w-full flex items-center gap-2 min-h-[44px] text-left focus:outline-none">
                                        <i class="bi bi-calendar-fill text-purple-500 text-lg flex-shrink-0"></i>
                                        <div class="flex-1 min-w-0">
                                            <label class="text-[10px] font-medium text-purple-800 dark:text-purple-200 block pointer-events-none">Data</label>
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 leading-tight"
                                                  x-text="fmtSel()"></span>
                                        </div>
                                        <i class="bi bi-chevron-down text-purple-400/70 text-xs flex-shrink-0 transition-transform duration-200"
                                           :class="open ? 'rotate-180' : ''"></i>
                                    </button>

                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-[9999] top-full left-0 mt-2 w-64 rounded-2xl overflow-hidden shadow-2xl"
                                         style="background:linear-gradient(160deg,#1e1b4b 0%,#312e81 50%,#1a1640 100%);border:1px solid rgba(165,180,252,0.25);">

                                        <div class="flex items-center justify-between px-4 py-3"
                                             style="background:linear-gradient(135deg,rgba(99,102,241,0.55),rgba(139,92,246,0.45));border-bottom:1px solid rgba(165,180,252,0.18);">
                                            <button type="button" @click.stop="prev()"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-indigo-200 hover:bg-white/15 transition-colors">
                                                <i class="bi bi-chevron-left text-xs"></i>
                                            </button>
                                            <span class="text-sm font-bold text-white capitalize" x-text="mname"></span>
                                            <button type="button" @click.stop="next()"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-indigo-200 hover:bg-white/15 transition-colors">
                                                <i class="bi bi-chevron-right text-xs"></i>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-7 px-3 pt-2 pb-0.5">
                                            <template x-for="w in wdays">
                                                <div class="text-center text-[10px] font-bold pb-1"
                                                     style="color:rgba(165,180,252,0.65)"
                                                     x-text="w"></div>
                                            </template>
                                        </div>

                                        <div class="grid grid-cols-7 px-3 pb-2 gap-y-0.5">
                                            <template x-for="_ in fdow">
                                                <div></div>
                                            </template>
                                            <template x-for="day in dim" :key="day">
                                                <button type="button"
                                                        @click.stop="pick(day)"
                                                        :class="isSel(day)
                                                            ? 'text-white font-bold shadow-lg'
                                                            : isToday(day)
                                                                ? 'text-indigo-200 font-semibold border border-indigo-400/50'
                                                                : 'text-slate-300 hover:bg-white/10'"
                                                        :style="isSel(day) ? 'background:linear-gradient(135deg,#6366f1,#9333ea)' : ''"
                                                        class="w-7 h-7 mx-auto flex items-center justify-center text-xs rounded-lg transition-all duration-100"
                                                        x-text="day">
                                                </button>
                                            </template>
                                        </div>

                                        <div class="px-3 pb-3 pt-1">
                                            <button type="button" @click.stop="goToday()"
                                                    class="w-full py-1.5 text-xs font-semibold rounded-lg transition-colors"
                                                    style="color:rgba(165,180,252,0.9);border:1px solid rgba(99,102,241,0.35);"
                                                    onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                                                    onmouseout="this.style.background=''">
                                                Hoje
                                            </button>
                                        </div>
                                    </div>

                                    @error('sale_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Bloco Pagamento -->
                                <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                    <div class="relative h-full">
                                        <button type="button" @click="open = !open" class="w-full h-full text-left">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-credit-card-fill text-green-500 text-lg"></i>
                                                <div>
                                                    <label class="text-[10px] font-medium text-green-800 dark:text-green-200">Pagamento</label>
                                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                        <span>{{ $tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.away="open = false"
                                         class="summary-dropdown-menu absolute z-[9999] left-0 min-w-[160px] w-full mt-2 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1">
                                            <button @click="$wire.set('tipo_pagamento', 'a_vista'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 {{ $tipo_pagamento === 'a_vista' ? 'bg-green-100 dark:bg-green-900/50' : '' }}">
                                                @if($tipo_pagamento === 'a_vista') <i class="bi bi-check-circle-fill text-green-500"></i> @endif
                                                <span>À Vista</span>
                                            </button>
                                            <button @click="$wire.set('tipo_pagamento', 'parcelado'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 {{ $tipo_pagamento === 'parcelado' ? 'bg-green-100 dark:bg-green-900/50' : '' }}">
                                                @if($tipo_pagamento === 'parcelado') <i class="bi bi-check-circle-fill text-green-500"></i> @endif
                                                <span>Parcelado</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('tipo_pagamento') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Bloco Parcelas -->
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl shadow-sm">
                                    @if($tipo_pagamento == 'parcelado')
                                    <div x-data="{ open: false }" x-transition class="h-full">
                                        <div class="relative h-full">
                                            <button type="button" @click="open = !open" class="w-full h-full text-left">
                                                <div class="flex items-center gap-2">
                                                    <i class="bi bi-hash text-amber-500 text-lg"></i>
                                                    <div>
                                                        <label class="text-[10px] font-medium text-amber-800 dark:text-amber-200">Parcelas</label>
                                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                            <span>{{ $parcelas }}x</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 @click.away="open = false"
                                             class="summary-dropdown-menu absolute z-[9999] left-0 min-w-[110px] w-full mt-2 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1 max-h-40 overflow-y-auto">
                                                @for($i = 1; $i <= 12; $i++)
                                                <button @click="$wire.set('parcelas', {{ $i }}); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-amber-100/50 dark:hover:bg-amber-900/20 {{ $parcelas == $i ? 'bg-amber-100 dark:bg-amber-900/50' : '' }}">
                                                    @if($parcelas == $i) <i class="bi bi-check-circle-fill text-amber-500"></i> @endif
                                                    <span>{{ $i }}x</span>
                                                </button>
                                                @endfor
                                            </div>
                                        </div>
                                        @error('parcelas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    @else
                                    <div class="flex items-center gap-2 text-slate-400 h-full">
                                        <i class="bi bi-calendar-range-fill"></i>
                                        <span class="text-xs">Sem parcelas</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($selectedProducts))
                            <div class="create-sale-total-panel">
                                <div class="create-sale-total-grid">
                                    <div class="create-sale-total-metric">
                                        <span class="block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Ticket médio por unidade</span>
                                        <strong class="mt-1 block text-base text-slate-900 dark:text-white">R$ {{ number_format($averageItemTicket, 2, ',', '.') }}</strong>
                                    </div>
                                    <div class="create-sale-total-metric">
                                        <span class="block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Parcelas atuais</span>
                                        <strong class="mt-1 block text-base text-slate-900 dark:text-white">{{ $tipo_pagamento === 'parcelado' ? $parcelas . 'x' : '1x' }}</strong>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-4">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="bi bi-currency-dollar"></i>Valor Total</span>
                                    <span class="text-2xl font-bold text-green-500">
                                        R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if(!$summaryReady)
                                <div class="create-sale-summary-alert">
                                    <p class="text-sm font-bold flex items-center gap-2"><i class="bi bi-exclamation-circle"></i>Faltando para liberar o resumo</p>
                                    <ul>
                                        @if(empty($client_id))<li>Selecionar o cliente da venda.</li>@endif
                                        @if($selectedItemsCount === 0)<li>Adicionar pelo menos um produto.</li>@endif
                                    </ul>
                                </div>
                            @endif

                            @php $canProceedMobile = count($selectedProducts) > 0 && $client_id; @endphp
                            <button
                                type="button"
                                @if($canProceedMobile)
                                    @click="currentStep = 2; $wire.set('currentStep', 2)"
                                @endif
                                @if(!$canProceedMobile) disabled @endif
                                class="sm:hidden mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-white transition-all duration-300
                                    {{ $canProceedMobile
                                        ? 'bg-gradient-to-r from-indigo-500 to-purple-600 shadow-md'
                                        : 'bg-slate-400/60 dark:bg-slate-700/60 cursor-not-allowed opacity-60'
                                    }}"
                            >
                                <span>Prosseguir para Resumo</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>

                        <!-- Lista de produtos selecionados -->
                        <div class="flex-1 overflow-y-auto create-sale-selected-list">
                            @if(empty($selectedProducts))
                            <div class="p-4 text-center create-sale-selected-empty">
                                <div class="text-gray-400 mb-3">
                                    <i class="bi bi-cart-x text-2xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Carrinho ainda vazio
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                    Clique nos produtos à esquerda para adicioná-los
                                </p>
                            </div>
                            @else
                            <div class="p-4">
                                @foreach($selectedProducts as $index => $productItem)
                                    @php
                                        $selectedProduct = $products->firstWhere('id', $productItem['product_id']);
                                    @endphp
                                    @if($selectedProduct)
                                    <div class="group create-sale-selected-item" wire:key="edit-selected-{{ $selectedProduct->id }}">

                                        <div class="flex items-start gap-2.5">
                                            <div class="relative shrink-0">
                                                <img src="{{ $selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png') }}"
                                                     alt="{{ $selectedProduct->name }}"
                                                     class="create-sale-selected-thumb border border-white/40 dark:border-slate-700/50">
                                                <span class="csi-type-badge {{ ($selectedProduct->tipo ?? 'simples') === 'kit' ? 'bg-violet-500' : 'bg-indigo-500' }}">
                                                    <i class="bi {{ ($selectedProduct->tipo ?? 'simples') === 'kit' ? 'bi-boxes' : 'bi-box' }} text-white" style="font-size:7px"></i>
                                                </span>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-bold text-slate-800 dark:text-white leading-tight truncate" title="{{ $selectedProduct->name }}">
                                                    {{ $selectedProduct->name }}
                                                </h4>
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">#{{ $selectedProduct->product_code }}</span>
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 inline-flex items-center gap-0.5">
                                                        <i class="bi bi-tag text-[9px]"></i>R${{ number_format($selectedProduct->price, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    wire:click.stop="toggleProduct({{ $selectedProduct->id }})"
                                                    class="create-sale-remove-btn shrink-0 mt-0.5"
                                                    title="Remover item">
                                                <i class="bi bi-x-lg text-xs"></i>
                                            </button>
                                        </div>

                                        <div class="my-2 h-px bg-gradient-to-r from-transparent via-slate-200/80 dark:via-slate-700/50 to-transparent"></div>

                                        <div class="create-sale-selected-grid">

                                            <div class="create-sale-selected-field">
                                                <label for="quantity-edit-{{ $selectedProduct->id }}">Qtd</label>
                                                <input type="number"
                                                       id="quantity-edit-{{ $selectedProduct->id }}"
                                                       wire:model.blur="selectedProducts.{{ $index }}.quantity"
                                                       value="{{ $productItem['quantity'] }}"
                                                       min="1"
                                                       @if(isset($selectedProduct->tipo) && $selectedProduct->tipo === 'simples')
                                                           max="{{ $selectedProduct->stock_quantity }}"
                                                       @endif
                                                       class="text-center">
                                            </div>

                                            <div class="create-sale-selected-field">
                                                <label for="price-edit-{{ $selectedProduct->id }}">Preço</label>
                                                <div class="relative"
                                                     x-data="{
                                                         cts: {{ (int)round(($productItem['price_sale'] ?? 0) * 100) }},
                                                         fmt() {
                                                             let s = String(this.cts).padStart(3, '0');
                                                             let d = s.slice(-2);
                                                             let i = s.slice(0, -2).replace(/^0+/, '') || '0';
                                                             i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                                             return i + ',' + d;
                                                         },
                                                         inp(e) {
                                                             let digs = e.target.value.replace(/\D/g, '');
                                                             this.cts = digs ? parseInt(digs) : 0;
                                                             e.target.value = this.fmt();
                                                         }
                                                     }">
                                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-medium text-slate-400 dark:text-slate-500 pointer-events-none">R$</span>
                                                    <input type="text"
                                                           inputmode="numeric"
                                                           id="price-edit-{{ $selectedProduct->id }}"
                                                           x-init="$el.value = fmt()"
                                                           @input="inp($event)"
                                                           @blur="$wire.set('selectedProducts.{{ $index }}.price_sale', (cts / 100).toFixed(2))"
                                                           class="pl-6 text-green-600 dark:text-green-400">
                                                </div>
                                            </div>

                                            <div class="create-sale-selected-subtotal">
                                                <span class="csi-total-label">Total</span>
                                                <span class="csi-total-value">R$&nbsp;{{ number_format(($productItem['quantity'] ?? 0) * ($productItem['price_sale'] ?? 0), 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Step 2: Resumo e Finalização - Layout em duas colunas (igual ao Create) -->
                <div x-show="currentStep === 2"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full flex flex-col lg:flex-row edit-sale-step2-shell">

                    <div class="w-full lg:w-2/5 bg-white dark:bg-zinc-800 p-3 sm:p-5 flex flex-col gap-3 sm:gap-4 edit-sale-review-info-pane">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white text-center sm:text-left mb-1 sm:mb-4 edit-sale-review-title">
                            <i class="bi bi-check-circle text-green-600 dark:text-green-400 mr-2"></i>
                            Resumo da Venda
                        </h2>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 mb-4 rounded-lg border-l-4 border-blue-500 edit-sale-review-client-card">
                            <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200 mb-3">
                                <i class="bi bi-person-circle mr-2"></i>Cliente
                            </h3>
                            @if($client_id && $selectedClient = $clients->firstWhere('id', $client_id))
                            <div class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Nome:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ $selectedClient->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Telefone:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ $selectedClient->phone }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Data:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ \Carbon\Carbon::parse($sale_date)->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Pagamento:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm">{{ ucfirst(str_replace('_', ' ', $tipo_pagamento)) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 rounded-lg border-l-4 border-indigo-500 mb-4 edit-sale-review-total-card">
                            <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-200 mb-2">
                                <i class="bi bi-calculator mr-2"></i>Total da Venda
                            </h3>
                            @if($tipo_pagamento === 'parcelado' && $parcelas > 1)
                            <div class="bg-white dark:bg-indigo-800/30 p-4 rounded-lg border border-indigo-200 dark:border-indigo-600 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-semibold text-indigo-700 dark:text-indigo-300">
                                        <i class="bi bi-credit-card-2-front mr-2"></i>Pagamento Parcelado
                                    </span>
                                    <span class="bg-indigo-100 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm font-bold">
                                        {{ $parcelas }}x
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-indigo-600 dark:text-indigo-400">Valor por parcela:</span>
                                        <span class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                                            R$ {{ number_format($this->getTotalPrice() / $parcelas, 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-2">
                                        <i class="bi bi-info-circle mr-1"></i>
                                        Parcelas mensais de {{ \Carbon\Carbon::parse($sale_date)->format('d/m/Y') }}
                                        até {{ \Carbon\Carbon::parse($sale_date)->addMonths($parcelas - 1)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="bg-white dark:bg-green-800/30 p-4 rounded-lg border border-green-200 dark:border-green-600 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-semibold text-green-700 dark:text-green-300">
                                        <i class="bi bi-cash mr-2"></i>Pagamento à Vista
                                    </span>
                                    <span class="bg-green-100 dark:bg-green-700 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-bold">
                                        À Vista
                                    </span>
                                </div>
                            </div>
                            @endif
                            <div class="text-center">
                                @if($tipo_pagamento === 'parcelado' && $parcelas > 1)
                                <p class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">
                                    {{ $parcelas }}x de R$ {{ number_format($this->getTotalPrice() / $parcelas, 2, ',', '.') }}
                                </p>
                                @endif
                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                    R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto space-y-3 edit-sale-review-actions">
                            <button type="button"
                                @click="currentStep = 1; $wire.set('currentStep', 1)"
                                class="group relative w-full inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                                <i class="bi bi-arrow-left mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                                Voltar: Produtos
                                <div class="absolute inset-0 rounded-2xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>

                            <button type="submit"
                                class="group relative w-full inline-flex items-center justify-center px-12 py-4 rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 hover:from-green-600 hover:via-emerald-600 hover:to-teal-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-green-300 backdrop-blur-sm"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove class="flex items-center">
                                    <i class="bi bi-check-circle mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                                    Salvar Alterações
                                </span>
                                <span wire:loading class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Salvando...
                                </span>
                                <div class="absolute inset-0 rounded-2xl bg-green-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                        </div>
                    </div>

                    <div class="w-full lg:w-4/5 bg-green-50 dark:bg-green-900/20 border-l border-gray-200 dark:border-zinc-700 p-4 sm:p-8 edit-sale-review-products-pane">
                        <h3 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-6 edit-sale-review-products-title">
                            <i class="bi bi-cart mr-2"></i>Produtos Selecionados ({{ count($selectedProducts) }})
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($selectedProducts as $product)
                            @if($product['product_id'])
                            @php
                                $productData = $products->firstWhere('id', $product['product_id']);
                                $total = ($product['quantity'] ?? 0) * ($product['price_sale'] ?? 0);
                            @endphp
                            @if($productData)
                            <div class="product-card-modern">
                                <div class="product-img-area">
                                    <img src="{{ $productData->image ? asset('storage/products/' . $productData->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $productData->name ?? 'Produto' }}"
                                         class="product-img">
                                    <span class="badge-product-code">
                                        <i class="bi bi-upc-scan"></i> {{ $productData->product_code ?? 'N/A' }}
                                    </span>
                                    <span class="badge-quantity">
                                        <i class="bi bi-cart-check"></i> {{ $product['quantity'] ?? 0 }}
                                    </span>
                                    @if($productData->category)
                                    <div class="category-icon-wrapper">
                                        <i class="{{ $productData->category->icone ?? 'bi bi-box' }} category-icon"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="product-title" title="{{ $productData->name ?? 'Produto não encontrado' }}">
                                        {{ ucwords($productData->name ?? 'Produto não encontrado') }}
                                    </div>
                                    <div class="price-area">
                                        <span class="badge-price" title="Preço Unitário">
                                            <i class="bi bi-tag"></i>
                                            {{ number_format($product['price_sale'] ?? 0, 2, ',', '.') }}
                                        </span>
                                        <span class="badge-price-sale" title="Total">
                                            <i class="bi bi-calculator"></i>
                                            {{ number_format($total, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            @endforeach
                        </div>

                        <div class="mt-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-green-800 dark:text-green-200">
                                    <i class="bi bi-calculator mr-2"></i>
                                    Total Geral:
                                </span>
                                <span class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    R$ {{ number_format($this->getTotalPrice(), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if (session()->has('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-check-circle-fill mr-2"></i>
        {{ session('success') }}
        <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
@endif

@if (session()->has('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
        {{ session('error') }}
        <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
@endif
