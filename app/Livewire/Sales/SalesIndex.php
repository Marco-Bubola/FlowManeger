<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesIndex extends Component
{
    use WithPagination;

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
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->search = request('search', '');
        $this->status = request('status', '');
        $this->filter = request('filter', '');
        $this->payment_type = request('payment_type', '');
        $this->date_start = request('date_start', '');
        $this->date_end = request('date_end', '');
        $this->min_value = request('min_value', '');
        $this->max_value = request('max_value', '');
        $this->perPage = request('perPage', 12);
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

    public function confirmDelete($saleId)
    {
        $this->deletingSale = Sale::find($saleId);
        $this->showDeleteModal = true;
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

    #[On('sale-updated')]
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

        // Filtro de status
        if ($this->status) {
            $query->where('status', $this->status);
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

        // Ordenação
        switch ($this->filter) {
            case 'created_at':
                $query->orderBy('sales.created_at', 'desc');
                break;
            case 'updated_at':
                $query->orderBy('sales.updated_at', 'desc');
                break;
            case 'name_asc':
                $query->join('clients', 'sales.client_id', '=', 'clients.id')
                      ->orderBy('clients.name', 'asc')
                      ->select('sales.*');
                break;
            case 'name_desc':
                $query->join('clients', 'sales.client_id', '=', 'clients.id')
                      ->orderBy('clients.name', 'desc')
                      ->select('sales.*');
                break;
            case 'price_asc':
                $query->orderBy('sales.total_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sales.total_price', 'desc');
                break;
            default:
                $query->orderByRaw("CASE WHEN status = 'pago' THEN 1 ELSE 0 END")
                      ->orderBy('created_at', 'desc');
        }

        return $query->paginate($this->perPage);
    }

    public function getClientsProperty()
    {
        return Client::where('user_id', Auth::id())->get();
    }

    public function render()
    {
        return view('livewire.sales.sales-index', [
            'sales' => $this->sales,
            'clients' => $this->clients,
        ]);
    }

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
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "venda-{$sale->id}-" . now()->format('Y-m-d_H-i-s') . ".pdf");
            
        } catch (\Exception $e) {
            // Disparar evento de erro
            $this->dispatch('download-error', [
                'message' => 'Erro ao gerar o PDF: ' . $e->getMessage()
            ]);
            
            \Log::error('Erro ao exportar PDF da venda: ' . $e->getMessage());
        }
    }
}
