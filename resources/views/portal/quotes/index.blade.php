<x-portal-layout title="Meus Orçamentos">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-quotes-index.css') }}">
@endpush

@php
    $quotedCount = $quotes->where('status', 'quoted')->count();
    $editableCount = $quotes->filter(fn($q) => $q->can_edit)->count();
@endphp

<div x-data="{
    search: '',
    statusFilter: '',
    quotes: {{ Js::from($quotes->map(function($q){ return ['id'=>$q->id,'status'=>$q->status,'label'=>$q->status_label,'color'=>$q->status_color,'date'=>$q->created_at->format('d/m/Y'),'total'=>$q->quoted_total]; })) }},
    get filtered() {
        return this.quotes.filter(q => {
            const m = !this.search || ('#'+q.id).includes(this.search) || q.date.includes(this.search) || q.label.toLowerCase().includes(this.search.toLowerCase());
            const s = !this.statusFilter || q.status === this.statusFilter;
            return m && s;
        });
    }
}">

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon">
                <i class="fas fa-file-lines"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney text-[8px]"></i> Início</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Meus Pedidos</span>
                </div>
                <h1 class="pph-title">Meus Orçamentos</h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge">
                    <i class="fas fa-file-invoice text-[8px]"></i>
                    {{ $quotes->total() }} solicitaç{{ $quotes->total() !== 1 ? 'ões' : 'ão' }}
                </span>
                @if($quotedCount > 0)
                <span class="pph-badge warning">
                    <i class="fas fa-bell text-[8px]"></i>
                    {{ $quotedCount }} aguardando resposta
                </span>
                @endif
                @if($editableCount > 0)
                <span class="pph-badge info">
                    <i class="fas fa-pen-to-square text-[8px]"></i>
                    {{ $editableCount }} editável
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
            <button @click="statusFilter='pending'"   class="pph-pill" :class="{ 'active': statusFilter === 'pending'   }">Pendente</button>
            <button @click="statusFilter='reviewing'" class="pph-pill" :class="{ 'active': statusFilter === 'reviewing' }">Em Análise</button>
            <button @click="statusFilter='quoted'"    class="pph-pill" :class="{ 'active': statusFilter === 'quoted'    }">Cotado</button>
            <button @click="statusFilter='approved'"  class="pph-pill" :class="{ 'active': statusFilter === 'approved'  }">Aprovado</button>
            <button @click="statusFilter='rejected'"  class="pph-pill" :class="{ 'active': statusFilter === 'rejected'  }">Recusado</button>
        </div>
    </div>

    {{-- Search bar --}}
    <div class="portal-card flex flex-wrap items-center gap-2 p-3 mb-4">
        <div class="relative flex-1 min-w-44">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 text-xs"></i>
            <input x-model="search" type="text" placeholder="Buscar por #ID, data, status..." class="portal-input pl-8 pr-3 py-2 text-xs w-full">
        </div>
    </div>

    @if($quotes->isEmpty())
        <div class="portal-card p-14 text-center">
            <div class="w-14 h-14 bg-violet-100 dark:bg-violet-900/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-file-invoice text-violet-400 dark:text-violet-500 text-xl"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900 dark:text-slate-200 mb-1">Nenhum orçamento ainda</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Solicite um orçamento para nossos produtos.</p>
            <a href="{{ route('portal.quotes.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-colors shadow">
                <i class="fas fa-circle-plus text-xs"></i> Solicitar Orçamento
            </a>
        </div>
    @else
        {{-- No results --}}
        <div x-cloak x-show="filtered.length === 0" class="portal-card p-10 text-center">
            <i class="fas fa-magnifying-glass text-2xl text-gray-200 dark:text-slate-600 mb-2"></i>
            <p class="text-xs text-gray-400 dark:text-slate-500">Nenhum orçamento encontrado.</p>
            <button @click="search=''; statusFilter=''" class="mt-2 text-xs text-sky-600 dark:text-sky-400 font-semibold hover:underline">Limpar filtros</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($quotes as $quote)
            @php $col = $quote->status_color; $needsAction = $quote->status === 'quoted'; @endphp
            <a href="{{ route('portal.quotes.show', $quote) }}"
               class="portal-card block overflow-hidden hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-700 transition-all group {{ $needsAction ? 'ring-2 ring-purple-400 dark:ring-purple-600' : '' }}"
               x-show="filtered.find(q => q.id === {{ $quote->id }})"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100">
                {{-- Card top gradient strip --}}
                <div class="h-1.5 bg-gradient-to-r from-{{ $col }}-400 to-{{ $col }}-600 {{ $needsAction ? 'h-2' : '' }}"></div>
                <div class="p-4">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0 {{ $needsAction ? 'ring-2 ring-purple-300 dark:ring-purple-700' : '' }}">
                                <i class="fas fa-file-invoice text-{{ $col }}-600 dark:text-{{ $col }}-400 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-gray-900 dark:text-slate-200">Orçamento #{{ $quote->id }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $quote->created_at->format('d/m/Y') }} · {{ $quote->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">{{ $quote->status_label }}</span>
                            @if($needsAction)
                            <span class="inline-flex items-center gap-1 text-[9px] font-black text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/40 px-2 py-0.5 rounded-full animate-pulse">
                                <i class="fas fa-bell text-[8px]"></i> Ação necessária
                            </span>
                            @endif
                            @if($quote->can_edit)
                            <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-amber-600 dark:text-amber-400">
                                <i class="fas fa-pen-to-square text-[8px]"></i> Editável
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Items do orçamento --}}
                    @if($quote->items && count($quote->items))
                    <div class="space-y-1 mb-3">
                        @foreach(array_slice($quote->items, 0, 3) as $item)
                        <div class="flex items-center gap-2 bg-sky-50/70 dark:bg-sky-900/10 rounded-lg px-2.5 py-1.5 border border-sky-100 dark:border-sky-800/30">
                            <div class="w-5 h-5 rounded-md bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-sky-500 dark:text-sky-400 text-[8px]"></i>
                            </div>
                            <span class="flex-1 text-[10px] font-semibold text-gray-700 dark:text-slate-300 truncate">{{ $item['name'] ?? 'Produto' }}</span>
                            <span class="text-[9px] font-black text-sky-600 dark:text-sky-400 flex-shrink-0 bg-sky-100 dark:bg-sky-900/40 px-1.5 py-0.5 rounded-md">{{ $item['quantity'] ?? 1 }}×</span>
                            @if(!empty($item['price_ref']) && $item['price_ref'] > 0)
                            <span class="text-[9px] font-bold text-gray-500 dark:text-slate-400 flex-shrink-0">R$ {{ number_format($item['price_ref'] * ($item['quantity'] ?? 1), 2, ',', '.') }}</span>
                            @endif
                        </div>
                        @endforeach
                        @if(count($quote->items) > 3)
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 text-center pt-0.5">
                            <i class="fas fa-ellipsis text-[8px] mr-1"></i>+ {{ count($quote->items) - 3 }} produto(s)
                        </p>
                        @endif
                    </div>
                    @endif

                    {{-- Footer --}}
                    <div class="flex items-center justify-between">
                        @if($quote->quoted_total)
                        <div>
                            <p class="text-[9px] text-gray-400 dark:text-slate-500">Valor cotado</p>
                            <p class="text-sm font-black text-gray-900 dark:text-slate-200">R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}</p>
                        </div>
                        @else
                        <p class="text-xs text-gray-400 dark:text-slate-500 italic">Aguardando cotação</p>
                        @endif
                        @if($needsAction)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-500 text-white text-[10px] font-black rounded-xl shadow group-hover:shadow-purple-400/30 group-hover:scale-105 transition-all">
                            <i class="fas fa-tag text-[9px]"></i> Ver proposta
                        </span>
                        @else
                        <span class="text-[10px] text-sky-600 dark:text-sky-400 font-bold group-hover:underline flex items-center gap-1">
                            Ver detalhes <i class="fas fa-chevron-right text-[8px] group-hover:translate-x-0.5 transition-transform"></i>
                        </span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $quotes->links() }}
        </div>
    @endif
</div>

</x-portal-layout>
