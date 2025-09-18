@props(['sale'])

<div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-500 group transform hover:-translate-y-1">

    <!-- Header da Venda -->
    <div class="relative h-16 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 flex items-center justify-between px-4">
        <!-- Status Badge -->
        <div class="flex items-center gap-3">
            @php
                $statusConfig = [
                    'finalizada' => ['bg' => 'bg-green-500', 'icon' => 'bi-check-circle-fill', 'text' => 'Finalizada'],
                    'pendente' => ['bg' => 'bg-yellow-500', 'icon' => 'bi-clock-fill', 'text' => 'Pendente'],
                    'cancelada' => ['bg' => 'bg-red-500', 'icon' => 'bi-x-circle-fill', 'text' => 'Cancelada'],
                ];
                $config = $statusConfig[$sale->status] ?? ['bg' => 'bg-gray-500', 'icon' => 'bi-question-circle-fill', 'text' => ucfirst($sale->status)];
            @endphp

            <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
                <div class="w-2 h-2 {{ $config['bg'] }} rounded-full"></div>
                <span class="text-white text-xs font-medium">{{ $config['text'] }}</span>
            </div>
        </div>

        <!-- ID da Venda -->
        <div class="text-white text-sm font-bold bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
            #{{ $sale->id }}
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="p-4 space-y-4">
        <!-- Info do Cliente -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-circle text-indigo-600 text-lg"></i>
                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                    {{ $sale->client->name ?? 'Cliente não informado' }}
                </h3>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <i class="bi bi-calendar3 text-xs"></i>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Valor Total -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-3 border border-green-200 dark:border-green-800">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-green-700 dark:text-green-300">Valor Total</span>
                <span class="text-lg font-bold text-green-800 dark:text-green-200">
                    R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Produtos (Preview) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <i class="bi bi-box text-indigo-600"></i>
                    Produtos
                </h4>
                <span class="text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded-full">
                    {{ $sale->saleItems->count() }} item(s)
                </span>
            </div>

            @if($sale->saleItems->isNotEmpty())
                <div class="space-y-1 max-h-20 overflow-y-auto">
                    @foreach($sale->saleItems->take(2) as $item)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-zinc-700 rounded-lg px-3 py-2">
                            <span class="text-xs font-mono text-gray-800 dark:text-gray-200 truncate">
                                {{ $item->product->product_code ?? 'N/A' }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $item->quantity }}x</span>
                                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                    R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach

                    @if($sale->saleItems->count() > 2)
                        <div class="text-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                +{{ $sale->saleItems->count() - 2 }} mais...
                            </span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Status de Pagamento -->
        @php
            $totalPaid = $sale->payments->sum('amount');
            $remainingAmount = $sale->total_price - $totalPaid;
            $paymentPercentage = $sale->total_price > 0 ? ($totalPaid / $sale->total_price) * 100 : 0;
        @endphp

        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Pagamento</span>
                <span class="font-medium {{ $remainingAmount <= 0 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $remainingAmount <= 0 ? 'Pago' : 'R$ ' . number_format($remainingAmount, 2, ',', '.') . ' pendente' }}
                </span>
            </div>

            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-300"
                     style="width: {{ $paymentPercentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="px-4 pb-4">
        <div class="flex gap-2">
            <!-- Ver Detalhes -->
            <a href="{{ route('sales.show', $sale) }}"
               class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-all duration-200 border border-blue-200 dark:border-blue-800">
                <i class="bi bi-eye mr-1"></i>Ver
            </a>

            <!-- Editar -->
            <a href="{{ route('sales.edit', $sale) }}"
               class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg transition-all duration-200 border border-emerald-200 dark:border-emerald-800">
                <i class="bi bi-pencil mr-1"></i>Editar
            </a>

            <!-- Menu Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/30 hover:bg-gray-100 dark:hover:bg-gray-900/50 rounded-lg transition-all duration-200 border border-gray-200 dark:border-gray-800">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-zinc-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-zinc-700"
                     style="display: none;">

                    <div class="py-2">
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
            </div>
        </div>
    </div>
</div>
