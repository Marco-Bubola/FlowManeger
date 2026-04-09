<x-portal-catalog-layout title="Catálogo de Produtos">

<div class="catalog-pub-page" x-data="{
    search:   '{{ $search }}',
    category: '{{ $category }}',
    viewMode: localStorage.getItem('portal-products-view') || 'grid',
    setView(v) { this.viewMode=v; localStorage.setItem('portal-products-view',v); },
    cart: [],
    cartCount: 0,
    showToast: false,
    toastName: '',
    _timer: null,
    initCart() {
        this.cart = JSON.parse(localStorage.getItem('portal_cart')||'[]');
        this.cartCount = this.cart.length;
    },
    inCart(id) { return this.cart.some(x=>x.id===id); },
    addToCart(id, name, price, stock, img) {
        const arr = JSON.parse(localStorage.getItem('portal_cart')||'[]');
        const idx = arr.findIndex(x=>x.id===id);
        if(idx>=0){ arr[idx].qty=Math.min((arr[idx].qty||1)+1, stock||999); }
        else { arr.push({id, name, price:parseFloat(price)||0, stock:parseInt(stock)||0, img:img||null, qty:1}); }
        localStorage.setItem('portal_cart', JSON.stringify(arr));
        this.cart=arr; this.cartCount=arr.length;
        /* mark viewed */
        try {
            let rec=JSON.parse(localStorage.getItem('portal_recent')||'[]');
            rec=[id,...rec.filter(x=>x!==id)].slice(0,20);
            localStorage.setItem('portal_recent', JSON.stringify(rec));
        } catch(e){}
        this.toastName=name; this.showToast=true;
        clearTimeout(this._timer);
        this._timer=setTimeout(()=>this.showToast=false, 2500);
    }
}" x-init="initCart()">

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header mb-6">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>
        <div class="pph-row1">
            <div class="pph-icon">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <span>Catálogo Público</span>
                </div>
                <h1 class="pph-title">Catálogo de Produtos</h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge info">
                    <i class="fas fa-box text-[8px]"></i>
                    {{ $products->total() }} produto{{ $products->total()!==1?'s':'' }}
                </span>
                @if($categories->count())
                <span class="pph-badge">
                    <i class="fas fa-tags text-[8px]"></i>
                    {{ $categories->count() }} categori{{ $categories->count()!==1?'as':'a' }}
                </span>
                @endif
                @if(!Auth::guard('portal')->check())
                <span class="pph-badge warning">
                    <i class="fas fa-lock text-[8px]"></i> Faça login para finalizar
                </span>
                @endif
            </div>
            {{-- Botão carrinho --}}
            <a href="{{ Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']) }}"
               class="pph-btn relative">
                <i class="fas fa-basket-shopping text-xs"></i>
                <span class="hidden sm:inline">Carrinho</span>
                <span x-show="cartCount > 0"
                      class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center"
                      x-text="cartCount"></span>
            </a>
        </div>

        @if($categories->count())
        <div class="pph-row2">
            <a href="{{ route('portal.catalog', array_merge(request()->only(['owner','search']), ['category'=>''])) }}"
               class="pph-pill {{ !$category ? 'active' : '' }}">
                <i class="fas fa-grid-2 text-[8px]"></i> Todos
            </a>
            @foreach($categories->take(8) as $cat)
            <a href="{{ route('portal.catalog', array_merge(request()->only(['owner','search']), ['category'=>$cat->getKey()])) }}"
               class="pph-pill {{ (string)$category===(string)$cat->getKey() ? 'active' : '' }}">
                @if($cat->icone)<i class="{{ $cat->icone }} text-[8px]"></i>@endif
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Banner de login (quando não autenticado) --}}
    @if(!Auth::guard('portal')->check())
    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 rounded-xl">
        <div class="w-8 h-8 bg-amber-100 dark:bg-amber-800/40 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-lock text-amber-600 dark:text-amber-400 text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-amber-900 dark:text-amber-200">Você está navegando como visitante</p>
            <p class="text-[10px] text-amber-700 dark:text-amber-400 mt-0.5">Adicione produtos ao carrinho e <a href="{{ route('portal.login', ['redirect' => 'cart']) }}" class="underline font-black">faça login</a> para finalizar o orçamento. Seu carrinho será preservado.</p>
        </div>
        <a href="{{ route('portal.login', ['redirect' => 'cart']) }}"
           class="flex-shrink-0 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl transition-colors">
            <i class="fas fa-sign-in-alt mr-1"></i> Entrar
        </a>
    </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('portal.catalog') }}"
          class="portal-card flex flex-wrap items-center gap-2 p-3 mb-5">
        <input type="hidden" name="owner" value="{{ request('owner') ?? $ownerId ?? '' }}">
        <div class="relative flex-1 min-w-44">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" name="search" x-model="search" value="{{ $search }}"
                   placeholder="Buscar produtos..."
                   class="portal-input pl-8 pr-3 py-2 text-xs w-full">
        </div>
        @if($categories->count())
        <select name="category" x-model="category" class="portal-input py-2 text-xs min-w-[9.5rem]" style="width:auto">
            <option value="">Todas as categorias</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->getKey() }}" {{ (string)$category===(string)$cat->getKey()?'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-filter mr-1 text-[10px]"></i> Filtrar
        </button>
        @if($search || $category)
        <a href="{{ route('portal.catalog', ['owner' => request('owner') ?? $ownerId]) }}"
           class="px-3 py-2 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors whitespace-nowrap">
            <i class="fas fa-xmark mr-1 text-[10px]"></i> Limpar
        </a>
        @endif
        <div class="ml-auto flex items-center gap-1.5 border border-gray-200 dark:border-slate-600 rounded-xl p-1">
            <button type="button" @click="setView('grid')" :class="viewMode==='grid'?'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-400':'text-gray-400 dark:text-slate-500'"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition-colors">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button type="button" @click="setView('list')" :class="viewMode==='list'?'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-400':'text-gray-400 dark:text-slate-500'"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition-colors">
                <i class="fas fa-list-ul"></i>
            </button>
        </div>
    </form>

    @if($products->isEmpty())
    <div class="portal-card p-14 text-center">
        <i class="fas fa-box-open text-4xl text-gray-200 dark:text-slate-600 mb-3 block"></i>
        <h3 class="font-bold text-sm text-gray-900 dark:text-slate-200 mb-1">Nenhum produto encontrado</h3>
        <p class="text-xs text-gray-500 dark:text-slate-400">Tente outra busca ou entre em contato com o vendedor.</p>
    </div>
    @else

    {{-- Grid --}}
    <div x-show="viewMode === 'grid'"
         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5">
        @foreach($products as $product)
        <div class="portal-product-card group">
            <div class="portal-product-img-area">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                @elseif($product->image)
                    <img src="{{ asset('storage/products/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                        <i class="fas fa-box text-3xl text-sky-200 dark:text-slate-600"></i>
                    </div>
                @endif
                <span class="badge-stock {{ $product->stock_quantity>5?'bg-emerald-500/90 text-white':'bg-amber-400/90 text-white' }} shadow-sm">
                    <i class="fas fa-layer-group text-[9px]"></i> {{ $product->stock_quantity }}
                </span>
                <div class="pcard-cat-circle" title="{{ $product->category?->name ?? 'Produto' }}">
                    <i class="{{ $product->category?->icone ?? 'fas fa-box' }}"></i>
                </div>
            </div>
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
                <button type="button"
                    @click.stop="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->price_sale }}', {{ (int)($product->stock_quantity??0) }}, '{{ $product->image_url ?? ($product->image ? asset('storage/products/'.$product->image) : '') }}')"
                    :class="inCart({{ $product->id }}) ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700'"
                    class="mt-2 inline-flex items-center gap-1.5 px-4 py-1.5 text-white text-[10px] font-black rounded-full transition-all shadow-sm hover:scale-105 whitespace-nowrap">
                    <i :class="inCart({{ $product->id }}) ? 'fas fa-check' : 'fas fa-basket-shopping'" class="text-[8px]"></i>
                    <span x-text="inCart({{ $product->id }}) ? 'No carrinho' : 'Adicionar'"></span>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- List --}}
    <div x-show="viewMode === 'list'" x-cloak class="portal-card overflow-hidden">
        <table class="w-full portal-table">
            <thead class="bg-gray-50/80 dark:bg-slate-700/40">
                <tr>
                    <th class="text-left text-gray-500 dark:text-slate-400">Produto</th>
                    <th class="text-left text-gray-500 dark:text-slate-400 hidden sm:table-cell">Categoria</th>
                    <th class="text-right text-gray-500 dark:text-slate-400">Estoque</th>
                    <th class="text-right text-gray-500 dark:text-slate-400">Preço</th>
                    <th class="text-center text-gray-500 dark:text-slate-400">Ação</th>
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
                            <p class="text-xs font-bold text-gray-900 dark:text-slate-200 truncate max-w-[12rem]">{{ $product->name }}</p>
                        </div>
                    </td>
                    <td class="hidden sm:table-cell">
                        <span class="text-[10px] font-semibold text-sky-600 dark:text-sky-400">{{ $product->category?->name ?? '—' }}</span>
                    </td>
                    <td class="text-right">
                        <span class="status-badge {{ $product->stock_quantity>5?'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400':'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td class="text-right">
                        @if($product->price_sale)
                        <span class="text-xs font-black text-gray-900 dark:text-slate-200">R$ {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                        @else
                        <span class="text-[10px] text-gray-400 italic">Consultar</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button"
                            @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->price_sale }}', {{ (int)($product->stock_quantity??0) }}, '{{ $product->image_url ?? ($product->image ? asset('storage/products/'.$product->image) : '') }}')"
                            :class="inCart({{ $product->id }}) ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-sky-500 hover:bg-sky-600'"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-white text-[10px] font-bold rounded-lg transition-colors">
                            <i :class="inCart({{ $product->id }}) ? 'fas fa-check' : 'fas fa-plus'" class="text-[9px]"></i>
                            <span x-text="inCart({{ $product->id }}) ? 'Adicionado' : 'Pedir'"></span>
                        </button>
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

    {{-- Barra flutuante carrinho (quando tem itens) --}}
    <div x-show="cartCount > 0" x-cloak
         class="fixed bottom-4 left-4 right-4 z-50 sm:left-auto sm:right-6 sm:w-auto">
        <a href="{{ Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart']) }}"
           class="flex items-center gap-3 px-5 py-3 rounded-2xl text-white font-black text-sm shadow-2xl"
           style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
            <div class="relative">
                <i class="fas fa-basket-shopping text-lg"></i>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[9px] font-black px-1 rounded-full min-w-[1rem] text-center border-2 border-white"
                      x-text="cartCount"></span>
            </div>
            <div class="flex flex-col">
                <span>{{ Auth::guard('portal')->check() ? 'Ver Carrinho' : 'Login para finalizar' }}</span>
                <span class="text-[10px] font-medium opacity-80">Carrinho preservado após login</span>
            </div>
            <i class="fas fa-chevron-right ml-2 opacity-70"></i>
        </a>
    </div>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-24 sm:bottom-6 left-1/2 -translate-x-1/2 z-[999] flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-xl"
         style="background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;min-width:200px;max-width:calc(100vw-2rem)">
        <i class="fas fa-check-circle text-emerald-300 flex-shrink-0"></i>
        <div class="min-w-0">
            <p class="text-[10px] font-black leading-tight truncate max-w-[140px]" x-text="toastName"></p>
            <p class="text-[9px] opacity-80 mt-0.5">Adicionado ao carrinho!</p>
        </div>
    </div>

</div>{{-- /x-data --}}

</x-portal-catalog-layout>
