{{-- Painel da conta do cliente, no visual do catálogo. --}}
<x-portal-catalog-layout title="Minha conta" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
@endpush

@php
    $orders = app(\App\Services\Portal\PortalOrderService::class);
    $catalogUrl = route('portal.catalog', ['userId' => $client->user_id]);
    $firstName = explode(' ', trim($client->name))[0];
@endphp

<div class="po-page">
    <div class="po-head">
        <div>
            <h1>Olá, {{ $firstName }}!</h1>
            <p>Sua conta em {{ $store?->name ?? 'nossa loja' }}@if($client->portal_last_login_at) · último acesso {{ $client->portal_last_login_at->format('d/m H:i') }}@endif</p>
        </div>
    </div>

    @if(session('success'))
        <div class="po-flash"><i class="fas fa-circle-check" style="margin-top:2px"></i><span>{{ session('success') }}</span></div>
    @endif

    @if(! $client->cpf_cnpj || ! $client->phone)
        <div class="pa-alert">
            <i class="fas fa-circle-exclamation"></i>
            <span>Complete seus dados para agilizar os próximos pedidos.</span>
            <a href="{{ route('portal.profile') }}" class="po-btn po-btn-primary">Completar</a>
        </div>
    @endif

    <nav class="po-card pa-tiles" aria-label="Atalhos da conta" x-data>
        <a href="{{ route('portal.quotes') }}" class="pa-tile">
            <span class="pa-tile-ic"><i class="fas fa-box"></i></span>Meus pedidos
            @if($openOrders > 0)<span class="pa-badge">{{ $openOrders }}</span>@endif
        </a>
        <a href="{{ route('portal.sales') }}" class="pa-tile">
            <span class="pa-tile-ic green"><i class="fas fa-bag-shopping"></i></span>Minhas compras
        </a>
        <a href="{{ route('portal.favorites') }}" class="pa-tile">
            <span class="pa-tile-ic pink"><i class="fas fa-heart"></i></span>Favoritos
            <span class="pa-badge" style="background:#ec4899" x-show="$store.favs.count > 0" x-text="$store.favs.count" x-cloak></span>
        </a>
        <a href="{{ route('portal.profile') }}" class="pa-tile">
            <span class="pa-tile-ic amber"><i class="fas fa-user-pen"></i></span>Meus dados
        </a>
    </nav>

    <div class="po-card pa-stats">
        <div class="pa-stat">
            <div class="pa-stat-l">Compras</div>
            <div class="pa-stat-v">{{ $totalSales }}</div>
        </div>
        <div class="pa-stat">
            <div class="pa-stat-l">Total pago</div>
            <div class="pa-stat-v green">{{ $orders->money((float) $totalPaid) }}</div>
        </div>
        <div class="pa-stat">
            <div class="pa-stat-l">Pendentes</div>
            <div class="pa-stat-v {{ $pendingSales > 0 ? 'amber' : '' }}">{{ $pendingSales }}</div>
        </div>
    </div>

    <div class="po-grid">
        <div>
            <section class="po-card">
                <div class="pa-sec-head">
                    <h2>Últimas compras</h2>
                    @if($recentSales->isNotEmpty())<a href="{{ route('portal.sales') }}">Ver todas</a>@endif
                </div>
                @forelse($recentSales as $sale)
                    @php
                        [$label, $tone] = $orders->saleStatus($sale);
                        $first = $sale->saleItems->first()?->product;
                        $photo = $orders->photo($first);
                        $count = $sale->saleItems->sum('quantity');
                    @endphp
                    <a href="{{ route('portal.sales') }}#compra-{{ $sale->id }}" class="pa-row">
                        <div class="po-thumb">
                            @if($photo)<img src="{{ $photo }}" alt="" loading="lazy" onerror="this.remove()">@else<i class="fas fa-bag-shopping"></i>@endif
                        </div>
                        <div class="pa-row-main">
                            <div class="pa-row-t">{{ $first?->name ?? 'Compra #' . $sale->id }}</div>
                            <div class="pa-row-s">{{ $sale->created_at->format('d/m/Y') }} · {{ $count }} {{ $count === 1 ? 'unidade' : 'unidades' }}</div>
                        </div>
                        <div class="pa-row-end">
                            <strong>{{ $orders->money((float) $sale->total_price) }}</strong>
                            <span class="tone-{{ $tone }}">{{ $label }}</span>
                        </div>
                    </a>
                @empty
                    <div class="po-empty" style="padding:28px 16px">
                        <i class="fas fa-bag-shopping"></i>
                        <h2>Nenhuma compra ainda</h2>
                        <p>Quando a loja registrar uma compra sua, ela aparece aqui.</p>
                        <a href="{{ $catalogUrl }}" class="po-btn po-btn-primary">Ver produtos</a>
                    </div>
                @endforelse
            </section>
        </div>

        <div>
            <section class="po-card">
                <div class="pa-sec-head">
                    <h2>Pedidos pelo catálogo</h2>
                    @if($recentQuotes->isNotEmpty())<a href="{{ route('portal.quotes') }}">Ver todos</a>@endif
                </div>
                @forelse($recentQuotes as $quote)
                    @php [, $qLabel, $qTone] = $orders->clientStatus($quote, $quote->sale); @endphp
                    <a href="{{ route('portal.quotes.show', $quote) }}" class="pa-row">
                        <div class="pa-row-main">
                            <div class="pa-row-t">Pedido #{{ $quote->id }}</div>
                            <div class="pa-row-s">{{ $quote->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div class="pa-row-end"><span class="tone-{{ $qTone }}">{{ $qLabel }}</span></div>
                        <i class="fas fa-chevron-right" style="color:#bbb;font-size:12px"></i>
                    </a>
                @empty
                    <div class="pa-row" style="color:var(--ml-muted);font-size:13px">Você ainda não fez pedidos pelo catálogo.</div>
                @endforelse
            </section>

            <div class="po-card">
                <a href="{{ $catalogUrl }}" class="po-card-foot" style="border-top:0">
                    <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-store" style="color:var(--ml-blue)"></i> Continuar comprando</span>
                    <i class="fas fa-chevron-right" style="color:#bbb"></i>
                </a>
                <a href="{{ route('portal.quotes.create') }}" class="po-card-foot">
                    <span style="display:flex;align-items:center;gap:10px;font-weight:700"><i class="fas fa-cart-shopping" style="color:var(--ml-blue)"></i> Meu carrinho</span>
                    <i class="fas fa-chevron-right" style="color:#bbb"></i>
                </a>
                <form method="POST" action="{{ route('portal.logout') }}" class="po-card-foot" style="margin:0">
                    @csrf
                    <button type="submit" style="display:flex;align-items:center;gap:10px;font-weight:700;border:0;background:none;padding:0;cursor:pointer;color:var(--ml-red)"><i class="fas fa-right-from-bracket"></i> Sair da conta</button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-portal-catalog-layout>
