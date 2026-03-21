@push('styles')
<style>
.skel-d{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel-d 1.4s infinite;}
@keyframes skel-d{0%{background-position:200% 0}100%{background-position:-200% 0}}
.claim-card:hover{transform:translateY(-1px);}
</style>
@endpush

<div class="mediations-page min-h-screen"
     x-data="{
         tipsOpen:        @entangle('tipsOpen'),
         showDetailModal: @entangle('showDetailModal'),
         showFiltersModal:@entangle('showFiltersModal')
     }">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/85 via-amber-50/90 to-yellow-50/80
                dark:from-slate-800/90 dark:via-amber-900/10 dark:to-slate-800/30
                backdrop-blur-xl border border-amber-100/60 dark:border-amber-900/30
                rounded-3xl shadow-2xl mb-6 mx-4 sm:mx-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-52 h-52 bg-gradient-to-br from-amber-400/20 via-yellow-300/15 to-orange-300/10 rounded-full transform translate-x-20 -translate-y-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-gradient-to-tr from-amber-300/15 via-yellow-300/10 to-orange-200/10 rounded-full transform -translate-x-12 translate-y-12 pointer-events-none"></div>
        <div class="relative max-w-6xl mx-auto px-6 sm:px-8 py-6">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
                <a href="{{ route('dashboard') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors flex items-center gap-1">
                    <i class="bi bi-house-fill text-[11px]"></i> Início
                </a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <a href="{{ route('mercadolivre.products') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Mercado Livre</a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <span class="text-amber-700 dark:text-amber-400 font-semibold">Mediações</span>
            </nav>

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-start gap-5">
                    <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-2xl shadow-xl shadow-amber-500/30 flex-shrink-0">
                        <i class="bi bi-shield-exclamation text-white text-3xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50 pointer-events-none"></div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center shadow-md ring-2 ring-amber-100 dark:ring-slate-700">
                            <span class="text-amber-600 dark:text-amber-400 font-black text-[8px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-orange-600 dark:from-amber-200 dark:via-amber-300 dark:to-yellow-300 bg-clip-text text-transparent leading-tight">
                            Mediações & Devoluções
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Reclamações e disputas no Mercado Livre
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button wire:click="loadClaims"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-white/70 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadClaims"></i>
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

            {{-- Status pills --}}
            <div class="flex flex-wrap gap-2 mt-5 pt-4 border-t border-amber-100/60 dark:border-amber-900/20">
                @foreach(['opened' => 'Abertas', 'closed' => 'Encerradas', 'all' => 'Todas'] as $val => $label)
                    <button wire:click="$set('statusFilter', '{{ $val }}')"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-all
                                   {{ $statusFilter === $val
                                      ? 'bg-amber-500 text-white border-amber-500 shadow-sm shadow-amber-300/30'
                                      : 'bg-white/80 dark:bg-slate-800 border-amber-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                        @if($statusFilter === $val && $total > 0)
                            <span class="ml-1 px-1.5 py-0.5 rounded-full bg-white/30 text-white text-[10px]">{{ $total }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-10">

        @if($errorMessage)
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 mb-6">
                <i class="bi bi-exclamation-circle-fill text-red-500 text-xl flex-shrink-0"></i>
                <p class="flex-1 text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                <button wire:click="loadClaims" class="text-sm font-bold text-red-600 hover:text-red-800">Tentar novamente</button>
            </div>
        @endif

        @if($loading)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @for($i=0;$i<6;$i++)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                        <div class="skel-d h-4 w-32 rounded-lg"></div>
                        <div class="skel-d h-3 w-20 rounded"></div>
                        <div class="skel-d h-3 w-full rounded"></div>
                        <div class="skel-d h-3 w-3/4 rounded"></div>
                    </div>
                @endfor
            </div>
        @elseif(count($claims) === 0)
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-4">
                    <i class="bi bi-shield-check text-4xl text-amber-300 dark:text-amber-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Nenhuma mediação encontrada</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $statusFilter === 'opened' ? 'Você não tem disputas abertas — ótimo sinal!' : 'Sem registros para o filtro selecionado.' }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($claims as $claim)
                    @php
                        $status = $this->getStatusBadge($claim['status'] ?? null);
                        $claimId = $claim['id'] ?? null;
                        $orderId = $claim['resource_id'] ?? ($claim['order_id'] ?? '-');
                        $reason  = $claim['reason_id'] ?? ($claim['type'] ?? '-');
                        $stage   = $claim['stage'] ?? '-';
                        $dateOpened = $this->formatDate($claim['date_created'] ?? null);
                    @endphp
                    <div class="claim-card bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm
                                hover:shadow-md transition-all duration-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800
                                    bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/50 dark:to-slate-900">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Claim #{{ $claimId }}</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white truncate mt-0.5">
                                        Pedido: {{ $orderId }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap {{ $status['bg'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4 space-y-2">
                            <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                <i class="bi bi-tag-fill text-amber-500 text-[10px]"></i>
                                <span class="truncate">{{ $reason }}</span>
                            </div>
                            @if($stage !== '-')
                                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    <i class="bi bi-layers-fill text-blue-400 text-[10px]"></i>
                                    <span>Estágio: {{ $stage }}</span>
                                </div>
                            @endif
                            @if($dateOpened)
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-calendar3 text-[10px]"></i>
                                    <span>{{ $dateOpened }}</span>
                                </div>
                            @endif
                        </div>
                        @if($claimId)
                            <div class="px-5 pb-4">
                                <button wire:click="openDetail({{ $claimId }})"
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
                                       text-slate-600 dark:text-slate-400 hover:bg-slate-50 disabled:opacity-40">
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
         MODAL – DETALHE DA MEDIAÇÃO
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

            {{-- Header do modal --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <i class="bi bi-shield-exclamation text-white text-xl"></i>
                    <h3 class="text-base font-extrabold text-white">
                        Mediação #{{ $selectedClaim['id'] ?? '–' }}
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
                        @for($i=0;$i<4;$i++)<div class="skel-d h-4 rounded-lg"></div>@endfor
                    </div>
                @elseif($selectedClaim)
                    @php
                        $sc = $selectedClaim;
                        $scStatus = $this->getStatusBadge($sc['status'] ?? null);
                    @endphp
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-slate-400 mb-0.5">Status</p>
                            <span class="px-2 py-0.5 rounded-full font-bold {{ $scStatus['bg'] }}">{{ $scStatus['label'] }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-slate-400 mb-0.5">Pedido</p>
                            <p class="font-bold text-slate-800 dark:text-white">#{{ $sc['resource_id'] ?? $sc['order_id'] ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-slate-400 mb-0.5">Motivo</p>
                            <p class="font-bold text-slate-800 dark:text-white truncate">{{ $sc['reason_id'] ?? $sc['type'] ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-slate-400 mb-0.5">Estágio</p>
                            <p class="font-bold text-slate-800 dark:text-white">{{ $sc['stage'] ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Mensagens da mediação --}}
                    @if(count($claimMessages) > 0)
                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Mensagens ({{ count($claimMessages) }})</p>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                @foreach($claimMessages as $msg)
                                    @php $fromSeller = ($msg['author']['type'] ?? '') === 'users'; @endphp
                                    <div class="flex {{ $fromSeller ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-xs px-3 py-2 rounded-xl text-xs {{ $fromSeller ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                            <p>{{ $msg['message'] ?? $msg['text'] ?? '' }}</p>
                                            <p class="text-[10px] mt-0.5 opacity-70">{{ $this->formatDate($msg['date_created'] ?? null) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Caixa de resposta --}}
                    @if(($sc['status'] ?? '') === 'opened')
                        <div>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Responder mediação</p>
                            <textarea wire:model="replyText" rows="3"
                                      placeholder="Digite sua mensagem para o mediador…"
                                      class="w-full px-3 py-2 text-sm rounded-xl bg-white dark:bg-slate-800
                                             border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white
                                             focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all resize-none"
                                      maxlength="2000"></textarea>
                            @error('replyText')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <button wire:click="sendReply" wire:loading.attr="disabled"
                                    class="mt-2 w-full py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-bold
                                           shadow hover:shadow-md transition-all disabled:opacity-60">
                                <span wire:loading.remove wire:target="sendReply"><i class="bi bi-send-fill mr-1"></i> Enviar resposta</span>
                                <span wire:loading wire:target="sendReply"><i class="bi bi-arrow-repeat animate-spin mr-1"></i> Enviando…</span>
                            </button>
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
                    <h3 class="text-base font-extrabold text-white">Dicas sobre Mediações</h3>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php $tips = [
                    ['icon'=>'bi-clock-fill','color'=>'amber','title'=>'Responda em 48h','desc'=>'Mediações sem resposta do vendedor são automaticamente decididas em favor do comprador.'],
                    ['icon'=>'bi-chat-text-fill','color'=>'blue','title'=>'Seja objetivo','desc'=>'Forneça evidências concretas: código de rastreio, fotos do produto, comprovantes de envio.'],
                    ['icon'=>'bi-camera-fill','color'=>'emerald','title'=>'Documente tudo','desc'=>'Sempre fotografe os produtos antes de enviar. Isso protege você em casos de devolução por item diferente.'],
                    ['icon'=>'bi-person-fill-check','color'=>'yellow','title'=>'Mediador ML','desc'=>'Se o acordo direto falhar, um mediador do ML intervirá. Seja respeitoso e apresente fatos.'],
                    ['icon'=>'bi-arrow-return-left','color'=>'red','title'=>'Devoluções preventivas','desc'=>'Se o custo de disputar for maior que o produto, avaliar aceitar a devolução diretamente pode ser mais eficiente.'],
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
