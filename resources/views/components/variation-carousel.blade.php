@props(['items', 'start' => 0, 'note' => null])
{{--
    Um card só para o grupo de variações: as variações passam como carrossel
    (setas e nome da variação numa barra logo abaixo do nome do produto, arrastar no celular). Cada página coloca os próprios cards no slot, e o card põe x-variation-carousel-nav abaixo do nome:
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
    /* Barra das variações dentro do card, abaixo do nome (x-variation-carousel-nav): o card do
       carrossel fica do mesmo tamanho dos outros da grade. */
    .vc .vcn { box-sizing: border-box; position: relative; z-index: 3; display: flex; align-items: center; gap: .3rem; margin: .45rem auto 0; width: 100%; max-width: 15rem; padding: .2rem;
        border-radius: 999px; background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.25); user-select: none; }
    .dark .vcn { background: rgba(139,92,246,.18); border-color: rgba(167,139,250,.35); }
    .vc .vcn .vcn-arrow { width: 1.6rem; height: 1.6rem; padding: 0; margin: 0; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #fff; background: linear-gradient(135deg, #8b5cf6, #a855f7); box-shadow: 0 2px 6px rgba(124,58,237,.3); transition: transform .15s; }
    .vc .vcn .vcn-arrow:hover { transform: scale(1.08); }
    .vc .vcn .vcn-arrow:active { transform: scale(.9); }
    .vcn-mid { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; line-height: 1.15; }
    .vcn-name { max-width: 100%; font-size: .68rem; font-weight: 800; color: #5b21b6; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dark .vcn-name { color: #ddd6fe; }
    .vcn-meta { display: flex; align-items: center; gap: .3rem; font-size: .6rem; font-weight: 700; color: #7c3aed; }
    .dark .vcn-meta { color: #c4b5fd; }
    .vcn-note { padding: 0 .3rem; border-radius: 999px; background: #7c3aed; color: #fff; }
    .vcn-dots { display: flex; gap: .15rem; }
    .vcn-dot { width: .3rem; height: .3rem; border-radius: 999px; background: rgba(124,58,237,.3); transition: all .2s; }
    .vcn-dot.on { width: .75rem; background: #7c3aed; }
    .dark .vcn-dot { background: rgba(196,181,253,.35); } .dark .vcn-dot.on { background: #c4b5fd; }
    @container (max-width: 170px) {
        .vc .vcn { gap: .15rem; padding: .12rem; margin-top: .3rem; }
        .vc .vcn .vcn-arrow { width: 1.3rem; height: 1.3rem; font-size: .62rem; }
        .vcn-name { font-size: .58rem; } .vcn-name .bi, .vcn-dots, .vcn-note { display: none; }
    }
</style>
@endonce
<div class="vc" x-data="{ vi: {{ (int) $start }}, n: {{ $n }}, labels: @js($labels->all()), note: @js($note), sx: null, go(d) { this.vi = (this.vi + d + this.n) % this.n } }"
     @touchstart.passive="sx = $event.touches[0].clientX"
     @touchend="if (sx !== null) { const dx = $event.changedTouches[0].clientX - sx; if (Math.abs(dx) > 45) go(dx < 0 ? 1 : -1); sx = null }"
     {{ $attributes }}>
    {{ $slot }}
</div>
