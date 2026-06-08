@props([
    'id',
    'type' => 'area',        // area | bar | donut | line | radialBar | heatmap
    'series' => [],          // array (numérico ou [{name, data}])
    'labels' => [],          // p/ donut/categorias
    'colors' => null,
    'height' => null,        // sobrescreve a altura padrão
    'extra' => [],           // opções extras do ApexCharts
])
@php
    $cfg = [
        'type' => $type,
        'series' => $series,
        'labels' => $labels,
        'colors' => $colors,
        'height' => $height,
        'extra' => $extra,
    ];
@endphp
<div class="dash-chart" id="{{ $id }}"
     data-dash-chart='@json($cfg)'
     wire:ignore></div>
