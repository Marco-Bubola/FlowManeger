<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['sale', 'remainingAmount']));

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

foreach (array_filter((['sale', 'remainingAmount']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 dark:from-indigo-900/20 dark:via-zinc-900/20 dark:to-purple-900/20 rounded-2xl p-6 shadow-lg border border-indigo-200 dark:border-indigo-800 mb-6 hover-lift">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Informações da Venda -->
        <div class="space-y-2">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-indigo-500/10 rounded-xl">
                    <i class="bi bi-receipt text-indigo-600 dark:text-indigo-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Venda #<?php echo e($sale->id); ?>

                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <?php echo e($sale->created_at->format('d/m/Y H:i')); ?>

                    </p>
                </div>
            </div>
        </div>

        <!-- Cliente -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-blue-500/10 rounded-xl">
                    <i class="bi bi-person-circle text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Cliente</h4>
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                <?php echo e($sale->client->name ?? 'Cliente não informado'); ?>

            </p>
            <!--[if BLOCK]><![endif]--><?php if($sale->client && $sale->client->phone): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    <i class="bi bi-telephone"></i>
                    <?php echo e($sale->client->phone); ?>

                </p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Total da Venda -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-green-500/10 rounded-xl">
                    <i class="bi bi-currency-dollar text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Total da Venda</h4>
            </div>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                R$ <?php echo e(number_format($sale->total_price, 2, ',', '.')); ?>

            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <?php echo e($sale->saleItems->count()); ?> <?php echo e($sale->saleItems->count() === 1 ? 'item' : 'itens'); ?>

            </p>
        </div>

        <!-- Valor Pendente -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-red-500/10 rounded-xl">
                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Valor Pendente</h4>
            </div>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                R$ <?php echo e(number_format($remainingAmount, 2, ',', '.')); ?>

            </p>
            <!--[if BLOCK]><![endif]--><?php if($sale->total_price > 0): ?>
                <?php $pendingPercentage = ($remainingAmount / $sale->total_price) * 100; ?>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="h-full bg-gradient-to-r from-red-500 to-orange-500 rounded-full transition-all duration-500"
                         style="width: <?php echo e($pendingPercentage); ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <?php echo e(number_format($pendingPercentage, 1)); ?>% pendente
                </p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!-- Resumo de Pagamentos Existentes (se houver) -->
    <!--[if BLOCK]><![endif]--><?php if($sale->payments && $sale->payments->count() > 0): ?>
        <div class="mt-6 pt-6 border-t border-indigo-200 dark:border-indigo-800">
            <div class="flex items-center gap-2 mb-3">
                <div class="p-2 bg-purple-500/10 rounded-xl">
                    <i class="bi bi-credit-card text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-300">
                    Pagamentos Realizados (<?php echo e($sale->payments->count()); ?>)
                </h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sale->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white/60 dark:bg-zinc-800/60 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $payment->payment_method))); ?>

                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php echo e(\Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y')); ?>

                                </p>
                            </div>
                            <p class="text-sm font-bold text-purple-600 dark:text-purple-400">
                                R$ <?php echo e(number_format($payment->amount_paid, 2, ',', '.')); ?>

                            </p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<style>
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/sale-summary-header.blade.php ENDPATH**/ ?>