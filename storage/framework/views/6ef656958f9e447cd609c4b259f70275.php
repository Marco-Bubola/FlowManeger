<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Categorias',
    'description' => 'Organize e gerencie suas categorias com eficiência',
    'totalCategories' => 0,
    'productCategories' => 0,
    'transactionCategories' => 0,
    'activeTab' => 'all',
    'showQuickActions' => true
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
    'title' => 'Categorias',
    'description' => 'Organize e gerencie suas categorias com eficiência',
    'totalCategories' => 0,
    'productCategories' => 0,
    'transactionCategories' => 0,
    'activeTab' => 'all',
    'showQuickActions' => true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!-- Header Moderno para Categorias -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-md shadow-indigo-400/20">
                    <i class="fas fa-tags text-white text-2xl"></i>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1">
                    <!--[if BLOCK]><![endif]--><?php if(isset($breadcrumb)): ?>
                        <?php echo e($breadcrumb); ?>

                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                        <?php echo e($title); ?>

                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400"><?php echo e($description); ?></p>

                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-blue-500/15 to-indigo-500/15 rounded-lg border border-blue-200 dark:border-blue-700">
                            <i class="fas fa-box text-blue-600 dark:text-blue-400 text-sm"></i>
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300"><?php echo e($productCategories); ?> produtos</span>
                        </div>

                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-emerald-500/15 to-teal-500/15 rounded-lg border border-emerald-200 dark:border-emerald-700">
                            <i class="fas fa-exchange-alt text-emerald-600 dark:text-emerald-400 text-sm"></i>
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300"><?php echo e($transactionCategories); ?> transações</span>
                        </div>

                        <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-lg border border-purple-200 dark:border-purple-700">
                            <i class="fas fa-list text-purple-600 dark:text-purple-400 text-sm"></i>
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-300"><?php echo e($totalCategories); ?> total</span>
                        </div>
                    </div>
                </div>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($showQuickActions): ?>
            <div class="flex flex-wrap gap-2 items-center">
                <!--[if BLOCK]><![endif]--><?php if($activeTab === 'products' || $activeTab === 'all'): ?>
                <button wire:click="createProductCategory"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Produto</span>
                </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($activeTab === 'transactions' || $activeTab === 'all'): ?>
                <button wire:click="createTransactionCategory"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Transação</span>
                </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <button wire:click="exportCategories"
                   class="group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-br from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-file-export mr-2 group-hover:scale-110 transition-transform duration-150"></i>
                    <span class="text-sm">Exportar</span>
                </button>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/category-header.blade.php ENDPATH**/ ?>