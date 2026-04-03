<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class DashboardCashbookExport
{
    public function __construct(protected array $metrics)
    {
    }

    public function collection(): Collection
    {
        $rows = collect([
            ['Resumo', 'Período', (string) ($this->metrics['periodLabel'] ?? '-'), ''],
            ['Resumo', 'Receitas do ano', $this->formatCurrency($this->metrics['totalReceitas'] ?? 0), ''],
            ['Resumo', 'Despesas do ano', $this->formatCurrency($this->metrics['totalDespesas'] ?? 0), ''],
            ['Resumo', 'Saldo total', $this->formatCurrency($this->metrics['saldoTotal'] ?? 0), ''],
            ['Resumo', 'Receita do mês', $this->formatCurrency($this->metrics['receitaMesAtual'] ?? 0), ''],
            ['Resumo', 'Despesa do mês', $this->formatCurrency($this->metrics['despesaMesAtual'] ?? 0), ''],
            ['Resumo', 'Invoices do mês', $this->formatCurrency($this->metrics['invoiceMesAtual'] ?? 0), ''],
            ['Resumo', 'Dias ativos', (string) ($this->metrics['activeDaysCount'] ?? 0), ''],
            ['Resumo', 'Taxa de poupança', number_format((float) ($this->metrics['savingsRatePercent'] ?? 0), 1, ',', '.') . '%', ''],
            ['Resumo', 'Pressão das invoices', number_format((float) ($this->metrics['invoicePressurePercent'] ?? 0), 1, ',', '.') . '%', ''],
            ['Resumo', 'Receita média por dia', $this->formatCurrency($this->metrics['mediaReceitaDia'] ?? 0), ''],
            ['Resumo', 'Despesa média por dia', $this->formatCurrency($this->metrics['mediaDespesaDia'] ?? 0), ''],
            ['Resumo', 'Invoice média por dia', $this->formatCurrency($this->metrics['mediaInvoiceDia'] ?? 0), ''],
            ['Comparativo', 'Mês atual', $this->formatCurrency($this->metrics['saldoMesAtual'] ?? 0), 'saldo'],
            ['Comparativo', 'Mês anterior', $this->formatCurrency(($this->metrics['receitaMesAnterior'] ?? 0) - ($this->metrics['despesaMesAnterior'] ?? 0)), 'saldo'],
            ['Bancos', 'Bancos cadastrados', (string) ($this->metrics['totalBancos'] ?? 0), ''],
            ['Bancos', 'Bancos com gasto no mês', (string) ($this->metrics['bancosAtivosComGasto'] ?? 0), ''],
            ['Categoria líder', (string) data_get($this->metrics, 'topExpenseCategory.categoria', 'Sem destaque'), $this->formatCurrency((float) data_get($this->metrics, 'topExpenseCategory.total', 0)), ''],
            ['Banco líder', (string) data_get($this->metrics, 'topBank.name', 'Sem destaque'), $this->formatCurrency((float) data_get($this->metrics, 'topBank.monthly_total', 0)), ''],
            ['Cofrinhos', 'Total guardado', $this->formatCurrency($this->metrics['totalCofrinhos'] ?? 0), ''],
            ['Cofrinhos', 'Total de metas', $this->formatCurrency($this->metrics['totalMetasCofrinhos'] ?? 0), ''],
            ['Cofrinhos', 'Economia do mês', $this->formatCurrency($this->metrics['economiadoMesAtual'] ?? 0), ''],
            ['Orçamento', 'Orçado no mês', $this->formatCurrency($this->metrics['orcamentoMesTotal'] ?? 0), ''],
            ['Orçamento', 'Executado no mês', $this->formatCurrency($this->metrics['orcamentoMesUsado'] ?? 0), ''],
            ['Orçamento', 'Restante', $this->formatCurrency($this->metrics['orcamentoRestante'] ?? 0), ''],
            ['Orçamento', 'Uso do orçamento', number_format((float) ($this->metrics['orcamentoUsoPercentual'] ?? 0), 1, ',', '.') . '%', ''],
            ['Projeção', '30 dias', $this->formatCurrency($this->metrics['previsao30dias'] ?? 0), ''],
            ['Projeção', '60 dias', $this->formatCurrency($this->metrics['previsao60dias'] ?? 0), ''],
            ['Projeção', '90 dias', $this->formatCurrency($this->metrics['previsao90dias'] ?? 0), ''],
        ]);

        foreach (($this->metrics['bankCycleOverview'] ?? []) as $item) {
            $rows->push([
                'Ciclos bancários',
                $item['name'] ?? '-',
                $this->formatCurrency($item['cycle_total'] ?? 0),
                ($item['cycle_start'] ?? '-') . ' a ' . ($item['cycle_end'] ?? '-') . ' | ' . ($item['days_to_close'] ?? 0) . ' dia(s)',
            ]);
        }

        foreach (($this->metrics['cofrinhosTopMeta'] ?? []) as $item) {
            $rows->push([
                'Top cofrinhos',
                $item['nome'] ?? '-',
                number_format((float) ($item['progresso'] ?? 0), 1, ',', '.') . '%',
                'Guardado: ' . $this->formatCurrency($item['valor_guardado'] ?? 0) . ' | Meta: ' . $this->formatCurrency($item['meta_valor'] ?? 0),
            ]);
        }

        foreach (($this->metrics['orcamentosTopEstouro'] ?? []) as $item) {
            $rows->push([
                'Estouro de orçamento',
                $item['category'] ?? '-',
                $this->formatCurrency($item['estouro'] ?? 0),
                'Gasto: ' . $this->formatCurrency($item['gasto'] ?? 0) . ' | Orçado: ' . $this->formatCurrency($item['orcado'] ?? 0),
            ]);
        }

        foreach (($this->metrics['recentTransactions'] ?? []) as $item) {
            $rows->push([
                'Movimentações recentes',
                $item['description'] ?? 'Sem descrição',
                $this->formatCurrency($item['value'] ?? 0),
                strtoupper((string) ($item['origin'] ?? 'cashbook')) . ' | ' . (string) ($item['meta'] ?? '-'),
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