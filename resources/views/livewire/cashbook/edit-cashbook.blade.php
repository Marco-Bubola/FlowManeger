<div class="edit-cashbook-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-cashbook-ultrawide.css') }}">
    <!-- Header Modernizado com botões de ação -->
    <x-cashbook-page-header title="Editar lançamento" subtitle="Altere os dados do lançamento" icon="bi-pencil-square" >
        <x-slot:actions>
            <a href="{{ route('cashbook.index') }}" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-x-lg"></i>Cancelar</a>
            <button data-mobile-save type="submit" form="edit-cashbook-form" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save"><i class="bi bi-check-lg mr-1"></i>Salvar alterações</span>
                <span wire:loading wire:target="save"><i class="bi bi-arrow-repeat animate-spin mr-1"></i>Salvando...</span>
            </button>
        </x-slot:actions>
    </x-cashbook-page-header>

    <!-- Conteúdo Principal -->
    <form id="edit-cashbook-form" wire:submit.prevent="save" class="pb-8">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- ========== COLUNA ESQUERDA: Formulário ========== -->
            <div class="flex-1">
                <div
                    class="bg-white dark:bg-slate-900/80 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200/80 dark:border-slate-700/70">

                    <!-- Informações Principais -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                                <i class="bi bi-cash-coin text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Informações da Transação</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Dados principais</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Valor -->
                            <div class="space-y-2">
                                <x-currency-input name="value" id="value" wireModel="value" label="Valor"
                                    icon="bi-currency-dollar" icon-color="green" :required="true" width="w-full" :value="$value" />
                            </div>

                            <!-- Cliente -->
                            <div class="space-y-2">
                                <label for="client_id"
                                    class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    <i class="bi bi-person text-cyan-400"></i>
                                    Cliente
                                </label>
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    selectedClient: @entangle('client_id'),
                                    selectedClientName: '{{ $selectedClientName ?? 'Selecione...' }}',
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
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all duration-200
                                            {{ $errors->has('client_id') ? 'border-red-500' : 'hover:border-cyan-500 focus:border-cyan-500' }}
                                            focus:ring-4 focus:ring-cyan-500/20 focus:outline-none">
                                        <span class="flex items-center gap-2">
                                            <i class="bi bi-person text-cyan-400"></i>
                                            <span x-text="selectedClientName"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open" x-transition @click.away="open = false"
                                        class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                        <!-- Campo de busca -->
                                        <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                            <div class="relative">
                                                <i
                                                    class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"></i>
                                                <input type="text" x-model="search" @click.stop
                                                    placeholder="Pesquisar cliente..."
                                                    class="w-full pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:outline-none focus:border-cyan-500">
                                            </div>
                                        </div>
                                        <!-- Lista de clientes -->
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="client in filteredClients" :key="client.id">
                                                <button type="button" @click="selectClient(client)"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                                                    <i class="bi bi-person text-cyan-400"></i>
                                                    <span class="text-slate-800 dark:text-white text-sm font-medium"
                                                        x-text="client.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredClients.length === 0"
                                                class="px-4 py-3 text-slate-500 dark:text-slate-400 text-sm text-center">
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
                    <label for="date" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        <i class="bi bi-calendar text-blue-400"></i>
                        Data
                    </label>
                    <div class="relative">
                        <input wire:model="date" type="date" id="date"
                            class="w-full px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-medium transition-all duration-200
                                           {{ $errors->has('date') ? 'border-red-500 focus:border-red-400' : 'focus:border-blue-500 hover:border-slate-300 dark:hover:border-slate-600' }}
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
        <div class="border-t border-slate-200/80 dark:border-slate-700/50 my-6"></div>

        <!-- Descrição -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-card-text text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Descrição</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Detalhes da transação</p>
                </div>
            </div>

            <div class="space-y-2">
                <textarea wire:model="description" id="description" rows="4"
                    class="w-full px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-medium resize-none transition-all duration-200
                                      {{ $errors->has('description') ? 'border-red-500 focus:border-red-400' : 'focus:border-indigo-500 hover:border-slate-300 dark:hover:border-slate-600' }}
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
        <div class="border-t border-slate-200/80 dark:border-slate-700/50 my-6"></div>

        <!-- Categoria, Tipo e Cofrinho -->
        <div>
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-grid-3x3 text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Classificação</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Categoria, tipo e cofrinho</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Categoria -->
                <div class="space-y-2">
                    <label for="category_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        <i class="bi bi-tags-fill text-purple-400"></i>
                        Categoria
                    </label>
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        selectedCategory: @entangle('category_id'),
                        selectedCategoryName: '{{ $selectedCategoryName ?? 'Selecione...' }}',
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
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all duration-200
                                            {{ $errors->has('category_id') ? 'border-red-500' : 'hover:border-purple-500 focus:border-purple-500' }}
                                            focus:ring-4 focus:ring-purple-500/20 focus:outline-none">
                            <span class="flex items-center gap-2">
                                <i class="bi bi-tags-fill text-purple-400"></i>
                                <span x-text="selectedCategoryName"></span>
                            </span>
                            <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition @click.away="open = false"
                            class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                            <!-- Campo de busca -->
                            <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                <div class="relative">
                                    <i
                                        class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"></i>
                                    <input type="text" x-model="search" @click.stop
                                        placeholder="Pesquisar categoria..."
                                        class="w-full pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:outline-none focus:border-purple-500">
                                </div>
                            </div>
                            <!-- Lista de categorias -->
                            <div class="max-h-48 overflow-y-auto">
                                <template x-for="category in filteredCategories" :key="category.id_category">
                                    <button type="button" @click="selectCategory(category)"
                                        class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                                        <i class="bi bi-tags-fill text-purple-400"></i>
                                        <span class="text-slate-800 dark:text-white text-sm font-medium" x-text="category.name"></span>
                                    </button>
                                </template>
                                <div x-show="filteredCategories.length === 0"
                                    class="px-4 py-3 text-slate-500 dark:text-slate-400 text-sm text-center">
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
            <label for="type_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <i class="bi bi-arrow-left-right text-indigo-400"></i>
                Tipo
            </label>
            <div class="relative" x-data="{
                open: false,
                selectedType: @entangle('type_id'),
                selectedTypeName: '{{ $selectedTypeName ?? 'Selecione...' }}',
                selectType(type) {
                    this.selectedType = type.id;
                    this.selectedTypeName = type.name;
                    this.open = false;
                    $wire.set('type_id', type.id);
                }
            }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all duration-200
                                            {{ $errors->has('type_id') ? 'border-red-500' : 'hover:border-indigo-500 focus:border-indigo-500' }}
                                            focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-arrow-left-right text-indigo-400"></i>
                        <span x-text="selectedTypeName"></span>
                    </span>
                    <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" x-transition @click.away="open = false"
                    class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                    @foreach ($types as $type)
                        <button type="button"
                            @click="selectType({ id: {{ $type->id_type }}, name: '{{ $type->desc_type }}' })"
                            class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                            <i class="bi bi-arrow-left-right text-indigo-400"></i>
                            <span class="text-slate-800 dark:text-white text-sm font-medium">{{ $type->desc_type }}</span>
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

        <!-- Conta (onde o dinheiro entrou ou saiu) -->
        @php $userAccounts = \App\Models\Account::owned()->where('archived', false)->orderBy('name')->get(); @endphp
        <div class="space-y-2">
            <label for="account_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <i class="bi bi-bank text-indigo-400"></i>
                Conta
            </label>
            @if ($userAccounts->isEmpty())
                <a href="{{ route('accounts.index') }}" class="block px-4 py-3 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400 hover:border-indigo-500 hover:text-indigo-300">
                    <i class="bi bi-plus-circle"></i> Cadastre suas contas para ver o saldo de cada uma
                </a>
            @else
                <select id="account_id" wire:model="account_id"
                    class="w-full px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                    <option value="">Sem conta</option>
                    @foreach ($userAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            @endif
            @error('account_id') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Cofrinho -->
        <div class="space-y-2">
            <label for="cofrinho_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <i class="bi bi-piggy-bank text-amber-400"></i>
                Cofrinho
            </label>
            <div class="relative" x-data="{
                open: false,
                selectedCofrinho: @entangle('cofrinho_id'),
                selectedCofrinhoName: '{{ $selectedCofrinhoName ?? 'Selecione...' }}',
                selectCofrinho(cofrinho) {
                    this.selectedCofrinho = cofrinho.id;
                    this.selectedCofrinhoName = cofrinho.name;
                    this.open = false;
                    $wire.set('cofrinho_id', cofrinho.id);
                }
            }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all duration-200
                                            {{ $errors->has('cofrinho_id') ? 'border-red-500' : 'hover:border-amber-500 focus:border-amber-500' }}
                                            focus:ring-4 focus:ring-amber-500/20 focus:outline-none">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-piggy-bank text-amber-400"></i>
                        <span x-text="selectedCofrinhoName"></span>
                    </span>
                    <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" x-transition @click.away="open = false"
                    class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                    @foreach ($cofrinhos as $cofrinho)
                        <button type="button"
                            @click="selectCofrinho({ id: {{ $cofrinho->id }}, name: '{{ $cofrinho->nome }}' })"
                            class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                            <i class="bi bi-piggy-bank text-amber-400"></i>
                            <span class="text-slate-800 dark:text-white text-sm font-medium">{{ $cofrinho->nome }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <!-- Help text explicativo -->
            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-start gap-1.5 mt-2">
                <i class="bi bi-info-circle text-amber-400 mt-0.5"></i>
                <span><strong class="text-amber-400">Dica:</strong> Ao escolher um cofrinho, <strong class="text-red-400">Despesa</strong> = dinheiro que sai da conta e entra no cofrinho (o saldo dele aumenta). <strong class="text-green-400">Receita</strong> = dinheiro que sai do cofrinho e volta para a conta (o saldo dele diminui).</span>
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
        class="bg-white dark:bg-slate-900/80 rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200/80 dark:border-slate-700/70 h-full flex flex-col">
        <div class="flex items-center gap-3 mb-6">
            <div
                class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <i class="bi bi-paperclip text-white text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Comprovante</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Anexe um arquivo (opcional)</p>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center">
            <div class="w-full">
                <label for="attachment"
                    class="flex flex-col items-center justify-center w-full h-[360px] border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-2xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:bg-indigo-50/60 dark:hover:bg-slate-800/80 transition-all duration-300 hover:border-blue-500">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="bi bi-cloud-arrow-up text-6xl text-slate-500 mb-4"></i>
                        <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="font-semibold text-blue-400">Clique para fazer upload</span> ou arraste e
                            solte
                        </p>
                        <p class="text-xs text-slate-500">PDF, DOC, DOCX, JPG, JPEG, PNG (MÁX. 10MB)</p>

                        @if ($attachment)
                            <div class="mt-4 p-3 bg-emerald-500/20 border border-emerald-500/50 rounded-lg">
                                <p class="text-emerald-400 text-sm flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Novo arquivo: {{ $attachment->getClientOriginalName() }}
                                </p>
                            </div>
                        @elseif($cashbook->attachment)
                            <div class="mt-4 p-3 bg-blue-500/20 border border-blue-500/50 rounded-lg">
                                <p class="text-blue-400 text-sm flex items-center gap-2">
                                    <i class="bi bi-file-earmark-fill"></i>
                                    Arquivo atual anexado
                                </p>
                                <a href="{{ asset('storage/' . $cashbook->attachment) }}" target="_blank"
                                    class="text-blue-300 hover:text-blue-200 text-xs underline mt-1 block">
                                    Ver arquivo atual
                                </a>
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

        <div class="mt-4 flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
            <i class="bi bi-info-circle text-blue-400 mt-0.5"></i>
            <p>O anexo é opcional. Você pode manter o arquivo atual ou substituir por um novo documento.</p>
        </div>
    </div>
</div>
</div>
</form>
</div>
