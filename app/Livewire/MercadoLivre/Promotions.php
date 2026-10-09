<?php

namespace App\Livewire\MercadoLivre;

use Livewire\Component;
use App\Services\MercadoLivre\PromotionService;
use App\Traits\HasNotifications;

class Promotions extends Component
{
    use HasNotifications;

    // Filtros
    public string $typeFilter   = '';
    public string $statusFilter = '';
    public int    $perPage      = 20;
    public int    $offset       = 0;

    // Dados
    public array  $promotions   = [];
    public int    $total        = 0;
    public array  $paging       = [];

    // Estado UI
    public bool   $loading      = true;
    public bool   $tipsOpen     = false;
    public string $errorMessage = '';

    // Detalhe
    public ?array $selectedPromotion = null;
    public array  $promoItems        = [];
    public bool   $showDetailModal   = false;
    public bool   $loadingDetail     = false;

    public function mount(): void
    {
        $this->loadPromotions();
    }

    public function loadPromotions(): void
    {
        $this->loading      = true;
        $this->errorMessage = '';

        $service = new PromotionService();
        $filters = [
            'type'   => $this->typeFilter   ?: null,
            'status' => $this->statusFilter ?: null,
            'limit'  => $this->perPage,
            'offset' => $this->offset,
        ];

        $result = $service->getPromotions($filters);

        if ($result['success'] ?? false) {
            $this->promotions = $result['promotions'] ?? [];
            $this->paging     = $result['paging']     ?? [];
            $this->total      = $result['total']      ?? count($this->promotions);
        } else {
            $this->errorMessage = $result['message'] ?? 'Erro ao carregar promoções.';
            $this->promotions = [];
        }

        $this->loading = false;
    }

    public function updatedTypeFilter(): void   { $this->offset = 0; $this->loadPromotions(); }
    public function updatedStatusFilter(): void { $this->offset = 0; $this->loadPromotions(); }

    public function clearFilters(): void
    {
        $this->typeFilter   = '';
        $this->statusFilter = '';
        $this->offset       = 0;
        $this->loadPromotions();
    }

    // ---------------------------------------------------------------
    // Detalhe de uma promoção
    // ---------------------------------------------------------------

    public function openDetail(string $promoId, string $promoType = ''): void
    {
        $this->selectedPromotion = null;
        $this->promoItems        = [];
        $this->showDetailModal   = true;
        $this->loadingDetail     = true;

        $service = new PromotionService();

        // A API exige o tipo da promoção; usa o da listagem se não vier na chamada
        if ($promoType === '') {
            foreach ($this->promotions as $p) {
                if ((string)($p['id'] ?? '') === $promoId) {
                    $promoType = (string)($p['type'] ?? '');
                    break;
                }
            }
        }

        $promo = $service->getPromotion($promoId, $promoType);
        if ($promo['success'] ?? false) {
            // Mantém o tipo mesmo que a resposta de detalhe não o traga
            $this->selectedPromotion = array_merge(['id' => $promoId, 'type' => $promoType], $promo['data'] ?? []);
        } else {
            $this->notifyError($promo['message'] ?? 'Erro ao carregar a promoção.');
        }

        $items = $service->getPromotionItems($promoId, $promoType, 30);
        if ($items['success'] ?? false) {
            $this->promoItems = $items['items'];
        }

        $this->loadingDetail = false;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal   = false;
        $this->selectedPromotion = null;
        $this->promoItems        = [];
    }

    // ---------------------------------------------------------------
    // Paginação
    // ---------------------------------------------------------------

    public function nextPage(): void
    {
        if ($this->offset + $this->perPage < $this->total) {
            $this->offset += $this->perPage;
            $this->loadPromotions();
        }
    }

    public function prevPage(): void
    {
        if ($this->offset > 0) {
            $this->offset = max(0, $this->offset - $this->perPage);
            $this->loadPromotions();
        }
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function getStatusBadge(?string $status): array
    {
        return match(strtolower($status ?? '')) {
            'started'   => ['label' => 'Ativa',      'bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
            'pending'   => ['label' => 'Agendada',   'bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'candidate' => ['label' => 'Convite',    'bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
            'finished'  => ['label' => 'Encerrada',  'bg' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'],
            default     => ['label' => ucfirst($status ?? '-'), 'bg' => 'bg-slate-100 text-slate-500'],
        };
    }

    public function getTypeBadge(?string $type): array
    {
        $labels = PromotionService::TYPES;
        $colors = [
            'DEAL'                 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'LIGHTNING'            => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'DOD'                  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'SELLER_CAMPAIGN'      => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'MARKETPLACE_CAMPAIGN' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'PRICE_DISCOUNT'       => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'VOLUME'               => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
            'PRE_NEGOTIATED'       => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
            'SMART'                => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
            'UNHEALTHY_STOCK'      => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        ];
        $key = strtoupper($type ?? '');

        return [
            'label' => $labels[$key] ?? ($type ?: 'Promoção'),
            'bg'    => $colors[$key] ?? 'bg-slate-100 text-slate-600',
        ];
    }

    public function formatDate(?string $date): string
    {
        if (!$date) return '';
        try {
            return \Carbon\Carbon::parse($date)->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $date;
        }
    }

    public function formatPrice(float|int|null $value, string $currency = 'BRL'): string
    {
        if ($value === null) return '-';
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }

    public function render()
    {
        return view('livewire.mercadolivre.promotions', [
                'promotionTypes' => PromotionService::TYPES,
            ])
            ->layout('components.layouts.app', ['title' => 'Promoções ML']);
    }
}
