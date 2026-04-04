@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center gap-1 mb-2">
    <h1 class="text-2xl font-bold tracking-tight text-white">{{ $title }}</h1>
    <p class="text-sm text-zinc-400">{{ $description }}</p>
</div>
