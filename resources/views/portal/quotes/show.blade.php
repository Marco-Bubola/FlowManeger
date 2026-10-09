{{-- Detalhe do pedido do portal: status, linha do tempo, itens e totais. Nunca mostra estoque. --}}
<x-portal-catalog-layout title="Pedido #{{ $quote->id }}" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
@endpush

@php
    [$statusKey, $statusLabel, $tone, $hint] = $orders->clientStatus($quote, $sale);
    $steps = $orders->timeline($quote, $sale);
    $items = $orders->items($quote, $sale, $products);
    $total = $orders->total($quote, $sale);
    $estimate = $orders->estimatedTotal($quote);
    $itemsTotal = (float) collect($items)->sum('total');
    $isEstimate = ! $sale && ! $quote->quoted_total;
    $paid = $sale ? (float) $sale->total_paid : 0;
    $icons = ['done' => 'fa-check', 'current' => 'fa-circle', 'todo' => 'fa-circle', 'failed' => 'fa-xmark'];
    $storePhone = app(\App\Services\Collections\CollectionService::class)->whatsappPhone($store?->phone);
    $storeWa = $storePhone ? 'https://wa.me/' . $storePhone . '?text=' . rawurlencode("Oi! Sobre o meu pedido #{$quote->id} no catálogo:") : null;
    $fmt = fn ($d) => $d?->locale('pt_BR')->translatedFormat('j \d\e M. \d\e Y, H:i');
@endphp

