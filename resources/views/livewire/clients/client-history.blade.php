<div class="client-history-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-client-page-header :client="$client" title="Histórico" icon="bi-clock-history" active="historico">
            <x-slot:actions>
                @if($waPhone)
                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="crm-btn crm-btn-wa" title="Abrir conversa no WhatsApp">
                        <i class="bi bi-whatsapp"></i><span class="hidden sm:inline">WhatsApp</span>
                    </a>
                @endif
                <a href="{{ route('crm.funnel', ['novo' => $client->id]) }}" wire:navigate class="crm-btn" title="Colocar um orçamento deste cliente no funil">
                    <i class="bi bi-funnel"></i><span class="hidden sm:inline">Novo negócio</span>
                </a>
                <a href="{{ route('tasks.index') }}" wire:navigate class="crm-btn" title="Todas as tarefas">
                    <i class="bi bi-alarm"></i><span class="hidden sm:inline">Tarefas</span>
                </a>
            </x-slot:actions>
        </x-client-page-header>

        @php
            $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
            $ago = fn ($d) => $d ? $d->locale('pt_BR')->diffForHumans() : 'nunca';
            $tz = \App\Models\ConsortiumNotification::DISPLAY_TZ;
        @endphp

        <div class="cmx cmx-4 mb-4">
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-chat-square-text text-indigo-500"></i>Contatos</span>
                <span class="cmx-value">{{ $stats['notes'] }}</span>
                <span class="cmx-hint">anotados</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-alarm text-amber-500"></i>Tarefas</span>
                <span class="cmx-value {{ $stats['open_tasks'] ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ $stats['open_tasks'] }}</span>
                <span class="cmx-hint">em aberto</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-telephone text-sky-500"></i>Último contato</span>
                <span class="cmx-value crm-small">{{ $ago($stats['last_contact']) }}</span>
                <span class="cmx-hint">anotado aqui</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-bag-check text-emerald-500"></i>Última compra</span>
                <span class="cmx-value crm-small">{{ $ago($stats['last_sale']) }}</span>
                <span class="cmx-hint">venda registrada</span>
            </div>
        </div>

        <div class="crm-grid">
            {{-- ===== Anotar contato + tarefas ===== --}}
            <div class="crm-side">
                <section class="crs-card" x-data x-on:crm-note-focus.window="$nextTick(() => { $refs.body.focus(); $el.scrollIntoView({ behavior: 'smooth', block: 'start' }); })">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-pencil-square"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">{{ $editingNoteId ? 'Editar anotação' : 'Anotar contato' }}</h2>
                            <p class="crs-sub">O que conversou, como foi e o próximo passo</p>
                        </div>
                    </div>

                    <form wire:submit="saveNote" class="p-3 sm:p-4 flex flex-col gap-3">
                        <div class="crm-chips" role="radiogroup" aria-label="Tipo de contato">
                            @foreach($kinds as $key => $k)
                                <button type="button" wire:click="$set('noteKind', '{{ $key }}')" role="radio" aria-checked="{{ $noteKind === $key ? 'true' : 'false' }}"
                                        class="crm-chip {{ $noteKind === $key ? 'is-on' : '' }}">
                                    <i class="bi {{ $k['icon'] }}"></i>{{ $k['label'] }}
                                </button>
                            @endforeach
                        </div>

                        <div>
                            <textarea x-ref="body" wire:model="noteBody" rows="3" class="crm-input" placeholder="Ex.: Perguntou do rack de ferro 1,60 m, vai ver a medida e me responde."></textarea>
                            @error('noteBody') <p class="crm-err">{{ $message }}</p> @enderror
                        </div>

                        <label class="crm-label">Quando foi
                            <input type="datetime-local" wire:model="noteDate" class="crm-input">
                        </label>

                        @unless($editingNoteId)
                            <div class="crm-task-box {{ $withTask ? 'is-open' : '' }}">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">
                                    <input type="checkbox" wire:model.live="withTask" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <i class="bi bi-alarm text-amber-500"></i> Criar tarefa (próximo passo)
                                </label>
                                @if($withTask)
                                    <div class="mt-3 flex flex-col gap-2">
                                        <input type="text" wire:model="taskTitle" class="crm-input" placeholder="Ex.: Mandar foto do rack pronto" maxlength="160">
                                        @error('taskTitle') <p class="crm-err">{{ $message }}</p> @enderror
                                        <div class="crm-chips">
                                            @foreach([0 => 'Hoje', 1 => 'Amanhã', 3 => 'Em 3 dias', 7 => 'Em 1 semana', 30 => 'Em 1 mês'] as $d => $lbl)
                                                @php $val = now($tz)->addDays($d)->toDateString(); @endphp
                                                <button type="button" wire:click="quickDate({{ $d }})" class="crm-chip {{ $taskDate === $val ? 'is-on' : '' }}">{{ $lbl }}</button>
                                            @endforeach
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="crm-label">Dia
                                                <input type="date" wire:model.live="taskDate" class="crm-input">
                                            </label>
                                            <label class="crm-label">Hora (opcional)
                                                <input type="time" wire:model="taskTime" class="crm-input">
                                            </label>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-bell"></i> Você recebe um aviso no sino no dia.</p>
                                    </div>
                                @endif
                            </div>
                        @endunless

                        <div class="flex items-center gap-2">
                            <button type="submit" class="crm-btn crm-btn-primary flex-1 justify-center" wire:loading.attr="disabled" wire:target="saveNote">
                                <i class="bi bi-check2"></i>{{ $editingNoteId ? 'Salvar alteração' : 'Salvar' }}
                            </button>
                            @if($editingNoteId)
                                <button type="button" wire:click="cancelEdit" class="crm-btn">Cancelar</button>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="crs-card mt-4">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-alarm"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">Tarefas deste cliente <span class="crs-count">{{ $openTasks->count() }}</span></h2>
                            <p class="crs-sub">Toque no círculo quando fizer</p>
                        </div>
                        <a href="{{ route('tasks.index', ['cliente' => $client->id]) }}" wire:navigate class="crs-link"><i class="bi bi-list-check"></i>Ver</a>
                    </div>
                    <div class="crs-list">
                        @forelse($openTasks as $task)
                            <div class="crs-row" wire:key="ot-{{ $task->id }}">
                                <button type="button" wire:click="toggleTask({{ $task->id }})" class="crm-check" title="Marcar como feita"><i class="bi bi-circle"></i></button>
                                <div class="min-w-0 flex-1">
                                    <p class="crs-row-title">{{ $task->title }}</p>
                                    <p class="crs-row-meta {{ $task->isLate() ? 'text-rose-600 dark:text-rose-400 font-semibold' : '' }}">
                                        <i class="bi bi-calendar-event"></i> {{ $task->dueLabel() }}{{ $task->isLate() ? ' · atrasada' : '' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="crs-empty py-6">
                                <i class="bi bi-check2-all"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhuma tarefa em aberto</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- ===== Linha do tempo ===== --}}
            <section class="crs-card crm-main">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-clock-history"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">Linha do tempo</h2>
                        <p class="crs-sub">Vendas, pagamentos, site, mensagens e contatos em ordem</p>
                    </div>
                </div>

                <div class="px-3 sm:px-4 pt-3 -mb-1 overflow-x-auto">
                    <div class="crm-chips flex-nowrap min-w-max pb-1">
                        @foreach($groups as $key => $label)
                            <button type="button" wire:click="setGroup('{{ $key }}')" class="crm-chip {{ $group === $key ? 'is-on' : '' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="crm-tl p-3 sm:p-4">
                    @php $lastDay = null; @endphp
                    @forelse($items as $it)
                        @php
                            $at = $it['at']->copy()->setTimezone($tz);
                            $day = $at->toDateString();
                            $dayLabel = $at->isToday() ? 'Hoje' : ($at->isYesterday() ? 'Ontem' : $at->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'));
                        @endphp
                        @if($day !== $lastDay)
                            <div class="crm-tl-day">{{ $dayLabel }}</div>
                            @php $lastDay = $day; @endphp
                        @endif
                        <div class="crm-tl-item" wire:key="tl-{{ $loop->index }}-{{ $it['note_id'] ?? '' }}-{{ $it['task_id'] ?? '' }}">
                            <span class="crm-tl-dot" style="{{ \App\Models\ConsortiumNotification::toneStyle($it['tone'], 0.16) }}"><i class="bi {{ $it['icon'] }}"></i></span>
                            <div class="crm-tl-body">
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="crm-tl-title">
                                            @if($it['url'])
                                                <a href="{{ $it['url'] }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-300">{{ $it['title'] }}</a>
                                            @else
                                                {{ $it['title'] }}
                                            @endif
                                            @unless($it['no_time'])<span class="crm-tl-time">{{ $at->format('H:i') }}</span>@endunless
                                        </p>
                                        @if($it['detail'])
                                            <p class="crm-tl-detail {{ $it['note_id'] ? 'is-note' : '' }}">{{ $it['detail'] }}</p>
                                        @endif
                                    </div>
                                    @if($it['value'])
                                        <span class="crs-row-value shrink-0">{{ $money($it['value']) }}</span>
                                    @endif
                                    @if($it['note_id'])
                                        <div class="flex items-center gap-0.5 shrink-0">
                                            <button type="button" wire:click="editNote({{ $it['note_id'] }})" class="crm-icon-btn" title="Editar"><i class="bi bi-pencil"></i></button>
                                            <button type="button" wire:click="deleteNote({{ $it['note_id'] }})" wire:confirm="Apagar esta anotação?" class="crm-icon-btn hover:!text-rose-600" title="Apagar"><i class="bi bi-trash"></i></button>
                                        </div>
                                    @elseif($it['task_id'])
                                        <button type="button" wire:click="toggleTask({{ $it['task_id'] }})" class="crm-icon-btn" title="{{ $it['kind'] === 'feita' ? 'Reabrir tarefa' : 'Marcar como feita' }}">
                                            <i class="bi {{ $it['kind'] === 'feita' ? 'bi-arrow-counterclockwise' : 'bi-check2-circle' }}"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="crs-empty py-12">
                            <i class="bi bi-clock-history"></i>
                            <p class="font-semibold text-slate-600 dark:text-slate-300">Nada por aqui ainda</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Anote o primeiro contato ao lado.</p>
                        </div>
                    @endforelse

                    @if($hasMore)
                        <div class="pt-2 text-center">
                            <button type="button" wire:click="loadMore" class="crm-btn"><i class="bi bi-arrow-down-circle"></i>Ver mais antigos</button>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <x-client-rows-css />
    <x-crm-css />
</div>
