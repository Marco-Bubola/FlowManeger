<div class="min-h-screen w-full bg-gradient-to-b from-slate-50 via-indigo-50/30 to-slate-50 dark:from-slate-950 dark:via-indigo-950/20 dark:to-slate-950">
    <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-8">

        
        <?php if (isset($component)) { $__componentOriginalcfcab9726b2315e1e83548084507bfeb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcfcab9726b2315e1e83548084507bfeb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sales-header','data' => ['title' => 'Editar Publicação','description' => 'Alterações são salvas no sistema e enviadas ao Mercado Livre','backRoute' => route('mercadolivre.publications')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sales-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Editar Publicação','description' => 'Alterações são salvas no sistema e enviadas ao Mercado Livre','backRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('mercadolivre.publications'))]); ?>
             <?php $__env->slot('breadcrumb', null, []); ?> 
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-1">
                    <a href="<?php echo e(route('mercadolivre.products')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Produtos ML</a>
                    <i class="bi bi-chevron-right text-xs"></i>
                    <a href="<?php echo e(route('mercadolivre.publications')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Publicações</a>
                    <i class="bi bi-chevron-right text-xs"></i>
                    <span class="text-slate-800 dark:text-slate-200 font-medium"><?php echo e($publication->ml_item_id ?? 'Editar'); ?></span>
                </div>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/20 border border-emerald-400/50 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                        <i class="bi bi-arrow-repeat"></i>
                        Sincronização automática com o ML
                    </span>
                    <a href="<?php echo e(route('mercadolivre.publications')); ?>"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-arrow-left"></i>
                        Voltar
                    </a>
                    <!--[if BLOCK]><![endif]--><?php if($publication->status === 'active'): ?>
                        <button wire:click="pausePublication"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500/20 border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-300 text-sm font-semibold hover:bg-amber-500/30 transition-all shadow-sm">
                            <i class="bi bi-pause-fill"></i>
                            Pausar
                        </button>
                    <?php elseif($publication->status === 'paused'): ?>
                        <button wire:click="activatePublication"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/20 border border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-300 text-sm font-semibold hover:bg-emerald-500/30 transition-all shadow-sm">
                            <i class="bi bi-play-fill"></i>
                            Ativar
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <button wire:click="updatePublication" wire:loading.attr="disabled" wire:target="updatePublication"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                        <i class="bi bi-check-lg"></i>
                        Salvar e enviar ao ML
                    </button>
                </div>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcfcab9726b2315e1e83548084507bfeb)): ?>
