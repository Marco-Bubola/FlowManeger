<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardSales extends Component
{
    // Métricas principais
    public float $totalFaturamento = 0;
    public float $totalFaltante = 0;
    public int $totalClientes = 0;
    public int $clientesComSalesPendentes = 0;
    public float $ticketMedioRecorrente = 0;

    // Vendas comparativas
    public float $vendasMesAtual = 0;
    public float $vendasMesAnterior = 0;
    public float $vendasAnoAtual = 0;
    public float $vendasAnoAnterior = 0;

    // Últimas informações
    public $ultimaVenda = null;

    // Listas e dados
    public $clientesPendentes = [];
    public $vendasPorMesEvolucao = [];
    public $vendasPorCliente = [];
    public $clientesInativos = [];
    public $clientesRecorrentes = [];
    public $vendasPorStatus = [];
    public $dadosGraficoPizza = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Clientes com vendas pendentes
        $this->clientesPendentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            })
            ->orderByDesc('created_at')
            ->with(['sales' => function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            }])
            ->get()
            ->map(function($cliente) {
                foreach ($cliente->sales as $sale) {
                    $pagamentos = DB::table('sale_payments')
                        ->where('sale_id', $sale->id)
                        ->sum('amount_paid');
                    $sale->valor_restante = $sale->total_price - $pagamentos;
                }
                return $cliente;
            })
            ->toArray();

        // Evolução das vendas por mês (últimos 12 meses)
        $this->vendasPorMesEvolucao = Sale::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as periodo"),
                DB::raw('SUM(total_price) as total')
            )
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->toArray();

        // Vendas por cliente (top 10)
        $this->vendasPorCliente = Sale::select('client_id', DB::raw('SUM(total_price) as total_vendas'), DB::raw('COUNT(*) as qtd_vendas'))
            ->where('user_id', $userId)
            ->groupBy('client_id')
            ->orderByDesc('total_vendas')
            ->with('client')
            ->limit(10)
            ->get()
            ->toArray();

        // Clientes inativos (sem compras nos últimos 6 meses)
        $this->clientesInativos = Client::where('user_id', $userId)
            ->whereDoesntHave('sales', function($q) use ($userId) {
                $q->where('created_at', '>=', now()->subMonths(6))->where('user_id', $userId);
            })
            ->get()
            ->toArray();

        // Recorrência de clientes
        $this->clientesRecorrentes = Sale::select('client_id', DB::raw('COUNT(*) as qtd_vendas'), DB::raw('SUM(total_price) as total'))
            ->where('user_id', $userId)
            ->groupBy('client_id')
            ->having('qtd_vendas', '>', 1)
            ->with('client')
            ->get()
            ->toArray();

        $this->ticketMedioRecorrente = count($this->clientesRecorrentes) > 0
            ? (collect($this->clientesRecorrentes)->sum('total') / collect($this->clientesRecorrentes)->sum('qtd_vendas'))
            : 0;

        // Comparativo de vendas por período
        $mesAtual = date('n');
        $anoAtual = date('Y');
        $mesAnterior = $mesAtual == 1 ? 12 : $mesAtual - 1;
        $anoMesAnterior = $mesAtual == 1 ? $anoAtual - 1 : $anoAtual;

        $this->vendasMesAtual = Sale::where('user_id', $userId)
            ->whereYear('created_at', $anoAtual)
            ->whereMonth('created_at', $mesAtual)
            ->sum('total_price');

        $this->vendasMesAnterior = Sale::where('user_id', $userId)
            ->whereYear('created_at', $anoMesAnterior)
            ->whereMonth('created_at', $mesAnterior)
            ->sum('total_price');

        $this->vendasAnoAtual = Sale::where('user_id', $userId)
            ->whereYear('created_at', $anoAtual)
            ->sum('total_price');

        $this->vendasAnoAnterior = Sale::where('user_id', $userId)
            ->whereYear('created_at', $anoAtual - 1)
            ->sum('total_price');

        // Vendas por status
        $this->vendasPorStatus = Sale::select('status', DB::raw('COUNT(*) as total'))
            ->where('user_id', $userId)
            ->groupBy('status')
            ->get()
            ->toArray();

        // Gráfico de vendas por categoria
        $this->dadosGraficoPizza = SaleItem::select(
            DB::raw('category.name as category_name'),
            DB::raw('SUM(sale_items.quantity) as total_sold')
        )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('category', 'products.category_id', '=', 'category.id_category')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->groupBy('category.name')
            ->get()
            ->toArray();

        // Faturamento
        $this->totalFaturamento = Sale::where('user_id', $userId)->sum('total_price');

        // Total faltante (somatório do valor restante das vendas pendentes)
        $this->totalFaltante = (float) (Sale::where('user_id', $userId)
            ->where('status', 'pendente')
            ->get()
            ->sum(function($sale) {
                $pagamentos = DB::table('sale_payments')
                    ->where('sale_id', $sale->id)
                    ->sum('amount_paid');
                return max($sale->total_price - $pagamentos, 0);
            }) ?? 0);

        // Última venda
        $this->ultimaVenda = Sale::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        // Total de clientes
        $this->totalClientes = Client::where('user_id', $userId)->count();

        // Clientes com vendas pendentes
        $this->clientesComSalesPendentes = Client::where('user_id', $userId)
            ->whereHas('sales', function($q) use ($userId) {
                $q->where('status', 'pendente')->where('user_id', $userId);
            })
            ->count();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-sales');
    }
}
