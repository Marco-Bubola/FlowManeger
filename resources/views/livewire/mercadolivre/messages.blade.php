@push('styles')
<style>
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

    <x-loading-overlay message="Carregando mensagens..." />

    {{-- Cabeçalho e busca da conversa no padrão das outras listas --}}
    @php
        $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    @endphp
    <div class="px-4 sm:px-6 lg:px-8">
        <x-page-header title="Mensagens" icon="bi-envelope" section="mercadolivre" tabs-section="mercadolivre" tabs-active="mensagens"
                       subtitle="Comunicação pós-venda com compradores">
            @if(!empty($packId))
                <x-slot:meta>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-hash"></i>{{ $packId }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><i class="bi bi-chat-dots"></i>{{ count($messages) }} mensagem(ns)</span>
                </x-slot:meta>
            @endif
            <x-slot:actions>
                <button type="button" @click="tipsOpen = true" title="Dicas" class="{{ $phBtn }}"><i class="bi bi-lightbulb text-amber-500"></i><span class="hidden sm:inline">Dicas</span></button>
                @if(!empty($packId))
                    <button type="button" wire:click="loadMessages" wire:loading.attr="disabled" title="Atualizar conversa"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap">
                        <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadMessages"></i><span class="hidden sm:inline">Atualizar</span>
                    </button>
                @endif
            </x-slot:actions>
        </x-page-header>

        {{-- Busca por Pack/Order ID (mesmo visual da x-list-toolbar; a busca roda no Enter ou no botão) --}}
        <div class="app-toolbar mb-4 flex items-center gap-2 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 shadow-sm">
            <div class="relative flex-1 min-w-0">
                <i class="bi bi-hash absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" wire:model="packIdInput" wire:keydown.enter="searchPack" placeholder="Pack ID ou Order ID do pedido..."
                       class="app-toolbar-input w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2.5 pl-10 pr-3 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <button type="button" wire:click="searchPack" wire:loading.attr="disabled" title="Buscar conversa"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-sm transition disabled:opacity-60">
                <i class="bi bi-search" wire:loading.remove wire:target="searchPack"></i>
                <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="searchPack"></i>
                <span class="hidden sm:inline">Buscar</span>
            </button>
        </div>
    </div>


    <div class="w-full px-4 sm:px-6 lg:px-8 pb-8">

        @if(empty($packId))
            {{-- Estado inicial --}}
            <x-empty-state icon="bi-envelope" tone="amber" title="Nenhuma conversa selecionada"
                           text="Digite o Pack ID ou o Order ID de um pedido na busca acima para carregar as mensagens da conversa com o comprador.">
                <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-700/30">
                    <i class="bi bi-lightbulb-fill mr-1"></i> Encontre o ID em Pedidos, no botão Ver detalhes
                </span>
                <a href="{{ route('mercadolivre.orders') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 transition">
                    <i class="bi bi-bag-check"></i> Ir para Pedidos
                </a>
            </x-empty-state>

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
                        <x-empty-state icon="bi-chat" tone="amber" title="Nenhuma mensagem nesta conversa" text="Envie a primeira mensagem para o comprador logo abaixo." class="border-0 bg-transparent dark:bg-transparent" />
                    @else
                        @foreach($messages as $msg)
                            @php
                                // Mensagem é do vendedor quando from.user_id == ml_user_id do vendedor logado
                                $isSellerMsg = !empty($sellerMlUserId)
                                    && (int)($msg['from']['user_id'] ?? 0) === (int)$sellerMlUserId;
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
