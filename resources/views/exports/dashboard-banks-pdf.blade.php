<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Financeiro Bancos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; }
        h1, h2, p { margin: 0; }
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
    <h1>Dashboard financeiro de bancos</h1>
    <p class="muted">Período: {{ $metrics['periodLabel'] ?? '-' }}</p>

    <table class="grid" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="muted">Faturas do mês</div>
                <div class="value">R$ {{ number_format($metrics['monthTotal'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Bancos com movimento</div>
                <div class="value">{{ number_format($metrics['activeBanksMonth'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Ciclo médio</div>
                <div class="value">R$ {{ number_format($metrics['avgCycleAmount'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Saúde operacional</div>
                <div class="value">{{ number_format($metrics['uploadSuccessAverage'] ?? 0, 1, ',', '.') }}%</div>
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
                <td>Total de bancos</td>
                <td>{{ number_format($metrics['totalBancos'] ?? 0, 0, ',', '.') }}</td>
                <td>Quantidade de invoices</td>
                <td>{{ number_format($metrics['monthCount'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ticket médio</td>
                <td>R$ {{ number_format($metrics['avgMonth'] ?? 0, 2, ',', '.') }}</td>
                <td>Ritmo diário</td>
                <td>R$ {{ number_format($metrics['monthDailyAverage'] ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Banco líder</td>
                <td>{{ data_get($metrics, 'topBankSummary.nome', 'Sem destaque') }}</td>
                <td>Categoria dominante</td>
                <td>{{ data_get($metrics, 'topCategorySummary.label', 'Sem destaque') }}</td>
            </tr>
            <tr>
                <td>Fechamento médio</td>
                <td>{{ number_format($metrics['avgDaysToClose'] ?? 0, 1, ',', '.') }} dias</td>
                <td>Carga anual</td>
                <td>R$ {{ number_format($metrics['totalInvoicesBancos'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Bancos detalhados</h2>
        <table>
            <tr>
                <th>Banco</th>
                <th>Mês</th>
                <th>Ciclo</th>
                <th>Invoices</th>
                <th>Ticket médio</th>
                <th>Fechamento</th>
            </tr>
            @foreach (($metrics['bancosInfo'] ?? []) as $item)
                <tr>
                    <td>{{ $item['nome'] ?? '-' }}</td>
                    <td>R$ {{ number_format($item['month_total'] ?? 0, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item['cycle_total'] ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $item['qtd_invoices'] ?? 0 }}</td>
                    <td>R$ {{ number_format($item['media_invoices'] ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $item['days_to_close'] ?? 0 }} dia(s)</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Categorias do mês</h2>
        <table>
            <tr>
                <th>Categoria</th>
                <th>Valor</th>
            </tr>
            @foreach (($metrics['invoiceCategoryShare'] ?? []) as $item)
                <tr>
                    <td>{{ $item['label'] ?? '-' }}</td>
                    <td>R$ {{ number_format($item['value'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Uploads recentes</h2>
        <table>
            <tr>
                <th>Banco</th>
                <th>Arquivo</th>
                <th>Status</th>
                <th>Sucesso</th>
                <th>Valor</th>
            </tr>
            @foreach (($metrics['recentUploads'] ?? []) as $item)
                <tr>
                    <td>{{ $item['bank'] ?? '-' }}</td>
                    <td>{{ $item['filename'] ?? '-' }}</td>
                    <td>{{ $item['status'] ?? '-' }}</td>
                    <td>{{ number_format($item['success_rate'] ?? 0, 1, ',', '.') }}%</td>
                    <td>R$ {{ number_format($item['total_value'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>