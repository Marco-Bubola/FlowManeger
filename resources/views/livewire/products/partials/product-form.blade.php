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
    @include('livewire.products.partials.ep-styles')

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
