{{-- PDF do orçamento para o cliente: só preço, prazo e fotos (sem custo). DomPDF: tabelas, nada de flex/grid. --}}
@php
    use App\Services\Quotes\QuoteService as QS;
    $money = fn ($v) => QS::money((float) $v);
    $subtotal = collect($items)->sum('line');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 26px 30px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .head td { vertical-align: middle; }
    .store { font-size: 16px; font-weight: bold; }
    .muted { color: #777; }
    .code { font-size: 10px; letter-spacing: 1px; color: #7c3aed; font-weight: bold; text-transform: uppercase; }
    h1 { font-size: 19px; margin: 4px 0 2px; }
    .bar { height: 4px; background: #8b5cf6; margin: 10px 0 14px; }
    table { width: 100%; border-collapse: collapse; }
    .items td { padding: 9px 6px; border-bottom: 1px solid #eee; vertical-align: top; }
    .items th { text-align: left; font-size: 9px; text-transform: uppercase; color: #888; padding: 4px 6px; border-bottom: 2px solid #ddd; }
    .ph { width: 70px; }
    .ph img { width: 64px; height: 64px; border-radius: 6px; }
    .name { font-size: 12px; font-weight: bold; }
    .attr { display: inline-block; background: #f1ecff; color: #6d28d9; padding: 1px 6px; border-radius: 8px; font-size: 9px; margin: 3px 3px 0 0; }
    .desc { color: #555; margin-top: 3px; }
    .num { text-align: right; white-space: nowrap; }
    .tot td { padding: 4px 6px; }
    .grand td { font-size: 15px; font-weight: bold; border-top: 2px solid #222; padding-top: 7px; }
    .info td { width: 33%; background: #f6f6f8; padding: 8px; border: 3px solid #fff; }
    .info b { display: block; font-size: 12px; margin-top: 2px; }
    .small { font-size: 9px; text-transform: uppercase; color: #888; }
    .notes { margin-top: 12px; padding: 9px; border: 1px solid #eee; border-radius: 6px; }
    .foot { margin-top: 18px; font-size: 9px; color: #888; text-align: center; }
</style>
</head>
<body>
<table class="head">
    <tr>
        <td style="width:70%">
            @if($logoPath)<img src="{{ $logoPath }}" style="height:42px;margin-bottom:4px"><br>@endif
            <span class="store">{{ $identity['name'] }}</span><br>
            <span class="muted">{{ implode(' · ', array_filter([$identity['whatsapp'] ?? '', $identity['instagram'] ? '@' . ltrim($identity['instagram'], '@') : '', $identity['address'] ?? ''])) }}</span>
        </td>
        <td style="text-align:right">
            <span class="code">Orçamento {{ $quote->code() }}</span><br>
            <span class="muted">{{ ($quote->sent_at ?? $quote->updated_at)->timezone(QS::TZ)->format('d/m/Y') }}</span>
        </td>
    </tr>
</table>
<div class="bar"></div>
<h1>{{ $quote->displayTitle() }}</h1>
<p class="muted" style="margin:0 0 10px">Para {{ $quote->customerName() }} · {{ $quote->kindInfo()['label'] }}@if($quote->kind === 'reforma' && $quote->services) · {{ implode(', ', $quote->services) }}@endif</p>

<table class="items">
    <tr><th></th><th>Item</th><th class="num">Qtd.</th><th class="num">Valor</th><th class="num">Total</th></tr>
    @foreach($items as $it)
        @php $img = $it['photos'] ? $local($it['photos'][0]) : null; @endphp
        <tr>
            <td class="ph">@if($img)<img src="{{ $img }}">@endif</td>
            <td>
                <div class="name">{{ $it['name'] }}</div>
                @foreach($it['attrs'] as $v)<span class="attr">{{ $v }}</span>@endforeach
                @if($it['description'] !== '')<div class="desc">{!! nl2br(e($it['description'])) !!}</div>@endif
            </td>
            <td class="num">{{ $it['qty'] }}</td>
            <td class="num">{{ $money($it['price']) }}</td>
            <td class="num"><b>{{ $money($it['line']) }}</b></td>
        </tr>
    @endforeach
</table>

<table class="tot" style="width:45%;margin-left:55%;margin-top:8px">
    <tr><td>Itens</td><td class="num">{{ $money($subtotal) }}</td></tr>
    @if($quote->discount > 0)<tr><td>Desconto</td><td class="num">− {{ $money($quote->discount) }}</td></tr>@endif
    @if($quote->delivery_fee > 0)<tr><td>Entrega</td><td class="num">{{ $money($quote->delivery_fee) }}</td></tr>@endif
    <tr class="grand"><td>Total</td><td class="num">{{ $money($quote->total) }}</td></tr>
</table>

<table class="info" style="margin-top:14px">
    <tr>
        <td><span class="small">Fica pronto em</span><b>{{ $quote->production_days ? $quote->production_days . ' dias' : 'A combinar' }}</b></td>
        <td><span class="small">Pagamento</span><b>{{ $quote->payment_terms ?: 'A combinar' }}</b></td>
        <td><span class="small">Válido até</span><b>{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</b></td>
    </tr>
</table>

@if($quote->client_notes || $footer)
    <div class="notes">
        @if($quote->client_notes)<div>{!! nl2br(e($quote->client_notes)) !!}</div>@endif
        @if($footer)<div class="muted" style="margin-top:6px">{!! nl2br(e($footer)) !!}</div>@endif
    </div>
@endif

<p class="foot">Para aprovar ou pedir mudança: {{ $quote->publicUrl() }}</p>
</body>
</html>
