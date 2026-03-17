@props(['index', 'payment' => [], 'showRemove' => false, 'remainingAmount' => 0])

<div class="payment-row-card bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-md border border-gray-100 dark:border-zinc-700/60">

    {{-- Cabeçalho do card --}}
    <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="bi bi-credit-card-2-front text-white text-sm"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Pagamento {{ $index + 1 }}</h4>
                <p class="text-[10px] text-white/70">Preencha os dados abaixo</p>
            </div>
        </div>
        @if($showRemove)
            <button type="button"
                    wire:click="removePaymentRow({{ $index }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white/20 hover:bg-white/35 text-white text-xs font-bold rounded-lg transition-colors border border-white/30">
                <i class="bi bi-trash3"></i>
                <span class="hidden sm:inline">Remover</span>
            </button>
        @endif
    </div>

    <div class="p-5">
        {{-- Seletor visual de método --}}
        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2.5">Método de Pagamento</p>
        <div class="method-picker grid grid-cols-3 sm:grid-cols-6 gap-2 mb-4">
            @php
                $methods = [
                    'dinheiro'       => ['emoji' => '💵', 'label' => 'Dinheiro',   'color' => 'emerald'],
                    'pix'            => ['emoji' => '⚡', 'label' => 'PIX',        'color' => 'violet'],
                    'cartao_debito'  => ['emoji' => '💳', 'label' => 'Débito',     'color' => 'blue'],
                    'cartao_credito' => ['emoji' => '💳', 'label' => 'Crédito',    'color' => 'indigo'],
                    'transferencia'  => ['emoji' => '🏦', 'label' => 'Transfer.',  'color' => 'cyan'],
                    'cheque'         => ['emoji' => '🧾', 'label' => 'Cheque',     'color' => 'amber'],
                ];
            @endphp
            @foreach($methods as $value => $meta)
                <label class="method-btn-label cursor-pointer">
                    <input type="radio"
                           wire:model="payments.{{ $index }}.payment_method"
                           value="{{ $value }}"
                           class="sr-only peer">
                    <div class="flex flex-col items-center justify-center gap-1 p-2.5 rounded-xl border-2
                                border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900
                                peer-checked:border-{{ $meta['color'] }}-500 peer-checked:bg-{{ $meta['color'] }}-50 dark:peer-checked:bg-{{ $meta['color'] }}-900/30
                                peer-checked:shadow-md hover:border-{{ $meta['color'] }}-300 transition-all duration-150 select-none">
                        <span class="text-xl leading-none">{{ $meta['emoji'] }}</span>
                        <span class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 leading-none text-center">{{ $meta['label'] }}</span>
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Valor + Data --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Valor (R$)</label>
                <div x-data="{
                        displayValue: '{{ isset($payment['amount_paid']) && $payment['amount_paid'] ? number_format((float)$payment['amount_paid'], 2, ',', '.') : '0,00' }}',
                        formatCurrency(value) {
                            let numeric = value.replace(/\D/g, '');
                            if (!numeric || numeric === '0') return '0,00';
                            let cents = parseInt(numeric);
                            return (cents / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }" class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-sm pointer-events-none">R$</span>
                    <input type="text"
                           x-model="displayValue"
                           x-on:input="
                               displayValue = formatCurrency($event.target.value);
                               let numericValue = displayValue.replace(/\./g, '').replace(',', '.');
                               @this.set('payments.{{ $index }}.amount_paid', numericValue);
                           "
                           x-on:focus="if (displayValue === '0,00') $event.target.select()"
                           x-on:blur="if (!displayValue) displayValue = '0,00'"
                           wire:ignore
                           placeholder="0,00"
                           class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl text-base font-bold bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-0 focus:border-emerald-500 transition-colors">
                </div>
                @if($remainingAmount > 0)
                    <button type="button"
                            wire:click="$set('payments.{{ $index }}.amount_paid', '{{ number_format((float)$remainingAmount, 2, '.', '') }}')"
                            class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-medium">
                        Usar restante (R$ {{ number_format((float)$remainingAmount, 2, ',', '.') }})
                    </button>
                @endif
                @error("payments.{$index}.amount_paid")
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Data</label>
                <input type="date"
                       wire:model="payments.{{ $index }}.payment_date"
                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-zinc-600 rounded-xl text-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-0 focus:border-emerald-500 transition-colors">
            </div>
        </div>
    </div>
</div>
