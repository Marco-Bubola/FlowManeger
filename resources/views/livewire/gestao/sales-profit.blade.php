<div class="w-full px-4 sm:px-6 py-4 space-y-4">
    <x-sales-header title="Lucro por Venda"
        description="Quanto cada venda deixou de lucro, descontando o custo dos produtos"
        icon="bi-graph-up-arrow" iconColor="green" :back-route="route('sales.index')" />

    <div class="flex items-center gap-3">
        <button type="button" wire:click="shiftMonth(-1)" aria-label="Mês anterior" class="w-9 h-9 rounded-xl border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="bi bi-chevron-left"></i>
        </button>
        <span class="text-lg font-black text-slate-800 dark:text-slate-100 capitalize min-w-[160px] text-center">
            {{ $start->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}
        </span>
        <button type="button" wire:click="shiftMonth(1)" aria-label="Próximo mês" class="w-9 h-9 rounded-xl border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach ([
            ['Faturamento', $summary['revenue'], 'text-slate-800 dark:text-slate-100'],
            ['Custo dos produtos', $summary['cost'], 'text-slate-600 dark:text-slate-300'],
            ['Lucro', $summary['profit'], $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600'],
            ['Ticket médio', $summary['ticket'], 'text-slate-800 dark:text-slate-100'],
        ] as [$label, $value, $class])
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="text-lg font-black {{ $class }}">R$ {{ number_format($value, 2, ',', '.') }}</p>
            </div>
        @endforeach
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Margem · {{ $summary['count'] }} vendas</p>
            <p class="text-lg font-black {{ $summary['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($summary['margin'], 1, ',', '.') }}%</p>
        </div>
    </div>

    <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 overflow-hidden">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            <a href="{{ route('sales.show', $row['sale']->id) }}" wire:key="profit-{{ $row['sale']->id }}"
                class="flex flex-wrap items-center gap-x-6 gap-y-1 px-4 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0 hover:bg-slate-50 dark:hover:bg-zinc-800/60">
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-800 dark:text-slate-100">Venda #{{ $row['sale']->id }} · {{ $row['sale']->client->name ?? 'Sem cliente' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $row['sale']->created_at?->format('d/m/Y') }} · {{ $row['sale']->saleItems->sum('quantity') }} itens
                        @if ($row['missingCost'])
                            · <span class="text-amber-600" title="Algum produto está sem preço de custo">custo incompleto</span>
                        @endif
                    </p>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-300 w-28 text-right">R$ {{ number_format($row['revenue'], 2, ',', '.') }}</div>
                <div class="text-sm font-black w-28 text-right {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">R$ {{ number_format($row['profit'], 2, ',', '.') }}</div>
                <div class="text-xs font-bold w-16 text-right {{ $row['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($row['margin'], 1, ',', '.') }}%</div>
            </a>
        @empty
            <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-receipt text-3xl"></i>
                <p class="mt-2">Nenhuma venda nesse mês.</p>
            </div>
        @endforelse
    </div>
</div>
