<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Financeiro Cashbook</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 22px; }
        h2 { font-size: 14px; margin-bottom: 8px; }
        .muted { color: #64748b; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 12px; }
        .grid td { width: 25%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; vertical-align: top; }
        .value { margin-top: 6px; font-size: 17px; font-weight: bold; }
        .section { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 7px; text-align: left; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Dashboard financeiro do cashbook</h1>
    <p class="muted">Período: {{ $metrics['periodLabel'] ?? '-' }}</p>

    <table class="grid" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="muted">Saldo total</div>
                <div class="value">R$ {{ number_format($metrics['saldoTotal'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Saldo do mês</div>
                <div class="value">R$ {{ number_format($metrics['saldoMesAtual'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Invoices do mês</div>
                <div class="value">R$ {{ number_format($metrics['invoiceMesAtual'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Taxa de poupança</div>
                <div class="value">{{ number_format($metrics['savingsRatePercent'] ?? 0, 1, ',', '.') }}%</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2>Indicadores executivos</h2>
        <table>
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
                <th>Indicador</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Receita do mês</td>
                <td>R$ {{ number_format($metrics['receitaMesAtual'] ?? 0, 2, ',', '.') }}</td>
                <td>Despesa do mês</td>
                <td>R$ {{ number_format($metrics['despesaMesAtual'] ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Dias ativos</td>
                <td>{{ number_format($metrics['activeDaysCount'] ?? 0, 0, ',', '.') }}</td>
                <td>Pressão das invoices</td>
                <td>{{ number_format($metrics['invoicePressurePercent'] ?? 0, 1, ',', '.') }}%</td>
            </tr>
            <tr>
                <td>Receita média/dia</td>
                <td>R$ {{ number_format($metrics['mediaReceitaDia'] ?? 0, 2, ',', '.') }}</td>
                <td>Despesa média/dia</td>
                <td>R$ {{ number_format($metrics['mediaDespesaDia'] ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Banco líder</td>
                <td>{{ data_get($metrics, 'topBank.name', 'Sem destaque') }}</td>
                <td>Categoria líder</td>
                <td>{{ data_get($metrics, 'topExpenseCategory.categoria', 'Sem destaque') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Orçamento e projeção</h2>
        <table>
            <tr>
                <th>Orçado</th>
                <th>Executado</th>
                <th>Restante</th>
                <th>Uso</th>
            </tr>
            <tr>
                <td>R$ {{ number_format($metrics['orcamentoMesTotal'] ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($metrics['orcamentoMesUsado'] ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($metrics['orcamentoRestante'] ?? 0, 2, ',', '.') }}</td>
                <td>{{ number_format($metrics['orcamentoUsoPercentual'] ?? 0, 1, ',', '.') }}%</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>30 dias</th>
                <th>60 dias</th>
                <th>90 dias</th>
            </tr>
            <tr>
                <td>R$ {{ number_format($metrics['previsao30dias'] ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($metrics['previsao60dias'] ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($metrics['previsao90dias'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Ciclos bancários</h2>
        <table>
            <tr>
                <th>Banco</th>
                <th>Período</th>
                <th>Total do ciclo</th>
                <th>Dias para fechar</th>
            </tr>
            @foreach (($metrics['bankCycleOverview'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] ?? '-' }}</td>
                    <td>{{ $item['cycle_start'] ?? '-' }} a {{ $item['cycle_end'] ?? '-' }}</td>
                    <td>R$ {{ number_format($item['cycle_total'] ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $item['days_to_close'] ?? 0 }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Cofrinhos em destaque</h2>
        <table>
            <tr>
                <th>Cofrinho</th>
                <th>Progresso</th>
                <th>Guardado</th>
                <th>Meta</th>
            </tr>
            @foreach (($metrics['cofrinhosTopMeta'] ?? []) as $item)
                <tr>
                    <td>{{ $item['nome'] ?? '-' }}</td>
                    <td>{{ number_format($item['progresso'] ?? 0, 1, ',', '.') }}%</td>
                    <td>R$ {{ number_format($item['valor_guardado'] ?? 0, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item['meta_valor'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Movimentações recentes</h2>
        <table>
            <tr>
                <th>Descrição</th>
                <th>Origem</th>
                <th>Valor</th>
                <th>Data</th>
            </tr>
            @foreach (($metrics['recentTransactions'] ?? []) as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'Sem descrição' }}</td>
                    <td>{{ strtoupper($item['origin'] ?? 'cashbook') }}</td>
                    <td>R$ {{ number_format($item['value'] ?? 0, 2, ',', '.') }}</td>
                    <td>{{ !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>