<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['percentage' => 0, 'size' => 'md', 'color' => 'blue', 'label' => null, 'showValue' => true]));

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

foreach (array_filter((['percentage' => 0, 'size' => 'md', 'color' => 'blue', 'label' => null, 'showValue' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $sizes = [
        'sm' => 'w-16 h-16',
        'md' => 'w-24 h-24',
        'lg' => 'w-32 h-32',
        'xl' => 'w-40 h-40',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $colors = [
        'blue' => ['stroke' => '#3B82F6', 'trail' => '#DBEAFE'],
        'green' => ['stroke' => '#10B981', 'trail' => '#D1FAE5'],
        'purple' => ['stroke' => '#8B5CF6', 'trail' => '#EDE9FE'],
        'orange' => ['stroke' => '#F59E0B', 'trail' => '#FEF3C7'],
        'pink' => ['stroke' => '#EC4899', 'trail' => '#FCE7F3'],
    ];
    $colorScheme = $colors[$color] ?? $colors['blue'];

    $circumference = 2 * pi() * 40; // raio = 40
    $offset = $circumference - ($percentage / 100) * $circumference;
?>

<div class="flex flex-col items-center gap-2">
    <div class="relative <?php echo e($sizeClass); ?>">
        <svg class="transform -rotate-90" viewBox="0 0 100 100">
            <!-- Background circle -->
            <circle
                cx="50"
                cy="50"
                r="40"
                fill="none"
                stroke="<?php echo e($colorScheme['trail']); ?>"
                stroke-width="8"
                class="dark:opacity-20"
            />

            <!-- Progress circle -->
            <circle
                cx="50"
                cy="50"
                r="40"
                fill="none"
                stroke="<?php echo e($colorScheme['stroke']); ?>"
                stroke-width="8"
                stroke-linecap="round"
                stroke-dasharray="<?php echo e($circumference); ?>"
                stroke-dashoffset="<?php echo e($offset); ?>"
                class="transition-all duration-1000 ease-out"
                style="filter: drop-shadow(0 0 8px <?php echo e($colorScheme['stroke']); ?>80)"
            />
        </svg>

        <!-- Percentage text -->
        <!--[if BLOCK]><![endif]--><?php if($showValue): ?>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xl font-bold text-slate-900 dark:text-white"><?php echo e(round($percentage)); ?>%</span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!--[if BLOCK]><![endif]--><?php if($label): ?>
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 text-center"><?php echo e($label); ?></p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/progress-ring.blade.php ENDPATH**/ ?>