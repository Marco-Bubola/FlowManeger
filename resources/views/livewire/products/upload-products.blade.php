<div class="">
    <!-- Custom CSS para manter o estilo dos cards -->
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">

    <!-- Header -->
    <x-upload-header-original
        title="Upload de Produtos"
        description="Importe produtos através de arquivo PDF ou CSV"
        :back-route="route('products.index')" />

    <!-- Conteúdo Principal -->
    <div class="px-6 py-8">
        <!-- DEBUG: showProductsTable = {{ $showProductsTable ? 'true' : 'false' }} -->
        <!-- DEBUG: count productsUpload = {{ count($productsUpload ?? []) }} -->

        @if(!$showProductsTable)
            <!-- Seção de Upload -->
            <x-upload-zone-original
                wire-model="pdf_file"
                :current-file="$pdf_file" />
        @else
            <!-- Tabela de Produtos Extraídos -->
            <x-products-preview-original
                :products="$productsUpload ?? []"
                :show-back-button="true" />
        @endif
    </div>

    <!-- CSS personalizado para badges editáveis -->
    <style>
        /* Tornar os cards de produto mais altos */
        .product-card-modern {
            min-height: 420px !important;
            height: auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        /* Reduzir espaçamento entre status e valores */
        .product-card-modern .card-body {
            padding: 0.8em 1em 0.5em 1em !important;
            min-height: auto !important;
            gap: 0.3em !important;
        }

        /* Corrigir imagem para não sair do quadrado */
        .product-card-modern .product-img-area {
            position: relative;
            overflow: hidden !important;
            border-top-left-radius: 1.2em;
            border-top-right-radius: 1.2em;
        }

        .product-card-modern .product-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-top-left-radius: 1.2em;
            border-top-right-radius: 1.2em;
        }

        /* Grupo de botões de ação */
        .btn-action-group {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        /* Botão de copiar nome (verde) */
        .btn-success {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
            transform: scale(1.05);
        }

        .btn-success:active {
            transform: scale(0.95);
        }

        /* Botão de copiar código (azul) */
        .btn-info {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-info:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            transform: scale(1.05);
        }

        .btn-info:active {
            transform: scale(0.95);
        }

        /* Animação de sucesso ao copiar */
        @keyframes copySuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .btn-success.copied,
        .btn-info.copied {
            animation: copySuccess 0.4s ease-in-out;
        }

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

    <!-- Script para upload de imagens (detecta produtos automaticamente) -->
    <script>
        // Função para copiar apenas o nome do produto
        function copyProductName(index, name) {
            const productName = name || 'Sem nome';

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productName).then(() => {
                    showCopySuccess(index, 'Nome copiado!', 'name');
                }).catch(err => {
                    console.error('Erro ao copiar nome:', err);
                    fallbackCopy(productName, index, 'name');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productName, index, 'name');
            }
        }

        // Função para copiar apenas o código do produto
        function copyProductCode(index, code) {
            // Remover pontos do código antes de copiar
            const productCode = (code || 'Sem código').replace(/\./g, '');

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productCode).then(() => {
                    showCopySuccess(index, 'Código copiado!', 'code');
                }).catch(err => {
                    console.error('Erro ao copiar código:', err);
                    fallbackCopy(productCode, index, 'code');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productCode, index, 'code');
            }
        }

        // Função para copiar informações do produto (mantida para compatibilidade)
        function copyProductInfo(index, name, code) {
            const productInfo = `Nome: ${name || 'Sem nome'}\nCódigo: ${code || 'Sem código'}`;

            // Tentar usar a API moderna do clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(productInfo).then(() => {
                    showCopySuccess(index, 'Informações copiadas!', 'info');
                }).catch(err => {
                    console.error('Erro ao copiar:', err);
                    fallbackCopy(productInfo, index, 'info');
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(productInfo, index, 'info');
            }
        }

        // Função de fallback para copiar
        function fallbackCopy(text, index, type) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                const message = type === 'name' ? 'Nome copiado!' :
                               type === 'code' ? 'Código copiado!' : 'Informações copiadas!';
                showCopySuccess(index, message, type);
            } catch (err) {
                console.error('Erro ao copiar:', err);
                showCopyError(index, 'Erro ao copiar', type);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        // Função para mostrar sucesso na cópia
        function showCopySuccess(index, message, type) {
            let copyButton;

            if (type === 'name') {
                copyButton = document.querySelector(`button[onclick*="copyProductName(${index}"]`);
            } else if (type === 'code') {
                copyButton = document.querySelector(`button[onclick*="copyProductCode(${index}"]`);
            } else {
                copyButton = document.querySelector(`button[onclick*="copyProductInfo(${index}"]`);
            }

            if (copyButton) {
                copyButton.classList.add('copied');
                const originalTitle = copyButton.title;
                copyButton.title = message;

                // Mudar ícone temporariamente
                const icon = copyButton.querySelector('i');
                if (icon) {
                    const originalClass = icon.className;
                    icon.className = 'bi bi-check2';

                    setTimeout(() => {
                        copyButton.classList.remove('copied');
                        copyButton.title = originalTitle;
                        icon.className = originalClass;
                    }, 1500);
                }
            }

            // Mostrar toast de sucesso
            showToast(message, 'success');
        }

        // Função para mostrar erro na cópia
        function showCopyError(index, message, type) {
            let copyButton;

            if (type === 'name') {
                copyButton = document.querySelector(`button[onclick*="copyProductName(${index}"]`);
            } else if (type === 'code') {
                copyButton = document.querySelector(`button[onclick*="copyProductCode(${index}"]`);
            } else {
                copyButton = document.querySelector(`button[onclick*="copyProductInfo(${index}"]`);
            }

            if (copyButton) {
                copyButton.title = message;
                setTimeout(() => {
                    copyButton.title = type === 'name' ? 'Copiar nome' :
                                     type === 'code' ? 'Copiar código' : 'Copiar nome e código';
                }, 2000);
            }

            // Mostrar toast de erro
            showToast(message, 'error');
        }

        // Função para mostrar toast
        function showToast(message, type) {
            // Remover toast anterior se existir
            const existingToast = document.getElementById('copy-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.id = 'copy-toast';
            toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                type === 'success'
                    ? 'bg-green-500 text-white'
                    : 'bg-red-500 text-white'
            }`;

            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);

            // Remover após 3 segundos
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }

        // Adicionar atalho de teclado Ctrl+C para copiar produto focado
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'c') {
                // Verificar se o foco está em um input de produto
                const activeElement = document.activeElement;
                if (activeElement && activeElement.tagName === 'INPUT') {
                    const productInput = activeElement.closest('.product-card-modern');
                    if (productInput) {
                        // Encontrar o índice do produto
                        const allCards = document.querySelectorAll('.product-card-modern');
                        const productIndex = Array.from(allCards).indexOf(productInput);

                        if (productIndex !== -1) {
                            e.preventDefault();

                            // Verificar qual tipo de input está focado
                            const wireModel = activeElement.getAttribute('wire:model.lazy');

                            if (wireModel && wireModel.includes('name')) {
                                // Copiar apenas o nome
                                const name = activeElement.value || 'Sem nome';
                                copyProductName(productIndex, name);
                            } else if (wireModel && wireModel.includes('product_code')) {
                                // Copiar apenas o código
                                const code = activeElement.value || 'Sem código';
                                copyProductCode(productIndex, code);
                            } else {
                                // Copiar informações gerais
                                const nameInput = productInput.querySelector('input[wire\\:model*="name"]');
                                const codeInput = productInput.querySelector('input[wire\\:model*="product_code"]');

                                const name = nameInput ? nameInput.value : 'Sem nome';
                                const code = codeInput ? codeInput.value : 'Sem código';

                                copyProductInfo(productIndex, name, code);
                            }
                        }
                    }
                }
            }
        });

        // Log inicial apenas para confirmar que o script foi carregado
        console.log('=== SCRIPT DE UPLOAD CARREGADO ===');
        console.log('Aguardando produtos...');

        let systemInitialized = false;

        function initUploadSystem() {
            console.log('Verificando se há produtos para inicializar...');

            // Debug: listar todos os elementos disponíveis
            const allElements = document.querySelectorAll('*[id*="product"]');
            console.log('Elementos com "product" no ID:', allElements.length);
            allElements.forEach(el => console.log('- Elemento encontrado:', el.id, el.tagName));

            // Verificar se existem produtos primeiro
            const productCards = document.querySelectorAll('[id^="product-image-"]');
            const imageInputs = document.querySelectorAll('[id^="image-input-"]');

            console.log('Cards buscados:', productCards.length);
            console.log('Inputs buscados:', imageInputs.length);

            if (productCards.length === 0 || imageInputs.length === 0) {
                console.log('Nenhum produto encontrado. Aguardando...');
                return false; // Não inicializar ainda
            }

            if (systemInitialized) {
                console.log('Sistema já foi inicializado, pulando...');
                return true;
            }

            console.log('=== INICIALIZANDO SISTEMA DE UPLOAD ===');
            console.log('Cards de produtos encontrados:', productCards.length);
            console.log('Inputs de imagem encontrados:', imageInputs.length);

            // Adicionar listeners aos inputs
            imageInputs.forEach(function(input, index) {
                const productIndex = input.id.replace('image-input-', '');
                console.log('Configurando input para produto:', productIndex);

                // Remover listener anterior se existir
                if (input.uploadHandler) {
                    input.removeEventListener('change', input.uploadHandler);
                }

                // Criar novo handler
                input.uploadHandler = function(event) {
                    console.log('=== UPLOAD INICIADO ===');
                    console.log('Produto:', productIndex);

                    const file = event.target.files[0];
                    if (!file) {
                        console.log('Nenhum arquivo selecionado');
                        return;
                    }

                    console.log('Arquivo:', file.name, 'Tamanho:', file.size);

                    // Validações básicas
                    if (!file.type.startsWith('image/')) {
                        alert('Selecione apenas arquivos de imagem');
                        event.target.value = '';
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        alert('Arquivo muito grande! Máximo 5MB');
                        event.target.value = '';
                        return;
                    }

                    // Ler arquivo
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const base64 = e.target.result;
                        console.log('Base64 gerado com sucesso');

                        // Atualizar imagem do card
                        const imgElement = document.getElementById('product-image-' + productIndex);
                        if (imgElement) {
                            console.log('Atualizando imagem do card...');
                            imgElement.src = base64;
                            console.log('Imagem atualizada!');

                            // Chamar método Livewire
                            if (window.Livewire) {
                                try {
                                    const component = window.Livewire.find('{{ $this->getId() }}');
                                    if (component) {
                                        component.call('updateProductImage', parseInt(productIndex), base64);
                                        console.log('Livewire sincronizado');
                                    }
                                } catch (error) {
                                    console.log('Erro Livewire:', error);
                                }
                            }
                        } else {
                            console.error('Imagem não encontrada:', 'product-image-' + productIndex);
                        }
                    };

                    reader.onerror = function() {
                        console.error('Erro ao ler arquivo');
                        alert('Erro ao processar imagem');
                    };

                    reader.readAsDataURL(file);
                };

                // Adicionar listener
                input.addEventListener('change', input.uploadHandler);
                console.log('Listener adicionado ao produto:', productIndex);
            });

            systemInitialized = true;
            console.log('=== SISTEMA DE UPLOAD INICIALIZADO COM SUCESSO ===');
            return true; // Inicialização bem-sucedida
        }

        // Função para verificar produtos periodicamente
        function checkForProducts() {
            console.log('Verificação periódica de produtos...');
            const success = initUploadSystem();
            if (success) {
                console.log('Produtos detectados na verificação periódica!');
                clearInterval(productCheckInterval);
            }
        }

        // Observer para detectar mudanças no DOM
        const observer = new MutationObserver(function(mutations) {
            let shouldCheck = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && ( // Elemento
                            node.id && node.id.includes('product') ||
                            node.querySelector && node.querySelector('[id*="product"]')
                        )) {
                            console.log('Mudança detectada no DOM com produtos');
                            shouldCheck = true;
                        }
                    });
                }
            });

            if (shouldCheck && !systemInitialized) {
                setTimeout(initUploadSystem, 100);
            }
        });

        // Iniciar observação do DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Eventos do Livewire
        document.addEventListener('livewire:updated', function() {
            console.log('Livewire updated - verificando produtos...');
            setTimeout(function() {
                const hasProducts = initUploadSystem();
                if (hasProducts) {
                    console.log('Produtos detectados via livewire:updated!');
                }
            }, 100);
        });

        document.addEventListener('livewire:load', function() {
            console.log('Livewire load - verificando produtos...');
            setTimeout(initUploadSystem, 100);
        });

        document.addEventListener('livewire:component:updated', function() {
            console.log('Livewire component updated - verificando produtos...');
            setTimeout(initUploadSystem, 100);
        });

        // Verificação periódica (como fallback)
        const productCheckInterval = setInterval(checkForProducts, 2000);

        // Parar verificação após 30 segundos
        setTimeout(function() {
            if (!systemInitialized) {
                console.log('Timeout: Parando verificação de produtos');
                clearInterval(productCheckInterval);
            }
        }, 30000);

        // Tentar inicializar quando o DOM estiver pronto
        function tryInitialize() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryInitialize);
                return;
            }

            // Tentar inicializar, mas não forçar se não há produtos
            initUploadSystem();
        }

        // Inicialização inicial
        tryInitialize();

        console.log('Sistema de detecção de produtos configurado');
    </script>
</div>
