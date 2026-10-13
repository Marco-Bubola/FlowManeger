@props([
    'products' => [],
    'categories' => [],
    'showBackButton' => true,
    'barcodeOptions' => []
])

@php
    // Mapeamento de ícones icons8 para Bootstrap Icons
    $iconMap = [
        'icons8-perfume' => 'bi-emoji-heart-eyes',
        'icons8-nubank' => 'bi-credit-card-2-front',
        'icons8-pagamento' => 'bi-currency-dollar',
        'icons8-pix' => 'bi-lightning-charge',
        'icons8-xp' => 'bi-graph-up-arrow',
        'icons8-inter' => 'bi-bank',
        'icons8-rendimento' => 'bi-graph-up',
        'icons8-restaurante' => 'bi-cup-straw',
        'icons8-beleza' => 'bi-heart',
        'icons8-supermercado' => 'bi-cart',
        'icons8-transporte' => 'bi-bus-front',
        'icons8-casa' => 'bi-house',
        'icons8-saude' => 'bi-heart-pulse',
        'icons8-educacao' => 'bi-book',
        'icons8-entretenimento' => 'bi-controller',
        'icons8-vestuario' => 'bi-bag',
        'icons8-tecnologia' => 'bi-laptop',
        'icons8-combustivel' => 'bi-fuel-pump',
        'icons8-farmacia' => 'bi-capsule',
        'icons8-pet' => 'bi-heart',
    ];
@endphp

<div class="products-preview-container">
    @if(!empty($products) && count($products) > 0)
        {{-- Cartões de revisão do upload: foto, código e quantidade em cima; nome, categoria e preços embaixo.
             Os ids image-input-N / product-image-N e os wire:model.lazy ficam iguais (o robô de imagens usa). --}}
        <div class="upc-grid">
                @foreach($products as $index => $product)
                    @php
                        $tabela = (float) ($product['price_original'] ?? 0);
                        $revenda = (float) ($product['price_sale'] ?? 0);
                        $descontoTabela = $tabela > 0 && $revenda > 0 ? (int) round((1 - $revenda / $tabela) * 100) : 0;
                        $podePromo = $tabela > $revenda && $revenda > 0;
                        $emPromo = !empty($product['promo']) && $podePromo;
                        $nomeJs = Js::from($product['name'] ?? '');
                        $codigoJs = Js::from($product['product_code'] ?? '');
                    @endphp
                    <div class="upc-card {{ $emPromo ? 'is-promo' : '' }}" wire:key="upc-{{ $index }}">
                        <!-- Foto: clicar copia o nome e abre o seletor de imagem -->
                        <div class="upc-media">
                            <div class="upc-photo"
                                 onclick="copyProductName({{ $index }}, {{ $nomeJs }}); document.getElementById('image-input-{{ $index }}').click();"
                                 title="Copiar nome e trocar a foto">
                                <img src="{{ $product['temp_image'] ?? asset('storage/products/product-placeholder.png') }}"
                                     alt="{{ $product['name'] ?? 'Produto' }}"
                                     id="product-image-{{ $index }}">
                                <span class="upc-photo-hint"><i class="bi bi-camera"></i> Trocar foto</span>
                            </div>

                            <input type="file" id="image-input-{{ $index }}" class="hidden" accept="image/*">

                            <label class="upc-chip upc-code" title="Código do produto">
                                <i class="bi bi-upc"></i>
                                <input type="text" wire:model.lazy="productsUpload.{{ $index }}.product_code" placeholder="Código" maxlength="15">
                            </label>
                            <label class="upc-chip upc-qty" title="Quantidade">
                                <span>Qtd</span>
                                <input type="number" min="0" wire:model.lazy="productsUpload.{{ $index }}.stock_quantity" placeholder="0">
                            </label>
                            @if($emPromo)
                                <span class="upc-ribbon"><i class="bi bi-fire"></i> -{{ $descontoTabela }}%</span>
                            @endif
                        </div>

                        <div class="upc-body">
                            <input type="text"
                                   wire:model.lazy="productsUpload.{{ $index }}.name"
                                   class="upc-name"
                                   title="{{ $product['name'] ?? '' }}"
                                   placeholder="Nome do produto">

                            {{-- Código de barras (EAN/GTIN): salvo no produto; id barcode-input-N para o robô preencher --}}
                            <label class="upc-ean" title="Código de barras (EAN)">
                                <i class="bi bi-upc-scan"></i>
                                <input type="text" inputmode="numeric" id="barcode-input-{{ $index }}" wire:model.lazy="productsUpload.{{ $index }}.barcode" placeholder="Cód. de barras" maxlength="14">
                                <button type="button" class="upc-ean-find" wire:click="lookupBarcode({{ $index }})" wire:loading.attr="disabled" wire:target="lookupBarcode({{ $index }})" title="Buscar código de barras no site do Boticário">
                                    <i class="bi bi-search" wire:loading.remove wire:target="lookupBarcode({{ $index }})"></i>
                                    <i class="bi bi-arrow-repeat upc-spin" wire:loading wire:target="lookupBarcode({{ $index }})"></i>
                                    <span>Buscar</span>
                                </button>
                            </label>
                            @if(!empty($barcodeOptions[$index]))
                                <div class="upc-ean-opts">
                                    <span class="upc-ean-opts-t">Qual é o certo?</span>
                                    @foreach($barcodeOptions[$index] as $opt)
                                        <button type="button" wire:click="chooseBarcode({{ $index }}, '{{ $opt['ean'] }}')" title="{{ $opt['name'] }} ({{ $opt['store'] }})">
                                            <b>{{ $opt['ean'] }}</b><small>{{ \Illuminate\Support\Str::limit($opt['name'], 42) }}</small>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

