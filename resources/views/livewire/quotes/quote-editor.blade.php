@php
    use App\Services\Quotes\QuoteService as QS;
    use App\Models\Quote;
    $money = fn ($v) => QS::money((float) $v);
    $minMarkup = (float) ($settings['min_markup'] ?? 5);
@endphp
<div class="qt-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header :title="$quote ? 'Orçamento ' . $quote->code() : 'Novo orçamento'"
                       :subtitle="$quote ? 'Editando: o link do cliente mostra a versão salva' : 'Monte o custo, veja o preço sugerido e mande para o cliente'"
                       icon="bi-file-earmark-text" :back-route="$quote ? route('orcamentos.show', $quote->id) : route('orcamentos.index')"
                       section="orcamentos" tabs-section="orcamentos" :tabs-active="$quote ? null : 'novo'">
            <x-slot:actions>
                <button type="button" wire:click="save('stay')" class="crm-btn qt-hide-sm" wire:loading.attr="disabled" wire:target="save"><i class="bi bi-floppy"></i>Salvar</button>
                <button type="button" wire:click="save" class="crm-btn crm-btn-primary" wire:loading.attr="disabled" wire:target="save"><i class="bi bi-check2"></i><span class="hidden sm:inline">Salvar e ver</span></button>
            </x-slot:actions>
        </x-page-header>

        @if($quote && $quote->change_request)
            <div class="qt-alert is-warn mb-3"><i class="bi bi-chat-left-dots mt-0.5"></i><span><b>O cliente pediu mudança:</b> {{ $quote->change_request }}</span></div>
        @endif
        @if($quote && in_array($quote->status, ['enviado', 'aprovado'], true))
            <div class="qt-alert is-info mb-3"><i class="bi bi-info-circle mt-0.5"></i>
                <span>Este orçamento já foi enviado. Pode mudar à vontade: o cliente vê a versão nova no mesmo link, com o aviso “Orçamento atualizado”.{{ $quote->status === 'aprovado' ? ' Se mudar item ou preço, ele precisa aprovar de novo.' : '' }}</span>
            </div>
        @endif

        @if($quote && ($quote->request_text || $quote->request_photos))
            @php $rd = $quote->request_data ?? []; @endphp
            <section class="crs-card mb-4">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-inbox"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">O que o cliente pediu</h2>
                        <p class="crs-sub">Pelo portal em {{ $quote->created_at->timezone(QS::TZ)->format('d/m/Y H:i') }}{{ $quote->product ? ' · produto: ' . $quote->product->name : '' }}</p>
                    </div>
                </div>
                <div class="qt-sec">
                    @if($quote->request_text)<p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $quote->request_text }}</p>@endif
                    @if(!empty($rd['cores']) || !empty($rd['medidas']))
                        <div class="crm-chips">
                            @foreach((array) ($rd['cores'] ?? []) as $g => $c)<span class="crm-chip is-on"><i class="bi bi-palette"></i>{{ is_int($g) ? '' : $g . ': ' }}{{ $c }}</span>@endforeach
                            @if(!empty($rd['medidas']))<span class="crm-chip"><i class="bi bi-rulers"></i>{{ $rd['medidas'] }}</span>@endif
                        </div>
                    @endif
                    @if($quote->request_photos)
                        <div class="qt-photos">
                            @foreach($quote->request_photos as $ph)
                                <a href="{{ Quote::photoUrl($ph) }}" target="_blank" class="qt-photo"><img src="{{ Quote::photoUrl($ph) }}" alt="Foto do cliente" loading="lazy"></a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="qt-editor">
            <div class="flex flex-col gap-4 min-w-0">
                {{-- ===== Tipo e cliente ===== --}}
                <section class="crs-card qt-card-visible">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-person-lines-fill"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Tipo e cliente</h2></div>
                    </div>
                    <div class="qt-sec">
                        <div class="qt-tc">
                            <div class="qt-tc-kind">
                                <span class="crm-label">Tipo</span>
                                <div class="qt-seg">
                                    @foreach(Quote::KINDS as $k => $def)
                                        <button type="button" wire:click="$set('kind', '{{ $k }}')" class="{{ $kind === $k ? 'is-on' : '' }}">
                                            <span class="crs-row-icon qt-tone" style="--t: {{ \App\Models\ConsortiumNotification::TONES[$def['tone']] }}"><i class="bi {{ $def['icon'] }}"></i></span>
                                            <span class="min-w-0"><span class="qt-seg-t block">{{ $def['label'] }}</span><span class="qt-muted block truncate">{{ $k === 'produto' ? 'Sob medida, outra cor ou peça nova' : 'A peça do cliente: estofar, pintar' }}</span></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Cliente: dropdown com pesquisa junto, igual ao criar venda --}}
                            <div class="qt-tc-client" x-data="{ open: false, creating: false }" @keydown.escape.window="open = false">
                                <span class="crm-label">Cliente</span>
                                <div class="relative">
                                    <button type="button" class="qt-dd {{ $client ? 'is-set' : '' }}" @click="open = !open; creating = false; $nextTick(() => { if (open) $refs.cs.focus() })" data-testid="client-dropdown">
                                        <span class="qt-dd-av">{{ $client ? mb_strtoupper(mb_substr($client->name, 0, 1)) : '' }}<i class="bi bi-person {{ $client ? 'hidden' : '' }}"></i></span>
                                        <span class="min-w-0 flex-1 text-left">
                                            <span class="block truncate font-bold">{{ $client->name ?? 'Selecionar cliente' }}</span>
                                            <span class="block truncate qt-muted">{{ $client ? ($client->phone ?: 'sem telefone') : 'Busque pelo nome ou telefone, ou crie um novo' }}</span>
                                        </span>
                                        @if($client)
                                            <span role="button" tabindex="0" wire:click.stop="clearClient" class="crm-icon-btn !w-7 !h-7" title="Tirar o cliente"><i class="bi bi-x-lg"></i></span>
                                        @endif
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform" :class="open && 'rotate-180'"></i>
                                    </button>

                                    <div x-show="open" x-cloak x-transition.origin.top @click.outside="open = false" class="qt-dd-menu">
                                        <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                                            <div class="relative">
                                                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input x-ref="cs" type="search" wire:model.live.debounce.250ms="clientSearch" class="crm-input !pl-9" placeholder="Buscar cliente..." autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="qt-dd-list">
                                            @forelse($clientResults as $c)
                                                <button type="button" wire:click="pickClient({{ $c->id }})" @click="open = false" wire:key="cl-{{ $c->id }}">
                                                    <span class="qt-dd-av">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                                                    <span class="flex-1 min-w-0 truncate font-semibold">{{ $c->name }}</span>
                                                    @if($c->phone)<span class="text-xs text-slate-400 shrink-0">{{ $c->phone }}</span>@endif
                                                </button>
                                            @empty
                                                <p class="px-4 py-3 text-sm text-slate-500">Nenhum cliente encontrado.</p>
                                            @endforelse
                                        </div>
                                        <div class="qt-dd-foot" x-data="{ nome: '', fone: '' }">
                                            <button type="button" x-show="!creating" class="qt-dd-new" @click="creating = true; nome = $wire.clientSearch; $nextTick(() => $refs.nn.focus())">
                                                <span><i class="bi bi-plus-lg"></i></span>
                                                {{ trim($clientSearch) !== '' ? 'Criar "' . \Illuminate\Support\Str::limit(trim($clientSearch), 28) . '"' : 'Criar novo cliente' }}
                                            </button>
                                            <div x-show="creating" x-cloak class="flex flex-col gap-2">
                                                <div class="qt-2">
                                                    <input x-ref="nn" x-model="nome" type="text" class="crm-input" placeholder="Nome do cliente" maxlength="255" @keydown.enter.prevent="$wire.createClient(nome, fone).then(() => { open = false; creating = false })">
                                                    <input x-model="fone" type="tel" class="crm-input" placeholder="WhatsApp (opcional)" maxlength="30">
                                                </div>
                                                @error('newClient') <span class="crm-err">{{ $message }}</span> @enderror
                                                <div class="flex gap-2">
                                                    <button type="button" class="crm-btn flex-1 justify-center" @click="creating = false">Cancelar</button>
                                                    <button type="button" class="crm-btn crm-btn-primary flex-1 justify-center" @click="$wire.createClient(nome, fone).then(() => { open = false; creating = false })"><i class="bi bi-check-lg"></i>Criar e usar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="qt-tc-row {{ $client ? 'has-client' : '' }}">
                            @if(!$client)
                                <label class="crm-label">Ou só o nome de quem pediu
                                    <input type="text" wire:model="contactName" class="crm-input" placeholder="Ex.: Ana (indicação da Paula)" maxlength="120">
                                    @error('contactName') <span class="crm-err">{{ $message }}</span> @enderror
                                </label>
                                <label class="crm-label">WhatsApp
                                    <input type="tel" wire:model="contactPhone" class="crm-input" placeholder="(16) 99999-9999" maxlength="30">
                                </label>
                            @endif
                            <label class="crm-label qt-tc-title">Título (opcional)
                                <input type="text" wire:model="title" class="crm-input" placeholder="{{ $kind === 'reforma' ? 'Ex.: Reforma de 4 cadeiras' : 'Ex.: Banquetas para a cozinha' }}" maxlength="160">
                            </label>
                        </div>
                        @if(!$client)<p class="qt-muted -mt-1">Quem ainda não é cliente vira cliente quando o orçamento virar venda.</p>@endif
                    </div>
                </section>

                @if($kind === 'reforma')
                    <section class="crs-card">
                        <div class="crs-head">
                            <span class="crs-head-icon bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-tools"></i></span>
                            <div class="min-w-0 flex-1"><h2 class="crs-title">A reforma</h2><p class="crs-sub">O cliente vê o que vai ser feito</p></div>
                        </div>
                        <div class="qt-sec qt-ref">
                            <div class="crm-chips">
                                @foreach($serviceOptions as $s)
                                    <button type="button" wire:click="toggleService(@js($s))" class="crm-chip {{ in_array($s, $this->services, true) ? 'is-on' : '' }}">{!! in_array($s, $this->services, true) ? '<i class="bi bi-check2"></i>' : '' !!}{{ $s }}</button>
                                @endforeach
                            </div>
                            <div class="crm-chips">
                                <span class="qt-muted self-center mr-1">A peça:</span>
                                <button type="button" wire:click="$set('pickup', 'busco')" class="crm-chip {{ $pickup === 'busco' ? 'is-on' : '' }}"><i class="bi bi-truck"></i>Eu busco</button>
                                <button type="button" wire:click="$set('pickup', 'cliente_leva')" class="crm-chip {{ $pickup === 'cliente_leva' ? 'is-on' : '' }}"><i class="bi bi-shop"></i>O cliente leva</button>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ===== Itens ===== --}}
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-list-check"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Itens <span class="crs-count">{{ count($items) }}</span></h2><p class="crs-sub">Cada item tem a ficha de custo (só você vê) e o preço</p></div>
                        <button type="button" wire:click="addItem" class="crs-link"><i class="bi bi-plus-lg"></i>Item</button>
                    </div>
                    <div class="qt-sec">
                        @error('items') <p class="crm-err">{{ $message }}</p> @enderror
                        @foreach($items as $i => $it)
                            @php
                                $cost = QS::itemCost($it);
                                $extrasV = QS::itemExtras($it);
                                $sug = QS::suggestedPrice($cost, (float) ($it['margin'] ?? $settings['margin']), $extrasV);
                                $pr = QS::itemProfit($it);
                                $low = $cost > 0 && (float) $it['price'] < $cost * (1 + $minMarkup / 100);
                                $base = !empty($it['product_id']) ? \App\Models\Product::where('user_id', auth()->id())->find($it['product_id']) : null;
                            @endphp
                            <div class="qt-item" wire:key="item-{{ $it['key'] }}">
                                <div class="qt-item-head">
                                    <span class="qt-num">{{ $i + 1 }}</span>
                                    <input type="text" wire:model.blur="items.{{ $i }}.name" class="crm-input !py-1.5 font-bold flex-1 min-w-0" placeholder="{{ $kind === 'reforma' ? 'Ex.: Cadeira de ferro, trocar assento' : 'Ex.: Banqueta Stylo 75 cm' }}" maxlength="160">
                                    <div class="flex items-center shrink-0">
                                        <button type="button" wire:click="moveItem({{ $i }}, -1)" class="crm-icon-btn qt-hide-sm" title="Subir" @disabled($i === 0)><i class="bi bi-arrow-up"></i></button>
                                        <button type="button" wire:click="moveItem({{ $i }}, 1)" class="crm-icon-btn qt-hide-sm" title="Descer" @disabled($i === count($items) - 1)><i class="bi bi-arrow-down"></i></button>
                                        <button type="button" wire:click="duplicateItem({{ $i }})" class="crm-icon-btn" title="Duplicar"><i class="bi bi-copy"></i></button>
                                        <button type="button" wire:click="removeItem({{ $i }})" wire:confirm="Tirar este item do orçamento?" class="crm-icon-btn" title="Tirar"><i class="bi bi-trash3"></i></button>
                                    </div>
                                </div>
                                @error('items.' . $i . '.name') <p class="crm-err px-3 pt-2">{{ $message }}</p> @enderror
                                <div class="p-3 flex flex-col gap-3">
                                    {{-- Produto de base --}}
                                    <div class="relative">
                                        @if($base)
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <x-product-thumb :product="$base" size="h-9 w-9" rounded="rounded-lg" />
                                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[14rem]">{{ $base->name }}</span>
                                                <span class="crm-chips">
                                                    <button type="button" wire:click="$set('items.{{ $i }}.type', 'variacao')" class="crm-chip {{ $it['type'] === 'variacao' ? 'is-on' : '' }}">Outra cor ou medida</button>
                                                    <button type="button" wire:click="$set('items.{{ $i }}.type', 'estoque')" class="crm-chip {{ $it['type'] === 'estoque' ? 'is-on' : '' }}">Do estoque, como está</button>
                                                </span>
                                                <button type="button" wire:click="clearProduct({{ $i }})" class="crm-icon-btn !w-7 !h-7" title="Tirar o produto de base"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        @else
                                            <button type="button" wire:click="pickProductFor({{ $i }})" class="crs-link"><i class="bi bi-box-seam"></i>Partir de um produto meu</button>
                                        @endif
                                        @if($productFor === $i)
                                            <div class="qt-pick !static !mt-2" x-data x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                                                <div class="p-2 border-b border-slate-100 dark:border-slate-800"><input type="search" wire:model.live.debounce.250ms="productSearch" class="crm-input" placeholder="Nome ou código do produto"></div>
                                                @forelse($productResults as $p)
                                                    <div class="flex items-center gap-2 px-3 py-2 border-b border-slate-100 dark:border-slate-800 last:border-0">
                                                        <x-product-thumb :product="$p" size="h-9 w-9" rounded="rounded-lg" />
                                                        <span class="flex-1 min-w-0"><span class="block truncate text-sm font-semibold">{{ $p->name }}</span><span class="qt-muted">{{ $p->product_code }} · {{ $money($p->price_sale) }}{{ $p->made_to_order ? ' · sob encomenda' : '' }}</span></span>
                                                        <button type="button" wire:click="useProduct({{ $p->id }}, 'variacao')" class="crm-chip" title="Mesmo produto em outra cor ou medida">Outra cor</button>
                                                        <button type="button" wire:click="useProduct({{ $p->id }}, 'estoque')" class="crm-chip" title="Vender este produto como ele é">Como está</button>
                                                    </div>
                                                @empty
                                                    <p class="p-3 qt-muted">Nenhum produto encontrado.</p>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>

                                    <div class="qt-3 qt-item-f">
                                        <label class="crm-label">Quantidade
                                            <input type="number" min="1" wire:model.blur="items.{{ $i }}.qty" class="crm-input">
                                        </label>
                                        <label class="crm-label">Cor / acabamento
                                            <input type="text" wire:model.blur="items.{{ $i }}.attrs.cor" class="crm-input" placeholder="Ex.: Preto com corano caramelo" maxlength="120">
                                        </label>
                                        <label class="crm-label col-span-2 md:col-span-1">Medida
                                            <input type="text" wire:model.blur="items.{{ $i }}.attrs.medida" class="crm-input" placeholder="Ex.: 75 cm de altura" maxlength="120">
                                        </label>
                                    </div>
                                    <div class="qt-item-dp">
                                    <label class="crm-label">Descrição para o cliente
                                        <textarea wire:model.blur="items.{{ $i }}.description" rows="2" class="crm-input !min-h-[3.5rem]" placeholder="O que vai ser feito, material, acabamento" maxlength="2000"></textarea>
                                    </label>

                                    <div class="qt-photos">
                                        @foreach($it['photos'] ?? [] as $p => $ph)
                                            <div class="qt-photo" wire:key="ph-{{ $it['key'] }}-{{ $p }}"><img src="{{ Quote::photoUrl($ph) }}" alt="Foto" loading="lazy"><button type="button" wire:click="removePhoto({{ $i }}, {{ $p }})" title="Tirar foto"><i class="bi bi-x-lg"></i></button></div>
                                        @endforeach
                                        @if(count($it['photos'] ?? []) < 8)
                                            <label class="qt-photo-add" wire:loading.class="opacity-60" wire:target="uploads.{{ $it['key'] }}">
                                                <i class="bi bi-camera text-lg" wire:loading.remove wire:target="uploads.{{ $it['key'] }}"></i>
                                                <span wire:loading.remove wire:target="uploads.{{ $it['key'] }}">Foto</span>
                                                <span wire:loading wire:target="uploads.{{ $it['key'] }}">Enviando</span>
                                                <input type="file" accept="image/*" multiple class="hidden" wire:model="uploads.{{ $it['key'] }}">
                                            </label>
                                        @endif
                                    </div>
                                    </div>
                                    @error('uploads.' . $it['key'] . '.*') <p class="crm-err">{{ $message }}</p> @enderror

                                    {{-- Ficha de custo --}}
                                    <div class="qt-cost">
                                        <button type="button" wire:click="toggleCosts({{ $i }})" class="w-full flex items-center gap-2 px-3 py-2.5 text-left">
                                            <i class="bi bi-calculator text-indigo-500"></i>
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100 flex-1">Custo {{ $money($cost) }} <span class="qt-private ml-1"><i class="bi bi-eye-slash"></i>só você vê</span></span>
                                            <span class="text-xs font-semibold text-slate-500">{{ count($it['costs'] ?? []) }} linha(s)</span>
                                            <i class="bi {{ $openCosts === $i ? 'bi-chevron-up' : 'bi-chevron-down' }} text-slate-400"></i>
                                        </button>
                                        @if(isset($stale[$i]))
                                            <div class="qt-alert is-warn mx-3 mb-2"><i class="bi bi-arrow-repeat mt-0.5"></i>
                                                <span class="flex-1">O preço de {{ count($stale[$i]) }} material(is) mudou na tabela desde que você montou este custo.</span>
                                                <button type="button" wire:click="refreshCosts({{ $i }})" class="crs-link shrink-0">Atualizar</button>
                                            </div>
                                        @endif
                                        @if($openCosts === $i)
                                            <div class="border-t border-dashed border-slate-200 dark:border-slate-700">
                                                @if(!empty($it['costs']))
                                                    <div class="qt-cost-row qt-head-row"><span>Material, peça ou hora</span><span>Qtd.</span><span>R$ unid.</span><span class="qt-line-total text-right">Total</span><span></span></div>
                                                @endif
                                                @foreach($it['costs'] ?? [] as $c => $row)
                                                    <div class="qt-cost-row" wire:key="cost-{{ $it['key'] }}-{{ $c }}">
                                                        <input type="text" wire:model.blur="items.{{ $i }}.costs.{{ $c }}.n" class="qt-cell" placeholder="Ex.: Tubo 3/4" maxlength="160" title="{{ $row['n'] }}">
                                                        <div class="relative"><input type="number" step="0.01" min="0" wire:model.blur="items.{{ $i }}.costs.{{ $c }}.q" class="qt-cell text-right !pr-1">@if(!empty($row['u']))<span class="absolute -bottom-2.5 right-1 text-[9px] font-bold text-slate-400">{{ $row['u'] }}</span>@endif</div>
                                                        <input type="number" step="0.01" min="0" wire:model.blur="items.{{ $i }}.costs.{{ $c }}.v" class="qt-cell text-right">
                                                        <span class="qt-line-total text-right text-sm font-bold text-slate-700 dark:text-slate-200">{{ $money((float) $row['q'] * (float) $row['v']) }}</span>
                                                        <button type="button" wire:click="removeCost({{ $i }}, {{ $c }})" class="crm-icon-btn !w-7 !h-7" title="Tirar linha"><i class="bi bi-x-lg"></i></button>
                                                    </div>
                                                @endforeach
                                                <div class="flex flex-wrap gap-2 p-3 relative">
                                                    <button type="button" wire:click="pickMaterialFor({{ $i }})" class="crm-btn !h-9 {{ $materialFor === $i ? '!border-indigo-400' : '' }}"><i class="bi bi-table"></i>Da tabela</button>
                                                    <button type="button" wire:click="addCost({{ $i }})" class="crm-btn !h-9"><i class="bi bi-plus-lg"></i>Linha livre</button>
                                                    <button type="button" wire:click="addCost({{ $i }}, 'frete')" class="crm-btn !h-9"><i class="bi bi-truck"></i>Frete</button>
                                                    <a href="{{ route('orcamentos.materials') }}" target="_blank" class="crs-link self-center ml-auto"><i class="bi bi-pencil"></i>Editar tabela</a>
                                                </div>
                                                @if($materialFor === $i)
                                                    <div class="mx-3 mb-3 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden" x-data x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                                                        <div class="p-2 bg-slate-50 dark:bg-slate-800/60"><input type="search" wire:model.live.debounce.250ms="materialSearch" class="crm-input" placeholder="Buscar: metalon 20, espuma d28, hora de solda..."></div>
                                                        <div class="max-h-72 overflow-y-auto">
                                                            @forelse($materials as $cat => $list)
                                                                <div class="qt-pick-cat">{{ $cat }}</div>
                                                                @foreach($list as $m)
                                                                    <button type="button" wire:click="addMaterial({{ $m->id }})" class="w-full flex items-center gap-2 px-3 py-2 text-left text-sm hover:bg-indigo-50 dark:hover:bg-slate-800" wire:key="pm-{{ $m->id }}">
                                                                        <i class="bi bi-plus-circle text-indigo-500"></i><span class="flex-1 min-w-0 truncate">{{ $m->name }}</span>
                                                                        <span class="text-xs font-bold text-slate-500 shrink-0">{{ $money($m->price) }}/{{ $m->unit }}</span>
                                                                    </button>
                                                                @endforeach
                                                            @empty
                                                                <p class="p-3 qt-muted">Nada encontrado. Use “Linha livre”.</p>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($settings['extras']))
                                        <div class="crm-chips">
                                            <span class="qt-muted self-center mr-1">Acréscimos:</span>
                                            @foreach($settings['extras'] as $e => $ex)
                                                @php $on = collect($it['extras'] ?? [])->contains('n', $ex['n']); @endphp
                                                <button type="button" wire:click="toggleExtra({{ $i }}, {{ $e }})" class="crm-chip {{ $on ? 'is-on' : '' }}">{!! $on ? '<i class="bi bi-check2"></i>' : '<i class="bi bi-plus"></i>' !!}{{ $ex['n'] }} +{{ $money($ex['v']) }}</button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="qt-price-box">
                                        <div class="qt-kv"><span>Custo unid.</span><b>{{ $money($cost) }}</b></div>
                                        <label class="qt-kv"><span>Margem %</span>
                                            <input type="number" min="0" max="90" step="1" wire:model.blur="items.{{ $i }}.margin" class="crm-input !py-1 !text-sm font-bold">
                                        </label>
                                        <div class="qt-kv"><span>Sugerido</span><b>{{ $money($sug) }}</b>
                                            @if(!empty($it['manual']) && abs($sug - (float) $it['price']) >= 0.01)
                                                <button type="button" wire:click="useSuggested({{ $i }})" class="text-left text-[11px] font-bold text-indigo-600 dark:text-indigo-300">Usar este</button>
                                            @endif
                                        </div>
                                        <label class="qt-kv"><span>Preço final unid.</span>
                                            <input type="number" min="0" step="0.01" wire:model.blur="items.{{ $i }}.price" class="crm-input !py-1 !text-sm font-black {{ $low ? '!border-rose-400' : '' }}">
                                        </label>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                        <span class="qt-pill qt-tone" style="--t: {{ $pr['profit'] >= 0 ? '#10b981' : '#f43f5e' }}"><i class="bi bi-graph-up-arrow"></i>Lucro {{ $money($pr['profit']) }} ({{ number_format($pr['pct'], 0) }}%)</span>
                                        @if((int) $it['qty'] > 1)<span class="qt-muted">× {{ (int) $it['qty'] }} = {{ $money((float) $it['price'] * (int) $it['qty']) }}</span>@endif
                                        @if($low)<span class="qt-pill qt-tone" style="--t: #f43f5e"><i class="bi bi-exclamation-triangle"></i>Abaixo do custo + {{ rtrim(rtrim(number_format($minMarkup, 1, ',', ''), '0'), ',') }}%</span>@endif
                                        @if($cost <= 0)<span class="qt-muted">Sem custo: monte a ficha para ver o preço sugerido.</span>@endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addItem" class="crm-btn justify-center border-dashed"><i class="bi bi-plus-lg"></i>Adicionar outro item</button>
                    </div>
                </section>

                {{-- ===== Condições ===== --}}
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-card-checklist"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Prazo, validade e pagamento</h2></div>
                    </div>
                    <div class="qt-sec">
                        <div class="qt-3 qt-terms">
                            <label class="crm-label">Fica pronto em (dias)
                                <input type="number" min="0" wire:model.blur="productionDays" class="crm-input" placeholder="Ex.: 15">
                            </label>
                            <label class="crm-label">Válido até
                                <input type="date" wire:model="validUntil" class="crm-input">
                            </label>
                            <label class="crm-label col-span-2 md:col-span-1">Pagamento
                                <input type="text" wire:model="paymentTerms" list="qt-pay" class="crm-input" placeholder="Ex.: Pix ou cartão em até 3x" maxlength="255">
                                <datalist id="qt-pay"><option value="Pix, dinheiro ou cartão"><option value="Pix com desconto ou cartão em até 3x"><option value="Metade para começar, metade na entrega"></datalist>
                            </label>
                            <label class="crm-label">Desconto (R$)
                                <input type="number" min="0" step="0.01" wire:model.blur="discount" class="crm-input" placeholder="0,00">
                            </label>
                            <label class="crm-label">Entrega cobrada (R$)
                                <input type="number" min="0" step="0.01" wire:model.blur="deliveryFee" class="crm-input" placeholder="0,00">
                            </label>
                        </div>
                        <div class="qt-2 qt-notes">
                        <label class="crm-label">Observações para o cliente
                            <textarea wire:model="clientNotes" rows="2" class="crm-input" placeholder="Ex.: Cores conferidas pela foto; pode variar um pouco no tom." maxlength="3000"></textarea>
                        </label>
                        <label class="crm-label"><span>Anotações só suas <span class="qt-private"><i class="bi bi-eye-slash"></i>o cliente não vê</span></span>
                            <textarea wire:model="adminNotes" rows="2" class="crm-input" placeholder="Fornecedor, onde comprar, lembretes" maxlength="3000"></textarea>
                        </label>
                        </div>
                    </div>
                </section>

                @if($kind === 'reforma' && $quote)
                    <section class="crs-card">
                        <div class="crs-head">
                            <span class="crs-head-icon bg-gradient-to-br from-violet-500 to-fuchsia-600"><i class="bi bi-images"></i></span>
                            <div class="min-w-0 flex-1"><h2 class="crs-title">Fotos de depois</h2><p class="crs-sub">Quando terminar a reforma: o cliente vê o antes e depois no link</p></div>
                        </div>
                        <div class="qt-sec">
                            <div class="qt-photos">
                                @foreach($afterPhotos as $p => $ph)
                                    <div class="qt-photo" wire:key="aph-{{ $p }}"><img src="{{ Quote::photoUrl($ph) }}" alt="Depois"><button type="button" wire:click="removeAfterPhoto({{ $p }})"><i class="bi bi-x-lg"></i></button></div>
                                @endforeach
                                <label class="qt-photo-add"><i class="bi bi-camera text-lg"></i><span>Foto</span><input type="file" accept="image/*" multiple class="hidden" wire:model="afterUploads"></label>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ===== Resumo ===== --}}
            <aside class="qt-side flex flex-col gap-4">
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-receipt"></i></span>
                        <div class="min-w-0 flex-1"><h2 class="crs-title">Resumo</h2><p class="crs-sub">O que o cliente vai ver</p></div>
                    </div>
                    <div class="qt-sec !gap-2">
                        @foreach($items as $it)
                            @if(trim((string) $it['name']) !== '')
                                <div class="qt-sum-row"><span class="truncate">{{ (int) $it['qty'] > 1 ? (int) $it['qty'] . '× ' : '' }}{{ $it['name'] }}</span><b class="shrink-0">{{ $money((float) $it['price'] * max(1, (int) $it['qty'])) }}</b></div>
                            @endif
                        @endforeach
                        @if((float) $discount > 0)<div class="qt-sum-row"><span>Desconto</span><b class="!text-emerald-600">− {{ $money($discount) }}</b></div>@endif
                        @if((float) $deliveryFee > 0)<div class="qt-sum-row"><span>Entrega</span><b>{{ $money($deliveryFee) }}</b></div>@endif
                        <div class="flex items-end justify-between pt-2 mt-1 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-sm font-bold text-slate-500">Total</span>
                            <span class="qt-sum-total">{{ $money($total) }}</span>
                        </div>
                        <div class="mt-2 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 p-3 flex flex-col gap-1.5">
                            <span class="qt-private self-start"><i class="bi bi-eye-slash"></i>só você vê</span>
                            <div class="qt-sum-row"><span>Custo total</span><b>{{ $money($costTotal) }}</b></div>
                            <div class="qt-sum-row"><span>Lucro</span><b class="{{ $profit >= 0 ? '!text-emerald-600' : '!text-rose-600' }}">{{ $money($profit) }}{{ ($subtotal - (float) $discount) > 0 ? ' (' . number_format($profit / max(0.01, $subtotal - (float) $discount) * 100, 0) . '%)' : '' }}</b></div>
                        </div>
                        <button type="button" data-mobile-save="Salvar orçamento" wire:click="save" class="crm-btn crm-btn-primary justify-center mt-2" wire:loading.attr="disabled" wire:target="save"><i class="bi bi-check2"></i>Salvar e ver</button>
                        <button type="button" wire:click="save('stay')" class="crm-btn justify-center" wire:loading.attr="disabled" wire:target="save"><i class="bi bi-floppy"></i>Salvar e continuar aqui</button>
                        @if(!$quote || !in_array($quote->status, ['convertido', 'recusado'], true))
                            <div class="qt-sale-box">
                                <p><i class="bi bi-cart-check"></i><span><b>O cliente confirmou?</b> Salve e já crie a venda com estes itens e preços.</span></p>
                                <button type="button" wire:click="save('venda')" wire:confirm="O cliente confirmou? Vou salvar e criar a venda com estes itens e preços." class="crm-btn qt-btn-sale justify-center w-full" wire:loading.attr="disabled" wire:target="save" data-testid="save-to-sale"><i class="bi bi-cart-plus"></i>Confirmar e criar venda</button>
                            </div>
                        @elseif($quote->sale_id)
                            <a href="{{ route('sales.show', $quote->sale_id) }}" wire:navigate class="crm-btn justify-center"><i class="bi bi-receipt"></i>Ver venda #{{ $quote->sale_id }}</a>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <x-quotes-css />
</div>
