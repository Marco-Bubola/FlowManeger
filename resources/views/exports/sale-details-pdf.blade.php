<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda #{{ $sale->id }} - {{ $sale->client->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            background: #f9fafb;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #4f46e5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .totals table {
            width: 300px;
            float: right;
        }
        .totals td {
            padding: 8px;
            font-weight: bold;
        }
        .total-row {
            background: #4f46e5 !important;
            color: white;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #4f46e5;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detalhes da Venda #{{ $sale->id }}</h1>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Cliente:</span> {{ $sale->client->name }}
            </div>
            <div class="info-cell">
                <span class="info-label">Email:</span> {{ $sale->client->email ?? 'N/A' }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Telefone:</span> {{ $sale->client->phone ?? 'N/A' }}
            </div>
            <div class="info-cell">
                <span class="info-label">Data da Venda:</span> {{ $sale->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Status:</span>
                <span class="badge {{ $sale->status === 'completed' ? 'badge-completed' : 'badge-pending' }}">
                    {{ ucfirst($sale->status ?? 'Pendente') }}
                </span>
            </div>
            <div class="info-cell">
                <span class="info-label">ID da Venda:</span> {{ $sale->id }}
            </div>
        </div>
    </div>

    <h2 style="margin-bottom: 10px; color: #4f46e5;">Itens da Venda</h2>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Tipo</th>
                <th>Qtd</th>
                <th>Preço Unit.</th>
                <th>Subtotal</th>
                <th>Desconto</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            @php
                $subtotal = $item->quantity * $item->unit_price;
                $discount = $item->discount ?? 0;
                $total = $subtotal - $discount;
            @endphp
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td>
                    @if($item->product->type === 'kit')
                        <span class="badge" style="background: #dbeafe; color: #1e40af;">KIT</span>
                    @else
                        <span class="badge" style="background: #e0e7ff; color: #3730a3;">SIMPLES</span>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($discount, 2, ',', '.') }}</td>
                <td><strong>R$ {{ number_format($total, 2, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>R$ {{ number_format($sale->saleItems->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                }), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Desconto:</td>
                <td>R$ {{ number_format($sale->saleItems->sum('discount'), 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL:</td>
                <td>R$ {{ number_format($sale->total_price, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($sale->payments && $sale->payments->count() > 0)
    <div style="clear: both; margin-top: 40px;">
        <h2 style="margin-bottom: 10px; color: #4f46e5;">Pagamentos</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Método</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td>R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p><strong>FlowManager</strong> - Sistema de Gestão</p>
        <p>Este é um documento gerado automaticamente. Todos os valores são expressos em Reais (R$).</p>
    </div>
</body>
</html>
