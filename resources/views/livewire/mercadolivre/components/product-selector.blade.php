<div class="w-full h-full flex flex-col">
    <!-- Incluir CSS dos produtos -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">

    <style>
        .product-card-modern.selected {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3) !important;
        }

        .product-card-modern {
            cursor: pointer;
            user-select: none;
        }

        .product-card-modern:hover {
            transform: translateY(-2px) scale(1.01);
        }

        .product-card-modern.selected:hover {
            transform: translateY(-2px) scale(1.02);
        }
    </style>

    <!-- Header com Controles de Busca -->
    <div class="flex-shrink-0 pb-4 border-b border-gray-200 dark:border-slate-700">
        <div class="flex flex-col gap-3">
            <!-- Campo de pesquisa -->
            <div class="relative">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Buscar produtos por nome, código ou código de barras..."
                    class="w-full pl-12 pr-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
            </div>

            <!-- Info de produtos já selecionados -->
            @if(!empty($products))
            <div class="flex items-center justify-between px-4 py-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-2">
                    <i class="bi bi-box-seam-fill text-purple-600 dark:text-purple-400"></i>
                    <span class="text-sm font-bold text-purple-700 dark:text-purple-400">
                        {{ count($products) }} produto(s) no kit
                    </span>
                </div>
                <div class="text-sm text-purple-600 dark:text-purple-400">
                    Disponível: <span class="font-black">{{ $this->getAvailableQuantity() }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Grid de Produtos com Scroll -->
    <div class="flex-1 overflow-y-auto mt-4">
        @if(!empty($searchResults))
            <!-- Grid de Cards de Produtos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 pr-2">
                @foreach($searchResults as $result)
                    @php
                        $isSelected = collect($products)->contains('id', $result['id']);
                    @endphp

                    <div class="product-card-modern {{ $isSelected ? 'selected' : '' }}"
                         wire:click="addProduct({{ $result['id'] }})"
                         wire:key="product-selector-{{ $result['id'] }}">

                        <!-- Toggle de seleção estilizado -->
                        <div class="btn-action-group flex gap-2">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 cursor-pointer
                                        {{ $isSelected
                                            ? 'bg-emerald-600 border-emerald-600 text-white'
                                            : 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-transparent hover:border-emerald-400 dark:hover:border-emerald-500' }}">
                                @if($isSelected)
                                <i class="bi bi-check text-sm"></i>
                                @endif
                            </div>
                        </div>

                        <!-- Área da imagem com badges -->
                        <div class="product-img-area">
                            <img src="{{ $result['image_url'] }}"
                                 alt="{{ $result['name'] }}"
                                 class="product-img">

                            @if($result['stock_quantity'] <= 5)
                            <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                <i class="bi bi-exclamation-triangle mr-1"></i>
                                Baixo estoque
                            </div>
                            @endif

                            <!-- Código do produto -->
                            <span class="badge-product-code">
                                <i class="bi bi-upc-scan"></i> {{ $result['product_code'] }}
                            </span>

                            <!-- Quantidade em estoque -->
                            <span class="badge-quantity">
                                <i class="bi bi-stack"></i> {{ $result['stock_quantity'] }}
                            </span>
                        </div>

                        <!-- Conteúdo do card -->
                        <div class="card-body">
                            <div class="product-title" title="{{ $result['name'] }}">
                                {{ ucwords($result['name']) }}
                            </div>

                            <!-- Área dos preços -->
                            <div class="price-area">
                                <span class="badge-price" title="Preço">
                                    <i class="bi bi-currency-dollar"></i>
                                    {{ number_format($result['price'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(strlen($searchTerm) > 0)
            <!-- Estado vazio de busca -->
            <div class="flex flex-col items-center justify-center h-full py-12">
                <div class="w-32 h-32 mx-auto mb-6 text-slate-400 dark:text-slate-600">
                    <i class="bi bi-search text-8xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-2">
                    Nenhum produto encontrado para "{{ $searchTerm }}"
                </h3>
                <p class="text-slate-600 dark:text-slate-400 text-center">
                    Tente buscar por outro termo ou código de barras.
                </p>
            </div>
        @else
            <!-- Loading inicial -->
            <div class="flex flex-col items-center justify-center h-full py-12" wire:loading>
                <div class="w-32 h-32 mx-auto mb-6 text-slate-400 dark:text-slate-600">
                    <i class="bi bi-arrow-repeat animate-spin text-8xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-2">
                    Carregando produtos...
                </h3>
            </div>
        @endif
    </div>
</div>
