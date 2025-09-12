
@push('styles')
<link rel="stylesheet" href="{{ asset('css/category-form.css') }}">
@endpush

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 custom-scrollbar">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50 dark:bg-gray-800/80 dark:border-gray-700/50 sticky top-0 z-10 glass-effect">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('categories.index') }}"
                       class="group text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                        <svg class="h-6 w-6 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div class="flex items-center">
                        <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl mr-3 shadow-lg color-indicator">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent typing-effect">
                                Nova Categoria
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Crie uma nova categoria para organizar suas transações</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20 category-form-container">
        <form wire:submit.prevent="save" class="space-y-8">

            <!-- Tipo de Categoria (Novo - Destaque especial) -->
            <div class="form-card bg-white/70 backdrop-blur-sm shadow-xl rounded-2xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden glass-effect">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-5">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Tipo de Categoria
                    </h3>
                    <p class="text-emerald-100 text-sm mt-1">Selecione o tipo principal da categoria</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Produto -->
                        <label class="relative cursor-pointer group custom-radio">
                            <input wire:model.live="type" type="radio" value="product" class="sr-only peer">
                            <div class="p-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-blue-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Produto</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Para categorizar produtos e itens</p>
                                </div>
                            </div>
                        </label>

                        <!-- Transação -->
                        <label class="relative cursor-pointer group custom-radio">
                            <input wire:model.live="type" type="radio" value="transaction" class="sr-only peer">
                            <div class="p-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:border-green-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Transação</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Para receitas, gastos e transferências</p>
                                </div>
                            </div>
                        </label>

                        <!-- Transferência -->
                        <label class="relative cursor-pointer group custom-radio">
                            <input wire:model.live="type" type="radio" value="transfer" class="sr-only peer">
                            <div class="p-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:border-purple-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Transferência</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Entre contas e carteiras</p>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-3 text-sm text-red-600 dark:text-red-400 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Informações Básicas -->
            <div class="form-card bg-white/70 backdrop-blur-sm shadow-xl rounded-2xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden glass-effect">
                <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-5">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informações Básicas
                    </h3>
                    <p class="text-indigo-100 text-sm mt-1">Dados principais da categoria</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Nome -->
                        <div class="lg:col-span-1 enhanced-input">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Nome da Categoria *
                            </label>
                            <input wire:model.live="name" type="text"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200"
                                   style="@error('name') border-color: #ef4444 !important; @enderror"
                                   placeholder="Ex: Transferência PIX, Alimentação, Transporte...">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div class="lg:col-span-1 enhanced-input">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Descrição Resumida
                            </label>
                            <input wire:model.live="desc_category" type="text"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200"
                                   placeholder="Breve descrição da categoria">
                        </div>

                        <!-- Status -->
                        <div class="lg:col-span-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Status da Categoria
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center cursor-pointer group">
                                    <input wire:model="is_active" type="radio" value="1" class="sr-only peer">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-all duration-200">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400">Ativo</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input wire:model="is_active" type="radio" value="0" class="sr-only peer">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-red-600 dark:group-hover:text-red-400">Inativo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aparência -->
            <div class="bg-white/70 backdrop-blur-sm shadow-xl rounded-2xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-5">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                        </svg>
                        Aparência e Visual
                    </h3>
                    <p class="text-purple-100 text-sm mt-1">Personalize a cor e ícone da categoria</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Cor -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                                </svg>
                                Cor da Categoria
                            </label>
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <input wire:model.live="hexcolor_category" type="color"
                                           class="h-14 w-20 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-700 transition-all duration-200 hover:scale-105">
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-3 h-3 rounded-full border-2 border-white shadow-lg"
                                         style="background-color: {{ $hexcolor_category }}"></div>
                                </div>
                                <div class="flex-1">
                                    <input wire:model.live="hexcolor_category" type="text"
                                           class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 font-mono text-sm"
                                           placeholder="#6366f1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formato hexadecimal (#rrggbb)</p>
                                </div>
                            </div>

                            <!-- Paleta de cores predefinidas -->
                            <div class="mt-4">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Cores sugeridas:</p>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $predefinedColors = [
                                            '#6366f1' => 'Indigo',
                                            '#8b5cf6' => 'Violeta',
                                            '#06b6d4' => 'Ciano',
                                            '#10b981' => 'Esmeralda',
                                            '#f59e0b' => 'Âmbar',
                                            '#ef4444' => 'Vermelho',
                                            '#ec4899' => 'Rosa',
                                            '#84cc16' => 'Lima',
                                            '#6b7280' => 'Cinza',
                                            '#1f2937' => 'Escuro'
                                        ];
                                    @endphp
                                    @foreach($predefinedColors as $color => $name)
                                        <button type="button"
                                                wire:click="$set('hexcolor_category', '{{ $color }}')"
                                                class="w-8 h-8 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-400 transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                style="background-color: {{ $color }}"
                                                title="{{ $name }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Ícone -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <svg class="h-4 w-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                                Ícone da Categoria
                            </label>
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg transition-all duration-200 hover:scale-105"
                                     style="background: linear-gradient(135deg, {{ $hexcolor_category }}, {{ $hexcolor_category }}dd)">
                                    <i class="{{ $icone }}"></i>
                                </div>
                                <div class="flex-1">
                                    <select wire:model.live="icone"
                                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                        <optgroup label="💰 Financeiro">
                                            <option value="fas fa-dollar-sign">💵 Dólar</option>
                                            <option value="fas fa-credit-card">💳 Cartão</option>
                                            <option value="fas fa-coins">🪙 Moedas</option>
                                            <option value="fas fa-money-bill-wave">💸 Dinheiro</option>
                                            <option value="fas fa-piggy-bank">🐷 Cofrinho</option>
                                            <option value="fas fa-chart-line">📈 Gráfico</option>
                                            <option value="fas fa-wallet">👛 Carteira</option>
                                        </optgroup>
                                        <optgroup label="🔄 Transferências">
                                            <option value="fas fa-exchange-alt">🔄 Transferência</option>
                                            <option value="fas fa-arrow-right">➡️ Enviar</option>
                                            <option value="fas fa-arrow-left">⬅️ Receber</option>
                                            <option value="fas fa-sync">🔄 Sincronizar</option>
                                            <option value="fas fa-share">📤 Compartilhar</option>
                                            <option value="fas fa-mobile-alt">📱 PIX</option>
                                        </optgroup>
                                        <optgroup label="🏠 Categorias Gerais">
                                            <option value="fas fa-home">🏠 Casa</option>
                                            <option value="fas fa-car">🚗 Transporte</option>
                                            <option value="fas fa-utensils">🍽️ Alimentação</option>
                                            <option value="fas fa-medkit">⚕️ Saúde</option>
                                            <option value="fas fa-graduation-cap">🎓 Educação</option>
                                            <option value="fas fa-gamepad">🎮 Entretenimento</option>
                                            <option value="fas fa-plane">✈️ Viagem</option>
                                            <option value="fas fa-gift">🎁 Presentes</option>
                                            <option value="fas fa-tools">🔧 Ferramentas</option>
                                            <option value="fas fa-shopping-cart">🛒 Compras</option>
                                            <option value="fas fa-tag">🏷️ Tag</option>
                                            <option value="fas fa-box">📦 Produtos</option>
                                        </optgroup>
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escolha um ícone que represente a categoria</p>
                                </div>
                            </div>

                            <!-- Preview da categoria -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 category-preview">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Preview em tempo real:</p>
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses[$type] ?? '' }}">
                                            {{ $typeLabels[$type] ?? $type }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configurações de Transferência (mostrar apenas para tipo transfer) -->
            @if($type === 'transfer')
            <div class="bg-white/70 backdrop-blur-sm shadow-xl rounded-2xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-5">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Configurações de Transferência
                    </h3>
                    <p class="text-orange-100 text-sm mt-1">Configurações específicas para transferências entre contas</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Tipo de Transferência -->
                        <div class="lg:col-span-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <svg class="h-4 w-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Tipo de Transferência
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative cursor-pointer group">
                                    <input wire:model="tipo" type="radio" value="pix" class="sr-only peer">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 hover:border-orange-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-2">
                                                <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">PIX</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Instantâneo</p>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer group">
                                    <input wire:model="tipo" type="radio" value="ted" class="sr-only peer">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 hover:border-orange-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-2">
                                                <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">TED</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Entre bancos</p>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer group">
                                    <input wire:model="tipo" type="radio" value="doc" class="sr-only peer">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 hover:border-orange-300 transition-all duration-200 bg-white dark:bg-gray-800">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-2">
                                                <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">DOC</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Mesmo banco</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Bancos de Origem e Destino -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Banco Padrão
                            </label>
                            <select wire:model="id_bank"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:border-orange-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                <option value="">Selecione um banco</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cliente Associado -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Cliente/Conta
                            </label>
                            <select wire:model="id_clients"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:border-orange-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                <option value="">Selecione um cliente</option>
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
            <div class="bg-white/70 backdrop-blur-sm shadow-xl rounded-2xl border border-gray-200/50 dark:bg-gray-800/70 dark:border-gray-700/50 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-5">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Configurações Avançadas
                    </h3>
                    <p class="text-green-100 text-sm mt-1">Opções adicionais de configuração</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Categoria Pai -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v0M8 5a2 2 0 002 2h4a2 2 0 002-2v0"></path>
                                </svg>
                                Categoria Pai
                            </label>
                            <select wire:model="parent_id"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:focus:border-green-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                <option value="">Nenhuma (categoria principal)</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Criar como subcategoria de outra categoria existente</p>
                        </div>

                        <!-- Limite de Orçamento -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Limite de Orçamento (R$)
                            </label>
                            <input wire:model="limite_orcamento" type="number" step="0.01" min="0"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:focus:border-green-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200"
                                   placeholder="0,00">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Limite máximo de gastos para esta categoria (opcional)</p>
                        </div>

                        <!-- Compartilhável -->
                        <div class="lg:col-span-2">
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <input wire:model="compartilhavel" type="checkbox"
                                       class="w-5 h-5 text-green-600 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500 dark:focus:ring-green-400 focus:ring-2">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                        Categoria Compartilhável
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Permite que outros usuários utilizem esta categoria
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="lg:col-span-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Tags de Identificação
                            </label>
                            <input wire:model="tags" type="text"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:focus:border-green-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200"
                                   placeholder="pix, transferencia, instantaneo, digital">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separe as tags com vírgula para facilitar a busca</p>
                        </div>

                        <!-- Descrição Detalhada -->
                        <div class="lg:col-span-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Descrição Detalhada
                            </label>
                            <textarea wire:model="descricao_detalhada" rows="4"
                                      class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:focus:border-green-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 resize-none"
                                      placeholder="Descrição completa da categoria, suas características e quando deve ser utilizada..."></textarea>
                        </div>

                        <!-- Observações -->
                        <div class="lg:col-span-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="h-4 w-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Observações Adicionais
                            </label>
                            <textarea wire:model="description" rows="3"
                                      class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:focus:border-green-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all duration-200 resize-none"
                                      placeholder="Observações, notas importantes ou instruções especiais..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="sticky bottom-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 p-6 rounded-t-2xl shadow-2xl glass-effect">
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 transition-all duration-200 font-semibold enhanced-input">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 gradient-btn enhanced-input">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animação de entrada dos elementos
    const elements = document.querySelectorAll('.category-form-container > *');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Efeito de digitação no placeholder
    function typeWriterEffect(element, text, speed = 100) {
        let i = 0;
        element.placeholder = '';
        const timer = setInterval(() => {
            if (i < text.length) {
                element.placeholder += text.charAt(i);
                i++;
            } else {
                clearInterval(timer);
            }
        }, speed);
    }

    // Aplicar efeito de digitação no campo nome
    const nameInput = document.querySelector('input[wire\\:model\\.live="name"]');
    if (nameInput) {
        setTimeout(() => {
            typeWriterEffect(nameInput, 'Ex: Transferência PIX, Alimentação, Transporte...', 50);
        }, 1000);
    }

    // Adicionar efeito de pulsação ao preview quando houver mudanças
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

    // Adicionar efeito de hover aos cards
    const cards = document.querySelectorAll('.form-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Validação em tempo real do campo cor
    const colorInput = document.querySelector('input[type="color"]');
    const textColorInput = document.querySelector('input[wire\\:model\\.live="hexcolor_category"][type="text"]');

    if (colorInput && textColorInput) {
        textColorInput.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                colorInput.value = value;
                this.style.borderColor = '#10b981'; // Verde para válido
            } else {
                this.style.borderColor = '#ef4444'; // Vermelho para inválido
            }
        });
    }

    // Feedback visual para submissão do formulário
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }

    // Salvar dados no localStorage para recuperação
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        // Carregar dados salvos
        const savedValue = localStorage.getItem(`category_form_${input.name || input.id}`);
        if (savedValue && !input.value) {
            input.value = savedValue;
        }

        // Salvar dados ao digitar
        input.addEventListener('input', function() {
            localStorage.setItem(`category_form_${this.name || this.id}`, this.value);
        });
    });

    // Limpar localStorage ao enviar o formulário
    form.addEventListener('submit', function() {
        inputs.forEach(input => {
            localStorage.removeItem(`category_form_${input.name || input.id}`);
        });
    });
});

// Event listeners para Livewire
document.addEventListener('livewire:init', () => {
    Livewire.on('category-created', (event) => {
        // Adicionar confetti ou animação de sucesso
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }

        // Limpar localStorage
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('category_form_')) {
                localStorage.removeItem(key);
            }
        });
    });
});
</script>
@endpush

