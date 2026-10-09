@props(['icon' => null])
<div {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 p-1']) }}>
    @if($icon)
        <span class="px-1.5 text-xs text-slate-400"><i class="bi {{ $icon }}"></i></span>
    @endif
    {{ $slot }}
</div>
