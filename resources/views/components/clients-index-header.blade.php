@props([
    'title' => 'Clientes',
    'description' => '',
    'backRoute' => null,
    'currentStep' => null,
    'steps' => [],
    'totalClients' => 0,
    'activeClients' => 0,
    'premiumClients' => 0,
    'newClientsThisMonth' => 0,
    'showQuickActions' => true,
    'showSteps' => false
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <!-- Título e Estatísticas -->
            <div class="flex items-center gap-6">
                @if($backRoute)
                <!-- Botão voltar melhorado -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <!-- Ícone principal -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-people' }} text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400">
                        {{ $description ?: ($showSteps ? 'Registre um novo cliente no sistema seguindo os passos' : 'Gerencie e monitore toda sua base de clientes') }}
                    </p>

                    @if(!$showSteps)
                    <!-- Estatísticas Rápidas -->
                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                            <i class="bi bi-people text-emerald-600 dark:text-emerald-400"></i>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $totalClients }} clientes</span>
                        </div>
                        @if($activeClients > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-xl border border-yellow-200 dark:border-yellow-700">
                            <i class="bi bi-star text-yellow-600 dark:text-yellow-400"></i>
                            <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">{{ $activeClients }} ativos</span>
                        </div>
                        @endif
                        @if($newClientsThisMonth > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                            <i class="bi bi-calendar-check text-blue-600 dark:text-blue-400"></i>
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $newClientsThisMonth }} novos</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @if($showQuickActions && !$showSteps)
            <div class="mt-6 flex items-start gap-6 justify-between">
                <!-- Controles vindos da view (slot) -->
                <div class="flex-1">
                    @if(trim(str_replace(['\n','\r',' '], '', $slot)) !== '')
                        <div class="bg-white/60 dark:bg-slate-800/60 rounded-2xl p-3 shadow-sm border border-white/10">
                            {{ $slot }}
                        </div>
                    @endif
                </div>

                <!-- Ações Rápidas alinhadas à direita -->
                <div class="flex-shrink-0 flex flex-col items-end gap-3">
                    <a href="{{ route('clients.create') }}"
                       class="group relative inline-flex items-center gap-3 px-5 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-xl shadow-purple-500/25 hover:shadow-2xl hover:shadow-purple-500/40 transform hover:scale-105">
                        <i class="bi bi-plus-circle text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                        <div class="flex flex-col items-start">
                            <span class="text-sm font-bold">Novo Cliente</span>
                            <span class="text-xs opacity-90">Cadastrar cliente</span>
                        </div>
                    </a>

                    <div class="flex items-center gap-2">
                        <button wire:click="exportClients"
                                class="group p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                title="Exportar clientes">
                            <i class="bi bi-download group-hover:scale-110 transition-transform duration-200"></i>
                        </button>
                        <button wire:click="$refresh"
                                class="group p-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                title="Atualizar lista">
                            <i class="bi bi-arrow-clockwise group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
