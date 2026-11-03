// Barra de baixo com bolinha (Configurações › Aparência › Movimento).
// Só liga quando <html data-fm-nav="bolinha">; sem isso a barra continua a de sempre.
// A aba aberta vira uma bolinha colorida que desliza até a aba tocada e pode ser arrastada.
(function () {
    if (window.__fmBeadBound) return;
    window.__fmBeadBound = true;

    // Cores das abas, na ordem em que aparecem (Início, Vendas, Clientes, Produtos, ML, Mais)
    const COLORS = ['#a3e635', '#34d399', '#a78bfa', '#fbbf24', '#38bdf8', '#f472b6'];
    const DARK_INK = '#1f2937';
    let lastX = null;      // posição da bolinha na página anterior (para deslizar entre páginas)
    let lastColor = null;

    const isOn = () => document.documentElement.getAttribute('data-fm-nav') === 'bolinha';
    const navEl = () => document.querySelector('.mobile-bottom-tabbar');
    const tabsOf = (nav) => Array.from(nav.querySelectorAll('.mobile-tab-item:not(.mobile-tab-fab)'))
        .filter((t) => t.offsetParent !== null && getComputedStyle(t).display !== 'none');

    function centerOf(nav, tab) {
        const icon = tab.querySelector('.tab-icon') || tab;
        const r = icon.getBoundingClientRect();
        return r.left + r.width / 2 - nav.getBoundingClientRect().left;
    }

    function colorOf(nav, tab) {
        const all = Array.from(nav.querySelectorAll('.mobile-tab-item:not(.mobile-tab-fab)'));
        return COLORS[Math.max(0, all.indexOf(tab)) % COLORS.length];
    }

    function paint(nav, bead, tab, animateIcon) {
        const c = colorOf(nav, tab);
        nav.style.setProperty('--fm-bead-c', c);
        nav.style.setProperty('--fm-bead-ink', DARK_INK);
        nav.querySelectorAll('.fm-bead-here').forEach((t) => t !== tab && t.classList.remove('fm-bead-here'));
        tab.classList.add('fm-bead-here');
        const svg = tab.querySelector('.tab-icon svg');
        const put = () => {
            bead.innerHTML = svg ? svg.outerHTML : '';
            bead.classList.remove('fm-swap');
        };
        if (animateIcon && bead.innerHTML) {
            bead.classList.add('fm-swap');
            setTimeout(put, 170);
        } else {
            put();
        }
        lastColor = c;
    }

    function moveTo(nav, x) {
        nav.style.setProperty('--fm-bx', x + 'px');
        lastX = x;
    }

    function off(nav) {
        nav.classList.remove('fm-has-bead', 'fm-bead-off', 'fm-dragging', 'fm-no-anim');
        nav.querySelector('.fm-bead')?.remove();
        nav.querySelectorAll('.fm-bead-here').forEach((t) => t.classList.remove('fm-bead-here'));
        nav.style.removeProperty('--fm-bx');
    }

    function setup() {
        const nav = navEl();
        if (!nav) return;
        if (!isOn()) { off(nav); return; }

        let bead = nav.querySelector('.fm-bead');
        if (!bead) {
            bead = document.createElement('div');
            bead.className = 'fm-bead';
            bead.setAttribute('aria-hidden', 'true');
            nav.appendChild(bead);
            bindDrag(nav, bead);
        }
        nav.classList.add('fm-has-bead');

        const active = nav.querySelector('.mobile-tab-item.is-active:not(.mobile-tab-fab)');
        if (!active || active.offsetParent === null) {
            nav.classList.add('fm-bead-off');
            return;
        }
        nav.classList.remove('fm-bead-off');
        const target = centerOf(nav, active);

        // Começa onde a bolinha estava na página anterior e desliza até a aba aberta
        nav.classList.add('fm-no-anim');
        moveTo(nav, lastX ?? target);
        if (lastColor) nav.style.setProperty('--fm-bead-c', lastColor);
        void nav.offsetWidth;
        nav.classList.remove('fm-no-anim');
        requestAnimationFrame(() => {
            moveTo(nav, target);
            paint(nav, bead, active, false);
        });
    }

    // Toque numa aba: a bolinha já sai andando antes da página trocar
    document.addEventListener('click', (e) => {
        if (!isOn()) return;
        const tab = e.target.closest && e.target.closest('.mobile-bottom-tabbar .mobile-tab-item:not(.mobile-tab-fab):not(.mobile-tab-more)');
        if (!tab) return;
        const nav = tab.closest('.mobile-bottom-tabbar');
        const bead = nav.querySelector('.fm-bead');
        if (!bead) return;
        nav.classList.remove('fm-bead-off');
        moveTo(nav, centerOf(nav, tab));
        paint(nav, bead, tab, true);
    }, true);

    function bindDrag(nav, bead) {
        let drag = null;
        bead.addEventListener('pointerdown', (e) => {
            const tabs = tabsOf(nav);
            if (!tabs.length) return;
            const xs = tabs.map((t) => centerOf(nav, t));
            drag = { id: e.pointerId, startX: e.clientX, moved: false, tabs, xs, base: lastX ?? xs[0] };
            bead.setPointerCapture(e.pointerId);
            e.preventDefault();
        });
        bead.addEventListener('pointermove', (e) => {
            if (!drag || e.pointerId !== drag.id) return;
            const dx = e.clientX - drag.startX;
            if (!drag.moved && Math.abs(dx) < 4) return;
            drag.moved = true;
            nav.classList.add('fm-dragging');
            const min = Math.min(...drag.xs), max = Math.max(...drag.xs);
            const x = Math.max(min, Math.min(max, drag.base + dx));
            moveTo(nav, x);
            const near = nearest(drag, x);
            if (!near.tab.classList.contains('fm-bead-here')) paint(nav, bead, near.tab, true);
        });
        const end = (e) => {
            if (!drag || e.pointerId !== drag.id) return;
            const d = drag;
            drag = null;
            nav.classList.remove('fm-dragging');
            if (!d.moved) return;
            const near = nearest(d, lastX);
            moveTo(nav, d.xs[near.i]);
            if (near.tab.classList.contains('is-active')) return;
            setTimeout(() => near.tab.click(), 120);
        };
        bead.addEventListener('pointerup', end);
        bead.addEventListener('pointercancel', end);
    }

    function nearest(d, x) {
        let best = 0;
        d.xs.forEach((v, i) => { if (Math.abs(v - x) < Math.abs(d.xs[best] - x)) best = i; });
        return { i: best, tab: d.tabs[best] };
    }

    // Guarda onde a bolinha está no meio da animação, para a próxima página continuar dali
    document.addEventListener('livewire:navigating', () => {
        const nav = navEl();
        if (!nav || !nav.classList.contains('fm-has-bead')) return;
        const v = parseFloat(getComputedStyle(nav).getPropertyValue('--fm-bx'));
        if (!Number.isNaN(v)) lastX = v;
    });
    document.addEventListener('livewire:navigated', setup);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
    else setup();

    // Ligou/desligou na tela de Aparência, girou o aparelho ou mudou o tamanho
    new MutationObserver(setup).observe(document.documentElement, { attributes: true, attributeFilter: ['data-fm-nav', 'class'] });
    let rt;
    window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => { lastX = null; setup(); }, 150); });
    window.fmNavBeadRefresh = setup;
})();
