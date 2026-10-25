{{-- Janela de escolher produtos: $pickFor, $pickSearch, $results, $picked --}}
{{-- ===================== Janela: escolher produtos ===================== --}}
@if($pickFor !== '')
    <div class="fixed inset-0 z-[95] flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="closePicker">
        <div class="absolute inset-0" wire:click="closePicker"></div>
        <div class="relative w-full sm:max-w-2xl h-[88vh] sm:h-auto sm:max-h-[86vh] flex flex-col rounded-t-3xl sm:rounded-3xl bg-white dark:bg-slate-900 shadow-2xl" data-testid="product-picker">
            <div class="p-4 sm:p-5 pb-3 space-y-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $pickFor === 'banner' ? 'Escolher o produto' : 'Escolher produtos' }}</h2>
                    <button type="button" wire:click="closePicker" class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" wire:model.live.debounce.300ms="pickSearch" placeholder="Buscar por nome ou código" class="{{ $input }} pl-9" autofocus data-testid="picker-search">
                </div>
                @isset($pickCats)
                    {{-- Filtros rápidos: o que mais vende, promoções, novos, com estoque e por categoria --}}
                    <div class="flex gap-1.5 overflow-x-auto -mx-1 px-1 [scrollbar-width:none]">
                        @foreach(['todos' => ['Todos', 'bi-grid'], 'vendidos' => ['Mais vendidos', 'bi-fire'], 'ofertas' => ['Em promoção', 'bi-percent'], 'novos' => ['Novidades', 'bi-stars'], 'estoque' => ['Com estoque', 'bi-box-seam']] as $fk => [$fname, $fico])
                            <button type="button" wire:click="$set('pickFilter', '{{ $fk }}')" data-testid="picker-filter-{{ $fk }}"
                                    class="shrink-0 inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-semibold border transition {{ $pickFilter === $fk ? 'bg-violet-500 border-violet-500 text-white' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-violet-300' }}"><i class="bi {{ $fico }}"></i>{{ $fname }}</button>
                        @endforeach
                    </div>
                    @if($pickCats->count() > 1)
                        <select wire:model.live="pickCat" class="{{ $input }} !py-2" aria-label="Categoria">
                            <option value="0">Todas as categorias</option>
                            @foreach($pickCats as $pc)<option value="{{ $pc->id_category }}">{{ $pc->name }}</option>@endforeach
                        </select>
                    @endif
                @endisset
            </div>
            <ul class="flex-1 overflow-y-auto overscroll-contain p-2 sm:p-3 grid gap-1">
                @forelse($results as $p)
                    @php $on = in_array($p->id, $picked, true); @endphp
                    <li wire:key="pk-{{ $p->id }}">
                        <button type="button" wire:click="pick({{ $p->id }})" class="w-full flex items-center gap-3 rounded-xl p-2 text-left transition {{ $on ? 'bg-violet-50 dark:bg-violet-500/15 ring-1 ring-violet-300 dark:ring-violet-500/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800' }}" data-testid="picker-item">
                            <img src="{{ $p->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100 shrink-0" loading="lazy">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-2">{{ $p->name }}</span>
                                @php
                                    $pp = $p->relationLoaded('activePromotion') ? $p->livePromotion() : null;
                                    $out = ! $p->made_to_order && (int) $p->stock_quantity <= 0;
                                @endphp
                                <span class="flex flex-wrap items-center gap-x-1.5 text-xs text-slate-500">
                                    <span>{{ $p->product_code }}</span>·
                                    @if($pp)<s class="text-slate-400">{{ $money($p->price_sale) }}</s><b class="text-emerald-600">{{ $money($pp->promo_price) }}</b>@else<span>{{ $money($p->price_sale) }}</span>@endif
                                    @if($out)<span class="font-semibold text-amber-600">· sem estoque</span>@elseif($p->made_to_order)<span class="text-sky-600">· sob encomenda</span>@else<span>· {{ (int) $p->stock_quantity }} em estoque</span>@endif
                                </span>
                            </span>
                            <span class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center {{ $on ? 'bg-violet-500 text-white' : 'border border-slate-300 dark:border-slate-600 text-transparent' }}"><i class="bi bi-check-lg"></i></span>
                        </button>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">Nenhum produto encontrado.</li>
                @endforelse
            </ul>
            <div class="p-3 sm:p-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                @if($pickFor !== 'banner')<span class="text-sm text-slate-500">{{ count($picked) }} {{ count($picked) == 1 ? 'escolhido' : 'escolhidos' }}</span>@endif
                <button type="button" wire:click="closePicker" class="ml-auto {{ $primary }}" data-testid="picker-done">Pronto</button>
            </div>
        </div>
    </div>
@endif
