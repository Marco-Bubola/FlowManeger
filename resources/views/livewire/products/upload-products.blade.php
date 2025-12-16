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
                    <div class="space-y-4 max-h-[550px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($uploadHistory as $upload)
                        <div class="group relative bg-gradient-to-br from-white via-slate-50 to-purple-50/30 dark:from-slate-800 dark:via-slate-800/90 dark:to-purple-900/20 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-purple-400 dark:hover:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-2xl hover:shadow-purple-500/20 hover:-translate-y-1 backdrop-blur-sm overflow-hidden">

                            <!-- Linha de status colorida no topo -->
                            @php $badge = $upload->status_badge; @endphp
                            <div class="h-1.5 bg-gradient-to-r from-{{ $badge['color'] }}-400 to-{{ $badge['color'] }}-600"></div>

                            <div class="p-5">
                                <!-- Header do Card -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-12 h-12 bg-gradient-to-br from-{{ $upload->file_type === 'pdf' ? 'red' : 'green' }}-400 to-{{ $upload->file_type === 'pdf' ? 'red' : 'green' }}-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="bi bi-file-earmark-{{ $upload->file_type === 'pdf' ? 'pdf' : 'spreadsheet' }} text-white text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-slate-800 dark:text-slate-200 truncate text-sm" title="{{ $upload->filename }}">
                                                {{ $upload->filename }}
                                            </h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-1">
                                                <i class="bi bi-calendar3"></i>
                                                {{ $upload->created_at->format('d/m/Y') }}
                                                <i class="bi bi-clock ml-2"></i>
                                                {{ $upload->created_at->format('H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Badge de Status -->
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $badge['color'] }}-100 dark:bg-{{ $badge['color'] }}-900/30 text-{{ $badge['color'] }}-700 dark:text-{{ $badge['color'] }}-300 border border-{{ $badge['color'] }}-200 dark:border-{{ $badge['color'] }}-700">
                                        <i class="bi {{ $badge['icon'] }} mr-1"></i>
                                        {{ $badge['label'] }}
                                    </span>
                                </div>

                                <!-- Estatísticas -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <!-- Total -->
                                    <div class="bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700/50 dark:to-slate-700/30 rounded-xl p-3 text-center border border-slate-300/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            <i class="bi bi-box-seam text-slate-600 dark:text-slate-400 text-sm"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-slate-700 dark:text-slate-200">{{ $upload->total_products }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Total</p>
                                    </div>

                                    <!-- Criados -->
                                    <div class="bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-900/20 rounded-xl p-3 text-center border border-green-300/50 dark:border-green-700/50 group-hover:scale-105 transition-transform duration-300">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            <i class="bi bi-plus-circle text-green-600 dark:text-green-400 text-sm"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $upload->products_created }}</p>
                                        <p class="text-xs text-green-600 dark:text-green-400 font-medium mt-1">Criados</p>
                                    </div>

                                    <!-- Atualizados -->
                                    <div class="bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-900/20 rounded-xl p-3 text-center border border-blue-300/50 dark:border-blue-700/50 group-hover:scale-105 transition-transform duration-300">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            <i class="bi bi-arrow-repeat text-blue-600 dark:text-blue-400 text-sm"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $upload->products_updated }}</p>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">Atualizados</p>
                                    </div>
                                </div>

                                <!-- Footer com duração -->
                                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                        <i class="bi bi-stopwatch"></i>
                                        <span class="font-medium">{{ $upload->formatted_duration }}</span>
                                    </div>
                                    @if($upload->status === 'completed')
                                    <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span class="font-bold">{{ number_format($upload->success_rate, 1) }}% sucesso</span>
                                    </div>
                                    @endif
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
            <!-- Barra de Ferramentas de Edição em Massa -->
            <div class="mb-6 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-tools text-slate-600 dark:text-slate-400 text-lg"></i>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Edição em Massa:</span>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Alterar Categoria de Todos -->
                        <div class="flex items-center gap-2" x-data="{
                            openCategory: false,
                            searchCategory: '',
                            selectedCategoryId: @entangle('bulkCategoryId'),
                            selectedCategoryName: 'Selecione...',
                            selectedCategoryIcon: 'bi-grid-3x3-gap-fill',
                            categories: {{ Js::from($categories->map(fn($cat) => [
                                'id' => $cat->id_category,
                                'name' => $cat->name,
                                'icon' => $this->getCategoryIcon($cat->icone)
                            ])) }},
                            get filteredCategories() {
                                if (!this.searchCategory) return this.categories;
                                return this.categories.filter(cat =>
                                    cat.name.toLowerCase().includes(this.searchCategory.toLowerCase())
                                );
                            },
                            selectCategory(category) {
                                this.selectedCategoryId = category.id;
                                this.selectedCategoryName = category.name;
                                this.selectedCategoryIcon = category.icon;
                                this.openCategory = false;
                                this.searchCategory = '';
                                $wire.set('bulkCategoryId', category.id);
                            }
                        }">
                            <label class="text-xs text-slate-600 dark:text-slate-400">Categoria:</label>
                            <div class="relative">
                                <button type="button"
                                        @click="openCategory = !openCategory"
                                        class="flex items-center justify-between px-3 py-1.5 rounded-lg border ">
                                    <span class="flex items-center gap-2 text-xs">
                                        <i :class="selectedCategoryIcon" class="text-purple-500"></i>
                                        <span x-text="selectedCategoryName"></span>
                                    </span>
                                    <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200 text-xs" :class="{ 'rotate-180': openCategory }"></i>
                                </button>

                                <div x-show="openCategory"
                                     x-transition
                                     @click.away="openCategory = false"
                                     class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl max-h-60 overflow-hidden">
                                    <!-- Search -->
                                    <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                        <input type="text"
                                               x-model="searchCategory"
                                               placeholder="Pesquisar..."
                                               class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                    </div>
                                    <!-- Options -->
                                    <div class="overflow-y-auto max-h-44">
                                        <template x-for="category in filteredCategories" :key="category.id">
                                            <button type="button"
                                                    @click="selectCategory(category)"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                <i :class="category.icon" class="text-purple-500 text-xs"></i>
                                                <span class="text-slate-700 dark:text-slate-200 text-xs" x-text="category.name"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredCategories.length === 0" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 text-center">
                                            Nenhuma categoria encontrada
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="bulkUpdateCategory($bulkCategoryId)"
                                    :disabled="!selectedCategoryId"
                                    class="px-3 py-1.5 rounded-lg bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-check-all mr-1"></i> Aplicar
                            </button>
                        </div>

                        <!-- Alterar Status de Todos -->
                        <div class="flex items-center gap-2" x-data="{
                            openStatus: false,
                            selectedStatus: @entangle('bulkStatus'),
                            selectedStatusName: 'Selecione...',
                            statuses: [
                                { value: 'ativo', name: 'Ativo', icon: 'bi-check-circle-fill', color: 'text-green-500' },
                                { value: 'inativo', name: 'Inativo', icon: 'bi-x-circle-fill', color: 'text-red-500' }
                            ],
                            selectStatus(status) {
                                this.selectedStatus = status.value;
                                this.selectedStatusName = status.name;
                                this.openStatus = false;
                                $wire.set('bulkStatus', status.value);
                            }
                        }">
                            <label class="text-xs text-slate-600 dark:text-slate-400">Status:</label>
                            <div class="relative">
                                <button type="button"
                                        @click="openStatus = !openStatus"
                                        class="flex items-center justify-between px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:border-green-400 focus:border-green-500 focus:ring-2 focus:ring-green-400/20 focus:outline-none transition-all duration-200 min-w-[150px]">
                                    <span class="text-xs" x-text="selectedStatusName"></span>
                                    <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200 text-xs" :class="{ 'rotate-180': openStatus }"></i>
                                </button>

                                <div x-show="openStatus"
                                     x-transition
                                     @click.away="openStatus = false"
                                     class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl overflow-hidden">
                                    <template x-for="status in statuses" :key="status.value">
                                        <button type="button"
                                                @click="selectStatus(status)"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                            <i :class="[status.icon, status.color]" class="text-xs"></i>
                                            <span class="text-slate-700 dark:text-slate-200 text-xs" x-text="status.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <button wire:click="bulkUpdateStatus($bulkStatus)"
                                    :disabled="!selectedStatus"
                                    class="px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white text-xs font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-check-all mr-1"></i> Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Produtos Extraídos -->
            <x-products-preview-original
                :products="$productsUpload ?? []"
                :categories="$categories ?? []"
                :show-back-button="true" />
        @endif
    </div>

    <!-- Modal de Dicas -->
    @if($showTipsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" wire:click="toggleTips"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <i class="bi bi-lightbulb-fill text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white" id="modal-title">
                                    Dicas para Upload de Produtos
                                </h3>
                                <p class="text-sm text-blue-100 mt-1">
                                    Siga estas orientações para um upload bem-sucedido
                                </p>
                            </div>
                        </div>
                        <button wire:click="toggleTips" class="text-white hover:text-blue-100 transition-colors duration-200">
                            <i class="bi bi-x-lg text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6 space-y-5">
                    <!-- Formato -->
                    <div class="flex items-start gap-4 p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/10 rounded-xl border-2 border-purple-200 dark:border-purple-700">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-lg flex items-center justify-center">
                                <i class="bi bi-file-earmark-pdf text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-purple-500"></i>
                                Formato do Arquivo
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Use <strong>PDF</strong> com produtos listados ou <strong>CSV</strong> com colunas organizadas (código, nome, preço, estoque)
                            </p>
                        </div>
                    </div>

                    <!-- Tamanho -->
                    <div class="flex items-start gap-4 p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 rounded-xl border-2 border-green-200 dark:border-green-700">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                <i class="bi bi-hdd text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                Tamanho do Arquivo
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                O arquivo deve ter no <strong>máximo 2MB</strong>. Arquivos maiores serão rejeitados.
                            </p>
                        </div>
                    </div>

                    <!-- Duplicatas -->
                    <div class="flex items-start gap-4 p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/10 rounded-xl border-2 border-yellow-200 dark:border-yellow-700">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <i class="bi bi-exclamation-triangle text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-yellow-500"></i>
                                Produtos Duplicados
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Produtos existentes serão <strong>identificados automaticamente</strong>. Você poderá escolher se deseja somar estoque, substituir ou pular.
                            </p>
                        </div>
                    </div>

                    <!-- Categorização IA -->
                    <div class="flex items-start gap-4 p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-900/10 rounded-xl border-2 border-indigo-200 dark:border-indigo-700">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-lg flex items-center justify-center">
                                <i class="bi bi-robot text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-indigo-500"></i>
                                Categorização Inteligente
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                O sistema usará <strong>Inteligência Artificial</strong> para sugerir categorias automaticamente baseado no nome e código dos produtos.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 flex justify-end">
                    <button wire:click="toggleTips"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium transition-all duration-300 shadow-lg hover:shadow-xl">
                        <i class="bi bi-check-lg mr-2"></i>
                        Entendi!
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
