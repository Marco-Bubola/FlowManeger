<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Produtos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1, h2 { margin: 0 0 8px; }
        h1 { font-size: 22px; }
        h2 { font-size: 15px; margin-top: 20px; }
        .muted { color: #64748b; }
        .grid { width: 100%; margin-top: 12px; }
        .grid td { width: 25%; padding: 10px; border: 1px solid #e2e8f0; vertical-align: top; }
        .value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Dashboard de Produtos</h1>
    <div class="muted">Período: {{ $metrics['periodLabel'] ?? '-' }}</div>

    <table class="grid" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="muted">Receita do período</div>
                <div class="value">R$ {{ number_format($metrics['faturamentoPeriodo'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Unidades vendidas</div>
                <div class="value">{{ number_format($metrics['unidadesVendidasPeriodo'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Ticket médio</div>
                <div class="value">R$ {{ number_format($metrics['ticketMedioPeriodo'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="muted">Lucro estimado</div>
                <div class="value">R$ {{ number_format($metrics['lucroEstimadoPeriodo'] ?? 0, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <h2>Kits</h2>
    <table>
        <tr>
            <th>Kits vendidos</th>
            <th>Receita de kits</th>
            <th>Componentes consumidos</th>
            <th>Produtos vinculados</th>
        </tr>
        <tr>
            <td>{{ number_format($metrics['kitsVendidosPeriodo'] ?? 0, 0, ',', '.') }}</td>
            <td>R$ {{ number_format($metrics['receitaKitsPeriodo'] ?? 0, 2, ',', '.') }}</td>
            <td>{{ number_format($metrics['componentesConsumidosViaKits'] ?? 0, 0, ',', '.') }}</td>
            <td>{{ number_format($metrics['produtosLigadosKits'] ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h2>Cobertura por produto</h2>
    <table>
        <tr>
            <th>Produto</th>
            <th>Estoque</th>
            <th>Demanda/dia</th>
            <th>Cobertura</th>
        </tr>
        @foreach (($metrics['coberturaProdutos'] ?? []) as $item)
            <tr>
                <td>{{ $item['name'] ?? '-' }}</td>
                <td>{{ number_format($item['stock_quantity'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format((float) ($item['daily_demand'] ?? 0), 2, ',', '.') }}</td>
                <td>{{ ($item['coverage_days'] ?? null) !== null ? number_format((float) $item['coverage_days'], 1, ',', '.') . ' dias' : 'Sem demanda' }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Cobertura por categoria</h2>
    <table>
        <tr>
            <th>Categoria</th>
            <th>Estoque</th>
            <th>Demanda/dia</th>
            <th>Cobertura</th>
        </tr>
        @foreach (($metrics['coberturaCategorias'] ?? []) as $item)
            <tr>
                <td>{{ $item['category_name'] ?? '-' }}</td>
                <td>{{ number_format($item['stock_quantity'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format((float) ($item['daily_demand'] ?? 0), 2, ',', '.') }}</td>
                <td>{{ ($item['coverage_days'] ?? null) !== null ? number_format((float) $item['coverage_days'], 1, ',', '.') . ' dias' : 'Sem demanda' }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Publicações por período</h2>
    <table>
        <tr>
            <th>Indicador</th>
            <th>Valor</th>
        </tr>
        @foreach ((data_get($metrics, 'marketplacePeriodMetrics.cards', [])) as $item)
            <tr>
                <td>{{ $item['label'] ?? '-' }}</td>
                <td>{{ number_format($item['value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
