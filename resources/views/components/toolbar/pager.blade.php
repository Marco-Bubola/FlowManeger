@props(['paginator'])
{{-- Paginação curta da barra de busca (a completa fica no fim da lista). --}}
@if($paginator->hasPages())
    <x-toolbar.group>
        <x-toolbar.chip wire:click.prevent="previousPage" :disabled="$paginator->onFirstPage()" title="Página anterior" icon="bi-chevron-left" />
        <span class="px-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        <x-toolbar.chip wire:click.prevent="nextPage" :disabled="! $paginator->hasMorePages()" title="Próxima página" icon="bi-chevron-right" />
    </x-toolbar.group>
@endif
