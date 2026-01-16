<div class="w-full" style="min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg, {{ $board->background_color ?? '#0079BF' }} 0%, {{ $board->background_color ?? '#0079BF' }}dd 100%);">

    <!-- Header do Board -->
    <div class="bg-black/20 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-full px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Título e Info -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('goals.dashboard') }}"
                       class="flex items-center justify-center w-10 h-10 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <i class="bi bi-arrow-left text-white text-lg"></i>
                    </a>

                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i class="bi bi-kanban"></i>
                            {{ $board->name }}
                            @if($board->is_favorite)
                            <i class="bi bi-star-fill text-amber-300 text-lg"></i>
                            @endif
                        </h1>
                        <p class="text-white/70 text-sm">{{ $board->description ?? 'Organize suas metas' }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button wire:click="openCreateListModal"
                            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all duration-200">
                        <i class="bi bi-plus-circle"></i>
                        <span class="font-medium">Nova Lista</span>
                    </button>

                    <button wire:click="$set('showBoardSettingsModal', true)"
                            class="flex items-center justify-center w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all duration-200">
                        <i class="bi bi-gear text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board - Scrollable Horizontal -->
    <div class="overflow-x-auto p-4 flex-1">
        <div class="flex gap-4 h-full" style="min-height: calc(100vh - 140px);">

            @foreach($lists as $list)
            <!-- Lista/Coluna -->
            <div class="flex-shrink-0 w-80 bg-slate-100 dark:bg-slate-800 rounded-xl shadow-lg flex flex-col"
                 style="max-height: calc(100vh - 160px);"
                 data-list-id="{{ $list['id'] }}">

                <!-- Header da Lista -->
                <div class="p-3 border-b-2 border-slate-200 dark:border-slate-700"
                     style="border-color: {{ $list['color'] }}">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $list['color'] }}"></div>
                            {{ $list['name'] }}
                            <span class="text-xs bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                {{ count($list['goals']) }}
                            </span>
                        </h3>
                        <button class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            <i class="bi bi-three-dots"></i>
                        </button>
                    </div>
                </div>

                <!-- Cards da Lista (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-3 goal-cards-container"
                     data-list-id="{{ $list['id'] }}">

                    @foreach($list['goals'] as $goal)
                    <!-- Card da Meta -->
                    <div class="bg-white dark:bg-slate-700 rounded-lg shadow-md hover:shadow-xl transition-all duration-200 cursor-pointer goal-card"
                         data-goal-id="{{ $goal['id'] }}"
                         onclick="window.location.href='{{ route('goals.edit', ['goalId' => $goal['id']]) }}'">>

                        <!-- Labels -->
                        @if(!empty($goal['labels']))
                        <div class="flex flex-wrap gap-1 p-2 pb-0">
                            @foreach($goal['labels'] as $label)
                            <span class="px-2 py-0.5 text-xs font-medium text-white rounded"
                                  style="background-color: {{ $label['color'] ?? '#6B7280' }}">
                                {{ $label['name'] ?? '' }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        <!-- Conteúdo do Card -->
                        <div class="p-3">
                            <h4 class="font-semibold text-slate-900 dark:text-white mb-2">
                                {{ $goal['title'] }}
                            </h4>

                            @if($goal['description'])
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-2 line-clamp-2">
                                {{ Str::limit($goal['description'], 80) }}
                            </p>
                            @endif

                            <!-- Progress Bar -->
                            @if($goal['progresso'] > 0)
                            <div class="mb-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Progresso</span>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $goal['progresso'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all duration-500
                                         {{ $goal['progresso'] >= 100 ? 'bg-green-500' : ($goal['progresso'] >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                         style="width: {{ min(100, $goal['progresso']) }}%">
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Badges e Ícones -->
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-2">
                                    <!-- Prioridade -->
                                    <span class="text-xs px-2 py-0.5 rounded
                                        {{ $goal['prioridade'] === 'urgente' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}
                                        {{ $goal['prioridade'] === 'alta' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : '' }}
                                        {{ $goal['prioridade'] === 'media' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : '' }}
                                        {{ $goal['prioridade'] === 'baixa' ? 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400' : '' }}">
                                        {{ ucfirst($goal['prioridade']) }}
                                    </span>

                                    <!-- Data Limite -->
                                    @if($goal['data_vencimento'])
                                    <span class="text-xs flex items-center gap-1
                                        {{ $goal['is_atrasada'] ? 'text-red-600 dark:text-red-400 font-bold' : 'text-slate-600 dark:text-slate-400' }}">
                                        <i class="bi bi-calendar3"></i>
                                        {{ \Carbon\Carbon::parse($goal['data_vencimento'])->format('d/m') }}
                                    </span>
                                    @endif
                                </div>

                                <!-- Ícones de Informação -->
                                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    @if($goal['checklists_count'] > 0)
                                    <span class="flex items-center gap-1">
                                        <i class="bi bi-check2-square"></i>
                                        {{ $goal['checklists_count'] }}
                                    </span>
                                    @endif

                                    @if($goal['comments_count'] > 0)
                                    <span class="flex items-center gap-1">
                                        <i class="bi bi-chat-dots"></i>
                                        {{ $goal['comments_count'] }}
                                    </span>
                                    @endif

                                    @if($goal['attachments_count'] > 0)
                                    <span class="flex items-center gap-1">
                                        <i class="bi bi-paperclip"></i>
                                        {{ $goal['attachments_count'] }}
                                    </span>
                                    @endif

                                    @if($goal['cofrinho'])
                                    <i class="bi bi-piggy-bank text-amber-500" title="Vinculado a cofrinho"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Botão Adicionar Meta -->
                    <a href="{{ route('goals.create', ['boardId' => $boardId]) }}"
                       class="w-full py-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="bi bi-plus-circle"></i>
                        Adicionar Meta
                    </a>
                </div>
            </div>
            @endforeach

            <!-- Botão Adicionar Nova Lista -->
            <div class="flex-shrink-0 w-80">
                <button wire:click="openCreateListModal"
                        class="w-full py-4 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-medium rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    Adicionar Lista
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Criar Lista -->
    @if($showCreateListModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         wire:click.self="$set('showCreateListModal', false)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Nova Lista</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nome da Lista</label>
                    <input type="text"
                           wire:model="newListName"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: A Fazer, Em Progresso, Concluído">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Cor</label>
                    <div class="flex gap-2">
                        @foreach(['#0079BF', '#61BD4F', '#F2D600', '#FF9F1A', '#EB5A46', '#C377E0', '#00C2E0', '#51E898'] as $color)
                        <button type="button"
                                wire:click="$set('newListColor', '{{ $color }}')"
                                class="w-10 h-10 rounded-lg transition-all duration-200 {{ $newListColor === $color ? 'ring-4 ring-offset-2 ring-blue-500' : '' }}"
                                style="background-color: {{ $color }}">
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showCreateListModal', false)"
                        class="flex-1 px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-all">
                    Cancelar
                </button>
                <button wire:click="createList"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all">
                    Criar Lista
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: Criar Meta -->
    <!-- Modal de criar meta removido - agora usa página separada -->

    <!-- Flash Messages -->
    @if (session()->has('message'))
    <div class="fixed bottom-4 right-4 z-50 animate-slide-in">
        <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-2">
            <i class="bi bi-check-circle text-xl"></i>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    </div>
    @endif

    <!-- SortableJS para Drag and Drop -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Sortable para cada lista
            document.querySelectorAll('.goal-cards-container').forEach(function(el) {
                new Sortable(el, {
                    group: 'goals',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    handle: '.goal-card',
                    onEnd: function(evt) {
                        const goalId = evt.item.dataset.goalId;
                        const newListId = evt.to.dataset.listId;
                        const newOrder = evt.newIndex;

                        @this.call('moveGoal', goalId, newListId, newOrder);
                    }
                });
            });
        });
    </script>
    @endpush
</div>
