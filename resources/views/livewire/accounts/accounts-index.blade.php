<div class="w-full px-4 sm:px-6 py-4 space-y-4">
    <x-sales-header title="Contas e Saldos"
        description="Quanto você tem em cada lugar e transferências entre contas"
        icon="bi-bank" iconColor="blue" :back-route="route('cashbook.index')">
        <x-slot name="actions">
            <div class="flex gap-2">
                <button type="button" wire:click="openTransfer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 font-bold text-sm">
                    <i class="bi bi-arrow-left-right"></i> Transferir
                </button>
                <button type="button" wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm">
                    <i class="bi bi-plus-lg"></i> Nova conta
                </button>
            </div>
        </x-slot>
    </x-sales-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-3">
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($accounts as $row)
                @php [$a, $balance] = [$row['account'], $row['balance']]; [$typeLabel, $typeIcon] = $types[$a->type] ?? ['Conta', 'bi-bank']; @endphp
                <div wire:key="acc-{{ $a->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 p-4 {{ $a->archived ? 'opacity-50' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 flex items-center justify-center"><i class="bi {{ $typeIcon }}"></i></div>
                        <div class="flex-1">
                            <p class="font-bold text-slate-800 dark:text-slate-100">{{ $a->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $typeLabel }}{{ $a->archived ? ' · arquivada' : '' }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-black {{ $balance >= 0 ? 'text-slate-800 dark:text-slate-100' : 'text-red-600' }}">R$ {{ number_format($balance, 2, ',', '.') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                        <button type="button" wire:click="openTransfer({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300"><i class="bi bi-arrow-left-right"></i> Transferir</button>
                        <button type="button" wire:click="openEdit({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300"><i class="bi bi-pencil"></i> Editar</button>
                        <button type="button" wire:click="toggleArchive({{ $a->id }})" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300">
                            <i class="bi {{ $a->archived ? 'bi-box-arrow-up' : 'bi-archive' }}"></i> {{ $a->archived ? 'Reativar' : 'Arquivar' }}
                        </button>
                    </div>
                </div>
            @endforeach
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
