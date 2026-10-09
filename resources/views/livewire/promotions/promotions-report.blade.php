@php
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $num = fn ($v, $d = 1) => number_format((float) $v, $d, ',', '.');
    $periodLabel = $months === 1
        ? ucfirst($end->locale('pt_BR')->isoFormat('MMMM [de] YYYY'))
        : ucfirst($start->locale('pt_BR')->isoFormat('MMM/YY')) . ' – ' . ucfirst($end->locale('pt_BR')->isoFormat('MMM/YY'));
    $pill = 'whitespace-nowrap rounded-xl px-2.5 sm:px-3 py-1.5 text-xs font-bold transition';
    $pillOn = 'bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow-sm';
    $pillOff = 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
    $sortIcon = fn ($col) => $sort === $col ? ($dir === 'desc' ? 'bi-sort-down' : 'bi-sort-up') : 'bi-arrow-down-up opacity-40';
    $statusMeta = [
        'ativa' => ['Ativa', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'agendada' => ['Agendada', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        'encerrada' => ['Encerrada', 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300'],
    ];
    $sortLabels = ['revenue' => 'faturamento', 'qty' => 'quantidade', 'discount' => 'desconto', 'profit' => 'lucro', 'margin' => 'margem', 'lift' => 'antes x durante'];
    $tabs = [
        ['Promoções', 'bi-fire', route('promotions.index'), false],
        ['Vendas em promoção', 'bi-bar-chart-line', route('promotions.report'), true],
    ];
@endphp

<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    {{-- ============ HEADER ============ --}}
    <div class="relative overflow-hidden app-ph rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-gradient-to-r from-white/90 via-rose-50/90 to-orange-50/80 dark:from-slate-900/95 dark:via-slate-800/90 dark:to-slate-900/95 backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
        <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-rose-400/20 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-6 h-28 w-28 rounded-full bg-orange-400/15 blur-2xl"></div>

        <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
            <div class="app-ph-main flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:pr-[360px]">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="app-ph-icon flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 shadow-lg shadow-rose-500/25">
                        <i class="bi bi-bar-chart-line text-white text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <a href="{{ route('promotions.index') }}" class="hover:text-rose-600 dark:hover:text-rose-300"><i class="bi bi-percent mr-1"></i>Promoções</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-rose-600 dark:text-rose-300 truncate">Vendas em promoção</span>
                        </nav>
                        <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-rose-700 to-orange-600 dark:from-rose-300 dark:via-pink-300 dark:to-orange-300 bg-clip-text text-transparent">Vendas em promoção</h1>
                        <p class="app-ph-sub mt-0.5 text-sm text-slate-600 dark:text-slate-400">Quanto você vendeu com preço promocional, quanto deu de desconto e quanto lucrou</p>
                    </div>
                </div>
            </div>

            {{-- Período: fora de .app-ph-actions (que no celular vira só ícones) --}}
            <div class="mt-3 lg:mt-0 lg:absolute lg:top-5 lg:right-6 lg:max-w-[340px] flex flex-wrap items-center gap-2 lg:justify-end">
                    <div class="flex flex-1 sm:flex-none items-center justify-between gap-1 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 p-1 shadow-sm">
                        <button type="button" wire:click="shiftMonth(-1)" aria-label="Mês anterior" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="min-w-[150px] text-center text-sm font-bold text-slate-800 dark:text-slate-100">{{ $periodLabel }}</span>
                        <button type="button" wire:click="shiftMonth(1)" aria-label="Próximo mês" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div class="flex flex-1 sm:flex-none items-center justify-between gap-1 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 p-1 shadow-sm">
                        @foreach ([1 => 'Mês', 3 => '3 meses', 6 => '6 meses', 12 => '12 meses'] as $n => $label)
                            <button type="button" wire:click="setMonths({{ $n }})" class="{{ $pill }} {{ $months === $n ? $pillOn : $pillOff }}">{{ $label }}</button>
                        @endforeach
                    </div>
            </div>

            <div class="app-ph-tabs mt-4 -mx-1 overflow-x-auto">
                <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                    @foreach ($tabs as [$label, $icon, $url, $on])
                        <a href="{{ $url }}"
                           class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                                  {{ $on ? 'bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow-md shadow-rose-500/25' : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-rose-700 dark:hover:text-rose-300' }}">
                            <i class="bi {{ $icon }}"></i>{{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    {{-- ============ RESUMO ============ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-3">
        <x-gestao-stat label="Unidades" :value="$num($summary['qty'], 0)" icon="bi-box-seam" tone="rose"
            :hint="$summary['orders'] . ' ' . ($summary['orders'] === 1 ? 'venda' : 'vendas') . ' · ' . $summary['promotions'] . ' ' . ($summary['promotions'] === 1 ? 'promoção' : 'promoções')" />
        <x-gestao-stat label="Faturado" :value="$money($summary['revenue'])" icon="bi-cash-stack" tone="indigo" hint="com preço promocional" />
        <x-gestao-stat label="Desconto" :value="$money($summary['discount'])" icon="bi-tag" tone="amber" hint="preço “de” − preço cobrado" />
        <x-gestao-stat label="Lucro" :value="$money($summary['profit'])" icon="bi-graph-up-arrow" :tone="$summary['profit'] >= 0 ? 'emerald' : 'rose'"
            :hint="'margem ' . $num($summary['margin']) . '%' . ($summary['missingCost'] ? ' · custo incompleto' : '')" />
        <div class="col-span-2 sm:col-span-1">
            <x-gestao-stat label="Fatia do total" :value="$num($summary['share']) . '%'" icon="bi-pie-chart" tone="sky"
                :hint="'de ' . $money($summary['allRevenue']) . ' vendidos'" />
        </div>
    </div>

    {{-- Barra: promoção x preço cheio --}}
    @if ($summary['allRevenue'] > 0)
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 text-sm">
                <p class="font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-pie-chart mr-1"></i>Vendas do período</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $num($summary['shareQty']) }}% das unidades ({{ $num($summary['qty'], 0) }} de {{ $num($summary['allQty'], 0) }}) saíram em promoção</p>
            </div>
            <div class="mt-3 flex h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                <div class="h-full bg-gradient-to-r from-rose-500 to-orange-500" style="width: {{ max(0, min(100, $summary['share'])) }}%"></div>
            </div>
            <div class="mt-2 flex flex-wrap justify-between gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>Em promoção <b class="text-slate-800 dark:text-slate-100">{{ $money($summary['revenue']) }}</b></span>
                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><span class="h-2.5 w-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Preço normal <b class="text-slate-800 dark:text-slate-100">{{ $money($summary['allRevenue'] - $summary['revenue']) }}</b></span>
            </div>
        </div>
    @endif

    {{-- ============ TABELA ============ --}}
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 p-4 border-b border-slate-100 dark:border-slate-800">
            <p class="font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-tags mr-1"></i>Por promoção</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Cada linha é uma promoção de um produto. Toque no título da coluna para ordenar.</p>
        </div>

        <div class="relative">
            <div wire:loading.flex class="absolute inset-0 rounded-b-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
                <i class="bi bi-arrow-repeat animate-spin text-2xl text-rose-500"></i>
            </div>

            @if ($rows->isEmpty())
                <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                    <i class="bi bi-tag text-3xl"></i>
                    <p class="mt-2 font-semibold">Nenhuma venda em promoção nesse período.</p>
                    <p class="mt-1 text-sm">Os itens contam aqui quando o produto está com promoção no ar na hora da venda.</p>
                    <a href="{{ route('promotions.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-2 text-sm font-bold text-white shadow-sm"><i class="bi bi-fire"></i>Ver promoções</a>
                </div>
            @else
                {{-- Desktop --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[11px] uppercase tracking-wide text-slate-400">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="px-4 py-2 text-left font-semibold">Produto</th>
                                <th class="px-3 py-2 text-right font-semibold">Preço</th>
                                @foreach (['qty' => 'Qtd', 'revenue' => 'Faturamento', 'discount' => 'Desconto', 'profit' => 'Lucro', 'margin' => 'Margem', 'lift' => 'Antes x durante'] as $col => $label)
                                    <th class="px-3 py-2 text-right font-semibold {{ $loop->last ? 'pr-4' : '' }}">
                                        <button type="button" wire:click="sortBy('{{ $col }}')" class="inline-flex items-center gap-1 uppercase hover:text-rose-600 {{ $sort === $col ? 'text-rose-600 dark:text-rose-300' : '' }}"
                                            @if ($col === 'lift') title="Unidades por dia antes da promoção (mesmo número de dias, até 30) e durante ela" @endif>
                                            {{ $label }} <i class="bi {{ $sortIcon($col) }}"></i>
                                        </button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($rows as $row)
                                @php
                                    $good = $row['profit'] >= 0;
                                    $st = $statusMeta[$row['status']] ?? null;
                                    $pace = $row['pace'];
                                @endphp
                                <tr wire:key="row-{{ $row['promotion_id'] }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img src="{{ $row['image'] }}" alt="" loading="lazy" class="h-11 w-11 shrink-0 rounded-xl object-cover border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 dark:text-white truncate max-w-[280px]" title="{{ $row['name'] }}">{{ $row['name'] }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-x-1.5">
                                                    @if ($row['code'])<span>#{{ $row['code'] }}</span>·@endif
                                                    @if ($st)<span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $st[1] }}">{{ $st[0] }}</span>@endif
                                                    @if ($row['starts_at'])
                                                        <span>{{ $row['starts_at']->format('d/m') }}{{ $row['ends_at'] ? ' – ' . $row['ends_at']->format('d/m') : ' em diante' }}</span>
                                                    @endif
                                                    @if ($row['missingCost'])· <span class="font-semibold text-amber-600">custo incompleto</span>@endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                        <p class="font-bold text-rose-600 dark:text-rose-300">{{ $money($row['promo_price']) }}</p>
                                        <p class="text-[11px] text-slate-400 line-through">{{ $money($row['original_price']) }}</p>
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-slate-700 dark:text-slate-200">{{ $num($row['qty'], 0) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $money($row['revenue']) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap text-amber-600 dark:text-amber-300">{{ $money($row['discount']) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap font-black {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($row['profit']) }}</td>
                                    <td class="px-3 py-2.5 text-right font-bold {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $num($row['margin']) }}%</td>
                                    <td class="px-3 pr-4 py-2.5 text-right min-w-[150px]">
                                        @include('livewire.promotions.partials.report-pace', ['pace' => $pace, 'lift' => $row['lift'], 'num' => $num])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 dark:border-slate-700 text-sm">
                            <tr>
                                <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-200" colspan="2">Total</td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-800 dark:text-slate-100">{{ $num($summary['qty'], 0) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold whitespace-nowrap text-slate-800 dark:text-slate-100">{{ $money($summary['revenue']) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold whitespace-nowrap text-amber-600 dark:text-amber-300">{{ $money($summary['discount']) }}</td>
                                <td class="px-3 py-2.5 text-right font-black whitespace-nowrap {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($summary['profit']) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $num($summary['margin']) }}%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Celular / tablet --}}
                <div class="lg:hidden">
                    <div class="-mx-px overflow-x-auto border-b border-slate-100 dark:border-slate-800">
                        <div class="flex min-w-max items-center gap-1 px-3 py-2">
                            <span class="mr-1 text-[11px] font-semibold uppercase text-slate-400"><i class="bi bi-arrow-down-up"></i></span>
                            @foreach ($sortLabels as $col => $label)
                                <button type="button" wire:click="sortBy('{{ $col }}')" class="{{ $pill }} {{ $sort === $col ? $pillOn : $pillOff }}">
                                    {{ ucfirst($label) }} @if ($sort === $col)<i class="bi {{ $dir === 'desc' ? 'bi-arrow-down' : 'bi-arrow-up' }}"></i>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($rows as $row)
                            @php
                                $good = $row['profit'] >= 0;
                                $st = $statusMeta[$row['status']] ?? null;
                            @endphp
                            <div wire:key="mrow-{{ $row['promotion_id'] }}" class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $row['image'] }}" alt="" loading="lazy" class="h-14 w-14 shrink-0 rounded-xl object-cover border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="font-semibold text-slate-900 dark:text-white line-clamp-2 break-words">{{ $row['name'] }}</p>
                                            <div class="text-right shrink-0">
                                                <p class="font-black whitespace-nowrap {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($row['profit']) }}</p>
                                                <p class="text-[11px] font-bold {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $num($row['margin']) }}% margem</p>
                                            </div>
                                        </div>
                                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                            <span class="font-bold text-rose-600 dark:text-rose-300">{{ $money($row['promo_price']) }}</span>
                                            <span class="line-through">{{ $money($row['original_price']) }}</span>
                                            @if ($st)<span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $st[1] }}">{{ $st[0] }}</span>@endif
                                            @if ($row['missingCost'])<span class="font-semibold text-amber-600">custo incompleto</span>@endif
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2.5 grid grid-cols-3 gap-2 text-[11px]">
                                    <div><p class="uppercase text-slate-400 font-semibold">Vendidos</p><p class="text-slate-700 dark:text-slate-200 font-semibold">{{ $num($row['qty'], 0) }} un.</p></div>
                                    <div><p class="uppercase text-slate-400 font-semibold">Faturou</p><p class="text-slate-700 dark:text-slate-200 font-semibold whitespace-nowrap">{{ $money($row['revenue']) }}</p></div>
                                    <div><p class="uppercase text-slate-400 font-semibold">Desconto</p><p class="text-amber-600 dark:text-amber-300 font-semibold whitespace-nowrap">{{ $money($row['discount']) }}</p></div>
                                </div>
                                @if ($row['pace'])
                                    <div class="mt-2 flex flex-wrap items-center justify-between gap-x-2 gap-y-1 rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 text-[11px]">
                                        <span class="whitespace-nowrap font-semibold uppercase text-slate-400">Antes x durante</span>
                                        @include('livewire.promotions.partials.report-pace', ['pace' => $row['pace'], 'lift' => $row['lift'], 'num' => $num])
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
        <i class="bi bi-info-circle mr-1"></i>
        Conta os itens vendidos com a promoção no ar (vendas canceladas e orçamentos ficam de fora), pela data da venda.
        Desconto = (preço “de” − preço cobrado) × quantidade. Lucro = faturado − preço de custo gravado na venda.
        Fatia = faturado em promoção ÷ faturado com todos os itens no período.
        “Antes x durante” compara unidades por dia durante toda a promoção com o mesmo número de dias (até 30) logo antes dela.
    </p>
</div>
