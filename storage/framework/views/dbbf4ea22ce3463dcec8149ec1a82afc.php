<div x-data="{ currentStep: $wire.currentStep, init() { window.addEventListener('gotoStep', e => { this.currentStep = e.detail; $wire.set('currentStep', e.detail); }); $watch('currentStep', v => $wire.set('currentStep', v)); } }" x-init="init()" class="">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/produtos.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/produtos-extra.css')); ?>">

    <!-- Header Modernizado para Edição (2 passos como Create) -->
    <?php if (isset($component)) { $__componentOriginalcfcab9726b2315e1e83548084507bfeb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcfcab9726b2315e1e83548084507bfeb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sales-header','data' => ['title' => 'Editar Venda #'.e($sale->id).'','description' => 'Atualize as informações da venda seguindo os passos','backRoute' => route('sales.show', $sale->id),'currentStep' => $currentStep,'steps' => [
            [
                'title' => 'Produtos',
                'description' => 'Selecione itens e quantidades',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Finalizar',
                'description' => 'Revisar e salvar alterações',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-green-500 to-emerald-500'
            ]
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sales-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Editar Venda #'.e($sale->id).'','description' => 'Atualize as informações da venda seguindo os passos','back-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('sales.show', $sale->id)),'current-step' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentStep),'steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            [
                'title' => 'Produtos',
                'description' => 'Selecione itens e quantidades',
                'icon' => 'bi-box',
                'gradient' => 'from-purple-500 to-pink-500',
                'connector_gradient' => 'from-purple-500 to-pink-500'
            ],
            [
                'title' => 'Finalizar',
                'description' => 'Revisar e salvar alterações',
                'icon' => 'bi-check-circle',
                'gradient' => 'from-green-500 to-emerald-500'
            ]
        ])]); ?>
         <?php $__env->slot('breadcrumb', null, []); ?> 
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="<?php echo e(route('sales.index')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-shopping-cart mr-1"></i>Vendas
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Editar Venda</span>
            </div>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php
                $canProceed = count($selectedProducts) > 0 && $client_id;
                $tooltip = 'Ir para o resumo da venda';
                if (!$canProceed) {
                    if (empty($client_id) && count($selectedProducts) === 0) {
                        $tooltip = 'Selecione um cliente e adicione produtos para poder continuar.';
                    } elseif (empty($client_id)) {
                        $tooltip = 'Selecione um cliente para poder continuar.';
                    } elseif (count($selectedProducts) === 0) {
                        $tooltip = 'Adicione ao menos um produto para poder continuar.';
                    }
                }
            ?>

            <button
                type="button"
                <?php if($canProceed): ?>
                    @click="currentStep = 2; $wire.set('currentStep', 2)"
                <?php endif; ?>
                title="<?php echo e($tooltip); ?>"
                <?php if(!$canProceed): ?> disabled <?php endif; ?>
                class="
                    group relative inline-flex items-center justify-center px-6 py-2.5 rounded-lg font-semibold tracking-wide text-white transition-all duration-300
                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-900
                    <?php echo e($canProceed
                        ? 'bg-black/20 dark:bg-white/10 backdrop-blur-md border border-white/20 shadow-lg shadow-indigo-500/20 hover:bg-gradient-to-r from-indigo-500 to-purple-600'
                        : 'bg-slate-400/50 dark:bg-slate-700/50 cursor-not-allowed opacity-60'); ?>

                "
            >
                <span class="flex items-center gap-2">
                    <span class="hidden sm:inline">Ir para Resumo</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </button>
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

    <!-- Conteúdo Principal -->
    <div class="">
        <form wire:submit.prevent="update" class="">
            <div class="">

                <!-- Step 1: Produtos - Layout Split 3/4 e 1/4 (igual ao Create) -->
                <div x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full h-[80vh] flex">

                    <!-- Lado Esquerdo: Lista de Produtos (3/4 da tela) -->
                    <div class="w-3/4 flex flex-col h-full">
                        <!-- Header com Controles -->
                        <div class="p-2">
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-search text-gray-400"></i>
                                        </div>
                                        <input type="text"
                                            wire:model.live.debounce.300ms="searchTerm"
                                            placeholder="Pesquisar produtos por nome ou código..."
                                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <label class="toggle-filter">
                                        <input type="checkbox"
                                               wire:model.live="showOnlySelected"
                                               class="toggle-filter-input">
                                        <span class="toggle-filter-track">
                                            <span class="toggle-filter-thumb"></span>
                                        </span>
                                        <span class="toggle-filter-text text-gray-700 dark:text-gray-300 font-medium">
                                            Selecionados
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de Produtos com Scroll -->
                        <div class="flex-1 p-6 overflow-y-auto min-h-0">
                            <!--[if BLOCK]><![endif]--><?php if($this->getFilteredProducts()->isEmpty()): ?>
                            <div class="flex flex-col items-center justify-center h-full">
                                <div class="w-32 h-32 mx-auto mb-6 text-gray-400">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m8-8v2m0 4h.01M21 21l-5-5m5 5v-4a1 1 0 00-1-1h-4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    <!--[if BLOCK]><![endif]--><?php if($showOnlySelected && empty($selectedProducts)): ?>
                                    Nenhum produto selecionado
                                    <?php elseif($searchTerm): ?>
                                    Nenhum produto encontrado
                                    <?php else: ?>
                                    Nenhum produto disponível
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">
                                    <!--[if BLOCK]><![endif]--><?php if($showOnlySelected && empty($selectedProducts)): ?>
                                    Selecione alguns produtos para visualizá-los aqui
                                    <?php elseif($searchTerm): ?>
                                    Tente pesquisar com outros termos
                                    <?php else: ?>
                                    Cadastre produtos para começar a vender
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                            </div>
                            <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 products-step-grid">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->getFilteredProducts(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isSelected = collect($selectedProducts)->contains(function($selected) use ($product) {
                                        return $selected['product_id'] == $product->id;
                                    });
                                ?>
                                <div class="product-card-modern <?php echo e($isSelected ? 'selected' : ''); ?>"
                                    wire:click="toggleProduct(<?php echo e($product->id); ?>)"
                                    wire:key="product-<?php echo e($product->id); ?>">

                                    <div class="btn-action-group flex gap-2">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                                    <?php echo e($isSelected
                                                        ? 'bg-purple-600 border-purple-600 text-white'
                                                        : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-purple-400 dark:hover:border-purple-500'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($isSelected): ?>
                                            <i class="bi bi-check text-sm"></i>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>

                                    <div class="product-img-area">
                                        <img src="<?php echo e($product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png')); ?>"
                                            alt="<?php echo e($product->name); ?>"
                                            class="product-img">
                                        <!--[if BLOCK]><![endif]--><?php if($product->stock_quantity <= 5): ?>
                                        <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Baixo estoque
                                        </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <span class="badge-product-code">
                                            <i class="bi bi-upc-scan"></i> <?php echo e($product->product_code); ?>

                                        </span>
                                        <span class="badge-quantity">
                                            <i class="bi bi-stack"></i> <?php echo e($product->stock_quantity); ?>

                                        </span>
                                        <!--[if BLOCK]><![endif]--><?php if($product->category): ?>
                                        <div class="category-icon-wrapper">
                                            <i class="<?php echo e($product->category->icone ?? 'bi bi-box'); ?> category-icon"></i>
                                        </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <div class="card-body">
                                        <div class="product-title" title="<?php echo e($product->name); ?>">
                                            <?php echo e(ucwords($product->name)); ?>

                                        </div>
                                        <div class="price-area">
                                            <span class="badge-price" title="Preço de Custo">
                                                <i class="bi bi-tag"></i>
                                                <?php echo e(number_format($product->price, 2, ',', '.')); ?>

                                            </span>
                                            <span class="badge-price-sale" title="Preço de Venda">
                                                <i class="bi bi-currency-dollar"></i>
                                                <?php echo e(number_format($product->price_sale, 2, ',', '.')); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!-- Lado Direito: Painel de Resumo & Produtos Selecionados (1/4 da tela) - igual ao Create -->
                    <div class="w-1/4 flex flex-col h-[80vh]">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="bi bi-receipt text-indigo-500"></i>
                                    <span>Resumo da Venda</span>
                                </h3>
                                <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full dark:bg-amber-900 dark:text-amber-300">Edição</span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <!-- Bloco Cliente -->
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                    <div class="relative">
                                        <button type="button" class="w-full text-left" @click="open = !open; $nextTick(() => { if (open) $refs.clientSearchSidebar.focus() })">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-person-fill text-blue-500 text-lg"></i>
                                                <div>
                                                    <label class="text-[10px] font-medium text-blue-800 dark:text-blue-200">Cliente</label>
                                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100 -mt-1 truncate">
                                                        <?php echo e($this->selectedClient->name ?? 'Selecionar...'); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition @click.away="open = false; $wire.set('clientSearch', '')" class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                                            <div class="p-2 border-b border-slate-100 dark:border-zinc-700">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400"><i class="bi bi-search"></i></span>
                                                    <input x-ref="clientSearchSidebar" type="text" wire:model.live.debounce.250ms="clientSearch" placeholder="Buscar cliente..." class="w-full pl-10 pr-4 py-2 rounded-md border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 text-sm focus:ring-2 focus:ring-indigo-400">
                                                </div>
                                            </div>
                                            <div class="py-1">
                                                <?php $filteredClients = $this->filteredClients; ?>
                                                <!--[if BLOCK]><![endif]--><?php if($filteredClients->isEmpty()): ?>
                                                <div class="px-4 py-2 text-sm text-slate-500">Nenhum cliente encontrado</div>
                                                <?php else: ?>
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $filteredClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <button type="button" @click="open=false; $wire.set('client_id', <?php echo e($client->id); ?>).then(() => $wire.set('clientSearch',''))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-zinc-700 flex items-center gap-3 text-sm transition-colors">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm"><?php echo e(strtoupper(substr($client->name,0,1))); ?></div>
                                                    <div class="text-slate-700 dark:text-slate-300"><?php echo e($client->name); ?></div>
                                                </button>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <!-- Bloco Data -->
                                <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-calendar-fill text-purple-500 text-lg"></i>
                                        <div>
                                            <label class="text-[10px] font-medium text-purple-800 dark:text-purple-200">Data</label>
                                            <input type="date" wire:model="sale_date" class="p-0 text-sm font-bold text-slate-700 dark:text-slate-200 bg-transparent border-0 focus:ring-0">
                                        </div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['sale_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <!-- Bloco Pagamento -->
                                <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl shadow-sm" x-data="{ open: false }">
                                    <div class="relative h-full">
                                        <button type="button" @click="open = !open" class="w-full h-full text-left">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-credit-card-fill text-green-500 text-lg"></i>
                                                <div>
                                                    <label class="text-[10px] font-medium text-green-800 dark:text-green-200">Pagamento</label>
                                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                        <span><?php echo e($tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.away="open = false"
                                             class="absolute z-10 w-full mt-2 bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1">
                                            <button @click="$wire.set('tipo_pagamento', 'a_vista'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 <?php echo e($tipo_pagamento === 'a_vista' ? 'bg-green-100 dark:bg-green-900/50' : ''); ?>">
                                                <!--[if BLOCK]><![endif]--><?php if($tipo_pagamento === 'a_vista'): ?> <i class="bi bi-check-circle-fill text-green-500"></i> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <span>À Vista</span>
                                            </button>
                                            <button @click="$wire.set('tipo_pagamento', 'parcelado'); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-green-100/50 dark:hover:bg-green-900/20 <?php echo e($tipo_pagamento === 'parcelado' ? 'bg-green-100 dark:bg-green-900/50' : ''); ?>">
                                                <!--[if BLOCK]><![endif]--><?php if($tipo_pagamento === 'parcelado'): ?> <i class="bi bi-check-circle-fill text-green-500"></i> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <span>Parcelado</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['tipo_pagamento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <!-- Bloco Parcelas -->
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl shadow-sm">
                                    <!--[if BLOCK]><![endif]--><?php if($tipo_pagamento == 'parcelado'): ?>
                                    <div x-data="{ open: false }" x-transition class="h-full">
                                        <div class="relative h-full">
                                            <button type="button" @click="open = !open" class="w-full h-full text-left">
                                                <div class="flex items-center gap-2">
                                                    <i class="bi bi-hash text-amber-500 text-lg"></i>
                                                    <div>
                                                        <label class="text-[10px] font-medium text-amber-800 dark:text-amber-200">Parcelas</label>
                                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200 -mt-1">
                                                            <span><?php echo e($parcelas); ?>x</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 @click.away="open = false"
                                                 class="absolute z-10 w-full mt-2 bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-xl shadow-xl border border-slate-200/70 dark:border-slate-700/50 p-1 max-h-40 overflow-y-auto">
                                                <!--[if BLOCK]><![endif]--><?php for($i = 1; $i <= 12; $i++): ?>
                                                <button @click="$wire.set('parcelas', <?php echo e($i); ?>); open = false" type="button" class="w-full text-left flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-amber-100/50 dark:hover:bg-amber-900/20 <?php echo e($parcelas == $i ? 'bg-amber-100 dark:bg-amber-900/50' : ''); ?>">
                                                    <!--[if BLOCK]><![endif]--><?php if($parcelas == $i): ?> <i class="bi bi-check-circle-fill text-amber-500"></i> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <span><?php echo e($i); ?>x</span>
                                                </button>
                                                <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['parcelas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <?php else: ?>
                                    <div class="flex items-center gap-2 text-slate-400 h-full">
                                        <i class="bi bi-calendar-range-fill"></i>
                                        <span class="text-xs">Sem parcelas</span>
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>

                            <!--[if BLOCK]><![endif]--><?php if(!empty($selectedProducts)): ?>
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700/50">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2"><i class="bi bi-currency-dollar"></i>Valor Total</span>
                                    <span class="text-2xl font-bold text-green-500">
                                        R$ <?php echo e(number_format($this->getTotalPrice(), 2, ',', '.')); ?>

                                    </span>
                                </div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Lista de produtos selecionados com scroll (estilo igual ao Create) -->
                        <div class="flex-1 overflow-y-auto">
                            <!--[if BLOCK]><![endif]--><?php if(empty($selectedProducts)): ?>
                            <div class="p-3 text-center">
                                <div class="text-gray-400 mb-2">
                                    <i class="bi bi-cart-x text-2xl"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-500 text-xs">
                                    Clique nos produtos à esquerda para adicioná-los
                                </p>
                            </div>
                            <?php else: ?>
                            <div class="p-4">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $productItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $selectedProduct = $products->firstWhere('id', $productItem['product_id']);
                                    ?>
                                    <!--[if BLOCK]><![endif]--><?php if($selectedProduct): ?>
                                    <div class="bg-white dark:bg-slate-800 rounded-xl p-3.5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-600 transition-all duration-300 group">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-shrink-0">
                                                <img src="<?php echo e($selectedProduct->image ? asset('storage/products/' . $selectedProduct->image) : asset('storage/products/product-placeholder.png')); ?>"
                                                     alt="<?php echo e($selectedProduct->name); ?>"
                                                     class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <h4 class="font-bold text-slate-800 dark:text-white truncate" title="<?php echo e($selectedProduct->name); ?>">
                                                        <?php echo e($selectedProduct->name); ?>

                                                    </h4>
                                                    <button type="button"
                                                            wire:click="toggleProduct(<?php echo e($selectedProduct->id); ?>)"
                                                            class="text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors -mt-1 -mr-1">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                                <div class="flex items-center text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                    <i class="bi bi-tag mr-1.5"></i>
                                                    <span>Custo: R$ <?php echo e(number_format($selectedProduct->price, 2, ',', '.')); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex items-end justify-between">
                                            <div class="flex items-center gap-2">
                                                <label for="quantity-edit-<?php echo e($selectedProduct->id); ?>" class="text-xs text-slate-500 dark:text-gray-400 font-medium">Qtd:</label>
                                                <input type="number" id="quantity-edit-<?php echo e($selectedProduct->id); ?>"
                                                    wire:model.blur="selectedProducts.<?php echo e($index); ?>.quantity"
                                                    min="1"
                                                    <?php if(isset($selectedProduct->tipo) && $selectedProduct->tipo === 'simples'): ?>
                                                        max="<?php echo e($selectedProduct->stock_quantity); ?>"
                                                    <?php endif; ?>
                                                    class="w-20 h-8 text-center text-sm border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                                            </div>
                                            <div>
                                                <label for="price-edit-<?php echo e($selectedProduct->id); ?>" class="text-xs text-slate-500 dark:text-gray-400 font-medium">Preço Venda</label>
                                                <div class="relative">
                                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-sm text-slate-400">R$</span>
                                                    <input type="number" id="price-edit-<?php echo e($selectedProduct->id); ?>"
                                                       wire:model.blur="selectedProducts.<?php echo e($index); ?>.price_sale"
                                                       step="0.01" min="0"
                                                       class="w-28 h-8 font-semibold text-green-600 dark:text-green-400 border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/50 pl-7 pr-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs text-slate-500 dark:text-slate-400">Subtotal</span>
                                                <p class="font-bold text-slate-800 dark:text-white">
                                                    R$ <?php echo e(number_format(($productItem['quantity'] ?? 0) * ($productItem['price_sale'] ?? 0), 2, ',', '.')); ?>

                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                <!-- Step 2: Resumo e Finalização - Layout em duas colunas (igual ao Create) -->
                <div x-show="currentStep === 2"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-4"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="w-full max-h-screen flex overflow-hidden">

                    <div class="w-2/5 bg-white dark:bg-zinc-800 p-4 flex flex-col">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                            <i class="bi bi-check-circle text-green-600 dark:text-green-400 mr-2"></i>
                            Resumo da Venda
                        </h2>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 mb-4 rounded-lg border-l-4 border-blue-500">
                            <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200 mb-3">
                                <i class="bi bi-person-circle mr-2"></i>Cliente
                            </h3>
                            <!--[if BLOCK]><![endif]--><?php if($client_id && $selectedClient = $clients->firstWhere('id', $client_id)): ?>
                            <div class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Nome:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm"><?php echo e($selectedClient->name); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Telefone:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm"><?php echo e($selectedClient->phone); ?></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Data:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm"><?php echo e(\Carbon\Carbon::parse($sale_date)->format('d/m/Y')); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Pagamento:</p>
                                        <p class="text-blue-800 dark:text-blue-200 font-semibold text-sm"><?php echo e(ucfirst(str_replace('_', ' ', $tipo_pagamento))); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 rounded-lg border-l-4 border-indigo-500 mb-4">
                            <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-200 mb-2">
                                <i class="bi bi-calculator mr-2"></i>Total da Venda
                            </h3>
                            <!--[if BLOCK]><![endif]--><?php if($tipo_pagamento === 'parcelado' && $parcelas > 1): ?>
                            <div class="bg-white dark:bg-indigo-800/30 p-4 rounded-lg border border-indigo-200 dark:border-indigo-600 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-semibold text-indigo-700 dark:text-indigo-300">
                                        <i class="bi bi-credit-card-2-front mr-2"></i>Pagamento Parcelado
                                    </span>
                                    <span class="bg-indigo-100 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm font-bold">
                                        <?php echo e($parcelas); ?>x
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-indigo-600 dark:text-indigo-400">Valor por parcela:</span>
                                        <span class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                                            R$ <?php echo e(number_format($this->getTotalPrice() / $parcelas, 2, ',', '.')); ?>

                                        </span>
                                    </div>
                                    <div class="text-xs text-indigo-500 dark:text-indigo-400 mt-2">
                                        <i class="bi bi-info-circle mr-1"></i>
                                        Parcelas mensais de <?php echo e(\Carbon\Carbon::parse($sale_date)->format('d/m/Y')); ?>

                                        até <?php echo e(\Carbon\Carbon::parse($sale_date)->addMonths($parcelas - 1)->format('d/m/Y')); ?>

                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="bg-white dark:bg-green-800/30 p-4 rounded-lg border border-green-200 dark:border-green-600 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-semibold text-green-700 dark:text-green-300">
                                        <i class="bi bi-cash mr-2"></i>Pagamento à Vista
                                    </span>
                                    <span class="bg-green-100 dark:bg-green-700 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-bold">
                                        À Vista
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <div class="text-center">
                                <!--[if BLOCK]><![endif]--><?php if($tipo_pagamento === 'parcelado' && $parcelas > 1): ?>
                                <p class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">
                                    <?php echo e($parcelas); ?>x de R$ <?php echo e(number_format($this->getTotalPrice() / $parcelas, 2, ',', '.')); ?>

                                </p>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                    R$ <?php echo e(number_format($this->getTotalPrice(), 2, ',', '.')); ?>

                                </p>
                            </div>
                        </div>

                        <div class="mt-auto space-y-3">
                            <button type="button"
                                @click="currentStep = 1; $wire.set('currentStep', 1)"
                                class="group relative w-full inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                                <i class="bi bi-arrow-left mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                                Voltar: Produtos
                                <div class="absolute inset-0 rounded-2xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>

                            <button type="submit"
                                class="group relative w-full inline-flex items-center justify-center px-12 py-4 rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 hover:from-green-600 hover:via-emerald-600 hover:to-teal-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-green-300 backdrop-blur-sm"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove class="flex items-center">
                                    <i class="bi bi-check-circle mr-2 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                                    Salvar Alterações
                                </span>
                                <span wire:loading class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Salvando...
                                </span>
                                <div class="absolute inset-0 rounded-2xl bg-green-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                        </div>
                    </div>

                    <div class="w-4/5 bg-green-50 dark:bg-green-900/20 border-l border-gray-200 dark:border-zinc-700 p-8">
                        <h3 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-6">
                            <i class="bi bi-cart mr-2"></i>Produtos Selecionados (<?php echo e(count($selectedProducts)); ?>)
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-118">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <!--[if BLOCK]><![endif]--><?php if($product['product_id']): ?>
                            <?php
                                $productData = $products->firstWhere('id', $product['product_id']);
                                $total = ($product['quantity'] ?? 0) * ($product['price_sale'] ?? 0);
                            ?>
                            <!--[if BLOCK]><![endif]--><?php if($productData): ?>
                            <div class="product-card-modern">
                                <div class="product-img-area">
                                    <img src="<?php echo e($productData->image ? asset('storage/products/' . $productData->image) : asset('storage/products/product-placeholder.png')); ?>"
                                         alt="<?php echo e($productData->name ?? 'Produto'); ?>"
                                         class="product-img">
                                    <span class="badge-product-code">
                                        <i class="bi bi-upc-scan"></i> <?php echo e($productData->product_code ?? 'N/A'); ?>

                                    </span>
                                    <span class="badge-quantity">
                                        <i class="bi bi-cart-check"></i> <?php echo e($product['quantity'] ?? 0); ?>

                                    </span>
                                    <!--[if BLOCK]><![endif]--><?php if($productData->category): ?>
                                    <div class="category-icon-wrapper">
                                        <i class="<?php echo e($productData->category->icone ?? 'bi bi-box'); ?> category-icon"></i>
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="card-body">
                                    <div class="product-title" title="<?php echo e($productData->name ?? 'Produto não encontrado'); ?>">
                                        <?php echo e(ucwords($productData->name ?? 'Produto não encontrado')); ?>

                                    </div>
                                    <div class="price-area">
                                        <span class="badge-price" title="Preço Unitário">
                                            <i class="bi bi-tag"></i>
                                            <?php echo e(number_format($product['price_sale'] ?? 0, 2, ',', '.')); ?>

                                        </span>
                                        <span class="badge-price-sale" title="Total">
                                            <i class="bi bi-calculator"></i>
                                            <?php echo e(number_format($total, 2, ',', '.')); ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="mt-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-green-800 dark:text-green-200">
                                    <i class="bi bi-calculator mr-2"></i>
                                    Total Geral:
                                </span>
                                <span class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    R$ <?php echo e(number_format($this->getTotalPrice(), 2, ',', '.')); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-check-circle-fill mr-2"></i>
        <?php echo e(session('success')); ?>

        <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<?php if(session()->has('error')): ?>
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i>
        <?php echo e(session('error')); ?>

        <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/sales/edit-sale.blade.php ENDPATH**/ ?>