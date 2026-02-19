<div class=" w-full">
    <div class=" space-y-3">

        
        <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-amber-50/90 to-yellow-50/80 dark:from-slate-800/90 dark:via-amber-900/20 dark:to-yellow-900/20 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-yellow-400/20 via-amber-400/20 to-orange-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-amber-400/10 to-yellow-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

            <div class="relative px-4 py-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <a href="<?php echo e(route('mercadolivre.products')); ?>"
                            class="group relative inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-white to-amber-50 dark:from-slate-800 dark:to-slate-700 hover:from-amber-50 hover:to-yellow-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50">
                            <i class="bi bi-arrow-left text-xl text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform"></i>
                        </a>
                        <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-600 rounded-2xl shadow-xl shadow-amber-500/25">
                            <i class="bi bi-gear-fill text-white text-2xl"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <a href="<?php echo e(route('mercadolivre.products')); ?>" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Mercado Livre</a>
                                <i class="bi bi-chevron-right text-xs"></i>
                                <span class="text-slate-800 dark:text-slate-200 font-medium">Configurações</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 via-amber-700 to-orange-700 dark:from-slate-100 dark:via-amber-300 dark:to-orange-300 bg-clip-text text-transparent">
                                Configurações Mercado Livre
                            </h1>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Integração, token e preferências da sua conta</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!--[if BLOCK]><![endif]--><?php if($isConnected && $token): ?>
                            <div class="flex items-center gap-2 px-3 py-2 bg-emerald-500/20 dark:bg-emerald-500/30 rounded-xl border border-emerald-200 dark:border-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Conectado</span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-600">
                                <i class="bi bi-clock text-amber-600 dark:text-amber-400"></i>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300"><?php echo e($expiresInHours); ?>h restantes</span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-600">
                                <!--[if BLOCK]><![endif]--><?php if(config('services.mercadolivre.environment') === 'sandbox'): ?>
                                    <i class="bi bi-braces text-purple-500"></i>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Sandbox</span>
                                <?php else: ?>
                                    <i class="bi bi-rocket text-indigo-500"></i>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Produção</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <button wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                                <i class="bi bi-wifi" wire:loading.remove wire:target="testConnection"></i>
                                <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="testConnection"></i>
                                <span wire:loading.remove wire:target="testConnection">Testar</span>
                                <span wire:loading wire:target="testConnection">Testando...</span>
                            </button>
                            <button wire:click="refreshToken" wire:loading.attr="disabled" wire:target="refreshToken"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 hover:bg-amber-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl transition-all disabled:opacity-50">
                                <i class="bi bi-arrow-repeat" wire:loading.remove wire:target="refreshToken"></i>
                                <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshToken"></i>
                                Renovar token
                            </button>
                        <?php else: ?>
                            <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-600">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Desconectado</span>
                            </div>
                            <button wire:click="connect" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-yellow-400 via-amber-500 to-orange-600 hover:from-yellow-500 hover:via-amber-600 hover:to-orange-700 text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                                <i class="bi bi-link-45deg"></i>
                                Conectar conta
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        </div>

        
        <div class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-950/40 p-4">
            <div class="flex gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="bi bi-info-circle text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200 mb-1">Por que a conexão às vezes “sai” ou expira?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        O token de acesso do Mercado Livre expira em <strong>6 horas</strong>. O sistema renova automaticamente o token quando você usa outras páginas (Produtos, Pedidos, Publicações). 
                        Se a renovação falhar (sem internet, erro temporário do ML ou token revogado), a conta pode aparecer como desconectada. 
                        Nesse caso, clique em <strong>Conectar conta</strong> novamente para reautorizar. Você também pode usar <strong>Renovar token</strong> aqui nas configurações para forçar a renovação.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            
            <div class="xl:col-span-8 space-y-6">
                <!--[if BLOCK]><![endif]--><?php if($isConnected && $token): ?>
                    
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 shadow-xl">
                        <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
                        <div class="relative p-6">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                                        <i class="bi bi-check-circle-fill text-white text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300">Conta conectada</h3>
                                        <p class="text-xl font-semibold text-slate-800 dark:text-slate-200"><?php echo e($token->ml_nickname ?? 'Usuário ML'); ?></p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">ID ML: <?php echo e($token->ml_user_id); ?></p>
                                    </div>
                                </div>
                                <button wire:click="disconnect" wire:confirm="Desconectar sua conta do Mercado Livre? Você precisará autorizar novamente para usar a integração."
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg">
                                    <i class="bi bi-link-45deg"></i>
                                    Desconectar
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                                Acesso rápido
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <a href="<?php echo e(route('mercadolivre.products')); ?>" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center group-hover:bg-amber-500/30 transition-colors">
                                    <i class="bi bi-box-seam text-amber-600 dark:text-amber-400 text-xl"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">Produtos</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Integrar e publicar</span>
                                </div>
                                <i class="bi bi-chevron-right text-slate-400 group-hover:text-amber-500 ml-auto"></i>
                            </a>
                            <a href="<?php echo e(route('mercadolivre.publications')); ?>" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
                                <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center group-hover:bg-indigo-500/30 transition-colors">
                                    <i class="bi bi-megaphone text-indigo-600 dark:text-indigo-400 text-xl"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">Publicações</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Anúncios no ML</span>
                                </div>
                                <i class="bi bi-chevron-right text-slate-400 group-hover:text-indigo-500 ml-auto"></i>
                            </a>
                            <a href="<?php echo e(route('mercadolivre.orders')); ?>" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
                                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/30 transition-colors">
                                    <i class="bi bi-cart-check text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">Pedidos</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Vendas do ML</span>
                                </div>
                                <i class="bi bi-chevron-right text-slate-400 group-hover:text-emerald-500 ml-auto"></i>
                            </a>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl <?php echo e($needsRefresh ? 'bg-red-500/20' : 'bg-blue-500/20'); ?> flex items-center justify-center">
                                    <i class="bi bi-clock-history <?php echo e($needsRefresh ? 'text-red-600' : 'text-blue-600'); ?> text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Expira em</div>
                                    <div class="text-lg font-bold <?php echo e($needsRefresh ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100'); ?>"><?php echo e($expiresInHours); ?>h</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400"><?php echo e($expiresAt); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                                    <i class="bi bi-braces text-purple-600 dark:text-purple-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Ambiente</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                        <?php echo e(config('services.mercadolivre.environment') === 'sandbox' ? 'Sandbox' : 'Produção'); ?>

                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                <?php echo e(config('services.mercadolivre.environment') === 'sandbox' ? 'Testes' : 'Vendas reais'); ?>

                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                                    <i class="bi bi-shield-check text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Status</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Token válido</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">Conexão ativa</p>
                        </div>
                    </div>

                    
                    <!--[if BLOCK]><![endif]--><?php if(!empty($userInfo)): ?>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <i class="bi bi-person-badge text-indigo-500"></i>
                                    Dados do vendedor (ML)
                                </h3>
                            </div>
                            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nickname</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e($userInfo['nickname'] ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Site</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e($userInfo['site_id'] ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">País</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e($userInfo['country_id'] ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reputação</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e($userInfo['seller_reputation']['level_id'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php else: ?>
                    
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 shadow-xl">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
                        <div class="relative p-8 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg">
                                <i class="bi bi-link-45deg text-white text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">Conta não conectada</h3>
                            <p class="text-slate-600 dark:text-slate-400 mb-6 max-w-md mx-auto">
                                Conecte sua conta do Mercado Livre para sincronizar produtos, gerenciar publicações e pedidos.
                            </p>
                            <button wire:click="connect" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-yellow-400 via-amber-500 to-orange-600 hover:from-yellow-500 hover:via-amber-600 hover:to-orange-700 text-white text-lg font-bold rounded-2xl shadow-2xl hover:shadow-amber-500/30 transition-all disabled:opacity-50">
                                <i class="bi bi-box-arrow-in-right text-2xl"></i>
                                Conectar com Mercado Livre
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-arrow-repeat text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Sincronização</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Estoque e preços entre seu sistema e o ML</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-cart-check text-purple-600 dark:text-purple-400 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Pedidos</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Importe vendas do ML para o sistema</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-megaphone text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Publicações</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Gerencie anúncios em um só lugar</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-bell text-amber-600 dark:text-amber-400 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Notificações</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Vendas e perguntas em tempo real</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="xl:col-span-4 space-y-6">
                <!--[if BLOCK]><![endif]--><?php if($isConnected && $token): ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-list-check text-indigo-500"></i>
                                Próximos passos
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                <span class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">1</span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Configurar produtos</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Dados para publicação no ML</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                <span class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">2</span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Publicar no ML</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Enviar anúncios ao marketplace</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                <span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">3</span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Sincronização</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Estoque e preços automáticos</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                <span class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">4</span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pedidos</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Importar vendas do ML</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-question-circle text-indigo-500"></i>
                                Como conectar
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">1</span>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Clique em <strong class="text-slate-800 dark:text-slate-200">Conectar conta</strong></p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">2</span>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Autorize o acesso no site do Mercado Livre</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">3</span>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Você volta aqui automaticamente com a conta conectada</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/50 dark:to-purple-950/50 shadow-xl overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-3 flex items-center gap-2">
                            <i class="bi bi-book text-indigo-500"></i>
                            Ajuda e documentação
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Configure App no portal de desenvolvedores do Mercado Livre e use as credenciais no .env.
                        </p>
                        <div class="space-y-2">
                            <a href="https://developers.mercadolivre.com.br/" target="_blank" rel="noopener"
                                class="flex items-center gap-2 w-full px-4 py-3 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 text-sm font-medium transition-colors">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Portal ML Developer
                            </a>
                            <a href="https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao" target="_blank" rel="noopener"
                                class="flex items-center gap-2 w-full px-4 py-3 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 text-sm font-medium transition-colors">
                                <i class="bi bi-key"></i>
                                OAuth e autorização
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const type = event[0]?.type || 'info';
                const message = event[0]?.message || 'Notificação';
                if (type === 'success') {
                    alert('✅ ' + message);
                } else if (type === 'error') {
                    alert('❌ ' + message);
                } else {
                    alert(message);
                }
            });
        });
    </script>
</div>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/mercadolivre/settings.blade.php ENDPATH**/ ?>