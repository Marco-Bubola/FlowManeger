<div class="w-full">
    <!-- Header Compacto Modernizado -->
    <x-sales-header
        title="Nova Fatura"
        description="Adicione uma nova fatura ao sistema"
        :back-route="route('invoices.index')"
        :current-step="$currentStep ?? 1"
        :steps="[
            [
                'title' => 'Informações',
                'description' => 'Detalhes da fatura',
                'icon' => 'bi-receipt',
                'gradient' => 'from-indigo-500 to-purple-500',
                'connector_gradient' => 'from-indigo-500 to-purple-500'
            ]
        ]" />

    <!-- Conteúdo Principal -->
    <div class="relative flex-1">
        <form wire:submit.prevent="save">
            <div class="flex flex-col">
                <div class="flex-1 space-y-4 animate-fadeIn">
                    <!-- Card Container Principal -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-6 shadow-lg shadow-slate-200/30 dark:shadow-slate-900/30 border border-white/20 dark:border-slate-700/50 w-full">

                        <!-- Seção superior: Tipo / Descrição / Valor (3 colunas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                            <!-- Tipo de Transação -->
                            <div class="group space-y-2">
                                <label for="type" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-tag text-white"></i>
                                    </div>
                                    Tipo
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-tag text-slate-400 group-hover:text-emerald-500 transition-colors duration-200"></i>
                                    </div>
                                    <select wire:model="type" id="type"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('type') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-emerald-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                        <option value="">Selecione o tipo</option>
                                        <option value="receita">💰 Receita</option>
                                        <option value="despesa">💸 Despesa</option>
                                    </select>
                                </div>
                                @error('type')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="group space-y-2">
                                <label for="description" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-card-text text-white"></i>
                                    </div>
                                    Descrição
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-card-text text-slate-400 group-hover:text-purple-500 transition-colors duration-200"></i>
                                    </div>
                                    <input wire:model="description" type="text" id="description"
                                        placeholder="Descrição..."
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                </div>
                                @error('description')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Valor -->
                            <div class="group space-y-2">
                                <x-currency-input
                                    name="value"
                                    id="value"
                                    wireModel="value"
                                    label="Valor"
                                    icon="bi-currency-dollar"
                                    iconColor="yellow"
                                    :required="true"
                                    width="w-full"
                                />
                            </div>
                        </div>

                        <!-- Grid: Categoria / Data -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                            <!-- Categoria -->
                            <div class="group space-y-2">
                                <label for="category_id" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-rose-400 to-pink-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-tags-fill text-white"></i>
                                    </div>
                                    Categoria
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-tags-fill text-slate-400 group-hover:text-rose-500 transition-colors duration-200"></i>
                                    </div>
                                    <select wire:model.live="category_id" id="category_id"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-rose-500 focus:ring-rose-500/20 hover:border-rose-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                        <option value="">Selecione uma categoria</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category_id')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Data -->
                            <div class="group space-y-2">
                                <label for="date" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-calendar text-white"></i>
                                    </div>
                                    Data
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-calendar text-slate-400 group-hover:text-blue-500 transition-colors duration-200"></i>
                                    </div>
                                    <input wire:model="date" type="date" id="date"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('date') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                </div>
                                @error('date')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Grid: Cliente / Vencimento / Parcelas (3 colunas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <!-- Cliente -->
                            <div class="group space-y-2">
                                <label for="client_id" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-person text-white"></i>
                                    </div>
                                    Cliente
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-person text-slate-400 group-hover:text-cyan-500 transition-colors duration-200"></i>
                                    </div>
                                    <select wire:model.live="client_id" id="client_id"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('client_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-cyan-500 focus:ring-cyan-500/20 hover:border-cyan-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('client_id')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Data de Vencimento -->
                            <div class="group space-y-2">
                                <label for="due_date" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-clock text-white"></i>
                                    </div>
                                    Vencimento
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-clock text-slate-400 group-hover:text-orange-500 transition-colors duration-200"></i>
                                    </div>
                                    <input wire:model="due_date" type="date" id="due_date"
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('due_date') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-orange-500 focus:ring-orange-500/20 hover:border-orange-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                </div>
                                @error('due_date')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Parcelas -->
                            <div class="group space-y-2">
                                <label for="installments" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-lg mr-3 shadow-sm">
                                        <i class="bi bi-list-ol text-white"></i>
                                    </div>
                                    Parcelas
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-list-ol text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"></i>
                                    </div>
                                    <input wire:model="installments" type="text" id="installments"
                                        placeholder="1x, 2x, 3x..."
                                        class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                        {{ $errors->has('installments') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                        focus:ring-2 focus:outline-none transition-all duration-200 shadow-sm">
                                </div>
                                @error('installments')
                                <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação (centralizados, estilo moderno) -->
                    <div class="flex items-center justify-center gap-4 mt-6">
                        <button type="button" wire:click="$parent.activeForm = 'list'"
                            class="inline-flex items-center gap-3 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-700/80 border border-slate-200 dark:border-slate-600 rounded-full hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all duration-200">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200">
                                <i class="bi bi-x-lg"></i>
                            </span>
                            Cancelar
                        </button>

                        <button type="submit"
                            class="inline-flex items-center gap-3 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500/30 transition-all duration-200 shadow-lg hover:shadow-2xl">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white">
                                <i class="bi bi-check-lg"></i>
                            </span>
                            Salvar Fatura
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Estilos Customizados -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
    </style>
</div>
