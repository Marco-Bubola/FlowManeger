<div class="w-full px-4 sm:px-6 py-4 space-y-4">
    <x-sales-header title="Movimentações de Estoque"
        description="Toda entrada e saída de estoque, com data, origem e quantidade"
        icon="bi-arrow-left-right" iconColor="blue" :back-route="route('products.index')" />

    <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Produto</label>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Nome ou código"
                class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-800 text-slate-800 dark:text-slate-100">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tipo</label>
            <select wire:model.live="direction" class="mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-800 text-slate-800 dark:text-slate-100">
                <option value="">Entradas e saídas</option>
                <option value="entrada">Só entradas</option>
                <option value="saida">Só saídas</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Período</label>
            <select wire:model.live="period" class="mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-800 text-slate-800 dark:text-slate-100">
                <option value="7">Últimos 7 dias</option>
                <option value="30">Últimos 30 dias</option>
                <option value="90">Últimos 90 dias</option>
                <option value="all">Tudo</option>
            </select>
        </div>
    </div>

    <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 overflow-hidden">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($logs as $log)
            @php $p = $products[$log->product_id] ?? null; $in = $log->quantity_change > 0; @endphp
            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 px-4 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $in ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40' : 'bg-red-100 text-red-600 dark:bg-red-900/40' }}">
                    <i class="bi {{ $in ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up' }}"></i>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $p->name ?? 'Produto removido' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $p->product_code ?? '' }} · {{ $log->getOperationDescription() }}</p>
                </div>
                <div class="text-sm font-bold {{ $in ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $in ? '+' : '' }}{{ $log->quantity_change }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 w-28">{{ $log->quantity_before }} → {{ $log->quantity_after }} un.</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 w-32 text-right">{{ $log->created_at?->format('d/m/Y H:i') }}</div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-inbox text-3xl"></i>
                <p class="mt-2">Nenhuma movimentação nesse filtro.</p>
            </div>
        @endforelse
    </div>

    {{ $logs->links() }}
</div>
