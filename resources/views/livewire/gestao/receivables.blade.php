<div class="w-full px-4 sm:px-6 py-4 space-y-4">
    <x-sales-header title="Contas a Receber"
        description="Parcelas e saldos que os clientes ainda devem, por data de vencimento"
        icon="bi-cash-coin" iconColor="green" :back-route="route('sales.index')" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 px-4 py-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Total a receber</p>
            <p class="text-xl font-black text-slate-800 dark:text-slate-100">R$ {{ number_format($totals['all'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 dark:border-red-900 bg-red-50/80 dark:bg-red-950/30 px-4 py-3">
            <p class="text-xs text-red-600 dark:text-red-400">Vencido</p>
            <p class="text-xl font-black text-red-700 dark:text-red-300">R$ {{ number_format($totals['overdue'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50/80 dark:bg-amber-950/30 px-4 py-3">
            <p class="text-xs text-amber-700 dark:text-amber-400">Vence em 7 dias</p>
            <p class="text-xl font-black text-amber-700 dark:text-amber-300">R$ {{ number_format($totals['week'], 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['todas' => 'Todas', 'vencidas' => 'Vencidas', 'semana' => 'Próximos 7 dias', 'mes' => 'Até o fim do mês'] as $key => $label)
            <button type="button" wire:click="$set('filter', '{{ $key }}')"
                class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $filter === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-zinc-900/60 overflow-hidden">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-indigo-500"></i>
        </div>
        @forelse ($rows as $row)
            @php
                $client = $row['sale']?->client;
                $overdue = $row['due'] && $row['due']->lt($today);
                $phone = $client?->phone ? preg_replace('/\D/', '', $client->phone) : null;
                $msg = 'Olá' . ($client ? ' ' . explode(' ', $client->name)[0] : '') . '! Passando para lembrar do pagamento de R$ ' . number_format($row['value'], 2, ',', '.') . ($row['due'] ? ' com vencimento em ' . $row['due']->format('d/m/Y') : '') . '. Obrigado!';
            @endphp
            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 px-4 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0" wire:key="rec-{{ $row['key'] }}">
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $client->name ?? 'Cliente não informado' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Venda #{{ $row['sale']?->id }} · {{ $row['label'] }}</p>
                </div>
                <div class="text-sm w-32">
                    @if ($row['due'])
                        <span class="{{ $overdue ? 'text-red-600 font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ $row['due']->format('d/m/Y') }}
                        </span>
                        @if ($overdue)
                            <span class="block text-xs text-red-500">{{ $row['due']->diffInDays($today) }} dias em atraso</span>
                        @endif
                    @else
                        <span class="text-slate-400">Sem vencimento</span>
                    @endif
                </div>
                <div class="text-sm font-black text-slate-800 dark:text-slate-100 w-28 text-right">R$ {{ number_format($row['value'], 2, ',', '.') }}</div>
                <div class="ml-auto flex items-center gap-2">
                    @if ($phone)
                        <a href="https://wa.me/55{{ ltrim($phone, '0') }}?text={{ rawurlencode($msg) }}" target="_blank" rel="noopener"
                            class="px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white" aria-label="Cobrar pelo WhatsApp">
                            <i class="bi bi-whatsapp"></i> Cobrar
                        </a>
                    @endif
                    <a href="{{ route('sales.show', $row['sale']?->id) }}" class="px-3 py-2 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Abrir venda
                    </a>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                <i class="bi bi-check2-circle text-3xl text-emerald-500"></i>
                <p class="mt-2">Nada a receber nesse filtro.</p>
            </div>
        @endforelse
    </div>
</div>
