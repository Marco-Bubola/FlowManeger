<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 dark:from-zinc-900 dark:via-purple-900/20 dark:to-indigo-900/20">
    <!-- Header -->
    <div class="w-full px-6 py-8 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="{{ route('cofrinhos.index') }}" wire:navigate
                   class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-white text-xl"></i>
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-piggy-bank text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-white mb-2">{{ $cofrinho->nome }}</h1>
                        <p class="text-white/90 text-lg">Acompanhe o progresso da sua meta</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Resumo do Cofrinho -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Progresso Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-8">
                    <div class="text-center mb-8">
                        <div class="text-6xl font-bold text-transparent bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text mb-4">
                            {{ number_format($porcentagem, 1) }}%
                        </div>
                        <div class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            R$ {{ number_format($valor_acumulado, 2, ',', '.') }}
                        </div>
                        <div class="text-lg text-gray-500 dark:text-gray-400">
                            de R$ {{ number_format($cofrinho->meta_valor, 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- Barra de Progresso -->
                    <div class="relative mb-6">
                        <div class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-6">
                            <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-6 rounded-full transition-all duration-1000 relative overflow-hidden"
                                 style="width: {{ min($porcentagem, 100) }}%">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
                            </div>
                        </div>
                        <div class="absolute top-0 left-0 h-6 flex items-center justify-center w-full">
                            <span class="text-xs font-semibold text-white drop-shadow-lg">
                                {{ number_format($porcentagem, 1) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="text-center">
                        @if($valor_acumulado >= $cofrinho->meta_valor)
                        <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full font-semibold">
                            <i class="bi bi-trophy-fill mr-2"></i>Meta Alcançada!
                        </div>
                        @else
                        <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-full font-semibold">
                            <i class="bi bi-hourglass-split mr-2"></i>Em Progresso
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informações Gerais -->
            <div class="space-y-6">
                <!-- Card Status -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h3>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                     @if($cofrinho->status === 'ativo') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                     @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                            {{ ucfirst($cofrinho->status) }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Criado em:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $cofrinho->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Transações:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ count($transacoes) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card Ações -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ações</h3>
                    <div class="space-y-3">
                        <a href="{{ route('cofrinhos.edit', $cofrinho->id) }}" wire:navigate
                           class="w-full flex items-center justify-center px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg transition-all duration-200">
                            <i class="bi bi-pencil mr-2"></i>Editar Cofrinho
                        </a>
                        <a href="{{ route('cashbook.index') }}" wire:navigate
                           class="w-full flex items-center justify-center px-4 py-2 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg transition-all duration-200">
                            <i class="bi bi-plus-circle mr-2"></i>Nova Transação
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-arrow-up-circle text-green-600 text-xl"></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Receitas</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    R$ {{ number_format($estatisticas['total_receitas'], 2, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $estatisticas['qtd_receitas'] }} transações
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-arrow-down-circle text-red-600 text-xl"></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Despesas</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    R$ {{ number_format($estatisticas['total_despesas'], 2, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $estatisticas['qtd_despesas'] }} transações
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar-month text-purple-600 text-xl"></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Este Mês</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $estatisticas['transacoes_mes'] }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    transações
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-flag text-blue-600 text-xl"></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Restante</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    R$ {{ number_format($estatisticas['valor_restante'], 2, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    para a meta
                </div>
            </div>
        </div>

        <!-- Descrição -->
        @if($cofrinho->description)
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-chat-text text-blue-600 mr-2"></i>Descrição
            </h3>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $cofrinho->description }}</p>
        </div>
        @endif

        <!-- Histórico de Transações -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <i class="bi bi-clock-history text-indigo-600 mr-2"></i>Histórico de Transações
                </h3>
                <a href="{{ route('cashbook.index') }}" wire:navigate
                   class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg transition-all duration-200">
                    <i class="bi bi-plus mr-2"></i>Nova Transação
                </a>
            </div>

            @if(count($transacoes) > 0)
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach($transacoes as $transacao)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-700 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4
                                    @if($transacao['type_id'] == 1) bg-green-100 dark:bg-green-900/20
                                    @else bg-red-100 dark:bg-red-900/20 @endif">
                            <i class="bi bi-{{ $transacao['type_id'] == 1 ? 'arrow-up-circle' : 'arrow-down-circle' }} 
                               text-{{ $transacao['type_id'] == 1 ? 'green' : 'red' }}-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $transacao['type_id'] == 1 ? 'Receita' : 'Despesa' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($transacao['created_at'])->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold 
                                    @if($transacao['type_id'] == 1) text-green-600 
                                    @else text-red-600 @endif">
                            {{ $transacao['type_id'] == 1 ? '+' : '-' }}R$ {{ number_format($transacao['value'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-clock-history text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nenhuma transação encontrada</h4>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Adicione sua primeira transação para começar a acompanhar o progresso</p>
                <a href="{{ route('cashbook.index') }}" wire:navigate
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all duration-200">
                    <i class="bi bi-plus-circle mr-2"></i>Adicionar Transação
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