<?php $attributes = $__attributesOriginalcfcab9726b2315e1e83548084507bfeb; ?>
<?php unset($__attributesOriginalcfcab9726b2315e1e83548084507bfeb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcfcab9726b2315e1e83548084507bfeb)): ?>
<?php $component = $__componentOriginalcfcab9726b2315e1e83548084507bfeb; ?>
<?php unset($__componentOriginalcfcab9726b2315e1e83548084507bfeb); ?>
<?php endif; ?>

        
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10 dark:from-indigo-500/20 dark:via-purple-500/20 dark:to-pink-500/20 border border-indigo-200/50 dark:border-indigo-800/50 shadow-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-transparent via-white/50 to-transparent dark:via-white/5"></div>
            <div class="relative px-6 py-6 sm:px-8 sm:py-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Publicação no Mercado Livre</p>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 max-w-2xl"><?php echo e($publication->title); ?></h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 text-xs font-mono"><?php echo e($publication->ml_item_id); ?></span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold <?php echo e($publication->status === 'active' ? 'bg-emerald-500/30 text-emerald-800 dark:text-emerald-200' : ''); ?> <?php echo e($publication->status === 'paused' ? 'bg-amber-500/30 text-amber-800 dark:text-amber-200' : ''); ?> <?php echo e($publication->status === 'closed' ? 'bg-red-500/30 text-red-800 dark:text-red-200' : ''); ?>"><?php echo e(ucfirst($publication->status)); ?></span>
                            <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-700 dark:text-purple-300 text-xs font-semibold"><?php echo e($publicationType === 'kit' ? 'Kit' : 'Simples'); ?></span>
                        </div>
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if($publication->ml_item_id): ?>
                        <?php
                            $mlUrl = $publication->ml_permalink
                                ?: 'https://articulo.mercadolibre.com.br/' . $publication->ml_item_id;
                        ?>
                        <button wire:click="refreshFromMl" wire:loading.attr="disabled" wire:target="refreshFromMl"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-500/20 border border-blue-400 dark:border-blue-600 text-blue-700 dark:text-blue-300 text-sm font-semibold hover:bg-blue-500/30 transition-all"
                            title="Trazer título, preço e descrição do ML para cá">
                            <i class="bi bi-arrow-down-circle" wire:loading.remove wire:target="refreshFromMl"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshFromMl"></i>
                            Atualizar do ML
                        </button>
                        <a href="<?php echo e($mlUrl); ?>" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-yellow-400/90 hover:bg-yellow-500 text-slate-900 text-sm font-bold shadow-lg transition-all">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Ver no ML
                        </a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">

                
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 dark:from-indigo-900/30 dark:to-purple-900/30 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white"><i class="bi bi-type"></i></span>
                            Título e descrição
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Título do anúncio *</label>
                            <input type="text" wire:model="title" maxlength="255"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder-slate-400"
                                placeholder="Título exibido no Mercado Livre">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Descrição</label>
                            <textarea wire:model="description" rows="5"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none placeholder-slate-400"
                                placeholder="Descrição do produto (texto simples)"></textarea>
                        </div>
                    </div>
                </div>

                
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 dark:from-emerald-900/30 dark:to-teal-900/30 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white"><i class="bi bi-currency-dollar"></i></span>
                            Preço e opções
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Preço *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 font-semibold">R$</span>
                                    <input type="number" wire:model="price" step="0.01" min="0.01"
                                        class="w-full pl-11 pr-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all">
                                </div>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tipo de anúncio</label>
                                <select wire:model="listingType"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="gold_special">Gold Special</option>
                                    <option value="gold_pro">Gold Pro</option>
                                    <option value="gold">Gold</option>
                                    <option value="free">Clássico (Free)</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Condição</label>
                                <select wire:model="condition"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="new">Novo</option>
                                    <option value="used">Usado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Garantia</label>
                                <input type="text" wire:model="warranty"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all placeholder-slate-400"
                                    placeholder="Ex: 90 dias, 1 ano">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Categoria ML (ID)</label>
                            <input type="text" wire:model="mlCategoryId"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all font-mono text-sm placeholder-slate-400"
                                placeholder="Ex: MLB1234">
                        </div>
                        <div class="flex flex-wrap gap-6 pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" wire:model="freeShipping" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Frete grátis</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" wire:model="localPickup" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Retirada local</span>
                            </label>
                        </div>
                        <button wire:click="updatePublication" wire:loading.attr="disabled" wire:target="updatePublication"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                            <i class="bi bi-check-lg"></i>
                            Salvar e enviar ao ML
                        </button>
                    </div>
                </div>

                
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-500/10 to-pink-500/10 dark:from-purple-900/30 dark:to-pink-900/30 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-500 flex items-center justify-center text-white"><i class="bi bi-box-seam"></i></span>
                            Produtos <?php echo e($publicationType === 'kit' ? 'do kit' : ''); ?>

                            <span class="text-sm font-normal text-slate-500 dark:text-slate-400">(<?php echo e(count($products)); ?>)</span>
                        </h3>
                        <button wire:click="toggleProductSelector"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                            <i class="bi <?php echo e($showProductSelector ? 'bi-x-lg' : 'bi-plus-lg'); ?>"></i>
                            <?php echo e($showProductSelector ? 'Fechar' : 'Adicionar produto'); ?>

                        </button>
                    </div>
                    <div class="p-6">
                        <!--[if BLOCK]><![endif]--><?php if($showProductSelector): ?>
                            <div class="mb-6 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border-2 border-dashed border-slate-200 dark:border-slate-700">
                                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('mercado-livre.components.product-selector', ['initialProducts' => []]);

