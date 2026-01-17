<?php

namespace App\Livewire\Dashboard;

use App\Models\Cashbook;
use App\Models\Invoice;
use App\Models\Category;
use App\Models\Cofrinho;
use App\Models\Orcamento;
use App\Exports\CashbookExport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
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

    // Orçamentos
    public float $orcamentoMesTotal = 0;
    public float $orcamentoMesUsado = 0;
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

    public function mount()
    {
        $this->ano = date('Y');
        $this->mes = date('n');
        $this->loadData();
    }

    public function setFilter($type)
    {
        $this->filterType = $this->filterType === $type ? null : $type;
        $this->loadRecentTransactions();
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
        $this->loadCashbookData();
        $this->loadInvoicesData();
        $this->loadCategoriasData();
        $this->loadCalendarData();
        $this->loadCofrinhosData();
        $this->loadCofrinhosStats();
        $this->loadEvolucaoCofrinhos();
        $this->loadOrcamentoData();
        $this->loadPrevisoesData();
        $this->loadRecentTransactions();
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
        $cashbooks = Cashbook::where('user_id', auth()->id())
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
        $userId = Auth::id();
        $query = Cashbook::where('user_id', $userId)->with('category');

        if ($this->filterType === 'receitas') {
            $query->where('type_id', 1);
        } elseif ($this->filterType === 'despesas') {
            $query->where('type_id', 2);
        }

        // Filtro por cofrinho
        if ($this->cofrinhoFiltro) {
            $query->where('cofrinho_id', $this->cofrinhoFiltro);
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
        $cofrinhos = Cofrinho::where('user_id', $userId)->get();

        $this->cofrinhos = $cofrinhos->map(function ($cofrinho) {
            // LÓGICA CORRIGIDA:
            // type_id=1 (receita) = dinheiro ENTRANDO no cofrinho (guardando dinheiro) - ADICIONA
            // type_id=2 (despesa) = dinheiro SAINDO do cofrinho (retirando dinheiro) - SUBTRAI
            $entradas = Cashbook::where('user_id', auth()->id())
                ->where('cofrinho_id', $cofrinho->id)
                ->where('type_id', 1)
                ->sum('value');
            $saidas = Cashbook::where('user_id', auth()->id())
                ->where('cofrinho_id', $cofrinho->id)
                ->where('type_id', 2)
                ->sum('value');

            $valorGuardado = $entradas - $saidas;
            $progresso = ($cofrinho->meta_valor > 0) ? ($valorGuardado / $cofrinho->meta_valor) * 100 : 0;

            return [
                'id' => $cofrinho->id,
                'nome' => $cofrinho->nome,
                'meta_valor' => $cofrinho->meta_valor,
                'valor_guardado' => $valorGuardado,
                'progresso' => round($progresso, 2),
                'status' => $cofrinho->status,
                'icone' => $cofrinho->icone,
                'link' => route('cofrinhos.show', $cofrinho->id),
            ];
        })->toArray();
    }

    private function loadCofrinhosStats()
    {
        $userId = Auth::id();

        // Total acumulado em todos os cofrinhos
        $this->totalCofrinhos = collect($this->cofrinhos)->sum('valor_guardado');

        // Total de todas as metas
        $this->totalMetasCofrinhos = collect($this->cofrinhos)->sum('meta_valor');

        // Top 3 cofrinhos mais próximos da meta
        $this->cofrinhosTopMeta = collect($this->cofrinhos)
            ->filter(fn($c) => $c['progresso'] < 100)
            ->sortByDesc('progresso')
            ->take(3)
            ->values()
            ->toArray();

        // Economizado neste mês (entradas em cofrinhos)
        $this->economiadoMesAtual = Cashbook::where('user_id', $userId)
            ->whereNotNull('cofrinho_id')
            ->where('type_id', 1)
            ->whereMonth('date', $this->mes)
            ->whereYear('date', $this->ano)
            ->sum('value');

        // Economizado no mês anterior
        $mesAnterior = $this->mes == 1 ? 12 : $this->mes - 1;
        $anoAnterior = $this->mes == 1 ? $this->ano - 1 : $this->ano;
        $this->economiadoMesAnterior = Cashbook::where('user_id', $userId)
            ->whereNotNull('cofrinho_id')
            ->where('type_id', 1)
            ->whereMonth('date', $mesAnterior)
            ->whereYear('date', $anoAnterior)
            ->sum('value');
    }

    private function loadEvolucaoCofrinhos()
    {
        $userId = Auth::id();
        $this->evolucaoCofrinhos = array_fill(0, 12, 0);

        // Para cada mês, calcular o valor ACUMULADO total até aquele mês
        for ($m = 1; $m <= 12; $m++) {
            $entradas = Cashbook::where('user_id', $userId)
                ->whereNotNull('cofrinho_id')
                ->where('type_id', 1)
                ->whereYear('date', $this->ano)
                ->whereMonth('date', '<=', $m)
                ->sum('value');

            $saidas = Cashbook::where('user_id', $userId)
                ->whereNotNull('cofrinho_id')
                ->where('type_id', 2)
                ->whereYear('date', $this->ano)
                ->whereMonth('date', '<=', $m)
                ->sum('value');

            $this->evolucaoCofrinhos[$m - 1] = (float)($entradas - $saidas);
        }
    }

    private function loadOrcamentoData()
    {
        $userId = Auth::id();
        // Orçamentos do mês
        $this->orcamentoMesTotal = (float) Orcamento::where('user_id', $userId)
            ->where('mes', $this->mes)
            ->where('ano', $this->ano)
            ->sum('valor');

        // Orçamento usado (despesas do mês)
        $this->orcamentoMesUsado = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereMonth('date', $this->mes)
            ->whereYear('date', $this->ano)
            ->sum('value');

        // Top categorias que mais estouraram orçamento (até 5)
        $orcamentosMes = Orcamento::where('user_id', $userId)
            ->where('mes', $this->mes)
            ->where('ano', $this->ano)
            ->with('category:id_category,name')
            ->get();

        $gastosPorCategoriaMes = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereMonth('date', $this->mes)
            ->whereYear('date', $this->ano)
            ->selectRaw('category_id, SUM(value) as total')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $estouros = [];
        foreach ($orcamentosMes as $orc) {
            $gasto = (float) ($gastosPorCategoriaMes[$orc->category_id]->total ?? 0);
            $orcado = (float) $orc->valor;
            $diff = $gasto - $orcado;
            if ($diff > 0) {
                $estouros[] = [
                    'category' => $orc->category?->name ?? ('Cat ' . $orc->category_id),
                    'orcado' => $orcado,
                    'gasto' => $gasto,
                    'estouro' => $diff,
                ];
            }
        }
        usort($estouros, fn($a, $b) => $b['estouro'] <=> $a['estouro']);
        $this->orcamentosTopEstouro = array_slice($estouros, 0, 5);
    }

    private function loadPrevisoesData()
    {
        $userId = Auth::id();
        $hoje = now();
        $inicio = $hoje->copy()->subDays(90);

        $transacoes = Cashbook::where('user_id', $userId)
            ->whereBetween('date', [$inicio, $hoje])
            ->selectRaw('type_id, SUM(value) as total')
            ->groupBy('type_id')
            ->get()
            ->keyBy('type_id');

        $receitas90dias = $transacoes[1]->total ?? 0;
        $despesas90dias = $transacoes[2]->total ?? 0;

        $mediaDiariaReceitas = $receitas90dias / 90;
        $mediaDiariaDespesas = $despesas90dias / 90;

        $saldoAtual = $this->saldoTotal;

        $this->previsao30dias = $saldoAtual + (($mediaDiariaReceitas - $mediaDiariaDespesas) * 30);
        $this->previsao60dias = $saldoAtual + (($mediaDiariaReceitas - $mediaDiariaDespesas) * 60);
        $this->previsao90dias = $saldoAtual + (($mediaDiariaReceitas - $mediaDiariaDespesas) * 90);
    }

    private function loadCashbookData()
    {
        $userId = Auth::id();

        // Totais gerais com consulta única (EXCLUINDO transações de cofrinho - movimentações internas)
        $totals = Cashbook::where('user_id', $userId)
            ->whereYear('date', $this->ano)
            ->whereNull('cofrinho_id')
            ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE 0 END) as total_receitas')
            ->selectRaw('SUM(CASE WHEN type_id = 2 THEN value ELSE 0 END) as total_despesas')
            ->first();

        $this->totalReceitas = $totals->total_receitas ?? 0;
        $this->totalDespesas = $totals->total_despesas ?? 0;
        $this->saldoTotal = Cashbook::where('user_id', $userId)
            ->whereNull('cofrinho_id')
            ->where('type_id', 1)->sum('value') - Cashbook::where('user_id', $userId)
            ->whereNull('cofrinho_id')
            ->where('type_id', 2)->sum('value');


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
            ->whereNull('cofrinho_id')
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

    private function loadInvoicesData()
    {
        $userId = Auth::id();
        $diasNoMes = \Carbon\Carbon::create($this->ano, $this->mes, 1)->daysInMonth;

        $this->diasInvoices = [];
        $valoresInvoicesTemp = array_fill(1, $diasNoMes, 0);

        for ($i = 1; $i <= $diasNoMes; $i++) {
            $this->diasInvoices[] = \Carbon\Carbon::create($this->ano, $this->mes, $i)->format('d/m');
        }

        $invoicesData = Invoice::where('user_id', $userId)
            ->whereYear('invoice_date', $this->ano)
            ->whereMonth('invoice_date', $this->mes)
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
        $mesAtual = $this->mes;
        $anoAtual = $this->ano;

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
