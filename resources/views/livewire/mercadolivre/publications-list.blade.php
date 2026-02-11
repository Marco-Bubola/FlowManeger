<div class="min-h-screen flex flex-col">

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    @endpush

    {{-- ═══════════════════════════════════════════════════════════
         HEADER ESTILO SALES-INDEX (search, filtros, paginação)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-purple-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-purple-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-indigo-400/20 to-blue-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

        <div class="relative px-4 py-3">
            {{-- Primeira Linha: Título + Badges + Controles --}}
            <div class="flex items-center justify-between gap-6">
                {{-- Esquerda: Ícone + Título + Stats --}}
                <div class="flex items-center gap-5">
                    <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-500 rounded-2xl shadow-xl shadow-purple-500/25">
                        <i class="bi bi-list-check text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                    </div>

                    <div class="space-y-1.5">
                        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <a href="{{ route('mercadolivre.products') }}" class="hover:text-purple-600 transition-colors">Mercado Livre</a>
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">Publicações</span>
                        </nav>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 dark:from-purple-300 dark:via-indigo-300 dark:to-blue-300 bg-clip-text text-transparent">
                            Publicações ML
                        </h1>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                <i class="bi bi-box-seam text-blue-600 dark:text-blue-400 text-xs"></i>
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $stats['total'] }} publicações</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-emerald-500/20 to-green-500/20 rounded-lg border border-emerald-200 dark:border-emerald-700">
                                <i class="bi bi-check-circle text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $stats['active'] }} ativas</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg border border-purple-200 dark:border-purple-700">
                                <i class="bi bi-boxes text-purple-600 dark:text-purple-400 text-xs"></i>
                                <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">{{ $stats['kits'] }} kits</span>
                            </div>
                            @if($stats['errors'] > 0)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-red-500/20 to-rose-500/20 rounded-lg border border-red-200 dark:border-red-700">
                                <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-xs"></i>
                                <span class="text-xs font-semibold text-red-700 dark:text-red-300">{{ $stats['errors'] }} erros</span>
                            </div>
                            @endif
                            @if(($stats['only_on_ml'] ?? 0) > 0)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r from-amber-500/20 to-orange-500/20 rounded-lg border border-amber-200 dark:border-amber-700">
                                <i class="bi bi-cloud-download text-amber-600 dark:text-amber-400 text-xs"></i>
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $stats['only_on_ml'] }} só no ML</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Direita: Pesquisa + Filtros + Paginação + Ações --}}
                <div class="flex items-center gap-3 flex-wrap justify-end">
                    {{-- Campo de Pesquisa --}}
                    <div class="relative group">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar publicações..."
                            class="w-56 pl-9 pr-8 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 transition-all duration-200 shadow-md text-sm">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-search text-slate-400 group-focus-within:text-purple-500 transition-colors text-sm"></i>
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
                        <div class="w-7 h-7 bg-gradient-to-br from-purple-400 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="bi bi-list-check text-white text-xs"></i>
                        </div>
                        <div class="text-sm">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $publications->total() }}</span>
                            <span class="text-slate-600 dark:text-slate-400 ml-1">{{ $publications->total() === 1 ? 'publicação' : 'publicações' }}</span>
                        </div>
                    </div>

                    {{-- Filtros Rápidos - Status --}}
                    <div class="flex items-center gap-1.5">
                        <button wire:click="$set('statusFilter', 'all')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'all' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            Todos
                        </button>
                        <button wire:click="$set('statusFilter', 'active')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'active' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-700 hover:bg-green-200 dark:hover:bg-green-800' }}">
                            Ativas
                        </button>
                        <button wire:click="$set('statusFilter', 'paused')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $statusFilter === 'paused' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700 hover:bg-yellow-200 dark:hover:bg-yellow-800' }}">
                            Pausadas
                        </button>
                    </div>

                    {{-- Filtro Tipo --}}
                    <select wire:model.live="typeFilter"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 shadow-md">
                        <option value="all">📦 Todos Tipos</option>
                        <option value="simple">Simples</option>
                        <option value="kit">Kit</option>
                    </select>

                    {{-- Filtro Sync --}}
                    <select wire:model.live="syncFilter"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 shadow-md">
                        <option value="all">🔄 Todos Status</option>
                        <option value="synced">Sincronizado</option>
                        <option value="pending">Pendente</option>
                        <option value="error">Erro</option>
                    </select>

                    {{-- Paginação Compacta --}}
                    @if($publications->hasPages())
                    <div class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl p-1 shadow-md">
                        @if($publications->currentPage() > 1)
                        <button wire:click.prevent="previousPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                            <i class="bi bi-chevron-left text-sm text-slate-600 dark:text-slate-300"></i>
                        </button>
                        @endif
                        <span class="px-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                            {{ $publications->currentPage() }} / {{ $publications->lastPage() }}
                        </span>
                        @if($publications->hasMorePages())
                        <button wire:click.prevent="nextPage" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all">
                            <i class="bi bi-chevron-right text-sm text-slate-600 dark:text-slate-300"></i>
                        </button>
                        @endif
                    </div>
                    @endif

                    {{-- Toggle Visualização --}}
                    <div class="flex items-center gap-1 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 rounded-xl p-1 shadow-md">
                        <button wire:click="$set('viewMode', 'cards')" 
                                title="Visualizar em Cards"
                                class="p-1.5 rounded-lg transition-all {{ $viewMode === 'cards' ? 'bg-purple-500 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <i class="bi bi-grid-3x3-gap text-sm"></i>
                        </button>
                        <button wire:click="$set('viewMode', 'table')" 
                                title="Visualizar em Tabela"
                                class="p-1.5 rounded-lg transition-all {{ $viewMode === 'table' ? 'bg-purple-500 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <i class="bi bi-list-ul text-sm"></i>
                        </button>
                    </div>

                    {{-- Botão Nova Publicação --}}
                    <a href="{{ route('mercadolivre.products') }}"
                       class="flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 text-sm">
                        <i class="bi bi-plus-circle"></i>
                        <span>Nova Publicação</span>
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
         INDICADOR DE SINCRONIZAÇÃO AUTOMÁTICA
    ═══════════════════════════════════════════════ --}}
    @if($isSyncing || $syncedCount > 0)
    <div class="mb-6 rounded-2xl overflow-hidden shadow-xl border-2" 
         x-data="{ show: true }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="{{ $isSyncing ? 'border-blue-400 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-950/30 dark:to-cyan-950/30' : ($syncedCount > 0 && count($syncErrors) === 0 ? 'border-emerald-400 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/30' : 'border-amber-400 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/30') }}">
        <div class="p-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    @if($isSyncing)
                        {{-- Em sincronização --}}
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                            <i class="bi bi-arrow-repeat text-white text-xl animate-spin"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-blue-700 dark:text-blue-300 mb-1">
                                🔄 Sincronizando com o Mercado Livre...
                            </h3>
                            <p class="text-sm text-blue-600 dark:text-blue-400">
                                {{ $syncedCount }} de {{ $totalToSync }} publicações sincronizadas
                            </p>
                            {{-- Barra de Progresso --}}
                            @if($totalToSync > 0)
                            <div class="mt-2 w-full bg-blue-200 dark:bg-blue-900 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-500" 
                                     style="width: {{ ($syncedCount / $totalToSync) * 100 }}%"></div>
                            </div>
                            @endif
                        </div>
                    @else
                        {{-- Sincronização concluída --}}
                        @if(count($syncErrors) === 0)
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center">
                                <i class="bi bi-check-circle-fill text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                    ✅ Sincronização Concluída!
                                </h3>
                                <p class="text-sm text-emerald-600 dark:text-emerald-400">
                                    {{ $syncedCount }} de {{ $totalToSync }} publicações atualizadas com sucesso
                                </p>
                            </div>
                        @else
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center">
                                <i class="bi bi-exclamation-triangle-fill text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-amber-700 dark:text-amber-300">
                                    ⚠️ Sincronização Concluída com Avisos
                                </h3>
                                <p class="text-sm text-amber-600 dark:text-amber-400 mb-2">
                                    {{ $syncedCount }} sincronizadas · {{ count($syncErrors) }} com erro
                                </p>
                                {{-- Lista de erros (colapsável) --}}
                                @if(count($syncErrors) > 0)
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs font-semibold text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200">
                                        Ver erros ({{ count($syncErrors) }})
                                    </summary>
                                    <div class="mt-2 space-y-1 max-h-40 overflow-y-auto">
                                        @foreach($syncErrors as $error)
                                        <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-800">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $error['title'] }}</p>
                                            <p class="text-xs text-red-600 dark:text-red-400">{{ $error['error'] }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </details>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
                
                {{-- Botão Fechar --}}
                @if(!$isSyncing)
                <button @click="show = false" 
                        class="flex-shrink-0 p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <i class="bi bi-x-lg text-slate-600 dark:text-slate-400"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════
         CONTEÚDO PRINCIPAL - GRID DE PUBLICAÇÕES
    ═══════════════════════════════════════════════ --}}
    <div class="flex-1 pb-6">
        
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

                    <div class="product-card-modern">
                        {{-- Área das IMAGENS DOS PRODUTOS --}}
                        <div class="product-img-area" style="height: 330px; position: relative; overflow: hidden; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); display: flex; align-items: center; justify-content: center; padding: 15px;">
                            
                            @if($publication->products->count() === 1)
                                {{-- UM PRODUTO: Imagem Grande --}}
                                @php $singleProduct = $publication->products->first(); @endphp
                                @if($singleProduct->image && $singleProduct->image !== 'product-placeholder.png')
                                    <img src="{{ asset('storage/products/' . $singleProduct->image) }}" 
                                         alt="{{ $singleProduct->name }}" 
                                         class="w-full h-full object-contain rounded-2xl shadow-2xl"
                                         style="max-width: 280px; max-height: 300px;">
                                @else
                                    <div class="w-64 h-64 bg-slate-200/50 rounded-2xl flex items-center justify-center border-4 border-slate-300">
                                        <i class="bi bi-image text-slate-400 text-6xl"></i>
                                    </div>
                                @endif
                            @elseif($publication->products->count() === 2)
                                {{-- DOIS PRODUTOS: Grid 1x2 --}}
                                <div class="grid grid-cols-2 gap-3 w-full h-full p-2">
                                    @foreach($publication->products as $product)
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <div class="relative group overflow-hidden rounded-xl border-2 border-slate-200 shadow-lg bg-white">
                                                <img src="{{ asset('storage/products/' . $product->image) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300">
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center bg-slate-200/50 rounded-xl border-2 border-slate-300">
                                                <i class="bi bi-image text-slate-400 text-4xl"></i>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif($publication->products->count() === 3)
                                {{-- TRÊS PRODUTOS: 1 grande + 2 pequenos --}}
                                <div class="grid grid-cols-2 grid-rows-2 gap-3 w-full h-full p-2">
                                    @foreach($publication->products as $index => $product)
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <div class="relative group overflow-hidden rounded-xl border-2 border-slate-200 shadow-lg bg-white {{ $index === 0 ? 'row-span-2' : '' }}">
                                                <img src="{{ asset('storage/products/' . $product->image) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300">
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center bg-slate-200/50 rounded-xl border-2 border-slate-300 {{ $index === 0 ? 'row-span-2' : '' }}">
                                                <i class="bi bi-image text-slate-400 text-4xl"></i>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                {{-- QUATRO OU MAIS: Grid 2x2 --}}
                                <div class="grid grid-cols-2 grid-rows-2 gap-3 w-full h-full p-2">
                                    @foreach($publication->products->take(4) as $product)
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <div class="relative group overflow-hidden rounded-xl border-2 border-slate-200 shadow-lg bg-white">
                                                <img src="{{ asset('storage/products/' . $product->image) }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300">
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center bg-slate-200/50 rounded-xl border-2 border-slate-300">
                                                <i class="bi bi-image text-slate-400 text-3xl"></i>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                
                                {{-- Badge de mais produtos --}}
                                @if($publication->products->count() > 4)
                                    <div class="absolute bottom-16 right-4 z-10">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-black border-2 border-white shadow-2xl">
                                            <i class="bi bi-plus-circle-fill"></i>
                                            +{{ $publication->products->count() - 4 }}
                                        </span>
                                    </div>
                                @endif
                            @endif

                            {{-- ML Item ID - TOPO ESQUERDO --}}
                            @if($publication->ml_item_id)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-black shadow-lg border-2 border-white" 
                                      style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); color: white;"
                                      title="ID Mercado Livre">
                                    <i class="bi bi-tag-fill"></i> {{ substr($publication->ml_item_id, 0, 12) }}...
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-black shadow-lg border-2 border-white" 
                                      style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;"
                                      title="Não Publicado">
                                    <i class="bi bi-clock"></i> Não Publicado
                                </span>
                            @endif

                            {{-- Tipo - ABAIXO DO ML ID --}}
                            @php
                                $typeConfig = $publication->publication_type === 'kit' 
                                    ? ['bg' => 'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)', 'icon' => 'boxes', 'text' => 'Kit']
                                    : ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', 'icon' => 'box', 'text' => 'Simples'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-black shadow-lg border-2 border-white" 
                                  style="position: absolute; top: 40px; left: 10px; background: {{ $typeConfig['bg'] }}; color: white;"
                                  title="Tipo de Publicação">
                                <i class="bi bi-{{ $typeConfig['icon'] }}"></i> 
                                {{ $typeConfig['text'] }}
                            </span>

                            {{-- Status ML - INFERIOR ESQUERDO --}}
                            <div class="absolute bottom-3 left-3 z-10">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r {{ $statusConfig['color'] }} text-white shadow-xl border-2 border-white text-xs font-bold">
                                    <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['text'] }}
                                </span>
                            </div>

                            {{-- Quantidade Disponível (inferior direito) --}}
                            <div class="absolute bottom-3 right-3 z-10">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-slate-700 to-slate-900 text-white shadow-xl border-2 border-white text-xs font-bold">
                                    <i class="bi bi-stack"></i> {{ $availableQty }}
                                </span>
                            </div>

                            {{-- Badge Sync Status - ACIMA DO STATUS --}}
                            <div class="absolute bottom-14 left-3 z-10">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gradient-to-r {{ $syncConfig['color'] }} text-white shadow-xl border-2 border-white text-[10px] font-bold">
                                    <i class="bi bi-{{ $syncConfig['icon'] }}"></i>
                                    {{ $syncConfig['text'] }}
                                </span>
                            </div>

                            {{-- Botões de Ação - LATERAL DIREITA --}}
                            <div class="absolute top-3 right-3 z-20 flex flex-col gap-2">
                                {{-- Ver no ML (se publicado) --}}
                                @if($publication->ml_permalink)
                                    <a href="{{ $publication->ml_permalink }}" 
                                       target="_blank"
                                       title="Ver no Mercado Livre"
                                       class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-box-arrow-up-right text-lg"></i>
                                    </a>
                                @endif
                                
                                {{-- Ver Publicação (sempre visível) --}}
                                <a href="{{ route('mercadolivre.publications.show', $publication->id) }}" 
                                   title="Ver Publicação Completa"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-full shadow-lg transition-all">
                                    <i class="bi bi-eye text-lg"></i>
                                </a>
                            {{-- Editar Publicação --}}
                            @if($publication->ml_item_id)
                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}" 
                                   title="Editar Publicação"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-full shadow-lg transition-all">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a>
                            @else
                                {{-- Publicar --}}
                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()->id) }}" 
                                   title="Publicar no ML"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-full shadow-lg transition-all animate-pulse">
                                    <i class="bi bi-upload text-lg"></i>
                                </a>
                            @endif

                            {{-- Sincronizar (se publicado) --}}
                            @if($publication->ml_item_id)
                                <button wire:click="syncPublication({{ $publication->id }})" 
                                        wire:loading.attr="disabled"
                                        title="Sincronizar com ML"
                                        class="inline-flex items-center justify-center w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg transition-all">
                                    <i class="bi bi-arrow-repeat text-lg"></i>
                                </button>
                            @endif

                            {{-- Pausar/Ativar --}}
                            @if($publication->ml_item_id)
                                @if($publication->status === 'active')
                                    <button wire:click="pausePublication({{ $publication->id }})" 
                                            wire:confirm="Pausar esta publicação?"
                                            title="Pausar Publicação"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-amber-500 hover:bg-amber-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-pause-circle text-lg"></i>
                                    </button>
                                @elseif($publication->status === 'paused')
                                    <button wire:click="activatePublication({{ $publication->id }})" 
                                            title="Ativar Publicação"
                                            class="inline-flex items-center justify-center w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg transition-all">
                                        <i class="bi bi-play-circle text-lg"></i>
                                    </button>
                                @endif
                            @endif

                            {{-- Excluir/Cancelar --}}
                            <button wire:click="deletePublication({{ $publication->id }})" 
                                    wire:confirm="Tem certeza que deseja excluir esta publicação?"
                                    title="Excluir Publicação"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-all">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Conteúdo do Card --}}
                    <div class="card-body">
                        {{-- Título da Publicação --}}
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-3 line-clamp-2 leading-tight min-h-[2.5rem]" title="{{ $publication->title }}">
                            {{ $publication->title }}
                        </h3>

                        {{-- Preço e Quantidade em destaque --}}
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mb-1 font-semibold">PREÇO</p>
                                <span class="text-2xl font-black bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                                    R$ {{ number_format($publication->price, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mb-1 font-semibold">DISPONÍVEL</p>
                                <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 border border-purple-300 dark:border-purple-700">
                                    <i class="bi bi-stack text-purple-600 dark:text-purple-400"></i>
                                    <span class="text-lg font-black text-purple-700 dark:text-purple-300">{{ $availableQty }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Info Rápida --}}
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                                <i class="bi bi-box-seam"></i>
                                {{ $publication->products->count() }} {{ $publication->products->count() === 1 ? 'produto' : 'produtos' }}
                            </span>
                            
                            @if($publication->ml_item_id)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Publicado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-semibold">
                                    <i class="bi bi-clock"></i>
                                    Pendente
                                </span>
                            @endif
                        </div>

                        {{-- Mensagem de Erro --}}
                        @if($publication->error_message)
                            <div class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                                <p class="text-[10px] text-red-600 dark:text-red-400 truncate" title="{{ $publication->error_message }}">
                                    <i class="bi bi-exclamation-triangle"></i> {{ \Illuminate\Support\Str::limit($publication->error_message, 50) }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                {{-- Estado Vazio --}}
                <div class="col-span-full">
                    <div class="rounded-2xl bg-slate-100 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-inbox text-4xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                            @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                                Ajuste os filtros ou pesquise novamente
                            @else
                                Crie sua primeira publicação no Mercado Livre
                            @endif
                        </p>
                        <a href="{{ route('mercadolivre.products') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                            <i class="bi bi-plus-lg"></i> Nova Publicação
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
        
        @else
            {{-- VISUALIZAÇÃO EM TABELA --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/50 dark:to-indigo-950/50 border-b-2 border-purple-200 dark:border-purple-800">
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Publicação
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Produtos
                                </th>
                                <th class="px-4 py-4 text-right text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Preço
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                                    Estoque
                                </th>
                                <th class="px-4 py-4 text-center text-xs font-black text-purple-900 dark:text-purple-100 uppercase tracking-wider">
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
                                               class="p-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-all">
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
                                        <div class="text-center">
                                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                                                <i class="bi bi-inbox text-4xl text-slate-400"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                                @if($search || $statusFilter !== 'all' || $typeFilter !== 'all' || $syncFilter !== 'all')
                                                    Ajuste os filtros ou pesquise novamente
                                                @else
                                                    Crie sua primeira publicação no Mercado Livre
                                                @endif
                                            </p>
                                            <a href="{{ route('mercadolivre.products') }}"
                                               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                                                <i class="bi bi-plus-lg"></i> Nova Publicação
                                            </a>
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
            {{ $publications->links() }}
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
</div>
