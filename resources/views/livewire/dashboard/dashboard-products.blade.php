<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-800">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard de Produtos</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Análise completa de produtos e vendas</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Principal
                    </a>
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerenciar Produtos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Produtos</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalProdutos) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produtosSemEstoque }} sem estoque</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stock Total -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Estoque Total</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalProdutosEstoque) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">unidades</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Ticket -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ticket Médio</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">por venda</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Balance -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Produtos</p>
                        <p class="text-2xl font-bold {{ $totalSaldoProdutos >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format(abs($totalSaldoProdutos), 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">lucro estimado</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 {{ $totalSaldoProdutos >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-full">
                        <svg class="w-6 h-6 {{ $totalSaldoProdutos >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Top Products -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales by Category Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Vendas por Categoria</h3>
                <div class="h-80">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Top Products Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Destaques</h3>
                <div class="space-y-4">
                    @if($produtoMaiorEstoque)
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Maior Estoque</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $produtoMaiorEstoque->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $produtoMaiorEstoque->stock_quantity }} unidades</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                    </div>
                    @endif

                    @if($produtoMaisVendido)
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mais Vendido</p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $produtoMaisVendido->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $produtoMaisVendido->product_code }}</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Sem Estoque</p>
                            <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $produtosSemEstoque }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">produtos</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Most Sold Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Produtos Mais Vendidos</h3>
                <div class="space-y-3">
                    @forelse($produtosMaisVendidos as $index => $produto)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $produto['name'] ?? $produto['product_code'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ number_format($produto['total_vendido']) }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto vendido</p>
                    @endforelse
                </div>
            </div>

            <!-- Highest Revenue Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Maior Receita</h3>
                <div class="space-y-3">
                    @forelse($produtosMaiorReceita as $index => $produto)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full">
                                <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $produto['name'] ?? $produto['product_code'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($produto['receita_total'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto vendido</p>
                    @endforelse
                </div>
            </div>

            <!-- Highest Profit Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Maior Lucro</h3>
                <div class="space-y-3">
                    @forelse($produtoMaiorLucro as $index => $produto)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                                <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $produto['name'] ?? $produto['product_code'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-purple-600 dark:text-purple-400">R$ {{ number_format($produto['lucro_total'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto vendido</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent and Stuck Products -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Produtos Recentes</h3>
                <div class="space-y-3">
                    @forelse($ultimosProdutos as $produto)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $produto['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }} • {{ $produto['stock_quantity'] }} unidades</p>
                        </div>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">R$ {{ number_format($produto['price_sale'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto cadastrado</p>
                    @endforelse
                </div>
            </div>

            <!-- Stuck Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Produtos Parados (60 dias)</h3>
                <div class="space-y-3">
                    @forelse($produtosParados as $produto)
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $produto['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $produto['product_code'] }} • {{ $produto['stock_quantity'] }} unidades</p>
                        </div>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400">R$ {{ number_format($produto['price_sale'], 2, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhum produto parado</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($dadosGraficoPizza);
    
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category_name),
            datasets: [{
                data: categoryData.map(item => item.total_sold),
                backgroundColor: [
                    '#34D399',
                    '#60A5FA',
                    '#F87171',
                    '#FBBF24',
                    '#A78BFA',
                    '#F472B6',
                    '#38BDF8',
                    '#FB923C'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' unidades';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
