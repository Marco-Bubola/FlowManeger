@push('styles')
<style>
.skel-p{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel-p 1.4s infinite;}
@keyframes skel-p{0%{background-position:200% 0}100%{background-position:-200% 0}}
.promo-card:hover{transform:translateY(-2px);}
</style>
@endpush

<div class="promotions-page min-h-screen"
     x-data="{
         tipsOpen:        @entangle('tipsOpen'),
         showDetailModal: @entangle('showDetailModal')
     }">

    <x-loading-overlay message="Carregando promoções..." />

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/85 via-amber-50/90 to-yellow-50/80
                dark:from-slate-800/90 dark:via-amber-900/10 dark:to-slate-800/30
                backdrop-blur-xl border-b border-amber-100/60 dark:border-amber-900/30
                shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-52 h-52 bg-gradient-to-br from-amber-400/20 via-yellow-300/15 to-orange-300/10 rounded-full transform translate-x-20 -translate-y-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-gradient-to-tr from-amber-300/15 via-yellow-300/10 to-orange-200/10 rounded-full transform -translate-x-12 translate-y-12 pointer-events-none"></div>
        <div class="relative w-full px-4 sm:px-6 lg:px-8 py-6">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <a href="{{ route('dashboard') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors flex items-center gap-1">
                    <i class="bi bi-house-fill text-[11px]"></i> Início
                </a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <a href="{{ route('mercadolivre.products') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Mercado Livre</a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <span class="text-amber-700 dark:text-amber-400 font-semibold">Promoções</span>
            </nav>

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-start gap-5">
                    <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-2xl shadow-xl shadow-amber-500/30 flex-shrink-0">
                        <i class="bi bi-percent text-white text-3xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50 pointer-events-none"></div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center shadow-md ring-2 ring-amber-100 dark:ring-slate-700">
                            <span class="text-amber-600 dark:text-amber-400 font-black text-[8px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-orange-600 dark:from-amber-200 dark:via-amber-300 dark:to-yellow-300 bg-clip-text text-transparent leading-tight">
                            Promoções ML
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Ofertas do dia, promoções relâmpago e marcas
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button wire:click="loadPromotions"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-white/70 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadPromotions"></i>
                        Atualizar
                    </button>
                    <button @click="tipsOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-bold
                                   shadow-lg shadow-amber-500/25 hover:shadow-xl hover:from-amber-600 hover:to-orange-600 transition-all">
                        <i class="bi bi-lightbulb-fill"></i> Dicas
                    </button>
                </div>
            </div>

            {{-- Filtros rápidos --}}
            <div class="mt-5 pt-4 border-t border-amber-100/60 dark:border-amber-900/20 flex flex-wrap gap-3">
                {{-- Tipo --}}
                <div class="flex items-center gap-1.5 bg-white/80 dark:bg-slate-800 rounded-xl px-3 py-1.5 border border-amber-200 dark:border-slate-600">
                    <i class="bi bi-tag-fill text-amber-500 text-sm"></i>
                    <select wire:model.live="typeFilter"
                            class="text-xs font-semibold bg-transparent text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="">Todos os tipos</option>
                        <option value="DEAL">Oferta do Dia</option>
                        <option value="LIGHTNING_DEAL">Oferta Relâmpago</option>
                        <option value="BRAND_PROMO">Promo de Marca</option>
                    </select>
                </div>
                {{-- Status --}}
                <div class="flex items-center gap-1.5 bg-white/80 dark:bg-slate-800 rounded-xl px-3 py-1.5 border border-amber-200 dark:border-slate-600">
                    <i class="bi bi-circle-fill text-emerald-500 text-[8px]"></i>
                    <select wire:model.live="statusFilter"
                            class="text-xs font-semibold bg-transparent text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="">Todos os status</option>
                        <option value="started">Ativas</option>
                        <option value="stopped">Pausadas</option>
                        <option value="scheduled">Agendadas</option>
                        <option value="finished">Encerradas</option>
                    </select>
                </div>
                @if($total > 0)
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20">
                        {{ $total }} promoç{{ $total === 1 ? 'ão' : 'ões' }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 pb-10">

        @if($errorMessage)
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 mb-6">
                <i class="bi bi-exclamation-circle-fill text-red-500 text-xl flex-shrink-0"></i>
                <p class="flex-1 text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                <button wire:click="loadPromotions" class="text-sm font-bold text-red-600 hover:text-red-800">Tentar novamente</button>
            </div>
        @endif

        @if($loading)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @for($i=0;$i<6;$i++)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                        <div class="skel-p h-4 w-28 rounded-lg"></div>
                        <div class="skel-p h-3 w-full rounded"></div>
                        <div class="skel-p h-3 w-3/4 rounded"></div>
                        <div class="skel-p h-6 w-20 rounded-full"></div>
                    </div>
                @endfor
            </div>
        @elseif(count($promotions) === 0)
            <div class="flex flex-col items-center justify-center py-16 sm:py-24">
                <div class="relative mb-6">
                    <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-amber-400/20 via-yellow-300/15 to-orange-300/10
                                dark:from-amber-900/30 dark:via-amber-800/20 dark:to-orange-900/10
                                border border-amber-200/60 dark:border-amber-700/30
                                flex items-center justify-center shadow-xl">
                        <i class="bi bi-percent text-5xl text-amber-400 dark:text-amber-500"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500
                                flex items-center justify-center shadow-lg">
                        <i class="bi bi-tag-fill text-white text-xs"></i>
                    </div>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-white mb-2 text-center">
                    Nenhuma promoção encontrada
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 max-w-sm text-center leading-relaxed">
                    Você não participa de nenhuma promoção com os filtros selecionados.
                    Tente outro filtro ou aguarde convites de promoção do Mercado Livre.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <button wire:click="$set('typeFilter', '')" wire:click.queueing="$set('statusFilter', '')"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl
                                   bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-sm
                                   shadow-lg shadow-amber-500/25 hover:shadow-xl hover:from-amber-600 hover:to-orange-600
                                   transition-all duration-200 active:scale-95">
                        <i class="bi bi-funnel-fill"></i> Ver Todas
                    </button>
                    <button wire:click="loadPromotions"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl
                                   bg-white dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-slate-700 dark:text-slate-300 font-semibold text-sm
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all duration-200">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($promotions as $promo)
                    @php
                        $promoId   = $promo['id'] ?? null;
                        $name      = $promo['name'] ?? $promo['title'] ?? 'Promoção';
                        $type      = $this->getTypeBadge($promo['type'] ?? null);
                        $status    = $this->getStatusBadge($promo['status'] ?? null);
                        $startDate = $this->formatDate($promo['start_time'] ?? $promo['start_date'] ?? null);
                        $endDate   = $this->formatDate($promo['finish_time'] ?? $promo['end_date'] ?? null);
                        $discount  = $promo['discount'] ?? $promo['percentage_discount'] ?? null;
                        $itemCount = $promo['items_count'] ?? $promo['offers_count'] ?? null;
                    @endphp
                    <div class="promo-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm
                                hover:shadow-md transition-all duration-200 overflow-hidden">
                        {{-- Header do card --}}
                        <div class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/40 dark:to-slate-900
                                    border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="{{ $type['bg'] }} px-2.5 py-0.5 rounded-full text-[10px] font-bold">{{ $type['label'] }}</span>
                                <span class="{{ $status['bg'] }} px-2.5 py-0.5 rounded-full text-[10px] font-bold">{{ $status['label'] }}</span>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm line-clamp-2">{{ $name }}</h3>
                            @if($promoId)
                                <p class="text-[10px] text-slate-400 mt-0.5">#{{ $promoId }}</p>
                            @endif
                        </div>

                        <div class="px-5 py-4 space-y-2">
                            @if($discount !== null)
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center">
                                        <i class="bi bi-percent text-amber-600 dark:text-amber-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 text-[10px]">Desconto</p>
                                        <p class="font-extrabold text-amber-600 dark:text-amber-400">{{ $discount }}%</p>
                                    </div>
                                </div>
                            @endif
                            @if($itemCount !== null)
                                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    <i class="bi bi-box-seam-fill text-amber-400 text-[10px]"></i>
                                    {{ number_format($itemCount) }} item(ns)
                                </div>
                            @endif
                            @if($startDate)
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-calendar3 text-[10px]"></i>
                                    <span>{{ $startDate }}{{ $endDate ? ' → ' . $endDate : '' }}</span>
                                </div>
                            @endif
                        </div>

                        @if($promoId)
                            <div class="px-5 pb-4">
                                <button wire:click="openDetail('{{ $promoId }}')"
                                        class="w-full px-3 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20
                                               border border-amber-200 dark:border-amber-700/30 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-all">
                                    <i class="bi bi-eye-fill mr-1"></i> Ver Detalhes
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($total > $perPage)
                <div class="flex items-center justify-between mt-6">
                    <p class="text-xs text-slate-500">
                        Exibindo {{ $offset + 1 }}–{{ min($offset + $perPage, $total) }} de {{ $total }}
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="prevPage" @disabled($offset === 0)
                                class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all
                                       bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700
                                       text-slate-600 dark:text-slate-300 hover:bg-slate-50 disabled:opacity-40">
                            <i class="bi bi-chevron-left mr-1"></i> Anterior
                        </button>
                        <button wire:click="nextPage" @disabled($offset + $perPage >= $total)
                                class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                                       bg-gradient-to-r from-amber-500 to-orange-500 shadow hover:shadow-md
                                       transition-all disabled:opacity-40">
                            Próximo <i class="bi bi-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ============================================================
         MODAL – DETALHE DA PROMOÇÃO
    ============================================================ --}}
    <div x-show="showDetailModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="showDetailModal = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9998] flex items-end sm:items-center justify-center p-4"
         style="display:none">
        <div x-show="showDetailModal"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh]
                    border border-amber-100 dark:border-amber-700/30 overflow-hidden flex flex-col"
             style="display:none">

            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <i class="bi bi-percent text-white text-xl"></i>
                    <h3 class="text-base font-extrabold text-white">
                        {{ $selectedPromotion['name'] ?? $selectedPromotion['title'] ?? 'Promoção' }}
                    </h3>
                </div>
                <button wire:click="closeDetailModal"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 p-6 space-y-4">
                @if($loadingDetail)
                    <div class="space-y-3">
                        @for($i=0;$i<5;$i++)<div class="skel-p h-4 rounded-lg"></div>@endfor
                    </div>
                @elseif($selectedPromotion)
                    @php
                        $sp = $selectedPromotion;
                        $spType = $this->getTypeBadge($sp['type'] ?? null);
                        $spStatus = $this->getStatusBadge($sp['status'] ?? null);
                    @endphp
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="{{ $spType['bg'] }} px-2.5 py-1 rounded-full text-xs font-bold">{{ $spType['label'] }}</span>
                        <span class="{{ $spStatus['bg'] }} px-2.5 py-1 rounded-full text-xs font-bold">{{ $spStatus['label'] }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        @php $sdItems = [
                            ['label'=>'Desconto',   'value'=> isset($sp['discount']) ? $sp['discount'].'%' : '-'],
                            ['label'=>'Itens',      'value'=> number_format($sp['items_count'] ?? $sp['offers_count'] ?? 0)],
                            ['label'=>'Início',     'value'=> $this->formatDate($sp['start_time'] ?? $sp['start_date'] ?? null)],
                            ['label'=>'Término',    'value'=> $this->formatDate($sp['finish_time'] ?? $sp['end_date'] ?? null)],
                        ]; @endphp
                        @foreach($sdItems as $sd)
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                <p class="text-slate-400 mb-0.5">{{ $sd['label'] }}</p>
                                <p class="font-bold text-slate-800 dark:text-white">{{ $sd['value'] ?: '-' }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if(count($promoItems) > 0)
                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">
                                Itens participantes ({{ count($promoItems) }})
                            </p>
                            <div class="space-y-2 max-h-52 overflow-y-auto">
                                @foreach($promoItems as $item)
                                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">
                                                {{ $item['item_id'] ?? $item['id'] ?? '-' }}
                                            </p>
                                            @if(isset($item['original_price']) || isset($item['new_price']))
                                                <p class="text-[10px] text-slate-500">
                                                    {{ $this->formatPrice($item['original_price'] ?? null) }}
                                                    @if(isset($item['new_price']))
                                                        → <span class="text-emerald-600 font-bold">{{ $this->formatPrice($item['new_price']) }}</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                        @if(isset($item['discount']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                                -{{ $item['discount'] }}%
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-500 text-center py-4">Nenhum dado disponível.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL – DICAS
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
                    <h3 class="text-base font-extrabold text-white">Dicas de Promoções</h3>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php $tips = [
                    ['icon'=>'bi-lightning-fill','color'=>'amber','title'=>'Oferta Relâmpago','desc'=>'Promoções de curta duração com alta visibilidade. Ideal para aumentar o volume de vendas rapidamente.'],
                    ['icon'=>'bi-sun-fill','color'=>'yellow','title'=>'Oferta do Dia','desc'=>'Aparece na vitrine do ML. Invista em produtos com boa margem e bastante estoque para sustentar a demanda.'],
                    ['icon'=>'bi-graph-up-arrow','color'=>'emerald','title'=>'Monitore as métricas','desc'=>'Analise taxa de conversão e vendas antes/durante/após cada promoção para calcular o ROI real.'],
                    ['icon'=>'bi-box-seam-fill','color'=>'blue','title'=>'Estoque suficiente','desc'=>'Produtos em promoção sem estoque perdem a oferta e prejudicam a reputação. Verifique antes de participar.'],
                    ['icon'=>'bi-tag-fill','color'=>'purple','title'=>'Promo de Marca','desc'=>'Disponível apenas para marcas certificadas no ML. Exige cadastro e aprovação prévia pelo Mercado Livre.'],
                ]; @endphp
                @foreach($tips as $t)
                    <div class="flex gap-3 p-3.5 rounded-xl bg-{{ $t['color'] }}-50 dark:bg-{{ $t['color'] }}-900/10 border border-{{ $t['color'] }}-100 dark:border-{{ $t['color'] }}-800/30">
                        <div class="w-8 h-8 rounded-lg bg-{{ $t['color'] }}-100 dark:bg-{{ $t['color'] }}-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="bi {{ $t['icon'] }} text-{{ $t['color'] }}-600 dark:text-{{ $t['color'] }}-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white mb-0.5">{{ $t['title'] }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $t['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-toast-notifications />
</div>
