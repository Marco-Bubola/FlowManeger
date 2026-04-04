<x-portal-layout title="Minhas Compras">

@push('styles')
<style>
[x-cloak] { display:none !important; }
</style>
@endpush

<div x-data="{
    search: '',
    statusFilter: '',
    sales: {{ Js::from($sales->map(function($s){ return ['id'=>$s->id,'status'=>$s->status,'total'=>$s->total_price,'paid'=>$s->amount_paid,'date'=>$s->created_at->format('d/m/Y'),'dateSort'=>$s->created_at->timestamp,'items'=>$s->saleItems->count(),'payment'=>$s->payment_method]; })) }},
    get filtered() {
        return this.sales.filter(s => {
            const q = this.search.toLowerCase();
            const matchQ = !q || ('#'+s.id).includes(q) || s.date.includes(q) || s.status.includes(q);
            const matchS = !this.statusFilter || s.status === this.statusFilter;
            return matchQ && matchS;
        });
    }
}">

    {{-- Hero banner --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-indigo-700 to-violet-800 rounded-2xl p-5 mb-5 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-br from-black/10 to-transparent"></div>
        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[.2em] mb-2">
                    <i class="fas fa-bag-shopping text-[9px]"></i> Histórico de Compras
                </div>
                <h1 class="text-lg font-black leading-tight">Minhas Compras</h1>
                <p class="text-sky-200/80 text-xs mt-1">{{ $sales->total() }} pedido{{ $sales->total() !== 1 ? 's' : '' }} registrado{{ $sales->total() !== 1 ? 's' : '' }}</p>
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
            <input x-model="search" type="text" placeholder="Buscar pedido..." class="portal-input pl-8 pr-3 py-2 text-xs w-full">
        </div>
        <select x-model="statusFilter" class="portal-input py-2 text-xs min-w-[9rem]">
            <option value="">Todos os status</option>
            <option value="pending">Pendente</option>
            <option value="paid">Pago</option>
            <option value="partial">Parcial</option>
            <option value="cancelled">Cancelado</option>
        </select>
    </div>

    @if($sales->isEmpty())
        <div class="portal-card p-14 text-center">
            <div class="w-14 h-14 bg-sky-100 dark:bg-sky-900/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-bag-shopping text-sky-400 dark:text-sky-500 text-xl"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900 dark:text-slate-200 mb-1">Nenhuma compra ainda</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">Quando suas compras forem registradas, aparecerão aqui.</p>
        </div>
    @else

        {{-- No results (live search) --}}
        <div x-cloak x-show="filtered.length === 0" class="portal-card p-10 text-center">
            <i class="fas fa-magnifying-glass text-2xl text-gray-200 dark:text-slate-600 mb-2"></i>
            <p class="text-xs text-gray-400 dark:text-slate-500">Nenhum pedido encontrado para os filtros selecionados.</p>
            <button @click="search=''; statusFilter=''" class="mt-2 text-xs text-sky-600 dark:text-sky-400 font-semibold hover:underline">Limpar filtros</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($sales as $sale)
            @php
                $statusMap = [
                    'pending'   => ['Pendente',   'amber',   'hourglass-half'],
                    'paid'      => ['Pago',        'emerald', 'circle-check'],
                    'cancelled' => ['Cancelado',   'red',     'circle-xmark'],
                    'partial'   => ['Parcial',     'blue',    'circle-half-stroke'],
                ];
                [$lbl, $col, $ico] = $statusMap[$sale->status] ?? [$sale->status, 'gray', 'circle'];
                $remaining = $sale->total_price - ($sale->amount_paid ?? 0);
            @endphp
            <div class="portal-card overflow-hidden"
                 x-show="filtered.find(s => s.id === {{ $sale->id }})"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 dark:border-slate-700/60 bg-gray-50/50 dark:bg-slate-700/20">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-{{ $ico }} text-{{ $col }}-600 dark:text-{{ $col }}-400 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black text-gray-900 dark:text-slate-200">Pedido #{{ $sale->id }}</p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $sale->created_at->format('d \d\e M \d\e Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">{{ $lbl }}</span>
                        <span class="text-sm font-black text-gray-900 dark:text-slate-200">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Items com thumbnail de produto --}}
                <div class="px-4 py-3 space-y-1.5">
                    @foreach($sale->saleItems->take(3) as $item)
                    <div class="flex items-center gap-2.5 bg-gray-50 dark:bg-slate-700/30 rounded-xl px-3 py-2">
                        {{-- Thumbnail --}}
                        <div class="w-9 h-9 rounded-xl overflow-hidden flex-shrink-0 bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800/30">
                            @if($item->product && $item->product->image_url)
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-box text-sky-300 dark:text-sky-700 text-xs"></i>
                                </div>
                            @endif
                        </div>
                        {{-- Nome + unidade --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold text-gray-800 dark:text-slate-200 truncate">{{ $item->product->name ?? 'Produto removido' }}</p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500">
                                {{ $item->quantity }}× · R$ {{ number_format($item->unit_price, 2, ',', '.') }} un.
                            </p>
                        </div>
                        {{-- Total do item --}}
                        <span class="text-[11px] font-black text-gray-900 dark:text-slate-200 flex-shrink-0">
                            R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                    @if($sale->saleItems->count() > 3)
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 text-center pt-0.5">
                        <i class="fas fa-ellipsis text-[8px] mr-1"></i>+ {{ $sale->saleItems->count() - 3 }} item(s) adicional(is)
                    </p>
                    @endif
                </div>

                {{-- Footer summary --}}
                <div class="flex flex-wrap items-center gap-3 px-4 py-2.5 bg-gray-50/50 dark:bg-slate-700/20 border-t border-gray-50 dark:border-slate-700/60">
                    <div class="flex items-center gap-1.5 text-[10px]">
                        <i class="fas fa-circle-check text-emerald-500 text-xs"></i>
                        <span class="text-gray-500 dark:text-slate-400">Pago:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}</span>
                    </div>
                    @if($remaining > 0.01)
                    <div class="flex items-center gap-1.5 text-[10px]">
                        <i class="fas fa-hourglass-half text-amber-500 text-xs"></i>
                        <span class="text-gray-500 dark:text-slate-400">Saldo:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">R$ {{ number_format($remaining, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($sale->payment_method)
                    <div class="flex items-center gap-1.5 text-[10px]">
                        <i class="fas fa-credit-card text-gray-400 dark:text-slate-500 text-xs"></i>
                        <span class="text-gray-500 dark:text-slate-400">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-1.5 text-[10px] ml-auto">
                        <i class="fas fa-box text-sky-400 text-xs"></i>
                        <span class="text-gray-500 dark:text-slate-400">{{ $sale->saleItems->count() }} {{ Str::plural('item', $sale->saleItems->count()) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $sales->links() }}
        </div>
    @endif
</div>

</x-portal-layout>
