<?php

namespace App\Exports;

use App\Models\ConsortiumParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsortiumParticipantExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $participantId;
    protected $participant;

    public function __construct($participantId)
    {
        $this->participantId = $participantId;
        $this->participant = ConsortiumParticipant::with(['consortium', 'client', 'payments'])
            ->findOrFail($participantId);
    }

    public function collection()
    {
        // Retorna uma coleção com uma única linha de resumo
        return collect([
            [
                'id' => $this->participant->id,
                'client' => $this->participant->client->name,
                'product' => $this->participant->consortium->name ?? 'N/A',
                'quota_number' => $this->participant->quota_number,
                'total_value' => $this->participant->consortium->total_value ?? 0,
                'amount_paid' => $this->participant->payments->sum('amount'),
                'balance' => ($this->participant->consortium->total_value ?? 0) - $this->participant->payments->sum('amount'),
                'status' => $this->participant->status,
                'created_at' => $this->participant->created_at->format('d/m/Y')
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'ID Participante',
            'Cliente',
            'Produto/Bem',
            'Número da Cota',
            'Valor Total',
            'Valor Pago',
            'Saldo Devedor',
            'Status',
            'Data de Adesão'
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'],
            $row['client'],
            $row['product'],
            $row['quota_number'],
            'R$ ' . number_format($row['total_value'], 2, ',', '.'),
            'R$ ' . number_format($row['amount_paid'], 2, ',', '.'),
            'R$ ' . number_format($row['balance'], 2, ',', '.'),
            ucfirst($row['status']),
            $row['created_at']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para o cabeçalho
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Consórcio - Cota ' . $this->participant->quota_number;
    }
}
