<div class="w-full app-viewport-fit products-index-page promotions-page mobile-393-base relative"
     x-data="{ fullHd: false, ultra: false,
        init() {
            const a = window.matchMedia('(min-width: 1920px)'), b = window.matchMedia('(min-width: 2498px)');
            const sync = () => { this.fullHd = a.matches; this.ultra = b.matches; };
            sync(); a.addEventListener('change', sync); b.addEventListener('change', sync);
        } }">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-header.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-mobile.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-iphone15.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ipad-portrait.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ipad-landscape.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-notebook.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/products-index-ultrawide.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-card-standard.css') }}?v=20260806">
    @endpush
    @include('livewire.promotions.partials.styles')

    @php
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $tabs = [
            'ativas'     => ['Ativas', 'bi-fire', $stats['ativas']],
            'sugestoes'  => ['Sugestões', 'bi-lightbulb', $stats['sugestoes']],
            'agendadas'  => ['Agendadas', 'bi-calendar-event', $stats['agendadas']],
            'encerradas' => ['Encerradas', 'bi-archive', null],
        ];
        $marginLabel = rtrim(rtrim(number_format((float) $settings->min_margin_percent, 2, ',', ''), '0'), ',');
    @endphp

    {{-- ============ HEADER (mesmo padrão de Produtos) ============ --}}
    <div class="products-index-header relative overflow-hidden bg-gradient-to-r from-white/80 via-rose-50/90 to-orange-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-rose-400/20 via-pink-400/20 to-orange-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-orange-400/10 via-pink-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

        <div class="products-index-header-inner relative px-8 py-6">
            <div class="products-index-header-layout flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="products-index-header-left flex items-center gap-6">
                    <div class="products-index-header-icon relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 rounded-2xl shadow-xl shadow-rose-500/25">
                        <i class="bi bi-percent text-white text-3xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                    </div>
                    <div class="products-index-header-content space-y-2">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <a href="{{ route('dashboard') }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition-colors"><i class="fas fa-home mr-1"></i>Dashboard</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium"><i class="bi bi-percent mr-1"></i>Promoções</span>
                        </div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-rose-700 to-orange-600 dark:from-rose-300 dark:via-pink-300 dark:to-orange-300 bg-clip-text text-transparent">Promoções</h1>
                        <div class="products-index-header-stats flex items-center gap-3 text-lg flex-wrap">
                            @foreach([
                                [$stats['ativas'], 'ativas', 'bi-fire', 'from-rose-400 to-rose-600'],
                                [$stats['desconto'] . '%', 'desconto médio', 'bi-graph-down-arrow', 'from-emerald-400 to-emerald-600'],
                                [$money($stats['vendas']['revenue']), 'vendido', 'bi-bag-check', 'from-sky-400 to-blue-600'],
                            ] as [$value, $label, $icon, $grad])
                                <div class="flex items-center gap-2 px-4 py-2 bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm rounded-xl border border-white/20 dark:border-slate-600/50">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br {{ $grad }} rounded-lg"><i class="bi {{ $icon }} text-white text-sm"></i></div>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $value }}</span>
                                    <span class="text-slate-600 dark:text-slate-400">{{ $label }}</span>
                                </div>
                            @endforeach
                            @if($stats['vencendo'])
                                <button type="button" wire:click="$set('sort', 'validade')" class="flex items-center gap-2 px-4 py-2 bg-amber-100/80 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-700/50" title="Ver as que vencem primeiro">
                                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-600 rounded-lg"><i class="bi bi-hourglass-split text-white text-sm"></i></div>
                                    <span class="font-semibold text-amber-800 dark:text-amber-200">{{ $stats['vencendo'] }}</span>
                                    <span class="text-amber-700 dark:text-amber-300">vencem em 3 dias</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="products-index-header-slot flex-1 flex items-center justify-end gap-4">
                    <div class="w-full products-index-controls">
                        {{-- Linha 1: busca + nova promoção --}}
                        <div class="prod-header-row-1">
                            <div class="prod-header-search relative group">
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou código..."
                                       class="w-full pl-11 pr-10 py-2.5 bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-rose-500/40 focus:border-rose-400 transition-all shadow-sm text-sm font-medium">
                                <div class="absolute left-3.5 top-1/2 -translate-y-1/2"><i class="bi bi-search text-slate-400 group-focus-within:text-rose-500"></i></div>
                                @if($search)
                                    <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 text-slate-600 hover:text-white rounded-lg"><i class="bi bi-x text-sm"></i></button>
                                @endif
                            </div>
                            <a href="{{ route('promotions.create') }}" class="prod-header-btn-create promo-btn-create group">
                                <i class="bi bi-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                                <span>Nova promoção</span>
                            </a>
                        </div>

                        {{-- Linha 2: abas + ordem + ações --}}
                        <div class="prod-header-row-2">
                            <div class="prod-header-row-2-left">
                                <div class="sale-filter-pills promo-tabs">
                                    @foreach($tabs as $key => [$label, $icon, $count])
                                        <button type="button" wire:click="setTab('{{ $key }}')" class="sale-filter-pill promo-pill {{ $tab === $key ? 'active' : '' }}">
                                            <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                                            @if($count)<span class="promo-pill-count">{{ $count }}</span>@endif
                                        </button>
                                    @endforeach
                                </div>
                                @if(in_array($tab, ['ativas', 'agendadas']))
                                    <div class="sale-filter-pills sale-sort-pills hidden md:flex">
                                        <span class="sale-filter-pill-label"><i class="bi bi-arrow-down-up"></i></span>
                                        @foreach(['desconto' => 'Desconto', 'validade' => 'Validade', 'recentes' => 'Recentes', 'nome' => 'A-Z'] as $key => $label)
                                            <button type="button" wire:click="$set('sort', '{{ $key }}')" class="sale-filter-pill {{ $sort === $key ? 'active' : '' }}"><span>{{ $label }}</span></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="prod-header-row-2-right">
                                @if($items->hasPages())
                                    <div class="sale-pagination-compact">
                                        @if($items->currentPage() > 1)
                                            <button type="button" wire:click.prevent="previousPage" class="sale-pagination-btn"><i class="bi bi-chevron-left"></i></button>
                                        @endif
                                        <span class="sale-pagination-indicator">{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
                                        @if($items->hasMorePages())
                                            <button type="button" wire:click.prevent="nextPage" class="sale-pagination-btn"><i class="bi bi-chevron-right"></i></button>
                                        @endif
                                    </div>
                                @endif
                                @if($items->count() && $tab !== 'encerradas')
                                    <button type="button" wire:click="selectAllOnPage" class="sale-action-btn" title="Selecionar todos desta página">
                                        <i class="bi bi-check2-square"></i><span>Selecionar</span>
                                    </button>
                                @endif
                                <button type="button" wire:click="openSettings" class="sale-action-btn sale-action-filter" title="Lucro mínimo, validade e mensagem">
                                    <i class="bi bi-gear"></i><span>Configurar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['semTabela'] > 0 && $tab !== 'encerradas')
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2 justify-between p-3 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
            <span><i class="bi bi-info-circle mr-1"></i> {{ $stats['semTabela'] }} produto(s) com estoque sem preço de tabela. Eles também podem entrar em promoção: o "de" vira o preço de revenda.</span>
            <button type="button" wire:click="backfillOriginalPrices" wire:loading.attr="disabled" wire:target="backfillOriginalPrices"
                    class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs whitespace-nowrap">
                <span wire:loading.remove wire:target="backfillOriginalPrices"><i class="bi bi-file-earmark-pdf"></i> Buscar tabela nos PDFs</span>
                <span wire:loading wire:target="backfillOriginalPrices">Lendo PDFs…</span>
            </button>
        </div>
    @endif

    {{-- Barra de seleção --}}
    @if(count($selected))
        <div class="promo-bulk-bar sticky top-2 z-30 mb-4 flex flex-wrap items-center gap-2 p-2.5 rounded-2xl bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-rose-200 dark:border-rose-800/60 shadow-lg text-sm">
            <span class="px-2 font-bold text-rose-600 dark:text-rose-300"><i class="bi bi-check2-circle"></i> {{ count($selected) }} selecionado(s)</span>
            @if($tab === 'sugestoes')
                <button type="button" wire:click="bulkStart" class="px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white font-semibold"><i class="bi bi-fire"></i> Pôr em promoção</button>
            @else
                <button type="button" wire:click="openShare" class="px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold"><i class="bi bi-whatsapp"></i> Ofertas da semana</button>
                <button type="button" wire:click="openBulk('desconto')" class="px-3 py-1.5 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white font-semibold"><i class="bi bi-percent"></i> Desconto</button>
                <button type="button" wire:click="openBulk('validade')" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold"><i class="bi bi-calendar"></i> Validade</button>
                <button type="button" wire:click="bulkEnd" wire:confirm="Retirar as promoções selecionadas?" class="px-3 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-700 text-white font-semibold"><i class="bi bi-x-circle"></i> Retirar</button>
            @endif
            <button type="button" wire:click="$set('selected', [])" class="ml-auto px-2 py-1.5 text-slate-500 hover:text-slate-700">Limpar</button>
        </div>
    @endif

    {{-- ============ LISTA ============ --}}
    @if($items->count() === 0)
        <div class="empty-state flex flex-col items-center justify-center py-16 bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-2xl border-2 border-dashed border-neutral-300 dark:border-neutral-600 text-center px-4">
            <div class="w-24 h-24 mb-5 rounded-3xl bg-gradient-to-br from-rose-500/20 to-orange-500/20 flex items-center justify-center">
                <i class="bi bi-tags text-4xl text-rose-500"></i>
            </div>
            <h3 class="text-xl font-bold text-neutral-800 dark:text-neutral-100 mb-2">
                @switch($tab)
                    @case('sugestoes') Nenhuma sugestão agora @break
                    @case('agendadas') Nenhuma promoção agendada @break
                    @case('encerradas') Nenhuma promoção encerrada ainda @break
                    @default Nenhuma promoção ativa
                @endswitch
            </h3>
            <p class="text-neutral-600 dark:text-neutral-400 mb-6 max-w-md">
                @if($tab === 'sugestoes') Aparecem aqui os produtos com estoque e pelo menos {{ (int) $settings->suggest_min_discount }}% de diferença entre a tabela e a revenda.
                @else Escolha produtos e defina o desconto de cada um. @endif
            </p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('promotions.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-orange-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl"><i class="bi bi-plus-circle mr-2"></i> Nova promoção</a>
                @if($tab === 'ativas' && $stats['sugestoes'])
                    <button type="button" wire:click="setTab('sugestoes')" class="inline-flex items-center px-6 py-3 bg-white dark:bg-slate-700 text-rose-600 dark:text-rose-300 font-semibold rounded-xl border border-rose-200 dark:border-rose-800"><i class="bi bi-lightbulb mr-2"></i> Ver {{ $stats['sugestoes'] }} sugestão(ões)</button>
                @endif
            </div>
        </div>
    @else
        <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 ultrawind:grid-cols-8 gap-6"
             x-bind:data-ultrawind="ultra ? 'true' : 'false'" x-bind:data-full-hd="fullHd ? 'true' : 'false'">
            @foreach($items as $item)
                @php
                    if ($tab === 'sugestoes') {
                        $product = $item;
                        $original = (float) $item->price_original;
                        $promoValue = (float) $item->price_sale;
                        $discount = $original > 0 ? (int) round((1 - $promoValue / $original) * 100) : 0;
                    } else {
                        $product = $item->product;
                        $original = (float) $item->original_price;
                        $promoValue = (float) $item->promo_price;
                        $discount = $item->discount_percent;
                    }
                    $isSelected = in_array($item->id, $selected);
                    $isKit = ($product?->tipo ?? '') === 'kit';
                @endphp
                <div wire:key="promo-{{ $tab }}-{{ $item->id }}" class="product-card-modern promo-card {{ $isSelected ? 'promo-selected' : '' }} {{ $tab === 'encerradas' ? 'promo-ended' : '' }}">
                    {{-- Ações --}}
                    <div class="btn-action-group">
                        @if($tab !== 'encerradas')
                            <button type="button" wire:click="toggleSelect({{ $item->id }})" class="promo-check {{ $isSelected ? 'on' : '' }}" title="Selecionar">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        @endif
                        @if($tab === 'sugestoes')
                            <a href="{{ route('promotions.create', ['produto' => $item->id]) }}" class="btn btn-primary" title="Ajustar desconto"><i class="bi bi-sliders"></i></a>
                            <button type="button" wire:click="quickStart({{ $item->id }})" class="btn btn-danger" title="Pôr em promoção agora"><i class="bi bi-fire"></i></button>
                        @elseif($tab === 'encerradas')
                            <button type="button" wire:click="restart({{ $item->id }})" class="btn btn-primary" title="Reativar"><i class="bi bi-arrow-repeat"></i></button>
                        @else
                            <button type="button" wire:click="openEdit({{ $item->id }})" class="btn btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" wire:click="openShare({{ $item->id }})" class="btn btn-success" title="Enviar no WhatsApp"><i class="bi bi-whatsapp"></i></button>
                            <button type="button" wire:click="endPromotion({{ $item->id }})" wire:confirm="Retirar a promoção? O produto volta para o preço de revenda." class="btn btn-danger" title="Retirar"><i class="bi bi-x-circle"></i></button>
                        @endif
                    </div>

                    <div class="product-img-area">
                        <img src="{{ $product?->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}" class="product-img" alt="{{ $product?->name }}">

                        <span class="badge-product-code" title="Código do Produto"><i class="bi bi-upc-scan"></i> {{ $product?->product_code }}</span>

                        <div class="promo-card-tags">
                            <span class="promo-tag bg-gradient-to-r from-rose-500 to-orange-500"><i class="bi bi-fire"></i> -{{ $discount }}%</span>
                            @if($isKit)
                                <span class="promo-tag bg-gradient-to-r from-blue-500 to-blue-600"><i class="bi bi-boxes"></i> KIT</span>
                            @elseif($product?->variation_value)
                                <span class="promo-tag bg-gradient-to-r from-violet-500 to-purple-600"><i class="bi bi-diagram-3"></i> {{ $product->variation_value }}</span>
                            @endif
                        </div>

                        @unless($isKit)
                            <span class="badge-quantity" title="Estoque"><i class="bi bi-stack"></i> {{ (int) $product?->stock_quantity }}</span>
                        @endunless
                        <div class="category-icon-wrapper">
                            <i class="{{ $product?->category->icone ?? 'bi bi-box' }} category-icon"></i>
                        </div>
                    </div>

                    <div class="card-body">
                        <a href="{{ $product ? route('products.show', $product->product_code) : '#' }}" class="product-title" title="{{ $product?->name }}">{{ ucwords($product?->name ?? 'Produto removido') }}</a>

                        <div class="promo-card-prices">
                            <s>{{ $money($original) }}</s>
                            <strong>{{ $money($promoValue) }}</strong>
                        </div>
                        <div class="promo-card-meta">
                            @if($tab === 'sugestoes')
                                <i class="bi bi-lightbulb"></i> tabela → revenda
                            @elseif($tab === 'encerradas')
                                <i class="bi bi-archive"></i> {{ \App\Models\Promotion::ENDED_REASONS[$item->ended_reason] ?? 'Encerrada' }} {{ $item->ended_at?->format('d/m') }}
                            @elseif($item->starts_at && $item->starts_at->isFuture())
                                <i class="bi bi-calendar-event"></i> começa {{ $item->starts_at->format('d/m') }}
                            @else
                                <span class="{{ $item->ends_at && $item->ends_at->lte(now()->addDays(3)) ? 'text-amber-600 font-bold' : '' }}">
                                    <i class="bi bi-hourglass-split"></i> {{ $item->ends_at ? 'até ' . $item->ends_at->format('d/m') : 'até acabar o estoque' }}
                                </span>
                                @if($last = $item->sends->first())
                                    <span class="text-green-600 ml-1" title="Último envio"><i class="bi bi-whatsapp"></i> {{ $last->created_at->format('d/m') }}</span>
                                @endif
                            @endif
                        </div>

                        <div class="price-area">
                            <span class="badge-price" title="Custo (a pagar)"><i class="bi bi-tag"></i> {{ number_format((float) $product?->price, 2, ',', '.') }}</span>
                            <span class="badge-price-sale" title="Revenda"><i class="bi bi-currency-dollar"></i> {{ number_format((float) $product?->price_sale, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div class="mt-8 products-pagination-area">{{ $items->links() }}</div>
        @endif
    @endif

    {{-- Modal: editar promoção --}}
    @if($showEditModal)
        @php $preview = $this->editPreview; $editingProduct = $this->editingProduct; @endphp
        <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4" wire:keydown.escape="$set('showEditModal', false)">
            <div class="w-full sm:max-w-md max-h-[95vh] overflow-y-auto bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center gap-3 px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-rose-500/10 via-pink-500/10 to-orange-500/10 backdrop-blur-xl">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-500 flex items-center justify-center shadow-lg"><i class="bi bi-pencil-square text-white"></i></div>
                    <h3 class="flex-1 font-bold text-lg text-slate-800 dark:text-slate-100">Editar promoção</h3>
                    <button type="button" wire:click="$set('showEditModal', false)" class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10"><i class="bi bi-x-lg"></i></button>
                </div>
                @if($editingProduct)
                    <div class="p-5 space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $editingProduct->image ? asset('storage/products/' . $editingProduct->image) : asset('storage/products/product-placeholder.png') }}" class="w-14 h-14 object-cover rounded-xl border border-slate-200 dark:border-slate-700" alt="">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ ucwords($editingProduct->name) }}</div>
                                <div class="text-xs text-slate-500">#{{ $editingProduct->product_code }} · {{ (int) $editingProduct->stock_quantity }} un.</div>
                            </div>
                        </div>

                        <div class="promo-row">
                            <div class="grid grid-cols-2 gap-2">
                                <label class="promo-field"><span>De</span><input type="text" inputmode="decimal" wire:model.blur="originalPrice" class="line-through text-slate-500"></label>
                                <label class="promo-field promo-field-main"><span>Por</span><input type="text" inputmode="decimal" wire:model.blur="promoPrice" class="text-rose-600 dark:text-rose-400 font-bold"></label>
                            </div>
                            <div class="mt-3 flex items-center gap-2" wire:key="edit-slider-{{ $preview['discount'] }}-{{ $preview['max'] }}" x-data="{ p: {{ $preview['discount'] }} }">
                                <button type="button" wire:click="nudgeDiscount(-1)" class="promo-step" title="Menos desconto"><i class="bi bi-dash-lg"></i></button>
                                <div class="flex-1 min-w-0">
                                    <input type="range" min="1" max="{{ max(1, $preview['max']) }}" x-model.number="p" @change="$wire.setDiscountPercent(p)" class="promo-range w-full"
                                           :style="'--fill:' + (({{ max(1, $preview['max']) }} > 1 ? (p - 1) / ({{ max(1, $preview['max']) }} - 1) : 1) * 100) + '%'">
                                    <div class="flex justify-between text-[9px] text-slate-400 -mt-0.5"><span>1%</span><span>máx. {{ $preview['max'] }}%</span></div>
                                </div>
                                <button type="button" wire:click="nudgeDiscount(1)" class="promo-step" title="Mais desconto" @disabled($preview['discount'] >= $preview['max'])><i class="bi bi-plus-lg"></i></button>
                                <span class="promo-off" x-text="p + '%'">{{ $preview['discount'] }}%</span>
                            </div>
                            <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span title="Quanto o produto custou (a pagar)">Custo <b class="text-slate-700 dark:text-slate-200">{{ $money($preview['cost']) }}</b></span>
                                <span title="Quanto sobra para você" class="{{ $preview['profit'] < 0 ? 'text-red-600' : 'text-emerald-600 dark:text-emerald-400' }}">Lucro <b>{{ $money($preview['profit']) }}</b></span>
                                <button type="button" wire:click="useMinPrice" title="Menor preço permitido. Toque para usar." class="text-amber-700 dark:text-amber-300 hover:underline">Mín. <b>{{ $money($preview['min']) }}</b></button>
                            </div>
                            @if($preview['belowMin'])
                                <p class="mt-1.5 text-xs font-semibold text-red-600">Abaixo do mínimo (custo + {{ $marginLabel }}%).</p>
                            @endif
                            @error('promoPrice') <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <label class="promo-field"><span>Começa</span><input type="date" wire:model="startsAt"></label>
                            <label class="promo-field"><span>Termina</span><input type="date" wire:model="endsAt"></label>
                        </div>
                        <p class="text-[11px] text-slate-400 -mt-2">Começo vazio = agora. Fim vazio = até acabar o estoque.</p>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Mensagem própria <span class="font-normal text-slate-400">(opcional)</span></label>
                            <textarea wire:model="message" rows="3" placeholder="Vazio = usa o modelo das configurações"
                                      class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200"></textarea>
                        </div>
                    </div>
                    <div class="sticky bottom-0 flex gap-2 px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <button type="button" wire:click="$set('showEditModal', false)" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
                        <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 text-white font-bold shadow-lg">Salvar</button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <style>
        .promotions-page .promo-btn-create { background: linear-gradient(135deg, #f43f5e, #ec4899, #f97316) !important; }
        .promotions-page .sale-filter-pill.promo-pill.active { background: linear-gradient(135deg, #f43f5e, #f97316) !important; color: #fff !important; box-shadow: 0 2px 8px rgba(244,63,94,.4) !important; }
        .promo-pill-count { margin-left: .25rem; padding: 0 .4rem; border-radius: 999px; font-size: .68em; font-weight: 800; background: rgba(148,163,184,.25); }
        .sale-filter-pill.active .promo-pill-count { background: rgba(255,255,255,.3); }
        .promotions-page .promo-tabs { overflow-x: auto; max-width: 100%; }
        .promotions-page .promo-tabs::-webkit-scrollbar { display: none; }
        /* Celular: as abas ficam visíveis (em Produtos elas vão para o filtro) e as ações viram só ícone */
        @media (max-width: 767.98px) {
            .promotions-page .prod-header-row-2-left { display: flex !important; width: 100%; min-width: 0; }
            .promotions-page .promo-tabs { flex-wrap: nowrap; width: 100%; }
            .promotions-page .promo-tabs .promo-pill { flex-shrink: 0; }
            .promotions-page .promo-tabs .promo-pill span { display: inline !important; }
            .promotions-page .promo-tabs .promo-pill:not(.active) { color: rgb(71 85 105) !important; background: rgba(255,255,255,.75) !important; }
            .dark .promotions-page .promo-tabs .promo-pill:not(.active) { color: rgb(203 213 225) !important; background: rgba(30,41,59,.7) !important; }
            .promotions-page .prod-header-row-2-right { justify-content: flex-end; gap: .4rem; }
            .promotions-page .prod-header-row-2-right .sale-action-btn span { display: none; }
            .promotions-page .promo-btn-create span { font-size: .78rem; }
        }
        /* iPad deitado (toque): espaço para a barra de baixo */
        @media (min-width: 1024px) and (max-width: 1366px) and (pointer: coarse) {
            .promotions-page { padding-bottom: 6rem; }
        }

        .promo-card .product-title { display: block; }
        .promo-card .promo-card-tags { position: absolute; left: .5em; bottom: .5em; z-index: 10; display: flex; flex-wrap: wrap; gap: .25em; max-width: 70%; }
        .promo-card .promo-card-prices { display: flex; align-items: baseline; justify-content: center; gap: .4em; margin-top: .15em; line-height: 1.1; }
        .promo-card .promo-card-prices s { font-size: .78em; color: rgb(148 163 184); }
        .promo-card .promo-card-prices strong { font-size: 1.12em; font-weight: 900; color: #e11d48; }
        .dark .promo-card .promo-card-prices strong { color: #fb7185; }
        .promo-card .promo-card-meta { text-align: center; font-size: .68em; color: rgb(100 116 139); margin-bottom: 1.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .promo-card.promo-selected { border-color: #f43f5e !important; box-shadow: 0 0 0 3px rgba(244,63,94,.25), 0 8px 28px rgba(244,63,94,.25) !important; }
        .promo-card.promo-ended .product-img { filter: grayscale(.7); opacity: .8; }
        .promo-check { width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
            background: #fff; border: 2px solid rgb(203 213 225); color: transparent; box-shadow: 0 4px 12px rgba(0,0,0,.12); transition: all .15s; }
        .promo-check:hover { border-color: #f43f5e; color: rgba(244,63,94,.5); }
        .promo-check.on { background: linear-gradient(135deg, #f43f5e, #f97316); border-color: transparent; color: #fff; }
        .dark .promo-check { background: rgb(30 41 59); border-color: rgb(71 85 105); }
    </style>

    <x-toast-notifications />

    {{-- Modal: ações em massa --}}
    @if($showBulkModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-5 space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">
                    {{ $bulkAction === 'desconto' ? 'Aplicar desconto' : 'Mudar validade' }} ({{ count($selected) }})
                </h3>
                @if($bulkAction === 'desconto')
                    <div>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Desconto sobre o preço original (%)</label>
                        <input type="text" inputmode="decimal" wire:model="bulkPercent" placeholder="Ex.: 30"
                               class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                        @error('bulkPercent') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Produtos que passariam do lucro mínimo ficam no mínimo permitido.</p>
                    </div>
                @else
                    <div>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Termina em</label>
                        <input type="date" wire:model="bulkEndsAt" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                        <p class="text-[11px] text-slate-400 mt-1">Vazio = até zerar o estoque.</p>
                    </div>
                @endif
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('showBulkModal', false)" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
                    <button type="button" wire:click="applyBulk" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold">Aplicar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: WhatsApp --}}
    @if($showShareModal)
        @php $share = $this->shareData; @endphp
        <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4">
            <div class="w-full sm:max-w-3xl max-h-[95vh] overflow-y-auto bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl"
                 x-data="promoShare(@js($share['cards']))" x-init="render()">
                <div class="sticky top-0 z-10 flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">
                        <i class="bi bi-whatsapp text-green-500"></i>
                        {{ count($shareIds) > 1 ? 'Ofertas da semana (' . count($shareIds) . ' produtos)' : 'Enviar promoção' }}
                    </h3>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="p-5 grid md:grid-cols-2 gap-5">
                    <div class="space-y-3">
                        <div class="flex gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 text-xs font-semibold">
                            <button type="button" @click="format = 'square'; render()" :class="format === 'square' ? 'bg-white dark:bg-slate-700 shadow' : ''" class="flex-1 py-1.5 rounded-lg text-slate-700 dark:text-slate-200">Post (quadrado)</button>
                            <button type="button" @click="format = 'story'; render()" :class="format === 'story' ? 'bg-white dark:bg-slate-700 shadow' : ''" class="flex-1 py-1.5 rounded-lg text-slate-700 dark:text-slate-200">Status (vertical)</button>
                        </div>
                        <div class="rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 flex justify-center">
                            <canvas x-ref="canvas" class="max-h-[420px] w-auto max-w-full"></canvas>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                            <button type="button" @click="download()" class="py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-download"></i> Baixar imagem</button>
                            <button type="button" @click="copyImage()" class="py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-images"></i> Copiar imagem</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Para</label>
                            <select wire:change="setShareClient($event.target.value)" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200">
                                <option value="">Qualquer contato / status / grupo</option>
                                @foreach($share['allClients'] as $c)
                                    <option value="{{ $c->id }}" @selected($shareClientId === $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Mensagem</label>
                            <textarea wire:model="shareText" x-ref="text" rows="9" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200 font-mono"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-semibold">
                            <button type="button" @click="shareAll()" class="py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white"><i class="bi bi-share"></i> Compartilhar</button>
                            <button type="button" @click="openWhatsapp({{ $shareClientId ?? 'null' }})" class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white"><i class="bi bi-whatsapp"></i> Abrir WhatsApp</button>
                            <button type="button" @click="copyText()" class="py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-clipboard"></i> Copiar texto</button>
                        </div>
                        <p class="text-[11px] text-slate-400">"Compartilhar" manda foto e texto juntos pelo celular. No computador, copie a imagem e cole na conversa depois de abrir o WhatsApp.</p>

                        @if($share['clients']->count())
                            <div class="pt-2">
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Sugestões de clientes</div>
                                <div class="space-y-1 max-h-56 overflow-y-auto">
                                    @foreach($share['clients'] as $row)
                                        <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $row['client']->name }}</div>
                                                <div class="text-[11px] text-slate-500">
                                                    {{ $row['reason'] }}
                                                    @if($row['last_send'])
                                                        · <span class="text-green-600">enviado em {{ $row['last_send']->created_at->format('d/m') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button type="button" @click="openWhatsapp({{ $row['client']->id }})"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $row['last_send'] ? 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-500 text-white' }}">
                                                <i class="bi bi-whatsapp"></i> {{ $row['last_send'] ? 'Reenviar' : 'Enviar' }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: configurações --}}
    @if($showSettingsModal)
        @include('livewire.promotions.partials.settings-modal')
    @endif
</div>

@script
<script>
    /**
     * Imagem da promoção (post quadrado ou status vertical) desenhada em canvas,
     * e envio por WhatsApp. Cada envio é registrado no servidor antes de abrir.
     */
    Alpine.data('promoShare', (cards) => ({
        cards,
        format: 'square',
        images: {},

        loadImage(src) {
            if (!src) return Promise.resolve(null);
            if (this.images[src]) return Promise.resolve(this.images[src]);
            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => { this.images[src] = img; resolve(img); };
                img.onerror = () => resolve(null);
                img.src = src;
            });
        },

        wrap(ctx, text, maxWidth, maxLines) {
            const words = String(text).split(/\s+/);
            const lines = [];
            let line = '';
            for (const w of words) {
                const test = line ? line + ' ' + w : w;
                if (ctx.measureText(test).width > maxWidth && line) {
                    lines.push(line);
                    line = w;
                    if (lines.length === maxLines) break;
                } else {
                    line = test;
                }
            }
            if (lines.length < maxLines && line) lines.push(line);
            if (lines.length === maxLines && words.join(' ').length > lines.join(' ').length) {
                lines[maxLines - 1] = lines[maxLines - 1].replace(/\s*\S*$/, '') + '…';
            }
            return lines;
        },

        async render() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.cards.length) return;
            const W = 1080, H = this.format === 'story' ? 1920 : 1080;
            canvas.width = W; canvas.height = H;
            const ctx = canvas.getContext('2d');

            const bg = ctx.createLinearGradient(0, 0, W, H);
            bg.addColorStop(0, '#fff1f2'); bg.addColorStop(1, '#fce7f3');
            ctx.fillStyle = bg; ctx.fillRect(0, 0, W, H);

            // Faixa do topo
            ctx.fillStyle = '#e11d48';
            ctx.fillRect(0, 0, W, this.format === 'story' ? 200 : 130);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold ' + (this.format === 'story' ? 92 : 70) + 'px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(this.cards.length > 1 ? 'OFERTAS DA SEMANA' : 'PROMOÇÃO', W / 2, this.format === 'story' ? 135 : 92);

            if (this.cards.length === 1) {
                await this.drawSingle(ctx, this.cards[0], W, H);
            } else {
                await this.drawList(ctx, W, H);
            }
        },

        async drawSingle(ctx, c, W, H) {
            const story = this.format === 'story';
            const imgBox = story ? { x: 140, y: 280, s: 800 } : { x: 290, y: 170, s: 500 };
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.roundRect(imgBox.x - 20, imgBox.y - 20, imgBox.s + 40, imgBox.s + 40, 40);
            ctx.fill();
            const img = await this.loadImage(c.image);
            if (img) {
                const r = Math.min(imgBox.s / img.width, imgBox.s / img.height);
                const w = img.width * r, h = img.height * r;
                ctx.drawImage(img, imgBox.x + (imgBox.s - w) / 2, imgBox.y + (imgBox.s - h) / 2, w, h);
            }

            // Selo de desconto
            const bx = imgBox.x + imgBox.s - 40, by = imgBox.y + 20, br = story ? 110 : 85;
            ctx.fillStyle = '#f97316';
            ctx.beginPath(); ctx.arc(bx, by, br, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#fff';
            ctx.font = 'bold ' + (story ? 70 : 54) + 'px sans-serif';
            ctx.fillText('-' + c.discount + '%', bx, by + (story ? 25 : 19));

            let y = imgBox.y + imgBox.s + (story ? 130 : 90);
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold ' + (story ? 58 : 44) + 'px sans-serif';
            for (const line of this.wrap(ctx, c.name, W - 140, 2)) {
                ctx.fillText(line, W / 2, y);
                y += story ? 72 : 54;
            }

            y += story ? 40 : 14;
            ctx.font = (story ? 54 : 40) + 'px sans-serif';
            ctx.fillStyle = '#64748b';
            const deText = 'De ' + c.original;
            ctx.fillText(deText, W / 2, y);
            const dw = ctx.measureText(deText).width;
            ctx.strokeStyle = '#64748b'; ctx.lineWidth = story ? 6 : 4;
            ctx.beginPath(); ctx.moveTo(W / 2 - dw / 2, y - (story ? 18 : 13)); ctx.lineTo(W / 2 + dw / 2, y - (story ? 18 : 13)); ctx.stroke();

            y += story ? 130 : 86;
            ctx.fillStyle = '#e11d48';
            ctx.font = 'bold ' + (story ? 120 : 84) + 'px sans-serif';
            ctx.fillText('Por ' + c.promo, W / 2, y);

            ctx.fillStyle = '#475569';
            ctx.font = (story ? 44 : 32) + 'px sans-serif';
            ctx.fillText(c.validity, W / 2, story ? H - 90 : H - 32);
        },

        async drawList(ctx, W, H) {
            const story = this.format === 'story';
            const top = story ? 250 : 160;
            const max = story ? 7 : 4;
            const items = this.cards.slice(0, max);
            const rowH = (H - top - (story ? 120 : 60)) / items.length;
            ctx.textAlign = 'left';
            for (let i = 0; i < items.length; i++) {
                const c = items[i];
                const y = top + i * rowH;
                ctx.fillStyle = '#ffffff';
                ctx.beginPath(); ctx.roundRect(40, y + 8, W - 80, rowH - 16, 28); ctx.fill();
                const s = Math.min(rowH - 40, 200);
                const img = await this.loadImage(c.image);
                if (img) {
                    const r = Math.min(s / img.width, s / img.height);
                    ctx.drawImage(img, 70 + (s - img.width * r) / 2, y + (rowH - img.height * r) / 2, img.width * r, img.height * r);
                }
                const tx = 70 + s + 30, tw = W - tx - 200;
                ctx.fillStyle = '#1e293b';
                ctx.font = 'bold 36px sans-serif';
                const lines = this.wrap(ctx, c.name, tw, 2);
                let ly = y + rowH / 2 - (lines.length > 1 ? 40 : 20);
                for (const l of lines) { ctx.fillText(l, tx, ly); ly += 42; }
                ctx.font = '30px sans-serif'; ctx.fillStyle = '#64748b';
                ctx.fillText(c.original, tx, ly + 8);
                const ow = ctx.measureText(c.original).width;
                ctx.strokeStyle = '#64748b'; ctx.lineWidth = 3;
                ctx.beginPath(); ctx.moveTo(tx, ly - 2); ctx.lineTo(tx + ow, ly - 2); ctx.stroke();
                ctx.font = 'bold 44px sans-serif'; ctx.fillStyle = '#e11d48';
                ctx.fillText(c.promo, tx + ow + 20, ly + 10);
                ctx.fillStyle = '#f97316';
                ctx.beginPath(); ctx.roundRect(W - 190, y + rowH / 2 - 35, 130, 70, 35); ctx.fill();
                ctx.fillStyle = '#fff'; ctx.font = 'bold 36px sans-serif'; ctx.textAlign = 'center';
                ctx.fillText('-' + c.discount + '%', W - 125, y + rowH / 2 + 13);
                ctx.textAlign = 'left';
            }
            if (this.cards.length > max) {
                ctx.textAlign = 'center'; ctx.fillStyle = '#475569'; ctx.font = '34px sans-serif';
                ctx.fillText('e mais ' + (this.cards.length - max) + ' oferta(s)!', W / 2, H - (story ? 60 : 22));
            }
            ctx.textAlign = 'center';
        },

        blob() {
            return new Promise((resolve) => this.$refs.canvas.toBlob(resolve, 'image/png'));
        },

        async download() {
            const a = document.createElement('a');
            a.href = this.$refs.canvas.toDataURL('image/png');
            a.download = 'promocao-' + this.format + '.png';
            a.click();
        },

        async copyImage() {
            try {
                const blob = await this.blob();
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                this.toast('Imagem copiada. Cole na conversa do WhatsApp.');
            } catch (e) {
                this.download();
            }
        },

        async copyText() {
            await navigator.clipboard.writeText(this.$refs.text.value);
            await $wire.recordShare('copiar', null);
            this.toast('Texto copiado.');
        },

        async openWhatsapp(clientId) {
            // Abre a janela antes da chamada ao servidor para o navegador não bloquear o pop-up.
            const win = window.open('about:blank', '_blank');
            await $wire.$set('shareText', this.$refs.text.value, false);
            const url = await $wire.recordShare('whatsapp', clientId);
            if (win) { win.location.href = url; } else { window.location.href = url; }
            $wire.$refresh();
        },

        async shareAll() {
            const text = this.$refs.text.value;
            try {
                const file = new File([await this.blob()], 'promocao.png', { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], text });
                    await $wire.$set('shareText', text, false);
                    await $wire.recordShare('compartilhar', {{ $shareClientId ?? 'null' }});
                    $wire.$refresh();
                    return;
                }
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
            // Sem compartilhamento nativo (computador): copia a imagem e abre o WhatsApp com o texto.
            await this.copyImage();
            this.openWhatsapp({{ $shareClientId ?? 'null' }});
        },

        toast(message) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message, duration: 3000 } }));
        },
    }));
</script>
@endscript
