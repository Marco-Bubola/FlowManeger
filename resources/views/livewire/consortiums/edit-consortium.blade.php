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
        :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
        <!-- Background decorativo com overflow controlado -->
        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-teal-400/20 to-cyan-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-cyan-400/10 via-emerald-400/10 to-teal-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>
        </div>

        <div class="relative px-8 py-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <!-- Título e Info -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('consortiums.show', $consortium) }}"
                        class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 transition-all duration-300 shadow-lg hover:shadow-xl group">
                        <i class="bi bi-arrow-left text-white text-xl group-hover:scale-110 transition-transform duration-300"></i>
                    </a>

                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 flex items-center justify-center group">
                        <i class="bi bi-pencil-square text-4xl text-white group-hover:scale-110 transition-transform duration-300"></i>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-emerald-300 dark:via-teal-300 dark:to-cyan-300 bg-clip-text text-transparent">
                            Editar Consórcio
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            <i class="bi bi-folder mr-1"></i>{{ $consortium->name }}
                        </p>
                    </div>
                </div>

                <!-- Status e Ação -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-white/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-lg backdrop-blur-sm">
                        <i class="bi bi-activity mr-2"></i>
                        {{ ucfirst($status) }}
                    </span>
                    <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-white/90 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-lg backdrop-blur-sm">
                        <i class="bi bi-graph-up"></i>
                        <span>Passo {{ $currentStep }} / 3</span>
                    </div>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-6">
                <div class="p-4 rounded-2xl border border-emerald-100 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-900/30 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Mensalidade</p>
                        <p class="text-xl font-bold text-emerald-900 dark:text-emerald-100">R$ {{ number_format($monthly_value ?: 0, 2, ',', '.') }}</p>
                    </div>
                    <i class="bi bi-wallet2 text-2xl text-emerald-500"></i>
                </div>
                <div class="p-4 rounded-2xl border border-blue-100 dark:border-blue-800 bg-blue-50/60 dark:bg-blue-900/30 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-300">Duração</p>
                        <p class="text-xl font-bold text-blue-900 dark:text-blue-100">{{ $duration_months ?: 0 }} meses</p>
                    </div>
                    <i class="bi bi-calendar2-range text-2xl text-blue-500"></i>
                </div>
                <div class="p-4 rounded-2xl border border-amber-100 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/30 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Vagas</p>
                        <p class="text-xl font-bold text-amber-900 dark:text-amber-100">{{ $max_participants ?: 0 }}</p>
                    </div>
                    <i class="bi bi-people text-2xl text-amber-500"></i>
                </div>
                <div class="p-4 rounded-2xl border border-purple-100 dark:border-purple-800 bg-purple-50/60 dark:bg-purple-900/30 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-700 dark:text-purple-300">Total Estimado</p>
                        <p class="text-xl font-bold text-purple-900 dark:text-purple-100">R$ {{ number_format($total_value ?: 0, 2, ',', '.') }}</p>
                    </div>
                    <i class="bi bi-cash-stack text-2xl text-purple-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= 3; $i++)
                <div class="flex items-center {{ $i < 3 ? 'flex-1' : '' }}">
                <button wire:click="goToStep({{ $i }})" @if ($i > $currentStep) disabled @endif
                    class="relative flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300 {{ $currentStep >= $i ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg scale-110' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                    @if ($currentStep > $i)
                        <i class="bi bi-check-lg text-xl font-bold"></i>
                    @else
                        <span class="text-lg font-bold">{{ $i }}</span>
                    @endif
                    @if ($currentStep === $i)
                        <div class="absolute inset-0 rounded-full bg-emerald-500 animate-ping opacity-25"></div>
                    @endif
                </button>

                @if ($i < 3)
                    <div
                        class="flex-1 h-1 mx-4 rounded-full transition-all duration-300 {{ $currentStep > $i ? 'bg-gradient-to-r from-emerald-500 to-teal-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                    </div>
                @endif
        </div>
        @endfor
    </div>

    <div class="flex justify-between text-sm mt-2">
        <span
            class="text-xs font-medium {{ $currentStep === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
            Informações Básicas
        </span>
        <span
            class="text-xs font-medium {{ $currentStep === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
            Configurações
        </span>
        <span
            class="text-xs font-medium {{ $currentStep === 3 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
            Revisão
        </span>
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

        <form wire:submit.prevent="{{ $currentStep === 3 ? 'update' : 'nextStep' }}">
            <!-- Passo 1: Informações Básicas -->
            @if ($currentStep === 1)
                <div class="p-8 space-y-6">
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
                </div>
            @endif

            <!-- Passo 2: Configurações -->
            @if ($currentStep === 2)
                <div class="p-8 space-y-6">
                    <div class="flex items-center gap-3 mb-6">
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
                                    <i class="bi bi-exclamation-circle"></i> Já possui {{ $consortium->participants()->count() }} participantes
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

                    <!-- Modo do Consórcio -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Modo do Consórcio <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label
                                class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $mode === 'draw' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="mode" value="draw" class="sr-only">
                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-lg {{ $mode === 'draw' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900 dark:text-white">Com Sorteio</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Sorteios conforme frequência</div>
                                </div>
                            </label>

                            <label
                                class="relative flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-500 {{ $mode === 'payoff' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="mode" value="payoff" class="sr-only">
                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-lg {{ $mode === 'payoff' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900 dark:text-white">Resgate por Quitação</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Sem sorteio, resgata ao pagar tudo</div>
                                </div>
                            </label>
                        </div>
                        @error('mode')
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
                                <input type="radio" wire:model.live="draw_frequency" value="monthly"
                                    class="sr-only">
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
                                <input type="radio" wire:model.live="draw_frequency" value="bimonthly"
                                    class="sr-only">
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
                                <input type="radio" wire:model.live="draw_frequency" value="weekly"
                                    class="sr-only">
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
                            class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Valor total do consórcio:
                                </span>
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ number_format($total_value, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Passo 3: Revisão -->
            @if ($currentStep === 3)
                <div class="p-8 space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl">
                            <i class="bi bi-check-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Revisão</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Confirme os dados antes de salvar</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Informações Básicas -->
                        <div
                            class="p-6 bg-gradient-to-r from-white to-emerald-50 dark:from-slate-900 dark:to-emerald-900/10 rounded-xl border border-slate-200 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
                                <i class="bi bi-info-circle"></i>
                                Informações Básicas
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Nome</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $name }}</p>
                                </div>
                                @if ($description)
                                    <div class="col-span-2">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Descrição</span>
                                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $description }}</p>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Valor Mensal</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">R$
                                        {{ number_format($monthly_value, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Duração</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $duration_months }} meses</p>
                                </div>
                            </div>
                        </div>

                        <!-- Configurações -->
                        <div
                            class="p-6 bg-gradient-to-r from-white to-blue-50 dark:from-slate-900 dark:to-blue-900/10 rounded-xl border border-slate-200 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-4 flex items-center gap-2">
                                <i class="bi bi-gear"></i>
                                Configurações
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Participantes</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $max_participants }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Frequência</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ ucfirst($draw_frequency) }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Início</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Modo</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $mode === 'draw' ? 'Com Sorteio' : 'Quitação' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Status</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ ucfirst($status) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo Financeiro -->
                        <div
                            class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-2 border-purple-200 dark:border-purple-700">
                            <h3
                                class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-4 flex items-center gap-2">
                                <i class="bi bi-cash-stack"></i>
                                Resumo Financeiro
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Valor por participante:</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">R$
                                        {{ number_format($monthly_value * $duration_months, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Mensalidade:</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">R$
                                        {{ number_format($monthly_value, 2, ',', '.') }}</span>
                                </div>
                                <div class="h-px bg-purple-200 dark:bg-purple-700"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-bold text-slate-900 dark:text-white">Total do Consórcio:</span>
                                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">R$
                                        {{ number_format($total_value, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div
                class="flex items-center justify-between gap-4 p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
                @if ($currentStep > 1)
                    <button type="button" wire:click="previousStep"
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                        <i class="bi bi-arrow-left"></i>
                        <span>Voltar</span>
                    </button>
                @else
                    <a href="{{ route('consortiums.show', $consortium) }}"
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                        <i class="bi bi-x-lg"></i>
                        <span>Cancelar</span>
                    </a>
                @endif

                <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    @if ($currentStep === 3)
                        <i class="bi bi-check-circle"></i>
                        <span>Salvar Alterações</span>
                    @else
                        <span>Próximo</span>
                        <i class="bi bi-arrow-right"></i>
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>
