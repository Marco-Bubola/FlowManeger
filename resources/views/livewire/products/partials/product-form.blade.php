{{-- Formulário do produto (novo e editar): dados, preços, descrição e ficha técnica, fotos e importar do ML. --}}
    @php
        $isEdit = $isEdit ?? false;
        $gallery = $isEdit
            ? $extraImages
            : collect($mlPendingPhotos)->map(fn ($u, $i) => ['id' => null, 'url' => $u, 'source' => 'mercadolivre', 'pending' => $i])->all();
        $epCost = (float) str_replace(',', '.', $price ?: '0');
        $epSale = (float) str_replace(',', '.', $price_sale ?: '0');
        $epProfit = $epSale - $epCost;
        $epMargin = $epSale > 0 ? round($epProfit / $epSale * 100) : 0;
        $epSpecCount = collect($specs)->filter(fn ($s) => trim($s['n'] ?? '') !== '' && trim($s['v'] ?? '') !== '')->count();
    @endphp
    <style>
        .ep-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1200px) { .ep-grid { grid-template-columns: minmax(0, 1fr) 380px; align-items: start; } .ep-side { position: sticky; top: 1rem; } }
        .ep-col { display: grid; gap: 1rem; min-width: 0; }
        .ep-card { background: rgba(255,255,255,.92); border: 1px solid rgba(148,163,184,.28); border-radius: 22px; box-shadow: 0 8px 28px rgba(15,23,42,.06); }
        .dark .ep-card { background: rgba(15,23,42,.82); border-color: rgba(71,85,105,.5); }
        .ep-card-h { display: flex; align-items: center; gap: .65rem; padding: .85rem 1rem; border-bottom: 1px solid rgba(148,163,184,.2); }
        .dark .ep-card-h { border-color: rgba(71,85,105,.45); }
        .ep-card-h h3 { margin: 0; font-size: .95rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .dark .ep-card-h h3 { color: #f1f5f9; }
        .ep-card-h p { margin: .1rem 0 0; font-size: .72rem; color: #64748b; line-height: 1.3; }
        .ep-card-b { padding: 1rem; }
        .ep-ico { width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; background: linear-gradient(135deg, #6366f1, #a855f7); box-shadow: 0 6px 16px rgba(99,102,241,.25); }
        .ep-ico.green { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,.25); }
        .ep-ico.teal { background: linear-gradient(135deg, #14b8a6, #0ea5e9); box-shadow: 0 6px 16px rgba(14,165,233,.22); }
        .ep-ico.pink { background: linear-gradient(135deg, #ec4899, #a855f7); }
        .ep-ico.ml { background: #ffe600; color: #2d3277; box-shadow: 0 6px 16px rgba(234,179,8,.25); }
        .ep-label { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .35rem; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
        .ep-label .req { color: #f43f5e; }
        .ep-input { width: 100%; border-radius: .9rem; border: 1px solid rgba(148,163,184,.45); background: #f8fafc; padding: .65rem .85rem; font-size: .92rem; color: #0f172a; transition: border-color .15s, box-shadow .15s; }
        .ep-input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.15); background: #fff; }
        .dark .ep-input { background: rgba(30,41,59,.8); border-color: rgba(71,85,105,.7); color: #f1f5f9; }
        .dark .ep-input:focus { background: rgba(30,41,59,1); }
        .ep-input.is-err { border-color: #f43f5e; }
        .ep-err { margin: .3rem 0 0; font-size: .72rem; color: #f43f5e; }
        .ep-row { display: grid; gap: .85rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .ep-row.c2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ep-row.c3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 900px) { .ep-row.c4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } .ep-row.c5 { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (min-width: 640px) and (max-width: 899px) { .ep-row.c4, .ep-row.c5 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ep-row.fit { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
        .ep-btn { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; padding: .6rem 1rem; border-radius: .9rem; font-weight: 800; font-size: .86rem; transition: transform .15s, filter .15s; white-space: nowrap; }
        .ep-btn:active { transform: scale(.97); }
        .ep-btn-ml { color: #2d3277; background: #ffe600; box-shadow: 0 6px 16px rgba(234,179,8,.25); }
        .ep-btn-ml:hover { filter: brightness(.97); }
        .ep-btn-soft { color: #4f46e5; background: rgba(99,102,241,.1); }
        .dark .ep-btn-soft { color: #c7d2fe; background: rgba(99,102,241,.18); }
        .ep-btn-primary { color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); box-shadow: 0 8px 20px rgba(99,102,241,.3); }
        .ep-btn-ghost { color: #475569; background: transparent; border: 1px solid rgba(148,163,184,.45); }
        .dark .ep-btn-ghost { color: #cbd5e1; border-color: rgba(71,85,105,.7); }
        .ep-profit { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .9rem; }
        .ep-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .7rem; border-radius: 999px; font-size: .78rem; font-weight: 700; background: rgba(148,163,184,.14); color: #475569; }
        .dark .ep-pill { color: #cbd5e1; }
        .ep-pill.good { background: rgba(16,185,129,.12); color: #047857; }
        .dark .ep-pill.good { color: #6ee7b7; }
        .ep-pill.bad { background: rgba(244,63,94,.12); color: #be123c; }
        .dark .ep-pill.bad { color: #fda4af; }
        .ep-spec { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 3fr) auto; gap: .4rem; align-items: center; }
        .ep-spec + .ep-spec { margin-top: .4rem; }
        .ep-spec .ep-input { padding: .5rem .7rem; font-size: .86rem; }
        .ep-x { width: 2.1rem; height: 2.1rem; border-radius: .7rem; display: flex; align-items: center; justify-content: center; color: #e11d48; background: rgba(244,63,94,.1); }
        .ep-gal { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .5rem; }
        .ep-gal-item { position: relative; aspect-ratio: 1; border-radius: .9rem; overflow: hidden; border: 1px solid rgba(148,163,184,.3); background: #fff; }
        .ep-gal-item img { width: 100%; height: 100%; object-fit: cover; }
        .ep-gal-item .del { position: absolute; top: .25rem; right: .25rem; width: 1.6rem; height: 1.6rem; border-radius: 999px; background: rgba(15,23,42,.65); color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center; }
        .ep-gal-item .tag { position: absolute; left: .25rem; bottom: .25rem; padding: .05rem .4rem; border-radius: 999px; background: #ffe600; color: #2d3277; font-size: .6rem; font-weight: 900; }
        .ep-hint { font-size: .74rem; color: #64748b; display: flex; gap: .4rem; align-items: flex-start; }
        .ep-ml-box { border-radius: 1rem; padding: .85rem; background: linear-gradient(135deg, rgba(255,230,0,.16), rgba(255,230,0,.05)); border: 1px solid rgba(234,179,8,.35); }
        .ep-ml-box b { color: #2d3277; }
        .dark .ep-ml-box b { color: #fde68a; }
    </style>

    <!-- Conteúdo Principal -->
    <form id="{{ $isEdit ? 'edit-product-form' : 'create-product-form' }}" wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="ep-grid">

        <div class="ep-col">
            {{-- Dados do produto --}}
            <section class="ep-card">
                <div class="ep-card-h">
                    <span class="ep-ico"><i class="bi bi-info-circle-fill"></i></span>
                    <div><h3>Dados do produto</h3><p>Nome, código e categoria</p></div>
                </div>
                <div class="ep-card-b ep-row c3">
                    <div>
                        <label for="name" class="ep-label"><span>Nome <span class="req">*</span></span></label>
                        <div class="relative" x-data="{ copied: false }">
                            <input type="text" wire:model.blur="name" id="name" class="ep-input pr-11 {{ $errors->has('name') ? 'is-err' : '' }}" placeholder="Ex: Batom matte vermelho">
                            <button type="button" title="Copiar nome"
                                @click="navigator.clipboard.writeText(document.getElementById('name').value).then(() => { copied = true; setTimeout(() => copied = false, 1600) })"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-500 hover:bg-indigo-500/10">
                                <i class="bi" :class="copied ? 'bi-clipboard-check text-emerald-500' : 'bi-clipboard'"></i>
                            </button>
                        </div>
                        @error('name')<p class="ep-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="product_code" class="ep-label"><span>Código <span class="req">*</span></span></label>
                        <input type="text" wire:model.blur="product_code" id="product_code" class="ep-input {{ $errors->has('product_code') ? 'is-err' : '' }}" placeholder="Ex: 1234.567">
                        @error('product_code')<p class="ep-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="ep-label"><span>Categoria <span class="req">*</span></span></label>
                        <div class="relative" x-data="{
                            open: false,
                            name: @js($selectedCategoryName ?? 'Escolha...'),
                            icon: @js($selectedCategoryIcon ?? 'bi-grid-3x3-gap-fill'),
                            search: '',
                            pick(c) { this.name = c.name; this.icon = c.icon; this.open = false; this.search = ''; $wire.set('category_id', c.id); }
                        }">
                            <button type="button" @click="open = !open" class="ep-input flex items-center justify-between text-left {{ $errors->has('category_id') ? 'is-err' : '' }}">
                                <span class="flex items-center gap-2 min-w-0"><i :class="icon" class="text-pink-500"></i><span class="truncate" x-text="name"></span></span>
                                <i class="bi bi-chevron-down text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition @click.outside="open = false"
                                class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
                                <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                    <input type="text" x-model="search" x-ref="catSearch" x-init="$watch('open', v => v && $nextTick(() => $refs.catSearch.focus()))"
                                        placeholder="Buscar categoria..." class="ep-input" style="padding:.45rem .7rem;font-size:.85rem">
                                </div>
                                <div class="max-h-56 overflow-y-auto">
                                    @foreach($categories as $category)
                                    <button type="button"
                                        x-show="!search || @js(mb_strtolower($category->name)).includes(search.toLowerCase())"
                                        @click="pick({ id: {{ $category->id_category }}, name: @js($category->name), icon: @js($this->getCategoryIcon($category->icone)) })"
                                        class="w-full flex items-center gap-2 px-3.5 py-2.5 text-left text-sm font-medium text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-700/70 {{ (string) $category->id_category === (string) $category_id ? 'bg-indigo-50 dark:bg-indigo-500/10' : '' }}">
                                        <i class="{{ $this->getCategoryIcon($category->icone) }} text-pink-500"></i>{{ $category->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('category_id')<p class="ep-err">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Preços e estoque --}}
            <section class="ep-card">
                <div class="ep-card-h">
                    <span class="ep-ico green"><i class="bi bi-currency-dollar"></i></span>
                    <div><h3>Preços e estoque</h3><p>{{ $isEdit ? "Custo, venda, preço de tabela e quantidade" : "Custo, venda e quantidade" }}</p></div>
                </div>
                <div class="ep-card-b">
                    <div class="ep-row fit">
                        <x-currency-input name="price" id="price" wireModel="price" label="Custo" icon="bi-tag-fill" icon-color="orange" :required="true" width="w-full" :value="$price" />
                        <x-currency-input name="price_sale" id="price_sale" wireModel="price_sale" label="Venda" icon="bi-currency-dollar" icon-color="green" :required="true" width="w-full" :value="$price_sale" />
                        @if($isEdit)
                        <x-currency-input name="price_original" id="price_original" wireModel="price_original" label="Tabela" icon="bi-fire" icon-color="purple" :required="false" width="w-full" :value="$price_original" />
                        @endif
                        <x-quantity-input name="stock_quantity" id="stock_quantity" wireModel="stock_quantity" :min="0" :max="99999" label="Estoque" icon="bi-boxes" icon-color="cyan" :required="true" width="w-full" />
                    </div>
                    @if($epSale > 0)
                    <div class="ep-profit">
                        <span class="ep-pill {{ $epProfit > 0 ? 'good' : 'bad' }}"><i class="bi bi-graph-up-arrow"></i>Lucro R$ {{ number_format($epProfit, 2, ',', '.') }}</span>
                        <span class="ep-pill {{ $epMargin >= 20 ? 'good' : ($epMargin > 0 ? '' : 'bad') }}"><i class="bi bi-percent"></i>Margem {{ $epMargin }}%</span>
                    </div>
                    @endif
                </div>
            </section>

            {{-- Descrição e ficha técnica --}}
            <section class="ep-card">
                <div class="ep-card-h">
                    <span class="ep-ico teal"><i class="bi bi-card-text"></i></span>
                    <div class="flex-1 min-w-0"><h3>Descrição e ficha técnica</h3><p>Aparecem no portal do cliente</p></div>
                    <button type="button" wire:click="openMlImport" class="ep-btn ep-btn-ml shrink-0" style="padding:.45rem .8rem;font-size:.78rem">
                        <i class="bi bi-cloud-download"></i>Puxar do ML
                    </button>
                </div>
                <div class="ep-card-b space-y-4">
                    <div x-data="{ len: null }">
                        <label for="description" class="ep-label"><span>Descrição</span><span class="normal-case tracking-normal font-semibold"><span x-text="len ?? {{ mb_strlen($description) }}">{{ mb_strlen($description) }}</span>/5000</span></label>
                        <textarea wire:model.blur="description" id="description" rows="6" maxlength="5000" @input="len = $event.target.value.length" @blur="len = null"
                            class="ep-input {{ $errors->has('description') ? 'is-err' : '' }}" style="resize:vertical;min-height:8rem"
                            placeholder="Conte o que o produto é, para que serve e o que vem na caixa..."></textarea>
                        @error('description')<p class="ep-err">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="ep-label"><span>Ficha técnica @if($epSpecCount)· {{ $epSpecCount }}@endif</span></div>
                        @forelse($specs as $i => $spec)
                            <div class="ep-spec" wire:key="spec-{{ $i }}">
                                <input type="text" wire:model.blur="specs.{{ $i }}.n" class="ep-input" placeholder="Ex: Cor" maxlength="80">
                                <input type="text" wire:model.blur="specs.{{ $i }}.v" class="ep-input" placeholder="Ex: Vermelho" maxlength="255">
                                <button type="button" wire:click="removeSpec({{ $i }})" class="ep-x" title="Tirar"><i class="bi bi-trash3"></i></button>
                            </div>
                        @empty
                            <p class="ep-hint"><i class="bi bi-info-circle"></i>Nenhum item ainda. Puxe do Mercado Livre ou adicione à mão (cor, tamanho, volume...).</p>
                        @endforelse
                        <button type="button" wire:click="addSpec" class="ep-btn ep-btn-soft mt-3" style="padding:.45rem .8rem;font-size:.8rem"><i class="bi bi-plus-lg"></i>Adicionar item</button>
                    </div>

                    <div class="ep-row fit pt-1">
                        <div>
                            <label for="brand" class="ep-label"><span>Marca</span></label>
                            <input type="text" wire:model.blur="brand" id="brand" class="ep-input" maxlength="100" placeholder="Ex: Natura">
                        </div>
                        <div>
                            <label for="model" class="ep-label"><span>Modelo</span></label>
                            <input type="text" wire:model.blur="model" id="model" class="ep-input" maxlength="100">
                        </div>
                        <div>
                            <label for="barcode" class="ep-label"><span>Código de barras</span></label>
                            <input type="text" wire:model.blur="barcode" id="barcode" inputmode="numeric" class="ep-input {{ $errors->has('barcode') ? 'is-err' : '' }}" maxlength="15" placeholder="7891234567890">
                            @error('barcode')<p class="ep-err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="warranty_months" class="ep-label"><span>Garantia (meses)</span></label>
                            <input type="number" wire:model.blur="warranty_months" id="warranty_months" min="0" max="120" class="ep-input">
                        </div>
                        <div>
                            <label for="condition" class="ep-label"><span>Condição</span></label>
                            <select wire:model="condition" id="condition" class="ep-input">
                                <option value="new">Novo</option>
                                <option value="used">Usado</option>
                                <option value="refurbished">Recondicionado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Fotos --}}
        <div class="ep-col ep-side">
            <section class="ep-card">
                <div class="ep-card-h">
                    <span class="ep-ico pink"><i class="bi bi-images"></i></span>
                    <div><h3>Fotos</h3><p>Principal e galeria do portal</p></div>
                </div>
                <div class="ep-card-b space-y-4">
                    <x-image-upload name="image" id="image" wire-model="image" title="Imagem principal" description="Toque ou arraste a foto aqui"
                        :existing-image="$isEdit ? $this->existingImageUrl : ($mlPendingCover ?: null)" :new-image="$image" height="h-[260px]" />
                    @if(!$isEdit && $mlPendingCover && !$image)
                        <p class="ep-hint -mt-2"><i class="bi bi-cloud-download"></i>Imagem principal vinda do Mercado Livre. Ela é salva junto com o produto.</p>
                    @endif

                    <div>
                        <div class="ep-label"><span>Galeria · {{ count($gallery) }}</span></div>
                        @if(count($gallery))
                            <div class="ep-gal">
                                @foreach($gallery as $img)
                                    <div class="ep-gal-item" wire:key="gal-{{ $img['id'] ?? 'p' . $img['pending'] }}">
                                        <img src="{{ $img['url'] }}" alt="" loading="lazy">
                                        @if($img['source'] === 'mercadolivre')<span class="tag">ML</span>@endif
                                        @if($isEdit)
                                            <button type="button" class="del" wire:click="deleteExtraImage({{ $img['id'] }})" wire:confirm="Tirar esta foto da galeria?" title="Tirar"><i class="bi bi-x-lg"></i></button>
                                        @else
                                            <button type="button" class="del" wire:click="removePendingPhoto({{ $img['pending'] }})" title="Tirar"><i class="bi bi-x-lg"></i></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="ep-hint"><i class="bi bi-info-circle"></i>Sem fotos na galeria. O portal mostra a imagem principal.</p>
                        @endif
                    </div>

                    <div class="ep-ml-box">
                        <p class="text-sm mb-1"><b>Catálogo do Mercado Livre</b></p>
                        <p class="ep-hint mb-3">Busque pelo código de barras ou pelo nome e traga fotos, descrição, ficha técnica, marca e modelo.</p>
                        <button type="button" wire:click="openMlImport" class="ep-btn ep-btn-ml w-full">
                            <span wire:loading.remove wire:target="openMlImport"><i class="bi bi-cloud-download"></i> Buscar no Mercado Livre</span>
                            <span wire:loading wire:target="openMlImport"><i class="bi bi-arrow-repeat animate-spin"></i> Buscando...</span>
                        </button>
                    </div>

                    <p class="ep-hint"><i class="bi bi-info-circle"></i>JPG, PNG ou WEBP até 2 MB. Melhor em 800 × 800.</p>
                </div>
            </section>
        </div>
    </form>

    @include('livewire.products.partials.ml-catalog-import')
