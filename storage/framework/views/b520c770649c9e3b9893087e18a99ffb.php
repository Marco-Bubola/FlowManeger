<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'category',
    'type' => 'product' // 'product' ou 'transaction'
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
    'category',
    'type' => 'product' // 'product' ou 'transaction'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div wire:sortable.item="<?php echo e($category->id_category); ?>"
     wire:key="<?php echo e($type); ?>-<?php echo e($category->id_category); ?>"
     class="group bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300 cursor-move overflow-hidden">

    <div class="p-4">
        <div class="flex items-center justify-between gap-3">
            <!-- Handle para arrastar + Ícone da Categoria -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Handle para arrastar -->
                <div wire:sortable.handle class="flex-shrink-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-grab active:cursor-grabbing">
                    <i class="fas fa-grip-vertical text-lg"></i>
                </div>

                <!-- Ícone da Categoria -->
                <div class="relative flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-md transform group-hover:scale-110 transition-transform duration-300"
                         style="background: linear-gradient(135deg, <?php echo e($category->hexcolor_category ?? ($type === 'product' ? '#3B82F6' : '#10B981')); ?>, <?php echo e($category->hexcolor_category ?? ($type === 'product' ? '#3B82F6' : '#10B981')); ?>dd)">
                        <!--[if BLOCK]><![endif]--><?php if($category->icone): ?>
                            <i class="<?php echo e($category->icone); ?> text-xl"></i>
                        <?php else: ?>
                            <i class="fas fa-<?php echo e($type === 'product' ? 'box' : 'exchange-alt'); ?> text-xl"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <!-- Badge de Status -->
                    <div class="absolute -top-1 -right-1 w-5 h-5 <?php echo e($category->is_active ? 'bg-green-500' : 'bg-red-500'); ?> rounded-full flex items-center justify-center shadow-sm">
                        <i class="fas fa-<?php echo e($category->is_active ? 'check' : 'times'); ?> text-white text-xs"></i>
                    </div>
                </div>

                <!-- Detalhes -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 text-base truncate"><?php echo e($category->name); ?></h4>
                        <!-- Botão de Favorita -->
                        <button wire:click="toggleFavorite(<?php echo e($category->id_category); ?>)"
                                class="flex-shrink-0 p-1 rounded-full hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                            <i class="fas fa-star text-yellow-500 text-xs"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm truncate"><?php echo e($category->description ?? 'Categoria de ' . ($type === 'product' ? 'produto' : 'transação')); ?></p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium <?php echo e($type === 'product' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200'); ?>">
                            <i class="fas fa-<?php echo e($type === 'product' ? 'box' : 'exchange-alt'); ?> mr-1"></i>
                            <?php echo e($type === 'product' ? 'Produto' : 'Transação'); ?>

                        </span>
                        <!--[if BLOCK]><![endif]--><?php if($type === 'transaction' && isset($category->tipo)): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium <?php echo e($category->tipo === 'gasto' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'); ?>">
                            <i class="fas fa-<?php echo e($category->tipo === 'gasto' ? 'arrow-down' : 'arrow-up'); ?> mr-1"></i>
                            <?php echo e($category->tipo === 'gasto' ? 'Despesa' : 'Receita'); ?>

                        </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex items-center gap-1 flex-shrink-0">
                <!-- Botão Compartilhar -->
                <button wire:click="shareCategory(<?php echo e($category->id_category); ?>)"
                        class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                        title="Compartilhar categoria">
                    <i class="fas fa-share-alt text-sm"></i>
                </button>
                <!-- Botão Editar -->
                <a href="<?php echo e(route('categories.edit', $category->id_category)); ?>"
                   class="p-2 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                   title="Editar categoria">
                    <i class="fas fa-edit text-sm"></i>
                </a>
                <!-- Botão Deletar -->
                <button wire:click="confirmDelete(<?php echo e($category->id_category); ?>)"
                        class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/category-card.blade.php ENDPATH**/ ?>