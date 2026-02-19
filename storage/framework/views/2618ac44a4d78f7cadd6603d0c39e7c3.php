<div class="w-full bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800" style="min-height: 100vh; display: flex; flex-direction: column;">
    <!-- Header Moderno -->
    <?php if (isset($component)) { $__componentOriginalcfcab9726b2315e1e83548084507bfeb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcfcab9726b2315e1e83548084507bfeb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sales-header','data' => ['title' => '📊 Dashboard de Metas','description' => 'Acompanhe o progresso das suas metas e objetivos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sales-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => '📊 Dashboard de Metas','description' => 'Acompanhe o progresso das suas metas e objetivos']); ?>
         <?php $__env->slot('breadcrumb', null, []); ?> 
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    <i class="bi bi-bullseye mr-1"></i>Metas e Objetivos
                </span>
            </div>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('goals.board', ['boardId' => $boards->first()->id ?? 1])); ?>"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg shadow-lg transition-all duration-200">
                <i class="bi bi-kanban"></i>
                <span class="font-medium">Abrir Quadro</span>
            </a>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- KPIs Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Total de Metas','value' => $stats['total'],'icon' => 'bi bi-bullseye','color' => 'blue','subtitle' => $stats['pending'] . ' pendentes']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total de Metas','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['total']),'icon' => 'bi bi-bullseye','color' => 'blue','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['pending'] . ' pendentes')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Em Andamento','value' => $stats['active'],'icon' => 'bi bi-play-circle','color' => 'green','subtitle' => 'Meta: ' . $stats['total']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Em Andamento','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['active']),'icon' => 'bi bi-play-circle','color' => 'green','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Meta: ' . $stats['total'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Concluídas','value' => $stats['completed'],'icon' => 'bi bi-check-circle','color' => 'purple','subtitle' => '🎉 Parabéns!']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Concluídas','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['completed']),'icon' => 'bi bi-check-circle','color' => 'purple','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('🎉 Parabéns!')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Taxa de Conclusão','value' => number_format($stats['avgProgress'], 1) . '%','icon' => 'bi bi-graph-up-arrow','color' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Taxa de Conclusão','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stats['avgProgress'], 1) . '%'),'icon' => 'bi bi-graph-up-arrow','color' => 'orange']); ?>
                <?php if (isset($component)) { $__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-ring','data' => ['percentage' => $stats['avgProgress'],'size' => 'sm','color' => 'orange','label' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percentage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['avgProgress']),'size' => 'sm','color' => 'orange','label' => '']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c)): ?>
<?php $attributes = $__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c; ?>
<?php unset($__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c)): ?>
<?php $component = $__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c; ?>
<?php unset($__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
        </div>

        <!-- Achievements Section -->
        <!--[if BLOCK]><![endif]--><?php if($achievementStats['unlocked'] > 0): ?>
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 shadow-xl text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="bi bi-trophy-fill text-yellow-300"></i>
                    Conquistas de Metas
                </h3>
                <a href="<?php echo e(route('achievements.index')); ?>" class="text-sm bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                    Ver Todas
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginal17b477f0bef4a0947da7fdd6ca140922 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.trophy-badge','data' => ['rarity' => 'bronze','size' => 'md','count' => $achievementStats['by_rarity']['bronze'] ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('trophy-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rarity' => 'bronze','size' => 'md','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievementStats['by_rarity']['bronze'] ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $attributes = $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $component = $__componentOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
                        <div>
                            <p class="text-2xl font-bold"><?php echo e($achievementStats['by_rarity']['bronze'] ?? 0); ?></p>
                            <p class="text-sm text-purple-200">Bronze</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginal17b477f0bef4a0947da7fdd6ca140922 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.trophy-badge','data' => ['rarity' => 'silver','size' => 'md','count' => $achievementStats['by_rarity']['silver'] ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('trophy-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rarity' => 'silver','size' => 'md','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievementStats['by_rarity']['silver'] ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $attributes = $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $component = $__componentOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
                        <div>
                            <p class="text-2xl font-bold"><?php echo e($achievementStats['by_rarity']['silver'] ?? 0); ?></p>
                            <p class="text-sm text-purple-200">Prata</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginal17b477f0bef4a0947da7fdd6ca140922 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.trophy-badge','data' => ['rarity' => 'gold','size' => 'md','count' => $achievementStats['by_rarity']['gold'] ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('trophy-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rarity' => 'gold','size' => 'md','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievementStats['by_rarity']['gold'] ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $attributes = $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__attributesOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922)): ?>
