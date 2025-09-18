@props([
    'wireModel' => 'pdf_file',
    'currentFile' => null
])

<div class="space-y-6">
    <form wire:submit.prevent="processUpload" class="space-y-6">
        <!-- Upload Area -->
        <div class="flex items-center justify-center w-full">
            <label for="{{ $wireModel }}" class="flex flex-col items-center justify-center w-full h-80 border-2 transition-colors duration-200">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    @if($currentFile)
                        <div class="text-center">
                            <i class="bi bi-file-earmark-check text-6xl text-green-600 dark:text-green-400 mb-4"></i>
                            <p class="text-lg font-medium text-green-700 dark:text-green-300 mb-2">Arquivo selecionado</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $currentFile->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ round($currentFile->getSize() / 1024, 2) }} KB</p>
                        </div>
                    @else
                        <i class="bi bi-cloud-upload text-6xl text-purple-400 dark:text-purple-300 mb-4"></i>
                        <p class="mb-2 text-lg text-gray-700 dark:text-gray-300"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">PDF ou CSV (Máx. 2MB)</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center">
                                <i class="bi bi-file-earmark-pdf text-red-500 mr-1"></i>
                                PDF
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-file-earmark-spreadsheet text-green-500 mr-1"></i>
                                CSV
                            </div>
                        </div>
                    @endif
                </div>
                <input wire:model="{{ $wireModel }}" id="{{ $wireModel }}" type="file" class="hidden" accept=".pdf,.csv">
            </label>
        </div>

        @error($wireModel)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-3"></i>
                    <p class="text-red-700 dark:text-red-400">{{ $message }}</p>
                </div>
            </div>
        @enderror

        <!-- Instruções -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4 flex items-center">
                <i class="bi bi-info-circle-fill mr-2"></i>
                Instruções para Upload
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-blue-700 dark:text-blue-300 mb-2 flex items-center">
                        <i class="bi bi-file-earmark-pdf text-red-500 mr-2"></i>
                        Arquivo PDF
                    </h4>
                    <ul class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                        <li>• Extrai produtos automaticamente</li>
                        <li>• Reconhece códigos e preços</li>
                        <li>• Formato específico necessário</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-blue-700 dark:text-blue-300 mb-2 flex items-center">
                        <i class="bi bi-file-earmark-spreadsheet text-green-500 mr-2"></i>
                        Arquivo CSV
                    </h4>
                    <ul class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                        <li>• Nome, preço, quantidade</li>
                        <li>• Uma linha por produto</li>
                        <li>• Separado por vírgulas</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botões Modernos -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('products.index') }}"
               class="group relative inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-gray-300 backdrop-blur-sm text-center">
                <i class="bi bi-x-circle mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                Cancelar
                <!-- Efeito hover ring -->
                <div class="absolute inset-0 rounded-2xl bg-gray-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <button type="submit"
                    class="group relative inline-flex items-center justify-center flex-1 px-6 py-3 rounded-2xl bg-gradient-to-br from-purple-500 via-pink-500 to-rose-600 hover:from-purple-600 hover:via-pink-600 hover:to-rose-700 text-white transition-all duration-300 shadow-lg hover:shadow-xl border border-purple-300 backdrop-blur-sm"
                    wire:loading.attr="disabled"
                    @if(!$currentFile) disabled @endif>
                <span wire:loading.remove class="flex items-center">
                    <i class="bi bi-upload mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    Processar Arquivo
                </span>
                <span wire:loading class="flex items-center">
                    <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                    Processando...
                </span>
                <!-- Efeito hover ring -->
                <div class="absolute inset-0 rounded-2xl bg-purple-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>
        </div>
    </form>
</div>
