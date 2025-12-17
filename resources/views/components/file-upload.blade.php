@props([
    'name' => 'file',
    'id' => 'file',
    'wireModel' => 'file',
    'acceptedTypes' => '.pdf,.csv',
    'maxSize' => '10MB',
    'supportedFormats' => 'PDF, CSV',
    'title' => 'Adicionar Arquivo',
    'description' => 'Clique para selecionar ou arraste e solte seu arquivo aqui',
    'existingFile' => null,
    'newFile' => null,
    'width' => 'w-full',
    'height' => 'h-96',
])

<div class="flex items-center justify-center {{ $width }}">
    <label for="{{ $id }}" class="group relative flex flex-col items-center justify-center {{ $width }} {{ $height }} border-3 border-dashed border-slate-300 dark:border-slate-600 rounded-3xl cursor-pointer
           bg-gradient-to-br from-white/80 via-blue-50/50 to-indigo-50/30
           dark:from-slate-800/80 dark:via-blue-900/20 dark:to-indigo-900/10
           hover:from-blue-50/80 hover:via-indigo-50/60 hover:to-purple-50/40
           dark:hover:from-slate-700/80 dark:hover:via-blue-900/30 dark:hover:to-indigo-900/20
           backdrop-blur-xl transition-all duration-500 ease-out
           hover:border-blue-400 dark:hover:border-blue-500
           hover:shadow-2xl hover:shadow-blue-500/20
           transform hover:scale-[1.02]">

        <!-- Efeito de brilho animado -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 rounded-3xl animate-pulse"></div>

        <!-- Partículas flutuantes -->
        <div class="absolute inset-0 overflow-hidden rounded-3xl">
            <div class="absolute w-2 h-2 bg-blue-400/30 rounded-full animate-float" style="top: 20%; left: 15%; animation-delay: 0.2s;"></div>
            <div class="absolute w-1 h-1 bg-purple-400/40 rounded-full animate-float" style="top: 60%; right: 20%; animation-delay: 0.8s;"></div>
            <div class="absolute w-3 h-3 bg-indigo-300/20 rounded-full animate-float" style="bottom: 30%; left: 30%; animation-delay: 1.2s;"></div>
        </div>

        <div class="relative flex flex-col items-center justify-center px-10 py-10 z-10">
            @if($newFile)
                <!-- Preview do Arquivo Selecionado -->
                <div class="relative group/file w-full h-full flex flex-col items-center justify-center">
                    <!-- Ícone do arquivo baseado no tipo -->
                    <div class="relative mb-6">
                        @php
                            $extension = strtolower(pathinfo($newFile->getClientOriginalName(), PATHINFO_EXTENSION));
                            $isPdf = $extension === 'pdf';
                        @endphp

                        <div class="w-32 h-32 bg-gradient-to-br {{ $isPdf ? 'from-red-500 to-red-600' : 'from-emerald-500 to-emerald-600' }} rounded-3xl flex items-center justify-center shadow-2xl group-hover:scale-110 transition-all duration-500">
                            <i class="bi {{ $isPdf ? 'bi-file-pdf-fill' : 'bi-file-earmark-spreadsheet-fill' }} text-white text-6xl"></i>
                        </div>

                        <!-- Badge de sucesso animado -->
                        <div class="absolute -top-2 -right-2 bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500 text-white rounded-full p-2 shadow-2xl shadow-green-500/40 animate-bounce">
                            <i class="bi bi-check-lg text-sm font-bold"></i>
                        </div>
                    </div>

                    <!-- Informações do arquivo -->
                    <div class="text-center space-y-3 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md px-6 py-4 rounded-2xl shadow-lg max-w-sm">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[250px]" title="{{ $newFile->getClientOriginalName() }}">
                            {{ $newFile->getClientOriginalName() }}
                        </p>
                        <div class="flex items-center justify-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <i class="bi bi-file-earmark"></i>
                                {{ strtoupper($extension) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <i class="bi bi-hdd"></i>
                                {{ number_format($newFile->getSize() / 1024, 2) }} KB
                            </span>
                        </div>
                        <p class="text-xs font-semibold bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-400 bg-clip-text text-transparent flex items-center justify-center gap-2 mt-3">
                            <i class="bi bi-check-circle-fill text-emerald-500 animate-pulse"></i>
                            Clique para alterar
                        </p>
                    </div>
                </div>
            @else
                <!-- Estado vazio com animações -->
                <div class="text-center space-y-8">
                    <div class="relative">
                        <!-- Ícone principal com efeitos -->
                        <div class="relative">
                            <i class="bi bi-cloud-upload text-8xl text-slate-300 dark:text-slate-600 group-hover:text-blue-400 dark:group-hover:text-blue-500 transition-all duration-500 transform group-hover:scale-110"></i>

                            <!-- Ícone de + flutuante -->
                            <div class="absolute -top-3 -right-3 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-xl shadow-blue-500/50 group-hover:scale-110 transition-transform duration-300">
                                <i class="bi bi-plus-lg text-white text-lg font-bold"></i>
                            </div>

                            <!-- Círculo de pulso -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-32 h-32 bg-blue-400/20 rounded-full animate-ping"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-slate-700 via-blue-600 to-indigo-700 dark:from-white dark:via-blue-300 dark:to-indigo-200 bg-clip-text text-transparent group-hover:from-blue-600 group-hover:via-indigo-600 group-hover:to-purple-600 transition-all duration-500">
                            {{ $title }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-300 font-medium">
                            {{ $description }}
                        </p>

                        <!-- Formatos suportados -->
                        <div class="flex items-center justify-center gap-4 pt-4">
                            @foreach((is_array($supportedFormats) ? $supportedFormats : explode(',', $supportedFormats)) as $format)
                                <div class="px-4 py-2 bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-xl group-hover:from-blue-100 group-hover:to-indigo-100 dark:group-hover:from-blue-900/30 dark:group-hover:to-indigo-900/30 transition-all duration-300 shadow-sm">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ trim($format) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tamanho máximo -->
                        <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center justify-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            Tamanho máximo: {{ $maxSize }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input oculto -->
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            wire:model.live="{{ $wireModel }}"
            accept="{{ $acceptedTypes }}"
            class="hidden"
        />
    </label>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) translateX(0px); }
        25% { transform: translateY(-10px) translateX(5px); }
        50% { transform: translateY(-5px) translateX(-5px); }
        75% { transform: translateY(-15px) translateX(3px); }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
</style>
