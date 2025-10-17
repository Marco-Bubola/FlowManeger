<?php

namespace App\Exports;

use Illuminate\Support\Collection;
// Nota: removemos as interfaces FromCollection/WithHeadings para permitir fallback
// quando o pacote maatwebsite/excel nao estiver instalado. Para habilitar export
// via Laravel-Excel, instale o pacote: composer require maatwebsite/excel
use Illuminate\Support\Facades\DB;

class VendasExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $query = DB::table('sales')
            ->select([
                'id',
                'client_id',
                'created_at',
                'total_price',
                'amount_paid',
                'status',
                'tipo_pagamento'
            ]);

        // Apply simple filters if provided
        if (!empty($this->filters['client_id'])) {
            $query->where('client_id', $this->filters['client_id']);
        }

        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }

        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        // Map rows to desired format if needed
        $mapped = $rows->map(function ($r) {
            return [
                'ID' => $r->id,
                'Client ID' => $r->client_id,
                'Date' => \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
                'Total' => number_format($r->total_price, 2, '.', ''),
                'Paid' => number_format($r->amount_paid ?? 0, 2, '.', ''),
                'Remaining' => number_format(($r->total_price - ($r->amount_paid ?? 0)), 2, '.', ''),
                'Status' => $r->status,
                'Payment Type' => $r->tipo_pagamento,
            ];
        });

        return new Collection($mapped->toArray());
    }

    public function headings(): array
    {
        return [
            'ID', 'Client ID', 'Date', 'Total', 'Paid', 'Remaining', 'Status', 'Payment Type'
        ];
    }
}
