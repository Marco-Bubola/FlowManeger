<div class="w-full space-y-4">
    <x-cashbook-page-header title="Lançamentos recorrentes" subtitle="Aluguel, salário, assinaturas: o que se repete é lançado sozinho no livro caixa" icon="bi-arrow-repeat" active="recorrentes">
        <x-slot:actions>
            <a href="{{ route('cashbook.create') }}" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50"><i class="bi bi-plus-lg"></i>Novo lançamento</a>
        </x-slot:actions>
    </x-cashbook-page-header>

    @if ($items->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center">
            <i class="bi bi-arrow-repeat text-4xl text-slate-400"></i>
            <p class="mt-3 text-slate-600 dark:text-slate-300 font-semibold">Nenhum lançamento recorrente ainda.</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">Ao criar uma transação, marque "Repetir este lançamento".</p>
            <a href="{{ route('cashbook.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold">
                <i class="bi bi-plus-lg"></i> Nova transação
            </a>
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($items as $item)
                @php $isIncome = (int) $item->type_id === 1; @endphp
                <div wire:key="rec-{{ $item->id }}"
                    class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 {{ $item->ativo ? '' : 'opacity-60' }}">
                    <div class="flex items-center gap-3 min-w-[220px] flex-1">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isIncome ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40' : 'bg-red-100 text-red-600 dark:bg-red-900/40' }}">
                            <i class="bi {{ $isIncome ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 dark:text-slate-100">{{ $item->descricao }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->category->name ?? 'Sem categoria' }} · {{ $frequencies[$item->frequencia] ?? $item->frequencia }}
                                @if ($item->data_fim) · até {{ \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-sm font-bold {{ $isIncome ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $isIncome ? '+' : '-' }} R$ {{ number_format($item->valor, 2, ',', '.') }}
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        @if ($item->ativo)
                            Próximo: <strong>{{ \Carbon\Carbon::parse($item->proximo_vencimento)->format('d/m/Y') }}</strong>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-600"><i class="bi bi-pause-circle"></i> Pausado</span>
                        @endif
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" wire:click="toggle({{ $item->id }})" aria-label="{{ $item->ativo ? 'Pausar' : 'Retomar' }}"
                            class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i class="bi {{ $item->ativo ? 'bi-pause-fill' : 'bi-play-fill' }}"></i> {{ $item->ativo ? 'Pausar' : 'Retomar' }}
                        </button>
                        <button type="button" wire:click="remove({{ $item->id }})"
                            wire:confirm="Excluir esta recorrência? Os lançamentos já criados continuam no livro-caixa."
                            aria-label="Excluir"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-red-600 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
