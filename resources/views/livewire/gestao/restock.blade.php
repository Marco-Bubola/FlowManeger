<div class="w-full px-4 sm:px-6 py-4 space-y-4">
    <x-sales-header title="Produtos para Repor"
        description="Estoque no mínimo ou abaixo, ordenado pelo que mais vende"
        icon="bi-box-seam" iconColor="orange" :back-route="route('products.index')" />

    <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-3">
        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
            Mostrar produtos com estoque até
            <input type="number" min="0" max="9999" inputmode="numeric" wire:model.live.debounce.500ms="minimum"
                class="w-20 px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-800 text-center font-bold">
            unidades
        </label>
        <span class="text-sm text-slate-500 dark:text-slate-400">
            {{ $rows->count() }} {{ $rows->count() === 1 ? 'produto' : 'produtos' }}
            @if ($zeroCount) · <strong class="text-red-600">{{ $zeroCount }} sem estoque</strong> @endif
        </span>
    </div>

    <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 overflow-hidden">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php $p = $row['product']; @endphp
            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 px-4 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0" wire:key="restock-{{ $p->id }}">
                <img src="{{ $p->image ? asset('storage/products/' . $p->image) : asset('storage/products/product-placeholder.png') }}"
                    alt="" class="w-10 h-10 rounded-lg object-cover bg-slate-100" onerror="this.style.visibility='hidden'">
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $p->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $p->product_code }}</p>
                </div>
                <div class="text-center w-24">
                    <p class="text-lg font-black {{ $p->stock_quantity <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $p->stock_quantity }}</p>
                    <p class="text-xs text-slate-500">em estoque</p>
                </div>
                <div class="text-center w-28">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $row['sold30'] }}</p>
                    <p class="text-xs text-slate-500">vendidos em 30 dias</p>
                </div>
                <div class="text-center w-28">
                    @if ($p->stock_quantity <= 0)
                        <span class="text-xs font-bold text-red-600">Esgotado</span>
                    @elseif ($row['daysLeft'] !== null)
                        <p class="text-sm font-bold {{ $row['daysLeft'] <= 7 ? 'text-red-600' : 'text-slate-700 dark:text-slate-200' }}">~{{ $row['daysLeft'] }} dias</p>
                        <p class="text-xs text-slate-500">até acabar</p>
                    @else
                        <span class="text-xs text-slate-400">sem vendas recentes</span>
                    @endif
                </div>
                <a href="{{ route('products.edit', $p->id) }}" class="ml-auto text-xs font-bold text-indigo-600 hover:underline">Editar estoque</a>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-emoji-smile text-3xl"></i>
                <p class="mt-2">Nenhum produto com estoque até {{ $minimum }} unidades.</p>
            </div>
        @endforelse
    </div>
</div>
