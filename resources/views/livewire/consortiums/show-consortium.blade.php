<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('consortiums.index') }}"
                    class="flex items-center justify-center w-10 h-10 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    <i class="bi bi-arrow-left text-slate-600 dark:text-slate-400"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                            {{ $this->consortium->name }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold {{ $this->consortium->status_color }}">
                            {{ $this->consortium->status_label }}
                        </span>
                    </div>
                    @if ($this->consortium->description)
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $this->consortium->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if ($this->consortium->canAddParticipants())
                    @livewire('consortiums.add-participant', ['consortium' => $this->consortium], key('add-participant-'.$this->consortium->id))
                @endif
                @if ($this->consortium->canPerformDraw())
                    <a href="{{ route('consortiums.draw', $this->consortium) }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all shadow-lg">
                        <i class="bi bi-trophy-fill"></i><span>Realizar Sorteio</span>
                    </a>
                @endif
                <a href="{{ route('consortiums.edit', $this->consortium) }}" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    <i class="bi bi-pencil"></i><span>Editar</span>
                </a>
                @livewire('consortiums.delete-consortium', ['consortium' => $this->consortium], key('delete-consortium-'.$this->consortium->id))
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Participantes Ativos</span>
                <div class="w-10 h-10 bg-emerald-500 dark:bg-emerald-600 rounded-lg flex items-center justify-center"><i class="bi bi-people text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ $this->consortium->active_participants_count }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">de {{ $this->consortium->max_participants }} vagas</div>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Contemplados</span>
                <div class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-lg flex items-center justify-center"><i class="bi bi-trophy text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $this->consortium->contemplated_count }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ number_format(($this->consortium->contemplated_count / max($this->consortium->active_participants_count, 1)) * 100, 1) }}% do total</div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Total Arrecadado</span>
                <div class="w-10 h-10 bg-purple-500 dark:bg-purple-600 rounded-lg flex items-center justify-center"><i class="bi bi-cash-stack text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">R$ {{ number_format($this->consortium->total_collected, 2, ',', '.') }}</div>
            <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">de R$ {{ number_format($this->consortium->total_value, 2, ',', '.') }}</div>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl p-6 border border-orange-200 dark:border-orange-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-orange-700 dark:text-orange-400">Progresso</span>
                <div class="w-10 h-10 bg-orange-500 dark:bg-orange-600 rounded-lg flex items-center justify-center"><i class="bi bi-graph-up text-white"></i></div>
            </div>
            <div class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{ number_format($this->consortium->completion_percentage, 1) }}%</div>
            <div class="h-2 bg-orange-200 dark:bg-orange-800 rounded-full mt-3">
                <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full transition-all" style="width: {{ $this->consortium->completion_percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
        <div class="border-b border-slate-200 dark:border-slate-700">
            <nav class="flex gap-2 p-4 overflow-x-auto">
                <button wire:click="setTab('overview')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'overview' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-grid"></i><span>Vis�o Geral</span>
                </button>
                <button wire:click="setTab('participants')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'participants' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-people"></i><span>Participantes</span><span class="px-2 py-0.5 bg-emerald-500 text-white text-xs rounded-full">{{ $this->consortium->active_participants_count }}</span>
                </button>
                <button wire:click="setTab('payments')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'payments' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-wallet2"></i><span>Pagamentos</span>
                </button>
                <button wire:click="setTab('draws')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'draws' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-trophy"></i><span>Sorteios</span><span class="px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full">{{ $this->consortium->draws->count() }}</span>
                </button>
                <button wire:click="setTab('contemplated')" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all {{ $activeTab === 'contemplated' ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi bi-star"></i><span>Contemplados</span><span class="px-2 py-0.5 bg-blue-500 text-white text-xs rounded-full">{{ $this->consortium->contemplated_count }}</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            @if ($activeTab === 'overview')
                <div class="text-center py-12">
                    <i class="bi bi-grid text-6xl text-emerald-500 mb-4"></i>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white">Vis�o Geral do Cons�rcio</p>
                    <p class="text-slate-600 dark:text-slate-400">Informa��es detalhadas em breve</p>
                </div>
            @elseif ($activeTab === 'participants')
                @if ($this->participants->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-people text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum participante cadastrado</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">Adicione participantes usando o botão acima</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">#</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Cliente</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Data Entrada</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Pagamentos</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Total Pago</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Contemplado</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">A��es</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($this->participants as $participant)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg font-semibold text-sm">
                                                {{ $participant->participation_number }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($participant->client->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $participant->client->name ?? 'N/A' }}</p>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $participant->client->email ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($participant->entry_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold {{ $participant->status_color }}">
                                                {{ $participant->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <span class="text-slate-700 dark:text-slate-300">
                                                {{ $participant->payments->where('status', 'paid')->count() }} / {{ $this->consortium->duration_months }}
                                            </span>
                                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 mt-1">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $participant->payment_percentage }}%"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-slate-100">
                                            R$ {{ number_format($participant->total_paid, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($participant->is_contemplated)
                                                <i class="bi bi-star-fill text-yellow-500 text-xl"></i>
                                            @else
                                                <i class="bi bi-star text-slate-300 dark:text-slate-600 text-xl"></i>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button
                                                wire:click="removeParticipant({{ $participant->id }})"
                                                wire:confirm="Tem certeza que deseja remover este participante? Se ele já tiver pagamentos, será marcado como desistente."
                                                type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Remover participante"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumo -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300 font-semibold">Total Participantes</p>
                                    <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100 mt-1">{{ $this->participants->count() }}</p>
                                </div>
                                <i class="bi bi-people text-3xl text-emerald-600 dark:text-emerald-400"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 font-semibold">Ativos</p>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ $this->participants->where('status', 'active')->count() }}</p>
                                </div>
                                <i class="bi bi-check-circle text-3xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold">Contemplados</p>
                                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">{{ $this->participants->where('is_contemplated', true)->count() }}</p>
                                </div>
                                <i class="bi bi-star-fill text-3xl text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($activeTab === 'payments')
                @if ($this->payments->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-wallet2 text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum pagamento registrado</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Cliente</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Referência</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Vencimento</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Pagamento</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Valor</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($this->payments as $payment)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="py-3 px-4 text-slate-900 dark:text-slate-100 font-medium">
                                            {{ $payment->participant->client->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                            {{ $payment->reference_month_name }}/{{ $payment->reference_year }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                            {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-slate-100">
                                            R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold {{ $payment->status_color }}">
                                                {{ $payment->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($payment->status !== 'paid')
                                                @livewire('consortiums.record-payment', ['payment' => $payment], key('payment-'.$payment->id))
                                            @else
                                                <span class="text-emerald-600 dark:text-emerald-400">
                                                    <i class="bi bi-check-circle-fill"></i> Pago
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif ($activeTab === 'draws')
                @if ($this->draws->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-trophy text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum sorteio realizado</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">Use o botão "Realizar Sorteio" para criar um novo sorteio</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($this->draws as $draw)
                            <div class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl p-6 hover:shadow-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <i class="bi bi-trophy-fill text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-slate-100">Sorteio #{{ $draw->draw_number }}</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if ($draw->winner)
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Vencedor</p>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $draw->winner->client->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Participação #{{ $draw->winner->participation_number }}</p>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300">
                                                Sem vencedor
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @elseif ($activeTab === 'contemplated')
                @if ($this->contemplated->isEmpty())
                    <div class="text-center py-12">
                        <i class="bi bi-star text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <p class="text-slate-600 dark:text-slate-400">Nenhum participante contemplado ainda</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($this->contemplated as $participant)
                            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $participant->client->name ?? 'N/A' }}</p>
                                            <p class="text-sm text-slate-600 dark:text-slate-400">Participação #{{ $participant->participation_number }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-slate-400">Data Contemplação:</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($participant->contemplation_date)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-slate-400">Tipo:</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $participant->contemplation_type_label ?? 'N/A' }}</span>
                                    </div>
                                    @if ($participant->contemplation)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-600 dark:text-slate-400">Resgate:</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $participant->contemplation->redemption_type_label ?? 'Pendente' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

