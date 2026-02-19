<div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950">
    
    <div>

        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                
                <div class="xl:col-span-12">
                    <?php echo $__env->make('livewire.dashboard.partials.kpis-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                
                <div class="xl:col-span-12">
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                        
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                        <i class="fas fa-wave-square text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Fluxo de
                                            Caixa</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">12 meses</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="cashflowMonthlyChart" class="h-56"></div>
                            </div>
                        </div>
                        
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center shadow">
                                        <i class="fas fa-chart-pie text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Despesas
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Top 10 mês</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="expensesByCategoryChart" class="h-56"></div>
                            </div>
                        </div>
                        
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow">
                                        <i class="fas fa-credit-card text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Invoices
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Por banco (6m)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="invoicesByBankChart" class="h-56"></div>
                            </div>
                        </div>
                        
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow">
                                        <i class="fas fa-chart-bar text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Vendas x
                                            Custos</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Visão geral</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="salesVsCostsChart" class="h-56"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="xl:col-span-4 grid grid-cols-1 gap-4">
                    
                    <div>
                        <?php echo $__env->make('livewire.dashboard.partials.alertas', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center shadow">
                                    <i class="fas fa-piggy-bank text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Total
                                        Economizado</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Cofrinhos</div>
                                </div>
                            </div>
                            <a href="<?php echo e(route('cofrinhos.index')); ?>"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Ver</a>
                        </div>
                        <div class="px-4 pb-4">
                            <p class="text-3xl font-bold text-pink-700 dark:text-pink-400">
                                R$ <?php echo e(number_format($totalEconomizado, 2, ',', '.')); ?>

                            </p>
                        </div>
                    </div>

                    
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow">
                                    <i class="fas fa-file-invoice-dollar text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pendências
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Janela de 30 dias</div>
                                </div>
                            </div>
                            <a href="<?php echo e(route('invoices.index')); ?>"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Invoices</a>
                        </div>
                        <div class="px-4 pb-4 space-y-2">
                            <div
                                class="flex items-center justify-between rounded-xl bg-emerald-50/60 dark:bg-emerald-900/15 p-3">
                                <span class="text-xs text-emerald-800 dark:text-emerald-200">A receber (parcelas)</span>
                                <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">R$
                                    <?php echo e(number_format($contasReceberPendentes, 2, ',', '.')); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-orange-50/60 dark:bg-orange-900/15 p-3">
                                <span class="text-xs text-orange-800 dark:text-orange-200">Invoices (30d)</span>
                                <span class="text-sm font-bold text-orange-700 dark:text-orange-300">R$
                                    <?php echo e(number_format($invoicesProxVenc30Total, 2, ',', '.')); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-3">
                                <span class="text-xs text-red-800 dark:text-red-200">A pagar (aprox.)</span>
                                <span class="text-sm font-bold text-red-700 dark:text-red-300">R$
                                    <?php echo e(number_format($contasPagarPendentes, 2, ',', '.')); ?></span>
                            </div>
                        </div>
                    </div>

                    
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow">
                                    <i class="fas fa-layer-group text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Consórcios
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Resumo do módulo</div>
                                </div>
                            </div>
                            <a href="<?php echo e(route('consortiums.index')); ?>"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                        </div>
                        <div class="px-4 pb-4 space-y-2">
                            <div
                                class="flex items-center justify-between rounded-xl bg-teal-50/60 dark:bg-teal-900/15 p-3">
                                <span class="text-xs text-teal-800 dark:text-teal-200">Ativos</span>
                                <span
                                    class="text-sm font-bold text-teal-700 dark:text-teal-300"><?php echo e($totalConsorciosAtivos); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-blue-50/60 dark:bg-blue-900/15 p-3">
                                <span class="text-xs text-blue-800 dark:text-blue-200">Participantes ativos</span>
                                <span
                                    class="text-sm font-bold text-blue-700 dark:text-blue-300"><?php echo e($consorcioParticipantesAtivos); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-amber-50/60 dark:bg-amber-900/15 p-3">
                                <span class="text-xs text-amber-800 dark:text-amber-200">Pagamentos pendentes</span>
                                <span class="text-sm font-bold text-amber-700 dark:text-amber-300">R$
                                    <?php echo e(number_format($consorcioPagamentosPendentesTotal, 2, ',', '.')); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-indigo-50/60 dark:bg-indigo-900/15 p-3">
                                <span class="text-xs text-indigo-800 dark:text-indigo-200">Sorteios (30d)</span>
                                <span
                                    class="text-sm font-bold text-indigo-700 dark:text-indigo-300"><?php echo e($proximosSorteios); ?></span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-purple-50/60 dark:bg-purple-900/15 p-3">
                                <span class="text-xs text-purple-800 dark:text-purple-200">Contemplações</span>
                                <span
                                    class="text-sm font-bold text-purple-700 dark:text-purple-300"><?php echo e($consorcioContemplacoesTotal); ?></span>
                            </div>
                        </div>
                    </div>

                </div>

                
                <div class="xl:col-span-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                        
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow">
                                        <i class="fas fa-shopping-cart text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Comercial
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Vendas e cobranças
                                        </div>
                                    </div>
                                </div>
                                <a href="<?php echo e(route('sales.index')); ?>"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Ver</a>
                            </div>
                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Vendas (mês)</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo e($salesMonth); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Ticket médio</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">R$
                                        <?php echo e(number_format($ticketMedio, 2, ',', '.')); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Faturamento Total</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">R$
                                        <?php echo e(number_format($totalFaturamento, 2, ',', '.')); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-emerald-50/60 dark:bg-emerald-900/15 p-3">
                                    <span class="text-xs text-emerald-800 dark:text-emerald-200">A receber
                                        (parcelas)</span>
                                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">R$
                                        <?php echo e(number_format($contasReceberPendentes, 2, ',', '.')); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-3">
                                    <span class="text-xs text-red-800 dark:text-red-200">Vencidas</span>
                                    <span
                                        class="text-sm font-bold text-red-700 dark:text-red-300"><?php echo e($parcelasVencidasCount); ?>

                                        (R$ <?php echo e(number_format($parcelasVencidasValor, 2, ',', '.')); ?>)</span>
                                </div>
                            </div>
                        </div>

                        
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center shadow">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Clientes
                                            & Estoque</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Base e alertas</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo e(route('clients.index')); ?>"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Clientes</a>
                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                    <a href="<?php echo e(route('products.index')); ?>"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Produtos</a>
                                </div>
                            </div>
                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Clientes</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo e($totalClientes); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Novos no mês</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo e($clientesNovosMes); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Produtos
                                        cadastrados</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo e($produtosCadastrados); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-yellow-50/60 dark:bg-yellow-900/15 p-3">
                                    <span class="text-xs text-yellow-800 dark:text-yellow-200">Estoque baixo</span>
                                    <span
                                        class="text-sm font-bold text-yellow-700 dark:text-yellow-300"><?php echo e($produtosEstoqueBaixo); ?></span>
                                </div>
                            </div>
                        </div>

                        
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow">
                                        <i class="fas fa-bullseye text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            Planejamento</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Orçamento e
                                            recorrências</div>
                                    </div>
                                </div>
                                <a href="<?php echo e(route('cashbook.index')); ?>"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                            </div>

                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-teal-50/60 dark:bg-teal-900/15 p-3">
                                    <span class="text-xs text-teal-800 dark:text-teal-200">Orçado (mês)</span>
                                    <span class="text-sm font-bold text-teal-700 dark:text-teal-300">R$
                                        <?php echo e(number_format($orcamentoMesTotal, 2, ',', '.')); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-rose-50/60 dark:bg-rose-900/15 p-3">
                                    <span class="text-xs text-rose-800 dark:text-rose-200">Gasto (mês)</span>
                                    <span class="text-sm font-bold text-rose-700 dark:text-rose-300">R$
                                        <?php echo e(number_format($orcamentoMesUsado, 2, ',', '.')); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-orange-50/60 dark:bg-orange-900/15 p-3">
                                    <span class="text-xs text-orange-800 dark:text-orange-200">Recorrências
                                        ativas</span>
                                    <span
                                        class="text-sm font-bold text-orange-700 dark:text-orange-300"><?php echo e($recorrentesAtivas); ?></span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-amber-50/60 dark:bg-amber-900/15 p-3">
                                    <span class="text-xs text-amber-800 dark:text-amber-200">Recorrências (30d)</span>
                                    <span class="text-sm font-bold text-amber-700 dark:text-amber-300">R$
                                        <?php echo e(number_format($recorrentesProx30Total, 2, ',', '.')); ?></span>
                                </div>

                                
                                <div class="mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-800">
                                    <div
                                        class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                        <i class="fas fa-heartbeat text-emerald-500"></i>
                                        Saúde do Sistema (uploads)
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Cashbook</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                <?php echo e($lastUploads['cashbook']->status ?? '—'); ?></div>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Produtos</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                <?php echo e($lastUploads['products']->status ?? '—'); ?></div>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Invoices</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                <?php echo e($lastUploads['invoices']->status ?? '—'); ?></div>
                                        </div>
                                    </div>
                                </div>

                                
                                <!--[if BLOCK]><![endif]--><?php if(!empty($orcamentosTopEstouro)): ?>
                                    <div class="mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-800">
                                        <div
                                            class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                                            Top estouros (mês)
                                        </div>
                                        <div class="max-h-24 overflow-auto space-y-2">
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $orcamentosTopEstouro; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div
                                                    class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-2">
                                                    <span
                                                        class="text-xs text-slate-700 dark:text-slate-200 truncate max-w-[70%]"><?php echo e($row['category']); ?></span>
                                                    <span class="text-xs font-bold text-red-700 dark:text-red-300">+R$
                                                        <?php echo e(number_format($row['estouro'], 2, ',', '.')); ?></span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                    </div>

                    
                    <div class="mt-6">
                        <?php echo $__env->make('livewire.dashboard.partials.atividades', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                </div>

            </div>

            
            <?php echo $__env->make('livewire.dashboard.partials.fab-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>
    </div>

    
    <style>
            .apexcharts-legend-text {
                color: #0f172a !important;
            }

            .dark .apexcharts-legend-text,
            .apexcharts-theme-dark .apexcharts-legend-text {
                color: #E5E7EB !important;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const cashflowMonthly = <?php echo json_encode($cashflowMonthly, 15, 512) ?>;
                const expensesByCategory = <?php echo json_encode($expensesByCategory, 15, 512) ?>;
                const gastosInvoicePorBanco = <?php echo json_encode($gastosInvoicePorBanco, 15, 512) ?>;

                // 1) Fluxo de caixa (12 meses)
                const cashflowMonthlyOptions = {
                    series: [{
                            name: 'Receitas',
                            data: (cashflowMonthly || []).map(x => Number(x.receitas || 0))
                        },
                        {
                            name: 'Despesas',
                            data: (cashflowMonthly || []).map(x => Number(x.despesas || 0))
                        }
                    ],
                    chart: {
                        type: 'area',
                        height: 240,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: ['#10b981', '#ef4444'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.55,
                            opacityTo: 0.12
                        }
                    },
                    xaxis: {
                        categories: (cashflowMonthly || []).map(x => x.label),
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const cashflowEl = document.querySelector('#cashflowMonthlyChart');
                if (cashflowEl) new ApexCharts(cashflowEl, cashflowMonthlyOptions).render();

                // 2) Despesas por categoria (donut)
                const expensesOptions = {
                    series: (expensesByCategory || []).map(x => Number(x.total || 0)),
                    labels: (expensesByCategory || []).map(x => x.label),
                    chart: {
                        type: 'donut',
                        height: 240,
                        toolbar: {
                            show: false
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    dataLabels: {
                        formatter: (val) => Number(val).toFixed(0) + '%'
                    }
                };
                const expensesEl = document.querySelector('#expensesByCategoryChart');
                if (expensesEl) new ApexCharts(expensesEl, expensesOptions).render();

                // 3) Invoices por banco (linha) - usando series do backend
                const invoicesByBankOptions = {
                    series: gastosInvoicePorBanco || [],
                    chart: {
                        type: 'line',
                        height: 240,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 3
                    },
                    xaxis: {
                        categories: [
                            '<?php echo e(now()->subMonths(5)->format('M')); ?>',
                            '<?php echo e(now()->subMonths(4)->format('M')); ?>',
                            '<?php echo e(now()->subMonths(3)->format('M')); ?>',
                            '<?php echo e(now()->subMonths(2)->format('M')); ?>',
                            '<?php echo e(now()->subMonths(1)->format('M')); ?>',
                            '<?php echo e(now()->format('M')); ?>'
                        ],
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const invoicesByBankEl = document.querySelector('#invoicesByBankChart');
                if (invoicesByBankEl) new ApexCharts(invoicesByBankEl, invoicesByBankOptions).render();

                // 4) Vendas vs custos (bar)
                const salesVsCostsOptions = {
                    series: [{
                            name: 'Vendas',
                            data: [Number(<?php echo json_encode($valorVendas, 15, 512) ?>)]
                        },
                        {
                            name: 'Custo Estoque',
                            data: [Number(<?php echo json_encode($custoEstoque, 15, 512) ?>)]
                        },
                        {
                            name: 'Custo Vendidos',
                            data: [Number(<?php echo json_encode($custoProdutosVendidos, 15, 512) ?>)]
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 240,
                        stacked: true,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#8b5cf6', '#3b82f6', '#f59e0b'],
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            columnWidth: '55%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: ['Total'],
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const salesVsCostsEl = document.querySelector('#salesVsCostsChart');
                if (salesVsCostsEl) new ApexCharts(salesVsCostsEl, salesVsCostsOptions).render();
            });
        </script>
</div><?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/dashboard/dashboard-index.blade.php ENDPATH**/ ?>