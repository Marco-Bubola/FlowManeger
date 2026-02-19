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

<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl shadow-lg">
                <i class="bi bi-box text-white text-2xl"></i>
            </div>
            Produtos da Venda
            <span class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-400 rounded-full text-sm font-semibold">
                <?php echo e($sale->saleItems->count()); ?> <?php echo e($sale->saleItems->count() === 1 ? 'item' : 'itens'); ?>

            </span>
        </h2>

        <div class="flex gap-3">
            <a href="<?php echo e(route('sales.edit-prices', $sale->id)); ?>"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
                <i class="bi bi-pencil mr-2"></i>
                Editar Preços
            </a>
            <a href="<?php echo e(route('sales.add-products', $sale->id)); ?>"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg font-semibold">
                <i class="bi bi-plus mr-2"></i>
                Adicionar Produto
            </a>
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($sale->saleItems->count() > 0): ?>
    <!-- Container de produtos com estilo unificado -->
    <div class="w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 pb-8 products-step-grid">
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sale->saleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $product = $item->product;
                $productName = $product->name ?? 'Produto não encontrado';
                $productCode = $product->product_code ?? 'N/A';
                $difference = $item->price_sale - $item->price;
                $subtotal = $item->price_sale * $item->quantity;
            ?>

            <div class="product-card-modern sale-product-card">
                <div class="btn-action-group">
                    <button type="button"
                            @click="$dispatch('show-modal-<?php echo e($item->id); ?>')"
                            class="btn btn-danger"
                            title="Remover produto">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="product-img-area">
                    <!--[if BLOCK]><![endif]--><?php if($product && $product->image): ?>
                        <img src="<?php echo e(asset('storage/products/' . $product->image)); ?>"
                             alt="<?php echo e($productName); ?>"
                             class="product-img">
                    <?php else: ?>
                        <img src="<?php echo e(asset('storage/products/product-placeholder.png')); ?>"
                             alt="Imagem não disponível"
                             class="product-img">
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <span class="badge-product-code">
                        <i class="bi bi-upc-scan"></i> <?php echo e($productCode); ?>

                    </span>

                    <span class="badge-quantity">
                        <i class="bi bi-cart-check"></i> <?php echo e($item->quantity); ?>x
                    </span>

                    <!--[if BLOCK]><![endif]--><?php if($product && $product->category): ?>
                    <div class="category-icon-wrapper">
                        <i class="<?php echo e($product->category->icone ?? 'bi bi-box'); ?> category-icon"></i>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div class="card-body sale-card-body">
                    <div class="product-title" title="<?php echo e($productName); ?>">
                        <?php echo e(ucwords($productName)); ?>

                    </div>



                    <div class="price-area">
                        <span class="badge-price" title="Preço Original">
                            <i class="bi bi-tag"></i>
                            <?php echo e(number_format($item->price, 2, ',', '.')); ?>

                        </span>

                        <span class="badge-price-sale" title="Preço de Venda">
                            <i class="bi bi-currency-dollar"></i>
                            <?php echo e(number_format($item->price_sale, 2, ',', '.')); ?>

                        </span>
                    </div>

                    <div class="sale-card-summary">
                        <div class="sale-card-row">
                            <span class="sale-card-label"><i class="bi bi-calculator"></i> Subtotal</span>
                            <span class="sale-card-value">R$ <?php echo e(number_format($subtotal, 2, ',', '.')); ?></span>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($difference != 0): ?>
                        <div class="sale-card-row <?php echo e($difference > 0 ? 'sale-card-row-increase' : 'sale-card-row-discount'); ?>">
                            <span class="sale-card-label">
                                <i class="bi <?php echo e($difference > 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'); ?>"></i>
                                <?php echo e($difference > 0 ? 'Aumento' : 'Desconto'); ?>

                            </span>
                            <span class="sale-card-value">
                                <?php echo e($difference > 0 ? '+' : ''); ?>R$ <?php echo e(number_format($difference, 2, ',', '.')); ?>

                            </span>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
    <?php else: ?>
    <div class="text-center py-16 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-zinc-800/50 dark:via-zinc-700/50 dark:to-zinc-800/50 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-600">
        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="bi bi-box text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nenhum produto adicionado</h3>
        <p class="text-lg text-gray-500 dark:text-gray-400 mb-6">Adicione produtos para começar a visualizar os itens da venda</p>
        <a href="<?php echo e(route('sales.add-products', $sale->id)); ?>"
           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl font-semibold text-lg">
            <i class="bi bi-plus-circle mr-3 text-xl"></i>
            Adicionar Primeiro Produto
        </a>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/sale-products-grid.blade.php ENDPATH**/ ?>