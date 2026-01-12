<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consórcio - Cota {{ $participant->quota_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 100%;
            margin: 0;
            background: white;
        }

        /* Header */
        .header {
            background: #0ea5e9;
            padding: 18px 20px;
            color: white;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            color: white;
            font-weight: normal;
            margin-bottom: 8px;
        }

        .header .doc-info {
            font-size: 10px;
            color: white;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 8px;
            margin-top: 8px;
        }

        /* Conteúdo */
        .content {
            padding: 20px;
        }

        /* Section Header */
        .section-header {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #0ea5e9;
            text-transform: uppercase;
            margin: 12px 0 8px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #0ea5e9;
        }

        /* Grid de Informações */
        .info-grid {
            width: 100%;
            margin-bottom: 12px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
        }

        .info-item {
            padding: 10px 12px;
            border-bottom: 1px solid #cbd5e1;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            display: block;
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-value {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
        }

        /* Stats */
        .stats-grid {
            width: 100%;
            margin: 12px 0;
        }

        .stat-box {
            width: 100%;
            padding: 12px 8px;
            text-align: center;
            border: 2px solid;
            margin-bottom: 8px;
        }

        .stat-box.total {
            background: #3b82f6;
            border-color: #2563eb;
        }

        .stat-box.paid {
            background: #10b981;
            border-color: #059669;
        }

        .stat-box.balance {
            background: #f59e0b;
            border-color: #d97706;
        }

        .stat-box h3 {
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .stat-box p {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        /* Barra de Progresso Compacta */
        .progress-wrapper {
            margin: 15px 0;
        }

        .progress-container {
            width: 100%;
            height: 28px;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
        }

        .progress-fill {
            height: 100%;
            background: #0ea5e9;
            text-align: center;
            line-height: 28px;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .progress-empty {
            height: 100%;
            text-align: center;
            line-height: 28px;
            color: #64748b;
            font-weight: bold;
            font-size: 12px;
        }

        /* Tabela Compacta */
        .table-wrapper {
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }

        thead {
            background: #0ea5e9;
        }

        th {
            padding: 11px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
            border-bottom: 2px solid #0284c7;
        }

        td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            color: #1e293b;
            font-weight: normal;
        }

        tbody tr {
            background: white;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-active {
            background: #10b981;
            color: white;
        }

        .badge-inactive {
            background: #94a3b8;
            color: white;
        }

        /* Resumo Box */
        .summary-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            border: 1px solid #d1fae5;
            padding: 12px;
            margin-top: 10px;
        }

        .summary-box h4 {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 7px;
        }

        .summary-box p {
            font-size: 10px;
            color: #047857;
            margin-bottom: 4px;
        }

        /* Alert */
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border: 1px solid #fde68a;
            padding: 12px;
            text-align: center;
            margin: 12px 0;
        }

        .alert-box p {
            font-size: 11px;
            font-weight: bold;
            color: #92400e;
        }

        /* Regras do Consórcio */
        .rules-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border: 1px solid #dbeafe;
            padding: 12px;
            margin: 15px 0;
        }

        .rules-box h4 {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 9px;
        }

        .rules-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .rules-list li {
            font-size: 10px;
            color: #1e3a8a;
            padding: 5px 0 5px 18px;
            position: relative;
            line-height: 1.6;
        }

        .rules-list li::before {
            content: '\2713';
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding: 15px;
            background: #f8fafc;
            border-top: 2px solid #0ea5e9;
            text-align: center;
        }

        .footer p {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0;
        }

        .footer-logo {
            font-size: 14px;
            font-weight: bold;
            color: #0ea5e9;
            margin-bottom: 5px;
        }

        .footer-text {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0;
        }

        /* Layout de 2 Colunas */
        .two-columns {
            width: 100%;
            margin-bottom: 12px;
        }

        .column {
            width: 100%;
            margin-bottom: 10px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Compacto -->
        <div class="header">
            <div class="header-content">
                <h1>Consórcio - Cota {{ $participant->quota_number }}</h1>
                <p class="subtitle">Relatório Completo de Participação</p>
                <div class="doc-info">
                    Gerado em {{ now()->format('d/m/Y \à\s H:i') }}
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Layout 2 Colunas: Participante + Consórcio -->
            <div class="two-columns">
                <div class="column">
                    <div class="section-header">Participante</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nome Completo</span>
                            <span class="info-value">{{ $participant->client->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">E-mail</span>
                            <span class="info-value">{{ $participant->client->email ?? 'Não informado' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Telefone</span>
                            <span class="info-value">{{ $participant->client->phone ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="section-header">Consórcio</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Produto/Bem</span>
                            <span class="info-value">{{ $participant->consortium->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Data de Adesão</span>
                            <span class="info-value">{{ $participant->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <span class="badge {{ $participant->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $participant->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clear"></div>

            @php
                $totalValue = $participant->consortium->total_value ?? 0;
                $amountPaid = $participant->payments->sum('amount');
                $balance = $totalValue - $amountPaid;
                $percentPaid = $totalValue > 0 ? ($amountPaid / $totalValue) * 100 : 0;
            @endphp

            <!-- Stats Cards Compactos -->
            <div class="section-header">Resumo Financeiro</div>
            <div class="stats-grid">
                <div class="stat-box total">
                    <h3>Valor Total</h3>
                    <p>R$ {{ number_format($totalValue, 2, ',', '.') }}</p>
                </div>
                <div class="stat-box paid">
                    <h3>Valor Pago</h3>
                    <p>R$ {{ number_format($amountPaid, 2, ',', '.') }}</p>
                </div>
                <div class="stat-box balance">
                    <h3>Saldo Devedor</h3>
                    <p>R$ {{ number_format($balance, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="clear"></div>

            <!-- Barra de Progresso -->
            <div class="progress-wrapper">
                <div class="section-header">Progresso de Pagamento</div>
                <div class="progress-container">
                    @if($percentPaid > 0)
                        <div class="progress-fill" style="width: {{ min($percentPaid, 100) }}%;">
                            {{ number_format($percentPaid, 1) }}%
                        </div>
                    @else
                        <div class="progress-empty">0.0%</div>
                    @endif
                </div>
            </div>

            <!-- Regras do Consórcio -->
            <div class="rules-box">
                <h4>Regras e Condições do Consórcio</h4>
                <ul class="rules-list">
                    <li>O pagamento das parcelas deve ser realizado mensalmente nas datas acordadas</li>
                    <li>Em caso de atraso, serão aplicados juros e multa conforme contrato</li>
                    <li>A contemplação ocorre por sorteio ou lance, conforme regulamento</li>
                    <li>O participante contemplado terá prazo definido para retirada do bem</li>
                    <li>É permitida a transferência de cota mediante aprovação da administradora</li>
                    <li>Taxa de administração está inclusa no valor das parcelas</li>
                    <li>Seguro obrigatório conforme tipo de bem consorciado</li>
                    <li>Desistência: valores pagos serão devolvidos após encerramento do grupo</li>
                </ul>
            </div>

            <!-- Histórico de Pagamentos -->
            @if($participant->payments && $participant->payments->count() > 0)
            <div class="table-wrapper">
                <div class="section-header">Histórico de Pagamentos</div>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Método</th>
                            <th>Valor</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participant->payments as $payment)
                        <tr>
                            <td><strong>{{ $payment->created_at->format('d/m/Y H:i') }}</strong></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                            <td><strong style="color: #10b981;">R$ {{ number_format($payment->amount, 2, ',', '.') }}</strong></td>
                            <td>{{ $payment->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Resumo Financeiro -->
                <div class="summary-box">
                    <h4>Estatísticas de Pagamento</h4>
                    <p><strong>Total de Pagamentos:</strong> {{ $participant->payments->count() }}</p>
                    <p><strong>Média por Pagamento:</strong> R$ {{ number_format($participant->payments->count() > 0 ? $amountPaid / $participant->payments->count() : 0, 2, ',', '.') }}</p>
                    <p><strong>Percentual Quitado:</strong> {{ number_format($percentPaid, 2) }}%</p>
                    <p><strong>Último Pagamento:</strong> {{ $participant->payments->sortByDesc('created_at')->first()->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            @else
            <div class="alert-box">
                <p>Nenhum pagamento registrado até o momento</p>
            </div>
            @endif
        </div>

        <!-- Footer Compacto -->
        <div class="footer">
            <div class="footer-logo">FlowManager</div>
            <p class="footer-text"><strong>Sistema de Gestão de Consórcios</strong></p>
            <p class="footer-text">Documento gerado automaticamente • Valores em Reais (R$) • © {{ now()->year }} FlowManager</p>
        </div>
    </div>
</body>
</html>
