{{-- Prévia do catálogo inteiro (iframe em escala). $inline: na coluna da Página inicial; senão, botão flutuante que abre em tela cheia.
     Com um tema escolhido e ainda não aplicado, mostra o site já com o tema (só o dono vê). --}}
@php
    $pvTheme = $pendingTheme !== '' && isset(\App\Services\Portal\HomeLayout::THEMES[$pendingTheme]) ? $pendingTheme : null;
    $pvQuery = ['previa' => $previewKey];
    if ($pvTheme) {
        $pvQuery += ['tema' => $pvTheme, 'com_banner' => $withBanner ? 1 : 0, 'com_aviso' => $withNotice ? 1 : 0];
    }
    $pvSrc = $catalogUrl . (str_contains($catalogUrl, '?') ? '&' : '?') . http_build_query($pvQuery);
    $pvName = $pvTheme ? \App\Services\Portal\HomeLayout::THEMES[$pvTheme]['emoji'] . ' ' . \App\Services\Portal\HomeLayout::THEMES[$pvTheme]['name'] : null;
    $pvFrame = "(() => { const iw = dev === 'pc' ? 1280 : 390; const sc = Math.min(1, w / iw); const left = Math.max(0, (w - iw * sc) / 2); return `width:\${iw}px;height:\${Math.round(\$refs.box.clientHeight / sc)}px;transform:scale(\${sc});left:\${left}px`; })()";
@endphp

@if($inline)
    <aside class="min-w-0 lg:sticky lg:top-4 {{ $card }} p-3 space-y-2" x-data="{ dev: 'pc', w: 0 }" x-init="w = $refs.box.clientWidth; new ResizeObserver(() => w = $refs.box.clientWidth).observe($refs.box)" data-testid="home-preview">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-eye text-violet-500"></i> Como fica</span>
            @if($pvName)<span class="rounded-full px-2 py-0.5 text-[11px] font-semibold bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-200" data-testid="preview-theme">Prévia: {{ $pvName }} (ainda não aplicado)</span>@endif
            <div class="ml-auto inline-flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 shadow text-violet-600' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-display"></i> Computador</button>
                <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 shadow text-violet-600' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-phone"></i> Celular</button>
            </div>
        </div>
        <div x-ref="box" class="relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 h-[560px] lg:h-[calc(100vh-150px)] lg:max-h-[820px]">
            <iframe wire:key="pv-{{ md5($pvSrc) }}" src="{{ $pvSrc }}" title="Prévia do catálogo" loading="lazy" data-testid="home-preview-frame"
                    class="absolute top-0 border-0 bg-white origin-top-left" :style="{{ $pvFrame }}"></iframe>
        </div>
        <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }} w-full justify-center"><i class="bi bi-box-arrow-up-right text-violet-500"></i>Abrir o catálogo</a>
    </aside>
@else
    <div x-data="{ open: false, dev: window.innerWidth < 768 ? 'cel' : 'pc', w: 0 }" @keydown.escape.window="open = false">
        <button type="button" @click="open = true; $nextTick(() => { w = $refs.box.clientWidth })" data-testid="preview-open"
                class="fixed z-[60] right-4 bottom-24 lg:bottom-6 inline-flex items-center gap-2 rounded-full px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 shadow-lg shadow-violet-500/30">
            <i class="bi bi-eye"></i>Prévia do site
        </button>
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[98] bg-slate-900/70 backdrop-blur-sm flex flex-col p-2 sm:p-4" data-testid="preview-sheet">
            <div class="flex items-center gap-2 pb-2 text-white">
                <b class="text-sm"><i class="bi bi-eye"></i> Como o site está agora</b>
                <div class="ml-auto inline-flex rounded-lg bg-white/15 p-0.5 text-xs font-semibold">
                    <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white text-violet-700' : 'text-white'" class="rounded-md px-2.5 py-1"><i class="bi bi-display"></i> Computador</button>
                    <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white text-violet-700' : 'text-white'" class="rounded-md px-2.5 py-1"><i class="bi bi-phone"></i> Celular</button>
                </div>
                <button type="button" @click="open = false" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
            </div>
            <div x-ref="box" class="relative flex-1 overflow-hidden rounded-2xl bg-white" x-init="new ResizeObserver(() => w = $refs.box.clientWidth).observe($refs.box)">
                <template x-if="open">
                    <iframe src="{{ $pvSrc }}" title="Prévia do catálogo" class="absolute top-0 border-0 bg-white origin-top-left" :style="{{ $pvFrame }}"></iframe>
                </template>
            </div>
        </div>
    </div>
@endif
