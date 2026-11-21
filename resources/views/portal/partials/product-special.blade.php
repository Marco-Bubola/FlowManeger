{{--
    Blocos da página especial do produto.
    $special = ['settings' => [...], 'blocks' => [...]], $where = 'topo' | 'meio',
    $name = nome do produto, $photos = fotos do produto, $phone = WhatsApp da loja,
    $editor = true na prévia do painel (botões não navegam).
--}}
@php
    $svc = \App\Services\Portal\ProductPageService::class;
    $st = $special['settings'];
    $editor = $editor ?? false;
    $photos = array_values(array_filter($photos ?? []));
    $list = collect($special['blocks'])->reject(fn ($b) => $b['hidden'])
        ->filter(fn ($b) => in_array($b['type'], ['banner', 'faixa'], true) ? (($b['where'] ?? 'meio') === $where) : $where === 'meio')->values();
    $wa = fn (string $msg) => $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($msg) : null;
    $t = fn ($v) => $svc::text($v, $name);
    $para = fn ($v) => nl2br(e($t($v)), false);
    $pic = function ($b, $field = 'image') use ($svc, $photos) {
        $own = $svc::imageUrl($b[$field] ?? null);
        if ($own) return $own;
        $n = max(1, (int) ($b['photo'] ?? 1));
        return $photos[$n - 1] ?? $photos[0] ?? null;
    };
@endphp
@if($list->isNotEmpty())
@php
    $rad = ['s' => '6px', 'm' => '14px', 'l' => '24px'][$st['round']];
    $ts = ['s' => '.88', 'm' => '1', 'l' => '1.16'][$st['title_size']];
    $btnTag = function (array $b, string $cls, string $style = '') use ($editor, $wa, $name, $t) {
        $label = e($t($b['button'] ?: 'Comprar'));
        $icon = ($b['action'] ?? 'comprar') === 'whatsapp' ? '<i class="fab fa-whatsapp"></i>' : '<i class="fas fa-bag-shopping"></i>';
        if ($editor) {
            return '<span class="' . $cls . '" style="' . $style . '">' . $icon . $label . '</span>';
        }
        if (($b['action'] ?? 'comprar') === 'whatsapp' && ($link = $wa('Olá! Tenho interesse em: ' . $name))) {
            return '<a class="' . $cls . '" style="' . $style . '" href="' . e($link) . '" target="_blank" rel="noopener">' . $icon . $label . '</a>';
        }

        return '<a class="' . $cls . '" style="' . $style . '" href="#comprar" onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent(\'pp-buy\'))">' . $icon . $label . '</a>';
    };
