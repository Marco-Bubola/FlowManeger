@push('styles')
<link rel="stylesheet" href="{{ asset('css/category-form.css') }}">
@endpush

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 custom-scrollbar">
    <!-- Header Compacto -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50 dark:bg-gray-800/80 dark:border-gray-700/50 sticky top-0 z-10 glass-effect">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center">
                    <a href="{{ route('categories.index') }}"
                       class="group text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 mr-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                        <svg class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div class="flex items-center">
                        <div class="p-1.5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg mr-2 shadow-lg color-indicator">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                                Nova Categoria
                            </h1>
                        </div>
                    </div>
                </div>
                <!-- Botões de Ação no Header -->
                <div class="flex space-x-3">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" form="category-form"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-md hover:shadow-lg">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">Criar Categoria</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <div class="spinner mr-1"></div>
                            Criando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário Layout em Grid Único -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 category-form-container">
        <form id="category-form" wire:submit.prevent="save">
            <!-- Layout Principal em Grid Responsivo -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Coluna 1: Tipo e Preview (3 colunas) -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Seleção de Tipo -->
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Tipo
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <label class="relative cursor-pointer group custom-radio">
                                <input wire:model.live="type" type="radio" value="product" class="sr-only peer">
                                <div class="p-3 rounded-lg border-2 border-gray-200 dark:border-gray-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-blue-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Produto</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Produtos e itens</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group custom-radio">
                                <input wire:model.live="type" type="radio" value="transaction" class="sr-only peer">
                                <div class="p-3 rounded-lg border-2 border-gray-200 dark:border-gray-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:border-green-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Transação</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Receitas e gastos</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group custom-radio">
                                <input wire:model.live="type" type="radio" value="transfer" class="sr-only peer">
                                <div class="p-3 rounded-lg border-2 border-gray-200 dark:border-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:border-purple-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Transferência</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Entre contas</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <div class="px-4 pb-3">
                                <p class="text-xs text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            </div>
                        @enderror
                    </div>

                    <!-- Preview da Categoria -->
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </h3>
                        </div>
                        <div class="p-4 category-preview">
                            <div class="flex items-center space-x-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-sm shadow-sm color-indicator"
                                     style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}cc)">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $name ?: 'Nome da categoria' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses[$type] ?? '' }}">
                                        {{ $typeLabels[$type] ?? $type }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna 2: Informações Básicas e Aparência (4 colunas) -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Informações Básicas -->
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Informações
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Nome -->
                            <div class="enhanced-input">
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-3 w-3 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Nome *
                                </label>
                                <input wire:model.live="name" type="text"
                                       class="w-full px-3 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-sm"
                                       style="@error('name') border-color: #ef4444 !important; @enderror"
                                       placeholder="Nome da categoria">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="enhanced-input">
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-3 w-3 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                    </svg>
                                    Descrição
                                </label>
                                <input wire:model.live="desc_category" type="text"
                                       class="w-full px-3 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 text-sm"
                                       placeholder="Descrição resumida">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-3 w-3 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Status
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center cursor-pointer group">
                                        <input wire:model="is_active" type="radio" value="1" class="sr-only peer">
                                        <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-all duration-200">
                                            <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ativo</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input wire:model="is_active" type="radio" value="0" class="sr-only peer">
                                        <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                            <div class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Inativo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aparência -->
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                Aparência
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Cor -->
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-3 w-3 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                    </svg>
                                    Cor
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input wire:model.live="hexcolor_category" type="color"
                                           class="h-10 w-16 border-2 border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer">
                                    <input wire:model.live="hexcolor_category" type="text"
                                           class="flex-1 px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono"
                                           placeholder="#6366f1">
                                </div>
                                <!-- Paleta de cores -->
                                <div class="mt-3">
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#84cc16'];
                                        @endphp
                                        @foreach($colors as $color)
                                            <button type="button"
                                                    wire:click="$set('hexcolor_category', '{{ $color }}')"
                                                    class="w-6 h-6 rounded border-2 border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform"
                                                    style="background-color: {{ $color }}">
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Ícone -->
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <svg class="h-3 w-3 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    Ícone
                                </label>
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-sm"
                                         style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}dd)">
                                        <i class="{{ $icone }}"></i>
                                    </div>
                                    <select wire:model.live="icone"
                                            class="flex-1 px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
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
                    </div>
                </div>

                <!-- Coluna 3: Configurações Específicas e Avançadas (5 colunas) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Configurações de Transferência (condicional) -->
                    @if($type === 'transfer')
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Transferência
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Tipo de Transferência -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Tipo</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="cursor-pointer">
                                        <input wire:model="tipo" type="radio" value="pix" class="sr-only peer">
                                        <div class="p-2 rounded border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 text-center">
                                            <span class="text-xs font-medium">PIX</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input wire:model="tipo" type="radio" value="ted" class="sr-only peer">
                                        <div class="p-2 rounded border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 text-center">
                                            <span class="text-xs font-medium">TED</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input wire:model="tipo" type="radio" value="doc" class="sr-only peer">
                                        <div class="p-2 rounded border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 text-center">
                                            <span class="text-xs font-medium">DOC</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Banco e Cliente para Transferência -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Banco</label>
                                    <select wire:model="id_bank" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Selecione</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Cliente</label>
                                    <select wire:model="id_clients" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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

                    <!-- Configurações Avançadas -->
                    <div class="form-card bg-white/70 backdrop-blur-sm shadow-lg rounded-xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Avançado
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Categoria Pai e Limite -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Categoria Pai</label>
                                    <select wire:model="parent_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Nenhuma</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Limite (R$)</label>
                                    <input wire:model="limite_orcamento" type="number" step="0.01" min="0"
                                           class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="0,00">
                                </div>
                            </div>

                            <!-- Compartilhável -->
                            <div class="flex items-center space-x-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <input wire:model="compartilhavel" type="checkbox"
                                       class="w-4 h-4 text-green-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Categoria compartilhável</label>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Tags</label>
                                <input wire:model="tags" type="text"
                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       placeholder="pix, transferencia, digital">
                            </div>

                            <!-- Descrição Detalhada -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Descrição Detalhada</label>
                                <textarea wire:model="descricao_detalhada" rows="3"
                                          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                                          placeholder="Descrição completa da categoria..."></textarea>
                            </div>

                            <!-- Observações -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Observações</label>
                                <textarea wire:model="description" rows="2"
                                          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                                          placeholder="Observações adicionais..."></textarea>
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
    // Animação de entrada dos elementos
    const elements = document.querySelectorAll('.category-form-container .form-card');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 150);
    });

    // Preview em tempo real
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
});

// Event listeners para Livewire
document.addEventListener('livewire:init', () => {
    Livewire.on('category-created', (event) => {
        // Animação de sucesso
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }
    });
});
</script>
@endpush
