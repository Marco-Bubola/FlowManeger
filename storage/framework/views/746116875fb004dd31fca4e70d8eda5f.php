<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['sale']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['sale']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
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
?>

<div class="sale-card shadow-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-gray-950 dark:to-black border border-slate-200 dark:border-gray-900" style="--sale-card-accent: <?php echo e($status['color']); ?>;">
    <div class="sale-card-header">
        <div class="flex items-center justify-between w-full">
            <!-- Status à esquerda -->
            <div class="sale-card-status" style="--sale-status-color: <?php echo e($status['color']); ?>">
                <i class="bi <?php echo e($status['icon']); ?>"></i>
                <span><?php echo e($status['label']); ?></span>
            </div>

            <!-- Info à direita -->
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <span class="sale-card-chip">
                    <i class="bi bi-hash"></i>
                    Pedido #<?php echo e($sale->id); ?>

                </span>
                <span class="sale-card-chip">
                    <i class="bi bi-box-seam"></i>
                    <?php echo e($productsCount); ?> <?php echo e(\Illuminate\Support\Str::plural('item', $productsCount)); ?>

                </span>
                <span class="sale-card-chip">
                    <i class="bi bi-calendar3"></i>
                    <?php echo e($sale->created_at->format('d/m/Y')); ?>

                </span>
            </div>
        </div>
    </div>

    <div class="sale-card-body">
        <div class="sale-card-client">
            <div class="sale-card-avatar">
                <!--[if BLOCK]><![endif]--><?php if($sale->client && $sale->client->caminho_foto): ?>
                    <img src="<?php echo e($sale->client->caminho_foto); ?>"
                         alt="Avatar de <?php echo e($clientName); ?>"
                         class="w-full h-full rounded-full object-cover">
                <?php else: ?>
                    <span><?php echo e(strtoupper($clientInitials ?: 'CL')); ?></span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            <div class="sale-card-client-info">
                <h3 class="text-slate-900 dark:text-white" title="<?php echo e($clientName); ?>"><?php echo e($clientName); ?></h3>
                <span class="text-slate-600 dark:text-slate-400">
                    <i class="bi bi-geo-alt"></i>
                    <?php echo e($clientCity ?? 'Cidade não informada'); ?>

                </span>
            </div>

            <div class="sale-card-payment-pills">
                <!--[if BLOCK]><![endif]--><?php if($paymentMethodLabel): ?>
                    <span class="sale-card-tag bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-300 dark:border-blue-700">
                        <i class="bi bi-credit-card"></i>
                        <?php echo e($paymentMethodLabel); ?>

                    </span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($paymentTypeLabel): ?>
                    <!--[if BLOCK]><![endif]--><?php if($sale->tipo_pagamento === 'parcelado'): ?>
                        <span class="sale-card-tag bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-300 dark:border-purple-700">
                            <i class="bi bi-credit-card-2-front"></i>
                            <?php echo e($paymentTypeLabel); ?>

                        </span>
                    <?php else: ?>
                        <span class="sale-card-tag bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700">
                            <i class="bi bi-cash-stack"></i>
                            <?php echo e($paymentTypeLabel); ?>

                        </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <div class="sale-card-products-wrapper">
            <?php if (isset($component)) { $__componentOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sale-card-products','data' => ['items' => $sale->saleItems,'max' => 3]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sale-card-products'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sale->saleItems),'max' => 3]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a)): ?>
<?php $attributes = $__attributesOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a; ?>
<?php unset($__attributesOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a)): ?>
<?php $component = $__componentOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a; ?>
<?php unset($__componentOriginaldff6fb9c1ab030acf35e2c3fd7c3f20a); ?>
<?php endif; ?>
        </div>

        <div class="sale-card-financial !flex !flex-row !flex-nowrap gap-2" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important;">
            <div class="sale-card-financial-block flex-1 min-w-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Pago</span>
                <strong class="text-lg font-black text-slate-900 dark:text-white block truncate">R$ <?php echo e(number_format($totalPaid, 2, ',', '.')); ?></strong>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($remainingAmount > 0): ?>
            <div class="sale-card-financial-block flex-1 min-w-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Pendente</span>
                <strong class="text-lg font-black text-slate-900 dark:text-white block truncate">
                    R$ <?php echo e(number_format($remainingAmount, 2, ',', '.')); ?>

                </strong>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div class="sale-card-total-block flex-1 min-w-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 border border-blue-200 dark:border-blue-900 rounded-xl p-3">
                <span class="text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Total</span>
                <strong class="text-lg font-black bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent block truncate">R$ <?php echo e(number_format($sale->total_price, 2, ',', '.')); ?></strong>
            </div>
        </div>

        <div class="sale-card-progress">
            <div class="sale-card-progress-info">
                <span class="text-slate-600 dark:text-slate-400">Status financeiro</span>
                <strong class="text-slate-900 dark:text-white"><?php echo e(number_format($paymentPercentage, 0)); ?>%</strong>
            </div>
            <div class="sale-card-overview-progress bg-slate-200 dark:bg-slate-700">
                <div class="sale-card-overview-progress-bar bg-gradient-to-r from-emerald-500 to-green-500" style="width: <?php echo e($paymentPercentage); ?>%"></div>
            </div>
        </div>
    </div>

    <div class="sale-card-actions p-3 border-t border-slate-200 dark:border-slate-700 flex flex-wrap gap-2 bg-transparent">
        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.show', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Ver detalhes da venda" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-eye"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip" aria-hidden="true">Ver</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.edit', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Editar venda" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-pencil"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Editar</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="openExportSaleModalFromCard(<?php echo e($sale->id); ?>)" @focus="open=true" @blur="open=false" aria-label="Exportar venda para PDF" :aria-expanded="open ? 'true' : 'false'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white hover:from-red-600 hover:to-pink-700 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pink-400">
                <i class="bi bi-file-earmark-pdf"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Exportar PDF</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.add-products', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Adicionar produtos" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-plus-circle text-blue-600"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Adicionar</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.edit-prices', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Editar preços" class="w-10 h-10 flex items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-900/10 text-purple-700 dark:text-purple-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <i class="bi bi-currency-dollar"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Preços</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.add-payments', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Adicionar pagamento" class="w-10 h-10 flex items-center justify-center rounded-xl bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <i class="bi bi-credit-card"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Pagamento</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <a href="<?php echo e(route('sales.edit-payments', $sale->id)); ?>" @focus="open=true" @blur="open=false" aria-label="Editar pagamentos" class="w-10 h-10 flex items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/10 text-indigo-700 dark:text-indigo-300 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <i class="bi bi-pencil-square"></i>
            </a>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Editar Pag.</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="payFull(<?php echo e($sale->id); ?>)" @focus="open=true" @blur="open=false" aria-label="Pagar tudo" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <i class="bi bi-cash-stack"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Pagar Tudo</span>
        </div>

        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
            <button type="button" wire:click="confirmDelete(<?php echo e($sale->id); ?>)" @focus="open=true" @blur="open=false" aria-label="Excluir venda" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-600 text-white hover:bg-red-700 shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400">
                <i class="bi bi-trash"></i>
            </button>
            <span x-show="open" x-cloak x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 px-2 py-1 rounded-md text-xs font-medium bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-lg pointer-events-none" role="tooltip">Excluir</span>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/sale-card.blade.php ENDPATH**/ ?>