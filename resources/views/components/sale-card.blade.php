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
        $isPaid = $remainingAmount <= 0;
        $progressToneClass = $isPaid ? 'is-paid' : 'is-pending';
        $progressStatusTitle = $isPaid ? 'Pagamento concluído' : 'Pagamento pendente';
        $progressStatusText = $isPaid ? 'Venda 100% paga' : 'Falta quitar R$ ' . number_format($remainingAmount, 2, ',', '.');
@endphp

<div class="sale-card shadow-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-gray-950 dark:to-black border border-slate-200 dark:border-gray-900"
    x-data="{ showInfoModal: false }" style="--sale-card-accent: {{ $status['color'] }};">

    <div class="sale-card-body">
        <!-- Cliente + Botão Info -->
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
            <div class="sale-card-client-info" style="flex: 1; min-width: 0;">
                <div class="flex items-center gap-2">
                    <h3 class="text-slate-900 dark:text-white truncate" title="{{ $clientName }}">{{ $clientName }}</h3>
                    <button type="button" @click="showInfoModal = true"
                        class="sale-card-btn-info flex-shrink-0" title="Informações da venda">
                        <i class="bi bi-info-circle"></i>
                    </button>
                </div>
                <span class="text-slate-600 dark:text-slate-400">
                    <i class="bi bi-geo-alt"></i>
                    {{ $clientCity ?? 'Cidade não informada' }}
                </span>
            </div>
        </div>

        <div class="sale-card-products-wrapper">
            <x-sale-card-products :items="$sale->saleItems" :max="3" />
        </div>

        <!-- Blocos Financeiros Modernos -->
        <div class="sale-card-financial-grid">
            <div class="sale-card-fin-block sale-card-fin-paid">
                <div class="sale-card-fin-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="sale-card-fin-data">
                    <span>Pago</span>
                    <strong>R$ {{ number_format($totalPaid, 2, ',', '.') }}</strong>
                </div>
            </div>

            @if($remainingAmount > 0)
            <div class="sale-card-fin-block sale-card-fin-pending">
                <div class="sale-card-fin-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="sale-card-fin-data">
                    <span>Pendente</span>
                    <strong>R$ {{ number_format($remainingAmount, 2, ',', '.') }}</strong>
                </div>
            </div>
            @endif

            <div class="sale-card-fin-block sale-card-fin-total">
                <div class="sale-card-fin-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="sale-card-fin-data">
                    <span>Total</span>
                    <strong>R$ {{ number_format($sale->total_price, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Barra de Progresso Moderna -->
        <div class="sale-card-progress-modern">
            <div class="sale-card-progress-top">
                <div class="sale-card-progress-label {{ $progressToneClass }}">
                    <i class="bi {{ $isPaid ? 'bi-patch-check-fill' : 'bi-exclamation-circle' }}"></i>
                    <span>{{ $isPaid ? 'Pago' : 'Pendente' }}</span>
                </div>
                <span class="sale-card-progress-pct">{{ number_format($paymentPercentage, 0) }}%</span>
                @if(!$isPaid)
                <span class="sale-card-progress-hint">Falta quitar R$ {{ number_format($remainingAmount, 2, ',', '.') }}</span>
                @endif
            </div>
            <div class="sale-card-progress-track">
                <div class="sale-card-progress-fill {{ $progressToneClass }}" x-data="{ pp: {{ number_format($paymentPercentage, 2, '.', '') }} }" :style="`width: ${pp}%`"></div>
            </div>
        </div>
    </div>

    <template x-teleport="body">
    <div class="sale-card-info-modal" x-show="showInfoModal" x-cloak x-transition.opacity @click="showInfoModal = false">
        <div class="sale-card-info-modal-panel" @click.stop>
            <div class="sale-card-info-modal-header">
                <h4>
                    <i class="bi bi-stars"></i>
                    Informações da Venda
                </h4>
                <button type="button" @click="showInfoModal = false" aria-label="Fechar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="sale-card-info-modal-grid">
                <span class="sale-card-chip"><i class="bi bi-hash"></i> Pedido #{{ $sale->id }}</span>
                <span class="sale-card-chip"><i class="bi bi-box-seam"></i> {{ $productsCount }} {{ \Illuminate\Support\Str::plural('item', $productsCount) }}</span>
                <span class="sale-card-chip"><i class="bi bi-stack"></i> {{ $itemsQuantity }} unidades</span>
                <span class="sale-card-chip"><i class="bi bi-calendar3"></i> {{ $sale->created_at->format('d/m/Y') }}</span>
                @if($paymentMethodLabel)
                    <span class="sale-card-chip"><i class="bi bi-credit-card"></i> {{ $paymentMethodLabel }}</span>
                @endif
                <span class="sale-card-chip"><i class="bi {{ $sale->tipo_pagamento === 'parcelado' ? 'bi-credit-card-2-front' : 'bi-cash-stack' }}"></i> {{ $paymentTypeLabel }}</span>
                <span class="sale-card-chip"><i class="bi bi-arrow-repeat"></i> Atualizada {{ $lastUpdateLabel }}</span>
                <span class="sale-card-chip"><i class="bi {{ $status['icon'] }}"></i> {{ $status['label'] }}</span>
            </div>
        </div>
    </div>
    </template>

    <div class="sale-card-actions p-3 border-t border-slate-200 dark:border-slate-700 flex flex-wrap gap-2 bg-transparent">
        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.show', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Ver detalhes da venda" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-eye"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip" aria-hidden="true">Ver</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.edit', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Editar venda" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-pencil"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Editar</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="openExportSaleModalFromCard({{ $sale->id }})" @focus="open=true" @blur="open=false" aria-label="Exportar venda para PDF" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white hover:from-red-600 hover:to-pink-700 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pink-400">
                <i class="bi bi-file-earmark-pdf"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Exportar PDF</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.add-products', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Adicionar produtos" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-plus-circle text-blue-600"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Adicionar</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.edit-prices', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Editar preços" class="w-10 h-10 flex items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-900/10 text-purple-700 dark:text-purple-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <i class="bi bi-currency-dollar"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Preços</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.add-payments', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Adicionar pagamento" class="w-10 h-10 flex items-center justify-center rounded-xl bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <i class="bi bi-credit-card"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Pagamento</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="{{ route('sales.edit-payments', $sale->id) }}" @focus="open=true" @blur="open=false" aria-label="Editar pagamentos" class="w-10 h-10 flex items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/10 text-indigo-700 dark:text-indigo-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-pencil-square"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Editar Pag.</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="payFull({{ $sale->id }})" @focus="open=true" @blur="open=false" aria-label="Pagar tudo" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <i class="bi bi-cash-stack"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Pagar Tudo</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="confirmDelete({{ $sale->id }})" @focus="open=true" @blur="open=false" aria-label="Excluir venda" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-600 text-white hover:bg-red-700 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400">
                <i class="bi bi-trash"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Excluir</span>
        </div>
    </div>
</div>
