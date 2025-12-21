@props(['sale'])

@php
    // Configurações de status (normaliza variações comuns)
    $statusConfig = [
        'finalizada' => ['color' => '#10b981', 'icon' => 'bi-check-circle-fill', 'label' => 'Finalizada'],
        'concluida'  => ['color' => '#10b981', 'icon' => 'bi-check-circle-fill', 'label' => 'Finalizada'],
        'pago'       => ['color' => '#22c55e', 'icon' => 'bi-check-circle-fill', 'label' => 'Pago'],
        'paga'       => ['color' => '#22c55e', 'icon' => 'bi-check-circle-fill', 'label' => 'Pago'],
        'pendente'   => ['color' => '#facc15', 'icon' => 'bi-clock-fill', 'label' => 'Pendente'],
        'orcamento'  => ['color' => '#60a5fa', 'icon' => 'bi-file-earmark-text', 'label' => 'Orçamento'],
        'confirmada' => ['color' => '#06b6d4', 'icon' => 'bi-check2-square', 'label' => 'Confirmada'],
        'cancelada'  => ['color' => '#f87171', 'icon' => 'bi-x-circle-fill', 'label' => 'Cancelada'],
    ];

    // Calcular total pago corretamente: preferir o accessor do model quando disponível
    $totalPaid = $sale->total_paid ?? $sale->payments->sum('amount_paid');
    // Garantir que seja float
    $totalPaid = (float) $totalPaid;

    $remainingAmount = max(0, (float) $sale->total_price - $totalPaid);
    $paymentPercentage = $sale->total_price > 0 ? min(100, ($totalPaid / (float) $sale->total_price) * 100) : 0;

    // Determinar status exibido: se tudo estiver pago, forçar 'pago'
    $rawStatus = strtolower((string) ($sale->status ?? 'pendente'));
    $displayStatusKey = $remainingAmount <= 0 ? 'pago' : $rawStatus;
    $status = $statusConfig[$displayStatusKey] ?? ['color' => '#a78bfa', 'icon' => 'bi-question-circle-fill', 'label' => ucfirst($rawStatus)];
    $clientName = $sale->client->name ?? 'Cliente não informado';
    $clientCity = optional($sale->client)->city;
    $clientInitials = collect(explode(' ', $clientName))
        ->filter()
        ->map(fn($n) => mb_substr($n, 0, 1))
        ->take(2)
        ->implode('');
    $paymentMethodLabel = $sale->payment_method
        ? ucwords(str_replace('_', ' ', $sale->payment_method))
        : null;
    $paymentTypeLabel = $sale->tipo_pagamento === 'parcelado'
        ? ($sale->parcelas ? $sale->parcelas . 'x Parcelado' : 'Parcelado')
        : 'À vista';
        $productsCount = $sale->saleItems->count();
        $itemsQuantity = $sale->saleItems->sum('quantity');
        $lastUpdateLabel = $sale->updated_at?->diffForHumans() ?? 'Agora mesmo';
@endphp

