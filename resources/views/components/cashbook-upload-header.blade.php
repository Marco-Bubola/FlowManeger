@props([
    'title' => 'Upload de Transações',
    'description' => 'Importe transações de arquivos PDF ou CSV',
    'showProcessButton' => false,
    'showConfirmation' => false,
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-green-50/90 to-emerald-50/80 dark:from-slate-800/90 dark:via-green-900/30 dark:to-emerald-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-green-400/20 via-emerald-400/20 to-teal-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-emerald-400/10 via-green-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Título e Ícone -->
            <div class="flex items-center gap-6">
                <!-- Ícone principal -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 via-emerald-500 to-teal-500 rounded-2xl shadow-xl shadow-green-500/25">
                    <i class="bi bi-cloud-upload text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-green-700 to-emerald-700 dark:from-slate-100 dark:via-green-300 dark:to-emerald-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-300 font-medium">
                        <i class="bi bi-info-circle text-green-500 mr-2"></i>
                        {{ $description }}
                    </p>
                </div>
            </div>

            <!-- Botões de Ação -->
            @if($showProcessButton && !$showConfirmation)
            <div class="flex items-center gap-3">
                <button wire:click="uploadFile"
                        class="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 hover:from-green-700 hover:via-emerald-700 hover:to-teal-700 text-white font-bold rounded-2xl transition-all duration-300 shadow-xl shadow-green-500/25 hover:shadow-2xl hover:shadow-green-500/40 transform hover:scale-105">
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <div wire:loading.remove wire:target="uploadFile" class="relative z-10 flex items-center gap-2">
                        <i class="bi bi-arrow-up-circle text-xl"></i>
                        <span>Processar Arquivo</span>
                    </div>

                    <div wire:loading wire:target="uploadFile" class="relative z-10 flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processando...</span>
                    </div>
                </button>
            </div>
            @endif

            @if($showConfirmation)
            <div class="flex items-center gap-3">
                <button wire:click="cancelUpload"
                        class="group relative inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-xl hover:from-slate-200 hover:to-slate-300 dark:hover:from-slate-600 dark:hover:to-slate-500 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancelar</span>
                </button>

                <button wire:click="confirmTransactions"
                        class="group relative inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 hover:from-green-700 hover:via-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all duration-300 shadow-xl shadow-green-500/25 hover:shadow-2xl hover:shadow-green-500/40 transform hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <div wire:loading.remove wire:target="confirmTransactions" class="relative z-10 flex items-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        <span>Confirmar Transações</span>
                    </div>

                    <div wire:loading wire:target="confirmTransactions" class="relative z-10 flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Salvando...</span>
                    </div>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
