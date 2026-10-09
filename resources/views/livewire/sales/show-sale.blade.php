<div class="w-full min-h-screen app-viewport-fit sale-show-page mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/show-sale-ultrawide.css') }}">
    {{-- Camada compacta comum das telas de venda (sempre por último) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sales-compact.css') }}?v=20260806">

    @php
        $percentage  = $sale->total_price > 0 ? min(100, ($sale->total_paid / $sale->total_price) * 100) : 0;

        $tipoLabel = $sale->tipo_pagamento === 'parcelado' ? 'Parcelado' : 'À Vista';
        $tipoIcon  = $sale->tipo_pagamento === 'parcelado' ? 'calendar3' : 'lightning-fill';

        // método de pagamento dos itens para badge
        $methodLabels = [
            'dinheiro' => '💵 Dinheiro', 'cartao_debito' => '💳 Débito',
            'cartao_credito' => '💳 Crédito', 'pix' => '⚡ PIX',
            'transferencia' => '🏦 Transferência', 'cheque' => '🧾 Cheque',
            'desconto' => '🏷️ Desconto', 'parcela' => '📅 Parcela',
        ];
    @endphp

    <div class="sale-main-content w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16 space-y-5">
    <x-sale-page-header :sale="$sale" title="Resumo" active="resumo">
        <x-slot:actions>
            @if($sale->remaining_amount > 0)
                <button wire:click="payFull" wire:confirm="Registrar o pagamento de todo o valor que falta e marcar a venda como paga?"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition">
                    <i class="bi bi-cash-stack"></i>Quitar tudo
                </button>
                <button wire:click="openDiscountModal"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                    <i class="bi bi-tag"></i>Desconto
                </button>
            @endif
            <button wire:click="abrirModalExportacao"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                <i class="bi bi-file-earmark-pdf text-rose-500"></i>Exportar
            </button>
        </x-slot:actions>
    </x-sale-page-header>

        {{-- Flash messages --}}
        @foreach(['success' => ['emerald', 'bi-check-circle-fill'], 'error' => ['rose', 'bi-x-circle-fill'], 'warning' => ['amber', 'bi-exclamation-triangle-fill']] as $flash => [$tone, $flashIcon])
            @if(session($flash))
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium
                    {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : ($tone === 'rose' ? 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300') }}">
                    <i class="bi {{ $flashIcon }} text-lg"></i>{{ session($flash) }}
                </div>
            @endif
        @endforeach

        @php
            $units = (int) $sale->saleItems->sum('quantity');
            $subtotal = $sale->saleItems->sum(fn($i) => $i->quantity * $i->price_sale);
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <x-gestao-stat label="Total da venda" :value="'R$ ' . number_format($sale->total_price, 2, ',', '.')" icon="bi-cash-stack" tone="indigo" :hint="$tipoLabel . ($sale->tipo_pagamento === 'parcelado' && $sale->parcelas ? ' · ' . $sale->parcelas . 'x' : '')" />
            <x-gestao-stat label="Pago" :value="'R$ ' . number_format($sale->total_paid, 2, ',', '.')" icon="bi-check2-circle" tone="emerald" :hint="number_format($percentage, 0) . '% da venda'" />
            <x-gestao-stat label="Falta receber" :value="'R$ ' . number_format($sale->remaining_amount, 2, ',', '.')" icon="bi-hourglass-split" :tone="$sale->remaining_amount > 0 ? 'rose' : 'slate'" />
            <x-gestao-stat label="Itens" :value="$units . ' ' . ($units === 1 ? 'unidade' : 'unidades')" icon="bi-box-seam" tone="sky" :hint="$sale->saleItems->count() . ' ' . ($sale->saleItems->count() === 1 ? 'produto' : 'produtos')" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            {{-- PRODUTOS --}}
            <section class="lg:col-span-2 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 px-4 py-3">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white"><i class="bi bi-bag text-indigo-500 mr-1"></i>Produtos da venda</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Subtotal R$ {{ number_format($subtotal, 2, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('sales.edit-prices', $sale->id) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            <i class="bi bi-currency-dollar"></i><span class="whitespace-nowrap">Editar preços</span>
                        </a>
                        <a href="{{ route('sales.add-products', $sale->id) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                            <i class="bi bi-plus-lg"></i><span class="whitespace-nowrap">Adicionar produto</span>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sale->saleItems as $item)
                        @php $product = $item->product; @endphp
                        <div class="flex items-center gap-3 px-4 py-3" wire:key="show-item-{{ $item->id }}">
                            <x-product-thumb :product="$product" size="h-14 w-14" class="border border-slate-200 dark:border-slate-700" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ ucwords($product->name ?? 'Produto não encontrado') }}</p>
                                <p class="sm:hidden text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-mono">{{ $product->product_code ?? 'N/A' }}</span>
                                    · {{ $item->quantity }} x R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                                    <span class="hidden sm:inline">· custo R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                </p>
                            </div>
                            <span class="hidden sm:inline shrink-0 text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}</span>
                            <button type="button" @click="$dispatch('show-modal-{{ $item->id }}')" title="Remover produto"
                                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 transition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                            <i class="bi bi-box text-3xl"></i>
                            <p class="mt-2">Nenhum produto nesta venda.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="space-y-5">
                {{-- CLIENTE --}}
                <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <x-client-avatar :name="$sale->client->name ?? '?'" :photo="$sale->client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-full" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-slate-900 dark:text-white">{{ $sale->client->name ?? 'Cliente não informado' }}</p>
                            @if($sale->client)
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sale->client->sales()->count() }} vendas no total</p>
                            @endif
                        </div>
                        @if($sale->client)
                            <a href="{{ route('clients.resumo', $sale->client->id) }}" title="Abrir cliente"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 transition">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @endif
                    </div>
                    @if($sale->client && ($sale->client->email || $sale->client->phone || $sale->client->address))
                        <div class="mt-3 space-y-1.5 text-sm text-slate-600 dark:text-slate-300">
                            @if($sale->client->phone)<p class="truncate"><i class="bi bi-telephone mr-2 text-slate-400"></i>{{ $sale->client->phone }}</p>@endif
                            @if($sale->client->email)<p class="truncate"><i class="bi bi-envelope mr-2 text-slate-400"></i>{{ $sale->client->email }}</p>@endif
                            @if($sale->client->address)<p class="truncate"><i class="bi bi-geo-alt mr-2 text-slate-400"></i>{{ $sale->client->address }}</p>@endif
                        </div>
                    @endif
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                            <p class="text-slate-500 dark:text-slate-400">Criada em</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                            <p class="text-slate-500 dark:text-slate-400">Pagamento</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100"><i class="bi bi-{{ $tipoIcon }} mr-0.5"></i>{{ $tipoLabel }}@if($sale->payment_method) · {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}@endif</p>
                        </div>
                    </div>
                </section>

                {{-- PARCELAS --}}
                @if($sale->tipo_pagamento === 'parcelado' && $sale->parcelasVenda && $sale->parcelasVenda->count() > 0)
                    <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
                        <h2 class="border-b border-slate-100 dark:border-slate-800 px-4 py-3 font-bold text-slate-900 dark:text-white"><i class="bi bi-calendar3 text-indigo-500 mr-1"></i>Parcelas ({{ $sale->parcelasVenda->count() }})</h2>
                        <div class="max-h-96 divide-y divide-slate-100 dark:divide-slate-800 overflow-y-auto">
                            @foreach($sale->parcelasVenda as $parcela)
                                @php
                                    $vencimento = \Carbon\Carbon::parse($parcela->data_vencimento);
                                    $parcelaPaga = $parcela->status === 'pago';
                                    $parcelaVencida = ! $parcelaPaga && $vencimento->isPast();
                                @endphp
                                <div class="flex items-center gap-3 px-4 py-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $parcela->numero_parcela }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">R$ {{ number_format($parcela->valor, 2, ',', '.') }}</p>
                                        <p class="text-xs {{ $parcelaVencida ? 'font-semibold text-rose-600' : 'text-slate-500 dark:text-slate-400' }}">Vence {{ $vencimento->format('d/m/Y') }}</p>
                                    </div>
                                    @if($parcelaPaga)
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300"><i class="bi bi-check-circle-fill mr-0.5"></i>Pago</span>
                                    @elseif($parcela->status === 'pendente')
                                        <button type="button" wire:click="openPaymentModal({{ $parcela->id }})"
                                                class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white transition">Pagar</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- PAGAMENTOS --}}
                <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 px-4 py-3">
                        <h2 class="font-bold text-slate-900 dark:text-white"><i class="bi bi-wallet2 text-emerald-500 mr-1"></i>Pagamentos</h2>
                        <div class="flex items-center gap-1">
                            @if($sale->payments->count() > 0)
                                <a href="{{ route('sales.edit-payments', $sale->id) }}" title="Editar pagamentos" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 transition"><i class="bi bi-pencil"></i></a>
                            @endif
                            <a href="{{ route('sales.add-payments', $sale->id) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1.5 text-xs font-semibold text-white transition"><i class="bi bi-plus-lg"></i>Adicionar</a>
                        </div>
                    </div>
                    <div class="relative px-4 py-3">
                        <div class="flex items-start gap-3 pb-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600"><i class="bi bi-cart-plus text-xs"></i></span>
                            <div class="pt-0.5">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Venda criada</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sale->created_at->format('d/m/Y \à\s H:i') }}</p>
                            </div>
                        </div>
                        @forelse($sale->payments->sortBy('payment_date') as $payment)
                            @php $isDiscount = $payment->payment_method === 'desconto'; @endphp
                            <div class="flex items-start gap-3 {{ $loop->last ? '' : 'pb-3' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $isDiscount ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' }}">
                                    <i class="bi {{ $isDiscount ? 'bi-tag' : 'bi-check2' }} text-xs"></i>
                                </span>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $isDiscount ? 'Desconto' : 'Pagamento' }}</p>
                                        <span class="text-sm font-bold {{ $isDiscount ? 'text-amber-600' : 'text-emerald-600' }}">R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $methodLabels[$payment->payment_method] ?? ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        · {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 px-3 py-4 text-center text-sm text-slate-500">Nenhum pagamento ainda.</p>
                        @endforelse
                    </div>
                </section>
            </div>
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
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Falta pagar <strong>R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}</strong>. O desconto abate do total e recalcula as parcelas pendentes.</p>
                        </div>
                    </div>

                    <form wire:submit="applyDiscount" class="space-y-3 mb-4">
                        <div class="flex rounded-xl bg-gray-100 dark:bg-zinc-700 p-1 text-sm font-bold">
                            <button type="button" wire:click="$set('discountType', 'valor')" class="flex-1 py-2 rounded-lg {{ $discountType === 'valor' ? 'bg-white dark:bg-zinc-800 shadow text-amber-600' : 'text-gray-500' }}">Em R$</button>
                            <button type="button" wire:click="$set('discountType', 'percentual')" class="flex-1 py-2 rounded-lg {{ $discountType === 'percentual' ? 'bg-white dark:bg-zinc-800 shadow text-amber-600' : 'text-gray-500' }}">Em %</button>
                        </div>
                        @if ($discountType === 'valor')
                            <x-money-input model="discountValue" :value="0" :live="false" wire:key="discount-valor" />
                        @else
                            <div class="relative" wire:key="discount-percent">
                                <input type="number" min="0" max="100" step="0.5" inputmode="decimal" wire:model="discountValue" placeholder="10"
                                    class="w-full pl-4 pr-10 py-3 rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white font-bold">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</span>
                            </div>
                        @endif
                        @error('discountValue') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white rounded-xl font-bold transition-all shadow-lg">Aplicar desconto</button>
                    </form>

                    <div class="flex gap-3">
                        <button wire:click="cancelDiscount" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all">Cancelar</button>
                        <button wire:click="applyDiscountToZero" wire:confirm="Dar desconto de todo o valor restante?" class="flex-1 px-4 py-3 border-2 border-amber-400 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl font-bold transition-all">Zerar o restante</button>
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
             x-cloak
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
    /* ── Alpine.js cloak (evita flash de modal no carregamento) ── */
    [x-cloak] { display: none !important; }

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

    {{-- mesmo modal de exportacao da listagem de vendas --}}
    @livewire('sales.export-sale-modal')

</div>
