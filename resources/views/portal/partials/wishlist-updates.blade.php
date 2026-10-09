{{--
    Aviso "Avise-me" para o cliente logado: um favorito entrou em promoção ou
    voltou ao estoque. Some quando o cliente abre "Meus favoritos".
    Variáveis opcionais: $variant ('ml' | 'app').
--}}
@php
    $variant = $variant ?? 'ml';
    $wishAlerts = collect();
    if (Auth::guard('portal')->check() && ! request()->routeIs('portal.favorites') && \App\Services\Portal\WishlistService::ready()) {
        $wishAlerts = app(\App\Services\Portal\WishlistService::class)->unseenAlertsForRequest(Auth::guard('portal')->user());
    }
    $wishFirst = $wishAlerts->first();
@endphp
@if($wishFirst)
    @php
        $wishName = app(\App\Services\Portal\WishlistService::class)->productName($wishFirst->product);
        $wishText = $wishFirst->reason === 'promo' ? 'entrou em promoção!' : 'voltou ao estoque!';
        $wishMore = $wishAlerts->count() - 1;
    @endphp
    <style>
        .wl-strip { display:block; text-decoration:none; }
        .wl-strip-in { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; font-size:13px; font-weight:600; line-height:1.3;
            background:#fdf2f8; color:#9d174d; border:1px solid #fbcfe8; box-shadow:0 1px 2px rgba(0,0,0,.08); }
        .wl-strip .wl-ic { width:30px; height:30px; flex-shrink:0; border-radius:50%; background:#ec4899; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .wl-strip .wl-txt { flex:1; min-width:0; }
        .wl-strip .wl-txt b { font-weight:800; }
        .wl-strip .wl-go { flex-shrink:0; font-weight:800; font-size:12px; white-space:nowrap; }
        .wl-strip-wrap-ml { max-width:1200px; margin:10px auto 0; padding:0 12px; }
        .wl-strip-wrap-app { margin:0 0 12px; }
    </style>
    <div class="wl-strip-wrap-{{ $variant }}">
        <a href="{{ route('portal.favorites') }}" class="wl-strip" data-testid="wishlist-update">
            <span class="wl-strip-in">
                <span class="wl-ic"><i class="fas {{ $wishFirst->reason === 'promo' ? 'fa-tag' : 'fa-heart' }}"></i></span>
                <span class="wl-txt">
                    <b>{{ Str::limit($wishName, 48) }}</b>, que você favoritou, {{ $wishText }}
                    @if($wishMore > 0)
                        <span style="font-weight:500;opacity:.85">+{{ $wishMore }} {{ $wishMore === 1 ? 'novidade' : 'novidades' }}</span>
                    @endif
                </span>
                <span class="wl-go">Ver <i class="fas fa-chevron-right" style="font-size:10px"></i></span>
            </span>
        </a>
    </div>
@endif
