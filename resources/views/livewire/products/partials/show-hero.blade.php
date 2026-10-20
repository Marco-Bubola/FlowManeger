{{-- Topo da tela do produto: fotos, variações, preços, estoque e todas as ações num lugar só. --}}
@php
    $v = $viewed;
    $isKit = ($v->tipo ?? 'simples') === 'kit';
    $promo = $v->livePromotion();
    $imgs = $v->all_images;
    if (empty($imgs)) { $imgs = [$v->image_url]; }
    $cost = (float) $v->price;
    $sale = (float) $v->price_sale;
    $charged = $promo ? (float) $promo->promo_price : $sale;
    $unitProfit = $charged - $cost;
    $margin = $charged > 0 ? $unitProfit / $charged * 100 : 0;
    $markup = $cost > 0 ? ($charged / $cost - 1) * 100 : null;
    $stock = $isKit ? (int) $kitMontaveis : (int) $v->stock_quantity;
    $stockTone = $stock <= 0 ? 'is-out' : ($stock <= 3 ? 'is-low' : 'is-ok');
    $statusOn = ($v->status ?? 'ativo') === 'ativo';
    $family = $products->count() > 1;
    $soldQty = (int) ($analytics['total_quantity_sold'] ?? 0);
    $lastSale = !empty($analytics['last_sale_date']) ? \Carbon\Carbon::parse($analytics['last_sale_date']) : null;
    $editUrl = $isKit ? route('products.kit.edit', $v) : route('products.edit', $v);
@endphp

