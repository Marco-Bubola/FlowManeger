<div>
    <!-- Modal Toggle (Ativar/Desativar) -->
    @if ($showToggleModal)
        <div x-data="{ modalOpen: true }" x-show="modalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99999] overflow-y-auto"
            @keydown.escape.window="modalOpen = false; $wire.set('showToggleModal', false)">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-900/40 backdrop-blur-md"></div>

            <!-- Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                    x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
                    class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                    <!-- Efeitos visuais -->
                    <div class="absolute inset-0 bg-gradient-to-br from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-500/5 via-transparent to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-500/5"></div>
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-400/20 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-600/20 rounded-full blur-3xl"></div>

                    <!-- Conteúdo -->
                    <div class="relative z-10">
                        <!-- Header -->
                        <div class="text-center pt-8 pb-4">
                            <div class="relative inline-flex items-center justify-center">
                                <div class="absolute w-24 h-24 bg-gradient-to-r from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-400/30 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-500/30 rounded-full animate-pulse"></div>
                                <div class="absolute w-20 h-20 bg-gradient-to-r from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-500/40 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-600/40 rounded-full animate-ping"></div>
                                <div class="relative w-16 h-16 bg-gradient-to-br from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-500 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-600 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-{{ $this->consortium->status === 'active' ? 'pause-circle' : 'play-circle' }} text-2xl text-white"></i>
                                </div>
                            </div>

                            <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                <i class="bi bi-question-circle text-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-500 mr-2"></i>
                                {{ $this->consortium->status === 'active' ? 'Desativar' : 'Ativar' }} Consórcio?
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                {{ $this->consortium->name }}
                            </p>
                        </div>

                        <!-- Corpo -->
                        <div class="px-8 pb-4">
                            <div class="bg-gradient-to-r from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-50 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-50 dark:from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-900/20 dark:to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-900/20 rounded-2xl p-4 border border-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-200/50 dark:border-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-700/50">
                                <div class="text-center">
                                    @if($this->consortium->status === 'active')
                                        <i class="bi bi-pause-circle text-3xl text-orange-500 mb-2"></i>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            O consórcio será marcado como <span class="font-bold text-orange-600">cancelado</span>.
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                            Não será possível adicionar participantes ou realizar sorteios, mas todos os dados serão preservados.
                                        </p>
                                    @else
                                        <i class="bi bi-play-circle text-3xl text-emerald-500 mb-2"></i>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            O consórcio voltará ao status <span class="font-bold text-emerald-600">ativo</span>.
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                            Será possível adicionar participantes e realizar sorteios novamente.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="px-8 pb-8">
                            <div class="flex gap-4">
                                <button wire:click="$set('showToggleModal', false)" @click="modalOpen = false"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 dark:hover:from-gray-600 dark:hover:to-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 dark:border-gray-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-x-circle mr-2"></i>Cancelar
                                </button>

                                <button wire:click="toggleStatus" @click="modalOpen = false"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-500 to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-600 hover:from-{{ $this->consortium->status === 'active' ? 'orange' : 'emerald' }}-600 hover:to-{{ $this->consortium->status === 'active' ? 'red' : 'teal' }}-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                    <i class="bi bi-{{ $this->consortium->status === 'active' ? 'pause-circle' : 'play-circle' }} mr-2"></i>
                                    {{ $this->consortium->status === 'active' ? 'Desativar' : 'Ativar' }}
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <button @click="modalOpen = false; $wire.set('showToggleModal', false)"
                                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200">
                                    <i class="bi bi-escape mr-1"></i>Pressione ESC para cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Excluir -->
    @if ($showDeleteModal)
        <div x-data="{ modalOpen: true }" x-show="modalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99999] overflow-y-auto"
            @keydown.escape.window="modalOpen = false; $wire.set('showDeleteModal', false)">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-gray-900/80 to-red-900/40 backdrop-blur-md"></div>

            <!-- Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                    x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
                    class="relative w-full max-w-lg mx-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">

                    <!-- Efeitos visuais -->
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-transparent to-pink-500/5"></div>
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-br from-pink-400/20 to-red-600/20 rounded-full blur-3xl"></div>

                    <!-- Conteúdo -->
                    <div class="relative z-10">
                        <!-- Header -->
                        <div class="text-center pt-8 pb-4">
                            <div class="relative inline-flex items-center justify-center">
                                <div class="absolute w-24 h-24 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-full animate-pulse"></div>
                                <div class="absolute w-20 h-20 bg-gradient-to-r from-red-500/40 to-pink-600/40 rounded-full animate-ping"></div>
                                <div class="relative w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="bi bi-exclamation-triangle text-2xl text-white animate-bounce"></i>
                                </div>
                            </div>

                            <h3 class="mt-4 text-2xl font-bold text-gray-800 dark:text-white">
                                <i class="bi bi-shield-exclamation text-red-500 mr-2"></i>
                                Confirmar Exclusão
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                <i class="bi bi-info-circle text-amber-500 mr-1"></i>
                                Esta ação não pode ser desfeita
                            </p>
                        </div>

                        <!-- Corpo -->
                        <div class="px-8 pb-4">
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-4 border border-red-200/50 dark:border-red-700/50">
                                <div class="text-center">
                                    <i class="bi bi-people text-3xl text-red-500 mb-2"></i>
                                    <p class="text-gray-700 dark:text-gray-300">
                                        Você está prestes a excluir o consórcio:
                                    </p>
                                    <p class="font-bold text-red-600 dark:text-red-400 text-lg mt-1">
                                        "{{ $this->consortium->name }}"
                                    </p>
                                </div>
                                <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-700">
                                    <p class="text-sm text-amber-800 dark:text-amber-300">
                                        ⚠️ Só é possível excluir consórcios <span class="font-bold">sem participantes e sem sorteios</span>.
                                    </p>
                                    <p class="text-sm text-amber-700 dark:text-amber-400 mt-2">
                                        Se o consórcio já possui dados, use "Desativar" ao invés de excluir.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-center gap-2 text-red-600 dark:text-red-400">
                                <i class="bi bi-clock-history"></i>
                                <span class="text-sm font-medium">Esta ação é permanente</span>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="px-8 pb-8">
                            <div class="flex gap-4">
                                <button wire:click="$set('showDeleteModal', false)" @click="modalOpen = false"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 dark:from-gray-700 dark:to-gray-600 dark:hover:from-gray-600 dark:hover:to-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-300 dark:border-gray-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="bi bi-x-circle mr-2"></i>Cancelar
                                </button>

                                <button wire:click="deleteConsortium" @click="modalOpen = false"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border-2 border-red-400/50">
                                    <i class="bi bi-trash3 mr-2"></i>Excluir
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <button @click="modalOpen = false; $wire.set('showDeleteModal', false)"
                                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200">
                                    <i class="bi bi-escape mr-1"></i>Pressione ESC para cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
<!-- Modais renderizados FORA do header (no root do body) -->
