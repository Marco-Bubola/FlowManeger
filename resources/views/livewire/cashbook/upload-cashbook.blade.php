<div class=" w-full ">
    <!-- Header alinhado ao estilo de products/sales -->
    <x-products-header
        title="Upload de Transações"
        description="Importe transações de arquivos PDF ou CSV"
        :total-products="0"
        :total-categories="0"
        :show-quick-actions="false"
    />

    <!-- Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-12">
        @if($step == 1)
            <!-- Step 1: Upload File -->
            <div class="w-full bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 dark:from-gray-800 dark:via-blue-900/30 dark:to-purple-900/30 rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 backdrop-blur-sm">
                <!-- Header do Card -->
                <div class="px-8 py-6 border-b border-blue-200/50 dark:border-gray-700/50 bg-gradient-to-r from-blue-50/50 to-purple-50/50 dark:from-gray-700/50 dark:to-gray-600/50 rounded-t-3xl">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-600 dark:to-purple-700 rounded-2xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                                Enviar Arquivo
                            </h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300 font-medium">
                                <svg class="w-4 h-4 inline mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Selecione um arquivo PDF ou CSV para importar transações
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form wire:submit="processFile" class="space-y-8">
                        <!-- File Upload Zone -->
                        <div class="relative border-3 border-dashed border-blue-300 dark:border-blue-600 rounded-2xl p-12 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 group bg-gradient-to-br from-blue-50/30 via-white to-purple-50/30 dark:from-gray-700/30 dark:via-gray-800/30 dark:to-gray-700/30 hover:shadow-2xl transform hover:scale-[1.02]">
                            <!-- Background decorativo -->
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-100/20 via-transparent to-purple-100/20 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="relative space-y-6">
                                <!-- Ícone principal animado -->
                                <div class="flex justify-center">
                                    <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 via-purple-600 to-pink-600 dark:from-blue-600 dark:via-purple-700 dark:to-pink-700 rounded-2xl shadow-xl transform group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-10 h-10 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                                        Selecione um arquivo
                                    </h3>
                                    <p class="text-lg text-gray-600 dark:text-gray-300">
                                        Arraste e solte aqui ou clique para selecionar
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Formatos aceitos: PDF, CSV (máximo 2MB)
                                    </p>
                                </div>

                                <!-- Input de arquivo estilizado -->
                                <div class="flex justify-center">
                                    <label for="file" class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-700 dark:via-purple-700 dark:to-pink-700 text-white text-lg font-bold rounded-2xl hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg cursor-pointer">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <svg class="w-6 h-6 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span class="relative z-10">Escolher Arquivo</span>
                                        <input wire:model="file" type="file" id="file" class="hidden" accept=".pdf,.csv">
                                    </label>
                                </div>

                                <!-- Feedback do arquivo selecionado -->
                                @if($file)
                                    <div class="bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-green-900/20 dark:via-emerald-900/20 dark:to-teal-900/20 border border-green-200 dark:border-green-700 rounded-2xl p-6 animate-fadeIn">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-lg font-bold text-green-900 dark:text-green-200">✅ Arquivo Selecionado</h4>
                                                <p class="text-green-800 dark:text-green-300 font-medium">
                                                    📄 {{ $file->getClientOriginalName() }}
                                                </p>
                                                <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                                                    📦 Tamanho: {{ number_format($file->getSize() / 1024, 1) }} KB |
                                                    📂 Tipo: {{ strtoupper($file->getClientOriginalExtension()) }}
                                                </p>
                                            </div>
                                            <button type="button" wire:click="$set('file', null)"
                                                    class="flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 rounded-lg transition-colors duration-200 group">
                                                <svg class="w-4 h-4 text-white group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @error('file')
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <p class="text-red-600 font-medium">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-900/20 dark:via-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-8 border border-blue-200 dark:border-blue-700 shadow-lg">
                            <div class="flex items-start space-x-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 rounded-xl shadow-lg flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-blue-900 dark:text-blue-200 mb-4">📋 Instruções</h4>
                                    <div class="space-y-3 text-blue-800 dark:text-blue-300">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                            <p class="font-medium">Arquivos PDF: Extratos bancários com formato padrão</p>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 flex-shrink-0"></div>
                                            <p class="font-medium">Arquivos CSV: Formato: Data, Valor, Descrição</p>
                                        </div>
                                        <div class="flex items-start space-x-3">
                                            <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 flex-shrink-0"></div>
                                            <p class="font-medium">Após o processamento, você poderá escolher o cofrinho para cada transação individualmente</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-center space-x-4">
                            <button type="button" wire:click="cancel"
                                    class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:from-gray-200 hover:to-gray-300 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-700 dark:via-purple-700 dark:to-pink-700 text-white font-bold rounded-xl hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="relative z-10">Processar Arquivo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Step 2: Preview Transactions -->
            <div class="w-full space-y-8">
                <!-- Header da confirmação -->
                <div class="w-full bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-green-900/20 dark:via-emerald-900/20 dark:to-teal-900/20 rounded-2xl p-8 border border-green-200 dark:border-green-700 shadow-lg">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 rounded-2xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold bg-gradient-to-r from-green-800 via-emerald-800 to-teal-800 dark:from-green-200 dark:via-emerald-200 dark:to-teal-200 bg-clip-text text-transparent">
                                    Revisar Transações
                                </h3>
                                <p class="text-lg text-green-700 dark:text-green-300 font-medium">
                                    <svg class="w-5 h-5 inline mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    {{ count($transactions) }} transações encontradas
                                    @if(method_exists($this, 'getTotalValidTransactions'))
                                        ({{ $this->getTotalValidTransactions() }} completas)
                                    @endif
                                </p>
                                <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center space-x-4">
                                    <span class="flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <span>Selecione categorias ({{ count($categories) }} disponíveis)</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>Escolha cofrinhos ({{ count($cofrinhos) }} disponíveis)</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        <span>Remova transações indesejadas</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-4 items-center">
                            <!-- ...existing code... -->

                            <div class="flex">
                                <button wire:click="backToUpload"
                                    class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:from-gray-200 hover:to-gray-300 dark:hover:from-gray-600 dark:hover:to-gray-500 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Voltar
                            </button>
                            <button wire:click="confirmTransactions"
                                    class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 dark:from-green-700 dark:via-emerald-700 dark:to-teal-700 text-white font-bold rounded-xl hover:from-green-700 hover:via-emerald-700 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="relative z-10">Confirmar Transações</span>
                            </button>
                            </div>
                        </div>
                    </div>
                </div>

                @if(count($transactions) > 0)
                    <!-- Mensagens de feedback -->
                    @if(session()->has('info'))
                        <div class="mb-6 bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700 rounded-lg p-4 animate-fadeIn">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-blue-800 dark:text-blue-300 font-medium">{{ session('info') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(session()->has('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700 rounded-lg p-4 animate-fadeIn">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-red-800 dark:text-red-300 font-bold mb-2">❌ Erro de Validação</h4>
                                    <pre class="text-red-700 dark:text-red-300 text-sm whitespace-pre-wrap">{{ session('error') }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Alertas de validação global -->
                    @if($errors->any())
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-700 rounded-lg p-4 animate-fadeIn">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-yellow-800 dark:text-yellow-300 font-bold mb-2">⚠️ Atenção - Corrija os Campos</h4>
                                    <ul class="text-yellow-700 dark:text-yellow-300 text-sm space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li class="flex items-center space-x-2">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                                <span>{{ $error }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tabela de transações com design moderno -->
                    <div class="w-full bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 dark:from-gray-800 dark:via-blue-900/30 dark:to-purple-900/30 rounded-2xl shadow-2xl border border-white/20 dark:border-gray-700/50 backdrop-blur-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-gray-50 via-blue-50 to-purple-50 dark:from-gray-700 dark:via-blue-900/50 dark:to-purple-900/50">
                                    <tr>
                                        <th class="px-2 py-4 text-center text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider w-12">
                                            <div class="flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </th>
                                        <th class="px-3 py-4 text-center text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider w-16">
                                            <div class="flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>Data</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>Descrição</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                <span>Valor</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                <span>Tipo</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                                <span>Categoria</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <span>Cofrinho</span>
                                            </div>
                                        </th>
                                        <th class="px-4 py-4 text-left text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.042 12.042 0 0112 15c2.21 0 4.28.57 6.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>Cliente</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($transactions as $index => $transaction)
                                        @php
                                            $validationStatus = method_exists($this, 'getTransactionValidationStatus')
                                                ? $this->getTransactionValidationStatus($transaction)
                                                : ['isValid' => !empty($transaction['category_id']), 'issues' => []];
                                        @endphp
                                        <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 transition-all duration-300 group {{ !$validationStatus['isValid'] ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">
                                            <!-- Status indicator (nova coluna) -->
                                            <td class="px-2 py-4 whitespace-nowrap text-center">
                                                @if($validationStatus['isValid'])
                                                    <div class="flex items-center justify-center w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full">
                                                        <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center w-6 h-6 bg-yellow-100 dark:bg-yellow-900/30 rounded-full" title="{{ implode(', ', $validationStatus['issues']) }}">
                                                        <svg class="w-3 h-3 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </td>
                                            <!-- Botão de exclusão -->
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                <button type="button"
                                                        wire:click="removeTransaction({{ $index }})"
                                                        class="group/btn btn-delete flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 rounded-lg transition-all duration-200 transform hover:scale-110"
                                                        title="Remover transação">
                                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400 group-hover/btn:text-red-800 dark:group-hover/btn:text-red-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>

                                            <!-- Data -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                                    <span class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</span>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Descrição -->
                                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300 max-w-xs">
                                                <div class="flex items-start space-x-2">
                                                    <svg class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <div class="truncate group-hover:text-purple-700 dark:group-hover:text-purple-300 transition-colors duration-300">
                                                        {{ $transaction['description'] }}
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Valor -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $transaction['type_id'] == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($transaction['type_id'] == 1)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                        @endif
                                                    </svg>
                                                    {{ $transaction['type_id'] == 1 ? '+' : '-' }}R$ {{ number_format($transaction['value'], 2, ',', '.') }}
                                                </span>
                                            </td>

                                            <!-- Tipo -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $transaction['type_id'] == 1 ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 dark:from-green-900/30 dark:to-emerald-900/30 dark:text-green-300' : 'bg-gradient-to-r from-red-100 to-pink-100 text-red-800 dark:from-red-900/30 dark:to-pink-900/30 dark:text-red-300' }}">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($transaction['type_id'] == 1)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                                        @endif
                                                    </svg>
                                                    {{ $transaction['type_id'] == 1 ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>

                                            <!-- Categoria (agora editável com validação) -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-pink-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    <div class="w-full">
                                                        <select wire:model="transactions.{{ $index }}.category_id"
                                                                class="w-full px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-xs {{ $errors->has('transactions.'.$index.'.category_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }}"
                                                                required>
                                                            <option value="">* Selecionar Categoria</option>
                                                            @if(isset($categories) && count($categories) > 0)
                                                                @foreach($categories as $category)
                                                                    <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                                                @endforeach
                                                            @else
                                                                <option value="" disabled>Nenhuma categoria encontrada</option>
                                                            @endif
                                                        </select>
                                                        @error('transactions.'.$index.'.category_id')
                                                            <p class="text-red-500 text-xs mt-1 animate-shake">{{ $message }}</p>
                                                        @enderror
                                                        @if(empty($transaction['category_id']))
                                                            <p class="text-yellow-600 dark:text-yellow-400 text-xs mt-1 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                                </svg>
                                                                Campo obrigatório
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Cofrinho -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                        @php
                                                            $selectedCofrinho = null;
                                                            if(!empty($transaction['cofrinho_id'])) {
                                                                $selectedCofrinho = collect($cofrinhos)->firstWhere('id', $transaction['cofrinho_id']);
                                                            }
                                                        @endphp
                                                        <div class="relative w-full">
                                                            <div class="absolute left-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                                                <i class="{{ $selectedCofrinho->icone ?? 'bi bi-piggy-bank' }} text-indigo-500"></i>
                                                            </div>
                                                            <select wire:model="transactions.{{ $index }}.cofrinho_id"
                                                                    class="w-full pl-8 px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-xs">
                                                                <option value="">Selecionar Cofrinho</option>
                                                                @if(isset($cofrinhos))
                                                                    @foreach($cofrinhos as $cofrinho)
                                                                        <option value="{{ $cofrinho->id }}">{{ $cofrinho->nome }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                </div>
                                            </td>
                                            <!-- Cliente -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.042 12.042 0 0112 15c2.21 0 4.28.57 6.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <select wire:model="transactions.{{ $index }}.client_id"
                                                            class="w-full px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-xs">
                                                        <option value="">Nenhum</option>
                                                        @foreach($clients as $client)
                                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <!-- Empty state com design moderno -->
                    <div class="w-full bg-gradient-to-br from-white via-gray-50 to-blue-50 dark:from-gray-800 dark:via-gray-700 dark:to-blue-900/30 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-600 p-16 text-center">
                        <div class="flex justify-center mb-6">
                            <div class="flex items-center justify-center w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl shadow-lg">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Nenhuma transação encontrada</h3>
                        <p class="text-lg text-gray-500 dark:text-gray-400">Não foi possível extrair transações do arquivo enviado.</p>
                        <div class="mt-8">
                            <button wire:click="backToUpload"
                                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Seção de dicas e recursos (aparece apenas no step 1) -->
            @if($step == 1)
                <div class="mt-8 w-full grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Dica 1 -->
                    <div class="group bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg animate-float">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-900 dark:text-blue-200 mb-2">💡 Processamento Inteligente</h3>
                                <p class="text-sm text-blue-800 dark:text-blue-300">Nosso sistema identifica automaticamente categorias com base na descrição das transações</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dica 2 -->
                    <div class="group bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg animate-glow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-green-900 dark:text-green-200 mb-2">⚡ Análise Rápida</h3>
                                <p class="text-sm text-green-800 dark:text-green-300">Processe centenas de transações em segundos com nossa tecnologia avançada</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dica 3 -->
                    <div class="group bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-purple-900 dark:text-purple-200 mb-2">🔒 Segurança Total</h3>
                                <p class="text-sm text-purple-800 dark:text-purple-300">Seus dados financeiros são processados com máxima segurança e privacidade</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>


    <!-- Scripts adicionais para interações -->
    <style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .animate-glow {
        animation: glow 2s ease-in-out infinite alternate;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
        }
        to {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
        }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        75% { transform: translateX(2px); }
    }

    .animate-shake {
        animation: shake 0.3s ease-in-out;
    }

    .btn-delete:hover {
        animation: shake 0.3s ease-in-out;
    }
    </style>

    <script>
    // Adicionar tooltips aos elementos
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar atributos de tooltip aos elementos importantes
        const uploadButton = document.querySelector('label[for="file"]');
        if (uploadButton) {
            uploadButton.setAttribute('data-tooltip', 'Clique para selecionar arquivo ou arraste e solte aqui');
        }

        // Adicionar efeitos de loading
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                // Adicionar loading state visual se necessário
            });
        });

        // Animação de entrada para elementos
        const elements = document.querySelectorAll('.card-hover, .group');
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            setTimeout(() => {
                el.style.transition = 'all 0.5s ease-out';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>
</div>
