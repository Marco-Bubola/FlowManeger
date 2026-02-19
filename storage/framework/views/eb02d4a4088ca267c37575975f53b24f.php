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

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-green-50/90 to-emerald-50/80 dark:from-slate-800/90 dark:via-green-900/30 dark:to-emerald-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-green-400/20 via-emerald-400/20 to-teal-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-400/10 via-green-400/10 to-emerald-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                <!-- Botão voltar melhorado -->
                <a href="<?php echo e(route('sales.show', $sale->id)); ?>"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-green-50 dark:from-slate-800 dark:to-slate-700 hover:from-green-50 hover:to-emerald-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>

                <!-- Ícone principal e título -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 via-emerald-500 to-teal-500 rounded-2xl shadow-xl shadow-green-500/25">
                    <i class="bi bi-cash-coin text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-green-700 to-emerald-700 dark:from-slate-100 dark:via-green-300 dark:to-emerald-300 bg-clip-text text-transparent">
                        Adicionar Pagamentos
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">
                        💳 Venda #<?php echo e($sale->id); ?> • Cliente: <?php echo e($sale->client->name ?? 'Cliente não informado'); ?>

                    </p>
                </div>
            </div>

            <!-- Ações do header -->
            <div class="flex items-center gap-4">
                <button type="button"
                        wire:click="addPayments"
                        class="group relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-br from-green-500 via-emerald-500 to-teal-500 hover:from-green-600 hover:via-emerald-600 hover:to-teal-600 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-green-500/25 border border-white/20">
                    <i class="bi bi-check-circle mr-3 text-xl group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="text-lg">Salvar Pagamentos</span>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/add-payments-header.blade.php ENDPATH**/ ?>