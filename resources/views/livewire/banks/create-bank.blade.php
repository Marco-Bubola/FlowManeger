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
    <!-- Header Compacto Modernizado -->
    <x-sales-header
        title="Novo Cartão/Banco"
        description="Cadastre um cartão ou banco para gerenciar finanças"
        :back-route="route('banks.index')"
        :current-step="1"
        :steps="[
            [
                'title' => 'Informações',
                'description' => 'Dados do cartão',
                'icon' => 'bi-credit-card',
                'gradient' => 'from-blue-500 to-cyan-500',
                'connector_gradient' => 'from-blue-500 to-cyan-500'
            ]
        ]" />

    <!-- Conteúdo Principal -->
    <div class="relative flex-1">
        <form wire:submit.prevent="store" x-data="{ cardNumber: @entangle('description') }">
            <div class="flex flex-col">
                <div class="flex-1 space-y-4 animate-fadeIn">
                    <!-- Card Container Principal -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg shadow-slate-200/30 dark:shadow-slate-900/30 border border-white/20 dark:border-slate-700/50 w-full">

                        <!-- Seção superior: Titular / Número do Cartão (2 colunas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                            <!-- Titular do Cartão -->
                            <div class="group space-y-2">
                                <label for="name" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-400 to-cyan-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-person text-white"></i>
                                    </div>
                                    Titular do Cartão
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-person text-slate-400 group-hover:text-blue-500 transition-colors duration-200"></i>
                                    </div>
                                    <input type="text" id="name" wire:model="name"
                                        placeholder="Nome do titular"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm" required>
                                </div>
                                @error('name')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Número do Cartão -->
                            <div class="group space-y-2">
                                <label for="description" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-credit-card text-white"></i>
                                    </div>
                                    Número do Cartão
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-credit-card text-slate-400 group-hover:text-purple-500 transition-colors duration-200"></i>
                                    </div>
                                    <input type="text" maxlength="19" id="description" wire:model="description"
                                        x-model="cardNumber"
                                        x-on:input="
                                            let value = cardNumber.replace(/\D/g, '').slice(0,16);
                                            let formatted = '';
                                            for (let i = 0; i < value.length; i += 4) {
                                                if (formatted) formatted += '-';
                                                formatted += value.substr(i, 4);
                                            }
                                            cardNumber = formatted;"
                                        placeholder="0000-0000-0000-0000"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm" required>
                                </div>
                                @error('description')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Seleção de ícone do banco -->
                        <div class="mb-4">
                            <label class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">
                                <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-bank text-white"></i>
                                </div>
                                Escolha o banco/cartão
                            </label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-7 gap-3">
                                @foreach($bankIcons as $icon)
                                <label class="cursor-pointer group">
                                    <input type="radio" wire:model="caminho_icone" value="{{ $icon['icon'] }}" class="sr-only peer" required>
                                    <div class="flex flex-col items-center p-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 dark:peer-checked:border-blue-400 bg-white/60 dark:bg-slate-700/60 hover:shadow-md transition-all duration-200">
                                        <img src="{{ $icon['icon'] }}" alt="{{ $icon['name'] }}" title="{{ $icon['name'] }}" class="w-12 h-12 mb-1">
                                        <span class="text-xs text-slate-600 dark:text-slate-400 text-center">{{ $icon['name'] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('caminho_icone')
                            <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Grid: Abertura / Fechamento (2 colunas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Data de Abertura -->
                            <div class="group space-y-2">
                                <label for="start_date" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-calendar-check text-white"></i>
                                    </div>
                                    Abertura da Fatura
                                </label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                                    Dia do mês em que a fatura abre (ex: dia 6)
                                </p>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-calendar-check text-slate-400 group-hover:text-green-500 transition-colors duration-200"></i>
                                    </div>
                                    <input type="date" id="start_date" wire:model="start_date"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('start_date') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-green-500 focus:ring-green-500/20 hover:border-green-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm" required>
                                </div>
                                @error('start_date')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Data de Fechamento -->
                            <div class="group space-y-2">
                                <label for="end_date" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-calendar-x text-white"></i>
                                    </div>
                                    Fechamento da Fatura
                                </label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                                    Dia do mês em que a fatura fecha (ex: dia 5)
                                </p>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-calendar-x text-slate-400 group-hover:text-orange-500 transition-colors duration-200"></i>
                                    </div>
                                    <input type="date" id="end_date" wire:model="end_date"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('end_date') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-orange-500 focus:ring-orange-500/20 hover:border-orange-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm" required>
                                </div>
                                @error('end_date')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>



                    <!-- Botões de Ação (centralizados, estilo moderno) -->
                    <div class="flex items-center justify-center gap-4 mt-6">
                        <a href="{{ route('banks.index') }}"
                            class="inline-flex items-center gap-3 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-700/80 border border-slate-200 dark:border-slate-600 rounded-full hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all duration-200">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200">
                                <i class="bi bi-x-lg"></i>
                            </span>
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-200 shadow-lg hover:shadow-2xl">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white">
                                <i class="bi bi-check-lg"></i>
                            </span>
                            Salvar Cartão
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Estilos Customizados -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
    </style>
</div>
