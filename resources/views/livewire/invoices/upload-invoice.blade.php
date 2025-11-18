<div class="w-full">
    @push('styles')
        @vite('resources/css/upload-animations.css')
    @endpush

    @push('scripts')
        @vite('resources/js/upload-interactions.js')
    @endpush

    <!-- Incluir controles de tema -->
    @include('components.theme-controls')

    <!-- Incluir sistema de notificações -->
    @include('components.toast-notifications')

    <div class="">
        <!-- Modern header component (apenas um) -->
        <x-upload-header :title="'Upload de Transações'" :description="'Impor transações a partir de arquivo PDF ou CSV'" :backRoute="route('invoices.index', ['bankId' => $bankId])" :showConfirmation="$showConfirmation" :transactionsCount="count($transactions)" />
        <!-- Content -->
        <div class="w-full px-4 sm:px-6 lg:px-8 ">
            @if (!$showConfirmation)
                <!-- Upload Form -->
                <div
                    class="w-full bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 dark:from-gray-800 dark:via-blue-900/30 dark:to-purple-900/30 rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 backdrop-blur-sm">
                    <!-- Header do Card -->
                    <div
                        class="px-8 py-6 border-b border-blue-200/50 dark:border-gray-700/50 bg-gradient-to-r from-blue-50/50 to-purple-50/50 dark:from-gray-700/50 dark:to-gray-600/50 rounded-t-3xl">
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-600 dark:to-purple-700 rounded-2xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-gray-800 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                                    Enviar Arquivo
                                </h2>
                                <p class="text-lg lg:text-xl text-gray-600 dark:text-gray-300 font-medium">
                                    <svg class="w-4 h-4 inline mr-1 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                                        </path>
                                    </svg>
                                    Selecione um arquivo PDF ou CSV para importar transações
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <form wire:submit="uploadFile" class="space-y-8">
                            <!-- File Upload Zone -->
                            <div
                                class="relative border-3 border-dashed border-blue-300 dark:border-blue-600 rounded-2xl p-12 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 group bg-gradient-to-br from-blue-50/30 via-white to-purple-50/30 dark:from-gray-700/30 dark:via-gray-800/30 dark:to-gray-700/30 hover:shadow-2xl transform hover:scale-[1.02]">
                                <!-- Background decorativo -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-blue-100/20 via-transparent to-purple-100/20 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>

                                <div class="relative space-y-6">
                                    <!-- Ícone principal animado -->
                                    <div class="flex justify-center">
                                        <div class="relative">
                                            <div
                                                class="flex items-center justify-center w-24 h-24 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 dark:from-blue-600 dark:via-purple-600 dark:to-pink-600 rounded-3xl shadow-2xl transform group-hover:rotate-6 transition-all duration-300 animate-pulse">
                                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <!-- Pontos decorativos -->
                                            <div
                                                class="absolute -top-3 -right-3 w-6 h-6 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-bounce">
                                            </div>
                                            <div
                                                class="absolute -bottom-3 -left-3 w-4 h-4 bg-gradient-to-r from-green-400 to-blue-500 rounded-full animate-ping">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <h3
                                            class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                                            Arraste e solte ou clique para selecionar
                                        </h3>
                                        <div
                                            class="flex items-center justify-center space-x-4 text-lg text-gray-600 dark:text-gray-300">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                <span class="font-semibold">PDF</span>
                                            </div>
                                            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                <span class="font-semibold">CSV</span>
                                            </div>
                                            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-blue-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2h-4m-6-6V9a2 2 0 012-2h2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2m0 0v2a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                    </path>
                                                </svg>
                                                <span class="font-semibold">até 10MB</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botão de upload estilizado -->
                                    <div class="flex justify-center">
                                        <label for="file"
                                            class="group cursor-pointer relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-700 dark:via-purple-700 dark:to-pink-700 text-white font-bold text-lg rounded-2xl hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg">
                                            <svg class="w-6 h-6 mr-3 group-hover:animate-bounce" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            <span>Escolher Arquivo</span>
                                            <input wire:model="file" type="file" id="file"
                                                accept=".pdf,.csv" class="hidden">
                                            <!-- Efeito de brilho -->
                                            <div
                                                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 via-transparent to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Feedback do arquivo selecionado -->
                                    @if ($file)
                                        <div
                                            class="mt-8 p-6 bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-green-900/20 dark:via-emerald-900/20 dark:to-teal-900/20 rounded-2xl border border-green-200 dark:border-green-700 shadow-lg transform transition-all duration-300 hover:scale-[1.02]">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div
                                                        class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 rounded-xl shadow-lg">
                                                        <svg class="w-6 h-6 text-white animate-pulse" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-lg font-bold text-green-800 dark:text-green-200">
                                                            <svg class="w-5 h-5 inline mr-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                </path>
                                                            </svg>
                                                            Arquivo Selecionado com Sucesso!
                                                        </p>
                                                        <p
                                                            class="text-sm text-green-700 dark:text-green-300 font-medium">
                                                            {{ $file->getClientOriginalName() }}</p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-ping"></div>
                                                    <span class="text-sm font-semibold">Pronto</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @error('file')
                                        <div
                                            class="mt-4 p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-xl border border-red-200 dark:border-red-700">
                                            <div class="flex items-center space-x-3">
                                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <p class="text-red-700 dark:text-red-300 font-medium">{{ $message }}
                                                </p>
                                            </div>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-center">
                                <button type="submit"
                                    class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-700 dark:via-purple-700 dark:to-pink-700 text-white text-lg font-bold rounded-2xl hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                    wire:loading.attr="disabled" wire:target="uploadFile"
                                    @if (!$file) disabled @endif>
                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    </div>

                                    <!-- Loading Spinner -->
                                    <div wire:loading wire:target="uploadFile"
                                        class="absolute inset-0 flex items-center justify-center">
                                        <svg class="animate-spin h-6 w-6 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>

                                    <!-- Normal Content -->
                                    <div wire:loading.remove wire:target="uploadFile"
                                        class="flex items-center relative z-10">
                                        <svg class="w-6 h-6 mr-3 group-hover:animate-bounce" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <span>🚀 Processar Arquivo com IA</span>
                                        <div class="ml-3 w-2 h-2 bg-white rounded-full animate-ping"></div>
                                    </div>

                                    <!-- Loading Content -->
                                    <div wire:loading wire:target="uploadFile"
                                        class="flex items-center relative z-10 text-white/80">
                                        <span>⚡ Processando com IA...</span>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- Confirmation View - Full Width -->
                <div class="w-full ">
                    {{-- Header já renderizado acima; não repetir aqui --}}

                    <!-- Transações em Grid: 4 por linha (normal) e 6 em ultrawind -->
                    <div class="w-full">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ultrawind:grid-cols-6 gap-6">
                            @foreach ($transactions as $index => $transaction)
                                <div
                                    class="group relative bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 dark:from-gray-800 dark:via-blue-900/20 dark:to-purple-900/20 border-2 border-gray-200 dark:border-gray-600 rounded-2xl p-6 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">
                                    <!-- Indicador visual lateral -->
                                    <div
                                        class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-purple-500 to-pink-500 rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    </div>

                                    <!-- Header do Card -->
                                    <div class="flex justify-between items-start mb-6">
                                        <!-- Descrição e Data -->
                                        <div class="space-y-3">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-md flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p
                                                        class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                                                        {{ $transaction['description'] ?? 'Sem descrição' }}
                                                    </p>
                                                    <div
                                                        class="flex items-center space-x-2 text-gray-600 dark:text-gray-300 mt-1">
                                                        <svg class="w-4 h-4 text-blue-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                            </path>
                                                        </svg>
                                                        <!-- Campo de data editável: chama o método Livewire updateTransactionDate -->
                                                        <input type="date" value="{{ $transaction['date'] ?? '' }}"
                                                            wire:change="updateTransactionDate({{ $index }}, $event.target.value)"
                                                            class="px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Botão de remover -->
                                        <button wire:click="removeTransaction({{ $index }})"
                                            class="group flex items-center justify-center w-10 h-10 bg-gradient-to-r from-red-500 to-pink-600 dark:from-red-600 dark:to-pink-700 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg">
                                            <svg class="w-5 h-5 group-hover:animate-pulse" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Conteúdo do Card -->
                                    <div class="space-y-6">


                                        <!-- Valor e Parcelas -->
                                        <div
                                            class="flex items-center space-x-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-700">
                                            <div
                                                class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 rounded-xl shadow-md">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p
                                                    class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                                    R$ {{ number_format($transaction['value'] ?? 0, 2, ',', '.') }}
                                                </p>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-purple-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    <span
                                                        class="text-gray-600 dark:text-gray-300 font-medium">{{ $transaction['installments'] ?? 'À vista' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seletores -->
                                        <div class="space-y-4">
                                            <!-- Categoria -->
                                            <div class="space-y-2">
                                                <label
                                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                        </path>
                                                    </svg>
                                                    Categoria
                                                </label>
                                                <select
                                                    wire:change="updateTransactionCategory({{ $index }}, $event.target.value)"
                                                    class="w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500">
                                                    <option value="">🏷️ Selecione categoria</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id_category }}"
                                                            {{ ($transaction['category_id'] ?? '') == $category->id_category ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Cliente -->
                                            <div class="space-y-2">
                                                <label
                                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                        </path>
                                                    </svg>
                                                    Cliente
                                                </label>
                                                <select
                                                    wire:change="updateTransactionClient({{ $index }}, $event.target.value)"
                                                    class="w-full px-4 py-3 text-sm bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500">
                                                    <option value="">👤 Cliente (opcional)</option>
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}"
                                                            {{ ($transaction['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                                                            {{ $client->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Barra de progresso decorativa -->
                                    <div
                                        class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>


    </div>

    <!-- Scripts adicionais para interações -->
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
                    showToast('loading', 'Processando arquivo...');
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
