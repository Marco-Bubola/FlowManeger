<div class="min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-900 dark:via-purple-950 dark:to-indigo-950">
    <!-- Header -->
    <?php if (isset($component)) { $__componentOriginalcfcab9726b2315e1e83548084507bfeb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcfcab9726b2315e1e83548084507bfeb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sales-header','data' => ['title' => 'Conquistas','breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Conquistas', 'url' => null]
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sales-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Conquistas','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Conquistas', 'url' => null]
        ])]); ?>
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

    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Header -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Total de Conquistas','value' => $stats['unlocked_count'] . '/' . $stats['total_count'],'icon' => 'bi bi-trophy-fill','color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total de Conquistas','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['unlocked_count'] . '/' . $stats['total_count']),'icon' => 'bi bi-trophy-fill','color' => 'purple']); ?>
                <div class="flex items-center gap-4">
                    <?php if (isset($component)) { $__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-ring','data' => ['percentage' => $stats['completion_rate'],'size' => 'sm','color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percentage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['completion_rate']),'size' => 'sm','color' => 'purple']); ?>
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
                    <span class="text-sm text-slate-600 dark:text-slate-400">
                        <?php echo e(round($stats['completion_rate'])); ?>% completo
                    </span>
                </div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Pontos Totais','value' => $stats['total_points'],'icon' => 'bi bi-star-fill','color' => 'orange','subtitle' => 'De ' . \App\Models\Achievement::sum('points') . ' possíveis']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pontos Totais','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['total_points']),'icon' => 'bi bi-star-fill','color' => 'orange','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('De ' . \App\Models\Achievement::sum('points') . ' possíveis')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Troféus de Bronze','value' => $stats['by_rarity']['bronze'] ?? 0,'icon' => 'bi bi-trophy-fill','color' => 'orange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Troféus de Bronze','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['by_rarity']['bronze'] ?? 0),'icon' => 'bi bi-trophy-fill','color' => 'orange']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Troféus de Prata','value' => $stats['by_rarity']['silver'] ?? 0,'icon' => 'bi bi-trophy-fill','color' => 'cyan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Troféus de Prata','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['by_rarity']['silver'] ?? 0),'icon' => 'bi bi-trophy-fill','color' => 'cyan']); ?>
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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Troféus de Ouro','value' => $stats['by_rarity']['gold'] ?? 0,'icon' => 'bi bi-trophy-fill','color' => 'pink']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Troféus de Ouro','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['by_rarity']['gold'] ?? 0),'icon' => 'bi bi-trophy-fill','color' => 'pink']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['title' => 'Troféus de Platina','value' => $stats['by_rarity']['platinum'] ?? 0,'icon' => 'bi bi-gem','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Troféus de Platina','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['by_rarity']['platinum'] ?? 0),'icon' => 'bi bi-gem','color' => 'blue']); ?>
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

            <!-- Troféu animado para o rarity mais alto -->
            <div class="lg:col-span-2 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-200 mb-1">Maior Raridade Desbloqueada</p>
                        <h3 class="text-2xl font-bold capitalize">
                            <?php if(($stats['by_rarity']['platinum'] ?? 0) > 0): ?>
                                Platina
                            <?php elseif(($stats['by_rarity']['gold'] ?? 0) > 0): ?>
                                Ouro
                            <?php elseif(($stats['by_rarity']['silver'] ?? 0) > 0): ?>
                                Prata
                            <?php else: ?>
                                Bronze
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </h3>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal17b477f0bef4a0947da7fdd6ca140922 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17b477f0bef4a0947da7fdd6ca140922 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.trophy-badge','data' => ['rarity' => ($stats['by_rarity']['platinum'] ?? 0) > 0 ? 'platinum' : (($stats['by_rarity']['gold'] ?? 0) > 0 ? 'gold' : (($stats['by_rarity']['silver'] ?? 0) > 0 ? 'silver' : 'bronze')),'size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('trophy-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rarity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($stats['by_rarity']['platinum'] ?? 0) > 0 ? 'platinum' : (($stats['by_rarity']['gold'] ?? 0) > 0 ? 'gold' : (($stats['by_rarity']['silver'] ?? 0) > 0 ? 'silver' : 'bronze'))),'size' => 'xl']); ?>
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
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-8 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-wrap gap-4">
                <!-- Filtro de Raridade -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-filter mr-2"></i>Raridade
                    </label>
                    <select wire:model.live="filterRarity" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="all">Todas</option>
                        <option value="bronze">Bronze</option>
                        <option value="silver">Prata</option>
                        <option value="gold">Ouro</option>
                        <option value="platinum">Platina</option>
                    </select>
                </div>

                <!-- Filtro de Categoria -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-tag mr-2"></i>Categoria
                    </label>
                    <select wire:model.live="filterCategory" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="all">Todas</option>
                        <option value="habits">Hábitos</option>
                        <option value="goals">Metas</option>
                        <option value="streak">Sequências</option>
                        <option value="general">Geral</option>
                    </select>
                </div>

                <!-- Ordenação -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-sort-down mr-2"></i>Ordenar por
                    </label>
                    <select wire:model.live="sortBy" class="w-full px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                        <option value="order">Padrão</option>
                        <option value="points">Pontos</option>
                        <option value="rarity">Raridade</option>
                        <option value="name">Nome</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Grid de Conquistas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $unlocked = in_array($achievement->id, $unlockedIds);
                    $userAchievement = $userAchievements->get($achievement->id);
                    $unlockedAt = $userAchievement ? $userAchievement->unlocked_at : null;
                ?>
                <?php if (isset($component)) { $__componentOriginale3a843f07e17c4baeda8a7fe86d8befc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3a843f07e17c4baeda8a7fe86d8befc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.achievement-card','data' => ['achievement' => $achievement,'unlocked' => $unlocked,'unlockedAt' => $unlockedAt]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('achievement-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['achievement' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement),'unlocked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unlocked),'unlockedAt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unlockedAt)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3a843f07e17c4baeda8a7fe86d8befc)): ?>
<?php $attributes = $__attributesOriginale3a843f07e17c4baeda8a7fe86d8befc; ?>
<?php unset($__attributesOriginale3a843f07e17c4baeda8a7fe86d8befc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3a843f07e17c4baeda8a7fe86d8befc)): ?>
<?php $component = $__componentOriginale3a843f07e17c4baeda8a7fe86d8befc; ?>
<?php unset($__componentOriginale3a843f07e17c4baeda8a7fe86d8befc); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!--[if BLOCK]><![endif]--><?php if($achievements->isEmpty()): ?>
            <div class="text-center py-16">
                <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="bi bi-trophy text-6xl text-slate-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Nenhuma conquista encontrada</h3>
                <p class="text-slate-600 dark:text-slate-400">Tente ajustar os filtros</p>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/achievements/achievements-page.blade.php ENDPATH**/ ?>