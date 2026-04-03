<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class DashboardProductsExport
{
    public function __construct(protected array $metrics)
    {
    }

    public function collection(): Collection
    {
        $rows = collect();

        $summaryRows = [
            ['Resumo', 'Periodo', $this->metrics['periodLabel'] ?? '-', ''],
            ['Resumo', 'Receita do periodo', $this->formatCurrency($this->metrics['faturamentoPeriodo'] ?? 0), ''],
            ['Resumo', 'Unidades vendidas', (string) ($this->metrics['unidadesVendidasPeriodo'] ?? 0), ''],
            ['Resumo', 'Pedidos do periodo', (string) ($this->metrics['pedidosPeriodo'] ?? 0), ''],
            ['Resumo', 'Ticket medio', $this->formatCurrency($this->metrics['ticketMedioPeriodo'] ?? 0), ''],
            ['Resumo', 'Lucro estimado', $this->formatCurrency($this->metrics['lucroEstimadoPeriodo'] ?? 0), ''],
            ['Kits', 'Kits vendidos', (string) ($this->metrics['kitsVendidosPeriodo'] ?? 0), ''],
            ['Kits', 'Receita de kits', $this->formatCurrency($this->metrics['receitaKitsPeriodo'] ?? 0), ''],
            ['Kits', 'Componentes consumidos via kits', (string) ($this->metrics['componentesConsumidosViaKits'] ?? 0), ''],
            ['Kits', 'Produtos vinculados a kits', (string) ($this->metrics['produtosLigadosKits'] ?? 0), ''],
        ];

        $rows = $rows->merge($summaryRows);

        foreach (($this->metrics['coberturaProdutos'] ?? []) as $item) {
            $rows->push([
                'Cobertura por produto',
                $item['name'] ?? '-',
                ($item['coverage_days'] ?? null) !== null ? number_format((float) $item['coverage_days'], 1, ',', '.') . ' dias' : 'Sem demanda',
                'Estoque: ' . ($item['stock_quantity'] ?? 0) . ' | Demanda dia: ' . number_format((float) ($item['daily_demand'] ?? 0), 2, ',', '.'),
            ]);
        }

        foreach (($this->metrics['coberturaCategorias'] ?? []) as $item) {
            $rows->push([
                'Cobertura por categoria',
                $item['category_name'] ?? '-',
                ($item['coverage_days'] ?? null) !== null ? number_format((float) $item['coverage_days'], 1, ',', '.') . ' dias' : 'Sem demanda',
                'Estoque: ' . ($item['stock_quantity'] ?? 0) . ' | Demanda dia: ' . number_format((float) ($item['daily_demand'] ?? 0), 2, ',', '.'),
            ]);
        }

        foreach (($this->metrics['topKits'] ?? []) as $item) {
            $rows->push([
                'Top kits',
                $item['name'] ?? '-',
                (string) ($item['total_vendido'] ?? 0),
                'Receita: ' . $this->formatCurrency($item['receita_total'] ?? 0),
            ]);
        }

        foreach ((data_get($this->metrics, 'marketplacePeriodMetrics.cards', [])) as $item) {
            $rows->push([
                'Publicacoes por periodo',
                $item['label'] ?? '-',
                (string) ($item['value'] ?? 0),
                strtoupper((string) ($item['tone'] ?? '')),
            ]);
        }

        return $rows->map(fn ($row) => [
            'secao' => $row[0],
            'indicador' => $row[1],
            'valor' => $row[2],
            'detalhe' => $row[3],
        ]);
    }

    public function headings(): array
    {
        return ['Secao', 'Indicador', 'Valor', 'Detalhe'];
    }

    protected function formatCurrency(float|int $value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
