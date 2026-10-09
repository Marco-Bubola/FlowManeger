{{-- Formulário compartilhado de Novo/Editar cofrinho. Espera $editing (bool). --}}
@php
    $cofInput = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition';
    $cofLabel = 'mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300';
@endphp

<form wire:submit="save" id="cofrinho-form" class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md"><i class="bi bi-piggy-bank"></i></div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Dados do cofrinho</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Nome, meta e ícone</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $cofLabel }}"><i class="bi bi-tag text-indigo-500"></i>Nome <span class="text-rose-500">*</span></label>
                <input type="text" wire:model.live.debounce.400ms="nome" class="{{ $cofInput }}" placeholder="Ex.: Viagem de férias, Reserva de emergência">
                @error('nome') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $cofLabel }}"><i class="bi bi-flag text-emerald-500"></i>Meta <span class="text-rose-500">*</span></label>
                <x-money-input model="meta_valor" :value="$meta_valor" />
                @error('meta_valor') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
        </div>

        @if($editing)
            <div class="max-w-xs">
                <label class="{{ $cofLabel }}"><i class="bi bi-toggle-on text-sky-500"></i>Situação</label>
                <select wire:model="status" class="{{ $cofInput }}">
                    <option value="ativo">Ativo</option>
                    <option value="arquivado">Arquivado</option>
                </select>
                @error('status') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label class="{{ $cofLabel }}"><i class="bi bi-emoji-smile text-amber-500"></i>Ícone</label>
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach(\App\Livewire\Cofrinhos\CreateCofrinho::ICONES as $iconKey => $iconLabel)
                    <button type="button" wire:click="$set('icone', '{{ $iconKey }}')"
                            class="flex flex-col items-center gap-1 rounded-xl border px-2 py-2.5 text-xs font-semibold transition
                                   {{ $icone === $iconKey
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 ring-2 ring-indigo-500/20'
                                        : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <i class="fas {{ $iconKey }} text-lg"></i>{{ $iconLabel }}
                    </button>
                @endforeach
            </div>
            @error('icone') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Prévia</p>
            <div class="mt-3 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                    <i class="fas {{ $icone }} text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="truncate font-bold text-slate-900 dark:text-white">{{ $nome !== '' ? $nome : 'Nome do cofrinho' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Meta: R$ {{ is_numeric($meta_valor) ? number_format((float) $meta_valor, 2, ',', '.') : '0,00' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-indigo-200/70 dark:border-indigo-500/20 bg-indigo-50/60 dark:bg-indigo-500/5 p-5">
            <p class="flex items-center gap-1.5 text-sm font-bold text-indigo-800 dark:text-indigo-200"><i class="bi bi-lightbulb text-amber-500"></i>Como funciona</p>
            <ul class="mt-2 space-y-1.5 text-sm text-indigo-900/80 dark:text-indigo-100/80">
                <li class="flex gap-2"><i class="bi bi-arrow-down-circle text-emerald-500 mt-0.5"></i><span>Uma <b>despesa</b> no Livro caixa com este cofrinho é dinheiro que sai da conta e entra no cofrinho.</span></li>
                <li class="flex gap-2"><i class="bi bi-arrow-up-circle text-rose-500 mt-0.5"></i><span>Uma <b>receita</b> com este cofrinho é dinheiro que sai do cofrinho e volta para a conta.</span></li>
                <li class="flex gap-2"><i class="bi bi-flag text-indigo-500 mt-0.5"></i><span>A barra mostra quanto falta para a meta.</span></li>
            </ul>
        </div>
    </div>
</form>
