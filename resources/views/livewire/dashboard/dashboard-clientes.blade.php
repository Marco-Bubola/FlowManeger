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
        $segLabels = ['Recorrentes (3+ compras)','Novos no período','Sem comprar há 6 meses','Com saldo em aberto'];
    @endphp

    {{-- HEADER --}}
    <x-dash.page-header title="Clientes" active="clientes" icon="bi-people-fill" gradient="from-sky-500 via-blue-500 to-indigo-500"
        subtitle="Base, compras e fidelidade">
        <x-slot:period>
            <x-dash.period-chips :options="\App\Livewire\Dashboard\DashboardClientes::PERIODS" :current="$period" />
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $periodLabel }}</span>
        </x-slot:period>
    </x-dash.page-header>

    {{-- KPIs --}}
    <div class="dash-kpis dash-kpis-8">
        <x-dash.kpi label="Clientes" tone="indigo" icon="bi-people" :value="$totalClientes ?? 0" countup />
        <x-dash.kpi label="Novos" tone="emerald" icon="bi-person-plus" :value="$clientesNovosMes ?? 0" countup />
        <x-dash.kpi label="Compraram" tone="teal" icon="bi-person-check" :value="$clientesComCompraMes ?? 0" countup />
        <x-dash.kpi label="Vendido" tone="amber" icon="bi-cash-coin" :value="$fmt($receitaClientesMes ?? 0)" />
        <x-dash.kpi label="Ticket médio" tone="sky" icon="bi-receipt" :value="$fmt($ticketMedioClientes ?? 0)" />
        <x-dash.kpi label="Recorrentes" tone="purple" icon="bi-arrow-repeat" :value="$clientesRecorrentesCount ?? 0" countup />
        <x-dash.kpi label="Devendo" tone="rose" icon="bi-person-exclamation" :value="$clientesInadimplentes ?? 0" countup />
        <x-dash.kpi label="Inativos (6 meses)" tone="slate" icon="bi-person-dash" :value="$clientesInativosCount ?? 0" countup />
    </div>

    {{-- GRID --}}
    <div class="dash-grid">
        {{-- Top clientes por receita --}}
        <x-dash.card title="Clientes que mais compraram" sub="No período" icon="bi-trophy" tone="amber" span="dash-col-8">
            @if(!empty($topClientes))
                <div class="dash-list dash-scroll max-h-[300px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($topClientes) ? $topClientes : $topClientes->toArray(), 0, 8) as $i => $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? ($c['client_name'] ?? 'Cliente');
                            $total = $c['total_vendas'] ?? 0;
                            $qtd = (int) ($c['qtd_vendas'] ?? 0);
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'#' . ($i + 1) . ' · ' . $qtd . ($qtd === 1 ? ' compra' : ' compras')" icon="bi-star-fill" tone="amber" :value="$fmt($total)" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-trophy" message="Nenhuma compra neste período" />
            @endif
        </x-dash.card>

        {{-- Segmentação (donut) --}}
        <x-dash.card title="Perfil da base" sub="Clientes por situação" icon="bi-pie-chart" tone="indigo" span="dash-col-4">
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
                            $dia = $c['date'] ?? '';
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="'Aniversário ' . $dia" icon="bi-gift-fill" tone="rose" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-balloon" message="Nenhum aniversariante este mês" />
            @endif
        </x-dash.card>

        {{-- Pendentes --}}
        <x-dash.card title="Saldo em aberto" sub="Clientes que ainda devem" icon="bi-person-exclamation" tone="amber" span="dash-col-4">
            @if(!empty($clientesPendentes))
                <div class="dash-list dash-scroll max-h-[240px] overflow-y-auto pr-1">
                    @foreach(array_slice(is_array($clientesPendentes) ? $clientesPendentes : $clientesPendentes->toArray(), 0, 8) as $c)
                        @php
                            $nome = $c['name'] ?? $c['nome'] ?? 'Cliente';
                            $valor = $c['pending_value'] ?? 0;
                        @endphp
                        <x-dash.list-item :title="$nome" :sub="(int) ($c['sales_count'] ?? 0) . ((int) ($c['sales_count'] ?? 0) === 1 ? ' venda em aberto' : ' vendas em aberto')" icon="bi-hourglass-split" tone="amber" :value="$fmt($valor)" />
                    @endforeach
                </div>
            @else
                <x-dash.empty icon="bi-check-circle" message="Nenhum cliente devendo" />
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
                            if ($quando) { try { $quando = 'Cadastrado ' . \Carbon\Carbon::createFromFormat('d/m/Y', $quando)->startOfDay()->diffForHumans(); } catch (\Throwable $e) {} }
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
