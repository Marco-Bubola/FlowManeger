<div class="w-full bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900" style="min-height: 100vh;">
    <x-sales-header
        title="🎯 Criar Novo Hábito"
        description="Adicione um novo hábito à sua rotina diária">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('daily-habits.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="bi bi-calendar-check mr-1"></i>Hábitos Diários
                </a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-800 dark:text-slate-200 font-medium">
                    Criar Hábito
                </span>
            </div>
        </x-slot>
    </x-sales-header>

    <div class="max-w-4xl mx-auto px-6 py-8">
        <!-- Mensagens de Feedback -->
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl shadow-lg animate-bounce-in">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl shadow-lg animate-bounce-in">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <p class="font-semibold text-red-800 mb-2">⚠️ Erros de validação:</p>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário em Grid -->
        <form wire:submit.prevent="createHabit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Coluna Principal (2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Seção: Informações Básicas -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-info-circle text-blue-500"></i>
                        Informações Básicas
                    </h3>

                    <!-- Nome do Hábito -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Nome do Hábito *
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white"
                            placeholder="Ex: Beber água, Fazer exercício..."
                        >
                        @error('name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Descrição (opcional)
                        </label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white"
                            placeholder="Descreva seu hábito..."
                        ></textarea>
                        @error('description')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Aparência -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-palette text-purple-500"></i>
                        Aparência
                    </h3>

                    <!-- Seleção de Ícone -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Escolha um Ícone *
                        </label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                            @foreach($availableIcons as $iconClass => $iconName)
                                <button
                                    type="button"
                                    wire:click="$set('icon', '{{ $iconClass }}')"
                                    class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all hover:scale-105 {{ $icon === $iconClass ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 shadow-md' : 'border-gray-200 dark:border-slate-600 hover:border-gray-300 dark:hover:border-slate-500' }}"
                                >
                                    <i class="bi {{ $iconClass }} text-3xl mb-1 {{ $icon === $iconClass ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }}"></i>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $iconName }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('icon')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Seleção de Cor -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Escolha uma Cor *
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($availableColors as $colorHex => $colorName)
                                <button
                                    type="button"
                                    wire:click="$set('color', '{{ $colorHex }}')"
                                    class="flex flex-col items-center p-3 rounded-xl border-2 transition-all hover:scale-105 {{ $color === $colorHex ? 'border-gray-800 dark:border-gray-400 shadow-lg' : 'border-gray-200 dark:border-slate-600' }}"
                                >
                                    <div
                                        class="w-12 h-12 rounded-full mb-1 {{ $color === $colorHex ? 'ring-4 ring-offset-2 ring-gray-800 dark:ring-gray-400' : '' }}"
                                        style="background-color: {{ $colorHex }};"
                                    ></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $colorName }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('color')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Configurações -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-gear text-green-500"></i>
                        Configurações
                    </h3>

                    <!-- Meta Diária -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Quantas vezes por dia? *
                        </label>
                        <input
                            type="number"
                            wire:model="goal_frequency"
                            min="1"
                            max="10"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white"
                        >
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Meta de quantas vezes você quer fazer este hábito por dia (1-10)</p>
                        @error('goal_frequency')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Horário de Lembrete -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Horário de Lembrete (opcional)
                        </label>
                        <input
                            type="time"
                            wire:model="reminder_time"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white"
                        >
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Receba uma notificação neste horário para lembrar do seu hábito</p>
                        @error('reminder_time')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (1/3) - Preview e Ações -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Preview do Hábito -->
                @if($name)
                    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 sticky top-6">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                            <i class="bi bi-eye text-indigo-500"></i>
                            Preview
                        </h3>
                        <div class="space-y-4">
                            <div class="flex flex-col items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-800 border-2 border-slate-200 dark:border-slate-600">
                                <div
                                    class="w-20 h-20 rounded-full flex items-center justify-center text-white shadow-lg"
                                    style="background-color: {{ $color }};"
                                >
                                    <i class="bi {{ $icon }} text-4xl"></i>
                                </div>
                                <div class="text-center w-full">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-lg">{{ $name }}</h4>
                                    @if($description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $description }}</p>
                                    @endif
                                    <div class="flex flex-col gap-2 mt-3 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center justify-center gap-2">
                                            <i class="bi bi-target"></i>
                                            {{ $goal_frequency }}x por dia
                                        </span>
                                        @if($reminder_time)
                                            <span class="flex items-center justify-center gap-2">
                                                <i class="bi bi-clock"></i>
                                                {{ $reminder_time }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botões de Ação -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 space-y-3">
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            <i class="bi bi-check-circle mr-2"></i>
                            Criar Hábito
                        </span>
                        <span wire:loading>
                            <i class="bi bi-arrow-repeat animate-spin mr-2"></i>
                            Criando...
                        </span>
                    </button>

                    <a
                        href="{{ route('daily-habits.dashboard') }}"
                        class="block w-full px-6 py-3 bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-slate-600 transition-all text-center"
                    >
                        <i class="bi bi-x-circle mr-2"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <style>
        @keyframes bounce-in {
            0% { transform: scale(0.95); opacity: 0; }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-bounce-in {
            animation: bounce-in 0.3s ease-out;
        }
    </style>
</div>
