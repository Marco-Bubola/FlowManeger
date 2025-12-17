<div class="w-full">
    @push('styles')
        @vite('resources/css/upload-animations.css')
    @endpush

    @push('scripts')
        @vite('resources/js/upload-interactions.js')
    @endpush

    @include('components.theme-controls')
    @include('components.toast-notifications')

    <div class="">
        <x-upload-header
            :title="'Upload de Transações'"
            :description="'Importar transações a partir de arquivo PDF ou CSV'"
            :backRoute="route('cashbook.index')"
            :showConfirmation="$showConfirmation"
            :transactionsCount="is_array($transactions) ? count($transactions) : 0"
        />

        <div class="w-full px-4 sm:px-6 lg:px-8">
        @if (!$showConfirmation)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="w-full xl:w-auto">
                    <div class="bg-gradient-to-br from-slate-900/95 via-green-900/20 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-file-earmark-arrow-up-fill text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Upload de Transações</h3>
                                <p class="text-xs text-slate-400">Envie seu arquivo PDF ou CSV</p>
                            </div>
                        </div>

                        <div class="flex-1 flex items-center justify-center">
                            <x-file-upload
                                name="file"
                                id="file"
                                wireModel="newFile"
                                title="Upload do Arquivo"
                                description="Clique ou arraste seu arquivo aqui"
                                :newFile="$newFile"
                                height="h-[400px]"
                                :supportedFormats="['PDF (.pdf)', 'CSV (.csv)']"
                                maxSize="10MB"
                            />
                        </div>

                        @error('file')
                            <div class="mt-4 flex items-start gap-2 text-xs text-red-400">
                                <i class="bi bi-exclamation-circle text-red-400 mt-0.5"></i>
                                <p>{{ $message }}</p>
                            </div>
                        @enderror

                        <div class="mt-4 flex items-start gap-2 text-xs text-slate-400">
                            <i class="bi bi-info-circle text-green-400 mt-0.5"></i>
                            <p>PDF, CSV • Máx 10MB • Arquivo com transações do cashbook</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Histórico de Uploads</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Últimos 10</span>
                    </div>

                    @if($uploadHistory && count($uploadHistory) > 0)
                        <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($uploadHistory as $upload)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                        <div class="px-4 py-3 bg-gradient-to-r {{ strtolower($upload->file_type) === 'pdf' ? 'from-red-500 to-red-600' : 'from-emerald-500 to-emerald-600' }} relative overflow-hidden">
                                            <div class="absolute inset-0 opacity-10">
                                                @else
                                                    <div class="w-full space-y-8">
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
                                                                                <span>Remova duplicados ou itens incorretos</span>
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                <div class="flex flex-wrap gap-3 mt-6">
                                                                    <button wire:click="backToUpload"
                                                                            class="inline-flex items-center px-6 py-3 bg-white text-green-700 font-bold rounded-xl border-2 border-green-500 hover:bg-green-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                                        </svg>
                                                                        Voltar ao Upload
                                                                    </button>

                                                                    <button wire:click="confirmTransactions"
                                                                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white font-bold rounded-xl hover:from-green-600 hover:via-emerald-600 hover:to-teal-600 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        <span class="relative z-10">Confirmar Transações</span>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        @if(count($transactions) > 0)
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
                                                                            <p class="text-red-700 dark:text-red-200">{{ session('error') }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                                                    <div class="flex items-center space-x-3">
                                                                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6l-2 1m13 4V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2h7"></path>
                                                                            </svg>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Transações Encontradas</h4>
                                                                            <p class="text-sm text-gray-500 dark:text-gray-400">Revise, ajuste categorias e confirme</p>
                                                                        </div>
                                                                    </div>

                                                                    <div class="flex items-center space-x-3">
                                                                        <div class="px-3 py-1 rounded-full bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 text-green-700 dark:text-green-200 text-sm font-semibold">
                                                                            {{ $this->getTotalValidTransactions() }} válidas
                                                                        </div>
                                                                        <div class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 text-blue-700 dark:text-blue-200 text-sm font-semibold">
                                                                            {{ count($transactions) }} no arquivo
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="p-6">
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                                                                        @foreach($transactions as $index => $transaction)
                                                                            <div class="group relative bg-gradient-to-br from-white via-blue-50/40 to-purple-50/40 dark:from-gray-800 dark:via-blue-900/20 dark:to-purple-900/20 border-2 border-gray-200 dark:border-gray-700 rounded-2xl p-5 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">
                                                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-purple-500 to-pink-500 rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                                                                <div class="flex items-start justify-between mb-4">
                                                                                    <div class="space-y-1">
                                                                                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wide">
                                                                                            {{ $transaction['date'] ?? 'Sem data' }}
                                                                                        </p>
                                                                                        <p class="text-base font-bold text-gray-900 dark:text-white leading-tight line-clamp-2" title="{{ $transaction['description'] ?? '' }}">
                                                                                            {{ $transaction['description'] ?? 'Sem descrição' }}
                                                                                        </p>
                                                                                        @if(empty($transaction['description']))
                                                                                            <p class="text-xs text-red-500">Descrição obrigatória</p>
                                                                                        @endif
                                                                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-200">
                                                                                            <i class="bi bi-diagram-3 text-indigo-500"></i>
                                                                                            {{ $types->firstWhere('id', $transaction['type'] ?? null)?->name ?? 'Tipo não definido' }}
                                                                                        </span>
                                                                                    </div>
                                                                                    <button wire:click="removeTransaction({{ $index }})"
                                                                                            class="group flex items-center justify-center w-10 h-10 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg">
                                                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>

                                                                                <div class="mb-4 p-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-700 flex items-center justify-between">
                                                                                    <div>
                                                                                        <p class="text-sm text-gray-600 dark:text-gray-300">Valor</p>
                                                                                        <p class="text-2xl font-extrabold bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                                                                            R$ {{ isset($transaction['amount']) ? number_format($transaction['amount'], 2, ',', '.') : '0,00' }}
                                                                                        </p>
                                                                                    </div>
                                                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ ($transaction['type'] ?? '') === 'expense' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' }}">
                                                                                        {{ ($transaction['type'] ?? '') === 'expense' ? 'Saída' : 'Entrada' }}
                                                                                    </span>
                                                                                </div>

                                                                                <div class="space-y-3">
                                                                                    <div class="space-y-1">
                                                                                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"></path>
                                                                                            </svg>
                                                                                            Tipo
                                                                                        </label>
                                                                                        <select wire:model="transactions.{{ $index }}.type"
                                                                                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                                                                            <option value="">Selecionar tipo</option>
                                                                                            @foreach($types as $type)
                                                                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="space-y-1">
                                                                                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                                                            </svg>
                                                                                            Categoria
                                                                                        </label>
                                                                                        <select wire:model="transactions.{{ $index }}.category_id"
                                                                                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                                                                            <option value="">Selecionar</option>
                                                                                            @foreach($categories as $category)
                                                                                                <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="space-y-1">
                                                                                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                                                            <i class="bi bi-piggy-bank text-indigo-500"></i>
                                                                                            Cofrinho
                                                                                        </label>
                                                                                        <select wire:model="transactions.{{ $index }}.cofrinho_id"
                                                                                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                                                                            <option value="">Selecionar Cofrinho</option>
                                                                                            @if(isset($cofrinhos))
                                                                                                @foreach($cofrinhos as $cofrinho)
                                                                                                    <option value="{{ $cofrinho->id }}">{{ $cofrinho->nome }}</option>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="space-y-1">
                                                                                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                                                            <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.042 12.042 0 0112 15c2.21 0 4.28.57 6.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                            </svg>
                                                                                            Cliente
                                                                                        </label>
                                                                                        <select wire:model="transactions.{{ $index }}.client_id"
                                                                                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                                                                            <option value="">Nenhum</option>
                                                                                            @foreach($clients as $client)
                                                                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
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
                                                @endif
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>

                                                            <div class="space-y-1">
                                                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.042 12.042 0 0112 15c2.21 0 4.28.57 6.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                    Cliente
                                                                </label>
                                                                <select wire:model="transactions.{{ $index }}.client_id"
                                                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                                                    <option value="">Nenhum</option>
                                                                    @foreach($clients as $client)
                                                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                                    <p class="text-xs text-red-500">Descrição obrigatória</p>
                                                @endif
                                            </td>

                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 py-1 rounded-lg font-semibold {{ ($transaction['type'] ?? '') === 'expense' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200' }}">
                                                    {{ isset($transaction['amount']) ? number_format($transaction['amount'], 2, ',', '.') : '0,00' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <select wire:model="transactions.{{ $index }}.type"
                                                        class="w-full px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 text-xs">
                                                    <option value="">Tipo</option>
                                                    @foreach($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

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

                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                                <button wire:click="removeTransaction({{ $index }})" class="btn-delete text-red-600 hover:text-red-700 text-xs font-semibold">Remover</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
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
        @endif
    </div>

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
    document.addEventListener('DOMContentLoaded', function() {
        const uploadButton = document.querySelector('label[for="file"]');
        if (uploadButton) {
            uploadButton.setAttribute('data-tooltip', 'Clique para selecionar arquivo ou arraste e solte aqui');
        }

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

</div>
