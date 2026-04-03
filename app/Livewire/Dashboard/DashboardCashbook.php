<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Invoice;
use App\Exports\CashbookExport;
use App\Services\Dashboard\DashboardFinanceMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;

class DashboardCashbook extends Component
{
    // Filtros
    public int $ano;
    public int $mes;
    public ?int $cofrinhoFiltro = null;

    // Dados principais
    public float $totalReceitas = 0;
    public float $totalDespesas = 0;
    public float $saldoTotal = 0;
    public array $dadosReceita = [];
    public array $dadosDespesa = [];
    public array $saldosMes = [];
    public float $saldoUltimoMes = 0;
    public string $nomeUltimoMes = '-';
    public float $receitaUltimoMes = 0;
    public float $despesaUltimoMes = 0;

    // Gráfico diário de invoices
    public array $diasInvoices = [];
    public array $valoresInvoices = [];

    // Gráfico de categorias
    public array $categorias = [];
    public array $valoresCategorias = [];

    // Calendário
    public array $cashbookDays = [];
    public array $invoiceDays = [];

    // Cofrinhos
    public array $cofrinhos = [];
    public float $totalCofrinhos = 0;
    public float $totalMetasCofrinhos = 0;
    public array $cofrinhosTopMeta = [];
    public float $economiadoMesAtual = 0;
    public float $economiadoMesAnterior = 0;
    public array $evolucaoCofrinhos = [];
    public ?array $selectedCofrinhoSummary = null;

    // Orçamentos
    public float $orcamentoMesTotal = 0;
    public float $orcamentoMesUsado = 0;
    public float $orcamentoRestante = 0;
    public float $orcamentoUsoPercentual = 0;
    public array $orcamentosTopEstouro = [];

    // Previsões
    public float $previsao30dias = 0;
    public float $previsao60dias = 0;
    public float $previsao90dias = 0;

    // Detalhes do dia e transações recentes
    public ?string $selectedDate = null;
    public array $dayDetails = [];
    public array $recentTransactions = [];
    public ?string $filterType = null;
    public string $periodLabel = '';
    public float $receitaMesAtual = 0;
    public float $despesaMesAtual = 0;
    public float $saldoMesAtual = 0;
    public float $receitaMesAnterior = 0;
    public float $despesaMesAnterior = 0;
    public float $invoiceMesAtual = 0;
    public float $invoiceMesAnterior = 0;
    public int $invoiceQuantidadeMesAtual = 0;
    public float $ticketMedioInvoice = 0;
    public $highestInvoice = null;
    public array $invoiceTotalsByMonth = [];
    public array $invoiceCountsByMonth = [];
    public array $expenseCategories = [];
    public array $bankBreakdown = [];
    public array $bankCycleOverview = [];
    public array $activityFeed = [];
    public array $uploadSummary = [];
    public array $periodComparison = [];
    public int $totalBancos = 0;
    public int $bancosAtivosComGasto = 0;
    public int $activeDaysCount = 0;
    public float $mediaReceitaDia = 0;
    public float $mediaDespesaDia = 0;
    public float $mediaInvoiceDia = 0;
    public float $invoicePressurePercent = 0;
    public float $savingsRatePercent = 0;
    public $topExpenseCategory = null;
    public $topBank = null;

    public function mount()
    {
        $this->ano = date('Y');
        $this->mes = date('n');
        $this->loadData();
    }

    public function setFilter($type)
    {
        $this->filterType = $this->filterType === $type ? null : $type;
        $this->recentTransactions = app(DashboardFinanceMetricsService::class)
            ->buildRecentTransactions((int) Auth::id(), $this->filterType, $this->cofrinhoFiltro)
        ;
    }

    public function updatedAno()
    {
        $this->loadData();
    }

    public function updatedMes()
    {
        $this->loadData();
    }

    public function updatedCofrinhoFiltro()
    {
        $this->loadData();
    }

    public function clearCofrinhoFilter()
    {
        $this->cofrinhoFiltro = null;
        $this->loadData();
    }

    public function loadData()
    {
        $metrics = app(DashboardFinanceMetricsService::class)->getCashbookMetrics(
            (int) Auth::id(),
            $this->ano,
            $this->mes,
            $this->cofrinhoFiltro,
        );

        foreach ($metrics as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->recentTransactions = app(DashboardFinanceMetricsService::class)
            ->buildRecentTransactions((int) Auth::id(), $this->filterType, $this->cofrinhoFiltro)
        ;

        $this->dispatch('cashbook-charts-updated');
    }

    public function showDayDetails($date)
    {
        $this->selectedDate = $date;
        $this->dayDetails = $this->getDayDetails($date);
    }

    public function exportExcel()
    {
        return Excel::download(new CashbookExport($this->ano), 'fluxo-de-caixa-'.$this->ano.'.xlsx');
    }

    public function exportPdf()
    {
        $cashbooks = Cashbook::where('user_id', Auth::id())
            ->whereYear('date', $this->ano)
            ->get();

        $html = view('livewire.exports.cashbook-pdf', ['cashbooks' => $cashbooks, 'ano' => $this->ano])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, "fluxo-de-caixa-{$this->ano}.pdf");
    }

    private function loadRecentTransactions()
    {
        $this->recentTransactions = app(DashboardFinanceMetricsService::class)
            ->buildRecentTransactions((int) Auth::id(), $this->filterType, $this->cofrinhoFiltro)
        ;
    }

    public function getDayDetails($date)
    {
        $userId = Auth::id();

        // Receitas (exceto cofrinhos)
        $receitas = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->whereNull('cofrinho_id')
            ->whereDate('date', $date)
            ->with('category')
            ->get();

        // Despesas (exceto cofrinhos)
        $despesas = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->whereDate('date', $date)
            ->with('category')
            ->get();

        // Invoices
        $invoices = Invoice::where('user_id', $userId)
            ->whereDate('invoice_date', $date)
            ->with('category')
            ->get();

        // Movimentações de cofrinhos
        $cofrinhos = Cashbook::where('user_id', $userId)
            ->whereNotNull('cofrinho_id')
            ->whereDate('date', $date)
            ->with(['cofrinho', 'type'])
            ->get();

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'invoices' => $invoices,
            'cofrinhos' => $cofrinhos
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-cashbook');
    }
}
