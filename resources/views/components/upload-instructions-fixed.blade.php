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

            <div class="space-y-3 ml-13">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Catálogo de produtos com preços e descrições
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Formato de tabela bem estruturado
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Texto legível e não digitalizado
                    </p>
                </div>
            </div>
        </div>

        <!-- Arquivo CSV -->
        <div class="space-y-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="bi bi-file-earmark-excel text-white text-lg"></i>
                </div>
                <h4 class="text-lg font-bold text-blue-700 dark:text-blue-300">
                    Arquivo CSV
                </h4>
            </div>

            <div class="space-y-3 ml-13">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Colunas: Nome, Preço, Descrição, Categoria
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Separado por vírgulas ou ponto e vírgula
                    </p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm">
                        Primeira linha com cabeçalhos
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dicas Importantes -->
    <div class="mt-8 p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-700 rounded-xl">
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="bi bi-lightbulb text-white text-sm"></i>
            </div>
            <div class="space-y-2">
                <h5 class="font-bold text-amber-800 dark:text-amber-200">Dicas Importantes</h5>
                <div class="space-y-2">
                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                        • Certifique-se de que os preços estão em formato numérico
                    </p>
                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                        • Evite caracteres especiais nos nomes dos produtos
                    </p>
                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                        • Verifique se não há linhas em branco no arquivo
                    </p>
                    <p class="text-blue-600 dark:text-blue-400 text-xs">Sempre revise antes de salvar os produtos</p>
                </div>
            </div>
        </div>
    </div>
</div>
