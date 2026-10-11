<div class="edit-payments-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16 space-y-5">
    <x-sale-page-header :sale="$sale" title="Pagamentos" active="pagamentos" :back-route="route('sales.show', $sale->id)">
        @if(count($payments) > 0)
            <x-slot:actions>
                <button data-mobile-save type="button" wire:click="updatePayments" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>Salvar alterações
                </button>
            </x-slot:actions>
        @endif
    </x-sale-page-header>

    @foreach(['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300', 'error' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-300'] as $flash => $flashClass)
        @if(session($flash))
            <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium {{ $flashClass }}">
                <i class="bi {{ $flash === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} text-lg"></i>{{ session($flash) }}
            </div>
        @endif
    @endforeach

    @php $difference = (float) $sale->total_price - (float) $this->totalPayments; @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 [&>*:last-child:nth-child(odd)]:col-span-2 sm:[&>*:last-child:nth-child(odd)]:col-span-1">
        <x-gestao-stat label="Total da venda" :value="'R$ ' . number_format($sale->total_price, 2, ',', '.')" icon="bi-cash-stack" tone="indigo" />
        <x-gestao-stat label="Soma dos pagamentos" :value="'R$ ' . number_format($this->totalPayments, 2, ',', '.')" icon="bi-wallet2" tone="emerald" hint="como está salvo agora" />
        <x-gestao-stat label="Falta receber" :value="'R$ ' . number_format(max(0, $difference), 2, ',', '.')" icon="bi-hourglass-split" :tone="$difference > 0 ? 'rose' : 'slate'" />
    </div>

    @if(count($payments) > 0)
        <form wire:submit.prevent="updatePayments">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($payments as $index => $payment)
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm" wire:key="edit-payment-{{ $payment['id'] ?? $index }}">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-sm text-indigo-700 dark:text-indigo-300">{{ $index + 1 }}</span>
                                Pagamento {{ $index + 1 }}
                            </span>
                            <button type="button" wire:click="removePayment({{ $index }})" wire:confirm="Remover este pagamento?" title="Remover pagamento"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 transition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Valor</label>
                            <x-money-input bare :model="'payments.'.$index.'.amount_paid'" :value="$payment['amount_paid'] ?? 0" :live="false"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400" />
                            @error("payments.{$index}.amount_paid")
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Forma</label>
                                <select wire:model="payments.{{ $index }}.payment_method"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400">
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="cartao_debito">Cartão de débito</option>
                                    <option value="cartao_credito">Cartão de crédito</option>
                                    <option value="pix">PIX</option>
                                    <option value="transferencia">Transferência</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Data</label>
                                <input type="date" wire:model="payments.{{ $index }}.payment_date"
                                       class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <a href="{{ route('sales.show', $sale->id) }}"
                   class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancelar</a>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>Salvar alterações
                </button>
            </div>
        </form>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
            <i class="bi bi-credit-card text-3xl"></i>
            <p class="mt-2 font-semibold text-slate-700 dark:text-slate-200">Nenhum pagamento registrado</p>
            <a href="{{ route('sales.add-payments', $sale->id) }}"
               class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition">
                <i class="bi bi-plus-lg"></i>Adicionar pagamento
            </a>
        </div>
    @endif
</div>
