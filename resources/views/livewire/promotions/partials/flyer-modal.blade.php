{{-- Folheto: as promoções no ar numa ou mais imagens (status, quadrado, feed). Desenho em promoFlyer (promotions-index @script). --}}
@php $flyer = $this->flyerData; @endphp
<div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('showFlyerModal', false)">
    <div class="flyer-modal w-full sm:max-w-6xl max-h-[96vh] flex flex-col bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden"
         x-data="promoFlyer(@js($flyer))">

        {{-- Cabeçalho --}}
        <div class="flex items-center gap-3 px-4 sm:px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flyer-head">
            <div class="w-11 h-11 rounded-2xl flyer-grad flex items-center justify-center shadow-lg shrink-0"><i class="bi bi-images text-white text-xl"></i></div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white leading-tight">Folheto das promoções</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                    <span x-text="selectedCards.length"></span> de {{ count($flyer['cards']) }} ofertas ·
                    <span x-text="pages.length || 1"></span> <span x-text="(pages.length || 1) === 1 ? 'imagem' : 'imagens'"></span>
                </p>
            </div>
            <button type="button" wire:click="$set('showFlyerModal', false)" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
        </div>

        @if(count($flyer['cards']) === 0)
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl flyer-grad flex items-center justify-center"><i class="bi bi-tags text-white text-2xl"></i></div>
                <p class="font-semibold text-slate-700 dark:text-slate-200">Nenhuma promoção no ar com estoque.</p>
                <p class="text-sm text-slate-500 mt-1">Ponha produtos em promoção para montar o folheto.</p>
                <a href="{{ route('promotions.create') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold flyer-grad"><i class="bi bi-plus-circle"></i> Nova promoção</a>
            </div>
        @else
            <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden lg:overflow-hidden grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)]">

                {{-- ===== PRÉVIA ===== --}}
                <div class="flyer-stage min-w-0 lg:overflow-y-auto p-3 sm:p-5 flex flex-col gap-3">
                    <div class="relative">
                        <div x-ref="strip" class="flyer-strip" @scroll.debounce.80ms="syncPage()">
                            <template x-for="(pg, i) in pages" :key="pg.url">
                                <div class="flyer-slide">
                                    <img :src="pg.url" :alt="'Folheto ' + (i + 1)" class="flyer-img" :class="'is-' + format">
                                </div>
                            </template>
                            <div x-show="!pages.length" class="flyer-slide"><div class="flyer-img is-square flex items-center justify-center text-slate-400 text-sm bg-white/70" x-text="selectedCards.length ? 'Gerando…' : 'Escolha pelo menos uma oferta'"></div></div>
                        </div>
                        <template x-if="pages.length > 1">
                            <div>
                                <button type="button" class="flyer-nav left-1" @click="goTo(current - 1)" :disabled="current === 0" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                                <button type="button" class="flyer-nav right-1" @click="goTo(current + 1)" :disabled="current >= pages.length - 1" aria-label="Próxima"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-center gap-1.5" x-show="pages.length > 1">
                        <template x-for="(pg, i) in pages" :key="'dot' + i">
                            <button type="button" @click="goTo(i)" class="flyer-dot" :class="i === current ? 'on' : ''" :aria-label="'Imagem ' + (i + 1)"></button>
                        </template>
                        <span class="ml-2 text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="(current + 1) + ' / ' + pages.length"></span>
                    </div>
                    <p x-show="tainted" class="text-[11px] text-amber-700 dark:text-amber-300 text-center"><i class="bi bi-exclamation-triangle"></i> Algumas fotos não puderam entrar na imagem e foram trocadas por um desenho.</p>

                    {{-- Ações --}}
                    <div class="flyer-actions grid gap-2" :class="canShare && canCopy ? 'grid-cols-3' : 'grid-cols-2'">
                        <button type="button" @click="download()" :disabled="!pages.length || busy" class="flyer-btn flyer-btn-soft">
                            <i class="bi bi-download"></i><span x-text="pages.length > 1 ? 'Baixar ' + pages.length : 'Baixar'"></span>
                        </button>
                        <button type="button" x-show="canShare" @click="share()" :disabled="!pages.length || busy" class="flyer-btn flyer-btn-main">
                            <i class="bi bi-share-fill"></i><span>Compartilhar</span>
                        </button>
                        <button type="button" x-show="canCopy" @click="copy()" :disabled="!pages.length || busy" class="flyer-btn" :class="canShare ? 'flyer-btn-soft' : 'flyer-btn-main'">
                            <i class="bi bi-clipboard-check"></i><span x-text="pages.length > 1 ? 'Copiar ' + (current + 1) + '/' + pages.length : 'Copiar imagem'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-center text-slate-500 dark:text-slate-400">
                        <i class="bi bi-lightbulb"></i>
                        <span x-show="canShare">No celular, "Compartilhar" manda todas as imagens direto para o status do WhatsApp ou o Instagram.</span>
                        <span x-show="!canShare">Baixe as imagens e poste no status ou no Instagram, ou copie e cole (Ctrl+V) no WhatsApp Web.</span>
                    </p>
                </div>

                {{-- ===== OPÇÕES ===== --}}
                <div class="min-w-0 lg:overflow-y-auto p-3 sm:p-5 space-y-3 border-t lg:border-t-0 lg:border-l border-slate-100 dark:border-slate-700">
                    <div class="set-card space-y-3">
                        <div>
                            <span class="share-label mb-1.5"><i class="bi bi-aspect-ratio"></i> Formato</span>
                            <div class="grid grid-cols-3 gap-1.5">
                                <template x-for="f in formats" :key="f.key">
                                    <button type="button" @click="setFormat(f.key)" class="flyer-format" :class="format === f.key ? 'on' : ''">
                                        <span class="flyer-format-shape" :style="'aspect-ratio:' + f.ratio"></span>
                                        <b x-text="f.label"></b><small x-text="f.size"></small>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="share-label"><i class="bi bi-grid-3x3-gap"></i> Ofertas por imagem</span>
                            <div class="flex gap-1 p-1 rounded-xl bg-white dark:bg-slate-900">
                                <template x-for="n in [2, 4, 6, 9]" :key="n">
                                    <button type="button" @click="perPage = n; schedule()" class="share-seg" :class="perPage === n ? 'flyer-seg-on' : ''" x-text="n"></button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <span class="share-label mb-1.5"><i class="bi bi-type"></i> Título</span>
                            <input type="text" maxlength="40" x-model="title" @input.debounce.250ms="schedule()" class="flyer-input" placeholder="Ofertas da semana">
                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                <template x-for="t in titles" :key="t">
                                    <button type="button" @click="title = t; schedule()" class="promo-chip" :class="title === t ? 'promo-chip-active' : ''" x-text="t"></button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="share-label"><i class="bi bi-calendar-event"></i> Validade</span>
                                <input type="checkbox" class="sr-only peer" x-model="showValidity" @change="schedule()">
                                <span class="set-switch"></span>
                            </label>
                            <input type="text" maxlength="70" x-show="showValidity" x-model="validity" @input="validityEdited = true" @input.debounce.250ms="schedule()" class="flyer-input mt-1.5">
                        </div>

                        <div>
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="share-label"><i class="bi bi-shop"></i> Rodapé (loja / contato)</span>
                                <input type="checkbox" class="sr-only peer" x-model="showFooter" @change="schedule()">
                                <span class="set-switch"></span>
                            </label>
                            <input type="text" maxlength="60" x-show="showFooter" x-model="footer" @input.debounce.250ms="schedule()" placeholder="Ex.: Loja da Ana · (11) 99999-9999" class="flyer-input mt-1.5">
                        </div>

                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="share-label"><i class="bi bi-palette"></i> Cores</span>
                            <div class="flex gap-1.5">
                                <template x-for="(th, key) in themes" :key="key">
                                    <button type="button" @click="theme = key; schedule()" :title="th.name" class="share-swatch" :class="theme === key ? 'on' : ''"
                                            :style="'background: linear-gradient(135deg,' + th.g.join(',') + ')'"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Produtos --}}
                    <div class="set-card">
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                            <span class="share-label"><i class="bi bi-check2-square"></i> Ofertas no folheto</span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="sortBy('desconto')" class="promo-chip" :class="sort === 'desconto' ? 'promo-chip-active' : ''">Maior desconto</button>
                                <button type="button" @click="sortBy('nome')" class="promo-chip" :class="sort === 'nome' ? 'promo-chip-active' : ''">A-Z</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mb-2 text-xs font-semibold">
                            <button type="button" @click="selectAll(true)" class="text-violet-600 dark:text-violet-300 hover:underline">Marcar todas</button>
                            <button type="button" @click="selectAll(false)" class="text-slate-500 hover:underline">Desmarcar</button>
                        </div>
                        <div class="space-y-1.5 max-h-72 lg:max-h-none overflow-y-auto share-scroll pr-0.5">
                            <template x-for="c in list" :key="c.id">
                                <label class="flyer-item" :class="isOn(c.id) ? 'on' : ''">
                                    <input type="checkbox" class="sr-only" :checked="isOn(c.id)" @change="toggle(c.id)">
                                    <span class="flyer-check"><i class="bi bi-check-lg"></i></span>
                                    <template x-if="c.image"><img :src="c.image" alt="" class="w-10 h-10 rounded-lg object-cover bg-white border border-slate-200 dark:border-slate-700 shrink-0" loading="lazy"></template>
                                    <template x-if="!c.image"><span class="w-10 h-10 rounded-lg flyer-grad opacity-80 shrink-0 inline-flex items-center justify-center text-white"><i class="bi bi-bag-heart"></i></span></template>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[13px] font-semibold text-slate-700 dark:text-slate-200 truncate" x-text="c.name"></span>
                                        <span class="block text-[11px] text-slate-500"><s x-text="c.original"></s> · <b class="text-rose-600 dark:text-rose-400" x-text="c.promo"></b></span>
                                    </span>
                                    <span class="flyer-off" x-text="'-' + c.discount + '%'"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .flyer-grad { background: linear-gradient(135deg, #ec4899, #a855f7 55%, #3b82f6); }
    .flyer-head { background: linear-gradient(90deg, rgba(236,72,153,.10), rgba(168,85,247,.10), rgba(59,130,246,.10)); }
    .flyer-stage { background: #efeae2 radial-gradient(rgba(0,0,0,.05) 1px, transparent 1px) 0 0 / 14px 14px; }
    .dark .flyer-stage { background-color: #0b141a; }
    .flyer-strip { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 1rem; scrollbar-width: none; border-radius: 1rem; }
    .flyer-strip::-webkit-scrollbar { display: none; }
    .flyer-slide { flex: 0 0 100%; min-width: 0; scroll-snap-align: center; display: flex; justify-content: center; align-items: center; }
    .flyer-img { display: block; height: auto; width: auto; max-width: 100%; border-radius: .9rem; box-shadow: 0 10px 30px rgba(15,23,42,.25); background: #fff; }
    .flyer-img.is-story { max-height: min(62vh, 640px); aspect-ratio: 9 / 16; }
    .flyer-img.is-square { max-height: min(52vh, 520px); aspect-ratio: 1 / 1; }
    .flyer-img.is-feed { max-height: min(56vh, 560px); aspect-ratio: 4 / 5; }
    @media (max-width: 640px) {
        .flyer-img.is-story { max-height: 58vh; }
        .flyer-img.is-square, .flyer-img.is-feed { max-height: 48vh; }
    }
    .flyer-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 2.4rem; height: 2.4rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.92); color: rgb(91 33 182); box-shadow: 0 4px 14px rgba(0,0,0,.18); }
    .flyer-nav:disabled { opacity: .3; }
    .flyer-dot { width: .5rem; height: .5rem; border-radius: 999px; background: rgba(100,116,139,.4); transition: all .15s; }
    .flyer-dot.on { width: 1.4rem; background: linear-gradient(90deg, #ec4899, #a855f7, #3b82f6); }
    .flyer-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .75rem .5rem; border-radius: .9rem; font-size: .85rem; font-weight: 800; transition: all .15s; white-space: nowrap; }
    .flyer-btn:disabled { opacity: .5; }
    .flyer-btn:active { transform: scale(.97); }
    .flyer-btn-main { color: #fff; background: linear-gradient(135deg, #ec4899, #a855f7 55%, #3b82f6); box-shadow: 0 6px 16px rgba(168,85,247,.35); }
    .flyer-btn-soft { color: rgb(51 65 85); background: #fff; border: 1px solid rgba(203,213,225,.9); }
    .dark .flyer-btn-soft { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .flyer-format { display: flex; flex-direction: column; align-items: center; gap: .2rem; padding: .55rem .3rem; border-radius: .9rem; background: #fff; border: 1.5px solid rgba(226,232,240,.95); color: rgb(71 85 105); }
    .dark .flyer-format { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(203 213 225); }
    .flyer-format b { font-size: .78rem; }
    .flyer-format small { font-size: .62rem; opacity: .7; }
    .flyer-format-shape { height: 1.6rem; border-radius: .25rem; border: 2px solid currentColor; opacity: .7; }
    .flyer-format.on { color: #fff; border-color: transparent; background: linear-gradient(135deg, #ec4899, #a855f7 55%, #3b82f6); box-shadow: 0 6px 16px rgba(168,85,247,.3); }
    .flyer-format.on .flyer-format-shape { opacity: 1; }
    .flyer-modal .share-seg.flyer-seg-on { background: linear-gradient(135deg, #ec4899, #a855f7); color: #fff; box-shadow: 0 2px 8px rgba(168,85,247,.3); }
    .flyer-input { width: 100%; padding: .5rem .7rem; border-radius: .8rem; font-size: .85rem; font-weight: 600; border: 1px solid rgba(203,213,225,.9); background: #fff; color: rgb(30 41 59); }
    .flyer-input:focus { outline: none; border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168,85,247,.15); }
    .dark .flyer-input { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .flyer-item { display: flex; align-items: center; gap: .6rem; padding: .45rem .55rem; border-radius: .9rem; cursor: pointer; background: #fff; border: 1.5px solid rgba(226,232,240,.95); transition: all .12s; }
    .dark .flyer-item { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .flyer-item.on { border-color: rgba(168,85,247,.55); box-shadow: 0 0 0 2px rgba(168,85,247,.12); }
    .flyer-check { width: 1.35rem; height: 1.35rem; flex-shrink: 0; border-radius: .45rem; display: inline-flex; align-items: center; justify-content: center; border: 2px solid rgb(203 213 225); color: transparent; font-size: .8rem; }
    .flyer-item.on .flyer-check { border-color: transparent; color: #fff; background: linear-gradient(135deg, #ec4899, #a855f7); }
    .flyer-off { flex-shrink: 0; padding: .15rem .45rem; border-radius: 999px; font-size: .7rem; font-weight: 900; color: #fff; background: linear-gradient(135deg, #f43f5e, #f97316); }
    .flyer-modal .share-label { display: flex; align-items: center; gap: .35rem; font-size: .8rem; font-weight: 700; color: rgb(51 65 85); }
    .dark .flyer-modal .share-label { color: rgb(226 232 240); }
    .flyer-modal .share-seg { padding: .35rem .7rem; border-radius: .6rem; font-size: .75rem; font-weight: 700; color: rgb(100 116 139); }
    .flyer-modal .share-swatch { width: 1.9rem; height: 1.9rem; border-radius: 999px; border: 3px solid #fff; box-shadow: 0 0 0 1px rgba(148,163,184,.5); transition: transform .12s; }
    .flyer-modal .share-swatch.on { transform: scale(1.12); box-shadow: 0 0 0 2px #a855f7; }
    .flyer-modal .share-scroll::-webkit-scrollbar { width: 4px; }
    .flyer-modal .share-scroll::-webkit-scrollbar-thumb { background: rgba(168,85,247,.3); border-radius: 8px; }
</style>
@include('livewire.promotions.partials.settings-modal-styles')
