<?php

namespace App\Livewire\Achievements;

use Livewire\Component;
use App\Services\AchievementService;
use App\Models\Achievement;
use Illuminate\Support\Facades\Auth;

class AchievementsPage extends Component
{
    public $filterRarity = 'all';
    public $filterCategory = 'all';
    public $sortBy = 'order';

    protected $achievementService;

    public function boot(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    public function render()
    {
        $userId = Auth::id();
        $userAchievements = $this->achievementService->getUserAchievements($userId);
        $stats = $this->achievementService->getUserStats($userId);

        $query = Achievement::query()->orderBy($this->sortBy);

        // Filtros
        if ($this->filterRarity !== 'all') {
            $query->where('rarity', $this->filterRarity);
        }

        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        $achievements = $query->get();

        // Mapear achievements desbloqueadas
        $unlockedIds = $userAchievements->pluck('achievement_id')->toArray();

        return view('livewire.achievements.achievements-page', [
            'achievements' => $achievements,
            'unlockedIds' => $unlockedIds,
            'userAchievements' => $userAchievements->keyBy('achievement_id'),
            'stats' => $stats,
        ]);
    }
}
