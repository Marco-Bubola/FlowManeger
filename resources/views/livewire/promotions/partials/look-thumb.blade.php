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
            @php $st = match ($k) { 'elevado' => 'box-shadow: 0 6px 10px -4px rgba(0,0,0,.35)', 'borda' => 'box-shadow: 0 0 0 1px #cbd5e1', 'contorno' => "box-shadow: 0 0 0 2px {$c3}88", 'plano' => '', default => 'box-shadow: 0 1px 2px rgba(0,0,0,.2)' }; @endphp
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
        @default
            <span class="absolute inset-0 flex items-center justify-center text-xl" style="color: {{ $c3 }}"><i class="bi {{ $k === 'nao' ? 'bi-pause-circle' : 'bi-magic' }} {{ $k === 'sim' ? 'animate-pulse' : '' }}"></i></span>
    @endswitch
</span>
