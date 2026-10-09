@php
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $channelMeta = \App\Livewire\Gestao\ChannelProfit::CHANNELS;
    $periodLabel = $months === 1
        ? ucfirst($end->locale('pt_BR')->isoFormat('MMMM [de] YYYY'))
        : ucfirst($start->locale('pt_BR')->isoFormat('MMM/YY')) . ' – ' . ucfirst($end->locale('pt_BR')->isoFormat('MMM/YY'));
    $pill = 'rounded-xl px-3 py-1.5 text-xs font-bold transition';
    $pillOn = 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm';
    $pillOff = 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
@endphp

<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Lucro real" subtitle="Quanto sobra de verdade em cada canal, depois do custo dos produtos e das taxas"
        icon="bi-pie-chart" active="lucro-real">
        <x-slot:actions>
            <div class="inline-flex items-center gap-1 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 p-1 shadow-sm">
                <button type="button" wire:click="shiftMonth(-1)" aria-label="Mês anterior" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="min-w-[150px] text-center text-sm font-bold text-slate-800 dark:text-slate-100">{{ $periodLabel }}</span>
                <button type="button" wire:click="shiftMonth(1)" aria-label="Próximo mês" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="inline-flex items-center gap-1 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 p-1 shadow-sm">
                @foreach ([1 => 'Mês', 3 => '3 meses', 6 => '6 meses', 12 => '12 meses'] as $n => $label)
                    <button type="button" wire:click="setMonths({{ $n }})" class="{{ $pill }} {{ $months === $n ? $pillOn : $pillOff }}">{{ $label }}</button>
                @endforeach
            </div>
            <button type="button" wire:click="$toggle('showSettings')" aria-label="Configurar taxas"
                class="h-11 w-11 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 text-slate-600 dark:text-slate-300 shadow-sm hover:text-indigo-600 {{ $showSettings ? 'ring-2 ring-indigo-400' : '' }}">
                <i class="bi bi-sliders"></i>
            </button>
        </x-slot:actions>
    </x-gestao-header>

    @if ($showSettings)
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-sliders mr-1"></i>Taxa estimada dos marketplaces</p>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Usada só nos pedidos em que a taxa real não veio do marketplace. Esses valores aparecem como "estimada".</p>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl">
                <label class="block">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mercado Livre (%)</span>
                    <input type="number" step="0.1" min="0" max="100" wire:model.blur="mlFeePct"
                        class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-indigo-400">
                </label>
                <label class="block">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Shopee (%)</span>
                    <input type="number" step="0.1" min="0" max="100" wire:model.blur="shopeeFeePct"
                        class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-indigo-400">
                </label>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-gestao-stat label="Faturamento total" :value="$money($total['revenue'])" icon="bi-cash-stack" tone="indigo" />
        <x-gestao-stat label="Lucro real" :value="$money($total['profit'])" icon="bi-graph-up-arrow" :tone="$total['profit'] >= 0 ? 'emerald' : 'rose'" />
        <x-gestao-stat label="Margem" :value="number_format($total['margin'], 1, ',', '.') . '%'" icon="bi-percent" :tone="$total['margin'] >= 0 ? 'emerald' : 'rose'" />
    </div>

    {{-- Cards por canal --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach ($channels as $key => $c)
            @php $good = $c['profit'] >= 0; @endphp
            <div wire:key="channel-{{ $key }}" class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow">
                            <i class="bi {{ $channelMeta[$key]['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-slate-900 dark:text-white">{{ $channelMeta[$key]['label'] }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $c['orders'] }} {{ $c['orders'] === 1 ? 'pedido' : 'pedidos' }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-xs font-black {{ $good ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}">
                        {{ number_format($c['margin'], 1, ',', '.') }}%
                    </span>
                </div>

                <dl class="mt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500 dark:text-slate-400">Faturamento</dt><dd class="font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">{{ $money($c['revenue']) }}</dd></div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Custo dos produtos
                            @if ($c['missingCost'])<i class="bi bi-exclamation-triangle-fill text-amber-500" title="Há produtos sem preço de custo ou anúncios sem produto vinculado"></i>@endif
                        </dt>
                        <dd class="font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">− {{ $money($c['cost']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Taxas
                            @if ($c['feesEstimated'] > 0)
                                <span class="ml-1 rounded-md bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-700 dark:text-amber-300"
                                    title="{{ $money($c['feesEstimated']) }} estimados pelo percentual configurado">{{ $c['feesEstimated'] >= $c['fees'] ? 'estimada' : 'parte estimada' }}</span>
                            @endif
                        </dt>
                        <dd class="font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">− {{ $money($c['fees']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Frete pago</dt>
                        <dd class="font-semibold whitespace-nowrap {{ $c['shippingKnown'] ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                            {{ $c['shippingKnown'] ? '− ' . $money($c['shipping']) : 'não informado' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2 border-t border-slate-100 dark:border-slate-800 pt-2">
                        <dt class="font-bold text-slate-700 dark:text-slate-200">Lucro</dt>
                        <dd class="text-base font-black whitespace-nowrap {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($c['profit']) }}</dd>
                    </div>
                </dl>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div class="h-full rounded-full {{ $good ? ($c['margin'] >= 30 ? 'bg-emerald-500' : 'bg-amber-500') : 'bg-rose-500' }}" style="width: {{ $good ? max(0, min(100, $c['margin'])) : 100 }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tabela por produto --}}
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-4 border-b border-slate-100 dark:border-slate-800">
            <p class="font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-box-seam mr-1"></i>Por produto</p>
            <div class="-mx-1 overflow-x-auto">
                <div class="inline-flex min-w-max items-center gap-1 px-1">
                    <button type="button" wire:click="setChannel('all')" class="{{ $pill }} {{ $channel === 'all' ? $pillOn : $pillOff }}">Todos</button>
                    @foreach ($channelMeta as $key => $meta)
                        <button type="button" wire:click="setChannel('{{ $key }}')" class="{{ $pill }} {{ $channel === $key ? $pillOn : $pillOff }}">{{ $meta['label'] }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="relative">
            <div wire:loading.flex class="absolute inset-0 rounded-b-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
                <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
            </div>

            @if ($rows->isEmpty())
                <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                    <i class="bi bi-receipt text-3xl"></i>
                    <p class="mt-2">Nenhuma venda nesse período.</p>
                </div>
            @else
                {{-- Desktop --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[11px] uppercase tracking-wide text-slate-400">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="px-4 py-2 text-left font-semibold">Produto</th>
                                <th class="px-3 py-2 text-right font-semibold">Qtd vendida</th>
                                <th class="px-3 py-2 text-right font-semibold">Faturamento</th>
                                <th class="px-3 py-2 text-right font-semibold">Custo</th>
                                <th class="px-3 py-2 text-right font-semibold" title="Taxas e frete do pedido rateados pelo valor de cada produto">Taxas rateadas</th>
                                <th class="px-3 py-2 text-right font-semibold">
                                    <button type="button" wire:click="toggleSort" class="inline-flex items-center gap-1 uppercase hover:text-indigo-600">
                                        Lucro <i class="bi {{ $sort === 'desc' ? 'bi-sort-down' : 'bi-sort-up' }}"></i>
                                    </button>
                                </th>
                                <th class="px-4 py-2 text-right font-semibold">Margem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($rows as $i => $row)
                                @php $good = $row['profit'] >= 0; @endphp
                                <tr wire:key="row-{{ $i }}-{{ md5($row['name']) }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-4 py-2.5">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ collect($row['channels'])->map(fn ($c) => $channelMeta[$c]['label'])->join(' · ') }}
                                            @if ($row['missingCost'])· <span class="font-semibold text-amber-600">custo incompleto</span>@endif
                                        </p>
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-slate-700 dark:text-slate-200">{{ number_format($row['qty'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-700 dark:text-slate-200">{{ $money($row['revenue']) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $money($row['cost']) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $money($row['fees']) }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap font-black {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($row['profit']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($row['margin'], 1, ',', '.') }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Celular --}}
                <div class="md:hidden">
                    <button type="button" wire:click="toggleSort" class="w-full px-4 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        Ordenado por lucro {{ $sort === 'desc' ? '(maior primeiro)' : '(menor primeiro)' }} <i class="bi bi-arrow-down-up"></i>
                    </button>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($rows as $i => $row)
                            @php $good = $row['profit'] >= 0; @endphp
                            <div wire:key="mrow-{{ $i }}-{{ md5($row['name']) }}" class="px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ number_format($row['qty'], 0, ',', '.') }} vendidos · {{ collect($row['channels'])->map(fn ($c) => $channelMeta[$c]['label'])->join(' · ') }}
                                            @if ($row['missingCost'])· <span class="font-semibold text-amber-600">custo incompleto</span>@endif
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-black whitespace-nowrap {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($row['profit']) }}</p>
                                        <p class="text-xs font-bold {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($row['margin'], 1, ',', '.') }}%</p>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                                    <div><p class="uppercase text-slate-400 font-semibold">Faturou</p><p class="text-slate-700 dark:text-slate-200 font-semibold">{{ $money($row['revenue']) }}</p></div>
                                    <div><p class="uppercase text-slate-400 font-semibold">Custo</p><p class="text-slate-700 dark:text-slate-200 font-semibold">{{ $money($row['cost']) }}</p></div>
                                    <div><p class="uppercase text-slate-400 font-semibold">Taxas</p><p class="text-slate-700 dark:text-slate-200 font-semibold">{{ $money($row['fees']) }}</p></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <p class="text-xs text-slate-500 dark:text-slate-400">
        <i class="bi bi-info-circle mr-1"></i>
        Lucro = faturamento − custo dos produtos − taxas − frete pago pelo vendedor (quando o marketplace informa).
        O custo dos marketplaces usa o preço de custo atual dos produtos vinculados ao anúncio; pedidos do Mercado Livre importados como venda contam só no Mercado Livre.
    </p>
</div>
