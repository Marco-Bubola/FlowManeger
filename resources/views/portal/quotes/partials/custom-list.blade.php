{{-- Portal › Meus pedidos › Sob medida e reforma: orçamentos feitos pela loja e pedidos de orçamento. --}}
@php
    use App\Services\Quotes\QuoteService as QS;
    $labels = [
        'pedido' => ['Pedido recebido', 'A loja está olhando o seu pedido.', 'blue'],
        'rascunho' => ['Em análise', 'A loja está montando o seu orçamento.', 'blue'],
        'enviado' => ['Orçamento pronto', 'Veja e aprove pelo link.', 'orange'],
        'aprovado' => ['Aprovado', 'A loja vai combinar o pagamento e a entrega.', 'green'],
        'convertido' => ['Aprovado', 'Em produção.', 'green'],
        'recusado' => ['Não aprovado', '', 'gray'],
        'vencido' => ['Venceu', 'Peça um novo orçamento.', 'gray'],
    ];
@endphp
@forelse($customQuotes as $cq)
    @php
        [$lbl, $hint, $tone] = $labels[$cq->status] ?? $labels['rascunho'];
        $cover = $cq->coverPhoto();
        $open = !in_array($cq->status, ['pedido', 'rascunho'], true);
    @endphp
    <a href="{{ $open ? $cq->publicUrl() : '#' }}" class="po-card po-link-card" data-testid="custom-quote-card" @unless($open) onclick="return false" style="cursor:default" @endunless>
        <div class="po-card-head">
            <span><strong>{{ $cq->created_at->locale('pt_BR')->translatedFormat('j \d\e M. \d\e Y') }}</strong> · {{ $cq->code() }}</span>
            @if($cq->status === 'enviado' && !$cq->viewed_at)<span class="po-new">Novidade</span>@endif
            @if($cq->edited_after_sent_at && $cq->status === 'enviado')<span class="po-new" style="background:#f59e0b">Atualizado</span>@endif
        </div>
        <div class="po-card-body">
            <p class="po-status tone-{{ $tone }}">{{ $lbl }}</p>
            @if($hint)<p class="po-hint">{{ $hint }}</p>@endif
            <div class="po-prod">
                <div class="po-thumb">
                    @if($cover)<img src="{{ \App\Models\Quote::photoUrl($cover) }}" alt="" loading="lazy" onerror="this.remove()">@else<i class="fas {{ $cq->kind === 'reforma' ? 'fa-screwdriver-wrench' : 'fa-ruler-combined' }}"></i>@endif
                </div>
                <div style="min-width:0">
                    <div class="po-prod-name">{{ $cq->displayTitle() }}</div>
                    <div class="po-prod-sub">{{ $cq->kindInfo()['label'] }}{{ $cq->production_days ? ' · pronto em ' . $cq->production_days . ' dias' : '' }}</div>
                </div>
            </div>
        </div>
        <div class="po-card-foot">
            <div>
                <div class="po-total-lbl">Total</div>
                <div class="po-total">{{ $cq->total > 0 && $open ? QS::money($cq->total) : 'A combinar' }}</div>
            </div>
            @if($open)
                <span class="po-btn {{ $cq->status === 'enviado' ? 'po-btn-primary' : 'po-btn-soft' }}">{{ $cq->status === 'enviado' ? 'Ver e aprovar' : 'Ver orçamento' }} <i class="fas fa-chevron-right" style="font-size:11px"></i></span>
            @endif
        </div>
    </a>
@empty
    <div class="po-card po-empty">
        <i class="fas fa-ruler-combined"></i>
        <h2>Nenhum orçamento sob medida</h2>
        <p>Quer um produto do seu jeito ou reformar uma peça? Peça um orçamento com fotos.</p>
        <a href="{{ route('portal.custom-quote.create', ['userId' => $client->user_id]) }}" class="po-btn po-btn-primary">Pedir orçamento</a>
    </div>
@endforelse
@if($customQuotes->isNotEmpty())
    <a href="{{ route('portal.custom-quote.create', ['userId' => $client->user_id]) }}" class="po-btn po-btn-primary" style="align-self:flex-start"><i class="fas fa-plus"></i> Pedir outro orçamento</a>
@endif
