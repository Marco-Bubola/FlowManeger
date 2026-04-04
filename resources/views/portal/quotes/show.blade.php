<x-portal-layout title="Orçamento #{{ $quote->id }}">

@php
    $col = $quote->status_color;
    $icons = ['pending'=>'hourglass-half','reviewing'=>'magnifying-glass','quoted'=>'tag','approved'=>'circle-check','rejected'=>'circle-xmark'];
    $ico = $icons[$quote->status] ?? 'file-invoice';
@endphp

<div class="max-w-2xl mx-auto">

    {{-- Back + title --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('portal.quotes') }}" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-sm font-black text-gray-900 dark:text-slate-100">Orçamento #{{ $quote->id }}</h1>
            <p class="text-[10px] text-gray-400 dark:text-slate-500">Enviado em {{ $quote->created_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
    </div>

    {{-- Status banner --}}
    <div class="flex items-center gap-4 p-4 bg-{{ $col }}-50 dark:bg-{{ $col }}-900/20 border border-{{ $col }}-200 dark:border-{{ $col }}-700/50 rounded-2xl mb-4">
        <div class="w-11 h-11 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-{{ $ico }} text-{{ $col }}-600 dark:text-{{ $col }}-400 text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-{{ $col }}-800 dark:text-{{ $col }}-300 text-sm">{{ $quote->status_label }}</p>
            <p class="text-{{ $col }}-600 dark:text-{{ $col }}-400/80 text-xs mt-0.5">
                @if($quote->status === 'pending') Seu orçamento está aguardando análise.
                @elseif($quote->status === 'reviewing') Estamos analisando seu pedido.
                @elseif($quote->status === 'quoted') Seu orçamento foi respondido! Veja os detalhes abaixo.
                @elseif($quote->status === 'approved') Orçamento aprovado. Aguarde o contato do vendedor.
                @elseif($quote->status === 'rejected') Este orçamento não pôde ser atendido no momento.
                @endif
            </p>
        </div>
        @if($quote->quoted_total)
        <div class="text-right flex-shrink-0">
            <p class="text-[10px] text-{{ $col }}-600 dark:text-{{ $col }}-400 font-semibold">Valor cotado</p>
            <p class="text-xl font-black text-{{ $col }}-800 dark:text-{{ $col }}-300">R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}</p>
            @if($quote->valid_until)
            <p class="text-[10px] text-{{ $col }}-500 dark:text-{{ $col }}-400/70 mt-0.5">Válido até {{ $quote->valid_until->format('d/m/Y') }}</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Items --}}
    @if($quote->items && count($quote->items))
    <div class="portal-card mb-4 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2">
            <i class="fas fa-boxes-stacked text-sky-500 dark:text-sky-400 text-xs"></i>
            <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Produtos Solicitados</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 p-4">
            @foreach($quote->items as $item)
            @php
                $product = \App\Models\Product::find($item['product_id'] ?? null);
            @endphp
            <div class="portal-product-card">
                {{-- Área de imagem --}}
                <div class="portal-product-img-area" style="height:7rem">
                    @if($product && $product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $item['name'] }}" loading="lazy">
                    @elseif($product && $product->image)
                        <img src="{{ asset('storage/products/'.$product->image) }}" alt="{{ $item['name'] }}" loading="lazy">
                    @else
                        <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                            <i class="fas fa-box text-2xl text-sky-200 dark:text-slate-600"></i>
                        </div>
                    @endif
                    <span class="badge-stock bg-sky-500/90 text-white shadow-sm">
                        <i class="fas fa-hashtag text-[8px]"></i> {{ $item['quantity'] }}×
                    </span>
                </div>
                {{-- Ícone categoria --}}
                <div class="pcard-cat-circle" style="bottom:-1rem;width:2rem;height:2rem;font-size:0.75rem;">
                    <i class="{{ $product?->category?->icone ?? 'fas fa-box' }}"></i>
                </div>
                {{-- Corpo --}}
                <div class="pcard-body" style="padding:1.55rem 0.6rem 0.7rem">
                    <p class="text-[10px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 w-full">{{ $item['name'] }}</p>
                    @if(!empty($item['notes']))
                    <p class="text-[9px] text-gray-400 dark:text-slate-500 w-full">{{ $item['notes'] }}</p>
                    @endif
                    @if(!empty($item['price_ref']))
                    <p class="text-[10px] font-bold text-sky-600 dark:text-sky-400">R$ {{ number_format($item['price_ref'], 2, ',', '.') }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Extra items --}}
    @if($quote->extra_items && count($quote->extra_items))
    <div class="portal-card mb-4 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2">
            <i class="fas fa-list-check text-violet-500 dark:text-violet-400 text-xs"></i>
            <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Itens Adicionais</h2>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-slate-700/60">
            @foreach($quote->extra_items as $ei)
            <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                <span class="text-gray-700 dark:text-slate-300">{{ $ei['description'] }}</span>
                <span class="font-semibold text-gray-500 dark:text-slate-400">{{ $ei['quantity'] }}×</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($quote->client_notes || $quote->admin_notes)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
        @if($quote->client_notes)
        <div class="portal-card p-4">
            <h3 class="text-xs font-black text-gray-700 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                <i class="fas fa-note-sticky text-blue-400 text-[10px]"></i> Suas Observações
            </h3>
            <p class="text-xs text-gray-600 dark:text-slate-400 leading-relaxed">{{ $quote->client_notes }}</p>
        </div>
        @endif
        @if($quote->admin_notes)
        <div class="portal-card p-4 border-green-200 dark:border-green-700/50">
            <h3 class="text-xs font-black text-gray-700 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                <i class="fas fa-comment-dots text-green-500 text-[10px]"></i> Resposta do Vendedor
            </h3>
            <p class="text-xs text-gray-600 dark:text-slate-400 leading-relaxed">{{ $quote->admin_notes }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('portal.quotes') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors">
            ← Voltar
        </a>
        @if($quote->status === 'quoted')
        <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black rounded-xl transition-colors shadow-sm">
                <i class="fas fa-thumbs-up mr-1"></i> Aprovar Orçamento
            </button>
        </form>
        <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="rejected">
            <button type="submit" class="px-5 py-2.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 text-xs font-black rounded-xl transition-colors border border-red-200 dark:border-red-700/50">
                <i class="fas fa-thumbs-down mr-1"></i> Recusar
            </button>
        </form>
        @endif
    </div>
</div>

</x-portal-layout>
