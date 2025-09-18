@props(['placeholder' => 'Buscar vendas...', 'search' => ''])

<!-- Barra de Pesquisa Moderna -->
<div class="relative mb-6">
    <div class="relative group">
        <!-- Input principal -->
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="{{ $placeholder }}"
                   class="w-full pl-14 pr-20 py-4 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900
                          border border-slate-200/50 dark:border-slate-600/50 rounded-2xl
                          text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400
                          focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-400
                          transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm
                          text-lg font-medium">

            <!-- Ícone de busca -->
            <div class="absolute left-5 top-1/2 transform -translate-y-1/2">
                <i class="bi bi-search text-slate-500 dark:text-slate-400 text-xl group-focus-within:text-purple-500 transition-colors duration-200"></i>
            </div>

            <!-- Botão limpar -->
            <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                <button wire:click="$set('search', '')"
                        x-show="$wire.search && $wire.search.length > 0"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-50"
                        class="group/clear p-2 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500
                               text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white
                               rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                        title="Limpar busca">
                    <i class="bi bi-x-lg text-sm group-hover/clear:rotate-90 transition-transform duration-200"></i>
                </button>
            </div>
        </div>

        <!-- Indicador de carregamento -->
        <div wire:loading.delay wire:target="search"
             class="absolute right-16 top-1/2 transform -translate-y-1/2">
            <div class="animate-spin rounded-full h-5 w-5 border-2 border-purple-500 border-t-transparent"></div>
        </div>

        <!-- Efeito de brilho -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-purple-500/10 via-transparent to-blue-500/10 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
    </div>

    <!-- Sugestões de busca -->
    <div class="mt-3 flex flex-wrap gap-2">
        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">Buscar por:</span>

        <button wire:click="$set('search', 'pendente')"
                class="group px-3 py-1 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50
                       text-yellow-700 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300
                       text-xs font-medium rounded-lg transition-all duration-200 border border-yellow-200 dark:border-yellow-700">
            <i class="bi bi-clock mr-1"></i>
            Status pendente
        </button>

        <button wire:click="$set('search', 'hoje')"
                class="group px-3 py-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50
                       text-blue-700 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300
                       text-xs font-medium rounded-lg transition-all duration-200 border border-blue-200 dark:border-blue-700">
            <i class="bi bi-calendar-day mr-1"></i>
            Vendas de hoje
        </button>

        <button wire:click="$set('search', 'pago')"
                class="group px-3 py-1 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50
                       text-green-700 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300
                       text-xs font-medium rounded-lg transition-all duration-200 border border-green-200 dark:border-green-700">
            <i class="bi bi-check-circle mr-1"></i>
            Status pago
        </button>
    </div>

    <!-- Resultados da busca -->
    @if($search && strlen($search) > 0)
    <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20
                rounded-xl border border-blue-200/50 dark:border-blue-700/50">
        <div class="flex items-center gap-2 text-sm">
            <i class="bi bi-search text-blue-600 dark:text-blue-400"></i>
            <span class="text-slate-700 dark:text-slate-300">
                Buscando por:
                <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ $search }}"</span>
            </span>
            <div wire:loading.delay wire:target="search" class="ml-auto">
                <span class="text-slate-500 dark:text-slate-400 text-xs">Carregando...</span>
            </div>
        </div>
    </div>
    @endif
</div>
