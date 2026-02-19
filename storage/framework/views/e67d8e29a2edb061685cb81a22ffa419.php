<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'subtitle' => null]));

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

foreach (array_filter((['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'subtitle' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $colorClasses = [
        'blue' => 'from-blue-500 to-indigo-600',
        'green' => 'from-green-500 to-emerald-600',
        'purple' => 'from-purple-500 to-pink-600',
        'orange' => 'from-orange-500 to-red-600',
        'cyan' => 'from-cyan-500 to-blue-600',
        'pink' => 'from-pink-500 to-rose-600',
    ];
    $gradient = $colorClasses[$color] ?? $colorClasses['blue'];
?>

<div class="group relative bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-slate-200 dark:border-slate-700">
    <!-- Gradient background animado -->
    <div class="absolute inset-0 bg-gradient-to-br <?php echo e($gradient); ?> opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

    <!-- Ícone decorativo de fundo -->
    <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br <?php echo e($gradient); ?> opacity-10 rounded-full blur-2xl"></div>

    <div class="relative z-10">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1"><?php echo e($title); ?></p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white flex items-baseline gap-2">
                    <?php echo e($value); ?>

                    <!--[if BLOCK]><![endif]--><?php if($trend): ?>
                        <span class="text-sm font-medium <?php echo e($trend > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <i class="bi bi-<?php echo e($trend > 0 ? 'arrow-up' : 'arrow-down'); ?>"></i>
                            <?php echo e(abs($trend)); ?>%
                        </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </h3>
                <!--[if BLOCK]><![endif]--><?php if($subtitle): ?>
                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1"><?php echo e($subtitle); ?></p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Ícone -->
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br <?php echo e($gradient); ?> flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                <i class="<?php echo e($icon); ?> text-2xl text-white"></i>
            </div>
        </div>

        <!-- Slot para conteúdo adicional -->
        <!--[if BLOCK]><![endif]--><?php if($slot->isNotEmpty()): ?>
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <?php echo e($slot); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/stat-card.blade.php ENDPATH**/ ?>