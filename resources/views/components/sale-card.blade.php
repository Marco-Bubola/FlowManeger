@props(['sale'])

@php
    $statusConfig = [
        'finalizada' => ['color' => '#10b981', 'icon' => 'bi-check-circle-fill', 'label' => 'Finalizada'],
        'pago' => ['color' => '#22c55e', 'icon' => 'bi-check-circle-fill', 'label' => 'Pago'],
        'pendente' => ['color' => '#facc15', 'icon' => 'bi-clock-fill', 'label' => 'Pendente'],
        'cancelada' => ['color' => '#f87171', 'icon' => 'bi-x-circle-fill', 'label' => 'Cancelada'],
    ];
    $status = $statusConfig[$sale->status] ?? ['color' => '#a78bfa', 'icon' => 'bi-question-circle-fill', 'label' => ucfirst($sale->status)];
    $totalPaid = $sale->payments->sum('amount');
    $remainingAmount = max(0, $sale->total_price - $totalPaid);
    $paymentPercentage = $sale->total_price > 0 ? min(100, ($totalPaid / $sale->total_price) * 100) : 0;
    $clientName = $sale->client->name ?? 'Cliente não informado';
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
@endphp

<div class="sale-card shadow-lg" style="--sale-card-accent: {{ $status['color'] }};">
    <div class="sale-card-header">
        <div class="sale-card-status" style="--sale-status-color: {{ $status['color'] }}">
            <i class="bi {{ $status['icon'] }}"></i>
            <span>{{ $status['label'] }}</span>
        </div>
        <div class="sale-card-id">#{{ $sale->id }}</div>
    </div>

    <div class="sale-card-body">
        <div class="sale-card-client">
            <div class="sale-card-avatar">
                <span>{{ strtoupper($clientInitials ?: 'CL') }}</span>
            </div>
            <div class="sale-card-client-info">
                <h3 title="{{ $clientName }}">{{ $clientName }}</h3>
                <span>
                    <i class="bi bi-calendar3"></i>
                    {{ $sale->created_at->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        @if($paymentMethodLabel || $paymentTypeLabel)
        <div class="sale-card-tags">
            @if($paymentMethodLabel)
            <span class="sale-card-tag">
                <i class="bi bi-credit-card"></i>
                {{ $paymentMethodLabel }}
            </span>
            @endif
            @if($paymentTypeLabel)
            <span class="sale-card-tag">
                <i class="bi bi-wallet2"></i>
                {{ $paymentTypeLabel }}
            </span>
            @endif
        </div>
        @endif

        <div class="sale-card-products-wrapper">
            <x-sale-card-products :items="$sale->saleItems" :max="3" />
        </div>

        <div class="sale-card-overview">
            <div class="sale-card-overview-row">
                <span>Total</span>
                <strong>R$ {{ number_format($sale->total_price, 2, ',', '.') }}</strong>
            </div>
            <div class="sale-card-overview-row">
                <span>Pago</span>
                <strong class="text-emerald-500">R$ {{ number_format($totalPaid, 2, ',', '.') }}</strong>
            </div>
            <div class="sale-card-overview-row">
                <span>Pendente</span>
                <strong class="{{ $remainingAmount <= 0 ? 'text-emerald-500' : 'text-amber-500' }}">{{ $remainingAmount <= 0 ? 'Tudo pago' : 'R$ ' . number_format($remainingAmount, 2, ',', '.') }}</strong>
            </div>

            <div class="sale-card-overview-progress">
                <div class="sale-card-overview-progress-bar" style="width: {{ $paymentPercentage }}%"></div>
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

        <div x-data="{ open: false }" class="sale-card-menu">
            <button @click="open = !open" @click.away="open = false" class="sale-card-button sale-card-button-more">
                <i class="bi bi-three-dots"></i>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="sale-card-dropdown" style="display: none;">
                <a href="{{ route('sales.add-products', $sale->id) }}">
                    <i class="bi bi-plus-circle"></i>
                    Adicionar Produtos
                </a>
                <a href="{{ route('sales.edit-prices', $sale->id) }}">
                    <i class="bi bi-currency-dollar"></i>
                    Editar Preços
                </a>
                <a href="{{ route('sales.add-payments', $sale->id) }}">
                    <i class="bi bi-credit-card"></i>
                    Adicionar Pagamento
                </a>
                <a href="{{ route('sales.edit-payments', $sale->id) }}">
                    <i class="bi bi-pencil-square"></i>
                    Editar Pagamentos
                </a>
                <button type="button" wire:click="exportPdf({{ $sale->id }})">
                    <i class="bi bi-file-earmark-pdf"></i>
                    Exportar PDF
                </button>
                <button type="button" wire:click="confirmDelete({{ $sale->id }})" class="sale-card-dropdown-danger">
                    <i class="bi bi-trash"></i>
                    Excluir Venda
                </button>
            </div>
        </div>
    </div>
</div>
