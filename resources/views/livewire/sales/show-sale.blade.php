<div class="w-full min-h-screen app-viewport-fit sale-show-page mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ultrawide.css') }}">

    @php
        if ($sale->client) {
            $clientLink = '<a href="' . route('clients.dashboard', $sale->client->id) . '" class="font-semibold text-indigo-600 hover:underline">' . e($sale->client->name) . '</a>';
        } else {
            $clientLink = 'Cliente não informado';
        }
        $descriptionHtml = "Cliente: " . $clientLink
            . " | " . $sale->saleItems->count() . " " . ($sale->saleItems->count() === 1 ? 'item' : 'itens')
            . " | Total: R$ " . number_format($sale->total_price, 2, ',', '.');

        $status      = $sale->remaining_amount <= 0 ? 'pago' : ($sale->total_paid > 0 ? 'parcial' : 'pendente');
        $statusColor = $status === 'pago' ? 'green' : ($status === 'parcial' ? 'yellow' : 'red');
        $statusText  = $status === 'pago' ? 'Pago' : ($status === 'parcial' ? 'Parcial' : 'Pendente');
        $statusIcon  = $status === 'pago' ? 'check-circle-fill' : ($status === 'parcial' ? 'clock-fill' : 'x-circle-fill');
        $percentage  = $sale->total_price > 0 ? min(100, ($sale->total_paid / $sale->total_price) * 100) : 0;

        $tipoLabel = $sale->tipo_pagamento === 'parcelado' ? 'Parcelado' : 'À Vista';
        $tipoIcon  = $sale->tipo_pagamento === 'parcelado' ? 'calendar3' : 'lightning-fill';

        $circumference = 2 * M_PI * 38;
        $dashOffset    = $circumference * (1 - $percentage / 100);

        // método de pagamento dos itens para badge
        $methodLabels = [
            'dinheiro' => '💵 Dinheiro', 'cartao_debito' => '💳 Débito',
            'cartao_credito' => '💳 Crédito', 'pix' => '⚡ PIX',
            'transferencia' => '🏦 Transferência', 'cheque' => '🧾 Cheque',
            'desconto' => '🏷️ Desconto', 'parcela' => '📅 Parcela',
        ];
    @endphp

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <x-sales-header
        title="Venda #{{ $sale->id }}"
        :description="$descriptionHtml"
        :back-route="route('sales.index')"
        :current-step="1"
        :steps="[]">
        <x-slot name="actions">
            <button wire:click="exportPdf"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                <i class="bi bi-file-earmark-pdf text-base"></i>
                <span class="hidden sm:inline">PDF</span>
            </button>
            @if($sale->remaining_amount > 0)
                <button wire:click="payFull"
                        class="ml-2 inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                    <i class="bi bi-cash-stack text-base"></i>
                    <span class="hidden sm:inline">Pagar Tudo</span>
                </button>
                <button wire:click="openDiscountModal"
                        class="ml-2 inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                    <i class="bi bi-tag text-base"></i>
                    <span class="hidden sm:inline">Zerar</span>
                </button>
            @endif
        </x-slot>
    </x-sales-header>

    {{-- ============================================================
         CONTEÚDO PRINCIPAL
    ============================================================ --}}
    <div class="sale-main-content container-fluid mx-auto  ">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mt-4 mb-2 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="bi bi-check-circle-fill text-emerald-500 text-xl flex-shrink-0"></i>
                <span class="text-emerald-800 dark:text-emerald-300 font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mt-4 mb-2 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="bi bi-x-circle-fill text-red-500 text-xl flex-shrink-0"></i>
                <span class="text-red-800 dark:text-red-300 font-medium text-sm">{{ session('error') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="mt-4 mb-2 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-xl flex-shrink-0"></i>
                <span class="text-amber-800 dark:text-amber-300 font-medium text-sm">{{ session('warning') }}</span>
            </div>
        @endif

        {{-- ========================================================
             SEÇÃO 1 — INFO: CLIENTE | STATUS | DETALHES
        ======================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mt-5 mb-6">

            {{-- CARD CLIENTE --}}
            <div class="show-card bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-zinc-700/60 stagger-item">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-blue-500/10 rounded-xl">
                        <i class="bi bi-person-lines-fill text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Cliente</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informações do comprador</p>
                    </div>
                    @if($sale->client)
                        <a href="{{ route('clients.dashboard', $sale->client->id) }}"
                           class="p-2 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-xl transition-colors"
                           title="Ver perfil completo">
                            <i class="bi bi-box-arrow-up-right text-blue-600 dark:text-blue-400 text-sm"></i>
                        </a>
                    @endif
                </div>

                {{-- Avatar + nome --}}
                <div class="flex items-center gap-3 mb-4 p-3 bg-blue-50/60 dark:bg-blue-900/10 rounded-xl">
                    @if($sale->client && $sale->client->caminho_foto)
                        <img src="{{ asset('storage/' . $sale->client->caminho_foto) }}"
                             alt="{{ $sale->client->name }}"
                             class="w-12 h-12 rounded-full object-cover border-2 border-blue-200 dark:border-blue-700 flex-shrink-0">
                    @else
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                            <span class="text-white font-bold text-lg">{{ strtoupper(substr($sale->client->name ?? 'C', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 dark:text-white text-sm leading-tight truncate">
                            {{ $sale->client->name ?? 'Cliente não informado' }}
                        </p>
                        @if($sale->client)
                            <span class="text-xs text-blue-600 dark:text-blue-400">
                                <i class="bi bi-bag-check mr-0.5"></i>{{ $sale->client->sales()->count() }} venda(s) no total
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Dados de contato --}}
                <div class="space-y-2">
                    @if($sale->client && $sale->client->email)
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-gray-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-envelope text-gray-500 dark:text-gray-400 text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $sale->client->email }}</span>
                        </div>
                    @endif
                    @if($sale->client && $sale->client->phone)
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-gray-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-telephone text-gray-500 dark:text-gray-400 text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $sale->client->phone }}</span>
                        </div>
                    @endif
                    @if($sale->client && $sale->client->address)
                        <div class="flex items-start gap-2.5">
                            <div class="w-7 h-7 bg-gray-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="bi bi-geo-alt text-gray-500 dark:text-gray-400 text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-300 leading-tight">{{ $sale->client->address }}</span>
                        </div>
                    @endif
                    @if(!$sale->client || (!$sale->client->email && !$sale->client->phone && !$sale->client->address))
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-2">Sem dados de contato</p>
                    @endif
                </div>
            </div>

            {{-- CARD STATUS (circular progress) --}}
            <div class="show-card bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-zinc-700/60 stagger-item">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2.5
                        @if($status === 'pago') bg-green-500/10
                        @elseif($status === 'parcial') bg-yellow-500/10
                        @else bg-red-500/10 @endif
                        rounded-xl">
                        <i class="bi bi-{{ $statusIcon }}
                            @if($status === 'pago') text-green-600 dark:text-green-400
                            @elseif($status === 'parcial') text-yellow-600 dark:text-yellow-400
                            @else text-red-600 dark:text-red-400 @endif
                            text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Status da Venda</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Situação financeira</p>
                    </div>
                    <span class="px-2.5 py-1
                        @if($status === 'pago') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                        @elseif($status === 'parcial') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                        @else bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 @endif
                        rounded-full text-xs font-bold uppercase tracking-wide">
                        {{ $statusText }}
                    </span>
                </div>

                {{-- Circular progress --}}
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-28 h-28">
                        <svg class="w-28 h-28 -rotate-90" viewBox="0 0 96 96">
                            <circle cx="48" cy="48" r="38" stroke-width="7" fill="none"
                                    class="stroke-gray-200 dark:stroke-zinc-700"/>
                            <circle cx="48" cy="48" r="38" stroke-width="7" fill="none"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ number_format($circumference, 4, '.', '') }}"
                                    stroke-dashoffset="{{ number_format($dashOffset, 4, '.', '') }}"
                                    class="
                                        @if($status === 'pago') stroke-green-500
                                        @elseif($status === 'parcial') stroke-yellow-400
                                        @else stroke-red-500 @endif
                                        transition-all duration-1000"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ number_format($percentage, 0) }}%</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">pago</span>
                        </div>
                    </div>
                </div>

                {{-- Valores --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-zinc-800 rounded-xl">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                        <span class="text-xs text-emerald-700 dark:text-emerald-400">Pago</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 text-sm">R$ {{ number_format($sale->total_paid, 2, ',', '.') }}</span>
                    </div>
                    @if($sale->remaining_amount > 0)
                        <div class="flex items-center justify-between px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-xl">
                            <span class="text-xs text-red-700 dark:text-red-400">Restante</span>
                            <span class="font-bold text-red-700 dark:text-red-400 text-sm">R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- CARD DETALHES DA VENDA --}}
            <div class="show-card bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-zinc-700/60 stagger-item">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-violet-500/10 rounded-xl">
                        <i class="bi bi-info-circle text-violet-600 dark:text-violet-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Detalhes da Venda</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informações gerais</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-hash text-violet-400 text-sm"></i>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Número</span>
                        </div>
                        <span class="text-sm font-bold text-violet-600 dark:text-violet-400">#{{ $sale->id }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar-event text-violet-400 text-sm"></i>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Criada em</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-arrow-clockwise text-violet-400 text-sm"></i>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Atualizada</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $sale->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-credit-card text-violet-400 text-sm"></i>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Tipo pagamento</span>
                        </div>
                        <span class="px-2.5 py-0.5 bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 rounded-full text-xs font-bold">
                            <i class="bi bi-{{ $tipoIcon }} mr-1"></i>{{ $tipoLabel }}
                        </span>
                    </div>

                    @if($sale->tipo_pagamento === 'parcelado' && $sale->parcelas)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-grid-3x3-gap text-violet-400 text-sm"></i>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Parcelas</span>
                            </div>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $sale->parcelas }}x</span>
                        </div>
                        @if($sale->total_price > 0 && $sale->parcelas > 0)
                            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-zinc-700/60">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-calculator text-violet-400 text-sm"></i>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Valor/parcela</span>
                                </div>
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">R$ {{ number_format($sale->total_price / $sale->parcelas, 2, ',', '.') }}</span>
                            </div>
                        @endif
                    @endif

                    @if($sale->payment_method)
                        <div class="flex items-center justify-between py-1.5">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-wallet2 text-violet-400 text-sm"></i>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Método</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================================
             SEÇÃO 3 — LINHA DO TEMPO
        ======================================================== --}}
        @if($sale->payments->count() > 0)
            <div class="show-card bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-zinc-700/60 mb-6 stagger-item">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-500/10 rounded-xl">
                            <i class="bi bi-clock-history text-amber-600 dark:text-amber-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Linha do Tempo</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Histórico de eventos</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-zinc-700 rounded-full px-2.5 py-0.5">
                        {{ $sale->payments->count() + 1 }} evento(s)
                    </span>
                </div>

                <div class="relative pl-4">
                    {{-- Linha vertical --}}
                    <div class="absolute left-7 top-4 bottom-4 w-0.5 bg-gray-200 dark:bg-zinc-700"></div>

                    {{-- Evento: venda criada --}}
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 z-10
                                    bg-indigo-100 dark:bg-indigo-900/40 border-2 border-indigo-300 dark:border-indigo-600">
                            <i class="bi bi-cart-plus text-indigo-600 dark:text-indigo-400 text-xs"></i>
                        </div>
                        <div class="pt-0.5">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Venda criada</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d/m/Y \à\s H:i') }}</p>
                        </div>
                    </div>

                    {{-- Eventos: pagamentos --}}
                    @foreach($sale->payments->sortBy('payment_date') as $payment)
                        @php
                            $isDiscount = $payment->payment_method === 'desconto';
                            $evtColor   = $isDiscount ? 'amber' : 'emerald';
                            $evtIcon    = $isDiscount ? 'tag' : 'check2-circle';
                            $evtLabel   = $isDiscount ? 'Desconto aplicado' : 'Pagamento recebido';
                        @endphp
                        <div class="flex items-start gap-4 {{ !$loop->last ? 'mb-4' : '' }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 z-10
                                        bg-{{ $evtColor }}-100 dark:bg-{{ $evtColor }}-900/40 border-2 border-{{ $evtColor }}-300 dark:border-{{ $evtColor }}-600">
                                <i class="bi bi-{{ $evtIcon }} text-{{ $evtColor }}-600 dark:text-{{ $evtColor }}-400 text-xs"></i>
                            </div>
                            <div class="pt-0.5 flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $evtLabel }}</p>
                                    <span class="text-sm font-bold text-{{ $evtColor }}-600 dark:text-{{ $evtColor }}-400 flex-shrink-0">
                                        R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $methodLabels[$payment->payment_method] ?? ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    · {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ========================================================
             SEÇÃO 4 — PAGAMENTOS & PARCELAS (redesign moderno)
        ======================================================== --}}
        <div class="payments-section mb-6">

            {{-- Cabeçalho da seção --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-500/10 rounded-xl">
                        <i class="bi bi-wallet2 text-emerald-600 dark:text-emerald-400 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Pagamentos</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $sale->payments->count() }} registro(s) · última em
                            {{ $sale->payments->count() > 0 ? \Carbon\Carbon::parse($sale->payments->sortByDesc('payment_date')->first()->payment_date)->format('d/m/Y') : '-' }}
                        </p>
                    </div>
                </div>
                <button wire:click="toggleAddPaymentForm"
                        class="payment-add-btn inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl
                               {{ $showAddPaymentForm
                                   ? 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300'
                                   : 'bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white' }}">
                    <i class="bi bi-{{ $showAddPaymentForm ? 'x-lg' : 'plus-circle-fill' }}"></i>
                    <span class="hidden sm:inline">{{ $showAddPaymentForm ? 'Fechar' : 'Novo Pagamento' }}</span>
                </button>
            </div>

            {{-- Barra financeira resumida --}}
            <div class="payment-summary-bar rounded-2xl overflow-hidden mb-5 shadow-md">
                <div class="grid grid-cols-3">
                    <div class="px-5 py-4 bg-gradient-to-br from-slate-700 to-slate-800 text-white text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60 mb-0.5">Total</p>
                        <p class="text-lg font-extrabold leading-none">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</p>
                    </div>
                    <div class="px-5 py-4 bg-gradient-to-br from-emerald-600 to-green-700 text-white text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60 mb-0.5">Pago</p>
                        <p class="text-lg font-extrabold leading-none">R$ {{ number_format($sale->total_paid, 2, ',', '.') }}</p>
                    </div>
                    <div class="px-5 py-4 text-white text-center
                        @if($sale->remaining_amount <= 0) bg-gradient-to-br from-green-500 to-teal-600
                        @else bg-gradient-to-br from-red-500 to-rose-600 @endif">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60 mb-0.5">Restante</p>
                        <p class="text-lg font-extrabold leading-none">R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Barra de progresso --}}
                <div class="h-2 bg-gray-200 dark:bg-zinc-700">
                    <div class="h-full rounded-r-full transition-all duration-1000
                        @if($status === 'pago') bg-gradient-to-r from-emerald-400 to-green-500
                        @elseif($status === 'parcial') bg-gradient-to-r from-amber-400 to-orange-400
                        @else bg-gradient-to-r from-red-400 to-rose-400 @endif"
                         style="width: {{ number_format($percentage, 2, '.', '') }}%">
                    </div>
                </div>
            </div>

            {{-- Formulário de pagamento moderno --}}
            @if($showAddPaymentForm)
                <div class="payment-form-modern bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-xl border border-emerald-200/80 dark:border-emerald-700/40 mb-5 animate-fade-in">
                    {{-- Header do form --}}
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-plus-circle-fill text-white text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Registrar Pagamento</h4>
                                <p class="text-xs text-white/70">Preencha os dados abaixo</p>
                            </div>
                        </div>
                        @if($sale->remaining_amount > 0)
                            <button wire:click="payFull"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-xl transition-colors border border-white/30">
                                <i class="bi bi-lightning-fill"></i> Pagar Tudo
                            </button>
                        @endif
                    </div>

                    <div class="p-5">
                        @foreach($newPayments as $index => $payment)
                            <div class="payment-row-card mb-4 p-4 bg-gray-50/80 dark:bg-zinc-800/80 rounded-2xl border border-gray-200/80 dark:border-zinc-700/60
                                        {{ count($newPayments) > 1 ? '' : '' }}">

                                {{-- Seletor visual de método --}}
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2.5">Método de Pagamento</p>
                                <div class="method-picker grid grid-cols-3 sm:grid-cols-6 gap-2 mb-4">
                                    @php
                                        $methods = [
                                            'dinheiro'       => ['emoji' => '💵', 'label' => 'Dinheiro',      'color' => 'emerald'],
                                            'pix'            => ['emoji' => '⚡', 'label' => 'PIX',           'color' => 'violet'],
                                            'cartao_debito'  => ['emoji' => '💳', 'label' => 'Débito',        'color' => 'blue'],
                                            'cartao_credito' => ['emoji' => '💳', 'label' => 'Crédito',       'color' => 'indigo'],
                                            'transferencia'  => ['emoji' => '🏦', 'label' => 'Transfer.',     'color' => 'cyan'],
                                            'cheque'         => ['emoji' => '🧾', 'label' => 'Cheque',        'color' => 'amber'],
                                        ];
                                    @endphp
                                    @foreach($methods as $value => $meta)
                                        <label class="method-btn-label cursor-pointer">
                                            <input type="radio" wire:model="newPayments.{{ $index }}.payment_method"
                                                   value="{{ $value }}" class="sr-only peer">
                                            <div class="flex flex-col items-center justify-center gap-1 p-2.5 rounded-xl border-2
                                                        border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900
                                                        peer-checked:border-{{ $meta['color'] }}-500 peer-checked:bg-{{ $meta['color'] }}-50 dark:peer-checked:bg-{{ $meta['color'] }}-900/30
                                                        peer-checked:shadow-md hover:border-{{ $meta['color'] }}-300 transition-all duration-150 select-none">
                                                <span class="text-xl leading-none">{{ $meta['emoji'] }}</span>
                                                <span class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 peer-checked:text-{{ $meta['color'] }}-700 dark:peer-checked:text-{{ $meta['color'] }}-300 leading-none text-center">{{ $meta['label'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Valor + Data --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Valor (R$)</label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-sm">R$</span>
                                            <input type="number" step="0.01" min="0.01"
                                                   wire:model="newPayments.{{ $index }}.amount_paid"
                                                   placeholder="0,00"
                                                   class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl text-base font-bold bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-0 focus:border-emerald-500 transition-colors">
                                        </div>
                                        @if($sale->remaining_amount > 0)
                                            <button type="button"
                                                    wire:click="$set('newPayments.{{ $index }}.amount_paid', '{{ number_format($sale->remaining_amount, 2, '.', '') }}')"
                                                    class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-medium">
                                                Usar restante (R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }})
                                            </button>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Data</label>
                                        <input type="date"
                                               wire:model="newPayments.{{ $index }}.payment_date"
                                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl text-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-0 focus:border-emerald-500 transition-colors">
                                    </div>
                                </div>

                                @if(count($newPayments) > 1)
                                    <div class="flex justify-end mt-3">
                                        <button wire:click="removePaymentRow({{ $index }})"
                                                class="inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium transition-colors">
                                            <i class="bi bi-trash"></i> Remover linha
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="flex items-center justify-between pt-1">
                            <button wire:click="addPaymentRow"
                                    class="inline-flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-semibold transition-colors">
                                <i class="bi bi-plus-square"></i> Outra linha
                            </button>
                            <button wire:click="addPayments"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-2xl transition-all text-sm shadow-xl hover:shadow-2xl transform hover:scale-105">
                                <i class="bi bi-check-circle-fill text-base"></i> Confirmar Pagamento
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Listagens --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @if($sale->tipo_pagamento === 'parcelado' && $sale->parcelasVenda && $sale->parcelasVenda->count() > 0)
                    <x-installments-list :parcelas="$sale->parcelasVenda" />
                @endif
                <x-payments-list :sale="$sale" />
            </div>
        </div>

        {{-- ========================================================
             SEÇÃO 5 — PRODUTOS
        ======================================================== --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 bg-blue-500/10 rounded-xl">
                    <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Produtos da Venda</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $sale->saleItems->count() }} {{ $sale->saleItems->count() === 1 ? 'item' : 'itens' }}
                        · Subtotal: R$ {{ number_format($sale->saleItems->sum(fn($i) => $i->quantity * $i->price_sale), 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <x-sale-products-grid :sale="$sale" />
        </div>

    </div>{{-- end .sale-main-content --}}

    {{-- ============================================================
         MODAIS
    ============================================================ --}}

    {{-- Modal de pagamento de parcela --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-zinc-800 rounded-3xl shadow-2xl w-full max-w-md mx-auto border border-gray-200 dark:border-zinc-700 overflow-hidden">
            <div class="relative p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-b border-green-100 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl shadow-lg">
                            <i class="bi bi-credit-card text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirmar Pagamento</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Parcela {{ $selectedParcela?->numero_parcela ?? 0 }}</p>
                        </div>
                    </div>
                    <button wire:click="closePaymentModal" class="p-2 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-xl transition-colors">
                        <i class="bi bi-x-lg text-gray-500 dark:text-gray-400"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="text-center p-4 bg-gradient-to-r from-gray-50 to-white dark:from-zinc-700 dark:to-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-600">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Valor da parcela</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($selectedParcela?->valor ?? 0, 2, ',', '.') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="bi bi-credit-card mr-1"></i>Método de Pagamento
                    </label>
                    <select wire:model="paymentMethod"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                        <option value="dinheiro">💵 Dinheiro</option>
                        <option value="cartao_debito">💳 Cartão de Débito</option>
                        <option value="cartao_credito">💳 Cartão de Crédito</option>
                        <option value="pix">⚡ PIX</option>
                        <option value="transferencia">🏦 Transferência</option>
                        <option value="cheque">🧾 Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="bi bi-calendar-event mr-1"></i>Data do Pagamento
                    </label>
                    <input type="date" wire:model="paymentDate"
                           class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="p-6 bg-gray-50 dark:bg-zinc-900/50 border-t border-gray-100 dark:border-zinc-700 flex gap-3">
                <button wire:click="closePaymentModal"
                        class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 rounded-xl transition-all font-semibold">
                    <i class="bi bi-x-circle mr-2"></i>Cancelar
                </button>
                <button wire:click="confirmPayment"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all shadow-lg font-semibold">
                    <i class="bi bi-check-circle mr-2"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de desconto --}}
    @if($showDiscountModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-5">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-tag text-amber-600 dark:text-amber-400 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Aplicar Desconto</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Isto irá aplicar um desconto equivalente ao valor restante (<strong>R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}</strong>) e zerar o saldo pendente. Deseja continuar?</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="cancelDiscount" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all">Cancelar</button>
                        <button wire:click="applyDiscountToZero" class="flex-1 px-4 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white rounded-xl font-bold transition-all shadow-lg">Confirmar Desconto</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modais de remoção de item (Alpine.js) --}}
    @if($sale->saleItems->count() > 0)
        @foreach($sale->saleItems as $item)
        <div x-data="{ modalOpen: false }"
             x-show="modalOpen"
             x-on:show-modal-{{ $item->id }}.window="modalOpen = true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[99999] overflow-y-auto">
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                     x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
                     class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-br from-pink-400/20 to-red-600/20 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="text-center pt-8 pb-4">
                            <div class="relative inline-flex items-center justify-center">
                                <div class="absolute w-24 h-24 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-full animate-pulse"></div>
                                <div class="absolute w-20 h-20 bg-gradient-to-r from-red-500/40 to-pink-600/40 rounded-full animate-ping"></div>
                                <div class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-exclamation-triangle text-2xl text-white animate-bounce"></i>
                                </div>
                            </div>
                            <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                <i class="bi bi-cart-x text-red-500 mr-2"></i>Remover Produto
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                <i class="bi bi-info-circle text-amber-500 mr-1"></i>Esta ação não pode ser desfeita
                            </p>
                        </div>
                        <div class="px-8 pb-4">
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-4 border border-red-200/50 dark:border-red-700/50 text-center">
                                <i class="bi bi-box-seam text-3xl text-red-500 mb-2"></i>
                                <p class="text-gray-700 dark:text-gray-300 mb-2">Você está prestes a remover:</p>
                                <p class="font-bold text-red-600 dark:text-red-400 text-lg">"{{ $item->product->name }}"</p>
                                <div class="mt-3 flex items-center justify-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span><i class="bi bi-hash mr-1"></i>{{ $item->quantity }}x</span>
                                    <span><i class="bi bi-currency-dollar mr-1"></i>R$ {{ number_format($item->price_sale, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-8 pb-8">
                            <div class="flex gap-4">
                                <button @click="modalOpen = false"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 dark:border-gray-600 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-x-circle mr-2"></i>Cancelar
                                </button>
                                <button @click="modalOpen = false"
                                        wire:click="removeSaleItem({{ $item->id }})"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all border-2 border-red-400/50">
                                    <i class="bi bi-cart-dash mr-2"></i>Remover
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @endif

    <style>
    /* ── Animações ─────────────────────────────────────────────── */
    .animate-fade-in {
        animation: fadeInUp 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .stagger-item {
        animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    .stagger-item:nth-child(1) { animation-delay: 0.05s; }
    .stagger-item:nth-child(2) { animation-delay: 0.10s; }
    .stagger-item:nth-child(3) { animation-delay: 0.15s; }
    .stagger-item:nth-child(4) { animation-delay: 0.20s; }
    .stagger-item:nth-child(5) { animation-delay: 0.25s; }

    /* ── Payment form modern styles ─────────────────────────── */
    .method-btn-label .peer-checked + div,
    .method-btn-label input:checked ~ div {
        transform: scale(1.04);
    }
    .method-picker label:hover div {
        transform: translateY(-1px);
    }

    /* ── Show cards hover ──────────────────────────────────────── */
    .show-card {
        transition: box-shadow 0.2s ease;
    }
    .show-card:hover {
        box-shadow: 0 10px 30px -5px rgba(0,0,0,0.10), 0 4px 10px -3px rgba(0,0,0,0.05);
    }

    /* ── Scrollbar customizado ─────────────────────────────────── */
    .overflow-y-auto::-webkit-scrollbar { width: 4px; }
    .overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.35);
        border-radius: 2px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.55);
    }

    /* ── Container padding ─────────────────────────────────────── */
    .sale-main-content {
        padding-top: 0.5rem;
    }
    </style>
</div>