@endphp
<div class="ps {{ $st['style'] }} {{ $where === 'topo' ? 'top' : '' }}" data-testid="special-{{ $where }}"
     style="--ps-acc: {{ $st['acc'] }}; --ps-r: {{ $rad }}; --ps-t: {{ $ts }}; @if($st['bg']) --ps-bg: {{ $st['bg'] }}; @endif">
    @foreach($list as $b)
        @switch($b['type'])
            @case('banner')
                @php $img = $pic($b); $lay = $img ? $b['lay'] : 'sem'; @endphp
                <section class="ps-ban {{ $lay }} {{ $b['ink'] === 'dark' ? 'dark' : '' }}" style="--c1: {{ $svc::color($b['c1'], '#7c3aed') }}; --c2: {{ $svc::color($b['c2'], '#db2777') }}">
                    <div class="ps-ban-txt">
                        @if(trim($b['badge']))<span class="ps-ban-badge">{{ $t($b['badge']) }}</span>@endif
                        @if(trim($b['title']))<h2>{{ $t($b['title']) }}</h2>@endif
                        @if(trim($b['text']))<p>{!! $para($b['text']) !!}</p>@endif
                        @if(trim($b['button'])){!! $btnTag($b, 'ps-btn') !!}@endif
                    </div>
                    @if($img)<div class="ps-ban-img"><img src="{{ $img }}" alt="" loading="lazy"></div>@endif
                </section>
                @break
            @case('faixa')
                <div class="ps-faixa" style="--c1: {{ $svc::color($b['c1'], '#16a34a') }}"><i class="bi {{ preg_match('/^bi-[a-z0-9-]+$/', $b['icon']) ? $b['icon'] : 'bi-megaphone' }}"></i><span>{{ $t($b['text']) }}</span></div>
                @break
            @case('texto')
                <section class="ps-sec">
                    @if(trim($b['title']))<h2 class="ps-h {{ $b['align'] === 'centro' ? 'c' : '' }}">{{ $t($b['title']) }}</h2>@endif
                    <p class="ps-p {{ $b['align'] === 'centro' ? 'c' : '' }}">{!! $para($b['text']) !!}</p>
                </section>
                @break
            @case('foto_texto')
                @php $img = $pic($b); @endphp
                <section class="ps-sec">
                    <div class="ps-ft {{ $b['side'] }}">
                        @if($img)<div class="ps-ft-img"><img src="{{ $img }}" alt="" loading="lazy" class="ps-zoom" @unless($editor) @click="zoomOpen(0, [@js($img)])" @endunless></div>@endif
                        <div>
                            @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                            <p class="ps-p">{!! $para($b['text']) !!}</p>
                        </div>
                    </div>
                </section>
                @break
            @case('destaques')
                <section class="ps-sec">
                    @if(trim($b['title']))<h2 class="ps-h c">{{ $t($b['title']) }}</h2>@endif
                    <div class="ps-dest">
                        @foreach(array_slice((array) $b['items'], 0, 8) as $it)
                            <div><i class="bi {{ preg_match('/^bi-[a-z0-9-]+$/', $it['icon'] ?? '') ? $it['icon'] : 'bi-star' }}"></i><b>{{ $t($it['title'] ?? '') }}</b>@if(trim($it['text'] ?? ''))<span>{{ $t($it['text']) }}</span>@endif</div>
                        @endforeach
                    </div>
                </section>
                @break
            @case('galeria')
                @php
                    $gal = collect((array) $b['photos'])->map(fn ($p) => $svc::imageUrl($p))->filter();
                    if ($b['product_photos'] ?? false) { $gal = $gal->concat($photos); }
                    $gal = $gal->unique()->values()->take(24)->all();
                @endphp
                @if($gal)
                    <section class="ps-sec">
                        @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                        <div class="ps-gal" style="--cols: {{ max(2, min(4, (int) $b['cols'])) }}">
                            @foreach($gal as $gi => $src)
                                <button type="button" @unless($editor) @click="zoomOpen({{ $gi }}, @js($gal))" @endunless aria-label="Ampliar foto {{ $gi + 1 }}"><img src="{{ $src }}" alt="" loading="lazy"></button>
                            @endforeach
                        </div>
                    </section>
                @endif
                @break
            @case('video')
                @php $v = $svc::video($b['url']); @endphp
                @if($v || $editor)
                    <section class="ps-sec">
                        @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                        <div class="ps-vid {{ ($v['tall'] ?? false) ? 'tall' : '' }}">
                            @if(! $v)
                                <div class="ps-vid-empty"><i class="bi bi-play-circle"></i> Cole o link do vídeo</div>
                            @elseif($v['kind'] === 'file')
                                <video src="{{ $v['src'] }}" controls playsinline preload="metadata"></video>
                            @else
                                <iframe src="{{ $v['src'] }}" title="Vídeo" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            @endif
                        </div>
                    </section>
                @endif
                @break
            @case('medidas')
                @php $img = $svc::imageUrl($b['image']); $rows = collect((array) $b['rows'])->filter(fn ($r) => trim($r['k'] ?? '') !== '' && trim($r['v'] ?? '') !== ''); @endphp
                @if($rows->isNotEmpty() || $img || $editor)
                    <section class="ps-sec">
                        @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                        <div class="ps-med {{ $img ? 'img' : '' }}">
                            @if($img)<img src="{{ $img }}" alt="" loading="lazy" class="ps-zoom" @unless($editor) @click="zoomOpen(0, [@js($img)])" @endunless>@endif
                            <dl>
                                @forelse($rows as $r)
                                    <div><dt>{{ $r['k'] }}</dt><dd>{{ $t($r['v']) }}</dd></div>
                                @empty
                                    <div><dt>Medida</dt><dd>Preencha os valores</dd></div>
                                @endforelse
                            </dl>
                        </div>
                    </section>
                @endif
                @break
            @case('perguntas')
                <section class="ps-sec ps-faq">
                    @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                    @foreach((array) $b['items'] as $qi => $it)
                        @if(trim($it['q'] ?? ''))
                            <details @if($qi === 0) open @endif><summary>{{ $t($it['q']) }}</summary><p>{!! $para($it['a'] ?? '') !!}</p></details>
                        @endif
                    @endforeach
                </section>
                @break
            @case('depoimentos')
                <section class="ps-sec">
                    @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                    <div class="ps-dep">
                        @foreach((array) $b['items'] as $it)
                            @if(trim($it['text'] ?? ''))
                                <figure><blockquote>{{ $t($it['text']) }}</blockquote><figcaption><span>★★★★★</span>{{ $it['name'] ?? '' }}</figcaption></figure>
                            @endif
                        @endforeach
                    </div>
                </section>
                @break
            @case('contagem')
                <section class="ps-sec ps-cd" style="--c1: {{ $svc::color($b['c1'], '#dc2626') }}" x-data="psCount(@js($b['ends']))">
                    @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                    <div class="ps-cd-n" x-show="! over">
                        <div><b x-text="d">0</b><small>dias</small></div><div><b x-text="h">00</b><small>horas</small></div><div><b x-text="m">00</b><small>min</small></div><div><b x-text="s">00</b><small>seg</small></div>
                    </div>
                    <p x-show="over" x-cloak><b>Esta oferta já acabou.</b></p>
                    @if(trim($b['text']))<p x-show="! over">{{ $t($b['text']) }}</p>@endif
                </section>
                @break
            @case('chamada')
                <section class="ps-sec ps-cta" style="--c1: {{ $svc::color($b['c1'], '#3483fa') }}">
                    @if(trim($b['title']))<h2 class="ps-h">{{ $t($b['title']) }}</h2>@endif
                    @if(trim($b['text']))<p class="ps-p c">{!! $para($b['text']) !!}</p>@endif
                    {!! $btnTag($b, 'ps-btn') !!}
                </section>
                @break
            @case('espaco')
                <div class="ps-sec ps-gap {{ in_array($b['size'], ['s', 'm', 'l'], true) ? $b['size'] : 'm' }} {{ $b['line'] ? 'line' : '' }}"></div>
                @break
        @endswitch
    @endforeach
</div>
@endif
