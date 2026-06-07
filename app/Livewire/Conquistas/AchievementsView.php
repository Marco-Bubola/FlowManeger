<?php

namespace App\Livewire\Conquistas;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\XpLog;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AchievementsView extends Component
{
    public array $level = [];
    public array $achievements = [];
    public array $xpLogs = [];

    protected GamificationService $gamification;

    public function boot(GamificationService $gamification): void
    {
        $this->gamification = $gamification;
    }

    public function mount(): void
    {
        $userId = Auth::id();
        $this->level = $this->gamification->summary($userId);

        $unlocked = UserAchievement::where('user_id', $userId)
            ->pluck('unlocked_at', 'achievement_id')->all();

        $this->achievements = Achievement::where('is_secret', false)
            ->orderBy('order')->get()
            ->map(fn ($a) => [
                'name'        => $a->name,
                'description' => $a->description,
                'icon'        => $a->icon ?: 'bi-award',
                'color'       => $a->color ?: '#a490c2',
                'rarity'      => $a->rarity,
                'points'      => $a->points,
                'unlocked'    => isset($unlocked[$a->id]),
            ])->toArray();

        $this->xpLogs = XpLog::where('user_id', $userId)
            ->latest()->limit(12)->get()
            ->map(fn ($x) => [
                'amount' => $x->amount,
                'reason' => $x->reason,
                'when'   => $x->created_at->diffForHumans(),
            ])->toArray();
    }

    public function render()
    {
        return view('livewire.conquistas.achievements-view');
    }
}
