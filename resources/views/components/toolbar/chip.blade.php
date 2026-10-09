@props(['active' => false, 'icon' => null])
<button type="button" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-1.5 text-xs font-semibold transition disabled:opacity-40 ' . ($active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700')]) }}>
    @if($icon)<i class="bi {{ $icon }}"></i>@endif{{ $slot }}
</button>
