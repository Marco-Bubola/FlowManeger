
<div>
    @if($show && $achievement)
    <div x-data="{ visible: true }"
         x-init="setTimeout(() => { visible = false; $wire.hideNotification(); }, 5000)"
         x-show="visible"
         x-transition:enter="transition ease-out duration-500 transform"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-300 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         @click="visible = false; $wire.hideNotification()"
         class="fixed bottom-4 right-4 z-50 max-w-md cursor-pointer">

        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl shadow-2xl p-6 text-white border-2 border-purple-400 relative overflow-hidden">
            <!-- Animated background particles -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute w-32 h-32 bg-white/10 rounded-full -top-8 -left-8 animate-pulse"></div>
                <div class="absolute w-24 h-24 bg-white/10 rounded-full -bottom-4 -right-4 animate-pulse" style="animation-delay: 0.5s"></div>
            </div>

            <div class="relative z-10">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shadow-lg animate-bounce">
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
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-white/20 to-white/10 flex items-center justify-center">
                            <i class="{{ $achievement['icon'] ?? 'bi bi-star-fill' }} text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h5 class="font-bold text-base mb-1 flex items-center gap-2">
                                {{ $achievement['name'] ?? 'Nova Conquista' }}
                                <span class="px-2 py-0.5 text-xs rounded-full capitalize" style="background: {{ $achievement['rarity_color'] ?? '#CD7F32' }}">
                                    {{ $achievement['rarity'] ?? 'bronze' }}
                                </span>
                            </h5>
                            <p class="text-sm text-purple-100 mb-2">{{ $achievement['description'] ?? '' }}</p>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="px-2 py-1 bg-yellow-500/30 rounded-full font-semibold">
                                    <i class="bi bi-star-fill mr-1"></i>+{{ $achievement['points'] ?? 0 }} pontos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-purple-200 mt-3 text-center">Clique para fechar</p>
            </div>
        </div>
    </div>
    @endif

    <style>
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .animate-bounce {
            animation: bounce 1s ease-in-out infinite;
        }
    </style>
</div>
