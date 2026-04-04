<x-portal-layout title="Catálogo de Produtos">

@push('styles')
<style>
[x-cloak] { display:none !important; }
.product-placeholder { background: linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); }
.dark .product-placeholder { background: linear-gradient(135deg,#0f172a 0%,#1e293b 100%); }
</style>
@endpush

<div x-data="{
    search: '{{ $search }}',
    category: '{{ $category }}',
    viewMode: localStorage.getItem('portal-products-view') || 'grid',
    setView(v) { this.viewMode = v; localStorage.setItem('portal-products-view', v); }
}">

    {{-- Page header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-indigo-700 to-violet-800 rounded-2xl p-5 mb-5 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-br from-black/10 to-transparent"></div>
        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[.2em] mb-2">
                    <i class="fas fa-sparkles text-[9px]"></i> Catálogo Premium
                </div>
                <h1 class="text-lg font-black leading-tight">Produtos disponíveis para pedido</h1>
                <p class="text-sky-200/80 text-xs mt-1">{{ $products->total() }} produto{{ $products->total() !== 1 ? 's' : '' }} em estoque</p>
            </div>
            <a href="{{ route('portal.quotes.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 rounded-xl font-bold text-xs transition-all hover:scale-105 shadow-lg whitespace-nowrap">
                <i class="fas fa-file-invoice text-xs"></i>
                Solicitar Orçamento
            </a>
        </div>
    </div>

    {{-- Filters bar --}}
    <form method="GET" action="{{ route('portal.products') }}"
          class="portal-card flex flex-wrap items-center gap-2 p-3 mb-5">
        {{-- Search --}}
        <div class="relative flex-1 min-w-44">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 text-xs"></i>
            <input type="text" name="search" x-model="search" value="{{ $search }}"
                   placeholder="Buscar produtos..."
                   class="portal-input pl-8 pr-3 py-2 text-xs w-full">
        </div>

        {{-- Category --}}
        @if($categories->count())
        <select name="category" x-model="category" class="portal-input py-2 text-xs min-w-[9.5rem]">
            <option value="">Todas as categorias</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->getKey() }}" {{ (string)$category === (string)$cat->getKey() ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @endif

        <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1 text-[10px]"></i> Filtrar
        </button>
        @if($search || $category)
        <a href="{{ route('portal.products') }}" class="px-3 py-2 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-xmark mr-1 text-[10px]"></i> Limpar
        </a>
        @endif

        {{-- View toggle --}}
        <div class="ml-auto flex items-center gap-1.5 border border-gray-200 dark:border-slate-600 rounded-xl p-1">
            <button type="button" @click="setView('grid')" :class="viewMode==='grid' ? 'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-400' : 'text-gray-400 dark:text-slate-500'"
                class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-xs">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button type="button" @click="setView('list')" :class="viewMode==='list' ? 'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-400' : 'text-gray-400 dark:text-slate-500'"
                class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-xs">
                <i class="fas fa-list-ul"></i>
            </button>
        </div>
    </form>

    @if($products->isEmpty())
        <div class="portal-card p-14 text-center">
            <div class="w-14 h-14 bg-gray-100 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-boxes-stacked text-gray-400 dark:text-slate-500 text-xl"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900 dark:text-slate-200 mb-1">Nenhum produto encontrado</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">Tente outra busca ou entre em contato com o vendedor.</p>
        </div>
    @else

        {{-- Grid mode --}}
        <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
            @foreach($products as $product)
            <div class="portal-product-card group">
                {{-- Área de imagem --}}
                <div class="portal-product-img-area">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                    @elseif($product->image)
                        <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                    @else
                        <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                            <i class="fas fa-box text-3xl text-sky-200 dark:text-slate-600"></i>
                        </div>
                    @endif
                    {{-- Stock badge --}}
                    <span class="badge-stock {{ $product->stock_quantity > 5 ? 'bg-emerald-500/95 text-white' : 'bg-amber-400/95 text-white' }} shadow-sm">
                        <i class="fas fa-layer-group text-[9px]"></i> {{ $product->stock_quantity }}
                    </span>
                </div>

                {{-- Ícone circular de categoria (estilo product-card-modern) --}}
                <div class="pcard-cat-circle" title="{{ $product->category?->name ?? 'Produto' }}">
                    <i class="{{ $product->category?->icone ?? 'fas fa-box' }}"></i>
                </div>

                {{-- Corpo do card --}}
                <div class="pcard-body">
                    @if($product->category)
                    <p class="text-[9px] font-black uppercase tracking-[.2em] text-sky-600 dark:text-sky-400 truncate w-full">{{ $product->category->name }}</p>
                    @endif
                    <h3 class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 min-h-[2rem] w-full">{{ $product->name }}</h3>

                    @if($product->price_sale)
                    <p class="text-base font-black text-sky-700 dark:text-sky-400 leading-none mt-1">
                        R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                    </p>
                    @else
                    <p class="text-[10px] italic text-gray-400 dark:text-slate-500 mt-1">Sob consulta</p>
                    @endif

                    <a href="{{ route('portal.quotes.create', ['product_id' => $product->id]) }}"
                       class="mt-2 inline-flex items-center gap-1.5 px-4 py-1.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-[10px] font-black rounded-full transition-all shadow-sm hover:shadow-sky-400/30 hover:scale-105 whitespace-nowrap">
                        <i class="fas fa-plus text-[8px]"></i> Solicitar
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        {{-- List mode --}}
        <div x-show="viewMode === 'list'" x-cloak class="portal-card overflow-hidden">
            <table class="w-full portal-table">
                <thead class="bg-gray-50/80 dark:bg-slate-700/40">
                    <tr>
                        <th class="text-left text-gray-500 dark:text-slate-400">Produto</th>
                        <th class="text-left text-gray-500 dark:text-slate-400 hidden sm:table-cell">Categoria</th>
                        <th class="text-right text-gray-500 dark:text-slate-400">Estoque</th>
                        <th class="text-right text-gray-500 dark:text-slate-400">Preço</th>
                        <th class="text-center text-gray-500 dark:text-slate-400 hidden xs:table-cell">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl overflow-hidden flex-shrink-0 bg-sky-50 dark:bg-sky-900/20">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                                    @elseif($product->image)
                                        <img src="{{ asset('storage/products/'.$product->image) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-sky-300 text-xs"></i></div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-slate-200 truncate max-w-[10rem]">{{ $product->name }}</p>
                                    @if($product->description)
                                    <p class="text-[10px] text-gray-400 dark:text-slate-500 truncate max-w-[10rem]">{{ $product->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell">
                            <span class="text-[10px] font-semibold text-sky-600 dark:text-sky-400">{{ $product->category?->name ?? '—' }}</span>
                        </td>
                        <td class="text-right">
                            <span class="status-badge {{ $product->stock_quantity > 5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($product->price_sale)
                            <span class="text-xs font-black text-gray-900 dark:text-slate-200">R$ {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                            @else
                            <span class="text-[10px] text-gray-400 dark:text-slate-500 italic">Consultar</span>
                            @endif
                        </td>
                        <td class="text-center hidden xs:table-cell">
                            <a href="{{ route('portal.quotes.create', ['product_id' => $product->id]) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white text-[10px] font-bold rounded-lg transition-colors">
                                <i class="fas fa-plus text-[9px]"></i> Pedir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif

</div>

</x-portal-layout>
