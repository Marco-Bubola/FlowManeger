<div class="w-full min-h-screen app-viewport-fit add-payments-page mobile-393-base">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/add-payments-ultrawide.css') }}">

    <x-sales-header
        title="Adicionar Pagamentos"
        :description="'Venda #' . $sale->id . ' &middot; ' . ($sale->client->name ?? 'Cliente não informado')"
        :back-route="route('sales.show', $sale->id)"
        :current-step="1"
        :steps="[]">
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
        

        {{-- Layout 2 colunas no tablet+: formulários | sidebar --}}
        <div class="add-payments-layout mt-5 mb-6">

            {{-- Coluna principal: cards de pagamento com scroll --}}
            <div class="add-payments-forms">
                <div class="add-payments-cards-scroll">
                    @foreach($payments as $index => $payment)
                        <x-payment-form-card
                            :index="$index"
                            :payment="$payment"
                            :showRemove="count($payments) > 1"
                            :remainingAmount="$this->remainingAmount" />
                    @endforeach
                </div>

                <button wire:click="addPaymentRow"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-zinc-900 border-2 border-dashed border-indigo-300 dark:border-indigo-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 font-semibold rounded-2xl transition-all duration-200 text-sm shadow-sm">
                    <i class="bi bi-plus-circle"></i>
                    Adicionar outra linha de pagamento
                </button>
            </div>

            {{-- Sidebar: resumo do lançamento + ações --}}
            <div class="add-payments-sidebar">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-md border border-gray-100 dark:border-zinc-700/60 overflow-hidden sticky top-4">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-white/90 text-sm"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-widest">Resumo do Lançamento</span>
                    </div>
                    <div class="p-5 space-y-3">
                        @php
                            $apTotal = collect($payments)->sum(fn($p) => is_numeric($p['amount_paid'] ?? 0) ? (float)$p['amount_paid'] : 0);
                            $apNewRemaining = $this->remainingAmount - $apTotal;

                            // Variáveis para o gráfico pizza SVG
                            $pieTotal      = max($sale->total_price, 0.01);
                            $piePago       = min($sale->total_paid, $pieTotal);
                            $pieLancamento = min(max($apTotal, 0), $pieTotal - $piePago);
                            $pieRestante   = max($pieTotal - $piePago - $pieLancamento, 0);

                            $r = 40; $cx = 50; $cy = 50;
                            $circumference = 2 * M_PI * $r;

                            $pctPago       = $piePago / $pieTotal;
                            $pctLancamento = $pieLancamento / $pieTotal;
                            $pctRestante   = $pieRestante / $pieTotal;

                            $dashPago       = $circumference * $pctPago;
                            $dashLancamento = $circumference * $pctLancamento;
                            $dashRestante   = $circumference * $pctRestante;

                            // offsets: começar do topo (-90° = offset negativo)
                            $offPago       = -$circumference * 0.25; // -90 graus
                            $offLancamento = $offPago + ($circumference - $dashPago);
                            $offRestante   = $offLancamento + ($circumference - $dashLancamento);
                        @endphp

                        {{-- Gráfico Pizza SVG --}}
                        <div class="flex items-center justify-center py-2">
                            <div class="relative w-28 h-28">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    {{-- Fundo (restante) --}}
                                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                            fill="none" stroke-width="18"
                                            class="stroke-red-400 dark:stroke-red-500"
                                            stroke-dasharray="{{ number_format($dashRestante, 4, '.', '') }} {{ number_format($circumference - $dashRestante, 4, '.', '') }}"
                                            stroke-dashoffset="{{ number_format($offRestante, 4, '.', '') }}"/>
                                    {{-- Lançamento atual --}}
                                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                            fill="none" stroke-width="18"
                                            class="stroke-indigo-400 dark:stroke-indigo-400"
                                            stroke-dasharray="{{ number_format($dashLancamento, 4, '.', '') }} {{ number_format($circumference - $dashLancamento, 4, '.', '') }}"
                                            stroke-dashoffset="{{ number_format($offLancamento, 4, '.', '') }}"/>
                                    {{-- Já pago --}}
                                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                            fill="none" stroke-width="18"
                                            class="stroke-emerald-400 dark:stroke-emerald-400"
                                            stroke-dasharray="{{ number_format($dashPago, 4, '.', '') }} {{ number_format($circumference - $dashPago, 4, '.', '') }}"
                                            stroke-dashoffset="{{ number_format($offPago, 4, '.', '') }}"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-lg font-extrabold text-gray-900 dark:text-white leading-none">
                                        {{ number_format(($pctPago + $pctLancamento) * 100, 0) }}%
                                    </span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">quitado</span>
                                </div>
                            </div>
                        </div>

                        {{-- Legenda --}}
                        <div class="flex items-center justify-center gap-4 pb-1">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Pago</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-indigo-400"></div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Este lanc.</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Restante</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-xl">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Pendente atual</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white">R$ {{ number_format($this->remainingAmount, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 px-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                            <span class="text-xs text-indigo-700 dark:text-indigo-400 font-medium">Este lançamento</span>
                            <span class="text-sm font-extrabold text-indigo-700 dark:text-indigo-400">R$ {{ number_format($apTotal, 2, ',', '.') }}</span>
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
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-white font-bold rounded-2xl transition-all text-sm shadow-xl hover:shadow-2xl transform hover:scale-[1.02]">
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

    /* ── Scroll container dos cards ──────────────────────────── */
    .add-payments-page .add-payments-cards-scroll {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-height: calc(100vh - 260px);
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px; /* evita corte de sombra */
        scrollbar-width: thin;
        scrollbar-color: #a5b4fc #f1f5f9;
    }
    .dark .add-payments-page .add-payments-cards-scroll {
        scrollbar-color: #4f46e5 #27272a;
    }
    .add-payments-page .add-payments-cards-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .add-payments-page .add-payments-cards-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 99px;
    }
    .dark .add-payments-page .add-payments-cards-scroll::-webkit-scrollbar-track {
        background: #27272a;
    }
    .add-payments-page .add-payments-cards-scroll::-webkit-scrollbar-thumb {
        background: #a5b4fc;
        border-radius: 99px;
    }
    .dark .add-payments-page .add-payments-cards-scroll::-webkit-scrollbar-thumb {
        background: #4f46e5;
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
