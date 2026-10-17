{{-- ============================================================
     SHOPEE SETTINGS — Configurações de Integração
     Layout: "Planta Baixa" responsivo (iPhone 15 393px → Desktop)
     ============================================================ --}}
<div class="shopee-settings-page min-h-screen">

    <div class="w-full px-4 pt-4 pb-6 sm:px-6 lg:px-8">

        {{-- ─── Cabeçalho ─── --}}
        <x-page-header title="Configurações" icon="bi-gear" section="shopee" tabs-section="shopee" tabs-active="config"
                       subtitle="Conecte sua loja Shopee ao FlowManager">
            <x-slot:meta>
                @if($isConnected)
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-plug"></i>Loja conectada</span>
                    @if($expiresAt)
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300"><i class="bi bi-clock"></i>Token até {{ $expiresAt }}</span>
                    @endif
                @else
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200"><i class="bi bi-plug"></i>Não conectado</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                @unless($isConnected)
                    <button type="button" wire:click="connect" wire:loading.attr="disabled" wire:target="connect" title="Conectar Shopee"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-md shadow-orange-500/25 transition whitespace-nowrap disabled:opacity-60">
                        <i class="bi bi-link-45deg"></i>
                        <span class="hidden sm:inline" wire:loading.remove wire:target="connect">Conectar Shopee</span>
                        <span class="hidden sm:inline" wire:loading wire:target="connect">Redirecionando...</span>
                    </button>
                @endunless
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6 items-start">
        {{-- ─── Card Principal de Status ─── --}}
        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

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
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-md shadow-orange-500/25 transition-all duration-200 active:scale-95">
                            <span wire:loading.remove wire:target="connect"><i class="bi bi-link-45deg"></i> Conectar Shopee</span>
                            <span wire:loading wire:target="connect">Redirecionando...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Card de Informações da API ─── --}}
        <div class="lg:col-span-2 min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i class="bi bi-info-circle text-gray-400"></i>
                Variáveis de Ambiente Necessárias
            </h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 font-mono text-xs space-y-1 text-gray-700 break-all dark:text-gray-300">
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
</div>
