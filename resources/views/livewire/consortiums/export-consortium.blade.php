<div>
    @if($showModal)
    <div x-data="{
        modalOpen: true,
        exportType: @entangle('exportType'),
        downloading: false
    }"
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] overflow-y-auto"
         @keydown.escape.window="modalOpen = false; $wire.closeModal()">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-slate-900/80 to-emerald-900/40 backdrop-blur-md"></div>

        <!-- Container do Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                 class="relative w-full max-w-4xl bg-gradient-to-br from-white/95 to-slate-50/95 dark:from-slate-800/95 dark:to-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden">

                <!-- Header -->
                <div class="relative bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                <i class="bi bi-file-earmark-arrow-down text-xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    Exportar Consórcio
                                </h3>
                                <p class="text-xs text-white/80">
                                    Escolha o formato e o tipo de exportação
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeModal"
                                @click="modalOpen = false"
                                class="w-8 h-8 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg flex items-center justify-center text-white transition-all duration-200">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Conteúdo -->
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Seletor de Consórcio (quando não vem de detalhes) -->
                        @if(!empty($availableConsortiums))
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-2">
                                    <i class="bi bi-piggy-bank text-emerald-500 mr-2"></i>
                                    Selecione o Consórcio:
                                </label>
                                <select wire:model.live="consortiumId"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 font-medium">
                                    @foreach($availableConsortiums as $cons)
                                        <option value="{{ $cons['id'] }}">{{ $cons['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">
                                <i class="bi bi-toggles text-emerald-500 mr-2"></i>
                                Selecione o Formato de Exportação:
                            </label>

                            @if(!empty($participants))
                                <div class="mb-4 space-y-2">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Filtros rápidos:</span>
                                        <button type="button" wire:click="$set('clientId', null)"
                                            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-emerald-400">
                                            Todos os clientes
                                        </button>
                                        <button type="button" wire:click="$set('clientId', $participants[0]['client_id'] ?? null)"
                                            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-emerald-400">
                                            Usar primeiro cliente
                                        </button>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Exportações direcionadas (opcional)</label>
                                        <select wire:model.live="clientId"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                                            <option value="">Todos / não filtrar</option>
                                            @foreach($participants as $option)
                                                <option value="{{ $option['client_id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Use apenas se quiser PDF/Excel/Imagem de um cliente específico.</p>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @if($consortiumId)
                                    <!-- PDF Completo do Consórcio -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="pdf_full"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all duration-200 hover:shadow-lg hover:border-red-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-pdf text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        PDF Completo
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Todos participantes com detalhes
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Excel Completo do Consórcio -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="excel_full"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all duration-200 hover:shadow-lg hover:border-green-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-excel text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Excel Completo
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Planilha com todos os dados
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-green-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- PDF por Cliente (ZIP) -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="pdf_clients_zip"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 transition-all duration-200 hover:shadow-lg hover:border-amber-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-archive text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        PDF por Cliente (ZIP)
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Gera um PDF para cada cliente do consórcio
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-amber-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- PDF Clientes (único PDF) -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="pdf_clients_single"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 transition-all duration-200 hover:shadow-lg hover:border-orange-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-people-fill text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        PDF Clientes (único)
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Todos os clientes em um único PDF
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-orange-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- PDF Contrato/Regras -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="pdf_contract"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-slate-500 peer-checked:bg-slate-50 dark:peer-checked:bg-slate-900/20 transition-all duration-200 hover:shadow-lg hover:border-slate-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-slate-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-earmark-text text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Contrato/Regras (PDF)
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Gera um PDF do contrato do consórcio
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-slate-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Imagem Completa do Consórcio (PNG) -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="image_full"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-cyan-500 peer-checked:bg-cyan-50 dark:peer-checked:bg-cyan-900/20 transition-all duration-200 hover:shadow-lg hover:border-cyan-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-sky-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-image text-xl text-white"></i>
                                                </div>
                                            <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Imagem (PNG)
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Exporta o PDF em formato de imagem
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-cyan-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>
                                @endif

                                @if($clientId)
                                    <!-- PDF do Cliente -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="pdf_client"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 transition-all duration-200 hover:shadow-lg hover:border-purple-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-person text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        PDF Cliente
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Todos consórcios do cliente
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Excel do Cliente -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="excel_client"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all duration-200 hover:shadow-lg hover:border-blue-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-table text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Excel Cliente
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Planilha dos consórcios
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-blue-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Imagem do Cliente -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="image_client"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20 transition-all duration-200 hover:shadow-lg hover:border-teal-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-image-fill text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Imagem do Cliente
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Exporta os dados do cliente em PNG
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-teal-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>
                                @endif

                                @if(!$consortiumId && !$clientId)
                                    <!-- Excel Todos Consórcios -->
                                    <label class="relative cursor-pointer group block md:col-span-2">
                                        <input type="radio"
                                               wire:model.live="exportType"
                                               value="excel_all"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 transition-all duration-200 hover:shadow-lg hover:border-orange-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-earmark-spreadsheet text-xl text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Excel - Todos Consórcios
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Resumo geral de todos os consórcios
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-orange-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Descrição do formato selecionado -->
                        <div class="mt-4 p-4 bg-slate-100 dark:bg-slate-700/50 rounded-xl">
                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-info-circle text-blue-500 mr-1"></i>
                                Sobre este formato:
                            </h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                @switch($exportType)
                                    @case('pdf_full')
                                        Gera um PDF completo com todas as informações do consórcio, incluindo lista de participantes, parcelas pagas/não pagas, contemplações e estatísticas gerais.
                                        @break
                                    @case('excel_full')
                                        Exporta para Excel todas as informações dos participantes do consórcio em formato de planilha, facilitando análises e filtros personalizados.
                                        @break
                                    @case('pdf_client')
                                        Cria um PDF personalizado com todos os consórcios do cliente, mostrando parcelas, contemplações e produtos resgatados.
                                        @break
                                    @case('excel_client')
                                        Exporta para Excel todos os consórcios do cliente com informações detalhadas sobre pagamentos e contemplações.
                                        @break
                                    @case('pdf_clients_zip')
                                        Gera um pacote ZIP com um PDF separado para cada cliente participante deste consórcio.
                                        @break
                                    @case('pdf_clients_single')
                                        Gera um único PDF contendo todos os clientes e suas participações neste consórcio.
                                        @break
                                    @case('excel_all')
                                        Gera uma planilha Excel com resumo de todos os consórcios cadastrados no sistema, ideal para análises gerenciais.
                                        @break
                                    @case('image_full')
                                        Converte o PDF do consórcio em uma imagem PNG (requer Imagick instalado no servidor).
                                        @break
                                    @case('image_client')
                                        Converte o PDF do cliente em PNG, útil para compartilhamentos rápidos (requer Imagick).
                                        @break
                                    @case('pdf_contract')
                                        Gera um PDF de contrato/regras do consórcio com informações básicas preenchidas.
                                        @break
                                    @default
                                        Selecione um formato para ver mais informações.
                                @endswitch
                            </p>
                        </div>
                    </div>

                    <!-- Footer com botões -->
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button wire:click="closeModal"
                                @click="modalOpen = false"
                                class="px-6 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-medium transition-all duration-200">
                            <i class="bi bi-x-circle mr-2"></i>
                            Cancelar
                        </button>
                        <button wire:click="export"
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-bold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="bi bi-download mr-2"></i>
                            Exportar Agora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
