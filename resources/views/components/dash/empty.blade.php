@props([
    'icon' => 'bi-inbox',
    'message' => 'Nenhum dado disponível',
])
<div {{ $attributes->merge(['class' => 'dash-empty']) }}>
    <i class="bi {{ $icon }}"></i>
    <p>{{ $message }}</p>
    @if(isset($slot) && trim($slot) !== '')<div class="mt-2">{{ $slot }}</div>@endif
</div>
