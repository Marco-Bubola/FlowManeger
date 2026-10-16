<div class="client-faturas-page w-full mobile-393-base">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-client-page-header :client="$client" title="Faturas" icon="bi-receipt" active="faturas" />

        @php $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); @endphp

        <div class="cmx mb-4">
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-receipt text-indigo-500"></i>Faturas</span>
                <span class="cmx-value">{{ $faturas->total() }}</span>
                <span class="cmx-hint">{{ $totalDivididas }} {{ $totalDivididas == 1 ? 'dividida' : 'divididas' }}</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-currency-dollar text-amber-500"></i>Original</span>
                <span class="cmx-value text-amber-600 dark:text-amber-400" title="{{ $money($totalOriginal) }}">{{ $money($totalOriginal) }}</span>
                <span class="cmx-hint">Soma das faturas</span>
            </div>
            <div class="cmx-item">
                <span class="cmx-label"><i class="bi bi-wallet2 text-emerald-500"></i>Do cliente</span>
                <span class="cmx-value text-emerald-600 dark:text-emerald-400" title="{{ $money($totalCliente) }}">{{ $money($totalCliente) }}</span>
                <span class="cmx-hint">Divididas contam a metade</span>
            </div>
        </div>

        <section class="crs-card">
            <div class="crs-head">
                <span class="crs-head-icon bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-receipt"></i></span>
                <div class="min-w-0 flex-1">
                    <h2 class="crs-title">Faturas <span class="crs-count">{{ $faturas->total() }}</span></h2>
                    <p class="crs-sub">Toque em Integral ou Dividida para dividir a fatura ao meio</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="crs-link"><i class="bi bi-plus-lg"></i>Nova</a>
            </div>

            <div class="crs-list is-grid">
                @forelse($faturas as $fatura)
                    <div class="crs-row" wire:key="fat-{{ $fatura->id }}">
                        <span class="crs-row-icon bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="{{ $fatura->category->icone ?? 'bi bi-receipt' }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="crs-row-title" title="{{ $fatura->description }}">{{ $fatura->description }}</p>
                            <p class="crs-row-meta">
                                {{ \Carbon\Carbon::parse($fatura->invoice_date)->format('d/m/Y') }}
                                @if($fatura->category) · {{ $fatura->category->name }} @endif
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="crs-row-value">
                                @if($fatura->dividida)
                                    <s class="mr-1 text-[11px] font-semibold text-slate-400">{{ $money($fatura->value) }}</s>
                                @endif
                                {{ $money($fatura->dividida ? $fatura->value / 2 : $fatura->value) }}
                            </span>
                            <button type="button" wire:click="toggleDividida({{ $fatura->id }})"
                                    class="crs-split {{ $fatura->dividida ? 'is-on' : '' }}" title="Dividir fatura ao meio">
                                <span class="crs-split-dot"></span>{{ $fatura->dividida ? 'Dividida' : 'Integral' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="crs-empty py-12">
                        <i class="bi bi-receipt"></i>
                        <p class="font-semibold text-slate-600 dark:text-slate-300">Nenhuma fatura deste cliente</p>
                        <a href="{{ route('invoices.index') }}" class="crs-link mt-1"><i class="bi bi-plus-lg"></i>Lançar fatura</a>
                    </div>
                @endforelse
            </div>
        </section>

        @if($faturas->hasPages())
            <div class="mt-4">
                <x-infinite-feed :paginator="$faturas" label="faturas" />
            </div>
        @endif
    </div>

    <x-client-rows-css />

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" x-init="setTimeout(() => show = false, 5000)" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i class="bi bi-check-circle-fill mr-2"></i>
                {{ session('success') }}
                <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    @endif
</div>