<section class="sph" x-data="{ img: 0, copied: '' , copy(t, k) { navigator.clipboard?.writeText(t); this.copied = k; setTimeout(() => this.copied = '', 1500) } }">
    {{-- Fotos --}}
    <div class="sph-media">
        <div class="sph-photo">
            @foreach ($imgs as $i => $src)
                <img src="{{ $src }}" alt="{{ $v->name }}" x-show="img === {{ $i }}" @if($i) x-cloak @endif loading="{{ $i ? 'lazy' : 'eager' }}">
            @endforeach
            @if ($promo)
                <span class="sph-flag is-promo"><i class="bi bi-fire"></i>-{{ $promo->discount_percent }}%</span>
            @elseif ($v->made_to_order)
                <span class="sph-flag is-promo" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"><i class="bi bi-hammer"></i>{{ $v->madeToOrderLabel() }}</span>
            @elseif ($stock <= 0)
                <span class="sph-flag is-out"><i class="bi bi-x-circle"></i>Sem estoque</span>
            @endif
            @if (count($imgs) > 1)
                <button type="button" class="sph-nav is-prev" x-on:click="img = (img + {{ count($imgs) - 1 }}) % {{ count($imgs) }}" aria-label="Foto anterior"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="sph-nav is-next" x-on:click="img = (img + 1) % {{ count($imgs) }}" aria-label="Próxima foto"><i class="bi bi-chevron-right"></i></button>
                <span class="sph-count" x-text="(img + 1) + '/{{ count($imgs) }}'"></span>
            @endif
        </div>
        @if (count($imgs) > 1)
            <div class="sph-thumbs">
                @foreach ($imgs as $i => $src)
                    <button type="button" x-on:click="img = {{ $i }}" :class="img === {{ $i }} && 'is-on'"><img src="{{ $src }}" alt="" loading="lazy"></button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Dados --}}
    <div class="sph-body">
        <div class="sph-pills">
            <button type="button" wire:click="toggleStatus" class="sph-pill {{ $statusOn ? 'is-green' : 'is-gray' }}" title="Clique para {{ $statusOn ? 'inativar' : 'ativar' }}">
                <i class="bi {{ $statusOn ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>{{ $statusOn ? 'Ativo' : ucfirst($v->status ?? 'inativo') }}
            </button>
            @if ($isKit)<span class="sph-pill is-purple"><i class="bi bi-boxes"></i>Kit</span>@endif
            @if ($category)<span class="sph-pill"><i class="{{ $this->getCategoryIcon($category->icone) }}"></i>{{ $category->name }}</span>@endif
            @if ($v->brand)<span class="sph-pill"><i class="bi bi-award"></i>{{ $v->brand }}</span>@endif
            @if ($promo)<span class="sph-pill is-rose"><i class="bi bi-fire"></i>Promoção até {{ $promo->ends_at ? $promo->ends_at->format('d/m') : 'sem fim' }}</span>@endif
        </div>

        <h2 class="sph-name">{{ $v->name }}</h2>

        <div class="sph-codes">
            <button type="button" class="sph-code" x-on:click="copy(@js($v->product_code), 'code')" title="Copiar código">
                <i class="bi bi-upc-scan"></i>{{ $v->product_code }}
                <i class="bi" :class="copied === 'code' ? 'bi-check2 text-emerald-500' : 'bi-copy'"></i>
            </button>
            @if ($v->barcode)
                <button type="button" class="sph-code" x-on:click="copy(@js($v->barcode), 'ean')" title="Copiar código de barras">
                    <i class="bi bi-barcode"></i>{{ $v->barcode }}
                    <i class="bi" :class="copied === 'ean' ? 'bi-check2 text-emerald-500' : 'bi-copy'"></i>
                </button>
            @endif
        </div>

        @if ($family)
            <div class="sph-vars">
                <span class="sph-label"><i class="bi bi-layers"></i>{{ $products->count() }} variações</span>
                <div class="sph-var-list">
                    @foreach ($products as $fv)
                        @php $fvStock = (int) $fv->stock_quantity; @endphp
                        <button type="button" wire:click="viewVariant({{ $fv->id }})" class="sph-var {{ $fv->id === $v->id ? 'is-on' : '' }} {{ $fvStock <= 0 ? 'is-out' : '' }}" title="{{ $fv->name }}">
                            <img src="{{ $fv->image_url }}" alt="" loading="lazy">
                            <span class="sph-var-name">{{ $fv->variation_value ?: ($fv->is_variation_parent ? 'Principal' : $fv->product_code) }}</span>
                            <span class="sph-var-stock">{{ $fvStock }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="sph-panels">
            {{-- Preços --}}
            <div class="sph-panel">
                <div class="sph-panel-head">
                    <span class="sph-label"><i class="bi bi-cash-coin"></i>Preços</span>
                    @if (! $editingPrices)
                        <button type="button" class="sph-mini" wire:click="startPrices"><i class="bi bi-pencil"></i>Alterar</button>
                    @endif
                </div>
                @if ($editingPrices)
                    <div class="sph-price-form" x-on:keydown.enter.prevent="$wire.savePrices()">
                        <label><span>Custo</span><input type="text" inputmode="decimal" wire:model="priceCost"></label>
                        <label><span>Venda</span><input type="text" inputmode="decimal" wire:model="priceSale" class="{{ $errors->has('priceSale') ? 'has-error' : '' }}"></label>
                        <label><span>De (riscado)</span><input type="text" inputmode="decimal" wire:model="priceOriginal" placeholder="opcional"></label>
                        <div class="sph-price-actions">
                            <button type="button" class="sph-mini" wire:click="$set('editingPrices', false)">Cancelar</button>
                            <button type="button" class="sph-mini is-primary" wire:click="savePrices"><i class="bi bi-check2"></i>Salvar</button>
                        </div>
                    </div>
                @else
                    <div class="sph-price">
                        @if ($promo)
                            <span class="sph-price-old">R$ {{ number_format($sale, 2, ',', '.') }}</span>
                        @elseif ($v->price_original && (float) $v->price_original > $sale)
                            <span class="sph-price-old">R$ {{ number_format((float) $v->price_original, 2, ',', '.') }}</span>
                        @endif
                        <strong>R$ {{ number_format($charged, 2, ',', '.') }}</strong>
                    </div>
                    <div class="sph-facts">
                        <div><span>Custo</span><b>R$ {{ number_format($cost, 2, ',', '.') }}</b></div>
                        <div><span>Lucro/un.</span><b class="{{ $unitProfit < 0 ? 'text-rose-600' : 'text-emerald-600 dark:text-emerald-400' }}">R$ {{ number_format($unitProfit, 2, ',', '.') }}</b></div>
                        <div><span>Margem</span><b>{{ number_format($margin, 1, ',', '.') }}%</b></div>
                        @if ($markup !== null)<div><span>Markup</span><b>{{ number_format($markup, 0, ',', '.') }}%</b></div>@endif
                    </div>
                @endif
            </div>

            {{-- Estoque --}}
            <div class="sph-panel">
                <div class="sph-panel-head">
                    <span class="sph-label"><i class="bi bi-boxes"></i>{{ $isKit ? 'Kits montáveis' : 'Estoque' }}</span>
                    <a href="{{ route('gestao.stock-movements', ['search' => $v->product_code]) }}" class="sph-mini"><i class="bi bi-clock-history"></i>Histórico</a>
                </div>
                <div class="sph-stock">
                    <strong class="{{ $stockTone }}">{{ $stock }}</strong>
                    <span>{{ $stock === 1 ? 'unidade' : 'unidades' }}@if ($family) · família: {{ (int) $products->sum('stock_quantity') }}@endif</span>
                </div>
                @if ($isKit)
                    <p class="sph-note">O estoque do kit vem dos itens. Veja a composição abaixo.</p>
                @else
                    <div class="sph-stock-ctrl">
                        <button type="button" wire:click="stepStock(-1)" @disabled($stock <= 0) aria-label="Tirar 1"><i class="bi bi-dash-lg"></i></button>
                        <button type="button" wire:click="stepStock(1)" aria-label="Somar 1"><i class="bi bi-plus-lg"></i></button>
                        <div class="sph-stock-set">
                            <input type="text" inputmode="numeric" wire:model="stockInput" placeholder="Definir" x-on:keydown.enter.prevent="$wire.setStock()">
                            <button type="button" wire:click="setStock" title="Salvar estoque"><i class="bi bi-check2"></i></button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="sph-stats">
            <div><i class="bi bi-cart-check"></i><span>Vendidos</span><b>{{ $soldQty }}</b></div>
            <div><i class="bi bi-calendar-check"></i><span>Última venda</span><b>{{ $lastSale ? $lastSale->format('d/m/Y') : '—' }}</b></div>
            <a href="{{ route('products.interested') }}"><i class="bi bi-heart"></i><span>Interessados</span><b>{{ $interestedCount }}</b></a>
            <div><i class="bi bi-calendar-plus"></i><span>Cadastrado</span><b>{{ optional($v->created_at)->format('d/m/Y') ?? '—' }}</b></div>
        </div>
    </div>

    {{-- Ações --}}
    <div class="sph-actions">
        <span class="sph-label"><i class="bi bi-lightning-charge"></i>Ações</span>
        <div class="sph-tiles">
            <a href="{{ route('sales.create', ['product_id' => $v->id]) }}" class="sph-tile is-indigo"><i class="bi bi-cart-plus"></i>Vender</a>
            <a href="{{ $editUrl }}" class="sph-tile is-amber"><i class="bi bi-pencil-square"></i>Editar</a>
            <button type="button" x-on:click="$dispatch('open-product-share', { productId: {{ $v->id }} })" class="sph-tile is-green"><i class="bi bi-whatsapp"></i>WhatsApp</button>
            <a href="{{ route('promotions.index', ['produto' => $v->id]) }}" class="sph-tile is-rose"><i class="bi bi-fire"></i>{{ $promo ? 'Promoção' : 'Promover' }}</a>
            @unless ($isKit)
                <a href="{{ route('products.breakdown', $v) }}" class="sph-tile is-orange"><i class="bi bi-scissors"></i>Desmembrar</a>
            @endunless
            <button type="button" wire:click="$dispatch('openExportModal', { productId: {{ $v->id }} })" class="sph-tile is-sky"><i class="bi bi-image"></i>Card</button>
            <a href="{{ route('mercadolivre.products.publish', $v) }}" class="sph-tile is-yellow"><i class="bi bi-shop"></i>Mercado Livre</a>
            <a href="{{ route('shopee.products.publish', $v) }}" class="sph-tile is-orange"><i class="bi bi-bag"></i>Shopee</a>
            <a href="{{ route('portal.catalog', ['userId' => auth()->id(), 'search' => $v->name]) }}" target="_blank" rel="noopener" class="sph-tile is-purple"><i class="bi bi-shop-window"></i>Ver no catálogo</a>
            <button type="button" wire:click="duplicateProduct" wire:loading.attr="disabled" wire:target="duplicateProduct" class="sph-tile is-slate"><i class="bi bi-files"></i>Duplicar</button>
            <a href="{{ route('products.interested') }}" class="sph-tile is-pink"><i class="bi bi-heart"></i>Interessados</a>
            <button type="button" wire:click="askDelete" class="sph-tile is-danger"><i class="bi bi-trash3"></i>Excluir</button>
        </div>
    </div>

    @if ($confirmingDelete)
        <x-delete-dialog confirm="deleteProduct" cancel="cancelDelete" title="Excluir produto?" confirm-label="Excluir" icon="bi-box-seam">
            "{{ \Illuminate\Support\Str::limit($v->name, 60) }}" ({{ $v->product_code }}) sai da lista. As vendas antigas continuam registradas.
        </x-delete-dialog>
    @endif
</section>

<style>
    .sph { display: grid; gap: 14px; grid-template-columns: minmax(0, 1fr); margin-bottom: 16px; padding: 14px; border-radius: 24px;
        background: #fff; border: 1px solid rgb(226 232 240 / .9); box-shadow: 0 10px 30px -18px rgb(79 70 229 / .35); }
    .dark .sph { background: rgb(15 23 42 / .85); border-color: rgb(51 65 85 / .7); }
    @media (min-width: 700px) { .sph { grid-template-columns: minmax(0, 300px) minmax(0, 1fr); padding: 18px; gap: 18px; } .sph-actions { grid-column: 1 / -1; } }
    @media (min-width: 1280px) { .sph { grid-template-columns: minmax(0, 320px) minmax(0, 1fr) minmax(0, 300px); } .sph-actions { grid-column: auto; } }
    @media (min-width: 1900px) { .sph { grid-template-columns: minmax(0, 380px) minmax(0, 1fr) minmax(0, 340px); } }

    .sph-photo { position: relative; aspect-ratio: 1; border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #f8fafc, #eef2ff); }
    .dark .sph-photo { background: linear-gradient(135deg, #1e293b, #0f172a); }
    .sph-photo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; padding: 14px; }
    .sph-flag { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: .74rem; font-weight: 800; color: #fff; }
    .sph-flag.is-promo { background: linear-gradient(90deg, #f43f5e, #f97316); }
    .sph-flag.is-out { background: #ef4444; }
    .sph-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border-radius: 999px; background: rgb(255 255 255 / .9); color: #4f46e5; box-shadow: 0 2px 8px rgb(0 0 0 / .15); display: flex; align-items: center; justify-content: center; }
    .sph-nav.is-prev { left: 8px; } .sph-nav.is-next { right: 8px; }
    .sph-count { position: absolute; bottom: 10px; right: 10px; font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgb(15 23 42 / .6); color: #fff; }
    .sph-thumbs { display: flex; gap: 6px; margin-top: 8px; overflow-x: auto; padding-bottom: 2px; }
    .sph-thumbs button { flex: 0 0 52px; height: 52px; border-radius: 12px; overflow: hidden; border: 2px solid transparent; background: #f8fafc; }
    .sph-thumbs button.is-on { border-color: #6366f1; }
    .sph-thumbs img { width: 100%; height: 100%; object-fit: contain; }

    .sph-body { min-width: 0; display: flex; flex-direction: column; gap: 10px; }
    .sph-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .sph-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; background: rgb(241 245 249); color: rgb(71 85 105); }
    .dark .sph-pill { background: rgb(30 41 59); color: rgb(203 213 225); }
    .sph-pill.is-green { background: rgb(220 252 231); color: rgb(21 128 61); }
    .sph-pill.is-gray { background: rgb(226 232 240); color: rgb(100 116 139); }
    .sph-pill.is-purple { background: rgb(243 232 255); color: rgb(126 34 206); }
    .sph-pill.is-rose { background: rgb(255 228 230); color: rgb(190 18 60); }
    .dark .sph-pill.is-green { background: rgb(34 197 94 / .15); color: rgb(134 239 172); }
    .dark .sph-pill.is-rose { background: rgb(244 63 94 / .15); color: rgb(253 164 175); }
    .dark .sph-pill.is-purple { background: rgb(168 85 247 / .15); color: rgb(216 180 254); }
    .sph-name { font-size: clamp(1.1rem, 1rem + .8vw, 1.6rem); font-weight: 900; line-height: 1.2; color: rgb(15 23 42); overflow-wrap: anywhere; }
    .dark .sph-name { color: #fff; }
    .sph-codes { display: flex; flex-wrap: wrap; gap: 6px; }
    .sph-code { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 10px; font-size: .78rem; font-weight: 700; font-family: ui-monospace, monospace;
        background: rgb(238 242 255); color: rgb(67 56 202); }
    .dark .sph-code { background: rgb(79 70 229 / .18); color: rgb(199 210 254); }
    .sph-label { display: inline-flex; align-items: center; gap: 6px; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: rgb(100 116 139); }
    .sph-var-list { display: flex; gap: 6px; overflow-x: auto; padding: 4px 1px 4px; margin-top: 4px; }
    .sph-var { flex: 0 0 auto; display: flex; align-items: center; gap: 6px; padding: 4px 10px 4px 4px; border-radius: 999px; border: 2px solid rgb(226 232 240); background: #fff; font-size: .76rem; font-weight: 700; color: rgb(51 65 85); max-width: 210px; }
    .dark .sph-var { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .sph-var img { width: 28px; height: 28px; border-radius: 999px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
    .sph-var-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sph-var-stock { font-size: .66rem; padding: 1px 6px; border-radius: 999px; background: rgb(220 252 231); color: rgb(21 128 61); }
    .sph-var.is-out .sph-var-stock { background: rgb(254 226 226); color: rgb(185 28 28); }
    .sph-var.is-on { border-color: #6366f1; background: rgb(238 242 255); color: rgb(67 56 202); }
    .dark .sph-var.is-on { background: rgb(79 70 229 / .25); color: #fff; }

    .sph-panels { display: grid; gap: 10px; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 520px) and (max-width: 699px), (min-width: 1100px) { .sph-panels { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .sph-panel { border-radius: 18px; padding: 12px; background: rgb(248 250 252); border: 1px solid rgb(226 232 240 / .8); min-width: 0; }
    .dark .sph-panel { background: rgb(30 41 59 / .55); border-color: rgb(51 65 85 / .6); }
    .sph-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
    .sph-mini { display: inline-flex; align-items: center; gap: 4px; padding: 4px 9px; border-radius: 9px; font-size: .72rem; font-weight: 700; background: #fff; color: rgb(79 70 229); border: 1px solid rgb(224 231 255); }
    .dark .sph-mini { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(165 180 252); }
    .sph-mini.is-primary { background: linear-gradient(90deg, #4f46e5, #9333ea); color: #fff; border: 0; }
    .sph-price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 8px; }
    .sph-price strong { font-size: 1.7rem; font-weight: 900; color: rgb(5 150 105); line-height: 1.1; }
    .dark .sph-price strong { color: rgb(52 211 153); }
    .sph-price-old { font-size: .9rem; font-weight: 700; color: rgb(148 163 184); text-decoration: line-through; }
    .sph-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 10px; margin-top: 8px; font-size: .74rem; }
    .sph-facts div { display: flex; justify-content: space-between; gap: 6px; }
    .sph-facts span { color: rgb(100 116 139); } .sph-facts b { color: rgb(30 41 59); font-weight: 800; white-space: nowrap; }
    .dark .sph-facts b { color: rgb(226 232 240); }
    .sph-price-form { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
    .sph-price-form label { display: flex; flex-direction: column; gap: 2px; font-size: .64rem; font-weight: 700; color: rgb(100 116 139); text-transform: uppercase; }
    .sph-price-form input { width: 100%; border-radius: 10px; border: 1.5px solid rgb(203 213 225); padding: 7px 8px; font-size: .9rem; font-weight: 800; background: #fff; color: rgb(15 23 42); }
    .sph-price-form input.has-error { border-color: #f43f5e; }
    .dark .sph-price-form input { background: rgb(15 23 42); border-color: rgb(71 85 105); color: #fff; }
    .sph-price-actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 6px; margin-top: 2px; }
    .sph-stock { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
    .sph-stock strong { font-size: 1.9rem; font-weight: 900; line-height: 1.05; }
    .sph-stock strong.is-ok { color: rgb(5 150 105); } .sph-stock strong.is-low { color: rgb(217 119 6); } .sph-stock strong.is-out { color: rgb(225 29 72); }
    .sph-stock span { font-size: .74rem; color: rgb(100 116 139); font-weight: 600; }
    .sph-note { font-size: .74rem; color: rgb(100 116 139); margin-top: 6px; }
    .sph-stock-ctrl { display: flex; gap: 6px; margin-top: 8px; }
    .sph-stock-ctrl > button { width: 40px; height: 40px; border-radius: 12px; background: #fff; border: 1.5px solid rgb(203 213 225); color: rgb(51 65 85); font-size: 1.05rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .sph-stock-ctrl > button:disabled { opacity: .35; }
    .dark .sph-stock-ctrl > button { background: rgb(15 23 42); border-color: rgb(71 85 105); color: #fff; }
    .sph-stock-set { flex: 1; min-width: 0; display: flex; border-radius: 12px; overflow: hidden; border: 1.5px solid rgb(203 213 225); background: #fff; }
    .dark .sph-stock-set { background: rgb(15 23 42); border-color: rgb(71 85 105); }
    .sph-stock-set input { flex: 1; min-width: 0; border: 0; background: transparent; padding: 0 10px; font-size: .9rem; font-weight: 800; color: rgb(15 23 42); }
    .dark .sph-stock-set input { color: #fff; }
    .sph-stock-set input:focus { outline: none; box-shadow: none; }
    .sph-stock-set button { width: 40px; background: linear-gradient(135deg, #10b981, #0d9488); color: #fff; }
    .sph-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
    @media (min-width: 900px) { .sph-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .sph-stats > * { display: grid; grid-template-columns: auto 1fr; grid-template-rows: auto auto; column-gap: 8px; align-items: center; padding: 8px 10px; border-radius: 14px; background: rgb(248 250 252); border: 1px solid rgb(226 232 240 / .8); }
    .dark .sph-stats > * { background: rgb(30 41 59 / .55); border-color: rgb(51 65 85 / .6); }
    .sph-stats i { grid-row: 1 / 3; font-size: 1.05rem; color: #6366f1; }
    .sph-stats span { font-size: .64rem; font-weight: 700; text-transform: uppercase; color: rgb(100 116 139); }
    .sph-stats b { font-size: .9rem; font-weight: 900; color: rgb(15 23 42); }
    .dark .sph-stats b { color: #fff; }

    .sph-actions { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
    .sph-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    @media (min-width: 700px) and (max-width: 1279px) { .sph-tiles { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
    .sph-tile { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; min-height: 64px; padding: 8px 4px; border-radius: 16px; font-size: .72rem; font-weight: 800; text-align: center; line-height: 1.15;
        background: var(--t-bg); color: var(--t-fg); border: 1px solid var(--t-bd); transition: transform .12s, box-shadow .12s; }
    .sph-tile i { font-size: 1.2rem; }
    .sph-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 16px -8px var(--t-fg); }
    .sph-tile.is-indigo { --t-bg: #eef2ff; --t-fg: #4338ca; --t-bd: #c7d2fe; }
    .sph-tile.is-amber { --t-bg: #fffbeb; --t-fg: #b45309; --t-bd: #fde68a; }
    .sph-tile.is-green { --t-bg: #ecfdf5; --t-fg: #047857; --t-bd: #a7f3d0; }
    .sph-tile.is-rose { --t-bg: #fff1f2; --t-fg: #be123c; --t-bd: #fecdd3; }
    .sph-tile.is-orange { --t-bg: #fff7ed; --t-fg: #c2410c; --t-bd: #fed7aa; }
    .sph-tile.is-sky { --t-bg: #f0f9ff; --t-fg: #0369a1; --t-bd: #bae6fd; }
    .sph-tile.is-yellow { --t-bg: #fefce8; --t-fg: #a16207; --t-bd: #fef08a; }
    .sph-tile.is-purple { --t-bg: #faf5ff; --t-fg: #7e22ce; --t-bd: #e9d5ff; }
    .sph-tile.is-pink { --t-bg: #fdf2f8; --t-fg: #be185d; --t-bd: #fbcfe8; }
    .sph-tile.is-slate { --t-bg: #f8fafc; --t-fg: #334155; --t-bd: #e2e8f0; }
    .sph-tile.is-danger { --t-bg: #fff; --t-fg: #e11d48; --t-bd: #fecdd3; }
    .dark .sph-tile { --t-bg: rgb(30 41 59 / .7); --t-bd: rgb(51 65 85); }
    .dark .sph-tile.is-indigo { --t-fg: #a5b4fc; } .dark .sph-tile.is-amber { --t-fg: #fcd34d; } .dark .sph-tile.is-green { --t-fg: #6ee7b7; }
    .dark .sph-tile.is-rose { --t-fg: #fda4af; } .dark .sph-tile.is-orange { --t-fg: #fdba74; } .dark .sph-tile.is-sky { --t-fg: #7dd3fc; }
    .dark .sph-tile.is-yellow { --t-fg: #fde047; } .dark .sph-tile.is-purple { --t-fg: #d8b4fe; } .dark .sph-tile.is-pink { --t-fg: #f9a8d4; }
    .dark .sph-tile.is-slate { --t-fg: #cbd5e1; } .dark .sph-tile.is-danger { --t-fg: #fb7185; }
    @media (max-width: 699px) { .sph-photo { aspect-ratio: auto; height: 240px; } }
    @media (max-width: 420px) { .sph { padding: 12px; border-radius: 20px; } .sph-tile { min-height: 58px; font-size: .68rem; } }
</style>
