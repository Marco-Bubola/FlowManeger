<?php

namespace App\Livewire\Quotes;

use App\Services\Quotes\QuoteService;
use App\Support\Quotes\QuoteDefaults;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Ajustes dos orçamentos: margem, validade, prazo de resposta, acréscimos, cores do portal e automações. */
#[Layout('components.layouts.app')]
class QuoteSettings extends Component
{
    public $margin = 30;
    public $validity_days = 15;
    public $reply_days = 2;
    public $min_markup = 5;
    public bool $remind_client = true;
    public bool $portal_requests = true;
    public string $footer = '';
    public array $extras = [];
    public array $colors = [];

    public string $newColorName = '';
    public string $newColorHex = '#888888';
    public ?int $colorGroup = null;
    public string $newGroup = '';

    public function mount(): void
    {
        $s = QuoteService::settings(Auth::user());
        foreach (['margin', 'validity_days', 'reply_days', 'min_markup', 'remind_client', 'portal_requests', 'extras', 'colors'] as $k) {
            $this->{$k} = $s[$k];
        }
        $this->footer = (string) $s['footer'];
    }

    public function addExtra(): void
    {
        $this->extras[] = ['n' => '', 'v' => 0];
    }

    public function removeExtra(int $i): void
    {
        unset($this->extras[$i]);
        $this->extras = array_values($this->extras);
    }

    public function addColor(int $g): void
    {
        $name = trim($this->newColorName);
        if ($name === '' || !isset($this->colors[$g])) {
            return;
        }
        $this->colors[$g]['items'][] = [mb_substr($name, 0, 40), preg_match('/^#[0-9a-f]{6}$/i', $this->newColorHex) ? $this->newColorHex : '#888888'];
        $this->newColorName = '';
        $this->save(false);
    }

    public function removeColor(int $g, int $i): void
    {
        unset($this->colors[$g]['items'][$i]);
        $this->colors[$g]['items'] = array_values($this->colors[$g]['items'] ?? []);
        if (!$this->colors[$g]['items']) {
            unset($this->colors[$g]);
            $this->colors = array_values($this->colors);
        }
        $this->save(false);
    }

    public function addGroup(): void
    {
        $name = trim($this->newGroup);
        if ($name === '') {
            return;
        }
        $this->colors[] = ['key' => \Illuminate\Support\Str::slug($name), 'name' => mb_substr($name, 0, 60), 'items' => [['Preto', '#1f1f1f']]];
        $this->newGroup = '';
        $this->colorGroup = count($this->colors) - 1;
        $this->save(false);
    }

    public function resetColors(): void
    {
        $this->colors = QuoteService::colorGroups(null);
        QuoteService::saveSettings(Auth::user(), ['colors' => null]);
        $this->dispatch('notify', type: 'success', message: 'Cores iguais às do robô de novo.');
    }

    public function save(bool $toast = true): void
    {
        $this->validate([
            'margin' => 'required|numeric|min:0|max:90',
            'validity_days' => 'required|integer|min:1|max:180',
            'reply_days' => 'required|integer|min:1|max:30',
            'min_markup' => 'required|numeric|min:0|max:100',
            'footer' => 'nullable|string|max:1500',
            'extras.*.n' => 'nullable|string|max:80',
            'extras.*.v' => 'nullable|numeric|min:0|max:100000',
        ], [], ['margin' => 'margem', 'validity_days' => 'validade', 'reply_days' => 'prazo de resposta', 'min_markup' => 'aviso de preço baixo']);

        QuoteService::saveSettings(Auth::user(), [
            'margin' => (float) $this->margin,
            'validity_days' => (int) $this->validity_days,
            'reply_days' => (int) $this->reply_days,
            'min_markup' => (float) $this->min_markup,
            'remind_client' => $this->remind_client,
            'portal_requests' => $this->portal_requests,
            'footer' => trim($this->footer),
            'extras' => array_values(array_filter($this->extras, fn ($e) => trim((string) ($e['n'] ?? '')) !== '')),
            'colors' => QuoteService::colorGroups($this->colors),
        ]);
        if ($toast) {
            $this->dispatch('notify', type: 'success', message: 'Ajustes dos orçamentos salvos.');
        }
    }

    public function render()
    {
        $example = QuoteService::suggestedPrice(100, (float) $this->margin);

        return view('livewire.quotes.quote-settings', [
            'example' => $example,
            'portalUrl' => route('portal.custom-quote.create', ['userId' => Auth::id()]),
        ])->title('Ajustes dos orçamentos');
    }
}
