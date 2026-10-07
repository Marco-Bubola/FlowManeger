@props([
    'client' => null,
    'title' => null,
    'subtitle' => null,
    'icon' => 'bi-person',
    'active' => null,
    'backRoute' => null,
])

@php
    // Cabeçalho único das telas de cliente: mesmo visual, mesmo menu de abas.
    $back = $backRoute ?? route('clients.index');
    $tabs = $client ? [
        'resumo' => ['label' => 'Resumo', 'icon' => 'bi-graph-up', 'url' => route('clients.resumo', $client->id)],
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => route('clients.dashboard', $client->id)],
        'faturas' => ['label' => 'Faturas', 'icon' => 'bi-receipt', 'url' => route('clients.faturas', $client->id)],
        'transferencias' => ['label' => 'Transferências', 'icon' => 'bi-arrow-left-right', 'url' => route('clients.transferencias', $client->id)],
        'consorcios' => ['label' => 'Consórcios', 'icon' => 'bi-building', 'url' => route('clients.consortiums', $client->id)],
        'orcamentos' => ['label' => 'Orçamentos', 'icon' => 'bi-file-earmark-text', 'url' => route('clients.portal.quotes', $client->id)],
        'portal' => ['label' => 'Portal', 'icon' => 'bi-key', 'url' => route('clients.portal.access', $client->id)],
        'editar' => ['label' => 'Editar', 'icon' => 'bi-pencil-square', 'url' => route('clients.edit', $client->id)],
    ] : [];
    $headerIcon = str_contains($icon, ' ') ? $icon : 'bi ' . $icon;
    $since = $client?->created_at ? $client->created_at->locale('pt_BR')->diffForHumans(null, true) : null;
@endphp

<div class="client-page-header relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.12),transparent_32%)]"></div>
    <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

    <div class="relative px-4 sm:px-6 pt-4 sm:pt-5 {{ $client ? 'pb-3' : 'pb-4 sm:pb-5' }}">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ $back }}" title="Voltar"
                   class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>

                @if($client)
                    <x-client-avatar :client="$client" size="w-12 h-12 sm:w-14 sm:h-14 text-lg" rounded="rounded-full" class="ring-4 ring-white/70 dark:ring-slate-700/70" />
                @else
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
                        <i class="{{ $headerIcon }} text-white text-2xl"></i>
                    </div>
                @endif

                <div class="min-w-0">
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('clients.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-people mr-1"></i>Clientes</a>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-300 truncate"><i class="{{ $headerIcon }}"></i>{{ $title }}</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $client?->name ?? $title }}
                    </h1>
                    @if($client)
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
                            @if($client->email)
                                <span class="inline-flex items-center gap-1 truncate max-w-[220px]"><i class="bi bi-envelope"></i>{{ $client->email }}</span>
                            @endif
                            @if($client->phone)
                                <span class="inline-flex items-center gap-1"><i class="bi bi-telephone"></i>{{ $client->phone }}</span>
                            @endif
                            @if($since)
                                <span class="inline-flex items-center gap-1"><i class="bi bi-calendar3"></i>Cliente há {{ $since }}</span>
                            @endif
                        </div>
                    @elseif($subtitle)
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">{!! $subtitle !!}</p>
                    @endif
                </div>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        @if($client)
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
        @endif
    </div>
</div>
