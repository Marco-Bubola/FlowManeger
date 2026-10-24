<div class="product-share-root">
@if($show && $data['product'])
    @php
        $product = $data['product'];
        $promo = $data['promo'];
        $templates = $promo ? \App\Models\PromotionSetting::TEMPLATES : \App\Models\PromotionSetting::PRODUCT_TEMPLATES;
    @endphp
    @include('livewire.promotions.partials.styles')
    @include('livewire.promotions.partials.settings-modal-styles')

    <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="close" data-testid="product-share">
        <div class="absolute inset-0" wire:click="close"></div>
        <div class="relative w-full sm:max-w-5xl max-h-[95vh] flex flex-col bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden"
             wire:key="ps-{{ $product->id }}"
             x-data="productShare(@js($data['card']), {{ $promo ? 'true' : 'false' }})" x-init="render()">

            {{-- Cabeçalho --}}
            <div class="px-5 pt-4 pb-3 bg-gradient-to-r from-green-500/10 via-emerald-500/10 to-indigo-500/10 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/30 shrink-0">
                        <i class="bi bi-whatsapp text-white text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white leading-tight">{{ $promo ? 'Enviar promoção' : 'Enviar produto' }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ ucwords(mb_strtolower($product->name)) }}</p>
                    </div>
                    <button type="button" wire:click="close" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10" title="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                {{-- Abas só no celular/tablet; no computador as duas colunas aparecem juntas --}}
                <div class="mt-3 grid grid-cols-2 gap-1 p-1 rounded-2xl bg-white/70 dark:bg-slate-900/60 lg:hidden">
                    <button type="button" @click="pane = 'img'" :class="pane === 'img' ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow' : 'text-slate-600 dark:text-slate-300'" class="py-2 rounded-xl text-sm font-semibold"><i class="bi bi-image"></i> Imagem</button>
                    <button type="button" @click="pane = 'msg'" :class="pane === 'msg' ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow' : 'text-slate-600 dark:text-slate-300'" class="py-2 rounded-xl text-sm font-semibold"><i class="bi bi-chat-text"></i> Mensagem</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- ===== IMAGEM ===== --}}
                <div class="space-y-3 min-w-0" :class="pane === 'img' ? '' : 'hidden lg:block'">
                    <div class="ps-preview">
                        <canvas x-ref="canvas" class="max-h-[380px] w-auto max-w-full rounded-xl shadow-lg"></canvas>
                    </div>

                    <div class="set-card space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="ps-label"><i class="bi bi-aspect-ratio"></i> Formato</span>
                            <div class="flex gap-1 p-1 rounded-xl bg-white dark:bg-slate-900">
                                <button type="button" @click="format = 'square'; render()" :class="format === 'square' ? 'ps-seg-on' : ''" class="ps-seg"><i class="bi bi-square"></i> Post</button>
                                <button type="button" @click="format = 'story'; render()" :class="format === 'story' ? 'ps-seg-on' : ''" class="ps-seg"><i class="bi bi-phone"></i> Status</button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="ps-label"><i class="bi bi-palette"></i> Cores</span>
                            <div class="flex gap-1.5 flex-wrap justify-end">
                                <template x-for="(th, key) in themes" :key="key">
                                    <button type="button" @click="theme = key; render()" :title="th.name"
                                            class="ps-swatch" :class="theme === key ? 'on' : ''"
                                            :style="'background: linear-gradient(135deg,' + th.band + ',' + th.accent + ')'"></button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <span class="ps-label mb-1.5"><i class="bi bi-type"></i> Título</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="label in titles" :key="label">
                                    <button type="button" @click="title = label; render()" class="promo-chip" :class="title === label ? 'promo-chip-active' : ''" x-text="label"></button>
                                </template>
                                <input type="text" maxlength="24" placeholder="Outro…" x-model="customTitle" @input.debounce.300ms="title = customTitle.toUpperCase(); render()"
                                       class="w-28 px-2.5 py-1 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            </div>
                        </div>

                        <div class="grid {{ $promo ? 'grid-cols-3' : 'grid-cols-1' }} gap-2">
                            @if($promo)
                                <button type="button" @click="showBadge = !showBadge; render()" class="set-option !min-h-0 !py-2" :class="showBadge ? 'set-option-on' : ''"><i class="bi bi-percent"></i><span class="text-[11px] font-semibold">Selo %</span></button>
                                <button type="button" @click="showSavings = !showSavings; render()" class="set-option !min-h-0 !py-2" :class="showSavings ? 'set-option-on' : ''"><i class="bi bi-piggy-bank"></i><span class="text-[11px] font-semibold">Economia</span></button>
                            @endif
                            <button type="button" @click="showValidity = !showValidity; render()" class="set-option !min-h-0 !py-2" :class="showValidity ? 'set-option-on' : ''">
                                <i class="bi {{ $promo ? 'bi-calendar-event' : 'bi-box-seam' }}"></i><span class="text-[11px] font-semibold">{{ $promo ? 'Validade' : 'Rodapé "Pronta entrega"' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                        <button type="button" @click="download()" class="ps-btn-soft"><i class="bi bi-download"></i> Baixar imagem</button>
                        <button type="button" @click="copyImage()" class="ps-btn-soft"><i class="bi bi-images"></i> Copiar imagem</button>
                    </div>
                </div>

                {{-- ===== MENSAGEM ===== --}}
                <div class="space-y-3 min-w-0" :class="pane === 'msg' ? '' : 'hidden lg:block'">
                    @if($promo)
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 text-xs font-semibold text-orange-700 dark:text-orange-300">
                            <i class="bi bi-fire"></i> Este produto está em promoção: a imagem e a mensagem já saem com o preço De/Por.
                        </div>
                    @endif
                    <div>
                        <span class="ps-label mb-1"><i class="bi bi-person"></i> Para</span>
                        <select wire:change="setClient($event.target.value)" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200">
                            <option value="">Qualquer contato, status ou grupo</option>
                            @foreach($data['allClients'] as $c)
                                <option value="{{ $c->id }}" @selected($clientId === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <span class="ps-label mb-1.5"><i class="bi bi-magic"></i> Modelo da mensagem</span>
                        <div class="flex gap-1.5 overflow-x-auto pb-1 ps-scroll">
                            @if($promo)
                                <button type="button" wire:click="setTemplate('')" class="promo-chip {{ $template === '' ? 'promo-chip-active' : '' }}"><i class="bi bi-star"></i> Meu padrão</button>
                            @endif
                            @foreach($templates as $key => [$label, $icon])
                                <button type="button" wire:click="setTemplate('{{ $key }}')" class="promo-chip {{ $template === $key || (! $promo && $template === '' && $loop->first) ? 'promo-chip-active' : '' }}"><i class="bi {{ $icon }}"></i> {{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <span class="ps-label mb-1"><i class="bi bi-chat-text"></i> Mensagem <small class="font-normal text-slate-400">(pode editar)</small></span>
                        <textarea wire:model="text" x-ref="text" rows="7" class="set-textarea" data-testid="product-share-text"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-bold">
                        <button type="button" @click="shareAll()" class="ps-btn ps-btn-main"><i class="bi bi-send-fill"></i> Enviar foto + texto</button>
                        <button type="button" @click="openWhatsapp({{ $clientId ?? 'null' }})" class="ps-btn ps-btn-wa"><i class="bi bi-whatsapp"></i> Abrir WhatsApp</button>
                        <button type="button" @click="copyText()" class="ps-btn-soft"><i class="bi bi-clipboard"></i> Copiar texto</button>
                    </div>
                    <div class="flex gap-2 p-2.5 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 text-[11.5px] text-green-800 dark:text-green-200">
                        <i class="bi bi-lightbulb-fill mt-0.5"></i>
                        <span><b>Celular:</b> "Enviar foto + texto" abre a lista de apps; escolha o WhatsApp e a foto vai junto.<br>
                        <b>Computador:</b> o texto já abre escrito e a foto vai copiada. Na conversa, aperte <b>Ctrl+V</b> antes de enviar: a foto vai em cima e o texto como legenda.</span>
                    </div>

                    @if($data['clients']->count())
                        <div class="pt-1">
                            <span class="ps-label mb-1.5"><i class="bi bi-people"></i> Clientes que podem gostar</span>
                            <div class="space-y-1.5 max-h-60 overflow-y-auto ps-scroll">
                                @foreach($data['clients'] as $row)
                                    @php $name = $row['client']->name; $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join(''); @endphp
                                    <div class="flex items-center gap-2.5 p-2 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 text-white text-xs font-black flex items-center justify-center shrink-0">{{ $initials }}</div>
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
@endif

<style>
    .ps-preview { display: flex; justify-content: center; align-items: center; padding: .9rem; border-radius: 1.25rem;
        background: #efeae2 radial-gradient(rgba(0,0,0,.05) 1px, transparent 1px) 0 0 / 14px 14px; }
    .dark .ps-preview { background-color: #0b141a; }
    .ps-label { display: flex; align-items: center; gap: .35rem; font-size: .8rem; font-weight: 700; color: rgb(51 65 85); }
    .dark .ps-label { color: rgb(226 232 240); }
    .ps-seg { padding: .35rem .7rem; border-radius: .6rem; font-size: .75rem; font-weight: 700; color: rgb(100 116 139); }
    .ps-seg-on { background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
    .ps-swatch { width: 1.9rem; height: 1.9rem; border-radius: 999px; border: 3px solid #fff; box-shadow: 0 0 0 1px rgba(148,163,184,.5); transition: transform .12s; }
    .ps-swatch.on { transform: scale(1.12); box-shadow: 0 0 0 2px #6366f1; }
    .ps-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .75rem; border-radius: .9rem; color: #fff; transition: all .15s; }
    .ps-btn:active { transform: scale(.97); }
    .ps-btn-main { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 6px 16px rgba(34,197,94,.3); }
    .ps-btn-wa { background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 6px 16px rgba(5,150,105,.3); }
    .ps-btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .7rem; border-radius: .9rem;
        border: 1px solid rgba(203,213,225,.9); color: rgb(51 65 85); background: #fff; font-weight: 700; }
    .ps-btn-soft:hover { border-color: rgba(99,102,241,.5); color: rgb(79 70 229); }
    .dark .ps-btn-soft { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .ps-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
    .ps-scroll::-webkit-scrollbar-thumb { background: rgba(99,102,241,.3); border-radius: 8px; }
    /* Botão de WhatsApp dos cards e telas de produto */
    .btn-wa-product { background: linear-gradient(135deg, #22c55e, #16a34a) !important; color: #fff !important; }
    .btn-wa-product:hover { filter: brightness(1.08); }
</style>

@script
<script>
    /**
     * Imagem do produto (post quadrado ou status) desenhada em canvas e envio
     * pelo WhatsApp. Com promoção mostra De/Por e o selo; sem promoção, o preço.
     */
    Alpine.data('productShare', (card, isPromo) => ({
        c: card,
        isPromo,
        format: 'square',
        images: {},
        pane: 'img',
        theme: isPromo ? 'rosa' : 'roxo',
        title: isPromo ? 'PROMOÇÃO' : 'DISPONÍVEL',
        customTitle: '',
        titles: isPromo
            ? ['PROMOÇÃO', 'OFERTA', 'SÓ HOJE', 'ÚLTIMAS UNIDADES', 'QUEIMA DE ESTOQUE']
            : ['DISPONÍVEL', 'NOVIDADE', 'PRONTA ENTREGA', 'ÚLTIMAS UNIDADES', 'DICA DE PRESENTE'],
        showBadge: true,
        showSavings: true,
        showValidity: true,
        file: null,
        themes: {
            rosa:    { name: 'Rosa',    band: '#e11d48', bg1: '#fff1f2', bg2: '#fce7f3', accent: '#f97316', price: '#e11d48', text: '#1e293b', sub: '#64748b' },
            roxo:    { name: 'Roxo',    band: '#7c3aed', bg1: '#f5f3ff', bg2: '#fae8ff', accent: '#ec4899', price: '#7c3aed', text: '#1e1b4b', sub: '#6b7280' },
            noite:   { name: 'Preto e dourado', band: '#0f172a', bg1: '#1e293b', bg2: '#020617', accent: '#eab308', price: '#facc15', text: '#f8fafc', sub: '#94a3b8' },
            verde:   { name: 'Verde',   band: '#059669', bg1: '#ecfdf5', bg2: '#d1fae5', accent: '#f59e0b', price: '#047857', text: '#064e3b', sub: '#6b7280' },
            laranja: { name: 'Laranja', band: '#ea580c', bg1: '#fff7ed', bg2: '#ffedd5', accent: '#dc2626', price: '#c2410c', text: '#431407', sub: '#78716c' },
            azul:    { name: 'Azul',    band: '#2563eb', bg1: '#eff6ff', bg2: '#e0f2fe', accent: '#f43f5e', price: '#1d4ed8', text: '#172554', sub: '#64748b' },
        },
        get palette() { return this.themes[this.theme] || this.themes.roxo; },

        loadImage(src) {
            if (!src) return Promise.resolve(null);
            if (this.images[src]) return Promise.resolve(this.images[src]);
            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => { this.images[src] = img; resolve(img); };
                img.onerror = () => resolve(null);
                img.src = src;
            });
        },

        wrap(ctx, text, maxWidth, maxLines) {
            const words = String(text).split(/\s+/);
            const lines = [];
            let line = '';
            for (const w of words) {
                const test = line ? line + ' ' + w : w;
                if (ctx.measureText(test).width > maxWidth && line) {
                    lines.push(line);
                    line = w;
                    if (lines.length === maxLines) break;
                } else {
                    line = test;
                }
            }
            if (lines.length < maxLines && line) lines.push(line);
            if (lines.length === maxLines && words.join(' ').length > lines.join(' ').length) {
                lines[maxLines - 1] = lines[maxLines - 1].replace(/\s*\S*$/, '') + '…';
            }
            return lines;
        },

        async render() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.c) return;
            const story = this.format === 'story';
            const W = 1080, H = story ? 1920 : 1080;
            canvas.width = W; canvas.height = H;
            const ctx = canvas.getContext('2d');
            const t = this.palette;
            const dark = t.band === '#0f172a';
            const c = this.c;
            const badge = this.isPromo && this.showBadge;

            // Fundo em degradê com círculos suaves
            const bg = ctx.createLinearGradient(0, 0, W, H);
            bg.addColorStop(0, t.bg1); bg.addColorStop(1, t.bg2);
            ctx.fillStyle = bg; ctx.fillRect(0, 0, W, H);
            ctx.globalAlpha = .14;
            ctx.fillStyle = t.band; ctx.beginPath(); ctx.arc(W - 80, 90, story ? 360 : 260, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = t.accent; ctx.beginPath(); ctx.arc(60, H - 60, story ? 320 : 220, 0, Math.PI * 2); ctx.fill();
            ctx.globalAlpha = 1;

            // Título numa pílula no topo
            const title = (this.title || 'DISPONÍVEL').toUpperCase();
            let size = story ? 64 : 50;
            ctx.font = '900 ' + size + 'px sans-serif';
            while (ctx.measureText(title).width > W - (badge ? 420 : 160) && size > 26) { size -= 2; ctx.font = '900 ' + size + 'px sans-serif'; }
            const pillH = size + (story ? 48 : 38), pillW = ctx.measureText(title).width + (story ? 96 : 76);
            const pillX = badge ? 60 : (W - pillW) / 2, pillY = story ? 70 : 48;
            const pg = ctx.createLinearGradient(pillX, 0, pillX + pillW, 0);
            pg.addColorStop(0, t.band); pg.addColorStop(1, t.accent);
            ctx.save(); ctx.shadowColor = 'rgba(0,0,0,.18)'; ctx.shadowBlur = 24; ctx.shadowOffsetY = 8;
            ctx.fillStyle = pg; ctx.beginPath(); ctx.roundRect(pillX, pillY, pillW, pillH, pillH / 2); ctx.fill(); ctx.restore();
            ctx.fillStyle = '#fff'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.fillText(title, pillX + pillW / 2, pillY + pillH / 2 + 2);
            ctx.textBaseline = 'alphabetic';

            // Cartão branco grande com a foto
            const card = story ? { x: 60, y: 230, w: W - 120, h: 1080 } : { x: 60, y: 160, w: W - 120, h: 560 };
            ctx.save(); ctx.shadowColor = 'rgba(15,23,42,.18)'; ctx.shadowBlur = 50; ctx.shadowOffsetY = 18;
            ctx.fillStyle = '#ffffff'; ctx.beginPath(); ctx.roundRect(card.x, card.y, card.w, card.h, 48); ctx.fill(); ctx.restore();
            const img = await this.loadImage(c.image);
            if (img) {
                const pad = story ? 60 : 36;
                const bw = card.w - pad * 2, bh = card.h - pad * 2;
                const r = Math.min(bw / img.width, bh / img.height);
                const w = img.width * r, h = img.height * r;
                ctx.drawImage(img, card.x + (card.w - w) / 2, card.y + (card.h - h) / 2, w, h);
            } else {
                ctx.fillStyle = '#cbd5e1'; ctx.textAlign = 'center';
                ctx.font = '700 ' + (story ? 60 : 44) + 'px sans-serif';
                ctx.fillText('📦', W / 2, card.y + card.h / 2 + 20);
            }

            // Selo de desconto (só promoção)
            if (badge && c.discount) {
                const br = story ? 125 : 95;
                const bx = card.x + card.w - br + 20, by = story ? 150 : 120;
                const g = ctx.createLinearGradient(bx - br, by - br, bx + br, by + br);
                g.addColorStop(0, t.accent); g.addColorStop(1, t.band);
                ctx.save(); ctx.shadowColor = 'rgba(0,0,0,.25)'; ctx.shadowBlur = 30; ctx.shadowOffsetY = 10;
                ctx.fillStyle = g; ctx.beginPath(); ctx.arc(bx, by, br, 0, Math.PI * 2); ctx.fill(); ctx.restore();
                ctx.strokeStyle = '#fff'; ctx.lineWidth = story ? 10 : 8; ctx.beginPath(); ctx.arc(bx, by, br - 4, 0, Math.PI * 2); ctx.stroke();
                ctx.fillStyle = '#fff'; ctx.textAlign = 'center';
                ctx.font = '900 ' + (story ? 78 : 60) + 'px sans-serif';
                ctx.fillText('-' + c.discount + '%', bx, by + (story ? 14 : 10));
                ctx.font = '800 ' + (story ? 30 : 24) + 'px sans-serif';
                ctx.fillText('OFF', bx, by + (story ? 56 : 42));
            }

            // Nome
            ctx.textAlign = 'center';
            let y = card.y + card.h + (story ? 110 : 72);
            ctx.fillStyle = t.text;
            ctx.font = '800 ' + (story ? 60 : 42) + 'px sans-serif';
            const name = String(c.name).toLowerCase().replace(/(^|\s)\S/g, (m) => m.toUpperCase());
            for (const line of this.wrap(ctx, name, W - 160, 2)) { ctx.fillText(line, W / 2, y); y += story ? 72 : 50; }

            // Preço: De/Por na promoção, só o preço fora dela
            y += story ? 70 : 36;
            const priceFont = '900 ' + (story ? 132 : 92) + 'px sans-serif';
            if (this.isPromo && c.original) {
                ctx.font = '600 ' + (story ? 50 : 36) + 'px sans-serif';
                const deText = 'De ' + c.original;
                const dw = ctx.measureText(deText).width;
                ctx.font = priceFont;
                const porW = ctx.measureText(c.promo).width;
                const gap = story ? 40 : 28;
                const startX = (W - (dw + gap + porW)) / 2;
                ctx.textAlign = 'left';
                ctx.font = '600 ' + (story ? 50 : 36) + 'px sans-serif';
                ctx.fillStyle = t.sub;
                ctx.fillText(deText, startX, y - (story ? 18 : 12));
                ctx.strokeStyle = t.sub; ctx.lineWidth = story ? 5 : 4;
                ctx.beginPath(); ctx.moveTo(startX, y - (story ? 34 : 24)); ctx.lineTo(startX + dw, y - (story ? 34 : 24)); ctx.stroke();
                const g2 = ctx.createLinearGradient(startX + dw + gap, 0, startX + dw + gap + porW, 0);
                g2.addColorStop(0, dark ? '#fde047' : t.price); g2.addColorStop(1, dark ? '#f59e0b' : t.accent);
                ctx.fillStyle = g2; ctx.font = priceFont;
                ctx.fillText(c.promo, startX + dw + gap, y + (story ? 20 : 14));
                ctx.textAlign = 'center';
            } else {
                ctx.font = priceFont;
                const pw = ctx.measureText(c.promo).width;
                const g2 = ctx.createLinearGradient(W / 2 - pw / 2, 0, W / 2 + pw / 2, 0);
                g2.addColorStop(0, dark ? '#fde047' : t.price); g2.addColorStop(1, dark ? '#f59e0b' : t.accent);
                ctx.fillStyle = g2;
                ctx.fillText(c.promo, W / 2, y + (story ? 20 : 14));
            }

            // Economia (promoção)
            if (this.isPromo && this.showSavings && c.savings && c.savings !== 'R$ 0,00') {
                y += story ? 120 : 78;
                const label = 'Economize ' + c.savings;
                ctx.font = '800 ' + (story ? 44 : 30) + 'px sans-serif';
                const lw = ctx.measureText(label).width + (story ? 80 : 56), lh = story ? 76 : 52;
                ctx.fillStyle = dark ? 'rgba(250,204,21,.18)' : 'rgba(255,255,255,.85)';
                ctx.beginPath(); ctx.roundRect(W / 2 - lw / 2, y - lh / 2 - (story ? 14 : 10), lw, lh, lh / 2); ctx.fill();
                ctx.fillStyle = dark ? '#fde047' : t.band;
                ctx.fillText(label, W / 2, y);
            }

            // Rodapé: validade da promoção ou "Pronta entrega"
            if (this.showValidity && c.validity) {
                ctx.fillStyle = t.sub;
                ctx.font = '600 ' + (story ? 40 : 28) + 'px sans-serif';
                ctx.fillText((this.isPromo ? '⏳ ' : '📦 ') + c.validity, W / 2, story ? H - 80 : H - 26);
            }

            // Deixa o arquivo pronto: o compartilhamento precisa acontecer logo no clique.
            this.file = null;
            this.blob().then((b) => { if (b) this.file = new File([b], 'produto.png', { type: 'image/png' }); }).catch(() => {});
        },

        blob() {
            return new Promise((resolve) => this.$refs.canvas.toBlob(resolve, 'image/png'));
        },

        download() {
            const a = document.createElement('a');
            a.href = this.$refs.canvas.toDataURL('image/png');
            a.download = 'produto-' + this.format + '.png';
            a.click();
        },

        async copyImage(quiet = false) {
            try {
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': this.blob() })]);
                if (!quiet) this.toast('Imagem copiada. Na conversa do WhatsApp, cole com Ctrl+V.');
                return true;
            } catch (e) {
                if (!quiet) this.download();
                return false;
            }
        },

        async copyText() {
            try { await navigator.clipboard.writeText(this.$refs.text.value); } catch (e) {}
            await $wire.recordShare('copiar', null);
            this.toast('Texto copiado.');
        },

        async openWhatsapp(clientId, copied = null) {
            // O link do WhatsApp só leva texto. Copia a imagem junto para colar na conversa.
            const copying = copied === null ? this.copyImage(true) : Promise.resolve(copied);
            // Abre a janela ainda no clique para o navegador não bloquear o pop-up.
            const win = window.open('about:blank', '_blank');
            await $wire.$set('text', this.$refs.text.value, false);
            const url = await $wire.recordShare('whatsapp', clientId);
            if (win) { win.location.href = url; } else { window.location.href = url; }
            if (await copying) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'info', duration: 9000,
                    message: 'Antes de enviar, clique na conversa e aperte Ctrl+V (no celular, segure e "Colar"): a foto vai em cima e o texto já escrito vira a legenda.' } }));
            } else {
                this.download();
                this.toast('O WhatsApp só recebe texto pelo link. Baixei a imagem para você anexar na conversa.');
            }
        },

        async shareAll() {
            const text = this.$refs.text.value;
            const file = this.file;
            try {
                if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], text });
                    await $wire.$set('text', text, false);
                    await $wire.recordShare('compartilhar', $wire.clientId);
                    return;
                }
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
            // Sem compartilhamento de arquivo (computador): copia a imagem e abre o WhatsApp com o texto.
            this.openWhatsapp($wire.clientId);
        },

        toast(message) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message, duration: 3000 } }));
        },
    }));
</script>
@endscript
</div>
