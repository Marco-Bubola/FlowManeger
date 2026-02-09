<div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4 mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Mercado Livre</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Gerencie sua integração e sincronização</p>
                    </div>
                </div>
                
                {{-- Actions --}}
                @if($isConnected && $token)
                    <div class="flex items-center gap-2">
                        <button wire:click="testConnection" 
                                wire:loading.attr="disabled"
                                wire:target="testConnection"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg hover:shadow-xl disabled:opacity-50">
                            <svg wire:loading.remove wire:target="testConnection" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.007H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                            <svg wire:loading wire:target="testConnection" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="testConnection">Testar</span>
                            <span wire:loading wire:target="testConnection">Testando...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            {{-- Main Content --}}
            <div class="xl:col-span-8">
                @if($isConnected && $token)
                    {{-- CONECTADO --}}
                    <div class="space-y-6">
                        {{-- Status Card --}}
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 backdrop-blur shadow-xl">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
                            <div class="relative p-6">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-xl font-bold text-emerald-800 dark:text-emerald-300">Conta Conectada</h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500 text-white">
                                                    Ativo
                                                </span>
                                            </div>
                                            <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                                                {{ $token->ml_nickname ?? 'Usuário ML' }}
                                            </p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                                ID: {{ $token->ml_user_id }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <button wire:click="disconnect" 
                                            wire:confirm="Tem certeza que deseja desconectar sua conta do Mercado Livre?"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg hover:shadow-xl">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/>
                                        </svg>
                                        Desconectar
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Info Cards Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Expiração --}}
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $needsRefresh ? 'from-red-500 to-rose-600' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center shadow">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Expiração</div>
                                            <div class="text-lg font-bold {{ $needsRefresh ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100' }}">
                                                {{ $expiresInHours }}h
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 mb-3">
                                        {{ $expiresAt }}
                                    </div>
                                    @if($needsRefresh)
                                        <button wire:click="refreshToken" 
                                                wire:loading.attr="disabled"
                                                class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-xs font-medium rounded-lg transition-all shadow">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                            </svg>
                                            Renovar
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Ambiente --}}
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Ambiente</div>
                                            <div class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                                @if(config('services.mercadolivre.environment') === 'sandbox')
                                                    🧪 Sandbox
                                                @else
                                                    🚀 Produção
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        @if(config('services.mercadolivre.environment') === 'sandbox')
                                            Ambiente de testes
                                        @else
                                            Ambiente real
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Status</div>
                                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                ✅ Ativo
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        Token válido
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- User Info --}}
                        @if(!empty($userInfo))
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                                <div class="p-6">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                        Informações do Vendedor
                                    </h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                @if(isset($userInfo['seller_reputation']['level_id']))
                                                    {{ $userInfo['seller_reputation']['level_id'] }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- DESCONECTADO --}}
                    <div class="space-y-6">
                        {{-- Main CTA Card --}}
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 backdrop-blur shadow-xl">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-500/10 rounded-full blur-3xl"></div>
                            <div class="relative p-8 text-center">
                                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-slate-300 to-slate-400 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-10 h-10 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">
                                    Não Conectado
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 mb-6 max-w-md mx-auto">
                                    Conecte sua conta do Mercado Livre para sincronizar produtos, gerenciar vendas e muito mais.
                                </p>
                                <button wire:click="connect" 
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-yellow-400 via-yellow-500 to-amber-600 hover:from-yellow-500 hover:via-yellow-600 hover:to-amber-700 text-white text-lg font-bold rounded-2xl transition-all shadow-2xl hover:shadow-yellow-500/50 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                                    </svg>
                                    Conectar com Mercado Livre
                                </button>
                            </div>
                        </div>

                        {{-- Benefits Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Sincronização Automática</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Sincronize automaticamente estoque e preços entre seu sistema e o Mercado Livre</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Importação de Pedidos</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Importe pedidos automaticamente para seu sistema de gestão</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.25-3v1.5c0 .621-.504 1.125-1.125 1.125h-6.75c-.621 0-1.125-.504-1.125-1.125v-1.5m8.25 0h-8.25"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Gestão Centralizada</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Gerencie todos os seus anúncios e vendas em um só lugar</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-orange-600 flex items-center justify-center shadow flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">Notificações em Tempo Real</h4>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Receba notificações instantâneas sobre vendas e perguntas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="xl:col-span-4 space-y-6">
                {{-- Próximos Passos --}}
                @if($isConnected && $token)
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Próximos Passos
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                    <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">1</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Configure seus produtos</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Adicione informações necessárias para publicação</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                    <div class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">2</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Publique no ML</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Envie seus produtos para o marketplace</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                    <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">3</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Configure sincronização</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Ative a sincronização automática de estoque</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                                    <div class="w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">4</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Gerencie pedidos</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Importe e processe vendas automaticamente</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Como Funciona --}}
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                                </svg>
                                Como Funciona
                            </h3>
                            <ol class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">1</span>
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        Clique em <span class="font-semibold text-slate-900 dark:text-slate-100">"Conectar com Mercado Livre"</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">2</span>
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        Você será redirecionado para o Mercado Livre para autorizar o acesso
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">3</span>
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        Após autorizar, você retornará automaticamente ao sistema
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-xs font-bold">4</span>
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        Pronto! Sua conta estará conectada e sincronizada
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                @endif

                {{-- Ajuda --}}
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/50 dark:to-purple-950/50 backdrop-blur shadow-xl">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                            </svg>
                            Precisa de Ajuda?
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Consulte nossa documentação completa ou entre em contato com o suporte.
                        </p>
                        <div class="space-y-2">
                            <a href="https://developers.mercadolivre.com.br/" target="_blank" 
                               class="block w-full px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors border border-slate-200 dark:border-slate-700 text-center">
                                🔗 Portal ML Developer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Notifications --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const type = event[0].type || 'info';
                const message = event[0].message || 'Notification';
                
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