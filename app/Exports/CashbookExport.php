<?php

namespace App\Exports;

use App\Models\Cashbook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashbookExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ano;

    public function __construct(int $ano)
    {
        $this->ano = $ano;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Cashbook::where('user_id', auth()->id())
            ->whereYear('date', $this->ano)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Data',
            'Descrição',
            'Valor',
            'Tipo',
            'Categoria',
        ];
    }

    public function map($cashbook): array
    {
        return [
            $cashbook->date,
            $cashbook->description,
            $cashbook->value,
            $cashbook->type->desc_type,
            $cashbook->category->name,
        ];
    }
}
