{{-- Relatório do site: visitas, origem, produtos mais vistos e pedidos do catálogo. --}}
@php
    $S = \App\Services\Portal\SiteStats::class;
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $num = fn ($v) => number_format((int) $v, 0, ',', '.');
    $diff = $r && $r['prevVisitors'] > 0 ? (int) round(($r['visitors'] - $r['prevVisitors']) / $r['prevVisitors'] * 100) : null;
    $srcTotal = $r ? max(1, array_sum($r['sources'])) : 1;
    $kindTotal = $r ? max(1, array_sum($r['kinds'])) : 1;
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4">
    <x-page-header title="Relatório do site" icon="bi-graph-up-arrow" section="produtos" back-route="promotions.storefront"
                   subtitle="Quantas pessoas visitam o catálogo, de onde vêm e o que mais olham">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }}"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav current="relatorio">

    <div class="space-y-4" data-testid="report-page">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <nav class="inline-flex gap-1 rounded-2xl bg-slate-100 dark:bg-slate-800/70 p-1" aria-label="Período">
                @foreach($S::PERIODS as $d => $dn)
                    <button type="button" wire:click="$set('days', {{ $d }})" data-testid="report-days-{{ $d }}"
                            class="rounded-xl px-3 py-1.5 text-sm font-semibold transition {{ $days === $d ? 'bg-white dark:bg-slate-900 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $dn }}</button>
                @endforeach
            </nav>
            <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-incognito"></i> Contagem anônima, sem cookies. Suas próprias visitas logada não contam.</p>
        </div>

        @if(! $r || $r['views'] === 0)
            <x-empty-state icon="bi-graph-up" tone="violet" title="Ainda sem visitas contadas"
                text="As visitas começam a contar a partir de agora. Mande o link do catálogo para os clientes (no WhatsApp, no Instagram) e volte aqui em alguns dias." />
        @endif

        @if($r)
            <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
                <div class="{{ $card }} p-4" data-testid="report-visitors">
                    <small class="block text-xs font-semibold text-slate-500">Visitantes</small>
                    <b class="text-2xl font-black text-slate-900 dark:text-white">{{ $num($r['visitors']) }}</b>
                    @if($diff !== null)<span class="ml-1 text-xs font-bold {{ $diff >= 0 ? 'text-emerald-600' : 'text-rose-600' }}"><i class="bi {{ $diff >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>{{ abs($diff) }}%</span>@endif
                    <small class="block text-[11px] text-slate-400 mt-0.5">{{ $num($r['views']) }} páginas vistas</small>
                </div>
                <div class="{{ $card }} p-4">
                    <small class="block text-xs font-semibold text-slate-500">Pedidos pelo site</small>
                    <b class="text-2xl font-black text-slate-900 dark:text-white">{{ $num($r['orders']) }}</b>
                    <small class="block text-[11px] text-slate-400 mt-0.5">{{ $num($r['confirmed']) }} confirmados</small>
                </div>
                <div class="{{ $card }} p-4">
                    <small class="block text-xs font-semibold text-slate-500">Vendido pelo site</small>
                    <b class="text-2xl font-black text-emerald-600">{{ $money($r['value']) }}</b>
                    <small class="block text-[11px] text-slate-400 mt-0.5">pedidos confirmados</small>
                </div>
                <div class="{{ $card }} p-4" title="Pedidos ÷ visitantes">
                    <small class="block text-xs font-semibold text-slate-500">Conversão</small>
                    <b class="text-2xl font-black text-violet-600">{{ number_format($r['conversion'], 1, ',', '') }}%</b>
                    <small class="block text-[11px] text-slate-400 mt-0.5">de cada 100 visitantes, {{ number_format($r['conversion'], 1, ',', '') }} pedem</small>
                </div>
            </div>

            {{-- Visitantes por dia --}}
            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="font-bold text-slate-900 dark:text-white mb-3">Visitantes por dia</h2>
                <div class="flex items-end gap-[3px] h-40" data-testid="report-chart">
                    @foreach($r['series'] as $pt)
                        <div class="group relative flex-1 min-w-0 h-full flex flex-col justify-end">
                            <div class="rounded-t-md bg-gradient-to-t from-violet-500 to-fuchsia-400 transition group-hover:from-violet-600 group-hover:to-fuchsia-500" style="height: {{ max(2, round($pt['visitors'] / $r['max'] * 100)) }}%; {{ $pt['visitors'] ? '' : 'opacity:.25' }}"></div>
                            <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block whitespace-nowrap rounded-lg bg-slate-900 text-white text-[11px] px-2 py-1 z-10">{{ $pt['label'] }} · {{ $pt['visitors'] }} {{ $pt['visitors'] === 1 ? 'visitante' : 'visitantes' }}</span>
                        </div>
                    @endforeach
                </div>
                @php $every = max(1, (int) ceil(count($r['series']) / 8)); @endphp
                <div class="flex gap-[3px] mt-1.5">
                    @foreach($r['series'] as $i => $pt)
                        <span class="flex-1 min-w-0 text-center text-[10px] text-slate-400 truncate">{{ $i % $every === 0 ? $pt['label'] : '' }}</span>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-4 lg:grid-cols-2">
                {{-- De onde vieram --}}
                <section class="{{ $card }} p-4 sm:p-5" data-testid="report-sources">
                    <h2 class="font-bold text-slate-900 dark:text-white mb-1">De onde vieram</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Dica: nos links que você posta, acrescente <code class="rounded bg-slate-100 dark:bg-slate-800 px-1">?origem=instagram</code> para saber de onde veio cada visita.</p>
                    <div class="space-y-2.5">
                        @forelse($r['sources'] as $src => $n)
                            @php [$sn, $si, $sc] = $S::SOURCES[$src] ?? [$src, 'bi-globe2', '#6366f1']; @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm"><span class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><i class="bi {{ $si }}" style="color: {{ $sc }}"></i>{{ $sn }}</span><b class="text-slate-900 dark:text-white">{{ $num($n) }} <small class="font-normal text-slate-400">{{ round($n / $srcTotal * 100) }}%</small></b></div>
                                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 mt-1 overflow-hidden"><div class="h-full rounded-full" style="width: {{ round($n / $srcTotal * 100) }}%; background: {{ $sc }}"></div></div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Sem dados no período.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Páginas e carrinhos --}}
                <section class="{{ $card }} p-4 sm:p-5">
                    <h2 class="font-bold text-slate-900 dark:text-white mb-3">O que mais abrem</h2>
                    <div class="space-y-2.5">
                        @foreach($S::KINDS as $kk => $kn)
                            @php $n = $r['kinds'][$kk] ?? 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm"><span class="text-slate-700 dark:text-slate-200">{{ $kn }}</span><b class="text-slate-900 dark:text-white">{{ $num($n) }}</b></div>
                                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 mt-1 overflow-hidden"><div class="h-full rounded-full bg-sky-500" style="width: {{ round($n / $kindTotal * 100) }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 flex items-center gap-3">
                        <i class="bi bi-cart-x text-xl text-violet-500"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300 flex-1">{{ $r['cartsOpen'] }} {{ $r['cartsOpen'] === 1 ? 'carrinho esperando' : 'carrinhos esperando' }} · {{ $r['cartsRecovered'] }} {{ $r['cartsRecovered'] === 1 ? 'recuperado' : 'recuperados' }} com o lembrete ({{ $money($r['cartsRecoveredValue']) }})</p>
                        <a href="{{ route('promotions.carts') }}" class="text-sm font-semibold text-violet-600 whitespace-nowrap">Ver</a>
                    </div>
                </section>
            </div>

            {{-- Produtos mais vistos --}}
            <section class="{{ $card }} p-4 sm:p-5" data-testid="report-top">
                <h2 class="font-bold text-slate-900 dark:text-white mb-1">Produtos mais vistos</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Muita visita e pouco pedido? Veja o preço, as fotos e a descrição desse produto, ou faça uma promoção.</p>
                @if(empty($r['top']))
                    <p class="text-sm text-slate-400">Sem visitas a produtos no período.</p>
                @else
                    <ol class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($r['top'] as $i => $p)
                            <li class="flex items-center gap-3 py-2.5">
                                <span class="w-6 text-center text-sm font-black text-slate-400">{{ $i + 1 }}</span>
                                <span class="shrink-0 w-11 h-11 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center">@if($p['img'])<img src="{{ $p['img'] }}" alt="" class="w-full h-full object-cover" loading="lazy">@else<i class="bi bi-box text-slate-400"></i>@endif</span>
                                <a href="{{ $p['url'] }}" target="_blank" rel="noopener" class="min-w-0 flex-1 text-sm font-semibold text-slate-800 dark:text-slate-100 truncate hover:text-violet-600">{{ $p['name'] }}</a>
                                <span class="text-right shrink-0"><b class="block text-sm text-slate-900 dark:text-white">{{ $num($p['views']) }} <small class="font-normal text-slate-400">visitas</small></b><small class="text-xs {{ $p['ordered'] ? 'text-emerald-600' : 'text-slate-400' }}">{{ $p['ordered'] }} {{ $p['ordered'] === 1 ? 'pedido' : 'pedidos' }}</small></span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        @endif
    </div>

    </x-portal-settings-nav>
</div>
