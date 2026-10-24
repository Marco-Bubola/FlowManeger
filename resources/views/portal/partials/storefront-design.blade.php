{{-- Banner de modelo pronto, desenhado em HTML (nítido em qualquer tela).
     $tpl, $c1, $c2, $acc, $ink, $badge, $title, $subtitle, $button, $photos (urls), $kind = hero|strip|side --}}
@php
    $tpl = $tpl ?: 'oferta';
    $photos = array_values(array_filter($photos ?? []));
    $n = $tpl === 'vitrine' ? min(count($photos), 3) : min(count($photos), 1);
    $hex = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
    $svg = [
        'bolt'  => '<svg viewBox="0 0 24 24"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>',
        'heart' => '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg>',
        'truck' => '<svg viewBox="0 0 24 24"><path d="M2 5h12v10H2zM14 9h4l3 3v3h-7zM6 19.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm11 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>',
        'star'  => '<svg viewBox="0 0 24 24"><path d="m12 2 2.9 6.6 7.1.6-5.4 4.7 1.6 7L12 17.3 5.8 21l1.6-7L2 9.2l7.1-.6z"/></svg>',
        'snow'  => '<svg viewBox="0 0 24 24"><path d="M11 2h2v20h-2zM2 11h20v2H2zM4.9 3.5l15.6 15.6-1.4 1.4L3.5 4.9zM19.1 3.5l1.4 1.4L4.9 20.5l-1.4-1.4z"/></svg>',
        'tag'   => '<svg viewBox="0 0 24 24"><path d="M2 12V3h9l11 11-9 9zM7 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>',
    ];
    $deco = ['oferta' => 'bolt', 'maes' => 'heart', 'frete' => 'truck', 'natal' => 'snow', 'black' => 'star', 'novidade' => 'star', 'cupom' => 'tag', 'vitrine' => null][$tpl] ?? null;
@endphp
<span class="sf-d sf-d-{{ $tpl }} ink-{{ $ink === 'dark' ? 'dark' : 'light' }} pics-{{ $n }}"
      style="--c1: {{ $hex($c1, '#3483fa') }}; --c2: {{ $hex($c2, '#2968c8') }}; --acc: {{ $hex($acc, '#ffffff') }}">
    @if($deco)
        <span class="sf-d-deco" aria-hidden="true">@for($i = 0; $i < 5; $i++)<i>{!! $svg[$deco] !!}</i>@endfor</span>
    @endif
    <span class="sf-d-txt">
        @if($badge)<em class="sf-d-badge">{{ $badge }}</em>@endif
        @if($title)<b class="sf-d-title">{{ $title }}</b>@endif
        @if($subtitle)<span class="sf-d-sub">{{ $subtitle }}</span>@endif
        @if($button)<span class="sf-d-cta">{{ $button }} <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 11h11.2l-4.6-4.6L13 5l7 7-7 7-1.4-1.4 4.6-4.6H5z"/></svg></span>@endif
    </span>
    @if($n)
        <span class="sf-d-pics">
            @foreach(array_slice($photos, 0, $n) as $src)
                <span class="sf-d-pic"><img src="{{ $src }}" alt="" loading="lazy" decoding="async"></span>
            @endforeach
        </span>
    @endif
</span>
