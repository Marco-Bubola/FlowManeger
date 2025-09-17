@props([
    'steps',
    'currentStep' => 1,
    'showStepNumbers' => true
])

<!-- Stepper Container com Glassmorphism -->
<div class="flex items-center justify-center p-6">
    <div class="relative flex items-center space-x-8 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-3xl px-8 py-6 shadow-2xl shadow-blue-500/10 border border-white/20 dark:border-slate-700/50">
        <!-- Efeito de brilho de fundo -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-transparent to-purple-500/5 rounded-3xl"></div>

        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isActive = $currentStep >= $stepNumber;
                $isCompleted = $currentStep > $stepNumber;
            @endphp

            <!-- Step Container -->
            <div class="relative flex items-center group">
                <!-- Step Circle -->
                <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl transition-all duration-500 transform group-hover:scale-105
                    {{ $isActive
                        ? 'bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 text-white shadow-2xl shadow-blue-500/40'
                        : 'bg-gradient-to-br from-white to-slate-100 dark:from-slate-700 dark:to-slate-600 text-slate-400 border-2 border-slate-200 dark:border-slate-600' }}">

                    @if($isCompleted)
                        <i class="bi bi-check-lg text-2xl animate-bounce"></i>
                        <!-- Efeito de sucesso -->
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-green-400/20 to-emerald-400/20 animate-pulse"></div>
                    @else
                        @if($showStepNumbers)
                            <span class="text-2xl font-bold">{{ $stepNumber }}</span>
                        @else
                            <i class="{{ $step['icon'] ?? 'bi bi-circle' }} text-2xl"></i>
                        @endif
                    @endif

                    <!-- Ring de destaque para step ativo -->
                    @if($isActive && !$isCompleted)
                        <div class="absolute -inset-2 rounded-2xl bg-gradient-to-r from-blue-400 to-purple-400 opacity-20 animate-pulse"></div>
                    @endif
                </div>

                <!-- Step Info -->
                <div class="ml-6 min-w-0">
                    <div class="flex items-center space-x-3 mb-1">
                        <h3 class="text-lg font-bold {{ $isActive ? 'text-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-400' }} transition-colors duration-300">
                            {{ $step['title'] }}
                        </h3>
                        @if($isCompleted)
                            <div class="flex items-center justify-center w-6 h-6 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse">
                                <i class="bi bi-check text-white text-xs font-bold"></i>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm {{ $isActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-500 dark:text-slate-500' }} transition-colors duration-300 leading-relaxed">
                        {{ $step['description'] }}
                    </p>
                </div>
            </div>

            <!-- Connector animado (não mostrar no último step) -->
            @if(!$loop->last)
                <div class="relative flex items-center mx-4">
                    <!-- Linha de base -->
                    <div class="w-24 h-1 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <!-- Progresso animado -->
                        <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-700 ease-out
                            {{ $currentStep > $stepNumber ? 'w-full' : 'w-0' }}">
                        </div>
                    </div>

                    <!-- Partículas flutuantes quando ativo -->
                    @if($currentStep > $stepNumber)
                        <div class="absolute inset-0 overflow-hidden">
                            <div class="absolute w-1 h-1 bg-blue-400 rounded-full animate-ping" style="left: 20%; animation-delay: 0.2s;"></div>
                            <div class="absolute w-1 h-1 bg-purple-400 rounded-full animate-ping" style="left: 60%; animation-delay: 0.6s;"></div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach

        <!-- Efeito de borda brilhante -->
        <div class="absolute inset-0 rounded-3xl bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-50 pointer-events-none"></div>
    </div>
</div>
