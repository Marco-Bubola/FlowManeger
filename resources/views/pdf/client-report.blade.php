<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório do Cliente - {{ $client->name }}</title>
    <style>

    /* Forcar ajuste de cores na hora de imprimir/gerar PDF (ajuda DomPDF) */
    * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #1f2937;
            line-height: 1.5;
            background: #f9fafb;
        }

        /* Header moderno com gradiente */
        .header {
            background: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-align: center;
            padding: 25px 20px;
            margin: -15px -15px 18px -15px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #ffffff !important;
            margin: 0;
            font-size: 26px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .header .subtitle {
            color: #1818ec !important;
            margin: 4px 0 0 0;
            font-size: 13px;
        }

        /* Subtitles fora do header devem ser escuras para nao sumirem sobre fundo claro */
        .subtitle {
            color: #4b5563 !important;
        }

        /* Cliente info compacto */
        .client-info {
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 14px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }

        .client-info h2 {
            color: #667eea !important;
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .client-info .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 11px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 4px 0;
        }

        .info-item .label {
            font-weight: 600;
            color: #1f2937 !important;
            margin-right: 6px;
            min-width: 100px;
        }

        .info-item .value {
            color: #374151 !important;
        }

        /* Cards de estatísticas modernos */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border-top: 3px solid;
            position: relative;
        }

        .stat-card.primary {
            border-top-color: #3b82f6;
            background: #dbeafe;
        }

        .stat-card.success {
            border-top-color: #10b981;
            background: #d1fae5;
        }

        .stat-card.warning {
            border-top-color: #f59e0b;
            background: #fef3c7;
        }

        .stat-card.danger {
            border-top-color: #ef4444;
            background: #fee2e2;
        }

        .stat-card.info {
            border-top-color: #06b6d4;
            background: #cffafe;
        }

        .stat-card h3 {
            margin: 0 0 6px 0;
            font-size: 10px;
            color: #1f2937 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card .value {
            font-size: 22px;
            font-weight: bold;
            color: #111827 !important;
            margin: 0;
            line-height: 1.2;
        }

        .stat-card .subtitle {
            font-size: 9px;
            color: #4b5563 !important;
            margin-top: 3px;
        }

        /* Seções compactas */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section h2 {
            background: #667eea;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            padding: 8px 12px;
            margin: 0 0 12px 0;
            font-size: 14px;
            border-radius: 6px;
            font-weight: bold;
        }

        /* Tabela moderna */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0;
            font-size: 10px;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table th {
            background: #667eea;
            color: #ffffff !important;
            font-weight: 600;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
        }

        .table td {
            padding: 7px 6px;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tr:nth-child(even) {
            background: #f9fafb;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Badges modernos */
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pago {
            background: #10b981 !important;
            color: #ffffff !important;
        }

        .status-pendente {
            background: #f59e0b !important;
            color: #ffffff !important;
        }

        .status-cancelado {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        .currency {
            font-weight: 600;
            color: #059669 !important;
        }

        /* Valor negativo/restante */
        .amount-pending {
            font-weight: 600;
            color: #dc2626 !important;
        }

        /* Melhor contraste para textos */
        .table td {
            color: #374151 !important;
            font-weight: 500;
        }

        /* Resumo de vendas */
        .sales-summary {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(245,158,11,0.15);
        }

        .sales-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 8px;
        }

        .sales-summary-item {
            background: #ffffff;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
        }

        .sales-summary-item .label {
            font-size: 9px;
            color: #1f2937 !important;
            font-weight: 600;
            text-transform: uppercase;
        }

        .sales-summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #111827 !important;
            margin-top: 2px;
        }

        /* Footer discreto */
        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #4b5563 !important;
            font-size: 9px;
        }

        /* Filtros info */
        .filters-info {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .filters-info h3 {
            color: #0369a1 !important;
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: bold;
        }

        .chart-placeholder {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            color: #4b5563 !important;
            margin: 12px 0;
            font-size: 11px;
        }

        /* Resumo Executivo Destacado */
        .executive-summary {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 8px rgba(14,165,233,0.15);
        }

        .executive-summary h2 {
            color: #0369a1 !important;
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 2px solid #0ea5e9;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 12px;
        }

        .summary-item {
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid;
        }

        .summary-item.blue { border-left-color: #3b82f6; }
        .summary-item.green { border-left-color: #10b981; }
        .summary-item.red { border-left-color: #ef4444; }
        .summary-item.purple { border-left-color: #8b5cf6; }
        .summary-item.orange { border-left-color: #f59e0b; }
        .summary-item.teal { border-left-color: #14b8a6; }

        .summary-item .icon {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .summary-item .label {
            font-size: 9px;
            color: #1f2937 !important;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #111827 !important;
        }

        .summary-item .detail {
            font-size: 8px;
            color: #4b5563 !important;
            margin-top: 2px;
        }

        @media print {
            body { margin: 0; background: #ffffff; }
            .page-break { page-break-before: always; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>RELATORIO DO CLIENTE</h1>
        <div class="subtitle">{{ $client->name }}</div>
        <div class="subtitle">Gerado em {{ $generatedAt->format('d/m/Y H:i:s') }}</div>
        @if($type === 'complete')
            <div class="subtitle"><strong>Relatorio Completo</strong> - Todas as informacoes (Vendas, Financeiro e Analytics)</div>
        @elseif($type === 'vendas')
            <div class="subtitle"><strong>Relatorio de Vendas</strong> - Lista detalhada de vendas (sem resumo financeiro)</div>
        @elseif($type === 'financeiro')
            <div class="subtitle"><strong>Relatorio Financeiro</strong> - Resumo financeiro e parcelas</div>
        @endif
    </div>

    <!-- Filtros Aplicados -->
    @php
        // Considerar filtro de ano padrão (ano corrente) como "sem filtro" para o propósito do PDF
        $defaultYear = \Carbon\Carbon::now()->year;
        $showFilters = false;
        if ((isset($filterYear) && $filterYear !== null && intval($filterYear) !== $defaultYear)
            || (isset($filterMonth) && $filterMonth !== 'all')
            || (isset($filterStatus) && $filterStatus !== 'all')
            || (isset($filterPaymentType) && $filterPaymentType !== 'all')) {
            $showFilters = true;
        }
    @endphp

    @if($showFilters)
    <div class="filters-info">
        <h3>FILTROS APLICADOS</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; font-size: 10px;">
            @if($filterYear && intval($filterYear) !== $defaultYear)
                <div><strong>Ano:</strong> {{ $filterYear }}</div>
            @endif
            @if($filterMonth !== 'all')
                <div><strong>Mes:</strong> {{ \Carbon\Carbon::create()->month($filterMonth)->translatedFormat('F') }}</div>
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
        <h2>INFORMACOES DO CLIENTE</h2>
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
                <span class="label">Ultima Compra:</span>
                <span class="value">{{ \Carbon\Carbon::parse($ultimaCompra)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Resumo Executivo Destacado -->
    @if($type === 'complete' || $type === 'financeiro')
    <div class="executive-summary">
        <h2>RESUMO EXECUTIVO</h2>
        <div class="summary-grid">
            <div class="summary-item blue">
                <div class="icon">[V]</div>
                <div class="label">Total Vendas</div>
                <div class="value">{{ $totalVendas }}</div>
                <div class="detail">operacoes</div>
            </div>
            <div class="summary-item green">
                <div class="icon">[$]</div>
                <div class="label">Faturado</div>
                <div class="value">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</div>
                <div class="detail">receita bruta</div>
            </div>
            <div class="summary-item green">
                <div class="icon">[OK]</div>
                <div class="label">Pago</div>
                <div class="value">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                <div class="detail">recebido</div>
            </div>
            <div class="summary-item red">
                <div class="icon">[!]</div>
                <div class="label">A Receber</div>
                <div class="value">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
                <div class="detail">
                    @if($totalFaturado > 0)
                        {{ number_format(($totalPendente / $totalFaturado) * 100, 1) }}% pendente
                    @else
                        0% pendente
                    @endif
                </div>
            </div>
            <div class="summary-item purple">
                <div class="icon">[G]</div>
                <div class="label">Ticket Medio</div>
                <div class="value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                <div class="detail">por venda</div>
            </div>
            <div class="summary-item orange">
                <div class="icon">[%]</div>
                <div class="label">Taxa Pagamento</div>
                <div class="value">
                    @if($totalFaturado > 0)
                        {{ number_format(($totalPago / $totalFaturado) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <div class="detail">do total</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumo Financeiro (apenas para 'complete' e 'financeiro') -->
    @if($type === 'complete' || $type === 'financeiro')
    <div class="section">
        <h2>RESUMO FINANCEIRO</h2>
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total Vendas</h3>
                <div class="value">{{ $totalVendas }}</div>
                <div class="subtitle">Volume total</div>
            </div>
            <div class="stat-card success">
                <h3>Faturado</h3>
                <div class="value currency">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</div>
                <div class="subtitle">Receita bruta</div>
            </div>
            <div class="stat-card success">
                <h3>Total Pago</h3>
                <div class="value currency">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                <div class="subtitle">Receita líquida</div>
            </div>
            <div class="stat-card warning">
                <h3>Restante</h3>
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
                <h3>Ticket Medio</h3>
                <div class="value currency">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                <div class="subtitle">Valor medio/venda</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Vendas -->
    @if($type === 'complete' || $type === 'vendas')
    <div class="section">
        <h2>LISTA DE VENDAS ({{ count($vendas) }} vendas)</h2>

        @if(count($vendas) > 0)
        <!-- Resumo Rápido de Vendas -->
        <div class="sales-summary">
            <div style="font-weight: bold; color: #92400e; margin-bottom: 6px; font-size: 11px;">RESUMO RAPIDO</div>
            <div class="sales-summary-grid">
                <div class="sales-summary-item">
                    <div class="label">Faturado</div>
                    <div class="value" style="color: #10b981;">R$ {{ number_format(collect($vendas)->sum('total_price'), 2, ',', '.') }}</div>
                </div>
                <div class="sales-summary-item">
                    <div class="label">Pago</div>
                    <div class="value" style="color: #059669;">R$ {{ number_format(collect($vendas)->sum('amount_paid'), 2, ',', '.') }}</div>
                </div>
                <div class="sales-summary-item">
                    <div class="label">Restante</div>
                    <div class="value" style="color: #dc2626;">R$ {{ number_format(collect($vendas)->sum('total_price') - collect($vendas)->sum('amount_paid'), 2, ',', '.') }}</div>
                </div>
                <div class="sales-summary-item">
                    <div class="label">Ticket Medio</div>
                    <div class="value" style="color: #3b82f6;">R$ {{ number_format(collect($vendas)->avg('total_price'), 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

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
                @php
                    $restante = $venda['total_price'] - ($venda['amount_paid'] ?? 0);
                @endphp
                <tr>
                    <td style="font-weight: 600; color: #667eea;">#{{ $venda['id'] }}</td>
                    <td style="color: #4b5563;">{{ \Carbon\Carbon::parse($venda['created_at'])->format('d/m/Y') }}</td>
                    <td class="currency">R$ {{ number_format($venda['total_price'], 2, ',', '.') }}</td>
                    <td style="color: #10b981; font-weight: 600;">R$ {{ number_format($venda['amount_paid'] ?? 0, 2, ',', '.') }}</td>
                    <td class="{{ $restante > 0 ? 'amount-pending' : 'currency' }}">
                        R$ {{ number_format($restante, 2, ',', '.') }}
                    </td>
                    <td>
                        <span class="status-badge status-{{ $venda['status'] }}">
                            @if($venda['status'] === 'pago') [OK] @elseif($venda['status'] === 'pendente') [!] @else [X] @endif
                            {{ ucfirst($venda['status']) }}
                        </span>
                    </td>
                    <td style="color: #6b7280;">{{ ucfirst(str_replace('_', ' ', $venda['tipo_pagamento'])) }}</td>
                    <td style="color: #8b5cf6; font-weight: 600;">{{ $venda['parcelas'] ?? 1 }}x</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="chart-placeholder">
            <p>Nenhuma venda encontrada com os filtros aplicados</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Parcelas (para relatorios completo e financeiro) -->
    @if(($type === 'complete' || $type === 'financeiro') && isset($parcelas) && count($parcelas) > 0)
    <div class="section page-break">
        <h2>PARCELAS ({{ count($parcelas) }} parcelas)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Venda</th>
                    <th>Parcela</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Situacao</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parcelas as $parcela)
                <tr>
                    <td style="font-weight: 600; color: #667eea;">#{{ $parcela['sale']['id'] }}</td>
                    <td style="color: #4b5563; font-weight: 600;">{{ $parcela['numero_parcela'] }}</td>
                    <td style="color: #6b7280;">{{ \Carbon\Carbon::parse($parcela['data_vencimento'])->format('d/m/Y') }}</td>
                    <td class="currency">R$ {{ number_format($parcela['valor'], 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ $parcela['status'] }}">
                            @if($parcela['status'] === 'pago') [OK] @else [!] @endif
                            {{ ucfirst($parcela['status']) }}
                        </span>
                    </td>
                    <td style="font-weight: 600;">
                        @if(\Carbon\Carbon::parse($parcela['data_vencimento'])->isPast() && $parcela['status'] === 'pendente')
                            <span style="color: #dc2626;">[!] Vencida</span>
                        @elseif($parcela['status'] === 'pendente')
                            <span style="color: #059669;">Em dia</span>
                        @else
                            <span style="color: #10b981;">[OK] Pago</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Produtos Mais Comprados (apenas relatorio completo) -->
    @if($type === 'complete' && isset($produtosMaisComprados) && count($produtosMaisComprados) > 0)
    <div class="section">
        <h2>PRODUTOS MAIS COMPRADOS</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade Total</th>
                    <th>N Compras</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtosMaisComprados as $produto)
                <tr>
                    <td style="color: #374151; font-weight: 600;">{{ $produto['produto'] }}</td>
                    <td style="color: #10b981; font-weight: bold;">{{ $produto['quantidade'] }} unidades</td>
                    <td style="color: #667eea; font-weight: 600;">{{ $produto['vendas'] }} compras</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Analytics (apenas relatorio completo) -->
    @if($type === 'complete' && isset($vendasPorStatus) && count($vendasPorStatus) > 0)
    <div class="section">
        <h2>ANALYTICS</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 12px;">
            <div style="background: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="color: #667eea; font-size: 13px; margin-bottom: 10px; font-weight: bold;">Vendas por Status</h3>
                @foreach($vendasPorStatus as $status => $total)
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; border-bottom: 1px solid #f3f4f6; align-items: center;">
                    <span style="color: #4b5563; font-size: 11px;">
                        @if($status === 'pago') [OK] @elseif($status === 'pendente') [!] @else [X] @endif
                        {{ ucfirst($status) }}
                    </span>
                    <strong style="color: #111827; font-size: 12px;">{{ $total }} vendas</strong>
                </div>
                @endforeach
            </div>

            @if(isset($categoriasMaisCompradas) && count($categoriasMaisCompradas) > 0)
            <div style="background: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="color: #667eea; font-size: 13px; margin-bottom: 10px; font-weight: bold;">Categorias Preferidas</h3>
                @foreach($categoriasMaisCompradas as $categoria)
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; border-bottom: 1px solid #f3f4f6; align-items: center;">
                    <span style="color: #4b5563; font-size: 11px;">{{ $categoria['categoria'] }}</span>
                    <strong style="color: #10b981; font-size: 12px;">{{ $categoria['quantidade'] }} un.</strong>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Footer com Resumo Final -->
    <div class="footer">
        @if($type === 'complete' || $type === 'financeiro')
        <div style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); padding: 12px; border-radius: 8px; margin-bottom: 12px; text-align: left;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; font-size: 10px;">
                <div style="text-align: center;">
                    <div style="font-weight: 600; color: #374151;">Total Faturado</div>
                    <div style="font-size: 12px; font-weight: bold; color: #10b981;">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-weight: 600; color: #374151;">Total Pago</div>
                    <div style="font-size: 12px; font-weight: bold; color: #10b981;">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-weight: 600; color: #374151;">A Receber</div>
                    <div style="font-size: 12px; font-weight: bold; color: #ef4444;">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-weight: 600; color: #374151;">Taxa Pgto</div>
                    <div style="font-size: 12px; font-weight: bold; color: #3b82f6;">
                        @if($totalFaturado > 0)
                            {{ number_format(($totalPago / $totalFaturado) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <p style="font-weight: 600; color: #374151; font-size: 11px;"><strong>FlowManager</strong> - Sistema de Gestao de Vendas</p>
        <p style="font-size: 9px;">Relatorio gerado automaticamente em {{ $generatedAt->format('d/m/Y \a\s H:i:s') }}</p>
        <p style="margin-top: 8px; font-size: 8px; color: #9ca3af;">
            Este documento contem informacoes confidenciais do cliente {{ $client->name }}
        </p>
    </div>
</body>
</html>
