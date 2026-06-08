@props([
    'label',
    'value',
    'icon' => 'bi-graph-up',
    'tone' => 'indigo',
    'delta' => null,
    'spark' => [],
    'sparkColor' => null,
    'countup' => false,
])
<div {{ $attributes->merge(['class' => "dash-kpi dash-kpi-{$tone} dash-anim group"]) }}>
    <div class="flex items-center justify-between">
        <span class="dash-kpi-ico"><i class="bi {{ $icon }}"></i></span>
        @if(!is_null($delta))
            <span class="dash-kpi-delta {{ $delta >= 0 ? 'up' : 'down' }}">
                <i class="bi bi-arrow-{{ $delta >= 0 ? 'up' : 'down' }}-right"></i>{{ abs($delta) }}%
            </span>
        @endif
    </div>
    <p class="dash-kpi-value" @if($countup) data-countup="{{ is_numeric(str_replace(['.',','],['',''],(string)$value)) ? $value : '' }}" @endif>{{ $value }}</p>
    <p class="dash-kpi-label">{{ $label }}</p>
    @if(!empty($spark))
        <div class="dash-kpi-spark" data-spark='@json($spark)' @if($sparkColor) data-spark-color="{{ $sparkColor }}" @endif></div>
    @endif
</div>
