<div class="w-full" x-data="{
    showFilters: false,
    fullHd: false,
    ultra: false,
    showDeleteModal: <?php if ((object) ('showDeleteModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDeleteModal'->value()); ?>')<?php echo e('showDeleteModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDeleteModal'); ?>')<?php endif; ?>.live,
    initResponsiveWatcher() {
        const mq = window.matchMedia('(min-width: 1850px)');
        const mqUltra = window.matchMedia('(min-width: 2400px)');

        const sync = () => {
            this.fullHd = mq.matches;
            if (typeof $wire !== 'undefined') {
                $wire.set('fullHdLayout', mq.matches);
            }
        };

        const syncUltra = () => {
            this.ultra = mqUltra.matches;
            if (typeof $wire !== 'undefined') {
                $wire.set('ultraWindClient', mqUltra.matches);
            }
        };

        sync();
        syncUltra();

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', sync);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(sync);
        }

        if (typeof mqUltra.addEventListener === 'function') {
            mqUltra.addEventListener('change', syncUltra);
        } else if (typeof mqUltra.addListener === 'function') {
            mqUltra.addListener(syncUltra);
        }
    }
}" x-init="initResponsiveWatcher()" x-bind:data-ultrawind="ultra ? 'true' : 'false'"
    x-bind:data-full-hd="fullHd ? 'true' : 'false'">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-emerald-50/90 to-teal-50/80 dark:from-slate-800/90 dark:via-emerald-900/30 dark:to-teal-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-teal-400/20 to-green-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-emerald-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

        <div class="relative px-4 py-3">
                <div class="flex items-center justify-between gap-6 mb-6">
                    <div class="flex items-center gap-6">
                        <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 via-teal-500 to-green-500 rounded-2xl shadow-xl shadow-emerald-500/25">
                            <i class="bi bi-piggy-bank text-white text-3xl"></i>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                        </div>

                        <div class="space-y-2">
                            <!-- Breadcrumb -->
                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                                <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <i class="fas fa-home mr-1"></i>Dashboard
                                </a>
                                <i class="fas fa-chevron-right text-xs"></i>
                                <span class="text-slate-800 dark:text-slate-200 font-medium">
                                    <i class="fas fa-handshake mr-1"></i>Consórcios
                                </span>
                            </div>

                            <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                                Consórcios
                            </h1>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                                    <i class="bi bi-check-circle text-emerald-600 dark:text-emerald-400"></i>
                                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300"><?php echo e($totalActive); ?> ativos</span>
                                </div>
                                <!--[if BLOCK]><![endif]--><?php if($totalCompleted > 0): ?>
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                                    <i class="bi bi-trophy text-blue-600 dark:text-blue-400"></i>
                                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-300"><?php echo e($totalCompleted); ?> concluídos</span>
                                </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php if($totalParticipants > 0): ?>
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl border border-purple-200 dark:border-purple-700">
                                    <i class="bi bi-people text-purple-600 dark:text-purple-400"></i>
                                    <span class="text-sm font-semibold text-purple-700 dark:text-purple-300"><?php echo e($totalParticipants); ?> participantes</span>
                                </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative group w-96">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar consórcios..."
                                class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all duration-200 shadow-md text-sm">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-search text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                            </div>
                            <button wire:click="$set('search', '')" x-show="$wire.search && $wire.search.length > 0"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg transition-all duration-200">
                                <i class="bi bi-x text-xs"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-2 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl shadow-md">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-piggy-bank text-white text-sm"></i>
                            </div>
                            <div class="text-sm">
                                <span class="font-bold text-slate-800 dark:text-slate-200"><?php echo e($consortiums->total()); ?></span>
                                <span class="text-slate-600 dark:text-slate-400 ml-1"><?php echo e($consortiums->total() === 1 ? 'consórcio' : 'consórcios'); ?></span>
                            </div>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($consortiums->hasPages()): ?>
                        <div class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl p-1 shadow-md">
                            <!--[if BLOCK]><![endif]--><?php if($consortiums->currentPage() > 1): ?>
                            <button wire:click.prevent="previousPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                                <i class="bi bi-chevron-left text-sm text-slate-600 dark:text-slate-300"></i>
                            </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <span class="px-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <?php echo e($consortiums->currentPage()); ?> / <?php echo e($consortiums->lastPage()); ?>

                            </span>
                            <!--[if BLOCK]><![endif]--><?php if($consortiums->hasMorePages()): ?>
                            <button wire:click.prevent="nextPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                                <i class="bi bi-chevron-right text-sm text-slate-600 dark:text-slate-300"></i>
                            </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <button wire:click="$dispatch('openExportModal')"
                            class="p-2.5 bg-gradient-to-br from-blue-400 to-indigo-500 hover:from-blue-500 hover:to-indigo-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105"
                            title="Exportar">
                            <i class="bi bi-download"></i>
                        </button>

                        <button wire:click="toggleTips"
                            class="p-2.5 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105"
                            title="Dicas">
                            <i class="bi bi-lightbulb"></i>
                        </button>

                        <button @click="showFilters = !showFilters"
                            class="p-2.5 bg-white/80 hover:bg-emerald-100 dark:bg-slate-800/80 dark:hover:bg-emerald-900/50 border border-slate-200 dark:border-slate-600 rounded-xl transition-all duration-200 shadow-md"
                            :class="{ 'bg-emerald-100 dark:bg-emerald-900 border-emerald-300 dark:border-emerald-600': showFilters }">
                            <i class="bi bi-funnel text-emerald-600 dark:text-emerald-400"></i>
                        </button>

                        <a href="<?php echo e(route('consortiums.create')); ?>"
                           class="group flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                            <span class="text-sm">Novo Consórcio</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <!-- Filtros Avançados -->
    <div x-show="showFilters"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="mb-6">

        <div class="relative overflow-hidden bg-gradient-to-br from-white via-slate-50 to-emerald-50 dark:from-slate-800 dark:via-slate-700 dark:to-emerald-900 rounded-3xl border border-slate-200/50 dark:border-slate-600/50 shadow-xl shadow-emerald-500/5 dark:shadow-emerald-500/10 backdrop-blur-xl">
            <!-- Fundo decorativo -->
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-50/50 via-transparent to-teal-50/50 dark:from-emerald-900/20 dark:via-transparent dark:to-teal-900/20"></div>

            <!-- Header do painel de filtros -->
            <div class="relative p-6 border-b border-slate-200/50 dark:border-slate-600/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl shadow-lg">
                            <i class="bi bi-funnel text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Filtros Avançados</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Refine sua busca com filtros específicos</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="clearFilters"
                                class="group px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-x-circle mr-1 group-hover:rotate-90 transition-transform duration-200"></i>
                            Limpar
                        </button>
                        <button @click="showFilters = false"
                                class="group p-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500 text-slate-600 dark:text-slate-300 rounded-xl transition-all duration-200">
                            <i class="bi bi-x-lg group-hover:rotate-90 transition-transform duration-200"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Conteúdo dos filtros -->
            <div class="relative p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    <!-- Coluna 1: Status do Consórcio -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-flag mr-1 text-emerald-500"></i>
                            Status do Consórcio
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Todos os Status -->
                            <button wire:click="$set('statusFilter', '')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 transition-all duration-200 <?php echo e($statusFilter === '' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-list-ul text-emerald-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Todos</div>
                                </div>
                            </button>

                            <!-- Ativo -->
                            <button wire:click="setQuickFilter('active')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 <?php echo e($statusFilter === 'active' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-check-circle text-green-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Ativo</div>
                                </div>
                            </button>

                            <!-- Concluído -->
                            <button wire:click="setQuickFilter('completed')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 <?php echo e($statusFilter === 'completed' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-trophy text-blue-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Concluído</div>
                                </div>
                            </button>

                            <!-- Cancelado -->
                            <button wire:click="setQuickFilter('cancelled')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500 transition-all duration-200 <?php echo e($statusFilter === 'cancelled' ? 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-x-circle text-red-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Cancelado</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Coluna 2: Filtros Rápidos de Data -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-calendar mr-1 text-purple-500"></i>
                            Período
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Hoje -->
                            <button wire:click="setQuickFilter('today')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 <?php echo e($quickFilter === 'today' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-calendar-day text-purple-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Hoje</div>
                                </div>
                            </button>

                            <!-- Semana -->
                            <button wire:click="setQuickFilter('week')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 <?php echo e($quickFilter === 'week' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-calendar-week text-blue-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Semana</div>
                                </div>
                            </button>

                            <!-- Mês -->
                            <button wire:click="setQuickFilter('month')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 <?php echo e($quickFilter === 'month' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : ''); ?>">
                                <div class="text-center">
                                    <i class="bi bi-calendar-month text-teal-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Mês</div>
                                </div>
                            </button>

                            <!-- Limpar Data -->
                            <button wire:click="$set('dateStart', ''); $wire.set('dateEnd', ''); $wire.set('quickFilter', '')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500 transition-all duration-200">
                                <div class="text-center">
                                    <i class="bi bi-x-lg text-slate-500 text-lg"></i>
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Limpar</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Coluna 3: Itens por página -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-list-ol mr-1 text-amber-500"></i>
                            Itens por página
                        </h4>
                        <div class="grid grid-cols-3 gap-2">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button wire:click="$set('perPage', <?php echo e($option); ?>)"
                                        class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-amber-300 dark:hover:border-amber-500 transition-all duration-200 <?php echo e($perPage == $option ? 'ring-2 ring-amber-500 bg-amber-50 dark:bg-amber-900/30' : ''); ?>">
                                    <div class="text-center">
                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo e($option); ?></div>
                                    </div>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!-- Coluna 4: Ordenação -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-arrow-up-down mr-1 text-indigo-500"></i>
                            Ordenação
                        </h4>

                        <div class="grid grid-cols-2 gap-2">
                            <!-- Ordenar por Data -->
                            <button wire:click="sortByField('created_at')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 <?php echo e($sortBy === 'created_at' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : ''); ?>">
                                <div class="flex items-center justify-between">
                                    <div class="text-center w-full">
                                        <i class="bi bi-calendar text-indigo-500 text-lg"></i>
                                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Data</div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if($sortBy === 'created_at'): ?>
                                        <i class="bi bi-<?php echo e($sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'); ?> text-indigo-500 text-sm absolute top-1 right-1"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </button>

                            <!-- Ordenar por Nome -->
                            <button wire:click="sortByField('name')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 transition-all duration-200 <?php echo e($sortBy === 'name' ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/30' : ''); ?>">
                                <div class="flex items-center justify-between">
                                    <div class="text-center w-full">
                                        <i class="bi bi-sort-alpha-down text-purple-500 text-lg"></i>
                                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Nome</div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if($sortBy === 'name'): ?>
                                        <i class="bi bi-<?php echo e($sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'); ?> text-purple-500 text-sm absolute top-1 right-1"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </button>

                            <!-- Ordenar por Valor -->
                            <button wire:click="sortByField('monthly_value')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500 transition-all duration-200 <?php echo e($sortBy === 'monthly_value' ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30' : ''); ?>">
                                <div class="flex items-center justify-between">
                                    <div class="text-center w-full">
                                        <i class="bi bi-currency-dollar text-green-500 text-lg"></i>
                                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Valor</div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if($sortBy === 'monthly_value'): ?>
                                        <i class="bi bi-<?php echo e($sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'); ?> text-green-500 text-sm absolute top-1 right-1"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </button>

                            <!-- Ordenar por Início -->
                            <button wire:click="sortByField('start_date')"
                                    class="group p-3 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500 transition-all duration-200 <?php echo e($sortBy === 'start_date' ? 'ring-2 ring-teal-500 bg-teal-50 dark:bg-teal-900/30' : ''); ?>">
                                <div class="flex items-center justify-between">
                                    <div class="text-center w-full">
                                        <i class="bi bi-calendar-check text-teal-500 text-lg"></i>
                                        <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1">Início</div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if($sortBy === 'start_date'): ?>
                                        <i class="bi bi-<?php echo e($sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'); ?> text-teal-500 text-sm absolute top-1 right-1"></i>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Cards (Responsivo com Ultra Wide) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 ultrawind:grid-cols-6 gap-6 mb-8"
         data-ultrawind="<?php echo e($ultraWindClient ?? false ? 'true' : 'false'); ?>"
         data-full-hd="<?php echo e($fullHdLayout ?? false ? 'true' : 'false'); ?>"
         x-bind:data-ultrawind="ultra ? 'true' : 'false'"
         x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $consortiums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consortium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if (isset($component)) { $__componentOriginal3837b3fd2542fd65b4019b8a89f7e33e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3837b3fd2542fd65b4019b8a89f7e33e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.consortium-card','data' => ['consortium' => $consortium]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('consortium-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['consortium' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($consortium)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3837b3fd2542fd65b4019b8a89f7e33e)): ?>
