<?php
$content = <<<'BLADE'
@if ($paginator->hasPages())
<nav role="navigation" aria-label="Paginação" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5">

    <p class="text-[11px] text-gray-400 dark:text-slate-500 order-2 sm:order-1">
        @if ($paginator->firstItem())
            Exibindo <span class="font-bold text-gray-700 dark:text-slate-300">{{ $paginator->firstItem() }}</span>–<span class="font-bold text-gray-700 dark:text-slate-300">{{ $paginator->lastItem() }}</span>
            de <span class="font-bold text-gray-800 dark:text-slate-200">{{ $paginator->total() }}</span> resultados
        @else
            {{ $paginator->count() }} resultado(s)
        @endif
    </p>

    <div class="flex items-center gap-1 order-1 sm:order-2">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold opacity-30 cursor-not-allowed bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-500">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold transition-all bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 hover:bg-sky-50 dark:hover:bg-sky-900/20 hover:border-sky-200 dark:hover:border-sky-700 hover:text-sky-700 dark:hover:text-sky-400 shadow-sm">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-gray-400 dark:text-slate-500">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-black text-white bg-gradient-to-br from-sky-500 to-indigo-600 shadow-md shadow-sky-500/25">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold transition-all bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 hover:bg-sky-50 dark:hover:bg-sky-900/20 hover:border-sky-200 dark:hover:border-sky-700 hover:text-sky-700 dark:hover:text-sky-400 shadow-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold transition-all bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 hover:bg-sky-50 dark:hover:bg-sky-900/20 hover:border-sky-200 dark:hover:border-sky-700 hover:text-sky-700 dark:hover:text-sky-400 shadow-sm">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold opacity-30 cursor-not-allowed bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-500">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </span>
        @endif
    </div>
</nav>
@endif
BLADE;

file_put_contents(
    __DIR__ . '/resources/views/vendor/pagination/tailwind.blade.php',
    $content
);
echo 'OK - ' . count(file(__DIR__ . '/resources/views/vendor/pagination/tailwind.blade.php')) . ' linhas escritas' . PHP_EOL;
