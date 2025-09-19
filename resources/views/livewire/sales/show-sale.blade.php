<div class="w-full min-h-screen">
    <!-- Header modernizado usando sales-header -->
    <x-sales-header
        title="Venda #{{ $sale->id }}"
        description="Cliente: {{ $sale->client->name ?? 'Cliente não informado' }} | {{ $sale->saleItems->count() }} {{ $sale->saleItems->count() === 1 ? 'item' : 'itens' }} | Total: R$ {{ number_format($sale->total_price, 2, ',', '.') }}"
        :back-route="route('sales.index')"
        :current-step="1"
        :steps="[]">

        <!-- Slot para botões de ação no header -->
        <x-slot name="actions">
            <button wire:click="exportPdf"
                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="bi bi-file-earmark-pdf mr-2 text-lg"></i>
                <span class="hidden md:inline">Exportar PDF</span>
            </button>
        </x-slot>
    </x-sales-header>

    <!-- Sistema de Abas com Design Moderno -->
    <div class="container mx-auto px-6 py-8">
        <div class="mx-auto">
            <!-- Navegação das Abas com Design Melhorado -->
            <div class="mb-8">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 p-2">
                    <nav class="flex space-x-2" aria-label="Abas">
                        <button wire:click="$set('activeTab', 'resumo')"
                                class="flex-1 py-3 px-6 rounded-xl font-semibold text-sm transition-all duration-300 {{ $activeTab === 'resumo' ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-lg transform scale-105' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-zinc-700' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-file-text text-lg"></i>
                                <span>Resumo Geral</span>
                            </div>
                        </button>

                        <button wire:click="$set('activeTab', 'produtos')"
                                class="flex-1 py-3 px-6 rounded-xl font-semibold text-sm transition-all duration-300 {{ $activeTab === 'produtos' ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg transform scale-105' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-zinc-700' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-box-seam text-lg"></i>
                                <span>Produtos</span>
                                <span class="ml-1 bg-white/20 text-white text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $sale->saleItems->count() }}
                                </span>
                            </div>
                        </button>

                        <button wire:click="$set('activeTab', 'pagamentos')"
                                class="flex-1 py-3 px-6 rounded-xl font-semibold text-sm transition-all duration-300 {{ $activeTab === 'pagamentos' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg transform scale-105' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-zinc-700' }}">
                            <div class="flex items-center justify-center gap-2">
                                <i class="bi bi-credit-card text-lg"></i>
                                <span>Pagamentos</span>
                                @if($sale->payments->count() > 0)
                                    <span class="ml-1 bg-white/20 text-white text-xs font-bold px-2 py-1 rounded-full">
                                        {{ $sale->payments->count() }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Conteúdo das Abas -->
            <div class="tab-content">
                @if($activeTab === 'resumo')
                    <!-- Dashboard Completo - Primeira Aba Reestruturada -->
                    <div class="animate-fade-in">
                        <!-- Layout Principal em Grid Responsivo -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 lg:grid-cols-8 gap-6 min-h-[calc(100vh-300px)]">

                            <!-- Coluna 1: Informações do Cliente (3 colunas XL, 3 colunas LG) -->
                            <div class="xl:col-span-3 lg:col-span-3 space-y-4">
                                <!-- Card do Cliente -->
                                <div class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 rounded-2xl p-6 shadow-lg border border-blue-200 dark:border-blue-800 h-full">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-3 bg-blue-500/10 rounded-xl">
                                            <i class="bi bi-person-circle text-blue-600 dark:text-blue-400 text-2xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cliente</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Informações do comprador</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome</p>
                                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $sale->client->name ?? 'Cliente não informado' }}
                                            </p>
                                        </div>

                                        @if($sale->client && $sale->client->email)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 break-all">
                                                {{ $sale->client->email }}
                                            </p>
                                        </div>
                                        @endif

                                        @if($sale->client && $sale->client->phone)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefone</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $sale->client->phone }}
                                            </p>
                                        </div>
                                        @endif

                                        <div class="pt-3 border-t border-blue-200 dark:border-blue-800">
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Data da Venda</p>
                                            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                {{ $sale->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Coluna 2: Status e Resumo Financeiro (4 colunas XL, 2 colunas LG) -->
                            <div class="xl:col-span-4 lg:col-span-2 space-y-4">
                                <!-- Status da Venda -->
                                <div class="bg-gradient-to-br from-purple-50 via-white to-pink-50 dark:from-purple-900/20 dark:via-zinc-900/20 dark:to-pink-900/20 rounded-2xl p-6 shadow-lg border border-purple-200 dark:border-purple-800">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-3 bg-purple-500/10 rounded-xl">
                                            <i class="bi bi-clipboard-check text-purple-600 dark:text-purple-400 text-2xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Status da Venda</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Situação atual</p>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        @php
                                            $status = $sale->remaining_amount <= 0 ? 'pago' : ($sale->total_paid > 0 ? 'parcial' : 'pendente');
                                            $statusColor = $status === 'pago' ? 'green' : ($status === 'parcial' ? 'yellow' : 'red');
                                            $statusText = $status === 'pago' ? 'Pago' : ($status === 'parcial' ? 'Pagamento Parcial' : 'Pendente');
                                            $statusIcon = $status === 'pago' ? 'check-circle-fill' : ($status === 'parcial' ? 'clock-fill' : 'x-circle-fill');
                                        @endphp

                                        <div class="w-20 h-20 mx-auto mb-4 bg-{{ $statusColor }}-500/10 rounded-full flex items-center justify-center">
                                            <i class="bi bi-{{ $statusIcon }} text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 text-3xl"></i>
                                        </div>

                                        <h4 class="text-xl font-bold text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 mb-2">
                                            {{ $statusText }}
                                        </h4>

                                        <div class="grid grid-cols-2 gap-4 text-center">
                                            <div class="bg-white/60 dark:bg-zinc-800/60 rounded-lg p-3">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                                    R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                                </p>
                                            </div>
                                            <div class="bg-white/60 dark:bg-zinc-800/60 rounded-lg p-3">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Pago</p>
                                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    R$ {{ number_format($sale->total_paid, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($sale->total_price > 0)
                                            @php $percentage = ($sale->total_paid / $sale->total_price) * 100; @endphp
                                            <div class="mt-4">
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                                    <div class="h-full bg-gradient-to-r from-{{ $statusColor }}-500 to-{{ $statusColor }}-600 rounded-full transition-all duration-1000"
                                                         style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mt-2">
                                                    {{ number_format($percentage, 1) }}% concluído
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Resumo Rápido -->
                                <div class="bg-gradient-to-br from-gray-50 via-white to-slate-50 dark:from-gray-900/20 dark:via-zinc-900/20 dark:to-slate-900/20 rounded-2xl p-4 shadow-lg border border-gray-200 dark:border-gray-800">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-speedometer2 text-gray-600 dark:text-gray-400"></i>
                                        Resumo Rápido
                                    </h4>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="text-center p-2 bg-white/60 dark:bg-zinc-800/60 rounded">
                                            <p class="text-gray-500 dark:text-gray-400">Itens</p>
                                            <p class="font-bold text-blue-600 dark:text-blue-400">{{ $sale->saleItems->count() }}</p>
                                        </div>
                                        <div class="text-center p-2 bg-white/60 dark:bg-zinc-800/60 rounded">
                                            <p class="text-gray-500 dark:text-gray-400">Pagamentos</p>
                                            <p class="font-bold text-green-600 dark:text-green-400">{{ $sale->payments->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Coluna 3: Produtos e Pagamentos (5 colunas XL, 3 colunas LG) -->
                            <div class="xl:col-span-5 lg:col-span-3 space-y-4">
                                <!-- Produtos -->
                                <div class="bg-gradient-to-br from-emerald-50 via-white to-green-50 dark:from-emerald-900/20 dark:via-zinc-900/20 dark:to-green-900/20 rounded-2xl p-6 shadow-lg border border-emerald-200 dark:border-emerald-800 h-[48%]">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-emerald-500/10 rounded-xl">
                                                <i class="bi bi-box-seam text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                    Produtos ({{ $sale->saleItems->count() }})
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Itens da venda</p>
                                            </div>
                                        </div>
                                        <button wire:click="$set('activeTab', 'produtos')"
                                                class="text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium transition-colors duration-200">
                                            Ver todos <i class="bi bi-arrow-right ml-1"></i>
                                        </button>
                                    </div>

                                    <div class="space-y-3 max-h-40 overflow-y-auto">
                                        @foreach($sale->saleItems->take(4) as $item)
                                            <div class="flex items-center justify-between p-3 bg-white/60 dark:bg-zinc-800/60 rounded-xl hover:bg-white/80 dark:hover:bg-zinc-800/80 transition-colors">
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900 dark:text-white text-sm truncate">
                                                        {{ $item->product->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $item->quantity }}x R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                                <div class="text-right ml-2">
                                                    <p class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                                        R$ {{ number_format($item->price_sale * $item->quantity, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if($sale->saleItems->count() > 4)
                                            <div class="text-center py-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    +{{ $sale->saleItems->count() - 4 }} produtos restantes
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pagamentos -->
                                <div class="bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-amber-900/20 dark:via-zinc-900/20 dark:to-orange-900/20 rounded-2xl p-6 shadow-lg border border-amber-200 dark:border-amber-800 h-[48%]">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-amber-500/10 rounded-xl">
                                                <i class="bi bi-credit-card text-amber-600 dark:text-amber-400 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                    Pagamentos ({{ $sale->payments->count() }})
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Histórico de pagamentos</p>
                                            </div>
                                        </div>
                                        <button wire:click="$set('activeTab', 'pagamentos')"
                                                class="text-sm text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 font-medium transition-colors duration-200">
                                            Ver todos <i class="bi bi-arrow-right ml-1"></i>
                                        </button>
                                    </div>

                                    @if($sale->payments->count() > 0)
                                        <div class="space-y-3 max-h-40 overflow-y-auto">
                                            @foreach($sale->payments->take(4) as $payment)
                                                <div class="flex items-center justify-between p-3 bg-white/60 dark:bg-zinc-800/60 rounded-xl hover:bg-white/80 dark:hover:bg-zinc-800/80 transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                                                            <i class="bi bi-check text-white text-sm"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium text-gray-900 dark:text-white text-sm">
                                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="font-bold text-amber-600 dark:text-amber-400 text-sm">
                                                        R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            @endforeach

                                            @if($sale->payments->count() > 4)
                                                <div class="text-center py-2">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        +{{ $sale->payments->count() - 4 }} pagamentos restantes
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center h-32 text-center">
                                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center mb-3">
                                                <i class="bi bi-credit-card text-gray-400 text-xl"></i>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Nenhum pagamento registrado</p>
                                            <button wire:click="$set('activeTab', 'pagamentos')"
                                                    class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 font-medium">
                                                Adicionar pagamento
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($activeTab === 'produtos')
                    <!-- Aba Produtos -->
                    <div class="space-y-8 animate-fade-in">

                        <x-sale-products-grid :sale="$sale" />
                    </div>
                @endif

                @if($activeTab === 'pagamentos')
                    <!-- Aba Pagamentos -->
                    <div class="space-y-8 animate-fade-in">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Parcelas se existirem -->
                        @if($sale->tipo_pagamento === 'parcelado' && $sale->parcelasVenda && $sale->parcelasVenda->count() > 0)
                            <x-installments-list :parcelas="$sale->parcelasVenda" />
                        @endif
                        <x-payments-list :sale="$sale" />
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Pagamento das Parcelas -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-zinc-800 rounded-3xl shadow-2xl w-full max-w-md mx-auto border border-gray-200 dark:border-zinc-700 overflow-hidden">

            <!-- Header do Modal -->
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

            <!-- Corpo do Modal -->
            <div class="p-6 space-y-6">
                <!-- Valor da Parcela -->
                <div class="text-center p-4 bg-gradient-to-r from-gray-50 to-white dark:from-zinc-700 dark:to-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-600">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Valor da parcela</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        R$ {{ number_format($selectedParcela?->valor ?? 0, 2, ',', '.') }}
                    </p>
                </div>

                <!-- Método de Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        <i class="bi bi-credit-card mr-1"></i>Método de Pagamento
                    </label>
                    <select wire:model="paymentMethod"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                        <option value="dinheiro">💵 Dinheiro</option>
                        <option value="cartao_debito">💳 Cartão de Débito</option>
                        <option value="cartao_credito">💳 Cartão de Crédito</option>
                        <option value="pix">⚡ PIX</option>
                        <option value="transferencia">🏦 Transferência</option>
                        <option value="cheque">🧾 Cheque</option>
                    </select>
                </div>

                <!-- Data do Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        <i class="bi bi-calendar-event mr-1"></i>Data do Pagamento
                    </label>
                    <input type="date" wire:model="paymentDate"
                           class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="p-6 bg-gray-50 dark:bg-zinc-900/50 border-t border-gray-100 dark:border-zinc-700">
                <div class="flex gap-3">
                    <button wire:click="closePaymentModal"
                            class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 rounded-xl transition-all duration-200 font-semibold">
                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                    </button>
                    <button wire:click="confirmPayment"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <i class="bi bi-check-circle mr-2"></i>Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Confirmação de Exclusão -->
    @if($sale->saleItems->count() > 0)
        @foreach($sale->saleItems as $item)
        <div id="popup-modal-{{ $item->id }}" tabindex="-1"
             class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <div class="relative bg-white dark:bg-zinc-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-zinc-700">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-zinc-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
                                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                            </div>
                            Confirmar Exclusão
                        </h3>
                        <button type="button" class="text-gray-400 hover:bg-gray-200 dark:hover:bg-zinc-700 hover:text-gray-900 dark:hover:text-white rounded-xl text-sm w-8 h-8 flex justify-center items-center transition-colors"
                                data-modal-hide="popup-modal-{{ $item->id }}">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Conteúdo -->
                    <div class="p-6 text-center">
                        <p class="text-lg text-gray-900 dark:text-white mb-2">Tem certeza que deseja remover este produto?</p>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-6">{{ $item->product->name }}</p>

                        <div class="flex gap-3">
                            <button data-modal-hide="popup-modal-{{ $item->id }}"
                                    type="button"
                                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 rounded-xl transition-all duration-200 font-semibold">
                                <i class="bi bi-x-circle mr-2"></i>Cancelar
                            </button>
                            <button data-modal-hide="popup-modal-{{ $item->id }}"
                                    type="button"
                                    wire:click="removeSaleItem({{ $item->id }})"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                                <i class="bi bi-trash mr-2"></i>Sim, remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @endif

    <style>
    .animate-fade-in {
        animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Hover effects para os cards */
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Animação para as abas */
    nav button {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    nav button:hover {
        transform: translateY(-1px);
    }

    nav button.active {
        transform: translateY(-2px) scale(1.02);
    }

    /* Efeito de brilho nas abas ativas */
    .tab-glow {
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
    }

    /* Animação de entrada para os elementos */
    .stagger-item {
        animation: staggerIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .stagger-item:nth-child(1) { animation-delay: 0.1s; }
    .stagger-item:nth-child(2) { animation-delay: 0.2s; }
    .stagger-item:nth-child(3) { animation-delay: 0.3s; }
    .stagger-item:nth-child(4) { animation-delay: 0.4s; }

    @keyframes staggerIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Efeito de pulso para notificações */
    .pulse-glow {
        animation: pulseGlow 2s ease-in-out infinite;
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 5px rgba(34, 197, 94, 0.4);
        }
        50% {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.6);
        }
    }

    /* Layout compacto para a aba resumo */
    .tab-content {
        min-height: calc(100vh - 320px);
        max-height: calc(100vh - 280px);
        overflow: hidden;
    }

    /* Scrollbar customizado */
    .overflow-y-auto::-webkit-scrollbar {
        width: 4px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.3);
        border-radius: 2px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.5);
    }

    /* Layout responsivo para telas menores */
    @media (max-width: 1280px) {
        .xl\:grid-cols-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .lg\:grid-cols-3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .tab-content {
            min-height: auto;
            max-height: none;
            overflow: visible;
        }
    }

    /* Efeitos de transição para as abas */
    .tab-button {
        position: relative;
        transition: all 0.3s ease;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(135deg, #8b5cf6, #ec4899);
        border-radius: 2px 2px 0 0;
        animation: slideIn 0.3s ease-out forwards;
    }

    @keyframes slideIn {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }
    </style>
</div>
