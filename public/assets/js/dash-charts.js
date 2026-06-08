/* ═══════════════════════════════════════════════════════════
   DASH CHARTS — FlowManager
   Monta gráficos ApexCharts (data-dash-chart), sparklines (data-spark)
   e anima números (data-countup). Tema dark/light automático +
   re-render no toggle de tema e em updates do Livewire.
   ════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const registry = new Map(); // id -> ApexCharts instance

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function baseOptions(type, opts) {
        const dark = isDark();
        const palette = opts.colors || ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#0ea5e9', '#8b5cf6'];
        const base = {
            chart: {
                type,
                height: opts.height || undefined,
                fontFamily: 'inherit',
                toolbar: { show: false },
                sparkline: { enabled: !!opts.spark },
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                background: 'transparent',
                parentHeightOffset: 0,
            },
            theme: { mode: dark ? 'dark' : 'light' },
            grid: { borderColor: dark ? 'rgba(71,85,105,.25)' : 'rgba(148,163,184,.2)', strokeDashArray: 4, padding: { left: 2, right: 2 } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: type === 'line' ? 3 : (type === 'area' ? 2.5 : 0) },
            colors: palette,
            tooltip: { theme: dark ? 'dark' : 'light' },
            legend: { labels: { colors: dark ? '#cbd5e1' : '#475569' }, fontSize: '12px' },
            xaxis: {
                labels: { style: { colors: dark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { style: { colors: dark ? '#94a3b8' : '#64748b', fontSize: '11px' } } },
        };

        if (type === 'area') {
            base.fill = { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90] } };
        }
        if (type === 'bar') {
            base.plotOptions = { bar: { borderRadius: 6, columnWidth: '55%', borderRadiusApplication: 'end' } };
        }
        if (type === 'donut' || type === 'pie') {
            base.plotOptions = { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '12px', color: dark ? '#cbd5e1' : '#475569' } } } } };
            base.legend = Object.assign(base.legend, { position: 'bottom' });
            base.stroke = { width: 0 };
        }
        if (type === 'radialBar') {
            base.plotOptions = { radialBar: { hollow: { size: '60%' }, dataLabels: { name: { color: dark ? '#cbd5e1' : '#475569' }, value: { color: dark ? '#fff' : '#0f172a', fontWeight: 800 } } } };
        }
        if (opts.spark) {
            base.tooltip = { fixed: { enabled: false }, x: { show: false }, marker: { show: false }, theme: dark ? 'dark' : 'light' };
        }

        return base;
    }

    function buildConfig(cfg) {
        const type = cfg.type || 'area';
        const opts = {
            colors: cfg.colors,
            height: cfg.height,
            extra: cfg.extra || {},
        };
        const base = baseOptions(type, opts);

        // Séries / labels
        let series = cfg.series || [];
        if (type === 'donut' || type === 'pie' || type === 'radialBar') {
            base.labels = cfg.labels || [];
            // séries devem ser array de números
        } else {
            base.xaxis.categories = cfg.labels || base.xaxis.categories;
        }
        base.series = series;

        // Merge de opções extras (profundo simples)
        if (cfg.extra && typeof cfg.extra === 'object') {
            deepMerge(base, cfg.extra);
        }
        return base;
    }

    function deepMerge(target, src) {
        for (const k in src) {
            if (src[k] && typeof src[k] === 'object' && !Array.isArray(src[k])) {
                target[k] = target[k] || {};
                deepMerge(target[k], src[k]);
            } else {
                target[k] = src[k];
            }
        }
        return target;
    }

    function mountChart(el) {
        if (typeof ApexCharts === 'undefined' || !el) return;
        if (el.dataset.dashMounted === '1') return;
        let cfg;
        try { cfg = JSON.parse(el.dataset.dashChart); } catch (e) { return; }
        if (!cfg) return;

        const options = buildConfig(cfg);
        if (!options.chart.height) {
            options.chart.height = el.clientHeight || 220;
        }
        try {
            const chart = new ApexCharts(el, options);
            chart.render();
            registry.set(el.id || ('chart_' + registry.size), chart);
            el.dataset.dashMounted = '1';
            el._dashChart = chart;
        } catch (e) { /* noop */ }
    }

    function mountSpark(el) {
        if (typeof ApexCharts === 'undefined' || !el) return;
        if (el.dataset.dashMounted === '1') return;
        let data;
        try { data = JSON.parse(el.dataset.spark); } catch (e) { return; }
        if (!Array.isArray(data) || !data.length) return;
        const color = el.dataset.sparkColor || '#6366f1';
        const options = baseOptions('area', { spark: true, colors: [color] });
        options.series = [{ data }];
        options.chart.height = el.clientHeight || 30;
        try {
            const chart = new ApexCharts(el, options);
            chart.render();
            registry.set('spark_' + registry.size, chart);
            el.dataset.dashMounted = '1';
            el._dashChart = chart;
        } catch (e) { /* noop */ }
    }

    function animateCount(el) {
        if (el.dataset.countDone === '1') return;
        const raw = el.dataset.countup;
        const target = parseFloat(String(raw).replace(/\./g, '').replace(',', '.'));
        if (isNaN(target)) return;
        el.dataset.countDone = '1';
        const decimals = (String(raw).split(',')[1] || '').length;
        const dur = 1100, start = performance.now();
        const fmt = (n) => n.toLocaleString('pt-BR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        function tick(now) {
            const p = Math.min(1, (now - start) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(target * eased);
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = fmt(target);
        }
        requestAnimationFrame(tick);
    }

    function initAll(root) {
        root = root || document;
        root.querySelectorAll('[data-dash-chart]').forEach(mountChart);
        root.querySelectorAll('[data-spark]').forEach(mountSpark);
        root.querySelectorAll('[data-countup]').forEach(animateCount);
    }

    function destroyAll() {
        registry.forEach((c) => { try { c.destroy(); } catch (e) {} });
        registry.clear();
        document.querySelectorAll('[data-dash-mounted="1"]').forEach((el) => { el.dataset.dashMounted = ''; el._dashChart = null; });
    }

    // Re-render no toggle de tema
    window.addEventListener('flowmanager:theme-changed', () => {
        destroyAll();
        setTimeout(() => initAll(), 60);
    });

    // Inicialização + Livewire
    document.addEventListener('DOMContentLoaded', () => initAll());
    document.addEventListener('livewire:navigated', () => { destroyAll(); setTimeout(() => initAll(), 60); });
    document.addEventListener('livewire:init', () => {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('morph.updated', () => { setTimeout(() => initAll(), 50); });
        }
    });

    // Expor para uso manual
    window.dashInitCharts = initAll;
    window.dashDestroyCharts = destroyAll;
})();
