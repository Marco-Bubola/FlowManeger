{{-- NOVO ANÚNCIO NO MERCADO LIVRE — 3 passos: 1. Produtos | 2. Catálogo | 3. Anúncio --}}
<div class="publish-product-page w-full mobile-393-base">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    <style>
        .publish-product-page { --pp-accent: #6366f1; }
        .pp-card { background: rgba(255,255,255,.92); border: 1px solid rgba(148,163,184,.28); border-radius: 22px; box-shadow: 0 8px 28px rgba(15,23,42,.06); overflow: hidden; }
        .dark .pp-card { background: rgba(15,23,42,.82); border-color: rgba(71,85,105,.5); }
        .pp-card-h { display: flex; align-items: center; gap: .65rem; padding: .85rem 1rem; border-bottom: 1px solid rgba(148,163,184,.2); }
        .dark .pp-card-h { border-color: rgba(71,85,105,.45); }
        .pp-card-h h3 { margin: 0; font-size: .95rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .dark .pp-card-h h3 { color: #f1f5f9; }
        .pp-card-h p { margin: .1rem 0 0; font-size: .72rem; color: #64748b; line-height: 1.3; }
        .pp-ico { width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .75rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem;
                  background: linear-gradient(135deg, #6366f1, #a855f7); box-shadow: 0 6px 16px rgba(99,102,241,.25); }
        .pp-ico.ml { background: #ffe600; color: #2d3277; box-shadow: 0 6px 16px rgba(234,179,8,.25); }
        .pp-ico.green { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,.25); }
        .pp-ico.teal { background: linear-gradient(135deg, #14b8a6, #0ea5e9); box-shadow: 0 6px 16px rgba(14,165,233,.22); }
        .pp-ico.pink { background: linear-gradient(135deg, #ec4899, #a855f7); }
        .pp-body { padding: 1rem; }
        .pp-label { display: block; margin-bottom: .35rem; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
        .pp-input { width: 100%; border-radius: .9rem; border: 1px solid rgba(148,163,184,.45); background: #f8fafc; padding: .65rem .85rem; font-size: .9rem; color: #0f172a; transition: border-color .15s, box-shadow .15s; }
        .pp-input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.15); background: #fff; }
        .dark .pp-input { background: rgba(30,41,59,.8); border-color: rgba(71,85,105,.7); color: #f1f5f9; }
        .pp-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 700; }
        .pp-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; font-size: .85rem; }
        .pp-row + .pp-row { margin-top: .45rem; }
        .pp-opt { display: flex; gap: .7rem; align-items: flex-start; padding: .75rem; border-radius: 1rem; border: 1.5px solid rgba(148,163,184,.35); cursor: pointer; transition: border-color .15s, background .15s; }
        .pp-opt:hover { border-color: #a5b4fc; }
        .pp-opt.on { border-color: #6366f1; background: rgba(99,102,241,.07); }
        .dark .pp-opt { border-color: rgba(71,85,105,.7); }
        .dark .pp-opt.on { border-color: #818cf8; background: rgba(99,102,241,.14); }
        .pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; padding: .6rem 1.1rem; border-radius: .9rem; font-weight: 800; font-size: .88rem; transition: transform .15s, filter .15s; white-space: nowrap; }
        .pp-btn:active { transform: scale(.97); }
        .pp-btn-primary { color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); box-shadow: 0 8px 20px rgba(99,102,241,.3); }
        .pp-btn-primary:hover { filter: brightness(1.06); }
        .pp-btn-go { color: #fff; background: linear-gradient(90deg, #10b981, #059669); box-shadow: 0 8px 20px rgba(16,185,129,.3); }
        .pp-btn-ghost { color: #334155; background: #fff; border: 1px solid rgba(148,163,184,.5); }
        .dark .pp-btn-ghost { color: #e2e8f0; background: rgba(30,41,59,.8); border-color: rgba(71,85,105,.7); }
        .pp-btn:disabled { opacity: .55; cursor: not-allowed; }
        .pp-step { display: inline-flex; align-items: center; gap: .45rem; padding: .4rem .8rem; border-radius: .8rem; font-size: .85rem; font-weight: 700; color: #64748b; transition: background .15s; }
        .pp-step:hover { background: rgba(99,102,241,.08); }
        .pp-step .n { width: 1.35rem; height: 1.35rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 800; background: rgba(148,163,184,.25); }
        .pp-step.on { color: #fff; background: linear-gradient(90deg, #6366f1, #a855f7); box-shadow: 0 6px 16px rgba(99,102,241,.25); }
        .pp-step.on .n { background: rgba(255,255,255,.25); }
        .pp-step.done { color: #059669; }
        .pp-step.done .n { background: #d1fae5; color: #059669; }
        .pp-actions-bar { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; padding: .75rem 1rem; }
        .product-card-modern { cursor: pointer; user-select: none; }
        .product-card-modern.selected { border-color: #6366f1 !important; box-shadow: 0 0 0 3px rgba(99,102,241,.25), 0 10px 26px rgba(99,102,241,.2) !important; }
        .pp-check { width: 1.6rem; height: 1.6rem; border-radius: 999px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,.9); background: rgba(15,23,42,.25); color: transparent; backdrop-filter: blur(4px); }
        .selected .pp-check { background: #6366f1; border-color: #6366f1; color: #fff; }
        .pp-thumb { width: 3.25rem; height: 3.25rem; flex-shrink: 0; border-radius: .8rem; object-fit: cover; border: 1px solid rgba(148,163,184,.3); background: #fff; }
        @keyframes pp-spin { to { transform: rotate(360deg); } }
        .pp-spinner { width: 3.5rem; height: 3.5rem; border-radius: 999px; border: 4px solid rgba(99,102,241,.15); border-top-color: #6366f1; border-right-color: #a855f7; animation: pp-spin .9s linear infinite; }
        @media (max-width: 639px) {
            .pp-body { padding: .85rem; }
            .pp-actions-bar { position: sticky; bottom: calc(env(safe-area-inset-bottom) + 76px); z-index: 20; margin-top: .75rem; }
        }
    </style>

    @php
        $steps = [1 => ['Produtos', 'bi-box-seam'], 2 => ['Catálogo', 'bi-search'], 3 => ['Anúncio', 'bi-megaphone']];
        $stepSub = [1 => 'Escolha o produto (ou vários, para um kit)', 2 => 'Use os dados do catálogo do ML, se existir', 3 => 'Confira preço, categoria e envio e publique'];
    @endphp

    <x-page-header title="Novo anúncio" icon="bi-megaphone" :back-route="route('mercadolivre.publications')"
        :subtitle="'Mercado Livre · Passo ' . $currentStep . ' de 3 · ' . $stepSub[$currentStep]">
        <x-slot name="actions">
            @if($currentStep > 1)
                <button type="button" wire:click="previousStep" class="pp-btn pp-btn-ghost" title="Voltar">
                    <i class="bi bi-arrow-left"></i><span class="hidden sm:inline">Voltar</span>
                </button>
            @endif
            @if($currentStep === 1)
                <button type="button" wire:click="nextStep" class="pp-btn pp-btn-primary" @disabled(empty($selectedProducts))>
                    Continuar <i class="bi bi-arrow-right"></i>
                </button>
            @elseif($currentStep === 2)
                <button type="button" wire:click="nextStep" class="pp-btn pp-btn-primary">
                    Continuar <i class="bi bi-arrow-right"></i>
                </button>
            @else
                <button type="submit" form="publish-form" class="pp-btn pp-btn-go" wire:loading.attr="disabled" wire:target="publishProduct">
                    <i class="bi bi-rocket-takeoff-fill"></i> Publicar
                </button>
            @endif
        </x-slot>
        <x-slot name="tabs">
            @foreach($steps as $n => [$label, $icon])
                <button type="button" wire:click="goToStep({{ $n }})" @disabled($n > 1 && empty($selectedProducts))
                    class="pp-step {{ $currentStep === $n ? 'on' : ($currentStep > $n ? 'done' : '') }}">
                    <span class="n">@if($currentStep > $n)<i class="bi bi-check-lg"></i>@else{{ $n }}@endif</span>
                    <span class="{{ $currentStep === $n ? '' : 'hidden sm:inline' }}">{{ $label }}</span>
                </button>
                @if($n < 3)<i class="bi bi-chevron-right text-xs text-slate-400"></i>@endif
            @endforeach
        </x-slot>
    </x-page-header>

    {{-- Carregando: indo para o catálogo / buscando --}}
    <div wire:loading.flex wire:target="nextStep, searchCatalog, goToStep"
        class="fixed inset-0 z-[9998] hidden flex-col items-center justify-center gap-4 bg-white/90 dark:bg-slate-900/92 backdrop-blur-sm px-6 text-center">
        <div class="pp-spinner"></div>
        <div>
            <p class="text-lg font-black text-slate-800 dark:text-white">Consultando o Mercado Livre</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buscando a categoria e o produto no catálogo pelo código de barras…</p>
        </div>
    </div>

    {{-- Carregando: publicando --}}
    <div wire:loading.flex wire:target="publishProduct"
        class="fixed inset-0 z-[9998] hidden flex-col items-center justify-center gap-4 bg-white/90 dark:bg-slate-900/92 backdrop-blur-sm px-6 text-center">
        <div class="pp-spinner" style="border-top-color:#10b981;border-right-color:#34d399"></div>
        <div>
            <p class="text-lg font-black text-slate-800 dark:text-white">Publicando no Mercado Livre</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Enviando fotos, atributos e descrição. Não feche esta tela.</p>
        </div>
    </div>

    {{-- ═══════════════ PASSO 1: PRODUTOS ═══════════════ --}}
    @if($currentStep === 1)
    @php
        $ready = $this->readyProducts;
        $shown = $this->filteredProducts;
        $notReady = $this->notReadySummary;
    @endphp
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-4 items-start">
        <section class="pp-card">
            <div class="pp-card-h flex-wrap">
                <div class="pp-ico"><i class="bi bi-box-seam"></i></div>
                <div class="min-w-0 flex-1">
                    <h3>Escolha o produto</h3>
                    <p>Toque para selecionar. Com mais de um produto, o anúncio vira um kit.</p>
                </div>
                <div class="flex w-full sm:w-auto gap-2">
                    <div class="relative flex-1 sm:w-64">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" placeholder="Nome, código ou EAN" class="pp-input" style="padding-left:2.2rem">
                    </div>
                    <select wire:model.live="selectedCategory" class="pp-input" style="width:auto;max-width:44%">
                        <option value="">Todas</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!empty($notReady))
                <div class="mx-4 mt-3 flex items-start gap-2 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 px-3 py-2 text-xs text-amber-800 dark:text-amber-200">
                    <i class="bi bi-info-circle-fill mt-0.5"></i>
                    <span>
                        {{ array_sum($notReady) }} produto(s) não aparecem porque estão
                        {{ collect($notReady)->map(fn ($n, $why) => "$why ($n)")->implode(', ') }}.
                        Para o ML, o produto precisa de estoque, foto, preço e código de barras.
                    </span>
                </div>
            @endif

            <div class="pp-body">
                @if($shown->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-box-seam text-3xl text-slate-400"></i>
                        </div>
                        <p class="font-bold text-slate-700 dark:text-slate-200">{{ $searchTerm || $selectedCategory ? 'Nenhum produto pronto com esse filtro' : 'Nenhum produto pronto para o ML' }}</p>
                        <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">Abrir produtos</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-3">
                        @foreach($shown as $item)
                            @php $isSelected = $this->isProductSelected($item->id); @endphp
                            <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}" wire:click="toggleProduct({{ $item->id }})" wire:key="p-{{ $item->id }}">
                                <div class="btn-action-group"><div class="pp-check"><i class="bi bi-check-lg"></i></div></div>
                                <div class="product-img-area">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="product-img" loading="lazy">
                                    <span class="badge-product-code"><i class="bi bi-upc-scan"></i> {{ $item->product_code }}</span>
                                    <span class="badge-quantity"><i class="bi bi-stack"></i> {{ $item->stock_quantity }}</span>
                                </div>
                                <div class="card-body">
                                    <div class="product-title">{{ ucwords($item->name) }}</div>
                                    <div class="price-area mt-2 flex flex-col gap-1">
                                        <span class="badge-price" title="Custo"><i class="bi bi-tag"></i> R$ {{ number_format($item->price ?? 0, 2, ',', '.') }}</span>
                                        <span class="badge-price-sale" title="Venda"><i class="bi bi-currency-dollar"></i> R$ {{ number_format($item->price_sale ?? $item->price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3 text-xs text-slate-500">
                        <span>Mostrando {{ $shown->count() }} de {{ $ready->count() }} produto(s) prontos</span>
                        @if($ready->count() > $shown->count())
                            <button type="button" wire:click="showMoreProducts" class="pp-btn pp-btn-ghost" style="padding:.4rem .8rem;font-size:.8rem">Mostrar mais</button>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <aside class="pp-card xl:sticky xl:top-4">
            <div class="pp-card-h">
                <div class="pp-ico green"><i class="bi bi-check2-square"></i></div>
                <div class="flex-1"><h3>Selecionados</h3><p>{{ count($selectedProducts) > 1 ? 'Kit com '.count($selectedProducts).' produtos' : 'Preço do anúncio vem daqui' }}</p></div>
                <span class="pp-chip bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">{{ count($selectedProducts) }}</span>
            </div>
            <div class="pp-body space-y-2">
                @forelse($selectedProducts as $idx => $p)
                    <div class="flex gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700 p-2.5" wire:key="sel-{{ $p['id'] }}">
                        <img src="{{ $p['image_url'] ?? '' }}" class="pp-thumb" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight line-clamp-2">{{ $p['name'] }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $p['product_code'] ?? '' }} · {{ $p['stock_quantity'] ?? 0 }} em estoque</p>
                            <div class="mt-1.5 flex items-center gap-2">
                                <span class="text-[11px] font-semibold text-slate-500">Preço</span>
                                <x-money-input :model="'selectedProducts.'.$idx.'.price_sale'" :value="$p['price_sale'] ?? 0" bare
                                    class="pp-input flex-1 min-w-0 py-1.5 px-2.5 text-sm font-bold" />
                            </div>
                        </div>
                        <button type="button" wire:click="toggleProduct({{ $p['id'] }})" class="self-start w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30" title="Tirar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500">
                        <i class="bi bi-hand-index text-2xl text-slate-300 block mb-2"></i>
                        Toque num produto para começar.
                    </div>
                @endforelse

                @if(!empty($selectedProducts))
                    <div class="pt-2 border-t border-slate-200/70 dark:border-slate-700">
                        <div class="pp-row"><span class="text-slate-500">Preço do anúncio</span><strong class="text-slate-900 dark:text-white">R$ {{ number_format($this->getTotalProductsPrice(), 2, ',', '.') }}</strong></div>
                        <div class="pp-row"><span class="text-slate-500">Quantidade no ML</span><strong class="text-slate-900 dark:text-white">{{ $this->getAvailableQuantity() }} un</strong></div>
                    </div>
                    <button type="button" wire:click="nextStep" class="pp-btn pp-btn-primary w-full mt-2">
                        Continuar <i class="bi bi-arrow-right"></i>
                    </button>
                @endif
            </div>
        </aside>
    </div>
    @endif

    {{-- ═══════════════ PASSO 2: CATÁLOGO ═══════════════ --}}
    @if($currentStep === 2)
    <div class="grid grid-cols-1 lg:grid-cols-[380px_minmax(0,1fr)] gap-4 items-start">
        <section class="pp-card">
            <div class="pp-card-h">
                <div class="pp-ico ml"><i class="bi bi-upc-scan"></i></div>
                <div class="flex-1"><h3>Catálogo do Mercado Livre</h3><p>Busca pelo código de barras do produto</p></div>
                <button type="button" wire:click="searchCatalog" class="pp-btn pp-btn-ghost" style="padding:.4rem .7rem" title="Buscar de novo">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
            <div class="pp-body">
                @if(empty($catalogResults))
                    <div class="py-6 text-center">
                        <i class="bi bi-search text-3xl text-slate-300"></i>
                        <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Nada no catálogo com esse código</p>
                        <p class="text-xs text-slate-500 mt-1">Sem problema: o anúncio é criado com os dados do seu produto.</p>
                        <button type="button" wire:click="nextStep" class="pp-btn pp-btn-primary mt-4">Continuar sem catálogo <i class="bi bi-arrow-right"></i></button>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2.5 max-h-[60vh] overflow-y-auto pr-1">
                        @foreach($catalogResults as $catalogProduct)
                            @php
                                $cProductId = $catalogProduct['id'] ?? $catalogProduct['product_id'] ?? '';
                                $domainId = $catalogProduct['domain_id'] ?? '';
                                $isSel = $catalogProductId === $cProductId;
                                $imgUrl = $this->getCatalogResultImage($catalogProduct);
                                $cPrice = $this->getCatalogResultPrice($catalogProduct);
                            @endphp
                            <button type="button" wire:key="cat-{{ $cProductId }}" wire:click="selectCatalogProduct('{{ $cProductId }}', '{{ $domainId }}')"
                                class="pp-opt flex-col text-left !gap-1.5 !p-2 {{ $isSel ? 'on' : '' }}">
                                <div class="w-full h-20 rounded-lg bg-white flex items-center justify-center overflow-hidden">
                                    @if($imgUrl)<img src="{{ $imgUrl }}" alt="" class="max-h-full object-contain">@else<i class="bi bi-image text-2xl text-slate-300"></i>@endif
                                </div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-100 line-clamp-2">{{ $catalogProduct['name'] ?? $catalogProduct['title'] ?? 'Sem título' }}</span>
                                @if($cPrice)<span class="text-sm font-black text-emerald-600">R$ {{ number_format($cPrice, 2, ',', '.') }}</span>@endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="pp-card relative">
            <div wire:loading.flex wire:target="selectCatalogProduct" class="absolute inset-0 z-10 hidden items-center justify-center bg-white/85 dark:bg-slate-900/85">
                <div class="pp-spinner"></div>
            </div>
            @if($catalogProductId && !empty($catalogProductData))
                <div class="pp-card-h flex-wrap">
                    <div class="pp-ico green"><i class="bi bi-patch-check-fill"></i></div>
                    <div class="min-w-0 flex-1"><h3 class="line-clamp-2">{{ $catalogProductName ?: 'Produto do catálogo' }}</h3><p>Título, fotos e ficha técnica vêm do catálogo</p></div>
                    <div class="flex w-full sm:w-auto gap-2">
                        <a href="https://www.mercadolivre.com.br/p/{{ $catalogProductId }}" target="_blank" rel="noopener" class="pp-btn pp-btn-ghost flex-1 sm:flex-none" style="padding:.4rem .7rem;font-size:.8rem"><i class="bi bi-box-arrow-up-right"></i> Ver no ML</a>
                        <button type="button" wire:click="clearCatalogProduct" class="pp-btn pp-btn-ghost text-red-600 flex-1 sm:flex-none" style="padding:.4rem .7rem;font-size:.8rem"><i class="bi bi-x-circle"></i> Não usar</button>
                    </div>
                </div>
                <div class="pp-body space-y-4">
                    <label class="pp-opt {{ $linkToCatalog ? 'on' : '' }}">
                        <input type="checkbox" wire:model.live="linkToCatalog" class="mt-1 w-4 h-4 accent-indigo-600">
                        <span>
                            <span class="block text-sm font-bold text-slate-900 dark:text-white">Ligar o anúncio a este produto do catálogo</span>
                            <span class="block text-xs text-slate-500 mt-0.5">O anúncio aparece na página do produto do ML e concorre pelo "Comprar". Desmarcado, só copia os dados.</span>
                        </span>
                    </label>

                    @if(!empty($catalogPictures))
                        <div>
                            <span class="pp-label">Fotos ({{ count($catalogPictures) }})</span>
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                @foreach($catalogPictures as $pic)
                                    @php $picUrl = $pic['secure_url'] ?? $pic['url'] ?? ''; @endphp
                                    @if($picUrl)<img src="{{ $picUrl }}" alt="" class="w-20 h-20 flex-shrink-0 rounded-xl object-contain bg-white border border-slate-200 dark:border-slate-700">@endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($catalogAttributes))
                        <div>
                            <span class="pp-label">Ficha técnica</span>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($catalogAttributes as $attr)
                                    @if(!empty($attr['value_name']))
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                                            <p class="text-[10px] font-bold uppercase text-slate-500">{{ $attr['name'] }}</p>
                                            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $attr['value_name'] }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-center justify-center gap-2 py-14 px-6 text-center">
                    <i class="bi bi-cursor text-3xl text-slate-300"></i>
                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ empty($catalogResults) ? 'O catálogo é opcional' : 'Toque num resultado para ver os detalhes' }}</p>
                    <p class="text-xs text-slate-500 max-w-sm">Com o catálogo, o ML já preenche título, fotos e ficha técnica. Sem ele, usamos o nome, a foto e a descrição do seu produto.</p>
                </div>
            @endif
        </section>
    </div>
    @endif

    {{-- ═══════════════ PASSO 3: ANÚNCIO ═══════════════ --}}
    @if($currentStep === 3)
    @php
        $basePrice = (float) ($publishPrice ?: 0) ?: $this->getTotalProductsPrice();
        $feeRate = $this->listingFeeRate();
        $feeAmount = $basePrice * $feeRate;
        $fixed = $this->fixedFee($basePrice);
        $costTotal = collect($selectedProducts)->sum(fn ($p) => (float) (\App\Models\Product::find($p['id'])->price ?? 0) * (int) ($p['quantity'] ?? 1));
        $net = $basePrice - $feeAmount - $fixed;
        $profit = $net - $costTotal;
        $suggested = $this->getSuggestedPrice();
        $families = $this->familyOptions;
        $requiredAttrs = $catalogProductId ? [] : $this->manualRequiredAttributes();
        $catName = collect($mlCategories)->firstWhere('id', $mlCategoryId)['name'] ?? null;
    @endphp
    <form id="publish-form" wire:submit.prevent="publishProduct">
        <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-4 items-start">

            {{-- Coluna 1: o que o cliente vê --}}
            <div class="space-y-4">
                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico"><i class="bi bi-type"></i></div>
                        <div class="flex-1"><h3>Título e descrição</h3><p>{{ $catalogProductName ? 'Título do catálogo do ML' : 'Nome do produto (máx. 60 letras)' }}</p></div>
                    </div>
                    <div class="pp-body space-y-3">
                        <div class="flex gap-3 items-center rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-3">
                            <img src="{{ $selectedPictures[0] ?? ($selectedProducts[0]['image_url'] ?? '') }}" class="pp-thumb" alt="">
                            <p class="text-sm font-bold text-slate-900 dark:text-white leading-snug">{{ mb_substr($this->getFinalTitle(), 0, 60) }}</p>
                        </div>
                        <div>
                            <span class="pp-label">Descrição</span>
                            <textarea wire:model="catalogDescription" rows="6" class="pp-input resize-y" placeholder="Conte o que é o produto, medidas, modo de uso..."></textarea>
                        </div>
                    </div>
                </section>

                @if(count($selectedProducts) > 1)
                    <section class="pp-card">
                        <div class="pp-card-h">
                            <div class="pp-ico pink"><i class="bi bi-boxes"></i></div>
                            <div class="flex-1"><h3>Kit com {{ count($selectedProducts) }} produtos</h3><p>Cada venda baixa uma unidade de cada</p></div>
                        </div>
                        <div class="pp-body space-y-2">
                            @foreach($selectedProducts as $p)
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p['image_url'] ?? '' }}" class="pp-thumb" style="width:2.5rem;height:2.5rem" alt="">
                                    <p class="flex-1 min-w-0 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $p['name'] }}</p>
                                    <span class="text-xs text-slate-500">{{ $p['stock_quantity'] }} un</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if(!empty($families))
                    <section class="pp-card">
                        <div class="pp-card-h">
                            <div class="pp-ico pink"><i class="bi bi-palette"></i></div>
                            <div class="flex-1"><h3>Publicar outras cores junto</h3><p>Cada cor vira um anúncio com o nome "{{ $this->familyTitle() }}"; o ML mostra todas na mesma página</p></div>
                        </div>
                        <div class="pp-body space-y-2">
                            @foreach($families as $f)
                                <label class="pp-opt {{ in_array($f['id'], array_map('intval', $extraColors)) ? 'on' : '' }} {{ $f['ready'] ? '' : 'opacity-60 !cursor-not-allowed' }}" wire:key="fam-{{ $f['id'] }}">
                                    <input type="checkbox" value="{{ $f['id'] }}" wire:model.live="extraColors" class="mt-1 w-4 h-4 accent-indigo-600" @disabled(!$f['ready'])>
                                    <img src="{{ $f['image_url'] }}" class="pp-thumb" style="width:2.5rem;height:2.5rem" alt="">
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $f['color'] }}</span>
                                        <span class="block text-xs text-slate-500">
                                            @if(!$f['ready']) {{ $f['reason'] }} @else {{ $f['stock'] }} em estoque @endif
                                            @if($f['published']) · <span class="text-amber-600">já tem anúncio</span> @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Coluna 2: preço e tipo --}}
            <div class="space-y-4">
                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico green"><i class="bi bi-currency-dollar"></i></div>
                        <div class="flex-1"><h3>Preço</h3><p>Quanto o cliente paga no Mercado Livre</p></div>
                    </div>
                    <div class="pp-body space-y-3">
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-500">R$</span>
                            <x-money-input bare :model="'publishPrice'" :value="$publishPrice ?? 0" class="pp-input text-xl font-black" style="padding-left:2.6rem" />
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="pp-chip bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">Loja: R$ {{ number_format($this->getTotalProductsPrice(), 2, ',', '.') }}</span>
                            @if($catalogPrice)<span class="pp-chip bg-yellow-100 text-yellow-800">Catálogo: R$ {{ number_format($catalogPrice, 2, ',', '.') }}</span>@endif
                            @if($suggested > 0)
                                <button type="button" wire:click="applySuggestedPrice" class="pp-chip bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200 hover:bg-indigo-200">
                                    <i class="bi bi-magic"></i> Usar R$ {{ number_format($suggested, 2, ',', '.') }} (cobre a taxa)
                                </button>
                            @endif
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-3">
                            <div class="pp-row"><span class="text-slate-500">Taxa do ML (~{{ round($feeRate * 100) }}%)</span><span class="font-bold text-red-600">- R$ {{ number_format($feeAmount, 2, ',', '.') }}</span></div>
                            @if($fixed > 0)
                                <div class="pp-row"><span class="text-slate-500">Custo fixo (abaixo de R$ 79)</span><span class="font-bold text-red-600">- R$ {{ number_format($fixed, 2, ',', '.') }}</span></div>
                            @endif
                            <div class="pp-row"><span class="text-slate-500">Você recebe</span><span class="font-black text-slate-900 dark:text-white">R$ {{ number_format($net, 2, ',', '.') }}</span></div>
                            @if($costTotal > 0)
                                <div class="pp-row border-t border-slate-200 dark:border-slate-700 pt-2 mt-2"><span class="text-slate-500">Lucro (menos o custo R$ {{ number_format($costTotal, 2, ',', '.') }})</span><span class="font-black {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">R$ {{ number_format($profit, 2, ',', '.') }}</span></div>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-2">Valores estimados; a taxa exata depende da categoria. Frete grátis é cobrado à parte pelo ML.</p>
                        </div>
                        <div class="pp-row rounded-2xl border border-slate-200 dark:border-slate-700 px-3 py-2.5">
                            <span class="text-slate-500"><i class="bi bi-stack mr-1"></i>Quantidade no anúncio</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $this->getAvailableQuantity() }} un <span class="text-xs font-medium text-slate-400">(segue o estoque)</span></span>
                        </div>
                    </div>
                </section>

                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico ml"><i class="bi bi-star-fill"></i></div>
                        <div class="flex-1"><h3>Tipo de anúncio</h3><p>Muda a taxa e a exposição</p></div>
                    </div>
                    <div class="pp-body grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(['gold_special' => ['Clássico', '~14%', 'Exposição alta, sem parcelamento sem juros'], 'gold_pro' => ['Premium', '~19%', 'Exposição máxima e parcelado sem juros para o cliente']] as $key => [$name, $fee, $desc])
                            <label class="pp-opt {{ $listingType === $key ? 'on' : '' }}">
                                <input type="radio" wire:model.live="listingType" value="{{ $key }}" class="mt-1 accent-indigo-600">
                                <span>
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $name }} <span class="text-xs font-semibold text-slate-500">· taxa {{ $fee }}</span></span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $desc }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Coluna 3: categoria, envio e condição --}}
            <div class="space-y-4 lg:space-y-0 lg:col-span-2 lg:grid lg:grid-cols-2 lg:gap-4 2xl:col-span-1 2xl:block 2xl:space-y-4">
                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico teal"><i class="bi bi-diagram-3"></i></div>
                        <div class="flex-1"><h3>Categoria no ML</h3><p>{{ $catName ? 'Sugerida pelo ML: '.$catName : 'Obrigatória' }}</p></div>
                        @if($mlCategoryId)<span class="pp-chip bg-emerald-100 text-emerald-700"><i class="bi bi-check-lg"></i> ok</span>@endif
                    </div>
                    <div class="pp-body space-y-2">
                        <select wire:model.live="mlCategoryId" class="pp-input">
                            <option value="">Escolha a categoria</option>
                            @if($mlCategoryId && !$catName)
                                <option value="{{ $mlCategoryId }}">{{ $mlCategoryId }} (do catálogo)</option>
                            @endif
                            @foreach($mlCategories as $cat)
                                <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="relative">
                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" wire:model.live.debounce.500ms="categorySearch" placeholder="Não achou? Busque outra categoria" class="pp-input" style="padding-left:2.2rem">
                        </div>

                        @if(!empty($requiredAttrs))
                            <div class="pt-2 space-y-2">
                                <span class="pp-label">Ficha obrigatória da categoria</span>
                                @foreach($requiredAttrs as $attr)
                                    <div wire:key="req-attr-{{ $attr['id'] }}">
                                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $attr['name'] ?? $attr['id'] }} *</label>
                                        @if(!empty($attr['values']) && is_array($attr['values']))
                                            <select wire:model="selectedAttributes.{{ $attr['id'] }}" class="pp-input mt-1">
                                                <option value="">Escolha</option>
                                                @foreach($attr['values'] as $val)
                                                    <option value="{{ $val['name'] ?? $val['id'] }}">{{ $val['name'] ?? $val['id'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" wire:model="selectedAttributes.{{ $attr['id'] }}" placeholder="{{ $attr['hint'] ?? '' }}" class="pp-input mt-1">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico teal"><i class="bi bi-truck"></i></div>
                        <div class="flex-1"><h3>Envio</h3><p>Pelo Mercado Envios</p></div>
                    </div>
                    <div class="pp-body space-y-2">
                        <label class="pp-opt {{ $freeShipping ? 'on' : '' }}">
                            <input type="checkbox" wire:model.live="freeShipping" class="mt-1 w-4 h-4 accent-indigo-600">
                            <span>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">Frete grátis</span>
                                <span class="block text-xs text-slate-500 mt-0.5">Você paga o frete e o anúncio ganha o selo. Acima de R$ 79 o ML costuma exigir.</span>
                            </span>
                        </label>
                        <label class="pp-opt {{ $localPickup ? 'on' : '' }}">
                            <input type="checkbox" wire:model.live="localPickup" class="mt-1 w-4 h-4 accent-indigo-600">
                            <span>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">Retirada no local</span>
                                <span class="block text-xs text-slate-500 mt-0.5">O comprador pode buscar no seu endereço cadastrado no ML.</span>
                            </span>
                        </label>
                    </div>
                </section>

                <section class="pp-card">
                    <div class="pp-card-h">
                        <div class="pp-ico"><i class="bi bi-shield-check"></i></div>
                        <div class="flex-1"><h3>Condição e garantia</h3></div>
                    </div>
                    <div class="pp-body space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['new' => 'Novo', 'used' => 'Usado'] as $key => $name)
                                <label class="pp-opt items-center {{ $productCondition === $key ? 'on' : '' }}">
                                    <input type="radio" wire:model.live="productCondition" value="{{ $key }}" class="accent-indigo-600">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div>
                            <span class="pp-label">Garantia (opcional)</span>
                            <input type="text" wire:model="warranty" placeholder="Ex.: 90 dias de garantia do fabricante" class="pp-input">
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="pp-card pp-actions-bar">
            <button type="button" wire:click="previousStep" class="pp-btn pp-btn-ghost"><i class="bi bi-arrow-left"></i> Voltar</button>
            <button type="submit" class="pp-btn pp-btn-go" wire:loading.attr="disabled" wire:target="publishProduct">
                <i class="bi bi-rocket-takeoff-fill"></i>
                {{ count($extraColors) ? 'Publicar '.(1 + count($extraColors)).' anúncios' : 'Publicar no Mercado Livre' }}
            </button>
        </div>
    </form>
    @endif
</div>