<!-- Select de Categoria Estilizado -->
                            <div class="upc-cat" x-data="{
                                open: false,
                                search: '',
                                selectedId: {{ $product['category_id'] ?? 1 }},
                                dropdownTop: 0,
                                dropdownLeft: 0,
                                dropdownWidth: 0,
                                dropdownBottom: 0,
                                dropdownIsAbove: false,
                                categories: {{ Js::from($categories->map(function($cat) use ($iconMap) {
                                    return [
                                        'id' => $cat->id_category,
                                        'name' => $cat->name,
                                        'icon' => $iconMap[$cat->icone] ?? 'bi-tag'
                                    ];
                                })) }},
                                get selectedCategory() {
                                    return this.categories.find(c => c.id == this.selectedId) || this.categories[0] || { name: 'Categoria', icon: 'bi-tag' };
                                },
                                get filteredCategories() {
                                    if (!this.search) return this.categories;
                                    return this.categories.filter(c =>
                                        c.name.toLowerCase().includes(this.search.toLowerCase())
                                    );
                                },
                                openDropdown() {
                                    const rect = this.$refs.dropdownTrigger.getBoundingClientRect();
                                    const vw = window.innerWidth;
                                    const vh = window.innerHeight;
                                    const dropH = 256; // max-h-60 = 240px + search bar ~16px
                                    const spaceBelow = vh - rect.bottom;
                                    const w = Math.max(180, Math.min(rect.width, vw - 16));
                                    let left = rect.left;
                                    if (left + w > vw - 8) left = vw - w - 8;
                                    if (left < 8) left = 8;
                                    this.dropdownWidth = w;
                                    this.dropdownLeft = left;
                                    if (spaceBelow < dropH && rect.top > dropH) {
                                        this.dropdownIsAbove = true;
                                        this.dropdownBottom = vh - rect.top + 4;
                                        this.dropdownTop = 0;
                                    } else {
                                        this.dropdownIsAbove = false;
                                        this.dropdownTop = rect.bottom + 4;
                                        this.dropdownBottom = 0;
                                    }
                                    this.open = true;
                                },
                                closeDropdown() {
                                    this.open = false;
                                },
                                selectCategory(cat) {
                                    this.selectedId = cat.id;
                                    this.closeDropdown();
                                    this.search = '';
                                    $wire.set('productsUpload.{{ $index }}.category_id', cat.id);
                                    
                                }
                            }">
                                <div class="relative w-full" x-ref="dropdownAnchor">
                                    <button type="button"
                                            x-ref="dropdownTrigger"
                                            @click.stop="open ? closeDropdown() : openDropdown()"
                                            class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg border border-purple-200 bg-white dark:bg-slate-700 dark:border-purple-700 text-slate-700 dark:text-slate-200 hover:border-purple-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-400/20 focus:outline-none transition-all duration-200 text-xs">
                                        <span class="flex items-center gap-2 overflow-hidden">
                                            <i :class="selectedCategory.icon" class="text-purple-500 flex-shrink-0"></i>
                                            <span x-text="selectedCategory.name" class="truncate"></span>
                                        </span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <template x-teleport="body">
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.away="closeDropdown()"
                                             :style="dropdownIsAbove
                                                 ? `position:fixed; z-index:2147483647; width:${dropdownWidth}px; bottom:${dropdownBottom}px; left:${dropdownLeft}px`
                                                 : `position:fixed; z-index:2147483647; width:${dropdownWidth}px; top:${dropdownTop}px; left:${dropdownLeft}px`"
                                             class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl max-h-60 overflow-hidden">
                                            <!-- Search -->
                                            <div class="p-2 border-b border-slate-200 dark:border-slate-700">
                                                <input type="text"
                                                       x-model="search"
                                                       @click.stop
                                                       placeholder="Pesquisar..."
                                                       class="w-full px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                            </div>
                                            <!-- Options -->
                                            <div class="overflow-y-auto max-h-44">
                                                <template x-for="cat in filteredCategories" :key="cat.id">
                                                    <button type="button"
                                                            @click="selectCategory(cat)"
                                                            class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-b-0">
                                                        <i :class="cat.icon" class="text-purple-500 text-xs"></i>
                                                        <span class="text-slate-700 dark:text-slate-200 text-xs" x-text="cat.name"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filteredCategories.length === 0" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 text-center">
                                                    Nenhuma categoria encontrada
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        
                            <div class="upc-prices">
                                <label class="upc-field" title="Preço de custo">
                                    <span class="upc-label">Custo</span>
                                    <span class="upc-money"><small>R$</small>
                                        <input type="number" step="0.01" min="0" wire:model.lazy="productsUpload.{{ $index }}.price" placeholder="0,00">
                                    </span>
                                </label>
                                <label class="upc-field upc-field-sale" title="Preço de venda (revenda)">
                                    <span class="upc-label">Venda</span>
                                    <span class="upc-money"><small>R$</small>
                                        <input type="number" step="0.01" min="0" wire:model.lazy="productsUpload.{{ $index }}.price_sale" placeholder="0,00">
                                    </span>
                                </label>
                                <label class="upc-field" title="R$ TABELA do PDF (preço original, aparece riscado na promoção)">
                                    <span class="upc-label">Tabela</span>
                                    <span class="upc-money"><small>R$</small>
                                        <input type="number" step="0.01" min="0" wire:model.lazy="productsUpload.{{ $index }}.price_original" placeholder="0,00" class="{{ $emPromo ? 'is-struck' : '' }}">
                                    </span>
                                </label>
                                <label class="upc-promo {{ $emPromo ? 'is-on' : '' }} {{ $podePromo ? '' : 'is-disabled' }}"
                                       title="{{ $podePromo ? 'Pôr em promoção ao salvar: tabela riscado, venda como preço de promoção' : 'Sem desconto: a venda não é menor que a tabela' }}">
                                    <span class="upc-label">Promoção</span>
                                    <span class="upc-promo-row">
                                        @if($podePromo)
                                            <input type="checkbox" wire:model.live="productsUpload.{{ $index }}.promo">
                                            <span><i class="bi bi-fire"></i> -{{ $descontoTabela }}%</span>
                                        @else
                                            <span class="upc-nodisc">Sem desconto</span>
                                        @endif
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="upc-actions">
                            <button type="button" onclick="copyProductName({{ $index }}, {{ $nomeJs }})" title="Copiar nome">
                                <i class="bi bi-clipboard"></i><span>Nome</span>
                            </button>
                            <button type="button" onclick="copyProductCode({{ $index }}, {{ $codigoJs }})" title="Copiar código">
                                <i class="bi bi-upc-scan"></i><span>Código</span>
                            </button>
                            {{-- Buscar no Mercado Livre (nome ou código de barras): o que escolher só entra no produto ao salvar --}}
                            <button type="button" wire:click="openMlForRow({{ $index }})" class="upc-ml {{ !empty($product['ml']) ? 'is-set' : '' }}"
                                    title="{{ !empty($product['ml']) ? 'Mercado Livre escolhido: ' . ($product['ml']['title'] ?? '') . ' (entra ao salvar). Toque para trocar.' : 'Buscar fotos, descrição e ficha no Mercado Livre' }}">
                                <i class="bi {{ !empty($product['ml']) ? 'bi-check-circle-fill' : 'bi-cloud-download' }}" wire:loading.remove wire:target="openMlForRow({{ $index }})"></i>
                                <i class="bi bi-arrow-repeat upc-spin" wire:loading wire:target="openMlForRow({{ $index }})"></i><span>ML</span>
                            </button>
                            <button type="button" wire:click="removeProduct({{ $index }})" class="is-danger" title="Remover da lista">
                                <i class="bi bi-trash3"></i><span>Remover</span>
                            </button>
                        </div>
                    </div>
                @endforeach
        </div>
    @endif
