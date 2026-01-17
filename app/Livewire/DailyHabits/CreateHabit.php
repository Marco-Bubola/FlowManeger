<?php

namespace App\Livewire\DailyHabits;

use App\Models\DailyHabit;
use App\Services\HabitService;
use App\Services\AchievementService;
use Livewire\Component;

class CreateHabit extends Component
{
    public $name = '';
    public $description = '';
    public $icon = 'bi-check-circle';
    public $color = '#3B82F6';
    public $goal_frequency = 1;
    public $reminder_time = '';

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
    protected $achievementService;

    public function boot(HabitService $habitService, AchievementService $achievementService)
    {
        $this->habitService = $habitService;
        $this->achievementService = $achievementService;
    }

    public function createHabit()
    {
        try {
            \Log::info('[CreateHabit] Iniciando criação', [
                'name' => $this->name,
                'icon' => $this->icon,
                'color' => $this->color,
            ]);

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
                'icon.required' => 'Selecione um ícone',
                'color.required' => 'Selecione uma cor',
                'goal_frequency.required' => 'A meta diária é obrigatória',
                'goal_frequency.min' => 'A meta deve ser pelo menos 1',
                'goal_frequency.max' => 'A meta não pode ser maior que 10',
            ]);

            // Usar HabitService para criar
            $habit = $this->habitService->create([
                'name' => $this->name,
                'description' => $this->description,
                'icon' => $this->icon,
                'color' => $this->color,
                'goal_frequency' => $this->goal_frequency,
                'reminder_time' => $this->reminder_time ?: null,
            ], auth()->id());

            // Verificar achievements
            $unlockedAchievements = $this->achievementService->checkAndUnlock(
                auth()->id(),
                'habit_created',
                ['habit_id' => $habit->id]
            );

            // Emitir evento de achievement se algum foi desbloqueado
            if (!empty($unlockedAchievements)) {
                foreach ($unlockedAchievements as $achievement) {
                    $this->dispatch('achievement-unlocked', [
                        'name' => $achievement->name,
                        'description' => $achievement->description,
                        'icon' => $achievement->icon,
                        'rarity' => $achievement->rarity,
                        'rarity_color' => $achievement->rarity_color,
                        'points' => $achievement->points,
                    ]);
                }
            }

            \Log::info('[CreateHabit] Hábito criado', ['id' => $habit->id]);

            session()->flash('message', '✨ Hábito criado com sucesso!');
            return redirect()->route('daily-habits.dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[CreateHabit] Erro de validação', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('[CreateHabit] Erro ao criar', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', '❌ Erro ao criar hábito: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.daily-habits.create-habit');
    }
}
