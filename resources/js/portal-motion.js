// Movimento ao rolar do portal (Vitrine › Estilo do site › Movimento ao rolar).
// Só liga com body.lk-motion-suave ou body.lk-motion-completo; o padrão (nenhum) não mexe em nada.
import '../css/portal-motion.css';

(function () {
    const body = document.body;
    const full = body.classList.contains('lk-motion-completo');
    if (!full && !body.classList.contains('lk-motion-suave')) return;
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // Blocos que surgem ao rolar; os cards de listas e faixas vão um depois do outro
    const BLOCKS = '.hm-sec, .hm-strip, .sf-hero-wrap, .ml-hero, .ml-cats, .ml-box, .sf-col-page, .sf-strip, .ml-toolbar, .ml-foot2, .sp-card, .pp-main ~ section, .rv-wrap';
    const ITEMS = '.ml-grid > *, .ml-rail > *, .ml-cats > *, .hm-row > *, .hm-cats > *, .hm-mosaic > *, .hm-quotes > *';

    const io = new IntersectionObserver((entries) => {
        let n = 0;
        entries.forEach((e) => {
            if (!e.isIntersecting) return;
            const el = e.target;
            if (el.dataset.moStagger) {
                el.style.setProperty('--mo-d', Math.min(n++, 8) * 70 + 'ms');
            }
            el.classList.add('mo-in');
            io.unobserve(el);
            setTimeout(() => { el.style.removeProperty('--mo-d'); el.style.willChange = 'auto'; }, 1600);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    function watch(el, stagger) {
        if (el.classList.contains('mo-r') || el.closest('.ml-cart, .ml-sheet, [role="dialog"]')) return;
        el.classList.add('mo-r');
        if (stagger) el.dataset.moStagger = '1';
        io.observe(el);
    }

    function words(el, baseDelay) {
        // Só texto, ou texto com <em>/<b> dentro (títulos com a última palavra em itálico)
        if (!el || el.dataset.moWords || Array.from(el.children).some((c) => !/^(EM|B|I|STRONG|SPAN)$/.test(c.tagName))) return;
        const txt = el.textContent.trim().replace(/\s+/g, ' ');
        if (!txt || txt.length > 90) return;
        el.dataset.moWords = '1';
        el.setAttribute('aria-label', txt);
        let i = 0;
        const split = (text, into) => text.split(/(\s+)/).forEach((w) => {
            if (w === '') return;
            if (/^\s+$/.test(w)) { into.appendChild(document.createTextNode(' ')); return; }
            const s = document.createElement('span');
            s.className = 'mo-w';
            s.setAttribute('aria-hidden', 'true');
            s.style.setProperty('--mo-i', i++);
            if (baseDelay) s.style.setProperty('--mo-d', baseDelay);
            s.textContent = w;
            into.appendChild(s);
        });
        const nodes = Array.from(el.childNodes);
        el.innerHTML = '';
        nodes.forEach((n) => {
            if (n.nodeType === 3) split(n.textContent, el);
            else if (n.nodeType === 1) { const c = n.cloneNode(false); c.setAttribute('aria-hidden', 'true'); split(n.textContent, c); el.appendChild(c); }
        });
        el.classList.add('mo-words');
        io.observe(el);
    }

    function scan(root) {
        root.querySelectorAll(BLOCKS).forEach((el) => {
            // As seções com faixa de cards já animam pelos cards: o bloco só aparece junto
            watch(el, false);
        });
        root.querySelectorAll(ITEMS).forEach((el) => watch(el, true));
        if (full) {
            root.querySelectorAll('.sf-d-title, .ml-hero h1, .hm-words, .hm-h').forEach((el) => words(el, '.15s'));
        }
    }

    // Aviso da loja correndo de um lado para o outro
    function marquee() {
        const n = document.querySelector('.sf-notice');
        if (!n || n.classList.contains('mo-marquee')) return;
        const inner = n.innerHTML;
        n.classList.add('mo-marquee');
        const track = document.createElement('span');
        track.className = 'mo-marquee-track';
        track.setAttribute('aria-hidden', 'true');
        const copies = Math.max(2, Math.ceil((window.innerWidth * 2) / Math.max(200, n.scrollWidth)) * 2);
        for (let i = 0; i < copies; i++) {
            const s = document.createElement('span');
            s.innerHTML = inner;
            track.appendChild(s);
        }
        const label = document.createElement('span');
        label.className = 'sr-only';
        label.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)';
        label.textContent = n.textContent.trim();
        n.innerHTML = '';
        n.appendChild(label);
        n.appendChild(track);
        n.style.setProperty('--mo-mq', Math.max(14, copies * 6) + 's');
    }

    // Texto que vai acendendo palavra por palavra conforme a página rola
    const reads = [];
    function readable(el) {
        if (el.dataset.moRead) return;
        const txt = el.textContent;
        if (txt.trim().length < 60 || el.querySelector('a, button, input')) return;
        el.dataset.moRead = '1';
        el.classList.add('mo-read');
        const parts = txt.split(/(\s+)/);
        el.innerHTML = '';
        parts.forEach((p) => {
            if (/^\s+$/.test(p)) { el.appendChild(document.createTextNode(p)); return; }
            if (!p) return;
            const s = document.createElement('span');
            s.className = 'mo-rw';
            s.textContent = p;
            el.appendChild(s);
        });
        reads.push({ el, ws: Array.from(el.querySelectorAll('.mo-rw')) });
    }
    let ticking = false;
    function paintReads() {
        ticking = false;
        const vh = window.innerHeight;
        reads.forEach(({ el, ws }) => {
            const r = el.getBoundingClientRect();
            // começa a acender quando o texto entra pela parte de baixo e termina perto do meio da tela
            const p = Math.min(1, Math.max(0, (vh * 0.92 - r.top) / (r.height + vh * 0.35)));
            const lit = Math.round(p * ws.length);
            ws.forEach((w, i) => w.classList.toggle('lit', i < lit));
        });
    }
    function onScroll() {
        if (!ticking) { ticking = true; requestAnimationFrame(paintReads); }
    }

    // Carrinho pulando quando um produto entra
    function cartBump() {
        const Alpine = window.Alpine;
        if (!Alpine || !Alpine.store || !Alpine.store('cart')) return;
        let last = Alpine.store('cart').count;
        Alpine.effect(() => {
            const c = Alpine.store('cart').count;
            if (c > last) {
                document.querySelectorAll('.ml-header .fa-cart-shopping').forEach((i) => {
                    const b = i.closest('.ml-icon-btn') || i;
                    b.classList.remove('mo-bump');
                    void b.offsetWidth;
                    b.classList.add('mo-bump');
                });
            }
            last = c;
        });
    }

    function start() {
        scan(document);
        if (full) {
            marquee();
            document.querySelectorAll('.sp-text, [data-mo-read]').forEach(readable);
            if (reads.length) {
                window.addEventListener('scroll', onScroll, { passive: true });
                window.addEventListener('resize', onScroll);
                paintReads();
            }
            if (window.Alpine) cartBump();
            else document.addEventListener('alpine:initialized', cartBump, { once: true });
        }
        // Produtos que chegam pela rolagem infinita também surgem
        const mo = new MutationObserver((list) => {
            list.forEach((m) => m.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches(ITEMS)) watch(node, true);
                else if (node.querySelectorAll) scan(node);
            }));
        });
        document.querySelectorAll('.ml-grid, .ml-rail').forEach((g) => mo.observe(g, { childList: true }));
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
