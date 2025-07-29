<div class="flex h-full w-full flex-1 flex-col gap-3  p-3" x-data="{ showFilters: false, showQuickActions: false }">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-compact.css') }}">



    <!-- Header Moderno com Métricas e Ações -->
    <div class="relative overflow-hidden shadow-xl border rounded-3xl mb-6 bg-gradient-to-br from-white via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900">
        <!-- Background decorativo -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400 via-blue-400 to-indigo-400 rounded-full transform translate-x-16 -translate-y-16 opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400 via-blue-400 to-purple-400 rounded-full transform -translate-x-10 translate-y-10 opacity-10"></div>

        <div class="relative p-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <!-- Título Moderno -->
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl text-white shadow-lg">
                        <i class="bi bi-boxes text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 via-indigo-800 to-purple-700 dark:from-white dark:via-indigo-200 dark:to-purple-300 bg-clip-text text-transparent">
                            Catálogo de Produtos
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 text-lg">
                            {{ $products->total() ?? 0 }} produtos • {{ $categories->count() }} categorias
                        </p>
                    </div>
                </div>

                <!-- Painel de Métricas Rápidas e Informações Extras -->
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-check-circle"></i> Ativos: {{ $products->where('status', 'ativo')->count() }}
                    </div>
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-slash-circle"></i> Inativos: {{ $products->where('status', 'inativo')->count() }}
                    </div>
                    <div class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-x-circle"></i> Descontinuados: {{ $products->where('status', 'descontinuado')->count() }}
                    </div>
                    <div class="px-4 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle"></i> Estoque Baixo: {{ $products->where('stock_quantity', '<=', 5)->count() }}
                    </div>
                    <div class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-box"></i> Kits: {{ $products->where('tipo', 'kit')->count() }}
                    </div>
                    <div class="px-4 py-2 bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-image"></i> Sem Imagem: {{ $products->whereNull('image')->count() + $products->where('image', '')->count() }}
                    </div>
                    <div class="px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-stack"></i> Total Estoque: {{ $products->sum('stock_quantity') }}
                    </div>
                    <div class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-currency-dollar"></i> Valor Catálogo: R$ {{ number_format($products->sum('price_sale'), 2, ',', '.') }}
                    </div>
                </div>

                <!-- Painel de Ações Rápidas -->
                <div class="flex flex-wrap gap-2 items-center">
                    <button @click="showQuickActions = !showQuickActions"
                        class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        Novo Produto
                    </button>
                    <a href="{{ route('products.upload') }}"
                        class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        Upload
                    </a>
                    <button @click="showFilters = !showFilters"
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2"
                        :class="{'bg-gradient-to-r from-blue-700 to-indigo-700': showFilters}">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="hidden sm:inline">Filtros</span>
                        @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
                        <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Cards de estatísticas extras -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 my-8">
                <!-- Produtos sem estoque -->
                <div class="bg-gradient-to-br from-pink-100 to-purple-200 dark:from-pink-900 dark:to-purple-900 rounded-xl p-6 shadow flex flex-col items-center">
                    <i class="bi bi-x-circle text-2xl text-red-500 mb-2"></i>
                    <div class="font-bold text-lg">Produtos sem estoque</div>
                    <div class="text-2xl text-red-600 font-bold">{{ $products->where('stock_quantity', 0)->count() }}</div>
                </div>
                <!-- Produto mais vendido -->
                <div class="bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900 dark:to-emerald-900 rounded-xl p-6 shadow flex flex-col items-center">
                    <i class="bi bi-cart-check text-2xl text-green-600 mb-2"></i>
                    <div class="font-bold text-lg">Mais vendido</div>
                    @php
                        $maisVendido = $products->sortByDesc('sales_count')->first();
                    @endphp
                    <div class="text-xl text-green-700 font-bold">{{ $maisVendido?->name ?? '-' }}</div>
                    <div class="text-xs text-neutral-500">Vendas: {{ $maisVendido?->sales_count ?? 0 }}</div>
                </div>
                <!-- Produto com maior margem -->
                <div class="bg-gradient-to-br from-yellow-100 to-orange-200 dark:from-yellow-900 dark:to-orange-900 rounded-xl p-6 shadow flex flex-col items-center">
                    <i class="bi bi-graph-up-arrow text-2xl text-yellow-600 mb-2"></i>
                    <div class="font-bold text-lg">Maior margem</div>
                    @php
                        $maiorMargem = $products->sortByDesc('margin')->first();
                    @endphp
                    <div class="text-xl text-yellow-700 font-bold">{{ $maiorMargem?->name ?? '-' }}</div>
                    <div class="text-xs text-neutral-500">Margem: {{ isset($maiorMargem->margin) ? number_format($maiorMargem->margin, 1, ',', '.') . '%' : '-' }}</div>
                </div>
                <!-- Total de vendas do mês -->
                <div class="bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-blue-900 dark:to-indigo-900 rounded-xl p-6 shadow flex flex-col items-center">
                    <i class="bi bi-calendar-event text-2xl text-blue-600 mb-2"></i>
                    <div class="font-bold text-lg">Vendas no mês</div>
                    @php
                        $totalVendasMes = $products->sum(function($p){
                            return isset($p->sales_this_month) ? $p->sales_this_month : 0;
                        });
                    @endphp
                    <div class="text-2xl text-blue-700 font-bold">{{ $totalVendasMes }}</div>
                </div>
            </div>

            <!-- Ações rápidas visíveis -->
            <div class="flex flex-wrap gap-4 mb-8 justify-center">
                <a href="{{ route('products.create') }}" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-plus-square"></i> Novo Produto
                </a>
                <a href="{{ route('products.kit.create') }}" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-boxes"></i> Novo Kit
                </a>
                <a href="#" class="px-6 py-3 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                </a>
                <a href="#" class="px-6 py-3 bg-gradient-to-r from-purple-400 to-purple-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
                </a>
                <a href="#" class="px-6 py-3 bg-gradient-to-r from-orange-400 to-orange-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-graph-up-arrow"></i> Relatórios
                </a>
                <a href="#" class="px-6 py-3 bg-gradient-to-r from-pink-400 to-pink-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-files"></i> Duplicar Produto
                </a>
            </div>

            <!-- Tabela de produtos em destaque -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow border border-neutral-200 dark:border-neutral-700 p-6 mb-8">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-star text-yellow-500"></i> Produtos em Destaque</h3>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-neutral-100 dark:bg-neutral-700">
                            <th class="p-2 text-left">Produto</th>
                            <th class="p-2 text-left">Código</th>
                            <th class="p-2 text-left">Estoque</th>
                            <th class="p-2 text-left">Preço Venda</th>
                            <th class="p-2 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products->sortByDesc('stock_quantity')->take(5) as $product)
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="p-2">{{ $product->name }}</td>
                            <td class="p-2">{{ $product->product_code }}</td>
                            <td class="p-2">{{ $product->stock_quantity }}</td>
                            <td class="p-2">R$ {{ number_format($product->price_sale, 2, ',', '.') }}</td>
                            <td class="p-2 flex gap-2">
                                <a href="{{ route('products.show', $product->product_code) }}" class="text-blue-600 dark:text-blue-400"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('products.edit', $product) }}" class="text-green-600 dark:text-green-400"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" wire:click="confirmDelete({{ $product->id }})" class="text-red-600 dark:text-red-400"><i class="bi bi-trash3"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Dicas e alertas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                @if($products->whereNull('image')->count() + $products->where('image', '')->count())
                <div class="bg-pink-100 dark:bg-pink-900 rounded-xl p-4 flex items-center gap-3">
                    <i class="bi bi-image text-pink-600 text-2xl"></i>
                    <div>
                        <div class="font-bold">Produtos sem imagem</div>
                        <div class="text-sm">{{ $products->whereNull('image')->count() + $products->where('image', '')->count() }} produtos estão sem imagem cadastrada.</div>
                    </div>
                </div>
                @endif
                @if($products->where('price_sale', 0)->count())
                <div class="bg-yellow-100 dark:bg-yellow-900 rounded-xl p-4 flex items-center gap-3">
                    <i class="bi bi-currency-dollar text-yellow-600 text-2xl"></i>
                    <div>
                        <div class="font-bold">Produtos com preço zerado</div>
                        <div class="text-sm">{{ $products->where('price_sale', 0)->count() }} produtos estão com preço de venda zerado.</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Botões de ação em massa -->
            <div class="flex flex-wrap gap-4 mb-8 justify-center">
                <button class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-trash3"></i> Excluir Selecionados
                </button>
                <button class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-pencil-square"></i> Editar em Massa
                </button>
                <button class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-check-circle"></i> Ativar Selecionados
                </button>
                <button class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-700 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 hover:scale-105 transition">
                    <i class="bi bi-slash-circle"></i> Inativar Selecionados
                </button>
            </div>
        </div>
    </div>


    <!-- Barra de Pesquisa Rápida -->
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-3 mb-4">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-neutral-400 text-lg"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="🔍 Pesquisar produtos..."
                class="w-full pl-10 pr-10 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder:text-neutral-400">

            @if($search)
            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-red-500 transition-colors duration-200">
                <i class="bi bi-x-circle text-lg"></i>
            </button>
            @endif
        </div>
        <!-- Sugestões rápidas e histórico -->
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs">Sugestão: Estoque baixo</span>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Sugestão: Sem imagem</span>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Histórico: Kit</span>
            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Histórico: Exportação</span>
        </div>
    </div>

    <!-- Seção de filtros escondida -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i class="bi bi-funnel-fill text-purple-500"></i>
                <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Filtros Avançados</h3>
            </div>

            <!-- Indicador de filtros ativos -->
            @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <i class="bi bi-filter-circle mr-1"></i>
                    Filtros ativos
                </span>
                <button wire:click="clearFilters" class="text-neutral-500 hover:text-red-500 transition-colors duration-200" title="Limpar todos os filtros">
                    <i class="bi bi-x-circle text-lg"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Grid de filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Itens por página -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-eye mr-1"></i>
                    Itens por página
                </label>
                <select wire:model.live="perPage" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="12">12 itens</option>
                    <option value="24">24 itens</option>
                    <option value="36">36 itens</option>
                    <option value="48">48 itens</option>
                </select>
                <div class="mt-1 text-xs text-neutral-500">Total: {{ $products->total() }}</div>
            </div>

            <!-- Ordenação -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-sort-alpha-down mr-1"></i>
                    Ordenar por
                </label>
                <select wire:model.live="ordem" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📅 Padrão</option>
                    <option value="recentes">🆕 Mais Recentes</option>
                    <option value="antigas">📜 Mais Antigas</option>
                    <option value="az">🔤 A-Z</option>
                    <option value="za">🔤 Z-A</option>
                    <option value="preco_asc">💰 Menor Preço</option>
                    <option value="preco_desc">💎 Maior Preço</option>
                    <option value="estoque_asc">📦 Menor Estoque</option>
                    <option value="estoque_desc">📈 Maior Estoque</option>
                </select>
            </div>

            <!-- Categoria -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-tags mr-1"></i>
                    Categoria
                </label>
                <select wire:model.live="category" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">🏷️ Todas</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id_category }}">{{ $cat->name }} ({{ $products->where('category_id', $cat->id_category)->count() }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-activity mr-1"></i>
                    Status
                </label>
                <select wire:model.live="status_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📊 Todos</option>
                    <option value="ativo">✅ Ativo</option>
                    <option value="inativo">⏸️ Inativo</option>
                    <option value="descontinuado">❌ Descontinuado</option>
                </select>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-box-seam mr-1"></i>
                    Tipo
                </label>
                <select wire:model.live="tipo" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">🔄 Todos</option>
                    <option value="simples">📦 Simples</option>
                    <option value="kit">📦📦 Kit</option>
                </select>
            </div>

            <!-- Preço Mínimo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-currency-dollar mr-1"></i>
                    Preço Mínimo
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-neutral-500">R$</span>
                    <input type="number" wire:model.live="preco_min" placeholder="0,00" step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Preço Máximo -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-currency-dollar mr-1"></i>
                    Preço Máximo
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-neutral-500">R$</span>
                    <input type="number" wire:model.live="preco_max" placeholder="∞" step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Estoque -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-boxes mr-1"></i>
                    Estoque
                </label>
                <select wire:model.live="estoque_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📦 Todos</option>
                    <option value="disponivel">✅ Disponível (>5)</option>
                    <option value="baixo">⚠️ Baixo (1-5)</option>
                    <option value="zerado">❌ Zerado (0)</option>
                </select>
            </div>

            <!-- Filtro por Data -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    <i class="bi bi-calendar mr-1"></i>
                    Criado em
                </label>
                <select wire:model.live="data_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                    <option value="">📅 Todos os períodos</option>
                    <option value="hoje">📅 Hoje</option>
                </select>
            </div>

            <!-- Filtro por Margem de Lucro -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                </label>
                <select wire:model.live="margem_filtro" class="w-full p-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500">
                </select>
            </div>
        </div>

        <!-- Botão limpar filtros -->
        <div class="mt-4 flex justify-between items-center">
            <button wire:click="clearFilters"
                class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                🗑️ Limpar Filtros
            </button>
            <button class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-700 text-white font-medium rounded-lg shadow-sm flex items-center gap-2">
                <i class="bi bi-star"></i> Salvar Filtro
            </button>
            <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-medium rounded-lg shadow-sm flex items-center gap-2">
                <i class="bi bi-file-earmark-arrow-down"></i> Exportar Resultado
            </button>
        </div>
    </div>

    <!-- Seção de produtos -->
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg text-white">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <h2 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">
                    Produtos Encontrados
                    @if($products->total())
                    <span class="text-sm font-normal text-neutral-500 dark:text-neutral-400">
                        ({{ $products->total() }} {{ $products->total() === 1 ? 'item' : 'itens' }})
                    </span>
                    @endif
                </h2>
                <!-- Resumo de filtros ativos -->
                @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max || (isset($estoque_filtro) && $estoque_filtro) || (isset($data_filtro) && $data_filtro) || (isset($margem_filtro) && $margem_filtro))
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-2">
                    <i class="bi bi-filter-circle mr-1"></i>
                    Filtros ativos: {{ $products->total() }} encontrados
                </span>
                @endif
            </div>

            <!-- Views toggle e Paginação -->
            <div class="flex items-center gap-4">
                <!-- Paginação Horizontal Estilizada -->
                @if($products->hasPages())
                <div class="flex items-center gap-1 bg-neutral-100 dark:bg-neutral-700 rounded-lg p-1">
                    <!-- Primeiro -->
                    @if($products->currentPage() > 1)
                    <a href="{{ $products->url(1) }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Primeira página">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                    @endif

                    <!-- Anterior -->
                    @if($products->previousPageUrl())
                    <a href="{{ $products->previousPageUrl() }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Página anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    @endif

                    <!-- Páginas -->
                    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                    @if($page == $products->currentPage())
                    <span class="px-3 py-1 bg-purple-500 text-white rounded-md text-sm font-medium">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}"
                        class="px-3 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-600 rounded-md transition-colors duration-200 text-sm">
                        {{ $page }}
                    </a>
                    @endif
                    @endforeach

                    <!-- Próximo -->
                    @if($products->nextPageUrl())
                    <a href="{{ $products->nextPageUrl() }}"
                        class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                        title="Próxima página">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    @endif

                    <!-- Último -->
                    @if($products->currentPage() < $products->lastPage())
                        <a href="{{ $products->url($products->lastPage()) }}"
                            class="px-2 py-1 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200 text-sm"
                            title="Última página">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                        @endif
                </div>
                @endif

                <!-- Views toggle -->
                <div class="flex items-center bg-neutral-100 dark:bg-neutral-700 rounded-lg p-1">
                    <button class="px-3 py-2 bg-purple-500 text-white rounded-md shadow-sm transition-all duration-200">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="px-3 py-2 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors duration-200">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Produtos -->
        @if($products->isEmpty())
        <!-- Estado vazio aprimorado -->
        <div class="empty-state flex flex-col items-center justify-center py-20 bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-2xl border-2 border-dashed border-neutral-300 dark:border-neutral-600">
            <div class="relative">
                <!-- Ícone animado -->
                <div class="w-32 h-32 mx-auto mb-6 text-neutral-400 relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 dark:from-purple-800 dark:to-blue-800 rounded-full opacity-20 animate-pulse"></div>
                    <svg class="w-full h-full relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 5l7 7" />
                    </svg>
                </div>

                <!-- Elementos decorativos -->
                <div class="absolute top-0 left-0 w-4 h-4 bg-purple-300 rounded-full opacity-50 animate-bounce"></div>
                <div class="absolute top-4 right-0 w-3 h-3 bg-blue-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 0.5s;"></div>
                <div class="absolute bottom-0 left-4 w-2 h-2 bg-pink-300 rounded-full opacity-50 animate-bounce" style="animation-delay: 1s;"></div>
            </div>

            <h3 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 mb-3">📦 Nenhum produto encontrado</h3>
            <p class="text-neutral-600 dark:text-neutral-400 text-center mb-8 max-w-md text-lg">
                @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
                Nenhum produto corresponde aos filtros aplicados. Tente ajustar os critérios de busca.
                @else
                Sua prateleira está vazia! Que tal começar adicionando seu primeiro produto ao catálogo?
                @endif
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                @if($search || $category || $tipo || $status_filtro || $preco_min || $preco_max)
                <button wire:click="clearFilters"
                    class="btn-gradient inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="bi bi-x-circle mr-2 icon-rotate"></i>
                    🔄 Limpar Filtros
                </button>
                @else
                <a href="{{ route('products.create') }}"
                    class="btn-gradient inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="bi bi-plus-square mr-3 text-xl floating-badge"></i>
                    ✨ Criar Primeiro Produto
                </a>
                @endif

                <a href="{{ route('products.upload') }}"
                    class="btn-gradient inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="bi bi-file-earmark-arrow-up mr-2 icon-pulse"></i>
                    📂 Upload em Lote
                </a>
            </div>
        </div>
        @else
        <!-- Grid de Produtos com CSS customizado mantido -->
        <form>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
            @foreach($products as $product)
            @if($product->tipo === 'kit')
            <!-- Kit Card com informações extras -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl border-2 border-blue-200 dark:border-blue-700 hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="relative p-4">
                    <div class="absolute top-2 right-2 flex gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <i class="bi bi-boxes mr-1"></i>KIT
                        </span>
                        <!-- Badge de status -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-100 text-{{ $product->status == 'ativo' ? 'green' : ($product->status == 'inativo' ? 'gray' : 'red') }}-800" title="Status">
                            <i class="bi bi-circle-fill mr-1"></i> {{ ucfirst($product->status) }}
                        </span>
                        <!-- Badge de novo -->
                        @if(
                            \Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7
                        )
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800" title="Novo Produto">
                            <i class="bi bi-stars mr-1"></i> Novo
                        </span>
                        @endif
                        <!-- Badge de promoção -->
                        @if(isset($product->promotion) && $product->promotion)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="Em promoção">
                            <i class="bi bi-lightning mr-1"></i> Promoção
                        </span>
                        @endif
                        <!-- Badge de vendas recorrentes -->
                        @if(isset($product->recorrente) && $product->recorrente)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" title="Vendas recorrentes">
                            <i class="bi bi-arrow-repeat mr-1"></i> Recorrente
                        </span>
                        @endif
                        <button type="button" class="text-pink-600 hover:text-pink-800" title="Duplicar"><i class="bi bi-files"></i></button>
                        <button type="button" class="text-gray-600 hover:text-gray-800" title="Histórico de vendas"><i class="bi bi-clock-history"></i></button>
                        <button type="button" class="text-indigo-600 hover:text-indigo-800" title="Imprimir"><i class="bi bi-printer"></i></button>
                        <button type="button" class="text-blue-600 hover:text-blue-800" title="Exportar Produto"><i class="bi bi-file-earmark-arrow-down"></i></button>
                        <button type="button" class="text-yellow-600 hover:text-yellow-800" title="Adicionar ao destaque"><i class="bi bi-star"></i></button>
                    </div>

                    <div class="text-center">
                        <input type="checkbox" class="mb-2" title="Selecionar">
                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                            alt="{{ $product->name }}"
                            class="w-24 h-24 mx-auto rounded-lg object-cover bg-neutral-100 dark:bg-neutral-700 mb-3">

                        <!-- Categoria com ícone -->
                        <div class="mb-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                <i class="{{ $product->category->icone ?? 'bi bi-box' }} mr-1"></i> {{ $product->category->name ?? '-' }}
                            </span>
                        </div>

                        <h3 class="font-bold text-neutral-800 dark:text-neutral-100 text-sm mb-1" title="{{ $product->name }}" data-tooltip="Código: #{{ $product->product_code }}&#10;Estoque: {{ $product->stock_quantity }}&#10;Última venda: {{ $product->last_sale_at ? \Carbon\Carbon::parse($product->last_sale_at)->format('d/m/Y') : '-' }}&#10;Vendas: {{ $product->sales_count ?? 0 }}">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2" title="Código do produto">#{{ $product->product_code }}</p>

                        <div class="space-y-1 text-xs">
                            <div class="text-green-600 dark:text-green-400 font-semibold" title="Preço de venda">
                                <i class="bi bi-currency-dollar"></i> R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                            </div>
                            <!-- Margem de lucro -->
                            @if(isset($product->margin))
                            <div class="text-{{ $product->margin < 10 ? 'red' : ($product->margin < 30 ? 'orange' : 'green') }}-600 font-semibold" title="Margem de Lucro">
                                <i class="bi bi-graph-up-arrow"></i> Margem: {{ number_format($product->margin, 1, ',', '.') }}%
                                @if($product->margin < 10)
                                <span class="ml-1 px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs">Baixa</span>
                                @endif
                            </div>
                            @endif
                            <!-- Vendas -->
                            <div class="text-blue-600 font-semibold" title="Vendas">
                                <i class="bi bi-cart-check"></i> Vendas: {{ $product->sales_count ?? 0 }}
                            </div>
                            <!-- Última venda -->
                            <div class="text-xs text-neutral-500" title="Última venda">
                                <i class="bi bi-clock-history"></i> Última venda: {{ $product->last_sale_at ? \Carbon\Carbon::parse($product->last_sale_at)->format('d/m/Y') : '-' }}
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2 justify-center">
                            <a href="{{ route('products.show', $product->product_code) }}"
                                class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                                title="Ver Detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('products.kit.edit', $product) }}"
                                class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                                title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="#" class="inline-flex items-center px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition-colors duration-200" title="Ver histórico de vendas">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <a href="#" class="inline-flex items-center px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-colors duration-200" title="Exportar Produto">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            </a>
                            <a href="#" class="inline-flex items-center px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium rounded-lg transition-colors duration-200" title="Adicionar ao destaque">
                                <i class="bi bi-star"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Produto Simples com CSS customizado mantido -->
            <div class="product-card-modern">
                <!-- Botões flutuantes -->
                <div class="btn-action-group flex gap-2">
                    <input type="checkbox" title="Selecionar">
                    <a href="{{ route('products.show', $product->product_code) }}" class="btn btn-secondary" title="Ver Detalhes">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <button type="button" wire:click="confirmDelete({{ $product->id }})" class="btn btn-danger" title="Excluir">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button type="button" class="btn btn-info" title="Duplicar"><i class="bi bi-files"></i></button>
                    <button type="button" class="btn btn-warning" title="Histórico"><i class="bi bi-clock-history"></i></button>
                    <button type="button" class="btn btn-dark" title="Imprimir"><i class="bi bi-printer"></i></button>
                </div>

                <!-- Área da imagem com badges -->
                <div class="product-img-area">
                    <img src="{{ asset('storage/products/' . $product->image) }}" class="product-img" alt="{{ $product->name }}">

                    @if($product->stock_quantity == 0)
                    <div class="out-of-stock">
                        <i class="bi bi-x-circle"></i> Fora de Estoque
                    </div>
                    @endif

                    <!-- Código do produto -->
                    <span class="badge-product-code" title="Código do Produto">
                        <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                    </span>

                    <!-- Quantidade -->
                    <span class="badge-quantity" title="Quantidade em Estoque">
                        <i class="bi bi-stack"></i> {{ $product->stock_quantity }}
                    </span>

                    <!-- Ícone da categoria -->
                    <div class="category-icon-wrapper">
                        <i class="{{ $product->category->icone ?? 'bi bi-box' }} category-icon"></i>
                    </div>
                </div>

                <!-- Conteúdo -->
                <div class="card-body">
                    <div class="product-title" title="{{ $product->name }}">
                        {{ ucwords($product->name) }}
                    </div>
                </div>

                <span class="badge-price" title="Preço de Custo">
                    <i class="bi bi-tag"></i>
                    {{ number_format($product->price, 2, ',', '.') }}
                </span>

                <span class="badge-price-sale" title="Preço de Venda">
                    <i class="bi bi-currency-dollar"></i>
                    {{ number_format($product->price_sale, 2, ',', '.') }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
        </form>

        <!-- Paginação aprimorada -->
        <div class="pagination-wrapper mt-12 flex flex-col items-center">
            <div class="bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-xl p-6 border border-neutral-200 dark:border-neutral-600 shadow-sm flex flex-col gap-4 items-center">
                {{ $products->links() }}
                <div class="flex gap-2 mt-2">
                    <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow flex items-center gap-2"><i class="bi bi-file-earmark-arrow-down"></i> Exportar Página</button>
                    <button class="px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-700 text-white font-bold rounded-xl shadow flex items-center gap-2"><i class="bi bi-printer"></i> Imprimir Página</button>
                </div>
            </div>

            <!-- Informações da paginação -->
            <div class="mt-4 text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    📊 Exibindo
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->firstItem() ?? 0 }}</span>
                    até
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 floating-badge">{{ $products->total() }}</span>
                    produtos
                    <span class="ml-2 text-xs text-purple-500">({{ $products->lastPage() }} páginas)</span>
                </p>
            </div>
        </div>
        @endif
    </div>

