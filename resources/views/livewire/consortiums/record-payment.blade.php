<div>
    <!-- Botão para abrir modal -->
    <button wire:click="openModal"
        class="flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-lg transition-all shadow">
        <i class="bi bi-check-circle-fill"></i>
        <span>Registrar</span>
    </button>

    <!-- Modal -->
    @if ($showModal)
        <div x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            <i class="bi bi-wallet2 text-emerald-600 dark:text-emerald-400"></i>
                            Registrar Pagamento
                        </h3>
                        <button wire:click="closeModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <i class="bi bi-x-lg text-2xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <form wire:submit.prevent="recordPayment" class="p-6 space-y-4">
                        <!-- Informações do Pagamento -->
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Cliente:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $payment->participant->client->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Referência:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $payment->reference_month_name }}/{{ $payment->reference_year }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Vencimento:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-400">Valor:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 text-lg">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Data do Pagamento -->
                        <div>
                            <label for="payment_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Data do Pagamento *
                            </label>
                            <input type="date" wire:model="payment_date" id="payment_date"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            @error('payment_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Método de Pagamento -->
                        <div>
                            <label for="payment_method" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Método de Pagamento *
                            </label>
                            <select wire:model="payment_method" id="payment_method"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartão de Crédito</option>
                                <option value="cartao_debito">Cartão de Débito</option>
                                <option value="transferencia">Transferência Bancária</option>
                                <option value="boleto">Boleto</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observações -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Observações
                            </label>
                            <textarea wire:model="notes" id="notes" rows="2"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none"
                                placeholder="Observações sobre o pagamento..."></textarea>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold rounded-xl transition-all">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all shadow-lg">
                                <i class="bi bi-check-lg"></i> Confirmar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
