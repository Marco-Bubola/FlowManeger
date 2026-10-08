<div class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-8 space-y-5">
    <x-gestao-header title="Contas a receber" subtitle="Parcelas e saldos que os clientes ainda devem, por data de vencimento"
        icon="bi-cash-coin" active="receber" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-gestao-stat label="Total a receber" :value="'R$ ' . number_format($totals['all'], 2, ',', '.')" icon="bi-wallet2" tone="indigo" />
        <x-gestao-stat label="Vencido" :value="'R$ ' . number_format($totals['overdue'], 2, ',', '.')" icon="bi-exclamation-octagon" tone="rose" />
        <x-gestao-stat label="Vence em 7 dias" :value="'R$ ' . number_format($totals['week'], 2, ',', '.')" icon="bi-calendar-week" tone="amber" />
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-3 sm:p-4 shadow-sm flex flex-wrap gap-1.5">
        @foreach (['todas' => 'Todas', 'vencidas' => 'Vencidas', 'semana' => 'Próximos 7 dias', 'mes' => 'Até o fim do mês'] as $key => $label)
            <button type="button" wire:click="$set('filter', '{{ $key }}')"
                class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ $filter === $key ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="relative space-y-2">
        <div wire:loading.flex class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-slate-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php
                $client = $row['sale']?->client;
                $overdue = $row['due'] && $row['due']->lt($today);
                $soon = ! $overdue && $row['due'] && $row['due']->lte($today->copy()->addDays(7));
                $phone = $client?->phone ? preg_replace('/\D/', '', $client->phone) : null;
                $msg = 'Olá' . ($client ? ' ' . explode(' ', $client->name)[0] : '') . '! Passando para lembrar do pagamento de R$ ' . number_format($row['value'], 2, ',', '.') . ($row['due'] ? ' com vencimento em ' . $row['due']->format('d/m/Y') : '') . '. Obrigado!';
            @endphp
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 rounded-2xl border bg-white dark:bg-slate-900/80 px-4 py-3 shadow-sm hover:shadow-md transition {{ $overdue ? 'border-rose-200 dark:border-rose-500/30' : 'border-slate-200/80 dark:border-slate-700/70' }}" wire:key="rec-{{ $row['key'] }}">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <x-client-avatar :name="$client->name ?? '?'" :photo="$client->caminho_foto ?? null" size="w-11 h-11 text-sm" rounded="rounded-full" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $client->name ?? 'Cliente não informado' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">Venda #{{ $row['sale']?->id }} · {{ $row['label'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 sm:gap-4 w-full sm:w-auto">
                    @if ($row['due'])
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap
                            {{ $overdue ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : ($soon ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">
                            <i class="bi {{ $overdue ? 'bi-exclamation-circle' : 'bi-calendar3' }}"></i>
                            {{ $row['due']->format('d/m/Y') }}@if ($overdue) · {{ (int) $row['due']->diffInDays($today) }}d em atraso @endif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-500">Sem vencimento</span>
                    @endif
                    <span class="ml-auto sm:ml-0 sm:w-28 text-right text-base font-black text-slate-900 dark:text-white whitespace-nowrap">R$ {{ number_format($row['value'], 2, ',', '.') }}</span>
                </div>
                <div class="flex w-full sm:w-[210px] sm:justify-end items-center gap-2">
                    @if ($phone)
                        <a href="https://wa.me/55{{ ltrim($phone, '0') }}?text={{ rawurlencode($msg) }}" target="_blank" rel="noopener"
                            class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-500/20 transition" aria-label="Cobrar pelo WhatsApp">
                            <i class="bi bi-whatsapp"></i>Cobrar
                        </a>
                    @endif
                    <a href="{{ route('sales.show', $row['sale']?->id) }}"
                        class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                        <i class="bi bi-receipt"></i>Abrir venda
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-check2-circle text-3xl text-emerald-500"></i>
                <p class="mt-2">Nada a receber nesse filtro.</p>
            </div>
        @endforelse
    </div>
</div>
