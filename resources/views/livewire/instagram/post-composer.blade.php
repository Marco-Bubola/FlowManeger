@php
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $ratio = $kind === 'story' ? '9 / 16' : ($format === 'retrato' ? '4 / 5' : '1 / 1');
    $pool = array_values(array_unique(array_merge($extra, $photos)));
    $user = $setting->username ?: 'sua_loja';
@endphp

<div class="ig-composer w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-28 lg:pb-10">
    <script>
        window.igArt = window.igArt || function (info, photo) {
            return {
                busy: false,
                theme: 'rosa',
                themes: {
                    rosa: { name: 'Rosa', g: ['#f58529', '#dd2a7b', '#8134af'], ink: '#ffffff', tag: '#ffe14d' },
                    noite: { name: 'Noite', g: ['#0f172a', '#1e293b', '#334155'], ink: '#ffffff', tag: '#f59e0b' },
                    areia: { name: 'Areia', g: ['#f5f0e8', '#e9dfcf', '#d6c4a8'], ink: '#3b2f22', tag: '#b45309' },
                    verde: { name: 'Verde', g: ['#065f46', '#047857', '#10b981'], ink: '#ffffff', tag: '#fde047' },
                },
                load(src) {
                    return new Promise((ok) => {
                        if (!src) return ok(null);
                        let u = src;
                        try { const p = new URL(src, location.href); if (p.pathname.startsWith('/storage/')) u = p.pathname; } catch (e) {}
                        const img = new Image();
                        if (!u.startsWith('/')) img.crossOrigin = 'anonymous';
                        img.onload = () => ok(img);
                        img.onerror = () => ok(null);
                        img.src = u;
                    });
                },
                round(ctx, x, y, w, h, r) { ctx.beginPath(); ctx.moveTo(x + r, y); ctx.arcTo(x + w, y, x + w, y + h, r); ctx.arcTo(x + w, y + h, x, y + h, r); ctx.arcTo(x, y + h, x, y, r); ctx.arcTo(x, y, x + w, y, r); ctx.closePath(); },
                wrap(ctx, text, max) {
                    const words = text.split(' '); const lines = []; let line = '';
                    for (const w of words) { const t = line ? line + ' ' + w : w; if (ctx.measureText(t).width > max && line) { lines.push(line); line = w; } else line = t; }
                    if (line) lines.push(line);
                    return lines.slice(0, 2);
                },
                async make(size) {
                    this.busy = true;
                    const W = 1080, H = size === 'story' ? 1920 : (size === 'retrato' ? 1350 : 1080);
                    const c = document.createElement('canvas'); c.width = W; c.height = H;
                    const ctx = c.getContext('2d'); const t = this.themes[this.theme];
                    const g = ctx.createLinearGradient(0, 0, W, H); t.g.forEach((col, i) => g.addColorStop(i / (t.g.length - 1), col));
                    ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);
                    ctx.globalAlpha = .12; ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(W * .9, H * .08, 260, 0, 7); ctx.fill(); ctx.beginPath(); ctx.arc(W * .05, H * .7, 200, 0, 7); ctx.fill(); ctx.globalAlpha = 1;
                    const textH = 330; const pad = 70; const top = size === 'story' ? 220 : 70;
                    const boxH = H - top - textH - pad; const boxW = W - pad * 2;
                    ctx.save(); ctx.shadowColor = 'rgba(0,0,0,.25)'; ctx.shadowBlur = 40; ctx.shadowOffsetY = 16;
                    this.round(ctx, pad, top, boxW, boxH, 48); ctx.fillStyle = '#fff'; ctx.fill(); ctx.restore();
                    const img = await this.load(photo);
                    if (img) {
                        ctx.save(); this.round(ctx, pad, top, boxW, boxH, 48); ctx.clip();
                        const s = Math.min((boxW - 60) / img.width, (boxH - 60) / img.height);
                        const iw = img.width * s, ih = img.height * s;
                        ctx.drawImage(img, pad + (boxW - iw) / 2, top + (boxH - ih) / 2, iw, ih); ctx.restore();
                    }
                    if (info.off) {
                        ctx.save(); ctx.translate(W - pad - 100, top + 100); ctx.rotate(.18);
                        ctx.fillStyle = t.tag; ctx.beginPath(); ctx.arc(0, 0, 110, 0, 7); ctx.fill();
                        ctx.fillStyle = '#111'; ctx.font = '900 76px system-ui, sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillText(info.off, 0, 6); ctx.restore();
                    }
                    ctx.fillStyle = t.ink; ctx.textAlign = 'left'; ctx.textBaseline = 'alphabetic';
                    let y = H - textH - pad / 2 + 70;
                    ctx.font = '800 54px system-ui, sans-serif';
                    for (const l of this.wrap(ctx, info.name, W - pad * 2)) { ctx.fillText(l, pad, y); y += 64; }
                    y += 20;
                    if (info.old) {
                        ctx.font = '600 44px system-ui, sans-serif'; ctx.globalAlpha = .8;
                        const tx = 'De ' + info.old; ctx.fillText(tx, pad, y); const tw = ctx.measureText(tx).width;
                        ctx.fillRect(pad, y - 15, tw, 4); ctx.globalAlpha = 1; y += 90;
                        ctx.font = '900 96px system-ui, sans-serif'; ctx.fillText('Por ' + info.price, pad, y);
                    } else {
                        ctx.font = '900 96px system-ui, sans-serif'; ctx.fillText(info.price, pad, y + 60); y += 60;
                    }
                    ctx.font = '700 34px system-ui, sans-serif'; ctx.globalAlpha = .9; ctx.textAlign = 'right';
                    const foot = [info.ends, info.insta || info.store].filter(Boolean).join('  ·  ');
                    if (foot) ctx.fillText(foot, W - pad, H - pad / 2 - 6);
                    ctx.globalAlpha = 1;
                    c.toBlob((blob) => {
                        if (!blob) { this.busy = false; return; }
                        const f = new File([blob], 'arte.jpg', { type: 'image/jpeg' });
                        this.$wire.upload('artFile', f, () => {}, () => { this.busy = false; });
                    }, 'image/jpeg', .92);
                },
            };
        };
    </script>
    <x-page-header :title="$postId ? 'Editar post agendado' : 'Novo post no Instagram'" icon="bi-instagram" section="produtos" back-route="instagram.index"
                   subtitle="Escolha as fotos, confira a legenda e poste na hora ou agende">
        <x-slot:meta>
            @if($connected)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-pink-50 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300"><i class="bi bi-instagram"></i>{{ '@' . $setting->username }}</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200"><i class="bi bi-exclamation-triangle"></i>Instagram não conectado</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('instagram.index') }}" wire:navigate class="{{ $phBtn }}" title="Posts"><i class="bi bi-grid-3x3 text-pink-500"></i><span class="hidden sm:inline">Posts</span></a>
            <a href="{{ route('settings.instagram') }}" wire:navigate class="{{ $phBtn }}" title="Configurar"><i class="bi bi-gear text-slate-500"></i><span class="hidden sm:inline">Configurar</span></a>
        </x-slot:actions>
    </x-page-header>

    @unless($connected)
        <a href="{{ route('settings.instagram') }}" wire:navigate class="igc-warn" data-testid="igc-not-connected">
            <i class="bi bi-plug"></i>
            <span><b>Conecte o Instagram para postar</b><small>Você pode montar o post agora; para postar ou agendar, conecte a conta em Configurações › Instagram.</small></span>
            <i class="bi bi-chevron-right"></i>
        </a>
    @endunless

    <div class="igc-grid">
        {{-- Produto --}}
        <div class="igc-card igc-prod">
            <div class="igc-head"><span class="igc-num">1</span><h2>Produto</h2>
                @if($product)<button type="button" wire:click="clearProduct" class="igc-link">Trocar</button>@endif
            </div>
            @if($product)
                <div class="igc-product" data-testid="igc-product">
                    <img src="{{ $photos[0] ?? $product->image_url }}" alt="" class="igc-product-img" referrerpolicy="no-referrer">
                    <div class="min-w-0 flex-1">
                        <b class="block truncate">{{ $product->name }}</b>
                        <small class="block text-slate-500 dark:text-slate-400">{{ $product->product_code }}{{ $product->variation_value ? ' · ' . $product->variation_value : '' }}</small>
                        @if($promo)
                            <small class="igc-promo"><i class="bi bi-fire"></i> Promoção: <s>R$ {{ number_format((float) $promo->original_price, 2, ',', '.') }}</s> por <b>R$ {{ number_format((float) $promo->promo_price, 2, ',', '.') }}</b></small>
                        @elseif($product->price_sale > 0)
                            <small class="block font-semibold text-slate-700 dark:text-slate-200">R$ {{ number_format((float) $product->price_sale, 2, ',', '.') }}</small>
                        @endif
                    </div>
                </div>
            @else
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar produto pelo nome ou código" class="igc-input pl-9" data-testid="igc-search">
                </div>
                <p class="igc-hint">{{ trim($search) === '' ? 'Atualizados por último:' : 'Resultados:' }} ou pule e envie suas próprias fotos.</p>
                <div class="igc-results">
                    @forelse($results as $r)
                        <button type="button" wire:click="chooseProduct({{ $r->id }})" class="igc-result" wire:key="r{{ $r->id }}">
                            <img src="{{ $r->image_url }}" alt="" loading="lazy">
                            <span class="min-w-0"><b class="block truncate">{{ $r->name }}</b><small>{{ $r->product_code }}</small></span>
                        </button>
                    @empty
                        <p class="igc-hint">Nenhum produto encontrado.</p>
                    @endforelse
                </div>
            @endif
        </div>

        {{-- Fotos --}}
        <div class="igc-card igc-photos">
            <div class="igc-head"><span class="igc-num">2</span><h2>Fotos</h2><small class="igc-count">{{ count($picked) }}/{{ \App\Livewire\Instagram\PostComposer::MAX_PHOTOS }}</small></div>
            <p class="igc-hint mb-2">Toque para escolher. {{ $kind === 'story' ? 'Cada foto vira um story.' : 'Mais de uma foto vira carrossel; a ordem é a dos números.' }}</p>
            <div class="igc-pool" data-testid="igc-pool">
                @foreach($pool as $u)
                    @php $pos = array_search($u, $picked, true); @endphp
                    <button type="button" wire:click="togglePhoto(@js($u))" wire:key="p{{ md5($u) }}" class="igc-thumb {{ $pos !== false ? 'is-on' : '' }}">
                        <img src="{{ $u }}" alt="" loading="lazy" referrerpolicy="no-referrer">
                        @if($pos !== false)<span class="igc-thumb-n">{{ $pos + 1 }}</span>@endif
                        @if(in_array($u, $extra, true))<span class="igc-thumb-tag">sua</span>@endif
                    </button>
                @endforeach
                <label class="igc-thumb igc-upload" wire:loading.class="is-busy" wire:target="uploads">
                    <input type="file" wire:model="uploads" accept="image/*" multiple class="sr-only">
                    <i class="bi bi-cloud-arrow-up" wire:loading.remove wire:target="uploads"></i>
                    <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="uploads"></i>
                    <small>Enviar fotos</small>
                </label>
            </div>
            @error('uploads.*') <p class="igc-err">{{ $message }}</p> @enderror
            @error('picked') <p class="igc-err">{{ $message }}</p> @enderror

            @if(count($picked) > 1)
                <div class="igc-order">
                    <span class="igc-label"><i class="bi bi-arrow-left-right"></i> Ordem</span>
                    <div class="igc-order-row">
                        @foreach($picked as $i => $u)
                            <div class="igc-order-item" wire:key="o{{ md5($u) }}">
                                <img src="{{ $u }}" alt="" referrerpolicy="no-referrer"><span>{{ $i + 1 }}</span>
                                <div>
                                    <button type="button" wire:click="movePhoto({{ $i }}, {{ $i - 1 }})" @disabled($i === 0) aria-label="Para trás"><i class="bi bi-chevron-left"></i></button>
                                    <button type="button" wire:click="movePhoto({{ $i }}, {{ $i + 1 }})" @disabled($i === count($picked) - 1) aria-label="Para frente"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Arte da oferta --}}
            @if($product && $art['price'])
                <div class="igc-art" x-data="igArt(@js($art), @js($photos[0] ?? null))" x-on:ig-art-ready.window="busy = false">
                    <div class="min-w-0 flex-1">
                        <b><i class="bi bi-magic"></i> Arte {{ $promo ? 'da oferta' : 'do produto' }}</b>
                        <small>Foto, nome e preço{{ $promo ? ' de / por' : '' }} numa imagem pronta. Entra como 1ª foto.</small>
                        <div class="igc-swatches">
                            <template x-for="(t, k) in themes" :key="k">
                                <button type="button" @click="theme = k" :class="theme === k ? 'on' : ''" :style="'background:linear-gradient(135deg,' + t.g.join(',') + ')'" :title="t.name"></button>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="make(@js($kind === 'story' ? 'story' : $format))" :disabled="busy" class="igc-btn-art" data-testid="igc-make-art">
                        <i class="bi" :class="busy ? 'bi-arrow-repeat animate-spin' : 'bi-stars'"></i><span x-text="busy ? 'Criando…' : 'Criar arte'"></span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Prévia --}}
        <div class="igc-preview-wrap">
            <div class="igc-preview {{ $kind === 'story' ? 'is-story' : '' }}" wire:key="pv{{ md5(json_encode([$picked, $format, $kind, $fit])) }}" x-data="{ i: 0, n: {{ max(1, count($picked)) }} }">
                @if($kind === 'story')
                    <div class="igc-story" style="aspect-ratio: {{ $ratio }}; background: {{ $bg }}">
                        <div class="igc-story-bars">@foreach($picked ?: [null] as $j => $u)<span :class="i >= {{ $j }} ? 'on' : ''"></span>@endforeach</div>
                        <div class="igc-story-user"><span class="igc-av">@if($setting->picture_url)<img src="{{ $setting->picture_url }}" alt="" referrerpolicy="no-referrer">@endif</span><b>{{ $user }}</b><small>agora</small></div>
                        @forelse($picked as $j => $u)
                            <img src="{{ $u }}" alt="" x-show="i === {{ $j }}" class="igc-media {{ $fit === 'preencher' ? 'is-cover' : '' }}" referrerpolicy="no-referrer">
                        @empty
                            <div class="igc-empty"><i class="bi bi-image"></i><span>Escolha uma foto</span></div>
                        @endforelse
                        <button type="button" class="igc-tap left" @click="i = Math.max(0, i - 1)" aria-label="Anterior"></button>
                        <button type="button" class="igc-tap right" @click="i = Math.min(n - 1, i + 1)" aria-label="Próxima"></button>
                    </div>
                @else
                    <div class="igc-post-top"><span class="igc-av">@if($setting->picture_url)<img src="{{ $setting->picture_url }}" alt="" referrerpolicy="no-referrer">@endif</span><b>{{ $user }}</b><i class="bi bi-three-dots ml-auto"></i></div>
                    <div class="igc-media-box" style="aspect-ratio: {{ $ratio }}; background: {{ $bg }}">
                        @forelse($picked as $j => $u)
                            <img src="{{ $u }}" alt="" x-show="i === {{ $j }}" class="igc-media {{ $fit === 'preencher' ? 'is-cover' : '' }}" referrerpolicy="no-referrer">
                        @empty
                            <div class="igc-empty"><i class="bi bi-image"></i><span>Escolha uma foto</span></div>
                        @endforelse
                        @if(count($picked) > 1)
                            <span class="igc-pager" x-text="(i + 1) + '/' + n"></span>
                            <button type="button" class="igc-nav left" @click="i = Math.max(0, i - 1)" x-show="i > 0" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                            <button type="button" class="igc-nav right" @click="i = Math.min(n - 1, i + 1)" x-show="i < n - 1" aria-label="Próxima"><i class="bi bi-chevron-right"></i></button>
                        @endif
                    </div>
                    <div class="igc-post-icons"><i class="bi bi-heart"></i><i class="bi bi-chat"></i><i class="bi bi-send"></i>
                        @if(count($picked) > 1)<span class="igc-dots">@foreach($picked as $j => $u)<span :class="i === {{ $j }} ? 'on' : ''"></span>@endforeach</span>@endif
                        <i class="bi bi-bookmark ml-auto"></i></div>
                    <p class="igc-caption" x-data="{ more: false }"><b>{{ $user }}</b> <span :class="more ? '' : 'line-clamp-3'">{!! nl2br(e($caption)) !!}</span>
                        @if(mb_strlen($caption) > 120)<button type="button" @click="more = !more" class="text-slate-400" x-text="more ? 'menos' : 'mais'"></button>@endif
                    </p>
                @endif
            </div>
            <p class="igc-hint text-center mt-2"><i class="bi bi-phone"></i> Prévia de como fica no Instagram</p>
        </div>

        {{-- Formato, legenda, postar --}}
        <div class="igc-card igc-opts">
            <div class="igc-head"><span class="igc-num">3</span><h2>Formato</h2></div>
            <div class="igc-seg3">
                <button type="button" wire:click="$set('kind', 'feed'); $set('format', 'quadrado')" class="{{ $kind === 'feed' && $format === 'quadrado' ? 'on' : '' }}"><span class="igc-shape" style="aspect-ratio:1/1"></span><b>Quadrado</b><small>Feed 1:1</small></button>
                <button type="button" wire:click="$set('kind', 'feed'); $set('format', 'retrato')" class="{{ $kind === 'feed' && $format === 'retrato' ? 'on' : '' }}"><span class="igc-shape" style="aspect-ratio:4/5"></span><b>Retrato</b><small>Feed 4:5</small></button>
                <button type="button" wire:click="$set('kind', 'story')" class="{{ $kind === 'story' ? 'on' : '' }}" data-testid="igc-story"><span class="igc-shape" style="aspect-ratio:9/16"></span><b>Story</b><small>9:16, 24 horas</small></button>
            </div>
            <div class="igc-row">
                <span class="igc-label"><i class="bi bi-aspect-ratio"></i> Foto</span>
                <div class="igc-seg">
                    <button type="button" wire:click="$set('fit', 'inteira')" class="{{ $fit === 'inteira' ? 'on' : '' }}">Inteira</button>
                    <button type="button" wire:click="$set('fit', 'preencher')" class="{{ $fit === 'preencher' ? 'on' : '' }}">Preencher</button>
                </div>
                @if($fit === 'inteira')
                    <div class="igc-bgs">
                        @foreach(['#ffffff' => 'Branco', '#f5f0e8' => 'Areia', '#111827' => 'Preto', '#fce7f3' => 'Rosa'] as $hex => $nm)
                            <button type="button" wire:click="$set('bg', '{{ $hex }}')" class="{{ $bg === $hex ? 'on' : '' }}" style="background: {{ $hex }}" title="Fundo {{ $nm }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($kind === 'feed')
                <div class="igc-head mt-4"><span class="igc-num">4</span><h2>Legenda</h2>
                    <button type="button" wire:click="resetCaption" class="igc-link"><i class="bi bi-arrow-counterclockwise"></i> Refazer do modelo</button>
                </div>
                <div class="igc-dates">
                    <span class="igc-label"><i class="bi bi-calendar-heart"></i> Data especial</span>
                    @foreach($dates as $d)
                        <button type="button" wire:click="useDate('{{ $d['key'] }}')" class="{{ $d['now'] ? 'is-now' : '' }}" title="{{ \App\Services\Promotions\CommercialCalendar::label($d) }}">{{ $d['emoji'] }} {{ $d['name'] }}</button>
                    @endforeach
                </div>
                <textarea wire:model.live.debounce.500ms="caption" rows="9" maxlength="2200" class="igc-input igc-textarea" data-testid="igc-caption"></textarea>
                <div class="igc-meta">
                    <span>{{ mb_strlen($caption) }}/2200</span>
                    <span class="{{ $hashtagCount > 30 ? 'text-rose-600 font-bold' : '' }}"># {{ $hashtagCount }}/30</span>
                    <a href="{{ route('settings.instagram') }}" wire:navigate class="igc-link ml-auto">Mudar o modelo e as hashtags</a>
                </div>
                @error('caption') <p class="igc-err">{{ $message }}</p> @enderror
                <p class="igc-hint"><i class="bi bi-info-circle"></i> Link na legenda do Instagram não abre. Por isso a legenda fala "link na bio": deixe o link do catálogo na bio do perfil.</p>
            @else
                <p class="igc-hint mt-3"><i class="bi bi-info-circle"></i> Story não leva legenda pela API do Instagram. Use a "Arte" para mostrar nome e preço na imagem.</p>
            @endif

            <div class="igc-head mt-4"><span class="igc-num">{{ $kind === 'feed' ? 5 : 4 }}</span><h2>Quando</h2></div>
            <div class="igc-seg igc-when">
                <button type="button" wire:click="$set('scheduling', false)" class="{{ ! $scheduling ? 'on' : '' }}"><i class="bi bi-lightning-charge"></i> Agora</button>
                <button type="button" wire:click="$set('scheduling', true)" class="{{ $scheduling ? 'on' : '' }}" data-testid="igc-schedule-tab"><i class="bi bi-calendar-event"></i> Agendar</button>
            </div>
            @if($scheduling)
                <div class="igc-when-fields">
                    <input type="date" wire:model="scheduleDate" class="igc-input" min="{{ now(\App\Models\Promotion::tz())->format('Y-m-d') }}">
                    <input type="time" wire:model="scheduleTime" class="igc-input">
                </div>
                <p class="igc-hint">Horário de Brasília. O app posta sozinho na hora (precisa do cron ligado no servidor).</p>
            @endif
            @error('schedule') <p class="igc-err">{{ $message }}</p> @enderror
            @error('publish') <div class="igc-fail" data-testid="igc-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}</div> @enderror

            <div class="igc-actions">
                @if($scheduling)
                    <button type="button" wire:click="saveSchedule" wire:loading.attr="disabled" wire:target="saveSchedule" class="igc-btn-main" data-testid="igc-save-schedule">
                        <i class="bi bi-calendar-check" wire:loading.remove wire:target="saveSchedule"></i><i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="saveSchedule"></i>
                        {{ $postId ? 'Salvar agendamento' : 'Agendar post' }}
                    </button>
                @else
                    <button type="button" wire:click="postNow" wire:loading.attr="disabled" wire:target="postNow" class="igc-btn-main" data-testid="igc-post-now">
                        <i class="bi bi-send" wire:loading.remove wire:target="postNow"></i><i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="postNow"></i>
                        <span wire:loading.remove wire:target="postNow">{{ $kind === 'story' ? (count($picked) > 1 ? 'Postar ' . count($picked) . ' stories' : 'Postar story') : 'Postar agora' }}</span>
                        <span wire:loading wire:target="postNow">Postando…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>


    <style>
        .igc-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); grid-template-areas: "prod" "photos" "preview" "opts"; }
        .igc-prod { grid-area: prod; } .igc-photos { grid-area: photos; } .igc-preview-wrap { grid-area: preview; } .igc-opts { grid-area: opts; }
        @media (min-width: 1024px) {
            .igc-grid { grid-template-columns: minmax(0, 1fr) 380px; grid-template-rows: auto auto 1fr; grid-template-areas: "prod preview" "photos preview" "opts preview"; align-items: start; }
            .igc-preview-wrap { position: sticky; top: 1rem; align-self: start; }
        }
        @media (min-width: 1536px) { .igc-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 420px; grid-template-rows: auto 1fr; grid-template-areas: "prod opts preview" "photos opts preview"; } }
        @media (min-width: 2200px) { .igc-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 460px; max-width: 2000px; margin: 0 auto; } }
        .igc-card { min-width: 0; padding: 1rem 1.1rem; border-radius: 1.4rem; background: #fff; border: 1px solid rgb(226 232 240 / .9); box-shadow: 0 10px 30px -22px rgb(79 70 229 / .35); }
        .dark .igc-card { background: rgb(15 23 42 / .85); border-color: rgb(51 65 85 / .7); }
        .igc-head { display: flex; align-items: center; gap: .6rem; margin-bottom: .7rem; }
        .igc-head h2 { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .dark .igc-head h2 { color: #f1f5f9; }
        .igc-num { flex: none; display: grid; place-items: center; width: 1.9rem; height: 1.9rem; border-radius: .7rem; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); color: #fff; font-weight: 900; font-size: .85rem; }
        .igc-count { margin-left: auto; font-size: .75rem; font-weight: 800; color: #be185d; }
        .igc-link { margin-left: auto; font-size: .78rem; font-weight: 700; color: #db2777; display: inline-flex; align-items: center; gap: .25rem; }
        .dark .igc-link { color: #f9a8d4; }
        .igc-hint { font-size: .74rem; color: #64748b; margin-top: .35rem; }
        .dark .igc-hint { color: #94a3b8; }
        .igc-err { color: #ef4444; font-size: .76rem; margin-top: .35rem; }
        .igc-label { display: inline-flex; align-items: center; gap: .35rem; font-size: .76rem; font-weight: 800; color: #475569; }
        .dark .igc-label { color: #cbd5e1; }
        .igc-input { width: 100%; border-radius: .9rem; border: 1px solid #e2e8f0; background: #fff; padding: .6rem .8rem; font-size: .88rem; color: #0f172a; }
        .igc-input:focus { outline: none; border-color: #f472b6; box-shadow: 0 0 0 3px rgba(244, 114, 182, .2); }
        .dark .igc-input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        .igc-textarea { line-height: 1.45; resize: vertical; min-height: 12rem; }
        .igc-warn { display: flex; align-items: center; gap: .8rem; margin-bottom: 1rem; padding: .85rem 1rem; border-radius: 1.1rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .igc-warn > i:first-child { font-size: 1.4rem; }
        .igc-warn span { flex: 1; min-width: 0; } .igc-warn b { display: block; font-size: .9rem; } .igc-warn small { display: block; font-size: .76rem; }
        .dark .igc-warn { background: rgba(245, 158, 11, .1); border-color: rgba(245, 158, 11, .3); color: #fcd34d; }
        .igc-product { display: flex; align-items: center; gap: .8rem; }
        .igc-product-img { width: 4rem; height: 4rem; border-radius: .9rem; object-fit: cover; background: #f8fafc; border: 1px solid #e2e8f0; flex: none; }
        .igc-promo { display: block; color: #e11d48; font-size: .76rem; }
        .igc-results { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 210px), 1fr)); gap: .45rem; margin-top: .6rem; max-height: 19rem; overflow-y: auto; }
        .igc-result { display: flex; align-items: center; gap: .55rem; padding: .45rem; border-radius: .9rem; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; font-size: .8rem; color: #0f172a; }
        .igc-result:hover { border-color: #f9a8d4; background: #fdf2f8; }
        .igc-result img { width: 2.6rem; height: 2.6rem; border-radius: .6rem; object-fit: cover; background: #fff; flex: none; }
        .igc-result small { color: #64748b; font-size: .7rem; }
        .dark .igc-result { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .igc-pool { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: .5rem; }
        .igc-thumb { position: relative; aspect-ratio: 1; border-radius: .9rem; overflow: hidden; border: 2px solid transparent; background: #f1f5f9; transition: transform .12s; }
        .igc-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .igc-thumb:not(.is-on) img { opacity: .75; }
        .igc-thumb.is-on { border-color: #db2777; box-shadow: 0 8px 18px -10px #db2777; }
        .igc-thumb:active { transform: scale(.96); }
        .igc-thumb-n { position: absolute; top: .3rem; right: .3rem; display: grid; place-items: center; width: 1.45rem; height: 1.45rem; border-radius: 999px; background: #db2777; color: #fff; font-size: .72rem; font-weight: 900; box-shadow: 0 0 0 2px #fff; }
        .igc-thumb-tag { position: absolute; left: .3rem; bottom: .3rem; padding: 0 .35rem; border-radius: .4rem; background: rgba(15, 23, 42, .7); color: #fff; font-size: .6rem; font-weight: 700; }
        .igc-upload { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .2rem; cursor: pointer; border: 2px dashed #f9a8d4; background: #fdf2f8; color: #be185d; font-size: 1.3rem; }
        .igc-upload small { font-size: .66rem; font-weight: 700; }
        .dark .igc-thumb { background: #1e293b; } .dark .igc-upload { background: rgba(236, 72, 153, .08); border-color: rgba(236, 72, 153, .35); color: #f9a8d4; }
        .igc-order { margin-top: .9rem; }
        .igc-order-row { display: flex; gap: .45rem; overflow-x: auto; padding: .4rem 0 .2rem; }
        .igc-order-item { position: relative; flex: none; width: 4.6rem; }
        .igc-order-item img { width: 4.6rem; height: 4.6rem; border-radius: .75rem; object-fit: cover; }
        .igc-order-item > span { position: absolute; top: .2rem; left: .2rem; display: grid; place-items: center; width: 1.25rem; height: 1.25rem; border-radius: 999px; background: #db2777; color: #fff; font-size: .65rem; font-weight: 900; }
        .igc-order-item > div { display: flex; justify-content: space-between; margin-top: .2rem; }
        .igc-order-item button { display: grid; place-items: center; width: 2.1rem; height: 1.8rem; border-radius: .55rem; background: #f1f5f9; color: #334155; }
        .igc-order-item button:disabled { opacity: .35; }
        .dark .igc-order-item button { background: #1e293b; color: #e2e8f0; }
        .igc-art { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; margin-top: 1rem; padding: .8rem .9rem; border-radius: 1rem; background: linear-gradient(135deg, rgba(245, 133, 41, .1), rgba(221, 42, 123, .1), rgba(129, 52, 175, .1)); border: 1px solid #fbcfe8; }
        .dark .igc-art { border-color: rgba(236, 72, 153, .3); }
        .igc-art b { display: block; font-size: .88rem; color: #9d174d; } .dark .igc-art b { color: #f9a8d4; }
        .igc-art small { display: block; font-size: .74rem; color: #64748b; }
        .igc-swatches { display: flex; gap: .35rem; margin-top: .45rem; }
        .igc-swatches button { width: 1.6rem; height: 1.6rem; border-radius: 999px; border: 2px solid #fff; box-shadow: 0 0 0 1px #e2e8f0; }
        .igc-swatches button.on { box-shadow: 0 0 0 2px #db2777; }
        .igc-btn-art { display: inline-flex; align-items: center; gap: .4rem; padding: .6rem 1rem; border-radius: .9rem; background: #fff; color: #be185d; font-weight: 800; font-size: .85rem; border: 1.5px solid #f9a8d4; }
        .dark .igc-btn-art { background: #111827; color: #f9a8d4; }
        .igc-seg3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; }
        .igc-seg3 button { display: flex; flex-direction: column; align-items: center; gap: .15rem; padding: .7rem .4rem; border-radius: 1rem; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #334155; }
        .igc-seg3 button b { font-size: .82rem; } .igc-seg3 button small { font-size: .66rem; color: #64748b; }
        .igc-seg3 button.on { border-color: #db2777; background: #fdf2f8; color: #9d174d; }
        .dark .igc-seg3 button { background: #111827; border-color: #334155; color: #e2e8f0; }
        .dark .igc-seg3 button.on { background: rgba(236, 72, 153, .12); border-color: #ec4899; color: #f9a8d4; }
        .igc-shape { display: block; height: 1.9rem; border-radius: .3rem; border: 2px solid currentColor; opacity: .7; margin-bottom: .15rem; }
        .igc-row { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; margin-top: .8rem; }
        .igc-seg { display: inline-flex; gap: .2rem; padding: .2rem; border-radius: .9rem; background: #f1f5f9; }
        .igc-seg button { padding: .42rem .8rem; border-radius: .7rem; font-size: .8rem; font-weight: 700; color: #475569; display: inline-flex; align-items: center; gap: .35rem; }
        .igc-seg button.on { background: #fff; color: #be185d; box-shadow: 0 2px 8px -3px rgba(15, 23, 42, .25); }
        .dark .igc-seg { background: #1e293b; } .dark .igc-seg button { color: #cbd5e1; } .dark .igc-seg button.on { background: #0f172a; color: #f9a8d4; }
        .igc-bgs { display: flex; gap: .35rem; }
        .igc-bgs button { width: 1.7rem; height: 1.7rem; border-radius: .55rem; border: 1px solid #cbd5e1; }
        .igc-bgs button.on { box-shadow: 0 0 0 2px #db2777; }
        .igc-meta { display: flex; align-items: center; gap: .8rem; font-size: .72rem; color: #64748b; margin-top: .3rem; flex-wrap: wrap; }
        .igc-dates { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem; margin-bottom: .55rem; }
        .igc-dates button { padding: .28rem .6rem; border-radius: 999px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: .74rem; font-weight: 700; color: #334155; }
        .igc-dates button.is-now { border-color: #f9a8d4; background: #fdf2f8; color: #be185d; }
        .dark .igc-dates button { background: #111827; border-color: #334155; color: #e2e8f0; }
        .igc-when { display: flex; width: 100%; } .igc-when button { flex: 1; justify-content: center; }
        .igc-when-fields { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: .5rem; margin-top: .6rem; }
        .igc-fail { margin-top: .8rem; display: flex; gap: .5rem; align-items: flex-start; padding: .7rem .9rem; border-radius: .9rem; background: rgba(239, 68, 68, .1); color: #b91c1c; font-size: .83rem; font-weight: 600; }
        .dark .igc-fail { color: #fca5a5; }
        .igc-actions { margin-top: 1rem; }
        .igc-btn-main { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .85rem 1rem; border-radius: 1rem; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); color: #fff; font-size: .95rem; font-weight: 800; box-shadow: 0 14px 28px -16px #8134af; }
        .igc-btn-main:disabled { opacity: .7; }
        /* Prévia */
        .igc-preview { max-width: 380px; margin: 0 auto; border-radius: 1.3rem; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 24px 50px -30px rgba(15, 23, 42, .6); color: #0f172a; }
        .dark .igc-preview { background: #000; border-color: #262626; color: #f5f5f5; }
        .igc-preview.is-story { max-width: 320px; background: #000; border: 0; }
        .igc-post-top { display: flex; align-items: center; gap: .55rem; padding: .6rem .75rem; font-size: .82rem; }
        .igc-av { width: 2rem; height: 2rem; border-radius: 999px; padding: 2px; background: linear-gradient(135deg, #feda75, #f58529, #dd2a7b, #8134af); flex: none; overflow: hidden; }
        .igc-av img { width: 100%; height: 100%; border-radius: 999px; object-fit: cover; border: 2px solid #fff; }
        .igc-media-box, .igc-story { position: relative; overflow: hidden; }
        .igc-media { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; }
        .igc-media.is-cover { object-fit: cover; }
        .igc-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .3rem; color: #94a3b8; font-size: .8rem; }
        .igc-empty i { font-size: 2rem; }
        .igc-pager { position: absolute; top: .6rem; right: .6rem; padding: .1rem .5rem; border-radius: 999px; background: rgba(0, 0, 0, .6); color: #fff; font-size: .7rem; font-weight: 700; }
        .igc-nav { position: absolute; top: 50%; transform: translateY(-50%); display: grid; place-items: center; width: 1.8rem; height: 1.8rem; border-radius: 999px; background: rgba(255, 255, 255, .9); color: #111; box-shadow: 0 2px 8px rgba(0, 0, 0, .2); font-size: .8rem; }
        .igc-nav.left { left: .5rem; } .igc-nav.right { right: .5rem; }
        .igc-post-icons { display: flex; align-items: center; gap: .9rem; padding: .6rem .75rem .2rem; font-size: 1.25rem; position: relative; }
        .igc-dots { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: .2rem; }
        .igc-dots span { width: .35rem; height: .35rem; border-radius: 999px; background: #cbd5e1; } .igc-dots span.on { background: #3b82f6; }
        .igc-caption { padding: .2rem .75rem .85rem; font-size: .8rem; line-height: 1.4; overflow-wrap: anywhere; }
        .igc-story-bars { position: absolute; z-index: 2; top: .5rem; left: .5rem; right: .5rem; display: flex; gap: .2rem; }
        .igc-story-bars span { flex: 1; height: 3px; border-radius: 3px; background: rgba(255, 255, 255, .4); } .igc-story-bars span.on { background: #fff; }
        .igc-story-user { position: absolute; z-index: 2; top: 1rem; left: .6rem; display: flex; align-items: center; gap: .45rem; color: #fff; font-size: .78rem; text-shadow: 0 1px 3px rgba(0, 0, 0, .5); }
        .igc-story-user small { opacity: .75; }
        .igc-tap { position: absolute; z-index: 1; top: 0; bottom: 0; width: 40%; } .igc-tap.left { left: 0; } .igc-tap.right { right: 0; }
        @media (max-width: 640px) {
            .igc-card { padding: .9rem; border-radius: 1.2rem; }
            .igc-pool { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .igc-preview { max-width: 100%; }
            .igc-preview.is-story { max-width: 270px; }
            .igc-actions { position: sticky; bottom: calc(env(safe-area-inset-bottom, 0px) + 78px); z-index: 20; }
        }
    </style>
</div>
