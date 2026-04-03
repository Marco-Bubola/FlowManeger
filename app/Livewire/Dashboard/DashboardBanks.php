<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardFinanceMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardBanks extends Component
{
    public $bancos = [];
    public $bancosInfo = [];
    public string $periodLabel = '';
    public int $ano;
    public int $mes;
    public array $availableYears = [];
    public int $totalBancos = 0;
    public float $totalInvoicesBancos = 0;
    public float $saldoTotalBancos = 0;
    public float $totalSaidasBancos = 0;
    public float $monthTotal = 0;
    public int $monthCount = 0;
    public float $avgMonth = 0;
    public array $trendLabels = [];
    public array $trendValues = [];
    public array $topBanks = [];
    public array $topBanksMonthValues = [];
    public array $topBanksCycleValues = [];
    public array $invoiceCategoryShare = [];
    public array $recentUploads = [];
    public int $activeBanksMonth = 0;
    public $topBankSummary = null;
    public $topCategorySummary = null;
    public float $avgCycleAmount = 0;
    public float $avgDaysToClose = 0;
    public float $uploadSuccessAverage = 0;
    public float $monthDailyAverage = 0;

    public function mount()
    {
        $this->ano = (int) now()->year;
        $this->mes = (int) now()->month;
        $this->availableYears = range((int) now()->year - 4, (int) now()->year + 1);
        $this->loadBanksData();
    }

    public function updatedAno(): void
    {
        $this->loadBanksData();
    }

    public function updatedMes(): void
    {
        $this->loadBanksData();
    }

    private function loadBanksData()
    {
        $metrics = app(DashboardFinanceMetricsService::class)->getBanksMetrics((int) Auth::id(), $this->ano, $this->mes);

        foreach ($metrics as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->bancos = $this->bancosInfo;
        $this->dispatch('banks-charts-updated');
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-banks');
    }
}
