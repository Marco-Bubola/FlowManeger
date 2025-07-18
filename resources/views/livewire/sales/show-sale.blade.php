<div class="w-full min-h-screen ">
    <!-- Header fixo -->
    <div class="w-full px-6 py-4 sticky top-0 z-20  shadow-lg rounded-b-2xl backdrop-blur border-b ">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('sales.index') }}"
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white dark:bg-neutral-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all duration-200 shadow border border-blue-200 dark:border-blue-700">
                    <i class="bi bi-arrow-left text-2xl text-blue-600 dark:text-blue-400"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold flex items-center gap-2 px-6 py-2 shadow-lg">
                        <i class="bi bi-receipt text-indigo-600 dark:text-indigo-400 text-3xl"></i>
                        <span class="text-gray-900 dark:text-white border-b-4 border-black pb-1">Venda #{{ $sale->id }}</span>
                    </h1>
                </div>
            </div>
            <!-- Botões de Ação -->
            <div class="flex items-center gap-3">
                <button wire:click="exportPdf" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow border border-red-600 hover:border-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="bi bi-file-earmark-pdf mr-2"></i>
                    <span class="hidden md:inline">Exportar PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="w-full flex">
        <!-- Área principal (75% da largura) -->
        <div class="w-3/4 pr-4">
            <!-- Informações da Venda -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-info-circle text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                    Informações da Venda
                </h2>
                @if($sale->tipo_pagamento === 'a_vista')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cliente -->
                    <div class="bg-gradient-to-r from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 shadow flex flex-col gap-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-1">
                            <i class="bi bi-person-circle text-blue-500 text-lg"></i>Cliente
                        </h3>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2"><i class="bi bi-person-badge text-lg"></i> {{ $sale->client->name }}</p>
                        @if($sale->client->email)
                        <p class="text-base text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                            <i class="bi bi-envelope"></i>{{ $sale->client->email }}
                        </p>
                        @endif
                        @if($sale->client->phone)
                        <p class="text-base text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                            <i class="bi bi-telephone"></i>{{ $sale->client->phone }}
                        </p>
                        @endif
                    </div>
                    <!-- Status e Pagamento -->
                    <div class="bg-gradient-to-r from-green-50 via-white to-indigo-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow flex flex-col gap-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-1">
                            <i class="bi bi-flag text-green-500 text-lg"></i>Status e Pagamento
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-activity"></i>Status:</span>
                                <span class="px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1
                                    @if($sale->status === 'pago') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                    @elseif($sale->status === 'pendente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                    @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                                    <i class="bi bi-check-circle"></i> {{ ucfirst($sale->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-wallet2"></i>Tipo:</span>
                                <span class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-1">
                                    <i class="bi bi-cash-coin"></i> {{ $sale->tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}
                                    @if($sale->tipo_pagamento === 'parcelado')
                                    ({{ $sale->parcelas }}x)
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cliente e Status/Tipo (um embaixo do outro) -->
                    <div class="flex flex-col gap-4 w-full">
                        <div class="bg-gradient-to-r from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 shadow flex flex-col gap-2 w-full">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-1">
                                <i class="bi bi-person-circle text-blue-500 text-lg"></i>Cliente
                            </h3>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2"><i class="bi bi-person-badge text-lg"></i> {{ $sale->client->name }}</p>
                            @if($sale->client->email)
                            <p class="text-base text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                <i class="bi bi-envelope"></i>{{ $sale->client->email }}
                            </p>
                            @endif
                            @if($sale->client->phone)
                            <p class="text-base text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                <i class="bi bi-telephone"></i>{{ $sale->client->phone }}
                            </p>
                            @endif
                        </div>
                        <div class="bg-gradient-to-r from-green-50 via-white to-indigo-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow flex flex-col gap-2 w-full">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-1">
                                <i class="bi bi-flag text-green-500 text-lg"></i>Status e Pagamento
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-activity"></i>Status:</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1
                                        @if($sale->status === 'pago') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                        @elseif($sale->status === 'pendente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                        @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @endif">
                                        <i class="bi bi-check-circle"></i> {{ ucfirst($sale->status) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-wallet2"></i>Tipo:</span>
                                    <span class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-1">
                                        <i class="bi bi-cash-coin"></i> {{ $sale->tipo_pagamento === 'a_vista' ? 'À Vista' : 'Parcelado' }}
                                        @if($sale->tipo_pagamento === 'parcelado')
                                        ({{ $sale->parcelas }}x)
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Parcelas (se aplicável) -->
                    @if($sale->tipo_pagamento === 'parcelado' && $parcelas->count() > 0)
                    <div class="mb-8">
                        <div class="space-y-3">
                            @foreach($parcelas as $parcela)
                                @if($parcela->status === 'pendente')
                                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-white via-indigo-50 to-green-50 dark:from-zinc-800 dark:via-indigo-900/20 dark:to-green-900/20 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                            {{ $parcela->numero_parcela }}
                                        </span>
                                        <div>
                                            <p class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-1">
                                                <i class="bi bi-cash-coin text-green-600"></i> R$ {{ number_format($parcela->valor, 2, ',', '.') }}
                                            </p>
                                            <p class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                                <i class="bi bi-calendar-event"></i> Vencimento: {{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                            {{ ucfirst($parcela->status) }}
                                        </span>
                                        <button wire:click="openPaymentModal({{ $parcela->id }})"
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs transition-colors duration-200 flex items-center gap-1 shadow">
                                            <i class="bi bi-cash-stack"></i> Pagar
                                        </button>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Produtos da Venda -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <i class="bi bi-box text-indigo-600 dark:text-indigo-400 mr-2 text-2xl"></i>
                        Produtos da Venda
                    </h2>
                     <a href="{{ route('sales.edit-prices', $sale->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    <i class="bi bi-pencil mr-2"></i>
                    Editar Preços
                </a>
                    <a href="{{ route('sales.add-products', $sale->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Adicionar Produto
                    </a>
                </div>

                <!-- Lista de Produtos em Cards -->
                @if($sale->saleItems->count() > 0)
                <!-- Inclui o CSS dos produtos -->
                <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
                <style>
                    .sale-item-card {
                        border: 2.5px solid var(--card-border);
                        background: var(--card-bg);
                        box-shadow: 0 4px 18px 0 var(--card-shadow);
                        border-radius: 1.7em;
                        position: relative;
                        overflow: visible;
                        transition: all 0.18s ease-in-out;
                    }

                    .sale-item-card:hover {
                        box-shadow: 0 8px 32px 0 var(--shadow-strong);
                        transform: translateY(-2px) scale(1.01);
                        border-color: var(--blue-strong);
                    }

                    .sale-item-card:before {
                        content: '';
                        position: absolute;
                        inset: 0;
                        border-radius: 1.7em;
                        pointer-events: none;
                        box-shadow: 0 0 0 3px var(--card-accent) inset;
                        z-index: 1;
                    }

                    .sale-item-card .product-img-area {
                        background: var(--card-bg);
                        border-top-left-radius: 1.7em;
                        border-top-right-radius: 1.7em;
                        border-bottom: 4px solid var(--card-border);
                        box-shadow: 0 2px 16px var(--card-shadow2);
                        position: relative;
                        z-index: 2;
                        overflow: visible;
                        height: 200px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .sale-item-card .category-icon-wrapper {
                        border: 5px solid var(--gold-semi);
                        background: var(--gold-semi);
                        box-shadow: 0 4px 18px 0 var(--gold-shadow);
                        border-radius: 50%;
                        position: absolute;
                        bottom: -15px;
                        right: 15px;
                        z-index: 3;
                        width: 50px;
                        height: 50px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .sale-item-card .category-icon {
                        font-size: 1.5em;
                        color: var(--gold-text);
                    }

                    .sale-item-card .product-title {
                        font-size: 1.1em;
                        font-weight: 700;
                        color: var(--card-title);
                        margin-bottom: 0.5em;
                        text-align: center;
                    }

                    .sale-item-card .badge-quantity {
                        position: absolute;
                        top: 10px;
                        left: 10px;
                        background: var(--blue-strong);
                        color: white;
                        padding: 0.3em 0.7em;
                        border-radius: 50px;
                        font-size: 0.8em;
                        font-weight: 600;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                        z-index: 4;
                    }

                    .sale-item-card .badge-price {
                        background: var(--green-strong);
                        color: white;
                        padding: 0.3em 0.7em;
                        border-radius: 50px;
                        font-size: 0.85em;
                        font-weight: 600;
                        margin-bottom: 0.5em;
                        display: inline-block;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                    }

                    .sale-item-card .badge-price-sale {
                        background: var(--purple-strong);
                        color: white;
                        padding: 0.3em 0.7em;
                        border-radius: 50px;
                        font-size: 0.85em;
                        font-weight: 600;
                        display: inline-block;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                    }

                    .sale-item-card .btn-action-group {
                        position: absolute;
                        top: 10px;
                        right: 10px;
                        z-index: 4;
                        display: flex;
                        flex-direction: column;
                        gap: 0.4em;
                    }

                    .sale-item-card .btn-action-group .btn {
                        border-radius: 50%;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 0;
                        font-size: 1em;
                        box-shadow: 0 1px 4px var(--shadow);
                        border: 2px solid var(--card-accent);
                        transition: all 0.2s ease-in-out;
                    }

                    .sale-item-card .btn-action-group .btn-danger {
                        background: var(--card-danger);
                        color: var(--card-text);
                    }

                    .sale-item-card .btn-action-group .btn-primary {
                        background: var(--card-accent2);
                        color: var(--white);
                    }

                    .sale-item-card .btn-action-group .btn:hover {
                        transform: scale(1.1);
                    }

                    .sale-item-card .card-body {
                        padding: 1.5em 1em 2.5em 1em;
                        position: relative;
                        background: transparent;
                        height: 100px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                </style>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($sale->saleItems as $item)
                    <div class="sale-item-card">
                        <!-- Área da Imagem -->
                        <div class="product-img-area">
                            @if($item->product->image)
                            <img src="{{ asset('storage/products/' . $item->product->image) }}"
                                alt="{{ $item->product->name }}"
                                class="w-full h-full object-cover rounded-lg">
                            @else
                            <div class="w-full h-full bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-image text-4xl text-gray-400"></i>
                            </div>
                            @endif

                            <!-- Ícone da Categoria -->
                            <div class="category-icon-wrapper">
                                @if($item->product->category)
                                <i class="bi bi-{{ $item->product->category->icon ?? 'box' }} category-icon"></i>
                                @else
                                <i class="bi bi-box category-icon"></i>
                                @endif
                            </div>
                        </div>

                        <!-- Badge de Quantidade -->
                        <div class="badge-quantity">
                            <i class="bi bi-stack mr-1"></i>{{ $item->quantity }}
                        </div>

                        <!-- Corpo do Card -->
                        <div class="card-body">
                            <h4 class="product-title text-lg font-bold" title="{{ $item->product->name }}">
                                {{ strlen($item->product->name) > 20 ? substr($item->product->name, 0, 20) . '...' : $item->product->name }}
                            </h4>

                        <!-- Preços -->
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <div class="badge-price text-base font-semibold">
                                R$ {{ number_format($item->price, 2, ',', '.') }}
                            </div>
                            <div class="badge-price-sale text-base font-semibold">
                                R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                            </div>
                        </div>
                        </div>

                        <!-- Ações -->
                        <div class="btn-action-group">
                            <a href="{{ route('sales.edit-prices', $sale->id) }}"
                                class="btn btn-primary"
                                title="Editar preços">
                                <i class="bi bi-pencil"></i>
                            </a>
                        <button type="button"
                            class="btn btn-danger"
                            title="Remover produto"
                            data-modal-target="popup-modal-{{ $item->id }}" data-modal-toggle="popup-modal-{{ $item->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-box text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nenhum produto adicionado</h3>
                    <p class="text-base text-gray-500 dark:text-gray-400 mb-4">Adicione produtos para começar a venda</p>
                    <a href="{{ route('sales.add-products', $sale->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Adicionar Produto
                    </a>
                </div>
                @endif
            </div>

           
        </div>

        <!-- Sidebar (25% da largura) -->
        <div class="w-1/4 p-6">
            <!-- Resumo Financeiro -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-calculator text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    Resumo Financeiro
                </h2>
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-indigo-50 via-white to-blue-50 dark:from-indigo-900/20 dark:via-zinc-900/20 dark:to-blue-900/20 rounded-lg p-4 shadow flex flex-col gap-2">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-receipt"></i> Total da Venda</p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                <i class="bi bi-cash"></i> R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @if($sale->amount_paid > 0)
                    <div class="bg-gradient-to-r from-green-50 via-white to-emerald-50 dark:from-green-900/20 dark:via-zinc-900/20 dark:to-emerald-900/20 rounded-lg p-4 shadow flex flex-col gap-2">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-cash-stack"></i> Valor Pago</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                                <i class="bi bi-coin"></i> R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @endif
                    @if($sale->amount_due > 0)
                    <div class="bg-gradient-to-r from-red-50 via-white to-rose-50 dark:from-red-900/20 dark:via-zinc-900/20 dark:to-rose-900/20 rounded-lg p-4 shadow flex flex-col gap-2">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1"><i class="bi bi-exclamation-circle"></i> Valor Pendente</p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-diamond"></i> R$ {{ number_format($sale->amount_due, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Pagamentos -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-credit-card text-indigo-600 dark:text-indigo-400 text-xl"></i>
                        Pagamentos
                    </h2>
                    <div class="flex gap-2">
                        <a href="{{ route('sales.add-payments', $sale->id) }}"
                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm transition-colors duration-200 flex items-center gap-1 shadow">
                            <i class="bi bi-plus-lg"></i>
                            Adicionar
                        </a>
                        @if($sale->payments->count() > 0)
                        <a href="{{ route('sales.edit-payments', $sale->id) }}"
                            class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm transition-colors duration-200 flex items-center gap-1 shadow">
                            <i class="bi bi-pencil"></i>
                            Editar
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Lista de Pagamentos -->
                @if($sale->payments->count() > 0)
                <div class="space-y-3">
                    @foreach($sale->payments as $payment)
                    <div class="p-3 bg-gradient-to-r from-white via-indigo-50 to-green-50 dark:from-zinc-800 dark:via-indigo-900/20 dark:to-green-900/20 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center gap-4 shadow">
                        <div class="flex-1 flex flex-col gap-1">
                            <p class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-1">
                                <i class="bi bi-cash-coin text-green-600"></i> R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}
                            </p>
                            <p class="text-base text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                <i class="bi bi-credit-card-2-front"></i> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center gap-1">
                                <i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="bi bi-credit-card text-xl text-gray-400"></i>
                    </div>
                    <p class="text-base text-gray-500 dark:text-gray-400">
                        Nenhum pagamento registrado ainda.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Pagamento -->
    @if($showPaymentModal)
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-cash-coin text-green-600"></i> Confirmar Pagamento
                </h3>
                <button wire:click="closePaymentModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            @if($selectedParcela)
            <div class="mb-4 p-3 bg-gradient-to-r from-indigo-50 to-green-50 dark:from-indigo-900/20 dark:to-green-900/20 rounded-lg border border-indigo-200 dark:border-indigo-700">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Parcela {{ $selectedParcela->numero_parcela }}</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    R$ {{ number_format($selectedParcela->valor, 2, ',', '.') }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Vencimento: {{ \Carbon\Carbon::parse($selectedParcela->data_vencimento)->format('d/m/Y') }}
                </p>
            </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-credit-card mr-1"></i> Método de Pagamento
                </label>
                <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
                    <option value="dinheiro">💵 Dinheiro</option>
                    <option value="cartao_debito">💳 Cartão de Débito</option>
                    <option value="cartao_credito">💳 Cartão de Crédito</option>
                    <option value="pix">⚡ PIX</option>
                    <option value="transferencia">🏦 Transferência</option>
                    <option value="cheque">🧾 Cheque</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-calendar-event mr-1"></i> Data do Pagamento
                </label>
                <input type="date" wire:model="paymentDate" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-700 dark:text-white">
            </div>

            <div class="flex gap-3 justify-end">
                <button wire:click="closePaymentModal" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">
                    Cancelar
                </button>
                <button wire:click="confirmPayment" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-1">
                    <i class="bi bi-check-circle"></i> Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modais de Confirmação de Exclusão dos Produtos -->
    @if($sale->saleItems->count() > 0)
        @foreach($sale->saleItems as $item)
        <div id="popup-modal-{{ $item->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
                    <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="popup-modal-{{ $item->id }}">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-4 md:p-5 text-center">
                        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Tem certeza que deseja remover este produto?</h3>
                        <p class="mb-5 text-sm text-gray-400">{{ $item->product->name }}</p>
                        <button data-modal-hide="popup-modal-{{ $item->id }}" type="button" wire:click="removeSaleItem({{ $item->id }})" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                            Sim, remover
                        </button>
                        <button data-modal-hide="popup-modal-{{ $item->id }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>