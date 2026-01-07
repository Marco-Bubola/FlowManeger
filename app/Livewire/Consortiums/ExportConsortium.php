<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Consortium;
use App\Models\Client;
use App\Exports\ConsortiumExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use ZipArchive;

class ExportConsortium extends Component
{
    public $showModal = false;
    public $exportType = 'pdf_full'; // pdf_full, pdf_client, pdf_clients_zip, pdf_clients_single, pdf_contract, excel_full, excel_client, excel_all, image_full, image_client
    public $consortiumId = null;
    public $clientId = null;
    public $consortium = null;
    public $client = null;
    public $participants = [];

    protected $listeners = [
        'openExportModal' => 'openModal'
    ];

    public function openModal($consortiumId = null, $clientId = null)
    {
        $this->consortiumId = $consortiumId;
        $this->clientId = $clientId;

        if ($consortiumId) {
            $this->consortium = Consortium::findOrFail($consortiumId);
            $this->participants = $this->consortium->participants()
                ->with('client')
                ->get()
                ->filter(fn ($participant) => $participant->client)
                ->map(fn ($participant) => [
                    'client_id' => $participant->client_id,
                    'label' => $participant->client->name,
                ])
                ->unique('client_id')
                ->values()
                ->toArray();

            if (!$this->clientId && count($this->participants) > 0) {
                $this->clientId = $this->participants[0]['client_id'];
            }
        }

        if ($clientId) {
            $this->client = Client::findOrFail($clientId);
        }

        $this->showModal = true;

        if ($consortiumId) {
            $this->exportType = 'pdf_full';
        } elseif ($clientId) {
            $this->exportType = 'pdf_client';
        } else {
            $this->exportType = 'excel_all';
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['consortiumId', 'clientId', 'consortium', 'client', 'exportType', 'participants']);
    }

