<div x-data="{ showFilters: false }" class="product-integration-page min-h-screen flex flex-col mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/product-integration-ultrawide.css') }}">
    {{-- Os CSS de iPad desta tela têm chaves sobrando e suas regras vazam para todos os tamanhos
         (grade de 4 colunas no celular, texto dos botões escondido, título grande). Aqui o cabeçalho
         padrão e a grade voltam ao normal sem mexer nesses arquivos. --}}
    <style>
        .product-integration-page .app-ph h1 { font-size: 1.25rem !important; line-height: 1.75rem !important; }
        .product-integration-page .app-ph-actions a { padding: 0.5rem 0.75rem !important; font-size: 0.875rem !important; }
        @media (min-width: 640px) {
            .product-integration-page .app-ph h1 { font-size: 1.5rem !important; line-height: 2rem !important; }
            .product-integration-page .app-ph-actions a span.sm\:inline { display: inline !important; }
            .product-integration-page .app-ph-actions a { padding: 0.5rem 1rem !important; }
        }
        @media (max-width: 767px) {
            .product-integration-page .products-grid.products-grid { grid-template-columns: minmax(0, 1fr) !important; }
        }
    </style>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    {{-- Modal de Publicações do Produto --}}
    @livewire('mercado-livre.product-publications-modal')

    {{-- Cabeçalho, busca e filtros no padrão das outras listas (Produtos, Promoções) --}}
    @php
        $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
        $piChip = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold';
        $piFiltersOn = $statusFilter !== 'all' || $categoryFilter !== 'all' || $search !== '';
    @endphp
    <x-page-header title="Produtos no ML" icon="bi-box-seam" section="mercadolivre" tabs-section="mercadolivre" tabs-active="produtos"
                   subtitle="Escolha produtos do estoque para publicar no Mercado Livre">
        <x-slot:meta>
            <span class="{{ $piChip }} bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-box-seam"></i>{{ $this->totalProducts ?? 0 }} produtos</span>
            <span class="{{ $piChip }} bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300"><i class="bi bi-check-circle"></i>{{ $this->publishedCount ?? 0 }} publicados</span>
            @if(($this->pendingCount ?? 0) > 0)
                <button type="button" wire:click="$set('statusFilter', 'unpublished')" title="Ver só os pendentes" class="{{ $piChip }} bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-500/20 dark:text-amber-200"><i class="bi bi-clock"></i>{{ $this->pendingCount }} pendentes</button>
            @endif
            @if($isConnected)
                <span class="{{ $piChip }} bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Conectado</span>
            @else
                <a href="{{ route('mercadolivre.settings') }}" class="{{ $piChip }} bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/15 dark:text-red-300"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Desconectado</a>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" @click="showFilters = true" title="Filtros" class="{{ $phBtn }} relative">
                <i class="bi bi-sliders text-indigo-500"></i><span class="hidden sm:inline">Filtros</span>
                @if($piFiltersOn)<span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-500 rounded-full border-2 border-white dark:border-slate-800"></span>@endif
            </button>
            <a href="{{ route('mercadolivre.publications') }}" title="Publicações no Mercado Livre" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-list-check text-purple-500"></i><span class="hidden sm:inline">Publicações</span></a>
            <a href="{{ route('mercadolivre.settings') }}" title="Configurações do Mercado Livre" class="{{ $phBtn }} max-sm:!hidden"><i class="bi bi-gear text-slate-500"></i><span class="hidden sm:inline">Config</span></a>
            <a href="{{ route('mercadolivre.products.publish.create') }}" title="Criar publicação"
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-md shadow-amber-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Criar publicação</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-list-toolbar placeholder="Buscar por nome, código ou EAN...">
        <x-toolbar.group icon="bi-funnel">
            <x-toolbar.chip wire:click="$set('statusFilter', 'all')" :active="$statusFilter === 'all'">Todos</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'published')" :active="$statusFilter === 'published'" icon="bi-check-circle">Publicados</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('statusFilter', 'unpublished')" :active="$statusFilter === 'unpublished'" icon="bi-clock">Pendentes</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group>
            <span class="px-2 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap"><i class="bi bi-box-seam mr-1 text-amber-500"></i>{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }}</span>
        </x-toolbar.group>
    </x-list-toolbar>

    <x-filter-sheet title="Filtros de produtos" subtitle="Situação no ML e categoria" :active="$piFiltersOn" target="statusFilter,categoryFilter,clearFilters">
        <div class="fm-fs-group">
            <h4><i class="bi bi-flag text-amber-500"></i>Situação no Mercado Livre</h4>
            <div class="fm-fs-options">
                @foreach(['all' => ['Todos', 'bi-list-check'], 'published' => ['Publicados', 'bi-check-circle'], 'unpublished' => ['Pendentes', 'bi-clock']] as $key => [$label, $icon])
                    <button type="button" wire:click="$set('statusFilter', '{{ $key }}')" class="fm-fs-opt {{ $statusFilter === $key ? 'is-on' : '' }}"><i class="bi {{ $icon }}"></i>{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="fm-fs-group">
            <h4><i class="bi bi-tags text-indigo-500"></i>Categoria</h4>
            <select wire:model.live="categoryFilter">
                <option value="all">Todas as categorias</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fm-fs-group sm:hidden">
            <h4><i class="bi bi-three-dots text-slate-500"></i>Mais</h4>
            <div class="fm-fs-options">
                <a href="{{ route('mercadolivre.publications') }}" class="fm-fs-opt"><i class="bi bi-list-check"></i>Publicações</a>
                <a href="{{ route('mercadolivre.settings') }}" class="fm-fs-opt"><i class="bi bi-gear"></i>Configurações</a>
            </div>
        </div>
    </x-filter-sheet>

    {{-- ═══════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ═══════════════════════════════════════════════ --}}
    <div class="flex-1   pb-6">

        {{-- Alerta se não conectado --}}
        @if(!$isConnected)
            <div class="mb-6 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-950/50 dark:to-yellow-950/50 border-2 border-amber-200 dark:border-amber-800 shadow-xl">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center flex-shrink-0 shadow-lg">
                            <i class="bi bi-exclamation-triangle text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-2">Conexão Necessária</h3>
                            <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                                Conecte sua conta do Mercado Livre para publicar e gerenciar produtos.
                            </p>
                            <a href="{{ route('mercadolivre.settings') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                                <i class="bi bi-link-45deg text-lg"></i> Conectar Agora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════
             GRID DE PRODUTOS - Estilo Product-Index
        ═══════════════════════════════════════ --}}
        <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 gap-6">
            @forelse($products as $product)
                @php
                    $mlProduct = $product->mercadoLivreProduct;
                    $mlValidation = $product->isReadyForMercadoLivre();
                    $custo = (float)($product->price ?? 0);
                    $venda = (float)($product->price_sale ?? 0);
                    $margem = $custo > 0 ? (($venda - $custo) / $custo) * 100 : 0;
                    $firstPublication = $product->mlPublications->first();
                @endphp
                <div class="product-card-modern">
                    {{-- Área da imagem com badges e botões --}}
                    <div class="product-img-area" style="position: relative; overflow: visible;">
                        <img src="{{ $product->image_url }}" class="product-img" alt="{{ $product->name }}">

                        {{-- Código do produto - TOPO ESQUERDO --}}
                        <span class="badge-product-code" title="Código do Produto" style="top: 10px; left: 10px;">
                            <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                        </span>

                        {{-- Código de barras - ABAIXO DO PRODUCT CODE --}}
                        @if($product->barcode)
                            <span class="badge-product-code" title="Código de Barras (EAN)" style="top: 40px; left: 10px; padding: 4px 8px; font-size: 10px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-upc"></i> {{ $product->barcode }}
                            </span>
                        @else
                            <span class="badge-product-code" title="Sem código de barras" style="top: 40px; left: 10px; padding: 4px 8px; font-size: 10px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                <i class="bi bi-exclamation-triangle"></i> Sem EAN
                            </span>
                        @endif

                        {{-- Botão Publicações - ACIMA DO STATUS (só se tiver publicações) --}}
                        @if($product->mlPublications && $product->mlPublications->count() > 0)
                            <button 
                                wire:click="$dispatch('openPublicationsModal', { productId: {{ $product->id }} })" 
                                title="Ver Todas as Publicações ({{ $product->mlPublications->count() }})"
                                style="position: absolute; bottom: 55px; left: 10px; z-index: 20; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(to right, #6366f1, #8b5cf6); color: white; border-radius: 50%; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transition: all 0.2s; border: none; cursor: pointer;"
                                onmouseover="this.style.background='linear-gradient(to right, #4f46e5, #7c3aed)'"
                                onmouseout="this.style.background='linear-gradient(to right, #6366f1, #8b5cf6)'">
                                <i class="bi bi-list-ul" style="font-size: 14px;"></i>
                            </button>
                        @endif

                        {{-- Badge Status ML - INFERIOR ESQUERDO COM TEXTO --}}
                        @if($mlProduct)
                            @php
                                $statusConfig = match($mlProduct->status) {
                                    'active' => ['color' => 'from-green-500 to-green-600', 'icon' => 'check-circle-fill', 'text' => 'Ativo'],
                                    'paused' => ['color' => 'from-amber-500 to-amber-600', 'icon' => 'pause-circle-fill', 'text' => 'Pausado'],
                                    'closed' => ['color' => 'from-red-500 to-red-600', 'icon' => 'x-circle-fill', 'text' => 'Fechado'],
                                    'under_review' => ['color' => 'from-purple-500 to-purple-600', 'icon' => 'search', 'text' => 'Em Revisão'],
                                    'inactive' => ['color' => 'from-gray-500 to-gray-600', 'icon' => 'dash-circle', 'text' => 'Inativo'],
                                    default => ['color' => 'from-slate-500 to-slate-600', 'icon' => 'question-circle', 'text' => 'Desconhecido'],
                                };
                            @endphp
                            <div class="absolute bottom-3 left-3 z-10">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gradient-to-r {{ $statusConfig['color'] }} text-white shadow-lg border-2 border-white text-xs font-bold">
                                    <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['text'] }}
                                </span>
                            </div>
                        @else
                            @if($mlValidation['ready'])
                                <div class="absolute bottom-3 left-3 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg border-2 border-green-400 text-xs font-bold animate-pulse">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Pronto
                                    </span>
                                </div>
                            @else
                                <div class="absolute bottom-3 left-3 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-lg border-2 border-amber-400 text-xs font-bold">
                                        <i class="bi bi-clock"></i>
                                        Pendente
                                    </span>
                                </div>
                            @endif
                        @endif

                        {{-- Quantidade (inferior direito) --}}
                        <span class="badge-quantity" title="Quantidade em Estoque">
                            <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                        </span>

                        {{-- Ícone da categoria --}}
                        <div class="category-icon-wrapper">
                            <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                        </div>

                        {{-- Botões de Ação ML - LATERAL DIREITA --}}
                        <div class="absolute top-3 right-3 z-20 flex flex-col gap-2">
                            {{-- Botões Visualizar e Editar (sempre visíveis) --}}
                            <a href="{{ route('products.show', ['productCode' => $product->product_code, 'id' => $product->id]) }}" title="Ver Detalhes"
                               class="inline-flex items-center justify-center w-10 h-10 bg-purple-500 hover:bg-purple-600 text-white rounded-full shadow-lg transition-all">
                                <i class="bi bi-eye text-lg"></i>
                            </a>
                            <a href="{{ route('products.edit', $product->id) }}" title="Editar Produto"
                               class="inline-flex items-center justify-center w-10 h-10 bg-pink-500 hover:bg-pink-600 text-white rounded-full shadow-lg transition-all">
                                <i class="bi bi-pencil-square text-lg"></i>
                            </a>

                            @if($mlProduct)
                                {{-- PRODUTO PUBLICADO --}}
                                @if($mlProduct->status === 'active')
                                    <a href="{{ route('mercadolivre.products.publish', $product->id) }}" title="Nova Publicação"
                                       class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all">
                                        <i class="bi bi-plus-circle text-lg"></i>
                                    </a>
                                    <button wire:click="syncProduct({{ $product->id }})" wire:loading.attr="disabled" title="Sincronizar"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-arrow-repeat text-lg"></i>
                                    </button>
                                    <button wire:click="pauseProduct({{ $product->id }})" wire:confirm="Pausar?" title="Pausar"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-amber-500 hover:bg-amber-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-pause-circle text-lg"></i>
                                    </button>
                                @elseif($mlProduct->status === 'paused')
                                    <button wire:click="activateProduct({{ $product->id }})" wire:loading.attr="disabled" title="Reativar"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all">
                                        <i class="bi bi-play-circle text-lg"></i>
                                    </button>
                                    <a href="{{ route('mercadolivre.products.publish', $product->id) }}" title="Nova Publicação"
                                       class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-plus-circle text-lg"></i>
                                    </a>
                                    <button wire:click="checkMLStatus({{ $product->id }})" title="Verificar Status"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-search text-lg"></i>
                                    </button>
                                @elseif(in_array($mlProduct->status, ['closed', 'inactive']))
                                    <button wire:click="deleteLocalRecord({{ $product->id }})" wire:confirm="Excluir?" title="Excluir Registro"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-trash text-lg"></i>
                                    </button>
                                    <a href="{{ route('mercadolivre.products.publish', $product->id) }}" title="Publicar Novamente"
                                       class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-plus-circle text-lg"></i>
                                    </a>
                                @else
                                    <button wire:click="checkMLStatus({{ $product->id }})" title="Verificar Status"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-search text-lg"></i>
                                    </button>
                                    <button wire:click="deleteLocalRecord({{ $product->id }})" wire:confirm="Excluir?" title="Excluir Registro"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-trash text-lg"></i>
                                    </button>
                                @endif

                                @if($firstPublication)
                                    <a href="{{ route('mercadolivre.publications.edit', $firstPublication->id) }}" title="Editar Publicação"
                                       class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all">
                                        <i class="bi bi-box-seam text-lg"></i>
                                    </a>
                                @endif
                            @else
                                {{-- PRODUTO NÃO PUBLICADO --}}
                                <a href="{{ $mlValidation['ready'] ? route('mercadolivre.products.publish', $product->id) : '#' }}"
                                   @if(!$mlValidation['ready']) onclick="event.preventDefault();" @endif
                                   title="{{ $mlValidation['ready'] ? 'Publicar no ML' : 'Corrija as pendências' }}"
                                   class="inline-flex items-center justify-center w-10 h-10 rounded-full shadow-lg hover:shadow-xl transition-all {{ $mlValidation['ready'] ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white animate-pulse' : 'bg-slate-400/90 dark:bg-slate-700/90 text-slate-600 dark:text-slate-400 cursor-not-allowed backdrop-blur-sm' }}">
                                    <i class="bi bi-{{ $mlValidation['ready'] ? 'upload' : 'exclamation-circle' }} text-xl"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="card-body">
                        <div class="product-title" title="{{ $product->name }}">
                            {{ ucwords($product->name) }}
                        </div>

                        {{-- Área de preços --}}
                        <div class="price-area mt-3">
                            <div class="flex flex-col gap-2">
                                <span class="badge-price" title="Preço de Custo">
                                    <i class="bi bi-tag"></i>
                                    R$ {{ number_format($custo, 2, ',', '.') }}
                                </span>

                                <span class="badge-price-sale" title="Preço de Venda">
                                    <i class="bi bi-currency-dollar"></i>
                                    R$ {{ number_format($venda, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        @if($mlProduct && $mlProduct->error_message)
                            <div class="mt-3 p-2 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                                <p class="text-[10px] text-red-600 dark:text-red-400 truncate" title="{{ $mlProduct->error_message }}">
                                    <i class="bi bi-exclamation-triangle"></i> {{ \Illuminate\Support\Str::limit($mlProduct->error_message, 60) }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state icon="bi-box-seam" tone="amber" title="Nenhum produto encontrado" text="Ajuste os filtros ou adicione novos produtos com estoque.">
                        @if($statusFilter !== 'all' || $categoryFilter !== 'all' || $search !== '')
                            <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200"><i class="bi bi-eraser"></i>Limpar filtros</button>
                        @endif
                        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 shadow-md shadow-amber-500/25"><i class="bi bi-plus-lg"></i>Adicionar produto</a>
                    </x-empty-state>
                </div>
            @endforelse
        </div>

        {{-- Paginação Inferior --}}
        @if($products->hasPages())
        <div class="mt-6">
            <x-infinite-feed :paginator="$products" label="produtos" />
        </div>
        @endif
    </div>
</div>
