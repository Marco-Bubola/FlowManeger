<div class="w-full bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
    style="min-height: 100vh;">

    <x-modern-header :icon="'bi bi-pencil'" :title="'📝 Editar Meta - ' . $board_name" :subtitle="'Atualize as informações da sua meta'" :breadcrumb="[
        ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => route('dashboard')],
        ['icon' => 'bi bi-bullseye', 'label' => 'Metas', 'url' => route('goals.dashboard')],
        [
            'icon' => 'bi bi-kanban',
            'label' => $board_name,
            'url' => $boardId ? route('goals.board', ['boardId' => $boardId]) : '#',
        ],
        ['label' => 'Editar Meta'],
    ]" />

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
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form wire:submit.prevent="updateGoal">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Nome da Meta -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nome da Meta *
                        </label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Ex: Aumentar vendas em 20%">
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Lista -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Lista *
                        </label>
                        <select wire:model="list_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="">Selecione...</option>
                            @foreach ($lists as $list)
                                <option value="{{ $list->id }}">{{ $list->name }}</option>
                            @endforeach
                        </select>
                        @error('list_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Prioridade -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Prioridade *
                        </label>
                        <select wire:model="priority"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="baixa">🟢 Baixa</option>
                            <option value="media">🟡 Média</option>
                            <option value="alta">🟠 Alta</option>
                            <option value="urgente">🔴 Urgente</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status *
                        </label>
                        <select wire:model="status"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="pendente">⏳ Pendente</option>
                            <option value="em_andamento">🔄 Em Andamento</option>
                            <option value="concluido">✅ Concluído</option>
                            <option value="arquivado">📦 Arquivado</option>
                        </select>
                    </div>

                    <!-- Data de Vencimento -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Data de Vencimento
                        </label>
                        <input type="date" wire:model="data_vencimento"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Período -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Período *
                        </label>
                        <select wire:model="periodo"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="diario">📅 Diário</option>
                            <option value="semanal">📆 Semanal</option>
                            <option value="mensal">🗓️ Mensal</option>
                            <option value="trimestral">📊 Trimestral</option>
                            <option value="semestral">📈 Semestral</option>
                            <option value="anual">📋 Anual</option>
                            <option value="custom">🎯 Personalizado</option>
                        </select>
                    </div>

                    <!-- Valor da Meta -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Valor da Meta (R$)
                        </label>
                        <input type="number" wire:model="valor_meta" step="0.01" min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                            placeholder="0.00">
                    </div>

                    <!-- Progresso -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Progresso (%) - {{ $progresso }}%
                        </label>
                        <input type="range" wire:model="progresso" min="0" max="100" class="w-full">
                    </div>

                    <!-- Cofrinho -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Cofrinho
                        </label>
                        <select wire:model="cofrinho_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="">Nenhum</option>
                            @foreach ($cofrinhos as $cofrinho)
                                <option value="{{ $cofrinho->id }}">{{ $cofrinho->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Categoria
                        </label>
                        <select wire:model="category_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="">Nenhuma</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id_category }}">{{ $category->name_category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Recorrência Dia -->
                    @if ($periodo !== 'custom')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Dia da Recorrência
                            </label>
                            <input type="number" wire:model="recorrencia_dia" min="1" max="31"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                placeholder="Dia do mês (1-31)">
                        </div>
                    @endif

                    <!-- Cor -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Cor da Meta *
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($availableColors as $colorHex => $colorName)
                                <button type="button" wire:click="$set('cor', '{{ $colorHex }}')"
                                    class="flex flex-col items-center p-3 rounded-xl border-2 transition-all hover:scale-105 {{ $cor === $colorHex ? 'border-gray-800 shadow-lg' : 'border-gray-200' }}">
                                    <div class="w-12 h-12 rounded-full {{ $cor === $colorHex ? 'ring-4 ring-offset-2 ring-gray-800' : '' }}"
                                        style="background-color: {{ $colorHex }};"></div>
                                    <span class="text-xs text-gray-600 mt-1">{{ $colorName }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Descrição
                        </label>
                        <textarea wire:model="description" rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                            placeholder="Descreva os detalhes da sua meta..."></textarea>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 mt-8">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-check-circle mr-2"></i>Salvar Alterações
                        </span>
                        <span wire:loading>
                            <i class="bi bi-arrow-repeat animate-spin mr-2"></i>Salvando...
                        </span>
                    </button>

                    <button type="button" wire:click="confirmDelete"
                        class="px-6 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-rose-700 transition-all shadow-lg">
                        <i class="bi bi-trash mr-2"></i>Arquivar
                    </button>

                    <a href="{{ $boardId ? route('goals.board', ['boardId' => $boardId]) : '#' }}"
                        class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all">
                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-bounce-in">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Confirmar Arquivamento</h3>
                </div>

                <p class="text-gray-600 mb-6">
                    Tem certeza que deseja arquivar esta meta? Ela será movida para o status arquivado.
                </p>

                <div class="flex gap-3">
                    <button wire:click="deleteGoal"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-rose-700 transition-all"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-check-circle mr-2"></i>Sim, Arquivar
                        </span>
                        <span wire:loading>
                            <i class="bi bi-arrow-repeat animate-spin mr-2"></i>Arquivando...
                        </span>
                    </button>
                    <button wire:click="$set('showDeleteModal', false)"
                        class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all">
                        <i class="bi bi-x-circle mr-2"></i>Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif


<style>
    @keyframes bounce-in {
        0% {
            transform: scale(0.95);
            opacity: 0;
        }

        50% {
            transform: scale(1.02);
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-bounce-in {
        animation: bounce-in 0.3s ease-out;
    }
</style>
</div>
