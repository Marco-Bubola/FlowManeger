@push('styles')
<style>
.q-card { animation: q-fadein .3s ease both; }
@keyframes q-fadein { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.skel-q { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
          background-size:200% 100%;animation:skel-q 1.4s infinite; }
@keyframes skel-q {0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
@endpush

<div class="questions-page min-h-screen"
     x-data="{
         showFiltersModal: @entangle('showFiltersModal'),
         tipsOpen: false,
         showAnswerModal: @entangle('showAnswerModal'),
     }">

    <x-loading-overlay message="Carregando perguntas..." />

    {{-- ============================================================
         HEADER – AMBER / ML
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
                <span class="text-amber-700 dark:text-amber-400 font-semibold">Perguntas</span>
            </nav>

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

                <div class="flex items-start gap-5">
                    <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-2xl shadow-xl shadow-amber-500/30 flex-shrink-0">
                        <i class="bi bi-chat-dots-fill text-white text-3xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50 pointer-events-none"></div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center shadow-md ring-2 ring-amber-100 dark:ring-slate-700">
                            <span class="text-amber-600 dark:text-amber-400 font-black text-[8px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-orange-600 dark:from-amber-200 dark:via-amber-300 dark:to-yellow-300 bg-clip-text text-transparent leading-tight">
                            Perguntas do ML
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Responda compradores e aumente sua reputação
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text"
                               wire:model.live.debounce.400ms="itemIdFilter"
                               placeholder="Filtrar por ID do anúncio…"
                               class="pl-8 pr-3 py-2 text-sm rounded-xl bg-white/80 dark:bg-slate-800
                                      border border-amber-200/80 dark:border-slate-600
                                      focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20
                                      text-slate-800 dark:text-white placeholder-slate-400 transition-all w-52">
                    </div>
                    <button @click="tipsOpen = true"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl
                                   bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-lightbulb-fill text-amber-500"></i> Dicas
                    </button>
                    <button wire:click="loadQuestions" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-bold
                                   shadow hover:shadow-md hover:from-amber-600 hover:to-orange-600 transition-all">
                        <span wire:loading.remove wire:target="loadQuestions"><i class="bi bi-arrow-clockwise"></i> Atualizar</span>
                        <span wire:loading wire:target="loadQuestions"><i class="bi bi-arrow-repeat animate-spin"></i> Carregando…</span>
                    </button>
                </div>
            </div>

            {{-- Status pills --}}
            <div class="flex flex-wrap items-center gap-2 mt-5 pt-4 border-t border-amber-100/60 dark:border-amber-900/20">
                <button wire:click="$set('statusFilter', 'UNANSWERED')"
                        class="px-3 py-1 rounded-full text-xs font-bold border transition-all
                               {{ $statusFilter === 'UNANSWERED' ? 'bg-amber-500 border-amber-500 text-white shadow' : 'bg-white/60 dark:bg-slate-800 border-amber-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-amber-400' }}">
                    <i class="bi bi-question-circle-fill mr-1"></i> Não respondidas
                </button>
                <button wire:click="$set('statusFilter', 'ANSWERED')"
                        class="px-3 py-1 rounded-full text-xs font-bold border transition-all
                               {{ $statusFilter === 'ANSWERED' ? 'bg-emerald-500 border-emerald-500 text-white shadow' : 'bg-white/60 dark:bg-slate-800 border-emerald-200 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 hover:border-emerald-400' }}">
                    <i class="bi bi-check-circle-fill mr-1"></i> Respondidas
                </button>
                <button wire:click="$set('statusFilter', '')"
                        class="px-3 py-1 rounded-full text-xs font-bold border transition-all
                               {{ $statusFilter === '' ? 'bg-slate-700 border-slate-700 text-white shadow' : 'bg-white/60 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-slate-400' }}">
                    Todas
                </button>

                <div class="flex-1"></div>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $total }} pergunta(s)
                </span>
            </div>

            {{-- Stats mini --}}
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="bg-white/70 dark:bg-slate-800/80 border border-amber-100 dark:border-slate-700
                            rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i class="bi bi-chat-dots-fill text-amber-600 dark:text-amber-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-slate-800 dark:text-white leading-none">{{ $total }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Total</p>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-slate-800/80 border border-red-100 dark:border-slate-700
                            rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="bi bi-question-circle-fill text-red-600 dark:text-red-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-red-600 dark:text-red-400 leading-none">
                            {{ count(array_filter($questions, fn($q) => ($q['status'] ?? '') === 'UNANSWERED')) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">Pendentes</p>
                    </div>
                </div>
                <div class="bg-white/70 dark:bg-slate-800/80 border border-emerald-100 dark:border-slate-700
                            rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">
                            {{ count(array_filter($questions, fn($q) => ($q['status'] ?? '') === 'ANSWERED')) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">Respondidas</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- /HEADER --}}

    <div class="w-full px-4 sm:px-6 lg:px-8 pb-8">

        {{-- LOADING SKELETONS --}}
        @if($loading)
            <div class="space-y-3">
                @for($i=0;$i<6;$i++)
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5 space-y-3">
                        <div class="flex justify-between">
                            <div class="skel-q h-4 w-40 rounded-lg"></div>
                            <div class="skel-q h-5 w-24 rounded-full"></div>
                        </div>
                        <div class="skel-q h-3 w-full rounded-lg"></div>
                        <div class="skel-q h-3 w-3/4 rounded-lg"></div>
                    </div>
                @endfor
            </div>

        {{-- EMPTY --}}
        @elseif(count($questions) === 0)
            <div class="flex flex-col items-center justify-center py-16 sm:py-24">
                <div class="relative mb-6">
                    <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-amber-400/20 via-yellow-300/15 to-orange-300/10
                                dark:from-amber-900/30 dark:via-amber-800/20 dark:to-orange-900/10
                                border border-amber-200/60 dark:border-amber-700/30
                                flex items-center justify-center shadow-xl">
                        <i class="bi bi-chat-dots-fill text-5xl text-amber-400 dark:text-amber-500"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500
                                flex items-center justify-center shadow-lg">
                        <i class="bi bi-{{ $statusFilter === 'UNANSWERED' ? 'check-lg' : 'search' }} text-white text-xs"></i>
                    </div>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-white mb-2 text-center">
                    {{ $statusFilter === 'UNANSWERED' ? 'Tudo em dia!' : 'Nenhuma pergunta encontrada' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 max-w-sm text-center leading-relaxed">
                    {{ $statusFilter === 'UNANSWERED'
                        ? 'Parabéns! Você respondeu todas as perguntas dos compradores. Continue assim para manter sua reputação.'
                        : 'Nenhuma pergunta encontrada para o filtro atual. Tente outro status ou limpe os filtros.' }}
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    @if($itemIdFilter || $statusFilter !== 'UNANSWERED')
                        <button wire:click="clearFilters"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl
                                       bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-sm
                                       shadow-lg shadow-amber-500/25 hover:shadow-xl hover:from-amber-600 hover:to-orange-600
                                       transition-all duration-200 active:scale-95">
                            <i class="bi bi-x-circle-fill"></i> Limpar Filtros
                        </button>
                    @endif
                    <button wire:click="loadQuestions"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl
                                   bg-white dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-slate-700 dark:text-slate-300 font-semibold text-sm
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all duration-200">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                    </button>
                </div>
            </div>

        {{-- QUESTIONS LIST --}}
        @else
            <div class="space-y-3">
                @foreach($questions as $idx => $q)
                    @php
                        $isUnanswered = ($q['status'] ?? '') === 'UNANSWERED';
                        $answer       = $q['answer'] ?? null;
                    @endphp
                    <div class="q-card bg-white dark:bg-slate-900 rounded-2xl border
                                {{ $isUnanswered ? 'border-amber-200 dark:border-amber-700/40' : 'border-slate-100 dark:border-slate-800' }}
                                shadow-sm overflow-hidden"
                         style="animation-delay:{{ $idx * 30 }}ms">

                        <div class="p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                                {{-- Avatar comprador --}}
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500
                                            flex items-center justify-center flex-shrink-0 shadow">
                                    <span class="text-white font-black text-sm">
                                        {{ mb_strtoupper(mb_substr($q['from']['nickname'] ?? 'U', 0, 1)) }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    {{-- Header da pergunta --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="font-bold text-slate-800 dark:text-white text-sm">
                                            {{ $q['from']['nickname'] ?? 'Comprador' }}
                                        </span>
                                        @if(!empty($q['item_id']))
                                            <span class="text-xs font-mono text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20
                                                         border border-amber-200 dark:border-amber-700/30 px-2 py-0.5 rounded">
                                                {{ $q['item_id'] }}
                                            </span>
                                        @endif
                                        <span class="ml-auto text-[10px] text-slate-400">
                                            {{ $this->formatDate($q['date_created'] ?? '') }}
                                        </span>
                                    </div>

                                    {{-- Texto da pergunta --}}
                                    <p class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed">
                                        {{ $q['text'] ?? '—' }}
                                    </p>

                                    {{-- Resposta (se houver) --}}
                                    @if($answer)
                                        <div class="mt-3 ml-3 pl-3 border-l-2 border-emerald-400 dark:border-emerald-600">
                                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                                                <i class="bi bi-reply-fill mr-1"></i> Sua resposta
                                            </p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                                {{ $answer['text'] ?? '—' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                {{ $this->formatDate($answer['date_created'] ?? '') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Ações --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($isUnanswered)
                                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400
                                                     border border-amber-200 dark:border-amber-700/40 px-2 py-1 rounded-full uppercase">
                                            Pendente
                                        </span>
                                        <button wire:click="openAnswer({{ json_encode($q) }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold
                                                       bg-gradient-to-r from-amber-500 to-orange-500 text-white
                                                       hover:shadow hover:from-amber-600 hover:to-orange-600 transition-all">
                                            <i class="bi bi-reply-fill"></i> Responder
                                        </button>
                                    @else
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400
                                                     border border-emerald-200 dark:border-emerald-700/40 px-2 py-1 rounded-full uppercase">
                                            Respondida
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($total > $perPage)
                <div class="flex items-center justify-between mt-6 px-2">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Mostrando {{ $offset + 1 }}–{{ min($offset + $perPage, $total) }} de {{ $total }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button wire:click="prevPage" @disabled($offset === 0)
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-900
                                       border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300
                                       hover:bg-amber-50 dark:hover:bg-slate-800 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </button>
                        <button wire:click="nextPage" @disabled($offset + $perPage >= $total)
                                class="px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-900
                                       border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300
                                       hover:bg-amber-50 dark:hover:bg-slate-800 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            Próxima <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            @endif
        @endif

    </div>
    {{-- /max-w --}}

    {{-- ============================================================
         MODAL – RESPONDER PERGUNTA
    ============================================================ --}}
    <div x-show="showAnswerModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="$wire.closeAnswerModal()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-end sm:items-center justify-center p-4"
         style="display:none">
        <div x-show="showAnswerModal"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
             @click.stop
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg
                    border border-amber-100 dark:border-amber-700/30 overflow-hidden"
             style="display:none">

            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500">
                <div class="flex items-center gap-3">
                    <i class="bi bi-reply-fill text-white text-lg"></i>
                    <h3 class="text-base font-extrabold text-white">Responder Pergunta</h3>
                </div>
                <button wire:click="closeAnswerModal"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="p-6">
                @if($selectedQuestion)
                    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl p-4 mb-4">
                        <p class="text-xs text-amber-700 dark:text-amber-400 font-bold mb-1">
                            <i class="bi bi-person-fill mr-1"></i>{{ $selectedQuestion['from']['nickname'] ?? 'Comprador' }}
                        </p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $selectedQuestion['text'] ?? '' }}</p>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Sua Resposta *</label>
                    <textarea wire:model="answerText"
                              rows="4"
                              placeholder="Digite uma resposta clara e objetiva para o comprador…"
                              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800
                                     border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm
                                     focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all resize-none"
                              maxlength="2000"></textarea>
                    @error('answerText')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-slate-400 text-right mt-1">{{ strlen($answerText) }}/2000</p>
                </div>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button wire:click="closeAnswerModal"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-sm
                               hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                    Cancelar
                </button>
                <button wire:click="sendAnswer" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-extrabold text-sm
                               hover:shadow-lg hover:shadow-amber-400/30 transition-all
                               flex items-center justify-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="sendAnswer"><i class="bi bi-send-fill"></i> Enviar Resposta</span>
                    <span wire:loading wire:target="sendAnswer"><i class="bi bi-arrow-repeat animate-spin"></i> Enviando…</span>
                </button>
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
                    <h3 class="text-base font-extrabold text-white">Dicas para Perguntas</h3>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php $tips = [
                    ['icon'=>'bi-clock-fill','color'=>'amber','title'=>'Responda rapido','desc'=>'Respostas em menos de 24h aumentam sua reputação no ML significativamente.'],
                    ['icon'=>'bi-translate','color'=>'blue','title'=>'Seja objetivo','desc'=>'Respostas curtas e claras são mais valorizadas pelos compradores. Evite textos longos.'],
                    ['icon'=>'bi-star-fill','color'=>'yellow','title'=>'Reputação em jogo','desc'=>'Perguntas sem resposta impactam diretamente sua taxa de conversão e seu termômetro.'],
                    ['icon'=>'bi-shield-check-fill','color'=>'emerald','title'=>'Evite dados pessoais','desc'=>'Não inclua telefone, e-mail ou WhatsApp nas respostas – viola as políticas do ML.'],
                    ['icon'=>'bi-arrow-repeat','color'=>'sky','title'=>'Sincronize com frequência','desc'=>'Atualize a lista regularmente para não deixar nenhuma pergunta sem atenção.'],
                ]; @endphp
                @foreach($tips as $t)
                    <div class="flex gap-3 p-3.5 rounded-xl bg-{{ $t['color'] }}-50 dark:bg-{{ $t['color'] }}-900/10 border border-{{ $t['color'] }}-100 dark:border-{{ $t['color'] }}-800/30">
                        <div class="w-8 h-8 rounded-lg bg-{{ $t['color'] }}-100 dark:bg-{{ $t['color'] }}-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
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
