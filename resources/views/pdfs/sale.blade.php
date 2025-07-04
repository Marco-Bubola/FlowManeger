<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda #{{ $sale->id }} - PDF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
        }
        
        .info-box h3 {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            display: inline-block;
            width: 80px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .products-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .products-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        .products-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .total-section {
            margin-top: 30px;
            border-top: 2px solid #667eea;
            padding-top: 20px;
        }
        
        .total-grid {
            display: table;
            width: 100%;
        }
        
        .total-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .total-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            padding-left: 20px;
        }
        
        .total-box {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .total-row.final {
            border-top: 2px solid #667eea;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
            color: #667eea;
        }
        
        .payments-section {
            margin-top: 20px;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .payments-table th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        
        .payments-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pago {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-pendente {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Venda #{{ $sale->id }}</h1>
        <p>Emitida em {{ $sale->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Informações principais -->
    <div class="info-grid">
        <div class="info-column">
            <div class="info-box">
                <h3>Informações da Venda</h3>
                <div class="info-row">
                    <span class="info-label">ID:</span>
                    <span>#{{ $sale->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data:</span>
                    <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pagamento:</span>
                    <span>{{ $sale->tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}</span>
                </div>
                @if($sale->tipo_pagamento === 'parcelado')
                <div class="info-row">
                    <span class="info-label">Parcelas:</span>
                    <span>{{ $sale->parcelas }}x</span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="info-column">
            <div class="info-box">
                <h3>Cliente</h3>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span>{{ $sale->client->name }}</span>
                </div>
                @if($sale->client->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span>{{ $sale->client->email }}</span>
                </div>
                @endif
                @if($sale->client->phone)
                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span>{{ $sale->client->phone }}</span>
                </div>
                @endif
                @if($sale->client->address)
                <div class="info-row">
                    <span class="info-label">Endereço:</span>
                    <span>{{ $sale->client->address }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Produtos -->
    <h3 style="font-size: 16px; margin-bottom: 15px; color: #374151; border-bottom: 2px solid #667eea; padding-bottom: 5px;">
        Produtos da Venda
    </h3>
    
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 40%;">Produto</th>
                <th style="width: 15%;" class="text-center">Qtd</th>
                <th style="width: 20%;" class="text-right">Preço Unit.</th>
                <th style="width: 25%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->name }}</strong>
                    @if($item->product->description)
                    <br><small style="color: #6b7280;">{{ Str::limit($item->product->description, 60) }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                <td class="text-right font-bold">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totais e Pagamentos -->
    <div class="total-section">
        <div class="total-grid">
            <div class="total-left">
                @if($sale->salePayments->count() > 0)
                <div class="payments-section">
                    <h3 style="font-size: 14px; margin-bottom: 10px; color: #374151;">Pagamentos Realizados</h3>
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Método</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->salePayments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td>{{ $payment->payment_method ?? 'Não informado' }}</td>
                                <td class="text-right">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            
            <div class="total-right">
                <div class="total-box">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>R$ {{ number_format($sale->saleItems->sum(function($item) { return $item->price * $item->quantity; }), 2, ',', '.') }}</span>
                    </div>
                    @if($sale->discount > 0)
                    <div class="total-row">
                        <span>Desconto:</span>
                        <span>-R$ {{ number_format($sale->discount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="total-row final">
                        <span>Total Geral:</span>
                        <span>R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
                    </div>
                    @if($sale->amount_paid > 0)
                    <div class="total-row" style="color: #059669;">
                        <span>Total Pago:</span>
                        <span>R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}</span>
                    </div>
                    @if($sale->amount_paid < $sale->total_price)
                    <div class="total-row" style="color: #dc2626;">
                        <span>Saldo Restante:</span>
                        <span>R$ {{ number_format($sale->total_price - $sale->amount_paid, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Gerenciamento de Vendas - {{ config('app.name', 'Flow Manager') }}</p>
    </div>
</body>
</html>
