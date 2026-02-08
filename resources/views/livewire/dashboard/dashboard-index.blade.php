<div class="min-h-screen w-full bg-slate-50 dark:bg-slate-950">
    {{-- Top Header + Conteúdo --}}
    <div>

        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                {{-- KPIs --}}
                <div class="xl:col-span-12">
                    @include('livewire.dashboard.partials.kpis-grid')
                </div>
                {{-- Charts: Financeiro (4 charts) --}}
                <div class="xl:col-span-12">
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                        {{-- Fluxo de caixa 12 meses --}}
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                        <i class="fas fa-wave-square text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Fluxo de
                                            Caixa</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">12 meses</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="cashflowMonthlyChart" class="h-56"></div>
                            </div>
                        </div>
                        {{-- Despesas por categoria (donut) --}}
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center shadow">
                                        <i class="fas fa-chart-pie text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Despesas
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Top 10 mês</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="expensesByCategoryChart" class="h-56"></div>
                            </div>
                        </div>
                        {{-- Invoices por banco (linha) --}}
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow">
                                        <i class="fas fa-credit-card text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Invoices
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Por banco (6m)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="invoicesByBankChart" class="h-56"></div>
                            </div>
                        </div>
                        {{-- Vendas vs custos (bar) --}}
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow">
                                        <i class="fas fa-chart-bar text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Vendas x
                                            Custos</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Visão geral</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <div id="salesVsCostsChart" class="h-56"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Sidebar --}}
                <div class="xl:col-span-4 grid grid-cols-1 gap-4">
                    {{-- Notificações (na sidebar) --}}
                    <div>
                        @include('livewire.dashboard.partials.alertas')
                    </div>
                    {{-- Total Economizado --}}
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center shadow">
                                    <i class="fas fa-piggy-bank text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Total
                                        Economizado</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Cofrinhos</div>
                                </div>
                            </div>
                            <a href="{{ route('cofrinhos.index') }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Ver</a>
                        </div>
                        <div class="px-4 pb-4">
                            <p class="text-3xl font-bold text-pink-700 dark:text-pink-400">
                                R$ {{ number_format($totalEconomizado, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Pendências 30 dias (compacto) --}}
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow">
                                    <i class="fas fa-file-invoice-dollar text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pendências
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Janela de 30 dias</div>
                                </div>
                            </div>
                            <a href="{{ route('invoices.index') }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Invoices</a>
                        </div>
                        <div class="px-4 pb-4 space-y-2">
                            <div
                                class="flex items-center justify-between rounded-xl bg-emerald-50/60 dark:bg-emerald-900/15 p-3">
                                <span class="text-xs text-emerald-800 dark:text-emerald-200">A receber (parcelas)</span>
                                <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">R$
                                    {{ number_format($contasReceberPendentes, 2, ',', '.') }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-orange-50/60 dark:bg-orange-900/15 p-3">
                                <span class="text-xs text-orange-800 dark:text-orange-200">Invoices (30d)</span>
                                <span class="text-sm font-bold text-orange-700 dark:text-orange-300">R$
                                    {{ number_format($invoicesProxVenc30Total, 2, ',', '.') }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-3">
                                <span class="text-xs text-red-800 dark:text-red-200">A pagar (aprox.)</span>
                                <span class="text-sm font-bold text-red-700 dark:text-red-300">R$
                                    {{ number_format($contasPagarPendentes, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Consórcios (resumo) --}}
                    <div
                        class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow">
                                    <i class="fas fa-layer-group text-white"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Consórcios
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Resumo do módulo</div>
                                </div>
                            </div>
                            <a href="{{ route('consortiums.index') }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                        </div>
                        <div class="px-4 pb-4 space-y-2">
                            <div
                                class="flex items-center justify-between rounded-xl bg-teal-50/60 dark:bg-teal-900/15 p-3">
                                <span class="text-xs text-teal-800 dark:text-teal-200">Ativos</span>
                                <span
                                    class="text-sm font-bold text-teal-700 dark:text-teal-300">{{ $totalConsorciosAtivos }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-blue-50/60 dark:bg-blue-900/15 p-3">
                                <span class="text-xs text-blue-800 dark:text-blue-200">Participantes ativos</span>
                                <span
                                    class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ $consorcioParticipantesAtivos }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-amber-50/60 dark:bg-amber-900/15 p-3">
                                <span class="text-xs text-amber-800 dark:text-amber-200">Pagamentos pendentes</span>
                                <span class="text-sm font-bold text-amber-700 dark:text-amber-300">R$
                                    {{ number_format($consorcioPagamentosPendentesTotal, 2, ',', '.') }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-indigo-50/60 dark:bg-indigo-900/15 p-3">
                                <span class="text-xs text-indigo-800 dark:text-indigo-200">Sorteios (30d)</span>
                                <span
                                    class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ $proximosSorteios }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between rounded-xl bg-purple-50/60 dark:bg-purple-900/15 p-3">
                                <span class="text-xs text-purple-800 dark:text-purple-200">Contemplações</span>
                                <span
                                    class="text-sm font-bold text-purple-700 dark:text-purple-300">{{ $consorcioContemplacoesTotal }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Detalhes + Atividades --}}
                <div class="xl:col-span-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                        {{-- Comercial --}}
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center shadow">
                                        <i class="fas fa-shopping-cart text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Comercial
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Vendas e cobranças
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('sales.index') }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Ver</a>
                            </div>
                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Vendas (mês)</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $salesMonth }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Ticket médio</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">R$
                                        {{ number_format($ticketMedio, 2, ',', '.') }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Faturamento Total</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">R$
                                        {{ number_format($totalFaturamento, 2, ',', '.') }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-emerald-50/60 dark:bg-emerald-900/15 p-3">
                                    <span class="text-xs text-emerald-800 dark:text-emerald-200">A receber
                                        (parcelas)</span>
                                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">R$
                                        {{ number_format($contasReceberPendentes, 2, ',', '.') }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-3">
                                    <span class="text-xs text-red-800 dark:text-red-200">Vencidas</span>
                                    <span
                                        class="text-sm font-bold text-red-700 dark:text-red-300">{{ $parcelasVencidasCount }}
                                        (R$ {{ number_format($parcelasVencidasValor, 2, ',', '.') }})</span>
                                </div>
                            </div>
                        </div>

                        {{-- Clientes & Estoque --}}
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center shadow">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Clientes
                                            & Estoque</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Base e alertas</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('clients.index') }}"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Clientes</a>
                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                    <a href="{{ route('products.index') }}"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Produtos</a>
                                </div>
                            </div>
                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Clientes</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $totalClientes }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Novos no mês</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $clientesNovosMes }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-slate-950/40 p-3">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">Produtos
                                        cadastrados</span>
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $produtosCadastrados }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-yellow-50/60 dark:bg-yellow-900/15 p-3">
                                    <span class="text-xs text-yellow-800 dark:text-yellow-200">Estoque baixo</span>
                                    <span
                                        class="text-sm font-bold text-yellow-700 dark:text-yellow-300">{{ $produtosEstoqueBaixo }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Planejamento --}}
                        <div
                            class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl overflow-hidden">
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow">
                                        <i class="fas fa-bullseye text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            Planejamento</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Orçamento e
                                            recorrências</div>
                                    </div>
                                </div>
                                <a href="{{ route('cashbook.index') }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Abrir</a>
                            </div>

                            <div class="px-4 pb-4 space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-xl bg-teal-50/60 dark:bg-teal-900/15 p-3">
                                    <span class="text-xs text-teal-800 dark:text-teal-200">Orçado (mês)</span>
                                    <span class="text-sm font-bold text-teal-700 dark:text-teal-300">R$
                                        {{ number_format($orcamentoMesTotal, 2, ',', '.') }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-rose-50/60 dark:bg-rose-900/15 p-3">
                                    <span class="text-xs text-rose-800 dark:text-rose-200">Gasto (mês)</span>
                                    <span class="text-sm font-bold text-rose-700 dark:text-rose-300">R$
                                        {{ number_format($orcamentoMesUsado, 2, ',', '.') }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-orange-50/60 dark:bg-orange-900/15 p-3">
                                    <span class="text-xs text-orange-800 dark:text-orange-200">Recorrências
                                        ativas</span>
                                    <span
                                        class="text-sm font-bold text-orange-700 dark:text-orange-300">{{ $recorrentesAtivas }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-xl bg-amber-50/60 dark:bg-amber-900/15 p-3">
                                    <span class="text-xs text-amber-800 dark:text-amber-200">Recorrências (30d)</span>
                                    <span class="text-sm font-bold text-amber-700 dark:text-amber-300">R$
                                        {{ number_format($recorrentesProx30Total, 2, ',', '.') }}</span>
                                </div>

                                {{-- Saúde do sistema (uploads) --}}
                                <div class="mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-800">
                                    <div
                                        class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                        <i class="fas fa-heartbeat text-emerald-500"></i>
                                        Saúde do Sistema (uploads)
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Cashbook</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $lastUploads['cashbook']->status ?? '—' }}</div>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Produtos</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $lastUploads['products']->status ?? '—' }}</div>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950/40 p-2">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                Invoices</div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $lastUploads['invoices']->status ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Top estouros --}}
                                @if (!empty($orcamentosTopEstouro))
                                    <div class="mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-800">
                                        <div
                                            class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                                            Top estouros (mês)
                                        </div>
                                        <div class="max-h-24 overflow-auto space-y-2">
                                            @foreach ($orcamentosTopEstouro as $row)
                                                <div
                                                    class="flex items-center justify-between rounded-xl bg-red-50/60 dark:bg-red-900/15 p-2">
                                                    <span
                                                        class="text-xs text-slate-700 dark:text-slate-200 truncate max-w-[70%]">{{ $row['category'] }}</span>
                                                    <span class="text-xs font-bold text-red-700 dark:text-red-300">+R$
                                                        {{ number_format($row['estouro'], 2, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Atividades (últimas 5) --}}
                    <div class="mt-6">
                        @include('livewire.dashboard.partials.atividades')
                    </div>

                </div>

            </div>

            {{-- FAB --}}
            @include('livewire.dashboard.partials.fab-menu')

        </div>


        {{-- ApexCharts --}}
        <style>
            .apexcharts-legend-text {
                color: #0f172a !important;
            }

            .dark .apexcharts-legend-text,
            .apexcharts-theme-dark .apexcharts-legend-text {
                color: #E5E7EB !important;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const cashflowMonthly = @json($cashflowMonthly);
                const expensesByCategory = @json($expensesByCategory);
                const gastosInvoicePorBanco = @json($gastosInvoicePorBanco);

                // 1) Fluxo de caixa (12 meses)
                const cashflowMonthlyOptions = {
                    series: [{
                            name: 'Receitas',
                            data: (cashflowMonthly || []).map(x => Number(x.receitas || 0))
                        },
                        {
                            name: 'Despesas',
                            data: (cashflowMonthly || []).map(x => Number(x.despesas || 0))
                        }
                    ],
                    chart: {
                        type: 'area',
                        height: 240,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    colors: ['#10b981', '#ef4444'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.55,
                            opacityTo: 0.12
                        }
                    },
                    xaxis: {
                        categories: (cashflowMonthly || []).map(x => x.label),
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const cashflowEl = document.querySelector('#cashflowMonthlyChart');
                if (cashflowEl) new ApexCharts(cashflowEl, cashflowMonthlyOptions).render();

                // 2) Despesas por categoria (donut)
                const expensesOptions = {
                    series: (expensesByCategory || []).map(x => Number(x.total || 0)),
                    labels: (expensesByCategory || []).map(x => x.label),
                    chart: {
                        type: 'donut',
                        height: 240,
                        toolbar: {
                            show: false
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    dataLabels: {
                        formatter: (val) => Number(val).toFixed(0) + '%'
                    }
                };
                const expensesEl = document.querySelector('#expensesByCategoryChart');
                if (expensesEl) new ApexCharts(expensesEl, expensesOptions).render();

                // 3) Invoices por banco (linha) - usando series do backend
                const invoicesByBankOptions = {
                    series: gastosInvoicePorBanco || [],
                    chart: {
                        type: 'line',
                        height: 240,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 3
                    },
                    xaxis: {
                        categories: [
                            '{{ now()->subMonths(5)->format('M') }}',
                            '{{ now()->subMonths(4)->format('M') }}',
                            '{{ now()->subMonths(3)->format('M') }}',
                            '{{ now()->subMonths(2)->format('M') }}',
                            '{{ now()->subMonths(1)->format('M') }}',
                            '{{ now()->format('M') }}'
                        ],
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const invoicesByBankEl = document.querySelector('#invoicesByBankChart');
                if (invoicesByBankEl) new ApexCharts(invoicesByBankEl, invoicesByBankOptions).render();

                // 4) Vendas vs custos (bar)
                const salesVsCostsOptions = {
                    series: [{
                            name: 'Vendas',
                            data: [Number(@json($valorVendas))]
                        },
                        {
                            name: 'Custo Estoque',
                            data: [Number(@json($custoEstoque))]
                        },
                        {
                            name: 'Custo Vendidos',
                            data: [Number(@json($custoProdutosVendidos))]
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 240,
                        stacked: true,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#8b5cf6', '#3b82f6', '#f59e0b'],
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            columnWidth: '55%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: ['Total'],
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                maximumFractionDigits: 0
                            }),
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            })
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };
                const salesVsCostsEl = document.querySelector('#salesVsCostsChart');
                if (salesVsCostsEl) new ApexCharts(salesVsCostsEl, salesVsCostsOptions).render();
            });
        </script>

    </div>
