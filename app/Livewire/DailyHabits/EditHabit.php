<?php

namespace App\Livewire\DailyHabits;

use App\Models\DailyHabit;
use App\Services\HabitService;
use Livewire\Component;

class EditHabit extends Component
{
    public $habitId;
    public $name = '';
    public $description = '';
    public $icon = 'bi-check-circle';
    public $color = '#3B82F6';
    public $goal_frequency = 1;
    public $reminder_time = '';
    public $showDeleteModal = false;

    public $availableIcons = [
        'bi-check-circle' => 'Check',
        'bi-heart' => 'Coração',
        'bi-droplet' => 'Água',
        'bi-trophy' => 'Troféu',
        'bi-book' => 'Livro',
        'bi-bicycle' => 'Exercício',
        'bi-moon-stars' => 'Sono',
        'bi-apple' => 'Alimentação',
        'bi-clipboard-check' => 'Tarefas',
        'bi-chat-dots' => 'Comunicação',
        'bi-brush' => 'Criatividade',
        'bi-graph-up' => 'Produtividade',
    ];

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

    protected $habitService;

    public function boot(HabitService $habitService)
    {
        $this->habitService = $habitService;
    }

    public function mount($habitId)
    {
        $habit = DailyHabit::where('id', $habitId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->habitId = $habit->id;
        $this->name = $habit->name;
        $this->description = $habit->description;
        $this->icon = $habit->icon;
        $this->color = $habit->color;
        $this->goal_frequency = $habit->goal_frequency;
        $this->reminder_time = $habit->reminder_time ? $habit->reminder_time->format('H:i') : '';
    }

    public function updateHabit()
    {
        try {
            \Log::info('[EditHabit] Iniciando atualização', ['habit_id' => $this->habitId]);

            $validated = $this->validate([
                'name' => 'required|min:3|max:255',
                'description' => 'nullable|max:500',
                'icon' => 'required|in:' . implode(',', array_keys($this->availableIcons)),
                'color' => 'required',
                'goal_frequency' => 'required|integer|min:1|max:10',
                'reminder_time' => 'nullable|date_format:H:i',
            ], [
                'name.required' => 'O nome do hábito é obrigatório',
                'name.min' => 'O nome deve ter pelo menos 3 caracteres',
            ]);

            $habit = DailyHabit::where('id', $this->habitId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $habit->update([
                'name' => $this->name,
                'description' => $this->description,
                'icon' => $this->icon,
                'color' => $this->color,
                'goal_frequency' => $this->goal_frequency,
                'reminder_time' => $this->reminder_time ?: null,
            ]);

            \Log::info('[EditHabit] Hábito atualizado', ['habit_id' => $habit->id]);

            session()->flash('message', '✅ Hábito atualizado com sucesso!');
            return redirect()->route('daily-habits.dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[EditHabit] Erro ao atualizar', [
                'message' => $e->getMessage(),
                'habit_id' => $this->habitId,
            ]);
            session()->flash('error', '❌ Erro ao atualizar hábito: ' . $e->getMessage());
        }
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function deleteHabit()
    {
        try {
            \Log::info('[EditHabit] Iniciando exclusão', ['habit_id' => $this->habitId]);

            $habit = DailyHabit::where('id', $this->habitId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Soft delete - apenas desativa
            $habit->update(['is_active' => false]);

            \Log::info('[EditHabit] Hábito arquivado', ['habit_id' => $habit->id]);

            session()->flash('message', '🗑️ Hábito arquivado com sucesso!');
            return redirect()->route('daily-habits.dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[EditHabit] Erro ao excluir', [
                'message' => $e->getMessage(),
                'habit_id' => $this->habitId,
            ]);
            session()->flash('error', '❌ Erro ao arquivar hábito: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.daily-habits.edit-habit');
    }
}
