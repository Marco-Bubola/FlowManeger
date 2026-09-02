@props(['sale'])

{{--
  Cabeçalho padrão das exportações de venda (PDF e imagem).

  Layout em <table>, não em flex: o DomPDF ignora flexbox por completo, e o
  html2canvas rasteriza melhor tabelas. É o único jeito de o mesmo bloco sair
  igual nos dois formatos.

  TODA cor é declarada explicitamente. O card é forçado para fundo branco na
  exportação; sem `color` os textos herdavam a cor do tema e, no modo escuro,
  saíam brancos sobre branco — foi assim que o nome do cliente e os nomes dos
  produtos sumiram da imagem exportada.
--}}

@php
    $iniciais = mb_strtoupper(mb_substr($sale->client->name ?? 'CL', 0, 2));
    $statusMapa = [
        'pago'      => ['rotulo' => 'PAGO',      'fundo' => '#dcfce7', 'texto' => '#15803d'],
        'pendente'  => ['rotulo' => 'PENDENTE',  'fundo' => '#fef3c7', 'texto' => '#b45309'],
        'cancelada' => ['rotulo' => 'CANCELADA', 'fundo' => '#fee2e2', 'texto' => '#b91c1c'],
    ];
    $st = $statusMapa[$sale->status] ?? ['rotulo' => mb_strtoupper($sale->status ?: 'ABERTA'), 'fundo' => '#e2e8f0', 'texto' => '#475569'];
    $qtdItens = $sale->saleItems->sum('quantity');
    $pagamento = $sale->tipo_pagamento === 'parcelado'
        ? $sale->parcelas . 'x de R$ ' . number_format(($sale->parcelas > 0 ? $sale->total_price / $sale->parcelas : 0), 2, ',', '.')
        : 'À vista';
@endphp

<table style="width:100%; border-collapse:collapse; border-radius:14px; background:#6d28d9; color:#ffffff;">
    <tr>
        <td style="padding:18px 20px; vertical-align:middle; width:64px;">
            <div style="width:52px; height:52px; border-radius:14px; background:#ffffff; color:#6d28d9;
                        font-size:20px; font-weight:bold; text-align:center; line-height:52px;">{{ $iniciais }}</div>
        </td>
        <td style="padding:18px 8px; vertical-align:middle; color:#ffffff;">
            <div style="font-size:20px; font-weight:bold; color:#ffffff; line-height:1.2;">Venda #{{ $sale->id }}</div>
            <div style="font-size:13px; color:#e9d5ff; margin-top:3px;">{{ $sale->client->name ?? 'Cliente' }}</div>
        </td>
        <td style="padding:18px 20px; vertical-align:middle; text-align:right; color:#ffffff;">
            <div style="font-size:22px; font-weight:bold; color:#ffffff; line-height:1.2;">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</div>
            <div style="font-size:12px; color:#e9d5ff; margin-top:3px;">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-top:10px;">
    <tr>
        <td style="width:33.33%; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; vertical-align:top;">
            <div style="font-size:10px; font-weight:bold; color:#64748b; letter-spacing:0.08em;">STATUS</div>
            <div style="margin-top:5px;">
                <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold;
                             background:{{ $st['fundo'] }}; color:{{ $st['texto'] }};">{{ $st['rotulo'] }}</span>
            </div>
        </td>
        <td style="width:33.33%; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; vertical-align:top;">
            <div style="font-size:10px; font-weight:bold; color:#64748b; letter-spacing:0.08em;">ITENS</div>
            <div style="margin-top:5px; font-size:14px; font-weight:bold; color:#0f172a;">{{ $qtdItens }}</div>
        </td>
        <td style="width:33.33%; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; vertical-align:top;">
            <div style="font-size:10px; font-weight:bold; color:#64748b; letter-spacing:0.08em;">PAGAMENTO</div>
            <div style="margin-top:5px; font-size:13px; font-weight:bold; color:#0f172a;">{{ $pagamento }}</div>
        </td>
    </tr>
</table>
