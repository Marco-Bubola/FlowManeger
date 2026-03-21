@push('styles')
<style>
.messages-header-bg {
    background: linear-gradient(135deg,
        rgba(255,255,255,.82) 0%,
        rgba(254,243,199,.90) 40%,
        rgba(251,191,36,.50) 100%);
}
/* Bolhas de chat */
.msg-bubble-seller { background: linear-gradient(135deg, #f59e0b, #f97316); color:#fff; border-radius:1rem 1rem 0 1rem; }
.msg-bubble-buyer  { background: #f1f5f9; color:#1e293b; border-radius:1rem 1rem 1rem 0; }
.dark .msg-bubble-buyer { background:#1e293b; color:#e2e8f0; }
.skel-m {background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel-m 1.4s infinite;}
@keyframes skel-m{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
@endpush

<div class="messages-page min-h-screen"
     x-data="{ tipsOpen: @entangle('tipsOpen') }">

    {{-- ============================================================
         HEADER – AMBER / ML
    ============================================================ --}}
    <div class="messages-header-bg border-b border-amber-200/60 dark:border-amber-700/30 px-4 sm:px-6 py-5 mb-5
                dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
        <div class="max-w-4xl mx-auto">

            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-amber-600 transition-colors">Início</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('mercadolivre.products') }}" class="hover:text-amber-600 transition-colors">Mercado Livre</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-amber-700 dark:text-amber-400 font-semibold">Mensagens</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative w-14 h-14 flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500
                                    flex items-center justify-center shadow-lg shadow-amber-400/40">
                            <i class="bi bi-envelope-fill text-2xl text-white"></i>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-gradient-to-br from-yellow-300 to-amber-500
                                    flex items-center justify-center shadow ring-2 ring-white dark:ring-slate-900">
                            <span class="text-white font-black text-[9px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight
                                   bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700
                                   dark:from-white dark:via-amber-400 dark:to-yellow-300
                                   bg-clip-text text-transparent leading-tight">
                            Mensagens ML
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Comunicação pós-venda com compradores
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="tipsOpen = true"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl
                                   bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-lightbulb-fill text-amber-500"></i> Dicas
                    </button>
                </div>
            </div>

            {{-- Busca por Pack/Order ID --}}
            <div class="mt-4 flex gap-2">
                <div class="flex-1 relative">
                    <i class="bi bi-hash absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text"
                           wire:model="packIdInput"
                           wire:keydown.enter="searchPack"
                           placeholder="Pack ID ou Order ID do pedido…"
                           class="pl-8 pr-3 py-2.5 text-sm rounded-xl w-full bg-white/80 dark:bg-slate-800
                                  border border-amber-200/80 dark:border-slate-600
                                  focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20
                                  text-slate-800 dark:text-white placeholder-slate-400 transition-all">
                </div>
                <button wire:click="searchPack" wire:loading.attr="disabled"
                        class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500
                               text-white text-sm font-bold shadow hover:shadow-md transition-all
                               disabled:opacity-60">
                    <span wire:loading.remove wire:target="searchPack"><i class="bi bi-search"></i> Buscar</span>
                    <span wire:loading wire:target="searchPack"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                </button>
            </div>

        </div>
    </div>
    {{-- /HEADER --}}

    <div class="max-w-4xl mx-auto px-4 sm:px-6 pb-8">

        @if(empty($packId))
            {{-- Estado inicial --}}
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-4">
                    <i class="bi bi-envelope text-4xl text-amber-300 dark:text-amber-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Nenhuma conversa selecionada</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm">
                    Digite o Pack ID ou Order ID de um pedido para carregar as mensagens da conversa.
                </p>
            </div>

        @elseif($loading)
            {{-- Skeleton chat --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="skel-m h-4 w-40 rounded-lg"></div>
                </div>
                <div class="p-5 space-y-4 min-h-64">
                    @for($i=0;$i<4;$i++)
                        <div class="{{ $i % 2 === 0 ? 'flex justify-end' : 'flex justify-start' }}">
                            <div class="skel-m h-10 rounded-xl {{ $i % 2 === 0 ? 'w-48' : 'w-64' }}"></div>
                        </div>
                    @endfor
                </div>
            </div>

        @else
            {{-- Chat window --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">

                {{-- Chat header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800
                            bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800 dark:to-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500
                                    flex items-center justify-center shadow">
                            <i class="bi bi-chat-dots-fill text-white text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 dark:text-white text-sm">Pack / Order #{{ $packId }}</p>
                            <p class="text-xs text-slate-500">{{ count($messages) }} mensagem(ns)</p>
                        </div>
                    </div>
                    <button wire:click="loadMessages"
                            class="p-2 rounded-xl bg-white/80 dark:bg-slate-700 border border-amber-200 dark:border-slate-600
                                   text-amber-600 dark:text-amber-400 hover:bg-amber-50 transition-all">
                        <i class="bi bi-arrow-clockwise text-sm"></i>
                    </button>
                </div>

                {{-- Mensagens --}}
                <div class="flex-1 p-5 space-y-4 overflow-y-auto min-h-80 max-h-[50vh]"
                     id="messages-container">

                    @if(count($messages) === 0)
                        <div class="flex flex-col items-center justify-center h-full py-10 text-center">
                            <i class="bi bi-chat text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                            <p class="text-sm text-slate-400">Nenhuma mensagem nesta conversa.</p>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php
                                $isMine = ($msg['from']['user_id'] ?? 0) != ($msg['to'][0]['user_id'] ?? null);
                                // Seller messages = 'isMine', buyer messages = not isMine
                                // Actually let's determine by message_type or check if from is seller
                                $isSellerMsg = ($msg['message_type'] ?? '') === 'seller_message'
                                    || !empty($msg['from']['seller']);
                            @endphp
                            <div class="flex {{ $isSellerMsg ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs sm:max-w-sm lg:max-w-md">
                                    @if(!$isSellerMsg && !empty($msg['from']['nickname']))
                                        <p class="text-[10px] text-slate-400 mb-1 ml-1">{{ $msg['from']['nickname'] }}</p>
                                    @endif
                                    <div class="{{ $isSellerMsg ? 'msg-bubble-seller' : 'msg-bubble-buyer' }} px-4 py-2.5 shadow-sm">
                                        <p class="text-sm leading-relaxed">{{ $msg['text'] ?? '' }}</p>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1 {{ $isSellerMsg ? 'text-right mr-1' : 'ml-1' }}">
                                        {{ $this->formatDate($msg['message_date']['created'] ?? $msg['date_created'] ?? '') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Caixa de envio --}}
                <div class="border-t border-slate-100 dark:border-slate-800 px-5 py-4 bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <textarea wire:model="newMessage"
                                      wire:keydown.ctrl.enter="sendMessage"
                                      rows="2"
                                      placeholder="Digite sua mensagem… (Ctrl+Enter para enviar)"
                                      class="w-full px-3 py-2 text-sm rounded-xl bg-white dark:bg-slate-800
                                             border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white
                                             focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all resize-none"
                                      maxlength="2000"></textarea>
                            @error('newMessage')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button wire:click="sendMessage" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white
                                       font-bold text-sm shadow hover:shadow-md transition-all flex-shrink-0
                                       disabled:opacity-60 self-end"
                                title="Enviar (Ctrl+Enter)">
                            <span wire:loading.remove wire:target="sendMessage"><i class="bi bi-send-fill text-base"></i></span>
                            <span wire:loading wire:target="sendMessage"><i class="bi bi-arrow-repeat animate-spin text-base"></i></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">
                        <i class="bi bi-shield-check text-emerald-500 mr-0.5"></i>
                        Não compartilhe dados pessoais (telefone, e-mail) – viola as políticas do ML.
                    </p>
                </div>

            </div>
        @endif

    </div>
    {{-- /max-w --}}

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
                    <h3 class="text-base font-extrabold text-white">Dicas de Mensagens ML</h3>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php $tips = [
                    ['icon'=>'bi-hash','color'=>'amber','title'=>'Use o Pack ID','desc'=>'O Pack ID agrupa pedidos de uma mesma compra. Encontre-o nos detalhes do pedido.'],
                    ['icon'=>'bi-clock-fill','color'=>'blue','title'=>'Responda em até 48h','desc'=>'Mensagens sem resposta deterioram a experiência e a reputação na plataforma.'],
                    ['icon'=>'bi-shield-check-fill','color'=>'emerald','title'=>'Dados pessoais são proibidos','desc'=>'Não compartilhe telefone, e-mail ou WhatsApp – viola as políticas do ML e pode causar suspensão.'],
                    ['icon'=>'bi-emoji-smile','color'=>'yellow','title'=>'Seja cordial','desc'=>'Respostas amigáveis e empáticas resultam em avaliações positivas do comprador.'],
                    ['icon'=>'bi-ctrl','color'=>'sky','title'=>'Atalho de envio','desc'=>'Use Ctrl+Enter para enviar a mensagem rapidamente, sem sair do teclado.'],
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

{{-- Auto-scroll para o fim da conversa --}}
<script>
    document.addEventListener('livewire:updated', () => {
        const el = document.getElementById('messages-container');
        if (el) el.scrollTop = el.scrollHeight;
    });
</script>
