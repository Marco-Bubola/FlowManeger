<?php

namespace App\Http\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AppearanceSettings extends Component
{
    public $appearance = 'system';
    public $font_family = 'sans';
    public $border_style = 'rounded';
    public $secondary_color = '#3b82f6'; // Tailwind blue-500
    public $background_pattern = 'none';

    public function mount()
    {
        // Aqui você pode carregar as preferências do usuário autenticado, se desejar
        $user = Auth::user();
        if ($user && $user->appearance_settings) {
            $settings = json_decode($user->appearance_settings, true);
            $this->appearance = $settings['appearance'] ?? $this->appearance;
            $this->font_family = $settings['font_family'] ?? $this->font_family;
            $this->border_style = $settings['border_style'] ?? $this->border_style;
            $this->secondary_color = $settings['secondary_color'] ?? $this->secondary_color;
            $this->background_pattern = $settings['background_pattern'] ?? $this->background_pattern;
        }
    }

    public function saveAppearance()
    {
        $user = Auth::user();
        if ($user) {
            $user->appearance_settings = json_encode([
                'appearance' => $this->appearance,
                'font_family' => $this->font_family,
                'border_style' => $this->border_style,
                'secondary_color' => $this->secondary_color,
                'background_pattern' => $this->background_pattern,
            ]);
            $user->save();
        }
        session()->flash('success', 'Preferências de aparência salvas com sucesso!');
        $this->emit('appearance-updated');
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
