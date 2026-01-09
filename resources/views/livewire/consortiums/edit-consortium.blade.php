<div class="w-full space-y-4" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header Original com Botões Integrados -->
    <x-sales-header title="Editar Consórcio" subtitle="Modifique os detalhes do consórcio" icon="bi-pencil-square"
        :backRoute="route('consortiums.show', $consortium)">

        <x-slot name="actions">
            <div class="flex items-center gap-4">
                <!-- Cards de Resumo -->
                <div class="flex items-center gap-3">
                    <div class="px-4 py-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-cash-coin text-white/90 text-lg"></i>
                            <div>
                                <div class="text-xs text-white/70">Mensalidade</div>
                                <div class="text-base font-black text-white">R$ {{ number_format($monthly_value ?: 0, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock-history text-white/90 text-lg"></i>
                            <div>
                                <div class="text-xs text-white/70">Duração</div>
                                <div class="text-base font-black text-white">{{ $duration_months ?: 0 }} meses</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-people text-white/90 text-lg"></i>
                            <div>
                                <div class="text-xs text-white/70">Vagas</div>
                                <div class="text-base font-black text-white">{{ $max_participants ?: 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divisor -->
                <div class="h-10 w-px bg-white/30"></div>

                <!-- Botões de Ação -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('consortiums.show', $consortium) }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-bold rounded-lg border border-white/30 transition-all hover:scale-105 shadow-lg">
                        <i class="bi bi-x-lg text-base"></i>
                        <span class="text-sm">Cancelar</span>
                    </a>

                    <button type="submit" form="consortium-form"
                        class="flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-white/90 text-emerald-600 font-black rounded-lg transition-all shadow-lg hover:shadow-xl hover:scale-105">
                        <i class="bi bi-check-circle-fill text-lg"></i>
                        <span class="text-sm">Salvar Alterações</span>
                    </button>
                </div>
            </div>
        </x-slot>
    </x-sales-header>

    <!-- Form sem Card - Layout por Linhas -->
    <form id="consortium-form" wire:submit.prevent="save" class="space-y-3 transition-all duration-700 delay-100"
        :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">

        <!-- LINHA 1: Calendário + Info Básica -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

            <!-- Calendário Moderno (4 colunas) -->
            <div class="lg:col-span-4">
                <div class="relative rounded-2xl shadow-xl p-5"
                    style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(139, 92, 246, 0.08) 100%); border: 2px solid rgba(59, 130, 246, 0.3);">

                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg">
                                <i class="bi bi-calendar-event text-white text-lg"></i>
                            </div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">Data de Início</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="previousMonth"
                                class="p-2 rounded-lg bg-white/90 dark:bg-gray-800/90 hover:bg-white text-gray-700 dark:text-gray-300 shadow hover:shadow-md transition-all hover:scale-110">
                                <i class="bi bi-chevron-left text-sm"></i>
                            </button>
                            <button type="button" wire:click="nextMonth"
                                class="p-2 rounded-lg bg-white/90 dark:bg-gray-800/90 hover:bg-white text-gray-700 dark:text-gray-300 shadow hover:shadow-md transition-all hover:scale-110">
                                <i class="bi bi-chevron-right text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-center text-sm font-black text-gray-900 dark:text-white mb-3">
                        {{ \Carbon\Carbon::parse($start_date ?? now())->locale('pt_BR')->isoFormat('MMMM YYYY') }}
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        @foreach (['D', 'S', 'T', 'Q', 'Q', 'S', 'S'] as $day)
                            <div class="text-center text-sm font-black text-gray-600 dark:text-gray-400 py-2">
                                {{ $day }}</div>
                        @endforeach

                        @php
                            $date = \Carbon\Carbon::parse($start_date ?? now())->startOfMonth();
                            $endDate = $date->copy()->endOfMonth();
                            $startDay = $date->copy()->dayOfWeek;
                            $selectedDate = \Carbon\Carbon::parse($start_date ?? now());
                        @endphp

                        @for ($i = 0; $i < $startDay; $i++)
                            <div></div>
                        @endfor

                        @while ($date <= $endDate)
                            @php
                                $isSelected = $date->isSameDay($selectedDate);
                                $isToday = $date->isToday();
                                $dateString = $date->format('Y-m-d');
                            @endphp
                            <button type="button" wire:click="$set('start_date', '{{ $dateString }}')"
                                class="aspect-square rounded-lg text-sm font-bold transition-all hover:scale-110 {{ $isSelected ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg scale-105' : ($isToday ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 ring-2 ring-blue-500' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300') }}">
                                {{ $date->day }}
                            </button>
                            @php $date->addDay(); @endphp
                        @endwhile
                    </div>

                    <div
                        class="mt-4 p-3 rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-2 border-blue-300 dark:border-blue-600 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-calendar-check-fill text-blue-500 text-lg"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-bold">Selecionada</span>
                            </div>
                            <span class="font-black text-base text-blue-600 dark:text-blue-400">
                                {{ \Carbon\Carbon::parse($start_date ?? now())->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna com Informações Básicas + Valores Financeiros + Participantes (8 colunas) -->
            <div class="lg:col-span-8 space-y-4">
                <!-- Informações Básicas -->
                <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-200 dark:border-emerald-700">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-info-circle-fill text-white text-lg"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Informações Básicas</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label
                                class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-bookmark-fill text-emerald-500 text-base"></i>
                                Nome do Consórcio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.blur="name"
                                class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium shadow-sm"
                                placeholder="Ex: Consórcio de Veículos 2025">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-file-text-fill text-emerald-500 text-base"></i>
                                Descrição <span class="text-slate-400 text-xs">(opcional)</span>
                            </label>
                            <input type="text" wire:model.blur="description"
                                class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium shadow-sm"
                                placeholder="Descrição breve...">
                            @error('description')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Valores Financeiros -->
                <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-blue-200 dark:border-blue-700">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-cash-stack text-white text-lg"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Valores Financeiros</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label
                                class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-currency-dollar text-blue-500 text-base"></i>
                                Mensalidade <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-sm">R$</span>
                                <input type="number" wire:model.live="monthly_value" step="0.01" min="0.01"
                                    class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium shadow-sm"
                                    placeholder="0,00">
                            </div>
                            @error('monthly_value')
                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-clock-fill text-blue-500 text-base"></i>
                                Duração <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model.live="duration_months" min="1" max="120"
                                class="w-full px-3 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium shadow-sm"
                                placeholder="Ex: 24">
                            @error('duration_months')
                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Participantes + Total Geral -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Participantes -->
                    <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-3 pb-3 border-b-2 border-purple-200 dark:border-purple-700">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="bi bi-people-fill text-white text-lg"></i>
                            </div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Participantes</h2>
                        </div>

                        <div class="mt-4">
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                <i class="bi bi-person-check-fill text-purple-500 text-base"></i>
                                Máximo de Participantes <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model.live="max_participants" min="{{ $consortium->participants()->count() }}" max="1000"
                                class="w-full px-3 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm font-medium shadow-sm"
                                placeholder="Ex: 50">
                            @error('max_participants')
                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </p>
                            @enderror
                            @if($consortium->participants()->count() > 0)
                                <p class="mt-1 text-xs text-blue-600 flex items-center gap-1">
                                    <i class="bi bi-info-circle-fill"></i> Este consórcio já possui {{ $consortium->participants()->count() }} participante(s)
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Total Geral -->
                    <div
                        class="relative p-5 rounded-2xl bg-gradient-to-br from-emerald-500/15 to-teal-500/15 border-2 border-emerald-300 dark:border-emerald-600 shadow-xl overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-400/10 rounded-full blur-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                                    <i class="bi bi-calculator-fill text-white text-sm"></i>
                                </div>
                                <span class="text-sm font-black text-slate-700 dark:text-slate-300">Total Geral</span>
                            </div>
                            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mb-2">
                                R$ {{ number_format($total_value, 2, ',', '.') }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 bg-white/50 dark:bg-slate-800/50 rounded-lg px-2 py-1">
                                <i class="bi bi-info-circle-fill text-emerald-500"></i>
                                <span class="font-bold">{{ $max_participants ?: 0 }} participantes × R$ {{ number_format((floatval($monthly_value) * floatval($duration_months)) ?: 0, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LINHA 2: Configurações (Modo + Frequência) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div>
                <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-4 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3 pb-3 mb-3 border-b-2 border-orange-200 dark:border-orange-700">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-toggles text-white text-lg"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Modo do Consórcio</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="mode" value="draw" class="sr-only peer" {{ $consortium->draws()->count() > 0 ? 'disabled' : '' }}>
                            <div
                                class="flex flex-col items-center gap-3 p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all {{ $consortium->draws()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <div
                                    class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg {{ $mode === 'draw' ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-trophy-fill text-xl"></i>
                                </div>
                                <div class="text-center">
                                    <div class="font-black text-sm text-slate-900 dark:text-white">Com Sorteio</div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Sorteios regulares</div>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="mode" value="payoff" class="sr-only peer" {{ $consortium->draws()->count() > 0 ? 'disabled' : '' }}>
                            <div
                                class="flex flex-col items-center gap-3 p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all {{ $consortium->draws()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <div
                                    class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg {{ $mode === 'payoff' ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-box-seam-fill text-xl"></i>
                                </div>
                                <div class="text-center">
                                    <div class="font-black text-sm text-slate-900 dark:text-white">Por Quitação</div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Sem sorteio</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('mode')
                        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                    @enderror
                    @if($consortium->draws()->count() > 0)
                        <p class="mt-2 text-xs text-amber-600 flex items-center gap-1">
                            <i class="bi bi-exclamation-triangle-fill"></i> Este consórcio já possui sorteios, não é possível alterar o modo
                        </p>
                    @endif
                </div>
            </div>

            <div>
                <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-4 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3 pb-3 mb-3 border-b-2 border-orange-200 dark:border-orange-700">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-calendar-event-fill text-white text-lg"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Frequência dos Sorteios</h2>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="draw_frequency" value="weekly" class="sr-only peer" {{ $consortium->draws()->count() > 0 ? 'disabled' : '' }}>
                            <div
                                class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all {{ $consortium->draws()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg {{ $draw_frequency === 'weekly' ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-calendar-week-fill text-base"></i>
                                </div>
                                <div class="font-black text-xs text-slate-900 dark:text-white text-center">Semanal</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="draw_frequency" value="monthly" class="sr-only peer" {{ $consortium->draws()->count() > 0 ? 'disabled' : '' }}>
                            <div
                                class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all {{ $consortium->draws()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg {{ $draw_frequency === 'monthly' ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-calendar-month-fill text-base"></i>
                                </div>
                                <div class="font-black text-xs text-slate-900 dark:text-white text-center">Mensal</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="draw_frequency" value="bimonthly"
                                class="sr-only peer" {{ $consortium->draws()->count() > 0 ? 'disabled' : '' }}>
                            <div
                                class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all {{ $consortium->draws()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg {{ $draw_frequency === 'bimonthly' ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-calendar2-range-fill text-base"></i>
                                </div>
                                <div class="font-black text-xs text-slate-900 dark:text-white text-center">Bimestral</div>
                            </div>
                        </label>
                    </div>
                    @error('draw_frequency')
                        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </p>
                    @enderror
                    @if($consortium->draws()->count() > 0)
                        <p class="mt-2 text-xs text-amber-600 flex items-center gap-1">
                            <i class="bi bi-exclamation-triangle-fill"></i> Frequência não pode ser alterada após sorteios iniciados
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- LINHA 3: Status (apenas para edit) -->
        <div class="bg-white/50 dark:bg-slate-800/50 rounded-2xl shadow-xl p-5 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3 pb-3 mb-3 border-b-2 border-indigo-200 dark:border-indigo-700">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-flag-fill text-white text-lg"></i>
                </div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Status do Consórcio</h2>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="status" value="active" class="sr-only peer">
                    <div
                        class="flex flex-col items-center gap-3 p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg {{ $status === 'active' ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                            <i class="bi bi-check-circle-fill text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-black text-sm text-slate-900 dark:text-white">Ativo</div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Em funcionamento</div>
                        </div>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="status" value="completed" class="sr-only peer">
                    <div
                        class="flex flex-col items-center gap-3 p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg {{ $status === 'completed' ? 'bg-gradient-to-br from-blue-500 to-cyan-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                            <i class="bi bi-trophy-fill text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-black text-sm text-slate-900 dark:text-white">Concluído</div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Finalizado</div>
                        </div>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="status" value="cancelled" class="sr-only peer">
                    <div
                        class="flex flex-col items-center gap-3 p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 peer-checked:shadow-xl peer-checked:scale-105 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg {{ $status === 'cancelled' ? 'bg-gradient-to-br from-red-500 to-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                            <i class="bi bi-x-circle-fill text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-black text-sm text-slate-900 dark:text-white">Cancelado</div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Encerrado</div>
                        </div>
                    </div>
                </label>
            </div>
            @error('status')
                <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </form>
</div>
