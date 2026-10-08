<div class="edit-prices-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16">

    <x-sale-page-header :sale="$sale" title="Preços" active="precos" :back-route="route('sales.show', $sale->id)" />

    <!-- Conteúdo principal -->
    <div class="">
        <!-- Alertas de erro -->
        @if($errors->has('general'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 mr-3 text-lg"></i>
                    <span class="text-red-600 dark:text-red-400 font-medium">{{ $errors->first('general') }}</span>
                </div>
            </div>
        @endif

        <!-- Formulário de edição -->
        <form wire:submit.prevent="savePrices">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($saleItems as $index => $item)
                    <x-product-price-card :item="$item" :index="$index" />
                @endforeach
            </div>

            <div class="mt-5 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Novo total da venda</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($this->total, 2, ',', '.') }}</p>
                </div>
                <div class="flex flex-col-reverse sm:flex-row gap-2">
                    <a href="{{ route('sales.show', $sale->id) }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancelar</a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="savePrices"><i class="bi bi-check2-circle mr-1"></i>Salvar preços</span>
                        <span wire:loading wire:target="savePrices"><i class="bi bi-arrow-repeat animate-spin mr-1"></i>Salvando...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Toast Notifications Component -->
    <x-toast-notifications />

    <!-- JavaScript para Formatação de Preços -->
    <script>
        // Inicializa os inputs de preço
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, inicializando inputs de preço...');
            @foreach($saleItems as $index => $item)
                console.log('Inicializando item {{ $index }} - Produto: {{ $item["product_name"] ?? "N/A" }} - Preço: {{ $item["price_sale"] ?? "0" }}');
                initializePriceInput({{ $index }});
            @endforeach
            console.log('Inicialização concluída.');
        });

        function initializePriceInput(index) {
            console.log('Inicializando input de preço para índice:', index);

            const input = document.getElementById('price_input_' + index);
            const hiddenInput = document.getElementById('price_hidden_' + index);

            if (!input) {
                console.error('Input não encontrado para índice:', index);
                return;
            }

            if (!hiddenInput) {
                console.error('Hidden input não encontrado para índice:', index);
                return;
            }

            console.log('Valores iniciais - Input:', input.value, 'Hidden:', hiddenInput.value, 'Index:', index);

            // Remove eventos anteriores para evitar duplicação
            input.removeEventListener('input', handlePriceInput);
            input.removeEventListener('keydown', handlePriceKeydown);
            input.removeEventListener('focus', handlePriceFocus);
            input.removeEventListener('blur', handlePriceBlur);

            // Garante que o valor inicial está correto
            if (!hiddenInput.value || hiddenInput.value === '0' || hiddenInput.value === '0.00') {
                const displayValue = input.value.replace(',', '.');
                const initialValue = parseFloat(displayValue) || 0;
                hiddenInput.value = initialValue.toFixed(2);
                console.log('Valor inicial corrigido para índice', index, ':', initialValue.toFixed(2));
            }

            // Adiciona os eventos
            input.addEventListener('keydown', function(e) { handlePriceKeydown(e, index); });
            input.addEventListener('focus', function(e) { handlePriceFocus(e, index); });
            input.addEventListener('blur', function(e) { handlePriceBlur(e, index); });

            console.log('Eventos adicionados para índice:', index);
        }        function handlePriceInput(event, index) {
            const input = event.target;
            let value = input.value;

            // Remove tudo que não é número
            value = value.replace(/[^\d]/g, '');

            // Se vazio, deixa vazio temporariamente
            if (value === '') {
                input.value = '';
                updateHiddenField(index, '0,00');
                return;
            }

            // Garante que tenha pelo menos 3 dígitos (para centavos)
            while (value.length < 3) {
                value = '0' + value;
            }

            // Converte para formato XX,XX
            const reais = value.slice(0, -2);
            const centavos = value.slice(-2);
            const formattedValue = parseInt(reais).toString() + ',' + centavos;

            // Atualiza o valor do input
            input.value = formattedValue;

            // Converte para decimal e atualiza o campo oculto
            updateHiddenField(index, formattedValue);
        }

        function handlePriceKeydown(event, index) {
            console.log('handlePriceKeydown - Index:', index, 'KeyCode:', event.keyCode);
            const input = event.target;

            // Permite: tab, escape, enter, home, end, left, right
            if ([9, 27, 13, 35, 36, 37, 39].indexOf(event.keyCode) !== -1 ||
                // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true) ||
                (event.keyCode === 90 && event.ctrlKey === true)) {
                return;
            }

            // Trata backspace
            if (event.keyCode === 8) {
                event.preventDefault();
                let currentValue = input.value.replace(/[^\d]/g, '');
                console.log('Backspace - Valor atual:', currentValue);

                // Remove o último dígito
                currentValue = currentValue.slice(0, -1);

                // Se vazio, deixa vazio
                if (currentValue === '') {
                    input.value = '';
                    updateHiddenField(index, '0,00');
                    return;
                }

                // Aplica a máscara
                while (currentValue.length < 3) {
                    currentValue = '0' + currentValue;
                }

                const reais = currentValue.slice(0, -2);
                const centavos = currentValue.slice(-2);
                const formattedValue = parseInt(reais).toString() + ',' + centavos;

                input.value = formattedValue;
                updateHiddenField(index, formattedValue);
                return;
            }

            // Trata delete
            if (event.keyCode === 46) {
                event.preventDefault();
                // Delete tem o mesmo comportamento do backspace neste caso
                let currentValue = input.value.replace(/[^\d]/g, '');
                currentValue = currentValue.slice(0, -1);

                if (currentValue === '') {
                    input.value = '';
                    updateHiddenField(index, '0,00');
                    return;
                }

                while (currentValue.length < 3) {
                    currentValue = '0' + currentValue;
                }

                const reais = currentValue.slice(0, -2);
                const centavos = currentValue.slice(-2);
                const formattedValue = parseInt(reais).toString() + ',' + centavos;

                input.value = formattedValue;
                updateHiddenField(index, formattedValue);
                return;
            }

            // Verifica se é um número (0-9)
            const isNumber = (event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105);

            if (!isNumber) {
                event.preventDefault();
                return;
            }

            // Se é um número, processa como máscara
            if (isNumber) {
                event.preventDefault();

                const digit = String.fromCharCode(event.keyCode >= 96 ? event.keyCode - 48 : event.keyCode);
                let currentValue = input.value.replace(/[^\d]/g, '');
                console.log('Número digitado:', digit, 'Valor atual:', currentValue);

                // Adiciona o novo dígito
                currentValue += digit;

                // Aplica a máscara
                if (currentValue === '') {
                    input.value = '';
                } else {
                    // Garante que tenha pelo menos 3 dígitos
                    while (currentValue.length < 3) {
                        currentValue = '0' + currentValue;
                    }

                    // Formata como XX,XX
                    const reais = currentValue.slice(0, -2);
                    const centavos = currentValue.slice(-2);
                    const formattedValue = parseInt(reais).toString() + ',' + centavos;

                    console.log('Valor formatado:', formattedValue);
                    input.value = formattedValue;
                    updateHiddenField(index, formattedValue);
                }
            }
        }        function handlePriceFocus(event, index) {
            const input = event.target;
            // Limpa o valor 0,00 quando foca para facilitar a edição
            if (input.value === '0,00') {
                input.value = '';
            }
        }

        function handlePriceBlur(event, index) {
            const input = event.target;
            let value = input.value;

            // Se estiver vazio, define como 0,00
            if (value === '' || value === '0' || value === '0,') {
                value = '0,00';
            } else {
                // Garante o formato correto
                const parts = value.split(',');
                let reais = parts[0] || '0';
                let centavos = parts[1] || '00';

                // Limita centavos a 2 dígitos
                if (centavos.length === 1) {
                    centavos += '0';
                } else if (centavos.length > 2) {
                    centavos = centavos.substring(0, 2);
                }

                value = reais + ',' + centavos;
            }

            input.value = value;
            updateHiddenField(index, value);
        }

        function updateHiddenField(index, formattedValue) {
            console.log('updateHiddenField chamada - Index:', index, 'Value:', formattedValue);

            const hiddenInput = document.getElementById('price_hidden_' + index);

            if (!hiddenInput) {
                console.error('Hidden input não encontrado no updateHiddenField para índice:', index);
                return;
            }

            // Converte para decimal
            const decimalValue = parseFloat(formattedValue.replace(',', '.')) || 0;
            console.log('Valor decimal calculado:', decimalValue);

            // Atualiza o campo oculto
            hiddenInput.value = decimalValue.toFixed(2);
            console.log('Hidden input atualizado para:', hiddenInput.value);

            // Dispara o evento change para atualizar o Livewire
            hiddenInput.dispatchEvent(new Event('change'));

            // Chama a função do Livewire para atualizar o preço
            console.log('Chamando Livewire updatePrice com index:', index, 'value:', decimalValue);
            @this.call('updatePrice', index, decimalValue);
        }

        // Função para resetar um campo de preço (quando necessário)
        function resetPrice(index) {
            const input = document.getElementById('price_input_' + index);
            input.value = '0,00';
            updateHiddenField(index, '0,00');
        }

        // Mantém a função antiga para compatibilidade (será removida)
        function handlePriceKeypress(event, index) {
            // Esta função não é mais usada, mas mantida para evitar erros
            return true;
        }

        function updatePriceFromFormatted(index) {
            // Esta função agora só é chamada no blur, que já trata tudo
            handlePriceBlur({ target: document.getElementById('price_input_' + index) }, index);
        }

        function formatPriceDisplay(value) {
            // Função mantida para compatibilidade
            const intValue = parseInt(value);
            const reais = Math.floor(intValue / 100);
            const centavos = intValue % 100;
            return reais.toString() + ',' + centavos.toString().padStart(2, '0');
        }
    </script>
</div>