<div class="sale-card shadow-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-gray-950 dark:to-black border border-slate-200 dark:border-gray-900" style="--sale-card-accent: {{ $status['color'] }};">
    <div class="sale-card-header">
        <div class="flex items-center justify-between w-full">
            <!-- Status à esquerda -->
            <div class="sale-card-status" style="--sale-status-color: {{ $status['color'] }}">
                <i class="bi {{ $status['icon'] }}"></i>
                <span>{{ $status['label'] }}</span>
            </div>

            <!-- Info à direita -->
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <span class="sale-card-chip">
                    <i class="bi bi-hash"></i>
                    Pedido #{{ $sale->id }}
                </span>
                <span class="sale-card-chip">
                    <i class="bi bi-box-seam"></i>
                    {{ $productsCount }} {{ \Illuminate\Support\Str::plural('item', $productsCount) }}
                </span>
                <span class="sale-card-chip">
                    <i class="bi bi-calendar3"></i>
                    {{ $sale->created_at->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    <div class="sale-card-body">
        <div class="sale-card-client">
            <div class="sale-card-avatar">
                @if($sale->client && $sale->client->caminho_foto)
                    <img src="{{ $sale->client->caminho_foto }}"
                         alt="Avatar de {{ $clientName }}"
                         class="w-full h-full rounded-full object-cover">
                @else
                    <span>{{ strtoupper($clientInitials ?: 'CL') }}</span>
                @endif
            </div>
            <div class="sale-card-client-info">
                <h3 class="text-slate-900 dark:text-white" title="{{ $clientName }}">{{ $clientName }}</h3>
                <span class="text-slate-600 dark:text-slate-400">
                    <i class="bi bi-geo-alt"></i>
                    {{ $clientCity ?? 'Cidade não informada' }}
                </span>
            </div>

            <div class="sale-card-payment-pills">
                @if($paymentMethodLabel)
                    <span class="sale-card-tag bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-300 dark:border-blue-700">
                        <i class="bi bi-credit-card"></i>
                        {{ $paymentMethodLabel }}
                    </span>
                @endif

                @if($paymentTypeLabel)
                    @if($sale->tipo_pagamento === 'parcelado')
                        <span class="sale-card-tag bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-300 dark:border-purple-700">
                            <i class="bi bi-credit-card-2-front"></i>
                            {{ $paymentTypeLabel }}
                        </span>
                    @else
                        <span class="sale-card-tag bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700">
                            <i class="bi bi-cash-stack"></i>
                            {{ $paymentTypeLabel }}
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <div class="sale-card-products-wrapper">
            <x-sale-card-products :items="$sale->saleItems" :max="3" />
        </div>

        <div class="sale-card-financial !flex !flex-row !flex-nowrap gap-2" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important;">
            <div class="sale-card-financial-block flex-1 min-w-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Pago</span>
                <strong class="text-lg font-black text-slate-900 dark:text-white block truncate">R$ {{ number_format($totalPaid, 2, ',', '.') }}</strong>
            </div>

            @if($remainingAmount > 0)
            <div class="sale-card-financial-block flex-1 min-w-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Pendente</span>
                <strong class="text-lg font-black text-slate-900 dark:text-white block truncate">
                    R$ {{ number_format($remainingAmount, 2, ',', '.') }}
                </strong>
            </div>
            @endif

            <div class="sale-card-total-block flex-1 min-w-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 border border-blue-200 dark:border-blue-900 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Total</span>
                <strong class="text-lg font-black bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent block truncate">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</strong>
            </div>
        </div>

        <div class="sale-card-progress">
            <div class="sale-card-progress-info">
                <span class="text-slate-600 dark:text-slate-400">Status financeiro</span>
                <strong class="text-slate-900 dark:text-white">{{ number_format($paymentPercentage, 0) }}%</strong>
            </div>
            <div class="sale-card-overview-progress bg-slate-200 dark:bg-slate-700">
                <div class="sale-card-overview-progress-bar bg-gradient-to-r from-emerald-500 to-green-500" style="width: {{ $paymentPercentage }}%"></div>
            </div>
        </div>
    </div>

    <div class="sale-card-actions">
        <a href="{{ route('sales.show', $sale->id) }}" class="sale-card-button sale-card-button-view">
            <i class="bi bi-eye"></i>
            Ver
        </a>
        <a href="{{ route('sales.edit', $sale->id) }}" class="sale-card-button sale-card-button-edit">
            <i class="bi bi-pencil"></i>
            Editar
        </a>
        <button type="button" wire:click="openExportSaleModalFromCard({{ $sale->id }})" class="sale-card-button sale-card-button-view bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white border-red-500 dark:border-red-600" title="Exportar">
            <i class="bi bi-file-earmark-pdf"></i>
            PDF
        </button>

        <div x-data="{ open: false }" class="sale-card-menu">
            <button @click="open = !open" @click.away="open = false" class="sale-card-button sale-card-button-more">
                <i class="bi bi-three-dots-vertical"></i>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 class="sale-card-dropdown bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl"
                 style="display: none;">
                <a href="{{ route('sales.add-products', $sale->id) }}" class="text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                    <i class="bi bi-plus-circle text-blue-600 dark:text-blue-400"></i>
                    Adicionar Produtos
                </a>
                <a href="{{ route('sales.edit-prices', $sale->id) }}" class="text-slate-700 dark:text-slate-300 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                    <i class="bi bi-currency-dollar text-purple-600 dark:text-purple-400"></i>
                    Editar Preços
                </a>
                <a href="{{ route('sales.add-payments', $sale->id) }}" class="text-slate-700 dark:text-slate-300 hover:bg-green-50 dark:hover:bg-green-900/20">
                    <i class="bi bi-credit-card text-green-600 dark:text-green-400"></i>
                    Adicionar Pagamento
                </a>
                <a href="{{ route('sales.edit-payments', $sale->id) }}" class="text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                    <i class="bi bi-pencil-square text-indigo-600 dark:text-indigo-400"></i>
                    Editar Pagamentos
                </a>
                <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                <button type="button" wire:click="confirmDelete({{ $sale->id }})" class="sale-card-dropdown-danger text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                    <i class="bi bi-trash"></i>
                    Excluir Venda
                </button>
            </div>
        </div>
    </div>
</div>
