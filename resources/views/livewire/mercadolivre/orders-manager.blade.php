@push('styles')
<style>
/* ==================== ORDERS MANAGER – ML AMBER ==================== */
.orders-card {
    transition: transform .18s ease, box-shadow .18s ease;
}
.orders-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(217,119,6,.18);
}
.orders-status-badge {
    font-size: .68rem;
    letter-spacing: .04em;
}
/* skeleton */
.skel { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: skel-anim 1.4s infinite; }
@keyframes skel-anim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
/* scrollbar */
.orders-items-scroll::-webkit-scrollbar { width: 4px; }
.orders-items-scroll::-webkit-scrollbar-thumb { background: #f59e0b44; border-radius: 4px; }
</style>
@endpush

<div class="orders-manager-page min-h-screen relative"
     x-data="{
         showFiltersModal: @entangle('showFiltersModal'),
         tipsOpen: false,
         viewMode: @entangle('viewMode'),
         openFiltersModal() { this.showFiltersModal = true; },
         closeFiltersModal() { this.showFiltersModal = false; }
     }">
    <x-loading-overlay message="Carregando pedidos..." />
    {{-- Cabeçalho, contadores, busca e status no padrão das outras listas --}}
    @php
        $s = $this->getStats();
        $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
        $statusPills = [
            '' => ['Todos', 'bi-grid', $s['total']],
            'paid' => ['Pagos', 'bi-check-circle', $s['paid']],
            'payment_in_process' => ['Em processo', 'bi-clock', $s['processing']],
            'cancelled' => ['Cancelados', 'bi-x-circle', $s['cancelled']],
        ];
    @endphp
    <div class="px-4 sm:px-6 lg:px-8">
        <x-page-header title="Pedidos" icon="bi-bag-check" section="mercadolivre" tabs-section="mercadolivre" tabs-active="pedidos"
                       subtitle="Gerencie, filtre e importe seus pedidos do Mercado Livre">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-bag"></i>{{ $s['total'] }} pedido{{ $s['total'] == 1 ? '' : 's' }}</span>
                @if($dateFrom || $dateTo)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><i class="bi bi-calendar3"></i>{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m') : '...' }} a {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m') : 'hoje' }}</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                <button type="button" @click="openFiltersModal()" title="Filtros" class="{{ $phBtn }} relative">
                    <i class="bi bi-sliders text-amber-500"></i><span class="hidden sm:inline">Filtros</span>
                    @if($statusFilter)
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white dark:ring-slate-900"></span>
                    @endif
                </button>
                <button type="button" @click="tipsOpen = true" title="Dicas" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-lightbulb text-amber-500"></i><span class="hidden sm:inline">Dicas</span></button>
                <button type="button" wire:click="loadOrders" wire:loading.attr="disabled" title="Atualizar pedidos"
                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap">
                    <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadOrders"></i><span class="hidden sm:inline">Atualizar</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Contadores --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            @foreach([
                ['Total pedidos', $s['total'], 'bi-bag-fill', 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400', 'text-slate-800 dark:text-white'],
                ['Pagos', $s['paid'], 'bi-check-circle-fill', 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400', 'text-emerald-700 dark:text-emerald-400'],
                ['Em processamento', $s['processing'], 'bi-clock-fill', 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400', 'text-blue-700 dark:text-blue-400'],
                ['Cancelados', $s['cancelled'], 'bi-x-circle-fill', 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400', 'text-red-700 dark:text-red-400'],
            ] as [$label, $value, $icon, $iconCls, $valCls])
                <div class="bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-700/70 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-sm min-w-0">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $iconCls }}"><i class="bi {{ $icon }} text-base"></i></div>
                    <div class="min-w-0">
                        <p class="text-2xl font-black leading-none {{ $valCls }}">{{ $value }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $label }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <x-list-toolbar model="searchTerm" placeholder="Buscar por pedido ou comprador...">
            <x-toolbar.group icon="bi-flag">
                @foreach($statusPills as $key => [$label, $icon, $count])
                    <x-toolbar.chip wire:click="$set('statusFilter', '{{ $key }}')" :active="$statusFilter === $key" :icon="$icon">{{ $label }} ({{ $count }})</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
            <x-toolbar.group>
                <x-toolbar.chip wire:click="$set('viewMode','cards')" :active="$viewMode === 'cards'" icon="bi-grid-3x3-gap" title="Cards" />
                <x-toolbar.chip wire:click="$set('viewMode','table')" :active="$viewMode === 'table'" icon="bi-table" title="Tabela" />
            </x-toolbar.group>
            @if($statusFilter || $searchTerm)
                <x-toolbar.group>
                    <x-toolbar.chip wire:click="clearFilters" icon="bi-x-lg">Limpar</x-toolbar.chip>
                </x-toolbar.group>
            @endif
        </x-list-toolbar>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 pb-8">

        {{-- ============================================================
             LOADING SKELETONS
        ============================================================ --}}
        @if($loading)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @for($i = 0; $i < 8; $i++)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 space-y-3 overflow-hidden">
                        <div class="flex justify-between">
                            <div class="skel h-4 w-24 rounded-lg"></div>
                            <div class="skel h-5 w-16 rounded-full"></div>
                        </div>
                        <div class="skel h-3 w-32 rounded-lg"></div>
                        <div class="skel h-16 w-full rounded-xl"></div>
                        <div class="skel h-3 w-20 rounded-lg"></div>
                        <div class="flex gap-2">
                            <div class="skel h-8 flex-1 rounded-lg"></div>
                            <div class="skel h-8 w-8 rounded-lg"></div>
                        </div>
                    </div>
                @endfor
            </div>

        {{-- ============================================================
             CARDS VIEW
        ============================================================ --}}
        @elseif($viewMode === 'cards')
            @if(count($orders) === 0)
                <x-empty-state icon="bi-bag-x" tone="amber" title="Nenhum pedido encontrado"
                               text="Ajuste os filtros ou aguarde novos pedidos chegarem do Mercado Livre. Os pedidos são atualizados automaticamente.">
                    @if($statusFilter || $searchTerm)
                        <button type="button" wire:click="clearFilters"
                                class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition">
                            <i class="bi bi-eraser"></i> Limpar filtros
                        </button>
                    @endif
                    <button type="button" wire:click="loadOrders" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 transition">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                    </button>
                </x-empty-state>

            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($orders as $order)
                        @php
                            $statusInfo = $this->getStatusBadge($order['status'] ?? '');
                            $items      = $order['order_items'] ?? [];
                            $buyer      = $order['buyer'] ?? [];
                            $shipping   = $order['shipping'] ?? null;
                            $payments   = $order['payments'] ?? [];
                            $payType    = $payments[0]['payment_type'] ?? null;
                            $shipSt     = $shipping['status'] ?? null;
                            $orderId    = $order['id'] ?? '—';
                            $scol = match($statusInfo['color']){
                                'emerald','green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                'blue'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'red'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                default  => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                            };
                        @endphp
                        <div class="orders-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800
                                    shadow-sm overflow-hidden flex flex-col">

                            {{-- Card header --}}
                            <div class="flex items-center justify-between px-4 pt-4 pb-2.5">
                                <div>
                                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">#{{ $orderId }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                        {{ $this->formatDate($order['date_created'] ?? now()) }}
                                    </p>
                                </div>
                                <span class="orders-status-badge {{ $scol }} px-2.5 py-1 rounded-full font-bold uppercase">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </div>

                            {{-- Comprador --}}
                            <div class="px-4 pb-3 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500
                                            flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-black text-[10px]">
                                        {{ mb_strtoupper(mb_substr($buyer['nickname'] ?? 'U', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 dark:text-white truncate leading-tight">
                                        {{ $buyer['nickname'] ?? 'Comprador' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 leading-tight">
                                        ID {{ $buyer['id'] ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-100 dark:border-slate-800 mx-4"></div>

                            {{-- Itens --}}
                            <div class="px-4 py-3 flex-1 space-y-2 orders-items-scroll overflow-y-auto max-h-36">
                                @foreach(array_slice($items, 0, 3) as $item)
                                    @php $it = $item['item'] ?? []; @endphp
                                    <div class="flex items-center gap-2.5">
                                        @if(!empty($it['thumbnail']))
                                            <img src="{{ $it['thumbnail'] }}" alt="{{ $it['title'] ?? '' }}"
                                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-slate-100 dark:border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800
                                                        flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-box text-slate-400 text-sm"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate leading-snug">
                                                {{ $it['title'] ?? 'Produto' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 leading-snug">
                                                {{ $item['quantity'] ?? 1 }}x &bull; {{ $this->formatPrice($item['unit_price'] ?? 0) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                                @if(count($items) > 3)
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold text-right">
                                        +{{ count($items) - 3 }} item(s) a mais
                                    </p>
                                @endif
                            </div>

                            <div class="border-t border-dashed border-slate-100 dark:border-slate-800 mx-4"></div>

                            {{-- Total + badges --}}
                            <div class="px-4 pt-2.5 pb-1 flex items-end justify-between">
                                <div class="space-y-0.5">
                                    @if($shipSt)
                                        @php
                                            $sc = match($shipSt){ 'delivered'=>'text-emerald-600','shipped','handling'=>'text-blue-500','ready_to_ship'=>'text-purple-500',default=>'text-slate-400' };
                                            $sl = match($shipSt){ 'delivered'=>'Entregue','shipped'=>'Enviado','handling'=>'Em manuseio','ready_to_ship'=>'Pronto p/ envio','pending'=>'Aguard. envio',default=>$shipSt };
                                        @endphp
                                        <span class="text-[10px] font-semibold {{ $sc }}">
                                            <i class="bi bi-truck-fill mr-0.5"></i>{{ $sl }}
                                        </span>
                                    @endif
                                    @if($payType)
                                        @php
                                            $pi = match($payType){'credit_card'=>'bi-credit-card-fill','debit_card'=>'bi-credit-card-2-back-fill','account_money'=>'bi-wallet2','ticket'=>'bi-upc-scan',default=>'bi-cash-coin'};
                                            $pl = match($payType){'credit_card'=>'Crédito','debit_card'=>'Débito','account_money'=>'Conta ML','ticket'=>'Boleto',default=>$payType};
                                        @endphp
                                        <span class="block text-[10px] text-slate-400"><i class="bi {{ $pi }} mr-0.5"></i>{{ $pl }}</span>
                                    @endif
                                </div>
                                <p class="text-lg font-black text-emerald-700 dark:text-emerald-400 leading-none">
                                    {{ $this->formatPrice($order['total_amount'] ?? 0) }}
                                </p>
                            </div>

                            {{-- Ações --}}
                            <div class="px-4 pt-2 pb-4 flex gap-2">
                                <button wire:click="viewOrderDetails('{{ $orderId }}')"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl
                                               bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40
                                               text-amber-700 dark:text-amber-400 text-xs font-bold
                                               hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all">
                                    <i class="bi bi-eye-fill"></i> Detalhes
                                </button>
                                <button wire:click="importOrder('{{ $orderId }}')"
                                        class="inline-flex items-center justify-center px-3 py-2 rounded-xl
                                               bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40
                                               text-emerald-700 dark:text-emerald-400 text-xs font-bold
                                               hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all"
                                        title="Importar pedido">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ============================================================
             TABLE VIEW
        ============================================================ --}}
        @else
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pedido</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Comprador</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Data</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($orders as $order)
                                @php
                                    $si = $this->getStatusBadge($order['status'] ?? '');
                                    $tc = match($si['color']){
                                        'emerald','green'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'blue'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'yellow'=>'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'red'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        default=>'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                    };
                                @endphp
                                <tr class="hover:bg-amber-50/30 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-slate-900 dark:text-white">#{{ $order['id'] ?? '—' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ count($order['order_items'] ?? []) }} item(s)</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-semibold text-slate-800 dark:text-white">{{ $order['buyer']['nickname'] ?? '—' }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 hidden sm:table-cell text-slate-500 dark:text-slate-400 text-xs">
                                        {{ $this->formatDate($order['date_created'] ?? now()) }}
                                    </td>
                                    <td class="px-5 py-3.5 font-black text-emerald-700 dark:text-emerald-400">
                                        {{ $this->formatPrice($order['total_amount'] ?? 0) }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="orders-status-badge {{ $tc }} px-2.5 py-1 rounded-full font-bold uppercase">
                                            {{ $si['text'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button wire:click="viewOrderDetails('{{ $order['id'] }}')"
                                                    class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 transition-all">
                                                <i class="bi bi-eye-fill text-sm"></i>
                                            </button>
                                            <button wire:click="importOrder('{{ $order['id'] }}')"
                                                    class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition-all">
                                                <i class="bi bi-download text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4">
                                        <x-empty-state icon="bi-inbox" tone="amber" title="Nenhum pedido encontrado" text="Ajuste os filtros para ver mais resultados." class="border-0" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(($paging['total'] ?? 0) > count($orders) || $mlPage > 1)
            <div class="mt-6 flex items-center justify-center gap-2 text-sm">
                <button wire:click="goToMlPage({{ $mlPage - 1 }})" @disabled($mlPage <= 1)
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 disabled:opacity-40">
                    <i class="bi bi-chevron-left"></i> Anterior
                </button>
                <span class="px-3 text-slate-600 dark:text-slate-300">Página {{ $mlPage }} de {{ $this->mlLastPage }} · {{ $paging['total'] ?? 0 }} pedidos</span>
                <button wire:click="goToMlPage({{ $mlPage + 1 }})" @disabled($mlPage >= $this->mlLastPage)
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 disabled:opacity-40">
                    Próxima <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        @endif

    </div>
    {{-- /max-w --}}

    {{-- ============================================================
         FILTROS
    ============================================================ --}}
    <x-filter-sheet show="showFiltersModal" title="Filtros de pedidos" subtitle="Status, período e quantidade" :active="$statusFilter !== '' || $searchTerm !== ''" target="statusFilter,dateFrom,dateTo,perPage,clearFilters">
        <div class="fm-fs-group fm-fs-wide">
            <h4><i class="bi bi-flag text-amber-500"></i>Status</h4>
            <div class="fm-fs-options">
                @foreach([
                    '' => 'Todos', 'confirmed' => 'Confirmado', 'payment_required' => 'Aguardando pagamento',
                    'payment_in_process' => 'Pagamento em processo', 'paid' => 'Pago', 'fulfilled' => 'Entregue', 'cancelled' => 'Cancelado',
                ] as $key => $label)
                    <button type="button" wire:click="$set('statusFilter', '{{ $key }}')" class="fm-fs-opt {{ $statusFilter === $key ? 'is-on' : '' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="fm-fs-group">
            <h4><i class="bi bi-calendar3 text-sky-500"></i>Período</h4>
            <div class="grid grid-cols-2 gap-2">
                <label class="block min-w-0"><span class="block text-xs font-semibold text-slate-500 mb-1">De</span><input type="date" wire:model.live="dateFrom"></label>
                <label class="block min-w-0"><span class="block text-xs font-semibold text-slate-500 mb-1">Até</span><input type="date" wire:model.live="dateTo"></label>
            </div>
        </div>
        <div class="fm-fs-group">
            <h4><i class="bi bi-list-ol text-indigo-500"></i>Pedidos por página</h4>
            <div class="fm-fs-options">
                @foreach([10, 20, 50] as $n)
                    <button type="button" wire:click="$set('perPage', {{ $n }})" class="fm-fs-opt {{ (int) $perPage === $n ? 'is-on' : '' }}">{{ $n }} pedidos</button>
                @endforeach
            </div>
        </div>
        <div class="fm-fs-group sm:hidden">
            <h4><i class="bi bi-three-dots text-slate-500"></i>Mais</h4>
            <div class="fm-fs-options">
                <button type="button" @click="showFiltersModal = false; tipsOpen = true" class="fm-fs-opt"><i class="bi bi-lightbulb"></i>Dicas</button>
            </div>
        </div>
    </x-filter-sheet>

    {{-- ============================================================
         MODAL – DICAS  (z-[9999])
    ============================================================ --}}
    <div x-show="tipsOpen"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="tipsOpen = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
         style="display:none">
        <div x-show="tipsOpen"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh]
                    border border-amber-100 dark:border-amber-700/30 overflow-hidden flex flex-col"
             style="display:none">

            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-yellow-400 to-amber-500">
                <div class="flex items-center gap-3">
                    <i class="bi bi-lightbulb-fill text-white text-xl"></i>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Dicas de Pedidos ML</h3>
                        <p class="text-yellow-100 text-xs">Use ao máximo a plataforma</p>
                    </div>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php
                    $tips = [
                        ['icon'=>'bi-search','color'=>'amber','title'=>'Busca rápida','desc'=>'Pesquise pelo ID do pedido ou apelido do comprador para localizar rapidamente.'],
                        ['icon'=>'bi-funnel-fill','color'=>'blue','title'=>'Filtros combinados','desc'=>'Combine status + data para extrair relatórios precisos de um período.'],
                        ['icon'=>'bi-download','color'=>'emerald','title'=>'Importe pedidos','desc'=>'Importe pedidos do ML para o FlowManager para controle completo de estoque e notas.'],
                        ['icon'=>'bi-truck-fill','color'=>'purple','title'=>'Rastreie envios','desc'=>'O status de envio é atualizado via API do ML. Clique em detalhes para ver o rastreio completo.'],
                        ['icon'=>'bi-clock-fill','color'=>'yellow','title'=>'Prazos de pagamento','desc'=>'Pedidos "Aguardando pagamento" têm prazo de 3 dias. Após isso são cancelados automaticamente.'],
                        ['icon'=>'bi-arrow-clockwise','color'=>'orange','title'=>'Atualizações','desc'=>'Atualize manualmente ou configure o webhook ML para receber pedidos em tempo real via push.'],
                        ['icon'=>'bi-grid-3x3-gap-fill','color'=>'sky','title'=>'Modos de visualização','desc'=>'Alterne entre cards e tabela usando os botões no canto direito da listagem.'],
                    ];
                @endphp
                @foreach($tips as $tip)
                    <div class="flex gap-3 p-3.5 rounded-xl bg-{{ $tip['color'] }}-50 dark:bg-{{ $tip['color'] }}-900/10
                                border border-{{ $tip['color'] }}-100 dark:border-{{ $tip['color'] }}-800/30">
                        <div class="w-8 h-8 rounded-lg bg-{{ $tip['color'] }}-100 dark:bg-{{ $tip['color'] }}-900/30
                                    flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="bi {{ $tip['icon'] }} text-{{ $tip['color'] }}-600 dark:text-{{ $tip['color'] }}-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white mb-0.5">{{ $tip['title'] }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $tip['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL – DETALHES DO PEDIDO
    ============================================================ --}}
    @if($showDetailsModal && $selectedOrder)
        @php
            $so      = $selectedOrder;
            $soItems = $so['order_items'] ?? [];
            $soShip  = $so['shipping'] ?? [];
            $soPays  = $so['payments'] ?? [];
            $soBuyer = $so['buyer'] ?? [];
            $soSt    = $this->getStatusBadge($so['status'] ?? '');
        @endphp
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9998] flex items-center justify-center p-4"
             wire:click="closeDetailsModal">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh]
                        border border-amber-100 dark:border-amber-800/30 overflow-hidden flex flex-col"
                 wire:click.stop>

                <div class="flex-shrink-0 flex items-center justify-between px-6 py-4
                            bg-gradient-to-r from-amber-500 to-orange-500">
                    <div>
                        <p class="text-xs text-amber-100 font-medium mb-0.5">Pedido Mercado Livre</p>
                        <h3 class="text-lg font-extrabold text-white">#{{ $so['id'] }}</h3>
                        <p class="text-xs text-amber-100 mt-0.5">{{ $this->formatDate($so['date_created'] ?? now()) }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1.5 rounded-full bg-white/25 text-white text-xs font-extrabold uppercase">
                            {{ $soSt['text'] }}
                        </span>
                        <button wire:click="closeDetailsModal"
                                class="w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-5">

                    {{-- Comprador --}}
                    <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-4 border border-amber-100 dark:border-amber-800/30">
                        <h4 class="text-xs font-extrabold text-amber-700 dark:text-amber-400 uppercase mb-3">
                            <i class="bi bi-person-fill mr-1"></i> Comprador
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase mb-0.5">Apelido</p>
                                <p class="font-bold text-slate-800 dark:text-white">{{ $soBuyer['nickname'] ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase mb-0.5">ID ML</p>
                                <p class="font-mono text-slate-700 dark:text-slate-300">{{ $soBuyer['id'] ?? '—' }}</p>
                            </div>
                            @if(!empty($soBuyer['first_name']))
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase mb-0.5">Nome</p>
                                <p class="font-semibold text-slate-800 dark:text-white">
                                    {{ $soBuyer['first_name'] }} {{ $soBuyer['last_name'] ?? '' }}
                                </p>
                            </div>
                            @endif
                            @if(!empty($soBuyer['email']))
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase mb-0.5">E-mail</p>
                                <p class="text-slate-700 dark:text-slate-300 truncate">{{ $soBuyer['email'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Itens --}}
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-500 uppercase mb-3">
                            <i class="bi bi-box-seam-fill text-amber-500 mr-1"></i> Itens ({{ count($soItems) }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($soItems as $item)
                                @php $it = $item['item'] ?? []; @endphp
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50
                                            border border-slate-100 dark:border-slate-700">
                                    @if(!empty($it['thumbnail']))
                                        <img src="{{ $it['thumbnail'] }}" alt="{{ $it['title'] ?? '' }}"
                                             class="w-14 h-14 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700">
                                    @else
                                        <div class="w-14 h-14 rounded-xl bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-box text-slate-400 text-xl"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white line-clamp-2">
                                            {{ $it['title'] ?? 'Produto' }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1 flex-wrap">
                                            <span class="text-xs text-slate-500">Qtd: <strong>{{ $item['quantity'] ?? 1 }}</strong></span>
                                            <span class="text-xs text-slate-500">Unit: <strong>{{ $this->formatPrice($item['unit_price'] ?? 0) }}</strong></span>
                                            @if(!empty($item['variation_id']))
                                                <span class="text-xs text-purple-500">Variação: {{ $item['variation_id'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="font-black text-emerald-700 dark:text-emerald-400 text-sm flex-shrink-0">
                                        {{ $this->formatPrice(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1)) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pagamento + Envio --}}
                    @if(!empty($soPays) || !empty($soShip))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if(!empty($soPays))
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                            <h4 class="text-xs font-extrabold text-slate-500 uppercase mb-2">
                                <i class="bi bi-credit-card-fill text-blue-500 mr-1"></i> Pagamento
                            </h4>
                            @php $pay = $soPays[0]; @endphp
                            <p class="text-sm font-bold text-slate-800 dark:text-white">
                                {{ match($pay['payment_type'] ?? ''){
                                    'credit_card'=>'Cartão de Crédito',
                                    'debit_card'=>'Cartão de Débito',
                                    'account_money'=>'Conta Mercado Pago',
                                    'ticket'=>'Boleto Bancário',
                                    default=>$pay['payment_type'] ?? '—'
                                } }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 capitalize">{{ $pay['status'] ?? '—' }}</p>
                            @if(!empty($pay['transaction_amount']))
                                <p class="text-xs text-emerald-600 mt-0.5 font-semibold">
                                    {{ $this->formatPrice($pay['transaction_amount']) }}
                                </p>
                            @endif
                        </div>
                        @endif
                        @if(!empty($soShip))
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                            <h4 class="text-xs font-extrabold text-slate-500 uppercase mb-2">
                                <i class="bi bi-truck-fill text-purple-500 mr-1"></i> Envio
                            </h4>
                            <p class="text-sm font-bold text-slate-800 dark:text-white capitalize">
                                {{ $soShip['status'] ?? '—' }}
                            </p>
                            @if(!empty($soShip['id']))
                                <p class="text-xs text-slate-500 mt-1 font-mono">ID: {{ $soShip['id'] }}</p>
                            @endif
                            @if(!empty($soShip['receiver_address']['city']['name']))
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <i class="bi bi-geo-alt-fill mr-0.5"></i>
                                    {{ $soShip['receiver_address']['city']['name'] }},
                                    {{ $soShip['receiver_address']['state']['name'] ?? '' }}
                                </p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Total --}}
                    <div class="flex items-center justify-between p-4 rounded-xl
                                bg-gradient-to-r from-emerald-50 to-green-50
                                dark:from-emerald-900/20 dark:to-green-900/20
                                border border-emerald-200 dark:border-emerald-700/40">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Total do Pedido</span>
                        <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400">
                            {{ $this->formatPrice($so['total_amount'] ?? 0) }}
                        </span>
                    </div>
                </div>

                <div class="flex-shrink-0 flex gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="importOrder('{{ $so['id'] }}')"
                            class="flex-1 px-5 py-2.5 rounded-xl font-extrabold text-sm text-white
                                   bg-gradient-to-r from-emerald-500 to-green-600
                                   hover:shadow-lg hover:shadow-emerald-400/30 transition-all
                                   flex items-center justify-center gap-2">
                        <i class="bi bi-download"></i> Importar para o Sistema
                    </button>
                    <button wire:click="closeDetailsModal"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800
                                   text-slate-700 dark:text-slate-300 font-bold text-sm
                                   hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-toast-notifications />
</div>
