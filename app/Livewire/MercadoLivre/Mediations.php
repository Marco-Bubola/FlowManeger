<?php

namespace App\Livewire\MercadoLivre;

use Livewire\Component;
use App\Services\MercadoLivre\MediationService;
use App\Traits\HasNotifications;

class Mediations extends Component
{
    use HasNotifications;

    // Filtros
    public string $statusFilter   = 'opened';
    public int    $perPage        = 25;
    public int    $offset         = 0;

    // Dados
    public array  $claims         = [];
    public int    $total          = 0;
    public array  $paging         = [];

    // Estado UI
    public bool   $loading        = true;
    public bool   $showFiltersModal = false;
    public bool   $tipsOpen       = false;
    public string $errorMessage   = '';

    // Detalhes de mediação selecionada
    public ?array $selectedClaim  = null;
    public array  $claimMessages  = [];
    public bool   $showDetailModal = false;
    public bool   $loadingDetail  = false;
    public string $replyText      = '';
    public bool   $sendingReply   = false;

    public function mount(): void
    {
        $this->loadClaims();
    }

    public function loadClaims(): void
    {
        $this->loading      = true;
        $this->errorMessage = '';

        $service = new MediationService();
        $result  = $service->getClaims([
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'limit'  => $this->perPage,
            'offset' => $this->offset,
        ]);

        if ($result['success'] ?? false) {
            $this->claims  = $result['claims'] ?? [];
            $this->paging  = $result['paging'] ?? [];
            $this->total   = $result['total']  ?? count($this->claims);
        } else {
            $this->errorMessage = $result['message'] ?? 'Erro ao carregar mediações.';
            $this->claims = [];
        }

        $this->loading = false;
    }

    public function updatedStatusFilter(): void
    {
        $this->offset = 0;
        $this->loadClaims();
    }

    // ---------------------------------------------------------------
    // Detalhe e mensagens de uma mediação
    // ---------------------------------------------------------------

    public function openDetail(int $claimId): void
    {
        $this->selectedClaim   = null;
        $this->claimMessages   = [];
        $this->replyText       = '';
        $this->showDetailModal = true;
        $this->loadingDetail   = true;

        $service = new MediationService();

        $details  = $service->getClaimDetails($claimId);
        $messages = $service->getClaimMessages($claimId);

        if ($details['success'] ?? false) {
            $this->selectedClaim = $details['data'];
        }
        if ($messages['success'] ?? false) {
            $this->claimMessages = $messages['messages'];
        }

        $this->loadingDetail = false;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedClaim   = null;
        $this->claimMessages   = [];
    }

    public function sendReply(): void
    {
        $this->validate(['replyText' => 'required|min:3|max:2000']);

        if (!$this->selectedClaim) {
            return;
        }

        $this->sendingReply = true;
        $service = new MediationService();

        $result = $service->sendMessage((int) $this->selectedClaim['id'], $this->replyText);

        if ($result['success'] ?? false) {
            $this->replyText = '';
            $this->notifySuccess('Mensagem enviada com sucesso!');
            // Recarregar mensagens
            $this->openDetail((int) $this->selectedClaim['id']);
        } else {
            $this->notifyError($result['message'] ?? 'Erro ao enviar mensagem.');
        }

        $this->sendingReply = false;
    }

    // ---------------------------------------------------------------
    // Paginação
    // ---------------------------------------------------------------

    public function nextPage(): void
    {
        if ($this->offset + $this->perPage < $this->total) {
            $this->offset += $this->perPage;
            $this->loadClaims();
        }
    }

    public function prevPage(): void
    {
        if ($this->offset > 0) {
            $this->offset = max(0, $this->offset - $this->perPage);
            $this->loadClaims();
        }
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function getStatusBadge(?string $status): array
    {
        return match(strtolower($status ?? '')) {
            'opened'   => ['label' => 'Aberta',     'bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
            'closed'   => ['label' => 'Encerrada',  'bg' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'],
            'resolved' => ['label' => 'Resolvida',  'bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            default    => ['label' => ucfirst($status ?? '-'), 'bg' => 'bg-slate-100 text-slate-500 dark:bg-slate-800'],
        };
    }

    public function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->setTimezone(config('app.timezone', 'America/Sao_Paulo'))->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $date;
        }
    }

    public function render()
    {
        return view('livewire.mercadolivre.mediations')
            ->layout('components.layouts.app', ['title' => 'Mediações ML']);
    }
}
