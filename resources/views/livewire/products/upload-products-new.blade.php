<div>
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700 sticky top-0 z-10">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors duration-200">
                        <i class="bi bi-arrow-left text-neutral-600 dark:text-neutral-300"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center">
                            <i class="bi bi-file-earmark-arrow-up text-purple-600 dark:text-purple-400 mr-3"></i>
                            Upload de Produtos
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-400">Importe produtos através de arquivo PDF ou CSV</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="">
    <div class="px-6 py-8">
        <!-- DEBUG: showProductsTable = {{ $showProductsTable ? 'true' : 'false' }} -->
        <!-- DEBUG: count productsUpload = {{ count($productsUpload ?? []) }} -->

        @if(!$showProductsTable)
            <!-- Seção de Upload -->
            <div class="space-y-6">
                
                
                <div class="">
                  

                    <form wire:submit.prevent="processUpload" class="space-y-6">
                        <!-- Upload Area -->
                        <div class="flex items-center justify-center w-full">
                            <label for="pdf_file" class="flex flex-col items-center justify-center w-full h-80 border-2 transition-colors duration-200">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    @if($pdf_file)
                                        <div class="text-center">
                                            <i class="bi bi-file-earmark-check text-6xl text-green-600 dark:text-green-400 mb-4"></i>
                                            <p class="text-lg font-medium text-green-700 dark:text-green-300 mb-2">Arquivo selecionado</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pdf_file->getClientOriginalName() }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ round($pdf_file->getSize() / 1024, 2) }} KB</p>
                                        </div>
                                    @else
                                        <i class="bi bi-cloud-upload text-6xl text-purple-400 dark:text-purple-300 mb-4"></i>
                                        <p class="mb-2 text-lg text-gray-700 dark:text-gray-300"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">PDF ou CSV (Máx. 2MB)</p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center">
                                                <i class="bi bi-file-earmark-pdf text-red-500 mr-1"></i>
                                                PDF
                                            </div>
                                            <div class="flex items-center">
                                                <i class="bi bi-file-earmark-spreadsheet text-green-500 mr-1"></i>
                                                CSV
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <input wire:model="pdf_file" id="pdf_file" type="file" class="hidden" accept=".pdf,.csv">
                            </label>
                        </div>
                        
                        @error('pdf_file')
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="bi bi-exclamation-triangle text-red-500 mr-3"></i>
                                    <p class="text-red-700 dark:text-red-400">{{ $message }}</p>
                                </div>
                            </div>
                        @enderror

                        <!-- Instruções -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4 flex items-center">
                                <i class="bi bi-info-circle-fill mr-2"></i>
                                Instruções para Upload
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-blue-700 dark:text-blue-300 mb-2 flex items-center">
                                        <i class="bi bi-file-earmark-pdf text-red-500 mr-2"></i>
                                        Arquivo PDF
                                    </h4>
                                    <ul class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                                        <li>• Extrai produtos automaticamente</li>
                                        <li>• Reconhece códigos e preços</li>
                                        <li>• Formato específico necessário</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-blue-700 dark:text-blue-300 mb-2 flex items-center">
                                        <i class="bi bi-file-earmark-spreadsheet text-green-500 mr-2"></i>
                                        Arquivo CSV
                                    </h4>
                                    <ul class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                                        <li>• Nome, preço, quantidade</li>
                                        <li>• Uma linha por produto</li>
                                        <li>• Separado por vírgulas</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('products.index') }}" 
                               class="flex-1 sm:flex-none px-6 py-3 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors duration-200 text-center">
                                <i class="bi bi-x-circle mr-2"></i>
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    wire:loading.attr="disabled"
                                    @if(!$pdf_file) disabled @endif>
                                <span wire:loading.remove>
                                    <i class="bi bi-upload mr-2"></i>
                                    Processar Arquivo
                                </span>
                                <span wire:loading>
                                    <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                                    Processando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Tabela de Produtos Extraídos -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Produtos Extraídos</h2>
                </div>
                
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-neutral-600 dark:text-neutral-300">Revise os dados antes de salvar no sistema</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ !empty($productsUpload) ? count($productsUpload) : 0 }} produtos encontrados
                            </span>
                        </div>
                    </div>

                    @if(!empty($productsUpload) && count($productsUpload) > 0)
                        <!-- Grid de Cards de Produtos -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                            @foreach($productsUpload as $index => $product)
                                <div class="product-card-modern">
                                    <!-- Botões de ação -->
                                    <div class="btn-action-group">
                                        <button type="button" wire:click="removeProduct({{ $index }})" class="btn btn-danger" title="Remover">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>

                                    <!-- Área da imagem com badges -->
                                    <div class="product-img-area">
                                        <!-- Upload de imagem ao clicar -->
                                        <div class="relative cursor-pointer" 
                                             onclick="document.getElementById('image-input-{{ $index }}').click();">
                                            @if(isset($product['temp_image']))
                                                <img src="{{ $product['temp_image'] }}" 
                                                     class="product-img" 
                                                     alt="{{ $product['name'] ?? 'Produto' }}"
                                                     id="product-image-{{ $index }}">
                                            @else
                                                <img src="{{ asset('storage/products/product-placeholder.png') }}" 
                                                     class="product-img" 
                                                     alt="{{ $product['name'] ?? 'Produto' }}"
                                                     id="product-image-{{ $index }}">
                                            @endif
                                            
                                            <!-- Overlay para indicar que é clicável -->
                                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-200 rounded-t-xl flex items-center justify-center opacity-0 hover:opacity-100">
                                                <div class="bg-white bg-opacity-90 rounded-full p-2">
                                                    <i class="bi bi-camera text-gray-700 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Input de arquivo oculto (sem wire:model para evitar conflitos) -->
                                        <input type="file" 
                                               id="image-input-{{ $index }}" 
                                               class="hidden" 
                                               accept="image/*">
                                        
                                        <!-- Código do produto editável -->
                                        <div class="badge-product-code editable-badge" title="Código do Produto">
                                            <input type="text" 
                                                   wire:model.lazy="productsUpload.{{ $index }}.product_code"
                                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded"
                                                   placeholder="Código"
                                                   maxlength="15">
                                        </div>
                                        
                                        <!-- Quantidade editável -->
                                        <div class="badge-quantity editable-badge" title="Quantidade em Estoque">
                                            <input type="number" 
                                                   wire:model.lazy="productsUpload.{{ $index }}.stock_quantity"
                                                   min="0"
                                                   class="bg-transparent border-none text-inherit font-inherit w-full text-center focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded"
                                                   placeholder="0">
                                        </div>
                                        
                                        <!-- Ícone da categoria -->
                                        <div class="category-icon-wrapper">
                                            <i class="bi bi-box-seam category-icon"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Conteúdo editável -->
                                    <div class="card-body">
                                        <!-- Nome do produto como título editável -->
                                        <div class="product-title-editable">
                                            <input type="text" 
                                                   wire:model.lazy="productsUpload.{{ $index }}.name"
                                                   class="w-full text-center font-bold bg-transparent border-none text-inherit focus:outline-none focus:ring-2 focus:ring-purple-300 focus:bg-white focus:bg-opacity-10 rounded px-2 py-1"
                                                   placeholder="Nome do produto"
                                                   style="font-size: inherit; color: inherit;">
                                        </div>

                                        <!-- Status como mini badge -->
                                        <div class="flex justify-center mt-2 mb-4">
                                            <select wire:model.lazy="productsUpload.{{ $index }}.status"
                                                    class="status-select">
                                                <option value="ativo">✅ Ativo</option>
                                                <option value="inativo">⏸️ Inativo</option>
                                                <option value="descontinuado">❌ Descontinuado</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Badges de preço editáveis no rodapé -->
                                    <div class="badge-price editable-price-badge" title="Preço de Custo">
                                        <i class="bi bi-tag"></i>
                                        <span class="text-xs">R$</span>
                                        <input type="number" 
                                               wire:model.lazy="productsUpload.{{ $index }}.price"
                                               step="0.01"
                                               class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded text-right"
                                               placeholder="0,00">
                                    </div>
                                    
                                    <div class="badge-price-sale editable-price-badge" title="Preço de Venda">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span class="text-xs">R$</span>
                                        <input type="number" 
                                               wire:model.lazy="productsUpload.{{ $index }}.price_sale"
                                               step="0.01"
                                               class="bg-transparent border-none text-inherit font-inherit w-16 focus:outline-none focus:ring-1 focus:ring-white focus:bg-white focus:bg-opacity-20 rounded text-right"
                                               placeholder="0,00">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Script para upload de imagens -->
                        <script>
                            console.log('SCRIPT CARREGADO - Sistema de upload iniciando...');
                            
                            function initializeImageUpload() {
                                console.log('Inicializando sistema de upload de imagens');
                                
                                // Encontrar todos os inputs de imagem
                                const imageInputs = document.querySelectorAll('[id^="image-input-"]');
                                console.log('Inputs encontrados:', imageInputs.length);
                                
                                imageInputs.forEach(function(input, index) {
                                    console.log('Configurando input:', input.id);
                                    
                                    // Remover listeners antigos
                                    input.removeEventListener('change', input._uploadHandler);
                                    
                                    // Criar handler personalizado
                                    input._uploadHandler = function(event) {
                                        console.log('=== UPLOAD INICIADO ===');
                                        console.log('Input ID:', this.id);
                                        
                                        const file = this.files[0];
                                        if (!file) {
                                            console.log('Nenhum arquivo selecionado');
                                            return;
                                        }
                                        
                                        console.log('Arquivo:', file.name, 'Tamanho:', file.size, 'Tipo:', file.type);
                                        
                                        // Validações
                                        if (!file.type.startsWith('image/')) {
                                            alert('Por favor, selecione uma imagem');
                                            this.value = '';
                                            return;
                                        }
                                        
                                        if (file.size > 5 * 1024 * 1024) {
                                            alert('Arquivo muito grande! Máximo 5MB');
                                            this.value = '';
                                            return;
                                        }
                                        
                                        console.log('Arquivo válido, processando...');
                                        
                                        // Extrair índice do ID
                                        const index = this.id.replace('image-input-', '');
                                        console.log('Índice extraído:', index);
                                        
                                        // Encontrar elemento de imagem
                                        const imgElement = document.getElementById('product-image-' + index);
                                        console.log('Elemento de imagem:', imgElement ? 'ENCONTRADO' : 'NÃO ENCONTRADO');
                                        
                                        if (!imgElement) {
                                            console.error('Elemento de imagem não encontrado!');
                                            const allImages = document.querySelectorAll('[id^="product-image-"]');
                                            console.log('IDs disponíveis:', Array.from(allImages).map(el => el.id));
                                            return;
                                        }
                                        
                                        // Ler arquivo
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            console.log('Arquivo lido com sucesso');
                                            const base64 = e.target.result;
                                            
                                            console.log('Aplicando imagem ao elemento...');
                                            imgElement.src = base64;
                                            console.log('SUCESSO! Imagem aplicada');
                                            
                                            // Sincronizar com Livewire
                                            try {
                                                if (window.Livewire) {
                                                    const component = window.Livewire.find('{{ $this->getId() }}');
                                                    if (component && component.updateProductImage) {
                                                        component.updateProductImage(parseInt(index), base64);
                                                        console.log('Sincronizado com Livewire');
                                                    }
                                                }
                                            } catch (error) {
                                                console.log('Erro Livewire:', error.message);
                                            }
                                        };
                                        
                                        reader.onerror = function() {
                                            console.error('Erro ao ler arquivo');
                                            alert('Erro ao processar imagem');
                                        };
                                        
                                        reader.readAsDataURL(file);
                                    };
                                    
                                    // Adicionar listener
                                    input.addEventListener('change', input._uploadHandler);
                                    console.log('Listener configurado para:', input.id);
                                });
                                
                                console.log('Sistema de upload configurado!');
                            }
                            
                            // Inicializar quando DOM estiver pronto
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', initializeImageUpload);
                            } else {
                                initializeImageUpload();
                            }
                            
                            // Reinicializar após atualizações do Livewire
                            document.addEventListener('livewire:update', function() {
                                console.log('Livewire atualizou, reinicializando...');
                                setTimeout(initializeImageUpload, 100);
                            });
                        </script>

                        <!-- CSS personalizado para badges editáveis -->
                        <style>
                            /* Inputs editáveis em badges */
                            .editable-badge input {
                                transition: all 0.2s ease;
                                min-width: 60px;
                                background: transparent;
                                border: none;
                                outline: none;
                                color: inherit;
                                font-weight: inherit;
                                font-size: inherit;
                                text-align: center;
                            }
                            
                            .editable-badge input:focus {
                                background-color: rgba(255, 255, 255, 0.95) !important;
                                color: #374151 !important;
                                font-weight: 600;
                                border-radius: 4px;
                                padding: 2px 4px;
                                box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
                            }
                            
                            .editable-badge input:hover {
                                background-color: rgba(255, 255, 255, 0.1);
                                border-radius: 4px;
                            }
                            
                            /* Inputs de preço */
                            .editable-price-badge {
                                display: flex !important;
                                align-items: center !important;
                                gap: 0.2rem !important;
                            }
                            
                            .editable-price-badge input {
                                transition: all 0.2s ease;
                                background: transparent;
                                border: none;
                                outline: none;
                                color: inherit;
                                font-weight: inherit;
                                font-size: inherit;
                                text-align: right;
                                width: 60px;
                            }
                            
                            .editable-price-badge input:focus {
                                background-color: rgba(255, 255, 255, 0.95) !important;
                                color: #374151 !important;
                                font-weight: 600;
                                border-radius: 4px;
                                padding: 2px 4px;
                                box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
                            }
                            
                            .editable-price-badge input:hover {
                                background-color: rgba(255, 255, 255, 0.1);
                                border-radius: 4px;
                            }
                            
                            /* Título do produto editável */
                            .product-title-editable input {
                                transition: all 0.2s ease;
                                background: transparent;
                                border: none;
                                outline: none;
                                color: inherit;
                                font-weight: inherit;
                                font-size: inherit;
                                text-align: center;
                                width: 100%;
                            }
                            
                            .product-title-editable input:focus {
                                background-color: rgba(147, 51, 234, 0.1) !important;
                                border-radius: 6px;
                                padding: 4px 8px;
                                transform: scale(1.02);
                                box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
                            }
                            
                            .product-title-editable input:hover {
                                background-color: rgba(147, 51, 234, 0.05);
                                border-radius: 6px;
                            }
                            
                            /* Efeito hover na imagem */
                            .product-img-area .cursor-pointer:hover {
                                transform: scale(1.02);
                                transition: transform 0.2s ease;
                            }
                            
                            .product-img-area .cursor-pointer:active {
                                transform: scale(0.98);
                            }
                            
                            /* Select de status */
                            .status-select {
                                transition: all 0.2s ease;
                                background: rgba(243, 244, 246, 0.8);
                                border: 1px solid rgba(209, 213, 219, 0.5);
                                border-radius: 1rem;
                                padding: 4px 12px;
                                font-size: 0.75rem;
                                font-weight: 600;
                                color: #374151;
                                outline: none;
                                cursor: pointer;
                            }
                            
                            .status-select:focus {
                                background: rgba(255, 255, 255, 0.95);
                                border-color: rgba(147, 51, 234, 0.5);
                                box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.1);
                                transform: scale(1.05);
                            }
                            
                            .status-select:hover {
                                background: rgba(255, 255, 255, 0.9);
                                border-color: rgba(147, 51, 234, 0.3);
                            }
                            
                            /* Loading states */
                            .product-img.loading {
                                filter: blur(1px);
                                opacity: 0.6;
                            }
                            
                            /* Animação de foco */
                            @keyframes focusGlow {
                                0% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4); }
                                70% { box-shadow: 0 0 0 6px rgba(147, 51, 234, 0); }
                                100% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0); }
                            }
                            
                            .editable-badge input:focus,
                            .editable-price-badge input:focus,
                            .product-title-editable input:focus {
                                animation: focusGlow 0.6s ease-out;
                            }
                            
                            /* Placeholder styles */
                            .editable-badge input::placeholder,
                            .editable-price-badge input::placeholder,
                            .product-title-editable input::placeholder {
                                color: rgba(255, 255, 255, 0.6);
                                font-style: italic;
                            }
                            
                            .editable-badge input:focus::placeholder,
                            .editable-price-badge input:focus::placeholder,
                            .product-title-editable input:focus::placeholder {
                                color: rgba(55, 65, 81, 0.5);
                            }
                        </style>

                        <!-- Botões de Ação -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                            <button wire:click="$set('showProductsTable', false)" 
                                    class="flex-1 sm:flex-none px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors duration-200">
                                <i class="bi bi-arrow-left mr-2"></i>
                                Voltar
                            </button>
                            
                            <button wire:click="store" 
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle mr-2"></i>
                                    Salvar Produtos ({{ !empty($productsUpload) ? count($productsUpload) : 0 }})
                                </span>
                                <span wire:loading>
                                    <i class="bi bi-arrow-clockwise animate-spin mr-2"></i>
                                    Salvando...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    </div>
</div>
