<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 dark:from-zinc-900 dark:via-purple-900/20 dark:to-indigo-900/20">
    <!-- Header -->
    <div class="w-full px-6 py-8 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 shadow-xl">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="{{ route('cofrinhos.index') }}" wire:navigate
                   class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-white text-xl"></i>
                </a>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Criar Novo Cofrinho</h1>
                    <p class="text-white/90 text-lg">Defina sua meta e comece a economizar</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-8">
                <form wire:submit="save">
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

                        <!-- Dicas -->
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

        <!-- Seção de Exemplos -->
        <div class="mt-8 bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-gray-200 dark:border-zinc-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="bi bi-star-fill text-yellow-500 mr-2"></i>Exemplos de Cofrinhos
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center mb-2">
                        <i class="bi bi-airplane text-blue-600 text-xl mr-3"></i>
                        <span class="font-semibold text-gray-900 dark:text-white">Viagem dos Sonhos</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meta: R$ 5.000,00</p>
                </div>
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center mb-2">
                        <i class="bi bi-car-front text-green-600 text-xl mr-3"></i>
                        <span class="font-semibold text-gray-900 dark:text-white">Carro Novo</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meta: R$ 30.000,00</p>
                </div>
                <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl p-4 border border-orange-200 dark:border-orange-800">
                    <div class="flex items-center mb-2">
                        <i class="bi bi-house text-orange-600 text-xl mr-3"></i>
                        <span class="font-semibold text-gray-900 dark:text-white">Casa Própria</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meta: R$ 80.000,00</p>
                </div>
            </div>
        </div>
    </div>
</div>
