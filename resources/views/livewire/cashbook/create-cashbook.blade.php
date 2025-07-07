<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Back Button and Title -->
                <div class="flex items-center space-x-4">
                    <button wire:click="cancel" 
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nova Transação</h1>
                        <p class="text-sm text-gray-500">Adicione uma nova transação ao seu livro caixa</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button wire:click="cancel" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button wire:click="save" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-6">
                <form wire:submit="save">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Value -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign text-blue-600"></i>
                                    <span>Valor *</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input wire:model="value" type="number" step="0.01" min="0.01" 
                                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('value') border-red-500 @enderror"
                                           placeholder="0,00">
                                </div>
                                @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-comment text-blue-600"></i>
                                    <span>Descrição</span>
                                </label>
                                <input wire:model="description" type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                       placeholder="Descrição da transação">
                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-blue-600"></i>
                                    <span>Data *</span>
                                </label>
                                <input wire:model="date" type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                                @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tags text-blue-600"></i>
                                    <span>Categoria *</span>
                                </label>
                                <select wire:model="category_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-exchange-alt text-blue-600"></i>
                                    <span>Tipo *</span>
                                </label>
                                <select wire:model="type_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type_id') border-red-500 @enderror">
                                    <option value="">Selecione um tipo</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id_type }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Client -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span>Cliente</span>
                                </label>
                                <select wire:model="client_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('client_id') border-red-500 @enderror">
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Segment -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-layer-group text-blue-600"></i>
                                    <span>Segmento</span>
                                </label>
                                <select wire:model="segment_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('segment_id') border-red-500 @enderror">
                                    <option value="">Selecione um segmento</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                                @error('segment_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Cofrinho -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-piggy-bank text-blue-600"></i>
                                    <span>Cofrinho</span>
                                </label>
                                <select wire:model="cofrinho_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cofrinho_id') border-red-500 @enderror">
                                    <option value="">Selecione um cofrinho</option>
                                    @foreach($cofrinhos as $cofrinho)
                                        <option value="{{ $cofrinho->id }}">{{ $cofrinho->name }}</option>
                                    @endforeach
                                </select>
                                @error('cofrinho_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Note -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note text-blue-600"></i>
                                    <span>Observações</span>
                                </label>
                                <textarea wire:model="note" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('note') border-red-500 @enderror"
                                          placeholder="Observações adicionais..."></textarea>
                                @error('note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Attachment -->
                            <div>
                                <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paperclip text-blue-600"></i>
                                    <span>Anexo</span>
                                </label>
                                <input wire:model="attachment" type="file" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('attachment') border-red-500 @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                @error('attachment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-sm text-gray-500">Máximo 2MB</p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="flex items-center space-x-2">
                                    <input wire:model="is_pending" type="checkbox" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">Transação pendente</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="cancel" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Salvar Transação
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
