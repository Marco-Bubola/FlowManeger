@php
    use App\Models\Quote;
    use App\Services\Quotes\QuoteService as QS;
    $tones = \App\Models\ConsortiumNotification::TONES;
    $st = $quote->statusInfo(); $kd = $quote->kindInfo();
    $money = fn ($v) => QS::money((float) $v);
    $publicItems = $quote->publicItems();
    $profit = round($quote->subtotal() - (float) $quote->discount - (float) $quote->cost_total, 2);
    $evIcons = ['criado' => ['bi-plus-circle', 'indigo'], 'enviado' => ['bi-send', 'sky'], 'visto' => ['bi-eye', 'violet'], 'aprovado' => ['bi-hand-thumbs-up', 'emerald'],
        'recusado' => ['bi-x-circle', 'rose'], 'mudanca' => ['bi-chat-left-dots', 'amber'], 'editado' => ['bi-pencil', 'slate'], 'venda' => ['bi-cart-check', 'violet'],
        'vencido' => ['bi-hourglass-bottom', 'amber'], 'renovado' => ['bi-arrow-repeat', 'sky'], 'reaberto' => ['bi-arrow-counterclockwise', 'slate'], 'pedido' => ['bi-inbox', 'pink'], 'lembrete' => ['bi-bell', 'sky']];
