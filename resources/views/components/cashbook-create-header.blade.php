@props([
    'title' => 'Nova Transação',
    'description' => 'Adicione uma nova transação ao seu livro caixa',
    'showSave' => true,
    'showCancel' => true,
])

<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-4">
                <button wire:click="cancel"
                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-gray-100 to-white dark:from-zinc-700 dark:to-zinc-800 hover:from-gray-200 hover:to-gray-100 dark:hover:from-zinc-600 dark:hover:to-zinc-700 rounded-lg transition-colors duration-200 border border-white/30 dark:border-zinc-600/40">
                    <i class="fas fa-arrow-left text-gray-600 dark:text-gray-200"></i>
                </button>

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 text-white shadow-md">
                            <i class="fas fa-wallet"></i>
                        </span>
                        {{ $title }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                @if($showCancel)
                <button wire:click="cancel"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                @endif

                @if($showSave)
                <button wire:click="save"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Salvar
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
