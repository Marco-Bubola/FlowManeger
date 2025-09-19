@props([
    'title' => 'Novo Cliente',
    'description' => '',
    'backRoute' => null,
    'currentStep' => null,
    'steps' => [],
    'showSteps' => false
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-green-50/90 to-emerald-50/80 dark:from-slate-800/90 dark:via-green-900/30 dark:to-emerald-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-8">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-green-400/20 to-teal-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-emerald-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Título e Descrição -->
            <div class="flex items-center gap-6">
                @if($backRoute)
                <!-- Botão voltar melhorado -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-green-50 dark:from-slate-800 dark:to-slate-700 hover:from-green-50 hover:to-emerald-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <!-- Ícone principal -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 via-green-500 to-teal-500 rounded-2xl shadow-xl shadow-green-500/25">
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-person-plus' }} text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400">
                        {{ $description ?: 'Cadastre um novo cliente no sistema com todas as informações necessárias' }}
                    </p>

                    @if($showSteps && !empty($steps))
                    <!-- Steps Progress -->
                    <div class="flex items-center gap-4 mt-4">
                        @foreach($steps as $index => $step)
                        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                            <!-- Step Circle -->
                            <div class="relative flex items-center justify-center w-10 h-10 rounded-full
                                {{ $index + 1 <= ($currentStep ?? 1) ? 'bg-gradient-to-br from-emerald-500 to-green-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}
                                shadow-lg transition-all duration-300">
                                @if($index + 1 < ($currentStep ?? 1))
                                    <i class="bi bi-check text-sm"></i>
                                @elseif($index + 1 == ($currentStep ?? 1))
                                    <i class="bi {{ $step['icon'] ?? 'bi-circle' }} text-sm"></i>
                                @else
                                    <span class="text-sm font-bold">{{ $index + 1 }}</span>
                                @endif
                            </div>

                            <!-- Step Info -->
                            <div class="ml-3 min-w-0">
                                <p class="text-sm font-semibold {{ $index + 1 <= ($currentStep ?? 1) ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500' }}">
                                    {{ $step['title'] ?? "Passo $index" }}
                                </p>
                                <p class="text-xs {{ $index + 1 <= ($currentStep ?? 1) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                    {{ $step['description'] ?? '' }}
                                </p>
                            </div>

                            <!-- Connector Line -->
                            @if(!$loop->last)
                            <div class="flex-1 mx-4 h-0.5 {{ $index + 1 < ($currentStep ?? 1) ? 'bg-gradient-to-r from-emerald-500 to-green-500' : 'bg-slate-200 dark:bg-slate-700' }} transition-all duration-300"></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ações rápidas (se necessário) -->
            <div class="flex items-center gap-3">
                <button wire:click="$refresh"
                        class="group p-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                        title="Atualizar formulário">
                    <i class="bi bi-arrow-clockwise group-hover:rotate-180 transition-transform duration-300"></i>
                </button>
            </div>
        </div>
    </div>
</div>
