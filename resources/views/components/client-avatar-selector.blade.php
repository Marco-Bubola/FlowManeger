@props([
    'avatarOptions' => [],
    'selectedAvatar' => null,
    'wireModel' => 'avatar_cliente'
])

<div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl p-8 shadow-lg border border-white/20 dark:border-slate-700/50 h-full">
    <!-- Header do componente -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent flex items-center">
            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center mr-3">
                <i class="bi bi-image text-white text-sm"></i>
            </div>
            Selecione um Avatar
        </h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
            Escolha um avatar que represente o cliente
        </p>
    </div>

    <!-- Grid de Avatares com efeitos modernos -->
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
        @foreach($avatarOptions as $index => $avatar)
            <label class="relative cursor-pointer group block">
                <input type="radio"
                       wire:model="{{ $wireModel }}"
                       value="{{ $avatar }}"
                       class="sr-only peer">

                <!-- Container do Avatar com efeitos -->
                <div class="relative w-20 h-20 rounded-2xl border-3 border-slate-200/50 dark:border-slate-600/50 overflow-hidden
                           peer-checked:border-emerald-500 peer-checked:ring-4 peer-checked:ring-emerald-200/50 dark:peer-checked:ring-emerald-400/30
                           group-hover:border-emerald-300 dark:group-hover:border-emerald-400
                           group-hover:shadow-xl group-hover:shadow-emerald-500/25
                           transition-all duration-300 transform group-hover:scale-105 peer-checked:scale-105
                           flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-800">

                    <!-- Imagem do Avatar -->
                    <img src="{{ $avatar }}"
                         alt="Avatar Option {{ $index + 1 }}"
                         class="w-full h-full object-cover transition-all duration-300 group-hover:scale-110"
                         loading="eager"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                    <!-- Fallback caso a imagem não carregue -->
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-600 dark:to-slate-700 rounded-xl" style="display: none;">
                        <i class="bi bi-person text-slate-400 dark:text-slate-500 text-3xl"></i>
                    </div>

                    <!-- Overlay de hover -->
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 opacity-0 group-hover:opacity-100 peer-checked:opacity-20 transition-opacity duration-300"></div>
                </div>

                <!-- Ícone de Selecionado -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-0 peer-checked:opacity-100 transition-all duration-300 z-10">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center shadow-lg">
                        <i class="bi bi-check text-white text-sm font-bold"></i>
                    </div>
                </div>

                <!-- Efeito de ring selecionado -->
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500/30 to-teal-500/30 opacity-0 peer-checked:opacity-100 transition-opacity duration-300 pointer-events-none animate-pulse"></div>
            </label>
        @endforeach
    </div>

    <!-- Erro de validação -->
    @error($wireModel)
        <div class="mt-6 p-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl border border-red-200 dark:border-red-700">
            <div class="flex items-center">
                <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center mr-3">
                    <i class="bi bi-exclamation text-white text-xs"></i>
                </div>
                <p class="text-sm text-red-600 dark:text-red-400 font-medium">
                    {{ $message }}
                </p>
            </div>
        </div>
    @enderror

    <!-- Preview do avatar selecionado -->
    @if($selectedAvatar)
    <div class="mt-6 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
        <div class="flex items-center">
            <img src="{{ $selectedAvatar }}"
                 alt="Avatar Selecionado"
                 class="w-12 h-12 rounded-xl border-2 border-emerald-300 dark:border-emerald-600 shadow-md">
            <div class="ml-3">
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                    <i class="bi bi-check-circle mr-1"></i>
                    Avatar Selecionado
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400">
                    Este será o avatar do cliente
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
