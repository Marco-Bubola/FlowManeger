<div class="ml-settings-page w-full mobile-393-base" x-data="{ showDisconnectModal: false }" @keydown.escape.window="showDisconnectModal = false">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/ml-settings-ultrawide.css') }}">
    <div class=" space-y-3">

        {{-- Cabeçalho no padrão do app, com o menu do Mercado Livre --}}
        @php
            $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70 disabled:opacity-50';
            $stChip = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold';
        @endphp
        <x-page-header title="Configurações" icon="bi-gear" section="mercadolivre" tabs-section="mercadolivre" tabs-active="config"
                       subtitle="Integração, token e preferências da sua conta">
            <x-slot:meta>
                @if($isConnected && $token)
                    <span class="{{ $stChip }} bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Conectado</span>
                    <span class="{{ $stChip }} bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-clock"></i>{{ $expiresInHours }}h restantes</span>
                    @if(config('services.mercadolivre.environment') === 'sandbox')
                        <span class="{{ $stChip }} bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300"><i class="bi bi-braces"></i>Sandbox</span>
                    @else
                        <span class="{{ $stChip }} bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-rocket"></i>Produção</span>
                    @endif
                @else
                    <span class="{{ $stChip }} bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Desconectado</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                @if($isConnected && $token)
                    <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection" title="Testar conexão" class="{{ $phBtn }}">
                        <i class="bi bi-wifi text-sky-500" wire:loading.remove wire:target="testConnection"></i>
                        <i class="bi bi-arrow-repeat animate-spin text-sky-500" wire:loading wire:target="testConnection"></i>
                        <span class="hidden sm:inline" wire:loading.remove wire:target="testConnection">Testar</span>
                        <span class="hidden sm:inline" wire:loading wire:target="testConnection">Testando...</span>
                    </button>
                    <button type="button" wire:click="refreshToken" wire:loading.attr="disabled" wire:target="refreshToken" title="Renovar token"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap disabled:opacity-50">
                        <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin" wire:target="refreshToken"></i><span class="hidden sm:inline">Renovar token</span>
                    </button>
                @else
                    <button type="button" wire:click="connect" wire:loading.attr="disabled" title="Conectar conta"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap disabled:opacity-50">
                        <i class="bi bi-link-45deg"></i><span class="hidden sm:inline">Conectar conta</span>
                    </button>
                @endif
            </x-slot:actions>
        </x-page-header>

        {{-- Aviso sobre expiração do token --}}
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
            {{-- Conteúdo principal --}}
            <div class="xl:col-span-8 space-y-6">
                @if($isConnected && $token)
                    {{-- Card status conectado --}}
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
                                        <p class="text-xl font-semibold text-slate-800 dark:text-slate-200">{{ $token->ml_nickname ?? 'Usuário ML' }}</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">ID ML: {{ $token->ml_user_id }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="showDisconnectModal = true"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg">
                                    <i class="bi bi-link-45deg"></i>
                                    Desconectar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Atalhos rápidos --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                                Acesso rápido
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <a href="{{ route('mercadolivre.products') }}" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center group-hover:bg-amber-500/30 transition-colors">
                                    <i class="bi bi-box-seam text-amber-600 dark:text-amber-400 text-xl"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">Produtos</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Integrar e publicar</span>
                                </div>
                                <i class="bi bi-chevron-right text-slate-400 group-hover:text-amber-500 ml-auto"></i>
                            </a>
                            <a href="{{ route('mercadolivre.publications') }}" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
                                <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center group-hover:bg-indigo-500/30 transition-colors">
                                    <i class="bi bi-megaphone text-indigo-600 dark:text-indigo-400 text-xl"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">Publicações</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Anúncios no ML</span>
                                </div>
                                <i class="bi bi-chevron-right text-slate-400 group-hover:text-indigo-500 ml-auto"></i>
                            </a>
                            <a href="{{ route('mercadolivre.orders') }}" class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg transition-all">
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

                    {{-- Info do token e ambiente --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl {{ $needsRefresh ? 'bg-red-500/20' : 'bg-blue-500/20' }} flex items-center justify-center">
                                    <i class="bi bi-clock-history {{ $needsRefresh ? 'text-red-600' : 'text-blue-600' }} text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Expira em</div>
                                    <div class="text-lg font-bold {{ $needsRefresh ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100' }}">{{ $expiresInHours }}h</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">{{ $expiresAt }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                                    <i class="bi bi-braces text-purple-600 dark:text-purple-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Ambiente</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                        {{ config('services.mercadolivre.environment') === 'sandbox' ? 'Sandbox' : 'Produção' }}
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                {{ config('services.mercadolivre.environment') === 'sandbox' ? 'Testes' : 'Vendas reais' }}
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

                    {{-- Informações do vendedor (API) --}}
                    @if(!empty($userInfo))
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
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $userInfo['nickname'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Site</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $userInfo['site_id'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">País</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $userInfo['country_id'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reputação</div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $userInfo['seller_reputation']['level_id'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Estado desconectado --}}
                    <x-empty-state icon="bi-link-45deg" tone="amber" title="Conta não conectada"
                                   text="Conecte sua conta do Mercado Livre para sincronizar produtos, gerenciar publicações e pedidos.">
                        <button wire:click="connect" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition disabled:opacity-50">
                            <i class="bi bi-box-arrow-in-right"></i> Conectar com Mercado Livre
                        </button>
                    </x-empty-state>

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
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="xl:col-span-4 space-y-6">
                @if($isConnected && $token)
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
                @else
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
                @endif

                {{-- Ajuda e documentação --}}
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

    {{-- ───── Modal moderno de confirmação: Desconectar ───── --}}
    <template x-teleport="body">
        <div x-show="showDisconnectModal" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="ml-modal-overlay" @click.self="showDisconnectModal = false" style="display:none;">
            <div class="ml-modal-card"
                 x-show="showDisconnectModal"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                <div class="ml-modal-icon"><i class="bi bi-plug"></i></div>
                <h3 class="ml-modal-title">Desconectar do Mercado Livre?</h3>
                <p class="ml-modal-text">
                    Você precisará <strong>autorizar novamente</strong> para voltar a usar a integração
                    (publicações, pedidos, perguntas e mensagens).
                </p>
                <div class="ml-modal-actions">
                    <button type="button" @click="showDisconnectModal = false" class="ml-modal-btn-cancel">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="button" @click="showDisconnectModal = false; $wire.call('disconnect')" class="ml-modal-btn-confirm">
                        <i class="bi bi-plug-fill"></i> Desconectar
                    </button>
                </div>
            </div>
        </div>
    </template>

    <style>
        [x-cloak] { display: none !important; }
        .ml-modal-overlay { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(43,30,62,0.55); backdrop-filter: blur(8px); }
        .ml-modal-card { width: 100%; max-width: 420px; background: #fff; border-radius: 1.5rem; padding: 2rem 1.75rem 1.6rem; text-align: center; box-shadow: 0 30px 70px rgba(43,30,62,0.4); border: 1px solid rgba(164,144,194,0.25); }
        .dark .ml-modal-card { background: linear-gradient(160deg,#2b1e3e,#241a35); border-color: rgba(164,144,194,0.3); }
        .ml-modal-icon { width: 4.5rem; height: 4.5rem; margin: 0 auto 1.1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.1rem; color: #fff; background: linear-gradient(135deg,#f87171,#dc2626); box-shadow: 0 10px 26px rgba(220,38,38,0.45); }
        .ml-modal-title { font-size: 1.25rem; font-weight: 800; color: #2b1e3e; margin: 0 0 0.6rem; }
        .dark .ml-modal-title { color: #e6e6fa; }
        .ml-modal-text { font-size: 0.92rem; line-height: 1.55; color: #475569; margin: 0 0 1.4rem; }
        .dark .ml-modal-text { color: #cbd5e1; }
        .ml-modal-text strong { color: #dc2626; }
        .dark .ml-modal-text strong { color: #fca5a5; }
        .ml-modal-actions { display: flex; gap: 0.7rem; }
        .ml-modal-btn-cancel, .ml-modal-btn-confirm { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.75rem 1rem; border-radius: 0.85rem; font-size: 0.9rem; font-weight: 800; cursor: pointer; border: none; transition: all 0.18s; }
        .ml-modal-btn-cancel { background: rgba(148,163,184,0.18); color: #475569; }
        .ml-modal-btn-cancel:hover { background: rgba(148,163,184,0.3); }
        .dark .ml-modal-btn-cancel { background: rgba(148,163,184,0.18); color: #cbd5e1; }
        .ml-modal-btn-confirm { background: linear-gradient(135deg,#f87171,#dc2626); color: #fff; box-shadow: 0 6px 18px rgba(220,38,38,0.4); }
        .ml-modal-btn-confirm:hover { background: linear-gradient(135deg,#ef4444,#b91c1c); transform: translateY(-1px); box-shadow: 0 8px 22px rgba(220,38,38,0.5); }
    </style>
</div>
