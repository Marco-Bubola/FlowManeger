<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\On;

class ExportSaleModal extends Component
{
    public $sale;
    public $showModal = false;
    public $exportType = 'pdf'; // 'pdf', 'image-complete', 'image-summary', etc.

    #[On('openExportSaleModal')]
    public function openExportSaleModal($payload = null)
    {
        \Log::debug('ExportSaleModal::openExportSaleModal called', ['payload' => $payload]);
        // Accept either: openExportSaleModal(96) or openExportSaleModal({ saleId: 96 })
        $saleId = null;
        if (is_array($payload) || is_object($payload)) {
            $arr = (array) $payload;
            $saleId = $arr['saleId'] ?? $arr['id'] ?? null;
        } else {
            $saleId = $payload;
        }

        if (!$saleId) {
            return; // nothing to open
        }

        $this->sale = Sale::with(['client', 'saleItems.product', 'payments'])->findOrFail($saleId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->sale = null;
        $this->exportType = 'pdf';
    }

    public function exportPdf($saleId)
    {
        try {
            $this->dispatch('download-started', [
                'message' => "Gerando PDF da venda #{$saleId}..."
            ]);

            $sale = Sale::with(['client', 'saleItems.product', 'payments'])->findOrFail($saleId);

            if ($sale->user_id !== auth()->id()) {
                $this->dispatch('download-error', ['message' => 'Acesso negado. Esta venda não pertence a você.']);
                return;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.sale', compact('sale'));

            $this->dispatch('download-completed');

            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sale->client->name);
            $filename = $clientName . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            $this->dispatch('download-error', ['message' => 'Erro ao gerar o PDF: ' . $e->getMessage()]);
            \Log::error('ExportSaleModal::exportPdf error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sales.export-sale-modal');
    }
}
