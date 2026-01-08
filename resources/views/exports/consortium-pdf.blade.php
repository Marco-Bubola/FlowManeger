<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Consórcio</title>
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }

        .info-card h3 {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-card p {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        table thead {
            background: #10b981;
            color: white;
        }

        table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        table tr:hover {
            background: #f3f4f6;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
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

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #10b981;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #10b981;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #14b8a6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $consortium->name }}</h1>
        <p>Relatório Completo do Consórcio - Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Informações Principais -->
    <div class="info-grid">
        <div class="info-card">
            <h3>Status</h3>
            <p>{{ $consortium->status_label }}</p>
        </div>
        <div class="info-card">
            <h3>Valor Mensal</h3>
            <p>R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}</p>
        </div>
        <div class="info-card">
            <h3>Duração</h3>
            <p>{{ $consortium->duration_months }} meses</p>
        </div>
        <div class="info-card">
            <h3>Data de Início</h3>
            <p>{{ $consortium->start_date?->format('d/m/Y') ?? '-' }}</p>
        </div>
        <div class="info-card">
            <h3>Frequência</h3>
            <p>{{ $consortium->draw_frequency_label }}</p>
        </div>
        <div class="info-card">
            <h3>Valor Total</h3>
            <p>R$ {{ number_format($consortium->total_value, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="label">Total Participantes</div>
            <div class="value">{{ $statistics['total_participants'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Ativos</div>
            <div class="value">{{ $statistics['active_participants'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Contemplados</div>
            <div class="value">{{ $statistics['contemplated'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Total Sorteios</div>
            <div class="value">{{ $statistics['total_draws'] }}</div>
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
            <div class="label">Vencidas</div>
            <div class="value">{{ $statistics['overdue_payments'] }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Total Arrecadado</div>
            <div class="value">R$ {{ number_format($statistics['total_collected'], 2, ',', '.') }}</div>
        </div>
    </div>

    @if($consortium->description)
        <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
            <strong style="color: #1e40af;">Descrição:</strong>
            <p style="margin-top: 5px; color: #1e3a8a;">{{ $consortium->description }}</p>
        </div>
    @endif

    <!-- Lista de Participantes -->
    <div class="section-title">Participantes</div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nº</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 15%;">CPF/CNPJ</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Contemplado</th>
                <th style="width: 10%;">Entrada</th>
                <th style="width: 10%;">Parcelas</th>
                <th style="width: 15%;">Total Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $participant)
                @php
                    $totalParcelas = $participant->payments->count();
                    $parcelasPagas = $participant->payments->where('status', 'paid')->count();
                    $progresso = $totalParcelas > 0 ? round(($parcelasPagas / $totalParcelas) * 100) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $participant->participation_number }}</strong></td>
                    <td>{{ $participant->client->name ?? '-' }}</td>
                    <td>{{ $participant->client->cpf ?? $participant->client->cnpj ?? '-' }}</td>
                    <td>
                        @if($participant->status === 'active')
                            <span class="badge badge-success">Ativo</span>
                        @elseif($participant->status === 'inactive')
                            <span class="badge badge-warning">Inativo</span>
                        @else
                            <span class="badge badge-danger">Cancelado</span>
                        @endif
                    </td>
                    <td>
                        @if($participant->is_contemplated)
                            <span class="badge badge-info">Sim</span>
                        @else
                            <span class="badge badge-warning">Não</span>
                        @endif
                    </td>
                    <td>{{ $participant->entry_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        {{ $parcelasPagas }}/{{ $totalParcelas }}
                        <div class="progress-bar" style="margin-top: 5px;">
                            <div class="progress-fill" style="width: {{ $progresso }}%;">
                                {{ $progresso }}%
                            </div>
                        </div>
                    </td>
                    <td><strong>R$ {{ number_format($participant->total_paid, 2, ',', '.') }}</strong></td>
                </tr>

                @if($participant->is_contemplated && $participant->contemplation)
                    <tr>
                        <td colspan="8" style="background: #fef3c7; padding: 10px;">
                            <strong style="color: #78350f;">Contemplação:</strong>
                            Data: {{ $participant->contemplation->contemplation_date?->format('d/m/Y') }} |
                            Sorteio: #{{ $participant->contemplation->draw?->draw_number }} |
                            @if($participant->contemplation->products->count() > 0)
                                Produtos: {{ $participant->contemplation->products->pluck('name')->implode(', ') }}
                            @else
                                Resgate: R$ {{ number_format($participant->contemplation->cash_value ?? 0, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema FlowManager</p>
        <p>Data de geração: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
