<div class="w-full">
    @push('styles')
        @vite('resources/css/upload-animations.css')
    @endpush

    {{-- Scripts custom removidos para evitar interferência no input de arquivo --}}

    @include('components.theme-controls')
    @include('components.toast-notifications')

    <div class="">
        <x-upload-header :title="'Upload de Transações'" :description="'Importar transações a partir de arquivo PDF ou CSV'" :backRoute="route('cashbook.index')" :showConfirmation="$showConfirmation" :transactionsCount="is_array($transactions) ? count($transactions) : 0" />

        <div class="w-full ">
            @if (!$showConfirmation)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="w-full xl:w-auto">
                        <div
                            class="bg-gradient-to-br from-slate-900/95 via-green-900/20 to-slate-900/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-700/50 h-full flex flex-col">
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-file-earmark-arrow-up-fill text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">Upload de Transações</h3>
                                    <p class="text-xs text-slate-400">Envie seu arquivo PDF ou CSV</p>
                                </div>
                            </div>

                            <div class="flex-1 flex items-center justify-center">
                                <x-file-upload name="file" id="file" wireModel="newFile"
                                    title="Upload do Arquivo" description="Clique ou arraste seu arquivo aqui"
                                    :newFile="$file" height="h-[400px]" :supportedFormats="['PDF (.pdf)', 'CSV (.csv)']" maxSize="10MB" />
                            </div>

                            @error('newFile')
                                <div class="mt-4 flex items-start gap-2 text-xs text-red-400">
                                    <i class="bi bi-exclamation-circle text-red-400 mt-0.5"></i>
                                    <p>{{ $message }}</p>
                                </div>
                            @enderror

                            @if ($file)
                                <div class="mt-6">
                                    <button wire:click="uploadFile" wire:loading.attr="disabled" type="button"
                                        class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="uploadFile">
                                            <i class="bi bi-lightning-charge-fill"></i>
                                            Processar Arquivo
                                        </span>
                                        <span wire:loading wire:target="uploadFile">
                                            <i class="bi bi-arrow-repeat animate-spin"></i>
                                            Processando...
                                        </span>
                                    </button>
                                </div>
                            @endif

                            <div class="mt-4 flex items-start gap-2 text-xs text-slate-400">
                                <i class="bi bi-info-circle text-green-400 mt-0.5"></i>
                                <p>PDF, CSV • Máx 10MB • Arquivo com transações do cashbook</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Histórico de Uploads</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Últimos 10</span>
                        </div>

                        @if ($uploadHistory && count($uploadHistory) > 0)
                            <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($uploadHistory as $upload)
                                        <div
                                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                            <div
                                                class="px-4 py-3 bg-gradient-to-r {{ strtolower($upload->file_type) === 'pdf' ? 'from-red-500 to-red-600' : 'from-emerald-500 to-emerald-600' }} relative overflow-hidden">
                                                <div class="absolute inset-0 opacity-10">
                                                    <div
                                                        class="absolute top-0 right-0 w-20 h-20 bg-white rounded-full -mr-10 -mt-10">
                                                    </div>
                                                    <div
                                                        class="absolute bottom-0 left-0 w-16 h-16 bg-white rounded-full -ml-8 -mb-8">
                                                    </div>
                                                </div>

                                                <div class="relative flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div
                                                            class="flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg">
                                                            <svg class="w-8 h-8 text-white" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-white font-bold text-sm truncate max-w-[150px]"
                                                                title="{{ $upload->file_name }}">
                                                                {{ Str::limit($upload->file_name, 20) }}
                                                            </h4>
                                                            <p class="text-white/80 text-xs font-medium uppercase">
                                                                {{ $upload->file_type }}</p>
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

                                            <div class="p-4">
                                                <div class="grid grid-cols-3 gap-2 mb-3">
                                                    <div
                                                        class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div
                                                            class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">
                                                            Total</div>
                                                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                            {{ $upload->total_transactions }}</div>
                                                    </div>

                                                    <div
                                                        class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div
                                                            class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">
                                                            Criadas</div>
                                                        <div
                                                            class="text-lg font-bold text-green-600 dark:text-green-400">
                                                            {{ $upload->transactions_created }}</div>
                                                    </div>

                                                    <div
                                                        class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 p-3 rounded-lg hover:scale-105 transition-transform duration-200 shadow-sm">
                                                        <div
                                                            class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-1">
                                                            Ignoradas</div>
                                                        <div
                                                            class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                                            {{ $upload->transactions_skipped }}</div>
                                                    </div>
                                                </div>

                                                @if ($upload->cofrinho)
                                                    <div
                                                        class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-3 mb-3">
                                                        <div class="flex items-center space-x-2">
                                                            <i
                                                                class="bi bi-piggy-bank-fill text-purple-600 dark:text-purple-400"></i>
                                                            <span
                                                                class="text-sm font-semibold text-purple-900 dark:text-purple-200">{{ $upload->cofrinho->nome }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="flex items-center justify-between gap-2">
                                                    <span
                                                        class="px-3 py-1 rounded-full text-xs font-bold {{ $upload->status_badge['color'] === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200' : ($upload->status_badge['color'] === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200') }}">
                                                        <i class="bi {{ $upload->status_badge['icon'] }} mr-1"></i>
                                                        {{ $upload->status_badge['label'] }}
                                                    </span>
                                                    <div class="flex gap-1">
                                                        @if($upload->file_path && strtolower($upload->file_type) === 'pdf')
                                                            <a href="{{ Storage::url($upload->file_path) }}" target="_blank"
                                                                class="inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-all duration-200"
                                                                title="Ver PDF">
                                                                <i class="bi bi-file-earmark-pdf"></i>
                                                            </a>
                                                        @endif
                                                        <button wire:click="deleteUploadHistory({{ $upload->id }})"
                                                            onclick="return confirm('Tem certeza que deseja excluir este histórico?')"
                                                            class="inline-flex items-center px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition-all duration-200"
                                                            title="Excluir">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div
                                class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center border border-gray-200 dark:border-gray-700">
                                <div class="flex justify-center mb-4">
                                    <div
                                        class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center">
                                        <i class="bi bi-clock-history text-3xl text-green-600 dark:text-green-400"></i>
                                    </div>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhum histórico ainda
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Seus uploads aparecerão aqui após o
                                    processamento</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 w-full grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="group bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg animate-float">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-900 dark:text-blue-200 mb-2">💡 Processamento Inteligente
                                </h3>
                                <p class="text-sm text-blue-800 dark:text-blue-300">Nosso sistema identifica
                                    automaticamente categorias com base na descrição das transações</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg animate-glow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-green-900 dark:text-green-200 mb-2">⚡ Análise Rápida</h3>
                                <p class="text-sm text-green-800 dark:text-green-300">Processe centenas de transações
                                    em
                                    segundos com nossa tecnologia avançada</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105 card-hover">
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-purple-900 dark:text-purple-200 mb-2">🔒 Segurança Total</h3>
                                <p class="text-sm text-purple-800 dark:text-purple-300">Seus dados financeiros são
                                    processados com máxima segurança e privacidade</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="w-full ">


                        @if (count($transactions) > 0)
                            @if (session()->has('info'))
                                <div
                                    class="mb-6 bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700 rounded-lg p-4 animate-fadeIn">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-blue-800 dark:text-blue-300 font-medium">{{ session('info') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div
                                    class="mb-6 bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700 rounded-lg p-4 animate-fadeIn">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                            </path>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-red-800 dark:text-red-300 font-bold mb-2">❌ Erro de
                                                Validação</h4>
                                            <p class="text-red-700 dark:text-red-200">{{ session('error') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div
                                class="overflow-hidden">


                                <div class="">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                                        @foreach ($transactions as $index => $transaction)
                                            <div
                                                class="group relative bg-gradient-to-br from-white via-blue-50/40 to-purple-50/40 dark:from-gray-800 dark:via-blue-900/20 dark:to-purple-900/20 border-2 border-gray-200 dark:border-gray-700 rounded-2xl p-5 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">
                                                <div
                                                    class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-purple-500 to-pink-500 rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                </div>

                                                <div class="flex items-start justify-between mb-4">
                                                    <div class="space-y-1">
                                                        <p
                                                            class="text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wide">
                                                            {{ $transaction['date'] ?? 'Sem data' }}
                                                        </p>
                                                        <p class="text-base font-bold text-gray-900 dark:text-white leading-tight line-clamp-2"
                                                            title="{{ $transaction['description'] ?? '' }}">
                                                            {{ $transaction['description'] ?? 'Sem descrição' }}
                                                        </p>
                                                        @if (empty($transaction['description']))
                                                            <p class="text-xs text-red-500">Descrição obrigatória</p>
                                                        @endif
                                                    </div>
                                                    <button wire:click="removeTransaction({{ $index }})"
                                                        class="group flex items-center justify-center w-10 h-10 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="mb-4 space-y-1">
                                                    <label
                                                        class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-blue-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                            </path>
                                                        </svg>
                                                        Data
                                                    </label>
                                                    <div class="relative">
                                                        <input wire:model="transactions.{{ $index }}.date"
                                                            type="text" id="date-{{ $index }}"
                                                            class="w-full px-4 py-3 text-left bg-slate-800/60 border-2 border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 text-white text-sm flatpickr-input"
                                                            placeholder="Selecione a data...">
                                                        <i
                                                            class="bi bi-calendar-event absolute right-4 top-1/2 -translate-y-1/2 text-blue-400 text-lg pointer-events-none"></i>
                                                    </div>
                                                </div>

                                                <div
                                                    class="mb-4 p-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-700 flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm text-gray-600 dark:text-gray-300">Valor</p>
                                                        <p
                                                            class="text-2xl font-extrabold bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                                            R$
                                                            {{ isset($transaction['value']) ? number_format($transaction['value'], 2, ',', '.') : '0,00' }}
                                                        </p>
                                                    </div>
                                                    <span
                                                        class="px-3 py-1 rounded-full text-xs font-semibold {{ ($transaction['type_id'] ?? '') == '2' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' }}">
                                                        {{ ($transaction['type_id'] ?? '') == '2' ? 'Saída' : 'Entrada' }}
                                                    </span>
                                                </div>

                                                <div class="space-y-3">
                                                    <div class="space-y-1" x-data="{
                                                        open: false,
                                                        search: '',
                                                        categories: @js($categories),
                                                        get selectedCategoryName() {
                                                            const cat = this.categories.find(c => c.id_category == @this.transactions[{{ $index }}]?.category_id);
                                                            return cat ? cat.name : 'Selecionar categoria';
                                                        },
                                                        get filteredCategories() {
                                                            if (!this.search) return this.categories;
                                                            return this.categories.filter(category =>
                                                                category.name.toLowerCase().includes(this.search.toLowerCase())
                                                            );
                                                        },
                                                        selectCategory(categoryId) {
                                                            @this.set('transactions.{{ $index }}.category_id', categoryId);
                                                            this.open = false;
                                                            this.search = '';
                                                        }
                                                    }">
                                                        <label
                                                            class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-purple-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                                </path>
                                                            </svg>
                                                            Categoria
                                                        </label>
                                                        <div class="relative">
                                                            <button type="button" @click="open = !open"
                                                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200 hover:border-purple-500 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 focus:outline-none">
                                                                <span class="flex items-center gap-2 text-sm"
                                                                    x-text="selectedCategoryName"></span>
                                                                <i class="bi bi-chevron-up text-slate-400 transition-transform duration-200"
                                                                    :class="{ 'rotate-180': open }"></i>
                                                            </button>
                                                            <div x-show="open" x-transition
                                                                @click.away="open = false"
                                                                class="absolute z-50 w-full bottom-full mb-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                                                <div class="p-2 border-b border-slate-700">
                                                                    <input x-model="search" type="text"
                                                                        placeholder="Buscar categoria..."
                                                                        class="w-full px-3 py-2 bg-slate-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                                                                </div>
                                                                <div class="max-h-48 overflow-y-auto">
                                                                    <template x-for="category in filteredCategories"
                                                                        :key="category.id_category">
                                                                        <button type="button"
                                                                            @click="selectCategory(category.id_category)"
                                                                            class="w-full text-left px-4 py-2 hover:bg-slate-700 text-white transition-colors duration-150 text-sm"
                                                                            x-text="category.name"></button>
                                                                    </template>
                                                                    <div x-show="filteredCategories.length === 0"
                                                                        class="px-4 py-3 text-slate-400 text-sm text-center">
                                                                        Nenhuma categoria encontrada
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-1" x-data="{
                                                        open: false,
                                                        search: '',
                                                        cofrinhos: @js($cofrinhos ?? []),
                                                        get selectedCofrinhoName() {
                                                            const cof = this.cofrinhos.find(c => c.id == @this.transactions[{{ $index }}]?.cofrinho_id);
                                                            return cof ? cof.nome : 'Selecionar Cofrinho';
                                                        },
                                                        get filteredCofrinhos() {
                                                            if (!this.search) return this.cofrinhos;
                                                            return this.cofrinhos.filter(cofrinho =>
                                                                cofrinho.nome.toLowerCase().includes(this.search.toLowerCase())
                                                            );
                                                        },
                                                        selectCofrinho(cofrinhoId) {
                                                            @this.set('transactions.{{ $index }}.cofrinho_id', cofrinhoId);
                                                            this.open = false;
                                                            this.search = '';
                                                        }
                                                    }">
                                                        <label
                                                            class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                            <i class="bi bi-piggy-bank text-indigo-500"></i>
                                                            Cofrinho
                                                        </label>
                                                        <div class="relative">
                                                            <button type="button" @click="open = !open"
                                                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200 hover:border-indigo-500 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 focus:outline-none">
                                                                <span class="flex items-center gap-2 text-sm"
                                                                    x-text="selectedCofrinhoName"></span>
                                                                <i class="bi bi-chevron-up text-slate-400 transition-transform duration-200"
                                                                    :class="{ 'rotate-180': open }"></i>
                                                            </button>
                                                            <div x-show="open" x-transition
                                                                @click.away="open = false"
                                                                class="absolute z-50 w-full bottom-full mb-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                                                <div class="p-2 border-b border-slate-700">
                                                                    <input x-model="search" type="text"
                                                                        placeholder="Buscar cofrinho..."
                                                                        class="w-full px-3 py-2 bg-slate-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                                                </div>
                                                                <div class="max-h-48 overflow-y-auto">
                                                                    <template x-for="cofrinho in filteredCofrinhos"
                                                                        :key="cofrinho.id">
                                                                        <button type="button"
                                                                            @click="selectCofrinho(cofrinho.id)"
                                                                            class="w-full text-left px-4 py-2 hover:bg-slate-700 text-white transition-colors duration-150 text-sm"
                                                                            x-text="cofrinho.nome"></button>
                                                                    </template>
                                                                    <div x-show="filteredCofrinhos.length === 0"
                                                                        class="px-4 py-3 text-slate-400 text-sm text-center">
                                                                        Nenhum cofrinho encontrado
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-1" x-data="{
                                                        open: false,
                                                        search: '',
                                                        clients: @js($clients),
                                                        get selectedClientName() {
                                                            const cli = this.clients.find(c => c.id == @this.transactions[{{ $index }}]?.client_id);
                                                            return cli ? cli.name : 'Nenhum';
                                                        },
                                                        get filteredClients() {
                                                            if (!this.search) return this.clients;
                                                            return this.clients.filter(client =>
                                                                client.name.toLowerCase().includes(this.search.toLowerCase())
                                                            );
                                                        },
                                                        selectClient(clientId) {
                                                            @this.set('transactions.{{ $index }}.client_id', clientId);
                                                            this.open = false;
                                                            this.search = '';
                                                        }
                                                    }">
                                                        <label
                                                            class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-teal-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M5.121 17.804A12.042 12.042 0 0112 15c2.21 0 4.28.57 6.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            Cliente
                                                        </label>
                                                        <div class="relative">
                                                            <button type="button" @click="open = !open"
                                                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-slate-800/60 border-slate-700 text-white font-medium transition-all duration-200 hover:border-teal-500 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/20 focus:outline-none">
                                                                <span class="flex items-center gap-2 text-sm"
                                                                    x-text="selectedClientName"></span>
                                                                <i class="bi bi-chevron-up text-slate-400 transition-transform duration-200"
                                                                    :class="{ 'rotate-180': open }"></i>
                                                            </button>
                                                            <div x-show="open" x-transition
                                                                @click.away="open = false"
                                                                class="absolute z-50 w-full bottom-full mb-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                                                <div class="max-h-48 overflow-y-auto">
                                                                    <button type="button" @click="selectClient(null)"
                                                                        class="w-full text-left px-4 py-2 hover:bg-slate-700 text-white transition-colors duration-150 text-sm border-b border-slate-700">
                                                                        Nenhum
                                                                    </button>
                                                                    <template x-for="client in filteredClients"
                                                                        :key="client.id">
                                                                        <button type="button"
                                                                            @click="selectClient(client.id)"
                                                                            class="w-full text-left px-4 py-2 hover:bg-slate-700 text-white transition-colors duration-150 text-sm"
                                                                            x-text="client.name"></button>
                                                                    </template>
                                                                    <div x-show="filteredClients.length === 0"
                                                                        class="px-4 py-3 text-slate-400 text-sm text-center">
                                                                        Nenhum cliente encontrado
                                                                    </div>
                                                                </div>
                                                                <div class="p-2 border-t border-slate-700">
                                                                    <input x-model="search" type="text"
                                                                        placeholder="Buscar cliente..."
                                                                        class="w-full px-3 py-2 bg-slate-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="w-full bg-gradient-to-br from-white via-gray-50 to-blue-50 dark:from-gray-800 dark:via-gray-700 dark:to-blue-900/30 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-600 p-16 text-center">
                                <div class="flex justify-center mb-6">
                                    <div
                                        class="flex items-center justify-center w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl shadow-lg">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Nenhuma transação
                                    encontrada</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Não foi possível extrair transações
                                    do arquivo enviado.</p>
                                <div class="mt-8">
                                    <button wire:click="backToUpload"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                            </path>
                                        </svg>
                                        Tentar Novamente
                                    </button>
                                </div>
                            </div>
                        @endif

            @endif
        </div>

        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fadeIn {
                animation: fadeIn 0.5s ease-out;
            }

            .animate-float {
                animation: float 3s ease-in-out infinite;
            }

            @keyframes float {

                0%,
                100% {
                    transform: translateY(0px);
                }

                50% {
                    transform: translateY(-10px);
                }
            }

            .animate-glow {
                animation: glow 2s ease-in-out infinite alternate;
            }

            @keyframes glow {
                from {
                    box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
                }

                to {
                    box-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
                }
            }

            @keyframes shake {

                0%,
                100% {
                    transform: translateX(0);
                }

                25% {
                    transform: translateX(-2px);
                }

                75% {
                    transform: translateX(2px);
                }
            }

            .animate-shake {
                animation: shake 0.3s ease-in-out;
            }

            .btn-delete:hover {
                animation: shake 0.3s ease-in-out;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const uploadButton = document.querySelector('label[for="file"]');
                if (uploadButton) {
                    uploadButton.setAttribute('data-tooltip', 'Clique para selecionar arquivo ou arraste e solte aqui');
                }

                const elements = document.querySelectorAll('.card-hover, .group');
                elements.forEach((el, index) => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        el.style.transition = 'all 0.5s ease-out';
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, index * 100);
                });

                // Inicializa Flatpickr para os campos de data
                initializeDatePickers();
            });

            function initializeDatePickers() {
                // Configuração padrão do Flatpickr
                if (typeof flatpickr !== 'undefined') {
                    flatpickr.localize(flatpickr.l10ns.pt);

                    const defaultConfig = {
                        locale: 'pt',
                        dateFormat: 'd/m/Y',
                        allowInput: true,
                        animate: true,
                        disableMobile: true,
                        prevArrow: '<i class="bi bi-chevron-left"></i>',
                        nextArrow: '<i class="bi bi-chevron-right"></i>',
                        onReady: function(selectedDates, dateStr, instance) {
                            instance.calendarContainer.style.setProperty('--fp-primary', 'rgb(59 130 246)');
                        }
                    };

                    // Inicializa todos os campos de data
                    document.querySelectorAll('.flatpickr-input').forEach(input => {
                        if (!input._flatpickr) {
                            const fp = flatpickr(input, {
                                ...defaultConfig,
                                defaultDate: input.value || null,
                                onChange: function(selectedDates, dateStr) {
                                    // Atualiza o valor e dispara evento para Livewire
                                    input.value = dateStr;
                                    input.dispatchEvent(new Event('input', {
                                        bubbles: true
                                    }));
                                }
                            });
                        }
                    });
                }
            }

            // Re-inicializa após updates do Livewire
            document.addEventListener('livewire:initialized', function() {
                Livewire.hook('morph.updated', ({
                    el,
                    component
                }) => {
                    setTimeout(() => {
                        initializeDatePickers();
                    }, 100);
                });
            });
        </script>
    </div>

</div>
