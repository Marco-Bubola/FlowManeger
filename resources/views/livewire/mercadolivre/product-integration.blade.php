<div class="min-h-screen flex flex-col ">

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    {{-- Modal de Publicações do Produto --}}
    @livewire('mercado-livre.product-publications-modal')

    {{-- ═══════════════════════════════════════════════════════════
         HEADER ESTILO SALES-INDEX (search, filtros, paginação)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-yellow-400/20 via-amber-400/20 to-orange-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

        <div class="relative px-4 py-3">
            {{-- Primeira Linha: Título + Badges + Controles --}}
            <div class="flex items-center justify-between gap-6">
                {{-- Esquerda: Ícone + Título + Stats --}}
                <div class="flex items-center gap-5">
                    <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 rounded-2xl shadow-xl shadow-yellow-500/25">
                        <i class="bi bi-shop text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                    </div>

                    <div class="space-y-1.5">
                        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">Mercado Livre</span>
                        </nav>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-yellow-600 via-amber-600 to-orange-600 dark:from-yellow-300 dark:via-amber-300 dark:to-orange-300 bg-clip-text text-transparent">
                            Integração ML
                        </h1>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-lg border border-emerald-200 dark:border-emerald-700">
                                <i class="bi bi-box-seam text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $this->totalProducts ?? 0 }} produtos</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                <i class="bi bi-check-circle text-blue-600 dark:text-blue-400 text-xs"></i>
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $this->publishedCount ?? 0 }} publicados</span>
                            </div>
                            @if(($this->pendingCount ?? 0) > 0)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                <i class="bi bi-clock text-yellow-600 dark:text-yellow-400 text-xs"></i>
                                <span class="text-xs font-semibold text-yellow-700 dark:text-yellow-300">{{ $this->pendingCount }} pendentes</span>
                            </div>
                            @endif
                            @if($isConnected)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    Conectado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-xs font-semibold">
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                    Desconectado
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Direita: Pesquisa + Filtros + Paginação + Ações --}}
                <div class="flex items-center gap-3 flex-wrap justify-end">
                    {{-- Campo de Pesquisa --}}
                    <div class="relative group">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar produtos..."
                            class="w-56 pl-9 pr-8 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500 transition-all duration-200 shadow-md text-sm">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-search text-slate-400 group-focus-within:text-yellow-500 transition-colors text-sm"></i>
                        </div>
                        @if($search)
                        <button wire:click="$set('search', '')"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-0.5 bg-slate-200 hover:bg-red-500 dark:bg-slate-600 dark:hover:bg-red-500 text-slate-600 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-md transition-all duration-200">
                            <i class="bi bi-x text-xs"></i>
                        </button>
                        @endif
                    </div>

                    {{-- Contador --}}
                    <div class="flex items-center gap-2 px-3 py-2 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl shadow-md">
                        <div class="w-7 h-7 bg-gradient-to-br from-yellow-400 to-amber-600 rounded-lg flex items-center justify-center">
                            <i class="bi bi-box-seam text-white text-xs"></i>
                        </div>
                        <div class="text-sm">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $products->total() }}</span>
                            <span class="text-slate-600 dark:text-slate-400 ml-1">{{ $products->total() === 1 ? 'produto' : 'produtos' }}</span>
                        </div>
                    </div>

                    {{-- Filtros Rápidos --}}
                    <div class="flex items-center gap-1.5">
                        <button wire:click="$set('statusFilter', 'all')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'all' ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            Todos
                        </button>
                        <button wire:click="$set('statusFilter', 'published')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'published' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-700 hover:bg-green-200 dark:hover:bg-green-800' }}">
                            Publicados
                        </button>
                        <button wire:click="$set('statusFilter', 'unpublished')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'unpublished' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700 hover:bg-yellow-200 dark:hover:bg-yellow-800' }}">
                            Pendentes
                        </button>
                    </div>

                    {{-- Filtro Categoria --}}
                    <select wire:model.live="categoryFilter"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500 shadow-md">
                        <option value="all">📂 Categorias</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    {{-- Paginação Compacta --}}
                    @if($products->hasPages())
                    <div class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl p-1 shadow-md">
                        @if($products->currentPage() > 1)
                        <button wire:click.prevent="previousPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                            <i class="bi bi-chevron-left text-sm text-slate-600 dark:text-slate-300"></i>
                        </button>
                        @endif
                        <span class="px-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                            {{ $products->currentPage() }} / {{ $products->lastPage() }}
                        </span>
                        @if($products->hasMorePages())
                        <button wire:click.prevent="nextPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                            <i class="bi bi-chevron-right text-sm text-slate-600 dark:text-slate-300"></i>
                        </button>
                        @endif
                    </div>
                    @endif

                    {{-- Botão Criar Publicação --}}
                    <a href="{{ route('mercadolivre.products.publish.create') }}"
                       class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-yellow-500 via-amber-500 to-orange-500 hover:from-yellow-600 hover:via-amber-600 hover:to-orange-600 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 text-sm">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Criar Publicação</span>
                    </a>

                    {{-- Botão Publicações --}}
                    <a href="{{ route('mercadolivre.publications') }}"
                       class="flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 text-sm">
                        <i class="bi bi-list-check"></i>
                        <span>Publicações</span>
                    </a>

                    {{-- Botão Configurações --}}
                    <a href="{{ route('mercadolivre.settings') }}"
                       class="p-2 bg-white/80 hover:bg-slate-100 dark:bg-slate-800/80 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl transition-all duration-200 shadow-md">
                        <i class="bi bi-gear text-slate-600 dark:text-slate-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                    <div class="product-img-area" style="height: 330px; position: relative; overflow: visible !important;">
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
                            <a href="{{ route('products.show', $product->product_code) }}" title="Ver Detalhes"
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
                    <div class="rounded-2xl bg-slate-100 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-inbox text-4xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhum produto encontrado</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Ajuste os filtros ou adicione novos produtos</p>
                        <a href="{{ route('products.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                            <i class="bi bi-plus-lg"></i> Adicionar Produto
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginação Inferior --}}
        @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
