<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SalePayment;
use App\Models\VendaParcela;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class ShowSale extends Component
{
    public Sale $sale;
    public $parcelas = [];
    public $activeTab = 'resumo'; // Nova propriedade para controlar a aba ativa

    // Para adicionar pagamentos
    public $showAddPaymentForm = false;
    public $newPayments = [];

    // Para modal de pagamento das parcelas
    public $showPaymentModal = false;
    public $selectedParcela;
    public $paymentMethod = 'dinheiro';
    public $paymentDate;

    // Modal de desconto/zerar restante
    public bool $showDiscountModal = false;


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

        session()->flash('success', 'Produto removido com sucesso!');
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

        // Aplicar descontos caso tenham sido registrados
        foreach ($this->newPayments as $paymentData) {
            if (isset($paymentData['payment_method']) && $paymentData['payment_method'] === 'desconto') {
                $discount = floatval($paymentData['amount_paid']);
                $this->sale->total_price = max(0, $this->sale->total_price - $discount);
                $this->sale->save();
            }
        }

        // Recarregar os dados da venda e relacionamentos
        $this->sale->refresh();
        $this->sale->load(['payments', 'parcelasVenda']);

        // Atualizar status se necessário
        if ($this->sale->total_paid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
            $this->sale->save();
        }

        $this->toggleAddPaymentForm();
        session()->flash('success', 'Pagamentos adicionados com sucesso!');
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

        // Recarregar os dados da venda e relacionamentos
        $this->sale->refresh();
        $this->sale->load(['payments', 'parcelasVenda']);

        // Atualizar status da venda se necessário
        if ($this->sale->total_paid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
        } else {
            $this->sale->status = 'pendente';
        }
        $this->sale->save();

        $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();

        session()->flash('success', 'Parcela registrada como paga!');
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

    // Desconto: abrir modal
    public function openDiscountModal()
    {
        // Não abrir modal se não houver valor restante
        if ($this->sale->remaining_amount <= 0) {
            session()->flash('warning', 'Não há valor restante para zerar.');
            return;
        }

        $this->showDiscountModal = true;
    }

    public function cancelDiscount()
    {
        $this->showDiscountModal = false;
    }

    public function applyDiscountToZero()
    {
        // Valor restante
        $remaining = $this->sale->remaining_amount; // usa accessor

        if ($remaining <= 0) {
            session()->flash('warning', 'Não há valor restante para zerar.');
            $this->showDiscountModal = false;
            return;
        }

        try {
            // Criar registro de pagamento do tipo desconto e abater do total
            SalePayment::create([
                'sale_id' => $this->sale->id,
                'amount_paid' => $remaining,
                'payment_method' => 'desconto',
                'payment_date' => now()->format('Y-m-d'),
            ]);

            // Abater do total_price
            $this->sale->total_price = max(0, $this->sale->total_price - $remaining);
            $this->sale->save();

            // Recarregar
            $this->sale->refresh();
            $this->sale->load(['payments', 'parcelasVenda']);

            // Atualizar status
            if ($this->sale->total_paid >= $this->sale->total_price) {
                $this->sale->status = 'pago';
            } else {
                $this->sale->status = 'pendente';
            }
            $this->sale->save();

            $this->showDiscountModal = false;
            session()->flash('success', 'Desconto aplicado. Valor restante zerado.');

        } catch (\Exception $e) {
            Log::error('Erro ao aplicar desconto: ' . $e->getMessage());
            session()->flash('error', 'Erro ao aplicar desconto.');
        }
    }

    /**
     * Registrar pagamento integral para a venda exibida (pagar tudo).
     */
    public function payFull($paymentMethod = 'dinheiro')
    {
        // Recarregar para garantir dados atualizados
        $this->sale->refresh();

        $paid = (float) $this->sale->payments()->where('payment_method', '<>', 'desconto')->sum('amount_paid');
        $remaining = max(0, (float) $this->sale->total_price - $paid);

        if ($remaining <= 0) {
            session()->flash('info', 'Não há valor restante para pagar nesta venda.');
            return;
        }

        try {
            SalePayment::create([
                'sale_id' => $this->sale->id,
                'amount_paid' => $remaining,
                'payment_method' => $paymentMethod,
                'payment_date' => now()->format('Y-m-d'),
            ]);

            // Atualizar valores e status
            $this->sale->refresh();
            $this->sale->amount_paid = (float) $this->sale->payments()->sum('amount_paid');
            $this->sale->status = ($this->sale->total_paid >= $this->sale->total_price) ? 'pago' : 'pendente';
            $this->sale->save();

            // Atualizar parcelas e relacionamentos
            $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)->orderBy('numero_parcela')->get();

            session()->flash('success', 'Pagamento integral registrado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao registrar pagamento integral: ' . $e->getMessage());
            session()->flash('error', 'Erro ao registrar pagamento.');
        }
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

        // Recarregar os dados da venda e relacionamentos
        $this->sale->refresh();
        $this->sale->load(['payments', 'parcelasVenda']);

        // Atualizar status da venda se necessário
        if ($this->sale->total_paid >= $this->sale->total_price) {
            $this->sale->status = 'pago';
        } else {
            $this->sale->status = 'pendente';
        }
        $this->sale->save();

        // Atualizar parcelas
        $this->parcelas = VendaParcela::where('sale_id', $this->sale->id)
            ->orderBy('numero_parcela')
            ->get();

        // Recarregar a venda com todos os relacionamentos
        $this->sale->refresh();
        $this->sale->load(['saleItems.product', 'client', 'payments']);

        // Fechar modal
        $this->closePaymentModal();

        session()->flash('success', 'Parcela paga com sucesso!');
    }

    public function exportPdf()
    {
        try {
            // Disparar evento de início do download
            $this->dispatch('download-started', [
                'message' => "Gerando PDF da venda #{$this->sale->id}..."
            ]);

            // Pré-processar imagens WebP se necessário
            $this->preprocessImages();

            $pdf = Pdf::loadView('pdfs.sale', ['sale' => $this->sale]);

            // Restaurar imagens originais após gerar PDF
            $this->restoreOriginalImages();

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

            Log::error('Erro ao exportar PDF da venda: ' . $e->getMessage());
        }
    }

    private function preprocessImages()
    {
        // Obter lista de imagens disponíveis no diretório
        $availableImages = glob(public_path('storage/products/*'));
        $availableImageMap = [];

        foreach ($availableImages as $imagePath) {
            if (is_file($imagePath)) {
                $filename = basename($imagePath);
                $availableImageMap[$filename] = $imagePath;
            }
        }

        foreach ($this->sale->saleItems as $item) {
            if ($item->product->image && $item->product->image !== 'product-placeholder.png') {
                $imagePath = public_path('storage/products/' . $item->product->image);

                // Verificar se a imagem existe
                if (!file_exists($imagePath)) {
                    // Tentar encontrar uma imagem disponível no diretório
                    if (!empty($availableImageMap)) {
                        $randomImage = array_rand($availableImageMap);
                        $imagePath = $availableImageMap[$randomImage];
                        $item->product->image = basename($imagePath);
                    }
                }

                if (file_exists($imagePath)) {
                    $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

                    // Convert WebP images to JPEG for PDF compatibility
                    if ($extension === 'webp') {
                        try {
                            if (function_exists('imagecreatefromwebp')) {
                                $convertedPath = $this->convertWebpToJpeg($imagePath);
                                if ($convertedPath && file_exists($convertedPath)) {
                                    // Move the converted file to the products directory
                                    $newFilename = pathinfo($item->product->image, PATHINFO_FILENAME) . '_pdf.jpg';
                                    $newPath = public_path('storage/products/' . $newFilename);

                                    if (copy($convertedPath, $newPath)) {
                                        // Store original image name and update for PDF
                                        $item->product->original_image = $item->product->image;
                                        $item->product->image = $newFilename;

                                        // Clean up temporary file
                                        @unlink($convertedPath);
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to convert WebP image for PDF: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }    private function convertWebpToJpeg($webpPath)
    {
        try {
            $webpImage = imagecreatefromwebp($webpPath);
            if ($webpImage === false) {
                return false;
            }

            $pathInfo = pathinfo($webpPath);
            $jpegPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_converted.jpg';

            $success = imagejpeg($webpImage, $jpegPath, 90);
            imagedestroy($webpImage);

            return $success ? $jpegPath : false;

        } catch (\Exception $e) {
            Log::error("Error converting WebP to JPEG: " . $e->getMessage());
            return false;
        }
    }

    private function restoreOriginalImages()
    {
        foreach ($this->sale->saleItems as $item) {
            if (isset($item->product->original_image)) {
                // Remove temporary converted file
                $tempFile = public_path('storage/products/' . $item->product->image);
                if (file_exists($tempFile) && strpos($item->product->image, '_pdf.jpg') !== false) {
                    @unlink($tempFile);
                }

                // Restore original image name
                $item->product->image = $item->product->original_image;
                unset($item->product->original_image);
            }
        }
    }

    public function render()
    {
        return view('livewire.sales.show-sale');
    }
}
