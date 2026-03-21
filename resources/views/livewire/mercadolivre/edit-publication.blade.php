<div class="edit-publication-page  w-full  mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/edit-publication-ultrawide.css') }}">
    <div class="  space-y-6">

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- HEADER                                                     --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <x-sales-header
            title="Editar Publicação"
            description="Gerencie todos os detalhes da sua publicação no Mercado Livre"
            :backRoute="route('mercadolivre.publications')">
            <x-slot name="breadcrumb">
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-1">
                    <a href="{{ route('mercadolivre.products') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Produtos ML</a>
                    <i class="bi bi-chevron-right text-xs"></i>
                    <a href="{{ route('mercadolivre.publications') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Publicações</a>
                    <i class="bi bi-chevron-right text-xs"></i>
                    <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $publication->ml_item_id ?? 'Editar' }}</span>
                </div>
            </x-slot>
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-3">
                    @if($publication->status === 'active')
                        <button wire:click="pausePublication"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500/20 border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-300 text-sm font-semibold hover:bg-amber-500/30 transition-all shadow-sm">
                            <i class="bi bi-pause-fill"></i>
                            Pausar
                        </button>
                    @elseif($publication->status === 'paused')
                        <button wire:click="activatePublication"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/20 border border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-300 text-sm font-semibold hover:bg-emerald-500/30 transition-all shadow-sm">
                            <i class="bi bi-play-fill"></i>
                            Ativar
                        </button>
                    @endif
                    <button wire:click="updatePublication" wire:loading.attr="disabled" wire:target="updatePublication"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                        <i class="bi bi-check-lg" wire:loading.remove wire:target="updatePublication"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="updatePublication"></i>
                        Salvar e enviar ao ML
                    </button>
                </div>
            </x-slot>
        </x-sales-header>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- HERO BANNER                                                --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500/10 via-purple-500/8 to-pink-500/10 dark:from-indigo-500/20 dark:via-purple-500/15 dark:to-pink-500/20 border border-indigo-200/50 dark:border-indigo-800/50 shadow-xl">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-transparent via-white/40 to-transparent dark:via-white/5"></div>
            <div class="relative px-5 py-5 sm:px-7 sm:py-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-2 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-200/80 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 text-xs font-mono font-bold">{{ $publication->ml_item_id }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $publication->status === 'active' ? 'bg-emerald-500/25 text-emerald-800 dark:text-emerald-200 border border-emerald-400/40' : '' }} {{ $publication->status === 'paused' ? 'bg-amber-500/25 text-amber-800 dark:text-amber-200 border border-amber-400/40' : '' }} {{ $publication->status === 'closed' ? 'bg-red-500/25 text-red-800 dark:text-red-200 border border-red-400/40' : '' }}">{{ ucfirst($publication->status) }}</span>
                            <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-700 dark:text-purple-300 text-xs font-bold border border-purple-400/30">{{ $publicationType === 'kit' ? 'Kit' : 'Simples' }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $publication->sync_status === 'synced' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : '' }} {{ $publication->sync_status === 'pending' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : '' }} {{ $publication->sync_status === 'error' ? 'bg-red-500/15 text-red-700 dark:text-red-300' : '' }}">
                                <i class="bi {{ $publication->sync_status === 'synced' ? 'bi-check-circle' : ($publication->sync_status === 'error' ? 'bi-exclamation-circle' : 'bi-clock') }} mr-1"></i>{{ ucfirst($publication->sync_status ?? 'N/A') }}
                            </span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">{{ $publication->title }}</h2>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button wire:click="refreshFromMl" wire:loading.attr="disabled" wire:target="refreshFromMl"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-500/20 border border-blue-400/50 dark:border-blue-600/50 text-blue-700 dark:text-blue-300 text-sm font-semibold hover:bg-blue-500/30 transition-all"
                            title="Atualizar dados do ML">
                            <i class="bi bi-arrow-down-circle" wire:loading.remove wire:target="refreshFromMl"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshFromMl"></i>
                            <span class="hidden sm:inline">Atualizar do ML</span>
                        </button>
                        @if($publication->ml_item_id)
                        @php $mlUrl = $publication->ml_permalink ?: 'https://articulo.mercadolibre.com.br/' . $publication->ml_item_id; @endphp
                        <a href="{{ $mlUrl }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-yellow-400/90 hover:bg-yellow-500 text-slate-900 text-sm font-bold shadow-md transition-all">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span class="hidden sm:inline">Ver no ML</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerta de erro --}}
        @if($publication->error_message)
        <div class="flex items-start gap-3 px-5 py-3.5 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5"></i>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-800 dark:text-red-200">Erro na última sincronização</p>
                <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">{{ $publication->error_message }}</p>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- GRID PRINCIPAL                                             --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── COLUNA PRINCIPAL (2/3) ── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: TÍTULO E DESCRIÇÃO                   --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-500/10 to-purple-500/8 dark:from-indigo-900/30 dark:to-purple-900/20 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25"><i class="bi bi-type"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Título e Descrição</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Título do anúncio *</label>
                                <span class="text-xs font-mono {{ strlen($title) > 60 ? 'text-red-500 font-bold' : 'text-slate-400' }}">{{ strlen($title) }}/60</span>
                            </div>
                            <input type="text" wire:model.live="title" maxlength="255"
                                class="w-full px-4 py-3 rounded-xl border-2 {{ strlen($title) > 60 ? 'border-red-300 dark:border-red-700 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 transition-all placeholder-slate-400"
                                placeholder="Título exibido no Mercado Livre">
                            @if(strlen($title) > 60)
                                <p class="mt-1 text-xs text-red-500"><i class="bi bi-exclamation-triangle mr-1"></i>ML limita títulos a 60 caracteres</p>
                            @endif
                            @error('title')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Descrição</label>
                                <span class="text-xs font-mono text-slate-400">{{ strlen($description) }} caracteres</span>
                            </div>
                            <textarea wire:model="description" rows="5"
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-y placeholder-slate-400"
                                placeholder="Descrição do produto (texto simples, sem HTML)"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: PREÇO E DETALHES COMERCIAIS          --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-emerald-500/10 to-teal-500/8 dark:from-emerald-900/30 dark:to-teal-900/20 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25"><i class="bi bi-currency-dollar"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Preço e Detalhes Comerciais</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        {{-- Preço + Tipo de Anúncio --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Preço *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 font-bold text-sm">R$</span>
                                    <input type="number" wire:model="price" step="0.01" min="0.01"
                                        class="w-full pl-11 pr-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-lg font-bold">
                                </div>
                                @error('price')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de anúncio</label>
                                <select wire:model="listingType"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                    <option value="gold_special">Gold Special (Premium)</option>
                                    <option value="gold_pro">Gold Pro</option>
                                    <option value="gold">Gold</option>
                                    <option value="free">Clássico (Free)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Condição + Garantia --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Condição</label>
                                <select wire:model="condition"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                    <option value="new">Novo</option>
                                    <option value="used">Usado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Garantia</label>
                                <input type="text" wire:model="warranty"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder-slate-400"
                                    placeholder="Ex: 90 dias contra defeitos">
                            </div>
                        </div>

                        {{-- Categoria + Tipo de Publicação --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Categoria ML (ID)</label>
                                <input type="text" wire:model="mlCategoryId"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-mono text-sm placeholder-slate-400"
                                    placeholder="Ex: MLB1234">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de publicação</label>
                                <select wire:model="publicationType"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                    <option value="simple">Simples (1 produto)</option>
                                    <option value="kit">Kit (múltiplos produtos)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Frete e Retirada --}}
                        <div class="flex flex-wrap gap-x-8 gap-y-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                            <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" wire:model="freeShipping" class="w-5 h-5 rounded-lg border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 transition-all">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors block">Frete grátis</span>
                                    <span class="text-[11px] text-slate-400 leading-none">O custo do frete é absorvido por você</span>
                                </div>
                            </label>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" wire:model="localPickup" class="w-5 h-5 rounded-lg border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 transition-all">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors block">Retirada local</span>
                                    <span class="text-[11px] text-slate-400 leading-none">Comprador pode retirar pessoalmente</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: PRODUTOS VINCULADOS                  --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    {{-- Header --}}
                    <div class="px-5 py-4 bg-gradient-to-r from-violet-500/10 via-purple-500/8 to-fuchsia-500/10 dark:from-violet-900/30 dark:via-purple-900/20 dark:to-fuchsia-900/30 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
                                    <i class="bi bi-box-seam-fill text-sm"></i>
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Produtos Vinculados</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ count($products) }} produto(s) · {{ $publicationType === 'kit' ? 'Kit' : 'Simples' }}
                                    </p>
                                </div>
                            </div>
                            <button wire:click="toggleProductSelector"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                    {{ $showProductSelector
                                        ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-200 dark:hover:bg-red-900/50'
                                        : 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-md shadow-emerald-500/25'
                                    }} text-sm font-semibold transition-all">
                                <i class="bi {{ $showProductSelector ? 'bi-x-lg' : 'bi-plus-lg' }} text-xs"></i>
                                {{ $showProductSelector ? 'Fechar busca' : 'Adicionar produto' }}
                            </button>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">

                        {{-- ── Painel de busca inline ── --}}
                        @if($showProductSelector)
                        <div class="rounded-xl border-2 border-dashed border-emerald-300 dark:border-emerald-700/60 bg-emerald-50/50 dark:bg-emerald-950/20 p-4 space-y-4">
                            <div class="relative">
                                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 text-sm pointer-events-none"></i>
                                <input type="text" wire:model.live.debounce.300ms="productSearch"
                                    placeholder="Buscar por nome, código ou EAN..."
                                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-emerald-200 dark:border-emerald-700/60 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 transition-all">
                            </div>

                            @if($this->searchableProducts->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[320px] overflow-y-auto pr-1">
                                @foreach($this->searchableProducts as $sp)
                                <button type="button" wire:click="addProduct({{ $sp->id }})" wire:key="sp-{{ $sp->id }}"
                                    class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-all group cursor-pointer text-left w-full">
                                    <img src="{{ $sp->image_url }}" alt="{{ $sp->name }}"
                                        class="w-11 h-11 rounded-lg object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0"
                                        onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ ucwords($sp->name) }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $sp->product_code }}</span>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Est: {{ $sp->stock_quantity }}</span>
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">R$ {{ number_format($sp->price, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="bi bi-plus-lg text-xs"></i>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-6 text-slate-400 dark:text-slate-500">
                                <i class="bi bi-search text-2xl block mb-2"></i>
                                <p class="text-sm">{{ strlen($productSearch) >= 2 ? 'Nenhum produto encontrado' : 'Digite para buscar produtos...' }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- ── Lista de produtos vinculados ── --}}
                        @if(!empty($products))
                        <div class="space-y-3">
                            @foreach($products as $idx => $product)
                            <div class="group relative flex flex-col sm:flex-row items-start gap-4 p-4 rounded-2xl bg-gradient-to-r from-slate-50/80 to-white dark:from-slate-800/60 dark:to-slate-800/40 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-lg transition-all duration-200">
                                {{-- Número de ordem --}}
                                <span class="absolute -top-2 -left-2 w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-[10px] font-bold flex items-center justify-center shadow-lg shadow-indigo-500/30 z-10">{{ $idx + 1 }}</span>

                                {{-- Imagem --}}
                                <div class="flex-shrink-0">
                                    <img src="{{ $product['image_url'] ?? asset('images/placeholder.png') }}" alt="{{ $product['name'] }}"
                                        class="w-20 h-20 rounded-xl object-cover border-2 border-slate-200 dark:border-slate-700 shadow-sm"
                                        onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
                                </div>

                                {{-- Info do produto --}}
                                <div class="flex-1 min-w-0 space-y-2">
                                    <h4 class="font-bold text-slate-900 dark:text-white truncate text-sm">{{ $product['name'] }}</h4>

                                    {{-- Badges informativos --}}
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-400 text-[11px] font-mono">
                                            <i class="bi bi-upc-scan text-[9px]"></i> {{ $product['product_code'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg {{ ($product['stock_quantity'] ?? 0) <= 5 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' }} text-[11px] font-semibold">
                                            <i class="bi bi-box-seam text-[9px]"></i> {{ $product['stock_quantity'] }} em estoque
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[11px] font-semibold">
                                            <i class="bi bi-tag text-[9px]"></i> R$ {{ number_format($product['unit_cost'] ?? 0, 2, ',', '.') }} custo
                                        </span>
                                        @if(!empty($product['price']) && $product['price'] != ($product['unit_cost'] ?? 0))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-[11px] font-semibold">
                                            <i class="bi bi-currency-dollar text-[9px]"></i> R$ {{ number_format($product['price'], 2, ',', '.') }} venda
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Qtd e cálculo --}}
                                    <div class="flex flex-wrap items-center gap-3 pt-1">
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">Qtd/venda:</label>
                                            <input type="number" min="1" value="{{ $product['quantity'] }}"
                                                wire:change="updateProductQuantity({{ $product['id'] }}, $event.target.value)"
                                                class="w-16 px-2 py-1.5 text-center text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all font-bold">
                                        </div>
                                        @if($publicationType === 'kit')
                                        <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <i class="bi bi-arrow-right text-[10px]"></i>
                                            Rende <strong class="text-indigo-600 dark:text-indigo-400">{{ floor(($product['stock_quantity'] ?? 0) / max(1, $product['quantity'])) }}</strong> vendas
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Botão remover --}}
                                <button wire:click="removeProduct({{ $product['id'] }})" wire:confirm="Remover este produto da publicação?"
                                    class="self-start p-2.5 rounded-xl text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all sm:opacity-0 sm:group-hover:opacity-100"
                                    title="Remover produto">
                                    <i class="bi bi-trash text-base"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        {{-- Resumo dos produtos --}}
                        @php
                            $totalCost = array_sum(array_map(fn($p) => ($p['unit_cost'] ?? 0) * ($p['quantity'] ?? 1), $products));
                            $totalProducts = count($products);
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 border border-indigo-200/60 dark:border-indigo-800/40">
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-slate-600 dark:text-slate-400">
                                    <strong class="text-slate-900 dark:text-white">{{ $totalProducts }}</strong> produto(s)
                                </span>
                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                <span class="text-slate-600 dark:text-slate-400">
                                    Custo: <strong class="text-amber-600 dark:text-amber-400">R$ {{ number_format($totalCost, 2, ',', '.') }}</strong>
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                <i class="bi bi-box-seam"></i>
                                {{ $availableQuantity }} un. disponíveis
                            </div>
                        </div>

                        @else
                        {{-- Estado vazio --}}
                        <div class="text-center py-12">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                                <i class="bi bi-box-seam text-3xl text-slate-400 dark:text-slate-500"></i>
                            </div>
                            <h4 class="font-semibold text-slate-700 dark:text-slate-300">Nenhum produto vinculado</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Clique em "Adicionar produto" para vincular produtos a esta publicação.</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: IMAGENS DA PUBLICAÇÃO                --}}
                {{-- ═══════════════════════════════════════════ --}}
                @if(!empty($pictures))
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-sky-500/10 to-cyan-500/8 dark:from-sky-900/30 dark:to-cyan-900/20 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-sky-500/25"><i class="bi bi-images"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Imagens do Anúncio</h3>
                        <span class="ml-1 text-sm font-normal text-slate-500">({{ count($pictures) }})</span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                            @foreach($pictures as $pic)
                            <div class="aspect-square rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-700 hover:border-sky-400 dark:hover:border-sky-600 transition-all hover:shadow-lg group cursor-pointer">
                                <img src="{{ is_array($pic) ? ($pic['secure_url'] ?? $pic['url'] ?? '') : $pic }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    onerror="this.parentElement.style.display='none'">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: ATRIBUTOS DO MERCADO LIVRE           --}}
                {{-- ═══════════════════════════════════════════ --}}
                @if(!empty($mlAttributes))
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-orange-500/10 to-rose-500/8 dark:from-orange-900/30 dark:to-rose-900/20 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/25"><i class="bi bi-list-columns-reverse"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Atributos do ML</h3>
                        <span class="ml-1 text-sm font-normal text-slate-500">({{ count($mlAttributes) }})</span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($mlAttributes as $attr)
                            @php
                                $attrName = is_array($attr) ? ($attr['name'] ?? $attr['id'] ?? 'N/A') : (string) $attr;
                                $attrValue = is_array($attr) ? ($attr['value_name'] ?? $attr['value_id'] ?? '-') : '-';
                            @endphp
                            <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 truncate">{{ $attrName }}</span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[55%] text-right">{{ $attrValue }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- ── SIDEBAR (1/3) ── --}}
            <div class="space-y-6">

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: INFORMAÇÕES DA PUBLICAÇÃO            --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-800/80 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center text-white"><i class="bi bi-info-circle"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Informações</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        {{-- ML ID --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">ID no ML</span>
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $publication->ml_item_id ?? '—' }}</span>
                        </div>

                        {{-- Status --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Status</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                                {{ $publication->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : '' }}
                                {{ $publication->status === 'paused' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : '' }}
                                {{ $publication->status === 'closed' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}">
                                {{ ucfirst($publication->status) }}
                            </span>
                        </div>

                        {{-- Sync --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sincronização</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                                {{ $publication->sync_status === 'synced' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : '' }}
                                {{ $publication->sync_status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : '' }}
                                {{ $publication->sync_status === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}">
                                {{ ucfirst($publication->sync_status ?? 'N/A') }}
                            </span>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700/60 pt-3"></div>

                        {{-- Qtd disponível --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Qtd. disponível</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $availableQuantity }}</span>
                        </div>

                        {{-- Tipo --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tipo</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                {{ $publicationType === 'kit' ? 'Kit' : 'Simples' }}
                            </span>
                        </div>

                        {{-- Listagem --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Listagem</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ ucwords(str_replace('_', ' ', $listingType)) }}</span>
                        </div>

                        {{-- Condição --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Condição</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $condition === 'new' ? 'Novo' : 'Usado' }}</span>
                        </div>

                        {{-- Frete --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Frete grátis</span>
                            <span class="text-xs font-semibold {{ $freeShipping ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">{{ $freeShipping ? 'Sim' : 'Não' }}</span>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700/60 pt-3"></div>

                        {{-- Última sync --}}
                        @if($publication->last_sync_at)
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Última sync</span>
                            <span class="text-xs text-slate-600 dark:text-slate-400" title="{{ $publication->last_sync_at->format('d/m/Y H:i:s') }}">{{ $publication->last_sync_at->diffForHumans() }}</span>
                        </div>
                        @endif

                        {{-- Criado --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Criado em</span>
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $publication->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>

                        {{-- Atualizado --}}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Atualizado em</span>
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $publication->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>

                        {{-- Erro --}}
                        @if($publication->error_message)
                        <div class="mt-2 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-start gap-2">
                                <i class="bi bi-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
                                <p class="text-xs text-red-700 dark:text-red-300">{{ $publication->error_message }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: AÇÕES RÁPIDAS                        --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white"><i class="bi bi-lightning-charge-fill text-sm"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Ações Rápidas</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <button wire:click="refreshFromMl" wire:loading.attr="disabled" wire:target="refreshFromMl"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl border border-blue-200 dark:border-blue-800/60 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-sm font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all">
                            <i class="bi bi-arrow-down-circle" wire:loading.remove wire:target="refreshFromMl"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="refreshFromMl"></i>
                            Atualizar do ML
                        </button>
                        <button wire:click="syncPublication" wire:loading.attr="disabled" wire:target="syncPublication"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all">
                            <i class="bi bi-arrow-repeat" wire:loading.remove wire:target="syncPublication"></i>
                            <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="syncPublication"></i>
                            Sincronizar estoque
                        </button>
                        @if($publication->status === 'active')
                        <button wire:click="pausePublication"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm font-semibold hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all">
                            <i class="bi bi-pause-fill"></i>
                            Pausar publicação
                        </button>
                        @elseif($publication->status === 'paused')
                        <button wire:click="activatePublication"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all">
                            <i class="bi bi-play-fill"></i>
                            Ativar publicação
                        </button>
                        @endif
                        @if($publication->ml_item_id)
                        @php $mlUrl = $publication->ml_permalink ?: 'https://articulo.mercadolibre.com.br/' . $publication->ml_item_id; @endphp
                        <a href="{{ $mlUrl }}" target="_blank" rel="noopener"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-yellow-400 to-amber-400 hover:from-yellow-500 hover:to-amber-500 text-slate-900 text-sm font-bold transition-all shadow-sm">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Ver no Mercado Livre
                        </a>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: HISTÓRICO / LOGS                     --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center text-white"><i class="bi bi-journal-text"></i></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Histórico</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        @if($stockLogs->count() > 0)
                        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($stockLogs as $log)
                            <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold
                                        {{ $log->operation_type === 'ml_sale' ? 'text-red-600 dark:text-red-400' : '' }}
                                        {{ $log->operation_type === 'sync_to_ml' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                        {{ $log->operation_type === 'manual_update' ? 'text-amber-600 dark:text-amber-400' : '' }}">
                                        <i class="bi {{ $log->operation_type === 'ml_sale' ? 'bi-cart-dash' : ($log->operation_type === 'sync_to_ml' ? 'bi-arrow-repeat' : 'bi-pencil') }}"></i>
                                        {{ $log->getOperationDescription() }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $log->created_at->format('d/m H:i') }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $log->product->name ?? 'Produto' }}:
                                    <span class="font-mono">{{ $log->quantity_before }} → {{ $log->quantity_after }}</span>
                                    <span class="font-bold {{ $log->quantity_change > 0 ? 'text-emerald-600' : 'text-red-500' }}">({{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }})</span>
                                </p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 text-sm text-slate-400 dark:text-slate-500">
                            <i class="bi bi-journal text-2xl block mb-2"></i>
                            Nenhum histórico
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- CARD: ZONA DE PERIGO                       --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div class="rounded-2xl border-2 border-red-200 dark:border-red-900/60 bg-red-50/50 dark:bg-red-950/20 shadow-xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-red-200/80 dark:border-red-900/40">
                        <h3 class="flex items-center gap-2 text-base font-bold text-red-700 dark:text-red-300">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Zona de Perigo
                        </h3>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-red-600/80 dark:text-red-400/80 mb-3">Esta ação é irreversível e remove a publicação do sistema.</p>
                        <button wire:click="deletePublication"
                            wire:confirm="Tem certeza que deseja deletar esta publicação? Esta ação não pode ser desfeita."
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition-all shadow-md hover:shadow-lg">
                            <i class="bi bi-trash3"></i>
                            Deletar publicação
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BARRA FLUTUANTE DE SALVAR                                  --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="sticky bottom-4 z-30 pointer-events-none">
            <div class="max-w-lg mx-auto pointer-events-auto">
                <div class="flex items-center justify-between gap-4 px-5 py-3 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/80 dark:border-slate-700/80 shadow-2xl shadow-slate-900/10 dark:shadow-black/30">
                    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Salvar alterações</span>
                    </div>
                    <button wire:click="updatePublication" wire:loading.attr="disabled" wire:target="updatePublication"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-bold shadow-lg transition-all disabled:opacity-50">
                        <i class="bi bi-check-lg" wire:loading.remove wire:target="updatePublication"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="updatePublication"></i>
                        Salvar e enviar ao ML
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
