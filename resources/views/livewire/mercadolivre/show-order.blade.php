@push('styles')
<style>
/* ==================== SHOW ORDER – ML AMBER ==================== */
.show-order-hero {
    background: linear-gradient(135deg,
        rgba(255,255,255,.85) 0%,
        rgba(254,243,199,.92) 40%,
        rgba(251,191,36,.55) 100%);
}
/* Skeleton */
.skel-o { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel-o 1.4s infinite; }
@keyframes skel-o {0%{background-position:200% 0}100%{background-position:-200% 0}}
/* Timeline envio */
.ship-step { position:relative; padding-left:1.75rem; }
.ship-step::before { content:''; position:absolute; left:.65rem; top:1.5rem; bottom:0; width:2px; background:#e2e8f0; }
.ship-step:last-child::before { display:none; }
.ship-dot { position:absolute; left:0; top:.35rem; width:1.3rem; height:1.3rem; border-radius:50%;
            border:2px solid; display:flex; align-items:center; justify-content:center; font-size:.5rem; }
/* Fade-in card */
.so-card { animation: so-fadein .4s ease both; }
@keyframes so-fadein { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
</style>
@endpush

<div class="show-order-page min-h-screen bg-slate-50 dark:bg-slate-950"
     x-data="{ ready: false }" x-init="setTimeout(()=>ready=true, 80)">

    {{-- ============================================================
         HERO HEADER
    ============================================================ --}}
    <div class="show-order-hero border-b border-amber-200/60 dark:border-amber-700/30 px-4 sm:px-6 py-5
                dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
        <div class="max-w-5xl mx-auto">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <a href="{{ route('dashboard') }}" class="hover:text-amber-600 transition-colors">Início</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('mercadolivre.orders') }}" class="hover:text-amber-600 transition-colors">Pedidos ML</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-amber-700 dark:text-amber-400 font-semibold">#{{ $orderId }}</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative w-14 h-14 flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500
                                    flex items-center justify-center shadow-lg shadow-amber-400/40">
                            <i class="bi bi-bag-check-fill text-2xl text-white"></i>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-gradient-to-br from-yellow-300 to-amber-500
                                    flex items-center justify-center shadow ring-2 ring-white dark:ring-slate-900">
                            <span class="text-white font-black text-[9px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pedido Mercado Livre</p>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight
                                   bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700
                                   dark:from-white dark:via-amber-400 dark:to-yellow-300
                                   bg-clip-text text-transparent leading-tight">
                            #{{ $orderId }}
                        </h1>
                        @if($order)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $this->formatDate($order['date_created'] ?? now()) }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($order && !$loading)
                        @php $st = $this->getStatusBadge($order['status'] ?? ''); @endphp
                        @php
                            $badgeCol = match($st['color']){
                                'emerald','green'=>'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700',
                                'blue'=>'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700',
                                'yellow'=>'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-700',
                                'red'=>'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700',
                                default=>'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                            };
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-xs font-extrabold uppercase border {{ $badgeCol }}">
                            {{ $st['text'] }}
                        </span>
                    @endif
                    <a href="{{ route('mercadolivre.orders') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold
                              bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                              text-slate-700 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    @if($order && !$loading)
                        <button wire:click="importOrder" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white shadow
                                       bg-gradient-to-r from-amber-500 to-orange-500
                                       hover:shadow-md hover:from-amber-600 hover:to-orange-600 transition-all
                                       disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="importOrder">
                                <i class="bi bi-download"></i> Importar
                            </span>
                            <span wire:loading wire:target="importOrder">
                                <i class="bi bi-arrow-repeat animate-spin"></i> Importando…
                            </span>
                        </button>
                    @endif
                    <button wire:click="loadOrder" wire:loading.attr="disabled"
                            class="p-2 rounded-xl bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm"
                            title="Recarregar">
                        <span wire:loading.remove wire:target="loadOrder"><i class="bi bi-arrow-clockwise text-lg"></i></span>
                        <span wire:loading wire:target="loadOrder"><i class="bi bi-arrow-repeat animate-spin text-lg"></i></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    {{-- /HERO --}}

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 space-y-5">

        {{-- ============================================================
             LOADING STATE
        ============================================================ --}}
        @if($loading)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 space-y-5">
                    @for($i=0;$i<3;$i++)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                            <div class="skel-o h-4 w-32 rounded-lg"></div>
                            <div class="skel-o h-10 w-full rounded-xl"></div>
                            <div class="skel-o h-10 w-3/4 rounded-xl"></div>
                        </div>
                    @endfor
                </div>
                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                        <div class="skel-o h-4 w-24 rounded-lg"></div>
                        <div class="skel-o h-16 w-full rounded-xl"></div>
                        <div class="skel-o h-16 w-full rounded-xl"></div>
                        <div class="skel-o h-10 w-full rounded-xl"></div>
                    </div>
                </div>
            </div>

        {{-- ============================================================
             ERROR STATE
        ============================================================ --}}
        @elseif($errorMessage)
            <div class="flex flex-col items-center justify-center py-20 text-center so-card">
                <div class="w-20 h-20 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-4xl text-red-400 dark:text-red-500"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Erro ao carregar pedido</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-sm">{{ $errorMessage }}</p>
                <button wire:click="loadOrder"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm transition-all shadow">
                    <i class="bi bi-arrow-clockwise mr-2"></i> Tentar Novamente
                </button>
            </div>

        {{-- ============================================================
             ORDER DATA
        ============================================================ --}}
        @elseif($order)
            @php
                $buyer   = $order['buyer'] ?? [];
                $items   = $order['order_items'] ?? [];
                $pays    = $order['payments'] ?? [];
                $ship    = $order['shipping'] ?? [];
                $tags    = $order['tags'] ?? [];
                $context = $order['context'] ?? [];
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5" x-show="ready" style="display:none"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">

                {{-- ============ COLUNA PRINCIPAL ============ --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- 1. Resumo do pedido --}}
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h2 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i class="bi bi-info-circle-fill text-amber-500 mr-2"></i> Resumo
                            </h2>
                        </div>
                        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-5">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">ID Pedido ML</p>
                                <p class="font-bold text-slate-800 dark:text-white font-mono text-sm">{{ $order['id'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Data</p>
                                <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">{{ $this->formatDate($order['date_created']) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Última atualização</p>
                                <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">
                                    {{ $this->formatDate($order['last_updated'] ?? $order['date_created']) }}
                                </p>
                            </div>
                            @if(!empty($order['date_closed']))
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Fechamento</p>
                                <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">{{ $this->formatDate($order['date_closed']) }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Moeda</p>
                                <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">{{ $order['currency_id'] ?? 'BRL' }}</p>
                            </div>
                            @if(!empty($tags))
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Tags</p>
                                <div class="flex flex-wrap gap-1 mt-0.5">
                                    @foreach($tags as $tag)
                                        <span class="text-[9px] bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                                     px-1.5 py-0.5 rounded font-semibold uppercase">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Comprador --}}
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden"
                         style="animation-delay:.05s">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                            <h2 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i class="bi bi-person-fill text-amber-500 mr-2"></i> Comprador
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500
                                            flex items-center justify-center flex-shrink-0 shadow">
                                    <span class="text-white font-black text-lg">
                                        {{ mb_strtoupper(mb_substr($buyer['nickname'] ?? 'U', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-extrabold text-slate-800 dark:text-white text-base">
                                        {{ $buyer['nickname'] ?? '—' }}
                                    </p>
                                    @if(!empty($buyer['first_name']))
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ $buyer['first_name'] }} {{ $buyer['last_name'] ?? '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">ID ML</p>
                                    <p class="font-mono text-slate-700 dark:text-slate-300">{{ $buyer['id'] ?? '—' }}</p>
                                </div>
                                @if(!empty($buyer['email']))
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">E-mail</p>
                                    <p class="text-slate-700 dark:text-slate-300 truncate">{{ $buyer['email'] }}</p>
                                </div>
                                @endif
                                @if(!empty($buyer['phone']['number']))
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Telefone</p>
                                    <p class="text-slate-700 dark:text-slate-300">
                                        +{{ $buyer['phone']['area_code'] ?? '' }} {{ $buyer['phone']['number'] }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. Itens --}}
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden"
                         style="animation-delay:.10s">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h2 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i class="bi bi-box-seam-fill text-amber-500 mr-2"></i> Itens do Pedido
                            </h2>
                            <span class="text-xs text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-900/20
                                         border border-amber-200 dark:border-amber-700/40 px-2 py-0.5 rounded-full">
                                {{ count($items) }} iten(s)
                            </span>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($items as $idx => $item)
                                @php $it = $item['item'] ?? []; @endphp
                                <div class="p-5 flex items-start gap-4
                                            {{ $idx % 2 === 0 ? '' : 'bg-slate-50/50 dark:bg-slate-800/20' }}">
                                    @if(!empty($it['thumbnail']))
                                        <img src="{{ $it['thumbnail'] }}" alt="{{ $it['title'] ?? '' }}"
                                             class="w-16 h-16 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm">
                                    @else
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-box text-slate-400 text-2xl"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-slate-800 dark:text-white leading-snug">
                                            {{ $it['title'] ?? 'Produto' }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 mt-2">
                                            @if(!empty($it['id']))
                                                <span class="text-xs text-slate-400 font-mono">{{ $it['id'] }}</span>
                                            @endif
                                            @if(!empty($item['variation_id']))
                                                <span class="text-xs bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-300
                                                             border border-purple-100 dark:border-purple-700/40 px-2 py-0.5 rounded-full font-semibold">
                                                    Variação: {{ $item['variation_id'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">
                                                Qtd: <strong class="text-slate-700 dark:text-slate-200">{{ $item['quantity'] ?? 1 }}</strong>
                                            </span>
                                            <span class="text-slate-500 dark:text-slate-400">
                                                Unitário: <strong class="text-slate-700 dark:text-slate-200">{{ $this->formatPrice($item['unit_price'] ?? 0) }}</strong>
                                            </span>
                                            @if(!empty($item['full_unit_price']) && $item['full_unit_price'] != $item['unit_price'])
                                                <span class="text-xs text-slate-400 line-through">
                                                    {{ $this->formatPrice($item['full_unit_price']) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-black text-emerald-700 dark:text-emerald-400 text-base">
                                            {{ $this->formatPrice(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1)) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. Envio --}}
                    @if(!empty($ship))
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden"
                         style="animation-delay:.15s">
                        <button class="w-full px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between"
                                wire:click="$toggle('showShipping')">
                            <h2 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i class="bi bi-truck-fill text-purple-500 mr-2"></i> Envio & Entrega
                            </h2>
                            <i class="bi {{ $showShipping ? 'bi-chevron-up' : 'bi-chevron-down' }} text-slate-400 text-sm transition-transform"></i>
                        </button>
                        @if($showShipping)
                        <div class="p-6">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-5">
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">ID Envio</p>
                                    <p class="font-mono text-slate-700 dark:text-slate-300">{{ $ship['id'] ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Status</p>
                                    <p class="font-bold text-slate-800 dark:text-white capitalize">{{ $ship['status'] ?? '—' }}</p>
                                </div>
                                @if(!empty($ship['date_created']))
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-0.5">Criado em</p>
                                    <p class="text-slate-700 dark:text-slate-300">{{ $this->formatDate($ship['date_created']) }}</p>
                                </div>
                                @endif
                            </div>

                            {{-- Endereço de entrega --}}
                            @if(!empty($ship['receiver_address']))
                                @php $addr = $ship['receiver_address']; @endphp
                                <div class="bg-purple-50 dark:bg-purple-900/10 rounded-xl p-4 border border-purple-100 dark:border-purple-800/30">
                                    <p class="text-xs font-extrabold text-purple-700 dark:text-purple-400 uppercase mb-2">
                                        <i class="bi bi-geo-alt-fill mr-1"></i> Endereço de Entrega
                                    </p>
                                    <div class="text-sm text-slate-700 dark:text-slate-300 space-y-0.5">
                                        @if(!empty($addr['street_name']))
                                            <p>{{ $addr['street_name'] }}, {{ $addr['street_number'] ?? 'S/N' }}</p>
                                        @endif
                                        @if(!empty($addr['comment']))
                                            <p class="text-slate-500">{{ $addr['comment'] }}</p>
                                        @endif
                                        <p>
                                            {{ $addr['city']['name'] ?? '' }}
                                            @if(!empty($addr['state']['name'])) – {{ $addr['state']['name'] }} @endif
                                        </p>
                                        @if(!empty($addr['zip_code']))
                                            <p class="font-mono text-xs text-slate-500">CEP {{ $addr['zip_code'] }}</p>
                                        @endif
                                        @if(!empty($addr['country']['name']))
                                            <p class="text-slate-500">{{ $addr['country']['name'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                </div>
                {{-- /COLUNA PRINCIPAL --}}

                {{-- ============ SIDEBAR ============ --}}
                <div class="space-y-5">

                    {{-- Total --}}
                    <div class="so-card bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20
                                rounded-2xl border border-emerald-200 dark:border-emerald-700/40 p-6 shadow-sm"
                         style="animation-delay:.05s">
                        <p class="text-xs font-extrabold text-emerald-700 dark:text-emerald-400 uppercase mb-1">
                            <i class="bi bi-currency-dollar mr-1"></i> Total do Pedido
                        </p>
                        <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400">
                            {{ $this->formatPrice($order['total_amount'] ?? 0) }}
                        </p>
                        @if(!empty($order['paid_amount']) && $order['paid_amount'] != $order['total_amount'])
                            <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-1">
                                Pago: {{ $this->formatPrice($order['paid_amount']) }}
                            </p>
                        @endif

                        <div class="mt-4">
                            <button wire:click="importOrder" wire:loading.attr="disabled"
                                    class="w-full px-4 py-3 rounded-xl font-extrabold text-sm text-white
                                           bg-gradient-to-r from-emerald-500 to-green-600
                                           hover:shadow-lg hover:shadow-emerald-400/30 transition-all
                                           flex items-center justify-center gap-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="importOrder">
                                    <i class="bi bi-download"></i> Importar Pedido
                                </span>
                                <span wire:loading wire:target="importOrder">
                                    <i class="bi bi-arrow-repeat animate-spin"></i> Importando…
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Pagamentos --}}
                    @if(!empty($pays))
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden"
                         style="animation-delay:.1s">
                        <button class="w-full px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between"
                                wire:click="$toggle('showPayments')">
                            <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i class="bi bi-credit-card-fill text-blue-500 mr-2"></i> Pagamentos
                            </h3>
                            <i class="bi {{ $showPayments ? 'bi-chevron-up' : 'bi-chevron-down' }} text-slate-400 text-sm"></i>
                        </button>
                        @if($showPayments)
                        <div class="p-5 space-y-3">
                            @foreach($pays as $pay)
                                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3.5 border border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">
                                            {{ match($pay['payment_type'] ?? ''){
                                                'credit_card' => 'Cartão Crédito',
                                                'debit_card'  => 'Cartão Débito',
                                                'account_money' => 'Conta ML',
                                                'ticket' => 'Boleto',
                                                default => $pay['payment_type'] ?? '—'
                                            } }}
                                        </p>
                                        @php
                                            $psc = match($pay['status'] ?? ''){
                                                'approved' => 'text-emerald-600',
                                                'pending','in_process' => 'text-yellow-500',
                                                'rejected','cancelled' => 'text-red-500',
                                                default => 'text-slate-500',
                                            };
                                        @endphp
                                        <span class="text-xs font-semibold {{ $psc }}">{{ $pay['status'] ?? '—' }}</span>
                                    </div>
                                    @if(!empty($pay['transaction_amount']))
                                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                            {{ $this->formatPrice($pay['transaction_amount']) }}
                                        </p>
                                    @endif
                                    @if(!empty($pay['date_approved']))
                                        <p class="text-xs text-slate-400 mt-1">
                                            Aprovado: {{ $this->formatDate($pay['date_approved']) }}
                                        </p>
                                    @endif
                                    @if(!empty($pay['installments']) && $pay['installments'] > 1)
                                        <p class="text-xs text-blue-500 mt-0.5">{{ $pay['installments'] }}x parcelas</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Dados extras --}}
                    @if(!empty($order['context']) || !empty($order['fulfilled']))
                    <div class="so-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5"
                         style="animation-delay:.15s">
                        <h3 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase mb-3">
                            <i class="bi bi-diagram-3-fill text-amber-500 mr-1"></i> Contexto
                        </h3>
                        <div class="space-y-2 text-sm">
                            @if(!empty($order['fulfilled']))
                                <div class="flex items-center gap-2 text-emerald-600">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span class="font-semibold">Pedido cumprido</span>
                                </div>
                            @endif
                            @if(!empty($context['channel']))
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Canal</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $context['channel'] }}</span>
                                </div>
                            @endif
                            @if(!empty($context['application']))
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Aplicação</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $context['application'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
                {{-- /SIDEBAR --}}

            </div>
        @endif
        {{-- /ORDER DATA --}}

    </div>
    {{-- /max-w --}}

    <x-toast-notifications />
</div>
