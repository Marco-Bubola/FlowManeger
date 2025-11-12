<div class="w-full">
    <!-- Score de Fidelidade -->
    <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Score de Fidelidade</h3>
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold">{{ $scoreFidelidade }}</span>
            </div>
        </div>

        <div class="progress-bar bg-white/20">
            <div class="progress-bar-fill" style="width: {{ $scoreFidelidade }}%"></div>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
            <div class="text-center">
                <div class="font-semibold">
                    @if($scoreFidelidade >= 80) Excelente
                    @elseif($scoreFidelidade >= 60) Bom
                    @elseif($scoreFidelidade >= 40) Regular
                    @else Baixo
                    @endif
                </div>
                <div class="text-purple-100">Nível</div>
            </div>
            <div class="text-center">
                <div class="font-semibold">{{ $client->sales->count() }}</div>
                <div class="text-purple-100">Compras</div>
            </div>
            <div class="text-center">
                <div class="font-semibold">
                    @if($previsaoProximaCompra)
                        {{ abs($previsaoProximaCompra['dias_restantes']) }}d
                    @else
                        N/A
                    @endif
                </div>
                <div class="text-purple-100">
                    @if($previsaoProximaCompra && $previsaoProximaCompra['dias_restantes'] < 0)
                        Atrasado
                    @else
                        Próxima
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Frequência de Compras -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <i class="bi bi-clock-history text-indigo-600 dark:text-indigo-400 mr-2"></i>
            Padrão de Compras
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Frequência -->
            <div class="space-y-4">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Intervalo Médio</span>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $frequenciaCompras['intervalo_medio'] ?? 0 }} dias
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Regularidade: {{ $frequenciaCompras['regularidade'] ?? 'N/A' }}
                    </div>
                </div>

                @if($previsaoProximaCompra)
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Próxima Compra Prevista</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                            {{ $previsaoProximaCompra['data'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">
                            @if($previsaoProximaCompra['dias_restantes'] > 0)
                                Em {{ $previsaoProximaCompra['dias_restantes'] }} dias
                            @elseif($previsaoProximaCompra['dias_restantes'] == 0)
                                Hoje!
                            @else
                                {{ abs($previsaoProximaCompra['dias_restantes']) }} dias atrasado
                            @endif
                        </span>
                        <span class="text-green-600 dark:text-green-400 font-medium">
                            {{ $previsaoProximaCompra['probabilidade'] }}% confiança
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Preferências -->
            <div class="space-y-4">
                @if($categoriaPreferida)
                <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoria Preferida</span>
                        <span class="text-lg font-bold text-orange-600 dark:text-orange-400">
                            {{ $categoriaPreferida }}
                        </span>
                    </div>
                </div>
                @endif

                @if($horarioPreferido)
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Horário Preferido</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                            {{ $horarioPreferido['hora'] }}h
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $horarioPreferido['periodo'] }} ({{ $horarioPreferido['total_vendas'] }} compras)
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sazonalidade -->
    @if(count($sazonalidade) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <i class="bi bi-calendar-range text-indigo-600 dark:text-indigo-400 mr-2"></i>
            Sazonalidade de Compras
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($sazonalidade as $mes)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-lg p-3 text-center">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $mes['mes'] }}</div>
                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $mes['vendas'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        R$ {{ number_format($mes['valor'], 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Dias da Semana Preferidos -->
    @if(count($diasSemanaPreferidos) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <i class="bi bi-calendar-week text-indigo-600 dark:text-indigo-400 mr-2"></i>
            Dias da Semana Preferidos
        </h3>

        <div class="space-y-3">
            @foreach(array_slice($diasSemanaPreferidos, 0, 3) as $index => $dia)
                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-blue-900/20 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ $index + 1 }}
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $dia['dia'] }}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $dia['total'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">compras</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tendências -->
    @if(isset($tendencias['tendencia']))
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <i class="bi bi-graph-up-arrow text-indigo-600 dark:text-indigo-400 mr-2"></i>
            Análise de Tendências
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tendência Geral -->
            <div class="bg-gradient-to-br from-{{ $tendencias['tendencia'] === 'Crescente' ? 'green' : ($tendencias['tendencia'] === 'Decrescente' ? 'red' : 'gray') }}-50
                        to-{{ $tendencias['tendencia'] === 'Crescente' ? 'emerald' : ($tendencias['tendencia'] === 'Decrescente' ? 'pink' : 'slate') }}-50
                        dark:from-{{ $tendencias['tendencia'] === 'Crescente' ? 'green' : ($tendencias['tendencia'] === 'Decrescente' ? 'red' : 'gray') }}-900/20
                        dark:to-{{ $tendencias['tendencia'] === 'Crescente' ? 'emerald' : ($tendencias['tendencia'] === 'Decrescente' ? 'pink' : 'slate') }}-900/20 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">
                    @if($tendencias['tendencia'] === 'Crescente')
                        📈
                    @elseif($tendencias['tendencia'] === 'Decrescente')
                        📉
                    @else
                        ➡️
                    @endif
                </div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $tendencias['tendencia'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $tendencias['percentual'] > 0 ? '+' : '' }}{{ $tendencias['percentual'] }}%
                </div>
            </div>

            <!-- Período de Análise -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📅</div>
                <div class="font-semibold text-gray-900 dark:text-white">Últimos 12 Meses</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ count($tendencias['dados']) }} pontos de dados</div>
            </div>

            <!-- Recomendação -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">💡</div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    @if($tendencias['tendencia'] === 'Crescente')
                        Manter Estratégia
                    @elseif($tendencias['tendencia'] === 'Decrescente')
                        Atenção Necessária
                    @else
                        Buscar Crescimento
                    @endif
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Recomendação</div>
            </div>
        </div>
    </div>
    @endif
</div>
