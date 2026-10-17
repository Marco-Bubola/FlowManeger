<div class="w-full space-y-4">
    <x-cashbook-page-header title="Contas e saldos" subtitle="Quanto você tem em cada lugar e transferências entre contas" icon="bi-bank" active="contas">
        <x-slot:actions>
            <button type="button" wire:click="openTransfer" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-arrow-left-right text-indigo-500"></i>Transferir</button>
            <button type="button" wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition disabled:opacity-50"><i class="bi bi-plus-lg"></i>Nova conta</button>
        </x-slot:actions>
    </x-cashbook-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm px-4 py-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Saldo total nas contas</p>
            <p class="text-2xl font-black {{ $total >= 0 ? 'text-slate-800 dark:text-slate-100' : 'text-red-600' }}">R$ {{ number_format($total, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 px-4 py-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Lançamentos sem conta no livro-caixa</p>
            <p class="text-lg font-bold text-slate-600 dark:text-slate-300">R$ {{ number_format($unassigned, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-400">Escolha a conta ao criar ou editar um lançamento para ele entrar no saldo.</p>
        </div>
    </div>

    @if ($accounts->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center">
            <i class="bi bi-bank text-4xl text-slate-400"></i>
            <p class="mt-3 text-slate-600 dark:text-slate-300 font-semibold">Nenhuma conta cadastrada.</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">Cadastre sua conta corrente, poupança ou o dinheiro da carteira com o saldo de hoje.</p>
            <button type="button" wire:click="openCreate" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold">
                <i class="bi bi-plus-lg"></i> Nova conta
            </button>
        </div>
    @else
        <style>
            .acc-layout { display: grid; gap: .75rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
            .acc-grid { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); }
            @media (min-width: 1100px) { .acc-layout { grid-template-columns: minmax(0, 1fr) 340px; } }
            @media (min-width: 1800px) { .acc-layout { grid-template-columns: minmax(0, 1fr) 400px; } }
        </style>
        @php $accById = $accounts->mapWithKeys(fn ($r) => [$r['account']->id => $r['account']]); @endphp
        <div class="acc-layout">
            <div class="acc-grid">
                @foreach ($accounts as $row)
                    @php
                        [$a, $balance] = [$row['account'], $row['balance']];
                        [$typeLabel, $typeIcon] = $types[$a->type] ?? ['Conta', 'bi-bank'];
                        $m = $month[$a->id] ?? null;
                        $share = (! $a->archived && $total > 0 && $balance > 0) ? min(100, round($balance / $total * 100)) : 0;
                    @endphp
                    <div wire:key="acc-{{ $a->id }}" class="flex flex-col rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm p-4 {{ $a->archived ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 shrink-0 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 flex items-center justify-center"><i class="bi {{ $typeIcon }}"></i></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 dark:text-slate-100 truncate">{{ $a->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $typeLabel }}{{ $a->archived ? ' · arquivada' : '' }}</p>
                            </div>
                            @if ($share > 0)
                                <span class="shrink-0 rounded-full bg-indigo-50 dark:bg-indigo-500/15 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:text-indigo-300">{{ $share }}% do total</span>
                            @endif
                        </div>
                        <p class="mt-3 text-2xl font-black {{ $balance >= 0 ? 'text-slate-800 dark:text-slate-100' : 'text-red-600' }}">R$ {{ number_format($balance, 2, ',', '.') }}</p>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $share }}%"></div></div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-2">
                                <p class="text-emerald-700/80 dark:text-emerald-300/80">Entrou no mês</p>
                                <p class="font-bold text-emerald-700 dark:text-emerald-300">R$ {{ number_format((float) ($m->income ?? 0), 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 px-2.5 py-2">
                                <p class="text-rose-700/80 dark:text-rose-300/80">Saiu no mês</p>
                                <p class="font-bold text-rose-700 dark:text-rose-300">R$ {{ number_format((float) ($m->expense ?? 0), 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                            <button type="button" wire:click="openTransfer({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-arrow-left-right"></i> Transferir</button>
                            <button type="button" wire:click="openEdit({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"><i class="bi bi-pencil"></i> Editar</button>
                            <button type="button" wire:click="toggleArchive({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i class="bi {{ $a->archived ? 'bi-box-arrow-up' : 'bi-archive' }}"></i> {{ $a->archived ? 'Reativar' : 'Arquivar' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm">
                <div class="flex items-center justify-between gap-2 px-4 pt-4 pb-2">
                    <p class="font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-clock-history mr-1 text-indigo-500"></i>Últimas movimentações</p>
                    <a href="{{ route('cashbook.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">Livro caixa</a>
                </div>
                @if ($recent->isEmpty())
                    <p class="px-4 pb-4 text-sm text-slate-500 dark:text-slate-400">Nada lançado nas contas ainda. Escolha a conta ao criar um lançamento no livro caixa.</p>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800 pb-2">
                        @foreach ($recent as $e)
                            <li class="flex items-center gap-3 px-4 py-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $e->type_id == 1 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300' }}"><i class="bi {{ $e->type_id == 1 ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $e->description ?: 'Sem descrição' }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $accById[$e->account_id]->name ?? 'Conta' }} · {{ \Illuminate\Support\Carbon::parse($e->date)->format('d/m') }}</p>
                                </div>
                                <span class="shrink-0 text-sm font-bold {{ $e->type_id == 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $e->type_id == 1 ? '+' : '−' }} R$ {{ number_format((float) $e->value, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" wire:keydown.escape="$set('showForm', false)">
            <form wire:submit="save" class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $editingId ? 'Editar conta' : 'Nova conta' }}</h3>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Nome</label>
                    <input type="text" wire:model="name" placeholder="Ex: Nubank, Carteira" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Tipo</label>
                    <select wire:model="type" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                        @foreach ($types as $key => [$label]) <option value="{{ $key }}">{{ $label }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Saldo de hoje (antes dos lançamentos)</label>
                    <x-money-input model="initial_balance" :value="$initial_balance" :live="false" wire:key="acc-balance-{{ $editingId ?? 'new' }}" />
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-slate-200 font-semibold">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">Salvar</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showTransfer)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" wire:keydown.escape="$set('showTransfer', false)">
            <form wire:submit="transfer" class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Transferir entre contas</h3>
                @php $active = $accounts->filter(fn ($r) => ! $r['account']->archived); @endphp
                @if ($active->count() < 2)
                    <p class="text-sm text-slate-500">Cadastre pelo menos duas contas para transferir.</p>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-500">De</label>
                            <select wire:model="fromId" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                                <option value="">Escolha...</option>
                                @foreach ($active as $r) <option value="{{ $r['account']->id }}">{{ $r['account']->name }}</option> @endforeach
                            </select>
                            @error('fromId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Para</label>
                            <select wire:model="toId" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                                <option value="">Escolha...</option>
                                @foreach ($active as $r) <option value="{{ $r['account']->id }}">{{ $r['account']->name }}</option> @endforeach
                            </select>
                            @error('toId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Valor</label>
                        <x-money-input model="transferValue" :value="0" :live="false" />
                        @error('transferValue') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Data</label>
                            <input type="date" wire:model="transferDate" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Observação</label>
                            <input type="text" wire:model="transferNote" maxlength="200" class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-white">
                        </div>
                    </div>
                @endif
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showTransfer', false)" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-slate-200 font-semibold">Cancelar</button>
                    @if ($active->count() >= 2)
                        <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">Transferir</button>
                    @endif
                </div>
            </form>
        </div>
    @endif
</div>
