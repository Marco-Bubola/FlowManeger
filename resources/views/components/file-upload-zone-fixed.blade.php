@props([
    'wireModel' => 'pdf_file',
    'fileTypes' => '.pdf,.csv',
    'maxSize' => '2MB',
    'currentFile' => null
])

<div class="relative">
    <form wire:submit.prevent="processUpload" class="space-y-8">
        <!-- Upload Drop Zone -->
        <div class="group relative">
            <label for="{{ $wireModel }}"
                   class="flex flex-col items-center justify-center w-full h-80 border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-2xl cursor-pointer bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-800 dark:to-neutral-700 hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/20 dark:hover:to-pink-900/20 hover:border-purple-400 dark:hover:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-xl group-hover:scale-[1.02]">

                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-6 text-center">
                    @if($currentFile)
                        <!-- Estado: Arquivo Selecionado -->
                        <div class="space-y-4">
                            <div class="relative">
                                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-blue-500 rounded-2xl flex items-center justify-center shadow-xl">
                                    <i class="bi bi-file-earmark-check text-3xl text-white"></i>
                                </div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-check text-white text-sm"></i>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xl font-bold text-emerald-700 dark:text-emerald-300">
                                    Arquivo Selecionado
                                </h3>
                                <p class="text-neutral-700 dark:text-neutral-300 font-medium">
                                    {{ $currentFile->getClientOriginalName() }}
                                </p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ round($currentFile->getSize() / 1024, 2) }} KB
                                </p>
                            </div>

                            <!-- Botão para trocar arquivo -->
                            <button type="button"
                                    onclick="document.getElementById('{{ $wireModel }}').click()"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="bi bi-arrow-repeat mr-2"></i>
                                Trocar Arquivo
                            </button>
                        </div>
                    @else
                        <!-- Estado: Aguardando Upload -->
                        <div class="space-y-4">
                            <div class="relative">
                                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-xl animate-bounce">
                                    <i class="bi bi-cloud-upload text-3xl text-white"></i>
                                </div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                    <i class="bi bi-plus text-white text-sm"></i>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                                    Clique para enviar
                                </h3>
                                <p class="text-lg text-neutral-700 dark:text-neutral-300">
                                    ou arraste e solte seu arquivo aqui
                                </p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    Suporta {{ str_replace('.', '', $fileTypes) }} • Máximo {{ $maxSize }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <input wire:model="{{ $wireModel }}"
                       id="{{ $wireModel }}"
                       type="file"
                       class="hidden"
                       accept="{{ $fileTypes }}">
            </label>
        </div>

        <!-- Error Message -->
        @error($wireModel)
            <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-xl p-4 shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-white"></i>
                    </div>
                    <p class="text-red-700 dark:text-red-400 font-medium">{{ $message }}</p>
                </div>
            </div>
        @enderror

        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('products.index') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-neutral-500 to-neutral-600 hover:from-neutral-600 hover:to-neutral-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="bi bi-x-circle mr-2"></i>
                Cancelar
            </a>

            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    wire:loading.attr="disabled"
                    @if(!$currentFile) disabled @endif>
                <span wire:loading.remove>
                    <i class="bi bi-upload mr-2"></i>
                    Processar Arquivo
                </span>
                <span wire:loading class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processando...
                </span>
            </button>
        </div>

        <!-- Efeitos visuais de fundo -->
        <div class="absolute inset-0 -z-10 opacity-30">
            <div class="absolute top-4 left-4 w-32 h-32 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
            <div class="absolute top-4 right-4 w-32 h-32 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-4 left-1/2 w-32 h-32 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
        </div>
    </form>

    <!-- CSS para animações - DENTRO DA DIV RAIZ -->
    <style>
    @keyframes blob {
        0% {
            transform: translate(0px, 0px) scale(1);
        }
        33% {
            transform: translate(30px, -50px) scale(1.1);
        }
        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        100% {
            transform: translate(0px, 0px) scale(1);
        }
    }

    .animate-blob {
        animation: blob 7s infinite;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    .animation-delay-4000 {
        animation-delay: 4s;
    }
    </style>
</div>
