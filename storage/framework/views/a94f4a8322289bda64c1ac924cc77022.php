<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items' => collect(),
    'max' => 3,
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
    'items' => collect(),
    'max' => 3,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $displayItems = $items->take($max);
?>

<!--[if BLOCK]><![endif]--><?php if($displayItems->isNotEmpty()): ?>
    <div class="sale-card-products">
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $displayItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $product = $item->product;
            ?>
            <div class="product-card-modern sale-card-product bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 overflow-hidden rounded-xl">
                <div class="product-img-area relative" style="padding: 0 !important; margin: 0 !important; height: var(--sale-product-image-height);">
                    <img src="<?php echo e($product && $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png')); ?>"
                         alt="<?php echo e($product->name ?? 'Produto'); ?>"
                         style="width: 100%; height: 100%; object-fit: cover; padding: 0; margin: 0; display: block;"
                         class="!p-0 !m-0">

                    <span class="badge-product-code bg-slate-900/80 dark:bg-slate-800/90 text-white">
                        <i class="bi bi-upc-scan"></i> <?php echo e($product->product_code ?? 'N/A'); ?>

                    </span>

                    <span class="badge-quantity bg-blue-600/90 dark:bg-blue-500/90 text-white">
                        <i class="bi bi-stack"></i> <?php echo e($item->quantity); ?>x
                    </span>
                </div>

                <div class="card-body bg-white dark:bg-slate-700">
                    <div class="product-title text-slate-900 dark:text-white" title="<?php echo e($product->name ?? 'Produto não encontrado'); ?>">
                        <?php echo e(ucwords($product->name ?? 'Produto não encontrado')); ?>

                    </div>

                    <div class="price-area">
                        <span class="badge-price bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200" title="Preço Unitário">
                            <i class="bi bi-tag"></i>
                            <?php echo e(number_format($item->price, 2, ',', '.')); ?>

                        </span>

                        <span class="badge-price-sale bg-gradient-to-r from-emerald-500 to-green-500 text-white" title="Subtotal">
                            <i class="bi bi-currency-dollar"></i>
                            <?php echo e(number_format($item->price_sale * $item->quantity, 2, ',', '.')); ?>

                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!--[if BLOCK]><![endif]--><?php if($items->count() > $max): ?>
        <div class="sale-card-more text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
            +<?php echo e($items->count() - $max); ?> produtos
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/sale-card-products.blade.php ENDPATH**/ ?>