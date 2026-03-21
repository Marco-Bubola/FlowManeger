{{-- ============================================================
     SHOPEE SETTINGS — Configurações de Integração
     Layout: "Planta Baixa" responsivo (iPhone 15 393px → Desktop)
     ============================================================ --}}
<div class="shopee-settings-page min-h-screen bg-gray-50 dark:bg-gray-900">

    {{-- ─── Cabeçalho ─── --}}
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

        {{-- Breadcrumb / Título --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);">
                {{-- Logo Shopee (SVG inline simplificado) --}}
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.686 2 6 4.686 6 8c0 1.657.672 3.157 1.757 4.243A6.956 6.956 0 0 0 5 18v2h14v-2a6.956 6.956 0 0 0-2.757-5.757A5.978 5.978 0 0 0 18 8c0-3.314-2.686-6-6-6z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Shopee — Configurações</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Conecte sua loja Shopee ao FlowManager</p>
            </div>
        </div>

        {{-- ─── Card Principal de Status ─── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">

            {{-- Faixa de status --}}
            <div class="px-5 py-3 flex items-center gap-2
                @if($isConnected) bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-800
                @else bg-orange-50 dark:bg-orange-900/20 border-b border-orange-100 dark:border-orange-800 @endif">
                @if($isConnected)
                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-sm font-semibold text-green-700 dark:text-green-400">Loja conectada</span>
                @else
                    <span class="w-2.5 h-2.5 bg-orange-400 rounded-full"></span>
                    <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">Não conectado</span>
                @endif
            </div>

            <div class="p-5">
                @if($isConnected && $token)
                    {{-- Informações da loja conectada --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Loja</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $token->shop_name ?? 'Loja #' . $token->shop_id }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Shop ID</p>
                            <p class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ $token->shop_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Região</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $token->region }}</p>
                        </div>
                        @if($expiresAt)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Token expira em</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $expiresAt }}</p>
                        </div>
                        @endif
                    </div>

                    <button wire:click="disconnect"
                            wire:confirm="Desconectar a loja Shopee irá pausar todas as sincronizações. Confirmar?"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-medium
                                   bg-red-50 text-red-700 border border-red-200
                                   hover:bg-red-100 transition-colors">
                        <span wire:loading.remove wire:target="disconnect">Desconectar loja</span>
                        <span wire:loading wire:target="disconnect">Desconectando...</span>
                    </button>

                @else
                    {{-- Instruções de conexão --}}
                    <div class="mb-5">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Para sincronizar produtos e estoque com a Shopee, você precisa conectar sua loja.
                            Você será redirecionado para a página de autorização da Shopee Open Platform.
                        </p>

                        {{-- Steps de configuração --}}
                        <div class="space-y-2 mb-5">
                            @foreach([
                                ['1', 'Configure as credenciais em <code>config/services.php</code> (SHOPEE_PARTNER_ID e SHOPEE_PARTNER_KEY)'],
                                ['2', 'Clique em "Conectar Shopee" abaixo'],
                                ['3', 'Autorize o FlowManager no painel Seller Center'],
                                ['4', 'Você será redirecionado de volta automaticamente'],
                            ] as [$step, $text])
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold
                                             flex items-center justify-center flex-shrink-0 mt-0.5">{{ $step }}</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{!! $text !!}</p>
                            </div>
                            @endforeach
                        </div>

                        <button wire:click="connect"
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold text-white
                                       transition-all duration-200 hover:opacity-90 active:scale-95"
                                style="background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);">
                            <span wire:loading.remove wire:target="connect">
                                <svg class="inline-block w-4 h-4 mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Conectar Shopee
                            </span>
                            <span wire:loading wire:target="connect">Redirecionando...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Card de Informações da API ─── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Variáveis de Ambiente Necessárias
            </h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 font-mono text-xs space-y-1 text-gray-700 dark:text-gray-300">
                <p><span class="text-blue-600 dark:text-blue-400">SHOPEE_PARTNER_ID</span>=seu_partner_id</p>
                <p><span class="text-blue-600 dark:text-blue-400">SHOPEE_PARTNER_KEY</span>=sua_partner_key</p>
                <p><span class="text-blue-600 dark:text-blue-400">SHOPEE_REDIRECT_URI</span>={{ config('app.url') }}/shopee/auth/callback</p>
                <p><span class="text-blue-600 dark:text-blue-400">SHOPEE_ENVIRONMENT</span>=sandbox <span class="text-gray-400"># ou production</span></p>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Obtenha suas credenciais no
                <a href="https://open.shopee.com" target="_blank" rel="noopener noreferrer"
                   class="text-orange-600 hover:underline">Shopee Open Platform</a>.
            </p>
        </div>

    </div>
</div>
