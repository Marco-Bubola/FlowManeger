@push('styles')
<link rel="stylesheet" href="{{ asset('css/category-form.css') }}">
<style>
    /* Container fluido e altura vertical aumentada */
    .fluid-container {
        min-height: 100vh;
        height: auto;
        width: 100%;
        max-width: none;
        padding-left: 0;
        padding-right: 0;
    }

    .form-content {
        min-height: calc(100vh - 4rem);
        padding: 2rem 1rem;
    }

    .form-card {
        min-height: 300px;
        height: auto;
    }

    .form-section {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .form-section .space-y-6 {
        flex: 1;
        gap: 1.5rem;
        display: flex;
        flex-direction: column;
    }

    /* Ajustes para altura vertical */
    @media (min-width: 1024px) {
        .form-content {
            padding: 3rem 2rem;
        }

        .form-card {
            min-height: 400px;
        }

        .lg\:col-span-3 .form-card,
        .lg\:col-span-4 .form-card,
        .lg\:col-span-5 .form-card {
            height: 100%;
            min-height: 500px;
        }
    }

    @media (max-width: 768px) {
        .form-content {
            padding: 1.5rem 1rem;
        }

        .form-card {
            min-height: 250px;
        }
    }
</style>
@endpush

<div class="fluid-container bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 custom-scrollbar"    <!-- Header Super Compacto -->
        <!-- Header Fluido -->
    <div class="bg-white/85 backdrop-blur-sm shadow-sm border-b border-gray-200/50 dark:bg-gray-800/85 dark:border-gray-700/50 sticky top-0 z-20 glass-effect w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-18">
                <div class="flex items-center">
                    <a href="{{ route('categories.index') }}"
                       class="group text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 mr-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                        <svg class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div class="flex items-center">
                        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg mr-3 shadow-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Nova Categoria
                        </h1>
                    </div>
                </div>
                <!-- Botões de Ação -->
                <div class="flex space-x-3">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" form="category-form"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-md hover:shadow-lg">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">Criar Categoria</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <div class="spinner mr-2"></div>
                            Criando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário Container Fluido -->
    <div class="form-content w-full category-form-container">
        <form id="category-form" wire:submit.prevent="save" class="w-full">

            <!-- Layout Principal Fluido -->
            <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                <!-- Seção 1: Tipo e Preview (LG: 3 cols) -->
                <div class="lg:col-span-3 form-section">
                    <div class="space-y-6 h-full">

                    <!-- Tipo de Categoria -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden flex-1">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Tipo de Categoria
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach([
                                'product' => ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Produto', 'desc' => 'Produtos e itens', 'color' => 'blue'],
                                'transaction' => ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1', 'label' => 'Transação', 'desc' => 'Receitas e gastos', 'color' => 'green'],
                                'transfer' => ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'label' => 'Transferência', 'desc' => 'Entre contas', 'color' => 'purple']
                            ] as $typeKey => $typeData)
                                <label class="relative cursor-pointer group custom-radio">
                                    <input wire:model.live="type" type="radio" value="{{ $typeKey }}" class="sr-only peer">
                                    <div class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 peer-checked:border-{{ $typeData['color'] }}-500 peer-checked:bg-{{ $typeData['color'] }}-50 dark:peer-checked:bg-{{ $typeData['color'] }}-900/20 hover:border-{{ $typeData['color'] }}-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-{{ $typeData['color'] }}-100 dark:bg-{{ $typeData['color'] }}-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="h-5 w-5 text-{{ $typeData['color'] }}-600 dark:text-{{ $typeData['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeData['icon'] }}"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $typeData['label'] }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $typeData['desc'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <div class="px-6 pb-4">
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            </div>
                        @enderror
                    </div>

                    <!-- Preview da Categoria -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </h3>
                        </div>
                        <div class="p-6 category-preview">
                            <div class="flex items-center space-x-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-lg shadow-sm color-indicator"
                                     style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}cc)">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-medium text-gray-900 dark:text-white truncate">
                                        {{ $name ?: 'Nome da categoria' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ $desc_category ?: 'Descrição da categoria' }}
                                    </p>
                                </div>
                                @if($type)
                                    @php
                                        $badgeClasses = [
                                            'product' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'transaction' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'transfer' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                        ];
                                        $typeLabels = [
                                            'product' => 'Produto',
                                            'transaction' => 'Transação',
                                            'transfer' => 'Transferência'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badgeClasses[$type] ?? '' }}">
                                        {{ $typeLabels[$type] ?? $type }}
                                    </span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Seção 2: Informações Básicas e Aparência (LG: 4 cols) -->
                <div class="lg:col-span-4 form-section">
                    <div class="space-y-6 h-full">

                    <!-- Informações Básicas -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Informações Básicas
                            </h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Nome -->
                            <div class="enhanced-input">
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Nome da Categoria *
                                </label>
                                <input wire:model.live="name" type="text"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-sm"
                                       style="@error('name') border-color: #ef4444 !important; @enderror"
                                       placeholder="Digite o nome da categoria">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição e Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="enhanced-input">
                                    <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                        </svg>
                                        Descrição
                                    </label>
                                    <input wire:model.live="desc_category" type="text"
                                           class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-sm"
                                           placeholder="Descrição resumida da categoria">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status da Categoria
                                    </label>
                                    <div class="flex space-x-4 mt-3">
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="1" class="sr-only peer">
                                            <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Ativo</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="0" class="sr-only peer">
                                            <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Inativo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aparência -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                Aparência Visual
                            </h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Cor e Ícone -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                        </svg>
                                        Cor da Categoria
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <input wire:model.live="hexcolor_category" type="color"
                                               class="h-12 w-20 border-2 border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer">
                                        <input wire:model.live="hexcolor_category" type="text"
                                               class="flex-1 px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono"
                                               placeholder="#6366f1">
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Ícone
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white shadow-sm"
                                             style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}dd)">
                                            <i class="{{ $icone }}"></i>
                                        </div>
                                        <select wire:model.live="icone"
                                                class="flex-1 px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                            <optgroup label="💰 Financeiro">
                                                <option value="fas fa-dollar-sign">💵 Dólar</option>
                                                <option value="fas fa-credit-card">💳 Cartão</option>
                                                <option value="fas fa-coins">🪙 Moedas</option>
                                            </optgroup>
                                            <optgroup label="🔄 Transferências">
                                                <option value="fas fa-exchange-alt">🔄 Transferência</option>
                                                <option value="fas fa-arrow-right">➡️ Enviar</option>
                                                <option value="fas fa-mobile-alt">📱 PIX</option>
                                            </optgroup>
                                            <optgroup label="🏠 Gerais">
                                                <option value="fas fa-home">🏠 Casa</option>
                                                <option value="fas fa-car">🚗 Transporte</option>
                                                <option value="fas fa-utensils">🍽️ Alimentação</option>
                                                <option value="fas fa-tag">🏷️ Tag</option>
                                                <option value="fas fa-box">📦 Produtos</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Paleta de cores -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Cores Rápidas</label>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#84cc16'];
                                    @endphp
                                    @foreach($colors as $color)
                                        <button type="button"
                                                wire:click="$set('hexcolor_category', '{{ $color }}')"
                                                class="w-8 h-8 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform shadow-sm"
                                                style="background-color: {{ $color }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                            ] as $typeKey => $typeData)
                                <label class="relative cursor-pointer group custom-radio">
                                    <input wire:model.live="type" type="radio" value="{{ $typeKey }}" class="sr-only peer">
                                    <div class="p-2 rounded-lg border-2 border-gray-200 dark:border-gray-600 peer-checked:border-{{ $typeData['color'] }}-500 peer-checked:bg-{{ $typeData['color'] }}-50 dark:peer-checked:bg-{{ $typeData['color'] }}-900/20 hover:border-{{ $typeData['color'] }}-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-{{ $typeData['color'] }}-100 dark:bg-{{ $typeData['color'] }}-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="h-3 w-3 text-{{ $typeData['color'] }}-600 dark:text-{{ $typeData['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeData['icon'] }}"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-gray-900 dark:text-white text-xs">{{ $typeData['label'] }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $typeData['desc'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <div class="px-3 pb-2">
                                <p class="text-xs text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            </div>
                        @enderror
                    </div>

                    <!-- Preview Compacto -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-md rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-3 py-2">
                            <h3 class="text-sm font-bold text-white flex items-center">
                                <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </h3>
                        </div>
                        <div class="p-3 category-preview">
                            <div class="flex items-center space-x-2 p-2.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs shadow-sm color-indicator"
                                     style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}cc)">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                        {{ $name ?: 'Nome da categoria' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $desc_category ?: 'Descrição...' }}
                                    </p>
                                </div>
                                @if($type)
                                    @php
                                        $badgeClasses = [
                                            'product' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'transaction' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'transfer' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                        ];
                                        $typeLabels = [
                                            'product' => 'Produto',
                                            'transaction' => 'Transação',
                                            'transfer' => 'Transferência'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses[$type] ?? '' }}">
                                        {{ $typeLabels[$type] ?? $type }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção 2: Informações Básicas e Aparência (Mobile: 1 col, MD: 2 cols, LG: 4 cols) -->
                <div class="md:col-span-2 lg:col-span-4 space-y-3 sm:space-y-4">

                    <!-- Informações Básicas Compactas -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-md rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-3 py-2">
                            <h3 class="text-sm font-bold text-white flex items-center">
                                <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Informações
                            </h3>
                        </div>
                        <div class="p-3 space-y-3">
                            <!-- Nome -->
                            <div class="enhanced-input">
                                <label class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <svg class="h-2.5 w-2.5 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Nome *
                                </label>
                                <input wire:model.live="name" type="text"
                                       class="w-full px-2.5 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-xs"
                                       style="@error('name') border-color: #ef4444 !important; @enderror"
                                       placeholder="Nome da categoria">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição e Status na mesma linha -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="enhanced-input">
                                    <label class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="h-2.5 w-2.5 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                        </svg>
                                        Descrição
                                    </label>
                                    <input wire:model.live="desc_category" type="text"
                                           class="w-full px-2.5 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-xs"
                                           placeholder="Descrição resumida">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="h-2.5 w-2.5 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status
                                    </label>
                                    <div class="flex space-x-3">
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="1" class="sr-only peer">
                                            <div class="w-3 h-3 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-1 h-1 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-1.5 text-xs text-gray-700 dark:text-gray-300">Ativo</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input wire:model="is_active" type="radio" value="0" class="sr-only peer">
                                            <div class="w-3 h-3 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                                <div class="w-1 h-1 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                            </div>
                                            <span class="ml-1.5 text-xs text-gray-700 dark:text-gray-300">Inativo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aparência Compacta -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-md rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-3 py-2">
                            <h3 class="text-sm font-bold text-white flex items-center">
                                <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                Aparência
                            </h3>
                        </div>
                        <div class="p-3 space-y-3">
                            <!-- Cor e Ícone na mesma linha -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="h-2.5 w-2.5 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                        </svg>
                                        Cor
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input wire:model.live="hexcolor_category" type="color"
                                               class="h-8 w-12 border border-gray-200 dark:border-gray-600 rounded cursor-pointer">
                                        <input wire:model.live="hexcolor_category" type="text"
                                               class="flex-1 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono"
                                               placeholder="#6366f1">
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="h-2.5 w-2.5 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Ícone
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded flex items-center justify-center text-white shadow-sm"
                                             style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}dd)">
                                            <i class="{{ $icone }}"></i>
                                        </div>
                                        <select wire:model.live="icone"
                                                class="flex-1 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <optgroup label="💰 Financeiro">
                                                <option value="fas fa-dollar-sign">💵 Dólar</option>
                                                <option value="fas fa-credit-card">💳 Cartão</option>
                                                <option value="fas fa-coins">🪙 Moedas</option>
                                            </optgroup>
                                            <optgroup label="🔄 Transferências">
                                                <option value="fas fa-exchange-alt">🔄 Transferência</option>
                                                <option value="fas fa-arrow-right">➡️ Enviar</option>
                                                <option value="fas fa-mobile-alt">📱 PIX</option>
                                            </optgroup>
                                            <optgroup label="🏠 Gerais">
                                                <option value="fas fa-home">🏠 Casa</option>
                                                <option value="fas fa-car">🚗 Transporte</option>
                                                <option value="fas fa-utensils">🍽️ Alimentação</option>
                                                <option value="fas fa-tag">🏷️ Tag</option>
                                                <option value="fas fa-box">📦 Produtos</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Paleta de cores compacta -->
                            <div>
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#84cc16'];
                                    @endphp
                                    @foreach($colors as $color)
                                        <button type="button"
                                                wire:click="$set('hexcolor_category', '{{ $color }}')"
                                                class="w-5 h-5 rounded border border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform"
                                                style="background-color: {{ $color }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção 3: Configurações Avançadas e Transfer (Mobile: 1 col, MD: 2 cols, LG: 5 cols) -->
                <div class="md:col-span-2 lg:col-span-5 space-y-3 sm:space-y-4">

                    <!-- Configurações de Transferência (condicional) -->
                    @if($type === 'transfer')
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-md rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-3 py-2">
                            <h3 class="text-sm font-bold text-white flex items-center">
                                <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Transferência
                            </h3>
                        </div>
                        <div class="p-3 space-y-3">
                            <!-- Tipo de Transferência -->
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Tipo</label>
                                <div class="grid grid-cols-3 gap-1">
                                    @foreach(['pix' => 'PIX', 'ted' => 'TED', 'doc' => 'DOC'] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input wire:model="tipo" type="radio" value="{{ $value }}" class="sr-only peer">
                                            <div class="p-2 rounded border border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 text-center">
                                                <span class="text-xs font-medium">{{ $label }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Banco e Cliente -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Banco</label>
                                    <select wire:model="id_bank" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Selecione</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Cliente</label>
                                    <select wire:model="id_clients" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Selecione</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Configurações Avançadas Compactas -->
                    <div class="form-card bg-white/75 backdrop-blur-sm shadow-md rounded-xl border border-gray-200/50 dark:bg-gray-800/75 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-3 py-2">
                            <h3 class="text-sm font-bold text-white flex items-center">
                                <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Avançado
                            </h3>
                        </div>
                        <div class="p-3 space-y-3">
                            <!-- Categoria Pai e Limite -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Categoria Pai</label>
                                    <select wire:model="parent_id" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Nenhuma</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Limite (R$)</label>
                                    <input wire:model="limite_orcamento" type="number" step="0.01" min="0"
                                           class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="0,00">
                                </div>
                            </div>

                            <!-- Compartilhável -->
                            <div class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-700/50 rounded">
                                <input wire:model="compartilhavel" type="checkbox"
                                       class="w-3 h-3 text-green-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500">
                                <label class="text-xs text-gray-700 dark:text-gray-300">Categoria compartilhável</label>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Tags</label>
                                <input wire:model="tags" type="text"
                                       class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       placeholder="pix, transferencia, digital">
                            </div>

                            <!-- Descrição Detalhada e Observações -->
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Descrição Detalhada</label>
                                    <textarea wire:model="descricao_detalhada" rows="2"
                                              class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                                              placeholder="Descrição completa..."></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Observações</label>
                                    <textarea wire:model="description" rows="2"
                                              class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                                              placeholder="Observações adicionais..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação de entrada escalonada mais rápida
    const elements = document.querySelectorAll('.category-form-container .form-card');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(15px)';
        setTimeout(() => {
            element.style.transition = 'all 0.4s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Preview em tempo real com feedback visual
    let previewElement = document.querySelector('.category-preview');
    if (previewElement) {
        const observer = new MutationObserver(() => {
            previewElement.style.transform = 'scale(1.02)';
            setTimeout(() => {
                previewElement.style.transform = 'scale(1)';
            }, 200);
        });

        observer.observe(previewElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    // Smooth scroll para mobile quando necessário
    if (window.innerWidth < 768) {
        const form = document.getElementById('category-form');
        if (form) {
            form.addEventListener('submit', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    }
});

// Event listeners para Livewire
document.addEventListener('livewire:init', () => {
    Livewire.on('category-created', (event) => {
        // Animação de sucesso
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 80,
                spread: 60,
                origin: { y: 0.6 },
                colors: ['#6366f1', '#8b5cf6', '#06b6d4']
            });
        }
    });
});
</script>
@endpush
