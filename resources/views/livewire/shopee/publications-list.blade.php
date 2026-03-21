{{-- ============================================================
     SHOPEE — LISTA DE PUBLICAÇÕES + LOGS DE ERRO
     ============================================================ --}}
<div class="shopee-publications-page min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-6xl mx-auto px-4 py-5 sm:px-6 lg:px-8 space-y-5">

        {{-- ─── Cabeçalho ─── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">Publicações — Shopee</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Gerenciar anúncios ativos e sincronização</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($ordersCount > 0)
                <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                    {{ $ordersCount }} pedido(s) pendente(s)
                </span>
                @endif
                <button wire:click="importOrders" wire:loading.attr="disabled"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl
                               bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                               hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                    <span wire:loading.remove wire:target="importOrders">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importar Pedidos
                    </span>
                    <span wire:loading wire:target="importOrders" class="flex items-center gap-1">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Importando...
                    </span>
                </button>
                <a href="{{ route('shopee.products.publish.create') }}"
                   class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl text-white
                          transition-all hover:opacity-90 active:scale-95"
                   style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nova Publicação
                </a>
            </div>
        </div>

        {{-- ─── Filtros ─── --}}
        <div class="flex flex-wrap gap-2">
            <div class="flex-1 min-w-[160px] relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.400ms="searchTerm" type="text" placeholder="Buscar por título ou item ID..."
                       class="w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                              rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
            </div>
            <select wire:model.live="statusFilter"
                    class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                           rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                <option value="">Todos os status</option>
                <option value="published">Publicado</option>
                <option value="draft">Rascunho</option>
                <option value="inactive">Inativo</option>
            </select>
            <select wire:model.live="syncFilter"
                    class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600
                           rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 dark:text-white">
                <option value="">Qualquer sync</option>
                <option value="synced">Sincronizado</option>
                <option value="pending">Pendente</option>
                <option value="error">Com erro</option>
            </select>
        </div>

        {{-- ─── Logs de Erro (destaque) ─── --}}
        @if($errorLogs->isNotEmpty())
        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-4">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                    Erros de Sincronização Recentes
                </h3>
            </div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($errorLogs as $log)
                <div class="flex items-start gap-2.5 text-xs">
                    <span class="text-red-400 text-xs flex-shrink-0 mt-0.5">
                        {{ $log->created_at->format('d/m H:i') }}
                    </span>
                    <span class="px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 font-medium flex-shrink-0">
                        {{ $log->sync_type }}
                    </span>
                    <span class="text-red-700 dark:text-red-300 flex-1">{{ $log->message }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ─── Tabela de Publicações ─── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($publications->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center opacity-30"
                     style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhuma publicação Shopee encontrada.</p>
                <a href="{{ route('shopee.products.publish.create') }}"
                   class="mt-3 inline-block px-4 py-2 text-sm font-medium rounded-xl text-white"
                   style="background: linear-gradient(135deg,#EE4D2D,#FF6633)">
                    Criar primeira publicação
                </a>
            </div>
            @else
            {{-- Desktop: tabela --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Produto</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Item ID</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Preço</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estoque</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sync</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Última sync</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($publications as $pub)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white text-sm line-clamp-1">{{ $pub->title }}</p>
                                <p class="text-xs text-gray-400">{{ $pub->products->count() }} produto(s)</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($pub->shopee_item_id)
                                <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $pub->shopee_item_id }}</span>
                                @else
                                <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-orange-600">
                                R$ {{ number_format($pub->price, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $pub->available_quantity }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                $statusMap = [
                                    'published' => ['bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', 'Publicado'],
                                    'draft'     => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400', 'Rascunho'],
                                    'inactive'  => ['bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', 'Inativo'],
                                    'deleted'   => ['bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'Removido'],
                                ];
                                [$statusClass, $statusLabel] = $statusMap[$pub->status] ?? ['bg-gray-100 text-gray-500', $pub->status];
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                $syncMap = [
                                    'synced'   => ['bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', '✓ Sincronizado'],
                                    'pending'  => ['bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', '⏳ Pendente'],
                                    'error'    => ['bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', '✗ Erro'],
                                    'updating' => ['bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', '↻ Atualizando'],
                                ];
                                [$syncClass, $syncLabel] = $syncMap[$pub->sync_status] ?? ['bg-gray-100 text-gray-500', $pub->sync_status];
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $syncClass }}">{{ $syncLabel }}</span>
                                @if($pub->sync_status === 'error' && $pub->error_message)
                                <p class="text-xs text-red-600 dark:text-red-400 mt-0.5 line-clamp-1" title="{{ $pub->error_message }}">
                                    {{ $pub->error_message }}
                                </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $pub->last_sync_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: cards --}}
            <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($publications as $pub)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium text-gray-900 dark:text-white text-sm line-clamp-2 flex-1">{{ $pub->title }}</p>
                        @php
                        $syncMap = [
                            'synced'  => 'bg-green-100 text-green-700',
                            'pending' => 'bg-blue-100 text-blue-700',
                            'error'   => 'bg-red-100 text-red-700',
                        ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0 {{ $syncMap[$pub->sync_status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ $pub->sync_status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>R$ {{ number_format($pub->price, 2, ',', '.') }}</span>
                        <span>·</span>
                        <span>{{ $pub->available_quantity }} em estoque</span>
                        @if($pub->shopee_item_id)
                        <span>·</span>
                        <span class="font-mono">{{ $pub->shopee_item_id }}</span>
                        @endif
                    </div>
                    @if($pub->sync_status === 'error' && $pub->error_message)
                    <p class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded">
                        {{ $pub->error_message }}
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Paginação --}}
        {{ $publications->links() }}

    </div>
</div>
