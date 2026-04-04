<x-portal-layout title="Meus Orçamentos">

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

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

    {{-- Hero banner --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-indigo-700 to-sky-800 rounded-2xl p-5 mb-5 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-br from-black/10 to-transparent"></div>
        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[.2em] mb-2">
                    <i class="fas fa-file-invoice text-[9px]"></i> Central de Orçamentos
                </div>
                <h1 class="text-lg font-black leading-tight">Meus Orçamentos</h1>
                <p class="text-sky-200/80 text-xs mt-1">{{ $quotes->total() }} solicitaç{{ $quotes->total() !== 1 ? 'ões' : 'ão' }} registrada{{ $quotes->total() !== 1 ? 's' : '' }}</p>
            </div>
            <a href="{{ route('portal.quotes.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 rounded-xl font-bold text-xs transition-all hover:scale-105 shadow-lg whitespace-nowrap">
                <i class="fas fa-circle-plus text-xs"></i> Novo Orçamento
            </a>
        </div>
    </div>

    {{-- Filters bar --}}
    <div class="portal-card flex flex-wrap items-center gap-2 p-3 mb-5">
        <div class="relative flex-1 min-w-44">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 text-xs"></i>
            <input x-model="search" type="text" placeholder="Buscar orçamento..." class="portal-input pl-8 pr-3 py-2 text-xs w-full">
        </div>
        <select x-model="statusFilter" class="portal-input py-2 text-xs min-w-[8rem]">
            <option value="">Todos</option>
            <option value="pending">Pendente</option>
            <option value="reviewing">Em Análise</option>
            <option value="quoted">Cotado</option>
                <option value="approved">Aprovado</option>
                <option value="rejected">Recusado</option>
            </select>
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
            @php $col = $quote->status_color; @endphp
            <a href="{{ route('portal.quotes.show', $quote) }}"
               class="portal-card block overflow-hidden hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-700 transition-all group"
               x-show="filtered.find(q => q.id === {{ $quote->id }})"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100">
                {{-- Card top gradient strip --}}
                <div class="h-1.5 bg-gradient-to-r from-{{ $col }}-400 to-{{ $col }}-600"></div>
                <div class="p-4">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-invoice text-{{ $col }}-600 dark:text-{{ $col }}-400 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-gray-900 dark:text-slate-200">Orçamento #{{ $quote->id }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $quote->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300 flex-shrink-0">{{ $quote->status_label }}</span>
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
                        <p class="text-sm font-black text-gray-900 dark:text-slate-200">R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}</p>
                        @else
                        <p class="text-xs text-gray-400 dark:text-slate-500 italic">Aguardando cotação</p>
                        @endif
                        <span class="text-[10px] text-sky-600 dark:text-sky-400 font-bold group-hover:underline flex items-center gap-1">
                            Ver detalhes <i class="fas fa-chevron-right text-[8px] group-hover:translate-x-0.5 transition-transform"></i>
                        </span>
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
