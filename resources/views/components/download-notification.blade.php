<!-- Componente de Notificação de Download -->
<div x-data="downloadNotification()" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-full"
     x-transition:enter-end="opacity-100 transform translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-x-0"
     x-transition:leave-end="opacity-0 transform translate-x-full"
     @download-started.window="startDownload($event.detail)"
     @download-completed.window="completeDownload()"
     @download-error.window="errorDownload($event.detail)"
     class="fixed top-4 right-4 z-50 max-w-sm w-full">
     
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-2xl border border-gray-200 dark:border-zinc-700 p-5 backdrop-blur-sm">
        <!-- Header com ícone e título -->
        <div class="flex items-center space-x-4 mb-4">
            <div class="flex-shrink-0">
                <!-- Ícone de carregamento com progresso circular -->
                <div x-show="status === 'loading'" class="relative w-12 h-12">
                    <!-- Círculo de fundo -->
                    <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845
                          a 15.9155 15.9155 0 0 1 0 31.831
                          a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="#e5e7eb"
                          stroke-width="2.5"/>
                        <!-- Círculo de progresso -->
                        <path d="M18 2.0845
                          a 15.9155 15.9155 0 0 1 0 31.831
                          a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="url(#progressGradient)"
                          stroke-width="2.5"
                          :stroke-dasharray="progress + ' 100'"
                          stroke-linecap="round"
                          class="transition-all duration-300"/>
                    </svg>
                    
                    <!-- Gradiente para o progresso -->
                    <svg style="position: absolute; width: 0; height: 0;">
                        <defs>
                            <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4f46e5"/>
                                <stop offset="100%" stop-color="#7c3aed"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    
                    <!-- Ícone e porcentagem no centro -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <i class="bi bi-file-earmark-pdf text-red-500 text-lg mb-0.5"></i>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400" x-text="Math.round(progress) + '%'"></span>
                    </div>
                </div>
                
                <!-- Ícone de sucesso -->
                <div x-show="status === 'success'" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-75"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                    <i class="bi bi-check-circle text-white text-xl"></i>
                </div>
                
                <!-- Ícone de erro -->
                <div x-show="status === 'error'" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-75"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center shadow-lg">
                    <i class="bi bi-exclamation-triangle text-white text-xl"></i>
                </div>
            </div>
            
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white" x-text="title"></h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="message"></p>
            </div>
            
            <!-- Botão fechar -->
            <button @click="hide()" 
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-200 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-700">
                <i class="bi bi-x text-lg"></i>
            </button>
        </div>
        
        <!-- Barra de progresso (apenas durante o carregamento) -->
        <div x-show="status === 'loading'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-2 mb-3 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-500 ease-out" 
                 :style="`width: ${progress}%`"></div>
        </div>
        
        <!-- Barra de Progresso (apenas durante carregamento) -->
        <div x-show="status === 'loading'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mb-3">
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-2">
                <span>Processando PDF...</span>
                <span x-text="Math.round(progress) + '%'"></span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 h-2.5 rounded-full transition-all duration-500 ease-out" 
                     :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
        
        <!-- Detalhes do progresso -->
        <div x-show="status === 'loading'" class="text-xs text-gray-500 dark:text-gray-400 mb-3" x-text="`${Math.round(progress)}% concluído`"></div>
        
        <!-- Botão de ação (apenas no sucesso) -->
        <div x-show="status === 'success'" 
             x-transition:enter="transition ease-out duration-300 delay-150"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mt-3">
            <div class="flex items-center space-x-2 text-xs text-green-600 dark:text-green-400 mb-2">
                <i class="bi bi-download"></i>
                <span>PDF pronto para download</span>
            </div>
        </div>
        
        <!-- Mensagem de erro (apenas no erro) -->
        <div x-show="status === 'error'" 
             x-transition:enter="transition ease-out duration-300 delay-150"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mt-3">
            <div class="flex items-center space-x-2 text-xs text-red-600 dark:text-red-400">
                <i class="bi bi-exclamation-circle"></i>
                <span>Tente novamente em alguns instantes</span>
            </div>
        </div>
    </div>
</div>

<script>
function downloadNotification() {
    return {
        show: false,
        status: 'loading', // loading, success, error
        title: '',
        message: '',
        progress: 0,
        progressInterval: null,
        hideTimeout: null,
        
        startDownload(detail) {
            // Limpar timeouts anteriores
            clearTimeout(this.hideTimeout);
            clearInterval(this.progressInterval);
            
            this.show = true;
            this.status = 'loading';
            this.title = 'Gerando PDF';
            this.message = detail?.message || 'Preparando documento para download...';
            this.progress = 0;
            
            // Simular progresso realístico
            this.simulateProgress();
        },
        
        completeDownload() {
            clearInterval(this.progressInterval);
            this.status = 'success';
            this.title = 'PDF Gerado com Sucesso!';
            this.message = 'Seu download foi iniciado automaticamente.';
            this.progress = 100;
            
            // Auto-ocultar após 3 segundos
            this.hideTimeout = setTimeout(() => {
                this.hide();
            }, 3000);
        },
        
        errorDownload(detail) {
            clearInterval(this.progressInterval);
            this.status = 'error';
            this.title = 'Erro ao Gerar PDF';
            this.message = detail?.message || 'Falha ao gerar o PDF. Tente novamente.';
            this.progress = 0;
            
            // Auto-ocultar após 6 segundos para dar tempo de ler o erro
            this.hideTimeout = setTimeout(() => {
                this.hide();
            }, 6000);
        },
        
        simulateProgress() {
            this.progress = 0;
            let step = 0;
            
            this.progressInterval = setInterval(() => {
                step++;
                
                // Progresso mais realístico para geração de PDF
                if (step <= 3) {
                    this.progress += 20; // Início: preparação rápida (0-60%)
                    this.message = 'Carregando dados da venda...';
                } else if (step <= 6) {
                    this.progress += 10; // Meio: processamento (60-90%)
                    this.message = 'Processando produtos e layout...';
                } else if (step <= 10) {
                    this.progress += 2; // Final: renderização lenta (90-98%)
                    this.message = 'Finalizando geração do PDF...';
                }
                
                // Não passar de 98% enquanto não receber confirmação
                if (this.progress > 98) this.progress = 98;
                
                // Parar se chegar no limite
                if (this.progress >= 98) {
                    clearInterval(this.progressInterval);
                    this.message = 'Aguarde, quase pronto...';
                }
            }, 200); // Intervalo um pouco mais lento para parecer mais real
        },
        
        hide() {
            clearInterval(this.progressInterval);
            clearTimeout(this.hideTimeout);
            this.show = false;
        }
    }
}
</script>