<?php $component = $__componentOriginal17b477f0bef4a0947da7fdd6ca140922; ?>
<?php unset($__componentOriginal17b477f0bef4a0947da7fdd6ca140922); ?>
<?php endif; ?>
                        <div>
                            <p class="text-2xl font-bold"><?php echo e($achievementStats['by_rarity']['gold'] ?? 0); ?></p>
                            <p class="text-sm text-purple-200">Ouro</p>
                        </div>
                    </div>
                </div>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($recentAchievements->count() > 0): ?>
            <div class="mt-4 pt-4 border-t border-white/20">
                <p class="text-sm text-purple-200 mb-3">Últimas Conquistas:</p>
                <div class="flex gap-2">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recentAchievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 flex items-center gap-2">
                            <i class="<?php echo e($achievement->achievement->icon); ?> text-xl"></i>
                            <span class="text-sm"><?php echo e($achievement->achievement->name); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Alertas e Boards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Coluna Esquerda: Metas Urgentes -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Alertas -->
                <!--[if BLOCK]><![endif]--><?php if($stats['delayed'] > 0 || $stats['upcoming'] > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!--[if BLOCK]><![endif]--><?php if($stats['delayed'] > 0): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-red-500 rounded-lg">
                                <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-700 dark:text-red-400"><?php echo e($stats['delayed']); ?></p>
                                <p class="text-sm text-red-600 dark:text-red-300">Metas atrasadas</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!--[if BLOCK]><![endif]--><?php if($stats['upcoming'] > 0): ?>
                    <div class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-amber-500 rounded-lg">
                                <i class="bi bi-clock-history text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-amber-700 dark:text-amber-400"><?php echo e($stats['upcoming']); ?></p>
                                <p class="text-sm text-amber-600 dark:text-amber-300">Vencendo em 7 dias</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!-- Metas Urgentes -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-fire text-red-500"></i>
                            Metas Urgentes
                        </h3>
                        <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-2 py-1 rounded-full">
                            <?php echo e(count($urgentGoals)); ?> metas
                        </span>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $urgentGoals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="group bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl p-4 transition-all duration-200 cursor-pointer border-l-4"
                             style="border-color: <?php echo e($goal['board_color'] ?? '#6B7280'); ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-slate-900 dark:text-white"><?php echo e($goal['title']); ?></h4>
                                        <!--[if BLOCK]><![endif]--><?php if($goal['is_atrasada']): ?>
                                        <span class="text-xs bg-red-500 text-white px-2 py-0.5 rounded-full">Atrasada</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-2">
                                        <?php echo e($goal['board']); ?> • <?php echo e($goal['list']); ?>

                                    </p>

                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2 mb-2">
                                        <div class="h-2 rounded-full transition-all duration-500 <?php echo e($goal['progresso'] < 30 ? 'bg-red-500' : ($goal['progresso'] < 70 ? 'bg-amber-500' : 'bg-green-500')); ?>"
                                             style="width: <?php echo e($goal['progresso']); ?>%">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-calendar3"></i>
                                            <?php echo e(\Carbon\Carbon::parse($goal['data_vencimento'])->format('d/m/Y')); ?>

                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="bi bi-graph-up"></i>
                                            <?php echo e($goal['progresso']); ?>%
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right ml-4">
                                    <!--[if BLOCK]><![endif]--><?php if($goal['days_left'] < 0): ?>
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                        <?php echo e(abs($goal['days_left'])); ?> dias atrás
                                    </span>
                                    <?php else: ?>
                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                        <?php echo e($goal['days_left']); ?> dias
                                    </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8">
                            <i class="bi bi-check-circle text-green-500 text-4xl mb-2"></i>
                            <p class="text-slate-600 dark:text-slate-400">Nenhuma meta urgente no momento! 👏</p>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!-- Gráfico de Progresso -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-bar-chart text-blue-500"></i>
                        Distribuição de Progresso
                    </h3>
                    <div class="grid grid-cols-5 gap-2">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['0-25', '26-50', '51-75', '76-99', '100']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-center">
                            <div class="h-32 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-end justify-center p-2 mb-2">
                                <div class="w-full <?php echo e($range === '100' ? 'bg-green-500' : 'bg-blue-500'); ?> rounded-t transition-all duration-500"
                                     style="height: <?php echo e(($progressStats[$range] / max(1, $stats['active'])) * 100); ?>%">
                                </div>
                            </div>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400"><?php echo e($range); ?>%</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo e($progressStats[$range]); ?></p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>

            <!-- Coluna Direita -->
            <div class="space-y-6">

                <!-- Quadros/Boards -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-kanban text-purple-500"></i>
                        Meus Quadros
                    </h3>

                    <div class="space-y-3">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $boards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $board): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('goals.board', ['boardId' => $board['id']])); ?>"
                           class="block group bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl p-4 transition-all duration-200"
                           style="border-left: 4px solid <?php echo e($board['color']); ?>">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <i class="<?php echo e($board['icon']); ?> text-xl" style="color: <?php echo e($board['color']); ?>"></i>
                                    <h4 class="font-semibold text-slate-900 dark:text-white"><?php echo e($board['name']); ?></h4>
                                    <!--[if BLOCK]><![endif]--><?php if($board['is_favorite']): ?>
                                    <i class="bi bi-star-fill text-amber-400 text-xs"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-slate-600 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <i class="bi bi-list-task"></i>
                                    <?php echo e($board['lists_count']); ?> listas
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <?php echo e($board['active_goals']); ?> metas
                                </span>
                            </div>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-center text-slate-500 dark:text-slate-400 py-4">Nenhum quadro criado</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!-- Metas por Período -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-calendar-range text-indigo-500"></i>
                        Por Período
                    </h3>
                    <div class="space-y-2">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $goalsByPeriodo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $periodo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?php echo e($periodo['label']); ?></span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-full">
                                    <?php echo e($periodo['count']); ?>

                                </span>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    <?php echo e(number_format($periodo['avgProgress'], 0)); ?>%
                                </span>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Sem dados</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!-- Atividades Recentes -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history text-slate-500"></i>
                        Atividades Recentes
                    </h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start gap-3 text-sm">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                                 style="background-color: <?php echo e($activity['color']); ?>20">
                                <i class="<?php echo e($activity['icon']); ?>" style="color: <?php echo e($activity['color']); ?>"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    <span class="font-medium text-slate-900 dark:text-white"><?php echo e($activity['user_name']); ?></span>
                                    <?php echo e($activity['description']); ?>

                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1"><?php echo e($activity['time_ago']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Nenhuma atividade</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs de Hábitos Diários -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Hábitos Ativos','value' => $habitStats['total_habits'] ?? 0,'icon' => 'bi bi-list-check','color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hábitos Ativos','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($habitStats['total_habits'] ?? 0),'icon' => 'bi bi-list-check','color' => 'purple']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Concluídos Hoje','value' => ($habitStats['completed_today'] ?? 0) . '/' . ($habitStats['total_habits'] ?? 0),'icon' => 'bi bi-check-circle','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Concluídos Hoje','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($habitStats['completed_today'] ?? 0) . '/' . ($habitStats['total_habits'] ?? 0)),'icon' => 'bi bi-check-circle','color' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Pendentes Hoje','value' => $habitStats['pending_today'] ?? 0,'icon' => 'bi bi-hourglass-split','color' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pendentes Hoje','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($habitStats['pending_today'] ?? 0),'icon' => 'bi bi-hourglass-split','color' => 'amber']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Taxa de Conclusão','value' => ($habitStats['completion_rate_today'] ?? 0) . '%','icon' => 'bi bi-graph-up','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Taxa de Conclusão','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($habitStats['completion_rate_today'] ?? 0) . '%'),'icon' => 'bi bi-graph-up','color' => 'blue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
        </div>
        <!-- Lista de Hábitos -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-calendar-check text-purple-500"></i>
                Hábitos Diários
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $habits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $habit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900 dark:to-indigo-900 rounded-xl p-4 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <i class="<?php echo e($habit->icon); ?> text-2xl" style="color: <?php echo e($habit->color); ?>"></i>
                        <span class="font-bold text-lg"><?php echo e($habit->name); ?></span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400"><?php echo e($habit->description); ?></p>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-full">Frequência: <?php echo e($habit->goal_frequency); ?></span>
                        <span class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-1 rounded-full">Streak: <?php echo e($habit->streak->current_streak ?? 0); ?></span>
                        <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2 py-1 rounded-full">Taxa: <?php echo e($habit->completion_rate ?? 0); ?>%</span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">Nenhum hábito cadastrado</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/goals/goals-dashboard.blade.php ENDPATH**/ ?>