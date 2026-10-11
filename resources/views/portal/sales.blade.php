{{-- Minhas compras: vendas registradas para o cliente, no visual do catálogo. --}}
<x-portal-catalog-layout title="Minhas compras" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
@endpush

@php
    $orders = app(\App\Services\Portal\PortalOrderService::class);
    $catalogUrl = route('portal.catalog', ['userId' => $client->user_id]);
@endphp

<div class="po-page">
    <a href="{{ route('portal.dashboard') }}" class="po-back"><i class="fas fa-chevron-left"></i> Minha conta</a>
    <div class="po-head">
        <div>
            <h1>Minhas compras</h1>
            <p>Tudo que você comprou em {{ $store?->name ?? 'nossa loja' }}, pelo catálogo ou direto com a loja.</p>
        </div>
    </div>

    <div class="po-card pa-stats four">
        <div class="pa-stat"><div class="pa-stat-l">Compras</div><div class="pa-stat-v">{{ $kpiTotal }}</div></div>
        <div class="pa-stat"><div class="pa-stat-l">Valor total</div><div class="pa-stat-v">{{ $orders->money((float) $kpiTotalValue) }}</div></div>
        <div class="pa-stat"><div class="pa-stat-l">Já pago</div><div class="pa-stat-v green">{{ $orders->money((float) $kpiPaid) }}</div></div>
        <div class="pa-stat"><div class="pa-stat-l">Falta pagar</div><div class="pa-stat-v {{ $kpiTotalValue - $kpiPaid > 0.009 ? 'amber' : '' }}">{{ $orders->money(max(0, (float) $kpiTotalValue - (float) $kpiPaid)) }}</div></div>
    </div>

    @forelse($sales as $sale)
        @php
            [$label, $tone, $hint] = $orders->saleStatus($sale);
            $paid = (float) $sale->total_paid;
            $items = $sale->saleItems;
        @endphp
        <article class="po-card" id="compra-{{ $sale->id }}" data-testid="sale-card">
            <div class="po-card-head">
                <span><strong>{{ $sale->created_at->locale('pt_BR')->translatedFormat('j \d\e M. \d\e Y') }}</strong> · Compra #{{ $sale->id }}</span>
                @if($sale->payment_method)
                    <span style="font-size:12px">{{ $orders->paymentLabel($sale->payment_method) }}</span>
                @endif
            </div>
            <div class="po-card-body">
                <p class="po-status tone-{{ $tone }}">{{ $label }}</p>
                <p class="po-hint">{{ $hint }}</p>

                @if($items->isNotEmpty())
                <ul class="po-items" style="margin-top:12px">
                    @foreach($items->take(3) as $item)
                        @php $photo = $orders->photo($item->product); @endphp
                        <li>
                            <div class="po-thumb">
                                @if($photo)<img src="{{ $photo }}" alt="" loading="lazy" onerror="this.remove()">@else<i class="fas fa-box-open"></i>@endif
                            </div>
                            <div class="po-info">
                                <div class="po-prod-name">{{ $item->product->name ?? 'Produto removido' }}</div>
                                <div class="po-prod-sub">{{ $item->quantity }} × {{ $orders->money((float) $item->price_sale) }}</div>
                            </div>
                            <div class="po-price"><strong>{{ $orders->money((float) $item->price_sale * $item->quantity) }}</strong></div>
                        </li>
                    @endforeach
                </ul>
                @if($items->count() > 3)
                    <p class="po-hint" style="margin-top:8px">e mais {{ $items->count() - 3 }} {{ $items->count() - 3 === 1 ? 'produto' : 'produtos' }}</p>
                @endif
                @endif
            </div>
            <div class="po-card-foot">
                <div>
                    <div class="po-total-lbl">Total</div>
                    <div class="po-total">{{ $orders->money((float) $sale->total_price) }}</div>
                </div>
                @if($sale->total_price > 0)
                <div style="text-align:right">
                    <div class="po-total-lbl">Pago</div>
                    <div style="font-size:15px;font-weight:800;color:var(--ml-green)">{{ $orders->money($paid) }}</div>
                </div>
                @endif
            </div>
        </article>
    @empty
        <div class="po-card po-empty">
            <i class="fas fa-bag-shopping"></i>
            <h2>Nenhuma compra ainda</h2>
            <p>Escolha os produtos no catálogo e envie o seu pedido.</p>
            <a href="{{ $catalogUrl }}" class="po-btn po-btn-primary">Ir para o catálogo</a>
        </div>
    @endforelse

    @if($sales->hasPages())
        <div class="po-pager">
            @if($sales->onFirstPage())<span></span>@else
                <a href="{{ $sales->previousPageUrl() }}" class="po-btn po-btn-ghost"><i class="fas fa-chevron-left"></i> Anteriores</a>
            @endif
            <span style="align-self:center;color:var(--ml-muted);font-size:13px">Página {{ $sales->currentPage() }} de {{ $sales->lastPage() }}</span>
            @if($sales->hasMorePages())
                <a href="{{ $sales->nextPageUrl() }}" class="po-btn po-btn-ghost">Próximas <i class="fas fa-chevron-right"></i></a>
            @else<span></span>@endif
        </div>
    @endif

    <div class="po-card">
        <a href="{{ $catalogUrl }}" class="po-card-foot" style="border-top:0">
            <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-store" style="color:var(--ml-blue)"></i> Continuar comprando</span>
            <i class="fas fa-chevron-right" style="color:#bbb"></i>
        </a>
    </div>
</div>

</x-portal-catalog-layout>
