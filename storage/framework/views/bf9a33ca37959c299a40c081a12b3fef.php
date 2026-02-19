<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Nova Venda',
    'description' => 'Registre uma nova venda no sistema seguindo os passos',
    'backRoute' => null,
    'currentStep' => 1,
    'steps' => []
]));

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

foreach (array_filter(([
    'title' => 'Nova Venda',
    'description' => 'Registre uma nova venda no sistema seguindo os passos',
    'backRoute' => null,
    'currentStep' => 1,
    'steps' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-lg ">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo (reduzido) -->
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!--[if BLOCK]><![endif]--><?php if($backRoute): ?>
                <!-- Botão voltar compacto -->
                <a href="<?php echo e($backRoute); ?>"
                    class="group relative inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-200 shadow-sm border border-white/40 dark:border-slate-600/40 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-lg text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-150"></i>
                    <div class="absolute inset-0 rounded-lg bg-blue-500/8 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!-- Ícone principal e título (compacto) -->
                <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-lg shadow-md shadow-purple-500/15">
                    <i class="bi bi-plus-circle text-white text-2xl"></i>
                    <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-white/20 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1">
                    <!--[if BLOCK]><![endif]--><?php if(isset($breadcrumb)): ?>
                        <?php echo e($breadcrumb); ?>

                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        <?php echo e($title); ?>

                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                        💼 <?php echo $description; ?>

                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                
                <?php echo $actions ?? ''; ?>


                <!-- Steppers compactos -->
                <!--[if BLOCK]><![endif]--><?php if(count($steps) > 0): ?>
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $stepNumber = $index + 1; ?>

                            <!-- Step -->
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200"
                                    :class="$wire.currentStep === <?php echo e($stepNumber); ?> ? 'bg-gradient-to-br <?php echo e($step['gradient'] ?? 'from-indigo-500 to-purple-500'); ?> text-white shadow-md shadow-indigo-500/20' : ($wire.currentStep > <?php echo e($stepNumber); ?> ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                    <i class="bi <?php echo e($step['icon'] ?? 'bi-circle'); ?> text-lg" x-show="$wire.currentStep === <?php echo e($stepNumber); ?>"></i>
                                    <i class="bi bi-check-lg text-lg" x-show="$wire.currentStep > <?php echo e($stepNumber); ?>"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <p class="text-sm font-semibold transition-colors duration-200"
                                            :class="$wire.currentStep === <?php echo e($stepNumber); ?> ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'"><?php echo e($step['title']); ?></p>
                                        <i class="bi bi-check-circle-fill text-green-500 ml-2 text-sm" x-show="$wire.currentStep > <?php echo e($stepNumber); ?>"></i>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400"><?php echo e($step['description']); ?></p>
                                </div>
                            </div>

                            <!-- Connector -->
                            <!--[if BLOCK]><![endif]--><?php if(!$loop->last): ?>
                            <div class="w-12 h-1 rounded-full transition-all duration-200"
                                :class="$wire.currentStep >= <?php echo e($stepNumber + 1); ?> ? 'bg-gradient-to-r <?php echo e($step['connector_gradient'] ?? 'from-indigo-500 to-purple-500'); ?>' : 'bg-gray-300 dark:bg-zinc-600'"></div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/sales-header.blade.php ENDPATH**/ ?>