$__html = app('livewire')->mount($__name, $__params, 'selector-' . $publication->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!empty($products)): ?>
                            <div class="space-y-4">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex flex-wrap items-center gap-4 p-5 rounded-2xl bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-slate-800/80 dark:to-slate-800/50 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
                                        <img src="<?php echo e($product['image_url'] ?? asset('images/placeholder.png')); ?>" alt="<?php echo e($product['name']); ?>"
                                            class="w-20 h-20 object-cover rounded-xl border-2 border-slate-200 dark:border-slate-600 shadow-inner">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-slate-900 dark:text-slate-100 truncate"><?php echo e($product['name']); ?></h4>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                <?php echo e($product['product_code']); ?> · Estoque: <?php echo e($product['stock_quantity']); ?>

                                                <!--[if BLOCK]><![endif]--><?php if($publicationType === 'kit'): ?>
                                                    · Até <?php echo e(floor($product['stock_quantity'] / max(1, $product['quantity']))); ?> un. disponíveis
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div>
                                                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Qtd. por venda</label>
                                                <input type="number" min="1" value="<?php echo e($product['quantity']); ?>"
                                                    wire:change="updateProductQuantity(<?php echo e($product['id']); ?>, $event.target.value)"
                                                    class="w-20 px-2 py-2.5 text-center rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                            <button wire:click="removeProduct(<?php echo e($product['id']); ?>)" wire:confirm="Remover este produto da publicação?"
                                                class="p-2.5 rounded-xl text-red-500 hover:bg-red-500/20 transition-all" title="Remover">
                                                <i class="bi bi-trash text-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php else: ?>
                            <div class="text-center py-14 text-slate-500 dark:text-slate-400">
                                <div class="w-16 h-16 rounded-2xl bg-slate-200/50 dark:bg-slate-700/50 flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-box-seam text-3xl text-slate-400"></i>
                                </div>
                                <p class="font-medium">Nenhum produto vinculado</p>
                                <p class="text-sm mt-1">Use "Adicionar produto" para associar produtos a esta publicação.</p>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>

            
            <div class="space-y-6">

                
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-100 to-slate-200/80 dark:from-slate-800 dark:to-slate-700 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-600 flex items-center justify-center text-white"><i class="bi bi-activity"></i></span>
                            Status
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Publicação</span>
                            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold <?php echo e($publication->status === 'active' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50' : ''); ?> <?php echo e($publication->status === 'paused' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-400/50' : ''); ?> <?php echo e($publication->status === 'closed' ? 'bg-red-500/20 text-red-700 dark:text-red-300 border border-red-400/50' : ''); ?>"><?php echo e(ucfirst($publication->status)); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Sincronização</span>
                            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold <?php echo e($publication->sync_status === 'synced' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : ''); ?> <?php echo e($publication->sync_status === 'pending' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300' : ''); ?> <?php echo e($publication->sync_status === 'error' ? 'bg-red-500/20 text-red-700 dark:text-red-300' : ''); ?>"><?php echo e(ucfirst($publication->sync_status)); ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Qtd. disponível</span>
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400"><?php echo e($availableQuantity); ?></span>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($publication->last_sync_at): ?>
                            <div class="flex justify-between items-center text-sm pt-1">
                                <span class="text-slate-600 dark:text-slate-400">Última sync</span>
                                <span class="text-slate-500 dark:text-slate-400"><?php echo e($publication->last_sync_at->diffForHumans()); ?></span>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if($publication->error_message): ?>
                            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                                <p class="text-xs text-red-700 dark:text-red-300"><?php echo e($publication->error_message); ?></p>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-500 flex items-center justify-center text-white"><i class="bi bi-journal-text"></i></span>
                            Logs recentes
                        </h3>
                    </div>
                    <div class="p-4 max-h-72 overflow-y-auto">
                        <!--[if BLOCK]><![endif]--><?php if($stockLogs->count() > 0): ?>
                            <div class="space-y-2">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $stockLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="text-xs p-3 rounded-xl border-l-4 <?php echo e($log->operation_type === 'ml_sale' ? 'bg-red-50 dark:bg-red-900/10 border-red-500' : ''); ?> <?php echo e($log->operation_type === 'sync_to_ml' ? 'bg-blue-50 dark:bg-blue-900/10 border-blue-500' : ''); ?> <?php echo e($log->operation_type === 'manual_update' ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-500' : ''); ?>">
                                        <div class="flex justify-between mb-1">
                                            <span class="font-semibold text-slate-700 dark:text-slate-300"><?php echo e($log->getOperationDescription()); ?></span>
                                            <span class="text-slate-500"><?php echo e($log->created_at->format('d/m H:i')); ?></span>
                                        </div>
                                        <div class="text-slate-600 dark:text-slate-400"><?php echo e($log->product->name ?? 'Produto'); ?>: <?php echo e($log->quantity_before); ?> → <?php echo e($log->quantity_after); ?> (<?php echo e($log->quantity_change > 0 ? '+' : ''); ?><?php echo e($log->quantity_change); ?>)</div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">Nenhum log ainda</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                
                <div class="rounded-3xl border-2 border-red-300 dark:border-red-800 bg-red-50/80 dark:bg-red-900/20 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-200 dark:border-red-800">
                        <h3 class="text-lg font-bold text-red-700 dark:text-red-300 flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle"></i>
                            Zona de perigo
                        </h3>
                    </div>
                    <div class="p-6">
                        <button wire:click="deletePublication"
                            wire:confirm="Tem certeza que deseja deletar esta publicação? Esta ação não pode ser desfeita."
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition-all shadow-md">
                            <i class="bi bi-trash"></i>
                            Deletar publicação
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/mercadolivre/edit-publication.blade.php ENDPATH**/ ?>