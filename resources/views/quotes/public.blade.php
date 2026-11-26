{{-- Orçamento como o cliente vê (link sem login). Nada de custo aqui: só $items (Quote::publicItems). --}}
@php
    use App\Models\Quote;
    use App\Services\Quotes\QuoteService as QS;
    $money = fn ($v) => QS::money((float) $v);
    $canAnswer = in_array($quote->status, ['enviado'], true) && !$quote->isExpired();
    $first = trim(explode(' ', $quote->customerName())[0] ?? '');
    $storeWa = $identity['wa_link'] ?? null;
    $waText = 'Olá! Sobre o orçamento ' . $quote->code() . ': ';
    $storeWaUrl = $storeWa ? $storeWa . (str_contains($storeWa, '?') ? '&' : '?') . 'text=' . rawurlencode($waText) : null;
    $subtotal = collect($items)->sum('line');
    $kindIcon = $quote->kind === 'reforma' ? 'fa-screwdriver-wrench' : 'fa-ruler-combined';
@endphp
<x-portal-catalog-layout :title="'Orçamento ' . $quote->code()" :owner-id="$ownerId">
    @push('styles')
        @include('portal.partials.custom-quote-styles')
        <meta name="robots" content="noindex">
    @endpush

    <div class="cq-page" x-data="{ change: {{ $errors->has('texto') ? 'true' : 'false' }}, no: false }">
        @if($isOwner && request()->boolean('previa'))
            <div class="cq-flash info"><i class="fas fa-eye"></i><span>Prévia: é assim que o cliente vê. Custo e anotações não aparecem.</span></div>
        @endif
        @if(session('success'))<div class="cq-flash ok"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div class="cq-flash bad"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>@endif

        <section class="cq-card">
            <div class="cq-hero">
                <span class="cq-code">Orçamento {{ $quote->code() }}</span>
                <h1>{{ $quote->displayTitle() }}</h1>
                <p>{{ $first && $first !== 'Sem' ? 'Para ' . $quote->customerName() . ' · ' : '' }}{{ $identity['name'] }}</p>
                <div class="cq-badges">
                    <span class="cq-badge"><i class="fas {{ $kindIcon }}"></i>{{ $quote->kindInfo()['label'] }}</span>
                    @if($quote->status === 'aprovado' || $quote->status === 'convertido')
                        <span class="cq-badge" style="background:#16a34a"><i class="fas fa-circle-check"></i>Aprovado</span>
                    @elseif($quote->status === 'recusado')
                        <span class="cq-badge"><i class="fas fa-circle-xmark"></i>Não aprovado</span>
                    @elseif($quote->isExpired() || $quote->status === 'vencido')
                        <span class="cq-badge" style="background:#b45309"><i class="fas fa-hourglass-end"></i>Venceu em {{ $quote->valid_until?->format('d/m/Y') }}</span>
                    @elseif($quote->valid_until)
                        <span class="cq-badge"><i class="fas fa-calendar"></i>Válido até {{ $quote->valid_until->format('d/m/Y') }}</span>
                    @endif
                    @if($quote->edited_after_sent_at)
                        <span class="cq-badge" style="background:#f59e0b"><i class="fas fa-pen"></i>Orçamento atualizado em {{ $quote->edited_after_sent_at->timezone(QS::TZ)->format('d/m') }}</span>
                    @endif
                </div>
            </div>

            @foreach($items as $it)
                <div class="cq-item">
                    <a class="cq-ph" href="{{ $it['photos'] ? Quote::photoUrl($it['photos'][0]) : '#' }}" @if($it['photos']) target="_blank" @endif>
                        <i class="fas {{ $kindIcon }}"></i>
                        @if($it['photos'])<img src="{{ Quote::photoUrl($it['photos'][0]) }}" alt="{{ $it['name'] }}" loading="lazy" onerror="this.remove()">@endif
                    </a>
                    <div style="min-width:0">
                        <p class="cq-name">{{ $it['qty'] > 1 ? $it['qty'] . '× ' : '' }}{{ $it['name'] }}</p>
                        @if($it['attrs'])
                            <div class="cq-attrs">
                                @foreach($it['attrs'] as $k => $v)<span class="cq-chip"><i class="fas {{ $k === 'cor' ? 'fa-palette' : 'fa-ruler' }}"></i>{{ $v }}</span>@endforeach
                            </div>
                        @endif
                        @if($it['description'] !== '')<p class="cq-desc">{{ $it['description'] }}</p>@endif
                        @if(count($it['photos']) > 1)
                            <div class="cq-thumbs">@foreach(array_slice($it['photos'], 1, 7) as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank"><img src="{{ Quote::photoUrl($ph) }}" alt="" loading="lazy"></a>@endforeach</div>
                        @endif
                        <div class="cq-price">
                            <span>{{ $it['qty'] > 1 ? $money($it['price']) . ' cada' : '' }}</span>
                            <b>{{ $money($it['line']) }}</b>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        @if($quote->kind === 'reforma' && ($quote->services || $quote->request_photos || $quote->after_photos))
            <section class="cq-card cq-pad">
                <h2 class="cq-h2"><i class="fas fa-screwdriver-wrench"></i>A reforma</h2>
                @if($quote->services)
                    <div class="cq-attrs">@foreach($quote->services as $s)<span class="cq-chip"><i class="fas fa-check"></i>{{ $s }}</span>@endforeach</div>
                @endif
                @if($quote->pickup)<p class="cq-muted" style="margin:.4rem 0 0">{{ $quote->pickup === 'busco' ? 'A loja busca a peça com você.' : 'Você leva a peça até a loja.' }}</p>@endif
                @if($quote->request_photos)
                    <p class="cq-muted" style="margin:.8rem 0 .3rem;font-weight:800">Antes</p>
                    <div class="cq-thumbs">@foreach($quote->request_photos as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank"><img src="{{ Quote::photoUrl($ph) }}" alt="Antes" loading="lazy"></a>@endforeach</div>
                @endif
                @if($quote->after_photos)
                    <p class="cq-muted" style="margin:.8rem 0 .3rem;font-weight:800">Depois</p>
                    <div class="cq-thumbs">@foreach($quote->after_photos as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank" style="width:84px;height:84px"><img src="{{ Quote::photoUrl($ph) }}" alt="Depois" loading="lazy"></a>@endforeach</div>
                @endif
            </section>
        @endif

        <section class="cq-card cq-pad">
            <div class="cq-row"><span>Itens</span><b>{{ $money($subtotal) }}</b></div>
            @if($quote->discount > 0)<div class="cq-row"><span>Desconto</span><b style="color:#16a34a">− {{ $money($quote->discount) }}</b></div>@endif
            @if($quote->delivery_fee > 0)<div class="cq-row"><span>Entrega</span><b>{{ $money($quote->delivery_fee) }}</b></div>@endif
            <div class="cq-total"><span>Total</span><b>{{ $money($quote->total) }}</b></div>
        </section>

        <div class="cq-info">
            <div><small>Fica pronto em</small><strong>{{ $quote->production_days ? $quote->production_days . ' dias' : 'A combinar' }}</strong></div>
            <div><small>Pagamento</small><strong>{{ $quote->payment_terms ?: 'A combinar com a loja' }}</strong></div>
            @if($quote->valid_until)<div><small>Válido até</small><strong>{{ $quote->valid_until->format('d/m/Y') }}</strong></div>@endif
        </div>

        @if($quote->client_notes || $footer)
            <section class="cq-card cq-pad">
                @if($quote->client_notes)<p class="cq-desc" style="margin:0 0 .6rem">{{ $quote->client_notes }}</p>@endif
                @if($footer)<p class="cq-muted" style="margin:0;white-space:pre-line">{{ $footer }}</p>@endif
            </section>
        @endif

        {{-- Resposta do cliente --}}
        @if($canAnswer)
            <section class="cq-card cq-pad">
                <h2 class="cq-h2"><i class="fas fa-handshake"></i>Gostou?</h2>
                <form method="POST" action="{{ route('orcamento.public.respond', $quote->token) }}" class="cq-actions">
                    @csrf
                    <input type="hidden" name="acao" value="aprovar">
                    <button type="submit" class="cq-btn cq-btn-primary"><i class="fas fa-circle-check"></i>Aprovar orçamento</button>
                </form>
                <div class="cq-actions two" style="margin-top:.6rem">
                    <button type="button" class="cq-btn cq-btn-soft" @click="change = !change; no = false"><i class="fas fa-pen"></i>Pedir mudança</button>
                    @if($storeWaUrl)<a href="{{ $storeWaUrl }}" target="_blank" rel="noopener" class="cq-btn cq-btn-wa"><i class="fab fa-whatsapp"></i>Tirar dúvida</a>@endif
                </div>
                <form x-show="change" x-cloak method="POST" action="{{ route('orcamento.public.respond', $quote->token) }}" style="margin-top:.8rem;display:flex;flex-direction:column;gap:.6rem">
                    @csrf
                    <input type="hidden" name="acao" value="mudanca">
                    <label class="cq-label">O que você quer mudar?
                        <textarea name="texto" class="cq-input" maxlength="1000" placeholder="Ex.: Prefiro o assento caramelo e 5 cm mais alta">{{ old('texto') }}</textarea>
                        @error('texto')<span class="cq-err">{{ $message }}</span>@enderror
                    </label>
                    <button type="submit" class="cq-btn cq-btn-primary"><i class="fas fa-paper-plane"></i>Mandar para a loja</button>
                </form>
                <p style="margin:.9rem 0 0;text-align:center"><button type="button" @click="no = !no; change = false" style="background:none;border:0;color:var(--ml-muted,#888);font-size:.82rem;text-decoration:underline;cursor:pointer">Não vou querer</button></p>
                <form x-show="no" x-cloak method="POST" action="{{ route('orcamento.public.respond', $quote->token) }}" style="margin-top:.5rem;display:flex;flex-direction:column;gap:.6rem">
                    @csrf
                    <input type="hidden" name="acao" value="recusar">
                    <input type="text" name="texto" class="cq-input" maxlength="300" placeholder="Se quiser, conte o motivo (ajuda a loja)">
                    <button type="submit" class="cq-btn cq-btn-soft">Avisar a loja</button>
                </form>
            </section>
        @elseif(in_array($quote->status, ['aprovado', 'convertido'], true))
            <div class="cq-flash ok"><i class="fas fa-circle-check"></i><span>Você aprovou este orçamento{{ $quote->approved_at ? ' em ' . $quote->approved_at->timezone(QS::TZ)->format('d/m/Y') : '' }}. A loja vai combinar com você o pagamento e a entrega.</span></div>
        @elseif($quote->isExpired() || $quote->status === 'vencido')
            <div class="cq-flash bad"><i class="fas fa-hourglass-end"></i><span>Este orçamento venceu. Chame a loja para fazer um novo com os preços de hoje.</span></div>
        @endif

        <div class="cq-actions two">
            <a href="{{ route('orcamento.public.pdf', $quote->token) }}" target="_blank" class="cq-btn cq-btn-soft"><i class="fas fa-file-pdf"></i>Baixar PDF</a>
            @if($storeWaUrl && !$canAnswer)<a href="{{ $storeWaUrl }}" target="_blank" rel="noopener" class="cq-btn cq-btn-wa"><i class="fab fa-whatsapp"></i>Falar com a loja</a>@endif
        </div>
    </div>
</x-portal-catalog-layout>
