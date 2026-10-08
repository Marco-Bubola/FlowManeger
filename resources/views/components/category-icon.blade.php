@props(['icon' => null, 'fallback' => 'fas fa-tag', 'size' => 'text-lg'])

@php
    // Ícone de categoria: aceita Font Awesome ("fas fa-box") ou as imagens icons8-* do icon-category.css.
    $icon = trim($icon ?: $fallback);
    $isImage = str_starts_with($icon, 'icons8-');
@endphp

@if($isImage)
    <span {{ $attributes->merge(['class' => $icon]) }} style="width:70%;height:70%;background-size:contain;background-position:center;background-repeat:no-repeat;display:inline-block;"></span>
@else
    <i {{ $attributes->merge(['class' => "$icon $size"]) }}></i>
@endif
