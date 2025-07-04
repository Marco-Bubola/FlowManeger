

<div>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('categories.index') }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 mr-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <svg class="h-8 w-8 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Categoria</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" 
                         style="background-color: {{ $hexcolor_category }}">
                        <i class="{{ $icone }}"></i>
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">{{ $name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit.prevent="save" class="space-y-8">
            <!-- Informações Básicas -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informações Básicas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Categoria *</label>
                            <input wire:model="name" type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('name') border-red-500 @enderror"
                                   placeholder="Digite o nome da categoria">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <input wire:model="desc_category" type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Descrição curta da categoria">
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                            <select wire:model="type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('type') border-red-500 @enderror">
                                <option value="">Selecione o tipo</option>
                                <option value="product">Produto</option>
                                <option value="transaction">Transação</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="is_active" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aparência -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                        </svg>
                        Aparência
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                            <div class="flex items-center space-x-3">
                                <input wire:model="hexcolor_category" type="color" 
                                       class="h-10 w-20 border border-gray-300 rounded-lg cursor-pointer">
                                <input wire:model="hexcolor_category" type="text" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                       placeholder="#6366f1">
                            </div>
                        </div>

                        <!-- Ícone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ícone</label>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" 
                                     style="background-color: {{ $hexcolor_category }}">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <select wire:model="icone" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="fas fa-tag">Tag</option>
                                    <option value="fas fa-box">Caixa</option>
                                    <option value="fas fa-shopping-cart">Carrinho</option>
                                    <option value="fas fa-dollar-sign">Dólar</option>
                                    <option value="fas fa-credit-card">Cartão</option>
                                    <option value="fas fa-chart-line">Gráfico</option>
                                    <option value="fas fa-home">Casa</option>
                                    <option value="fas fa-car">Carro</option>
                                    <option value="fas fa-utensils">Utensílios</option>
                                    <option value="fas fa-medkit">Kit Médico</option>
                                    <option value="fas fa-graduation-cap">Educação</option>
                                    <option value="fas fa-gamepad">Entretenimento</option>
                                    <option value="fas fa-plane">Viagem</option>
                                    <option value="fas fa-gift">Presente</option>
                                    <option value="fas fa-tools">Ferramentas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configurações Avançadas -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Configurações Avançadas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Categoria Pai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria Pai</label>
                            <select wire:model="parent_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Nenhuma (categoria principal)</option>
                                @foreach($categories as $categoryOption)
                                    <option value="{{ $categoryOption->id_category }}">{{ $categoryOption->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipo de Transação -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Transação</label>
                            <select wire:model="tipo" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Não definido</option>
                                <option value="gasto">Gasto</option>
                                <option value="receita">Receita</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>

                        <!-- Limite de Orçamento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Orçamento</label>
                            <input wire:model="limite_orcamento" type="number" step="0.01" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="0.00">
                        </div>

                        <!-- Banco -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Banco</label>
                            <select wire:model="id_bank" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Selecione um banco</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cliente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                            <select wire:model="id_clients" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Selecione um cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Compartilhável -->
                        <div class="flex items-center">
                            <input wire:model="compartilhavel" type="checkbox" 
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                            <label class="ml-3 block text-sm font-medium text-gray-700">
                                Categoria compartilhável
                            </label>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <input wire:model="tags" type="text" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               placeholder="Separe as tags com vírgula">
                    </div>

                    <!-- Descrição Detalhada -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição Detalhada</label>
                        <textarea wire:model="descricao_detalhada" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  placeholder="Descrição detalhada da categoria..."></textarea>
                    </div>

                    <!-- Observações -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea wire:model="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  placeholder="Observações adicionais..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('categories.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

