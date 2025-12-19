@props([
    'title' => 'Upload de Transações',
    'description' => 'Importar transações a partir de arquivo PDF ou CSV',
    'backRoute' => null,
    'showConfirmation' => false,
    'transactionsCount' => 0,
    'totalValue' => 0,
    'hasDuplicates' => false,
])

<!-- Header Moderno com Gradiente e Glassmorphism (estilo sales) -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-lg mb-4">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo (reduzido) -->
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($backRoute)
                <!-- Botão voltar compacto -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-200 shadow-sm border border-white/40 dark:border-slate-600/40 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-lg text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-150"></i>
                    <div class="absolute inset-0 rounded-lg bg-blue-500/8 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                @endif

                <!-- Ícone principal e título (compacto) -->
                <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-lg shadow-md shadow-purple-500/15">
                    <i class="bi bi-file-earmark-arrow-up text-white text-2xl"></i>
                    <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-white/20 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                        💼 {!! $description !!}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if (!$showConfirmation)
                    <!-- Botão Processar Arquivo -->
                    <button type="button" wire:click="uploadFile"
                        wire:loading.attr="disabled"
                        wire:target="uploadFile"
                        class="group relative inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">

                        <span wire:loading.remove wire:target="uploadFile" class="flex items-center gap-2">
                            <i class="bi bi-rocket-takeoff text-lg"></i>
                            Processar Arquivo
                        </span>

                        <span wire:loading wire:target="uploadFile" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando...
                        </span>

                        <div class="absolute inset-0 rounded-xl bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                @else
                    <!-- Badge de contagem e total -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg px-4 py-2">
                            <i class="bi bi-collection-fill text-indigo-600 dark:text-indigo-400 text-lg"></i>
                            <span class="font-bold text-indigo-700 dark:text-indigo-300">{{ $transactionsCount }} transações</span>
                        </div>

                        <div class="flex items-center gap-2 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 border border-green-200 dark:border-green-700 rounded-lg px-4 py-2">
                            <i class="bi bi-cash-coin text-green-600 dark:text-green-400 text-lg"></i>
                            <span class="font-bold text-lg bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                R$ {{ number_format($totalValue, 2, ',', '.') }}
                            </span>
                        </div>

                        @if($hasDuplicates)
                        <!-- Botão Excluir Duplicadas -->
                        <button wire:click="removeDuplicates" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <i class="bi bi-trash-fill text-lg"></i>
                            <span>Excluir Duplicadas</span>
                        </button>
                        @endif
                    </div>

                    <!-- Botões de ação -->
                    <button type="button" wire:click="cancelUpload"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg shadow hover:scale-105 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-x-lg"></i>
                        <span class="font-medium">Cancelar</span>
                    </button>

                    <button type="button" wire:click="confirmTransactions"
                        wire:loading.attr="disabled"
                        wire:target="confirmTransactions"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-lg shadow-lg hover:scale-105 transition-all duration-200 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">

                        <span wire:loading.remove wire:target="confirmTransactions" class="flex items-center gap-2">
                            <i class="bi bi-check-lg text-lg"></i>
                            <span class="font-bold">Confirmar</span>
                        </span>

                        <span wire:loading wire:target="confirmTransactions" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirmando...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
