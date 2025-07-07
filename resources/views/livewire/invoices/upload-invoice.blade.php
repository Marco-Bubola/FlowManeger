<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('invoices.index', ['bankId' => $bankId]) }}" 
                       class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Upload de Transações</h1>
                        <p class="text-sm text-gray-600">Importe transações de arquivo PDF ou CSV</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(!$showConfirmation)
            <!-- Upload Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Enviar Arquivo</h2>
                    <p class="text-sm text-gray-600">Selecione um arquivo PDF ou CSV para importar transações</p>
                </div>
                
                <div class="p-6">
                    <form wire:submit="uploadFile" class="space-y-6">
                        <!-- File Upload -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors duration-200">
                            <div class="space-y-4">
                                <div class="flex justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Selecione um arquivo</h3>
                                    <p class="text-sm text-gray-600">PDF ou CSV até 10MB</p>
                                </div>
                                
                                <div class="flex justify-center">
                                    <label for="file" class="cursor-pointer bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                        <span>Escolher arquivo</span>
                                        <input wire:model="file" type="file" id="file" accept=".pdf,.csv" class="hidden">
                                    </label>
                                </div>
                                
                                @if($file)
                                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                                        <p class="text-sm text-green-800">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Arquivo selecionado: {{ $file->getClientOriginalName() }}
                                        </p>
                                    </div>
                                @endif
                                
                                @error('file') 
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p> 
                                @enderror
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Instruções para Upload:</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Arquivos PDF: Extratos de cartão de crédito ou faturas</li>
                                <li>• Arquivos CSV: Formato com colunas: Data, Descrição, Categoria, Parcelas, Valor</li>
                                <li>• Tamanho máximo: 10MB</li>
                                <li>• As transações serão categorizadas automaticamente quando possível</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center"
                                    :disabled="!$file">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Processar Arquivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Confirmation View -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Confirmar Transações</h2>
                            <p class="text-sm text-gray-600">{{ count($transactions) }} transações encontradas</p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="cancelUpload" 
                                    class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Cancelar
                            </button>
                            <button wire:click="confirmTransactions" 
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200">
                                Confirmar Todas
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($transactions as $index => $transaction)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $transaction['description'] ?? 'Sem descrição' }}</p>
                                            <p class="text-sm text-gray-600">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $transaction['date'] ?? 'Sem data' }}
                                            </p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">R$ {{ number_format($transaction['value'] ?? 0, 2, ',', '.') }}</p>
                                            <p class="text-sm text-gray-600">{{ $transaction['installments'] ?? 'À vista' }}</p>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <select wire:change="updateTransactionCategory({{ $index }}, $event.target.value)" 
                                                    class="w-full px-3 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Selecione categoria</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id_category }}" 
                                                            {{ ($transaction['category_id'] ?? '') == $category->id_category ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            
                                            <select wire:change="updateTransactionClient({{ $index }}, $event.target.value)" 
                                                    class="w-full px-3 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Cliente (opcional)</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" 
                                                            {{ ($transaction['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <button wire:click="removeTransaction({{ $index }})" 
                                            class="ml-4 text-red-600 hover:text-red-800 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
