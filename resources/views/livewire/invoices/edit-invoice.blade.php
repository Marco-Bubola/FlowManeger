<script>
    // Função global para inicializar e abrir o flatpickr
    function initAndOpenFlatpickr(element) {
        if (!element) {
            console.error('initAndOpenFlatpickr: o elemento fornecido é nulo.');
            return;
        }

        // Se o flatpickr já está inicializado neste elemento, apenas o abre.
        if (element._flatpickr) {
            console.debug('Flatpickr já inicializado. Abrindo...');
            element._flatpickr.open();
            return;
        }

        console.debug('Inicializando flatpickr no elemento:', element);

        try {
            // Garante que a localização em português esteja carregada
            if (typeof flatpickr.l10ns.pt === 'undefined' && flatpickr.l10ns && flatpickr.l10ns.pt) {
                flatpickr.localize(flatpickr.l10ns.pt);
            }

            const defaultConfig = {
                locale: 'pt',
                dateFormat: 'Y-m-d',
                allowInput: true,
                animate: true,
                disableMobile: true,
                prevArrow: '<i class="bi bi-chevron-left"></i>',
                nextArrow: '<i class="bi bi-chevron-right"></i>',
            };

            // Lê a data existente a partir do atributo data-*
            const existingDate = JSON.parse(element.dataset.existingDate || 'null');

            const fp = flatpickr(element, {
                ...defaultConfig,
                defaultDate: existingDate,
                onChange: function(selectedDates, dateStr) {
                    // Atualiza a propriedade do Livewire de forma segura
                    @this.set('invoice_date', dateStr);
                }
            });

            console.debug('Flatpickr inicializado com sucesso.');

            // Abre o calendário imediatamente após a inicialização
            if (fp && typeof fp.open === 'function') {
                fp.open();
            }
        } catch (err) {
            console.error('Erro ao inicializar o flatpickr:', err);
        }
    }
