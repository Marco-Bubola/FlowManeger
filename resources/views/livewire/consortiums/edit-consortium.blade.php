<div class="max-w-5xl mx-auto px-4 py-8">
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
                        class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                        Editar Consórcio
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {{ $consortium->name }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Restrições -->
    @if ($hasDraws || $hasParticipants)
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-amber-600 dark:text-amber-400 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-200 mb-1">
                        Atenção - Edição Limitada
                    </h3>
                    <ul class="text-sm text-amber-700 dark:text-amber-300 space-y-1">
                        @if ($hasParticipants)
                            <li>• Este consórcio já possui participantes cadastrados. Algumas alterações podem afetar os
                                dados existentes.</li>
                        @endif
                        @if ($hasDraws)
                            <li>• Sorteios já foram realizados. Alterações em valores podem causar inconsistências.</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        <form wire:submit.prevent="update">
            <div class="p-8 space-y-6">
                <!-- Informações Básicas -->
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl">
                        <i class="bi bi-info-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Informações Básicas</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Dados principais do consórcio</p>
                    </div>
                </div>

                <!-- Nome -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Nome do Consórcio <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model.blur="name"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                        placeholder="Ex: Consórcio de Veículos 2025">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Descrição
                    </label>
                    <textarea wire:model.blur="description" rows="3"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                        placeholder="Descreva o objetivo e detalhes do consórcio..."></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Valor Mensal -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Valor da Mensalidade <span class="text-red-500">*</span>
                            @if ($hasDraws)
                                <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">
                                    <i class="bi bi-exclamation-circle"></i> Já possui sorteios
                                </span>
                            @endif
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 dark:text-slate-400">
                                R$
                            </div>
                            <input type="number" wire:model.live="monthly_value" step="0.01" min="0.01"
                                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all {{ $hasDraws ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}"
                                placeholder="0,00">
                        </div>
                        @error('monthly_value')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Duração -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Duração em Meses <span class="text-red-500">*</span>
                            @if ($hasDraws)
                                <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">
                                    <i class="bi bi-exclamation-circle"></i> Já possui sorteios
                                </span>
                            @endif
                        </label>
                        <input type="number" wire:model.live="duration_months" min="1" max="120"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all {{ $hasDraws ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}"
                            placeholder="Ex: 24">
                        @error('duration_months')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Configurações -->
                <div class="flex items-center gap-3 mt-8 mb-6">
                    <div
                        class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl">
                        <i class="bi bi-gear text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Configurações</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Defina as regras do consórcio</p>
                    </div>
                </div>

                <!-- Máximo de Participantes -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Número Máximo de Participantes <span class="text-red-500">*</span>
                        @if ($hasParticipants)
                            <span class="text-xs text-amber-600 dark:text-amber-400 ml-2">
                                <i class="bi bi-exclamation-circle"></i> Já possui {{ $consortium->participants()->count() }}
                                participantes
                            </span>
                        @endif
                    </label>
                    <input type="number" wire:model.live="max_participants" min="{{ $consortium->participants()->count() }}"
                        max="1000"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all {{ $hasParticipants ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}"
                        placeholder="Ex: 50">
                    @error('max_participants')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Frequência de Sorteios -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Frequência dos Sorteios <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label
                            class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $draw_frequency === 'monthly' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                            <input type="radio" wire:model.live="draw_frequency" value="monthly" class="sr-only">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-lg {{ $draw_frequency === 'monthly' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="bi bi-calendar-month"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-900 dark:text-white">Mensal</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">1x por mês</div>
                            </div>
                        </label>

                        <label
                            class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $draw_frequency === 'bimonthly' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                            <input type="radio" wire:model.live="draw_frequency" value="bimonthly" class="sr-only">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-lg {{ $draw_frequency === 'bimonthly' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="bi bi-calendar2-range"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-900 dark:text-white">Bimestral</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">A cada 2 meses</div>
                            </div>
                        </label>

                        <label
                            class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $draw_frequency === 'weekly' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                            <input type="radio" wire:model.live="draw_frequency" value="weekly" class="sr-only">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-lg {{ $draw_frequency === 'weekly' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                <i class="bi bi-calendar-week"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-900 dark:text-white">Semanal</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">1x por semana</div>
                            </div>
                        </label>
                    </div>
                    @error('draw_frequency')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data de Início -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Data de Início <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model.blur="start_date"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="status"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                        <option value="active">Ativo</option>
                        <option value="completed">Concluído</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Valor Total -->
                @if ($total_value > 0)
                    <div
                        class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200 dark:border-purple-700">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Valor total do consórcio:
                            </span>
                            <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                R$ {{ number_format($total_value, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div
                class="flex items-center justify-between gap-4 p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('consortiums.show', $consortium) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    <i class="bi bi-x-lg"></i>
                    <span>Cancelar</span>
                </a>

                <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="bi bi-check-circle"></i>
                    <span>Salvar Alterações</span>
                </button>
            </div>
        </form>
    </div>
</div>
