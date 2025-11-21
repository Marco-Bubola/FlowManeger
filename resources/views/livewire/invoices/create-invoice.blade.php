<div class="w-full">
    <!-- Modern Full-Width Invoice Creation Form -->
    <div class="relative  bg-gradient-to-br overflow-hidden">


            <!-- Modern Header (componentizado para manter consistência com outras páginas) -->
            <x-sales-header :back-route="route('invoices.index')" title="Nova Transação" description="Crie transações de forma rápida e intuitiva">
                <x-slot name="actions">
                    <div class="hidden md:flex items-center space-x-4">
                        <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
                            <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow">Ctrl</kbd>
                            <span class="text-gray-500 dark:text-gray-400">+</span>
                            <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow">S</kbd>
                            <span class="text-xs text-gray-600 dark:text-gray-400 ml-2">Salvar</span>
                        </div>
                    </div>
                </x-slot>
            </x-sales-header>

        <!-- Main Content Container -->
        <div class="relative z-10 max-w-7xl mx-auto px-6 py-8">
            <form wire:submit.prevent="save" class="space-y-12">

                <!-- Informações Básicas da Transação -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white">💼 Informações Básicas</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Defina o tipo e os detalhes principais da transação</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Tipo de Transação -->
                        <div class="space-y-3">
                            <label for="type" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                Tipo de Transação
                                <span class="text-red-500 ml-2">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model="type" id="type"
                                        class="w-full pl-4 pr-10 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl appearance-none">
                                    <option value="">Selecione o tipo</option>
                                    <option value="receita">💰 Receita (Entrada)</option>
                                    <option value="despesa">💸 Despesa (Saída)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('type')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div class="space-y-3">
                            <label for="description" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </div>
                                Descrição
                                <span class="text-red-500 ml-2">*</span>
                            </label>
                            <input wire:model="description" type="text" id="description"
                                   placeholder="Digite uma descrição detalhada..."
                                   class="w-full pl-4 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500/30 focus:border-purple-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl placeholder-gray-400 dark:placeholder-gray-500">
                            @error('description')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Valor -->
                        <div class="space-y-3">
                            <label for="value" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                Valor
                                <span class="text-red-500 ml-2">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-medium">R$</span>
                                </div>
                                <input wire:model="value" type="text" id="value"
                                       placeholder="0,00"
                                       class="w-full pl-12 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-500/30 focus:border-yellow-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl placeholder-gray-400 dark:placeholder-gray-500">
                            </div>
                            @error('value')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Categorização e Cliente -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white">🏷️ Categorização</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Organize e classifique sua transação</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Categoria com Ícones Visuais -->
                        <div class="space-y-3">
                            <label for="category_id" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                Categoria
                                <span class="text-red-500 ml-2">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model.live="category_id" id="category_id"
                                        class="w-full pl-4 pr-10 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-rose-500/30 focus:border-rose-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl appearance-none">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}" class="flex items-center">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                @if($category_id)
                                    @php $selectedCategory = $categories->where('id_category', $category_id)->first(); @endphp
                                    @if($selectedCategory && $selectedCategory->icone)
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="{{ $selectedCategory->icone }} text-rose-500"></i>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            @error('category_id')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Cliente com Avatar -->
                        <div class="space-y-3">
                            <label for="client_id" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                Cliente
                                <span class="text-gray-400 text-xs ml-2">(opcional)</span>
                            </label>
                            <div class="relative">
                                <select wire:model.live="client_id" id="client_id"
                                        class="w-full pl-4 pr-10 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl appearance-none">
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                @if($client_id)
                                    @php $selectedClient = $clients->find($client_id); @endphp
                                    @if($selectedClient)
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            @if($selectedClient->caminho_foto)
                                                <img src="{{ $selectedClient->caminho_foto }}" alt="{{ $selectedClient->name }}" class="w-6 h-6 rounded-full object-cover">
                                            @else
                                                <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                                    {{ strtoupper(substr($selectedClient->name, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                            @error('client_id')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Detalhes de Tempo e Pagamento -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white">📅 Detalhes Temporais</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Configure datas e forma de pagamento</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Data da Transação -->
                        <div class="space-y-3">
                            <label for="date" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                Data da Transação
                                <span class="text-red-500 ml-2">*</span>
                            </label>
                            <input wire:model="date" type="date" id="date"
                                   class="w-full pl-4 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl">
                            @error('date')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Data de Vencimento -->
                        <div class="space-y-3">
                            <label for="due_date" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                Data de Vencimento
                                <span class="text-gray-400 text-xs ml-2">(opcional)</span>
                            </label>
                            <input wire:model="due_date" type="date" id="due_date"
                                   class="w-full pl-4 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-orange-500/30 focus:border-orange-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl">
                            @error('due_date')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Parcelas -->
                        <div class="space-y-3">
                            <label for="installments" class="flex items-center text-sm font-bold text-gray-900 dark:text-white">
                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                Parcelas
                                <span class="text-gray-400 text-xs ml-2">(opcional)</span>
                            </label>
                            <input wire:model="installments" type="text" id="installments"
                                   placeholder="1x, 2x, 3x, à vista..."
                                   class="w-full pl-4 pr-4 py-4 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all duration-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium shadow-lg hover:shadow-xl placeholder-gray-400 dark:placeholder-gray-500">
                            @error('installments')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200/50 dark:border-gray-700/50">
                    <button type="button" wire:click="$parent.activeForm = 'list'"
                            class="inline-flex items-center px-8 py-4 text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-500/30 transition-all duration-300 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </button>

                    <button type="submit"
                            class="inline-flex items-center px-12 py-4 text-base font-bold text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500/30 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Salvar Transação
                    </button>
                </div>                <!-- Loading State -->
                <div wire:loading class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl max-w-sm mx-4">
                        <div class="flex flex-col items-center">
                            <div class="relative w-16 h-16 mb-6">
                                <div class="absolute inset-0 border-4 border-purple-200 dark:border-purple-700 rounded-full"></div>
                                <div class="absolute inset-0 border-4 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Salvando Transação</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Aguarde enquanto processamos sua transação...</p>
                        </div>
                    </div>
                </div>

            </form>
        </div>

    </div>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(156, 163, 175, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.6);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.8);
        }

        /* Enhanced select options */
        select option {
            padding: 12px 16px;
            background: white;
            color: #374151;
            font-weight: 500;
        }

        .dark select option {
            background: #374151;
            color: #f3f4f6;
        }

        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass {
            background: rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    const form = document.querySelector('form');
                    if (form) {
                        form.dispatchEvent(new Event('submit', { bubbles: true }));
                    }
                }
                if (e.key === 'Escape') {
                    const cancelBtn = document.querySelector('button[wire\\:click*="list"]');
                    if (cancelBtn) cancelBtn.click();
                }
            });

            // Enhanced value input formatting
            const valueInput = document.getElementById('value');
            if (valueInput) {
                valueInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value) {
                        // Convert to decimal
                        const decimal = parseFloat(value) / 100;
                        // Format as currency
                        e.target.value = decimal.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                });
            }

            // Enhanced form interactions
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.classList.add('ring-4');
                    this.parentElement.classList.add('transform', 'scale-[1.02]');
                });

                input.addEventListener('blur', function() {
                    this.classList.remove('ring-4');
                    this.parentElement.classList.remove('transform', 'scale-[1.02]');
                });
            });

            // Auto-focus first input
            const firstInput = document.querySelector('select[id="type"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 300);
            }

            // Form completion progress
            function updateFormProgress() {
                const requiredFields = ['type', 'description', 'value', 'category_id', 'date'];
                let completed = 0;

                requiredFields.forEach(fieldName => {
                    const field = document.querySelector(`#${fieldName}`);
                    if (field && field.value.trim()) {
                        completed++;
                        field.classList.add('border-emerald-400', 'ring-emerald-500/30');
                        field.classList.remove('border-gray-200', 'dark:border-gray-600');
                    } else if (field) {
                        field.classList.remove('border-emerald-400', 'ring-emerald-500/30');
                        field.classList.add('border-gray-200', 'dark:border-gray-600');
                    }
                });

                const progress = (completed / requiredFields.length) * 100;

                // Update submit button state
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    if (progress === 100) {
                        submitBtn.classList.add('animate-pulse');
                        submitBtn.disabled = false;
                    } else {
                        submitBtn.classList.remove('animate-pulse');
                    }
                }
            }

            // Monitor all form changes
            inputs.forEach(input => {
                input.addEventListener('input', updateFormProgress);
                input.addEventListener('change', updateFormProgress);
            });

            // Initial progress check
            setTimeout(updateFormProgress, 500);

            // Add smooth transitions to form elements
            inputs.forEach(input => {
                input.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            });

            // Enhanced category icon display
            const categorySelect = document.getElementById('category_id');
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    // Trigger Livewire update to show icon
                    this.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }

            // Enhanced client avatar display
            const clientSelect = document.getElementById('client_id');
            if (clientSelect) {
                clientSelect.addEventListener('change', function() {
                    // Trigger Livewire update to show avatar
                    this.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }

            // Add loading state to form
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = `
                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Salvando...
                        `;
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
</div>
