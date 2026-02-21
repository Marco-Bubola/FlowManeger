@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
@endpush

<div class="w-full sales-index-page" x-data="{
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
            :max-value="$maxValue">
            <x-slot name="breadcrumb">
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">
                        <i class="fas fa-shopping-cart mr-1"></i>Vendas
                    </span>
                </div>
            </x-slot>
        </x-sales-index-header>

        <!-- Filtros Avançados -->
        <x-sales-filters :show-filters="false" :clients="$clients ?? collect()" :sellers="$sellers ?? collect()" :status-filter="$statusFilter" :client-filter="$clientFilter"
            :start-date="$startDate" :end-date="$endDate" :min-value="$minValue" :max-value="$maxValue" :payment-method-filter="$paymentMethodFilter"
            :seller-filter="$sellerFilter" :quick-filter="$quickFilter" :sort-by="$sortBy" :sort-direction="$sortDirection" :per-page-options="$perPageOptions" />

        <!-- Grid de Cards de Vendas -->
        <div class="sales-grid gap-4 mb-8" x-ref="salesGrid"
            data-ultrawind="{{ $ultraWindClient ?? false ? 'true' : 'false' }}"
            data-full-hd="{{ $fullHdLayout ? 'true' : 'false' }}" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
            x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            @forelse($sales as $sale)
            <x-sale-card :sale="$sale" wire:delete-action="openDeleteModal" />
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
        <div class="mt-8 sales-pagination-section">
            <div
                class="sales-pagination-shell flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-r from-white via-slate-50 to-blue-50 dark:from-slate-800 dark:via-slate-700 dark:to-blue-900 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-600/50 p-4 md:p-6 backdrop-blur-xl">

                <!-- Left: Per page selector & summary -->
                <div class="sales-pagination-left flex items-center gap-4">
                    <div class="sales-per-page flex items-center gap-2">
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
                <div class="sales-pagination-center flex items-center gap-2">
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
                <div class="sales-pagination-right flex items-center gap-2" x-data>
                    <label class="text-sm text-slate-600 dark:text-slate-300">Ir para</label>
                    <input x-ref="pageInput" type="number" min="1" max="{{ $sales->lastPage() }}"
                        inputmode="numeric"
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
        <!-- Componente de Exportação de Venda (modal) -->
        @livewire('sales.export-sale-modal')

        <!-- Modal de Confirmação de Exclusão -->
        <div class="fixed inset-0 z-[9999] overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md sales-delete-modal-overlay"
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            wire:click="cancelDelete"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100 sales-delete-modal-panel"
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
    </div>
    <!-- Modal de Dicas (Wizard) -->
    @if($showTipsModal)
    <div x-data="{
            currentStep: 1,
            totalSteps: 5,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }" x-show="$wire.showTipsModal" x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sales-tips-modal-overlay"
        style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

        <!-- Modal Content -->
        <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
            class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50 sales-tips-modal-panel"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Header with Progress Bar -->
            <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-700 px-8 py-6 text-white">
                <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>

                <div class="pr-12">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                            <i class="bi bi-lightbulb-fill text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold">Dicas de Vendas</h2>
                            <p class="text-indigo-100 text-sm mt-1">Aprenda a gerenciar suas vendas com eficiência</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex gap-2 mt-6">
                        <template x-for="step in totalSteps" :key="step">
                            <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                <div class="h-full bg-white rounded-full transition-all duration-500"
                                    :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8 sales-tips-modal-content">
                <!-- Step 1: Visão Geral -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-cart-check text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Visão Geral de Vendas</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Conheça as principais funcionalidades do módulo de vendas</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-500 rounded-xl">
                                    <i class="bi bi-search text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Busca Rápida</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Encontre vendas por cliente, valor, data ou status</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-purple-500 rounded-xl">
                                    <i class="bi bi-funnel text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Filtros Avançados</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Filtre por status, período, cliente e forma de pagamento</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-500 rounded-xl">
                                    <i class="bi bi-card-list text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Cards Informativos</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Visualize todas as informações importantes de cada venda</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-orange-500 rounded-xl">
                                    <i class="bi bi-bar-chart text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Estatísticas</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Acompanhe total de vendas, pendências e faturamento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Status de Vendas -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-flag text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Status de Vendas</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Entenda os diferentes status e suas cores</p>
                    </div>

                    <div class="space-y-4">
                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-green-300 dark:border-green-600">
                            <div class="flex items-center gap-4 mb-3">
                                <span class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-xl">
                                    <i class="bi bi-check-circle-fill text-2xl text-white"></i>
                                </span>
                                <div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Concluída</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Venda finalizada e paga</p>
                                </div>
                                <span class="ml-auto px-4 py-2 bg-green-500 text-white rounded-lg font-bold">Concluída</span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">A venda foi processada com sucesso e o pagamento foi recebido. Não requer mais ações.</p>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-yellow-300 dark:border-yellow-600">
                            <div class="flex items-center gap-4 mb-3">
                                <span class="flex items-center justify-center w-12 h-12 bg-yellow-500 rounded-xl">
                                    <i class="bi bi-clock-fill text-2xl text-white"></i>
                                </span>
                                <div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Pendente</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Aguardando pagamento ou conclusão</p>
                                </div>
                                <span class="ml-auto px-4 py-2 bg-yellow-500 text-white rounded-lg font-bold">Pendente</span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">A venda foi registrada mas ainda não foi finalizada. Requer acompanhamento para confirmar o pagamento.</p>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-red-300 dark:border-red-600">
                            <div class="flex items-center gap-4 mb-3">
                                <span class="flex items-center justify-center w-12 h-12 bg-red-500 rounded-xl">
                                    <i class="bi bi-x-circle-fill text-2xl text-white"></i>
                                </span>
                                <div>
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white">Cancelada</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Venda cancelada ou rejeitada</p>
                                </div>
                                <span class="ml-auto px-4 py-2 bg-red-500 text-white rounded-lg font-bold">Cancelada</span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">A venda foi cancelada por algum motivo. Produtos voltam ao estoque automaticamente.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Informações dos Cards -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-card-text text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Informações dos Cards</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">O que você encontra em cada card de venda</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-info-circle text-blue-500"></i>
                                Dados Principais
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-person text-blue-500"></i>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Cliente</p>
                                        <p class="font-semibold text-slate-800 dark:text-white">Nome do cliente</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-calendar text-purple-500"></i>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Data</p>
                                        <p class="font-semibold text-slate-800 dark:text-white">Data da venda</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-cash text-green-500"></i>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Valor Total</p>
                                        <p class="font-semibold text-green-600">R$ 999,00</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-box text-orange-500"></i>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Produtos</p>
                                        <p class="font-semibold text-slate-800 dark:text-white">Quantidade</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-credit-card text-purple-500"></i>
                                Formas de Pagamento
                            </h4>
                            <p class="text-slate-700 dark:text-slate-300 mb-3">Cada venda exibe os métodos de pagamento utilizados:</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1.5 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-lg text-sm font-semibold">
                                    <i class="bi bi-credit-card mr-1"></i>Cartão
                                </span>
                                <span class="px-3 py-1.5 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-lg text-sm font-semibold">
                                    <i class="bi bi-cash mr-1"></i>Dinheiro
                                </span>
                                <span class="px-3 py-1.5 bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-lg text-sm font-semibold">
                                    <i class="bi bi-phone mr-1"></i>PIX
                                </span>
                                <span class="px-3 py-1.5 bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300 rounded-lg text-sm font-semibold">
                                    <i class="bi bi-calendar-range mr-1"></i>Parcelado
                                </span>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-mouse text-2xl text-amber-500"></i>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Clique no Card</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300">Clique em qualquer card de venda para ver todos os detalhes, produtos vendidos, pagamentos e histórico completo!</p>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Ações Disponíveis -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-lightning-charge text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Ações Disponíveis</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">O que você pode fazer com as vendas</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-500 rounded-xl">
                                    <i class="bi bi-plus-circle text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Nova Venda</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Registre uma nova venda no sistema</p>
                                    <button class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                                        <i class="bi bi-plus-circle mr-1"></i>Nova Venda
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-500 rounded-xl">
                                    <i class="bi bi-eye text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Visualizar Detalhes</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Veja produtos, pagamentos e histórico completo da venda</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-purple-500 rounded-xl">
                                    <i class="bi bi-pencil text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Editar Venda</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Altere produtos, quantidades, pagamentos ou status</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-orange-500 rounded-xl">
                                    <i class="bi bi-file-pdf text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Exportar PDF</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Gere relatórios e notas fiscais em PDF</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-red-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-red-200/50 dark:border-slate-500/50 md:col-span-2">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-red-500 rounded-xl">
                                    <i class="bi bi-trash text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Excluir Venda</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Remove a venda do sistema e devolve produtos ao estoque</p>
                                    <div class="mt-2 p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                                        <p class="text-xs text-red-700 dark:text-red-300">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Ação irreversível! Use com cautela.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Dicas Finais -->
                <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-star text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Dicas Especiais</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Maximize sua produtividade com estas dicas</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="flex items-center justify-center w-10 h-10 bg-blue-500 text-white rounded-xl font-bold text-lg">1</span>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Use os Filtros</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-3">Combine múltiplos filtros para encontrar exatamente o que procura:</p>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-blue-500"></i>
                                    Filtre vendas por período específico
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-blue-500"></i>
                                    Separe vendas por cliente ou vendedor
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-blue-500"></i>
                                    Use a busca rápida para encontrar por nome
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="flex items-center justify-center w-10 h-10 bg-purple-500 text-white rounded-xl font-bold text-lg">2</span>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Acompanhe Status</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-3">Mantenha o controle das suas vendas:</p>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-purple-500"></i>
                                    Verifique vendas pendentes diariamente
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-purple-500"></i>
                                    Atualize o status assim que receber pagamento
                                </li>
                                <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-purple-500"></i>
                                    Use as estatísticas do cabeçalho para visão geral
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="flex items-center justify-center w-10 h-10 bg-green-500 text-white rounded-xl font-bold text-lg">3</span>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Atalhos Rápidos</h4>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Nova Venda</p>
                                    <p class="font-semibold text-green-600">Botão no cabeçalho</p>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Ver Detalhes</p>
                                    <p class="font-semibold text-blue-600">Clique no card</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-amber-50 via-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-amber-300 dark:border-amber-600">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-emoji-smile-fill text-3xl text-amber-500"></i>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Pronto para Vender!</h4>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">Agora você domina o módulo de vendas</p>
                                </div>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300">Continue explorando as funcionalidades e otimize seu processo de vendas. Sempre que precisar, volte aqui clicando no botão <i class="bi bi-lightbulb text-amber-500 mx-1"></i> de Dicas!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with Navigation -->
            <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200 dark:border-slate-700 sales-tips-modal-footer">
                <div class="flex items-center justify-between">
                    <!-- Previous Button -->
                    <button @click="prevStep()" x-show="currentStep > 1"
                        class="flex items-center gap-2 px-6 py-3 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all duration-200 hover:scale-105">
                        <i class="bi bi-arrow-left"></i>
                        Anterior
                    </button>
                    <div x-show="currentStep === 1"></div>

                    <!-- Step Indicators -->
                    <div class="flex items-center gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step"
                                class="transition-all duration-300 rounded-full"
                                :class="currentStep === step ? 'w-8 h-3 bg-gradient-to-r from-indigo-600 to-purple-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'">
                            </button>
                        </template>
                    </div>

                    <!-- Next/Finish Button -->
                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'"></span>
                        <i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
 </div>