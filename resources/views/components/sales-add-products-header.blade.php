@props([
    'sale',
    'backRoute' => null,
    'totalSelected' => 0
])

<!-- Header Moderno para Adicionar Produtos -->
<div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-emerald-50/90 to-green-50/80 dark:from-slate-800/90 dark:via-emerald-900/30 dark:to-green-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-emerald-400/20 via-green-400/20 to-teal-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-emerald-400/10 to-blue-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="relative px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                @if($backRoute)
                <!-- Botão voltar melhorado -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-emerald-50 dark:from-slate-800 dark:to-slate-700 hover:from-emerald-50 hover:to-green-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <!-- Ícone principal e título -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 via-green-500 to-teal-500 rounded-2xl shadow-xl shadow-emerald-500/25">
                    <i class="bi bi-plus-circle text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-800 via-emerald-700 to-green-700 dark:from-slate-100 dark:via-emerald-300 dark:to-green-300 bg-clip-text text-transparent">
                        Adicionar Produtos
                    </h1>
                    <div class="flex items-center gap-4">
                        <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">
                            🛒 Venda #{{ $sale->id }}
                        </p>
                        <div class="h-6 w-px bg-slate-300 dark:bg-slate-600"></div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            <i class="bi bi-person-fill text-emerald-600 mr-1"></i>
                            {{ $sale->client->name }}
                        </p>
                        <div class="h-6 w-px bg-slate-300 dark:bg-slate-600"></div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            <i class="bi bi-currency-dollar text-green-600 mr-1"></i>
                            Total: R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status e contadores -->
            <div class="flex items-center gap-4">
                @if($totalSelected > 0)
                <!-- Badge de produtos selecionados -->
                <div class="relative">
                    <div class="bg-gradient-to-r from-emerald-500 to-green-500 text-white px-4 py-2 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="bi bi-cart-check"></i>
                        <span class="font-bold">{{ $totalSelected }}</span>
                        <span class="text-sm">{{ $totalSelected === 1 ? 'produto' : 'produtos' }}</span>
                    </div>
                    <!-- Animação de pulso -->
                    <div class="absolute inset-0 bg-emerald-400 rounded-xl animate-ping opacity-20"></div>
                </div>
                @endif

                <!-- Indicador de status -->
                <div class="flex flex-col items-end gap-1">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium">Selecionando produtos</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Clique nos produtos para adicionar à venda
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
