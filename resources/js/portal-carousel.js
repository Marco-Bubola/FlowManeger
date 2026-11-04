// Carrosséis e botão de pôr no carrinho do portal (Vitrine › Estilo do site › Movimento).
// Só carrega quando a loja escolhe algo diferente de "Arrastar para o lado" / "Como sempre".
//  - Vidro 3D: o produto do meio fica na frente e os dos lados giram para trás, em cards de vidro.
//  - Cartas em leque: os produtos ficam empilhados e abertos em leque.
//  - Girando sozinho: a faixa de sempre, andando sozinha (para quando o cliente mexe).
//  - Caminhãozinho / Caixa fechando: o botão "Adicionar ao carrinho" vira uma animação e mostra "No carrinho".
import '../css/portal-carousel.css';

(function () {
    const body = document.body;
    const has = (c) => body.classList.contains(c);
    const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mode = ['vidro', 'pilha', 'giro'].find((m) => has('lk-carousel-' + m));
    const RAILS = '.ml-rail, .hm-row';

    // ───── Vidro 3D e Cartas em leque ─────
    function flow(rail) {
        const items = Array.from(rail.children).filter((c) => c.nodeType === 1);
        if (items.length < 3 || rail.dataset.cf) return;
        rail.dataset.cf = '1';
        const n = items.length;
        let active = 0;
        let timer = null;

        const stage = document.createElement('div');
        stage.className = 'cf-stage cf-' + mode;
        rail.parentNode.insertBefore(stage, rail);
        stage.appendChild(rail);
        rail.classList.add('cf-rail');
        items.forEach((c, i) => { c.classList.add('cf-item'); c.style.setProperty('--cf-i', i); });

        // Nome do produto do meio + pontinhos, como no vídeo
        const bar = document.createElement('div');
        bar.className = 'cf-bar';
        bar.innerHTML = '<button type="button" class="cf-arrow" data-d="-1" aria-label="Anterior">‹</button>'
            + '<span class="cf-pill"><span class="cf-dots"></span><b class="cf-name"></b></span>'
            + '<button type="button" class="cf-arrow" data-d="1" aria-label="Próximo">›</button>';
        stage.appendChild(bar);
        const dots = bar.querySelector('.cf-dots');
        const name = bar.querySelector('.cf-name');
        items.forEach((_, i) => {
            const d = document.createElement('i');
            d.addEventListener('click', () => go(i, true));
            dots.appendChild(d);
        });
        const titleOf = (el) => (el.querySelector('.ml-title, .hm-card-name, .ml-card-link') || {}).textContent || '';

        function layout() {
            items.forEach((el, i) => {
                let d = i - active;
                if (d > n / 2) d -= n;
                if (d < -n / 2) d += n;
                const a = Math.abs(d);
                el.style.setProperty('--d', d);
                el.style.setProperty('--a', a);
                el.style.zIndex = String(20 - a);
                el.classList.toggle('cf-on', d === 0);
                el.classList.toggle('cf-far', a > 2);
                el.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
            });
            dots.querySelectorAll('i').forEach((d, i) => d.classList.toggle('on', i === active));
            name.textContent = titleOf(items[active]).trim();
            // Altura do palco = card mais alto
            const h = Math.max(...items.map((el) => el.offsetHeight));
            if (h) rail.style.height = h + 'px';
        }
        function go(i, user) {
            active = (i + n) % n;
            layout();
            if (user) restart();
        }
        function restart() {
            clearInterval(timer);
            if (reduce) return;
            timer = setInterval(() => { if (!document.hidden && !stage.matches(':hover')) go(active + 1); }, 3800);
        }
        bar.querySelectorAll('.cf-arrow').forEach((b) => b.addEventListener('click', () => go(active + Number(b.dataset.d), true)));

        // Tocar num card do lado traz ele para o meio (sem abrir a página)
        rail.addEventListener('click', (e) => {
            const it = e.target.closest('.cf-item');
            if (!it || it.classList.contains('cf-on')) return;
            e.preventDefault();
            e.stopPropagation();
            go(items.indexOf(it), true);
        }, true);

        // Arrastar para os lados
        let sx = null, moved = false;
        rail.addEventListener('pointerdown', (e) => { sx = e.clientX; moved = false; });
        rail.addEventListener('pointermove', (e) => { if (sx !== null && Math.abs(e.clientX - sx) > 8) moved = true; });
        const up = (e) => {
            if (sx === null) return;
            const dx = e.clientX - sx;
            sx = null;
            if (Math.abs(dx) > 40) go(active + (dx < 0 ? 1 : -1), true);
        };
        rail.addEventListener('pointerup', up);
        rail.addEventListener('pointercancel', () => { sx = null; });
        rail.addEventListener('click', (e) => { if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; } }, true);
        stage.tabIndex = 0;
        stage.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') go(active + 1, true);
            if (e.key === 'ArrowLeft') go(active - 1, true);
        });

        // Fotos chegando mudam a altura
        rail.querySelectorAll('img').forEach((img) => { if (!img.complete) img.addEventListener('load', layout, { once: true }); });
        window.addEventListener('resize', layout);
        layout();
        restart();
    }

    // ───── Girando sozinho: a faixa de sempre andando ─────
    function spin(rail) {
        if (rail.dataset.cf || rail.children.length < 3) return;
        rail.dataset.cf = '1';
        if (reduce) return;
        let paused = 0;
        const hold = () => { paused = Date.now() + 6000; };
        ['pointerdown', 'wheel', 'touchstart', 'mouseenter'].forEach((ev) => rail.addEventListener(ev, hold, { passive: true }));
        setInterval(() => {
            if (document.hidden || Date.now() < paused || rail.matches(':hover')) return;
            const first = rail.children[0];
            const step = first ? first.getBoundingClientRect().width + 10 : 160;
            const end = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 4;
            rail.scrollTo({ left: end ? 0 : rail.scrollLeft + step, behavior: 'smooth' });
        }, 3200);
    }

    function scan(root) {
        if (!mode) return;
        root.querySelectorAll(RAILS).forEach((r) => {
            if (r.closest('.ml-cart, .ml-sheet, [role="dialog"]') || r.querySelector(':scope > template')) return; // faixas montadas pelo Alpine ficam como estão
            if (r.classList.contains('hm-row') && window.matchMedia('(min-width: 1024px)').matches && mode !== 'giro') return; // no PC os modelos mostram em grade
            if (mode === 'giro') spin(r); else flow(r);
        });
    }

    // ───── Botão de pôr no carrinho ─────
    const btnMode = ['caminhao', 'caixa'].find((m) => has('lk-buybtn-' + m));
    const TRUCK = '<svg viewBox="0 0 64 36" aria-hidden="true"><rect x="2" y="6" width="38" height="22" rx="4" fill="currentColor"/><path d="M40 12h11l9 9v7H40z" fill="currentColor"/><rect x="44" y="14" width="8" height="6" rx="1" fill="#fff" opacity=".85"/><rect x="8" y="11" width="9" height="7" rx="1.5" fill="#fff" opacity=".9"/><rect x="20" y="11" width="9" height="7" rx="1.5" fill="#fff" opacity=".9"/><circle cx="14" cy="30" r="5" fill="#1f2937"/><circle cx="14" cy="30" r="2" fill="#e5e7eb"/><circle cx="50" cy="30" r="5" fill="#1f2937"/><circle cx="50" cy="30" r="2" fill="#e5e7eb"/></svg>';
    const BOX = '<svg viewBox="0 0 36 36" aria-hidden="true"><path d="M4 12 18 5l14 7v15l-14 7-14-7z" fill="currentColor"/><path class="bx-lid" d="M4 12 18 19l14-7" fill="none" stroke="#fff" stroke-width="2" opacity=".8"/><path d="M18 19v15" stroke="#fff" stroke-width="2" opacity=".6"/></svg>';
    function playBtn(btn) {
        if (btn.disabled || btn.classList.contains('bb-run')) return;
        const w = btn.offsetWidth;
        btn.style.minWidth = w + 'px';
        btn.classList.add('bb-btn', 'bb-run', 'bb-' + btnMode);
        const fx = document.createElement('span');
        fx.className = 'bb-fx';
        fx.setAttribute('aria-hidden', 'true');
        fx.innerHTML = (btnMode === 'caminhao' ? '<span class="bb-box"></span><span class="bb-truck">' + TRUCK + '</span>' : '<span class="bb-pack">' + BOX + '</span>')
            + '<span class="bb-done"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M6 10.4 8.8 13 14 7.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg> No carrinho</span>';
        btn.appendChild(fx);
        const total = reduce ? 1600 : (btnMode === 'caminhao' ? 3400 : 2900);
        setTimeout(() => { fx.remove(); btn.classList.remove('bb-run', 'bb-' + btnMode); btn.style.minWidth = ''; }, total);
    }
    if (btnMode) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest && e.target.closest('[data-testid="add-cart"], [data-testid="buy-together-add"], .ml-sheet .ml-btn-primary');
            if (btn) playBtn(btn);
        }, true);
    }

    function start() {
        scan(document);
        // Faixas que chegam depois (vistos recentemente, relacionados)
        new MutationObserver((list) => list.forEach((m) => m.addedNodes.forEach((n) => {
            if (n.nodeType !== 1 || !n.querySelectorAll || n.closest('.cf-stage')) return;
            scan(n.parentNode || n);
        }))).observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
