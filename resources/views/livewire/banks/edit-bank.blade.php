@php
$bankIcons = [
['name' => 'Nubank', 'icon' => asset('assets/img/banks/nubank.svg')],
['name' => 'Inter', 'icon' => asset('assets/img/banks/inter.png')],
['name' => 'Santander', 'icon' => asset('assets/img/banks/santander.png')],
['name' => 'Itaú', 'icon' => asset('assets/img/banks/itau.png')],
['name' => 'Banco do Brasil', 'icon' => asset('assets/img/banks/bb.png')],
['name' => 'Caixa', 'icon' => asset('assets/img/banks/caixa.png')],
['name' => 'Bradesco', 'icon' => asset('assets/img/banks/bradesco.png')],
];
@endphp
<div class="w-full">
    <!-- Header componentizado para consistência visual -->
    <x-sales-header title="Editar Cartão/Banco" description="Atualize as informações do seu cartão ou banco"
        icon="bi-pencil-square" iconColor="orange">
        <x-slot name="actions">
            <a href="{{ route('banks.index') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-200 border-2 border-slate-600 hover:border-slate-500 shadow-lg hover:shadow-xl">
                <i class="bi bi-x-lg"></i>
                Cancelar
            </a>
            <button type="submit" form="bank-edit-form"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-2xl hover:scale-105">
                <i class="bi bi-check-lg"></i>
                Salvar Alterações
            </button>
        </x-slot>
    </x-sales-header>
    <form id="bank-edit-form" wire:submit.prevent="update" x-data="{ cardNumber: @entangle('description') }" class="flex-1 w-full flex flex-col gap-8 px-4 md:px-10 pb-8 overflow-auto">
        <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/20 dark:border-slate-700/50">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div>
                <label for="edit_name" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl mr-4 shadow-lg">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    Titular do Cartão
                </label>
                <div class="relative">
                    <input type="text" id="edit_name" wire:model="name"
                        class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500/20 transition-all duration-300 shadow-lg" required placeholder="Nome do titular">
                </div>
                @error('name') <div class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="edit_description" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl mr-4 shadow-lg">
                        <i class="bi bi-credit-card text-white"></i>
                    </div>
                    Número do Cartão
                </label>
                <div class="relative">
                    <input type="text" maxlength="19" id="edit_description" wire:model="description"
                        x-model="cardNumber" x-on:input="
                            let value = cardNumber.replace(/\D/g, '').slice(0,16);
                            let formatted = '';
                            for (let i = 0; i < value.length; i += 4) {
                                if (formatted) formatted += '-';
                                formatted += value.substr(i, 4);
                            }
                            cardNumber = formatted;"
                        class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-500 focus:ring-yellow-500/20 transition-all duration-300 shadow-lg" required placeholder="0000-0000-0000-0000">
                </div>
                @error('description') <div class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
        </div>
        <div>
            <label class="block mb-4 text-xl font-bold text-gray-800 dark:text-gray-200">Escolha o ícone do banco/cartão</label>
            <div class="flex flex-wrap gap-8">
                @foreach($bankIcons as $icon)
                <label class="flex flex-col items-center cursor-pointer group">
                    <input type="radio" wire:model="caminho_icone" value="{{ $icon['icon'] }}" class="sr-only peer" required>
                    <div class="w-40 h-40 flex items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-700 peer-checked:border-blue-500 dark:peer-checked:border-white bg-gray-100 dark:bg-gray-900 transition">
                        <img src="{{ $icon['icon'] }}" alt="{{ $icon['name'] }}" title="{{ $icon['name'] }}" class="w-16 h-16">
                    </div>
                    <span class="text-base text-gray-600 dark:text-gray-400 mt-2 text-center group-hover:text-blue-700 dark:group-hover:text-white transition-colors">{{ $icon['name'] }}</span>
                </label>
                @endforeach
            </div>
            @error('caminho_icone') <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div>
                <label for="edit_start_date" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl mr-4 shadow-lg">
                        <i class="bi bi-calendar-event text-white"></i>
                    </div>
                    Dia de Abertura da Fatura
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Dia do mês em que a fatura do cartão abre (ex: dia 6)</p>
                <div class="relative">
                    <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-blue-400 text-lg pointer-events-none z-10"></i>
                    <input type="text" id="edit_start_date" wire:model="start_date" placeholder="Selecione a data de abertura..."
                        class="w-full pl-12 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-medium shadow-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/80 hover:shadow-lg hover:shadow-blue-500/10" readonly required>
                </div>
                @error('start_date') <div class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="edit_end_date" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl mr-4 shadow-lg">
                        <i class="bi bi-clock-history text-white"></i>
                    </div>
                    Dia de Fechamento da Fatura
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Dia do mês em que a fatura do cartão fecha (ex: dia 5)</p>
                <div class="relative">
                    <i class="bi bi-alarm absolute left-4 top-1/2 -translate-y-1/2 text-orange-400 text-lg pointer-events-none z-10"></i>
                    <input type="text" id="edit_end_date" wire:model="end_date" placeholder="Selecione a data de fechamento..."
                        class="w-full pl-12 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-2xl focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-medium shadow-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/80 hover:shadow-lg hover:shadow-orange-500/10" readonly required>
                </div>
                @error('end_date') <div class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Dica sobre o ciclo de fatura -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-1">💡 Como funciona o ciclo de fatura?</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-400">
                        <strong>Exemplo:</strong> Se sua fatura abre no dia 6 e fecha no dia 5 do mês seguinte, selecione qualquer data com dia 6 no campo "Abertura" e dia 5 no campo "Fechamento". O sistema usará apenas o dia do mês para calcular seu ciclo de fatura automaticamente.
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-2">
                        <strong>Resultado:</strong> As transações serão agrupadas do dia 6 de um mês até o dia 5 do próximo mês, como uma fatura real de cartão de crédito! 💳
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr.localize(flatpickr.l10ns.pt);

        const defaultConfig = {
            locale: 'pt',
            dateFormat: 'Y-m-d',
            allowInput: true,
            animate: true,
            disableMobile: true,
            prevArrow: '<i class="bi bi-chevron-left"></i>',
            nextArrow: '<i class="bi bi-chevron-right"></i>',
        };

        const startDateInput = document.getElementById('edit_start_date');
        if (startDateInput) {
            const existingDate = startDateInput.value || '{{ $start_date }}';
            flatpickr(startDateInput, {
                ...defaultConfig,
                defaultDate: existingDate,
                onChange: function(selectedDates, dateStr) {
                    @this.set('start_date', dateStr);
                }
            });
        }

        const endDateInput = document.getElementById('edit_end_date');
        if (endDateInput) {
            const existingDate = endDateInput.value || '{{ $end_date }}';
            flatpickr(endDateInput, {
                ...defaultConfig,
                defaultDate: existingDate,
                onChange: function(selectedDates, dateStr) {
                    @this.set('end_date', dateStr);
                }
            });
        }

        Livewire.hook('message.processed', (message, component) => {
            setTimeout(() => {
                if (document.getElementById('edit_start_date') && !document.getElementById('edit_start_date')._flatpickr) {
                    const existingDate = '{{ $start_date }}';
                    flatpickr('#edit_start_date', {
                        ...defaultConfig,
                        defaultDate: existingDate,
                        onChange: function(selectedDates, dateStr) {
                            @this.set('start_date', dateStr);
                        }
                    });
                }
                if (document.getElementById('edit_end_date') && !document.getElementById('edit_end_date')._flatpickr) {
                    const existingDate = '{{ $end_date }}';
                    flatpickr('#edit_end_date', {
                        ...defaultConfig,
                        defaultDate: existingDate,
                        onChange: function(selectedDates, dateStr) {
                            @this.set('end_date', dateStr);
                        }
                    });
                }
            }, 100);
        });
    });
</script>
@endpush
