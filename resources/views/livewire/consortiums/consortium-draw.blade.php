<div class="max-w-7xl mx-auto px-4 py-8" x-data="{ spinning: false }">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('consortiums.show', $consortium) }}"
                    class="flex items-center justify-center w-10 h-10 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    <i class="bi bi-arrow-left text-slate-600 dark:text-slate-400"></i>
                </a>
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 dark:from-purple-400 dark:via-pink-400 dark:to-red-400 bg-clip-text text-transparent">
                        Sistema de Sorteio
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {{ $consortium->name }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-400">
                    Sorteio #{{ $drawNumber }}
                </span>
            </div>
        </div>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Esquerda - Info e Ação -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Card de Sorteio -->
            <div
                class="bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-8 text-white">
                    <!-- Status -->
                    @if ($eligibleParticipants->count() > 0)
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></div>
                            <span class="text-sm font-semibold">Sistema Pronto para Sorteio</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-2 h-2 bg-red-300 rounded-full"></div>
                            <span class="text-sm font-semibold">Nenhum Participante Elegível</span>
                        </div>
                    @endif

                    <!-- Drum Animation -->
                    <div class="relative h-64 flex items-center justify-center my-8">
                        <div
                            class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-3xl border-2 border-white/20 flex items-center justify-center">
                            @if ($isDrawing)
                                <div class="animate-spin">
                                    <i class="bi bi-shuffle text-8xl opacity-50"></i>
                                </div>
                            @else
                                <i class="bi bi-trophy text-8xl opacity-30"></i>
                            @endif
                        </div>
                    </div>

                    <!-- Contador -->
                    <div class="text-center">
                        <div class="text-6xl font-bold mb-2">
                            {{ $eligibleParticipants->count() }}
                        </div>
                        <div class="text-lg opacity-90">
                            Participante{{ $eligibleParticipants->count() !== 1 ? 's' : '' }} Elegíve{{
                                $eligibleParticipants->count() !== 1 ? 'is' : 'l' }}
                        </div>
                    </div>

                    <!-- Botão de Sorteio -->
                    <div class="mt-8 flex justify-center">
                        @if ($eligibleParticipants->count() > 0)
                            <button wire:click="confirmDraw"
                                class="group relative px-12 py-4 bg-white text-purple-600 font-bold text-lg rounded-xl shadow-2xl transform transition-all hover:scale-105 hover:shadow-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="relative z-10 flex items-center gap-3">
                                    <i class="bi bi-play-circle text-2xl"></i>
                                    <span>Realizar Sorteio</span>
                                </span>
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-xl opacity-0 group-hover:opacity-20 transition-opacity">
                                </div>
                            </button>
                        @else
                            <div class="text-center p-4 bg-white/10 rounded-xl">
                                <p class="text-sm opacity-90">
                                    Não há participantes elegíveis no momento.
                                    <br>Verifique se há participantes ativos com pagamentos em dia.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Lista de Participantes Elegíveis -->
            @if ($eligibleParticipants->count() > 0)
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-people-fill text-purple-500"></i>
                            Participantes Elegíveis
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Todos os participantes abaixo podem ser sorteados
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($eligibleParticipants as $participant)
                                <div
                                    class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-700 transition-all">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl text-white font-bold">
                                        #{{ $participant->participation_number }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-slate-900 dark:text-white truncate">
                                            {{ $participant->client->name }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ number_format($participant->payment_percentage, 1) }}% pago
                                        </div>
                                    </div>
                                    <div>
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Coluna Direita - Histórico -->
        <div class="space-y-6">
            <!-- Stats -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Estatísticas</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Total de Sorteios</span>
                        <span
                            class="text-lg font-bold text-slate-900 dark:text-white">{{ $consortium->draws()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Contemplados</span>
                        <span
                            class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $consortium->contemplated_count }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Próximo Sorteio</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">#{{ $drawNumber }}</span>
                    </div>
                </div>
            </div>

            <!-- Histórico Recente -->
            @if ($recentDraws->count() > 0)
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-clock-history text-slate-500"></i>
                            Sorteios Recentes
                        </h3>
                    </div>

                    <div class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($recentDraws as $draw)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 font-bold text-sm">
                                        #{{ $draw->draw_number }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-slate-900 dark:text-white text-sm truncate">
                                            {{ $draw->winner?->client->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-lg {{ $draw->status_color }} bg-opacity-20">
                                        {{ $draw->status_label }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div x-show="$wire.showDrawModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div @click.away="$wire.cancelDraw()"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Header -->
            <div class="p-6 bg-gradient-to-r from-purple-600 to-pink-600">
                <div class="flex items-center gap-3 text-white">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Confirmar Sorteio</h3>
                        <p class="text-sm opacity-90">Sorteio #{{ $drawNumber }}</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <p class="text-slate-600 dark:text-slate-400 mb-4">
                    Tem certeza que deseja realizar o sorteio agora?
                </p>

                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Participantes elegíveis:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $eligibleParticipants->count()
                            }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Data/Hora:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{
                            \Carbon\Carbon::parse($drawDate)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4">
                    <i class="bi bi-info-circle"></i>
                    Esta ação não poderá ser desfeita.
                </p>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 p-6 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700">
                <button wire:click="cancelDraw"
                    class="flex-1 px-4 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    Cancelar
                </button>
                <button wire:click="performDraw" wire:loading.attr="disabled"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="performDraw">
                        <i class="bi bi-shuffle"></i> Sortear Agora
                    </span>
                    <span wire:loading wire:target="performDraw">
                        <i class="bi bi-arrow-repeat animate-spin"></i> Sorteando...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Vencedor -->
    <div x-show="$wire.showWinnerModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">

        <div @click.away="$wire.closeWinnerModal()"
            class="bg-gradient-to-br from-yellow-400 via-orange-400 to-red-500 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden relative">

            <!-- Confetti Animation -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                @for ($i = 0; $i < 20; $i++)
                    <div class="absolute w-2 h-2 bg-white/50 rounded-full animate-ping"
                        style="top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 1000) }}ms;">
                    </div>
                @endfor
            </div>

            <!-- Content -->
            <div class="relative p-8 text-center text-white">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full mb-4">
                        <i class="bi bi-trophy-fill text-6xl"></i>
                    </div>
                    <h2 class="text-4xl font-bold mb-2">🎉 Parabéns! 🎉</h2>
                    <p class="text-xl opacity-90">Temos um vencedor!</p>
                </div>

                @if ($selectedWinner)
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 mb-6">
                        <div class="text-5xl font-bold mb-2">
                            #{{ $selectedWinner->participation_number }}
                        </div>
                        <div class="text-2xl font-semibold">
                            {{ $selectedWinner->client->name }}
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="closeWinnerModal"
                            class="flex-1 px-6 py-3 bg-white/20 hover:bg-white/30 font-semibold rounded-xl transition-all">
                            Fechar
                        </button>
                        <button wire:click="openRedemptionModal"
                            class="flex-1 px-6 py-3 bg-white text-orange-600 hover:bg-slate-100 font-semibold rounded-xl transition-all shadow-lg">
                            Definir Resgate
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Tipo de Resgate -->
    <div x-show="$wire.showRedemptionModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">

        <div @click.away="$wire.closeRedemptionModal()"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">

            <!-- Header -->
            <div class="p-6 bg-gradient-to-r from-emerald-600 to-teal-600">
                <h3 class="text-xl font-bold text-white">Escolher Tipo de Resgate</h3>
                <p class="text-sm text-white/80 mt-1">Como o contemplado deseja resgatar?</p>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">
                <label
                    class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $redemptionType === 'cash' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" wire:model.live="redemptionType" value="cash" class="w-5 h-5">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-white">Dinheiro</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Receber o valor em dinheiro</div>
                    </div>
                    <i class="bi bi-cash-stack text-2xl text-emerald-600"></i>
                </label>

                <label
                    class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $redemptionType === 'products' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" wire:model.live="redemptionType" value="products" class="w-5 h-5">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-white">Produtos</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Escolher produtos do catálogo</div>
                    </div>
                    <i class="bi bi-box-seam text-2xl text-emerald-600"></i>
                </label>

                <label
                    class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-slate-300 {{ $redemptionType === 'pending' ? 'border-slate-300 bg-slate-50 dark:bg-slate-900' : 'border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" wire:model.live="redemptionType" value="pending" class="w-5 h-5">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-white">Decidir Depois</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Deixar pendente por enquanto</div>
                    </div>
                    <i class="bi bi-clock text-2xl text-slate-600"></i>
                </label>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 p-6 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700">
                <button wire:click="closeRedemptionModal"
                    class="flex-1 px-4 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    Cancelar
                </button>
                <button wire:click="saveRedemption"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all shadow-lg">
                    <i class="bi bi-check-circle"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
