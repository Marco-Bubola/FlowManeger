<div class="w-full">
    <!-- Header Modernizado com botões de ação -->
    <x-sales-header title="Nova Transação" description="Adicione uma nova transação ao sistema" :back-route="route('invoices.index')"
        :current-step="1" :steps="[]">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('invoices.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-file-invoice mr-1"></i>Faturas
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Nova Fatura</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('invoices.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-200 font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                <i class="bi bi-x-lg text-lg"></i>
                Cancelar
            </a>
            <button type="submit" form="create-invoice-form" wire:loading.attr="disabled"
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
    <form id="create-invoice-form" wire:submit.prevent="save" class="px-6 py-6">
        <div class="bg-gradient-to-br from-slate-900/95 via-slate-800/95 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50">

            <!-- Informações Básicas -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-info-circle text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Informações Básicas</h3>
                        <p class="text-xs text-slate-400">Defina o tipo e os detalhes principais</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Tipo de Transação -->
                    <div class="space-y-2">
                        <label for="type" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-tag text-emerald-400"></i>
                            Tipo de Transação
                            <span class="text-red-400">*</span>
                        </label>
                        <div class="relative" x-data="{
                            open: false,
                            selectedType: @entangle('type'),
                            selectedTypeName: 'Selecione o tipo',
                            selectType(type) {
                                this.selectedType = type.value;
                                this.selectedTypeName = type.name;
                                this.open = false;
                                $wire.set('type', type.value);
                            }
                        }">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200
                                {{ $errors->has('type') ? 'border-red-500' : 'hover:border-emerald-500 focus:border-emerald-500' }}
                                focus:ring-4 focus:ring-emerald-500/20 focus:outline-none">
                                <span class="flex items-center gap-2">
                                    <i class="bi bi-tag text-emerald-400"></i>
                                    <span x-text="selectedTypeName"></span>
                                </span>
                                <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" x-transition @click.away="open = false"
                                class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                <button type="button"
                                    @click="selectType({ value: 'receita', name: '💰 Receita (Entrada)' })"
                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700">
                                    <span class="text-white text-sm font-medium">💰 Receita (Entrada)</span>
                                </button>
                                <button type="button"
                                    @click="selectType({ value: 'despesa', name: '💸 Despesa (Saída)' })"
                                    class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors">
                                    <span class="text-white text-sm font-medium">💸 Despesa (Saída)</span>
                                </button>
                            </div>
                        </div>
                        @error('type')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="space-y-2">
                        <label for="description" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-card-text text-purple-400"></i>
                            Descrição
                            <span class="text-red-400">*</span>
                        </label>
                        <input wire:model="description" type="text" id="description"
                            placeholder="Digite uma descrição..."
                            class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                            {{ $errors->has('description') ? 'border-red-500 focus:border-red-400' : 'focus:border-purple-500 hover:border-slate-600' }}
                            focus:ring-4 focus:ring-purple-500/20 focus:outline-none">
                        @error('description')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Valor -->
                    <div class="space-y-2">
                        <x-currency-input name="value" id="value" wireModel="value" label="Valor"
                            icon="bi-currency-dollar" icon-color="yellow" :required="true" width="w-full" />
                    </div>
                </div>
            </div>

            <!-- Divisor -->
            <div class="border-t border-slate-700/50 my-6"></div>

            <!-- Categorização -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-folder text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Categorização</h3>
                        <p class="text-xs text-slate-400">Organize e classifique a transação</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Categoria -->
                    <div class="space-y-2">
                        <label for="category_id" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-bookmark text-rose-400"></i>
                            Categoria
                            <span class="text-red-400">*</span>
                        </label>
                        <div class="relative" x-data="{
                            open: false,
                            search: '',
                            selectedCategory: @entangle('category_id'),
                            selectedCategoryName: 'Selecione uma categoria',
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
                                {{ $errors->has('category_id') ? 'border-red-500' : 'hover:border-rose-500 focus:border-rose-500' }}
                                focus:ring-4 focus:ring-rose-500/20 focus:outline-none">
                                <span class="flex items-center gap-2">
                                    <i class="bi bi-bookmark text-rose-400"></i>
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
                                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" x-model="search" @click.stop
                                            placeholder="Pesquisar categoria..."
                                            class="w-full pl-10 pr-3 py-2 bg-slate-700/60 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-400 focus:outline-none focus:border-rose-500">
                                    </div>
                                </div>
                                <!-- Lista de categorias -->
                                <div class="max-h-48 overflow-y-auto">
                                    <template x-for="category in filteredCategories" :key="category.id_category">
                                        <button type="button" @click="selectCategory(category)"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-700/80 transition-colors border-b border-slate-700 last:border-b-0">
                                            <i class="bi bi-bookmark text-rose-400"></i>
                                            <span class="text-white text-sm font-medium" x-text="category.name"></span>
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

                    <!-- Cliente -->
                    <div class="space-y-2">
                        <label for="client_id" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-person text-cyan-400"></i>
                            Cliente
                            <span class="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <div class="relative" x-data="{
                            open: false,
                            search: '',
                            selectedClient: @entangle('client_id'),
                            selectedClientName: 'Selecione um cliente',
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
                                hover:border-cyan-500 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none">
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
                                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
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
                                            <span class="text-white text-sm font-medium" x-text="client.name"></span>
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
                </div>
            </div>

            <!-- Divisor -->
            <div class="border-t border-slate-700/50 my-6"></div>

            <!-- Detalhes Temporais -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Detalhes Temporais</h3>
                        <p class="text-xs text-slate-400">Configure datas e parcelas</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Data da Transação -->
                    <div class="space-y-2">
                        <label for="date" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-calendar-check text-blue-400"></i>
                            Data da Transação
                            <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-blue-400 text-lg pointer-events-none z-10"></i>
                            <input wire:model="date" type="text" id="date" placeholder="Selecione a data..."
                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                {{ $errors->has('date') ? 'border-red-500 focus:border-red-400' : 'focus:border-blue-500 hover:border-blue-600' }}
                                focus:ring-4 focus:ring-blue-500/20 focus:outline-none cursor-pointer
                                hover:bg-slate-800 hover:shadow-lg hover:shadow-blue-500/10" readonly>
                        </div>
                        @error('date')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Data de Vencimento -->
                    <div class="space-y-2">
                        <label for="due_date" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-clock text-orange-400"></i>
                            Data de Vencimento
                            <span class="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <div class="relative">
                            <i class="bi bi-alarm absolute left-4 top-1/2 -translate-y-1/2 text-orange-400 text-lg pointer-events-none z-10"></i>
                            <input wire:model="due_date" type="text" id="due_date" placeholder="Selecione a data de vencimento..."
                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                                focus:border-orange-500 hover:border-orange-600 focus:ring-4 focus:ring-orange-500/20 focus:outline-none cursor-pointer
                                hover:bg-slate-800 hover:shadow-lg hover:shadow-orange-500/10" readonly>
                        </div>
                        @error('due_date')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Parcelas -->
                    <div class="space-y-2">
                        <label for="installments" class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                            <i class="bi bi-layers text-indigo-400"></i>
                            Parcelas
                            <span class="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <input wire:model="installments" type="text" id="installments"
                            placeholder="1x, 2x, 3x, à vista..."
                            class="w-full px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white placeholder-slate-500 font-medium transition-all duration-200
                            focus:border-indigo-500 hover:border-slate-600 focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                        @error('installments')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuração padrão do Flatpickr em português
        flatpickr.localize(flatpickr.l10ns.pt);

        const defaultConfig = {
            locale: 'pt',
            dateFormat: 'Y-m-d',
            allowInput: true,
            animate: true,
            disableMobile: true, // Usa Flatpickr mesmo no mobile
            prevArrow: '<i class="bi bi-chevron-left"></i>',
            nextArrow: '<i class="bi bi-chevron-right"></i>',
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.style.setProperty('--fp-primary', 'rgb(16 185 129)');
            }
        };

        // Inicializa Data da Transação
        const dateInput = document.getElementById('date');
        if (dateInput) {
            flatpickr(dateInput, {
                ...defaultConfig,
                defaultDate: new Date(),
                onChange: function(selectedDates, dateStr) {
                    @this.set('date', dateStr);
                }
            });
        }

        // Inicializa Data de Vencimento
        const dueDateInput = document.getElementById('due_date');
        if (dueDateInput) {
            flatpickr(dueDateInput, {
                ...defaultConfig,
                minDate: 'today',
                onChange: function(selectedDates, dateStr) {
                    @this.set('due_date', dateStr);
                }
            });
        }

        // Re-inicializa após updates do Livewire
        Livewire.hook('message.processed', (message, component) => {
            // Re-aplica Flatpickr se os inputs foram re-renderizados
            setTimeout(() => {
                if (document.getElementById('date') && !document.getElementById('date')._flatpickr) {
                    flatpickr('#date', {
                        ...defaultConfig,
                        defaultDate: new Date(),
                        onChange: function(selectedDates, dateStr) {
                            @this.set('date', dateStr);
                        }
                    });
                }
                if (document.getElementById('due_date') && !document.getElementById('due_date')._flatpickr) {
                    flatpickr('#due_date', {
                        ...defaultConfig,
                        minDate: 'today',
                        onChange: function(selectedDates, dateStr) {
                            @this.set('due_date', dateStr);
                        }
                    });
                }
            }, 100);
        });
    });
</script>
@endpush
