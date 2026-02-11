<div class="p-6 space-y-6">
    {{-- Header --}}
    <x-sales-header 
        title="Pedidos do Mercado Livre"
        icon="bi-cart-check-fill"
        icon-color="text-blue-500"
        :back-route="route('mercadolivre.products')" />

    {{-- Filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Busca --}}
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                    <i class="bi bi-search"></i> Buscar
                </label>
                <input type="text" 
                       wire:model.live.debounce.500ms="searchTerm"
                       placeholder="ID do pedido ou nome do comprador..."
                       class="w-full px-4 py-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                    <i class="bi bi-funnel-fill"></i> Status
                </label>
                <select wire:model.live="statusFilter"
                        class="w-full px-4 py-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    <option value="">Todos</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="payment_required">Aguardando Pagamento</option>
                    <option value="payment_in_process">Pagamento em Processo</option>
                    <option value="paid">Pago</option>
                    <option value="fulfilled">Entregue</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>

            {{-- Ações --}}
            <div class="flex items-end gap-2">
                <button type="button" wire:click="loadOrders" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="loadOrders">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                    </span>
                    <span wire:loading wire:target="loadOrders">
                        <i class="bi bi-arrow-repeat animate-spin"></i>
                    </span>
                </button>
                <button type="button" wire:click="clearFilters"
                        class="px-4 py-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold transition-all">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Datas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                    <i class="bi bi-calendar"></i> Data Inicial
                </label>
                <input type="date" 
                       wire:model.live="dateFrom"
                       class="w-full px-4 py-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                    <i class="bi bi-calendar"></i> Data Final
                </label>
                <input type="date" 
                       wire:model.live="dateTo"
                       class="w-full px-4 py-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>
        </div>
    </div>

    {{-- Lista de Pedidos --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        {{-- Header da tabela --}}
        <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Pedidos ({{ count($orders) }})
            </h3>
            @if($loading)
                <span class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    <i class="bi bi-arrow-repeat animate-spin"></i> Carregando...
                </span>
            @endif
        </div>

        {{-- Tabela --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Pedido
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Comprador
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Data
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $order)
                        @php
                            $statusInfo = $this->getStatusBadge($order['status'] ?? '');
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-cart-fill text-blue-500"></i>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                                            #{{ $order['id'] ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ count($order['order_items'] ?? []) }} item(s)
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $order['buyer']['nickname'] ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    ID: {{ $order['buyer']['id'] ?? 'N/A' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ $this->formatDate($order['date_created'] ?? now()) }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $this->formatPrice($order['total_amount'] ?? 0) }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $statusInfo['color'] }}-100 dark:bg-{{ $statusInfo['color'] }}-900/20 text-{{ $statusInfo['color'] }}-700 dark:text-{{ $statusInfo['color'] }}-400">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" 
                                            wire:click="viewOrderDetails('{{ $order['id'] }}')"
                                            class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/40 transition-all"
                                            title="Ver detalhes">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button type="button" 
                                            wire:click="importOrder('{{ $order['id'] }}')"
                                            class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/40 transition-all"
                                            title="Importar pedido">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-inbox text-6xl mb-3"></i>
                                    <p class="text-lg font-semibold mb-1">Nenhum pedido encontrado</p>
                                    <p class="text-sm">Ajuste os filtros ou aguarde novos pedidos</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de Detalhes --}}
    @if($showDetailsModal && $selectedOrder)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click="closeDetailsModal">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-y-auto" wire:click.stop>
                {{-- Header --}}
                <div class="sticky top-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-cart-check-fill text-blue-500"></i>
                            Pedido #{{ $selectedOrder['id'] }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $this->formatDate($selectedOrder['date_created']) }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetailsModal"
                            class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center">
                        <i class="bi bi-x-lg text-slate-600 dark:text-slate-400"></i>
                    </button>
                </div>

                {{-- Conteúdo --}}
                <div class="p-6 space-y-6">
                    {{-- Informações do Comprador --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">
                            <i class="bi bi-person-fill text-blue-500"></i> Comprador
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase mb-1">Nome</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $selectedOrder['buyer']['nickname'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400 text-xs uppercase mb-1">ID</p>
                                <p class="font-mono text-slate-900 dark:text-white">{{ $selectedOrder['buyer']['id'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Itens do Pedido --}}
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">
                            <i class="bi bi-box-seam-fill text-purple-500"></i> Itens do Pedido
                        </h4>
                        <div class="space-y-2">
                            @foreach($selectedOrder['order_items'] ?? [] as $item)
                                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $item['item']['thumbnail'] ?? '' }}" 
                                             alt="{{ $item['item']['title'] ?? 'Produto' }}"
                                             class="w-16 h-16 object-cover rounded-lg">
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ $item['item']['title'] ?? 'Produto' }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Quantidade: {{ $item['quantity'] ?? 1 }}x
                                            </p>
                                        </div>
                                    </div>
                                    <p class="font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $this->formatPrice($item['unit_price'] ?? 0) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Totais --}}
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center justify-between text-lg">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Total do Pedido</span>
                            <span class="font-black text-emerald-700 dark:text-emerald-400">
                                {{ $this->formatPrice($selectedOrder['total_amount'] ?? 0) }}
                            </span>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="flex gap-3">
                        <button type="button" 
                                wire:click="importOrder('{{ $selectedOrder['id'] }}')"
                                class="flex-1 px-6 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <i class="bi bi-download"></i> Importar para o Sistema
                        </button>
                        <button type="button" 
                                wire:click="closeDetailsModal"
                                class="px-6 py-3 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-all">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-toast-notifications />
</div>
