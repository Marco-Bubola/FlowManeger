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
    <header class="w-full py-8 px-6 md:px-16 flex flex-col items-center justify-center text-center gap-2">
        <div class="flex items-center gap-4 justify-center">
            <!-- Ícone de banco/cartão -->
            <svg class="w-16 h-16 text-gray-700 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2" ry="2" stroke-width="2" stroke="currentColor" fill="none"/><path d="M2 11h20" stroke-width="2" stroke="currentColor"/></svg>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 dark:text-white mb-2">Adicionar Novo Cartão/Banco</h1>
            <!-- Ícone de adição -->
            <svg class="w-12 h-12 text-blue-400 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        </div>
        <div class="flex items-center gap-2 justify-center">
            <!-- Ícone de informação -->
            <svg class="w-10 h-10 text-gray-400 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" stroke="currentColor" fill="none"/><path d="M12 16v-4M12 8h.01" stroke-width="2" stroke="currentColor"/></svg>
            <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400">Cadastre um novo cartão ou banco para gerenciar suas finanças.</p>
        </div>
    </header>
    <form wire:submit.prevent="store" x-data="{ cardNumber: @entangle('description') }" class="flex-1 w-full flex flex-col gap-8 px-4 md:px-10 pb-8 overflow-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div>
                <label for="name" class="block mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Titular do Cartão</label>
                <div class="relative">
                    <span class="absolute left-0 top-1/2 transform -translate-y-1/2 pl-2">
                        <!-- Ícone de usuário -->
                        <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </span>
                    <input type="text" id="name" wire:model="name"
                        class="w-full bg-transparent border-b border-gray-300 dark:border-gray-700 focus:border-blue-500 dark:focus:border-white text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 px-10 outline-none transition text-xl" required placeholder="Nome do titular">
                </div>
                @error('name') <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="description" class="block mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Número do Cartão</label>
                <div class="relative">
                    <span class="absolute left-0 top-1/2 transform -translate-y-1/2 pl-2">
                        <!-- Ícone de cartão -->
                        <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2" ry="2" stroke-width="2" stroke="currentColor" fill="none"/><path d="M2 11h20" stroke-width="2" stroke="currentColor"/></svg>
                    </span>
                    <input type="text" maxlength="19" id="description" wire:model="description"
                        x-model="cardNumber" x-on:input="
                            let value = cardNumber.replace(/\D/g, '').slice(0,16);
                            let formatted = '';
                            for (let i = 0; i < value.length; i += 4) {
                                if (formatted) formatted += '-';
                                formatted += value.substr(i, 4);
                            }
                            cardNumber = formatted;"
                        class="w-full bg-transparent border-b border-gray-300 dark:border-gray-700 focus:border-blue-500 dark:focus:border-white text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 px-10 outline-none transition text-xl" required placeholder="0000-0000-0000-0000">
                </div>
                @error('description') <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
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
                <label for="start_date" class="block mb-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    📅 Dia de Abertura da Fatura
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Dia do mês em que a fatura do cartão abre (ex: dia 6)
                </p>
                <input type="date" id="start_date" wire:model="start_date"
                    class="w-full bg-transparent border-b border-gray-300 dark:border-gray-700 focus:border-blue-500 dark:focus:border-white text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 px-0 outline-none transition text-lg" required>
                @error('start_date') <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="end_date" class="block mb-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    🔒 Dia de Fechamento da Fatura
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Dia do mês em que a fatura do cartão fecha (ex: dia 5)
                </p>
                <input type="date" id="end_date" wire:model="end_date"
                    class="w-full bg-transparent border-b border-gray-300 dark:border-gray-700 focus:border-blue-500 dark:focus:border-white text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 px-0 outline-none transition text-lg" required>
                @error('end_date') <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div> @enderror
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
        <div class="flex flex-col md:flex-row items-center justify-center gap-6 mt-8">
            <a href="{{ route('banks.index') }}"
                class="inline-flex items-center px-10 py-4 text-lg font-bold text-gray-600 dark:text-gray-300 bg-transparent border border-gray-300 dark:border-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800 hover:text-blue-700 dark:hover:text-white transition">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-12 py-4 text-lg font-bold text-white dark:text-gray-900 bg-blue-600 dark:bg-gray-100 rounded-full hover:bg-blue-700 dark:hover:bg-white transition gap-2">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Salvar Cartão
            </button>
        </div>
    </form>
</div>
