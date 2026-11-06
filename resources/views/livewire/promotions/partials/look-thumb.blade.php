{{-- Miniatura de uma escolha do Estilo do site. Recebe $part, $k (a escolha) e $c (as 3 cores da loja). --}}
@php
    [$c1, $c2, $c3] = array_values($c) + ['#ec4899', '#3b82f6', '#8b5cf6'];
    $box = 'relative block w-full h-12 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800';
    $card = fn ($style) => 'flex-1 rounded-[4px] bg-white ' . $style;
@endphp
<span class="{{ $box }}" aria-hidden="true">
    @switch($part)
        @case('header')
            @php $bar = match ($k) { 'solido' => "background: {$c1}", 'vidro' => "background: {$c1}aa; backdrop-filter: blur(4px)", 'claro' => 'background: #fff; box-shadow: 0 1px 0 #e2e8f0', 'escuro' => 'background: #111114', default => "background: linear-gradient(90deg, {$c1}, {$c2})" }; @endphp
            <span class="absolute inset-x-0 top-0 h-4 flex items-center gap-1 px-1.5" style="{{ $bar }}">
                <i class="block w-2.5 h-2.5 rounded-sm bg-white/90 {{ $k === 'claro' ? '!bg-slate-300' : '' }}"></i>
                <i class="block flex-1 h-1.5 rounded-full {{ $k === 'claro' ? 'bg-slate-200' : 'bg-white/80' }}"></i>
            </span>
            <span class="absolute inset-x-1.5 bottom-1.5 top-6 flex gap-1"><i class="flex-1 rounded bg-white dark:bg-slate-700"></i><i class="flex-1 rounded bg-white dark:bg-slate-700"></i><i class="flex-1 rounded bg-white dark:bg-slate-700"></i></span>
            @break
        @case('cards')
            @php $st = match ($k) { 'elevado' => 'box-shadow: 0 6px 10px -4px rgba(0,0,0,.35)', 'borda' => 'box-shadow: 0 0 0 1px #cbd5e1', 'contorno' => "box-shadow: 0 0 0 2px {$c3}88", 'brilho' => "box-shadow: 0 5px 10px -3px {$c3}aa", 'plano' => '', default => 'box-shadow: 0 1px 2px rgba(0,0,0,.2)' }; @endphp
            <span class="absolute inset-2 flex gap-1.5">
                @for($n = 0; $n < 3; $n++)<i class="{{ $card('') }} flex flex-col p-0.5" style="{{ $st }}"><i class="block flex-1 rounded-sm bg-slate-200"></i><i class="block h-1 mt-0.5 w-3/4 rounded bg-slate-300"></i></i>@endfor
            </span>
            @break
        @case('pattern')
            @php $pt = \App\Services\Portal\HomeLayout::PATTERNS[$k] ?? null; @endphp
            @if($pt)
                <span class="absolute inset-0" style="background: {{ $c3 }}; opacity: .45; -webkit-mask-image: {!! str_replace('"', '&quot;', $pt[0]) !!}; mask-image: {!! str_replace('"', '&quot;', $pt[0]) !!}; @if($pt[1]) -webkit-mask-size: calc({{ explode(' ', $pt[1])[0] }} / 2) calc({{ explode(' ', $pt[1])[1] ?? explode(' ', $pt[1])[0] }} / 2); mask-size: calc({{ explode(' ', $pt[1])[0] }} / 2) calc({{ explode(' ', $pt[1])[1] ?? explode(' ', $pt[1])[0] }} / 2); @endif"></span>
            @else
                <span class="absolute inset-0 flex items-center justify-center text-slate-400"><i class="bi bi-slash-circle"></i></span>
            @endif
            @break
        @case('cardbg')
            @php
                $tints = ["{$c1}22", "{$c2}22", "{$c3}22"];
                $bgs = match ($k) {
                    'suave' => array_fill(0, 3, "background: {$c3}14"),
                    'degrade' => array_fill(0, 3, "background: linear-gradient(180deg, #fff 45%, {$c3}40)"),
                    'cores' => array_map(fn ($t) => "background: {$t}", $tints),
                    'vidro' => array_fill(0, 3, 'background: rgba(255,255,255,.55); backdrop-filter: blur(3px)'),
                    default => array_fill(0, 3, 'background: #fff'),
                };
            @endphp
            @if($k === 'vidro')<span class="absolute inset-0" style="background: linear-gradient(135deg, {{ $c1 }}66, {{ $c2 }}66)"></span>@endif
            <span class="absolute inset-2 flex gap-1.5">
                @for($n = 0; $n < 3; $n++)
                    <i class="flex-1 rounded-[4px] flex flex-col overflow-hidden shadow-sm" style="{{ $bgs[$n] }}; {{ $k === 'faixa' ? "border-top: 3px solid {$c3}" : '' }}">
                        <i class="block flex-1 m-0.5 rounded-sm bg-slate-200/80"></i>
                        <i class="block h-2 {{ $k === 'colorido' ? '' : 'mx-0.5 mb-0.5' }}" style="{{ $k === 'colorido' ? "background: {$c3}33" : '' }}"><i class="block h-1 w-3/4 rounded bg-slate-300 {{ $k === 'colorido' ? 'mx-0.5 mt-0.5' : '' }}"></i></i>
                    </i>
                @endfor
            </span>
            @break
        @case('price')
            <span class="absolute inset-0 flex items-center justify-center">
                <b class="leading-none {{ $k === 'grande' ? 'text-lg font-black' : 'text-sm' }} {{ $k === 'normal' ? 'font-normal text-slate-700 dark:text-slate-200' : '' }}"
                   style="{{ in_array($k, ['cor', 'etiqueta'], true) ? "color: {$c3}" : '' }}; {{ $k === 'etiqueta' ? "background: {$c3}22; padding: 3px 7px; border-radius: 6px" : '' }}">R$ 89<sup class="text-[8px]">90</sup></b>
            </span>
            @break
        @case('badge')
            <span class="absolute inset-1.5 rounded bg-white dark:bg-slate-600 overflow-hidden">
                @if($k === 'canto')
                    <i class="absolute block text-[8px] not-italic font-black text-white text-center py-0.5" style="background: #00a650; width: 64px; top: 7px; left: -18px; transform: rotate(-45deg)">-20%</i>
                @else
                    <i class="absolute top-1 left-1 not-italic font-black text-white text-[9px] flex items-center justify-center" style="background: #00a650; {{ $k === 'circulo' ? 'width: 26px; height: 26px; border-radius: 50%' : ($k === 'pilula' ? 'padding: 1px 6px; border-radius: 999px' : 'padding: 1px 4px; border-radius: 3px') }}">-20%</i>
                @endif
            </span>
            @break
        @case('addbtn')
            <span class="absolute inset-1.5 rounded bg-slate-200 dark:bg-slate-600">
                @if($k === 'barra')
                    <i class="absolute inset-x-1.5 bottom-1 h-4 rounded-full text-[8px] not-italic font-bold text-white flex items-center justify-center gap-0.5" style="background: {{ $c3 }}"><i class="bi bi-cart-plus"></i>Adicionar</i>
                @else
                    <i class="absolute right-1 bottom-1 w-5 h-5 flex items-center justify-center text-[10px] shadow" style="{{ $k === 'redondo' ? "background: #fff; color: {$c3}" : "background: {$c3}; color: #fff" }}; border-radius: {{ $k === 'quadrado' ? '5px' : '50%' }}"><i class="bi bi-cart-plus"></i></i>
                @endif
            </span>
            @break
        @case('bgtype')
            @php $bgv = match ($k) {
                'degrade' => "linear-gradient(180deg, #f8fafc, {$c3}44)",
                'brilho' => "radial-gradient(circle at 15% 15%, {$c3}55, transparent 50%), radial-gradient(circle at 85% 70%, {$c1}44, transparent 50%), #f8fafc",
                'malha' => "linear-gradient(135deg, {$c1}33, #f8fafc 45%, {$c2}33)",
                'topo' => "linear-gradient(180deg, {$c1}55, #f8fafc 70%)",
                default => '#f1f5f9',
            }; @endphp
            <span class="absolute inset-0" style="background: {{ $bgv }}"></span>
            <span class="absolute inset-x-2 bottom-1.5 top-4 flex gap-1"><i class="flex-1 rounded-sm bg-white/95"></i><i class="flex-1 rounded-sm bg-white/95"></i><i class="flex-1 rounded-sm bg-white/95"></i></span>
            @break
        @case('patternsize')
        @case('patternop')
            @php
                $pt = \App\Services\Portal\HomeLayout::PATTERNS['coracoes'];
                $sz = $part === 'patternsize' ? match ($k) { 'pequeno' => '22px', 'grande' => '64px', default => '38px' } : '38px';
                $op = $part === 'patternop' ? match ($k) { 'suave' => '.2', 'forte' => '.75', default => '.45' } : '.45';
            @endphp
            <span class="absolute inset-0" style="background: {{ $c3 }}; opacity: {{ $op }}; -webkit-mask-image: {!! str_replace('"', '&quot;', $pt[0]) !!}; mask-image: {!! str_replace('"', '&quot;', $pt[0]) !!}; -webkit-mask-size: {{ $sz }} {{ $sz }}; mask-size: {{ $sz }} {{ $sz }}"></span>
            @break
        @case('buttons')
            <span class="absolute inset-0 flex items-center justify-center">
                <i class="block w-16 h-5 shadow-sm" style="background: {{ $c3 }}; border-radius: {{ $k === 'pilula' ? '999px' : ($k === 'reto' ? '2px' : '6px') }}"></i>
            </span>
            @break
        @case('density')
            @php $cols = match ($k) { 'compacto' => 6, 'espacoso' => 3, default => 4 }; $gap = match ($k) { 'compacto' => '2px', 'espacoso' => '6px', default => '4px' }; @endphp
            <span class="absolute inset-1.5 grid" style="grid-template-columns: repeat({{ $cols }}, 1fr); gap: {{ $gap }}">
                @for($n = 0; $n < $cols * 2; $n++)<i class="block rounded-sm bg-white dark:bg-slate-600"></i>@endfor
            </span>
            @break
        @case('photo')
            <span class="absolute inset-0 flex items-center justify-center gap-1.5">
                @for($n = 0; $n < 3; $n++)
                    <i class="block bg-white dark:bg-slate-600 rounded-sm overflow-hidden flex items-center justify-center" style="width: {{ $k === 'retrato' ? '18px' : '24px' }}; height: {{ $k === 'retrato' ? '30px' : '24px' }}">
                        <i class="block" style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }}); {{ $k === 'quadrado' ? 'width: 60%; height: 60%; border-radius: 2px' : 'width: 100%; height: 100%' }}"></i>
                    </i>
                @endfor
            </span>
            @break
        @case('text')
            <span class="absolute inset-0 flex items-center justify-center text-lg text-slate-700 dark:text-slate-200" style="font-family: {{ $k === 'titulos' ? "Georgia, 'Playfair Display', serif" : "'Inter', system-ui, sans-serif" }}">Aa <small class="text-[10px] ml-1 opacity-70">texto</small></span>
            @break
        @case('titles')
            <span class="absolute inset-0 flex flex-col justify-center px-2 {{ $k === 'centro' ? 'items-center' : '' }}">
                <b class="text-[10px] leading-none text-slate-700 dark:text-slate-200 {{ $k === 'maiusculas' ? 'uppercase tracking-wider' : '' }}">Destaques</b>
                @if($k === 'linha')<i class="block mt-1 w-6 h-0.5 rounded" style="background: {{ $c3 }}"></i>@endif
                <span class="flex gap-1 mt-1.5 w-full"><i class="flex-1 h-3 rounded-sm bg-white dark:bg-slate-600"></i><i class="flex-1 h-3 rounded-sm bg-white dark:bg-slate-600"></i><i class="flex-1 h-3 rounded-sm bg-white dark:bg-slate-600"></i></span>
            </span>
            @break
        @case('width')
            <span class="absolute inset-y-1.5 left-1/2 -translate-x-1/2 rounded bg-white dark:bg-slate-600 shadow-sm" style="width: {{ $k === 'cheia' ? '94%' : ($k === 'larga' ? '72%' : '52%') }}"><i class="block h-1.5 rounded-t" style="background: linear-gradient(90deg, {{ $c1 }}, {{ $c2 }})"></i></span>
            @break
        @case('motion')
            <span class="absolute inset-0 flex flex-col justify-center gap-1 px-2">
                <i class="block h-2 w-3/4 rounded-sm bg-white dark:bg-slate-600 {{ $k !== 'nenhum' ? 'lk-thumb-rise' : '' }}"></i>
                <span class="flex gap-1 w-full">
                    @foreach([0, 1, 2] as $n)
                        <i class="flex-1 h-4 rounded-sm {{ $k !== 'nenhum' ? 'lk-thumb-rise' : '' }}" style="background: {{ $n === 1 && $k === 'completo' ? $c3 : '#fff' }}; animation-delay: {{ $n * 0.15 + 0.15 }}s"></i>
                    @endforeach
                </span>
            </span>
            @once
                <style>
                    @keyframes lk-thumb-rise { 0%, 15% { opacity: 0; transform: translateY(6px); } 45%, 100% { opacity: 1; transform: none; } }
                    .lk-thumb-rise { animation: lk-thumb-rise 2.4s ease-out infinite; }
                    @media (prefers-reduced-motion: reduce) { .lk-thumb-rise { animation: none; } }
                </style>
            @endonce
            @break
        @case('moentry')
        @case('mospeed')
            @php
                $ent = $part === 'moentry' ? $k : 'subir';
                $dur = $part === 'mospeed' ? ['normal' => 2.4, 'calma' => 3.4, 'rapida' => 1.6][$k] : 2.4;
            @endphp
            <span class="absolute inset-0 flex items-center gap-1 px-2" style="perspective: 200px">
                @foreach([0, 1, 2] as $n)
                    <i class="flex-1 h-7 rounded-sm lk-thumb-in-{{ $ent }}" style="background: {{ $n === 1 ? $c3 : '#fff' }}; animation-duration: {{ $dur }}s; animation-delay: {{ $n * ($dur / 12) }}s"></i>
                @endforeach
            </span>
            @once
                <style>
                    .lk-thumb-in-subir, .lk-thumb-in-zoom, .lk-thumb-in-lado, .lk-thumb-in-desfoque, .lk-thumb-in-virar, .lk-thumb-in-cortina { animation: 2.4s ease-out infinite; }
                    .lk-thumb-in-subir { animation-name: lk-ti-subir; } .lk-thumb-in-zoom { animation-name: lk-ti-zoom; } .lk-thumb-in-lado { animation-name: lk-ti-lado; }
                    .lk-thumb-in-desfoque { animation-name: lk-ti-desfoque; } .lk-thumb-in-virar { animation-name: lk-ti-virar; } .lk-thumb-in-cortina { animation-name: lk-ti-cortina; }
                    @keyframes lk-ti-subir { 0%, 15% { opacity: 0; transform: translateY(8px); } 45%, 100% { opacity: 1; transform: none; } }
                    @keyframes lk-ti-zoom { 0%, 15% { opacity: 0; transform: scale(.6); } 45%, 100% { opacity: 1; transform: none; } }
                    @keyframes lk-ti-lado { 0%, 15% { opacity: 0; transform: translateX(-10px); } 45%, 100% { opacity: 1; transform: none; } }
                    @keyframes lk-ti-desfoque { 0%, 15% { opacity: 0; filter: blur(4px); } 45%, 100% { opacity: 1; filter: none; } }
                    @keyframes lk-ti-virar { 0%, 15% { opacity: 0; transform: rotateX(70deg); } 45%, 100% { opacity: 1; transform: none; } }
                    @keyframes lk-ti-cortina { 0%, 15% { clip-path: inset(0 0 100% 0); } 45%, 100% { clip-path: inset(0); } }
                    @media (prefers-reduced-motion: reduce) { [class*="lk-thumb-in-"] { animation: none !important; } }
                </style>
            @endonce
            @break
        @case('mocart')
            <span class="absolute inset-0">
                <i class="absolute left-2 bottom-2 w-5 h-5 rounded-sm bg-white {{ $k === 'voar' ? 'lk-thumb-fly' : '' }}" style="background: {{ $c3 }}"></i>
                <i class="bi bi-cart3 absolute right-2 top-1.5 text-base text-slate-500 {{ $k === 'pular' ? 'lk-thumb-bump' : '' }}"></i>
            </span>
            @once
                <style>
                    .lk-thumb-fly { animation: lk-ti-fly 2s ease-in infinite; }
                    @keyframes lk-ti-fly { 0%, 20% { transform: none; opacity: 1; } 70% { transform: translate(calc(var(--w, 120px) * .55), -16px) scale(.3); opacity: .9; } 71%, 100% { opacity: 0; transform: translate(0, 0); } }
                    .lk-thumb-bump { animation: lk-ti-bump 1.6s ease infinite; display: inline-block; }
                    @keyframes lk-ti-bump { 0%, 40%, 100% { transform: none; } 55% { transform: scale(1.4) rotate(-10deg); } 70% { transform: scale(.9) rotate(5deg); } }
                    @media (prefers-reduced-motion: reduce) { .lk-thumb-fly, .lk-thumb-bump { animation: none; } }
                </style>
            @endonce
            @break
        @case('mohover')
            <span class="absolute inset-0 flex items-center justify-center" style="perspective: 160px">
                <i class="block w-10 h-9 rounded bg-white overflow-hidden shadow lk-thumb-hv-{{ $k }}"><i class="block w-full h-full" style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})"></i></i>
            </span>
            @once
                <style>
                    .lk-thumb-hv-zoom > i { animation: lk-hv-zoom 2s ease infinite; }
                    .lk-thumb-hv-levantar { animation: lk-hv-up 2s ease infinite; }
                    .lk-thumb-hv-inclinar { animation: lk-hv-tilt 2.4s ease infinite; }
                    @keyframes lk-hv-zoom { 0%, 30%, 100% { transform: none; } 55%, 80% { transform: scale(1.3); } }
                    @keyframes lk-hv-up { 0%, 30%, 100% { transform: none; } 55%, 80% { transform: translateY(-5px); box-shadow: 0 8px 10px -4px rgba(0,0,0,.3); } }
                    @keyframes lk-hv-tilt { 0%, 100% { transform: none; } 30% { transform: rotateY(18deg) rotateX(6deg); } 65% { transform: rotateY(-18deg) rotateX(-6deg); } }
                    @media (prefers-reduced-motion: reduce) { [class*="lk-thumb-hv-"], [class*="lk-thumb-hv-"] > i { animation: none !important; } }
                </style>
            @endonce
            @break
        @case('carousel')
            <span class="absolute inset-0 flex items-center justify-center" style="perspective: 160px">
                @foreach([-1, 0, 1] as $d)
                    @php
                        $t = match ($k) {
                            'vidro' => 'translateX(' . ($d * 70) . '%) rotateY(' . ($d * -35) . 'deg) scale(' . ($d ? .82 : 1) . ')',
                            'pilha' => 'translateX(' . ($d * 30) . '%) rotate(' . ($d * 9) . 'deg) scale(' . ($d ? .9 : 1) . ')',
                            default => 'translateX(' . ($d * 110) . '%)',
                        };
                    @endphp
                    <i class="absolute w-6 h-8 rounded {{ $k === 'giro' ? 'lk-thumb-spin' : '' }}" style="transform: {{ $t }}; z-index: {{ 2 - abs($d) }}; background: {{ $d === 0 ? 'linear-gradient(135deg,' . $c1 . ',' . $c2 . ')' : '#fff' }}; {{ $k === 'vidro' ? 'box-shadow: 0 0 0 1px rgba(255,255,255,.7), 0 4px 8px -3px rgba(0,0,0,.3); opacity: ' . ($d ? .8 : 1) : 'box-shadow: 0 2px 5px -2px rgba(0,0,0,.25)' }}"></i>
                @endforeach
            </span>
            @once
                <style>
                    .lk-thumb-spin { animation: lk-ti-spin 2.4s ease-in-out infinite; }
                    @keyframes lk-ti-spin { 0%, 30% { translate: 0 0; } 60%, 100% { translate: -110% 0; } }
                    @media (prefers-reduced-motion: reduce) { .lk-thumb-spin { animation: none; } }
                </style>
            @endonce
            @break
        @case('buybtn')
            <span class="absolute inset-0 flex items-center justify-center">
                <span class="relative w-16 h-5 rounded-full overflow-hidden flex items-center justify-center text-[8px] font-bold text-white" style="background: {{ $c1 }}">
                    @if($k === 'caminhao')
                        <i class="bi bi-truck absolute text-[11px] lk-thumb-truck" style="color: #fff"></i>
                    @elseif($k === 'caixa')
                        <i class="bi bi-box-seam absolute text-[11px] lk-thumb-box"></i>
                    @else
                        <i class="bi bi-cart-plus text-[11px]"></i>
                    @endif
                </span>
            </span>
            @once
                <style>
                    .lk-thumb-truck { animation: lk-ti-truck 2.2s ease-in infinite; }
                    @keyframes lk-ti-truck { 0%, 25% { transform: translateX(-4px); } 85%, 100% { transform: translateX(46px); } }
                    .lk-thumb-box { animation: lk-ti-box 2.2s cubic-bezier(.3,1.4,.5,1) infinite; }
                    @keyframes lk-ti-box { 0% { transform: scale(0); } 35%, 70% { transform: scale(1.1); } 100% { transform: scale(0) translateY(-6px); } }
                    @media (prefers-reduced-motion: reduce) { .lk-thumb-truck, .lk-thumb-box { animation: none; } }
                </style>
            @endonce
            @break
        @case('splash')
            <span class="absolute inset-0 flex items-center justify-center bg-white">
                @if($k !== 'nenhum')
                    <span class="absolute inset-0 flex items-center justify-center lk-thumb-sp-{{ $k }}" style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})"><i class="w-4 h-4 rounded bg-white lk-thumb-sp-logo"></i></span>
                @else
                    <i class="bi bi-dash-circle text-slate-400"></i>
                @endif
            </span>
            @once
                <style>
                    .lk-thumb-sp-logo { animation: lk-sp-pop 2.4s cubic-bezier(.2,1.4,.4,1) infinite; }
                    @keyframes lk-sp-pop { 0% { transform: scale(.2); } 25%, 100% { transform: none; } }
                    .lk-thumb-sp-logo, .lk-thumb-sp-cortina, .lk-thumb-sp-circulo { animation-duration: 2.4s; animation-iteration-count: infinite; }
                    .lk-thumb-sp-logo { animation-name: lk-sp-pop; }
                    .lk-thumb-sp-cortina { animation: lk-sp-up 2.4s ease infinite; }
                    @keyframes lk-sp-up { 0%, 55% { transform: none; } 85%, 100% { transform: translateY(-100%); } }
                    .lk-thumb-sp-circulo { animation: lk-sp-hole 2.4s ease infinite; -webkit-mask: radial-gradient(circle, transparent var(--h, 0%), #000 calc(var(--h, 0%) + .5%)); mask: radial-gradient(circle, transparent var(--h, 0%), #000 calc(var(--h, 0%) + .5%)); }
                    @property --h { syntax: '<percentage>'; inherits: false; initial-value: 0%; }
                    @keyframes lk-sp-hole { 0%, 55% { --h: 0%; } 90%, 100% { --h: 75%; } }
                    .lk-thumb-sp-logo { animation-name: lk-sp-pop; }
                    @media (prefers-reduced-motion: reduce) { [class*="lk-thumb-sp-"] { animation: none !important; } }
                </style>
            @endonce
            @break
        @default
            <span class="absolute inset-0 flex items-center justify-center text-xl" style="color: {{ $c3 }}"><i class="bi {{ $k === 'nao' ? 'bi-pause-circle' : 'bi-magic' }} {{ $k === 'sim' ? 'animate-pulse' : '' }}"></i></span>
    @endswitch
</span>