<div class="po-page" x-data="{ deleteModal: false }">
    <a href="{{ route('portal.quotes') }}" class="po-back"><i class="fas fa-chevron-left"></i> Meus pedidos</a>

    @if(session('success'))
        <div class="po-flash" data-testid="flash"><i class="fas fa-circle-check" style="margin-top:2px"></i>
            <span>{{ session('success') }}
                @if($quote->status === 'pending' && $quote->created_at->gt(now()->subMinutes(10)))
                    <br><span style="font-weight:500">Você vai ver aqui e em <a href="{{ route('portal.quotes') }}" style="text-decoration:underline">Meus pedidos</a> quando a loja confirmar.</span>
                @endif
            </span>
        </div>
    @endif
    @if($errors->any())
        <div class="po-flash err"><i class="fas fa-triangle-exclamation" style="margin-top:2px"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="po-grid">
        <div>
            {{-- Status --}}
            <div class="po-card" data-testid="order-status" data-status="{{ $statusKey }}">
                <div class="po-card-body">
                    <div class="po-hero-top">
                        <div class="po-hero-id"><strong>Pedido #{{ $quote->id }}</strong><br>{{ $fmt($quote->created_at) }}</div>
                        @if($isNew)<span class="po-new">Novidade</span>@endif
                    </div>
                    <p class="po-status tone-{{ $tone }}" style="font-size:20px;margin-top:12px">{{ $statusLabel }}</p>
                    <p class="po-hint">{{ $hint }}</p>

                    @if($quote->status === 'quoted')
                        <div class="po-msg" style="margin-top:12px;white-space:normal">
                            Proposta da loja: <strong>{{ $orders->money((float) ($quote->quoted_total ?? $estimate)) }}</strong>
                            @if($quote->valid_until)
                                · válida até {{ $quote->valid_until->format('d/m/Y') }}{{ $quote->valid_until->isPast() ? ' (expirada)' : '' }}
                            @endif
                        </div>
                        <div class="po-actions" style="grid-template-columns:1fr 1fr;margin-top:12px">
                            <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="po-btn po-btn-red po-btn-block">Recusar</button>
                            </form>
                            <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="po-btn po-btn-green po-btn-block"><i class="fas fa-check"></i> Aceitar</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Linha do tempo --}}
            <div class="po-card">
                <div class="po-card-body">
                    <h2 class="po-sec-title">Acompanhamento</h2>
                    <ol class="po-tl" data-testid="timeline">
                        @foreach($steps as $step)
                            <li class="{{ $step['state'] }}" data-step="{{ $step['key'] }}" data-state="{{ $step['state'] }}">
                                <span class="po-tl-ic"><i class="fas {{ $icons[$step['state']] }}" style="{{ $step['state'] === 'current' || $step['state'] === 'todo' ? 'font-size:7px' : '' }}"></i></span>
                                <div>
                                    <div class="po-tl-t">{{ $step['label'] }}</div>
                                    <div class="po-tl-d">{{ $step['date'] ? $fmt($step['date']) . ' · ' : '' }}{{ $step['text'] }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Recado da loja --}}
            @if(filled($quote->admin_notes))
                <div class="po-card">
                    <div class="po-card-body">
                        <h2 class="po-sec-title">Recado da loja</h2>
                        <div class="po-msg">{{ $quote->admin_notes }}</div>
                    </div>
                </div>
            @endif

            {{-- Itens --}}
            <div class="po-card">
                <div class="po-card-body">
                    <h2 class="po-sec-title">{{ count($items) === 1 ? '1 produto' : count($items) . ' produtos' }}</h2>
                    <ul class="po-items" data-testid="items">
                        @foreach($items as $item)
                            <li>
                                <div class="po-thumb">
                                    @if($item['image'])
                                        <img src="{{ $item['image'] }}" alt="" loading="lazy" onerror="this.remove()">
                                    @else
                                        <i class="fas fa-box-open"></i>
                                    @endif
                                </div>
                                <div class="po-info">
                                    <div class="po-prod-name">{{ $item['name'] }}</div>
                                    <div class="po-prod-sub">
                                        {{ $item['quantity'] }} {{ $item['quantity'] === 1 ? 'unidade' : 'unidades' }}
                                        @if($item['unit'] > 0) · {{ $orders->money($item['unit']) }} cada @endif
                                    </div>
                                    @if(filled($item['notes']))<div class="po-note">“{{ $item['notes'] }}”</div>@endif
                                </div>
                                <div class="po-price">
                                    @if($item['original'] && $item['original'] > $item['unit'])
                                        <s>{{ $orders->money($item['original'] * $item['quantity']) }}</s>
                                    @endif
                                    <strong>{{ $item['total'] > 0 ? $orders->money($item['total']) : '—' }}</strong>
                                </div>
                            </li>
                        @endforeach
                        @foreach($quote->extra_items ?? [] as $extra)
                            <li>
                                <div class="po-thumb"><i class="fas fa-pen-to-square" style="font-size:18px"></i></div>
                                <div class="po-info">
                                    <div class="po-prod-name">{{ $extra['description'] ?? 'Item adicional' }}</div>
                                    <div class="po-prod-sub">{{ $extra['quantity'] ?? 1 }} · item pedido por você</div>
                                </div>
                                <div class="po-price"><strong style="font-size:13px;color:var(--ml-muted)">A combinar</strong></div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="po-side">
            {{-- Resumo --}}
            <div class="po-card">
                <div class="po-card-body">
                    <h2 class="po-sec-title">Resumo</h2>
                    <table class="po-sum" data-testid="summary">
                        @if($itemsTotal > 0 && abs($itemsTotal - $total) > 0.009)
                            <tr><td>Produtos</td><td>{{ $orders->money($itemsTotal) }}</td></tr>
                            <tr><td>{{ $total < $itemsTotal ? 'Desconto' : 'Ajuste da loja' }}</td><td>{{ $total < $itemsTotal ? '− ' : '' }}{{ $orders->money(abs($total - $itemsTotal)) }}</td></tr>
                        @endif
                        @if($sale?->payment_method || $quote->payment_preference)
                            <tr><td>Pagamento</td><td>
                                {{ $orders->paymentLabel($sale?->payment_method ?? $quote->payment_preference) }}{{ $sale && $sale->tipo_pagamento === 'parcelado' && $sale->parcelas > 1 ? ' em ' . $sale->parcelas . 'x' : '' }}
                            </td></tr>
                        @endif
                        @if($sale && $paid > 0)
                            <tr><td>Pago</td><td class="tone-green">{{ $orders->money($paid) }}</td></tr>
                            @if($sale->total_price - $paid > 0.009)
                                <tr><td>Falta pagar</td><td>{{ $orders->money((float) $sale->total_price - $paid) }}</td></tr>
                            @endif
                        @endif
                        <tr class="po-sum-total"><td>{{ $isEstimate ? 'Total estimado' : 'Total' }}</td><td>{{ $total > 0 ? $orders->money($total) : 'A combinar' }}</td></tr>
                    </table>
                    @if($isEstimate && $quote->status !== 'rejected')
                        <p class="po-hint" style="font-size:12px;margin-top:8px">O valor final é confirmado pela loja.</p>
                    @endif
                </div>
            </div>

            @if(filled($quote->client_notes))
                <div class="po-card">
                    <div class="po-card-body">
                        <h2 class="po-sec-title">Sua observação</h2>
                        <p style="margin:0;font-size:14px;color:#444;white-space:pre-line">{{ $quote->client_notes }}</p>
                    </div>
                </div>
            @endif

            <div class="po-card">
                <div class="po-card-body po-actions">
                    @if($storeWa)
                        <a href="{{ $storeWa }}" target="_blank" rel="noopener" class="po-btn po-btn-wa po-btn-block"><i class="fab fa-whatsapp" style="font-size:18px"></i> Falar com a loja</a>
                    @endif
                    @if($quote->can_edit)
                        <a href="{{ route('portal.quotes.edit', $quote) }}" class="po-btn po-btn-soft po-btn-block"><i class="fas fa-pen"></i> Alterar pedido</a>
                        <button type="button" @click="deleteModal = true" class="po-btn po-btn-red po-btn-block"><i class="fas fa-trash"></i> Cancelar pedido</button>
                    @endif
                    <a href="{{ route('portal.catalog', ['userId' => $client->user_id]) }}" class="po-btn po-btn-ghost po-btn-block"><i class="fas fa-store"></i> Continuar comprando</a>
                </div>
            </div>
        </div>
    </div>

    @if($quote->can_edit)
        <div x-show="deleteModal" x-cloak x-transition.opacity @keydown.escape.window="deleteModal = false"
             style="position:fixed;inset:0;z-index:90;background:rgba(0,0,0,.5);display:flex;align-items:flex-end;justify-content:center;padding:12px"
             @click.self="deleteModal = false">
            <div class="po-card" style="width:100%;max-width:420px;margin:0 0 env(safe-area-inset-bottom)">
                <div class="po-card-body">
                    <h2 class="po-sec-title" style="margin-bottom:6px">Cancelar o pedido #{{ $quote->id }}?</h2>
                    <p class="po-hint" style="margin-bottom:14px">A loja ainda não confirmou. O pedido será apagado.</p>
                    <div class="po-actions" style="grid-template-columns:1fr 1fr">
                        <button type="button" class="po-btn po-btn-ghost" @click="deleteModal = false">Voltar</button>
                        <form method="POST" action="{{ route('portal.quotes.destroy', $quote) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="po-btn po-btn-block" style="background:var(--ml-red);color:#fff">Cancelar pedido</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

</x-portal-catalog-layout>
