<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesIndex extends Component
{
    use WithPagination;

    protected $listeners = [
        'sales:confirm-delete' => 'confirmDelete',
    ];

    // Filtros
    public string $search = '';
    public string $status = '';
    public string $filter = '';
    public string $payment_type = '';
    public string $date_start = '';
    public string $date_end = '';
    public string $min_value = '';
    public string $max_value = '';
    public int $perPage = 12;
    public bool $fullHdLayout = false;
    public bool $ultraWindClient = false;

    // Novos filtros para componentes modernos
    public string $statusFilter = '';
    public string $clientFilter = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $minValue = '';
    public string $maxValue = '';
    public string $paymentMethodFilter = '';
    public string $sellerFilter = '';
    public string $quickFilter = '';

    // Sistema de Ordenação
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    // Modal de exclusão
    public ?Sale $deletingSale = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'filter' => ['except' => ''],
        'payment_type' => ['except' => ''],
        'date_start' => ['except' => ''],
        'date_end' => ['except' => ''],
        'min_value' => ['except' => ''],
        'max_value' => ['except' => ''],
        'perPage' => ['except' => 12],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->ultraWindClient = $this->isUltraWind();
        $this->queryString['perPage']['except'] = $this->defaultPerPage();

        $this->search = request('search', '');
        $this->status = request('status', '');
        $this->filter = request('filter', '');
        $this->payment_type = request('payment_type', '');
        $this->date_start = request('date_start', '');
        $this->date_end = request('date_end', '');
        $this->min_value = request('min_value', '');
        $this->max_value = request('max_value', '');
        $this->perPage = (int) request('perPage', $this->defaultPerPage());

        $this->alignPerPageToOptions();
    }

    private function isUltraWind(): bool
    {
        $clientName = env('CLIENT_NAME', config('app.client_name', ''));
        return strtolower(trim($clientName)) === 'ultra wind';
    }

    private function usesUltraMultipliers(): bool
    {
        return $this->ultraWindClient;
    }

    private function defaultPerPage(): int
    {
        return $this->usesUltraMultipliers() ? 16 : 12;
    }

    private function alignPerPageToOptions(): void
    {
        $allowed = $this->getPerPageOptions();

        if (empty($allowed)) {
            return;
        }

        $current = (int) $this->perPage;

        if (!in_array($current, $allowed, true)) {
            $this->perPage = $allowed[array_key_first($allowed)];
        }
    }

    public function updatingPerPage($value): void
    {
        $target = (int) $value;
        $allowed = $this->getPerPageOptions();

        if (!in_array($target, $allowed, true)) {
            $this->perPage = $allowed[array_key_first($allowed)];
            session()->flash('info', 'Selecione um tamanho compatível com este layout.');
        } else {
            $this->perPage = $target;
        }

        $this->resetPage();
    }

    public function updatedFullHdLayout(bool $isFullHd): void
    {
        if ($this->ultraWindClient) {
            return;
        }

        $options = $this->getPerPageOptions();
        if (!empty($options)) {
            $this->queryString['perPage']['except'] = $options[array_key_first($options)];
        }

        $this->alignPerPageToOptions();
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->filter = '';
        $this->payment_type = '';
        $this->date_start = '';
        $this->date_end = '';
        $this->min_value = '';
        $this->max_value = '';
        $this->perPage = $this->defaultPerPage();
        $this->alignPerPageToOptions();

        // Limpar também os novos filtros
        $this->statusFilter = '';
        $this->clientFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->minValue = '';
        $this->maxValue = '';
        $this->paymentMethodFilter = '';
        $this->sellerFilter = '';
        $this->quickFilter = '';

        $this->resetPage();
    }

    public function setQuickFilter($filter)
    {
        $this->quickFilter = $filter;

        switch ($filter) {
            case 'today':
                $this->startDate = now()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'pending':
                $this->statusFilter = 'pending';
                break;
            case 'paid':
                $this->statusFilter = 'paid';
                break;
        }

        $this->resetPage();
    }

    // Métodos de Ordenação
    public function sortByField($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function setSortOrder($field, $direction = 'desc')
    {
        $this->sortBy = $field;
        $this->sortDirection = $direction;
        $this->resetPage();
    }

    // Filtros Rápidos Expandidos
    public function setQuickSearch($searchTerm)
    {
        $this->search = $searchTerm;
        $this->resetPage();
    }

    public function setQuickDateFilter($period)
    {
        switch ($period) {
            case 'yesterday':
                $this->startDate = now()->subDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->format('Y-m-d');
                break;
            case 'last_week':
                $this->startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_quarter':
                $this->startDate = now()->subQuarter()->startOfQuarter()->format('Y-m-d');
                $this->endDate = now()->subQuarter()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->endOfYear()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function setValueRange($range)
    {
        switch ($range) {
            case 'low':
                $this->minValue = '0';
                $this->maxValue = '100';
                break;
            case 'medium':
                $this->minValue = '100';
                $this->maxValue = '500';
                break;
            case 'high':
                $this->minValue = '500';
                $this->maxValue = '2000';
                break;
            case 'premium':
                $this->minValue = '2000';
                $this->maxValue = '';
                break;
        }
        $this->resetPage();
    }

    public function exportSales()
    {
        // Implementar exportação de vendas
        session()->flash('message', 'Exportação iniciada!');
    }

    public function confirmDelete($saleId)
    {
        $this->deletingSale = Sale::find($saleId);
        $this->showDeleteModal = true;
        $this->dispatch('refreshComponent');
    }

    public function cancelDelete()
    {
        $this->deletingSale = null;
        $this->showDeleteModal = false;
    }

    public function deleteSale()
    {
        if ($this->deletingSale) {
            // Restaurar o estoque dos produtos
            foreach ($this->deletingSale->saleItems as $saleItem) {
                $product = Product::find($saleItem->product_id);
                if ($product) {
                    $product->stock_quantity += $saleItem->quantity;
                    $product->save();
                }
            }

            // Excluir os itens da venda e a venda
            $this->deletingSale->saleItems()->delete();
            $this->deletingSale->payments()->delete();
            $this->deletingSale->parcelasVenda()->delete();
            $this->deletingSale->delete();

            session()->flash('message', 'Venda excluída com sucesso!');
            $this->cancelDelete();
        }
    }

    /**
     * Registrar pagamento integral da venda (pagar tudo).
     * Pode ser chamado a partir dos cards na listagem.
     */
    public function payFull($saleId, $paymentMethod = 'dinheiro')
    {
        $sale = Sale::with('payments')->find($saleId);

        if (!$sale || $sale->user_id !== Auth::id()) {
            session()->flash('error', 'Venda não encontrada ou acesso negado.');
            return;
        }

        $paid = (float) $sale->payments()->where('payment_method', '<>', 'desconto')->sum('amount_paid');
        $remaining = max(0, (float) $sale->total_price - $paid);

        if ($remaining <= 0) {
            session()->flash('info', 'Não há valor restante para pagar nesta venda.');
            return;
        }

        // Registrar pagamento completo
        SalePayment::create([
            'sale_id' => $sale->id,
            'amount_paid' => $remaining,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->format('Y-m-d'),
        ]);

        // Atualizar campos da venda e status
        $totalPaid = (float) $sale->payments()->sum('amount_paid');
        $sale->amount_paid = $totalPaid;
        $sale->status = 'pago';
        $sale->save();

        // Notificar e atualizar interface
        $this->dispatch('sale-updated', ['saleId' => $sale->id]);
        session()->flash('message', 'Pagamento integral registrado com sucesso!');
    }

    #[On('sale-updated')]
    #[On('refreshComponent')]
    public function refreshSales()
    {
        // Método para atualizar a lista quando houver mudanças
    }

    public function getSalesProperty()
    {
        $userId = Auth::id();

        $query = Sale::where('sales.user_id', $userId)
            ->with(['client', 'saleItems.product', 'payments', 'parcelasVenda']);

        // Filtro de pesquisa por nome do cliente
        if ($this->search) {
            $query->whereHas('client', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro de status (legado $this->status e moderno $this->statusFilter)
        $statusValue = $this->statusFilter ?: $this->status;
        if ($statusValue) {
            $val = strtolower($statusValue);
            switch ($val) {
                case 'pending':
                case 'pendente':
                    $query->where(function($q){
                        $q->where('status', 'pendente')
                          ->orWhereRaw("(select COALESCE(sum(amount_paid),0) from sale_payments where sale_payments.sale_id = sales.id) < sales.total_price");
                    });
                    break;
                case 'paid':
                case 'pago':
                case 'concluida':
                case 'finalizada':
                case 'confirmada':
                    $query->where(function($q){
                        $q->whereIn('status', ['pago','concluida','finalizada','confirmada'])
                          ->orWhereRaw("(select COALESCE(sum(amount_paid),0) from sale_payments where sale_payments.sale_id = sales.id) >= sales.total_price");
                    });
                    break;
                case 'partially_paid':
                case 'parcial':
                case 'parcialmente_pago':
                    $query->whereRaw("(select COALESCE(sum(amount_paid),0) from sale_payments where sale_payments.sale_id = sales.id) > 0")
                          ->whereRaw("(select COALESCE(sum(amount_paid),0) from sale_payments where sale_payments.sale_id = sales.id) < sales.total_price");
                    break;
                default:
                    $query->where('status', $statusValue);
                    break;
            }
        }

        // Filtro de tipo de pagamento
        if ($this->payment_type) {
            $query->where('tipo_pagamento', $this->payment_type);
        }

        // Filtro de data
        if ($this->date_start) {
            $query->whereDate('created_at', '>=', $this->date_start);
        }
        if ($this->date_end) {
            $query->whereDate('created_at', '<=', $this->date_end);
        }

        // Filtro de valor
        if ($this->min_value) {
            $query->where('total_price', '>=', $this->min_value);
        }
        if ($this->max_value) {
            $query->where('total_price', '<=', $this->max_value);
        }

        // Status filter already applied above (compatibility for legacy and modern values)


        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->minValue) {
            $query->where('total_price', '>=', $this->minValue);
        }

        if ($this->maxValue) {
            $query->where('total_price', '<=', $this->maxValue);
        }

        if ($this->paymentMethodFilter) {
            // Mapear valores amigáveis (usados na UI) para os valores reais salvos
            // no banco. Ex: 'cash' -> 'dinheiro', 'card' -> ['cartao_credito','cartao_debito']
            $filter = $this->paymentMethodFilter;
            $methods = [];

            if ($filter === 'cash' || $filter === 'dinheiro') {
                $methods = ['dinheiro'];
            } elseif ($filter === 'card' || $filter === 'cartao') {
                $methods = ['cartao_credito', 'cartao_debito', 'cartao'];
            } elseif ($filter === 'pix') {
                $methods = ['pix'];
            } else {
                // Se for outro valor, tente usar direto
                $methods = [$filter];
            }

            $query->where(function($q) use ($methods) {
                $q->whereIn('payment_method', $methods)
                  ->orWhereHas('payments', function($p) use ($methods) {
                      $p->whereIn('payment_method', $methods);
                  });
            });
        }

        if ($this->sellerFilter) {
            $query->where('user_id', $this->sellerFilter);
        }

        // Sistema de Ordenação Avançado
        switch ($this->sortBy) {
            case 'id':
                $query->orderBy('sales.id', $this->sortDirection);
                break;
            case 'client_name':
                $query->join('clients', 'sales.client_id', '=', 'clients.id')
                      ->orderBy('clients.name', $this->sortDirection)
                      ->select('sales.*');
                break;
            case 'total_price':
                $query->orderBy('sales.total_price', $this->sortDirection);
                break;
            case 'status':
                $query->orderBy('sales.status', $this->sortDirection);
                break;
            case 'updated_at':
                $query->orderBy('sales.updated_at', $this->sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('sales.created_at', $this->sortDirection);
                break;
        }

        return $query->paginate($this->perPage);
    }

    public function getPerPageOptions(): array
    {
        if ($this->usesUltraMultipliers()) {
            return [16, 24, 32, 40, 48, 56, 64];
        }

        if ($this->fullHdLayout) {
            return [10, 12, 14, 16, 18, 20, 24];
        }

        return [12, 14, 16, 18, 20, 24, 28];
    }

    public function getClientsProperty()
    {
        return Client::where('user_id', Auth::id())->get();
    }

    public function render()
    {
        // Calcular estatísticas para o header
        $totalSales = Sale::where('user_id', Auth::id())->count();
        $pendingSales = Sale::where('user_id', Auth::id())->where('status', 'pendente')->count();
        $todaySales = Sale::where('user_id', Auth::id())->whereDate('created_at', today())->count();
        $totalRevenue = Sale::where('user_id', Auth::id())->sum('total_price');

        // Coleções para filtros
        $clients = Client::where('user_id', Auth::id())->get();
        $sellers = collect(); // Implementar se houver tabela de vendedores

        return view('livewire.sales.sales-index', [
            'sales' => $this->sales,
            'clients' => $clients,
            'sellers' => $sellers,
            'totalSales' => $totalSales,
            'pendingSales' => $pendingSales,
            'todaySales' => $todaySales,
            'totalRevenue' => $totalRevenue,
            'perPageOptions' => $this->getPerPageOptions(),
            'ultraWindClient' => $this->ultraWindClient,
        ]);
    }

    // Serve como ponte: chamado a partir do botão no card para abrir o modal de export
    public function openExportSaleModalFromCard($saleId)
    {
        // Log para diagnóstico
        \Log::debug('SalesIndex::openExportSaleModalFromCard called', ['saleId' => $saleId]);

        // Dispatch a browser/livewire event para abrir o modal (compatível com o atributo #[On('openExportSaleModal')])
        $this->dispatch('openExportSaleModal', ['saleId' => $saleId]);
    }

    public function openDeleteModal($saleId)
    {
        $this->dispatch('sales:confirm-delete', ['saleId' => $saleId]);
    }

    #[On('exportSalePdf')]
    public function exportPdf($saleId)
    {
        try {
            // Disparar evento de início do download
            $this->dispatch('download-started', [
                'message' => "Gerando PDF da venda #{$saleId}..."
            ]);

            $sale = Sale::with(['client', 'saleItems.product', 'payments'])->findOrFail($saleId);

            // Verificar se a venda pertence ao usuário atual
            if ($sale->user_id !== Auth::id()) {
                $this->dispatch('download-error', [
                    'message' => 'Acesso negado. Esta venda não pertence a você.'
                ]);
                return;
            }

            $pdf = Pdf::loadView('pdfs.sale', compact('sale'));

            // Disparar evento de sucesso
            $this->dispatch('download-completed');

            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sale->client->name);
            $filename = $clientName . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            // Disparar evento de erro
            $this->dispatch('download-error', [
                'message' => 'Erro ao gerar o PDF: ' . $e->getMessage()
            ]);

            Log::error('Erro ao exportar PDF da venda: ' . $e->getMessage());
        }
    }
}
