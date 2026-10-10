{{-- ============================================================
     SHOPEE — PEDIDOS
     ============================================================ --}}
@php
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $sp = fn ($d) => $d ? $d->copy()->setTimezone(\App\Livewire\Shopee\OrdersList::TZ) : null;
    $badgeClass = fn ($color) => match ($color) {
        'green', 'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'yellow', 'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    };
    $hasFilters = $statusFilter !== '' || $search !== '' || $period !== '30';
    $pills = [
        '' => ['Todos', 'bi-grid-fill', $stats['all']],
        'a_pagar' => ['A pagar', 'bi-hourglass-split', $stats['a_pagar']],
        'a_enviar' => ['A enviar', 'bi-box-seam-fill', $stats['a_enviar']],
        'enviado' => ['Enviado', 'bi-truck', $stats['enviado']],
        'concluido' => ['Concluído', 'bi-check-circle-fill', $stats['concluido']],
        'cancelado' => ['Cancelado', 'bi-x-circle-fill', $stats['cancelado']],
    ];
    $inputCls = 'w-full px-3 py-2 text-sm rounded-xl bg-white/90 dark:bg-slate-800 border border-orange-200/80 dark:border-slate-600 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 text-slate-800 dark:text-white placeholder-slate-400 transition-all';
@endphp

