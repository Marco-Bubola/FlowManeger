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
        $actionItems = [
            [
                'type' => 'link',
                'href' => route('sales.show', $sale->id),
                'label' => 'Detalhes',
                'tooltip' => 'Ver detalhes completos da venda',
                'icon' => 'bi-eye',
                'tone' => 'sky',
            ],
            [
                'type' => 'link',
                'href' => route('sales.edit', $sale->id),
                'label' => 'Editar venda',
                'tooltip' => 'Editar dados gerais da venda',
                'icon' => 'bi-pencil',
                'tone' => 'amber',
            ],
            [
                'type' => 'button',
                'wireClick' => 'openExportSaleModalFromCard(' . $sale->id . ')',
                'label' => 'Exportar PDF',
                'tooltip' => 'Gerar PDF desta venda',
                'icon' => 'bi-file-earmark-pdf',
                'tone' => 'rose',
            ],
            [
                'type' => 'link',
                'href' => route('sales.add-products', $sale->id),
                'label' => 'Add produtos',
                'tooltip' => 'Adicionar produtos a esta venda',
                'icon' => 'bi-plus-circle',
                'tone' => 'teal',
            ],
            [
                'type' => 'link',
                'href' => route('sales.edit-prices', $sale->id),
                'label' => 'Editar preços',
                'tooltip' => 'Editar preços dos itens da venda',
                'icon' => 'bi-currency-dollar',
                'tone' => 'violet',
            ],
            [
                'type' => 'link',
                'href' => route('sales.add-payments', $sale->id),
                'label' => 'Add pagamento',
                'tooltip' => 'Adicionar pagamento à venda',
                'icon' => 'bi-credit-card',
                'tone' => 'green',
            ],
            [
                'type' => 'link',
                'href' => route('sales.edit-payments', $sale->id),
                'label' => 'Editar pagamento',
                'tooltip' => 'Editar pagamentos já lançados',
                'icon' => 'bi-pencil-square',
                'tone' => 'indigo',
            ],
            [
                'type' => 'button',
                'wireClick' => 'payFull(' . $sale->id . ')',
                'label' => 'Quitar saldo',
                'tooltip' => 'Quitar o saldo restante da venda',
                'icon' => 'bi-cash-stack',
                'tone' => 'emerald',
            ],
            [
                'type' => 'button',
                'wireClick' => 'confirmDelete(' . $sale->id . ')',
                'label' => 'Excluir venda',
                'tooltip' => 'Excluir venda e devolver produtos ao estoque',
                'icon' => 'bi-trash',
                'tone' => 'red',
            ],
        ];
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

    <div class="sale-card-actions" aria-label="Ações da venda">
        @foreach ($actionItems as $action)
            <div class="sale-card-action-wrap">
                @if ($action['type'] === 'link')
                    <a href="{{ $action['href'] }}"
                        class="sale-card-action-chip sale-card-action-{{ $action['tone'] }}"
                        data-tooltip="{{ $action['tooltip'] }}"
                        title="{{ $action['tooltip'] }}"
                        aria-label="{{ $action['tooltip'] }}">
                        <span class="sale-card-action-icon">
                            <i class="bi {{ $action['icon'] }}"></i>
                        </span>
                        <span class="sale-card-action-text">{{ $action['label'] }}</span>
                    </a>
                @else
                    <button type="button"
                        wire:click="{{ $action['wireClick'] }}"
                        class="sale-card-action-chip sale-card-action-{{ $action['tone'] }}"
                        data-tooltip="{{ $action['tooltip'] }}"
                        title="{{ $action['tooltip'] }}"
                        aria-label="{{ $action['tooltip'] }}">
                        <span class="sale-card-action-icon">
                            <i class="bi {{ $action['icon'] }}"></i>
                        </span>
                        <span class="sale-card-action-text">{{ $action['label'] }}</span>
                    </button>
                @endif
            </div>
        @endforeach
    </div>
</div>
