<div class="client-resumo-page w-full mobile-393-base">
    <x-client-page-header :client="$client" title="Resumo financeiro" icon="bi-graph-up" active="resumo">
        @if($client->portal_active)
            <x-slot name="actions">
                <a href="{{ route('portal.login') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-semibold shadow-md transition">
                    <i class="bi bi-box-arrow-up-right"></i>Abrir portal
                </a>
            </x-slot>
        @endif
    </x-client-page-header>

    @php
        $movimentado = max($totalRecebido + $totalEnviado, 1);
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        // Listas de transferências: mesmo cartão para recebidas e enviadas.
        $listas = [
            ['key' => 'recebidas', 'title' => 'Recebidas', 'icon' => 'bi-arrow-down-left', 'tone' => 'is-in', 'sign' => '+',
             'items' => $transferenciasRecebidas, 'total' => $recebidasTotal, 'page' => $recebidasPage,
             'prev' => 'prevRecebidasPage', 'next' => 'nextRecebidasPage', 'empty' => 'Nenhuma transferência recebida'],
            ['key' => 'enviadas', 'title' => 'Enviadas', 'icon' => 'bi-arrow-up-right', 'tone' => 'is-out', 'sign' => '−',
             'items' => $transferenciasEnviadas, 'total' => $enviadasTotal, 'page' => $enviadasPage,
             'prev' => 'prevEnviadasPage', 'next' => 'nextEnviadasPage', 'empty' => 'Nenhuma transferência enviada'],
        ];
        $catTotal = max(collect($categories)->sum(fn ($c) => (float) ((array) $c)['value']), 1);
        $catColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-sky-500', 'bg-purple-500', 'bg-teal-500', 'bg-orange-500'];
    @endphp

    <div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16 space-y-5">
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                <i class="bi bi-check-circle-fill text-lg"></i>{{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-2 sm:gap-3">
            <x-gestao-stat label="Recebido" :value="$money($totalRecebido)" icon="bi-arrow-down-left" tone="emerald" :hint="number_format($totalRecebido / $movimentado * 100, 0) . '% do movimentado'" />
            <x-gestao-stat label="Enviado" :value="$money($totalEnviado)" icon="bi-arrow-up-right" tone="rose" :hint="number_format($totalEnviado / $movimentado * 100, 0) . '% do movimentado'" />
            <x-gestao-stat label="Faturas" :value="$money($totalFaturas)" icon="bi-receipt" tone="amber" :hint="$faturasTotal . ' ' . ($faturasTotal == 1 ? 'fatura' : 'faturas')" />
            <x-gestao-stat label="Saldo" :value="$money($saldoAtual)" icon="bi-wallet2" :tone="$saldoAtual >= 0 ? 'sky' : 'rose'" :hint="$saldoAtual >= 0 ? 'Positivo' : 'Negativo'" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            {{-- Faturas --}}
            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-receipt"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Faturas <span class="crs-count">{{ $faturasTotal }}</span></h2>
                        <p class="crs-sub">Toque no botão para dividir a fatura</p>
                    </div>
                    <a href="{{ route('clients.faturas', $client->id) }}" class="crs-link">Ver todas<i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="crs-list">
                    @forelse($faturas as $fatura)
                        <div class="crs-row" wire:key="fat-{{ $fatura['id'] }}">
                            <span class="crs-row-icon bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                                <i class="{{ $fatura['category']['icone'] ?? 'bi bi-receipt' }}"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="crs-row-title">{{ $fatura['description'] }}</p>
                                <p class="crs-row-meta">
                                    {{ \Carbon\Carbon::parse($fatura['invoice_date'])->format('d/m/Y') }}
                                    @if($fatura['category']) · {{ $fatura['category']['name'] }} @endif
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span class="crs-row-value">{{ $money($fatura['dividida'] ? $fatura['value'] / 2 : $fatura['value']) }}</span>
                                <button type="button" wire:click="toggleDividida({{ $fatura['id'] }})"
                                        class="crs-split {{ $fatura['dividida'] ? 'is-on' : '' }}" title="Dividir fatura ao meio">
                                    <span class="crs-split-dot"></span>{{ $fatura['dividida'] ? 'Dividida' : 'Integral' }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="crs-empty"><i class="bi bi-receipt"></i><p>Nenhuma fatura deste cliente</p></div>
                    @endforelse
                </div>
                @if($faturasTotal > $perPage)
                    <div class="crs-pager">
                        <button type="button" wire:click="prevFaturasPage" @disabled($faturasPage <= 1)><i class="bi bi-chevron-left"></i></button>
                        <span>Página {{ $faturasPage }} de {{ (int) ceil($faturasTotal / $perPage) }}</span>
                        <button type="button" wire:click="nextFaturasPage" @disabled($faturasPage >= ceil($faturasTotal / $perPage))><i class="bi bi-chevron-right"></i></button>
                    </div>
                @endif
            </section>

            {{-- Transferências recebidas e enviadas --}}
            @foreach($listas as $lista)
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon {{ $lista['tone'] === 'is-in' ? 'bg-gradient-to-br from-emerald-500 to-teal-600' : 'bg-gradient-to-br from-rose-500 to-pink-600' }}"><i class="bi {{ $lista['icon'] }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">{{ $lista['title'] }} <span class="crs-count">{{ $lista['total'] }}</span></h2>
                            <p class="crs-sub">Transferências no livro caixa</p>
                        </div>
                        <a href="{{ route('clients.transferencias', ['cliente' => $client->id, 'tipo' => $lista['key']]) }}" class="crs-link">Ver todas<i class="bi bi-chevron-right"></i></a>
                    </div>
                    <div class="crs-list">
                        @forelse($lista['items'] as $t)
                            <div class="crs-row" wire:key="{{ $lista['key'] }}-{{ $t['id'] }}">
                                <span class="crs-row-icon {{ $lista['tone'] === 'is-in' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300' }}">
                                    <i class="bi {{ $lista['icon'] }}"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="crs-row-title">{{ $t['description'] ?: 'Sem descrição' }}</p>
                                    <p class="crs-row-meta">{{ \Carbon\Carbon::parse($t['date'])->format('d/m/Y') }}</p>
                                </div>
                                <span class="crs-row-value {{ $lista['tone'] === 'is-in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $lista['sign'] }} {{ $money($t['value']) }}</span>
                            </div>
                        @empty
                            <div class="crs-empty"><i class="bi {{ $lista['icon'] }}"></i><p>{{ $lista['empty'] }}</p></div>
                        @endforelse
                    </div>
                    @if($lista['total'] > $perPage)
                        @php $maxPage = (int) ceil($lista['total'] / $perPage); @endphp
                        <div class="crs-pager">
                            <button type="button" wire:click="{{ $lista['prev'] }}" @disabled($lista['page'] <= 1)><i class="bi bi-chevron-left"></i></button>
                            <span>Página {{ $lista['page'] }} de {{ $maxPage }}</span>
                            <button type="button" wire:click="{{ $lista['next'] }}" @disabled($lista['page'] >= $maxPage)><i class="bi bi-chevron-right"></i></button>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            {{-- Gastos por categoria (barras simples; antes era um gráfico que nunca carregava) --}}
            <section class="crs-card lg:col-span-2">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-amber-400 to-orange-500"><i class="bi bi-pie-chart"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Gastos por categoria</h2>
                        <p class="crs-sub">Faturas deste cliente, somando metade das divididas</p>
                    </div>
                </div>
                @if(count($categories) > 0)
                    <div class="p-4 space-y-3">
                        @foreach(collect($categories)->map(fn ($c) => (array) $c)->sortByDesc('value')->values() as $i => $cat)
                            @php $pct = (float) $cat['value'] / $catTotal * 100; @endphp
                            <div>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-2 min-w-0 font-semibold text-slate-700 dark:text-slate-200">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $catColors[$i % count($catColors)] }}"></span>
                                        <span class="truncate">{{ $cat['label'] }}</span>
                                    </span>
                                    <span class="shrink-0 font-bold text-slate-900 dark:text-white">{{ $money($cat['value']) }} <span class="text-xs font-semibold text-slate-400">{{ number_format($pct, 0) }}%</span></span>
                                </div>
                                <div class="mt-1.5 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full {{ $catColors[$i % count($catColors)] }}" style="width: {{ max(2, $pct) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="crs-empty"><i class="bi bi-pie-chart"></i><p>Nenhum gasto com faturas ainda</p></div>
                @endif
            </section>

            {{-- Atalhos --}}
            <section class="crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-lightning-charge"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Atalhos</h2>
                        <p class="crs-sub">Outras telas deste cliente</p>
                    </div>
                </div>
                <div class="crs-list">
                    @foreach([
                        [route('clients.faturas', $client->id), 'bi-receipt', 'Todas as faturas', 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300'],
                        [route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'recebidas']), 'bi-arrow-down-left', 'Transferências recebidas', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'],
                        [route('clients.transferencias', ['cliente' => $client->id, 'tipo' => 'enviadas']), 'bi-arrow-up-right', 'Transferências enviadas', 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300'],
                        [route('clients.edit', $client->id), 'bi-pencil', 'Editar cliente', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
                    ] as [$href, $icon, $label, $tone])
                        <a href="{{ $href }}" class="crs-row crs-row-link">
                            <span class="crs-row-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                            <span class="crs-row-title flex-1">{{ $label }}</span>
                            <i class="bi bi-chevron-right text-slate-400"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <style>
        .crs-card { border-radius: 1rem; border: 1px solid rgb(226 232 240 / .8); background: #fff; box-shadow: 0 1px 2px rgb(15 23 42 / .05); overflow: hidden; min-width: 0; }
        .dark .crs-card { border-color: rgb(51 65 85 / .7); background: rgb(15 23 42 / .8); }
        .crs-head { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-bottom: 1px solid rgb(241 245 249); }
        .dark .crs-head { border-bottom-color: rgb(30 41 59); }
        .crs-head-icon { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; box-shadow: 0 6px 14px -8px rgb(15 23 42 / .5); }
        .crs-title { font-weight: 800; font-size: .95rem; color: rgb(15 23 42); display: flex; align-items: center; gap: .4rem; }
        .dark .crs-title { color: #fff; }
        .crs-count { font-size: .7rem; font-weight: 700; padding: .05rem .45rem; border-radius: 999px; background: rgb(241 245 249); color: rgb(71 85 105); }
        .dark .crs-count { background: rgb(30 41 59); color: rgb(148 163 184); }
        .crs-sub { font-size: .72rem; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .crs-link { flex-shrink: 0; display: inline-flex; align-items: center; gap: .2rem; font-size: .75rem; font-weight: 700; color: rgb(79 70 229); padding: .35rem .6rem; border-radius: .6rem; background: rgb(238 242 255); }
        .dark .crs-link { color: rgb(165 180 252); background: rgb(49 46 129 / .35); }
        .crs-list > * + * { border-top: 1px solid rgb(241 245 249); }
        .dark .crs-list > * + * { border-top-color: rgb(30 41 59); }
        .crs-row { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; }
        .crs-row-link:hover { background: rgb(248 250 252); }
        .dark .crs-row-link:hover { background: rgb(30 41 59 / .5); }
        .crs-row-icon { width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .7rem; display: flex; align-items: center; justify-content: center; font-size: .95rem; }
        .crs-row-title { font-size: .875rem; font-weight: 600; color: rgb(15 23 42); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .crs-row-title { color: rgb(241 245 249); }
        .crs-row-meta { font-size: .72rem; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .crs-row-value { flex-shrink: 0; font-size: .875rem; font-weight: 800; color: rgb(15 23 42); white-space: nowrap; }
        .dark .crs-row-value { color: #fff; }
        .crs-split { display: inline-flex; align-items: center; gap: .35rem; font-size: .68rem; font-weight: 700; padding: .2rem .5rem; border-radius: 999px; color: rgb(100 116 139); background: rgb(241 245 249); }
        .crs-split-dot { width: .5rem; height: .5rem; border-radius: 999px; background: rgb(148 163 184); }
        .crs-split.is-on { color: rgb(67 56 202); background: rgb(224 231 255); }
        .crs-split.is-on .crs-split-dot { background: rgb(79 70 229); }
        .dark .crs-split { background: rgb(30 41 59); color: rgb(148 163 184); }
        .dark .crs-split.is-on { background: rgb(49 46 129 / .5); color: rgb(199 210 254); }
        .crs-empty { display: flex; flex-direction: column; align-items: center; gap: .4rem; padding: 2rem 1rem; color: rgb(148 163 184); font-size: .85rem; text-align: center; }
        .crs-empty i { font-size: 1.75rem; opacity: .6; }
        .crs-pager { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .6rem 1rem; border-top: 1px solid rgb(241 245 249); font-size: .75rem; font-weight: 600; color: rgb(100 116 139); }
        .dark .crs-pager { border-top-color: rgb(30 41 59); }
        .crs-pager button { width: 2rem; height: 2rem; border-radius: .6rem; display: flex; align-items: center; justify-content: center; background: rgb(241 245 249); color: rgb(51 65 85); }
        .dark .crs-pager button { background: rgb(30 41 59); color: rgb(226 232 240); }
        .crs-pager button:disabled { opacity: .4; }
        @media (max-width: 640px) { .crs-sub { display: none; } }
    </style>
</div>
