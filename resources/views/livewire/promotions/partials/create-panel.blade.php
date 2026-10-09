{{-- Painel dos produtos escolhidos (coluna da direita e janela do celular) --}}
@php $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); @endphp
<div class="flex flex-col h-full min-h-0">
    <div class="px-4 py-3 border-b border-slate-200/70 dark:border-slate-700/60 bg-gradient-to-r from-rose-500/10 via-pink-500/10 to-orange-500/10 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 flex items-center justify-center shadow-lg shadow-rose-500/30 shrink-0">
            <i class="bi bi-fire text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white leading-tight">Na promoção</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ count($rows) }} {{ count($rows) === 1 ? 'produto' : 'produtos' }}</p>
        </div>
        @if(count($rows))
            <button type="button" wire:click="clearItems" class="text-[11px] font-semibold text-rose-600 dark:text-rose-300 hover:underline">Limpar</button>
        @endif
        @if($mobile ?? false)
            <button type="button" @click="closeCart()" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
        @endif
    </div>

    @if(count($rows) === 0)
        <div class="flex-1 flex flex-col items-center justify-center text-center p-6">
            <div class="w-16 h-16 mb-3 rounded-2xl bg-gradient-to-br from-rose-500/15 to-orange-500/15 flex items-center justify-center">
                <i class="bi bi-hand-index-thumb text-2xl text-rose-500"></i>
            </div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Toque nos produtos</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-[14rem]">Eles aparecem aqui para você ajustar o desconto de cada um.</p>
        </div>
    @else
        {{-- Mesmo desconto para todos --}}
        <div class="px-4 pt-3 pb-2 border-b border-slate-100 dark:border-slate-700/60">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Desconto para todos</div>
            <div class="flex flex-wrap gap-1.5">
                @foreach([5, 10, 15, 20, 30] as $pct)
                    <button type="button" wire:click="applyToAll({{ $pct }})" class="promo-chip">{{ $pct }}%</button>
                @endforeach
                <button type="button" wire:click="applyToAll('max')" class="promo-chip promo-chip-max" title="O maior desconto que cada produto permite">Máximo</button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto ap-scroll p-3 space-y-2.5">
            @foreach($rows as $id => $row)
                @php $p = $row['product']; $err = $errors->first("items.$id"); @endphp
                <div wire:key="row-{{ $id }}-{{ ($mobile ?? false) ? 'm' : 'd' }}"
                     class="promo-row {{ $err || $row['belowMin'] ? 'promo-row-error' : '' }}">
                    <div class="flex items-start gap-2.5">
                        <img src="{{ $p->image ? asset('storage/products/' . $p->image) : asset('storage/products/product-placeholder.png') }}"
                             alt="" class="w-11 h-11 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 dark:text-white leading-tight line-clamp-2">{{ ucwords($p->name) }}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                #{{ $p->product_code }}
                                @if($p->variation_value)<span class="ml-1 px-1.5 py-px rounded bg-violet-500/15 text-violet-700 dark:text-violet-300 font-semibold">{{ $p->variation_value }}</span>@endif
                                · {{ (int) $p->stock_quantity }} un.
                            </p>
                        </div>
                        <button type="button" wire:click="removeItem({{ $id }})" class="shrink-0 w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-500/10" title="Tirar"><i class="bi bi-x-lg text-xs"></i></button>
                    </div>

                    {{-- De / Por --}}
                    <div class="grid grid-cols-2 gap-2 mt-2.5">
                        <label class="promo-field">
                            <span>De</span>
                            <input type="text" inputmode="decimal" wire:model.blur="items.{{ $id }}.original" class="line-through text-slate-500">
                        </label>
                        <label class="promo-field promo-field-main">
                            <span>Por</span>
                            <input type="text" inputmode="decimal" wire:model.blur="items.{{ $id }}.promo" class="text-rose-600 dark:text-rose-400 font-bold">
                        </label>
                    </div>

                    {{-- Desconto: − barra + --}}
                    @if($row['noRoom'])
                        <p class="mt-2 text-[11px] text-amber-700 dark:text-amber-300"><i class="bi bi-exclamation-triangle"></i> Sem margem: o mínimo ({{ $money($row['min']) }}) já é maior que o "de". Aumente o "de" ou tire este produto.</p>
                    @else
                        <div class="mt-2.5 flex items-center gap-2"
                             wire:key="slider-{{ $id }}-{{ $row['discount'] }}-{{ $row['max'] }}-{{ ($mobile ?? false) ? 'm' : 'd' }}"
                             x-data="{ p: {{ $row['discount'] }} }">
                            <button type="button" wire:click="nudge({{ $id }}, -1)" wire:loading.attr="disabled" class="promo-step" title="Menos desconto"><i class="bi bi-dash-lg"></i></button>
                            <div class="flex-1 min-w-0">
                                <input type="range" min="1" max="{{ max(1, $row['max']) }}" step="1" x-model.number="p"
                                       @change="$wire.setPercent({{ $id }}, p)" class="promo-range w-full"
                                       :style="'--fill:' + (({{ max(1, $row['max']) }} > 1 ? (p - 1) / ({{ max(1, $row['max']) }} - 1) : 1) * 100) + '%'">
                                <div class="flex justify-between text-[9px] text-slate-400 -mt-0.5"><span>1%</span><span>máx. {{ $row['max'] }}%</span></div>
                            </div>
                            <button type="button" wire:click="nudge({{ $id }}, 1)" wire:loading.attr="disabled" class="promo-step" title="Mais desconto" @disabled($row['discount'] >= $row['max'])><i class="bi bi-plus-lg"></i></button>
                            <span class="promo-off" x-text="p + '%'">{{ $row['discount'] }}%</span>
                        </div>
                    @endif

                    {{-- Números do produto --}}
                    <div class="mt-2 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[10.5px] text-slate-500 dark:text-slate-400">
                        <span title="Quanto o produto custou (a pagar)">Custo <b class="text-slate-700 dark:text-slate-200">{{ $money($row['cost']) }}</b></span>
                        <span title="Quanto sobra para você na promoção" class="{{ $row['profit'] < 0 ? 'text-red-600' : 'text-emerald-600 dark:text-emerald-400' }}">Lucro <b>{{ $money($row['profit']) }}</b></span>
                        <button type="button" wire:click="useMax({{ $id }})" title="Menor preço permitido. Toque para usar." class="text-amber-700 dark:text-amber-300 hover:underline">Mín. <b>{{ $money($row['min']) }}</b></button>
                    </div>
                    @if($err)
                        <p class="mt-1.5 text-[11px] font-semibold text-red-600">{{ $err }}</p>
                    @elseif($row['belowMin'])
                        <p class="mt-1.5 text-[11px] font-semibold text-red-600">Abaixo do mínimo de {{ $money($row['min']) }}.</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Validade e salvar --}}
        <div class="px-4 py-3 border-t border-slate-200/70 dark:border-slate-700/60 bg-slate-50/70 dark:bg-slate-800/50 space-y-2.5">
            @include('livewire.promotions.partials.schedule-fields', ['schedKey' => ($mobile ?? false) ? 'm' : 'd'])
            @error('dates') <p class="text-[11px] font-semibold text-red-600">{{ $message }}</p> @enderror
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 hover:from-rose-600 hover:via-pink-600 hover:to-orange-600 shadow-lg shadow-rose-500/30 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">
                    @if($startMode === 'date' && $startDate)
                        <i class="bi bi-alarm"></i> Agendar {{ count($rows) }} {{ count($rows) === 1 ? 'promoção' : 'promoções' }}
                    @else
                        <i class="bi bi-fire"></i> Pôr {{ count($rows) }} em promoção
                    @endif
                </span>
                <span wire:loading wire:target="save"><i class="bi bi-hourglass-split"></i> Salvando…</span>
            </button>
        </div>
    @endif
</div>
