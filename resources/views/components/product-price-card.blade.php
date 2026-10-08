@props(['item', 'index'])

@php
    $stockQuantity = $item['product']['stock_quantity'] ?? 0;
    $currentQuantity = $item['quantity'] ?? 1;
    $maxAvailable = $stockQuantity + $currentQuantity;
    $stockStatus = $stockQuantity > 20 ? 'high' : ($stockQuantity > 5 ? 'medium' : 'low');
@endphp

@php
    $difference = $item['price_sale'] - $item['original_price'];
    $percentChange = $item['original_price'] > 0 ? (($difference / $item['original_price']) * 100) : 0;
    $thumbProduct = isset($item['product']) ? (object) ['image' => $item['product']['image'] ?? null, 'name' => $item['product_name']] : null;
@endphp

<div class="flex flex-col rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm hover:shadow-md transition">
    <div class="flex items-center gap-3">
        <x-product-thumb :product="$thumbProduct" size="h-14 w-14" class="border border-slate-200 dark:border-slate-700" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate font-bold text-slate-900 dark:text-white">{{ $item['product_name'] }}</h3>
            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                <span class="rounded-full px-2 py-0.5 {{ $stockStatus === 'high' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($stockStatus === 'medium' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300') }}">
                    <i class="bi bi-boxes mr-0.5"></i>{{ $stockQuantity }} em estoque
                </span>
                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-slate-600 dark:text-slate-300" title="Preço de tabela">
                    <i class="bi bi-tag mr-0.5"></i>R$ {{ number_format($item['original_price'], 2, ',', '.') }}
                </span>
            </div>
        </div>
        <button type="button" onclick="openModal('confirm-remove-{{ $index }}')" title="Remover produto"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 transition">
            <i class="bi bi-trash"></i>
        </button>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Quantidade</label>
            <div class="flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <button type="button"
                        onclick="let input = this.nextElementSibling; if(input.value > 1) { input.value = parseInt(input.value) - 1; input.dispatchEvent(new Event('change')); }"
                        class="px-2.5 py-2 text-slate-600 dark:text-slate-300 hover:text-indigo-600" title="Menos um">
                    <i class="bi bi-dash-lg"></i>
                </button>
                <input type="number"
                       wire:model.lazy="saleItems.{{ $index }}.quantity"
                       wire:change="updateQuantity({{ $index }}, $event.target.value)"
                       class="w-full min-w-0 flex-1 border-0 bg-transparent px-1 py-2 text-center font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-0"
                       min="1" max="{{ $maxAvailable }}" step="1">
                <button type="button"
                        onclick="let input = this.previousElementSibling; if(parseInt(input.value) < {{ $maxAvailable }}) { input.value = parseInt(input.value) + 1; input.dispatchEvent(new Event('change')); }"
                        class="px-2.5 py-2 text-slate-600 dark:text-slate-300 hover:text-indigo-600" title="Mais um">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <p class="mt-1 text-center text-[11px] text-slate-500 dark:text-slate-400">até {{ $maxAvailable }}</p>
            @error("saleItems.{$index}.quantity")
                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Preço unitário</label>
            {{-- Máscara de centavos (mesma do carrinho): cada dígito entra
                 pela direita — 1 → 0,01 · 12 → 0,12 · 123 → 1,23.
                 Em Alpine, e não nas funções globais do <script> da view:
                 script dentro de componente Livewire não roda após o morph. --}}
            <div class="relative"
                 x-data="{
                     cts: {{ (int) round(($item['price_sale'] ?? 0) * 100) }},
                     fmt() {
                         let s = String(this.cts).padStart(3, '0');
                         let d = s.slice(-2);
                         let i = s.slice(0, -2).replace(/^0+/, '') || '0';
                         i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                         return i + ',' + d;
                     },
                     inp(e) {
                         let digs = e.target.value.replace(/\D/g, '');
                         this.cts = digs ? parseInt(digs) : 0;
                         e.target.value = this.fmt();
                     }
                 }">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500 dark:text-slate-400">R$</span>
                <input type="text" inputmode="numeric" id="price_input_{{ $index }}"
                       x-init="$el.value = fmt()"
                       @focus="$el.select()"
                       @input="inp($event)"
                       @blur="$wire.call('updatePrice', {{ $index }}, (cts / 100).toFixed(2))"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 pl-9 pr-3 text-right font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400"
                       placeholder="0,00">
            </div>
            @error("saleItems.{$index}.price_sale")
                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/60 px-3 py-2.5">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Subtotal</p>
            <p class="whitespace-nowrap text-lg font-black text-slate-900 dark:text-white">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</p>
        </div>
        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $difference > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($difference < 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}"
              title="Diferença para o preço de tabela">
            <i class="bi {{ $difference > 0 ? 'bi-arrow-up' : ($difference < 0 ? 'bi-arrow-down' : 'bi-dash') }}"></i>
            {{ $difference > 0 ? '+' : '' }}R$ {{ number_format($difference, 2, ',', '.') }} ({{ $difference > 0 ? '+' : '' }}{{ number_format($percentChange, 1, ',', '.') }}%)
        </span>
    </div>
</div>

<!-- Modal de Confirmação para Remoção -->
<x-confirmation-modal
    id="confirm-remove-{{ $index }}"
    title="Remover Produto"
    message="Tem certeza que deseja remover '{{ $item['product_name'] }}' desta venda? Esta ação não pode ser desfeita."
    confirm-text="Sim, Remover"
    cancel-text="Cancelar"
    confirm-action="$wire.removeSaleItem({{ $index }})"
    confirm-class="bg-red-600 hover:bg-red-700" />
