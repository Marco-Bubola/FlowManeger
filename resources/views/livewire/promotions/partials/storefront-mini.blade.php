{{-- Miniatura de um banner de modelo: desenha no tamanho real e encolhe (fica igual ao catálogo).
     $kind = hero|strip|side, $scale, e os dados do modelo: $tpl, $c1, $c2, $acc, $ink, $badge, $title, $subtitle, $button, $photos (urls) --}}
@php
    [$iw, $ih] = ['hero' => [640, 196], 'strip' => [640, 96], 'side' => [160, 600]][$kind] ?? [640, 196];
@endphp
<span class="relative block overflow-hidden rounded-xl pointer-events-none" style="width: {{ round($iw * $scale) }}px; height: {{ round($ih * $scale) }}px">
    <span class="sf-ban sf-{{ $kind === 'side' ? 'sb' : $kind }} has-design absolute left-0 top-0 origin-top-left" style="width: {{ $iw }}px; transform: scale({{ $scale }}); border-radius: {{ round(14 / $scale) }}px">
        @include('portal.partials.storefront-design', compact('tpl', 'c1', 'c2', 'acc', 'ink', 'badge', 'title', 'subtitle', 'button', 'photos') + ['lay' => $lay ?? 'esq', 'anim' => $anim ?? 'none'])
    </span>
</span>
