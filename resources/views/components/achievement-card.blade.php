@props(['achievement', 'unlocked' => false, 'progress' => null])

<div class="group relative bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-6 border-2 transition-all duration-300 hover:scale-105 hover:shadow-2xl {{ $unlocked ? 'border-' . $achievement->rarity . '-400' : 'border-slate-300 dark:border-slate-700 opacity-60' }}">
    <!-- Badge de raridade -->
    <div class="absolute -top-3 -right-3 w-12 h-12 rounded-full flex items-center justify-center shadow-lg {{ $unlocked ? 'animate-bounce-slow' : '' }}" style="background: {{ $unlocked ? $achievement->rarity_color : '#94A3B8' }}">
        <i class="{{ $achievement->rarity_icon }} text-white text-xl"></i>
    </div>

    <!-- Ícone principal -->
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center {{ $unlocked ? 'bg-gradient-to-br from-purple-500 to-indigo-600 animate-pulse-slow' : 'bg-slate-300 dark:bg-slate-700' }} shadow-lg">
            <i class="{{ $achievement->icon }} text-2xl {{ $unlocked ? 'text-white' : 'text-slate-500' }}"></i>
        </div>

        <div class="flex-1 min-w-0">
            <!-- Título -->
            <h3 class="font-bold text-lg {{ $unlocked ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-600' }} mb-1 flex items-center gap-2">
                {{ $achievement->name }}
                @if($achievement->is_secret && !$unlocked)
                    <i class="bi bi-lock-fill text-sm"></i>
                @endif
            </h3>

            <!-- Descrição -->
            <p class="text-sm {{ $unlocked ? 'text-slate-600 dark:text-slate-400' : 'text-slate-400 dark:text-slate-600' }} mb-3">
                {{ $unlocked || !$achievement->is_secret ? $achievement->description : '???' }}
            </p>

            <!-- Pontos e categoria -->
            <div class="flex items-center gap-3 text-xs">
                <span class="px-3 py-1 rounded-full {{ $unlocked ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }} font-semibold">
                    <i class="bi bi-star-fill mr-1"></i>{{ $achievement->points }} pts
                </span>
                <span class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 capitalize">
                    {{ __('achievements.' . $achievement->category) ?? $achievement->category }}
                </span>
            </div>

            <!-- Barra de progresso (se aplicável) -->
            @if($progress !== null && !$unlocked)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400 mb-1">
                        <span>Progresso</span>
                        <span>{{ $progress['current'] ?? 0 }}/{{ $progress['target'] ?? 100 }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full transition-all duration-500" style="width: {{ min((($progress['current'] ?? 0) / ($progress['target'] ?? 1)) * 100, 100) }}%"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Data de desbloqueio -->
    @if($unlocked && isset($unlockedAt))
        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-500 flex items-center gap-2">
            <i class="bi bi-calendar-check"></i>
            <span>Desbloqueado em {{ $unlockedAt->format('d/m/Y H:i') }}</span>
        </div>
    @endif
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }

    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }

    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>
