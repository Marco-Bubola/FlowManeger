{{-- Capa de uma coleção: modelo de banner (com as fotos dos produtos dela), imagem própria ou cor.
     $c = PortalCollection, $owner, $href (link "Ver tudo"; null na própria página da coleção) --}}
@php
    $front = \App\Services\Portal\StorefrontService::class;
    $tag = $href ? 'a' : 'div';
@endphp
@if($c->hasDesign())
    @php
        $d = $c->design + ['c1' => null, 'c2' => null, 'acc' => null, 'ink' => 'light', 'badge' => null, 'lay' => 'esq', 'anim' => 'none'];
        $photos = $front::collectionPhotos($c, $owner, $front::photoCap($d['tpl'], $d['anim']));
    @endphp
    <{{ $tag }} @if($href) href="{{ $href }}" @endif class="sf-ban sf-strip has-design sf-col-ban">
        @include('portal.partials.storefront-design', ['tpl' => $d['tpl'], 'c1' => $d['c1'], 'c2' => $d['c2'], 'acc' => $d['acc'], 'ink' => $d['ink'], 'lay' => $d['lay'], 'anim' => $d['anim'],
            'badge' => $d['badge'] ?: 'COLEÇÃO', 'title' => $c->name, 'subtitle' => $c->description, 'button' => $href ? 'Ver tudo' : null, 'photos' => $photos])
    </{{ $tag }}>
@else
    <{{ $tag }} @if($href) href="{{ $href }}" @endif class="sf-col-head {{ $c->image ? 'has-img' : 'no-img' }}" style="--sf-bg: {{ $front::gradient($c->color) }}">
        @if($c->image)<img src="{{ $c->imageUrl() }}" alt="" loading="lazy">@endif
        <span class="sf-col-txt">
            <small>Coleção</small>
            <b>{{ $c->name }}</b>
            @if($c->description)<span>{{ $c->description }}</span>@endif
        </span>
        @if($href)<span class="sf-col-go">Ver tudo</span>@endif
    </{{ $tag }}>
@endif
