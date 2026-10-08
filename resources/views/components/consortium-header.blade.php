@props([
    'title',
    'subtitle' => null,
    'crumb' => null,
    'backRoute' => null,
    'icon' => 'bi-piggy-bank',
])

{{-- Cabeçalho das telas de consórcio, no mesmo visual do detalhe e das telas de cliente --}}
<div class="relative overflow-hidden mb-6 rounded-[28px] border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.94),rgba(238,242,255,0.9),rgba(245,243,255,0.94))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl shadow-[0_20px_60px_rgba(15,23,42,0.12)]">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.16),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.12),transparent_32%)]"></div>
    <div class="pointer-events-none absolute -top-12 right-10 h-36 w-36 rounded-full bg-purple-400/20 blur-2xl"></div>

    <div class="relative px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ $backRoute ?? route('consortiums.index') }}" title="Voltar"
                   class="group inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700/70 shadow-sm transition">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div class="hidden sm:flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-purple-500/25">
                    <i class="bi {{ $icon }} text-white text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <nav class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('consortiums.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-300"><i class="bi bi-piggy-bank mr-1"></i>Consórcios</a>
                        @isset($parent)
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            {{ $parent }}
                        @endisset
                        @if ($crumb)
                            <i class="bi bi-chevron-right text-[10px]"></i>
                            <span class="text-indigo-600 dark:text-indigo-300">{{ $crumb }}</span>
                        @endif
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold truncate bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                    @isset($meta)
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-400">{{ $meta }}</div>
                    @endisset
                </div>
            </div>

            @if (trim($slot) !== '')
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
