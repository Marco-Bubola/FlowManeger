<!-- Instruções de Upload Modernas -->
<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200/50 dark:border-blue-700/50 rounded-2xl p-8 shadow-lg">
    <!-- Cabeçalho -->
    <div class="flex items-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="bi bi-info-circle-fill text-white text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-blue-800 dark:text-blue-200">
                Instruções para Upload
            </h3>
            <p class="text-blue-600 dark:text-blue-400 text-sm">
                Siga as diretrizes abaixo para um upload bem-sucedido
            </p>
        </div>
    </div>

    <!-- Grid de Instruções -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Arquivo PDF -->
        <div class="space-y-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="bi bi-file-earmark-pdf text-white text-lg"></i>
                </div>
                <h4 class="text-lg font-bold text-blue-700 dark:text-blue-300">
                    Arquivo PDF
                </h4>
            </div>

            <div class="space-y-3 pl-13">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Extração automática:</strong> O sistema reconhece produtos, códigos e preços automaticamente
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Formato específico:</strong> PDFs de catálogos ou listas de produtos estruturadas
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Qualidade:</strong> Textos claros e legíveis para melhor reconhecimento
                    </p>
                </div>
            </div>

            <!-- Exemplo Visual PDF -->
            <div class="bg-white/70 dark:bg-neutral-800/50 rounded-lg p-4 border border-red-200 dark:border-red-700">
                <div class="text-xs text-red-700 dark:text-red-300 font-mono">
                    <div class="font-bold mb-1">📄 Exemplo de PDF estruturado:</div>
                    <div class="opacity-75">
                        Produto: Notebook Dell XPS<br>
                        Código: NB001<br>
                        Preço: R$ 2.500,00<br>
                        ---<br>
                        Produto: Mouse Wireless<br>
                        Código: MS002<br>
                        Preço: R$ 85,00
                    </div>
                </div>
            </div>
        </div>

        <!-- Arquivo CSV -->
        <div class="space-y-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="bi bi-file-earmark-spreadsheet text-white text-lg"></i>
                </div>
                <h4 class="text-lg font-bold text-blue-700 dark:text-blue-300">
                    Arquivo CSV
                </h4>
            </div>

            <div class="space-y-3 pl-13">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Estrutura:</strong> Nome, código, preço de custo, preço de venda, quantidade
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Separador:</strong> Vírgulas (,) para separar os campos
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        <strong>Encoding:</strong> UTF-8 para caracteres especiais
                    </p>
                </div>
            </div>

            <!-- Exemplo Visual CSV -->
            <div class="bg-white/70 dark:bg-neutral-800/50 rounded-lg p-4 border border-emerald-200 dark:border-emerald-700">
                <div class="text-xs text-emerald-700 dark:text-emerald-300 font-mono">
                    <div class="font-bold mb-1">📊 Exemplo de CSV:</div>
                    <div class="opacity-75">
                        nome,codigo,preco,preco_venda,quantidade<br>
                        "Notebook Dell XPS",NB001,2000.00,2500.00,5<br>
                        "Mouse Wireless",MS002,60.00,85.00,25<br>
                        "Teclado Mecânico",TC003,150.00,220.00,10
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dicas Adicionais -->
    <div class="mt-8 pt-6 border-t border-blue-200 dark:border-blue-700">
        <h5 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4 flex items-center">
            <i class="bi bi-lightbulb text-yellow-500 mr-2"></i>
            Dicas para um upload perfeito
        </h5>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <i class="bi bi-file-check text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div>
                    <h6 class="font-medium text-blue-700 dark:text-blue-300 text-sm">Tamanho do arquivo</h6>
                    <p class="text-blue-600 dark:text-blue-400 text-xs">Máximo de 2MB para melhor performance</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <i class="bi bi-shield-check text-purple-600 dark:text-purple-400"></i>
                </div>
                <div>
                    <h6 class="font-medium text-blue-700 dark:text-blue-300 text-sm">Segurança</h6>
                    <p class="text-blue-600 dark:text-blue-400 text-xs">Arquivos são processados de forma segura</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <i class="bi bi-arrow-clockwise text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h6 class="font-medium text-blue-700 dark:text-blue-300 text-sm">Revisão</h6>
                    <p class="text-blue-600 dark:text-blue-400 text-xs">Sempre revise antes de salvar os produtos</p>
                </div>
            </div>
        </div>
    </div>
</div>
