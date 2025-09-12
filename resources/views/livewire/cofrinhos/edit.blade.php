<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 dark:from-zinc-900 dark:via-purple-900/20 dark:to-indigo-900/20">
    <!-- Header -->
    <div class="w-full px-6 py-8 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 shadow-xl">
        <div class="w-full px-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('cofrinhos.index') }}" wire:navigate
                   class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-white text-xl"></i>
                </a>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Editar Cofrinho</h1>
                    <p class="text-white/90 text-lg">Atualize as informações do seu cofrinho</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <div class="w-full px-6 py-8">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 overflow-hidden mx-4">
            <div class="p-8">
                <form wire:submit="save">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Coluna Esquerda - Formulário Principal -->
                        <div class="space-y-6">
                        <!-- Nome do Cofrinho -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <i class="bi bi-piggy-bank text-purple-600 mr-2"></i>Nome do Cofrinho
                            </label>
                            <input type="text" wire:model="nome"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-zinc-700 dark:text-white transition-all duration-200"
                                   placeholder="Ex: Viagem dos Sonhos, Carro Novo, Casa Própria...">
                            @error('nome')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Meta de Valor -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <i class="bi bi-currency-dollar text-green-600 mr-2"></i>Meta de Valor
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">R$</span>
                                <input type="number" wire:model="meta_valor" step="0.01" min="0"
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-zinc-700 dark:text-white transition-all duration-200"
                                       placeholder="0,00">
                            </div>
                            @error('meta_valor')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <i class="bi bi-chat-text text-blue-600 mr-2"></i>Descrição <span class="text-gray-500 text-xs">(opcional)</span>
                            </label>
                            <textarea wire:model="description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white resize-none transition-all duration-200"
                                      placeholder="Descreva o objetivo do seu cofrinho, quando pretende alcançar a meta, etc..."></textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <i class="bi bi-toggle-on text-indigo-600 mr-2"></i>Status
                            </label>
                            <select wire:model="status"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white transition-all duration-200">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                            @error('status')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                            @enderror
                        </div>
                        </div>

                        <!-- Coluna Direita - Informações e Avisos -->
                        <div class="space-y-6">
                            <!-- Informações Atuais -->
                            <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-900/20 dark:to-blue-900/20 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                    <i class="bi bi-info-circle text-blue-600 mr-2"></i>Informações Atuais
                                </h3>
                                <div class="space-y-4">
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Criado em</div>
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $cofrinho->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Última atualização</div>
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $cofrinho->updated_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aviso sobre Alterações -->
                            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl p-6 border border-yellow-200 dark:border-yellow-800">
                                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300 mb-2">
                                    <i class="bi bi-exclamation-triangle text-yellow-600 mr-2"></i>Atenção
                                </h3>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    Alterar a meta de valor pode afetar o cálculo da porcentagem de progresso do cofrinho.
                                    As transações já realizadas não serão alteradas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end mt-8 pt-6 border-t border-gray-200 dark:border-zinc-700">
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end mt-8 pt-6 border-t border-gray-200 dark:border-zinc-700">
                        <button type="button" wire:click="cancel"
                                class="w-full sm:w-auto px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="bi bi-x-circle mr-2"></i>Cancelar
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-check-circle mr-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ações Avançadas -->
        <div class="mt-8 bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-gear-fill text-gray-600 mr-2"></i>Ações Avançadas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('cofrinhos.show', $cofrinho->id) }}" wire:navigate
                   class="flex items-center justify-center px-4 py-3 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-xl transition-all duration-200 border border-blue-200 dark:border-blue-800">
                    <i class="bi bi-eye text-xl mr-3"></i>
                    <span class="font-medium">Visualizar Detalhes</span>
                </a>
                <a href="{{ route('cashbook.index') }}" wire:navigate
                   class="flex items-center justify-center px-4 py-3 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl transition-all duration-200 border border-green-200 dark:border-green-800">
                    <i class="bi bi-plus-circle text-xl mr-3"></i>
                    <span class="font-medium">Adicionar Transação</span>
                </a>
            </div>
        </div>
    </div>
</div>
