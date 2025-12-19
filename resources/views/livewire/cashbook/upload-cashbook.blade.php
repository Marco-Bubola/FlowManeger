<div class="w-full">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-12">
        @if ($step == 1)
            <!-- Step 1: Upload File -->
            <div class="max-w-3xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                            Upload de Transações
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            Selecione um arquivo PDF ou CSV para importar transações
                        </p>
                    </div>

                    @if (session()->has('error'))
                        <div class="mb-4 bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700 rounded-lg p-4">
                            <p class="text-red-600 dark:text-red-400">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if (session()->has('success'))
                        <div class="mb-4 bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-700 rounded-lg p-4">
                            <p class="text-green-600 dark:text-green-400">{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <!-- File Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Arquivo
                            </label>
                            <input
                                wire:model.live="file"
                                type="file"
                                accept=".pdf,.csv"
                                class="block w-full text-sm text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-700"
                            >
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Formatos aceitos: PDF, CSV (máximo 10MB)
                            </p>
                        </div>

                        @if ($file)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-green-800 dark:text-green-200">
                                            ✅ Arquivo Selecionado
                                        </p>
                                        <p class="text-sm text-green-600 dark:text-green-400">
                                            {{ $file->getClientOriginalName() }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="$set('file', null)"
                                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                wire:click="testClick"
                                class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                            >
                                TESTE CLICK
                            </button>
                            <button
                                type="button"
                                wire:click="cancel"
                                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                wire:click="processFile"
                                wire:loading.attr="disabled"
                                wire:target="processFile,file"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(!$file) disabled @endif
                            >
                                <span wire:loading.remove wire:target="processFile">Processar Arquivo</span>
                                <span wire:loading wire:target="processFile">Processando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Step 2: Preview Transactions -->
            <div class="max-w-6xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                            Confirmação de Importação
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ count($transactions) }} transações encontradas. Revise e confirme a importação.
                        </p>
                    </div>

                    <!-- Bank and Category Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Banco/Carteira
                            </label>
                            <select
                                wire:model="selectedBank"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecione um banco</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Categoria Padrão
                            </label>
                            <select
                                wire:model="selectedCategory"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecione uma categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrição</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Valor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $transaction['date'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $transaction['description'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">R$ {{ number_format($transaction['amount'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $transaction['type'] == 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $transaction['type'] == 'income' ? 'Receita' : 'Despesa' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="cancel"
                            class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Voltar
                        </button>
                        <button
                            type="button"
                            wire:click="saveTransactions"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                            @if(!$selectedBank) disabled @endif
                        >
                            Confirmar Importação
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
