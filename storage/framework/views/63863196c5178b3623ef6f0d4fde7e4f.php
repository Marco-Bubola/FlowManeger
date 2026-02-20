<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'image',
    'id' => 'image',
    'wireModel' => 'image',
    'acceptedTypes' => 'image/*',
    'maxSize' => '2MB',
    'supportedFormats' => 'PNG, JPG, JPEG',
    'title' => 'Adicionar Imagem',
    'description' => 'Clique para selecionar ou arraste e solte sua imagem aqui',
    'existingImage' => null,
    'newImage' => null,
    'width' => 'w-full',
    'height' => 'h-full',
    'showPreview' => true
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
    'name' => 'image',
    'id' => 'image',
    'wireModel' => 'image',
    'acceptedTypes' => 'image/*',
    'maxSize' => '2MB',
    'supportedFormats' => 'PNG, JPG, JPEG',
    'title' => 'Adicionar Imagem',
    'description' => 'Clique para selecionar ou arraste e solte sua imagem aqui',
    'existingImage' => null,
    'newImage' => null,
    'width' => 'w-full',
    'height' => 'h-full',
    'showPreview' => true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="flex items-center justify-center <?php echo e($width); ?>">
    <label for="<?php echo e($id); ?>" class="group relative flex flex-col items-center justify-center <?php echo e($width); ?> <?php echo e($height); ?> border-3 border-dashed border-slate-300 dark:border-slate-600 rounded-3xl cursor-pointer
           bg-gradient-to-br from-white/80 via-blue-50/50 to-indigo-50/30
           dark:from-slate-800/80 dark:via-blue-900/20 dark:to-indigo-900/10
           hover:from-blue-50/80 hover:via-indigo-50/60 hover:to-purple-50/40
           dark:hover:from-slate-700/80 dark:hover:via-blue-900/30 dark:hover:to-indigo-900/20
           backdrop-blur-xl transition-all duration-500 ease-out
           hover:border-blue-400 dark:hover:border-blue-500
           hover:shadow-2xl hover:shadow-blue-500/20
           transform hover:scale-[1.02]">

        <!-- Efeito de brilho animado -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 rounded-3xl animate-pulse"></div>

        <!-- Partículas flutuantes -->
        <div class="absolute inset-0 overflow-hidden rounded-3xl">
            <div class="absolute w-2 h-2 bg-blue-400/30 rounded-full animate-float" style="top: 20%; left: 15%; animation-delay: 0.2s;"></div>
            <div class="absolute w-1 h-1 bg-purple-400/40 rounded-full animate-float" style="top: 60%; right: 20%; animation-delay: 0.8s;"></div>
            <div class="absolute w-3 h-3 bg-indigo-300/20 rounded-full animate-float" style="bottom: 30%; left: 30%; animation-delay: 1.2s;"></div>
        </div>

        <div class="relative flex flex-col items-center justify-center px-2 py-10 z-10">
            <!--[if BLOCK]><![endif]--><?php if(($newImage && $showPreview) || ($existingImage && $showPreview && !$newImage)): ?>
                <!-- Preview da Imagem com Efeitos -->
                <div class="relative group/image w-full h-full flex items-center justify-center">
                    <!--[if BLOCK]><![endif]--><?php if($newImage): ?>
                        <!-- Nova imagem selecionada -->
                        <img src="<?php echo e($newImage->temporaryUrl()); ?>" class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl group-hover:scale-105 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    <?php elseif(is_string($existingImage)): ?>
                        <!-- Imagem existente -->
                        <img src="<?php echo e($existingImage); ?>" class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl group-hover:scale-105 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    <?php else: ?>
                        <!-- Imagem existente (objeto) -->
                        <img src="<?php echo e($existingImage->temporaryUrl()); ?>" class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl group-hover:scale-105 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    

                    <!-- Overlay com ícone de edição -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-pencil-square text-white text-3xl"></i>
                    </div>
                </div>

                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-center bg-white/90 dark:bg-slate-800/90 backdrop-blur-md px-6 py-3 rounded-xl shadow-lg">
                    <p class="text-sm font-bold bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-400 bg-clip-text text-transparent flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle-fill text-emerald-500 text-lg animate-pulse"></i>
                        Clique para alterar
                    </p>
                </div>
            <?php else: ?>
                <!-- Estado vazio com animações -->
                <div class="text-center space-y-8">
                    <div class="relative">
                        <!-- Ícone principal com efeitos -->
                        <div class="relative">
                            <i class="bi bi-cloud-upload text-8xl text-slate-300 dark:text-slate-600 group-hover:text-blue-400 dark:group-hover:text-blue-500 transition-all duration-500 transform group-hover:scale-110"></i>

                            <!-- Ícone de + flutuante -->
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center shadow-lg animate-bounce group-hover:scale-125 transition-transform duration-300">
                                <i class="bi bi-plus text-white text-lg font-bold"></i>
                            </div>

                            <!-- Círculos decorativos -->
                            <div class="absolute -top-4 -left-4 w-6 h-6 bg-blue-400/30 rounded-full animate-pulse"></div>
                            <div class="absolute -bottom-6 left-8 w-4 h-4 bg-purple-400/40 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-700 via-blue-600 to-indigo-600 dark:from-slate-300 dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent group-hover:from-blue-600 group-hover:to-purple-600 transition-all duration-500">
                            <i class="bi bi-images mr-3"></i>
                            <?php echo e($title); ?>

                        </h3>
                        <p class="text-lg text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                            <span class="font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"><?php echo e($description); ?></span>
                        </p>

                        <!-- Tags informativos melhorados -->
                        <div class="flex items-center justify-center space-x-6 pt-4">
                            <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                <i class="bi bi-file-earmark-image text-blue-500 mr-3 text-lg"></i>
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300"><?php echo e($supportedFormats); ?></span>
                            </div>
                            <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                <i class="bi bi-hdd text-emerald-500 mr-3 text-lg"></i>
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Máx. <?php echo e($maxSize); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <input wire:model="<?php echo e($wireModel); ?>" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>" type="file" class="hidden" accept="<?php echo e($acceptedTypes); ?>">
    </label>
</div>

<!--[if BLOCK]><![endif]--><?php $__errorArgs = [$wireModel];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
<div class="flex items-center justify-center p-4 bg-red-50/80 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl mt-6 backdrop-blur-sm">
    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-4 text-xl"></i>
    <p class="text-red-600 dark:text-red-400 font-bold text-lg"><?php echo e($message); ?></p>
</div>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        33% { transform: translateY(-10px) rotate(1deg); }
        66% { transform: translateY(-5px) rotate(-1deg); }
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/image-upload.blade.php ENDPATH**/ ?>