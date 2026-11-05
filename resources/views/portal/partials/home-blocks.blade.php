{{-- Blocos extras da página inicial (Vitrine › Página inicial › O que aparece e em que ordem) --}}
@php $o = $hs[$k] ?? []; $cls = $HL::secClass($hs, $k); @endphp
@if($k === 'novos' || $k === 'vendidos')
    @if(($extra[$k] ?? collect())->isNotEmpty())
        <section class="ml-box ml-offers {{ $cls }}" data-testid="home-{{ $k }}">
            <div class="ml-sec-head">
                <h2><i class="fas {{ $k === 'novos' ? 'fa-star' : 'fa-chart-line' }}"></i> {{ $HL::secTitle($hs, $k) }}</h2>
                <a href="{{ route('portal.catalog', ['userId' => $owner, 'ordem' => $k === 'novos' ? 'novos' : 'vendidos']) }}#todos">Ver todos</a>
            </div>
            @include('portal.partials.home-sec-sub', ['k' => $k])
            @include('portal.partials.home-products', ['list' => $extra[$k], 'k' => $k])
        </section>
    @endif
@elseif($k === 'benefits')
    <section class="sf-benefits {{ $cls }}" style="--n: {{ max(1, count($o['items'] ?? [])) }}" data-testid="home-benefits">
        @foreach($o['items'] ?? [] as $it)
            <div class="sf-benefit">
                <span class="sf-benefit-ico"><i class="{{ $HL::BENEFIT_ICONS[$it['ico']][1] ?? 'fas fa-star' }}"></i></span>
                <span><b>{{ $it['title'] }}</b>@if($it['text'] !== '')<small>{{ $it['text'] }}</small>@endif</span>
            </div>
        @endforeach
    </section>
@elseif($k === 'reviews')
    @php
        $revs = \App\Services\Portal\ReviewService::ready()
            ? \Illuminate\Support\Facades\Cache::remember("portal-home-reviews-{$owner}-" . ($o['min'] ?? 4) . '-' . ($o['limit'] ?? 6), now()->addMinutes(30), fn () => \App\Models\PortalReview::where('user_id', $owner)->where('hidden', false)
                ->where('rating', '>=', $o['min'] ?? 4)->whereNotNull('comment')->where('comment', '!=', '')->latest()->limit($o['limit'] ?? 6)->get(['name', 'rating', 'comment', 'verified', 'created_at']))
            : collect();
    @endphp
    @if($revs->isNotEmpty())
        <section class="ml-box {{ $cls }}" data-testid="home-reviews">
            <div class="ml-sec-head"><h2><i class="fas fa-comment-dots"></i> {{ $HL::secTitle($hs, 'reviews') }}</h2></div>
            @include('portal.partials.home-sec-sub', ['k' => 'reviews'])
            <div class="ml-rail sf-revs">
                @foreach($revs as $r)
                    <figure class="sf-rev">
                        <span class="sf-rev-stars" aria-label="{{ $r->rating }} de 5">{{ str_repeat('★', (int) $r->rating) }}<i>{{ str_repeat('★', 5 - (int) $r->rating) }}</i></span>
                        <blockquote>“{{ \Illuminate\Support\Str::limit($r->comment, 180) }}”</blockquote>
                        <figcaption>{{ \Illuminate\Support\Str::of($r->name ?: 'Cliente')->explode(' ')->first() }}@if($r->verified) <span><i class="fas fa-check-circle"></i> comprou na loja</span>@endif</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
@elseif($k === 'whatsapp')
    @php $wa = \App\Services\Portal\StoreIdentity::for($owner)['wa_link'] ?? null; @endphp
    @if($wa)
        <section class="sf-wa {{ $cls }}" data-testid="home-whatsapp">
            <span class="sf-wa-ico"><i class="fab fa-whatsapp"></i></span>
            <div class="sf-wa-txt">
                <h2>{{ $HL::secTitle($hs, 'whatsapp') }}</h2>
                <p>{{ ($o['text'] ?? '') !== '' ? $o['text'] : (($o['sub'] ?? '') !== '' ? $o['sub'] : 'Chama a gente no WhatsApp que a gente te ajuda a escolher.') }}</p>
            </div>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="sf-wa-btn">{{ ($o['button'] ?? '') !== '' ? $o['button'] : 'Falar no WhatsApp' }}</a>
        </section>
    @endif
@elseif($k === 'texto')
    @if(($o['title'] ?? '') !== '' || ($o['text'] ?? '') !== '')
        <section class="ml-box sf-text {{ ($o['align'] ?? 'centro') === 'esquerda' ? 'left' : '' }} {{ $cls }}" data-testid="home-texto">
            @if(($o['title'] ?? '') !== '')<h2>{{ $o['title'] }}</h2>@endif
            @if(($o['sub'] ?? '') !== '')<p class="sf-text-sub">{{ $o['sub'] }}</p>@endif
            @if(($o['text'] ?? '') !== '')<p>{!! nl2br(e($o['text'])) !!}</p>@endif
        </section>
    @endif
@endif
