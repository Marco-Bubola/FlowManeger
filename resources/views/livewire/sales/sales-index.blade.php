@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
@endpush

<div class="w-full min-h-screen app-viewport-fit sales-index-page mobile-393-base relative" x-data="{
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
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-ultrawide.css') }}">
    {{-- Camada compacta comum das telas de venda (sempre por último) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sales-compact.css') }}?v=20260806">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <x-loading-overlay message="Carregando vendas..." />
    <div class="">

        <!-- Header Moderno -->
        <x-page-header title="Vendas" icon="bi-cart3" section="vendas" tabs-section="vendas" tabs-active="lista">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-cart-check"></i>{{ $totalSales ?? 0 }} vendas</span>
                @if(($pendingSales ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-clock"></i>{{ $pendingSales }} pendentes</span>
                @endif
                @if(($todaySales ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-calendar-check"></i>{{ $todaySales }} hoje</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                <button type="button" wire:click="toggleTips" title="Dicas"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-lightbulb text-amber-500"></i>Dicas</button>
                <button type="button" @click="showFilters = !showFilters" title="Filtros avançados"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-sliders text-indigo-500"></i>Filtros</button>
                <a href="{{ route('sales.create', ['scanner' => 1]) }}" title="Criar venda com scanner"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-upc-scan text-indigo-500"></i>Scanner</a>
                <a href="{{ route('sales.create') }}" title="Nova venda"
                   class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition"><i class="bi bi-plus-lg"></i>Nova venda</a>
            </x-slot:actions>
        </x-page-header>

        <x-list-toolbar placeholder="Buscar por cliente ou número da venda...">
            <x-toolbar.group>
                <x-toolbar.chip wire:click="$set('statusFilter', '')" :active="$statusFilter === ''" icon="bi-grid">Todas</x-toolbar.chip>
                <x-toolbar.chip wire:click="$set('statusFilter', 'pendente')" :active="$statusFilter === 'pendente'" icon="bi-clock-history">Pendentes</x-toolbar.chip>
                <x-toolbar.chip wire:click="$set('statusFilter', 'pago')" :active="$statusFilter === 'pago'" icon="bi-check-circle">Pagas</x-toolbar.chip>
            </x-toolbar.group>
            <x-toolbar.group icon="bi-calendar-week">
                <x-toolbar.chip wire:click="setQuickFilter('today')" :active="$quickFilter === 'today'">Hoje</x-toolbar.chip>
                <x-toolbar.chip wire:click="setQuickFilter('week')" :active="$quickFilter === 'week'">Semana</x-toolbar.chip>
                <x-toolbar.chip wire:click="setQuickFilter('month')" :active="$quickFilter === 'month'">Mês</x-toolbar.chip>
            </x-toolbar.group>
            <x-toolbar.group icon="bi-arrow-down-up">
                <x-toolbar.chip wire:click="setSortOrder('created_at', 'desc')" :active="$sortBy === 'created_at' && $sortDirection === 'desc'">Recentes</x-toolbar.chip>
                <x-toolbar.chip wire:click="setSortOrder('created_at', 'asc')" :active="$sortBy === 'created_at' && $sortDirection === 'asc'">Antigas</x-toolbar.chip>
                <x-toolbar.chip wire:click="setSortOrder('total_price', 'desc')" :active="$sortBy === 'total_price' && $sortDirection === 'desc'">Maior valor</x-toolbar.chip>
            </x-toolbar.group>
            <x-toolbar.group title="Vendas por página">
                @foreach(array_slice($perPageOptions, 0, 5) as $pp)
                    <x-toolbar.chip wire:click="$set('perPage', {{ $pp }})" :active="$sales->perPage() == $pp">{{ $pp }}</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
            <x-toolbar.pager :paginator="$sales" />
        </x-list-toolbar>

        <!-- Filtros Avançados -->
        <x-sales-filters :show-filters="false" :clients="$clients ?? collect()" :sellers="$sellers ?? collect()" :status-filter="$statusFilter" :client-filter="$clientFilter"
            :start-date="$startDate" :end-date="$endDate" :min-value="$minValue" :max-value="$maxValue" :payment-method-filter="$paymentMethodFilter"
            :seller-filter="$sellerFilter" :quick-filter="$quickFilter" :sort-by="$sortBy" :sort-direction="$sortDirection" :per-page-options="$perPageOptions"
            :per-page="$perPage" :search="$search" />

        <!-- Pedidos do portal em aberto -->
        @if(isset($portalQuotes) && $portalQuotes->count() > 0)
        @php $po = app(\App\Services\Portal\PortalOrderService::class); @endphp
        <section class="spo mb-5" data-testid="sales-portal-orders">
            <div class="spo-head">
                <span class="spo-icon"><i class="bi bi-bag-heart"></i></span>
                <div class="min-w-0 flex-1">
                    <h3>Pedidos do portal <span class="spo-badge">{{ $portalQuotes->count() }}</span></h3>
                    <p>Clientes que pediram pelo catálogo e esperam sua confirmação</p>
                </div>
                <a href="{{ route('sales.portal-orders') }}" class="spo-all" wire:navigate>{{ $portalQuotes->count() > 4 ? 'Ver todos os ' . $portalQuotes->count() : 'Abrir' }}<i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="spo-row">
                @foreach($portalQuotes->take(4) as $pq)
                    @php
                        $pItems = $po->items($pq, null, $portalProducts);
                        $pTotal = $po->total($pq);
                        $pUnits = array_sum(array_column($pItems, 'quantity'));
                        [$pLabel, $pTone] = match ($pq->status) { 'reviewing' => ['Em análise', 'sky'], 'quoted' => ['Orçamento enviado', 'violet'], default => ['Novo', 'amber'] };
                    @endphp
                    <a href="{{ route('sales.portal-orders', ['pedido' => $pq->id]) }}" class="spo-card" wire:navigate>
                        <div class="flex items-center gap-2.5 min-w-0">
                            <x-client-avatar :name="$pq->client->name ?? '?'" :photo="$pq->client->caminho_foto ?? null" size="w-9 h-9 text-xs" rounded="rounded-xl" />
                            <div class="min-w-0 flex-1">
                                <p class="spo-name truncate">{{ $pq->client?->name ?? 'Cliente' }}</p>
                                <p class="spo-dim truncate">#{{ $pq->id }} · {{ $pq->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="spo-tag is-{{ $pTone }}">{{ $pLabel }}</span>
                        </div>
                        <div class="spo-bottom">
                            <div class="spo-thumbs">
                                @foreach(array_slice($pItems, 0, 4) as $it)
                                    <span>@if($it['image'])<img src="{{ $it['image'] }}" alt="" loading="lazy">@else<i class="bi bi-box-seam"></i>@endif</span>
                                @endforeach
                                @if(count($pItems) > 4)<span class="is-more">+{{ count($pItems) - 4 }}</span>@endif
                            </div>
                            <div class="text-right">
                                <strong>{{ $pTotal > 0 ? $po->money($pTotal) : 'A definir' }}</strong>
                                <p class="spo-dim">{{ $pUnits }} {{ $pUnits === 1 ? 'item' : 'itens' }}@if($pq->payment_preference) · {{ $po->paymentLabel($pq->payment_preference) }}@endif</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <style>
                .spo { border-radius: 22px; border: 1px solid rgb(226 232 240 / .9); background: linear-gradient(135deg, rgb(255 255 255), rgb(245 243 255)); padding: 14px; box-shadow: 0 1px 2px rgb(15 23 42 / .04); }
                .dark .spo { background: linear-gradient(135deg, rgb(15 23 42 / .85), rgb(30 27 75 / .55)); border-color: rgb(51 65 85 / .7); }
                .spo-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
                .spo-icon { width: 40px; height: 40px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; background: linear-gradient(135deg, rgb(99 102 241), rgb(147 51 234)); box-shadow: 0 6px 16px -6px rgb(99 102 241 / .6); flex-shrink: 0; }
                .spo-head h3 { font-weight: 800; font-size: .98rem; color: rgb(15 23 42); display: flex; align-items: center; gap: 6px; }
                .dark .spo-head h3 { color: #fff; }
                .spo-head p { font-size: .74rem; color: rgb(100 116 139); }
                @media (max-width: 639px) { .spo-head p { display: none; } }
                .spo-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: rgb(245 158 11); color: #fff; font-size: .7rem; display: inline-flex; align-items: center; justify-content: center; }
                .spo-all { display: inline-flex; align-items: center; gap: 6px; border-radius: 12px; padding: 8px 12px; font-size: .78rem; font-weight: 700; color: #fff; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); white-space: nowrap; }
                .spo-row { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(260px, 82%); gap: 10px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 2px; }
                @media (min-width: 768px) { .spo-row { grid-auto-flow: row; grid-template-columns: repeat(2, minmax(0, 1fr)); overflow: visible; } }
                @media (min-width: 1280px) { .spo-row { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
                .spo-card { scroll-snap-align: start; display: flex; flex-direction: column; gap: 10px; border-radius: 16px; background: #fff; border: 1px solid rgb(226 232 240); padding: 12px; transition: box-shadow .2s, transform .2s; min-width: 0; }
                .spo-card:hover { box-shadow: 0 10px 24px -14px rgb(79 70 229 / .6); transform: translateY(-1px); }
                .dark .spo-card { background: rgb(15 23 42 / .9); border-color: rgb(51 65 85); }
                .spo-card.is-more { align-items: center; justify-content: center; flex-direction: row; font-weight: 700; font-size: .85rem; color: rgb(79 70 229); border-style: dashed; }
                .spo-name { font-weight: 700; font-size: .86rem; color: rgb(15 23 42); }
                .dark .spo-name { color: #fff; }
                .spo-dim { font-size: .7rem; color: rgb(100 116 139); }
                .spo-tag { border-radius: 999px; padding: 2px 8px; font-size: .66rem; font-weight: 700; white-space: nowrap; background: rgb(255 251 235); color: rgb(180 83 9); }
                .spo-tag.is-sky { background: rgb(240 249 255); color: rgb(3 105 161); }
                .spo-tag.is-violet { background: rgb(245 243 255); color: rgb(109 40 217); }
                .dark .spo-tag { background: rgb(245 158 11 / .14); color: rgb(252 211 77); }
                .dark .spo-tag.is-sky { background: rgb(14 165 233 / .14); color: rgb(125 211 252); }
                .dark .spo-tag.is-violet { background: rgb(139 92 246 / .16); color: rgb(196 181 253); }
                .spo-bottom { display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; }
                .spo-bottom strong { font-size: 1rem; font-weight: 900; color: rgb(15 23 42); white-space: nowrap; }
                .dark .spo-bottom strong { color: #fff; }
                .spo-thumbs { display: flex; }
                .spo-thumbs span { width: 34px; height: 34px; border-radius: 10px; border: 2px solid #fff; background: rgb(248 250 252); margin-right: -8px; overflow: hidden; display: flex; align-items: center; justify-content: center; color: rgb(148 163 184); font-size: .7rem; font-weight: 800; }
                .dark .spo-thumbs span { border-color: rgb(15 23 42); background: rgb(30 41 59); }
                .spo-thumbs img { width: 100%; height: 100%; object-fit: contain; background: #fff; }
                .spo-thumbs .is-more { color: rgb(79 70 229); }
            </style>
        </section>
        @endif

        <!-- Grid de Cards de Vendas -->
        <div class="sales-grid-v2 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 mb-8" x-ref="salesGrid"
            data-ultrawind="{{ $ultraWindClient ?? false ? 'true' : 'false' }}"
            data-full-hd="{{ $fullHdLayout ? 'true' : 'false' }}" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
            x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            @forelse($sales as $sale)
            <x-sale-card :sale="$sale" wire:delete-action="openDeleteModal" />
            @empty
            <div class="col-span-full">
                @php $temVendas = \App\Models\Sale::query()->exists(); @endphp
                <x-empty-state icon="bi-cart" :title="$temVendas ? 'Nenhuma venda com esses filtros' : 'Nenhuma venda ainda'"
                    :text="$temVendas ? 'Tente outra busca ou limpe os filtros.' : 'Registre a primeira venda. Dá para usar o leitor de código de barras.'">
                    <a href="{{ route('sales.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md"><i class="bi bi-plus-lg"></i> Nova venda</a>
                    <a href="{{ route('sales.create', ['scanner' => 1]) }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-500/10"><i class="bi bi-upc-scan"></i> Com leitor</a>
                </x-empty-state>
            </div>
            @endforelse
        </div>

        <x-pagination-bar :paginator="$sales" :per-page-options="$perPageOptions" label="vendas" fit-grid=".sales-grid-v2" />
        <!-- Componente de Exportação de Venda (modal) -->
        @livewire('sales.export-sale-modal')

        <!-- Modal de Confirmação de Exclusão -->
        <x-delete-dialog show="showDeleteModal" confirm="deleteSale" cancel="cancelDelete" title="Excluir venda?" confirm-label="Excluir venda" icon="bi-cart-x">
            @if($deletingSale) A venda <strong>#{{ $deletingSale->id }}</strong> será apagada e os produtos voltam para o estoque. @else A venda será apagada e os produtos voltam para o estoque. @endif Não dá para desfazer.
        </x-delete-dialog>
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