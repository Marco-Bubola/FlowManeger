<div class="w-full min-h-screen app-viewport-fit add-payments-page mobile-393-base">

    <x-sales-header
        title="Adicionar Pagamentos"
        :description="'Venda #' . $sale->id . ' &middot; ' . ($sale->client->name ?? 'Cliente não informado')"
        :back-route="route('sales.show', $sale->id)"
        :current-step="1"
        :steps="[]">
        <x-slot name="actions">
            <button wire:click="addPaymentRow"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 font-semibold rounded-2xl transition-all duration-200 shadow text-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span class="hidden sm:inline">Nova Linha</span>
            </button>
            <button wire:click="addPayments"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                <i class="bi bi-check-circle-fill text-base"></i>
                <span class="hidden sm:inline">Confirmar</span>
            </button>
        </x-slot>
    </x-sales-header>

    {{-- ============================================================
         CONTEÚDO PRINCIPAL
    ============================================================ --}}
    <div class="add-payments-main container-fluid mx-auto px-4 sm:px-6 pb-16">

        {{-- Barra de resumo financeiro compacta (Total | Pago | Restante) --}}
        @php
            $apStatus  = $sale->remaining_amount <= 0 ? 'pago' : ($sale->total_paid > 0 ? 'parcial' : 'pendente');
            $apPercent = $sale->total_price > 0 ? min(100, ($sale->total_paid / $sale->total_price) * 100) : 0;
        @endphp
        <div class="mt-5 mb-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-md border border-gray-100 dark:border-zinc-700/60 overflow-hidden">
            <div class="grid grid-cols-3">
                <div class="px-4 py-3.5 text-center border-r border-gray-100 dark:border-zinc-700/60">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">Total</p>
                    <p class="text-sm font-extrabold text-gray-900 dark:text-white">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3.5 text-center border-r border-gray-100 dark:border-zinc-700/60">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">Pago</p>
                    <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($sale->total_paid, 2, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3.5 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">Restante</p>
                    <p class="text-sm font-extrabold {{ $sale->remaining_amount <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        R$ {{ number_format($sale->remaining_amount, 2, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="h-1.5 bg-gray-100 dark:bg-zinc-800">
                <div class="h-full transition-all duration-1000
                    @if($apStatus === 'pago') bg-gradient-to-r from-emerald-400 to-green-500
                    @elseif($apStatus === 'parcial') bg-gradient-to-r from-amber-400 to-orange-400
                    @else bg-gradient-to-r from-red-400 to-rose-400 @endif"
                     style="width: {{ number_format($apPercent, 2, '.', '') }}%"></div>
            </div>
        </div>

        {{-- Layout 2 colunas no tablet+: formulários | sidebar --}}
        <div class="add-payments-layout">

            {{-- Coluna principal: cards de pagamento --}}
            <div class="add-payments-forms">
                @foreach($payments as $index => $payment)
                    <x-payment-form-card
                        :index="$index"
                        :payment="$payment"
                        :showRemove="count($payments) > 1"
                        :remainingAmount="$this->remainingAmount" />
                @endforeach

                <button wire:click="addPaymentRow"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-zinc-900 border-2 border-dashed border-emerald-300 dark:border-emerald-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 text-emerald-600 dark:text-emerald-400 font-semibold rounded-2xl transition-all duration-200 text-sm shadow-sm">
                    <i class="bi bi-plus-circle"></i>
                    Adicionar outra linha de pagamento
                </button>
            </div>

            {{-- Sidebar: resumo do lançamento + ações --}}
            <div class="add-payments-sidebar">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-md border border-gray-100 dark:border-zinc-700/60 overflow-hidden sticky top-4">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-slate-700 to-slate-800 flex items-center gap-2">
                        <i class="bi bi-calculator text-white/80 text-sm"></i>
                        <span class="text-xs font-bold text-white/80 uppercase tracking-widest">Resumo do Lançamento</span>
                    </div>
                    <div class="p-5 space-y-3">
                        @php
                            $apTotal = collect($payments)->sum(fn($p) => is_numeric($p['amount_paid'] ?? 0) ? (float)$p['amount_paid'] : 0);
                            $apNewRemaining = $this->remainingAmount - $apTotal;
                        @endphp

                        <div class="flex items-center justify-between py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-xl">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Pendente atual</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white">R$ {{ number_format($this->remainingAmount, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 px-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">Este lançamento</span>
                            <span class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400">R$ {{ number_format($apTotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 px-3 rounded-xl
                            {{ $apNewRemaining <= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                            <span class="text-xs font-medium
                                {{ $apNewRemaining <= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                {{ $apNewRemaining <= 0 ? 'Ficará quitado' : 'Ficará pendente' }}
                            </span>
                            <span class="text-sm font-extrabold
                                {{ $apNewRemaining <= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                R$ {{ number_format(abs($apNewRemaining), 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="pt-1 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full
                                {{ $apNewRemaining <= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' }}">
                                <i class="bi bi-{{ $apNewRemaining <= 0 ? 'check-circle-fill' : 'clock-fill' }}"></i>
                                {{ $apNewRemaining <= 0 ? 'Venda Quitada' : 'Pagamento Parcial' }}
                            </span>
                        </div>

                        <button wire:click="addPayments"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-2xl transition-all text-sm shadow-xl hover:shadow-2xl transform hover:scale-[1.02]">
                            <i class="bi bi-check-circle-fill" wire:loading.remove wire:target="addPayments"></i>
                            <i class="bi bi-arrow-clockwise animate-spin" wire:loading wire:target="addPayments"></i>
                            <span wire:loading.remove wire:target="addPayments">Confirmar Pagamentos</span>
                            <span wire:loading wire:target="addPayments">Processando...</span>
                        </button>

                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-600 dark:text-gray-400 font-medium rounded-xl transition-all text-sm">
                            <i class="bi bi-x-lg text-xs"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>{{-- end sidebar --}}

        </div>{{-- end .add-payments-layout --}}
    </div>

    <style>
    [x-cloak] { display: none !important; }

    /* ── Layout ────────────────────────────────────────────────── */
    .add-payments-page .add-payments-layout {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .add-payments-page .add-payments-forms {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .add-payments-page .add-payments-sidebar {
        width: 100%;
    }

    @media (min-width: 768px) {
        .add-payments-page .add-payments-layout {
            flex-direction: row;
            align-items: flex-start;
        }
        .add-payments-page .add-payments-sidebar {
            width: 300px;
            flex-shrink: 0;
        }
    }
    @media (min-width: 1024px) {
        .add-payments-page .add-payments-sidebar {
            width: 340px;
        }
    }
    @media (min-width: 1280px) {
        .add-payments-page .add-payments-sidebar {
            width: 380px;
        }
    }

    /* ── Method picker ─────────────────────────────────────────── */
    .add-payments-page .method-btn-label .peer-checked + div,
    .add-payments-page .method-btn-label input:checked ~ div {
        transform: scale(1.04);
    }
    .add-payments-page .method-picker label:hover div {
        transform: translateY(-1px);
    }

    /* ── Animação entrada dos cards ────────────────────────────── */
    .add-payments-page .payment-row-card {
        animation: apFadeInUp 0.35s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    @keyframes apFadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    </style>
</div>
