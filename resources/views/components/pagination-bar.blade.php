@props([
    'paginator',
    // Opções de "por página" (vazio = sem seletor) e a propriedade Livewire que guarda o valor.
    'perPageOptions' => [],
    'perPageModel' => 'perPage',
    // Nome do que está sendo listado, no plural ("vendas", "clientes").
    'label' => 'itens',
    // true = botões Livewire (gotoPage); false = links comuns (?page=N).
    'livewire' => true,
    'scrollTop' => true,
    // Componentes com paginação própria (sem WithPagination) podem usar outro nome de método.
    'gotoMethod' => 'gotoPage',
])

@php
    // Paginação única do sistema: mesmo visual em todas as listas.
    $pg = $paginator;
    $lengthAware = method_exists($pg, 'lastPage') && method_exists($pg, 'total');
    $current = $pg->currentPage();
    $last = $lengthAware ? $pg->lastPage() : null;
    $pageName = method_exists($pg, 'getPageName') ? $pg->getPageName() : 'page';
    $hasPages = $pg->hasPages();
    $showBar = $hasPages || (count($perPageOptions) > 0 && $lengthAware && $pg->total() > 0);

    // Páginas visíveis: primeira, última e a atual com 2 vizinhas de cada lado
    // (as vizinhas mais distantes somem no celular).
    $pages = [];
    if ($lengthAware && $last > 1) {
        $keep = collect([1, $last])->merge(range(max(1, $current - 2), min($last, $current + 2)))->unique()->sort()->values();
        $prev = 0;
        foreach ($keep as $n) {
            if ($n - $prev > 1) {
                $pages[] = ['gap' => true];
            }
            $far = abs($n - $current) === 2 && $n !== 1 && $n !== $last;
            $pages[] = ['n' => $n, 'far' => $far];
            $prev = $n;
        }
    }

    $btn = 'inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl px-2.5 text-sm font-semibold transition select-none';
    $idle = 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-700 dark:hover:text-indigo-300';
    $on = 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25';
    $off = 'text-slate-300 dark:text-slate-600 cursor-not-allowed';
    $scroll = $scrollTop ? "window.scrollTo({ top: 0, behavior: 'smooth' })" : '';
    $pageUrlTemplate = $livewire ? null : $pg->url(987654321);
    // Só quem usa WithPagination recebe o nome da página como argumento.
    $pageArg = $gotoMethod === 'gotoPage' ? ", '" . $pageName . "'" : '';
@endphp

