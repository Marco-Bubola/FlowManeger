<div class="w-full h-full bg-slate-900 text-white">
    <div class="flex h-full">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Header -->
            <div class="relative bg-slate-800/50 backdrop-blur-xl border-b border-slate-700/50 p-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl shadow-lg flex items-center justify-center">
                            <i class="fas fa-wallet text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Fluxo de Caixa</h1>
                            <p class="text-slate-400">Controle Completo das Suas Finanças</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div>
                            <label for="ano" class="text-sm font-medium text-slate-300">Ano</label>
                            <select id="ano" wire:model.live="ano" class="w-full bg-slate-700 border-slate-600 rounded-md shadow-sm text-white">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="mes" class="text-sm font-medium text-slate-300">Mês</label>
                            <select id="mes" wire:model.live="mes" class="w-full bg-slate-700 border-slate-600 rounded-md shadow-sm text-white">
                                @foreach (range(1, 12) as $mes)
                                    <option value="{{ $mes }}">{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(count($cofrinhos) > 0)
                        <div>
                            <label for="cofrinhoFiltro" class="text-sm font-medium text-slate-300">Filtrar por Cofrinho</label>
                            <div class="flex gap-2">
                                <select id="cofrinhoFiltro" wire:model.live="cofrinhoFiltro" class="bg-slate-700 border-slate-600 rounded-md shadow-sm text-white">
                                    <option value="">Todos</option>
                                    @foreach($cofrinhos as $cofrinho)
                                        <option value="{{ $cofrinho['id'] }}">{{ $cofrinho['nome'] }}</option>
                                    @endforeach
                                </select>
                                @if($cofrinhoFiltro)
                                <button wire:click="clearCofrinhoFilter" class="px-3 py-2 bg-red-600 hover:bg-red-500 rounded-md text-white text-xs">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div>
                            <a href="{{ route('dashboard.banks') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 disabled:opacity-25 transition">
                                Dashboard Bancos
                            </a>
                        </div>
                        <div>
                            <button wire:click="exportExcel" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 disabled:opacity-25 transition">
                                Exportar Excel
                            </button>
                        </div>
                        <div>
                            <button wire:click="exportPdf" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition">
                                Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-6 space-y-6">
                <!-- KPIs Linha 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div wire:click="setFilter(null)"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === null) border-2 border-blue-500 shadow-lg shadow-blue-500/20 @endif">
                        <p class="text-sm text-slate-400">Saldo Total</p>
                        <p class="text-2xl font-bold">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</p>
                    </div>
                    <div wire:click="setFilter('receitas')"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === 'receitas') border-2 border-green-500 shadow-lg shadow-green-500/20 @endif">
                        <p class="text-sm text-slate-400">Total Receitas ({{ $ano }})</p>
                        <p class="text-2xl font-bold text-green-500">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
                    </div>
                    <div wire:click="setFilter('despesas')"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === 'despesas') border-2 border-red-500 shadow-lg shadow-red-500/20 @endif">
                        <p class="text-sm text-slate-400">Total Despesas ({{ $ano }})</p>
                        <p class="text-2xl font-bold text-red-500">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-4">
                        <p class="text-sm text-slate-400">Resultado Líquido ({{ $ano }})</p>
                        <p class="text-2xl font-bold {{ $saldoTotal >= 0 ? 'text-green-500' : 'text-red-500' }}">
                            R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- KPIs Linha 2 - Cofrinhos -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-white/80">Total em Cofrinhos</p>
                            <i class="fas fa-piggy-bank text-white/60 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-white">R$ {{ number_format($totalCofrinhos, 2, ',', '.') }}</p>
                        <p class="text-xs text-white/70 mt-1">{{ count($cofrinhos) }} cofrinho(s)</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-white/80">Total de Metas</p>
                            <i class="fas fa-bullseye text-white/60 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-white">R$ {{ number_format($totalMetasCofrinhos, 2, ',', '.') }}</p>
                        <p class="text-xs text-white/70 mt-1">
                            {{ $totalCofrinhos > 0 ? number_format(($totalCofrinhos / $totalMetasCofrinhos) * 100, 1) : 0 }}% alcançado
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-green-600 to-emerald-600 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-white/80">Economizado este Mês</p>
                            <i class="fas fa-arrow-circle-down text-white/60 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-white">R$ {{ number_format($economiadoMesAtual, 2, ',', '.') }}</p>
                        @php
                            $variacao = $economiadoMesAnterior > 0 ? (($economiadoMesAtual - $economiadoMesAnterior) / $economiadoMesAnterior) * 100 : 0;
                        @endphp
                        <p class="text-xs text-white/70 mt-1">
                            <i class="fas fa-{{ $variacao >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ number_format(abs($variacao), 1) }}% vs mês anterior
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-600 to-red-600 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-white/80">Faltante para Metas</p>
                            <i class="fas fa-flag-checkered text-white/60 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-white">R$ {{ number_format($totalMetasCofrinhos - $totalCofrinhos, 2, ',', '.') }}</p>
                        <p class="text-xs text-white/70 mt-1">Para atingir todas as metas</p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Fluxo de Caixa Mensal ({{ $ano }})</h3>
                        <div id="cashFlowChart" class="h-80"></div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Evolução dos Cofrinhos ({{ $ano }})</h3>
                        <div id="cofrinhosEvolutionChart" class="h-80"></div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Receitas vs Despesas Mensais</h3>
                        <div id="revenueExpenseChart" class="h-80"></div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Gastos Mensais de Invoices</h3>
                        <div id="gastosMensaisChart" class="h-80"></div>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Gastos Diários - Invoices ({{ \Carbon\Carbon::create($ano, $mes)->locale('pt_BR')->isoFormat('MMMM/YYYY') }})</h3>
                        <div id="dailyInvoicesChart" class="h-80"></div>
                    </div>
                </div>
                 @if(count($categorias) > 0)
                <div class="bg-slate-800 rounded-xl p-6">
                    <h3 class="text-lg font-bold mb-4">Top 10 Categorias de Invoices</h3>
                    <div id="categoriasChart" class="h-96"></div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="w-1/3 h-full bg-slate-800/50 border-l border-slate-700/50 overflow-y-auto p-6 space-y-6">
            @include('livewire.dashboard.components.calendar', ['cashbookDays' => $cashbookDays, 'invoiceDays' => $invoiceDays])

            @if ($selectedDate)
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Detalhes do Dia {{ $selectedDate }}</h3>
                <div wire:loading wire:target="showDayDetails">
                    <p class="text-slate-400">Carregando...</p>
                </div>
                <div wire:loading.remove wire:target="showDayDetails" class="space-y-4">
                    <div>
                        <h4 class="font-bold text-green-500">Receitas</h4>
                        @forelse ($dayDetails['receitas'] as $receita)
                            <div class="flex justify-between">
                                <span>{{ $receita['description'] }}</span>
                                <span>R$ {{ number_format($receita['value'], 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400">Nenhuma receita neste dia.</p>
                        @endforelse
                    </div>
                    <div>
                        <h4 class="font-bold text-red-500">Despesas</h4>
                        @forelse ($dayDetails['despesas'] as $despesa)
                            <div class="flex justify-between">
                                <span>{{ $despesa['description'] }}</span>
                                <span>R$ {{ number_format($despesa['value'], 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400">Nenhuma despesa neste dia.</p>
                        @endforelse
                    </div>
                    <div>
                        <h4 class="font-bold text-orange-500">Invoices</h4>
                        @forelse ($dayDetails['invoices'] as $invoice)
                            <div class="flex justify-between">
                                <span>{{ $invoice['description'] }}</span>
                                <span>R$ {{ number_format($invoice['value'], 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400">Nenhuma invoice neste dia.</p>
                        @endforelse
                    </div>
                    <div>
                        <h4 class="font-bold text-purple-500 flex items-center">
                            <i class="fas fa-piggy-bank mr-2"></i>Cofrinhos
                        </h4>
                        @forelse ($dayDetails['cofrinhos'] as $cofrinho)
                            <div class="flex justify-between items-center py-1">
                                <div>
                                    <span class="text-sm">{{ $cofrinho['cofrinho']['nome'] ?? 'Cofrinho' }}</span>
                                    <span class="text-xs text-slate-500 ml-2">
                                        ({{ $cofrinho['type_id'] == 1 ? 'Depósito' : 'Retirada' }})
                                    </span>
                                </div>
                                <span class="{{ $cofrinho['type_id'] == 1 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $cofrinho['type_id'] == 1 ? '+' : '-' }} R$ {{ number_format($cofrinho['value'], 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-400">Nenhuma movimentação de cofrinho.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            @if(count($cofrinhos) > 0)
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4 flex items-center">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                    Top Cofrinhos Próximos da Meta
                </h3>
                <div class="space-y-3">
                    @forelse($cofrinhosTopMeta as $cofrinho)
                        <a href="{{ $cofrinho['link'] }}" class="block hover:bg-slate-700/50 rounded-lg p-3 transition-all">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-white">{{ $cofrinho['nome'] }}</span>
                                <span class="text-xs font-bold text-green-400">{{ number_format($cofrinho['progresso'], 1) }}%</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2 mb-1">
                                <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full transition-all"
                                     style="width: {{ min($cofrinho['progresso'], 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400">
                                <span>R$ {{ number_format($cofrinho['valor_guardado'], 2, ',', '.') }}</span>
                                <span>R$ {{ number_format($cofrinho['meta_valor'], 2, ',', '.') }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-400 text-sm">Todas as metas alcançadas! 🎉</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Meus Cofrinhos</h3>
                <div class="space-y-4">
                    @foreach($cofrinhos as $cofrinho)
                        <a href="{{ $cofrinho['link'] }}" class="block hover:bg-slate-700/50 rounded-lg p-2">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">{{ $cofrinho['nome'] }}</span>
                                    <span class="text-sm font-medium">{{ number_format($cofrinho['progresso'], 2, ',', '.') }}%</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2.5">
                                    <div class="bg-blue-500 h-2.5 rounded-full progress-bar" style="width: {{ $cofrinho['progresso'] }}%"></div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Orçamento do Mês</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm">Total Orçado:</span>
                        <span class="font-bold">R$ {{ number_format($orcamentoMesTotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Total Gasto:</span>
                        <span class="font-bold text-red-500">R$ {{ number_format($orcamentoMesUsado, 2, ',', '.') }}</span>
                    </div>
                </div>

                @if(!empty($orcamentosTopEstouro))
                    <div class="mt-4">
                        <h4 class="font-bold text-orange-500">Top Categorias com Estouro</h4>
                        <div class="space-y-2 mt-2">
                            @foreach($orcamentosTopEstouro as $estouro)
                                <div class="flex justify-between">
                                    <span class="text-sm">{{ $estouro['category'] }}</span>
                                    <span class="text-sm font-bold text-red-500">+ R$ {{ number_format($estouro['estouro'], 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Previsão de Saldo</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm">Próximos 30 dias:</span>
                        <span class="font-bold">R$ {{ number_format($previsao30dias, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Próximos 60 dias:</span>
                        <span class="font-bold">R$ {{ number_format($previsao60dias, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Próximos 90 dias:</span>
                        <span class="font-bold">R$ {{ number_format($previsao90dias, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Transações Recentes</h3>
                <div class="space-y-2">
                    @forelse ($recentTransactions as $transaction)
                        <div class="flex justify-between">
                            <span>{{ $transaction['description'] ?? 'N/A' }}</span>
                            <span class="{{ ($transaction['type_id'] ?? 2) == 1 ? 'text-green-500' : 'text-red-500' }}">
                                R$ {{ number_format($transaction['value'], 2, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-400">Nenhuma transação recente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ApexCharts CDN e Scripts -->
    <style>
        .apexcharts-legend-text, .apexcharts-xaxis-label, .apexcharts-yaxis-label { color: #94a3b8 !important; }
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dadosReceita = @json($dadosReceita);
            const dadosDespesa = @json($dadosDespesa);
            const saldosMes = @json($saldosMes);
            const evolucaoCofrinhos = @json($evolucaoCofrinhos);
            const diasInvoices = @json($diasInvoices);
            const valoresInvoices = @json($valoresInvoices);
            const gastosMensaisMeses = @json($gastosMensaisMeses ?? []);
            const gastosPorCategoria = @json($gastosMensaisPorCategoria ?? []);
            const gastosPorCatBank = @json($gastosMensaisPorCategoriaBanco ?? []);
            const categorias = @json($categorias ?? []);
            const valoresCategorias = @json($valoresCategorias ?? []);

            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

            const cashFlowOptions = {
                series: [{ name: 'Saldo Mensal', data: saldosMes }],
                chart: { type: 'area', height: 320, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                colors: ['#06b6d4'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
                xaxis: { categories: meses, labels: { style: { colors: '#94a3b8' } } },
                yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#94a3b8' } } },
                grid: { borderColor: '#334155' },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                tooltip: { theme: 'dark', y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
            };
            new ApexCharts(document.querySelector("#cashFlowChart"), cashFlowOptions).render();

            // Gráfico de Evolução dos Cofrinhos
            const cofrinhosEvolutionOptions = {
                series: [{ name: 'Valor Acumulado', data: evolucaoCofrinhos }],
                chart: { type: 'area', height: 320, toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
                colors: ['#a855f7'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        colorStops: [
                            { offset: 0, color: '#a855f7', opacity: 0.7 },
                            { offset: 100, color: '#ec4899', opacity: 0.2 }
                        ]
                    }
                },
                xaxis: { categories: meses, labels: { style: { colors: '#94a3b8' } } },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        },
                        style: { colors: '#94a3b8' }
                    }
                },
                grid: { borderColor: '#334155' },
                markers: {
                    size: 4,
                    colors: ['#a855f7'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: { size: 6 }
                },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            };
            new ApexCharts(document.querySelector("#cofrinhosEvolutionChart"), cofrinhosEvolutionOptions).render();

            const revenueExpenseOptions = {
                series: [{ name: 'Receitas', data: dadosReceita }, { name: 'Despesas', data: dadosDespesa }],
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                colors: ['#10b981', '#ef4444'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                xaxis: { categories: meses, labels: { style: { colors: '#94a3b8' } } },
                yaxis: { labels: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }, style: { colors: '#94a3b8' } } },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                grid: { borderColor: '#334155' },
                tooltip: { theme: 'dark', y: { formatter: function(value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } }
            };
            new ApexCharts(document.querySelector("#revenueExpenseChart"), revenueExpenseOptions).render();

            if (document.querySelector("#gastosMensaisChart")) {
                const seriesData = [];
                const palette = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#f97316', '#ef4444', '#0ea5a4', '#a78bfa'];
                const sourceData = Object.keys(gastosPorCatBank).length > 0 ? gastosPorCatBank : gastosPorCategoria;

                for (const [label, valores] of Object.entries(sourceData)) {
                    seriesData.push({ name: label, data: valores });
                }

                const gastosMensaisOptions = {
                    series: seriesData,
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, stacked: true },
                    colors: palette,
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '70%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: gastosMensaisMeses, labels: { style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }), style: { colors: '#94a3b8' } } },
                    grid: { borderColor: '#334155' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                    tooltip: { theme: 'dark', y: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) } }
                };
                new ApexCharts(document.querySelector("#gastosMensaisChart"), gastosMensaisOptions).render();
            }

            if (document.querySelector("#dailyInvoicesChart")) {
                const dailyInvoicesOptions = {
                    series: [{ name: 'Gastos', data: valoresInvoices }],
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#f97316'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '70%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: diasInvoices, labels: { style: { colors: '#94a3b8' }, rotate: -45, rotateAlways: false } },
                    yaxis: { labels: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }), style: { colors: '#94a3b8' } } },
                    grid: { borderColor: '#334155' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                    tooltip: { theme: 'dark', y: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) } }
                };
                new ApexCharts(document.querySelector("#dailyInvoicesChart"), dailyInvoicesOptions).render();
            }

            if (categorias.length > 0 && document.querySelector("#categoriasChart")) {
                const categoriasOptions = {
                    series: [{ name: 'Total Gastos', data: valoresCategorias }],
                    chart: { type: 'bar', height: 384, toolbar: { show: false } },
                    colors: ['#8b5cf6'],
                    plotOptions: { bar: { horizontal: true, borderRadius: 8, dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: true, formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }), offsetX: 0, style: { fontSize: '12px', colors: ['#fff'] } },
                    xaxis: { categories: categorias, labels: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }), style: { colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '13px' }, maxWidth: 200 } },
                    grid: { borderColor: '#334155' },
                    legend: { position: 'top', horizontalAlign: 'right', labels: { colors: ['#e2e8f0'] } },
                    tooltip: { theme: 'dark', y: { formatter: (value) => 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) } }
                };
                new ApexCharts(document.querySelector("#categoriasChart"), categoriasOptions).render();
            }
        });
    </script>
</div>
