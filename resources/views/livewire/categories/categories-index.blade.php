<div id="categories-root" x-data="{ catFilters: false }" class="categories-index-page mobile-393-base relative">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/categories-index-ultrawide.css') }}">

    <x-loading-overlay message="Carregando categorias..." />

    <div class="w-full ">

    @php
        $catTotal = $productCategories->count() + $transactionCategories->count();
        $catActive = $productCategories->where('is_active', 1)->count() + $transactionCategories->where('is_active', 1)->count();
    @endphp
    <x-page-header title="Categorias" icon="bi-tags" section="categorias" tabs-section="categorias" tabs-active="lista">
        <x-slot:meta>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-box-seam"></i>{{ $productCategories->count() }} de produtos</span>
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-arrow-left-right"></i>{{ $transactionCategories->count() }} de transações</span>
        </x-slot:meta>
        <x-slot:actions>
            <button type="button" wire:click="createTransactionCategory" title="Nova categoria de transação" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70"><i class="bi bi-arrow-left-right text-emerald-500"></i>De transação</button>
            <button type="button" wire:click="createProductCategory" title="Nova categoria de produto"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md shadow-indigo-500/25 transition"><i class="bi bi-plus-lg"></i>Categoria de produto</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-3 mb-4">
        <x-gestao-stat label="De produtos" :value="$productCategories->count()" icon="bi-box-seam" tone="indigo" :hint="round(($productCategories->count() / max($catTotal, 1)) * 100) . '% do total'" />
        <x-gestao-stat label="De transações" :value="$transactionCategories->count()" icon="bi-arrow-left-right" tone="emerald" :hint="round(($transactionCategories->count() / max($catTotal, 1)) * 100) . '% do total'" />
        <x-gestao-stat label="Ativas" :value="$catActive" icon="bi-check-circle" tone="amber" :hint="($catTotal - $catActive) . ' inativas'" />
    </div>

    @php $catFilterOn = $statusFilter !== '' || ! in_array($sortBy, ['name', '']); @endphp
    <x-list-toolbar placeholder="Buscar categoria pelo nome ou descrição">
        <x-toolbar.group>
            <x-toolbar.chip wire:click="setActiveTab('products')" :active="$activeTab === 'products'" icon="bi-box-seam">Produtos <span class="opacity-70">{{ $productCategories->count() }}</span></x-toolbar.chip>
            <x-toolbar.chip wire:click="setActiveTab('transactions')" :active="$activeTab === 'transactions'" icon="bi-arrow-left-right">Transações <span class="opacity-70">{{ $transactionCategories->count() }}</span></x-toolbar.chip>
            <x-toolbar.chip wire:click="setActiveTab('all')" :active="$activeTab === 'all'" icon="bi-list-ul">Todas <span class="opacity-70">{{ $catTotal }}</span></x-toolbar.chip>
            <x-toolbar.chip wire:click="setActiveTab('tips')" :active="$activeTab === 'tips'" icon="bi-lightbulb">Dicas</x-toolbar.chip>
        </x-toolbar.group>
        <x-toolbar.group>
            <x-toolbar.chip @click="catFilters = true" :active="$catFilterOn" icon="bi-funnel">Filtros</x-toolbar.chip>
        </x-toolbar.group>
    </x-list-toolbar>

    <!-- Filtros: mesma janela das outras listas -->
    @php $opt = fn ($on) => 'fm-fs-opt' . ($on ? ' is-on' : ''); @endphp
    <x-filter-sheet show="catFilters" title="Filtros de categorias" subtitle="Status e ordem" :active="$catFilterOn" target="statusFilter,sortBy,clearFilters">
        <div class="fm-fs-group">
            <h4><i class="bi bi-toggle-on text-emerald-500"></i>Status</h4>
            <div class="fm-fs-options">
                @foreach(['' => 'Todas', '1' => 'Ativas', '0' => 'Inativas'] as $val => $lbl)
                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')" class="{{ $opt($statusFilter === (string) $val) }}">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>
        <div class="fm-fs-group">
            <h4><i class="bi bi-arrow-down-up text-amber-500"></i>Ordenar por</h4>
            <div class="fm-fs-options">
                @foreach(['name' => 'Padrão', 'name_desc' => 'Nome Z-A', 'created_at' => 'Mais recentes', 'created_at_desc' => 'Mais antigas'] as $val => $lbl)
                    <button type="button" wire:click="$set('sortBy', '{{ $val }}')" class="{{ $opt($sortBy === $val) }}">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>
    </x-filter-sheet>

    <!-- Conteúdo das Abas: mesmo card em Produtos, Transações e Todas -->
    @if(in_array($activeTab, ['products', 'transactions', 'all']))
        @php
            $section = match ($activeTab) {
                'products' => ['title' => 'Categorias de produtos', 'subtitle' => 'Organize seus produtos por categoria', 'icon' => 'fas fa-box', 'list' => $paginatedProductCategories, 'page' => 'product_page', 'create' => 'createProductCategory', 'button' => 'Nova categoria de produto'],
                'transactions' => ['title' => 'Categorias de transações', 'subtitle' => 'Classifique receitas e despesas', 'icon' => 'fas fa-exchange-alt', 'list' => $paginatedTransactionCategories, 'page' => 'transaction_page', 'create' => 'createTransactionCategory', 'button' => 'Nova categoria de transação'],
                default => ['title' => 'Todas as categorias', 'subtitle' => 'Produtos e transações juntos', 'icon' => 'fas fa-list', 'list' => $categories, 'page' => 'page', 'create' => null, 'button' => null],
            };
        @endphp
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white/70 dark:bg-slate-900/50 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 dark:border-slate-800 px-4 sm:px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow">
                        <i class="{{ $section['icon'] }}"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $section['title'] }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $section['subtitle'] }} · {{ $section['list']->total() }} {{ $section['list']->total() === 1 ? 'categoria' : 'categorias' }}</p>
                    </div>
                </div>
                @if($section['create'])
                    <button type="button" wire:click="{{ $section['create'] }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-md transition">
                        <i class="fas fa-plus"></i>{{ $section['button'] }}
                    </button>
                @endif
            </div>

            @if($section['list']->count() > 0)
                <div class="p-4 sm:p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                        @foreach($section['list'] as $category)
                            <x-category-tile :category="$category" :usage="$usage[$category->id_category] ?? []" />
                        @endforeach
                    </div>
                    <x-infinite-feed :paginator="$section['list']" :page-name="$section['page']" label="categorias" class="!mb-0" />
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400"><i class="fas fa-folder-open text-2xl"></i></div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Nenhuma categoria encontrada</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $search || $statusFilter !== '' ? 'Nenhuma categoria com essa busca ou filtro. Tente outro termo ou limpe os filtros.' : 'Crie uma categoria para organizar melhor.' }}</p>
                    @if($section['create'])
                        <button type="button" wire:click="{{ $section['create'] }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-plus"></i>{{ $section['button'] }}</button>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="space-y-4">

        <!-- Aba de Dicas Inteligentes -->
        @if($activeTab === 'tips')
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 dark:from-orange-600 dark:to-red-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-white bg-opacity-20 rounded-xl p-2">
                        <i class="fas fa-lightbulb text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Dicas Inteligentes</h2>
                        <p class="text-orange-100 text-sm mt-1">Otimize o uso das suas categorias</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Dica 1 -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/30 rounded-xl p-5 border border-blue-200 dark:border-blue-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-palette text-white text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">Use Cores Consistentes</h3>
                        <p class="text-blue-700 dark:text-blue-300 text-sm">
                            Atribua cores específicas para cada tipo de categoria. Isso facilita a identificação visual rápida.
                        </p>
                    </div>

                    <!-- Dica 2 -->
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/30 rounded-xl p-5 border border-emerald-200 dark:border-emerald-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-file-alt text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-emerald-900 dark:text-emerald-100 mb-2">Descrições Claras</h4>
                        <p class="text-emerald-700 dark:text-emerald-300 text-sm">Adicione descrições detalhadas para ajudar na organização futura.</p>
                    </div>

                    <!-- Dica 3 -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/30 rounded-xl p-5 border border-purple-200 dark:border-purple-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-tags text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-purple-900 dark:text-purple-100 mb-2">Use Tags</h4>
                        <p class="text-purple-700 dark:text-purple-300 text-sm">Utilize tags para facilitar a busca e filtragem das categorias.</p>
                    </div>

                    <!-- Dica 4 -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/30 rounded-xl p-5 border border-yellow-200 dark:border-yellow-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-chart-bar text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-yellow-900 dark:text-yellow-100 mb-2">Organize por Frequência</h4>
                        <p class="text-yellow-700 dark:text-yellow-300 text-sm">Coloque as categorias mais usadas no topo da lista para acesso rápido.</p>
                    </div>

                    <!-- Dica 5 -->
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/30 rounded-xl p-5 border border-indigo-200 dark:border-indigo-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-sync-alt text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-indigo-900 dark:text-indigo-100 mb-2">Revise Regularmente</h4>
                        <p class="text-indigo-700 dark:text-indigo-300 text-sm">Faça revisões mensais para manter suas categorias atualizadas e relevantes.</p>
                    </div>

                    <!-- Dica 6 -->
                    <div class="bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-900/20 dark:to-pink-800/30 rounded-xl p-5 border border-pink-200 dark:border-pink-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-star text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-pink-900 dark:text-pink-100 mb-2">Marque Favoritas</h4>
                        <p class="text-pink-700 dark:text-pink-300 text-sm">Use o sistema de favoritas para destacar as categorias mais importantes.</p>
                    </div>
                </div>

                <!-- Seção de Estatísticas -->
                <div class="mt-8 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600 rounded-xl p-6">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-6 text-center">
                        <i class="fas fa-chart-line mr-2"></i> Estatísticas das suas Categorias
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $productCategories->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Produtos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $transactionCategories->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Transações</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $categories->where('is_active', 1)->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Ativas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $categories->where('is_active', 0)->count() }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Inativas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Modal de Confirmação de Exclusão -->
    @if($showDeleteModal)
        <x-delete-dialog confirm="deleteCategory" cancel="cancelDelete" title="Excluir categoria?" confirm-label="Excluir categoria" icon="bi-tag">
            A categoria <strong>{{ $deletingCategory->name ?? $deletingCategory->description ?? '' }}</strong> será apagada. Não dá para desfazer.
        </x-delete-dialog>
    @endif
    </div>

</div>
