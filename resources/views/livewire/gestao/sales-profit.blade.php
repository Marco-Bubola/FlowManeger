<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Lucro por venda" subtitle="Quanto cada venda deixou de lucro, descontando o custo dos produtos"
        icon="bi-graph-up-arrow" active="lucro">
        <x-slot:actions>
            <div class="inline-flex items-center gap-1 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/85 dark:bg-slate-900/80 p-1 shadow-sm">
                <button type="button" wire:click="shiftMonth(-1)" aria-label="Mês anterior" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="min-w-[150px] text-center text-sm font-bold text-slate-800 dark:text-slate-100">
                    {{ ucfirst($start->locale('pt_BR')->isoFormat('MMMM [de] YYYY')) }}
                </span>
                <button type="button" wire:click="shiftMonth(1)" aria-label="Próximo mês" class="h-9 w-9 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </x-slot:actions>
    </x-gestao-header>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 [&>*:last-child:nth-child(odd)]:col-span-2 sm:[&>*:last-child:nth-child(odd)]:col-span-1">
        <x-gestao-stat label="Faturamento" :value="'R$ ' . number_format($summary['revenue'], 2, ',', '.')" icon="bi-cash-stack" tone="indigo" />
        <x-gestao-stat label="Custo dos produtos" :value="'R$ ' . number_format($summary['cost'], 2, ',', '.')" icon="bi-box-seam" tone="slate" />
        <x-gestao-stat label="Lucro" :value="'R$ ' . number_format($summary['profit'], 2, ',', '.')" icon="bi-graph-up-arrow" :tone="$summary['profit'] >= 0 ? 'emerald' : 'rose'" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 -mt-2">
        <x-gestao-stat label="Ticket médio" :value="'R$ ' . number_format($summary['ticket'], 2, ',', '.')" icon="bi-receipt" tone="sky" />
        <x-gestao-stat label="Margem" :value="number_format($summary['margin'], 1, ',', '.') . '%'" icon="bi-percent" :tone="$summary['margin'] >= 0 ? 'emerald' : 'rose'" :hint="$summary['count'] . ' ' . ($summary['count'] === 1 ? 'venda' : 'vendas')" />
    </div>

    <div class="relative space-y-2">
        <div wire:loading.flex class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php
                $good = $row['profit'] >= 0;
                $bar = max(0, min(100, (float) $row['margin']));
                $qty = (int) $row['sale']->saleItems->sum('quantity');
            @endphp
            <a href="{{ route('sales.show', $row['sale']->id) }}" wire:key="profit-{{ $row['sale']->id }}"
                class="flex flex-wrap sm:flex-nowrap items-center gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-4 py-3 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <x-client-avatar :name="$row['sale']->client->name ?? '?'" :photo="$row['sale']->client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-full" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $row['sale']->client->name ?? 'Sem cliente' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                            Venda #{{ $row['sale']->id }} · {{ $row['sale']->created_at?->format('d/m/Y') }} · {{ $qty }} {{ $qty === 1 ? 'item' : 'itens' }}
                            @if ($row['missingCost'])
                                · <span class="font-semibold text-amber-600" title="Algum produto está sem preço de custo">custo incompleto</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="grid w-full sm:w-auto grid-cols-3 gap-3 sm:flex sm:items-center sm:gap-5">
                    <div class="sm:w-28 sm:text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Vendeu</p>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">R$ {{ number_format($row['revenue'], 2, ',', '.') }}</p>
                    </div>
                    <div class="sm:w-28 sm:text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Lucro</p>
                        <p class="text-sm font-black whitespace-nowrap {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">R$ {{ number_format($row['profit'], 2, ',', '.') }}</p>
                    </div>
                    <div class="sm:w-24">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 sm:text-right">Margem</p>
                        <p class="text-sm font-bold sm:text-right {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($row['margin'], 1, ',', '.') }}%</p>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                            <div class="h-full rounded-full {{ $good ? ($bar >= 30 ? 'bg-emerald-500' : 'bg-amber-500') : 'bg-rose-500' }}" style="width: {{ $good ? $bar : 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-receipt text-3xl"></i>
                <p class="mt-2">Nenhuma venda nesse mês.</p>
            </div>
        @endforelse
    </div>
</div>
