<div class="dashboard-clientes-page dash-page mobile-393-base w-full">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @php
        $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $segSeries = [
            (int)($clientesRecorrentesCount ?? 0),
            (int)($clientesNovosMes ?? 0),
            (int)($clientesInativosCount ?? 0),
            (int)($clientesInadimplentes ?? 0),
        ];
        $segLabels = ['Recorrentes','Novos','Inativos','Inadimplentes'];
    @endphp

    {{-- HEADER --}}
    <div class="dash-header relative overflow-hidden rounded-2xl border border-white/40 dark:border-slate-700/50 bg-gradient-to-r from-white/80 via-sky-50/70 to-blue-50/60 dark:from-slate-800/90 dark:via-slate-800/40 dark:to-slate-900/60 backdrop-blur-xl shadow-xl mb-4">
        <div class="absolute -top-12 -right-10 w-44 h-44 rounded-full bg-gradient-to-br from-sky-400/20 to-blue-400/15 blur-3xl"></div>
        <div class="relative px-4 sm:px-5 py-3.5 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-500 flex items-center justify-center shadow-lg shadow-sky-500/30 shrink-0"><i class="bi bi-people-fill text-white text-lg"></i></div>
            <div class="flex-1 min-w-0">
                <h1 class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight truncate">Clientes</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Base, segmentação e fidelidade · {{ $periodLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="dash-kpis">
        <x-dash.kpi label="Total clientes" tone="indigo" icon="bi-people" :value="$totalClientes ?? 0" countup />
        <x-dash.kpi label="Novos no mês" tone="emerald" icon="bi-person-plus" :value="$clientesNovosMes ?? 0" countup />
        <x-dash.kpi label="Ativos" tone="teal" icon="bi-person-check" :value="$clientesComCompraMes ?? 0" countup />
        <x-dash.kpi label="Recorrentes" tone="purple" icon="bi-arrow-repeat" :value="$clientesRecorrentesCount ?? 0" countup />
        <x-dash.kpi label="Inadimplentes" tone="rose" icon="bi-person-exclamation" :value="$clientesInadimplentes ?? 0" countup />
        <x-dash.kpi label="Receita mês" tone="amber" icon="bi-cash-coin" :value="$fmt($receitaClientesMes ?? 0)" />
        <x-dash.kpi label="Ticket médio" tone="sky" icon="bi-receipt" :value="$fmt($ticketMedioClientes ?? 0)" />
        <x-dash.kpi label="Retenção" tone="blue" icon="bi-heart" :value="number_format($taxaRetencao ?? 0, 1) . '%'" />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Top clientes por receita --}}
        <x-dash.card title="Top clientes" sub="Maior receita" icon="bi-trophy" tone="amber" span="dash-col-8">
            @if(!empty($topClientes))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($topClientes) ? $topClientes : $topClientes->toArray(), 0, 8) as $i => $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? ($c['client_name'] ?? 'Cliente');
                            $total = $c['total'] ?? $c['receita'] ?? $c['total_gasto'] ?? 0;
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'#' . ($i + 1) . ' em receita'" icon="bi-star-fill" tone="amber" :value="$fmt($total)" trend="up" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-trophy" message="Sem dados de clientes" />
            @endif
        </x-dash.card>

        {{-- Segmentação (donut) --}}
        <x-dash.card title="Segmentação" sub="Perfil da base" icon="bi-pie-chart" tone="indigo" span="dash-col-4">
            @if(array_sum($segSeries) > 0)
                <x-dash.chart id="dashSegChart" type="donut" :series="$segSeries" :labels="$segLabels"
                    :colors="['#8b5cf6','#10b981','#64748b','#f43f5e']" />
            @else
                <x-dash.empty icon="bi-pie-chart" message="Sem segmentação" />
            @endif
        </x-dash.card>

        {{-- Aniversariantes --}}
        <x-dash.card title="Aniversariantes" sub="No mês" icon="bi-balloon" tone="rose" span="dash-col-4">
            @if(!empty($aniversariantesLista))
                <div class="dash-list dash-scroll max-h-[240px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($aniversariantesLista) ? $aniversariantesLista : $aniversariantesLista->toArray(), 0, 8) as $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? 'Cliente';
                            $dia = $c['dia'] ?? $c['birthday'] ?? $c['data'] ?? '';
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'Aniversário ' . $dia" icon="bi-gift-fill" tone="rose" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-balloon" message="Nenhum aniversariante este mês" />
            @endif
        </x-dash.card>

        {{-- Pendentes --}}
        <x-dash.card title="Clientes pendentes" sub="Com saldo a pagar" icon="bi-person-exclamation" tone="amber" span="dash-col-4">
            @if(!empty($clientesPendentes))
                <div class="dash-list dash-scroll max-h-[240px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($clientesPendentes) ? $clientesPendentes : $clientesPendentes->toArray(), 0, 8) as $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? 'Cliente';
                            $valor = $c['pendente'] ?? $c['valor'] ?? $c['total'] ?? 0;
                        @endphp
                        <x-dash.list-item :title="$nome" sub="Saldo em aberto" icon="bi-hourglass-split" tone="amber" :value="$fmt($valor)" trend="down" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-check-circle" message="Nenhum cliente pendente 🎉" />
            @endif
        </x-dash.card>

        {{-- Clientes recentes --}}
        <x-dash.card title="Clientes recentes" sub="Últimos cadastrados" icon="bi-person-plus-fill" tone="emerald" span="dash-col-4">
            @if(!empty($clientesRecentes))
                <div class="dash-list dash-scroll max-h-[240px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($clientesRecentes) ? $clientesRecentes : $clientesRecentes->toArray(), 0, 8) as $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? 'Cliente';
                            $quando = $c['created_at'] ?? $c['date'] ?? '';
                            if ($quando) { try { $quando = \Carbon\Carbon::parse($quando)->diffForHumans(); } catch (\Throwable $e) {} }
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="$quando ?: 'Novo cliente'" icon="bi-person-badge" tone="emerald" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-person-plus" message="Nenhum cliente recente" />
            @endif
        </x-dash.card>
    </div>
</div>
