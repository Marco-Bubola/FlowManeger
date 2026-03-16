@props([
    'showFilters' => false,
    'clients' => collect(),
    'sellers' => collect(),
    'statusFilter' => '',
    'clientFilter' => '',
    'startDate' => '',
    'endDate' => '',
    'minValue' => '',
    'maxValue' => '',
    'paymentMethodFilter' => '',
    'sellerFilter' => '',
    'quickFilter' => '',
    'sortBy' => 'created_at',
    'sortDirection' => 'desc'
    ,'perPageOptions' => [],
    'perPage' => 12,
    'search' => ''
])

@php
    $hasActiveFilters = $search || $statusFilter || $clientFilter || $startDate || $endDate || $minValue || $maxValue || $paymentMethodFilter || $sellerFilter || $quickFilter;
@endphp

<template x-teleport="body">
    <div x-show="showFilters" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="showFilters = false"
        @keydown.escape.window="showFilters = false"
        class="fixed inset-0 z-[9999]">

        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-md"></div>

        <div class="relative h-full p-0 md:p-5 lg:p-8 flex items-end md:items-center justify-center">
            <div x-show="showFilters"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="w-full h-[92dvh] md:h-auto md:max-h-[92vh] md:max-w-6xl rounded-t-3xl md:rounded-3xl shadow-2xl border border-white/30 dark:border-slate-700/70 overflow-hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl">

                <div class="relative px-4 md:px-6 py-4 border-b border-white/25 dark:border-slate-700/70 bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
                    <div class="relative z-10 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg shadow-indigo-500/25">
                                    <i class="bi bi-funnel"></i>
                                </span>
                                <h3 class="text-base md:text-lg font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">Filtros de Vendas</h3>
                            </div>
                            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-300">Refine por status, pagamento, cliente, periodo e valor.</p>
                        </div>

                        <button type="button" @click="showFilters = false"
                            class="w-10 h-10 inline-flex items-center justify-center rounded-xl bg-white/80 hover:bg-white dark:bg-slate-800/90 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-sm">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="relative z-10 mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                            <i class="bi bi-receipt mr-1"></i>{{ $search ? 'Busca ativa' : 'Sem busca' }}
                        </span>
                        @if($hasActiveFilters)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <i class="bi bi-check-circle mr-1"></i>Filtros ativos
                            </span>
                        @endif
                    </div>
                </div>

                <div class="overflow-y-auto px-3 md:px-5 py-3 md:py-4 max-h-[calc(92dvh-170px)] md:max-h-[calc(92vh-190px)] bg-gradient-to-b from-slate-50/80 to-white dark:from-slate-900/40 dark:to-slate-900">
                    <div class="space-y-4 md:space-y-5">
                        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/70 dark:bg-slate-900/40 p-3 md:p-4">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        <i class="bi bi-funnel mr-1"></i>Filtro rapido
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex items-stretch gap-2">
                                    <button type="button" wire:click="setQuickFilter('today')" class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">Hoje</button>
                                    <button type="button" wire:click="setQuickFilter('week')" class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">Semana</button>
                                    <button type="button" wire:click="setQuickFilter('month')" class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">Mes</button>
                                    <button type="button" wire:click="setQuickFilter('pending')" class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-yellow-400 dark:hover:border-yellow-500 transition-colors">Pendentes</button>
                                    <button type="button" wire:click="setQuickFilter('paid')" class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-green-400 dark:hover:border-green-500 transition-colors">Pagas</button>
                                    <button type="button" wire:click="clearFilters" class="px-3 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 transition-all shadow-md">Limpar tudo</button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                            <div class="xl:col-span-4 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-list-ol mr-2 text-pink-500"></i>Itens por pagina</h4>
                                <div class="grid grid-cols-3 sm:grid-cols-4 xl:grid-cols-3 gap-2">
                                    @foreach($perPageOptions as $option)
                                        <button wire:click="$set('perPage', {{ $option }})"
                                            class="flex items-center justify-center min-h-[52px] p-2.5 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ (int) $perPage === (int) $option ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500' }}">
                                            <i class="bi bi-grid-3x2-gap text-pink-500 text-sm mr-1.5"></i>
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $option }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="xl:col-span-8 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-arrow-up-down mr-2 text-amber-500"></i>Ordenacao</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                                    <button wire:click="sortByField('created_at')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'created_at' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500' }}"><i class="bi bi-calendar text-indigo-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Data</span></button>
                                    <button wire:click="sortByField('updated_at')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'updated_at' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500' }}"><i class="bi bi-arrow-clockwise text-pink-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Atualizado</span></button>
                                    <button wire:click="sortByField('total_price')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'total_price' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500' }}"><i class="bi bi-currency-dollar text-green-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Valor</span></button>
                                    <button wire:click="sortByField('client_name')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'client_name' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500' }}"><i class="bi bi-person text-blue-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Cliente</span></button>
                                    <button wire:click="sortByField('status')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'status' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500' }}"><i class="bi bi-flag text-purple-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status</span></button>
                                    <button wire:click="sortByField('id')" class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[86px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $sortBy === 'id' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500' }}"><i class="bi bi-hash text-orange-500 text-lg"></i><span class="text-xs font-semibold text-slate-700 dark:text-slate-300">ID</span></button>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Direcao atual: <span class="font-semibold">{{ strtoupper($sortDirection) }}</span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                            <div class="lg:col-span-8 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center"><i class="bi bi-flag mr-2 text-blue-500"></i>Status da venda</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button wire:click="$set('statusFilter', '')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $statusFilter === '' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Todos</button>
                                            <button wire:click="$set('statusFilter', 'pending')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $statusFilter === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Pendente</button>
                                            <button wire:click="$set('statusFilter', 'paid')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $statusFilter === 'paid' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Paga</button>
                                            <button wire:click="$set('statusFilter', 'partially_paid')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $statusFilter === 'partially_paid' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Parcial</button>
                                        </div>
                                    </div>

                                    <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center"><i class="bi bi-credit-card mr-2 text-violet-500"></i>Pagamento</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button wire:click="$set('paymentMethodFilter', '')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $paymentMethodFilter === '' ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Todos</button>
                                            <button wire:click="$set('paymentMethodFilter', 'cash')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $paymentMethodFilter === 'cash' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Dinheiro</button>
                                            <button wire:click="$set('paymentMethodFilter', 'card')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $paymentMethodFilter === 'card' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">Cartao</button>
                                            <button wire:click="$set('paymentMethodFilter', 'pix')" class="p-3 rounded-xl border-2 text-xs font-semibold {{ $paymentMethodFilter === 'pix' ? 'border-cyan-500 bg-cyan-50 dark:bg-cyan-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">PIX</button>
                                        </div>
                                    </div>

                                    <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center"><i class="bi bi-calendar-range mr-2 text-blue-500"></i>Periodo</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" wire:click="setQuickDateFilter('yesterday')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Ontem</button>
                                            <button type="button" wire:click="setQuickDateFilter('last_week')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Semana ant.</button>
                                            <button type="button" wire:click="setQuickDateFilter('last_month')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Mes ant.</button>
                                            <button type="button" wire:click="setQuickDateFilter('year')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Ano</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-people mr-2 text-cyan-500"></i>Cliente</h4>
                                    <select wire:model.live="clientFilter" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500/30 focus:border-cyan-400">
                                        <option value="">Todos os clientes</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($clients->count())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button" wire:click="$set('clientFilter', '')" class="px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $clientFilter === '' ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700' }}">Todos</button>
                                            @foreach($clients->take(8) as $client)
                                                <button type="button" wire:click="$set('clientFilter', '{{ $client->id }}')" class="px-3 py-1.5 rounded-lg text-xs font-semibold border {{ (string) $clientFilter === (string) $client->id ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700' }}">{{ $client->name }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="lg:col-span-4 space-y-4">
                                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-calendar-event mr-2 text-blue-500"></i>Intervalo de datas</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-[11px] text-slate-600 dark:text-slate-400">Data inicial</label>
                                            <input type="date" wire:model.live="startDate" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                                        </div>
                                        <div>
                                            <label class="text-[11px] text-slate-600 dark:text-slate-400">Data final</label>
                                            <input type="date" wire:model.live="endDate" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-cash-stack mr-2 text-emerald-500"></i>Faixa de valor</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[11px] text-slate-600 dark:text-slate-400">Minimo</label>
                                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="minValue" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200" placeholder="0,00">
                                        </div>
                                        <div>
                                            <label class="text-[11px] text-slate-600 dark:text-slate-400">Maximo</label>
                                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="maxValue" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200" placeholder="9999,99">
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="setValueRange('low')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Baixo</button>
                                        <button type="button" wire:click="setValueRange('medium')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Medio</button>
                                        <button type="button" wire:click="setValueRange('high')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Alto</button>
                                        <button type="button" wire:click="setValueRange('premium')" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">Premium</button>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3"><i class="bi bi-person-badge mr-2 text-violet-500"></i>Vendedor</h4>
                                    <select wire:model.live="sellerFilter" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200">
                                        <option value="">Todos os vendedores</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}">{{ $seller->name ?? ('Vendedor #' . $seller->id) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div wire:loading.flex wire:target="statusFilter,clientFilter,startDate,endDate,minValue,maxValue,paymentMethodFilter,sellerFilter,sortBy,sortDirection,perPage"
                            class="items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <span class="inline-block w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
                            Atualizando filtros...
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl sticky bottom-0">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-2 md:gap-3">
                        <button type="button" @click="showFilters = false"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </button>

                        <div class="flex items-center gap-2 md:gap-3 sm:ml-auto">
                            <button type="button" wire:click="clearFilters"
                                class="inline-flex items-center justify-center gap-2 px-3.5 md:px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-colors flex-1 sm:flex-none">
                                <i class="bi bi-eraser"></i>
                                Limpar
                            </button>
                            <button type="button" @click="showFilters = false"
                                class="inline-flex items-center justify-center gap-2 px-4 md:px-5 py-2.5 rounded-xl text-xs md:text-sm font-semibold bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 hover:from-indigo-700 hover:via-purple-700 hover:to-blue-700 text-white shadow-lg shadow-indigo-500/30 transition-all duration-200 flex-1 sm:flex-none">
                                <i class="bi bi-check2-circle"></i>
                                Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
