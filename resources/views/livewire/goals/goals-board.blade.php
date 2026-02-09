<div class="w-full min-h-screen flex flex-col bg-gradient-to-br from-[#0079BF] to-[#0079BFdd] dark:from-[#1a2634] dark:to-[#1a2634dd]"
    style="background: linear-gradient(135deg, {{ $board->background_color ?? '#0079BF' }} 0%, {{ $board->background_color ?? '#0079BF' }}dd 100%);">


    <x-modern-header :icon="'bi bi-kanban'" :title="$board->name" :subtitle="$board->description ?? 'Organize suas metas'" :breadcrumb="[
        ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => route('dashboard')],
        ['icon' => 'bi bi-bullseye', 'label' => 'Metas', 'url' => route('goals.dashboard')],
        ['label' => $board->name],
    ]" :actions="view('livewire.goals.partials.board-actions', compact('boardId'))" />

    <!-- Kanban Board - Scrollable Horizontal -->
    <div class="overflow-x-auto p-4 flex-1 scrollbar-thin scrollbar-thumb-blue-200 scrollbar-track-transparent">
        <div class="flex gap-6 h-full" style="min-height: calc(100vh - 140px);">

            @foreach ($lists as $list)
                <!-- Lista/Coluna -->
                <div class="flex-shrink-0 w-80 bg-slate-100 dark:bg-slate-800 rounded-2xl shadow-xl flex flex-col transition-transform duration-200 hover:-translate-y-1 hover:shadow-2xl border border-transparent hover:border-blue-400"
                    style="max-height: calc(100vh - 160px);" data-list-id="{{ $list['id'] }}">

                    <!-- Header da Lista -->
                    <div class="p-3 border-b-2 border-slate-200 dark:border-slate-700 bg-gradient-to-r from-white/80 to-slate-100/80 dark:from-slate-800/80 dark:to-slate-900/80 rounded-t-2xl"
                        style="border-color: {{ $list['color'] }}">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full border-2 border-white shadow" style="background-color: {{ $list['color'] }}"></div>
                                {{ $list['name'] }}
                                <span class="text-xs bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                    {{ count($list['goals']) }}
                                </span>
                            </h3>
                            <button
                                class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-full p-1 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Cards da Lista (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3 goal-cards-container scrollbar-thin scrollbar-thumb-blue-100 scrollbar-track-transparent"
                        data-list-id="{{ $list['id'] }}">

                        @foreach ($list['goals'] as $goal)
                            <!-- Card da Meta -->
                            <div class="bg-white dark:bg-slate-700 rounded-xl shadow-md hover:shadow-2xl transition-all duration-200 cursor-pointer goal-card border border-transparent hover:border-blue-400"
                                data-goal-id="{{ $goal['id'] }}"
                                onclick="window.location.href='{{ route('goals.edit', ['goalId' => $goal['id']]) }}'">

                                <!-- Labels -->
                                @if (!empty($goal['labels']))
                                    <div class="flex flex-wrap gap-1 p-2 pb-0">
                                        @foreach ($goal['labels'] as $label)
                                            <span class="px-2 py-0.5 text-xs font-medium text-white rounded shadow-sm"
                                                style="background-color: {{ $label['color'] ?? '#6B7280' }}">
                                                {{ $label['name'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Conteúdo do Card -->
                                <div class="p-3">
                                    <h4 class="font-semibold text-slate-900 dark:text-white mb-2 line-clamp-2">
                                        {{ $goal['title'] }}
                                    </h4>

                                    @if ($goal['description'])
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-2 line-clamp-2">
                                            {{ Str::limit($goal['description'], 80) }}
                                        </p>
                                    @endif

                                    <!-- Progress Bar -->
                                    @if ($goal['progresso'] > 0)
                                        <div class="mb-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Progresso</span>
                                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $goal['progresso'] }}%</span>
                                            </div>
                                            <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full transition-all duration-500 {{ $goal['progresso'] >= 100 ? 'bg-green-500' : ($goal['progresso'] >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                                    style="width: {{ min(100, $goal['progresso']) }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Badges e Ícones -->
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-2">
                                            <!-- Prioridade -->
                                            <span class="text-xs px-2 py-0.5 rounded select-none
                                                {{ $goal['prioridade'] === 'urgente' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}
                                                {{ $goal['prioridade'] === 'alta' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : '' }}
                                                {{ $goal['prioridade'] === 'media' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : '' }}
                                                {{ $goal['prioridade'] === 'baixa' ? 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400' : '' }}">
                                                {{ ucfirst($goal['prioridade']) }}
                                            </span>

                                            <!-- Data Limite -->
                                            @if ($goal['data_vencimento'])
                                                <span class="text-xs flex items-center gap-1 select-none
                                                    {{ $goal['is_atrasada'] ? 'text-red-600 dark:text-red-400 font-bold' : 'text-slate-600 dark:text-slate-400' }}">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ \Carbon\Carbon::parse($goal['data_vencimento'])->format('d/m') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Ícones de Informação -->
                                        <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                            @if ($goal['checklists_count'] > 0)
                                                <span class="flex items-center gap-1 select-none">
                                                    <i class="bi bi-check2-square"></i>
                                                    {{ $goal['checklists_count'] }}
                                                </span>
                                            @endif

                                            @if ($goal['comments_count'] > 0)
                                                <span class="flex items-center gap-1 select-none">
                                                    <i class="bi bi-chat-dots"></i>
                                                    {{ $goal['comments_count'] }}
                                                </span>
                                            @endif

                                            @if ($goal['attachments_count'] > 0)
                                                <span class="flex items-center gap-1 select-none">
                                                    <i class="bi bi-paperclip"></i>
                                                    {{ $goal['attachments_count'] }}
                                                </span>
                                            @endif

                                            @if ($goal['cofrinho'])
                                                <i class="bi bi-piggy-bank text-amber-500" title="Vinculado a cofrinho"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Botão Adicionar Meta -->
                        <a href="{{ route('goals.create', ['boardId' => $boardId]) }}"
                            class="w-full py-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 border border-dashed border-blue-300 mt-2">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Meta
                        </a>
                    </div>
                </div>
            @endforeach

            <!-- Botão Adicionar Nova Lista -->
            <div class="flex-shrink-0 w-80">
                <button wire:click="openCreateListModal"
                    class="w-full py-4 bg-white/30 hover:bg-white/60 dark:bg-slate-900/30 dark:hover:bg-slate-900/60 backdrop-blur-sm text-blue-700 dark:text-white font-medium rounded-2xl border-2 border-dashed border-blue-400 hover:border-blue-600 transition-all duration-200 flex items-center justify-center gap-2 shadow-none">
                    <i class="bi bi-plus-lg"></i>
                    Adicionar Lista
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Criar Lista -->
    @if ($showCreateListModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="$set('showCreateListModal', false)">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 animate-fade-in">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Nova Lista</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nome da Lista</label>
                        <input type="text" wire:model="newListName"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: A Fazer, Em Progresso, Concluído">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Cor</label>
                        <div class="flex gap-2 flex-wrap">
                            @foreach (['#0079BF', '#61BD4F', '#F2D600', '#FF9F1A', '#EB5A46', '#C377E0', '#00C2E0', '#51E898'] as $color)
                                <button type="button" wire:click="$set('newListColor', '{{ $color }}')"
                                    class="w-10 h-10 rounded-lg transition-all duration-200 border-2 {{ $newListColor === $color ? 'ring-4 ring-offset-2 ring-blue-500 border-blue-500' : 'border-transparent' }}"
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
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-2 animate-fade-in">
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
                document.querySelectorAll('.goal-cards-container').forEach(function(el) {
                    new Sortable(el, {
                        group: 'goals',
                        animation: 180,
                        ghostClass: 'opacity-50',
                        handle: '.goal-card',
                        dragClass: 'ring-4 ring-blue-400',
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
        <style>
            /* Custom scrollbar for Trello-like look */
            .scrollbar-thin {
                scrollbar-width: thin;
                scrollbar-color: #60a5fa #e0e7ef;
            }
            .scrollbar-thin::-webkit-scrollbar {
                height: 8px;
                width: 8px;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #60a5fa;
                border-radius: 8px;
            }
            .scrollbar-thin::-webkit-scrollbar-track {
                background: #e0e7ef;
            }
            /* Animation for fade-in */
            .animate-fade-in {
                animation: fadeIn 0.4s ease;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: none; }
            }
        </style>
    @endpush
</div>
