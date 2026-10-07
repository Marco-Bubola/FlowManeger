@props([
    'client' => null,
    'name' => null,
    'photo' => null,
    'size' => 'w-14 h-14 text-lg',
    'rounded' => 'rounded-2xl',
])

@php
    // Foto do cliente com iniciais por baixo: se a imagem não carregar (link
    // quebrado, avatar externo fora do ar), as iniciais continuam aparecendo.
    $avatarName = trim($name ?? ($client->name ?? ''));
    $avatarPhoto = $photo ?? ($client->caminho_foto ?? null);
    $parts = preg_split('/\s+/', $avatarName) ?: [];
    $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    $palettes = [
        'from-indigo-500 to-purple-600',
        'from-sky-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-rose-500 to-pink-600',
        'from-amber-500 to-orange-600',
        'from-fuchsia-500 to-violet-600',
    ];
    $palette = $palettes[abs(crc32($avatarName)) % count($palettes)];
@endphp

<div {{ $attributes->merge(['class' => "relative shrink-0 overflow-hidden $size $rounded bg-gradient-to-br $palette shadow-md"]) }}>
    <span class="absolute inset-0 flex items-center justify-center font-bold text-white tracking-wide select-none">{{ $initials ?: '?' }}</span>
    @if($avatarPhoto)
        <img src="{{ $avatarPhoto }}" alt="{{ $avatarName }}"
             class="absolute inset-0 w-full h-full object-cover"
             onerror="this.remove()">
    @endif
</div>
