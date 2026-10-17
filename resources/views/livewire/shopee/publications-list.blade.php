{{-- ============================================================
     SHOPEE — LISTA DE PUBLICAÇÕES + LOGS DE ERRO
     ============================================================ --}}
@php
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $statusOpts = ['' => ['Todos', 'bi-grid'], 'published' => ['Publicado', 'bi-check-circle'], 'draft' => ['Rascunho', 'bi-pencil'], 'inactive' => ['Inativo', 'bi-pause-circle']];
    $syncOpts = ['' => ['Qualquer sync', 'bi-arrow-repeat'], 'synced' => ['Sincronizado', 'bi-check2-all'], 'pending' => ['Pendente', 'bi-hourglass-split'], 'error' => ['Com erro', 'bi-exclamation-triangle']];
    $hasFilters = $searchTerm !== '' || $statusFilter !== '' || $syncFilter !== '';
@endphp
<div class="shopee-publications-page min-h-screen" x-data="{ showFilters: false }">
    <div class="w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- ─── Cabeçalho ─── --}}
        <x-page-header title="Publicações" icon="bi-shop" section="shopee" tabs-section="shopee" tabs-active="anuncios"
                       subtitle="Gerenciar anúncios ativos e sincronização">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300"><i class="bi bi-megaphone"></i>{{ $publications->total() }} anúncio{{ $publications->total() == 1 ? '' : 's' }}</span>
                @if($ordersCount > 0)
                    <a href="{{ route('shopee.orders') }}" wire:navigate title="{{ $ordersCount }} pedido(s) pendente(s) de envio" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-500/20 dark:text-blue-300"><i class="bi bi-bag-check"></i>{{ $ordersCount }} a enviar</a>
                @endif
                @if($errorLogs->isNotEmpty())
                    <span title="Erros de sincronização recentes" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300"><i class="bi bi-exclamation-triangle"></i>{{ $errorLogs->count() }} erro(s)</span>
                @endif
            </x-slot:meta>
            <x-slot:actions>
                <button type="button" @click="showFilters = true" title="Filtros" class="{{ $phBtn }}">
                    <i class="bi bi-sliders text-orange-500"></i><span class="hidden sm:inline">Filtros</span>
                    @if($hasFilters)<span class="h-2 w-2 rounded-full bg-orange-500"></span>@endif
                </button>
                <button type="button" wire:click="importOrders" wire:loading.attr="disabled" wire:target="importOrders" title="Importar pedidos recentes da Shopee" class="{{ $phBtn }} disabled:opacity-60">
                    <i class="bi bi-download text-sky-500" wire:loading.remove wire:target="importOrders"></i>
                    <i class="bi bi-arrow-repeat animate-spin text-sky-500" wire:loading wire:target="importOrders"></i>
                    <span class="hidden sm:inline" wire:loading.remove wire:target="importOrders">Importar pedidos</span>
                    <span class="hidden sm:inline" wire:loading wire:target="importOrders">Importando...</span>
                </button>
                <a href="{{ route('shopee.products.publish.create') }}" title="Nova publicação"
                   class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-md shadow-orange-500/25 transition whitespace-nowrap"><i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Nova publicação</span></a>
            </x-slot:actions>
        </x-page-header>

        {{-- ─── Busca e filtros ─── --}}
        <x-list-toolbar model="searchTerm" placeholder="Buscar por título ou item ID...">
            <x-toolbar.group icon="bi-flag">
                @foreach($statusOpts as $key => [$label, $icon])
                    <x-toolbar.chip wire:click="$set('statusFilter', '{{ $key }}')" :active="$statusFilter === $key" :icon="$icon">{{ $label }}</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
            <x-toolbar.group icon="bi-arrow-repeat">
                @foreach($syncOpts as $key => [$label, $icon])
                    @continue($key === '')
                    <x-toolbar.chip wire:click="$set('syncFilter', '{{ $syncFilter === $key ? '' : $key }}')" :active="$syncFilter === $key">{{ $label }}</x-toolbar.chip>
                @endforeach
            </x-toolbar.group>
            @if($hasFilters)
                <x-toolbar.group>
                    <x-toolbar.chip wire:click="clearFilters" icon="bi-x-lg" title="Limpar busca e filtros">Limpar</x-toolbar.chip>
                </x-toolbar.group>
            @endif
        </x-list-toolbar>

        <x-filter-sheet title="Filtros de publicações" subtitle="Situação e sincronização" :active="$hasFilters" target="statusFilter,syncFilter,clearFilters">
            <div class="fm-fs-group">
                <h4><i class="bi bi-flag text-orange-500"></i>Situação</h4>
                <div class="fm-fs-options">
                    @foreach($statusOpts as $key => [$label, $icon])
                        <button type="button" wire:click="$set('statusFilter', '{{ $key }}')" class="fm-fs-opt {{ $statusFilter === $key ? 'is-on' : '' }}"><i class="bi {{ $icon }}"></i>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="fm-fs-group">
                <h4><i class="bi bi-arrow-repeat text-red-500"></i>Sincronização</h4>
                <div class="fm-fs-options">
                    @foreach($syncOpts as $key => [$label, $icon])
                        <button type="button" wire:click="$set('syncFilter', '{{ $key }}')" class="fm-fs-opt {{ $syncFilter === $key ? 'is-on' : '' }}"><i class="bi {{ $icon }}"></i>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </x-filter-sheet>

        {{-- ─── Logs de Erro (destaque) ─── --}}
        @if($errorLogs->isNotEmpty())
        <div class="mb-4 bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-4">
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
                        {{ $log->created_at?->copy()->setTimezone('America/Sao_Paulo')->format('d/m H:i') }}
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
        <div class="{{ $publications->isEmpty() ? '' : 'bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden' }}">
            @if($publications->isEmpty())
            <x-empty-state icon="bi-megaphone" tone="amber"
                           :title="$hasFilters ? 'Nenhum anúncio encontrado' : 'Nenhuma publicação Shopee ainda'"
                           :text="$hasFilters ? 'Nada com essa busca ou filtros. Tente outro termo ou limpe os filtros.' : 'Escolha um produto do estoque e publique na sua loja Shopee.'">
                @if($hasFilters)
                    <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600"><i class="bi bi-x-lg"></i>Limpar filtros</button>
                @endif
                <a href="{{ route('shopee.products.publish.create') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 to-red-500 shadow-md"><i class="bi bi-plus-lg"></i>Criar primeira publicação</a>
            </x-empty-state>
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
                            {{ ['synced' => 'Sincronizado', 'pending' => 'Pendente', 'error' => 'Erro', 'updating' => 'Atualizando'][$pub->sync_status] ?? $pub->sync_status }}
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
        <x-infinite-feed :paginator="$publications" label="publicações" />

    </div>
</div>
