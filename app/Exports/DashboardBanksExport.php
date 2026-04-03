<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class DashboardBanksExport
{
    public function __construct(protected array $metrics)
    {
    }

    public function collection(): Collection
    {
        $rows = collect([
            ['Resumo', 'Período', (string) ($this->metrics['periodLabel'] ?? '-'), ''],
            ['Resumo', 'Bancos ativos', (string) ($this->metrics['totalBancos'] ?? 0), ''],
            ['Resumo', 'Bancos com movimento', (string) ($this->metrics['activeBanksMonth'] ?? 0), ''],
            ['Resumo', 'Faturas do mês', $this->formatCurrency($this->metrics['monthTotal'] ?? 0), ''],
            ['Resumo', 'Quantidade de invoices', (string) ($this->metrics['monthCount'] ?? 0), ''],
            ['Resumo', 'Ticket médio do mês', $this->formatCurrency($this->metrics['avgMonth'] ?? 0), ''],
            ['Resumo', 'Carga anual', $this->formatCurrency($this->metrics['totalInvoicesBancos'] ?? 0), ''],
            ['Resumo', 'Ciclo médio', $this->formatCurrency($this->metrics['avgCycleAmount'] ?? 0), ''],
            ['Resumo', 'Fechamento médio', number_format((float) ($this->metrics['avgDaysToClose'] ?? 0), 1, ',', '.') . ' dias', ''],
            ['Resumo', 'Ritmo diário', $this->formatCurrency($this->metrics['monthDailyAverage'] ?? 0), ''],
            ['Resumo', 'Saúde operacional', number_format((float) ($this->metrics['uploadSuccessAverage'] ?? 0), 1, ',', '.') . '%', ''],
            ['Banco líder', (string) data_get($this->metrics, 'topBankSummary.nome', 'Sem destaque'), $this->formatCurrency((float) data_get($this->metrics, 'topBankSummary.month_total', 0)), ''],
            ['Categoria dominante', (string) data_get($this->metrics, 'topCategorySummary.label', 'Sem destaque'), $this->formatCurrency((float) data_get($this->metrics, 'topCategorySummary.value', 0)), ''],
        ]);

        foreach (($this->metrics['bancosInfo'] ?? []) as $item) {
            $rows->push([
                'Bancos detalhados',
                $item['nome'] ?? '-',
                $this->formatCurrency($item['month_total'] ?? 0),
                'Ciclo: ' . $this->formatCurrency($item['cycle_total'] ?? 0) . ' | Invoices: ' . ($item['qtd_invoices'] ?? 0) . ' | Fechamento: ' . ($item['days_to_close'] ?? 0) . ' dia(s)',
            ]);
        }

        foreach (($this->metrics['invoiceCategoryShare'] ?? []) as $item) {
            $rows->push([
                'Categorias do mês',
                $item['label'] ?? '-',
                $this->formatCurrency($item['value'] ?? 0),
                '',
            ]);
        }

        foreach (($this->metrics['recentUploads'] ?? []) as $item) {
            $rows->push([
                'Uploads recentes',
                $item['bank'] ?? '-',
                $this->formatCurrency($item['total_value'] ?? 0),
                ($item['filename'] ?? '-') . ' | ' . ($item['status'] ?? '-') . ' | sucesso ' . number_format((float) ($item['success_rate'] ?? 0), 1, ',', '.') . '%',
            ]);
        }

        return $rows->map(fn (array $row) => [
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