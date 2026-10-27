<div class="po-page w-full space-y-4 sm:space-y-5" x-data="{ confirm: null }">
    <x-page-header title="Pedidos do portal" subtitle="O que os clientes pediram pelo catálogo" icon="bi-bag-heart" section="vendas" tabs-section="vendas" tabs-active="portal">
        <x-slot:meta>
            @if ($stats['new'] > 0)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-bell"></i>{{ $stats['new'] }} {{ $stats['new'] === 1 ? 'novo' : 'novos' }}</span>
            @endif
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-inbox"></i>{{ $counts['todos'] }} pedidos</span>
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" title="Copiar o link do catálogo para mandar aos clientes"
                    x-on:click="navigator.clipboard?.writeText(@js($catalogUrl)); $dispatch('notify', { type: 'success', message: 'Link do catálogo copiado.' })"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-link-45deg text-indigo-500"></i><span class="app-ph-label">Copiar link</span></button>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" title="Abrir o catálogo como o cliente vê"
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition"><i class="bi bi-shop"></i><span class="app-ph-label">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <x-gestao-stat label="Novos" :value="(string) $stats['new']" icon="bi-bell" tone="amber" hint="esperando sua resposta" />
        <x-gestao-stat label="Em aberto" :value="'R$ ' . number_format($stats['openValue'], 2, ',', '.')" icon="bi-hourglass-split" tone="indigo"
            :hint="$counts['abertos'] . ' ' . ($counts['abertos'] === 1 ? 'pedido' : 'pedidos')" />
        <x-gestao-stat label="Pagos pelo site" :value="(string) $counts['pagos']" icon="bi-patch-check" tone="emerald"
            :hint="$stats['paidWaiting'] ? $stats['paidWaiting'] . ' para confirmar' : 'Pix, cartão ou boleto'" />
        <x-gestao-stat label="Confirmados no mês" :value="'R$ ' . number_format($stats['monthValue'], 2, ',', '.')" icon="bi-check2-circle" tone="sky"
            :hint="$stats['monthConfirmed'] . ' ' . ($stats['monthConfirmed'] === 1 ? 'venda' : 'vendas')" />
    </div>

    <x-list-toolbar model="search" placeholder="Buscar cliente, telefone, produto ou nº do pedido...">
        <x-toolbar.group icon="bi-funnel" title="Mostrar">
            @foreach ($this::TABS as $key => [$label, $icon])
                <x-toolbar.chip wire:click="$set('tab', '{{ $key }}')" :active="$tab === $key && ! $focus" :icon="$icon">
                    {{ $label }}@if ($counts[$key] > 0)<span class="po-count">{{ $counts[$key] }}</span>@endif
                </x-toolbar.chip>
            @endforeach
        </x-toolbar.group>
        <x-toolbar.group icon="bi-arrow-down-up" title="Ordenar">
            @foreach (['recentes' => 'Recentes', 'antigos' => 'Antigos', 'valor' => 'Maior valor'] as $key => $label)
                <x-toolbar.chip wire:click="$set('sort', '{{ $key }}')" :active="$sort === $key">{{ $label }}</x-toolbar.chip>
            @endforeach
        </x-toolbar.group>
    </x-list-toolbar>

    @if (session('success'))
        <div class="po-flash"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div class="po-flash is-error"><i class="bi bi-exclamation-circle-fill"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Depois de confirmar ou recusar: avisar o cliente no WhatsApp em um clique --}}
    @php $n = $notify ?? session('portal_notify'); @endphp
    @if ($n)
        @php $ok = ($n['status'] ?? '') === 'approved'; @endphp
        <div class="po-notify {{ $ok ? '' : 'is-rose' }}" data-testid="portal-notify">
            <div class="po-notify-icon"><i class="bi bi-whatsapp"></i></div>
            <div class="min-w-0 flex-1">
                <p class="po-notify-title">{{ ! empty($n['title']) ? $n['title'] . '. Avise ' . ($n['client'] ?? 'o cliente') : 'Avise ' . ($n['client'] ?? 'o cliente') . ' que o pedido #' . $n['quote_id'] . ' foi ' . ($ok ? 'confirmado' : 'recusado') }}</p>
                <p class="po-dim">
                    O cliente também vê o novo status em "Meus pedidos" no portal.
                    @if (! empty($n['mailed'])) E-mail enviado. @endif
                    @if (empty($n['has_phone'])) <b class="text-amber-600 dark:text-amber-400">Cliente sem celular válido: escolha o contato no WhatsApp.</b> @endif
                </p>
                <details class="mt-1"><summary class="po-link">Ver mensagem</summary><pre class="po-msg">{{ $n['message'] }}</pre></details>
            </div>
            <div class="po-notify-actions">
                @if (! empty($n['url']))<a href="{{ $n['url'] }}" target="_blank" rel="noopener" class="po-btn is-wa" data-testid="notify-wa"><i class="bi bi-whatsapp"></i>Avisar no WhatsApp</a>@endif
                @if (! empty($n['sale_id']))
                    <a href="{{ route('sales.show', $n['sale_id']) }}" class="po-btn"><i class="bi bi-receipt"></i>Venda #{{ $n['sale_id'] }}</a>
                @endif
                @if ($notify)
                    <button type="button" wire:click="closeNotify" class="po-btn is-ghost" title="Fechar"><i class="bi bi-x-lg"></i></button>
                @endif
            </div>
        </div>
    @endif

    @if ($focus)
        <div class="po-summary">
            <span>Mostrando só o pedido <strong>#{{ $focus }}</strong></span>
            <button type="button" wire:click="$set('focus', null)" class="po-link"><i class="bi bi-arrow-left"></i>Ver todos</button>
        </div>
    @endif

    @if ($rows->isEmpty())
        <x-empty-state icon="bi-bag-heart"
            :title="$filtersOn ? 'Nenhum pedido com essa busca' : ($tab === 'abertos' ? 'Nenhum pedido esperando' : 'Nenhum pedido aqui')"
            :text="$filtersOn ? 'Tente outro nome ou limpe a busca.' : 'Quando um cliente fizer um pedido pelo catálogo, ele aparece aqui. Mande o link do catálogo para os clientes.'">
            @if ($filtersOn)
                <button type="button" wire:click="clearFilters" class="po-btn"><i class="bi bi-x-circle"></i>Limpar busca</button>
            @else
                <button type="button" x-on:click="navigator.clipboard?.writeText(@js($catalogUrl)); $dispatch('notify', { type: 'success', message: 'Link do catálogo copiado.' })" class="po-btn is-primary"><i class="bi bi-link-45deg"></i>Copiar link do catálogo</button>
            @endif
        </x-empty-state>
    @else
        <div class="po-grid">
            @foreach ($rows as $r)
                @php
                    $q = $r->q;
                    [$stLabel, $stTone, $stIcon] = $this::STATUS[$q->status] ?? ['?', 'slate', 'bi-question'];
                    $client = $q->client;
                    $first = $client?->name ? explode(' ', trim($client->name))[0] : '';
                    $canConfirm = in_array($q->status, ['pending', 'reviewing', 'quoted'], true) || ($q->status === 'approved' && ! $q->sale);
                    $open = in_array($q->status, ['pending', 'reviewing', 'quoted'], true);
                    $prefMap = ['credito' => 'cartao_credito', 'debito' => 'cartao_debito'];
                    $payload = ['id' => $q->id, 'client' => $client?->name, 'total' => number_format($r->total, 2, '.', ''), 'items' => count($r->items),
                        'method' => $prefMap[$q->payment_preference] ?? ($q->payment_preference ?: 'pix'), 'action' => route('clients.portal.quotes.confirm', $q)];
                    $hello = 'Oi' . ($first ? ', ' . $first : '') . '! Recebi seu pedido #' . $q->id . ' pelo catálogo'
                        . ($r->total > 0 ? ' (' . $orders->money($r->total) . ')' : '') . '. ';
                    $wa = $r->phone ? 'https://wa.me/' . $r->phone . '?text=' . rawurlencode($hello) : null;
                @endphp
                <article class="po-card is-{{ $stTone }}" wire:key="po-{{ $q->id }}" x-data="{ more: {{ $focus ? 'true' : 'false' }} }">
                    <div class="po-top">
                        <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-2xl" />
                        <div class="min-w-0 flex-1">
                            <p class="po-name truncate">{{ $client->name ?? 'Cliente' }}</p>
                            <p class="po-dim truncate">Pedido #{{ $q->id }} · {{ $q->created_at->diffForHumans() }} · {{ $q->created_at->format('d/m H:i') }}</p>
                        </div>
                        <div class="po-money">
                            <strong>{{ $r->total > 0 ? $orders->money($r->total) : 'A definir' }}</strong>
                            <span class="po-dim">{{ $r->units }} {{ $r->units === 1 ? 'item' : 'itens' }}{{ $r->estimated && $r->total > 0 ? ' · estimado' : '' }}</span>
                        </div>
                    </div>

                    <div class="po-tags">
                        <span class="po-tag is-{{ $stTone }}"><i class="bi {{ $stIcon }}"></i>{{ $stLabel }}</span>
                        @if ($q->delivery_stage && isset($orders::DELIVERY_STAGES[$q->delivery_stage]))
                            <span class="po-tag is-{{ $q->delivery_stage === 'entregue' ? 'emerald' : 'violet' }}" data-testid="delivery-tag"><i class="bi {{ $orders::DELIVERY_STAGES[$q->delivery_stage][2] }}"></i>{{ $orders::DELIVERY_STAGES[$q->delivery_stage][0] }}{{ $q->tracking_code ? ' · ' . \Illuminate\Support\Str::limit($q->tracking_code, 18) : '' }}</span>
                        @endif
                        @if ($q->payment_status === 'approved')
                            <span class="po-tag is-emerald" data-testid="paid-pix"><i class="bi bi-patch-check-fill"></i>Pago pelo site{{ $r->lastPayment ? ' · ' . $r->lastPayment->methodLabel() : '' }}</span>
                        @elseif ($q->payment_status === 'refunded')
                            <span class="po-tag is-rose"><i class="bi bi-arrow-counterclockwise"></i>Pagamento devolvido</span>
                        @elseif (in_array($q->payment_status, ['pending', 'expired', 'cancelled', 'rejected'], true) && ! $q->sale)
                            <span class="po-tag is-amber"><i class="bi bi-hourglass-split"></i>{{ $q->payment_status === 'pending' ? (($r->lastPayment?->method === 'boleto') ? 'Boleto gerado' : (($r->lastPayment?->method === 'cartao') ? 'Cartão em análise' : 'Pix gerado, não pago')) : ($q->payment_status === 'rejected' ? 'Pagamento recusado' : 'Pagamento venceu') }}</span>
                        @elseif ($q->sale && $q->sale->payment_method)
                            <span class="po-tag"><i class="bi bi-wallet2"></i>{{ $orders->paymentLabel($q->sale->payment_method) }}</span>
                        @elseif ($q->payment_preference)
                            <span class="po-tag"><i class="bi bi-wallet2"></i>Quer pagar com {{ $orders->paymentLabel($q->payment_preference) }}</span>
                        @endif
                        @if ($q->coupon_code)
                            <span class="po-tag is-emerald" data-testid="order-coupon"><i class="bi bi-ticket-perforated"></i>Cupom {{ $q->coupon_code }} · −{{ $orders->money((float) $q->discount) }}</span>
                        @endif
                        @if ($q->delivery_method)
                            <span class="po-tag"><i class="bi {{ $q->delivery_method === 'entrega' ? 'bi-truck' : 'bi-shop' }}"></i>{{ $q->delivery_method === 'entrega' ? 'Entrega' . ((float) $q->delivery_fee > 0 ? ' · ' . $orders->money((float) $q->delivery_fee) : '') : 'Retira com você' }}</span>
                        @endif
                        @if ($open && $r->short)
                            <span class="po-tag is-rose" title="Algum produto tem menos estoque do que o pedido"><i class="bi bi-exclamation-triangle"></i>Falta estoque</span>
                        @endif
                        @if ($q->client_notes)
                            <span class="po-tag is-amber"><i class="bi bi-chat-left-text"></i>Tem recado</span>
                        @endif
                    </div>

                    <button type="button" class="po-thumbs" x-on:click="more = !more" title="Ver os produtos do pedido">
                        @foreach (array_slice($r->items, 0, 5) as $it)
                            <span class="po-thumb">
                                @if ($it['image'])<img src="{{ $it['image'] }}" alt="" loading="lazy">@else<i class="bi bi-box-seam"></i>@endif
                                @if ($it['quantity'] > 1)<b>{{ $it['quantity'] }}x</b>@endif
                            </span>
                        @endforeach
                        @if (count($r->items) > 5)<span class="po-thumb is-more">+{{ count($r->items) - 5 }}</span>@endif
                        <span class="po-thumbs-label">
                            <span class="truncate">@if ($q->kit)<b class="text-rose-600 dark:text-rose-300">{{ \App\Services\Portal\KitService::style($q->kit['style'] ?? null)[1] }} Kit {{ $q->kit['name'] ?? '' }}:</b> @endif{{ $r->items[0]['name'] ?? 'Pedido sem produtos' }}@if (count($r->items) > 1) <span class="po-dim">e mais {{ count($r->items) - 1 }}</span>@endif</span>
                            <i class="bi" :class="more ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </span>
                    </button>

                    <div x-show="more" x-cloak x-transition class="po-details">
                        <ul class="po-items">
                            @foreach ($r->items as $it)
                                <li>
                                    <span class="po-thumb is-sm">@if ($it['image'])<img src="{{ $it['image'] }}" alt="" loading="lazy">@else<i class="bi bi-box-seam"></i>@endif</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="po-item-name">{{ $it['name'] }}</p>
                                        <p class="po-dim">
                                            {{ $it['quantity'] }} × {{ $orders->money($it['unit']) }}@if ($it['code']) · {{ $it['code'] }}@endif
                                            @if ($open && $it['stock'] !== null)
                                                · <span class="{{ $it['stock'] < $it['quantity'] ? 'text-rose-600 dark:text-rose-400 font-bold' : '' }}">estoque {{ $it['stock'] }}</span>
                                            @endif
                                        </p>
                                        @if ($it['notes'])<p class="po-dim italic">"{{ $it['notes'] }}"</p>@endif
                                    </div>
                                    <strong class="po-item-total">{{ $orders->money($it['total']) }}</strong>
                                </li>
                            @endforeach
                            @foreach (\App\Services\Portal\KitService::lines($q->kit) as [$kName, $kQty, $kVal])
                                <li data-testid="order-kit-line"><span class="po-thumb is-sm">{{ mb_substr($kName, 0, 1) }}</span><div class="min-w-0 flex-1"><p class="po-item-name">{{ trim(mb_substr($kName, 1)) }}</p><p class="po-dim">{{ $loop->first ? 'base do kit' : (str_starts_with($kName, '🎨') ? 'opção escolhida' : 'enfeite · ' . $kQty . 'x') }}</p></div><strong class="po-item-total">{{ $orders->money($kVal) }}</strong></li>
                            @endforeach
                            @foreach ($q->extra_items ?? [] as $extra)
                                <li><span class="po-thumb is-sm"><i class="bi bi-plus-lg"></i></span><div class="min-w-0 flex-1"><p class="po-item-name">{{ $extra['description'] ?? 'Item extra' }}</p><p class="po-dim">pedido à parte · {{ $extra['quantity'] ?? 1 }}x</p></div></li>
                            @endforeach
                        </ul>
                        @if ($q->delivery_method === 'entrega' && filled($q->shipping_address['text'] ?? null))
                            <p class="po-note"><i class="bi bi-geo-alt-fill text-violet-500"></i><span>{{ $q->shipping_address['text'] }}</span></p>
                        @endif
                        @foreach (\App\Services\Portal\KitService::details($q->kit) as [$dIc, $dTx])
                            <p class="po-note" data-testid="order-kit-detail"><span>{{ $dIc }}</span><span>{{ $dTx }}</span></p>
                        @endforeach
                        @if ($kPh = \App\Services\Portal\KitService::photoUrls($q->kit))
                            <div class="flex flex-wrap items-center gap-2 mt-2" data-testid="order-kit-photos"><span class="po-dim">📷 Fotos do cliente:</span>@foreach ($kPh as $ph)<a href="{{ $ph }}" target="_blank" rel="noopener" download><img src="{{ $ph }}" alt="" class="w-14 h-14 rounded-lg object-cover border border-slate-200 dark:border-slate-700"></a>@endforeach</div>
                        @endif
                        @if (filled($q->kit['message'] ?? null) || filled($q->kit['to'] ?? null))
                            <p class="po-note is-amber"><i class="bi bi-envelope-heart-fill"></i><span><b>Cartão do kit:</b> {{ filled($q->kit['to'] ?? null) ? 'Para ' . $q->kit['to'] . '. ' : '' }}{{ $q->kit['message'] ?? '' }}{{ filled($q->kit['from'] ?? null) ? ' De: ' . $q->kit['from'] : '' }}</span></p>
                        @endif
                        @if ($q->client_notes)
                            <p class="po-note is-amber"><i class="bi bi-chat-left-text-fill"></i><span><b>Recado do cliente:</b> {{ $q->client_notes }}</span></p>
                        @endif
                        @if (! in_array($q->status, ['approved'], true))
                            <div class="po-reply" x-data="{ text: @js((string) $q->admin_notes) }">
                                <label class="po-label">Resposta para o cliente (aparece no portal)</label>
                                <textarea x-model="text" rows="2" placeholder="Ex.: separei seus produtos, posso entregar amanhã."></textarea>
                                <button type="button" class="po-btn" x-on:click="$wire.saveNotes({{ $q->id }}, text)"><i class="bi bi-save"></i>Salvar resposta</button>
                            </div>
                        @elseif ($q->admin_notes)
                            <p class="po-note"><i class="bi bi-reply-fill text-indigo-500"></i><span><b>Sua resposta:</b> {{ $q->admin_notes }}</span></p>
                        @endif
                        @if ($q->client_notified_at)
                            <p class="po-dim"><i class="bi bi-whatsapp"></i> Avisado em {{ $q->client_notified_at->format('d/m H:i') }}{{ $q->client_seen_status === $q->status ? ' · o cliente já viu no portal' : '' }}</p>
                        @endif
                    </div>

                    <div class="po-actions">
                        @if ($canConfirm)
                            <button type="button" class="po-btn is-primary" x-on:click="confirm = @js($payload)"><i class="bi bi-check2-circle"></i>Confirmar venda</button>
                        @elseif ($q->sale)
                            <a href="{{ route('sales.show', $q->sale->id) }}" class="po-btn is-green"><i class="bi bi-receipt"></i>Venda #{{ $q->sale->id }}</a>
                        @endif

                        @if ($q->status === 'approved')
                            @php $flow = $orders->deliveryFlow($q); @endphp
                            <div class="relative" x-data="{ d: false, stage: @js($q->delivery_stage ?: $flow[0]), code: @js((string) $q->tracking_code), carrier: @js($q->tracking_carrier ?: ($q->delivery_method === 'retirada' ? '' : 'correios')), eta: @js($q->delivery_eta?->toDateString() ?? '') }" x-on:click.outside="d = false">
                                <button type="button" class="po-btn" x-on:click="d = !d" data-testid="delivery-open" title="Marcar o andamento da entrega"><i class="bi bi-truck"></i><span class="po-lbl">Entrega</span></button>
                                <div x-show="d" x-cloak x-transition class="po-menu po-deliv" data-testid="delivery-panel">
                                    <p class="po-label">{{ $q->delivery_method === 'retirada' ? 'Retirada na loja' : 'Andamento da entrega' }}</p>
                                    @foreach ($flow as $st)
                                        <button type="button" x-on:click="stage = '{{ $st }}'" :class="stage === '{{ $st }}' && 'is-on'" data-testid="delivery-stage-{{ $st }}"><i class="bi {{ $orders::DELIVERY_STAGES[$st][2] }}"></i>{{ $st === 'entregue' && $q->delivery_method === 'retirada' ? 'Retirado' : $orders::DELIVERY_STAGES[$st][0] }}</button>
                                    @endforeach
                                    <div x-show="stage === 'enviado' || stage === 'saiu'" class="po-deliv-fields">
                                        <select x-model="carrier" class="po-deliv-input">
                                            @foreach ($orders::CARRIERS as $ck => $cn)<option value="{{ $ck }}">{{ $cn }}</option>@endforeach
                                        </select>
                                        <input type="text" x-model="code" maxlength="60" placeholder="Código de rastreio ou link" class="po-deliv-input" data-testid="delivery-code">
                                    </div>
                                    <label x-show="stage !== 'entregue'" class="po-deliv-fields"><span class="po-dim">Previsão (opcional)</span><input type="date" x-model="eta" class="po-deliv-input"></label>
                                    <button type="button" class="po-btn is-primary w-full justify-center" x-on:click="$wire.setDelivery({{ $q->id }}, stage, code, carrier, eta); d = false" data-testid="delivery-save"><i class="bi bi-check2"></i>Salvar e avisar</button>
                                </div>
                            </div>
                        @endif

                        @if (in_array($q->status, ['approved', 'rejected'], true) && ($q->status === 'rejected' || $q->sale))
                            <a href="{{ route('clients.portal.quotes.whatsapp', $q) }}" target="_blank" rel="noopener" class="po-btn is-wa" data-testid="notify-whatsapp" title="Avisar o cliente no WhatsApp">
                                <i class="bi bi-whatsapp"></i><span class="po-lbl">{{ $q->client_notified_at ? 'Avisar de novo' : 'Avisar' }}</span>
                            </a>
                        @elseif ($wa)
                            <a href="{{ $wa }}" target="_blank" rel="noopener" class="po-btn is-wa" title="Conversar com o cliente no WhatsApp"><i class="bi bi-whatsapp"></i><span class="po-lbl">Conversar</span></a>
                        @endif

                        @if ($open)
                            <div class="relative" x-data="{ m: false }" x-on:click.outside="m = false">
                                <button type="button" class="po-btn" x-on:click="m = !m" title="Mudar a situação"><i class="bi bi-flag"></i><span class="po-lbl">Situação</span></button>
                                <div x-show="m" x-cloak x-transition class="po-menu">
                                    @foreach (['pending' => 'Novo', 'reviewing' => 'Em análise (separando)', 'quoted' => 'Orçamento enviado'] as $st => $lbl)
                                        <button type="button" wire:click="setStatus({{ $q->id }}, '{{ $st }}')" x-on:click="m = false" class="{{ $q->status === $st ? 'is-on' : '' }}">
                                            <i class="bi {{ $this::STATUS[$st][2] }}"></i>{{ $lbl }}
                                        </button>
                                    @endforeach
                                    <button type="button" wire:click="askReject({{ $q->id }})" x-on:click="m = false" class="is-rose"><i class="bi bi-x-circle"></i>Recusar pedido</button>
                                </div>
                            </div>
                        @endif

                        @if ($q->payment_status === 'approved')
                            <form method="POST" action="{{ route('clients.portal.quotes.refund', $q) }}" class="contents"
                                  onsubmit="return confirm('Devolver o pagamento de R$ {{ number_format((float) $q->payment_amount, 2, ',', '.') }} ao cliente pelo Mercado Pago? A venda é cancelada e o estoque volta.');">
                                @csrf
                                <button type="submit" class="po-btn is-ghost is-rose" data-testid="refund-btn" title="Devolver o pagamento ao cliente (Mercado Pago)"><i class="bi bi-arrow-counterclockwise"></i></button>
                            </form>
                        @endif

                        <button type="button" class="po-btn is-ghost po-more" x-on:click="more = !more" :title="more ? 'Esconder detalhes' : 'Ver detalhes'">
                            <i class="bi" :class="more ? 'bi-chevron-up' : 'bi-three-dots'"></i>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Confirmar como venda (mesma regra de antes: cria a venda e baixa o estoque) --}}
    {{-- Vai para o body: dentro da página um ancestral com transform encurta o fundo fixo --}}
    <template x-teleport="body">
    <div x-show="confirm" x-cloak>
    <template x-if="confirm">
        <div class="po-modal-backdrop" x-on:keydown.escape.window="confirm = null" x-on:click.self="confirm = null">
            <form method="POST" :action="confirm.action" class="po-modal" x-data="{ tipo: 'a_vista', method: confirm.method }">
                @csrf
                <input type="hidden" name="voltar" value="pedidos">
                <div class="po-modal-grab"></div>
                <button type="button" class="po-modal-x" x-on:click="confirm = null" title="Fechar"><i class="bi bi-x-lg"></i></button>
                <div class="po-modal-head">
                    <span class="po-modal-icon"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <h3>Confirmar como venda</h3>
                        <p x-text="'Pedido #' + confirm.id + ' · ' + (confirm.client || 'cliente') + ' · ' + confirm.items + (confirm.items == 1 ? ' produto' : ' produtos')"></p>
                    </div>
                </div>
                <label class="po-label">Valor total</label>
                <div class="po-amount"><span>R$</span><input type="number" step="0.01" min="0" name="total_price" :value="confirm.total" required></div>
                <label class="po-label">Forma de pagamento</label>
                <input type="hidden" name="payment_method" :value="method">
                <div class="po-methods">
                    @foreach (['pix' => ['PIX', 'bi-lightning-charge'], 'dinheiro' => ['Dinheiro', 'bi-cash'], 'cartao_debito' => ['Débito', 'bi-credit-card-2-front'], 'cartao_credito' => ['Crédito', 'bi-credit-card'], 'boleto' => ['Boleto', 'bi-upc'], 'transferencia' => ['Transf.', 'bi-bank']] as $key => [$label, $icon])
                        <button type="button" class="po-method" :class="method === '{{ $key }}' && 'is-on'" x-on:click="method = '{{ $key }}'"><i class="bi {{ $icon }}"></i>{{ $label }}</button>
                    @endforeach
                </div>
                <label class="po-label">Como vai pagar</label>
                <input type="hidden" name="tipo_pagamento" :value="tipo">
                <div class="po-methods is-2">
                    <button type="button" class="po-method" :class="tipo === 'a_vista' && 'is-on'" x-on:click="tipo = 'a_vista'"><i class="bi bi-lightning"></i>À vista</button>
                    <button type="button" class="po-method" :class="tipo === 'parcelado' && 'is-on'" x-on:click="tipo = 'parcelado'"><i class="bi bi-calendar2-range"></i>Parcelado</button>
                </div>
                <div x-show="tipo === 'parcelado'" x-cloak>
                    <label class="po-label">Parcelas</label>
                    <select name="parcelas" class="po-select">
                        @for ($p = 2; $p <= 24; $p++)<option value="{{ $p }}">{{ $p }}x</option>@endfor
                    </select>
                </div>
                <p class="po-help"><i class="bi bi-info-circle"></i>A venda é criada com os produtos do pedido e o estoque baixa na hora. Depois você avisa o cliente no WhatsApp.</p>
                <div class="po-modal-actions">
                    <button type="button" class="po-btn" x-on:click="confirm = null">Cancelar</button>
                    <button type="submit" class="po-btn is-primary"><i class="bi bi-check2"></i>Criar venda</button>
                </div>
            </form>
        </div>
    </template>
    </div>
    </template>

    {{-- Recusar pedido --}}
    @if ($rejectingId)
        <template x-teleport="body">
        <div class="po-modal-backdrop" wire:key="po-reject" x-on:keydown.escape.window="$wire.cancelReject()" x-on:click.self="$wire.cancelReject()">
            <div class="po-modal">
                <div class="po-modal-grab"></div>
                <button type="button" class="po-modal-x" wire:click="cancelReject" title="Fechar"><i class="bi bi-x-lg"></i></button>
                <div class="po-modal-head">
                    <span class="po-modal-icon is-rose"><i class="bi bi-x-circle"></i></span>
                    <div>
                        <h3>Recusar o pedido #{{ $rejectingId }}?</h3>
                        <p>O cliente vê "Recusado" no portal. Nada muda no estoque.</p>
                    </div>
                </div>
                <label class="po-label">Motivo (opcional, vai na mensagem)</label>
                <textarea wire:model="rejectReason" rows="3" class="po-textarea" placeholder="Ex.: esse produto acabou, posso te oferecer outro."></textarea>
                <div class="po-modal-actions">
                    <button type="button" class="po-btn" wire:click="cancelReject">Voltar</button>
                    <button type="button" class="po-btn is-danger" wire:click="reject"><i class="bi bi-x-circle"></i>Recusar pedido</button>
                </div>
            </div>
        </div>
        </template>
    @endif

    <style>
        .po-grid { display: grid; gap: 12px; grid-template-columns: 1fr; align-items: start; }
        @media (min-width: 768px) { .po-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1536px) { .po-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 2200px) { .po-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .po-count { margin-left: 4px; min-width: 18px; padding: 0 5px; border-radius: 999px; background: rgb(99 102 241 / .15); font-size: .66rem; line-height: 18px; text-align: center; }
        .po-card { position: relative; background: #fff; border: 1px solid rgb(226 232 240 / .9); border-radius: 20px; padding: 14px; box-shadow: 0 1px 2px rgb(15 23 42 / .04); transition: box-shadow .2s; min-width: 0; overflow: visible; }
        .po-card::before { content: ''; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px; border-radius: 0 4px 4px 0; background: rgb(148 163 184); }
        .po-card.is-amber::before { background: linear-gradient(rgb(251 191 36), rgb(249 115 22)); }
        .po-card.is-sky::before { background: linear-gradient(rgb(56 189 248), rgb(79 70 229)); }
        .po-card.is-violet::before { background: linear-gradient(rgb(167 139 250), rgb(147 51 234)); }
        .po-card.is-emerald::before { background: linear-gradient(rgb(52 211 153), rgb(13 148 136)); }
        .po-card.is-rose::before { background: linear-gradient(rgb(251 113 133), rgb(225 29 72)); }
        .po-card:hover { box-shadow: 0 8px 24px -12px rgb(79 70 229 / .35); }
        .dark .po-card { background: rgb(15 23 42 / .8); border-color: rgb(51 65 85 / .7); }
        .po-top { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .po-name { font-weight: 700; font-size: .98rem; color: rgb(15 23 42); line-height: 1.25; }
        .dark .po-name { color: #fff; }
        .po-dim { color: rgb(100 116 139); font-size: .72rem; font-weight: 500; }
        .dark .po-dim { color: rgb(148 163 184); }
        .po-money { display: flex; flex-direction: column; align-items: flex-end; text-align: right; flex-shrink: 0; }
        .po-money strong { font-size: 1.12rem; font-weight: 900; color: rgb(15 23 42); white-space: nowrap; }
        .dark .po-money strong { color: #fff; }
        .po-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
        .po-tag { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; padding: 3px 9px; font-size: .68rem; font-weight: 600; background: rgb(241 245 249); color: rgb(71 85 105); white-space: nowrap; }
        .po-tag.is-amber { background: rgb(255 251 235); color: rgb(180 83 9); }
        .po-tag.is-sky { background: rgb(240 249 255); color: rgb(3 105 161); }
        .po-tag.is-violet { background: rgb(245 243 255); color: rgb(109 40 217); }
        .po-tag.is-emerald { background: rgb(236 253 245); color: rgb(4 120 87); }
        .po-tag.is-rose { background: rgb(255 241 242); color: rgb(190 18 60); }
        .dark .po-tag { background: rgb(30 41 59); color: rgb(203 213 225); }
        .dark .po-tag.is-amber { background: rgb(245 158 11 / .12); color: rgb(252 211 77); }
        .dark .po-tag.is-sky { background: rgb(14 165 233 / .12); color: rgb(125 211 252); }
        .dark .po-tag.is-violet { background: rgb(139 92 246 / .14); color: rgb(196 181 253); }
        .dark .po-tag.is-emerald { background: rgb(16 185 129 / .12); color: rgb(110 231 183); }
        .dark .po-tag.is-rose { background: rgb(244 63 94 / .12); color: rgb(253 164 175); }
        .po-thumbs { display: flex; align-items: center; gap: 6px; width: 100%; margin-top: 12px; padding: 8px; border-radius: 16px; background: rgb(248 250 252); text-align: left; min-width: 0; }
        .dark .po-thumbs { background: rgb(30 41 59 / .6); }
        .po-thumb { position: relative; width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px; background: #fff; border: 1px solid rgb(226 232 240); display: flex; align-items: center; justify-content: center; overflow: hidden; color: rgb(148 163 184); }
        .dark .po-thumb { background: rgb(15 23 42); border-color: rgb(51 65 85); }
        .po-thumb img { width: 100%; height: 100%; object-fit: contain; }
        .po-thumb b { position: absolute; right: 2px; bottom: 2px; font-size: .58rem; font-weight: 800; color: #fff; background: rgb(15 23 42 / .75); border-radius: 6px; padding: 0 4px; }
        .po-thumb.is-more { font-size: .72rem; font-weight: 800; color: rgb(79 70 229); }
        .po-thumb.is-sm { width: 40px; height: 40px; }
        .po-thumbs-label { display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0; font-size: .78rem; font-weight: 600; color: rgb(51 65 85); justify-content: space-between; }
        .dark .po-thumbs-label { color: rgb(226 232 240); }
        .po-details { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; }
        .po-items { display: flex; flex-direction: column; gap: 2px; }
        .po-items li { display: flex; align-items: center; gap: 10px; padding: 6px 2px; border-bottom: 1px solid rgb(241 245 249); }
        .dark .po-items li { border-color: rgb(30 41 59); }
        .po-items li:last-child { border-bottom: 0; }
        .po-item-name { font-size: .8rem; font-weight: 600; color: rgb(30 41 59); line-height: 1.25; }
        .dark .po-item-name { color: rgb(226 232 240); }
        .po-item-total { font-size: .8rem; font-weight: 800; color: rgb(15 23 42); white-space: nowrap; }
        .dark .po-item-total { color: #fff; }
        .po-note { display: flex; gap: 8px; align-items: flex-start; border-radius: 12px; background: rgb(245 243 255); padding: 8px 10px; font-size: .76rem; color: rgb(51 65 85); }
        .po-note.is-amber { background: rgb(255 251 235); }
        .po-note.is-amber i { color: rgb(245 158 11); }
        .dark .po-note { background: rgb(139 92 246 / .1); color: rgb(226 232 240); }
        .dark .po-note.is-amber { background: rgb(245 158 11 / .1); }
        .po-reply { display: flex; flex-direction: column; gap: 6px; }
        .po-reply textarea, .po-textarea { width: 100%; border-radius: 12px; border: 1px solid rgb(226 232 240); background: #fff; padding: 8px 10px; font-size: .8rem; resize: vertical; }
        .dark .po-reply textarea, .dark .po-textarea { background: rgb(15 23 42); border-color: rgb(51 65 85); color: #fff; }
        .po-reply .po-btn { align-self: flex-end; }
        .po-label { display: block; margin: 12px 0 6px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); }
        .po-reply .po-label { margin: 0; }
        .po-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; align-items: center; }
        .po-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 12px; padding: 8px 12px; font-size: .78rem; font-weight: 700; border: 1px solid rgb(226 232 240); background: #fff; color: rgb(51 65 85); transition: all .15s; white-space: nowrap; min-height: 38px; }
        .po-btn:hover { background: rgb(248 250 252); border-color: rgb(199 210 254); color: rgb(79 70 229); }
        .dark .po-btn { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(226 232 240); }
        .po-btn.is-primary { border: 0; color: #fff; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); box-shadow: 0 4px 12px -4px rgb(79 70 229 / .5); }
        .po-btn.is-primary:hover { filter: brightness(1.08); color: #fff; }
        .po-btn.is-green { border-color: rgb(167 243 208); color: rgb(4 120 87); background: rgb(236 253 245); }
        .dark .po-btn.is-green { background: rgb(16 185 129 / .12); border-color: rgb(16 185 129 / .35); color: rgb(110 231 183); }
        .po-btn.is-wa { border: 0; color: #fff; background: rgb(22 163 74); }
        .po-btn.is-wa:hover { background: rgb(21 128 61); color: #fff; }
        .po-btn.is-danger { border: 0; color: #fff; background: linear-gradient(90deg, rgb(225 29 72), rgb(219 39 119)); }
        .po-btn.is-ghost { background: transparent; }
        .po-btn.is-rose { color: rgb(225 29 72); }
        .dark .po-btn.is-rose { color: rgb(251 113 133); }
        .po-actions > .po-btn.is-primary, .po-actions > .po-btn.is-green { flex: 1 1 auto; }
        .po-more { margin-left: auto; }
        .po-menu { position: absolute; z-index: 30; bottom: calc(100% + 6px); left: 0; min-width: 220px; padding: 6px; border-radius: 14px; background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 12px 32px -8px rgb(15 23 42 / .3); display: flex; flex-direction: column; }
        .dark .po-menu { background: rgb(15 23 42); border-color: rgb(51 65 85); }
        .po-menu button { display: flex; align-items: center; gap: 8px; padding: 9px 10px; border-radius: 10px; font-size: .8rem; font-weight: 600; color: rgb(51 65 85); text-align: left; }
        .dark .po-menu button { color: rgb(226 232 240); }
        .po-menu button:hover { background: rgb(241 245 249); }
        .dark .po-menu button:hover { background: rgb(30 41 59); }
        .po-menu button.is-on { color: rgb(79 70 229); background: rgb(238 242 255); }
        .dark .po-menu button.is-on { background: rgb(79 70 229 / .2); color: rgb(199 210 254); }
        .po-deliv { min-width: 260px; gap: 2px; }
        .po-deliv .po-label { padding: 4px 8px 2px; }
        .po-deliv-fields { display: flex; flex-direction: column; gap: 6px; padding: 6px 4px; }
        .po-deliv-input { width: 100%; border-radius: 10px; border: 1px solid rgb(226 232 240); background: #fff; padding: 7px 10px; font-size: .8rem; color: rgb(30 41 59); }
        .dark .po-deliv-input { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(226 232 240); }
        @media (max-width: 640px) { .po-deliv { position: fixed; left: 12px; right: 12px; bottom: calc(84px + env(safe-area-inset-bottom)); min-width: 0; } }
        .po-menu button.is-rose { color: rgb(225 29 72); border-top: 1px solid rgb(241 245 249); margin-top: 4px; padding-top: 10px; }
        .po-flash { display: flex; align-items: center; gap: 10px; border-radius: 16px; padding: 10px 14px; background: rgb(236 253 245); color: rgb(4 120 87); font-size: .85rem; font-weight: 600; border: 1px solid rgb(167 243 208); }
        .po-flash.is-error { background: rgb(255 241 242); color: rgb(190 18 60); border-color: rgb(254 205 211); }
        .dark .po-flash { background: rgb(16 185 129 / .12); color: rgb(110 231 183); border-color: rgb(16 185 129 / .3); }
        .po-notify { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; border-radius: 20px; padding: 14px; background: #fff; border: 1px solid rgb(167 243 208); }
        .po-notify.is-rose { border-color: rgb(254 205 211); }
        .dark .po-notify { background: rgb(15 23 42 / .8); border-color: rgb(16 185 129 / .35); }
        .po-notify-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgb(22 163 74); color: #fff; font-size: 1.3rem; flex-shrink: 0; }
        .po-notify-title { font-weight: 800; font-size: .9rem; color: rgb(15 23 42); }
        .dark .po-notify-title { color: #fff; }
        .po-notify-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .po-msg { margin-top: 6px; white-space: pre-wrap; overflow-wrap: anywhere; border-radius: 12px; background: rgb(248 250 252); padding: 10px; font: inherit; font-size: .76rem; }
        .dark .po-msg { background: rgb(30 41 59); }
        .po-link { color: rgb(79 70 229); font-weight: 700; font-size: .78rem; display: inline-flex; gap: 4px; align-items: center; cursor: pointer; }
        .dark .po-link { color: rgb(165 180 252); }
        .po-summary { display: flex; flex-wrap: wrap; gap: 8px; justify-content: space-between; align-items: center; font-size: .82rem; color: rgb(100 116 139); }
        body:has(.po-modal-backdrop) .mobile-bottom-tabbar { display: none !important; }
        .po-modal-backdrop { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: flex-end; justify-content: center; background: rgb(15 23 42 / .45); backdrop-filter: blur(4px); }
        .po-modal { position: relative; width: 100%; max-width: 480px; max-height: 92dvh; overflow-y: auto; background: #fff; border-radius: 24px 24px 0 0; padding: 10px 18px calc(18px + env(safe-area-inset-bottom)); box-shadow: 0 -10px 40px rgb(15 23 42 / .25); }
        @media (min-width: 640px) { .po-modal-backdrop { align-items: center; padding: 16px; } .po-modal { border-radius: 24px; padding: 22px; } .po-modal-grab { display: none; } }
        .dark .po-modal { background: rgb(15 23 42); color: rgb(226 232 240); }
        .po-modal-grab { width: 40px; height: 4px; border-radius: 999px; background: rgb(203 213 225); margin: 0 auto 10px; }
        .po-modal-x { position: absolute; top: 12px; right: 12px; width: 34px; height: 34px; border-radius: 10px; color: rgb(100 116 139); }
        .po-modal-head { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; padding-right: 36px; }
        .po-modal-head h3 { font-size: 1.05rem; font-weight: 800; color: rgb(15 23 42); }
        .dark .po-modal-head h3 { color: #fff; }
        .po-modal-head p { font-size: .8rem; color: rgb(100 116 139); }
        .po-modal-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; font-size: 1.2rem; background: linear-gradient(135deg, rgb(16 185 129), rgb(13 148 136)); }
        .po-modal-icon.is-rose { background: linear-gradient(135deg, rgb(244 63 94), rgb(219 39 119)); }
        .po-amount { display: flex; align-items: center; gap: 8px; border: 2px solid rgb(226 232 240); border-radius: 16px; padding: 4px 14px; }
        .po-amount:focus-within { border-color: rgb(99 102 241); }
        .dark .po-amount { border-color: rgb(51 65 85); }
        .po-amount span { font-weight: 800; color: rgb(100 116 139); }
        .po-amount input { flex: 1; min-width: 0; border: 0; background: transparent; font-size: 1.5rem; font-weight: 900; padding: 6px 0; color: rgb(15 23 42); }
        .dark .po-amount input { color: #fff; }
        .po-amount input:focus { outline: none; box-shadow: none; }
        .po-methods { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
        .po-methods.is-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .po-method { display: flex; flex-direction: column; align-items: center; gap: 2px; border-radius: 14px; padding: 9px 4px; font-size: .74rem; font-weight: 700; border: 2px solid rgb(226 232 240); color: rgb(71 85 105); }
        .po-method i { font-size: 1.05rem; }
        .po-method.is-on { border-color: rgb(99 102 241); background: rgb(238 242 255); color: rgb(67 56 202); }
        .dark .po-method { border-color: rgb(51 65 85); color: rgb(203 213 225); }
        .dark .po-method.is-on { background: rgb(79 70 229 / .2); color: rgb(199 210 254); }
        .po-select { width: 100%; border-radius: 14px; border: 2px solid rgb(226 232 240); padding: 9px 12px; font-weight: 600; background: transparent; }
        .dark .po-select { border-color: rgb(51 65 85); color: #fff; background: rgb(15 23 42); }
        .po-help { margin-top: 12px; font-size: .74rem; color: rgb(100 116 139); display: flex; gap: 6px; align-items: flex-start; }
        .po-modal-actions { display: flex; gap: 8px; margin-top: 18px; }
        .po-modal-actions .po-btn { flex: 1; min-height: 46px; font-size: .86rem; }
        .po-modal-actions .po-btn.is-primary, .po-modal-actions .po-btn.is-danger { flex: 2; }
        @media (min-width: 1280px) and (max-width: 1799px) { .po-page .app-toolbar { flex-direction: column; align-items: stretch; } }
        @media (max-width: 420px) {
            .po-card { padding: 12px; border-radius: 18px; }
            .po-money strong { font-size: 1rem; }
            .po-thumb { width: 38px; height: 38px; }
            .po-lbl { display: none; }
        }
    </style>
</div>
