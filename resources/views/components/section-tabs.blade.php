@props([
    'section',
    'active' => null,
    'extra' => [],
])

@php
    // Abas de navegação de cada seção (mesmo menu do cabeçalho de Produtos).
    $sections = [
        'clientes' => [
            'lista' => ['label' => 'Clientes', 'icon' => 'bi-people', 'route' => 'clients.index'],
            'novo' => ['label' => 'Novo cliente', 'icon' => 'bi-person-plus', 'route' => 'clients.create'],
        ],
        'vendas' => [
            'lista' => ['label' => 'Vendas', 'icon' => 'bi-cart3', 'route' => 'sales.index'],
            'nova' => ['label' => 'Nova venda', 'icon' => 'bi-plus-circle', 'route' => 'sales.create'],
            'receber' => ['label' => 'A receber', 'icon' => 'bi-cash-coin', 'route' => 'gestao.receivables'],
            'lucro' => ['label' => 'Lucro por venda', 'icon' => 'bi-graph-up-arrow', 'route' => 'gestao.profit'],
        ],
        'categorias' => [
            'lista' => ['label' => 'Categorias', 'icon' => 'bi-tags', 'route' => 'categories.index'],
            'nova' => ['label' => 'Nova categoria', 'icon' => 'bi-plus-circle', 'route' => 'categories.create'],
        ],
    ];
    $tabs = collect($sections[$section] ?? [])
        ->filter(fn ($t) => \Illuminate\Support\Facades\Route::has($t['route']))
        ->map(fn ($t) => $t + ['url' => route($t['route'])])
        ->all();
    foreach ($extra as $key => $tab) {
        $tabs[$key] = $tab;
    }
@endphp

<div {{ $attributes->merge(['class' => 'app-ph-tabs mt-4 -mx-1 overflow-x-auto']) }}>
    <nav class="flex min-w-max items-center gap-1 px-1 pb-1">
        @foreach($tabs as $key => $tab)
            <a href="{{ $tab['url'] }}"
               class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold transition
                      {{ $active === $key
                            ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800/80 hover:text-indigo-700 dark:hover:text-indigo-300' }}">
                <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
