<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Bank;
use App\Models\Invoice;
use App\Models\Category;
use App\Models\Cofrinho;
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

    // Cofrinhos
    public array $cofrinhos = [];

    // Detalhes do dia e transações recentes
    public ?string $selectedDate = null;
    public array $dayDetails = [];
    public array $recentTransactions = [];
    public ?string $filterType = null;

    public function mount()
    {
        $this->ano = date('Y');
        $this->mesInvoices = now()->month;
        $this->anoInvoices = now()->year;
        $this->loadData();
    }

    public function setFilter($type)
    {
        $this->filterType = $this->filterType === $type ? null : $type;
        $this->loadRecentTransactions();
    }
    
    public function updatedAno()
    {
        $this->loadCashbookData();
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
        $this->loadCashbookData();
        $this->loadBanksData();
        $this->loadInvoicesData();
        $this->loadCategoriasData();
        $this->loadCalendarData();
        $this->loadCofrinhosData();
        $this->loadRecentTransactions();
    }

    public function showDayDetails($date)
    {
        $this->selectedDate = $date;
        $this->dayDetails = $this->getDayDetails($date);
    }

    private function loadRecentTransactions()
    {
        $userId = Auth::id();
        $query = Cashbook::where('user_id', $userId)->with('category');

        if ($this->filterType === 'receitas') {
            $query->where('type_id', 1);
        } elseif ($this->filterType === 'despesas') {
            $query->where('type_id', 2);
        }

        $cashbook = $query->latest()->limit(10)->get();

        if ($this->filterType === 'invoices') {
            $this->recentTransactions = Invoice::where('user_id', $userId)->with('category')->latest('invoice_date')->limit(10)->get()->toArray();
        } elseif (!$this->filterType) {
            $invoices = Invoice::where('user_id', $userId)->with('category')->latest('invoice_date')->limit(5)->get();
            $this->recentTransactions = $cashbook->concat($invoices)->sortByDesc('date')->take(10)->toArray();
        } else {
            $this->recentTransactions = $cashbook->toArray();
        }
    }
    
        private function loadCofrinhosData()
        {
            $userId = Auth::id();
            $cofrinhos = Cofrinho::where('user_id', $userId)->with('cashbooks')->get();
    
            $this->cofrinhos = $cofrinhos->map(function ($cofrinho) {
                $valorGuardado = $cofrinho->cashbooks->sum('value');
                $progresso = ($cofrinho->meta_valor > 0) ? ($valorGuardado / $cofrinho->meta_valor) * 100 : 0;
    
                return [
                    'id' => $cofrinho->id,
                    'nome' => $cofrinho->nome,
                    'meta_valor' => $cofrinho->meta_valor,
                    'valor_guardado' => $valorGuardado,
                    'progresso' => round($progresso, 2),
                    'status' => $cofrinho->status,
                    'icone' => $cofrinho->icone,
                ];
            })->toArray();
        }
    

    private function loadCashbookData()
    {
        $userId = Auth::id();

        // Totais gerais com consulta única
        $totals = Cashbook::where('user_id', $userId)
            ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE 0 END) as total_receitas')
            ->selectRaw('SUM(CASE WHEN type_id = 2 THEN value ELSE 0 END) as total_despesas')
            ->first();

        $this->totalReceitas = $totals->total_receitas ?? 0;
        $this->totalDespesas = $totals->total_despesas ?? 0;
        $this->saldoTotal = $this->totalReceitas - $this->totalDespesas;


        // Dados mensais com consulta única
        $meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];
        $this->dadosReceita = array_fill(0, 12, 0);
        $this->dadosDespesa = array_fill(0, 12, 0);
        $this->saldosMes = array_fill(0, 12, 0);

        $cashbookData = Cashbook::where('user_id', $userId)
            ->whereYear('date', $this->ano)
            ->selectRaw('MONTH(date) as mes, type_id, SUM(value) as total')
            ->groupBy('mes', 'type_id')
            ->get();

        foreach ($cashbookData as $data) {
            $mesIndex = $data->mes - 1;
            if ($data->type_id == 1) {
                $this->dadosReceita[$mesIndex] = (float)$data->total;
            } else {
                $this->dadosDespesa[$mesIndex] = (float)$data->total;
            }
        }

        for ($i = 0; $i < 12; $i++) {
            $this->saldosMes[$i] = $this->dadosReceita[$i] - $this->dadosDespesa[$i];
        }

        // Último mês com movimentação
        $ultimoMes = 0;
        for ($i = 11; $i >= 0; $i--) {
            if ($this->dadosReceita[$i] != 0 || $this->dadosDespesa[$i] != 0) {
                $ultimoMes = $i + 1;
                break;
            }
        }

        if ($ultimoMes > 0) {
            $this->nomeUltimoMes = $meses[$ultimoMes];
            $this->receitaUltimoMes = $this->dadosReceita[$ultimoMes - 1];
            $this->despesaUltimoMes = $this->dadosDespesa[$ultimoMes - 1];
            $this->saldoUltimoMes = $this->saldosMes[$ultimoMes - 1];
        } else {
            $this->nomeUltimoMes = '-';
            $this->receitaUltimoMes = 0;
            $this->despesaUltimoMes = 0;
            $this->saldoUltimoMes = 0;
        }
    }


    private function loadBanksData()
    {
        $userId = Auth::id();
        $this->bancos = Bank::where('user_id', $userId)->get();
        $bankIds = $this->bancos->pluck('id_bank');

        // Busca todos os invoices de uma vez
        $allInvoices = Invoice::where('user_id', $userId)
            ->whereIn('id_bank', $bankIds)
            ->get()
            ->groupBy('id_bank');

        // Informações detalhadas de bancos e invoices
        $this->bancosInfo = $this->bancos->map(function ($bank) use ($allInvoices) {
            $invoices = $allInvoices->get($bank->id_bank, collect());
            $totalInvoices = $invoices->sum('value');
            $qtdInvoices = $invoices->count();
            $mediaInvoices = $qtdInvoices > 0 ? $totalInvoices / $qtdInvoices : 0;
            $maiorInvoice = $qtdInvoices > 0 ? $invoices->sortByDesc('value')->first() : null;
            $menorInvoice = $qtdInvoices > 0 ? $invoices->sortBy('value')->first() : null;

            return [
                'id_bank' => $bank->id_bank,
                'nome' => $bank->name,
                'descricao' => $bank->description,
                'total_invoices' => $totalInvoices,
                'qtd_invoices' => $qtdInvoices,
                'media_invoices' => $mediaInvoices,
                'maior_invoice' => $maiorInvoice,
                'menor_invoice' => $menorInvoice,
                'saldo' => -$totalInvoices,
                'saidas' => $totalInvoices,
            ];
        })->toArray();


        // Totais gerais de bancos
        $this->totalBancos = $this->bancos->count();
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
            $range = [$mesRef->copy()->startOfMonth(), $mesRef->copy()->endOfMonth()];

            $monthlyInvoices = Invoice::where("{$invoiceTable}.user_id", $userId)
                ->whereBetween("{$invoiceTable}.invoice_date", $range)
                ->join($categoryTable, "{$invoiceTable}.category_id", '=', "{$categoryTable}.id_category")
                ->join($bankTable, "{$invoiceTable}.id_bank", '=', "{$bankTable}.id_bank")
                ->selectRaw("
                    {$categoryTable}.name as categoria,
                    {$bankTable}.name as banco,
                    SUM({$invoiceTable}.value) as total
                ")
                ->groupBy("{$categoryTable}.id_category", "{$bankTable}.id_bank", "{$categoryTable}.name", "{$bankTable}.name")
                ->get();

            foreach ($monthlyInvoices as $row) {
                // Preenche gastos por categoria
                if (!isset($categoriasPorMes[$row->categoria])) {
                    $categoriasPorMes[$row->categoria] = array_fill(0, 12, 0);
                }
                $categoriasPorMes[$row->categoria][$i] += (float)$row->total;

                // Preenche gastos por banco
                if (!isset($bancosPorMes[$row->banco])) {
                    $bancosPorMes[$row->banco] = array_fill(0, 12, 0);
                }
                $bancosPorMes[$row->banco][$i] += (float)$row->total;

                // Preenche gastos por categoria+banco
                $compoundKey = $row->categoria . '||' . $row->banco;
                if (!isset($categoriasPorBanco[$compoundKey])) {
                    $categoriasPorBanco[$compoundKey] = array_fill(0, 12, 0);
                }
                $categoriasPorBanco[$compoundKey][$i] += (float)$row->total;
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
        $diasNoMes = \Carbon\Carbon::create($this->anoInvoices, $this->mesInvoices, 1)->daysInMonth;

        $this->diasInvoices = [];
        $valoresInvoicesTemp = array_fill(1, $diasNoMes, 0);

        for ($i = 1; $i <= $diasNoMes; $i++) {
            $this->diasInvoices[] = \Carbon\Carbon::create($this->anoInvoices, $this->mesInvoices, $i)->format('d/m');
        }

        $invoicesData = Invoice::where('user_id', $userId)
            ->whereYear('invoice_date', $this->anoInvoices)
            ->whereMonth('invoice_date', $this->mesInvoices)
            ->selectRaw('DAY(invoice_date) as dia, SUM(value) as total')
            ->groupBy('dia')
            ->get();

        foreach ($invoicesData as $data) {
            $valoresInvoicesTemp[$data->dia] = (float)$data->total;
        }

        $this->valoresInvoices = array_values($valoresInvoicesTemp);
    }

    private function loadCategoriasData()
    {
        $userId = Auth::id();
        $invoiceTable = (new Invoice)->getTable();
        $categoryTable = (new Category)->getTable();

        // Busca invoices com categorias agrupadas (top 10) para o ano selecionado
        $invoicesPorCategoria = Invoice::where("{$invoiceTable}.user_id", $userId)
            ->whereYear("{$invoiceTable}.invoice_date", $this->ano)
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

        $this->cashbookDays = Cashbook::where('user_id', $userId)
            ->whereYear('date', $anoAtual)
            ->whereMonth('date', $mesAtual)
            ->distinct()
            ->pluck('date')
            ->map(fn($date) => (new \Carbon\Carbon($date))->day)
            ->toArray();

        $this->invoiceDays = Invoice::where('user_id', $userId)
            ->whereYear('invoice_date', $anoAtual)
            ->whereMonth('invoice_date', $mesAtual)
            ->distinct()
            ->pluck('invoice_date')
            ->map(fn($date) => (new \Carbon\Carbon($date))->day)
            ->toArray();
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
