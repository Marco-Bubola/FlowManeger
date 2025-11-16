@props([
    'category',
    'type' => 'product' // 'product' ou 'transaction'
])

<div wire:sortable.item="{{ $category->id_category }}"
     wire:key="{{ $type }}-{{ $category->id_category }}"
     class="group bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300 cursor-move overflow-hidden">

    <div class="p-4">
        <div class="flex items-center justify-between gap-3">
            <!-- Handle para arrastar + Ícone da Categoria -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Handle para arrastar -->
                <div wire:sortable.handle class="flex-shrink-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-grab active:cursor-grabbing">
                    <i class="fas fa-grip-vertical text-lg"></i>
                </div>

                <!-- Ícone da Categoria -->
                <div class="relative flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-md transform group-hover:scale-110 transition-transform duration-300"
                         style="background: linear-gradient(135deg, {{ $category->hexcolor_category ?? ($type === 'product' ? '#3B82F6' : '#10B981') }}, {{ $category->hexcolor_category ?? ($type === 'product' ? '#3B82F6' : '#10B981') }}dd)">
                        @if($category->icone)
                            <i class="{{ $category->icone }} text-xl"></i>
                        @else
                            <i class="fas fa-{{ $type === 'product' ? 'box' : 'exchange-alt' }} text-xl"></i>
                        @endif
                    </div>
                    <!-- Badge de Status -->
                    <div class="absolute -top-1 -right-1 w-5 h-5 {{ $category->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full flex items-center justify-center shadow-sm">
                        <i class="fas fa-{{ $category->is_active ? 'check' : 'times' }} text-white text-xs"></i>
                    </div>
                </div>

                <!-- Detalhes -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 text-base truncate">{{ $category->name }}</h4>
                        <!-- Botão de Favorita -->
                        <button wire:click="toggleFavorite({{ $category->id_category }})"
                                class="flex-shrink-0 p-1 rounded-full hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                            <i class="fas fa-star text-yellow-500 text-xs"></i>
                        </button>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm truncate">{{ $category->description ?? 'Categoria de ' . ($type === 'product' ? 'produto' : 'transação') }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium {{ $type === 'product' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200' }}">
                            <i class="fas fa-{{ $type === 'product' ? 'box' : 'exchange-alt' }} mr-1"></i>
                            {{ $type === 'product' ? 'Produto' : 'Transação' }}
                        </span>
                        @if($type === 'transaction' && isset($category->tipo))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium {{ $category->tipo === 'gasto' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' }}">
                            <i class="fas fa-{{ $category->tipo === 'gasto' ? 'arrow-down' : 'arrow-up' }} mr-1"></i>
                            {{ $category->tipo === 'gasto' ? 'Despesa' : 'Receita' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex items-center gap-1 flex-shrink-0">
                <!-- Botão Compartilhar -->
                <button wire:click="shareCategory({{ $category->id_category }})"
                        class="p-2 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                        title="Compartilhar categoria">
                    <i class="fas fa-share-alt text-sm"></i>
                </button>
                <!-- Botão Editar -->
                <a href="{{ route('categories.edit', $category->id_category) }}"
                   class="p-2 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                   title="Editar categoria">
                    <i class="fas fa-edit text-sm"></i>
                </a>
                <!-- Botão Deletar -->
                <button wire:click="confirmDelete({{ $category->id_category }})"
                        class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>
