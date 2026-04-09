{{-- Partial: portal product card --}}
{{-- Variables: $product --}}
@php
    $mainImg = $product->image_url && !str_ends_with($product->image_url, 'product-placeholder.png')
        ? $product->image_url
        : null;
    $extraImgs = $product->images->count() > 0
        ? $product->images->pluck('url')->toArray()
        : ($mainImg ? [$mainImg] : []);
    $cardImages = $extraImgs ?: ($mainImg ? [$mainImg] : []);
@endphp
<div class="portal-product-card group"
     @click="$dispatch('open-product-modal', { id: {{ $product->id }} })"
     style="cursor:pointer">
    {{-- Área de imagem --}}
    <div class="portal-product-img-area">
        @if($mainImg)
            <img src="{{ $mainImg }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <div class="pimg-placeholder bg-gradient-to-br from-sky-50 to-sky-100 dark:from-slate-800 dark:to-slate-700">
                <i class="fas fa-box text-3xl text-sky-200 dark:text-slate-600"></i>
            </div>
        @endif
        {{-- Indicador de múltiplas imagens --}}
        @if(count($cardImages) > 1)
        <span class="absolute bottom-1.5 left-1.5 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[8px] font-bold text-white" style="background:rgba(0,0,0,0.45)">
            <i class="fas fa-images" style="font-size:.55rem"></i> {{ count($cardImages) }}
        </span>
        @endif
    </div>

    {{-- Corpo do card --}}
    <div class="pcard-body">
        <h3 class="text-[11px] font-black text-gray-900 dark:text-slate-200 leading-snug line-clamp-2 min-h-[2rem] w-full">{{ $product->name }}</h3>

        @if($product->price_sale)
        <p class="text-base font-black text-sky-700 dark:text-sky-400 leading-none mt-1">
            R$ {{ number_format($product->price_sale, 2, ',', '.') }}
        </p>
        @else
        <p class="text-[10px] italic text-gray-400 dark:text-slate-500 mt-1">Sob consulta</p>
        @endif

        <button type="button"
            @click.stop="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->price_sale }}', {{ (int)($product->stock_quantity ?? 0) }}, '{{ addslashes($mainImg ?? '') }}')"
            :class="inCart({{ $product->id }}) ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700'"
            class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 text-white text-[10px] font-black rounded-full transition-all shadow-sm hover:shadow-sky-400/30 hover:scale-105 whitespace-nowrap w-full justify-center">
            <i :class="inCart({{ $product->id }}) ? 'fas fa-check' : 'fas fa-basket-shopping'" style="font-size:.6rem"></i>
            <span x-text="inCart({{ $product->id }}) ? 'No carrinho' : 'Adicionar'"></span>
        </button>
    </div>
</div>