    public function export()
    {
        try {
            switch ($this->exportType) {
                case 'pdf_full':
                    return $this->exportPdfFull();
                case 'pdf_client':
                    return $this->exportPdfClient();
                case 'pdf_clients_zip':
                    return $this->exportPdfClientsZip();
                case 'pdf_clients_single':
                    return $this->exportPdfClientsSingle();
                case 'pdf_contract':
                    return $this->exportPdfContract();
                case 'excel_full':
                    return $this->exportExcelFull();
                case 'excel_client':
                    return $this->exportExcelClient();
                case 'excel_all':
                    return $this->exportExcelAll();
                case 'image_full':
                    return $this->exportImageFull();
                case 'image_client':
                    return $this->exportImageClient();
                default:
                    session()->flash('error', 'Tipo de exportação inválido.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao exportar: ' . $e->getMessage());
        }
    }

    protected function exportPdfFull()
    {
        if (!$this->consortiumId) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $export = new ConsortiumExport($this->consortiumId, null, 'full');
        $data = $export->getPdfData();

        $pdf = Pdf::loadView('exports.consortium-pdf', $data);

        $fileName = 'consorcio_' . $this->consortium->name . '_completo.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    protected function exportPdfClient()
    {
        if (!$this->clientId) {
            session()->flash('error', 'Cliente não especificado.');
            return;
        }

        if (!$this->client) {
            $this->client = Client::find($this->clientId);
        }

        $exportType = $this->consortiumId ? 'by_client_consortium' : 'by_client';

        $export = new ConsortiumExport($this->consortiumId, $this->clientId, $exportType);
        $data = $export->getPdfData();

        $pdf = Pdf::loadView('exports.consortium-client-pdf', $data);

        $fileName = 'consorcios_cliente_' . Str::slug($this->client->name, '_') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    protected function exportExcelFull()
    {
        if (!$this->consortiumId) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $fileName = 'consorcio_' . $this->consortium->name . '.xlsx';

        return Excel::download(
            new ConsortiumExport($this->consortiumId, null, 'full'),
            $fileName
        );
    }

    protected function exportExcelClient()
    {
        if (!$this->clientId) {
            session()->flash('error', 'Cliente não especificado.');
            return;
        }

        if (!$this->client) {
            $this->client = Client::find($this->clientId);
        }

        $fileName = 'consorcios_cliente_' . $this->client->name . '.xlsx';

        return Excel::download(
            new ConsortiumExport($this->consortiumId, $this->clientId, $this->consortiumId ? 'by_client_consortium' : 'by_client'),
            $fileName
        );
    }

    protected function exportExcelAll()
    {
        $fileName = 'todos_consorcios.xlsx';

        return Excel::download(
            new ConsortiumExport(null, null, 'all'),
            $fileName
        );
    }

    protected function exportPdfClientsZip()
    {
        if (!class_exists(\ZipArchive::class)) {
            // Fallback: gerar um único PDF com todos os clientes
            return $this->exportPdfClientsSingle();
        }

        if (!$this->consortiumId || !$this->consortium) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $participants = $this->consortium->participants()->with('client')->get();

        if ($participants->isEmpty()) {
            session()->flash('error', 'Nenhum participante encontrado para exportar.');
            return;
        }

        $tempDir = storage_path('app/exports/' . uniqid('consortium_clients_', true));
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($participants as $participant) {
            if (!$participant->client) {
                continue;
            }

            $export = new ConsortiumExport($this->consortiumId, $participant->client_id, 'by_client_consortium');
            $data = $export->getPdfData();

            $pdf = Pdf::loadView('exports.consortium-client-pdf', $data);

            $safeName = Str::slug($participant->client->name ?? 'cliente_' . $participant->participation_number, '_');
            file_put_contents($tempDir . '/cliente_' . $safeName . '.pdf', $pdf->output());
        }

        $pdfFiles = glob($tempDir . '/*.pdf');
        if (empty($pdfFiles)) {
            session()->flash('error', 'Nenhum PDF gerado para exportar.');
            @rmdir($tempDir);
            return;
        }

        $zipPath = storage_path('app/consorcio_' . Str::slug($this->consortium->name, '_') . '_clientes.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            session()->flash('error', 'Não foi possível criar o arquivo ZIP.');
            return;
        }

        foreach ($pdfFiles as $pdfFile) {
            $zip->addFile($pdfFile, basename($pdfFile));
        }

        $zip->close();

        foreach ($pdfFiles as $pdfFile) {
            @unlink($pdfFile);
        }
        @rmdir($tempDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    protected function exportPdfClientsSingle()
    {
        if (!$this->consortiumId || !$this->consortium) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $participants = $this->consortium->participants()->with(['client', 'payments', 'contemplation.draw'])->get();

        if ($participants->isEmpty()) {
            session()->flash('error', 'Nenhum participante encontrado para exportar.');
            return;
        }

        $pdf = Pdf::loadView('exports.consortium-clients-pdf', [
            'consortium' => $this->consortium,
            'participants' => $participants,
        ]);

        $fileName = 'consorcio_' . Str::slug($this->consortium->name, '_') . '_clientes.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    protected function exportPdfContract()
    {
        if (!$this->consortiumId || !$this->consortium) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $pdf = Pdf::loadView('exports.consortium-contract-pdf', [
            'consortium' => $this->consortium,
        ]);

        $fileName = 'contrato_' . Str::slug($this->consortium->name, '_') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    protected function exportImageFull()
    {
        if (!$this->consortiumId) {
            session()->flash('error', 'Consórcio não especificado.');
            return;
        }

        $export = new ConsortiumExport($this->consortiumId, null, 'full');
        $data = $export->getPdfData();

        return $this->streamPngFromView('exports.consortium-pdf', $data, 'consorcio_' . Str::slug($this->consortium->name, '_'));
    }

    protected function exportImageClient()
    {
        if (!$this->clientId) {
            session()->flash('error', 'Cliente não especificado.');
            return;
        }

        if (!$this->client) {
            $this->client = Client::find($this->clientId);
        }

        $exportType = $this->consortiumId ? 'by_client_consortium' : 'by_client';
        $export = new ConsortiumExport($this->consortiumId, $this->clientId, $exportType);
        $data = $export->getPdfData();

        $label = $this->client ? Str::slug($this->client->name, '_') : 'cliente';

        return $this->streamPngFromView('exports.consortium-client-pdf', $data, 'consorcio_cliente_' . $label);
    }

    private function streamPngFromView(string $view, array $data, string $fileLabel)
    {
        if (!class_exists(\Imagick::class)) {
            session()->flash('error', 'Exportação em imagem requer a extensão Imagick instalada no servidor.');
            return null;
        }

        $pdf = Pdf::loadView($view, $data);

        $tempPdf = storage_path('app/' . $fileLabel . '_' . uniqid() . '.pdf');
        file_put_contents($tempPdf, $pdf->output());

        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($tempPdf);
        $imagick->setImageFormat('png');
        $imagick->setIteratorIndex(0);

        $imageData = $imagick->getImageBlob();

        $imagick->clear();
        $imagick->destroy();
        @unlink($tempPdf);

        return response()->streamDownload(function () use ($imageData) {
            echo $imageData;
        }, $fileLabel . '.png', [
            'Content-Type' => 'image/png'
        ]);
    }

    public function render()
    {
        return view('livewire.consortiums.export-consortium');
    }
}
