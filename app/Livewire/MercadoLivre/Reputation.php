<?php

namespace App\Livewire\MercadoLivre;

use Livewire\Component;
use App\Services\MercadoLivre\ReputationService;
use App\Traits\HasNotifications;

class Reputation extends Component
{
    use HasNotifications;

    // Dados
    public array  $seller    = [];
    public array  $metrics   = [];
    public array  $feedback  = [];
    public array  $rep       = [];

    // Estado
    public bool   $loading   = true;
    public bool   $tipsOpen  = false;
    public string $errorMessage = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->loading      = true;
        $this->errorMessage = '';

        $service = new ReputationService();

        // Dados gerais do vendedor (inclui seller_reputation)
        $sellerRes = $service->getSellerData();
        if ($sellerRes['success'] ?? false) {
            $this->seller = $sellerRes['data'] ?? [];
            $this->rep    = $this->seller['seller_reputation'] ?? [];
        } else {
            $this->errorMessage = $sellerRes['message'] ?? 'Erro ao carregar dados.';
            $this->loading = false;
            return;
        }

        // Feedback summary
        $fbRes = $service->getFeedbackSummary();
        if ($fbRes['success'] ?? false) {
            $this->feedback = $fbRes['data'] ?? [];
        }

        $this->loading = false;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Retorna a label e a cor CSS do nível de reputação.
     */
    public function getLevelBadge(?string $level): array
    {
        return match($level) {
            '5_green'    => ['label' => '5 – Verde',    'bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            '4_light_green' => ['label' => '4 – Verde Claro', 'bg' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'],
            '3_yellow'   => ['label' => '3 – Amarelo',  'bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
            '2_orange'   => ['label' => '2 – Laranja',  'bg' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
            '1_red'      => ['label' => '1 – Vermelho',  'bg' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
            default      => ['label' => $level ?? 'Novo', 'bg' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'],
        };
    }

    public function render()
    {
        return view('livewire.mercadolivre.reputation')
            ->layout('components.layouts.app', ['title' => 'Reputação ML']);
    }
}
