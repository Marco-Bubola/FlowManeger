<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Livro Caixa',
    'description' => 'Controle financeiro inteligente',
    'totalTransactions' => 0,
    'totalBalance' => 0,
    'totalIncome' => 0,
    'totalExpense' => 0,
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
    'title' => 'Livro Caixa',
    'description' => 'Controle financeiro inteligente',
    'totalTransactions' => 0,
    'totalBalance' => 0,
    'totalIncome' => 0,
    'totalExpense' => 0,
    'showQuickActions' => true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!-- Header Moderno para Livro Caixa (estilo consistente com invoice) -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
    <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

    <div class="relative px-6 py-4">
        <!-- Breadcrumb -->
        <!--[if BLOCK]><![endif]--><?php if(isset($breadcrumb)): ?>
            <?php echo e($breadcrumb); ?>

        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Top Section: Title + Logo + Métricas + Actions -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 mb-4">
            <div class="flex items-center gap-4">
                <!-- Logo/Ícone -->
                <div class="relative flex items-center justify-center w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-400/20">
                    <i class="fas fa-book-open text-white text-3xl"></i>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                </div>

                <div class="space-y-1 ml-2">
                    <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100 leading-tight tracking-tight drop-shadow-sm">
                        <?php echo e($title); ?>

                    </h1>
                    <div class="flex items-center gap-2 flex-wrap mt-0.5">
                        <p class="text-base font-medium text-slate-600 dark:text-slate-400"><?php echo e($description); ?></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 lg:gap-6 flex-wrap">
                <!-- Lado Direito: Métricas Financeiras -->
                <div class="flex items-center gap-4">
                    <!-- Receitas -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Receitas</span>
                        <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-emerald-600">
                            R$ <?php echo e(number_format($totalIncome, 2, ',', '.')); ?>

                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Entradas</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                    <!-- Despesas -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Despesas</span>
                        <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-600">
                            R$ <?php echo e(number_format($totalExpense, 2, ',', '.')); ?>

                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Saídas</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                    <!-- Saldo -->
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Saldo</span>
                        <span class="text-2xl font-black <?php echo e($totalBalance >= 0 ? 'text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600' : 'text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600'); ?>">
                            R$ <?php echo e(number_format($totalBalance, 2, ',', '.')); ?>

                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($totalTransactions); ?> lançamentos</span>
                    </div>
                </div>

                <!-- Divider vertical entre métricas e actions -->
                <div class="hidden lg:block h-16 w-px bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent"></div>

                <!-- Quick Actions Container -->
                <!--[if BLOCK]><![endif]--><?php if($showQuickActions): ?>
                    <div class="flex flex-col gap-3">
                        <!-- Botões Nova e Upload -->
                        <div class="flex items-center gap-3">
                            <!-- Botão Nova -->
                            <a href="<?php echo e(route('cashbook.create')); ?>"
                                class="group relative inline-flex items-center justify-center gap-2.5 px-5 py-3 overflow-hidden rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-emerald-400/50">
                                <!-- Fundo gradiente animado -->
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 via-teal-500 to-green-600"></div>
                                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-600/0 via-teal-400/40 to-green-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <!-- Brilho superior -->
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"></div>

                                <!-- Shadow glow -->
                                <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                                <!-- Conteúdo -->
                                <div class="relative flex items-center gap-2.5 z-10">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-white/30 rounded-full blur-md group-hover:bg-white/50 transition-all duration-300"></div>
                                        <i class="fas fa-plus-circle text-xl text-white relative group-hover:rotate-90 transition-transform duration-500"></i>
                                    </div>
                                    <span class="font-black text-sm text-white tracking-wider uppercase drop-shadow-lg">Criar</span>
                                </div>
                            </a>

                            <!-- Botão Upload -->
                            <a href="<?php echo e(route('cashbook.upload2')); ?>"
                                class="group relative inline-flex items-center justify-center gap-2.5 px-5 py-3 overflow-hidden rounded-2xl transition-all duration-500 transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-400/50">
                                <!-- Fundo gradiente animado -->
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600"></div>
                                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/0 via-indigo-400/40 to-purple-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <!-- Brilho superior -->
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/80 to-transparent"></div>

                                <!-- Shadow glow -->
                                <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                                <!-- Conteúdo -->
                                <div class="relative flex items-center gap-2.5 z-10">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-white/30 rounded-full blur-md group-hover:bg-white/50 transition-all duration-300"></div>
                                        <i class="fas fa-cloud-upload-alt text-xl text-white relative group-hover:-translate-y-1 transition-transform duration-500"></i>
                                    </div>
                                    <span class="font-black text-sm text-white tracking-wider uppercase drop-shadow-lg">Upload</span>
                                </div>
                            </a>
                        </div>

                        <!-- Botões Dicas e Filtros -->
                        <div class="flex items-center gap-3">
                            <!-- Botão Dicas -->
                            <button wire:click="toggleTips"
                                class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 overflow-hidden rounded-xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 shadow-md hover:shadow-lg">
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                                <i class="bi bi-lightbulb text-white relative group-hover:rotate-12 transition-transform duration-300"></i>
                                <span class="text-sm font-bold text-white hidden sm:inline">Dicas</span>
                            </button>

                            <!-- Botão Filtros -->
                            <button @click="showFilters = !showFilters"
                                class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 overflow-hidden rounded-xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 shadow-md hover:shadow-lg"
                                :class="{'from-slate-700 to-slate-800': showFilters}">
                                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                                <i class="bi bi-funnel-fill text-white relative group-hover:scale-110 transition-transform duration-300"></i>
                                <span class="text-sm font-bold text-white hidden sm:inline">Filtros</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/cashbook-header.blade.php ENDPATH**/ ?>