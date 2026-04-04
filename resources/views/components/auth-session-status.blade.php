@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-400 font-medium']) }}>
        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ $status }}</span>
    </div>
@endif
