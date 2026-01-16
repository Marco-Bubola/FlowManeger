<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use App\Models\GoalBoard;
use App\Models\GoalList;
use App\Models\GoalActivity;
use App\Models\Cofrinho;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GoalsBoard extends Component
{
    public $boardId;
    public $board;
    public $lists = [];

    // Para os selects
    public $cofrinhos = [];
    public $categories = [];

    // Modais
    public $showCreateListModal = false;
    public $showCreateGoalModal = false;
    public $showEditGoalModal = false;
    public $showGoalDetailModal = false;
    public $showBoardSettingsModal = false;

    // Dados dos formulários
    public $newListName = '';
    public $newListColor = '#0079BF';
    public $selectedListId = null;
    public $selectedGoalId = null;

    // Goal form
    public $goalTitle = '';
    public $goalDescription = '';
    public $goalPeriodo = 'mensal';
    public $goalPrioridade = 'media';
    public $goalDataInicio = null;
    public $goalDataLimite = null;
    public $goalValorMeta = null;
    public $goalCofrinhoId = null;
    public $goalCategoryId = null;
    public $goalLabels = [];

    protected $listeners = [
        'goalMoved' => 'moveGoal',
        'listReordered' => 'reorderList',
        'refreshBoard' => '$refresh',
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->loadBoard();
        $this->loadFormOptions();
    }

    public function loadFormOptions()
    {
        $userId = Auth::id();

        // Carregar cofrinhos do usuário
        $this->cofrinhos = Cofrinho::where('user_id', $userId)
            ->orderBy('nome')
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'nome' => $c->nome,
                ];
            })->toArray();

        // Carregar categorias do usuário
        $this->categories = Category::where('user_id', $userId)
            ->orderBy('name')
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id_category,
                    'name' => $c->name,
                ];
            })->toArray();
    }

    public function loadBoard()
    {
        $this->board = GoalBoard::with(['lists' => function($query) {
            $query->orderBy('order');
        }])
        ->where('id', $this->boardId)
        ->where('user_id', Auth::id())
        ->firstOrFail();

        $this->lists = $this->board->lists->map(function($list) {
            return [
                'id' => $list->id,
                'name' => $list->name,
                'color' => $list->color,
                'order' => $list->order,
                'goals' => $list->goals()
                    ->with(['cofrinho', 'category', 'checklists'])
                    ->orderBy('order')
                    ->get()
                    ->map(function($goal) {
                        return [
                            'id' => $goal->id,
                            'title' => $goal->title,
                            'description' => $goal->description,
                            'progresso' => $goal->progresso_percentual,
                            'prioridade' => $goal->prioridade,
                            'periodo' => $goal->periodo,
                            'data_vencimento' => $goal->data_vencimento,
                            'is_atrasada' => $goal->is_atrasada,
                            'labels' => $goal->labels ?? [],
                            'cofrinho' => $goal->cofrinho ? [
                                'id' => $goal->cofrinho->id,
                                'nome' => $goal->cofrinho->nome,
                            ] : null,
                            'category' => $goal->category ? [
                                'id' => $goal->category->id_category,
                                'name' => $goal->category->name_category,
                            ] : null,
                            'checklists_count' => $goal->checklists->count(),
                            'comments_count' => $goal->comments()->count(),
                            'attachments_count' => $goal->attachments()->count(),
                            'order' => $goal->order,
                        ];
                    }),
            ];
        })->toArray();
    }

    public function openCreateListModal()
    {
        $this->showCreateListModal = true;
        $this->newListName = '';
        $this->newListColor = '#0079BF';
    }

    public function createList()
    {
        $this->validate([
            'newListName' => 'required|min:2|max:100',
        ]);

        $maxOrder = GoalList::where('board_id', $this->boardId)->max('order') ?? 0;

        GoalList::create([
            'board_id' => $this->boardId,
            'name' => $this->newListName,
            'color' => $this->newListColor,
            'order' => $maxOrder + 1,
        ]);

        $this->showCreateListModal = false;
        $this->loadBoard();

        session()->flash('message', 'Lista criada com sucesso!');
    }

    public function openCreateGoalModal($listId)
    {
        $this->selectedListId = $listId;
        $this->showCreateGoalModal = true;
        $this->resetGoalForm();
    }

    public function createGoal()
    {
        try {
            \Log::info('[GoalsBoard] Iniciando createGoal', [
                'user_id' => Auth::id(),
                'list_id' => $this->selectedListId,
                'title' => $this->goalTitle,
            ]);

            $this->validate([
                'goalTitle' => 'required|min:3|max:255',
                'goalPeriodo' => 'required|in:diario,semanal,mensal,trimestral,semestral,anual,custom',
                'goalPrioridade' => 'required|in:baixa,media,alta,urgente',
            ], [
                'goalTitle.required' => 'O título da meta é obrigatório',
                'goalTitle.min' => 'O título deve ter pelo menos 3 caracteres',
                'goalPeriodo.required' => 'Selecione um período',
                'goalPrioridade.required' => 'Selecione uma prioridade',
            ]);

            \Log::info('[GoalsBoard] Validação passou');

            $maxOrder = Goal::where('list_id', $this->selectedListId)->max('order') ?? 0;

            $goal = Goal::create([
                'list_id' => $this->selectedListId,
                'user_id' => Auth::id(),
                'title' => $this->goalTitle,
                'description' => $this->goalDescription,
                'periodo' => $this->goalPeriodo,
                'prioridade' => $this->goalPrioridade,
                'data_inicio' => $this->goalDataInicio ?: now(),
                'data_vencimento' => $this->goalDataLimite,
                'valor_meta' => $this->goalValorMeta,
                'cofrinho_id' => $this->goalCofrinhoId,
                'category_id' => $this->goalCategoryId,
                'labels' => $this->goalLabels,
                'order' => $maxOrder + 1,
            ]);

            \Log::info('[GoalsBoard] Meta criada com sucesso', ['goal_id' => $goal->id]);

            // Log activity
            $goal->logActivity('created', 'Meta criada');

            // Se vinculada a cofrinho, atualizar progresso
            if ($this->goalCofrinhoId) {
                $goal->updateProgressoFromCofrinho();
            }

            $this->showCreateGoalModal = false;
            $this->loadBoard();

            session()->flash('message', '✨ Meta criada com sucesso!');
            session()->flash('message_type', 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[GoalsBoard] Erro de validação', [
                'errors' => $e->errors(),
                'data' => ['title' => $this->goalTitle]
            ]);
            session()->flash('message', '❌ Erro de validação. Verifique os campos.');
            session()->flash('message_type', 'error');
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[GoalsBoard] Erro ao criar meta', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', '❌ Erro ao criar meta: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function openGoalDetail($goalId)
    {
        $this->selectedGoalId = $goalId;
        $this->showGoalDetailModal = true;
    }

    public function moveGoal($goalId, $newListId, $newOrder)
    {
        $goal = Goal::findOrFail($goalId);

        if ($goal->user_id !== Auth::id()) {
            return;
        }

        $oldListId = $goal->list_id;

        $goal->update([
            'list_id' => $newListId,
            'order' => $newOrder,
        ]);

        // Log activity
        $newList = GoalList::find($newListId);
        $goal->logActivity('moved', "Meta movida para lista '{$newList->name}'");

        // Reordenar outras metas na lista antiga
        Goal::where('list_id', $oldListId)
            ->where('id', '!=', $goalId)
            ->orderBy('order')
            ->get()
            ->each(function($g, $index) {
                $g->update(['order' => $index]);
            });

        // Reordenar outras metas na lista nova
        Goal::where('list_id', $newListId)
            ->where('id', '!=', $goalId)
            ->orderBy('order')
            ->get()
            ->each(function($g, $index) use ($goalId, $newOrder) {
                if ($index >= $newOrder) {
                    $g->update(['order' => $index + 1]);
                }
            });

        $this->loadBoard();
    }

    public function deleteGoal($goalId)
    {
        $goal = Goal::where('id', $goalId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $goal->delete();

        $this->loadBoard();
        session()->flash('message', 'Meta excluída com sucesso!');
    }

    public function archiveGoal($goalId)
    {
        $goal = Goal::where('id', $goalId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $goal->update(['is_archived' => true]);
        $goal->logActivity('archived', 'Meta arquivada');

        $this->loadBoard();
        session()->flash('message', 'Meta arquivada com sucesso!');
    }

    public function completeGoal($goalId)
    {
        $goal = Goal::where('id', $goalId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $goal->markAsCompleted();

        $this->loadBoard();
        session()->flash('message', 'Parabéns! Meta concluída! 🎉');
    }

    private function resetGoalForm()
    {
        $this->goalTitle = '';
        $this->goalDescription = '';
        $this->goalPeriodo = 'mensal';
        $this->goalPrioridade = 'media';
        $this->goalDataInicio = now()->format('Y-m-d');
        $this->goalDataLimite = null;
        $this->goalValorMeta = null;
        $this->goalCofrinhoId = null;
        $this->goalCategoryId = null;
        $this->goalLabels = [];
    }

    public function render()
    {
        return view('livewire.goals.goals-board');
    }
}
