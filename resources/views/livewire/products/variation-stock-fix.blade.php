<div class="variation-stock-fix-page">
    <x-product-page-header title="Acertar estoque das variações" icon="bi-diagram-3" active="variacoes"
        subtitle="Corrija o estoque que caiu na linha errada da família (no principal ou em outra cor) antes da correção de 08/10">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"><i class="bi bi-exclamation-triangle"></i>{{ $suspectCount }} {{ $suspectCount === 1 ? 'suspeita' : 'suspeitas' }}</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="bi bi-diagram-3"></i>{{ $familyCount }} {{ $familyCount === 1 ? 'família' : 'famílias' }}</span>
        </x-slot:meta>
    </x-product-page-header>

    <x-list-toolbar placeholder="Buscar família por nome, código ou variação (ex.: Cauterização, Nude)...">
        <x-toolbar.group>
            <x-toolbar.chip wire:click="setFilter('suspeitas')" :active="$filter !== 'todas'" icon="bi-exclamation-triangle">Só suspeitas <span class="opacity-75">({{ $suspectCount }})</span></x-toolbar.chip>
            <x-toolbar.chip wire:click="setFilter('todas')" :active="$filter === 'todas'" icon="bi-diagram-3">Todas as famílias <span class="opacity-75">({{ $familyCount }})</span></x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.pager :paginator="$families" />
    </x-list-toolbar>

    <details class="mb-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-4 py-3 text-sm text-slate-600 dark:text-slate-300 shadow-sm">
        <summary class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200"><i class="bi bi-info-circle mr-1 text-indigo-500"></i>O que conta como suspeita?</summary>
        <ul class="mt-2 space-y-1 pl-5 list-disc">
            <li><b>Principal com estoque</b> e alguma variação zerada: o estoque costumava cair no principal em vez das cores.</li>
            <li><b>Código repetido</b> dentro da família ou <b>variações com o mesmo nome</b>: sinal de linha duplicada.</li>
            <li><b>Todo o estoque numa só variação</b> (principal zerado, só uma cor com estoque): pode ser estoque de outra cor.</li>
        </ul>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Cada mudança salva entra em Gestão › Movimentações como acerto manual.</p>
    </details>

    <div class="relative">
        <div wire:loading.delay wire:target="setFilter,search,nextPage,previousPage,gotoPage"
             class="absolute inset-0 z-10 flex items-start justify-center rounded-2xl bg-white/60 dark:bg-slate-900/60 pt-16">
            <div class="flex items-center gap-2 rounded-xl bg-white dark:bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-200 shadow">
                <i class="bi bi-arrow-repeat animate-spin"></i>Carregando...
            </div>
        </div>

        @if($families->isEmpty())
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 px-6 py-12 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-500/10">
                    <i class="bi {{ $filter !== 'todas' && $search === '' ? 'bi-check2-circle text-emerald-600 dark:text-emerald-300' : 'bi-search text-slate-400' }} text-2xl"></i>
                </div>
                @if($search !== '')
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100">Nenhuma família encontrada</h4>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tente outro termo{{ $filter !== 'todas' ? ' ou veja todas as famílias' : '' }}.</p>
                @elseif($filter !== 'todas')
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100">Nenhuma família suspeita</h4>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">O estoque das variações parece certo. Veja todas as famílias para conferir uma a uma.</p>
                @else
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100">Nenhuma família de variações</h4>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Produtos com variações aparecem aqui.</p>
                @endif
                @if($filter !== 'todas')
                    <button type="button" wire:click="setFilter('todas')" class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition"><i class="bi bi-diagram-3"></i>Ver todas as famílias</button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 2xl:grid-cols-2">
                @foreach($families as $f)
                    @php
                        $root = $f['root_id'];
                        $parent = $f['parent'];
                        $members = collect([$parent])->merge($f['variants']);
                        $orig = $members->mapWithKeys(fn ($p) => [$p->id => (int) $p->stock_quantity]);
                    @endphp
                    <div wire:key="fam-{{ $root }}"
                         x-data="{
                            orig: @js($orig),
                            get s() { return ($wire.stocks && $wire.stocks[{{ $root }}]) || {} },
                            get after() { return Object.values(this.s).reduce((a, b) => a + (parseInt(b) || 0), 0) },
                            changed(id) { return (parseInt(this.s[id]) || 0) !== this.orig[id] },
                            get dirty() { return Object.keys(this.orig).some(id => this.changed(id)) },
                         }"
                         class="rounded-2xl border bg-white dark:bg-slate-900/80 shadow-sm transition {{ $f['score'] > 0 ? 'border-amber-200 dark:border-amber-500/30' : 'border-slate-200/80 dark:border-slate-700/70' }}"
                         :class="dirty && 'ring-2 ring-indigo-400/40'">

                        {{-- Cabeçalho da família --}}
                        <div class="flex items-start gap-3 border-b border-slate-100 dark:border-slate-800 px-4 py-3">
                            <x-product-thumb :product="$parent" size="h-12 w-12" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('products.edit', $parent->id) }}" class="block truncate font-semibold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-300" title="{{ $parent->name }}">{{ $parent->name }}</a>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    <span><i class="bi bi-diagram-3 mr-0.5"></i>{{ $f['variants']->count() }} {{ $f['variants']->count() === 1 ? 'variação' : 'variações' }}</span>
                                    @if($parent->variation_attribute)<span>· {{ $parent->variation_attribute }}</span>@endif
                                </div>
                                @if($f['flags'])
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach($f['flags'] as $key => $label)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ in_array($key, ['parent_stock', 'duplicate_code']) ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' }}">
                                                <i class="bi bi-exclamation-triangle"></i>{{ $label }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Total</div>
                                <div class="text-sm font-bold tabular-nums text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                    <span>{{ $f['total'] }}</span>
                                    <template x-if="after !== {{ $f['total'] }}">
                                        <span><i class="bi bi-arrow-right text-xs text-slate-400"></i> <span x-text="after" :class="after > {{ $f['total'] }} ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Linhas: principal + variações --}}
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($members as $p)
                                @php $isParent = $p->id === $parent->id; @endphp
                                <li wire:key="row-{{ $root }}-{{ $p->id }}" class="flex items-center gap-3 px-4 py-2.5 {{ $isParent ? 'bg-slate-50/70 dark:bg-slate-800/40' : '' }}">
                                    <x-product-thumb :product="$p" size="h-10 w-10" rounded="rounded-lg" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if($isParent)
                                                <span class="rounded-md bg-indigo-100 dark:bg-indigo-500/20 px-1.5 py-0.5 text-[11px] font-bold text-indigo-700 dark:text-indigo-300">Principal</span>
                                            @endif
                                            <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $isParent ? ($p->variation_value ?: 'Produto principal') : ($p->variation_value ?: $p->name) }}</span>
                                        </div>
                                        <div class="truncate text-xs text-slate-500 dark:text-slate-400">
                                            <i class="bi bi-upc"></i> {{ $p->product_code ?: 'sem código' }}
                                            <span class="ml-1">· atual <b class="tabular-nums text-slate-700 dark:text-slate-200">{{ (int) $p->stock_quantity }}</b></span>
                                        </div>
                                    </div>
                                    <label class="sr-only" for="stk-{{ $p->id }}">Novo estoque de {{ $p->variation_value ?: $p->name }}</label>
                                    <input id="stk-{{ $p->id }}" type="number" min="0" step="1" inputmode="numeric"
                                           wire:model="stocks.{{ $root }}.{{ $p->id }}"
                                           :class="changed({{ $p->id }}) ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-200' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100'"
                                           class="w-20 sm:w-24 shrink-0 rounded-xl border px-2.5 py-2 text-right text-sm font-semibold tabular-nums focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30">
                                </li>
                            @endforeach
                        </ul>

                        {{-- Ações --}}
                        <div class="flex flex-col gap-2 border-t border-slate-100 dark:border-slate-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-2">
                                <select wire:model="moveTarget.{{ $root }}" aria-label="Mover estoque do principal para"
                                        class="min-w-0 flex-1 sm:w-56 sm:flex-none rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 py-2 pl-3 pr-8 text-sm text-slate-700 dark:text-slate-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/30">
                                    <option value="">Mover do principal para…</option>
                                    @foreach($f['variants'] as $v)
                                        <option value="{{ $v->id }}">{{ $v->variation_value ?: $v->name }}{{ $v->product_code ? ' (' . $v->product_code . ')' : '' }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="moveParentStock({{ $root }})" wire:loading.attr="disabled" wire:target="moveParentStock({{ $root }})"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                    <i class="bi bi-arrow-down-right-square"></i>Mover
                                </button>
                            </div>
                            <div class="flex items-center gap-2 sm:justify-end">
                                <button type="button" x-show="dirty" x-cloak wire:click="resetFamily({{ $root }})"
                                        class="inline-flex items-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                    <i class="bi bi-arrow-counterclockwise"></i>Desfazer
                                </button>
                                <button type="button" wire:click="saveFamily({{ $root }})"
                                        wire:confirm="Salvar o novo estoque de &quot;{{ $parent->name }}&quot;? Cada linha alterada fica registrada em Movimentações."
                                        wire:loading.attr="disabled" wire:target="saveFamily({{ $root }})"
                                        :disabled="!dirty"
                                        class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-500/25 transition disabled:opacity-40 disabled:shadow-none">
                                    <span wire:loading.remove wire:target="saveFamily({{ $root }})"><i class="bi bi-check2-circle"></i> Salvar família</span>
                                    <span wire:loading wire:target="saveFamily({{ $root }})"><i class="bi bi-arrow-repeat animate-spin"></i> Salvando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($families->hasPages())
                <div class="mt-4">{{ $families->links() }}</div>
            @endif
        @endif
    </div>
</div>
