<div class="create-goal-page w-full bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 mobile-393-base" style="min-height: 100vh;">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/create-goal-ultrawide.css') }}">

    <x-modern-header
        :icon="'bi bi-bullseye'"
        :title="'🎯 Criar Nova Meta - ' . $board_name"
        :subtitle="'Adicione uma nova meta ao seu quadro'"
        :breadcrumb="[
            ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => route('dashboard')],
            ['icon' => 'bi bi-bullseye', 'label' => 'Metas', 'url' => route('goals.dashboard')],
            ['icon' => 'bi bi-kanban', 'label' => $board_name, 'url' => route('goals.board', ['boardId' => $boardId])],
            ['label' => 'Criar Meta']
        ]"
    />

    <div class="max-w-5xl mx-auto px-6 py-8">
        <!-- Mensagens -->
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl shadow-lg">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-600 rounded-lg">
                <p class="font-semibold text-red-800 dark:text-red-300 mb-2">⚠️ Erros:</p>
                <ul class="list-disc list-inside text-red-700 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário em Grid --><form wire:submit.prevent="createGoal" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Coluna Principal (2/3) --><div class="lg:col-span-2 space-y-6">

                <!-- Seção: Informações Básicas --><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-info-circle text-purple-500"></i>
                        Informações Básicas
                    </h3>

                    <div class="space-y-4">
                        <!-- Nome da Meta -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nome da Meta *
                            </label>
                            <input
                                type="text"
                                wire:model="name"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Ex: Aumentar vendas em 20%"
                            >
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Descrição
                            </label>
                            <textarea
                                wire:model="description"
                                rows="3"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                placeholder="Descreva os detalhes da sua meta..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Seção: Classificação --><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-tags text-blue-500"></i>
                        Classificação
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Lista -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Lista *
                            </label>
                            <select wire:model="list_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Selecione...</option>
                                @foreach($lists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }}</option>
                                @endforeach
                            </select>
                            @error('list_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Prioridade *
                            </label>
                            <select wire:model="priority" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="baixa">🟢 Baixa</option>
                                <option value="media">🟡 Média</option>
                                <option value="alta">🟠 Alta</option>
                                <option value="urgente">🔴 Urgente</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Status *
                            </label>
                            <select wire:model="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="pendente">⏳ Pendente</option>
                                <option value="em_andamento">🔄 Em Andamento</option>
                                <option value="concluido">✅ Concluído</option>
                                <option value="arquivado">📦 Arquivado</option>
                            </select>
                        </div>

                        <!-- Categoria -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Categoria
                            </label>
                            <select wire:model="category_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Nenhuma</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id_category }}">{{ $category->name_category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cor -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                Cor da Meta *
                            </label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($availableColors as $colorHex => $colorName)
                                    <button
                                        type="button"
                                        wire:click="$set('cor', '{{ $colorHex }}')"
                                        class="flex flex-col items-center p-2 rounded-xl border-2 transition-all hover:scale-105 {{ $cor === $colorHex ? 'border-slate-800 dark:border-slate-400 shadow-lg' : 'border-slate-200 dark:border-slate-600' }}"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-full {{ $cor === $colorHex ? 'ring-4 ring-offset-2 ring-slate-800 dark:ring-slate-400' : '' }}"
                                            style="background-color: {{ $colorHex }};"
                                        ></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $colorName }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Valores e Progresso --><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-graph-up text-green-500"></i>
                        Valores e Progresso
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Valor da Meta -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Valor da Meta (R$)
                            </label>
                            <input
                                type="number"
                                wire:model="valor_meta"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                placeholder="0.00"
                            >
                        </div>

                        <!-- Cofrinho -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Cofrinho
                            </label>
                            <select wire:model="cofrinho_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="">Nenhum</option>
                                @foreach($cofrinhos as $cofrinho)
                                    <option value="{{ $cofrinho->id }}">{{ $cofrinho->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Progresso -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Progresso (%) - {{ $progresso }}%
                            </label>
                            <input
                                type="range"
                                wire:model="progresso"
                                min="0"
                                max="100"
                                class="w-full h-2 bg-slate-200 dark:bg-slate-600 rounded-lg appearance-none cursor-pointer accent-purple-600"
                            >
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>0%</span>
                                <span>50%</span>
                                <span>100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Prazo e Recorrência --><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-calendar-event text-orange-500"></i>
                        Prazo e Recorrência
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Data de Vencimento -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Data de Vencimento
                            </label>
                            <input
                                type="date"
                                wire:model="data_vencimento"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                        </div>

                        <!-- Período -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Período *
                            </label>
                            <select wire:model="periodo" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <option value="diario">📅 Diário</option>
                                <option value="semanal">📆 Semanal</option>
                                <option value="mensal">🗓️ Mensal</option>
                                <option value="trimestral">📊 Trimestral</option>
                                <option value="semestral">📈 Semestral</option>
                                <option value="anual">📋 Anual</option>
                                <option value="custom">🎯 Personalizado</option>
                            </select>
                        </div>

                        <!-- Recorrência Dia -->
                        @if($periodo !== 'custom')
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Dia da Recorrência
                                </label>
                                <input
                                    type="number"
                                    wire:model="recorrencia_dia"
                                    min="1"
                                    max="31"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                    placeholder="Dia do mês (1-31)"
                                >
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Escolha o dia do mês para repetir esta meta</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (1/3) - Actions --><div class="lg:col-span-1 space-y-6">

                <!-- Botões de Ação -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 space-y-3 lg:sticky lg:top-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <i class="bi bi-gear text-indigo-500"></i>
                        Ações
                    </h3>

                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            <i class="bi bi-check-circle mr-2"></i>Criar Meta
                        </span>
                        <span wire:loading>
                            <i class="bi bi-arrow-repeat animate-spin mr-2"></i>Criando...
                        </span>
                    </button>

                    <a
                        href="{{ route('goals.board', ['boardId' => $boardId]) }}"
                        class="block w-full px-6 py-3 bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-slate-600 transition-all text-center"
                    >
                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                    </a>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            <i class="bi bi-info-circle mr-1"></i>
                            Preencha todos os campos obrigatórios (*) antes de salvar.
                        </p>
                    </div>
                </div>

                <!-- Dicas Rápidas -->
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800">
                    <h4 class="font-bold text-purple-800 dark:text-purple-300 mb-3 flex items-center gap-2">
                        <i class="bi bi-lightbulb"></i>
                        Dicas
                    </h4>
                    <ul class="space-y-2 text-sm text-purple-700 dark:text-purple-300">
                        <li class="flex gap-2">
                            <i class="bi bi-check2 mt-0.5 flex-shrink-0"></i>
                            <span>Use cores para organizar visualmente suas metas</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="bi bi-check2 mt-0.5 flex-shrink-0"></i>
                            <span>Defina prazos realistas para manter o foco</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="bi bi-check2 mt-0.5 flex-shrink-0"></i>
                            <span>Vincule um cofrinho para acompanhar valores</span>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>


