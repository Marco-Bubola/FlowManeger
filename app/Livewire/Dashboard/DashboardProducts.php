<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardProductsMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardProducts extends Component
{
    public int $selectedMonth;
    public int $selectedYear;
    public string $periodPreset = 'month';
    public string $selectedChannel = 'all';
    public array $availableYears = [];
    public array $monthOptions = [];
    public array $channelOptions = [];
    public string $periodLabel = '';

    public float $ticketMedio = 0;
    public int $totalProdutos = 0;
    public int $totalProdutosEstoque = 0;
    public int $produtosSemEstoque = 0;
    public int $produtosEstoqueCritico = 0;
    public int $produtosSemGiro = 0;
    public int $produtosAtivos = 0;
    public int $produtosInativos = 0;
    public int $produtosDescontinuados = 0;
    public int $produtosComMlAtivo = 0;
    public int $produtosComShopeeAtivo = 0;
    public int $unidadesVendidasPeriodo = 0;
    public int $pedidosPeriodo = 0;
    public int $categoriasAtivasPeriodo = 0;
    public int $kitsVendidosPeriodo = 0;
    public int $componentesConsumidosViaKits = 0;
    public int $produtosLigadosKits = 0;
    public float $totalDespesasProdutos = 0;
    public float $totalReceitasProdutos = 0;
    public float $totalSaldoProdutos = 0;
    public float $margemMediaEstoque = 0;
    public float $giroMedioProdutos = 0;
    public float $faturamentoPeriodo = 0;
    public float $lucroEstimadoPeriodo = 0;
    public float $ticketMedioPeriodo = 0;
    public float $participacaoTopProdutos = 0;
    public float $receitaKitsPeriodo = 0;

    public $produtoMaiorEstoque = null;
    public $produtoMaisVendido = null;
    public array $ultimosProdutos = [];
    public array $produtosParados = [];
    public array $produtosMaisVendidos = [];
    public array $produtosMaiorReceita = [];
    public array $produtoMaiorLucro = [];
    public array $produtosEstoqueBaixoAltaDemanda = [];
    public array $dadosGraficoPizza = [];
    public array $statusProdutos = [];
    public array $vendasMensaisProdutos = [];
    public array $periodComparison = [];
    public array $periodSummary = [];
    public array $topKits = [];
    public array $coberturaProdutos = [];
    public array $coberturaCategorias = [];
    public array $marketplacePeriodMetrics = [];
    public array $channelMetrics = [];

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;
        $this->availableYears = range((int) now()->year - 4, (int) now()->year + 1);
        $this->monthOptions = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
        $this->channelOptions = [
            'all' => 'Todos',
            'internal' => 'Loja interna',
            'kits' => 'Kits',
            'ml' => 'Mercado Livre',
            'shopee' => 'Shopee',
        ];

        $this->loadDashboardData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->periodPreset = 'month';
        $this->loadDashboardData();
    }

    public function updatedSelectedYear(): void
    {
        $this->periodPreset = 'month';
        $this->loadDashboardData();
    }

    public function updatedSelectedChannel(): void
    {
        $this->loadDashboardData();
    }

    public function applyPeriodPreset(string $preset): void
    {
        if (!in_array($preset, ['today', 'month', 'quarter', 'year'], true)) {
            return;
        }

        $this->periodPreset = $preset;
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $metrics = app(DashboardProductsMetricsService::class)->getMetrics(
            (int) Auth::id(),
            $this->selectedMonth,
            $this->selectedYear,
            $this->periodPreset,
            $this->selectedChannel,
        );

        foreach ($metrics as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-products');
    }
}
