<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Bank;
use App\Models\Invoice;
use App\Models\Category;
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
    public array $gastosMensaisMeses = [];
    public array $gastosMensaisPorCategoria = [];
    public array $gastosMensaisPorBanco = [];
    public array $gastosMensaisPorCategoriaBanco = [];

    // Gráfico diário de invoices
    public array $diasInvoices = [];
    public array $valoresInvoices = [];

    // Gráfico de categorias
    public array $categorias = [];
    public array $valoresCategorias = [];

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

        // Dados de categorias
        $this->loadCategoriasData();

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

        // Gastos mensais de invoices agrupados por categoria e banco (últimos 12 meses)
        $this->gastosMensaisMeses = [];
        $categoriasPorMes = [];
        $bancosPorMes = [];
        $categoriasPorBanco = [];

        $invoiceTable = (new Invoice)->getTable();
        $categoryTable = (new Category)->getTable();
        $bankTable = (new Bank)->getTable();

        $mesRef = now()->copy()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mesLabel = $mesRef->locale('pt_BR')->format('M/y');
            $this->gastosMensaisMeses[] = $mesLabel;

            // Gastos por categoria no mês
            $gastosCategorias = Invoice::where("{$invoiceTable}.user_id", $userId)
                ->whereBetween("{$invoiceTable}.invoice_date", [$mesRef->copy()->startOfMonth(), $mesRef->copy()->endOfMonth()])
                ->join($categoryTable, "{$invoiceTable}.category_id", '=', "{$categoryTable}.id_category")
                ->selectRaw("{$categoryTable}.name as categoria, SUM({$invoiceTable}.value) as total")
                ->groupBy("{$categoryTable}.id_category", "{$categoryTable}.name")
                ->get();

            foreach ($gastosCategorias as $cat) {
                if (!isset($categoriasPorMes[$cat->categoria])) {
                    $categoriasPorMes[$cat->categoria] = array_fill(0, 12, 0);
                }
                $categoriasPorMes[$cat->categoria][$i] = (float)$cat->total;
            }

            // Gastos por categoria+bank no mês
            $gastosCatBanco = Invoice::where("{$invoiceTable}.user_id", $userId)
                ->whereBetween("{$invoiceTable}.invoice_date", [$mesRef->copy()->startOfMonth(), $mesRef->copy()->endOfMonth()])
                ->join($categoryTable, "{$invoiceTable}.category_id", '=', "{$categoryTable}.id_category")
                ->join($bankTable, "{$invoiceTable}.id_bank", '=', "{$bankTable}.id_bank")
                ->selectRaw("{$categoryTable}.name as categoria, {$bankTable}.name as banco, SUM({$invoiceTable}.value) as total")
                ->groupBy("{$categoryTable}.id_category", "{$bankTable}.id_bank", "{$categoryTable}.name", "{$bankTable}.name")
                ->get();

            foreach ($gastosCatBanco as $row) {
                $keyCat = $row->categoria;
                $keyBanco = $row->banco;
                $compoundKey = $keyCat . '||' . $keyBanco;
                if (!isset($categoriasPorBanco[$compoundKey])) {
                    $categoriasPorBanco[$compoundKey] = array_fill(0, 12, 0);
                }
                $categoriasPorBanco[$compoundKey][$i] = (float)$row->total;
            }

            // Gastos por banco no mês
            $gastosBancos = Invoice::where("{$invoiceTable}.user_id", $userId)
                ->whereBetween("{$invoiceTable}.invoice_date", [$mesRef->copy()->startOfMonth(), $mesRef->copy()->endOfMonth()])
                ->join($bankTable, "{$invoiceTable}.id_bank", '=', "{$bankTable}.id_bank")
                ->selectRaw("{$bankTable}.name as banco, SUM({$invoiceTable}.value) as total")
                ->groupBy("{$bankTable}.id_bank", "{$bankTable}.name")
                ->get();

            foreach ($gastosBancos as $banco) {
                if (!isset($bancosPorMes[$banco->banco])) {
                    $bancosPorMes[$banco->banco] = array_fill(0, 12, 0);
                }
                $bancosPorMes[$banco->banco][$i] = (float)$banco->total;
            }

            $mesRef->addMonth();
        }

        // Pega top 5 categorias e top 3 bancos
        $topCategorias = collect($categoriasPorMes)
            ->sortByDesc(fn($valores) => array_sum($valores))
            ->take(5);

        $topBancos = collect($bancosPorMes)
            ->sortByDesc(fn($valores) => array_sum($valores))
            ->take(3);

        $this->gastosMensaisPorCategoria = $topCategorias->toArray();
        $this->gastosMensaisPorBanco = $topBancos->toArray();

        // Filtra combinações categoria+banco para incluir apenas top categorias e top bancos
        $topCategoriaKeys = array_keys($this->gastosMensaisPorCategoria);
        $topBancoKeys = array_keys($this->gastosMensaisPorBanco);

        $filteredCatBanco = [];
        foreach ($categoriasPorBanco as $compound => $values) {
            [$catName, $bankName] = explode('||', $compound);
            if (in_array($catName, $topCategoriaKeys) && in_array($bankName, $topBancoKeys)) {
                $label = $catName . ' — ' . $bankName;
                $filteredCatBanco[$label] = $values;
            }
        }

        $this->gastosMensaisPorCategoriaBanco = $filteredCatBanco;
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

    private function loadCategoriasData()
    {
        $userId = Auth::id();
        // Usa os nomes corretos de tabela/coluna a partir do model
        $invoiceTable = (new Invoice)->getTable();
        $categoryTable = (new Category)->getTable();

        // Busca invoices com categorias agrupadas (top 10)
        $invoicesPorCategoria = Invoice::where("{$invoiceTable}.user_id", $userId)
            ->join($categoryTable, "{$invoiceTable}.category_id", '=', "{$categoryTable}.id_category")
            ->selectRaw("{$categoryTable}.name as categoria, SUM({$invoiceTable}.value) as total")
            ->groupBy("{$categoryTable}.id_category", "{$categoryTable}.name")
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $this->categorias = $invoicesPorCategoria->pluck('categoria')->toArray();
        $this->valoresCategorias = $invoicesPorCategoria->pluck('total')->map(fn($v) => (float)$v)->toArray();
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
