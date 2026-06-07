<div class="ch-insights-wrap" x-data="chInsights(@js($adherence), @js($goalsByMonth))" x-init="init()">

    <!-- KPIs -->
    <div class="ch-kpi-row">
        <div class="ch-kpi"><span class="ch-kpi-val">{{ $kpis['completions_30'] ?? 0 }}</span><span class="ch-kpi-label">conclusões (30d)</span></div>
        <div class="ch-kpi"><span class="ch-kpi-val">{{ $kpis['avg_per_day'] ?? 0 }}</span><span class="ch-kpi-label">média/dia</span></div>
        <div class="ch-kpi"><span class="ch-kpi-val">{{ $kpis['goals_done_6m'] ?? 0 }}</span><span class="ch-kpi-label">metas concluídas (6m)</span></div>
        <div class="ch-kpi"><span class="ch-kpi-val">{{ $kpis['active_goals'] ?? 0 }}</span><span class="ch-kpi-label">metas ativas</span></div>
    </div>

    <div class="ch-charts">
        <div class="ch-chart-card">
            <h4><i class="bi bi-activity"></i> Adesão de hábitos (30 dias)</h4>
            <div x-ref="adherence" wire:ignore></div>
        </div>
        <div class="ch-chart-card">
            <h4><i class="bi bi-bar-chart"></i> Metas concluídas por mês</h4>
            <div x-ref="goals" wire:ignore></div>
        </div>
    </div>
</div>

<script>
function chInsights(adherence, goalsByMonth) {
    return {
        charts: [],
        init() {
            this.$nextTick(() => this.render(adherence, goalsByMonth));
        },
        render(adherence, goalsByMonth) {
            if (typeof ApexCharts === 'undefined') {
                // tenta de novo em breve (ApexCharts carrega no head)
                setTimeout(() => this.render(adherence, goalsByMonth), 300);
                return;
            }
            const isDark = document.documentElement.classList.contains('dark');
            const txt = isDark ? '#cbd5e1' : '#475569';

            const a = new ApexCharts(this.$refs.adherence, {
                chart: { type: 'area', height: 240, toolbar: { show: false }, foreColor: txt },
                series: [{ name: 'Conclusões', data: adherence.data }],
                xaxis: { categories: adherence.labels, labels: { rotate: -45, style: { fontSize: '10px' } }, tickAmount: 8 },
                colors: ['#a490c2'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                grid: { borderColor: isDark ? 'rgba(164,144,194,0.15)' : 'rgba(74,78,143,0.1)' },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            });
            a.render();

            const g = new ApexCharts(this.$refs.goals, {
                chart: { type: 'bar', height: 240, toolbar: { show: false }, foreColor: txt },
                series: [{ name: 'Metas', data: goalsByMonth.data }],
                xaxis: { categories: goalsByMonth.labels },
                colors: ['#4a4e8f'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                dataLabels: { enabled: true },
                grid: { borderColor: isDark ? 'rgba(164,144,194,0.15)' : 'rgba(74,78,143,0.1)' },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            });
            g.render();

            this.charts = [a, g];
        }
    };
}
</script>
