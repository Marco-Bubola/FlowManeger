<div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4 mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Integração com Mercado Livre</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Publique e gerencie seus produtos no marketplace</p>
                    </div>
                </div>
                
                {{-- Botão Configurações --}}
                <a href="{{ route('mercadolivre.settings') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="bi bi-gear"></i>
                    Configurações
                </a>
            </div>
        </div>

        {{-- Alerta se não conectado --}}
        @if(!$isConnected)
            <div class="mb-6 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-950/50 dark:to-yellow-950/50 border-2 border-amber-200 dark:border-amber-800 shadow-xl">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center flex-shrink-0 shadow-lg">
                            <i class="bi bi-exclamation-triangle text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-2">
                                Conexão Necessária
                            </h3>
                            <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                                Você precisa conectar sua conta do Mercado Livre antes de poder publicar produtos. 
                                Configure suas credenciais na página de configurações.
                            </p>
                            <a href="{{ route('mercadolivre.settings') }}" 
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                                <i class="bi bi-link-45deg text-lg"></i>
                                Conectar Agora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Busca --}}
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nome, código ou barcode..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition-all">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            
            {{-- Filtro Status --}}
            <select wire:model.live="statusFilter" 
                    class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition-all">
                <option value="all">📦 Todos os Produtos</option>
                <option value="published">✅ Publicados no ML</option>
                <option value="unpublished">❌ Não Publicados</option>
            </select>
            
            {{-- Filtro Categoria --}}
            <select wire:model.live="categoryFilter" 
                    class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition-all">
                <option value="all">📂 Todas as Categorias</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id_category }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Grid de Produtos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="relative rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden transition-all duration-200 hover:shadow-2xl hover:scale-[1.02]">
                    
                    {{-- Badge Status --}}
                    <div class="absolute top-3 right-3 z-10">
                        @if($product->mercadoLivreProduct)
                            @if($product->mercadoLivreProduct->status === 'active')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-lg">
                                    ✅ Ativo
                                </span>
                            @elseif($product->mercadoLivreProduct->status === 'paused')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-lg">
                                    ⏸️ Pausado
                                </span>
                            @elseif($product->mercadoLivreProduct->status === 'closed')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-lg">
                                    🚫 Encerrado
                                </span>
                            @elseif($product->mercadoLivreProduct->status === 'under_review')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500 text-white shadow-lg">
                                    🔍 Em Revisão
                                </span>
                            @elseif($product->mercadoLivreProduct->status === 'inactive')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-500 text-white shadow-lg">
                                    💤 Inativo
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-500 text-white shadow-lg">
                                    {{ $product->mercadoLivreProduct->status }}
                                </span>
                            @endif
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-500 text-white shadow-lg">
                                ❌ Não Publicado
                            </span>
                        @endif
                    </div>
                    
                    {{-- Imagem --}}
                    <div class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="bi bi-image text-6xl text-slate-400"></i>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Conteúdo --}}
                    <div class="p-5">
                        {{-- Título --}}
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 min-h-[3.5rem]">
                            {{ $product->name }}
                        </h3>
                        
                        {{-- Código --}}
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                            <i class="bi bi-upc-scan"></i>
                            {{ $product->product_code }}
                        </p>
                        
                        {{-- Info Grid --}}
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            {{-- Preço --}}
                            <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-950/50 dark:to-green-950/50 border border-emerald-200 dark:border-emerald-800 p-3">
                                <p class="text-xs text-emerald-700 dark:text-emerald-300 mb-1">
                                    Preço
                                </p>
                                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                                </p>
                            </div>
                            
                            {{-- Estoque --}}
                            <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/50 dark:to-indigo-950/50 border border-blue-200 dark:border-blue-800 p-3">
                                <p class="text-xs text-blue-700 dark:text-blue-300 mb-1">
                                    Estoque
                                </p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ $product->stock_quantity }} un
                                </p>
                            </div>
                        </div>
                        
                        {{-- Validação para ML --}}
                        @php
                            $mlValidation = $product->isReadyForMercadoLivre();
                        @endphp
                        
                        @if(!$mlValidation['ready'])
                            <div class="mb-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 p-3">
                                <div class="flex items-start gap-2 mb-2">
                                    <i class="bi bi-exclamation-triangle text-amber-600 dark:text-amber-400 text-lg flex-shrink-0 mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-amber-800 dark:text-amber-300 mb-1">
                                            Atenção: Produto com pendências
                                        </p>
                                        <ul class="text-xs text-amber-700 dark:text-amber-400 space-y-0.5">
                                            @foreach($mlValidation['errors'] as $error)
                                                <li class="flex items-start gap-1">
                                                    <span class="text-amber-600 dark:text-amber-500 flex-shrink-0">•</span>
                                                    <span>{{ $error }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 p-2.5 flex items-center gap-2">
                                <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400"></i>
                                <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                    Pronto para publicar no ML
                                </p>
                            </div>
                        @endif
                        
                        {{-- Link ML --}}
                        @if($product->mercadoLivreProduct && $product->mercadoLivreProduct->ml_permalink)
                            <a href="{{ $product->mercadoLivreProduct->ml_permalink }}" 
                               target="_blank"
                               class="block mb-3 text-sm text-blue-600 dark:text-blue-400 hover:underline truncate">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Ver no Mercado Livre
                            </a>
                        @elseif($product->mercadoLivreProduct && $product->mercadoLivreProduct->ml_item_id)
                            <p class="mb-3 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                <i class="bi bi-tag"></i> {{ $product->mercadoLivreProduct->ml_item_id }}
                                @if($product->mercadoLivreProduct->error_message)
                                    <span class="block text-red-500 text-[10px] mt-0.5 font-sans" title="{{ $product->mercadoLivreProduct->error_message }}">
                                        <i class="bi bi-exclamation-triangle"></i> {{ \Illuminate\Support\Str::limit($product->mercadoLivreProduct->error_message, 50) }}
                                    </span>
                                @endif
                            </p>
                        @endif
                        
                        {{-- Botões de Ação --}}
                        <div class="space-y-2">
                            @if($product->mercadoLivreProduct)
                                {{-- Produto publicado --}}
                                @if($product->mercadoLivreProduct->status === 'active')
                                    {{-- Ativo: Sincronizar + Pausar + Encerrar --}}
                                    <div class="flex gap-2">
                                        <button wire:click="syncProduct({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="syncProduct({{ $product->id }})"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <span wire:loading.remove wire:target="syncProduct({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat"></i> Sincronizar
                                            </span>
                                            <span wire:loading wire:target="syncProduct({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="pauseProduct({{ $product->id }})"
                                                wire:confirm="Tem certeza que deseja PAUSAR este anúncio?"
                                                wire:loading.attr="disabled"
                                                class="px-3 py-2 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50"
                                                title="Pausar anúncio">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                        <button wire:click="closeProduct({{ $product->id }})"
                                                wire:confirm="Tem certeza que deseja ENCERRAR este anúncio? Essa ação é permanente no ML."
                                                wire:loading.attr="disabled"
                                                class="px-3 py-2 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50"
                                                title="Encerrar anúncio">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @elseif($product->mercadoLivreProduct->status === 'paused')
                                    {{-- Pausado: Reativar + Verificar Status + Encerrar --}}
                                    <div class="flex gap-2">
                                        <button wire:click="activateProduct({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="activateProduct({{ $product->id }})"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <span wire:loading.remove wire:target="activateProduct({{ $product->id }})">
                                                <i class="bi bi-play-circle"></i> Reativar
                                            </span>
                                            <span wire:loading wire:target="activateProduct({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="checkMLStatus({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="checkMLStatus({{ $product->id }})"
                                                class="px-3 py-2 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50"
                                                title="Verificar status no ML">
                                            <span wire:loading.remove wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <span wire:loading wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="closeProduct({{ $product->id }})"
                                                wire:confirm="Tem certeza que deseja ENCERRAR este anúncio? Essa ação é permanente no ML."
                                                wire:loading.attr="disabled"
                                                class="px-3 py-2 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50"
                                                title="Encerrar anúncio">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @elseif(in_array($product->mercadoLivreProduct->status, ['closed', 'inactive']))
                                    {{-- Encerrado/Inativo: Verificar + Excluir registro + Republicar --}}
                                    <div class="rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 p-2.5 mb-2">
                                        <p class="text-xs text-red-700 dark:text-red-300 flex items-center gap-1.5">
                                            <i class="bi bi-info-circle"></i>
                                            Anúncio encerrado no ML. Exclua o registro para republicar.
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="checkMLStatus({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="checkMLStatus({{ $product->id }})"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <span wire:loading.remove wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-search"></i> Verificar
                                            </span>
                                            <span wire:loading wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="deleteLocalRecord({{ $product->id }})"
                                                wire:confirm="Excluir o registro de publicação? Isso permitirá republicar o produto."
                                                wire:loading.attr="disabled"
                                                wire:target="deleteLocalRecord({{ $product->id }})"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <span wire:loading.remove wire:target="deleteLocalRecord({{ $product->id }})">
                                                <i class="bi bi-trash"></i> Excluir Registro
                                            </span>
                                            <span wire:loading wire:target="deleteLocalRecord({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                @else
                                    {{-- Outros status: Verificar + Excluir registro --}}
                                    <div class="flex gap-2">
                                        <button wire:click="checkMLStatus({{ $product->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="checkMLStatus({{ $product->id }})"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <span wire:loading.remove wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-search"></i> Verificar Status
                                            </span>
                                            <span wire:loading wire:target="checkMLStatus({{ $product->id }})">
                                                <i class="bi bi-arrow-repeat animate-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="deleteLocalRecord({{ $product->id }})"
                                                wire:confirm="Excluir o registro de publicação? Isso permitirá republicar o produto."
                                                wire:loading.attr="disabled"
                                                class="flex-1 px-3 py-2 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white font-semibold text-sm hover:scale-105 transition-all shadow-lg disabled:opacity-50">
                                            <i class="bi bi-trash"></i> Excluir Registro
                                        </button>
                                    </div>
                                @endif
                            @else
                                {{-- Produto não publicado --}}
                                @php
                                    $canPublish = $product->isReadyForMercadoLivre()['ready'];
                                @endphp
                                
                                <a href="{{ $canPublish ? route('mercadolivre.products.publish', $product->id) : '#' }}"
                                   @if(!$canPublish) onclick="event.preventDefault();" title="Corrija as pendências antes de publicar" @endif
                                   class="block w-full px-4 py-3 rounded-xl font-bold text-sm text-center transition-all {{ $canPublish ? 'bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600 text-white shadow-lg shadow-yellow-500/30 hover:shadow-xl hover:shadow-yellow-500/50 hover:scale-105' : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed' }}">
                                    <i class="bi bi-upload"></i>
                                    {{ $canPublish ? 'Publicar no ML' : 'Corrigir Pendências' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                {{-- Estado vazio --}}
                <div class="col-span-full">
                    <div class="rounded-2xl bg-slate-100 dark:bg-slate-900/50 border-2 border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                            <i class="bi bi-inbox text-4xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">
                            Nenhum produto encontrado
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                            Tente ajustar os filtros ou adicione novos produtos
                        </p>
                        <a href="{{ route('products.create') }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                            <i class="bi bi-plus-lg"></i>
                            Adicionar Produto
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</div>
