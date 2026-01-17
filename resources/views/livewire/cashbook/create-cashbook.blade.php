<div class="w-full">
    <!-- Header Modernizado com botões de ação -->
    <x-sales-header title="Nova Transação" description="Adicione uma nova transação ao seu livro caixa" :back-route="route('cashbook.index')"
        :current-step="1" :steps="[]">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('cashbook.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-book mr-1"></i>Livro Caixa
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Nova Transação</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('cashbook.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-200 font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                <i class="bi bi-x-lg text-lg"></i>
                Cancelar
            </a>
            <button type="submit" form="create-cashbook-form" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 hover:scale-105 transition-all duration-200">
                <span wire:loading.remove wire:target="save">
                    <i class="bi bi-check-circle-fill text-lg"></i>
                    Salvar Transação
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Salvando...
                </span>
            </button>
        </x-slot>
    </x-sales-header>

    <!-- Conteúdo Principal -->
    <form id="create-cashbook-form" wire:submit.prevent="save" class="px-6 py-6">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- ========== COLUNA ESQUERDA: Formulário ========== -->
            <div class="flex-1">
                <div
                    class="bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50">

                    <!-- Informações Principais -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-cash-coin text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Informações da Transação</h3>
                                <p class="text-xs text-slate-400">Dados principais</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Valor -->
                            <div class="space-y-2">
                                <x-currency-input name="value" id="value" wireModel="value" label="Valor"
                                    icon="bi-currency-dollar" icon-color="green" :required="true" width="w-full" />
                            </div>

                            <!-- Cliente -->
                            <div class="space-y-2">
                                <label for="client_id"
                                    class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-person text-cyan-400"></i>
                                    Cliente
                                </label>
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    selectedClient: @entangle('client_id'),
                                    selectedClientName: 'Selecione...',
                                    clients: {{ $clients->toJson() }},
                                    get filteredClients() {
                                        if (!this.search) return this.clients;
                                        return this.clients.filter(client =>
                                            client.name.toLowerCase().includes(this.search.toLowerCase())
                                        );
                                    },
                                    selectClient(client) {
                                        this.selectedClient = client.id;
                                        this.selectedClientName = client.name;
                                        this.open = false;
                                        this.search = '';
                                        $wire.set('client_id', client.id);
                                    }
                                }">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                            {{ $errors->has('client_id') ? 'border-red-500' : 'hover:border-cyan-500 focus:border-cyan-500' }}
                                            focus:ring-4 focus:ring-cyan-500/20 focus:outline-none">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-person text-cyan-400"></i>
                                            <span x-text="selectedClientName"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open" x-transition @click.away="open = false"
                                        class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                        <!-- Campo de busca -->
                                        <div class="p-2 border-b border-slate-700">
                                            <div class="relative">
                                                <i
                                                    class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" @click.stop
                                                    placeholder="Pesquisar cliente..."
                                                    class="w-full pl-10 pr-3 py-2 bg-slate-700/60 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-400 focus:outline-none focus:border-cyan-500">
                                            </div>
                                        </div>
                                        <!-- Lista de clientes -->
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="client in filteredClients" :key="client.id">
                                                <button type="button" @click="selectClient(client)"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                                    <i class="bi bi-person text-cyan-400"></i>
                                                    <span class="text-white text-sm font-medium"
                                                        x-text="client.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredClients.length === 0"
                                                class="px-4 py-3 text-slate-400 text-sm text-center">
                                                Nenhum cliente encontrado
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('client_id')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Data -->
                            <div class="space-y-2">
                                <label for="date"
                                    class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-calendar text-blue-400"></i>
                                    Data
                                </label>
                                <div class="relative">
                                    <input wire:model="date" type="date" id="date"
                                        class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                           {{ $errors->has('date') ? 'border-red-500 focus:border-red-400' : 'focus:border-blue-500 hover:border-slate-600' }}
                                           focus:ring-4 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                @error('date')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="border-t border-slate-700/50 my-6"></div>

                    <!-- Descrição -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-card-text text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Descrição</h3>
                                <p class="text-xs text-slate-400">Detalhes da transação</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <textarea wire:model="description" id="description" rows="4"
                                class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium resize-none transition-all duration-200
                                      {{ $errors->has('description') ? 'border-red-500 focus:border-red-400' : 'focus:border-indigo-500 hover:border-slate-600' }}
                                      focus:ring-4 focus:ring-indigo-500/20 focus:outline-none"
                                placeholder="Descreva os detalhes da transação..."></textarea>
                            @error('description')
                                <p class="text-red-400 text-xs flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Divisor -->
                    <div class="border-t border-slate-700/50 my-6"></div>

                    <!-- Categoria, Tipo e Cofrinho -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-grid-3x3 text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Classificação</h3>
                                <p class="text-xs text-slate-400">Categoria, tipo e cofrinho</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Categoria -->
                            <div class="space-y-2">
                                <label for="category_id"
                                    class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                                    <i class="bi bi-tags-fill text-purple-400"></i>
                                    Categoria
                                </label>
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    selectedCategory: @entangle('category_id'),
                                    selectedCategoryName: 'Selecione...',
                                    categories: {{ $categories->toJson() }},
                                    get filteredCategories() {
                                        if (!this.search) return this.categories;
                                        return this.categories.filter(category =>
                                            category.name.toLowerCase().includes(this.search.toLowerCase())
                                        );
                                    },
                                    selectCategory(category) {
                                        this.selectedCategory = category.id_category;
                                        this.selectedCategoryName = category.name;
                                        this.open = false;
                                        this.search = '';
                                        $wire.set('category_id', category.id_category);
                                    }
                                }">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                            {{ $errors->has('category_id') ? 'border-red-500' : 'hover:border-purple-500 focus:border-purple-500' }}
                                            focus:ring-4 focus:ring-purple-500/20 focus:outline-none">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-tags-fill text-purple-400"></i>
                                            <span x-text="selectedCategoryName"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open" x-transition @click.away="open = false"
                                        class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                        <!-- Campo de busca -->
                                        <div class="p-2 border-b border-slate-700">
                                            <div class="relative">
                                                <i
                                                    class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" x-model="search" @click.stop
                                                    placeholder="Pesquisar categoria..."
                                                    class="w-full pl-10 pr-3 py-2 bg-slate-700/60 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-400 focus:outline-none focus:border-purple-500">
                                            </div>
                                        </div>
                                        <!-- Lista de categorias -->
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="category in filteredCategories"
                                                :key="category.id_category">
                                                <button type="button" @click="selectCategory(category)"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                                    <i class="bi bi-tags-fill text-purple-400"></i>
                                                    <span class="text-white text-sm font-medium"
                                                        x-text="category.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredCategories.length === 0"
                                                class="px-4 py-3 text-slate-400 text-sm text-center">
                                                Nenhuma categoria encontrada
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('category_id')
                                    <p class="text-red-400 text-xs flex items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                <div class="space-y-2">
                    <label for="type_id" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                        <i class="bi bi-arrow-left-right text-indigo-400"></i>
                        Tipo
                    </label>
                    <div class="relative" x-data="{
                        open: false,
                        selectedType: @entangle('type_id'),
                        selectedTypeName: 'Selecione...',
                        selectType(type) {
                            this.selectedType = type.id;
                            this.selectedTypeName = type.name;
                            this.open = false;
                            $wire.set('type_id', type.id);
                        }
                    }">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                            {{ $errors->has('type_id') ? 'border-red-500' : 'hover:border-indigo-500 focus:border-indigo-500' }}
                                            focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                            <span class="flex items-center gap-2">
                                <i class="bi bi-arrow-left-right text-indigo-400"></i>
                                <span x-text="selectedTypeName"></span>
                            </span>
                            <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition @click.away="open = false"
                            class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                            @foreach ($types as $type)
                                <button type="button"
                                    @click="selectType({ id: {{ $type->id_type }}, name: '{{ $type->desc_type }}' })"
                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                    <i class="bi bi-arrow-left-right text-indigo-400"></i>
                                    <span class="text-white text-sm font-medium">{{ $type->desc_type }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @error('type_id')
                        <p class="text-red-400 text-xs flex items-center gap-1">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Cofrinho -->
                <div class="space-y-2">
                    <label for="cofrinho_id" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                        <i class="bi bi-piggy-bank text-amber-400"></i>
                        Cofrinho
                    </label>
                    <div class="relative" x-data="{
                        open: false,
                        selectedCofrinho: @entangle('cofrinho_id'),
                        selectedCofrinhoName: 'Selecione...',
                        selectCofrinho(cofrinho) {
                            this.selectedCofrinho = cofrinho.id;
                            this.selectedCofrinhoName = cofrinho.name;
                            this.open = false;
                            $wire.set('cofrinho_id', cofrinho.id);
                        }
                    }">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                            {{ $errors->has('cofrinho_id') ? 'border-red-500' : 'hover:border-amber-500 focus:border-amber-500' }}
                                            focus:ring-4 focus:ring-amber-500/20 focus:outline-none">
                            <span class="flex items-center gap-2">
                                <i class="bi bi-piggy-bank text-amber-400"></i>
                                <span x-text="selectedCofrinhoName"></span>
                            </span>
                            <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition @click.away="open = false"
                            class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                            @foreach ($cofrinhos as $cofrinho)
                                <button type="button"
                                    @click="selectCofrinho({ id: {{ $cofrinho->id }}, name: '{{ $cofrinho->nome }}' })"
                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                    <i class="bi bi-piggy-bank text-amber-400"></i>
                                    <span class="text-white text-sm font-medium">{{ $cofrinho->nome }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <!-- Help text explicativo -->
                    <p class="text-xs text-slate-400 flex items-start gap-1.5 mt-2">
                        <i class="bi bi-info-circle text-amber-400 mt-0.5"></i>
                        <span><strong class="text-amber-400">Dica:</strong> Ao selecionar um cofrinho, se for <strong class="text-green-400">Receita</strong> = dinheiro saiu do cofrinho para a conta. Se for <strong class="text-red-400">Despesa</strong> = dinheiro saiu da conta para o cofrinho.</span>
                    </p>
                    @error('cofrinho_id')
                        <p class="text-red-400 text-xs flex items-center gap-1">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- ========== COLUNA DIREITA: Anexo ========== -->
        <div class="w-full xl:w-[450px]">
            <div
                class="bg-gradient-to-br from-slate-900/95 via-blue-900/20 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-paperclip text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Comprovante</h3>
                        <p class="text-xs text-slate-400">Anexe um arquivo (opcional)</p>
                    </div>
                </div>

                <div class="flex-1 flex items-center justify-center">
                    <div class="w-full">
                        <label for="attachment"
                            class="flex flex-col items-center justify-center w-full h-[500px] border-2 border-dashed border-slate-600 rounded-2xl cursor-pointer bg-slate-800/50 hover:bg-slate-800/80 transition-all duration-300 hover:border-blue-500">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="bi bi-cloud-arrow-up text-6xl text-slate-500 mb-4"></i>
                                <p class="mb-2 text-sm text-slate-400">
                                    <span class="font-semibold text-blue-400">Clique para fazer upload</span> ou
                                    arraste e
                                    solte
                                </p>
                                <p class="text-xs text-slate-500">PDF, DOC, DOCX, JPG, JPEG, PNG (MÁX. 10MB)</p>

                                @if ($attachment)
                                    <div class="mt-4 p-3 bg-emerald-500/20 border border-emerald-500/50 rounded-lg">
                                        <p class="text-emerald-400 text-sm flex items-center gap-2">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Arquivo selecionado: {{ $attachment->getClientOriginalName() }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                            <input id="attachment" wire:model="attachment" type="file" class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                        </label>

                        @error('attachment')
                            <p class="mt-2 text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex items-start gap-2 text-xs text-slate-400">
                    <i class="bi bi-info-circle text-blue-400 mt-0.5"></i>
                    <p>O anexo é opcional. Você pode adicionar comprovantes, notas fiscais ou outros documentos
                        relacionados.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
