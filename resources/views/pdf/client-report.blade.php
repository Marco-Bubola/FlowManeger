<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório do Cliente - {{ $client->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #6b7280;
            margin: 5px 0;
            font-size: 14px;
        }
        
        .client-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #4f46e5;
        }
        
        .client-info h2 {
            color: #4f46e5;
            margin: 0 0 15px 0;
            font-size: 20px;
        }
        
        .client-info .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
        }
        
        .info-item .label {
            font-weight: bold;
            color: #374151;
            margin-right: 10px;
            min-width: 120px;
        }
        
        .info-item .value {
            color: #6b7280;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card.primary {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }
        
        .stat-card.success {
            border-color: #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        .stat-card.warning {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }
        
        .stat-card.danger {
            border-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        
        .stat-card .subtitle {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .section h2 {
            color: #4f46e5;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .table th {
            background: #f9fafb;
            color: #374151;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pago {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-pendente {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .currency {
            font-weight: bold;
            color: #059669;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        
        .filters-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .filters-info h3 {
            color: #0369a1;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .chart-placeholder {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #9ca3af;
            margin: 20px 0;
        }
        
        @media print {
            body { margin: 0; }
            .page-break { page-break-before: always; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Relatório do Cliente</h1>
        <div class="subtitle">{{ $client->name }}</div>
        <div class="subtitle">Gerado em {{ $generatedAt->format('d/m/Y H:i:s') }}</div>
        @if($type === 'complete')
            <div class="subtitle"><strong>Relatório Completo</strong> - Todas as informações</div>
        @elseif($type === 'vendas')
            <div class="subtitle"><strong>Relatório de Vendas</strong> - Lista detalhada</div>
        @elseif($type === 'financeiro')
            <div class="subtitle"><strong>Relatório Financeiro</strong> - Resumo + parcelas</div>
        @endif
    </div>

    <!-- Filtros Aplicados -->
    @if($filterYear || $filterMonth !== 'all' || $filterStatus !== 'all' || $filterPaymentType !== 'all')
    <div class="filters-info">
        <h3>🔍 Filtros Aplicados:</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            @if($filterYear)
                <div><strong>Ano:</strong> {{ $filterYear }}</div>
            @endif
            @if($filterMonth !== 'all')
                <div><strong>Mês:</strong> {{ \Carbon\Carbon::create()->month($filterMonth)->translatedFormat('F') }}</div>
            @endif
            @if($filterStatus !== 'all')
                <div><strong>Status:</strong> {{ ucfirst($filterStatus) }}</div>
            @endif
            @if($filterPaymentType !== 'all')
                <div><strong>Pagamento:</strong> {{ ucfirst(str_replace('_', ' ', $filterPaymentType)) }}</div>
            @endif
        </div>
    </div>
    @endif

    <!-- Informações do Cliente -->
    <div class="client-info">
        <h2>👤 Informações do Cliente</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Nome:</span>
                <span class="value">{{ $client->name }}</span>
            </div>
            @if($client->email)
            <div class="info-item">
                <span class="label">Email:</span>
                <span class="value">{{ $client->email }}</span>
            </div>
            @endif
            @if($client->phone)
            <div class="info-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ $client->phone }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="label">Cliente há:</span>
                <span class="value">{{ $diasComoCliente }} dias</span>
            </div>
            @if($primeiraCompra)
            <div class="info-item">
                <span class="label">Primeira Compra:</span>
                <span class="value">{{ \Carbon\Carbon::parse($primeiraCompra)->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($ultimaCompra)
            <div class="info-item">
                <span class="label">Última Compra:</span>
                <span class="value">{{ \Carbon\Carbon::parse($ultimaCompra)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Resumo Financeiro -->
    <div class="section">
        <h2>💰 Resumo Financeiro</h2>
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total de Vendas</h3>
                <div class="value">{{ $totalVendas }}</div>
                <div class="subtitle">Volume total</div>
            </div>
            <div class="stat-card success">
                <h3>Total Faturado</h3>
                <div class="value currency">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</div>
                <div class="subtitle">Receita bruta</div>
            </div>
            <div class="stat-card success">
                <h3>Total Pago</h3>
                <div class="value currency">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                <div class="subtitle">Receita líquida</div>
            </div>
            <div class="stat-card warning">
                <h3>Restante a Pagar</h3>
                <div class="value currency">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
                <div class="subtitle">
                    @if($totalFaturado > 0)
                        {{ number_format(($totalPendente / $totalFaturado) * 100, 1) }}% pendente
                    @else
                        0% pendente
                    @endif
                </div>
            </div>
            <div class="stat-card primary">
                <h3>Ticket Médio</h3>
                <div class="value currency">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                <div class="subtitle">Valor médio por venda</div>
            </div>
        </div>
    </div>

    <!-- Lista de Vendas -->
    @if($type === 'complete' || $type === 'vendas')
    <div class="section">
        <h2>🛒 Lista de Vendas ({{ count($vendas) }} vendas)</h2>
        @if(count($vendas) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                    <th>Valor Pago</th>
                    <th>Restante</th>
                    <th>Status</th>
                    <th>Pagamento</th>
                    <th>Parcelas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendas as $venda)
                <tr>
                    <td>#{{ $venda['id'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda['created_at'])->format('d/m/Y') }}</td>
                    <td class="currency">R$ {{ number_format($venda['total_price'], 2, ',', '.') }}</td>
                    <td class="currency">R$ {{ number_format($venda['amount_paid'] ?? 0, 2, ',', '.') }}</td>
                    <td class="currency">R$ {{ number_format($venda['total_price'] - ($venda['amount_paid'] ?? 0), 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ $venda['status'] }}">
                            {{ ucfirst($venda['status']) }}
                        </span>
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $venda['tipo_pagamento'])) }}</td>
                    <td>{{ $venda['parcelas'] ?? 1 }}x</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="chart-placeholder">
            <p>📝 Nenhuma venda encontrada com os filtros aplicados</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Parcelas (para relatórios completo e financeiro) -->
    @if(($type === 'complete' || $type === 'financeiro') && isset($parcelas) && count($parcelas) > 0)
    <div class="section page-break">
        <h2>📅 Parcelas ({{ count($parcelas) }} parcelas)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Venda</th>
                    <th>Parcela</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parcelas as $parcela)
                <tr>
                    <td>#{{ $parcela['sale']['id'] }}</td>
                    <td>{{ $parcela['numero_parcela'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($parcela['data_vencimento'])->format('d/m/Y') }}</td>
                    <td class="currency">R$ {{ number_format($parcela['valor'], 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ $parcela['status'] }}">
                            {{ ucfirst($parcela['status']) }}
                        </span>
                    </td>
                    <td>
                        @if(\Carbon\Carbon::parse($parcela['data_vencimento'])->isPast() && $parcela['status'] === 'pendente')
                            <span style="color: #dc2626;">⚠️ Vencida</span>
                        @elseif($parcela['status'] === 'pendente')
                            <span style="color: #059669;">⏳ Em dia</span>
                        @else
                            <span style="color: #059669;">✅ Pago</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Produtos Mais Comprados (apenas relatório completo) -->
    @if($type === 'complete' && isset($produtosMaisComprados) && count($produtosMaisComprados) > 0)
    <div class="section">
        <h2>⭐ Produtos Mais Comprados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade Total</th>
                    <th>Número de Compras</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtosMaisComprados as $produto)
                <tr>
                    <td>{{ $produto['produto'] }}</td>
                    <td><strong>{{ $produto['quantidade'] }} unidades</strong></td>
                    <td>{{ $produto['vendas'] }} compras</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Analytics (apenas relatório completo) -->
    @if($type === 'complete' && isset($vendasPorStatus) && count($vendasPorStatus) > 0)
    <div class="section">
        <h2>📊 Analytics</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
            <div>
                <h3 style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">🔢 Vendas por Status</h3>
                @foreach($vendasPorStatus as $status => $total)
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                    <span>{{ ucfirst($status) }}:</span>
                    <strong>{{ $total }} vendas</strong>
                </div>
                @endforeach
            </div>
            
            @if(isset($categoriasMaisCompradas) && count($categoriasMaisCompradas) > 0)
            <div>
                <h3 style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">🏷️ Categorias Preferidas</h3>
                @foreach($categoriasMaisCompradas as $categoria)
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                    <span>{{ $categoria['categoria'] }}:</span>
                    <strong>{{ $categoria['quantidade'] }} unidades</strong>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>FlowManager</strong> - Sistema de Gestão de Vendas</p>
        <p>Relatório gerado automaticamente em {{ $generatedAt->format('d/m/Y \à\s H:i:s') }}</p>
        <p style="margin-top: 10px; font-size: 10px; color: #9ca3af;">
            Este documento contém informações confidenciais do cliente {{ $client->name }}
        </p>
    </div>
</body>
</html>
