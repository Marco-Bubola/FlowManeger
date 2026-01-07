<div class="w-full max-w-7xl mx-auto px-4 py-8">
    <!-- Header Moderno com Glassmorphism -->
    <div class="w-full mb-8">
        <div class="relative bg-gradient-to-r from-white/80 via-emerald-50/90 to-teal-50/80 dark:from-slate-800/90 dark:via-emerald-900/30 dark:to-teal-900/30 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl">
            <!-- Decorações de fundo -->
            <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
                <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-teal-400/20 to-cyan-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-cyan-400/10 via-emerald-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>
            </div>

            <!-- Conteúdo do header -->
            <div class="relative px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('clients.dashboard', $this->client) }}"
                            class="flex items-center justify-center w-12 h-12 bg-white/80 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-700 rounded-2xl border border-white/40 dark:border-slate-600/50 shadow-lg hover:shadow-xl transition-all duration-300 backdrop-blur-sm">
                            <i class="bi bi-arrow-left text-xl text-emerald-600 dark:text-emerald-400"></i>
                        </a>
                        <div>
                            <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 dark:from-emerald-400 dark:via-teal-400 dark:to-cyan-400 bg-clip-text text-transparent drop-shadow-sm">
                                Consórcios de {{ $this->client->name }}
                            </h1>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 font-medium">
                                <i class="bi bi-folder-open mr-1"></i>Visualize todos os consórcios e pagamentos do cliente
                            </p>
                        </div>
                    </div>

                    <!-- Botão de Exportação -->
                    <div class="flex items-center gap-3">
                        <button wire:click="$dispatch('openExportModal', { clientId: {{ $this->client->id }} })"
                            class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <i class="bi bi-file-earmark-arrow-down text-lg"></i>
                            <span>Exportar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
        <!-- Total Consórcios -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total Consórcios</span>
                <i class="bi bi-collection text-2xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $this->participations->count() }}</div>
        </div>

        <!-- Ativos -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Ativos</span>
                <i class="bi bi-check-circle text-2xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ $this->participations->where('status', 'active')->count() }}</div>
        </div>

        <!-- Contemplados -->
        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl p-6 border border-yellow-200 dark:border-yellow-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-yellow-700 dark:text-yellow-400">Contemplados</span>
                <i class="bi bi-trophy text-2xl text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div class="text-3xl font-bold text-yellow-900 dark:text-yellow-100">{{ $this->participations->where('is_contemplated', true)->count() }}</div>
        </div>

        <!-- Total Pago -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Total Pago</span>
                <i class="bi bi-wallet2 text-2xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                R$ {{ number_format($this->participations->sum('total_paid'), 2, ',', '.') }}
            </div>
        </div>

        @php
            $allPayments = $this->participations->flatMap(fn($p) => $p->payments);
            $totalParcelas = $allPayments->count();
            $parcelasPagas = $allPayments->where('status', 'paid')->count();
            $parcelasNaoPagas = $allPayments->whereIn('status', ['pending', 'overdue'])->count();
        @endphp

        <!-- Parcelas Pagas -->
        <div class="bg-gradient-to-br from-green-50 to-lime-50 dark:from-green-900/20 dark:to-lime-900/20 rounded-xl p-6 border border-green-200 dark:border-green-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-green-700 dark:text-green-400">Parcelas Pagas</span>
                <i class="bi bi-check2-circle text-2xl text-green-600 dark:text-green-400"></i>
            </div>
            <div class="text-3xl font-bold text-green-900 dark:text-green-100">{{ $parcelasPagas }}</div>
            <div class="text-xs text-green-700 dark:text-green-400 mt-1">de {{ $totalParcelas }} parcelas</div>
        </div>

        <!-- Parcelas Não Pagas -->
        <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl p-6 border border-red-200 dark:border-red-700 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-red-700 dark:text-red-400">Não Pagas</span>
                <i class="bi bi-x-circle text-2xl text-red-600 dark:text-red-400"></i>
            </div>
            <div class="text-3xl font-bold text-red-900 dark:text-red-100">{{ $parcelasNaoPagas }}</div>
            <div class="text-xs text-red-700 dark:text-red-400 mt-1">pendentes/vencidas</div>
        </div>
    </div>

    <!-- Lista de Consórcios -->
    <div class="space-y-6">
        @forelse($this->participations as $participation)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Cabeçalho do Consórcio -->
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-b border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold text-xl">
                                {{ $participation->participation_number }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ $participation->consortium->name }}
                                </h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    Entrada: {{ \Carbon\Carbon::parse($participation->entry_date)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($participation->is_contemplated)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">
                                    <i class="bi bi-star-fill"></i>
                                    Contemplado
                                </span>
                            @endif
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold {{ $participation->status_color }}">
                                {{ $participation->status_label }}
                            </span>
                            <a href="{{ route('consortiums.show', $participation->consortium) }}"
                               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                                Ver Consórcio
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Informações do Consórcio -->
                        <div>
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <i class="bi bi-info-circle text-emerald-600"></i>
                                Informações
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                                    <span class="text-slate-600 dark:text-slate-400">Valor Mensal:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        R$ {{ number_format($participation->consortium->monthly_value, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                                    <span class="text-slate-600 dark:text-slate-400">Duração:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $participation->consortium->duration_months }} meses
                                    </span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                                    <span class="text-slate-600 dark:text-slate-400">Total Pago:</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        R$ {{ number_format($participation->total_paid, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-slate-600 dark:text-slate-400">Progresso:</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $participation->payments->where('status', 'paid')->count() }} / {{ $participation->consortium->duration_months }}
                                    </span>
                                </div>
                            </div>

                            <!-- Barra de Progresso -->
                            <div class="mt-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-600 dark:text-slate-400">Progresso de Pagamentos</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($participation->payment_percentage, 1) }}%</span>
                                </div>
                                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all"
                                         style="width: {{ $participation->payment_percentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Contemplação (se contemplado) -->
                        @if($participation->is_contemplated && $participation->contemplation)
                            <div>
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                    <i class="bi bi-trophy text-yellow-600"></i>
                                    Contemplação
                                </h4>
                                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl p-4 border border-yellow-200 dark:border-yellow-700">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-yellow-700 dark:text-yellow-300">Tipo:</span>
                                            <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                                                {{ ucfirst($participation->contemplation->contemplation_type) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-yellow-700 dark:text-yellow-300">Data:</span>
                                            <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                                                {{ \Carbon\Carbon::parse($participation->contemplation->contemplation_date)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-yellow-700 dark:text-yellow-300">Resgate:</span>
                                            <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                                                {{ ucfirst($participation->contemplation->redemption_type) }}
                                            </span>
                                        </div>
                                        @if($participation->contemplation->redemption_type === 'products' && $participation->contemplation->products)
                                            <div class="mt-3 pt-3 border-t border-yellow-300 dark:border-yellow-700">
                                                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Produtos Escolhidos:</p>
                                                <ul class="space-y-1">
                                                    @foreach(json_decode($participation->contemplation->products, true) ?? [] as $product)
                                                        <li class="text-sm text-yellow-700 dark:text-yellow-300 flex items-center gap-2">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            {{ $product['name'] ?? 'N/A' }} - R$ {{ number_format($product['value'] ?? 0, 2, ',', '.') }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if($participation->contemplation->redemption_type === 'cash')
                                            <div class="mt-3 pt-3 border-t border-yellow-300 dark:border-yellow-700">
                                                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">Valor:</p>
                                                <p class="text-lg font-bold text-yellow-900 dark:text-yellow-100">
                                                    R$ {{ number_format($participation->contemplation->redemption_value ?? 0, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                                <!-- Todas as Parcelas (pagas, pendentes e vencidas) -->
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                        <i class="bi bi-calendar-check text-blue-600"></i>
                                        Todas as Parcelas
                                    </h4>
                                    <div class="space-y-2 max-h-72 overflow-y-auto">
                                        @php
                                            $payments = $participation->payments->sortBy('due_date');
                                        @endphp
                                        @forelse($payments as $payment)
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-3 px-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 text-slate-800 dark:text-slate-100 font-bold flex items-center justify-center">
                                                        {{ str_pad($payment->reference_month, 2, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                            {{ $payment->reference_month_name }}/{{ $payment->reference_year }}
                                                        </p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                                            Venc.: {{ optional($payment->due_date)->format('d/m/Y') ?? '-' }}
                                                            @if($payment->payment_date)
                                                                • Pago em {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                                            @endif
                                                        </p>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            @switch($payment->status)
                                                                @case('paid')
                                                                    <span class="text-[11px] font-semibold px-2 py-1 rounded bg-emerald-100 text-emerald-700">Paga</span>
                                                                    @break
                                                                @case('overdue')
                                                                    <span class="text-[11px] font-semibold px-2 py-1 rounded bg-red-100 text-red-700">Vencida</span>
                                                                    @break
                                                                @default
                                                                    <span class="text-[11px] font-semibold px-2 py-1 rounded bg-amber-100 text-amber-700">Pendente</span>
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-4 md:gap-6">
                                                    <div class="text-right">
                                                        <span class="block text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                            R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                                        </span>
                                                        @if($payment->status === 'overdue')
                                                            <span class="block text-[11px] text-red-500">Atualizar com juros/multa se houver</span>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-[120px] text-center">
                                                        @if ($payment->status !== 'paid')
                                                            @livewire('consortiums.record-payment', ['payment' => $payment], key('client-payment-'.$payment->id))
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                                <i class="bi bi-check-circle-fill"></i> Pago
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhuma parcela cadastrada</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
                <i class="bi bi-collection text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                <p class="text-lg font-semibold text-slate-900 dark:text-white">Nenhum consórcio encontrado</p>
                <p class="text-slate-600 dark:text-slate-400 mt-2">Este cliente ainda não está participando de nenhum consórcio.</p>
            </div>
        @endforelse
    </div>

    <!-- Componente de Exportação -->
    @livewire('consortiums.export-consortium')
</div>
