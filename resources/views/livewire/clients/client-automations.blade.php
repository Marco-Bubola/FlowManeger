<div class="client-automations-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-page-header title="Automações" subtitle="Mensagens prontas no dia certo: aniversário, pós-venda e cliente sumido" icon="bi-magic"
                       section="clientes" tabs-section="clientes" tabs-active="automacoes">
            <x-slot:actions>
                <a href="{{ route('tasks.index') }}" wire:navigate class="crm-btn" title="Tarefas">
                    <i class="bi bi-alarm"></i><span class="hidden sm:inline">Tarefas</span>
                    @if($pending)<span class="ml-0.5 inline-flex min-w-[20px] h-5 px-1.5 items-center justify-center rounded-full bg-amber-500 text-white text-[11px] font-bold">{{ $pending }}</span>@endif
                </a>
            </x-slot:actions>
        </x-page-header>

        @php $tones = \App\Models\ConsortiumNotification::TONES; $tz = \App\Models\ConsortiumNotification::DISPLAY_TZ; @endphp

        <div class="crm-how mb-4">
            <i class="bi bi-lightbulb text-amber-500 text-lg shrink-0"></i>
            <p>Toda manhã, às 8h, cada automação ligada cria uma <b>tarefa com a mensagem pronta</b> e avisa no sino. Em <a href="{{ route('tasks.index') }}" wire:navigate class="font-semibold text-indigo-600 dark:text-indigo-300 underline">Tarefas</a> é só tocar no WhatsApp: a conversa abre com o texto escrito. Clientes que não querem receber ofertas ficam de fora{{ $optOut ? " ({$optOut} hoje)" : '' }}.</p>
        </div>

        <div class="crm-grid">
            <div class="crm-main flex flex-col gap-4 order-1">
                @foreach($kinds as $key => $def)
                    @php $on = $auto[$key]['on'] ?? false; $n = count($today[$key] ?? []); @endphp
                    <section class="crs-card crm-auto {{ $on ? 'is-on' : '' }}" wire:key="auto-{{ $key }}">
                        <div class="crs-head">
                            <span class="crs-head-icon" style="background: {{ $tones[$def['tone']] }}"><i class="bi {{ $def['icon'] }}"></i></span>
                            <div class="min-w-0 flex-1">
                                <h2 class="crs-title">{{ $def['label'] }}</h2>
                                <p class="crs-sub !whitespace-normal">{{ $def['help'] }}</p>
                            </div>
                            <button type="button" wire:click="toggle('{{ $key }}')" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" class="crm-switch {{ $on ? 'is-on' : '' }}" title="{{ $on ? 'Desligar' : 'Ligar' }}">
                                <span></span>
                            </button>
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col gap-3">
                            @if($def['days'] !== null)
                                <label class="crm-label">Quantos dias depois da venda
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="1" max="90" wire:model.blur="auto.{{ $key }}.days" class="crm-input !w-24">
                                        <span class="text-sm text-slate-500 dark:text-slate-400">dias</span>
                                    </div>
                                </label>
                            @endif
                            <label class="crm-label">Mensagem
                                <textarea wire:model.blur="auto.{{ $key }}.msg" rows="3" class="crm-input"></textarea>
                            </label>
                            @error("auto.$key.msg") <p class="crm-err -mt-2">{{ $message }}</p> @enderror
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-[11px] font-semibold text-slate-400">Inserir:</span>
                                @foreach($vars as $v => $lbl)
                                    @if($v !== '{produto}' || $key === 'posvenda')
                                        @if($v !== '{dias}' || $key === 'sumido')
                                            <button type="button" wire:click="insertVar('{{ $key }}', '{{ $v }}')" class="crm-chip !py-1" title="{{ $lbl }}">{{ $v }}</button>
                                        @endif
                                    @endif
                                @endforeach
                                <button type="button" wire:click="resetMsg('{{ $key }}')" class="ml-auto text-[11px] font-semibold text-slate-400 hover:text-indigo-600">Voltar ao texto padrão</button>
                            </div>
                            <div class="crm-bubble">
                                <span class="crm-bubble-tag"><i class="bi bi-whatsapp"></i> Como a Maria vai receber</span>
                                <p>{{ $samples[$key] }}</p>
                            </div>
                            <button type="button" wire:click="showPreview('{{ $key }}')" class="self-start text-xs font-bold text-indigo-600 dark:text-indigo-300 inline-flex items-center gap-1">
                                <i class="bi {{ $preview === $key ? 'bi-chevron-up' : 'bi-people' }}"></i>
                                {{ $n === 0 ? 'Ninguém entra hoje' : ($n === 1 ? '1 cliente entra hoje' : "{$n} clientes entram hoje") }}
                            </button>
                            @if($preview === $key && $n)
                                <div class="crs-list rounded-xl border border-slate-100 dark:border-slate-800">
                                    @foreach(array_slice($today[$key], 0, 12) as $row)
                                        <a href="{{ route('clients.historico', $row['client']->id) }}" wire:navigate class="crs-row crs-row-link !py-2">
                                            <x-client-avatar :client="$row['client']" size="w-8 h-8 text-xs" rounded="rounded-full" />
                                            <span class="crs-row-title flex-1">{{ $row['client']->name }}</span>
                                            @if(! $row['client']->phone)<span class="text-[11px] text-rose-500 font-semibold">sem telefone</span>@endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="button" wire:click="save" class="crm-btn crm-btn-primary justify-center flex-1"><i class="bi bi-check2"></i>Salvar mensagens</button>
                    <button type="button" wire:click="runNow" wire:confirm="Criar agora as tarefas de hoje das automações ligadas?" class="crm-btn justify-center"><i class="bi bi-play-circle"></i>Criar as tarefas de hoje agora</button>
                </div>
            </div>

            <div class="crm-side order-2">
                <section class="crs-card">
                    <div class="crs-head">
                        <span class="crs-head-icon bg-gradient-to-br from-pink-500 to-rose-500"><i class="bi bi-cake2"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="crs-title">Próximos aniversários <span class="crs-count">{{ $birthdays->count() }}</span></h2>
                            <p class="crs-sub">Próximos 30 dias</p>
                        </div>
                    </div>
                    <div class="crs-list">
                        @forelse($birthdays as $c)
                            <a href="{{ route('clients.historico', $c->id) }}" wire:navigate class="crs-row crs-row-link">
                                <span class="crs-row-icon {{ $c->in_days === 0 ? 'bg-pink-500 text-white' : 'bg-pink-50 text-pink-600 dark:bg-pink-500/15 dark:text-pink-300' }} font-bold text-xs flex-col leading-none">
                                    <span>{{ $c->next_birthday->format('d') }}</span><span class="text-[9px] uppercase">{{ $c->next_birthday->locale('pt_BR')->isoFormat('MMM') }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="crs-row-title">{{ $c->name }}</p>
                                    <p class="crs-row-meta">{{ $c->in_days === 0 ? 'Hoje! 🎉' : ($c->in_days === 1 ? 'Amanhã' : 'Em ' . $c->in_days . ' dias') }}{{ $c->marketing_opt_out ? ' · não quer ofertas' : '' }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="crs-empty py-8">
                                <i class="bi bi-cake2"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum aniversário nos próximos 30 dias. Preencha a data de nascimento em Editar cliente.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="crs-card mt-4">
                    <div class="p-4 text-sm text-slate-600 dark:text-slate-300 flex gap-3">
                        <i class="bi bi-info-circle text-sky-500 text-lg shrink-0"></i>
                        <p>O app não manda sozinho pelo WhatsApp porque a Meta só deixa a loja começar uma conversa com modelos de mensagem aprovados por ela. Assim você revisa e envia em um toque, do seu próprio WhatsApp.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-client-rows-css />
    <x-crm-css />
    <style>
        .crm-how { display: flex; gap: .75rem; align-items: flex-start; padding: .8rem 1rem; border-radius: 1rem; background: rgb(255 251 235); border: 1px solid rgb(253 230 138); font-size: .85rem; color: rgb(71 85 105); }
        .dark .crm-how { background: rgb(69 26 3 / .3); border-color: rgb(146 64 14 / .5); color: rgb(203 213 225); }
        .crm-auto:not(.is-on) .crs-head-icon { filter: grayscale(.85); opacity: .7; }
        .crm-switch { position: relative; width: 3rem; height: 1.75rem; flex-shrink: 0; border-radius: 999px; background: rgb(203 213 225); transition: background .2s; }
        .crm-switch span { position: absolute; top: .2rem; left: .2rem; width: 1.35rem; height: 1.35rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgb(0 0 0 / .25); transition: transform .2s; }
        .crm-switch.is-on { background: rgb(16 185 129); }
        .crm-switch.is-on span { transform: translateX(1.25rem); }
        .dark .crm-switch:not(.is-on) { background: rgb(51 65 85); }
        .crm-bubble { position: relative; align-self: flex-start; max-width: 32rem; padding: .6rem .8rem .7rem; border-radius: .9rem .9rem .9rem .25rem; background: rgb(220 252 231); color: rgb(20 83 45); font-size: .85rem; white-space: pre-line; }
        .dark .crm-bubble { background: rgb(20 83 45 / .45); color: rgb(220 252 231); }
        .crm-bubble-tag { display: block; margin-bottom: .2rem; font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; opacity: .7; }
        @media (min-width: 1024px) { .client-automations-page .crm-grid { grid-template-columns: minmax(0, 7fr) minmax(300px, 4fr); } }
        @media (min-width: 2200px) { .client-automations-page .crm-main { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: start; } .client-automations-page .crm-main > div:last-child { grid-column: 1 / -1; } }
    </style>
</div>
