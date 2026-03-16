@props([
    'categories' => collect(),
    'search' => '',
    'category' => '',
    'tipo' => '',
    'status_filtro' => '',
    'preco_min' => '',
    'preco_max' => '',
    'perPage' => 12,
    'perPageOptions' => [],
    'ordem' => '',
    'estoque_filtro' => '',
    'estoque_valor' => '',
    'data_inicio' => '',
    'data_fim' => '',
    'sem_imagem' => false,
    'totalProducts' => 0,
    'semEstoque' => false
])

@php
    $hasActiveFilters = $search || $category || $tipo || $status_filtro || $preco_min || $preco_max || $estoque_filtro || $estoque_valor || $data_inicio || $data_fim || $sem_imagem || $semEstoque;
@endphp

<div class="space-y-4 md:space-y-5">
    <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/70 dark:bg-slate-900/40 p-3 md:p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    <i class="bi bi-box-seam mr-1"></i>{{ $totalProducts }} resultados
                </span>
                @if($hasActiveFilters)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <i class="bi bi-check2-circle mr-1"></i>Filtros ativos
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex items-stretch gap-2">
                <button type="button" wire:click="setQuickFilter('novos')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                    Novos 7d
                </button>
                <button type="button" wire:click="setQuickFilter('baixo-estoque')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-orange-400 dark:hover:border-orange-500 transition-colors">
                    Baixo estoque
                </button>
                <button type="button" wire:click="setQuickFilter('sem-imagem')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-purple-400 dark:hover:border-purple-500 transition-colors">
                    Sem imagem
                </button>
                <button type="button" wire:click="setQuickFilter('kits')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-cyan-400 dark:hover:border-cyan-500 transition-colors">
                    Somente kits
                </button>
                <button type="button" wire:click="setQuickFilter('preco-zero')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-rose-400 dark:hover:border-rose-500 transition-colors">
                    Preco zero
                </button>
                <button type="button" wire:click="clearFilters"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 transition-all shadow-md">
                    Limpar tudo
                </button>
            </div>
        </div>
    </div>

    <!-- Prioridade: Itens por Pagina + Ordenacao -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
        <div class="xl:col-span-4 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                <i class="bi bi-eye mr-2 text-pink-500"></i>Itens por pagina
            </h4>
            <div class="grid grid-cols-3 sm:grid-cols-4 xl:grid-cols-3 gap-2">
                @foreach($perPageOptions as $option)
                    <button wire:click="$set('perPage', {{ $option }})"
                        class="flex items-center justify-center min-h-[52px] p-2.5 bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $perPage == $option ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-pink-300 dark:hover:border-pink-500' }}">
                        <i class="bi bi-grid-3x2-gap text-pink-500 text-sm mr-1.5"></i>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $option }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="xl:col-span-8 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                <i class="bi bi-sort-alpha-down mr-2 text-amber-500"></i>Ordenacao
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                <button wire:click="toggleSort('data')"
                    class="flex flex-col items-center justify-center gap-2 p-3 min-h-[88px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ str_contains($ordem, 'data') || str_contains($ordem, 'recente') ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                    <i class="bi bi-calendar text-blue-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Data</span>
                        @if(str_contains($ordem, 'recente') || $ordem === 'data_desc')
                            <i class="bi bi-arrow-down text-blue-500 text-xs"></i>
                        @elseif($ordem === 'data_asc')
                            <i class="bi bi-arrow-up text-blue-500 text-xs"></i>
                        @endif
                    </div>
                </button>

                <button wire:click="toggleSort('updated')"
                    class="flex flex-col items-center justify-center gap-2 p-3 min-h-[88px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ str_contains($ordem, 'updated') || str_contains($ordem, 'atualizado') ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500' }}">
                    <i class="bi bi-clock-history text-orange-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Atualizado</span>
                        @if(str_contains($ordem, 'atualizado') || $ordem === 'updated_desc')
                            <i class="bi bi-arrow-down text-orange-500 text-xs"></i>
                        @elseif($ordem === 'updated_asc')
                            <i class="bi bi-arrow-up text-orange-500 text-xs"></i>
                        @endif
                    </div>
                </button>

                <button wire:click="toggleSort('nome')"
                    class="flex flex-col items-center justify-center gap-2 p-3 min-h-[88px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ str_contains($ordem, 'az') || str_contains($ordem, 'nome') ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500' }}">
                    <i class="bi bi-sort-alpha-down text-purple-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nome</span>
                        @if($ordem === 'az' || $ordem === 'nome_asc')
                            <i class="bi bi-arrow-up text-purple-500 text-xs"></i>
                        @elseif($ordem === 'nome_desc')
                            <i class="bi bi-arrow-down text-purple-500 text-xs"></i>
                        @endif
                    </div>
                </button>

                <button wire:click="toggleSort('preco')"
                    class="flex flex-col items-center justify-center gap-2 p-3 min-h-[88px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ str_contains($ordem, 'preco') ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500' }}">
                    <i class="bi bi-currency-dollar text-green-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Preco</span>
                        @if($ordem === 'preco_asc')
                            <i class="bi bi-arrow-up text-green-500 text-xs"></i>
                        @elseif($ordem === 'preco_desc')
                            <i class="bi bi-arrow-down text-green-500 text-xs"></i>
                        @endif
                    </div>
                </button>

                <button wire:click="toggleSort('estoque')"
                    class="flex flex-col items-center justify-center gap-2 p-3 min-h-[88px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ str_contains($ordem, 'estoque') ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-500' }}">
                    <i class="bi bi-stack text-teal-500 text-lg"></i>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Estoque</span>
                        @if($ordem === 'estoque_asc')
                            <i class="bi bi-arrow-up text-teal-500 text-xs"></i>
                        @elseif($ordem === 'estoque_desc')
                            <i class="bi bi-arrow-down text-teal-500 text-xs"></i>
                        @endif
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <div class="lg:col-span-8 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

                <!-- Status -->
                <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-activity mr-2 text-emerald-500"></i>Status
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('status_filtro', '')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $status_filtro === '' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500' }}">
                            <i class="bi bi-list-ul text-emerald-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                        </button>

                        <button wire:click="$set('status_filtro', 'ativo')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $status_filtro === 'ativo' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500' }}">
                            <i class="bi bi-check-circle text-green-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ativo</span>
                        </button>

                        <button wire:click="$set('status_filtro', 'inativo')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $status_filtro === 'inativo' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-yellow-300 dark:hover:border-yellow-500' }}">
                            <i class="bi bi-pause-circle text-yellow-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Inativo</span>
                        </button>

                        <button wire:click="$set('status_filtro', 'descontinuado')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $status_filtro === 'descontinuado' ? 'border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500' }}">
                            <i class="bi bi-x-circle text-red-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Descontinuado</span>
                        </button>
                    </div>
                </div>

                <!-- Tipo de Produto -->
                <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-box-seam mr-2 text-indigo-500"></i>Tipo
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('tipo', '')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $tipo === '' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500' }}">
                            <i class="bi bi-list-ul text-indigo-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                        </button>

                        <button wire:click="$set('tipo', 'simples')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $tipo === 'simples' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                            <i class="bi bi-box text-blue-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Simples</span>
                        </button>

                        <button wire:click="$set('tipo', 'kit')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 col-span-2 {{ $tipo === 'kit' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-green-300 dark:hover:border-green-500' }}">
                            <i class="bi bi-boxes text-green-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kit</span>
                        </button>
                    </div>
                </div>

                <!-- Estoque -->
                <div class="space-y-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center">
                        <i class="bi bi-stack mr-2 text-orange-500"></i>Estoque
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="applyStockPreset('all')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $estoque_filtro === '' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-500' }}">
                            <i class="bi bi-check2-circle text-indigo-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Todos</span>
                        </button>

                        <button wire:click="applyStockPreset('zero')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 {{ $estoque_filtro === 'zerado' || $semEstoque ? 'border-red-500 bg-red-50 dark:bg-red-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-red-300 dark:hover:border-red-500' }}">
                            <i class="bi bi-exclamation-triangle text-red-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Zerado</span>
                        </button>

                        <button wire:click="applyStockPreset('low-10')"
                            class="flex flex-col items-center justify-center gap-1.5 p-3 min-h-[82px] bg-white dark:bg-slate-700 rounded-xl border-2 transition-all duration-200 col-span-2 {{ $estoque_filtro === 'abaixo' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-amber-300 dark:hover:border-amber-500' }}">
                            <i class="bi bi-graph-down text-amber-500 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Abaixo de um valor</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="applyStockPreset('low-5')"
                            class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ $estoque_filtro === 'abaixo' && (string) $estoque_valor === '5' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                            &lt; 5
                        </button>
                        <button type="button" wire:click="applyStockPreset('low-10')"
                            class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ $estoque_filtro === 'abaixo' && (string) $estoque_valor === '10' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                            &lt; 10
                        </button>
                        <button type="button" wire:click="applyStockPreset('low-20')"
                            class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ $estoque_filtro === 'abaixo' && (string) $estoque_valor === '20' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                            &lt; 20
                        </button>
                    </div>

                    <div x-show="$wire.estoque === 'abaixo'" x-cloak>
                        <label class="text-[11px] text-slate-600 dark:text-slate-400">Quantidade limite</label>
                        <input type="number" min="1" wire:model.live.debounce.400ms="estoque_valor"
                            class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-400"
                            placeholder="Ex: 10">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                    <i class="bi bi-tags mr-2 text-cyan-500"></i>Categoria
                </h4>

                <select wire:model.live="category"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500/30 focus:border-cyan-400">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if($categories->count())
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('category', '')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors {{ $category === '' ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:border-cyan-400' }}">
                            Todas
                        </button>
                        @foreach($categories->take(8) as $cat)
                            <button type="button" wire:click="$set('category', '{{ $cat->id }}')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors {{ (string) $category === (string) $cat->id ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:border-cyan-400' }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                    <i class="bi bi-cash-stack mr-2 text-emerald-500"></i>Faixa de preco
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[11px] text-slate-600 dark:text-slate-400">Minimo</label>
                        <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="preco_min"
                            class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400"
                            placeholder="0,00">
                    </div>
                    <div>
                        <label class="text-[11px] text-slate-600 dark:text-slate-400">Maximo</label>
                        <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="preco_max"
                            class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400"
                            placeholder="9999,99">
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <button type="button" wire:click="applyPricePreset('all')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ $preco_min === '' && $preco_max === '' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        Todos
                    </button>
                    <button type="button" wire:click="applyPricePreset('0-99')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ (string) $preco_min === '0' && (string) $preco_max === '99.99' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        R$ 0-99
                    </button>
                    <button type="button" wire:click="applyPricePreset('100-299')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ (string) $preco_min === '100' && (string) $preco_max === '299.99' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        R$ 100-299
                    </button>
                    <button type="button" wire:click="applyPricePreset('300-999')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ (string) $preco_min === '300' && (string) $preco_max === '999.99' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        R$ 300-999
                    </button>
                    <button type="button" wire:click="applyPricePreset('1000+')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ (string) $preco_min === '1000' && $preco_max === '' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        R$ 1000+
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                    <i class="bi bi-calendar-range mr-2 text-blue-500"></i>Periodo
                </h4>
                <div class="space-y-2">
                    <div>
                        <label class="text-[11px] text-slate-600 dark:text-slate-400">Data inicial</label>
                        <input type="date" wire:model.live="data_inicio"
                            class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    </div>
                    <div>
                        <label class="text-[11px] text-slate-600 dark:text-slate-400">Data final</label>
                        <input type="date" wire:model.live="data_fim"
                            class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" wire:click="applyDatePreset('all')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border {{ $data_inicio === '' && $data_fim === '' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200' }}">
                        Todo periodo
                    </button>
                    <button type="button" wire:click="applyDatePreset('today')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                        Hoje
                    </button>
                    <button type="button" wire:click="applyDatePreset('7d')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                        7 dias
                    </button>
                    <button type="button" wire:click="applyDatePreset('30d')"
                        class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                        30 dias
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 p-3">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center mb-3">
                    <i class="bi bi-sliders2 mr-2 text-violet-500"></i>Opcoes extras
                </h4>
                <div class="space-y-2">
                    <button type="button" wire:click="$set('sem_imagem', {{ $sem_imagem ? 'false' : 'true' }})"
                        class="w-full text-left px-3 py-2.5 rounded-xl border transition-colors {{ $sem_imagem ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-violet-400' }}">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Somente sem imagem personalizada</span>
                    </button>
                    <button type="button" wire:click="$set('semEstoque', {{ $semEstoque ? 'false' : 'true' }})"
                        class="w-full text-left px-3 py-2.5 rounded-xl border transition-colors {{ $semEstoque ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-rose-400' }}">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Somente produtos sem estoque</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:loading.flex wire:target="search,category,status_filtro,tipo,preco_min,preco_max,estoque,estoque_valor,data_inicio,data_fim,ordem,perPage,sem_imagem,semEstoque"
        class="items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <span class="inline-block w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
        Atualizando filtros...
    </div>
</div>
