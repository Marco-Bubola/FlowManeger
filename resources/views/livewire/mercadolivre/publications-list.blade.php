<div>
    {{-- Header com sales-header component --}}
    <x-sales-header
        title="Publicações Mercado Livre"
        description="Gerencie suas publicações e kits no marketplace">
        <x-slot:breadcrumb>
            <nav class="flex items-center gap-2 text-sm mb-2">
                <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Dashboard
                </a>
                <i class="bi bi-chevron-right text-xs text-slate-400"></i>
                <a href="{{ route('mercadolivre.products') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    Mercado Livre
                </a>
                <i class="bi bi-chevron-right text-xs text-slate-400"></i>
                <span class="text-slate-700 dark:text-slate-300 font-medium">Publicações</span>
            </nav>
        </x-slot:breadcrumb>
        <x-slot:actions>
            <a href="{{ route('mercadolivre.products') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200">
                <i class="bi bi-plus-circle"></i>
                Nova Publicação
            </a>
        </x-slot:actions>
    </x-sales-header>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Ativas</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Kits</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $stats['kits'] }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Com Erro</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['errors'] }}</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Filters --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por título ou ID..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todos Status</option>
                        <option value="active">Ativo</option>
                        <option value="paused">Pausado</option>
                        <option value="closed">Fechado</option>
                        <option value="under_review">Em Revisão</option>
                    </select>
                    
                    <select wire:model.live="typeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todos Tipos</option>
                        <option value="simple">Simples</option>
                        <option value="kit">Kit</option>
                    </select>
                    
                    <select wire:model.live="syncFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todas Syncs</option>
                        <option value="synced">Sincronizado</option>
                        <option value="pending">Pendente</option>
                        <option value="error">Erro</option>
                    </select>
                </div>
            </div>
            
            {{-- Publications List --}}
            <div class="space-y-4">
                @forelse($publications as $publication)
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3 flex-wrap">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">
                                    {{ $publication->title }}
                                </h3>
                                
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                                    {{ $publication->publication_type === 'kit' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' }}">
                                    <i class="bi {{ $publication->publication_type === 'kit' ? 'bi-box-seam' : 'bi-box' }}"></i>
                                    {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                </span>
                                
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                                    {{ $publication->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : '' }}
                                    {{ $publication->status === 'paused' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                                    {{ $publication->status === 'closed' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : '' }}">
                                    <i class="bi {{ $publication->status === 'active' ? 'bi-check-circle-fill' : ($publication->status === 'paused' ? 'bi-pause-circle' : 'bi-x-circle') }}"></i>
                                    {{ ucfirst($publication->status) }}
                                </span>
                                
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg
                                    {{ $publication->sync_status === 'synced' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : '' }}
                                    {{ $publication->sync_status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                                    {{ $publication->sync_status === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : '' }}">
                                    <i class="bi {{ $publication->sync_status === 'synced' ? 'bi-arrow-repeat' : ($publication->sync_status === 'error' ? 'bi-exclamation-circle' : 'bi-clock') }}"></i>
                                    {{ $publication->sync_status === 'synced' ? 'Sincronizado' : ($publication->sync_status === 'error' ? 'Erro' : 'Pendente') }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                    <i class="bi bi-tag-fill text-blue-600 dark:text-blue-400"></i>
                                    <span><strong>ML ID:</strong> {{ $publication->ml_item_id ?: 'Não publicado' }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                    <i class="bi bi-cash-coin text-green-600 dark:text-green-400"></i>
                                    <span><strong>Preço:</strong> R$ {{ number_format($publication->price, 2, ',', '.') }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                    <i class="bi bi-box-seam text-purple-600 dark:text-purple-400"></i>
                                    <span><strong>Produtos:</strong> {{ $publication->products->count() }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                    <i class="bi bi-boxes text-orange-600 dark:text-orange-400"></i>
                                    <span><strong>Disponível:</strong> {{ $publication->calculateAvailableQuantity() }}</span>
                                </div>
                            </div>
                            
                            {{-- Produtos do Kit/Publicação --}}
                            @if($publication->products->count() > 0)
                            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                    <i class="bi bi-list-check"></i>
                                    Produtos desta publicação:
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($publication->products as $product)
                                    <div class="flex items-center gap-2 p-2 bg-white dark:bg-slate-900 rounded-lg">
                                        @if($product->image && $product->image !== 'product-placeholder.png')
                                            <img src="{{ asset('storage/' . $product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-10 h-10 object-cover rounded-md">
                                        @else
                                            <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-md flex items-center justify-center">
                                                <i class="bi bi-image text-slate-400"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">
                                                {{ $product->name }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Estoque: {{ $product->stock_quantity }} | 
                                                R$ {{ number_format($product->price_sale ?? $product->price, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            @if($publication->ml_permalink)
                            <div class="mt-3">
                                <a href="{{ $publication->ml_permalink }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Ver publicação no Mercado Livre
                                </a>
                            </div>
                            @endif
                            
                            @if($publication->error_message)
                            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 mt-0.5"></i>
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ $publication->error_message }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            @if($publication->ml_item_id)
                                <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}" 
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-lg font-medium shadow-lg shadow-blue-500/30 hover:shadow-xl transition-all duration-200">
                                    <i class="bi bi-pencil-square"></i>
                                    Editar
                                </a>
                            @else
                                <a href="{{ route('mercadolivre.products.publish', $publication->products->first()->id) }}" 
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 rounded-lg font-medium shadow-lg shadow-green-500/30 hover:shadow-xl transition-all duration-200">
                                    <i class="bi bi-send-fill"></i>
                                    Publicar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-gray-600 mb-2">Nenhuma publicação encontrada</p>
                    <p class="text-sm text-gray-500 mb-4">Crie sua primeira publicação no Mercado Livre</p>
                    <a href="{{ route('mercadolivre.products') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Criar Publicação
                    </a>
                </div>
                @endforelse
            </div>
            
            {{-- Pagination --}}
            @if($publications->hasPages())
            <div class="mt-6">
                {{ $publications->links() }}
            </div>
            @endif
            
        </div>
    </div>
</div>
