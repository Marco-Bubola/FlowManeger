@props([
    'category',
    'usage' => [],
])

@php
    // Card único das categorias (mesmo visual nas abas Produtos, Transações e Todas).
    $isProduct = $category->type === 'product';
    $color = $category->hexcolor_category ?: ($isProduct ? '#6366f1' : '#10b981');
    $icon = $category->icone ?: ($isProduct ? 'fas fa-box' : 'fas fa-exchange-alt');
    $summary = $category->desc_category ?: ($category->description ?: ($isProduct ? 'Categoria de produtos' : 'Categoria de lançamentos'));
    $products = (int) ($usage['products'] ?? 0);
    $entries = (int) ($usage['entries'] ?? 0);
    $month = (float) ($usage['month'] ?? 0);
    $inUse = $products + $entries;
    $limit = (float) ($category->limite_orcamento ?? 0);
    $percent = $limit > 0 ? min(100, round($month / $limit * 100)) : null;
    $kind = $isProduct
        ? ['label' => 'Produtos', 'icon' => 'fas fa-box', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300']
        : match ($category->tipo) {
            'receita' => ['label' => 'Receita', 'icon' => 'fas fa-arrow-up', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
            'gasto' => ['label' => 'Despesa', 'icon' => 'fas fa-arrow-down', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
            default => ['label' => 'Receita e despesa', 'icon' => 'fas fa-exchange-alt', 'class' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
        };
@endphp

<div wire:key="category-tile-{{ $category->id_category }}"
     class="category-tile group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900/80 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 {{ $category->is_active ? '' : 'opacity-75' }}">
    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}99)"></div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white shadow-md group-hover:scale-105 transition-transform"
                 style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc)">
                <x-category-icon :icon="$icon" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $category->name }}</h3>
                    @if($category->is_active)
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ativa</span>
                    @else
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Inativa</span>
                    @endif
                </div>
                <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $summary }}</p>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $kind['class'] }}"><i class="{{ $kind['icon'] }} text-[9px]"></i>{{ $kind['label'] }}</span>
            @if($isProduct)
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-slate-600 dark:text-slate-300"><i class="fas fa-cubes text-[9px]"></i>{{ $products }} {{ $products === 1 ? 'produto' : 'produtos' }}</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-slate-600 dark:text-slate-300"><i class="fas fa-list text-[9px]"></i>{{ $entries }} {{ $entries === 1 ? 'lançamento' : 'lançamentos' }}</span>
            @endif
        </div>

        @unless($isProduct)
            <div class="mt-3 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/60 px-3 py-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-500 dark:text-slate-400">Este mês</span>
                    <span class="font-bold text-slate-900 dark:text-white">R$ {{ number_format($month, 2, ',', '.') }}</span>
                </div>
                @if($percent !== null)
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $percent }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $percent }}% do limite de R$ {{ number_format($limit, 2, ',', '.') }}</p>
                @endif
            </div>
        @endunless

        <div class="mt-auto pt-4 flex items-center gap-2">
            <a href="{{ route('categories.edit', $category->id_category) }}"
               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition">
                <i class="fas fa-pen"></i>Editar
            </a>
            <button type="button" wire:click="toggleActive({{ $category->id_category }})"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 transition"
                    title="{{ $category->is_active ? 'Desativar' : 'Ativar' }}">
                <i class="fas {{ $category->is_active ? 'fa-toggle-on text-emerald-500' : 'fa-toggle-off' }}"></i>
            </button>
            @if($inUse === 0)
                <button type="button" wire:click="confirmDelete({{ $category->id_category }})"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 transition" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            @else
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 opacity-40 cursor-not-allowed" title="Em uso: não pode ser excluída (desative se não quiser mais usar)">
                    <i class="fas fa-trash"></i>
                </span>
            @endif
        </div>
    </div>
</div>
