<div class="furniture-kit-page">
    <x-product-page-header :title="$rootId ? 'Editar conjunto' : 'Novo conjunto'" icon="bi-boxes" active="kit"
        subtitle="Junte as peças (mesa, banquetas...) e cada combinação de cor vira uma opção do conjunto, ligada às variações">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-500/10 dark:text-fuchsia-300"><i class="bi bi-puzzle"></i>{{ count($pieces) }} {{ count($pieces) === 1 ? 'peça' : 'peças' }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-diagram-3"></i>{{ $onCount }} {{ $onCount === 1 ? 'opção' : 'opções' }}</span>
        </x-slot:meta>
    </x-product-page-header>

    @include('livewire.products.partials.ep-styles')

    <div class="ep-grid">
        <div class="ep-col">
            {{-- Peças --}}
            <section class="ep-card" data-testid="fk-pieces">
                <div class="ep-card-h">
                    <span class="ep-ico pink"><i class="bi bi-puzzle"></i></span>
                    <div class="flex-1 min-w-0"><h3>Peças do conjunto</h3><p>Busque o produto de cada peça e diga quantas vão no conjunto</p></div>
                </div>
                <div class="ep-card-b space-y-3">
                    <div class="relative">
                        <i class="bi bi-search fk-search-ico"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="ep-input fk-search" placeholder="Nome ou código da peça (ex.: banqueta 54, MBR)" data-testid="fk-search">
                        @if($results)
                            <div class="fk-results">
                                @foreach($results as $r)
                                    <button type="button" wire:click="addPiece({{ $r['id'] }})" class="fk-result" wire:key="res-{{ $r['id'] }}">
                                        <img src="{{ $r['image'] }}" alt="" loading="lazy">
                                        <span class="min-w-0 flex-1"><b>{{ $r['name'] }}</b><small>{{ $r['code'] }} · {{ $r['variants'] }} {{ $r['variants'] === 1 ? 'opção' : 'variações' }}</small></span>
                                        <i class="bi bi-plus-circle-fill"></i>
                                    </button>
                                @endforeach
                            </div>
                        @elseif(mb_strlen(trim($search)) >= 2)
                            <p class="ep-hint mt-2"><i class="bi bi-info-circle"></i>Nenhuma peça com esse nome.</p>
                        @endif
                    </div>

                    <div class="space-y-3">
                    @forelse($pieces as $pi => $piece)
                        @php $selCount = count($piece['selected']); @endphp
                        <div class="fk-piece" wire:key="piece-{{ $piece['family'] }}">
                            <div class="fk-piece-h">
                                <span class="fk-num">{{ $pi + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <b class="block truncate">{{ $piece['name'] }}</b>
                                    <small>{{ $selCount }} de {{ count($piece['variants']) }} cores no conjunto</small>
                                </div>
                                <label class="fk-qty" title="Quantas vão no conjunto">
                                    <span>Qtd.</span>
                                    <input type="number" min="1" max="20" wire:model.live.debounce.400ms="pieces.{{ $pi }}.qty" class="ep-input">
                                </label>
                                <button type="button" wire:click="removePiece({{ $pi }})" class="ep-x" title="Tirar a peça"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <div class="fk-piece-tools">
                                <label class="fk-label-edit"><span>Nome no conjunto</span><input type="text" wire:model.live.debounce.500ms="pieces.{{ $pi }}.label" class="ep-input" maxlength="30"></label>
                                <button type="button" wire:click="selectAll({{ $pi }}, true)" class="fk-link">Todas</button>
                                <button type="button" wire:click="selectAll({{ $pi }}, false)" class="fk-link">Nenhuma</button>
                            </div>
                            <div class="fk-vars">
                                @foreach($piece['variants'] as $v)
                                    @php $on = in_array($v['id'], $piece['selected'], true); @endphp
                                    <button type="button" wire:click="toggleVariant({{ $pi }}, {{ $v['id'] }})" class="fk-var {{ $on ? 'on' : '' }}" wire:key="v-{{ $v['id'] }}">
                                        <i class="bi {{ $on ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        <span class="min-w-0"><b>{{ $v['value'] }}</b><small>R$ {{ number_format($v['sale'], 2, ',', '.') }} · {{ $v['made'] && $v['stock'] <= 0 ? 'encomenda' : $v['stock'] . ' un.' }}</small></span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="fk-empty">
                            <i class="bi bi-lamp"></i>
                            <b>Comece pela peça principal</b>
                            <small>Ex.: busque "mesinha bistrô", depois "banqueta 54" e coloque 2.</small>
                        </div>
                    @endforelse
                    </div>

                    @if(count($pieces) > 1)
                        <div class="ep-ch-mode" role="group">
                            <button type="button" wire:click="$set('mode', 'cor')" class="{{ $mode === 'cor' ? 'on' : '' }}"><i class="bi bi-palette"></i> Mesma cor de ferragem</button>
                            <button type="button" wire:click="$set('mode', 'todas')" class="{{ $mode === 'todas' ? 'on' : '' }}"><i class="bi bi-grid-3x3"></i> Todas as combinações</button>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Opções --}}
            <section class="ep-card" data-testid="fk-options">
                <div class="ep-card-h">
                    <span class="ep-ico"><i class="bi bi-diagram-3"></i></span>
                    <div class="flex-1 min-w-0"><h3>Opções do conjunto</h3><p>Uma para cada combinação. Preço = soma das peças menos o desconto; dá para mudar à mão.</p></div>
                    @if($options)
                        <button type="button" wire:click="allOptions(true)" class="fk-link">Ligar todas</button>
                        <button type="button" wire:click="allOptions(false)" class="fk-link">Desligar</button>
                    @endif
                </div>
                <div class="ep-card-b">
                    @error('options')<p class="ep-err mb-3" data-testid="fk-error"><i class="bi bi-exclamation-triangle"></i> {{ $message }}</p>@enderror
                    @if($onCount > \App\Livewire\Products\FurnitureKit::MAX_OPTIONS)
                        <p class="ep-err mb-3"><i class="bi bi-exclamation-triangle"></i> {{ $onCount }} opções ligadas; o máximo é {{ \App\Livewire\Products\FurnitureKit::MAX_OPTIONS }}. Desmarque cores das peças ou desligue opções.</p>
                    @endif
                    <div class="fk-opts">
                        @forelse($options as $oi => $o)
                            <div class="fk-opt {{ $o['on'] ? '' : 'off' }} {{ !empty($o['orphan']) ? 'orphan' : '' }}" wire:key="opt-{{ $o['sig'] }}" data-testid="fk-opt">
                                <button type="button" wire:click="toggleOption({{ $oi }})" class="fk-opt-check" title="{{ $o['on'] ? 'Desligar' : 'Ligar' }}"><i class="bi {{ $o['on'] ? 'bi-check-lg' : '' }}"></i></button>
                                @if($o['image'])<img src="{{ $o['image'] }}" alt="" loading="lazy" class="fk-opt-img">@endif
                                <div class="min-w-0 flex-1">
                                    <b class="fk-opt-name">{{ $o['value'] }}</b>
                                    <small>{{ $o['code'] }} · peças R$ {{ number_format($o['sum'], 2, ',', '.') }} · {{ ($o['made'] ?? false) && $o['stock'] <= 0 ? 'sob encomenda' : $o['stock'] . ' ' . ($o['stock'] === 1 ? 'conjunto' : 'conjuntos') }}@if($o['existing_id']) · <span class="text-emerald-600">cadastrada</span>@endif @if(!empty($o['orphan'])) · <span class="text-amber-600">peça mudou</span>@endif</small>
                                </div>
                                <label class="fk-price">
                                    <span>R$</span>
                                    <input type="text" inputmode="decimal" value="{{ $o['sale'] }}" wire:change="setSale({{ $oi }}, $event.target.value)" class="ep-input" aria-label="Preço de venda">
                                </label>
                            </div>
                        @empty
                            <p class="ep-hint"><i class="bi bi-info-circle"></i>As opções aparecem quando você escolher as peças.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <aside class="ep-col ep-side">
            <section class="ep-card">
                <div class="ep-card-h">
                    <span class="ep-ico amber"><i class="bi bi-card-text"></i></span>
                    <div class="flex-1 min-w-0"><h3>Dados do conjunto</h3><p>Nome, código e preço</p></div>
                </div>
                <div class="ep-card-b space-y-3">
                    <div>
                        <label class="ep-label">Nome</label>
                        <input type="text" wire:model.blur="name" class="ep-input" placeholder="Conjunto Bistrô Mesinha e 2 Banquetas" data-testid="fk-name">
                        @error('name')<p class="ep-err mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="ep-label">Código</label>
                            <input type="text" wire:model.blur="code" class="ep-input uppercase" maxlength="20" placeholder="CBR" data-testid="fk-code">
                            @error('code')<p class="ep-err mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ep-label">Desconto %</label>
                            <div class="flex gap-1">
                                <input type="text" inputmode="decimal" wire:model.blur="discount" class="ep-input" placeholder="0">
                                <button type="button" wire:click="applyDiscount" class="ep-btn ep-btn-soft shrink-0" style="padding:.45rem .6rem" title="Aplicar o desconto em todas"><i class="bi bi-arrow-repeat"></i></button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="ep-label">Categoria</label>
                        <select wire:model="category_id" class="ep-input">
                            <option value="">Conjuntos</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id_category }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ep-label">Descrição</label>
                        <textarea wire:model.blur="description" rows="4" class="ep-input" placeholder="O que vem no conjunto, medidas..."></textarea>
                    </div>
                    <div class="ep-mto {{ $made_to_order ? 'on' : '' }}">
                        <label class="ep-mto-row">
                            <input type="checkbox" wire:model.live="made_to_order">
                            <span><b>Sob encomenda</b><small>Todas as opções podem ser pedidas mesmo sem estoque das peças.</small></span>
                        </label>
                        @if($made_to_order)
                            <div class="ep-mto-days"><label class="ep-label">Prazo (dias)</label><input type="number" min="1" max="365" wire:model.blur="production_days" class="ep-input"></div>
                        @endif
                    </div>
                    <div>
                        <label class="ep-label">Foto do conjunto</label>
                        <input type="file" wire:model="photo" accept="image/*" class="ep-input">
                        <p class="ep-hint mt-1"><i class="bi bi-info-circle"></i>Sem foto, cada opção usa a foto da primeira peça.</p>
                    </div>
                    <p class="ep-hint"><i class="bi bi-box-seam"></i>Estoque: o conjunto não tem estoque próprio. Vale a peça que acaba primeiro, e vender o conjunto baixa o estoque das peças.</p>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" class="ep-btn ep-btn-primary w-full" data-testid="fk-save">
                        <span wire:loading.remove wire:target="save"><i class="bi bi-check2-circle"></i> Salvar conjunto ({{ $onCount }})</span>
                        <span wire:loading wire:target="save"><i class="bi bi-arrow-repeat animate-spin"></i> Salvando...</span>
                    </button>
                    <a href="{{ $rootId ? route('products.kit.edit', ['kit' => $rootId, 'revenda' => 1]) : route('products.kit.create', ['revenda' => 1]) }}" class="fk-link block text-center">Abrir na página de kit de revenda</a>
                </div>
            </section>
        </aside>
    </div>

    <style>
        .furniture-kit-page .fk-search { padding-left: 2.3rem; }
        .furniture-kit-page .fk-search-ico { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .furniture-kit-page .fk-results { margin-top: .4rem; display: grid; gap: .3rem; border-radius: 1rem; border: 1px solid rgba(148,163,184,.3); padding: .35rem; background: #fff; }
        .dark .furniture-kit-page .fk-results { background: #0f172a; border-color: rgba(71,85,105,.6); }
        .furniture-kit-page .fk-result { display: flex; align-items: center; gap: .6rem; padding: .45rem .55rem; border-radius: .75rem; text-align: left; }
        .furniture-kit-page .fk-result:hover { background: rgba(99,102,241,.08); }
        .furniture-kit-page .fk-result img { width: 2.6rem; height: 2.6rem; border-radius: .6rem; object-fit: cover; flex-shrink: 0; background: #f1f5f9; }
        .furniture-kit-page .fk-result b { display: block; font-size: .86rem; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dark .furniture-kit-page .fk-result b { color: #f1f5f9; }
        .furniture-kit-page .fk-result small { font-size: .72rem; color: #64748b; }
        .furniture-kit-page .fk-result .bi-plus-circle-fill { color: #db2777; font-size: 1.2rem; }
        .furniture-kit-page .fk-piece { border-radius: 1rem; border: 1px solid rgba(148,163,184,.35); background: rgba(248,250,252,.7); padding: .75rem; display: grid; gap: .6rem; }
        .dark .furniture-kit-page .fk-piece { background: rgba(30,41,59,.5); border-color: rgba(71,85,105,.6); }
        .furniture-kit-page .fk-piece-h { display: flex; align-items: center; gap: .6rem; }
        .furniture-kit-page .fk-piece-h b { font-size: .92rem; color: #0f172a; }
        .dark .furniture-kit-page .fk-piece-h b { color: #f1f5f9; }
        .furniture-kit-page .fk-piece-h small { font-size: .72rem; color: #64748b; }
        .furniture-kit-page .fk-num { width: 1.8rem; height: 1.8rem; border-radius: .6rem; display: grid; place-items: center; flex-shrink: 0; background: linear-gradient(135deg, #ec4899, #a855f7); color: #fff; font-weight: 900; font-size: .85rem; }
        .furniture-kit-page .fk-qty { display: flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .furniture-kit-page .fk-qty input { width: 4rem; padding: .4rem .5rem; text-align: center; }
        .furniture-kit-page .fk-piece-tools { display: flex; align-items: flex-end; gap: .6rem; flex-wrap: wrap; }
        .furniture-kit-page .fk-label-edit { flex: 1; min-width: 10rem; display: grid; gap: .2rem; font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
        .furniture-kit-page .fk-label-edit input { padding: .4rem .6rem; font-size: .84rem; text-transform: none; letter-spacing: 0; }
        .furniture-kit-page .fk-link { font-size: .76rem; font-weight: 700; color: #4f46e5; padding: .3rem .4rem; }
        .dark .furniture-kit-page .fk-link { color: #a5b4fc; }
        .furniture-kit-page .fk-vars { display: grid; grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr)); gap: .35rem; }
        .furniture-kit-page .fk-var { display: flex; align-items: center; gap: .5rem; padding: .45rem .6rem; border-radius: .8rem; border: 1.5px solid rgba(148,163,184,.4); background: #fff; text-align: left; opacity: .6; }
        .furniture-kit-page .fk-var.on { opacity: 1; border-color: #db2777; background: rgba(236,72,153,.06); }
        .dark .furniture-kit-page .fk-var { background: #0f172a; }
        .furniture-kit-page .fk-var > i { color: #db2777; flex-shrink: 0; }
        .furniture-kit-page .fk-var b { display: block; font-size: .78rem; color: #0f172a; line-height: 1.25; }
        .dark .furniture-kit-page .fk-var b { color: #f1f5f9; }
        .furniture-kit-page .fk-var small { font-size: .68rem; color: #64748b; }
        .furniture-kit-page .fk-empty { display: grid; justify-items: center; gap: .2rem; padding: 1.4rem; border-radius: 1rem; border: 2px dashed rgba(148,163,184,.4); text-align: center; color: #64748b; }
        .furniture-kit-page .fk-empty i { font-size: 1.8rem; color: #db2777; }
        .furniture-kit-page .fk-empty b { color: #0f172a; }
        .dark .furniture-kit-page .fk-empty b { color: #f1f5f9; }
        .furniture-kit-page .fk-empty small { font-size: .76rem; }
        .furniture-kit-page .fk-opts { display: grid; gap: .4rem; }
        .furniture-kit-page .fk-opt { display: flex; align-items: center; gap: .6rem; padding: .5rem .6rem; border-radius: .9rem; border: 1px solid rgba(148,163,184,.3); background: #fff; }
        .dark .furniture-kit-page .fk-opt { background: #0f172a; border-color: rgba(71,85,105,.6); }
        .furniture-kit-page .fk-opt.off { opacity: .5; }
        .furniture-kit-page .fk-opt.orphan { border-style: dashed; border-color: #f59e0b; }
        .furniture-kit-page .fk-opt-check { width: 1.5rem; height: 1.5rem; flex-shrink: 0; border-radius: .45rem; border: 2px solid #cbd5e1; display: grid; place-items: center; color: #fff; }
        .furniture-kit-page .fk-opt:not(.off) .fk-opt-check { background: #4f46e5; border-color: #4f46e5; }
        .furniture-kit-page .fk-opt-img { width: 2.6rem; height: 2.6rem; border-radius: .6rem; object-fit: cover; flex-shrink: 0; background: #f1f5f9; }
        .furniture-kit-page .fk-opt-name { display: block; font-size: .82rem; color: #0f172a; line-height: 1.3; }
        .dark .furniture-kit-page .fk-opt-name { color: #f1f5f9; }
        .furniture-kit-page .fk-opt small { display: block; font-size: .7rem; color: #64748b; }
        .furniture-kit-page .fk-price { display: flex; align-items: center; gap: .25rem; flex-shrink: 0; font-size: .76rem; font-weight: 800; color: #64748b; }
        .furniture-kit-page .fk-price input { width: 6.5rem; padding: .4rem .55rem; text-align: right; font-weight: 800; }
        @media (max-width: 640px) {
            .furniture-kit-page .fk-opt { flex-wrap: wrap; }
            .furniture-kit-page .fk-opt-img { display: none; }
            .furniture-kit-page .fk-opt > .min-w-0 { flex-basis: calc(100% - 2.3rem); }
            .furniture-kit-page .fk-price { margin-left: 2.1rem; }
            .furniture-kit-page .fk-vars { grid-template-columns: minmax(0, 1fr); }
            .furniture-kit-page .ep-ch-mode { display: grid; grid-template-columns: 1fr; width: 100%; }
        }
    </style>
</div>
