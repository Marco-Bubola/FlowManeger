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
                            <label for="mesInvoices" class="text-sm font-medium text-slate-300">Mês Invoices</label>
                            <select id="mesInvoices" wire:model.live="mesInvoices" class="w-full bg-slate-700 border-slate-600 rounded-md shadow-sm text-white">
                                @foreach (range(1, 12) as $mes)
                                    <option value="{{ $mes }}">{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="anoInvoices" class="text-sm font-medium text-slate-300">Ano Invoices</label>
                            <select id="anoInvoices" wire:model.live="anoInvoices" class="w-full bg-slate-700 border-slate-600 rounded-md shadow-sm text-white">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-6 space-y-6">
                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div wire:click="setFilter(null)"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === null) border-2 border-blue-500 shadow-lg shadow-blue-500/20 @endif">
                        <p class="text-sm text-slate-400">Saldo Total</p>
                        <p class="text-2xl font-bold">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</p>
                    </div>
                    <div wire:click="setFilter('receitas')"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === 'receitas') border-2 border-green-500 shadow-lg shadow-green-500/20 @endif">
                        <p class="text-sm text-slate-400">Total Receitas</p>
                        <p class="text-2xl font-bold text-green-500">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
                    </div>
                    <div wire:click="setFilter('despesas')"
                         class="bg-slate-800 rounded-xl p-4 cursor-pointer transition-all duration-200 @if($filterType === 'despesas') border-2 border-red-500 shadow-lg shadow-red-500/20 @endif">
                        <p class="text-sm text-slate-400">Total Despesas</p>
                        <p class="text-2xl font-bold text-red-500">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-4">
                        <p class="text-sm text-slate-400">Resultado Líquido</p>
                        <p class="text-2xl font-bold {{ $saldoTotal >= 0 ? 'text-green-500' : 'text-red-500' }}">
                            R$ {{ number_format($saldoTotal, 2, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-800 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-4">Fluxo de Caixa Mensal ({{ $ano }})</h3>
                        <div id="cashFlowChart" class="h-80"></div>
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
                        <h3 class="text-lg font-bold mb-4">Gastos Diários - Invoices ({{ \Carbon\Carbon::create($anoInvoices, $mesInvoices)->locale('pt_BR')->isoFormat('MMMM/YYYY') }})</h3>
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
                </div>
            </div>
            @endif

            @if(count($cofrinhos) > 0)
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="text-lg font-bold mb-4">Meus Cofrinhos</h3>
                <div class="space-y-4">
                    @foreach($cofrinhos as $cofrinho)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">{{ $cofrinho['nome'] }}</span>
                                <span class="text-sm font-medium">{{ number_format($cofrinho['progresso'], 2, ',', '.') }}%</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full progress-bar" style="width: {{ $cofrinho['progresso'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

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
