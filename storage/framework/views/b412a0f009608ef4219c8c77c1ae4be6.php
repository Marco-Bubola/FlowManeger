<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'showBack' => false,
    'showNext' => false,
    'showSave' => false,
    'showCancel' => false,
    'backAction' => '',
    'nextAction' => '',
    'saveAction' => '',
    'cancelRoute' => '',
    'backText' => 'Voltar',
    'nextText' => 'Próxima Etapa',
    'saveText' => 'Salvar',
    'cancelText' => 'Cancelar',
    'loading' => false,
    'loadingText' => 'Processando...'
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
    'showBack' => false,
    'showNext' => false,
    'showSave' => false,
    'showCancel' => false,
    'backAction' => '',
    'nextAction' => '',
    'saveAction' => '',
    'cancelRoute' => '',
    'backText' => 'Voltar',
    'nextText' => 'Próxima Etapa',
    'saveText' => 'Salvar',
    'cancelText' => 'Cancelar',
    'loading' => false,
    'loadingText' => 'Processando...'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!-- Container dos Botões Modernizado -->
<div class="relative flex flex-col sm:flex-row gap-6 justify-center items-center p-8 bg-gradient-to-r from-white/60 via-slate-50/40 to-white/60 dark:from-slate-800/60 dark:via-slate-900/40 dark:to-slate-800/60 backdrop-blur-xl rounded-3xl border border-white/20 dark:border-slate-700/50 shadow-2xl">
    <!-- Efeito de brilho de fundo -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-transparent to-purple-500/5 rounded-3xl"></div>

    <!-- Grupo de Botões Secundários -->
    <div class="relative flex gap-4 z-10">
        <!--[if BLOCK]><![endif]--><?php if($showBack): ?>
            <button type="button"
                    <?php if($backAction): ?> wire:click="<?php echo e($backAction); ?>" <?php endif; ?>
                    class="group relative inline-flex items-center px-8 py-4
                           bg-gradient-to-r from-slate-100 to-slate-200 hover:from-slate-200 hover:to-slate-300
                           dark:from-slate-700 dark:to-slate-600 dark:hover:from-slate-600 dark:hover:to-slate-500
                           text-slate-700 dark:text-slate-200 font-bold rounded-2xl
                           transition-all duration-300 shadow-lg hover:shadow-xl
                           transform hover:scale-105 hover:-translate-y-1
                           border border-slate-200/50 dark:border-slate-600/50
                           backdrop-blur-sm">
                <!-- Efeito de onda -->
                <div class="absolute inset-0 bg-gradient-to-r from-slate-400/10 to-slate-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                <i class="bi bi-arrow-left mr-3 text-xl group-hover:transform group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span class="relative z-10"><?php echo e($backText); ?></span>

                <!-- Ring de hover -->
                <div class="absolute -inset-1 bg-gradient-to-r from-slate-300 to-slate-400 rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-sm"></div>
            </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($showCancel): ?>
            <!--[if BLOCK]><![endif]--><?php if($cancelRoute): ?>
                <a href="<?php echo e($cancelRoute); ?>"
                   class="group relative inline-flex items-center px-8 py-4
                          bg-gradient-to-r from-red-100 to-rose-100 hover:from-red-200 hover:to-rose-200
                          dark:from-red-900/30 dark:to-rose-900/30 dark:hover:from-red-800/40 dark:hover:to-rose-800/40
                          text-red-700 dark:text-red-300 font-bold rounded-2xl
                          transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-red-500/20
                          transform hover:scale-105 hover:-translate-y-1
                          border border-red-200/50 dark:border-red-800/50
                          backdrop-blur-sm">
                    <!-- Efeito de onda -->
                    <div class="absolute inset-0 bg-gradient-to-r from-red-400/10 to-rose-400/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <i class="bi bi-x-circle mr-3 text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                    <span class="relative z-10"><?php echo e($cancelText); ?></span>

                    <!-- Ring de hover -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-red-300 to-rose-300 rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-sm"></div>
                </a>
            <?php else: ?>
                <button type="button"
                        class="group relative inline-flex items-center px-8 py-4
                               bg-gradient-to-r from-red-100 to-rose-100 hover:from-red-200 hover:to-rose-200
                               dark:from-red-900/30 dark:to-rose-900/30 dark:hover:from-red-800/40 dark:hover:to-rose-800/40
                               text-red-700 dark:text-red-300 font-bold rounded-2xl
                               transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-red-500/20
                               transform hover:scale-105 hover:-translate-y-1
                               border border-red-200/50 dark:border-red-800/50
                               backdrop-blur-sm">
                    <!-- Efeito de onda -->
                    <div class="absolute inset-0 bg-gradient-to-r from-red-400/10 to-rose-400/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <i class="bi bi-x-circle mr-3 text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                    <span class="relative z-10"><?php echo e($cancelText); ?></span>

                    <!-- Ring de hover -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-red-300 to-rose-300 rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-sm"></div>
                </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- Separador Visual -->
    <!--[if BLOCK]><![endif]--><?php if(($showBack || $showCancel) && ($showNext || $showSave)): ?>
        <div class="hidden sm:block w-px h-12 bg-gradient-to-b from-transparent via-slate-300 dark:via-slate-600 to-transparent opacity-50"></div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Grupo de Botões Principais -->
    <div class="relative flex gap-4 z-10">
        <!--[if BLOCK]><![endif]--><?php if($showNext): ?>
            <button type="button"
                    <?php if($nextAction): ?> wire:click="<?php echo e($nextAction); ?>" <?php endif; ?>
                    class="group relative inline-flex items-center px-10 py-4
                           bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500
                           hover:from-blue-600 hover:via-indigo-600 hover:to-purple-600
                           text-white font-bold rounded-2xl
                           transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-blue-500/40
                           transform hover:scale-105 hover:-translate-y-1
                           border border-blue-400/50
                           backdrop-blur-sm overflow-hidden"
                    wire:loading.attr="disabled">

                <!-- Efeito de brilho animado -->
                <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-700 transform translate-x-[-100%] group-hover:translate-x-[100%]"></div>

                <span wire:loading.remove class="relative flex items-center z-10">
                    <i class="bi bi-arrow-right mr-3 text-xl group-hover:transform group-hover:translate-x-1 transition-transform duration-300"></i>
                    <?php echo e($nextText); ?>

                </span>
                <span wire:loading class="relative flex items-center z-10">
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-3"></div>
                    <?php echo e($loadingText); ?>

                </span>

                <!-- Partículas flutuantes -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute w-1 h-1 bg-white/40 rounded-full group-hover:animate-ping" style="top: 20%; left: 20%; animation-delay: 0.2s;"></div>
                    <div class="absolute w-1 h-1 bg-white/60 rounded-full group-hover:animate-ping" style="top: 60%; right: 30%; animation-delay: 0.5s;"></div>
                </div>

                <!-- Ring de hover -->
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 rounded-2xl opacity-0 group-hover:opacity-30 transition-opacity duration-300 blur-sm"></div>
            </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($showSave): ?>
            <button type="button"
                    <?php if($saveAction): ?> wire:click="<?php echo e($saveAction); ?>" <?php endif; ?>
                    class="group relative inline-flex items-center px-10 py-4
                           bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500
                           hover:from-emerald-600 hover:via-green-600 hover:to-teal-600
                           text-white font-bold rounded-2xl
                           transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-emerald-500/40
                           transform hover:scale-105 hover:-translate-y-1
                           border border-emerald-400/50
                           backdrop-blur-sm overflow-hidden"
                    wire:loading.attr="disabled">

                <!-- Efeito de brilho animado -->
                <div class="absolute inset-0 bg-gradient-to-r from-white/20 via-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-700 transform translate-x-[-100%] group-hover:translate-x-[100%]"></div>

                <span wire:loading.remove class="relative flex items-center z-10">
                    <i class="bi bi-check-circle mr-3 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                    <?php echo e($saveText); ?>

                </span>
                <span wire:loading class="relative flex items-center z-10">
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-3"></div>
                    <?php echo e($loadingText); ?>

                </span>

                <!-- Partículas de sucesso -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute w-1 h-1 bg-white/50 rounded-full group-hover:animate-ping" style="top: 30%; left: 25%; animation-delay: 0.1s;"></div>
                    <div class="absolute w-1 h-1 bg-white/70 rounded-full group-hover:animate-ping" style="top: 70%; right: 25%; animation-delay: 0.4s;"></div>
                    <div class="absolute w-1 h-1 bg-white/40 rounded-full group-hover:animate-ping" style="bottom: 40%; left: 60%; animation-delay: 0.7s;"></div>
                </div>

                <!-- Ring de hover -->
                <div class="absolute -inset-1 bg-gradient-to-r from-emerald-400 via-green-400 to-teal-400 rounded-2xl opacity-0 group-hover:opacity-30 transition-opacity duration-300 blur-sm"></div>
            </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- Efeito de borda brilhante -->
    <div class="absolute inset-0 rounded-3xl bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-30 pointer-events-none"></div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/action-buttons-new.blade.php ENDPATH**/ ?>