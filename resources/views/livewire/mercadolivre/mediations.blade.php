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

    <x-loading-overlay message="Carregando mediações..." />

    {{-- Cabeçalho, status e filtros no padrão das outras listas --}}
    @php
        $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
        $dStatus = ['opened' => ['Abertas', 'bi-exclamation-circle'], 'closed' => ['Encerradas', 'bi-check-circle'], 'all' => ['Todas', 'bi-grid']];
    @endphp
    <div class="px-4 sm:px-6 lg:px-8">
        <x-page-header title="Mediações" icon="bi-shield-exclamation" section="mercadolivre" tabs-section="mercadolivre" tabs-active="mediacoes"
                       subtitle="Reclamações, devoluções e disputas no Mercado Livre">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-shield-exclamation"></i>{{ $total }} {{ $total == 1 ? 'mediação' : 'mediações' }}</span>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><i class="bi bi-flag"></i>{{ $dStatus[$statusFilter][0] ?? 'Todas' }}</span>
            </x-slot:meta>
            <x-slot:actions>
                <button type="button" @click="showFiltersModal = true" title="Filtros" class="{{ $phBtn }}"><i class="bi bi-sliders text-amber-500"></i><span class="hidden sm:inline">Filtros</span></button>
                <button type="button" @click="tipsOpen = true" title="Dicas" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-lightbulb text-amber-500"></i><span class="hidden sm:inline">Dicas</span></button>
                <button type="button" wire:click="loadClaims" wire:loading.attr="disabled" title="Atualizar mediações"
                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap">
                    <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadClaims"></i><span class="hidden sm:inline">Atualizar</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        <x-list-toolbar model="">
            <x-toolbar.group icon="bi-flag">
                @foreach($dStatus as $val => [$label, $icon])
                    <x-toolbar.chip wire:click="$set('statusFilter', '{{ $val }}')" :active="$statusFilter === $val" :icon="$icon">{{ $label }}@if($statusFilter === $val && $total > 0) ({{ $total }})@endif</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
        </x-list-toolbar>
    </div>

    <x-filter-sheet show="showFiltersModal" title="Filtros de mediações" subtitle="Situação da reclamação" :active="$statusFilter !== 'opened'" target="statusFilter,clearFilters">
        <div class="fm-fs-group">
            <h4><i class="bi bi-flag text-amber-500"></i>Situação</h4>
            <div class="fm-fs-options">
                @foreach($dStatus as $val => [$label, $icon])
                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')" class="fm-fs-opt {{ $statusFilter === $val ? 'is-on' : '' }}"><i class="bi {{ $icon }}"></i>{{ $label }}</button>
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

    <div class="w-full px-4 sm:px-6 lg:px-8 pb-10">

        @if($errorMessage)
            <x-empty-state icon="bi-exclamation-triangle" tone="rose" title="Não foi possível carregar as mediações" :text="$errorMessage" class="mb-6">
                <button type="button" wire:click="loadClaims" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition">
                    <i class="bi bi-arrow-clockwise"></i> Tentar novamente
                </button>
                <a href="{{ route('mercadolivre.settings') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 transition">
                    <i class="bi bi-gear"></i> Configurações
                </a>
            </x-empty-state>
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
            @unless($errorMessage)
                @php $dCalm = $statusFilter === 'opened'; @endphp
                <x-empty-state :icon="$dCalm ? 'bi-shield-check' : 'bi-shield'" tone="emerald"
                               :title="$dCalm ? 'Tudo tranquilo!' : 'Nenhuma mediação encontrada'"
                               :text="$dCalm ? 'Excelente! Você não tem disputas abertas no momento. Continue oferecendo uma ótima experiência de compra.' : 'Sem registros de mediação para o filtro selecionado. Tente outro filtro.'">
                    @if($dCalm)
                        <span class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-700/30">
                            <i class="bi bi-trophy-fill mr-1.5"></i>Vendedor em conformidade
                        </span>
                    @else
                        <button type="button" wire:click="$set('statusFilter', 'opened')"
                                class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition">
                            <i class="bi bi-funnel"></i> Ver abertas
                        </button>
                    @endif
                </x-empty-state>
            @endunless
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
                                    @php $fromSeller = ($msg['sender_role'] ?? '') === 'respondent'; @endphp
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
