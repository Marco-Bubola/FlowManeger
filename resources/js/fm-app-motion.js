// Animações nas telas do app (Configurações › Aparência › Movimento › "Cards surgindo").
// Só liga com <html data-fm-motion="sim">; desligado (padrão) não mexe em nada.
// Cabeçalho da página desce de leve, e cards, indicadores e listas surgem ao rolar, um depois do outro.
// Usa animações do navegador (el.animate) e não classes: o Livewire refaz as classes dos
// elementos a cada atualização da tela e apagaria qualquer marca posta aqui.
(function () {
    if (window.__fmMotionBound) return;
    window.__fmMotionBound = true;

    const reduce = () => window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isOn = () => document.documentElement.getAttribute('data-fm-motion') === 'sim' && !reduce()
        && !document.documentElement.classList.contains('no-animations') // "Reduzir animações" em Aparência
        && 'IntersectionObserver' in window && typeof Element.prototype.animate === 'function';
    const CARD = /(^|\s)([a-z0-9]+-)*card(-modern)?(\s|$)/;

    let io = null;
    let mo = null;
    let seen = new WeakSet();
    let batch = 0;
    let batchTimer = null;

    function looksLikeCard(el) {
        if (/^(INPUT|SELECT|TEXTAREA|BUTTON|LABEL|OPTION|TEMPLATE|SCRIPT|STYLE)$/.test(el.tagName)) return false;
        const c = typeof el.className === 'string' ? el.className : '';
        if (CARD.test(c)) return true;
        // Campos de formulário em grade não animam
        if (el.querySelector('input:not([type="checkbox"]):not([type="hidden"]), select, textarea')) return false;
        return /rounded/.test(c) && /(shadow|border)/.test(c);
    }

    function watch(el) {
        if (seen.has(el) || el.closest('[role="dialog"], [aria-modal="true"], .mobile-action-sheet, .fixed')) return;
        seen.add(el);
        io.observe(el);
    }

    function scan(root) {
        if (!io) return;
        root.querySelectorAll('.grid, .dash-kpis, [class*="-grid"]').forEach((g) => {
            const kids = Array.from(g.children).filter((k) => k.nodeType === 1 && looksLikeCard(k));
            if (kids.length >= 2) kids.forEach(watch);
        });
        root.querySelectorAll('.product-card-modern, .ap-card').forEach(watch);
    }

    function show(el) {
        // Os que entram juntos na tela vão um depois do outro
        const d = Math.min(batch++, 8) * 55;
        clearTimeout(batchTimer);
        batchTimer = setTimeout(() => { batch = 0; }, 120);
        el.animate([
            { opacity: 0, transform: 'translateY(18px) scale(.985)' },
            { opacity: 1, transform: 'none' },
        ], { duration: 600, delay: d, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' });
    }

    function stop() {
        if (io) io.disconnect();
        if (mo) mo.disconnect();
        io = mo = null;
        seen = new WeakSet();
    }

    function start() {
        stop();
        if (!isOn()) return;
        const main = document.getElementById('mainContent');
        if (!main) return;
        io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (!e.isIntersecting) return;
                io.unobserve(e.target);
                show(e.target);
            });
        }, { threshold: 0 });
        // Cabeçalho entrando de cima a cada página aberta
        main.querySelectorAll('.app-ph').forEach((h) => h.animate([
            { opacity: 0, transform: 'translateY(-14px)' }, { opacity: 1, transform: 'none' },
        ], { duration: 550, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' }));
        scan(main);
        // Itens que chegam depois (rolagem infinita, filtros, abas)
        mo = new MutationObserver((list) => {
            if (!io) return;
            list.forEach((m) => m.addedNodes.forEach((n) => {
                if (n.nodeType !== 1) return;
                const p = n.parentElement;
                if (n.matches('.product-card-modern, .ap-card')) watch(n);
                else if (p && looksLikeCard(n) && p.children.length > 1 && /(^|\s)grid(\s|$)|-grid|dash-kpis/.test(typeof p.className === 'string' ? p.className : '')) watch(n);
                if (n.querySelectorAll) scan(n);
            }));
        });
        mo.observe(main, { childList: true, subtree: true });
    }

    document.addEventListener('livewire:navigated', start);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
    // Ligou ou desligou na tela de Aparência (só reage quando muda de verdade)
    let was = document.documentElement.getAttribute('data-fm-motion');
    new MutationObserver(() => {
        const now = document.documentElement.getAttribute('data-fm-motion');
        if (now !== was) { was = now; if (now === 'sim') start(); else stop(); }
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-fm-motion'] });
})();
