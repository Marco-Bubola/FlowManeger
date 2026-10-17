<div class="edit-prices-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16">

    <x-sale-page-header :sale="$sale" title="Preços" active="precos" :back-route="route('sales.show', $sale->id)" />

    <!-- Conteúdo principal -->
    <div class="">
        <!-- Alertas de erro -->
        @if($errors->has('general'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 mr-3 text-lg"></i>
                    <span class="text-red-600 dark:text-red-400 font-medium">{{ $errors->first('general') }}</span>
                </div>
            </div>
        @endif

        <!-- Formulário de edição: itens à esquerda, resumo fixo à direita em telas largas -->
        <style>
            .ep-layout { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
            .ep-items { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 330px), 1fr)); }
            @media (min-width: 1024px) {
                .ep-layout { grid-template-columns: minmax(0, 1fr) 300px; }
                .ep-summary { position: sticky; top: 1rem; }
            }
            @media (min-width: 1800px) { .ep-layout { grid-template-columns: minmax(0, 1fr) 340px; } }
        </style>
        @php
            $epOriginal = (float) $sale->saleItems->sum(fn ($i) => $i->quantity * $i->price_sale);
            $epDiff = $this->total - $epOriginal;
            $epUnits = array_sum(array_column($saleItems, 'quantity'));
        @endphp
        <form wire:submit.prevent="savePrices" class="ep-layout">
            <div class="ep-items">
                @foreach($saleItems as $index => $item)
                    <x-product-price-card :item="$item" :index="$index" />
                @endforeach
            </div>

            <aside class="ep-summary rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Novo total da venda</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($this->total, 2, ',', '.') }}</p>

                <dl class="mt-3 space-y-1.5 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Total antes</dt>
                        <dd class="font-semibold text-slate-700 dark:text-slate-200">R$ {{ number_format($epOriginal, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Diferença</dt>
                        <dd class="font-semibold {{ abs($epDiff) < 0.005 ? 'text-slate-500 dark:text-slate-400' : ($epDiff > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400') }}">
                            {{ abs($epDiff) < 0.005 ? 'sem mudança' : ($epDiff > 0 ? '+' : '−') . ' R$ ' . number_format(abs($epDiff), 2, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-slate-500 dark:text-slate-400">Itens</dt>
                        <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ count($saleItems) }} {{ count($saleItems) === 1 ? 'produto' : 'produtos' }} · {{ $epUnits }} {{ $epUnits == 1 ? 'unidade' : 'unidades' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-col gap-2">
                    <button data-mobile-save type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="savePrices"><i class="bi bi-check2-circle mr-1"></i>Salvar preços</span>
                        <span wire:loading wire:target="savePrices"><i class="bi bi-arrow-repeat animate-spin mr-1"></i>Salvando...</span>
                    </button>
                    <a href="{{ route('sales.show', $sale->id) }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancelar</a>
                </div>
            </aside>
        </form>
    </div>

    <!-- Toast Notifications Component -->
    <x-toast-notifications />

</div>
