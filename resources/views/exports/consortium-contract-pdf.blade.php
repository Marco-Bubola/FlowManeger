<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        p { margin: 4px 0; }
        .section { margin-top: 14px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .muted { color: #6b7280; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Contrato / Regras do Consórcio</h1>
    <p class="muted">Consórcio: {{ $consortium->name }} | Valor mensal: R$ {{ number_format($consortium->monthly_value, 2, ',', '.') }} | Duração: {{ $consortium->duration_months }} meses | Início: {{ optional($consortium->start_date)->format('d/m/Y') ?? '-' }}</p>

    <div class="section">
        <h3>1. Participação</h3>
        <p>Cada participante recebe um número de participação único.</p>
        <p>Status possíveis: ativo, desistente, contemplado, cancelado.</p>
        <p>Participante contemplado não pode ser removido.</p>
    </div>

    <div class="section">
        <h3>2. Pagamentos</h3>
        <p>Parcelas mensais no valor acima, com vencimentos conforme calendário acordado.</p>
        <p>Métodos aceitos: dinheiro, PIX, cartão crédito/débito, transferência, boleto.</p>
        <p>Situações: pendente, paga, vencida. Vencidas podem ter juros/multa conforme política definida com o cliente.</p>
    </div>

    <div class="section">
        <h3>3. Sorteios e Contemplação</h3>
        <p>Frequência de sorteio: {{ $consortium->draw_frequency_label }}.</p>
        <p>Elegibilidade: participante deve estar ativo e não contemplado.</p>
        <p>Registro inclui tipo, data e número da participação vencedora.</p>
    </div>

    <div class="section">
        <h3>4. Resgate</h3>
        <p>Modos de resgate: produtos ou dinheiro. Para produtos, registrar itens e valores; para dinheiro, registrar valor acordado.</p>
        <p>Prazos de entrega/pagamento acordados entre administradora e participante.</p>
    </div>

    <div class="section">
        <h3>5. Desistência e Reativação</h3>
        <p>Participante pode ser marcado como desistente; reativação possível mediante autorização.</p>
        <p>Definir regras de reembolso e efeitos financeiros diretamente com o cliente.</p>
    </div>

    <div class="section">
        <h3>6. Cancelamento do Consórcio</h3>
        <p>O consórcio pode ser desativado/cancelado pela administradora por motivos operacionais ou legais.</p>
        <p>Impactos sobre participantes e valores devem ser comunicados e acordados.</p>
    </div>

    <div class="section">
        <h3>7. Comunicação</h3>
        <p>Canal oficial para dúvidas e suporte: ____________________________</p>
        <p>Avisos de sorteios e vencimentos serão enviados via: e-mail / WhatsApp / aplicativo (definir).</p>
    </div>

    <div class="section">
        <h3>8. Foro e Vigência</h3>
        <p>Vigência: da adesão até o término do prazo ou cancelamento.</p>
        <p>Foro para dirimir dúvidas: ____________________________</p>
    </div>

    <p class="muted" style="margin-top: 16px;">Preencha os campos em branco antes de enviar ao cliente.</p>
</body>
</html>