</div>

<style>
    .upc-grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
    @media (max-width: 640px) { .upc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; } }
    @media (max-width: 359px) { .upc-grid { grid-template-columns: 1fr; } }

    .upc-card { position: relative; display: flex; flex-direction: column; min-width: 0; border-radius: 18px; overflow: hidden;
        background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 1px 2px rgb(15 23 42 / .05), 0 6px 18px -10px rgb(15 23 42 / .18);
        transition: box-shadow .2s, border-color .2s, transform .2s; }
    .upc-card:hover { border-color: rgb(196 181 253); box-shadow: 0 10px 28px -12px rgb(124 58 237 / .35); }
    .upc-card.is-promo { border-color: rgb(253 164 175); }
    .dark .upc-card { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .dark .upc-card.is-promo { border-color: rgb(190 18 60 / .6); }

    .upc-media { position: relative; background: #fff; }
    .upc-photo { position: relative; aspect-ratio: 1 / 1; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 34px 12px 10px; }
    .upc-photo img { width: 100%; height: 100%; object-fit: contain; }
    .upc-photo-hint { position: absolute; left: 50%; bottom: 10px; transform: translateX(-50%); display: inline-flex; gap: 4px; align-items: center;
        font-size: 11px; font-weight: 700; color: #fff; background: rgb(15 23 42 / .72); padding: 4px 10px; border-radius: 999px; white-space: nowrap;
        opacity: 0; transition: opacity .2s; pointer-events: none; }
    .upc-photo:hover .upc-photo-hint { opacity: 1; }
    @media (hover: none) { .upc-photo-hint { opacity: .85; font-size: 10px; } }

    .upc-chip { position: absolute; top: 8px; display: inline-flex; align-items: center; gap: 3px; height: 26px; padding: 0 8px; border-radius: 999px;
        font-size: 11px; font-weight: 700; background: rgb(255 255 255 / .92); border: 1px solid rgb(226 232 240); color: rgb(71 85 105);
        box-shadow: 0 1px 3px rgb(15 23 42 / .08); backdrop-filter: blur(6px); }
    .upc-chip input { background: transparent; border: 0; padding: 0; font: inherit; color: rgb(30 41 59); outline: none; box-shadow: none; min-width: 0; }
    .upc-chip input:focus { box-shadow: none; }
    .upc-code { left: 8px; max-width: calc(100% - 92px); }
    .upc-code i { color: rgb(124 58 237); }
    .upc-code input { width: 7ch; }
    .upc-qty { right: 8px; }
    .upc-qty span { color: rgb(100 116 139); font-weight: 600; }
    .upc-qty input { width: 3.2ch; text-align: center; -moz-appearance: textfield; }
    .upc-ribbon { position: absolute; left: 8px; bottom: 8px; display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 800; color: #fff;
        padding: 3px 9px; border-radius: 999px; background: linear-gradient(135deg, #f43f5e, #f97316); box-shadow: 0 4px 10px -4px rgb(244 63 94 / .6); }

    .upc-body { display: flex; flex-direction: column; gap: 8px; padding: 10px 10px 8px; border-top: 1px solid rgb(241 245 249); flex: 1; }
    .dark .upc-body { border-top-color: rgb(30 41 59); }
    .upc-name { width: 100%; border: 1px solid transparent; background: transparent; border-radius: 10px; padding: 4px 6px; font-size: 13px; font-weight: 700;
        color: rgb(15 23 42); text-overflow: ellipsis; }
    .upc-name:hover { border-color: rgb(226 232 240); }
    .upc-name:focus { outline: none; border-color: rgb(167 139 250); box-shadow: 0 0 0 3px rgb(167 139 250 / .2); background: #fff; }
    .dark .upc-name { color: rgb(241 245 249); }
    .dark .upc-name:focus { background: rgb(30 41 59); }
    .upc-ean { display: flex; align-items: center; gap: 6px; padding: 5px 8px; border-radius: 10px; background: rgb(248 250 252); border: 1px solid rgb(241 245 249); cursor: text; }
    .upc-ean i { color: rgb(100 116 139); font-size: 13px; }
    .upc-ean input { width: 100%; min-width: 0; border: 0; background: transparent; padding: 0; font-size: 12px; font-weight: 600; letter-spacing: .02em; color: rgb(30 41 59); outline: none; box-shadow: none; }
    .upc-ean input::placeholder { color: rgb(148 163 184); font-weight: 500; letter-spacing: 0; }
    .upc-ean:focus-within { border-color: rgb(167 139 250); box-shadow: 0 0 0 3px rgb(167 139 250 / .18); }
    .dark .upc-ean { background: rgb(30 41 59 / .7); border-color: rgb(51 65 85); }
    .dark .upc-ean input { color: rgb(226 232 240); }
    .upc-ean-find { flex: none; display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 8px; border: 0; background: rgb(237 233 254); color: rgb(109 40 217); font-size: 11px; font-weight: 700; cursor: pointer; }
    .upc-ean-find:hover { background: rgb(221 214 254); }
    .upc-ean-find i { color: inherit !important; font-size: 11px !important; }
    @media (max-width: 640px) { .upc-ean-find span { display: none; } .upc-ean-find { padding: 4px 7px; } }
    .upc-spin { display: inline-block; animation: upcSpin .8s linear infinite; }
    @keyframes upcSpin { to { transform: rotate(360deg); } }
    .upc-ean-opts { display: flex; flex-direction: column; gap: 4px; padding: 6px; border-radius: 10px; background: rgb(245 243 255); border: 1px dashed rgb(196 181 253); }
    .upc-ean-opts-t { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(109 40 217); }
    .upc-ean-opts button { display: flex; flex-direction: column; align-items: flex-start; text-align: left; padding: 5px 8px; border-radius: 8px; border: 1px solid rgb(221 214 254); background: #fff; cursor: pointer; }
    .upc-ean-opts button:hover { border-color: rgb(139 92 246); }
    .upc-ean-opts b { font-size: 12px; color: rgb(30 41 59); letter-spacing: .02em; }
    .upc-ean-opts small { font-size: 10px; color: rgb(100 116 139); line-height: 1.25; }
    .dark .upc-ean-find { background: rgb(76 29 149 / .5); color: rgb(221 214 254); }
    .dark .upc-ean-opts { background: rgb(46 16 101 / .35); border-color: rgb(91 33 182); }
    .dark .upc-ean-opts button { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .dark .upc-ean-opts b { color: rgb(226 232 240); }
    .upc-cat button { padding: 6px 10px !important; border-radius: 10px !important; border-color: rgb(226 232 240) !important; }
    .dark .upc-cat button { border-color: rgb(51 65 85) !important; }

    .upc-prices { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .upc-field, .upc-promo { display: flex; flex-direction: column; gap: 1px; min-width: 0; padding: 5px 8px; border-radius: 10px; background: rgb(248 250 252);
        border: 1px solid rgb(241 245 249); cursor: text; }
    .dark .upc-field, .dark .upc-promo { background: rgb(30 41 59 / .7); border-color: rgb(51 65 85); }
    .upc-field:focus-within { border-color: rgb(167 139 250); box-shadow: 0 0 0 3px rgb(167 139 250 / .18); }
    .upc-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: rgb(100 116 139); }
    .upc-money { display: flex; align-items: baseline; gap: 3px; min-width: 0; }
    .upc-money small { font-size: 10px; color: rgb(148 163 184); }
    .upc-money input { width: 100%; min-width: 0; border: 0; background: transparent; padding: 0; font-size: 13px; font-weight: 700; color: rgb(30 41 59);
        outline: none; box-shadow: none; -moz-appearance: textfield; }
    .upc-money input.is-struck { text-decoration: line-through; color: rgb(148 163 184); font-weight: 600; }
    .dark .upc-money input { color: rgb(226 232 240); }
    .upc-field-sale { background: rgb(236 253 245); border-color: rgb(209 250 229); }
    .upc-field-sale .upc-money input { color: rgb(4 120 87); }
    .dark .upc-field-sale { background: rgb(6 78 59 / .35); border-color: rgb(6 95 70 / .6); }
    .dark .upc-field-sale .upc-money input { color: rgb(110 231 183); }
    .upc-card input[type=number]::-webkit-outer-spin-button, .upc-card input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    .upc-promo { cursor: pointer; }
    .upc-promo-row { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: rgb(71 85 105); white-space: nowrap; overflow: hidden; }
    .upc-promo-row input { width: 14px; height: 14px; border-radius: 4px; color: rgb(244 63 94); accent-color: rgb(244 63 94); flex-shrink: 0; }
    .upc-nodisc { font-size: 11px; }
    .upc-promo.is-on { background: rgb(255 241 242); border-color: rgb(254 205 211); }
    .upc-promo.is-on .upc-promo-row { color: rgb(225 29 72); }
    .dark .upc-promo.is-on { background: rgb(136 19 55 / .35); border-color: rgb(159 18 57 / .6); }
    .dark .upc-promo.is-on .upc-promo-row { color: rgb(253 164 175); }
    .upc-promo.is-disabled { cursor: not-allowed; }
    .upc-promo.is-disabled .upc-promo-row { color: rgb(148 163 184); font-weight: 600; }

    .upc-actions { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; border-top: 1px solid rgb(241 245 249); }
    .upc-actions button { flex-direction: column; gap: 1px; padding: 6px 2px; font-size: 10.5px; line-height: 1.1; }
    .upc-actions button i { font-size: 14px; }
    .upc-actions button.upc-ml { color: rgb(45 50 119); background: rgb(255 230 0 / .22); }
    .upc-actions button.upc-ml:hover { background: rgb(255 230 0 / .45); color: rgb(45 50 119); }
    .upc-actions button.upc-ml.is-set { color: rgb(4 120 87); background: rgb(209 250 229); }
    .dark .upc-actions button.upc-ml { color: rgb(253 230 138); background: rgb(161 98 7 / .25); }
    .dark .upc-actions button.upc-ml.is-set { color: rgb(110 231 183); background: rgb(6 78 59 / .4); }
    .dark .upc-actions { border-top-color: rgb(30 41 59); }
    .upc-actions button { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 8px 4px; font-size: 11px; font-weight: 600;
        color: rgb(71 85 105); transition: background .15s, color .15s; }
    .upc-actions button + button { border-left: 1px solid rgb(241 245 249); }
    .dark .upc-actions button { color: rgb(148 163 184); }
    .dark .upc-actions button + button { border-left-color: rgb(30 41 59); }
    .upc-actions button:hover { background: rgb(245 243 255); color: rgb(109 40 217); }
    .upc-actions button.is-danger:hover { background: rgb(254 242 242); color: rgb(220 38 38); }
    .dark .upc-actions button:hover { background: rgb(30 41 59); }
    .upc-actions button.copied { color: rgb(5 150 105); }
    @media (max-width: 640px) {
        .upc-actions button span { display: none; }
        .upc-actions button { padding: 9px 4px; font-size: 14px; }
        .upc-body { padding: 8px 7px 7px; gap: 6px; }
        .upc-field, .upc-promo { padding: 4px 6px; }
        .upc-money input { font-size: 12px; }
        .upc-name { font-size: 12px; }
        .upc-photo { padding-top: 32px; }
        .upc-code input { width: 5.5ch; }
        .upc-nodisc { font-size: 10px; }
    }
</style>
