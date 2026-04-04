<x-layouts.app title="Portal do Cliente - Orçamentos">
<div class="w-full px-4 sm:px-6 lg:px-8 py-6">
    <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 p-6 shadow-2xl mb-6">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,189,248,0.22),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(99,102,241,0.18),transparent_28%)]"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-sky-200">
                    Portal do Cliente
                </div>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-white">Orçamentos de {{ $client->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">
                    Área para acompanhar pedidos de orçamento do cliente, responder com valor e controlar aprovação.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('clients.resumo', $client->id) }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Voltar ao Cliente
                </a>
                @if($client->portal_active)
                <a href="{{ route('portal.login') }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-400">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    Abrir Portal
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($quotes->isEmpty())
        <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fas fa-file-invoice-dollar text-2xl"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-900">Nenhum orçamento solicitado</h2>
            <p class="mt-2 text-sm text-slate-500">Quando o cliente enviar pedidos pelo portal, eles aparecerão aqui.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach($quotes as $quote)
                @php
                    $statusClasses = [
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'reviewing' => 'bg-sky-50 text-sky-700 border-sky-200',
                        'quoted' => 'bg-violet-50 text-violet-700 border-violet-200',
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                    ];
                @endphp
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-lg font-bold text-slate-900">Orçamento #{{ $quote->id }}</h2>
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusClasses[$quote->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                    {{ $quote->status_label }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">Solicitado em {{ $quote->created_at->format('d/m/Y H:i') }}</p>
                            @if($quote->client_notes)
                                <p class="mt-3 max-w-3xl rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">{{ $quote->client_notes }}</p>
                            @endif
                        </div>
                        <div class="text-left lg:text-right">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Valor atual</p>
                            <p class="mt-1 text-2xl font-black text-slate-900">
                                {{ $quote->quoted_total ? 'R$ ' . number_format($quote->quoted_total, 2, ',', '.') : 'Ainda não enviado' }}
                            </p>
                            @if($quote->valid_until)
                                <p class="mt-1 text-xs text-slate-500">Válido até {{ $quote->valid_until->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-6 px-6 py-6 lg:grid-cols-[1.2fr,0.8fr]">
                        <div class="space-y-4">
                            @if($quote->items)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <h3 class="text-sm font-bold text-slate-900">Itens pedidos</h3>
                                    <div class="mt-3 space-y-3">
                                        @foreach($quote->items as $item)
                                            <div class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 border border-slate-100">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ $item['name'] }}</p>
                                                    @if(!empty($item['notes']))
                                                        <p class="text-xs text-slate-500 mt-1">{{ $item['notes'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-bold text-slate-900">{{ $item['quantity'] }}x</p>
                                                    @if(!empty($item['price_ref']))
                                                        <p class="text-xs text-slate-400">Ref. R$ {{ number_format($item['price_ref'], 2, ',', '.') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($quote->extra_items)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <h3 class="text-sm font-bold text-slate-900">Itens adicionais</h3>
                                    <div class="mt-3 space-y-2">
                                        @foreach($quote->extra_items as $item)
                                            <div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 border border-slate-100 text-sm">
                                                <span class="font-medium text-slate-700">{{ $item['description'] }}</span>
                                                <span class="text-slate-500">{{ $item['quantity'] }}x</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <form method="POST" action="{{ route('clients.portal.quotes.update', $quote->id) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Status</label>
                                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                        @foreach(\App\Models\ClientQuoteRequest::STATUS_LABELS as $status => $label)
                                            <option value="{{ $status }}" @selected($quote->status === $status)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Valor cotado</label>
                                    <input type="number" step="0.01" min="0" name="quoted_total" value="{{ old('quoted_total', $quote->quoted_total) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Validade</label>
                                    <input type="date" name="valid_until" value="{{ old('valid_until', $quote->valid_until?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Observações internas / resposta</label>
                                    <textarea name="admin_notes" rows="5" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('admin_notes', $quote->admin_notes) }}</textarea>
                                </div>

                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-indigo-700">
                                    <i class="fas fa-save text-xs"></i>
                                    Salvar resposta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>