@if($showBar)
<nav role="navigation" aria-label="Paginação"
     {{ $attributes->merge(['class' => 'pagination-bar mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-3 py-3 sm:px-4 shadow-sm lg:flex-row lg:items-center lg:justify-between']) }}>

    {{-- Resumo --}}
    <p class="text-center text-sm text-slate-600 dark:text-slate-400 lg:text-left">
        @if($lengthAware)
            @if($pg->total() > 0)
                Mostrando <span class="font-semibold text-slate-900 dark:text-white">{{ $pg->firstItem() }}–{{ $pg->lastItem() }}</span>
                de <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($pg->total(), 0, ',', '.') }}</span> {{ $label }}
            @else
                Nenhum resultado
            @endif
        @else
            Página <span class="font-semibold text-slate-900 dark:text-white">{{ $current }}</span>
        @endif
    </p>

    {{-- Botões de página --}}
    @if($hasPages)
    <div class="flex items-center justify-center gap-1">
        @php $isFirst = $pg->onFirstPage(); @endphp
        @if($isFirst)
            <span class="{{ $btn }} {{ $off }}" aria-disabled="true"><i class="bi bi-chevron-left"></i><span class="ml-1 hidden sm:inline">Anterior</span></span>
        @elseif($livewire)
            <button type="button" wire:click="previousPage({{ ltrim($pageArg, ', ') }})" x-on:click="{{ $scroll }}" wire:loading.attr="disabled" class="{{ $btn }} {{ $idle }}" title="Página anterior"><i class="bi bi-chevron-left"></i><span class="ml-1 hidden sm:inline">Anterior</span></button>
        @else
            <a href="{{ $pg->previousPageUrl() }}" rel="prev" class="{{ $btn }} {{ $idle }}" title="Página anterior"><i class="bi bi-chevron-left"></i><span class="ml-1 hidden sm:inline">Anterior</span></a>
        @endif

        @if($lengthAware)
            @foreach($pages as $p)
                @if(isset($p['gap']))
                    <span class="inline-flex h-9 w-6 items-center justify-center text-sm text-slate-400">…</span>
                @elseif($p['n'] === $current)
                    <span class="{{ $btn }} {{ $on }} {{ $p['far'] ? 'hidden sm:inline-flex' : '' }}" aria-current="page">{{ $p['n'] }}</span>
                @elseif($livewire)
                    <button type="button" wire:click="{{ $gotoMethod }}({{ $p['n'] }}{!! $pageArg !!})" x-on:click="{{ $scroll }}" wire:loading.attr="disabled" class="{{ $btn }} {{ $idle }} {{ $p['far'] ? 'hidden sm:inline-flex' : '' }}">{{ $p['n'] }}</button>
                @else
                    <a href="{{ $pg->url($p['n']) }}" class="{{ $btn }} {{ $idle }} {{ $p['far'] ? 'hidden sm:inline-flex' : '' }}">{{ $p['n'] }}</a>
                @endif
            @endforeach
        @endif

        @if(! $pg->hasMorePages())
            <span class="{{ $btn }} {{ $off }}" aria-disabled="true"><span class="mr-1 hidden sm:inline">Próxima</span><i class="bi bi-chevron-right"></i></span>
        @elseif($livewire)
            <button type="button" wire:click="nextPage({{ ltrim($pageArg, ', ') }})" x-on:click="{{ $scroll }}" wire:loading.attr="disabled" class="{{ $btn }} {{ $idle }}" title="Próxima página"><span class="mr-1 hidden sm:inline">Próxima</span><i class="bi bi-chevron-right"></i></button>
        @else
            <a href="{{ $pg->nextPageUrl() }}" rel="next" class="{{ $btn }} {{ $idle }}" title="Próxima página"><span class="mr-1 hidden sm:inline">Próxima</span><i class="bi bi-chevron-right"></i></a>
        @endif
    </div>
    @endif

    {{-- Por página e "Ir para" --}}
    @if(count($perPageOptions) > 0 || ($lengthAware && $last > 5))
    <div class="flex flex-wrap items-center justify-center gap-2 lg:justify-end">
        @if(count($perPageOptions) > 0)
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <select wire:model.live="{{ $perPageModel }}"
                        class="h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 pl-3 pr-8 text-sm font-semibold text-slate-700 dark:text-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    @foreach($perPageOptions as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
                por página
            </label>
        @endif

        @if($lengthAware && $last > 5)
            {{-- div (não form) para funcionar mesmo dentro de outro formulário --}}
            <div class="inline-flex items-center gap-1.5"
                 x-data="{ n: '', go() {
                      let v = parseInt(this.n);
                      if (!v) return;
                      v = Math.min({{ $last }}, Math.max(1, v));
                      @if($livewire)
                          this.$wire.{{ $gotoMethod }}(v{!! $pageArg !!}); {{ $scroll }}; this.n = '';
                      @else
                          window.location = @js($pageUrlTemplate).replace('987654321', v);
                      @endif
                 } }">
                <span class="text-sm text-slate-600 dark:text-slate-400">Ir para</span>
                <input type="number" min="1" max="{{ $last }}" x-model="n" x-on:keydown.enter.prevent="go()" inputmode="numeric" placeholder="{{ $current }}"
                       class="h-9 w-16 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 text-center text-sm font-semibold text-slate-700 dark:text-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <button type="button" x-on:click="go()" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:hover:bg-indigo-500/25 transition" title="Ir">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        @endif
    </div>
    @endif
</nav>
@endif