@endphp
<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header :title="$quote->code() . ' · ' . $quote->displayTitle()" :subtitle="$kd['label'] . ' para ' . $quote->customerName()" :icon="$kd['icon']"
                       back-route="orcamentos.index" section="orcamentos" tabs-section="orcamentos">
            <x-slot:actions>
                <a href="{{ route('orcamentos.edit', $quote->id) }}" wire:navigate class="crm-btn" title="Editar"><i class="bi bi-pencil"></i><span class="hidden sm:inline">Editar</span></a>
                @if($wa && $quote->items)
                    <a href="{{ $wa }}" target="_blank" rel="noopener" wire:click="markSent('pelo WhatsApp')" class="crm-btn crm-btn-wa" title="Mandar pelo WhatsApp"><i class="bi bi-whatsapp"></i><span class="hidden sm:inline">WhatsApp</span></a>
                @endif
            </x-slot:actions>
        </x-page-header>

        {{-- Situação --}}
        <div class="crs-card mb-4">
            <div class="crs-head !border-0 flex-wrap">
                <span class="crs-head-icon" style="background: {{ $tones[$st['tone']] }}"><i class="bi {{ $st['icon'] }}"></i></span>
                <div class="min-w-0 flex-1">
                    <h2 class="crs-title">{{ $st['label'] }}
                        @if($quote->status === 'enviado')<span class="crs-count">{{ $quote->viewed_at ? 'visto ' . $quote->viewed_at->diffForHumans() : 'ainda não visto' }}</span>@endif
                    </h2>
                    <p class="crs-sub !whitespace-normal">
                        @switch($quote->status)
                            @case('pedido') Chegou pelo portal. Monte o custo e o preço e mande para o cliente. @break
                            @case('rascunho') Pronto para mandar? Use o WhatsApp ou copie o link. @break
                            @case('enviado') O cliente pode aprovar ou pedir mudança pelo link{{ $quote->valid_until ? ' até ' . $quote->valid_until->format('d/m/Y') : '' }}. @break
                            @case('aprovado') O cliente aprovou {{ $quote->approved_at?->timezone(QS::TZ)->format('d/m \à\s H:i') }}. Agora é só criar a venda. @break
                            @case('convertido') Virou a venda #{{ $quote->sale_id }}. @break
                            @case('recusado') Não aprovado. Dá para reabrir se o cliente mudar de ideia. @break
                            @case('vencido') Passou da validade. Renove para o cliente poder aprovar de novo. @break
                        @endswitch
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    @if(in_array($quote->status, ['aprovado', 'enviado', 'rascunho'], true))
                        <button type="button" wire:click="convert" wire:confirm="{{ $quote->status === 'aprovado' ? 'Criar a venda com estes itens e preços?' : 'O cliente confirmou? Vou criar a venda com estes itens e preços.' }}" class="crm-btn qt-btn-sale flex-1 sm:flex-none justify-center" data-testid="convert"><i class="bi bi-cart-plus"></i>{{ $quote->status === 'aprovado' ? 'Criar venda' : 'Confirmado: criar venda' }}</button>
                    @endif
                    @if($quote->status === 'enviado')
                        <button type="button" wire:click="approve" wire:confirm="O cliente aprovou por fora (WhatsApp, pessoalmente)?" class="crm-btn flex-1 sm:flex-none justify-center"><i class="bi bi-hand-thumbs-up"></i>Aprovado</button>
                    @endif
                    @if($quote->status === 'convertido' && $quote->sale_id)
                        <a href="{{ route('sales.show', $quote->sale_id) }}" wire:navigate class="crm-btn crm-btn-primary flex-1 sm:flex-none justify-center"><i class="bi bi-receipt"></i>Ver venda #{{ $quote->sale_id }}</a>
                    @endif
                    @if(in_array($quote->status, ['vencido'], true) || ($quote->isExpired() && $quote->isOpen()))
                        <button type="button" wire:click="renew" class="crm-btn crm-btn-primary flex-1 sm:flex-none justify-center"><i class="bi bi-arrow-repeat"></i>Renovar validade</button>
                    @endif
                    @if($quote->status === 'recusado')
                        <button type="button" wire:click="reopen" class="crm-btn flex-1 sm:flex-none justify-center"><i class="bi bi-arrow-counterclockwise"></i>Reabrir</button>
                    @endif
                </div>
            </div>
            @if($quote->change_request && $quote->isOpen())
                <div class="qt-alert is-warn mx-4 mb-4"><i class="bi bi-chat-left-dots mt-0.5"></i>
                    <span class="flex-1"><b>Pediu mudança:</b> {{ $quote->change_request }}</span>
                    <a href="{{ route('orcamentos.edit', $quote->id) }}" wire:navigate class="crs-link shrink-0">Mudar</a>
                </div>
            @endif
            @if($stale && $quote->isOpen())
                <div class="qt-alert is-warn mx-4 mb-4"><i class="bi bi-arrow-repeat mt-0.5"></i>
                    <span class="flex-1">Preço de material mudou na tabela depois que este custo foi montado.</span>
                    <a href="{{ route('orcamentos.edit', $quote->id) }}" wire:navigate class="crs-link shrink-0">Rever custo</a>
                </div>
            @endif
        </div>

        <div class="qt-editor">
            <div class="flex flex-col gap-4 min-w-0">
                {{-- Mandar --}}
                @if($quote->items && in_array($quote->status, ['rascunho', 'enviado', 'aprovado', 'pedido'], true))
                    <section class="crs-card" x-data="{ url: @js($quote->publicUrl()) }">
                        <div class="crs-head">
                            <span class="crs-head-icon bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-send"></i></span>
                            <div class="min-w-0 flex-1"><h2 class="crs-title">Mandar para o cliente</h2><p class="crs-sub">Ele vê os itens, fotos, preço e prazo. Nunca o custo.</p></div>
                        </div>
                        <div class="qt-sec">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                @if($wa)
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" wire:click="markSent('pelo WhatsApp')" class="crm-btn crm-btn-wa justify-center"><i class="bi bi-whatsapp"></i>WhatsApp</a>
                                @else
                                    <span class="crm-btn justify-center opacity-60" title="Sem telefone do cliente"><i class="bi bi-whatsapp"></i>Sem WhatsApp</span>
                                @endif
                                <button type="button" @click="navigator.clipboard.writeText(url); $wire.linkCopied()" class="crm-btn justify-center"><i class="bi bi-link-45deg"></i>Copiar link</button>
                                <a href="{{ $quote->publicUrl() }}?previa=1" target="_blank" class="crm-btn justify-center"><i class="bi bi-eye"></i>Ver como cliente</a>
                                <a href="{{ route('orcamentos.pdf', $quote->id) }}" target="_blank" wire:click="markSent('em PDF')" class="crm-btn justify-center"><i class="bi bi-filetype-pdf"></i>PDF</a>
                            </div>
                            @if($quote->edited_after_sent_at)
                                <p class="qt-muted"><i class="bi bi-pencil"></i> Editado depois de enviado ({{ $quote->edited_after_sent_at->timezone(QS::TZ)->format('d/m H:i') }}): o cliente vê “Orçamento atualizado”.</p>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Itens como o cliente vê --}}
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-list-check"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Itens <span class="crs-count">{{ count($publicItems) }}</span></h2><p class="crs-sub">Como aparece para o cliente</p></div>
                    </div>
                    <div class="crs-list">
                        @forelse($quote->items ?? [] as $i => $it)
                            @php $pr = QS::itemProfit($it); $photos = array_values(array_filter((array) ($it['photos'] ?? []))); @endphp
                            <div class="p-4 flex flex-col gap-3" x-data="{ open: false }" wire:key="si-{{ $i }}">
                                <div class="flex gap-3">
                                    <div class="qt-thumb qt-tone !w-16 !h-16" style="--t: {{ $tones[$kd['tone']] }}">
                                        <i class="bi {{ $kd['icon'] }}"></i>
                                        @if($photos)<img src="{{ Quote::photoUrl($photos[0]) }}" alt="" loading="lazy" onerror="this.remove()">@endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ (int) ($it['qty'] ?? 1) > 1 ? (int) $it['qty'] . '× ' : '' }}{{ $it['name'] }}</p>
                                        @php $attrs = array_filter((array) ($it['attrs'] ?? [])); @endphp
                                        @if($attrs)<p class="qt-muted">{{ implode(' · ', $attrs) }}</p>@endif
                                        @if(!empty($it['description']))<p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line">{{ $it['description'] }}</p>@endif
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-black text-slate-900 dark:text-white">{{ $money((float) ($it['price'] ?? 0) * max(1, (int) ($it['qty'] ?? 1))) }}</p>
                                        @if((int) ($it['qty'] ?? 1) > 1)<p class="qt-muted">{{ $money($it['price'] ?? 0) }} cada</p>@endif
                                    </div>
                                </div>
                                @if(count($photos) > 1)
                                    <div class="qt-photos">@foreach(array_slice($photos, 1) as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank" class="qt-photo !w-14 !h-14"><img src="{{ Quote::photoUrl($ph) }}" alt="" loading="lazy"></a>@endforeach</div>
                                @endif
                                <button type="button" @click="open = !open" class="self-start flex items-center gap-2 text-xs font-bold text-slate-500">
                                    <span class="qt-private"><i class="bi bi-eye-slash"></i>só você vê</span>
                                    Custo {{ $money($pr['cost']) }} · lucro <span class="{{ $pr['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $money($pr['profit']) }} ({{ number_format($pr['pct'], 0) }}%)</span>
                                    <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                </button>
                                <div x-show="open" x-cloak class="qt-cost">
                                    @foreach($it['costs'] ?? [] as $c)
                                        <div class="qt-cost-row text-sm"><span class="truncate">{{ $c['n'] }}</span><span class="text-right qt-muted">{{ rtrim(rtrim(number_format((float) $c['q'], 2, ',', ''), '0'), ',') }} {{ $c['u'] ?? '' }}</span><span class="text-right qt-muted">{{ $money($c['v']) }}</span><span class="qt-line-total text-right font-bold">{{ $money((float) $c['q'] * (float) $c['v']) }}</span><span></span></div>
                                    @endforeach
                                    @foreach($it['extras'] ?? [] as $e)
                                        <div class="qt-cost-row text-sm"><span class="truncate"><i class="bi bi-plus"></i>{{ $e['n'] }} (no preço)</span><span></span><span></span><span class="qt-line-total text-right font-bold">+{{ $money($e['v']) }}</span><span></span></div>
                                    @endforeach
                                    @if(empty($it['costs']))<p class="p-3 qt-muted">Sem ficha de custo.</p>@endif
                                </div>
                            </div>
                        @empty
                            <div class="crs-empty"><i class="bi bi-calculator"></i>Ainda sem itens. <a href="{{ route('orcamentos.edit', $quote->id) }}" wire:navigate class="crs-link">Montar o orçamento</a></div>
                        @endforelse
                    </div>
                </section>

                @if($quote->request_text || $quote->request_photos)
                    <section class="crs-card">
                        <div class="crs-head">
                            <span class="crs-head-icon bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-inbox"></i></span>
                            <div class="min-w-0 flex-1"><h2 class="crs-title">O que o cliente pediu</h2></div>
                        </div>
                        <div class="qt-sec">
                            @if($quote->request_text)<p class="text-sm whitespace-pre-line text-slate-700 dark:text-slate-200">{{ $quote->request_text }}</p>@endif
                            @if(!empty($quote->request_data['cores']))
                                <div class="crm-chips">@foreach((array) $quote->request_data['cores'] as $g => $c)<span class="crm-chip is-on"><i class="bi bi-palette"></i>{{ is_int($g) ? '' : $g . ': ' }}{{ $c }}</span>@endforeach</div>
                            @endif
                            @if(!empty($quote->request_data['cor_texto']))<p class="text-sm text-slate-600 dark:text-slate-300"><b>Cor:</b> {{ $quote->request_data['cor_texto'] }}</p>@endif
                            @if(!empty($quote->request_data['medidas']))<p class="text-sm text-slate-600 dark:text-slate-300"><b>Medidas:</b> {{ $quote->request_data['medidas'] }}</p>@endif
                            @if($quote->request_photos)
                                <div class="qt-photos">@foreach($quote->request_photos as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank" class="qt-photo"><img src="{{ Quote::photoUrl($ph) }}" alt="" loading="lazy"></a>@endforeach</div>
                            @endif
                        </div>
                    </section>
                @endif

                @if($quote->kind === 'reforma' && ($quote->services || $quote->after_photos))
                    <section class="crs-card">
                        <div class="crs-head">
                            <span class="crs-head-icon bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-tools"></i></span>
                            <div class="min-w-0 flex-1"><h2 class="crs-title">Reforma</h2><p class="crs-sub">{{ $quote->pickup === 'busco' ? 'Você busca a peça' : ($quote->pickup === 'cliente_leva' ? 'O cliente leva a peça' : '') }}</p></div>
                        </div>
                        <div class="qt-sec">
                            @if($quote->services)<div class="crm-chips">@foreach($quote->services as $s)<span class="crm-chip is-on"><i class="bi bi-check2"></i>{{ $s }}</span>@endforeach</div>@endif
                            @if($quote->after_photos)
                                <p class="crm-label">Fotos de depois</p>
                                <div class="qt-photos">@foreach($quote->after_photos as $ph)<a href="{{ Quote::photoUrl($ph) }}" target="_blank" class="qt-photo"><img src="{{ Quote::photoUrl($ph) }}" alt="" loading="lazy"></a>@endforeach</div>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            <aside class="qt-side flex flex-col gap-4">
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-receipt"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Resumo</h2></div>
                    </div>
                    <div class="qt-sec !gap-2">
                        <div class="qt-sum-row"><span>Itens</span><b>{{ $money($quote->subtotal()) }}</b></div>
                        @if($quote->discount > 0)<div class="qt-sum-row"><span>Desconto</span><b class="!text-emerald-600">− {{ $money($quote->discount) }}</b></div>@endif
                        @if($quote->delivery_fee > 0)<div class="qt-sum-row"><span>Entrega</span><b>{{ $money($quote->delivery_fee) }}</b></div>@endif
                        <div class="flex items-end justify-between pt-2 border-t border-slate-100 dark:border-slate-800"><span class="text-sm font-bold text-slate-500">Total</span><span class="qt-sum-total">{{ $money($quote->total) }}</span></div>
                        <div class="qt-sum-row"><span>Prazo</span><b>{{ $quote->production_days ? $quote->production_days . ' dias' : '—' }}</b></div>
                        <div class="qt-sum-row"><span>Válido até</span><b class="{{ $quote->isExpired() ? '!text-rose-600' : '' }}">{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</b></div>
                        @if($quote->payment_terms)<div class="qt-sum-row"><span>Pagamento</span><b class="text-right">{{ $quote->payment_terms }}</b></div>@endif
                        <div class="mt-2 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 p-3 flex flex-col gap-1.5">
                            <span class="qt-private self-start"><i class="bi bi-eye-slash"></i>só você vê</span>
                            <div class="qt-sum-row"><span>Custo total</span><b>{{ $money($quote->cost_total) }}</b></div>
                            <div class="qt-sum-row"><span>Lucro</span><b class="{{ $profit >= 0 ? '!text-emerald-600' : '!text-rose-600' }}">{{ $money($profit) }}</b></div>
                            @if($quote->admin_notes)<p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line pt-1 border-t border-rose-100 dark:border-rose-900/50">{{ $quote->admin_notes }}</p>@endif
                        </div>
                        @if($quote->client)
                            <a href="{{ route('clients.dashboard', $quote->client_id) }}" wire:navigate class="crs-link self-start mt-1"><i class="bi bi-person"></i>{{ $quote->client->name }}</a>
                        @elseif($quote->contact_name)
                            <p class="qt-muted mt-1"><i class="bi bi-person"></i> {{ $quote->contact_name }}{{ $quote->contact_phone ? ' · ' . $quote->contact_phone : '' }} (vira cliente na venda)</p>
                        @endif
                    </div>
                </section>

                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-slate-500 to-slate-700"><i class="bi bi-clock-history"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Linha do tempo</h2></div>
                    </div>
                    <div class="qt-sec qt-tl">
                        @forelse(array_reverse($quote->events ?? []) as $ev)
                            @php [$ic, $tn] = $evIcons[$ev['t']] ?? ['bi-dot', 'slate']; @endphp
                            <div class="qt-tl-item">
                                <span class="qt-tl-dot qt-tone" style="--t: {{ $tones[$tn] }}"><i class="bi {{ $ic }}"></i></span>
                                <div class="min-w-0"><p class="font-semibold">{{ $ev['txt'] }}</p><p class="qt-muted">{{ \Illuminate\Support\Carbon::parse($ev['at'])->timezone(QS::TZ)->format('d/m/Y H:i') }}</p></div>
                            </div>
                        @empty
                            <p class="qt-muted">Nada ainda.</p>
                        @endforelse
                    </div>
                </section>

                <section class="crs-card" x-data="{ rej: false }">
                    <div class="qt-sec !gap-2">
                        <a href="{{ route('orcamentos.edit', $quote->id) }}" wire:navigate class="crm-btn justify-center"><i class="bi bi-pencil"></i>Editar orçamento</a>
                        @if($quote->isOpen())
                            <button type="button" @click="rej = !rej" class="crm-btn justify-center"><i class="bi bi-x-circle"></i>O cliente não quis</button>
                            <div x-show="rej" x-cloak class="flex flex-col gap-2">
                                <input type="text" wire:model="rejectReason" class="crm-input" placeholder="Motivo (opcional): achou caro, desistiu..." maxlength="300">
                                <button type="button" wire:click="reject" class="crm-btn justify-center !text-rose-600">Marcar como recusado</button>
                            </div>
                        @endif
                        <button type="button" wire:click="delete" wire:confirm="Apagar {{ $quote->code() }}? O link do cliente para de funcionar." class="crm-btn justify-center !text-rose-600"><i class="bi bi-trash3"></i>Apagar</button>
                    </div>
                </section>
            </aside>
        </div>
    </div>
    <x-quotes-css />
</div>
