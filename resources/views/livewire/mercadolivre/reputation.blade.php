@push('styles')
<style>
.reputation-header-bg{background:linear-gradient(135deg,rgba(255,255,255,.82) 0%,rgba(254,243,199,.90) 40%,rgba(251,191,36,.50) 100%);}
.rep-bar-bg{background:#f1f5f9;}
.dark .rep-bar-bg{background:#1e293b;}
.skel-r{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel-r 1.4s infinite;}
@keyframes skel-r{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
@endpush

<div class="reputation-page min-h-screen"
     x-data="{ tipsOpen: @entangle('tipsOpen') }">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="reputation-header-bg border-b border-amber-200/60 dark:border-amber-700/30 px-4 sm:px-6 py-5 mb-5
                dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
        <div class="max-w-5xl mx-auto">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-amber-600 transition-colors">Início</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('mercadolivre.products') }}" class="hover:text-amber-600 transition-colors">Mercado Livre</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-amber-700 dark:text-amber-400 font-semibold">Reputação</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative w-14 h-14 flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500
                                    flex items-center justify-center shadow-lg shadow-amber-400/40">
                            <i class="bi bi-star-fill text-2xl text-white"></i>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-lg bg-gradient-to-br from-yellow-300 to-amber-500
                                    flex items-center justify-center shadow ring-2 ring-white dark:ring-slate-900">
                            <span class="text-white font-black text-[9px] leading-none">ML</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight
                                   bg-gradient-to-r from-slate-800 via-amber-700 to-yellow-700
                                   dark:from-white dark:via-amber-400 dark:to-yellow-300
                                   bg-clip-text text-transparent leading-tight">
                            Reputação & Métricas
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Desempenho do vendedor no Mercado Livre</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="loadData" title="Atualizar"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl
                                   bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-arrow-clockwise" wire:loading.class="animate-spin" wire:target="loadData"></i>
                    </button>
                    <button @click="tipsOpen = true"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl
                                   bg-white/80 dark:bg-slate-800 border border-amber-200 dark:border-slate-600
                                   text-sm font-semibold text-slate-600 dark:text-slate-300
                                   hover:bg-amber-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                        <i class="bi bi-lightbulb-fill text-amber-500"></i> Dicas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 pb-10">

        @if($errorMessage)
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 mb-6">
                <i class="bi bi-exclamation-circle-fill text-red-500 text-xl flex-shrink-0"></i>
                <div class="flex-1 text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</div>
                <button wire:click="loadData" class="text-sm font-bold text-red-600 hover:text-red-800 dark:text-red-400">Tentar novamente</button>
            </div>
        @endif

        @if($loading)
            {{-- Skeletons --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @for($i=0;$i<4;$i++)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
                        <div class="skel-r h-3 w-20 rounded mb-3"></div>
                        <div class="skel-r h-8 w-16 rounded-lg mb-2"></div>
                        <div class="skel-r h-2 w-full rounded"></div>
                    </div>
                @endfor
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    @for($i=0;$i<5;$i++)
                        <div class="skel-r h-4 rounded-lg"></div>
                    @endfor
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    @for($i=0;$i<5;$i++)
                        <div class="skel-r h-4 rounded-lg"></div>
                    @endfor
                </div>
            </div>
        @else
            @php
                $level      = $this->getLevelBadge($rep['level_id'] ?? null);
                $tx         = $rep['transactions'] ?? [];
                $metrics    = $rep['metrics'] ?? [];
                $total      = $tx['total'] ?? 0;
                $completed  = $tx['completed'] ?? 0;
                $cancelled  = $tx['cancelled'] ?? 0;
                $cancelRate = $metrics['cancellations']['rate'] ?? 0;
                $delayRate  = $metrics['delayed_handling_time']['rate'] ?? 0;
                $claimRate  = $metrics['claims']['rate'] ?? 0;
                $powerSeller= $rep['power_seller_status'] ?? null;
                $nickname   = $seller['nickname'] ?? 'Vendedor';
                $email      = $seller['email'] ?? '';
                $registDate = isset($seller['registration_date']) ? \Carbon\Carbon::parse($seller['registration_date'])->format('d/m/Y') : '';
                // Feedback
                $positive   = $feedback['total_positive'] ?? 0;
                $negative   = $feedback['total_negative'] ?? 0;
                $neutral    = $feedback['total_neutral'] ?? 0;
                $totalFb    = $positive + $negative + $neutral;
                $posRate    = $totalFb > 0 ? round($positive / $totalFb * 100, 1) : 0;
            @endphp

            {{-- Perfil header --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm
                        flex flex-col sm:flex-row items-start sm:items-center gap-4 px-6 py-5 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500
                             flex items-center justify-center shadow text-white font-extrabold text-2xl flex-shrink-0">
                    {{ strtoupper(substr($nickname, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-slate-900 dark:text-white text-lg truncate">{{ $nickname }}</p>
                    @if($email)<p class="text-xs text-slate-500 truncate">{{ $email }}</p>@endif
                    @if($registDate)<p class="text-xs text-slate-400 mt-0.5">Membro desde {{ $registDate }}</p>@endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($level)
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $level['bg'] }}">
                            {{ $level['label'] }}
                        </span>
                    @endif
                    @if($powerSeller)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                            <i class="bi bi-trophy-fill mr-1"></i>Power Seller
                        </span>
                    @endif
                </div>
            </div>

            {{-- Stats cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @php $cards = [
                    ['icon'=>'bi-bag-check-fill','color'=>'amber','label'=>'Total de vendas','value'=> number_format($total)],
                    ['icon'=>'bi-check-circle-fill','color'=>'emerald','label'=>'Concluídas','value'=> number_format($completed)],
                    ['icon'=>'bi-x-circle-fill','color'=>'red','label'=>'Canceladas','value'=> number_format($cancelled)],
                    ['icon'=>'bi-star-fill','color'=>'yellow','label'=>'Avaliação positiva','value'=> $posRate . '%'],
                ]; @endphp
                @foreach($cards as $c)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm
                                hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <div class="w-9 h-9 rounded-xl bg-{{ $c['color'] }}-100 dark:bg-{{ $c['color'] }}-900/30
                                    flex items-center justify-center mb-3">
                            <i class="bi {{ $c['icon'] }} text-{{ $c['color'] }}-600 dark:text-{{ $c['color'] }}-400"></i>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none mb-1">{{ $c['value'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $c['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Métricas de qualidade --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm mb-5 flex items-center gap-2">
                        <i class="bi bi-speedometer2 text-amber-500"></i> Métricas de Qualidade
                    </h3>
                    @php
                        $qualMetrics = [
                            ['label'=>'Taxa de cancelamento', 'rate'=>$cancelRate, 'icon'=>'bi-x-circle-fill', 'danger'=>5],
                            ['label'=>'Atrasos no envio',     'rate'=>$delayRate,  'icon'=>'bi-clock-fill',    'danger'=>10],
                            ['label'=>'Taxa de reclamações',  'rate'=>$claimRate,  'icon'=>'bi-exclamation-triangle-fill', 'danger'=>5],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach($qualMetrics as $m)
                            @php
                                $pct = min(round($m['rate'] * 100, 1), 100);
                                $overLimit = $m['rate'] * 100 > $m['danger'];
                                $barColor  = $overLimit ? 'bg-red-500' : 'bg-emerald-500';
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <i class="bi {{ $m['icon'] }} text-xs {{ $overLimit ? 'text-red-500' : 'text-emerald-500' }}"></i>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $m['label'] }}</span>
                                    </div>
                                    <span class="text-xs font-bold {{ $overLimit ? 'text-red-600':'text-emerald-600' }}">{{ $pct }}%</span>
                                </div>
                                <div class="h-2 rep-bar-bg rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full {{ $barColor }} transition-all duration-700"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Limite seguro: {{ $m['danger'] }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Feedbacks --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm mb-5 flex items-center gap-2">
                        <i class="bi bi-chat-square-heart-fill text-amber-500"></i> Avaliações de Compradores
                    </h3>
                    <div class="space-y-4">
                        @php
                            $fbItems = [
                                ['label'=>'Positivas', 'value'=>$positive, 'color'=>'emerald', 'icon'=>'bi-hand-thumbs-up-fill'],
                                ['label'=>'Negativas', 'value'=>$negative, 'color'=>'red',     'icon'=>'bi-hand-thumbs-down-fill'],
                                ['label'=>'Neutras',   'value'=>$neutral,  'color'=>'slate',   'icon'=>'bi-dash-circle-fill'],
                            ];
                        @endphp
                        @foreach($fbItems as $fb)
                            @php $pct = $totalFb > 0 ? round($fb['value'] / $totalFb * 100, 1) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <i class="bi {{ $fb['icon'] }} text-xs text-{{ $fb['color'] }}-500"></i>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $fb['label'] }}</span>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ number_format($fb['value']) }} ({{ $pct }}%)</span>
                                </div>
                                <div class="h-2 rep-bar-bg rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full bg-{{ $fb['color'] }}-500 transition-all duration-700"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-2 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs text-slate-500">Total de avaliações</span>
                            <span class="text-sm font-extrabold text-slate-800 dark:text-white">{{ number_format($totalFb) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados completos do seller --}}
            @if(!empty($seller))
            <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                <h3 class="font-extrabold text-slate-800 dark:text-white text-sm mb-4 flex items-center gap-2">
                    <i class="bi bi-person-fill-gear text-amber-500"></i> Informações da Conta
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-xs">
                    @php $infoItems = [
                        ['label'=>'ID do vendedor', 'value'=> $seller['id'] ?? '-'],
                        ['label'=>'Tipo de conta',  'value'=> ucfirst($seller['account_type'] ?? '-')],
                        ['label'=>'País',            'value'=> $seller['country_id'] ?? '-'],
                        ['label'=>'Status',          'value'=> ucfirst($seller['status'] ?? '-')],
                    ]; @endphp
                    @foreach($infoItems as $info)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <p class="text-slate-400 mb-0.5">{{ $info['label'] }}</p>
                            <p class="font-bold text-slate-800 dark:text-white truncate">{{ $info['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        @endif
    </div>

    {{-- ============================================================
         MODAL – DICAS
    ============================================================ --}}
    <div x-show="tipsOpen"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="tipsOpen = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
         style="display:none">
        <div x-show="tipsOpen"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md
                    border border-amber-100 dark:border-amber-700/30 overflow-hidden flex flex-col max-h-[80vh]"
             style="display:none">
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-yellow-400 to-amber-500">
                <div class="flex items-center gap-3">
                    <i class="bi bi-lightbulb-fill text-white text-xl"></i>
                    <h3 class="text-base font-extrabold text-white">Dicas de Reputação</h3>
                </div>
                <button @click="tipsOpen = false"
                        class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 transition-all flex items-center justify-center text-white">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-5 space-y-3 flex-1">
                @php $tips = [
                    ['icon'=>'bi-truck-fill',       'color'=>'amber',   'title'=>'Envie no prazo',        'desc'=>'Atrasos no envio afetam diretamente a nota de velocidade e reduzem o nível de reputação.'],
                    ['icon'=>'bi-chat-dots-fill',    'color'=>'blue',    'title'=>'Responda perguntas',    'desc'=>'Responder perguntas rapidamente aumenta a taxa de conversão e demonstra profissionalismo.'],
                    ['icon'=>'bi-x-circle-fill',     'color'=>'red',     'title'=>'Evite cancelamentos',   'desc'=>'Cancele somente quando absolutamente necessário. Cada cancelamento piora sua métrica.'],
                    ['icon'=>'bi-star-fill',         'color'=>'yellow',  'title'=>'Solicite avaliações',   'desc'=>'Após a entrega, clientes satisfeitos muitas vezes esquecem de avaliar. O ML notifica automaticamente.'],
                    ['icon'=>'bi-graph-up-arrow',    'color'=>'emerald', 'title'=>'Nível "5 Verde"',       'desc'=>'É o maior nível. Exige baixa taxa de reclamações, cancelamentos e atrasos por 3 meses consecutivos.'],
                ]; @endphp
                @foreach($tips as $t)
                    <div class="flex gap-3 p-3.5 rounded-xl bg-{{ $t['color'] }}-50 dark:bg-{{ $t['color'] }}-900/10 border border-{{ $t['color'] }}-100 dark:border-{{ $t['color'] }}-800/30">
                        <div class="w-8 h-8 rounded-lg bg-{{ $t['color'] }}-100 dark:bg-{{ $t['color'] }}-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="bi {{ $t['icon'] }} text-{{ $t['color'] }}-600 dark:text-{{ $t['color'] }}-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white mb-0.5">{{ $t['title'] }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $t['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-toast-notifications />
</div>
