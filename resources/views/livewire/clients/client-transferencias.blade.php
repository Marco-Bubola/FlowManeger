<div class="client-transferencias-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-client-page-header :client="$client" title="Transferências" icon="bi-arrow-left-right" active="transferencias" />

        @php
            $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
            $saldo = $totalRecebido - $totalEnviado;
        @endphp

        {{-- Os três números também são o filtro: toque para ver só recebidas ou só enviadas --}}
        <div class="cmx mb-4">
            <button type="button" wire:click="setTipo('all')" class="cmx-item {{ $tipo === 'all' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-calculator text-sky-500"></i>Saldo</span>
                <span class="cmx-value {{ $saldo >= 0 ? '' : 'text-rose-600 dark:text-rose-400' }}" title="{{ $money($saldo) }}">{{ $money($saldo) }}</span>
                <span class="cmx-hint">Todas</span>
            </button>
            <button type="button" wire:click="setTipo('recebidas')" class="cmx-item {{ $tipo === 'recebidas' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-arrow-down-left text-emerald-500"></i>Recebido</span>
                <span class="cmx-value text-emerald-600 dark:text-emerald-400" title="{{ $money($totalRecebido) }}">{{ $money($totalRecebido) }}</span>
                <span class="cmx-hint">Ver só recebidas</span>
            </button>
            <button type="button" wire:click="setTipo('enviadas')" class="cmx-item {{ $tipo === 'enviadas' ? 'is-active' : '' }}">
                <span class="cmx-label"><i class="bi bi-arrow-up-right text-rose-500"></i>Enviado</span>
                <span class="cmx-value text-rose-600 dark:text-rose-400" title="{{ $money($totalEnviado) }}">{{ $money($totalEnviado) }}</span>
                <span class="cmx-hint">Ver só enviadas</span>
            </button>
        </div>

        <section class="crs-card">
            <div class="crs-head">
                <span class="crs-head-icon bg-gradient-to-br from-sky-500 to-indigo-600"><i class="bi bi-arrow-left-right"></i></span>
                <div class="min-w-0 flex-1">
                    <h2 class="crs-title">{{ ['all' => 'Transferências', 'recebidas' => 'Recebidas', 'enviadas' => 'Enviadas'][$tipo] ?? 'Transferências' }} <span class="crs-count">{{ $transferencias->total() }}</span></h2>
                    <p class="crs-sub">Lançamentos do livro caixa com este cliente</p>
                </div>
                <a href="{{ route('cashbook.index') }}" class="crs-link"><i class="bi bi-plus-lg"></i>Nova</a>
            </div>

            <div class="crs-list is-grid">
                @forelse($transferencias as $t)
                    @php $in = $t->type_id == 1; @endphp
                    <div class="crs-row" wire:key="tr-{{ $t->id }}">
                        <span class="crs-row-icon {{ $in ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300' }}">
                            <i class="bi {{ $in ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="crs-row-title" title="{{ $t->description }}">{{ $t->description ?: 'Sem descrição' }}</p>
                            <p class="crs-row-meta">
                                {{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}
                                @if($t->category)
                                    · <span class="inline-block w-2 h-2 rounded-full align-middle" style="background-color: {{ $t->category->hexcolor_category ?: '#94a3b8' }}"></span> {{ $t->category->name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="crs-row-value {{ $in ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $in ? '+' : '−' }} {{ $money($t->value) }}</span>
                            <span class="crs-badge {{ $in ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' }}">{{ $in ? 'Recebido' : 'Enviado' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="crs-empty py-12">
                        <i class="bi bi-arrow-left-right"></i>
                        <p class="font-semibold text-slate-600 dark:text-slate-300">
                            {{ ['all' => 'Nenhuma transferência com este cliente', 'recebidas' => 'Nenhuma transferência recebida', 'enviadas' => 'Nenhuma transferência enviada'][$tipo] ?? 'Nenhuma transferência' }}
                        </p>
                        <a href="{{ route('cashbook.index') }}" class="crs-link mt-1"><i class="bi bi-plus-lg"></i>Lançar no livro caixa</a>
                    </div>
                @endforelse
            </div>
        </section>

        @if($transferencias->hasPages())
            <div class="mt-4">
                <x-pagination-bar :paginator="$transferencias" label="transferências" />
            </div>
        @endif
    </div>

    <x-client-rows-css />
</div>
