<div>
    <!-- Botão para abrir modal -->
    <button wire:click="openModal"
        class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 rounded-lg text-sm font-medium transition-colors duration-200"
        title="Cancelar e reverter pagamento">
        <i class="bi bi-x-circle"></i>
        <span>Cancelar</span>
    </button>

    <!-- Modal de Confirmação -->
    @if ($showModal)
        <div x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true"
             @keydown.escape.window="show = false; $wire.closeModal()">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">

                    <!-- Header com Gradiente -->
                    <div class="relative bg-gradient-to-r from-red-500 to-pink-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="bi bi-exclamation-triangle-fill text-2xl text-white"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white" id="modal-title">
                                    Cancelar Pagamento
                                </h3>
                            </div>
                            <button wire:click="closeModal"
                                class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-colors">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6 space-y-4">
                        <!-- Aviso -->
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex gap-3">
                                <i class="bi bi-exclamation-circle-fill text-red-600 dark:text-red-400 text-xl flex-shrink-0"></i>
                                <div class="space-y-2">
                                    <p class="text-sm font-semibold text-red-900 dark:text-red-100">
                                        Atenção! Esta ação não pode ser desfeita.
                                    </p>
                                    <p class="text-sm text-red-700 dark:text-red-300">
                                        O pagamento será revertido para o status <strong>PENDENTE</strong> e as seguintes ações serão realizadas:
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalhes do Pagamento -->
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 space-y-3">
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">Detalhes do Pagamento:</h4>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Participante:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $payment->participant->client->name ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Valor:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Data Pagamento:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Método:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Ações -->
                        <div class="space-y-2">
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100">Ações que serão executadas:</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-500 mt-0.5"></i>
                                    <span class="text-slate-700 dark:text-slate-300">Pagamento voltará ao status <strong>PENDENTE</strong></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-emerald-500 mt-0.5"></i>
                                    <span class="text-slate-700 dark:text-slate-300">Total pago do participante será recalculado</span>
                                </li>
                                @if($payment->participant->is_contemplated && $payment->participant->contemplation_type === 'payoff')
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-orange-500 mt-0.5"></i>
                                        <span class="text-slate-700 dark:text-slate-300">
                                            <strong>Contemplação por quitação será REMOVIDA</strong> (se houver pagamentos pendentes)
                                        </span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-orange-500 mt-0.5"></i>
                                        <span class="text-slate-700 dark:text-slate-300">
                                            Participante voltará ao status <strong>ATIVO</strong>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                        <button wire:click="closeModal"
                            type="button"
                            class="px-5 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            Voltar
                        </button>
                        <button wire:click="cancelPayment"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <i class="bi bi-x-circle" wire:loading.remove></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading></i>
                            <span wire:loading.remove>Sim, Cancelar Pagamento</span>
                            <span wire:loading>Cancelando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
