<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Produtos para repor" subtitle="Estoque no mínimo ou abaixo, ordenado pelo que mais vende"
        icon="bi-box-seam" active="repor" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-gestao-stat label="Para repor" :value="$rows->count() . ' ' . ($rows->count() === 1 ? 'produto' : 'produtos')" icon="bi-box-seam" tone="amber" />
        <x-gestao-stat label="Esgotados" :value="$zeroCount" icon="bi-x-octagon" tone="rose" />
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md"><i class="bi bi-sliders text-lg"></i></div>
            <label class="min-w-0 text-sm text-slate-600 dark:text-slate-300">
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mostrar com estoque até</span>
                <span class="mt-1 flex items-center gap-2">
                    <input type="number" min="0" max="9999" inputmode="numeric" wire:model.live.debounce.500ms="minimum"
                        class="w-20 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-1 text-center font-bold text-slate-900 dark:text-white">
                    unidades
                </span>
            </label>
        </div>
    </div>

    <div class="relative grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
        <div wire:loading.flex class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php
                $p = $row['product'];
                $out = $row['stock'] <= 0;
                $urgent = $out || ($row['daysLeft'] !== null && $row['daysLeft'] <= 7);
            @endphp
            <div class="flex flex-col rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-lg transition overflow-hidden" wire:key="restock-{{ $p->id }}">
                <div class="h-1.5 {{ $out ? 'bg-gradient-to-r from-rose-500 to-pink-500' : ($urgent ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-sky-400 to-indigo-500') }}"></div>
                <div class="flex flex-1 flex-col p-4">
                    <div class="flex items-center gap-3">
                        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-box-seam text-xl text-slate-400"></i>
                            @if($p->image)
                                <img src="{{ asset('storage/products/' . $p->image) }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-slate-900 dark:text-white">{{ $p->name }}@if($p->variation_value) <span class="font-medium text-slate-500 dark:text-slate-400">· {{ $p->variation_value }}</span>@endif</p>
                            <p class="truncate text-xs font-mono text-slate-500 dark:text-slate-400">
                                @if($row['isKit'])<span class="mr-1 rounded bg-purple-50 px-1.5 py-0.5 font-sans text-[10px] font-bold text-purple-700 dark:bg-purple-500/10 dark:text-purple-300">KIT</span>@endif{{ $p->product_code }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $out ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : ($urgent ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300') }}">
                            {{ $out ? 'Esgotado' : ($urgent ? 'Urgente' : 'Baixo') }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-700 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/60 py-2 text-center">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500" @if($row['isKit']) title="Quantos kits dá para montar com os componentes" @endif>{{ $row['isKit'] ? 'Dá p/ montar' : 'Estoque' }}</p>
                            <p class="text-base font-black {{ $out ? 'text-rose-600' : 'text-amber-600' }}">{{ $row['stock'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500" title="Quantidade de itens vendidos em 30 dias">Vendeu 30d</p>
                            <p class="text-base font-black text-slate-900 dark:text-white">{{ $row['sold30'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Acaba em</p>
                            <p class="text-sm font-bold leading-6 {{ $urgent ? 'text-rose-600' : 'text-slate-700 dark:text-slate-200' }}">
                                @if($out) já acabou @elseif($row['daysLeft'] !== null) ~{{ $row['daysLeft'] }} dias @else <span class="text-xs font-medium text-slate-400">sem vendas</span> @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-2 flex items-center justify-between gap-2 rounded-xl border border-slate-100 dark:border-slate-700/60 px-3 py-2 text-xs">
                        <div class="min-w-0 text-slate-600 dark:text-slate-300">
                            <span title="Média de vendas por dia nos últimos 30 dias">≈ {{ number_format($row['avgDaily'], $row['avgDaily'] > 0 && $row['avgDaily'] < 10 ? 1 : 0, ',', '.') }}/dia</span>
                            <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>
                            @if($row['isKit'])
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Repor componentes</span>
                            @else
                                <span title="Para cobrir 30 dias de vendas">Repor <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $row['suggested'] }} un.</span></span>
                            @endif
                        </div>
                        <label class="flex shrink-0 items-center gap-1 text-slate-500 dark:text-slate-400" title="Mínimo só deste produto (vazio = usa o mínimo geral de {{ $minimum }})">
                            Mín.
                            <input type="number" min="0" max="9999" inputmode="numeric" placeholder="{{ $minimum }}"
                                value="{{ $p->min_stock }}" wire:key="min-{{ $p->id }}"
                                wire:change="setMinStock({{ $p->id }}, $event.target.value)"
                                class="w-14 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-1.5 py-0.5 text-center font-bold text-slate-900 dark:text-white placeholder:font-normal placeholder:text-slate-400">
                        </label>
                    </div>

                    <a href="{{ route('products.edit', $p->id) }}"
                       class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                        <i class="bi bi-pencil"></i>Atualizar estoque
                    </a>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 2xl:col-span-3 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-emoji-smile text-3xl text-emerald-500"></i>
                <p class="mt-2">Nenhum produto com estoque até {{ $minimum }} unidades (ou abaixo do mínimo próprio).</p>
            </div>
        @endforelse
    </div>
</div>