<?php $attributes = $__attributesOriginal3837b3fd2542fd65b4019b8a89f7e33e; ?>
<?php unset($__attributesOriginal3837b3fd2542fd65b4019b8a89f7e33e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3837b3fd2542fd65b4019b8a89f7e33e)): ?>
<?php $component = $__componentOriginal3837b3fd2542fd65b4019b8a89f7e33e; ?>
<?php unset($__componentOriginal3837b3fd2542fd65b4019b8a89f7e33e); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div class="w-20 h-20 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-piggy-bank text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhum consórcio encontrado
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            <!--[if BLOCK]><![endif]--><?php if($search ?? false): ?>
                                Não encontramos consórcios com o termo "<?php echo e($search); ?>".
                            <?php else: ?>
                                Comece criando seu primeiro consórcio.
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                        <a href="<?php echo e(route('consortiums.create')); ?>"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 rounded-xl hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus-circle mr-2"></i>
                            Criar Novo Consórcio
                        </a>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

    <!-- Paginação -->
    <!--[if BLOCK]><![endif]--><?php if($consortiums->hasPages()): ?>
            <div class="mt-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-r from-white via-slate-50 to-emerald-50 dark:from-slate-800 dark:via-slate-700 dark:to-emerald-900 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-600/50 p-4 md:p-6 backdrop-blur-xl">

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-600 dark:text-slate-300">Exibir</label>
                            <select wire:model.live="perPage"
                                class="text-sm rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"><?php echo e($option); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <span class="text-sm text-slate-500 dark:text-slate-400">por página</span>
                        </div>

                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            Mostrando <span class="font-semibold text-slate-800 dark:text-white"><?php echo e($consortiums->firstItem() ?? 0); ?></span>
                            até <span class="font-semibold text-slate-800 dark:text-white"><?php echo e($consortiums->lastItem() ?? 0); ?></span>
                            de <span class="font-semibold text-slate-800 dark:text-white"><?php echo e($consortiums->total()); ?></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click.prevent="gotoPage(1)" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-left"></i>
                        </button>

                        <button <?php if($consortiums->onFirstPage()): ?> disabled <?php endif; ?> wire:click.prevent="previousPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <?php
                            $start = max(1, $consortiums->currentPage() - 2);
                            $end = min($consortiums->lastPage(), $consortiums->currentPage() + 2);
                        ?>

                        <!--[if BLOCK]><![endif]--><?php for($i = $start; $i <= $end; $i++): ?>
                            <button wire:click.prevent="gotoPage(<?php echo e($i); ?>)" wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md text-sm <?php echo e($consortiums->currentPage() === $i ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600'); ?>">
                                <?php echo e($i); ?>

                            </button>
                        <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->

                        <button <?php if(!$consortiums->hasMorePages()): ?> disabled <?php endif; ?> wire:click.prevent="nextPage"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <button wire:click.prevent="gotoPage(<?php echo e($consortiums->lastPage()); ?>)" wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-md text-sm bg-slate-100 dark:bg-zinc-700 text-slate-600 dark:text-slate-200 hover:bg-white dark:hover:bg-zinc-600">
                            <i class="bi bi-chevron-double-right"></i>
                        </button>
                    </div>

                    <div class="flex items-center gap-2" x-data>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Ir para</label>
                        <input x-ref="pageInput" type="number" min="1" max="<?php echo e($consortiums->lastPage()); ?>"
                            class="w-20 text-sm rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1 text-slate-700 dark:text-slate-200"
                            placeholder="#">
                        <button @click.prevent="$wire.call('gotoPage', $refs.pageInput.value)"
                            class="px-3 py-1 rounded-md text-sm bg-emerald-600 text-white">
                            Ir
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Modal de Dicas (Wizard Moderno) -->
    <!--[if BLOCK]><![endif]--><?php if($showTipsModal): ?>
        <div x-data="{
            currentStep: 1,
            totalSteps: 4,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }" x-show="$wire.showTipsModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

            <!-- Modal Content -->
            <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
                class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Header with Progress Bar -->
                <div class="relative bg-gradient-to-br from-emerald-600 via-teal-600 to-green-700 px-8 py-6 text-white">
                    <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>

                    <div class="pr-12">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="bi bi-lightbulb-fill text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold">Dicas de Consórcios</h2>
                                <p class="text-emerald-100 text-sm mt-1">Aprenda a gerenciar seus consórcios com eficiência</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex gap-2 mt-6">
                            <template x-for="step in totalSteps" :key="step">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                    <div class="h-full bg-white rounded-full transition-all duration-500"
                                         :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8">
                    <!-- Step 1: Visão Geral -->
                    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-piggy-bank text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Visão Geral de Consórcios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Conheça as principais funcionalidades do módulo de consórcios</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-emerald-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-emerald-500 rounded-xl">
                                        <i class="bi bi-check-circle text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Gerenciamento</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Acompanhe participantes e contemplações</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-calendar-check text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Sorteios</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Realize sorteios conforme frequência definida</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-wallet2 text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Pagamentos</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Controle financeiro preciso e detalhado</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-amber-500 rounded-xl">
                                        <i class="bi bi-file-pdf text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Exportação</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Gere relatórios em PDF dos consórcios</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Status -->
                    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-flag text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Status de Consórcios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Entenda os diferentes status e suas cores</p>
                        </div>

                        <div class="space-y-4">
                            <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-green-300 dark:border-green-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-xl">
                                        <i class="bi bi-check-circle-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Ativo</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Consórcio em andamento</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-green-500 text-white rounded-lg font-bold">Ativo</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio está ativo com participantes e sorteios acontecendo regularmente.</p>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-blue-300 dark:border-blue-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-blue-500 rounded-xl">
                                        <i class="bi bi-trophy-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Concluído</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Todos contemplados</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-blue-500 text-white rounded-lg font-bold">Concluído</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio foi finalizado com todos os participantes contemplados.</p>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border-2 border-red-300 dark:border-red-600">
                                <div class="flex items-center gap-4 mb-3">
                                    <span class="flex items-center justify-center w-12 h-12 bg-red-500 rounded-xl">
                                        <i class="bi bi-x-circle-fill text-2xl text-white"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800 dark:text-white">Cancelado</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Consórcio encerrado</p>
                                    </div>
                                    <span class="ml-auto px-4 py-2 bg-red-500 text-white rounded-lg font-bold">Cancelado</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 text-sm">O consórcio foi cancelado antes de concluir.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Participantes -->
                    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-people text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Participantes e Sorteios</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">Gerencie participantes e realize sorteios</p>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="bi bi-person-plus text-purple-500"></i>
                                    Adicionar Participantes
                                </h4>
                                <p class="text-slate-700 dark:text-slate-300 text-sm mb-3">Cadastre novos participantes com as informações:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Nome completo</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">CPF</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Contato</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-check-circle text-green-500"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Valor da cota</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="bi bi-shuffle text-blue-500"></i>
                                    Realizar Sorteios
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">1</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Defina a frequência dos sorteios</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">2</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Sistema sorteia participante automaticamente</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">3</div>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">Notifique contemplado e registre</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Ações -->
                    <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                                <i class="bi bi-lightning-charge text-5xl text-white"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Ações Disponíveis</h3>
                            <p class="text-slate-600 dark:text-slate-300 text-lg">O que você pode fazer com os consórcios</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-green-500 rounded-xl">
                                        <i class="bi bi-plus-circle text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Novo Consórcio</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Crie um novo consórcio e adicione participantes</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-blue-500 rounded-xl">
                                        <i class="bi bi-eye text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Visualizar Detalhes</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Veja participantes e histórico completo</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-purple-500 rounded-xl">
                                        <i class="bi bi-pencil text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Editar Consórcio</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Altere informações e gerencie participantes</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-orange-500 rounded-xl">
                                        <i class="bi bi-file-pdf text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-white mb-2">Exportar PDF</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">Gere relatórios detalhados</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-emerald-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-lightbulb text-2xl text-emerald-500"></i>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Dica Final</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 text-sm">
                                Mantenha seus consórcios sempre atualizados! Registre pagamentos, realize sorteios regularmente e
                                comunique-se com os participantes para manter tudo organizado e transparente.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between">
                    <button @click="prevStep()" x-show="currentStep > 1"
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold hover:bg-slate-100 dark:hover:bg-slate-600 transition-all duration-200 shadow-md hover:shadow-lg border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-arrow-left"></i>
                        <span>Anterior</span>
                    </button>

                    <div class="flex gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step"
                                class="w-3 h-3 rounded-full transition-all duration-300"
                                :class="currentStep === step ? 'bg-emerald-600 w-8' : 'bg-slate-300 dark:bg-slate-600 hover:bg-emerald-400'">
                            </button>
                        </template>
                    </div>

                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg"
                        x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir'">
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Modal Exclusão -->
    <div class="fixed inset-0 z-[9999] overflow-y-auto overflow-x-hidden flex justify-center items-center w-full h-full bg-black/30 backdrop-blur-md"
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            wire:click="cancelDelete"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="relative p-4 w-full max-w-lg max-h-full transform transition-all duration-300 scale-100"
                x-transition:enter="transition ease-out duration-300 delay-75"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4" wire:click.stop>
                <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/30 dark:border-zinc-700/50 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-orange-500 to-pink-600"></div>

                    <div class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-trash text-lg"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                Excluir Consórcio
                            </h3>
                        </div>
                        <button type="button" wire:click="cancelDelete"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-slate-100/50 hover:bg-slate-200/70 dark:bg-zinc-700/50 dark:hover:bg-zinc-600/70 rounded-xl text-sm w-10 h-10 flex justify-center items-center transition-all duration-200 backdrop-blur-sm"
                            title="Fechar">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <div class="p-6 text-center">
                        <h3 class="mb-2 text-2xl font-bold text-slate-900 dark:text-white">
                            Excluir Consórcio "<?php echo e($deletingConsortium?->name); ?>"?
                        </h3>
                        <p class="mb-6 text-slate-600 dark:text-slate-400 leading-relaxed">
                            Esta ação não pode ser desfeita. O consórcio e todos os seus dados (participantes, pagamentos, sorteios) serão <span class="font-semibold text-red-600 dark:text-red-400">permanentemente removidos</span>.
                        </p>

                        <div class="flex gap-3">
                            <button wire:click="cancelDelete" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-slate-700 dark:text-slate-300 bg-slate-100/70 dark:bg-zinc-700/70 hover:bg-slate-200/80 dark:hover:bg-zinc-600/80 rounded-xl font-semibold transition-all duration-200 backdrop-blur-sm border border-slate-200/50 dark:border-zinc-600/50 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="bi bi-shield-check text-lg"></i>
                                <span>Cancelar</span>
                            </button>
                            <button wire:click="deleteConsortium" type="button"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 ring-2 ring-red-500/20 focus:ring-4 focus:ring-red-500/40">
                                <i class="bi bi-trash-fill text-lg"></i>
                                <span>Confirmar Exclusão</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Componente de Exportar (Livewire) -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('consortiums.export-consortium');

$__html = app('livewire')->mount($__name, $__params, 'lw-1777427793-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/consortiums/consortiums-index.blade.php ENDPATH**/ ?>