@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
@endpush

<div class="w-full" x-data="{
    showFilters: false,
    fullHd: false,
    ultra: false,
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
        <x-sales-index-header
            title="Vendas"
            :total-sales="$totalSales ?? 0"
            :pending-sales="$pendingSales ?? 0"
            :today-sales="$todaySales ?? 0"
            :total-revenue="$totalRevenue ?? 0"
            :show-quick-actions="true"
            :sales="$sales"
            :search="$search"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection"
            :status-filter="$statusFilter"
            :client-filter="$clientFilter"
            :start-date="$startDate"
            :end-date="$endDate"
            :min-value="$minValue"
            :max-value="$maxValue"
        />

        <!-- Filtros Avançados -->
        <x-sales-filters :show-filters="false" :clients="$clients ?? collect()" :sellers="$sellers ?? collect()" :status-filter="$statusFilter" :client-filter="$clientFilter"
            :start-date="$startDate" :end-date="$endDate" :min-value="$minValue" :max-value="$maxValue" :payment-method-filter="$paymentMethodFilter"
            :seller-filter="$sellerFilter" :quick-filter="$quickFilter" :sort-by="$sortBy" :sort-direction="$sortDirection" />

        <!-- Grid de Cards de Vendas -->
        <div class="sales-grid gap-4 mb-8" x-ref="salesGrid"
            data-ultrawind="{{ $ultraWindClient ?? false ? 'true' : 'false' }}"
            data-full-hd="{{ $fullHdLayout ? 'true' : 'false' }}" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
            x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            @forelse($sales as $sale)
                <x-sale-card :sale="$sale" />
            @empty
                <!-- Estado Vazio -->
                <div class="col-span-full">
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-cart-x text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhuma venda encontrada
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if ($search ?? false)
                                Não encontramos vendas com o termo "{{ $search }}".
                            @else
                                Comece registrando sua primeira venda.
                            @endif
                        </p>
                        <a href="{{ route('sales.create') }}"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar Nova Venda
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginação Moderna Customizada -->
        @if ($sales->hasPages())
            <div class="mt-8">
                <div
                    class="flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-600/50 p-4 md:p-6 backdrop-blur-xl">

                    <!-- Left: Per page selector & summary -->
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
                            Mostrando <span
                                class="font-semibold text-slate-800 dark:text-white">{{ $sales->firstItem() ?? 0 }}</span>
                            até <span
                                class="font-semibold text-slate-800 dark:text-white">{{ $sales->lastItem() ?? 0 }}</span>
                            de <span class="font-semibold text-slate-800 dark:text-white">{{ $sales->total() }}</span>
                        </div>
                    </div>

                    <!-- Center: Page buttons -->
                    <div class="flex items-center gap-2">
                        <button wire:click.prevent="gotoPage(1)" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-left"></i>
                        </button>

                        <button @if ($sales->onFirstPage()) disabled @endif wire:click.prevent="previousPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        {{-- Numeric pages: mostrar range de páginas ao redor da atual --}}
                        @php
                            $start = max(1, $sales->currentPage() - 2);
                            $end = min($sales->lastPage(), $sales->currentPage() + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            <button wire:click.prevent="gotoPage({{ $i }})" wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md text-sm {{ $sales->currentPage() === $i ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600' }}">
                                {{ $i }}
                            </button>
                        @endfor

                        <button @if (!$sales->hasMorePages()) disabled @endif wire:click.prevent="nextPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <button wire:click.prevent="gotoPage({{ $sales->lastPage() }})" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-right"></i>
                        </button>
                    </div>

                    <!-- Right: Jump to page input -->
                    <div class="flex items-center gap-2" x-data>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Ir para</label>
                        <input x-ref="pageInput" type="number" min="1" max="{{ $sales->lastPage() }}"
                            class="w-20 text-sm rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1 text-slate-700 dark:text-slate-200"
                            placeholder="#">
                        <button @click.prevent="$wire.call('gotoPage', $refs.pageInput.value)"
                            class="px-3 py-1 rounded-md text-sm bg-indigo-600 text-white">
                            Ir
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if ($showDeleteModal ?? false)
        <div class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" wire:click="cancelDelete"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100"
                x-transition:enter="transition ease-out duration-300 delay-75"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4" wire:click.stop>
                <div
                    class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/30 dark:border-zinc-700/50 overflow-hidden">
                    <!-- Gradiente decorativo no topo -->
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-orange-500 to-pink-600">
                    </div>

                    <!-- Header do modal -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-cart-x text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                Excluir Venda
                            </h3>
                        </div>
                        <button type="button" wire:click="cancelDelete"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100/50 hover:bg-slate-200/70 dark:bg-zinc-700/50 dark:hover:bg-zinc-600/70 rounded-xl text-sm w-10 h-10 flex justify-center items-center transition-all duration-200 backdrop-blur-sm"
                            title="Fechar">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <!-- Conteúdo do modal -->
                    <div class="p-6 text-center">
                        <!-- Ícone central com animação -->
                        <div class="relative mx-auto mb-6">
                            <div
                                class="w-20 h-20 mx-auto bg-gradient-to-br from-red-100 to-orange-100 dark:from-red-900/40 dark:to-orange-900/40 rounded-full flex items-center justify-center shadow-xl ring-4 ring-red-100 dark:ring-red-900/30">
                                <i
                                    class="bi bi-receipt-cutoff text-red-600 dark:text-red-400 text-3xl animate-pulse"></i>
                            </div>
                            <!-- Ícones decorativos orbitando -->
                            <div
                                class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                                <i class="bi bi-exclamation text-white text-sm font-bold"></i>
                            </div>
                            <div
                                class="absolute -bottom-2 -left-2 w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                <i class="bi bi-arrow-return-left text-white text-sm"></i>
                            </div>
                        </div>

                        <!-- Título e descrição -->
                        <h3 class="mb-2 text-2xl font-bold text-slate-900 dark:text-white">
                            Excluir Venda #{{ $deletingSale?->id }}?
                        </h3>
                        <p class="mb-6 text-slate-600 dark:text-slate-400 leading-relaxed">
                            Esta ação não pode ser desfeita. A venda será <span
                                class="font-semibold text-red-600 dark:text-red-400">permanentemente removida</span> e
                            os produtos serão devolvidos ao estoque.
                        </p>

                        <!-- Alertas adicionais -->
                        <div
                            class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-200 dark:border-amber-700/50">
                            <div class="flex items-center justify-center gap-2 text-amber-700 dark:text-amber-400">
                                <i class="bi bi-info-circle text-lg"></i>
                                <span class="text-sm font-medium">O que acontecerá:</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-3 text-xs text-amber-600 dark:text-amber-500">
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <span>Venda removida</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-arrow-return-left"></i>
                                    <span>Produtos ao estoque</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Pagamentos cancelados</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-graph-down"></i>
                                    <span>Métricas atualizadas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex gap-3">
                            <button wire:click="cancelDelete" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-slate-700 dark:text-slate-300 bg-slate-100/70 dark:bg-zinc-700/70 hover:bg-slate-200/80 dark:hover:bg-zinc-600/80 rounded-xl font-semibold transition-all duration-200 backdrop-blur-sm border border-slate-200/50 dark:border-zinc-600/50 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="bi bi-shield-check text-lg"></i>
                                <span>Manter Venda</span>
                            </button>
                            <button wire:click="deleteSale" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 ring-2 ring-red-500/20 focus:ring-4 focus:ring-red-500/40">
                                <i class="bi bi-trash-fill text-lg"></i>
                                <span>Confirmar Exclusão</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
