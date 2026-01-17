@props(['achievement'])

<div x-data="{ show: false, animateOut: false }"
     x-init="setTimeout(() => show = true, 100); setTimeout(() => { animateOut = true; setTimeout(() => show = false, 500); }, 5000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
     x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 transform translate-y-8 scale-95"
     class="fixed bottom-4 right-4 z-50 max-w-md"
     style="display: none;">

    <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl shadow-2xl p-6 text-white border-2 border-purple-400 relative overflow-hidden">
        <!-- Animated background particles -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute w-32 h-32 bg-white/10 rounded-full -top-8 -left-8 animate-pulse"></div>
            <div class="absolute w-24 h-24 bg-white/10 rounded-full -bottom-4 -right-4 animate-pulse" style="animation-delay: 0.5s"></div>
        </div>

        <div class="relative z-10">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shadow-lg animate-bounce-slow">
                    <i class="bi bi-trophy-fill text-2xl text-white"></i>
                </div>
                <div>
                    <h4 class="font-bold text-lg">🎉 Conquista Desbloqueada!</h4>
                    <p class="text-sm text-purple-200">Novo troféu adicionado</p>
                </div>
            </div>

            <!-- Achievement info -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gradient-to-br from-white/20 to-white/10 flex items-center justify-center">
                        <i class="{{ $achievement->icon }} text-3xl"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="font-bold text-lg mb-1 flex items-center gap-2">
                            {{ $achievement->name }}
                            <span class="px-2 py-0.5 text-xs rounded-full" style="background: {{ $achievement->rarity_color }}">
                                {{ ucfirst($achievement->rarity) }}
                            </span>
                        </h5>
                        <p class="text-sm text-purple-100 mb-2">{{ $achievement->description }}</p>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2 py-1 bg-yellow-500/30 rounded-full font-semibold">
                                <i class="bi bi-star-fill mr-1"></i>+{{ $achievement->points }} pontos
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .animate-bounce-slow {
        animation: bounce-slow 1s ease-in-out infinite;
    }
</style>
