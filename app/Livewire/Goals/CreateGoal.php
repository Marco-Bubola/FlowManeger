<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use App\Models\GoalBoard;
use App\Models\GoalList;
use App\Models\Category;
use App\Models\Cofrinho;
use App\Services\GoalService;
use Livewire\Component;

class CreateGoal extends Component
{
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

    public function mount($boardId = null)
    {
        if ($boardId) {
            $board = GoalBoard::where('id', $boardId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $this->boardId = $board->id;
            $this->board_name = $board->name;
            $this->lists = GoalList::where('board_id', $boardId)->get();
        }

        $this->categories = Category::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();

        $this->cofrinhos = Cofrinho::where('user_id', auth()->id())
            ->get();
    }

    public function createGoal()
    {
        try {
            \Log::info('[CreateGoal] Iniciando criação');

            $validated = $this->validate([
                'boardId' => 'required|exists:goal_boards,id',
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
                'list_id.required' => 'Selecione uma lista para a meta',
            ]);

            // Usar GoalService para criar a meta
            $goalService = app(GoalService::class);
            $goal = $goalService->create([
                'list_id' => $this->list_id,
                'title' => $this->name,
                'description' => $this->description,
                'prioridade' => $this->priority,
                'data_vencimento' => $this->data_vencimento ?: null,
                'valor_meta' => $this->valor_meta ?: null,
                'progresso' => $this->progresso ?? 0,
                'cofrinho_id' => $this->cofrinho_id ?: null,
                'category_id' => $this->category_id ?: null,
                'periodo' => $this->periodo,
                'recorrencia_dia' => $this->recorrencia_dia,
                'cor' => $this->cor,
            ], auth()->id());

            \Log::info('[CreateGoal] Meta criada', ['goal_id' => $goal->id]);

            session()->flash('message', '✅ Meta criada com sucesso!');
            return redirect()->route('goals.board', ['boardId' => $this->boardId]);

        } catch (\Exception $e) {
            \Log::error('[CreateGoal] Erro ao criar', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', '❌ Erro ao criar meta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.goals.create-goal');
    }
}
