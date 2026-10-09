<div class="cofrinhos-create-page w-full pb-8">
    <x-cashbook-page-header title="Novo cofrinho" subtitle="Defina a meta e comece a guardar" icon="bi-piggy-bank" active="cofrinhos" :back-route="route('cofrinhos.index')">
        <x-slot:actions>
            <button type="button" wire:click="cancel" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-x-lg"></i>Cancelar</button>
            <button type="submit" form="cofrinho-form" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-60"><i class="bi bi-check-lg"></i>Criar cofrinho</button>
        </x-slot:actions>
    </x-cashbook-page-header>

    @include('livewire.cofrinhos._form', ['editing' => false])
</div>
