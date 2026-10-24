{{-- Banner de modelo pronto, desenhado em HTML (nítido em qualquer tela).
     $tpl, $c1, $c2, $acc, $ink, $badge, $title, $subtitle, $button, $photos (urls), $lay = esq|dir|centro,
     $anim = none|fotos|deslizar|zoom|entrada|vivo (as que trocam as fotos passam até 3 fotos por espaço, em ciclo),
     $fmt = formato (StorefrontService::FORMATS), $ends = fim do banner (contagem regressiva) --}}
@php
    $tpl = $tpl ?: 'oferta';
    $t = \App\Services\Portal\StorefrontService::TEMPLATES[$tpl] ?? \App\Services\Portal\StorefrontService::TEMPLATES['oferta'];
    $lay = in_array($lay ?? 'esq', ['esq', 'dir', 'centro'], true) ? ($lay ?? 'esq') : 'esq';
    $photos = $lay === 'centro' ? [] : array_values(array_filter($photos ?? []));
    $anim = array_key_exists($anim ?? 'none', \App\Services\Portal\StorefrontService::ANIMATIONS) ? ($anim ?? 'none') : 'none';
    $rotates = \App\Services\Portal\StorefrontService::ANIMATIONS[$anim][2];
    $fmt = \App\Services\Portal\StorefrontService::format($fmt ?? null);
    $n = min(count($photos), \App\Services\Portal\StorefrontService::picsFor($tpl, $fmt));
    // Contagem regressiva: até o fim do dia em que o banner sai do ar.
    $count = null;
    if ($fmt === 'contagem' && ! empty($ends)) {
        try {
            $end = \Carbon\Carbon::parse($ends, \App\Models\Promotion::tz())->endOfDay();
            $left = max(0, now(\App\Models\Promotion::tz())->diffInMinutes($end, false));
            $count = [intdiv($left, 1440), intdiv($left % 1440, 60), $left % 60];
        } catch (\Throwable $e) {
            $count = null;
        }
    }
    $ghost = $fmt === 'revista' ? mb_strtoupper(\Illuminate\Support\Str::of((string) $title)->explode(' ')->sortByDesc(fn ($w) => mb_strlen($w))->first() ?? '') : '';
    // Fotos de cada espaço: com animação, o espaço i mostra as fotos i, i+n, i+2n em ciclo.
    $slots = [];
    for ($i = 0; $i < $n; $i++) {
        $slots[$i] = $rotates ? array_values(array_filter([$photos[$i] ?? null, $photos[$i + $n] ?? null, $photos[$i + 2 * $n] ?? null])) : [$photos[$i]];
    }
    $hex = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
    $svg = [
        'bolt'    => '<svg viewBox="0 0 24 24"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>',
        'heart'   => '<svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg>',
        'truck'   => '<svg viewBox="0 0 24 24"><path d="M2 5h12v10H2zM14 9h4l3 3v3h-7zM6 19.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm11 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>',
        'star'    => '<svg viewBox="0 0 24 24"><path d="m12 2 2.9 6.6 7.1.6-5.4 4.7 1.6 7L12 17.3 5.8 21l1.6-7L2 9.2l7.1-.6z"/></svg>',
        'snow'    => '<svg viewBox="0 0 24 24"><path d="M11 2h2v20h-2zM2 11h20v2H2zM4.9 3.5l15.6 15.6-1.4 1.4L3.5 4.9zM19.1 3.5l1.4 1.4L4.9 20.5l-1.4-1.4z"/></svg>',
        'tag'     => '<svg viewBox="0 0 24 24"><path d="M2 12V3h9l11 11-9 9zM7 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>',
        'spark'   => '<svg viewBox="0 0 24 24"><path d="M12 1l2.2 7.8L22 11l-7.8 2.2L12 21l-2.2-7.8L2 11l7.8-2.2z"/></svg>',
        'book'    => '<svg viewBox="0 0 24 24"><path d="M3 4h7a3 3 0 0 1 2 1 3 3 0 0 1 2-1h7v15h-7a2 2 0 0 0-2 2 2 2 0 0 0-2-2H3z"/></svg>',
        'flower'  => '<svg viewBox="0 0 24 24"><path d="M12 7a3 3 0 1 1 3-3 3 3 0 1 1 2 5 3 3 0 1 1-2 5 3 3 0 1 1-6 0 3 3 0 1 1-2-5 3 3 0 1 1 2-5 3 3 0 0 1 3 3zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>',
        'egg'     => '<svg viewBox="0 0 24 24"><path d="M12 2c4 0 7 6.5 7 11.5A7 7 0 0 1 5 13.5C5 8.5 8 2 12 2z"/></svg>',
        'flag'    => '<svg viewBox="0 0 24 24"><path d="M1 3h22v2H1zM2 5h4l-2 6zM7 5h4l-2 6zM12 5h4l-2 6zM17 5h4l-2 6z"/></svg>',
        'tie'     => '<svg viewBox="0 0 24 24"><path d="M9 2h6l-1.5 4L16 18l-4 4-4-4 2.5-12z"/></svg>',
        'balloon' => '<svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 4.5-3.6 8-7 8s-7-3.5-7-8a7 7 0 0 1 7-7zm-1 15h2l1 2h-4zm.5 3h1v2h-1z"/></svg>',
        'percent' => '<svg viewBox="0 0 24 24"><path d="M19 3.6 20.4 5 5 20.4 3.6 19zM7 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm10 10a4 4 0 1 1 0 8 4 4 0 0 1 0-8z"/></svg>',
        'gift'    => '<svg viewBox="0 0 24 24"><path d="M3 8h18v4H3zM4 12h7v9H4zm9 0h7v9h-7zM11 8h2v13h-2zM12 8S9 2 6.5 4 9 8 12 8zm0 0s3-6 5.5-4S15 8 12 8z"/></svg>',
        'trophy'  => '<svg viewBox="0 0 24 24"><path d="M6 2h12v3h4v3a5 5 0 0 1-5 5 6 6 0 0 1-4 3v3h4v3H7v-3h4v-3a6 6 0 0 1-4-3 5 5 0 0 1-5-5V5h4zm0 5H4v1a3 3 0 0 0 2 2.8zm12 0v3.8A3 3 0 0 0 20 8V7z"/></svg>',
        'ruler'   => '<svg viewBox="0 0 24 24"><path d="M2 16 16 2l6 6L8 22zm4.2-.6 1.4 1.4 1.4-1.4-1.4-1.4zm2.8-2.8 2.1 2.1 1.4-1.4-2.1-2.1zm2.8-2.8 1.4 1.4 1.4-1.4-1.4-1.4zm2.8-2.8 2.1 2.1 1.4-1.4-2.1-2.1z"/></svg>',
        'chair'   => '<svg viewBox="0 0 24 24"><path d="M6 2h12v9H6zM4 11h16v4H4zM5 15h2v7H5zm12 0h2v7h-2z"/></svg>',
        'lamp'    => '<svg viewBox="0 0 24 24"><path d="M7 2h10l3 9H4zM11 11h2v8h-2zM7 19h10v3H7z"/></svg>',
        'drop'    => '<svg viewBox="0 0 24 24"><path d="M12 2s7 7.6 7 12.5A7 7 0 0 1 5 14.5C5 9.6 12 2 12 2z"/></svg>',
        'wrench'  => '<svg viewBox="0 0 24 24"><path d="M21 6.5 17.5 10 14 6.5 17.5 3A6 6 0 0 0 10 10.6L2.6 18a2 2 0 0 0 0 2.8l.6.6a2 2 0 0 0 2.8 0l7.4-7.4A6 6 0 0 0 21 6.5z"/></svg>',
        'card'    => '<svg viewBox="0 0 24 24"><path d="M2 5h20v14H2zm0 3v3h20V8zm3 7v2h6v-2z"/></svg>',
    ];
    $deco = $t['deco'] ?? null;
