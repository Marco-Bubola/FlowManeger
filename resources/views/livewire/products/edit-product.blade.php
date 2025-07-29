<div x-data="{ currentStep: 1 }">

    <!-- Header com Steppers -->
    <div class="">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white dark:bg-neutral-800 hover:bg-orange-50 dark:hover:bg-orange-900/30 transition-all duration-200 shadow-sm border border-orange-200 dark:border-orange-700">
                        <i class="bi bi-arrow-left text-xl text-orange-600 dark:text-orange-400"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl mr-4 shadow-lg">
                                <i class="bi bi-pencil-square text-white text-xl"></i>
                            </div>
                            Editar Produto
                        </h1>
                        <p class="text-lg text-neutral-600 dark:text-neutral-400 mt-1">Altere as informações do produto de forma simples e organizada</p>
                    </div>
                </div>

            <!-- Steppers Melhorados -->
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-12">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                             :class="currentStep === 1 ? 'bg-gradient-to-br from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/30' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400'">
                            <i class="bi bi-info-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="flex items-center">
                                <p class="text-lg font-bold transition-colors duration-300"
                                   :class="currentStep === 1 ? 'text-neutral-800 dark:text-neutral-100' : 'text-neutral-600 dark:text-neutral-400'">Informações do Produto</p>
                                <i class="bi bi-patch-check-fill text-green-500 ml-2 text-lg"></i>
                            </div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Nome, preços, estoque e categoria</p>
                        </div>
                    </div>

                    <!-- Connector -->
                    <div class="w-32 h-1 rounded-full transition-all duration-300"
                         :class="currentStep >= 2 ? 'bg-gradient-to-r from-orange-500 to-red-500' : 'bg-neutral-300 dark:bg-neutral-600'"></div>

                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300"
                             :class="currentStep === 2 ? 'bg-gradient-to-br from-red-500 to-pink-500 text-white shadow-lg shadow-red-500/30' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400'">
                            <i class="bi bi-image text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="flex items-center">
                                <p class="text-lg font-bold transition-colors duration-300"
                                   :class="currentStep === 2 ? 'text-neutral-800 dark:text-neutral-100' : 'text-neutral-600 dark:text-neutral-400'">Imagem do Produto</p>
                                @if($image || $product->image)
                                    <i class="bi bi-patch-check-fill text-green-500 ml-2 text-lg"></i>
                                @endif
                            </div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Upload da foto do produto</p>
                        </div>
                    </div>
                </div>
            </div>
                <!-- Badge do produto -->
                <div class="hidden sm:block">
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                        <i class="bi bi-box mr-2"></i>
                        #{{ $product->product_code }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class=" overflow-y-auto">
        <form wire:submit="update" class="w-full h-full">
            <div class="px-6 py-6 space-y-6 h-full flex flex-col">

                <!-- Step 1: Informações do Produto -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="flex-1 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Nome do Produto -->
                        <div class="space-y-3">
                            <label for="name" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-tag-fill text-blue-600 dark:text-blue-400"></i>
                                </div>
                                Nome do Produto *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <i class="bi bi-box-seam text-neutral-400"></i>
                                </div>
                                <input type="text"
                                    wire:model.live="name"
                                    id="name"
                                    value="{{ $name }}"
                                    class="w-full pl-12 pr-12 py-3 border-2 {{ $errors->has('name') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-blue-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200 placeholder-neutral-400"
                                    placeholder="Ex: Notebook Dell Inspiron 15">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                    @if($name && !$errors->has('name'))
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                    @endif
                                </div>
                            </div>
                            @error('name')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Código do Produto -->
                        <div class="space-y-3">
                            <label for="product_code" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-upc-scan text-purple-600 dark:text-purple-400"></i>
                                </div>
                                Código do Produto *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <i class="bi bi-hash text-neutral-400"></i>
                                </div>
                                <input type="text"
                                    wire:model.live="product_code"
                                    id="product_code"
                                    value="{{ $product_code }}"
                                    class="w-full pl-12 pr-12 py-3 border-2 {{ $errors->has('product_code') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-purple-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500/20 focus:outline-none transition-all duration-200 placeholder-neutral-400"
                                    placeholder="Ex: NB-DELL-001">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                    @if($product_code && !$errors->has('product_code'))
                                        <i class="bi bi-check-circle-fill text-green-500"></i>
                                    @endif
                                </div>
                            </div>
                            @error('product_code')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Descrição -->
                        <div class="space-y-3">
                            <label for="description" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-card-text text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                Descrição do Produto
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-4">
                                    <i class="bi bi-text-paragraph text-neutral-400"></i>
                                </div>
                                <textarea wire:model.live="description"
                                    id="description"
                                    rows="3"
                                    class="w-full pl-12 pr-4 py-3 border-2 {{ $errors->has('description') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-indigo-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all duration-200 resize-none placeholder-neutral-400"
                                    placeholder="Descreva as principais características do produto...">{{ $description }}</textarea>
                            </div>
                            @error('description')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Categoria -->
                        <div class="space-y-3">
                            <label for="category_id" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-tags-fill text-purple-600 dark:text-purple-400"></i>
                                </div>
                                Selecione a Categoria *
                            </label>

                            <!-- Dropdown Customizado para Categorias -->
                            <div class="relative" x-data="{
                                open: false,
                                selectedCategoryName: '{{ $selectedCategoryName ?? 'Escolha uma categoria...' }}',
                                selectedCategoryIcon: '{{ $selectedCategoryIcon ?? 'bi-grid-3x3-gap-fill' }}',
                                selectCategory(category) {
                                    this.selectedCategoryName = category.name;
                                    this.selectedCategoryIcon = category.icon;
                                    this.open = false;
                                    $wire.set('category_id', category.id);
                                }
                            }">
                                <button type="button"
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between pl-12 pr-4 py-3 border-2 {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-purple-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-purple-500/20 focus:outline-none transition-all duration-200">
                                    <div class="flex items-center">
                                        <div class="absolute left-4">
                                            <i :class="selectedCategoryIcon" class="text-neutral-400"></i>
                                        </div>
                                        <span x-text="selectedCategoryName" class="text-left"></span>
                                    </div>
                                    <i class="bi bi-chevron-down text-neutral-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="open = false"
                                     class="absolute z-50 w-full mt-2 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">

                                    @foreach($categories as $category)
                                    <button type="button"
                                            @click="selectCategory({ id: {{ $category->id_category }}, name: '{{ $category->name }}', icon: '{{ $this->getCategoryIcon($category->icone) }}' })"
                                            class="w-full flex items-center px-4 py-3 text-left hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors duration-150 border-b border-neutral-100 dark:border-neutral-600 last:border-b-0">
                                        <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg mr-3">
                                            <i class="{{ $this->getCategoryIcon($category->icone) }} text-purple-600 dark:text-purple-400"></i>
                                        </div>
                                        <span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $category->name }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('category_id')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <!-- Preço de Custo -->
                        <div class="space-y-3">
                            <label for="price" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-tag-fill text-orange-600 dark:text-orange-400"></i>
                                </div>
                                Preço de Custo *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <span class="text-neutral-500 dark:text-neutral-400 font-bold">R$</span>
                                </div>
                                <input type="text"
                                    wire:model.live="price"
                                    id="price"
                                    value="{{ $price }}"
                                    class="w-full pl-12 pr-4 py-3 border-2 {{ $errors->has('price') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-orange-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition-all duration-200 placeholder-neutral-400"
                                    placeholder="0,00"
                                    maxlength="12">
                            </div>
                            @error('price')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Preço de Venda -->
                        <div class="space-y-3">
                            <label for="price_sale" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-currency-dollar text-green-600 dark:text-green-400"></i>
                                </div>
                                Preço de Venda *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                                    <span class="text-neutral-500 dark:text-neutral-400 font-bold">R$</span>
                                </div>
                                <input type="text"
                                    wire:model.live="price_sale"
                                    id="price_sale"
                                    value="{{ $price_sale }}"
                                    class="w-full pl-12 pr-4 py-3 border-2 {{ $errors->has('price_sale') ? 'border-red-400 focus:border-red-500' : 'border-neutral-300 dark:border-neutral-600 focus:border-green-500' }} rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-200 placeholder-neutral-400"
                                    placeholder="0,00">
                            </div>
                            @error('price_sale')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Quantidade em Estoque -->
                        <div class="space-y-3">
                            <label for="stock_quantity" class="flex items-center text-base font-bold text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center justify-center w-8 h-8 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-stack text-cyan-600 dark:text-cyan-400"></i>
                                </div>
                                Quantidade em Estoque *
                            </label>
                            <div class="relative flex items-center max-w-[10rem]">
                                <button type="button"
                                        onclick="decrementQuantityEdit()"
                                        class="bg-neutral-100 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:border-neutral-600 hover:bg-neutral-200 border-2 {{ $errors->has('stock_quantity') ? 'border-red-400' : 'border-neutral-300 dark:border-neutral-600' }} rounded-s-lg p-3 h-12 focus:ring-cyan-100 dark:focus:ring-cyan-700 focus:ring-2 focus:outline-none transition-all duration-200">
                                    <svg class="w-3 h-3 text-neutral-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                                    </svg>
                                </button>
                                <input type="number"
                                       wire:model.live="stock_quantity"
                                       id="stock_quantity"
                                       min="0"
                                       value="{{ $stock_quantity }}"
                                       class="bg-white dark:bg-neutral-700 border-x-0 {{ $errors->has('stock_quantity') ? 'border-red-400' : 'border-neutral-300 dark:border-neutral-600' }} h-12 text-center text-neutral-900 dark:text-neutral-100 text-base focus:ring-cyan-500 focus:border-cyan-500 block w-full transition-all duration-200"
                                       placeholder="0">
                                <button type="button"
                                        onclick="incrementQuantityEdit()"
                                        class="bg-neutral-100 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:border-neutral-600 hover:bg-neutral-200 border-2 {{ $errors->has('stock_quantity') ? 'border-red-400' : 'border-neutral-300 dark:border-neutral-600' }} rounded-e-lg p-3 h-12 focus:ring-cyan-100 dark:focus:ring-cyan-700 focus:ring-2 focus:outline-none transition-all duration-200">
                                    <svg class="w-3 h-3 text-neutral-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                                    </svg>
                                </button>
                            </div>
                            @error('stock_quantity')
                            <div class="flex items-center mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2"></i>
                                <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- Step 2: Upload de Imagem -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="flex-1 space-y-6">                        <div class="space-y-4">
                            <h2 class="text-xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center">
                                <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mr-3 shadow-sm">
                                    <i class="bi bi-image-fill text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                Imagem do Produto
                            </h2>

                            <div class="flex items-center justify-center w-full">
                            <label for="image" class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-2xl cursor-pointer bg-gradient-to-br from-neutral-50 to-white dark:from-neutral-800 dark:to-neutral-700 hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-900/20 dark:hover:to-purple-900/20 transition-all duration-300 group">
                                <div class="flex flex-col items-center justify-center px-8 py-8">
                                    @if($image)
                                        <div class="relative group">
                                            <img src="{{ $image->temporaryUrl() }}" class="w-32 h-32 object-cover rounded-xl mb-4 shadow-xl group-hover:scale-105 transition-transform duration-300 border-2 border-white dark:border-neutral-700" loading="lazy">
                                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full p-2 shadow-lg animate-pulse">
                                                <i class="bi bi-check-lg"></i>
                                            </div>
                                        </div>
                                        <div class="text-center space-y-2">
                                            <p class="text-lg font-bold text-green-600 dark:text-green-400 flex items-center justify-center">
                                                <i class="bi bi-check-circle-fill mr-2"></i>
                                                Nova imagem selecionada!
                                            </p>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Clique para alterar a imagem</p>
                                        </div>
                                    @elseif($product->image)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/products/' . $product->image) }}" class="w-32 h-32 object-cover rounded-xl mb-4 shadow-xl group-hover:scale-105 transition-transform duration-300 border-2 border-white dark:border-neutral-700" loading="lazy">
                                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full p-2 shadow-lg">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        </div>
                                        <div class="text-center space-y-2">
                                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                                <i class="bi bi-image mr-2"></i>
                                                Imagem atual do produto
                                            </p>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Clique para alterar a imagem</p>
                                        </div>
                                    @else
                                        <div class="text-center space-y-6">
                                            <div class="relative">
                                                <i class="bi bi-cloud-upload text-6xl text-neutral-300 dark:text-neutral-600 group-hover:text-indigo-400 transition-colors duration-300"></i>
                                                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-plus text-white text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="space-y-3">
                                                <h3 class="text-lg font-bold text-neutral-700 dark:text-neutral-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                                    <i class="bi bi-images mr-2"></i>
                                                    Adicionar Imagem do Produto
                                                </h3>
                                                <p class="text-neutral-500 dark:text-neutral-400">
                                                    <span class="font-bold">Clique para selecionar</span> ou arraste e solte sua imagem aqui
                                                </p>
                                                <div class="flex items-center justify-center space-x-6 pt-3">
                                                    <div class="flex items-center bg-white dark:bg-neutral-800 px-3 py-2 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-600">
                                                        <i class="bi bi-file-earmark-image text-blue-500 mr-2"></i>
                                                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">PNG, JPG, JPEG</span>
                                                    </div>
                                                    <div class="flex items-center bg-white dark:bg-neutral-800 px-3 py-2 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-600">
                                                        <i class="bi bi-hdd text-green-500 mr-2"></i>
                                                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Máx. 2MB</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <input wire:model="image" id="image" type="file" class="hidden" accept="image/*">
                            </label>
                        </div>
                        @error('image')
                        <div class="flex items-center justify-center p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl mt-4">
                            <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                            <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                        </div>
                        @enderror
                    </div>
                </div>

                <!-- Botões de Navegação Entre Steps -->
                <div class="flex justify-center items-center pt-6">
                    <!-- Botão Voltar (Step 2) -->
                    <button type="button"
                            x-show="currentStep === 2"
                            @click="currentStep = 1"
                            style="display: none;"
                            x-bind:style="currentStep === 2 ? 'display: flex;' : 'display: none;'"
                            class="flex items-center px-6 py-3 bg-gradient-to-r from-neutral-300 to-neutral-400 hover:from-neutral-400 hover:to-neutral-500 text-neutral-700 font-bold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Voltar
                    </button>

                    <!-- Espaçador quando no Step 1 -->
                    <div x-show="currentStep === 1"></div>

                    <!-- Botões do lado direito -->
                    <div class="flex items-center space-x-4">
                        <!-- Botão Cancelar -->
                        <a href="{{ route('products.index') }}"
                           class="flex items-center px-6 py-3 bg-gradient-to-r from-neutral-300 to-neutral-400 hover:from-neutral-400 hover:to-neutral-500 text-neutral-700 font-bold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="bi bi-x-circle mr-2"></i>
                            Cancelar
                        </a>

                        <!-- Botão Próximo (Step 1) -->
                        <button type="button"
                                x-show="currentStep === 1"
                                @click="currentStep = 2"
                                style="display: flex;"
                                x-bind:style="currentStep === 1 ? 'display: flex;' : 'display: none;'"
                                class="flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            Próximo
                            <i class="bi bi-arrow-right ml-2"></i>
                        </button>

                        <!-- Botão Salvar (Step 2) -->
                        <button type="submit"
                                x-show="currentStep === 2"
                                style="display: none;"
                                x-bind:style="currentStep === 2 ? 'display: flex;' : 'display: none;'"
                                class="flex items-center px-8 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove class="flex items-center">
                                <i class="bi bi-check-circle mr-2"></i>
                                Atualizar Produto
                            </span>
                            <span wire:loading class="flex items-center">
                                <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                                Atualizando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function incrementQuantityEdit() {
            const input = document.getElementById('stock_quantity');
            const currentValue = parseInt(input.value) || 0;
            const newValue = currentValue + 1;
            input.value = newValue;
            input.dispatchEvent(new Event('input'));
            // Força atualização no Livewire
            input.dispatchEvent(new Event('change'));
        }

        function decrementQuantityEdit() {
            const input = document.getElementById('stock_quantity');
            const currentValue = parseInt(input.value) || 0;
            const newValue = Math.max(0, currentValue - 1);
            input.value = newValue;
            input.dispatchEvent(new Event('input'));
            // Força atualização no Livewire
            input.dispatchEvent(new Event('change'));
        }

        // Máscara de moeda
        function formatCurrency(value) {
            // Verifica se o valor é null, undefined ou string vazia
            if (!value || value === null || value === undefined) return '0,00';

            // Converte para string caso seja um número
            const stringValue = String(value);

            // Remove tudo que não é dígito
            const digits = stringValue.replace(/\D/g, '');

            // Se não há dígitos, retorna 0,00
            if (!digits) return '0,00';

            // Converte para centavos
            const centavos = parseInt(digits);

            // Formata para reais
            const reais = (centavos / 100).toFixed(2).replace('.', ',');

            return reais;
        }

        function applyCurrencyMask(input) {
            // Previne loops infinitos
            if (input.dataset.processing === 'true') {
                return;
            }

            input.dataset.processing = 'true';

            const formatted = formatCurrency(input.value);
            input.value = formatted;

            // Remove o flag de processamento após um pequeno delay
            setTimeout(() => {
                input.dataset.processing = 'false';
            }, 10);
        }

        // Aplica a máscara nos campos de preço
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            const priceSaleInput = document.getElementById('price_sale');

            function setupCurrencyField(input) {
                if (!input) return;

                // Formatar valor inicial se existir
                const currentValue = input.value || '';
                if (currentValue && currentValue !== '0' && currentValue !== '0.00' && currentValue !== '') {
                    const numericValue = parseFloat(currentValue);
                    if (!isNaN(numericValue) && numericValue > 0) {
                        input.value = numericValue.toFixed(2).replace('.', ',');
                    } else {
                        input.value = '0,00';
                    }
                } else {
                    input.value = '0,00';
                }

                // Event listener otimizado
                input.addEventListener('input', function(e) {
                    // Evita loops infinitos verificando se o evento é confiável
                    if (!e.isTrusted) return;

                    applyCurrencyMask(this);
                });

                input.addEventListener('focus', function() {
                    if (this.value === '0,00') {
                        this.value = '';
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value === '' || this.value === null || this.value === undefined) {
                        this.value = '0,00';
                    }
                });

                // Restringir entrada apenas a números
                input.addEventListener('keydown', function(e) {
                    // Permite: backspace, delete, tab, escape, enter
                    if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                        // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                        (e.keyCode === 65 && e.ctrlKey === true) ||
                        (e.keyCode === 67 && e.ctrlKey === true) ||
                        (e.keyCode === 86 && e.ctrlKey === true) ||
                        (e.keyCode === 88 && e.ctrlKey === true) ||
                        // Permite: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                        return;
                    }
                    // Garante que é um número
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
            }

            setupCurrencyField(priceInput);
            setupCurrencyField(priceSaleInput);

            // Aguarda um pouco para garantir que os valores do Livewire foram carregados
            setTimeout(() => {
                if (priceInput && priceInput.value && priceInput.value !== '0,00') {
                    const numericValue = parseFloat(priceInput.value);
                    if (!isNaN(numericValue)) {
                        priceInput.value = numericValue.toFixed(2).replace('.', ',');
                    }
                }
                if (priceSaleInput && priceSaleInput.value && priceSaleInput.value !== '0,00') {
                    const numericValue = parseFloat(priceSaleInput.value);
                    if (!isNaN(numericValue)) {
                        priceSaleInput.value = numericValue.toFixed(2).replace('.', ',');
                    }
                }
            }, 100);
        });

        // Reinicializa quando o Livewire atualiza o componente
        document.addEventListener('livewire:navigated', function() {
            const priceInput = document.getElementById('price');
            const priceSaleInput = document.getElementById('price_sale');

            if (priceInput && !priceInput.value) {
                priceInput.value = '0,00';
            }
            if (priceSaleInput && !priceSaleInput.value) {
                priceSaleInput.value = '0,00';
            }
        });

        // Event listener para upload de imagem
        // REMOVIDO: Não é necessário forçar refresh do Livewire ao selecionar imagem.
        // O Livewire já faz o binding automaticamente do arquivo para a propriedade $image.
    </script>
</div>
