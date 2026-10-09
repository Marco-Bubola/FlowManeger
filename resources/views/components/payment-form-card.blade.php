@props(['index', 'payment' => [], 'showRemove' => false, 'remainingAmount' => 0])

<div class="payment-row-card bg-white dark:bg-slate-900/80 rounded-2xl overflow-hidden shadow-sm border border-slate-200/80 dark:border-slate-700/70">

    {{-- Cabeçalho do card --}}
    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-sm font-bold text-white shadow-md">{{ $index + 1 }}</span>
            <h4 class="font-bold text-slate-900 dark:text-white">Pagamento {{ $index + 1 }}</h4>
        </div>
        @if($showRemove)
            <button type="button"
                    wire:click="removePaymentRow({{ $index }})"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-slate-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 text-xs font-semibold rounded-lg transition-colors">
                <i class="bi bi-trash3"></i>
                <span class="hidden sm:inline">Remover</span>
            </button>
        @endif
    </div>

    <div class="p-5">
        {{-- Seletor visual de método --}}
        <p class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300"><i class="bi bi-wallet2 text-indigo-500"></i>Forma de pagamento</p>
        <div class="method-picker grid grid-cols-3 sm:grid-cols-6 gap-2 mb-4">
            @php
                // Classes completas (o Tailwind não gera cor montada por variável).
                $methods = [
                    'dinheiro'       => ['icon' => 'bi-cash-stack',     'label' => 'Dinheiro',  'tone' => 'text-emerald-600 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15', 'on' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50/70 dark:peer-checked:bg-emerald-500/10 peer-checked:ring-emerald-500/20'],
                    'pix'            => ['icon' => 'bi-qr-code',        'label' => 'PIX',       'tone' => 'text-teal-600 dark:text-teal-300 bg-teal-50 dark:bg-teal-500/15',             'on' => 'peer-checked:border-teal-500 peer-checked:bg-teal-50/70 dark:peer-checked:bg-teal-500/10 peer-checked:ring-teal-500/20'],
                    'cartao_debito'  => ['icon' => 'bi-credit-card-2-front', 'label' => 'Débito', 'tone' => 'text-sky-600 dark:text-sky-300 bg-sky-50 dark:bg-sky-500/15',                 'on' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50/70 dark:peer-checked:bg-sky-500/10 peer-checked:ring-sky-500/20'],
                    'cartao_credito' => ['icon' => 'bi-credit-card',    'label' => 'Crédito',   'tone' => 'text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/15',     'on' => 'peer-checked:border-indigo-500 peer-checked:bg-indigo-50/70 dark:peer-checked:bg-indigo-500/10 peer-checked:ring-indigo-500/20'],
                    'transferencia'  => ['icon' => 'bi-bank',           'label' => 'Transfer.', 'tone' => 'text-purple-600 dark:text-purple-300 bg-purple-50 dark:bg-purple-500/15',     'on' => 'peer-checked:border-purple-500 peer-checked:bg-purple-50/70 dark:peer-checked:bg-purple-500/10 peer-checked:ring-purple-500/20'],
                    'cheque'         => ['icon' => 'bi-receipt',        'label' => 'Cheque',    'tone' => 'text-amber-600 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/15',         'on' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50/70 dark:peer-checked:bg-amber-500/10 peer-checked:ring-amber-500/20'],
                ];
            @endphp
            @foreach($methods as $value => $meta)
                <label class="method-btn-label cursor-pointer">
                    <input type="radio"
                           wire:model="payments.{{ $index }}.payment_method"
                           value="{{ $value }}"
                           class="sr-only peer">
                    <div class="flex flex-col items-center justify-center gap-1.5 px-2 py-3 rounded-xl border
                                border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60
                                hover:border-slate-300 dark:hover:border-slate-600 peer-checked:ring-2 peer-checked:shadow-sm {{ $meta['on'] }}
                                transition-all duration-150 select-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $meta['tone'] }}"><i class="bi {{ $meta['icon'] }} text-lg"></i></span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 leading-none text-center">{{ $meta['label'] }}</span>
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Valor + Data --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300"><i class="bi bi-cash-coin text-emerald-500"></i>Valor</label>
                {{-- Máscara de centavos: 1 → 0,01 · 12 → 0,12 · 123 → 1,23.
                     O x-data envolve o campo E o botão "Usar restante": o campo
                     é `wire:ignore`, então quem muda o valor pelo servidor
                     precisa atualizar a tela também — senão o usuário vê um
                     valor e o sistema grava outro. --}}
                <div x-data="{
                        cts: {{ (int) round((float) ($payment['amount_paid'] ?? 0) * 100) }},
                        fmt() {
                            let s = String(this.cts).padStart(3, '0');
                            let d = s.slice(-2);
                            let i = s.slice(0, -2).replace(/^0+/, '') || '0';
                            i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            return i + ',' + d;
                        },
                        push() {
                            $wire.set('payments.{{ $index }}.amount_paid', (this.cts / 100).toFixed(2));
                        },
                        inp(e) {
                            let digs = e.target.value.replace(/\D/g, '');
                            this.cts = digs ? parseInt(digs) : 0;
                            e.target.value = this.fmt();
                            this.push();
                        },
                        setValor(centavos) {
                            this.cts = centavos;
                            this.$refs.valor.value = this.fmt();
                            this.push();
                        }
                    }">
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-sm pointer-events-none">R$</span>
                        <input type="text"
                               inputmode="numeric"
                               x-ref="valor"
                               x-init="$el.value = fmt()"
                               x-on:focus="$el.select()"
                               x-on:input="inp($event)"
                               wire:ignore
                               placeholder="0,00"
                               class="w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-base font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    @if($remainingAmount > 0)
                        <button type="button"
                                x-on:click="setValor({{ (int) round((float) $remainingAmount * 100) }})"
                                class="mt-1.5 inline-flex items-center gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition"><i class="bi bi-magic"></i>
                            Usar restante (R$ {{ number_format((float)$remainingAmount, 2, ',', '.') }})
                        </button>
                    @endif
                </div>
                @error("payments.{$index}.amount_paid")
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300"><i class="bi bi-calendar3 text-sky-500"></i>Data do pagamento</label>
                <input type="date"
                       wire:model="payments.{{ $index }}.payment_date"
                       class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition dark:[color-scheme:dark]">
            </div>
        </div>
    </div>
</div>
