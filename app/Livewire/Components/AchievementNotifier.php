<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

class AchievementNotifier extends Component
{
    public $achievement = null;
    public $show = false;

    #[On('achievement-unlocked')]
    public function showAchievement($achievementData)
    {
        $this->achievement = $achievementData;
        $this->show = true;
    }

    public function hideNotification()
    {
        $this->show = false;
        $this->achievement = null;
    }

    public function render()
    {
        return view('livewire.components.achievement-notifier');
    }
}