<div class="shopee-orders-page min-h-screen relative pb-8 px-4 sm:px-6 lg:px-8 pt-4">

    {{-- ─────────────── HEADER ─────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/85 via-orange-50/90 to-red-50/70
                dark:from-slate-800/90 dark:via-orange-900/10 dark:to-slate-800/30
                backdrop-blur-xl border border-orange-100/70 dark:border-orange-900/30
                rounded-3xl shadow-xl mb-5 p-4 sm:p-6">
        <div class="absolute top-0 right-0 w-52 h-52 bg-gradient-to-br from-orange-400/20 via-red-300/15 to-amber-300/10 rounded-full translate-x-20 -translate-y-20 pointer-events-none"></div>
        <div class="relative w-full">

            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <a href="{{ route('dashboard') }}" class="hover:text-orange-600 dark:hover:text-orange-400 flex items-center gap-1" wire:navigate>
                    <i class="bi bi-house-fill text-[11px]"></i> Início
                </a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <a href="{{ route('shopee.publications') }}" class="hover:text-orange-600 dark:hover:text-orange-400" wire:navigate>Shopee</a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <span class="text-orange-700 dark:text-orange-400 font-semibold">Pedidos</span>
            </nav>

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 lg:gap-6">
                <div class="flex items-start gap-4">
                    <div class="relative flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl shadow-xl shadow-orange-500/30 flex-shrink-0"
                         style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                        <i class="bi bi-bag-check-fill text-white text-2xl sm:text-3xl"></i>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-4xl font-bold bg-gradient-to-r from-slate-800 via-orange-700 to-red-600 dark:from-orange-200 dark:via-orange-300 dark:to-amber-300 bg-clip-text text-transparent leading-tight">
                            Pedidos Shopee
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Pedidos recebidos da loja, estoque baixado e importação como venda
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap w-full lg:w-auto">
                    <a href="{{ route('shopee.publications') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/80 dark:bg-slate-800 border border-orange-200 dark:border-slate-600 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-shop"></i> Publicações
                    </a>
                    <button wire:click="syncOrders" wire:loading.attr="disabled" wire:target="syncOrders"
                            @disabled(!$connected)
                            title="{{ $connected ? 'Busca na Shopee os pedidos dos últimos 15 dias' : 'Conecte a Shopee para sincronizar' }}"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold shadow hover:shadow-md hover:opacity-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                        <span wire:loading.remove wire:target="syncOrders" class="whitespace-nowrap"><i class="bi bi-arrow-repeat"></i> Sincronizar<span class="hidden sm:inline"> pedidos</span></span>
                        <span wire:loading wire:target="syncOrders"><i class="bi bi-arrow-repeat animate-spin inline-block"></i> Sincronizando…</span>
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
                <div class="bg-white/70 dark:bg-slate-800/80 border border-orange-100 dark:border-slate-700 rounded-xl px-3 sm:px-4 py-3 flex items-center gap-2.5 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-bag-fill text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-none">{{ $stats['all'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pedidos no período</p>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-slate-800/80 border border-emerald-100 dark:border-slate-700 rounded-xl px-3 sm:px-4 py-3 flex items-center gap-2.5 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-cash-coin text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[15px] sm:text-xl font-black text-emerald-700 dark:text-emerald-400 leading-none whitespace-nowrap">{{ $money($stats['revenue']) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Vendido (pagos)</p>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-slate-800/80 border border-red-100 dark:border-slate-700 rounded-xl px-3 sm:px-4 py-3 flex items-center gap-2.5 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-percent text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[15px] sm:text-xl font-black text-red-600 dark:text-red-400 leading-none whitespace-nowrap">{{ $money($stats['fees']) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tarifas Shopee</p>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-slate-800/80 border border-blue-100 dark:border-slate-700 rounded-xl px-3 sm:px-4 py-3 flex items-center gap-2.5 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-box-seam-fill text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl sm:text-2xl font-black text-blue-700 dark:text-blue-400 leading-none">{{ $stats['a_enviar'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">A enviar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full">

        @unless($connected)
            <div class="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/15 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                <i class="bi bi-plug-fill mt-0.5"></i>
                <p>Loja Shopee não conectada: a lista mostra os pedidos já gravados.
                    <a href="{{ route('shopee.settings') }}" class="font-semibold underline" wire:navigate>Conectar</a></p>
            </div>
        @endunless

        {{-- ─────────────── FILTROS ─────────────── --}}
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 sm:p-4 shadow-sm mb-5 space-y-3">
            <div class="flex flex-col md:flex-row gap-2">
                <div class="relative flex-1">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="search" wire:model.live.debounce.400ms="search"
                           placeholder="Nº do pedido, comprador ou produto…"
                           class="{{ $inputCls }} pl-8">
                </div>
                <div class="grid grid-cols-2 md:flex gap-2">
                    <select wire:model.live="period" class="{{ $inputCls }} md:w-44" aria-label="Período">
                        <option value="7">Últimos 7 dias</option>
                        <option value="15">Últimos 15 dias</option>
                        <option value="30">Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="all">Todo o período</option>
                        <option value="custom">Personalizado</option>
                    </select>
                    <select wire:model.live="statusFilter" class="{{ $inputCls }} md:w-40" aria-label="Status">
                        <option value="">Todos os status</option>
                        @foreach(\App\Livewire\Shopee\OrdersList::GROUP_LABELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($period === 'custom')
                <div class="grid grid-cols-2 gap-2 md:max-w-md">
                    <label class="text-xs text-slate-500 dark:text-slate-400">De
                        <input type="date" wire:model.live="dateFrom" class="{{ $inputCls }} mt-1">
                    </label>
                    <label class="text-xs text-slate-500 dark:text-slate-400">Até
                        <input type="date" wire:model.live="dateTo" class="{{ $inputCls }} mt-1">
                    </label>
                </div>
            @endif

            {{-- Pills de status --}}
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 [scrollbar-width:none]">
                @foreach($pills as $key => [$label, $icon, $count])
                    @php $on = $statusFilter === $key; @endphp
                    <button wire:click="setStatus('{{ $key }}')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-bold border transition-all
                                   {{ $on ? 'text-white border-transparent shadow' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-orange-400' }}"
                            @if($on) style="background: linear-gradient(135deg,#EE4D2D,#FF6633)" @endif>
                        <i class="bi {{ $icon }} mr-1"></i>{{ $label }}
                        <span class="ml-1 opacity-75">({{ $count }})</span>
                    </button>
                @endforeach
                @if($hasFilters)
                    <button wire:click="clearFilters"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-100">
                        <i class="bi bi-x-lg mr-1"></i>Limpar
                    </button>
                @endif
            </div>
        </div>

        {{-- ─────────────── LISTA ─────────────── --}}
        <div wire:loading.class="opacity-60" wire:target="search,statusFilter,period,dateFrom,dateTo,setStatus,clearFilters,gotoPage,nextPage,previousPage" class="transition-opacity">
        @if($orders->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 sm:py-20 text-center">
                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-orange-400/20 to-red-300/10 dark:from-orange-900/30 dark:to-red-900/10 border border-orange-200/60 dark:border-orange-700/30 flex items-center justify-center shadow-lg mb-5">
                    <i class="bi bi-bag-x-fill text-5xl text-orange-400"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-white mb-2">Nenhum pedido encontrado</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-sm">
                    {{ $hasFilters ? 'Ajuste os filtros para ver outros pedidos.' : 'Os pedidos chegam pela Shopee automaticamente. Use "Sincronizar pedidos" para buscar os últimos 15 dias.' }}
                </p>
                @if($hasFilters)
                    <button wire:click="clearFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-sm shadow-lg" style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                        <i class="bi bi-x-circle-fill"></i> Limpar filtros
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                @foreach($orders as $order)
                    @php
                        [$stText, $stColor] = \App\Livewire\Shopee\OrdersList::statusBadge($order->order_status);
                        $orderLogs = $logs[$order->shopee_order_sn] ?? collect();
                        [$stockText, $stockColor, $stockIcon] = \App\Livewire\Shopee\OrdersList::stockBadge($order, $orderLogs);
                        $items = \App\Livewire\Shopee\OrdersList::itemsWithProducts($order, $publications);
                        $buyer = $order->buyer_username ?: 'Comprador';
                        $sale = $order->importedSale;
                        $cancelled = \App\Livewire\Shopee\OrdersList::groupOf($order->order_status) === 'cancelado';
                    @endphp
                    <div wire:key="so-{{ $order->id }}"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col hover:shadow-lg hover:shadow-orange-500/10 hover:-translate-y-0.5 transition-all">

                        <div class="flex items-start justify-between gap-2 px-4 pt-4 pb-2.5">
                            <div class="min-w-0">
                                <button wire:click="openOrder('{{ $order->shopee_order_sn }}')" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline font-mono truncate block max-w-full">
                                    #{{ $order->shopee_order_sn }}
                                </button>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $sp($order->shopee_created_at ?? $order->created_at)?->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span class="{{ $badgeClass($stColor) }} px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide whitespace-nowrap">{{ $stText }}</span>
                        </div>

                        <div class="px-4 pb-3 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                                <span class="text-white font-black text-[10px]">{{ mb_strtoupper(mb_substr($buyer, 0, 1)) }}</span>
                            </div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $buyer }}</p>
                        </div>

                        <div class="border-t border-dashed border-slate-100 dark:border-slate-800 mx-4"></div>

                        <div class="px-4 py-3 flex-1 space-y-2">
                            @foreach(array_slice($items, 0, 3) as $item)
                                <div class="flex items-center gap-2.5">
                                    @if($item['thumb'])
                                        <img src="{{ $item['thumb'] }}" alt="" loading="lazy"
                                             class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-box text-slate-400 text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate leading-snug">{{ $item['title'] }}</p>
                                        <p class="text-[11px] text-slate-400 leading-snug truncate">
                                            {{ $item['qty'] }}x @if($item['unit'] > 0) &bull; {{ $money($item['unit']) }} @endif
                                            @unless($item['linked'])
                                                <span class="text-amber-600 dark:text-amber-400 font-semibold">&bull; sem vínculo</span>
                                            @endunless
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                            @if(count($items) > 3)
                                <p class="text-[11px] text-orange-600 dark:text-orange-400 font-semibold text-right">+{{ count($items) - 3 }} item(s)</p>
                            @endif
                        </div>

                        <div class="border-t border-dashed border-slate-100 dark:border-slate-800 mx-4"></div>

                        <div class="px-4 pt-2.5 flex items-end justify-between gap-2">
                            <div class="space-y-1 min-w-0">
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Tarifa: <span class="font-semibold text-red-600 dark:text-red-400">{{ $order->fee_amount !== null ? '- ' . $money($order->fee_amount) : '—' }}</span>
                                </p>
                                <p class="text-[11px] font-semibold {{ $stockColor === 'emerald' ? 'text-emerald-600 dark:text-emerald-400' : ($stockColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400') }} truncate">
                                    <i class="bi {{ $stockIcon }} mr-0.5"></i>{{ $stockText }}
                                </p>
                            </div>
                            <p class="text-lg font-black leading-none whitespace-nowrap {{ $cancelled ? 'text-slate-400 line-through' : 'text-emerald-700 dark:text-emerald-400' }}">{{ $money($order->total_amount) }}</p>
                        </div>

                        <div class="px-4 pt-3 pb-4 flex gap-2">
                            <button wire:click="openOrder('{{ $order->shopee_order_sn }}')"
                                    class="{{ ($sale || !$cancelled) ? 'flex-none' : 'flex-1' }} inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700/40 text-orange-700 dark:text-orange-400 text-xs font-bold hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-all">
                                <i class="bi bi-eye-fill"></i> Detalhes
                            </button>
                            @if($sale)
                                <a href="{{ route('sales.show', $sale->id) }}" wire:navigate
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition-all"
                                   title="Já importado como venda">
                                    <i class="bi bi-check2-circle"></i> Venda #{{ $sale->id }}
                                </a>
                            @elseif(!$cancelled)
                                <button wire:click="importOrder({{ $order->id }})"
                                        wire:confirm="Importar o pedido #{{ $order->shopee_order_sn }} como venda? O estoque não será baixado de novo."
                                        wire:loading.attr="disabled" wire:target="importOrder({{ $order->id }})"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all disabled:opacity-60">
                                    <i class="bi bi-download"></i> <span class="whitespace-nowrap">Importar como venda</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <x-pagination-bar :paginator="$orders" label="pedidos" :per-page-options="[12, 24, 48]" />
        @endif
        </div>
    </div>

    {{-- ─────────────── MODAL DETALHE ─────────────── --}}
    @if($pedido !== '')
        @teleport('body')
        <div>
        {{-- Barra inferior do celular some enquanto o detalhe está aberto --}}
        <style>@media (max-width: 1366px) { .mobile-bottom-tabbar, .mobile-top-bell { display: none !important; } }</style>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[99990] flex items-end sm:items-center justify-center sm:p-4"
             wire:click="closeOrder" x-data @keydown.escape.window="$wire.closeOrder()">
            <div class="bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-2xl max-h-[92vh] border border-orange-100 dark:border-orange-800/30 overflow-hidden flex flex-col"
                 wire:click.stop>
                @if(!$selected)
                    <div class="p-8 text-center">
                        <i class="bi bi-search text-4xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Pedido #{{ $pedido }} não encontrado.</p>
                        <button wire:click="closeOrder" class="mt-4 px-5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-sm">Fechar</button>
                    </div>
                @else
                    @php
                        $so = $selected;
                        [$soText] = \App\Livewire\Shopee\OrdersList::statusBadge($so->order_status);
                        $soItems = \App\Livewire\Shopee\OrdersList::itemsWithProducts($so, $publications);
                        [$soStock, $soStockColor, $soStockIcon] = \App\Livewire\Shopee\OrdersList::stockBadge($so, $selectedLogs);
                        $soSubtotal = collect($soItems)->sum(fn ($i) => $i['unit'] * $i['qty']);
                        $soRaw = is_array($so->raw_data) ? $so->raw_data : [];
                        $soShipFee = $soRaw['actual_shipping_fee'] ?? $soRaw['estimated_shipping_fee'] ?? null;
                        $soAddr = is_array($so->shipping_address) ? $so->shipping_address : [];
                        $soCancelled = \App\Livewire\Shopee\OrdersList::groupOf($so->order_status) === 'cancelado';
                        $soSale = $so->importedSale;
                    @endphp
                    <div class="flex-shrink-0 flex items-center justify-between gap-3 px-5 sm:px-6 py-4" style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                        <div class="min-w-0">
                            <p class="text-xs text-orange-100 font-medium mb-0.5">Pedido Shopee</p>
                            <h3 class="text-base sm:text-lg font-extrabold text-white font-mono truncate">#{{ $so->shopee_order_sn }}</h3>
                            <p class="text-xs text-orange-100 mt-0.5">{{ $sp($so->shopee_created_at ?? $so->created_at)?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-3 py-1.5 rounded-full bg-white/25 text-white text-[11px] font-extrabold uppercase whitespace-nowrap">{{ $soText }}</span>
                            <button wire:click="closeOrder" class="w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white" aria-label="Fechar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-y-auto flex-1 p-4 sm:p-6 space-y-5">

                        {{-- Comprador + envio --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-orange-50 dark:bg-orange-900/10 rounded-xl p-4 border border-orange-100 dark:border-orange-800/30">
                                <h4 class="text-xs font-extrabold text-orange-700 dark:text-orange-400 uppercase mb-2"><i class="bi bi-person-fill mr-1"></i> Comprador</h4>
                                <p class="font-bold text-slate-800 dark:text-white">{{ $so->buyer_username ?: '—' }}</p>
                                @if(!empty($soAddr['name']))<p class="text-sm text-slate-600 dark:text-slate-300">{{ $soAddr['name'] }}</p>@endif
                                @if(!empty($soAddr['city']) || !empty($soAddr['state']))
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ trim(($soAddr['city'] ?? '') . ' / ' . ($soAddr['state'] ?? ''), ' /') }}</p>
                                @endif
                                @if($so->payment_method)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><i class="bi bi-credit-card mr-1"></i>{{ $so->payment_method }}</p>
                                @endif
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4 border border-slate-100 dark:border-slate-700">
                                <h4 class="text-xs font-extrabold text-slate-600 dark:text-slate-300 uppercase mb-2"><i class="bi bi-truck mr-1"></i> Envio</h4>
                                <dl class="text-sm space-y-1">
                                    <div class="flex justify-between gap-2"><dt class="text-slate-500 dark:text-slate-400">Transportadora</dt><dd class="font-semibold text-slate-800 dark:text-white text-right truncate">{{ $so->shipping_carrier ?: '—' }}</dd></div>
                                    <div class="flex justify-between gap-2"><dt class="text-slate-500 dark:text-slate-400">Rastreio</dt><dd class="font-mono text-slate-800 dark:text-white text-right truncate">{{ $so->tracking_number ?: '—' }}</dd></div>
                                    @if($so->ship_by_date)
                                        <div class="flex justify-between gap-2"><dt class="text-slate-500 dark:text-slate-400">Enviar até</dt><dd class="font-semibold text-slate-800 dark:text-white">{{ $sp($so->ship_by_date)->format('d/m/Y') }}</dd></div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        {{-- Itens --}}
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase mb-2"><i class="bi bi-box-seam mr-1"></i> Itens ({{ count($soItems) }})</h4>
                            <div class="space-y-2">
                                @foreach($soItems as $item)
                                    <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        @if($item['thumb'])
                                            <img src="{{ $item['thumb'] }}" alt="" class="w-12 h-12 rounded-lg object-cover flex-shrink-0 border border-slate-100 dark:border-slate-700">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0"><i class="bi bi-box text-slate-400"></i></div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-white leading-snug">{{ $item['title'] }}</p>
                                            @if($item['model_name'])<p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['model_name'] }}</p>@endif
                                            <p class="text-[11px] mt-0.5 {{ $item['linked'] ? 'text-slate-500 dark:text-slate-400' : 'text-amber-600 dark:text-amber-400 font-semibold' }}">
                                                @if($item['linked'])
                                                    <i class="bi bi-link-45deg"></i> {{ $item['products']->map(fn ($p) => ($p->pivot->quantity > 1 ? $p->pivot->quantity . 'x ' : '') . $p->name)->implode(', ') }}
                                                @else
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Anúncio sem produto vinculado
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['qty'] }}x {{ $item['unit'] > 0 ? $money($item['unit']) : '' }}</p>
                                            @if($item['unit'] > 0)<p class="text-sm font-bold text-slate-800 dark:text-white">{{ $money($item['unit'] * $item['qty']) }}</p>@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Valores --}}
                        <div class="rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                            @if($soSubtotal > 0)
                                <div class="flex justify-between px-4 py-2"><span class="text-slate-500 dark:text-slate-400">Produtos</span><span class="text-slate-800 dark:text-white">{{ $money($soSubtotal) }}</span></div>
                            @endif
                            @if($soShipFee !== null)
                                <div class="flex justify-between px-4 py-2"><span class="text-slate-500 dark:text-slate-400">Frete</span><span class="text-slate-800 dark:text-white">{{ $money($soShipFee) }}</span></div>
                            @endif
                            <div class="flex justify-between px-4 py-2"><span class="font-semibold text-slate-700 dark:text-slate-200">Total do pedido</span><span class="font-bold text-slate-900 dark:text-white">{{ $money($so->total_amount) }}</span></div>
                            <div class="flex justify-between px-4 py-2"><span class="text-slate-500 dark:text-slate-400">Tarifas Shopee</span><span class="text-red-600 dark:text-red-400">{{ $so->fee_amount !== null ? '- ' . $money($so->fee_amount) : 'não informado' }}</span></div>
                            @if($so->fee_amount !== null)
                                <div class="flex justify-between px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/15"><span class="font-bold text-slate-700 dark:text-slate-200">Você recebe (aprox.)</span><span class="text-lg font-black text-emerald-700 dark:text-emerald-400">{{ $money((float) $so->total_amount - (float) $so->fee_amount) }}</span></div>
                            @endif
                        </div>

                        {{-- Estoque --}}
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase mb-2 flex items-center justify-between gap-2">
                                <span><i class="bi bi-boxes mr-1"></i> Movimentos de estoque</span>
                                <span class="{{ $badgeClass($soStockColor) }} normal-case px-2 py-0.5 rounded-full text-[11px] font-bold"><i class="bi {{ $soStockIcon }}"></i> {{ $soStock }}</span>
                            </h4>
                            @if($selectedLogs->isEmpty())
                                <p class="text-sm text-slate-500 dark:text-slate-400 px-1">Nenhum movimento de estoque para este pedido.</p>
                            @else
                                <div class="rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($selectedLogs as $log)
                                        <div class="flex items-center justify-between gap-3 px-4 py-2 text-sm">
                                            <div class="min-w-0">
                                                <p class="font-medium text-slate-800 dark:text-white truncate">{{ $log->product_name }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                    {{ $log->operation_type === 'marketplace_cancel' ? 'Devolvido (cancelamento)' : 'Baixa da venda' }}
                                                    &bull; {{ $sp($log->created_at)?->format('d/m H:i') }}
                                                    &bull; {{ $log->quantity_before }} → {{ $log->quantity_after }}
                                                </p>
                                            </div>
                                            <span class="font-bold whitespace-nowrap {{ $log->quantity_change < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if($so->error_message)
                                <p class="mt-2 text-xs text-amber-700 dark:text-amber-400"><i class="bi bi-exclamation-triangle-fill"></i> {{ $so->error_message }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 px-4 sm:px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                        <button wire:click="closeOrder" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-700">Fechar</button>
                        @if($soSale)
                            <a href="{{ route('sales.show', $soSale->id) }}" wire:navigate
                               class="flex-1 px-5 py-2.5 rounded-xl font-extrabold text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40 flex items-center justify-center gap-2">
                                <i class="bi bi-check2-circle"></i> Importado: ver venda #{{ $soSale->id }}
                            </a>
                        @elseif($soCancelled)
                            <p class="flex-1 text-center sm:text-left text-sm text-slate-500 dark:text-slate-400 self-center">Pedido cancelado: não pode virar venda.</p>
                        @else
                            <button wire:click="importOrder({{ $so->id }})"
                                    wire:confirm="Importar o pedido #{{ $so->shopee_order_sn }} como venda? O estoque não será baixado de novo."
                                    wire:loading.attr="disabled" wire:target="importOrder"
                                    class="flex-1 px-5 py-2.5 rounded-xl font-extrabold text-sm text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:shadow-lg hover:shadow-emerald-400/30 flex items-center justify-center gap-2 disabled:opacity-60">
                                <i class="bi bi-download"></i> Importar como venda
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        </div>
        @endteleport
    @endif

    <x-toast-notifications />
</div>
