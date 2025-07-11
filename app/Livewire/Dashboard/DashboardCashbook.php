<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Bank;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class DashboardCashbook extends Component
{
    // Filtros
    public int $ano;
    public int $mesInvoices;
    public int $anoInvoices;

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

    // Dados dos bancos
    public $bancos = [];
    public $bancosInfo = [];
    public int $totalBancos = 0;
    public float $totalInvoicesBancos = 0;
    public float $saldoTotalBancos = 0;
    public float $totalSaidasBancos = 0;
    public array $bancosEvolucaoMeses = [];
    public array $bancosEvolucaoSaldos = [];

    // Gráfico diário de invoices
    public array $diasInvoices = [];
    public array $valoresInvoices = [];

    // Calendário
    public array $cashbookDays = [];
    public array $invoiceDays = [];

    public function mount()
    {
        $this->ano = date('Y');
        $this->mesInvoices = now()->month;
        $this->anoInvoices = now()->year;
        $this->loadData();
    }

    public function updatedAno()
    {
        $this->loadData();
    }

    public function updatedMesInvoices()
    {
        $this->loadInvoicesData();
    }

    public function updatedAnoInvoices()
    {
        $this->loadInvoicesData();
    }

    public function loadData()
    {
        $userId = Auth::id();

        // Totais gerais
        $this->totalReceitas = Cashbook::where('user_id', $userId)->where('type_id', 1)->sum('value');
        $this->totalDespesas = Cashbook::where('user_id', $userId)->where('type_id', 2)->sum('value');
        $this->saldoTotal = $this->totalReceitas - $this->totalDespesas;

        // Dados mensais
        $meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];

        $this->dadosReceita = [];
        $this->dadosDespesa = [];
        $this->saldosMes = [];

        foreach ($meses as $num => $nome) {
            $receita = (float) Cashbook::where('user_id', $userId)
                ->where('type_id', 1)
                ->whereYear('date', $this->ano)
                ->whereMonth('date', $num)
                ->sum('value');
            $despesa = (float) Cashbook::where('user_id', $userId)
                ->where('type_id', 2)
                ->whereYear('date', $this->ano)
                ->whereMonth('date', $num)
                ->sum('value');
            $this->dadosReceita[] = $receita;
            $this->dadosDespesa[] = $despesa;
            $this->saldosMes[] = $receita - $despesa;
        }

        // Último mês com movimentação
        $ultimoMes = null;
        foreach (array_reverse(array_keys($meses)) as $i) {
            if ($this->dadosReceita[$i-1] != 0 || $this->dadosDespesa[$i-1] != 0) {
                $ultimoMes = $i;
                break;
            }
        }
        $this->nomeUltimoMes = $ultimoMes ? $meses[$ultimoMes] : '-';
        $this->receitaUltimoMes = $ultimoMes ? $this->dadosReceita[$ultimoMes-1] : 0;
        $this->despesaUltimoMes = $ultimoMes ? $this->dadosDespesa[$ultimoMes-1] : 0;
        $this->saldoUltimoMes = $ultimoMes ? $this->saldosMes[$ultimoMes-1] : 0;

        // Dados dos bancos
        $this->loadBanksData();
        
        // Dados de invoices
        $this->loadInvoicesData();
        
        // Dados do calendário
        $this->loadCalendarData();
    }

    private function loadBanksData()
    {
        $userId = Auth::id();
        $this->bancos = Bank::where('user_id', $userId)->get();

        // Informações detalhadas de bancos e invoices
        $this->bancosInfo = $this->bancos->map(function($bank) use ($userId) {
            $invoices = Invoice::where('id_bank', $bank->id_bank)->where('user_id', $userId)->get();
            $totalInvoices = $invoices->sum('value');
            $qtdInvoices = $invoices->count();
            $mediaInvoices = $qtdInvoices > 0 ? $totalInvoices / $qtdInvoices : 0;
            $maiorInvoice = $invoices->sortByDesc('value')->first();
            $menorInvoice = $invoices->sortBy('value')->first();

            $saldoBanco = -$totalInvoices;

            return [
                'id_bank' => $bank->id_bank,
                'nome' => $bank->name,
                'descricao' => $bank->description,
                'total_invoices' => $totalInvoices,
                'qtd_invoices' => $qtdInvoices,
                'media_invoices' => $mediaInvoices,
                'maior_invoice' => $maiorInvoice,
                'menor_invoice' => $menorInvoice,
                'saldo' => $saldoBanco,
                'saidas' => $totalInvoices,
            ];
        })->toArray();

        // Totais gerais de bancos
        $this->totalBancos = count($this->bancos);
        $this->totalInvoicesBancos = collect($this->bancosInfo)->sum('total_invoices');
        $this->saldoTotalBancos = collect($this->bancosInfo)->sum('saldo');
        $this->totalSaidasBancos = collect($this->bancosInfo)->sum('saidas');

        // Evolução do saldo total dos bancos nos últimos 12 meses
        $this->bancosEvolucaoMeses = [];
        $this->bancosEvolucaoSaldos = [];
        $saldoAcumulado = 0;
        $mesRef = now()->copy()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mesLabel = $mesRef->format('m/Y');
            $invoicesMes = Invoice::where('user_id', $userId)
                ->whereBetween('invoice_date', [$mesRef->copy()->startOfMonth(), $mesRef->copy()->endOfMonth()])
                ->get();
            $saidasMes = $invoicesMes->sum('value');
            $saldoAcumulado -= $saidasMes;
            $this->bancosEvolucaoMeses[] = $mesLabel;
            $this->bancosEvolucaoSaldos[] = $saldoAcumulado;
            $mesRef->addMonth();
        }
    }

    private function loadInvoicesData()
    {
        $userId = Auth::id();

        // Gera labels e valores para os dias do mês selecionado
        $this->diasInvoices = [];
        $this->valoresInvoices = [];
        $diasNoMes = \Carbon\Carbon::create($this->anoInvoices, $this->mesInvoices, 1)->daysInMonth;
        for ($i = 1; $i <= $diasNoMes; $i++) {
            $data = \Carbon\Carbon::create($this->anoInvoices, $this->mesInvoices, $i);
            $this->diasInvoices[] = $data->format('d/m');
            $valorDia = Invoice::where('user_id', $userId)
                ->whereDate('invoice_date', $data->format('Y-m-d'))
                ->sum('value');
            $this->valoresInvoices[] = (float)$valorDia;
        }
    }

    private function loadCalendarData()
    {
        $userId = Auth::id();
        $mesAtual = now()->month;
        $anoAtual = now()->year;
        $diasNoMes = \Carbon\Carbon::create($anoAtual, $mesAtual, 1)->daysInMonth;
        $this->cashbookDays = [];
        $this->invoiceDays = [];

        for ($i = 1; $i <= $diasNoMes; $i++) {
            $data = \Carbon\Carbon::create($anoAtual, $mesAtual, $i)->format('Y-m-d');
            $receita = Cashbook::where('user_id', $userId)
                ->where('type_id', 1)
                ->whereDate('date', $data)
                ->exists();
            $despesa = Cashbook::where('user_id', $userId)
                ->where('type_id', 2)
                ->whereDate('date', $data)
                ->exists();
            if ($receita || $despesa) {
                $this->cashbookDays[] = $i;
            }
            $hasInvoice = Invoice::where('user_id', $userId)
                ->whereDate('invoice_date', $data)
                ->exists();
            if ($hasInvoice) {
                $this->invoiceDays[] = $i;
            }
        }
    }

    public function getDayDetails($date)
    {
        $userId = Auth::id();
        
        // Receitas
        $receitas = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->whereDate('date', $date)
            ->with('category')
            ->get();

        // Despesas
        $despesas = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereDate('date', $date)
            ->with('category')
            ->get();

        // Invoices
        $invoices = Invoice::where('user_id', $userId)
            ->whereDate('invoice_date', $date)
            ->with('category')
            ->get();

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'invoices' => $invoices
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-cashbook');
    }
}
