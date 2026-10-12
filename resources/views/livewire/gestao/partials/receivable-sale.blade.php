{{-- Uma compra em aberto. $r = linha de Receivables::openSales(); $withClient mostra o cliente (visão por compra). --}}
@php
    $soon = ! $r->overdue && $r->due && $r->due->lte($today->copy()->addDays(7));
    $client = $r->client;
    $waPhone = $client?->phone ? preg_replace('/\D/', '', $client->phone) : null;
    if ($waPhone && ! (strlen($waPhone) >= 12 && str_starts_with($waPhone, '55'))) { $waPhone = '55' . ltrim($waPhone, '0'); }
    $waMsg = 'Olá' . ($client ? ' ' . explode(' ', trim($client->name))[0] : '') . '! Tudo bem? Passando para lembrar da sua compra'
        . ($r->date ? ' de ' . $r->date->format('d/m/Y') : '') . ': falta R$ ' . number_format($r->remaining, 2, ',', '.')
        . ' de R$ ' . number_format($r->total, 2, ',', '.') . '.'
        . ($r->due ? ($r->overdue ? ' Venceu em ' . $r->due->format('d/m/Y') . '.' : ' Vence em ' . $r->due->format('d/m/Y') . '.') : '')
        . ' Qualquer dúvida é só me chamar. Obrigado!';
@endphp
<div class="rcv-sale {{ $r->overdue ? 'is-late' : '' }} {{ $withClient ? 'is-card' : '' }}" wire:key="rs-{{ $r->sale->id }}">
    <div class="rcv-sale-top">
        @if ($withClient)
            <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-10 h-10 text-sm" rounded="rounded-full" />
        @else
            <span class="rcv-sale-icon"><i class="bi bi-bag"></i></span>
        @endif
        <div class="min-w-0 flex-1">
            @if ($withClient)
                <p class="rcv-name truncate">{{ $client->name ?? 'Cliente não informado' }}</p>
            @endif
            <p class="rcv-sale-title">
                Compra #{{ $r->sale->id }}
                <span class="rcv-dim whitespace-nowrap">· {{ $r->date?->format('d/m/Y') }}@if($r->sale->sale_items_count) · {{ $r->sale->sale_items_count }} {{ $r->sale->sale_items_count == 1 ? 'item' : 'itens' }}@endif</span>
            </p>
            <div class="rcv-tags">
                @if ($r->due)
                    <span class="rcv-tag {{ $r->overdue ? 'is-rose' : ($soon ? 'is-amber' : '') }}">
                        <i class="bi {{ $r->overdue ? 'bi-exclamation-circle' : 'bi-calendar3' }}"></i>
                        {{ $r->overdue ? 'Venceu' : 'Vence' }} {{ $r->due->format('d/m') }}@if ($r->overdue) · {{ $r->late }}d @endif
                    </span>
                @else
                    <span class="rcv-tag"><i class="bi bi-calendar-x"></i>Sem vencimento</span>
                @endif
                @if ($r->parcelado)
                    <span class="rcv-tag" title="Parcelas são só uma referência: o cliente pode pagar qualquer valor">
                        <i class="bi bi-calendar2-range"></i>{{ $r->pendingParcels }} de {{ $r->parcelsTotal }} parcelas abertas
                    </span>
                @else
                    <span class="rcv-tag rcv-hide-xs"><i class="bi bi-wallet2"></i>Sem parcelas</span>
                @endif
            </div>
        </div>
        <div class="rcv-sale-money">
            <span class="rcv-dim">Falta</span>
            <strong class="{{ $r->overdue ? 'text-rose-600 dark:text-rose-400' : '' }}">R$ {{ number_format($r->remaining, 2, ',', '.') }}</strong>
        </div>
    </div>

    <div class="rcv-progress" title="Pago {{ $r->pct }}%">
        <div class="rcv-progress-bar" style="width: {{ $r->pct }}%"></div>
    </div>
    <div class="rcv-progress-legend">
        <span>Pago R$ {{ number_format($r->paid, 2, ',', '.') }}</span>
        <span>Total R$ {{ number_format($r->total, 2, ',', '.') }}</span>
    </div>

    <div class="rcv-actions">
        <button type="button" class="rcv-btn is-primary" wire:click="openReceive('sale', {{ $r->sale->id }})">
            <i class="bi bi-cash-coin"></i><span>Receber</span>
        </button>
        <button type="button" class="rcv-btn is-green" wire:click="openReceive('sale', {{ $r->sale->id }}, true)" title="Receber tudo o que falta">
            <i class="bi bi-check2-all"></i><span>Quitar</span>
        </button>
        @if ($waPhone)
            <a class="rcv-btn is-wa" href="https://wa.me/{{ $waPhone }}?text={{ rawurlencode($waMsg) }}" target="_blank" rel="noopener" title="Cobrar pelo WhatsApp">
                <i class="bi bi-whatsapp"></i><span class="rcv-wa-label">Cobrar</span>
            </a>
        @endif
        <a class="rcv-btn" href="{{ route('sales.show', $r->sale->id) }}" title="Abrir a venda"><i class="bi bi-receipt"></i><span class="rcv-open-label">Abrir</span></a>
        <button type="button" class="rcv-btn is-ghost" wire:click="askForgive({{ $r->sale->id }})" title="Perdoar o saldo (lança como desconto)">
            <i class="bi bi-eraser"></i>
        </button>
    </div>
</div>
