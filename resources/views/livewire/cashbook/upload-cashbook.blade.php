<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Back Button and Title -->
                <div class="flex items-center space-x-4">
                    <button wire:click="cancel" 
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Upload de Transações</h1>
                        <p class="text-sm text-gray-500">Importe transações de arquivos PDF ou CSV</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button wire:click="cancel" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($step == 1)
            <!-- Step 1: Upload File -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                            <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Selecione um arquivo</h3>
                        <p class="text-sm text-gray-500 mb-6">Faça upload de um arquivo PDF ou CSV com suas transações</p>
                    </div>

                    <form wire:submit="processFile">
                        <div class="space-y-6">
                            <!-- File Input -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file text-blue-600"></i>
                                    <span>Arquivo *</span>
                                </label>
                                <input wire:model="file" type="file" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('file') border-red-500 @else border-gray-300 @enderror"
                                       accept=".pdf,.csv">
                                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-sm text-gray-500">Formatos aceitos: PDF, CSV (máximo 2MB)</p>
                            </div>

                            <!-- Cofrinho -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-piggy-bank text-blue-600"></i>
                                    <span>Cofrinho (Opcional)</span>
                                </label>
                                <select wire:model="cofrinho_id" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cofrinho_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Selecione um cofrinho</option>
                                    @foreach($cofrinhos as $cofrinho)
                                        <option value="{{ $cofrinho->id }}">{{ $cofrinho->name }}</option>
                                    @endforeach
                                </select>
                                @error('cofrinho_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Instructions -->
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex">
                                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-900">Instruções</h4>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Arquivos PDF: Extratos bancários com formato padrão</li>
                                                <li>Arquivos CSV: Formato: Data, Valor, Descrição</li>
                                                <li>As transações serão processadas e você poderá revisar antes de salvar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3">
                                <button type="button" wire:click="cancel" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-cog mr-2"></i>
                                    Processar Arquivo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Step 2: Preview Transactions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Revisar Transações</h3>
                            <p class="text-sm text-gray-500">{{ count($transactions) }} transações encontradas</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button wire:click="backToUpload" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Voltar
                            </button>
                            <button wire:click="confirmTransactions" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                <i class="fas fa-check mr-2"></i>
                                Confirmar Transações
                            </button>
                        </div>
                    </div>

                    @if(count($transactions) > 0)
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transactions as $index => $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $transaction['description'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <span class="{{ $transaction['type_id'] == 1 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $transaction['type_id'] == 1 ? '+' : '-' }}R$ {{ number_format($transaction['value'], 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction['type_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $transaction['type_id'] == 1 ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($transaction['category_id'])
                                                    @php
                                                        $category = $categories->firstWhere('id', $transaction['category_id']);
                                                    @endphp
                                                    {{ $category ? $category->name : 'Categoria não encontrada' }}
                                                @else
                                                    <span class="text-gray-400">Sem categoria</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma transação encontrada</h3>
                            <p class="text-gray-500">Não foi possível extrair transações do arquivo enviado.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
