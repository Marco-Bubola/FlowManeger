<div class="max-w-5xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('consortiums.index') }}"
                    class="flex items-center justify-center w-10 h-10 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                    <i class="bi bi-arrow-left text-slate-600 dark:text-slate-400"></i>
                </a>
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-teal-700 dark:from-slate-100 dark:via-emerald-300 dark:to-teal-300 bg-clip-text text-transparent">
                        Novo Consórcio
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Siga os passos para criar um novo consórcio
                    </p>
                </div>
            </div>

            <button wire:click="toggleTips"
                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="bi bi-lightbulb"></i>
                <span class="text-sm font-medium">Dicas</span>
            </button>
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

    <!-- Form Card -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        <form wire:submit.prevent="{{ $currentStep === 3 ? 'save' : 'nextStep' }}">
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
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 dark:text-slate-400">
                                    R$
                                </div>
                                <input type="number" wire:model.live="monthly_value" step="0.01" min="0.01"
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
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
                            </label>
                            <input type="number" wire:model.live="duration_months" min="1" max="120"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                placeholder="Ex: 24">
                            @error('duration_months')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Preview Valor Total -->
                    @if ($total_value > 0)
                        <div
                            class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Valor total estimado por participante:
                                </span>
                                <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format($monthly_value * $duration_months, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif
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
                        </label>
                        <input type="number" wire:model.live="max_participants" min="2" max="1000"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
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

                    <!-- Preview Valor Total do Consórcio -->
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
                            <p class="text-sm text-slate-600 dark:text-slate-400">Confirme os dados antes de criar
                            </p>
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
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $duration_months }}
                                        meses</p>
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
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $max_participants }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Frequência</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ ucfirst($draw_frequency) }}</p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Início</span>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</p>
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
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Valor por
                                        participante:</span>
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
                                    <span class="text-base font-bold text-slate-900 dark:text-white">Total do
                                        Consórcio:</span>
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
                    <a href="{{ route('consortiums.index') }}"
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-all">
                        <i class="bi bi-x-lg"></i>
                        <span>Cancelar</span>
                    </a>
                @endif

                <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 hover:from-emerald-700 hover:via-teal-700 hover:to-green-700 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    @if ($currentStep === 3)
                        <i class="bi bi-check-circle"></i>
                        <span>Criar Consórcio</span>
                    @else
                        <span>Próximo</span>
                        <i class="bi bi-arrow-right"></i>
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>
