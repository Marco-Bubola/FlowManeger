@php
    [$ptitle, $picon] = $pageInfo;
    $others = collect(\App\Services\Portal\StoreIdentity::footerPages($identity))->except($page);
    $legalLine = collect([$identity['legal_name'] ?: null, $identity['document_text'] ?? null, $identity['address'] ?: null])->filter()->implode(' · ');
@endphp
<x-portal-catalog-layout :title="$ptitle" :store="$store" :owner-id="$ownerId">
    <style>
        .sp-wrap { max-width: 760px; margin: 0 auto; padding: 18px 12px 30px; }
        .sp-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--ml-blue); text-decoration: none; margin-bottom: 12px; }
        .sp-card { background: #fff; border-radius: var(--ml-radius); box-shadow: 0 1px 2px rgba(0,0,0,.08); padding: 22px 20px; }
        .sp-card h1 { display: flex; align-items: center; gap: 10px; margin: 0 0 14px; font-size: 22px; }
        .sp-card h1 i { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: var(--ml-soft); color: var(--ml-primary); font-size: 17px; }
        .sp-text { font-size: 15px; line-height: 1.65; color: #333; white-space: pre-line; overflow-wrap: anywhere; }
        .sp-more { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .sp-more a { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.08); color: #333; text-decoration: none; font-size: 13px; font-weight: 600; }
        .sp-wa { display: inline-flex; align-items: center; gap: 8px; margin-top: 18px; padding: 11px 18px; border-radius: 10px; background: #25d366; color: #fff; font-weight: 700; text-decoration: none; }
    </style>
    <div class="sp-wrap" data-testid="store-page">
        <a href="{{ route('portal.catalog', ['userId' => $ownerId]) }}" class="sp-back"><i class="fas fa-arrow-left"></i> Voltar ao catálogo</a>
        <article class="sp-card">
            <h1 class="sf-font"><i class="fas {{ $picon }}"></i>{{ $ptitle }}</h1>
            <div class="sp-text">{{ $text }}</div>
            @if($legalLine)<p style="margin:16px 0 0;font-size:12px;color:var(--ml-muted,#888)">{{ $legalLine }}</p>@endif
            @if($identity['wa_link'])
                <a href="{{ $identity['wa_link'] }}" target="_blank" rel="noopener" class="sp-wa"><i class="fab fa-whatsapp"></i> Falar no WhatsApp</a>
            @endif
        </article>
        @if($others->isNotEmpty())
            <div class="sp-more">
                @foreach($others as $k => [$t, $ic])
                    <a href="{{ route('portal.store-page', ['userId' => $ownerId, 'page' => $k]) }}"><i class="fas {{ $ic }}"></i>{{ $t }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-portal-catalog-layout>
