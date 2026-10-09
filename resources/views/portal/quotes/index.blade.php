{{-- Meus pedidos: pedidos feitos pelo portal, com o status e o andamento da venda. --}}
<x-portal-catalog-layout title="Meus pedidos" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
@endpush

@php
    $catalogUrl = route('portal.catalog', ['userId' => $client->user_id]);
    $shortLabels = ['sent' => 'Enviado', 'quoted' => 'Proposta', 'confirmed' => 'Confirmado', 'rejected' => 'Recusado', 'paid' => 'Pago', 'delivered' => 'Entregue', 'cancelled' => 'Cancelado'];
    $chips = ['' => 'Todos', 'open' => 'Em andamento', 'confirmed' => 'Confirmados', 'rejected' => 'Recusados'];
@endphp

<div class="po-page">
    <div class="po-head">
        <div>
            <h1>Meus pedidos</h1>
            <p>Acompanhe o que você pediu para {{ $store?->name ?? 'a loja' }}.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="po-flash"><i class="fas fa-circle-check" style="margin-top:2px"></i><span>{{ session('success') }}</span></div>
    @endif

    <nav class="po-chips" aria-label="Filtrar pedidos">
        @foreach($chips as $key => $label)
            <a href="{{ route('portal.quotes', array_filter(['status' => $key])) }}" class="po-chip {{ $filter === $key ? 'on' : '' }}">
                {{ $label }} <b>{{ $counts[$key] }}</b>
            </a>
        @endforeach
    </nav>

    @forelse($quotes as $quote)
        @php
            $sale = $quote->sale;
            [$statusKey, $statusLabel, $tone, $hint] = $orders->clientStatus($quote, $sale);
            $steps = $orders->timeline($quote, $sale);
            $items = $orders->items($quote, $sale, $products);
            $first = $items[0] ?? null;
            $units = collect($items)->sum('quantity') + collect($quote->extra_items ?? [])->sum('quantity');
            $total = $orders->total($quote, $sale);
            $isEstimate = ! $sale && ! $quote->quoted_total;
        @endphp
        <a href="{{ route('portal.quotes.show', $quote) }}" class="po-card po-link-card" data-testid="order-card" data-order="{{ $quote->id }}">
            <div class="po-card-head">
                <span><strong>{{ $quote->created_at->locale('pt_BR')->translatedFormat('j \d\e M. \d\e Y') }}</strong> · Pedido #{{ $quote->id }}</span>
                @if($quote->has_unseen_update)
                    <span class="po-new">Novidade</span>
                @endif
            </div>
            <div class="po-card-body">
                <p class="po-status tone-{{ $tone }}">{{ $statusLabel }}</p>
                <p class="po-hint">{{ $hint }}</p>

                @if($first || !empty($quote->extra_items))
                <div class="po-prod">
                    <div class="po-thumb">
                        @if($first && $first['image'])
                            <img src="{{ $first['image'] }}" alt="" loading="lazy" onerror="this.remove()">
                        @else
                            <i class="fas fa-box-open"></i>
                        @endif
                        @if(count($items) > 1)
                            <span class="po-thumb-more">+{{ count($items) - 1 }}</span>
                        @endif
                    </div>
                    <div style="min-width:0">
                        <div class="po-prod-name">{{ $first['name'] ?? ($quote->extra_items[0]['description'] ?? 'Itens do pedido') }}</div>
                        <div class="po-prod-sub">
                            {{ $units }} {{ $units === 1 ? 'unidade' : 'unidades' }}
                            @if(count($items) > 1) · {{ count($items) }} produtos @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="po-steps" aria-hidden="true">
                    @foreach($steps as $step)
                        <div class="po-step {{ $step['state'] }}"><i class="po-dot"></i><span>{{ $shortLabels[$step['key']] ?? $step['label'] }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="po-card-foot">
                <div>
                    <div class="po-total-lbl">{{ $isEstimate ? 'Total estimado' : 'Total' }}</div>
                    <div class="po-total">{{ $total > 0 ? $orders->money($total) : 'A combinar' }}</div>
                </div>
                <span class="po-btn {{ $statusKey === 'quoted' ? 'po-btn-primary' : 'po-btn-soft' }}">
                    {{ $statusKey === 'quoted' ? 'Responder' : 'Ver pedido' }} <i class="fas fa-chevron-right" style="font-size:11px"></i>
                </span>
            </div>
        </a>
    @empty
        <div class="po-card po-empty">
            <i class="fas fa-box-open"></i>
            <h2>{{ $filter ? 'Nenhum pedido aqui' : 'Você ainda não fez pedidos' }}</h2>
            <p>{{ $filter ? 'Troque o filtro para ver os outros pedidos.' : 'Escolha os produtos no catálogo e envie o seu pedido.' }}</p>
            <a href="{{ $filter ? route('portal.quotes') : $catalogUrl }}" class="po-btn po-btn-primary">{{ $filter ? 'Ver todos' : 'Ir para o catálogo' }}</a>
        </div>
    @endforelse

    @if($quotes->hasPages())
        <div class="po-pager">
            @if($quotes->onFirstPage())<span></span>@else
                <a href="{{ $quotes->previousPageUrl() }}" class="po-btn po-btn-ghost"><i class="fas fa-chevron-left"></i> Anteriores</a>
            @endif
            <span style="align-self:center;color:var(--ml-muted);font-size:13px">Página {{ $quotes->currentPage() }} de {{ $quotes->lastPage() }}</span>
            @if($quotes->hasMorePages())
                <a href="{{ $quotes->nextPageUrl() }}" class="po-btn po-btn-ghost">Próximos <i class="fas fa-chevron-right"></i></a>
            @else<span></span>@endif
        </div>
    @endif

    <div class="po-card">
        <a href="{{ $catalogUrl }}" class="po-card-foot" style="border-top:0">
            <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-store" style="color:var(--ml-blue)"></i> Continuar comprando</span>
            <i class="fas fa-chevron-right" style="color:#bbb"></i>
        </a>
        @if($otherSales > 0)
        <a href="{{ route('portal.sales') }}" class="po-card-foot">
            <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-bag-shopping" style="color:var(--ml-blue)"></i> Compras feitas na loja <b style="color:var(--ml-muted);font-weight:700">({{ $otherSales }})</b></span>
            <i class="fas fa-chevron-right" style="color:#bbb"></i>
        </a>
        @endif
    </div>
</div>

</x-portal-catalog-layout>
