<div class="cofrinhos-show-page w-full pb-8">
    @php
        $meta = (float) $cofrinho->meta_valor;
        $done = $meta > 0 && $valor_acumulado >= $meta;
        $pct = (float) $porcentagem;
    @endphp

    <x-cashbook-page-header :title="$cofrinho->nome" subtitle="Quanto já foi guardado e cada movimentação deste cofrinho"
        :icon="'fas ' . ($cofrinho->icone ?: 'fa-piggy-bank')" active="cofrinhos" :back-route="route('cofrinhos.index')">
        <x-slot:meta>
            @if($done)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-trophy-fill"></i>Meta alcançada</span>
            @elseif($cofrinho->status !== 'ativo')
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-archive"></i>Arquivado</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-hourglass-split"></i>Em andamento</span>
            @endif
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-calendar3"></i>Criado em {{ $cofrinho->created_at?->format('d/m/Y') }}</span>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('cofrinhos.edit', $cofrinho->id) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-pencil"></i>Editar</a>
            <a href="{{ route('cashbook.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition"><i class="bi bi-plus-lg"></i>Novo lançamento</a>
        </x-slot:actions>
    </x-cashbook-page-header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-gestao-stat label="Guardado" :value="'R$ ' . number_format($valor_acumulado, 2, ',', '.')" icon="bi-piggy-bank" tone="emerald" />
        <x-gestao-stat label="Meta" :value="'R$ ' . number_format($meta, 2, ',', '.')" icon="bi-flag" tone="indigo" />
        <x-gestao-stat label="Falta" :value="'R$ ' . number_format(max(0, $estatisticas['valor_restante']), 2, ',', '.')" icon="bi-hourglass-split" tone="amber" />
        <x-gestao-stat label="Movimentações no mês" :value="$estatisticas['transacoes_mes']" icon="bi-calendar-check" tone="sky" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Progresso da meta</p>
                <p class="mt-2 text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ number_format($pct, 1, ',', '.') }}%</p>
                <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full {{ $done ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}" style="width: {{ min($pct, 100) }}%"></div>
                </div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    R$ {{ number_format($valor_acumulado, 2, ',', '.') }} de R$ {{ number_format($meta, 2, ',', '.') }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"><i class="bi bi-arrow-down-circle text-emerald-500"></i>Guardado ({{ $estatisticas['qtd_receitas'] }})</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($estatisticas['total_receitas'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"><i class="bi bi-arrow-up-circle text-rose-500"></i>Retirado ({{ $estatisticas['qtd_despesas'] }})</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">R$ {{ number_format($estatisticas['total_despesas'], 2, ',', '.') }}</span>
                </div>
                <p class="border-t border-slate-100 dark:border-slate-800 pt-3 text-xs text-slate-500 dark:text-slate-400">
                    O Livro caixa é a sua conta corrente: uma <b>despesa</b> com este cofrinho é dinheiro que saiu da conta e entrou aqui. Uma <b>receita</b> com este cofrinho é dinheiro que voltou para a conta.
                </p>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md"><i class="bi bi-clock-history"></i></div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Movimentações</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ count($transacoes) }} {{ count($transacoes) === 1 ? 'lançamento' : 'lançamentos' }} ligados a este cofrinho</p>
                </div>
            </div>

            @if(count($transacoes) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($transacoes as $transacao)
                        @php $in = $transacao['type_id'] == \App\Models\Cofrinho::TIPO_GUARDAR; @endphp
                        <a href="{{ route('cashbook.edit', $transacao['id']) }}" wire:navigate
                           class="flex items-center gap-3 py-3 px-2 -mx-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $in ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300' }}">
                                <i class="bi {{ $in ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' }} text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $transacao['description'] ?: ($in ? 'Guardado' : 'Retirado') }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $in ? 'Guardado' : 'Retirado' }} · {{ \Carbon\Carbon::parse($transacao['date'] ?? $transacao['created_at'])->format('d/m/Y') }}</p>
                            </div>
                            <span class="shrink-0 font-bold {{ $in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $in ? '+' : '-' }} R$ {{ number_format($transacao['value'], 2, ',', '.') }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"><i class="bi bi-clock-history text-2xl text-slate-400"></i></div>
                    <p class="font-semibold text-slate-900 dark:text-white">Nenhuma movimentação ainda</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lance uma despesa no Livro caixa escolhendo este cofrinho para guardar dinheiro nele.</p>
                    <a href="{{ route('cashbook.create') }}" wire:navigate class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md"><i class="bi bi-plus-lg"></i>Novo lançamento</a>
                </div>
            @endif
        </div>
    </div>
</div>
