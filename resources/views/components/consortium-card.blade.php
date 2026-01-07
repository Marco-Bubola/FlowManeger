@props(['consortium'])

<div
    class="relative flex flex-col overflow-hidden bg-gradient-to-br from-white via-white to-emerald-50/50 dark:from-slate-800 dark:via-slate-800 dark:to-emerald-900/20 rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border border-slate-200/50 dark:border-slate-700/50 backdrop-blur-sm group">

    <!-- Barra de Status no Topo -->
    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r {{ $consortium->status_color }}"></div>

    <!-- Background decorativo animado -->
    <div
        class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br {{ $consortium->status_color }} opacity-5 rounded-full transform translate-x-8 -translate-y-8 group-hover:scale-150 transition-transform duration-500">
    </div>

    <!-- Header do Card -->
    <div class="relative p-5 pb-3 border-b border-slate-200/50 dark:border-slate-700/50 bg-gradient-to-r from-white/80 to-transparent dark:from-slate-800/80">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div
                    class="relative flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br {{ $consortium->status_color }} shadow-lg">
                    <i class="bi bi-piggy-bank text-white text-xl"></i>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/20 to-transparent opacity-50 group-hover:opacity-70 transition-opacity">
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        {{ $consortium->name }}
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $consortium->status_color }} border border-current/20">
                            {{ $consortium->status_label }}
                        </span>
                        @if ($consortium->draw_frequency)
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                <i class="bi bi-calendar-event"></i>
                                {{ ucfirst($consortium->draw_frequency) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dropdown de Ações -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                    class="flex items-center justify-center w-8 h-8 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 rounded-xl shadow-2xl bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 z-50 border border-slate-200 dark:border-slate-700"
                    style="display: none;">
                    <div class="py-1">
                        <a href="{{ route('consortiums.show', $consortium) }}"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors">
                            <i class="bi bi-eye text-emerald-600 dark:text-emerald-400"></i>
                            <span>Ver Detalhes</span>
                        </a>
                        <a href="{{ route('consortiums.edit', $consortium) }}"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                            <i class="bi bi-pencil text-blue-600 dark:text-blue-400"></i>
                            <span>Editar</span>
                        </a>
                        @if ($consortium->canPerformDraw())
                            <a href="{{ route('consortiums.draw', $consortium) }}"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-colors">
                                <i class="bi bi-trophy text-purple-600 dark:text-purple-400"></i>
                                <span>Realizar Sorteio</span>
                            </a>
                        @endif
                        <button wire:click="confirmDelete({{ $consortium->id }})"
                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                            <i class="bi bi-trash"></i>
                            <span>Excluir</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($consortium->description)
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">
                {{ $consortium->description }}
            </p>
        @endif
    </div>

    <!-- Informações Financeiras -->
    <div class="p-5 space-y-4">
        <!-- Valores -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <i class="bi bi-calendar-range"></i>
                    <span>Valor Mensal</span>
                </div>
                <div class="text-lg font-bold text-slate-900 dark:text-white">
                    R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <i class="bi bi-cash-stack"></i>
                    <span>Valor Total</span>
                </div>
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    R$ {{ number_format($consortium->total_value, 2, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Informações de Participação -->
        <div class="grid grid-cols-3 gap-3">
            <div
                class="flex flex-col items-center justify-center p-3 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 rounded-xl border border-emerald-200/50 dark:border-emerald-700/50">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $consortium->active_participants_count }}
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 text-center">
                    Participantes
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-center p-3 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200/50 dark:border-blue-700/50">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $consortium->contemplated_count }}
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 text-center">
                    Contemplados
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-center p-3 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200/50 dark:border-purple-700/50">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $consortium->duration_months }}
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400 text-center">
                    Meses
                </div>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-400">Progresso do Consórcio</span>
                <span class="font-semibold text-slate-900 dark:text-white">
                    {{ number_format($consortium->completion_percentage, 1) }}%
                </span>
            </div>
            <div class="h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 rounded-full transition-all duration-500 shadow-lg"
                    style="width: {{ $consortium->completion_percentage }}%">
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <i class="bi bi-calendar-check"></i>
                <span>Início: {{ $consortium->start_date?->format('d/m/Y') ?? 'Não definido' }}</span>
            </div>

            @if ($consortium->canAddParticipants())
                <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ $consortium->getRemainingSlots() }} vagas
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-orange-700 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    Completo
                </span>
            @endif
        </div>

        <!-- Total Arrecadado -->
        @if ($consortium->total_collected > 0)
            <div
                class="flex items-center justify-between p-3 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center justify-center w-8 h-8 bg-emerald-500 dark:bg-emerald-600 rounded-lg">
                        <i class="bi bi-wallet2 text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Total Arrecadado</span>
                </div>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    R$ {{ number_format($consortium->total_collected, 2, ',', '.') }}
                </span>
            </div>
        @endif
    </div>

    <!-- Botões de Ação Rápida -->
    <div
        class="flex items-center gap-2 p-4 border-t border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30">
        <a href="{{ route('consortiums.show', $consortium) }}"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 rounded-xl transition-all transform hover:scale-105 border border-emerald-200 dark:border-emerald-700">
            <i class="bi bi-eye"></i>
            <span>Ver Detalhes</span>
        </a>

        @if ($consortium->canPerformDraw())
            <a href="{{ route('consortiums.draw', $consortium) }}"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                <i class="bi bi-trophy-fill"></i>
                <span>Sortear</span>
            </a>
        @endif
    </div>
</div>