</script>
<div class="edit-invoice-page mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-invoice-ultrawide.css') }}">
    @include('components.toast-notifications')

    <form wire:submit.prevent="save">
        <x-bank-page-header title="Editar compra" :subtitle="e($invoice->description)" icon="bi-pencil-square" :bank="$invoice->bank" :back-route="route('invoices.index', ['bankId' => $invoice->id_bank, 'return_month' => $returnMonth, 'return_year' => $returnYear])">
            <x-slot:actions>
                <a href="{{ route('invoices.index', ['bankId' => $invoice->id_bank, 'return_month' => $returnMonth, 'return_year' => $returnYear]) }}" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-x-lg"></i>Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50"><i class="bi bi-check-lg"></i>Salvar alterações</button>
            </x-slot:actions>
        </x-bank-page-header>

        <!-- Conteúdo -->
        <div class="pb-8">
            {{-- Bloco para exibir TODOS os erros de validação --}}
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-white p-4 rounded-2xl mb-4">
                    <div class="font-bold text-lg mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Ocorreram erros de validação:</div>
                    <ul class="list-disc list-inside space-y-1 text-red-600 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-900/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 shadow-sm p-5 sm:p-6 space-y-8">

                <!-- Informações Básicas -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-info-circle text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Informações Básicas</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Dados principais da fatura</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Descrição -->
                        <div class="space-y-2">
                            <label for="description" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <i class="bi bi-card-text text-purple-400"></i>
                                Descrição
                                <span class="text-red-400">*</span>
                            </label>
                            <input wire:model.live="description" type="text" id="description"
                                placeholder="Digite uma descrição..." value="{{ $description }}"
                                class="w-full px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-medium transition-all duration-200
                                {{ $errors->has('description') ? 'border-red-500 focus:border-red-400' : 'focus:border-purple-500 hover:border-slate-300 dark:hover:border-slate-600' }}
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
                                icon="bi-currency-dollar" icon-color="yellow" :required="true" width="w-full"
                                :value="$value" />
                        </div>

                        <!-- Data da Fatura -->
                        <div class="space-y-2">
                            <label for="invoice_date" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <i class="bi bi-calendar-check text-blue-400"></i>
                                Data da Fatura
                                <span class="text-red-400">*</span>
                            </label>
                            <div class="relative" wire:ignore>
                                <i class="bi bi-calendar-event absolute left-4 top-1/2 -translate-y-1/2 text-blue-400 text-lg pointer-events-none z-10"></i>
                                <input wire:model.live="invoice_date" name="invoice_date" type="text" id="invoice_date"
                                    placeholder="Selecione a data..."
                                    onclick="initAndOpenFlatpickr(this)"
                                    data-existing-date='{{ json_encode($invoice_date) }}'
                                    value="{{ $invoice_date }}"
                                    class="w-full pl-12 pr-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-medium transition-all duration-200
                                    {{ $errors->has('invoice_date') ? 'border-red-500 focus:border-red-400' : 'focus:border-blue-500 hover:border-blue-600' }}
                                    focus:ring-4 focus:ring-blue-500/20 focus:outline-none cursor-pointer
                                    hover:bg-white dark:hover:bg-slate-800 hover:shadow-lg hover:shadow-blue-500/10" readonly>
                            </div>
                            @error('invoice_date')
                                <p class="text-red-400 text-xs flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Divisor -->
                <div class="border-t border-slate-200/80 dark:border-slate-700/50 my-6"></div>

                <!-- Categorização -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center">
                            <i class="bi bi-tags-fill text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Categorização</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Classifique sua fatura</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Categoria -->
                        <div class="space-y-2">
                            <label for="category_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <i class="bi bi-bookmark text-rose-400"></i>
                                Categoria
                                <span class="text-red-400">*</span>
                            </label>
                            <div class="relative" x-data="{
                                open: false,
                                search: '',
                                selectedCategory: @entangle('category_id'),
                                selectedCategoryName: '{{ $selectedCategoryName }}',
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
                                    {{ $errors->has('category_id') ? 'border-red-500' : 'hover:border-rose-500 focus:border-rose-500' }}
                                    focus:ring-4 focus:ring-rose-500/20 focus:outline-none">
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-bookmark text-rose-400"></i>
                                        <span x-text="selectedCategoryName"></span>
                                    </span>
                                    <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }"></i>
                                </button>

                                <div x-show="open" x-transition @click.away="open = false"
                                    class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                        <div class="relative">
                                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"></i>
                                            <input type="text" x-model="search" @click.stop
                                                placeholder="Pesquisar categoria..."
                                                class="w-full pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:outline-none focus:border-rose-500">
                                        </div>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <template x-for="category in filteredCategories" :key="category.id_category">
                                            <button type="button" @click="selectCategory(category)"
                                                class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                                                <i class="bi bi-bookmark text-rose-400"></i>
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

                        <!-- Cliente -->
                        <div class="space-y-2">
                            <label for="client_id" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <i class="bi bi-person text-cyan-400"></i>
                                Cliente
                                <span class="text-slate-500 dark:text-slate-400 text-xs">(opcional)</span>
                            </label>
                            <div class="relative" x-data="{
                                open: false,
                                search: '',
                                selectedClient: @entangle('client_id'),
                                selectedClientName: '{{ $selectedClientName }}',
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
                                    hover:border-cyan-500 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none">
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-person text-cyan-400"></i>
                                        <span x-text="selectedClientName"></span>
                                    </span>
                                    <i class="bi bi-chevron-down text-slate-500 dark:text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }"></i>
                                </button>

                                <div x-show="open" x-transition @click.away="open = false"
                                    class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                        <div class="relative">
                                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"></i>
                                            <input type="text" x-model="search" @click.stop
                                                placeholder="Pesquisar cliente..."
                                                class="w-full pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm placeholder-slate-400 focus:outline-none focus:border-cyan-500">
                                        </div>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <template x-for="client in filteredClients" :key="client.id">
                                            <button type="button" @click="selectClient(client)"
                                                class="w-full flex items-center gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-colors border-b border-slate-200 dark:border-slate-700 last:border-b-0">
                                                <i class="bi bi-person text-cyan-400"></i>
                                                <span class="text-slate-800 dark:text-white text-sm font-medium" x-text="client.name"></span>
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

                        <!-- Parcelas -->
                        <div class="space-y-2">
                            <label for="installments" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <i class="bi bi-layers text-indigo-400"></i>
                                Parcelas
                                <span class="text-slate-500 dark:text-slate-400 text-xs">(opcional)</span>
                            </label>
                            <input wire:model.live="installments" type="text" id="installments"
                                placeholder="1x, 2x, 3x, à vista..." value="{{ $installments }}"
                                class="w-full px-4 py-3 rounded-xl border-2 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-medium transition-all duration-200
                                focus:border-indigo-500 hover:border-slate-300 dark:hover:border-slate-600 focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                            @error('installments')
                                <p class="text-red-400 text-xs flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Apenas carrega a localização globalmente uma vez.
        if (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) {
            flatpickr.localize(flatpickr.l10ns.pt);
        }
    });

    Livewire.hook('message.processed', (message, component) => {
        // Log para depurar re-renderizações do Livewire
        setTimeout(() => {
            const el = document.getElementById('invoice_date');
            console.debug('Livewire re-renderizou. Elemento existe:', !!el, 'Tem flatpickr:', el ? !!el._flatpickr : 'N/A');
        }, 100);
    });
</script>
@endpush
