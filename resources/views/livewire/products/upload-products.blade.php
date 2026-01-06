<div class="">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Header -->
    @if(!$showProductsTable)
    <x-upload-header-original
        title="Upload de Produtos"
        description="Importe produtos através de arquivo PDF ou CSV"
        :back-route="route('products.index')">
        <x-slot name="actions">
            <!-- Botão Dicas -->
            <button wire:click="toggleTips"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-300 backdrop-blur-sm">
                <i class="bi bi-lightbulb-fill mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Dicas
            </button>

            <!-- Botão Processar Arquivo -->
            @if($pdf_file)
            <button wire:click="processUpload"
                    wire:loading.attr="disabled"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-purple-300 backdrop-blur-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="bi bi-lightning-charge-fill mr-2 group-hover:animate-pulse"></i>
                <span wire:loading.remove wire:target="processUpload">Processar Arquivo</span>
                <span wire:loading wire:target="processUpload" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processando...
                </span>
            </button>
            @endif
        </x-slot>
    </x-upload-header-original>
    @else
    <x-upload-header-original
        title="Produtos Extraídos"

        :back-route="null">
        <x-slot name="actions">
            <!-- Botão Desfazer (se houver produtos removidos) -->
            @if(!empty($removedProducts))
            <button wire:click="undoRemove"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-yellow-300 backdrop-blur-sm">
                <i class="bi bi-arrow-counterclockwise mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Desfazer ({{ count($removedProducts) }})
            </button>
            @endif

            <!-- Botão Validar Duplicatas -->
            <button wire:click="checkDuplicates"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-300 backdrop-blur-sm">
                <i class="bi bi-shield-check mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Validar
            </button>

            <button wire:click="$set('showProductsTable', false)"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm">
                <i class="bi bi-arrow-left mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Voltar
            </button>

            <button wire:click="store"
                    class="group relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 hover:from-green-600 hover:via-emerald-600 hover:to-teal-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-green-300 backdrop-blur-sm"
                    wire:loading.attr="disabled">
                <span wire:loading.remove class="flex items-center">
                    <i class="bi bi-check-circle mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Salvar ({{ count($productsUpload ?? []) }})
                </span>
                <span wire:loading class="flex items-center">
                    <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                    Salvando...
                </span>
            </button>
        </x-slot>
    </x-upload-header-original>
    @endif

    <!-- Alerta de Duplicatas -->
    @if(!empty($duplicates))
    <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-yellow-400 text-2xl"></i>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    {{ count($duplicates) }} produto(s) duplicado(s) encontrado(s)
                </h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <p>Escolha uma ação para cada produto duplicado abaixo:</p>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach($duplicates as $index => $duplicate)
                    <div class="bg-white dark:bg-slate-800 p-3 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $duplicate['product']['name'] ?? 'Produto sem nome' }}
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Código: {{ $duplicate['product']['product_code'] }} |
                                    Estoque atual: {{ $duplicate['existing']->stock_quantity }} |
                                    Nova quantidade: {{ $duplicate['product']['stock_quantity'] }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button wire:click="setDuplicateAction({{ $index }}, 'sum_stock')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                                        {{ ($duplicate['action'] ?? 'sum_stock') === 'sum_stock' ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300' }}">
                                    <i class="bi bi-plus-circle mr-1"></i> Somar
                                </button>
                                <button wire:click="setDuplicateAction({{ $index }}, 'skip')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                                        {{ ($duplicate['action'] ?? 'sum_stock') === 'skip' ? 'bg-blue-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300' }}">
                                    <i class="bi bi-skip-forward mr-1"></i> Pular
                                </button>
                                <button wire:click="setDuplicateAction({{ $index }}, 'replace')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                                        {{ ($duplicate['action'] ?? 'sum_stock') === 'replace' ? 'bg-orange-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300' }}">
                                    <i class="bi bi-arrow-repeat mr-1"></i> Substituir
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Conteúdo Principal -->
    <div class="">
        @if(!$showProductsTable)
            <!-- Layout 2 Colunas: Upload + Histórico -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Coluna Esquerda: Upload de Arquivo -->
                <div class="space-y-6">
                    <div class="flex items-center justify-center w-full">
                        <label for="pdf_file" class="group relative flex flex-col items-center justify-center w-full h-[600px] border-3 border-dashed border-slate-300 dark:border-slate-600 rounded-3xl cursor-pointer
                               bg-gradient-to-br from-white/80 via-purple-50/50 to-indigo-50/30
                               dark:from-slate-800/80 dark:via-purple-900/20 dark:to-indigo-900/10
                               hover:from-purple-50/80 hover:via-indigo-50/60 hover:to-purple-50/40
                               dark:hover:from-slate-700/80 dark:hover:via-purple-900/30 dark:hover:to-indigo-900/20
                               backdrop-blur-xl transition-all duration-500 ease-out
                               hover:border-purple-400 dark:hover:border-purple-500
                               hover:shadow-2xl hover:shadow-purple-500/20
                               transform hover:scale-[1.02]">

                            <!-- Efeito de brilho animado -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 rounded-3xl animate-pulse"></div>

                            <!-- Partículas flutuantes -->
                            <div class="absolute inset-0 overflow-hidden rounded-3xl">
                                <div class="absolute w-2 h-2 bg-purple-400/30 rounded-full animate-float" style="top: 20%; left: 15%; animation-delay: 0.2s;"></div>
                                <div class="absolute w-1 h-1 bg-indigo-400/40 rounded-full animate-float" style="top: 60%; right: 20%; animation-delay: 0.8s;"></div>
                                <div class="absolute w-3 h-3 bg-purple-300/20 rounded-full animate-float" style="bottom: 30%; left: 30%; animation-delay: 1.2s;"></div>
                            </div>

                            <div class="relative flex flex-col items-center justify-center px-10 py-10 z-10">
                                @if($pdf_file)
                                    <!-- Preview do Arquivo com Efeitos -->
                                    <div class="relative group/file w-full h-full flex flex-col items-center justify-center">
                                        <div class="relative">
                                            <i class="bi bi-file-earmark-pdf text-9xl text-red-500 group-hover:scale-110 transition-transform duration-500"></i>

                                            <!-- Badge de sucesso animado -->
                                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500 text-white rounded-full p-3 shadow-2xl shadow-green-500/40 animate-bounce">
                                                <i class="bi bi-check-lg text-lg font-bold"></i>
                                                <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-30"></div>
                                            </div>
                                        </div>

                                        <div class="mt-8 text-center space-y-4">
                                            <h3 class="text-2xl font-bold text-slate-700 dark:text-slate-200">
                                                {{ $pdf_file->getClientOriginalName() }}
                                            </h3>
                                            <p class="text-lg text-slate-500 dark:text-slate-400">
                                                Tamanho: {{ round($pdf_file->getSize() / 1024, 2) }} KB
                                            </p>

                                            <!-- Mensagem de alterar arquivo -->
                                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                                <p class="text-sm font-bold bg-gradient-to-r from-purple-600 to-indigo-600 dark:from-purple-400 dark:to-indigo-400 bg-clip-text text-transparent flex items-center justify-center gap-2">
                                                    <i class="bi bi-arrow-repeat text-purple-500 text-lg"></i>
                                                    Clique para alterar arquivo
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Overlay com ícone de edição -->
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl flex items-center justify-center backdrop-blur-sm">
                                            <i class="bi bi-arrow-repeat text-white text-4xl"></i>
                                        </div>
                                    </div>
                                @else
                                    <!-- Estado vazio com animações -->
                                    <div class="text-center space-y-8">
                                        <div class="relative">
                                            <!-- Ícone principal com efeitos -->
                                            <div class="relative">
                                                <i class="bi bi-cloud-arrow-up text-9xl text-slate-300 dark:text-slate-600 group-hover:text-purple-400 dark:group-hover:text-purple-500 transition-all duration-500 transform group-hover:scale-110"></i>

                                                <!-- Ícone de PDF flutuante -->
                                                <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full flex items-center justify-center shadow-lg animate-bounce group-hover:scale-125 transition-transform duration-300">
                                                    <i class="bi bi-file-earmark-pdf text-white text-xl font-bold"></i>
                                                </div>

                                                <!-- Círculos decorativos -->
                                                <div class="absolute -top-4 -left-4 w-6 h-6 bg-purple-400/30 rounded-full animate-pulse"></div>
                                                <div class="absolute -bottom-6 left-8 w-4 h-4 bg-indigo-400/40 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-700 via-purple-600 to-indigo-600 dark:from-slate-300 dark:via-purple-400 dark:to-indigo-400 bg-clip-text text-transparent group-hover:from-purple-600 group-hover:to-indigo-600 transition-all duration-500">
                                                <i class="bi bi-file-earmark-arrow-up mr-3"></i>
                                                Upload de Produtos
                                            </h3>
                                            <p class="text-lg text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                                <span class="font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">Clique ou arraste seu arquivo aqui</span>
                                            </p>

                                            <!-- Tags informativos melhorados -->
                                            <div class="flex items-center justify-center space-x-6 pt-4">
                                                <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                                    <i class="bi bi-file-earmark-pdf text-red-500 mr-3 text-lg"></i>
                                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">PDF, CSV</span>
                                                </div>
                                                <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                                    <i class="bi bi-hdd text-emerald-500 mr-3 text-lg"></i>
                                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Máx. 2MB</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <input wire:model="pdf_file" id="pdf_file" name="pdf_file" type="file" class="hidden" accept=".pdf,.csv">
                        </label>
                    </div>

                    @error('pdf_file')
                    <div class="flex items-center justify-center p-4 bg-red-50/80 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl backdrop-blur-sm">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-4 text-xl"></i>
                        <p class="text-red-600 dark:text-red-400 font-bold text-lg">{{ $message }}</p>
                    </div>
                    @enderror
                </div>

                <!-- Coluna Direita: Histórico de Uploads -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-clock-history text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Histórico de Uploads</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Últimos envios realizados</p>
                            </div>
                        </div>
                        @if(!empty($uploadHistory))
                        <span class="px-3 py-1.5 bg-gradient-to-r from-purple-500 to-indigo-500 text-white text-xs font-bold rounded-full shadow-lg">
                            {{ count($uploadHistory) }}
                        </span>
                        @endif
                    </div>

                    <!-- Cards do Histórico -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[690px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($uploadHistory as $upload)
                        @php $badge = $upload->status_badge; @endphp
                        <div class="group relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-purple-400 dark:hover:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-2xl hover:shadow-purple-500/20 hover:-translate-y-1 overflow-hidden">

                            <!-- Header com Gradiente -->
                            <div class="relative bg-gradient-to-br from-{{ $upload->file_type === 'pdf' ? 'red' : 'emerald' }}-500 via-{{ $upload->file_type === 'pdf' ? 'red' : 'emerald' }}-600 to-{{ $upload->file_type === 'pdf' ? 'red' : 'emerald' }}-700 p-4">
                                <!-- Pattern decorativo -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                                <div class="absolute bottom-0 left-0 w-24 h-24 bg-black/5 rounded-full -ml-12 -mb-12"></div>

                                <div class="relative flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-xl border border-white/30 group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-file-earmark-{{ $upload->file_type === 'pdf' ? 'pdf' : 'spreadsheet' }} text-white text-2xl drop-shadow-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-white truncate text-sm mb-1 drop-shadow-md" title="{{ $upload->filename }}">
                                                {{ $upload->filename }}
                                            </h4>
                                            <p class="text-xs text-white/90 flex items-center gap-2">
                                                <span class="flex items-center gap-1">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $upload->created_at->format('d/m/Y') }}
                                                </span>
                                                <span>•</span>
                                                <span class="flex items-center gap-1">
                                                    <i class="bi bi-clock"></i>
                                                    {{ $upload->created_at->format('H:i') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-white/90 text-{{ $badge['color'] }}-700 shadow-lg backdrop-blur-sm">
                                        <i class="bi {{ $badge['icon'] }} mr-1"></i>
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Corpo do Card -->
                            <div class="p-4">
                                <!-- Estatísticas -->
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <div class="relative group/stat">
                                        <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 rounded-xl opacity-50 group-hover/stat:opacity-100 transition-opacity"></div>
                                        <div class="relative bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700/80 dark:to-slate-700/60 rounded-xl p-3 text-center border border-slate-300/50 dark:border-slate-600/50">
                                            <i class="bi bi-box-seam text-slate-600 dark:text-slate-400 text-lg mb-1 block"></i>
                                            <p class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $upload->total_products }}</p>
                                            <p class="text-[9px] text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Total</p>
                                        </div>
                                    </div>

                                    <div class="relative group/stat">
                                        <div class="absolute inset-0 bg-gradient-to-br from-green-200 to-green-300 dark:from-green-700 dark:to-green-800 rounded-xl opacity-50 group-hover/stat:opacity-100 transition-opacity"></div>
                                        <div class="relative bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/50 dark:to-green-900/30 rounded-xl p-3 text-center border border-green-300/50 dark:border-green-700/50">
                                            <i class="bi bi-plus-circle text-green-600 dark:text-green-400 text-lg mb-1 block"></i>
                                            <p class="text-2xl font-black text-green-800 dark:text-green-100">{{ $upload->products_created }}</p>
                                            <p class="text-[9px] text-green-700 dark:text-green-400 font-semibold uppercase tracking-wider mt-0.5">Criados</p>
                                        </div>
                                    </div>

                                    <div class="relative group/stat">
                                        <div class="absolute inset-0 bg-gradient-to-br from-blue-200 to-blue-300 dark:from-blue-700 dark:to-blue-800 rounded-xl opacity-50 group-hover/stat:opacity-100 transition-opacity"></div>
                                        <div class="relative bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-900/30 rounded-xl p-3 text-center border border-blue-300/50 dark:border-blue-700/50">
                                            <i class="bi bi-arrow-repeat text-blue-600 dark:text-blue-400 text-lg mb-1 block"></i>
                                            <p class="text-2xl font-black text-blue-800 dark:text-blue-100">{{ $upload->products_updated }}</p>
                                            <p class="text-[9px] text-blue-700 dark:text-blue-400 font-semibold uppercase tracking-wider mt-0.5">Atualizados</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer com Ações -->
                                <div class="flex items-center justify-between pt-3 border-t-2 border-slate-200 dark:border-slate-700">
                                    <div class="flex flex-col gap-1">
                                        <span class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                            <i class="bi bi-stopwatch text-purple-500"></i>
                                            <span class="font-semibold">{{ $upload->formatted_duration }}</span>
                                        </span>
                                        @if($upload->status === 'completed')
                                        <span class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            <i class="bi bi-check-circle-fill"></i>
                                            {{ number_format($upload->success_rate, 1) }}% de sucesso
                                        </span>
                                        @endif
                                    </div>

                                    <!-- Botões de Ação -->
                                    <div class="flex items-center gap-2">
                                        <!-- Botão Ver Detalhes -->
                                        <button wire:click="showUploadDetails({{ $upload->id }})"
                                            class="group/btn inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-blue-500/50 hover:scale-105">
                                            <i class="bi bi-info-circle text-base group-hover/btn:rotate-12 transition-transform"></i>
                                            <span>Detalhes</span>
                                        </button>

                                        @if($upload->file_type === 'pdf' && $upload->file_path)
                                        <!-- Botão Abrir PDF -->
                                        <a href="{{ Storage::url($upload->file_path) }}"
                                           target="_blank"
                                           class="group/btn inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-red-500 via-red-600 to-red-700 hover:from-red-600 hover:via-red-700 hover:to-red-800 text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-red-500/50 hover:scale-105">
                                            <i class="bi bi-file-earmark-pdf text-base group-hover/btn:rotate-12 transition-transform"></i>
                                            <span>PDF</span>
                                        </a>
                                        @endif

                                        <!-- Botão Excluir -->
                                        <button wire:click="confirmDeleteUpload({{ $upload->id }})"
                                            class="group/btn inline-flex items-center justify-center p-2 rounded-xl bg-gradient-to-r from-red-500 via-rose-600 to-pink-600 hover:from-red-600 hover:via-rose-700 hover:to-pink-700 text-white text-xs font-bold transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-red-500/50 hover:scale-105">
                                            <i class="bi bi-trash text-base group-hover/btn:rotate-12 transition-transform"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center py-16 px-6 bg-gradient-to-br from-slate-50 to-purple-50/30 dark:from-slate-800/50 dark:to-purple-900/10 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700">
                            <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-full flex items-center justify-center mb-4">
                                <i class="bi bi-inbox text-4xl text-purple-400 dark:text-purple-500"></i>
                            </div>
                            <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhum upload realizado</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 text-center">Envie seu primeiro arquivo para ver o histórico aqui</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        @else


            <!-- Tabela de Produtos Extraídos -->
            <x-products-preview-original
                :products="$productsUpload ?? []"
                :categories="$categories ?? []"
                :show-back-button="true" />
        @endif
    </div>

    <!-- Modal de Dicas -->
    @if($showTipsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{
            show: @entangle('showTipsModal'),
            currentStep: 1,
            totalSteps: 5,
            nextStep() {
                if(this.currentStep < this.totalSteps) this.currentStep++;
            },
            prevStep() {
                if(this.currentStep > 1) this.currentStep--;
            }
        }">
            <!-- Backdrop com Blur -->
            <div x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-md"
                @click="$wire.toggleTips()"></div>

            <!-- Modal -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.away=""
                    class="relative w-full max-w-4xl bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden">

                <!-- Header com Progress Bar -->
                <div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 px-8 py-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center ring-4 ring-white/30">
                                <i class="bi bi-lightbulb-fill text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">
                                    Guia de Upload de Produtos
                                </h3>
                                <p class="text-sm text-white/80 mt-1 font-medium">
                                    <span x-text="'Passo ' + currentStep + ' de ' + totalSteps"></span>
                                </p>
                            </div>
                        </div>
                        <button @click="$wire.toggleTips()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all">
                            <i class="bi bi-x-lg text-2xl"></i>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex items-center gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <div class="flex-1 h-2.5 rounded-full overflow-hidden bg-white/20">
                                <div class="h-full bg-white rounded-full transition-all duration-500 ease-out"
                                     :style="step <= currentStep ? 'width: 100%' : 'width: 0%'"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-10 py-12 min-h-[520px] relative">
                    <!-- Step 1: Prepare seu Arquivo -->
                    <div x-show="currentStep === 1"
                         x-transition:enter="transition ease-out duration-300 delay-75"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 absolute"
                         class="space-y-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-600 rounded-3xl shadow-2xl mb-6 ring-8 ring-purple-100 dark:ring-purple-900/30">
                                <i class="bi bi-file-earmark-pdf text-white text-5xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Prepare seu Arquivo</h4>
                            <p class="text-lg text-slate-600 dark:text-slate-400">Escolha o formato adequado para seu upload</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-2xl border-2 border-purple-200 dark:border-purple-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-filetype-pdf text-white text-xl"></i>
                                    </div>
                                    <h5 class="text-xl font-bold text-slate-800 dark:text-white">Arquivo PDF</h5>
                                </div>
                                <ul class="space-y-3 text-sm text-slate-700 dark:text-slate-300">
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-purple-500 mt-0.5"></i>
                                        <span>Lista ou tabela de produtos formatada</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-purple-500 mt-0.5"></i>
                                        <span>Texto legível e bem organizado</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-purple-500 mt-0.5"></i>
                                        <span>Máximo de 2MB por arquivo</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-6 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-2xl border-2 border-indigo-200 dark:border-indigo-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-filetype-csv text-white text-xl"></i>
                                    </div>
                                    <h5 class="text-xl font-bold text-slate-800 dark:text-white">Planilha CSV</h5>
                                </div>
                                <ul class="space-y-3 text-sm text-slate-700 dark:text-slate-300">
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-indigo-500 mt-0.5"></i>
                                        <span>Colunas: código, nome, preço, estoque</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-indigo-500 mt-0.5"></i>
                                        <span>Separado por vírgula ou ponto e vírgula</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-indigo-500 mt-0.5"></i>
                                        <span>Formato UTF-8 recomendado</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Faça o Upload -->
                    <div x-show="currentStep === 2"
                         x-transition:enter="transition ease-out duration-300 delay-75"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 absolute"
                         class="space-y-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 via-blue-600 to-cyan-600 rounded-3xl shadow-2xl mb-6 ring-8 ring-blue-100 dark:ring-blue-900/30">
                                <i class="bi bi-cloud-upload text-white text-5xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Faça o Upload</h4>
                            <p class="text-lg text-slate-600 dark:text-slate-400">Envie seu arquivo de forma simples e rápida</p>
                        </div>
                        <div class="space-y-6">
                            <div class="p-8 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl border-2 border-dashed border-blue-300 dark:border-blue-700">
                                <div class="text-center space-y-4">
                                    <i class="bi bi-file-earmark-arrow-up text-6xl text-blue-500"></i>
                                    <div>
                                        <p class="text-lg font-semibold text-slate-800 dark:text-white">Arraste seu arquivo aqui</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">ou clique para selecionar do seu computador</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="p-4 bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <i class="bi bi-shield-check text-3xl text-green-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white text-sm">Validação Automática</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400">Formato verificado</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <i class="bi bi-lightning-charge text-3xl text-yellow-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white text-sm">Processamento Rápido</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400">Análise inteligente</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <i class="bi bi-lock text-3xl text-blue-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white text-sm">100% Seguro</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400">Dados protegidos</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Revise os Produtos -->
                    <div x-show="currentStep === 3"
                         x-transition:enter="transition ease-out duration-300 delay-75"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 absolute"
                         class="space-y-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-yellow-500 via-yellow-600 to-orange-600 rounded-3xl shadow-2xl mb-6 ring-8 ring-yellow-100 dark:ring-yellow-900/30">
                                <i class="bi bi-eye text-white text-5xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Revise os Produtos</h4>
                            <p class="text-lg text-slate-600 dark:text-slate-400">Confira e ajuste os dados antes de salvar</p>
                        </div>
                        <div class="space-y-5">
                            <div class="p-6 bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-2xl border-2 border-yellow-200 dark:border-yellow-700">
                                <div class="flex items-start gap-4">
                                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-exclamation-triangle text-white text-2xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Detecção de Duplicados</h5>
                                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-3">Produtos que já existem no sistema serão marcados automaticamente</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-lg text-xs font-semibold">Somar Estoque</span>
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-xs font-semibold">Substituir</span>
                                            <span class="px-3 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-semibold">Pular</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 bg-white dark:bg-slate-700 rounded-xl shadow-sm border border-slate-200 dark:border-slate-600">
                                    <i class="bi bi-pencil-square text-3xl text-blue-500 mb-3"></i>
                                    <h6 class="font-bold text-slate-800 dark:text-white mb-1">Edição Rápida</h6>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Ajuste categorias, preços e estoque</p>
                                </div>
                                <div class="p-5 bg-white dark:bg-slate-700 rounded-xl shadow-sm border border-slate-200 dark:border-slate-600">
                                    <i class="bi bi-trash text-3xl text-red-500 mb-3"></i>
                                    <h6 class="font-bold text-slate-800 dark:text-white mb-1">Remover Itens</h6>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Exclua produtos indesejados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: IA Categoriza -->
                    <div x-show="currentStep === 4"
                         x-transition:enter="transition ease-out duration-300 delay-75"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 absolute"
                         class="space-y-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 via-emerald-600 to-teal-600 rounded-3xl shadow-2xl mb-6 ring-8 ring-green-100 dark:ring-green-900/30">
                                <i class="bi bi-stars text-white text-5xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">IA Categoriza Automaticamente</h4>
                            <p class="text-lg text-slate-600 dark:text-slate-400">Inteligência artificial trabalhando para você</p>
                        </div>
                        <div class="space-y-6">
                            <div class="p-8 bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 dark:from-green-900/20 dark:via-emerald-900/20 dark:to-teal-900/20 rounded-2xl border-2 border-green-200 dark:border-green-700">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center">
                                        <i class="bi bi-robot text-white text-3xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-2xl font-bold text-slate-800 dark:text-white">Sistema Inteligente</h5>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Análise automática de produtos</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check2-circle text-2xl text-green-600 mt-0.5"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Análise de Nome e Código</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Sistema identifica padrões nos produtos</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check2-circle text-2xl text-green-600 mt-0.5"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Aprendizado Contínuo</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Melhora com cada upload realizado</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check2-circle text-2xl text-green-600 mt-0.5"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Sugestões Precisas</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Categorias sugeridas baseadas no histórico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                                <div class="flex items-start gap-3">
                                    <i class="bi bi-info-circle text-2xl text-blue-600"></i>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        <strong>Dica:</strong> Você sempre pode revisar e modificar as categorias sugeridas antes de salvar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Confirme e Salve -->
                    <div x-show="currentStep === 5"
                         x-transition:enter="transition ease-out duration-300 delay-75"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 absolute"
                         class="space-y-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-600 to-violet-600 rounded-3xl shadow-2xl mb-6 ring-8 ring-indigo-100 dark:ring-indigo-900/30">
                                <i class="bi bi-check2-circle text-white text-5xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Confirme e Salve</h4>
                            <p class="text-lg text-slate-600 dark:text-slate-400">Finalize seu upload e salve no sistema</p>
                        </div>
                        <div class="space-y-6">
                            <div class="p-8 bg-gradient-to-br from-indigo-50 via-purple-50 to-violet-50 dark:from-indigo-900/20 dark:via-purple-900/20 dark:to-violet-900/20 rounded-2xl border-2 border-indigo-200 dark:border-indigo-700">
                                <h5 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
                                    <i class="bi bi-clipboard-check text-2xl text-indigo-600"></i>
                                    Antes de Confirmar
                                </h5>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check-circle-fill text-2xl text-indigo-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Revise o Resumo</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Contagem total de produtos</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check-circle-fill text-2xl text-indigo-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Verifique Dados</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Preços e estoques corretos</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check-circle-fill text-2xl text-indigo-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Confirme Categorias</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Produtos bem organizados</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-check-circle-fill text-2xl text-indigo-500"></i>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-white">Salve no Sistema</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Histórico registrado</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="p-5 text-center bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <i class="bi bi-clock-history text-3xl text-indigo-500 mb-2"></i>
                                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Histórico Salvo</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Acesso posterior</p>
                                </div>
                                <div class="p-5 text-center bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <i class="bi bi-eye text-3xl text-indigo-500 mb-2"></i>
                                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Visualizar Detalhes</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Ver o que foi criado</p>
                                </div>
                                <div class="p-5 text-center bg-white dark:bg-slate-700 rounded-xl shadow-sm">
                                    <i class="bi bi-trash text-3xl text-indigo-500 mb-2"></i>
                                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Excluir Uploads</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Gerenciar histórico</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer com Navegação -->
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-10 py-6 flex items-center justify-between border-t border-slate-200 dark:border-slate-700">
                    <button @click="prevStep()"
                            x-show="currentStep > 1"
                            x-transition
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-100 dark:hover:bg-slate-600 transition-all duration-300 shadow hover:shadow-lg border border-slate-200 dark:border-slate-600">
                        <i class="bi bi-arrow-left text-lg"></i>
                        Anterior
                    </button>

                    <div x-show="currentStep <= 1" class="w-24"></div>

                    <div class="flex items-center gap-3">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step"
                                    class="transition-all duration-300 rounded-full"
                                    :class="step === currentStep ? 'w-10 h-3 bg-gradient-to-r from-blue-500 to-indigo-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-blue-400 hover:w-6'">
                            </button>
                        </template>
                    </div>

                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                            class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 hover:from-blue-600 hover:via-indigo-700 hover:to-purple-700 text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'" class="text-lg"></span>
                        <i class="bi text-xl" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-lg'"></i>
                    </button>
                </div>
                </div>
            </div>
        </div>
    @endif

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
                wire:click="closeDetailsModal"></div>

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
                        <button wire:click="closeDetailsModal"
                            class="w-10 h-10 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Conteúdo do Modal -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <!-- Estatísticas -->
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedUpload->total_products }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $selectedUpload->products_created }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Criados</div>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedUpload->products_updated }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Atualizados</div>
                            </div>
                            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $selectedUpload->products_skipped }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Ignorados</div>
                            </div>
                        </div>

                        <!-- Grid de 3 Colunas: Criados, Atualizados e Ignorados -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            @php
                                // Verificar se o summary tem arrays ou apenas contadores (formato antigo)
                                $summaryCreated = $selectedUpload->summary['created'] ?? [];
                                $summaryUpdated = $selectedUpload->summary['updated'] ?? [];
                                $summarySkipped = $selectedUpload->summary['skipped'] ?? [];

                                // Se forem números (formato antigo), criar arrays vazios
                                $created = is_array($summaryCreated) ? $summaryCreated : [];
                                $updated = is_array($summaryUpdated) ? $summaryUpdated : [];
                                $skipped = is_array($summarySkipped) ? $summarySkipped : [];

                                // Flag para saber se é formato antigo
                                $isOldFormat = !is_array($summaryCreated) || !is_array($summaryUpdated) || !is_array($summarySkipped);
                            @endphp

                            @if($isOldFormat)
                                <!-- Mensagem para formato antigo -->
                                <div class="col-span-full bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-info-circle-fill text-yellow-600 dark:text-yellow-400 text-xl flex-shrink-0 mt-0.5"></i>
                                        <div class="text-sm text-gray-800 dark:text-gray-200">
                                            <p class="font-bold mb-1">Upload Antigo</p>
                                            <p>Este upload foi criado com uma versão anterior do sistema. Os detalhes dos produtos não estão disponíveis, mas você pode ver os totais acima.</p>
                                            <p class="mt-2 text-xs">Faça um novo upload para ver todos os detalhes dos produtos.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Produtos Criados -->
                            @if(!$isOldFormat && count($created) > 0)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-plus-circle text-green-600"></i>
                                        Produtos Criados ({{ count($created) }})
                                    </h4>
                                    <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                                        @foreach($created as $item)
                                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800 hover:shadow-lg transition-shadow">
                                                <div class="font-semibold text-xs text-gray-900 dark:text-white truncate" title="{{ $item['name'] ?? 'N/A' }}">
                                                    {{ $item['name'] ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    Código: {{ $item['code'] ?? 'N/A' }}
                                                </div>
                                                @if(isset($item['price']))
                                                    <div class="text-xs text-green-600 dark:text-green-400 font-semibold mt-1">
                                                        R$ {{ number_format($item['price'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 text-center">
                                    <i class="bi bi-inbox text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum produto criado</p>
                                </div>
                            @endif

                            <!-- Produtos Atualizados -->
                            @if(!$isOldFormat && count($updated) > 0)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-arrow-repeat text-blue-600"></i>
                                        Produtos Atualizados ({{ count($updated) }})
                                    </h4>
                                    <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                                        @foreach($updated as $item)
                                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800 hover:shadow-lg transition-shadow">
                                                <div class="font-semibold text-xs text-gray-900 dark:text-white truncate" title="{{ $item['name'] ?? 'N/A' }}">
                                                    {{ $item['name'] ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    Código: {{ $item['code'] ?? 'N/A' }}
                                                </div>
                                                @if(isset($item['price']))
                                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-1">
                                                        R$ {{ number_format($item['price'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 text-center">
                                    <i class="bi bi-inbox text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum produto atualizado</p>
                                </div>
                            @endif

                            <!-- Produtos Ignorados -->
                            @if(!$isOldFormat && count($skipped) > 0)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="bi bi-dash-circle text-orange-600"></i>
                                        Produtos Ignorados ({{ count($skipped) }})
                                    </h4>
                                    <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                                        @foreach($skipped as $item)
                                            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 border border-orange-200 dark:border-orange-800 hover:shadow-lg transition-shadow">
                                                <div class="font-semibold text-xs text-gray-900 dark:text-white truncate" title="{{ $item['name'] ?? $item['reason'] ?? 'N/A' }}">
                                                    {{ $item['name'] ?? 'Produto' }}
                                                </div>
                                                @if(isset($item['code']))
                                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Código: {{ $item['code'] }}
                                                    </div>
                                                @endif
                                                <div class="text-xs text-orange-600 dark:text-orange-400 mt-1 italic">
                                                    {{ $item['reason'] ?? 'Não especificado' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 text-center">
                                    <i class="bi bi-check-circle text-3xl text-green-400 mb-2"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum produto ignorado</p>
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
                                    <div class="absolute inset-0 bg-white/30 rounded-xl animate-ping"></div>
                                    <div class="relative w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                        <i class="bi bi-exclamation-triangle-fill text-white text-3xl"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-1">Confirmar Exclusão</h3>
                                    <p class="text-sm text-white/80">Esta ação não pode ser desfeita</p>
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
                                    <p class="font-bold mb-1">O que será excluído:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Histórico de upload</li>
                                        <li>Arquivo PDF/CSV associado</li>
                                        <li>Dados de produtos importados</li>
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
                                    <i class="bi bi-x-circle"></i>
                                    <span>Cancelar</span>
                                </div>
                            </button>
                            <button wire:click="deleteUpload" @click="show = false" type="button"
                                class="group relative px-6 py-4 bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 hover:from-red-700 hover:via-rose-700 hover:to-pink-700 text-white font-bold rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-2xl overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                                <div class="relative flex items-center justify-center gap-2">
                                    <i class="bi bi-trash"></i>
                                    <span>Confirmar</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS personalizado para badges editáveis -->
    <style>
        /* Animação float para partículas */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(148, 163, 184, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #a855f7, #6366f1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #9333ea, #4f46e5);
        }

        /* Tornar os cards de produto mais altos */
        .product-card-modern {
            min-height: 420px !important;
            height: auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        /* Reduzir espaçamento entre status e valores */
        .product-card-modern .card-body {
            padding: 0.8em 1em 0.5em 1em !important;
            min-height: auto !important;
            gap: 0.3em !important;
        }

        /* Corrigir imagem para não sair do quadrado */
        .product-card-modern .product-img-area {
            position: relative;
            overflow: visible !important;
            border-top-left-radius: 1.2em;
            border-top-right-radius: 1.2em;
        }

        .product-card-modern .product-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-top-left-radius: 1.2em;
            border-top-right-radius: 1.2em;
        }

        /* Corrigir categoria não sendo cortada */
        .product-card-modern .category-icon-wrapper {
            z-index: 10 !important;
            position: absolute !important;
        }

        /* Grupo de botões de ação */
        .btn-action-group {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        /* Botão de copiar nome (verde) */
        .btn-success {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
            transform: scale(1.05);
        }

        .btn-success:active {
            transform: scale(0.95);
        }

        /* Botão de copiar código (azul) */
        .btn-info {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-info:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            transform: scale(1.05);
        }

        .btn-info:active {
            transform: scale(0.95);
        }

        /* Animação de sucesso ao copiar */
        @keyframes copySuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .btn-success.copied,
        .btn-info.copied {
            animation: copySuccess 0.4s ease-in-out;
        }

        /* Inputs editáveis em badges */
        .editable-badge input {
            transition: all 0.2s ease;
            min-width: 60px;
            background: transparent;
            border: none;
            outline: none;
            color: inherit;
            font-weight: inherit;
            font-size: inherit;
            text-align: center;
        }

        .editable-badge input:focus {
            background-color: rgba(255, 255, 255, 0.95) !important;
            color: #374151 !important;
            font-weight: 600;
            border-radius: 4px;
            padding: 2px 4px;
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
        }

        .editable-badge input:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Inputs de preço */
        .editable-price-badge {
            display: flex !important;
            align-items: center !important;
            gap: 0.2rem !important;
        }

        .editable-price-badge input {
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            outline: none;
            color: inherit;
            font-weight: inherit;
            font-size: inherit;
            text-align: right;
            width: 60px;
        }

        .editable-price-badge input:focus {
            background-color: rgba(255, 255, 255, 0.95) !important;
            color: #374151 !important;
            font-weight: 600;
            border-radius: 4px;
            padding: 2px 4px;
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
        }

        .editable-price-badge input:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Título do produto editável */
        .product-title-editable input {
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            outline: none;
            color: inherit;
            font-weight: inherit;
            font-size: inherit;
            text-align: center;
            width: 100%;
        }

        .product-title-editable input:focus {
            background-color: rgba(147, 51, 234, 0.1) !important;
            border-radius: 6px;
            padding: 4px 8px;
            transform: scale(1.02);
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
        }

        .product-title-editable input:hover {
            background-color: rgba(147, 51, 234, 0.05);
            border-radius: 6px;
        }

        /* Efeito hover na imagem */
        .product-img-area .cursor-pointer:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }

        .product-img-area .cursor-pointer:active {
            transform: scale(0.98);
        }

        /* Select de status */
        .status-select {
            transition: all 0.2s ease;
            background: rgba(243, 244, 246, 0.8);
            border: 1px solid rgba(209, 213, 219, 0.5);
            border-radius: 1rem;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            outline: none;
            cursor: pointer;
        }

        .status-select:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(147, 51, 234, 0.5);
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.1);
            transform: scale(1.05);
        }

        .status-select:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(147, 51, 234, 0.3);
        }

        /* Select de categoria */
        .category-select {
            transition: all 0.2s ease;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(147, 51, 234, 0.1);
        }

        .category-select:hover {
            background: rgba(243, 244, 246, 0.9) !important;
            border-color: rgba(147, 51, 234, 0.5) !important;
            transform: scale(1.02);
        }

        .category-select:focus {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.2);
        }

        /* Loading states */
        .product-img.loading {
            filter: blur(1px);
            opacity: 0.6;
        }

        /* Animação de foco */
        @keyframes focusGlow {
            0% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(147, 51, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0); }
        }

        .editable-badge input:focus,
        .editable-price-badge input:focus,
        .product-title-editable input:focus {
            animation: focusGlow 0.6s ease-out;
        }

        /* Placeholder styles */
        .editable-badge input::placeholder,
        .editable-price-badge input::placeholder,
        .product-title-editable input::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
        }

        .editable-badge input:focus::placeholder,
        .editable-price-badge input:focus::placeholder,
        .product-title-editable input:focus::placeholder {
            color: rgba(55, 65, 81, 0.5);
        }
    </style>

    <!-- Script para upload de imagens (detecta produtos automaticamente) -->
    <script>
        // Função para atualizar ícone da categoria
        function updateCategoryIcon(productIndex, selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const iconClass = selectedOption.getAttribute('data-icon') || 'bi bi-box-seam';
            const iconElement = document.getElementById('category-icon-' + productIndex);

            if (iconElement) {
                // Remover todas as classes antigas
                iconElement.className = '';
                // Adicionar novas classes
                iconElement.className = iconClass + ' category-icon';
                console.log('\u00cdcone atualizado para produto ' + productIndex + ': ' + iconClass);
            }
        }

        // Função para limpar e copiar apenas o nome do produto
        function copyProductName(index, name) {
            let productName = name || 'Sem nome';

            // Limpeza: remover / ( ) - e colapsar múltiplos espaços
            try {
                productName = productName.replace(/[\/(\)\-]/g, '');
                productName = productName.replace(/\s+/g, ' ').trim();
            } catch (e) {
                console.warn('Erro ao limpar nome do produto antes de copiar', e);
            }

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productName).then(() => {
                    showCopySuccess(index, 'Nome copiado!', 'name');
                }).catch(err => {
                    console.error('Erro ao copiar nome:', err);
                    fallbackCopy(productName, index, 'name');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productName, index, 'name');
            }
        }

        // Função para copiar apenas o código do produto
        function copyProductCode(index, code) {
            // Remover pontos do código antes de copiar
            const productCode = (code || 'Sem código').replace(/\./g, '');

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productCode).then(() => {
                    showCopySuccess(index, 'Código copiado!', 'code');
                }).catch(err => {
                    console.error('Erro ao copiar código:', err);
                    fallbackCopy(productCode, index, 'code');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productCode, index, 'code');
            }
        }

        // Função para copiar informações do produto (mantida para compatibilidade)
        function copyProductInfo(index, name, code) {
            const productInfo = `Nome: ${name || 'Sem nome'}\nCódigo: ${code || 'Sem código'}`;

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productInfo).then(() => {
                    showCopySuccess(index, 'Informações copiadas!', 'info');
                }).catch(err => {
                    console.error('Erro ao copiar:', err);
                    fallbackCopy(productInfo, index, 'info');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productInfo, index, 'info');
            }
        }

        // Função de fallback para copiar
        function fallbackCopy(text, index, type) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                const message = type === 'name' ? 'Nome copiado!' :
                               type === 'code' ? 'Código copiado!' : 'Informações copiadas!';
                showCopySuccess(index, message, type);
            } catch (err) {
                console.error('Erro ao copiar:', err);
                showCopyError(index, 'Erro ao copiar', type);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        // Função para mostrar sucesso na cópia
        function showCopySuccess(index, message, type) {
            let copyButton;

            if (type === 'name') {
                copyButton = document.querySelector(`button[onclick*="copyProductName(${index}"]`);
            } else if (type === 'code') {
                copyButton = document.querySelector(`button[onclick*="copyProductCode(${index}"]`);
            } else {
                copyButton = document.querySelector(`button[onclick*="copyProductInfo(${index}"]`);
            }

            if (copyButton) {
                copyButton.classList.add('copied');
                const originalTitle = copyButton.title;
                copyButton.title = message;

                // Mudar ícone temporariamente
                const icon = copyButton.querySelector('i');
                if (icon) {
                    const originalClass = icon.className;
                    icon.className = 'bi bi-check2';

                    setTimeout(() => {
                        copyButton.classList.remove('copied');
                        copyButton.title = originalTitle;
                        icon.className = originalClass;
                    }, 1500);
                }
            }

            // Mostrar toast de sucesso
            showToast(message, 'success');
        }

        // Função para mostrar erro na cópia
        function showCopyError(index, message, type) {
            let copyButton;

            if (type === 'name') {
                copyButton = document.querySelector(`button[onclick*="copyProductName(${index}"]`);
            } else if (type === 'code') {
                copyButton = document.querySelector(`button[onclick*="copyProductCode(${index}"]`);
            } else {
                copyButton = document.querySelector(`button[onclick*="copyProductInfo(${index}"]`);
            }

            if (copyButton) {
                copyButton.title = message;
                setTimeout(() => {
                    copyButton.title = type === 'name' ? 'Copiar nome' :
                                     type === 'code' ? 'Copiar código' : 'Copiar nome e código';
                }, 2000);
            }

            // Mostrar toast de erro
            showToast(message, 'error');
        }

        // Função para mostrar toast
        function showToast(message, type) {
            // Remover toast anterior se existir
            const existingToast = document.getElementById('copy-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.id = 'copy-toast';
            toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                type === 'success'
                    ? 'bg-green-500 text-white'
                    : 'bg-red-500 text-white'
            }`;

            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);

            // Remover após 3 segundos
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }

        // Adicionar atalho de teclado Ctrl+C para copiar produto focado
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'c') {
                // Verificar se o foco está em um input de produto
                const activeElement = document.activeElement;
                if (activeElement && activeElement.tagName === 'INPUT') {
                    const productInput = activeElement.closest('.product-card-modern');
                    if (productInput) {
                        // Encontrar o índice do produto
                        const allCards = document.querySelectorAll('.product-card-modern');
                        const productIndex = Array.from(allCards).indexOf(productInput);

                        if (productIndex !== -1) {
                            e.preventDefault();

                            // Verificar qual tipo de input está focado
                            const wireModel = activeElement.getAttribute('wire:model.lazy');

                            if (wireModel && wireModel.includes('name')) {
                                // Copiar apenas o nome
                                const name = activeElement.value || 'Sem nome';
                                copyProductName(productIndex, name);
                            } else if (wireModel && wireModel.includes('product_code')) {
                                // Copiar apenas o código
                                const code = activeElement.value || 'Sem código';
                                copyProductCode(productIndex, code);
                            } else {
                                // Copiar informações gerais
                                const nameInput = productInput.querySelector('input[wire\\:model*="name"]');
                                const codeInput = productInput.querySelector('input[wire\\:model*="product_code"]');

                                const name = nameInput ? nameInput.value : 'Sem nome';
                                const code = codeInput ? codeInput.value : 'Sem código';

                                copyProductInfo(productIndex, name, code);
                            }
                        }
                    }
                }
            }
        });

        // Log inicial apenas para confirmar que o script foi carregado
        console.log('=== SCRIPT DE UPLOAD CARREGADO ===');
        console.log('Aguardando produtos...');

        let systemInitialized = false;

        function initUploadSystem() {
            console.log('Verificando se há produtos para inicializar...');

            // Debug: listar todos os elementos disponíveis
            const allElements = document.querySelectorAll('*[id*="product"]');
            console.log('Elementos com "product" no ID:', allElements.length);
            allElements.forEach(el => console.log('- Elemento encontrado:', el.id, el.tagName));

            // Verificar se existem produtos primeiro
            const productCards = document.querySelectorAll('[id^="product-image-"]');
            const imageInputs = document.querySelectorAll('[id^="image-input-"]');

            console.log('Cards buscados:', productCards.length);
            console.log('Inputs buscados:', imageInputs.length);

            if (productCards.length === 0 || imageInputs.length === 0) {
                console.log('Nenhum produto encontrado. Aguardando...');
                return false; // Não inicializar ainda
            }

            if (systemInitialized) {
                console.log('Sistema já foi inicializado, pulando...');
                return true;
            }

            console.log('=== INICIALIZANDO SISTEMA DE UPLOAD ===');
            console.log('Cards de produtos encontrados:', productCards.length);
            console.log('Inputs de imagem encontrados:', imageInputs.length);

            // Adicionar listeners aos inputs
            imageInputs.forEach(function(input, index) {
                const productIndex = input.id.replace('image-input-', '');
                console.log('Configurando input para produto:', productIndex);

                // Remover listener anterior se existir
                if (input.uploadHandler) {
                    input.removeEventListener('change', input.uploadHandler);
                }

                // Criar novo handler
                input.uploadHandler = function(event) {
                    console.log('=== UPLOAD INICIADO ===');
                    console.log('Produto:', productIndex);

                    const file = event.target.files[0];
                    if (!file) {
                        console.log('Nenhum arquivo selecionado');
                        return;
                    }

                    console.log('Arquivo:', file.name, 'Tamanho:', file.size);

                    // Validações básicas
                    if (!file.type.startsWith('image/')) {
                        alert('Selecione apenas arquivos de imagem');
                        event.target.value = '';
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        alert('Arquivo muito grande! Máximo 5MB');
                        event.target.value = '';
                        return;
                    }

                    // Ler arquivo
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const base64 = e.target.result;
                        console.log('Base64 gerado com sucesso');

                        // Atualizar imagem do card
                        const imgElement = document.getElementById('product-image-' + productIndex);
                        if (imgElement) {
                            console.log('Atualizando imagem do card...');
                            imgElement.src = base64;
                            console.log('Imagem atualizada!');

                            // Chamar método Livewire
                            if (window.Livewire) {
                                try {
                                    const component = window.Livewire.find('{{ $this->getId() }}');
                                    if (component) {
                                        component.call('updateProductImage', parseInt(productIndex), base64);
                                        console.log('Livewire sincronizado');
                                    }
                                } catch (error) {
                                    console.log('Erro Livewire:', error);
                                }
                            }
                        } else {
                            console.error('Imagem não encontrada:', 'product-image-' + productIndex);
                        }
                    };

                    reader.onerror = function() {
                        console.error('Erro ao ler arquivo');
                        alert('Erro ao processar imagem');
                    };

                    reader.readAsDataURL(file);
                };

                // Adicionar listener
                input.addEventListener('change', input.uploadHandler);
                console.log('Listener adicionado ao produto:', productIndex);
            });

            systemInitialized = true;
            console.log('=== SISTEMA DE UPLOAD INICIALIZADO COM SUCESSO ===');
            return true; // Inicialização bem-sucedida
        }

        // Função para verificar produtos periodicamente
        function checkForProducts() {
            console.log('Verificação periódica de produtos...');
            const success = initUploadSystem();
            if (success) {
                console.log('Produtos detectados na verificação periódica!');
                clearInterval(productCheckInterval);
            }
        }

        // Observer para detectar mudanças no DOM
        const observer = new MutationObserver(function(mutations) {
            let shouldCheck = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && ( // Elemento
                            node.id && node.id.includes('product') ||
                            node.querySelector && node.querySelector('[id*="product"]')
                        )) {
                            console.log('Mudança detectada no DOM com produtos');
                            shouldCheck = true;
                        }
                    });
                }
            });

            if (shouldCheck && !systemInitialized) {
                setTimeout(initUploadSystem, 100);
            }
        });

        // Iniciar observação do DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Eventos do Livewire
        document.addEventListener('livewire:updated', function() {
            console.log('Livewire updated - verificando produtos...');
            setTimeout(function() {
                const hasProducts = initUploadSystem();
                if (hasProducts) {
                    console.log('Produtos detectados via livewire:updated!');
                }
            }, 100);
        });

        document.addEventListener('livewire:load', function() {
            console.log('Livewire load - verificando produtos...');
            setTimeout(initUploadSystem, 100);
        });

        document.addEventListener('livewire:component:updated', function() {
            console.log('Livewire component updated - verificando produtos...');
            setTimeout(initUploadSystem, 100);
        });

        // Verificação periódica (como fallback)
        const productCheckInterval = setInterval(checkForProducts, 2000);

        // Parar verificação após 30 segundos
        setTimeout(function() {
            if (!systemInitialized) {
                console.log('Timeout: Parando verificação de produtos');
                clearInterval(productCheckInterval);
            }
        }, 30000);

        // Tentar inicializar quando o DOM estiver pronto
        function tryInitialize() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryInitialize);
                return;
            }

            // Tentar inicializar, mas não forçar se não há produtos
            initUploadSystem();
        }

        // Inicialização inicial
        tryInitialize();

        console.log('Sistema de detecção de produtos configurado');
    </script>

    <!-- Toast Notification System -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-toast', (event) => {
                showToast(event.type, event.message, event.duration || 5000);
            });

            Livewire.on('open-pdf', (event) => {
                window.open(event.url, '_blank');
            });
        });

        function showToast(type, message, duration) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // Cores e ícones por tipo
            const config = {
                success: {
                    bg: 'bg-gradient-to-r from-green-500 to-green-600',
                    icon: 'bi-check-circle-fill',
                    iconColor: 'text-white'
                },
                error: {
                    bg: 'bg-gradient-to-r from-red-500 to-red-600',
                    icon: 'bi-x-circle-fill',
                    iconColor: 'text-white'
                },
                warning: {
                    bg: 'bg-gradient-to-r from-yellow-500 to-yellow-600',
                    icon: 'bi-exclamation-triangle-fill',
                    iconColor: 'text-white'
                },
                info: {
                    bg: 'bg-gradient-to-r from-blue-500 to-blue-600',
                    icon: 'bi-info-circle-fill',
                    iconColor: 'text-white'
                }
            };

            const settings = config[type] || config.info;

            // Criar elemento toast
            const toast = document.createElement('div');
            toast.className = `toast-notification ${settings.bg} text-white px-6 py-4 rounded-xl shadow-2xl border border-white/20 backdrop-blur-sm flex items-center gap-3 transform transition-all duration-300 ease-out`;
            toast.style.cssText = 'opacity: 0; transform: translateX(400px);';

            toast.innerHTML = `
                <div class="flex-shrink-0">
                    <i class="bi ${settings.icon} ${settings.iconColor} text-2xl"></i>
                </div>
                <div class="flex-1 font-medium">
                    ${message}
                </div>
                <button onclick="this.closest('.toast-notification').remove()" class="flex-shrink-0 hover:bg-white/20 rounded-lg p-1 transition-colors duration-200">
                    <i class="bi bi-x-lg text-white text-sm"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animação de entrada
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 10);

            // Auto-remover após duração
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(400px)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    </script>
</div>
