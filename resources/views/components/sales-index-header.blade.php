@props([
    'title' => 'Vendas',
    'description' => '',
    'backRoute' => null,
    'currentStep' => null,
    'steps' => [],
    'totalSales' => 0,
    'pendingSales' => 0,
    'todaySales' => 0,
    'totalRevenue' => 0,
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
                    <i class="bi {{ $showSteps ? 'bi-plus-circle' : 'bi-cart' }} text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400">
                        {{ $description ?: ($showSteps ? 'Registre uma nova venda no sistema seguindo os passos' : 'Gerencie e monitore todas as suas vendas') }}
                    </p>

                    @if(!$showSteps)
                    <!-- Estatísticas Rápidas -->
                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                            <i class="bi bi-cart-check text-emerald-600 dark:text-emerald-400"></i>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $totalSales }} vendas</span>
                        </div>
                        @if($pendingSales > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-xl border border-yellow-200 dark:border-yellow-700">
                            <i class="bi bi-clock text-yellow-600 dark:text-yellow-400"></i>
                            <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">{{ $pendingSales }} pendentes</span>
                        </div>
                        @endif
                        @if($todaySales > 0)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-700">
                            <i class="bi bi-calendar-check text-blue-600 dark:text-blue-400"></i>
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $todaySales }} hoje</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @if($showQuickActions && !$showSteps)
            <!-- Ações Rápidas -->
            <div class="flex items-center gap-3">
                <!-- Nova Venda -->
                <a href="{{ route('sales.create') }}"
                   class="group relative inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-xl shadow-purple-500/25 hover:shadow-2xl hover:shadow-purple-500/40 transform hover:scale-105">
                    <i class="bi bi-plus-circle text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                    <div class="flex flex-col items-start">
                        <span class="text-sm font-bold">Nova Venda</span>
                        <span class="text-xs opacity-90">Registrar venda</span>
                    </div>
                </a>

                <!-- Botões de Ação Secundários -->
                <div class="flex items-center gap-2">
                    <button wire:click="exportSales"
                            class="group p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Exportar vendas">
                        <i class="bi bi-download group-hover:scale-110 transition-transform duration-200"></i>
                    </button>
                    <button wire:click="$refresh"
                            class="group p-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                            title="Atualizar lista">
                        <i class="bi bi-arrow-clockwise group-hover:rotate-180 transition-transform duration-300"></i>
                    </button>
                    <button @click="showFilters = !showFilters"
                            class="group p-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                            :class="{ 'ring-4 ring-purple-300 dark:ring-purple-600': showFilters }"
                            title="Filtros avançados">
                        <i class="bi bi-funnel group-hover:scale-110 transition-transform duration-200"></i>
                    </button>
                </div>
            </div>
            @endif

            <!-- Steppers Modernos -->
            @if($showSteps && count($steps) > 0)
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-6">
                    @foreach($steps as $index => $step)
                        @php $stepNumber = $index + 1; @endphp

                        <!-- Step -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                                :class="currentStep === {{ $stepNumber }} ? 'bg-gradient-to-br {{ $step['gradient'] ?? 'from-indigo-500 to-purple-500' }} text-white shadow-lg shadow-indigo-500/30' : (currentStep > {{ $stepNumber }} ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                <i class="bi {{ $step['icon'] ?? 'bi-circle' }} text-xl" x-show="currentStep === {{ $stepNumber }}"></i>
                                <i class="bi bi-check-lg text-xl" x-show="currentStep > {{ $stepNumber }}"></i>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <p class="text-lg font-bold transition-colors duration-300"
                                        :class="currentStep === {{ $stepNumber }} ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">{{ $step['title'] }}</p>
                                    <i class="bi bi-check-circle-fill text-green-500 ml-2 text-lg" x-show="currentStep > {{ $stepNumber }}"></i>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                            </div>
                        </div>

                        <!-- Connector -->
                        @if(!$loop->last)
                        <div class="w-16 h-1 rounded-full transition-all duration-300"
                            :class="currentStep >= {{ $stepNumber + 1 }} ? 'bg-gradient-to-r {{ $step['connector_gradient'] ?? 'from-indigo-500 to-purple-500' }}' : 'bg-gray-300 dark:bg-zinc-600'"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
