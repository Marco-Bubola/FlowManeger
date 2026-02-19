<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'categories' => collect(),
    'search' => '',
    'category' => '',
    'tipo' => '',
    'status_filtro' => '',
    'preco_min' => '',
    'preco_max' => '',
    'perPage' => 12,
    'perPageOptions' => [],
    'ordem' => '',
    'estoque_filtro' => '',
    'data_filtro' => '',
    'totalProducts' => 0,
    'semEstoque' => false
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
    'categories' => collect(),
    'search' => '',
    'category' => '',
    'tipo' => '',
    'status_filtro' => '',
    'preco_min' => '',
    'preco_max' => '',
    'perPage' => 12,
    'perPageOptions' => [],
    'ordem' => '',
    'estoque_filtro' => '',
    'data_filtro' => '',
    'totalProducts' => 0,
    'semEstoque' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!-- Painel de Filtros Moderno - Estilo Sales Index -->
<div x-show="showFilters"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4"
     class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 mb-6">

    <!-- Header dos Filtros -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl shadow-lg">
                <i class="bi bi-funnel-fill text-white text-xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-purple-600 to-indigo-600 dark:from-indigo-300 dark:via-purple-300 dark:to-pink-300 bg-clip-text text-transparent">
                    Filtros Avançados
                </h3>
                <p class="text-slate-600 dark:text-slate-400">
                    Refine sua busca para encontrar produtos específicos
                </p>
            </div>
        </div>

        <!-- Contador de filtros ativos -->
        <div class="flex items-center gap-3">
            <!--[if BLOCK]><![endif]--><?php if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque_filtro || $data_filtro): ?>
            <div class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                <i class="bi bi-filter-circle text-blue-600 dark:text-blue-400"></i>
                <span class="font-semibold text-blue-800 dark:text-blue-300 text-sm"><?php echo e($totalProducts); ?> encontrados</span>
            </div>
            <button wire:click="clearFilters"
                    class="group flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="bi bi-x-circle group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="text-sm">Limpar</span>
            </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!-- Grid de Filtros - 4 Colunas Estilo Sales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Coluna 1: Status -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-activity mr-2 text-emerald-500"></i>
                Status do Produto
            </h4>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="$set('status_filtro', '')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($status_filtro === '' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500'); ?>">
                    <i class="bi bi-list-ul text-emerald-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                </button>

                <button wire:click="$set('status_filtro', 'ativo')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($status_filtro === 'ativo' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500'); ?>">
                    <i class="bi bi-check-circle text-green-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ativo</span>
                </button>

                <button wire:click="$set('status_filtro', 'inativo')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($status_filtro === 'inativo' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500'); ?>">
                    <i class="bi bi-pause-circle text-yellow-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Inativo</span>
                </button>

                <button wire:click="$set('status_filtro', 'descontinuado')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($status_filtro === 'descontinuado' ? 'border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500'); ?>">
                    <i class="bi bi-x-circle text-red-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Descontinuado</span>
                </button>
            </div>
        </div>

        <!-- Coluna 2: Tipo de Produto -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-box-seam mr-2 text-indigo-500"></i>
                Tipo de Produto
            </h4>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="$set('tipo', '')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($tipo === '' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500'); ?>">
                    <i class="bi bi-list-ul text-indigo-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                </button>

                <button wire:click="$set('tipo', 'simples')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($tipo === 'simples' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500'); ?>">
                    <i class="bi bi-box text-blue-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Simples</span>
                </button>

                <button wire:click="$set('tipo', 'kit')"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($tipo === 'kit' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500'); ?>">
                    <i class="bi bi-boxes text-green-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kit</span>
                </button>
            </div>
        </div>

        <!-- Coluna 3: Estoque -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-stack mr-2 text-red-500"></i>
                Estoque
            </h4>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="$set('semEstoque', false)"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e(!$semEstoque ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500'); ?>">
                    <i class="bi bi-check-circle text-indigo-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                </button>

                <button wire:click="$set('semEstoque', true)"
                        class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($semEstoque ? 'border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500'); ?>">
                    <i class="bi bi-exclamation-triangle text-red-500 text-xl"></i>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Sem Estoque</span>
                </button>
            </div>
        </div>

        <!-- Coluna 3: Itens por Página -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-eye mr-2 text-pink-500"></i>
                Itens por página
            </h4>
            <div class="grid grid-cols-3 gap-2">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button wire:click="$set('perPage', <?php echo e($option); ?>)"
                        class="flex items-center justify-center p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e($perPage == $option ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500'); ?>">
                    <i class="bi bi-grid-3x2-gap text-pink-500 text-sm mr-1.5"></i>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo e($option); ?></span>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Coluna 4: Ordenação -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                <i class="bi bi-sort-alpha-down mr-2 text-amber-500"></i>
                Ordenação
            </h4>
            <div class="grid grid-cols-2 gap-2">
                <button
                    wire:click="toggleSort('data')"
                    class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e(str_contains($ordem, 'data') || str_contains($ordem, 'recente') ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500'); ?>">
                    <i class="bi bi-calendar text-blue-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Data</span>
                        <!--[if BLOCK]><![endif]--><?php if(str_contains($ordem, 'recente') || $ordem === 'data_desc'): ?>
                            <i class="bi bi-arrow-down text-blue-500 text-xs"></i>
                        <?php elseif($ordem === 'data_asc'): ?>
                            <i class="bi bi-arrow-up text-blue-500 text-xs"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </button>

                <button
                    wire:click="toggleSort('updated')"
                    class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e(str_contains($ordem, 'updated') || str_contains($ordem, 'atualizado') ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500'); ?>">
                    <i class="bi bi-clock-history text-orange-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Atualizado</span>
                        <!--[if BLOCK]><![endif]--><?php if(str_contains($ordem, 'atualizado') || $ordem === 'updated_desc'): ?>
                            <i class="bi bi-arrow-down text-orange-500 text-xs"></i>
                        <?php elseif($ordem === 'updated_asc'): ?>
                            <i class="bi bi-arrow-up text-orange-500 text-xs"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </button>

                <button
                    wire:click="toggleSort('nome')"
                    class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e(str_contains($ordem, 'az') || str_contains($ordem, 'nome') ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500'); ?>">
                    <i class="bi bi-sort-alpha-down text-purple-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nome</span>
                        <!--[if BLOCK]><![endif]--><?php if($ordem === 'az' || $ordem === 'nome_asc'): ?>
                            <i class="bi bi-arrow-up text-purple-500 text-xs"></i>
                        <?php elseif($ordem === 'nome_desc'): ?>
                            <i class="bi bi-arrow-down text-purple-500 text-xs"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </button>

                <button
                    wire:click="toggleSort('preco')"
                    class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 <?php echo e(str_contains($ordem, 'preco') ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500'); ?>">
                    <i class="bi bi-currency-dollar text-green-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Preço</span>
                        <!--[if BLOCK]><![endif]--><?php if($ordem === 'preco_asc'): ?>
                            <i class="bi bi-arrow-up text-green-500 text-xs"></i>
                        <?php elseif($ordem === 'preco_desc'): ?>
                            <i class="bi bi-arrow-down text-green-500 text-xs"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </button>

                <button
                    wire:click="toggleSort('estoque')"
                    class="flex flex-col items-center justify-center gap-2 p-3 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 col-span-2 <?php echo e(str_contains($ordem, 'estoque') ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500'); ?>">
                    <i class="bi bi-stack text-teal-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Estoque</span>
                        <!--[if BLOCK]><![endif]--><?php if($ordem === 'estoque_asc'): ?>
                            <i class="bi bi-arrow-up text-teal-500 text-xs"></i>
                        <?php elseif($ordem === 'estoque_desc'): ?>
                            <i class="bi bi-arrow-down text-teal-500 text-xs"></i>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </button>
            </div>
        </div>

    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/products-filters.blade.php ENDPATH**/ ?>