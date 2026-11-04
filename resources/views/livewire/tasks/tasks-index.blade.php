<div class="tasks-index-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Tarefas" subtitle="Follow-ups e próximos passos com os clientes" icon="bi-alarm"
                       section="clientes" tabs-section="clientes" tabs-active="tarefas">
            <x-slot:actions>
                <button type="button" wire:click="openForm" class="crm-btn crm-btn-primary" title="Nova tarefa">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Nova tarefa</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @php $tz = \App\Models\ConsortiumNotification::DISPLAY_TZ; @endphp

        <div class="cmx mb-4">
            <button type="button" wire:click="setTab('hoje')" class="cmx-item {{ $tab === 'hoje' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-sun text-amber-500"></i>Hoje</span>
                <span class="cmx-value">{{ $counts['hoje'] }}</span>
                <span class="cmx-hint {{ $counts['atrasadas'] ? '!text-rose-600 dark:!text-rose-400 font-semibold' : '' }}">{{ $counts['atrasadas'] ? $counts['atrasadas'] . ' atrasada' . ($counts['atrasadas'] > 1 ? 's' : '') : 'nenhuma atrasada' }}</span>
            </button>
            <button type="button" wire:click="setTab('proximas')" class="cmx-item {{ $tab === 'proximas' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-calendar-week text-sky-500"></i>Próximas</span>
                <span class="cmx-value">{{ $counts['proximas'] }}</span>
                <span class="cmx-hint">a partir de amanhã</span>
            </button>
            <button type="button" wire:click="setTab('feitas')" class="cmx-item {{ $tab === 'feitas' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-check2-all text-emerald-500"></i>Feitas</span>
                <span class="cmx-value">{{ $counts['feitas'] }}</span>
                <span class="cmx-hint">concluídas</span>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="crm-input !pl-9" placeholder="Buscar tarefa ou cliente">
            </div>
            @if($filterClient)
                <span class="crm-chip is-on self-start sm:self-auto !py-2">
                    <i class="bi bi-person"></i>{{ $filterClient->name }}
                    <button type="button" wire:click="clearClient" class="ml-1" title="Ver todas"><i class="bi bi-x-lg"></i></button>
                </span>
            @endif
        </div>

        <div class="crm-grid {{ $showForm ? '' : 'crm-grid-single' }}">
            @if($showForm)
                {{-- ===== Nova / editar tarefa ===== --}}
                <section class="crs-card crm-side" x-data x-init="$nextTick(() => { $refs.title.focus(); if (window.innerWidth < 1024) $el.scrollIntoView({ behavior: 'smooth', block: 'start' }); })">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-alarm"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">{{ $editingId ? 'Editar tarefa' : 'Nova tarefa' }}</h2>
                            <p class="crs-sub">Você recebe um aviso no sino no dia</p>
                        </div>
                        <button type="button" wire:click="cancel" class="crm-icon-btn" title="Fechar"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <form wire:submit="save" class="p-3 sm:p-4 flex flex-col gap-3">
                        <label class="crm-label">O que fazer
                            <input x-ref="title" type="text" wire:model="title" class="crm-input" placeholder="Ex.: Ligar para confirmar a entrega" maxlength="160">
                        </label>
                        @error('title') <p class="crm-err -mt-2">{{ $message }}</p> @enderror

                        <div class="crm-label">Cliente (opcional)
                            @if($formClient)
                                <div class="flex items-center gap-2 rounded-xl border border-indigo-200 dark:border-indigo-500/40 bg-indigo-50/70 dark:bg-indigo-500/10 px-3 py-2">
                                    <i class="bi bi-person-check text-indigo-600 dark:text-indigo-300"></i>
                                    <span class="flex-1 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $formClient->name }}</span>
                                    <button type="button" wire:click="$set('clientId', null)" class="crm-icon-btn !w-7 !h-7" title="Tirar cliente"><i class="bi bi-x-lg"></i></button>
                                </div>
                            @else
                                <div class="relative">
                                    <input type="search" wire:model.live.debounce.250ms="clientSearch" class="crm-input" placeholder="Digite o nome do cliente">
                                    @if($clientResults->isNotEmpty())
                                        <div class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                                            @foreach($clientResults as $c)
                                                <button type="button" wire:click="pickClient({{ $c->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-slate-800 flex items-center gap-2">
                                                    <i class="bi bi-person text-slate-400"></i>
                                                    <span class="flex-1 truncate font-semibold text-slate-800 dark:text-slate-100">{{ $c->name }}</span>
                                                    @if($c->phone)<span class="text-xs text-slate-400">{{ $c->phone }}</span>@endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="crm-chips">
                            @foreach([0 => 'Hoje', 1 => 'Amanhã', 3 => 'Em 3 dias', 7 => 'Em 1 semana', 30 => 'Em 1 mês'] as $d => $lbl)
                                @php $val = now($tz)->addDays($d)->toDateString(); @endphp
                                <button type="button" wire:click="quickDate({{ $d }})" class="crm-chip {{ $dueDate === $val ? 'is-on' : '' }}">{{ $lbl }}</button>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="crm-label">Dia
                                <input type="date" wire:model.live="dueDate" class="crm-input">
                            </label>
                            <label class="crm-label">Hora (opcional)
                                <input type="time" wire:model="dueTime" class="crm-input">
                            </label>
                        </div>
                        @error('dueDate') <p class="crm-err -mt-2">{{ $message }}</p> @enderror

                        <label class="crm-label">Detalhes (opcional)
                            <textarea wire:model="notes" rows="2" class="crm-input !min-h-[4rem]" placeholder="Algo para lembrar na hora"></textarea>
                        </label>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="crm-btn crm-btn-primary flex-1 justify-center" wire:loading.attr="disabled" wire:target="save">
                                <i class="bi bi-check2"></i>{{ $editingId ? 'Salvar alteração' : 'Criar tarefa' }}
                            </button>
                            <button type="button" wire:click="cancel" class="crm-btn">Cancelar</button>
                        </div>
                    </form>
                </section>
            @endif

            {{-- ===== Lista ===== --}}
            <section class="crs-card crm-main">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br {{ $tab === 'feitas' ? 'from-emerald-500 to-teal-500' : ($tab === 'proximas' ? 'from-sky-500 to-indigo-600' : 'from-amber-500 to-rose-500') }}">
                        <i class="bi {{ $tab === 'feitas' ? 'bi-check2-all' : ($tab === 'proximas' ? 'bi-calendar-week' : 'bi-sun') }}"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">{{ ['hoje' => 'Para hoje', 'proximas' => 'Próximas', 'feitas' => 'Feitas'][$tab] }} <span class="crs-count">{{ $counts[$tab] }}</span></h2>
                        <p class="crs-sub">{{ $tab === 'feitas' ? 'Toque no círculo verde para reabrir' : 'Toque no círculo quando fizer' }}</p>
                    </div>
                </div>

                <div class="crs-list {{ $showForm ? '' : 'is-grid crm-tasks-grid' }}">
                    @forelse($tasks as $task)
                        @php $wa = $task->client ? \App\Services\Clients\ClientContact::whatsappUrl($task->client->phone) : null; @endphp
                        <div class="crs-row crm-task-row {{ $task->done_at ? 'is-done' : '' }}" wire:key="task-{{ $task->id }}">
                            <button type="button" wire:click="toggle({{ $task->id }})" class="crm-check {{ $task->done_at ? 'is-done' : '' }}" title="{{ $task->done_at ? 'Reabrir' : 'Marcar como feita' }}">
                                <i class="bi {{ $task->done_at ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="crs-row-title" title="{{ $task->title }}">{{ $task->title }}</p>
                                <p class="crs-row-meta">
                                    @if($task->done_at)
                                        <i class="bi bi-check2"></i> Feita {{ $task->done_at->setTimezone($tz)->locale('pt_BR')->isoFormat('DD/MM [às] HH:mm') }}
                                    @else
                                        <span class="{{ $task->isLate() ? 'text-rose-600 dark:text-rose-400 font-semibold' : ($task->isToday() ? 'text-amber-600 dark:text-amber-400 font-semibold' : '') }}">
                                            <i class="bi bi-calendar-event"></i> {{ $task->dueLabel() }}{{ $task->isLate() ? ' · atrasada' : '' }}
                                        </span>
                                    @endif
                                    @if($task->client)
                                        · <a href="{{ route('clients.historico', $task->client->id) }}" wire:navigate class="font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">{{ $task->client->name }}</a>
                                    @endif
                                </p>
                                @if($task->notes)
                                    <p class="crs-row-meta !whitespace-normal mt-0.5">{{ \Illuminate\Support\Str::limit($task->notes, 140) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-0.5 shrink-0">
                                @if($wa && ! $task->done_at)
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="crm-icon-btn !text-green-600" title="WhatsApp do cliente"><i class="bi bi-whatsapp"></i></a>
                                @endif
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open" class="crm-icon-btn" title="Mais opções"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 z-20 mt-1 w-48 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1 text-sm">
                                        @unless($task->done_at)
                                            <button type="button" wire:click="postpone({{ $task->id }}, 1)" @click="open = false" class="crm-menu-item"><i class="bi bi-arrow-right-circle"></i>Adiar 1 dia</button>
                                            <button type="button" wire:click="postpone({{ $task->id }}, 7)" @click="open = false" class="crm-menu-item"><i class="bi bi-calendar-plus"></i>Adiar 1 semana</button>
                                        @endunless
                                        <button type="button" wire:click="edit({{ $task->id }})" @click="open = false" class="crm-menu-item"><i class="bi bi-pencil"></i>Editar</button>
                                        <button type="button" wire:click="delete({{ $task->id }})" wire:confirm="Apagar esta tarefa?" @click="open = false" class="crm-menu-item !text-rose-600"><i class="bi bi-trash"></i>Apagar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="crs-empty py-14">
                            <i class="bi {{ $tab === 'feitas' ? 'bi-inbox' : 'bi-check2-all' }}"></i>
                            <p class="font-semibold text-slate-600 dark:text-slate-300">{{ $tab === 'hoje' ? 'Tudo em dia por hoje' : ($tab === 'proximas' ? 'Nenhuma tarefa marcada' : 'Nenhuma tarefa feita ainda') }}</p>
                            @if($tab !== 'feitas')
                                <button type="button" wire:click="openForm" class="crs-link mt-1"><i class="bi bi-plus-lg"></i>Nova tarefa</button>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if($hasMore)
                    <div class="p-3 text-center border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="loadMore" class="crm-btn"><i class="bi bi-arrow-down-circle"></i>Ver mais</button>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <x-client-rows-css />
    <x-crm-css />
</div>
