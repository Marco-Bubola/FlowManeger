{{-- Enviar promoção: imagem (com tema e opções) + mensagem + clientes --}}
@php
    $share = $this->shareData;
    $single = count($shareIds) === 1;
@endphp
<div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('showShareModal', false)">
    <div class="w-full sm:max-w-5xl max-h-[95vh] flex flex-col bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden"
         x-data="promoShare(@js($share['cards']))" x-init="render()">

        {{-- Cabeçalho --}}
        <div class="px-5 pt-4 pb-3 bg-gradient-to-r from-green-500/10 via-emerald-500/10 to-rose-500/10 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/30 shrink-0">
                    <i class="bi bi-whatsapp text-white text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white leading-tight">{{ $single ? 'Enviar promoção' : 'Ofertas da semana' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $single ? ucwords(mb_strtolower($share['promotions']->first()?->product?->name ?? '')) : count($shareIds) . ' produtos numa imagem e numa mensagem só' }}</p>
                </div>
                <button type="button" wire:click="$set('showShareModal', false)" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
            </div>
            {{-- Abas só no celular/tablet; no computador as duas colunas aparecem juntas --}}
            <div class="mt-3 grid grid-cols-2 gap-1 p-1 rounded-2xl bg-white/70 dark:bg-slate-900/60 lg:hidden">
                <button type="button" @click="pane = 'img'" :class="pane === 'img' ? 'bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow' : 'text-slate-600 dark:text-slate-300'" class="py-2 rounded-xl text-sm font-semibold"><i class="bi bi-image"></i> Imagem</button>
                <button type="button" @click="pane = 'msg'" :class="pane === 'msg' ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow' : 'text-slate-600 dark:text-slate-300'" class="py-2 rounded-xl text-sm font-semibold"><i class="bi bi-chat-text"></i> Mensagem</button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-5 grid lg:grid-cols-2 gap-5">

            {{-- ===== IMAGEM ===== --}}
            <div class="space-y-3" :class="pane === 'img' ? '' : 'hidden lg:block'">
                <div class="share-preview">
                    <canvas x-ref="canvas" class="max-h-[380px] w-auto max-w-full rounded-xl shadow-lg"></canvas>
                </div>

                <div class="set-card space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="share-label"><i class="bi bi-aspect-ratio"></i> Formato</span>
                        <div class="flex gap-1 p-1 rounded-xl bg-white dark:bg-slate-900">
                            <button type="button" @click="format = 'square'; render()" :class="format === 'square' ? 'share-seg-on' : ''" class="share-seg"><i class="bi bi-square"></i> Post</button>
                            <button type="button" @click="format = 'story'; render()" :class="format === 'story' ? 'share-seg-on' : ''" class="share-seg"><i class="bi bi-phone"></i> Status</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="share-label"><i class="bi bi-palette"></i> Cores</span>
                        <div class="flex gap-1.5">
                            <template x-for="(th, key) in themes" :key="key">
                                <button type="button" @click="theme = key; render()" :title="th.name"
                                        class="share-swatch" :class="theme === key ? 'on' : ''"
                                        :style="'background: linear-gradient(135deg,' + th.band + ',' + th.accent + ')'"></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="share-label mb-1.5"><i class="bi bi-type"></i> Título</span>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="label in titles" :key="label">
                                <button type="button" @click="title = label; render()" class="promo-chip" :class="title === label ? 'promo-chip-active' : ''" x-text="label"></button>
                            </template>
                            <input type="text" maxlength="24" placeholder="Outro…" x-model="customTitle" @input.debounce.300ms="title = customTitle.toUpperCase(); render()"
                                   class="w-28 px-2.5 py-1 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="opt in [['showBadge','Selo %','bi-percent'],['showSavings','Economia','bi-piggy-bank'],['showValidity','Validade','bi-calendar-event']]" :key="opt[0]">
                            <button type="button" @click="$data[opt[0]] = !$data[opt[0]]; render()" class="set-option !min-h-0 !py-2" :class="$data[opt[0]] ? 'set-option-on' : ''">
                                <i class="bi" :class="opt[2]"></i><span class="text-[11px] font-semibold" x-text="opt[1]"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                    <button type="button" @click="download()" class="share-btn-soft"><i class="bi bi-download"></i> Baixar imagem</button>
                    <button type="button" @click="copyImage()" class="share-btn-soft"><i class="bi bi-images"></i> Copiar imagem</button>
                </div>
            </div>

            {{-- ===== MENSAGEM ===== --}}
            <div class="space-y-3" :class="pane === 'msg' ? '' : 'hidden lg:block'">
                <div>
                    <span class="share-label mb-1"><i class="bi bi-person"></i> Para</span>
                    <select wire:change="setShareClient($event.target.value)" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200">
                        <option value="">Qualquer contato, status ou grupo</option>
                        @foreach($share['allClients'] as $c)
                            <option value="{{ $c->id }}" @selected($shareClientId === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($single)
                    <div>
                        <span class="share-label mb-1.5"><i class="bi bi-magic"></i> Modelo da mensagem</span>
                        <div class="flex gap-1.5 overflow-x-auto pb-1 share-scroll">
                            <button type="button" wire:click="setShareTemplate('')" class="promo-chip {{ $shareTemplate === '' ? 'promo-chip-active' : '' }}"><i class="bi bi-star"></i> Meu padrão</button>
                            @foreach(\App\Models\PromotionSetting::TEMPLATES as $key => [$label, $icon])
                                <button type="button" wire:click="setShareTemplate('{{ $key }}')" class="promo-chip {{ $shareTemplate === $key ? 'promo-chip-active' : '' }}"><i class="bi {{ $icon }}"></i> {{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <span class="share-label mb-1"><i class="bi bi-chat-text"></i> Mensagem <small class="font-normal text-slate-400">(pode editar)</small></span>
                    <textarea wire:model="shareText" x-ref="text" rows="8" class="set-textarea"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-bold">
                    <button type="button" @click="shareAll()" class="share-btn share-btn-main"><i class="bi bi-send-fill"></i> Enviar foto + texto</button>
                    <button type="button" @click="openWhatsapp({{ $shareClientId ?? 'null' }})" class="share-btn share-btn-wa"><i class="bi bi-whatsapp"></i> Abrir WhatsApp</button>
                    <button type="button" @click="copyText()" class="share-btn-soft"><i class="bi bi-clipboard"></i> Copiar texto</button>
                </div>
                <div class="flex gap-2 p-2.5 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 text-[11.5px] text-green-800 dark:text-green-200">
                    <i class="bi bi-lightbulb-fill mt-0.5"></i>
                    <span><b>Celular:</b> "Enviar foto + texto" abre a lista de apps; escolha o WhatsApp e a foto vai junto.<br>
                    <b>Computador:</b> o WhatsApp só aceita texto pelo link. A foto é copiada sozinha: na conversa, aperte <b>Ctrl+V</b> para colar e envie.</span>
                </div>

                @if($share['clients']->count())
                    <div class="pt-1">
                        <span class="share-label mb-1.5"><i class="bi bi-people"></i> Clientes que podem gostar</span>
                        <div class="space-y-1.5 max-h-60 overflow-y-auto share-scroll">
                            @foreach($share['clients'] as $row)
                                @php $name = $row['client']->name; $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join(''); @endphp
                                <div class="flex items-center gap-2.5 p-2 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-rose-400 to-orange-400 text-white text-xs font-black flex items-center justify-center shrink-0">{{ $initials }}</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $name }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">
                                            {{ $row['reason'] }}
                                            @if($row['last_send']) · <span class="text-green-600">enviado em {{ $row['last_send']->created_at->format('d/m') }}</span> @endif
                                        </div>
                                    </div>
                                    <button type="button" @click="openWhatsapp({{ $row['client']->id }})"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $row['last_send'] ? 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-500 hover:bg-green-600 text-white' }}">
                                        <i class="bi bi-whatsapp"></i> {{ $row['last_send'] ? 'Reenviar' : 'Enviar' }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .share-preview { display: flex; justify-content: center; align-items: center; padding: .9rem; border-radius: 1.25rem;
        background: #efeae2 radial-gradient(rgba(0,0,0,.05) 1px, transparent 1px) 0 0 / 14px 14px; }
    .dark .share-preview { background-color: #0b141a; }
    .share-label { display: flex; align-items: center; gap: .35rem; font-size: .8rem; font-weight: 700; color: rgb(51 65 85); }
    .dark .share-label { color: rgb(226 232 240); }
    .share-seg { padding: .35rem .7rem; border-radius: .6rem; font-size: .75rem; font-weight: 700; color: rgb(100 116 139); }
    .share-seg-on { background: linear-gradient(135deg, #f43f5e, #f97316); color: #fff; box-shadow: 0 2px 8px rgba(244,63,94,.3); }
    .share-swatch { width: 1.9rem; height: 1.9rem; border-radius: 999px; border: 3px solid #fff; box-shadow: 0 0 0 1px rgba(148,163,184,.5); transition: transform .12s; }
    .share-swatch.on { transform: scale(1.12); box-shadow: 0 0 0 2px #f43f5e; }
    .share-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .75rem; border-radius: .9rem; color: #fff; transition: all .15s; }
    .share-btn:active { transform: scale(.97); }
    .share-btn-main { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 6px 16px rgba(34,197,94,.3); }
    .share-btn-wa { background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 6px 16px rgba(5,150,105,.3); }
    .share-btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .7rem; border-radius: .9rem;
        border: 1px solid rgba(203,213,225,.9); color: rgb(51 65 85); background: #fff; font-weight: 700; }
    .share-btn-soft:hover { border-color: rgba(244,63,94,.5); color: rgb(225 29 72); }
    .dark .share-btn-soft { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .share-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
    .share-scroll::-webkit-scrollbar-thumb { background: rgba(244,63,94,.3); border-radius: 8px; }
</style>
