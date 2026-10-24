{{-- Um banner da vitrine. $b = PortalBanner, $kind = hero|strip|side. Sem imagem vira banner de texto no fundo colorido. --}}
@php
    $front = \App\Services\Portal\StorefrontService::class;
    $href = $front::href($b, $owner);
    $img = $b->imageUrl();
    $mob = $kind !== 'side' ? $b->mobileUrl() : null;
    $tag = $href ? 'a' : 'div';
    $hasText = $b->title || $b->subtitle || $b->button_text;
    $design = $b->isDesign();
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @if($front::external($href)) target="_blank" rel="noopener" @endif @endif
    class="sf-ban sf-{{ $kind === 'side' ? 'sb' : $kind }} {{ $design ? 'has-design' : ($img ? 'has-img' : 'no-img') }} {{ $mob ? 'has-m' : '' }}"
    style="--sf-bg: {{ $front::gradient($b->color) }}" data-testid="banner-{{ $kind }}"
    @if(! $hasText && $img) aria-label="{{ $b->title ?: 'Banner' }}" @endif>
    @if($design)
        @include('portal.partials.storefront-design', ['tpl' => $b->design['tpl'], 'c1' => $b->design['c1'] ?? null, 'c2' => $b->design['c2'] ?? null, 'acc' => $b->design['acc'] ?? null, 'ink' => $b->design['ink'] ?? 'light',
            'badge' => $b->design['badge'] ?? null, 'lay' => $b->design['lay'] ?? 'esq', 'title' => $b->title, 'subtitle' => $b->subtitle, 'button' => $b->button_text, 'photos' => $b->designPhotos()])
    @elseif($img)
        <picture>
            @if($mob)<source media="(max-width: 767px)" srcset="{{ $mob }}">@endif
            <img src="{{ $img }}" alt="{{ $b->title ?: '' }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" decoding="async">
        </picture>
    @endif
    @if($hasText && ! $design)
        <span class="sf-txt">
            @if($b->title)<b>{{ $b->title }}</b>@endif
            @if($b->subtitle)<span>{{ $b->subtitle }}</span>@endif
            @if($b->button_text)<em class="sf-cta">{{ $b->button_text }} <i class="fas fa-arrow-right"></i></em>@endif
        </span>
    @endif
</{{ $tag }}>
