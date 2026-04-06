<x-portal-layout title="Minhas Compras">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-mobile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-iphone15.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-ipad-portrait.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-ipad-landscape.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-notebook.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-sales-ultrawide.css') }}">
@endpush

<div class="portal-sales-page"
     x-data="{
    search: '',
    statusFilter: '',
    sales: {{ Js::from($sales->map(function($s) {
        return [
            'id'         => $s->id,
            'status'     => $s->status,
            'total'      => (float) $s->total_price,
            'paid'       => (float) $s->total_paid,
            'remaining'  => (float) $s->remaining_amount,
            'date'       => $s->created_at->format('d/m/Y'),
            'dateHuman'  => $s->created_at->diffForHumans(),
            'items'      => $s->saleItems->count(),
            'payment'    => $s->payment_method,
        ];
    })) }},
    get filtered() {
        return this.sales.filter(s => {
            const q = this.search.toLowerCase().trim();
            const matchQ = !q || ('#'+s.id).includes(q) || s.date.includes(q);
            const matchS = !this.statusFilter || s.status === this.statusFilter;
            return matchQ && matchS;
        });
    },
    isVisible(id) { return !!this.filtered.find(s => s.id === id); }
}" x-cloak>

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon">
                <i class="fas fa-bag-shopping"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney text-[8px]"></i> Início</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Minhas Compras</span>
                </div>
                <h1 class="pph-title">Histórico de Compras</h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge">
                    <i class="fas fa-bag-shopping text-[8px]"></i>
                    {{ $kpiTotal }} pedido{{ $kpiTotal !== 1 ? 's' : '' }}
                </span>
                @if(isset($kpiPending) && $kpiPending > 0)
                <span class="pph-badge warning">
                    <i class="fas fa-clock text-[8px]"></i>
                    {{ $kpiPending }} pendente{{ $kpiPending !== 1 ? 's' : '' }}
                </span>
                @endif
            </div>
            <a href="{{ route('portal.quotes.create') }}" class="pph-btn">
                <i class="fas fa-circle-plus text-xs"></i>
                Novo Orçamento
            </a>
        </div>

        <div class="pph-row2">
            <button @click="statusFilter=''" class="pph-pill" :class="{ 'active': statusFilter === '' }">Todos</button>
            <button @click="statusFilter='pendente'"   class="pph-pill" :class="{ 'active': statusFilter === 'pendente'   }">Pendente</button>
            <button @click="statusFilter='confirmada'" class="pph-pill" :class="{ 'active': statusFilter === 'confirmada' }">Confirmado</button>
            <button @click="statusFilter='concluida'"  class="pph-pill" :class="{ 'active': statusFilter === 'concluida'  }">Concluído</button>
            <button @click="statusFilter='orcamento'"  class="pph-pill" :class="{ 'active': statusFilter === 'orcamento'  }">Em Orçamento</button>
            <button @click="statusFilter='cancelada'"  class="pph-pill" :class="{ 'active': statusFilter === 'cancelada'  }">Cancelado</button>
        </div>
    </div>

    {{-- ── KPI Grid ── --}}
    <div class="portal-sales-kpi-grid grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="kpi-tile">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1">Total de Pedidos</p>
                    <p class="kpi-value text-2xl font-black text-gray-900 dark:text-slate-100 leading-none">{{ $kpiTotal }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">pedido{{ $kpiTotal !== 1 ? 's' : '' }}</p>
                </div>
                <div class="w-9 h-9 bg-sky-100 dark:bg-sky-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bag-shopping text-sky-600 dark:text-sky-400 text-sm"></i>
                </div>
            </div>
        </div>
        <div class="kpi-tile">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1">Valor Total</p>
                    <p class="kpi-value text-xl font-black text-gray-900 dark:text-slate-100 leading-none">R$ {{ number_format($kpiTotalValue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">acumulado</p>
                </div>
                <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-dollar-sign text-indigo-600 dark:text-indigo-400 text-sm"></i>
                </div>
            </div>
        </div>
        <div class="kpi-tile">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1">Total Pago</p>
                    <p class="kpi-value text-xl font-black leading-none" style="color:#10b981">R$ {{ number_format($kpiPaid, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">confirmado</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(16,185,129,0.12)">
                    <i class="fas fa-circle-check text-sm" style="color:#10b981"></i>
                </div>
            </div>
        </div>
        <div class="kpi-tile">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-1">Aguardando</p>
                    <p class="kpi-value text-2xl font-black text-gray-900 dark:text-slate-100 leading-none">{{ $kpiPending }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">{{ $kpiPending === 1 ? 'pendente' : 'pendentes' }}</p>
                </div>
                <div class="w-9 h-9 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 text-sm"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="portal-card portal-sales-filter-bar flex flex-wrap items-center gap-2.5 p-3 mb-5 shadow-sm">
        <div class="search-wrap relative flex-1 min-w-44">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 text-xs pointer-events-none"></i>
            <input x-model="search" type="text" placeholder="Buscar por nº do pedido ou data..."
                   class="portal-input pl-8 pr-3 py-2 text-xs">
        </div>
        <select x-model="statusFilter" class="portal-input py-2 text-xs" style="min-width:10rem">
            <option value="">Todos os status</option>
            <option value="pendente">Pendente</option>
            <option value="confirmada">Confirmado</option>
            <option value="concluida">Concluído</option>
            <option value="orcamento">Em Orçamento</option>
            <option value="cancelada">Cancelado</option>
        </select>
        <div class="text-[10px] text-gray-400 dark:text-slate-500 font-semibold ml-auto whitespace-nowrap hidden sm:block">
            <span x-text="filtered.length"></span> resultado(s)
        </div>
    </div>

    {{-- ── Empty state (initial) ── --}}
    @if($sales->isEmpty())
        <div class="portal-sales-empty portal-card p-16 text-center">
            <div class="w-16 h-16 bg-sky-100 dark:bg-sky-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bag-shopping text-sky-400 dark:text-sky-500 text-2xl"></i>
            </div>
            <h3 class="font-black text-base text-gray-900 dark:text-slate-200 mb-1">Nenhuma compra ainda</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-5">Quando suas compras forem registradas, aparecerão aqui.</p>
            <a href="{{ route('portal.quotes.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm text-white shadow transition-all hover:scale-105"
               style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                <i class="fas fa-circle-plus text-xs"></i> Solicitar Orçamento
            </a>
        </div>
    @else

        {{-- No results (live search) --}}
        <div x-cloak x-show="filtered.length === 0" class="portal-card p-10 text-center">
            <i class="fas fa-magnifying-glass text-2xl text-gray-200 dark:text-slate-600 mb-3 block"></i>
            <p class="text-sm text-gray-500 dark:text-slate-400">Nenhum pedido encontrado.</p>
            <button @click="search=''; statusFilter=''" class="mt-2 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">
                Limpar filtros
            </button>
        </div>

        {{-- ── Cards Grid ── --}}
        <div class="portal-sales-grid grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($sales as $sale)
            @php
                $statusCfg = [
                    'pendente'   => ['Pendente',    'amber',   'hourglass-half',    'linear-gradient(90deg,#fbbf24,#f59e0b)'],
                    'orcamento'  => ['Em Orçamento','violet',  'file-invoice',       'linear-gradient(90deg,#a855f7,#8b5cf6)'],
                    'confirmada' => ['Confirmado',  'sky',     'thumbs-up',          'linear-gradient(90deg,#38bdf8,#0ea5e9)'],
                    'concluida'  => ['Concluído',   'emerald', 'circle-check',       'linear-gradient(90deg,#10b981,#059669)'],
                    'cancelada'  => ['Cancelado',   'red',     'circle-xmark',       'linear-gradient(90deg,#f87171,#ef4444)'],
                ];
                [$lbl, $col, $ico, $stripGrad] = $statusCfg[$sale->status] ?? [$sale->status, 'gray', 'circle', 'linear-gradient(90deg,#9ca3af,#6b7280)'];
                $paidPct   = $sale->total_price > 0 ? min(100, round(($sale->total_paid / $sale->total_price) * 100)) : 0;
                $remaining = $sale->remaining_amount;
            @endphp

            <div class="portal-sale-card"
                 x-show="isVisible({{ $sale->id }})"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Colored top strip (changes per status) --}}
                <div class="portal-sale-card-strip" style="background: {{ $stripGrad }}"></div>

                {{-- Card Header --}}
                <div class="portal-sale-card-header">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                             style="background: {{ $stripGrad }}">
                            <i class="fas fa-{{ $ico }} text-white text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="sale-id-text text-sm font-black text-gray-900 dark:text-slate-100">Pedido #{{ $sale->id }}</p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5 flex items-center gap-1">
                                <i class="far fa-calendar text-[9px]"></i>
                                {{ $sale->created_at->format('d/m/Y') }}
                                <span class="text-gray-300 dark:text-slate-600">·</span>
                                {{ $sale->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">
                            {{ $lbl }}
                        </span>
                        <span class="text-base font-black text-gray-900 dark:text-slate-100">
                            R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Items Section --}}
                <div class="portal-sale-card-items">
                    @forelse($sale->saleItems->take(3) as $item)
                    <div class="portal-sale-item-row">
                        {{-- Thumbnail --}}
                        <div class="portal-sale-item-thumb">
                            @if($item->product && ($item->product->image_url || $item->product->image))
                                <img src="{{ $item->product->image_url ?? asset('storage/products/'.$item->product->image) }}"
                                     alt="{{ $item->product->name }}" loading="lazy">
                            @else
                                <i class="fas fa-box text-sky-400 dark:text-sky-600 text-sm"></i>
                            @endif
                        </div>
                        {{-- Product details --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-800 dark:text-slate-200 truncate">
                                {{ $item->product->name ?? 'Produto removido' }}
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500">
                                @if($item->product?->category)
                                    <span class="inline-flex items-center gap-0.5">
                                        @if($item->product->category->icone)
                                            <i class="{{ $item->product->category->icone }} text-[9px]"></i>
                                        @endif
                                        {{ $item->product->category->name }} ·
                                    </span>
                                @endif
                                {{ $item->quantity }}× · R$ {{ number_format($item->price_sale, 2, ',', '.') }}/un
                            </p>
                        </div>
                        {{-- Item total --}}
                        <span class="text-xs font-black text-gray-900 dark:text-slate-200 flex-shrink-0">
                            R$ {{ number_format($item->price_sale * $item->quantity, 2, ',', '.') }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 dark:text-slate-500 text-center py-2">Sem itens registrados.</p>
                    @endforelse

                    @if($sale->saleItems->count() > 3)
                    <p class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 text-center mt-1.5">
                        <i class="fas fa-ellipsis text-[8px]"></i>
                        e mais {{ $sale->saleItems->count() - 3 }} item(s)
                    </p>
                    @endif
                </div>

                {{-- Financial Footer --}}
                <div class="portal-sale-card-footer">
                    {{-- Payment progress bar --}}
                    @if($sale->total_price > 0)
                    <div class="portal-pay-bar-wrap mb-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-slate-500">Progresso do pagamento</span>
                            <span class="text-[9px] font-black" style="color:#10b981">{{ $paidPct }}%</span>
                        </div>
                        <div class="portal-pay-progress">
                            <div class="portal-pay-progress-fill" style="width: {{ $paidPct }}%"></div>
                        </div>
                    </div>
                    @endif

                    {{-- Financial chips row --}}
                    <div class="fin-chip-group flex items-center gap-3 flex-wrap">
                        <div class="portal-fin-chip">
                            <span class="portal-fin-chip-label text-gray-400 dark:text-slate-500">Total</span>
                            <span class="portal-fin-chip-value text-gray-900 dark:text-slate-100">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
                        </div>
                        <div class="fin-divider"></div>
                        <div class="portal-fin-chip">
                            <span class="portal-fin-chip-label" style="color:#10b981">Pago</span>
                            <span class="portal-fin-chip-value" style="color:#10b981">R$ {{ number_format($sale->total_paid, 2, ',', '.') }}</span>
                        </div>
                        @if($remaining > 0.01)
                        <div class="fin-divider"></div>
                        <div class="portal-fin-chip">
                            <span class="portal-fin-chip-label text-amber-500">Saldo</span>
                            <span class="portal-fin-chip-value text-amber-600 dark:text-amber-400">R$ {{ number_format($remaining, 2, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="ml-auto flex items-center gap-1.5 flex-shrink-0 flex-wrap justify-end">
                            @if($sale->payment_method)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold border"
                                  style="background:rgba(99,102,241,0.08);border-color:rgba(99,102,241,0.2);color:#6366f1">
                                <i class="fas fa-credit-card text-[8px]"></i>
                                {{ ucfirst(str_replace(['_','-'], ' ', $sale->payment_method)) }}
                            </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold border"
                                  style="background:rgba(14,165,233,0.08);border-color:rgba(14,165,233,0.2);color:#0ea5e9">
                                <i class="fas fa-box text-[8px]"></i>
                                {{ $sale->saleItems->count() }} {{ Str::plural('item', $sale->saleItems->count()) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($sales->hasPages())
        <div class="mt-5 flex justify-center">
            {{ $sales->links() }}
        </div>
        @endif
    @endif

</div>

</x-portal-layout>