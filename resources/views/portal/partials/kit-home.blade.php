{{-- Página inicial do catálogo: chamada do "Monte seu kit" com os tipos de kit da loja. --}}
@php
    $kTypes = \App\Services\Portal\KitService::types($owner)->take(8);
    $kUrl = route('portal.kit', ['userId' => $owner]);
@endphp
@if($kTypes->isNotEmpty())
<section class="kh" data-testid="home-kits">
    <style>
        .kh { margin: 14px 0; border-radius: var(--ml-radius, 14px); overflow: hidden; background: var(--ml-card, #fff); box-shadow: 0 1px 2px rgba(0,0,0,.08); }
        .kh-top { position: relative; display: flex; align-items: center; gap: 14px; padding: 18px 18px 16px; color: #fff; background: linear-gradient(120deg, #e11d48, #c026d3 55%, #7c3aed); text-decoration: none; }
        .kh-top h2 { margin: 0; font-size: clamp(18px, 2.6vw, 24px); font-weight: 800; color: #fff; }
        .kh-top p { margin: 3px 0 0; font-size: 13px; opacity: .92; }
        .kh-top .kh-cta { margin-left: auto; flex-shrink: 0; padding: 10px 16px; border-radius: 12px; background: #fff; color: #be185d; font-weight: 800; font-size: 13px; white-space: nowrap; }
        .kh-emo { font-size: 40px; line-height: 1; flex-shrink: 0; animation: khBob 3s ease-in-out infinite; }
        @keyframes khBob { 0%, 100% { transform: translateY(0) rotate(-6deg); } 50% { transform: translateY(-6px) rotate(6deg); } }
        @media (max-width: 560px) { .kh-top { flex-wrap: wrap; } .kh-top .kh-cta { margin-left: 0; width: 100%; text-align: center; } }
        .kh-rail { display: flex; gap: 10px; overflow-x: auto; padding: 14px; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .kh-rail::-webkit-scrollbar { display: none; }
        .kh-type { flex: 0 0 min(200px, 62%); scroll-snap-align: start; border-radius: 14px; overflow: hidden; text-decoration: none; background: var(--ml-chip, #f5f5f5); color: var(--ml-text, #222); transition: transform .2s; }
        .kh-type:hover { transform: translateY(-3px); }
        .kh-cover { position: relative; aspect-ratio: 16 / 10; display: flex; align-items: center; justify-content: center; font-size: 46px; overflow: hidden; }
        .kh-cover img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .kh-type b { display: block; padding: 9px 11px 2px; font-size: 13.5px; }
        .kh-type small { display: block; padding: 0 11px 10px; font-size: 12px; color: var(--ml-muted, #777); }
    </style>
    <a href="{{ $kUrl }}" class="kh-top">
        <span class="kh-emo" aria-hidden="true">🎁</span>
        <span><h2 class="sf-font">Monte seu kit de presente</h2><p>Escolha a cesta, os produtos, balão, doces e um cartão com recado.</p></span>
        <span class="kh-cta">Montar agora <i class="fas fa-arrow-right"></i></span>
    </a>
    <div class="kh-rail">
        @foreach($kTypes as $t)
            @php $lk = \App\Services\Portal\KitService::style($t->style); $im = \App\Services\Portal\KitService::imageUrl($t->image); @endphp
            <a href="{{ route('portal.kit', ['userId' => $owner, 'tipo' => $t->id]) }}" class="kh-type">
                <span class="kh-cover" style="background:linear-gradient(135deg, {{ $lk[3] }}, {{ $lk[4] }})">@if($im)<img src="{{ $im }}" alt="" loading="lazy">@else{{ $lk[1] }}@endif</span>
                <b>{{ $t->name }}</b>
                <small>{{ (float) $t->base_price > 0 ? 'Base R$ ' . number_format((float) $t->base_price, 2, ',', '.') . ' + produtos' : 'Você escolhe tudo' }}</small>
            </a>
        @endforeach
    </div>
</section>
@endif
