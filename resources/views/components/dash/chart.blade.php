@props([
    'id',
    'type' => 'area',        // area | bar | donut | line | radialBar | heatmap
    'series' => [],          // array (numérico ou [{name, data}])
    'labels' => [],          // p/ donut/categorias
    'colors' => null,
    'height' => null,        // sobrescreve a altura padrão
    'extra' => [],           // opções extras do ApexCharts
    'currency' => false,     // formata o total do donut como R$
])
@php
    $cfg = [
        'type' => $type,
        'series' => $series,
        'labels' => $labels,
        'colors' => $colors,
        'height' => $height,
        'currency' => (bool) $currency,
        'extra' => $extra,
    ];
@endphp
<div class="dash-chart" id="{{ $id }}" wire:key="{{ $id }}-{{ md5(json_encode($cfg)) }}"
     data-dash-chart='@json($cfg)'
     wire:ignore></div>
