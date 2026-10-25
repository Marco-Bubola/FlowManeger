{{-- Kit montado no pedido (base, enfeites e cartão). Precisa de $kit e $money. --}}
@php
    $kLook = \App\Services\Portal\KitService::style($kit['style'] ?? null);
    $kLines = \App\Services\Portal\KitService::lines($kit);
@endphp
<div class="po-card" data-testid="order-kit" style="overflow:hidden">
    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;color:#fff;background:linear-gradient(135deg, {{ $kLook[3] }}, {{ $kLook[4] }})">
        <span style="font-size:30px;line-height:1">{{ $kLook[1] }}</span>
        <div style="min-width:0">
            <small style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.85">Kit montado por você</small>
            <strong style="display:block;font-size:16px">{{ $kit['name'] ?? 'Kit' }}</strong>
        </div>
    </div>
    <div class="po-card-body">
        <ul class="po-items">
            @foreach($kLines as [$kName, $kQty, $kVal])
                <li>
                    <div class="po-thumb" style="font-size:22px">{{ mb_substr($kName, 0, 1) }}</div>
                    <div class="po-info">
                        <div class="po-prod-name">{{ trim(mb_substr($kName, 1)) }}</div>
                        <div class="po-prod-sub">{{ $loop->first ? 'Base e montagem' : $kQty . ($kQty == 1 ? ' unidade' : ' unidades') }}</div>
                    </div>
                    <div class="po-price"><strong>{{ $kVal > 0 ? $money($kVal) : 'Grátis' }}</strong></div>
                </li>
            @endforeach
        </ul>
        @if(filled($kit['message'] ?? null) || filled($kit['to'] ?? null))
            <div style="margin-top:12px;padding:12px 14px;border-radius:12px;background:var(--ml-chip, #fdf2f8);border:1px dashed {{ $kLook[3] }}">
                <small style="display:block;font-size:11px;font-weight:700;color:{{ $kLook[3] }};text-transform:uppercase;letter-spacing:.05em">💌 Cartão</small>
                @if(filled($kit['to'] ?? null))<p style="margin:4px 0 0;font-size:13px;color:var(--ml-text, #333)">Para: <b>{{ $kit['to'] }}</b></p>@endif
                @if(filled($kit['message'] ?? null))<p style="margin:4px 0 0;font-size:14px;color:var(--ml-text, #333);white-space:pre-line;font-style:italic">“{{ $kit['message'] }}”</p>@endif
                @if(filled($kit['from'] ?? null))<p style="margin:4px 0 0;font-size:13px;color:var(--ml-muted, #666)">De: {{ $kit['from'] }}</p>@endif
            </div>
        @endif
    </div>
</div>
