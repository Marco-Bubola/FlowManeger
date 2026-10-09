{{-- Antes x durante: unidades/dia antes da promoção → durante. --}}
@if (!$pace)
    <span class="text-xs text-slate-400">—</span>
@else
    <span class="inline-flex flex-wrap items-center justify-end gap-1.5" title="{{ $num($pace['before'], 2) }}/dia nos {{ $pace['beforeDays'] }} dias antes · {{ $num($pace['during'], 2) }}/dia em {{ $pace['days'] }} {{ $pace['days'] === 1 ? 'dia' : 'dias' }} de promoção">
        <span class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">{{ $num($pace['before']) }} → <b class="text-slate-800 dark:text-slate-100">{{ $num($pace['during']) }}</b>/dia</span>
        @if ($lift === null)
            <span class="whitespace-nowrap rounded-md bg-sky-50 dark:bg-sky-500/10 px-1.5 py-0.5 text-[10px] font-bold uppercase text-sky-700 dark:text-sky-300">{{ $pace['during'] > 0 ? 'sem vendas antes' : 'sem vendas' }}</span>
        @else
            <span class="whitespace-nowrap rounded-md px-1.5 py-0.5 text-[10px] font-black {{ $lift >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}">{{ $lift >= 0 ? '+' : '' }}{{ $num($lift, 0) }}%</span>
        @endif
    </span>
@endif
