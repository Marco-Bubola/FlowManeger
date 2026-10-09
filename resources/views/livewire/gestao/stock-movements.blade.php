<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Movimentações de estoque" subtitle="Toda entrada e saída de estoque, com data, origem e quantidade"
        icon="bi-arrow-left-right" active="movimentacoes" />

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 [&>*:last-child:nth-child(odd)]:col-span-2 sm:[&>*:last-child:nth-child(odd)]:col-span-1">
        <x-gestao-stat label="Entradas" :value="'+' . $totals['in'] . ' un.'" icon="bi-box-arrow-in-down" tone="emerald" />
        <x-gestao-stat label="Saídas" :value="'-' . $totals['out'] . ' un.'" icon="bi-box-arrow-up" tone="rose" />
        <x-gestao-stat label="Movimentações" :value="$totals['count']" icon="bi-list-ul" tone="indigo" hint="no período escolhido" />
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 sm:p-4 shadow-sm flex flex-col lg:flex-row gap-3 lg:items-center">
        <div class="relative flex-1">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar produto por nome ou código"
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100">
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'Tudo', 'entrada' => 'Entradas', 'saida' => 'Saídas'] as $key => $label)
                <button type="button" wire:click="$set('direction', '{{ $key }}')"
                    class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ $direction === $key ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach (['7' => '7 dias', '30' => '30 dias', '90' => '90 dias', 'all' => 'Sempre'] as $key => $label)
                <button type="button" wire:click="$set('period', '{{ $key }}')"
                    class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ (string) $period === (string) $key ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="relative space-y-2">
        <div wire:loading.flex class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($logs as $log)
            @php $p = $products[$log->product_id] ?? null; $in = $log->quantity_change > 0; @endphp
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-4 py-3 shadow-sm hover:shadow-md transition" wire:key="mov-{{ $log->id }}">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $in ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10' }}">
                    <i class="bi {{ $in ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up' }} text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $p->name ?? 'Produto removido' }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        @if($p?->product_code)<span class="font-mono">{{ $p->product_code }}</span> · @endif{{ $log->getOperationDescription() }}
                    </p>
                </div>
                <div class="hidden sm:block text-right text-xs text-slate-500 dark:text-slate-400 w-28">
                    <p>{{ $log->quantity_before }} → <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $log->quantity_after }}</span> un.</p>
                    <p>{{ $log->created_at?->format('d/m/Y H:i') }}</p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-sm font-black {{ $in ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}">
                    {{ $in ? '+' : '' }}{{ $log->quantity_change }}
                </span>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-inbox text-3xl"></i>
                <p class="mt-2">Nenhuma movimentação nesse filtro.</p>
            </div>
        @endforelse
    </div>

    {{ $logs->links() }}
</div>
