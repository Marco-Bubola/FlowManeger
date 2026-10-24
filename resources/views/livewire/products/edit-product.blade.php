<div class="product-form-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16">
    <x-product-page-header title="Editar produto" :product="$product" :subtitle="e($product->name) . ' · ' . e($product->product_code)" active="">
        <x-slot:actions>
            <button wire:click="toggleTips" type="button"
                class="hidden sm:inline-flex items-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 text-sm font-semibold text-amber-600 dark:text-amber-300 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                <i class="bi bi-lightbulb"></i>Dicas
            </button>
            <button type="button" @click="$dispatch('open-product-share', { productId: {{ $product->id }} })" title="Enviar no WhatsApp"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm text-white bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700">
            <i class="bi bi-whatsapp"></i><span class="hidden sm:inline">WhatsApp</span>
        </button>
            @if(($product->tipo ?? 'simples') !== 'kit')
            <a href="{{ route('products.breakdown', $product) }}" title="Desmembrar kit: separar nos produtos que vêm dentro"
           class="hidden sm:inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70">
            <i class="bi bi-scissors text-orange-500"></i>Desmembrar
        </a>
            @endif
            <a href="{{ route('products.index') }}"
                class="hidden sm:inline-flex items-center gap-1.5 rounded-xl bg-white/85 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 shadow-sm transition">
                <i class="bi bi-x-lg"></i>Cancelar
            </a>
            <button data-mobile-save type="submit" form="edit-product-form" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="update"><i class="bi bi-check2-circle mr-1"></i>Salvar alterações</span>
                <span wire:loading wire:target="update"><i class="bi bi-arrow-repeat animate-spin mr-1"></i>Salvando...</span>
            </button>
        </x-slot:actions>
    </x-product-page-header>

    @include('livewire.products.partials.product-form', ['isEdit' => true])

    {{-- ══════════ VARIAÇÕES ══════════ --}}
    @php
        $familyStock = $product->stock_quantity + $variants->sum('stock_quantity');
        $isVariant = (bool) $product->parent_id;
        $withStock = collect([$product])->merge($variants)->where('stock_quantity', '>', 0)->count();
        $vxImg = fn ($p) => asset('storage/products/' . ($p->image ?: 'product-placeholder.png'));
        $vxMoney = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    @endphp
    <section class="vx" id="variacoes">
        <button type="button" wire:click="toggleVariationsPanel" class="vx-h">
            <span class="vx-h-ico"><i class="bi bi-diagram-3-fill"></i></span>
            <span class="min-w-0 flex-1 text-left">
                <span class="vx-h-t">Variações
                    @if($variants->count() > 0)<span class="vx-pill">{{ $variants->count() + 1 }} no grupo</span>@endif
                    @if(!$isVariant && $sameCodeCount > 0)<span class="vx-pill is-code"><i class="bi bi-upc-scan"></i>{{ $sameCodeCount }} com o mesmo código</span>@endif
                </span>
                <span class="vx-h-s">Mesmo item com preço, cor ou tamanho diferente. Cada um tem seu estoque.</span>
            </span>
            @if($variants->count() > 0)
                <span class="vx-h-stock"><small>Estoque do grupo</small><b>{{ $familyStock }}</b><small>{{ $withStock }} de {{ $variants->count() + 1 }} com estoque</small></span>
            @endif
            <span class="vx-h-chev"><i class="bi {{ $showVariationsPanel ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i></span>
        </button>

        @if($showVariationsPanel)
        <div class="vx-b">
            @if($isVariant && $product->parent)
            <div class="vx-note">
                <i class="bi bi-diagram-3"></i>
                <span class="flex-1 min-w-0">Este produto é a variação <strong>{{ $product->variation_value }}</strong> de <strong>{{ $product->parent->name }}</strong>. Para ligar ou tirar outras variações, use o produto principal.</span>
                <a href="{{ route('products.edit', $product->parent) }}" class="vx-btn is-primary">Abrir principal</a>
                <button type="button" wire:click="detachSelf" wire:confirm="Tirar este produto do grupo? Ele continua cadastrado, só deixa de ser variação." class="vx-btn is-danger">Sair do grupo</button>
            </div>
            @endif

            {{-- O grupo: este produto + as variações, em cards iguais aos da lista de produtos --}}
            <div class="vx-sec-h">
                <span><i class="bi bi-collection-fill"></i> {{ $variants->count() ? 'No grupo' : 'Este produto' }}</span>
                @if(!$variants->count())<small>Nenhuma variação ainda. Escolha abaixo.</small>@endif
            </div>
            <div class="vx-grid">
                @foreach(collect([$product])->merge($variants) as $vp)
                    @php $self = $vp->id === $product->id; @endphp
                    <div class="vx-card {{ $self ? 'is-self' : '' }}" wire:key="vx-g-{{ $vp->id }}">
                        <div class="vx-img">
                            <img src="{{ $vxImg($vp) }}" alt="{{ $vp->name }}" loading="lazy">
                            <span class="vx-code" title="Código"><i class="bi bi-upc-scan"></i>{{ $vp->product_code }}</span>
                            <span class="vx-qty {{ $vp->stock_quantity > 0 ? '' : 'is-out' }}" title="Estoque"><i class="bi bi-stack"></i>{{ $vp->stock_quantity }}</span>
                            @if($self)
                                <span class="vx-tag is-self"><i class="bi bi-geo-alt-fill"></i>Este</span>
                            @elseif($vp->is_variation_parent)
                                <span class="vx-tag">Principal</span>
                            @elseif($vp->variation_value)
                                <span class="vx-tag" title="{{ $vp->variation_value }}">{{ $vp->variation_value }}</span>
                            @endif
                        </div>
                        <div class="vx-info">
                            <p class="vx-name" title="{{ $vp->name }}">{{ $vp->name }}</p>
                            <div class="vx-prices">
                                <span class="vx-cost" title="Custo"><i class="bi bi-tag"></i>{{ $vxMoney($vp->price) }}</span>
                                <span class="vx-sale" title="Venda"><i class="bi bi-currency-dollar"></i>{{ $vxMoney($vp->price_sale) }}</span>
                            </div>
                        </div>
                        @unless($self)
                        <div class="vx-act">
                            <a href="{{ route('products.edit', $vp) }}" title="Editar"><i class="bi bi-pencil-square"></i><span>Editar</span></a>
                            @unless($isVariant)
                            <button type="button" title="Trocar o nome da variação"
                                x-on:click="const v = prompt('Nome da variação (ex.: 30 cm, Preto)', @js((string) $vp->variation_value)); if (v !== null && v.trim() !== '') $wire.renameVariation({{ $vp->id }}, v)"><i class="bi bi-tag"></i><span>Nome</span></button>
                            @endunless
                            @unless($isVariant)
                            <button type="button" wire:click="detachVariation({{ $vp->id }})" wire:confirm="Tirar esta variação do grupo? Ela continua cadastrada como produto separado." class="is-danger" title="Tirar do grupo"><i class="bi bi-x-lg"></i><span>Tirar</span></button>
                            @endunless
                        </div>
                        @endunless
                    </div>
                @endforeach
            </div>

            {{-- Gerar variações de uma vez: tamanhos (e cores) da mesma peça --}}
            @unless($isVariant)
            @php $gridPreview = $this->gridPreviewList(); @endphp
            <div class="vx-gen" id="gerar-variacoes">
                <div class="vx-sug-h">
                    <div class="min-w-0">
                        <p class="vx-sug-t"><i class="bi bi-grid-3x3-gap"></i> Gerar variações</p>
                        <p class="vx-sug-s">Escreva os tamanhos (e as cores, se tiver) separados por vírgula. Cada combinação vira uma variação com a descrição, a ficha e a embalagem deste produto.</p>
                    </div>
                </div>
                <div class="vx-gen-grid">
                    <div>
                        <label class="ep-label" for="grid-values"><span>Tamanhos ou modelos</span></label>
                        <input id="grid-values" type="text" wire:model.live.debounce.400ms="gridValues" class="ep-input {{ $errors->has('gridValues') ? 'is-err' : '' }}" placeholder="Ex: 30 cm, 40 cm, 50 cm">
                        @error('gridValues')<p class="ep-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="ep-label" for="grid-colors"><span>Cores (opcional)</span></label>
                        <input id="grid-colors" type="text" wire:model.live.debounce.400ms="gridColors" class="ep-input" placeholder="Ex: Preto, Branco, Ouro velho">
                    </div>
                    <div>
                        <label class="ep-label" for="grid-attr"><span>Nome da variação</span></label>
                        <input id="grid-attr" type="text" list="grid-attr-list" wire:model.blur="gridAttribute" class="ep-input" maxlength="40" placeholder="{{ trim($gridColors) !== '' && trim($gridValues) !== '' ? 'Tamanho e cor' : 'Tamanho' }}">
                        <datalist id="grid-attr-list"><option value="Tamanho"><option value="Cor"><option value="Tamanho e cor"><option value="Modelo"><option value="Vagas"><option value="Acabamento"></datalist>
                    </div>
                    @if(!$variants->count())
                    <div>
                        <label class="ep-label" for="grid-self"><span>Este produto é o</span></label>
                        <input id="grid-self" type="text" wire:model.blur="gridSelfValue" class="ep-input" maxlength="120" placeholder="Ex: 20 cm (opcional)">
                    </div>
                    @endif
                    <div>
                        <label class="ep-label" for="grid-price"><span>Custo de cada</span></label>
                        <input id="grid-price" type="text" inputmode="decimal" wire:model.blur="gridPrice" class="ep-input" placeholder="{{ number_format((float) $product->price, 2, ',', '.') }}">
                    </div>
                    <div>
                        <label class="ep-label" for="grid-sale"><span>Venda de cada</span></label>
                        <input id="grid-sale" type="text" inputmode="decimal" wire:model.blur="gridPriceSale" class="ep-input" placeholder="{{ number_format((float) $product->price_sale, 2, ',', '.') }}">
                    </div>
                    <div>
                        <label class="ep-label" for="grid-stock"><span>Estoque de cada</span></label>
                        <input id="grid-stock" type="number" min="0" wire:model.blur="gridStock" class="ep-input">
                    </div>
                </div>
                @if(count($gridPreview))
                    <div class="vx-gen-chips">
                        @foreach(array_slice($gridPreview, 0, 40) as $gv)
                            <span>{{ $gv }}</span>
                        @endforeach
                        @if(count($gridPreview) > 40)<span class="is-more">+{{ count($gridPreview) - 40 }}</span>@endif
                    </div>
                @endif
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="generateVariations" wire:loading.attr="disabled" wire:target="generateVariations" class="vx-btn is-primary" @disabled(!count($gridPreview)) data-testid="grid-generate">
                        <i class="bi bi-magic"></i>{{ count($gridPreview) ? 'Criar ' . count($gridPreview) . (count($gridPreview) === 1 ? ' variação' : ' variações') : 'Criar variações' }}
                    </button>
                    <span class="vx-sug-s">Preço e estoque vazios: usa os deste produto e 0.</span>
                </div>
            </div>
            @endunless

            {{-- Sugestões: primeiro os de mesmo código, depois os de mesmo nome --}}
            @unless($isVariant)
            <div class="vx-sug">
                <div class="vx-sug-h">
                    <div class="min-w-0">
                        <p class="vx-sug-t"><i class="bi bi-link-45deg"></i> Vincular variação</p>
                        <p class="vx-sug-s">{{ trim($linkSearch) === '' ? 'Sugeridos: mesmo código primeiro, depois nome parecido.' : 'Resultados da busca' }}</p>
                    </div>
                    <div class="vx-search">
                        <i class="bi bi-search"></i>
                        <input type="search" wire:model.live.debounce.300ms="linkSearch" placeholder="Buscar por nome ou código">
                    </div>
                    @if($sameCodeCount > 1 && trim($linkSearch) === '')
                        <button type="button" wire:click="linkAllSameCode" wire:loading.attr="disabled" wire:target="linkAllSameCode"
                            wire:confirm="Vincular os {{ $sameCodeCount }} produtos com o mesmo código como variações deste?" class="vx-btn is-primary">
                            <i class="bi bi-link"></i>Vincular os {{ $sameCodeCount }} de mesmo código
                        </button>
                    @endif
                </div>

                <div class="vx-grid" wire:loading.class="opacity-60" wire:target="linkSearch">
                    @forelse($linkSearchResults as $lp)
                        <div class="vx-card {{ $lp->match === 'code' ? 'is-match' : '' }}" wire:key="vx-s-{{ $lp->id }}">
                            <div class="vx-img">
                                <img src="{{ $vxImg($lp) }}" alt="{{ $lp->name }}" loading="lazy">
                                <span class="vx-code" title="Código"><i class="bi bi-upc-scan"></i>{{ $lp->product_code }}</span>
                                <span class="vx-qty {{ $lp->stock_quantity > 0 ? '' : 'is-out' }}" title="Estoque"><i class="bi bi-stack"></i>{{ $lp->stock_quantity }}</span>
                                @if($lp->match === 'code')
                                    <span class="vx-tag is-code"><i class="bi bi-check2"></i>Mesmo código</span>
                                @elseif($lp->match === 'name')
                                    <span class="vx-tag is-name">Mesmo nome</span>
                                @endif
                            </div>
                            <div class="vx-info">
                                <p class="vx-name" title="{{ $lp->name }}">{{ $lp->name }}</p>
                                <div class="vx-prices">
                                    <span class="vx-cost" title="Custo"><i class="bi bi-tag"></i>{{ $vxMoney($lp->price) }}</span>
                                    <span class="vx-sale" title="Venda"><i class="bi bi-currency-dollar"></i>{{ $vxMoney($lp->price_sale) }}</span>
                                </div>
                            </div>
                            <div class="vx-act">
                                <a href="{{ route('products.edit', $lp) }}" target="_blank" title="Abrir em outra aba"><i class="bi bi-box-arrow-up-right"></i></a>
                                <button type="button" wire:click="linkOne({{ $lp->id }})" wire:loading.attr="disabled" wire:target="linkOne({{ $lp->id }})" class="is-link" title="Vincular como variação">
                                    <i class="bi bi-link" wire:loading.remove wire:target="linkOne({{ $lp->id }})"></i><i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="linkOne({{ $lp->id }})"></i><span>Vincular</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="vx-empty">{{ trim($linkSearch) !== '' ? 'Nenhum produto encontrado para "' . $linkSearch . '".' : 'Nenhum outro produto com o mesmo código ou nome parecido. Busque acima por nome ou código.' }}</p>
                    @endforelse
                </div>
                @error('linkVariantId')<p class="ep-err">{{ $message }}</p>@enderror
            </div>
            @endunless
        </div>
        @endif
    </section>

    <style>
        .vx { margin-top: 1rem; background: rgba(255,255,255,.92); border: 1px solid rgba(148,163,184,.28); border-radius: 22px; box-shadow: 0 8px 28px rgba(15,23,42,.06); }
        .dark .vx { background: rgba(15,23,42,.82); border-color: rgba(71,85,105,.5); }
        .vx-h { width: 100%; display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-radius: 22px; }
        .vx-h:hover { background: rgba(139,92,246,.04); }
        .vx-h-ico { width: 2.4rem; height: 2.4rem; flex-shrink: 0; border-radius: .85rem; display: flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(135deg, #8b5cf6, #d946ef); box-shadow: 0 6px 16px rgba(139,92,246,.3); }
        .vx-h-t { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; font-size: .98rem; font-weight: 800; color: #0f172a; }
        .dark .vx-h-t { color: #f1f5f9; }
        .vx-h-s { display: block; margin-top: .1rem; font-size: .72rem; color: #64748b; }
        .vx-pill { display: inline-flex; align-items: center; gap: .25rem; padding: .1rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 800; color: #6d28d9; background: rgba(139,92,246,.14); }
        .vx-pill.is-code { color: #047857; background: rgba(16,185,129,.14); }
        .dark .vx-pill { color: #ddd6fe; } .dark .vx-pill.is-code { color: #6ee7b7; }
        .vx-h-stock { display: none; flex-direction: column; align-items: flex-end; line-height: 1.15; }
        .vx-h-stock small { font-size: .62rem; color: #64748b; font-weight: 600; }
        .vx-h-stock b { font-size: 1.05rem; color: #6d28d9; }
        .dark .vx-h-stock b { color: #c4b5fd; }
        @media (min-width: 640px) { .vx-h-stock { display: flex; } }
        .vx-h-chev { width: 2rem; height: 2rem; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: rgba(148,163,184,.14); color: #475569; }
        .dark .vx-h-chev { color: #cbd5e1; }
        .vx-b { padding: 1rem; border-top: 1px solid rgba(148,163,184,.2); display: grid; gap: 1rem; }
        .vx-note { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .75rem 1rem; border-radius: 1rem; font-size: .85rem; color: #4c1d95; background: rgba(139,92,246,.08); border: 1px solid rgba(139,92,246,.25); }
        .dark .vx-note { color: #ede9fe; }
        .vx-sec-h { display: flex; flex-wrap: wrap; align-items: baseline; gap: .5rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: -.35rem; }
        .vx-sec-h i { color: #a78bfa; }
        .vx-sec-h small { text-transform: none; letter-spacing: 0; font-weight: 600; }
        .vx-grid { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(168px, 1fr)); }
        @media (max-width: 640px) { .vx-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; } }
        .vx-card { position: relative; display: flex; flex-direction: column; min-width: 0; border-radius: 18px; overflow: hidden; background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 1px 2px rgb(15 23 42 / .05), 0 6px 18px -10px rgb(15 23 42 / .18); transition: border-color .15s, box-shadow .15s; }
        .vx-card:hover { border-color: rgb(196 181 253); box-shadow: 0 10px 28px -12px rgb(124 58 237 / .35); }
        .vx-card.is-self { border-color: rgb(139 92 246); box-shadow: 0 0 0 2px rgb(139 92 246 / .25); }
        .vx-card.is-match { border-color: rgb(110 231 183); }
        .dark .vx-card { background: rgb(15 23 42); border-color: rgb(51 65 85); }
        .dark .vx-card.is-self { border-color: rgb(139 92 246); }
        .dark .vx-card.is-match { border-color: rgb(16 185 129 / .6); }
        .vx-img { position: relative; aspect-ratio: 1 / 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 30px 10px 26px; }
        .vx-img img { width: 100%; height: 100%; object-fit: contain; }
        .vx-code, .vx-qty { position: absolute; top: 7px; display: inline-flex; align-items: center; gap: 3px; height: 23px; padding: 0 7px; border-radius: 999px; font-size: 10.5px; font-weight: 800; background: rgb(255 255 255 / .94); border: 1px solid rgb(226 232 240); color: rgb(51 65 85); box-shadow: 0 1px 3px rgb(15 23 42 / .08); max-width: calc(100% - 60px); overflow: hidden; white-space: nowrap; }
        .vx-code { left: 7px; } .vx-code i { color: rgb(124 58 237); }
        .vx-qty { right: 7px; color: rgb(4 120 87); } .vx-qty.is-out { color: rgb(225 29 72); }
        .vx-tag { position: absolute; left: 7px; bottom: 7px; max-width: calc(100% - 14px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 999px; font-size: 10.5px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #8b5cf6, #a855f7); }
        .vx-tag.is-self { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .vx-tag.is-code { background: linear-gradient(135deg, #10b981, #059669); }
        .vx-tag.is-name { background: linear-gradient(135deg, #0ea5e9, #6366f1); }
        .vx-info { padding: 8px 10px 9px; border-top: 1px solid rgb(241 245 249); flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .dark .vx-info { border-top-color: rgb(30 41 59); }
        .vx-name { margin: 0; font-size: 12.5px; font-weight: 700; line-height: 1.3; color: rgb(15 23 42); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
        .dark .vx-name { color: rgb(241 245 249); }
        .vx-prices { display: flex; flex-wrap: wrap; gap: 4px; margin-top: auto; }
        .vx-cost, .vx-sale { display: inline-flex; align-items: center; gap: 2px; padding: 2px 7px; border-radius: 8px; font-size: 11px; font-weight: 800; white-space: nowrap; }
        .vx-cost { color: rgb(194 65 12); background: rgb(255 237 213); }
        .vx-sale { color: rgb(4 120 87); background: rgb(209 250 229); }
        .dark .vx-cost { color: rgb(253 186 116); background: rgb(124 45 18 / .35); }
        .dark .vx-sale { color: rgb(110 231 183); background: rgb(6 78 59 / .4); }
        .vx-gen { display: grid; gap: .85rem; padding: .9rem; border-radius: 1.1rem; border: 1px dashed rgba(139,92,246,.4); background: rgba(139,92,246,.04); }
        .vx-gen-grid { display: grid; gap: .7rem; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
        .vx-gen-grid > div:nth-child(-n+2) { grid-column: span 2; }
        @media (max-width: 640px) { .vx-gen-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .vx-gen-grid > div:nth-child(-n+2) { grid-column: 1 / -1; } }
        .vx-gen-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
        .vx-gen-chips span { padding: .2rem .6rem; border-radius: 999px; font-size: .74rem; font-weight: 700; color: #6d28d9; background: rgba(139,92,246,.12); }
        .dark .vx-gen-chips span { color: #ddd6fe; }
        .vx-gen-chips .is-more { color: #475569; background: rgba(148,163,184,.18); }
        .vx-act { display: flex; border-top: 1px solid rgb(241 245 249); }
        .dark .vx-act { border-top-color: rgb(30 41 59); }
        .vx-act > * { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 8px 4px; font-size: 11.5px; font-weight: 700; color: rgb(71 85 105); transition: background .15s, color .15s; }
        .vx-act > * + * { border-left: 1px solid rgb(241 245 249); }
        .dark .vx-act > * { color: rgb(148 163 184); } .dark .vx-act > * + * { border-left-color: rgb(30 41 59); }
        .vx-act > *:hover { background: rgb(245 243 255); color: rgb(109 40 217); }
        .vx-act .is-danger:hover { background: rgb(254 242 242); color: rgb(220 38 38); }
        .vx-act .is-link { flex: 2.2; color: #fff; background: linear-gradient(90deg, #6366f1, #8b5cf6); }
        .vx-act .is-link:hover { color: #fff; background: linear-gradient(90deg, #4f46e5, #7c3aed); }
        .dark .vx-act > *:hover { background: rgb(30 41 59); }
        .vx-sug { border-radius: 1.1rem; padding: .9rem; background: rgba(99,102,241,.05); border: 1px solid rgba(99,102,241,.18); display: grid; gap: .8rem; }
        .vx-sug-h { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; }
        .vx-sug-h > .min-w-0 { flex: 1 1 200px; }
        .vx-sug-t { margin: 0; font-size: .8rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; color: #4338ca; }
        .dark .vx-sug-t { color: #a5b4fc; }
        .vx-sug-s { margin: .1rem 0 0; font-size: .72rem; color: #64748b; }
        .vx-search { position: relative; flex: 1 1 220px; max-width: 340px; }
        .vx-search i { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .8rem; }
        .vx-search input { width: 100%; padding: .55rem .8rem .55rem 2.1rem; border-radius: .85rem; border: 1px solid rgba(148,163,184,.45); background: #fff; font-size: .86rem; color: #0f172a; }
        .vx-search input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .dark .vx-search input { background: rgba(30,41,59,.85); border-color: rgba(71,85,105,.7); color: #f1f5f9; }
        @media (max-width: 640px) { .vx-search { max-width: none; flex-basis: 100%; } }
        .vx-btn { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .5rem .85rem; border-radius: .85rem; font-size: .8rem; font-weight: 800; white-space: nowrap; }
        .vx-btn.is-primary { color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); box-shadow: 0 6px 16px rgba(99,102,241,.25); }
        .vx-btn.is-danger { color: #e11d48; background: #fff; border: 1px solid rgba(244,63,94,.35); }
        .dark .vx-btn.is-danger { background: transparent; }
        .vx-empty { grid-column: 1 / -1; margin: 0; padding: 1.2rem; text-align: center; font-size: .78rem; color: #64748b; border: 1px dashed rgba(148,163,184,.5); border-radius: 1rem; }
    </style>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Modal de Dicas (Wizard) -->
    @if($showTipsModal)
    <div x-data="{
            currentStep: 1,
            totalSteps: 5,
            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }" x-show="$wire.showTipsModal" x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);">

        <!-- Modal Content -->
        <div @click.away="if(currentStep === totalSteps) $wire.toggleTips()"
            class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Header with Progress Bar -->
            <div class="relative bg-gradient-to-br from-green-600 via-emerald-600 to-teal-700 px-8 py-6 text-white">
                <button @click="$wire.toggleTips()" class="absolute top-4 right-4 p-2 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>

                <div class="pr-12">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                            <i class="bi bi-lightbulb-fill text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold">Dicas: Editar Produto</h2>
                            <p class="text-green-100 text-sm mt-1">Aprenda a editar produtos corretamente</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex gap-2 mt-6">
                        <template x-for="step in totalSteps" :key="step">
                            <div class="flex-1 h-2 rounded-full overflow-hidden bg-white/20">
                                <div class="h-full bg-white rounded-full transition-all duration-500"
                                    :style="currentStep >= step ? 'width: 100%' : 'width: 0%'"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="relative overflow-y-auto max-h-[calc(90vh-280px)] p-8">
                <!-- Step 1: Campos Obrigatórios -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-asterisk text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Campos Obrigatórios</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Informações essenciais para cadastrar um produto</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-500 rounded-xl">
                                    <i class="bi bi-tag text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Nome do Produto *</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Nome claro e descritivo do produto (obrigatório)</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-purple-500 rounded-xl">
                                    <i class="bi bi-upc-scan text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Código do Produto *</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">SKU, código de barras ou código interno único</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-500 rounded-xl">
                                    <i class="bi bi-cash text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Preço de Venda *</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Preço pelo qual o produto será vendido</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-orange-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-orange-500 rounded-xl">
                                    <i class="bi bi-box-seam text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Quantidade em Estoque *</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Quantidade disponível para venda</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-pink-50 to-rose-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-pink-200/50 dark:border-slate-500/50 md:col-span-2">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-pink-500 rounded-xl">
                                    <i class="bi bi-folder text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white mb-2">Categoria *</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Selecione a categoria apropriada para organizar seu produto no catálogo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Campos Opcionais -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-plus-circle text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Campos Opcionais</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Informações adicionais para enriquecer seu produto</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-500 rounded-xl">
                                    <i class="bi bi-image text-2xl text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Imagem do Produto</h4>
                                    <p class="text-slate-700 dark:text-slate-300 mb-3">Adicione uma imagem para facilitar a identificação visual</p>
                                    <ul class="space-y-2">
                                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                            <i class="bi bi-check-circle-fill text-green-500"></i>
                                            Formatos aceitos: JPG, PNG, JPEG, GIF, WEBP
                                        </li>
                                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                            <i class="bi bi-check-circle-fill text-green-500"></i>
                                            Tamanho máximo: 2MB
                                        </li>
                                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                            <i class="bi bi-check-circle-fill text-green-500"></i>
                                            Resolução recomendada: 800x800px
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-purple-500 rounded-xl">
                                    <i class="bi bi-text-paragraph text-2xl text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Descrição</h4>
                                    <p class="text-slate-700 dark:text-slate-300 mb-3">Adicione detalhes, características ou informações relevantes</p>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <p class="text-sm text-slate-600 dark:text-slate-400 italic">💡 Dica: Uma boa descrição ajuda na busca e na identificação do produto</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-500 rounded-xl">
                                    <i class="bi bi-cash-coin text-2xl text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Preço de Custo</h4>
                                    <p class="text-slate-700 dark:text-slate-300 mb-3">Quanto você pagou pelo produto (útil para calcular margem de lucro)</p>
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                        <p class="text-sm text-slate-600 dark:text-slate-400">
                                            <i class="bi bi-calculator text-green-500 mr-1"></i>
                                            O sistema calculará automaticamente sua margem de lucro
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Preços e Valores -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-currency-dollar text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Preços e Valores</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Entenda como gerenciar os preços do produto</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="flex items-center justify-center w-8 h-8 bg-green-500 text-white rounded-lg font-bold">1</span>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Preço de Venda</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-4">Este é o preço principal que aparecerá nas vendas:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Formato</p>
                                    <p class="text-lg font-bold text-green-600">R$ 99,90</p>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Campo aceita</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">Decimais com vírgula</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-lg font-bold">2</span>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Preço de Custo</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-4">Quanto você pagou pelo produto (para controle interno):</p>
                            <div class="p-4 bg-white dark:bg-slate-800 rounded-xl">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Custo</p>
                                        <p class="text-lg font-bold text-blue-600">R$ 50,00</p>
                                    </div>
                                    <div class="text-center px-3">
                                        <i class="bi bi-arrow-right text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Venda</p>
                                        <p class="text-lg font-bold text-green-600">R$ 99,90</p>
                                    </div>
                                    <div class="text-center px-3">
                                        <i class="bi bi-arrow-right text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Lucro</p>
                                        <p class="text-lg font-bold text-emerald-600">R$ 49,90</p>
                                    </div>
                                </div>
                                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300 text-center font-semibold">
                                        <i class="bi bi-graph-up-arrow mr-1"></i>
                                        Margem de Lucro: 99.8%
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-amber-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-lightbulb-fill text-2xl text-amber-500"></i>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Dica de Precificação</h4>
                            </div>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-amber-500"></i>
                                    Considere custos adicionais (frete, impostos, comissões)
                                </li>
                                <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-amber-500"></i>
                                    Pesquise preços da concorrência
                                </li>
                                <li class="flex items-center gap-2 text-slate-700 dark:text-slate-300 text-sm">
                                    <i class="bi bi-check-circle-fill text-amber-500"></i>
                                    Mantenha uma margem de lucro saudável (mínimo 30%)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Categorias e Organização -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-folder2-open text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Categorias e Organização</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Organize seus produtos de forma eficiente</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-purple-500 rounded-lg">
                                    <i class="bi bi-tags text-xl text-white"></i>
                                </div>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Importância das Categorias</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-4">Categorizar produtos traz diversos benefícios:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-search text-purple-500"></i>
                                    Facilita a busca
                                </div>
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-funnel text-purple-500"></i>
                                    Melhora os filtros
                                </div>
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-graph-up text-purple-500"></i>
                                    Relatórios por categoria
                                </div>
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 text-sm">
                                    <i class="bi bi-grid text-purple-500"></i>
                                    Organização visual
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <i class="bi bi-lightbulb text-xl text-white"></i>
                                </div>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Dicas de Categorização</h4>
                            </div>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">1</span>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-white">Seja específico</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Ex: "Camisetas Masculinas" ao invés de apenas "Roupas"</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">2</span>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-white">Use padrões</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Mantenha uma nomenclatura consistente</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <span class="flex items-center justify-center w-6 h-6 bg-blue-500 text-white rounded-full text-xs font-bold mt-0.5">3</span>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-white">Não exagere</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Muitas categorias podem confundir. Mantenha entre 5-15 categorias</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-green-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-plus-circle-fill text-2xl text-green-500"></i>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Criar Nova Categoria</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-2">Não encontrou a categoria ideal?</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                <i class="bi bi-arrow-right text-green-500 mr-1"></i>
                                Acesse o menu <strong>Categorias</strong> para criar novas categorias personalizadas com ícones e cores!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Finalização -->
                <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-xl mb-6">
                            <i class="bi bi-check-circle text-5xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mb-3">Pronto para Salvar!</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-lg">Checklist final antes de salvar as alterações</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-blue-200/50 dark:border-slate-500/50">
                            <h4 class="text-xl font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-list-check text-blue-500"></i>
                                Checklist de Verificação
                            </h4>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Nome do produto está claro e descritivo</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Código/SKU está correto</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Preços estão corretos (venda e custo)</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Quantidade em estoque está atualizada</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Categoria apropriada selecionada</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-slate-700 dark:text-slate-300">Imagem do produto adicionada (opcional mas recomendado)</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-purple-200/50 dark:border-slate-500/50">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-purple-500 rounded-lg">
                                    <i class="bi bi-rocket-takeoff text-xl text-white"></i>
                                </div>
                                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Após Salvar</h4>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mb-3">O que você poderá fazer:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <i class="bi bi-cart-plus text-lg text-purple-500 mb-1"></i>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Adicionar em Vendas</p>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <i class="bi bi-pencil text-lg text-blue-500 mb-1"></i>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Editar a qualquer momento</p>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <i class="bi bi-files text-lg text-green-500 mb-1"></i>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Duplicar o produto</p>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-800 rounded-lg">
                                    <i class="bi bi-boxes text-lg text-orange-500 mb-1"></i>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Criar kits</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-slate-700 dark:via-slate-700 dark:to-slate-600 rounded-2xl border-2 border-green-300 dark:border-green-600">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="bi bi-emoji-smile-fill text-3xl text-green-500"></i>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Tudo Pronto!</h4>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">Agora você está pronto para salvar as alterações</p>
                                </div>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300">Revise o formulário e clique em <strong class="text-green-600 dark:text-green-400">"Salvar Alterações"</strong> para finalizar!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with Navigation -->
            <div class="bg-slate-50 dark:bg-slate-900/50 px-8 py-6 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <!-- Previous Button -->
                    <button @click="prevStep()" x-show="currentStep > 1"
                        class="flex items-center gap-2 px-6 py-3 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all duration-200 hover:scale-105">
                        <i class="bi bi-arrow-left"></i>
                        Anterior
                    </button>
                    <div x-show="currentStep === 1"></div>

                    <!-- Step Indicators -->
                    <div class="flex items-center gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <button @click="currentStep = step"
                                class="transition-all duration-300 rounded-full"
                                :class="currentStep === step ? 'w-8 h-3 bg-gradient-to-r from-green-600 to-emerald-600' : 'w-3 h-3 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'">
                            </button>
                        </template>
                    </div>

                    <!-- Next/Finish Button -->
                    <button @click="currentStep < totalSteps ? nextStep() : $wire.toggleTips()"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <span x-text="currentStep < totalSteps ? 'Próximo' : 'Concluir!'"></span>
                        <i class="bi" :class="currentStep < totalSteps ? 'bi-arrow-right' : 'bi-check-circle-fill'"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <livewire:products.product-share />
</div>
