<?php

namespace App\Livewire\Gestao;

use App\Models\PromotionSetting;
use App\Services\Collections\CollectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Cobranças: parcelas de vendas e de consórcios vencidas ou vencendo, com
 * lembrete pelo WhatsApp em um clique (abre o wa.me com a mensagem pronta e
 * registra o envio). Envio automático exigiria a API do WhatsApp Business.
 */
class Collections extends Component
{
    #[Url(except: 'vencidas')]
    public string $filter = 'vencidas'; // vencidas | vencendo | todas

    #[Url(except: 3)]
    public int $days = 3;

    #[Url(except: 'todos')]
    public string $channel = 'todos'; // todos | vendas | consorcios

    #[Url(except: '')]
    public string $search = '';

    public function updatedDays($value): void
    {
        $this->days = max(0, min(60, (int) $value));
    }

    /** Registra o lembrete; o link do wa.me abre direto no navegador. */
    public function remind(string $key, CollectionService $service): void
    {
        $row = $service->rows(Auth::user())->firstWhere('key', $key);

        if (! $row) {
            return;
        }

        $service->recordReminder(Auth::user(), $row);
    }

    public function render(CollectionService $service)
    {
        $user = Auth::user();
        $all = $service->rows($user);
        $settings = PromotionSetting::forUser($user->id);
        $days = max(0, $this->days);

        $term = mb_strtolower(trim($this->search));

        $rows = $all
            ->filter(fn ($r) => match ($this->filter) {
                'vencidas' => $r['days'] < 0,
                'vencendo' => $r['days'] >= 0 && $r['days'] <= $days,
                default => true,
            })
            ->filter(fn ($r) => $this->channel === 'todos' || $r['channel'] === $this->channel)
            ->filter(fn ($r) => $term === '' || str_contains(mb_strtolower($r['client']->name ?? ''), $term))
            ->map(function ($r) use ($service, $settings, $user) {
                $r['phone'] = $service->whatsappPhone($r['client']->phone ?? null);
                $r['message'] = $service->message($r, $settings, $user->name);
                $r['wa_url'] = $r['phone'] ? $service->whatsappUrl($r['phone'], $r['message']) : null;

                return $r;
            })
            ->values();

        $overdue = $all->filter(fn ($r) => $r['days'] < 0);
        $soon = $all->filter(fn ($r) => $r['days'] >= 0 && $r['days'] <= $days);

        return view('livewire.gestao.collections', [
            'rows' => $rows,
            'summary' => [
                'overdue_count' => $overdue->count(),
                'overdue_total' => $overdue->sum('value'),
                'soon_count' => $soon->count(),
                'soon_total' => $soon->sum('value'),
            ],
        ]);
    }
}
