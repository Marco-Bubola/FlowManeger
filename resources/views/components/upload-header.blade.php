@props([
    'title' => 'Upload de Transações',
    'description' => 'Impor transações a partir de arquivo PDF ou CSV',
    'icon' => 'bi bi-file-earmark-arrow-up',
    'backRoute' => null,
    'showConfirmation' => false,
    'transactionsCount' => 0,
    'actions' => null,
])
<!-- Header Moderno e Organizado -->
<div class="relative overflow-hidden rounded-2xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-white/85 to-indigo-50/60 dark:from-slate-900/70 dark:to-indigo-900/20 backdrop-blur-md pointer-events-none"></div>

    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-gradient-to-br from-purple-300/20 to-indigo-300/20 blur-2xl pointer-events-none"></div>
    <div class="absolute -bottom-8 -left-6 w-36 h-36 rounded-full bg-gradient-to-tr from-emerald-200/10 to-blue-200/10 blur-xl pointer-events-none"></div>

    <div class="relative px-6 py-6 lg:py-8">
        <div class="flex items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                @if ($backRoute)
                    <a href="{{ $backRoute }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white/70 dark:bg-slate-800/80 shadow-sm border border-white/30 hover:scale-105 transition">
                        <i class="bi bi-arrow-left text-lg text-slate-700 dark:text-slate-200"></i>
                    </a>
                @endif

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-14 h-14 rounded-lg bg-gradient-to-br from-indigo-600 to-pink-500 shadow-xl text-white">
                        <i class="{{ $icon }} text-3xl"></i>
                    </div>

                    <div class="min-w-0">
                        <h1 class="text-3xl lg:text-4xl font-extrabold leading-tight text-slate-900 dark:text-white">
                            {{ $title }}
                        </h1>
                        <p class="mt-1 text-sm lg:text-base text-slate-600 dark:text-slate-300">
                            {!! $description !!}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 z-20">
                {!! $actions ?? '' !!}

                <div class="hidden lg:flex items-center gap-3">
                    <div class="rounded-xl px-3 py-2 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200/50 dark:border-emerald-700/30 flex items-center gap-3">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-200">Pronto para upload</span>
                    </div>

                    @if ($showConfirmation)
                        <div class="flex items-center gap-3 bg-white/10 rounded-lg px-3 py-2 ring-1 ring-white/20">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/10 text-white rounded-md px-3 py-2 flex items-center gap-2">
                                    <i class="bi bi-collection-fill text-white text-lg"></i>
                                    <span class="text-white font-bold text-lg">{{ $transactionsCount }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="cancelUpload"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 rounded-md shadow hover:scale-105 transition transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-300 z-30">
                                    <i class="bi bi-x-lg text-base"></i>
                                    <span class="text-sm font-medium">Cancelar</span>
                                </button>

                                <button type="button" wire:click="confirmTransactions"
                                    wire:loading.attr="disabled" wire:target="confirmTransactions"
                                    @if($processing) disabled @endif
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-emerald-500 to-green-500 text-white rounded-md shadow-lg hover:scale-105 transition transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-300 z-40">
                                    <i class="bi bi-check-lg"></i>
                                    <span class="text-sm font-semibold">Confirmar</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <button class="p-3 rounded-lg bg-white/60 dark:bg-slate-800/60 shadow-sm hover:scale-105 transition" title="Ajuda">
                        <i class="bi bi-question-circle text-slate-700 dark:text-slate-300"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
