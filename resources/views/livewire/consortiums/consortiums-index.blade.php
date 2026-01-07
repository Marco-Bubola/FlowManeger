@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
@endpush

<div class="w-full" x-data="{
    showFilters: false,
    fullHd: false,
    ultra: false,
    showDeleteModal: @entangle('showDeleteModal').live,
    initResponsiveWatcher() {
        const mq = window.matchMedia('(min-width: 1850px)');
        const mqUltra = window.matchMedia('(min-width: 2400px)');

        const sync = () => {
            this.fullHd = mq.matches;
            if (typeof $wire !== 'undefined') {
                $wire.set('fullHdLayout', mq.matches);
            }
        };

        const syncUltra = () => {
            this.ultra = mqUltra.matches;
            if (typeof $wire !== 'undefined') {
                $wire.set('ultraWindClient', mqUltra.matches);
            }
        };

        sync();
        syncUltra();

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', sync);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(sync);
        }

        if (typeof mqUltra.addEventListener === 'function') {
            mqUltra.addEventListener('change', syncUltra);
        } else if (typeof mqUltra.addListener === 'function') {
            mqUltra.addListener(syncUltra);
        }
    }
}" x-init="initResponsiveWatcher()" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
    x-bind:data-full-hd="fullHd ? 'true' : 'false'">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="">
        <!-- Header Moderno -->
        <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-emerald-50/90 to-teal-50/80 dark:from-slate-800/90 dark:via-emerald-900/30 dark:to-teal-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-teal-400/20 to-green-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-emerald-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

            <div class="relative px-4 py-3">
                <div class="flex items-center justify-between gap-6 mb-6">
                    <div class="flex items-center gap-6">
                        <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 via-teal-500 to-green-500 rounded-2xl shadow-xl shadow-emerald-500/25">
                            <i class="bi bi-piggy-bank text-white text-3xl"></i>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                        </div>

                        <div class="space-y-2">
                            <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                                Consórcios
                            </h1>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                                    <i class="bi bi-check-circle text-emerald-600 dark:text-emerald-400"></i>
                                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $totalActive }} ativos</span>
                                </div>
                                @if($totalCompleted > 0)
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                                    <i class="bi bi-trophy text-blue-600 dark:text-blue-400"></i>
                                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $totalCompleted }} concluídos</span>
                                </div>
                                @endif
                                @if($totalParticipants > 0)
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl border border-purple-200 dark:border-purple-700">
                                    <i class="bi bi-people text-purple-600 dark:text-purple-400"></i>
                                    <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">{{ $totalParticipants }} participantes</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative group w-96">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar consórcios..."
                                class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all duration-200 shadow-md text-sm">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                            </div>
                            <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                                <i class="bi bi-x text-xs"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-2 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl shadow-md">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-piggy-bank text-white text-sm"></i>
                            </div>
                            <div class="text-sm">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $consortiums->total() }}</span>
                                <span class="text-slate-600 dark:text-slate-400 ml-1">{{ $consortiums->total() === 1 ? 'consórcio' : 'consórcios' }}</span>
                            </div>
                        </div>

                        @if ($consortiums->hasPages())
                        <div class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl p-1 shadow-md">
                            @if ($consortiums->currentPage() > 1)
                            <button wire:click.prevent="previousPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                                <i class="bi bi-chevron-left text-sm text-slate-600 dark:text-slate-300"></i>
                            </button>
                            @endif
                            <span class="px-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                {{ $consortiums->currentPage() }} / {{ $consortiums->lastPage() }}
                            </span>
                            @if ($consortiums->hasMorePages())
                            <button wire:click.prevent="nextPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                                <i class="bi bi-chevron-right text-sm text-slate-600 dark:text-slate-300"></i>
                            </button>
                            @endif
                        </div>
                        @endif

                        <button wire:click="toggleTips"
                            class="p-2.5 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                            <i class="bi bi-lightbulb"></i>
                        </button>

                        <button @click="showFilters = !showFilters"
                            class="p-2.5 bg-white/80 hover:bg-emerald-100 dark:bg-slate-800/80 dark:hover:bg-emerald-900/50 border border-slate-200 dark:border-slate-600 rounded-xl transition-all duration-200 shadow-md"
                            :class="{ 'bg-emerald-100 dark:bg-emerald-900 border-emerald-300 dark:border-emerald-600': showFilters }">
                            <i class="bi bi-funnel text-emerald-600 dark:text-emerald-400"></i>
                        </button>

                        <a href="{{ route('consortiums.create') }}"
                           class="group flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                            <span class="text-sm">Novo Consórcio</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de Cards -->
        <div class="sales-grid gap-4 mb-8" x-ref="consortiumsGrid"
            data-ultrawind="{{ $ultraWindClient ?? false ? 'true' : 'false' }}"
            data-full-hd="{{ $fullHdLayout ? 'true' : 'false' }}" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
            x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            @forelse($consortiums as $consortium)
                <x-consortium-card :consortium="$consortium" />
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div class="w-20 h-20 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-piggy-bank text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhum consórcio encontrado
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if ($search ?? false)
                                Não encontramos consórcios com o termo "{{ $search }}".
                            @else
                                Comece criando seu primeiro consórcio.
                            @endif
                        </p>
                        <a href="{{ route('consortiums.create') }}"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 rounded-xl hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar Novo Consórcio
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if ($consortiums->hasPages())
            <div class="mt-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-r from-white via-slate-50 to-emerald-50 dark:from-slate-800 dark:via-slate-700 dark:to-emerald-900 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-600/50 p-4 md:p-6 backdrop-blur-xl">

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-600 dark:text-slate-300">Exibir</label>
                            <select wire:model.live="perPage"
                                class="text-sm rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                                @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="text-sm text-slate-500 dark:text-slate-400">por página</span>
                        </div>

                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            Mostrando <span class="font-semibold text-slate-800 dark:text-white">{{ $consortiums->firstItem() ?? 0 }}</span>
                            até <span class="font-semibold text-slate-800 dark:text-white">{{ $consortiums->lastItem() ?? 0 }}</span>
                            de <span class="font-semibold text-slate-800 dark:text-white">{{ $consortiums->total() }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click.prevent="gotoPage(1)" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-left"></i>
                        </button>

                        <button @if ($consortiums->onFirstPage()) disabled @endif wire:click.prevent="previousPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        @php
                            $start = max(1, $consortiums->currentPage() - 2);
                            $end = min($consortiums->lastPage(), $consortiums->currentPage() + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            <button wire:click.prevent="gotoPage({{ $i }})" wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md text-sm {{ $consortiums->currentPage() === $i ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600' }}">
                                {{ $i }}
                            </button>
                        @endfor

                        <button @if (!$consortiums->hasMorePages()) disabled @endif wire:click.prevent="nextPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <button wire:click.prevent="gotoPage({{ $consortiums->lastPage() }})" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-right"></i>
                        </button>
                    </div>

                    <div class="flex items-center gap-2" x-data>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Ir para</label>
                        <input x-ref="pageInput" type="number" min="1" max="{{ $consortiums->lastPage() }}"
                            class="w-20 text-sm rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1 text-slate-700 dark:text-slate-200"
                            placeholder="#">
                        <button @click.prevent="$wire.call('gotoPage', $refs.pageInput.value)"
                            class="px-3 py-1 rounded-md text-sm bg-emerald-600 text-white">
                            Ir
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Exclusão -->
        <div class="fixed inset-0 z-[9999] overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            wire:click="cancelDelete"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100"
                x-transition:enter="transition ease-out duration-300 delay-75"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4" wire:click.stop>
                <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/30 dark:border-zinc-700/50 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-orange-500 to-pink-600"></div>

                    <div class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-trash text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                Excluir Consórcio
                            </h3>
                        </div>
                        <button type="button" wire:click="cancelDelete"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100/50 hover:bg-slate-200/70 dark:bg-zinc-700/50 dark:hover:bg-zinc-600/70 rounded-xl text-sm w-10 h-10 flex justify-center items-center transition-all duration-200 backdrop-blur-sm"
                            title="Fechar">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <div class="p-6 text-center">
                        <h3 class="mb-2 text-2xl font-bold text-slate-900 dark:text-white">
                            Excluir Consórcio "{{ $deletingConsortium?->name }}"?
                        </h3>
                        <p class="mb-6 text-slate-600 dark:text-slate-400 leading-relaxed">
                            Esta ação não pode ser desfeita. O consórcio e todos os seus dados (participantes, pagamentos, sorteios) serão <span class="font-semibold text-red-600 dark:text-red-400">permanentemente removidos</span>.
                        </p>

                        <div class="flex gap-3">
                            <button wire:click="cancelDelete" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-slate-700 dark:text-slate-300 bg-slate-100/70 dark:bg-zinc-700/70 hover:bg-slate-200/80 dark:hover:bg-zinc-600/80 rounded-xl font-semibold transition-all duration-200 backdrop-blur-sm border border-slate-200/50 dark:border-zinc-600/50 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="bi bi-shield-check text-lg"></i>
                                <span>Cancelar</span>
                            </button>
                            <button wire:click="deleteConsortium" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 ring-2 ring-red-500/20 focus:ring-4 focus:ring-red-500/40">
                                <i class="bi bi-trash-fill text-lg"></i>
                                <span>Confirmar Exclusão</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
