{{-- Produtos de um bloco da página inicial: faixa de arrastar (como sempre) ou grade --}}
@if(($hs[$k]['layout'] ?? 'faixa') === 'grade')
    <div class="ml-grid sf-sec-grid">
        @foreach($list as $p)
            @include('portal.partials.ml-card', ['d' => $all[$p->id]])
        @endforeach
    </div>
@else
    <div class="ml-rail">
        @foreach($list as $p)
            @include('portal.partials.ml-card', ['d' => $all[$p->id]])
        @endforeach
    </div>
@endif
