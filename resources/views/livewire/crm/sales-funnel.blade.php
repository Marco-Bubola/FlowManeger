<div class="sales-funnel-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Funil de vendas" subtitle="Orçamentos e conversas até virarem venda" icon="bi-funnel"
                       section="clientes" tabs-section="clientes" tabs-active="funil">
            <x-slot:actions>
                <button type="button" wire:click="openForm" class="crm-btn crm-btn-primary" title="Novo negócio">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Novo negócio</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @php
            $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
            $tones = [
                'sky' => ['dot' => 'bg-sky-500', 'soft' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300'],
                'indigo' => ['dot' => 'bg-indigo-500', 'soft' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'],
                'amber' => ['dot' => 'bg-amber-500', 'soft' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'],
                'emerald' => ['dot' => 'bg-emerald-500', 'soft' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'],
                'rose' => ['dot' => 'bg-rose-500', 'soft' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'],
            ];
            $stages = \App\Models\CrmDeal::STAGES;
        @endphp

        {{-- ===== Números ===== --}}
        <div class="cmx fn-kpis mb-4">
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-hourglass-split text-indigo-500"></i>Em aberto</span>
                <span class="cmx-value">{{ $kpi['openCount'] }}</span>
                <span class="cmx-hint">{{ $kpi['openValue'] ? $money($kpi['openValue']) . ' em jogo' : 'nenhum valor ainda' }}</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-trophy text-emerald-500"></i>Ganhos no mês</span>
                <span class="cmx-value text-emerald-600 dark:text-emerald-400">{{ $kpi['wonCount'] }}</span>
                <span class="cmx-hint">{{ $kpi['wonValue'] ? $money($kpi['wonValue']) : 'nenhum este mês' }}</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-bullseye text-amber-500"></i>Fecham</span>
                <span class="cmx-value">{{ $kpi['rate'] === null ? '—' : $kpi['rate'] . '%' }}</span>
                <span class="cmx-hint">{{ $kpi['rate'] === null ? 'nos últimos 90 dias' : $kpi['won90'] . ' ganhos, ' . $kpi['lost90'] . ' perdidos (90 dias)' }}</span>
            </div>
            <div class="cmx-item fn-kpi-extra">
                <span class="cmx-label"><i class="bi bi-person-plus text-sky-500"></i>Leads</span>
                <span class="cmx-value">{{ $kpi['leads'] }}</span>
                <span class="cmx-hint">contatos que ainda não são clientes</span>
            </div>
            <div class="cmx-item fn-kpi-extra">
                <span class="cmx-label"><i class="bi bi-x-circle text-rose-500"></i>Mais perde por</span>
                <span class="cmx-value !text-base !leading-7">{{ $kpi['topLost'] ? (\App\Models\CrmDeal::LOST_REASONS[$kpi['topLost']->lost_reason] ?? 'Outro') : '—' }}</span>
                <span class="cmx-hint">{{ $kpi['topLost'] ? $kpi['topLost']->n . ' vez' . ($kpi['topLost']->n > 1 ? 'es' : '') . ' em 90 dias' : 'nenhum perdido' }}</span>
            </div>
        </div>

        {{-- ===== Busca e filtros ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="crm-input !pl-9" placeholder="Buscar pelo nome, telefone ou o que a pessoa quer">
            </div>
            <div class="flex gap-2">
                <select wire:model.live="sourceFilter" class="crm-input !w-auto flex-1 sm:flex-none">
                    <option value="">Todas as origens</option>
                    @foreach($sources as $k => $s)<option value="{{ $k }}">{{ $s['label'] }}</option>@endforeach
                </select>
                <button type="button" wire:click="toggleClosed" class="crm-chip {{ $showClosed ? 'is-on' : '' }} !py-2 whitespace-nowrap">
                    <i class="bi {{ $showClosed ? 'bi-eye-slash' : 'bi-eye' }}"></i>{{ $showClosed ? 'Esconder fechados' : 'Ganhos e perdidos' }}
                </button>
            </div>
        </div>

        {{-- ===== Sugestões de leads ===== --}}
        @if($suggestions->isNotEmpty() && $search === '' && $sourceFilter === '')
            <div class="fn-suggest mb-3" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 w-full text-left">
                    <i class="bi bi-lightbulb text-amber-500"></i>
                    <span class="flex-1">{{ $suggestions->count() }} {{ $suggestions->count() > 1 ? 'pessoas se cadastraram' : 'pessoa se cadastrou' }} nos últimos 30 dias e ainda não {{ $suggestions->count() > 1 ? 'compraram' : 'comprou' }}</span>
                    <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div x-show="open" x-cloak x-transition class="mt-2 flex flex-wrap gap-2">
                    @foreach($suggestions as $s)
                        <button type="button" wire:click="addSuggestion({{ $s->id }})" class="crm-chip !py-1.5" title="Colocar no funil">
                            <x-client-avatar :client="$s" size="w-5 h-5 text-[9px]" rounded="rounded-full" />
                            {{ \Illuminate\Support\Str::limit($s->name, 22) }}
                            @if($s->source && isset($sources[$s->source]))<i class="bi {{ $sources[$s->source]['icon'] }} text-slate-400"></i>@endif
                            <i class="bi bi-plus-lg text-indigo-500"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== Etapas no celular ===== --}}
        <div class="fn-stagebar mb-3">
            @foreach($columns as $key => $col)
                <button type="button" wire:click="setMobileStage('{{ $key }}')" class="fn-stagechip {{ $mobileStage === $key ? 'is-on' : '' }}">
                    <span class="w-2 h-2 rounded-full {{ $tones[$col['tone']]['dot'] }}"></span>{{ $col['short'] }}
                    <span class="fn-stagechip-n">{{ $col['items']->count() }}</span>
                </button>
            @endforeach
        </div>

        {{-- ===== Quadro ===== --}}
        <div class="fn-board fn-cols-{{ count($columns) }}">
            @foreach($columns as $key => $col)
                <section class="fn-col {{ $mobileStage === $key ? 'is-mobile' : '' }}" wire:key="col-{{ $key }}"
                         x-data="{ over: false }"
                         @dragover.prevent="over = true" @dragleave="over = false"
                         @drop.prevent="over = false; const id = $event.dataTransfer.getData('text/deal'); if (id) $wire.move(parseInt(id), '{{ $key }}')"
                         :class="over && 'is-over'">
                    <header class="fn-col-head">
                        <span class="fn-col-icon {{ $tones[$col['tone']]['soft'] }}"><i class="bi {{ $col['icon'] }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="fn-col-title">{{ $col['label'] }} <span class="crs-count">{{ $col['items']->count() }}</span></h2>
                            <p class="fn-col-sub">{{ $col['total'] ? $money($col['total']) : (in_array($key, ['ganho', 'perdido']) ? 'últimos 60 dias' : 'sem valor') }}</p>
                        </div>
                        @if(in_array($key, \App\Models\CrmDeal::OPEN))
                            <button type="button" wire:click="openForm('{{ $key }}')" class="crm-icon-btn" title="Novo negócio aqui"><i class="bi bi-plus-lg"></i></button>
                        @endif
                    </header>

                    <div class="fn-col-body">
                        @forelse($col['items'] as $deal)
                            @php
                                $wa = $deal->whatsappUrl();
                                $days = $deal->daysInStage();
                                $late = $deal->isOpen() && $deal->expected_date && $deal->expected_date->lt(today());
                                $src = $deal->source ? ($sources[$deal->source] ?? null) : null;
                            @endphp
                            <article class="fn-card" wire:key="deal-{{ $deal->id }}" draggable="true"
                                     @dragstart="$event.dataTransfer.setData('text/deal', '{{ $deal->id }}'); $el.classList.add('is-drag')"
                                     @dragend="$el.classList.remove('is-drag')">
                                <div class="flex items-start gap-2">
                                    @if($deal->client)
                                        <x-client-avatar :client="$deal->client" size="w-8 h-8 text-xs" rounded="rounded-full" />
                                    @else
                                        <span class="w-8 h-8 shrink-0 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center" title="Lead: ainda não é cliente"><i class="bi bi-person-plus"></i></span>
                                    @endif
                                    <button type="button" wire:click="edit({{ $deal->id }})" class="min-w-0 flex-1 text-left">
                                        <p class="fn-card-name">{{ $deal->displayName() }}
                                            @if($deal->isLead())<span class="fn-lead">lead</span>@endif
                                        </p>
                                        <p class="fn-card-title">{{ $deal->title }}</p>
                                    </button>
                                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                        <button type="button" @click="open = !open" class="crm-icon-btn !w-7 !h-7" title="Mais opções"><i class="bi bi-three-dots-vertical"></i></button>
                                        <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 z-30 mt-1 w-52 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl py-1 text-sm">
                                            <p class="px-3 pt-1 pb-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">Mover para</p>
                                            @foreach($stages as $sk => $sd)
                                                @if($sk !== $deal->stage)
                                                    <button type="button" wire:click="move({{ $deal->id }}, '{{ $sk }}')" @click="open = false" class="crm-menu-item"><i class="bi {{ $sd['icon'] }}"></i>{{ $sd['label'] }}</button>
                                                @endif
                                            @endforeach
                                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                                            <button type="button" wire:click="edit({{ $deal->id }})" @click="open = false" class="crm-menu-item"><i class="bi bi-pencil"></i>Editar</button>
                                            @if($deal->isLead())
                                                <button type="button" wire:click="convertLead({{ $deal->id }})" @click="open = false" class="crm-menu-item"><i class="bi bi-person-check"></i>Cadastrar como cliente</button>
                                            @else
                                                <a href="{{ route('clients.historico', $deal->client_id) }}" wire:navigate class="crm-menu-item"><i class="bi bi-clock-history"></i>Histórico do cliente</a>
                                                <a href="{{ route('tasks.index', ['cliente' => $deal->client_id]) }}" wire:navigate class="crm-menu-item"><i class="bi bi-alarm"></i>Tarefas do cliente</a>
                                            @endif
                                            @if($deal->sale_id)
                                                <a href="{{ route('sales.show', $deal->sale_id) }}" wire:navigate class="crm-menu-item"><i class="bi bi-receipt"></i>Ver a venda</a>
                                            @endif
                                            <button type="button" wire:click="delete({{ $deal->id }})" wire:confirm="Apagar este negócio do funil?" @click="open = false" class="crm-menu-item !text-rose-600"><i class="bi bi-trash"></i>Apagar</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="fn-card-meta">
                                    @if($deal->value)<span class="fn-value">{{ $money($deal->value) }}</span>@endif
                                    @if($src)<span title="Origem"><i class="bi {{ $src['icon'] }}"></i>{{ $src['label'] }}</span>@endif
                                    @if($deal->isOpen())
                                        @if($deal->expected_date)
                                            <span class="{{ $late ? 'text-rose-600 dark:text-rose-400 font-semibold' : '' }}" title="Previsão de fechar"><i class="bi bi-calendar-event"></i>{{ $deal->expected_date->format('d/m') }}</span>
                                        @endif
                                        <span class="{{ $days >= 7 ? 'text-amber-600 dark:text-amber-400 font-semibold' : '' }}" title="Tempo nesta etapa"><i class="bi bi-clock"></i>{{ $days === 0 ? 'hoje' : $days . 'd' }}</span>
                                    @elseif($deal->stage === 'perdido')
                                        <span class="text-rose-600 dark:text-rose-400"><i class="bi bi-x-circle"></i>{{ \App\Models\CrmDeal::LOST_REASONS[$deal->lost_reason] ?? 'Perdido' }}</span>
                                    @else
                                        <span class="text-emerald-600 dark:text-emerald-400"><i class="bi bi-check2-circle"></i>{{ $deal->closed_at?->timezone(\App\Models\ConsortiumNotification::DISPLAY_TZ)->format('d/m') }}</span>
                                    @endif
                                </div>
                                @if($deal->stage === 'perdido' && $deal->lost_note)
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $deal->lost_note }}</p>
                                @endif

                                <div class="fn-card-actions">
                                    @if($wa)
                                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="crm-icon-btn !w-8 !h-8 !text-green-600" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                    @endif
                                    @if($deal->isOpen())
                                        @php $nextKey = \App\Models\CrmDeal::OPEN[array_search($deal->stage, \App\Models\CrmDeal::OPEN) + 1] ?? 'ganho'; @endphp
                                        <button type="button" wire:click="move({{ $deal->id }}, 'perdido')" class="crm-icon-btn !w-8 !h-8 !text-rose-500" title="Perdido"><i class="bi bi-x-lg"></i></button>
                                        <button type="button" wire:click="next({{ $deal->id }})" class="fn-next {{ $nextKey === 'ganho' ? 'is-win' : '' }}">
                                            @if($nextKey === 'ganho') <i class="bi bi-trophy"></i>Ganhou @else {{ $stages[$nextKey]['short'] }}<i class="bi bi-arrow-right"></i> @endif
                                        </button>
                                    @else
                                        <button type="button" wire:click="reopen({{ $deal->id }})" class="fn-next"><i class="bi bi-arrow-counterclockwise"></i>Reabrir</button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="fn-empty">
                                <i class="bi {{ $col['icon'] }}"></i>
                                <p>{{ in_array($key, \App\Models\CrmDeal::OPEN) ? ($key === 'contato' ? 'Anote aqui quem pediu preço ou mostrou interesse.' : 'Arraste um negócio para cá ou use o botão da etapa no card.') : 'Nada fechado nos últimos 60 dias.' }}</p>
                                @if($key === 'contato')
                                    <button type="button" wire:click="openForm('contato')" class="crm-btn mt-1"><i class="bi bi-plus-lg"></i>Novo negócio</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    {{-- ===== Novo / editar negócio ===== --}}
    @if($showForm)
        <div class="fn-modal" wire:key="deal-form" x-data @keydown.escape.window="$wire.cancel()">
            <div class="fn-modal-bg" wire:click="cancel"></div>
            <section class="fn-sheet crs-card">
                <div class="crs-head">
                    <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-violet-600"><i class="bi bi-funnel"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="crs-title">{{ $editingId ? 'Editar negócio' : 'Novo negócio' }}</h2>
                        <p class="crs-sub">Um orçamento ou conversa que pode virar venda</p>
                    </div>
                    <button type="button" wire:click="cancel" class="crm-icon-btn" title="Fechar"><i class="bi bi-x-lg"></i></button>
                </div>
                <form wire:submit="save" class="p-3 sm:p-4 flex flex-col gap-3 overflow-y-auto">
                    <div class="fn-seg">
                        <button type="button" wire:click="setWho('cliente')" class="{{ $who === 'cliente' ? 'is-on' : '' }}"><i class="bi bi-person-check"></i>Já é cliente</button>
                        <button type="button" wire:click="setWho('lead')" class="{{ $who === 'lead' ? 'is-on' : '' }}"><i class="bi bi-person-plus"></i>Contato novo (lead)</button>
                    </div>

                    @if($who === 'cliente')
                        <div class="crm-label">Cliente
                            @if($pickedClient)
                                <div class="flex items-center gap-2 rounded-xl border border-indigo-200 dark:border-indigo-500/40 bg-indigo-50/60 dark:bg-indigo-500/10 px-2.5 py-2">
                                    <x-client-avatar :client="$pickedClient" size="w-7 h-7 text-[10px]" rounded="rounded-full" />
                                    <span class="flex-1 font-semibold text-sm text-slate-800 dark:text-slate-100 truncate">{{ $pickedClient->name }}</span>
                                    <button type="button" wire:click="$set('clientId', null)" class="crm-icon-btn !w-7 !h-7" title="Trocar cliente"><i class="bi bi-x-lg"></i></button>
                                </div>
                            @else
                                <input type="search" wire:model.live.debounce.250ms="clientSearch" class="crm-input" placeholder="Digite o nome ou telefone">
                                @if($clientResults->isNotEmpty())
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden">
                                        @foreach($clientResults as $c)
                                            <button type="button" wire:click="pickClient({{ $c->id }})" class="w-full flex items-center gap-2 px-2.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                                <x-client-avatar :client="$c" size="w-7 h-7 text-[10px]" rounded="rounded-full" />
                                                <span class="flex-1 text-sm font-medium text-slate-700 dark:text-slate-200 truncate">{{ $c->name }}</span>
                                                <span class="text-[11px] text-slate-400">{{ $c->phone }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(mb_strlen(trim($clientSearch)) >= 2)
                                    <p class="text-xs text-slate-500">Ninguém com esse nome. <button type="button" wire:click="setWho('lead')" class="font-semibold text-indigo-600 dark:text-indigo-300 underline">Anotar como contato novo</button></p>
                                @endif
                            @endif
                        </div>
                        @error('clientId') <p class="crm-err -mt-2">{{ $message }}</p> @enderror
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="crm-label">Nome
                                <input type="text" wire:model="name" class="crm-input" placeholder="Ex.: Ana do Instagram" maxlength="120">
                            </label>
                            <label class="crm-label">WhatsApp (opcional)
                                <input type="tel" wire:model="phone" class="crm-input" placeholder="(11) 98765-4321" maxlength="30">
                            </label>
                        </div>
                        @error('name') <p class="crm-err -mt-2">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-1">Quando ganhar, o contato vira cliente sozinho.</p>
                    @endif

                    <label class="crm-label">O que a pessoa quer
                        <input type="text" wire:model="title" class="crm-input" placeholder="Ex.: Mesa de jantar 6 lugares em ferro" maxlength="160">
                    </label>
                    @error('title') <p class="crm-err -mt-2">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-2 gap-3">
                        <label class="crm-label">Valor (opcional)
                            <input type="text" inputmode="decimal" wire:model="value" class="crm-input" placeholder="0,00">
                        </label>
                        <label class="crm-label">Previsão de fechar
                            <input type="date" wire:model="expectedDate" class="crm-input">
                        </label>
                    </div>
                    @error('value') <p class="crm-err -mt-2">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-2 gap-3">
                        <label class="crm-label">Etapa
                            <select wire:model="stage" class="crm-input">
                                @foreach($stages as $sk => $sd)
                                    @if(in_array($sk, \App\Models\CrmDeal::OPEN) || $editingId)<option value="{{ $sk }}">{{ $sd['label'] }}</option>@endif
                                @endforeach
                            </select>
                        </label>
                        <label class="crm-label">Como chegou
                            <select wire:model="source" class="crm-input">
                                <option value="">{{ $who === 'cliente' ? 'Igual ao cliente' : 'Não sei' }}</option>
                                @foreach($sources as $k => $s)<option value="{{ $k }}">{{ $s['label'] }}</option>@endforeach
                            </select>
                        </label>
                    </div>

                    <label class="crm-label">Anotações (opcional)
                        <textarea wire:model="notes" rows="2" class="crm-input !min-h-[4rem]" placeholder="Medidas, cor, prazo combinado..."></textarea>
                    </label>

                    <div class="flex gap-2 pt-1">
                        <button type="submit" class="crm-btn crm-btn-primary flex-1 justify-center" wire:loading.attr="disabled" wire:target="save">
                            <i class="bi bi-check2"></i>{{ $editingId ? 'Salvar alteração' : 'Colocar no funil' }}
                        </button>
                        <button type="button" wire:click="cancel" class="crm-btn">Cancelar</button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    {{-- ===== Ganhou ===== --}}
    @if($winDeal)
        <div class="fn-modal" wire:key="deal-win">
            <div class="fn-modal-bg" wire:click="closeDialogs"></div>
            <section class="fn-sheet fn-sheet-sm crs-card p-4 flex flex-col gap-3 text-center">
                <div class="text-4xl">🎉</div>
                <h2 class="text-lg font-black text-slate-800 dark:text-white">Fechou com {{ $winDeal->displayName() }}!</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $winDeal->title }}{{ $winDeal->value ? ' · R$ ' . number_format((float) $winDeal->value, 2, ',', '.') : '' }}
                    @if($winDeal->isLead())<br><span class="text-xs">O contato vira cliente agora.</span>@endif
                </p>
                <button type="button" wire:click="win(true)" class="crm-btn crm-btn-primary justify-center"><i class="bi bi-cart-plus"></i>Lançar a venda agora</button>
                <button type="button" wire:click="win(false)" class="crm-btn justify-center"><i class="bi bi-trophy"></i>Só marcar como ganho</button>
                <button type="button" wire:click="closeDialogs" class="text-xs font-semibold text-slate-400 hover:text-slate-600">Voltar</button>
            </section>
        </div>
    @endif

    {{-- ===== Perdeu ===== --}}
    @if($loseDeal)
        <div class="fn-modal" wire:key="deal-lose">
            <div class="fn-modal-bg" wire:click="closeDialogs"></div>
            <section class="fn-sheet fn-sheet-sm crs-card p-4 flex flex-col gap-3">
                <div>
                    <h2 class="text-base font-black text-slate-800 dark:text-white">Por que não fechou?</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $loseDeal->displayName() }} · {{ $loseDeal->title }}</p>
                </div>
                <div class="crm-chips">
                    @foreach(\App\Models\CrmDeal::LOST_REASONS as $rk => $rl)
                        <button type="button" wire:click="$set('lostReason', '{{ $rk }}')" class="crm-chip {{ $lostReason === $rk ? 'is-on' : '' }}">{{ $rl }}</button>
                    @endforeach
                </div>
                @error('lostReason') <p class="crm-err">{{ $message }}</p> @enderror
                <label class="crm-label">Detalhe (opcional)
                    <input type="text" wire:model="lostNote" class="crm-input" placeholder="Ex.: achou outra mesa por R$ 900" maxlength="255">
                </label>
                <div class="flex gap-2">
                    <button type="button" wire:click="lose" class="crm-btn crm-btn-primary flex-1 justify-center !bg-rose-600 !border-rose-600"><i class="bi bi-x-circle"></i>Marcar como perdido</button>
                    <button type="button" wire:click="closeDialogs" class="crm-btn">Voltar</button>
                </div>
            </section>
        </div>
    @endif

    <x-client-rows-css />
    <x-crm-css />
    <style>
        .sales-funnel-page .fn-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sales-funnel-page .fn-kpi-extra { display: none; }
        @media (min-width: 1024px) { .sales-funnel-page .fn-kpis { grid-template-columns: repeat(5, minmax(0, 1fr)); } .sales-funnel-page .fn-kpi-extra { display: flex; } }
        .fn-suggest { padding: .65rem .85rem; border-radius: 1rem; background: rgb(255 251 235); border: 1px solid rgb(253 230 138); }
        .dark .fn-suggest { background: rgb(69 26 3 / .3); border-color: rgb(146 64 14 / .5); }

        .fn-stagebar { display: flex; gap: .4rem; overflow-x: auto; scrollbar-width: none; max-width: 100%; }
        .fn-stagebar::-webkit-scrollbar { display: none; }
        .fn-stagechip { display: inline-flex; align-items: center; gap: .4rem; flex-shrink: 0; padding: .45rem .8rem; border-radius: 999px; font-size: .8rem; font-weight: 700; background: #fff; border: 1px solid rgb(226 232 240); color: rgb(71 85 105); }
        .dark .fn-stagechip { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(203 213 225); }
        .fn-stagechip.is-on { background: rgb(79 70 229); border-color: rgb(79 70 229); color: #fff; }
        .fn-stagechip-n { min-width: 1.25rem; padding: 0 .3rem; border-radius: 999px; background: rgb(241 245 249); color: rgb(71 85 105); font-size: .7rem; text-align: center; }
        .dark .fn-stagechip-n { background: rgb(30 41 59); color: rgb(203 213 225); }
        .fn-stagechip.is-on .fn-stagechip-n { background: rgb(255 255 255 / .25); color: #fff; }

        .fn-board { display: grid; gap: .75rem; align-items: start; }
        .fn-col { display: none; flex-direction: column; min-width: 0; border-radius: 1.1rem; background: rgb(248 250 252); border: 1px solid rgb(226 232 240 / .8); transition: box-shadow .15s, border-color .15s; }
        .dark .fn-col { background: rgb(15 23 42 / .6); border-color: rgb(51 65 85 / .6); }
        .fn-col.is-mobile { display: flex; }
        .fn-col.is-over { border-color: rgb(99 102 241); box-shadow: 0 0 0 3px rgb(99 102 241 / .18); }
        .fn-col-head { display: flex; align-items: center; gap: .6rem; padding: .7rem .75rem .5rem; }
        .fn-col-icon { width: 2rem; height: 2rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: .7rem; }
        .fn-col-title { font-size: .85rem; font-weight: 800; color: rgb(30 41 59); display: flex; align-items: center; gap: .35rem; }
        .dark .fn-col-title { color: #fff; }
        .fn-col-sub { font-size: .7rem; color: rgb(100 116 139); }
        .fn-col-body { display: flex; flex-direction: column; gap: .5rem; padding: .25rem .5rem .6rem; min-height: 6rem; }
        .fn-card { padding: .65rem .7rem; border-radius: .9rem; background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 1px 2px rgb(15 23 42 / .05); cursor: grab; }
        .dark .fn-card { background: rgb(15 23 42); border-color: rgb(51 65 85 / .8); }
        .fn-card.is-drag { opacity: .45; }
        .fn-card-name { font-size: .85rem; font-weight: 700; color: rgb(30 41 59); display: flex; align-items: center; gap: .3rem; min-width: 0; }
        .dark .fn-card-name { color: #fff; }
        .fn-card-title { font-size: .78rem; color: rgb(71 85 105); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .dark .fn-card-title { color: rgb(203 213 225); }
        .fn-lead { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: .05rem .35rem; border-radius: .35rem; background: rgb(224 242 254); color: rgb(3 105 161); }
        .dark .fn-lead { background: rgb(14 165 233 / .2); color: rgb(125 211 252); }
        .fn-card-meta { display: flex; flex-wrap: wrap; gap: .25rem .6rem; margin-top: .45rem; font-size: .7rem; color: rgb(100 116 139); }
        .fn-card-meta span { display: inline-flex; align-items: center; gap: .2rem; }
        .fn-value { font-weight: 800; color: rgb(15 23 42); }
        .dark .fn-value { color: #fff; }
        .fn-card-actions { display: flex; align-items: center; justify-content: flex-end; gap: .35rem; margin-top: .5rem; }
        .fn-next { display: inline-flex; align-items: center; gap: .3rem; padding: .35rem .65rem; border-radius: .6rem; font-size: .72rem; font-weight: 800; background: rgb(238 242 255); color: rgb(67 56 202); }
        .dark .fn-next { background: rgb(99 102 241 / .2); color: rgb(199 210 254); }
        .fn-next.is-win { background: rgb(16 185 129); color: #fff; }
        .fn-empty { display: flex; flex-direction: column; align-items: center; gap: .35rem; padding: 1.25rem .75rem; text-align: center; font-size: .78rem; color: rgb(100 116 139); }
        .fn-empty > i { font-size: 1.4rem; opacity: .5; }

        @media (min-width: 640px) and (max-width: 1023px) { .fn-col-body { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; } .fn-col-body > .fn-empty { grid-column: 1 / -1; } }
        @media (min-width: 1024px) {
            .fn-stagebar { display: none; }
            .fn-col { display: flex; }
            .fn-board.fn-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .fn-board.fn-cols-5 { grid-template-columns: repeat(5, minmax(220px, 1fr)); overflow-x: auto; padding-bottom: .5rem; }
            .fn-col-body { max-height: calc(100vh - 21rem); overflow-y: auto; }
        }
        @media (min-width: 2200px) { .fn-board.fn-cols-3 { grid-template-columns: repeat(3, minmax(0, 520px)); } .fn-board.fn-cols-5 { grid-template-columns: repeat(5, minmax(0, 420px)); } }

        .fn-modal { position: fixed; inset: 0; z-index: 80; display: flex; align-items: flex-end; justify-content: center; }
        .fn-modal-bg { position: absolute; inset: 0; background: rgb(15 23 42 / .5); backdrop-filter: blur(2px); }
        .fn-sheet { position: relative; width: 100%; max-width: 34rem; max-height: 92vh; display: flex; flex-direction: column; border-radius: 1.25rem 1.25rem 0 0 !important; padding-bottom: env(safe-area-inset-bottom); animation: fn-up .2s ease-out; }
        .fn-sheet-sm { max-width: 26rem; }
        @media (min-width: 640px) { .fn-modal { align-items: center; padding: 1rem; } .fn-sheet { border-radius: 1.25rem !important; } }
        @keyframes fn-up { from { transform: translateY(1.5rem); opacity: 0; } to { transform: none; opacity: 1; } }
        .fn-seg { display: grid; grid-template-columns: 1fr 1fr; gap: .25rem; padding: .25rem; border-radius: .85rem; background: rgb(241 245 249); }
        .dark .fn-seg { background: rgb(30 41 59); }
        .fn-seg button { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .5rem; border-radius: .65rem; font-size: .8rem; font-weight: 700; color: rgb(100 116 139); }
        .fn-seg button.is-on { background: #fff; color: rgb(67 56 202); box-shadow: 0 1px 3px rgb(15 23 42 / .12); }
        .dark .fn-seg button.is-on { background: rgb(15 23 42); color: rgb(199 210 254); }
    </style>
</div>
