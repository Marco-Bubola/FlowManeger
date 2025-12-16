<div>
    @if($showModal)
    <div x-data="{
        modalOpen: true,
        exportType: @entangle('exportType'),
        downloading: false,

        async waitForImages(element) {
            const images = element.querySelectorAll('img');
            const promises = Array.from(images).map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                    // Timeout de 5 segundos
                    setTimeout(reject, 5000);
                });
            });
            return Promise.allSettled(promises);
        },

        async exportCard(productId, type) {
            this.downloading = true;

            // Aguarda um momento para garantir que o DOM está atualizado
            await new Promise(resolve => setTimeout(resolve, 100));

            const cardElement = document.getElementById('export-card-' + productId);

            if (!cardElement) {
                console.error('Card element not found');
                alert('Erro: Elemento do card não encontrado');
                this.downloading = false;
                return;
            }

            try {
                console.log('Aguardando carregamento das imagens...');
                await this.waitForImages(cardElement);
                console.log('Imagens carregadas! Iniciando captura do card...');

                // Usa html2canvas para capturar o card
                const canvas = await html2canvas(cardElement, {
                    backgroundColor: '#e6e6fa',
                    scale: 3,
                    logging: true,
                    useCORS: true,
                    allowTaint: true,
                    imageTimeout: 15000,
                    removeContainer: true,
                    foreignObjectRendering: false
                });

                console.log('Canvas criado com sucesso');

                // Converte para blob e faz download
                canvas.toBlob((blob) => {
                    if (!blob) {
                        console.error('Erro ao criar blob');
                        alert('Erro ao gerar imagem');
                        this.downloading = false;
                        return;
                    }

                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const productName = cardElement.dataset.productName;

                    // Define o nome do arquivo baseado no tipo
                    const typeLabels = {
                        'complete': 'completo',
                        'public': 'publico',
                        'image-only': 'apenas-imagem',
                        'image-name': 'imagem-nome',
                        'image-price': 'imagem-preco'
                    };
                    const typeLabel = typeLabels[type] || type;

                    link.href = url;
                    link.download = `${productName}-${typeLabel}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);

                    console.log('Download iniciado');
                    this.downloading = false;
                }, 'image/png', 1.0);
            } catch (error) {
                console.error('Erro detalhado ao exportar:', error);
                alert('Erro ao exportar: ' + error.message);
                this.downloading = false;
            }
        }
    }"
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] overflow-y-auto"
         @keydown.escape.window="modalOpen = false; $wire.closeModal()">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-slate-900/80 to-blue-900/40 backdrop-blur-md"></div>

        <!-- Container do Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                 class="relative w-full max-w-6xl bg-gradient-to-br from-white/95 to-slate-50/95 dark:from-slate-800/95 dark:to-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden">

                <!-- Header Compacto -->
                <div class="relative bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                <i class="bi bi-file-earmark-image text-xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    Exportar Card de Produto
                                </h3>
                                <p class="text-xs text-white/80">
                                    Escolha o formato e baixe a imagem
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeModal"
                                @click="modalOpen = false"
                                class="w-8 h-8 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg flex items-center justify-center text-white transition-all duration-200">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Conteúdo: Layout Horizontal (Opções | Preview) -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- LADO ESQUERDO: Opções de Formato -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">
                                    <i class="bi bi-toggles text-purple-500 mr-2"></i>
                                    Selecione o Formato:
                                </label>

                                <div class="space-y-3">
                                    <!-- Opção Completo -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               x-model="exportType"
                                               value="complete"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all duration-200 hover:shadow-lg hover:border-blue-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-file-earmark-check text-lg text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Completo
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Todos os detalhes
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-blue-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Opção Público -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               x-model="exportType"
                                               value="public"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/20 transition-all duration-200 hover:shadow-lg hover:border-emerald-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-globe text-lg text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Público
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Sem custo e estoque
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-emerald-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Opção Somente Imagem -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               x-model="exportType"
                                               value="image-only"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 transition-all duration-200 hover:shadow-lg hover:border-purple-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-image text-lg text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Apenas Imagem
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Foto do produto
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Opção Imagem + Nome -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               x-model="exportType"
                                               value="image-name"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 transition-all duration-200 hover:shadow-lg hover:border-orange-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-card-image text-lg text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Imagem + Nome
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Foto com título
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-orange-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Opção Imagem + Preço -->
                                    <label class="relative cursor-pointer group block">
                                        <input type="radio"
                                               x-model="exportType"
                                               value="image-price"
                                               class="sr-only peer">
                                        <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 dark:border-slate-600 peer-checked:border-pink-500 peer-checked:bg-pink-50 dark:peer-checked:bg-pink-900/20 transition-all duration-200 hover:shadow-lg hover:border-pink-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="bi bi-tags text-lg text-white"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
                                                        Imagem + Preço
                                                    </h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                                        Foto com valor
                                                    </p>
                                                </div>
                                                <i class="bi bi-check-circle-fill text-xl text-pink-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 flex-shrink-0"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="space-y-2 pt-4">
                                @if($product)
                                <button @click="exportCard({{ $product->id }}, exportType)"
                                        :disabled="downloading"
                                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!downloading">
                                        <i class="bi bi-download text-lg"></i>
                                    </template>
                                    <template x-if="downloading">
                                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                                    </template>
                                    <span x-text="downloading ? 'Gerando...' : 'Baixar Imagem'"></span>
                                </button>
                                @endif

                                <button wire:click="closeModal"
                                        @click="modalOpen = false"
                                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl transition-all duration-200">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <!-- LADO DIREITO: Preview do Card -->
                        <div>
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">
                                <i class="bi bi-eye text-purple-500 mr-2"></i>
                                Preview:
                            </label>

                            @if($product)

                            <!-- CARD COMPLETO -->
                            <template x-if="exportType === 'complete' || exportType === 'public'">
                                <div id="export-card-{{ $product->id }}"
                                     data-product-name="{{ $product->product_code }}"
                                     style="width: 380px; background: #ffffff; border-radius: 28px; border: 6px solid #b39ddb; box-shadow: 0 12px 32px rgba(149, 117, 205, 0.25); overflow: hidden; margin: 0 auto; font-family: 'Segoe UI', Tahoma, sans-serif; position: relative;">

                                    <div style="position: absolute; top: 16px; left: 16px; background: linear-gradient(135deg, #9575cd, #b39ddb); color: #fff; padding: 8px 18px; border-radius: 18px; font-size: 14px; font-weight: 700; border: 3px solid #ffe0b2; box-shadow: 0 4px 12px rgba(149, 117, 205, 0.4); display: flex; align-items: center; gap: 6px; z-index: 20;">
                                        <i class="bi bi-upc-scan" style="font-size: 16px;"></i> {{ $product->product_code }}
                                    </div>

                                    <template x-if="exportType === 'complete'">
                                        <div style="position: absolute; top: 380px; right: 16px; background: linear-gradient(135deg, #f8bbd0, #ba68c8); color: #fff; padding: 8px 18px; border-radius: 18px; font-size: 14px; font-weight: 700; border: 3px solid #ffe0b2; box-shadow: 0 4px 12px rgba(248, 187, 208, 0.4); display: flex; align-items: center; gap: 6px; z-index: 20;">
                                            <i class="bi bi-stack" style="font-size: 16px;"></i> {{ $product->stock_quantity }}
                                        </div>
                                    </template>

                                    <div style="position: relative; width: 100%; height: 420px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 20px;">
                                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                             alt="{{ $product->name }}"
                                             crossorigin="anonymous"
                                             loading="eager"
                                             decoding="sync"
                                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>

                                    <div style="background: linear-gradient(135deg, #e1bee7 0%, #ba68c8 50%, #9575cd 100%); padding: 20px 16px; text-align: center;">
                                        <h3 style="font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; color: #fff; margin: 0; line-height: 1.3; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); max-height: 54px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ ucwords(substr($product->name, 0, 60)) }}{{ strlen($product->name) > 60 ? '...' : '' }}
                                        </h3>
                                    </div>

                                    <div style="background: linear-gradient(180deg, #f3e5f5 0%, #e1bee7 100%); padding: 24px 20px; display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap;">
                                        <template x-if="exportType === 'complete'">
                                            <div style="background: linear-gradient(135deg, #f8bbd0, #f48fb1); color: #424242; padding: 14px 32px; border-radius: 20px; font-size: 20px; font-weight: 700; box-shadow: 0 4px 12px rgba(244, 143, 177, 0.3); display: flex; align-items: center; gap: 8px; border: 2px solid rgba(255, 255, 255, 0.5);">
                                                <i class="bi bi-tag" style="font-size: 22px;"></i>
                                                <span>R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                            </div>
                                        </template>

                                        <div style="background: linear-gradient(135deg, #ffcdd2, #f8bbd0); color: #424242; padding: 16px 36px; border-radius: 24px; font-size: 24px; font-weight: 800; box-shadow: 0 6px 16px rgba(248, 187, 208, 0.4); display: flex; align-items: center; gap: 10px; border: 3px solid rgba(255, 255, 255, 0.6);">
                                            <i class="bi bi-currency-dollar" style="font-size: 28px;"></i>
                                            <span>R$ {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- APENAS IMAGEM -->
                            <template x-if="exportType === 'image-only'">
                                <div id="export-card-{{ $product->id }}"
                                     data-product-name="{{ $product->product_code }}"
                                     style="width: 500px; height: 500px; background: #fff; border-radius: 20px; overflow: hidden; margin: 0 auto; display: flex; align-items: center; justify-content: center; padding: 20px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);">
                                    <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                         alt="{{ $product->name }}"
                                         crossorigin="anonymous"
                                         loading="eager"
                                         decoding="sync"
                                         style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                            </template>

                            <!-- IMAGEM + NOME -->
                            <template x-if="exportType === 'image-name'">
                                <div id="export-card-{{ $product->id }}"
                                     data-product-name="{{ $product->product_code }}"
                                     style="width: 450px; background: #ffffff; border-radius: 24px; border: 4px solid #b39ddb; overflow: hidden; margin: 0 auto; font-family: 'Segoe UI', Tahoma, sans-serif; box-shadow: 0 8px 24px rgba(149, 117, 205, 0.2);">

                                    <div style="width: 100%; height: 400px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 20px;">
                                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                             alt="{{ $product->name }}"
                                             crossorigin="anonymous"
                                             loading="eager"
                                             decoding="sync"
                                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>

                                    <div style="background: linear-gradient(135deg, #e1bee7 0%, #ba68c8 50%, #9575cd 100%); padding: 24px 20px; text-align: center;">
                                        <h3 style="font-size: 22px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; color: #fff; margin: 0; line-height: 1.3; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);">
                                            {{ ucwords($product->name) }}
                                        </h3>
                                    </div>
                                </div>
                            </template>

                            <!-- IMAGEM + PREÇO -->
                            <template x-if="exportType === 'image-price'">
                                <div id="export-card-{{ $product->id }}"
                                     data-product-name="{{ $product->product_code }}"
                                     style="width: 450px; background: #ffffff; border-radius: 24px; border: 4px solid #b39ddb; overflow: hidden; margin: 0 auto; font-family: 'Segoe UI', Tahoma, sans-serif; position: relative; box-shadow: 0 8px 24px rgba(149, 117, 205, 0.2);">

                                    <div style="width: 100%; height: 400px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 20px;">
                                        <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}"
                                             alt="{{ $product->name }}"
                                             crossorigin="anonymous"
                                             loading="eager"
                                             decoding="sync"
                                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>

                                    <div style="background: linear-gradient(180deg, #f3e5f5 0%, #e1bee7 100%); padding: 28px 20px; display: flex; align-items: center; justify-content: center;">
                                        <div style="background: linear-gradient(135deg, #ffcdd2, #f8bbd0); color: #424242; padding: 18px 40px; border-radius: 28px; font-size: 28px; font-weight: 800; box-shadow: 0 6px 16px rgba(248, 187, 208, 0.4); display: flex; align-items: center; gap: 12px; border: 3px solid rgba(255, 255, 255, 0.6);">
                                            <i class="bi bi-currency-dollar" style="font-size: 32px;"></i>
                                            <span>R$ {{ number_format($product->price_sale, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
