<?php

namespace App\Livewire\Promotions;

use App\Services\Portal\SiteStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/** Relatório do site: visitas, de onde vieram, produtos mais vistos e pedidos do catálogo. */
class PortalReport extends Component
{
    public int $days = 30;

    protected $queryString = ['days' => ['except' => 30, 'as' => 'dias']];

    public function render()
    {
        if (! isset(SiteStats::PERIODS[$this->days])) {
            $this->days = 30;
        }

        return view('livewire.promotions.portal-report', [
            'r' => SiteStats::ready() ? SiteStats::report(Auth::id(), $this->days) : null,
            'catalogUrl' => route('portal.catalog', ['userId' => Auth::id()]),
        ]);
    }
}
