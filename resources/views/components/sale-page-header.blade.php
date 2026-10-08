@props([
    'sale',
    'title' => null,
    'active' => null,
    'backRoute' => null,
])

@php
    // Cabeçalho único das telas de uma venda: mesmo visual do cabeçalho de cliente,
    // com as abas para ir de uma tela da venda para outra.
    $back = $backRoute ?? route('sales.index');
    $paid = (float) ($sale->total_paid ?? $sale->payments->sum('amount_paid'));
    $total = (float) $sale->total_price;
    $remaining = max(0, $total - $paid);
    $status = $remaining <= 0
        ? ['Pago', 'bi-check-circle-fill', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300']
        : ($paid > 0
            ? ['Parcial', 'bi-hourglass-split', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300']
            : ['Pendente', 'bi-clock-fill', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300']);
    $tabs = [
        'resumo' => ['label' => 'Resumo', 'icon' => 'bi-receipt', 'url' => route('sales.show', $sale->id)],
        'editar' => ['label' => 'Editar', 'icon' => 'bi-pencil-square', 'url' => route('sales.edit', $sale->id)],
        'produtos' => ['label' => 'Adicionar produtos', 'icon' => 'bi-bag-plus', 'url' => route('sales.add-products', $sale->id)],
        'precos' => ['label' => 'Preços', 'icon' => 'bi-currency-dollar', 'url' => route('sales.edit-prices', $sale->id)],
        'pagar' => ['label' => 'Adicionar pagamento', 'icon' => 'bi-credit-card', 'url' => route('sales.add-payments', $sale->id)],
        'pagamentos' => ['label' => 'Pagamentos', 'icon' => 'bi-wallet2', 'url' => route('sales.edit-payments', $sale->id)],
    ];
    $client = $sale->client;
@endphp

<div class="sale-page-header relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_32%)]"></div>
    <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ $back }}" title="Voltar"
                   class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>

                <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-12 h-12 sm:w-14 sm:h-14 text-lg" rounded="rounded-full" class="hidden sm:block ring-4 ring-white/70 dark:ring-slate-700/70" />

                <div class="min-w-0">
                    <nav class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('sales.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-cart mr-1"></i>Vendas</a>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="truncate text-indigo-600 dark:text-indigo-300">{{ $title ?? 'Venda #' . $sale->id }}</span>
                    </nav>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                            Venda #{{ $sale->id }}
                        </h1>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $status[2] }}"><i class="bi {{ $status[1] }} text-[9px]"></i>{{ $status[0] }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                        @if($client)
                            <a href="{{ route('clients.resumo', $client->id) }}" class="inline-flex items-center gap-1 font-semibold text-indigo-600 dark:text-indigo-300 hover:underline"><i class="bi bi-person"></i>{{ $client->name }}</a>
                        @else
                            <span class="inline-flex items-center gap-1"><i class="bi bi-person"></i>Cliente não informado</span>
                        @endif
                        <span class="inline-flex items-center gap-1"><i class="bi bi-calendar3"></i>{{ $sale->created_at?->format('d/m/Y') }}</span>
                        <span class="inline-flex items-center gap-1"><i class="bi bi-cash"></i>Total R$ {{ number_format($total, 2, ',', '.') }}</span>
                        @if($remaining > 0)
                            <span class="inline-flex items-center gap-1 font-semibold text-rose-600 dark:text-rose-400"><i class="bi bi-exclamation-circle"></i>Falta R$ {{ number_format($remaining, 2, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        <div class="mt-4 -mx-1 overflow-x-auto">
            <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
                @foreach($tabs as $key => $tab)
                    <a href="{{ $tab['url'] }}"
                       class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                              {{ $active === $key
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                        <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</div>
