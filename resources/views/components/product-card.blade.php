@props([
    'product',
    'wireModel' => '',
    'selected' => false,
    'quantity' => 1,
    'maxQuantity' => null,
    'showQuantity' => true,
    'compact' => false
])

<div class="border rounded-lg p-4 hover:shadow-md transition-all duration-200 {{ $selected ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600' : 'border-neutral-200 dark:border-neutral-600' }}">
    <div class="flex items-start space-x-3">
        <!-- Checkbox -->
        <div class="flex-shrink-0 pt-1">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox"
                    @if($wireModel) wire:model.live="{{ $wireModel }}.selecionado" @endif
                    class="w-4 h-4 text-blue-600 bg-white border-neutral-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-neutral-800 focus:ring-2 dark:bg-neutral-700 dark:border-neutral-600">
            </label>
        </div>

        <!-- Produto Info -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-neutral-900 dark:text-white mb-1 truncate">
                {{ $product->name }}
            </h4>
            @if(!$compact && $product->product_code)
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                #{{ $product->product_code }}
            </p>
            @endif
            <div class="flex items-center justify-between text-xs">
                <span class="text-green-600 dark:text-green-400 font-medium">
                    R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                </span>
                @if(!$compact && isset($product->stock_quantity))
                <span class="text-neutral-500 dark:text-neutral-400">
                    Est: {{ $product->stock_quantity }}
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Quantidade (apenas se selecionado) -->
    @if($selected && $showQuantity)
    <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-600">
        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Quantidade
        </label>
        <input type="number"
            @if($wireModel) wire:model.live="{{ $wireModel }}.quantidade" @endif
            min="1"
            @if($maxQuantity) max="{{ $maxQuantity }}" @endif
            value="{{ $quantity }}"
            class="w-full px-2 py-1 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>
    @endif

    {{ $slot }}
</div>
