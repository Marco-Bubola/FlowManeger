<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Cobranças" subtitle="Lembre os clientes das parcelas vencidas ou vencendo pelo WhatsApp"
        icon="bi-bell" active="cobrancas" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <x-gestao-stat label="Vencidas" :value="'R$ ' . number_format($summary['overdue_total'], 2, ',', '.')" icon="bi-exclamation-octagon" tone="rose"
            :hint="$summary['overdue_count'] . ' ' . ($summary['overdue_count'] === 1 ? 'parcela' : 'parcelas')" />
        <x-gestao-stat :label="$days === 0 ? 'Vencem hoje' : 'Vencem em ' . $days . ' ' . ($days === 1 ? 'dia' : 'dias')" :value="'R$ ' . number_format($summary['soon_total'], 2, ',', '.')" icon="bi-calendar-week" tone="amber"
            :hint="$summary['soon_count'] . ' ' . ($summary['soon_count'] === 1 ? 'parcela' : 'parcelas')" />
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 sm:p-4 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach (['vencidas' => 'Vencidas', 'vencendo' => 'Vencendo', 'todas' => 'Todas'] as $key => $label)
                <button type="button" wire:click="$set('filter', '{{ $key }}')"
                    class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ $filter === $key ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
            <label class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-sm text-slate-600 dark:text-slate-300">
                <span class="whitespace-nowrap">Próximos</span>
                <input type="number" min="0" max="60" wire:model.live.debounce.400ms="days"
                    class="w-14 rounded-lg border-0 bg-white dark:bg-slate-900 px-2 py-1 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                <span>dias</span>
            </label>
            <span class="mx-1 hidden sm:inline h-6 w-px bg-slate-200 dark:bg-slate-700"></span>
            @foreach (['todos' => ['Tudo', 'bi-grid'], 'vendas' => ['Vendas', 'bi-receipt'], 'consorcios' => ['Consórcios', 'bi-people']] as $key => [$label, $icon])
                <button type="button" wire:click="$set('channel', '{{ $key }}')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $channel === $key ? 'bg-purple-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <i class="bi {{ $icon }}"></i>{{ $label }}
                </button>
            @endforeach
        </div>
        <div class="relative">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar cliente…"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
        </div>
    </div>

    <div class="relative space-y-2">
        <div wire:loading.flex wire:target="filter,days,channel,search" class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php
                $client = $row['client'];
                $d = $row['days'];
                [$chipText, $chipClass, $chipIcon] = match (true) {
                    $d < 0 => ['Vencida há ' . abs($d) . ' ' . (abs($d) === 1 ? 'dia' : 'dias'), 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300', 'bi-exclamation-circle'],
                    $d === 0 => ['Vence hoje', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300', 'bi-alarm'],
                    default => ['Vence em ' . $d . ' ' . ($d === 1 ? 'dia' : 'dias'), 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300', 'bi-calendar3'],
                };
            @endphp
            <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 rounded-2xl border bg-white dark:bg-slate-900/80 px-4 py-3 shadow-sm hover:shadow-md transition {{ $d < 0 ? 'border-rose-200 dark:border-rose-500/30' : 'border-slate-200/80 dark:border-slate-700/70' }}" wire:key="col-{{ $row['key'] }}">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-full" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $client->name ?? 'Cliente não informado' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                            @if ($client?->phone)
                                <i class="bi bi-telephone"></i> {{ $client->phone }}
                            @else
                                <span class="text-rose-500"><i class="bi bi-telephone-x"></i> Sem telefone</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="min-w-0 w-full sm:w-auto lg:w-56">
                    <p class="flex items-center gap-1.5 truncate text-sm font-medium text-slate-700 dark:text-slate-200">
                        <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $row['channel'] === 'consorcios' ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' }}">
                            {{ $row['channel'] === 'consorcios' ? 'Consórcio' : 'Venda' }}
                        </span>
                        @if ($row['url'])
                            <a href="{{ $row['url'] }}" class="truncate hover:text-indigo-600 dark:hover:text-indigo-300 hover:underline">{{ $row['what'] }}</a>
                        @else
                            <span class="truncate">{{ $row['what'] }}</span>
                        @endif
                    </p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $row['installment'] }} · vence {{ $row['due']->format('d/m/Y') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap {{ $chipClass }}">
                        <i class="bi {{ $chipIcon }}"></i>{{ $chipText }}
                    </span>
                    @if ($row['last_reminder'])
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 whitespace-nowrap" title="Último lembrete: {{ $row['last_reminder']->format('d/m/Y H:i') }}">
                            <i class="bi bi-check2-all"></i>Lembrado em {{ $row['last_reminder']->format('d/m') }}
                        </span>
                    @endif
                    <span class="ml-auto sm:ml-0 sm:w-28 text-right text-base font-black text-slate-900 dark:text-white whitespace-nowrap">R$ {{ number_format($row['value'], 2, ',', '.') }}</span>
                </div>

                <div class="flex w-full sm:w-auto lg:w-[170px] lg:justify-end">
                    @if ($row['wa_url'])
                        <a href="{{ $row['wa_url'] }}" target="_blank" rel="noopener" wire:click="remind('{{ $row['key'] }}')"
                            title="{{ $row['message'] }}"
                            class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-500/20 transition">
                            <i class="bi bi-whatsapp"></i>Enviar WhatsApp
                        </a>
                    @else
                        <span class="inline-flex flex-1 sm:flex-none" title="{{ $client?->phone ? 'Telefone inválido: ' . $client->phone : 'Cliente sem telefone cadastrado' }}">
                            <button type="button" disabled aria-disabled="true"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                <i class="bi bi-whatsapp"></i>Enviar WhatsApp
                            </button>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-check2-circle text-3xl text-emerald-500"></i>
                <p class="mt-2">Nenhuma parcela para cobrar nesse filtro.</p>
            </div>
        @endforelse
    </div>

    <p class="text-xs text-slate-500 dark:text-slate-400">
        <i class="bi bi-info-circle"></i> O botão abre o WhatsApp com a mensagem pronta; é só apertar enviar. Os textos podem ser editados em
        <a href="{{ route('promotions.index') }}" class="font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">Promoções › Configurações › Cobrança</a>.
    </p>
</div>
