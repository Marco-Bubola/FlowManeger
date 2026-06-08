@props([
    'title',
    'sub' => null,
    'value' => null,
    'icon' => null,
    'image' => null,
    'tone' => 'indigo',
    'trend' => null,   // 'up' | 'down' | null
])
<div {{ $attributes->merge(['class' => 'dash-li']) }}>
    @if($image)
        <img src="{{ $image }}" alt="" class="dash-li-img" />
    @elseif($icon)
        <span class="dash-li-ico dash-ico-{{ $tone }} text-white"><i class="bi {{ $icon }}"></i></span>
    @endif
    <div class="dash-li-main">
        <p class="dash-li-title">{{ $title }}</p>
        @if($sub)<p class="dash-li-sub">{{ $sub }}</p>@endif
    </div>
    @if(!is_null($value))
        <span class="dash-li-val {{ $trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : ($trend === 'down' ? 'text-rose-600 dark:text-rose-400' : '') }}">{{ $value }}</span>
    @endif
</div>
