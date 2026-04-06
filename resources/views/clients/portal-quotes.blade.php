<x-layouts.app title="Orçamentos do Portal — {{ $client->name }}">
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/sale-index-mobile.css') }}">
    <style>
        [x-cloak] { display: none !important; }

        /* ── Quote cards grid ─────────────────────────────────────── */
        .pq-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 900px)  { .pq-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1400px) { .pq-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 1900px) { .pq-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 2400px) { .pq-grid { grid-template-columns: repeat(5, 1fr); } }

        /* ── Card de orçamento ──────────────────────────────────── */
        .pq-card {
            background: linear-gradient(160deg, #ffffff 0%, #f8fafc 100%);
            border: 1.5px solid #e2e8f0;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(30,27,75,0.07);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s;
        }
        .dark .pq-card {
            background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%);
            border-color: #334155;
            box-shadow: 0 4px 24px rgba(0,0,0,0.35);
        }

        /* ── Produto cards — usa product-card-modern completo ─────── */
        .pq-products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.625rem;
        }
        @media (min-width: 480px)  { .pq-products-grid { grid-template-columns: repeat(4, 1fr); } }
        /* Dentro do pq-grid 2-col (900px+), cada pq-card tem ~440px → 4 cols OK */
        /* Dentro do pq-grid 3-col (1400px+), ~460px → 4 cols OK */
        /* Dentro do pq-grid 4-col (1900px+), ~395px → 4 cols OK, ou 5 se quiser mais compacto */
        @media (min-width: 1900px) { .pq-products-grid { grid-template-columns: repeat(5, 1fr); } }

        /* Overrides para deixar product-card-modern compacto nesta página */
        .pq-products-grid .product-card-modern {
            min-height: 0 !important;
            border-radius: 1.1rem !important;
        }
        .pq-products-grid .product-card-modern .product-img-area {
            min-height: 0 !important;
            height: 8rem !important;
        }
        /* 1920px: imagens ligeiramente maiores pois os cards ficam mais compactos */
        @media (min-width: 1900px) {
            .pq-products-grid .product-card-modern .product-img-area { height: 7rem !important; }
        }
        .pq-products-grid .product-card-modern .category-icon-wrapper {
            width: 38px !important;
            height: 38px !important;
            bottom: -19px !important;
        }
        .pq-products-grid .product-card-modern .category-icon {
            font-size: 1.2em !important;
        }
        .pq-products-grid .product-card-modern .card-body {
            padding: 1.5rem 0.5rem 0.75rem !important;
            min-height: 0 !important;
            gap: 0.15rem !important;
        }
        .pq-products-grid .product-card-modern .product-title {
            font-size: 0.62rem !important;
            letter-spacing: 0.05em !important;
        }
        .pq-products-grid .product-card-modern .badge-product-code {
            font-size: 0.6rem !important;
            padding: 0.18em 0.5em !important;
        }
        .pq-products-grid .product-card-modern .badge-quantity {
            font-size: 0.68rem !important;
            padding: 0.2em 0.6em !important;
        }
        .pq-products-grid .product-card-modern .price-area { margin-top: 0.2rem !important; }
        .pq-products-grid .product-card-modern .badge-price,
        .pq-products-grid .product-card-modern .badge-price-sale {
            font-size: 0.6rem !important;
            padding: 0.2em 0.55em !important;
        }
        .pq-products-grid .product-card-modern .btn-action-group { display: none !important; }

        /* Badge de quantidade pedida */
        .pq-qty-badge {
            position: absolute;
            top: 0.4rem;
            left: 0.4rem;
            z-index: 10;
            background: rgba(15,23,42,0.72);
            color: #fff;
            border-radius: 9999px;
            padding: 0.12rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 900;
            backdrop-filter: blur(4px);
            pointer-events: none;
        }

        /* ── Botões de ação modernos ─────────────────────────────── */
        .pq-action-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 1rem;
            border-radius: 0.875rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            transition: all 0.15s cubic-bezier(.4,0,.2,1);
            cursor: pointer;
            border: 1.5px solid transparent;
        }
        .pq-action-chip:hover { transform: translateY(-1px); }
        .pq-chip-sky     { background: rgba(14,165,233,0.1);  color: #0369a1; border-color: rgba(14,165,233,0.25); }
        .pq-chip-sky:hover { background: rgba(14,165,233,0.18); }
        .dark .pq-chip-sky { color: #38bdf8; }
        .pq-chip-violet  { background: rgba(139,92,246,0.1); color: #6d28d9; border-color: rgba(139,92,246,0.25); }
        .pq-chip-violet:hover { background: rgba(139,92,246,0.18); }
        .dark .pq-chip-violet { color: #a78bfa; }
        .pq-chip-rose    { background: rgba(244,63,94,0.08);  color: #be123c; border-color: rgba(244,63,94,0.2); }
        .pq-chip-rose:hover { background: rgba(244,63,94,0.15); }
        .dark .pq-chip-rose { color: #fb7185; }
        .pq-chip-slate   { background: rgba(100,116,139,0.08); color: #475569; border-color: rgba(100,116,139,0.2); }
        .pq-chip-slate:hover { background: rgba(100,116,139,0.15); }
        .dark .pq-chip-slate { color: #94a3b8; }

        /* ── Status pill ────────────────────────────────────────── */
        .pq-status { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.69rem; font-weight: 800; }

        /* ── Stat cards no header ─────────────────────────────── */
        .pq-stat { background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.16); backdrop-filter: blur(10px); border-radius: 1rem; padding: 0.75rem 1rem; }

        /* Mobile overrides */
        @media (max-width: 767px) {
            .pq-products-grid .product-card-modern .product-img-area { height: 6rem !important; }
            .pq-products-grid .product-card-modern .category-icon-wrapper { width: 30px !important; height: 30px !important; bottom: -15px !important; }
            .pq-products-grid .product-card-modern .card-body { padding-top: 1.2rem !important; }
        }
    </style>
@endpush

<div class="w-full" x-data="{ confirmModal: null }">

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- HEADER — idêntico ao sales-index-header                        --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="sales-index-header relative overflow-hidden
                bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80
                dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30
                backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50
                rounded-3xl shadow-2xl mb-6">

        {{-- Brilho + decorações --}}
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full translate-x-16 -translate-y-16 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full -translate-x-10 translate-y-10 pointer-events-none"></div>

        <div class="relative px-5 py-4 sales-index-header-inner">

            {{-- ROW 1 --}}
            <div class="sales-index-header-row-1">

                {{-- Ícone + título --}}
                <div class="sales-index-header-left">
                    <div class="sales-index-header-icon">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                    <div class="sales-index-header-title-wrap">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">
                                <i class="fas fa-home mr-1"></i>Dashboard
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <a href="{{ route('clients.index') }}" class="hover:text-indigo-600 transition-colors">Clientes</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Orçamentos</span>
                        </div>
                        <h1 class="sales-index-header-title">
                            Orçamentos — {{ $client->name }}
                        </h1>
                    </div>
                </div>

                {{-- Badges de stats --}}
                @php
                    $totalQ    = $quotes->count();
                    $pendingQ  = $quotes->whereIn('status', ['pending','reviewing'])->count();
                    $quotedQ   = $quotes->where('status', 'quoted')->count();
                    $approvedQ = $quotes->where('status', 'approved')->count();
                @endphp
                <div class="sales-index-header-badges sales-mobile-hide hidden md:flex">
                    <div class="sale-badge sale-badge-success">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>{{ $totalQ }} orçamentos</span>
                    </div>
                    @if($pendingQ > 0)
                    <div class="sale-badge sale-badge-warning">
                        <i class="fas fa-clock"></i>
                        <span>{{ $pendingQ }} pendentes</span>
                    </div>
                    @endif
                    @if($approvedQ > 0)
                    <div class="sale-badge sale-badge-info">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $approvedQ }} aprovados</span>
                    </div>
                    @endif
                </div>

                {{-- Botões direita --}}
                <div class="flex items-center gap-2 ml-auto">
                    <a href="{{ route('clients.resumo', $client->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200/80 dark:border-slate-600/80 bg-white/80 dark:bg-slate-800/80 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-700 transition shadow-sm">
                        <i class="fas fa-arrow-left text-xs"></i> Voltar
                    </a>
                    @if($client->portal_active)
                    <a href="{{ route('portal.login') }}" target="_blank"
                       class="sales-index-header-btn-create group">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Portal</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- ROW 2 — status pills + filtro rápido --}}
            <div class="sales-index-header-row-2 mt-2">
                <div class="sales-index-header-row-2-left">
                    <div class="sale-filter-pills sale-status-group">
                        @foreach([''=>'Todos','pending'=>'Aguardando','reviewing'=>'Em Análise','quoted'=>'Cotado','approved'=>'Aprovado','rejected'=>'Recusado'] as $val => $lbl)
                        <a href="{{ request()->fullUrlWithQuery(['status_filter' => $val]) }}"
                           class="sale-filter-pill {{ request('status_filter', '') === $val ? 'active' : '' }}">
                            {{ $lbl }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="sales-index-header-row-2-right">
                    @if($client->portal_active)
                    <span class="sale-badge sale-badge-success">
                        <i class="fas fa-circle" style="font-size:0.45rem"></i> Portal ativo
                    </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mx-4 sm:mx-6 lg:mx-8 mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-700/30 px-4 py-3 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-4 sm:mx-6 lg:mx-8 mb-4 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-700/30 px-4 py-3 text-sm font-semibold text-rose-700 dark:text-rose-300">
        <i class="fas fa-exclamation-circle text-rose-500"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- GRID DE ORÇAMENTOS                                             --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @php
        $statusFilter = request('status_filter', '');
        $filteredQuotes = $statusFilter ? $quotes->where('status', $statusFilter) : $quotes;
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 pb-10">

        @if($filteredQuotes->isEmpty())
        <div class="pq-card p-14 text-center">
            <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-2xl text-indigo-400"></i>
            </div>
            <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">Nenhum orçamento</h2>
            <p class="mt-1 text-sm text-slate-400">Quando o cliente enviar pedidos pelo portal, eles aparecerão aqui.</p>
        </div>

        @else
        <div class="pq-grid">
        @foreach($filteredQuotes as $quote)
        @php
            $autoTotal    = collect($quote->items ?? [])->sum(fn($i) => ($i['price_ref'] ?? 0) * ($i['quantity'] ?? 1));
            $displayTotal = $quote->quoted_total ?? $autoTotal;
            $autoValidDate= $quote->valid_until ?? $quote->created_at->copy()->addDays(30);

            $sCfg = [
                'pending'   => ['label'=>'Aguardando','bg'=>'bg-amber-50 dark:bg-amber-900/25',  'text'=>'text-amber-700 dark:text-amber-300',  'dot'=>'bg-amber-400', 'bar'=>'from-amber-400 to-orange-500'],
                'reviewing' => ['label'=>'Em Análise', 'bg'=>'bg-sky-50 dark:bg-sky-900/25',    'text'=>'text-sky-700 dark:text-sky-300',      'dot'=>'bg-sky-400',   'bar'=>'from-sky-400 to-blue-500'],
                'quoted'    => ['label'=>'Cotado',     'bg'=>'bg-violet-50 dark:bg-violet-900/25','text'=>'text-violet-700 dark:text-violet-300','dot'=>'bg-violet-400','bar'=>'from-violet-400 to-purple-500'],
                'approved'  => ['label'=>'Aprovado',   'bg'=>'bg-emerald-50 dark:bg-emerald-900/25','text'=>'text-emerald-700 dark:text-emerald-300','dot'=>'bg-emerald-400','bar'=>'from-emerald-400 to-teal-500'],
                'rejected'  => ['label'=>'Recusado',   'bg'=>'bg-rose-50 dark:bg-rose-900/25',  'text'=>'text-rose-700 dark:text-rose-300',    'dot'=>'bg-rose-400',  'bar'=>'from-rose-400 to-pink-500'],
            ];
            $sc = $sCfg[$quote->status] ?? $sCfg['pending'];

            $payLabels = ['pix'=>'PIX','dinheiro'=>'Dinheiro','credito'=>'Crédito','debito'=>'Débito','boleto'=>'Boleto','outro'=>'Outro'];
            $payIcons  = ['pix'=>'fa-qrcode','dinheiro'=>'fa-money-bill-wave','credito'=>'fa-credit-card','debito'=>'fa-credit-card','boleto'=>'fa-barcode'];
            $payLabel  = $payLabels[$quote->payment_preference ?? ''] ?? ucfirst($quote->payment_preference ?? '');
            $payIcon   = $payIcons[$quote->payment_preference ?? ''] ?? 'fa-wallet';
            $modalPayload = json_encode(['id'=>$quote->id,'total'=>number_format((float)$displayTotal,2,'.',''),'items'=>count($quote->items ?? [])]);
        @endphp

        <div class="pq-card">

            {{-- Topo colorido do card --}}
            <div class="relative h-1.5 bg-gradient-to-r {{ $sc['bar'] }}"></div>

            {{-- Header do card --}}
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                            <i class="fas fa-file-alt text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-black text-slate-900 dark:text-slate-100">ORC-{{ $quote->id }}</span>
                                <span class="pq-status {{ $sc['bg'] }} {{ $sc['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} shrink-0"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                <i class="fas fa-calendar-alt mr-0.5"></i>{{ $quote->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    {{-- Total --}}
                    <div class="text-right shrink-0">
                        <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Valor</p>
                        @if($displayTotal > 0)
                            <p class="text-lg font-black text-slate-900 dark:text-slate-100 leading-tight">
                                R$ {{ number_format($displayTotal, 2, ',', '.') }}
                            </p>
                            <p class="text-[9px] {{ $quote->quoted_total ? 'text-emerald-500' : 'text-sky-400' }}">
                                {{ $quote->quoted_total ? '✓ confirmado' : '≈ estimativa' }}
                            </p>
                        @else
                            <p class="text-sm font-bold text-slate-400">A definir</p>
                        @endif
                    </div>
                </div>

                {{-- Meta: pagamento + validade --}}
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    @if($quote->payment_preference)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 text-[10px] font-bold text-indigo-600 dark:text-indigo-300">
                        <i class="fas {{ $payIcon }} text-[8px]"></i> {{ $payLabel }}
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-600/40 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                        <i class="fas fa-hourglass-half text-[8px]"></i>
                        válido até {{ $autoValidDate->format('d/m/Y') }}
                    </span>
                    @if($quote->items)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800/30 text-[10px] font-bold text-sky-600 dark:text-sky-300">
                        <i class="fas fa-boxes text-[8px]"></i> {{ count($quote->items) }} produto{{ count($quote->items)!==1?'s':'' }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Nota do cliente --}}
            @if($quote->client_notes)
            <div class="mx-4 mt-3 flex items-start gap-2 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30 px-3 py-2.5">
                <i class="fas fa-comment-dots text-amber-500 text-sm mt-0.5 shrink-0"></i>
                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $quote->client_notes }}</p>
            </div>
            @endif

            {{-- Produtos — idêntico ao product-card-modern ──────── --}}
            @if(!empty($quote->items))
            <div class="px-4 pt-4">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2.5">
                    <i class="fas fa-box mr-1 text-violet-400"></i>Itens solicitados
                    @if($autoTotal > 0) &bull; <span class="text-emerald-500">R$ {{ number_format($autoTotal,2,',','.') }}</span> @endif
                </p>
                <div class="pq-products-grid">
                    @foreach($quote->items as $item)
                    @php
                        $prod = null;
                        if (!empty($item['product_id'])) {
                            $prod = \App\Models\Product::find($item['product_id']);
                        }
                        if (!$prod && !empty($item['name'])) {
                            $prod = \App\Models\Product::where('name', 'like', '%' . trim($item['name']) . '%')->first();
                        }
                        $hasImg   = $prod?->image;
                        $imgSrc   = $hasImg ? asset('storage/products/'.$prod->image) : asset('storage/products/product-placeholder.png');
                        $catIcon  = $prod?->category?->icone ?? 'bi bi-box-seam';
                        $stockQty = $prod?->stock_quantity;
                        $salePrice= $prod?->price_sale;
                        $unitPrice= $item['price_ref'] ?? $salePrice;
                        $itemTotal= ($unitPrice ?? 0) * ($item['quantity'] ?? 1);
                    @endphp
                    <div class="product-card-modern" style="position:relative;">

                        {{-- Badge qty pedida --}}
                        <span class="pq-qty-badge">{{ $item['quantity'] ?? 1 }}x</span>

                        {{-- Imagem --}}
                        <div class="product-img-area">
                            <img src="{{ $imgSrc }}" alt="{{ $item['name'] }}" class="product-img">

                            @if($stockQty !== null)
                            <span style="position:absolute;top:0.4rem;right:0.4rem;z-index:8;"
                                  class="inline-flex items-center gap-0.5 rounded-full text-[7px] font-black px-1.5 py-0.5 shadow
                                  {{ $stockQty > 5 ? 'bg-emerald-500/85 text-white' : ($stockQty > 0 ? 'bg-amber-400/85 text-white' : 'bg-rose-500/85 text-white') }}">
                                <i class="bi bi-stack"></i> {{ $stockQty }}
                            </span>
                            @endif

                            {{-- Category icon --}}
                            <div class="category-icon-wrapper">
                                <i class="{{ $catIcon }} category-icon"></i>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="card-body">
                            <div class="product-title" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                            <div class="price-area">
                                <div class="flex flex-col gap-1 items-center">
                                    @if($unitPrice)
                                    <span class="badge-price">
                                        <i class="bi bi-tag"></i>
                                        R$ {{ number_format($unitPrice, 2, ',', '.') }}
                                    </span>
                                    @endif
                                    @if($itemTotal > 0 && ($item['quantity'] ?? 1) > 1)
                                    <span class="badge-price-sale">
                                        <i class="bi bi-currency-dollar"></i>
                                        R$ {{ number_format($itemTotal, 2, ',', '.') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Itens extras --}}
            @if(!empty($quote->extra_items))
            <div class="mx-4 mt-3 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">
                    <i class="fas fa-plus mr-1 text-violet-400"></i> Itens adicionais
                </p>
                <div class="space-y-1">
                    @foreach($quote->extra_items as $extra)
                    <div class="flex justify-between rounded-lg bg-white dark:bg-slate-700 px-3 py-2 border border-slate-100 dark:border-slate-600">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $extra['description'] }}</span>
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $extra['quantity'] }}x</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── Painel de resposta ─────────────────────────────── --}}
            <div class="p-4 pt-3 mt-3 border-t border-slate-100 dark:border-slate-700/60 space-y-3">

                <form method="POST" action="{{ route('clients.portal.quotes.update', $quote->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $quote->status }}">
                    <input type="hidden" name="quoted_total" value="{{ $displayTotal > 0 ? round($displayTotal,2) : '' }}">
                    <input type="hidden" name="valid_until" value="{{ $autoValidDate->format('Y-m-d') }}">

                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1.5">
                        <i class="fas fa-sticky-note mr-1 text-indigo-400"></i> Resposta / Observações
                    </label>
                    <textarea name="admin_notes" rows="2"
                              placeholder="Resposta ao cliente, prazo, condições..."
                              class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-xs text-slate-700 dark:text-slate-300 placeholder-slate-300 dark:placeholder-slate-600 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 resize-none transition">{{ $quote->admin_notes }}</textarea>

                    {{-- Ações rápidas --}}
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @if($quote->status === 'pending')
                        <button type="submit"
                                onclick="this.form.querySelector('[name=status]').value='reviewing'"
                                class="pq-action-chip pq-chip-sky">
                            <i class="fas fa-search text-[9px]"></i> Iniciar Análise
                        </button>
                        @endif
                        @if(in_array($quote->status,['pending','reviewing']))
                        <button type="submit"
                                onclick="this.form.querySelector('[name=status]').value='quoted'"
                                class="pq-action-chip pq-chip-violet">
                            <i class="fas fa-tag text-[9px]"></i> Marcar Cotado
                        </button>
                        @endif
                        @if(!in_array($quote->status,['approved','rejected']))
                        <button type="submit"
                                onclick="this.form.querySelector('[name=status]').value='rejected'"
                                class="pq-action-chip pq-chip-rose">
                            <i class="fas fa-times text-[9px]"></i> Recusar
                        </button>
                        @endif
                        <button type="submit" class="pq-action-chip pq-chip-slate">
                            <i class="fas fa-save text-[9px]"></i> Salvar
                        </button>
                    </div>
                </form>

                {{-- Confirmar como Venda --}}
                @if(in_array($quote->status,['pending','reviewing','quoted']))
                <button type="button"
                        @click="confirmModal = {{ $modalPayload }}"
                        class="w-full flex items-center justify-center gap-2 rounded-xl
                               bg-gradient-to-r from-emerald-500 to-teal-600
                               hover:from-emerald-600 hover:to-teal-700
                               py-3 text-sm font-black text-white
                               shadow-lg shadow-emerald-500/25 hover:scale-[1.01] transition-all">
                    <i class="fas fa-check-circle"></i> Confirmar como Venda
                </button>
                @elseif($quote->status === 'approved')
                <div class="flex items-center justify-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/30 py-3 text-sm font-black text-emerald-700 dark:text-emerald-300">
                    <i class="fas fa-check-double"></i> Venda já criada
                </div>
                @endif
            </div>

        </div>{{-- /pq-card --}}
        @endforeach
        </div>{{-- /pq-grid --}}
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Confirmar como Venda                                    --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-cloak
         x-show="confirmModal !== null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         @click.self="confirmModal = null">

        <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-2xl border border-white/10"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-90 -translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             @click.stop>

            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-700 to-sky-800 px-6 pt-5 pb-7 text-white">
                <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black">Confirmar como Venda</h3>
                            <p class="text-sm text-emerald-200"
                               x-text="confirmModal ? 'ORC-' + confirmModal.id + ' · ' + confirmModal.items + ' produto(s)' : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="confirmModal = null"
                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <template x-if="confirmModal">
                <form method="POST" :action="`/clients/portal/quotes/${confirmModal.id}/confirm`"
                      class="px-6 pt-5 pb-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5 block">
                            <i class="fas fa-dollar-sign mr-1 text-emerald-500"></i> Valor Total (R$)
                        </label>
                        <input type="number" step="0.01" min="0" name="total_price"
                               :value="confirmModal.total"
                               class="w-full rounded-2xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-xl font-black text-slate-900 dark:text-white focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-900/30 transition"
                               required>
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5 block">
                            <i class="fas fa-credit-card mr-1 text-sky-500"></i> Forma de Pagamento
                        </label>
                        <select name="payment_method" required
                                class="w-full rounded-2xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm font-bold text-slate-900 dark:text-white focus:border-emerald-400 focus:outline-none transition">
                            <option value="pix">PIX</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="boleto">Boleto</option>
                            <option value="transferencia">Transferência Bancária</option>
                        </select>
                    </div>

                    <div x-data="{ tipo: 'a_vista' }">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5 block">Modalidade</label>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_pagamento" value="a_vista" x-model="tipo" class="sr-only peer">
                                <div class="text-center py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-600
                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/20
                                            text-xs font-black text-slate-400 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300 transition cursor-pointer">
                                    <i class="fas fa-bolt mr-1"></i>À Vista
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo_pagamento" value="parcelado" x-model="tipo" class="sr-only peer">
                                <div class="text-center py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-600
                                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20
                                            text-xs font-black text-slate-400 peer-checked:text-indigo-700 dark:peer-checked:text-indigo-300 transition cursor-pointer">
                                    <i class="fas fa-th-list mr-1"></i>Parcelado
                                </div>
                            </label>
                        </div>
                        <div x-show="tipo === 'parcelado'" x-transition>
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1 block">Parcelas</label>
                            <select name="parcelas"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white focus:border-indigo-400 focus:outline-none">
                                @for($p = 2; $p <= 24; $p++)
                                <option value="{{ $p }}">{{ $p }}x</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="pt-1 flex gap-3">
                        <button type="button" @click="confirmModal = null"
                                class="flex-1 py-3 rounded-2xl border-2 border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-sm shadow-lg hover:scale-[1.01] transition-all">
                            <i class="fas fa-check mr-2"></i>Criar Venda
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
