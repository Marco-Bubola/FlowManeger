<div class="w-full">
    @push('styles')
        @vite('resources/css/upload-animations.css')
    @endpush

    @push('scripts')
        @vite('resources/js/upload-interactions.js')
    @endpush

    <!-- Incluir controles de tema -->
    @include('components.theme-controls')

    <!-- Incluir sistema de notificações -->
    @include('components.toast-notifications')

    <div class="">
        <!-- Modern header component -->
        <x-upload-header
            :title="'Upload de Transações'"
            :description="'Importar transações a partir de arquivo PDF ou CSV'"
            :backRoute="route('invoices.index', ['bankId' => $bankId])"
            :showConfirmation="$showConfirmation"
            :transactionsCount="is_array($transactions) ? count($transactions) : 0"
            :totalValue="is_array($transactions) ? array_sum(array_column($transactions, 'value')) : 0"
            :hasDuplicates="is_array($transactions) ? collect($transactions)->contains(fn($t) => $t['is_duplicate'] ?? false) : false"
        />

        <!-- Content -->
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @if (!$showConfirmation)
                <!-- Grid Layout: Upload + Histórico -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Coluna 1: Upload Form -->
                    <div class="w-full xl:w-auto">
                        <div class="bg-gradient-to-br from-slate-900/95 via-purple-900/20 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-file-earmark-arrow-up-fill text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">Upload de Transações</h3>
                                    <p class="text-xs text-slate-400">Envie seu arquivo PDF ou CSV</p>
                                </div>
                            </div>

                            <div class="flex-1 flex items-center justify-center">
                                <x-file-upload
                                    name="file"
                                    id="file"
                                    wireModel="file"
                                    title="Upload do Arquivo"
                                    description="Clique ou arraste seu arquivo aqui"
                                    :newFile="$file"
                                    height="h-[400px]"
                                    :supportedFormats="['PDF (.pdf)', 'CSV (.csv)']"
                                    maxSize="10MB"
                                    />
                                </div>

                                @error('file')
                                    <div class="mt-4 flex items-start gap-2 text-xs text-red-400">
                                        <i class="bi bi-exclamation-circle text-red-400 mt-0.5"></i>
                                        <p>{{ $message }}</p>
                                    </div>
                                @enderror

                                <div class="mt-4 flex items-start gap-2 text-xs text-slate-400">
                                    <i class="bi bi-info-circle text-blue-400 mt-0.5"></i>
                                    <p>PDF, CSV • Máx 10MB • Arquivo com transações bancárias</p>
                                </div>
                            </div>

                    </div>

                    <!-- Coluna 2: Histórico de Uploads -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Histórico de Uploads</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Últimos 10</span>
                        </div>

                        @if($uploadHistory && count($uploadHistory) > 0)
                            <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($uploadHistory as $upload)
                                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                            <!-- Header com cor baseada no tipo de arquivo -->
                                            <div class="px-4 py-3 bg-gradient-to-r {{ strtolower($upload->file_type) === 'pdf' ? 'from-red-500 to-red-600' : 'from-emerald-500 to-emerald-600' }} relative overflow-hidden">
                                                <!-- Padrão decorativo -->
                                                <div class="absolute inset-0 opacity-10">
                                                    <div class="absolute top-0 right-0 w-20 h-20 bg-white rounded-full -mr-10 -mt-10"></div>
                                                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-white rounded-full -ml-8 -mb-8"></div>
                                                </div>

                                                <div class="relative flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg">
                                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-white font-bold text-sm truncate max-w-[150px]" title="{{ $upload->filename }}">
                                                                {{ Str::limit($upload->filename, 20) }}
                                                            </h4>
                                                            <p class="text-white/80 text-xs font-medium uppercase">{{ $upload->file_type }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-white/90 text-xs font-semibold">
                                                            {{ $upload->created_at->format('d/m/Y') }}
                                                        </p>
                                                        <p class="text-white/70 text-xs">
                                                            {{ $upload->created_at->format('H:i') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Stats Grid -->
                                            <div class="p-4">
                                                <div class="grid grid-cols-2 gap-2 mb-3">
                                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">Total</div>
                                                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $upload->total_transactions }}</div>
                                                    </div>

                                                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">Criados</div>
                                                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $upload->transactions_created }}</div>
                                                    </div>

                                                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">Ignorados</div>
                                                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $upload->transactions_skipped }}</div>
                                                    </div>

                                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">Valor Total</div>
                                                        <div class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $upload->formatted_total_value }}</div>
                                                    </div>
                                                </div>

                                                <!-- Botões de Ação em Grid -->
                                                <div class="grid grid-cols-3 gap-2 mb-3">
                                                    @if($upload->file_path)
                                                        <a href="{{ Storage::url($upload->file_path) }}" target="_blank"
                                                            class="flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-xs font-semibold rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                    @endif

                                                    @if($upload->summary && (count($upload->summary['created'] ?? []) > 0 || count($upload->summary['skipped'] ?? []) > 0))
                                                        <button wire:click="showUploadDetails({{ $upload->id }})" type="button"
                                                            class="flex items-center justify-center px-3 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-semibold rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                                                            <i class="bi bi-list-ul"></i>
                                                        </button>
                                                    @endif

                                                    <button wire:click="confirmDeleteUpload({{ $upload->id }})" type="button"
                                                        class="flex items-center justify-center px-3 py-2 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white text-xs font-semibold rounded-lg shadow-lg transform hover:scale-105 transition-all duration-200">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>

                                                <!-- Status Badge -->
                                                <div class="mt-3 mb-3">
                                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                        {{ $upload->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                                        {{ $upload->status === 'processing' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                                        {{ $upload->status === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                                        <i class="{{ $upload->status_badge['icon'] }}"></i>
                                                        {{ $upload->status_badge['label'] }}
                                                    </span>
                                                </div>

                                                <!-- Footer Info -->
                                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="flex items-center gap-1">
                                                        <i class="bi bi-clock"></i>
                                                        {{ $upload->formatted_duration }}
                                                    </span>
                                                    <span class="font-semibold {{ $upload->success_rate >= 80 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }} flex items-center gap-1">
                                                        <i class="bi bi-graph-up"></i>
                                                        {{ number_format($upload->success_rate, 1) }}% sucesso
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nenhum upload ainda</h3>
                                <p class="text-gray-600 dark:text-gray-400">Faça o upload do seu primeiro arquivo para ver o histórico aqui</p>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                <!-- Confirmation View - Full Width -->
                <div class="w-full">
                    <!-- Transações em Grid -->
                    <div class="w-full">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-6 gap-6">
                            @foreach ($transactions as $index => $transaction)
                                <div class="group relative bg-gradient-to-br {{ ($transaction['is_duplicate'] ?? false) ? 'from-orange-50 via-red-50/30 to-orange-50/30 dark:from-red-900/20 dark:via-orange-900/20 dark:to-red-900/20 border-red-300 dark:border-red-700' : 'from-white via-blue-50/30 to-purple-50/30 dark:from-gray-800 dark:via-blue-900/20 dark:to-purple-900/20' }} border-2 {{ ($transaction['is_duplicate'] ?? false) ? 'border-red-300 dark:border-red-700' : 'border-gray-200 dark:border-gray-600' }} rounded-2xl p-6 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">

                                    @if($transaction['is_duplicate'] ?? false)
                                    <!-- Badge de Duplicata -->
                                    <div class="absolute top-3 right-3 z-10">
                                        <div class="flex items-center gap-2 bg-gradient-to-r from-red-500 to-orange-500 text-white px-3 py-1.5 rounded-lg shadow-lg text-xs font-bold uppercase animate-pulse">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            DUPLICATA
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Indicador visual lateral -->
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-purple-500 to-pink-500 rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                    <!-- Header do Card -->
                                    <div class="flex justify-between items-start mb-6">
                                        <!-- Descrição e Data -->
                                        <div class="space-y-3">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-md flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                                                        {{ $transaction['description'] ?? 'Sem descrição' }}
                                                    </p>
                                                    <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-300 mt-1">
                                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <input type="date" value="{{ $transaction['date'] ?? '' }}"
                                                            wire:change="updateTransactionDate({{ $index }}, $event.target.value)"
                                                            class="px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Botão de remover -->
                                        <div class="flex items-center gap-2">
                                            @if($transaction['is_duplicate'] ?? false)
                                            <!-- Botão Forçar Criação -->
                                            <button wire:click="forceCreateTransaction({{ $index }})"
                                                type="button"
                                                title="Forçar criação desta transação duplicada"
                                                class="group flex items-center justify-center w-10 h-10 bg-gradient-to-r from-orange-500 to-amber-600 dark:from-orange-600 dark:to-amber-700 text-white rounded-lg hover:from-orange-600 hover:to-amber-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg">
                                                <i class="bi bi-arrow-clockwise text-lg group-hover:rotate-180 transition-transform duration-300"></i>
                                            </button>
                                            @endif

                                            <button wire:click="removeTransaction({{ $index }})"
                                                type="button"
                                                class="group flex items-center justify-center w-10 h-10 bg-gradient-to-r from-red-500 to-pink-600 dark:from-red-600 dark:to-pink-700 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg">
                                                <svg class="w-5 h-5 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Conteúdo do Card -->
                                    <div class="space-y-6">
                                        <!-- Valor e Parcelas -->
                                        <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-700">
                                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 rounded-xl shadow-md">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                                    R$ {{ number_format($transaction['value'] ?? 0, 2, ',', '.') }}
                                                </p>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $transaction['installments'] ?? 'À vista' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seletores com Alpine.js -->
                                        <div class="space-y-4">
                                            <!-- Categoria Dropdown -->
                                            <div class="space-y-2" x-data="{
                                                open: false,
                                                search: '',
                                                selectedId: '{{ $transaction['category_id'] ?? '' }}',
                                                selectedName: '{{ $categories->firstWhere('id_category', $transaction['category_id'] ?? '')?->name ?? 'Selecione categoria' }}',
                                                get filteredCategories() {
                                                    if (this.search === '') return @js($categories);
                                                    return @js($categories).filter(cat =>
                                                        cat.name.toLowerCase().includes(this.search.toLowerCase())
                                                    );
                                                },
                                                selectCategory(id, name) {
                                                    this.selectedId = id;
                                                    this.selectedName = name;
                                                    this.open = false;
                                                    this.search = '';
                                                    @this.call('updateTransactionCategory', {{ $index }}, id);
                                                }
                                            }">
                                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    Categoria
                                                </label>
                                                <div class="relative">
                                                    <button @click="open = !open" type="button"
                                                        class="w-full px-4 py-3 text-left bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 flex items-center justify-between">
                                                        <span class="text-sm" :class="selectedId ? 'text-gray-900 dark:text-white' : 'text-gray-500'" x-text="selectedName"></span>
                                                        <svg class="w-5 h-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>

                                                    <div x-show="open" @click.away="open = false" x-transition
                                                        class="absolute z-50 w-full bottom-full mb-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl max-h-60 overflow-hidden">
                                                        <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                                                            <input type="text" x-model="search" @click.stop
                                                                placeholder="Buscar categoria..."
                                                                class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </div>
                                                        <div class="max-h-48 overflow-y-auto">
                                                            <template x-for="category in filteredCategories" :key="category.id_category">
                                                                <button type="button" @click="selectCategory(category.id_category, category.name)"
                                                                    class="w-full px-4 py-2 text-left text-sm hover:bg-blue-50 dark:hover:bg-gray-600 transition-colors"
                                                                    :class="selectedId == category.id_category ? 'bg-blue-100 dark:bg-gray-600 text-blue-700 dark:text-blue-300 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                                    <span x-text="category.name"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cliente Dropdown -->
                                            <div class="space-y-2" x-data="{
                                                open: false,
                                                search: '',
                                                selectedId: '{{ $transaction['client_id'] ?? '' }}',
                                                selectedName: '{{ $clients->firstWhere('id', $transaction['client_id'] ?? '')?->name ?? 'Cliente (opcional)' }}',
                                                get filteredClients() {
                                                    if (this.search === '') return @js($clients);
                                                    return @js($clients).filter(client =>
                                                        client.name.toLowerCase().includes(this.search.toLowerCase())
                                                    );
                                                },
                                                selectClient(id, name) {
                                                    this.selectedId = id;
                                                    this.selectedName = name;
                                                    this.open = false;
                                                    this.search = '';
                                                    @this.call('updateTransactionClient', {{ $index }}, id);
                                                }
                                            }">
                                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    Cliente
                                                </label>
                                                <div class="relative">
                                                    <button @click="open = !open" type="button"
                                                        class="w-full px-4 py-3 text-left bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500 flex items-center justify-between">
                                                        <span class="text-sm" :class="selectedId ? 'text-gray-900 dark:text-white' : 'text-gray-500'" x-text="selectedName"></span>
                                                        <svg class="w-5 h-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>

                                                    <div x-show="open" @click.away="open = false" x-transition
                                                        class="absolute z-50 w-full bottom-full mb-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl max-h-60 overflow-hidden">
                                                        <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                                                            <input type="text" x-model="search" @click.stop
                                                                placeholder="Buscar cliente..."
                                                                class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </div>
                                                        <div class="max-h-48 overflow-y-auto">
                                                            <button type="button" @click="selectClient('', 'Cliente (opcional)')"
                                                                class="w-full px-4 py-2 text-left text-sm hover:bg-blue-50 dark:hover:bg-gray-600 transition-colors text-gray-500 dark:text-gray-400">
                                                                Cliente (opcional)
                                                            </button>
                                                            <template x-for="client in filteredClients" :key="client.id">
                                                                <button type="button" @click="selectClient(client.id, client.name)"
                                                                    class="w-full px-4 py-2 text-left text-sm hover:bg-blue-50 dark:hover:bg-gray-600 transition-colors"
                                                                    :class="selectedId == client.id ? 'bg-blue-100 dark:bg-gray-600 text-blue-700 dark:text-blue-300 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                                    <span x-text="client.name"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Barra de progresso decorativa -->
                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Detalhes do Upload -->
    @if($showDetailsModal && $selectedUpload)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showDetailsModal') }">
            <!-- Backdrop -->
            <div x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                @click="$wire.closeDetailsModal()"></div>

            <!-- Modal -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-4xl bg-gradient-to-br from-white via-blue-50/50 to-purple-50/50 dark:from-gray-800 dark:via-blue-900/20 dark:to-purple-900/20 rounded-3xl shadow-2xl overflow-hidden">

                    <!-- Header do Modal -->
                    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <i class="bi bi-file-earmark-text-fill text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">Detalhes do Upload</h3>
                                <p class="text-sm text-white/80">{{ $selectedUpload->filename }}</p>
                            </div>
                        </div>
                        <button @click="$wire.closeDetailsModal()"
                            class="w-10 h-10 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Conteúdo do Modal -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <!-- Estatísticas -->
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedUpload->total_transactions }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $selectedUpload->transactions_created }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Criadas</div>
                            </div>
                            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $selectedUpload->transactions_skipped }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Ignoradas</div>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 text-center">
                                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $selectedUpload->formatted_total_value }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Valor Total</div>
                            </div>
                        </div>

                        <!-- Grid de 2 Colunas: Criadas e Ignoradas -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Transações Criadas -->
                            @if(count($selectedUpload->summary['created'] ?? []) > 0)
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                        Transações Criadas ({{ count($selectedUpload->summary['created']) }})
                                    </h4>
                                    <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
                                        @foreach($selectedUpload->summary['created'] as $created)
                                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800 hover:shadow-lg transition-shadow">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-1">
                                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $created['description'] ?? 'N/A' }}</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            <span class="font-bold text-green-600 dark:text-green-400">R$ {{ number_format($created['value'] ?? 0, 2, ',', '.') }}</span>
                                                            <span class="mx-2">•</span>
                                                            <span>{{ \Carbon\Carbon::parse($created['date'])->format('d/m/Y') }}</span>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('invoices.index', ['bankId' => $selectedUpload->bank_id]) }}"
                                                        class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors">
                                                        <i class="bi bi-arrow-right-circle-fill text-xl"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Transações Ignoradas -->
                            @if(count($selectedUpload->summary['skipped'] ?? []) > 0)
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-exclamation-triangle-fill text-orange-500"></i>
                                        Transações Ignoradas ({{ count($selectedUpload->summary['skipped']) }})
                                    </h4>
                                    <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
                                        @foreach($selectedUpload->summary['skipped'] as $index => $skipped)
                                            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800 hover:shadow-lg transition-shadow">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex-1">
                                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $skipped['description'] ?? 'N/A' }}</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            <span class="font-bold text-orange-600 dark:text-orange-400">R$ {{ number_format($skipped['value'] ?? 0, 2, ',', '.') }}</span>
                                                            <span class="mx-2">•</span>
                                                            <span>{{ \Carbon\Carbon::parse($skipped['date'])->format('d/m/Y') }}</span>
                                                        </div>
                                                        <div class="mt-2">
                                                            <span class="px-2 py-1 bg-orange-200 dark:bg-orange-800 rounded text-xs font-semibold">{{ $skipped['reason'] ?? 'Desconhecido' }}</span>
                                                        </div>
                                                    </div>
                                                    <button wire:click="createInvoiceFromSkipped({{ json_encode($skipped) }})" type="button"
                                                        title="Criar transação"
                                                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-all transform hover:scale-110 shadow-md">
                                                        <i class="bi bi-plus-lg text-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Exclusão -->
    <div x-data="{
        show: false,
        init() {
            this.$wire.on('show-delete-upload-modal', () => { this.show = true });
            this.$wire.on('hide-delete-upload-modal', () => { this.show = false });
        }
    }">
        <div x-show="show"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;">
            <!-- Backdrop -->
            <div x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/70 backdrop-blur-md"
                @click="show = false"></div>

            <!-- Modal -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-90 -translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                    class="relative w-full max-w-lg bg-gradient-to-br from-white via-red-50/30 to-pink-50/30 dark:from-gray-800 dark:via-red-900/10 dark:to-pink-900/10 rounded-3xl shadow-2xl overflow-hidden border-2 border-red-200/50 dark:border-red-800/50">

                    <!-- Efeitos decorativos -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-400/20 to-pink-400/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-orange-400/20 to-red-400/20 rounded-full blur-2xl"></div>

                    <!-- Header Moderno -->
                    <div class="relative bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 px-8 py-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Ícone animado -->
                                <div class="relative">
                                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center animate-pulse">
                                        <i class="bi bi-exclamation-triangle-fill text-white text-3xl"></i>
                                    </div>
                                    <div class="absolute inset-0 bg-white/10 rounded-2xl animate-ping"></div>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black text-white mb-1">Confirmar Exclusão</h3>
                                    <p class="text-sm text-white/90 font-medium">⚠️ Esta ação não pode ser desfeita</p>
                                </div>
                            </div>
                            <button @click="show = false"
                                class="w-10 h-10 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all hover:rotate-90 duration-300">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="relative p-8">
                        <!-- Alerta visual -->
                        <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-600 dark:border-red-500 rounded-lg p-4 mb-6">
                            <div class="flex gap-3">
                                <i class="bi bi-info-circle-fill text-red-600 dark:text-red-400 text-xl flex-shrink-0 mt-0.5"></i>
                                <div class="text-sm text-gray-800 dark:text-gray-200">
                                    <p class="font-bold mb-1">Você está prestes a excluir:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                                        <li>Histórico de upload completo</li>
                                        <li>Arquivo PDF associado</li>
                                        <li>Todas as informações de transações</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <p class="text-gray-700 dark:text-gray-300 text-center mb-8 font-medium">
                            Deseja realmente continuar com a exclusão?
                        </p>

                        <!-- Botões Modernos -->
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="show = false" type="button"
                                class="group relative px-6 py-4 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl transition-all duration-300 hover:scale-105 hover:shadow-lg overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                                <div class="relative flex items-center justify-center gap-2">
                                    <i class="bi bi-x-circle text-lg"></i>
                                    <span>Cancelar</span>
                                </div>
                            </button>
                            <button wire:click="deleteUpload" @click="show = false" type="button"
                                class="group relative px-6 py-4 bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 hover:from-red-700 hover:via-rose-700 hover:to-pink-700 text-white font-bold rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-2xl overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                                <div class="relative flex items-center justify-center gap-2">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                    <span>Excluir Agora</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Scrollbar Styles -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #2563eb, #7c3aed);
        }
    </style>
</div>
