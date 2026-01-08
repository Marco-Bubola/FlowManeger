<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Consórcios - {{ $client->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
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

        .client-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .client-info h3 {
            font-size: 16px;
            color: #1e40af;
            margin-bottom: 10px;
        }

        .client-info p {
            margin: 5px 0;
            font-size: 13px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 25px;
            padding: 15px;
            background: #fef3c7;
            border-radius: 8px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .label {
            font-size: 10px;
            color: #92400e;
            margin-bottom: 5px;
        }

        .stat-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #78350f;
        }

        .consortium-block {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .consortium-header {
            background: #10b981;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .consortium-header h2 {
            font-size: 16px;
            margin: 0;
        }

        .consortium-header .number {
            background: white;
            color: #10b981;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .info-item {
            background: #f9fafb;
            padding: 10px;
            border-radius: 6px;
        }

        .info-item .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        table thead {
            background: #e5e7eb;
            color: #374151;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 600;
        }

        table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .contemplation-box {
            background: #fef3c7;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            border-left: 4px solid #f59e0b;
        }

        .contemplation-box h4 {
            color: #92400e;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .contemplation-box p {
            margin: 3px 0;
            font-size: 10px;
            color: #78350f;
        }

        .products-list {
            margin-top: 8px;
            padding-left: 15px;
        }

        .products-list li {
            font-size: 10px;
            color: #78350f;
            margin: 2px 0;
        }

        .progress-bar {
            width: 100%;
            height: 18px;
            background: #e5e7eb;
            border-radius: 9px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #14b8a6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 9px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .page-break {
            page-break-after: always;
        }

        .summary-box {
            background: #eff6ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .summary-box h3 {
            color: #1e40af;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .contract-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }

        .contract-section h3 {
            color: #0f766e;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .contract-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .contract-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
        }

        .contract-item h4 {
            margin-bottom: 6px;
            font-size: 12px;
            color: #0f172a;
        }

        .contract-item p {
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Relatório de Consórcios - {{ $client->name }}</h1>
        <p>Todos os consórcios do cliente - Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Informações do Cliente -->
    <div class="client-info">
        <h3>Dados do Cliente</h3>
        <p><strong>Nome:</strong> {{ $client->name }}</p>
        @if($client->cpf)
            <p><strong>CPF:</strong> {{ $client->cpf }}</p>
        @endif
        @if($client->cnpj)
            <p><strong>CNPJ:</strong> {{ $client->cnpj }}</p>
        @endif
        @if($client->phone)
            <p><strong>Telefone:</strong> {{ $client->phone }}</p>
        @endif
        @if($client->email)
            <p><strong>E-mail:</strong> {{ $client->email }}</p>
        @endif
    </div>

    <!-- Estatísticas Gerais -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="label">Total Consórcios</div>
            <div class="value">{{ $statistics['total_consortiums'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Ativos</div>
            <div class="value">{{ $statistics['active_consortiums'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Contemplados</div>
            <div class="value">{{ $statistics['contemplated'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Total Pago</div>
            <div class="value">R$ {{ number_format($statistics['total_paid'], 2, ',', '.') }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Total Parcelas</div>
            <div class="value">{{ $statistics['total_payments'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Pagas</div>
            <div class="value">{{ $statistics['paid_payments'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Pendentes</div>
            <div class="value">{{ $statistics['pending_payments'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Vencidas</div>
            <div class="value">{{ $statistics['overdue_payments'] }}</div>
        </div>
    </div>

    <!-- Lista de Consórcios -->
    @foreach($participations as $participation)
        @php
            $totalParcelas = $participation->payments->count();
            $parcelasPagas = $participation->payments->where('status', 'paid')->count();
            $parcelasVencidas = $participation->payments->where('status', 'overdue')->count();
            $progresso = $totalParcelas > 0 ? round(($parcelasPagas / $totalParcelas) * 100) : 0;
        @endphp

        <div class="consortium-block">
            <!-- Header do Consórcio -->
            <div class="consortium-header">
                <h2>{{ $participation->consortium->name }}</h2>
                <div class="number">Nº {{ $participation->participation_number }}</div>
            </div>

            <!-- Informações do Consórcio -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">
                        @if($participation->status === 'active')
                            <span class="badge badge-success">Ativo</span>
                        @elseif($participation->status === 'inactive')
                            <span class="badge badge-warning">Inativo</span>
                        @else
                            <span class="badge badge-danger">Cancelado</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Data Entrada</div>
                    <div class="value">{{ $participation->entry_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Valor Mensal</div>
                    <div class="value">R$ {{ number_format($participation->consortium->monthly_value, 2, ',', '.') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Duração</div>
                    <div class="value">{{ $participation->consortium->duration_months }} meses</div>
                </div>
                <div class="info-item">
                    <div class="label">Total Pago</div>
                    <div class="value">R$ {{ number_format($participation->total_paid, 2, ',', '.') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Contemplado</div>
                    <div class="value">
                        @if($participation->is_contemplated)
                            <span class="badge badge-info">Sim</span>
                        @else
                            <span class="badge badge-warning">Não</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progresso de Pagamento -->
            <div style="margin: 15px 0;">
                <strong style="font-size: 11px; color: #374151;">Progresso de Pagamento:</strong>
                <p style="font-size: 10px; color: #6b7280; margin: 3px 0;">
                    {{ $parcelasPagas }} de {{ $totalParcelas }} parcelas pagas
                    @if($parcelasVencidas > 0)
                        ({{ $parcelasVencidas }} vencidas)
                    @endif
                </p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $progresso }}%;">
                        {{ $progresso }}%
                    </div>
                </div>
            </div>

            <!-- Contemplação (se houver) -->
            @if($participation->is_contemplated && $participation->contemplation)
                <div class="contemplation-box">
                    <h4>🏆 CONTEMPLAÇÃO</h4>
                    <p><strong>Data:</strong> {{ $participation->contemplation->contemplation_date?->format('d/m/Y') }}</p>
                    <p><strong>Sorteio:</strong> #{{ $participation->contemplation->draw?->draw_number }}</p>

                    @if($participation->contemplation->redemption_type === 'products' && $participation->contemplation->products->count() > 0)
                        <p><strong>Produtos Resgatados:</strong></p>
                        <ul class="products-list">
                            @foreach($participation->contemplation->products as $product)
                                <li>{{ $product->name }} - R$ {{ number_format($product->price, 2, ',', '.') }}</li>
                            @endforeach
                        </ul>
                        <p style="margin-top: 5px;"><strong>Valor Total:</strong> R$ {{ number_format($participation->contemplation->products->sum('price'), 2, ',', '.') }}</p>
                    @elseif($participation->contemplation->redemption_type === 'cash')
                        <p><strong>Valor em Dinheiro:</strong> R$ {{ number_format($participation->contemplation->cash_value, 2, ',', '.') }}</p>
                    @endif
                </div>
            @endif

            <!-- Tabela de Parcelas Resumida -->
            @if($participation->payments->count() > 0)
                <h4 style="font-size: 12px; color: #374151; margin-top: 15px; margin-bottom: 8px;">Últimas Parcelas:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Parcela</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participation->payments->take(10) as $payment)
                            <tr>
                                <td>{{ $payment->installment_number }}</td>
                                <td>{{ $payment->due_date?->format('d/m/Y') }}</td>
                                <td>R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge badge-success">Paga</span>
                                    @elseif($payment->status === 'overdue')
                                        <span class="badge badge-danger">Vencida</span>
                                    @else
                                        <span class="badge badge-warning">Pendente</span>
                                    @endif
                                </td>
                                <td>{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($participation->payments->count() > 10)
                    <p style="font-size: 9px; color: #6b7280; margin-top: 5px; text-align: center;">
                        Mostrando 10 de {{ $participation->payments->count() }} parcelas
                    </p>
                @endif
            @endif
        </div>

        @if(!$loop->last)
            <div style="margin: 20px 0; border-bottom: 2px dashed #e5e7eb;"></div>
        @endif
    @endforeach

    @php
        $consortiumInfo = $participations->first()?->consortium;
    @endphp

    @if($consortiumInfo)
        <div class="page-break"></div>
        <div class="contract-section">
            <h3>Contrato / Regras do Consórcio</h3>
            <p style="font-size: 11px; color: #0f172a; margin-bottom: 12px;">Consórcio: {{ $consortiumInfo->name }} | Valor mensal: R$ {{ number_format($consortiumInfo->monthly_value, 2, ',', '.') }} | Duração: {{ $consortiumInfo->duration_months }} meses | Início: {{ optional($consortiumInfo->start_date)->format('d/m/Y') ?? '-' }}</p>

            <div class="contract-grid">
                <div class="contract-item">
                    <h4>1. Participação</h4>
                    <p>Cada participante recebe um número único. Status possíveis: ativo, desistente, contemplado, cancelado. Participante contemplado não pode ser removido.</p>
                </div>
                <div class="contract-item">
                    <h4>2. Pagamentos</h4>
                    <p>Parcelas mensais conforme calendário. Situações: pendente, paga, vencida. Juros/multas podem ser aplicados a parcelas vencidas.</p>
                </div>
                <div class="contract-item">
                    <h4>3. Sorteios e Contemplação</h4>
                    <p>Frequência: {{ $consortiumInfo->draw_frequency_label }}. Elegível quem está ativo e não contemplado. Registro inclui tipo, data e participação vencedora.</p>
                </div>
                <div class="contract-item">
                    <h4>4. Resgate</h4>
                    <p>Modos: produtos ou dinheiro. Produtos devem ter itens/valores registrados; dinheiro deve ter valor acordado e prazo de pagamento.</p>
                </div>
                <div class="contract-item">
                    <h4>5. Desistência e Reativação</h4>
                    <p>Participante pode ser marcado como desistente; reativação depende de autorização e regras financeiras acordadas.</p>
                </div>
                <div class="contract-item">
                    <h4>6. Cancelamento do Consórcio</h4>
                    <p>O consórcio pode ser desativado/cancelado por motivos operacionais ou legais. Impactos devem ser comunicados e acordados.</p>
                </div>
                <div class="contract-item">
                    <h4>7. Comunicação</h4>
                    <p>Canal oficial: ________________________. Avisos de sorteios e vencimentos enviados via e-mail / WhatsApp / aplicativo.</p>
                </div>
                <div class="contract-item">
                    <h4>8. Foro e Vigência</h4>
                    <p>Vigência: adesão até término/cancelamento. Foro para dirimir dúvidas: ________________________.</p>
                </div>
            </div>
            <p class="muted" style="font-size: 10px; color: #6b7280; margin-top: 12px;">Preencha os campos em branco antes de enviar ao cliente.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema FlowManager</p>
        <p>Data de geração: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
