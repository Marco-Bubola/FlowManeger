@props(['client'])

<div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-500 group transform hover:-translate-y-1">

    <!-- Header do Cliente -->
    <div class="relative h-16 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 flex items-center justify-between px-4">
        <!-- Status Badge -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-white text-xs font-medium">Ativo</span>
            </div>
        </div>

        <!-- Tipo do Cliente -->
        <div class="text-white text-xs font-bold bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
            <i class="bi bi-person mr-1"></i>Cliente
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="p-4 space-y-4">
        <!-- Info do Cliente -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-circle text-indigo-600 text-lg"></i>
                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                    {{ $client->name }}
                </h3>
            </div>

            @if($client->email)
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <i class="bi bi-envelope text-xs"></i>
                <span class="truncate">{{ $client->email }}</span>
            </div>
            @endif

            @if($client->phone)
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <i class="bi bi-telephone text-xs"></i>
                <span>{{ $client->phone }}</span>
            </div>
            @endif

            @if($client->address)
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <i class="bi bi-geo-alt text-xs"></i>
                <span class="truncate">{{ $client->address }}</span>
            </div>
            @endif

            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <i class="bi bi-calendar3 text-xs"></i>
                <span>Cadastrado em {{ $client->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Estatísticas de Compras -->
        @php
            $salesCount = $client->sales()->count();
            $salesTotal = $client->sales()->sum('total_price');
            $lastSale = $client->sales()->latest()->first();
        @endphp

        <div class="grid grid-cols-2 gap-2">
            <!-- Total de Compras -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-3 border border-blue-200 dark:border-blue-800">
                <div class="text-center">
                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Compras</span>
                    <div class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $salesCount }}</div>
                </div>
            </div>

            <!-- Valor Total -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-3 border border-green-200 dark:border-green-800">
                <div class="text-center">
                    <span class="text-xs font-medium text-green-700 dark:text-green-300">Total</span>
                    <div class="text-lg font-bold text-green-900 dark:text-green-100">
                        R$ {{ number_format($salesTotal, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        @if($lastSale)
        <!-- Última Compra -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-3 border border-purple-200 dark:border-purple-800">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-purple-700 dark:text-purple-300">Última Compra</span>
                <span class="text-xs text-purple-600 dark:text-purple-400">{{ $lastSale->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="text-sm font-semibold text-purple-900 dark:text-purple-100 mt-1">
                R$ {{ number_format($lastSale->total_price, 2, ',', '.') }}
            </div>
        </div>
        @endif

        <!-- Ações -->
        <div class="flex justify-center gap-2 pt-2">
            <a href="{{ route('clients.dashboard', $client->id) }}"
               class="flex-1 p-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg transition-colors duration-200 text-center"
               title="Ver dashboard">
                <i class="bi bi-eye text-sm mr-1"></i>
                <span class="text-xs font-medium">Dashboard</span>
            </a>
            <a href="{{ route('clients.edit', $client->id) }}"
               class="flex-1 p-2 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-600 dark:text-green-400 rounded-lg transition-colors duration-200 text-center"
               title="Editar">
                <i class="bi bi-pencil text-sm mr-1"></i>
                <span class="text-xs font-medium">Editar</span>
            </a>
            @if($client->sales->count() == 0)
            <button wire:click="confirmDelete({{ $client->id }})"
                    class="flex-1 p-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg transition-colors duration-200"
                    title="Excluir">
                <i class="bi bi-trash text-sm mr-1"></i>
                <span class="text-xs font-medium">Excluir</span>
            </button>
            @endif
        </div>
    </div>
</div>
