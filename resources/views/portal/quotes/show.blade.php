<x-portal-layout title="Orçamento #{{ $quote->id }}">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-quotes-show.css') }}">
@endpush

@php
    $col = $quote->status_color;
    $icons = ['pending'=>'hourglass-half','reviewing'=>'magnifying-glass','quoted'=>'tag','approved'=>'circle-check','rejected'=>'circle-xmark'];
    $ico = $icons[$quote->status] ?? 'file-invoice';
    $canEdit = $quote->can_edit;
    $totalItems = collect($quote->items ?? [])->sum('quantity');
    $totalRef   = collect($quote->items ?? [])->sum(fn($i) => ($i['price_ref'] ?? 0) * ($i['quantity'] ?? 1));
@endphp

<div x-data="{ deleteModal: false }" class="max-w-5xl mx-auto space-y-4">

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon">
                <i class="fas fa-{{ $ico }}"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney text-[8px]"></i> Início</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <a href="{{ route('portal.quotes') }}">Meus Orçamentos</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>#{{ $quote->id }}</span>
                </div>
                <h1 class="pph-title">Orçamento <span class="text-indigo-600 dark:text-indigo-400">#{{ $quote->id }}</span></h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge {{ match($quote->status) { 'approved' => 'success', 'quoted' => 'warning', 'rejected' => '', default => 'info' } }}">
                    <i class="fas fa-{{ $ico }} text-[8px]"></i>
                    {{ $quote->status_label }}
                </span>
                <span class="pph-badge">
                    <i class="fas fa-box text-[8px]"></i>
                    {{ count($quote->items ?? []) }} produto{{ count($quote->items ?? []) !== 1 ? 's' : '' }}, {{ $totalItems }} unid.
                </span>
                @if($quote->quoted_total)
                <span class="pph-badge success">
                    <i class="fas fa-tag text-[8px]"></i>
                    R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}
                </span>
                @elseif($totalRef > 0)
                <span class="pph-badge">
                    <i class="fas fa-tag text-[8px]"></i>
                    Ref: R$ {{ number_format($totalRef, 2, ',', '.') }}
                </span>
                @endif
            </div>
            <a href="{{ route('portal.quotes') }}" class="pph-btn" style="background:rgba(255,255,255,0.7);color:#0f172a;border:1px solid rgba(0,0,0,0.1);box-shadow:none;">
                <i class="fas fa-arrow-left text-xs"></i>
                Voltar
            </a>
        </div>

        <div class="pph-row2">
            <span class="pph-pill" style="cursor:default;">
                <i class="fas fa-calendar-plus text-[8px]"></i>
                Enviado {{ $quote->created_at->format('d/m/Y \à\s H:i') }}
            </span>
            @if($quote->updated_at->ne($quote->created_at))
            <span class="pph-pill" style="cursor:default;">
                <i class="fas fa-clock-rotate-left text-[8px]"></i>
                Atualizado {{ $quote->updated_at->diffForHumans() }}
            </span>
            @endif
            @if($canEdit)
            <a href="{{ route('portal.quotes.edit', $quote) }}" class="pph-btn" style="margin-left:auto;">
                <i class="fas fa-pen-to-square text-xs"></i> Editar
            </a>
            <button type="button" @click="deleteModal = true"
                    class="pph-btn" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                <i class="fas fa-trash text-xs"></i> Excluir
            </button>
            @endif
        </div>
    </div>

    {{-- ─── Status Timeline ──────────────────────────────────────────────────── --}}
    <div class="portal-card p-4">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-timeline text-sky-500 dark:text-sky-400 text-xs"></i>
            <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Acompanhamento</h2>
        </div>
        @php
        $steps = [
            ['status' => 'pending',   'label' => 'Enviado',    'icon' => 'paper-plane'],
            ['status' => 'reviewing', 'label' => 'Em Análise', 'icon' => 'magnifying-glass'],
            ['status' => 'quoted',    'label' => 'Cotado',     'icon' => 'tag'],
            ['status' => 'approved',  'label' => 'Aprovado',   'icon' => 'circle-check'],
        ];
        $statusOrder = ['pending'=>0,'reviewing'=>1,'quoted'=>2,'approved'=>3,'rejected'=>2];
        $currentOrder = $statusOrder[$quote->status] ?? 0;
        @endphp
        <div class="flex items-start overflow-x-auto pb-1">
            @foreach($steps as $step)
            @php
                $stepOrder = $statusOrder[$step['status']] ?? 0;
                $isDone    = $currentOrder > $stepOrder && $quote->status !== 'rejected';
                $isCurrent = $quote->status === $step['status'] || ($quote->status === 'rejected' && $step['status'] === 'quoted');
            @endphp
            <div class="flex items-start flex-1 min-w-0">
                <div class="flex flex-col items-center flex-1 min-w-[4.5rem]">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                        {{ $isDone ? 'bg-emerald-500 text-white shadow shadow-emerald-400/30'
                            : ($isCurrent ? ($quote->status === 'rejected' ? 'bg-red-500 text-white ring-4 ring-red-100 dark:ring-red-900/40 shadow shadow-red-400/30' : 'bg-'.$col.'-500 text-white ring-4 ring-'.$col.'-100 dark:ring-'.$col.'-900/40 shadow')
                            : 'bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-500') }}">
                        @if($quote->status === 'rejected' && $isCurrent)
                            <i class="fas fa-circle-xmark text-[10px]"></i>
                        @else
                            <i class="fas fa-{{ $isDone ? 'check' : $step['icon'] }} text-[10px]"></i>
                        @endif
                    </div>
                    <p class="text-[9px] font-bold mt-1 text-center leading-tight
                        {{ $isDone ? 'text-emerald-600 dark:text-emerald-400'
                            : ($isCurrent ? ($quote->status === 'rejected' ? 'text-red-500 dark:text-red-400' : 'text-'.$col.'-600 dark:text-'.$col.'-400')
                            : 'text-gray-400 dark:text-slate-500') }}">
                        {{ $quote->status === 'rejected' && $isCurrent ? 'Recusado' : $step['label'] }}
                    </p>
                </div>
                @if(!$loop->last)
                <div class="flex-1 h-0.5 mt-4 mx-1 {{ $isDone ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-slate-600' }} rounded-full min-w-[1rem]"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ─── Quoted action banner ─────────────────────────────────────────────── --}}
    @if($quote->status === 'quoted')
    <div class="relative overflow-hidden bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-4 text-white shadow-lg">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tag text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-black text-sm">Proposta recebida — aguardando sua decisão!</p>
                <p class="text-white/70 text-xs mt-0.5 leading-relaxed">
                    Revise os itens e o valor abaixo. Após aprovação, o vendedor entrará em contato para finalizar o pedido.
                </p>
                @if($quote->valid_until)
                <p class="mt-1.5 inline-flex items-center gap-1.5 text-amber-200 text-xs font-bold">
                    <i class="fas fa-clock text-[10px]"></i>
                    Proposta válida até {{ $quote->valid_until->format('d/m/Y') }}
                    @if($quote->valid_until->isPast()) <span class="text-red-300">(expirada)</span> @endif
                </p>
                @endif
            </div>
            <div class="flex flex-row sm:flex-col gap-2 flex-shrink-0">
                <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-purple-700 hover:bg-white/90 font-black text-xs rounded-xl transition-all shadow hover:scale-105 whitespace-nowrap w-full justify-center">
                        <i class="fas fa-thumbs-up text-[10px]"></i> Aprovar
                    </button>
                </form>
                <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white font-black text-xs rounded-xl transition-all whitespace-nowrap w-full justify-center">
                        <i class="fas fa-thumbs-down text-[10px]"></i> Recusar
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="flex items-center gap-4 p-4 bg-{{ $col }}-50 dark:bg-{{ $col }}-900/20 border border-{{ $col }}-200 dark:border-{{ $col }}-700/50 rounded-2xl">
        <div class="w-10 h-10 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-{{ $ico }} text-{{ $col }}-600 dark:text-{{ $col }}-400 text-base"></i>
        </div>
        <div class="flex-1">
            <p class="text-{{ $col }}-700 dark:text-{{ $col }}-400 text-sm font-semibold">
                @if($quote->status === 'pending') Seu orçamento está aguardando análise pela nossa equipe.
                @elseif($quote->status === 'reviewing') Estamos analisando seu pedido. Em breve você terá uma resposta.
                @elseif($quote->status === 'approved') Orçamento aprovado! Em breve o vendedor entrará em contato para finalizar.
                @elseif($quote->status === 'rejected') Este orçamento foi recusado. Você pode criar um novo orçamento se desejar.
                @endif
            </p>
        </div>
        @if($quote->status === 'rejected')
        <a href="{{ route('portal.quotes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 hover:bg-{{ $col }}-200 dark:hover:bg-{{ $col }}-900/60 text-{{ $col }}-700 dark:text-{{ $col }}-400 text-xs font-bold rounded-xl transition-colors flex-shrink-0">
            <i class="fas fa-plus text-[10px]"></i> Novo
        </a>
        @endif
    </div>
    @endif

    {{-- ─── Main grid: products + details sidebar ───────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- Products list (2 cols) --}}
        @if($quote->items && count($quote->items))
        <div class="lg:col-span-2 portal-card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-sky-50/80 to-indigo-50/80 dark:from-sky-900/20 dark:to-indigo-900/20 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fas fa-boxes-stacked text-white text-xs"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Produtos Solicitados</h2>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ count($quote->items) }} tipo(s) · {{ $totalItems }} unidades</p>
                    </div>
                </div>
                @if($totalRef > 0 && !$quote->quoted_total)
                <div class="text-right">
                    <p class="text-[9px] text-gray-400 dark:text-slate-500">Valor ref.</p>
                    <p class="text-sm font-black text-sky-600 dark:text-sky-400">R$ {{ number_format($totalRef, 2, ',', '.') }}</p>
                </div>
                @endif
            </div>
            {{-- Compact product rows --}}
            <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                @foreach($quote->items as $idx => $item)
                @php
                    $product  = \App\Models\Product::with('category')->find($item['product_id'] ?? null);
                    $lineTotal = ($item['price_ref'] ?? 0) * ($item['quantity'] ?? 1);
                @endphp
                <div class="flex items-center gap-3 px-4 py-3 hover:bg-sky-50/40 dark:hover:bg-sky-900/10 transition-colors">
                    {{-- Number badge --}}
                    <span class="w-5 h-5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400 text-[9px] font-black flex items-center justify-center flex-shrink-0">{{ $idx + 1 }}</span>
                    {{-- Thumbnail --}}
                    <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 bg-sky-50 dark:bg-slate-700 border border-sky-100 dark:border-slate-600">
                        @if($product && $product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @elseif($product && $product->image)
                            <img src="{{ asset('storage/products/'.$product->image) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-box text-sky-300 dark:text-slate-500 text-sm"></i>
                            </div>
                        @endif
                    </div>
                    {{-- Name + category + notes --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-black text-gray-900 dark:text-slate-200 truncate">{{ $item['name'] }}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            @if($product?->category)
                            <span class="inline-flex items-center gap-1 text-[9px] text-sky-600 dark:text-sky-400 font-semibold">
                                <i class="{{ $product->category->icone ?? 'fas fa-tag' }} text-[8px]"></i>
                                {{ $product->category->name }}
                            </span>
                            @endif
                            @if(!empty($item['notes']))
                            <span class="text-[9px] text-gray-400 dark:text-slate-500 truncate">{{ $item['notes'] }}</span>
                            @endif
                        </div>
                    </div>
                    {{-- Quantities and price --}}
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <div class="text-center">
                            <p class="text-[9px] text-gray-400 dark:text-slate-500 mb-0.5">Qtd</p>
                            <span class="text-sm font-black text-sky-600 dark:text-sky-400 bg-sky-100 dark:bg-sky-900/40 px-2 py-0.5 rounded-lg leading-none">{{ $item['quantity'] }}×</span>
                        </div>
                        @if(!empty($item['price_ref']) && $item['price_ref'] > 0)
                        <div class="text-right hidden sm:block">
                            <p class="text-[9px] text-gray-400 dark:text-slate-500">Unit.</p>
                            <p class="text-xs font-bold text-gray-600 dark:text-slate-300">R$ {{ number_format($item['price_ref'], 2, ',', '.') }}</p>
                            <p class="text-[9px] font-black text-sky-600 dark:text-sky-300">= R$ {{ number_format($lineTotal, 2, ',', '.') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            {{-- Total footer --}}
            @if($quote->quoted_total)
            <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                    <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Valor Final Cotado pelo Vendedor</p>
                </div>
                <p class="text-lg font-black text-emerald-700 dark:text-emerald-300">R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}</p>
            </div>
            @elseif($totalRef > 0)
            <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700 flex items-center justify-between">
                <p class="text-xs text-gray-400 dark:text-slate-500">Total de Referência <span class="text-[9px]">(sujeito a negociação)</span></p>
                <p class="text-base font-black text-gray-700 dark:text-slate-300">R$ {{ number_format($totalRef, 2, ',', '.') }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Sidebar: Details + Notes --}}
        <div class="space-y-3">
            {{-- Details card --}}
            <div class="portal-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50/80 to-violet-50/80 dark:from-indigo-900/20 dark:to-violet-900/20 flex items-center gap-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-circle-info text-white text-[10px]"></i>
                    </div>
                    <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Detalhes do Orçamento</h2>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50 text-xs">
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Número</span>
                        <span class="font-black text-gray-900 dark:text-slate-200">#{{ $quote->id }}</span>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Status</span>
                        <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">{{ $quote->status_label }}</span>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Criado em</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $quote->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($quote->updated_at->ne($quote->created_at))
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Atualizado</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $quote->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Produtos</span>
                        <span class="font-black text-gray-900 dark:text-slate-200">{{ count($quote->items ?? []) }} tipo(s)</span>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Unidades</span>
                        <span class="font-black text-gray-900 dark:text-slate-200">{{ $totalItems }}</span>
                    </div>
                    @if($quote->valid_until)
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Válido até</span>
                        <span class="font-semibold {{ $quote->valid_until->isPast() ? 'text-red-500 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $quote->valid_until->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($quote->quoted_total)
                    <div class="px-4 py-3 flex items-center justify-between bg-emerald-50 dark:bg-emerald-900/20">
                        <span class="font-bold text-emerald-700 dark:text-emerald-300">Valor Cotado</span>
                        <span class="text-base font-black text-emerald-700 dark:text-emerald-300">R$ {{ number_format($quote->quoted_total, 2, ',', '.') }}</span>
                    </div>
                    @elseif($totalRef > 0)
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Ref. total</span>
                        <span class="font-black text-sky-600 dark:text-sky-400">R$ {{ number_format($totalRef, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Client notes --}}
            @if($quote->client_notes)
            <div class="portal-card p-4">
                <h3 class="text-xs font-black text-gray-700 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-note-sticky text-blue-400 text-[10px]"></i> Suas Observações
                </h3>
                <p class="text-xs text-gray-600 dark:text-slate-400 leading-relaxed">{{ $quote->client_notes }}</p>
            </div>
            @endif

            {{-- Admin notes --}}
            @if($quote->admin_notes)
            <div class="portal-card p-4 border-emerald-200 dark:border-emerald-700/50">
                <h3 class="text-xs font-black text-gray-700 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-comment-dots text-emerald-500 text-[10px]"></i> Resposta do Vendedor
                </h3>
                <p class="text-xs text-gray-600 dark:text-slate-400 leading-relaxed">{{ $quote->admin_notes }}</p>
            </div>
            @endif

            {{-- Extra items --}}
            @if($quote->extra_items && count($quote->extra_items))
            <div class="portal-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2">
                    <i class="fas fa-list-check text-violet-500 dark:text-violet-400 text-xs"></i>
                    <h2 class="font-black text-xs text-gray-900 dark:text-slate-200">Itens Adicionais</h2>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-slate-700/60">
                    @foreach($quote->extra_items as $ei)
                    <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                        <span class="text-gray-700 dark:text-slate-300">{{ $ei['description'] }}</span>
                        <span class="font-semibold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">{{ $ei['quantity'] }}×</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ─── Bottom action bar ────────────────────────────────────────────────── --}}
    <div class="portal-card p-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('portal.quotes') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors">
            <i class="fas fa-arrow-left text-[10px]"></i> Voltar
        </a>
        @if($canEdit)
        <a href="{{ route('portal.quotes.edit', $quote) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-50 dark:bg-sky-900/30 hover:bg-sky-100 dark:hover:bg-sky-900/50 text-sky-600 dark:text-sky-400 text-xs font-bold rounded-xl transition-colors border border-sky-200 dark:border-sky-700/50">
            <i class="fas fa-pen-to-square text-[10px]"></i> Editar
        </a>
        <button type="button" @click="deleteModal = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 text-xs font-bold rounded-xl transition-colors border border-red-200 dark:border-red-700/50">
            <i class="fas fa-trash text-[10px]"></i> Excluir
        </button>
        @endif
        @if($quote->status === 'quoted')
        <div class="flex items-center gap-2 ml-auto">
            <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black rounded-xl transition-all shadow-sm hover:shadow-emerald-500/30 hover:scale-105">
                    <i class="fas fa-thumbs-up text-[10px]"></i> Aprovar Orçamento
                </button>
            </form>
            <form method="POST" action="{{ route('portal.quotes.respond', $quote) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 text-xs font-black rounded-xl transition-colors border border-red-200 dark:border-red-700/50">
                    <i class="fas fa-thumbs-down text-[10px]"></i> Recusar
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- ─── Delete confirmation modal ───────────────────────────────────────── --}}
    @if($canEdit)
    <div x-show="deleteModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-sm w-full shadow-2xl border border-red-100 dark:border-red-900/50"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>
            <div class="text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-900/40 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-red-500 dark:text-red-400 text-xl"></i>
                </div>
                <h3 class="text-base font-black text-gray-900 dark:text-slate-200 mb-1">Excluir Orçamento #{{ $quote->id }}?</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-5 leading-relaxed">
                    Esta ação <strong>não pode ser desfeita</strong>. O orçamento e todos os seus itens serão removidos permanentemente.
                </p>
                <div class="flex gap-2 justify-center">
                    <button type="button" @click="deleteModal = false"
                            class="px-5 py-2.5 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <form method="POST" action="{{ route('portal.quotes.destroy', $quote) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-xs font-black rounded-xl transition-colors shadow hover:shadow-red-500/30">
                            <i class="fas fa-trash text-[10px] mr-1.5"></i> Sim, excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

</x-portal-layout>
