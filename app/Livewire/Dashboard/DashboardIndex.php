<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use App\Models\Cashbook;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Invoice;
use App\Models\Bank;
use App\Models\Cofrinho;
use App\Models\GoalActivity;
use App\Models\Consortium;
use App\Models\ConsortiumParticipant;
use App\Models\ConsortiumPayment;
use App\Models\ConsortiumContemplation;
use App\Models\MercadoLivreSyncLog;
use App\Models\MlStockLog;
use App\Models\ShopeeSyncLog;
use App\Models\VendaParcela;
use App\Models\LancamentoRecorrente;
use App\Models\Orcamento;
use App\Models\Category;
use App\Models\CashbookUploadHistory;
use App\Models\InvoiceUploadHistory;
use App\Models\ProductUploadHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class DashboardIndex extends Component
{
    // Dados do dashboard principal
    public int $selectedMonth;
    public int $selectedYear;
    public string $periodPreset = 'month';
    public array $availableYears = [];
    public array $monthOptions = [];

    public float $totalCashbook = 0;
    public int $totalProdutos = 0;
    public int $totalProdutosEstoque = 0;
    public int $totalClientes = 0;
    public int $clientesComSalesPendentes = 0;
    public float $totalFaturamento = 0;
    public float $totalFaltante = 0;
    public $ultimaMovimentacaoCashbook = null;
    public $produtoMaiorEstoque = null;
    public $ultimaVenda = null;
    public $produtoMaisVendido = null;

    // Novas variáveis para o mês atual
    public int $salesMonth = 0;
    public float $ticketMedio = 0;
    public int $clientesNovosMes = 0;
    public int $produtosEstoqueBaixo = 0;

    // Variáveis adicionais para dashboard completo
    public int $clientesInadimplentes = 0;
    public int $aniversariantesMes = 0;
    public int $produtosVendidosMes = 0;
    public int $produtosCadastrados = 0;
    public float $saldoCaixa = 0;
    public float $contasPagar = 0;
    public float $contasReceber = 0;
    public float $fornecedoresPagar = 0;
    public float $despesasFixas = 0;
    public float $clientesReceber = 0;
    public float $outrosReceber = 0;

    // Novos dados para gráficos
    public float $valorVendas = 0;
    public float $custoEstoque = 0;
    public float $custoProdutosVendidos = 0;
    public array $gastosInvoiceMensal = [];
    public array $gastosInvoicePorBanco = [];
    public float $margemLucro = 0;
    public float $taxaCrescimento = 0;
    public int $produtosAtivos = 0;

    // Controle de seções expandidas/colapsadas
    public bool $vendasExpanded = true;
    public bool $produtosExpanded = true;
    public bool $clientesExpanded = true;
    public bool $faturasExpanded = false;
    public bool $bancosExpanded = false;
    public bool $consorciosExpanded = false;

    // Novos dados para seções adicionais
    public int $totalBancos = 0;
    public int $totalInvoices = 0;
    public int $totalCofrinhos = 0;
    public float $totalEconomizado = 0;
    public int $totalConsorciosAtivos = 0;
    public int $proximosSorteios = 0;
    public int $consorcioParticipantesAtivos = 0;
    public float $consorcioPagamentosPendentesTotal = 0;
    public int $consorcioContemplacoesTotal = 0;

    public array $alertas = [];
    public array $atividades = [];
    public float $lucroLiquido = 0;
    public float $receitasPeriodo = 0;
    public float $despesasPeriodo = 0;

    // Contas reais (pendências)
    public float $contasReceberPendentes = 0;
    public float $contasPagarPendentes = 0;
    public int $parcelasVencidasCount = 0;
    public float $parcelasVencidasValor = 0;

    // Orçamentos
    public float $orcamentoMesTotal = 0;
    public float $orcamentoMesUsado = 0;
    public array $orcamentosTopEstouro = [];

    // Recorrências
    public int $recorrentesAtivas = 0;
    public float $recorrentesProx30Total = 0;

    // Invoices (resumo)
    public float $invoicesProxVenc30Total = 0;

    // Gráficos adicionais
    public array $cashflowMonthly = [];
    public array $expensesByCategory = [];
    public array $periodComparison = [];

    // Saúde do sistema (últimos uploads)
    public ?array $lastUploads = null;

    public int $activityFeedLimit = 40;

    public function mount()
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

    public function applyPeriodPreset(string $preset): void
    {
        if (!in_array($preset, ['today', 'month', 'quarter', 'year'], true)) {
            return;
        }

        $this->periodPreset = $preset;

        if (in_array($preset, ['today', 'month', 'quarter', 'year'], true)) {
            $this->selectedMonth = (int) now()->month;
            $this->selectedYear = (int) now()->year;
        }

        $this->loadDashboardData();
    }

    public function toggleSection($section)
    {
        switch ($section) {
            case 'vendas':
                $this->vendasExpanded = !$this->vendasExpanded;
                break;
            case 'produtos':
                $this->produtosExpanded = !$this->produtosExpanded;
                break;
            case 'clientes':
                $this->clientesExpanded = !$this->clientesExpanded;
                break;
            case 'faturas':
                $this->faturasExpanded = !$this->faturasExpanded;
                break;
            case 'bancos':
                $this->bancosExpanded = !$this->bancosExpanded;
                break;
            case 'consorcios':
                $this->consorciosExpanded = !$this->consorciosExpanded;
                break;
        }
    }

    public function refreshData()
    {
        Cache::forget('dashboard_data_' . Auth::id());
        $this->loadDashboardData();
        $this->dispatch('notify', ['message' => 'Dashboard atualizado!', 'type' => 'success']);
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();
        $referenceDate = $this->getReferenceDate();
        [$periodStart, $periodEnd] = $this->getPeriodBounds($referenceDate);
        $previousPeriod = $referenceDate->copy()->subMonth();
        $previousPeriodStart = $previousPeriod->copy()->startOfMonth()->startOfDay();
        $previousPeriodEnd = $previousPeriod->copy()->endOfMonth()->endOfDay();
        $rollingTwelveStart = $referenceDate->copy()->subMonths(11)->startOfMonth()->startOfDay();
        $rollingSixStart = $referenceDate->copy()->subMonths(5)->startOfMonth()->startOfDay();
        $windowThirtyEnd = $periodStart->copy()->addDays(30)->endOfDay();
        [$comparisonPreviousStart, $comparisonPreviousEnd, $comparisonLastYearStart, $comparisonLastYearEnd] = $this->getComparisonRanges($periodStart, $periodEnd);

        // Saldo total do cashbook (EXCLUINDO transações de cofrinho - movimentações internas)
        $totalReceitas = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->whereNull('cofrinho_id')
            ->sum('value');
        $totalDespesas = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->sum('value');
        $this->totalCashbook = $totalReceitas - $totalDespesas;
        $this->saldoCaixa = $this->totalCashbook;

        // Produtos
        $this->totalProdutos = Product::where('user_id', $userId)->count();
        $this->totalProdutosEstoque = Product::where('user_id', $userId)->sum('stock_quantity');
        $this->produtosCadastrados = $this->totalProdutos;

        // Produtos vendidos no mês
        $this->produtosVendidosMes = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->sum('sale_items.quantity');

        // Clientes
        $this->totalClientes = Client::where('user_id', $userId)->count();
        $this->clientesComSalesPendentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            })->count();

        // Clientes inadimplentes (com vendas pendentes)
        $this->clientesInadimplentes = $this->clientesComSalesPendentes;

        // Aniversariantes do mês (coluna não existe, comentar para evitar erro)
        // $this->aniversariantesMes = Client::where('user_id', $userId)
        //     ->whereMonth('data_nascimento', now()->month)
        //     ->count();
        $this->aniversariantesMes = 0;

        // Faturamento
        $this->totalFaturamento = Sale::where('user_id', $userId)->sum('total_price');

        // Valor faltante (vendas pendentes - pagamentos)
        $salesPendentes = Sale::where('user_id', $userId)->where('status', 'pendente')->get(['id', 'total_price']);
        $idsPendentes = $salesPendentes->pluck('id');
        $totalPendentes = $salesPendentes->sum('total_price');
        $totalPagamentos = DB::table('sale_payments')
            ->whereIn('sale_id', $idsPendentes)
            ->sum('amount_paid');
        $this->totalFaltante = $totalPendentes - $totalPagamentos;

        // Card Cashbook: última movimentação
        $this->ultimaMovimentacaoCashbook = Cashbook::where('user_id', $userId)
            ->orderByDesc('date')
            ->first();

        // Card Produtos: produto com maior estoque
        $this->produtoMaiorEstoque = Product::where('user_id', $userId)
            ->orderByDesc('stock_quantity')
            ->first();

        // Produto mais vendido (nome) - Otimizado com groupBy correto
        $this->produtoMaisVendido = SaleItem::select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_vendido')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('products.user_id', $userId)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_vendido')
            ->first();

        // Card Vendas: última venda
        $this->ultimaVenda = Sale::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        // Vendas no mês atual
        $salesMonth = Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Ticket médio do mês
        $totalVendasMes = Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');
        $ticketMedio = $salesMonth > 0 ? $totalVendasMes / $salesMonth : 0;

        // Novos clientes no mês
        $clientesNovosMes = Client::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Produtos com estoque baixo (exemplo: <= 5)
        $produtosEstoqueBaixo = Product::where('user_id', $userId)
            ->where('stock_quantity', '<=', 5)
            ->count();

        // Contas a pagar/receber (HISTÓRICO) - manter como totais gerais, EXCLUINDO cofrinhos
        $this->contasPagar = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->sum('value');
        $this->contasReceber = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->whereNull('cofrinho_id')
            ->sum('value');

        // Recorrências (precisa vir antes do cálculo de contas a pagar pendentes)
        $this->recorrentesAtivas = (int) LancamentoRecorrente::where('user_id', $userId)
            ->where('ativo', 1)
            ->count();

        $this->recorrentesProx30Total = (float) LancamentoRecorrente::where('user_id', $userId)
            ->where('ativo', 1)
            ->whereNotNull('proximo_vencimento')
            ->where('proximo_vencimento', '>=', $periodStart)
            ->where('proximo_vencimento', '<=', $windowThirtyEnd)
            ->sum('valor');

        // Contas reais (pendências)
        // A receber: parcelas pendentes
        $this->contasReceberPendentes = (float) VendaParcela::join('sales', 'venda_parcelas.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('venda_parcelas.status', 'pendente')
            ->sum('venda_parcelas.valor');

        // Parcelas vencidas
        $this->parcelasVencidasCount = (int) VendaParcela::join('sales', 'venda_parcelas.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('venda_parcelas.status', 'pendente')
            ->where('venda_parcelas.data_vencimento', '<', $periodEnd)
            ->count();

        $this->parcelasVencidasValor = (float) VendaParcela::join('sales', 'venda_parcelas.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('venda_parcelas.status', 'pendente')
            ->where('venda_parcelas.data_vencimento', '<', $periodEnd)
            ->sum('venda_parcelas.valor');

        // A pagar: aproximar via invoices nos próximos 30 dias (se a coluna de vencimento existir)
        $invoiceDateColumn = Schema::hasColumn('invoice', 'due_date') ? 'due_date' : 'invoice_date';
        $this->invoicesProxVenc30Total = (float) Invoice::where('user_id', $userId)
            ->where($invoiceDateColumn, '>=', $periodStart)
            ->where($invoiceDateColumn, '<=', $windowThirtyEnd)
            ->sum('value');

        $this->contasPagarPendentes = $this->invoicesProxVenc30Total + $this->recorrentesProx30Total;

        // Fornecedores a pagar (exemplo: cashbook category_id = 2), EXCLUINDO cofrinhos
        $this->fornecedoresPagar = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->where('category_id', 2)
            ->whereNull('cofrinho_id')
            ->sum('value');
        // Despesas fixas (exemplo: cashbook category_id = 1), EXCLUINDO cofrinhos
        $this->despesasFixas = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->where('category_id', 1)
            ->whereNull('cofrinho_id')
            ->sum('value');

        // Clientes a receber (exemplo: cashbook type_id = 1, category_id = 3), EXCLUINDO cofrinhos
        $this->clientesReceber = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->where('category_id', 3)
            ->whereNull('cofrinho_id')
            ->sum('value');
        // Outros a receber (exemplo: cashbook type_id = 1, category_id != 3), EXCLUINDO cofrinhos
        $this->outrosReceber = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->where('category_id', '!=', 3)
            ->whereNull('cofrinho_id')
            ->sum('value');

        // Guardar para uso na view
        $this->salesMonth = $salesMonth;
        $this->ticketMedio = $ticketMedio;
        $this->clientesNovosMes = $clientesNovosMes;
        $this->produtosEstoqueBaixo = $produtosEstoqueBaixo;

        // Novos cálculos para gráficos
        // Valor total de vendas
        $this->valorVendas = $this->totalFaturamento;

        // Custo dos produtos em estoque (cost_price * stock_quantity)
        $this->custoEstoque = Product::where('user_id', $userId)
            ->selectRaw('SUM(price * stock_quantity) as total_custo')
            ->value('total_custo') ?? 0;

        // Custo dos produtos vendidos (cost_price * quantity)
        $this->custoProdutosVendidos = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->selectRaw('SUM(products.price * sale_items.quantity) as total_custo')
            ->value('total_custo') ?? 0;

        // Gastos mensais de invoices (últimos 6 meses)
        // Ajuste para o schema do seu banco: Invoice usa 'invoice_date' e 'value'
        $monthExpr = $this->monthExpression('invoice_date');

        $gastosInvoice = Invoice::where('user_id', $userId)
            ->where('invoice_date', '>=', $rollingSixStart)
            ->where('invoice_date', '<=', $periodEnd)
            ->selectRaw("{$monthExpr} as mes, SUM(value) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $this->gastosInvoiceMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = $referenceDate->copy()->subMonths($i)->month;
            $gasto = $gastosInvoice->firstWhere('mes', $mes);
            $this->gastosInvoiceMensal[] = $gasto ? (float) $gasto->total : 0;
        }

        // Agrupar gastos por banco (últimos 6 meses)
        $gastosPorBancoRaw = Invoice::where('user_id', $userId)
            ->where('invoice_date', '>=', $rollingSixStart)
            ->where('invoice_date', '<=', $periodEnd)
            ->selectRaw("{$monthExpr} as mes, id_bank, SUM(value) as total")
            ->groupBy('mes', 'id_bank')
            ->orderBy('mes')
            ->get();

        // Lista de bancos usados
        $bankIds = $gastosPorBancoRaw->pluck('id_bank')->unique()->filter()->values();
        $seriesByBank = [];

        foreach ($bankIds as $bankId) {
            $bank = Bank::find($bankId);
            $name = $bank ? $bank->name : ('Banco ' . $bankId);
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $mes = $referenceDate->copy()->subMonths($i)->month;
                $row = $gastosPorBancoRaw->firstWhere(function($item) use ($mes, $bankId) {
                    return (int)$item->mes === (int)$mes && (int)$item->id_bank === (int)$bankId;
                });
                $data[] = $row ? (float)$row->total : 0;
            }
            $seriesByBank[] = [
                'name' => $name,
                'data' => $data,
            ];
        }

        $this->gastosInvoicePorBanco = $seriesByBank;

        // Indicadores de performance
        // Margem de lucro
        $this->margemLucro = $this->valorVendas > 0 ?
            (($this->valorVendas - $this->custoProdutosVendidos) / $this->valorVendas) * 100 : 0;

        // Taxa de crescimento (comparar mês atual com mês anterior)
        $vendasMesAnterior = Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('total_price');

        $vendasMesAtual = Sale::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');

        $this->taxaCrescimento = $vendasMesAnterior > 0 ?
            (($vendasMesAtual - $vendasMesAnterior) / $vendasMesAnterior) * 100 : 0;

        // Produtos ativos (com estoque > 0)
        $this->produtosAtivos = Product::where('user_id', $userId)
            ->where('stock_quantity', '>', 0)
            ->count();

        // Dados de Bancos e Cofrinhos
        $this->totalBancos = Bank::where('user_id', $userId)->count();
        $this->totalInvoices = Invoice::where('user_id', $userId)->count();
        $this->totalCofrinhos = Cofrinho::where('user_id', $userId)->where('status', 'ativo')->count();

        // Total economizado nos cofrinhos
        // LÓGICA CORRIGIDA:
        // type_id=1 (receita) = dinheiro ENTRANDO no cofrinho (guardando) - ADICIONA
        // type_id=2 (despesa) = dinheiro SAINDO do cofrinho (retirando) - SUBTRAI
        $cofrinhos = Cofrinho::where('user_id', $userId)->where('status', 'ativo')->get();
        $this->totalEconomizado = 0;
        foreach ($cofrinhos as $cofrinho) {
            $entradas = Cashbook::where('user_id', $userId)
                ->where('cofrinho_id', $cofrinho->id)
                ->where('type_id', 1)
                ->sum('value');
            $saidas = Cashbook::where('user_id', $userId)
                ->where('cofrinho_id', $cofrinho->id)
                ->where('type_id', 2)
                ->sum('value');
            $this->totalEconomizado += ($entradas - $saidas);
        }

        // Dados de Consórcios
        $this->totalConsorciosAtivos = Consortium::where('user_id', $userId)->where('status', 'active')->count();

        $this->consorcioParticipantesAtivos = (int) ConsortiumParticipant::join('consortiums', 'consortium_participants.consortium_id', '=', 'consortiums.id')
            ->where('consortiums.user_id', $userId)
            ->where('consortiums.status', 'active')
            ->where('consortium_participants.status', 'active')
            ->count();

        // Pagamentos pendentes (schema real: consortium_payments -> consortium_participant_id)
        // consortium_participants possui consortium_id, então fazemos join payments -> participants -> consortiums
        $this->consorcioPagamentosPendentesTotal = (float) ConsortiumPayment::join('consortium_participants', 'consortium_payments.consortium_participant_id', '=', 'consortium_participants.id')
            ->join('consortiums', 'consortium_participants.consortium_id', '=', 'consortiums.id')
            ->where('consortiums.user_id', $userId)
            ->where('consortiums.status', 'active')
            ->where('consortium_payments.status', 'pending')
            ->sum('consortium_payments.amount');

        // Contemplações (schema real: consortium_contemplations -> consortium_participant_id)
        $this->consorcioContemplacoesTotal = (int) ConsortiumContemplation::join('consortium_participants', 'consortium_contemplations.consortium_participant_id', '=', 'consortium_participants.id')
            ->join('consortiums', 'consortium_participants.consortium_id', '=', 'consortiums.id')
            ->where('consortiums.user_id', $userId)
            ->count();

        // Calcular próximos sorteios baseado em consortium_draws
        $this->proximosSorteios = DB::table('consortium_draws')
            ->join('consortiums', 'consortium_draws.consortium_id', '=', 'consortiums.id')
            ->where('consortiums.user_id', $userId)
            ->where('consortiums.status', 'active')
            ->where('consortium_draws.draw_date', '>=', $periodStart)
            ->where('consortium_draws.draw_date', '<=', $windowThirtyEnd)
            ->whereNull('consortiums.deleted_at')
            ->count();

        // Calcular lucro líquido (receitas - despesas do período)
        $this->receitasPeriodo = Cashbook::where('user_id', $userId)
            ->where('type_id', 1)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('value');

        $this->despesasPeriodo = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('value');

        $this->lucroLiquido = $this->receitasPeriodo - $this->despesasPeriodo;

        $this->periodComparison = [
            'labels' => ['Atual', 'Anterior', 'Ano passado'],
            'sales' => [
                (float) Sale::where('user_id', $userId)->whereBetween('created_at', [$periodStart, $periodEnd])->sum('total_price'),
                (float) Sale::where('user_id', $userId)->whereBetween('created_at', [$comparisonPreviousStart, $comparisonPreviousEnd])->sum('total_price'),
                (float) Sale::where('user_id', $userId)->whereBetween('created_at', [$comparisonLastYearStart, $comparisonLastYearEnd])->sum('total_price'),
            ],
            'income' => [
                $this->receitasPeriodo,
                (float) Cashbook::where('user_id', $userId)->where('type_id', 1)->whereBetween('date', [$comparisonPreviousStart, $comparisonPreviousEnd])->sum('value'),
                (float) Cashbook::where('user_id', $userId)->where('type_id', 1)->whereBetween('date', [$comparisonLastYearStart, $comparisonLastYearEnd])->sum('value'),
            ],
            'expenses' => [
                $this->despesasPeriodo,
                (float) Cashbook::where('user_id', $userId)->where('type_id', 2)->whereBetween('date', [$comparisonPreviousStart, $comparisonPreviousEnd])->sum('value'),
                (float) Cashbook::where('user_id', $userId)->where('type_id', 2)->whereBetween('date', [$comparisonLastYearStart, $comparisonLastYearEnd])->sum('value'),
            ],
        ];

        // Orçamentos do mês
        $this->orcamentoMesTotal = (float) Orcamento::where('user_id', $userId)
            ->where('mes', $referenceDate->month)
            ->where('ano', $referenceDate->year)
            ->sum('valor');

        // Orçamento usado (despesas do mês)
        $this->orcamentoMesUsado = $this->despesasPeriodo;

        // Top categorias que mais estouraram orçamento (até 5)
        $orcamentosMes = Orcamento::where('user_id', $userId)
            ->where('mes', $referenceDate->month)
            ->where('ano', $referenceDate->year)
            ->with('category:id_category,desc_category')
            ->get();

        $gastosPorCategoriaMes = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereBetween('date', [$periodStart, $periodEnd])
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
                    'category' => $orc->category?->desc_category ?? ('Cat ' . $orc->category_id),
                    'orcado' => $orcado,
                    'gasto' => $gasto,
                    'estouro' => $diff,
                ];
            }
        }
        usort($estouros, fn($a, $b) => $b['estouro'] <=> $a['estouro']);
        $this->orcamentosTopEstouro = array_slice($estouros, 0, 5);

        // Gráfico fluxo de caixa (últimos 12 meses) - EXCLUINDO transações de cofrinho
        $monthExprCash = $this->monthExpression('date');
        $yearExprCash = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%Y', date) AS INTEGER)" : 'YEAR(date)';

        $cashAgg = Cashbook::where('user_id', $userId)
            ->where('date', '>=', $rollingTwelveStart)
            ->where('date', '<=', $periodEnd)
            ->whereNull('cofrinho_id')
            ->selectRaw("{$yearExprCash} as ano, {$monthExprCash} as mes, SUM(CASE WHEN type_id=1 THEN value ELSE 0 END) as receitas, SUM(CASE WHEN type_id=2 THEN value ELSE 0 END) as despesas")
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        $this->cashflowMonthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = $referenceDate->copy()->subMonths($i);
            $mes = (int) $dt->month;
            $ano = (int) $dt->year;
            $row = $cashAgg->firstWhere(function($item) use ($mes, $ano) {
                return (int)$item->mes === $mes && (int)$item->ano === $ano;
            });

            $this->cashflowMonthly[] = [
                'label' => $dt->format('M/y'),
                'receitas' => (float) ($row->receitas ?? 0),
                'despesas' => (float) ($row->despesas ?? 0),
            ];
        }

        // Gráfico: despesas por categoria (top 10 no mês) - EXCLUINDO transações de cofrinho
        $despesasTop = Cashbook::where('user_id', $userId)
            ->where('type_id', 2)
            ->whereNull('cofrinho_id')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->selectRaw('category_id, SUM(value) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $categoryNames = Category::whereIn('id_category', $despesasTop->pluck('category_id')->filter()->values())
            ->pluck('desc_category', 'id_category');

        $this->expensesByCategory = $despesasTop->map(fn($row) => [
            'label' => $categoryNames[$row->category_id] ?? ('Cat ' . $row->category_id),
            'total' => (float) $row->total,
        ])->values()->all();

        // Saúde do sistema: últimos uploads (cashbook / products / invoices)
        $this->lastUploads = [
            'cashbook' => CashbookUploadHistory::where('user_id', $userId)->orderByDesc('created_at')->first(),
            'products' => ProductUploadHistory::where('user_id', $userId)->orderByDesc('created_at')->first(),
            'invoices' => InvoiceUploadHistory::where('user_id', $userId)->orderByDesc('created_at')->first(),
        ];

        // Carregar alertas críticos
        $this->carregarAlertas($userId, $periodEnd);

        // Carregar atividades recentes
        $this->carregarAtividades($userId);
    }

    protected function carregarAlertas($userId, Carbon $periodEnd)
    {
        $this->alertas = [];

        // Contas vencidas (parcelas atrasadas)
        $parcelasVencidas = VendaParcela::join('sales', 'venda_parcelas.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->where('venda_parcelas.status', 'pendente')
            ->where('venda_parcelas.data_vencimento', '<', $periodEnd)
            ->count();

        if ($parcelasVencidas > 0) {
            $this->alertas[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "{$parcelasVencidas} parcela(s) vencida(s)",
                'link' => route('sales.index'),
            ];
        }

        // Produtos com estoque baixo
        if ($this->produtosEstoqueBaixo > 0) {
            $this->alertas[] = [
                'type' => 'warning',
                'icon' => 'fas fa-box',
                'message' => "{$this->produtosEstoqueBaixo} produto(s) com estoque baixo",
                'link' => route('products.index'),
            ];
        }

        // Clientes inadimplentes
        if ($this->clientesInadimplentes > 0) {
            $this->alertas[] = [
                'type' => 'warning',
                'icon' => 'fas fa-user-clock',
                'message' => "{$this->clientesInadimplentes} cliente(s) com pendências",
                'link' => route('clients.index'),
            ];
        }

        if ($this->invoicesProxVenc30Total > 0) {
            $this->alertas[] = [
                'type' => 'warning',
                'icon' => 'fas fa-calendar-day',
                'message' => 'Invoices a vencer em 30 dias: R$ ' . number_format($this->invoicesProxVenc30Total, 2, ',', '.'),
                'link' => route('invoices.index'),
            ];
        }

        if (!empty($this->orcamentosTopEstouro)) {
            $this->alertas[] = [
                'type' => 'danger',
                'icon' => 'fas fa-chart-line',
                'message' => count($this->orcamentosTopEstouro) . ' categoria(s) acima do orçamento no mês',
                'link' => route('cashbook.index'),
            ];
        }

        // Próximos sorteios de consórcios
        if ($this->proximosSorteios > 0) {
            $this->alertas[] = [
                'type' => 'info',
                'icon' => 'fas fa-calendar-alt',
                'message' => "{$this->proximosSorteios} sorteio(s) nos próximos 30 dias",
                'link' => route('consortiums.index'),
            ];
        }
    }

    protected function carregarAtividades($userId)
    {
        $this->atividades = [];

        // Últimas vendas
        $vendasRecentes = Sale::where('user_id', $userId)
            ->with('client:id,name')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        foreach ($vendasRecentes as $venda) {
            $this->atividades[] = [
                'icon' => 'fas fa-shopping-cart',
                'color' => 'text-purple-500',
                'module' => 'Vendas',
                'title' => 'Nova venda #' . $venda->id,
                'description' => $venda->client ? 'Cliente: ' . $venda->client->name : 'Sem cliente',
                'time' => $venda->created_at->diffForHumans(),
                'timestamp' => $venda->created_at->timestamp,
                'link' => route('sales.show', $venda->id),
            ];
        }

        $vendasAtualizadas = Sale::where('user_id', $userId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($vendasAtualizadas as $vendaAtualizada) {
            $this->atividades[] = [
                'icon' => 'fas fa-pen-to-square',
                'color' => 'text-violet-500',
                'module' => 'Vendas',
                'title' => 'Venda atualizada #' . $vendaAtualizada->id,
                'description' => 'Registro comercial alterado no sistema',
                'time' => $vendaAtualizada->updated_at->diffForHumans(),
                'timestamp' => $vendaAtualizada->updated_at->timestamp,
                'link' => route('sales.show', $vendaAtualizada->id),
            ];
        }

        // Últimos clientes cadastrados
        $clientesRecentes = Client::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($clientesRecentes as $cliente) {
            $this->atividades[] = [
                'icon' => 'fas fa-user-plus',
                'color' => 'text-green-500',
                'module' => 'Clientes',
                'title' => 'Novo cliente',
                'description' => $cliente->name,
                'time' => $cliente->created_at->diffForHumans(),
                'timestamp' => $cliente->created_at->timestamp,
                'link' => route('clients.edit', $cliente->id),
            ];
        }

        $clientesAtualizados = Client::where('user_id', $userId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($clientesAtualizados as $clienteAtualizado) {
            $this->atividades[] = [
                'icon' => 'fas fa-address-card',
                'color' => 'text-emerald-500',
                'module' => 'Clientes',
                'title' => 'Cliente atualizado',
                'description' => $clienteAtualizado->name,
                'time' => $clienteAtualizado->updated_at->diffForHumans(),
                'timestamp' => $clienteAtualizado->updated_at->timestamp,
                'link' => route('clients.edit', $clienteAtualizado->id),
            ];
        }

        // Últimas movimentações de caixa
        $movimentacoes = Cashbook::where('user_id', $userId)
            ->with('type:id_type,desc_type')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        foreach ($movimentacoes as $mov) {
            $this->atividades[] = [
                'icon' => $mov->type_id == 1 ? 'fas fa-arrow-up' : 'fas fa-arrow-down',
                'color' => $mov->type_id == 1 ? 'text-green-500' : 'text-red-500',
                'module' => 'Financeiro',
                'title' => $mov->type?->desc_type ?? 'Lançamento',
                'description' => $mov->description . ' - R$ ' . number_format($mov->value, 2, ',', '.'),
                'time' => \Carbon\Carbon::parse($mov->date)->diffForHumans(),
                'timestamp' => \Carbon\Carbon::parse($mov->date)->timestamp,
                'link' => route('cashbook.index'),
            ];
        }

        // Últimos produtos cadastrados
        $produtosRecentes = Product::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($produtosRecentes as $produto) {
            $this->atividades[] = [
                'icon' => 'fas fa-box',
                'color' => 'text-blue-500',
                'module' => 'Produtos',
                'title' => 'Novo produto',
                'description' => $produto->name,
                'time' => $produto->created_at->diffForHumans(),
                'timestamp' => $produto->created_at->timestamp,
                'link' => route('products.edit', $produto->id),
            ];
        }

        $produtosAtualizados = Product::where('user_id', $userId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($produtosAtualizados as $produtoAtualizado) {
            $this->atividades[] = [
                'icon' => 'fas fa-box-open',
                'color' => 'text-sky-500',
                'module' => 'Produtos',
                'title' => 'Produto atualizado',
                'description' => $produtoAtualizado->name,
                'time' => $produtoAtualizado->updated_at->diffForHumans(),
                'timestamp' => $produtoAtualizado->updated_at->timestamp,
                'link' => route('products.edit', $produtoAtualizado->id),
            ];
        }

        // Últimos consórcios cadastrados
        $consorciosRecentes = Consortium::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($consorciosRecentes as $consorcio) {
            $this->atividades[] = [
                'icon' => 'fas fa-users',
                'color' => 'text-yellow-500',
                'module' => 'Consórcios',
                'title' => 'Novo consórcio',
                'description' => $consorcio->name,
                'time' => $consorcio->created_at->diffForHumans(),
                'timestamp' => $consorcio->created_at->timestamp,
                'link' => route('consortiums.show', $consorcio->id),
            ];
        }

        // Últimas faturas cadastradas
        $faturasRecentes = Invoice::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($faturasRecentes as $fatura) {
            $this->atividades[] = [
                'icon' => 'fas fa-file-invoice-dollar',
                'color' => 'text-indigo-500',
                'module' => 'Invoices',
                'title' => 'Nova fatura',
                'description' => $fatura->description,
                'time' => $fatura->created_at->diffForHumans(),
                'timestamp' => $fatura->created_at->timestamp,
                'link' => route('invoices.index'),
            ];
        }

        $faturasAtualizadas = Invoice::where('user_id', $userId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        foreach ($faturasAtualizadas as $faturaAtualizada) {
            $this->atividades[] = [
                'icon' => 'fas fa-file-pen',
                'color' => 'text-indigo-500',
                'module' => 'Invoices',
                'title' => 'Invoice atualizada',
                'description' => $faturaAtualizada->description,
                'time' => $faturaAtualizada->updated_at->diffForHumans(),
                'timestamp' => $faturaAtualizada->updated_at->timestamp,
                'link' => route('invoices.index'),
            ];
        }

        // Últimos bancos cadastrados
        $bancosRecentes = Bank::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($bancosRecentes as $banco) {
            $this->atividades[] = [
                'icon' => 'fas fa-university',
                'color' => 'text-gray-500',
                'module' => 'Bancos',
                'title' => 'Novo banco',
                'description' => $banco->name,
                'time' => $banco->created_at->diffForHumans(),
                'timestamp' => $banco->created_at->timestamp,
                'link' => route('banks.index'),
            ];
        }

        // Últimos cofrinhos cadastrados
        $cofrinhosRecentes = Cofrinho::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        foreach ($cofrinhosRecentes as $cofrinho) {
            $this->atividades[] = [
                'icon' => 'fas fa-piggy-bank',
                'color' => 'text-pink-500',
                'module' => 'Cofrinhos',
                'title' => 'Novo cofrinho',
                'description' => $cofrinho->nome,
                'time' => $cofrinho->created_at->diffForHumans(),
                'timestamp' => $cofrinho->created_at->timestamp,
                'link' => route('cofrinhos.index'),
            ];
        }

        $cashbookUploads = CashbookUploadHistory::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        foreach ($cashbookUploads as $upload) {
            $this->atividades[] = [
                'icon' => 'fas fa-file-arrow-up',
                'color' => 'text-emerald-500',
                'module' => 'Uploads',
                'title' => 'Importacao de cashbook',
                'description' => $upload->file_name . ' · ' . ($upload->status ?? 'status indefinido'),
                'time' => $upload->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $upload->created_at?->timestamp ?? now()->timestamp,
                'link' => route('cashbook.index'),
            ];
        }

        $productUploads = ProductUploadHistory::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        foreach ($productUploads as $upload) {
            $this->atividades[] = [
                'icon' => 'fas fa-boxes-packing',
                'color' => 'text-sky-500',
                'module' => 'Uploads',
                'title' => 'Importacao de produtos',
                'description' => $upload->filename . ' · ' . ($upload->status ?? 'status indefinido'),
                'time' => $upload->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $upload->created_at?->timestamp ?? now()->timestamp,
                'link' => route('products.index'),
            ];
        }

        $invoiceUploads = InvoiceUploadHistory::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        foreach ($invoiceUploads as $upload) {
            $this->atividades[] = [
                'icon' => 'fas fa-receipt',
                'color' => 'text-indigo-500',
                'module' => 'Uploads',
                'title' => 'Importacao de invoices',
                'description' => $upload->filename . ' · ' . ($upload->status ?? 'status indefinido'),
                'time' => $upload->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $upload->created_at?->timestamp ?? now()->timestamp,
                'link' => route('invoices.index'),
            ];
        }

        $goalActivities = GoalActivity::where('user_id', $userId)
            ->with('goal:id,title')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        foreach ($goalActivities as $activity) {
            $this->atividades[] = [
                'icon' => 'fas ' . ($activity->action_icon ?: 'fa-bullseye'),
                'color' => $activity->action_color ?: 'text-cyan-500',
                'module' => 'Metas',
                'title' => 'Meta ' . $activity->action,
                'description' => ($activity->goal->title ?? 'Meta') . ' · ' . $activity->description,
                'time' => $activity->created_at->diffForHumans(),
                'timestamp' => $activity->created_at->timestamp,
                'link' => route('dashboard'),
            ];
        }

        $mlSyncLogs = MercadoLivreSyncLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        foreach ($mlSyncLogs as $log) {
            $this->atividades[] = [
                'icon' => $log->status === 'error' ? 'fas fa-circle-xmark' : 'fas fa-store',
                'color' => $log->status === 'error' ? 'text-rose-500' : 'text-yellow-500',
                'module' => 'Mercado Livre',
                'title' => 'Sync ML · ' . strtoupper((string) $log->status),
                'description' => ($log->message ?: 'Sincronizacao executada') . ' · ' . ($log->sync_type ?: 'geral'),
                'time' => $log->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $log->created_at?->timestamp ?? now()->timestamp,
                'link' => route('dashboard'),
            ];
        }

        $stockLogs = MlStockLog::where('user_id', $userId)
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        foreach ($stockLogs as $log) {
            $this->atividades[] = [
                'icon' => 'fas fa-arrows-rotate',
                'color' => 'text-amber-500',
                'module' => 'Estoque',
                'title' => $log->getOperationDescription(),
                'description' => ($log->product->name ?? 'Produto') . ' · ajuste ' . $log->formatted_change,
                'time' => $log->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $log->created_at?->timestamp ?? now()->timestamp,
                'link' => route('products.index'),
            ];
        }

        $shopeeLogs = ShopeeSyncLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        foreach ($shopeeLogs as $log) {
            $this->atividades[] = [
                'icon' => $log->status === 'error' ? 'fas fa-triangle-exclamation' : 'fas fa-bag-shopping',
                'color' => $log->status === 'error' ? 'text-rose-500' : 'text-orange-500',
                'module' => 'Shopee',
                'title' => 'Sync Shopee · ' . strtoupper((string) $log->status),
                'description' => ($log->message ?: 'Operacao de sincronizacao') . ' · ' . ($log->sync_type ?: 'geral'),
                'time' => $log->created_at?->diffForHumans() ?? 'agora',
                'timestamp' => $log->created_at?->timestamp ?? now()->timestamp,
                'link' => route('dashboard'),
            ];
        }

        // Ordenar por data mais recente
        usort($this->atividades, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        $this->atividades = collect($this->atividades)
            ->unique(fn ($activity) => implode('|', [
                $activity['module'] ?? '',
                $activity['title'] ?? '',
                $activity['description'] ?? '',
                $activity['timestamp'] ?? 0,
            ]))
            ->values()
            ->all();

        // Limitar a 40 atividades
        $this->atividades = array_slice($this->atividades, 0, $this->activityFeedLimit);
    }

    public function render()
    {
        $totalSales = Sale::where('user_id', Auth::id())->count();
        $banks = Bank::where('user_id', Auth::id())->get();

        return view('livewire.dashboard.dashboard-index', [
            'totalSales' => $totalSales,
            'banks' => $banks,
            'produtoMaisVendido' => $this->produtoMaisVendido,
            'totalProdutosEstoque' => $this->totalProdutosEstoque,
            'totalCashbook' => $this->totalCashbook,
            'totalClientes' => $this->totalClientes,
            'clientesComSalesPendentes' => $this->clientesComSalesPendentes,
            'totalFaturamento' => $this->totalFaturamento,
            'totalProdutos' => $this->totalProdutos,
            'salesMonth' => $this->salesMonth,
            'ticketMedio' => $this->ticketMedio,
            'clientesNovosMes' => $this->clientesNovosMes,
            'produtosEstoqueBaixo' => $this->produtosEstoqueBaixo,
            'clientesInadimplentes' => $this->clientesInadimplentes ?? 0,
            'aniversariantesMes' => $this->aniversariantesMes ?? 0,
            'produtosVendidosMes' => $this->produtosVendidosMes ?? 0,
            'produtosCadastrados' => $this->produtosCadastrados ?? 0,
            'saldoCaixa' => $this->saldoCaixa ?? 0,
            'contasPagar' => $this->contasPagar ?? 0,
            'contasReceber' => $this->contasReceber ?? 0,
            'fornecedoresPagar' => $this->fornecedoresPagar ?? 0,
            'despesasFixas' => $this->despesasFixas ?? 0,
            'clientesReceber' => $this->clientesReceber ?? 0,
            'outrosReceber' => $this->outrosReceber ?? 0,
            'valorVendas' => $this->valorVendas ?? 0,
            'custoEstoque' => $this->custoEstoque ?? 0,
            'custoProdutosVendidos' => $this->custoProdutosVendidos ?? 0,
            'gastosInvoiceMensal' => $this->gastosInvoiceMensal ?? [],
            'gastosInvoicePorBanco' => $this->gastosInvoicePorBanco ?? [],
            'margemLucro' => $this->margemLucro ?? 0,
            'taxaCrescimento' => $this->taxaCrescimento ?? 0,
            'produtosAtivos' => $this->produtosAtivos ?? 0,
            // Novos dados
            'totalBancos' => $this->totalBancos ?? 0,
            'totalInvoices' => $this->totalInvoices ?? 0,
            'totalCofrinhos' => $this->totalCofrinhos ?? 0,
            'totalEconomizado' => $this->totalEconomizado ?? 0,
            'totalConsorciosAtivos' => $this->totalConsorciosAtivos ?? 0,
            'proximosSorteios' => $this->proximosSorteios ?? 0,
            'consorcioParticipantesAtivos' => $this->consorcioParticipantesAtivos ?? 0,
            'consorcioPagamentosPendentesTotal' => $this->consorcioPagamentosPendentesTotal ?? 0,
            'consorcioContemplacoesTotal' => $this->consorcioContemplacoesTotal ?? 0,
            'alertas' => $this->alertas ?? [],
            'atividades' => $this->atividades ?? [],
            'lucroLiquido' => $this->lucroLiquido ?? 0,
            'receitasPeriodo' => $this->receitasPeriodo ?? 0,
            'despesasPeriodo' => $this->despesasPeriodo ?? 0,

            // Contas reais
            'contasReceberPendentes' => $this->contasReceberPendentes ?? 0,
            'contasPagarPendentes' => $this->contasPagarPendentes ?? 0,
            'parcelasVencidasCount' => $this->parcelasVencidasCount ?? 0,
            'parcelasVencidasValor' => $this->parcelasVencidasValor ?? 0,

            // Orçamentos
            'orcamentoMesTotal' => $this->orcamentoMesTotal ?? 0,
            'orcamentoMesUsado' => $this->orcamentoMesUsado ?? 0,
            'orcamentosTopEstouro' => $this->orcamentosTopEstouro ?? [],

            // Recorrências
            'recorrentesAtivas' => $this->recorrentesAtivas ?? 0,
            'recorrentesProx30Total' => $this->recorrentesProx30Total ?? 0,

            // Invoices resumo
            'invoicesProxVenc30Total' => $this->invoicesProxVenc30Total ?? 0,

            // Gráficos
            'cashflowMonthly' => $this->cashflowMonthly ?? [],
            'expensesByCategory' => $this->expensesByCategory ?? [],
            'periodComparison' => $this->periodComparison ?? [],

            // Saúde do sistema
            'lastUploads' => $this->lastUploads,
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'availableYears' => $this->availableYears,
            'monthOptions' => $this->monthOptions,
            'periodPreset' => $this->periodPreset,
            'periodLabel' => $this->getPeriodLabel(),
        ]);
    }

    protected function getReferenceDate(): Carbon
    {
        $month = $this->selectedMonth >= 1 && $this->selectedMonth <= 12 ? $this->selectedMonth : (int) now()->month;
        $year = $this->selectedYear > 2000 ? $this->selectedYear : (int) now()->year;

        return Carbon::create($year, $month, 1, 0, 0, 0, config('app.timezone'));
    }

    protected function getPeriodBounds(Carbon $referenceDate): array
    {
        return match ($this->periodPreset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'quarter' => [
                $referenceDate->copy()->subMonths(2)->startOfMonth()->startOfDay(),
                $referenceDate->copy()->endOfMonth()->endOfDay(),
            ],
            'year' => [
                $referenceDate->copy()->startOfYear()->startOfDay(),
                $referenceDate->copy()->endOfMonth()->endOfDay(),
            ],
            default => [
                $referenceDate->copy()->startOfMonth()->startOfDay(),
                $referenceDate->copy()->endOfMonth()->endOfDay(),
            ],
        };
    }

    protected function getPeriodLabel(): string
    {
        $referenceDate = $this->getReferenceDate();

        return match ($this->periodPreset) {
            'today' => 'Hoje',
            'quarter' => 'Ultimos 3 meses',
            'year' => 'Ano atual acumulado',
            default => $referenceDate->translatedFormat('F \d\e Y'),
        };
    }

    protected function getComparisonRanges(Carbon $periodStart, Carbon $periodEnd): array
    {
        $durationInSeconds = $periodEnd->diffInSeconds($periodStart) + 1;

        $previousEnd = $periodStart->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subSeconds($durationInSeconds - 1);

        $lastYearStart = $periodStart->copy()->subYear();
        $lastYearEnd = $periodEnd->copy()->subYear();

        return [$previousStart, $previousEnd, $lastYearStart, $lastYearEnd];
    }

    protected function monthExpression(string $column): string
    {
        $format = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%%m', %s) AS INTEGER)"
            : 'MONTH(%s)';

        return sprintf($format, $column);
    }
}
