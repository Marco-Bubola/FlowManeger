@props([
    'title' => 'Nova Venda',
    'description' => 'Registre uma nova venda no sistema seguindo os passos',
    'backRoute' => null,
    'currentStep' => 1,
    'steps' => []
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex items-center justify-between">
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

                <!-- Ícone principal e título -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="bi bi-plus-circle text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">
                        💼 {!! $description !!}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Slot de ações (botões) passado pelo componente pai --}}
                {!! $actions ?? '' !!}

                <!-- Steppers Modernos -->
                @if(count($steps) > 0)
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-6">
                        @foreach($steps as $index => $step)
                            @php $stepNumber = $index + 1; @endphp

                            <!-- Step -->
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                                    :class="$wire.currentStep === {{ $stepNumber }} ? 'bg-gradient-to-br {{ $step['gradient'] ?? 'from-indigo-500 to-purple-500' }} text-white shadow-lg shadow-indigo-500/30' : ($wire.currentStep > {{ $stepNumber }} ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                    <i class="bi {{ $step['icon'] ?? 'bi-circle' }} text-xl" x-show="$wire.currentStep === {{ $stepNumber }}"></i>
                                    <i class="bi bi-check-lg text-xl" x-show="$wire.currentStep > {{ $stepNumber }}"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-lg font-bold transition-colors duration-300"
                                            :class="$wire.currentStep === {{ $stepNumber }} ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">{{ $step['title'] }}</p>
                                        <i class="bi bi-check-circle-fill text-green-500 ml-2 text-lg" x-show="$wire.currentStep > {{ $stepNumber }}"></i>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                                </div>
                            </div>

                            <!-- Connector -->
                            @if(!$loop->last)
                            <div class="w-16 h-1 rounded-full transition-all duration-300"
                                :class="$wire.currentStep >= {{ $stepNumber + 1 }} ? 'bg-gradient-to-r {{ $step['connector_gradient'] ?? 'from-indigo-500 to-purple-500' }}' : 'bg-gray-300 dark:bg-zinc-600'"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
