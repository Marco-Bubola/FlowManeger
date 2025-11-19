(function(){
    // invoices-charts.js
    // invoices-charts.js
    // Inicializa e reinicializa o donut ApexCharts a partir de window.__categoriesChartData

    function formatCurrency(v){
        return 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getData(){
        // Prefer data embedded in the DOM (re-rendered by Livewire) if present
        try{
            const el = document.getElementById('categories-data');
            if(el && el.textContent){
                try{
                    const parsed = JSON.parse(el.textContent);
                    if(Array.isArray(parsed)) return parsed;
                }catch(e){ /* ignore parse errors */ }
            }

            // Fallback to window variable (legacy)
            if(Array.isArray(window.__categoriesChartData)){
                return window.__categoriesChartData;
            }
            return [];
        }catch(e){
            console.error('[invoices-charts] getData error', e);
            return [];
        }
    }

    let lastCategoriesJson = null;

    function removeTotalOverlay(){
        try{
            const old = document.getElementById('apex-pie-total');
            if(old && old.parentNode) old.parentNode.removeChild(old);
        }catch(e){}
    }

    function addTotalOverlayWithRetry(el, formattedTotal){
        let attempts = 0;
        function tryAdd(){
            attempts++;
            try{
                // parent where we place the overlay: prefer container's parent to avoid SVG stacking issues
                const parent = el.parentNode || el;
                if(!parent) throw new Error('no parent');
                // ensure parent is positioned
                const computed = window.getComputedStyle(parent);
                if(computed.position === 'static') parent.style.position = 'relative';

                removeTotalOverlay();
                const totalWrapper = document.createElement('div');
                totalWrapper.id = 'apex-pie-total';
                totalWrapper.setAttribute('aria-hidden', 'true');
                totalWrapper.style.pointerEvents = 'none';
                totalWrapper.style.position = 'absolute';
                totalWrapper.style.left = '50%';
                totalWrapper.style.top = '50%';
                totalWrapper.style.transform = 'translate(-50%, -50%)';
                totalWrapper.style.textAlign = 'center';
                totalWrapper.style.color = '#ffffff';
                totalWrapper.style.fontWeight = '700';
                totalWrapper.style.lineHeight = '1';
                totalWrapper.style.zIndex = '9999';
                totalWrapper.innerHTML = '<div style="font-size:12px;opacity:0.85">Total</div><div style="font-size:18px;margin-top:4px;">' + formattedTotal + '</div>';
                parent.appendChild(totalWrapper);
            }catch(err){
                // failed to add overlay on this attempt
                if(attempts < 6){
                    setTimeout(tryAdd, 80);
                }
            }
        }
        tryAdd();
    }

    function destroyChart(){

        // Destroy ApexCharts instance completely
        if(window.__invoicesChartInstance){
            try{
                if(typeof window.__invoicesChartInstance.destroy === 'function'){
                    window.__invoicesChartInstance.destroy();
                }
            }catch(e){ console.warn('[invoices-charts] destroy error', e); }
            window.__invoicesChartInstance = null;
        }

        // Remove overlay
        removeTotalOverlay();

        // Force clear container
        const el = document.querySelector('#apex-pie');
        if(el){
            try{
                el.innerHTML = '';
                // Remove any apexcharts classes/attributes
                el.removeAttribute('data-apexcharts-id');
                const toRemove = el.querySelectorAll('.apexcharts-canvas, .apexcharts-svg');
                toRemove.forEach(node => { try{ node.remove(); }catch(e){} });
            }catch(e){}
        }


    }

    function renderChart(){
        const el = document.querySelector('#apex-pie');
        if(!el || typeof ApexCharts === 'undefined'){
            console.warn('[invoices-charts] renderChart: no container or ApexCharts undefined');
            return;
        }

        const data = getData();
        if(!data || data.length === 0){
            destroyChart();
            return;
        }

        const labels = data.map(c => c.label);
        const series = data.map(c => Number(c.value) || 0);
        // normalize colors: ensure we have a color per series; fallback palette if missing
        const DEFAULT_PALETTE = ['#60a5fa','#93c5fd','#a78bfa','#f472b6','#fbbf24','#34d399','#fb7185','#60a5fa','#7c3aed'];
        const colors = data.map((c, i) => (c.color && String(c.color).trim()) ? c.color : DEFAULT_PALETTE[i % DEFAULT_PALETTE.length]);
        const total = series.reduce((a,b)=>a+b,0);



        // cor do texto central: escolher branca semi-opaca para fundos escuros
        const centerTextColor = '#ffffff';

        const options = {
            chart: {
                type: 'donut',
                height: 260,
                toolbar: { show: false },
                redrawOnParentResize: true,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 400,
                    animateGradually: { enabled: false },
                    dynamicAnimation: { enabled: false }
                }
            },
            series: series,
            labels: labels,
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: false },
                            total: {
                                show: true,
                                label: '',
                                fontSize: '14px',
                                fontWeight: 600,
                                color: centerTextColor,
                                formatter: function(w){ return formatCurrency(total); }
                            }
                        }
                    }
                }
            },
            legend: { show: false },
            tooltip: { theme: 'dark', style: { fontSize: '13px', color: '#fff' }, fillSeriesColor: false, y: { formatter: function(val){ return formatCurrency(val); } } },
            stroke: { colors: ['transparent'], width: 0 },
            responsive: [{ breakpoint: 480, options: { chart: { width: '100%' } } }],
            dataLabels: { enabled: false }
        };

        // CRITICAL: destroy old chart FIRST, then clear container
        destroyChart();

        // Small delay to ensure cleanup is complete
        setTimeout(function(){
            try{

                window.__invoicesChartInstance = new ApexCharts(el, options);
                window.__invoicesChartInstance.render().then(function(){


                    // atualizar cache do JSON (para o observer)
                    try{
                        const currentJson = document.getElementById('categories-data') ? document.getElementById('categories-data').textContent : JSON.stringify(data);
                        lastCategoriesJson = currentJson;

                    }catch(e){ lastCategoriesJson = JSON.stringify(data); }

                    // adicionar overlay resiliente mostrando o total centralizado (com retries)
                    try{
                        addTotalOverlayWithRetry(el, formatCurrency(total));
                    }catch(e){ console.error('failed to schedule total overlay', e); }

                        // no debug badge in production
                }).catch(function(err){
                    console.error('[invoices-charts] render failed', err);
                });
            }catch(err){
                console.error('[invoices-charts] create instance failed', err);
            }
        }, 100);
    }

    function safeRender(){
        try{
            renderChart();
        }catch(e){ console.error('[invoices-charts] render error', e); }
    }

    // Wrapper that waits for ApexCharts to be available before rendering
    function safeRenderWrapper(){
        if(typeof ApexCharts === 'undefined'){
            setTimeout(safeRenderWrapper, 150);
            return;
        }

        safeRender();
    }

    // On initial load (use wrapper that waits for ApexCharts)
    if(document.readyState === 'complete' || document.readyState === 'interactive'){
        safeRenderWrapper();
    } else {
        document.addEventListener('DOMContentLoaded', safeRenderWrapper);
    }

    // Re-render after Livewire updates (works when Livewire is present)
    if(window.Livewire && typeof window.Livewire.hook === 'function'){
        try{
            window.Livewire.hook('message.processed', (message, component) => {
                // small delay to allow DOM patch
                setTimeout(safeRenderWrapper, 40);
            });
        }catch(e){ /* ignore */ }
    } else {
        // Fallback: observe container mutations and re-render
        const container = document.querySelector('#apex-pie');
        if(container){
            const parent = container.parentNode;
            const mo = new MutationObserver((mutations) => {
                // reinit when children change
                safeRenderWrapper();
            });
            try{ mo.observe(parent, { childList: true, subtree: true, characterData: true }); }catch(e){}
        }
    }

    // Additional robust observer: watch for changes to the embedded JSON script
    try{
        const bodyObserver = new MutationObserver((mutations) => {
                const el = document.getElementById('categories-data');
            const current = el ? el.textContent : null;
                if(current && current !== lastCategoriesJson){
                    // small delay to allow DOM to stabilise
                    setTimeout(safeRenderWrapper, 40);
                }
        });
        bodyObserver.observe(document.body, { childList: true, subtree: true, characterData: true });
    }catch(e){}


    // expose for manual calls
    window.renderInvoicesDonut = safeRenderWrapper;
})();
