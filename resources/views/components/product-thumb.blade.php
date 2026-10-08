@props(['product' => null, 'size' => 'h-12 w-12', 'rounded' => 'rounded-xl'])

{{-- Foto do produto com ícone por baixo: se a imagem não existir, o ícone continua aparecendo. --}}
<div {{ $attributes->merge(['class' => "relative shrink-0 overflow-hidden $size $rounded bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700"]) }}>
    <span class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500"><i class="bi bi-box-seam"></i></span>
    @if($product && $product->image)
        <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}"
             class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
    @endif
</div>
