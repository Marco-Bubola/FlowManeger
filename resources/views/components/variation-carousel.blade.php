@props(['items', 'start' => 0, 'note' => null])
{{--
    Um card só para o grupo de variações: as variações passam como carrossel
    (setas, bolinhas, arrastar no celular). Cada página coloca os próprios cards no slot:
        <div class="vc-slide" x-show="vi === {{ $k }}" @if($k !== $start) x-cloak @endif> ...card... </div>
--}}
@php
    $labels = collect($items)->values()->map(function ($p) {
        $v = trim((string) ($p->variation_value ?? ''));
        return $v !== '' ? $v : ($p->is_variation_parent ? 'Principal' : $p->product_code);
    });
    $n = $labels->count();
@endphp
@once
<style>
    .vc { position: relative; display: flex; flex-direction: column; min-width: 0; container-type: inline-size; }
    .vc > .vc-slide { flex: 1; display: flex; flex-direction: column; }
    .vc > .vc-slide > * { flex: 1; }
    .vc-bar { display: flex; align-items: center; gap: .35rem; margin-top: .4rem; padding: .25rem; border-radius: 999px;
        background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.22); user-select: none; }
    .dark .vc-bar { background: rgba(139,92,246,.16); border-color: rgba(139,92,246,.32); }
    .vc-nav { width: 1.85rem; height: 1.85rem; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        background: #fff; color: #6d28d9; font-size: .85rem; box-shadow: 0 2px 6px rgba(76,29,149,.15); transition: transform .15s; }
    .dark .vc-nav { background: rgba(30,41,59,.95); color: #c4b5fd; }
    .vc-nav:active { transform: scale(.9); }
    .vc-mid { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: .2rem; }
    .vc-label { max-width: 100%; font-size: .72rem; font-weight: 800; color: #5b21b6; line-height: 1.1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dark .vc-label { color: #ddd6fe; }
    .vc-label small { font-weight: 600; opacity: .7; }
    .vc-dots { display: flex; gap: .2rem; max-width: 100%; overflow: hidden; }
    .vc-dot { width: .38rem; height: .38rem; border-radius: 999px; background: rgba(109,40,217,.25); transition: all .2s; flex-shrink: 0; }
    .vc-dot.on { width: .95rem; background: #7c3aed; }
    /* Card estreito (3 por linha no celular): barra enxuta, só nome e contador. */
    @container (max-width: 150px) {
        .vc-bar { gap: .15rem; padding: .15rem; }
        .vc-nav { width: 1.45rem; height: 1.45rem; font-size: .7rem; }
        .vc-label { font-size: .62rem; }
        .vc-label .bi, .vc-label .vc-extra { display: none; }
        .vc-dots { display: none; }
    }
</style>
@endonce
<div class="vc" x-data="{ vi: {{ (int) $start }}, n: {{ $n }}, sx: null, go(d) { this.vi = (this.vi + d + this.n) % this.n } }"
     @touchstart.passive="sx = $event.touches[0].clientX"
     @touchend="if (sx !== null) { const dx = $event.changedTouches[0].clientX - sx; if (Math.abs(dx) > 45) go(dx < 0 ? 1 : -1); sx = null }"
     {{ $attributes }}>
    {{ $slot }}
    <div class="vc-bar" @click.stop>
        <button type="button" class="vc-nav" @click.stop="go(-1)" aria-label="Variação anterior"><i class="bi bi-chevron-left"></i></button>
        <div class="vc-mid">
            <span class="vc-label"><i class="bi bi-diagram-3"></i> <span x-text="@js($labels->all())[vi]">{{ $labels[(int) $start] ?? '' }}</span> <small x-text="(vi + 1) + '/' + n">{{ (int) $start + 1 }}/{{ $n }}</small>@if($note) <small class="vc-extra">· {{ $note }}</small>@endif</span>
            <span class="vc-dots">
                @foreach($labels as $k => $l)
                    <button type="button" class="vc-dot" :class="{ on: vi === {{ $k }} }" @click.stop="vi = {{ $k }}" aria-label="{{ $l }}"></button>
                @endforeach
            </span>
        </div>
        <button type="button" class="vc-nav" @click.stop="go(1)" aria-label="Próxima variação"><i class="bi bi-chevron-right"></i></button>
    </div>
</div>
