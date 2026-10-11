<div class="category-form-page w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-sales-header tabs-section="categorias" title="{{ $category->name }}" description="Edite as configurações desta categoria"
        icon="bi-tags" icon-color="purple" :back-route="route('categories.index')">
        <x-slot name="breadcrumb">
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('categories.index') }}" class="hover:text-indigo-600"><i class="bi bi-tags mr-1"></i>Categorias</a>
                <i class="bi bi-chevron-right text-[10px]"></i><span class="text-indigo-600 dark:text-indigo-300">Editar</span>
            </div>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('categories.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/85 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold hover:bg-white dark:hover:bg-slate-700 shadow-sm transition">
                <i class="bi bi-x-lg"></i>Cancelar
            </a>
            <button data-mobile-save type="submit" form="category-form" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-semibold shadow-md transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2"><i class="bi bi-check-lg"></i>Salvar alterações</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2"><span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>Salvando...</span>
            </button>
        </x-slot>
    </x-sales-header>

    @include('livewire.categories._form')
</div>
