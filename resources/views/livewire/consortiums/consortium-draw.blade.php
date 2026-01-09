<div class="w-full" x-data="{ spinning: false, showDraw: $wire.entangle('showDrawModal'), showWinner: $wire.entangle('showWinnerModal'), showRedemption: $wire.entangle('showRedemptionModal') }">

    <!-- Header Moderno igual às outras páginas -->
    <x-sales-header
        title="Sistema de Sorteio"
        :description="'🎲 Sorteio #' . $drawNumber . ' - ' . $consortium->name"
        :back-route="route('consortiums.show', $consortium)">
        <x-slot name="actions">
            <div class="flex items-center gap-3">
                <!-- Stats rápidas -->
                <div class="px-4 py-2 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-lg border border-purple-200 dark:border-purple-700/50 backdrop-blur-sm">
                    <div class="text-xs text-purple-600 dark:text-purple-400 font-medium">Próximo Sorteio</div>
                    <div class="text-lg font-black text-purple-700 dark:text-purple-300">#{{ $drawNumber }}</div>
                </div>
                <div class="px-4 py-2 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-lg border border-green-200 dark:border-green-700/50 backdrop-blur-sm">
                    <div class="text-xs text-green-600 dark:text-green-400 font-medium">Elegíveis</div>
                    <div class="text-lg font-black text-green-700 dark:text-green-300">{{ $eligibleParticipants->count() }}</div>
                </div>
            </div>
        </x-slot>
    </x-sales-header>

    <!-- Layout Otimizado em UMA TELA -->
    <div class="grid grid-cols-12 gap-6 px-6" :class="{ 'blur-[1px] pointer-events-none': showDraw || showRedemption }">

        <!-- COLUNA PRINCIPAL (8 colunas) - Área de Sorteio -->
        <div class="col-span-12 lg:col-span-8 space-y-6">

            <!-- Card Principal de Sorteio COMPACTO -->
            <div class="bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between mb-4">
                        @if ($eligibleParticipants->count() > 0)
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-green-400/30 backdrop-blur-sm rounded-lg border border-green-300/50">
                                <div class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></div>
                                <span class="text-sm font-bold text-white">✓ Pronto para Sortear</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-red-400/30 backdrop-blur-sm rounded-lg border border-red-300/50">
                                <div class="w-2 h-2 bg-red-300 rounded-full"></div>
                                <span class="text-sm font-bold text-white">✗ Sem Participantes</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-white/80 text-sm">
                            <i class="bi bi-calendar3"></i>
                            <span>{{ \Carbon\Carbon::parse($drawDate)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Drum Animation GRANDE com ANIMAÇÃO DE VENCEDOR -->
                    <div class="relative h-96 flex items-center justify-center mb-4">
                        <!-- Canvas para Confetes (aparece quando tem vencedor selecionado) -->
                        <canvas x-show="$wire.selectedWinner != null" id="confettiCanvasMain" class="absolute inset-0 w-full h-full pointer-events-none z-10 rounded-2xl"></canvas>

                        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-2xl border-2 border-white/20 flex items-center justify-center overflow-hidden"
                            x-data="{ confettiStarted: false }"
                            x-init="
                                $watch('$wire.selectedWinner', value => {
                                    if (value && !confettiStarted) {
                                        confettiStarted = true;

                                        const canvas = document.getElementById('confettiCanvasMain');
                                        if (!canvas) return;

                                        const ctx = canvas.getContext('2d');
                                        const rect = canvas.getBoundingClientRect();
                                        canvas.width = rect.width;
                                        canvas.height = rect.height;

                                        const confetti = [];
                                        const colors = [
                                            '#FFD700', '#FFA500', '#FF6347', '#FF1493', '#FF69B4',
                                            '#00CED1', '#1E90FF', '#9370DB', '#32CD32', '#FFD700',
                                            '#FF4500', '#FF6B6B', '#4ECDC4', '#45B7D1', '#F7DC6F'
                                        ];

                                        class Confetto {
                                            constructor(delay = 0) {
                                                this.delay = delay;
                                                this.x = Math.random() * canvas.width;
                                                this.y = -50 - Math.random() * 100;
                                                this.size = Math.random() * 8 + 4;
                                                this.speedY = Math.random() * 4 + 2;
                                                this.speedX = Math.random() * 4 - 2;
                                                this.rotation = Math.random() * 360;
                                                this.rotationSpeed = Math.random() * 15 - 7.5;
                                                this.color = colors[Math.floor(Math.random() * colors.length)];
                                                this.shape = Math.floor(Math.random() * 4);
                                                this.opacity = 1;
                                                this.life = 0;
                                            }

                                            update() {
                                                if (this.delay > 0) {
                                                    this.delay--;
                                                    return;
                                                }

                                                this.life++;
                                                this.y += this.speedY;
                                                this.x += this.speedX + Math.sin(this.life * 0.05) * 1.5;
                                                this.rotation += this.rotationSpeed;
                                                this.speedY += 0.06;

                                                if (this.y > canvas.height + 50) {
                                                    this.y = -50;
                                                    this.x = Math.random() * canvas.width;
                                                    this.speedY = Math.random() * 4 + 2;
                                                    this.speedX = Math.random() * 4 - 2;
                                                    this.life = 0;
                                                }
                                            }

                                            draw() {
                                                if (this.delay > 0) return;

                                                ctx.save();
                                                ctx.translate(this.x, this.y);
                                                ctx.rotate(this.rotation * Math.PI / 180);
                                                ctx.globalAlpha = this.opacity;

                                                ctx.shadowColor = this.color;
                                                ctx.shadowBlur = 8;
                                                ctx.fillStyle = this.color;

                                                if (this.shape === 0) {
                                                    ctx.beginPath();
                                                    ctx.arc(0, 0, this.size, 0, Math.PI * 2);
                                                    ctx.fill();
                                                } else if (this.shape === 1) {
                                                    ctx.fillRect(-this.size/2, -this.size/2, this.size, this.size);
                                                } else if (this.shape === 2) {
                                                    ctx.beginPath();
                                                    ctx.moveTo(0, -this.size);
                                                    ctx.lineTo(this.size, this.size);
                                                    ctx.lineTo(-this.size, this.size);
                                                    ctx.closePath();
                                                    ctx.fill();
                                                } else {
                                                    ctx.beginPath();
                                                    for (let i = 0; i < 5; i++) {
                                                        const angle = (i * 4 * Math.PI) / 5 - Math.PI / 2;
                                                        const x = Math.cos(angle) * this.size;
                                                        const y = Math.sin(angle) * this.size;
                                                        if (i === 0) ctx.moveTo(x, y);
                                                        else ctx.lineTo(x, y);
                                                    }
                                                    ctx.closePath();
                                                    ctx.fill();
                                                }

                                                ctx.restore();
                                            }
                                        }

                                        for (let i = 0; i < 150; i++) {
                                            confetti.push(new Confetto(Math.random() * 20));
                                        }

                                        let animationRunning = true;
                                        function animate() {
                                            if (animationRunning && $wire.selectedWinner) {
                                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                                                confetti.forEach(c => {
                                                    c.update();
                                                    c.draw();
                                                });
                                                requestAnimationFrame(animate);
                                            }
                                        }

                                        animate();

                                        // Som de celebração
                                        try {
                                            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                                            const notes = [523.25, 659.25, 783.99, 1046.50];
                                            notes.forEach((freq, i) => {
                                                setTimeout(() => {
                                                    const oscillator = audioContext.createOscillator();
                                                    const gainNode = audioContext.createGain();
                                                    oscillator.connect(gainNode);
                                                    gainNode.connect(audioContext.destination);
                                                    oscillator.frequency.value = freq;
                                                    oscillator.type = 'sine';
                                                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                                                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                                                    oscillator.start(audioContext.currentTime);
                                                    oscillator.stop(audioContext.currentTime + 0.5);
                                                }, i * 150);
                                            });
                                        } catch (e) {
                                            console.log('Audio not supported');
                                        }
                                    }
                                });
                            ">

                            <!-- Estado: Sorteando com COUNTDOWN e SLOT MACHINE -->
                            @if ($isDrawing && !$selectedWinner)
                                <div class="text-center relative z-20 w-full px-8"
                                    x-data="{
                                        countdown: 3,
                                        showCountdown: true,
                                        showSlotMachine: false,
                                        drawExecuted: false,
                                        participants: @js($eligibleParticipants->pluck('client.name')->toArray()),
                                        init() {
                                            // Countdown de 3 segundos
                                            const countdownInterval = setInterval(() => {
                                                this.countdown--;
                                                if (this.countdown <= 0) {
                                                    clearInterval(countdownInterval);
                                                    this.showCountdown = false;
                                                    this.showSlotMachine = true;

                                                    // Após 3 segundos de slot machine (total 6s), executa o sorteio
                                                    setTimeout(() => {
                                                        if (!this.drawExecuted) {
                                                            this.drawExecuted = true;
                                                            $wire.executeDraw();
                                                        }
                                                    }, 3000);
                                                }
                                            }, 1000);
                                        }
                                    }">

                                    <!-- Countdown 3, 2, 1... -->
                                    <div x-show="showCountdown" x-transition class="py-20">
                                        <div class="text-[150px] font-black text-white drop-shadow-2xl"
                                            x-text="countdown"
                                            style="animation: bounce 0.5s ease-in-out, pulse 0.5s ease-in-out infinite;"></div>
                                        <div class="text-3xl font-bold text-white/90 mt-6 uppercase tracking-widest animate-pulse">
                                            ⏱️ Preparando Sorteio...
                                        </div>
                                    </div>

                                    <!-- Slot Machine Animation -->
                                    <div x-show="showSlotMachine" x-transition wire:poll.50ms class="py-6">
                                        <!-- Efeito de Slot Machine Cassino -->
                                        <div class="relative">
                                            <!-- Brilho de fundo -->
                                            <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/30 via-red-500/40 to-orange-400/30 rounded-3xl animate-pulse blur-2xl"></div>

                                            <div class="relative bg-gradient-to-br from-red-600/40 via-orange-500/30 to-yellow-400/40 backdrop-blur-md rounded-3xl border-4 border-yellow-400/60 p-10 shadow-2xl">
                                                <div class="mb-6">
                                                    <div class="text-5xl font-black text-yellow-300 uppercase tracking-wider drop-shadow-2xl animate-pulse">
                                                        🎰 GIRANDO A MÁQUINA...
                                                    </div>
                                                </div>

                                                <!-- Display do Nome com Efeito Slot Rolando -->
                                                <div class="relative h-40 overflow-hidden rounded-2xl bg-black/50 border-4 border-yellow-400 shadow-2xl" style="box-shadow: inset 0 0 50px rgba(255,215,0,0.5), 0 0 30px rgba(255,215,0,0.6);">
                                                    <!-- Fundo animado tipo LED -->
                                                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-yellow-400/10 to-transparent animate-pulse"></div>

                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        @if($eligibleParticipants->count() > 0)
                                                            <div class="text-6xl font-black text-yellow-300 drop-shadow-2xl"
                                                                style="text-shadow: 0 0 40px rgba(255,215,0,1), 0 0 80px rgba(255,165,0,0.8), 0 0 120px rgba(255,100,0,0.6); animation: pulse 0.15s ease-in-out infinite, wiggle 0.1s ease-in-out infinite;">
                                                                {{ $eligibleParticipants->random()->client->name }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Linhas de guia do slot (topo e baixo) -->
                                                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-yellow-400 to-transparent animate-pulse"></div>
                                                    <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-yellow-400 to-transparent animate-pulse"></div>

                                                    <!-- Bordas laterais brilhantes -->
                                                    <div class="absolute top-0 left-0 bottom-0 w-2 bg-gradient-to-b from-transparent via-yellow-400 to-transparent animate-pulse"></div>
                                                    <div class="absolute top-0 right-0 bottom-0 w-2 bg-gradient-to-b from-transparent via-yellow-400 to-transparent animate-pulse"></div>
                                                </div>

                                                <!-- Indicadores tipo cassino -->
                                                <div class="mt-8 flex justify-center gap-4">
                                                    <div class="w-5 h-5 bg-yellow-400 rounded-full shadow-lg shadow-yellow-400/50 animate-[bounce_0.5s_ease-in-out_infinite]"></div>
                                                    <div class="w-5 h-5 bg-red-500 rounded-full shadow-lg shadow-red-500/50 animate-[bounce_0.5s_ease-in-out_infinite_0.15s]"></div>
                                                    <div class="w-5 h-5 bg-orange-400 rounded-full shadow-lg shadow-orange-400/50 animate-[bounce_0.5s_ease-in-out_infinite_0.3s]"></div>
                                                    <div class="w-5 h-5 bg-yellow-300 rounded-full shadow-lg shadow-yellow-300/50 animate-[bounce_0.5s_ease-in-out_infinite_0.45s]"></div>
                                                </div>

                                                <!-- Texto adicional -->
                                                <div class="mt-6 text-2xl font-bold text-yellow-200/90 animate-pulse">
                                                    💰 Aguarde o resultado... 💰
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <!-- Estado: Vencedor -->
                            @elseif ($selectedWinner)
                                <div class="text-center relative z-20 w-full px-8 animate-[fadeIn_0.5s_ease-out]">

                                    <div class="mb-6">
                                        <div class="text-6xl font-black mb-4 text-white drop-shadow-2xl animate-bounce">
                                            🎉 VENCEDOR! 🎉
                                        </div>
                                    </div>

                                    <div class="bg-white/30 backdrop-blur-xl rounded-2xl p-8 border-4 border-white/50 shadow-2xl max-w-2xl mx-auto">
                                        <div class="flex items-center justify-center w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl text-white font-black text-4xl shadow-2xl animate-pulse">
                                            #{{ $selectedWinner->participation_number }}
                                        </div>
                                        <div class="text-5xl font-black text-white mb-3 drop-shadow-2xl">
                                            {{ $selectedWinner->client->name }}
                                        </div>
                                        <div class="text-lg text-white/90 font-bold mt-4">
                                            🏆 Contemplado no Sorteio #{{ $drawNumber }}
                                        </div>
                                    </div>
                                </div>

                            <!-- Estado: Pronto para sortear -->
                            @else
                                <div class="text-center py-16 px-8">
                                    <div class="mb-8">
                                        <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full shadow-2xl animate-bounce">
                                            <i class="bi bi-trophy-fill text-6xl text-white"></i>
                                        </div>
                                    </div>
                                    <div class="text-4xl font-black text-white mb-4 drop-shadow-xl">
                                        🎯 Sorteio Pronto!
                                    </div>
                                    <div class="text-xl text-white/80 mb-8">
                                        Clique no botão abaixo para realizar o sorteio
                                    </div>
                                    <div class="flex justify-center gap-4">
                                        <div class="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-xl border-2 border-white/40 text-white">
                                            <span class="text-3xl font-black">{{ $eligibleParticipants->count() }}</span>
                                            <span class="text-sm ml-2">Participantes Elegíveis</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contador e Botão HORIZONTAL -->
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 text-white">
                            <div class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl border border-white/30">
                                <span class="text-3xl font-black">{{ $eligibleParticipants->count() }}</span>
                            </div>
                            <div>
                                <div class="text-xs opacity-80 uppercase tracking-wider">Participantes</div>
                                <div class="text-lg font-bold">Elegíve{{ $eligibleParticipants->count() !== 1 ? 'is' : 'l' }}</div>
                            </div>
                        </div>

                        <!-- Botão de Sorteio INLINE -->
                        <div x-show="!(showDraw || showRedemption)">
                            @if ($eligibleParticipants->count() > 0 && !$selectedWinner)
                                <button wire:click="confirmDraw"
                                    class="group relative px-8 py-3 bg-white text-purple-600 font-black text-base rounded-xl shadow-xl transform transition-all hover:scale-105 hover:shadow-2xl">
                                    <span class="relative z-10 flex items-center gap-2">
                                        <i class="bi bi-play-circle-fill text-xl"></i>
                                        <span>Realizar Sorteio</span>
                                    </span>
                                </button>
                            @elseif($selectedWinner)
                                <!-- Botões de Ação do Vencedor -->
                                <div class="flex gap-3">
                                    <button wire:click="resetDraw"
                                        class="px-6 py-3 bg-red-500/80 hover:bg-red-600 backdrop-blur-xl text-white font-bold rounded-xl transition-all border-2 border-red-300/50 hover:scale-105 shadow-lg">
                                        <i class="bi bi-arrow-repeat mr-2"></i> Sortear Novamente
                                    </button>
                                    <button wire:click="openRedemptionModal"
                                        class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-black rounded-xl transition-all shadow-xl border-2 border-white hover:scale-105">
                                        <i class="bi bi-check-circle-fill mr-2"></i> Confirmar Sorteio
                                    </button>
                                </div>
                            @else
                                <div class="text-center px-4 py-3 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
                                    <p class="text-sm text-white/90">
                                        ⚠️ Sem participantes elegíveis
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Participantes Elegíveis COMPACTA -->
            @if ($eligibleParticipants->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="bi bi-people-fill text-purple-500"></i>
                                Participantes Elegíveis ({{ $eligibleParticipants->count() }})
                            </h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                <i class="bi bi-check-circle-fill text-green-500"></i> Todos aptos ao sorteio
                            </span>
                        </div>
                    </div>

                    <div class="p-4 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-purple-300 scrollbar-track-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($eligibleParticipants as $participant)
                                <div class="flex items-center gap-3 p-3 bg-gradient-to-br from-slate-50 to-purple-50/30 dark:from-slate-900 dark:to-purple-900/10 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-600 transition-all hover:shadow-md group">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg text-white font-black text-sm shadow-md group-hover:scale-110 transition-transform">
                                        #{{ $participant->participation_number }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                            {{ $participant->client->name }}
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                            <i class="bi bi-wallet2"></i>
                                            <span>{{ number_format($participant->payment_percentage, 1) }}% pago</span>
                                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                            <i class="bi bi-ticket-perforated"></i>
                                            <span>Cota {{ $participant->participation_number }}</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-check-circle-fill text-green-500 text-lg group-hover:scale-125 transition-transform"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- COLUNA LATERAL (4 colunas) - Estatísticas e Histórico -->
        <div class="col-span-12 lg:col-span-4 space-y-6">

            <!-- Cards de Estatísticas MODERNOS -->
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                <!-- Total de Sorteios -->
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg">
                                <i class="bi bi-trophy-fill text-2xl text-white"></i>
                            </div>
                            <span class="text-4xl font-black text-white">{{ $consortium->draws()->count() }}</span>
                        </div>
                        <div class="text-white/90 text-sm font-semibold">Total de Sorteios</div>
                        <div class="text-white/70 text-xs mt-1">Realizados até hoje</div>
                    </div>
                </div>

                <!-- Contemplados -->
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg">
                                <i class="bi bi-star-fill text-2xl text-white"></i>
                            </div>
                            <span class="text-4xl font-black text-white">{{ $consortium->contemplated_count }}</span>
                        </div>
                        <div class="text-white/90 text-sm font-semibold">Contemplados</div>
                        <div class="text-white/70 text-xs mt-1">Vencedores totais</div>
                    </div>
                </div>

                <!-- Participantes Totais -->
                <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg">
                                <i class="bi bi-people-fill text-2xl text-white"></i>
                            </div>
                            <span class="text-4xl font-black text-white">{{ $consortium->participants()->count() }}</span>
                        </div>
                        <div class="text-white/90 text-sm font-semibold">Participantes</div>
                        <div class="text-white/70 text-xs mt-1">Total no consórcio</div>
                    </div>
                </div>

                <!-- Taxa de Conclusão -->
                <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg">
                                <i class="bi bi-speedometer2 text-2xl text-white"></i>
                            </div>
                            <span class="text-4xl font-black text-white">
                                {{ $consortium->participants()->count() > 0 ? round(($consortium->contemplated_count / $consortium->participants()->count()) * 100) : 0 }}%
                            </span>
                        </div>
                        <div class="text-white/90 text-sm font-semibold">Conclusão</div>
                        <div class="text-white/70 text-xs mt-1">Taxa contemplados</div>
                    </div>
                </div>
            </div>

            <!-- Histórico Recente COMPACTO -->
            @if ($recentDraws->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-slate-50 to-blue-50 dark:from-slate-900 dark:to-blue-900/20 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-clock-history text-slate-500"></i>
                            Sorteios Recentes
                        </h3>
                    </div>

                    <div class="max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-gray-100">
                        @foreach ($recentDraws as $draw)
                            <div class="px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/10 dark:hover:to-pink-900/10 transition-all border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-lg text-purple-600 dark:text-purple-400 font-black text-sm shadow-sm">
                                        #{{ $draw->draw_number }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-trophy-fill text-yellow-500 text-xs"></i>
                                            <div class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                                {{ $draw->winner?->client->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            <i class="bi bi-calendar3"></i>
                                            <span>{{ \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y') }}</span>
                                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                            <i class="bi bi-clock"></i>
                                            <span>{{ \Carbon\Carbon::parse($draw->draw_date)->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-bold rounded-lg {{ $draw->status_color }} bg-opacity-20 shrink-0">
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

    <!-- Modal de Vencedor REMOVIDO - Animação agora aparece in-place na área de sorteio -->

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

    <!-- Estilos das Animações -->
    <style>
        @keyframes wiggle {
            0%, 100% { transform: rotate(-8deg) scale(1); }
            50% { transform: rotate(8deg) scale(1.05); }
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.3) rotate(-15deg);
            }
            50% {
                transform: scale(1.15) rotate(5deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            0% {
                opacity: 0;
                transform: translateY(100px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes twinkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(0.8) rotate(0deg);
            }
            50% {
                opacity: 1;
                transform: scale(1.2) rotate(180deg);
            }
        }
    </style>
</div>
