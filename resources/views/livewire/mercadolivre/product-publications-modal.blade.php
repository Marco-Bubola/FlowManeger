<div>
    @if($showModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak>
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

        {{-- Modal --}}
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-5xl transform transition-all">
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    
                    {{-- Header com Gradient --}}
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-800 dark:via-indigo-800 dark:to-purple-800 px-8 py-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                                    <i class="bi bi-list-check text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Publicações do Produto</h2>
                                    @if($product)
                                    <p class="text-sm text-blue-100 mt-1">{{ $product->name }}</p>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="closeModal" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 text-white transition-all duration-200">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                        @if($publications && count($publications) > 0)
                            <div class="space-y-4">
                                @php
                                    $pubCount = 0;
                                    // Garantir que publications é sempre um array iterável
                                    $pubsList = is_array($publications) ? $publications : $publications->toArray();
                                @endphp
                                @foreach($pubsList as $pub)
                                @php
                                    $pubCount++;
                                    // Converter para array se for objeto
                                    $pubArray = is_object($pub) ? (property_exists($pub, 'getAttributes') ? $pub->getAttributes() : (array) $pub) : $pub;
                                    
                                    $statusConfig = match($pubArray['status'] ?? null) {
                                        'active' => ['bg' => 'bg-emerald-500', 'text' => 'Ativo', 'icon' => 'check-circle-fill', 'dot' => 'bg-emerald-500'],
                                        'paused' => ['bg' => 'bg-amber-500', 'text' => 'Pausado', 'icon' => 'pause-circle-fill', 'dot' => 'bg-amber-500'],
                                        'closed' => ['bg' => 'bg-red-500', 'text' => 'Fechado', 'icon' => 'x-circle-fill', 'dot' => 'bg-red-500'],
                                        'under_review' => ['bg' => 'bg-purple-500', 'text' => 'Em Revisão', 'icon' => 'search', 'dot' => 'bg-purple-500'],
                                        default => ['bg' => 'bg-slate-500', 'text' => 'Desconhecido', 'icon' => 'question-circle', 'dot' => 'bg-slate-500'],
                                    };
                                    // Corrigir acesso ao pictures - pode ser array ou null
                                    $firstPicture = null;
                                    $pictures = $pubArray['pictures'] ?? null;
                                    if (is_array($pictures) && count($pictures) > 0) {
                                        $firstPicture = is_string($pictures[0]) ? $pictures[0] : null;
                                    } elseif (is_string($pictures)) {
                                        $decodedPics = json_decode($pictures, true);
                                        $firstPicture = is_array($decodedPics) && count($decodedPics) > 0 && is_string($decodedPics[0]) ? $decodedPics[0] : null;
                                    }
                                @endphp
                                    <div class="group relative bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/80 dark:to-slate-900/80 rounded-2xl border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all duration-300 overflow-hidden shadow-sm hover:shadow-lg">
                                        
                                        {{-- Badge Número e Status --}}
                                        <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-xs font-bold shadow-md border border-slate-200 dark:border-slate-700">
                                                <i class="bi bi-hash"></i>
                                                {{ $pubCount }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $statusConfig['bg'] }} text-white text-xs font-bold shadow-md">
                                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                                {{ $statusConfig['text'] }}
                                            </span>
                                        </div>

                                        <div class="p-6">
                                            <div class="flex gap-5">
                                                {{-- Imagem do Anúncio --}}
                                                <div class="flex-shrink-0">
                                                    @if($firstPicture)
                                                        <div class="relative w-28 h-28 rounded-xl overflow-hidden shadow-md border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700">
                                                            <img src="{{ $firstPicture }}" alt="{{ $pubArray['title'] ?? '' }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                                                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                                        </div>
                                                    @else
                                                        <div class="w-28 h-28 rounded-xl bg-gradient-to-br from-slate-300 to-slate-400 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center shadow-md border border-slate-300 dark:border-slate-600">
                                                            <i class="bi bi-image text-4xl text-slate-500 dark:text-slate-400"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Conteúdo --}}
                                                <div class="flex-1 min-w-0">
                                                    {{-- Título --}}
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-3 pr-20 line-clamp-2">
                                                        {{ $pubArray['title'] ?? 'Sem título' }}
                                                    </h3>
                                                    
                                                    {{-- Info Chips --}}
                                                    <div class="flex flex-wrap gap-2 mb-4">
                                                        {{-- Preço --}}
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                                                            <i class="bi bi-currency-dollar"></i>
                                                            R$ {{ number_format($pubArray['price'] ?? 0, 2, ',', '.') }}
                                                        </span>
                                                        
                                                        {{-- Tipo --}}
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 text-xs font-semibold">
                                                            <i class="bi bi-{{ ($pubArray['publication_type'] ?? 'simple') === 'kit' ? 'boxes' : 'box-seam' }}"></i>
                                                            {{ ($pubArray['publication_type'] ?? 'simple') === 'kit' ? 'Kit' : 'Simples' }}
                                                        </span>
                                                        
                                                        {{-- Quantidade --}}
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                                                            <i class="bi bi-stack"></i>
                                                            {{ $pubArray['available_quantity'] ?? 0 }} disponível
                                                        </span>

                                                        {{-- Tipo de Listagem --}}
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 text-xs font-semibold">
                                                            <i class="bi bi-star-fill"></i>
                                                            {{ match($pubArray['listing_type'] ?? 'free') { 'gold_special' => 'Destaque', 'gold_pro' => 'Premium', 'gold' => 'Gold', 'free' => 'Grátis', default => 'Padrão' } }}
                                                        </span>

                                                        {{-- Quantidade de Produtos --}}
                                                        @php
                                                            $prodCount = 0;
                                                            if (is_array($pubArray['products'] ?? null)) {
                                                                $prodCount = count($pubArray['products']);
                                                            } elseif (is_object($pubArray['products'] ?? null) && method_exists($pubArray['products'], 'count')) {
                                                                $prodCount = $pubArray['products']->count();
                                                            }
                                                        @endphp
                                                        @if($prodCount > 0)
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 text-xs font-semibold">
                                                                <i class="bi bi-box-seam"></i>
                                                                {{ $prodCount }} produto(s)
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Meta Data --}}
                                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                                                        @php
                                                            $createdAt = $pubArray['created_at'] ?? null;
                                                            if (is_string($createdAt)) {
                                                                try {
                                                                    $createdAt = \Carbon\Carbon::parse($createdAt);
                                                                } catch (\Exception $e) {
                                                                    $createdAt = null;
                                                                }
                                                            }
                                                            $lastSyncAt = $pubArray['last_sync_at'] ?? null;
                                                            if (is_string($lastSyncAt)) {
                                                                try {
                                                                    $lastSyncAt = \Carbon\Carbon::parse($lastSyncAt);
                                                                } catch (\Exception $e) {
                                                                    $lastSyncAt = null;
                                                                }
                                                            }
                                                        @endphp
                                                        @if($createdAt)
                                                            <span class="flex items-center gap-1.5">
                                                                <i class="bi bi-calendar3"></i>
                                                                @if(method_exists($createdAt, 'format') ?? false)
                                                                    {{ $createdAt->format('d/m/Y H:i') }}
                                                                @else
                                                                    {{ $createdAt }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                        @if($lastSyncAt)
                                                            <span class="flex items-center gap-1.5">
                                                                <i class="bi bi-arrow-repeat"></i>
                                                                Sincronizado há
                                                                @if(method_exists($lastSyncAt, 'diffForHumans') ?? false)
                                                                    {{ $lastSyncAt->diffForHumans() }}
                                                                @else
                                                                    recentemente
                                                                @endif
                                                            </span>
                                                        @endif
                                                        @if($pubArray['ml_item_id'] ?? null)
                                                            <span class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-mono">
                                                                <i class="bi bi-link-45deg"></i>
                                                                {{ $pubArray['ml_item_id'] }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Ações --}}
                                                    <div class="flex flex-wrap gap-2">
                                                        {{-- Ver Detalhes --}}
                                                        <a href="{{ route('mercadolivre.publications.show', $pubArray['id'] ?? 0) }}" 
                                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md"
                                                           wire:navigate>
                                                            <i class="bi bi-eye"></i>
                                                            <span>Detalhes</span>
                                                        </a>
                                                        
                                                        {{-- Editar --}}
                                                        <a href="{{ route('mercadolivre.publications.edit', $pubArray['id'] ?? 0) }}" 
                                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md"
                                                           wire:navigate>
                                                            <i class="bi bi-pencil-square"></i>
                                                            <span>Editar</span>
                                                        </a>

                                                        {{-- Ver no ML --}}
                                                        @if($pubArray['ml_permalink'] ?? null)
                                                            <a href="{{ $pubArray['ml_permalink'] }}" target="_blank"
                                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                                                                <i class="bi bi-box-arrow-up-right"></i>
                                                                <span>Ver no ML</span>
                                                            </a>
                                                        @endif

                                                        {{-- Status --}}
                                                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg {{ $statusConfig['bg'] }} text-white text-xs font-semibold">
                                                            <div class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }} animate-pulse"></div>
                                                            {{ $statusConfig['text'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Estado Vazio --}}
                            <div class="flex flex-col items-center justify-center py-16">
                                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shadow-md">
                                    <i class="bi bi-inbox text-5xl text-slate-400 dark:text-slate-500"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 text-center max-w-xs mb-6">
                                    Este produto ainda não foi publicado no Mercado Livre
                                </p>
                                @if($product)
                                    <a href="{{ route('mercadolivre.products.publish', $product->id) }}"
                                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white font-semibold transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="bi bi-plus-circle-fill"></i>
                                        <span>Publicar Agora</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 px-8 py-5 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            @if($publications && count($publications) > 0)
                                <span class="font-medium">{{ count($publications) }} {{ count($publications) === 1 ? 'publicação' : 'publicações' }}</span>
                            @endif
                        </div>
                        <button wire:click="closeModal" 
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold transition-all duration-200">
                            <i class="bi bi-x-lg"></i>
                            <span>Fechar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</div>
