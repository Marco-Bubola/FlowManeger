<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ClientDashboard extends Component
{
    public Client $client;
    public $activeTab = 'vendas';

    // Filtros
    public $filterYear;
    public $filterMonth;
    public $filterStatus = 'all';
    public $filterPaymentType = 'all';

    // Paginação independente para cada aba
    public $produtosPage = 1;
    public $parcelasPage = 1;
    public $pagamentosPage = 1;
    public $produtosPerPage = 12;
    public $parcelasPerPage = 9;
    public $pagamentosPerPage = 9;

    // Dados financeiros
    public $totalVendas = 0;
    public $totalFaturado = 0;
    public $totalPago = 0;
    public $totalPendente = 0;
    public $ticketMedio = 0;
    public $ultimaCompra = null;
    public $primeiraCompra = null;
    public $diasComoCliente = 0;

    // Dados para gráficos
    public $vendasPorMes = [];
    public $vendasPorStatus = [];
    public $produtosMaisComprados = [];
    public $categoriasMaisCompradas = [];
    public $evolucaoFaturamento = [];
    public $metodoPagamentoPreferido = [];

    // Dados das vendas
    public $vendas = [];
    public $parcelas = [];
    public $pagamentos = [];
    public $totalParcelas = 0;
    public $totalPagamentos = 0;
    public $totalProdutos = 0;

    public function mount($cliente)
    {
        $this->client = Client::where('user_id', Auth::id())->findOrFail($cliente);
        $this->filterYear = now()->year;
        $this->filterMonth = 'all';

        // Inicializar arrays para evitar problemas
        $this->vendas = [];
        $this->parcelas = [];
        $this->pagamentos = [];
        $this->produtosMaisComprados = [];

        $this->loadData();
    }

    public function updatedFilterYear()
    {
        $this->loadData();
    }

    public function updatedFilterMonth()
    {
        $this->loadData();
    }

    public function updatedFilterStatus()
    {
        $this->loadData();
    }

    public function updatedFilterPaymentType()
    {
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Métodos de paginação
    public function nextProdutosPage()
    {
        Log::info('nextProdutosPage chamado', ['current_page' => $this->produtosPage]);
        $maxPage = ceil($this->totalProdutos / $this->produtosPerPage);
        if ($this->produtosPage < $maxPage) {
            $this->produtosPage++;
            $this->loadChartData();
            Log::info('Página incrementada', ['new_page' => $this->produtosPage]);
            // Adicionar feedback visual
            session()->flash('message', 'Próxima página carregada: ' . $this->produtosPage);
        }
    }

    public function prevProdutosPage()
    {
        Log::info('prevProdutosPage chamado', ['current_page' => $this->produtosPage]);
        if ($this->produtosPage > 1) {
            $this->produtosPage--;
            $this->loadChartData();
            Log::info('Página decrementada', ['new_page' => $this->produtosPage]);
            // Adicionar feedback visual
            session()->flash('message', 'Página anterior carregada: ' . $this->produtosPage);
        }
    }

    public function nextParcelasPage()
    {
        $maxPage = ceil($this->totalParcelas / $this->parcelasPerPage);
        if ($this->parcelasPage < $maxPage) {
            $this->parcelasPage++;
            $this->loadParcelasData();
            session()->flash('message', 'Próxima página de parcelas: ' . $this->parcelasPage);
        }
    }

    public function prevParcelasPage()
    {
        if ($this->parcelasPage > 1) {
            $this->parcelasPage--;
            $this->loadParcelasData();
            session()->flash('message', 'Página anterior de parcelas: ' . $this->parcelasPage);
        }
    }

    public function nextPagamentosPage()
    {
        $maxPage = ceil($this->totalPagamentos / $this->pagamentosPerPage);
        if ($this->pagamentosPage < $maxPage) {
            $this->pagamentosPage++;
            $this->loadPaymentsData();
            session()->flash('message', 'Próxima página de pagamentos: ' . $this->pagamentosPage);
        }
    }

    public function prevPagamentosPage()
    {
        if ($this->pagamentosPage > 1) {
            $this->pagamentosPage--;
            $this->loadPaymentsData();
            session()->flash('message', 'Página anterior de pagamentos: ' . $this->pagamentosPage);
        }
    }

    public function loadData()
    {
        $this->loadFinancialData();
        $this->loadChartData();
        $this->loadSalesData();
        $this->loadPaymentsData();
        $this->loadParcelasData();
    }

    private function loadFinancialData()
    {
        $query = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id());

        // Aplicar filtros
        if ($this->filterYear) {
            $query->whereYear('created_at', $this->filterYear);
        }

        if ($this->filterMonth !== 'all') {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentType !== 'all') {
            $query->where('tipo_pagamento', $this->filterPaymentType);
        }

        $vendas = $query->get();

        $this->totalVendas = $vendas->count();
        $this->totalFaturado = $vendas->sum('total_price');
        $this->totalPago = $vendas->sum('amount_paid');
        $this->totalPendente = $this->totalFaturado - $this->totalPago;
        $this->ticketMedio = $this->totalVendas > 0 ? $this->totalFaturado / $this->totalVendas : 0;

        // Primeira e última compra
        $todasVendas = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get();

        $this->primeiraCompra = $todasVendas->first()?->created_at;
        $this->ultimaCompra = $todasVendas->last()?->created_at;

        if ($this->primeiraCompra) {
            $this->diasComoCliente = Carbon::parse($this->primeiraCompra)->diffInDays(now());
        }
    }

    private function loadChartData()
    {
        // Vendas por mês (últimos 12 meses)
        $vendasPorMes = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_vendas'),
                DB::raw('SUM(total_price) as total_faturamento')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $this->vendasPorMes = $vendasPorMes->map(function ($item) {
            return [
                'mes' => Carbon::createFromDate($item->year, $item->month, 1)->format('M/Y'),
                'vendas' => $item->total_vendas,
                'faturamento' => $item->total_faturamento
            ];
        })->toArray();

        // Vendas por status
        $this->vendasPorStatus = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // Produtos mais comprados com paginação
        $produtosQuery = SaleItem::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantidade'), DB::raw('COUNT(*) as total_vendas'))
            ->groupBy('product_id')
            ->orderBy('total_quantidade', 'desc');

        $allProdutos = $produtosQuery->get()->map(function ($item) {
            return [
                'produto' => $item->product->name ?? 'Produto não encontrado',
                'quantidade' => $item->total_quantidade,
                'vendas' => $item->total_vendas,
                'product_id' => $item->product_id
            ];
        });

        // Implementar paginação manual
        $offset = ($this->produtosPage - 1) * $this->produtosPerPage;
        $this->produtosMaisComprados = $allProdutos->slice($offset, $this->produtosPerPage)->values()->toArray();
        $this->totalProdutos = $allProdutos->count();

        // Categorias mais compradas
        $this->categoriasMaisCompradas = SaleItem::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->whereHas('product.category')
            ->with('product.category')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantidade'))
            ->groupBy('product_id')
            ->get()
            ->groupBy('product.category.name')
            ->map(function ($items, $categoria) {
                return [
                    'categoria' => $categoria,
                    'quantidade' => $items->sum('total_quantidade')
                ];
            })
            ->sortByDesc('quantidade')
            ->take(5)
            ->values()
            ->toArray();

        // Método de pagamento preferido
        $this->metodoPagamentoPreferido = SalePayment::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();
    }

    private function loadSalesData()
    {
        $query = Sale::where('client_id', $this->client->id)
            ->where('user_id', Auth::id())
            ->with(['saleItems.product', 'payments', 'parcelasVenda']);

        // Aplicar filtros
        if ($this->filterYear) {
            $query->whereYear('created_at', $this->filterYear);
        }

        if ($this->filterMonth !== 'all') {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentType !== 'all') {
            $query->where('tipo_pagamento', $this->filterPaymentType);
        }

        $vendasCollection = $query->orderBy('created_at', 'desc')->get();
        $this->vendas = $vendasCollection->toArray();
    }

    private function loadPaymentsData()
    {
        $query = SalePayment::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->with('sale')
            ->orderBy('payment_date', 'desc');

        // Obter todos os pagamentos e implementar paginação manual
        $allPagamentos = $query->get();
        $offset = ($this->pagamentosPage - 1) * $this->pagamentosPerPage;
        $this->pagamentos = $allPagamentos->slice($offset, $this->pagamentosPerPage)->toArray();
        $this->totalPagamentos = $allPagamentos->count();
    }

    private function loadParcelasData()
    {
        $query = VendaParcela::whereHas('sale', function ($query) {
                $query->where('client_id', $this->client->id)
                    ->where('user_id', Auth::id());
            })
            ->with('sale');

        // Ordenar parcelas: pendentes primeiro, pagas por último
        $allParcelas = $query->get()->sortBy(function($parcela) {
            if ($parcela->status === 'pendente') {
                return 1; // Prioridade alta para pendentes
            } elseif ($parcela->status === 'pago') {
                return 3; // Prioridade baixa para pagas (vão para o final)
            }
            return 2; // Outros status no meio
        })->values();

        // Implementar paginação manual
        $offset = ($this->parcelasPage - 1) * $this->parcelasPerPage;
        $this->parcelas = $allParcelas->slice($offset, $this->parcelasPerPage)->toArray();
        $this->totalParcelas = $allParcelas->count();
    }

    public function exportClientPDF($type = 'complete')
    {
        try {
            // Carregar dados atuais
            $this->loadData();

            // Dados do cliente e resumo financeiro
            $data = [
                'client' => $this->client,
                'vendas' => $this->vendas,
                'totalVendas' => $this->totalVendas,
                'totalFaturado' => $this->totalFaturado,
                'totalPago' => $this->totalPago,
                'totalPendente' => $this->totalPendente,
                'ticketMedio' => $this->ticketMedio,
                'diasComoCliente' => $this->diasComoCliente,
                'primeiraCompra' => $this->primeiraCompra,
                'ultimaCompra' => $this->ultimaCompra,
                'type' => $type,
                'filterYear' => $this->filterYear,
                'filterMonth' => $this->filterMonth,
                'filterStatus' => $this->filterStatus,
                'filterPaymentType' => $this->filterPaymentType,
                'generatedAt' => now(),
            ];

            // Adicionar dados específicos baseado no tipo
            switch ($type) {
                case 'complete':
                    $data['parcelas'] = $this->parcelas;
                    $data['pagamentos'] = $this->pagamentos;
                    $data['produtosMaisComprados'] = $this->produtosMaisComprados;
                    $data['categoriasMaisCompradas'] = $this->categoriasMaisCompradas;
                    $data['vendasPorMes'] = $this->vendasPorMes;
                    $data['vendasPorStatus'] = $this->vendasPorStatus;
                    break;
                case 'financeiro':
                    $data['parcelas'] = $this->parcelas;
                    $data['pagamentos'] = $this->pagamentos;
                    break;
                case 'vendas':
                    // Apenas vendas já incluídas
                    break;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.client-report', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = "cliente_{$this->client->name}_{$type}_" . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao gerar PDF: ' . $e->getMessage());
            return null;
        }
    }

    public function exportClientExcel()
    {
        try {
            $this->loadData();

            // Preparar dados para Excel
            $excelData = [];

            foreach ($this->vendas as $venda) {
                $excelData[] = [
                    'ID Venda' => $venda['id'],
                    'Data' => \Carbon\Carbon::parse($venda['created_at'])->format('d/m/Y'),
                    'Valor Total' => $venda['total_price'],
                    'Valor Pago' => $venda['amount_paid'] ?? 0,
                    'Restante' => $venda['total_price'] - ($venda['amount_paid'] ?? 0),
                    'Status' => ucfirst($venda['status']),
                    'Tipo Pagamento' => ucfirst(str_replace('_', ' ', $venda['tipo_pagamento'])),
                    'Parcelas' => $venda['parcelas'] ?? 1,
                    'Produtos' => isset($venda['sale_items']) ? count($venda['sale_items']) : 0,
                ];
            }

            // Criar arquivo temporário
            $filename = "cliente_{$this->client->name}_" . now()->format('Y-m-d_H-i-s') . '.csv';
            $tempFile = storage_path('app/temp/' . $filename);

            // Criar diretório se não existir
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            $output = fopen($tempFile, 'w');

            // Cabeçalho
            if (!empty($excelData)) {
                fputcsv($output, array_keys($excelData[0]), ';');

                // Dados
                foreach ($excelData as $row) {
                    fputcsv($output, $row, ';');
                }
            }

            fclose($output);

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao gerar Excel: ' . $e->getMessage());
            return null;
        }
    }

    public function exportClientData()
    {
        // Método legado - redireciona para PDF completo
        return $this->exportClientPDF('complete');
    }

    public function clearFilters()
    {
        $this->reset(['dateStart', 'dateEnd', 'status', 'tipo_pagamento']);
        $this->loadData();
        session()->flash('message', 'Filtros limpos com sucesso!');
    }


    public function render()
    {
        return view('livewire.clients.client-dashboard');
    }
}
