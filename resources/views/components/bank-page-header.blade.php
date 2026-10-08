@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi-credit-card',
    'active' => null,
    'backRoute' => null,
    'bank' => null,
])

@php
    // Cabeçalho único das telas de Cartões e Faturas, no mesmo visual de Clientes.
    // Sem cartão: abas da lista de cartões. Com cartão: abas das telas daquele cartão.
    $back = $backRoute ?? route('banks.index');
    $tabs = $bank
        ? [
            'fatura' => ['label' => 'Fatura', 'icon' => 'bi-receipt', 'url' => route('invoices.index', $bank->id_bank)],
            'compra' => ['label' => 'Nova compra', 'icon' => 'bi-plus-circle', 'url' => route('invoices.create', $bank->id_bank)],
            'importar' => ['label' => 'Importar fatura', 'icon' => 'bi-cloud-upload', 'url' => route('invoices.upload', $bank->id_bank)],
            'editar' => ['label' => 'Editar cartão', 'icon' => 'bi-pencil-square', 'url' => route('banks.edit', $bank->id_bank)],
            'cartoes' => ['label' => 'Todos os cartões', 'icon' => 'bi-credit-card-2-front', 'url' => route('banks.index')],
        ]
        : [
            'cartoes' => ['label' => 'Cartões', 'icon' => 'bi-credit-card-2-front', 'url' => route('banks.index')],
            'novo' => ['label' => 'Novo cartão', 'icon' => 'bi-plus-circle', 'url' => route('banks.create')],
        ];
    $headerIcon = str_contains($icon, ' ') ? $icon : 'bi ' . $icon;
@endphp

<div {{ $attributes->merge(['class' => "bank-page-header relative mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]"]) }}>
    {{-- Decoração recortada à parte, para menus abertos nas ações não serem cortados --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[28px]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_32%)]"></div>
        <div class="absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>
    </div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 pb-3">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ $back }}" title="Voltar"
                   class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>

                @if($bank && $bank->caminho_icone)
                    <div class="hidden sm:flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 overflow-hidden rounded-2xl bg-white dark:bg-slate-800 ring-4 ring-white/70 dark:ring-slate-700/70 shadow-md">
                        <img src="{{ $bank->caminho_icone }}" alt="{{ $bank->name }}" class="h-9 w-9 object-contain" onerror="this.replaceWith(Object.assign(document.createElement('i'), {className: 'bi bi-credit-card text-2xl text-indigo-500'}))">
                    </div>
                @else
                    <div class="hidden sm:flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
                        <i class="{{ $headerIcon }} text-white text-2xl"></i>
                    </div>
                @endif

                <div class="min-w-0">
                    <nav class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('banks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-credit-card mr-1"></i>Cartões</a>
                        @if($bank)
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <a href="{{ route('invoices.index', $bank->id_bank) }}" class="truncate hover:text-indigo-600 dark:hover:text-indigo-300">{{ $bank->name }}</a>
                        @endif
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="truncate text-indigo-600 dark:text-indigo-300">{{ $title }}</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    @if($subtitle)
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">{!! $subtitle !!}</p>
                    @endif
                    @isset($meta)
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">{{ $meta }}</div>
                    @endisset
                </div>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        <div class="mt-4 -mx-1 overflow-x-auto">
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
    </div>
</div>
