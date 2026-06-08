@props([
    'title' => null,
    'sub' => null,
    'icon' => null,
    'tone' => 'indigo',
    'span' => 'dash-col-6',
])
<div {{ $attributes->merge(['class' => "dash-card dash-anim {$span}"]) }}>
    @if($title || isset($actions))
    <div class="dash-card-head">
        <div class="flex items-center gap-2.5 min-w-0">
            @if($icon)<span class="dash-card-ico dash-ico-{{ $tone }}"><i class="bi {{ $icon }}"></i></span>@endif
            <div class="min-w-0">
                <h3 class="dash-card-title truncate">{{ $title }}</h3>
                @if($sub)<p class="dash-card-sub truncate">{{ $sub }}</p>@endif
            </div>
        </div>
        @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
    </div>
    @endif
    <div class="dash-card-body">{{ $slot }}</div>
</div>
