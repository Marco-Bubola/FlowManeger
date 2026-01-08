<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        .header { background: #0f766e; color: #fff; padding: 12px 16px; border-radius: 8px; }
        .section { margin-top: 16px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .client { margin-top: 12px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .meta { color: #6b7280; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .contract { margin-top: 20px; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
        .contract h2 { color: #0f766e; font-size: 15px; margin-bottom: 10px; }
        .contract-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .contract-item { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
        .contract-item h4 { margin: 0 0 6px 0; color: #0f172a; font-size: 12px; }
        .contract-item p { margin: 0; color: #475569; font-size: 11px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Clientes do Consórcio</h1>
        <p class="meta">Consórcio: {{ $consortium->name }} | Duração: {{ $consortium->duration_months }} meses | Valor mensal: R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }}</p>
    </div>

    @php
        $grouped = $participants->groupBy('client_id');
    @endphp

    @foreach($grouped as $clientId => $items)
        @php
            $client = $items->first()->client;
            $payments = $items->flatMap->payments;
            $paid = $payments->where('status', 'paid')->count();
            $overdue = $payments->where('status', 'overdue')->count();
            $pending = $payments->where('status', 'pending')->count();
        @endphp
        <div class="client">
            <h3>{{ $client->name ?? 'Cliente #' . $clientId }}</h3>
            <p class="meta">Participações: {{ $items->count() }} | Pagas: {{ $paid }} | Pendentes: {{ $pending }} | Vencidas: {{ $overdue }}</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Referência</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $participation)
                        @foreach($participation->payments->sortBy('due_date') as $payment)
                            <tr>
                                <td>{{ $participation->participation_number }}</td>
                                <td>{{ $payment->reference_month_name }}/{{ $payment->reference_year }}</td>
                                <td>{{ optional($payment->due_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}</td>
                                <td>R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $class = $payment->status === 'paid' ? 'status-paid' : ($payment->status === 'overdue' ? 'status-overdue' : 'status-pending');
                                        $label = $payment->status_label;
                                    @endphp
                                    <span class="badge {{ $class }}">{{ $label }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="contract">
        <h2>Contrato / Regras do Consórcio</h2>
        <p class="meta">Consórcio: {{ $consortium->name }} | Valor mensal: R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }} | Duração: {{ $consortium->duration_months }} meses | Início: {{ optional($consortium->start_date)->format('d/m/Y') ?? '-' }}</p>

        <div class="contract-grid">
            <div class="contract-item">
                <h4>1. Participação</h4>
                <p>Números de participação são únicos. Status possíveis: ativo, desistente, contemplado, cancelado.</p>
            </div>
            <div class="contract-item">
                <h4>2. Pagamentos</h4>
                <p>Parcelas mensais conforme calendário. Situações: pendente, paga, vencida (podem ter juros/multa).</p>
            </div>
            <div class="contract-item">
                <h4>3. Sorteios e Contemplação</h4>
                <p>Frequência: {{ $consortium->draw_frequency_label }}. Elegibilidade: ativo e não contemplado.</p>
            </div>
            <div class="contract-item">
                <h4>4. Resgate</h4>
                <p>Resgate por produtos (itens/valores registrados) ou dinheiro (valor e prazo acordados).</p>
            </div>
            <div class="contract-item">
                <h4>5. Desistência e Reativação</h4>
                <p>Desistência marca o participante; reativação depende de autorização e regras financeiras.</p>
            </div>
            <div class="contract-item">
                <h4>6. Cancelamento do Consórcio</h4>
                <p>Consórcio pode ser desativado/cancelado; impactos devem ser comunicados e acordados.</p>
            </div>
            <div class="contract-item">
                <h4>7. Comunicação</h4>
                <p>Canal oficial: ________________________. Avisos via e-mail / WhatsApp / aplicativo.</p>
            </div>
            <div class="contract-item">
                <h4>8. Foro e Vigência</h4>
                <p>Vigência até término/cancelamento. Foro: ________________________.</p>
            </div>
        </div>

        <p class="meta" style="margin-top: 12px;">Preencha os campos em branco antes de enviar ao cliente.</p>
    </div>
</body>
</html>
