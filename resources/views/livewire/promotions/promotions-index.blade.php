<div class="promotions-page w-full px-2 sm:px-4 py-4">
    @php
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $tabs = [
            'ativas'     => ['Ativas', 'bi-fire', $stats['ativas']],
            'sugestoes'  => ['Sugestões', 'bi-lightbulb', $stats['sugestoes']],
            'agendadas'  => ['Agendadas', 'bi-calendar-event', $stats['agendadas']],
            'encerradas' => ['Encerradas', 'bi-archive', null],
        ];
    @endphp

    {{-- Cabeçalho --}}
    <div class="relative overflow-hidden rounded-3xl border border-white/20 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-pink-50/90 to-rose-50/80 dark:from-slate-800/90 dark:via-slate-700/30 dark:to-slate-800/30 shadow-2xl mb-5">
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-pink-400/20 via-rose-400/20 to-orange-400/20 rounded-full translate-x-16 -translate-y-16"></div>
        <div class="relative px-5 sm:px-8 py-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 via-rose-500 to-orange-500 shadow-xl shadow-rose-500/25 shrink-0">
                    <i class="bi bi-percent text-white text-2xl"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                        <a href="{{ route('dashboard') }}" class="hover:text-rose-600"><i class="bi bi-house-door"></i> Dashboard</a>
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span>Promoções</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-800 via-rose-700 to-orange-600 dark:from-rose-300 dark:via-pink-300 dark:to-orange-300 bg-clip-text text-transparent">Promoções</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Preço de tabela riscado, revenda como preço de promoção. Lucro mínimo:
                        <strong>{{ rtrim(rtrim(number_format((float) $settings->min_margin_percent, 2, ',', ''), '0'), ',') }}%</strong> sobre o a pagar.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="openCreate"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-semibold shadow-lg">
                    <i class="bi bi-plus-lg"></i> Nova promoção
                </button>
                <button type="button" wire:click="openSettings"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-white dark:hover:bg-slate-700">
                    <i class="bi bi-gear"></i> Configurar
                </button>
            </div>
        </div>
    </div>

    {{-- Indicadores --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
        @foreach([
            ['Ativas', $stats['ativas'], 'bi-fire', 'from-rose-500 to-pink-600'],
            ['Agendadas', $stats['agendadas'], 'bi-calendar-event', 'from-indigo-500 to-purple-600'],
            ['Vencem em 3 dias', $stats['vencendo'], 'bi-hourglass-split', 'from-amber-500 to-orange-600'],
            ['Desconto médio', $stats['desconto'] . '%', 'bi-graph-down-arrow', 'from-emerald-500 to-teal-600'],
            ['Vendido em promoção', $money($stats['vendas']['revenue']), 'bi-bag-check', 'from-sky-500 to-blue-600'],
            ['Clientes economizaram', $money($stats['vendas']['savings']), 'bi-piggy-bank', 'from-fuchsia-500 to-purple-600'],
        ] as [$label, $value, $icon, $grad])
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/70 dark:bg-slate-800/70 border border-slate-200/70 dark:border-slate-700">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center shrink-0">
                    <i class="bi {{ $icon }} text-white"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100 truncate">{{ $value }}</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @if($stats['semTabela'] > 0)
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2 justify-between p-3 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
            <span><i class="bi bi-info-circle mr-1"></i> {{ $stats['semTabela'] }} produto(s) com estoque ainda não têm preço de tabela.</span>
            <button type="button" wire:click="backfillOriginalPrices" wire:loading.attr="disabled" wire:target="backfillOriginalPrices"
                    class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs whitespace-nowrap">
                <span wire:loading.remove wire:target="backfillOriginalPrices"><i class="bi bi-file-earmark-pdf"></i> Buscar nos PDFs antigos</span>
                <span wire:loading wire:target="backfillOriginalPrices">Lendo PDFs…</span>
            </button>
        </div>
    @endif

    {{-- Abas e filtros --}}
    <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
        <div class="flex gap-1 p-1 rounded-2xl bg-white/70 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 overflow-x-auto">
            @foreach($tabs as $key => [$label, $icon, $count])
                <button type="button" wire:click="setTab('{{ $key }}')"
                        class="px-3 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition {{ $tab === $key ? 'bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                    @if($count !== null)
                        <span class="ml-1 px-1.5 rounded-full text-[11px] {{ $tab === $key ? 'bg-white/25' : 'bg-slate-200 dark:bg-slate-700' }}">{{ $count }}</span>
                    @endif
                </button>
            @endforeach
        </div>
        <div class="flex-1 flex gap-2">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por nome ou código…"
                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-rose-400 focus:outline-none">
            </div>
            @if(in_array($tab, ['ativas', 'agendadas']))
                <select wire:model.live="sort" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200">
                    <option value="desconto">Maior desconto</option>
                    <option value="validade">Vence primeiro</option>
                    <option value="recentes">Mais recentes</option>
                    <option value="nome">Nome</option>
                </select>
            @endif
        </div>
    </div>

    {{-- Barra de seleção --}}
    @if($items->count() && $tab !== 'encerradas')
        <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
            <button type="button" wire:click="selectAllOnPage" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                <i class="bi bi-check2-square"></i> Selecionar página
            </button>
            @if(count($selected))
                <span class="text-slate-500 dark:text-slate-400">{{ count($selected) }} selecionado(s)</span>
                @if($tab === 'sugestoes')
                    <button type="button" wire:click="bulkStart" class="px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white font-semibold"><i class="bi bi-fire"></i> Pôr em promoção</button>
                @else
                    <button type="button" wire:click="openShare" class="px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold"><i class="bi bi-whatsapp"></i> Ofertas da semana</button>
                    <button type="button" wire:click="openBulk('desconto')" class="px-3 py-1.5 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white font-semibold"><i class="bi bi-percent"></i> Aplicar desconto</button>
                    <button type="button" wire:click="openBulk('validade')" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold"><i class="bi bi-calendar"></i> Mudar validade</button>
                    <button type="button" wire:click="bulkEnd" wire:confirm="Retirar as promoções selecionadas?" class="px-3 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-700 text-white font-semibold"><i class="bi bi-x-circle"></i> Retirar</button>
                @endif
                <button type="button" wire:click="$set('selected', [])" class="px-2 py-1.5 text-slate-500 hover:text-slate-700">Limpar</button>
            @endif
        </div>
    @endif

    {{-- Lista --}}
    @if($items->count() === 0)
        <div class="text-center py-16 rounded-3xl bg-white/60 dark:bg-slate-800/60 border border-dashed border-slate-300 dark:border-slate-600">
            <i class="bi bi-tags text-5xl text-slate-300 dark:text-slate-600"></i>
            <p class="mt-3 text-slate-600 dark:text-slate-300 font-semibold">
                @switch($tab)
                    @case('sugestoes') Nenhum produto com desconto de pelo menos {{ (int) $settings->suggest_min_discount }}% sobre a tabela. @break
                    @case('agendadas') Nenhuma promoção agendada. @break
                    @case('encerradas') Nenhuma promoção encerrada ainda. @break
                    @default Nenhuma promoção ativa.
                @endswitch
            </p>
            @if($tab === 'ativas' && $stats['sugestoes'])
                <button type="button" wire:click="setTab('sugestoes')" class="mt-3 text-rose-600 font-semibold hover:underline">Ver {{ $stats['sugestoes'] }} sugestão(ões)</button>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
            @foreach($items as $item)
                @php
                    if ($tab === 'sugestoes') {
                        $product = $item;
                        $original = (float) $item->price_original;
                        $promoValue = (float) $item->price_sale;
                        $discount = $original > 0 ? (int) round((1 - $promoValue / $original) * 100) : 0;
                    } else {
                        $product = $item->product;
                        $original = (float) $item->original_price;
                        $promoValue = (float) $item->promo_price;
                        $discount = $item->discount_percent;
                    }
                    $isSelected = in_array($item->id, $selected);
                @endphp
                <div wire:key="promo-{{ $tab }}-{{ $item->id }}"
                     class="relative flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-slate-800 border-2 {{ $isSelected ? 'border-rose-400 ring-2 ring-rose-300/50' : 'border-slate-200 dark:border-slate-700' }} shadow-sm hover:shadow-lg transition">
                    <div class="relative flex gap-3 p-3">
                        @if($tab !== 'encerradas')
                            <button type="button" wire:click="toggleSelect({{ $item->id }})"
                                    class="absolute top-2 left-2 z-10 w-6 h-6 rounded-md border-2 flex items-center justify-center {{ $isSelected ? 'bg-rose-500 border-rose-500 text-white' : 'bg-white/90 dark:bg-slate-700 border-slate-300 dark:border-slate-500' }}"
                                    title="Selecionar">
                                @if($isSelected)<i class="bi bi-check text-sm"></i>@endif
                            </button>
                        @endif
                        <img src="{{ $product?->image_url }}" alt="" class="w-24 h-24 object-contain rounded-xl bg-slate-50 dark:bg-slate-700 shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-[11px] text-slate-400"># {{ $product?->product_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-orange-500">-{{ $discount }}%</span>
                            </div>
                            <a href="{{ $product ? route('products.show', $product->product_code) : '#' }}" class="block font-bold text-sm text-slate-800 dark:text-slate-100 leading-tight line-clamp-2 hover:text-rose-600">{{ $product?->name ?? 'Produto removido' }}</a>
                            <div class="mt-1.5">
                                <span class="text-xs text-slate-400 line-through">{{ $money($original) }}</span>
                                <span class="ml-1 text-lg font-extrabold text-rose-600 dark:text-rose-400">{{ $money($promoValue) }}</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap gap-x-2">
                                <span><i class="bi bi-box-seam"></i> {{ (int) $product?->stock_quantity }} un.</span>
                                <span title="A pagar"><i class="bi bi-tag"></i> custo {{ $money($product?->price) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-2 text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap gap-x-3 gap-y-1">
                        @if($tab === 'sugestoes')
                            <span><i class="bi bi-lightbulb"></i> Tabela {{ $money($original) }} → revenda {{ $money($promoValue) }}</span>
                        @elseif($tab === 'encerradas')
                            <span><i class="bi bi-archive"></i> {{ \App\Models\Promotion::ENDED_REASONS[$item->ended_reason] ?? 'Encerrada' }} em {{ $item->ended_at?->format('d/m/Y') }}</span>
                        @else
                            @if($item->starts_at && $item->starts_at->isFuture())
                                <span><i class="bi bi-calendar-event"></i> Começa {{ $item->starts_at->format('d/m') }}</span>
                            @endif
                            <span class="{{ $item->ends_at && $item->ends_at->lte(now()->addDays(3)) ? 'text-amber-600 font-semibold' : '' }}">
                                <i class="bi bi-hourglass-split"></i>
                                {{ $item->ends_at ? 'Até ' . $item->ends_at->format('d/m') : 'Enquanto durar o estoque' }}
                            </span>
                            @if($last = $item->sends->first())
                                <span class="text-green-600"><i class="bi bi-whatsapp"></i> Enviado {{ $last->created_at->format('d/m') }}{{ $last->client ? ' para ' . \Illuminate\Support\Str::of($last->client->name)->before(' ') : '' }}</span>
                            @endif
                        @endif
                    </div>

                    <div class="mt-auto grid {{ $tab === 'sugestoes' || $tab === 'encerradas' ? 'grid-cols-2' : 'grid-cols-3' }} border-t border-slate-100 dark:border-slate-700 text-sm font-semibold">
                        @if($tab === 'sugestoes')
                            <button type="button" wire:click="openCreate({{ $item->id }})" class="py-2.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700"><i class="bi bi-pencil"></i> Ajustar</button>
                            <button type="button" wire:click="quickStart({{ $item->id }})" class="py-2.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-700 border-l border-slate-100 dark:border-slate-700"><i class="bi bi-fire"></i> Pôr em promoção</button>
                        @elseif($tab === 'encerradas')
                            <button type="button" wire:click="restart({{ $item->id }})" class="py-2.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-700"><i class="bi bi-arrow-repeat"></i> Reativar</button>
                            <span class="py-2.5 text-center text-[11px] text-slate-400 border-l border-slate-100 dark:border-slate-700">{{ $item->sends->count() }} envio(s)</span>
                        @else
                            <button type="button" wire:click="openEdit({{ $item->id }})" class="py-2.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700"><i class="bi bi-pencil"></i> Editar</button>
                            <button type="button" wire:click="openShare({{ $item->id }})" class="py-2.5 text-green-600 hover:bg-green-50 dark:hover:bg-slate-700 border-x border-slate-100 dark:border-slate-700"><i class="bi bi-whatsapp"></i> WhatsApp</button>
                            <button type="button" wire:click="endPromotion({{ $item->id }})" wire:confirm="Retirar a promoção? O produto volta para o preço de revenda." class="py-2.5 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="bi bi-x-circle"></i> Retirar</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $items->links() }}</div>
    @endif

    {{-- Modal: nova promoção / editar --}}
    @if($showEditModal)
        @php $preview = $this->editPreview; $editingProduct = $this->editingProduct; @endphp
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4" wire:keydown.escape="$set('showEditModal', false)">
            <div class="w-full sm:max-w-lg max-h-[95vh] overflow-y-auto bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">{{ $editingPromotionId ? 'Editar promoção' : 'Nova promoção' }}</h3>
                    <button type="button" wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    @if(!$editingProduct)
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Produto</label>
                            <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Digite o nome ou código…" autofocus
                                   class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <div class="mt-2 space-y-1">
                                @foreach($this->productResults as $result)
                                    <button type="button" wire:click="selectProduct({{ $result->id }})" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-slate-700 text-left">
                                        <img src="{{ $result->image_url }}" class="w-10 h-10 object-contain rounded-lg bg-slate-50" alt="">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $result->name }}</div>
                                            <div class="text-[11px] text-slate-500"># {{ $result->product_code }} · {{ (int) $result->stock_quantity }} un. · revenda {{ $money($result->price_sale) }}{{ $result->price_original ? ' · tabela ' . $money($result->price_original) : '' }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900">
                            <img src="{{ $editingProduct->image_url }}" class="w-14 h-14 object-contain rounded-xl bg-white" alt="">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-slate-800 dark:text-slate-100 leading-tight">{{ $editingProduct->name }}</div>
                                <div class="text-xs text-slate-500"># {{ $editingProduct->product_code }} · {{ (int) $editingProduct->stock_quantity }} un. · revenda {{ $money($editingProduct->price_sale) }}</div>
                            </div>
                            @unless($editingPromotionId)
                                <button type="button" wire:click="$set('editingProductId', null)" class="text-xs text-rose-600 font-semibold">Trocar</button>
                            @endunless
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">De (original)</label>
                                <div class="mt-1 relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">R$</span>
                                    <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="originalPrice"
                                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-500 line-through font-semibold">
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Por (promoção)</label>
                                <div class="mt-1 relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">R$</span>
                                    <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="promoPrice"
                                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border {{ $preview['belowMin'] ? 'border-red-400 ring-2 ring-red-200' : 'border-slate-200 dark:border-slate-600' }} bg-white dark:bg-slate-900 text-rose-600 font-bold">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 p-3 rounded-2xl bg-rose-50 dark:bg-rose-900/20">
                            <button type="button" wire:click="nudgeDiscount(-1)" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-rose-200 dark:border-rose-800 text-rose-600 font-bold" title="Menos desconto">−</button>
                            <div class="text-center">
                                <div class="text-2xl font-extrabold text-rose-600">{{ $preview['discount'] }}% OFF</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">cliente economiza {{ $money($preview['savings']) }}</div>
                            </div>
                            <button type="button" wire:click="nudgeDiscount(1)" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-rose-200 dark:border-rose-800 text-rose-600 font-bold" title="Mais desconto">+</button>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900">
                                <div class="text-slate-500">A pagar</div>
                                <div class="font-bold text-slate-700 dark:text-slate-200">{{ $money($preview['cost']) }}</div>
                            </div>
                            <div class="p-2 rounded-xl {{ $preview['profit'] < 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' }}">
                                <div>Lucro</div>
                                <div class="font-bold">{{ $money($preview['profit']) }} ({{ number_format($preview['margin'], 1, ',', '') }}%)</div>
                            </div>
                            <button type="button" wire:click="useMinPrice" class="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 hover:bg-amber-100" title="Usar o menor preço permitido">
                                <div>Mínimo</div>
                                <div class="font-bold">{{ $money($preview['min']) }}</div>
                            </button>
                        </div>
                        @if($preview['belowMin'])
                            <p class="text-xs text-red-600"><i class="bi bi-exclamation-triangle"></i> Abaixo do lucro mínimo de {{ rtrim(rtrim(number_format((float) $settings->min_margin_percent, 2, ',', ''), '0'), ',') }}%. O mínimo é {{ $money($preview['min']) }}.</p>
                        @endif
                        @error('promoPrice') <p class="text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Começa em</label>
                                <input type="date" wire:model="startsAt" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                <p class="text-[11px] text-slate-400 mt-0.5">Vazio = agora</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Termina em</label>
                                <input type="date" wire:model="endsAt" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                <p class="text-[11px] text-slate-400 mt-0.5">Vazio = até zerar o estoque</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Mensagem própria deste produto <span class="font-normal text-slate-400">(opcional)</span></label>
                            <textarea wire:model="message" rows="4" placeholder="Vazio = usa o modelo das configurações"
                                      class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200"></textarea>
                            <p class="text-[11px] text-slate-400">Campos: {{ implode(' ', array_keys(\App\Models\PromotionSetting::VARIABLES)) }}</p>
                        </div>
                    @endif
                </div>
                @if($editingProduct)
                    <div class="sticky bottom-0 flex gap-2 px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <button type="button" wire:click="$set('showEditModal', false)" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
                        <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold">
                            {{ $editingPromotionId ? 'Salvar' : 'Pôr em promoção' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal: ações em massa --}}
    @if($showBulkModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-5 space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">
                    {{ $bulkAction === 'desconto' ? 'Aplicar desconto' : 'Mudar validade' }} ({{ count($selected) }})
                </h3>
                @if($bulkAction === 'desconto')
                    <div>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Desconto sobre o preço original (%)</label>
                        <input type="text" inputmode="decimal" wire:model="bulkPercent" placeholder="Ex.: 30"
                               class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                        @error('bulkPercent') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Produtos que passariam do lucro mínimo ficam no mínimo permitido.</p>
                    </div>
                @else
                    <div>
                        <label class="text-sm text-slate-600 dark:text-slate-300">Termina em</label>
                        <input type="date" wire:model="bulkEndsAt" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                        <p class="text-[11px] text-slate-400 mt-1">Vazio = até zerar o estoque.</p>
                    </div>
                @endif
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('showBulkModal', false)" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
                    <button type="button" wire:click="applyBulk" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold">Aplicar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: WhatsApp --}}
    @if($showShareModal)
        @php $share = $this->shareData; @endphp
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4">
            <div class="w-full sm:max-w-3xl max-h-[95vh] overflow-y-auto bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl"
                 x-data="promoShare(@js($share['cards']))" x-init="render()">
                <div class="sticky top-0 z-10 flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">
                        <i class="bi bi-whatsapp text-green-500"></i>
                        {{ count($shareIds) > 1 ? 'Ofertas da semana (' . count($shareIds) . ' produtos)' : 'Enviar promoção' }}
                    </h3>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="p-5 grid md:grid-cols-2 gap-5">
                    <div class="space-y-3">
                        <div class="flex gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 text-xs font-semibold">
                            <button type="button" @click="format = 'square'; render()" :class="format === 'square' ? 'bg-white dark:bg-slate-700 shadow' : ''" class="flex-1 py-1.5 rounded-lg text-slate-700 dark:text-slate-200">Post (quadrado)</button>
                            <button type="button" @click="format = 'story'; render()" :class="format === 'story' ? 'bg-white dark:bg-slate-700 shadow' : ''" class="flex-1 py-1.5 rounded-lg text-slate-700 dark:text-slate-200">Status (vertical)</button>
                        </div>
                        <div class="rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 flex justify-center">
                            <canvas x-ref="canvas" class="max-h-[420px] w-auto max-w-full"></canvas>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                            <button type="button" @click="download()" class="py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-download"></i> Baixar imagem</button>
                            <button type="button" @click="copyImage()" class="py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-images"></i> Copiar imagem</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Para</label>
                            <select wire:change="setShareClient($event.target.value)" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200">
                                <option value="">Qualquer contato / status / grupo</option>
                                @foreach($share['allClients'] as $c)
                                    <option value="{{ $c->id }}" @selected($shareClientId === $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Mensagem</label>
                            <textarea wire:model="shareText" x-ref="text" rows="9" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200 font-mono"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-semibold">
                            <button type="button" @click="shareAll()" class="py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white"><i class="bi bi-share"></i> Compartilhar</button>
                            <button type="button" @click="openWhatsapp({{ $shareClientId ?? 'null' }})" class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white"><i class="bi bi-whatsapp"></i> Abrir WhatsApp</button>
                            <button type="button" @click="copyText()" class="py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200"><i class="bi bi-clipboard"></i> Copiar texto</button>
                        </div>
                        <p class="text-[11px] text-slate-400">"Compartilhar" manda foto e texto juntos pelo celular. No computador, copie a imagem e cole na conversa depois de abrir o WhatsApp.</p>

                        @if($share['clients']->count())
                            <div class="pt-2">
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Sugestões de clientes</div>
                                <div class="space-y-1 max-h-56 overflow-y-auto">
                                    @foreach($share['clients'] as $row)
                                        <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $row['client']->name }}</div>
                                                <div class="text-[11px] text-slate-500">
                                                    {{ $row['reason'] }}
                                                    @if($row['last_send'])
                                                        · <span class="text-green-600">enviado em {{ $row['last_send']->created_at->format('d/m') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button type="button" @click="openWhatsapp({{ $row['client']->id }})"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $row['last_send'] ? 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-green-500 text-white' }}">
                                                <i class="bi bi-whatsapp"></i> {{ $row['last_send'] ? 'Reenviar' : 'Enviar' }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: configurações --}}
    @if($showSettingsModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4">
            <div class="w-full sm:max-w-lg max-h-[95vh] overflow-y-auto bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100"><i class="bi bi-gear"></i> Configurações das promoções</h3>
                    <button type="button" wire:click="$set('showSettingsModal', false)" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Lucro mínimo (%)</label>
                            <input type="text" inputmode="decimal" wire:model="settingsForm.min_margin_percent" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            @error('settingsForm.min_margin_percent') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sugerir a partir de (%)</label>
                            <input type="text" inputmode="decimal" wire:model="settingsForm.suggest_min_discount" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            @error('settingsForm.suggest_min_discount') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Validade padrão (dias)</label>
                            <input type="number" min="1" wire:model="settingsForm.default_days" placeholder="Sem data" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            @error('settingsForm.default_days') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-2">O preço de promoção nunca fica abaixo do a pagar + o lucro mínimo. Com 5%, um produto que custa R$ 100,00 não sai por menos de R$ 105,00.</p>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Modelo da mensagem</label>
                            <button type="button" wire:click="resetTemplate" class="text-xs text-rose-600 font-semibold">Restaurar padrão</button>
                        </div>
                        <textarea wire:model="settingsForm.message_template" rows="6" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm font-mono text-slate-700 dark:text-slate-200"></textarea>
                        @error('settingsForm.message_template') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach(\App\Models\PromotionSetting::VARIABLES as $var => $label)
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-[11px] text-slate-600 dark:text-slate-300" title="{{ $label }}">{{ $var }}</span>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">*texto* = negrito e ~texto~ = riscado no WhatsApp.</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Rodapé (assinatura)</label>
                        <textarea wire:model="settingsForm.footer" rows="2" placeholder="Ex.: Ana · Pix, cartão ou parcelado" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200"></textarea>
                        <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input type="checkbox" wire:model="settingsForm.footer_catalog_link" class="rounded text-rose-500">
                            Incluir o link do meu catálogo
                        </label>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-between gap-3">
                        <span class="text-xs text-slate-600 dark:text-slate-300">Preencher o preço de tabela dos produtos já cadastrados relendo os PDFs de uploads anteriores.</span>
                        <button type="button" wire:click="backfillOriginalPrices" wire:loading.attr="disabled" wire:target="backfillOriginalPrices" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold whitespace-nowrap">
                            <span wire:loading.remove wire:target="backfillOriginalPrices">Buscar</span>
                            <span wire:loading wire:target="backfillOriginalPrices">Lendo…</span>
                        </button>
                    </div>
                </div>
                <div class="sticky bottom-0 flex gap-2 px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <button type="button" wire:click="$set('showSettingsModal', false)" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold">Cancelar</button>
                    <button type="button" wire:click="saveSettings" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold">Salvar</button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    /**
     * Imagem da promoção (post quadrado ou status vertical) desenhada em canvas,
     * e envio por WhatsApp. Cada envio é registrado no servidor antes de abrir.
     */
    Alpine.data('promoShare', (cards) => ({
        cards,
        format: 'square',
        images: {},

        loadImage(src) {
            if (!src) return Promise.resolve(null);
            if (this.images[src]) return Promise.resolve(this.images[src]);
            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => { this.images[src] = img; resolve(img); };
                img.onerror = () => resolve(null);
                img.src = src;
            });
        },

        wrap(ctx, text, maxWidth, maxLines) {
            const words = String(text).split(/\s+/);
            const lines = [];
            let line = '';
            for (const w of words) {
                const test = line ? line + ' ' + w : w;
                if (ctx.measureText(test).width > maxWidth && line) {
                    lines.push(line);
                    line = w;
                    if (lines.length === maxLines) break;
                } else {
                    line = test;
                }
            }
            if (lines.length < maxLines && line) lines.push(line);
            if (lines.length === maxLines && words.join(' ').length > lines.join(' ').length) {
                lines[maxLines - 1] = lines[maxLines - 1].replace(/\s*\S*$/, '') + '…';
            }
            return lines;
        },

        async render() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.cards.length) return;
            const W = 1080, H = this.format === 'story' ? 1920 : 1080;
            canvas.width = W; canvas.height = H;
            const ctx = canvas.getContext('2d');

            const bg = ctx.createLinearGradient(0, 0, W, H);
            bg.addColorStop(0, '#fff1f2'); bg.addColorStop(1, '#fce7f3');
            ctx.fillStyle = bg; ctx.fillRect(0, 0, W, H);

            // Faixa do topo
            ctx.fillStyle = '#e11d48';
            ctx.fillRect(0, 0, W, this.format === 'story' ? 200 : 130);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold ' + (this.format === 'story' ? 92 : 70) + 'px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(this.cards.length > 1 ? 'OFERTAS DA SEMANA' : 'PROMOÇÃO', W / 2, this.format === 'story' ? 135 : 92);

            if (this.cards.length === 1) {
                await this.drawSingle(ctx, this.cards[0], W, H);
            } else {
                await this.drawList(ctx, W, H);
            }
        },

        async drawSingle(ctx, c, W, H) {
            const story = this.format === 'story';
            const imgBox = story ? { x: 140, y: 280, s: 800 } : { x: 290, y: 170, s: 500 };
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.roundRect(imgBox.x - 20, imgBox.y - 20, imgBox.s + 40, imgBox.s + 40, 40);
            ctx.fill();
            const img = await this.loadImage(c.image);
            if (img) {
                const r = Math.min(imgBox.s / img.width, imgBox.s / img.height);
                const w = img.width * r, h = img.height * r;
                ctx.drawImage(img, imgBox.x + (imgBox.s - w) / 2, imgBox.y + (imgBox.s - h) / 2, w, h);
            }

            // Selo de desconto
            const bx = imgBox.x + imgBox.s - 40, by = imgBox.y + 20, br = story ? 110 : 85;
            ctx.fillStyle = '#f97316';
            ctx.beginPath(); ctx.arc(bx, by, br, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#fff';
            ctx.font = 'bold ' + (story ? 70 : 54) + 'px sans-serif';
            ctx.fillText('-' + c.discount + '%', bx, by + (story ? 25 : 19));

            let y = imgBox.y + imgBox.s + (story ? 130 : 90);
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold ' + (story ? 58 : 44) + 'px sans-serif';
            for (const line of this.wrap(ctx, c.name, W - 140, 2)) {
                ctx.fillText(line, W / 2, y);
                y += story ? 72 : 54;
            }

            y += story ? 40 : 14;
            ctx.font = (story ? 54 : 40) + 'px sans-serif';
            ctx.fillStyle = '#64748b';
            const deText = 'De ' + c.original;
            ctx.fillText(deText, W / 2, y);
            const dw = ctx.measureText(deText).width;
            ctx.strokeStyle = '#64748b'; ctx.lineWidth = story ? 6 : 4;
            ctx.beginPath(); ctx.moveTo(W / 2 - dw / 2, y - (story ? 18 : 13)); ctx.lineTo(W / 2 + dw / 2, y - (story ? 18 : 13)); ctx.stroke();

            y += story ? 130 : 86;
            ctx.fillStyle = '#e11d48';
            ctx.font = 'bold ' + (story ? 120 : 84) + 'px sans-serif';
            ctx.fillText('Por ' + c.promo, W / 2, y);

            ctx.fillStyle = '#475569';
            ctx.font = (story ? 44 : 32) + 'px sans-serif';
            ctx.fillText(c.validity, W / 2, story ? H - 90 : H - 32);
        },

        async drawList(ctx, W, H) {
            const story = this.format === 'story';
            const top = story ? 250 : 160;
            const max = story ? 7 : 4;
            const items = this.cards.slice(0, max);
            const rowH = (H - top - (story ? 120 : 60)) / items.length;
            ctx.textAlign = 'left';
            for (let i = 0; i < items.length; i++) {
                const c = items[i];
                const y = top + i * rowH;
                ctx.fillStyle = '#ffffff';
                ctx.beginPath(); ctx.roundRect(40, y + 8, W - 80, rowH - 16, 28); ctx.fill();
                const s = Math.min(rowH - 40, 200);
                const img = await this.loadImage(c.image);
                if (img) {
                    const r = Math.min(s / img.width, s / img.height);
                    ctx.drawImage(img, 70 + (s - img.width * r) / 2, y + (rowH - img.height * r) / 2, img.width * r, img.height * r);
                }
                const tx = 70 + s + 30, tw = W - tx - 200;
                ctx.fillStyle = '#1e293b';
                ctx.font = 'bold 36px sans-serif';
                const lines = this.wrap(ctx, c.name, tw, 2);
                let ly = y + rowH / 2 - (lines.length > 1 ? 40 : 20);
                for (const l of lines) { ctx.fillText(l, tx, ly); ly += 42; }
                ctx.font = '30px sans-serif'; ctx.fillStyle = '#64748b';
                ctx.fillText(c.original, tx, ly + 8);
                const ow = ctx.measureText(c.original).width;
                ctx.strokeStyle = '#64748b'; ctx.lineWidth = 3;
                ctx.beginPath(); ctx.moveTo(tx, ly - 2); ctx.lineTo(tx + ow, ly - 2); ctx.stroke();
                ctx.font = 'bold 44px sans-serif'; ctx.fillStyle = '#e11d48';
                ctx.fillText(c.promo, tx + ow + 20, ly + 10);
                ctx.fillStyle = '#f97316';
                ctx.beginPath(); ctx.roundRect(W - 190, y + rowH / 2 - 35, 130, 70, 35); ctx.fill();
                ctx.fillStyle = '#fff'; ctx.font = 'bold 36px sans-serif'; ctx.textAlign = 'center';
                ctx.fillText('-' + c.discount + '%', W - 125, y + rowH / 2 + 13);
                ctx.textAlign = 'left';
            }
            if (this.cards.length > max) {
                ctx.textAlign = 'center'; ctx.fillStyle = '#475569'; ctx.font = '34px sans-serif';
                ctx.fillText('e mais ' + (this.cards.length - max) + ' oferta(s)!', W / 2, H - (story ? 60 : 22));
            }
            ctx.textAlign = 'center';
        },

        blob() {
            return new Promise((resolve) => this.$refs.canvas.toBlob(resolve, 'image/png'));
        },

        async download() {
            const a = document.createElement('a');
            a.href = this.$refs.canvas.toDataURL('image/png');
            a.download = 'promocao-' + this.format + '.png';
            a.click();
        },

        async copyImage() {
            try {
                const blob = await this.blob();
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                this.toast('Imagem copiada. Cole na conversa do WhatsApp.');
            } catch (e) {
                this.download();
            }
        },

        async copyText() {
            await navigator.clipboard.writeText(this.$refs.text.value);
            await $wire.recordShare('copiar', null);
            this.toast('Texto copiado.');
        },

        async openWhatsapp(clientId) {
            // Abre a janela antes da chamada ao servidor para o navegador não bloquear o pop-up.
            const win = window.open('about:blank', '_blank');
            await $wire.$set('shareText', this.$refs.text.value, false);
            const url = await $wire.recordShare('whatsapp', clientId);
            if (win) { win.location.href = url; } else { window.location.href = url; }
            $wire.$refresh();
        },

        async shareAll() {
            const text = this.$refs.text.value;
            try {
                const file = new File([await this.blob()], 'promocao.png', { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], text });
                    await $wire.$set('shareText', text, false);
                    await $wire.recordShare('compartilhar', {{ $shareClientId ?? 'null' }});
                    $wire.$refresh();
                    return;
                }
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
            // Sem compartilhamento nativo (computador): copia a imagem e abre o WhatsApp com o texto.
            await this.copyImage();
            this.openWhatsapp({{ $shareClientId ?? 'null' }});
        },

        toast(message) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message, duration: 3000 } }));
        },
    }));
</script>
@endscript
