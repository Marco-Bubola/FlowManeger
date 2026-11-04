{{-- Título em duas partes dos modelos da página inicial. Editorial: maiúsculas condensadas. Elegante: letra fina com a 2ª parte em itálico. --}}
@if(($hModel ?? 'atual') === 'botanico')
    <h2 class="hm-h"><span>{{ $t1 }}</span> <b aria-hidden="true">✦</b> <em>{{ $t2 }}</em></h2>
@else
    <h2 class="hm-h"><span>{{ $t1 }}</span> <em>{{ $t2 }}</em></h2>
@endif
