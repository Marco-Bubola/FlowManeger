@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
@endpush

<div class="w-full">
    <div class="container mx-auto px-4 py-6 max-w-7xl">

        {{-- Header Moderno Estilo Sales-Index --}}
        <div class="mb-8">
            <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-6">
                    {{-- Breadcrumb --}}
                    <div class="flex items-center gap-2 text-sm text-white/80 mb-4">
                        <a href="{{ route('mercadolivre.publications') }}" class="hover:text-white transition-colors" wire:navigate>
                            <i class="bi bi-list-ul"></i> Publicações
                        </a>
                        <i class="bi bi-chevron-right text-xs"></i>
                        <span class="text-white font-medium">Detalhes</span>
                    </div>

                    {{-- Título e Ações --}}
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            {{-- Imagem Principal --}}
                            @if($publication->pictures && count($publication->pictures) > 0)
                                <div class="flex-shrink-0">
                                    <img src="{{ $publication->pictures[0] }}" 
                                         alt="{{ $publication->title }}" 
                                         class="w-20 h-20 rounded-xl shadow-lg object-cover border-2 border-white/20">
                                </div>
                            @endif

                            <div>
                                <h1 class="text-3xl font-bold text-white mb-2">
                                    {{ $publication->title }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-3">
                                    @php
                                        $statusConfig = match($publication->status) {
                                            'active' => ['bg' => 'bg-green-500', 'text' => 'Ativo', 'icon' => 'check-circle-fill'],
                                            'paused' => ['bg' => 'bg-amber-500', 'text' => 'Pausado', 'icon' => 'pause-circle-fill'],
                                            'closed' => ['bg' => 'bg-red-500', 'text' => 'Fechado', 'icon' => 'x-circle-fill'],
                                            'under_review' => ['bg' => 'bg-purple-500', 'text' => 'Em Revisão', 'icon' => 'search'],
                                            default => ['bg' => 'bg-gray-500', 'text' => 'Desconhecido', 'icon' => 'question-circle'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $statusConfig['bg'] }} text-white text-sm font-bold shadow-lg">
                                        <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                        {{ $statusConfig['text'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-white text-sm font-medium">
                                        <i class="bi bi-{{ $publication->publication_type === 'kit' ? 'box-seam' : 'box' }}"></i>
                                        {{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                    </span>

                                    @if($publication->ml_item_id)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-white text-sm font-mono">
                                            <i class="bi bi-link-45deg"></i>
                                            {{ $publication->ml_item_id }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('mercadolivre.publications.edit', $publication->id) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-medium transition-all"
                               wire:navigate>
                                <i class="bi bi-pencil"></i>
                                Editar
                            </a>

                            @if($publication->ml_permalink)
                                <a href="{{ $publication->ml_permalink }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-medium transition-all">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Ver no ML
                                </a>
                            @endif

                            <button wire:click="syncToMercadoLivre" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-medium transition-all">
                                <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin"></i>
                                <span wire:loading.remove>Sincronizar</span>
                                <span wire:loading>Sincronizando...</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 px-6 pb-6">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs font-medium mb-1">Preço</div>
                        <div class="text-2xl font-bold text-white">R$ {{ number_format($publication->price, 2, ',', '.') }}</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs font-medium mb-1">Disponível</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_stock_available'] }}</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs font-medium mb-1">Produtos</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_products'] }}</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs font-medium mb-1">Vendas</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_sales'] }}</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs font-medium mb-1">Receita</div>
                        <div class="text-2xl font-bold text-white">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteúdo Principal --}}
        <div class="grid lg:grid-cols-3 gap-6">
            
            {{-- Coluna Principal - 2/3 --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Informações Básicas --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-info-circle text-indigo-500"></i>
                            Informações da Publicação
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($publication->description)
                            <div>
                                <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Descrição</h4>
                                <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $publication->description }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Categoria ML</h4>
                                <p class="text-slate-900 dark:text-slate-100 font-mono text-sm">{{ $publication->ml_category_id }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Tipo de Anúncio</h4>
                                <p class="text-slate-900 dark:text-slate-100">{{ ucfirst(str_replace('_', ' ', $publication->listing_type)) }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Condição</h4>
                                <p class="text-slate-900 dark:text-slate-100">{{ $publication->condition === 'new' ? 'Novo' : 'Usado' }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Frete Grátis</h4>
                                <p class="text-slate-900 dark:text-slate-100">{{ $publication->free_shipping ? 'Sim' : 'Não' }}</p>
                            </div>

                            @if($publication->warranty)
                                <div class="col-span-2">
                                    <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Garantia</h4>
                                    <p class="text-slate-900 dark:text-slate-100">{{ $publication->warranty }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">Criado por</h4>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <i class="bi bi-person text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div>
                                    <p class="text-slate-900 dark:text-slate-100 font-medium">{{ $publication->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $publication->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Produtos Vinculados --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-box-seam text-purple-500"></i>
                            Produtos Vinculados ({{ $publication->products->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($publication->products as $product)
                                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-lg object-cover shadow">
                                    
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">{{ $product->name }}</h4>
                                        <div class="flex flex-wrap gap-2 text-sm">
                                            <span class="text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-upc-scan"></i> {{ $product->product_code }}
                                            </span>
                                            <span class="text-slate-600 dark:text-slate-400">
                                                <i class="bi bi-stack"></i> Estoque: {{ $product->stock_quantity }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ $product->pivot->quantity }}x
                                        </div>
                                        <div class="text-xs text-slate-500">por venda</div>
                                        @if($product->pivot->unit_cost)
                                            <div class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                                R$ {{ number_format($product->pivot->unit_cost, 2, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Histórico de Estoque --}}
                @if(count($stockHistory) > 0)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-clock-history text-emerald-500"></i>
                                Histórico de Movimentações
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                @foreach($stockHistory as $log)
                                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg text-sm">
                                        @php
                                            $operationConfig = match($log['operation']) {
                                                'ml_sale' => ['icon' => 'cart-check', 'color' => 'text-green-600', 'label' => 'Venda ML'],
                                                'manual_adjustment' => ['icon' => 'pencil', 'color' => 'text-blue-600', 'label' => 'Ajuste Manual'],
                                                'ml_sync' => ['icon' => 'arrow-repeat', 'color' => 'text-purple-600', 'label' => 'Sincronização'],
                                                'rollback' => ['icon' => 'arrow-counterclockwise', 'color' => 'text-red-600', 'label' => 'Estorno'],
                                                default => ['icon' => 'question-circle', 'color' => 'text-gray-600', 'label' => 'Outro'],
                                            };
                                        @endphp
                                        <i class="bi bi-{{ $operationConfig['icon'] }} {{ $operationConfig['color'] }} text-lg"></i>
                                        <div class="flex-1">
                                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ $operationConfig['label'] }}
                                                @if($log['product'])
                                                    - {{ $log['product']['name'] }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold {{ $log['quantity_changed'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $log['quantity_changed'] > 0 ? '+' : '' }}{{ $log['quantity_changed'] }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                Antes: {{ $log['quantity_before'] }} → Depois: {{ $log['quantity_after'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar - 1/3 --}}
            <div class="space-y-6">
                
                {{-- Imagens --}}
                @if($publication->pictures && count($publication->pictures) > 0)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-images text-pink-500"></i>
                                Imagens ({{ count($publication->pictures) }})
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($publication->pictures as $picture)
                                    <a href="{{ $picture }}" target="_blank" class="group relative aspect-square rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all">
                                        <img src="{{ $picture }}" alt="Imagem da publicação" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center">
                                            <i class="bi bi-arrows-fullscreen text-white opacity-0 group-hover:opacity-100 text-2xl transition-opacity"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Status de Sincronização --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-cloud-check text-emerald-500"></i>
                            Sincronização
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Status</div>
                            @php
                                $syncConfig = match($publication->sync_status) {
                                    'synced' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'label' => 'Sincronizado'],
                                    'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300', 'label' => 'Pendente'],
                                    'error' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Erro'],
                                    default => ['bg' => 'bg-gray-100 dark:bg-gray-900/30', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Desconhecido'],
                                };
                            @endphp
                            <span class="inline-block px-3 py-1.5 rounded-lg {{ $syncConfig['bg'] }} {{ $syncConfig['text'] }} text-sm font-medium">
                                {{ $syncConfig['label'] }}
                            </span>
                        </div>

                        @if($publication->last_sync_at)
                            <div>
                                <div class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1">Última Sincronização</div>
                                <p class="text-slate-900 dark:text-slate-100">{{ $publication->last_sync_at->format('d/m/Y H:i:s') }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $publication->last_sync_at->diffForHumans() }}</p>
                            </div>
                        @endif

                        @if($publication->error_message)
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <div class="text-sm font-semibold text-red-700 dark:text-red-300 mb-1 flex items-center gap-1">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Erro
                                </div>
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $publication->error_message }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Atributos ML --}}
                @if($publication->ml_attributes && count($publication->ml_attributes) > 0)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="bi bi-tags text-amber-500"></i>
                                Atributos ML
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                @foreach($publication->ml_attributes as $attr)
                                    <div class="flex items-start gap-2 text-sm">
                                        <i class="bi bi-dot text-indigo-500 text-lg"></i>
                                        <div class="flex-1">
                                            <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $attr['name'] ?? $attr['id'] }}</div>
                                            <div class="text-slate-600 dark:text-slate-400">{{ $attr['value_name'] ?? $attr['value'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