@endphp
<span class="sf-d sf-d-{{ $t['look'] }} lay-{{ $lay }} ink-{{ $ink === 'dark' ? 'dark' : 'light' }} pics-{{ $n }} anim-{{ $anim }} fmt-{{ $fmt }}"
      style="--c1: {{ $hex($c1, '#3483fa') }}; --c2: {{ $hex($c2, '#2968c8') }}; --acc: {{ $hex($acc, '#ffffff') }}">
    @if($deco && isset($svg[$deco]))
        <span class="sf-d-deco" aria-hidden="true">@for($i = 0; $i < 5; $i++)<i>{!! $svg[$deco] !!}</i>@endfor</span>
    @endif
    @if($ghost !== '')<span class="sf-d-ghost" aria-hidden="true">{{ $ghost }}</span>@endif
    <span class="sf-d-txt">
        @if($badge)<em class="sf-d-badge">{{ $badge }}</em>@endif
        @if($title)<b class="sf-d-title">{{ $title }}</b>@endif
        @if($subtitle)<span class="sf-d-sub">{{ $subtitle }}</span>@endif
        @if($count)
            <span class="sf-d-count" aria-label="Termina em {{ $count[0] }} dias e {{ $count[1] }} horas">
                @foreach([[$count[0], $count[0] === 1 ? 'dia' : 'dias'], [$count[1], 'horas'], [$count[2], 'min']] as [$v, $u])<span><b>{{ str_pad((string) $v, 2, '0', STR_PAD_LEFT) }}</b><small>{{ $u }}</small></span>@endforeach
            </span>
        @elseif($fmt === 'contagem')
            <span class="sf-d-count is-empty"><span><b>--</b><small>dias</small></span><span><b>--</b><small>horas</small></span><span><b>--</b><small>min</small></span></span>
        @endif
        @if($button)<span class="sf-d-cta">{{ $button }} <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 11h11.2l-4.6-4.6L13 5l7 7-7 7-1.4-1.4 4.6-4.6H5z"/></svg></span>@endif
    </span>
    @if($n)
        <span class="sf-d-pics">
            @foreach($slots as $i => $imgs)
                <span class="sf-d-pic"><span class="sf-d-in cyc-{{ count($imgs) }}" style="--sf-o: {{ $i * -1.2 }}s">@foreach($imgs as $j => $src)<img src="{{ $src }}" alt="" loading="lazy" decoding="async" style="--sf-j: {{ $j }}">@endforeach</span></span>
            @endforeach
        </span>
    @endif
</span>
