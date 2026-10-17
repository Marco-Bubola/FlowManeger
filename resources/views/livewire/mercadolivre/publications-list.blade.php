<div class="w-full app-viewport-fit mobile-393-base publications-page relative" x-data="{
    showFilters: false,
    tipsOpen: false,
    hasActiveFilters: {{ ($statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all' || $search) ? 'true' : 'false' }},
    openFiltersModal() {
        this.showFilters = true;
        document.documentElement.style.overflow = 'hidden';
    },
    closeFiltersModal() {
        this.showFilters = false;
        document.documentElement.style.overflow = '';
    },
    applyFiltersModal() {
        this.closeFiltersModal();
    }
}">

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-mobile.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-iphone15.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ipad-portrait.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ipad-landscape.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-notebook.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-ultrawide.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/responsive/publications-list-carousel.css') }}">
    @endpush

    <x-loading-overlay message="Carregando anúncios..." />

    {{-- Cabeçalho, busca e filtros no padrão das outras listas (Produtos, Promoções) --}}
    @php
        $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
        $pubFiltersOn = $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all' || $search;
        $pubChip = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold';
    @endphp
    <x-page-header title="Anúncios" icon="bi-megaphone" section="mercadolivre" tabs-section="mercadolivre" tabs-active="anuncios"
                   subtitle="Suas publicações no Mercado Livre">
        <x-slot:meta>
            @if($stats['errors'] > 0)
                <button type="button" wire:click="$set('syncFilter', 'error')" title="Ver só as publicações com erro" class="{{ $pubChip }} bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-500/20 dark:text-red-300"><i class="bi bi-exclamation-triangle"></i>{{ $stats['errors'] }} com erro</button>
            @endif
            <span class="{{ $pubChip }} bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-megaphone"></i>{{ $stats['total'] }} publicaç{{ $stats['total'] == 1 ? 'ão' : 'ões' }}</span>
            <span class="{{ $pubChip }} bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-check-circle"></i>{{ $stats['active'] }} ativa{{ $stats['active'] == 1 ? '' : 's' }}</span>
            @if($stats['kits'] > 0)
                <span class="{{ $pubChip }} bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300"><i class="bi bi-boxes"></i>{{ $stats['kits'] }} kit{{ $stats['kits'] == 1 ? '' : 's' }}</span>
            @endif
            @if(($stats['only_on_ml'] ?? 0) > 0)
                <span class="{{ $pubChip }} bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300"><i class="bi bi-cloud-download"></i>{{ $stats['only_on_ml'] }} só no ML</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" @click="showFilters = true" title="Filtros" class="{{ $phBtn }} relative">
                <i class="bi bi-sliders text-indigo-500"></i><span class="hidden sm:inline">Filtros</span>
                @if($pubFiltersOn)<span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-500 rounded-full border-2 border-white dark:border-slate-800"></span>@endif
            </button>
            <button type="button" wire:click="syncAllPublications()" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed"
                    title="Sincronizar tudo com o Mercado Livre" class="{{ $phBtn }} relative disabled:opacity-60">
                <i class="bi bi-arrow-repeat text-sky-500" wire:loading.class="animate-spin" wire:target="syncAllPublications"></i><span class="hidden sm:inline">Sincronizar</span>
                @if($isSyncing)<span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-cyan-400 rounded-full border-2 border-white dark:border-slate-800 animate-pulse"></span>@endif
            </button>
            <button type="button" @click="tipsOpen = true" title="Dicas de publicação" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-lightbulb text-amber-500"></i><span class="hidden sm:inline">Dicas</span></button>
            <a href="{{ route('mercadolivre.settings') }}" title="Configurações do Mercado Livre" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-gear text-slate-500"></i><span class="hidden sm:inline">Config</span></a>
            <a href="{{ route('mercadolivre.products') }}" title="Nova publicação"
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Nova publicação</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-list-toolbar placeholder="Buscar publicações...">
        <x-toolbar.group icon="bi-circle-half">
            <x-toolbar.chip wire:click="$set('statusFilter', 'all')" :active="$statusFilter === 'all'">Todos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'active')" :active="$statusFilter === 'active'" icon="bi-check-circle">Ativas</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'paused')" :active="$statusFilter === 'paused'" icon="bi-pause-circle">Pausadas</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group icon="bi-boxes">
            <x-toolbar.chip wire:click="$set('typeFilter', 'all')" :active="$typeFilter === 'all'">Tipos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('typeFilter', 'simple')" :active="$typeFilter === 'simple'">Simples</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('typeFilter', 'kit')" :active="$typeFilter === 'kit'">Kits</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group icon="bi-arrow-repeat">
            <x-toolbar.chip wire:click="$set('syncFilter', 'all')" :active="$syncFilter === 'all'">Sync</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('syncFilter', 'synced')" :active="$syncFilter === 'synced'">OK</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('syncFilter', 'pending')" :active="$syncFilter === 'pending'">Pendente</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('syncFilter', 'error')" :active="$syncFilter === 'error'">Erro</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group>
            <x-toolbar.chip wire:click="$set('viewMode', 'cards')" :active="$viewMode === 'cards'" icon="bi-grid-3x3-gap" title="Grade">Grade</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('viewMode', 'table')" :active="$viewMode === 'table'" icon="bi-list-ul" title="Lista">Lista</x-toolbar.chip>
        </x-toolbar.group>
    </x-list-toolbar>

    <x-filter-sheet title="Filtros de anúncios" subtitle="Status, tipo e sincronização" :active="(bool) $pubFiltersOn" target="statusFilter,typeFilter,syncFilter,clearFilters">
        @foreach([
            ['statusFilter', 'Status da publicação', 'bi-circle-half text-amber-500', [
                ['all', 'Todos', 'bi-list-check'], ['active', 'Ativas', 'bi-check-circle'], ['paused', 'Pausadas', 'bi-pause-circle'],
                ['closed', 'Fechadas', 'bi-x-circle'], ['under_review', 'Em revisão', 'bi-search'],
            ]],
            ['typeFilter', 'Tipo de publicação', 'bi-boxes text-blue-500', [
                ['all', 'Todos', 'bi-box-seam'], ['simple', 'Simples', 'bi-box'], ['kit', 'Kits', 'bi-boxes'],
            ]],
            ['syncFilter', 'Sincronização', 'bi-arrow-repeat text-teal-500', [
                ['all', 'Todos', 'bi-arrow-repeat'], ['synced', 'Sincronizados', 'bi-check-circle'], ['pending', 'Pendentes', 'bi-clock'], ['error', 'Com erros', 'bi-exclamation-triangle'],
            ]],
        ] as [$prop, $label, $ico, $opts])
            <div class="fm-fs-group">
                <h4><i class="bi {{ $ico }}"></i>{{ $label }}</h4>
                <div class="fm-fs-options">
                    @foreach($opts as [$val, $txt, $oi])
                        <button type="button" wire:click="$set('{{ $prop }}', '{{ $val }}')" class="fm-fs-opt {{ $$prop === $val ? 'is-on' : '' }}"><i class="bi {{ $oi }}"></i>{{ $txt }}</button>
                    @endforeach
                </div>
            </div>
        @endforeach
        <div class="fm-fs-group">
            <h4><i class="bi bi-layout-split text-indigo-500"></i>Visualização</h4>
            <div class="fm-fs-options">
                <button type="button" wire:click="$set('viewMode', 'cards')" class="fm-fs-opt {{ $viewMode === 'cards' ? 'is-on' : '' }}"><i class="bi bi-grid-3x3-gap"></i>Grade</button>
                <button type="button" wire:click="$set('viewMode', 'table')" class="fm-fs-opt {{ $viewMode === 'table' ? 'is-on' : '' }}"><i class="bi bi-list-ul"></i>Lista</button>
            </div>
        </div>
        <div class="fm-fs-group sm:hidden">
            <h4><i class="bi bi-three-dots text-slate-500"></i>Mais</h4>
            <div class="fm-fs-options">
                <button type="button" @click="showFilters = false; tipsOpen = true" class="fm-fs-opt"><i class="bi bi-lightbulb"></i>Dicas de publicação</button>
                <a href="{{ route('mercadolivre.settings') }}" class="fm-fs-opt"><i class="bi bi-gear"></i>Configurações</a>
            </div>
        </div>
    </x-filter-sheet>

    {{-- ═══════════════════════════════════════════════
         INDICADOR DE SINCRONIZAÇÃO AUTOMÁTICA (INLINE)
    ═══════════════════════════════════════════════ --}}
    @if($isSyncing)
    <div class="mb-4 rounded-xl overflow-hidden border-2 border-blue-400 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-950/30 dark:to-cyan-950/30">
        <div class="p-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                        <i class="bi bi-arrow-repeat text-white animate-spin"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-blue-700 dark:text-blue-300">🔄 Sincronizando com o Mercado Livre...</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $syncedCount }} de {{ $totalToSync }} publicações sincronizadas</p>
                        @if($totalToSync > 0)
                        <div class="mt-1.5 w-full bg-blue-200 dark:bg-blue-900 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-500"
                                 style="width: {{ ($syncedCount / $totalToSync) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
                

            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════
         CONTEÚDO PRINCIPAL - GRID DE PUBLICAÇÕES
    ═══════════════════════════════════════════════ --}}
    <div class="pb-6">
        
        @if($viewMode === 'cards')
            {{-- GRID DE PUBLICAÇÕES - 5 colunas --}}
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 gap-6">
                @forelse($publications as $publication)
                    @php
                        $statusConfig = match($publication->status) {
                            'active' => ['color' => 'from-green-500 to-green-600', 'icon' => 'check-circle-fill', 'text' => 'Ativo'],
                            'paused' => ['color' => 'from-amber-500 to-amber-600', 'icon' => 'pause-circle-fill', 'text' => 'Pausado'],
                            'closed' => ['color' => 'from-red-500 to-red-600', 'icon' => 'x-circle-fill', 'text' => 'Fechado'],
                            'under_review' => ['color' => 'from-purple-500 to-purple-600', 'icon' => 'search', 'text' => 'Em Revisão'],
                            default => ['color' => 'from-slate-500 to-slate-600', 'icon' => 'question-circle', 'text' => ucfirst($publication->status)],
                        };
                        
                        $syncConfig = match($publication->sync_status) {
                            'synced' => ['color' => 'from-blue-500 to-blue-600', 'icon' => 'arrow-repeat', 'text' => 'Sincronizado'],
                            'pending' => ['color' => 'from-yellow-500 to-yellow-600', 'icon' => 'clock', 'text' => 'Pendente'],
                            'error' => ['color' => 'from-red-500 to-red-600', 'icon' => 'exclamation-triangle', 'text' => 'Erro'],
                            default => ['color' => 'from-slate-500 to-slate-600', 'icon' => 'question-circle', 'text' => 'Desconhecido'],
                        };

                        $availableQty = $publication->calculateAvailableQuantity();
                        
                        // Usar imagens do catálogo ML (pictures) ou fallback para imagem do produto
                        $catalogImages = $publication->pictures ?? [];
                        $mainImage = !empty($catalogImages) ? $catalogImages[0] : null;
                        
                        if (!$mainImage && $publication->products->first()) {
                            $firstProduct = $publication->products->first();
                            if ($firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
                                $mainImage = asset('storage/' . $firstProduct->image);
                            }
                        }
                    @endphp

                    {{-- ═════════════════════════════════════════════════════
                         PUBLICATION CARD: carousel de product cards
                         (cada produto aparece como card idêntico ao products-index,
                          um por vez com navegação por setas e dots)
                    ═════════════════════════════════════════════════════ --}}
                    <div class="product-card-modern pub-publication-card"
                         wire:key="pub-{{ $publication->id }}"
                         x-data="{ slide: 0, count: {{ $publication->products->count() }} }">

                        {{-- ══ BOTÕES DE AÇÃO DA PUBLICAÇÃO (flutuantes, z-index alto) ══ --}}
                        <div class="btn-action-group">
                            @if($publication->ml_permalink)
                                <a href="{{ $publication->ml_permalink }}" target="_blank" rel="noopener"
                                   title="Ver no Mercado Livre"
                                   class="btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; border: 2px solid #fcd34d;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                            <a href="{{ route('mercadolivre.publications.show', $publication->id) }}"
                               title="Ver Publicação" class="btn btn-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($publication->ml_item_id)
                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}"
                                   title="Editar Publicação" class="btn btn-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button wire:click="syncPublication({{ $publication->id }})"
                                        wire:loading.attr="disabled"
                                        title="Sincronizar com ML"
                                        class="btn" style="background: #3b82f6; color: #fff; border: 2px solid #93c5fd;">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                @if($publication->status === 'active')
                                    <button wire:click="pausePublication({{ $publication->id }})"
                                            wire:confirm="Pausar esta publicação?"
                                            title="Pausar Publicação"
                                            class="btn" style="background: #f59e0b; color: #fff; border: 2px solid #fcd34d;">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                @elseif($publication->status === 'paused')
                                    <button wire:click="activatePublication({{ $publication->id }})"
                                            title="Ativar Publicação"
                                            class="btn" style="background: #10b981; color: #fff; border: 2px solid #6ee7b7;">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()?->id) }}"
                                   title="Publicar no Mercado Livre"
                                   class="btn btn-success" style="animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite;">
                                    <i class="bi bi-upload"></i>
                                </a>
                            @endif
                            <button wire:click="deletePublication({{ $publication->id }})"
                                    wire:confirm="Tem certeza que deseja excluir esta publicação?"
                                    title="Excluir Publicação"
                                    class="btn btn-danger">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>

                        {{-- ══ CARROSSEL DE CARDS DE PRODUTO (estilo products-index) ══ --}}
                        <div class="pub-product-carousel">

                            {{-- Slides: um card completo por produto --}}
                            @foreach($publication->products as $idx => $product)
                                <div x-show="slide === {{ $idx }}"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     style="{{ $idx !== 0 ? 'display:none;' : '' }}"
                                     class="pub-product-slide">

                                    {{-- ── Área de imagem (idêntica ao products-index) ────── --}}
                                    <div class="product-img-area pub-img-area">
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <img src="{{ asset('storage/products/' . $product->image) }}"
                                                 class="product-img"
                                                 alt="{{ $product->name }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center"
                                                 style="background: var(--card-bg);">
                                                <i class="bi bi-image text-5xl" style="color: var(--card-border);"></i>
                                            </div>
                                        @endif

                                        {{-- Fora de estoque do produto --}}
                                        @if($product->stock_quantity == 0)
                                            <div class="out-of-stock">
                                                <i class="bi bi-x-circle"></i> Fora de Estoque
                                            </div>
                                        @endif

                                        {{-- Overlay "Não Publicado" (publicação ainda não enviada ao ML) --}}
                                        @if(!$publication->ml_item_id)
                                            <div class="out-of-stock"
                                                 style="background: linear-gradient(90deg, rgba(245,158,11,0.95), rgba(217,119,6,0.95)); transform: translateY(-50%) rotate(-12deg); z-index: 12;">
                                                <i class="bi bi-clock-history"></i> Não Publicado
                                            </div>
                                        @endif

                                        {{-- Badge código do produto (top-left) --}}
                                        <span class="badge-product-code"
                                              title="Código: {{ $product->product_code }}">
                                            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                        </span>

                                        {{-- Badge estoque do produto (bottom-right) --}}
                                        <span class="badge-quantity"
                                              title="Estoque: {{ $product->stock_quantity }} unidades">
                                            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                                        </span>

                                        {{-- Ícone da categoria (bottom-center, idêntico ao products-index) --}}
                                        <div class="category-icon-wrapper"
                                             title="{{ $product->category?->name ?? 'Categoria' }}">
                                            <i class="{{ $product->category?->icone ?? 'bi bi-box' }} category-icon"></i>
                                        </div>
                                    </div>{{-- /product-img-area --}}

                                    {{-- ── Conteúdo do produto (idêntico ao products-index) ── --}}
                                    <div class="card-body">
                                        <div class="product-title" title="{{ $product->name }}">
                                            {{ ucwords($product->name) }}
                                        </div>

                                        {{-- Status + badge Novo (igual products-index) --}}
                                        <div class="mt-1 mb-1 flex items-center gap-1.5 flex-wrap justify-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                                bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-100
                                                text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-800
                                                dark:bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-900
                                                dark:text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-200">
                                                <i class="bi bi-circle-fill mr-0.5" style="font-size:6px;"></i>
                                                {{ ucfirst($product->status) }}
                                            </span>
                                            @if(\Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    <i class="bi bi-stars mr-0.5"></i> Novo
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Preços do produto (idêntico ao products-index) --}}
                                        <div class="price-area mt-1">
                                            <div class="flex flex-col gap-1">
                                                <span class="badge-price" title="Preço de Custo">
                                                    <i class="bi bi-tag"></i>
                                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                                </span>
                                                <span class="badge-price-sale" title="Preço de Venda">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>{{-- /card-body produto --}}

                                </div>{{-- /pub-product-slide --}}
                            @endforeach

                            {{-- Anúncio sem produto ligado (ex.: importado do ML) --}}
                            @if($publication->products->isEmpty())
                                @php
                                    $pic = $mainImage;
                                    if (is_array($pic)) { $pic = $pic['secure_url'] ?? $pic['url'] ?? null; }
                                @endphp
                                <div class="pub-product-slide">
                                    <div class="product-img-area pub-img-area">
                                        @if($pic)
                                            <img src="{{ $pic }}" class="product-img" alt="{{ $publication->title }}" style="object-fit: contain; background: #fff;">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center" style="background: var(--card-bg);">
                                                <i class="bi bi-image text-5xl" style="color: var(--card-border);"></i>
                                            </div>
                                        @endif
                                    </div>
                                    @if($publication->ml_item_id)
                                    <div class="pt-3 pb-1 flex flex-col items-center gap-1.5 text-center" style="padding-left: 0.75rem; padding-right: 3.25rem;">
                                        @if($publication->usesVariationLinks())
                                        @php $varLinked = $publication->variationLinks()->whereNotNull('product_id')->count(); @endphp
                                        <span class="text-[10px] font-semibold {{ $varLinked >= $publication->mlVariationCount() ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}"><i class="bi bi-diagram-3"></i> {{ $varLinked }}/{{ $publication->mlVariationCount() }} variações ligadas</span>
                                        <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition-all shadow-sm">
                                            <i class="bi bi-link-45deg"></i> Ligar variações
                                        </a>
                                        @else
                                        <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-300"><i class="bi bi-link-45deg"></i> Sem produto do estoque ligado</span>
                                        <button wire:click="openLinkModal({{ $publication->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition-all shadow-sm">
                                            <i class="bi bi-link-45deg"></i> Ligar a um produto
                                        </button>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Setas de navegação (só quando há mais de 1 produto) --}}
                            @if($publication->products->count() > 1)
                                <button x-on:click.prevent="slide = (slide - 1 + count) % count"
                                        class="pub-carousel-arrow pub-carousel-arrow-left"
                                        title="Produto anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button x-on:click.prevent="slide = (slide + 1) % count"
                                        class="pub-carousel-arrow pub-carousel-arrow-right"
                                        title="Próximo produto">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            @endif

                        </div>{{-- /pub-product-carousel --}}

                        {{-- Dots indicadores + contador (só quando há mais de 1 produto) --}}
                        @if($publication->products->count() > 1)
                            <div class="pub-carousel-nav">
                                @foreach($publication->products as $idx => $product)
                                    <button x-on:click.prevent="slide = {{ $idx }}"
                                            :class="['pub-carousel-dot', slide === {{ $idx }} ? 'pub-carousel-dot-active' : '']"
                                            title="{{ $product->name }}"></button>
                                @endforeach
                                <span class="pub-carousel-counter"
                                      x-text="(slide + 1) + ' / ' + count"></span>
                            </div>
                        @endif

                        {{-- ══ FOOTER: DADOS DA PUBLICAÇÃO NO ML ══ --}}
                        <div class="pub-info-footer">

                            {{-- Título da publicação --}}
                            <div class="product-title" title="{{ $publication->title }}" style="font-size:0.82rem;">
                                <i class="bi bi-megaphone-fill"
                                   style="color:var(--card-accent); font-size:0.65rem; vertical-align:middle;"></i>
                                {{ $publication->title }}
                            </div>

                            {{-- ML ID + Tipo de publicação --}}
                            <div class="flex items-center gap-1 flex-wrap justify-center mt-1">
                                <span class="pub-ml-badge"
                                      title="{{ $publication->ml_item_id ? 'ID ML: '.$publication->ml_item_id : 'Aguardando publicação' }}">
                                    @if($publication->ml_item_id)
                                        <i class="bi bi-tag-fill"></i> {{ substr($publication->ml_item_id, 0, 10) }}
                                    @else
                                        <i class="bi bi-clock"></i> Pendente
                                    @endif
                                </span>
                                <span class="pub-type-badge {{ $publication->publication_type === 'kit' ? 'pub-type-kit' : 'pub-type-simple' }}">
                                    <i class="bi bi-{{ $publication->publication_type === 'kit' ? 'boxes' : 'box' }}"></i>
                                    {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                </span>
                            </div>

                            {{-- Status ML + Status Sync --}}
                            <div class="flex items-center gap-1 flex-wrap justify-center mt-1">
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-gradient-to-r {{ $statusConfig['color'] }} text-white shadow-sm">
                                    <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['text'] }}
                                </span>
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-gradient-to-r {{ $syncConfig['color'] }} text-white shadow-sm">
                                    <i class="bi bi-{{ $syncConfig['icon'] }}"></i>
                                    {{ $syncConfig['text'] }}
                                </span>
                            </div>

                            {{-- Mensagem de erro ML --}}
                            @if($publication->error_message)
                                <div class="mt-1 px-2 py-1 rounded-lg w-full"
                                     style="background: var(--card-danger); border: 1px solid #ef9a9a;">
                                    <p class="text-[9px] truncate text-center" style="color: #b71c1c;"
                                       title="{{ $publication->error_message }}">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        {{ \Illuminate\Support\Str::limit($publication->error_message, 35) }}
                                    </p>
                                </div>
                            @endif

                            {{-- Preço no ML + Qtd disponível --}}
                            <div class="price-area mt-1">
                                <span class="badge-price" title="Preço no Mercado Livre">
                                    <i class="bi bi-tag-fill"></i>
                                    R$ {{ number_format($publication->price, 2, ',', '.') }}
                                </span>
                                <span class="badge-price-sale" title="{{ $publication->products->isEmpty() ? 'Quantidade no Mercado Livre' : 'Quantidade disponível pelo seu estoque' }}">
                                    <i class="bi bi-stack"></i> {{ $publication->products->isEmpty() ? (int) $publication->available_quantity : $availableQty }}
                                </span>
                            </div>

                        </div>{{-- /pub-info-footer --}}

                    </div>{{-- /pub-publication-card --}}
            @empty
                <div class="col-span-full">
                    <x-empty-state icon="bi-megaphone" tone="amber" title="Nenhuma publicação encontrada"
                        :text="($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all') ? 'Ajuste os filtros ou pesquise novamente.' : 'Crie sua primeira publicação no Mercado Livre.'">
                        @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                            <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200"><i class="bi bi-eraser"></i>Limpar filtros</button>
                        @endif
                        <a href="{{ route('mercadolivre.products') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 shadow-md shadow-amber-500/25"><i class="bi bi-plus-lg"></i>Nova publicação</a>
                    </x-empty-state>
                </div>
            @endforelse
        </div>
        
        @else
            {{-- VISUALIZAÇÃO EM TABELA --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 border-b-2 border-amber-200 dark:border-amber-800">
                                <th class="px-4 py-4 text-left text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Publicação
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Produtos
                                </th>
                                <th class="px-4 py-4 text-right text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Preço
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Estoque
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-amber-900 dark:text-amber-100 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($publications as $publication)
                                @php
                                    $statusConfig = match($publication->status) {
                                        'active' => ['color' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-green-300', 'icon' => 'check-circle-fill'],
                                        'paused' => ['color' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-300', 'icon' => 'pause-circle-fill'],
                                        'closed' => ['color' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-300', 'icon' => 'x-circle-fill'],
                                        'under_review' => ['color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 border-purple-300', 'icon' => 'search'],
                                        default => ['color' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300 border-slate-300', 'icon' => 'question-circle'],
                                    };
                                    
                                    $syncConfig = match($publication->sync_status) {
                                        'synced' => ['color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-300', 'icon' => 'arrow-repeat'],
                                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-yellow-300', 'icon' => 'clock'],
                                        'error' => ['color' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-300', 'icon' => 'exclamation-triangle'],
                                        default => ['color' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300 border-slate-300', 'icon' => 'question-circle'],
                                    };

                                    $availableQty = $publication->calculateAvailableQuantity();
                                    $catalogImages = $publication->pictures ?? [];
                                    $mainImage = !empty($catalogImages) ? $catalogImages[0] : null;
                                    
                                    if (!$mainImage && $publication->products->first()) {
                                        $firstProduct = $publication->products->first();
                                        if ($firstProduct->image && $firstProduct->image !== 'product-placeholder.png') {
                                            $mainImage = asset('storage/products/' . $firstProduct->image);
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    {{-- Publicação (com imagem e título) --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shadow-md">
                                                @if($mainImage)
                                                    <img src="{{ is_array($mainImage) ? ($mainImage['url'] ?? $mainImage['secure_url'] ?? '') : $mainImage }}" 
                                                         alt="{{ $publication->title }}"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="bi bi-image text-2xl text-slate-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 line-clamp-2 mb-1">
                                                    {{ $publication->title }}
                                                </h4>
                                                @if($publication->ml_item_id)
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                        <i class="bi bi-tag-fill"></i> {{ $publication->ml_item_id }}
                                                    </p>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-semibold">
                                                        <i class="bi bi-clock"></i> Não publicado
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    {{-- Status --}}
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $statusConfig['color'] }} text-xs font-semibold border w-fit">
                                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                                {{ ucfirst($publication->status) }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $syncConfig['color'] }} text-xs font-semibold border w-fit">
                                                <i class="bi bi-{{ $syncConfig['icon'] }}"></i>
                                                {{ $publication->sync_status === 'synced' ? 'Sincronizado' : ($publication->sync_status === 'error' ? 'Erro' : 'Pendente') }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    {{-- Tipo --}}
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $publication->publication_type === 'kit' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            <i class="bi bi-{{ $publication->publication_type === 'kit' ? 'boxes' : 'box' }}"></i>
                                            {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                        </span>
                                    </td>
                                    
                                    {{-- Produtos --}}
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($publication->products->take(2) as $product)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300" title="{{ $product->name }}">
                                                    <i class="bi bi-box"></i>
                                                    {{ \Illuminate\Support\Str::limit($product->name, 20) }}
                                                </span>
                                            @endforeach
                                            @if($publication->products->count() > 2)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-bold">
                                                    +{{ $publication->products->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    {{-- Preço --}}
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-lg font-black text-green-600 dark:text-green-400">
                                            R$ {{ number_format($publication->price, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    
                                    {{-- Estoque --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold">
                                            <i class="bi bi-stack"></i>
                                            {{ $availableQty }}
                                        </span>
                                    </td>
                                    
                                    {{-- Ações --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            @if($publication->ml_permalink)
                                                <a href="{{ $publication->ml_permalink }}" 
                                                   target="_blank"
                                                   title="Ver no ML"
                                                   class="p-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                            
                                            {{-- Ver Publicação (sempre visível) --}}
                                            <a href="{{ route('mercadolivre.publications.show', $publication->id) }}" 
                                               title="Ver Publicação"
                                               class="p-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-all">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            @if($publication->ml_item_id)
                                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}" 
                                                   title="Editar"
                                                   class="p-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button wire:click="syncPublication({{ $publication->id }})" 
                                                        title="Sincronizar"
                                                        class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()->id) }}" 
                                                   title="Publicar"
                                                   class="p-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all">
                                                    <i class="bi bi-upload"></i>
                                                </a>
                                            @endif
                                            
                                            <button wire:click="deletePublication({{ $publication->id }})" 
                                                    wire:confirm="Excluir esta publicação?"
                                                    title="Excluir"
                                                    class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12">
                <div class="col-span-full">
                    <x-empty-state icon="bi-megaphone" tone="amber" title="Nenhuma publicação encontrada"
                        :text="($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all') ? 'Ajuste os filtros ou pesquise novamente.' : 'Crie sua primeira publicação no Mercado Livre.'">
                        @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                            <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200"><i class="bi bi-eraser"></i>Limpar filtros</button>
                        @endif
                        <a href="{{ route('mercadolivre.products') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 shadow-md shadow-amber-500/25"><i class="bi bi-plus-lg"></i>Nova publicação</a>
                    </x-empty-state>
                </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Paginação Completa (footer) --}}
        @if($publications->hasPages())
        <div class="mt-8">
            <x-infinite-feed :paginator="$publications" label="publicações" />
        </div>
        @endif

        {{-- Outras publicações no ML (não criadas pelo sistema) --}}
        @if($onlyOnMlItems->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-cloud text-amber-500"></i>
                Outras publicações no Mercado Livre
                <span class="text-sm font-normal text-slate-500 dark:text-slate-400">(criadas direto no ML — importe para editar aqui)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($onlyOnMlItems as $item)
                    <div class="rounded-2xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/30 p-4 shadow-lg">
                        @if(!empty($item['thumbnail']))
                            <img src="{{ $item['thumbnail'] }}" alt="" class="w-full h-40 object-contain rounded-xl bg-white dark:bg-slate-800 mb-3">
                        @else
                            <div class="w-full h-40 rounded-xl bg-slate-200 dark:bg-slate-700 flex items-center justify-center mb-3">
                                <i class="bi bi-image text-3xl text-slate-400"></i>
                            </div>
                        @endif
                        <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm line-clamp-2 mb-2" title="{{ $item['title'] }}">{{ $item['title'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mb-3">{{ $item['id'] }}</p>
                        <div class="flex items-center gap-2">
                            @if(!empty($item['permalink']))
                                <a href="{{ $item['permalink'] }}" target="_blank" rel="noopener"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-all">
                                    <i class="bi bi-box-arrow-up-right"></i> Ver no ML
                                </a>
                            @endif
                            <button wire:click="importFromMl('{{ $item['id'] ?? '' }}')" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition-all disabled:opacity-50">
                                <i class="bi bi-download" wire:loading.remove wire:target="importFromMl"></i>
                                <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="importFromMl"></i>
                                Importar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         MODAL "LIGAR A UM PRODUTO" (depois de importar do ML)
    ═══════════════════════════════════════════════════════════════ --}}
    @if($linkPublicationId && $this->linkPublication)
    @php $lp = $this->linkPublication; $lpPic = $lp->pictures[0] ?? null; if (is_array($lpPic)) { $lpPic = $lpPic['secure_url'] ?? $lpPic['url'] ?? null; } @endphp
    <div class="fixed inset-0 z-[210] flex items-end sm:items-center justify-center" wire:key="link-modal-{{ $lp->id }}"
         x-data @keydown.escape.window="$wire.closeLinkModal()">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer" wire:click="closeLinkModal"></div>

        <div class="relative w-full sm:w-auto sm:min-w-[540px] sm:max-w-xl mx-0 sm:mx-4 bg-white/98 dark:bg-slate-900/98 backdrop-blur-xl rounded-t-3xl sm:rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden">
            <div class="sm:hidden mx-auto mt-3 mb-1 w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>

            {{-- Header --}}
            <div style="flex-wrap: nowrap;" class="flex items-center justify-between gap-3 px-5 sm:px-6 pt-4 sm:pt-5 pb-4 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-amber-50/80 to-orange-50/80 dark:from-amber-950/30 dark:to-orange-950/30">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/20 shrink-0">
                        <i class="bi bi-link-45deg text-white text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Ligar a um produto</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Qual produto do seu estoque é vendido neste anúncio?</p>
                    </div>
                </div>
                <button wire:click="closeLinkModal"
                        class="flex items-center justify-center w-9 h-9 rounded-xl hover:bg-amber-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all duration-200 shrink-0">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 sm:px-6 py-4 space-y-3 max-h-[65vh] overflow-y-auto">
                {{-- Anúncio --}}
                <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    @if($lpPic)
                        <img src="{{ $lpPic }}" alt="" class="w-12 h-12 rounded-xl object-contain bg-white shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0"><i class="bi bi-image text-slate-400"></i></div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-2">{{ $lp->title }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $lp->ml_item_id }} · {{ (int) $lp->available_quantity }} un. no ML · R$ {{ number_format($lp->price, 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Busca --}}
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="text" wire:model.live.debounce.300ms="linkSearch" autofocus
                           placeholder="Buscar por nome, código ou código de barras..."
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-900 outline-none">
                </div>

                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    {{ mb_strlen(trim($linkSearch)) >= 2 ? 'Resultados' : 'Sugestões pelo nome do anúncio' }}
                </p>

                @php $cands = $this->linkCandidates; @endphp
                @if($cands->isNotEmpty())
                    <div class="space-y-1.5">
                        @foreach($cands as $cand)
                            @php $candStock = $cand->isKit() ? $cand->availableStock() : (int) $cand->stock_quantity; @endphp
                            <button type="button" wire:click="linkProduct({{ $cand->id }})" wire:key="cand-{{ $cand->id }}"
                                    wire:loading.attr="disabled" wire:target="linkProduct"
                                    class="w-full flex items-center gap-3 p-2.5 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-all text-left group disabled:opacity-60">
                                <img src="{{ $cand->image_url }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0 bg-slate-100 dark:bg-slate-700"
                                     onerror="this.style.visibility='hidden'">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $cand->name }}</p>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                        <span class="font-mono truncate">{{ $cand->product_code }}</span>
                                        @if($cand->isKit())<span class="px-1.5 rounded bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-bold text-[10px]">KIT</span>@endif
                                        <span class="font-semibold whitespace-nowrap {{ $candStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">{{ $candStock }} un.</span>
                                    </div>
                                </div>
                                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500 group-hover:bg-amber-600 text-white text-xs font-semibold">
                                    <i class="bi bi-link-45deg"></i> Ligar
                                </span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-6 text-sm text-slate-400 dark:text-slate-500">
                        <i class="bi bi-search mr-1"></i>
                        {{ mb_strlen(trim($linkSearch)) >= 2 ? 'Nenhum produto encontrado' : 'Nenhuma sugestão — digite o nome ou o código do produto' }}
                    </p>
                @endif

                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                    <i class="bi bi-info-circle mr-0.5"></i>Ligar não muda nada no Mercado Livre. Na tela de edição você confere e escolhe se envia o seu estoque.
                </p>
            </div>

            {{-- Footer --}}
            <div class="px-5 sm:px-6 pb-5 pt-3 flex items-center justify-between gap-2 border-t border-slate-200/60 dark:border-slate-700/60">
                <a href="{{ route('mercadolivre.publications.edit', $lp->id) }}"
                   class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-amber-600">
                    <i class="bi bi-pencil-square"></i> Abrir edição
                </a>
                <button wire:click="closeLinkModal"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold transition-all text-sm">
                    Ligar depois
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL DE DICAS DE PUBLICAÇÃO
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="tipsOpen"
         x-cloak
         class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center"
         @keydown.escape.window="tipsOpen = false">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"
             @click="tipsOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Painel do Modal --}}
        <div class="relative w-full sm:w-auto sm:min-w-[540px] sm:max-w-xl mx-0 sm:mx-4 bg-white/98 dark:bg-slate-900/98 backdrop-blur-xl rounded-t-3xl sm:rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-0">

            {{-- Mobile handle --}}
            <div class="sm:hidden mx-auto mt-3 mb-1 w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-200/60 dark:border-slate-700/60 bg-gradient-to-r from-amber-50/80 to-orange-50/80 dark:from-amber-950/30 dark:to-orange-950/30">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/20">
                        <i class="bi bi-lightbulb-fill text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Dicas de Publicação</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Melhores práticas no Mercado Livre</p>
                    </div>
                </div>
                <button @click="tipsOpen = false"
                        class="flex items-center justify-center w-9 h-9 rounded-xl hover:bg-amber-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all duration-200">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-3 max-h-[65vh] overflow-y-auto">

                {{-- Dica 1: Título --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-type-h1 text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-0.5">Título Otimizado</p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">Use 60–80 caracteres com palavras-chave relevantes. Inclua marca, modelo e características principais. Evite símbolos e maiúsculas excessivas.</p>
                    </div>
                </div>

                {{-- Dica 2: Imagens --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-purple-50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-purple-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-images text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-purple-800 dark:text-purple-300 mb-0.5">Imagens de Qualidade</p>
                        <p class="text-xs text-purple-700 dark:text-purple-400 leading-relaxed">Use no mínimo 6 fotos com fundo branco. Mínimo 500x500px. A primeira imagem é a mais importante — mostre o produto com clareza.</p>
                    </div>
                </div>

                {{-- Dica 3: Preço --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-currency-dollar text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300 mb-0.5">Preço Competitivo</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">Pesquise a concorrência antes de definir o preço. Lembre-se das taxas do ML (cerca de 11–16% para conta clássica). Ofereça frete grátis quando possível.</p>
                    </div>
                </div>

                {{-- Dica 4: Estoque --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-boxes text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-0.5">Estoque Atualizado</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">Mantenha o estoque sempre sincronizado para evitar vendas canceladas. Publicações pausadas por falta de estoque afetam a reputação do vendedor.</p>
                    </div>
                </div>

                {{-- Dica 5: Sincronização --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-teal-50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-teal-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-arrow-repeat text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-teal-800 dark:text-teal-300 mb-0.5">Sincronização Regular</p>
                        <p class="text-xs text-teal-700 dark:text-teal-400 leading-relaxed">Use o botão de sincronizar para atualizar preço e estoque no ML. Erros de sincronização ficam marcados em vermelho — resolva-os rapidamente.</p>
                    </div>
                </div>

                {{-- Dica 6: Kits --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-indigo-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-gift text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-indigo-800 dark:text-indigo-300 mb-0.5">Kits Aumentam o Ticket</p>
                        <p class="text-xs text-indigo-700 dark:text-indigo-400 leading-relaxed">Publicações do tipo "Kit" combinam produtos complementares. O estoque disponível é calculado automaticamente com base no produto de menor estoque do kit.</p>
                    </div>
                </div>

                {{-- Dica 7: Reputação --}}
                <div class="flex gap-3 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-rose-500 flex items-center justify-center shadow-sm">
                        <i class="bi bi-star-fill text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-rose-800 dark:text-rose-300 mb-0.5">Proteja sua Reputação</p>
                        <p class="text-xs text-rose-700 dark:text-rose-400 leading-relaxed">Responda perguntas em menos de 24h. Envie dentro do prazo. Reputação verde ou amarela garante maior visibilidade nos resultados do ML.</p>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-6 pb-5 pt-3 flex items-center justify-end border-t border-slate-200/60 dark:border-slate-700/60">
                <button @click="tipsOpen = false"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all text-sm">
                    <i class="bi bi-check-lg"></i> Entendido!
                </button>
            </div>
        </div>
    </div>

</div>
