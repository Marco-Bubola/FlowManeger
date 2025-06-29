<!-- Modal de Edição para Banco -->
@php
    // Definir os ícones dos bancos diretamente aqui para evitar problemas de variável indefinida
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

<div id="editBankModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <svg class="w-6 h-6 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar Cartão
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editBankModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Fechar modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form wire:submit.prevent="update">
                <div class="p-4 md:p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Titular do Cartão
                            </label>
                            <input type="text" id="edit_name" wire:model="editingBank.name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            @error('editingBank.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit_description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Número do Cartão
                            </label>
                            <input type="text" maxlength="19" id="edit_description" wire:model="editingBank.description" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required placeholder="0000-0000-0000-0000">
                            @error('editingBank.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Seleção de ícone do banco/cartão -->
                    <div>
                        <label class="block mb-3 text-sm font-medium text-gray-900 dark:text-white">
                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            Escolha o ícone do banco/cartão
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                            @foreach($bankIcons as $icon)
                                <label class="flex flex-col items-center cursor-pointer group">
                                    <input type="radio" wire:model="editingBank.caminho_icone" value="{{ $icon['icon'] }}" class="sr-only peer" required>
                                    <img src="{{ $icon['icon'] }}" alt="{{ $icon['name'] }}" title="{{ $icon['name'] }}" class="w-16 h-16 rounded-lg border-2 border-transparent peer-checked:border-blue-500 peer-checked:ring-4 peer-checked:ring-blue-200 group-hover:border-gray-300 transition-all duration-200">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ $icon['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('editingBank.caminho_icone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_start_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Data de Início
                            </label>
                            <input type="date" id="edit_start_date" wire:model="editingBank.start_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            @error('editingBank.start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit_end_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Data de Término
                            </label>
                            <input type="date" id="edit_end_date" wire:model="editingBank.end_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            @error('editingBank.end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" data-modal-hide="editBankModal">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara dinâmica para número do cartão
    const cardInput = document.getElementById('edit_description');
    if (cardInput) {
        cardInput.addEventListener('input', function(e) {
            let value = cardInput.value.replace(/\D/g, '').slice(0,16);
            let formatted = '';
            for (let i = 0; i < value.length; i += 4) {
                if (formatted) formatted += '-';
                formatted += value.substr(i, 4);
            }
            cardInput.value = formatted;
        });
    }

    // Seleção de ícones com feedback visual
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove todas as seleções visuais
            document.querySelectorAll('.peer-checked\\:border-blue-500').forEach(img => {
                img.classList.remove('peer-checked:border-blue-500', 'peer-checked:ring-4', 'peer-checked:ring-blue-200');
            });
            
            // Adiciona seleção visual ao item selecionado
            if (this.checked) {
                const img = this.nextElementSibling;
                img.classList.add('peer-checked:border-blue-500', 'peer-checked:ring-4', 'peer-checked:ring-blue-200');
            }
        });
    });
});
</script> 