@props([
    'name' => 'image',
    'id' => 'image',
    'wireModel' => 'image',
    'acceptedTypes' => 'image/*',
    'maxSize' => '2MB',
    'supportedFormats' => 'PNG, JPG, JPEG',
    'title' => 'Adicionar Imagem',
    'description' => 'Clique para selecionar ou arraste e solte sua imagem aqui',
    'existingImage' => null,
    'newImage' => null,
    'width' => 'w-full',
    'height' => 'h-96',
    'showPreview' => true
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
            @if(($newImage && $showPreview) || ($existingImage && $showPreview && !$newImage))
                <!-- Preview da Imagem com Efeitos -->
                <div class="relative group/image">
                    @if($newImage)
                        <!-- Nova imagem selecionada -->
                        <img src="{{ $newImage->temporaryUrl() }}" class="w-40 h-40 object-cover rounded-2xl shadow-2xl group-hover:scale-110 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    @elseif(is_string($existingImage))
                        <!-- Imagem existente -->
                        <img src="{{ $existingImage }}" class="w-40 h-40 object-cover rounded-2xl shadow-2xl group-hover:scale-110 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    @else
                        <!-- Imagem existente (objeto) -->
                        <img src="{{ $existingImage->temporaryUrl() }}" class="w-40 h-40 object-cover rounded-2xl shadow-2xl group-hover:scale-110 transition-all duration-500 border-4 border-white dark:border-slate-700 group-hover:border-blue-300 dark:group-hover:border-blue-600" loading="lazy">
                    @endif

                    <!-- Badge de sucesso animado -->
                    <div class="absolute -top-3 -right-3 bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500 text-white rounded-full p-3 shadow-2xl shadow-green-500/40 animate-bounce">
                        <i class="bi bi-check-lg text-lg font-bold"></i>
                        <!-- Ring de sucesso -->
                        <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-30"></div>
                    </div>

                    <!-- Overlay com ícone de edição -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="bi bi-pencil-square text-white text-2xl"></i>
                    </div>
                </div>

                <div class="text-center space-y-3 mt-6">
                    <p class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-400 bg-clip-text text-transparent flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-emerald-500 mr-3 text-2xl animate-pulse"></i>
                        @if($newImage)
                            Nova imagem selecionada!
                        @else
                            Imagem carregada com sucesso!
                        @endif
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Clique para alterar a imagem</p>
                </div>
            @else
                <!-- Estado vazio com animações -->
                <div class="text-center space-y-8">
                    <div class="relative">
                        <!-- Ícone principal com efeitos -->
                        <div class="relative">
                            <i class="bi bi-cloud-upload text-8xl text-slate-300 dark:text-slate-600 group-hover:text-blue-400 dark:group-hover:text-blue-500 transition-all duration-500 transform group-hover:scale-110"></i>

                            <!-- Ícone de + flutuante -->
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center shadow-lg animate-bounce group-hover:scale-125 transition-transform duration-300">
                                <i class="bi bi-plus text-white text-lg font-bold"></i>
                            </div>

                            <!-- Círculos decorativos -->
                            <div class="absolute -top-4 -left-4 w-6 h-6 bg-blue-400/30 rounded-full animate-pulse"></div>
                            <div class="absolute -bottom-6 left-8 w-4 h-4 bg-purple-400/40 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-700 via-blue-600 to-indigo-600 dark:from-slate-300 dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent group-hover:from-blue-600 group-hover:to-purple-600 transition-all duration-500">
                            <i class="bi bi-images mr-3"></i>
                            {{ $title }}
                        </h3>
                        <p class="text-lg text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                            <span class="font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ $description }}</span>
                        </p>

                        <!-- Tags informativos melhorados -->
                        <div class="flex items-center justify-center space-x-6 pt-4">
                            <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                <i class="bi bi-file-earmark-image text-blue-500 mr-3 text-lg"></i>
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ $supportedFormats }}</span>
                            </div>
                            <div class="flex items-center bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-4 py-3 rounded-xl shadow-lg border border-slate-200/50 dark:border-slate-600/50 group-hover:scale-105 transition-transform duration-300">
                                <i class="bi bi-hdd text-emerald-500 mr-3 text-lg"></i>
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Máx. {{ $maxSize }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <input wire:model="{{ $wireModel }}" id="{{ $id }}" name="{{ $name }}" type="file" class="hidden" accept="{{ $acceptedTypes }}">
    </label>
</div>

@error($wireModel)
<div class="flex items-center justify-center p-4 bg-red-50/80 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl mt-6 backdrop-blur-sm">
    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-4 text-xl"></i>
    <p class="text-red-600 dark:text-red-400 font-bold text-lg">{{ $message }}</p>
</div>
@enderror

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        33% { transform: translateY(-10px) rotate(1deg); }
        66% { transform: translateY(-5px) rotate(-1deg); }
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>