<!-- Modal de Confirmação de Exclusão aprimorado -->
@if($showDeleteModal)
<div class="modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="modal-content bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header do modal -->
        <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6 text-white">
            <div class="flex items-center justify-center mb-4">
                <div class="p-3 bg-white/20 rounded-full">
                    <i class="bi bi-exclamation-triangle text-3xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center mb-2">⚠️ Confirmar Exclusão</h3>
            <p class="text-red-100 text-center text-sm">Esta ação é irreversível!</p>
        </div>

            <!-- Kit Card com badges de alerta e dropdown de ações rápidas -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl border-2 border-blue-200 dark:border-blue-700 hover:shadow-lg transition-all duration-300 transform hover:scale-105 relative">
                <!-- Badges de alerta -->
                <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                    @if($product->price_sale == 0)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="Preço de venda zerado">
                        <i class="bi bi-currency-dollar mr-1"></i> Preço Zerado
                    </span>
                    @endif
                    @if(empty($product->image))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800" title="Sem imagem cadastrada">
                        <i class="bi bi-image mr-1"></i> Sem Imagem
                    </span>
                    @endif
                    @if($product->stock_quantity <= 5)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800" title="Estoque baixo">
                        <i class="bi bi-exclamation-triangle mr-1"></i> Estoque Baixo
                    </span>
                    @endif
                </div>
                <div class="relative p-4">
                    <div class="absolute top-2 right-2 flex gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <i class="bi bi-boxes mr-1"></i>KIT
                        </span>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-indigo-600 hover:text-indigo-800" title="Ações rápidas">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white dark:bg-neutral-900 rounded-xl shadow-lg z-20">
                                <ul class="py-2">
                                    <li><button type="button" class="w-full text-left px-4 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-pink-600" title="Duplicar"><i class="bi bi-files mr-1"></i> Duplicar</button></li>
                                    <li><button type="button" class="w-full text-left px-4 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-gray-600" title="Histórico"><i class="bi bi-clock-history mr-1"></i> Histórico</button></li>
                                    <li><button type="button" class="w-full text-left px-4 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-indigo-600" title="Imprimir"><i class="bi bi-printer mr-1"></i> Imprimir</button></li>
                                    <li><button type="button" class="w-full text-left px-4 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-blue-600" title="Exportar"><i class="bi bi-file-earmark-arrow-down mr-1"></i> Exportar</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <input type="checkbox" class="mb-2" title="Selecionar">
                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                            alt="{{ $product->name }}"
                            class="w-24 h-24 mx-auto rounded-lg object-cover bg-neutral-100 dark:bg-neutral-700 mb-3">
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-100 text-sm mb-1" title="{{ $product->name }}">{{ $product->name }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2" title="Código do produto">#{{ $product->product_code }}</p>
                        <div class="space-y-1 text-xs">
                            <div class="text-green-600 dark:text-green-400 font-semibold" title="Preço de venda">
                                <i class="bi bi-currency-dollar"></i> R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2 justify-center">
                            <a href="{{ route('products.show', $product->product_code) }}"
                                class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                                title="Ver Detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('products.kit.edit', $product) }}"
                                class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
                                title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
@endif
