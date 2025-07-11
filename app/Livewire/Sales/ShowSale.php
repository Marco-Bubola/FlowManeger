<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class ShowSale extends Component
{
    public Sale $sale;
    public $parcelas = [];

    // Para adicionar pagamentos
    public $showAddPaymentForm = false;
    public $newPayments = [];

    // Para modal de pagamento das parcelas
    public $showPaymentModal = false;
    public $selectedParcela;
    public $paymentMethod = 'dinheiro';
    public $paymentDate;

    public function mount($id)
    {
        $this->sale = Sale::with(['saleItems.product', 'client', 'payments'])->findOrFail($id);
        $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function removeSaleItem($itemId)
    {
        $saleItem = $this->sale->saleItems()->findOrFail($itemId);
        
        // Restaurar estoque
        $product = Product::find($saleItem->product_id);
        if ($product) {
            $product->stock_quantity += $saleItem->quantity;
            $product->save();
        }

        // Remover item
        $saleItem->delete();

        // Atualizar total da venda
        $this->sale->refresh();
        $totalPrice = $this->sale->saleItems->sum(function ($item) {
            return $item->quantity * $item->price_sale;
        });
        $this->sale->update(['total_price' => $totalPrice]);

        session()->flash('message', 'Produto removido com sucesso!');
    }

    public function toggleAddPaymentForm()
    {
        $this->showAddPaymentForm = !$this->showAddPaymentForm;
        if ($this->showAddPaymentForm) {
            $this->newPayments = [[
                'amount_paid' => '',
                'payment_method' => 'dinheiro',
                'payment_date' => now()->format('Y-m-d'),
            ]];
        } else {
            $this->newPayments = [];
        }
    }

    public function addPaymentRow()
    {
        $this->newPayments[] = [
            'amount_paid' => '',
            'payment_method' => 'dinheiro',
            'payment_date' => now()->format('Y-m-d'),
        ];
    }

    public function removePaymentRow($index)
    {
        unset($this->newPayments[$index]);
        $this->newPayments = array_values($this->newPayments);
    }

    public function addPayments()
    {
        $this->validate([
            'newPayments.*.amount_paid' => 'required|numeric|min:0.01',
            'newPayments.*.payment_method' => 'required|string',
            'newPayments.*.payment_date' => 'required|date',
        ]);

        foreach ($this->newPayments as $paymentData) {
            SalePayment::create([
                'sale_id' => $this->sale->id,
                'amount_paid' => $paymentData['amount_paid'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => $paymentData['payment_date'],
            ]);
        }

        // Atualizar total pago
        $totalPaid = SalePayment::where('sale_id', $this->sale->id)->sum('amount_paid');
        $this->sale->amount_paid = $totalPaid;

        // Atualizar status se necessário
        if ($totalPaid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
        }
        $this->sale->save();

        $this->toggleAddPaymentForm();
        session()->flash('message', 'Pagamentos adicionados com sucesso!');
    }

    public function pagarParcela($parcelaId, $valorPago, $dataPagamento)
    {
        $parcela = VendaParcela::findOrFail($parcelaId);
        
        // Atualizar status da parcela
        $parcela->status = 'paga';
        $parcela->pago_em = $dataPagamento;
        $parcela->save();

        // Registrar pagamento
        SalePayment::create([
            'sale_id' => $parcela->sale_id,
            'amount_paid' => $valorPago,
            'payment_method' => 'parcela',
            'payment_date' => $dataPagamento,
        ]);

        // Atualizar total pago na venda
        $totalPaid = SalePayment::where('sale_id', $this->sale->id)->sum('amount_paid');
        $this->sale->amount_paid = $totalPaid;

        // Atualizar status da venda se necessário
        if ($totalPaid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
        } else {
            $this->sale->status = 'pendente';
        }
        $this->sale->save();

        $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();

        session()->flash('message', 'Parcela registrada como paga!');
    }

    public function openPaymentModal($parcelaId)
    {
        $this->selectedParcela = VendaParcela::findOrFail($parcelaId);
        $this->showPaymentModal = true;
        $this->paymentMethod = 'dinheiro';
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedParcela = null;
        $this->paymentMethod = 'dinheiro';
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function confirmPayment()
    {
        $this->validate([
            'paymentMethod' => 'required|string',
            'paymentDate' => 'required|date',
        ]);

        if (!$this->selectedParcela) {
            session()->flash('error', 'Parcela não encontrada!');
            return;
        }

        // Atualizar status da parcela
        $this->selectedParcela->status = 'paga';
        $this->selectedParcela->pago_em = $this->paymentDate;
        $this->selectedParcela->save();

        // Registrar pagamento
        SalePayment::create([
            'sale_id' => $this->selectedParcela->sale_id,
            'amount_paid' => $this->selectedParcela->valor,
            'payment_method' => $this->paymentMethod,
            'payment_date' => $this->paymentDate,
        ]);

        // Atualizar total pago na venda
        $totalPaid = SalePayment::where('sale_id', $this->sale->id)->sum('amount_paid');
        $this->sale->amount_paid = $totalPaid;

        // Atualizar status da venda se necessário
        if ($totalPaid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
        } else {
            $this->sale->status = 'pendente';
        }
        $this->sale->save();

        // Atualizar parcelas
        $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();

        // Fechar modal
        $this->closePaymentModal();

        session()->flash('message', 'Parcela paga com sucesso!');
    }

    public function exportPdf()
    {
        try {
            // Disparar evento de início do download
            $this->dispatch('download-started', [
                'message' => "Gerando PDF da venda #{$this->sale->id}..."
            ]);

            $pdf = Pdf::loadView('pdfs.sale', ['sale' => $this->sale]);
            
            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->sale->client->name);
            $filename = $clientName . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            // Disparar evento de sucesso
            $this->dispatch('download-completed');
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            // Disparar evento de erro
            $this->dispatch('download-error', [
                'message' => 'Erro ao gerar o PDF: ' . $e->getMessage()
            ]);
            
            \Log::error('Erro ao exportar PDF da venda: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sales.show-sale');
    }
}
