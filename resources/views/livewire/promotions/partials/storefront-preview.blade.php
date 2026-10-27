{{-- Prévia do catálogo inteiro numa janela (iframe em escala): botão flutuante "Prévia do site" e o evento
     open-site-preview (botões "Ver como fica" das abas). Com um tema escolhido e ainda não aplicado, mostra o
     site já com o tema (só o dono vê). data-src muda a cada mudança salva, então a prévia abre sempre atualizada. --}}
@php
    $pvTheme = $pendingTheme !== '' && isset(\App\Services\Portal\HomeLayout::THEMES[$pendingTheme]) ? $pendingTheme : null;
    $pvQuery = ['previa' => $previewKey];
    if ($pvTheme) {
        $pvQuery += ['tema' => $pvTheme, 'com_banner' => $withBanner ? 1 : 0, 'com_aviso' => $withNotice ? 1 : 0];
    }
    $pvSrc = $catalogUrl . (str_contains($catalogUrl, '?') ? '&' : '?') . http_build_query($pvQuery);
    $pvName = $pvTheme ? \App\Services\Portal\HomeLayout::THEMES[$pvTheme]['emoji'] . ' ' . \App\Services\Portal\HomeLayout::THEMES[$pvTheme]['name'] : null;
    $pvDevices = ['pc' => ['Computador', 'bi-display', 1280], 'tab' => ['Tablet', 'bi-tablet', 820], 'cel' => ['Celular', 'bi-phone', 390]];
@endphp

<div data-pv-src="{{ $pvSrc }}" data-pv-name="{{ $pvName }}"
     x-data="{
        open: false, dev: window.innerWidth < 768 ? 'cel' : (window.innerWidth < 1100 ? 'tab' : 'pc'), w: 0, h: 0, src: '', name: '', sizes: { pc: 1280, tab: 820, cel: 390 },
        show() { this.src = this.$root.dataset.pvSrc; this.name = this.$root.dataset.pvName; this.open = true; document.documentElement.style.overflow = 'hidden'; this.$nextTick(() => this.measure()); },
        hide() { this.open = false; this.src = ''; document.documentElement.style.overflow = ''; },
        measure() { if (this.$refs.box) { this.w = this.$refs.box.clientWidth; this.h = this.$refs.box.clientHeight; } },
        frame() { const iw = this.sizes[this.dev]; const sc = Math.min(1, this.w / iw); const left = Math.max(0, (this.w - iw * sc) / 2); return `width:${iw}px;height:${Math.round(this.h / sc)}px;transform:scale(${sc});left:${left}px`; },
     }"
     x-init="new ResizeObserver(() => measure()).observe($refs.box)"
     @open-site-preview.window="show()" @keydown.escape.window="open && hide()">
    <button type="button" @click="show()" data-testid="preview-open"
            class="fixed z-[60] right-4 bottom-24 lg:bottom-6 inline-flex items-center gap-2 rounded-full px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 shadow-lg shadow-violet-500/30 hover:-translate-y-0.5 transition">
        <i class="bi bi-eye"></i>Prévia do site
    </button>
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[98] bg-slate-900/75 backdrop-blur-sm flex flex-col p-2 sm:p-4" data-testid="preview-sheet" role="dialog" aria-modal="true" aria-label="Prévia do site">
        <div class="flex items-center gap-2 pb-2 text-white flex-wrap">
            <b class="text-sm"><i class="bi bi-eye"></i> Como o cliente vê</b>
            <span x-show="name" class="rounded-full px-2 py-0.5 text-[11px] font-semibold bg-white/20" data-testid="preview-theme">Tema <span x-text="name"></span> (ainda não aplicado)</span>
            <div class="ml-auto inline-flex rounded-lg bg-white/15 p-0.5 text-xs font-semibold">
                @foreach($pvDevices as $dk => [$dn, $di])
                    <button type="button" @click="dev = '{{ $dk }}'" :class="dev === '{{ $dk }}' ? 'bg-white text-violet-700' : 'text-white'" class="rounded-md px-2.5 py-1 {{ $dk === 'tab' ? 'hidden sm:inline-block' : '' }}" data-testid="preview-dev-{{ $dk }}"><i class="bi {{ $di }}"></i><span class="hidden min-[420px]:inline"> {{ $dn }}</span></button>
                @endforeach
            </div>
            <a :href="src.replace(/[?&]previa=\d+/, '')" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 inline-flex items-center justify-center" title="Abrir em outra aba"><i class="bi bi-box-arrow-up-right"></i></a>
            <button type="button" @click="hide()" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25" aria-label="Fechar" data-testid="preview-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div x-ref="box" class="relative flex-1 overflow-hidden rounded-2xl bg-white">
            <template x-if="open && src">
                <iframe :src="src" title="Prévia do catálogo" class="absolute top-0 border-0 bg-white origin-top-left" :style="frame()" data-testid="home-preview-frame"></iframe>
            </template>
        </div>
    </div>
</div>
