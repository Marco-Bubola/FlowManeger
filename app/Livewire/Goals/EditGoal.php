<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use App\Models\GoalBoard;
use App\Models\GoalList;
use App\Models\Category;
use App\Models\Cofrinho;
use Livewire\Component;

class EditGoal extends Component
{
    public $goalId;
    public $boardId;
    public $board_name = '';
    public $name = '';
    public $description = '';
    public $list_id = '';
    public $priority = 'media';
    public $status = 'pendente';
    public $data_vencimento = '';
    public $valor_meta = '';
    public $progresso = 0;
    public $cofrinho_id = '';
    public $category_id = '';
    public $periodo = 'custom';
    public $recorrencia_dia = null;
    public $cor = '#3B82F6';
    public $showDeleteModal = false;

    public $lists = [];
    public $categories = [];
    public $cofrinhos = [];

    public $availableColors = [
        '#3B82F6' => 'Azul',
        '#10B981' => 'Verde',
        '#F59E0B' => 'Laranja',
        '#EF4444' => 'Vermelho',
        '#8B5CF6' => 'Roxo',
        '#EC4899' => 'Rosa',
        '#06B6D4' => 'Ciano',
        '#84CC16' => 'Lima',
    ];

    public function mount($goalId)
    {
        $goal = Goal::where('id', $goalId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->goalId = $goal->id;
        $this->boardId = $goal->board_id;
        $this->name = $goal->name;
        $this->description = $goal->description;
        $this->list_id = $goal->list_id;
        $this->priority = $goal->priority;
        $this->status = $goal->status;
        $this->data_vencimento = $goal->data_vencimento ? $goal->data_vencimento->format('Y-m-d') : '';
        $this->valor_meta = $goal->valor_meta;
        $this->progresso = $goal->progresso;
        $this->cofrinho_id = $goal->cofrinho_id;
        $this->category_id = $goal->category_id;
        $this->periodo = $goal->periodo;
        $this->recorrencia_dia = $goal->recorrencia_dia;
        $this->cor = $goal->cor;

        $board = GoalBoard::find($this->boardId);
        $this->board_name = $board->name;

        $this->lists = GoalList::where('board_id', $this->boardId)->get();

        $this->categories = Category::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();

        $this->cofrinhos = Cofrinho::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();
    }

    public function updateGoal()
    {
        try {
            \Log::info('[EditGoal] Iniciando atualização', ['goal_id' => $this->goalId]);

            $validated = $this->validate([
                'name' => 'required|min:3|max:255',
                'description' => 'nullable',
                'list_id' => 'required|exists:goal_lists,id',
                'priority' => 'required|in:baixa,media,alta,urgente',
                'status' => 'required|in:pendente,em_andamento,concluido,arquivado',
                'data_vencimento' => 'nullable|date',
                'valor_meta' => 'nullable|numeric|min:0',
                'progresso' => 'nullable|integer|min:0|max:100',
                'cofrinho_id' => 'nullable|exists:cofrinhos,id',
                'category_id' => 'nullable|exists:category,id_category',
                'periodo' => 'required|in:diario,semanal,mensal,trimestral,semestral,anual,custom',
                'recorrencia_dia' => 'nullable|integer|min:1|max:31',
                'cor' => 'required',
            ], [
                'name.required' => 'O nome da meta é obrigatório',
                'name.min' => 'O nome deve ter pelo menos 3 caracteres',
            ]);

            $goal = Goal::where('id', $this->goalId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $goal->update([
                'name' => $this->name,
                'description' => $this->description,
                'list_id' => $this->list_id,
                'priority' => $this->priority,
                'status' => $this->status,
                'data_vencimento' => $this->data_vencimento ?: null,
                'valor_meta' => $this->valor_meta ?: null,
                'progresso' => $this->progresso ?? 0,
                'cofrinho_id' => $this->cofrinho_id ?: null,
                'category_id' => $this->category_id ?: null,
                'periodo' => $this->periodo,
                'recorrencia_dia' => $this->recorrencia_dia,
                'cor' => $this->cor,
            ]);

            \Log::info('[EditGoal] Meta atualizada', ['goal_id' => $goal->id]);

            session()->flash('message', '✅ Meta atualizada com sucesso!');
            return redirect()->route('goals.board', ['boardId' => $this->boardId]);

        } catch (\Exception $e) {
            \Log::error('[EditGoal] Erro ao atualizar', [
                'message' => $e->getMessage(),
                'goal_id' => $this->goalId,
            ]);
            session()->flash('error', '❌ Erro ao atualizar meta: ' . $e->getMessage());
        }
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function deleteGoal()
    {
        try {
            \Log::info('[EditGoal] Iniciando exclusão', ['goal_id' => $this->goalId]);

            $goal = Goal::where('id', $this->goalId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Arquivar a meta ao invés de excluir
            $goal->update(['status' => 'arquivado']);

            \Log::info('[EditGoal] Meta arquivada', ['goal_id' => $goal->id]);

            session()->flash('message', '🗑️ Meta arquivada com sucesso!');
            return redirect()->route('goals.board', ['boardId' => $this->boardId]);

        } catch (\Exception $e) {
            \Log::error('[EditGoal] Erro ao excluir', [
                'message' => $e->getMessage(),
                'goal_id' => $this->goalId,
            ]);
            session()->flash('error', '❌ Erro ao arquivar meta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.goals.edit-goal');
    }
}
