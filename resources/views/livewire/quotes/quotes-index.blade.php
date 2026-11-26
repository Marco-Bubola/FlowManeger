@php
    use App\Models\Quote;
    use App\Services\Quotes\QuoteService as QS;
    $tones = \App\Models\ConsortiumNotification::TONES;
    $tabs = ['abertos' => ['label' => 'Em aberto', 'icon' => 'bi-folder2-open']] + collect(Quote::STATUSES)->map(fn ($s) => ['label' => $s['label'], 'icon' => $s['icon']])->all() + ['todos' => ['label' => 'Todos', 'icon' => 'bi-collection']];
@endphp
<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Orçamentos" subtitle="Produto sob medida e reforma: monte o custo, mande para o cliente e vire venda" icon="bi-file-earmark-text"
                       section="vendas" tabs-section="orcamentos" tabs-active="lista">
            <x-slot:actions>
                <a href="{{ route('orcamentos.create', ['tipo' => 'reforma']) }}" wire:navigate class="crm-btn" title="Nova reforma"><i class="bi bi-tools"></i><span class="hidden sm:inline">Reforma</span></a>
                <a href="{{ route('orcamentos.create') }}" wire:navigate class="crm-btn crm-btn-primary" title="Novo orçamento"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Novo orçamento</span></a>
            </x-slot:actions>
        </x-page-header>

        <div class="cmx cmx-4 mb-4">
            <button type="button" wire:click="setTab('abertos')" class="cmx-item {{ $tab === 'abertos' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-folder2-open text-indigo-500"></i>Em aberto</span>
                <span class="cmx-value">{{ $counts['abertos'] }}</span>
                <span class="cmx-hint">pedidos, rascunhos e enviados</span>
            </button>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-cash-stack text-emerald-500"></i>Valor em aberto</span>
                <span class="cmx-value">{{ QS::money($openValue) }}</span>
                <span class="cmx-hint">se todos virarem venda</span>
            </div>
            <button type="button" wire:click="setTab('{{ ($counts['pedido'] ?? 0) ? 'pedido' : 'aprovado' }}')" class="cmx-item {{ in_array($tab, ['pedido', 'aprovado']) ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-bell text-pink-500"></i>Precisam de você</span>
                <span class="cmx-value {{ $waiting ? '!text-pink-600 dark:!text-pink-400' : '' }}">{{ $waiting }}</span>
                <span class="cmx-hint">{{ ($counts['pedido'] ?? 0) }} pedido(s) · {{ ($counts['aprovado'] ?? 0) }} aprovado(s)</span>
            </button>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-graph-up-arrow text-violet-500"></i>Viraram venda no mês</span>
                <span class="cmx-value">{{ $conversion !== null ? $conversion . '%' : $wonMonth }}</span>
                <span class="cmx-hint">{{ $wonMonth }} · {{ QS::money($wonValue) }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="crm-input !pl-9" placeholder="Buscar por cliente, item ou número (ex.: 12)">
            </div>
            <div class="crm-chips shrink-0">
                @foreach(Quote::KINDS as $k => $def)
                    <button type="button" wire:click="setKind('{{ $k }}')" class="crm-chip !py-2 {{ $kind === $k ? 'is-on' : '' }}"><i class="bi {{ $def['icon'] }}"></i>{{ $def['label'] }}</button>
                @endforeach
            </div>
        </div>

        <div class="qt-tabs mb-4">
            @foreach($tabs as $k => $t)
                @if($k === 'abertos' || $k === 'todos' || ($counts[$k] ?? 0) > 0 || $tab === $k)
                    <button type="button" wire:click="setTab('{{ $k }}')" class="qt-tab {{ $tab === $k ? 'is-on' : '' }}"><i class="bi {{ $t['icon'] }}"></i>{{ $t['label'] }} <b>{{ $counts[$k] ?? 0 }}</b></button>
                @endif
            @endforeach
        </div>

        @if($quotes->isEmpty())
            <x-empty-state icon="bi-file-earmark-text" title="{{ $search !== '' ? 'Nenhum orçamento encontrado' : 'Nenhum orçamento aqui' }}"
                           text="{{ $search !== '' ? 'Tente outro nome ou número.' : 'Crie um orçamento de produto sob medida ou de reforma. Os pedidos do portal também aparecem aqui.' }}">
                <a href="{{ route('orcamentos.create') }}" wire:navigate class="crm-btn crm-btn-primary"><i class="bi bi-plus-lg"></i>Novo orçamento</a>
            </x-empty-state>
        @else
            <div class="qt-grid">
                @foreach($quotes as $q)
                    @php
                        $st = $q->statusInfo(); $kd = $q->kindInfo(); $cover = $q->coverPhoto();
                        $wa = in_array($q->status, ['rascunho', 'enviado', 'vencido'], true) && $q->items ? QS::whatsappUrl($q) : null;
                        $expSoon = $q->status === 'enviado' && $q->valid_until && $q->valid_until->diffInDays(now(QS::TZ)->startOfDay(), true) <= 2 && !$q->isExpired();
                    @endphp
                    <article class="qt-card" wire:key="q-{{ $q->id }}">
                        <a href="{{ $q->status === 'pedido' ? route('orcamentos.edit', $q->id) : route('orcamentos.show', $q->id) }}" wire:navigate class="qt-card-top">
                            <div class="qt-thumb qt-tone" style="--t: {{ $tones[$kd['tone']] }}">
                                <i class="bi {{ $kd['icon'] }}"></i>
                                @if($cover)<img src="{{ Quote::photoUrl($cover) }}" alt="" loading="lazy" onerror="this.remove()">@endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                    <span class="qt-code">{{ $q->code() }}</span>
                                    <span class="qt-pill qt-tone" style="--t: {{ $tones[$st['tone']] }}"><i class="bi {{ $st['icon'] }}"></i>{{ $st['label'] }}</span>
                                    @if($q->source !== 'app')<span class="qt-pill qt-tone" style="--t: {{ $tones['sky'] }}" title="Chegou pelo portal"><i class="bi bi-globe2"></i>portal</span>@endif
                                </div>
                                <h3 class="qt-title">{{ $q->displayTitle() }}</h3>
                                <p class="qt-who"><i class="bi bi-person"></i> {{ $q->customerName() }}</p>
                            </div>
                        </a>
                        @if($q->change_request && $q->status === 'enviado')
                            <p class="mx-3 mb-2 qt-alert is-warn !py-1.5 !text-xs"><i class="bi bi-chat-left-dots"></i><span class="line-clamp-2">Pediu mudança: {{ $q->change_request }}</span></p>
                        @elseif($expSoon)
                            <p class="mx-3 mb-2 qt-alert is-warn !py-1.5 !text-xs"><i class="bi bi-hourglass-split"></i>Vence {{ $q->valid_until->format('d/m') }}</p>
                        @endif
                        <div class="qt-card-foot">
                            <div class="min-w-0 flex-1">
                                <p class="qt-total">{{ $q->total > 0 ? QS::money($q->total) : '—' }}</p>
                                <p class="qt-muted truncate">
                                    @if($q->status === 'pedido') pedido {{ $q->created_at->diffForHumans() }}
                                    @elseif($q->status === 'enviado') enviado {{ $q->sent_at?->diffForHumans() }}{{ $q->viewed_at ? ' · visto' : ' · não visto' }}
                                    @elseif($q->status === 'convertido') venda #{{ $q->sale_id }}
                                    @else {{ $q->updated_at->diffForHumans() }} @endif
                                </p>
                            </div>
                            @if($wa)
                                <a href="{{ $wa }}" target="_blank" rel="noopener" wire:click="markSent({{ $q->id }})" class="crm-btn crm-btn-wa !h-9 !px-3" title="Mandar pelo WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            @endif
                            @if($q->status === 'aprovado')
                                <a href="{{ route('orcamentos.show', $q->id) }}" wire:navigate class="crm-btn crm-btn-primary !h-9 !px-3"><i class="bi bi-cart-plus"></i>Vender</a>
                            @elseif($q->status === 'pedido')
                                <a href="{{ route('orcamentos.edit', $q->id) }}" wire:navigate class="crm-btn crm-btn-primary !h-9 !px-3"><i class="bi bi-calculator"></i>Orçar</a>
                            @endif
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open" class="crm-icon-btn" title="Mais"><i class="bi bi-three-dots-vertical"></i></button>
                                <div x-show="open" x-cloak x-transition class="absolute right-0 bottom-full mb-1 z-20 w-48 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl overflow-hidden text-sm">
                                    <a href="{{ route('orcamentos.edit', $q->id) }}" wire:navigate class="crm-menu-item"><i class="bi bi-pencil"></i>Editar</a>
                                    <button type="button" wire:click="duplicate({{ $q->id }})" class="crm-menu-item"><i class="bi bi-copy"></i>Duplicar</button>
                                    <a href="{{ route('orcamentos.pdf', $q->id) }}" target="_blank" class="crm-menu-item"><i class="bi bi-filetype-pdf"></i>PDF para o cliente</a>
                                    <button type="button" wire:click="delete({{ $q->id }})" wire:confirm="Apagar {{ $q->code() }}?" class="crm-menu-item !text-rose-600"><i class="bi bi-trash3"></i>Apagar</button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <x-infinite-feed :paginator="$quotes" label="orçamentos" />
        @endif
    </div>
    <x-quotes-css />
</div>
