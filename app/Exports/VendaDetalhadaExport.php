<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendaDetalhadaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $saleId;
    protected $sale;

    public function __construct($saleId)
    {
        $this->saleId = $saleId;
        $this->sale = Sale::with(['client', 'saleItems.product'])->findOrFail($saleId);
    }

    public function collection()
    {
        return $this->sale->saleItems;
    }

    public function headings(): array
    {
        return [
            'ID Item',
            'Produto',
            'Tipo',
            'Quantidade',
            'Preço Unitário',
            'Subtotal',
            'Desconto',
            'Total'
        ];
    }

    public function map($item): array
    {
        $subtotal = $item->quantity * $item->unit_price;
        $discount = $item->discount ?? 0;
        $total = $subtotal - $discount;

        return [
            $item->id,
            $item->product->name ?? 'N/A',
            $item->product->type === 'kit' ? 'KIT' : 'SIMPLES',
            $item->quantity,
            'R$ ' . number_format($item->unit_price, 2, ',', '.'),
            'R$ ' . number_format($subtotal, 2, ',', '.'),
            'R$ ' . number_format($discount, 2, ',', '.'),
            'R$ ' . number_format($total, 2, ',', '.')
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
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Venda #' . $this->saleId;
    }
}
