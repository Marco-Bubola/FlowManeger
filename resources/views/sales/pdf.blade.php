<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #4f46e5;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-title {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-cell {
            display: table-cell;
            padding: 8px 15px 8px 0;
            vertical-align: top;
            width: 50%;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        
        .info-value {
            color: #6b7280;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pago {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pendente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-cancelado {
            background-color: #fecaca;
            color: #991b1b;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .products-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #d1d5db;
        }
        
        .products-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .summary-label {
            display: table-cell;
            width: 70%;
            font-weight: bold;
            color: #374151;
        }
        
        .summary-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            color: #111827;
        }
        
        .total-row {
            border-top: 2px solid #4f46e5;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .total-row .summary-label,
        .total-row .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
        }
        
        .payments-section {
            margin-top: 30px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>RELATÓRIO DE VENDA</h1>
        <p>Venda #{{ $sale->id }}</p>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Informações da Venda -->
    <div class="info-section">
        <div class="info-title">Informações da Venda</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Data da Venda:</span><br>
                    <span class="info-value">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">Status:</span><br>
                    <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações do Cliente -->
    <div class="info-section">
        <div class="info-title">Cliente</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Nome:</span><br>
                    <span class="info-value">{{ $sale->client->name }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">E-mail:</span><br>
                    <span class="info-value">{{ $sale->client->email ?? 'Não informado' }}</span>
                </div>
            </div>
            @if($sale->client->phone)
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Telefone:</span><br>
                    <span class="info-value">{{ $sale->client->phone }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">Tipo de Pagamento:</span><br>
                    <span class="info-value">
                        {{ $sale->tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}
                        @if($sale->tipo_pagamento === 'parcelado')
                            ({{ $sale->parcelas }}x)
                        @endif
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Produtos -->
    <div class="info-section">
        <div class="info-title">Produtos</div>
        @if($sale->saleItems->count() > 0)
        <table class="products-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-right">Preço Unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->product->category)
                        <br><small style="color: #6b7280;">{{ $item->product->category->name }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">R$ {{ number_format($item->price_sale, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6b7280; padding: 20px;">Nenhum produto adicionado a esta venda.</p>
        @endif
    </div>

    <!-- Resumo Financeiro -->
    <div class="summary-box">
        <div class="info-title" style="margin-bottom: 15px;">Resumo Financeiro</div>
        
        <div class="summary-row">
            <div class="summary-label">Subtotal:</div>
            <div class="summary-value">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</div>
        </div>
        
        @if($sale->amount_paid > 0)
        <div class="summary-row">
            <div class="summary-label">Valor Pago:</div>
            <div class="summary-value" style="color: #059669;">R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}</div>
        </div>
        @endif
        
        @if($sale->amount_due > 0)
        <div class="summary-row">
            <div class="summary-label">Valor Pendente:</div>
            <div class="summary-value" style="color: #dc2626;">R$ {{ number_format($sale->amount_due, 2, ',', '.') }}</div>
        </div>
        @endif
        
        <div class="summary-row total-row">
            <div class="summary-label">TOTAL:</div>
            <div class="summary-value">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</div>
        </div>
    </div>

    <!-- Pagamentos -->
    @if($sale->payments->count() > 0)
    <div class="payments-section">
        <div class="info-title">Histórico de Pagamentos</div>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Método</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td class="text-right">R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Parcelas (se aplicável) -->
    @if($sale->tipo_pagamento === 'parcelado' && isset($parcelas) && $parcelas->count() > 0)
    <div class="payments-section">
        <div class="info-title">Parcelas</div>
        <table class="products-table">
            <thead>
                <tr>
                    <th class="text-center">Parcela</th>
                    <th>Data Vencimento</th>
                    <th class="text-right">Valor</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parcelas as $parcela)
                <tr>
                    <td class="text-center">{{ $parcela->numero_parcela }}</td>
                    <td>{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</td>
                    <td class="text-right">R$ {{ number_format($parcela->valor, 2, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $parcela->status }}">{{ ucfirst($parcela->status) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Este relatório foi gerado automaticamente pelo sistema em {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ config('app.name') }} - Sistema de Gestão de Vendas</p>
    </div>
</body>
</html>
