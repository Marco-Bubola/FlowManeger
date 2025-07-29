<div class="min-h-screen w-full py-8">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <!-- Modern Header with Analytics -->
        <div class="bg-gradient-to-br from-white via-blue-50 to-indigo-100 dark:from-gray-800 dark:via-gray-900 dark:to-indigo-900 rounded-3xl p-8 mb-8 border border-gray-200 dark:border-gray-700 shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Header principal com métricas -->
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-cart text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 via-indigo-800 to-purple-700 dark:from-white dark:via-indigo-200 dark:to-purple-300 bg-clip-text text-transparent">
                                Central de Vendas
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">Gerencie e monitore todas as suas vendas</p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-receipt text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $sales->total() }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Total de Vendas</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-currency-dollar text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($sales->sum('total_price'), 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Faturamento Total</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-clock text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $sales->where('status', 'pendente')->count() }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Pendentes</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-4 border border-white/50 dark:border-gray-700/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-person text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $sales->unique('client_id')->count() }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Clientes Únicos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Action Panel -->
                <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-white/50 dark:border-gray-700/50 lg:min-w-[400px]">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <i class="bi bi-tools text-indigo-600 mr-2"></i>
                        Ações Rápidas
                    </h3>

                    <div class="space-y-4">
                        <!-- Search and Filters Row -->
                        <div class="flex gap-3">
                            <!-- Campo de Pesquisa -->
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="bi bi-search text-gray-400"></i>
                                    </div>
                                    <input type="text"
                                        wire:model.live.debounce.300ms="search"
                                        id="search"
                                        placeholder="Buscar por cliente..."
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">
                                </div>
                            </div>

                            <!-- Dropdown de Filtros -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" type="button"
                                    class="flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-zinc-600 text-sm font-medium rounded-xl shadow-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i class="bi bi-funnel mr-2"></i>Filtros
                                    <i class="bi bi-chevron-down ml-2" :class="{'rotate-180': open}"></i>
                                </button>

                                <!-- Enhanced Dropdown Panel -->
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-cloak
                                     class="absolute right-0 z-50 mt-2 w-96 lg:w-[500px] bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-2xl p-6">

                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-zinc-600">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                            <i class="bi bi-funnel mr-2 text-indigo-600"></i>Filtros Avançados
                                        </h3>
                                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
                                            <i class="bi bi-x text-xl"></i>
                                        </button>
                                    </div>

                                    <!-- Quick Filter Buttons -->
                                    <div class="mb-6">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtros Rápidos:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button wire:click="$set('status', 'pendente')"
                                                    class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors">
                                                <i class="bi bi-clock mr-1"></i>Pendentes
                                            </button>
                                            <button wire:click="$set('status', 'pago')"
                                                    class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                                <i class="bi bi-check-circle mr-1"></i>Pagos
                                            </button>
                                            <button wire:click="$set('payment_type', 'parcelado')"
                                                    class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                                <i class="bi bi-credit-card mr-1"></i>Parcelados
                                            </button>
                                            <button wire:click="$set('filter', 'price_desc')"
                                                    class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors">
                                                <i class="bi bi-arrow-up mr-1"></i>Maior Valor
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Main Filters Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <!-- Status -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                <i class="bi bi-flag text-gray-400 mr-1"></i>Status
                                            </label>
                                            <select wire:model.live="status" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                                <option value="">Todos</option>
                                                <option value="pendente">Pendente</option>
                                                <option value="pago">Pago</option>
                                                <option value="cancelado">Cancelado</option>
                                            </select>
                                        </div>

                                        <!-- Tipo de Pagamento -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                <i class="bi bi-credit-card text-gray-400 mr-1"></i>Pagamento
                                            </label>
                                            <select wire:model.live="payment_type" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                                <option value="">Todos</option>
                                                <option value="a_vista">À Vista</option>
                                                <option value="parcelado">Parcelado</option>
                                            </select>
                                        </div>

                                        <!-- Ordenação -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                <i class="bi bi-sort-down text-gray-400 mr-1"></i>Ordenar
                                            </label>
                                            <select wire:model.live="filter" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                                <option value="">Padrão</option>
                                                <option value="created_at">Mais Recentes</option>
                                                <option value="price_desc">Maior Valor</option>
                                                <option value="price_asc">Menor Valor</option>
                                            </select>
                                        </div>

                                        <!-- Por página -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                <i class="bi bi-list text-gray-400 mr-1"></i>Por página
                                            </label>
                                            <select wire:model.live="perPage" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                                                <option value="12">12</option>
                                                <option value="18">18</option>
                                                <option value="24">24</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-zinc-600">
                                        <button @click="
                                            $wire.set('status', '');
                                            $wire.set('payment_type', '');
                                            $wire.set('filter', '');
                                            $wire.set('perPage', 12);
                                        "
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors duration-200">
                                            <i class="bi bi-arrow-clockwise mr-2"></i>Limpar
                                        </button>
                                        <button @click="open = false"
                                                class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg">
                                            <i class="bi bi-check mr-2"></i>Aplicar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Row -->
                        <div class="flex gap-3">
                            <!-- Export Button -->
                            <button class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-all duration-200">
                                <i class="bi bi-download mr-2 text-blue-600"></i>
                                Exportar
                            </button>

                            <!-- Refresh Button -->
                            <button wire:click="$refresh" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-all duration-200">
                                <i class="bi bi-arrow-clockwise mr-2 text-green-600"></i>
                                Atualizar
                            </button>

                            <!-- Nova Venda Button -->
                            <a href="{{ route('sales.create') }}"
                                class="flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="bi bi-plus mr-2"></i>
                                Nova Venda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Cards Grid - Estilo Produtos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 mb-8">
            @forelse($sales as $sale)
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-500 group transform hover:-translate-y-1">

                    <!-- Product Images Section - Todas as imagens -->
                    @php
                        $productsWithImages = $sale->saleItems->filter(fn($item) => $item->product && $item->product->image);
                        $totalItems = $sale->saleItems->count();
                    @endphp

                    <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-zinc-700 dark:to-zinc-800 overflow-hidden">
                        @if($productsWithImages->isNotEmpty())
                            @if($productsWithImages->count() === 1)
                                <!-- Uma única imagem -->
                                <img src="{{ Storage::url($productsWithImages->first()->product->image) }}"
                                     alt="{{ $productsWithImages->first()->product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @elseif($productsWithImages->count() === 2)
                                <!-- Duas imagens lado a lado -->
                                <div class="flex h-full">
                                    @foreach($productsWithImages->take(2) as $item)
                                        <div class="flex-1 relative overflow-hidden">
                                            <img src="{{ Storage::url($item->product->image) }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($productsWithImages->count() === 3)
                                <!-- Três imagens: uma grande + duas pequenas -->
                                <div class="flex h-full">
                                    <div class="w-2/3 relative overflow-hidden">
                                        <img src="{{ Storage::url($productsWithImages->first()->product->image) }}"
                                             alt="{{ $productsWithImages->first()->product->name }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="w-1/3 flex flex-col">
                                        @foreach($productsWithImages->skip(1)->take(2) as $item)
                                            <div class="flex-1 relative overflow-hidden {{ !$loop->last ? 'border-b border-white/20' : '' }}">
                                                <img src="{{ Storage::url($item->product->image) }}"
                                                     alt="{{ $item->product->name }}"
                                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Quatro ou mais imagens: grid 2x2 -->
                                <div class="grid grid-cols-2 grid-rows-2 h-full gap-1">
                                    @foreach($productsWithImages->take(4) as $item)
                                        <div class="relative overflow-hidden">
                                            <img src="{{ Storage::url($item->product->image) }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">

                                            @if($loop->last && $productsWithImages->count() > 4)
                                                <!-- Overlay para mostrar quantas imagens a mais -->
                                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                                    <span class="text-white font-bold text-lg">
                                                        +{{ $productsWithImages->count() - 4 }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <!-- Fallback quando não há imagens -->
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                                    <i class="bi bi-cart text-white text-2xl"></i>
                                </div>
                            </div>
                        @endif

                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold backdrop-blur-sm
                                @if($sale->status === 'pago') bg-green-500/90 text-white
                                @elseif($sale->status === 'pendente') bg-yellow-500/90 text-white
                                @else bg-red-500/90 text-white @endif">
                                @if($sale->status === 'pago')
                                    <i class="bi bi-check-circle mr-1"></i>Pago
                                @elseif($sale->status === 'pendente')
                                    <i class="bi bi-clock mr-1"></i>Pendente
                                @else
                                    <i class="bi bi-x-circle mr-1"></i>Cancelado
                                @endif
                            </span>
                        </div>

                        <!-- Items Count Badge -->
                        @if($totalItems > 1)
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-black/60 text-white backdrop-blur-sm">
                                    <i class="bi bi-stack mr-1"></i>{{ $totalItems }} itens
                                </span>
                            </div>
                        @endif

                        <!-- Price Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                            <div class="text-white">
                                <p class="text-lg font-bold">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</p>
                                <p class="text-xs opacity-90">{{ $sale->payment_type === 'a_vista' ? 'À Vista' : 'Parcelado' }}</p>
                            </div>
                        </div>
                    </div>                    <!-- Product Details Section -->
                    <div class="p-4 space-y-3">
                        <!-- Sale Header -->
                        <div class="space-y-1">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm flex items-center justify-between">
                                <span>Venda #{{ $sale->id }}</span>
                                <!-- Action Menu -->
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open"
                                            class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition-colors">
                                        <i class="bi bi-three-dots text-sm"></i>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                         class="absolute right-0 top-7 z-50 w-48 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-xl py-1">
 <a href="{{ route('sales.show', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-eye text-blue-500 mr-3"></i>Ver Detalhes
                                        </a>
                                        <a href="{{ route('sales.edit', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-pencil text-green-500 mr-3"></i>Editar Venda
                                        </a>
                                        <a href="{{ route('sales.add-products', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-plus-circle text-indigo-500 mr-3"></i>Adicionar Produtos
                                        </a>
                                        <a href="{{ route('sales.edit-prices', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-currency-dollar text-purple-500 mr-3"></i>Editar Preços
                                        </a>
                                        <a href="{{ route('sales.add-payments', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-credit-card text-teal-500 mr-3"></i>Adicionar Pagamento
                                        </a>
                                        <a href="{{ route('sales.edit-payments', $sale->id) }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-pencil-square text-orange-500 mr-3"></i>Editar Pagamentos
                                        </a>
                                        <div class="border-t border-gray-200 dark:border-zinc-600 my-2"></div>
                                        <button wire:click="exportPdf({{ $sale->id }})"
                                                class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                                            <i class="bi bi-file-earmark-pdf text-red-500 mr-3"></i>Exportar PDF
                                        </button>
                                        <button wire:click="confirmDelete({{ $sale->id }})"
                                                class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                            <i class="bi bi-trash text-red-500 mr-3"></i>Excluir Venda
                                        </button>
                                    </div>
                                </div>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <!-- Client Info -->
                        <div class="flex items-center space-x-2">
                            <i class="bi bi-person text-gray-400 text-xs"></i>
                            <span class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                {{ $sale->client->name ?? 'Cliente não informado' }}
                            </span>
                        </div>

                        <!-- Products List - Apenas Códigos e Quantidades -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    <i class="bi bi-box text-indigo-600 mr-1"></i>Produtos:
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $sale->saleItems->count() }} item(s)
                                </span>
                            </div>

                            <div class="space-y-1 max-h-20 overflow-y-auto">
                                @foreach($sale->saleItems->take(3) as $item)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-zinc-700 rounded-lg px-2 py-1">
                                        <span class="text-xs font-mono text-gray-800 dark:text-gray-200 truncate">
                                            {{ $item->product->code ?? $item->product->name }}
                                        </span>
                                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 ml-2">
                                            {{ $item->quantity }}x
                                        </span>
                                    </div>
                                @endforeach

                                @if($sale->saleItems->count() > 3)
                                    <div class="text-center">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            +{{ $sale->saleItems->count() - 3 }} produto(s)
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-4 pb-4">
                        <div class="flex gap-2">
                            <a href="{{ route('sales.show', $sale) }}"
                               class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-all duration-200 border border-indigo-200 dark:border-indigo-800">
                                <i class="bi bi-eye mr-1"></i>Ver
                            </a>

                            <a href="{{ route('sales.edit', $sale) }}"
                               class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg transition-all duration-200 border border-emerald-200 dark:border-emerald-800">
                                <i class="bi bi-pencil mr-1"></i>Edit
                            </a>

                            <button wire:click="confirmDelete({{ $sale->id }})"
                                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-all duration-200 border border-red-200 dark:border-red-800">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl p-12 text-center border border-gray-200 dark:border-zinc-700">
                        <div class="w-20 h-20 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-cart-x text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Nenhuma venda encontrada
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if($search)
                                Não encontramos vendas com o termo "{{ $search }}".
                            @else
                                Comece registrando sua primeira venda.
                            @endif
                        </p>
                        <a href="{{ route('sales.create') }}"
                           class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="bi bi-plus mr-2"></i>
                            Criar primeira venda
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if($sales->hasPages())
            <div class="flex justify-center mt-8">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tem certeza de que deseja excluir a venda <strong>#{{ $deletingSale?->id }}</strong> do cliente <strong>{{ $deletingSale?->client->name }}</strong>?
                                Esta ação não pode ser desfeita e o estoque dos produtos será restaurado.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        wire:click="deleteSale"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="bi bi-trash mr-2"></i>
                        Excluir
                    </button>
                    <button type="button"
                        wire:click="cancelDelete"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
