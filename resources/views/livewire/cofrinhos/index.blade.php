<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 dark:from-zinc-900 dark:via-purple-900/20 dark:to-indigo-900/20">
    <!-- Header -->
    <div class="w-full px-6 py-8 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4 mb-6 lg:mb-0">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-piggy-bank text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-white mb-2">Meus Cofrinhos</h1>
                        <p class="text-white/90 text-lg">"O segredo do sucesso é a constância do propósito." - Benjamin Disraeli</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 lg:text-right">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center">
                        <div class="text-white/80 text-sm font-medium">Total Acumulado</div>
                        <div class="text-white text-2xl font-bold">R$ {{ number_format(collect($cofrinhos)->sum('valor_acumulado'), 2, ',', '.') }}</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center">
                        <div class="text-white/80 text-sm font-medium">Cofrinhos</div>
                        <div class="text-white text-2xl font-bold">{{ count($cofrinhos) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Botão Criar Novo Cofrinho -->
        <div class="mb-8">
            <a href="{{ route('cofrinhos.create') }}" wire:navigate
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="bi bi-plus-circle-fill text-xl mr-2"></i>
                Criar Novo Cofrinho
            </a>
        </div>

        <!-- Ranking dos Cofrinhos -->
        @if(collect($ranking)->sum('crescimento') > 0)
        <div class="mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mr-4">
                        <i class="bi bi-trophy-fill text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Ranking do Mês</h2>
                        <p class="text-gray-600 dark:text-gray-400">Cofrinhos que mais cresceram este mês</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    @foreach($ranking as $index => $item)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-zinc-700 dark:to-blue-900/20 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4
                                        @if($index == 0) bg-gradient-to-r from-yellow-400 to-orange-500 text-white
                                        @elseif($index == 1) bg-gradient-to-r from-gray-400 to-gray-600 text-white
                                        @else bg-gradient-to-r from-orange-600 to-red-600 text-white @endif">
                                <span class="font-bold text-sm">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $item['nome'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Meta: R$ {{ number_format($item['meta'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-600 font-bold text-lg">+R$ {{ number_format($item['crescimento'], 2, ',', '.') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total: R$ {{ number_format($item['valor_acumulado'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Lista de Cofrinhos -->
        @if(count($cofrinhos) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($cofrinhos as $cofrinho)
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <!-- Header do Card -->
                <div class="bg-gradient-to-r from-purple-500 to-blue-500 p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="bi bi-piggy-bank text-white text-xl"></i>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('cofrinhos.show', $cofrinho['id']) }}" wire:navigate
                               class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="bi bi-eye text-white text-sm"></i>
                            </a>
                            <a href="{{ route('cofrinhos.edit', $cofrinho['id']) }}" wire:navigate
                               class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="bi bi-pencil text-white text-sm"></i>
                            </a>
                            <button wire:click="confirmDelete({{ $cofrinho['id'] }})"
                                    class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-red-500/50 transition-colors">
                                <i class="bi bi-trash text-white text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold mb-2">{{ $cofrinho['nome'] }}</h3>
                    <p class="text-white/90 text-sm">{{ $cofrinho['cashbooks_count'] }} transações</p>
                </div>

                <!-- Conteúdo do Card -->
                <div class="p-6">
                    <!-- Valor Acumulado -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Valor Acumulado</span>
                            <span class="text-2xl font-bold text-green-600">R$ {{ number_format($cofrinho['valor_acumulado'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Meta</span>
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">R$ {{ number_format($cofrinho['meta_valor'], 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Barra de Progresso -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Progresso</span>
                            <span class="text-sm font-semibold text-purple-600">{{ number_format($cofrinho['porcentagem'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-3 rounded-full transition-all duration-500"
                                 style="width: {{ min($cofrinho['porcentagem'], 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                     @if($cofrinho['status'] === 'ativo') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                     @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                            <i class="bi bi-circle-fill text-xs mr-1"></i>{{ ucfirst($cofrinho['status']) }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            @if($cofrinho['valor_acumulado'] >= $cofrinho['meta_valor'])
                                <i class="bi bi-check-circle-fill text-green-500 mr-1"></i>Meta Alcançada!
                            @else
                                <i class="bi bi-clock text-purple-500 mr-1"></i>Em Progresso
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Estado Vazio -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-r from-purple-100 to-blue-100 dark:from-purple-900/20 dark:to-blue-900/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-piggy-bank text-purple-500 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Nenhum cofrinho criado ainda</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                Crie seu primeiro cofrinho para começar a economizar e acompanhar suas metas financeiras.
            </p>
            <a href="{{ route('cofrinhos.create') }}" wire:navigate
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="bi bi-plus-circle-fill text-xl mr-2"></i>
                Criar Primeiro Cofrinho
            </a>
        </div>
        @endif
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Confirmar Exclusão</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-2">Tem certeza que deseja excluir o cofrinho?</p>
                @if($deletingCofrinho)
                <p class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $deletingCofrinho->nome }}</p>
                @endif
                <div class="flex gap-3 justify-center">
                    <button wire:click="cancelDelete" 
                            class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deleteCofrinho" 
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <i class="bi bi-check-circle-fill mr-2"></i>{{ session('success') }}
    </div>
    @endif
</div>
