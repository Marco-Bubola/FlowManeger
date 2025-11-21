<div class="w-full">
    <!-- Header -->
    <x-sales-header title="Criar Novo Cofrinho" subtitle="Defina sua meta e comece a economizar">
        <x-slot name="actions">
            <div class="hidden sm:flex items-center gap-3">
                <span class="text-sm text-gray-400">Salvar: <kbd class="bg-gray-100 text-xs px-2 py-0.5 rounded">Ctrl</kbd>+<kbd class="bg-gray-100 text-xs px-2 py-0.5 rounded">S</kbd></span>
            </div>
        </x-slot>
    </x-sales-header>

    <!-- Formulário -->
    <div class="w-full px-6 py-8">
        <div class="bg-white/70 dark:bg-zinc-800/60 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 overflow-hidden mx-4 backdrop-blur-sm">
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
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-zinc-700 dark:text-white transition-all duration-200"
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
                                         class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-zinc-700 dark:text-white transition-all duration-200"
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
                                <textarea wire:model="description" rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:text-white resize-none transition-all duration-200"
                                          placeholder="Descreva o objetivo do seu cofrinho, quando pretende alcançar a meta, etc..."></textarea>
                                @error('description')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <i class="bi bi-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Coluna Direita - Dicas e Informações -->
                        <div class="space-y-6">
                            <!-- Dicas para o Sucesso -->
                            <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-800">
                                <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-300 mb-4">
                                    <i class="bi bi-lightbulb text-yellow-500 mr-2"></i>Dicas para o Sucesso
                                </h3>
                                <ul class="space-y-2 text-sm text-purple-700 dark:text-purple-300">
                                    <li class="flex items-start">
                                        <i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>
                                        <span>Defina metas realistas e alcançáveis</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>
                                        <span>Seja específico sobre o que deseja conquistar</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>
                                        <span>Estabeleça um prazo para alcançar sua meta</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>
                                        <span>Faça depósitos regulares, mesmo que pequenos</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Exemplos de Cofrinhos -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border border-green-200 dark:border-green-800">
                                <h3 class="text-lg font-semibold text-green-800 dark:text-green-300 mb-4">
                                    <i class="bi bi-star-fill text-yellow-500 mr-2"></i>Exemplos de Cofrinhos
                                </h3>
                                <div class="space-y-3 text-sm text-green-700 dark:text-green-300">
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-700 rounded-lg">
                                        <span>🏖️ Férias em Cancún</span>
                                        <span class="font-semibold">R$ 8.000</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-700 rounded-lg">
                                        <span>🚗 Carro Zero</span>
                                        <span class="font-semibold">R$ 45.000</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-700 rounded-lg">
                                        <span>🏠 Casa Própria</span>
                                        <span class="font-semibold">R$ 150.000</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-700 rounded-lg">
                                        <span>💻 Notebook Gamer</span>
                                        <span class="font-semibold">R$ 5.000</span>
                                    </div>
                                </div>
                            </div>
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
                            <i class="bi bi-piggy-bank mr-2"></i>Criar Cofrinho
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
