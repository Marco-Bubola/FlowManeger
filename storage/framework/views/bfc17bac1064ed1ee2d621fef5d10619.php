
<div class="min-h-screen flex flex-col" x-data="{ autoSearched: false }">
    <?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/produtos.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/produtos-extra.css')); ?>">
    <?php $__env->stopPush(); ?>

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
        }

        .product-card-modern {
            cursor: pointer;
            user-select: none;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }
    </style>

    
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-amber-50/90 to-yellow-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-yellow-900/20 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="relative px-6 py-5">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-5">
                    <a href="<?php echo e(route('mercadolivre.products')); ?>" class="w-12 h-12 rounded-xl bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-amber-50 dark:hover:bg-slate-700 transition-all">
                        <i class="bi bi-arrow-left text-xl text-amber-600 dark:text-amber-400"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 dark:from-amber-300 dark:to-orange-300 bg-clip-text text-transparent">Publicar no Mercado Livre</h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                            <!--[if BLOCK]><![endif]--><?php if($currentStep === 1): ?> Passo 1: Selecione os produtos
                            <?php elseif($currentStep === 2): ?> Passo 2: Catálogo ML (opcional)
                            <?php else: ?> Passo 3: Valores e configuração
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = [1 => 'Produtos', 2 => 'Catálogo', 3 => 'Config']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center">
                        <button type="button" wire:click="goToStep(<?php echo e($step); ?>)"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-sm transition-all
                                           <?php echo e($currentStep === $step ? 'bg-amber-500 text-white shadow-lg' : ($currentStep > $step ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500')); ?>">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center <?php echo e($currentStep >= $step ? 'bg-white/20' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php if($currentStep > $step): ?><i class="bi bi-check-lg text-xs"></i><?php else: ?><?php echo e($step); ?><?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </span>
                            <?php echo e($label); ?>

                        </button>
                        <!--[if BLOCK]><![endif]--><?php if($step < 3): ?><i class="bi bi-chevron-right text-slate-400 text-xs"></i><?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                
                <div class="flex items-center gap-2 ml-auto">
                    <!--[if BLOCK]><![endif]--><?php if($currentStep === 2): ?>
                    <button type="button" wire:click="searchCatalog" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold hover:shadow-lg transition-all disabled:opacity-50">
                        <i class="bi bi-search" wire:loading.remove wire:target="searchCatalog"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="searchCatalog"></i>
                        Buscar Catálogo
                    </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php if($currentStep > 1): ?>
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php if($currentStep < 3): ?>
                        <!--[if BLOCK]><![endif]--><?php if($currentStep===1 && $this->hasSelectedProducts()): ?>
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold shadow-lg transition-all">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                        <?php elseif($currentStep === 2): ?>
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold shadow-lg transition-all">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php else: ?>
                        <button type="submit" form="publish-form"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold shadow-xl transition-all">
                            <i class="bi bi-rocket-takeoff-fill"></i> Publicar no ML
                        </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($currentStep === 1): ?>
    <div class="flex-1 flex">
        <div class="w-3/4 bg-white dark:bg-zinc-800 rounded-2xl border border-slate-200 dark:border-zinc-700 flex flex-col overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="bi bi-box text-amber-500 mr-3"></i>Selecionar Produtos
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Apenas produtos prontos para publicação (com EAN, imagem, preço)</p>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Buscar por nome, código ou EAN..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <select wire:model.live="selectedCategory"
                        class="px-4 py-3 border border-gray-200 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                        <option value="">Todas as categorias</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id_category ?? $category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                <!--[if BLOCK]><![endif]--><?php if($this->filteredProducts->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center h-full">
                    <i class="bi bi-box-seam text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">Nenhum produto pronto</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-center text-sm">Cadastre produtos com: nome, preço, estoque, imagem e código de barras (EAN)</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->filteredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $isSelected = $this->isProductSelected($product->id); ?>
                    <div class="product-card-modern <?php echo e($isSelected ? 'selected' : ''); ?>"
                        wire:click="toggleProduct(<?php echo e($product->id); ?>)" wire:key="p-<?php echo e($product->id); ?>">
                        <div class="btn-action-group flex gap-2">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all <?php echo e($isSelected ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-slate-700 border-gray-300 text-transparent'); ?>">
                                <!--[if BLOCK]><![endif]--><?php if($isSelected): ?><i class="bi bi-check text-sm"></i><?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                        <div class="product-img-area">
                            <img src="<?php echo e($product->image_url); ?>" alt="<?php echo e($product->name); ?>" class="product-img">
                            <span class="badge-product-code"><i class="bi bi-upc-scan"></i> <?php echo e($product->product_code); ?></span>
                            <span class="badge-quantity"><i class="bi bi-stack"></i> <?php echo e($product->stock_quantity); ?></span>
                            <!--[if BLOCK]><![endif]--><?php if($product->category): ?>
                            <div class="category-icon-wrapper"><i class="<?php echo e($product->category->icone ?? 'bi bi-box'); ?> category-icon"></i></div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="card-body">
                            <div class="product-title"><?php echo e(ucwords($product->name)); ?></div>
                            <div class="price-area mt-2 flex flex-col gap-1">
                                <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> R$ <?php echo e(number_format($product->price ?? 0, 2, ',', '.')); ?></span>
                                <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> R$ <?php echo e(number_format($product->price_sale ?? $product->price, 2, ',', '.')); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <div class="w-1/4 flex flex-col bg-white dark:bg-zinc-800 rounded-2xl border border-slate-200 dark:border-zinc-700 ml-4 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white"><i class="bi bi-cart-check text-amber-500 mr-2"></i>Selecionados (<?php echo e(count($selectedProducts)); ?>)</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <!--[if BLOCK]><![endif]--><?php if(empty($selectedProducts)): ?>
                <div class="text-center py-8">
                    <i class="bi bi-cart-x text-4xl text-slate-300 mb-3"></i>
                    <p class="text-sm text-slate-500">Clique nos produtos à esquerda</p>
                </div>
                <?php else: ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 mb-2">
                    <img src="<?php echo e($p['image_url'] ?? ''); ?>" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" onerror="this.src='<?php echo e(asset('storage/products/product-placeholder.png')); ?>'">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"><?php echo e($p['name']); ?></p>
                        <div class="mt-1 flex items-center gap-2">
                            <label class="text-[10px] text-slate-500 font-medium">R$</label>
                            <input type="number" wire:model.live="selectedProducts.<?php echo e($idx); ?>.price_sale" step="0.01" min="0"
                                class="w-20 py-1 px-1.5 text-xs rounded border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($currentStep === 2): ?>
    <div class="flex-1 flex gap-6">
        
        <!--[if BLOCK]><![endif]--><?php if(!empty($catalogResults)): ?>
        <div class="flex-shrink-0 w-96 lg:w-[420px]">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sticky top-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3"><?php echo e(count($catalogResults)); ?> resultado(s)</h3>
                <div class="grid grid-cols-2 gap-3 max-h-[60vh] overflow-y-auto">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $catalogResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catalogProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                    $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                    $domainId = $catalogProduct['domain_id'] ?? '';
                    $isSelected = $catalogProductId === $cProductId;
                    $imgUrl = $this->getCatalogResultImage($catalogProduct);
                    $price = $this->getCatalogResultPrice($catalogProduct);
                    ?>
                    <div wire:click="selectCatalogProduct('<?php echo e($cProductId); ?>', '<?php echo e($domainId); ?>')"
                        class="flex flex-col rounded-lg cursor-pointer transition-all border-2 overflow-hidden <?php echo e($isSelected ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300'); ?>">
                        <div class="h-20 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden">
                            <!--[if BLOCK]><![endif]--><?php if($imgUrl): ?>
                            <img src="<?php echo e($imgUrl); ?>" alt="" class="max-w-full max-h-full object-contain p-1">
                            <?php else: ?>
                            <i class="bi bi-box text-2xl text-slate-400"></i>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="p-2 flex-1 min-h-0">
                            <p class="font-semibold text-xs text-slate-900 dark:text-white line-clamp-2"><?php echo e($catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título'); ?></p>
                            <!--[if BLOCK]><![endif]--><?php if($price): ?>
                            <p class="text-sm font-bold text-teal-600 dark:text-teal-400 mt-1">R$ <?php echo e(number_format($price, 2, ',', '.')); ?></p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($catalogProductId && !empty($catalogProductData)): ?>
        <div class="flex-1 min-w-0 grid grid-cols-1 xl:grid-cols-2 gap-4">
            
            <div class="rounded-2xl bg-white dark:bg-slate-900 border-2 border-teal-200 dark:border-teal-800 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <h3 class="text-xl font-bold text-teal-800 dark:text-teal-300 flex items-center gap-2">
                            <i class="bi bi-patch-check-fill text-2xl"></i> Catálogo Selecionado
                        </h3>
                        <div class="flex items-center gap-3">
                            <!--[if BLOCK]><![endif]--><?php if($catalogPrice): ?>
                            <span class="text-2xl font-black text-teal-600 dark:text-teal-400">R$ <?php echo e(number_format($catalogPrice, 2, ',', '.')); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <a href="https://www.mercadolivre.com.br/p/<?php echo e($catalogProductId); ?>" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-teal-500 text-white text-sm font-bold hover:bg-teal-600">
                                <i class="bi bi-box-arrow-up-right"></i> Ver no ML
                            </a>
                            <button type="button" wire:click="clearCatalogProduct"
                                class="text-sm text-red-600 hover:text-red-700 font-medium">
                                <i class="bi bi-x-circle mr-1"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-lg font-bold text-slate-900 dark:text-white"><?php echo e($catalogProductName ?: '—'); ?></p>
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if(!empty($catalogPictures)): ?>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-b">
                    <h3 class="text-sm font-bold text-blue-700 dark:text-blue-400"><i class="bi bi-images mr-1"></i> Galeria (<?php echo e(count($catalogPictures)); ?>)</h3>
                </div>
                <div class="p-3 flex gap-2 overflow-x-auto">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $catalogPictures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $pic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $picUrl = $pic['secure_url'] ?? $pic['url'] ?? ''; ?>
                    <!--[if BLOCK]><![endif]--><?php if($picUrl): ?>
                    <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                        <img src="<?php echo e($picUrl); ?>" alt="Foto <?php echo e($idx + 1); ?>" class="w-full h-full object-cover">
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($catalogDescription): ?>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border-b">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300"><i class="bi bi-file-text mr-1"></i> Descrição</h3>
                </div>
                <div class="p-4 max-h-48 overflow-y-auto">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap"><?php echo e($catalogDescription); ?></p>
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if(!empty($catalogAttributes)): ?>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden xl:col-span-2">
                <div class="px-4 py-2 bg-purple-50 dark:bg-purple-900/20 border-b">
                    <h3 class="text-sm font-bold text-purple-700 dark:text-purple-400"><i class="bi bi-sliders mr-1"></i> Atributos (<?php echo e(count($catalogAttributes)); ?>)</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $catalogAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <!--[if BLOCK]><![endif]--><?php if(!empty($attr['value_id']) || !empty($attr['value_name'])): ?>
                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-500 uppercase"><?php echo e($attr['name']); ?></p>
                            <p class="text-xs font-semibold text-slate-900 dark:text-white"><?php echo e($attr['value_name'] ?: '—'); ?></p>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <?php else: ?>
        <div class="flex-1 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 min-h-[200px]">
            <div class="text-center p-6">
                <!--[if BLOCK]><![endif]--><?php if(empty($catalogResults)): ?>
                <i class="bi bi-search text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Clique em "Buscar Catálogo" no header para buscar pelo EAN</p>
                <?php else: ?>
                <i class="bi bi-cursor text-4xl text-slate-400 mb-3"></i>
                <p class="text-slate-600 dark:text-slate-400">Selecione um resultado à esquerda para ver os detalhes</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($currentStep === 3): ?>
    <form id="publish-form" wire:submit.prevent="publishProduct" class="flex-1">
        <?php
        $basePrice = (float)($publishPrice ?: 0) ?: $this->getTotalProductsPrice();
        $mlFee = match($listingType) { 'gold_special' => 0.16, 'gold_pro' => 0.17, 'gold' => 0.13, default => 0.11 };
        $mlFeeAmount = $basePrice * $mlFee;
        $shippingCost = $freeShipping ? 15.00 : 0;
        $netAmount = $basePrice - $mlFeeAmount - $shippingCost;
        $suggestedPrice = $this->getSuggestedPrice();
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <aside class="space-y-6">
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                        <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                            <i class="bi bi-box-seam text-lg"></i> Produtos (<?php echo e(count($selectedProducts)); ?>)
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <img src="<?php echo e($p['image_url'] ?? ''); ?>" class="w-14 h-14 rounded-lg object-cover" onerror="this.src='<?php echo e(asset('storage/products/product-placeholder.png')); ?>'">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-slate-900 dark:text-white truncate"><?php echo e($p['name']); ?></p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">R$ <?php echo e(number_format($p['price_sale'] ?? $p['unit_cost'] ?? 0, 2, ',', '.')); ?> · Est: <?php echo e($p['stock_quantity']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-emerald-200 dark:border-emerald-800">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                            <i class="bi bi-calculator-fill text-lg"></i> Resumo de Taxas
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Taxa ML (<?php echo e($mlFee * 100); ?>%)</span>
                            <span class="font-bold text-red-600 dark:text-red-400">- R$ <?php echo e(number_format($mlFeeAmount, 2, ',', '.')); ?></span>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($freeShipping): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Frete grátis</span>
                            <span class="font-bold text-red-600 dark:text-red-400">- R$ <?php echo e(number_format($shippingCost, 2, ',', '.')); ?></span>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Valor Líquido</span>
                            <span class="text-lg font-black <?php echo e($netAmount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'); ?>">R$ <?php echo e(number_format($netAmount, 2, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>
            </aside>

            
            <div class="space-y-6">
                
                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 overflow-hidden shadow-lg">
                    <div class="p-5">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                <i class="bi bi-textarea-t text-xl text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">Título que será publicado</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white leading-snug break-words"><?php echo e($this->getFinalTitle()); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <!--[if BLOCK]><![endif]--><?php if($catalogProductName): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-xs font-bold text-teal-700 dark:text-teal-300">
                                <i class="bi bi-patch-check-fill"></i> Do Catálogo ML
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400">
                                <i class="bi bi-box"></i> Produto Original
                            </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <i class="bi bi-currency-dollar text-lg"></i> Preço e Quantidade
                        </h4>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Preço do Anúncio</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 font-bold">R$</span>
                                    <input type="number" wire:model.live="publishPrice" step="0.01" min="0.01"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10 text-slate-900 dark:text-white font-bold text-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-[10px] text-slate-500 uppercase">Soma produtos</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">R$ <?php echo e(number_format($this->getTotalProductsPrice(), 2, ',', '.')); ?></p>
                                </div>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php if($catalogPrice): ?>
                            <p class="text-xs text-teal-600 dark:text-teal-400 mt-1"><i class="bi bi-patch-check"></i> Catálogo: R$ <?php echo e(number_format($catalogPrice, 2, ',', '.')); ?></p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($suggestedPrice > 0): ?>
                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">Preço Sugerido</p>
                                    <p class="text-xl font-black text-blue-700 dark:text-blue-300">R$ <?php echo e(number_format($suggestedPrice, 2, ',', '.')); ?></p>
                                </div>
                                <button type="button" wire:click="applySuggestedPrice"
                                    class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold transition-all">
                                    <i class="bi bi-check2-square mr-1"></i> Aplicar
                                </button>
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Quantidade</label>
                            <input type="number" wire:model="publishQuantity" min="1"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-bold">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-purple-200 dark:border-purple-800">
                        <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                            <i class="bi bi-bookmark-star-fill text-lg"></i> Tipo de Anúncio
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">Valores fixos do Mercado Livre (não vêm da API) · Definem taxa e exposição</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-3">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['gold_special' => ['Clássico', '16%', 'bi-trophy-fill', 'Destaque na busca · Boa visibilidade · Conversões médias'], 'gold_pro' => ['Premium', '17%', 'bi-star-fill', 'Máxima visibilidade · Topo das buscas · Mais vendas'], 'gold' => ['Gold', '13%', 'bi-star', 'Visibilidade intermediária · Bom custo-benefício'], 'free' => ['Grátis', '11%', 'bi-bag', 'Taxa mais baixa · Menos destaque · Básico']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="cursor-pointer">
                                <div class="p-3 rounded-xl border-2 transition-all <?php echo e($listingType === $key ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 shadow-md' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300'); ?>">
                                    <input type="radio" wire:model.live="listingType" value="<?php echo e($key); ?>" class="sr-only">
                                    <i class="bi <?php echo e($data[2]); ?> block text-2xl mb-1 <?php echo e($listingType === $key ? 'text-amber-600' : 'text-slate-400'); ?>"></i>
                                    <p class="font-bold text-sm <?php echo e($listingType === $key ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300'); ?>"><?php echo e($data[0]); ?></p>
                                    <p class="text-xs font-semibold <?php echo e($listingType === $key ? 'text-amber-600' : 'text-slate-500'); ?>">Taxa: <?php echo e($data[1]); ?></p>
                                    <p class="text-[9px] text-slate-400 mt-1 leading-tight"><?php echo e($data[3] ?? ''); ?></p>
                                </div>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="space-y-6">
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 border-b border-teal-200 dark:border-teal-800">
                        <h4 class="font-bold text-teal-700 dark:text-teal-400 flex items-center gap-2">
                            <i class="bi bi-truck text-lg"></i> Envio e Logística
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">📦 Mercado Envios · ⏱️ Prazo 2–5 dias úteis · 📍 Rastreamento incluído</p>
                    </div>
                    <div class="p-4 space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all <?php echo e($freeShipping ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300'); ?>">
                            <input type="checkbox" wire:model.live="freeShipping" class="w-5 h-5 rounded text-amber-500">
                            <div class="flex-1">
                                <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-truck-front-fill text-teal-500 text-lg"></i> Frete Grátis</p>
                                <p class="text-xs text-slate-500 leading-relaxed">💰 Você paga ~R$ 15,00 por venda<br>⭐ Destaque "FRETE GRÁTIS" na busca<br>📈 Aumenta conversão em até 30%</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all <?php echo e($localPickup ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300'); ?>">
                            <input type="checkbox" wire:model.live="localPickup" class="w-5 h-5 rounded text-amber-500">
                            <div class="flex-1">
                                <p class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-shop-window text-blue-500 text-lg"></i> Retirada Local</p>
                                <p class="text-xs text-slate-500 leading-relaxed">💵 Sem custo adicional<br>🏪 Cliente retira no endereço cadastrado<br>⚡ Atendimento mais rápido</p>
                            </div>
                        </label>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2"><i class="bi bi-info-circle mr-1"></i>Informações de Envio</p>
                            <ul class="space-y-1 text-[11px] text-slate-500">
                                <li>✓ Modalidade: <strong>Mercado Envios Full</strong></li>
                                <li>✓ Proteção: <strong>Garantia de entrega</strong></li>
                                <li>✓ Embalagem: <strong>Responsabilidade do vendedor</strong></li>
                                <li>✓ Coleta: <strong>Agendada automaticamente</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <i class="bi bi-box-seam text-lg"></i> Condição e Garantia
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Condição do produto</label>
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all <?php echo e($productCondition === 'new' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'); ?>">
                                    <input type="radio" wire:model.live="productCondition" value="new" class="sr-only">
                                    <i class="bi bi-star-fill text-blue-500"></i>
                                    <span class="font-semibold text-sm">Novo</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all <?php echo e($productCondition === 'used' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-700'); ?>">
                                    <input type="radio" wire:model.live="productCondition" value="used" class="sr-only">
                                    <i class="bi bi-box text-amber-500"></i>
                                    <span class="font-semibold text-sm">Usado</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Garantia (opcional)</label>
                            <input type="text" wire:model="warranty" placeholder="Ex: 90 dias, 1 ano"
                                class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-lg">
                    <div class="px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-amber-200 dark:border-amber-800">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <i class="bi bi-tag-fill text-lg"></i> Categoria ML
                        </h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <input type="text" wire:model.live.debounce.500ms="categorySearch" placeholder="Buscar categoria..."
                            class="w-full px-4 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                        <select wire:model.live="mlCategoryId"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-medium">
                            <option value="">Selecione</option>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $mlCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat['id']); ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/mercadolivre/publish-product.blade.php ENDPATH**/ ?>