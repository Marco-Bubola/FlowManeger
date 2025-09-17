@props([
    'availableProducts',
    'selectedProducts' => [],
    'wireModel' => 'produtos',
    'emptyStateTitle' => 'Nenhum produto disponível',
    'emptyStateDescription' => 'Crie produtos simples primeiro para poder montar kits.',
    'emptyStateButtonText' => 'Criar Produto',
    'emptyStateButtonRoute' => null,
    'showSummary' => true,
    'columns' => '1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5'
])

<div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
    @if($availableProducts->isEmpty())
    <div class="text-center py-12">
        <div class="w-20 h-20 mx-auto mb-4 text-neutral-400">
            <i class="bi bi-box text-4xl"></i>
        </div>
        <h3 class="text-lg font-medium text-neutral-700 dark:text-neutral-300 mb-2">{{ $emptyStateTitle }}</h3>
        <p class="text-neutral-500 dark:text-neutral-400 mb-4">{{ $emptyStateDescription }}</p>
        @if($emptyStateButtonRoute)
        <a href="{{ route($emptyStateButtonRoute) }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
            <i class="bi bi-plus mr-2"></i>
            {{ $emptyStateButtonText }}
        </a>
        @endif
    </div>
    @else
    <div class="grid grid-cols-{{ $columns }} gap-4">
        @foreach($availableProducts as $product)
        <x-product-card
            :product="$product"
            :wire-model="$wireModel . '.' . $product->id"
            :selected="isset($selectedProducts[$product->id]['selecionado']) && $selectedProducts[$product->id]['selecionado']"
            :quantity="$selectedProducts[$product->id]['quantidade'] ?? 1"
            :max-quantity="$product->stock_quantity ?? null" />
        @endforeach
    </div>

    <!-- Resumo dos produtos selecionados -->
    @if($showSummary)
    @php
    $produtosSelecionados = collect($selectedProducts)->filter(fn($p) => $p['selecionado'] ?? false);
    @endphp

    @if($produtosSelecionados->count() > 0)
    <div class="mt-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
        <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
            Produtos selecionados ({{ $produtosSelecionados->count() }})
        </h3>
        <div class="space-y-2">
            @foreach($produtosSelecionados as $produtoId => $dados)
            @php
            $product = $availableProducts->find($produtoId);
            @endphp
            @if($product)
            <div class="flex items-center justify-between text-sm">
                <span class="text-neutral-700 dark:text-neutral-300">
                    {{ $product->name }} x{{ $dados['quantidade'] }}
                </span>
                <span class="text-neutral-500 dark:text-neutral-400">
                    R$ {{ number_format($product->price_sale * $dados['quantidade'], 2, ',', '.') }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
    @endif
    @endif

    {{ $slot }}
</div>
