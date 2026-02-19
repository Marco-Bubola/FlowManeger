
<!--[if BLOCK]><![endif]--><?php if($showExportModal && $selectedClientForExport): ?>
<div x-data="{ exportType: 'vendas', selectedSale: null, selectedConsortium: null }"
     x-show="true"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     @keydown.escape.window="$wire.closeExportModal()">

    <!-- Backdrop com blur e gradiente (estilo produtos) -->
    <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-purple-900/50 to-indigo-900/40 backdrop-blur-md"
         @click="$wire.closeExportModal()"></div>

    <!-- Container do Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <!-- Modal -->
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
             @click.stop
             class="relative w-full max-w-2xl mx-4 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden">

            <!-- Efeitos visuais de fundo -->
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-indigo-500/5"></div>
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-purple-400/20 to-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-br from-indigo-400/20 to-purple-600/20 rounded-full blur-3xl"></div>

            <!-- Conteúdo do Modal -->
            <div class="relative z-10">
                <!-- Header -->
                <div class="p-6 border-b border-white/10 dark:border-slate-700/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Ícone animado -->
                            <div class="relative">
                                <div class="absolute w-12 h-12 bg-gradient-to-r from-purple-400/30 to-indigo-500/30 rounded-full animate-pulse"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-download text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                                    Exportar Dados
                                </h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    Cliente: <span class="font-semibold text-purple-600 dark:text-purple-400"><?php echo e($selectedClientForExport->name); ?></span>
                                </p>
                            </div>
                        </div>
                        <button @click="$wire.closeExportModal()"
                                class="p-2 bg-slate-100 hover:bg-red-500 dark:bg-slate-700 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabs: Vendas | Consórcios -->
                <div class="px-6 pt-6">
                    <div class="flex gap-2">
                        <button @click="exportType = 'vendas'; selectedSale = null"
                                :class="exportType === 'vendas' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-2 ring-blue-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'"
                                class="flex-1 px-4 py-3 rounded-xl font-semibold transition-all duration-200">
                            <i class="bi bi-cart3 mr-2"></i>
                            Vendas (<?php echo e($selectedClientForExport->sales->count()); ?>)
                        </button>
                        <button @click="exportType = 'consortiums'; selectedConsortium = null"
                                :class="exportType === 'consortiums' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg ring-2 ring-green-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'"
                                class="flex-1 px-4 py-3 rounded-xl font-semibold transition-all duration-200">
                            <i class="bi bi-building mr-2"></i>
                            Consórcios (<?php echo e($selectedClientForExport->consortiumParticipants ? $selectedClientForExport->consortiumParticipants->count() : 0); ?>)
                        </button>
                    </div>
                </div>

                <!-- Conteúdo: Vendas -->
                <div x-show="exportType === 'vendas'" class="p-6 space-y-4">
                    <!--[if BLOCK]><![endif]--><?php if($selectedClientForExport->sales->count() > 0): ?>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                            <i class="bi bi-check-circle mr-2 text-blue-500"></i>
                            Selecione uma venda para exportar:
                        </h4>

                        <!-- Lista de Vendas -->
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedClientForExport->sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-blue-50 dark:from-slate-700/50 dark:to-blue-900/20 hover:from-slate-100 hover:to-blue-100 dark:hover:from-slate-700 dark:hover:to-blue-900/30 rounded-xl cursor-pointer transition-all border border-slate-200 dark:border-slate-600 hover:border-blue-400 dark:hover:border-blue-500 shadow-sm hover:shadow-lg">
                                <div class="flex items-center gap-3">
                                    <input type="radio"
                                           name="sale_export_modal"
                                           value="<?php echo e($sale->id); ?>"
                                           @click="selectedSale = <?php echo e($sale->id); ?>"
                                           :checked="selectedSale === <?php echo e($sale->id); ?>"
                                           class="w-4 h-4 text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-600 border-slate-300 dark:border-slate-500">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            Venda #<?php echo e($sale->id); ?>

                                        </p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">
                                            <?php echo e($sale->created_at->format('d/m/Y H:i')); ?> - R$ <?php echo e(number_format($sale->total_price, 2, ',', '.')); ?>

                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs px-3 py-1 rounded-full font-semibold
                                    <?php echo e($sale->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-300 dark:border-green-600' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-300 dark:border-yellow-600'); ?>">
                                    <?php echo e(ucfirst($sale->status ?? 'Pendente')); ?>

                                </span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Botões Export Vendas -->
                        <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <button @click="if(selectedSale) { $wire.exportSalePDF(selectedSale); $wire.closeExportModal() }"
                                    :disabled="!selectedSale"
                                    :class="selectedSale ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-lg hover:shadow-xl' : 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed opacity-50'"
                                    class="flex-1 px-4 py-3 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center transform hover:scale-105">
                                <i class="bi bi-file-pdf mr-2 text-lg"></i>
                                Exportar PDF
                            </button>
                            <button @click="if(selectedSale) { $wire.exportSaleExcel(selectedSale); $wire.closeExportModal() }"
                                    :disabled="!selectedSale"
                                    :class="selectedSale ? 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 shadow-lg hover:shadow-xl' : 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed opacity-50'"
                                    class="flex-1 px-4 py-3 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center transform hover:scale-105">
                                <i class="bi bi-file-excel mr-2 text-lg"></i>
                                Exportar Excel
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-16">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 rounded-full flex items-center justify-center mb-4">
                                <i class="bi bi-cart-x text-4xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">Nenhuma venda encontrada</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Este cliente ainda não possui vendas registradas</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!-- Conteúdo: Consórcios -->
                <div x-show="exportType === 'consortiums'" class="p-6 space-y-4">
                    <!--[if BLOCK]><![endif]--><?php if($selectedClientForExport->consortiumParticipants && $selectedClientForExport->consortiumParticipants->count() > 0): ?>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                            <i class="bi bi-check-circle mr-2 text-green-500"></i>
                            Selecione um consórcio para exportar:
                        </h4>

                        <!-- Lista de Consórcios -->
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedClientForExport->consortiumParticipants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-green-50 dark:from-slate-700/50 dark:to-green-900/20 hover:from-slate-100 hover:to-green-100 dark:hover:from-slate-700 dark:hover:to-green-900/30 rounded-xl cursor-pointer transition-all border border-slate-200 dark:border-slate-600 hover:border-green-400 dark:hover:border-green-500 shadow-sm hover:shadow-lg">
                                <div class="flex items-center gap-3">
                                    <input type="radio"
                                           name="consortium_export_modal"
                                           value="<?php echo e($participant->id); ?>"
                                           @click="selectedConsortium = <?php echo e($participant->id); ?>"
                                           :checked="selectedConsortium === <?php echo e($participant->id); ?>"
                                           class="w-4 h-4 text-green-600 focus:ring-green-500 bg-white dark:bg-slate-600 border-slate-300 dark:border-slate-500">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            <?php echo e($participant->consortium->name ?? 'Consórcio #' . $participant->consortium_id); ?>

                                        </p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">
                                            Cota: <?php echo e($participant->quota_number); ?> -
                                            R$ <?php echo e(number_format($participant->consortium->total_value ?? 0, 2, ',', '.')); ?>

                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs px-3 py-1 rounded-full font-semibold
                                    <?php echo e($participant->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-300 dark:border-green-600' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-600'); ?>">
                                    <?php echo e(ucfirst($participant->status ?? 'Inativo')); ?>

                                </span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Botões Export Consórcio -->
                        <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <button @click="if(selectedConsortium) { $wire.exportConsortiumPDF(selectedConsortium); $wire.closeExportModal() }"
                                    :disabled="!selectedConsortium"
                                    :class="selectedConsortium ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-lg hover:shadow-xl' : 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed opacity-50'"
                                    class="flex-1 px-4 py-3 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center transform hover:scale-105">
                                <i class="bi bi-file-pdf mr-2 text-lg"></i>
                                Exportar PDF
                            </button>
                            <button @click="if(selectedConsortium) { $wire.exportConsortiumExcel(selectedConsortium); $wire.closeExportModal() }"
                                    :disabled="!selectedConsortium"
                                    :class="selectedConsortium ? 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 shadow-lg hover:shadow-xl' : 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed opacity-50'"
                                    class="flex-1 px-4 py-3 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center transform hover:scale-105">
                                <i class="bi bi-file-excel mr-2 text-lg"></i>
                                Exportar Excel
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-16">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30 rounded-full flex items-center justify-center mb-4">
                                <i class="bi bi-building-slash text-4xl text-green-600 dark:text-green-400"></i>
                            </div>
                            <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">Nenhum consórcio encontrado</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Este cliente não participa de nenhum consórcio</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

            </div>
        </div>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/clients/_export-modal.blade.php ENDPATH**/ ?>