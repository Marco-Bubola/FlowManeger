<?php

namespace App\Livewire\DailyHabits;

use App\Models\DailyHabit;
use App\Models\DailyHabitCompletion;
use App\Services\AchievementService;
use App\Services\GamificationService;
use App\Services\HabitService;
use App\Traits\HasNotifications;
use Carbon\Carbon;
use Livewire\Component;

class DailyHabitsDashboard extends Component
{
    use HasNotifications;

    public $habits = [];
    public $stats = [];
    public $recentAchievements = [];

    protected HabitService $habitService;
    protected AchievementService $achievementService;

    public function boot(HabitService $habitService, AchievementService $achievementService)
    {
        $this->habitService = $habitService;
        $this->achievementService = $achievementService;
    }

    public function mount()
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $userId = auth()->id();
        $today = Carbon::today();

        $doneIds = DailyHabitCompletion::where('user_id', $userId)
            ->whereDate('completion_date', $today)
            ->pluck('habit_id')->all();

        $since = $today->copy()->subDays(29);
        $last30 = DailyHabitCompletion::where('user_id', $userId)
            ->whereDate('completion_date', '>=', $since)
            ->selectRaw('habit_id, COUNT(DISTINCT DATE(completion_date)) as dias')
            ->groupBy('habit_id')
            ->pluck('dias', 'habit_id');

        $this->habits = DailyHabit::where('user_id', $userId)
            ->active()
            ->where(fn ($q) => $q->where('is_archived', false)->orWhereNull('is_archived'))
            ->ordered()
            ->with('streak')
            ->get()
            ->map(function ($habit) use ($doneIds, $last30, $today) {
                $streak = $habit->streak;

                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'icon' => $habit->icon ?: 'bi-check2-circle',
                    'color' => $habit->color ?: '#8B5CF6',
                    'done' => in_array($habit->id, $doneIds),
                    'scheduled' => $habit->isScheduledFor($today),
                    'current_streak' => (int) ($streak->current_streak ?? 0),
                    'longest_streak' => (int) ($streak->longest_streak ?? 0),
                    'total_completions' => (int) ($streak->total_completions ?? 0),
                    'rate30' => (int) round(((int) ($last30[$habit->id] ?? 0)) / 30 * 100),
                ];
            })
            ->toArray();

        $all = collect($this->habits);
        $scheduled = $all->where('scheduled', true);
        $doneToday = $scheduled->where('done', true)->count();

        $this->stats = [
            'total_habits' => $all->count(),
            'scheduled_today' => $scheduled->count(),
            'completed_today' => $doneToday,
            'completion_percentage' => $scheduled->count() > 0 ? (int) round($doneToday / $scheduled->count() * 100) : 0,
            'current_streak' => (int) $all->max('current_streak'),
            'best_streak' => (int) $all->max('longest_streak'),
            'rate30' => $all->count() > 0 ? (int) round($all->avg('rate30')) : 0,
        ];

        $this->recentAchievements = $this->achievementService->getUserAchievements($userId)
            ->filter(fn ($ua) => in_array($ua->achievement->category, ['habits', 'streak']))
            ->sortByDesc('unlocked_at')
            ->take(3)
            ->map(fn ($ua) => [
                'name' => $ua->achievement->name,
                'icon' => $ua->achievement->icon,
                'points' => $ua->achievement->points,
            ])
            ->values()
            ->toArray();
    }

    public function toggleHabit($habitId)
    {
        $userId = auth()->id();
        $habit = DailyHabit::where('id', $habitId)->where('user_id', $userId)->first();

        if (! $habit) {
            $this->notifyError('Hábito não encontrado.');
            return;
        }

        $toggle = $this->habitService->toggleToday($habit, $userId);

        if ($toggle['done']) {
            $unlocked = $this->achievementService->checkAndUnlock($userId, 'habit_completed', ['habit_id' => $habit->id]);
            foreach ($unlocked ?? [] as $achievement) {
                $this->dispatch('achievement-unlocked', [
                    'name' => $achievement->name,
                    'description' => $achievement->description,
                    'icon' => $achievement->icon,
                    'rarity' => $achievement->rarity,
                    'rarity_color' => $achievement->rarity_color,
                    'points' => $achievement->points,
                ]);
            }

            $result = $toggle['result'];
            if (! empty($result['leveledUp'])) {
                $this->notifySuccess('🎉 Subiu para o nível ' . $result['level']->level . '!');
            } else {
                $this->notifySuccess($habit->name . ' concluído hoje. +' . GamificationService::XP_HABIT_COMPLETE . ' XP');
            }
        } else {
            $this->notifyInfo($habit->name . ' desmarcado.');
        }

        $this->loadData();
    }

    public function render()
    {
        return view('livewire.daily-habits.daily-habits-dashboard');
    }
}
