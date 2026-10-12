<div class="rcv-page w-full px-3 sm:px-6 lg:px-8 pt-4 pb-10 space-y-4 sm:space-y-5" x-data="{ more: @js($period !== '' || $minValue !== '' || $filter === 'sem') }">
    <x-gestao-header title="Contas a receber" subtitle="Quanto cada cliente ainda deve, por cliente ou por compra"
        icon="bi-cash-coin" active="receber" />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
        <x-gestao-stat label="Total a receber" :value="'R$ ' . number_format($totals['all'], 2, ',', '.')" icon="bi-wallet2" tone="indigo"
            :hint="$totals['sales'] . ' ' . ($totals['sales'] === 1 ? 'compra em aberto' : 'compras em aberto')" />
        <x-gestao-stat label="Vencido" :value="'R$ ' . number_format($totals['overdue'], 2, ',', '.')" icon="bi-exclamation-octagon" tone="rose"
            :hint="$totals['overdueClients'] . ' ' . ($totals['overdueClients'] === 1 ? 'cliente atrasado' : 'clientes atrasados')" />
        <x-gestao-stat label="Vence em 7 dias" :value="'R$ ' . number_format($totals['week'], 2, ',', '.')" icon="bi-calendar-week" tone="amber" />
        <x-gestao-stat label="Clientes devendo" :value="(string) $totals['clients']" icon="bi-people" tone="sky" />
    </div>

    <x-list-toolbar model="search" placeholder="Buscar cliente, telefone ou nº da compra..." class="!mb-0">
        <x-toolbar.group icon="bi-layout-three-columns" title="Ver por">
            <x-toolbar.chip wire:click="$set('view', 'clientes')" :active="$view === 'clientes'" icon="bi-people">Por cliente</x-toolbar.chip>
            <x-toolbar.chip wire:click="$set('view', 'compras')" :active="$view === 'compras'" icon="bi-bag">Por compra</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group icon="bi-calendar-event" title="Vencimento">
            @foreach (['todas' => 'Todas', 'vencidas' => 'Vencidas', 'semana' => '7 dias', 'mes' => 'Este mês'] as $key => $label)
                <x-toolbar.chip wire:click="$set('filter', '{{ $key }}')" :active="$filter === $key">{{ $label }}</x-toolbar.chip>
            @endforeach
        </x-toolbar.group>
        <x-toolbar.group icon="bi-arrow-down-up" title="Ordenar">
            @foreach (['divida' => 'Maior valor', 'atraso' => 'Mais atrasado', 'nome' => 'A-Z', 'recente' => 'Recentes'] as $key => $label)
                <x-toolbar.chip wire:click="$set('sort', '{{ $key }}')" :active="$sort === $key">{{ $label }}</x-toolbar.chip>
            @endforeach
        </x-toolbar.group>
        <x-toolbar.group>
            <x-toolbar.chip x-on:click="more = !more" x-bind:style="more ? 'background:linear-gradient(90deg,#4f46e5,#9333ea);color:#fff' : ''" icon="bi-sliders">Filtros</x-toolbar.chip>
            <x-toolbar.chip wire:click="export" icon="bi-download" title="Baixar a lista em planilha (CSV)">Exportar</x-toolbar.chip>
        </x-toolbar.group>
    </x-list-toolbar>

    <div x-show="more" x-cloak x-transition class="rcv-more">
        <div class="rcv-more-block">
            <span class="rcv-more-label"><i class="bi bi-clock-history"></i>Compras feitas em</span>
            <div class="rcv-chips">
                @foreach (['' => 'Qualquer data', '30' => 'Últimos 30 dias', '90' => '3 meses', '180' => '6 meses', '365' => '1 ano'] as $key => $label)
                    <button type="button" wire:click="$set('period', '{{ $key }}')" class="rcv-chip {{ $period === (string) $key ? 'is-on' : '' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="rcv-more-block">
            <span class="rcv-more-label"><i class="bi bi-cash-stack"></i>Falta pelo menos</span>
            <div class="rcv-money-input">
                <span>R$</span>
                <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="minValue" placeholder="0,00">
            </div>
        </div>
        <div class="rcv-more-block">
            <span class="rcv-more-label"><i class="bi bi-calendar2-range"></i>Tipo</span>
            <div class="rcv-chips">
                <button type="button" wire:click="$set('filter', '{{ $filter === 'sem' ? 'todas' : 'sem' }}')" class="rcv-chip {{ $filter === 'sem' ? 'is-on' : '' }}">Só sem parcelas</button>
            </div>
        </div>
        @if ($filtersOn)
            <button type="button" wire:click="clearFilters" class="rcv-chip is-clear"><i class="bi bi-x-circle"></i> Limpar filtros</button>
        @endif
    </div>

    @if (session('success'))
        <div class="rcv-flash" x-data="{ s: true }" x-show="s" x-init="setTimeout(() => s = false, 6000)">
            <i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span>
            <button type="button" x-on:click="s = false"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="rcv-summary">
        <span>
            @if ($view === 'clientes')
                <strong>{{ $list->count() }}</strong> {{ $list->count() === 1 ? 'cliente' : 'clientes' }} ·
            @endif
            <strong>{{ $shownCount }}</strong> {{ $shownCount === 1 ? 'compra' : 'compras' }} · falta <strong>R$ {{ number_format($shownTotal, 2, ',', '.') }}</strong>
        </span>
        @if ($filtersOn)
            <button type="button" wire:click="clearFilters" class="rcv-link"><i class="bi bi-x-circle"></i> Limpar filtros</button>
        @endif
    </div>

    <div class="relative">
        <div wire:loading.flex wire:target="search,view,filter,sort,period,minValue,clearFilters" class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-start justify-center pt-16 z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>

        @if ($list->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-check2-circle text-4xl text-emerald-500"></i>
                <p class="mt-2 font-semibold text-slate-700 dark:text-slate-200">{{ $filtersOn ? 'Nada a receber com esses filtros.' : 'Ninguém está devendo. Tudo recebido!' }}</p>
            </div>
        @elseif ($view === 'compras')
            <div class="rcv-grid">
                @foreach ($list as $r)
                    @include('livewire.gestao.partials.receivable-sale', ['r' => $r, 'withClient' => true])
                @endforeach
            </div>
        @else
            <div class="rcv-grid is-clients">
                @foreach ($list as $g)
                    @php
                        $client = $g->client;
                        $pct = $g->total > 0 ? (int) min(100, round($g->paid / $g->total * 100)) : 0;
                        $waPhone = $client?->phone ? preg_replace('/\D/', '', $client->phone) : null;
                        if ($waPhone && ! (strlen($waPhone) >= 12 && str_starts_with($waPhone, '55'))) { $waPhone = '55' . ltrim($waPhone, '0'); }
                        $lines = $g->sales->map(fn ($r) => '• Compra' . ($r->date ? ' de ' . $r->date->format('d/m/Y') : ' #' . $r->sale->id) . ': falta R$ ' . number_format($r->remaining, 2, ',', '.') . ($r->overdue ? ' (venceu ' . $r->due->format('d/m') . ')' : ''))->implode("\n");
                        $waMsg = 'Olá' . ($client ? ' ' . explode(' ', trim($client->name))[0] : '') . "! Tudo bem? Passando para lembrar do valor em aberto:\n" . $lines
                            . "\nTotal: R$ " . number_format($g->remaining, 2, ',', '.') . '. Qualquer dúvida é só me chamar. Obrigado!';
                    @endphp
                    <div class="rcv-client {{ $g->overdueValue > 0 ? 'is-late' : '' }}" x-data="{ open: false }" wire:key="rc-{{ $g->key }}">
                        <div class="rcv-client-head" x-on:click="open = !open">
                            <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-12 h-12 text-base" rounded="rounded-full" />
                            <div class="min-w-0 flex-1">
                                <p class="rcv-name truncate">{{ $client->name ?? 'Cliente não informado' }}</p>
                                <div class="rcv-tags">
                                    <span class="rcv-tag"><i class="bi bi-bag"></i>{{ $g->sales->count() }} {{ $g->sales->count() === 1 ? 'compra' : 'compras' }}</span>
                                    @if ($g->overdueValue > 0)
                                        <span class="rcv-tag is-rose"><i class="bi bi-exclamation-circle"></i>{{ $g->late }}d em atraso</span>
                                    @elseif ($g->oldestDue)
                                        <span class="rcv-tag"><i class="bi bi-calendar3"></i>Vence {{ $g->oldestDue->format('d/m') }}</span>
                                    @elseif ($g->oldestDate)
                                        <span class="rcv-tag"><i class="bi bi-clock-history"></i>Desde {{ \Carbon\Carbon::createFromTimestamp($g->oldestDate)->format('d/m/Y') }}</span>
                                    @endif
                                    @if ($client?->phone)
                                        <span class="rcv-tag rcv-hide-xs"><i class="bi bi-telephone"></i>{{ $client->phone }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="rcv-client-money">
                                <span class="rcv-dim">Falta</span>
                                <strong>R$ {{ number_format($g->remaining, 2, ',', '.') }}</strong>
                                @if ($g->overdueValue > 0 && $g->overdueValue < $g->remaining)
                                    <span class="rcv-late-value">R$ {{ number_format($g->overdueValue, 2, ',', '.') }} vencido</span>
                                @endif
                            </div>
                        </div>

                        <div class="rcv-progress"><div class="rcv-progress-bar" style="width: {{ $pct }}%"></div></div>
                        <div class="rcv-progress-legend">
                            <span>Pago R$ {{ number_format($g->paid, 2, ',', '.') }}</span>
                            <span>Comprou R$ {{ number_format($g->total, 2, ',', '.') }}</span>
                        </div>

                        <div class="rcv-actions">
                            @if ($g->key)
                                <button type="button" class="rcv-btn is-primary" wire:click="openReceive('client', {{ $g->key }})">
                                    <i class="bi bi-cash-coin"></i><span>Receber</span>
                                </button>
                            @endif
                            @if ($waPhone)
                                <a class="rcv-btn is-wa" href="https://wa.me/{{ $waPhone }}?text={{ rawurlencode($waMsg) }}" target="_blank" rel="noopener" title="Cobrar tudo pelo WhatsApp">
                                    <i class="bi bi-whatsapp"></i><span>Cobrar</span>
                                </a>
                            @endif
                            @if ($g->key)
                                <a class="rcv-btn" href="{{ route('clients.dashboard', $g->key) }}" title="Abrir o cliente"><i class="bi bi-person"></i><span class="rcv-hide-xs">Cliente</span></a>
                            @endif
                            <button type="button" class="rcv-btn is-ghost rcv-toggle" x-on:click="open = !open" :aria-expanded="open">
                                <span x-text="open ? 'Esconder' : 'Ver compras'"></span>
                                <i class="bi bi-chevron-down transition-transform" :class="open && 'rotate-180'"></i>
                            </button>
                        </div>

                        <div x-show="open" x-collapse x-cloak class="rcv-client-sales">
                            @foreach ($g->sales as $r)
                                @include('livewire.gestao.partials.receivable-sale', ['r' => $r, 'withClient' => false])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Receber: qualquer valor, abatido da compra mais antiga para a mais nova --}}
    @if ($showReceive && $receiveTarget)
        <div class="rcv-modal-backdrop" wire:click.self="closeReceive" x-on:keydown.escape.window="$wire.closeReceive()">
            <div class="rcv-modal" role="dialog" aria-modal="true">
                <div class="rcv-modal-grab"></div>
                <button type="button" class="rcv-modal-x" wire:click="closeReceive" title="Fechar"><i class="bi bi-x-lg"></i></button>
                <div class="rcv-modal-head">
                    <span class="rcv-modal-icon"><i class="bi bi-cash-coin"></i></span>
                    <div class="min-w-0">
                        <h3>Receber de {{ \Illuminate\Support\Str::limit($receiveTarget->name, 28) }}</h3>
                        <p>Falta R$ {{ number_format($receiveTarget->owed, 2, ',', '.') }}
                            @if ($receiveTarget->saleId) na compra #{{ $receiveTarget->saleId }} @else em {{ $receiveTarget->count }} {{ $receiveTarget->count === 1 ? 'compra' : 'compras' }} @endif
                        </p>
                    </div>
                </div>

                <label class="rcv-field-label" for="rcv-amount">Quanto o cliente pagou?</label>
                <div class="rcv-amount {{ $errors->has('receiveAmount') ? 'has-error' : '' }}">
                    <span>R$</span>
                    <input id="rcv-amount" type="text" inputmode="decimal" wire:model="receiveAmount" placeholder="0,00" autofocus
                        x-on:keydown.enter.prevent="$wire.confirmReceive()">
                    <button type="button" wire:click="fillAll">Tudo</button>
                </div>
                @error('receiveAmount') <p class="rcv-error">{{ $message }}</p> @enderror
                <p class="rcv-help"><i class="bi bi-info-circle"></i>
                    Pode ser qualquer valor, mais ou menos que a parcela.
                    @if (! $receiveTarget->saleId && $receiveTarget->count > 1) Abatemos da compra mais antiga para a mais nova. @endif
                    As parcelas se ajustam sozinhas.
                </p>

                <span class="rcv-field-label">Forma de pagamento</span>
                <div class="rcv-methods">
                    @foreach (\App\Livewire\Gestao\Receivables::METHODS as $key => [$label, $icon])
                        <button type="button" wire:click="$set('receiveMethod', '{{ $key }}')" class="rcv-method {{ $receiveMethod === $key ? 'is-on' : '' }}">
                            <i class="bi {{ $icon }}"></i>{{ $label }}
                        </button>
                    @endforeach
                </div>

                <label class="rcv-field-label" for="rcv-date">Data do pagamento</label>
                <input id="rcv-date" type="date" wire:model="receiveDate" class="rcv-date">
                @error('receiveDate') <p class="rcv-error">{{ $message }}</p> @enderror

                <div class="rcv-modal-actions">
                    <button type="button" class="rcv-btn" wire:click="closeReceive">Cancelar</button>
                    <button type="button" class="rcv-btn is-primary" wire:click="confirmReceive" wire:loading.attr="disabled" wire:target="confirmReceive">
                        <span wire:loading.remove wire:target="confirmReceive"><i class="bi bi-check2-circle"></i> Confirmar recebimento</span>
                        <span wire:loading wire:target="confirmReceive"><i class="bi bi-arrow-repeat animate-spin"></i> Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($forgiveRow)
        <x-delete-dialog confirm="forgive" cancel="cancelForgive" title="Perdoar o saldo?" confirm-label="Perdoar" icon="bi-eraser" loading-label="Salvando...">
            A compra #{{ $forgiveRow->sale->id }} de {{ $forgiveRow->client->name ?? 'cliente não informado' }} fica quitada.
            Os R$ {{ number_format($forgiveRow->remaining, 2, ',', '.') }} que faltam entram como desconto.
        </x-delete-dialog>
    @endif

    <style>
        .rcv-grid { display: grid; gap: 12px; grid-template-columns: 1fr; }
        @media (min-width: 768px) { .rcv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1536px) { .rcv-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 2200px) { .rcv-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .rcv-grid.is-clients { align-items: start; }

        .rcv-client, .rcv-sale.is-card { background: #fff; border: 1px solid rgb(226 232 240 / .9); border-radius: 20px; padding: 14px; box-shadow: 0 1px 2px rgb(15 23 42 / .04); transition: box-shadow .2s, border-color .2s; min-width: 0; }
        .rcv-client:hover, .rcv-sale.is-card:hover { box-shadow: 0 8px 24px -12px rgb(79 70 229 / .35); }
        .rcv-client.is-late, .rcv-sale.is-card.is-late { border-color: rgb(254 205 211); box-shadow: inset 4px 0 0 rgb(244 63 94); }
        .dark .rcv-client, .dark .rcv-sale.is-card { background: rgb(15 23 42 / .8); border-color: rgb(51 65 85 / .7); }
        .dark .rcv-client.is-late, .dark .rcv-sale.is-card.is-late { border-color: rgb(244 63 94 / .35); }

        .rcv-client-head, .rcv-sale-top { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .rcv-client-head { cursor: pointer; }
        .rcv-name { font-weight: 700; color: rgb(15 23 42); font-size: .98rem; line-height: 1.25; }
        .dark .rcv-name { color: #fff; }
        .rcv-dim { color: rgb(100 116 139); font-weight: 500; font-size: .72rem; }
        .dark .rcv-dim { color: rgb(148 163 184); }
        .rcv-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
        .rcv-tag { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; padding: 2px 8px; font-size: .68rem; font-weight: 600; background: rgb(241 245 249); color: rgb(71 85 105); white-space: nowrap; }
        .rcv-tag.is-rose { background: rgb(255 241 242); color: rgb(190 18 60); }
        .rcv-tag.is-amber { background: rgb(255 251 235); color: rgb(180 83 9); }
        .dark .rcv-tag { background: rgb(30 41 59); color: rgb(203 213 225); }
        .dark .rcv-tag.is-rose { background: rgb(244 63 94 / .12); color: rgb(253 164 175); }
        .dark .rcv-tag.is-amber { background: rgb(245 158 11 / .12); color: rgb(252 211 77); }

        .rcv-client-money, .rcv-sale-money { display: flex; flex-direction: column; align-items: flex-end; text-align: right; flex-shrink: 0; }
        .rcv-client-money strong { font-size: 1.2rem; font-weight: 900; color: rgb(15 23 42); line-height: 1.15; white-space: nowrap; }
        .rcv-sale-money strong { font-size: 1.02rem; font-weight: 900; color: rgb(15 23 42); white-space: nowrap; }
        .dark .rcv-client-money strong, .dark .rcv-sale-money strong { color: #fff; }
        .rcv-client.is-late .rcv-client-money strong { color: rgb(225 29 72); }
        .dark .rcv-client.is-late .rcv-client-money strong { color: rgb(251 113 133); }
        .rcv-late-value { font-size: .66rem; font-weight: 700; color: rgb(225 29 72); white-space: nowrap; }

        .rcv-progress { margin-top: 12px; height: 7px; border-radius: 999px; background: rgb(241 245 249); overflow: hidden; }
        .dark .rcv-progress { background: rgb(30 41 59); }
        .rcv-progress-bar { height: 100%; border-radius: 999px; background: linear-gradient(90deg, rgb(16 185 129), rgb(20 184 166)); min-width: 4px; }
        .rcv-progress-legend { display: flex; justify-content: space-between; gap: 8px; margin-top: 4px; font-size: .68rem; font-weight: 600; color: rgb(100 116 139); }

        .rcv-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .rcv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 12px; padding: 8px 12px; font-size: .78rem; font-weight: 700; border: 1px solid rgb(226 232 240); background: #fff; color: rgb(51 65 85); transition: all .15s; white-space: nowrap; min-height: 38px; }
        .rcv-btn:hover { background: rgb(248 250 252); border-color: rgb(199 210 254); color: rgb(79 70 229); }
        .dark .rcv-btn { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(226 232 240); }
        .rcv-btn.is-primary { border: 0; color: #fff; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); box-shadow: 0 4px 12px -4px rgb(79 70 229 / .5); }
        .rcv-btn.is-primary:hover { filter: brightness(1.08); color: #fff; }
        .rcv-btn.is-green { border-color: rgb(167 243 208); color: rgb(4 120 87); background: rgb(236 253 245); }
        .dark .rcv-btn.is-green { background: rgb(16 185 129 / .12); border-color: rgb(16 185 129 / .35); color: rgb(110 231 183); }
        .rcv-btn.is-wa { border: 0; color: #fff; background: rgb(22 163 74); box-shadow: 0 4px 12px -4px rgb(22 163 74 / .5); }
        .rcv-btn.is-wa:hover { background: rgb(21 128 61); color: #fff; }
        .rcv-btn.is-ghost { background: transparent; }
        .rcv-toggle { margin-left: auto; }
        .rcv-actions > .rcv-btn.is-primary, .rcv-actions > .rcv-btn.is-wa { flex: 1 1 auto; }
        @media (min-width: 640px) { .rcv-actions > .rcv-btn.is-primary, .rcv-actions > .rcv-btn.is-wa { flex: 0 0 auto; } }

        .rcv-client-sales { margin-top: 12px; border-top: 1px dashed rgb(226 232 240); padding-top: 4px; }
        .dark .rcv-client-sales { border-color: rgb(51 65 85); }
        .rcv-client-sales .rcv-sale { padding: 12px 0; border-bottom: 1px solid rgb(241 245 249); }
        .rcv-client-sales .rcv-sale:last-child { border-bottom: 0; padding-bottom: 2px; }
        .dark .rcv-client-sales .rcv-sale { border-color: rgb(30 41 59); }
        .rcv-client-sales .rcv-actions { margin-top: 8px; }
        .rcv-client-sales .rcv-btn { min-height: 34px; padding: 6px 10px; }
        .rcv-sale-icon { width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgb(238 242 255); color: rgb(79 70 229); }
        .dark .rcv-sale-icon { background: rgb(79 70 229 / .15); color: rgb(165 180 252); }
        .rcv-sale-title { font-weight: 700; font-size: .85rem; color: rgb(30 41 59); }
        .dark .rcv-sale-title { color: rgb(226 232 240); }
        .rcv-sale.is-card .rcv-sale-title { font-size: .76rem; }

        .rcv-more { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 20px; border-radius: 18px; border: 1px solid rgb(226 232 240 / .9); background: #fff; padding: 12px 14px; }
        .dark .rcv-more { background: rgb(15 23 42 / .8); border-color: rgb(51 65 85 / .7); }
        .rcv-more-block { display: flex; flex-direction: column; gap: 6px; min-width: 0; max-width: 100%; }
        .rcv-more-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); display: inline-flex; gap: 6px; }
        .rcv-chips { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 2px; }
        .rcv-chip { border-radius: 10px; padding: 6px 10px; font-size: .76rem; font-weight: 600; background: rgb(241 245 249); color: rgb(71 85 105); white-space: nowrap; }
        .rcv-chip.is-on { background: rgb(79 70 229); color: #fff; }
        .rcv-chip.is-clear { background: rgb(255 241 242); color: rgb(190 18 60); align-self: flex-end; }
        .dark .rcv-chip { background: rgb(30 41 59); color: rgb(203 213 225); }
        .dark .rcv-chip.is-on { background: rgb(79 70 229); color: #fff; }
        .rcv-money-input { display: flex; align-items: center; gap: 6px; border-radius: 10px; background: rgb(241 245 249); padding: 0 10px; width: 150px; }
        .rcv-money-input span { font-size: .78rem; font-weight: 700; color: rgb(100 116 139); }
        .rcv-money-input input { border: 0; background: transparent; padding: 6px 0; width: 100%; font-size: .85rem; font-weight: 700; color: rgb(15 23 42); }
        .rcv-money-input input:focus { outline: none; box-shadow: none; }
        .dark .rcv-money-input { background: rgb(30 41 59); }
        .dark .rcv-money-input input { color: #fff; }

        .rcv-summary { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px; font-size: .8rem; color: rgb(100 116 139); padding: 0 2px; }
        .rcv-summary strong { color: rgb(30 41 59); }
        .dark .rcv-summary strong { color: rgb(226 232 240); }
        .rcv-link { color: rgb(225 29 72); font-weight: 600; display: inline-flex; gap: 4px; align-items: center; }

        .rcv-flash { display: flex; align-items: center; gap: 10px; border-radius: 16px; padding: 10px 14px; background: rgb(236 253 245); color: rgb(4 120 87); font-size: .85rem; font-weight: 600; border: 1px solid rgb(167 243 208); }
        .rcv-flash span { flex: 1; }
        .dark .rcv-flash { background: rgb(16 185 129 / .12); color: rgb(110 231 183); border-color: rgb(16 185 129 / .3); }

        body:has(.rcv-modal-backdrop) .mobile-bottom-tabbar { display: none !important; }
        .rcv-modal-backdrop { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: flex-end; justify-content: center; background: rgb(15 23 42 / .45); backdrop-filter: blur(4px); }
        .rcv-modal { position: relative; width: 100%; max-width: 460px; max-height: 92dvh; overflow-y: auto; background: #fff; border-radius: 24px 24px 0 0; padding: 10px 18px calc(18px + env(safe-area-inset-bottom)); box-shadow: 0 -10px 40px rgb(15 23 42 / .25); animation: rcvUp .22s ease-out; }
        @media (min-width: 640px) { .rcv-modal-backdrop { align-items: center; padding: 16px; } .rcv-modal { border-radius: 24px; padding: 22px; animation: none; } .rcv-modal-grab { display: none; } }
        .dark .rcv-modal { background: rgb(15 23 42); color: rgb(226 232 240); }
        @keyframes rcvUp { from { transform: translateY(40px); opacity: .6; } to { transform: none; opacity: 1; } }
        .rcv-modal-grab { width: 40px; height: 4px; border-radius: 999px; background: rgb(203 213 225); margin: 0 auto 10px; }
        .rcv-modal-x { position: absolute; top: 12px; right: 12px; width: 34px; height: 34px; border-radius: 10px; color: rgb(100 116 139); }
        .rcv-modal-x:hover { background: rgb(241 245 249); }
        .rcv-modal-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-right: 36px; }
        .rcv-modal-head h3 { font-size: 1.05rem; font-weight: 800; color: rgb(15 23 42); }
        .dark .rcv-modal-head h3 { color: #fff; }
        .rcv-modal-head p { font-size: .8rem; color: rgb(100 116 139); }
        .rcv-modal-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; font-size: 1.2rem; background: linear-gradient(135deg, rgb(16 185 129), rgb(13 148 136)); }
        .rcv-field-label { display: block; margin: 12px 0 6px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); }
        .rcv-amount { display: flex; align-items: center; gap: 8px; border: 2px solid rgb(226 232 240); border-radius: 16px; padding: 4px 6px 4px 14px; }
        .rcv-amount:focus-within { border-color: rgb(99 102 241); }
        .rcv-amount.has-error { border-color: rgb(244 63 94); }
        .dark .rcv-amount { border-color: rgb(51 65 85); }
        .rcv-amount span { font-weight: 800; color: rgb(100 116 139); }
        .rcv-amount input { flex: 1; min-width: 0; border: 0; background: transparent; font-size: 1.5rem; font-weight: 900; padding: 6px 0; color: rgb(15 23 42); }
        .dark .rcv-amount input { color: #fff; }
        .rcv-amount input:focus { outline: none; box-shadow: none; }
        .rcv-amount button { border-radius: 12px; padding: 8px 12px; font-size: .78rem; font-weight: 700; background: rgb(238 242 255); color: rgb(79 70 229); }
        .rcv-error { margin-top: 6px; font-size: .78rem; font-weight: 600; color: rgb(225 29 72); }
        .rcv-help { margin-top: 8px; font-size: .74rem; color: rgb(100 116 139); display: flex; gap: 6px; align-items: flex-start; }
        .rcv-methods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; }
        .rcv-method { display: flex; flex-direction: column; align-items: center; gap: 2px; border-radius: 14px; padding: 9px 4px; font-size: .74rem; font-weight: 700; border: 2px solid rgb(226 232 240); color: rgb(71 85 105); }
        .rcv-method i { font-size: 1.05rem; }
        .rcv-method.is-on { border-color: rgb(99 102 241); background: rgb(238 242 255); color: rgb(67 56 202); }
        .dark .rcv-method { border-color: rgb(51 65 85); color: rgb(203 213 225); }
        .dark .rcv-method.is-on { background: rgb(79 70 229 / .2); color: rgb(199 210 254); }
        .rcv-date { width: 100%; border-radius: 14px; border: 2px solid rgb(226 232 240); padding: 9px 12px; font-weight: 600; background: transparent; }
        .dark .rcv-date { border-color: rgb(51 65 85); color: #fff; color-scheme: dark; }
        .rcv-modal-actions { display: flex; gap: 8px; margin-top: 18px; }
        .rcv-modal-actions .rcv-btn { flex: 1; min-height: 46px; font-size: .86rem; }
        .rcv-modal-actions .rcv-btn.is-primary { flex: 2; }

        .rcv-sale.is-card .rcv-open-label, .rcv-sale.is-card .rcv-wa-label { display: none; }
        .rcv-sale.is-card .rcv-actions > .rcv-btn.is-wa { flex: 0 0 auto; }
        /* notebook: busca em cima e botões embaixo, para a busca não ficar espremida */
        @media (min-width: 1280px) and (max-width: 1799px) { .rcv-page .app-toolbar { flex-direction: column; align-items: stretch; } }
        @media (min-width: 1280px) { .rcv-sale.is-card .rcv-open-label, .rcv-sale.is-card .rcv-wa-label { display: inline; } }
        @media (max-width: 420px) {
            .rcv-hide-xs { display: none; }
            .rcv-client, .rcv-sale.is-card { padding: 12px; border-radius: 18px; }
            .rcv-client-money strong { font-size: 1.05rem; }
        }
    </style>
</div>
