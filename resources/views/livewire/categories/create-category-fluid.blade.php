<div class="w-full">

    <!-- Header Moderno -->
    <div class="relative overflow-hidden bg-gradient-to-r from-white/80 via-indigo-50/90 to-purple-50/80 dark:from-slate-800/90 dark:via-indigo-900/30 dark:to-purple-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl mb-4">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5"></div>
        <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-indigo-400/20 via-purple-400/20 to-pink-400/20 rounded-full transform translate-x-12 -translate-y-12"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-400/10 via-purple-400/10 to-pink-400/10 rounded-full transform -translate-x-8 translate-y-8"></div>

        <div class="relative px-6 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-md shadow-indigo-400/20">
                        <i class="fas fa-plus text-white text-2xl"></i>
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-white/15 to-transparent opacity-40"></div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-1">
                            <a href="{{ route('categories.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <i class="fas fa-list mr-1"></i>Categorias
                            </a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Nova Categoria</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-slate-100">
                            Nova Categoria
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Crie e configure uma nova categoria para organizar seus produtos, transações ou transferências</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg transition-all duration-200 border border-slate-200 dark:border-slate-600 font-medium shadow-sm">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" form="category-form"
                            class="inline-flex items-center px-5 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg transition-all duration-200 font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-check mr-2"></i>
                        <span wire:loading.remove wire:target="save">Criar Categoria</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Criando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário Principal -->
    <form id="category-form" wire:submit.prevent="save">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <!-- Coluna 1: Tipo e Preview -->
            <div class="space-y-4">

                <!-- Tipo de Categoria -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 px-6 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-layer-group mr-2"></i>
                            Tipo de Categoria
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                            @foreach([
                                'product' => ['icon' => 'fa-box', 'label' => 'Produto', 'desc' => 'Produtos e itens', 'color' => 'blue'],
                                'transaction' => ['icon' => 'fa-exchange-alt', 'label' => 'Transação', 'desc' => 'Receitas e despesas', 'color' => 'emerald'],
                                'transfer' => ['icon' => 'fa-arrows-alt-h', 'label' => 'Transferência', 'desc' => 'Entre contas', 'color' => 'purple']
                            ] as $typeKey => $typeData)
                                <label class="relative cursor-pointer group block">
                                    <input wire:model.live="type" type="radio" value="{{ $typeKey }}" class="sr-only peer">
                                    <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-{{ $typeData['color'] }}-500 peer-checked:bg-{{ $typeData['color'] }}-50 dark:peer-checked:bg-{{ $typeData['color'] }}-900/20 hover:border-{{ $typeData['color'] }}-300 dark:hover:border-{{ $typeData['color'] }}-600 transition-all duration-200 bg-white dark:bg-slate-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-{{ $typeData['color'] }}-100 dark:bg-{{ $typeData['color'] }}-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <i class="fas {{ $typeData['icon'] }} text-{{ $typeData['color'] }}-600 dark:text-{{ $typeData['color'] }}-400 text-xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-slate-900 dark:text-slate-100 text-base">{{ $typeData['label'] }}</h4>
                                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $typeData['desc'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                            @error('type')
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center mt-2">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                <!-- Preview -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-600 to-slate-700 dark:from-slate-700 dark:to-slate-800 px-6 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-eye mr-2"></i>
                            Preview
                        </h3>
                    </div>
                    <div class="p-4">
                            <div class="flex items-center gap-4 p-5 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-2xl shadow-md"
                                     style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}cc)">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">
                                        {{ $name ?: 'Nome da categoria' }}
                                    </p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 truncate">
                                        {{ $desc_category ?: 'Descrição da categoria' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>

            <!-- Coluna 2: Informações Básicas e Aparência -->
            <div class="space-y-4">

                    <!-- Informações Básicas -->
                    <div class="form-card bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-5 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Informações Básicas
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Nome -->
                            <div class="enhanced-input">
                                <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Nome da Categoria *
                                </label>
                                <input wire:model.live="name" type="text"
                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200"
                                       style="@error('name') border-color: #ef4444 !important; @enderror"
                                       placeholder="Digite o nome da categoria">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição e Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="enhanced-input">
                                    <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                        </svg>
                                        Descrição
                                    </label>
                                    <input wire:model.live="desc_category" type="text"
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200"
                                           placeholder="Descrição resumida da categoria">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status da Categoria
                                    </label>
                                    <div class="flex space-x-4 mt-2">
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="1" class="sr-only peer">
                                            <div class="w-4 h-4 border-2 border-slate-300 dark:border-slate-600 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">Ativo</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="0" class="sr-only peer">
                                            <div class="w-4 h-4 border-2 border-slate-300 dark:border-slate-600 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">Inativo</span>
                                        </label>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Aparência -->
                    <div class="form-card bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex-1">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-5 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                Aparência Visual
                            </h3>
                        </div>
                        <div class="p-4 space-y-4 flex-1">
                            @php
                                // Ler o CSS de ícones e extrair classes (.icons8-...)
                                $cssPath = public_path('assets/css/icon-category.css');
                                $availableIcons = [];
                                if (file_exists($cssPath)) {
                                    $css = file_get_contents($cssPath);
                                    if (preg_match_all('/\.([a-zA-Z0-9\-_]+)\s*\{/', $css, $m)) {
                                        $availableIcons = array_values(array_unique($m[1]));
                                    }
                                }

                                // Buscar categorias já salvas e detectar ícones faltantes
                                $missingIcons = [];
                                try {
                                    $cats = \App\Models\Category::select('name','icone')->get();
                                    foreach ($cats as $c) {
                                        $iconClass = trim($c->icone ?? '');
                                        // extrair última classe se houver múltiplas (ex: 'fas fa-box')
                                        $parts = preg_split('/\s+/', $iconClass);
                                        $check = end($parts) ?: $iconClass;
                                        if ($check && !in_array($check, $availableIcons) && !in_array($iconClass, $availableIcons)) {
                                            $missingIcons[] = ['category' => $c->name, 'icon' => $iconClass];
                                        }
                                    }
                                } catch (\Throwable $e) {
                                    // silêncio — em dev local pode não haver DB
                                }

                                // Id para input hidden que sincroniza com Livewire
                                $iconeInputId = 'icone_hidden_'.\Illuminate\Support\Str::random(8);

                                // Gerar arquivo JSON com ícones faltantes para revisão (sobrescreve)
                                if (!empty($missingIcons)) {
                                    $outPath = public_path('assets/icons-missing.json');
                                    @file_put_contents($outPath, json_encode($missingIcons, JSON_PRETTY_PRINT));
                                }
                            @endphp
                            <!-- Cor e Ícone -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                        </svg>
                                        Cor da Categoria
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <input wire:model.live="hexcolor_category" type="color"
                                               class="h-10 w-16 border border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer">
                                        <input wire:model.live="hexcolor_category" type="text"
                                               class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-mono"
                                               placeholder="#6366f1">
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Ícone
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg shadow-sm category-preview"
                                            style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}dd)">
                                            {{-- Mostrar ícone selecionado (pode ser fontawesome ou classe de CSS customizada) --}}
                                            @if(str_contains($icone ?? '', ' '))
                                                @php $last = last(explode(' ', $icone)); @endphp
                                                <i class="{{ $last }}"></i>
                                            @else
                                                <i class="{{ $icone }}"></i>
                                            @endif
                                        </div>

                                        <input id="{{ $iconeInputId }}" type="hidden" wire:model.live="icone">

                                        <div class="flex-1" x-data="iconDropdown(@json($availableIcons), '{{ $icone ?? '' }}', '{{ $iconeInputId }}')">
                                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-medium transition-all duration-200 hover:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                                <span class="flex items-center gap-3">
                                                    <span class="inline-block w-6 h-6">
                                                        <span :class="selected" class="inline-block w-6 h-6"></span>
                                                    </span>
                                                    <span x-text="selected || 'Selecione um ícone'">Selecione um ícone</span>
                                                </span>
                                                <i class="bi bi-chevron-down text-slate-400" :class="{'rotate-180': open}"></i>
                                            </button>

                                            <div x-show="open" x-transition @click.away="open = false" class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl shadow-2xl max-h-72 overflow-y-auto">
                                                <div class="p-2 border-b border-slate-200 dark:border-slate-600">
                                                    <input type="text" x-model="search" placeholder="Buscar ícone..." class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-slate-800 border border-slate-300 rounded-lg focus:outline-none">
                                                </div>
                                                <div class="p-2 grid grid-cols-2 gap-2">
                                                    <template x-for="icon in filtered" :key="icon">
                                                        <button type="button" @click="select(icon)" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 text-sm text-left">
                                                            <span class="inline-block w-8 h-8 rounded-md overflow-hidden flex items-center justify-center bg-white dark:bg-slate-800">
                                                                <span :class="icon" class="inline-block"></span>
                                                            </span>
                                                            <span x-text="icon" class="truncate"></span>
                                                        </button>
                                                    </template>
                                                    <template x-if="filtered.length === 0">
                                                        <div class="text-sm text-gray-500 p-3">Nenhum ícone encontrado</div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paleta de cores -->
                            <div>
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Cores Rápidas</label>
                                <div class="flex flex-wrap gap-2">
                                        @php
                                            $colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#84cc16'];
                                        @endphp
                                    @foreach($colors as $color)
                                        <button type="button"
                                                wire:click="$set('hexcolor_category', '{{ $color }}')"
                                                class="w-8 h-8 rounded-lg border border-slate-300 dark:border-slate-600 hover:scale-110 transition-transform shadow-sm"
                                                style="background-color: {{ $color }}">
                                        </button>
                                    @endforeach
                                    </div>
                                </div>
                    </div>
                </div>
            </div>

            <!-- Coluna 3: Configurações de Transferência e Avançadas -->
            <div class="space-y-4">                    <!-- Configurações de Transferência (condicional) -->
                    @if($type === 'transfer')
                    <div class="form-card bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-5 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Configurações de Transferência
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Tipo de Transferência -->
                            <div>
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Tipo de Transferência</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach(['pix' => 'PIX', 'ted' => 'TED', 'doc' => 'DOC'] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input wire:model="tipo" type="radio" value="{{ $value }}" class="sr-only peer">
                                            <div class="p-3 rounded-lg border border-slate-300 dark:border-slate-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 text-center transition-all duration-200">
                                                <span class="text-sm font-medium">{{ $label }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Banco e Cliente -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Banco</label>
                                    <select wire:model="id_bank" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                        <option value="">Selecione um banco</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Cliente</label>
                                    <select wire:model="id_clients" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Configurações Avançadas -->
                    <div class="form-card bg-white dark:bg-slate-800 shadow-lg rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex-1">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Configurações Avançadas
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Categoria Pai e Limite -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Categoria Pai</label>
                                    <select wire:model="parent_id" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                        <option value="">Nenhuma categoria pai</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Limite de Orçamento (R$)</label>
                                    <input wire:model="limite_orcamento" type="number" step="0.01" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                           placeholder="0,00">
                                </div>
                            </div>

                            <!-- Compartilhável -->
                            <div class="flex items-center space-x-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                                <input wire:model="compartilhavel" type="checkbox"
                                       class="w-4 h-4 text-green-600 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500">
                                <label class="text-sm text-slate-700 dark:text-slate-300">Categoria compartilhável com outros usuários</label>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Tags (separadas por vírgula)</label>
                                <input wire:model="tags" type="text"
                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="pix, transferencia, digital, pagamento">
                            </div>

                            <!-- Descrição Detalhada e Observações -->
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Descrição Detalhada</label>
                                    <textarea wire:model="descricao_detalhada" rows="3"
                                              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white resize-none"
                                              placeholder="Descrição completa da categoria e suas funcionalidades..."></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Observações Adicionais</label>
                                    <textarea wire:model="description" rows="3"
                                              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white resize-none"
                                              placeholder="Observações e notas adicionais sobre a categoria..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação de entrada escalonada
    const elements = document.querySelectorAll('.category-form-container .form-card');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 150);
    });

    // Preview em tempo real com feedback visual
    let previewElement = document.querySelector('.category-preview');
    if (previewElement) {
        const observer = new MutationObserver(() => {
            previewElement.style.transform = 'scale(1.02)';
            setTimeout(() => {
                previewElement.style.transform = 'scale(1)';
            }, 300);
        });

        observer.observe(previewElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }
});

// Event listeners para Livewire
document.addEventListener('livewire:init', () => {
    Livewire.on('category-created', (event) => {
        // Animação de sucesso
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 120,
                spread: 80,
                origin: { y: 0.6 },
                colors: ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981']
            });
        }
    });
});
</script>
@endpush
