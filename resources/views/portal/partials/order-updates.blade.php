{{--
    Aviso de novidade nos pedidos do cliente logado no portal (pedido
    confirmado, recusado ou com proposta). Some quando o cliente abre o pedido.
    Variáveis opcionais: $variant ('ml' | 'app').
--}}
@php
    $variant = $variant ?? 'ml';
    $portalUpdates = collect();
    if (Auth::guard('portal')->check()) {
        $currentQuote = request()->route('quote');
        $portalUpdates = \App\Models\ClientQuoteRequest::where('client_id', Auth::guard('portal')->id())
            ->unseenByClient()
            ->when($currentQuote, fn ($q) => $q->whereKeyNot(is_object($currentQuote) ? $currentQuote->getKey() : $currentQuote))
            ->latest('responded_at')
            ->get(['id', 'status', 'responded_at']);
    }
    $latestUpdate = $portalUpdates->first();
    $updateCfg = [
        'approved' => ['fa-circle-check', 'foi confirmado pela loja!', 'po-tone-ok'],
        'rejected' => ['fa-circle-xmark', 'não pôde ser atendido.', 'po-tone-bad'],
        'quoted'   => ['fa-tag', 'recebeu uma proposta. Responda!', 'po-tone-info'],
    ];
@endphp
@if($latestUpdate && ! request()->routeIs('portal.quotes'))
    @php [$uIcon, $uText, $uTone] = $updateCfg[$latestUpdate->status] ?? $updateCfg['quoted']; @endphp
    <style>
        .po-strip { display:block; text-decoration:none; }
        .po-strip-in { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; font-size:13px; font-weight:600; line-height:1.3; box-shadow:0 1px 2px rgba(0,0,0,.08); }
        .po-strip i.po-ic { font-size:18px; flex-shrink:0; }
        .po-strip .po-txt { flex:1; min-width:0; }
        .po-strip .po-go { flex-shrink:0; font-weight:800; font-size:12px; white-space:nowrap; }
        .po-tone-ok   { background:#e6f7ee; color:#00733a; border:1px solid #b7e6cc; }
        .po-tone-bad  { background:#fdecee; color:#b4232f; border:1px solid #f6c3c8; }
        .po-tone-info { background:#f3edff; color:#6d28d9; border:1px solid #ddd0fb; }
        .po-strip-wrap-ml { max-width:1200px; margin:10px auto 0; padding:0 12px; }
        .po-strip-wrap-app { margin:0 0 12px; }
    </style>
    <div class="po-strip-wrap-{{ $variant }}">
        <a href="{{ $portalUpdates->count() > 1 ? route('portal.quotes') : route('portal.quotes.show', $latestUpdate->id) }}" class="po-strip" data-testid="order-update">
            <span class="po-strip-in {{ $uTone }}">
                <i class="fas {{ $uIcon }} po-ic"></i>
                <span class="po-txt">
                    Seu pedido #{{ $latestUpdate->id }} {{ $uText }}
                    @if($portalUpdates->count() > 1)
                        <span style="font-weight:500;opacity:.85">+{{ $portalUpdates->count() - 1 }} {{ $portalUpdates->count() - 1 === 1 ? 'novidade' : 'novidades' }}</span>
                    @endif
                </span>
                <span class="po-go">{{ $portalUpdates->count() > 1 ? 'Ver pedidos' : 'Ver pedido' }} <i class="fas fa-chevron-right" style="font-size:10px"></i></span>
            </span>
        </a>
    </div>
@endif
