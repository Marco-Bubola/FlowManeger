@props(['sale'])

@php
    // Card da lista de vendas no mesmo visual do card de cliente.
    $totalPaid = (float) ($sale->total_paid ?? $sale->payments->sum('amount_paid'));
    $total = (float) $sale->total_price;
    $remaining = max(0, $total - $totalPaid);
    $percent = $total > 0 ? min(100, round($totalPaid / $total * 100)) : 0;
    $isPaid = $remaining <= 0;
    $rawStatus = strtolower((string) ($sale->status ?? 'pendente'));
    $status = $isPaid
        ? ['label' => 'Pago', 'icon' => 'bi-check-circle-fill', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300', 'band' => 'from-emerald-400 to-teal-500']
        : match ($rawStatus) {
            'cancelada' => ['label' => 'Cancelada', 'icon' => 'bi-x-circle-fill', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300', 'band' => 'from-rose-400 to-pink-500'],
            'orcamento' => ['label' => 'Orçamento', 'icon' => 'bi-file-earmark-text', 'class' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300', 'band' => 'from-sky-400 to-indigo-500'],
            default => $totalPaid > 0
                ? ['label' => 'Parcial', 'icon' => 'bi-hourglass-split', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300', 'band' => 'from-amber-400 to-orange-500']
                : ['label' => 'Pendente', 'icon' => 'bi-clock-fill', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300', 'band' => 'from-amber-400 to-orange-500'],
        };
    $client = $sale->client;
    $clientName = $client->name ?? 'Cliente não informado';
    $items = $sale->saleItems;
    $units = (int) $items->sum('quantity');
    $paymentLabel = $sale->tipo_pagamento === 'parcelado'
        ? ($sale->parcelas ? $sale->parcelas . 'x' : 'Parcelado')
        : 'À vista';
    $moreActions = [
        ['type' => 'link', 'href' => route('sales.edit', $sale->id), 'label' => 'Editar venda', 'icon' => 'bi-pencil'],
        ['type' => 'link', 'href' => route('sales.edit-prices', $sale->id), 'label' => 'Editar preços', 'icon' => 'bi-currency-dollar'],
        ['type' => 'link', 'href' => route('sales.edit-payments', $sale->id), 'label' => 'Editar pagamentos', 'icon' => 'bi-pencil-square'],
        ['type' => 'button', 'wire' => 'openExportSaleModalFromCard(' . $sale->id . ')', 'label' => 'Exportar PDF', 'icon' => 'bi-file-earmark-pdf'],
    ];
    if (! $isPaid) {
        array_unshift($moreActions, ['type' => 'button', 'wire' => 'payFull(' . $sale->id . ')', 'label' => 'Quitar saldo', 'icon' => 'bi-cash-stack']);
    }
@endphp

<div wire:key="sale-card-{{ $sale->id }}"
     class="sale-card-v2 group relative flex flex-col rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300"
     x-data="{ more: false }" @click.outside="more = false">
    <div class="h-1.5 w-full rounded-t-2xl bg-gradient-to-r {{ $status['band'] }}"></div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start gap-3">
            <x-client-avatar :name="$clientName" :photo="$client->caminho_foto ?? null" size="w-12 h-12 text-base" rounded="rounded-full" />
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="truncate text-base font-bold text-slate-900 dark:text-white" title="{{ $clientName }}">{{ $clientName }}</h3>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $status['class'] }}"><i class="bi {{ $status['icon'] }} text-[9px]"></i>{{ $status['label'] }}</span>
                </div>
                <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                    Venda #{{ $sale->id }} · {{ $sale->created_at?->format('d/m/Y') }} · {{ $paymentLabel }}
                </p>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <div class="flex -space-x-2">
                @foreach($items->take(4) as $item)
                    <x-product-thumb :product="$item->product" size="h-10 w-10" class="border-2 border-white dark:border-slate-900 shadow-sm" title="{{ $item->quantity }}x {{ $item->product->name ?? 'Produto' }}" />
                @endforeach
                @if($items->count() > 4)
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">+{{ $items->count() - 4 }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">
                    {{ ucwords($items->first()?->product?->name ?? 'Sem produtos') }}@if($items->count() > 1)<span class="font-normal text-slate-500"> e mais {{ $items->count() - 1 }}</span>@endif
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $units }} {{ $units === 1 ? 'unidade' : 'unidades' }}</p>
            </div>
        </div>

        <div class="mt-3 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/60 px-3 py-2.5">
            <div class="flex items-end justify-between gap-2">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format($total, 2, ',', '.') }}</p>
                </div>
                <div class="text-right text-xs">
                    <p class="text-emerald-600 dark:text-emerald-400">Pago <span class="font-bold">R$ {{ number_format($totalPaid, 2, ',', '.') }}</span></p>
                    @unless($isPaid)
                        <p class="text-rose-600 dark:text-rose-400">Falta <span class="font-bold">R$ {{ number_format($remaining, 2, ',', '.') }}</span></p>
                    @endunless
                </div>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div class="h-full rounded-full {{ $isPaid ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $percent }}%"></div>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">{{ $percent }}%</span>
            </div>
        </div>

        <div class="relative mt-auto pt-4 flex items-center gap-2">
            <a href="{{ route('sales.show', $sale->id) }}"
               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                <i class="bi bi-eye"></i><span class="whitespace-nowrap">Ver venda</span>
            </a>
            @unless($isPaid)
                <a href="{{ route('sales.add-payments', $sale->id) }}" title="Adicionar pagamento"
                   class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-500/20 transition">
                    <i class="bi bi-credit-card"></i>Pagar
                </a>
            @endunless
            <a href="{{ route('sales.add-products', $sale->id) }}" title="Adicionar produtos"
               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-teal-600 transition">
                <i class="bi bi-bag-plus"></i>
            </a>
            <button type="button" @click="more = !more" title="Mais ações" :aria-expanded="more.toString()"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <button type="button" wire:click="confirmDelete({{ $sale->id }})" title="Excluir venda (devolve os produtos ao estoque)"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 transition">
                <i class="bi bi-trash"></i>
            </button>

            <div x-show="more" x-cloak x-transition.origin.bottom.right
                 class="absolute bottom-12 right-0 z-30 w-52 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 py-1 shadow-xl">
                @foreach($moreActions as $action)
                    @if($action['type'] === 'link')
                        <a href="{{ $action['href'] }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i class="bi {{ $action['icon'] }} w-4 text-slate-400"></i>{{ $action['label'] }}
                        </a>
                    @else
                        <button type="button" wire:click="{{ $action['wire'] }}" @click="more = false" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i class="bi {{ $action['icon'] }} w-4 text-slate-400"></i>{{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
